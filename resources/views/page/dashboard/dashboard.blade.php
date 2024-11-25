@extends('template.app.app')
@section('header_title') {{__('Dashboard')}} @endsection

@section('content-main')

<h1 class="text-light mb-4">Dashboard</h1>
<div class="row">
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
            <div class="card-header text-light">
                Clientes
            </div>
            <div class="card-body text-light">
                <h5 class="card-title">250</h5>
                <p class="card-text">Total de clientes registrados</p>
                <a href="#" class="btn btn-primary">Ver detalles</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
            <div class="card-header text-light">
                Productos
            </div>
            <div class="card-body text-light">
                <h5 class="card-title">1,500</h5>
                <p class="card-text">Productos en inventario</p>
                <a href="#" class="btn btn-primary">Ver detalles</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
            <div class="card-header text-light">
                Pedidos
            </div>
            <div class="card-body text-light">
                <h5 class="card-title">75</h5>
                <p class="card-text">Pedidos pendientes</p>
                <a href="#" class="btn btn-primary">Ver detalles</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
            <div class="card-header text-light">
                Facturas
            </div>
            <div class="card-body text-light">
                <h5 class="card-title">$52,000</h5>
                <p class="card-text">Ingresos del mes</p>
                <a href="#" class="btn btn-primary">Ver detalles</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts_app')

<script>
</script>

@endpush