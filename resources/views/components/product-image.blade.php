@props(['product', 'loading' => 'lazy'])

@php
    $showComplete = in_array($product->slug, [
        'chocolate-oscuro-70',
        'miel-flor-cafe',
        'caja-cuatro-origenes',
    ], true);
@endphp

<img
    src="{{ asset($product->image) }}"
    alt="{{ $product->name }}"
    loading="{{ $loading }}"
    @class(['product-photo', 'product-photo-contain' => $showComplete])
    {{ $attributes }}
>
