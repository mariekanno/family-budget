<div class="grid">
<label>日付<input type="date" name="transaction_date" value="{{ $t->transaction_date ?? now('Asia/Tokyo')->format('Y-m-d') }}" required></label>
<label>カテゴリ<select name="category_id" required>@foreach($categories->where('is_active',true) as $c)<option value="{{ $c->id }}" @selected(($t->category_id ?? null)==$c->id)>{{ $c->type==='income'?'収入':'支出' }} / {{ $c->name }}</option>@endforeach</select></label>
<label>金額（円）<input type="number" name="amount" min="1" max="999999999" value="{{ $t->amount ?? '' }}" required></label>
<label>内容<input name="description" maxlength="100" value="{{ $t->description ?? '' }}" required></label>
<label>区分<select name="scope"><option value="family" @selected(($t->scope ?? 'family')==='family')>家族</option><option value="personal" @selected(($t->scope ?? '')==='personal')>個人</option></select></label>
<label>公開設定（家族分は共有）<select name="visibility"><option value="shared" @selected(($t->visibility ?? 'shared')==='shared')>共有</option><option value="private" @selected(($t->visibility ?? '')==='private')>自分のみ</option></select></label>
</div><label>メモ<input name="memo" maxlength="2000" value="{{ $t->memo ?? '' }}"></label>