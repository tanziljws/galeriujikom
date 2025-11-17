<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
=======
use App\Models\Kategori;
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b

class Agenda extends Model
{
    use HasFactory;

<<<<<<< HEAD
    protected $table = 'agendas';

    protected $fillable = [
        'title',
        'date',
        'place',
        'description',
        'poster_path',
        'created_by',
    ];
=======
    protected $table = 'agenda';

    protected $fillable = [
        'judul',
        'kategori_id',
    ];

    // Relasi ke kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b
}
