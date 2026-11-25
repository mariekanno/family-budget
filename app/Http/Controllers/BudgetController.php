<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BudgetController extends Controller
{
    private function member() {
        return DB::table('family_user')->where('user_id',Auth::id())->first();
    }
    private function membership() {
        $m=$this->member(); abort_unless($m,403); return $m;
    }
    private function visible($m) {
        return DB::table('transactions')->where('transactions.family_id',$m->family_id)
            ->where(function($q){$q->where('transactions.visibility','shared')->orWhere('transactions.user_id',Auth::id());});
    }
    private function month(Request $r) {
        $month=$r->input('month',now('Asia/Tokyo')->format('Y-m'));
        abort_unless(is_string($month)&&preg_match('/^(20[0-9]{2}|2100)-(0[1-9]|1[0-2])$/D',$month),422);
        return $month;
    }
    public function home(Request $r) {
        if (!Auth::check()) return view('budget.auth');
        $m=$this->member();
        if (!$m) return view('budget.family');
        $month=$this->month($r);
        $tab=$r->query('tab','family');
        abort_unless(is_string($tab)&&in_array($tab,['family','personal','entry','records','settings'],true),404);
        $categories=DB::table('categories')->where('family_id',$m->family_id)->orderBy('sort_order')->get();
        $rows=$this->visible($m)->where('transaction_date','like',$month.'-%')
            ->join('categories','categories.id','=','transactions.category_id')
            ->join('users','users.id','=','transactions.user_id')
            ->select('transactions.*','categories.name as category','users.name as owner')
            ->orderByDesc('transaction_date')->orderByDesc('transactions.id')->get();
        $budgets=DB::table('budgets')->where('family_id',$m->family_id)->where('year',(int)substr($month,0,4))->where('month',(int)substr($month,5,2))->pluck('amount','category_id');
        $family=DB::table('families')->find($m->family_id);
        return view('budget.home',compact('m','month','categories','rows','budgets','family','tab'));
    }
    public function register(Request $r) {
        $r->merge(['email'=>is_string($r->input('email'))?strtolower($r->input('email')):$r->input('email')]);
        $d=$r->validate(['last_name'=>'required|string|max:50','first_name'=>'required|string|max:50','last_name_kana'=>['required','string','max:50','regex:/\A[ァ-ヺー・]+\z/u'],'first_name_kana'=>['required','string','max:50','regex:/\A[ァ-ヺー・]+\z/u'],'email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:12|max:72|confirmed']);
        $u=User::create(['last_name'=>$d['last_name'],'first_name'=>$d['first_name'],'last_name_kana'=>$d['last_name_kana'],'first_name_kana'=>$d['first_name_kana'],'name'=>$d['last_name'].' '.$d['first_name'],'email'=>strtolower($d['email']),'password'=>Hash::make($d['password'])]);
        Auth::login($u); $r->session()->regenerate(); return redirect('/');
    }
    public function login(Request $r) {
        $d=$r->validate(['email'=>'required|email','password'=>'required|string']);
        if (!Auth::attempt(['email'=>strtolower($d['email']),'password'=>$d['password']])) throw ValidationException::withMessages(['email'=>'メールアドレスまたはパスワードが違います。']);
        $r->session()->regenerate(); return redirect('/');
    }
    public function logout(Request $r) {
        Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken(); return redirect('/');
    }
    public function family(Request $r) {
        $d=$r->validate(['name'=>'required|string|max:100']);
        DB::transaction(function()use($d){
            DB::table('users')->where('id',Auth::id())->lockForUpdate()->first();
            abort_if($this->member(),409);
            $f=DB::table('families')->insertGetId(['name'=>$d['name'],'created_by'=>Auth::id(),'created_at'=>now(),'updated_at'=>now()]);
            DB::table('family_user')->insert(['family_id'=>$f,'user_id'=>Auth::id(),'role'=>'admin','created_at'=>now(),'updated_at'=>now()]);
            foreach(['income'=>['給与','賞与','その他収入'],'expense'=>['食費','日用品','住居費','水道光熱費','通信費','交通費','保育・教育','医療費','保険','娯楽','衣服','その他']] as $type=>$names)
                foreach($names as $i=>$name) DB::table('categories')->insert(['family_id'=>$f,'name'=>$name,'normalized_name'=>$name,'type'=>$type,'sort_order'=>$i,'created_at'=>now(),'updated_at'=>now()]);
        });
        return redirect('/');
    }
    public function invite(Request $r) {
        $m=$this->membership(); abort_unless($m->role==='admin',403);
        $d=$r->validate(['email'=>'required|email|max:255']);
        $token=Str::random(64);
        DB::table('invitations')->insert(['family_id'=>$m->family_id,'invited_by'=>Auth::id(),'email'=>strtolower($d['email']),'token_hash'=>hash('sha256',$token),'expires_at'=>now()->addDays(7),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('invite_token',$token);
    }
    public function join(Request $r) {
        $d=$r->validate(['token'=>'required|string|size:64']);
        DB::transaction(function()use($d){
            DB::table('users')->where('id',Auth::id())->lockForUpdate()->first();
            abort_if($this->member(),409);
            $i=DB::table('invitations')->where('token_hash',hash('sha256',$d['token']))->lockForUpdate()->first();
            abort_unless($i&&$i->status==='pending'&&$i->expires_at>now()->toDateTimeString()&&strtolower($i->email)===strtolower(Auth::user()->email),422,'無効または期限切れの招待です。');
            DB::table('family_user')->insert(['family_id'=>$i->family_id,'user_id'=>Auth::id(),'role'=>'member','created_at'=>now(),'updated_at'=>now()]);
            DB::table('invitations')->where('id',$i->id)->update(['status'=>'accepted','accepted_at'=>now(),'updated_at'=>now()]);
        });
        return redirect('/');
    }
    private function data(array $input,$m) {
        $d=validator($input,[
            'category_id'=>['required','integer',Rule::exists('categories','id')->where('family_id',$m->family_id)->where('is_active',true)],
            'amount'=>'required|integer|min:1|max:999999999','transaction_date'=>'required|date_format:Y-m-d|after_or_equal:2000-01-01|before_or_equal:2100-12-31',
            'description'=>'required|string|max:100','memo'=>'nullable|string|max:2000',
            'scope'=>'required|in:family,personal','visibility'=>'required|in:shared,private',
        ])->validate();
        $d['type']=DB::table('categories')->where('id',$d['category_id'])->value('type');
        if($d['scope']==='family' && $d['visibility']!=='shared') throw ValidationException::withMessages(['visibility'=>'家族の明細は共有を選択してください。']);
        return $d;
    }
    public function store(Request $r) {
        $m=$this->membership();
        DB::table('transactions')->insert($this->data($r->all(),$m)+['family_id'=>$m->family_id,'user_id'=>Auth::id(),'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('home',['tab'=>$r->input('scope')==='personal'?'personal':'family','month'=>substr($r->input('transaction_date'),0,7)])->with('notice','登録しました。');
    }
    private function owned($m,$id) {return DB::table('transactions')->where('family_id',$m->family_id)->where('user_id',Auth::id())->where('id',$id);}
    public function update(Request $r,$id) {
        $m=$this->membership(); $q=$this->owned($m,$id); abort_unless($q->exists(),404);
        $q->update($this->data($r->all(),$m)+['updated_at'=>now()]); return back()->with('notice','更新しました。');
    }
    public function destroy($id) {
        $m=$this->membership(); abort_unless($this->owned($m,$id)->delete(),404); return back()->with('notice','削除しました。');
    }
    public function budget(Request $r) {
        $m=$this->membership(); abort_unless($m->role==='admin',403);
        $month=$this->month($r);
        $d=$r->validate(['category_id'=>['required','integer',Rule::exists('categories','id')->where('family_id',$m->family_id)->where('type','expense')],'amount'=>'required|integer|min:0|max:999999999']);
        DB::table('budgets')->upsert([['family_id'=>$m->family_id,'category_id'=>$d['category_id'],'year'=>(int)substr($month,0,4),'month'=>(int)substr($month,5,2),'amount'=>$d['amount'],'created_at'=>now(),'updated_at'=>now()]],['family_id','category_id','year','month'],['amount','updated_at']);
        return back()->with('notice','予算を保存しました。');
    }
    public function export(Request $r) {
        $m=$this->membership(); $month=$this->month($r);
        $rows=$this->visible($m)->where('transaction_date','like',$month.'-%')->join('categories','categories.id','=','transactions.category_id')->select('transactions.*','categories.name as category')->get();
        return response()->streamDownload(function()use($rows){
            $f=fopen('php://output','w'); fwrite($f,"\xEF\xBB\xBF");
            fputcsv($f,['date','category','amount','description','scope','visibility','memo']);
            foreach($rows as $t){
                $safe=fn($s)=>preg_match('/^[\s]*[=+\-@]/u',(string)$s)?"'".$s:$s;
                fputcsv($f,[$t->transaction_date,$safe($t->category),$t->amount,$safe($t->description),$t->scope,$t->visibility,$safe($t->memo)]);
            } fclose($f);
        },'family-budget-'.$month.'.csv',['Content-Type'=>'text/csv; charset=UTF-8','Cache-Control'=>'no-store, private']);
    }
    public function import(Request $r) {
        $m=$this->membership(); $r->validate(['csv'=>'required|file|max:1024']);
        $path=$r->file('csv')->getRealPath(); $hash=hash_file('sha256',$path);
        $f=fopen($path,'r'); $header=fgetcsv($f); if($header)$header[0]=preg_replace('/^\xEF\xBB\xBF/','',$header[0]);
        $expected=['date','category','amount','description','scope','visibility','memo'];
        if($header!==$expected){fclose($f);throw ValidationException::withMessages(['csv'=>'CSVの見出しが正しくありません。出力ファイルの形式を使用してください。']);}
        $rows=[];
        try {
            while(($row=fgetcsv($f))!==false){
                if(count($row)!==7||count($rows)>=1000)throw ValidationException::withMessages(['csv'=>'列数または行数が不正です（最大1000行）。']);
                $a=array_combine($expected,$row);
                $ids=DB::table('categories')->where('family_id',$m->family_id)->where('name',$a['category'])->pluck('id');
                if($ids->count()!==1)throw ValidationException::withMessages(['csv'=>'カテゴリが不明または重複しています。']);
                $rows[]=$this->data(['category_id'=>$ids->first(),'transaction_date'=>$a['date'],'amount'=>$a['amount'],'description'=>$a['description'],'scope'=>$a['scope'],'visibility'=>$a['visibility'],'memo'=>$a['memo']],$m);
            }
        } finally {fclose($f);}
        if(!$rows)throw ValidationException::withMessages(['csv'=>'データがありません。']);
        DB::transaction(function()use($rows,$hash,$m,$r){
            DB::table('users')->where('id',Auth::id())->lockForUpdate()->first();
            if(DB::table('imports')->where('user_id',Auth::id())->where('fingerprint',$hash)->where('status','completed')->exists())throw ValidationException::withMessages(['csv'=>'同じファイルは取り込み済みです。']);
            $id=DB::table('imports')->insertGetId(['family_id'=>$m->family_id,'user_id'=>Auth::id(),'original_filename'=>mb_substr($r->file('csv')->getClientOriginalName(),0,255),'fingerprint'=>$hash,'status'=>'completed','total_rows'=>count($rows),'imported_rows'=>count($rows),'confirmed_at'=>now(),'completed_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            foreach($rows as $row)DB::table('transactions')->insert($row+['family_id'=>$m->family_id,'user_id'=>Auth::id(),'import_id'=>$id,'created_at'=>now(),'updated_at'=>now()]);
        });
        return back()->with('notice',count($rows).'件取り込みました。');
    }
}
