<?php

namespace App\Http\Requests;

use App\Models\DemandeFinancement;
use App\Models\Projet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * A citizen proposes an amount to fund a project.
 *
 * The submitting user is NEVER taken from the payload: it is read from the
 * authenticated session, so nobody can file a request on someone else's behalf.
 */
class StoreDemandeFinancementRequest extends FormRequest
{
    /**
     * Lowest amount a citizen may propose, in TND.
     */
    public const MONTANT_MINIMUM = 1000;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'projet_id' => ['required', 'integer', 'exists:projets,id'],
            'montant'   => ['required', 'numeric', 'min:'.self::MONTANT_MINIMUM, 'max:1000000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'projet_id' => 'le projet',
            'montant'   => 'le montant',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $projet = Projet::query()->find($this->input('projet_id'));

                if (! $projet instanceof Projet) {
                    return;
                }

                if (! in_array($projet->statut, DemandeFinancement::STATUTS_PROJET_ELIGIBLE, true)) {
                    $validator->errors()->add('projet_id', 'Ce projet n\'est plus ouvert au financement citoyen.');

                    return;
                }

                $disponible = (float) $projet->budget_disponible;

                if ($disponible <= 0) {
                    $validator->errors()->add('montant', 'Ce projet est déjà entièrement financé.');

                    return;
                }

                if ((float) $this->input('montant') > $disponible + 0.0001) {
                    $validator->errors()->add(
                        'montant',
                        'Le montant demandé dépasse le budget encore disponible pour ce projet ('
                            .number_format($disponible, 2, ',', ' ').' TND).'
                    );
                }
            },
        ];
    }
}
