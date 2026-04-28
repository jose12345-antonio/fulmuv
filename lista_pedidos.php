<?php
include 'includes/header.php';
$timer = time();

echo '<input type="hidden" placeholder="" id="id_cliente" class="form-control" value="' . $_SESSION["id_usuario"] . '" />';
?>
<link rel="canonical" href="https://fulmuv.com/lista_pedidos.php">

<style>
    /* ── Sidebar ─────────────────────────────────────────── */
    .dashboard-menu {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,78,96,.12);
        box-shadow: 0 2px 12px rgba(0,78,96,.07);
        overflow: hidden;
    }
    .dashboard-menu .sidebar-title {
        display: flex; align-items: center; gap: 10px;
        padding: 16px 18px;
        font-size: 14px; font-weight: 700; letter-spacing: .3px;
        color: #fff;
        background: linear-gradient(135deg, #016b84, #004E60);
    }
    .dashboard-menu .sidebar-title i { font-size: 16px; color: #FFDC2B; }
    .dashboard-menu ul.nav { padding: 6px 0; }
    .dashboard-menu .nav-item .nav-link {
        display: flex; align-items: center; gap: 10px;
        padding: 11px 18px;
        font-size: 13px; font-weight: 600; color: #4a5568;
        border-left: 3px solid transparent;
        transition: all .15s;
    }
    .dashboard-menu .nav-item .nav-link:hover { background: rgba(0,78,96,.05); color: #004E60; }
    .dashboard-menu .nav-item .nav-link.active {
        background: rgba(0,78,96,.09); color: #004E60;
        border-left-color: #004E60;
    }
    .dashboard-menu .nav-item .nav-link i { font-size: 14px; }

    /* ── Page header ─────────────────────────────────────── */
    .lp-header {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 18px;
        background: #fff;
        border-radius: 14px 14px 0 0;
        border-bottom: 1px solid rgba(0,78,96,.10);
    }
    .lp-header h3 { margin: 0; font-size: 16px; font-weight: 800; color: #004E60; }
    .lp-header .select-wrap { margin-left: auto; min-width: 380px; }

    /* ── Order cards ─────────────────────────────────────── */
    .order-cards { display: flex; flex-direction: column; gap: 16px; }

    .order-card {
        border-radius: 14px; overflow: hidden; background: #fff;
        border: 1px solid rgba(0,78,96,.12);
        box-shadow: 0 2px 14px rgba(0,78,96,.07);
        transition: box-shadow .2s;
    }
    .order-card:hover { box-shadow: 0 4px 22px rgba(0,78,96,.13); }

    .order-card .card-head {
        display: flex; flex-wrap: wrap; gap: 10px 22px;
        padding: 12px 16px;
        background: linear-gradient(135deg, #004E60 0%, #016b84 100%);
        align-items: center;
    }
    .order-card .card-head .kv { display: flex; flex-direction: column; }
    .order-card .card-head .kv .k { font-size: 10px; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .5px; }
    .order-card .card-head .kv .v { font-size: 15px; font-weight: 700; color: #fff; }
    .order-card .card-head .ms-auto { margin-left: auto; }

    .badge-estado {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 20px;
        font-size: 11px; font-weight: 700; letter-spacing: .2px;
    }
    .badge-estado.s-creada    { background: rgba(255,255,255,.18); color: #fff; }
    .badge-estado.s-procesada { background: #FFDC2B; color: #004E60; }
    .badge-estado.s-enviada   { background: #90FFBD; color: #004E60; }
    .badge-estado.s-entregada,.badge-estado.s-aprobada,.badge-estado.s-completada { background: #10b981; color: #fff; }
    .badge-estado.s-cancelada,.badge-estado.s-eliminada { background: #ef4444; color: #fff; }
    .badge-estado.s-pendiente { background: rgba(255,255,255,.18); color: #fff; }
    .badge-estado.s-default   { background: rgba(255,255,255,.15); color: #fff; }

    /* ── Steps ───────────────────────────────────────────── */
    .order-steps {
        display: flex; align-items: flex-start;
        padding: 10px 16px;
        background: rgba(0,78,96,.03);
        border-bottom: 1px solid rgba(0,78,96,.07);
        overflow-x: auto;
    }
    .order-step {
        display: flex; flex-direction: column; align-items: center;
        flex: 1; position: relative; min-width: 60px;
    }
    .order-step::before {
        content: ''; position: absolute;
        top: 9px; left: calc(-50% + 10px); right: calc(50% + 10px);
        height: 2px; background: #e5e7eb;
    }
    .order-step:first-child::before { display: none; }
    .order-step.done::before  { background: #004E60; }
    .order-step.active::before { background: linear-gradient(to right, #004E60, #FF6D01); }
    .step-dot {
        width: 20px; height: 20px; border-radius: 50%;
        border: 2px solid #d1d5db; background: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 9px; color: #fff;
        margin-bottom: 5px; z-index: 1; position: relative;
    }
    .order-step.done  .step-dot { background: #004E60; border-color: #004E60; }
    .order-step.done  .step-dot::after { content: '✓'; }
    .order-step.active .step-dot { background: #FF6D01; border-color: #FF6D01; box-shadow: 0 0 0 3px rgba(255,109,1,.18); }
    .step-label { font-size: 10px; color: #9ca3af; text-align: center; white-space: nowrap; }
    .order-step.done   .step-label { color: #004E60; font-weight: 600; }
    .order-step.active .step-label { color: #FF6D01; font-weight: 700; }

    /* ── Card body ───────────────────────────────────────── */
    .order-card .card-body {
        display: flex; gap: 14px; padding: 14px 16px;
    }
    .order-left {
        flex: 1 1 auto; min-width: 0;
        display: flex; flex-direction: column;
    }

    .product-thumb {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #eee;
    }

    /* ── Select de orden ─────────────────────────────────── */
    .lp-select-wrap .select2-container { width: 100% !important; }
    .lp-select-wrap .select2-selection--single {
        height: 40px !important; border-radius: 10px !important;
        border: 2px solid rgba(0,78,96,.25) !important;
        display: flex !important; align-items: center !important;
        padding: 0 12px !important; background: #fff !important;
    }
    .lp-select-wrap .select2-selection__rendered {
        line-height: 40px !important; font-size: 13px !important;
        font-weight: 600 !important; color: #004E60 !important;
        padding-left: 0 !important;
    }
    .lp-select-wrap .select2-selection__arrow {
        height: 38px !important; right: 8px !important;
    }
    .lp-select-wrap .select2-selection__arrow b {
        border-color: #004E60 transparent transparent !important;
    }
    .select2-dropdown {
        border: 2px solid rgba(0,78,96,.2) !important;
        border-radius: 10px !important; box-shadow: 0 8px 24px rgba(0,78,96,.12) !important;
        overflow: hidden;
    }
    .select2-results__option { font-size: 13px !important; padding: 8px 14px !important; }
    .select2-results__option--highlighted { background: #004E60 !important; color: #fff !important; }
    .select2-search--dropdown input {
        border: 1px solid rgba(0,78,96,.2) !important; border-radius: 6px !important;
        font-size: 13px !important; padding: 6px 10px !important;
    }

    /* ── Product list ────────────────────────────────────── */
    .order-info { min-width: 0; flex: 1; }
    .product-name { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #0f172a; }
    .meta { font-size: 11px; color: #6b7280; margin-top: 2px; }

    .product-item {
        display: flex; gap: 12px; align-items: center;
        padding: 10px 0; border-bottom: 1px solid #f1f5f9;
    }
    .product-item:last-of-type { border-bottom: 0; }
    .product-thumb {
        width: 60px; height: 60px; object-fit: cover;
        border-radius: 10px; border: 1px solid #e5e7eb; flex-shrink: 0;
    }

    /* ── Estado bar (debajo de productos) ────────────────── */
    .estado-bar {
        display: flex; justify-content: flex-end; align-items: center;
        padding: 6px 0 2px;
    }

    /* ── Shipping box ────────────────────────────────────── */
    .shipping-box {
        border-radius: 10px; padding: 10px 12px; margin-top: 8px;
        display: flex; gap: 10px; align-items: flex-start;
        background: #f8fafc; border: 1px solid #e5e7eb;
    }
    .shipping-box .sbox-icon { font-size: 16px; flex-shrink: 0; color: #6b7280; margin-top: 1px; }
    .shipping-box .sbox-content { min-width: 0; }
    .shipping-box .label  { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; font-weight: 600; margin-bottom: 3px; }
    .shipping-box .amount { font-weight: 700; font-size: 17px; color: #111827; }
    .shipping-box .note   { font-size: 11px; color: #6b7280; margin-top: 2px; }
    .shipping-box.success { background: #f0fdf4; border-color: #86efac; }
    .shipping-box.success .sbox-icon { color: #16a34a; }
    .shipping-box.success .amount    { color: #15803d; }
    .shipping-box.warning { background: #fffbeb; border-color: #fcd34d; }
    .shipping-box.warning .sbox-icon { color: #d97706; }
    .shipping-box.warning .amount    { color: #92400e; }
    .shipping-box.info { background: rgba(0,78,96,.05); border-color: rgba(0,78,96,.2); }
    .shipping-box.info .sbox-icon { color: #004E60; }
    .shipping-box.info .amount { color: #004E60; }

    /* ── Card body: columna única ────────────────────────── */
    .order-card .card-body {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
    }
    .order-card .card-body .product-item {
        width: 100%;
    }

    /* ── Barra de acciones ───────────────────────────────── */
    .order-actions-bar {
        display: flex; flex-wrap: wrap; gap: 8px;
        padding: 10px 16px;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
    }
    .order-actions-bar .btn-action {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 8px;
        font-size: 12px; font-weight: 600;
        border: none; cursor: pointer; transition: opacity .15s, transform .1s;
        white-space: nowrap;
    }
    .order-actions-bar .btn-action:hover { opacity: .88; transform: translateY(-1px); }
    .order-actions-bar .btn-action:active { transform: translateY(0); }
    .btn-action-map    { background: rgba(0,78,96,.1); color: #004E60; }
    .btn-action-pay    { background: #FF6D01; color: #fff; }
    .btn-action-detail { background: #004E60; color: #fff; }
    .btn-action-guide  { background: #FFDC2B; color: #004E60; }

    /* ── Footer ──────────────────────────────────────────── */
    .order-footer {
        padding: 7px 16px; border-top: 1px solid #f1f5f9;
        display: flex; justify-content: flex-end; align-items: center;
        background: #f4fafe; gap: 8px;
    }

    .modal { z-index: 1060; }
    .modal-backdrop { z-index: 1055; }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 768px) {
        .dashboard-content.pl-50 { padding-left: 0 !important; }
        .order-card .card-head { flex-direction: column; align-items: flex-start; gap: 6px; }
        .order-card .card-head .ms-auto { margin-left: 0; }
        .lp-header { flex-wrap: wrap; }
        .lp-header .select-wrap { min-width: 100%; margin-left: 0; }
    }
    @media (max-width: 480px) {
        .order-actions-bar .btn-action { flex: 1 1 calc(50% - 8px); justify-content: center; }
        .product-thumb { width: 48px; height: 48px; }
    }

    /* ── Card Dropzone/File ──────────────────────────────── */
    .card-file { border:1px solid #ccc; padding:10px; text-align:center; border-radius:8px; margin-bottom:10px; }
    .card-file i { font-size:2rem; color:#555; }
    .card-file small { display:block; margin-top:5px; word-break:break-word; }
    .dropzone { border:2px dashed #ccc; background:#f5f5f5; padding:40px; border-radius:8px; cursor:pointer; transition:background-color .3s; }
    .dropzone:hover { background-color:#f0f0f0; }
    .dropzone .dz-message { display:flex; flex-direction:column; align-items:center; justify-content:center; color:#666; }
    .dropzone .dz-message i { margin-top:10px; }

    .pago-card {
        margin: 0 auto;
        max-width: 780px;
        border-radius: 20px;
        padding: 28px 28px 24px;
        background: linear-gradient(145deg, #016b84, #004E60, #003a48);
        position: relative;
        color: #ffffff;
    }

    .pago-card h3 {
        font-weight: 700;
        letter-spacing: .5px;
        margin: 0 0 6px 0;
        text-transform: uppercase;
        color: #ffffff;
    }

    .pago-card .sub {
        font-weight: 700;
        letter-spacing: .3px;
        color: #c2f0f6;
    }

    .pago-chip {
        width: 62px;
        height: 44px;
        border-radius: 10px;
        background: linear-gradient(145deg, #e9c55f, #b98d28);
        box-shadow: inset 0 3px 6px rgba(0, 0, 0, .25);
    }

    .pago-item b {
        display: inline-block;
        min-width: 120px;
        color: #ffffff;
    }

    .pago-item {
        color: #e6f9fc;
    }

    .pago-divider {
        border-top: 1px solid rgba(255, 255, 255, .25);
        margin: 10px 0 14px;
    }

    .meta-line {
        font-size: .9rem;
        color: #d0f2f6;
        opacity: .9;
    }

    a {
        color: #c2f0f6;
        text-decoration: underline;
    }

    @media (max-width:576px) {
        .pago-card {
            padding: 20px;
        }

        .pago-item b {
            min-width: 110px;
        }
    }

    /* ===== FIX RESPONSIVE (móvil) ===== */
    @media (max-width: 768px) {

        /* Quita el padding izquierdo grande del dashboard en móvil */
        .dashboard-content.pl-50 {
            padding-left: 0 !important;
        }

        /* Header de la card: que no quede apretado */
        .order-card .card-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }

        .order-card .card-head .ms-auto {
            margin-left: 0 !important;
        }

        /* BODY: apilar en columna (productos arriba, botones abajo) */
        .order-card .card-body {
            flex-direction: column;
            gap: 12px;
        }

        /* Elimina el ancho fijo que rompe todo */
        .order-right {
            width: 100% !important;
            justify-content: flex-start;
            align-items: stretch;
        }

        /* Botones: que se acomoden bien en móvil */
        .order-actions {
            max-width: 100% !important;
            width: 100%;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 8px;
        }

        .order-actions .btn {
            flex: 1 1 calc(50% - 8px);
            /* 2 por fila */
            width: auto !important;
        }

        /* Producto: permitir wrap real (evita letras verticales) */
        .product-item {
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .order-info, .meta, .product-name {
            white-space: normal !important; word-break: break-word; overflow-wrap: anywhere;
        }
        .product-item .ms-auto { width: 100%; text-align: right; margin-top: 6px; }
    }
</style>

<div class="container mt-30">
    <div class="row">
        <div class="col-lg-12 m-auto">
            <div class="row">
                <div class="col-md-3">
                    <div class="dashboard-menu">
                        <div class="sidebar-title">
                            <i class="fi-rs-shopping-bag"></i> Mis Pedidos
                        </div>
                        <ul class="nav flex-column" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab">
                                    <i class="fi-rs-time-past"></i> Pendientes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pedidos_entregados-tab" data-bs-toggle="tab" href="#pedidos_entregados" role="tab">
                                    <i class="fi-rs-check-circle"></i> Entregados
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pedidos_cancelados-tab" data-bs-toggle="tab" href="#pedidos_cancelados" role="tab">
                                    <i class="fi-rs-cross-circle"></i> Cancelados
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade active show" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <div class="card" style="border-radius:14px;overflow:hidden;border:1px solid rgba(0,78,96,.12);box-shadow:0 2px 14px rgba(0,78,96,.07);">
                                <div class="lp-header">
                                    <i class="fi-rs-shopping-bag" style="font-size:18px;color:#004E60;"></i>
                                    <h3>Seguimiento de Pedido</h3>
                                    <div class="select-wrap lp-select-wrap">
                                        <select id="selectOrden" class="form-select" data-placeholder="Seleccione tu orden"></select>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div id="cardsEmpresas" class="order-cards"></div>

                                        <!-- <table class="table" id="tabla-productos">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>No. Orden</th>
                                                    <th>Empresa</th>
                                                    <th>Status</th>
                                                    <th>Nombre del Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Descuento</th>
                                                    <th>Precio</th>
                                                    <th>Total</th>
                                                    <th>Acciones</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="track-orders" role="tabpanel" aria-labelledby="track-orders-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card mb-3 mb-lg-0">
                                        <div class="card-header">
                                            <h3 class="mb-0">Datos de Facturación</h3>
                                        </div>
                                        <div class="card-body" id="facturacion-entrega">
                                            <!-- Dirección llenada con JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card mb-3 mb-lg-0">
                                        <div class="card-header">
                                            <h3 class="mb-0">Dirección de entrega</h3>
                                        </div>
                                        <div class="card-body" id="direccion-entrega">
                                            <!-- Dirección llenada con JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pedidos_entregados" role="tabpanel" aria-labelledby="address-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card mb-3 mb-lg-0">
                                        <div class="card-header">
                                            <h3 class="mb-0">Pedidos Entregados</h3>
                                        </div>
                                        <div class="card-body" id="direccion-entrega">
                                            <h5 class="text-center">No tiene pedidos entregados</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pedidos_cancelados" role="tabpanel" aria-labelledby="address-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card mb-3 mb-lg-0">
                                        <div class="card-header">
                                            <h3 class="mb-0">Pedidos Cancelados</h3>
                                        </div>
                                        <div class="card-body" id="direccion-entrega">
                                            <h5 class="text-center">No tiene pedidos cancelados</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Mapa -->
<div class="modal fade" id="modalMapaOrden" tabindex="-1" aria-labelledby="modalMapaOrdenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalMapaOrdenLabel">Ubicación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div id="mapaOrden" style="width:100%;height:420px;"></div>
            </div>
            <div class="modal-footer py-2">
                <small class="text-muted" id="mapaOrdenDireccion"></small>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Pago (Dropzone) -->
<div class="modal fade" id="modalPagoOrden" tabindex="-1" aria-labelledby="modalPagoOrdenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Subir comprobante — Orden <span id="numeroOrdenPago"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="id_orden_empresaPago">
                <!-- col-lg-12 con el resumen de productos -->
                <div class="alert alert-info d-flex align-items-start gap-2 mb-3" role="alert" id="bannerPagoFulmuv">
                    <i class="fas fa-shipping-fast mt-1"></i>
                    <div class="h5">
                        <strong>Importante:</strong> Este valor corresponde a <b>FULMUV</b> y se utiliza para la
                        <b>aprobación del envío</b>.
                        <span class="d-block small text-black mt-2">
                            Una vez validado el comprobante, se gestionará el trayecto y la coordinación logística.
                        </span>
                    </div>
                </div>
                <div class="row mb-3">

                    <div class="col-lg-12 mb-3">
                        <div class="table-responsive">

                            <table class="table table-sm" id="tablaPagoProductos">
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
                                    <tr>
                                        <th colspan="3" class="text-end">IVA (15%)</th>
                                        <th class="text-end" id="totalIVAPago"></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">TOTAL</th>
                                        <th class="text-end" id="totalPago"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div> <!-- col-lg-12 con el Dropzone debajo del resumen -->
                    <div class="col-lg-12 mb-3">
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaTarifaEnvio">
                                <thead>
                                    <tr>
                                        <th>Trayecto</th>
                                        <th class="text-end">Peso</th>
                                        <th class="text-end">Tarifa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Datos estáticos de ejemplo -->
                                    <tr>
                                        <td>Local (urbano)</td>
                                        <td class="text-end">1,00 kg</td>
                                        <td class="text-end">$2,50</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">TOTAL A PAGAR POR LA TARIFA</th>
                                        <th class="text-end">$2,50</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <div id="notaTarifaDeposito" class="alert alert-info py-2 px-3 mt-2 mb-0 d-none" role="alert">
                                <strong>Valor a depositar (tarifa de trayecto):</strong>
                                <span id="montoDeposito">—</span>
                                <span class="small text-muted d-block" id="detalleDeposito">(incluye IVA)</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="pago-bg mb-3">
                                <div class="pago-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h3 class="mb-1">Datos de pago</h3>
                                            <div class="sub text-uppercase">CHANGETHEMOVE S.A.S.</div>
                                            <div class="text-muted small">RUC: 1793223041001</div>
                                        </div>
                                        <div class="pago-chip"></div>
                                    </div>

                                    <hr class="pago-divider">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="pago-item"><b>Banco:</b> BANCO PICHINCHA</div>
                                            <div class="pago-item"><b>Tipo Cta:</b> Cuenta Corriente</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="pago-item"><b># Cta:</b> 2100338115</div>
                                            <div class="pago-item"><b>Correo:</b> <a href="mailto:gestiones@fulmuv.com">gestiones@fulmuv.com</a></div>
                                        </div>
                                    </div>

                                    <hr class="pago-divider">

                                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                                        <div class="meta-line">
                                            Enviar comprobante a <a href="mailto:gestiones@fulmuv.com">gestiones@fulmuv.com</a>
                                        </div>
                                        <div class="meta-line">
                                            Cobertura de seguro del envío: <b>100%</b> del valor declarado
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <form class="dropzone" id="miDropzoneManual">
                            <div class="dz-message text-center">
                                <p style="font-size: 16px;">Cargar tus evidencias de archivos de pago aquí o haz clic</p> <i class="fi fi-rs-upload" style="font-size: 32px; color: #888;"></i>
                            </div>
                        </form> <!-- Contenedor para mostrar la lista de archivos -->
                        <div class="row mt-3" id="vista-archivos"></div>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarPago">Guardar Pago</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Detalle (debe estar al nivel raíz del body) -->
<button type="button" class="btn btn-primary d-none" id="btnModal" data-bs-toggle="modal" data-bs-target="#modalDetalleOrden">
    Launch static backdrop modal
</button>
<div class="modal fade" id="modalDetalleOrden" tabindex="-1" aria-labelledby="modalDetalleOrdenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalDetalleOrdenLabel">Detalle de la orden</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <!-- <div class="modal-body">
                <div id="detalleContenido"></div>
            </div> -->

            <div class="modal-body">

                <!-- Banner informativo -->
                <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
                    <i class="fas fa-info-circle mt-1"></i>
                    <div>
                        <strong>Importante:</strong> El <b>total mostrado corresponde únicamente al proveedor</b>.
                        Para confirmar disponibilidad, tiempos de entrega o coordinar detalles, <b>debe comunicarse directamente con el proveedor</b>.
                    </div>
                </div>

                <div id="detalleContenido"></div>
            </div>

        </div>
    </div>
</div>


<!-- Modal: Guía Servientrega -->
<div class="modal fade" id="modalGuiaServi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Guía Grupo Entregas #<span id="modalGuiaId"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end mb-2">
                    <a id="linkGuiaNuevaPestana" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fi-rs-eye"></i> Ver en nueva pestaña
                    </a>
                </div>
                <iframe id="iframeGuia" style="width:100%;height:75vh;border:0"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Información de pago de envío -->
<div class="modal fade" id="modalPagoEnvio" tabindex="-1" aria-labelledby="modalPagoEnvioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalPagoEnvioLabel">Pago de envío y coordinación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <!-- NUEVO "BANNER" CON DISEÑO -->
                <div class="pago-bg mb-3">
                    <div class="pago-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h3 class="mb-1">Datos de pago</h3>
                                <div class="sub text-uppercase">CHANGETHEMOVE S.A.S.</div>
                                <div class="text-muted small">RUC: 1793223041001</div>
                            </div>
                            <div class="pago-chip"></div>
                        </div>

                        <hr class="pago-divider">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="pago-item"><b>Banco:</b> BANCO PICHINCHA</div>
                                <div class="pago-item"><b>Tipo Cta:</b> Cuenta Corriente</div>
                            </div>
                            <div class="col-md-6">
                                <div class="pago-item"><b># Cta:</b> 2100338115</div>
                                <div class="pago-item"><b>Correo:</b> <a href="mailto:gesLones@fulmuv.com">gesLones@fulmuv.com</a></div>
                            </div>
                        </div>

                        <hr class="pago-divider">

                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="meta-line">
                                Enviar comprobante a <a href="mailto:gesLones@fulmuv.com">gesLones@fulmuv.com</a>
                            </div>
                            <div class="meta-line">
                                Cobertura de seguro del envío: <b>90%</b> del valor declarado
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendido</button>
                <!-- (Opcional) botón que dispara tu generarOrden() directamente -->
                <!-- <button type="button" class="btn btn-primary" onclick="generarOrden(); document.getElementById('modalPagoEnvioClose').click();">Generar orden ahora</button> -->
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
<script src="js/lista_pedidos.js?v=<?php echo $timer; ?>"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg"></script>

<!-- Dropzone CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
<!-- Dropzone JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
    Dropzone.autoDiscover = false;

    let archivosPendientes = [];

    const dropzone = new Dropzone("#miDropzoneManual", {
        url: "#", // No se usa porque se subirá manualmente
        autoProcessQueue: false,
        addRemoveLinks: true,
        maxFilesize: 5,
        previewsContainer: false,
        maxFiles: 10,
        acceptedFiles: "image/*,.pdf",
        paramName: "file",
        init: function() {
            this.on("addedfile", function(file) {
                archivosPendientes.push(file);
                renderListaArchivos();
            });

            this.on("removedfile", function(file) {
                archivosPendientes = archivosPendientes.filter(f => f.name !== file.name);
                renderListaArchivos();
            });
        }
    });


    $(document).ready(function() {
        // Inicializar Select2
        $('#ordenSelect').select2({
            placeholder: "Selecciona una orden...",
            allowClear: true
        });

        // Cargar órdenes pendientes vía POST
        // $.post("api/ordenes_pendientes.php", {}, function(respuesta) {
        //     if (respuesta && respuesta.error === false) {
        //         respuesta.data.forEach(orden => {
        //             $('#ordenSelect').append(`
        //         <option value="${orden.numero_orden}">
        //             Orden #${orden.numero_orden} - ${orden.fecha}
        //         </option>
        //     `);
        //         });
        //     } else {
        //         console.warn("No se encontraron órdenes pendientes");
        //     }
        // }, 'json');
    });


    function renderListaArchivos() {
        const contenedor = document.getElementById("vista-archivos");
        contenedor.innerHTML = "";

        archivosPendientes.forEach((file) => {
            const extension = file.name.split('.').pop().toLowerCase();
            const isPDF = extension === "pdf";
            const objectURL = URL.createObjectURL(file);
            const fileSizeKB = (file.size / 1024).toFixed(2);

            const vista = isPDF ?
                `<img src="img/pdf.png" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px;" />` :
                `<img src="${objectURL}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px;" />`;

            contenedor.innerHTML += `
            <div class="col-lg-6 col-md-6 col-sm-6 col-12 archivo-card mb-3">
                <div class="card-file" style="height: 220px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; padding: 10px;">
                    <div class="vista-preview text-center" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        ${vista}
                    </div>
                    <div class="info text-center">
                        <span style="font-weight: bold; font-size: 10px" class="limitar-lineas-1">${file.name}</span>
                        <span>${fileSizeKB} KB</span>
                    </div>
                    <div class="acciones mt-2 d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="verArchivo('${objectURL}')">
                            <i class="fi-rs-eye" style="font-size: 12px; color: #FFF"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" style="background-color: #ff4d00ff" onclick="eliminarArchivo('${file.name}')">
                            <i class="fi-rs-trash" style="font-size: 12px; color: #FFF"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        });
    }


    function verArchivo(url) {
        window.open(url, "_blank");
    }



    function eliminarArchivo(nombreArchivo) {
        // Buscar archivo por nombre
        const archivo = archivosPendientes.find(f => f.name === nombreArchivo);
        if (!archivo) return;

        // Remover de Dropzone (para mantenerlo sincronizado visualmente)
        dropzone.removeFile(archivo);

        // Remover del array
        archivosPendientes = archivosPendientes.filter(f => f.name !== nombreArchivo);

        // Volver a renderizar la lista
        renderListaArchivos();
    }
    // Subir archivos al hacer clic en "Guardar Pago"
    document.getElementById("guardarPago").addEventListener("click", function() {
        if (archivosPendientes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'FulMuv',
                text: 'No hay archivos para subir.',
                showConfirmButton: false
            })
            return;
        }

        const formData = new FormData();

        archivosPendientes.forEach(file => {
            formData.append("archivos[]", file);
        });

        fetch("cargar_imagen_pago.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.response === "success") {
                    console.log("Archivos subidos correctamente:");
                    console.log(data.data); // <- array con las rutas y tipo
                    $.post("api/v1/fulmuv/empresas/pagoOrden", {
                        id_orden: $("#id_orden_empresaPago").val(),
                        imagenArray: data.data["archivos"]
                    }, function(returnedData) {

                        if (!returnedData.error) {
                            Swal.fire({
                                icon: 'success',
                                title: 'FulMuv',
                                text: 'Archivos guardados correctamente.',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                window.location.reload(); // refresca la pantalla
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'FULMUV',
                                text: 'No se generó el pago.',
                                showConfirmButton: true
                            });
                        }
                    }, 'json')


                    // ✅ Opcional: limpiar Dropzone y lista
                    archivosPendientes = [];
                    dropzone.removeAllFiles(true);
                    renderListaArchivos();
                }
            })
            .catch(err => {
                console.error("Error en la subida:", err);
                Swal.fire({
                    icon: 'warning',
                    title: 'FulMuv',
                    text: 'Error al subir archivos.',
                    showConfirmButton: false
                })
            });
    });
</script>