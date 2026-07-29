<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'role' => 'recipient',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+491234567890',
            'city' => 'Berlin',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('recipient.main_page', absolute: false));
    }

    public function test_new_donor_can_register(): void
    {
        $response = $this->post('/register', [
            'role' => 'donor',
            'name' => 'Test Donor',
            'email' => 'donor@example.com',
            'phone' => '+491234567891',
            'city' => 'Berlin',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('donor.main_page', absolute: false));
    }
}
