<?php

namespace App\Models;

use App\Enums\FamilyRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FamilyUser extends Pivot
{
    protected $table = 'family_user';

    protected $casts = [
        'role' => FamilyRole::class,
    ];
}
