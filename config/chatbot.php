<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chatbot IA — AquaSecure (module Projets et Financements)
    |--------------------------------------------------------------------------
    |
    | Ce fichier configure l'assistant conversationnel IA qui répond aux
    | questions relatives au module Projets et Financements uniquement.
    |
    | Fournisseur LLM : Google Gemini Flash (API officielle).
    | Aucun microservice IA supplémentaire n'est introduit.
    */

    'enabled' => (bool) env('CHATBOT_ENABLED', true),

    'llm_provider' => env('CHATBOT_LLM_PROVIDER', 'groq'),

    /*
    |--------------------------------------------------------------------------
    | Grok via Groq Cloud
    |--------------------------------------------------------------------------
    |
    | La clé API doit être renseignée dans le fichier .env local (jamais
    | committée). Obtenez-la sur https://console.groq.com/
    |
    | Le modèle doit être compatible avec le Function Calling (format OpenAI).
    | Voir https://console.groq.com/docs
    */
    'groq' => [
        'api_key' => env('GROK_API_KEY'),
        'model'   => env('GROQ_MODEL', 'qwen/qwen3.8-27b'),
        'timeout' => (int) env('GROQ_TIMEOUT', 30),
        'base_url'=> 'https://api.groq.com/openai/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites de sécurité et de coût
    |--------------------------------------------------------------------------
    */
    'max_message_length' => (int) env('CHATBOT_MAX_MESSAGE_LENGTH', 2000),
    'max_tool_calls' => (int) env('CHATBOT_MAX_TOOL_CALLS', 3),
    'rate_limit' => (int) env('CHATBOT_RATE_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | RAG (Recherche documentaire)
    |--------------------------------------------------------------------------
    */
    'rag_enabled' => (bool) env('CHATBOT_RAG_ENABLED', true),
    'max_rag_chunks' => (int) env('CHATBOT_MAX_RAG_CHUNKS', 5),

];
