<?php
$menu = "verificacion";
require 'includes/header.php';
// foreach ($permisos as $value) {
//     if ($value["permiso"] == "Ordenes" && $value["valor"] == "false") {
//         echo "<script>window.location.href = '" . $dashboard . "'</script>";
//     }
// }
?>
<title>Verificación de empresas</title>
<div class="card shadow-none h-100">
    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Empresas</h5>
            </div>
            <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                <div class="d-none" id="table-number-pagination-actions">
                    <div class="d-flex">
                        <select class="form-select form-select-sm" aria-label="Bulk actions" id="orden_estado">

                        </select>
                        <button class="btn btn-falcon-default btn-sm ms-2" type="button" onclick="updateEstadoBulk()">Aplicar</button>
                    </div>
                </div>
                <div id="table-number-pagination-replace-element">
                    <!-- <a class="btn btn-falcon-default btn-sm" type="button" href="crear_orden.php">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a> -->
                    <!-- <button class="btn btn-falcon-default btn-sm mx-2" type="button">
                                <span class="fas fa-filter" data-fa-transform="shrink-3 down-2"></span>
                                <span class="d-none d-sm-inline-block ms-1">Filter</span>
                            </button>
                            <button class="btn btn-falcon-default btn-sm" type="button">
                                <span class="fas fa-external-link-alt" data-fa-transform="shrink-3 down-2"></span>
                                <span class="d-none d-sm-inline-block ms-1">Export</span>
                            </button> -->
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="falcon-data-table">
            <table class="table table-sm mb-0 data-table fs-10" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 sort pe-1 align-middle">Empresa</th>
                        <th class="text-900 sort pe-1 align-middle">Correo</th>
                        <th class="text-900 sort pe-1 align-middle">Dirección</th>
                        <th class="text-900 sort pe-1 align-middle">Cédula/RUC</th>
                        <th class="text-900 sort pe-1 align-middle">Estado</th>
                        <th class="text-900 sort pe-1 align-middle text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody class="" id="tabla_empresas_lista">

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Rechazar / Quitar verificación -->
<div class="modal fade" id="modalRechazoVerificacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quitar verificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="small text-muted mb-2">
                    Indica el motivo por el cual se le quita la verificación a la empresa:
                </div>
                <textarea class="form-control" id="motivo_rechazo" rows="4" placeholder="Escribe el motivo..."></textarea>

                <input type="hidden" id="rechazo_id_verificacion">
                <input type="hidden" id="rechazo_estado_actual">
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm" id="btnConfirmarRechazo">
                    Rechazar
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Conexión API js -->
<script src="js/verificacion_empresas.js?v1.0.0.0.0.8"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?> 