<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeachHistoricalAdvisoryPointRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['point_name', 'source_name', 'source_url'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'point_name' => ['required', 'string', 'min:2', 'max:255'],
            'governorate_id' => ['required', 'integer', 'exists:tunibeach_governorates,id'],
            'reported_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'reported_status' => ['required', 'in:restricted,unknown'],
            'source_name' => ['required', 'string', 'min:2', 'max:180'],
            'source_url' => [
                'required', 'url', 'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $scheme = strtolower((string) parse_url((string) $value, PHP_URL_SCHEME));
                    if (! in_array($scheme, ['http', 'https'], true)) {
                        $fail('L’URL doit commencer par http:// ou https://.');
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'point_name.required' => 'Le nom du point est obligatoire.',
            'point_name.min' => 'Le nom du point doit contenir au moins :min caractères.',
            'point_name.max' => 'Le nom du point ne peut pas dépasser :max caractères.',
            'governorate_id.required' => 'Sélectionnez un gouvernorat.',
            'governorate_id.integer' => 'Le gouvernorat sélectionné est invalide.',
            'governorate_id.exists' => 'Le gouvernorat sélectionné n’existe pas.',
            'reported_at.required' => 'La date du signalement est obligatoire.',
            'reported_at.date_format' => 'Saisissez une date valide.',
            'reported_at.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'reported_status.required' => 'Sélectionnez un état.',
            'reported_status.in' => 'L’état sélectionné est invalide.',
            'source_name.required' => 'La source est obligatoire.',
            'source_name.min' => 'La source doit contenir au moins :min caractères.',
            'source_name.max' => 'La source ne peut pas dépasser :max caractères.',
            'source_url.required' => 'L’URL de la source est obligatoire.',
            'source_url.url' => 'Saisissez une URL valide.',
            'source_url.max' => 'L’URL ne peut pas dépasser :max caractères.',
        ];
    }
}
