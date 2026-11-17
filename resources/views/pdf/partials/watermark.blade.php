@if ($empresa->marca_agua ?? null)
    <img src="{{ $empresa->marca_agua }}" style="
        position: fixed;
        top: 35%;
        left: 25%;
        width: 50%;
        opacity: 0.08;
        z-index: -1;
    ">
@endif
