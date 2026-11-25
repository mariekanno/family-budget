@extends('budget.layout')
@section('content')
<div class="shell"><nav class="nav" aria-label="家計簿メニュー">
@foreach(['family'=>'家族の家計簿','personal'=>'自分の家計簿','entry'=>'収支を登録','records'=>'明細一覧','settings'=>'共有・CSV設定'] as $key=>$label)
<a href="{{ route('home',['tab'=>$key,'month'=>$month]) }}" @if($tab===$key) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav><div class="maincol">
<h1>{{ $family->name }} の家計簿</h1>
<form method="get" action="/" class="monthform"><input type="hidden" name="tab" value="{{ $tab }}"><input aria-label="表示月" type="month" name="month" value="{{ $month }}" required><button>表示</button></form>
@php
$shared=$rows->where('scope','family');
$personal=$rows->where('scope','personal')->where('user_id',auth()->id());
$summary=$tab==='personal'?$personal:$shared;
$income=$summary->where('type','income')->sum('amount');
$expense=$summary->where('type','expense')->sum('amount');
$parts=$summary->where('type','expense')->groupBy('category')->map(fn($group)=>$group->sum('amount'))->sortDesc();
$palette=['#20624f','#4b91a4','#d8a34b','#9275ad','#cb7868','#74966b','#64748b'];
$segments=[];$legend=[];$start=0;
foreach($parts as $label=>$amount){
 $end=$start+($expense>0?$amount/$expense*100:0);
 $color=$palette[count($legend)%count($palette)];
 $segments[]=$color.' '.$start.'% '.$end.'%';
 $legend[]=[$label,$amount,$color];$start=$end;
}
@endphp
@if(in_array($tab,['family','personal'],true))
<section><h2>{{ $tab==='family'?'家族の収支':'自分の収支（個人分）' }}</h2><div class="grid"><div class="stat income">収入<strong>¥{{ number_format($income) }}</strong></div><div class="stat expense">支出<strong>¥{{ number_format($expense) }}</strong></div><div class="stat">残高<strong>¥{{ number_format($income-$expense) }}</strong></div></div></section>
<section><h2>支出の内訳</h2>@if($expense>0)<div class="chartarea"><div class="donut" aria-hidden="true" style="background:conic-gradient({{ implode(',',$segments) }})"></div><div class="legend">@foreach($legend as [$label,$amount,$color])<div class="legendline"><span><span class="dot" style="background:{{ $color }}"></span>{{ $label }}</span><strong>¥{{ number_format($amount) }} ({{ round($amount/$expense*100) }}%)</strong></div>@endforeach</div></div>@else<p>この月の支出はまだありません。</p>@endif</section>
<a class="btn" href="{{ route('home',['tab'=>'entry','month'=>$month]) }}">＋ 収支を登録</a>
@if($tab==='family')<div style="margin-top:18px"><section><h2>予算と実績（家族分）</h2>
@foreach($categories->where('type','expense') as $c)
@php $actual=$shared->where('type','expense')->where('category_id',$c->id)->sum('amount'); $limit=$budgets[$c->id]??null; @endphp
<div style="margin:12px 0"><div>{{ $c->name }}　¥{{ number_format($actual) }} @if($limit!==null) / ¥{{ number_format($limit) }} @endif</div><div class="bar"><span style="width:{{ min(100,$limit!==null&&$limit>0?$actual/$limit*100:($expense>0?$actual/$expense*100:0)) }}%"></span></div></div>
@endforeach
@if($m->role==='admin')<details><summary>予算を設定</summary><form method="post" action="/budget">@csrf<input type="hidden" name="month" value="{{ $month }}"><label>カテゴリ<select name="category_id">@foreach($categories->where('type','expense') as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label><label>予算（円）<input type="number" name="amount" min="0" max="999999999" required></label><button>保存</button></form></details>@endif</section>
</div>@endif
@elseif($tab==='entry')
<section><h2>収支を登録</h2><form method="post" action="/transactions">@csrf @include('budget.fields',['t'=>null])<button>登録する</button></form></section>
@elseif($tab==='records')
<section><h2>明細</h2><div class="scroll"><table><thead><tr><th>日付</th><th>内容</th><th>カテゴリ</th><th>金額</th><th>区分</th><th>登録者</th></tr></thead><tbody>@forelse($rows as $t)<tr><td>{{ $t->transaction_date }}</td><td>{{ $t->description }}</td><td>{{ $t->category }}</td><td><span class="{{ $t->type==='expense'?'negative':'positive' }}">{{ $t->type==='expense'?'−':'+' }}¥{{ number_format($t->amount) }}</span></td><td>{{ $t->scope==='family'?'家族':($t->visibility==='private'?'個人・非公開':'個人・共有') }}</td><td>{{ $t->owner }}</td></tr>
@if($t->user_id==auth()->id())<tr><td colspan="6"><details><summary>編集・削除</summary><form method="post" action="/transactions/{{ $t->id }}">@csrf @include('budget.fields',['t'=>$t])<button>更新</button></form><form method="post" action="/transactions/{{ $t->id }}">@csrf @method('DELETE')<details><summary>削除する場合は開く</summary><button class="danger">明細を削除</button></details></form></details></td></tr>@endif
@empty<tr><td colspan="6">まだ記録がありません。</td></tr>@endforelse</tbody></table></div></section>

@elseif($tab==='settings')
<section><h2>CSV</h2><a class="btn" href="/export?month={{ $month }}">表示月の明細を出力</a><form method="post" action="/import" enctype="multipart/form-data">@csrf<label>CSV取り込み（UTF-8、出力と同じ列順、最大1000行）<input type="file" name="csv" accept=".csv,text/csv" required></label><p><small>取り込んだ明細は自分の登録になります。同じファイルの再登録は防止しますが、既存明細との重複は確認してください。</small></p><button>取り込み</button></form></section>
@if($m->role==='admin')<section><h2>家族を招待</h2><form method="post" action="/invite">@csrf<label>相手が登録するメールアドレス<input name="email" type="email" required></label><button>招待コードを発行</button></form>@if(session('invite_token'))<p>以下のコードを相手に安全な方法で送ってください（7日間有効）。</p><textarea readonly>{{ session('invite_token') }}</textarea>@endif</section>@endif

@endif
</div></div>
@endsection