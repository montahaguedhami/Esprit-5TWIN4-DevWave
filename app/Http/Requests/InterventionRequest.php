<?php

namespace App\Http\Requests;

use App\Models\Intervention;
use App\Services\InterventionPlanningService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class InterventionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'technicien_id' => ['required', 'exists:techniciens,id'],
            'infrastructure_id' => ['nullable', 'exists:infrastructures,id'],
            // Une intervention terminée ne peut pas avoir une date dans le futur
            'date' => ['required', 'date', Rule::when($this->input('statut') === 'Terminée', 'before_or_equal:today')],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
            'statut' => ['required', Rule::in(Intervention::STATUTS)],
            'cout' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    /**
     * Règles de planification : pas de double affectation le même jour, pas d'affectation en congé.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['technicien_id', 'date', 'statut'])) {
                    return;
                }

                $message = app(InterventionPlanningService::class)->verifier(
                    (int) $this->input('technicien_id'),
                    (string) $this->input('date'),
                    (string) $this->input('statut'),
                    $this->route('intervention')?->id
                );

                if ($message) {
                    $validator->errors()->add('date', $message);
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'technicien_id' => 'technicien',
            'infrastructure_id' => 'infrastructure concernée',
            'date' => 'date',
            'description' => 'description',
            'statut' => 'statut',
            'cout' => 'coût',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'min' => 'Le champ :attribute doit être d\'au moins :min.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'in' => 'La valeur choisie pour :attribute est invalide.',
            'technicien_id.exists' => 'Le technicien sélectionné n\'existe pas.',
            'description.min' => 'La description doit contenir au moins :min caractères.',
            'description.max' => 'La description ne doit pas dépasser :max caractères.',
            'date.before_or_equal' => 'Une intervention terminée ne peut pas avoir une date future.',
        ];
    }
}
