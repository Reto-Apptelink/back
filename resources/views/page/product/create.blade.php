@extends('template.app.app')
@section('header_title') {{__('New')}} @endsection

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

<h1 class="mb-4">Crear Nuevo Producto</h1>

<div class="row justify-content-center">
    <div class="col-sm-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <form class="row g-3" id="createProductForm" novalidate>
                    <div class="col-sm-12">
                        <label for="productName" class="form-label text-white">Nombre del Producto</label>
                        <input type="text" class="form-control" name="productName" id="productName" required>
                        <div class="invalid-feedback">
                            Por favor, ingrese un nombre para el producto.
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <label for="productDescription" class="form-label text-white">Descripción</label>
                        <textarea class="form-control" name="productDescription" id="productDescription" rows="3"></textarea>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <label for="productPrice" class="form-label text-white">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="productPrice" id="productPrice" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">
                                Por favor, ingrese un precio mayor a cero.
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <label for="productQuantity" class="form-label text-white">Cantidad Stock</label>
                        <input type="number" class="form-control" name="productQuantity" id="productQuantity" min="0" required>
                        <div class="invalid-feedback">
                            Por favor, ingrese una cantidad no negativa.
                        </div>
                    </div>
                    <div class="col-s-12 col-lg-4 mx-auto">
                        <button type="submit" class="btn btn-primary w-100">Crear Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_app')
<script src="{{asset('assets/js/api/apiClient.js')}}"></script>
<script>
    document.getElementById('createProductForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        await createProduct();
    });

    async function createProduct() {
        const modalMessageContainer = document.getElementById('modalMessageContainer');
        const modalTitle = document.getElementById('validationMessageModalLabel');
        const validationModal = new bootstrap.Modal(document.getElementById('validationMessageModal'));


        const url = 'products/register';
        const headers = {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            // 'Content-Type': 'application/json',
        };

        // Obtener los valores de los campos del formulario
        const productData = {
            name: document.getElementById('productName').value.trim(),
            price: parseFloat(document.getElementById('productPrice').value),
            stock_quantity: parseInt(document.getElementById('productQuantity').value, 10),
            description: document.getElementById('productDescription').value.trim(),
        };

        try {
            // Realizar la llamada a la API usando la función fetchDataFromApi
            const response = await fetchDataFromApi(url, productData, 'POST', headers);

            // Obtener el div de mensaje para mostrar la respuesta
            const messageDiv = document.getElementById('message');
            if (response && response.success) {
                // Mostrar mensaje de éxito
                modalTitle.textContent = 'Registro Exitoso.';
                modalMessageContainer.innerHTML = `
                <p class="text-success fs-sm">${response.message}</p>`;
                validationModal.show();
                document.getElementById('createProductForm').reset(); // Limpiar el formulario
                return true;
            } else if (response.status === 422) {
                // Mostrar mensajes de error si ocurren
                modalTitle.textContent = response.message || 'Errores de Validación';
                const errorList = Object.entries(response.errors || {})
                    .map(([field, messages]) =>
                        messages.map(message => `<li class="list-group-item bg-transparent border-0 py-0">${message}</li>`).join('')
                    )
                    .join('');
                modalMessageContainer.innerHTML = `
                <ul class="list-group list-group-flush text-start fs-sm">${errorList}</ul>`;
                validationModal.show();
                return false;
            } else {
                modalTitle.textContent = response.message || 'Error inesperado';
                modalMessageContainer.innerHTML = `
                <p class="text-danger fs-sm">${response.error || 'Ocurrió un error al registrar el usuario.'}</p>`;
                validationModal.show();
                return false;
            }
        } catch (error) {
            modalTitle.textContent = 'Error del Servidor';
            modalMessageContainer.innerHTML = `
            <p class="text-danger fs-sm">No se pudo completar la solicitud. Intente más tarde.</p>`;
            validationModal.show();
            return false;
        }
    }

    /* (function() {
        'use strict'

        var form = document.getElementById('createProductForm')
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })() */
</script>
@endpush