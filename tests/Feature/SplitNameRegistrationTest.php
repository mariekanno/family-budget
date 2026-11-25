<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitNameRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_stores_names_separately(): void
    {
        $this->post('/register', [
            'last_name'=>'山田', 'first_name'=>'花子',
            'email'=>'split-name@example.com',
            'password'=>'test-password-123',
            'password_confirmation'=>'test-password-123',
        ])->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email'=>'split-name@example.com',
            'last_name'=>'山田', 'first_name'=>'花子', 'name'=>'山田 花子',
        ]);
        $this->assertAuthenticated();
    }

    public function test_both_names_are_required(): void
    {
        $this->post('/register', [
            'last_name'=>'山田', 'email'=>'split-name@example.com',
            'password'=>'test-password-123',
            'password_confirmation'=>'test-password-123',
        ])->assertSessionHasErrors('first_name');
        $this->assertGuest();
    }
}
