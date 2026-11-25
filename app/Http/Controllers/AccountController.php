<?php
namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) return redirect('/');
        return view('budget.verify');
    }
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect('/')->with('notice','メールアドレスを確認しました。');
    }
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) return redirect('/');
        try { $request->user()->sendEmailVerificationNotification(); }
        catch (\Throwable $e) { report($e); return back()->withErrors(['email'=>'メールを送信できませんでした。']); }
        return back()->with('notice','確認メールを送信しました。');
    }
    public function forgot()
    {
        return view('budget.forgot');
    }
    public function sendReset(Request $request)
    {
        $d=$request->validate(['email'=>'required|string|email|max:255']);
        try { Password::sendResetLink(['email'=>strtolower($d['email'])]); }
        catch (\Throwable $e) { report($e); }
        return back()->with('notice','登録済みのアドレスであれば再設定メールが届きます。');
    }
    public function resetForm(Request $request, string $token)
    {
        return view('budget.reset',['token'=>$token,'email'=>is_string($request->query('email'))?$request->query('email'):'']);
    }
    public function reset(Request $request)
    {
        $d=$request->validate(['token'=>'required|string|max:255','email'=>'required|string|email|max:255','password'=>['required','string','min:12','max:72','confirmed',function($attribute,$value,$fail){if(strlen($value)>72)$fail('パスワードは72バイト以内にしてください。');}]]);
        $d['email']=strtolower($d['email']);
        $status=Password::reset($d,function($user,$password){
            $user->forceFill(['password'=>Hash::make($password)])->setRememberToken(Str::random(60));
            $user->save();
            $user->tokens()->delete();
            event(new PasswordReset($user));
        });
        if($status!==Password::PASSWORD_RESET)return back()->withErrors(['email'=>'再設定リンクが無効か期限切れです。もう一度申請してください。']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('notice','パスワードを変更しました。新しいパスワードでログインしてください。');
    }
}
