@props(['product', 'loading' => 'lazy'])

@php
    $showComplete = in_array($product->slug, [
        'chocolate-oscuro-82',
        'miel-flor-cafe',
        'caja-regiones-costa-rica',
    ], true);
@endphp

<img
    src="{{ asset($product->image) }}"
    alt="{{ $product->name }}"
    loading="{{ $loading }}"
    @class(['product-photo', 'product-photo-contain' => $showComplete])
    {{ $attributes }}
>
