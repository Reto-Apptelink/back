@extends('template.auth.app')
@section('header_title') {{__('Register')}} @endsection

@section('content-auth')

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

<div class="register-container">
    <div class="row">
        <!-- Formulario de Registro -->
        <div class="col-md-6 register-form">
            <h3 class="mb-4">Crear una nueva cuenta</h3>
            <form class="needs-validation" novalidate id="registerForm">
                <div class="mb-3">
                    <input type="text" class="form-control" name="name" id="name" placeholder="Nombre completo" required>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" id="email" placeholder="Correo electrónico" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Contraseña" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}">
                    <div class="password-requirements">
                        La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula, un número y un carácter especial.
                    </div>
                </div>
                <div class="mb-3">
                    <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="Confirmar contraseña" required>
                </div>
                <button type="submit" class="btn btn-register">Registrarse</button>
            </form>
        </div>

        <!-- Banner Informativo -->
        <div class="col-md-6 register-banner d-none d-md-block">
            <div class="pattern-bg"></div>
            <h2 class="mb-4">Únete a nuestro Sistema de Gestión de Inventarios Inteligente</h2>
            <p class="mb-4">Descubre cómo nuestra plataforma puede revolucionar tu gestión de inventario</p>

            <div class="benefits">
                <div class="benefit-item">
                    <i class="fas fa-shield-alt benefit-icon"></i>
                    <div>
                        <h5>Seguridad Avanzada</h5>
                        <p>Protección de datos de última generación para tu tranquilidad</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <i class="fas fa-chart-bar benefit-icon"></i>
                    <div>
                        <h5>Análisis Detallado</h5>
                        <p>Informes y análisis profundos para tomar decisiones informadas</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <i class="fas fa-mobile-alt benefit-icon"></i>
                    <div>
                        <h5>Acceso Móvil</h5>
                        <p>Gestiona tu inventario desde cualquier lugar con nuestra app móvil</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts_app')
<script src="{{asset('assets/js/api/apiClient.js')}}"></script>
<script>
    async function registerUser(name, email, password) {
        const modalMessageContainer = document.getElementById('modalMessageContainer');
        const modalTitle = document.getElementById('validationMessageModalLabel');
        const validationModal = new bootstrap.Modal(document.getElementById('validationMessageModal'));

        try {
            const userData = {
                name,
                email,
                password
            };
            const response = await fetchDataFromApi('register', userData, 'POST');

            // Manejar respuesta
            if (response.success) {
                modalTitle.textContent = 'Registro Exitoso.';
                modalMessageContainer.innerHTML = `
                <p class="text-success fs-sm">${response.message}</p>`;
                validationModal.show();
                return true;
            } else if (response.status === 422) {
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

    // Manejo del formulario
    document.getElementById('registerForm').addEventListener('submit', async function(event) {
        event.preventDefault(); // Prevenir envío del formulario

        // Capturar valores de los campos por su ID
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();

        // Validaciones
        if (!name || !email || !password || !confirmPassword) {
            const modalMessageContainer = document.getElementById('modalMessageContainer');
            modalMessageContainer.innerHTML = `
            <p class="text-danger fs-sm">Todos los campos son obligatorios.</p>`;
            const validationModal = new bootstrap.Modal(document.getElementById('validationMessageModal'));
            validationModal.show();
            return;
        }

        if (password !== confirmPassword) {
            const modalMessageContainer = document.getElementById('modalMessageContainer');
            modalMessageContainer.innerHTML = `
            <p class="text-danger fs-sm">Las contraseñas no coinciden.</p>`;
            const validationModal = new bootstrap.Modal(document.getElementById('validationMessageModal'));
            validationModal.show();
            return;
        }

        // Llamar a la función para registrar al usuario
        const isRegistered = await registerUser(name, email, password);

        if (isRegistered) {
            // Resetear formulario si el registro fue exitoso
            this.reset();
        }
    });
</script>
@endpush