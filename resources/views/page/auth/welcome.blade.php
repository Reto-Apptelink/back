@extends('template.auth.app')
@section('header_title') {{__('Login')}} @endsection

@section('content-auth')

<div class="login-container">
    <div class="row">
        <!-- Formulario de Login -->
        <div class="col-md-6 my-auto login-form">

            <h3 class="mb-4">Bienvenido de nuevo</h3>
            <form>
                <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Correo electrónico" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Contraseña" required>
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
@endpush