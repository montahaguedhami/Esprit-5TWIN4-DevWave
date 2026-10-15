<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'localisation' => $this->localisation,
            'statut' => [
                'code' => $this->statut?->value ?? 'active',
                'libelle' => $this->statut?->label() ?? 'Active',
            ],
            'coordonnees' => [
                'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
                'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            ],
            'superficie_km2' => $this->superficie_km2 !== null ? (float) $this->superficie_km2 : null,
            'population_desservie' => $this->population_desservie !== null ? (int) $this->population_desservie : null,
            'responsable' => $this->whenLoaded('responsable', function () {
                return $this->responsable ? [
                    'id' => $this->responsable->id,
                    'nom' => $this->responsable->name,
                    'email' => $this->responsable->email,
                ] : null;
            }),
            'infrastructures_count' => $this->whenCounted('infrastructures', $this->infrastructures_count),
            'infrastructures' => InfrastructureResource::collection($this->whenLoaded('infrastructures')),
            'cree_le' => $this->created_at?->toISOString(),
            'mis_a_jour_le' => $this->updated_at?->toISOString(),
        ];
    }
}
