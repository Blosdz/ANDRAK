@php
    /** @var \App\Models\Comprobante $comprobante */
    /** @var \App\Models\Empresa $empresa */

    $esquemasIgv = [
        '10' => ['id' => '1000', 'nombre' => 'IGV', 'codigo' => 'VAT'],
        '20' => ['id' => '9997', 'nombre' => 'EXO', 'codigo' => 'VAT'],
        '30' => ['id' => '9998', 'nombre' => 'INA', 'codigo' => 'FRE'],
    ];

    $montoEnLetras = \App\CoreFacturalo\Helpers\NumeroALetras::convertir(
        (float) $comprobante->total,
        $comprobante->moneda_id === 'USD' ? 'DOLARES AMERICANOS' : 'SOLES'
    );
@endphp
{!! '<'.'?xml version="1.0" encoding="utf-8" standalone="no"?'.'>' !!}
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent/>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>{{ $comprobante->serie }}-{{ $comprobante->numero }}</cbc:ID>
    <cbc:IssueDate>{{ $comprobante->fecha_emision->format('Y-m-d') }}</cbc:IssueDate>
    <cbc:IssueTime>{{ $comprobante->hora_emision }}</cbc:IssueTime>
    <cbc:InvoiceTypeCode listID="0101">{{ $comprobante->tipo_comprobante_id }}</cbc:InvoiceTypeCode>
    <cbc:Note languageLocaleID="1000"><![CDATA[{{ $montoEnLetras }}]]></cbc:Note>
    <cbc:DocumentCurrencyCode>{{ $comprobante->moneda_id }}</cbc:DocumentCurrencyCode>
    <cac:Signature>
        <cbc:ID>{{ $empresa->ruc }}</cbc:ID>
        <cbc:Note>{{ $empresa->razon_social ?: $empresa->nombre_comercial }}</cbc:Note>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>{{ $empresa->ruc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[{{ $empresa->nombre_comercial }}]]></cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#{{ $empresa->ruc }}</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6">{{ $empresa->ruc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[{{ $empresa->nombre_comercial }}]]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $empresa->razon_social ?: $empresa->nombre_comercial }}]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:AddressTypeCode>{{ $empresa->establecimiento_codigo }}</cbc:AddressTypeCode>
                    @if($empresa->direccion)
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[{{ $empresa->direccion }}]]></cbc:Line>
                    </cac:AddressLine>
                    @endif
                    <cac:Country>
                        <cbc:IdentificationCode>PE</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
            @if($empresa->telefono)
            <cac:Contact>
                <cbc:Telephone>{{ $empresa->telefono }}</cbc:Telephone>
            </cac:Contact>
            @endif
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="{{ $comprobante->cliente->tipo_documento_id }}">{{ $comprobante->cliente->ruc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $comprobante->cliente->nombre }}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
    </cac:PaymentTerms>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{{ $comprobante->moneda_id }}">{{ $comprobante->total_igv }}</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="{{ $comprobante->moneda_id }}">{{ $comprobante->subtotal }}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="{{ $comprobante->moneda_id }}">{{ $comprobante->total_igv }}</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="{{ $comprobante->moneda_id }}">{{ $comprobante->subtotal }}</cbc:LineExtensionAmount>
        <cbc:TaxInclusiveAmount currencyID="{{ $comprobante->moneda_id }}">{{ $comprobante->total }}</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="{{ $comprobante->moneda_id }}">{{ $comprobante->total }}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    @foreach($comprobante->items as $item)
    @php($esquema = $esquemasIgv[$item->tipo_afectacion_igv_id] ?? $esquemasIgv['10'])
    <cac:InvoiceLine>
        <cbc:ID>{{ $loop->iteration }}</cbc:ID>
        <cbc:InvoicedQuantity unitCode="{{ $item->unidad_medida_id }}">{{ $item->cantidad }}</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="{{ $comprobante->moneda_id }}">{{ $item->subtotal }}</cbc:LineExtensionAmount>
        <cac:PricingReference>
            <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="{{ $comprobante->moneda_id }}">{{ $item->precio_unitario }}</cbc:PriceAmount>
                <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
        </cac:PricingReference>
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{{ $comprobante->moneda_id }}">{{ $item->igv }}</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{{ $comprobante->moneda_id }}">{{ $item->subtotal }}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{ $comprobante->moneda_id }}">{{ $item->igv }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>{{ $item->tipoAfectacionIgv->porcentaje }}</cbc:Percent>
                    <cbc:TaxExemptionReasonCode>{{ $item->tipo_afectacion_igv_id }}</cbc:TaxExemptionReasonCode>
                    <cac:TaxScheme>
                        <cbc:ID>{{ $esquema['id'] }}</cbc:ID>
                        <cbc:Name>{{ $esquema['nombre'] }}</cbc:Name>
                        <cbc:TaxTypeCode>{{ $esquema['codigo'] }}</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description><![CDATA[{{ $item->descripcion }}]]></cbc:Description>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="{{ $comprobante->moneda_id }}">{{ $item->precio_unitario }}</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
    @endforeach
</Invoice>
