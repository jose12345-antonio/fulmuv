<?php
include 'includes/header.php';

$id_producto = $_GET["q"];

echo '<input type="hidden" placeholder="" id="id_producto" class="form-control" value="' . $id_producto . '" />';
echo '<input type="hidden" id="id_usuario_session" value="' . (int)($_SESSION["id_usuario"] ?? 0) . '" />';
?>

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

    #subCategoriasProductos {
        text-decoration: underline;
        text-underline-offset: 3px;
        margin-bottom: 10px;
        /* separación del texto */
        text-decoration-thickness: 2px;
        /* grosor */
    }

    /* Restaura los puntos */
    .antes-de-comprar ul {
        list-style: disc !important;
        list-style-position: outside;
        padding-left: 1.25rem;
        /* espacio para el punto */
        margin: 0;
    }

    .antes-de-comprar li {
        margin-bottom: .25rem;
    }

    .btn-whatsapp {
        background: #25D366;
        color: #fff !important;
        border: none;
        display: inline-flex;
        align-items: center;
        padding: .6rem 1rem;
        border-radius: .6rem;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(37, 211, 102, .35);
    }

    .btn-whatsapp:hover {
        background: #1ebe57;
        color: #fff !important;
    }

    .contact-line {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-top: .5rem;
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

    .vendor-social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 18px 0 14px;
    }

    .vendor-social-link {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 16px;
    }

    .vendor-description-block {
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }


    /* ====== SLIDER PRINCIPAL ====== */
    .detail-gallery .product-image-slider {
        width: 100%;
        max-width: 100%;
    }

    /* alto fijo del marco (ajústalo si quieres) */
    .detail-gallery .product-image-slider .slick-slide {
        height: 520px;
        display: flex !important;
    }

    /* IMPORTANTE: el <a> debe ocupar todo el alto/ancho */
    .detail-gallery .product-image-slider .slick-slide a {
        width: 100%;
        height: 100%;
        display: block;
    }

    /* La imagen llena el marco */
    .detail-gallery .product-image-slider .slick-slide img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        /* 👈 llena el marco */
        border-radius: 14px;
    }

    /* ====== THUMBNAILS IGUALES ====== */
    .detail-gallery .slider-nav-thumbnails .slick-slide {
        padding: 6px;
    }

    .detail-gallery .slider-nav-thumbnails img {
        width: 115px !important;
        height: 115px !important;
        object-fit: cover;
        border-radius: 12px;
        display: block;
    }

    .product-price .current-price {
        line-height: 1.1;
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
        padding: .20rem .55rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: .78rem;
        line-height: 1;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #111827;
        margin-right: .35rem;
        margin-bottom: .35rem;
        /* si hay 2, que bajen bonito */
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
</style>

<div class="container mb-30" style="transform: none;">
    <div class="row" style="transform: none;">
        <div class="col-xl-12 col-lg-12 m-auto" style="transform: none;">
            <div class="row" style="transform: none;">
                <div class="col-xl-12">
                    <div class=" p accordion-detail">
                        <div class="row mb-50 mt-30">
                            <div class="col-md-8 col-sm-12 col-xs-12 mb-md-0 mb-sm-5">
                                <div class="detail-gallery">
                                    <span class="zoom-icon"><i class="fi-rs-search"></i></span>
                                    <!-- MAIN SLIDES -->
                                    <div class="product-image-slider">

                                    </div>
                                    <!-- THUMBNAILS -->
                                    <div class="slider-nav-thumbnails">

                                    </div>
                                </div>
                                <!-- End Gallery -->
                            </div>
                            <div class="col-md-4 col-sm-12 col-xs-12">
                                <div class="detail-info pr-30 pl-30">
                                    <span class="stock-status out-stock d-none"> Sale Off </span>
                                    <div>
                                        <h6 class="fw-bold" id="subCategoriasProductos"></h6>
                                    </div>
                                    <h4 class="title-detail"></h4>
                                    <div class="clearfix product-price-cover">
                                        <div class="product-price primary-color float-left mb-2">
                                            <span class="current-price text-brand text-secondary"></span>
                                            <span class="d-none" id="listDescuento">
                                                <span class="save-price font-md color3 ml-15" id="valorporcentaje"></span>
                                                <span class="old-price font-md ml-15" id="valorDescuento"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- ✅ debajo del precio SIEMPRE -->
                                    <div id="flagsPrecio" class="mb-3 mt-0"></div>
                                    <div class="mb-10" id="viewVisitaTienda">
                                    </div>

                                    <div class="row g-2 mt-3" id="infoExtraVehiculo"></div>

                                </div>
                                <!-- Detail Info -->
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="tab-style3">
                                <ul class="nav nav-tabs text-uppercase">
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
                                    <div class="tab-pane fade show active" id="Description">

                                    </div>
                                    <div class="tab-pane fade" id="Additional-info">
                                        <div class="row">
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Modelo</h5>
                                                <h6 class="fw-normal mt-2" id="modelo_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Color</h5>
                                                <h6 class="fw-normal mt-2" id="color_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Tracción</h5>
                                                <h6 class="fw-normal mt-2" id="traccion_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Condición</h5>
                                                <h6 class="fw-normal mt-2" id="condicion_info"></h6>
                                            </div>
                                            <!-- <div class="col-lg-3 text-center mb-3">
                                                <h5 class="fw-bold">Combustible</h5>
                                                <h6 class="fw-normal mt-2" id="combustible_info"></h6>
                                            </div> -->
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Transmisión</h5>
                                                <h6 class="fw-normal mt-2" id="transmision_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Tipo de dueño</h5>
                                                <h6 class="fw-normal mt-2" id="dueno_info"></h6>
                                            </div>
                                            <!-- <div class="col-lg-3 text-center  mb-3">
                                                <h5 class="fw-bold">Subtipo</h5>
                                                <h6 class="fw-normal mt-2" id="subtipo_info"></h6>
                                            </div> -->
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Tipo de vendedor</h5>
                                                <h6 class="fw-normal mt-2" id="vendedor_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Placa empieza con</h5>
                                                <h6 class="fw-normal mt-2" id="placa_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Placa termina en</h5>
                                                <h6 class="fw-normal mt-2" id="placa_termina_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Dirección</h5>
                                                <h6 class="fw-normal mt-2" id="direccion_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Climatización</h5>
                                                <h6 class="fw-normal mt-2" id="climatizacion_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Cilindraje de motor</h5>
                                                <h6 class="fw-normal mt-2" id="cilindraje_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Tapicería de asientos</h5>
                                                <h6 class="fw-normal mt-2" id="tapiceria_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Funcionamiento de motor</h5>
                                                <h6 class="fw-normal mt-2" id="motor_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Año del vehículo</h5>
                                                <h6 class="fw-normal mt-2" id="anio_info"></h6>
                                            </div>
                                            <div class="col-lg-3 text-center mb-3 mt-3">
                                                <h5 class="fw-bold">Ubicación</h5>
                                                <h6 class="fw-normal mt-2" id="ubicacion_info"></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Vendor-info">
                                        <div class="vendor-logo d-flex mb-30">
                                            <img id="imagenEmpresa" onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';">
                                            <div class="vendor-name ml-15">
                                            </div>
                                        </div>
                                        <h4>
                                            <a id="nombreEmpresa"></a>
                                        </h4>
                                        <div class="mt-20 mb-20 antes-de-comprar">
                                            <h5 class="fw-bold">Antes de comprar:</h5>
                                            <ul class="mt-2">
                                                <li class="h5 mb-2">Verifica antes de comprar, que sea el producto que realmente te interesa, su estado y calidad.</li>
                                                <li class="h5">Añade tu producto al carrito, para que puedas gestionar tu compra.</li>
                                            </ul>
                                        </div>

                                        <div>
                                            <a id="btnWhatsApp" class="btn btn-whatsapp d-inline-flex align-items-center">
                                                <!-- ícono -->
                                                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 32 32">
                                                    <path fill="currentColor" d="M19.11 17.05c-.27-.14-1.61-.79-1.86-.88c-.25-.09-.43-.14-.61.14c-.18.27-.7.88-.86 1.06c-.16.18-.32.2-.59.07c-.27-.14-1.15-.42-2.19-1.34c-.81-.72-1.36-1.6-1.52-1.87c-.16-.27-.02-.41.12-.55c.12-.12.27-.32.41-.48c.14-.16.18-.27.27-.45c.09-.18.05-.34-.02-.48c-.07-.14-.61-1.47-.83-2.01c-.22-.53-.44-.46-.61-.46h-.52c-.18 0-.48.07-.73.34c-.25.27-.96.94-.96 2.3c0 1.36.98 2.67 1.11 2.85c.14.18 1.93 2.95 4.68 4.14c.65.28 1.16.45 1.55.57c.65.21 1.24.18 1.71.11c.52-.08 1.61-.66 1.84-1.3c.23-.64.23-1.19.16-1.3c-.07-.11-.25-.18-.52-.32zM16 3C8.83 3 3 8.83 3 16c0 2.29.62 4.44 1.7 6.28L3 29l6.9-1.81A12.93 12.93 0 0 0 16 29c7.17 0 13-5.83 13-13S23.17 3 16 3z" />
                                                </svg>
                                                <span class="ms-2">Contáctame por WhatsApp</span>
                                            </a>
                                        </div>
                                        <div class="mt-15" id="contactosEmpresa"></div>
                                        <div id="empresaSocialLinks" class="vendor-social-links"></div>


                                        <div id="map" class="mt-30" style="width:auto;height:500px;border-radius:10px;overflow:hidden;"></div>

                                        <div id="descripcionEmpresa" class="vendor-description-block"></div>
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
                                        <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-vehiculos">

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
<!-- <script src="https://cdn.jsdelivr.net/npm/medium-zoom@1.0.6/dist/medium-zoom.min.js"></script> -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

 
<?php
include 'includes/footer.php';
?>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&callback=initMap" async defer></script>

<script src="js/detalle_vehiculo.js?v1.0.0.0.0.0.0.0.0.1.10"></script>
