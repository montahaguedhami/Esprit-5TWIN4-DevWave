<?php

namespace App\Services\Recommandation;

/**
 * One ranked recommendation, ready to be rendered.
 *
 * This object is the CONTRACT of the recommendation engine. A future V2 that
 * swaps the rules for a machine-learning model must still produce objects
 * shaped like this one, so the Blade views keep working unchanged.
 *
 * @property-read int    $projetId
 * @property-read string $nom
 * @property-read float  $score            0–100, an internal RANKING score
 * @property-read bool   $financable       can this project still take money?
 * @property-read array<int, string> $raisons  human-readable reasons
 */
final class Recommandation
{
    /**
     * @param  array<int, string>  $raisons
     */
    public function __construct(
        public readonly int $projetId,
        public readonly string $nom,
        public readonly float $score,
        public readonly bool $financable,
        public readonly array $raisons,
        public readonly \App\Models\Projet $projet,
        /** Set when the ranking came from the no-history fallback. */
        public readonly bool $repli = false,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'projet_id'  => $this->projetId,
            'nom'        => $this->nom,
            'score'      => $this->score,
            'financable' => $this->financable,
            'raisons'    => $this->raisons,
            'repli'      => $this->repli,
        ];
    }
}
