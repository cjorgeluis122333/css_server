<?php

namespace App\Http\Requests\photo;

use Illuminate\Foundation\Http\FormRequest;

class DniPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'front' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'back' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'front.required' => 'La imagen del frente es obligatoria.',
            'front.image' => 'El archivo del frente debe ser una imagen.',
            'front.mimes' => 'El frente debe ser JPG, JPEG o PNG.',
            'front.max' => 'La imagen del frente no puede superar los 5 MB.',

            'back.required' => 'La imagen del reverso es obligatoria.',
            'back.image' => 'El archivo del reverso debe ser una imagen.',
            'back.mimes' => 'El reverso debe ser JPG, JPEG o PNG.',
            'back.max' => 'La imagen del reverso no puede superar los 5 MB.',
        ];
    }
}
