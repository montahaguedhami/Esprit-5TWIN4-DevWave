<?php

namespace App\Http\Requests;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInfrastructureRequest extends FormRequest
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
            'nom' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('infrastructures', 'nom')->where('zone_id', $this->input('zone_id')),
            ],
            'type' => ['required', Rule::enum(InfrastructureType::class)],
            'localisation' => ['required', 'string', 'min:3', 'max:255'],
            'etat' => ['required', Rule::enum(InfrastructureEtat::class)],
            'date_installation' => ['required', 'date', 'before_or_equal:today'],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'capacite' => ['nullable', 'numeric', 'min:0'],
            'unite_capacite' => ['nullable', 'string', 'in:m3,m3/h,l/s'],
            'fabricant' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255', Rule::unique('infrastructures', 'reference')],
            'date_derniere_maintenance' => ['nullable', 'date'],
            'date_prochaine_maintenance' => [
                'nullable',
                'date',
                $this->filled('date_derniere_maintenance') ? 'after_or_equal:date_derniere_maintenance' : 'nullable',
            ],
            'duree_vie_annees' => ['nullable', 'integer', 'min:1', 'max:150'],
            'niveau_criticite' => ['nullable', Rule::enum(NiveauCriticite::class)],
            'commentaire_etat' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l’infrastructure est obligatoire.',
            'nom.min' => 'Le nom de l’infrastructure doit comporter au moins 3 caractères.',
            'nom.max' => 'Le nom de l’infrastructure ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom d’infrastructure est déjà utilisé dans cette zone.',
            'type.required' => 'Le type d’infrastructure est obligatoire.',
            'type.enum' => 'Le type d’infrastructure sélectionné n’est pas valide.',
            'localisation.required' => 'La localisation de l’infrastructure est obligatoire.',
            'localisation.string' => 'La localisation doit être un texte.',
            'localisation.min' => 'La localisation doit comporter au moins 3 caractères.',
            'localisation.max' => 'La localisation ne peut pas dépasser 255 caractères.',
            'etat.required' => 'L’état de l’infrastructure est obligatoire.',
            'etat.enum' => 'L’état sélectionné n’est pas valide.',
            'date_installation.required' => 'La date d’installation est obligatoire.',
            'date_installation.date' => 'La date d’installation doit être une date valide.',
            'date_installation.before_or_equal' => 'La date d’installation ne peut pas être dans le futur.',
            'zone_id.required' => 'La zone de l’infrastructure est obligatoire.',
            'zone_id.integer' => 'La zone sélectionnée n’est pas valide.',
            'zone_id.exists' => 'La zone sélectionnée n’existe pas.',
            'latitude.numeric' => 'La latitude doit être un nombre décimal.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90 degrés.',
            'longitude.numeric' => 'La longitude doit être un nombre décimal.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180 degrés.',
            'capacite.numeric' => 'La capacité doit être un nombre.',
            'capacite.min' => 'La capacité doit être supérieure ou égale à 0.',
            'unite_capacite.in' => 'L’unité de capacité doit être m3, m3/h ou l/s.',
            'reference.unique' => 'Cette référence d’équipement est déjà utilisée.',
            'date_derniere_maintenance.date' => 'La date de dernière maintenance doit être une date valide.',
            'date_prochaine_maintenance.date' => 'La date de prochaine maintenance doit être une date valide.',
            'date_prochaine_maintenance.after_or_equal' => 'La date de prochaine maintenance doit être postérieure ou égale à la date de dernière maintenance.',
            'duree_vie_annees.integer' => 'La durée de vie doit être un nombre entier d’années.',
            'duree_vie_annees.min' => 'La durée de vie doit être positive.',
            'duree_vie_annees.max' => 'La durée de vie ne peut pas dépasser 150 ans.',
            'niveau_criticite.enum' => 'Le niveau de criticité sélectionné n’est pas valide.',
            'commentaire_etat.max' => 'Le motif du changement d’état ne peut pas dépasser 500 caractères.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom de l’infrastructure',
            'type' => 'type',
            'localisation' => 'localisation',
            'etat' => 'état',
            'date_installation' => 'date d’installation',
            'zone_id' => 'zone',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'capacite' => 'capacité',
            'unite_capacite' => 'unité de capacité',
            'fabricant' => 'fabricant',
            'reference' => 'référence',
            'date_derniere_maintenance' => 'date de dernière maintenance',
            'date_prochaine_maintenance' => 'date de prochaine maintenance',
            'duree_vie_annees' => 'durée de vie',
            'niveau_criticite' => 'niveau de criticité',
        ];
    }
}
