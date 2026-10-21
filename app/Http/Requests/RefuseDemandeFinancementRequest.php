<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A manager refuses a pending funding request. A reason is required so the
 * citizen gets a meaningful answer instead of a bare status change.
 */
class RefuseDemandeFinancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'motif_refus' => ['required', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'motif_refus' => 'le motif de refus',
        ];
    }

    public function messages(): array
    {
        return [
            'motif_refus.required' => 'Indiquez un motif de refus : il sera affiché au citoyen.',
            'motif_refus.max'      => 'Le motif de refus ne doit pas dépasser 500 caractères.',
        ];
    }
}
