@extends('budget.layout')
@section('content')
<h1>家族のお金を、ひとつに。</h1><p>共有する家計と、自分だけの記録を管理できます。</p>
<div class="grid"><section><h2>ログイン</h2><form method="post" action="/login">@csrf<label>メールアドレス<input name="email" type="email" required autocomplete="email"></label><label>パスワード<input name="password" type="password" required autocomplete="current-password"></label><button>ログイン</button></form></section>
<section><h2>新規登録</h2><form method="post" action="/register">@csrf<label>名前<input name="name" required maxlength="100"></label><label>メールアドレス<input name="email" type="email" required></label><label>パスワード（12文字以上）<input name="password" type="password" required minlength="12" maxlength="72" autocomplete="new-password"></label><label>パスワード確認<input name="password_confirmation" type="password" required autocomplete="new-password"></label><button>登録する</button></form></section></div>
@endsection