<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
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
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::unique('techniciens', 'user_id')->ignore($this->route('technicien')),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = User::find($value);
                    if ($user && ! $user->hasRole(Role::TECHNICIEN)) {
                        $fail('Le compte choisi doit avoir le rôle Technicien.');
                    }
                },
            ],
            'nom' => ['required', 'string', 'min:3', 'max:100'],
            'specialite' => ['required', Rule::in(Technicien::SPECIALITES)],
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9 ]{8,20}$/'],
            'disponibilite' => ['required', Rule::in(Technicien::DISPONIBILITES)],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'compte utilisateur',
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
            'user_id.exists' => 'Ce compte utilisateur n\'existe pas.',
            'user_id.unique' => 'Ce compte est déjà lié à une autre fiche technicien.',
            'telephone.regex' => 'Le numéro de téléphone doit contenir 8 à 20 chiffres (ex. +216 22 123 456).',
        ];
    }
}
