<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Requête de validation pour l'endpoint du chatbot.
 *
 * Valide la taille et le format du message. L'identité utilisateur provient
 * de l'authentification Laravel, jamais du payload.
 */
class ChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'message'         => ['required', 'string', 'min:1', 'max:2000'],
            'history'         => ['sometimes', 'array', 'max:20'],
            'history.*.role'  => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Un message est requis.',
            'message.string' => 'Le message doit être du texte.',
            'message.min' => 'Le message est trop court.',
            'message.max' => 'Le message ne doit pas dépasser 2000 caractères.',
        ];
    }
}
