<?php
// admin.php
session_start();

// REQUISITO: Protección de rutas
// Si no hay sesión iniciada O el usuario NO es admin, lo expulsamos al inicio
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <div class="container py-5 text-center">
        <h1 class="text-warning">Panel de Control: Nivel Administrador</h1>
        <p class="lead">Bienvenido, <?php echo $_SESSION['username']; ?>. Tienes privilegios de sistema.</p>
        
        <div class="alert alert-success mt-4">
            Si estás viendo esto, la protección de rutas funcionó correctamente.
        </div>

        <a href="index.html" class="btn btn-outline-light mt-3">Volver a la Tienda</a>
    </div>
</body>
</html>