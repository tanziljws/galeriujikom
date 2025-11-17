<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsTo;
=======
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b

class DownloadLog extends Model
{
    use HasFactory;

    protected $fillable = [
<<<<<<< HEAD
        'user_id', 'photo_id', 'url', 'filename', 'ip'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
=======
        'filename', 'url', 'name', 'email', 'role', 'purpose', 'ip'
    ];
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b
}
