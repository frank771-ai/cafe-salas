@props(['product', 'loading' => 'lazy'])

{{-- Conserva completa la fotografía de productos cuyo empaque no debe recortarse. --}}
@php
    $useContainFit = in_array($product->slug, [
        'chocolate-oscuro-82',
        'miel-flor-cafe',
        'caja-regiones-costa-rica',
    ], true);
@endphp

<img
    src="{{ asset($product->image) }}"
    alt="{{ $product->name }}"
    loading="{{ $loading }}"
    @class(['product-photo', 'product-photo-contain' => $useContainFit])
    {{ $attributes }}
>
