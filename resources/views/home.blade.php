@extends('layouts.app')

@section('title', 'Café con origen')

@section('content')
    {{-- Una fotografía original muestra el trabajo detrás del producto y sostiene la nueva identidad premium. --}}
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero-inner">
            <div class="hero-copy">
                <span class="eyebrow text-light">Café costarricense, sin distancias</span>
                <h1 id="hero-title">Origen que se nota.<br>Calidad que se disfruta.</h1>
                <p class="lead">Café de especialidad y productos artesanales elegidos con criterio, trazabilidad y un precio honesto para todos los días.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-cream btn-lg" href="{{ route('products.index') }}">Descubrir la colección</a>
                    <a class="btn btn-outline-light btn-lg" href="#nuestra-historia">Conocer el proceso</a>
                </div>
                <p class="hero-note">Selecciones desde ₡4.200 · Compra segura · Envíos en Costa Rica</p>
            </div>
        </div>
    </section>

    <section class="value-strip" aria-label="Compromisos de Origen Tico">
        <div class="container">
            <div class="row g-0">
                <div class="col-md-4 value-item">
                    <span class="value-number">01</span>
                    <div><strong>Selección consciente</strong><small>Elegimos calidad, origen y oficio.</small></div>
                </div>
                <div class="col-md-4 value-item">
                    <span class="value-number">02</span>
                    <div><strong>Hecho en Costa Rica</strong><small>Sabores y manos de nuestra tierra.</small></div>
                </div>
                <div class="col-md-4 value-item">
                    <span class="value-number">03</span>
                    <div><strong>Precio honesto</strong><small>Calidad premium para disfrutar a diario.</small></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Navegación por categorías construida con el conteo de productos activos. --}}
    <section class="section-space" id="categorias">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Explore a su manera</span>
                    <h2>Un ritual para cada momento</h2>
                    <p class="section-intro">Desde una taza de origen hasta un detalle artesanal para compartir.</p>
                </div>
                <a class="text-link" href="{{ route('products.index') }}">Ver colección completa <span aria-hidden="true">→</span></a>
            </div>
            <div class="row g-4">
                @foreach ($categories as $category)
                    <div class="col-sm-6 col-lg-3">
                        <a class="category-card category-tone-{{ $loop->iteration }}" href="{{ route('products.index', ['category' => $category->slug]) }}">
                            <span class="category-number">0{{ $loop->iteration }}</span>
                            <div class="category-copy">
                                <h3>{{ $category->name }}</h3>
                                <p>{{ $category->description }}</p>
                                <small>{{ $category->products_count }} productos</small>
                            </div>
                            <span class="category-arrow" aria-hidden="true">↗</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="origin-story section-space" id="nuestra-historia" aria-labelledby="story-title">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="story-image" role="img" aria-label="Manos seleccionando cerezas de café maduras en una finca costarricense">
                        <span>Selección manual</span>
                    </div>
                </div>
                <div class="col-lg-6 story-copy">
                    <span class="eyebrow">Del cafetal a su mesa</span>
                    <h2 id="story-title">Lo extraordinario empieza con hacer bien lo esencial.</h2>
                    <p class="lead">Detrás de una gran taza hay paciencia, conocimiento y decisiones cuidadosas. Origen Tico celebra ese proceso con productos que cuentan de dónde vienen y por qué valen la pena.</p>
                    <p>No buscamos lujo distante. Buscamos una calidad real, cercana y consistente: la que se reconoce en el aroma, en el sabor y en las manos que la hacen posible.</p>
                    <div class="story-facts" aria-label="Datos de nuestra propuesta">
                        <div><strong>100%</strong><span>identidad costarricense</span></div>
                        <div><strong>4</strong><span>colecciones para descubrir</span></div>
                        <div><strong>₡30 mil</strong><span>envío gratuito</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space featured-section">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Selección de la casa</span>
                    <h2>Favoritos con carácter propio</h2>
                    <p class="section-intro">Productos elegidos por su historia, sabor y capacidad de convertir lo cotidiano en un buen momento.</p>
                </div>
                <a class="text-link" href="{{ route('products.index') }}">Comprar todo <span aria-hidden="true">→</span></a>
            </div>
            <div class="row g-4">
                @foreach ($featured as $product)
                    <div class="col-md-6 col-lg-4"><x-product-card :product="$product" /></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="quality-process section-space" aria-labelledby="quality-title">
        <div class="container">
            <div class="quality-heading">
                <span class="eyebrow text-light">Nuestro estándar</span>
                <h2 id="quality-title">Calidad visible, de principio a fin.</h2>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <article class="process-card">
                        <span>01</span><h3>Elegimos con criterio</h3>
                        <p>Origen, materiales, sabor y oficio deben aportar algo auténtico.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="process-card">
                        <span>02</span><h3>Cuidamos la experiencia</h3>
                        <p>Información clara, precio transparente y una compra sin complicaciones.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="process-card">
                        <span>03</span><h3>Entregamos confianza</h3>
                        <p>Seguimiento, factura y atención pensados para que vuelva con gusto.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    {{-- Esta sección solo aparece cuando la cookie contiene productos válidos y activos. --}}
    @if ($recent->isNotEmpty())
        <section class="section-space" aria-labelledby="recientes-title">
            <div class="container">
                <div class="section-heading">
                    <div><span class="eyebrow">Su recorrido</span><h2 id="recientes-title">Vistos recientemente</h2></div>
                </div>
                <div class="row g-4">
                    @foreach ($recent as $product)
                        <div class="col-md-6 col-lg-3"><x-product-card :product="$product" /></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
