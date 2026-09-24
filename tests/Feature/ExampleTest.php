<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/genre/')) {
                return Http::response([
                    'genres' => [
                        ['id' => 16, 'name' => 'Animation'],
                    ],
                ], 200);
            }
            return Http::response([
                'results' => [],
            ], 200);
        });

        $response = $this->get('/');
        $response->assertOk();
    }
}
