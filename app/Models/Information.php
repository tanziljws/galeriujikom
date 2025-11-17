<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;

    protected $table = 'informations';

    protected $fillable = [
        'title',
        'category',
        'date',
        'image_path',
        'description',
        'content',
        'created_by',
    ];
}
