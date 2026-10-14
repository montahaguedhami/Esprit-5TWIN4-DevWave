<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_email',
        'action',
        'resource_type',
        'resource_id',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $resourceType = null, ?int $resourceId = null, array $details = [], ?User $user = null): self
    {
        $resolvedUser = $user ?? auth()->user();
        $email = $resolvedUser?->email ?? session('user.email');
        $userId = $resolvedUser?->id;

        return self::create([
            'user_id' => $userId,
            'user_email' => $email,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'AquaSecure RBAC Service',
            'details' => $details,
        ]);
    }
}
