@props(['product'])

{{-- Componente reutilizado por portada, catálogo y productos vistos recientemente. --}}
<article class="product-card card h-100 border-0">
    <a href="{{ route('products.show', $product) }}" class="product-image-wrap">
        <x-product-image :product="$product" class="card-img-top" width="640" height="480" />
        @if ($product->featured)
            <span class="product-badge">Selección</span>
        @endif
    </a>
    <div class="card-body d-flex flex-column">
        <div class="product-meta">
            <span>{{ $product->category->name }}</span>
            <span class="product-availability">{{ $product->stock > 0 ? 'Disponible' : 'Agotado' }}</span>
        </div>
        <h3 class="h5 card-title"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <p class="card-text text-secondary flex-grow-1">{{ \Illuminate\Support\Str::limit($product->description, 92) }}</p>
        <div class="product-card-footer">
            <div class="product-price"><small>Precio justo en colones</small><strong class="price">₡{{ number_format($product->price, 0, ',', '.') }}</strong></div>
            <form method="POST" action="{{ route('cart.store', $product) }}">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button
                    class="btn btn-primary"
                    type="submit"
                    aria-label="{{ $product->stock ? 'Agregar '.$product->name.' al carrito' : $product->name.' está agotado' }}"
                    @disabled($product->stock < 1)
                >{{ $product->stock ? 'Agregar' : 'Agotado' }}</button>
            </form>
        </div>
    </div>
</article>
