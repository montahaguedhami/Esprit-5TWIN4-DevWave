<?php

namespace App\Http\Requests;

use App\Enums\ZoneStatut;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session('user') && in_array(session('user.role'), ['manager', 'admin']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'min:3', 'max:255', Rule::unique('zones', 'nom')],
            'description' => ['nullable', 'string', 'max:1000'],
            'localisation' => ['required', 'string', 'min:2', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'superficie_km2' => ['nullable', 'numeric', 'min:0'],
            'population_desservie' => ['nullable', 'integer', 'min:0'],
            'statut' => ['nullable', Rule::enum(ZoneStatut::class)],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la zone est obligatoire.',
            'nom.min' => 'Le nom de la zone doit comporter au moins 3 caractères.',
            'nom.max' => 'Le nom de la zone ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom de zone est déjà utilisé.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
            'localisation.required' => 'La localisation de la zone est obligatoire.',
            'localisation.string' => 'La localisation doit être un texte.',
            'localisation.min' => 'La localisation doit comporter au moins 2 caractères.',
            'localisation.max' => 'La localisation ne peut pas dépasser 255 caractères.',
            'latitude.numeric' => 'La latitude doit être un nombre décimal.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90 degrés.',
            'longitude.numeric' => 'La longitude doit être un nombre décimal.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180 degrés.',
            'superficie_km2.numeric' => 'La superficie doit être un nombre.',
            'superficie_km2.min' => 'La superficie doit être supérieure ou égale à 0.',
            'population_desservie.integer' => 'La population desservie doit être un nombre entier.',
            'population_desservie.min' => 'La population desservie doit être positive ou nulle.',
            'statut.enum' => 'Le statut sélectionné n’est pas valide.',
            'responsable_id.exists' => 'Le responsable sélectionné n’existe pas.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom de la zone',
            'description' => 'description',
            'localisation' => 'localisation',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'superficie_km2' => 'superficie',
            'population_desservie' => 'population desservie',
            'statut' => 'statut',
            'responsable_id' => 'responsable',
        ];
    }
}
