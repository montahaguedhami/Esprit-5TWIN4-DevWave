<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'provider',
        'provider_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'provider_id',
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
            'password'          => 'hashed',
            'is_active'         => 'boolean',
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
     *
     * NOTE: `rol` is informational only — it is NEVER used for authorization decisions
     * server-side. Every request re-validates roles from the database.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'rol' => $this->roles->first()?->slug ?? null,
            'aud' => 'aquasecure-api',
        ];
    }

    /* ── Recommandation de projets (V1 à règles) ─────────────────────────── */

    /**
     * Favourite rows saved by this citizen.
     */
    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class);
    }

    /**
     * The projects this citizen saved, most recent first.
     *
     * Eager-loads the zones so a favourite list never issues one query per
     * card.
     */
    public function projetsFavoris(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'favoris', 'user_id', 'projet_id')
            ->withTimestamps()
            ->orderByDesc('favoris.created_at')
            ->orderByDesc('projets.id');
    }

    /**
     * Consultation rows recorded for this citizen.
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(ConsultationProjet::class);
    }

    /**
     * Confirmed fundings made by this citizen.
     *
     * Only rows carrying a `user_id` are citizen fundings: a funding entered
     * manually by a manager has no author and must never feed a personal
     * profile.
     */
    public function financements(): HasMany
    {
        return $this->hasMany(Financement::class);
    }
}
