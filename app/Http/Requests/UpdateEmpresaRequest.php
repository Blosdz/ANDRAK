<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends FormRequest
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
            'logo' => ['nullable', 'string'],
            'marca_agua' => ['nullable', 'string'],
            'nombre_comercial' => ['required', 'string', 'max:255'],
            'eslogan' => ['nullable', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'ruc' => ['required', 'digits:11', Rule::in(['20616592395', '10730862453']),
                Rule::unique('empresa', 'ruc')->ignore($this->route('empresa')?->id ?? 1)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ubigeo' => ['nullable', 'string', 'max:6'],
            'establecimiento_codigo' => ['nullable', 'string', 'max:4'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'correo' => ['nullable', 'email', 'max:255'],
            'representante_nombre' => ['nullable', 'string', 'max:255'],
            'representante_dni' => ['nullable', 'string', 'max:20'],
            'titular_cuenta' => ['nullable', 'string', 'max:255'],
            'cuentas' => ['sometimes', 'array'],
            'cuentas.*.banco' => ['required_with:cuentas', 'string', 'max:100'],
            'cuentas.*.moneda' => ['required_with:cuentas', 'string', 'max:10'],
            'cuentas.*.numero_cuenta' => ['required_with:cuentas', 'string', 'max:50'],
            'cuentas.*.cci' => ['nullable', 'string', 'max:50'],
            'modo_sunat' => ['nullable', 'in:beta,produccion'],
            'sunat_usuario_sol' => ['nullable', 'string', 'max:255'],
            'sunat_clave_sol' => ['nullable', 'string', 'max:255'],
        ];
    }
}
