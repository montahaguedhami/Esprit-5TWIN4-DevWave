<?php

namespace App\Http\Requests;

use App\Models\MesureQualite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMesureQualiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reference'     => is_string($this->reference) ? trim($this->reference) : $this->reference,
            'verifier'      => is_string($this->verifier) ? (trim($this->verifier) ?: null) : null,
            'source_donnee' => is_string($this->source_donnee) ? trim($this->source_donnee) : $this->source_donnee,
        ]);
    }

    public function rules(): array
    {
        return [
            'reference'       => ['required', 'string', 'min:2', 'max:50', 'unique:mesures_qualites,reference'],
            'point_mesure_id' => ['required', 'integer', 'exists:point_mesures,id'],
            'date_mesure'     => ['required', 'date'],
            'ph'              => ['required', 'numeric', 'between:0,14'],
            'turbidite'       => ['required', 'numeric', 'min:0', 'max:1000'],
            'chlore_residuel' => ['required', 'numeric', 'min:0', 'max:100'],
            'plomb'           => ['required', 'numeric', 'min:0', 'max:10000'],
            'nitrates'        => ['required', 'numeric', 'min:0', 'max:1000'],
            'source_donnee'   => ['required', Rule::in(MesureQualite::SOURCES)],
            'is_verified'     => ['sometimes', 'boolean'],
            'verifier'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.required'       => 'La référence unique de la mesure est obligatoire.',
            'reference.min'            => 'La référence doit comporter au moins :min caractères.',
            'reference.max'            => 'La référence ne peut pas dépasser :max caractères.',
            'reference.unique'         => 'Cette référence de mesure existe déjà dans le système.',
            'point_mesure_id.required' => 'Le point de prélèvement associé est obligatoire.',
            'point_mesure_id.exists'   => 'Le point de prélèvement sélectionné n’existe pas.',
            'date_mesure.required'     => 'La date et l’heure de mesure sont obligatoires.',
            'date_mesure.date'         => 'Le format de la date de mesure est invalide.',
            'ph.required'              => 'La valeur du pH est obligatoire.',
            'ph.numeric'               => 'Le pH doit être une valeur numérique.',
            'ph.between'               => 'Le pH doit être compris dans l’échelle physico-chimique de 0 à 14.',
            'turbidite.required'       => 'La valeur de la turbidité est obligatoire.',
            'turbidite.numeric'        => 'La turbidité doit être un nombre valide.',
            'turbidite.min'            => 'La turbidité ne peut pas être négative.',
            'turbidite.max'            => 'La turbidité dépasse la limite maximale acceptable (:max NTU).',
            'chlore_residuel.required' => 'Le taux de chlore résiduel est obligatoire.',
            'chlore_residuel.numeric'  => 'Le chlore résiduel doit être un nombre valide.',
            'chlore_residuel.min'      => 'Le chlore résiduel ne peut pas être négatif.',
            'chlore_residuel.max'      => 'Le chlore résiduel dépasse la limite maximale (:max mg/L).',
            'plomb.required'           => 'La concentration en plomb est obligatoire.',
            'plomb.numeric'            => 'La concentration en plomb doit être un nombre valide.',
            'plomb.min'                => 'La concentration en plomb ne peut pas être négative.',
            'plomb.max'                => 'La concentration en plomb dépasse la limite maximale (:max ppb).',
            'nitrates.required'        => 'Le taux de nitrates est obligatoire.',
            'nitrates.numeric'         => 'Le taux de nitrates doit être un nombre valide.',
            'nitrates.min'             => 'Le taux de nitrates ne peut pas être négatif.',
            'nitrates.max'             => 'Le taux de nitrates dépasse la limite maximale (:max mg/L).',
            'source_donnee.required'   => 'La provenance de la donnée est obligatoire.',
            'source_donnee.in'         => 'La provenance doit être "manuel", "laboratoire" ou "simule".',
            'verifier.max'             => 'Le nom du vérificateur ne peut pas dépasser :max caractères.',
        ];
    }
}
