<?php
include 'includes/header.php';

$id_producto = $_GET["q"];
$sinCuentaMode = (defined('APP_SIN_CUENTA') && APP_SIN_CUENTA) || (isset($_GET['sin_cuenta']) && $_GET['sin_cuenta'] == '1');

echo '<input type="hidden" id="id_producto" value="' . $id_producto . '" />';
echo '<input type="hidden" id="id_usuario_session" value="' . (int)($_SESSION["id_usuario"] ?? 0) . '" />';
?>

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>,
        productDetailPath: "detalle_producto_sincuenta.php",
        vendorProductsPath: "productos_vendor_sincuenta.php",
        categoryProductsPath: "productos_categoria_sincuenta.php"
    });
</script>

<link rel="canonical" href="https://fulmuv.com/detalle_productos.php?q=<?php echo $id_producto; ?>">

<style>
    /* --- VARIABLES Y BASES --- */
    :root {
        --primary-color: #044554;
        --secondary-color: #198754;
        --bg-light: #f9f9f9;
        --text-title: clamp(22px, 5vw, 32px);
        --text-price: clamp(20px, 4vw, 26px);
        --text-body: clamp(14px, 2vw, 16px);
        --text-small: clamp(11px, 1.5vw, 13px);
        --app-skeleton-a: #e2e8f0;
        --app-skeleton-b: #f8fafc;
    }

    body {
        background-color: #fff;
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
    }

    /* --- GALERÍA MODERNA --- */
    .product-gallery-modern {
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
        border-bottom: 1px solid #e5e7eb;
        padding: 0 0 14px;
    }

    .main-image-container {
        width: 100%;
        height: clamp(380px, 62vw, 640px);
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        background: transparent;
    }

    .main-image-link {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: zoom-in;
    }

    .main-image-link .zoom-icon {
        position: absolute;
        top: 18px;
        right: 18px;
        z-index: 3;
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.08);
        color: #0f172a;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    }

    .main-image-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 20px 24px 8px;
        transition: opacity 0.2s ease;
    }

    .thumbnails-grid {
        display: flex;
        gap: 12px;
        padding: 12px 24px 0;
        justify-content: center;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .thumb-item {
        width: clamp(65px, 12vw, 90px);
        height: clamp(65px, 12vw, 90px);
        border-radius: 12px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
        position: relative;
        background: #fff;
        overflow: hidden;
    }

    .thumb-item.active {
        border-color: var(--primary-color);
    }

    .thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
    }

    .thumb-more-overlay::after {
        content: attr(data-count);
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border-radius: 10px;
    }

    /* --- INFORMACIÓN DEL PRODUCTO --- */
    .product-card-info {
        background: #fff;
        position: relative;
        padding: 18px 16px;
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.03);
        border-radius: 24px 24px 0 0;
        margin-top: 0;
    }

    .detail-summary-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .title-detail {
        font-size: var(--text-title);
        font-weight: 800;
        color: #111;
        margin-bottom: 8px;
    }

    .current-price {
        font-size: var(--text-price);
        font-weight: 700;
        color: var(--primary-color);
    }

    .old-price {
        text-decoration: line-through;
        color: #999;
        font-size: 0.9em;
        margin-left: 10px;
    }

    /* --- BADGES Y FLAGS --- */
    .price-flag {
        display: inline-flex;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: var(--text-small);
        font-weight: 600;
        margin: 4px 4px 0 0;
    }

    .price-flag.iva {
        background: #e3f2fd;
        color: #1976d2;
    }

    .price-flag.neg {
        background: #e8f5e9;
        color: #2e7d32;
    }

    /* --- GRILLA TÉCNICA --- */
    .technical-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin: 25px 0;
        background: var(--bg-light);
        padding: 20px;
        border-radius: 18px;
    }

    .tech-item small {
        color: #777;
        font-size: var(--text-small);
        text-transform: uppercase;
        display: block;
    }

    .tech-item span {
        font-size: var(--text-body);
        font-weight: 700;
        color: #333;
    }

    /* --- BOTONES --- */
    .btn-cart-modern {
        background: var(--primary-color);
        color: #fff;
        border-radius: 14px;
        padding: 14px;
        font-weight: 700;
        width: 100%;
        border: none;
        transition: 0.3s;
    }

    .purchase-panel {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 14px;
        margin-bottom: 22px;
    }

    .purchase-panel .detail-qty {
        min-height: 54px;
        border: 1px solid #dbe2ea !important;
        border-radius: 14px !important;
        background: #fff;
    }

    .purchase-panel .qty-val {
        background: transparent;
    }

    .service-cta {
        display: none;
        margin-bottom: 22px;
    }

    .service-note {
        margin-top: 12px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid #d1fae5;
        background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%);
        color: #166534;
        font-size: 14px;
        line-height: 1.55;
    }

    .vendor-social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 18px 0 14px;
    }

    .vendor-social-link {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid #dbe2ea;
        background: #f8fafc;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 16px;
    }

    .vendor-description-block {
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #475569;
        font-size: 14px;
        line-height: 1.65;
    }

    .login-upsell-note {
        display: none;
        margin-bottom: 22px;
        padding: 18px 18px 16px;
        border-radius: 18px;
        border: 1px solid #bfdbfe;
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, 0.14), transparent 42%),
            linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
        color: #1e3a8a;
    }

    .login-upsell-note strong {
        display: block;
        font-size: 15px;
        margin-bottom: 6px;
    }

    .login-upsell-note p {
        margin: 0;
        font-size: 13px;
        line-height: 1.65;
        color: #1d4ed8;
    }

    <?php if ($sinCuentaMode): ?>
    #purchasePanel {
        display: none;
    }

    #loginUpsellNote {
        display: block;
    }
    <?php endif; ?>

    .btn-service-contact {
        width: 100%;
        min-height: 54px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        color: #fff !important;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        box-shadow: 0 16px 28px rgba(34, 197, 94, 0.25);
    }

    .related-grid-shell {
        position: relative;
    }

    .home-verify-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 3;
        width: 32px;
        height: 32px;
        filter: drop-shadow(0 4px 10px rgba(15, 23, 42, 0.18));
    }

    .home-verify-overlay img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .related-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        height: 100%;
        margin: 0 6px;
    }

    .home-loading-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .home-loading-card {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
        min-height: 340px;
    }

    .home-loading-media,
    .home-loading-line,
    .home-loading-pill {
        background: linear-gradient(90deg, var(--app-skeleton-a) 25%, var(--app-skeleton-b) 37%, var(--app-skeleton-a) 63%);
        background-size: 400% 100%;
        animation: homeSkeleton 1.4s ease infinite;
    }

    .home-loading-media {
        width: 100%;
        height: 210px;
    }

    .home-loading-content {
        padding: .95rem 1rem 1rem;
    }

    .home-loading-caption {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .82rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: .9rem;
    }

    .home-loading-caption::before {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #0f766e;
        box-shadow: 0 0 0 6px rgba(15, 118, 110, .12);
    }

    .home-loading-line {
        height: 12px;
        border-radius: 999px;
        margin-bottom: .65rem;
    }

    .home-loading-line.w-90 {
        width: 90%;
    }

    .home-loading-line.w-75 {
        width: 75%;
    }

    .home-loading-line.w-60 {
        width: 60%;
    }

    .home-loading-pill {
        width: 44%;
        height: 26px;
        border-radius: 999px;
        margin-top: .8rem;
    }

    @keyframes homeSkeleton {
        0% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0 50%;
        }
    }

    .related-card-media {
        position: relative;
        height: 190px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .related-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-verified {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        z-index: 2;
        object-fit: contain;
        filter: drop-shadow(0 4px 10px rgba(15, 23, 42, 0.2));
    }

    .related-discount {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
        padding: 4px 10px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
    }

    .related-card-body {
        padding: 12px 14px 14px;
        min-height: 145px;
        display: flex;
        flex-direction: column;
    }

    .related-card-title {
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
        color: #111827;
        min-height: 38px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .related-card-desc {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.45;
        min-height: 52px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .related-card-price {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-height: 44px;
        margin-top: auto;
    }

    .related-card-price strong {
        color: #2563eb;
        font-size: 17px;
        line-height: 1;
    }

    .related-card-price .old-price {
        margin-left: 0;
        margin-top: 4px;
        color: #dc2626;
        font-size: 12px;
        font-weight: 700;
    }

    .btn-whatsapp-modern {
        background: #25D366;
        color: #fff !important;
        border-radius: 14px;
        padding: 12px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 15px;
    }

    .product-info-tabs {
        margin-top: 38px;
    }

    .product-info-tabs .nav {
        gap: 10px;
        background: #f3f6fb;
        padding: 8px;
        border-radius: 18px;
        display: inline-flex;
        flex-wrap: wrap;
    }

    .product-info-tabs .nav-link {
        border: 0;
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 700;
        color: #4b5563;
        background: transparent;
    }

    .product-info-tabs .nav-link.active {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .tab-content {
        padding: 0 !important;
        margin-top: 18px;
    }

    .tab-pane-panel {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 22px;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
    }

    #Description.tab-pane-panel {
        line-height: 1.75;
        color: #334155;
    }

    #Description.tab-pane-panel p:last-child {
        margin-bottom: 0;
    }

    #Additional-info.tab-pane-panel table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
        border-radius: 16px;
    }

    #Additional-info.tab-pane-panel th,
    #Additional-info.tab-pane-panel td {
        padding: 14px 16px;
        vertical-align: top;
    }

    #Additional-info.tab-pane-panel th {
        width: 34%;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
    }

    #Additional-info.tab-pane-panel td p {
        margin: 0;
        color: #0f172a;
    }

    .vendor-panel-head {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .vendor-panel-head img {
        width: 72px;
        height: 72px;
        object-fit: cover;
    }

    .vendor-panel-name {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }

    .vendor-panel-body {
        display: grid;
        gap: 18px;
    }

    .vendor-note-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px 18px;
    }

    .vendor-note-box ul {
        padding-left: 18px;
        margin-bottom: 0;
    }

    /* --- RESPONSIVE TABLET --- */
    @media (min-width: 768px) {
        .technical-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .product-card-info {
            padding: 40px;
            width: 85%;
            margin-inline: auto;
        }
    }

    @media (max-width: 767px) {
        .detail-summary-row {
            align-items: flex-start;
        }

        .main-image-container {
            height: clamp(340px, 88vw, 480px);
        }

        .main-image-container img {
            padding: 16px 14px 6px;
        }

        .thumbnails-grid {
            padding: 12px 14px 0;
            justify-content: flex-start;
        }

        .product-info-tabs .nav {
            width: 100%;
            justify-content: center;
        }

        .tab-pane-panel {
            padding: 18px 16px;
        }

        .vendor-panel-head {
            align-items: flex-start;
        }

        .related-card-media {
            height: 168px;
        }

        .home-loading-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .home-loading-card {
            min-height: 310px;
        }

        .home-loading-media {
            height: 175px;
        }
    }
