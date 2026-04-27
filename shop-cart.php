<?php
include 'includes/header.php';

$id_empresa = $_GET["q"];

echo '<input type="hidden" placeholder="" id="id_empresa" class="form-control" value=' . $id_empresa . ' />';
?>
<link rel="canonical" href="https://fulmuv.com/shop-cart.php">

<style>
    .vendor-wrap {
        height: 100%;
    }

    .vendor-img {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .vendor-img img {
        max-height: 100%;
        width: auto;
        object-fit: contain;
    }

    .span.h2 {
        font-size: 12px;
    }

    /* ===== CART SUMMARY CARD ===== */
    .cart-summary-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 28px rgba(0, 78, 96, 0.13);
        border: 1px solid rgba(0, 78, 96, 0.14);
        background: #fff;
        position: sticky;
        top: 20px;
    }

    .cscard-header {
        background: #004E60;
        color: #fff;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .cscard-header i {
        color: #FFDC2B;
        font-size: 17px;
    }

    .cscard-body {
        padding: 4px 0 0;
    }

    .cscard-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 11px 20px;
        border-bottom: 1px solid rgba(0, 78, 96, 0.07);
    }

    .cscard-row:last-child {
        border-bottom: none;
    }

    .cscard-label {
        font-size: 13px;
        color: #4a5568;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 0;
    }

    .cscard-label i {
        color: #004E60;
        font-size: 13px;
        opacity: 0.7;
    }

    .cscard-value {
        font-size: 14px;
        color: #004E60;
        font-weight: 700;
        white-space: nowrap;
    }

    .cscard-row-savings {
        background: rgba(144, 255, 189, 0.18);
    }

    .cscard-row-savings .cscard-label {
        color: #00754a;
    }

    .cscard-row-savings .cscard-label i {
        color: #00754a;
        opacity: 1;
    }

    .cscard-row-savings .cscard-value {
        color: #00754a;
    }

    .cscard-divider {
        height: 1px;
        background: rgba(0, 78, 96, 0.13);
        margin: 2px 20px 0;
    }

    .cscard-row-estimated {
        padding: 13px 20px;
        background: rgba(0, 78, 96, 0.04);
    }

    .cscard-row-estimated .cscard-label {
        font-size: 13.5px;
        font-weight: 700;
        color: #004E60;
    }

    .cscard-row-estimated .cscard-value {
        font-size: 17px;
        font-weight: 900;
        color: #004E60;
    }

    .cscard-row-meta {
        padding: 8px 20px;
    }

    .cscard-meta-label {
        font-size: 12px;
        color: #888;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }

    .cscard-meta-label i {
        font-size: 12px;
        color: #aaa;
    }

    .cscard-meta-value {
        font-size: 12px;
        color: #555;
        text-align: right;
        max-width: 150px;
        line-height: 1.3;
    }

    .cscard-info-btn {
        background: none;
        border: none;
        padding: 0 2px;
        cursor: pointer;
        color: #004E60;
        font-size: 13px;
        opacity: 0.6;
        line-height: 1;
    }

    .cscard-info-btn:hover {
        opacity: 1;
    }

    /* TyC */
    .cscard-tyc {
        background: rgba(0, 78, 96, 0.04);
        border-top: 1px solid rgba(0, 78, 96, 0.10);
        border-bottom: 1px solid rgba(0, 78, 96, 0.10);
        padding: 14px 18px;
    }

    .cscard-tyc-info {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 12px;
    }

    .cscard-tyc-info i {
        color: #004E60;
        font-size: 14px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .cscard-tyc-info p {
        font-size: 11.5px;
        color: #555;
        margin: 0;
        line-height: 1.45;
    }

    .cscard-tyc .form-check-input:checked {
        background-color: #004E60;
        border-color: #004E60;
    }

    .cscard-tyc .form-check-label {
        font-size: 11.5px;
        color: #444;
        line-height: 1.45;
    }

    .cscard-tyc-link {
        color: #004E60;
        font-weight: 600;
        font-size: 11.5px;
        display: inline-block;
        margin-top: 4px;
        text-decoration: underline;
    }

    .cscard-tyc-link:hover {
        color: #FF6D01;
    }

    /* Total final */
    .cscard-total {
        background: #004E60;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cscard-total-label {
        color: #90FFBD;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .cscard-total-value {
        color: #FFDC2B;
        font-size: 24px;
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    /* Botón */
    .cscard-footer {
        padding: 14px 18px 18px;
        background: #fff;
    }

    .cscard-btn {
        background: #FF6D01 !important;
        color: #fff !important;
        font-weight: 700;
        font-size: 14px;
        border-radius: 12px;
        padding: 13px 20px;
        border: none;
        transition: background 0.2s, transform 0.1s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

    .cscard-btn:hover {
        background: #e55e00 !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .cscard-btn.disabled {
        background: rgba(0, 78, 96, 0.20) !important;
        color: #fff !important;
        pointer-events: none;
        transform: none;
    }

    /* ── Secciones internas ─────────────────────────────── */
    .cscard-section {
        border-radius: 0;
    }

    .cscard-section-hd {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 13px 18px 11px;
    }

    .cscard-section--teal .cscard-section-hd {
        background: rgba(0, 78, 96, 0.07);
        border-left: 4px solid #004E60;
    }

    .cscard-section--orange .cscard-section-hd {
        background: rgba(255, 109, 1, 0.07);
        border-left: 4px solid #FF6D01;
    }

    .cscard-section-hd-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }

    .cscard-section--teal .cscard-section-hd-icon {
        background: #004E60;
        color: #FFDC2B;
    }

    .cscard-section--orange .cscard-section-hd-icon {
        background: #FF6D01;
        color: #fff;
    }

    .cscard-section-title {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
    }

    .cscard-section-note {
        display: block;
        font-size: 11px;
        color: #7a8898;
        margin-top: 3px;
        line-height: 1.3;
        font-style: italic;
    }

    .cscard-rows {
        padding: 4px 0;
    }

    .cscard-row-total-ref {
        background: rgba(0, 78, 96, 0.05);
        border-top: 1px solid rgba(0, 78, 96, 0.10) !important;
    }

    .cscard-row-total-ref .cscard-label {
        font-weight: 700;
        color: #004E60;
    }

    .cscard-value-total {
        font-size: 17px !important;
        font-weight: 900 !important;
        color: #004E60 !important;
    }

    .cscard-between-sep {
        height: 6px;
        background: #f1f5f9;
        border-top: 1px solid rgba(0, 78, 96, 0.09);
        border-bottom: 1px solid rgba(0, 78, 96, 0.09);
    }

    @media (max-width: 576px) {
        .cart-summary-card {
            position: static;
            margin-top: 24px;
        }
    }

    /* ── Mini lista productos (panel derecho) ───────────────── */
    .cscard-products-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 18px 10px;
        background: #004E60;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
    }

    .cscard-products-header i {
        color: #FFDC2B;
        font-size: 14px;
    }

    .cscard-products-count {
        margin-left: auto;
        background: #FFDC2B;
        color: #004E60;
        font-size: 11px;
        font-weight: 800;
        border-radius: 20px;
        padding: 1px 8px;
        line-height: 1.6;
    }

    .cscard-products-list {
        max-height: 210px;
        overflow-y: auto;
        border-bottom: 1px solid rgba(0, 78, 96, 0.10);
    }

    .cscard-products-list::-webkit-scrollbar {
        width: 4px;
    }

    .cscard-products-list::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .cscard-products-list::-webkit-scrollbar-thumb {
        background: rgba(0, 78, 96, 0.3);
        border-radius: 4px;
    }

    .cscard-prod-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 16px;
        border-bottom: 1px solid rgba(0, 78, 96, 0.06);
    }

    .cscard-prod-item:last-child {
        border-bottom: none;
    }

    .cscard-prod-img {
        width: 42px;
        height: 42px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid rgba(0, 78, 96, 0.12);
        flex-shrink: 0;
    }

    .cscard-prod-info {
        flex: 1;
        min-width: 0;
    }

    .cscard-prod-name {
        font-size: 11.5px;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.3;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .cscard-prod-qty {
        font-size: 11px;
        color: #888;
        margin-top: 2px;
    }

    .cscard-prod-subtotal {
        font-size: 13px;
        font-weight: 800;
        color: #004E60;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* ── Encabezados de tabla del carrito ─────────────────── */
    .shopping-summery table.table-wishlist thead tr,
    .shopping-summery table.table-wishlist thead {
        background: #004E60 !important;
    }

    .shopping-summery table.table-wishlist thead th {
        text-align: center;
        vertical-align: middle;
        padding: 14px 12px;
        font-size: 13px;
        font-weight: 700;
        color: #fff !important;
        background-color: #004E60 !important;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: none !important;
        white-space: nowrap;
    }

    .shopping-summery table.table-wishlist thead th:first-child {
        border-radius: 12px 0 0 12px;
        width: 110px;
    }

    .shopping-summery table.table-wishlist thead th:last-child {
        border-radius: 0 12px 12px 0;
    }

    /* ── Filas body ─────────────────────────────────────── */
    .shopping-summery table.table-wishlist tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid rgba(0, 78, 96, 0.08) !important;
    }

    .shopping-summery table.table-wishlist tbody tr:last-child {
        border-bottom: none !important;
    }

    .shopping-summery table.table-wishlist tbody tr:hover {
        background: rgba(0, 78, 96, 0.025);
    }

    .shopping-summery table.table-wishlist tbody td {
        text-align: center;
        vertical-align: middle;
        padding: 18px 12px;
        border-top: none !important;
    }

    /* Col Producto: izquierda */
    .shopping-summery table.table-wishlist tbody td:nth-child(2) {
        text-align: left;
        min-width: 220px;
    }

    /* ── Imagen ──────────────────────────────────────────── */
    .cart-item-img {
        width: 86px;
        height: 86px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid rgba(0, 78, 96, 0.12);
        display: block;
        margin: 0 auto;
    }

    /* ── Info del producto ───────────────────────────────── */
    .cart-product-name {
        font-size: 12.5px;
        font-weight: 400;
        color: #1e293b;
        line-height: 1.35;
        margin-bottom: 5px;
        max-width: 260px;
    }

    .cart-product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 7px;
    }

    .cart-product-meta span {
        font-size: 10.5px;
        font-weight: 600;
        border-radius: 20px;
        padding: 2px 9px;
        line-height: 1.6;
    }

    .cart-product-meta .meta-codigo {
        background: rgba(0, 78, 96, 0.08);
        color: #004E60;
    }

    .cart-product-meta .meta-peso {
        background: rgba(255, 220, 43, 0.20);
        color: #7a5c00;
    }

    .cart-product-delete a {
        font-size: 11.5px;
        color: #FF6D01;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .cart-product-delete a:hover {
        color: #c45200;
    }

    /* ── Precio unitario ─────────────────────────────────── */
    .cart-price-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }

    .cart-price-original {
        font-size: 11.5px;
        color: #FF6D01;
        text-decoration: line-through;
        font-weight: 600;
    }

    .cart-price-current {
        font-size: 14.5px;
        font-weight: 800;
        color: #004E60;
    }

    /* ── Subtotal ────────────────────────────────────────── */
    .cart-subtotal-val {
        font-size: 16px;
        font-weight: 900;
        color: #004E60;
        display: block;
        letter-spacing: -0.01em;
    }

    /* ── Badge IVA ───────────────────────────────────────── */
    .badge.bg-success {
        background: rgba(0, 78, 96, 0.12) !important;
        color: #004E60 !important;
        font-size: 10px;
        font-weight: 700;
        border-radius: 20px;
        padding: 2px 7px;
    }

    .cart-info-banner {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        background: linear-gradient(135deg, rgba(0, 78, 96, 0.06) 0%, rgba(0, 78, 96, 0.02) 100%);
        border: 1.5px solid rgba(0, 78, 96, 0.18);
        border-left: 4px solid #004E60;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 24px;
    }

    .cart-info-banner .cart-info-icon {
        width: 36px;
        height: 36px;
        background: #004E60;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #FFDC2B;
        font-size: 15px;
    }

    .cart-info-banner p {
        margin: 0;
        font-size: 13.5px;
        color: #1e3a44;
        line-height: 1.55;
    }

    .cart-info-banner p strong {
        color: #004E60;
    }
