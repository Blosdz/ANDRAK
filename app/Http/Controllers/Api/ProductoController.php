<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProductosTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Imports\ProductosImport;
use App\Models\Almacen;
use App\Models\Producto;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    public function plantillaExcel()
    {
        return Excel::download(new ProductosTemplateExport(), 'plantilla-productos.xlsx');
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new ProductosImport();
        Excel::import($import, $request->file('archivo'));

        return response()->json([
            'creados' => $import->creados,
            'errores' => $import->errores,
        ]);
    }

    public function index(Request $request)
    {
        $productos = Producto::query()
            ->with('almacen')
            ->when($request->buscar, fn ($q, $buscar) => $q->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ilike', "%{$buscar}%")
                    ->orWhere('codigo', 'ilike', "%{$buscar}%");
            }))
            ->when($request->categoria, fn ($q, $categoria) => $q->where('categoria', $categoria))
            ->when($request->estado, fn ($q, $estado) => $q->where('estado', $estado))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->through(fn (Producto $producto) => $producto->makeHidden('img'));

        return response()->json($productos);
    }

    public function show(Producto $producto)
    {
        return response()->json($producto->load('almacen'));
    }

    public function store(StoreProductoRequest $request)
    {
        $producto = Producto::create($request->safe()->except(['cantidad_disponible', 'stock_minimo']));

        Almacen::create([
            'producto_id' => $producto->id,
            'cantidad_disponible' => $request->input('cantidad_disponible', 0),
            'stock_minimo' => $request->input('stock_minimo', 5),
        ]);

        return response()->json($producto->load('almacen'), 201);
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $producto->update($request->safe()->except(['cantidad_disponible', 'stock_minimo']));

        if ($request->has('cantidad_disponible') || $request->has('stock_minimo')) {
            $producto->almacen()->updateOrCreate(
                ['producto_id' => $producto->id],
                $request->only(['cantidad_disponible', 'stock_minimo'])
            );
        }

        return response()->json($producto->load('almacen'));
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return response()->json(null, 204);
    }
}
