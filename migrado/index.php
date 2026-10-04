<?php
require_once 'includes/empleados.php';
require_once 'includes/layout.php';

$buscar = get('buscar');
$area   = get('area');
$pagina = max(1, entero(get('pagina')));

try {
    $total   = contar_empleados($buscar, $area);
    $paginas = max(1, (int) ceil($total / POR_PAGINA));
    $pagina  = min($pagina, $paginas);
    $filas   = buscar_empleados($buscar, $area, $pagina);
} catch (PDOException $ex) {
    error_interno($ex);
}

function url_pagina($n, $buscar, $area)
{
    return 'index.php?' . http_build_query(array('buscar' => $buscar, 'area' => $area, 'pagina' => $n));
}

layout_inicio('Empleados');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 m-0">Legajos de empleados</h1>
    <a href="empleado.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo empleado</a>
</div>

<form method="get" class="row g-2 mb-3" role="search">
    <div class="col-12 col-md-6">
        <input type="search" name="buscar" class="form-control" placeholder="Buscar por apellido, nombre o legajo"
               value="<?php echo e($buscar); ?>">
    </div>
    <div class="col-8 col-md-4">
        <select name="area" class="form-select">
            <option value="">Todas las áreas</option>
            <?php foreach (areas_validas() as $a): ?>
                <option value="<?php echo e($a); ?>"<?php echo $a === $area ? ' selected' : ''; ?>><?php echo e($a); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-4 col-md-2 d-grid">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Buscar</button>
    </div>
</form>

<p class="text-body-secondary small"><?php echo $total; ?> empleado<?php echo $total === 1 ? '' : 's'; ?></p>

<?php if (!$filas): ?>
    <div class="alert alert-light border">No se encontraron empleados con ese criterio.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
        <tr>
            <th>Legajo</th><th>Apellido y nombre</th><th class="d-none d-md-table-cell">Área</th>
            <th class="d-none d-sm-table-cell">Ingreso</th><th class="text-end">Sueldo</th><th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><code><?php echo e($f['legajo']); ?></code></td>
                <td><?php echo e($f['apellido'] . ', ' . $f['nombre']); ?></td>
                <td class="d-none d-md-table-cell"><?php echo e($f['area']); ?></td>
                <td class="d-none d-sm-table-cell"><?php echo e(date('d/m/Y', strtotime($f['ingreso']))); ?></td>
                <td class="text-end">$ <?php echo e(pesos($f['sueldo'])); ?></td>
                <td class="text-end text-nowrap">
                    <a href="empleado.php?id=<?php echo (int) $f['id']; ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger js-borrar" title="Borrar"
                            data-id="<?php echo (int) $f['id']; ?>"
                            data-nombre="<?php echo e($f['apellido'] . ', ' . $f['nombre']); ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($paginas > 1): ?>
<nav aria-label="Páginas">
    <ul class="pagination justify-content-center">
        <?php for ($n = 1; $n <= $paginas; $n++): ?>
            <li class="page-item<?php echo $n === $pagina ? ' active' : ''; ?>">
                <a class="page-link" href="<?php echo e(url_pagina($n, $buscar, $area)); ?>"><?php echo $n; ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>

<!-- [P8] La baja se confirma en un modal y viaja por POST con token CSRF. -->
<div class="modal fade" id="modalBorrar" tabindex="-1" aria-labelledby="modalBorrarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="eliminar.php" class="modal-content">
            <?php echo csrf_campo(); ?>
            <input type="hidden" name="id" id="borrarId">
            <div class="modal-header">
                <h2 class="modal-title h5" id="modalBorrarTitulo">Borrar empleado</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">¿Seguro que querés borrar a <strong id="borrarNombre"></strong>? No se puede deshacer.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger">Borrar</button>
            </div>
        </form>
    </div>
</div>
<?php layout_fin(); ?>
