<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCotizacionRequest extends FormRequest
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
            'cliente_id' => ['sometimes', 'required', 'exists:clientes,id'],
            'tipo_pago_id' => ['nullable', 'exists:tipos_de_pago,id'],
            'moneda' => ['sometimes', 'required', Rule::in(['USD', 'PEN'])],
            'estado' => ['sometimes', 'required', Rule::in(['borrador', 'enviada', 'aceptada', 'rechazada'])],
            'horas_entrega' => ['nullable', 'integer', 'min:0'],
            'detalles' => ['nullable', 'string'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'validez_dias' => ['nullable', 'integer', 'min:0'],
            'garantia' => ['nullable', 'string', 'max:255'],
            'impuestos' => ['nullable', 'numeric', 'min:0'],
            'lineas' => ['sometimes', 'required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required_with:lineas', 'exists:productos,id'],
            'lineas.*.cantidad' => ['required_with:lineas', 'integer', 'min:1'],
            'lineas.*.precio_unitario' => ['required_with:lineas', 'numeric', 'min:0'],
        ];
    }
}
