@extends('budget.layout')
@section('content')
<section><h1>新しいパスワード</h1><form method="post" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><label>メールアドレス<input name="email" type="email" value="{{ $email }}" required></label><label>新しいパスワード（12文字以上）<input name="password" type="password" required minlength="12" maxlength="72" autocomplete="new-password"></label><label>確認<input name="password_confirmation" type="password" required autocomplete="new-password"></label><button>変更する</button></form></section>
@endsection