<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;
    public function test_unverified_user_cannot_access_budget_until_verified(): void
    {
        $u=User::factory()->unverified()->create();
        Notification::fake();
        $this->actingAs($u)->get('/')->assertRedirect(route('verification.notice'));
        $this->post('/family',['name'=>'Blocked'])->assertRedirect(route('verification.notice'));
        $this->get('/export')->assertRedirect(route('verification.notice'));
        $this->post(route('verification.send'))->assertRedirect();
        Notification::assertSentTo($u,VerifyEmail::class);
        $this->post(route('verification.send'))->assertRedirect();
        $this->post(route('verification.send'))->assertRedirect();
        $this->post(route('verification.send'))->assertStatus(429);
        $link=URL::temporarySignedRoute('verification.verify',now()->addHour(),['id'=>$u->id,'hash'=>sha1($u->email)]);
        $this->get($link)->assertRedirect('/');
        $this->assertTrue($u->fresh()->hasVerifiedEmail());
        $this->post('/family',['name'=>'Allowed'])->assertRedirect('/');
    }
    public function test_reset_token_is_single_use(): void
    {
        $u=User::factory()->create();
        Notification::fake();
        $this->post(route('password.email'),['email'=>$u->email])->assertRedirect();
        Notification::assertSentTo($u,ResetPassword::class);
        $token=Password::createToken($u);
        $payload=['email'=>$u->email,'token'=>$token,'password'=>'new-password-123','password_confirmation'=>'new-password-123'];
        $this->post(route('password.update'),$payload)->assertRedirect('/');
        $this->assertTrue(Hash::check($payload['password'],$u->fresh()->password));
        $this->post(route('password.update'),$payload)->assertSessionHasErrors('email');
    }
}
