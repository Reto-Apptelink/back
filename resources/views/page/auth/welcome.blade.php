@extends('template.auth.app')
@section('header_title') {{__('Login')}} @endsection

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

<div class="login-container">
    <div class="row">
        <!-- Formulario de Login -->
        <div class="col-md-6 my-auto login-form">

            <h3 class="mb-4">Bienvenido de nuevo</h3>
            <form class="needs-validation" novalidate id="authForm">
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" id="email" placeholder="Correo electrónico" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Contraseña" required>
                    <a href="{{route('app.user.password.recovery')}}" class="forgot-password mt-2">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="btn btn-login">Iniciar Sesión</button>
                <a href="{{route('app.user.register')}}" class="btn btn-register">Crear una cuenta</a>
                <div class="social-login text-center d-none">
                    <a href="#" class="social-btn"><i class="fab fa-google text-white"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-microsoft text-white"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-apple text-white"></i></a>
                </div>
            </form>
        </div>

        <!-- Banner Informativo -->
        <div class="col-md-6 login-banner d-none d-lg-block">
            <div class="pattern-bg"></div>
            <h2 class="mb-4">Sistema de Gestión de Inventarios Inteligente</h2>
            <p class="mb-4">Descubre una nueva forma de gestionar tu inventario con tecnología de vanguardia</p>

            <div class="benefits">
                <div class="benefit-item">
                    <i class="fas fa-chart-line benefit-icon"></i>
                    <div>
                        <h5>Control en Tiempo Real</h5>
                        <p>Monitorea tu inventario en tiempo real desde cualquier dispositivo</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <i class="fas fa-robot benefit-icon"></i>
                    <div>
                        <h5>IA Predictiva</h5>
                        <p>Predicciones precisas de stock basadas en inteligencia artificial</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <i class="fas fa-sync benefit-icon"></i>
                    <div>
                        <h5>Automatización</h5>
                        <p>Procesos automatizados para reducir errores y ahorrar tiempo</p>
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
    document.getElementById('authForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!email || !password) {
            alert('Por favor, complete todos los campos.');
            return;
        }

        try {
            const response = await fetchDataFromApi('login', {
                email,
                password
            }, 'POST');

            if (response && response.status === 'success') {
                // Guardar el token de autenticación
                localStorage.setItem('authToken', response.token);
                localStorage.setItem('userName', response.data.name); 

                // Redirigir al usuario al dashboard o a la URL definida en el servidor
                const redirectUrl = response.redirect_url || '/dashboard';
                window.location.href = redirectUrl;
            } else {
                // Mostrar mensaje de error del servidor
                const errorMessage = response?.message || 'Credenciales incorrectas.';
                alert(errorMessage);
            }
        } catch (error) {
            // Manejar errores inesperados
            console.error(error);
            alert('Error inesperado. Por favor, inténtelo más tarde.');
        }
    });
</script>
@endpush