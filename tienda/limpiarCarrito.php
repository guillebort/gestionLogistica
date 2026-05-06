<!DOCTYPE html>
<html>
<head>
    <title>Cerrando sesión...</title>
</head>
<body>
    <script>
        // 1. Bomba nuclear a la memoria del navegador (donde se guarda el carrito)
        localStorage.clear();
        sessionStorage.clear();
        
        // 2. Redirección automática a la tienda (reemplaza la página para que no puedan volver atrás)
        window.location.replace("productos.php");
    </script>
</body>
</html>