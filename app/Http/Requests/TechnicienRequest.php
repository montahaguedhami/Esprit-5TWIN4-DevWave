<?php

namespace App\Http\Requests;

use App\Models\Technicien;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TechnicienRequest extends FormRequest
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
            'nom' => ['required', 'string', 'min:3', 'max:100'],
            'specialite' => ['required', Rule::in(Technicien::SPECIALITES)],
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9 ]{8,20}$/'],
            'disponibilite' => ['required', Rule::in(Technicien::DISPONIBILITES)],
        ];
    }

    public function attributes(): array
    {
        return [
            'nom' => 'nom',
            'specialite' => 'spécialité',
            'telephone' => 'téléphone',
            'disponibilite' => 'disponibilité',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'min' => 'Le champ :attribute doit contenir au moins :min caractères.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'in' => 'La valeur choisie pour :attribute est invalide.',
            'telephone.regex' => 'Le numéro de téléphone doit contenir 8 à 20 chiffres (ex. +216 22 123 456).',
        ];
    }
}