</style>

<div class="container mb-80 mt-50">
    <div class="row d-none">
        <div class="col-lg-8 mb-20">
            <h1 class="heading-2 mb-0">Tu Carrito</h1>
            <span id="totalCarritoShop" class="d-none"></span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="cart-info-banner">
                <div class="cart-info-icon">
                    <i class="fi fi-rs-truck-side"></i>
                </div>
                <p>
                    <strong>Gestiona directamente la compra de tus productos seleccionados con cada proveedor.</strong>
                    Una vez comprados, FULMUV gestiona tu envío a domicilio.
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <!-- ✅ DESKTOP/TABLET: tabla -->
            <div class="table-responsive shopping-summery d-none d-md-block">
                <table class="table table-wishlist">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="listaProductoShopCart"></tbody>
                </table>
            </div>

            <!-- ✅ MÓVIL: cards -->
            <div class="d-block d-md-none" id="listaProductoShopCartMobile"></div>
            <div class="divider-2 mb-30"></div>
            <div class="cart-action d-flex justify-content-between">
                <a class="btn " href="vendor.php"><i class="fi-rs-arrow-left mr-10"></i>Continúa comprando</a>
                <a class="btn mr-10 mb-sm-15" onclick="actualizarshopCart()"><i class="fi-rs-refresh mr-10"></i>Actualizar carrito</a>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cart-summary-card">

                <!-- === Mini lista de productos === -->
                <div class="cscard-products-header">
                    <i class="fi fi-rs-shopping-bag"></i>
                    <span>Productos en tu carrito</span>
                    <span class="cscard-products-count" id="cscard-prod-count"></span>
                </div>
                <div class="cscard-products-list" id="listaProductoShopCartSummary"></div>

                <!-- === SECCIÓN 1: Pago a proveedores === -->
                <div class="cscard-section cscard-section--teal">
                    <div class="cscard-section-hd">
                        <div class="cscard-section-hd-icon">
                            <i class="fi fi-rs-store-alt"></i>
                        </div>
                        <div>
                            <span class="cscard-section-title">Pago a proveedores</span>
                            <span class="cscard-section-note">Gestiónalo directamente con los proveedores.</span>
                        </div>
                    </div>
                    <div class="cscard-rows">
                        <div class="cscard-row">
                            <span class="cscard-label">Valor de productos</span>
                            <span class="cscard-value" id="cart_carrito_amount"></span>
                        </div>
                        <div class="cscard-row">
                            <span class="cscard-label">
                                IVA
                                <button type="button" class="cscard-info-btn"
                                    data-bs-toggle="modal" data-bs-target="#modalIVA" id="btnInfoIVA">
                                    <i class="fi-rs-interrogation"></i>
                                </button>
                            </span>
                            <span class="cscard-value" id="cart_iva_amount"></span>
                        </div>
                        <div class="cscard-row cscard-row-savings">
                            <span class="cscard-label">
                                <i class="fi fi-rs-label"></i> Descuento
                            </span>
                            <span class="cscard-value" id="cart_ahorro_amount"></span>
                        </div>
                        <div class="cscard-row cscard-row-total-ref">
                            <span class="cscard-label">Total de pago a proveedores</span>
                            <span class="cscard-value cscard-value-total" id="cart_total_amount"></span>
                        </div>
                    </div>
                </div>

                <!-- Separador visual entre secciones -->
                <div class="cscard-between-sep"></div>

                <!-- === SECCIÓN 2: Envío FULMUV === -->
                <div class="cscard-section cscard-section--orange">
                    <div class="cscard-section-hd">
                        <div class="cscard-section-hd-icon">
                            <i class="fi fi-rs-truck-side"></i>
                        </div>
                        <div>
                            <span class="cscard-section-title">Gestión de envío a domicilio por FULMUV</span>
                        </div>
                    </div>
                    <div class="cscard-rows">
                        <div class="cscard-row">
                            <span class="cscard-label">Valor de envío</span>
                            <span class="cscard-meta-value">Se calcula en el checkout</span>
                        </div>
                    </div>
                </div>

                <!-- TyC -->
                <div class="cscard-tyc">
                    <div class="cscard-tyc-info">
                        <i class="fi fi-rs-info"></i>
                        <p>El valor de los productos se paga directamente al proveedor o vendedor.
                            FULMUV únicamente gestiona la intermediación comercial y el servicio logístico de entrega.
                            El pago realizado a FULMUV corresponde exclusivamente al servicio de envío a domicilio y coordinación logística nacional.
                            Antes de confirmar tu compra, comunícate con el proveedor y verifica disponibilidad, características y condiciones del producto.

                        </p>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input mt-1" type="checkbox" id="aceptoTyC">
                        <label class="form-check-label" for="aceptoTyC">
                            Declaro que he leído y acepto los Términos y Condiciones de Uso para Usuarios Visitantes y Compradores y el Aviso Legal de FULMUV. Entiendo que FULMUV actúa como plataforma de contacto y no interviene en las negociaciones ni garantiza productos o servicios; toda relación comercial se realiza directamente con el proveedor. Autorizo el tratamiento de mis datos personales conforme a la LOPDP del Ecuador.
                            <a href="#" class="cscard-tyc-link" data-bs-toggle="modal" data-bs-target="#modalTyC">
                                Ver documentos legales
                            </a>
                        </label>
                    </div>
                </div>

                <!-- Botón -->
                <div class="cscard-footer">
                    <a id="btnContinuarOrden"
                        href="shop-checkout.php"
                        class="cscard-btn w-100"
                        tabindex="-1">
                        Continuar con Pedido y Envío <i class="fi-rs-sign-out"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIVA" tabindex="-1" aria-labelledby="modalIVALabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalIVALabel">
                    Detalle del IVA incluido en el precio
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border small mb-3">
                    Algunos productos tienen <strong>IVA incluido</strong> en su precio.
                    En el resumen, se muestra como ahorro el valor que corresponde al <strong>15%</strong> del subtotal de esos productos:
                    <br><strong>IVA</strong> = Subtotal × 0.15
                </div>

                <div id="ivaDetalleContenido"></div>

                <div class="mt-3 border-top pt-3 d-flex justify-content-between align-items-center">
                    <div class="fw-bold">Total IVA</div>
                    <div class="fw-bold text-primary fw-bold" id="ivaTotalRetirado"></div>
                </div>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal TyC con Tabs que controlan un IFRAME -->
