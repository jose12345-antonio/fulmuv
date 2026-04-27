<?php
include 'includes/header.php';

echo '<input type="hidden" placeholder="" id="id_cliente" class="form-control" value="' . $_GET["id_usuario"] . '" />';
?>
<link rel="canonical" href="https://fulmuv.com/lista_pedidos.php">

<style>
    .orders-shell {
        padding-top: 10px;
        padding-bottom: 24px;
    }

    .orders-shell .dashboard-menu,
    .orders-shell .card,
    .orders-shell .modal-content {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        background: #ffffff;
    }

    .orders-shell .dashboard-menu {
        padding: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .orders-shell .dashboard-menu .nav-link {
        border-radius: 12px;
        padding: 12px 14px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
        border: 1px solid transparent;
    }

    .orders-shell .dashboard-menu .nav-link.active {
        background: #0f766e;
        color: #ffffff;
        border-color: #0f766e;
    }

    .orders-shell .card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .orders-shell .card-body {
        padding: 18px 20px;
    }

    .orders-shell #selectOrden,
    .orders-shell .select2-container {
        min-width: 220px;
    }

    .card-file {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .card-file i {
        font-size: 2rem;
        color: #555;
    }

    .card-file small {
        display: block;
        margin-top: 5px;
        word-break: break-word;
    }

    .btn-sm {
        padding: 2px 10px !important;
    }

    .dropzone {
        border: 2px dashed #ccc;
        background: #f5f5f5;
        padding: 40px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .dropzone:hover {
        background-color: #f0f0f0;
    }

    .dropzone .dz-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #666;
    }

    .dropzone .dz-message i {
        margin-top: 10px;
    }

    .order-cards {
        display: grid;
        gap: 16px;
    }

    .order-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .order-card .card-head {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 16px;
        background: #f8fafc;
        align-items: center;
        border-bottom: 1px solid #eef2f7;
    }

    .order-card .kv .k {
        font-size: 12px;
        color: #6b7280;
        margin-right: 6px;
    }

    .order-card .kv .v {
        font-weight: 600;
        color: #111827;
    }

    .order-card .card-body {
        display: flex;
        gap: 14px;
        padding: 16px;
    }

    .order-left {
        display: flex;
        gap: 12px;
        flex: 1 1 auto;
        min-width: 0;
    }

    .product-thumb {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #eee;
    }

    .order-info {
        min-width: 0;
    }

    .order-info h6 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .product-name {
        font-weight: 700;
        text-transform: uppercase;
        color: #0f172a;
    }

    .meta {
        font-size: 12px;
        color: #6b7280;
    }

    .product-item {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .product-item:last-child {
        border-bottom: 0;
    }

    .order-right {
        width: 240px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .order-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
        max-width: 220px;
        margin: 0 auto;
    }

    .order-actions .btn {
        width: 100%;
        min-height: 42px;
        border-radius: 12px;
        font-weight: 700;
    }

    .badge-estado {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
    }

    /* ya tenías mapping en JS; aquí sólo un fallback neutro */
    .badge-estado.bg-light {
        background: #f1f5f9;
        color: #111827;
    }

    .order-footer {
        padding: 10px 14px;
        border-top: 1px solid #eef2f7;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal {
        z-index: 1060;
    }

    .modal-backdrop {
        z-index: 1055;
    }

    .shipping-box {
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        border-radius: 10px;
        padding: 12px 14px;
        margin-top: 10px;
    }

    .shipping-box .label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .shipping-box .amount {
        font-weight: 800;
        font-size: 14px;
    }

    .shipping-box .note {
        font-size: 12px;
        color: #6b7280;
    }

    .shipping-box.success {
        background: #ecfdf5;
        border-color: #10b981;
    }

    .shipping-box.success .amount {
        color: #065f46;
    }

    .order-empty-state {
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        padding: 30px 20px;
        text-align: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #64748b;
    }

    .order-empty-state strong {
        display: block;
        font-size: 18px;
        color: #0f172a;
        margin-bottom: 8px;
    }

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

    @media (max-width: 768px) {
        .orders-shell {
            margin-top: 16px;
            padding-bottom: 18px;
        }

        .orders-shell .row {
            --bs-gutter-x: 16px;
        }

        .orders-shell .dashboard-menu {
            margin-bottom: 14px;
            overflow-x: auto;
            padding: 10px;
        }

        .orders-shell .dashboard-menu ul {
            flex-direction: row !important;
            flex-wrap: nowrap;
            gap: 10px;
        }

        .orders-shell .dashboard-menu .nav-item {
            flex: 0 0 auto;
        }

        .orders-shell .dashboard-menu .nav-link {
            margin-bottom: 0;
            white-space: nowrap;
            min-width: max-content;
        }

        .orders-shell .card-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 12px;
            padding: 16px;
        }

        .orders-shell #selectOrden,
        .orders-shell .select2-container {
            width: 100% !important;
            min-width: 100%;
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

        .order-info,
        .meta,
        .product-name {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* Precio a la derecha: que baje si no cabe */
        .product-item .ms-auto {
            width: 100%;
            text-align: right;
            margin-top: 6px;
        }
    }

    /* Extra para pantallas MUY pequeñas */
    @media (max-width: 420px) {
        .order-actions .btn {
            flex: 1 1 100%;
            /* 1 por fila */
        }

        .product-thumb {
            width: 54px;
            height: 54px;
        }
        .orders-shell .card-header h3 {
            font-size: 20px;
        }
    }

    @media (max-width: 768px) {
        .modal-dialog {
            margin: 10px;
        }

        .modal-content {
            border-radius: 18px;
        }

        #tablaPagoProductos,
        #tablaTarifaEnvio,
        #detalleContenido .table {
            min-width: 620px;
        }

        .pago-card {
            border-radius: 18px;
        }
    }

    @media (max-width: 420px) {
        .orders-shell .card-body,
        .orders-shell .card-header {
            padding-left: 14px;
            padding-right: 14px;
        }
    }
</style>

<div class="container mt-30 orders-shell">
    <div class="row">
        <div class="col-lg-12 m-auto">
            <div class="row">
                <div class="col-md-3">
                    <div class="dashboard-menu">
                        <ul class="nav flex-column" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="false"><i class="fi-rs-shopping-bag mr-10"></i>Pedidos Pendientes</a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link" id="track-orders-tab" data-bs-toggle="tab" href="#track-orders" role="tab" aria-controls="track-orders" aria-selected="false"><i class="fi-rs-receipt mr-10"></i>Datos de Facturación</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="address-tab" data-bs-toggle="tab" href="#address" role="tab" aria-controls="address" aria-selected="true"><i class="fi-rs-marker mr-10"></i>Mi dirección a domicilio</a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link" id="pedidos_entregados-tab" data-bs-toggle="tab" href="#pedidos_entregados" role="tab" aria-controls="pedidos_entregados" aria-selected="true"><i class="fi-rs-check mr-10"></i>Pedidos Entregados</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pedidos_cancelados-tab" data-bs-toggle="tab" href="#pedidos_cancelados" role="tab" aria-controls="pedidos_cancelados" aria-selected="true"><i class="fi-rs-trash mr-10"></i>Pedidos Cancelados</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade active show" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0 fw-bold">Tu Orden
                                        <button class="btn btn-primary btn-md ms-2 d-none" data-bs-toggle="modal" data-bs-target="#modalPagoEnvio">
                                            <i class="fi-rs-credit-card me-1"></i> Datos de Pago
                                        </button>
                                    </h3>
                                    <div>
                                        <select id="selectOrden" class="form-select" data-placeholder="Seleccione tu orden">

                                        </select>
                                    </div>

                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
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
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnSeleccionarComprobante">
                                Seleccionar comprobante
                            </button>
                        </div>
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
<script src="js/lista_pedidos.js?v1.0.0.0.0.0.0.0.0.0.0.2.2.0.0.13"></script>
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
            const isDoc = extension === "doc" || extension === "docx";
            const objectURL = URL.createObjectURL(file);
            const fileSizeKB = (file.size / 1024).toFixed(2);

            const vista = isPDF ?
                `<img src="img/pdf.png" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px;" />` :
                isDoc ?
                `<div class="d-flex align-items-center justify-content-center w-100 h-100 rounded" style="background:#eff6ff;color:#1d4ed8;font-weight:800;font-size:28px;">DOC</div>` :
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

    function agregarArchivosPendientes(files) {
        (files || []).forEach((file) => {
            const repetido = archivosPendientes.some(f => f.name === file.name && f.size === file.size);
            if (!repetido) {
                archivosPendientes.push(file);
            }
        });
        renderListaArchivos();
    }

    async function seleccionarComprobantesDesdeDispositivo() {
        const files = await seleccionarArchivosApp({
            accept: "image/*,.pdf,.doc,.docx",
            multiple: true
        });

        if (files.length) {
            agregarArchivosPendientes(files);
        }
    }


    function verArchivo(url) {
        window.open(url, "_blank");
    }



    function eliminarArchivo(nombreArchivo) {
        // Buscar archivo por nombre
        const archivo = archivosPendientes.find(f => f.name === nombreArchivo);
        if (!archivo) return;

        // Remover de Dropzone (para mantenerlo sincronizado visualmente)
        if (dropzone.files.includes(archivo)) {
            dropzone.removeFile(archivo);
        }

        // Remover del array
        archivosPendientes = archivosPendientes.filter(f => f.name !== nombreArchivo);

        // Volver a renderizar la lista
        renderListaArchivos();
    }

    document.getElementById("btnSeleccionarComprobante").addEventListener("click", function() {
        seleccionarComprobantesDesdeDispositivo();
    });

    document.getElementById("miDropzoneManual").addEventListener("click", function(e) {
        if (!puedeUsarFlutterBridge()) return;
        e.preventDefault();
        e.stopPropagation();
        seleccionarComprobantesDesdeDispositivo();
    });
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
                    $.post("../api/v1/fulmuv/empresas/pagoOrden", {
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
 