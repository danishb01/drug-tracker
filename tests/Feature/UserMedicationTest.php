<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RxNormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserMedicationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test getting user medications
     */
    public function test_get_user_medications(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/medications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test adding medication to user list
     */
    public function test_add_medication_to_user_list(): void
    {
        $drugDetails = [
            'rxcui' => '12345',
            'drug_name' => 'Aspirin 325 MG Oral Tablet',
            'base_names' => ['Aspirin'],
            'dose_form_group_names' => ['Oral Tablet'],
        ];

        $this->mock(RxNormService::class, function ($mock) use ($drugDetails) {
            $mock->shouldReceive('validateRxcui')
                ->with('12345')
                ->once()
                ->andReturn(true);
            
            $mock->shouldReceive('getDrugDetails')
                ->with('12345')
                ->once()
                ->andReturn($drugDetails);
        });

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/medications', [
            'rxcui' => '12345'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'medication' => [
                    'id',
                    'rxcui',
                    'drug_name',
                    'base_names',
                    'dose_form_group_names',
                ]
            ]);
    }

    /**
     * Test adding duplicate medication
     */
    public function test_add_duplicate_medication(): void
    {
        $drugDetails = [
            'rxcui' => '12345',
            'drug_name' => 'Aspirin 325 MG Oral Tablet',
            'base_names' => ['Aspirin'],
            'dose_form_group_names' => ['Oral Tablet'],
        ];

        $this->mock(RxNormService::class, function ($mock) use ($drugDetails) {
            $mock->shouldReceive('validateRxcui')
                ->with('12345')
                ->once()
                ->andReturn(true);
            
            $mock->shouldReceive('getDrugDetails')
                ->with('12345')
                ->once()
                ->andReturn($drugDetails);
        });

        // Add medication first time
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/medications', ['rxcui' => '12345']);

        // Try to add same medication again
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/medications', ['rxcui' => '12345']);

        $response->assertStatus(400)
            ->assertJson(['message' => 'This medication is already in your list.']);
    }

    /**
     * Test removing medication from user list
     */
    public function test_remove_medication_from_user_list(): void
    {
        $drugDetails = [
            'rxcui' => '12345',
            'drug_name' => 'Aspirin 325 MG Oral Tablet',
            'base_names' => ['Aspirin'],
            'dose_form_group_names' => ['Oral Tablet'],
        ];

        $this->mock(RxNormService::class, function ($mock) use ($drugDetails) {
            $mock->shouldReceive('validateRxcui')
                ->with('12345')
                ->once()
                ->andReturn(true);
            
            $mock->shouldReceive('getDrugDetails')
                ->with('12345')
                ->once()
                ->andReturn($drugDetails);
        });

        // Add medication first
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/medications', ['rxcui' => '12345']);

        // Remove medication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/medications', ['rxcui' => '12345']);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Medication removed successfully']);
    }

    /**
     * Test removing non-existent medication
     */
    public function test_remove_nonexistent_medication(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/medications', ['rxcui' => '99999']);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Medication not found in your list.']);
    }

    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access(): void
    {
        $response = $this->getJson('/api/medications');
        $response->assertStatus(401);
    }
}
