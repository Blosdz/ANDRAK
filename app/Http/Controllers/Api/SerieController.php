<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    public function index()
    {
        return response()->json(Serie::with('tipoComprobante')->get());
    }

    public function update(Request $request, Serie $serie)
    {
        $request->validate([
            'serie' => ['required', 'string', 'max:4'],
            'correlativo' => ['required', 'integer', 'min:0'],
        ]);

        $serie->update($request->only(['serie', 'correlativo']));

        return response()->json($serie);
    }
}
