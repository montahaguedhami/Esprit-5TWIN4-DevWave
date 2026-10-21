<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One view of a project detail page by a citizen — an IMPLICIT signal.
 *
 * A consultation only means the citizen looked at the project. It says
 * nothing about the strength of the interest, which is why it carries the
 * lowest weight of the ranking and only counts inside a recent time window.
 *
 * PRIVACY: nothing but the (citoyen, projet) pair and its timestamp is
 * recorded. No IP address, no geolocation, no user agent, no device data.
 * None of it is needed to rank projects, so none of it is collected.
 */
class ConsultationProjet extends Model
{
    use HasFactory;

    /**
     * Grouping window, in minutes, used to merge repeated views of the same
     * project by the same citizen.
     *
     * This constant is the SINGLE definition of the rule: the controller, the
     * database unique index and the tests all refer to it, so the rule can
     * never drift between the layers.
     */
    public const FENETRE_REGROUPEMENT_MINUTES = 30;

    /**
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'projet_id',
        'bucket',
    ];

    /**
     * Index of the grouping window a given instant falls into.
     *
     * Two views in the same window produce the same bucket, so the unique
     * index (user_id, projet_id, bucket) collapses them into one row.
     *
     * Computed on the UTC timeline so the result never depends on a server
     * timezone setting — two identical instants always give one bucket.
     */
    public static function bucketFor(\DateTimeInterface $moment): int
    {
        $seconds = (new \DateTimeImmutable('@' . $moment->getTimestamp()))
            ->getTimestamp();

        return (int) floor($seconds / (self::FENETRE_REGROUPEMENT_MINUTES * 60));
    }

    /**
     * The bucket of "right now", used when recording a consultation.
     */
    public static function bucketCourant(): int
    {
        return self::bucketFor(now());
    }

    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }
}
