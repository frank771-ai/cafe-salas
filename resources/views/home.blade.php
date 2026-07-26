@extends('layouts.app')

@section('title', 'Tienda virtual de café y productos costarricenses')

@section('content')
    {{-- Las fotografías aportadas por el equipo conectan la compra con el origen humano del café. --}}
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero-inner">
            <div class="hero-copy">
                <span class="eyebrow text-light">Café costarricense · precio justo</span>
                <h1 id="hero-title">Calidad de origen.<br>Precio que invita a volver.</h1>
                <p class="lead">Café de especialidad y productos artesanales con trazabilidad, sabor auténtico y precios pensados para disfrutarlos todos los días.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-purchase btn-lg" href="{{ route('products.index') }}">Comprar café costarricense</a>
                    <a class="btn btn-outline-light btn-lg" href="#nuestra-historia">Conocer a quienes lo hacen</a>
                </div>
                <p class="hero-note">Opciones desde ₡3.500 · Envío gratis desde ₡20.000 · Compra protegida</p>
            </div>
        </div>
    </section>

    <section class="value-strip" aria-label="Compromisos de Café Salas">
        <div class="container">
            <div class="row g-0">
                <div class="col-md-4 value-item">
                    <span class="value-number">01</span>
                    <div><strong>Cosecha costarricense</strong><small>Origen que puede reconocer y disfrutar.</small></div>
                </div>
                <div class="col-md-4 value-item">
                    <span class="value-number">02</span>
                    <div><strong>Precios competitivos</strong><small>Calidad superior sin pagar de más.</small></div>
                </div>
                <div class="col-md-4 value-item">
                    <span class="value-number">03</span>
                    <div><strong>Compra sin sorpresas</strong><small>Totales, impuestos y envío siempre claros.</small></div>
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
                            <div class="category-copy">
                                <h3>{{ $category->name }}</h3>
                                <p>{{ $category->description }}</p>
                                <small>{{ $category->products_count }} productos</small>
                            </div>
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
                    <figure class="story-image">
                        <img src="{{ asset('images/brand/coffee-producer-harvest.png') }}" alt="Productor recolectando cerezas maduras de café en una finca costarricense" width="1536" height="2048" loading="lazy">
                        <figcaption>Personas reales · café con historia</figcaption>
                    </figure>
                </div>
                <div class="col-lg-6 story-copy">
                    <span class="eyebrow">Del cafetal a su mesa</span>
                    <h2 id="story-title">Cuando conoce el origen, cada taza sabe mejor.</h2>
                    <p class="lead">Detrás de una gran taza hay personas, experiencia y una cosecha cuidada. Café Salas acerca ese trabajo a su mesa con productos de calidad y precios accesibles.</p>
                    <p>No vendemos lujo distante. Ofrecemos calidad real y cercana: la que se reconoce en el aroma, en el sabor y en las manos que la hacen posible.</p>
                    <div class="story-facts" aria-label="Datos de nuestra propuesta">
                        <div><strong>100%</strong><span>identidad costarricense</span></div>
                        <div><strong>4</strong><span>colecciones para descubrir</span></div>
                        <div><strong>₡20 mil</strong><span>envío gratuito</span></div>
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
