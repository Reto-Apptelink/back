@extends('template.app.app')
@section('header_title') {{__('New Order')}} @endsection

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

<h1 class="text-light mb-4">Crear Orden</h1>
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <form id="createOrderForm" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="clientSearch" class="form-label text-light">Buscar Cliente (RUC o Nombre)</label>
                            <input type="text" class="form-control" name="clientSearch" id="clientSearch" autocomplete="off" required />
                            <ul id="clients-list" class="list-group position-absolute w-100 mt-1"
                                style="z-index: 1000; max-height: 100px; overflow-y: auto;">
                            </ul>
                            <div class="invalid-feedback">
                                Por favor, seleccione un cliente.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="orderDate" class="form-label text-light">Fecha de Pedido</label>
                            <input type="date" class="form-control" name="orderDate" id="orderDate" required>
                            <div class="invalid-feedback">
                                La fecha no puede ser futura.
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="productSearch" class="form-label text-light">Buscar Producto (ID o Nombre)</label>
                            <input type="text" class="form-control" name="productSearch" id="productSearch" autocomplete="off" />
                            <ul id="products-list" class="list-group position-absolute w-100 mt-1"
                                style="z-index: 1000; max-height: 100px; overflow-y: auto;">
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" id="addProductBtn">Agregar Producto</button>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table fs-sm" id="orderTable">
                            <thead>
                                <tr>
                                    <th class="text-light">Producto</th>
                                    <th class="text-light">Cantidad</th>
                                    <th class="text-light">Precio Unitario</th>
                                    <th class="text-light">Indicador de IGV/IVA</th>
                                    <th class="text-light">Subtotal</th>
                                    <th class="text-light">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los productos se agregarán aquí dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td id="totalAmount">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary">Crear Factura</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts_app')
