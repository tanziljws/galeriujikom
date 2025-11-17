<?php

namespace App\Http\Controllers;

use App\Models\PhotoReaction;
use App\Models\PhotoComment;
use App\Models\DownloadLog;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    public function index()
    {
        try {
            // Load categories from JSON file (tetap), dipakai sebagai daftar pilihan kategori
            $categoriesPath = resource_path('data/umbrella_categories.json');
            $categories = [];
            
            if (is_file($categoriesPath)) {
                try {
                    $jsonContent = file_get_contents($categoriesPath);
                    $decoded = json_decode($jsonContent, true);
                    // Handle both flat array and nested structure
                    if (is_array($decoded)) {
                        // If it's a nested structure (umbrella categories), flatten it
                        if (isset($decoded[0]) && is_array($decoded[0])) {
                            $categories = [];
                            foreach ($decoded as $umbrella => $subcats) {
                                if (is_array($subcats)) {
                                    $categories = array_merge($categories, $subcats);
                                } else {
                                    $categories[] = $subcats;
                                }
                            }
                        } else {
                            $categories = $decoded;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error reading categories file: ' . $e->getMessage());
                    $categories = [];
                }
            }
            
            // Ensure categories is always an array
            if (!is_array($categories)) {
                $categories = [];
            }

            $activeCategory = request('category', '');
            $searchQuery = request('search', '');

            // Query dari DB
            $query = GalleryItem::query()->whereNotNull('filename');
            if ($activeCategory !== '') {
                $query->where('category', $activeCategory);
            }
            if ($searchQuery !== '') {
                $query->where(function($q) use ($searchQuery){
                    $q->where('title', 'like', '%'.$searchQuery.'%')
                      ->orWhere('category', 'like', '%'.$searchQuery.'%');
                });
            }
            $items = $query->orderByDesc('created_at')->get();

            // Normalisasi dan grouping per judul menjadi album
            $albums = [];
            foreach ($items as $it) {
                $title = $it->title ?: 'Tanpa Judul';
                $url = $it->filename ? asset('uploads/gallery/'.$it->filename) : ($it->image_path ?? '');
                if (!isset($albums[$title])) {
                    $albums[$title] = [
                        'title' => $title,
                        'category' => $it->category ?? 'Lainnya',
                        'photos' => [],
                        'thumbnail' => $url,
                        'uploaded_at' => optional($it->created_at)->toDateTimeString(),
                        'photo_count' => 0,
                    ];
                }
                $albums[$title]['photos'][] = [
                    'id' => $it->id,
                    'title' => $title,
                    'category' => $it->category ?? 'Lainnya',
                    'url' => $url,
                    'uploaded_at' => optional($it->created_at)->toDateTimeString(),
                ];
                $albums[$title]['photo_count']++;
            }
            $albums = array_values($albums);
            usort($albums, function($a,$b){ return strcmp($b['uploaded_at'] ?? '', $a['uploaded_at'] ?? ''); });

            return view('gallery', [
                'categories' => $categories,
                'activeCategory' => $activeCategory,
                'searchQuery' => $searchQuery,
                'albums' => $albums
            ]);
        } catch (\Exception $e) {
            \Log::error('Gallery index error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return view with empty data on error
            return view('gallery', [
                'categories' => [],
                'activeCategory' => '',
                'searchQuery' => '',
                'albums' => []
            ]);
        }
    }
    
    public function showAlbum($title)
    {
        $albumTitle = urldecode($title);
        $photos = GalleryItem::where('title', $albumTitle)
            ->whereNotNull('filename')
            ->orderByDesc('created_at')
            ->get()
            ->map(function($it){
                return [
                    'id' => $it->id,
                    'title' => $it->title,
                    'category' => $it->category ?? 'Lainnya',
                    'filename' => $it->filename,
                    'url' => asset('uploads/gallery/'.$it->filename),
                    'uploaded_at' => optional($it->created_at)->toDateTimeString(),
                ];
            })->toArray();

        return view('gallery-album', [
            'albumTitle' => $albumTitle,
            'photos' => $photos,
            'category' => $photos[0]['category'] ?? ''
        ]);
    }
    public function reactToPhoto(Request $request)
    {
        $validated = $request->validate([
            'photo_id' => ['required', 'string'],
            'reaction' => ['required', 'string', Rule::in(['like', 'dislike', 'clear'])]
        ]);

        // Toggle behavior: if same reaction clicked again -> clear
        $existing = PhotoReaction::where('photo_id', $validated['photo_id'])
            ->where('user_id', Auth::id())
            ->first();

        if ($validated['reaction'] === 'clear' || ($existing && $existing->reaction === $validated['reaction'])) {
            if ($existing) { $existing->delete(); }
        } else {
            PhotoReaction::updateOrCreate(
                [ 'photo_id' => $validated['photo_id'], 'user_id' => Auth::id() ],
                [ 'reaction' => $validated['reaction'] ]
            );
        }

        $stats = $this->getPhotoStats($validated['photo_id']);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'user_reaction' => $stats['user_reaction'] ?? null,
        ]);
    }

    public function addComment(Request $request)
    {
        $validated = $request->validate([
            'photo_id' => ['required', 'string'],
            'comment' => ['required', 'string', 'max:1000']
        ]);

        $comment = PhotoComment::create([
            'photo_id' => $validated['photo_id'],
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'status' => 'approved' // Or 'pending' if you want to moderate comments
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'message' => 'Komentar berhasil ditambahkan.'
        ]);
    }

    public function getComments($photoId)
    {
        $comments = PhotoComment::with('user')
            ->where('photo_id', $photoId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    public function downloadPhoto(Request $request)
    {
        $validated = $request->validate([
            'photo_id' => ['required', 'string'],
            'photo_url' => ['required', 'url']
        ]);

        // Get the file name from URL
        $url = $validated['photo_url'];
        $fileName = basename(parse_url($url, PHP_URL_PATH));

        // Log the download
        DownloadLog::create([
            'user_id' => Auth::id(),
            'photo_id' => $validated['photo_id'],
            'url' => $validated['photo_url'],
            'filename' => $fileName,
            'ip' => $request->ip()
        ]);
        
        // For local files
        if (Str::startsWith($url, asset(''))) {
            $relativePath = str_replace(asset(''), '', $url);
            $filePath = public_path($relativePath);
            
            if (file_exists($filePath)) {
                return response()->download($filePath, $fileName);
            }
        }

        // For external files
        $tempFile = tempnam(sys_get_temp_dir(), 'download_');
        file_put_contents($tempFile, file_get_contents($url));
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function getPhotoStats($photoId)
    {
        $likes = PhotoReaction::where('photo_id', $photoId)
            ->where('reaction', 'like')
            ->count();

        $dislikes = PhotoReaction::where('photo_id', $photoId)
            ->where('reaction', 'dislike')
            ->count();

        $commentsCount = PhotoComment::where('photo_id', $photoId)
            ->where('status', 'approved')
            ->count();

        $userReaction = null;
        if (Auth::check()) {
            $reaction = PhotoReaction::where('photo_id', $photoId)
                ->where('user_id', Auth::id())
                ->first();
            
            if ($reaction) {
                $userReaction = $reaction->reaction;
            }
        }

        return [
            'likes' => $likes,
            'dislikes' => $dislikes,
            'comments_count' => $commentsCount,
            'user_reaction' => $userReaction
        ];
    }
}
