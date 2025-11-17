<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
<<<<<<< HEAD
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('photo_id');
            $table->string('url', 1000)->nullable();
            $table->string('filename')->nullable();
            $table->string('ip', 64)->nullable();
            $table->timestamps();
            
            $table->index(['photo_id']);
            $table->index(['user_id']);
=======
            $table->string('filename')->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('name', 120);
            $table->string('email', 150);
            $table->string('role', 30);
            $table->string('purpose', 500);
            $table->string('ip', 64)->nullable();
            $table->timestamps();
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_logs');
    }
};
