<?php

use Illuminate\Database\Migrations\Migration;
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
        if (!Schema::hasTable('media')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        // 1. Backfill genre_ids for existing media containing genre 16 (Animation) in data
        if ($driver === 'mysql') {
            DB::statement("
                UPDATE media 
                SET genre_ids = CASE 
                    WHEN genre_ids IS NOT NULL AND genre_ids != '' THEN 
                        CASE WHEN (data LIKE '%\"id\":16%' OR data LIKE '%[16,%' OR data LIKE '%,16,%' OR data LIKE '%,16]%' OR data LIKE '%[16]%') AND genre_ids NOT LIKE '%,16,%'
                             THEN CONCAT(genre_ids, '16,')
                             ELSE genre_ids 
                        END
                    WHEN data LIKE '%\"id\":16%' OR data LIKE '%[16,%' OR data LIKE '%,16,%' OR data LIKE '%,16]%' OR data LIKE '%[16]%'
                    THEN ',16,'
                    ELSE genre_ids 
                END
                WHERE data IS NOT NULL AND (genre_ids IS NULL OR genre_ids NOT LIKE '%,16,%')
            ");
        }

        // 2. Seed foundational anime catalog so /animation has instant, high-quality content
        $now = now();
        $animeSeeds = [
            [
                'tmdb_id' => 1429,
                'media_type' => 'tv',
                'title' => 'Attack on Titan',
                'poster_path' => '/hTP1DtLGFamjfu8WqjnuQdP1n4i.jpg',
                'vote_average' => 8.7,
                'genre_ids' => ',16,10759,10765,',
                'data' => json_encode([
                    'id' => 1429,
                    'name' => 'Attack on Titan',
                    'title' => 'Attack on Titan',
                    'poster_path' => '/hTP1DtLGFamjfu8WqjnuQdP1n4i.jpg',
                    'vote_average' => 8.7,
                    'genre_ids' => [16, 10759, 10765],
                    'overview' => 'Centuries ago, mankind was almost slaughtered to extinction by monstrous creatures called Titans.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 85937,
                'media_type' => 'tv',
                'title' => 'Demon Slayer: Kimetsu no Yaiba',
                'poster_path' => '/xU7qi2vE2u6yXvX4Yh2c94Xv70Q.jpg',
                'vote_average' => 8.7,
                'genre_ids' => ',16,10759,10765,',
                'data' => json_encode([
                    'id' => 85937,
                    'name' => 'Demon Slayer: Kimetsu no Yaiba',
                    'title' => 'Demon Slayer: Kimetsu no Yaiba',
                    'poster_path' => '/xU7qi2vE2u6yXvX4Yh2c94Xv70Q.jpg',
                    'vote_average' => 8.7,
                    'genre_ids' => [16, 10759, 10765],
                    'overview' => 'It is the Taisho Period in Japan. Tanjiro, a kindhearted boy who sells charcoal for a living, finds his family slaughtered by a demon.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 129,
                'media_type' => 'movie',
                'title' => 'Spirited Away',
                'poster_path' => '/39wmItIWsg5sZMyRUHLkWBcuVCM.jpg',
                'vote_average' => 8.5,
                'genre_ids' => ',16,14,10751,',
                'data' => json_encode([
                    'id' => 129,
                    'title' => 'Spirited Away',
                    'name' => 'Spirited Away',
                    'poster_path' => '/39wmItIWsg5sZMyRUHLkWBcuVCM.jpg',
                    'vote_average' => 8.5,
                    'genre_ids' => [16, 14, 10751],
                    'overview' => 'A young girl, Chihiro, becomes trapped in a strange new world of spirits.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 372058,
                'media_type' => 'movie',
                'title' => 'Your Name.',
                'poster_path' => '/q719jXXEzOoYaps6qFsR65q4i.jpg',
                'vote_average' => 8.5,
                'genre_ids' => ',16,10749,18,',
                'data' => json_encode([
                    'id' => 372058,
                    'title' => 'Your Name.',
                    'name' => 'Your Name.',
                    'poster_path' => '/q719jXXEzOoYaps6qFsR65q4i.jpg',
                    'vote_average' => 8.5,
                    'genre_ids' => [16, 10749, 18],
                    'overview' => 'High schoolers Mitsuha and Taki are complete strangers living separate lives until they suddenly switch bodies.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 13916,
                'media_type' => 'tv',
                'title' => 'Death Note',
                'poster_path' => '/iigTJJskR1Pcjjq0tUq2s9sKk1b.jpg',
                'vote_average' => 8.6,
                'genre_ids' => ',16,9648,10765,',
                'data' => json_encode([
                    'id' => 13916,
                    'name' => 'Death Note',
                    'title' => 'Death Note',
                    'poster_path' => '/iigTJJskR1Pcjjq0tUq2s9sKk1b.jpg',
                    'vote_average' => 8.6,
                    'genre_ids' => [16, 9648, 10765],
                    'overview' => 'Light Yagami is an ace student who finds the Death Note, a notebook dropped by a rogue Shinigami death god.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 95479,
                'media_type' => 'tv',
                'title' => 'Jujutsu Kaisen',
                'poster_path' => '/hFWqu9yJepq9hKqB8i2w6m5Qe5V.jpg',
                'vote_average' => 8.6,
                'genre_ids' => ',16,10759,10765,',
                'data' => json_encode([
                    'id' => 95479,
                    'name' => 'Jujutsu Kaisen',
                    'title' => 'Jujutsu Kaisen',
                    'poster_path' => '/hFWqu9yJepq9hKqB8i2w6m5Qe5V.jpg',
                    'vote_average' => 8.6,
                    'genre_ids' => [16, 10759, 10765],
                    'overview' => 'Yuji Itadori is a boy with tremendous physical strength, though he lives a completely ordinary high school life.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 37854,
                'media_type' => 'tv',
                'title' => 'One Piece',
                'poster_path' => '/fcXdJlbSdUEeMSWig0PfCw8f05.jpg',
                'vote_average' => 8.7,
                'genre_ids' => ',16,10759,35,',
                'data' => json_encode([
                    'id' => 37854,
                    'name' => 'One Piece',
                    'title' => 'One Piece',
                    'poster_path' => '/fcXdJlbSdUEeMSWig0PfCw8f05.jpg',
                    'vote_average' => 8.7,
                    'genre_ids' => [16, 10759, 35],
                    'overview' => 'Monkey D. Luffy and his swashbuckling crew in search of the ultimate treasure, the One Piece.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 128,
                'media_type' => 'movie',
                'title' => 'Princess Mononoke',
                'poster_path' => '/cMYCDADoLKLbB83gpe190N9ao4u.jpg',
                'vote_average' => 8.3,
                'genre_ids' => ',16,12,14,',
                'data' => json_encode([
                    'id' => 128,
                    'title' => 'Princess Mononoke',
                    'name' => 'Princess Mononoke',
                    'poster_path' => '/cMYCDADoLKLbB83gpe190N9ao4u.jpg',
                    'vote_average' => 8.3,
                    'genre_ids' => [16, 12, 14],
                    'overview' => 'Ashitaka, a prince of the disappearing Emishi people, is cursed by a demonized boar god.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 4935,
                'media_type' => 'movie',
                'title' => "Howl's Moving Castle",
                'poster_path' => '/6p0v2UoB7QzOqB4iS7w2o0L6k9v.jpg',
                'vote_average' => 8.4,
                'genre_ids' => ',16,14,12,',
                'data' => json_encode([
                    'id' => 4935,
                    'title' => "Howl's Moving Castle",
                    'name' => "Howl's Moving Castle",
                    'poster_path' => '/6p0v2UoB7QzOqB4iS7w2o0L6k9v.jpg',
                    'vote_average' => 8.4,
                    'genre_ids' => [16, 14, 12],
                    'overview' => 'Sophie, a young milliner, is turned into an elderly woman by a witch and finds work in the walking castle of the wizard Howl.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tmdb_id' => 378064,
                'media_type' => 'movie',
                'title' => 'A Silent Voice: The Movie',
                'poster_path' => '/tuFaWiqX0TXR8xM7tO0i0x9F4q.jpg',
                'vote_average' => 8.4,
                'genre_ids' => ',16,18,10749,',
                'data' => json_encode([
                    'id' => 378064,
                    'title' => 'A Silent Voice: The Movie',
                    'name' => 'A Silent Voice: The Movie',
                    'poster_path' => '/tuFaWiqX0TXR8xM7tO0i0x9F4q.jpg',
                    'vote_average' => 8.4,
                    'genre_ids' => [16, 18, 10749],
                    'overview' => 'Shoya Ishida starts bullying the new girl in class, Shoko Nishimiya, because she is deaf.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('media')->insertOrIgnore($animeSeeds);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No destructive reverse needed for seeds
    }
};

