<?php

namespace App\CoreFacturalo;

use App\CoreFacturalo\WS\Client\WsClient;
use App\CoreFacturalo\WS\Response\BillResult;
use App\CoreFacturalo\WS\Services\BillSender;
use App\CoreFacturalo\WS\Services\SunatEndpoints;
use App\CoreFacturalo\WS\Signed\XmlSigned;
use App\CoreFacturalo\WS\Validator\XmlErrorCodeProvider;
use App\Models\Comprobante;
use App\Models\Empresa;
use Exception;
use Illuminate\Support\Facades\Storage;

class Facturalo
{
    public function generarXml(Comprobante $comprobante): string
    {
        $empresa = $comprobante->empresa ?? Empresa::actual();

        return view('xml.comprobante', [
            'comprobante' => $comprobante->load(['cliente', 'items.tipoAfectacionIgv']),
            'empresa' => $empresa,
        ])->render();
    }

    public function firmarXml(Comprobante $comprobante, string $xml): string
    {
        $empresa = $comprobante->empresa ?? Empresa::actual();

        if (! $empresa->certificado) {
            throw new Exception('No hay un certificado digital configurado.');
        }

        $rutaCertificado = storage_path('app/certificates/'.$empresa->certificado);

        $firmador = new XmlSigned();
        $firmador->setCertificateFromFile($rutaCertificado);

        return $firmador->signXml($xml);
    }

    public function enviarSunat(Comprobante $comprobante, string $xmlFirmado): BillResult
    {
        $empresa = $comprobante->empresa ?? Empresa::actual();

        $client = new WsClient();
        $client->setCredentials($empresa->sunat_usuario_sol, $empresa->sunat_clave_sol);
        $client->setService($empresa->modo_sunat === 'produccion' ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);

        $sender = new BillSender();
        $sender->setClient($client);
        $sender->setCodeProvider(new XmlErrorCodeProvider());

        $nombreArchivo = sprintf('%s-%s-%s-%s', $empresa->ruc, $comprobante->tipo_comprobante_id, $comprobante->serie, $comprobante->numero);

        return $sender->send($nombreArchivo, $xmlFirmado);
    }

    public function procesarCdr(Comprobante $comprobante, BillResult $resultado, string $xmlFirmado): void
    {
        $empresa = $comprobante->empresa ?? Empresa::actual();
        $carpeta = "comprobantes/{$empresa->ruc}/{$comprobante->tipo_comprobante_id}";

        $nombreBase = sprintf('%s-%s', $comprobante->serie, $comprobante->numero);
        $rutaXml = "{$carpeta}/{$nombreBase}.xml";
        Storage::put($rutaXml, $xmlFirmado);

        $datos = [
            'xml_path' => $rutaXml,
        ];

        if ($resultado->isSuccess()) {
            $cdr = $resultado->getCdrResponse();
            $codigo = (int) $cdr->getCode();

            $rutaCdr = "{$carpeta}/{$nombreBase}-cdr.zip";
            Storage::put($rutaCdr, $resultado->getCdrZip());

            $datos['cdr_path'] = $rutaCdr;
            $datos['has_cdr'] = true;
            $datos['estado'] = match (true) {
                $codigo === 0 => 'aceptado',
                $codigo >= 2000 && $codigo < 4000 => 'observado',
                default => 'rechazado',
            };
            $datos['respuesta_sunat'] = [
                'codigo' => $cdr->getCode(),
                'descripcion' => $cdr->getDescription(),
                'notas' => $cdr->getNotes(),
            ];

            $hash = (new \App\CoreFacturalo\Helpers\Xml\XmlHash())->getHashSign($xmlFirmado);
            $datos['hash'] = $hash;
            $datos['qr'] = implode('|', [
                $empresa->ruc,
                $comprobante->tipo_comprobante_id,
                $comprobante->serie,
                $comprobante->numero,
                $comprobante->total_igv,
                $comprobante->total,
                $comprobante->fecha_emision->format('Y-m-d'),
                $comprobante->cliente->tipo_documento_id,
                $comprobante->cliente->ruc,
            ]);
        } else {
            $error = $resultado->getError();
            $datos['estado'] = 'rechazado';
            $datos['respuesta_sunat'] = [
                'codigo' => $error?->getCode(),
                'descripcion' => $error?->getMessage(),
            ];
        }

        $comprobante->update($datos);
    }

    /**
     * Genera, firma, envía y procesa el resultado de un comprobante en un solo paso.
     */
    public function emitir(Comprobante $comprobante): Comprobante
    {
        $xml = $this->generarXml($comprobante);
        $xmlFirmado = $this->firmarXml($comprobante, $xml);
        $resultado = $this->enviarSunat($comprobante, $xmlFirmado);
        $this->procesarCdr($comprobante, $resultado, $xmlFirmado);

        return $comprobante->fresh();
    }
}
