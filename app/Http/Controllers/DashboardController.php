<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Information;
use App\Models\Agenda;
use App\Models\GalleryItem;

class DashboardController extends Controller
{
    public function index()
    {
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

        return view('dashboard', compact('stats', 'informations', 'galleries', 'agendas'));
    }
}