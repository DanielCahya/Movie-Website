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
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM media"))
                ->pluck('Key_name')
                ->unique()
                ->toArray();

            if (!in_array('idx_media_title', $existingIndexes)) {
                Schema::table('media', function (Blueprint $table) {
                    $table->index('title', 'idx_media_title');
                });
            }
        } else {
            Schema::table('media', function (Blueprint $table) {
                $table->index('title', 'idx_media_title');
            });
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
            $table->dropIndex('idx_media_title');
        });
    }
};
