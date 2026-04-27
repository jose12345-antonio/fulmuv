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
<style>
    .ordenes-page-card {
        border: 1px solid var(--card-border);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .ordenes-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .ordenes-toolbar-copy h5 {
        margin-bottom: .2rem;
    }

    .ordenes-toolbar-copy p {
        margin: 0;
        color: var(--text-muted);
        font-size: .9rem;
    }

    .ordenes-table-wrap {
        padding: 1rem 1rem .35rem;
    }

    .ordenes-table-wrap .dataTables_wrapper .dataTables_filter input {
        min-width: 260px;
        border-radius: 10px;
    }

    .ordenes-table-wrap .dataTables_wrapper .dataTables_filter,
    .ordenes-table-wrap .dataTables_wrapper .dataTables_info,
    .ordenes-table-wrap .dataTables_wrapper .dataTables_paginate {
        padding-inline: .25rem;
    }

    .ordenes-table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .ordenes-table thead th {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        border-bottom-width: 1px;
    }

    .ordenes-table tbody td {
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        vertical-align: middle;
        border-color: rgba(148, 163, 184, .18);
    }

    .ordenes-id-link {
        font-weight: 800;
        color: var(--fmv-green-dark);
    }

    .ordenes-client-name {
        font-size: .92rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.2;
    }

    .ordenes-client-meta,
    .ordenes-company-meta {
        color: var(--text-muted);
        font-size: .78rem;
        line-height: 1.45;
    }

    .ordenes-company-name {
        font-weight: 700;
        color: var(--text-main);
    }

    .ordenes-total {
        font-size: .92rem;
        font-weight: 800;
        color: #b45309;
    }

    .ordenes-status-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .45rem .7rem;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }

    .ordenes-status-badge.is-creada {
        background: rgba(100, 116, 139, .12);
        color: #475569;
    }

    .ordenes-status-badge.is-aprobada {
        background: rgba(245, 158, 11, .15);
        color: #b45309;
    }

    .ordenes-status-badge.is-procesada {
        background: rgba(14, 116, 144, .14);
        color: #0f766e;
    }

    .ordenes-status-badge.is-enviada {
        background: rgba(37, 99, 235, .14);
        color: #1d4ed8;
    }

    .ordenes-status-badge.is-completada {
        background: rgba(22, 163, 74, .14);
        color: #15803d;
    }

    .ordenes-status-badge.is-eliminada {
        background: rgba(220, 38, 38, .14);
        color: #b91c1c;
    }

    .ordenes-action-cluster,
    .ordenes-icon-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: .45rem;
        flex-wrap: nowrap;
    }

    .ordenes-icon-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .ordenes-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--text-muted);
    }

    @media (max-width: 767px) {
        .ordenes-table-wrap {
            padding-inline: .75rem;
        }

        .ordenes-table-wrap .dataTables_wrapper .dataTables_filter input {
            min-width: 100%;
        }
    }
</style>
<title>Órdenes</title>
<div class="card shadow-none h-100 ordenes-page-card">
    <div class="card-header border-bottom">
        <div class="ordenes-toolbar">
            <div class="ordenes-toolbar-copy">
                <h5 class="fs-9 mb-0 text-nowrap">Órdenes Recientes</h5>
                <p>Consulta el estado de cada orden y accede a sus acciones principales más rápido.</p>
            </div>
            <div class="ms-auto text-end">
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
        <div class="ordenes-table-wrap">
            <table class="table table-sm mb-0 ordenes-table" id="my_table" style="min-height: 150px;">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 no-sort text-end">Opciones</th>
                        <th class="text-900 sort pe-1" data-sort="date">Fecha</th>
                        <th class="text-900 sort pe-1" data-sort="address" style="min-width: 12.5rem;">Cliente</th>
                        <th class="text-900 sort pe-1" data-sort="empresa">Empresa</th>
                        <th class="text-900 sort pe-1" data-sort="amount">Valor</th>
                        <th class="text-900 sort pe-1" data-sort="accion">Estado / Acción</th>
                    </tr>
                </thead>
                <tbody class="" id="lista_ordenes">
                    <tr>
                        <td colspan="6" class="ordenes-empty-state">Cargando órdenes...</td>
                    </tr>
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
<script src="js/ordenes.js?v1.0.0.0.0.7"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
