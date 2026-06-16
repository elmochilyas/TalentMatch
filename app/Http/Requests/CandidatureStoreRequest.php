<?php

namespace App\Http\Requests;

use App\Models\Offre;
use Illuminate\Foundation\Http\FormRequest;

class CandidatureStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offre = $this->route('offre');

        if ($offre instanceof Offre) {
            return $this->user()->id === $offre->user_id;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'nom_candidat' => ['required', 'string', 'max:255'],
            'cv_texte' => ['required', 'string', 'min:20'],
        ];
    }
}
