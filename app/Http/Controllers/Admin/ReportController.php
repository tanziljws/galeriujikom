<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GalleryItem;
use App\Models\PhotoReaction;
use App\Models\DownloadLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Initialize all variables with safe defaults
        $totalUsers = 0;
        $recentUsers = collect();
        $photoReports = [];
        
        try {
            // Get user statistics - with full error handling
            try {
                if (class_exists(User::class)) {
                    $totalUsers = (int)User::count();
                    $recentUsers = User::orderBy('created_at', 'desc')->take(10)->get() ?: collect();
                }
            } catch (\Throwable $e) {
                \Log::error('Error fetching users in reports: ' . $e->getMessage());
                $totalUsers = 0;
                $recentUsers = collect();
            }
            
            // Get photos - with full error handling
            $photos = collect();
            try {
                if (class_exists(GalleryItem::class)) {
                    $photos = GalleryItem::whereNotNull('filename')->limit(100)->get() ?: collect();
                }
            } catch (\Throwable $e) {
                \Log::error('Error fetching photos in reports: ' . $e->getMessage());
                $photos = collect();
            }
            
            // Get reactions - with full error handling
            $reactionsByPhoto = [];
            try {
                if (class_exists(PhotoReaction::class) && Schema::hasTable('photo_reactions')) {
                    $reactionsData = PhotoReaction::select('photo_id', 'reaction', DB::raw('COUNT(*) as count'))
                        ->groupBy('photo_id', 'reaction')
                        ->get();
                    
                    foreach ($reactionsData as $r) {
                        $photoId = (string)($r->photo_id ?? '');
                        if ($photoId) {
                            if (!isset($reactionsByPhoto[$photoId])) {
                                $reactionsByPhoto[$photoId] = [];
                            }
                            $reactionsByPhoto[$photoId][] = [
                                'reaction' => (string)($r->reaction ?? ''),
                                'count' => (int)($r->count ?? 0)
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error fetching reactions in reports: ' . $e->getMessage());
            }
            
            // Get downloads - with full error handling
            $downloadsByPhoto = [];
            try {
                if (class_exists(DownloadLog::class) && Schema::hasTable('download_logs')) {
                    $downloadsData = DownloadLog::select('photo_id', DB::raw('COUNT(*) as count'))
                        ->groupBy('photo_id')
                        ->get();
                    
                    foreach ($downloadsData as $d) {
                        $photoId = (string)($d->photo_id ?? '');
                        if ($photoId) {
                            $downloadsByPhoto[$photoId] = (int)($d->count ?? 0);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error fetching downloads in reports: ' . $e->getMessage());
            }
            
            // Process photos
            if ($photos && $photos->count() > 0) {
                foreach ($photos as $photo) {
                    try {
                        if (!$photo || !$photo->id) {
                            continue;
                        }
                        
                        $photoId = (string)$photo->id;
                        
                        $photoReactions = $reactionsByPhoto[$photoId] ?? [];
                        $likes = 0;
                        $dislikes = 0;
                        
                        foreach ($photoReactions as $r) {
                            $reaction = $r['reaction'] ?? '';
                            $count = (int)($r['count'] ?? 0);
                            if ($reaction === 'like') {
                                $likes += $count;
                            } elseif ($reaction === 'dislike') {
                                $dislikes += $count;
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
                    } catch (\Throwable $e) {
                        \Log::error('Error processing photo stats: ' . $e->getMessage());
                        continue;
                    }
                }
            }
            
            // Sort safely
            try {
        usort($photoReports, function($a, $b) {
                    $totalA = (int)(($a['stats']['likes'] ?? 0) + ($a['stats']['dislikes'] ?? 0) + ($a['stats']['downloads'] ?? 0));
                    $totalB = (int)(($b['stats']['likes'] ?? 0) + ($b['stats']['dislikes'] ?? 0) + ($b['stats']['downloads'] ?? 0));
            return $totalB - $totalA;
        });
            } catch (\Throwable $e) {
                \Log::error('Error sorting photo reports: ' . $e->getMessage());
            }
            
        } catch (\Throwable $e) {
            \Log::error('Reports index fatal error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
        
        // Always return view with safe data
        return view('admin.reports.index', [
            'totalUsers' => (int)($totalUsers ?? 0),
            'recentUsers' => $recentUsers ?? collect(),
            'photoReports' => $photoReports ?? []
        ]);
    }
    
    /**
     * Show detailed user report
     */
    public function users()
    {
        try {
        $users = User::orderBy('created_at', 'desc')->get();
        } catch (\Throwable $e) {
            \Log::error('Reports users error: ' . $e->getMessage());
            $users = collect();
        }
        
        return view('admin.reports.users', [
            'users' => $users ?? collect()
        ]);
    }
    
    public function editUser($id)
    {
        try {
        $user = User::findOrFail($id);
        } catch (\Throwable $e) {
            \Log::error('Reports editUser error: ' . $e->getMessage());
            return redirect()->route('admin.reports.users')->with('error', 'User tidak ditemukan.');
        }

        return view('admin.reports.users-edit', [
            'user' => $user,
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        try {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$user->id],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('admin.reports.users')->with('status', 'Pengguna berhasil diperbarui.');
        } catch (\Throwable $e) {
            \Log::error('Reports updateUser error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage());
        }
    }

    public function destroyUser($id)
    {
        try {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.reports.users')->with('status', 'Pengguna berhasil dihapus.');
        } catch (\Throwable $e) {
            \Log::error('Reports destroyUser error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
        }
    }
    
    /**
     * Show detailed photo report
     */
    public function photos()
    {
        $photoReports = [];
        
        try {
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get() ?: collect();
            
            // Get reactions
            $reactionsByPhoto = [];
            try {
                if (Schema::hasTable('photo_reactions')) {
                    $reactionsData = PhotoReaction::select('photo_id', 'reaction', DB::raw('COUNT(*) as count'))
                        ->groupBy('photo_id', 'reaction')
                        ->get();
                    
                    foreach ($reactionsData as $r) {
                        $photoId = (string)($r->photo_id ?? '');
                        if ($photoId) {
                            if (!isset($reactionsByPhoto[$photoId])) {
                                $reactionsByPhoto[$photoId] = [];
                            }
                            $reactionsByPhoto[$photoId][] = [
                                'reaction' => (string)($r->reaction ?? ''),
                                'count' => (int)($r->count ?? 0)
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error fetching reactions: ' . $e->getMessage());
            }
            
            // Get downloads
            $downloadsByPhoto = [];
            try {
                if (Schema::hasTable('download_logs')) {
                    $downloadsData = DownloadLog::select('photo_id', DB::raw('COUNT(*) as count'))
                        ->groupBy('photo_id')
                        ->get();
                    
                    foreach ($downloadsData as $d) {
                        $photoId = (string)($d->photo_id ?? '');
                        if ($photoId) {
                            $downloadsByPhoto[$photoId] = (int)($d->count ?? 0);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error fetching downloads: ' . $e->getMessage());
            }
            
            foreach ($allPhotos as $photo) {
                try {
                    if (!$photo || !$photo->id) continue;
                    
                    $photoId = (string)$photo->id;
                    
                    $photoReactions = $reactionsByPhoto[$photoId] ?? [];
                    $likes = 0;
                    $dislikes = 0;
                    foreach ($photoReactions as $r) {
                        if (($r['reaction'] ?? '') === 'like') {
                            $likes += (int)($r['count'] ?? 0);
                        } elseif (($r['reaction'] ?? '') === 'dislike') {
                            $dislikes += (int)($r['count'] ?? 0);
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
                } catch (\Throwable $e) {
                    \Log::error('Error processing photo: ' . $e->getMessage());
                    continue;
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Reports photos error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
        
        return view('admin.reports.photos', [
            'photoReports' => $photoReports
        ]);
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
                return back()->with('error', 'PDF library tidak tersedia.');
            }
        } catch (\Throwable $e) {
            \Log::error('Export users PDF error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor PDF.');
        }
    }
    
    /**
     * Export photo report to PDF
     */
    public function exportPhotosPdf()
    {
        try {
        $photoReports = [];
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get() ?: collect();
        
        foreach ($allPhotos as $photo) {
                try {
                    if (!$photo || !$photo->id) continue;
                    
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
                            'likes' => (int)$likes,
                            'dislikes' => (int)$dislikes,
                            'downloads' => (int)$downloads
                        ]
                    ];
                } catch (\Throwable $e) {
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
                return back()->with('error', 'PDF library tidak tersedia.');
            }
        } catch (\Throwable $e) {
            \Log::error('Export photos PDF error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor PDF.');
        }
    }
}
