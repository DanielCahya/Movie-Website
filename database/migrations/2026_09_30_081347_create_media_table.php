<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id');
            $table->string('media_type', 10); // 'movie' or 'tv'
            $table->string('title');
            $table->string('poster_path')->nullable();
            $table->json('data')->nullable(); // Store the entire TMDB response!
            $table->timestamps();
            
            // Fast lookups
            $table->unique(['tmdb_id', 'media_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
};
