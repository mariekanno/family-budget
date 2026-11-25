<?php

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Models\Family;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            // 招待先のFamilyを作成
            'family_id' => Family::factory(),

            // Familyの作成者を招待者に設定
            'invited_by' => function (array $attributes) {
                return Family::findOrFail(
                    $attributes['family_id']
                )->created_by;
            },

            // 招待先メールアドレス
            'email' => fake()->unique()->safeEmail(),

            // 招待トークンのハッシュ
            'token_hash' => hash(
                'sha256',
                Str::random(64)
            ),

            // 招待ステータス
            'status' => InvitationStatus::Pending,

            // 招待の有効期限（7日後）
            'expires_at' => now()->addDays(7),
        ];
    }
}
