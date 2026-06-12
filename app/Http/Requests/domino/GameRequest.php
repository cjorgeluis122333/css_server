<?php

namespace App\Http\Requests\domino;

use Illuminate\Foundation\Http\FormRequest;

class GameRequest extends FormRequest
{
    public function rules(): array
    {
        return [

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
