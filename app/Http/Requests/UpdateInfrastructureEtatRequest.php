<?php

namespace App\Http\Requests;

use App\Enums\InfrastructureEtat;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInfrastructureEtatRequest extends FormRequest
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
            'etat' => ['required', Rule::enum(InfrastructureEtat::class)],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'etat.required' => 'Le nouvel état est obligatoire.',
            'etat.enum' => 'L’état sélectionné n’est pas valide.',
            'commentaire.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'etat' => 'état',
            'commentaire' => 'commentaire',
        ];
    }
}
