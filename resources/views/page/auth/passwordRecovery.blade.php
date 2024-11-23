@extends('template.auth.app')
@section('header_title') {{__('Password Recovery')}} @endsection

@section('content-auth')
<div class="recover-container">
    <div class="row">
        <!-- Formulario de Recuperación de Contraseña -->
        <div class="col-md-6 recover-form">
            <h3 class="mb-4">Recuperar Contraseña</h3>
            <p class="mb-4">Ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.</p>
            <form>
                <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Correo electrónico" required>
                </div>
                <button type="submit" class="btn btn-recover">Enviar Instrucciones</button>
            </form>
        </div>

        <!-- Banner Informativo -->
        <div class="col-md-6 recover-banner">
            <div class="pattern-bg"></div>
            <h2 class="mb-4">Recupera el acceso a tu cuenta</h2>
            <p class="mb-4">No te preocupes, te ayudaremos a recuperar el acceso a tu Sistema de Gestión de Inventarios Inteligente</p>

            <div class="info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <div>
                        <h5>Revisa tu correo</h5>
                        <p>Te enviaremos un enlace para restablecer tu contraseña</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-lock info-icon"></i>
                    <div>
                        <h5>Crea una nueva contraseña</h5>
                        <p>Elige una contraseña segura para proteger tu cuenta</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-sign-in-alt info-icon"></i>
                    <div>
                        <h5>Vuelve a iniciar sesión</h5>
                        <p>Accede a tu cuenta con tu nueva contraseña</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_app')
@endpush