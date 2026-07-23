@extends('layouts.app')
@section('title', $product->name)

@section('content')
    {{-- Visitar esta pantalla actualiza la cookie recent_products desde ProductController. --}}
    <div class="container section-space">
        <nav aria-label="Migas de pan">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Catálogo</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="product-detail-image">
                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" width="800" height="600">
                </div>
            </div>
            <div class="col-lg-6">
                <span class="eyebrow">{{ $product->category->name }}</span>
                <h1 class="display-5">{{ $product->name }}</h1>
                <p class="lead text-secondary">{{ $product->description }}</p>
                <span class="price-context">Precio justo en colones</span>
                <div class="price price-large">₡{{ number_format($product->price, 0, ',', '.') }}</div>
                <p id="product-stock" class="stock {{ $product->stock < 6 ? 'low' : '' }}">
                    {{ $product->stock > 0 ? $product->stock.' unidades disponibles' : 'Producto agotado' }}
                </p>

                <form method="POST" action="{{ route('cart.store', $product) }}" class="d-flex gap-3 align-items-end mt-4">
                    @csrf
                    <div>
                        <label class="form-label" for="quantity">Cantidad</label>
                        <input class="form-control quantity-input" type="number" id="quantity" name="quantity" min="1" max="{{ $product->stock }}" value="1" aria-describedby="product-stock" required>
                    </div>
                    <button class="btn btn-primary btn-lg" type="submit" @disabled($product->stock < 1)>Agregar al carrito</button>
                </form>

                <div class="security-note mt-4">
                    <strong>Compra protegida</strong>
                    <span>Los datos sensibles de pago nunca se almacenan.</span>
                </div>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="section-space bg-warm">
            <div class="container">
                <div class="section-heading"><h2>Puede interesarle</h2></div>
                <div class="row g-4">
                    @foreach ($related as $item)
                        <div class="col-md-4"><x-product-card :product="$item" /></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
