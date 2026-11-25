<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BudgetPrivacyTest extends TestCase
{
    use RefreshDatabase;
    public function test_private_records_are_not_visible_or_editable_by_other_members(): void
    {
        $a=User::factory()->create(); $b=User::factory()->create(); $c=User::factory()->create();
        $this->actingAs($a)->post('/family',['name'=>'Family A'])->assertRedirect('/');
        $family=DB::table('family_user')->where('user_id',$a->id)->value('family_id');
        DB::table('family_user')->insert(['family_id'=>$family,'user_id'=>$b->id,'role'=>'member']);
        $category=DB::table('categories')->where('family_id',$family)->where('type','expense')->first();
        $data=['category_id'=>$category->id,'amount'=>1234,'transaction_date'=>'2026-11-25','description'=>'PRIVATE-ONLY-TEST','scope'=>'personal','visibility'=>'private'];
        $this->post('/transactions',$data)->assertRedirect();
        $id=DB::table('transactions')->where('description',$data['description'])->value('id');
        $this->get('/?tab=records&month=2026-11')->assertSee($data['description']);
        $this->actingAs($b)->get('/?tab=records&month=2026-11')->assertDontSee($data['description']);
        $csv=$this->get('/export?month=2026-11');
        $csv->assertOk(); $this->assertStringNotContainsString($data['description'],$csv->streamedContent());
        $this->post('/transactions/'.$id,$data)->assertNotFound();
        $this->delete('/transactions/'.$id)->assertNotFound();
        $this->actingAs($c)->post('/family',['name'=>'Family B'])->assertRedirect('/');
        $this->get('/?tab=records&month=2026-11')->assertDontSee($data['description']);
        $this->post('/transactions',$data)->assertSessionHasErrors('category_id');
        $this->delete('/transactions/'.$id)->assertNotFound();
        $this->assertDatabaseHas('transactions',['id'=>$id,'amount'=>1234]);
    }
    public function test_family_private_conflict_is_rejected(): void
    {
        $a=User::factory()->create();
        $this->actingAs($a)->post('/family',['name'=>'Family'])->assertRedirect('/');
        $category=DB::table('categories')->where('type','expense')->first();
        $this->post('/transactions',['category_id'=>$category->id,'amount'=>100,'transaction_date'=>'2026-11-25','description'=>'conflict','scope'=>'family','visibility'=>'private'])->assertSessionHasErrors('visibility');
        $this->assertDatabaseCount('transactions',0);
    }
}
