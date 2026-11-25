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
            'last_name_kana'=>'ヤマダ', 'first_name_kana'=>'ハナコ',
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

    public function test_kana_must_be_full_width_katakana(): void
    {
        $this->post('/register', [
            'last_name'=>'山田', 'first_name'=>'花子',
            'last_name_kana'=>'やまだ', 'first_name_kana'=>'ﾊﾅｺ',
            'email'=>'kana@example.com',
            'password'=>'test-password-123',
            'password_confirmation'=>'test-password-123',
        ])->assertSessionHasErrors(['last_name_kana','first_name_kana']);
        $this->assertGuest();
    }

    public function test_both_names_are_required(): void
    {
        $this->post('/register', [
            'last_name'=>'山田', 'last_name_kana'=>'ヤマダ', 'first_name_kana'=>'ハナコ', 'email'=>'split-name@example.com',
            'password'=>'test-password-123',
            'password_confirmation'=>'test-password-123',
        ])->assertSessionHasErrors('first_name');
        $this->assertGuest();
    }
}
