<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Information;
use Illuminate\Support\Facades\Auth;

class InformationController extends Controller
{
    /**
     * Return the default seed informations used to pre-populate the site.
     * This is exposed so the Admin routes can bootstrap the manifest on first run.
     */
    public function getSeedInformations(): array
    {
        return $this->getInformations();
    }

    private function getInformations(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Penerimaan Peserta Didik Baru (PPDB) 2025',
                'description' => 'Pendaftaran PPDB tahun ajaran 2025/2026 akan segera dibuka. Dapatkan informasi lengkap mengenai persyaratan, jadwal, dan prosedur pendaftaran.',
                'content' => 'SMKN 4 Bogor membuka pendaftaran untuk tahun ajaran 2025/2026 dengan berbagai jurusan unggulan. Pendaftaran akan dibuka mulai tanggal 1 Februari 2025 hingga 31 Maret 2025. Tersedia berbagai jurusan seperti Teknik Komputer dan Jaringan, Multimedia, Rekayasa Perangkat Lunak, dan Teknik Elektronika.',
                'date' => '2024-12-01',
                'category' => 'Pendaftaran',
                'image' => 'https://via.placeholder.com/400x250/7A9CC6/FFFFFF?text=PPDB+2025',
                'is_featured' => true
            ],
            [
                'id' => 2,
                'title' => 'Jadwal Ujian Semester Genap 2024',
                'description' => 'Informasi lengkap mengenai jadwal pelaksanaan ujian semester genap tahun ajaran 2023/2024.',
                'content' => 'Ujian semester genap akan dilaksanakan mulai tanggal 15 Juni 2024 hingga 25 Juni 2024. Semua siswa wajib mengikuti ujian sesuai dengan jadwal yang telah ditentukan. Ujian akan dilaksanakan secara luring dengan protokol kesehatan yang ketat.',
                'date' => '2024-05-15',
                'category' => 'Akademik',
                'image' => 'https://via.placeholder.com/400x250/9BB5D1/FFFFFF?text=Ujian+Semester',
                'is_featured' => false
            ],
            [
                'id' => 3,
                'title' => 'Program Magang Industri 2024',
                'description' => 'Kesempatan magang di berbagai perusahaan teknologi terkemuka untuk siswa kelas XI dan XII.',
                'content' => 'SMKN 4 Bogor bekerja sama dengan berbagai perusahaan teknologi untuk memberikan kesempatan magang kepada siswa. Program ini bertujuan untuk memberikan pengalaman kerja nyata dan meningkatkan kompetensi siswa sebelum lulus.',
                'date' => '2024-07-01',
                'category' => 'Kerjasama',
                'image' => 'https://via.placeholder.com/400x250/A8C8EC/FFFFFF?text=Magang+Industri',
                'is_featured' => true
            ],
            [
                'id' => 4,
                'title' => 'Lomba Kompetensi Siswa (LKS) 2024',
                'description' => 'Persiapan dan pelaksanaan Lomba Kompetensi Siswa tingkat provinsi dan nasional.',
                'content' => 'Siswa SMKN 4 Bogor akan mengikuti LKS 2024 dalam berbagai bidang keahlian. Persiapan intensif telah dilakukan untuk memastikan siswa dapat bersaing di tingkat provinsi dan nasional.',
                'date' => '2024-08-15',
                'category' => 'Prestasi',
                'image' => 'https://via.placeholder.com/400x250/B8D4F1/FFFFFF?text=LKS+2024',
                'is_featured' => false
            ],
            [
                'id' => 5,
                'title' => 'Sertifikasi Kompetensi Keahlian',
                'description' => 'Program sertifikasi kompetensi untuk siswa kelas XII sebagai bekal memasuki dunia kerja.',
                'content' => 'Seluruh siswa kelas XII akan mengikuti uji kompetensi keahlian yang diselenggarakan oleh Lembaga Sertifikasi Profesi (LSP). Sertifikat ini akan menjadi nilai tambah dalam memasuki dunia kerja atau melanjutkan pendidikan.',
                'date' => '2024-09-01',
                'category' => 'Sertifikasi',
                'image' => 'https://via.placeholder.com/400x250/C8E4F6/FFFFFF?text=Sertifikasi',
                'is_featured' => false
            ],
            [
                'id' => 6,
                'title' => 'Kegiatan Ekstrakurikuler Semester Baru',
                'description' => 'Pembukaan pendaftaran berbagai kegiatan ekstrakurikuler untuk semester baru.',
                'content' => 'Tersedia berbagai pilihan ekstrakurikuler seperti Robotika, Multimedia, Pramuka, Basket, Futsal, dan masih banyak lagi. Pendaftaran dibuka untuk semua siswa kelas X dan XI.',
                'date' => '2024-07-15',
                'category' => 'Ekstrakurikuler',
                'image' => 'https://via.placeholder.com/400x250/D8F4FF/FFFFFF?text=Ekstrakurikuler',
                'is_featured' => false
            ],
            [
                'id' => 7,
                'title' => 'Pelatihan Guru Berbasis Industri',
                'description' => 'Peningkatan kompetensi guru melalui pelatihan bersama IDUKA.',
                'content' => 'Guru-guru mengikuti pelatihan kurikulum berbasis industri untuk menyelaraskan pembelajaran di kelas dengan kebutuhan dunia kerja.',
                'date' => '2024-10-02',
                'category' => 'Kerjasama',
                'image' => 'https://via.placeholder.com/400x250/C0D6E8/FFFFFF?text=Pelatihan+Guru',
                'is_featured' => false
            ],
            [
                'id' => 8,
                'title' => 'Pameran Karya Siswa 2024',
                'description' => 'Pameran karya inovatif siswa lintas jurusan di aula sekolah.',
                'content' => 'Berbagai produk TFFL, TO, PPLG, dan TKJ dipamerkan kepada publik dan mitra industri.',
                'date' => '2024-11-12',
                'category' => 'Prestasi',
                'image' => 'https://via.placeholder.com/400x250/ACCBE1/FFFFFF?text=Pameran+Karya',
                'is_featured' => false
            ],
            [
                'id' => 9,
                'title' => 'Beasiswa Prestasi Semester Ganjil',
                'description' => 'Pemberian beasiswa untuk siswa berprestasi akademik dan non-akademik.',
                'content' => 'Detail syarat dan teknis penyaluran beasiswa diumumkan oleh kesiswaan.',
                'date' => '2024-09-20',
                'category' => 'Akademik',
                'image' => 'https://via.placeholder.com/400x250/9EBBD8/FFFFFF?text=Beasiswa',
                'is_featured' => false
            ],
            [
                'id' => 10,
                'title' => 'Kunjungan Industri 2024',
                'description' => 'Kelas XI melakukan kunjungan ke perusahaan teknologi mitra.',
                'content' => 'Kunjungan ini memberikan wawasan proses produksi, standar K3, dan budaya kerja.',
                'date' => '2024-08-10',
                'category' => 'Kerjasama',
                'image' => 'https://via.placeholder.com/400x250/8FB1D3/FFFFFF?text=Kunjungan+Industri',
                'is_featured' => false
            ],
            [
                'id' => 11,
                'title' => 'Pengembangan Kurikulum Berbasis Industri',
                'description' => 'Pengembangan kurikulum yang sesuai dengan kebutuhan industri.',
                'content' => 'Kurikulum yang dikembangkan akan memastikan siswa memiliki kompetensi yang relevan dengan kebutuhan industri.',
                'date' => '2024-10-15',
                'category' => 'Kurikulum',
                'image' => 'https://via.placeholder.com/400x250/C0D6E8/FFFFFF?text=Pengembangan+Kurikulum',
                'is_featured' => false
            ],
            [
                'id' => 12,
                'title' => 'Pengembangan Fasilitas Sekolah',
                'description' => 'Pengembangan fasilitas sekolah untuk meningkatkan kualitas pembelajaran.',
                'content' => 'Fasilitas yang dikembangkan akan memastikan siswa memiliki lingkungan belajar yang nyaman dan mendukung.',
                'date' => '2024-11-20',
                'category' => 'Fasilitas',
                'image' => 'https://via.placeholder.com/400x250/ACCBE1/FFFFFF?text=Pengembangan+Fasilitas',
                'is_featured' => false
            ],
            [
                'id' => 13,
                'title' => 'Pengembangan Program Ekstrakurikuler',
                'description' => 'Pengembangan program ekstrakurikuler untuk meningkatkan kualitas siswa.',
                'content' => 'Program yang dikembangkan akan memastikan siswa memiliki kesempatan untuk mengembangkan bakat dan minat.',
                'date' => '2024-12-01',
                'category' => 'Ekstrakurikuler',
                'image' => 'https://via.placeholder.com/400x250/9EBBD8/FFFFFF?text=Pengembangan+Program+Ekstrakurikuler',
                'is_featured' => false
            ],
            [
                'id' => 14,
                'title' => 'Pengembangan Kerjasama dengan Industri',
                'description' => 'Pengembangan kerjasama dengan industri untuk meningkatkan kualitas pembelajaran.',
                'content' => 'Kerjasama yang dikembangkan akan memastikan siswa memiliki kesempatan untuk mengembangkan kompetensi yang relevan dengan kebutuhan industri.',
                'date' => '2024-12-15',
                'category' => 'Kerjasama',
                'image' => 'https://via.placeholder.com/400x250/C0D6E8/FFFFFF?text=Pengembangan+Kerjasama+dengan+Industri',
                'is_featured' => false
            ],
            [
                'id' => 15,
                'title' => 'Pengembangan Sistem Informasi Sekolah',
                'description' => 'Pengembangan sistem informasi sekolah untuk meningkatkan kualitas pembelajaran.',
                'content' => 'Sistem yang dikembangkan akan memastikan siswa memiliki akses ke informasi yang relevan dan akurat.',
                'date' => '2024-12-20',
                'category' => 'Sistem Informasi',
                'image' => 'https://via.placeholder.com/400x250/ACCBE1/FFFFFF?text=Pengembangan+Sistem+Informasi+Sekolah',
                'is_featured' => false
            ],
        ];
    }

    public function index(Request $request)
    {
        // Ambil informasi dari DB (terbaru berdasarkan created_at)
        $items = Information::orderByDesc('created_at')->limit(10)->get();
        // Map ke struktur yang dipakai view publik saat ini
        $informations = $items->map(function($it){
            return [
                'id' => $it->id,
                'title' => $it->title,
                'description' => $it->description,
                'content' => $it->content,
                'date' => $it->date,
                'category' => $it->category,
                'image' => $it->image_path ? (str_starts_with($it->image_path, 'http') ? $it->image_path : asset('uploads/informations/'.$it->image_path)) : null,
                'is_featured' => false,
            ];
        });

        // Data statistik sekolah (statis sementara)
        $stats = [
            ['title' => 'Total Siswa','value' => '1,250','icon' => '👥','color' => 'bg-blue-100 text-blue-800'],
            ['title' => 'Jurusan','value' => '4','icon' => '🎓','color' => 'bg-green-100 text-green-800'],
            ['title' => 'Guru & Staff','value' => '85','icon' => '👨‍🏫','color' => 'bg-purple-100 text-purple-800'],
            ['title' => 'Alumni Sukses','value' => '5,000+','icon' => '🏆','color' => 'bg-yellow-100 text-yellow-800'],
        ];

        return view('information', compact('informations', 'stats'));
    }

    public function show($id)
    {
        $it = Information::findOrFail($id);
        $item = [
            'id' => $it->id,
            'title' => $it->title,
            'description' => $it->description,
            'content' => $it->content,
            'date' => $it->date,
            'category' => $it->category,
            'image' => $it->image_path ? (str_starts_with($it->image_path, 'http') ? $it->image_path : asset('uploads/informations/'.$it->image_path)) : null,
        ];
        $related = Information::where('id','!=',$it->id)
            ->where('category',$it->category)
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(function($r){
                return [
                    'id' => $r->id,
                    'title' => $r->title,
                    'description' => $r->description,
                    'date' => $r->date,
                    'category' => $r->category,
                    'image' => $r->image_path ? (str_starts_with($r->image_path, 'http') ? $r->image_path : asset('uploads/informations/'.$r->image_path)) : null,
                ];
            });
        return view('information-detail', [ 'info' => $item, 'related' => $related ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:500',
            'content' => 'nullable|string',
            'date' => 'nullable|date',
            'category' => 'nullable|string|max:50',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'image_url' => 'nullable|url'
        ]);

        $dir = public_path('uploads/informations');
        if (!is_dir($dir)) { @mkdir($dir,0755,true); }
        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $safe = preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $filename = uniqid('info_')."_".$safe;
            $file->move($dir, $filename);
            $imagePath = $filename; // lokal
        } elseif (!empty($validated['image_url'])) {
            $imagePath = $validated['image_url']; // URL
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

        return redirect()->route('information')->with('success', 'Informasi berhasil ditambahkan.');
    }
}
