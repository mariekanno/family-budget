<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
    ];

    protected $casts = [
        'status' => InvitationStatus::class,
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function inviter()
    {
        return $this->belongsTo(
            User::class,
            'invited_by'
        );
    }
}
