<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfrastructureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sante = $this->sante;

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'reference' => $this->reference,
            'type' => [
                'code' => $this->type->value,
                'libelle' => $this->type->label(),
            ],
            'etat' => [
                'code' => $this->etat->value,
                'libelle' => $this->etat->label(),
                'couleur' => $this->etat->color(),
            ],
            'criticite' => [
                'code' => $this->niveau_criticite?->value ?? 'moyen',
                'libelle' => $this->niveau_criticite?->label() ?? 'Moyen',
                'couleur' => $this->niveau_criticite?->color() ?? 'amber',
            ],
            'sante' => [
                'score' => $sante['score'],
                'libelle' => $sante['label'],
                'couleur' => $sante['color'],
            ],
            'localisation' => [
                'adresse' => $this->localisation,
                'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
                'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            ],
            'capacite' => [
                'valeur' => $this->capacite !== null ? (float) $this->capacite : null,
                'unite' => $this->unite_capacite,
            ],
            'fabricant' => $this->fabricant,
            'duree_vie_annees' => $this->duree_vie_annees,
            'date_installation' => $this->date_installation?->format('Y-m-d'),
            'maintenance' => [
                'derniere' => $this->date_derniere_maintenance?->format('Y-m-d'),
                'prochaine' => $this->date_prochaine_maintenance?->format('Y-m-d'),
                'en_retard' => (bool) ($this->date_prochaine_maintenance && $this->date_prochaine_maintenance->isPast()),
            ],
            'zone' => $this->whenLoaded('zone', function () {
                return $this->zone ? [
                    'id' => $this->zone->id,
                    'nom' => $this->zone->nom,
                    'localisation' => $this->zone->localisation,
                ] : null;
            }),
            'zone_id' => $this->zone_id,
            'cree_le' => $this->created_at?->toISOString(),
            'mis_a_jour_le' => $this->updated_at?->toISOString(),
        ];
    }
}
