<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GalleryItem;
use App\Models\PhotoReaction;
use App\Models\DownloadLog;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Initialize all variables with safe defaults
        $totalUsers = 0;
        $totalPhotos = 0;
        $totalLikes = 0;
        $totalDislikes = 0;
        $totalDownloads = 0;
        $recentUsers = collect();
        $photoReports = [];
        
        try {
            // Get user statistics
            try {
                $totalUsers = (int) User::count();
            } catch (\Exception $e) {
                $totalUsers = 0;
            }
            
            try {
                $recentUsers = User::orderBy('created_at', 'desc')->take(10)->get();
            } catch (\Exception $e) {
                $recentUsers = collect();
            }
            
            // Get total photos count directly from database
            try {
                $totalPhotos = (int) GalleryItem::whereNotNull('filename')->count();
            } catch (\Exception $e) {
                $totalPhotos = 0;
            }
            
            // Get total likes, dislikes, and downloads directly from database
            try {
                $totalLikes = (int) PhotoReaction::where('reaction', 'like')->count();
            } catch (\Exception $e) {
                $totalLikes = 0;
            }
            
            try {
                $totalDislikes = (int) PhotoReaction::where('reaction', 'dislike')->count();
            } catch (\Exception $e) {
                $totalDislikes = 0;
            }
            
            try {
                $totalDownloads = (int) DownloadLog::count();
            } catch (\Exception $e) {
                $totalDownloads = 0;
            }
            
            // Get photos for detailed reports
            $photos = collect();
            try {
                $photos = GalleryItem::whereNotNull('filename')->limit(100)->get();
            } catch (\Exception $e) {
                $photos = collect();
            }
            
            // Get reactions - count directly for each photo
            $reactionsByPhoto = [];
            try {
        foreach ($photos as $photo) {
                    if (!$photo || !isset($photo->id)) continue;
                    $photoId = (string)$photo->id;
                    
                    try {
                        $likes = PhotoReaction::where('photo_id', $photoId)
                ->where('reaction', 'like')
                ->count();
                        $dislikes = PhotoReaction::where('photo_id', $photoId)
                ->where('reaction', 'dislike')
                ->count();
            
                        $reactionsByPhoto[$photoId] = [
                            'like' => (int)$likes,
                            'dislike' => (int)$dislikes
                        ];
                    } catch (\Exception $e) {
                        $reactionsByPhoto[$photoId] = ['like' => 0, 'dislike' => 0];
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
            
            // Get downloads - count directly for each photo
            $downloadsByPhoto = [];
            try {
                foreach ($photos as $photo) {
                    if (!$photo || !isset($photo->id)) continue;
                    $photoId = (string)$photo->id;
                    
                    try {
                        $downloads = DownloadLog::where('photo_id', $photoId)->count();
                        $downloadsByPhoto[$photoId] = (int)$downloads;
                    } catch (\Exception $e) {
                        $downloadsByPhoto[$photoId] = 0;
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
            
            // Process photos
            foreach ($photos as $photo) {
                try {
                    if (!$photo || !isset($photo->id)) {
                        continue;
                    }
                    
                    $photoId = (string)$photo->id;
                    
                    $likes = (int)($reactionsByPhoto[$photoId]['like'] ?? 0);
                    $dislikes = (int)($reactionsByPhoto[$photoId]['dislike'] ?? 0);
                    $downloads = (int)($downloadsByPhoto[$photoId] ?? 0);
            
            $photoReports[] = [
                'photo' => $photo,
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
            try {
        usort($photoReports, function($a, $b) {
                    $totalA = (int)(($a['stats']['likes'] ?? 0) + ($a['stats']['dislikes'] ?? 0) + ($a['stats']['downloads'] ?? 0));
                    $totalB = (int)(($b['stats']['likes'] ?? 0) + ($b['stats']['dislikes'] ?? 0) + ($b['stats']['downloads'] ?? 0));
            return $totalB - $totalA;
        });
            } catch (\Exception $e) {
                // Ignore sort errors
            }
            
        } catch (\Exception $e) {
            // Fallback to empty data
            $totalUsers = 0;
            $recentUsers = collect();
            $photoReports = [];
        }
        
        return view('admin.reports.index', [
            'totalUsers' => $totalUsers,
            'totalPhotos' => $totalPhotos,
            'totalLikes' => $totalLikes,
            'totalDislikes' => $totalDislikes,
            'totalDownloads' => $totalDownloads,
            'recentUsers' => $recentUsers,
            'photoReports' => $photoReports
        ]);
    }
    
    public function users()
    {
        try {
        $users = User::orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $users = collect();
        }
        
        return view('admin.reports.users', [
            'users' => $users
        ]);
    }
    
    public function editUser($id)
    {
        try {
        $user = User::findOrFail($id);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui pengguna.');
        }
    }

    public function destroyUser($id)
    {
        try {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.reports.users')->with('status', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pengguna.');
        }
    }
    
    public function photos()
    {
        $photoReports = [];
        
        try {
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get();
        
        foreach ($allPhotos as $photo) {
                try {
                    if (!$photo || !isset($photo->id)) continue;
                    
                    $photoId = (string)$photo->id;
                    
                    try {
                        $likes = PhotoReaction::where('photo_id', $photoId)->where('reaction', 'like')->count();
                        $dislikes = PhotoReaction::where('photo_id', $photoId)->where('reaction', 'dislike')->count();
                        $downloads = DownloadLog::where('photo_id', $photoId)->count();
                    } catch (\Exception $e) {
                        $likes = 0;
                        $dislikes = 0;
                        $downloads = 0;
                    }
            
            $photoReports[] = [
                'photo' => $photo,
                'stats' => [
                            'likes' => (int)$likes,
                            'dislikes' => (int)$dislikes,
                            'downloads' => (int)$downloads
                ]
            ];
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            // Fallback to empty
        }
        
        return view('admin.reports.photos', [
            'photoReports' => $photoReports
        ]);
    }
    
    public function exportUsersPdf()
    {
        try {
        $users = User::orderBy('created_at', 'desc')->get();
        
            if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf.users', [
            'users' => $users,
            'generatedAt' => now()->format('d F Y H:i')
        ]);
        
        return $pdf->download('laporan-pengguna-' . date('Y-m-d') . '.pdf');
            } else {
                return back()->with('error', 'PDF library tidak tersedia.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor PDF.');
        }
    }
    
    public function exportPhotosPdf()
    {
        try {
        $photoReports = [];
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get();
        
        foreach ($allPhotos as $photo) {
                try {
                    if (!$photo || !isset($photo->id)) continue;
            
                    $photoId = (string)$photo->id;
            
                    $likes = PhotoReaction::where('photo_id', $photoId)->where('reaction', 'like')->count();
                    $dislikes = PhotoReaction::where('photo_id', $photoId)->where('reaction', 'dislike')->count();
                    $downloads = DownloadLog::where('photo_id', $photoId)->count();
            
            $photoReports[] = [
                'photo' => $photo,
                'stats' => [
                            'likes' => (int)$likes,
                            'dislikes' => (int)$dislikes,
                            'downloads' => (int)$downloads
                ]
            ];
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf.photos', [
            'photoReports' => $photoReports,
            'generatedAt' => now()->format('d F Y H:i')
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan-foto-galeri-' . date('Y-m-d') . '.pdf');
            } else {
                return back()->with('error', 'PDF library tidak tersedia.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor PDF.');
        }
    }
}
