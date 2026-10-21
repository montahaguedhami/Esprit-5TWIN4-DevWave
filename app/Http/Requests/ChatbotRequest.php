<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

/**
 * Requête de validation pour l'endpoint du chatbot.
 *
 * Valide la taille et le format du message. L'identité utilisateur provient
 * de l'authentification Laravel, jamais du payload.
 *
 * IMPORTANT : l'endpoint chatbot doit TOUJOURS répondre en JSON HTTP 200.
 * Le front (ai-assistant) lit la réponse via fetch().json() ; une redirection
 * 302 ou une 422 HTML ferait échouer le .json() et afficherait « Impossible de
 * joindre le serveur ». On convertit donc toute erreur de validation en 200 JSON.
 */
class ChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Convertit les erreurs de validation en réponse JSON 200 lisible
     * (et non en redirection 302 / 422 HTML qui casserait le front).
     */
    protected function failedValidation(Validator $validator): void
    {
        $first = $validator->errors()->first('message')
            ?? 'Votre question est vide ou trop longue. Veuillez la reformuler (2000 caractères maximum).';

        throw new HttpResponseException(response()->json([
            'response'        => $first,
            'sources'         => [],
            'tool_calls'      => [],
            'error'           => 'validation',
            'groq_available'  => (bool) (new \App\Services\Chatbot\GroqClient())->isConfigured(),
        ], 200));
    }

    public function rules(): array
    {
        return [
            'message'           => ['required', 'string', 'min:1', 'max:2000'],
            'history'           => ['sometimes', 'array', 'max:20'],
            'history.*.role'    => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:15000'],
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
