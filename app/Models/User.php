<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
<<<<<<< HEAD
=======
use App\Traits\HasRoles;
>>>>>>> user
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< HEAD

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
=======
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;
>>>>>>> user

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
<<<<<<< HEAD
=======
        'is_active',
>>>>>>> user
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
<<<<<<< HEAD
=======
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot model and add protection against deleting the last administrator.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->isLastAdministrator()) {
                throw ValidationException::withMessages([
                    'user' => ['Impossible de supprimer le dernier administrateur actif du système.'],
                ]);
            }
        });
    }

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array of custom claims added to the JWT payload.
     * NOTE: `rol` is informational only — never used for authorization decisions server-side.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'rol' => $this->roles->first()?->slug ?? null,
            'aud' => 'aquasecure-api',
>>>>>>> user
        ];
    }
}
