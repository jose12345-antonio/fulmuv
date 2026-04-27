<?php
include 'includes/header.php';

// Capturamos parámetros de la URL enviados desde la App
$id_empresa = isset($_GET["q"]) ? $_GET["q"] : '';
$id_usuario = isset($_GET["id_usuario"]) ? $_GET["id_usuario"] : 1;

echo '<input type="hidden" id="id_empresa" value="' . $id_empresa . '" />';
echo '<input type="hidden" id="id_usuario_session" value="' . $id_usuario . '" />';
?>
<link rel="canonical" href="https://fulmuv.com/shop-checkout.php">

<style>
    :root {
        --checkout-bg: #f5f7fb;
        --checkout-surface: #ffffff;
        --checkout-surface-soft: #f8fafc;
        --checkout-border: rgba(15, 23, 42, 0.08);
        --checkout-text-primary: #0f172a;
        --checkout-text-secondary: #64748b;
        --checkout-accent: #004E60;
        --checkout-accent-soft: rgba(0, 78, 96, 0.10);
        --checkout-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
    }

    body {
        background-color: var(--checkout-bg);
        font-family: 'Quicksand', sans-serif;
    }

    .heading-2 {
        font-weight: 900;
        color: var(--checkout-accent);
        letter-spacing: -0.02em;
    }

    .checkout-card {
        background:
            radial-gradient(circle at top right, rgba(0, 78, 96, 0.08), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 24px;
        padding: 24px;
        box-shadow: var(--checkout-shadow);
        border: 1px solid var(--checkout-border);
        margin-bottom: 20px;
    }

    .checkout-shell {
        padding-top: 18px;
        padding-bottom: 36px;
    }

    .checkout-hero {
        background:
            radial-gradient(circle at top right, rgba(0, 78, 96, 0.14), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid var(--checkout-border);
        border-radius: 28px;
        padding: 24px 22px;
        box-shadow: var(--checkout-shadow);
        margin-bottom: 18px;
    }

    .checkout-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: var(--checkout-accent-soft);
        color: var(--checkout-accent);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .checkout-hero-copy {
        max-width: 620px;
    }

    .checkout-hero-text {
        color: var(--checkout-text-secondary);
        font-size: 14px;
        line-height: 1.65;
        margin: 8px 0 0;
    }

    .modern-stepper {
        display: flex;
        justify-content: flex-start;
        gap: 14px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .m-step {
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0.55;
        transition: 0.3s;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid transparent;
    }

    .m-step.active {
        opacity: 1;
        transform: translateY(-1px);
        background: #fff;
        border-color: rgba(0, 78, 96, 0.14);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    }

    .m-step .num {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--checkout-accent);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
    }

    .m-step .txt {
        font-weight: 800;
        color: var(--checkout-accent);
        font-size: 14px;
    }

    .section-title-checkout {
        font-size: 22px;
        font-weight: 900;
        color: var(--checkout-accent);
        margin-bottom: 6px;
    }

    .section-copy-checkout {
        color: var(--checkout-text-secondary);
        font-size: 13px;
        line-height: 1.6;
        margin-bottom: 18px;
    }

    /* Inputs Estilo App */
    .form-label {
        font-weight: 700;
        color: var(--checkout-accent);
        font-size: 13px;
        margin-bottom: 5px;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        background-color: var(--checkout-surface-soft);
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--checkout-accent);
        box-shadow: 0 0 0 3px rgba(0, 78, 96, 0.1);
        background: #fff;
    }

    .toggle-delivery {
        background: #eef2f7;
        border-radius: 18px;
        padding: 6px;
        gap: 8px;
    }

    .toggle-delivery .btn {
        border-radius: 14px !important;
        min-height: 52px;
        font-weight: 800;
        border-width: 1px;
    }

    .toggle-delivery .btn.active,
    .toggle-delivery .btn.btn-primary,
    .toggle-delivery .btn[aria-pressed="true"] {
        background: linear-gradient(135deg, var(--checkout-accent) 0%, #0f766e 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 14px 28px rgba(0, 78, 96, 0.18);
    }

    .delivery-note {
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 12px 14px;
        color: var(--checkout-text-secondary);
        font-size: 12px;
        line-height: 1.6;
        margin-bottom: 14px;
    }

    .map-wrapper {
        border-radius: 22px !important;
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
    }

    .map-search-container .input-group {
        max-width: min(95%, 480px) !important;
        border-radius: 14px;
        overflow: hidden;
    }

    .map-search-container .input-group-text,
    .map-search-container .form-control {
        background: rgba(255, 255, 255, 0.96) !important;
    }

    /* Botón Principal */
    .btn-generate {
        background: linear-gradient(135deg, var(--checkout-accent) 0%, #0f766e 100%);
        color: #fff;
        border-radius: 16px;
        padding: 16px;
        font-weight: 900;
        font-size: 16px;
        border: none;
        width: 100%;
        transition: 0.3s;
        box-shadow: 0 16px 30px rgba(0, 78, 96, 0.2);
    }

    .btn-generate:disabled {
        background: #cbd5e0;
        box-shadow: none;
    }

    .btn-step-next {
        background: linear-gradient(135deg, var(--checkout-accent) 0%, #0f766e 100%) !important;
        border-radius: 14px !important;
        min-height: 52px;
        border: 0 !important;
        box-shadow: 0 16px 30px rgba(0, 78, 96, 0.2);
    }

    .btn-step-back {
        text-decoration: none !important;
        font-weight: 800 !important;
        color: var(--checkout-text-secondary) !important;
        padding: 0 !important;
    }

    .sticky-checkout-box {
        position: sticky;
        top: 95px;
    }

    .summary-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .summary-title {
        font-size: 22px;
        font-weight: 900;
        color: var(--checkout-text-primary);
        margin: 0;
    }

    .summary-copy {
        color: var(--checkout-text-secondary);
        font-size: 13px;
        line-height: 1.6;
        margin: 6px 0 0;
    }

    .summary-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: var(--checkout-accent-soft);
        color: var(--checkout-accent);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .order-summary-table {
        background: rgba(255, 255, 255, 0.84);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 6px 10px;
    }

    .order-summary-table table {
        table-layout: fixed;
    }

    .order-summary-table td {
        vertical-align: middle;
        padding-top: 12px;
        padding-bottom: 12px;
        border-color: #eef2f7;
    }

    .summary-product-thumb {
        width: 74px;
    }

    .summary-product-name {
        color: var(--checkout-text-primary);
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
    }

    .summary-product-qty {
        width: 60px;
        color: var(--checkout-text-secondary);
        font-size: 13px;
        font-weight: 700;
        text-align: center;
        white-space: nowrap;
    }

    .summary-money-cell {
        width: 116px;
        text-align: right;
        white-space: nowrap;
    }

    .summary-total-row td {
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .summary-total-label {
        color: var(--checkout-text-primary);
        font-size: 15px;
        font-weight: 800;
    }

    .summary-total-value {
        text-align: right;
        white-space: nowrap;
        font-size: 15px;
        font-weight: 800;
    }

    .legal-checkout-box {
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 14px 16px;
    }

    .badge-step {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 800;
        color: var(--checkout-accent);
        background: var(--checkout-accent-soft);
        padding: 8px 12px;
        border-radius: 999px;
        margin-bottom: 12px;
    }

    @media (max-width: 576px) {
        .checkout-card {
            padding: 18px;
        }

        .modern-stepper {
            gap: 10px;
        }

        .checkout-hero {
            padding: 20px 18px;
            border-radius: 24px;
        }

        .sticky-checkout-box {
            position: static;
        }

        .summary-heading {
            flex-direction: column;
        }
    }
</style>

<div class="container checkout-shell">
    <div class="checkout-hero">
        <div class="checkout-hero-copy">
            <span class="checkout-kicker"><i class="fi-rs-shopping-bag"></i> Checkout FULMUV</span>
            <h2 class="heading-2 mb-2">Completa tu orden</h2>
            <p class="checkout-hero-text">Verifica tu facturación, configura la entrega y revisa el resumen antes de generar la orden de compra.</p>
        </div>
    </div>

    <div class="modern-stepper">
        <div class="m-step active" id="m-step-1">
            <div class="num">1</div>
            <div class="txt">Facturación</div>
        </div>
        <div class="m-step" id="m-step-2">
            <div class="num">2</div>
            <div class="txt">Envío</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">

            <div id="step1_panel" class="checkout-card">
                <span class="badge-step"><i class="fi-rs-user"></i>Paso 1</span>
                <h4 class="section-title-checkout">Identificación</h4>
                <p class="section-copy-checkout">Verifica tus datos para la factura. Los cargamos automáticamente para que el proceso sea más rápido.</p>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre o Razón Social *</label>
                        <input type="text" id="fact_nombre" class="form-control" placeholder="Cargando...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Identificación</label>
                        <select class="form-select" id="tipo_identificacion">
                            <option value="cedula" selected>Cédula</option>
                            <option value="ruc">RUC</option>
                            <option value="pasaporte">Pasaporte</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Número Identificación *</label>
                        <input type="text" id="identificacion" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" id="correo" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono *</label>
                        <input type="text" id="telefono_fact" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Dirección Fiscal</label>
                        <input type="text" id="direccion_fiscal" class="form-control" placeholder="Provincia, Cantón, Calle...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Forma de Pago</label>
                        <select class="form-select" id="forma_pago">
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="deposito">Depósito</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnToStep2" class="btn px-5 py-3 fw-bold text-white btn-step-next">
                        CONTINUAR AL ENVÍO <i class="fi-rs-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <div id="step2_panel" class="checkout-card d-none">
                <span class="badge-step"><i class="fi-rs-truck-side"></i>Paso 2</span>
                <h4 class="section-title-checkout">Detalles de entrega</h4>
                <p class="section-copy-checkout">Elige cómo recibirás tu pedido y confirma la información del receptor y la ubicación.</p>

                <div class="btn-group w-100 mb-4 toggle-delivery">
                    <button type="button" class="btn btn-outline-primary active py-3" id="btnEnvio"><strong>ENVÍO DOMICILIO</strong></button>
                    <button type="button" class="btn btn-outline-primary py-3" id="btnPickup"><strong>RECOGER TIENDA</strong></button>
                </div>

                <div class="delivery-note">
                    Completa los datos del receptor y usa el mapa para confirmar la dirección exacta si eliges entrega a domicilio.
                </div>

                <div id="envioDomicilio" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre de quien recibe *</label>
                        <input type="text" id="receptor_nombre" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cédula Receptor *</label>
                        <input type="text" id="receptor_cedula" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono Receptor *</label>
                        <input type="text" id="telefono_receptor" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Provincia</label>
                        <select id="selectProvincia" class="form-select"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cantón</label>
                        <select id="selectCanton" class="form-select" disabled></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sector</label>
                        <select id="selectSector" class="form-select" disabled></select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Horario de disponibilidad *</label>
                        <select class="form-select" id="horario_entrega" required></select>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="map-wrapper position-relative" style="border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0;">
                            <div id="mapaEntrega" style="height: 400px; width: 100%;"></div>

                            <div class="map-search-container" style="position: absolute; top: 65px; left: 10px; right: 10px; z-index: 5;">
                                <div class="input-group shadow-sm" style="max-width: 95%;">
                                    <span class="input-group-text bg-white border-0"><i class="fi-rs-search"></i></span>
                                    <input id="buscarDireccion" type="text" class="form-control border-0"
                                        placeholder="Escribe tu calle o sector..."
                                        style="height: 45px; box-shadow: none; font-size: 14px;">
                                </div>
                                <ul id="sugerenciasDirecciones" class="list-group shadow-sm mt-1" style="display:none; max-width: 95%;"></ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Dirección Exacta (Confirmada en mapa)</label>
                        <input type="text" id="direccion_mapa" class="form-control" readonly style="background-color: #f1f5f9;">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Punto de referencia *</label>
                        <input type="text" id="referencia" class="form-control" placeholder="Ej: Junto a la farmacia, casa color verde...">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Código Postal *</label>
                        <input type="text" id="codigo_postal" class="form-control" placeholder="000000">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observaciones adicionales</label>
                        <textarea id="observaciones_entrega" class="form-control" rows="2" placeholder="Notas para el repartidor..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" id="btnBackToStep1" class="btn btn-link btn-step-back">
                        <i class="fi-rs-arrow-left small me-1"></i> VOLVER ATRÁS
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="checkout-card sticky-checkout-box">
                <div class="summary-heading">
                    <div>
                        <h4 class="summary-title">Resumen de orden</h4>
                        <p class="summary-copy">Confirma productos, logística y condiciones antes de generar tu orden.</p>
                    </div>
                </div>

                <div class="table-responsive order-summary-table">
                    <table class="table no-border mb-0">
                        <tbody id="listaTotalProductosCheckOut"></tbody>
                    </table>
                </div>

                <hr class="my-4">

                <div class="form-check mb-4 legal-checkout-box">
                    <input class="form-check-input" type="checkbox" id="aceptoTyC">
                    <label class="form-check-label small text-secondary" for="aceptoTyC" style="line-height:1.4">
                        Acepto las <a href="documentos/5_NW_Condiciones_Servicio_Logístico_FULMUV.pdf" target="_blank" class="fw-bold text-dark text-decoration-underline">Condiciones de Envíos y Logística FULMUV - GRUPO ENTREGAS.</a> Entiendo que FULMUV no es transportista ni responsable por tiempos, condiciones o resultados del servicio. Acepto las tarifas publicadas (con IVA incluido) y que el pago del envío se realiza mediante depósito o transferencia bancaria a la cuenta de FULMUV. La cobertura de seguro de envío alcanza el 100%, sin IVA; el IVA no está cubierto. Confirmo que mi envío cuenta con seguro solo si solicité y verifiqué la emisión de la factura por parte del proveedor, y fue declarado el valor pagado. Los envíos se sujetan a las políticas de GRUPO ENTREGAS. Todo reclamo deberá enviarse a servicios@fulmuv.com para su gestión con el proveedor logístico.
                    </label>
                </div>

                <button type="button" class="btn-generate" id="btnGenerarOrden" onclick="generarOrden()" disabled>
                    GENERAR ORDEN DE COMPRA
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/shop-checkout.js?v=2.7"></script>
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


    const idUsuario = $("#id_usuario_session").val();
    $(document).ready(function() {
        // ✅ LLAMADA A TU API getClienteById
        if (idUsuario) {
            $.post("../api/v1/fulmuv/cliente/getClienteById", {
                id_usuario: idUsuario
            }, function(res) {
                if (!res.error && res.data) {
                    const c = res.data;
                    // Autocompletado Paso 1
                    $("#fact_nombre").val(c.nombres || '');
                    $("#identificacion").val(c.cedula || '');
                    $("#correo").val(c.correo || '');
                    $("#telefono_fact").val(c.telefono || '');
                    $("#direccion_fiscal").val(c.direccion || '');

                    // Autocompletado Paso 2 (Receptor por defecto)
                    $("#receptor_nombre").val(c.nombres || '');
                    $("#receptor_cedula").val(c.cedula || '');
                    $("#telefono_receptor").val(c.telefono || '');
                }
            }, 'json');
        }
    });

    // Navegación entre pasos
</script>