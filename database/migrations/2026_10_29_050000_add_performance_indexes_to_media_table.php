<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('media', function (Blueprint $table) {
            if (!Schema::hasColumn('media', 'vote_average')) {
                $table->decimal('vote_average', 4, 1)->nullable()->after('poster_path');
            }
            if (!Schema::hasColumn('media', 'genre_ids')) {
                $table->string('genre_ids', 255)->nullable()->after('vote_average');
            }
        });

        // Add performance indexes for scalable filtering and sorting
        Schema::table('media', function (Blueprint $table) {
            $table->index(['media_type', 'id'], 'idx_media_type_id');
            $table->index(['media_type', 'title'], 'idx_media_type_title');
            $table->index(['media_type', 'vote_average'], 'idx_media_type_vote_avg');
            $table->index(['media_type', 'genre_ids'], 'idx_media_type_genre_ids');
        });

        // Backfill vote_average and genre_ids from existing data column
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("
                UPDATE media 
                SET 
                    vote_average = CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.vote_average')) AS DECIMAL(4,1)),
                    genre_ids = CASE 
                        WHEN JSON_EXTRACT(data, '$.genre_ids') IS NOT NULL 
                        THEN CONCAT(',', REPLACE(REPLACE(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.genre_ids')), '[', ''), ']', ''), ' ', ''), ',')
                        ELSE NULL 
                    END
                WHERE data IS NOT NULL AND JSON_VALID(data)
            ");
        } elseif ($driver === 'sqlite') {
            DB::statement("
                UPDATE media 
                SET vote_average = CAST(json_extract(data, '$.vote_average') AS REAL)
                WHERE data IS NOT NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('idx_media_type_id');
            $table->dropIndex('idx_media_type_title');
            $table->dropIndex('idx_media_type_vote_avg');
            $table->dropIndex('idx_media_type_genre_ids');

            if (Schema::hasColumn('media', 'vote_average')) {
                $table->dropColumn('vote_average');
            }
            if (Schema::hasColumn('media', 'genre_ids')) {
                $table->dropColumn('genre_ids');
            }
        });
    }
};

