<?php

/**
 * Script Migrasi Data dari JSON ke Database
 * Jalankan dengan: php migrate-json-to-db.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PhotoReaction;
use App\Models\DownloadLog;
use App\Models\GalleryItem;
use App\Models\User;

echo "🚀 Memulai Migrasi Data dari JSON ke Database...\n";
echo "================================================\n\n";

// ============================================
// MIGRASI REACTIONS
// ============================================
echo "📊 Migrasi Reactions (Like/Dislike)...\n";

$reactionsPath = public_path('uploads/gallery/reactions.json');

if (!file_exists($reactionsPath)) {
    echo "⚠️  File reactions.json tidak ditemukan.\n";
} else {
    $reactionsData = json_decode(file_get_contents($reactionsPath), true);
    
    if (empty($reactionsData)) {
        echo "⚠️  File reactions.json kosong.\n";
    } else {
        $count = 0;
        $skipped = 0;
        
        // Cari atau buat user default untuk data lama
        $defaultUser = User::first();
        if (!$defaultUser) {
            echo "❌ Tidak ada user di database. Buat user terlebih dahulu.\n";
        } else {
            foreach ($reactionsData as $item) {
                $filename = $item['filename'] ?? null;
                
                if (!$filename) {
                    $skipped++;
                    continue;
                }
                
                // Cari photo berdasarkan filename
                $photo = GalleryItem::where('filename', $filename)->first();
                
                // Jika tidak ditemukan, coba cari dengan LIKE
                if (!$photo) {
                    $photo = GalleryItem::where('filename', 'LIKE', '%' . basename($filename) . '%')->first();
                }
                
                // Jika masih tidak ditemukan, gunakan filename sebagai photo_id langsung
                if (!$photo) {
                    // Buat dummy photo_id dari filename
                    $photoId = $filename;
                    echo "⚠️  Foto tidak ditemukan di DB, gunakan filename sebagai ID: {$filename}\n";
                } else {
                    $photoId = $photo->id;
                }
                
                // Process likes
                $likes = $item['likes'] ?? 0;
                for ($i = 0; $i < $likes; $i++) {
                    try {
                        // Cek apakah sudah ada
                        $exists = PhotoReaction::where('photo_id', $photoId)
                            ->where('user_id', $defaultUser->id)
                            ->where('reaction', 'like')
                            ->exists();
                        
                        if (!$exists) {
                            PhotoReaction::create([
                                'photo_id' => $photoId,
                                'user_id' => $defaultUser->id,
                                'reaction' => 'like',
                                'created_at' => $item['updated_at'] ?? now(),
                                'updated_at' => $item['updated_at'] ?? now(),
                            ]);
                            $count++;
                        }
                    } catch (\Exception $e) {
                        echo "❌ Error: {$e->getMessage()}\n";
                    }
                }
                
                // Process dislikes
                $dislikes = $item['dislikes'] ?? 0;
                for ($i = 0; $i < $dislikes; $i++) {
                    try {
                        PhotoReaction::create([
                            'photo_id' => $photoId,
                            'user_id' => $defaultUser->id,
                            'reaction' => 'dislike',
                            'created_at' => $item['updated_at'] ?? now(),
                            'updated_at' => $item['updated_at'] ?? now(),
                        ]);
                        $count++;
                    } catch (\Exception $e) {
                        // Skip duplicate
                    }
                }
            }
            
            echo "✅ Berhasil migrasi {$count} reactions\n";
            echo "⚠️  Dilewati: {$skipped} item\n";
            
            // Backup file JSON
            $backupPath = public_path('uploads/gallery/reactions.json.backup');
            copy($reactionsPath, $backupPath);
            echo "💾 Backup dibuat: reactions.json.backup\n";
        }
    }
}

echo "\n";

// ============================================
// MIGRASI DOWNLOADS (jika ada)
// ============================================
echo "📥 Migrasi Download Logs...\n";

$downloadsPath = public_path('uploads/gallery/downloads.json');

if (!file_exists($downloadsPath)) {
    echo "⚠️  File downloads.json tidak ditemukan. Skip.\n";
} else {
    $downloadsData = json_decode(file_get_contents($downloadsPath), true);
    
    if (empty($downloadsData)) {
        echo "⚠️  File downloads.json kosong.\n";
    } else {
        $count = 0;
        
        foreach ($downloadsData as $item) {
            try {
                DownloadLog::create([
                    'user_id' => $item['user_id'] ?? null,
                    'photo_id' => $item['photo_id'] ?? $item['filename'] ?? null,
                    'url' => $item['url'] ?? null,
                    'filename' => $item['filename'] ?? null,
                    'ip' => $item['ip'] ?? null,
                    'created_at' => $item['created_at'] ?? now(),
                    'updated_at' => $item['updated_at'] ?? now(),
                ]);
                $count++;
            } catch (\Exception $e) {
                echo "❌ Error: {$e->getMessage()}\n";
            }
        }
        
        echo "✅ Berhasil migrasi {$count} download logs\n";
        
        // Backup file JSON
        $backupPath = public_path('uploads/gallery/downloads.json.backup');
        copy($downloadsPath, $backupPath);
        echo "💾 Backup dibuat: downloads.json.backup\n";
    }
}

echo "\n";
echo "================================================\n";
echo "✅ MIGRASI SELESAI!\n\n";

// Tampilkan statistik
$totalReactions = PhotoReaction::count();
$totalDownloads = DownloadLog::count();
$totalLikes = PhotoReaction::where('reaction', 'like')->count();
$totalDislikes = PhotoReaction::where('reaction', 'dislike')->count();

echo "📊 STATISTIK DATABASE:\n";
echo "   - Total Reactions: {$totalReactions}\n";
echo "   - Total Likes: {$totalLikes}\n";
echo "   - Total Dislikes: {$totalDislikes}\n";
echo "   - Total Downloads: {$totalDownloads}\n";
echo "\n";
echo "🎉 Data berhasil dipindahkan ke database!\n";
echo "📁 File JSON asli sudah dibackup dengan ekstensi .backup\n";
