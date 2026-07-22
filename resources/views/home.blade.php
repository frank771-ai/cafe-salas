@extends('layouts.app')

@section('title', 'Café con origen')

@section('content')
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5 py-5">
            <div class="col-lg-6">
                <span class="eyebrow text-light">De la finca a su mesa</span>
                <h1 class="display-3">Costa Rica se saborea en cada taza.</h1>
                <p class="lead">Café de especialidad y complementos seleccionados para celebrar el trabajo de manos costarricenses.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-cream btn-lg" href="{{ route('products.index') }}">Explorar catálogo</a>
                    <a class="btn btn-outline-light btn-lg" href="#categorias">Ver categorías</a>
                </div>
                <div class="hero-proof mt-5"><span>13% IVA calculado</span><span>Envíos nacionales</span><span>Pago protegido</span></div>
            </div>
            <div class="col-lg-6">
                <div class="hero-art" role="img" aria-label="Ilustración de una taza de café entre montañas costarricenses">
                    <div class="sun"></div><div class="mountain mountain-one"></div><div class="mountain mountain-two"></div><div class="cup"><span></span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space" id="categorias">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Encuentre su ritual</span><h2>Categorías</h2></div><a href="{{ route('products.index') }}">Ver todo →</a></div>
        <div class="row g-4">
            @foreach($categories as $category)
                <div class="col-sm-6 col-lg-3"><a class="category-card" href="{{ route('products.index', ['category' => $category->slug]) }}"><span class="category-number">0{{ $loop->iteration }}</span><h3>{{ $category->name }}</h3><p>{{ $category->description }}</p><small>{{ $category->products_count }} productos</small></a></div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space bg-warm">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Selección de la casa</span><h2>Productos destacados</h2></div></div>
        <div class="row g-4">@foreach($featured as $product)<div class="col-md-6 col-lg-4"><x-product-card :product="$product" /></div>@endforeach</div>
    </div>
</section>

@if($recent->isNotEmpty())
<section class="section-space" aria-labelledby="recientes-title">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Guardados mediante cookie</span><h2 id="recientes-title">Vistos recientemente</h2></div></div>
        <div class="row g-4">@foreach($recent as $product)<div class="col-md-6 col-lg-3"><x-product-card :product="$product" /></div>@endforeach</div>
    </div>
</section>
@endif
@endsection
