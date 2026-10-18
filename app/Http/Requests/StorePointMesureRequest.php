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

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:point_mesures,code'],
            'nom' => ['required', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'type' => ['required', Rule::in(['Station', 'Puits', 'Réservoir', 'Réseau', 'Autre'])],
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'description' => ['nullable', 'string'],
        ];
    }
}
