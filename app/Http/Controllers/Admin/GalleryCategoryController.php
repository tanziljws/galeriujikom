<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\GalleryItem;

class GalleryCategoryController extends Controller
{
    protected $categoriesPath;
    protected $umbrellaCategoriesPath;

    public function __construct()
    {
        // Gunakan file yang sama dengan publik
        $this->categoriesPath = resource_path('data/umbrella_categories.json');
        $this->umbrellaCategoriesPath = resource_path('data/umbrella_categories.json');
        
        // Create files if they don't exist
        $this->initializeFiles();
    }
    
    protected function initializeFiles()
    {
        // Buat direktori data jika belum ada
        $dataDir = resource_path('data');
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        if (!file_exists($this->categoriesPath)) {
            // Default categories
            $defaultCategories = [
                "Kegiatan Sekolah",
                "Akademik",
                "Ekstrakurikuler",
                "Perayaan & Hari Besar",
                "Kunjungan & Study Tour",
                "Prestasi & Penghargaan",
                "Proyek P5 & Kurikulum Merdeka",
                "Sarana & Prasarana",
                "Kegiatan Guru & Staf"
            ];
            file_put_contents($this->categoriesPath, json_encode($defaultCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Get all categories
     */
    protected function getCategories()
    {
        try {
            if (!file_exists($this->categoriesPath)) {
                return [];
            }
            
            $content = file_get_contents($this->categoriesPath);
            return json_decode($content, true) ?: [];
        } catch (\Exception $e) {
            \Log::error('Error reading categories file: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all umbrella categories
     */
    protected function getUmbrellaCategories()
    {
        try {
            if (!file_exists($this->umbrellaCategoriesPath)) {
                return [];
            }
            
            $content = file_get_contents($this->umbrellaCategoriesPath);
            return json_decode($content, true) ?: [];
        } catch (\Exception $e) {
            \Log::error('Error reading umbrella categories file: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save categories to file
     */
    protected function saveCategories(array $categories)
    {
        try {
            file_put_contents(
                $this->categoriesPath, 
                json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            return true;
        } catch (\Exception $e) {
            \Log::error('Error saving categories: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save umbrella categories to file
     */
    protected function saveUmbrellaCategories(array $categories)
    {
        try {
            file_put_contents(
                $this->umbrellaCategoriesPath, 
                json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            return true;
        } catch (\Exception $e) {
            \Log::error('Error saving umbrella categories: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Display the categories management page
     */
    public function index()
    {
        try {
            $umbrellaCategories = $this->getUmbrellaCategories();
            
            // Handle both flat array and nested structure - same logic as GalleryController
            $categories = [];
            if (is_array($umbrellaCategories)) {
                // Check if it's an associative array (object) or indexed array
                if (array_keys($umbrellaCategories) !== range(0, count($umbrellaCategories) - 1)) {
                    // It's an object/associative array (umbrella categories structure)
                    // Only show umbrella categories (keys), not subcategories - same as user filter
                    $categories = array_keys($umbrellaCategories);
                } else {
                    // It's a flat indexed array
                    $categories = $umbrellaCategories;
                }
            }
            
            // Count photos per category from DB (only for umbrella categories shown to users)
            $photosByCategory = [];
            foreach ($categories as $category) {
                $photosByCategory[$category] = GalleryItem::where('category', $category)->count();
            }
            
            return view('admin.gallery.categories', [
                'categories' => $categories,
                'umbrellaCategories' => $umbrellaCategories,
                'photosByCategory' => $photosByCategory
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading categories page: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return view('admin.gallery.categories', [
                'categories' => [],
                'umbrellaCategories' => [],
                'photosByCategory' => []
            ]);
        }
    }

    // Menyimpan kategori baru
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:100']);
        
        $categories = $this->getCategories();
        $name = trim($validated['name']);
        
        // Cek duplikasi
        if (in_array($name, $categories)) {
            return back()->with('error', 'Kategori "' . $name . '" sudah ada');
        }
        
        $categories[] = $name;
        
        if ($this->saveCategories($categories)) {
            return back()->with('status', 'Kategori "' . $name . '" berhasil ditambahkan');
        } else {
            return back()->with('error', 'Gagal menambahkan kategori');
        }
    }

    // Mengubah nama kategori
    public function update(Request $request)
    {
        $validated = $request->validate([
            'old' => 'required|string',
            'new' => 'required|string|max:100'
        ]);
        
        $categories = $this->getCategories();
        $old = $validated['old'];
        $new = trim($validated['new']);
        
        // Cek apakah kategori baru sudah ada (kecuali nama lama)
        if ($old !== $new && in_array($new, $categories)) {
            return back()->with('error', 'Kategori dengan nama tersebut sudah ada');
        }
        
        $index = array_search($old, $categories);
        if ($index !== false) {
            $categories[$index] = $new;
            // Re-index array untuk memastikan konsistensi
            $categories = array_values($categories);
            
            if ($this->saveCategories($categories)) {
                return back()->with('status', 'Kategori berhasil diubah dari "' . $old . '" menjadi "' . $new . '"');
            } else {
                return back()->with('error', 'Gagal menyimpan perubahan kategori');
            }
        }
        
        return back()->with('error', 'Kategori "' . $old . '" tidak ditemukan');
    }

    // Menghapus kategori
    public function destroy(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string']);
        
        $categories = $this->getCategories();
        $name = $validated['name'];
        
        // Cek apakah kategori ada
        if (!in_array($name, $categories)) {
            return back()->with('error', 'Kategori "' . $name . '" tidak ditemukan');
        }
        
        // Hapus kategori dan re-index array
        $categories = array_values(array_diff($categories, [$name]));
        
        if ($this->saveCategories($categories)) {
            return back()->with('status', 'Kategori "' . $name . '" berhasil dihapus');
        } else {
            return back()->with('error', 'Gagal menghapus kategori');
        }
    }

}
