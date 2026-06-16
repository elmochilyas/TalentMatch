<?php

namespace App\Http\Requests;

use App\Models\Offre;
use Illuminate\Foundation\Http\FormRequest;

class AssistantMessageRequest extends FormRequest
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
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }
}
