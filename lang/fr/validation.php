<?php

/*
|--------------------------------------------------------------------------
| Messages de validation — français
|--------------------------------------------------------------------------
|
| Utilisés dès que APP_LOCALE=fr. Les messages anglais du framework restent
| le repli pour toute clé absente de ce fichier, donc il est acceptable de
| compléter ce fichier au fur et à mesure.
|
| Placeholders : :attribute (nom du champ), :min, :max, :other, :values.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Messages génériques
    |----------------------------------------------------------------------
    */

    'accepted'             => 'Le champ :attribute doit être accepté.',
    'active_url'           => 'Le champ :attribute n\'est pas une URL valide.',
    'after'                => 'Le champ :attribute doit être une date postérieure à :other.',
    'after_or_equal'       => 'Le champ :attribute doit être une date postérieure ou égale à :other.',
    'alpha'                => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash'           => 'Le champ :attribute ne doit contenir que des lettres, des chiffres et des tirets.',
    'alpha_num'            => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array'                => 'Le champ :attribute doit être un tableau.',
    'before'               => 'Le champ :attribute doit être une date antérieure à :other.',
    'before_or_equal'      => 'Le champ :attribute doit être une date antérieure ou égale à :other.',
    'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed'            => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password'     => 'Le mot de passe est incorrect.',
    'date'                 => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals'          => 'Le champ :attribute doit être une date égale à :other.',
    'date_format'          => 'Le champ :attribute ne correspond pas au format :format.',
    'decimal'              => 'Le champ :attribute doit avoir :decimal décimales.',
    'declined'             => 'Le champ :attribute doit être refusé.',
    'different'            => 'Le champ :attribute et :other doivent être différents.',
    'digits'               => 'Le champ :attribute doit contenir exactement :digits chiffres.',
    'digits_between'       => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'email'                => 'Le champ :attribute doit être une adresse email valide.',
    'ends_with'            => 'Le champ :attribute doit se terminer par l\'une des valeurs suivantes : :values.',
    'exists'               => 'Le champ :attribute sélectionné est invalide.',
    'file'                 => 'Le champ :attribute doit être un fichier.',
    'filled'               => 'Le champ :attribute doit avoir une valeur.',
    'image'                => 'Le champ :attribute doit être une image.',
    'in'                   => 'Le champ :attribute sélectionné est invalide.',
    'in_array'             => 'Le champ :attribute n\'existe pas dans :other.',
    'integer'              => 'Le champ :attribute doit être un nombre entier.',
    'ip'                   => 'Le champ :attribute doit être une adresse IP valide.',
    'json'                 => 'Le champ :attribute doit être une chaîne JSON valide.',
    'max'                  => [
        'array'   => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file'    => 'Le champ :attribute ne doit pas dépasser :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
        'string'  => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'mimes'                => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes'            => 'Le champ :attribute doit être un fichier de type : :values.',
    'min'                  => [
        'array'   => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file'    => 'Le champ :attribute doit contenir au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'not_in'               => 'Le champ :attribute sélectionné est invalide.',
    'numeric'              => 'Le champ :attribute doit être un nombre.',
    'password'             => [
        'letters'       => 'Le champ :attribute doit contenir au moins une lettre.',
        'mixed'         => 'Le champ :attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers'       => 'Le champ :attribute doit contenir au moins un chiffre.',
        'symbols'       => 'Le champ :attribute doit contenir au moins un caractère spécial.',
        'uncompromised' => 'La valeur proposée pour :attribute figure dans une fuite de données connue. Choisissez-en une autre.',
    ],
    'present'              => 'Le champ :attribute doit être présent.',
    'prohibited'           => 'Le champ :attribute est interdit.',
    'regex'                => 'Le format de :attribute est invalide.',
    'required'             => 'Le champ :attribute est obligatoire.',
    'required_if'          => 'Le champ :attribute est obligatoire lorsque :other vaut :value.',
    'required_unless'      => 'Le champ :attribute est obligatoire sauf si :other vaut :values.',
    'required_with'        => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_without'     => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
    'same'                 => 'Les champs :attribute et :other doivent être identiques.',
    'size'                 => [
        'array'   => 'Le champ :attribute doit contenir :size éléments.',
        'file'    => 'Le champ :attribute doit peser :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit être égal à :size.',
        'string'  => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with'          => 'Le champ :attribute doit commencer par l\'une des valeurs suivantes : :values.',
    'string'               => 'Le champ :attribute doit être une chaîne de caractères.',
    'unique'               => 'La valeur de :attribute est déjà utilisée.',
    'url'                  => 'Le format de :attribute est invalide.',
    'uuid'                 => 'Le champ :attribute doit être un UUID valide.',

    /*
    |----------------------------------------------------------------------
    | Noms de champs en français
    |----------------------------------------------------------------------
    |
    | Retire le « champ password » anglophone des messages.
    |
    */

    'attributes' => [
        'name'                  => 'nom complet',
        'email'                 => 'adresse email',
        'password'              => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'phone'                 => 'numéro de téléphone',
        'telephone'             => 'numéro de téléphone',
        'terms'                 => 'conditions d\'utilisation',
        'current_password'      => 'mot de passe actuel',
        'remember'              => 'se souvenir de moi',
        'role_id'               => 'rôle',
        'zone_id'               => 'zone',
        'point_mesure_id'       => 'point de mesure',
        'date_debut'            => 'date de début',
        'date_fin'              => 'date de fin',
        'description'           => 'description',
        'type'                  => 'type',
        'statut'                => 'statut',
        'status'                => 'statut',
        'titre'                 => 'titre',
        'title'                 => 'titre',
        'adresse'               => 'adresse',
        'address'               => 'adresse',
        'capacite'              => 'capacité',
        'debit'                 => 'débit',
        'pression'              => 'pression',
        'temperature'           => 'température',
        'qualite_id'            => 'qualité de l\'eau',
        'valeur'                => 'valeur',
        'unite'                 => 'unité',
        'mesure'                => 'mesure',
        'village'               => 'village',
        'gouvernorat'           => 'gouvernorat',
        'file'                  => 'fichier',
        'image'                 => 'image',
    ],

];
