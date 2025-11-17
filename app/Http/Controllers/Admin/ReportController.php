<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GalleryItem;
use App\Models\PhotoReaction;
use App\Models\DownloadLog;
use Illuminate\Support\Facades\DB;

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
            // Get user statistics - simple and safe
            try {
                $totalUsers = (int) User::count();
            } catch (\Exception $e) {
                $totalUsers = 0;
            }
            
            try {
                $recentUsers = User::orderBy('created_at', 'desc')->take(10)->get();
                if (!$recentUsers) {
                    $recentUsers = collect();
                }
            } catch (\Exception $e) {
                $recentUsers = collect();
            }
            
            // Get photos - simple and safe
            $photos = collect();
            try {
                $photos = GalleryItem::whereNotNull('filename')->limit(100)->get();
                if (!$photos) {
                    $photos = collect();
                }
            } catch (\Exception $e) {
                $photos = collect();
            }
            
            // Get reactions - simplified query with limit
            $reactionsByPhoto = [];
            try {
                $reactions = PhotoReaction::limit(1000)->get();
                foreach ($reactions as $r) {
                    $photoId = (string)($r->photo_id ?? '');
                    if ($photoId) {
                        if (!isset($reactionsByPhoto[$photoId])) {
                            $reactionsByPhoto[$photoId] = ['like' => 0, 'dislike' => 0];
                        }
                        $reaction = $r->reaction ?? '';
                        if ($reaction === 'like') {
                            $reactionsByPhoto[$photoId]['like']++;
                        } elseif ($reaction === 'dislike') {
                            $reactionsByPhoto[$photoId]['dislike']++;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
            
            // Get downloads - simplified query with limit
            $downloadsByPhoto = [];
            try {
                $downloads = DownloadLog::limit(1000)->get();
                foreach ($downloads as $d) {
                    $photoId = (string)($d->photo_id ?? '');
                    if ($photoId) {
                        $downloadsByPhoto[$photoId] = ($downloadsByPhoto[$photoId] ?? 0) + 1;
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
            
            // Process photos
            if ($photos->isNotEmpty()) {
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
            }
            
            // Sort
            usort($photoReports, function($a, $b) {
                $totalA = (int)(($a['stats']['likes'] ?? 0) + ($a['stats']['dislikes'] ?? 0) + ($a['stats']['downloads'] ?? 0));
                $totalB = (int)(($b['stats']['likes'] ?? 0) + ($b['stats']['dislikes'] ?? 0) + ($b['stats']['downloads'] ?? 0));
                return $totalB - $totalA;
            });
            
        } catch (\Throwable $e) {
            // Fallback to empty data on any error
            $totalUsers = 0;
            $recentUsers = collect();
            $photoReports = [];
        }
        
        // Always return view with safe data
        return view('admin.reports.index', [
            'totalUsers' => $totalUsers,
            'recentUsers' => $recentUsers,
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
    
    /**
     * Show detailed photo report
     */
    public function photos()
    {
        $photoReports = [];
        
        try {
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get();
            
            // Get reactions - simplified query
            $reactionsByPhoto = [];
            try {
                $reactions = PhotoReaction::limit(1000)->get();
                foreach ($reactions as $r) {
                    $photoId = (string)($r->photo_id ?? '');
                    if ($photoId) {
                        if (!isset($reactionsByPhoto[$photoId])) {
                            $reactionsByPhoto[$photoId] = ['like' => 0, 'dislike' => 0];
                        }
                        $reaction = $r->reaction ?? '';
                        if ($reaction === 'like') {
                            $reactionsByPhoto[$photoId]['like']++;
                        } elseif ($reaction === 'dislike') {
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
                $downloads = DownloadLog::limit(1000)->get();
                foreach ($downloads as $d) {
                    $photoId = (string)($d->photo_id ?? '');
                    if ($photoId) {
                        $downloadsByPhoto[$photoId] = ($downloadsByPhoto[$photoId] ?? 0) + 1;
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
            
            foreach ($allPhotos as $photo) {
                try {
                    if (!$photo || !isset($photo->id)) continue;
                    
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
        } catch (\Exception $e) {
            // Fallback to empty
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
    
    /**
     * Export photo report to PDF
     */
    public function exportPhotosPdf()
    {
        try {
            $photoReports = [];
            $allPhotos = GalleryItem::whereNotNull('filename')->orderBy('created_at', 'desc')->get();
            
            foreach ($allPhotos as $photo) {
                try {
                    if (!$photo || !isset($photo->id)) continue;
                    
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