<div class="modal fade" id="modalTyC" tabindex="-1" aria-labelledby="modalTyCLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalTyCLabel">Términos y condiciones</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-0">
                <!-- NAV TABS -->
                <ul class="nav nav-tabs px-3 pt-3" id="tycTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link active"
                            id="tab-tyc-proveedores"
                            data-bs-toggle="tab"
                            data-bs-target="#pane-iframe"
                            type="button"
                            role="tab"
                            aria-controls="pane-iframe"
                            aria-selected="true"
                            data-src="https://fulmuv.com/documentos/8_Aviso Legal y Descargos de Responsabilidad de FULMUV.pdf">
                            <span class="text-center ms-4">T&C Proveedores</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link text-center"
                            id="tab-privacidad"
                            data-bs-toggle="tab"
                            data-bs-target="#pane-iframe"
                            type="button"
                            role="tab"
                            aria-controls="pane-iframe"
                            aria-selected="false"
                            data-src="https://fulmuv.com/documentos/8_Política de Privacidad y Cookies de FULMUV.pdf">
                            <span class="text-center ms-4">Privacidad & Cookies</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            id="tab-aviso-legal"
                            data-bs-toggle="tab"
                            data-bs-target="#pane-iframe"
                            type="button"
                            role="tab"
                            aria-controls="pane-iframe"
                            aria-selected="false"
                            data-src="https://fulmuv.com/documentos/8_Términos y Condiciones de Uso General para Usuarios Visitantes y Compradores de FULMUV.pdf">
                            <span class="text-center ms-4">Aviso Legal</span>
                        </button>
                    </li>
                </ul>

                <!-- CONTENEDOR DEL IFRAME (un único pane reutilizado) -->
                <div class="tab-content border-top" id="tycTabsContent">
                    <div class="tab-pane fade show active" id="pane-iframe" role="tabpanel" aria-labelledby="tab-tyc-proveedores">
                        <div class="ratio ratio-16x9">
                            <iframe id="tycIframe" src="" title="Documentos legales" allowfullscreen loading="lazy" style="border:0;"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const modalEl = document.getElementById('modalTyC');
        const iframe = document.getElementById('tycIframe');
        const tabs = document.querySelectorAll('#tycTabs .nav-link');

        function setIframeFromActiveTab({
            bust = false
        } = {}) {
            const activeTab = document.querySelector('#tycTabs .nav-link.active');
            const url = activeTab?.getAttribute('data-src');
            if (!iframe || !url) return;

            let finalUrl = url;
            if (bust) {
                const u = new URL(url, location.origin);
                u.searchParams.set('_ts', Date.now());
                finalUrl = u.toString();
            }
            iframe.src = finalUrl;
        }

        function iframeIsEmpty() {
            const current = iframe?.getAttribute('src') || '';
            return !current || current === 'about:blank';
        }

        // Precarga el primero al cargar la página (opcional, puedes quitar si no lo deseas)
        document.addEventListener('DOMContentLoaded', () => {
            if (iframeIsEmpty()) setIframeFromActiveTab();
        });

        // Cargar el primer documento justo al abrir el modal (antes de la animación)
        modalEl.addEventListener('show.bs.modal', function() {
            if (iframeIsEmpty()) setIframeFromActiveTab();
        });

        // Cambiar el documento del iframe al cambiar de tab
        tabs.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function(e) {
                const url = e.target.getAttribute('data-src');
                if (iframe && url) {
                    const u = new URL(url, location.origin);
                    u.searchParams.set('_ts', Date.now()); // cache-buster
                    iframe.src = u.toString();
                }
            });
        });

        // Limpiar el iframe al cerrar el modal (opcional)
        modalEl.addEventListener('hidden.bs.modal', function() {
            if (iframe) iframe.removeAttribute('src');
        });
    })();
</script>



<?php
include 'includes/footer.php';
?>
<script src="js/shop-cart.js?v1.0.0.0.0.0.0.0.0.22"></script>