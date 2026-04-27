<?php
$menu = "reportes";
$sub_menu = "reportes";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Ordenes" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
if (!empty($es_onemuv) || !empty($es_basicmuv)) {
    echo "<script>window.location.href = '" . $dashboard . "'</script>";
}
?>
<title>Reportes</title>
<input type="hidden" id="id_empresa_reportes" value="<?php echo $id_empresa; ?>">
<div class="card shadow-none h-100">
    <div class="card-header">
        <div class="row flex-between-center g-2">
            <div class="col-auto">
                <h5 class="mb-0">Reportes de Interacciones</h5>
                <small class="text-muted">Resumen de actividad de tus productos</small>
            </div>
            <div class="col-auto ms-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Tipo</span>
                    <select class="form-select" id="repTipo">
                        <option value="todos">Todos</option>
                        <option value="producto">Producto</option>
                        <option value="servicio">Servicio</option>
                        <option value="vehiculo">Vehículo</option>
                        <option value="evento">Evento</option>
                        <option value="empresa">Empresa</option>
                    </select>
                    <span class="input-group-text">Desde</span>
                    <input type="date" class="form-control" id="repDesde">
                    <span class="input-group-text">Hasta</span>
                    <input type="date" class="form-control" id="repHasta">
                    <button class="btn btn-falcon-primary" type="button" id="btnRefrescarReportes">
                        <span class="fas fa-sync-alt me-1"></span>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Interacciones</div>
                        <div class="fs-6 fw-bold" id="kpiTotalInteracciones">0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Sesiones Únicas</div>
                        <div class="fs-6 fw-bold" id="kpiSesiones">0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Productos Impactados</div>
                        <div class="fs-6 fw-bold" id="kpiProductos">0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Eventos Registrados</div>
                        <div class="fs-6 fw-bold" id="kpiEventos">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Interacciones por tipo</h6>
                    </div>
                    <div class="card-body">
                        <div id="chartTipoEvento" style="height:320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Top productos por interacción</h6>
                    </div>
                    <div class="card-body">
                        <div id="chartTopProductos" style="height:320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Detalle de interacciones</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 data-table fs-10" id="tablaInteracciones">
                        <thead class="bg-200">
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Tipo evento</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyInteracciones"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require 'includes/footer.php';
?>
<script src="js/reportes.js?v1.0.0.0.0.0.1"></script>
