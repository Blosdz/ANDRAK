<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use App\Models\Empresa;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    public function index()
    {
        $empresa = Empresa::findOrFail(request()->integer('empresa_id', 1));

        return response()->json($empresa->series()->with('tipoComprobante')->get());
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresa,id'],
            'tipo_comprobante_id' => ['required', 'in:01,03'],
            'serie' => ['required', 'string', 'size:4'],
            'correlativo' => ['required', 'integer', 'min:0'],
        ]);

        if (Serie::where('empresa_id', $datos['empresa_id'])->where('tipo_comprobante_id', $datos['tipo_comprobante_id'])->exists()) {
            return response()->json(['message' => 'Ya existe una serie para este tipo y emisor.'], 422);
        }

        return response()->json(Serie::create($datos)->load('tipoComprobante'), 201);
    }

    public function update(Request $request, Serie $serie)
    {
        $request->validate([
            'serie' => ['required', 'string', 'size:4'],
            'correlativo' => ['required', 'integer', 'min:0'],
        ]);

        $serie->update($request->only(['serie', 'correlativo']));

        return response()->json($serie);
    }
}
