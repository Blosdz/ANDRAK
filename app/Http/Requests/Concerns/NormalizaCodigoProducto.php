<?php

namespace App\Http\Requests\Concerns;

trait NormalizaCodigoProducto
{
    /**
     * The producto "codigo" is shown across the app as a #hashtag-style tag,
     * so it's normalized to a stable slug here (rather than trusting whatever
     * casing/punctuation the client sent) — same rules as ProductosImport.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('codigo')) {
            return;
        }

        $codigo = trim((string) $this->input('codigo'));
        $codigo = ltrim($codigo, '#');
        $codigo = strtolower($codigo);
        $codigo = preg_replace('/[^a-z0-9]+/', '_', $codigo);
        $codigo = trim($codigo, '_');

        $this->merge(['codigo' => $codigo === '' ? null : $codigo]);
    }
}
