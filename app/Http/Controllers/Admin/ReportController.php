<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GalleryItem;
use App\Models\PhotoReaction;
use App\Models\DownloadLog;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        try {
            // Get user statistics
            $totalUsers = 0;
            $recentUsers = collect();
            try {
                $totalUsers = User::count();
                $recentUsers = User::orderBy('created_at', 'desc')->take(10)->get();
            } catch (\Exception $e) {
                \Log::error('Error fetching users: ' . $e->getMessage());
            }
            
            // Get photo statistics from database - use more efficient query
            $photos = collect();
            $photoReports = [];
            try {
                $photos = GalleryItem::whereNotNull('filename')->limit(100)->get();
            } catch (\Exception $e) {
                \Log::error('Error fetching photos: ' . $e->getMessage());
                $photos = collect();
            }
            
            // Get all reactions grouped by photo_id for efficiency
            $reactionsByPhoto = [];
            try {
                $reactionsData = PhotoReaction::select('photo_id', 'reaction', DB::raw('COUNT(*) as count'))
                    ->groupBy('photo_id', 'reaction')
                    ->get();
                foreach ($reactionsData as $r) {
                    if (!isset($reactionsByPhoto[$r->photo_id])) {
                        $reactionsByPhoto[$r->photo_id] = [];
                    }
                    $reactionsByPhoto[$r->photo_id][] = [
                        'reaction' => $r->reaction,
                        'count' => (int)$r->count
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching reactions: ' . $e->getMessage());
            }
            
            // Get all downloads grouped by photo_id
            $downloadsByPhoto = [];
            try {
                $downloadsData = DownloadLog::select('photo_id', DB::raw('COUNT(*) as count'))
                    ->groupBy('photo_id')
                    ->get();
                foreach ($downloadsData as $d) {
                    $downloadsByPhoto[$d->photo_id] = (int)$d->count;
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching downloads: ' . $e->getMessage());
            }
            
            foreach ($photos as $photo) {
                try {
                    $photoId = (string)$photo->id;
                    
                    $photoReactions = $reactionsByPhoto[$photoId] ?? [];
                    $likes = 0;
                    $dislikes = 0;
                    foreach ($photoReactions as $r) {
                        if ($r['reaction'] === 'like') {
                            $likes += (int)$r['count'];
                        } elseif ($r['reaction'] === 'dislike') {
                            $dislikes += (int)$r['count'];
                        }
                    }
                    $downloads = (int)($downloadsByPhoto[$photoId] ?? 0);
                    
                    $photoReports[] = [
                        'photo' => $photo,
                        'stats' => [
                            'likes' => (int)$likes,
                            'dislikes' => (int)$dislikes,
                            'downloads' => (int)$downloads
                        ]
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error processing photo stats for photo ID ' . $photo->id . ': ' . $e->getMessage());
                    continue;
                }
            }
            
            // Sort by total interactions (likes + dislikes + downloads)
            usort($photoReports, function($a, $b) {
                $totalA = ($a['stats']['likes'] ?? 0) + ($a['stats']['dislikes'] ?? 0) + ($a['stats']['downloads'] ?? 0);
                $totalB = ($b['stats']['likes'] ?? 0) + ($b['stats']['dislikes'] ?? 0) + ($b['stats']['downloads'] ?? 0);
                return $totalB - $totalA;
            });
            
            return view('admin.reports.index', [
                'totalUsers' => $totalUsers,
                'recentUsers' => $recentUsers,
                'photoReports' => $photoReports
            ]);
        } catch (\Exception $e) {
            \Log::error('Reports index error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return view('admin.reports.index', [
                'totalUsers' => 0,
                'recentUsers' => collect(),
                'photoReports' => []
            ])->withErrors(['error' => 'Terjadi kesalahan saat memuat laporan.']);
        }
    }
    
    /**
     * Show detailed user report
     */
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        
        return view('admin.reports.users', [
            'users' => $users
        ]);
    }
    
    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view('admin.reports.users-edit', [
            'user' => $user,
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$user->id],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('admin.reports.users')->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.reports.users')->with('status', 'Pengguna berhasil dihapus.');
    }
    
    /**
     * Show detailed photo report
     */
    public function photos()
    {
        try {
            // Get all photos with stats from database - use more efficient query
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get();
            $photoReports = [];
            
            // Get all reactions grouped by photo_id for efficiency
            $reactionsByPhoto = [];
            try {
                $reactionsData = PhotoReaction::select('photo_id', 'reaction', DB::raw('COUNT(*) as count'))
                    ->groupBy('photo_id', 'reaction')
                    ->get();
                foreach ($reactionsData as $r) {
                    if (!isset($reactionsByPhoto[$r->photo_id])) {
                        $reactionsByPhoto[$r->photo_id] = [];
                    }
                    $reactionsByPhoto[$r->photo_id][] = [
                        'reaction' => $r->reaction,
                        'count' => (int)$r->count
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching reactions: ' . $e->getMessage());
            }
            
            // Get all downloads grouped by photo_id
            $downloadsByPhoto = [];
            try {
                $downloadsData = DownloadLog::select('photo_id', DB::raw('COUNT(*) as count'))
                    ->groupBy('photo_id')
                    ->get();
                foreach ($downloadsData as $d) {
                    $downloadsByPhoto[$d->photo_id] = (int)$d->count;
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching downloads: ' . $e->getMessage());
            }
            
            foreach ($allPhotos as $photo) {
                try {
                    $photoId = (string)$photo->id;
                    
                    $photoReactions = $reactionsByPhoto[$photoId] ?? [];
                    $likes = 0;
                    $dislikes = 0;
                    foreach ($photoReactions as $r) {
                        if ($r['reaction'] === 'like') {
                            $likes += (int)$r['count'];
                        } elseif ($r['reaction'] === 'dislike') {
                            $dislikes += (int)$r['count'];
                        }
                    }
                    $downloads = (int)($downloadsByPhoto[$photoId] ?? 0);
                    
                    $photoReports[] = [
                        'photo' => $photo,
                        'stats' => [
                            'likes' => (int)$likes,
                            'dislikes' => (int)$dislikes,
                            'downloads' => (int)$downloads
                        ]
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error processing photo stats for photo ID ' . $photo->id . ': ' . $e->getMessage());
                    continue;
                }
            }
            
            return view('admin.reports.photos', [
                'photoReports' => $photoReports
            ]);
        } catch (\Exception $e) {
            \Log::error('Reports photos error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return view('admin.reports.photos', [
                'photoReports' => []
            ])->withErrors(['error' => 'Terjadi kesalahan saat memuat laporan foto.']);
        }
    }
    
    /**
     * Export user report to PDF
     */
    public function exportUsersPdf()
    {
        try {
            $users = User::orderBy('created_at', 'desc')->get();
            
            if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = Pdf::loadView('admin.reports.pdf.users', [
                    'users' => $users,
                    'generatedAt' => now()->format('d F Y H:i')
                ]);
                
                return $pdf->download('laporan-pengguna-' . date('Y-m-d') . '.pdf');
            } else {
                return back()->with('error', 'PDF library tidak tersedia. Silakan install dompdf.');
            }
        } catch (\Exception $e) {
            \Log::error('Export users PDF error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Export photo report to PDF
     */
    public function exportPhotosPdf()
    {
        try {
            // Get photos with stats from database
            $photoReports = [];
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get();
            
            foreach ($allPhotos as $photo) {
                try {
                    $likes = PhotoReaction::where('photo_id', (string)$photo->id)
                        ->where('reaction', 'like')
                        ->count();
                    
                    $dislikes = PhotoReaction::where('photo_id', (string)$photo->id)
                        ->where('reaction', 'dislike')
                        ->count();
                    
                    $downloads = DownloadLog::where('photo_id', (string)$photo->id)->count();
                    
                    $photoReports[] = [
                        'photo' => $photo,
                        'stats' => [
                            'likes' => $likes,
                            'dislikes' => $dislikes,
                            'downloads' => $downloads
                        ]
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error processing photo stats: ' . $e->getMessage());
                    continue;
                }
            }
            
            if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = Pdf::loadView('admin.reports.pdf.photos', [
                    'photoReports' => $photoReports,
                    'generatedAt' => now()->format('d F Y H:i')
                ])->setPaper('a4', 'landscape');
                
                return $pdf->download('laporan-foto-galeri-' . date('Y-m-d') . '.pdf');
            } else {
                return back()->with('error', 'PDF library tidak tersedia. Silakan install dompdf.');
            }
        } catch (\Exception $e) {
            \Log::error('Export photos PDF error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }
}
