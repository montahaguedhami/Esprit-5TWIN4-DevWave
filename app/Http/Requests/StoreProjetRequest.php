<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjetRequest extends FormRequest
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
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'budget' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'statut' => ['required', 'string', 'in:planifie,en_cours,termine,suspendu,annule'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
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
            'nom.required' => 'Le nom du projet est obligatoire.',
            'nom.max' => 'Le nom du projet ne peut pas dépasser 255 caractères.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
            'budget.required' => 'Le budget est obligatoire.',
            'budget.numeric' => 'Le budget doit être un nombre.',
            'budget.min' => 'Le budget doit être positif ou nul.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être : planifié, en cours, terminé, suspendu ou annulé.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ];
    }
}
