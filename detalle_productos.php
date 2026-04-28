<?php
include 'includes/header.php';
$timer = time();

$id_producto = $_GET["q"];

echo '<input type="hidden" placeholder="" id="id_producto" class="form-control" value="' . $id_producto . '" />';
echo '<input type="hidden" id="id_usuario_session" value="' . (int)($_SESSION["id_usuario"] ?? 0) . '" />';
?>
<link rel="canonical" href="https://fulmuv.com/detalle_productos.php?q=<?php echo $id_producto; ?>">

<link rel="canonical" href="https://fulmuv.com/detalle_productos.php">
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

    /* Contenedor principal del slider, centrado en la columna */
    .detail-gallery .product-image-slider {
        max-width: 800px;
        /* centra el bloque completo */
    }

    /* Cada slide ocupa el mismo ancho y centra su contenido */
    .detail-gallery .product-image-slider .slick-slide {
        height: 518px;
        display: flex !important;
        justify-content: center;
        align-items: center;
    }

    /* La imagen se ajusta dentro del slide sin cortarse */
    .detail-gallery .product-image-slider .slick-slide img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .medium-zoom-overlay {
        background: rgba(0, 0, 0, 0.8) !important;
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

    /* âœ… thumbnails todos iguales */
    .slider-nav-thumbnails .slick-slide {
        height: 115px !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .slider-nav-thumbnails .slick-slide>div {
        width: 115px !important;
        height: 115px !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .slider-nav-thumbnails img {
        width: 115px !important;
        height: 115px !important;
        object-fit: cover !important;
        /* llena el cuadrado sin deformar */
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
    }

    .slick-arrow-custom {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        background-color: rgba(255, 255, 255, 0.7);
        border: none;
        font-size: 30px;
        padding: 10px;
        border-radius: 50%;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .slick-prev {
        left: 20px;
    }

    .slick-next {
        right: 20px;
    }

    .slick-arrow-custom:hover {
        background-color: rgba(255, 255, 255, 1);
    }



    /* Limitar altura del título para alinear cards */
    .product-content-wrap h2 {
        min-height: 3.2em;
        margin-bottom: 10px;
    }

    .product-content-wrap .product-price {
        margin-top: auto;
        margin-bottom: 15px;
    }


    .slick-prev,
    .slick-next {
        position: absolute;
        top: 50%;
        z-index: 10;
        border-radius: 50%;
        padding: 10px;
    }

    .slick-prev {
        left: 8px;
    }

    .slick-next {
        right: 8px;
    }


    /* Botón alineado al fondo */
    .product-cart-wrap .btn {
        margin-top: auto;
    }

    /* Contenedor relativo para permitir posicionamiento absoluto de flechas */
    #carausel-4-columns-oferta {
        position: relative;
        padding: 0 45px;
        /* Espacio lateral para que las flechas no queden sobre las imágenes */
    }

    /* Diseño base de los botones de navegación */
    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 45px;
        height: 45px;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 50%;
        display: flex !important;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        color: #2563eb;
        /* Color azul para el icono */
    }

    /* Iconos dentro de los botones */
    .slider-btn i {
        font-size: 22px;
    }

    /* Posiciones individuales */
    .slider-prev {
        left: 0;
    }

    .slider-next {
        right: 0;
    }

    /* Efectos al pasar el mouse (Hover) */
    .slider-btn:hover {
        background-color: #2563eb;
        color: #fff;
        border-color: #2563eb;
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25);
        transform: translateY(-52%) scale(1.05);
    }

    /* Estilo para botones deshabilitados (si infinite es false) */
    .slick-disabled {
        opacity: 0.4;
        cursor: not-allowed;
        filter: grayscale(1);
    }
</style>

<div class="container mb-30" style="transform: none;">
    <div class="row" style="transform: none;">
        <div class="col-xl-12 col-lg-12 m-auto" style="transform: none;">
            <div class="row" style="transform: none;">
                <div class="col-xl-12">
                    <div class=" p accordion-detail">
                        <div class="row mb-50 mt-30">
                            <div class="col-md-6 col-sm-12 col-xs-12 mb-md-0 mb-sm-5">
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
                            <div class="col-md-6 col-sm-12 col-xs-12">
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
                                    <!-- âœ… debajo del precio SIEMPRE -->
                                    <div id="flagsPrecio" class="mb-3 mt-0"></div>


                                    <div class="short-desc mb-20">
                                        <p class="font-lg limitar-lineas-4" id="descripcionProducto"></p>
                                    </div>
                                    <div class="mb-10" id="viewVisitaTienda">
                                    </div>
                                    <div class="detail-extralink" id="accionesCompraProducto">
                                        <div class="detail-qty border radius">
                                            <a href="#" class="qty-down"><i class="fi-rs-angle-small-down"></i></a>
                                            <input type="text" name="quantity" class="qty-val" value="1" min="1">
                                            <a href="#" class="qty-up"><i class="fi-rs-angle-small-up"></i></a>
                                        </div>
                                        <div class="product-extra-link2">
                                            <button type="submit" class="btn btn-sm button button-add-to-cart d-flex align-items-center">
                                                <img alt="Carrito de compra" src="img/carrito_transparente.png" style="width: 30px; height: 24px;" class="me-2" />
                                                Agregar al carrito
                                            </button> <!-- <a aria-label="Add To Wishlist" class="action-btn hover-up" href="shop-wishlist.html"><i class="fi-rs-heart"></i></a>
                                                <a aria-label="Compare" class="action-btn hover-up" href="shop-compare.html"><i class="fi-rs-shuffle"></i></a> -->
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <h6 class="fw-bold text-secondary" id="tituloEntregaProducto">FULMUV te envía a domicilio</h6>
                                    </div>
                                    <div class="mb-25 d-none" id="mensajeEntregaProductoWrap">
                                        <div class="alert alert-info mb-3" id="mensajeEntregaProducto"></div>
                                        <a id="btnWhatsAppEntrega" class="btn btn-whatsapp d-inline-flex align-items-center d-none">
                                            <i class="fab fa-whatsapp me-2"></i>
                                            Contactar por WhatsApp
                                        </a>
                                    </div>
                                    <div class="row" id="infoExtraProducto">
                                        <div class="col-6 mb-2">
                                            <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-center" style="min-height: 85px;">
                                                <div class="small text-muted mb-1" style="font-size: 0.75rem; text-transform: capitalize;">Marca</div>
                                                <div class="fw-bold" style="font-size: 0.95rem; line-height: 1.2;" id="marcaProducto"></div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-center" style="min-height: 85px;">
                                                <div class="small text-muted mb-1" style="font-size: 0.75rem; text-transform: capitalize;">Modelo</div>
                                                <div class="fw-bold" style="font-size: 0.95rem; line-height: 1.2;" id="modeloProducto"></div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-center" style="min-height: 85px;">
                                                <div class="small text-muted mb-1" style="font-size: 0.75rem; text-transform: capitalize;">Categorías</div>
                                                <div class="fw-bold" style="font-size: 0.95rem; line-height: 1.2;" id="categoriaProducto"></div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-center" style="min-height: 85px;">
                                                <div class="small text-muted mb-1" style="font-size: 0.75rem; text-transform: capitalize;">Subcategorías</div>
                                                <div class="fw-bold" style="font-size: 0.95rem; line-height: 1.2;" id="subcategoriaProducto"></div>
                                            </div>
                                        </div>
                                    </div>
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

                                    </div>
                                    <div class="tab-pane fade" id="Vendor-info">
                                        <div class="vendor-logo d-flex mb-30">
                                            <img id="imagenEmpresa">
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
                                <h2 class="section-title style-1 mb-30">Productos Relacionados</h2>
                            </div>
                            <div class="tab-content" id="myTabContent-1">
                                <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                                    <div class="carausel-4-columns-cover arrow-center position-relative">
                                        <div class="slider-arrow slider-arrow-2 carausel-4-columns-arrow" id="carausel-4-columns-arrows-oferta"></div>
                                        <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-oferta">

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
<script src="js/detalle_productos.js?v=<?php echo $timer; ?>"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&callback=initMap" async defer></script>



<script>

    $(document).on("input", ".qty-val", function() {
        let v = parseInt($(this).val(), 10);
        if (isNaN(v) || v < 1) v = 1;
        $(this).val(v);
    });
</script>
