<?php
include 'includes/header.php';

$id_producto = $_GET["q"];
$sinCuentaMode = defined('APP_SIN_CUENTA') && APP_SIN_CUENTA;

echo '<input type="hidden" placeholder="" id="id_producto" class="form-control" value="' . $id_producto . '" />';
?>

<style>
    :root {
        --primary-color: #044554;
        --secondary-color: #198754;
        --bg-light: #f8fafc;
        --text-title: clamp(24px, 5vw, 34px);
        --text-price: clamp(22px, 4vw, 30px);
        --text-body: clamp(14px, 2vw, 16px);
        --text-small: clamp(11px, 1.5vw, 13px);
    }

    body {
        background: #fff;
    }

    #subCategoriasProductos {
        text-decoration: underline;
        text-underline-offset: 3px;
        text-decoration-thickness: 2px;
        margin-bottom: 10px;
    }

    .vehicle-gallery-modern {
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
        border-bottom: 1px solid #e5e7eb;
        padding: 0 0 14px;
    }

    .detail-gallery {
        position: relative;
    }

    .detail-gallery .zoom-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 4;
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.08);
        color: #0f172a;
    }

    .detail-gallery .product-image-slider {
        width: 100%;
        max-width: 100%;
    }

    .detail-gallery .product-image-slider .slick-slide {
        height: clamp(380px, 62vw, 640px);
        display: flex !important;
    }

    .detail-gallery .product-image-slider .slick-slide a {
        width: 100%;
        height: 100%;
        display: block;
    }

    .detail-gallery .product-image-slider .slick-slide img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain;
        padding: 20px 24px 8px;
    }

    .detail-gallery .slider-nav-thumbnails {
        padding: 12px 24px 0;
    }

    .detail-gallery .slider-nav-thumbnails .slick-slide {
        padding: 6px;
    }

    .detail-gallery .slider-nav-thumbnails img {
        width: 115px !important;
        height: 115px !important;
        object-fit: cover;
        border-radius: 12px;
        display: block;
        border: 2px solid transparent;
    }

    .detail-gallery .slider-nav-thumbnails .slick-current img {
        border-color: var(--primary-color);
    }

    .vehicle-detail-shell {
        padding: 24px 16px 0;
    }

    .vehicle-card-info {
        background: #fff;
        position: relative;
        padding: 18px 16px;
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.03);
        border-radius: 24px 24px 0 0;
    }

    .title-detail {
        font-size: var(--text-title);
        font-weight: 800;
        color: #111827;
        margin-bottom: 8px;
    }

    .product-price .current-price {
        color: var(--primary-color);
        font-size: var(--text-price);
        font-weight: 800;
        line-height: 1.05;
    }

    #listDescuento {
        display: block;
        margin-top: 2px;
    }

    #valorporcentaje,
    #valorDescuento {
        display: block;
        margin-left: 0 !important;
    }

    #flagsPrecio {
        display: block;
        width: 100%;
    }

    #flagsPrecio .price-flag {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .65rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: .78rem;
        line-height: 1;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #111827;
        margin-right: .35rem;
        margin-bottom: .35rem;
    }

    #flagsPrecio .price-flag.iva {
        background: rgba(13, 110, 253, .10);
        border-color: rgba(13, 110, 253, .25);
        color: #0d6efd;
    }

    #flagsPrecio .price-flag.neg {
        background: rgba(25, 135, 84, .12);
        border-color: rgba(25, 135, 84, .25);
        color: #198754;
    }

    .detail-summary-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    #viewVisitaTienda .btn {
        border-radius: 14px;
        font-weight: 700;
        padding: 10px 16px;
    }

    .vehicle-spec-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin: 24px 0 6px;
    }

    .vehicle-spec-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 14px 16px;
        min-height: 88px;
    }

    .vehicle-spec-card small {
        display: block;
        color: #64748b;
        font-size: var(--text-small);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .vehicle-spec-card span {
        color: #0f172a;
        font-size: var(--text-body);
        font-weight: 800;
        line-height: 1.35;
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
        border: 0;
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

    .vehicle-extra-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .vehicle-extra-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px 14px;
        text-align: center;
        min-height: 110px;
    }

    .vehicle-extra-item h5 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        margin-bottom: 10px;
        font-weight: 800;
    }

    .vehicle-extra-item h6 {
        margin: 0;
        color: #0f172a;
        font-size: 15px;
        line-height: 1.4;
        font-weight: 700;
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
        border-radius: 999px;
        border: 1px solid #dbe2ea;
    }

    .vendor-panel-name {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
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

    .antes-de-comprar ul {
        list-style: disc !important;
        list-style-position: outside;
        padding-left: 1.25rem;
        margin: 0;
    }

    .antes-de-comprar li {
        margin-bottom: .35rem;
    }

    .btn-whatsapp {
        background: #25D366;
        color: #fff !important;
        border: none;
        display: inline-flex;
        align-items: center;
        padding: .8rem 1rem;
        border-radius: .85rem;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(37, 211, 102, .25);
        text-decoration: none;
        justify-content: center;
    }

    .btn-whatsapp:hover {
        background: #1ebe57;
        color: #fff !important;
    }

    .guest-experience-note {
        margin-bottom: 16px;
        padding: 16px 18px;
        border: 1px solid rgba(4, 69, 84, .12);
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(4, 69, 84, .08) 0%, rgba(25, 135, 84, .08) 100%);
    }

    .guest-experience-note h5 {
        margin: 0 0 6px;
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
    }

    .guest-experience-note p {
        margin: 0;
        color: #475569;
        line-height: 1.6;
        font-size: 14px;
    }

    .contact-line {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-weight: 600;
    }

    .contact-line a {
        color: #0d6efd;
        text-decoration: none;
    }

    .contact-line a:hover {
        text-decoration: underline;
    }

    .icon-circle {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .icon-wa {
        background: rgba(37, 211, 102, .15);
        color: #25D366;
    }

    .icon-tel {
        background: rgba(13, 110, 253, .12);
        color: #0d6efd;
    }

    #map {
        width: auto;
        height: 420px;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    @media (min-width: 768px) {
        .vehicle-card-info {
            padding: 40px;
            width: 85%;
            margin-inline: auto;
        }

        .vehicle-spec-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 991px) {
        .vehicle-extra-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .detail-gallery .product-image-slider .slick-slide {
            height: clamp(340px, 88vw, 480px);
        }

        .detail-gallery .product-image-slider .slick-slide img {
            padding: 16px 14px 6px;
        }

        .detail-gallery .slider-nav-thumbnails {
            padding: 12px 14px 0;
        }

        .product-info-tabs .nav {
            width: 100%;
            justify-content: center;
        }

        .tab-pane-panel {
            padding: 18px 16px;
        }

        .vehicle-extra-grid {
            grid-template-columns: 1fr;
        }

        .vendor-panel-head {
            align-items: flex-start;
        }

        #map {
            height: 300px;
        }
    }
