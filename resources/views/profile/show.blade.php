@extends('layouts.app')
@section('title', 'Mi perfil')

@section('content')
    <header class="page-header compact">
        <div class="container">
            <span class="eyebrow text-light">Cuenta personal</span>
            <h1>Mi perfil</h1>
            <p>Actualice sus datos y consulte sus pedidos.</p>
        </div>
    </header>

    <div class="container section-space">
        <div class="row g-5">
            {{-- Solo se exponen los cuatro campos personales permitidos por ProfileController. --}}
            <div class="col-lg-4">
                <section class="profile-card">
                    <h2 class="h4">Datos personales</h2>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label" for="name">Nombre</label>
                            <input class="form-control" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Correo</label>
                            <input class="form-control" type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">Teléfono</label>
                            <input class="form-control" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="address">Dirección</label>
                            <textarea class="form-control" id="address" name="address" rows="4" maxlength="500">{{ old('address', auth()->user()->address) }}</textarea>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Guardar cambios</button>
                    </form>
                </section>
            </div>

            <div class="col-lg-8">
                <section>
                    <div class="section-heading">
                        <div><span class="eyebrow">Trazabilidad</span><h2>Historial de pedidos</h2></div>
                    </div>

                    @forelse ($orders as $order)
                        <article class="order-card">
                            <div class="order-card-head">
                                <div>
                                    <span class="eyebrow">{{ $order->order_number }}</span>
                                    <h3 class="h5">{{ $order->purchased_at->format('d/m/Y H:i') }}</h3>
                                </div>
                                <span class="status status-{{ $order->status }}">
                                    {{ match ($order->status) {
                                        'paid' => 'Pagado',
                                        'preparing' => 'Preparando',
                                        'shipped' => 'Enviado',
                                        'delivered' => 'Entregado',
                                        'cancelled' => 'Cancelado',
                                        default => $order->status
                                    } }}
                                </span>
                            </div>
                            <div class="order-items">
                                @foreach ($order->items as $item)
                                    <span>{{ $item->quantity }} × {{ $item->product_name }}</span>
                                @endforeach
                            </div>
                            <div class="order-card-foot">
                                <div><small>Seguimiento</small><strong>{{ $order->tracking_number }}</strong></div>
                                <div><small>Total</small><strong>₡{{ number_format($order->total, 0, ',', '.') }}</strong></div>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('orders.invoice', $order) }}">Factura</a>
                                    <a class="btn btn-sm btn-primary" href="{{ route('orders.invoice.pdf', $order) }}">PDF</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state compact">
                            <h3>Aún no tiene pedidos</h3>
                            <p>Cuando complete una compra aparecerá aquí.</p>
                            <a class="btn btn-primary" href="{{ route('products.index') }}">Comprar</a>
                        </div>
                    @endforelse

                    <div class="mt-4">{{ $orders->links() }}</div>
                </section>
            </div>
        </div>
    </div>
@endsection
