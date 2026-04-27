<?php
$menu = "sucursales";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Empresas" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>

<style>
    .sucursales-page-card {
        border: 1px solid var(--card-border);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .sucursales-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .sucursales-toolbar-copy h5 {
        margin-bottom: .2rem;
    }

    .sucursales-toolbar-copy p {
        margin: 0;
        color: var(--text-muted);
        font-size: .9rem;
    }

    .sucursales-table-wrap {
        padding: 1rem 1rem .35rem;
    }

    .sucursales-table-wrap .dataTables_wrapper .dataTables_filter input {
        min-width: 260px;
        border-radius: 10px;
    }

    .sucursales-table-wrap .dataTables_wrapper .dataTables_filter,
    .sucursales-table-wrap .dataTables_wrapper .dataTables_info,
    .sucursales-table-wrap .dataTables_wrapper .dataTables_paginate {
        padding-inline: .25rem;
    }

    .sucursales-table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .sucursales-table thead th {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        border-bottom-width: 1px;
    }

    .sucursales-table tbody td {
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        vertical-align: middle;
        border-color: rgba(148, 163, 184, .18);
    }

    .sucursales-cell-name {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 220px;
    }

    .sucursales-avatar {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 104, 111, .12);
        color: var(--fmv-green);
        font-weight: 800;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .sucursales-name-title {
        font-size: .92rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.2;
    }

    .sucursales-name-meta {
        color: var(--text-muted);
        font-size: .78rem;
        margin-top: .15rem;
    }

    .sucursales-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .38rem .65rem;
        border-radius: 999px;
        background: rgba(0, 104, 111, .09);
        color: var(--fmv-green-dark);
        font-weight: 700;
        font-size: .76rem;
        line-height: 1;
    }

    .sucursales-address {
        color: var(--text-muted);
        font-size: .84rem;
        line-height: 1.5;
        min-width: 220px;
        max-width: 360px;
    }

    .sucursales-actions {
        display: flex;
        justify-content: flex-end;
        gap: .45rem;
        min-width: 96px;
    }

    .sucursales-action-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .sucursales-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--text-muted);
    }

    #map_new {
        flex-grow: 1;
        /* Permitir que el mapa ocupe el espacio disponible */
        width: 100%;
        /* Ancho completo */
        min-height: 300px;
        /* Altura mínima */
    }

    #mapaEntrega {
        width: 100%;
        height: 300px;
        /* o 55vh, lo que prefieras */
        border-radius: 8px;
    }


    .map-wrapper {
        position: relative;
    }

    /* Ubica el input arriba a la derecha del mapa */
    .map-search {
        position: absolute;
        top: 0px;
        right: 110px;
        /* <- antes estaba left */
        left: auto;
        /* <- importante para soltar el anclaje izquierdo */
        z-index: 2000;
    }

    /* Para que el autocomplete siempre quede visible */
    .pac-container {
        z-index: 20000 !important;
    }

    /* (Opcional) en pantallas pequeñas que no quede cortado */
    @media (max-width: 576px) {
        .sucursales-table-wrap {
            padding-inline: .75rem;
        }

        .sucursales-table-wrap .dataTables_wrapper .dataTables_filter input {
            min-width: 100%;
        }

        .map-search {
            left: 8px;
            right: 8px;
        }

        /* se centra con margen a ambos lados */
        #buscarDireccion {
            width: 100%;
        }
    }
</style>
<title>Sucursales</title>
<div class="card mb-1 sucursales-page-card">

    <div class="card-header border-bottom">
        <div class="sucursales-toolbar">
            <div class="sucursales-toolbar-copy">
                <h5 class="fs-9 text-nowrap">Sucursales</h5>
                <p>Visualiza y administra sucursales con una tabla más clara y acciones rápidas.</p>
            </div>
            <div id="table-customers-replace-element">
                <button onclick="addSucursal()" class="btn btn-falcon-default btn-sm" type="button">
                    <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                    <span class="d-none d-sm-inline-block ms-1">Crear sucursal</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="sucursales-table-wrap">
            <table class="table table-sm align-middle sucursales-table" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 sort pe-1 align-middle ">Nombre</th>
                        <th class="text-900 sort pe-1 align-middle ">Dirección</th>
                        <th class="text-900 sort pe-1 align-middle ">Empresa</th>
                        <th class="text-900 sort pe-1 align-middle ">Fecha de creación</th>
                        <th class="text-900 align-middle text-end white-space-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody id="lista_sucursales">
                    <tr>
                        <td colspan="5" class="sucursales-empty-state">Cargando sucursales...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Conexión API js -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&loading=async&callback=onMapsReady" async defer></script>
<script src="js/sucursales.js?v1.0.0.0.0.8"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
