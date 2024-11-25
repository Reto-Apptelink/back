@extends('template.app.app')
@section('header_title') {{__('Product Catalog')}} @endsection

@section('content-main')
<h1 class="mb-4 text-white">Lista de Productos</h1>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <input type="text" class="form-control" name="query" id="query" placeholder="Buscar productos...">
    </div>
    <div class="col-md-3 mb-3 mb-md-0 d-none">
        <input type="date" class="form-control" name="queryDate" id="queryDate" placeholder="Filtrar por fecha">
    </div>
    <div class="col-md-3 ms-auto">
        <a href="{{route('app.product.create.form')}}" class="btn btn-primary w-100">{{__('Nuevo producto')}}</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="text-light">ID</th>
                <th class="text-light">Nombre</th>
                <th class="text-light">Descripción</th>
                <th class="text-light">Precio</th>
                <th class="text-light">Stock</th>
                <th class="text-light">Fecha</th>
                <th class="text-light">Acción</th>
            </tr>
        </thead>
        <tbody id="product-table-body">
            <td colspan="7" class="fs-sm align-middle text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tbody>
    </table>
</div>

<div class="row align-items-center justify-content-between py-2 pe-0 fs-sm text-white">
    <div class="col-auto d-flex">
        <span class="fs-sm" id="page-info-product"></span>
    </div>
    <div class="col-auto d-flex">
        <ul class="fs-sm mb-0 pagination align-items-center" id="paginationBodyProduct"></ul>
    </div>
</div>

@endsection

@push('scripts_app')
<script src="{{asset('assets/js/api/apiClient.js')}}"></script>
<script src="{{asset('assets/js/helpers/utils.js')}}"></script>
<script>
    document.getElementById('query').addEventListener('input', function() {
        let query = this.value;
        if (query.length >= 4 || query.length === 0) {
            productCatalog(1);
        }
    });

    productCatalog(1);

    async function productCatalog(page) {
        let query = document.getElementById('query').value.trim();
        let queryDate = document.getElementById('queryDate').value;
        let encodeDate = queryDate.replace(' to ', ',');
        query = encodeURIComponent(query);
        currentPage = page === undefined ? currentPage : page;

        const url = `products`;
        const queryParams = new URLSearchParams({
            query: query,
            queryDate: encodeDate,
            per_page: 10,
            page: currentPage
        });
        const headers = {
            'Authorization': `Bearer ${token}`,
            "Accept": "application/json",
        };
        const data = await fetchDataFromApi(url, queryParams, 'GET', headers);
        const tableBody = document.getElementById('product-table-body');
        tableBody.innerHTML = '';
        if (data && data.success) {
            if (data.data.productCatalog.length === 0) {
                tableBody.innerHTML = `
                    <tr class="position-static products-row">
                        <td colspan="7" class="text-center fs-sm">
                            <p class="fw-bo text-900 mb-0">No se encontraron resultados.</p>
                        </td>
                    </tr>
                `;
            } else {
                data.data.productCatalog.forEach(product => {
                    const row = `
                        <tr>
                            <td>${product.id}</td>
                            <td>${product.name}</td>
                            <td>${product.description}</td>
                            <td>$${product.price}</td>
                            <td>${product.stock_quantity}</td>
                            <td>${new Date(product.created_at).toLocaleDateString('es', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' })}</td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1" onclick="editProduct(${product.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }
            footerProductCatalog(data.data.pagination);
        } else {
            tableBody.innerHTML = `
                <tr class="position-static products-row">
                    <td colspan="7" class="text-center fs-sm"><p class="fw-bo text-900 mb-0">No se encontraron resultados.</p></td>
                </tr>
            `;
        }
        // contadores(data.data.counts);
    }

    async function deleteProduct(productId) {
        const confirmDelete = confirm('¿Estás seguro de que deseas eliminar este producto?');
        if (confirmDelete) {
            const url = `products/remove/${productId}`;
            const headers = {
                'Authorization': `Bearer ${token}`,
                "Accept": "application/json",
            };
            const response = await fetchDataFromApi(url, null, 'DELETE', headers);
            if (response && response.success) {
                alert('Producto eliminado exitosamente');
                productCatalog(currentPage); // Recarga el catálogo actual
            } else {
                alert('Error al eliminar el producto');
            }
        }
    }

    function footerProductCatalog(data) {
        pagination(
            data,
            'paginationBodyProduct',
            'page-info-product',
            'btn-prev-product',
            'btn-next-product',
            productCatalog
        )
    }
</script>

@endpush