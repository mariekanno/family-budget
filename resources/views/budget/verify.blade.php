@extends('budget.layout')
@section('content')
<section><h1>メールアドレスの確認</h1><p>{{ auth()->user()->email }} に届いた確認メールのリンクを開いてください。確認するまで家計簿は利用できません。</p><form method="post" action="{{ route('verification.send') }}">@csrf<button>確認メールを再送する</button></form></section>
@endsection