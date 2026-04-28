<?php
include 'includes/header.php';
$timer = time();

$id_empresa = $_GET["q"];
echo '<input type="hidden" id="id_empresa" class="form-control" value="' . $id_empresa . '" />';

$sesionClienteId = ($frontUserLoggedIn && isset($_SESSION['id_usuario'])) ? (int)$_SESSION['id_usuario'] : 0;
?>
<link rel="canonical" href="https://fulmuv.com/shop-checkout.php">

<style>
    @media (min-width: 992px) {
        .sticky-checkout-box {
            position: sticky;
            top: 100px;
        }
    }

    /* ── Login card ───────────────────────────────────── */
    .checkout-login-card {
        background: #fff;
        border: 1.5px solid rgba(0, 78, 96, 0.18);
        border-radius: 14px;
        padding: 18px 22px;
        margin-top: 14px;
        box-shadow: 0 4px 18px rgba(0, 78, 96, 0.09);
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .checkout-login-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #004E60, #016b84);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFDC2B;
        font-size: 18px;
        flex-shrink: 0;
    }

    .checkout-login-card .checkout-login-text {
        flex: 1;
        min-width: 0;
    }

    .checkout-login-card .checkout-login-title {
        font-size: 15px;
        font-weight: 700;
        color: #004E60;
        margin-bottom: 2px;
    }

    .checkout-login-card .checkout-login-sub {
        font-size: 12.5px;
        color: #6b7280;
        line-height: 1.4;
    }

    .checkout-login-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    /* ── Info card ─────────────────────────────────────── */
    .checkout-info-card {
        background: rgba(0, 78, 96, 0.06);
        border: 1.5px solid rgba(0, 78, 96, 0.16);
        border-left: 5px solid #004E60;
        border-radius: 14px;
        padding: 18px 22px 16px;
        margin-bottom: 28px;
    }

    .checkout-info-icon {
        width: 36px;
        height: 36px;
        background: #004E60;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFDC2B;
        font-size: 15px;
        flex-shrink: 0;
    }

    .checkout-info-card p {
        font-size: 13px;
        color: #1e3a44;
        line-height: 1.55;
        margin: 0;
    }

    .checkout-info-card p + p {
        margin-top: 8px;
    }

    /* ── Summary card (paso 2) ─────────────────────────── */
    .co-summary-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0, 78, 96, 0.12);
        border: 1px solid rgba(0, 78, 96, 0.13);
        background: #fff;
    }

    .co-card-title {
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        color: #004E60;
        padding: 12px 16px 10px;
        letter-spacing: .2px;
        border-bottom: 1px solid rgba(0,78,96,0.10);
    }

    /* ── Secciones ── */
    .co-sec-hd {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        background: #004E60;
    }
    .co-sec-hd--orange { background: #FF6D01; }
    .co-sec-hd i { font-size: 13px; color: #FFDC2B; flex-shrink: 0; }
    .co-sec-hd--orange i { color: #fff; }

    .co-sec-body {
        background: rgba(0,78,96,0.04);
        padding: 10px 14px;
    }
    .co-sec-body--orange { background: rgba(255,109,1,0.04); }

    .co-sec-note {
        font-size: 11px;
        color: #FF6D01;
        font-weight: 600;
        padding: 6px 14px 4px;
        background: rgba(255,109,1,0.04);
    }

    .co-sec-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
    }
    .co-sec-lbl { font-size: 12px; color: #4a5568; }
    .co-sec-val { font-size: 13px; font-weight: 700; color: #004E60; white-space: nowrap; }
    .co-sec-val--pending { font-size: 11px; color: #6b7280; font-style: italic; }

    .co-sec-gap { height: 6px; background: #f1f5f9; }

    /* ── Mini lista de productos ── */
    .co-mini-list {
        overflow-y: hidden;
        padding: 6px 14px;
        background: rgba(255,109,1,0.04);
    }
    .co-mini-list.is-expanded {
        max-height: 210px;
        overflow-y: auto;
    }
    .co-mini-list::-webkit-scrollbar { width: 4px; }
    .co-mini-list::-webkit-scrollbar-track { background: transparent; }
    .co-mini-list::-webkit-scrollbar-thumb { background: rgba(255,109,1,0.3); border-radius: 4px; }

    /* ── Toggle ver todos ── */
    .co-list-toggle {
        width: 100%;
        background: rgba(255,109,1,0.07);
        border: none;
        border-top: 1px solid rgba(255,109,1,0.14);
        border-bottom: 1px solid rgba(255,109,1,0.14);
        padding: 7px 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 11.5px;
        font-weight: 700;
        color: #FF6D01;
        cursor: pointer;
        transition: background .15s;
    }
    .co-list-toggle:hover { background: rgba(255,109,1,0.13); }
    .co-list-toggle i { font-size: 11px; transition: transform .25s; }
    .co-list-toggle.is-open i { transform: rotate(180deg); }

    .co-mini-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 6px 0;
        border-bottom: 1px solid rgba(255,109,1,0.10);
    }
    .co-mini-item:last-child { border-bottom: none; }
    .co-mini-img {
        width: 40px; height: 40px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid rgba(255,109,1,0.18);
        flex-shrink: 0;
    }
    .co-mini-info { flex: 1; min-width: 0; }
    .co-mini-name { font-size: 11px; font-weight: 400; color: #1e293b; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .co-mini-qty { font-size: 10px; color: #888; margin-top: 2px; }
    .co-mini-price { font-size: 12px; font-weight: 700; color: #004E60; white-space: nowrap; flex-shrink: 0; }
    .co-badge-iva { background: rgba(0,78,96,0.10); color: #004E60; font-size: 9px; font-weight: 700; border-radius: 20px; padding: 1px 6px; display: inline-block; margin-top: 2px; }

    /* ── Totales ── */
    .co-sec-totals {
        background: rgba(255,109,1,0.04);
        padding: 8px 14px 10px;
        border-top: 1px solid rgba(255,109,1,0.10);
    }
    .co-sec-totals .co-sec-row { padding: 4px 0; border-bottom: 1px solid rgba(255,109,1,0.08); }
    .co-sec-totals .co-sec-row:last-child { border-bottom: none; }

    .co-sec-row--savings {
        background: rgba(144, 255, 189, 0.18);
        border-radius: 6px;
        padding: 4px 6px;
        margin: 2px 0;
    }
    .co-sec-row--savings .co-sec-lbl { color: #00754a; }
    .co-sec-row--savings .co-sec-lbl i { color: #00754a; }
    .co-sec-row--savings .co-sec-val { color: #00754a; }

    .co-sec-row--total {
        background: #004E60;
        margin: 6px -14px -10px;
        padding: 8px 14px !important;
        border-radius: 0 0 0 0;
    }
    .co-sec-lbl-total { font-size: 13px; font-weight: 700; color: #90FFBD; }
    .co-sec-val-total { font-size: 16px; font-weight: 800; color: #FFDC2B; white-space: nowrap; }

    /* Stepper */
    .stepper {
        display: flex;
        gap: 0;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
        position: relative
    }

    .stepper .step {
        flex: 1;
        text-align: center;
        position: relative;
        color: #6c757d
    }

    .stepper .step .circle {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        background: #cfe2ff;
        color: #0d6efd;
        z-index: 1
    }

    .stepper .step .label {
        margin-top: .25rem;
        font-weight: 600
    }

    .stepper .step::after {
        content: "";
        position: absolute;
        top: 17px;
        left: calc(50% + 18px);
        right: -50%;
        height: 3px;
        background: #cfe2ff
    }

    .stepper .step:last-child::after {
        display: none
    }

    .stepper .step.is-active .circle,
    .stepper .step.is-complete .circle {
        background: #004E60;
        color: #fff
    }

    .stepper .step.is-active .label,
    .stepper .step.is-complete .label {
        color: #004E60
    }

    .stepper .step.is-complete::after {
        background: #004E60
    }

    .map-wrapper {
        position: relative;
        width: 100%
    }

    #mapaEntrega {
        width: 100%;
        height: 500px
    }

    .map-search {
        position: absolute;
        top: 20px;
        left: 20px;
        width: 180px;
        z-index: 1000
    }

    /* ── Botón ubicación en tiempo real ── */
    #btnMiUbicacion {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 1000;
        background: #fff;
        border: none;
        border-radius: 10px;
        padding: 8px 13px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.18);
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #004E60;
        cursor: pointer;
        transition: background .15s, box-shadow .15s;
        white-space: nowrap;
    }
    #btnMiUbicacion:hover { background: #f0f9fb; box-shadow: 0 4px 16px rgba(0,78,96,0.18); }
    #btnMiUbicacion:disabled { opacity: .7; cursor: not-allowed; }
    #btnMiUbicacion i { font-size: 15px; color: #004E60; }
    #btnMiUbicacion .loc-pulse {
        width: 10px; height: 10px;
        background: #004E60;
        border-radius: 50%;
        animation: locPulse .8s ease-in-out infinite alternate;
    }
    @keyframes locPulse {
        from { opacity: 1; transform: scale(1); }
        to   { opacity: .35; transform: scale(.55); }
    }

    /* Autocomplete */
    .autocomplete-wrap {
        position: relative
    }

    .autocomplete-list {
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        z-index: 1100;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .375rem;
        max-height: 260px;
        overflow: auto;
        display: none;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .06)
    }

    .autocomplete-item {
        padding: .5rem .75rem;
        cursor: pointer
    }

    .autocomplete-item:hover {
        background: #f8fafc
    }

    /* Caja de valor referencial */
    #envioCostoBox .badge {
        font-size: .75rem
    }

    .pago-bg {}

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

        /* contenedor general (reduce un poquito) */
        .container {
            font-size: 13px;
        }

        /* Títulos */
        h1.heading-2 {
            font-size: 20px !important;
            margin-bottom: 8px !important;
        }

        h4 {
            font-size: 16px !important;
        }

        h5 {
            font-size: 14px !important;
        }

        h6 {
            font-size: 13px !important;
        }

        /* textos auxiliares */
        .small,
        small {
            font-size: 12px !important;
        }

        p {
            font-size: 13px !important;
        }

        /* Stepper */
        .stepper .step .circle {
            width: 28px !important;
            height: 28px !important;
            font-size: 12px !important;
        }

        .stepper .step .label {
            font-size: 12px !important;
        }

        .stepper .step::after {
            top: 14px !important;
        }

        /* Formularios */
        .form-label {
            font-size: 12px !important;
            margin-bottom: 4px !important;
        }

        .form-control,
        .form-select,
        select.form-control {
            font-size: 13px !important;
            padding: 9px 10px !important;
        }

        ::placeholder {
            font-size: 13px !important;
        }

        /* Bloques informativos */
        .bg-light p,
        .bg-light,
        .alert,
        .form-text {
            font-size: 12px !important;
            line-height: 1.35 !important;
        }

        /* Resumen (lado derecho) */
        .cart-totals h4 {
            font-size: 16px !important;
        }

        .cart-totals h6 {
            font-size: 12.5px !important;
        }

        /* Checkbox texto */
        .form-check-label {
            font-size: 12px !important;
            line-height: 1.35 !important;
        }

        /* Botones */
        .btn {
            font-size: 13px !important;
            padding: 10px 12px !important;
        }

        #btnGenerarOrden {
            font-size: 14px !important;
            padding: 12px 14px !important;
            border-radius: 12px !important;
        }

        /* Modal pago envío (si lo abres en móvil) */
        .pago-card {
            padding: 18px !important;
        }

        .pago-card h3 {
            font-size: 16px !important;
        }

        .pago-card .sub {
            font-size: 12px !important;
        }

        .pago-item,
        .meta-line {
            font-size: 12px !important;
        }

        /* caja resumen */
        .cart-totals {
            padding: 14px !important;
            border-radius: 14px;
        }

        /* título */
        .cart-totals h4 {
            font-size: 16px !important;
            margin-bottom: 6px !important;
        }

        /* tabla resumen */
        .order_table.checkout .table {
            margin-bottom: 0 !important;
        }

        /* ===== 1) FILAS DE TOTALES (Subtotal/Ahorro/IVA/Total) ===== */
        /* Asumimos que tu JS pinta filas tipo: td(label) + td(valor) */
        .order_table.checkout tbody tr {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #eef2f7;
        }

        .order_table.checkout tbody tr:last-child {
            border-bottom: 0;
        }

        /* celdas */
        .order_table.checkout tbody tr td {
            padding: 0 !important;
            border: 0 !important;
            width: auto !important;
        }

        /* izquierda: etiqueta */
        .order_table.checkout tbody tr td:first-child {
            text-align: left !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #111 !important;
            line-height: 1.2;
        }

        /* derecha: valor */
        .order_table.checkout tbody tr td:last-child {
            text-align: right !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            white-space: nowrap;
            line-height: 1.2;
        }

        /* total final más notorio */
        .order_table.checkout tbody tr.total td:last-child {
            font-size: 15px !important;
            font-weight: 900 !important;
        }

        /* si tu template usa h6/h5 dentro de td */
        .order_table.checkout td h6,
        .order_table.checkout td h5,
        .order_table.checkout td strong {
            margin: 0 !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        /* ===== 2) LISTADO DE PRODUCTOS (imagen + nombre + xN + precio) ===== */
        /* Si tus productos se imprimen en las primeras filas del tbody, compactamos */
        .order_table.checkout .product-thumbnail img {
            width: 38px !important;
            height: 38px !important;
            object-fit: cover !important;
            border-radius: 10px !important;
        }

        .order_table.checkout .product-name,
        .order_table.checkout .product-name a {
            font-size: 12px !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
        }

        .order_table.checkout .product-quantity,
        .order_table.checkout .product-quantity span {
            font-size: 11px !important;
            opacity: .85;
        }

        .order_table.checkout .product-price,
        .order_table.checkout .product-price span {
            font-size: 12px !important;
            font-weight: 800 !important;
            white-space: nowrap;
        }

        /* ===== 3) TEXTO + CHECK (condiciones) compacto ===== */
        .cart-totals .form-check-label {
            font-size: 12px !important;
            line-height: 1.35 !important;
        }

        .cart-totals .form-check-label h6,
        .cart-totals .form-check-label p {
            margin: 6px 0 0 !important;
        }

        .cart-totals .form-check-label a {
            font-size: 12px !important;
        }

        /* botón */
        #btnGenerarOrden {
            border-radius: 12px;
            font-weight: 800;
            padding: 12px 14px;
        }
    }
</style>

<div class="container mb-80 mt-50">

    <!-- Título + Info card -->
    <div class="row mb-2">
        <div class="col-12">
            <h1 class="heading-2 mb-20">Verificar</h1>
            <div class="checkout-info-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="checkout-info-icon"><i class="fi fi-rs-info"></i></div>
                    <div>
                        <ul style="font-size:11px;color:#4a5568;line-height:1.7;margin:0;padding-left:16px;">
                            <li>El valor de los productos se paga directamente con cada proveedor.</li>
                            <li>FULMUV actúa como intermediario comercial y facilita la gestión logística de entrega a través de su socio autorizado, Grupo Entregas.</li>
                            <li>El pago realizado a FULMUV corresponde exclusivamente al servicio de envío a domicilio y cobertura logística.</li>
                            <li>Antes de confirmar tu pedido, verifica con el proveedor disponibilidad, características del producto y aprobación de tu compra.</li>
                            <li>Para agilizar tu entrega, te recomendamos mantener contacto con tu proveedor y solicitar la validación de tu pedido desde su sistema.</li>
                        </ul>
                    </div>
                </div>
                <span id="totalCarritoShop" class="d-none"></span>
            </div>

            <?php if (!$frontUserLoggedIn): ?>
            <div class="checkout-login-card">
                <div class="checkout-login-icon">
                    <i class="fi fi-rs-lock"></i>
                </div>
                <div class="checkout-login-text">
                    <div class="checkout-login-title">Inicie sesión o cree su cuenta</div>
                    <div class="checkout-login-sub">Para continuar con el proceso de compra necesita una sesión activa en FULMUV.</div>
                </div>
                <div class="checkout-login-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalElegirRol">
                        <i class="fi fi-rs-sign-in me-1"></i> Iniciar sesión / Registrarse
                    </button>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Stepper compartido -->
    <div class="row mb-3">
        <div class="col-12 col-lg-8">
            <div class="stepper">
                <div class="step is-active" id="step1Head">
                    <div class="circle">1</div>
                    <div class="label">Identificación</div>
                </div>
                <div class="step" id="step2Head">
                    <div class="circle">2</div>
                    <div class="label">Envío</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna formulario: col-12 en paso 1, col-lg-8 en paso 2 (cambia por JS) -->
        <div class="col-12" id="checkoutFormCol">

            <!-- PASO 1 -->
            <div id="step1" class="card border-0 mb-4">
                <div class="card-body p-0">
                    <h4 class="mb-2">Identificación</h4>
                    <p class="text-muted small mb-3">Solicitamos únicamente la información esencial para la finalización de la compra.</p>

                    <h6 class="text-uppercase text-muted mb-2">Datos para Facturación de envío a domicilio</h6>
                    <div class="row g-3">
                        <div class="col-lg-6"><input type="text" required id="fact_nombre" class="form-control" placeholder="Nombre completo o razón social *"></div>
                        <div class="col-lg-6">
                            <select class="form-control h-100" required id="tipo_identificacion">
                                <option value="">Tipo de identificación *</option>
                                <option value="cedula">Cédula</option>
                                <option value="ruc">RUC</option>
                                <option value="pasaporte">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-lg-6"><input type="text" required id="identificacion" class="form-control" placeholder="Número de identificación *"></div>
                        <div class="col-lg-6"><input type="email" required id="correo" class="form-control" placeholder="Correo electrónico *"></div>
                        <div class="col-lg-6"><input type="text" required id="telefono_fact" class="form-control" placeholder="Número de teléfono *"></div>
                        <div class="col-lg-6"><input type="text" required id="direccion_fiscal" class="form-control" placeholder="Dirección fiscal (incluye provincia, cantón, parroquia)"></div>
                        <div class="col-lg-6">
                            <select class="form-control h-100" required id="forma_pago">
                                <option value="">Forma de pago *</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="deposito">Depósito</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 d-grid d-md-flex">
                        <button type="button" id="btnToStep2" class="btn btn-primary ms-md-auto" <?php echo !$frontUserLoggedIn ? 'disabled title="Debe iniciar sesión para continuar"' : ''; ?>>Ir para el envío</button>
                    </div>
                    <div id="s1Msg" class="text-danger small mt-2 d-none">Completa los campos requeridos para continuar.</div>
                </div>
            </div>

            <!-- PASO 2 -->
            <div id="step2" class="card border-0 d-none">
                <div class="card-body p-0">
                    <h4 class="mb-3">Envío</h4>

                    <!-- Toggle envío / recoger -->
                    <div class="btn-group w-100 mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary w-50 active" id="btnEnvio">Enviar<br><small class="text-muted">a la dirección</small></button>
                        <button type="button" class="btn btn-outline-primary w-50" id="btnPickup">Recoger<br><small class="text-muted">en la tienda</small></button>
                    </div>

                    <!-- Envío a domicilio -->
                    <div id="envioDomicilio">
                        <div class="row g-3">
                            <div class="col-lg-12"><input type="text" required id="receptor_nombre" class="form-control" placeholder="Nombre de quien recibe el paquete *"></div>
                            <div class="col-lg-6"><input type="text" required id="receptor_cedula" class="form-control" placeholder="Cédula o número de identidad *"></div>
                            <div class="col-lg-6"><input type="text" required id="telefono_receptor" class="form-control" placeholder="Teléfono de contacto del receptor *"></div>

                            <!-- Párrafo de ayuda -->
                            <div class="col-lg-12 bg-light p-3 rounded">
                                <p class="mb-0">
                                    Para localizar tu dirección con precisión, escribe en los campos <strong>Provincia</strong>, <strong>Cantón</strong> y <strong>Sector</strong>.
                                    Al ingresar <strong>al menos 2 caracteres</strong> en cada uno aparecerá una lista de sugerencias para seleccionar la opción correcta.
                                    Es importante completar también el <strong>Sector</strong> (barrio/recinto/parroquia) y, de ser posible, añadir referencias cercanas;
                                    esto nos permite <em>identificar el punto exacto de entrega</em>, evitar reintentos, calcular el costo referencial de envío y garantizar que tu pedido llegue sin demoras.
                                </p>
                            </div>

                            <!-- Provincia -->
                            <div class="col-lg-4">
                                <label class="form-label" for="selectProvincia">Provincia</label>
                                <select id="selectProvincia" class="form-control" required>
                                    <option value="">Seleccione una provincia</option>
                                </select>
                            </div>

                            <!-- Cantón -->
                            <div class="col-lg-4">
                                <label class="form-label" for="selectCanton">Cantón</label>
                                <select id="selectCanton" class="form-control" required disabled>
                                    <option value="">Seleccione un cantón</option>
                                </select>
                                <div id="cantonAviso" class="form-text text-warning d-none" style="margin-top:.35rem;"></div>
                            </div>

                            <!-- Sector (ya estaba con select) -->
                            <div class="col-lg-4">
                                <label class="form-label" for="selectSector">Sector</label>
                                <select id="selectSector" class="form-control" required disabled>
                                    <option value="">Seleccione un sector</option>
                                </select>
                            </div>

                            <!-- Caja de valor referencial (dinámica) -->
                            <div id="envioCostoBox" class="border rounded bg-light px-3 py-2 mt-2 d-none">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-coin me-2"></i>
                                    <strong class="me-2">Valor referencial por la entrega</strong>
                                    <span id="envioBadge" class="badge bg-secondary"></span>
                                </div>
                                <div id="envioCostoContenido" class="mt-2 small"></div>
                            </div>

                            <div class="col-lg-8 d-none">
                                <label class="form-label" for="selectCiudadesAgencia">Elige tu agencia más cercana</label>
                                <select id="selectCiudadesAgencia" class="form-control" required></select>
                            </div>
                            <div class="col-lg-12">
                                <label class="form-label" for="horario_entrega">Elige tu horario de disponibilidad</label>
                                <select class="form-control" id="horario_entrega" required></select>
                            </div>

                            <div class="map-hint alert alert-warning py-2 px-3 mb-0" id="mapHint" role="alert">
                                <i class="bi bi-geo-alt me-1"></i>
                                Para mayor precisión, <strong>haz clic en el mapa</strong> (o arrastra el marcador) para confirmar tu ubicación exacta.
                            </div>
                            <div class="col-lg-12">
                                <div class="map-wrapper position-relative">
                                    <div id="mapaEntrega"></div>
                                    <div class="map-search">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input id="buscarDireccion" class="form-control form-control-sm"
                                                style="width:clamp(200px,39vw,400px);margin-top:10px;background:#fff;height:40px"
                                                placeholder="Buscar..." />
                                        </div>
                                        <ul id="sugerenciasDirecciones" class="list-group list-group-flush shadow-sm mt-1" style="display:none;"></ul>
                                    </div>
                                    <button type="button" id="btnMiUbicacion" title="Usar mi ubicación actual">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span id="btnMiUbicacionTxt">Mi ubicación</span>
                                    </button>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <label for="direccion_mapa" class="form-label">Dirección de Entrega</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="direccion_mapa" placeholder="Ej. Av. 9 de Octubre y Boyacá" required disabled>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalMapa">
                                        <i class="fi-rs-marker"></i>
                                    </button>
                                </div>


                            </div>

                            <div class="col-lg-8"><input type="text" id="referencia" class="form-control" placeholder="Punto de referencia"></div>
                            <div class="col-lg-4"><input type="text" id="codigo_postal" class="form-control" placeholder="Código postal (opcional)"></div>
                            <div class="col-lg-12"><textarea rows="3" id="observaciones_entrega" class="form-control" placeholder="Observaciones adicionales"></textarea></div>
                        </div>
                    </div>

                    <!-- Pickup -->
                    <div id="pickupTienda" class="d-none">
                        <div class="alert d-flex gap-2 align-items-start mb-3 py-2 px-3" style="background:rgba(0,78,96,0.06);border:1px solid rgba(0,78,96,0.18);border-radius:10px;">
                            <i class="fi fi-rs-info" style="color:#004E60;font-size:15px;flex-shrink:0;margin-top:2px;"></i>
                            <div style="font-size:11.5px;color:#374151;line-height:1.65;">
                                Si eliges retirar en tienda, notificaremos a las empresas seleccionadas que te acercarás directamente a gestionar tu compra y retirar tus productos.<br>
                                <span style="color:#FF6D01;font-weight:600;">No aplicará el servicio de envío a domicilio gestionado por FULMUV.</span>
                            </div>
                        </div>
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex">
                                <div class="me-2 text-primary"><i class="fi-rs-marker"></i></div>
                                <div class="w-100">
                                    <strong>Selecciona las empresas donde recogerás tu pedido</strong>
                                    <div class="text-muted small mt-1">
                                        Al marcar una empresa, aceptas recoger todos los productos asociados a esa empresa en su local.
                                    </div>
                                    <div id="pickupMensajeRegla" class="alert alert-warning bg-transparent border border-warning mt-2 d-none"></div>
                                    <div id="pickupListaContainer" class="d-none">
                                        <div class="list-group" id="pickupLista"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-12"><input type="text" id="pickup_responsable" class="form-control" placeholder="Responsable que recogerá el pedido *" required></div>
                            <div class="col-lg-12 text-muted small">Si el pedido es retirado por un tercero, adjunta una autorización escrita con nombre e identificación de quien retira.</div>
                        </div>
                    </div>

                    <div class="mt-3 d-grid d-md-flex">
                        <button type="button" id="btnBackToStep1" class="btn btn-outline-secondary">Volver</button>
                    </div>

                    <div id="s2Msg" class="text-danger small mt-2 d-none">Completa los campos requeridos del envío.</div>
                </div>
            </div>
        </div>

        <!-- Resumen (visible solo en paso 2) -->
        <div class="col-lg-4 d-none" id="checkoutSummaryCol">
            <div class="co-summary-card sticky-checkout-box">

                <!-- Título centrado -->
                <div class="co-card-title">Detalles de tu pedido a proveedores</div>

                <!-- Sección 1: Envío FULMUV -->
                <div class="co-sec-hd">
                    <i class="fi fi-rs-truck-side"></i>
                    Gestión de envío a domicilio por FULMUV
                </div>
                <div class="co-sec-body">
                    <div class="co-sec-row">
                        <span class="co-sec-lbl">Valor de envío</span>
                        <span class="co-sec-val--pending">Se calcula en el checkout</span>
                    </div>
                </div>

                <div class="co-sec-gap"></div>

                <!-- Sección 2: Pago a proveedores -->
                <div class="co-sec-hd co-sec-hd--orange">
                    <i class="fi fi-rs-hand-holding-usd"></i>
                    Pago a proveedores
                </div>
                <div class="co-sec-note">Gestiónalo directamente con los proveedores.</div>

                <!-- Mini lista de productos -->
                <div id="coProductList" class="co-mini-list"></div>
                <div id="coListToggleArea"></div>

                <!-- Totales -->
                <div class="co-sec-totals">
                    <div class="co-sec-row">
                        <span class="co-sec-lbl">Valor de productos</span>
                        <span class="co-sec-val" id="coValorProductos">—</span>
                    </div>
                    <div class="co-sec-row">
                        <span class="co-sec-lbl">IVA (15%)</span>
                        <span class="co-sec-val" id="coIVA">—</span>
                    </div>
                    <div class="co-sec-row co-sec-row--savings" id="coAhorrasteRow" style="display:none;">
                        <span class="co-sec-lbl"><i class="fi fi-rs-label me-1"></i>Ahorraste</span>
                        <span class="co-sec-val" id="coAhorraste">—</span>
                    </div>
                    <div class="co-sec-row co-sec-row--total">
                        <span class="co-sec-lbl-total">Total referencial</span>
                        <span class="co-sec-val-total" id="coTotalReferencial">—</span>
                    </div>
                </div>

                <!-- TyC + botón -->
                <div class="px-3 pb-3 pt-3">
                    <div class="form-check">
                        <input class="form-check-input mt-1" type="checkbox" id="aceptoTyC">
                        <label class="form-check-label" for="aceptoTyC" style="font-size:11px;line-height:1.5;color:#4a5568;">
                            Declaro que he leído y acepto las Condiciones de Envíos y Logística FULMUV – GRUPO ENTREGAS. Entiendo que FULMUV no es transportista ni responsable por tiempos, condiciones o resultados del servicio. Acepto las tarifas publicadas (con IVA incluido) y que el pago del envío se realiza mediante depósito o transferencia bancaria a la cuenta de FULMUV. La cobertura de seguro de envío alcanza el 100%, sin IVA; el IVA no está cubierto. Confirmo que mi envío cuenta con seguro solo si solicité y verifiqué la emisión de la factura por parte del proveedor, y fue declarado el valor pagado. Los envíos se sujetan a las políticas de GRUPO ENTREGAS. Todo reclamo deberá enviarse a servicios@fulmuv.com para su gestión con el proveedor logístico.
                            <a href="documentos/5_NW_Condiciones_Servicio_Logístico_FULMUV.pdf" target="_blank" class="d-block mt-1" style="font-size:11px;color:#004E60;">
                                Ver Condiciones de Envíos y Logística Clientes
                            </a>
                        </label>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary w-100" id="btnGenerarOrden" onclick="generarOrden()" disabled>
                            Generar orden
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modales mapa -->
<div class="modal fade" id="modalMapa" tabindex="-1" aria-labelledby="modalMapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMapaLabel">Selecciona una ubicación</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" id="guardarUbicacion">Guardar dirección</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMapaPickup" tabindex="-1" aria-labelledby="modalMapaPickupLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title mb-0" id="modalMapaPickupLabel">Ubicación</h6>
                    <div class="small text-muted" id="modalMapaPickupDireccion"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div id="mapaPickup" style="width:100%;height:420px;"></div>
            </div>
            <div class="modal-footer py-2"><button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
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


<?php include 'includes/footer.php'; ?>
<script>
    const SESION_CLIENTE_ID = <?php echo $sesionClienteId; ?>;
</script>
<script src="js/shop-checkout.js?v=<?php echo $timer; ?>"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&callback=initMap" async defer></script>

<script>
   

    // con jQuery
    $(function() {
        const $chk = $('#aceptoTyC');
        const $btn = $('#btnGenerarOrden');

        function syncBtn() {
            $btn.prop('disabled', !$chk.is(':checked'));
        }

        $chk.on('change', syncBtn);
        syncBtn(); // estado inicial
    });
</script>
