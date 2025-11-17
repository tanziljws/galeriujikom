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
        $recentUsers = [];
        $photoReports = [];
        
        try {
            // Get user statistics
            try {
                $totalUsers = User::count();
            } catch (\Exception $e) {
                $totalUsers = 0;
            }
            
            try {
                $recentUsers = User::orderBy('created_at', 'desc')->take(10)->get()->toArray();
            } catch (\Exception $e) {
                $recentUsers = [];
            }
            
            // Get photos
            $photos = [];
            try {
                $photos = GalleryItem::whereNotNull('filename')->limit(100)->get()->toArray();
            } catch (\Exception $e) {
                $photos = [];
            }
            
            // Get reactions - simplified query
            $reactionsByPhoto = [];
            try {
                $reactions = PhotoReaction::all();
                foreach ($reactions as $r) {
                    $photoId = (string)($r->photo_id ?? '');
                    if ($photoId) {
                        if (!isset($reactionsByPhoto[$photoId])) {
                            $reactionsByPhoto[$photoId] = ['like' => 0, 'dislike' => 0];
                        }
                        if ($r->reaction === 'like') {
                            $reactionsByPhoto[$photoId]['like']++;
                        } elseif ($r->reaction === 'dislike') {
                            $reactionsByPhoto[$photoId]['dislike']++;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
            
            // Get downloads - simplified query
            $downloadsByPhoto = [];
            try {
                $downloads = DownloadLog::all();
                foreach ($downloads as $d) {
                    $photoId = (string)($d->photo_id ?? '');
                    if ($photoId) {
                        $downloadsByPhoto[$photoId] = ($downloadsByPhoto[$photoId] ?? 0) + 1;
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
            
            // Process photos
            foreach ($photos as $photo) {
                try {
                    $photoId = (string)($photo['id'] ?? $photo->id ?? '');
                    if (!$photoId) continue;
                    
                    $likes = (int)($reactionsByPhoto[$photoId]['like'] ?? 0);
                    $dislikes = (int)($reactionsByPhoto[$photoId]['dislike'] ?? 0);
                    $downloads = (int)($downloadsByPhoto[$photoId] ?? 0);
                    
                    $photoReports[] = [
                        'photo' => is_array($photo) ? (object)$photo : $photo,
                        'stats' => [
                            'likes' => $likes,
                            'dislikes' => $dislikes,
                            'downloads' => $downloads
                        ]
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Sort
            usort($photoReports, function($a, $b) {
                $totalA = (int)(($a['stats']['likes'] ?? 0) + ($a['stats']['dislikes'] ?? 0) + ($a['stats']['downloads'] ?? 0));
                $totalB = (int)(($b['stats']['likes'] ?? 0) + ($b['stats']['dislikes'] ?? 0) + ($b['stats']['downloads'] ?? 0));
                return $totalB - $totalA;
            });
            
        } catch (\Exception $e) {
            // Fallback to empty data
            $totalUsers = 0;
            $recentUsers = [];
            $photoReports = [];
        }
        
        // Convert recentUsers array back to collection for view compatibility
        $recentUsersCollection = collect($recentUsers);
        
        return view('admin.reports.index', [
            'totalUsers' => $totalUsers,
            'recentUsers' => $recentUsersCollection,
            'photoReports' => $photoReports
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
