@extends('budget.layout')
@section('content')
<h1>家計簿を始める</h1><div class="grid"><section><h2>家族を作成</h2><form method="post" action="/family">@csrf<label>家族名<input name="name" required maxlength="100"></label><button>作成</button></form></section><section><h2>招待を受ける</h2><form method="post" action="/join">@csrf<label>招待コード<input name="token" required maxlength="64"></label><button>参加</button></form></section></div>
@endsection