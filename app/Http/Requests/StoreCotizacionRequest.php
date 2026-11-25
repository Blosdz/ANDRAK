<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCotizacionRequest extends FormRequest
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
            'empresa_id' => ['required', 'integer', 'exists:empresa,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo_pago_id' => ['nullable', 'exists:tipos_de_pago,id'],
            'moneda' => ['required', Rule::in(['USD', 'PEN'])],
            'horas_entrega' => ['nullable', 'integer', 'min:0'],
            'detalles' => ['nullable', 'string'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'validez_dias' => ['nullable', 'integer', 'min:0'],
            'garantia' => ['nullable', 'string', 'max:255'],
            'impuestos' => ['nullable', 'numeric', 'min:0'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'exists:productos,id'],
            'lineas.*.cantidad' => ['required', 'integer', 'min:1'],
            'lineas.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }
}
