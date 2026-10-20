<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'provider',
        'provider_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

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

    // ── JWT ────────────────────────────────────────────────────────────────

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'rol' => $this->roles->first()?->slug ?? null,
            'aud' => 'aquasecure-api',
        ];
    }

    // ── Module Maintenance (collègues) ────────────────────────────────────

    /**
     * Fiche technicien liée à ce compte.
     */
    public function technicien(): HasOne
    {
        return $this->hasOne(Technicien::class);
    }

    // ── Module Projets / Financements (Ghada) ─────────────────────────────

    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class);
    }

    public function projetsFavoris(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'favoris', 'user_id', 'projet_id')
            ->withTimestamps()
            ->orderByDesc('favoris.created_at')
            ->orderByDesc('projets.id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(ConsultationProjet::class);
    }

    public function financements(): HasMany
    {
        return $this->hasMany(Financement::class);
    }
}
