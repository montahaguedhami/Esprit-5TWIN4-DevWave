<?php

namespace App\Services\Chatbot;

/**
 * Contrat pour chaque exécuteur d'outil métier.
 *
 * Chaque outil est exécuté dans le contexte de l'utilisateur authentifié.
 * L'identité du propriétaire provient toujours de Laravel, jamais des
 * arguments fournis par le modèle.
 */
interface ToolExecutor
{
    /**
     * Nom de l'outil (doit correspondre à une entrée du ToolRegistry).
     */
    public function name(): string;

    /**
     * Vérifie si l'utilisateur connecté est autorisé à exécuter cet outil.
     */
    public function isAuthorized(mixed $user): bool;

    /**
     * Valide et nettoie les arguments reçus du LLM.
     *
     * @throws \Illuminate\Validation\ValidationException si les arguments sont invalides
     * @return array<string, mixed> arguments nettoyés et sûrs
     */
    public function validateArguments(array $arguments): array;

    /**
     * Exécute l'outil et retourne un résultat limité aux données nécessaires.
     *
     * @param  array<string, mixed>  $validatedArguments  arguments déjà validés
     * @return array<string, mixed> résultat de l'outil (jamais de données brutes non filtrées)
     */
    public function execute(mixed $user, array $validatedArguments): array;
}
