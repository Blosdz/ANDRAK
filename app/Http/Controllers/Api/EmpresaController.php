<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Models\Empresa;
use App\Models\Cotizacion;
use App\Models\Comprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    public function index()
    {
        Empresa::actual();

        return response()->json(Empresa::query()->select('id', 'nombre_comercial', 'razon_social', 'ruc')->orderBy('id')->get());
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:255'],
            'razon_social' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'in:10730862453', Rule::unique('empresa', 'ruc')],
        ]);

        return response()->json(Empresa::create($datos), 201);
    }

    public function showEmpresa(Empresa $empresa)
    {
        return response()->json($empresa->load('cuentasBancarias'));
    }

    public function show()
    {
        return response()->json(Empresa::actual()->load('cuentasBancarias'));
    }

    public function update(UpdateEmpresaRequest $request)
    {
        return $this->guardar($request, Empresa::actual());
    }

    public function updateEmpresa(UpdateEmpresaRequest $request, Empresa $empresa)
    {
        return $this->guardar($request, $empresa);
    }

    private function guardar(UpdateEmpresaRequest $request, Empresa $empresa)
    {
        $datos = $request->validated();

        if ($datos['ruc'] !== $empresa->ruc &&
            (Cotizacion::where('empresa_id', $empresa->id)->exists() || Comprobante::where('empresa_id', $empresa->id)->exists())) {
            return response()->json(['message' => 'El RUC no se puede cambiar porque este emisor ya tiene documentos.'], 422);
        }

        if (empty($datos['sunat_clave_sol'])) {
            unset($datos['sunat_clave_sol']);
        }

        $cuentas = collect($datos['cuentas'] ?? null);
        unset($datos['cuentas']);

        DB::transaction(function () use ($empresa, $datos, $request, $cuentas) {
            $empresa->update($datos);
            if ($request->has('cuentas')) {
                $empresa->cuentasBancarias()->delete();
                $empresa->cuentasBancarias()->createMany(
                    $cuentas->values()->map(fn ($cuenta, $orden) => [...$cuenta, 'orden' => $orden])->all()
                );
            }
        });

        return response()->json($empresa->load('cuentasBancarias'));
    }
}