</style>

<div class="vehicle-gallery-modern">
    <div class="detail-gallery">
        <span class="zoom-icon"><i class="fi-rs-search"></i></span>
        <div class="product-image-slider"></div>
        <div class="slider-nav-thumbnails"></div>
    </div>
</div>

<div class="vehicle-detail-shell">
    <div class="vehicle-card-info">
        <div>
            <h6 class="fw-bold" id="subCategoriasProductos"></h6>
        </div>
        <h4 class="title-detail"></h4>

        <div class="detail-summary-row">
            <div>
                <div class="clearfix product-price-cover">
                    <div class="product-price primary-color float-left mb-2">
                        <span class="current-price text-brand text-secondary"></span>
                        <span class="d-none" id="listDescuento">
                            <span class="save-price font-md color3 ml-15" id="valorporcentaje"></span>
                            <span class="old-price font-md ml-15" id="valorDescuento"></span>
                        </span>
                    </div>
                </div>
                <div id="flagsPrecio" class="mb-1 mt-0"></div>
            </div>
            <div class="mb-10" id="viewVisitaTienda"></div>
        </div>

        <div class="vehicle-spec-grid" id="infoExtraVehiculo"></div>

        <div class="product-info-tabs">
            <ul class="nav nav-tabs text-uppercase" id="vehicleDetailTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="Description-tab" data-bs-toggle="tab" href="#Description">Descripción</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="Additional-info-tab" data-bs-toggle="tab" href="#Additional-info">Información adicional</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="Vendor-info-tab" data-bs-toggle="tab" href="#Vendor-info">Vendedor</a>
                </li>
            </ul>

            <div class="tab-content shop_info_tab entry-main-content">
                <div class="tab-pane fade show active tab-pane-panel" id="Description"></div>

                <div class="tab-pane fade tab-pane-panel" id="Additional-info">
                    <div class="vehicle-extra-grid">
                        <div class="vehicle-extra-item">
                            <h5>Modelo</h5>
                            <h6 id="modelo_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Color</h5>
                            <h6 id="color_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Tracción</h5>
                            <h6 id="traccion_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Condición</h5>
                            <h6 id="condicion_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Transmisión</h5>
                            <h6 id="transmision_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Tipo de dueño</h5>
                            <h6 id="dueno_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Tipo de vendedor</h5>
                            <h6 id="vendedor_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Placa empieza con</h5>
                            <h6 id="placa_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Placa termina en</h5>
                            <h6 id="placa_termina_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Dirección</h5>
                            <h6 id="direccion_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Climatización</h5>
                            <h6 id="climatizacion_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Cilindraje de motor</h5>
                            <h6 id="cilindraje_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Tapicería de asientos</h5>
                            <h6 id="tapiceria_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Funcionamiento de motor</h5>
                            <h6 id="motor_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Año del vehículo</h5>
                            <h6 id="anio_info"></h6>
                        </div>
                        <div class="vehicle-extra-item">
                            <h5>Ubicación</h5>
                            <h6 id="ubicacion_info"></h6>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade tab-pane-panel" id="Vendor-info">
                    <div class="vendor-panel-head">
                        <img id="imagenEmpresa" onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';">
                        <?php if ($sinCuentaMode): ?>
                            <div class="guest-experience-note">
                                <h5>Inicia sesion para una mejor experiencia</h5>
                                <p>Inicia sesion para tener una mejor experiencia en tus gestiones de ordenes, seguimiento y contacto dentro de FULMUV.</p>
                            </div>
                        <?php endif; ?>

                        <div>
                            <h4 id="nombreEmpresa" class="vendor-panel-name"></h4>
                            <div id="contactosEmpresa"></div>
                        </div>
                    </div>

                    <div class="vendor-panel-body">
                        <div class="vendor-note-box antes-de-comprar">
                            <h5 class="fw-bold">Antes de comprar:</h5>
                            <ul class="mt-2">
                                <li>Verifica antes de comprar, que sea el producto que realmente te interesa, su estado y calidad.</li>
                                <li>Añade tu producto al carrito, para que puedas gestionar tu compra.</li>
                            </ul>
                        </div>

                        <div>
                            <a id="btnWhatsApp" class="btn btn-whatsapp d-inline-flex align-items-center">
                                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 32 32">
                                    <path fill="currentColor" d="M19.11 17.05c-.27-.14-1.61-.79-1.86-.88c-.25-.09-.43-.14-.61.14c-.18.27-.7.88-.86 1.06c-.16.18-.32.2-.59.07c-.27-.14-1.15-.42-2.19-1.34c-.81-.72-1.36-1.6-1.52-1.87c-.16-.27-.02-.41.12-.55c.12-.12.27-.32.41-.48c.14-.16.18-.27.27-.45c.09-.18.05-.34-.02-.48c-.07-.14-.61-1.47-.83-2.01c-.22-.53-.44-.46-.61-.46h-.52c-.18 0-.48.07-.73.34c-.25.27-.96.94-.96 2.3c0 1.36.98 2.67 1.11 2.85c.14.18 1.93 2.95 4.68 4.14c.65.28 1.16.45 1.55.57c.65.21 1.24.18 1.71.11c.52-.08 1.61-.66 1.84-1.3c.23-.64.23-1.19.16-1.3c-.07-.11-.25-.18-.52-.32zM16 3C8.83 3 3 8.83 3 16c0 2.29.62 4.44 1.7 6.28L3 29l6.9-1.81A12.93 12.93 0 0 0 16 29c7.17 0 13-5.83 13-13S23.17 3 16 3z" />
                                </svg>
                                <span class="ms-2">Contáctame por WhatsApp</span>
                            </a>
                        </div>

                        <div id="map"></div>

                        <p id="descripcionEmpresa"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-60">
            <div class="col-12">
                <h2 class="section-title style-1 mb-30">Vehículos Relacionados</h2>
            </div>
            <div class="tab-content" id="myTabContent-1">
                <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                    <div class="carausel-4-columns-cover arrow-center position-relative">
                        <div class="slider-arrow slider-arrow-2 carausel-4-columns-arrow" id="carausel-4-columns-arrows-oferta"></div>
                        <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-vehiculos"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>
    });
</script>

<?php include 'includes/footer.php'; ?>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&callback=initMap" async defer></script>
<script src="js/detalle_vehiculo.js?v1.0.0.0.0.0.0.0.0.1.7"></script>
