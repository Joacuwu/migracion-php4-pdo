<?php
require_once 'includes/empleados.php';
require_once 'includes/layout.php';

$id = entero(isset($_POST['id']) ? $_POST['id'] : get('id'));
$errores = array();

try {
    $actual = $id > 0 ? obtener_empleado($id) : null;
    if ($id > 0 && $actual === null) {
        flash('warning', 'El empleado no existe.');
        redirigir('index.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verificar_csrf();
        list($datos, $errores) = validar_empleado($_POST, $id);
        if (!$errores) {
            guardar_empleado($datos, $id);
            flash('success', $id > 0 ? 'Cambios guardados.' : 'Empleado dado de alta.');
            redirigir('index.php');
        }
    } else {
        $datos = $actual ? $actual : array('legajo' => '', 'apellido' => '', 'nombre' => '', 'area' => '', 'ingreso' => '', 'sueldo' => '');
    }
} catch (PDOException $ex) {
    error_interno($ex);
}

function campo_clase($errores, $campo)
{
    return isset($errores[$campo]) ? ' is-invalid' : '';
}

function campo_error($errores, $campo)
{
    return isset($errores[$campo]) ? '<div class="invalid-feedback">' . e($errores[$campo]) . '</div>' : '';
}

layout_inicio($id > 0 ? 'Editar empleado' : 'Nuevo empleado');
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Empleados</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $id > 0 ? 'Editar' : 'Nuevo'; ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width: 640px">
    <div class="card-body">
        <h1 class="h4 mb-3"><?php echo $id > 0 ? 'Editar empleado' : 'Nuevo empleado'; ?></h1>
        <?php if ($errores): ?>
            <div class="alert alert-danger">Revisá los campos marcados.</div>
        <?php endif; ?>

        <form method="post" novalidate>
            <?php echo csrf_campo(); ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="row g-3">
                <div class="col-sm-4">
                    <label class="form-label" for="legajo">Legajo</label>
                    <input class="form-control<?php echo campo_clase($errores, 'legajo'); ?>" id="legajo" name="legajo"
                           maxlength="6" placeholder="S-0012" value="<?php echo e($datos['legajo']); ?>" required>
                    <?php echo campo_error($errores, 'legajo'); ?>
                </div>
                <div class="col-sm-8">
                    <label class="form-label" for="area">Área</label>
                    <select class="form-select<?php echo campo_clase($errores, 'area'); ?>" id="area" name="area" required>
                        <option value="">Elegí un área</option>
                        <?php foreach (areas_validas() as $a): ?>
                            <option value="<?php echo e($a); ?>"<?php echo $a === $datos['area'] ? ' selected' : ''; ?>><?php echo e($a); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo campo_error($errores, 'area'); ?>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="apellido">Apellido</label>
                    <input class="form-control<?php echo campo_clase($errores, 'apellido'); ?>" id="apellido" name="apellido"
                           maxlength="50" value="<?php echo e($datos['apellido']); ?>" required>
                    <?php echo campo_error($errores, 'apellido'); ?>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control<?php echo campo_clase($errores, 'nombre'); ?>" id="nombre" name="nombre"
                           maxlength="50" value="<?php echo e($datos['nombre']); ?>" required>
                    <?php echo campo_error($errores, 'nombre'); ?>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="ingreso">Fecha de ingreso</label>
                    <input type="date" class="form-control<?php echo campo_clase($errores, 'ingreso'); ?>" id="ingreso" name="ingreso"
                           max="<?php echo date('Y-m-d'); ?>" value="<?php echo e($datos['ingreso']); ?>" required>
                    <?php echo campo_error($errores, 'ingreso'); ?>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="sueldo">Sueldo bruto</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text">$</span>
                        <input class="form-control<?php echo campo_clase($errores, 'sueldo'); ?>" id="sueldo" name="sueldo"
                               inputmode="decimal" placeholder="850000,00" value="<?php echo e($datos['sueldo']); ?>" required>
                        <?php echo campo_error($errores, 'sueldo'); ?>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Guardar</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php layout_fin(); ?>