<script src="{{asset('assets/js/api/apiClient.js')}}"></script>
<script src="{{asset('assets/js/helpers/utils.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createOrderForm');
        const clientSearch = document.getElementById('clientSearch');
        const clientsList = document.getElementById('clients-list');
        const orderDate = document.getElementById('orderDate');
        const productSearch = document.getElementById('productSearch');
        const productsList = document.getElementById('products-list');
        const addProductBtn = document.getElementById('addProductBtn');
        const orderTable = document.getElementById('orderTable');
        const totalAmount = document.getElementById('totalAmount');

        // Búsqueda de cliente
        clientSearch.addEventListener('input', function() {
            const searchQuery = this.value.trim();
            if (searchQuery.length >= 3 || searchQuery.length === 0) {
                searchClients();
            }
        });

        // Validar fecha
        orderDate.addEventListener('change', function() {
            const today = new Date();
            const selectedDate = new Date(this.value);
            if (selectedDate > today) {
                this.setCustomValidity('La fecha no puede ser futura');
            } else {
                this.setCustomValidity('');
            }
        });

        // Búsqueda de producto
        productSearch.addEventListener('input', function() {
            const searchQuery = this.value.trim();
            if (searchQuery.length >= 3 || searchQuery.length === 0) {
                searchProducts();
            }
        });

        // Agregar producto a la tabla
        addProductBtn.addEventListener('click', function() {
            const productName = productSearch.value;
            const productId = productSearch.dataset.selectedProductId || null;
            const productPrice = productSearch.dataset.selectedProductPrice || 0;

            if (!productName || !productId) {
                alert('Seleccione un producto válido de la lista antes de agregarlo.');
                return;
            }

            const newRow = orderTable.tBodies[0].insertRow();
            newRow.innerHTML = `
                <td>${productName}</td>
                <td><input type="number" class="form-control border text-dark quantity" value="1" min="1"></td>
                <td><input type="number" class="form-control border text-dark price" value="${parseFloat(productPrice).toFixed(2)}" min="0" step="0.01"></td>
                <td><input type="number" class="form-control border text-dark tax" value="0.00" min="0" step="0.01"></td>
                <td class="subtotal">$${parseFloat(productPrice).toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button></td>
            `;

            // Limpiar el campo de búsqueda
            productSearch.value = '';
            productSearch.dataset.selectedProductId = '';
            productSearch.dataset.selectedProductPrice = '';
            updateTotals();

        });

        // Eliminar producto de la tabla
        orderTable.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('tr').remove();
                updateTotals();
            }
        });

        // Actualizar subtotales y total
        orderTable.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity') || e.target.classList.contains('price') || e.target.classList.contains('tax')) {
                updateTotals();
            }
        });

        function updateTotals() {
            let total = 0;
            const rows = orderTable.tBodies[0].rows;
            for (let i = 0; i < rows.length; i++) {
                const quantity = parseFloat(rows[i].querySelector('.quantity').value) || 0;
                const price = parseFloat(rows[i].querySelector('.price').value) || 0;
                const tax = parseFloat(rows[i].querySelector('.tax').value) || 0;
                const subtotal = (quantity * price) + tax;
                rows[i].querySelector('.subtotal').textContent = '$' + subtotal.toFixed(2);
                total += subtotal;
            }
            totalAmount.textContent = '$' + total.toFixed(2);
        }

        async function searchProducts() {
            const query = productSearch.value.trim();
            if (query === '') {
                productsList.innerHTML = '';
                return;
            }

            const url = `products`;
            const queryParams = new URLSearchParams({
                query
            });
            const headers = {
                'Authorization': `Bearer ${token}`,
                "Accept": "application/json",
            };

            const data = await fetchDataFromApi(url, queryParams, 'GET', headers);
            productsList.innerHTML = '';

            if (data && data.success && data.data.productCatalog.length > 0) {
                data.data.productCatalog.forEach(product => {
                    const listItem = document.createElement('li');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.textContent = `${product.id} - ${product.name} ($${product.price})`;
                    listItem.dataset.productId = product.id;
                    listItem.dataset.productName = product.name;
                    listItem.dataset.productPrice = product.price;
                    listItem.addEventListener('click', function() {
                        productSearch.value = product.name;
                        productSearch.dataset.selectedProductId = product.id;
                        productSearch.dataset.selectedProductPrice = product.price;
                        productsList.innerHTML = '';
                    });
                    productsList.appendChild(listItem);
                });
            } else {
                productsList.innerHTML = '<li class="list-group-item text-center">No se encontraron productos</li>';
            }
        }

        async function searchClients() {
            const query = clientSearch.value.trim();
            if (query === '') {
                clientsList.innerHTML = '';
                return;
            }

            const url = `customers`;
            const queryParams = new URLSearchParams({
                query
            });
            const headers = {
                'Authorization': `Bearer ${token}`,
                "Accept": "application/json",
            };

            const data = await fetchDataFromApi(url, queryParams, 'GET', headers);
            clientsList.innerHTML = '';

            if (data && data.success && data.data.customers.length > 0) {
                data.data.customers.forEach(customer => {
                    const listItem = document.createElement('li');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.textContent = `${customer.id} - ${customer.name}`;
                    listItem.dataset.customer = customer.id;
                    listItem.dataset.customerName = customer.name;
                    listItem.addEventListener('click', function() {
                        clientSearch.value = customer.name;
                        clientSearch.dataset.selectedCustomerId = customer.id;
                        clientsList.innerHTML = '';
                    });
                    clientsList.appendChild(listItem);
                });
            } else {
                clientsList.innerHTML = '<li class="list-group-item text-center">No se encontraron cliente</li>';
            }
        }

        // Enviar orden al servidor
        /* createOrderBtn.addEventListener('click', async function() {
            const customerId = document.querySelector('#customerId').value;
            const userId = document.querySelector('#userId').value;
            const orderDate = document.querySelector('#orderDate').value;
            const taxRate = parseFloat(document.querySelector('#taxRate').value) || 0;
            const discount = parseFloat(document.querySelector('#discount').value) || 0;

            if (!customerId || !userId || !orderDate) {
                alert("Por favor, complete todos los campos obligatorios.");
                return;
            }

            const orderDetails = [];
            const rows = orderTable.tBodies[0].rows;

            for (let i = 0; i < rows.length; i++) {
                const productId = rows[i].querySelector('.price').dataset.productId;
                const quantity = parseFloat(rows[i].querySelector('.quantity').value) || 0;

                if (productId && quantity > 0) {
                    orderDetails.push({
                        product_id: parseInt(productId),
                        quantity: quantity
                    });
                }
            }

            if (orderDetails.length === 0) {
                alert("Por favor, agregue al menos un producto a la orden.");
                return;
            }

            const orderData = {
                customer_id: parseInt(customerId),
                user_id: parseInt(userId),
                order_date: orderDate,
                tax_rate: taxRate,
                discount: discount,
                order_details: orderDetails
            };

            const url = "http://127.0.0.1:8000/api-iims/digitales/v1/orders/register";
            const method = "POST";

            const result = await fetchDataFromApi(url, method, orderData);

            if (result) {
                alert("Orden creada exitosamente.");
                // Opcional: Limpia el formulario o redirige
            }
        }); */

        // Validación del formulario
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
</script>
@endpush