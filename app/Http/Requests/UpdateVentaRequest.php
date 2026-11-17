<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo_pago_id' => ['nullable', 'exists:tipos_de_pago,id'],
            'cuotas' => ['nullable', 'integer', 'min:1'],
            'url_pago' => ['nullable', 'string', 'max:255'],
        ];
    }
}
