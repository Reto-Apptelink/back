<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApptelinkReto - @yield('header_title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/app.css')}}">
    @stack('styles_app')

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="https://placeholder.com/wp-content/uploads/2018/10/placeholder.com-logo1.png" alt="Logo de la empresa">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="clientesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Clientes
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="clientesDropdown">
                            <li><a class="dropdown-item" href="#">Lista</a></li>
                            <li><a class="dropdown-item" href="#">Crear</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="productosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Productos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="productosDropdown">
                            <li><a class="dropdown-item" href="{{route('app.product.index')}}">Lista</a></li>
                            <li><a class="dropdown-item" href="{{route('app.product.create.form')}}">Crear</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="pedidosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Pedidos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="pedidosDropdown">
                            <li><a class="dropdown-item" href="{{route('app.order.index')}}">Lista</a></li>
                            <li><a class="dropdown-item" href="{{route('app.order.create.form')}}">Crear</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="facturasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Facturas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="facturasDropdown">
                            <li><a class="dropdown-item" href="#">Lista</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="user-info">
                <img src="{{asset('assets/img/team/avatar-rounded.webp')}}" alt="Avatar del usuario" class="user-avatar">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">Perfil</a></li>
                        <li><a class="dropdown-item" id="logout">Cerrar sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        @yield('content-main')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const token = localStorage.getItem('authToken');
        const userName = localStorage.getItem('userName');

        if (!token) {
            // alert('No hay una sesión activa.');
            window.location.href = '/';
        }

        if (userName) {
            document.querySelector('#userDropdown').textContent = userName;
        }

        document.getElementById('logout').addEventListener('click', async function(e) {
            e.preventDefault();

            const token = localStorage.getItem('authToken');
            if (!token) {
                alert('No hay una sesión activa.');
                window.location.href = '/';
                return;
            }

            try {
                const response = await fetch('/api-iims/digitales/v1/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    localStorage.removeItem('authToken');
                    alert('Sesión cerrada con éxito.');
                    window.location.href = '/';
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al cerrar sesión.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error inesperado. Por favor, inténtelo más tarde.');
            }
        });
    </script>
    @stack('scripts_app')
</body>

</html>