<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\DownloadLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Models\DownloadLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Agenda;
use App\Models\Information;
use App\Models\GalleryItem;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/information', [InformationController::class, 'index'])->name('information');
Route::get('/information/{id}', [InformationController::class, 'show'])->name('information.show');
Route::post('/information', [InformationController::class, 'store'])->name('information.store');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/gallery/album/{title}', [GalleryController::class, 'showAlbum'])->name('gallery.album');
Route::get('/agenda', function () {
    // Public agenda list from DB, newest uploads first
    $items = Agenda::orderByDesc('created_at')->get()->map(function($ag){
        return [
            'id' => $ag->id,
            'title' => $ag->title,
            'date' => $ag->date,
            'place' => $ag->place,
            'description' => $ag->description,
            'created_at' => optional($ag->created_at)->toIso8601String(),
        ];
    })->toArray();
    return view('agenda', ['items' => $items]);
})->name('agenda');

Route::get('/agenda/{id}', function ($id) {
    $agenda = Agenda::findOrFail($id);
    return view('agenda-detail', ['agenda' => $agenda]);
})->name('agenda.show');

// Gallery interactions (public endpoints) - DB backed
Route::get('/gallery/photo-stats/{photoId}', [GalleryController::class, 'getPhotoStats'])->name('gallery.photo_stats');
Route::get('/gallery/comments/{photoId}', [GalleryController::class, 'getComments'])->name('gallery.comments');

// Gallery interactions - Require authentication (DB backed)
Route::post('/gallery/react', [GalleryController::class, 'reactToPhoto'])->middleware('auth')->name('gallery.react');
Route::post('/gallery/comment', [GalleryController::class, 'addComment'])->middleware('auth')->name('gallery.comment');

    // Gallery download
    Route::get('/gallery/download', function (Request $request) {
        $request->validate([
            'photo_id' => 'required|string',
            'filename' => 'required|string'
        ]);
        
        // Get the filename directly
        $filename = $request->input('filename');
        
        // Get file path (assuming photos are in public/uploads/gallery)
        $filePath = public_path('uploads/gallery/' . $filename);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }
        
        // Get file extension and set proper MIME type
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';
        
        // Log the download
        if (class_exists(DownloadLogController::class)) {
            try {
                app(DownloadLogController::class)->store($request);
            } catch (\Exception $e) {
                // Ignore log errors
            }
        }
        
        // Clean filename for download (remove prefix)
        $cleanFilename = preg_replace('/^img_[a-f0-9]+_/i', '', $filename);
        
        // Return file with proper headers
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $cleanFilename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('gallery.download');

    // Gallery download log -> controller (simpan ke DB)
    Route::post('/gallery/download-log', [DownloadLogController::class, 'store'])->name('gallery.download_log');

// Guru & Staf (public)
Route::get('/guru-staf', function () {
    // Scan all files then filter with case-insensitive image extension check
    $scanAndFilter = function (string $dir) {
        $paths = glob(public_path($dir . '/*')) ?: [];
        $files = [];
        foreach ($paths as $p) {
            if (is_file($p)) {
                $ext = pathinfo($p, PATHINFO_EXTENSION);
                if (!$ext || preg_match('/\.(jpe?g|png|webp|gif)$/i', $p)) {
                    $files[] = basename($p);
                }
            }
        }
        sort($files);
        return $files;
    };
    $guru = $scanAndFilter('images/guru');
    $staf = $scanAndFilter('images/staf');
    $kepala = $scanAndFilter('images/kepala-sekolah');
    return view('guru-staf', compact('guru','staf','kepala'));
})->name('guru-staf');

// Admin Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Login form & submit
    Route::get('/login', [\App\Http\Controllers\Auth\AdminAuthController::class, 'showLoginForm'])
        ->middleware('guest:petugas')->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AdminAuthController::class, 'login'])
        ->middleware('guest:petugas')->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Auth\AdminAuthController::class, 'logout'])
        ->middleware('auth:petugas')->name('logout');
});

