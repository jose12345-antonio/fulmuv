<?php
include 'includes/header.php';
// ID de empresa opcional para filtrar si es necesario
$id_empresa = isset($_GET["q"]) ? $_GET["q"] : '';
echo '<input type="hidden" id="id_empresa" value="' . $id_empresa . '" />';
?>

<style>
    :root {
        --cart-bg: #f5f7fb;
        --cart-surface: #ffffff;
        --cart-surface-soft: #f8fafc;
        --cart-border: rgba(15, 23, 42, 0.08);
        --cart-text-primary: #0f172a;
        --cart-text-secondary: #64748b;
        --cart-accent: #004E60;
        --cart-accent-dark: #003946;
        --cart-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
    }

    body {
        background-color: var(--cart-bg);
        font-family: 'Quicksand', sans-serif;
    }

    .heading-2 {
        font-weight: 900;
        color: var(--cart-accent);
        letter-spacing: -0.02em;
    }

    .cart-shell {
        padding-top: 18px;
        padding-bottom: 36px;
    }

    .cart-hero {
        background:
            radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid var(--cart-border);
        border-radius: 28px;
        padding: 24px 22px;
        box-shadow: var(--cart-shadow);
        margin-bottom: 20px;
    }

    .cart-hero-copy {
        max-width: 580px;
    }

    .cart-hero-note {
        color: var(--cart-text-secondary);
        font-size: 14px;
        line-height: 1.6;
        margin: 8px 0 0;
    }

    .cart-mini-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(0, 78, 96, 0.1);
        color: var(--cart-accent);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
    }

    /* Tarjetas de Producto */
    .cart-item-card {
        background: var(--cart-surface);
        border-radius: 24px;
        padding: 16px;
        margin-bottom: 15px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07);
        border: 1px solid var(--cart-border);
    }

    .cart-item-img {
        width: 85px;
        height: 85px;
        border-radius: 18px;
        object-fit: cover;
        background: #f1f5f9;
    }

    .product-name {
        font-size: 14px;
        font-weight: 800;
        color: var(--cart-text-primary);
        line-height: 1.2;
        text-transform: uppercase;
    }

    /* Controles de Cantidad */
    .qty-container {
        display: flex;
        align-items: center;
        background: #f1f5f9;
        border-radius: 10px;
        padding: 2px;
    }

    .qty-btn {
        width: 26px;
        height: 26px;
        border: none;
        background: #fff;
        border-radius: 7px;
        font-weight: bold;
        color: var(--cart-accent);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .detail-qty {
        min-height: 32px !important;
        padding: 2px 4px !important;
        border-radius: 10px !important;
    }

    .detail-qty a {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .detail-qty .qty-val,
    .qty-val {
        min-width: 28px;
        font-size: 12px !important;
        line-height: 1;
    }

    .btn-eliminar-producto,
    .btn-eliminar-producto i,
    .btn-remove {
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }

    .btn-remove,
    .btn-remove:hover,
    .btn-remove:focus {
        padding: 0 !important;
        color: #ef4444 !important;
    }

    /* Resumen de Totales Sticky */
    .sticky-summary {
        background:
            radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 28px;
        box-shadow: var(--cart-shadow);
        padding: 24px 22px;
        position: sticky;
        top: 95px;
        z-index: 10;
        border: 1px solid var(--cart-border);
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .total-label {
        color: var(--cart-text-secondary);
        font-weight: 600;
        font-size: 14px;
    }

    .savings-value {
        color: #e53e3e;
        font-weight: 800;
    }

    .summary-note-box {
        font-size: 12px;
        line-height: 1.65;
        color: var(--cart-text-secondary);
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid #e2e8f0;
        margin-bottom: 14px;
    }

    .summary-check {
        background: rgba(255, 255, 255, 0.82);
        padding: 14px 16px;
        border-radius: 18px;
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
        font-size: 12px;
    }

    .btn-checkout {
        background: linear-gradient(135deg, var(--cart-accent) 0%, #0f766e 100%);
        color: #fff;
        border-radius: 16px;
        height: 55px;
        font-weight: 900;
        border: none;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 16px 30px rgba(0, 78, 96, 0.22);
    }

    .btn-checkout:hover,
    .btn-checkout:focus {
        color: #fff;
        background: linear-gradient(135deg, var(--cart-accent-dark) 0%, #0b5f59 100%);
    }

    .modal-content.rounded-4,
    .modal-content.rounded-2 {
        border: 1px solid var(--cart-border);
        box-shadow: var(--cart-shadow);
    }

    #tycTabs .nav-link {
        border: 0;
        border-radius: 12px 12px 0 0;
    }

    #tycTabs .nav-link.active {
        background: #fff;
        color: var(--cart-accent);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }

    @media (max-width: 991px) {
        .shopping-summery {
            display: none;
        }

        .sticky-summary {
            top: auto;
            bottom: 0;
            border-radius: 24px 24px 0 0;
            padding-bottom: 18px;
        }

        .cart-hero {
            padding: 20px 18px;
        }
    }

    @media (max-width: 576px) {
        .cart-shell {
            padding-top: 14px;
        }

        .cart-hero {
            border-radius: 24px;
        }

        .detail-qty {
            transform: scale(.94);
            transform-origin: left center;
        }
    }

    #pane-iframe {
        background-color: #f1f5f9;
        /* Color de fondo mientras carga el PDF */
        min-height: 400px;
    }

    #tycIframe {
        width: 100%;
        height: 600px;
        /* Incrementamos la altura para mejor lectura en App/Tablet */
        border: 0;
    }
</style>

<div class="container cart-shell">
    <div class="cart-hero">
        <div class="cart-hero-copy">
            <span class="cart-mini-badge"><i class="fi-rs-shopping-bag"></i> Carrito FULMUV</span>
            <h2 class="heading-2 mb-2">Tu Carrito</h2>
            <p class="cart-hero-note">Tienes <span class="text-brand fw-bold" id="totalCarritoShop">0</span> productos listos para revisar, actualizar y convertir en orden.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div id="listaProductoShopCartMobile"></div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-summary">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold" style="font-size:16px;color:#0f172a;">Resumen de compra</div>
                        <div class="small text-muted">Revisa montos e impuestos antes de continuar</div>
                    </div>
                </div>
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span class="fw-bold" id="cart_carrito_amount">$0.00</span>
                </div>
                <div class="total-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="total-label">IVA (15%)</span>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalIVA" class="text-muted"><i class="fi-rs-interrogation"></i></a>
                    </div>
                    <span class="fw-bold" id="cart_iva_amount">$0.00</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Tus Ahorros</span>
                    <span class="savings-value" id="cart_ahorro_amount">-$0.00</span>
                </div>
                <hr class="my-3">
                <div class="total-row mb-20">
                    <span class="h5 fw-bold">TOTAL</span>
                    <span class="h4 fw-900" style="color: #004E60;" id="cart_total_amount">$0.00</span>
                </div>
                <div class="summary-note-box">
                    Antes de pagar por tus productos de interés, comunícate con los vendedores y asegúrate que sean los productos que realmente quieres o necesitas. Recuerda que es una transacción directa entre tú, como comprador, y el vendedor. Una vez comprado tu producto, FULMUV te lo puede enviar A DOMICILIO.
                </div>
                <div class="summary-check">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="aceptoTyC">
                        <label class="form-check-label text-secondary" for="aceptoTyC" style="line-height: 1.5; display: block">
                            Declaro que he leído y acepto los <a href="javascript:void(0)" class="fw-bold text-dark text-decoration-underline" data-bs-toggle="modal" data-bs-target="#modalTyC">términos y condiciones</a> de Uso para Usuarios Visitantes y Compradores y el Aviso Legal de FULMUV. Entiendo que FULMUV actúa como plataforma de contacto y no interviene en las negociaciones ni garantiza productos o servicios; toda relación comercial se realiza directamente con el proveedor. Autorizo el tratamiento de mis datos personales conforme a las finalidades y bases legales descritas, en cumplimiento de la LOPDP del Ecuador. Reconozco que las entregas podrán realizarse mediante el socio logístico autorizado bajo las condiciones informadas.
                        </label>
                    </div>
                    <span></span>
                </div>

                <a id="btnContinuarOrden" onclick="enviarAOrderCheckout()" class="btn-checkout" style="text-decoration:none;">
                    Continúa a crear orden <i class="fi-rs-sign-out"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIVA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="fw-bold">Detalle del IVA</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Algunos productos ya incluyen el 15% de IVA en su precio referencial.</p>
                <div id="ivaDetalleContenido"></div>
                <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                    <span class="fw-bold">Total IVA</span>
                    <span class="fw-bold text-primary" id="ivaTotalRetirado"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTyC" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-2 overflow-hidden">
            <div class="modal-header bg-light">
                <h6 class="fw-bold m-0">Documentos Legales FULMUV</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs px-1 pt-1 bg-light border-0" id="tycTabs">
                    <li class="nav-item">
                        <button class="nav-link active small fw-bold" data-bs-toggle="tab" data-bs-target="#pane-iframe" data-src="https://fulmuv.com/documentos/8_Aviso Legal y Descargos de Responsabilidad de FULMUV.pdf">T&C Proveedores</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link small fw-bold" data-bs-toggle="tab" data-bs-target="#pane-iframe" data-src="https://fulmuv.com/documentos/8_Política de Privacidad y Cookies de FULMUV.pdf">Privacidad</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link small fw-bold" data-bs-toggle="tab" data-bs-target="#pane-iframe" data-src="https://fulmuv.com/documentos/8_Términos y Condiciones de Uso General para Usuarios Visitantes y Compradores de FULMUV.pdf">Aviso Legal</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pane-iframe">
                        <div class="ratio" style="--bs-aspect-ratio: 120%;">
                            <iframe id="tycIframe" src="about:blank" style="border:0;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    (function() {
        const modalEl = document.getElementById('modalTyC');
        const iframe = document.getElementById('tycIframe');
        const tabs = document.querySelectorAll('#tycTabs .nav-link');

        // Función para construir la URL del visor
        function getViewerUrl(url) {
            return "https://docs.google.com/viewer?url=" + encodeURIComponent(url) + "&embedded=true";
        }

        // ✅ 1. CARGA INMEDIATA AL INICIAR LA PÁGINA
        // Esto hace que cuando el usuario abra el modal, el PDF ya esté procesándose
        document.addEventListener('DOMContentLoaded', () => {
            const firstTab = document.querySelector('#tycTabs .nav-link.active');
            if (firstTab) {
                const url = firstTab.getAttribute('data-src');
                iframe.src = getViewerUrl(url);
            }
        });

        // ✅ 2. RE-CONFIRMACIÓN AL ABRIR EL MODAL (Por si falló la precarga)
        modalEl.addEventListener('show.bs.modal', () => {
            if (!iframe.src || iframe.src.includes('about:blank')) {
                const activeTab = document.querySelector('#tycTabs .nav-link.active');
                iframe.src = getViewerUrl(activeTab.getAttribute('data-src'));
            }
        });

        // ✅ 3. CAMBIO RÁPIDO ENTRE PESTAÑAS
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                const url = e.target.getAttribute('data-src');
                // Mostrar un pequeño indicador o limpiar el iframe para feedback visual
                iframe.src = getViewerUrl(url);
            });
        });
    })();

    function enviarAOrderCheckout() {
        // 1. Validación de Términos y Condiciones
        if (!document.getElementById('aceptoTyC').checked) {
            Swal.fire({
                title: 'FULMUV',
                text: 'Debes aceptar los términos y condiciones para continuar.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#004E60',
                heightAuto: false, // ✅ Crucial para WebViews de Flutter (evita saltos de scroll)
                customClass: {
                    container: 'my-swal-mobile' // Opcional: para estilos personalizados
                }
            });
            return;
        }

        // 2. Puente de comunicación con Flutter 
        if (window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('navToCheckout');
        } else {
            // Fallback para navegador web convencional
            window.location.href = "shop-checkout.php";
        }
    }
</script>

<script src="js/shop-cart.js?v=1.4"></script>