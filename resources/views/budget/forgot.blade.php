@extends('budget.layout')
@section('content')
<section><h1>パスワードの再設定</h1><form method="post" action="{{ route('password.email') }}">@csrf<label>登録メールアドレス<input name="email" type="email" required autocomplete="email"></label><button>再設定メールを送信</button></form><p><a href="/">ログインに戻る</a></p></section>
@endsection