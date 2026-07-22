@props(['product'])

{{-- Componente reutilizado por portada, catálogo y productos vistos recientemente. --}}
<article class="product-card card h-100 border-0">
    <a href="{{ route('products.show', $product) }}" class="product-image-wrap">
        <img src="{{ asset($product->image) }}" class="card-img-top" alt="{{ $product->name }}" loading="lazy" width="640" height="480">
        @if ($product->featured)
            <span class="product-badge">Favorito</span>
        @endif
    </a>
    <div class="card-body d-flex flex-column">
        <span class="eyebrow">{{ $product->category->name }}</span>
        <h3 class="h5 card-title"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <p class="card-text text-secondary flex-grow-1">{{ \Illuminate\Support\Str::limit($product->description, 92) }}</p>
        <div class="d-flex align-items-center justify-content-between gap-2 mt-2">
            <strong class="price">₡{{ number_format($product->price, 0, ',', '.') }}</strong>
            <form method="POST" action="{{ route('cart.store', $product) }}">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button class="btn btn-primary" type="submit" @disabled($product->stock < 1)>{{ $product->stock ? 'Agregar' : 'Agotado' }}</button>
            </form>
        </div>
    </div>
</article>
