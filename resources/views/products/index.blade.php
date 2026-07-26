@extends('layouts.app')
@section('title', 'Catálogo')

@section('content')
    <header class="page-header catalog-header">
        <div class="container">
            <span class="eyebrow text-light">Colección Café Salas</span>
            <h1>Café y detalles<br>con historia.</h1>
            <p>Calidad costarricense elegida para regalar, preparar y disfrutar todos los días.</p>
        </div>
    </header>

    <div class="container section-space">
        <nav class="catalog-chips" aria-label="Categorías del catálogo">
            <a class="{{ request('category') ? '' : 'active' }}" href="{{ route('products.index') }}">Todo</a>
            @foreach ($categories as $category)
                <a class="{{ request('category') === $category->slug ? 'active' : '' }}" href="{{ route('products.index', ['category' => $category->slug]) }}">{{ $category->name }}</a>
            @endforeach
        </nav>

        <div class="row g-5">
            {{-- Los parámetros GET pueden compartirse por URL y se conservan al paginar. --}}
            <aside class="col-lg-3">
                <form method="GET" action="{{ route('products.index') }}" class="filter-panel" aria-label="Filtros del catálogo">
                    <span class="eyebrow">A su gusto</span>
                    <h2 class="h4">Encuentre lo suyo</h2>
                    <p class="filter-intro">Combine nombre, categoría y presupuesto.</p>
                    <div class="mb-3">
                        <label class="form-label" for="q">Nombre o descripción</label>
                        <input class="form-control" id="q" name="q" value="{{ request('q') }}" maxlength="80" placeholder="Ej. Tarrazú">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="category">Categoría</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Todas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="min_price">Precio mín.</label>
                            <input type="number" min="0" class="form-control" id="min_price" name="min_price" value="{{ request('min_price') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="max_price">Precio máx.</label>
                            <input type="number" min="0" class="form-control" id="max_price" name="max_price" value="{{ request('max_price') }}">
                        </div>
                    </div>
                    <div class="my-3">
                        <label class="form-label" for="sort">Ordenar</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="newest">Más recientes</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Menor precio</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Mayor precio</option>
                            <option value="name" @selected(request('sort') === 'name')>Nombre A-Z</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Aplicar filtros</button>
                    <a class="btn btn-link w-100 mt-2" href="{{ route('products.index') }}">Limpiar</a>
                </form>
            </aside>

            <section class="col-lg-9" aria-live="polite">
                <div class="catalog-results-heading">
                    <h2 class="h4 mb-0">{{ $products->total() }} {{ $products->total() === 1 ? 'producto' : 'productos' }}</h2>
                    <span class="text-secondary small">Página {{ $products->currentPage() }} de {{ $products->lastPage() }}</span>
                </div>
                <div class="row g-4">
                    @forelse ($products as $product)
                        <div class="col-md-6 col-xl-4"><x-product-card :product="$product" /></div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <h3>No encontramos productos</h3>
                                <p>Pruebe con otros filtros o limpie la búsqueda.</p>
                                <a class="btn btn-primary" href="{{ route('products.index') }}">Ver todo</a>
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="mt-5">{{ $products->links() }}</div>
            </section>
        </div>
    </div>
@endsection
