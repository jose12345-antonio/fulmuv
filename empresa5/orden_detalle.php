<?php
require 'includes/header.php';
$id_orden = $_GET["id_orden"];
$input_id_orden = "<input type='hidden' id='id_orden' value='$id_orden' >";
echo $input_id_orden;
?>
<style>
    .orden-detalle-hero,
    .orden-detalle-card {
        border: 1px solid var(--card-border);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .orden-detalle-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .orden-detalle-meta {
        color: var(--text-muted);
        font-size: .88rem;
        line-height: 1.55;
    }

    .orden-detalle-table-wrap {
        padding: 1rem 1rem .35rem;
    }

    .orden-detalle-table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .orden-detalle-table thead th {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
    }

    .orden-detalle-table tbody td {
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        vertical-align: middle;
        border-color: rgba(148, 163, 184, .18);
    }

    .orden-detalle-product {
        display: flex;
        align-items: center;
        gap: .85rem;
        min-width: 240px;
    }

    .orden-detalle-product img {
        width: 62px;
        height: 62px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, .18);
        background: #fff;
    }

    .orden-detalle-product-name {
        font-size: .92rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.25;
    }

    .orden-detalle-number {
        font-weight: 700;
        color: var(--text-main);
    }

    .orden-detalle-total-box table th,
    .orden-detalle-total-box table td {
        font-size: .88rem;
    }

    .orden-detalle-empty {
        padding: 2.25rem 1rem;
        text-align: center;
        color: var(--text-muted);
    }

    @media (max-width: 767px) {
        .orden-detalle-table-wrap {
            padding-inline: .75rem;
        }
    }
</style>
<title>Órden Detalle</title>
<div class="card mb-3 orden-detalle-hero">
    <div class="bg-holder d-none d-lg-block bg-card" style="background-image:url(../theme/public/assets/img/icons/spot-illustrations/corner-4.png);opacity: 0.7;">
    </div>
    <!--/.bg-holder-->

    <div class="card-body position-relative">
        <div class="row g-2 align-items-sm-center">
            <div class="col-auto">
                <h5 class="mb-2 orden-detalle-title" id="numero_orden">Detalle de órden: #</h5>
                <p class="mb-0 orden-detalle-meta" id="fecha"></p>
                <p class="mb-0 orden-detalle-meta" id="nombre_empresa"> <strong>Empresa: </strong> </p>
                <p class="mb-0 orden-detalle-meta" id="nombre_sucursal"> <strong>Sucursal: </strong> </p>
                <p class="mb-2 orden-detalle-meta" id="nombre_area"> <strong>Área: </strong> </p>
                <div id="estado">

                </div>
                <div class="alert alert-success mt-3">
                    <h6 class="mb-0 text-800" id="mensaje"></h6>
                </div>
            </div>
            <div class="col">
                <div class="row align-items-center">
                    <!-- <div class="col-3 col-sm-auto align-items-center">
                        <label for="form-label">Tipo de Trayecto</label>
                        <select id="tipo_trayecto" class="form-select" onchange="llenarTrayectos()">
                            <option value="documentos">DOCUMENTOS</option>
                            <option value="mercancia_premier">MERCANCIA PREMIER (CARGA LIVIANA)</option>
                        </select>
                    </div>
                    <div class="col-3 col-sm-auto align-items-center">
                        <label for="form-label">Trayecto</label>
                        <select id="trayecto" class="form-select">
                            <option value="">Seleccione trayecto</option>
                        </select>
                    </div> -->
                    <div class="col-12 col-sm-auto ms-auto">
                        <!-- <button class="btn btn-falcon-primary" type="button" onclick="verTrayecto()">Trayecto</button> -->
                        <button class="btn btn-falcon-primary" id="btn_confirmar_peso" type="button" onclick="confirmarPeso()"><span class="fas fa-weight-hanging me-1"></span>Confirmar Peso</button>
                        <!-- <button class="btn btn-falcon-primary" type="button" onclick="confirmarEnvio()">Confirmar Envío</button> -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="alert alert-warning d-none" id="alert_retiro_empresa"></div>
<div class="card mb-3 orden-detalle-card">
    <div class="card-header border-bottom">
        <div class="row flex-between-center">
            <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                <div class="d-none" id="table-number-pagination-actions">
                    <div class="d-flex">
                        <select class="form-select form-select-sm" aria-label="Bulk actions" id="orden_estado">
                            <option value="vender">Confirmar venta</option>
                        </select>
                        <button class="btn btn-falcon-default btn-sm ms-2" type="button" onclick="updateEstadoBulk()"><span class="fas fa-check me-1"></span>Aplicar</button>
                    </div>
                </div>
                <div id="table-number-pagination-replace-element">

                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="orden-detalle-table-wrap">
            <table class="table table-sm orden-detalle-table" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 no-sort white-space-nowrap">
                            <div class="form-check mb-0 d-flex align-items-center">
                                <input class="form-check-input" id="checkbox-bulk-table-item-select" type="checkbox" />
                            </div>
                        </th>
                        <th class="text-900 border-0">Producto</th>
                        <th class="text-900 border-0">Peso</th>
                        <th class="text-900 border-0 text-center">Cantidad</th>
                        <th class="text-900 border-0 text-center">Peso total (kg)</th>
                        <th class="text-900 border-0 text-end carrito">Valor</th>
                    </tr>
                </thead>

                <tbody id="lista_productos">
                    <tr>
                        <td colspan="6" class="orden-detalle-empty">Cargando productos de la orden...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row g-0 justify-content-end carrito">
            <div class="col-auto orden-detalle-total-box pe-3 pb-3">
                <table class="table table-sm table-borderless fs-10 text-end">
                    <tr>
                        <th class="text-900">Subtotal:</th>
                        <td class="fw-semi-bold" id="subtotal">$0.00 </td>
                    </tr>
                    <tr>
                        <th class="text-900">IVA 15%:</th>
                        <td class="fw-semi-bold" id="iva">$0.00</td>
                    </tr>
                    <tr class="border-top">
                        <th class="text-900">Total:</th>
                        <td class="fw-semi-bold" id="total">$0.00</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Conexión API js -->
<script src="js/orden_detalle.js?v1.0.0.0.0.0.7"></script> 
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
    