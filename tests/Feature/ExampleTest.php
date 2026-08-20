<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_cart_post_does_not_trigger_csrf_expiration(): void
    {
        $response = $this->post('/cart.php', [
            'menu_id' => 1,
            'qty' => 1,
        ]);

        $response->assertStatus(200);
    }
}
