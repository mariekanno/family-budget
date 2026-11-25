<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(FamilyUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}
