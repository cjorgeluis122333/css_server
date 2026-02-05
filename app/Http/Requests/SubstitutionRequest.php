<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubstitutionRequest extends FormRequest
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
