@extends('template.app.app')
@section('header_title') {{__('Orders')}} @endsection

@section('content-main')
@component('components.organisms.modal.modal_response_messages', [
'modalId' => 'validationMessageModal',
'dataBsBackdrop' => 'data-bs-backdrop="static"',
'modalDialogClass' => 'modal-sm modal-dialog-centered modal-dialog-scrollable',
'modalHeaderClass' => 'border-0 justify-content-center pb-0',
'modalTitleClass' => 'h6 fw-bold',
'modalTitle' => 'Error',
'showCloseButton' => false,
'modalBodyClass' => 'text-center',
'modalFooterClass' => 'border-0 pt-0',
'modalFooter' => '<button type="button" class="btn btn-success w-100" data-bs-dismiss="modal">Aceptar</button>'
])
@if($errors->any())
<ul class="list-group list-group-flush text-start fs-sm">
    @foreach ($errors->all() as $error)
    <li class="list-group-item border-0 py-0">{{ $error }}</li>
    @endforeach
</ul>
@elseif(session('error') && session('message'))
<p class="text-danger fs-sm">{{ session('message') }}</p>
@endif
<div id="modalMessageContainer">
</div>
@endcomponent

<h1 class="mb-4 text-white">{{__('Lista de Pedidos')}}</h1>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <input type="text" class="form-control" name="query" id="query" placeholder="Buscar orders...">
    </div>
    <div class="col-md-3 mb-3 mb-md-0 d-none">
        <input type="date" class="form-control" name="queryDate" id="queryDate" placeholder="Filtrar por fecha">
    </div>
    <div class="col-md-3 ms-auto">
        <a href="{{route('app.order.create.form')}}" class="btn btn-primary w-100">{{__('Nuevo pedido')}}</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="text-light">ID</th>
                <th class="text-light">Cliente</th>
                <th class="text-light">Fecha</th>
                <th class="text-light">Producto</th>
                <th class="text-light">Cantidad</th>
                <th class="text-light">Precio/U</th>
                <th class="text-light">Subtotal</th>
                <th class="text-light">Acción</th>
            </tr>
        </thead>
        <tbody id="order-table-body">
            <td colspan="8" class="fs-sm align-middle text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tbody>
    </table>
</div>

<div class="row align-items-center justify-content-between py-2 pe-0 fs-sm text-white">
    <div class="col-auto d-flex">
        <span class="fs-sm" id="page-info-order"></span>
    </div>
    <div class="col-auto d-flex">
        <ul class="fs-sm mb-0 pagination align-items-center" id="paginationBodyOrder"></ul>
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
            orderList(1);
        }
    });

    orderList(1);

    async function orderList(page) {
        let query = document.getElementById('query').value.trim();
        let queryDate = document.getElementById('queryDate').value.trim();
        let encodeDate = queryDate.replace(' to ', ',');
        query = encodeURIComponent(query);
        const currentPage = page === undefined ? 1 : page;

        const url = `orders`;
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

        const tableBody = document.getElementById('order-table-body');
        tableBody.innerHTML = ''; // Limpiar la tabla antes de cargar los datos

        if (data && data.success) {
            if (data.data.length === 0) {
                tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center fs-sm">
                        <p class="fw-bold text-900 mb-0">No se encontraron resultados.</p>
                    </td>
                </tr>
            `;
            } else {
                data.data.forEach(order => {
                    // Primera fila con rowspan
                    const rowSpan = order.details.length;
                    const firstRow = `
                    <tr>
                        <td rowspan="${rowSpan}" class="align-middle white-space-nowrap fw-semi-bold border-end text-1100 px-2 py-0">${order.order_id}</td>
                        <td rowspan="${rowSpan}" class="align-middle white-space-nowrap fw-semi-bold border-end text-1100 px-2 py-0">${order.customer_name}</td>
                        <td rowspan="${rowSpan}" class="align-middle white-space-nowrap fw-semi-bold border-end text-1100 px-2 py-0">${new Date(order.order_date).toLocaleDateString('es', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' })}</td>
                        <td>${order.details[0].product_name}</td>
                        <td>${order.details[0].quantity}</td>
                        <td>${order.details[0].unit_price}</td>
                        <td>${order.details[0].subtotal}</td>
                        <td rowspan="${rowSpan}" class="align-middle white-space-nowrap fw-semi-bold border-start text-1100 px-2 py-0">
                            <button class="btn btn-sm btn-primary me-2" onclick="editOrder(${order.order_id})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteOrder(${order.order_id})">Eliminar</button>
                        </td>
                    </tr>
                `;

                    tableBody.innerHTML += firstRow;

                    // Filas adicionales para detalles
                    for (let i = 1; i < order.details.length; i++) {
                        const detailRow = `
                        <tr>
                            <td>${order.details[i].product_name}</td>
                            <td>${order.details[i].quantity}</td>
                            <td>${order.details[i].unit_price}</td>
                            <td>${order.details[i].subtotal}</td>
                        </tr>
                    `;
                        tableBody.innerHTML += detailRow;
                    }
                });
            }

            // Actualizar la paginación
            if (data.pagination) {
                footerOrders(data.pagination);
            }
        } else {
            tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center fs-sm">
                    <p class="fw-bold text-900 mb-0">Hubo un problema al cargar las órdenes.</p>
                </td>
            </tr>
        `;
        }
    }


    async function editOrder(orderId) {
        window.location.href = `/product/edit?id=${orderId}`;
    }

    async function deleteOrder(orderId) {
        const modalMessageContainer = document.getElementById('modalMessageContainer');
        const modalTitle = document.getElementById('validationMessageModalLabel');
        const validationModal = new bootstrap.Modal(document.getElementById('validationMessageModal'));
        const confirmDelete = confirm('¿Estás seguro de que deseas eliminar este producto?');

        if (confirmDelete) {
            const url = `products/remove/${orderId}`;
            const headers = {
                'Authorization': `Bearer ${token}`,
                "Accept": "application/json",
            };
            const response = await fetchDataFromApi(url, null, 'DELETE', headers);
            if (response && response.success) {
                modalTitle.textContent = 'Exitoso.';
                modalMessageContainer.innerHTML = `
                <p class="text-success fs-sm">${response.message}</p>`;
                validationModal.show();
                productCatalog(currentPage);
                return true;
            } else {
                modalTitle.textContent = 'Error.';
                modalMessageContainer.innerHTML = `
                <p class="text-success fs-sm">Al eliminar el producto</p>`;
                validationModal.show();
            }
        }
    }

    function footerOrders(data) {
        pagination(
            data,
            'paginationBodyOrder',
            'page-info-order',
            'btn-prev-order',
            'btn-next-order',
            orderList
        )
    }
</script>

@endpush