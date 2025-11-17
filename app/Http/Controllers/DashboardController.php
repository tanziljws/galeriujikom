<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Models\Information;
use App\Models\Agenda;
use App\Models\GalleryItem;
=======
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b

class DashboardController extends Controller
{
    public function index()
    {
<<<<<<< HEAD
        // Data statistik dashboard (dinamis)
        $stats = [
            'total_gallery' => GalleryItem::count(),
            'total_information' => Information::count(),
            'total_agenda' => Agenda::count(),
            'total_students' => 1200, // placeholder jika ada tabel siswa nanti
        ];

        // Informasi terbaru (5)
        $informations = Information::orderByDesc('created_at')->limit(5)->get()
            ->map(function($it){
                return [
                    'title' => $it->title,
                    'description' => $it->description,
                    'date' => $it->date,
                ];
            })->toArray();

        // Galeri terbaru (8 foto terbaru)
        $galleries = GalleryItem::orderByDesc('created_at')->limit(8)->get()
            ->map(function($g){
                return [
                    'title' => $g->title,
                    'image' => $g->filename ? asset('uploads/gallery/'.$g->filename) : ($g->image_path ?? ''),
                    'date' => optional($g->created_at)->format('Y-m-d'),
                ];
            })->toArray();

        // Agenda terbaru (5) berdasarkan created_at
        $agendas = Agenda::orderByDesc('created_at')->limit(5)->get()
            ->map(function($a){
                return [
                    'title' => $a->title,
                    'description' => $a->description,
                    'date' => $a->date,
                    'time' => '',
                ];
            })->toArray();
=======
        // Data statistik dashboard
        $stats = [
            'total_gallery' => 150,
            'total_information' => 25,
            'total_agenda' => 12,
            'total_students' => 1200
        ];

        // Data informasi terbaru
        $informations = [
            [
                'title' => 'Penerimaan Siswa Baru 2024',
                'description' => 'Pendaftaran dibuka mulai 1 Januari 2024. Segera daftarkan putra-putri Anda.',
                'date' => '2023-12-01'
            ],
            [
                'title' => 'Jadwal Ujian Semester',
                'description' => 'Ujian semester ganjil akan dilaksanakan pada 15-20 Desember 2023.',
                'date' => '2023-12-15'
            ],
            [
                'title' => 'Kegiatan Ekstrakurikuler',
                'description' => 'Pembukaan pendaftaran ekstrakurikuler baru untuk semester genap.',
                'date' => '2023-12-10'
            ]
        ];

        // Data galeri terbaru
        $galleries = [
            [
                'title' => 'Kegiatan Pramuka',
                'image' => 'https://via.placeholder.com/200x150/7A9CC6/FFFFFF?text=Kegiatan+1',
                'date' => '2023-12-05'
            ],
            [
                'title' => 'Lomba Sains',
                'image' => 'https://via.placeholder.com/200x150/9BB5D1/FFFFFF?text=Kegiatan+2',
                'date' => '2023-12-03'
            ],
            [
                'title' => 'Upacara Bendera',
                'image' => 'https://via.placeholder.com/200x150/7A9CC6/FFFFFF?text=Kegiatan+3',
                'date' => '2023-12-01'
            ],
            [
                'title' => 'Workshop IT',
                'image' => 'https://via.placeholder.com/200x150/9BB5D1/FFFFFF?text=Kegiatan+4',
                'date' => '2023-11-28'
            ]
        ];

        // Data agenda mendatang
        $agendas = [
            [
                'title' => 'Ujian Semester Ganjil',
                'description' => 'Pelaksanaan ujian semester ganjil untuk semua kelas dan jurusan.',
                'date' => '2023-12-15',
                'time' => '08:00 - 12:00'
            ],
            [
                'title' => 'Pembagian Raport',
                'description' => 'Pembagian raport semester ganjil untuk orang tua siswa.',
                'date' => '2023-12-20',
                'time' => '09:00 - 15:00'
            ],
            [
                'title' => 'Libur Semester',
                'description' => 'Libur semester ganjil dimulai dari 25 Desember 2023 - 8 Januari 2024.',
                'date' => '2023-12-25',
                'time' => '00:00 - 23:59'
            ],
            [
                'title' => 'Masuk Semester Genap',
                'description' => 'Kegiatan belajar mengajar semester genap dimulai.',
                'date' => '2024-01-09',
                'time' => '07:00 - 15:00'
            ]
        ];
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b

        return view('dashboard', compact('stats', 'informations', 'galleries', 'agendas'));
    }
}