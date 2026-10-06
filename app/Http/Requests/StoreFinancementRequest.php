<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Autoriser si l'utilisateur est manager ou admin
        return session('user') && in_array(session('user.role'), ['manager', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'projet_id' => ['required', 'integer', 'exists:projets,id'],
            'source' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'date_financement' => ['required', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'projet_id.required' => 'Le projet est obligatoire.',
            'projet_id.exists' => 'Le projet sélectionné n\'existe pas.',
            'source.required' => 'La source de financement est obligatoire.',
            'source.max' => 'La source ne peut pas dépasser 255 caractères.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être positif ou nul.',
            'date_financement.required' => 'La date de financement est obligatoire.',
            'date_financement.date' => 'La date de financement doit être une date valide.',
        ];
    }
}