</style>

<div class="product-gallery-modern">
    <div class="main-image-container">
    </div>
    <div class="thumbnails-grid" id="thumbGrid">
    </div>
</div>

<div class="product-card-info mt-4">
    <div class="container-fluid p-0">
        <h1 class="title-detail"></h1>
        <div class="detail-summary-row">
            <div>
                <div class="d-flex align-items-baseline mb-2 flex-wrap">
                    <span class="current-price"></span>
                    <span id="listDescuento" class="d-none">
                        <span class="old-price" id="valorDescuento"></span>
                        <span class="badge bg-danger ms-2" id="valorporcentaje"></span>
                    </span>
                </div>
                <div id="flagsPrecio"></div>
            </div>
            <div id="viewVisitaTienda"></div>
        </div>

        <div id="purchasePanel" class="purchase-panel">
            <div class="row g-3 align-items-center">
                <div class="col-4 col-md-3">
                    <div class="detail-qty p-2 d-flex justify-content-between align-items-center">
                        <a href="#" class="qty-down text-dark"><i class="fi-rs-angle-small-down"></i></a>
                        <input type="text" name="quantity" class="qty-val border-0 text-center w-50 fw-bold" value="1">
                        <a href="#" class="qty-up text-dark"><i class="fi-rs-angle-small-up"></i></a>
                    </div>
                </div>
                <div class="col-8 col-md-9">
                    <button class="btn btn-cart-modern button-add-to-cart">
                        <img src="../img/carrito_transparente.png" width="22" class="me-2">
                        Agregar al carrito
                    </button>
                </div>
            </div>
        </div>

        <div id="serviceCtaPanel" class="service-cta">
            <a id="btnServiceContact" class="btn-service-contact" href="#">
                <i class="fab fa-whatsapp"></i>
                <span>Comunícate con la empresa</span>
            </a>
            <div id="serviceInfoBox" class="service-note">
                Debes comunicarte por WhatsApp con la empresa para obtener este servicio.
            </div>
        </div>

        <div id="loginUpsellNote" class="login-upsell-note">
            <strong>Inicia sesión para tener mejores opciones</strong>
            <p>Con una cuenta podrás guardar productos, gestionar tu carrito, dar seguimiento a tus solicitudes y acceder a una experiencia más completa dentro de Fulmuv.</p>
        </div>

        <div class="technical-grid">
            <div class="tech-item">
                <small>Marca</small>
                <span id="marcaProducto">---</span>
            </div>
            <div class="tech-item">
                <small>Modelo</small>
                <span id="modeloProducto">---</span>
            </div>
            <div class="tech-item">
                <small>Categoría</small>
                <span id="categoriaProducto">---</span>
            </div>
            <div class="tech-item">
                <small>Subcategoría</small>
                <span id="subcategoriaProducto">---</span>
            </div>
        </div>

        <div class="product-info-tabs mt-5">
            <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#Description">Descripción</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#Additional-info">Ficha Técnica</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#Vendor-info">Vendedor</button>
                </li>
            </ul>

            <div class="tab-content p-2">
                <div class="tab-pane fade show active tab-pane-panel" id="Description"></div>
                <div class="tab-pane fade tab-pane-panel" id="Additional-info"></div>
                <div class="tab-pane fade tab-pane-panel" id="Vendor-info">
                    <div class="vendor-panel-head">
                        <img id="imagenEmpresa" class="rounded-circle border" width="60" height="60">
                        <div>
                            <h5 id="nombreEmpresa" class="vendor-panel-name mb-0"></h5>
                            <div id="contactosEmpresa"></div>
                        </div>
                    </div>
                    <div class="vendor-panel-body">
                        <div class="vendor-note-box antes-de-comprar">
                            <h6 class="fw-bold">Antes de comprar:</h6>
                            <ul class="mt-1">
                                <li class="mb-1" style="font-size: 12px">Verifica antes de comprar, que sea el producto que realmente te interesa, su estado y calidad.</li>
                                <li class="" style="font-size: 12px">Añade tu producto al carrito, para que puedas gestionar tu compra.</li>
                            </ul>
                        </div>
                        <a id="btnWhatsApp" class="btn-whatsapp-modern">
                            <i class="fab fa-whatsapp me-2"></i> Contáctame por WhatsApp
                        </a>
                        <div id="empresaSocialLinks" class="vendor-social-links"></div>
                        <div id="map" class="mt-4 rounded-4 shadow-sm" style="height:300px;"></div>
                        <div id="descripcionEmpresa" class="vendor-description-block"></div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-top">
                <h4 class="fw-bold mb-4">Productos Relacionados</h4>
                <div class="related-grid-shell">
                    <div id="carausel-4-columns-oferta" class="row g-3"></div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

    <?php include 'includes/footer.php'; ?>

    <script src="js/detalle_productos.js?v1.0.0.0.0.0.0.0.0.0.2.19"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&callback=initMap" async defer></script>

    <script>
        $(document).on("input", ".qty-val", function() {
            let v = parseInt($(this).val(), 10);
            if (isNaN(v) || v < 1) v = 1;
            $(this).val(v);
        });
    </script>


