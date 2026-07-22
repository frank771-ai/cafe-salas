@extends('layouts.app')
@section('title', 'Carrito')

@section('content')
    <header class="page-header compact">
        <div class="container">
            <span class="eyebrow text-light">Revise su selección</span>
            <h1>Carrito de compras</h1>
        </div>
    </header>

    <div class="container section-space">
        @if ($items->isEmpty())
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true">☕</span>
                <h2>Su carrito está esperando</h2>
                <p>Explore cafés y complementos seleccionados.</p>
                <a class="btn btn-primary" href="{{ route('products.index') }}">Ir al catálogo</a>
            </div>
        @else
            <div class="row g-5">
                {{-- Líneas del carrito almacenadas en la sesión. --}}
                <div class="col-lg-8">
                    <div class="cart-list">
                        @foreach ($items as $item)
                            <article class="cart-item">
                                <img src="{{ asset($item['product']->image) }}" alt="{{ $item['product']->name }}" width="120" height="90">
                                <div class="cart-item-info">
                                    <span class="eyebrow">{{ $item['product']->category->name }}</span>
                                    <h2 class="h5">
                                        <a href="{{ route('products.show', $item['product']) }}">{{ $item['product']->name }}</a>
                                    </h2>
                                    <span>₡{{ number_format($item['product']->price, 0, ',', '.') }} c/u</span>
                                </div>

                                <form method="POST" action="{{ route('cart.update', $item['product']) }}" class="cart-quantity">
                                    @csrf
                                    @method('PATCH')
                                    <label class="visually-hidden" for="qty-{{ $item['product']->id }}">Cantidad</label>
                                    <input class="form-control" type="number" id="qty-{{ $item['product']->id }}" name="quantity" min="0" max="{{ $item['product']->stock }}" value="{{ $item['quantity'] }}">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">Actualizar</button>
                                </form>

                                <strong class="cart-line-total">₡{{ number_format($item['line_total'], 0, ',', '.') }}</strong>

                                <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger" type="submit">Eliminar</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                    <a class="btn btn-link ps-0 mt-3" href="{{ route('products.index') }}">← Seguir comprando</a>
                </div>

                {{-- Todos los importes provienen de CartService, la única fuente de reglas monetarias. --}}
                <aside class="col-lg-4">
                    <div class="summary-card">
                        <h2 class="h4">Resumen</h2>
                        <dl>
                            <div><dt>Subtotal</dt><dd>₡{{ number_format($totals['subtotal'], 0, ',', '.') }}</dd></div>
                            <div><dt>IVA (13%)</dt><dd>₡{{ number_format($totals['tax'], 0, ',', '.') }}</dd></div>
                            <div><dt>Envío</dt><dd>{{ $totals['shipping'] ? '₡'.number_format($totals['shipping'], 0, ',', '.') : 'Gratis' }}</dd></div>
                            <div class="summary-total"><dt>Total</dt><dd>₡{{ number_format($totals['total'], 0, ',', '.') }}</dd></div>
                        </dl>

                        @guest
                            <p class="small text-secondary">Debe iniciar sesión para finalizar la compra.</p>
                            {{-- El middleware auth guardará /comprar como URL prevista y volverá allí después del login. --}}
                            <a class="btn btn-primary btn-lg w-100" href="{{ route('checkout.create') }}">Ingresar y comprar</a>
                        @else
                            <a class="btn btn-primary btn-lg w-100" href="{{ route('checkout.create') }}">Continuar al pago</a>
                        @endguest

                        <p class="summary-help">Envío gratis desde ₡30.000 antes de IVA.</p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
