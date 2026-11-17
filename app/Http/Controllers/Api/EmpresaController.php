<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Models\Empresa;

class EmpresaController extends Controller
{
    public function show()
    {
        return response()->json(Empresa::actual()->load('cuentasBancarias'));
    }

    public function update(UpdateEmpresaRequest $request)
    {
        $empresa = Empresa::actual();
        $datos = $request->validated();

        if (empty($datos['sunat_clave_sol'])) {
            unset($datos['sunat_clave_sol']);
        }

        $cuentas = collect($datos['cuentas'] ?? null);
        unset($datos['cuentas']);

        $empresa->update($datos);

        if ($request->has('cuentas')) {
            $empresa->cuentasBancarias()->delete();
            $empresa->cuentasBancarias()->createMany(
                $cuentas->values()->map(fn ($cuenta, $orden) => [...$cuenta, 'orden' => $orden])->all()
            );
        }

        return response()->json($empresa->load('cuentasBancarias'));
    }
}
