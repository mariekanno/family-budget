<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Models\Family;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_belongs_to_family(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'admin',
        ]);

        $invitation = new Invitation;

        $invitation->family_id = $family->id;
        $invitation->invited_by = $user->id;
        $invitation->email = 'test@example.com';
        $invitation->token_hash = str_repeat('a', 64);
        $invitation->status = InvitationStatus::Pending;
        $invitation->expires_at = now()->addDays(7);

        $invitation->save();

        $invitationFamily = $invitation->family;

        $this->assertEquals(
            $family->id,
            $invitationFamily->id
        );
    }

    public function test_invitation_belongs_to_inviter(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'admin',
        ]);

        $invitation = new Invitation;

        $invitation->family_id = $family->id;
        $invitation->invited_by = $user->id;
        $invitation->email = 'test@example.com';
        $invitation->token_hash = str_repeat('a', 64);
        $invitation->status = InvitationStatus::Pending;
        $invitation->expires_at = now()->addDays(7);

        $invitation->save();

        $inviter = $invitation->inviter;

        $this->assertEquals(
            $user->id,
            $inviter->id
        );
    }

    public function test_family_has_many_invitations(): void
    {
        $user = User::factory()->create();

        $family = new Family;
        $family->name = '佐藤家';
        $family->created_by = $user->id;
        $family->save();

        $family->users()->attach($user->id, [
            'role' => 'admin',
        ]);

        $invitation1 = new Invitation;
        $invitation1->family_id = $family->id;
        $invitation1->invited_by = $user->id;
        $invitation1->email = 'test1@example.com';
        $invitation1->token_hash = str_repeat('a', 64);
        $invitation1->status = InvitationStatus::Pending;
        $invitation1->expires_at = now()->addDays(7);
        $invitation1->save();

        $invitation2 = new Invitation;
        $invitation2->family_id = $family->id;
        $invitation2->invited_by = $user->id;
        $invitation2->email = 'test2@example.com';
        $invitation2->token_hash = str_repeat('b', 64);
        $invitation2->status = InvitationStatus::Pending;
        $invitation2->expires_at = now()->addDays(7);
        $invitation2->save();

        $invitations = $family->invitations;

        $this->assertCount(2, $invitations);

        $foundInvitation1 = $invitations->firstWhere(
            'email',
            'test1@example.com'
        );

        $this->assertNotNull($foundInvitation1);

        $this->assertEquals(
            $invitation1->id,
            $foundInvitation1->id
        );

        $foundInvitation2 = $invitations->firstWhere(
            'email',
            'test2@example.com'
        );

        $this->assertNotNull($foundInvitation2);

        $this->assertEquals(
            $invitation2->id,
            $foundInvitation2->id
        );
    }
}
