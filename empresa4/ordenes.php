<?php
$menu = "ordenes";
$sub_menu = "ordenes";
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
<title>Órdenes</title>
<div class="card shadow-none h-100">
    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Órdenes Recientes</h5>
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
                    
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="falcon-data-table">
            <table class="table table-sm mb-0 data-table fs-10" id="my_table" style="min-height: 150px;">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 no-sort white-space-nowrap">
                            <div class="form-check mb-0 d-flex align-items-center">
                                <input class="form-check-input" id="checkbox-bulk-table-item-select" type="checkbox" />
                            </div>
                        </th>
                        <th class="text-900 sort pe-1" data-sort="order">Orden</th>
                        <th class="text-900 sort pe-1" data-sort="date">Fecha</th>
                        <th class="text-900 sort pe-1" data-sort="address" style="min-width: 12.5rem;">Cliente</th>
                        <th class="text-900 sort pe-1" data-sort="empresa">Empresa</th>
                        <th class="text-900 sort pe-1" data-sort="amount">Valor</th>
                        <th class="text-900 sort pe-1" data-sort="accion">Acción</th>
                        <th class="no-sort">Opciones</th>
                    </tr>
                </thead>
                <tbody class="" id="lista_ordenes">

                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="show_notes_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Actividad Reciente</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="card h-100">
                <div class="card-body scrollbar recent-activity-body-height ps-2" id="notesLog">
                </div>
                <div class="card-footer">
                    <div class="border rounded">
                        <form action="#" class="comment-area-box">
                            <textarea rows="2" class="form-control border-0 resize-none" id="comment" placeholder="Tu comentario..."></textarea>
                            <div class="p-2 bg-light text-end" id="div_submit">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Conexión API js -->
<script src="js/ordenes.js?v1.0.0.0.0.3"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
