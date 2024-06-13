<?php

namespace Tests\Unit;

use Tests\TestCase;

class UnauthenticatedFeaturesTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_get_job_listings_without_authentication()
    {
        $response = $this->getJson('/api/job-listings'); // Replace with your actual endpoint

        // Assert that the response has a successful status code (200 OK)
        $response->assertStatus(200);


        // Assert JSON structure and check for specific fields
        $response->assertJsonStructure([
            'code',
            'success',
            'message',
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]
        ]);

        // Assert specific values in the response JSON
        $response->assertJson([
            'code' => 200,
            'success' => true,
            'message' => 'List of the active job submissions!!',
        ]);

        // Assert that the 'data' field is an array and contains at least one item
        $responseData = $response->json();
        $this->assertTrue(is_array($responseData['data']['data']));
        $this->assertNotEmpty($responseData['data']['data']);
    }
}
