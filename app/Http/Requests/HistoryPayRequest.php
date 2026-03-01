<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HistoryPayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'acc'      => 'required|exists:0cc_socios,acc',
            'monto'    => 'required|numeric|min:0',
            'fecha'    => 'nullable|string|max:20',
            'mes'      => 'nullable|string|max:20',
            'oper'     => 'nullable|string',
            'descript' => 'nullable|string',
            'seniat'   => 'nullable|string|max:100',
            'operador' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
