@extends('budget.layout')
@section('content')
<h1>{{ $family->name }} の家計簿</h1>
<form method="get" action="/" style="display:flex;gap:10px;max-width:340px;margin-bottom:18px"><input type="month" name="month" value="{{ $month }}" required><button>表示</button></form>
@php
$shared=$rows->where('scope','family');
$income=$shared->where('type','income')->sum('amount');
$expense=$shared->where('type','expense')->sum('amount');
$personal=$rows->where('scope','personal')->where('user_id',auth()->id());
@endphp
<section><h2>家族の収支</h2><div class="grid"><div class="stat">収入<strong>¥{{ number_format($income) }}</strong></div><div class="stat">支出<strong>¥{{ number_format($expense) }}</strong></div><div class="stat">残高<strong>¥{{ number_format($income-$expense) }}</strong></div></div>
<h3>自分の収支（個人分）</h3><p>収入 ¥{{ number_format($personal->where('type','income')->sum('amount')) }} ／ 支出 ¥{{ number_format($personal->where('type','expense')->sum('amount')) }}</p></section>
<section><h2>収支を登録</h2><form method="post" action="/transactions">@csrf @include('budget.fields',['t'=>null])<button>登録する</button></form></section>
<section><h2>カテゴリ別支出・予算（家族分）</h2>
@foreach($categories->where('type','expense') as $c)
@php $actual=$shared->where('type','expense')->where('category_id',$c->id)->sum('amount'); $limit=$budgets[$c->id]??null; @endphp
<div style="margin:12px 0"><div>{{ $c->name }}　¥{{ number_format($actual) }} @if($limit!==null) / ¥{{ number_format($limit) }} @endif</div><div class="bar"><span style="width:{{ min(100,$limit!==null&&$limit>0?$actual/$limit*100:($expense>0?$actual/$expense*100:0)) }}%"></span></div></div>
@endforeach
@if($m->role==='admin')<details><summary>予算を設定</summary><form method="post" action="/budget">@csrf<input type="hidden" name="month" value="{{ $month }}"><label>カテゴリ<select name="category_id">@foreach($categories->where('type','expense') as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label><label>予算（円）<input type="number" name="amount" min="0" max="999999999" required></label><button>保存</button></form></details>@endif</section>
<section><h2>明細</h2><div class="scroll"><table><thead><tr><th>日付</th><th>内容</th><th>カテゴリ</th><th>金額</th><th>区分</th><th>登録者</th></tr></thead><tbody>@forelse($rows as $t)<tr><td>{{ $t->transaction_date }}</td><td>{{ $t->description }}</td><td>{{ $t->category }}</td><td>{{ $t->type==='expense'?'−':'+' }}¥{{ number_format($t->amount) }}</td><td>{{ $t->scope==='family'?'家族':($t->visibility==='private'?'個人・非公開':'個人・共有') }}</td><td>{{ $t->owner }}</td></tr>
@if($t->user_id==auth()->id())<tr><td colspan="6"><details><summary>編集・削除</summary><form method="post" action="/transactions/{{ $t->id }}">@csrf @include('budget.fields',['t'=>$t])<button>更新</button></form><form method="post" action="/transactions/{{ $t->id }}" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')<button class="danger">削除</button></form></details></td></tr>@endif
@empty<tr><td colspan="6">まだ記録がありません。</td></tr>@endforelse</tbody></table></div></section>
<section><h2>CSV</h2><a class="btn" href="/export?month={{ $month }}">表示月の明細を出力</a><form method="post" action="/import" enctype="multipart/form-data">@csrf<label>CSV取り込み（UTF-8、出力と同じ列順、最大1000行）<input type="file" name="csv" accept=".csv,text/csv" required></label><p><small>取り込んだ明細は自分の登録になります。同じファイルの再登録は防止しますが、既存明細との重複は確認してください。</small></p><button>取り込み</button></form></section>
@if($m->role==='admin')<section><h2>家族を招待</h2><form method="post" action="/invite">@csrf<label>相手が登録するメールアドレス<input name="email" type="email" required></label><button>招待コードを発行</button></form>@if(session('invite_token'))<p>以下のコードを相手に安全な方法で送ってください（7日間有効）。</p><textarea readonly>{{ session('invite_token') }}</textarea>@endif</section>@endif
@endsection