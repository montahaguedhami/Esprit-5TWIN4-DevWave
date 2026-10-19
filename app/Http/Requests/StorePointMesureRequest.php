<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePointMesureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code'        => is_string($this->code) ? trim($this->code) : $this->code,
            'nom'         => is_string($this->nom) ? trim($this->nom) : $this->nom,
            'zone'        => is_string($this->zone) ? (trim($this->zone) ?: null) : null,
            'adresse'     => is_string($this->adresse) ? (trim($this->adresse) ?: null) : null,
            'latitude'    => ($this->latitude !== null && $this->latitude !== '') ? $this->latitude : null,
            'longitude'   => ($this->longitude !== null && $this->longitude !== '') ? $this->longitude : null,
            'type'        => is_string($this->type) ? trim($this->type) : $this->type,
            'statut'      => is_string($this->statut) ? trim($this->statut) : $this->statut,
            'description' => is_string($this->description) ? (trim($this->description) ?: null) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            // The code is generated automatically when absent (see
            // PointMesureController::generateUniqueCode()).
            'code'        => ['sometimes', 'string', 'min:2', 'max:50', 'unique:point_mesures,code'],
            'nom'         => ['required', 'string', 'min:2', 'max:255'],
            'zone'        => ['nullable', 'string', 'max:255'],
            'infrastructure_id' => ['nullable', 'integer', Rule::exists('infrastructures', 'id')->whereNull('deleted_at')],
            'adresse'     => ['nullable', 'string', 'max:255'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'type'        => ['required', Rule::in(['Station', 'Puits', 'Réservoir', 'Réseau', 'Autre'])],
            'statut'      => ['required', Rule::in(['actif', 'inactif'])],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.min'             => 'Le code doit comporter au moins :min caractères.',
            'code.max'             => 'Le code ne peut pas dépasser :max caractères.',
            'code.unique'          => 'Ce code de point de mesure est déjà utilisé.',
            'nom.required'         => 'Le nom du point de mesure est obligatoire.',
            'nom.min'              => 'Le nom doit comporter au moins :min caractères.',
            'nom.max'              => 'Le nom ne peut pas dépasser :max caractères.',
            'latitude.numeric'     => 'La latitude doit être une valeur numérique.',
            'latitude.between'     => 'La latitude doit être comprise entre -90 et 90 degrés.',
            'longitude.numeric'    => 'La longitude doit être une valeur numérique.',
            'longitude.between'    => 'La longitude doit être comprise entre -180 et 180 degrés.',
            'type.required'        => 'Le type d’infrastructure de prélèvement est obligatoire.',
            'type.in'              => 'Le type sélectionné est invalide.',
            'statut.required'      => 'Le statut du point de mesure est obligatoire.',
            'statut.in'            => 'Le statut doit être "actif" ou "inactif".',
            'description.max'      => 'La description ne peut pas dépasser :max caractères.',
        ];
    }
}
