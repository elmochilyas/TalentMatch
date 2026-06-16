<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOffreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'competences_requises' => ['nullable'],
            'niveau_experience_minimum' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('competences_requises')) {
            $value = $this->input('competences_requises');

            if (is_string($value) && filled($value)) {
                $skills = array_filter(array_map('trim', explode(',', $value)));
                $this->merge(['competences_requises' => array_values($skills)]);
            } elseif (blank($value)) {
                $this->merge(['competences_requises' => null]);
            }
        }
    }
}
