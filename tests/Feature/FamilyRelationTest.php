<?php

namespace Tests\Feature;

use App\Enums\FamilyRole;
use App\Models\Family;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_belongs_to_creator(): void
    {
        $user = User::factory()->create([
            'name' => '佐藤',
        ]);

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $this->assertEquals(
            '佐藤',
            $family->creator->name
        );
    }

    public function test_family_has_users(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'member',
        ]);

        $users = $family->users;
        $member = $users->first();

        $this->assertEquals(
            1,
            $users->count()
        );

        $this->assertSame(
            FamilyRole::Member,
            $member->pivot->role
        );
    }

    public function test_user_belongs_to_family(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'member',
        ]);

        $families = $user->families;

        $this->assertEquals(
            $family->id,
            $families->first()->id
        );
    }

    public function test_user_cannot_join_multiple_families(): void
    {
        $user = User::factory()->create();

        $family1 = new Family;
        $family1->name = '田中家';
        $family1->created_by = $user->id;
        $family1->save();

        $family2 = new Family;
        $family2->name = '佐藤家';
        $family2->created_by = $user->id;
        $family2->save();

        $family1->users()->attach($user->id, [
            'role' => 'member',
        ]);

        $this->expectException(QueryException::class);

        $family2->users()->attach($user->id, [
            'role' => 'member',
        ]);
    }

    public function test_cannot_attach_same_user_twice(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'member',
        ]);

        $this->expectException(QueryException::class);

        $family->users()->attach($user->id, [
            'role' => 'member',
        ]);
    }

    public function test_can_update_family_member_role(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'member',
        ]);

        $family->users()->updateExistingPivot($user->id, [
            'role' => 'admin',
        ]);

        $member = $family->users()->first();

        $this->assertSame(
            FamilyRole::Admin,
            $member->pivot->role
        );
    }

    public function test_can_detach_family_member(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'member',
        ]);

        $family->users()->detach($user->id);

        $this->assertEquals(
            0,
            $family->users()->count()
        );

        $this->assertNotNull(
            User::find($user->id)
        );
    }

    public function test_user_has_created_families(): void
    {
        $user = User::factory()->create();

        $family1 = new Family;
        $family1->name = '田中家';
        $family1->created_by = $user->id;
        $family1->save();

        $family2 = new Family;
        $family2->name = '佐藤家';
        $family2->created_by = $user->id;
        $family2->save();

        $families = $user->createdFamilies;

        $this->assertEquals(2, $families->count());
    }

    public function test_admin_role_is_cast_to_enum(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'admin',
        ]);

        $admin = $family->users()->first();

        $this->assertSame(
            FamilyRole::Admin,
            $admin->pivot->role
        );
    }
}
