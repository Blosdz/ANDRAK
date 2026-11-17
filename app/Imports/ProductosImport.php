<?php

namespace App\Imports;

use App\Models\Almacen;
use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductosImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    /** @var array<int, array{fila: int, mensaje: string}> */
    public array $errores = [];

    public int $creados = 0;

    /**
     * The template's workbook also ships an "Instrucciones" sheet — only the
     * "Productos" sheet holds real rows, so route just that one back to
     * collection() below and let the rest be ignored.
     */
    public function sheets(): array
    {
        return [
            'Productos' => $this,
        ];
    }

    public function collection(Collection $rows): void
    {
        // Row 1 is the heading, so the first data row is sheet row 2.
        $codigosVistos = [];

        foreach ($rows as $i => $row) {
            $fila = $i + 2;

            if ($this->filaVacia($row)) {
                continue;
            }

            $nombre = trim((string) ($row['nombre'] ?? ''));
            $precio = $row['precio'] ?? null;

            if ($nombre === '') {
                $this->errores[] = ['fila' => $fila, 'mensaje' => 'El nombre es obligatorio.'];
                continue;
            }

            if ($precio === null || $precio === '' || ! is_numeric($precio)) {
                $this->errores[] = ['fila' => $fila, 'mensaje' => 'El precio es obligatorio y debe ser numerico.'];
                continue;
            }

            $codigo = $this->normalizarCodigo((string) ($row['codigo'] ?? ''));

            if ($codigo !== null) {
                if (isset($codigosVistos[$codigo]) || Producto::where('codigo', $codigo)->exists()) {
                    $this->errores[] = ['fila' => $fila, 'mensaje' => "El codigo \"#{$codigo}\" ya existe."];
                    continue;
                }
                $codigosVistos[$codigo] = true;
            }

            $moneda = strtoupper(trim((string) ($row['moneda'] ?? ''))) ?: 'PEN';
            if (! in_array($moneda, ['PEN', 'USD'], true)) {
                $this->errores[] = ['fila' => $fila, 'mensaje' => "Moneda \"{$moneda}\" invalida (usa PEN o USD)."];
                continue;
            }

            $estado = strtolower(trim((string) ($row['estado'] ?? ''))) ?: 'activo';
            if (! in_array($estado, ['activo', 'inactivo'], true)) {
                $this->errores[] = ['fila' => $fila, 'mensaje' => "Estado \"{$estado}\" invalido (usa activo o inactivo)."];
                continue;
            }

            try {
                DB::transaction(function () use ($row, $nombre, $precio, $codigo, $moneda, $estado) {
                    $producto = Producto::create([
                        'nombre' => $nombre,
                        'codigo' => $codigo,
                        'descripcion' => $this->valorOpcional($row['descripcion'] ?? null),
                        'categoria' => $this->valorOpcional($row['categoria'] ?? null),
                        'precio' => (float) $precio,
                        'moneda' => $moneda,
                        'estado' => $estado,
                        'img' => $this->normalizarImagenBase64($row['imagen_base64'] ?? null),
                        'descripcion_extra' => [
                            'dimensiones' => $this->valorOpcional($row['dimensiones'] ?? null),
                            'peso' => $this->valorOpcional($row['peso'] ?? null),
                            'motor' => $this->valorOpcional($row['motor'] ?? null),
                        ],
                    ]);

                    Almacen::create([
                        'producto_id' => $producto->id,
                        'cantidad_disponible' => (int) ($row['stock_actual'] ?? 0),
                        'stock_minimo' => (int) ($row['stock_minimo'] ?? 5),
                    ]);
                });

                $this->creados++;
            } catch (\Throwable $e) {
                $this->errores[] = ['fila' => $fila, 'mensaje' => 'No se pudo guardar: '.$e->getMessage()];
            }
        }
    }

    private function filaVacia(Collection $row): bool
    {
        return $row->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isEmpty();
    }

    private function valorOpcional(mixed $valor): ?string
    {
        $valor = trim((string) ($valor ?? ''));

        return $valor === '' ? null : $valor;
    }

    private function normalizarCodigo(string $codigo): ?string
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return null;
        }

        $codigo = ltrim($codigo, '#');
        $codigo = strtolower($codigo);
        $codigo = preg_replace('/[^a-z0-9]+/', '_', $codigo);
        $codigo = trim($codigo, '_');

        return $codigo === '' ? null : $codigo;
    }

    private function normalizarImagenBase64(mixed $valor): ?string
    {
        $valor = trim((string) ($valor ?? ''));
        if ($valor === '') {
            return null;
        }

        return str_starts_with($valor, 'data:') ? $valor : "data:image/jpeg;base64,{$valor}";
    }
}
