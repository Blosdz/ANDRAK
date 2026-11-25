<?php

namespace App\Http\Controllers\Api;

use App\CoreFacturalo\Helpers\Certificate\GenerateCertificate;
use App\CoreFacturalo\WS\Signed\Certificate\X509Certificate;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CertificadoController extends Controller
{
    public function show()
    {
        $empresa = Empresa::findOrFail(request()->integer('empresa_id', 1));

        return response()->json([
            'certificado' => $empresa->certificado,
            'vencimiento' => $empresa->certificado_vencimiento,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file'],
            'password' => ['required', 'string'],
        ]);

        $empresa = Empresa::findOrFail($request->integer('empresa_id', 1));

        try {
            $pfx = file_get_contents($request->file('file')->getRealPath());
            $pem = GenerateCertificate::typePEM($pfx, $request->input('password'));

            $carpeta = storage_path('app/certificates');
            if (! File::isDirectory($carpeta)) {
                File::makeDirectory($carpeta, 0755, true);
            }

            $nombre = 'certificate_'.$empresa->ruc.'.pem';
            File::put($carpeta.'/'.$nombre, $pem);

            $vencimiento = (new X509Certificate($pfx, $request->input('password')))->getExpiration();

            $empresa->update([
                'certificado' => $nombre,
                'certificado_vencimiento' => $vencimiento,
            ]);

            return response()->json([
                'success' => true,
                'certificado' => $nombre,
                'vencimiento' => $empresa->certificado_vencimiento,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar el certificado: '.$e->getMessage(),
            ], 422);
        }
    }

    public function destroy()
    {
        $empresa = Empresa::findOrFail(request()->integer('empresa_id', 1));

        if ($empresa->certificado) {
            $ruta = storage_path('app/certificates/'.$empresa->certificado);
            if (File::exists($ruta)) {
                File::delete($ruta);
            }
        }

        $empresa->update([
            'certificado' => null,
            'certificado_vencimiento' => null,
        ]);

        return response()->json(['success' => true]);
    }
}
