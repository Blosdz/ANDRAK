// Deterministic "random" color per tag string: same code always renders in
// the same color (stable across reloads/renders), but different codes are
// visually distinct. Palette kept at Industrial Integrity's saturation/lightness
// so every color reads as a legible chip against light and dark text.
const PALETTE = [
    { bg: '#fde2e1', text: '#b3261e' },
    { bg: '#ffe6c2', text: '#8a4b00' },
    { bg: '#fef3d6', text: '#92660a' },
    { bg: '#e3f2c8', text: '#4c6b0a' },
    { bg: '#d7f5dd', text: '#0a5a26' },
    { bg: '#d3f2ea', text: '#0a6b57' },
    { bg: '#d6e8ff', text: '#0a4d8a' },
    { bg: '#dee8ff', text: '#001b3d' },
    { bg: '#e4e0ff', text: '#3d2e8a' },
    { bg: '#f2ddf5', text: '#6b1f75' },
    { bg: '#fde0ee', text: '#8a1f5c' },
    { bg: '#e2e8f0', text: '#44474e' },
];

function hashString(value) {
    let hash = 0;
    for (let i = 0; i < value.length; i++) {
        hash = (hash << 5) - hash + value.charCodeAt(i);
        hash |= 0;
    }
    return Math.abs(hash);
}

export function tagColorFor(value) {
    const key = String(value || '');
    return PALETTE[hashString(key) % PALETTE.length];
}
