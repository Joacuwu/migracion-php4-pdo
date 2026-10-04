<?php
function layout_inicio($titulo)
{
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($titulo); ?> · <?php echo e(APP_NOMBRE); ?></title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-person-vcard"></i> <?php echo e(APP_NOMBRE); ?></a>
        <span class="badge text-bg-success">Versión migrada · PDO</span>
    </div>
</nav>
<main class="container pb-5">
    <?php echo mostrar_flash(); ?>
<?php
}

function layout_fin()
{
    ?>
</main>
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
<?php
}
