<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();
            $table->date('date')->nullable();
            $table->string('image_path')->nullable();
            $table->string('description', 500)->nullable();
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['created_at']);
            $table->index(['date']);
            $table->index(['category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informations');
    }
};
