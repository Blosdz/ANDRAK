<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizaCodigoProducto;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoRequest extends FormRequest
{
    use NormalizaCodigoProducto;

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
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255', Rule::unique('productos', 'codigo')->whereNull('deleted_at')],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', Rule::in(['USD', 'PEN'])],
            'estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
            'img' => ['nullable', 'string'],
            'descripcion_extra' => ['nullable', 'array'],
            'descripcion_extra.dimensiones' => ['nullable', 'string'],
            'descripcion_extra.peso' => ['nullable', 'string'],
            'descripcion_extra.motor' => ['nullable', 'string'],
            'cantidad_disponible' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
