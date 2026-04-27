<?php
$menu = "ordenes";
$sub_menu = "ordenes_iso";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Ordenes" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
    if ($value["permiso"] == "Ordenes" && $value["valor"] == "true") {
        if ($value["levels"] != "Fulmuv") {
            echo "<script>window.location.href = '" . $dashboard . "'</script>";
        }
        echo '<input type="hidden" id="levels" value="' . $value["levels"] . '">';
    }
}
?>
<title>Ordenes</title>
<style>
    /* Alineaciones coherentes */
    #my_table td.text-end,
    #my_table th.text-end {
        text-align: right;
    }

    #my_table td.text-center,
    #my_table th.text-center {
        text-align: center;
    }

    /* Evitar saltos raros en badges/botones */
    #my_table td .badge,
    #my_table td .btn {
        white-space: nowrap;
    }
</style>

<div class="card shadow-none h-100">
    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Ordenes Recientes</h5>
            </div>
            <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                <div id="table-number-pagination-replace-element">
                    <!-- <a class="btn btn-falcon-default btn-sm" type="button" href="crear_orden.php">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a> -->
                </div>
                <div class="d-none" id="table-number-pagination-actions">
                    <div class="d-flex">
                        <select class="form-select form-select-sm" aria-label="Bulk actions" id="orden_estado">
                            <option value="enviada">Enviar</option>
                            <option value="completada">Completada</option>
                            <!-- <option value="eliminada">Eliminar</option> -->
                        </select>
                        <button class="btn btn-falcon-default btn-sm ms-2" type="button" onclick="updateEstadoBulk()">Aplicar</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="falcon-data-table">
            <table class="table table-sm mb-0 data-table fs-10" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 no-sort white-space-nowrap">
                            <div class="form-check mb-0 d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" id="checkbox-bulk-table-item-select" />
                            </div>
                        </th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap" data-sort="order">Orden</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap pe-7" data-sort="date">Fecha</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap" data-sort="address" style="min-width: 12.5rem;">Clientes</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center" data-sort="status">Estado</th>

                        <!-- NUEVA COLUMNA -->
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center" data-sort="venta" style="min-width: 12rem;">Venta</th>

                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-end" data-sort="amount">Valor</th>
                        <th class="no-sort"></th>
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

<!-- Modal: Visualizar Pago -->
<div class="modal fade" id="modalVerPago" tabindex="-1" aria-labelledby="modalVerPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalVerPagoLabel">Pago de Envío — Orden <span id="vp-numero"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="vp-id-orden-iso">
                <div class="table-responsive mb-3">
                    <table class="table table-sm" id="vp-tabla-productos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cant.</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <!-- Bloque productos -->
                            <tr class="table-light">
                                <th colspan="4" class="text-start">Productos</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal</th>
                                <th class="text-end" id="vp-subtotal">$0.00</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">IVA (15%)</th>
                                <th class="text-end" id="vp-iva">$0.00</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Total productos</th>
                                <th class="text-end fw-bold" id="vp-total-productos">$0.00</th>
                            </tr>

                            <!-- Bloque envío -->
                            <tr class="table-light">
                                <th colspan="4" class="text-start">Envío</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Peso</th>
                                <th class="text-end" id="vp-peso">$0.00 kg</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Valor envío</th>
                                <th class="text-end fw-bold" id="vp-valor-envio">$0.00</th>
                            </tr>

                            <!-- Total general -->
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Total a pagar</th>
                                <th class="text-end fw-bold" id="vp-total-general">$0.00</th>
                            </tr>
                        </tfoot>

                    </table>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <img id="vp-img" src="" alt="Comprobante" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #eee">
                    <a id="vp-link" href="#" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-1"></i> Ver imagen en nueva pestaña
                    </a>
                </div>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="vp-confirmar">
                    ¿Quieres confirmar el envío? <strong>Confirmar</strong>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Guía -->
<div class="modal fade" id="modalVerGuia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guía Servientrega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" style="height:80vh;">
                <iframe id="vg-frame" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="vg-open" href="#" target="_blank" class="btn btn-outline-primary">Abrir en otra pestaña</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Conexión API js -->
<script src="js/ordenes_iso.js?v1.0.0.0.0.0.0.0.18"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>