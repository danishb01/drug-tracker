<?php

namespace Tests\Feature;

use App\Services\RxNormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class DrugSearchTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test drug search with valid input
     */
    public function test_drug_search_with_valid_input(): void
    {
        $mockData = [
            [
                'rxcui' => '12345',
                'drug_name' => 'Aspirin 325 MG Oral Tablet',
                'base_names' => ['Aspirin'],
                'dose_form_group_names' => ['Oral Tablet'],
            ]
        ];

        $this->mock(RxNormService::class, function ($mock) use ($mockData) {
            $mock->shouldReceive('searchDrugs')
                ->with('aspirin')
                ->once()
                ->andReturn($mockData);
        });

        $response = $this->getJson('/api/drugs/search?drug_name=aspirin');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'rxcui',
                        'drug_name',
                        'base_names',
                        'dose_form_group_names',
                    ]
                ]
            ]);
    }

    /**
     * Test drug search validation
     */
    public function test_drug_search_validation(): void
    {
        $response = $this->getJson('/api/drugs/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['drug_name']);
    }

    /**
     * Test drug search with empty results
     */
    public function test_drug_search_with_empty_results(): void
    {
        $this->mock(RxNormService::class, function ($mock) {
            $mock->shouldReceive('searchDrugs')
                ->with('nonexistent')
                ->once()
                ->andReturn([]);
        });

        $response = $this->getJson('/api/drugs/search?drug_name=nonexistent');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    /**
     * Test rate limiting
     */
    public function test_rate_limiting(): void
    {
        $this->mock(RxNormService::class, function ($mock) {
            $mock->shouldReceive('searchDrugs')
                ->times(10)
                ->andReturn([]);
        });

        // Make 10 requests (should succeed)
        for ($i = 0; $i < 10; $i++) {
            $this->getJson('/api/drugs/search?drug_name=test');
        }

        // 11th request should be rate limited
        $response = $this->getJson('/api/drugs/search?drug_name=test');
        $response->assertStatus(429);
    }
}
