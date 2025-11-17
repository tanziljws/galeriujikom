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
        // Get user statistics
        $totalUsers = User::count();
        $recentUsers = User::orderBy('created_at', 'desc')->take(10)->get();
        
        // Get photo statistics from database
        $photos = GalleryItem::all();
        $photoReports = [];
        
        foreach ($photos as $photo) {
            $likes = PhotoReaction::where('photo_id', $photo->id)
                ->where('reaction', 'like')
                ->count();
            
            $dislikes = PhotoReaction::where('photo_id', $photo->id)
                ->where('reaction', 'dislike')
                ->count();
            
            $downloads = DownloadLog::where('photo_id', $photo->id)->count();
            
            $photoReports[] = [
                'photo' => $photo,
                'stats' => [
                    'likes' => $likes,
                    'dislikes' => $dislikes,
                    'downloads' => $downloads
                ]
            ];
        }
        
        // Sort by total interactions (likes + dislikes + downloads)
        usort($photoReports, function($a, $b) {
            $totalA = $a['stats']['likes'] + $a['stats']['dislikes'] + $a['stats']['downloads'];
            $totalB = $b['stats']['likes'] + $b['stats']['dislikes'] + $b['stats']['downloads'];
            return $totalB - $totalA;
        });
        
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
        // Get all photos with stats from database
        $photoReports = [];
        $allPhotos = GalleryItem::orderBy('created_at', 'desc')->get();
        
        foreach ($allPhotos as $photo) {
            $likes = PhotoReaction::where('photo_id', $photo->id)
                ->where('reaction', 'like')
                ->count();
            
            $dislikes = PhotoReaction::where('photo_id', $photo->id)
                ->where('reaction', 'dislike')
                ->count();
            
            $downloads = DownloadLog::where('photo_id', $photo->id)->count();
            
            $photoReports[] = [
                'photo' => $photo,
                'stats' => [
                    'likes' => $likes,
                    'dislikes' => $dislikes,
                    'downloads' => $downloads
                ]
            ];
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
        $users = User::orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('admin.reports.pdf.users', [
            'users' => $users,
            'generatedAt' => now()->format('d F Y H:i')
        ]);
        
        return $pdf->download('laporan-pengguna-' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * Export photo report to PDF
     */
    public function exportPhotosPdf()
    {
        // Get photos with stats from database
        $photoReports = [];
        $allPhotos = GalleryItem::orderBy('created_at', 'desc')->get();
        
        foreach ($allPhotos as $photo) {
            $likes = PhotoReaction::where('photo_id', $photo->id)
                ->where('reaction', 'like')
                ->count();
            
            $dislikes = PhotoReaction::where('photo_id', $photo->id)
                ->where('reaction', 'dislike')
                ->count();
            
            $downloads = DownloadLog::where('photo_id', $photo->id)->count();
            
            $photoReports[] = [
                'photo' => $photo,
                'stats' => [
                    'likes' => $likes,
                    'dislikes' => $dislikes,
                    'downloads' => $downloads
                ]
            ];
        }
        
        $pdf = Pdf::loadView('admin.reports.pdf.photos', [
            'photoReports' => $photoReports,
            'generatedAt' => now()->format('d F Y H:i')
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan-foto-galeri-' . date('Y-m-d') . '.pdf');
    }
}
