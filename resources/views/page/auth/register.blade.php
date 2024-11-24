@extends('template.auth.app')
@section('header_title') {{__('Register')}} @endsection

@section('content-auth')

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
        <div class="col-md-6 register-banner">
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
        try {
            const userData = {
                name,
                email,
                password
            };

            // Enviar solicitud POST al servidor
            const response = await fetchDataFromApi('register', userData, 'POST');

            // Validar respuesta del servidor
            if (response && response.status === 'success') {
                alert('Usuario registrado exitosamente.');
                return true; // Indica éxito
            } else {
                alert(response?.message || 'Error al registrar el usuario.');
                return false; // Indica fallo
            }
        } catch (error) {
            console.error('Error al registrar el usuario:', error);
            alert('Ocurrió un error al procesar la solicitud. Intente más tarde.');
            return false; // Indica fallo
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
            alert('Todos los campos son obligatorios.');
            return;
        }

        if (password !== confirmPassword) {
            alert('Las contraseñas no coinciden.');
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