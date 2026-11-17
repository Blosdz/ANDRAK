<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::query()
            ->withCount(['cotizaciones', 'ventas'])
            ->when($request->buscar, fn ($q, $buscar) => $q->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ilike', "%{$buscar}%")
                    ->orWhere('ruc', 'ilike', "%{$buscar}%")
                    ->orWhere('empresa', 'ilike', "%{$buscar}%");
            }))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($clientes);
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'cotizaciones' => fn ($q) => $q->latest(),
            'ventas' => fn ($q) => $q->latest(),
        ])->loadCount(['cotizaciones', 'ventas']);

        return response()->json($cliente);
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        return response()->json($cliente, 201);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return response()->json(null, 204);
    }
}
