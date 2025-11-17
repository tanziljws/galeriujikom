<?php

/**
 * Script Cek Data di Database
 * Jalankan dengan: php check-database.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PhotoReaction;
use App\Models\DownloadLog;
use App\Models\GalleryItem;
use App\Models\User;

echo "🔍 CEK STATUS DATABASE\n";
echo "================================================\n\n";

// Statistik Umum
echo "📊 STATISTIK UMUM:\n";
echo "   - Total Users: " . User::count() . "\n";
echo "   - Total Photos: " . GalleryItem::count() . "\n";
echo "   - Total Reactions: " . PhotoReaction::count() . "\n";
echo "   - Total Downloads: " . DownloadLog::count() . "\n";
echo "\n";

// Detail Reactions
echo "💝 DETAIL REACTIONS:\n";
$totalLikes = PhotoReaction::where('reaction', 'like')->count();
$totalDislikes = PhotoReaction::where('reaction', 'dislike')->count();
echo "   - Total Likes: {$totalLikes}\n";
echo "   - Total Dislikes: {$totalDislikes}\n";
echo "\n";

// Sample Reactions
echo "📋 SAMPLE REACTIONS (5 terakhir):\n";
$reactions = PhotoReaction::with('user')->latest()->take(5)->get();
foreach ($reactions as $reaction) {
    $userName = $reaction->user ? $reaction->user->name : 'Unknown';
    echo "   - Photo ID: {$reaction->photo_id} | User: {$userName} | Reaction: {$reaction->reaction} | Date: {$reaction->created_at}\n";
}
echo "\n";

// Sample Downloads
echo "📥 SAMPLE DOWNLOADS (5 terakhir):\n";
$downloads = DownloadLog::with('user')->latest()->take(5)->get();
if ($downloads->count() > 0) {
    foreach ($downloads as $download) {
        $userName = $download->user ? $download->user->name : 'Guest';
        echo "   - Photo ID: {$download->photo_id} | User: {$userName} | IP: {$download->ip} | Date: {$download->created_at}\n";
    }
} else {
    echo "   - Belum ada data download\n";
}
echo "\n";

// Foto dengan interaksi terbanyak
echo "🏆 TOP 5 FOTO DENGAN INTERAKSI TERBANYAK:\n";
$photoStats = PhotoReaction::select('photo_id')
    ->selectRaw('COUNT(*) as total_reactions')
    ->selectRaw('SUM(CASE WHEN reaction = "like" THEN 1 ELSE 0 END) as likes')
    ->selectRaw('SUM(CASE WHEN reaction = "dislike" THEN 1 ELSE 0 END) as dislikes')
    ->groupBy('photo_id')
    ->orderByDesc('total_reactions')
    ->take(5)
    ->get();

foreach ($photoStats as $stat) {
    $downloads = DownloadLog::where('photo_id', $stat->photo_id)->count();
    echo "   - Photo ID: {$stat->photo_id}\n";
    echo "     Likes: {$stat->likes} | Dislikes: {$stat->dislikes} | Downloads: {$downloads}\n";
}

echo "\n";
echo "================================================\n";
echo "✅ CEK DATABASE SELESAI!\n";
