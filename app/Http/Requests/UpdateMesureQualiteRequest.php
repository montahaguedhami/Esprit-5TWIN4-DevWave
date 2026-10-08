<?php

namespace App\Http\Requests;

use App\Models\MesureQualite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMesureQualiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mesure = $this->route('mesure');
        $mesureId = $mesure instanceof MesureQualite ? $mesure->id : $mesure;

        return [
            'reference' => ['required', 'string', 'max:50', Rule::unique('mesures_qualites', 'reference')->ignore($mesureId)],
            'point_mesure_id' => ['required', 'exists:point_mesures,id'],
            'date_mesure' => ['required', 'date'],
            'ph' => ['required', 'numeric', 'between:0,14'],
            'turbidite' => ['required', 'numeric', 'min:0'],
            'chlore_residuel' => ['required', 'numeric', 'min:0'],
            'plomb' => ['required', 'numeric', 'min:0'],
            'nitrates' => ['required', 'numeric', 'min:0'],
            'is_verified' => ['required', 'boolean'],
            'verifier' => ['nullable', 'string', 'max:255'],
        ];
    }
}
