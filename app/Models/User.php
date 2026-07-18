<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'franchise_id', 'phone', 'avatar',
        'student_type', 'is_active', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function isInternal(): bool
    {
        return $this->student_type === 'internal';
    }

    public function isExternal(): bool
    {
        return $this->student_type === 'external';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isFranchiseAdmin(): bool
    {
        return $this->hasRole('franchise_admin');
    }
}