// Admin routes (protected)
Route::prefix('admin')->name('admin.')->middleware('auth:petugas')->group(function () {
    // Root admin menuju Dashboard
    Route::get('/', function(){
        return redirect()->route('admin.dashboard');
    });

    // Dashboard ringkasan konten
    Route::get('/dashboard', function(){
        // Hitung jumlah dari DB
        $countInfo = \App\Models\Information::count();
        $countAgenda = \App\Models\Agenda::count();
        $countGallery = \App\Models\GalleryItem::count();

        // Informasi terbaru (5) dari DB
        $latestInfo = \App\Models\Information::orderByDesc('created_at')->limit(5)->get()
            ->map(function($it){
                return [
                    'id' => $it->id,
                    'title' => $it->title,
                    'description' => $it->description,
                    'date' => $it->date,
                    'created_at' => optional($it->created_at)->toIso8601String(),
                ];
            })->toArray();

        // Agenda terbaru (5) dari DB
        $latestAgendas = \App\Models\Agenda::orderByDesc('created_at')->limit(5)->get()
            ->map(function($ag){
                return [
                    'id' => $ag->id,
                    'title' => $ag->title,
                    'date' => $ag->date,
                    'place' => $ag->place,
                    'description' => $ag->description,
                    'created_at' => optional($ag->created_at)->toIso8601String(),
                ];
            })->toArray();

        // Galeri terbaru (8) dari DB
        $latestGallery = \App\Models\GalleryItem::orderByDesc('created_at')->limit(8)->get()
            ->map(function($g){
                return [
                    'title' => $g->title,
                    'url' => $g->filename ? asset('uploads/gallery/'.$g->filename) : '',
                    'uploaded_at' => optional($g->created_at)->toIso8601String(),
                    'category' => $g->category ?? 'Lainnya',
                ];
            })->toArray();

        // Aktivitas 7 hari terakhir (informasi + agenda + galeri)
        $today = \Carbon\Carbon::today();
        $activityData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $infoCount = \App\Models\Information::whereDate('created_at', $date)->count();
            $agendaCount = \App\Models\Agenda::whereDate('created_at', $date)->count();
            $galleryCount = \App\Models\GalleryItem::whereDate('created_at', $date)->count();
            $activityData[] = $infoCount + $agendaCount + $galleryCount;
        }

        // Ringkasan Guru & Staf (scan 3 folder)
        $gsTypes = ['guru','staf','kepala-sekolah'];
        $gsItems = [];
        $gsCount = 0;
        foreach ($gsTypes as $t) {
            $dir = public_path('images/'.$t);
            $paths = glob($dir.'/*') ?: [];
            foreach ($paths as $p) {
                if (!is_file($p)) { continue; }
                $ext = pathinfo($p, PATHINFO_EXTENSION);
                if (!$ext || preg_match('/\.(jpe?g|png|webp|gif)$/i', $p)) {
                    $gsItems[] = [
                        'type' => $t,
                        'filename' => basename($p),
                        'url' => asset('images/'.$t.'/'.basename($p)),
                        'uploaded_at' => date('c', @filemtime($p) ?: time()),
                    ];
                    $gsCount++;
                }
            }
        }
        usort($gsItems, function($a,$b){ return strcmp($b['uploaded_at'], $a['uploaded_at']); });
        $latestGuruStaf = array_slice($gsItems, 0, 8);

        return view('admin.dashboard', [
            'countInfo' => $countInfo,
            'countAgenda' => $countAgenda,
            'countGallery' => $countGallery,
            'countGuruStaf' => $gsCount,
            'latestInfo' => $latestInfo,
            'latestAgendas' => $latestAgendas,
            'latestGallery' => $latestGallery,
            'latestGuruStaf' => $latestGuruStaf,
            'activityData' => $activityData,
        ]);
    })->name('dashboard');

    // Beranda (Kelola konten utama sederhana)
    Route::get('/home', function(){
        $path = storage_path('app/homepage.json');
        $data = [
            'title' => 'SMKN 4 Bogor',
            'subtitle' => 'Sekolah Unggul, Berkarakter, dan Berprestasi',
            'hero_text' => 'Selamat datang di portal resmi kami.'
        ];
        if (is_file($path)) {
            $loaded = json_decode(@file_get_contents($path), true) ?: [];
            $data = array_merge($data, $loaded);
        }
        return view('admin.home.index', ['data' => $data]);
    })->name('home');

    Route::post('/home', function (Request $request) {
        $validated = $request->validate([
            'title' => ['required','string','max:120'],
            'subtitle' => ['nullable','string','max:180'],
            'hero_text' => ['nullable','string','max:500'],
        ]);
        $path = storage_path('app/homepage.json');
        @file_put_contents($path, json_encode($validated, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        return back()->with('status','Konten beranda berhasil disimpan.');
    })->name('home.update');

    // Posts (Informasi) - CRUD via manifest.json
    Route::get('/posts', function () {
        // Admin list: only show items created by admin (exclude seeded examples)
        $dir = public_path('uploads/informations');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $items = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        if (!is_array($items)) { $items = []; }
        // keep only entries with created_by (uploaded from admin UI)
        $items = array_values(array_filter($items, function($it){ return isset($it['created_by']); }));

        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        // latest first
        usort($items, function($a,$b){ return strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''); });
        return view('admin.posts.index', ['items' => $items]);
    })->name('posts.index');

    Route::view('/posts/create', 'admin.posts.create')->name('posts.create');

    Route::get('/posts/{id}', function ($id) {
        $dir = public_path('uploads/informations');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $items = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $item = collect($items)->firstWhere('id', $id);
        // block seeded items without created_by
        abort_if(!$item || !isset($item['created_by']), 404);
        return view('admin.posts.show', compact('item'));
    })->name('posts.show');

    Route::get('/posts/{id}/edit', function ($id) {
        $dir = public_path('uploads/informations');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $items = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $item = collect($items)->firstWhere('id', $id);
        // block seeded items without created_by
        abort_if(!$item || !isset($item['created_by']), 404);
        return view('admin.posts.edit', compact('item'));
    })->name('posts.edit');

    Route::post('/posts', function (Request $request) {
        $validated = $request->validate([
            'title' => ['required','string','max:200'],
            'description' => ['required','string','max:500'],
            'content' => ['nullable','string'],
            'date' => ['required','date'],
            'category' => ['required','string','max:50'],
            'is_featured' => ['nullable','boolean'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,webp,gif','max:5120'],
        ]);
        $dir = public_path('uploads/informations');
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];

        $imageFilename = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $safe = preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $imageFilename = uniqid('info_') . '_' . $safe;
            $file->move($dir, $imageFilename);
        }
        $id = uniqid('info_');
        $item = [
            'id' => $id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'] ?? '',
            'date' => $validated['date'],
            'category' => $validated['category'],
            'image' => $imageFilename ? asset('uploads/informations/'.$imageFilename) : 'https://via.placeholder.com/800x450/7A9CC6/FFFFFF?text=Informasi',
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'created_at' => date('c'),
            'created_by' => Auth::guard('petugas')->user()->username ?? null,
        ];
        $manifest[] = $item;
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        return redirect()->route('admin.posts.index')->with('status','Informasi berhasil ditambahkan.');
    })->name('posts.store');

    Route::put('/posts/{id}', function (Request $request, $id) {
        $validated = $request->validate([
            'title' => ['required','string','max:200'],
            'description' => ['required','string','max:500'],
            'content' => ['nullable','string'],
            'date' => ['required','date'],
            'category' => ['required','string','max:50'],
            'is_featured' => ['nullable','boolean'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,webp,gif','max:5120'],
        ]);
        $dir = public_path('uploads/informations');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $index = null;
        foreach ($manifest as $i => $it) { if (($it['id'] ?? '') === $id) { $index = $i; break; } }
        abort_if($index === null, 404);
        $current = $manifest[$index];
        // replace image if uploaded
        if ($request->hasFile('image')) {
            if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
            // delete old if it's a local file under our dir
            $old = $current['image'] ?? null;
            if ($old && str_starts_with($old, asset('uploads/informations/'))) {
                $oldPath = $dir . DIRECTORY_SEPARATOR . basename(parse_url($old, PHP_URL_PATH));
                if (is_file($oldPath)) { @unlink($oldPath); }
            }
            $file = $request->file('image');
            $safe = preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $imageFilename = uniqid('info_') . '_' . $safe;
            $file->move($dir, $imageFilename);
            $current['image'] = asset('uploads/informations/'.$imageFilename);
        }
        $current['title'] = $validated['title'];
        $current['description'] = $validated['description'];
        $current['content'] = $validated['content'] ?? '';
        $current['date'] = $validated['date'];
        $current['category'] = $validated['category'];
        $current['is_featured'] = (bool) ($validated['is_featured'] ?? false);
        $manifest[$index] = $current;
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        return redirect()->route('admin.posts.show', $id)->with('status','Informasi berhasil diperbarui.');
    })->name('posts.update');

    Route::delete('/posts/{id}', function ($id) {
        $dir = public_path('uploads/informations');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $found = false;
        foreach ($manifest as $i => $it) {
            if (($it['id'] ?? '') === $id) {
                // try remove local image if ours
                $img = $it['image'] ?? null;
                if ($img && str_contains($img, '/uploads/informations/')) {
                    $basename = basename(parse_url($img, PHP_URL_PATH));
                    $path = $dir . DIRECTORY_SEPARATOR . $basename;
                    if (is_file($path)) { @unlink($path); }
                }
                array_splice($manifest, $i, 1);
                $found = true;
                break;
            }
        }
        if ($found) {
            file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        }
        return redirect()->route('admin.posts.index')->with('status', $found ? 'Informasi berhasil dihapus.' : 'Informasi tidak ditemukan.');
    })->name('posts.destroy');

    // Utility: Cleanup seeded informations (keep only admin-created entries)
    Route::get('/posts/cleanup', function () {
        $dir = public_path('uploads/informations');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        if (!is_array($manifest)) { $manifest = []; }
        // Keep only entries that have created_by (uploaded from Admin UI)
        $filtered = array_values(array_filter($manifest, function($it){ return isset($it['created_by']); }));
        // Optionally, delete local images of removed items if they were uploaded (seed uses external URLs)
        foreach ($manifest as $it) {
            if (!isset($it['created_by'])) {
                $img = $it['image'] ?? null;
                if ($img && str_contains($img, '/uploads/informations/')) {
                    $basename = basename(parse_url($img, PHP_URL_PATH));
                    $path = $dir . DIRECTORY_SEPARATOR . $basename;
                    if (is_file($path)) { @unlink($path); }
                }
            }
        }
        file_put_contents($manifestPath, json_encode($filtered, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        return redirect()->route('admin.posts.index')->with('status', 'Data informasi berhasil dibersihkan.');
    })->name('posts.cleanup');

    // Agendas (CRUD via Database)
    Route::get('/agendas', function () {
        $items = Agenda::orderByDesc('created_at')->get();
        return view('admin.agendas.index', ['items' => $items]);
    })->name('agendas.index');
    Route::view('/agendas/create', 'admin.agendas.create')->name('agendas.create');
    Route::get('/agendas/{id}', function ($id) {
        $item = Agenda::findOrFail($id);
        return view('admin.agendas.show', compact('item'));
    })->name('agendas.show');
    Route::get('/agendas/{id}/edit', function ($id) {
        $item = Agenda::findOrFail($id);
        return view('admin.agendas.edit', compact('item'));
    })->name('agendas.edit');
    Route::post('/agendas', function (Request $request) {
        $validated = $request->validate([
            'title' => ['required','string','max:150'],
            'date' => ['required','date'],
            'place' => ['nullable','string','max:150'],
            'description' => ['nullable','string','max:1000'],
        ]);
        Agenda::create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'place' => $validated['place'] ?? null,
            'description' => $validated['description'] ?? null,
            'created_by' => Auth::guard('petugas')->id(),
        ]);
        return redirect()->route('admin.agendas.index')->with('status','Agenda berhasil ditambahkan.');
    })->name('agendas.store');
    Route::put('/agendas/{id}', function (Request $request, $id) {
        $validated = $request->validate([
            'title' => ['required','string','max:150'],
            'date' => ['required','date'],
            'place' => ['nullable','string','max:150'],
            'description' => ['nullable','string','max:1000'],
        ]);
        $item = Agenda::findOrFail($id);
        $item->title = $validated['title'];
        $item->date = $validated['date'];
        $item->place = $validated['place'] ?? null;
        $item->description = $validated['description'] ?? null;
        $item->save();
        return redirect()->route('admin.agendas.index')->with('status','Agenda berhasil diperbarui.');
    })->name('agendas.update');
    Route::delete('/agendas/{id}', function ($id) {
        $item = Agenda::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.agendas.index')->with('status', 'Agenda berhasil dihapus.');
    })->name('agendas.destroy');
    // Informations (Admin) using DB - make sure specific routes come before the {id} catch-all delete
    Route::get('/informations', function(){
        $items = Information::orderByDesc('created_at')->get();
        return view('admin.informations.index', compact('items'));
    })->name('informations.index');

    Route::view('/informations/create', 'admin.informations.create')->name('informations.create');

    Route::post('/informations', function(Request $request){
        $validated = $request->validate([
            'title' => ['required','string','max:200'],
            'description' => ['required','string','max:500'],
            'content' => ['nullable','string'],
            'date' => ['nullable','date'],
            'category' => ['nullable','string','max:50'],
            'image_file' => ['nullable','image','mimes:jpeg,png,jpg,webp,gif'],
        ]);
        $dir = public_path('uploads/informations');
        if (!is_dir($dir)) { mkdir($dir,0755,true); }
        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $safe = preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $filename = uniqid('info_')."_".$safe;
            $file->move($dir, $filename);
            $imagePath = $filename;
        }
        Information::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'] ?? null,
            'date' => $validated['date'] ?? null,
            'category' => $validated['category'] ?? null,
            'image_path' => $imagePath,
            'created_by' => Auth::guard('petugas')->id(),
        ]);
        return redirect()->route('admin.informations.index')->with('status','Informasi berhasil ditambahkan.');
    })->name('informations.store');

    Route::get('/informations/{id}', function($id){
        $item = Information::findOrFail($id);
        return view('admin.informations.show', compact('item'));
    })->name('informations.show');

    Route::get('/informations/{id}/edit', function($id){
        $item = Information::findOrFail($id);
        return view('admin.informations.edit', compact('item'));
    })->name('informations.edit');

    Route::put('/informations/{id}', function(Request $request, $id){
        $validated = $request->validate([
            'title' => ['required','string','max:200'],
            'description' => ['required','string','max:500'],
            'content' => ['nullable','string'],
            'date' => ['nullable','date'],
            'category' => ['nullable','string','max:50'],
            'image_file' => ['nullable','image','mimes:jpeg,png,jpg,webp,gif'],
        ]);
        $dir = public_path('uploads/informations');
        $item = Information::findOrFail($id);
        if ($request->hasFile('image_file')) {
            if (!is_dir($dir)) { mkdir($dir,0755,true); }
            if ($item->image_path && !str_starts_with($item->image_path, 'http')) {
                $old = $dir . DIRECTORY_SEPARATOR . $item->image_path;
                if (is_file($old)) { @unlink($old); }
            }
            $file = $request->file('image_file');
            $safe = preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $filename = uniqid('info_')."_".$safe;
            $file->move($dir, $filename);
            $item->image_path = $filename;
        }
        $item->title = $validated['title'];
        $item->description = $validated['description'];
        $item->content = $validated['content'] ?? null;
        $item->date = $validated['date'] ?? null;
        $item->category = $validated['category'] ?? null;
        $item->save();
        return redirect()->route('admin.informations.index')->with('status','Informasi berhasil diperbarui.');
    })->name('informations.update');

    // Delete must come after other specific routes
    Route::delete('/informations/{id}', function($id){
        $dir = public_path('uploads/informations');
        $item = Information::findOrFail($id);
        if ($item->image_path && !str_starts_with($item->image_path, 'http')) {
            $path = $dir . DIRECTORY_SEPARATOR . $item->image_path;
            if (is_file($path)) { @unlink($path); }
        }
        $item->delete();
        return back()->with('status','Informasi dihapus.');
    })->name('informations.destroy');

    // One-time import from legacy JSON (admin/posts) to DB
    Route::post('/informations/import-legacy', function(){
        $dir = public_path('uploads/informations');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        if (!is_file($manifestPath)) {
            return back()->with('status','Tidak ditemukan file manifest.json legacy.');
        }
        $items = json_decode(file_get_contents($manifestPath), true) ?: [];
        $imported = 0;
        foreach ($items as $it) {
            // Only admin-created entries or all if not marked
            $title = $it['title'] ?? null;
            if (!$title) continue;
            $date = $it['date'] ?? null;
            $exists = Information::where('title',$title)->where('date',$date)->exists();
            if ($exists) continue;
            $img = $it['image'] ?? ($it['image_url'] ?? null);
            Information::create([
                'title' => $title,
                'description' => $it['description'] ?? null,
                'content' => $it['content'] ?? null,
                'date' => $date,
                'category' => $it['category'] ?? null,
                'image_path' => $img,
                'created_by' => Auth::guard('petugas')->id(),
            ]);
            $imported++;
        }
        return back()->with('status',"Import selesai: $imported data dimasukkan ke database.");
    })->name('informations.import_legacy');

    // Gallery (Admin) using DB
    Route::get('/gallery', function(){
        try {
        $items = \App\Models\GalleryItem::query()
                ->select(['title','category', \Illuminate\Support\Facades\DB::raw('MAX(created_at) as latest_at'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as photo_count')])
                ->whereNotNull('filename')
            ->groupBy('title','category')
            ->orderByDesc('latest_at')
            ->get();
        // Build array compatible with current admin view index
        $mapped = $items->map(function($g){
            // pick one thumbnail
                $thumb = \App\Models\GalleryItem::where('title',$g->title)->whereNotNull('filename')->latest('created_at')->first();
            $url = $thumb? ($thumb->filename ? asset('uploads/gallery/'.$thumb->filename) : '') : '';
            return [
                'filename' => urlencode($g->title), // used as identifier in routes
                'url' => $url,
                'category' => $g->category ?? 'Lainnya',
                'uploaded_at' => optional($g->latest_at)->toDateTimeString(),
                'photo_count' => (int)$g->photo_count,
                'title' => $g->title,
            ];
        })->toArray();
        return view('admin.gallery.index', ['items' => $mapped]);
        } catch (\Exception $e) {
            \Log::error('Admin gallery index error: ' . $e->getMessage());
            return view('admin.gallery.index', ['items' => []]);
        }
    })->name('gallery.index');

    Route::view('/gallery/create', 'admin.gallery.create')->name('gallery.create');

    Route::post('/gallery', function(Request $request){
        try {
        $validated = $request->validate([
            'title' => ['required','string','max:150'],
            'category' => ['nullable','string','max:100'],
            // Naikkan batas ukuran per foto menjadi 25MB agar tidak sering gagal
            'photos.*' => ['nullable','image','mimes:jpeg,png,jpg,webp,gif','max:25600'],
        ]);
        $title = $validated['title'];
        $category = $validated['category'] ?? null;
        $uploadedBy = Auth::guard('petugas')->id();

        $dir = public_path('uploads/gallery');
            if (!is_dir($dir)) { 
                mkdir($dir,0755,true); 
            }

        $files = $request->file('photos', []);
            if (empty($files) || count($files) === 0) {
                return back()->withErrors(['photos' => 'Silakan pilih minimal 1 foto'])->withInput();
            }
            
        if (count($files) > 15) {
            return back()->withErrors(['photos' => 'Maksimal 15 foto per album'])->withInput();
        }

            $uploadedCount = 0;
        foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;
                try {
            $safe = preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $filename = uniqid('gal_')."_".$safe;
            $file->move($dir, $filename);
            \App\Models\GalleryItem::create([
                'title' => $title,
                'category' => $category,
                'filename' => $filename,
                'uploaded_by' => $uploadedBy,
            ]);
                    $uploadedCount++;
                } catch (\Exception $e) {
                    \Log::error('Error uploading file: ' . $e->getMessage());
                    continue;
                }
            }
            
            if ($uploadedCount === 0) {
                return back()->withErrors(['photos' => 'Gagal mengupload foto. Pastikan file valid dan tidak melebihi 25MB per file.'])->withInput();
            }
            
            return redirect()->route('admin.gallery.index')->with('status','Album berhasil dibuat/ditambah foto. (' . $uploadedCount . ' foto)');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Gallery upload error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengupload foto: ' . $e->getMessage()])->withInput();
        }
    })->name('gallery.store');

    Route::get('/gallery/album/{title}', function($title){
        $albumTitle = urldecode($title);
        $photos = \App\Models\GalleryItem::where('title',$albumTitle)->orderByDesc('created_at')->get();
        return view('admin.gallery.album', compact('albumTitle','photos'));
    })->name('gallery.album.manage');

    Route::patch('/gallery/photo/{id}', function(Request $request, $id){
        $it = \App\Models\GalleryItem::findOrFail($id);
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'photo' => 'nullable|image|max:10240'
        ]);
        
        if ($request->hasFile('photo')) {
            // Delete old file
            if ($it->filename) {
                $oldPath = public_path('uploads/gallery/'.$it->filename);
                if (is_file($oldPath)) { @unlink($oldPath); }
            }
            // Upload new file
            $file = $request->file('photo');
            $filename = 'gal_' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/gallery'), $filename);
            $it->filename = $filename;
        }
        
        if ($request->filled('title')) {
            $it->title = $validated['title'];
        }
        if ($request->filled('category')) {
            $it->category = $validated['category'];
        }
        
        $it->save();
        return back()->with('status','Foto berhasil diperbarui.');
    })->name('gallery.photo.update');

    Route::delete('/gallery/photo/{id}', function($id){
        $it = \App\Models\GalleryItem::findOrFail($id);
        if ($it->filename) {
            $path = public_path('uploads/gallery/'.$it->filename);
            if (is_file($path)) { @unlink($path); }
        }
        $it->delete();
        return back()->with('status','Foto dihapus.');
    })->name('gallery.photo.destroy');

    // Gallery
    Route::get('/gallery', function () {
        $dir = public_path('uploads/gallery');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $items = [];
        if (is_file($manifestPath)) {
            $items = json_decode(file_get_contents($manifestPath), true) ?: [];
        }
        // latest first
        usort($items, function ($a, $b) { return strcmp($b['uploaded_at'] ?? '', $a['uploaded_at'] ?? ''); });
        return view('admin.gallery.index', ['items' => $items]);
    })->name('gallery.index');
    
    // Gallery Categories Management
    Route::prefix('gallery/categories')->name('gallery.categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\GalleryCategoryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\GalleryCategoryController::class, 'store'])->name('store');
        Route::put('/', [\App\Http\Controllers\Admin\GalleryCategoryController::class, 'update'])->name('rename');
        Route::delete('/', [\App\Http\Controllers\Admin\GalleryCategoryController::class, 'destroy'])->name('delete');
    });
    // Gallery reporting (DB-backed)
    Route::get('/gallery/report', function(){
        try {
            // Get all gallery items from DB
            $items = GalleryItem::whereNotNull('filename')->get();
            
            // Get reactions from DB
            $reactions = \App\Models\PhotoReaction::selectRaw('photo_id, reaction, COUNT(*) as count')
                ->groupBy('photo_id', 'reaction')
                ->get()
                ->groupBy('photo_id');
            
            // Get comments from DB
            $comments = \App\Models\PhotoComment::where('status', 'approved')
                ->selectRaw('photo_id, COUNT(*) as count')
                ->groupBy('photo_id')
                ->pluck('count', 'photo_id');
            
            // Get downloads from DB
            $downloads = DownloadLog::selectRaw('photo_id, COUNT(*) as count')
                ->whereNotNull('photo_id')
                ->groupBy('photo_id')
                ->pluck('count', 'photo_id');
            
            // Build summary
        $summary = [];
            foreach ($items as $item) {
                $photoId = (string)$item->id;
                $reactionsForPhoto = $reactions->get($photoId, collect());
                $likes = $reactionsForPhoto->where('reaction', 'like')->sum('count');
                $dislikes = $reactionsForPhoto->where('reaction', 'dislike')->sum('count');
                
                $summary[$photoId] = [
                    'likes' => $likes,
                    'dislikes' => $dislikes,
                    'comments' => $comments->get($photoId, 0),
                    'downloads' => $downloads->get($photoId, 0),
                ];
            }
            
        // Build rows
        $rows = [];
            foreach ($items as $item) {
                $photoId = (string)$item->id;
                $s = $summary[$photoId] ?? ['likes'=>0,'dislikes'=>0,'comments'=>0,'downloads'=>0];
            $rows[] = [
                    'id' => $item->id,
                    'filename' => $item->filename,
                    'title' => $item->title ?? 'Tanpa Judul',
                    'category' => $item->category ?? 'Lainnya',
                    'url' => $item->filename ? asset('uploads/gallery/'.$item->filename) : '',
                    'likes' => $s['likes'],
                    'dislikes' => $s['dislikes'],
                    'comments' => $s['comments'],
                    'downloads' => $s['downloads'],
                    'score' => $s['likes'] - $s['dislikes']
                ];
            }
            
            usort($rows, function($a,$b){ 
                return ($b['score'] <=> $a['score']) ?: ($b['likes'] <=> $a['likes']); 
            });
            
            // Recent comments from DB
            $recentComments = \App\Models\PhotoComment::with('user')
                ->where('status', 'approved')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function($c){
                    return [
                        'photo_id' => $c->photo_id,
                        'user_name' => $c->user->name ?? 'Anonymous',
                        'comment' => $c->comment,
                        'created_at' => $c->created_at->toIso8601String(),
                    ];
                })->toArray();
            
        return view('admin.gallery.report', compact('rows','recentComments'));
        } catch (\Exception $e) {
            \Log::error('Gallery report error: ' . $e->getMessage());
            return view('admin.gallery.report', ['rows' => [], 'recentComments' => []]);
        }
    })->name('gallery.report');

    // Export PDF of gallery report
    Route::get('/gallery/report/pdf', function(){
        $dir = public_path('uploads/gallery');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $reactionsPath = $dir . DIRECTORY_SEPARATOR . 'reactions.json';
        $commentsPath = $dir . DIRECTORY_SEPARATOR . 'comments.json';
        $downloadsJsonPath = $dir . DIRECTORY_SEPARATOR . 'downloads.json';
        $items = is_file($manifestPath) ? (json_decode(file_get_contents($manifestPath), true) ?: []) : [];
        $reactions = is_file($reactionsPath) ? (json_decode(file_get_contents($reactionsPath), true) ?: []) : [];
        $comments = is_file($commentsPath) ? (json_decode(file_get_contents($commentsPath), true) ?: []) : [];
        $downloadsJson = is_file($downloadsJsonPath) ? (json_decode(file_get_contents($downloadsJsonPath), true) ?: []) : [];

        $byFile = [];
        foreach ($items as $it){ $byFile[$it['filename']] = $it; }
        $summary = [];
        foreach ($reactions as $r){
            $f = $r['filename'] ?? null; if(!$f) continue;
            $summary[$f] = $summary[$f] ?? ['likes'=>0,'dislikes'=>0,'comments'=>0];
            $summary[$f]['likes'] = (int)($r['likes'] ?? 0);
            $summary[$f]['dislikes'] = (int)($r['dislikes'] ?? 0);
        }
        foreach ($comments as $c){
            $f = $c['filename'] ?? null; if(!$f) continue;
            $summary[$f] = $summary[$f] ?? ['likes'=>0,'dislikes'=>0,'comments'=>0];
            $summary[$f]['comments']++;
        }
        try {
            if (\Schema::hasTable('download_logs')) {
                $byDb = DownloadLog::selectRaw('filename, COUNT(*) as c')
                    ->whereNotNull('filename')
                    ->groupBy('filename')
                    ->pluck('c','filename')->toArray();
                foreach ($byDb as $f=>$cnt){
                    $summary[$f] = $summary[$f] ?? ['likes'=>0,'dislikes'=>0,'comments'=>0];
                    $summary[$f]['downloads'] = ($summary[$f]['downloads'] ?? 0) + (int)$cnt;
                }
            }
        } catch (\Throwable $e) { /* ignore */ }
        foreach ($downloadsJson as $d){
            $f = $d['filename'] ?? null; if(!$f) continue;
            $summary[$f] = $summary[$f] ?? ['likes'=>0,'dislikes'=>0,'comments'=>0];
            $summary[$f]['downloads'] = ($summary[$f]['downloads'] ?? 0) + 1;
        }
        $rows = [];
        foreach ($summary as $f=>$s){
            $item = $byFile[$f] ?? ['filename'=>$f,'title'=>$f,'category'=>'Lainnya','url'=>asset('uploads/gallery/'.$f)];
            $rows[] = [
                'filename'=>$f,
                'title'=>$item['title'] ?? $f,
                'category'=>$item['category'] ?? 'Lainnya',
                'url'=>$item['url'] ?? asset('uploads/gallery/'.$f),
                'likes'=>$s['likes'] ?? 0,
                'dislikes'=>$s['dislikes'] ?? 0,
                'comments'=>$s['comments'] ?? 0,
                'downloads'=>(int)($s['downloads'] ?? 0),
                'score'=>(($s['likes'] ?? 0) - ($s['dislikes'] ?? 0))
            ];
        }
        usort($rows, function($a,$b){ return ($b['score'] <=> $a['score']) ?: ($b['likes'] <=> $a['likes']); });

        // If Dompdf is available, generate PDF; otherwise, fallback to HTML
        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.gallery.report-pdf', [
                'rows' => $rows,
                'generatedAt' => date('d M Y H:i')
            ])->setPaper('a4', 'portrait');
            return $pdf->download('laporan-galeri.pdf');
        }
        return view('admin.gallery.report-pdf', [ 'rows'=>$rows, 'generatedAt'=>date('d M Y H:i') ]);
    })->name('gallery.report.pdf');
    // Gallery comments moderation
    Route::get('/gallery/comments', function(Request $request){
        $status = $request->query('status','pending'); // pending|approved|rejected|all
        $dir = public_path('uploads/gallery');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $commentsPath = $dir . DIRECTORY_SEPARATOR . 'comments.json';
        $items = is_file($manifestPath) ? (json_decode(file_get_contents($manifestPath), true) ?: []) : [];
        $comments = is_file($commentsPath) ? (json_decode(file_get_contents($commentsPath), true) ?: []) : [];
        // map filename => item (for title/category/url)
        $byFile = [];
        foreach ($items as $it){ $byFile[$it['filename']] = $it; }
        // filter
        $filtered = array_values(array_filter($comments, function($c) use ($status){
            if ($status === 'all') return true;
            return (($c['status'] ?? 'pending') === $status);
        }));
        // latest first
        usort($filtered, function($a,$b){ return strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''); });
        return view('admin.gallery.comments', [
            'comments' => $filtered,
            'status' => $status,
            'byFile' => $byFile,
        ]);
    })->name('gallery.comments');

    Route::post('/gallery/comments/{id}/approve', function($id){
        $dir = public_path('uploads/gallery');
        $commentsPath = $dir . DIRECTORY_SEPARATOR . 'comments.json';
        $comments = is_file($commentsPath) ? (json_decode(file_get_contents($commentsPath), true) ?: []) : [];
        foreach ($comments as &$c){ if (($c['id'] ?? '') === $id){ $c['status'] = 'approved'; $c['moderated_at']=date('c'); break; } }
        unset($c);
        file_put_contents($commentsPath, json_encode($comments, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        return back()->with('status','Komentar disetujui.');
    })->name('gallery.comments.approve');

    Route::post('/gallery/comments/{id}/reject', function($id){
        $dir = public_path('uploads/gallery');
        $commentsPath = $dir . DIRECTORY_SEPARATOR . 'comments.json';
        $comments = is_file($commentsPath) ? (json_decode(file_get_contents($commentsPath), true) ?: []) : [];
        foreach ($comments as &$c){ if (($c['id'] ?? '') === $id){ $c['status'] = 'rejected'; $c['moderated_at']=date('c'); break; } }
        unset($c);
        file_put_contents($commentsPath, json_encode($comments, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        return back()->with('status','Komentar ditolak.');
    })->name('gallery.comments.reject');

    Route::delete('/gallery/comments/{id}', function($id){
        $dir = public_path('uploads/gallery');
        $commentsPath = $dir . DIRECTORY_SEPARATOR . 'comments.json';
        $comments = is_file($commentsPath) ? (json_decode(file_get_contents($commentsPath), true) ?: []) : [];
        $found = false;
        foreach ($comments as $i => $c){ if (($c['id'] ?? '') === $id){ array_splice($comments, $i, 1); $found=true; break; } }
        if ($found){ file_put_contents($commentsPath, json_encode($comments, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)); }
        return back()->with('status', $found ? 'Komentar dihapus.' : 'Komentar tidak ditemukan.');
    })->name('gallery.comments.destroy');
    Route::get('/gallery/create', function () {
        $dir = public_path('uploads/gallery');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $items = is_file($manifestPath) ? (json_decode(file_get_contents($manifestPath), true) ?: []) : [];
        $categories = array_values(array_unique(array_map(function($it){ return $it['category'] ?? 'Lainnya'; }, $items)));
        sort($categories);
        return view('admin.gallery.create', ['categories' => $categories]);
    })->name('gallery.create');
    Route::get('/gallery/{filename}', function ($filename) {
        $dir = public_path('uploads/gallery');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $items = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $item = collect($items)->firstWhere('filename', $filename);
        abort_if(!$item, 404);
        return view('admin.gallery.show', compact('item'));
    })->name('gallery.show');
    Route::get('/gallery/{filename}/edit', function ($filename) {
        $dir = public_path('uploads/gallery');
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        $items = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $item = collect($items)->firstWhere('filename', $filename);
        abort_if(!$item, 404);
        return view('admin.gallery.edit', compact('item'));
    })->name('gallery.edit');

    Route::put('/gallery/{filename}', function (Request $request, $filename) {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'caption' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:25600'],
        ]);

        $uploadDir = public_path('uploads/gallery');
        $manifestPath = $uploadDir . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $index = null;
        foreach ($manifest as $i => $it) { if (($it['filename'] ?? '') === $filename) { $index = $i; break; } }
        abort_if($index === null, 404);

        $current = $manifest[$index];
        // Replace file if new image uploaded
        if ($request->hasFile('image')) {
            // delete old file
            $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $current['filename'];
            if (is_file($oldPath)) { @unlink($oldPath); }
            $file = $request->file('image');
            $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $newFilename = uniqid('img_') . '_' . $safeName;
            $file->move($uploadDir, $newFilename);
            $current['filename'] = $newFilename;
            $current['url'] = asset('uploads/gallery/' . $newFilename);
            $filename = $newFilename; // update route param reference
        }

        $current['title'] = $validated['title'];
        $current['category'] = $validated['category'];
        $current['caption'] = $validated['caption'] ?? '';
        $manifest[$index] = $current;
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.gallery.index')->with('status', 'Foto berhasil diperbarui.');
    })->name('gallery.update');

    Route::delete('/gallery/{filename}', function ($filename) {
        $uploadDir = public_path('uploads/gallery');
        $manifestPath = $uploadDir . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $found = false;
        foreach ($manifest as $i => $it) {
            if (($it['filename'] ?? '') === $filename) {
                // delete file
                $path = $uploadDir . DIRECTORY_SEPARATOR . $it['filename'];
                if (is_file($path)) { @unlink($path); }
                array_splice($manifest, $i, 1);
                $found = true;
                break;
            }
        }
        if ($found) {
            file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        }
        return redirect()->route('admin.gallery.index')->with('status', $found ? 'Foto berhasil dihapus.' : 'Foto tidak ditemukan.');
    })->name('gallery.destroy');

    // Guru & Staf Management
    Route::get('/guru-staf', function () {
        $guruDir = public_path('images/guru');
        $stafDir = public_path('images/staf');
        $kepalaDir = public_path('images/kepala-sekolah');
        if (!is_dir($guruDir)) { @mkdir($guruDir, 0755, true); }
        if (!is_dir($stafDir)) { @mkdir($stafDir, 0755, true); }
        if (!is_dir($kepalaDir)) { @mkdir($kepalaDir, 0755, true); }
        // include files with and without extension so legacy uploads still show
        $collect = function (string $dir, string $type) {
            // Scan all files then include those with image extensions (any case) or without extension
            $paths = glob($dir . '/*') ?: [];
            $items = [];
            foreach ($paths as $p) {
                if (!is_file($p)) { continue; }
                $ext = pathinfo($p, PATHINFO_EXTENSION);
                if (!$ext || preg_match('/\.(jpe?g|png|webp|gif)$/i', $p)) {
                    $items[] = [
                        'type' => $type,
                        'filename' => basename($p),
                        'url' => asset('images/'.$type.'/'.basename($p)),
                        'uploaded_at' => date('c', @filemtime($p) ?: time()),
                    ];
                }
            }
            return $items;
        };
        $items = array_merge(
            $collect($guruDir,'guru'),
            $collect($stafDir,'staf'),
            $collect($kepalaDir,'kepala-sekolah')
        );
        // sort latest first
        usort($items, function($a,$b){ return strcmp($b['uploaded_at'], $a['uploaded_at']); });
        return view('admin.guru-staf.index', compact('items'));
    })->name('guru-staf.index');

    // Upload page (create)
    Route::view('/guru-staf/create', 'admin.guru-staf.create')->name('guru-staf.create');

    Route::post('/guru-staf/upload', function (Request $request) {
        $validated = $request->validate([
            'type' => ['required','in:guru,staf,kepala-sekolah'],
            'image' => ['required','image','mimes:jpeg,png,jpg,webp,gif','max:25600'],
        ]);
        $dir = public_path('images/' . $validated['type']);
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $file = $request->file('image');
        $safe = preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
        // ensure extension; infer when missing
        $ext = pathinfo($safe, PATHINFO_EXTENSION);
        if (!$ext) {
            $ext = $file->getClientOriginalExtension() ?: $file->extension();
            if (!$ext) {
                $mime = $file->getMimeType();
                $map = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
                $ext = $map[$mime] ?? 'jpg';
            }
            $safe = pathinfo($safe, PATHINFO_FILENAME) . '.' . strtolower($ext);
        }
        // avoid overwrite if same name exists
        $target = $dir . DIRECTORY_SEPARATOR . $safe;
        if (is_file($target)) {
            $safe = uniqid('img_') . '_' . $safe;
            $target = $dir . DIRECTORY_SEPARATOR . $safe;
        }
        $file->move($dir, $safe);
        return redirect()->route('admin.guru-staf.index')->with('status', 'Foto berhasil diupload.');
    })->name('guru-staf.upload');

    // Show detail guru/staf
    Route::get('/guru-staf/{type}/{filename}', function ($type, $filename) {
        abort_unless(in_array($type, ['guru','staf','kepala-sekolah']), 404);
        $dir = public_path('images/'.$type);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        abort_if(!is_file($path), 404);
        $item = [
            'type' => $type,
            'filename' => $filename,
            'url' => asset('images/'.$type.'/'.$filename),
        ];
        return view('admin.guru-staf.show', compact('item'));
    })->name('guru-staf.show');

    // Edit form
    Route::get('/guru-staf/{type}/{filename}/edit', function ($type, $filename) {
        abort_unless(in_array($type, ['guru','staf','kepala-sekolah']), 404);
        $dir = public_path('images/'.$type);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        abort_if(!is_file($path), 404);
        $item = [
            'type' => $type,
            'filename' => $filename,
            'url' => asset('images/'.$type.'/'.$filename),
        ];
        return view('admin.guru-staf.edit', compact('item'));
    })->name('guru-staf.edit');

    // Update: optional new image and/or rename
    Route::put('/guru-staf/{type}/{filename}', function (Request $request, $type, $filename) {
        abort_unless(in_array($type, ['guru','staf','kepala-sekolah']), 404);
        $validated = $request->validate([
            'new_name' => ['nullable','string','max:150'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,webp,gif','max:25600'],
        ]);
        $dir = public_path('images/'.$type);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        abort_if(!is_file($path), 404);

        $currentName = $filename;

        // If user uploads new image, replace file (keep current name by default)
        if ($request->hasFile('image')) {
            $tmp = $request->file('image');
            $ext = pathinfo($currentName, PATHINFO_EXTENSION) ?: ($tmp->getClientOriginalExtension() ?: 'jpg');
            $base = pathinfo($currentName, PATHINFO_FILENAME);
            $targetName = $base . ($ext ? '.'.strtolower($ext) : '');
            $targetPath = $dir . DIRECTORY_SEPARATOR . $targetName;
            // ensure extension exists for the replacement
            if (!$ext) { $ext = $tmp->getClientOriginalExtension() ?: 'jpg'; $targetName = $base.'.'.$ext; $targetPath = $dir.DIRECTORY_SEPARATOR.$targetName; }
            // replace file contents
            @unlink($targetPath); // remove if exists
            $tmp->move($dir, $targetName);
            $currentName = $targetName;
        }

        // If new_name provided, rename file
        if (!empty($validated['new_name'])) {
            $baseNew = preg_replace('/[^A-Za-z0-9_\.-]/','_', $validated['new_name']);
            $ext = pathinfo($currentName, PATHINFO_EXTENSION);
            // If user-provided includes extension, prefer that
            $providedExt = pathinfo($baseNew, PATHINFO_EXTENSION);
            if ($providedExt) { $ext = $providedExt; }
            $baseOnly = pathinfo($baseNew, PATHINFO_FILENAME);
            $newFilename = $baseOnly . ($ext ? '.'.strtolower($ext) : '');
            $oldPath = $dir . DIRECTORY_SEPARATOR . $currentName;
            $newPath = $dir . DIRECTORY_SEPARATOR . $newFilename;
            if (strtolower($newPath) !== strtolower($oldPath) && is_file($newPath)) {
                $newFilename = uniqid('ren_') . '_' . $newFilename;
                $newPath = $dir . DIRECTORY_SEPARATOR . $newFilename;
            }
            @rename($oldPath, $newPath);
            $currentName = $newFilename;
        }

        return redirect()->route('admin.guru-staf.show', ['type'=>$type, 'filename'=>$currentName])
            ->with('status','Data berhasil diperbarui.');
    })->name('guru-staf.update');

    // Delete direct by filename
    Route::delete('/guru-staf/{type}/{filename}', function ($type, $filename) {
        abort_unless(in_array($type, ['guru','staf','kepala-sekolah']), 404);
        $dir = public_path('images/'.$type);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $deleted = false;
        if (is_file($path)) { $deleted = @unlink($path); }
        return redirect()->route('admin.guru-staf.index')->with('status', $deleted ? 'Foto berhasil dihapus.' : 'File tidak ditemukan.');
    })->name('guru-staf.destroy');

    Route::put('/guru-staf/rename', function (Request $request) {
        $validated = $request->validate([
            'type' => ['required','in:guru,staf,kepala-sekolah'],
            'old' => ['required','string'],
            'new' => ['required','string','max:150'],
        ]);
        $dir = public_path('images/' . $validated['type']);
        $oldPath = $dir . DIRECTORY_SEPARATOR . $validated['old'];
        abort_if(!is_file($oldPath), 404);
        $ext = pathinfo($validated['old'], PATHINFO_EXTENSION);
        $baseNew = preg_replace('/[^A-Za-z0-9_\.-]/','_', $validated['new']);
        // ensure extension
        if (!$ext) { $ext = pathinfo($baseNew, PATHINFO_EXTENSION); }
        $baseNew = pathinfo($baseNew, PATHINFO_FILENAME);
        $newFilename = $baseNew . ($ext ? ('.' . $ext) : '');
        $newPath = $dir . DIRECTORY_SEPARATOR . $newFilename;
        if (strtolower($newPath) !== strtolower($oldPath) && is_file($newPath)) {
            $newFilename = uniqid('ren_') . '_' . $newFilename;
            $newPath = $dir . DIRECTORY_SEPARATOR . $newFilename;
        }
        @rename($oldPath, $newPath);
        return redirect()->route('admin.guru-staf.index')->with('status', 'Nama file berhasil diubah.');
    })->name('guru-staf.rename');

    Route::delete('/guru-staf/delete', function (Request $request) {
        $validated = $request->validate([
            'type' => ['required','in:guru,staf,kepala-sekolah'],
            'filename' => ['required','string'],
        ]);
        $dir = public_path('images/' . $validated['type']);
        $path = $dir . DIRECTORY_SEPARATOR . $validated['filename'];
        $deleted = false;
        if (is_file($path)) { $deleted = @unlink($path); }
        return redirect()->route('admin.guru-staf.index')->with('status', $deleted ? 'Foto berhasil dihapus.' : 'File tidak ditemukan.');
    })->name('guru-staf.delete');

    // Home settings -> redirect to Guru & Staf management
    Route::get('/home/edit', function () { return redirect()->route('admin.guru-staf.index'); })->name('home.edit');
    
    // Manajemen Admin (hanya untuk role admin)
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    
    // Laporan Interaktif
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/users', [\App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
        Route::get('/photos', [\App\Http\Controllers\Admin\ReportController::class, 'photos'])->name('photos');
        Route::get('/users/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportUsersPdf'])->name('users.pdf');
        Route::get('/photos/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPhotosPdf'])->name('photos.pdf');
        Route::get('/users/{id}/edit', [\App\Http\Controllers\Admin\ReportController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'destroyUser'])->name('users.destroy');
    });
});
