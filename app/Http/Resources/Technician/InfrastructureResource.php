<?php

namespace App\Http\Resources\Technician;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfrastructureResource extends JsonResource
{
    /**
     * Transform the resource into an array for Technician role.
     * EXCLUT STRICTEMENT TOUTE DONNÉE DE COÛT OU DE BUDGET.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => [
                'valeur' => $this->type?->value ?? $this->type,
                'libelle' => $this->type?->label() ?? (string) $this->type,
                'icone' => $this->type?->icon() ?? 'wrench',
            ],
            'etat' => [
                'valeur' => $this->etat?->value ?? $this->etat,
                'libelle' => $this->etat?->label() ?? (string) $this->etat,
                'badge' => $this->etat?->badgeClass() ?? 'bg-slate-500/10 text-slate-300',
            ],
            'niveau_criticite' => [
                'valeur' => $this->niveau_criticite?->value ?? $this->niveau_criticite,
                'libelle' => $this->niveau_criticite?->label() ?? (string) $this->niveau_criticite,
                'couleur' => $this->niveau_criticite?->color() ?? 'blue',
            ],
            'zone' => $this->whenLoaded('zone', function () {
                return [
                    'id' => $this->zone?->id,
                    'nom' => $this->zone?->nom,
                    'localisation' => $this->zone?->localisation,
                ];
            }),
            'capacite' => $this->capacite ? (float) $this->capacite : null,
            'unite_capacite' => $this->unite_capacite,
            'fabricant' => $this->fabricant,
            'reference' => $this->reference,
            'localisation' => $this->localisation,
            'coordonnees' => [
                'latitude' => $this->latitude ? (float) $this->latitude : null,
                'longitude' => $this->longitude ? (float) $this->longitude : null,
            ],
            'date_installation' => $this->date_installation?->toDateString(),
            'date_derniere_maintenance' => $this->date_derniere_maintenance?->toDateString(),
            'date_prochaine_maintenance' => $this->date_prochaine_maintenance?->toDateString(),
            'duree_vie_annees' => $this->duree_vie_annees,
            'sante' => $this->sante,
        ];
    }
}
