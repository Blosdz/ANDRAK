// Mirrors NormalizaCodigoProducto on the backend: strips a leading '#',
// lowercases, and collapses anything that isn't a letter/number into '_'.
// Used to preview the #tag a "codigo" field will be saved as, client-side.
export function normalizarCodigo(value) {
    let codigo = String(value || '').trim();
    if (!codigo) return '';

    codigo = codigo.replace(/^#+/, '').toLowerCase();
    codigo = codigo.replace(/[^a-z0-9]+/g, '_');
    codigo = codigo.replace(/^_+|_+$/g, '');

    return codigo;
}
