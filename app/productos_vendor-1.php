<?php
include 'includes/header.php';

$id_empresa = $_GET["q"];

echo '<input type="hidden" placeholder="" id="id_empresa" class="form-control" value=' . $id_empresa . ' />';
?>
<link rel="canonical" href="https://fulmuv.com/productos_vendor.php?q=<?php echo $id_empresa; ?>">

<style>
    /* ==== GALERÍA EN GRID 4xN ==== */
    .gallery-wrapper {
        margin-top: 40px;
    }

    .gallery-header {
        text-align: center;
        margin-bottom: 25px;
    }

    /* Contenedor de las cartas */
    .gallery-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        /* espacio entre cartas */
        justify-content: flex-start;
    }

    /* Carta */
    .gallery-item {
        position: relative;
        flex: 0 0 calc(25% - 12px);
        /* 4 por fila en desktop */
        max-width: calc(25% - 12px);
        height: 360px;
        /* tamaño específico */
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.15);
        cursor: pointer;
        background: #000;
    }

    /* Imagen dentro de la carta */
    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Overlay con info */
    .gallery-info {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        /* un poco oscuro por defecto */
        color: #fff;
        opacity: 0;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        transition: opacity 0.3s ease, background 0.3s ease;
    }

    .gallery-info h4 {
        font-size: 15px;
        margin: 0 0 4px 0;
        font-weight: 600;
        color: #FFFFFF;
        transition: font-size 0.3s ease;
    }

    .gallery-info p {
        font-size: 13px;
        margin: 0;
        color: #FFFFFF;
        opacity: 0.95;
        transition: font-size 0.3s ease;
    }

    /* Hover: más oscuro y letras más grandes */
    .gallery-item:hover .gallery-info {
        opacity: 1;
        background: rgba(15, 23, 42, 0.9);
    }

    .gallery-item:hover .gallery-info h4 {
        font-size: 18px;
    }

    .gallery-item:hover .gallery-info p {
        font-size: 14px;
    }

    /* Responsivo: 3 por fila */
    @media (max-width: 1200px) {
        .gallery-item {
            flex: 0 0 calc(33.333% - 12px);
            max-width: calc(33.333% - 12px);
        }
    }

    /* 2 por fila */
    @media (max-width: 768px) {
        .gallery-item {
            flex: 0 0 calc(50% - 12px);
            max-width: calc(50% - 12px);
        }
    }

    /* 1 por fila */
    @media (max-width: 480px) {
        .gallery-item {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    .contact-line {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-top: .4rem;
        font-size: 13px;
    }

    .icon-circle {
        width: 30px;
        height: 30px;
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

    .contact-line a {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 600;
    }

    .contact-line a:hover {
        text-decoration: underline;
    }
</style>

<div class="container">
    <div class="archive-header-2 text-center mt-30">
        <!-- <h1 class="display-2 mb-50">Lista de Productos</h1> -->
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="sidebar-widget-2 widget_search mb-50">
                    <div class="search-form">
                        <form action="#">
                            <input type="text" placeholder="Buscar por Nombre de Producto o Servicio" />
                            <button type="submit"><i class="fi-rs-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row flex-row-reverse" style="transform: none;">
        <div class="col-lg-4-5">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <p>¡Encontramos <strong class="text-brand" id="totalProductosGeneral"></strong> artículos para ti!</p>
                </div>
                <div class="sort-by-product-area">
                    <div class="sort-by-cover d-flex justify-content-center align-items-center me-2">
                        <div>
                            <button type="button" id="btnUbicacion" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <i class="fi-rs-marker me-1"></i> Cambiar ubicación
                            </button>
                        </div>
                    </div>
                    <div class="sort-by-cover mr-10">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps"></i>Show:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> 20 <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-show">
                            <ul>
                                <li><a class="active" href="#" data-value="20">20</a></li>
                                <li><a href="#" data-value="40">40</a></li>
                                <li><a href="#" data-value="60">60</a></li>
                                <li><a href="#" data-value="80">80</a></li>
                                <li><a href="#" data-value="100">100</a></li>
                                <li><a href="#" data-value="120">120</a></li>
                                <li><a href="#" data-value="all">All</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="sort-by-cover">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps-sort"></i>Ordenado por:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> Todos <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-order">
                            <ul>
                                <li><a class="active" href="#" data-value="todos">Todos</a></li>
                                <li><a href="#" data-value="menor">Menor productos</a></li>
                                <li><a href="#" data-value="mayor">Mayor productos</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row product-grid">
            </div>
            <!--product grid-->
            <div class="pagination-area mt-20 mb-20">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start">
                        <li class="page-item">
                            <a class="page-link" href="#"><i class="fi-rs-arrow-small-left"></i></a>
                        </li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item active"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link dot" href="#">...</a></li>
                        <li class="page-item"><a class="page-link" href="#">6</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#"><i class="fi-rs-arrow-small-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            <section class="section-padding pb-5 d-none">
                <div class="section-title">
                    <h3 class="">Deals Of The Day</h3>
                    <a class="show-all" href="shop-grid-right.html">
                        All Deals
                        <i class="fi-rs-angle-right"></i>
                    </a>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-5.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/12/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">195</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">07</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Seeds of Change Organic Quinoa, Brown</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">NestFood</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$32.85</span>
                                            <span class="old-price">$33.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-6.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2026/04/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">316</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">07</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Perdue Simply Smart Organics Gluten</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Old El Paso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$24.85</span>
                                            <span class="old-price">$26.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-lg-block">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-7.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2027/03/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">650</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">07</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Signature Wood-Fired Mushroom</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Progresso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$12.85</span>
                                            <span class="old-price">$13.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-xl-block">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-8.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/02/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Simply Lemonade with Raspberry Juice</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Yoplait</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$15.85</span>
                                            <span class="old-price">$16.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!--End Deals-->
        </div>
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">


            <!-- Fillter By Price -->


            <div class="theiaStickySidebar" style="padding-top: 0px; padding-bottom: 1px; position: static; transform: none; top: 0px; left: 265.351px;">
                <div class="sidebar-widget widget-store-info mb-30 border-0">
                    <div class="mb-3" style="height: 200px; width: 200px">
                        <img id="imagenEmpresa" alt="" style="height: 100%; width: 100%; object-fit: contain">
                    </div>
                    <div class="vendor-info">
                        <div class="product-category">
                            <span class="text-muted" id="fechaInicioEmpresa"></span>
                        </div>
                        <h4 class="mb-5"><a href="productos_vendor.php?q=<?php echo $id_empresa; ?>" class="text-heading" id="nombreEmpresa"></a></h4>
                        <div class="vendor-info">
                            <ul class="font-sm mb-20 d-none">
                                <li><img class="mr-5" src="themelading/nest-frontend/assets/imgs/theme/icons/icon-location.svg" alt=""><strong>Dirección: </strong> <span id="direccionEmpresa"></span></li>
                                <li><img class="mr-5" src="themelading/nest-frontend/assets/imgs/theme/icons/icon-contact.svg" alt=""><strong>Teléfono:</strong><span id="telefonoEmpresa"></span></li>

                            </ul>
                            <!-- <?php if (isset($_SESSION["id_usuario"])): ?>
                                <a href="#" class="btn btn-xs">Contactar <i class="fi-rs-arrow-small-right"></i></a>
                            <?php endif; ?> -->

                            <a href="#" id="btnWhatsSidebar" class="btn btn-xs">
                                Comunícate al WhatsApp <i class="fi-rs-arrow-small-right"></i>
                            </a>

                            <div class="mt-2" id="contactosSidebar"></div>

                        </div>
                    </div>
                </div>
                <div class="categories-dropdown-wrap style-2 font-heading mt-30 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Marca</h5>
                    <div id="filtro-marca" class="mt-2"></div>
                </div>
                <div class="categories-dropdown-wrap style-2 font-heading mt-30 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Modelo</h5>
                    <div id="filtro-modelo" class="mt-2"></div>
                </div>

                <div class="categories-dropdown-wrap style-2 font-heading mt-30 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Categorías</h5>
                    <div id="filtro-categorias" class="mt-2"></div>
                </div>

                <div id="subcats-box" class="categories-dropdown-wrap style-2 font-heading mt-30 p-2" style="display:none;">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Sub categorías</h5>
                    <div id="filtro-sub-categorias" class="mt-2"></div>
                </div>

                <div class="sidebar-widget price_range range mb-30 mt-4">
                    <h5 class="section-title style-1 mb-30">Filtrar por precio</h5>
                    <div class="price-filter">
                        <div class="price-filter-inner">
                            <div id="slider-range" class="mb-20"></div>
                            <div class="d-flex justify-content-between">
                                <div class="caption">From: <strong id="slider-range-value1" class="text-brand"></strong></div>
                                <div class="caption">To: <strong id="slider-range-value2" class="text-brand"></strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="price-filter">
                    <div class="price-filter-inner">
                        <div id="slider-range-descuento" class="mb-20"></div>
                        <div class="d-flex justify-content-between">
                            <div class="caption">From: <strong id="slider-range-value1-descuento" class="text-brand"></strong></div>
                            <div class="caption">To: <strong id="slider-range-value2-descuento" class="text-brand"></strong></div>
                        </div>
                    </div>
                </div> -->
                </div>
                <!-- <div class="sidebar-widget widget-category-2 mb-30 p-3">
                    <h5 class="section-title style-1 mb-30">Categorías</h5>
                    <ul id="listCategoriasEmpresa">
                        
                    </ul>
                </div> -->
                <div class="sidebar-widget p-3 range mb-30 d-none">
                    <h5 class="section-title style-1 mb-30">Filtrar por precio</h5>
                    <div class="price-filter">
                        <div class="price-filter-inner">
                            <div id="slider-range" class="mb-20 noUi-target noUi-ltr noUi-horizontal noUi-background">
                                <div class="noUi-base">
                                    <div class="noUi-origin noUi-connect" style="left: 25%;">
                                        <div class="noUi-handle noUi-handle-lower"></div>
                                    </div>
                                    <div class="noUi-origin noUi-background" style="left: 50%;">
                                        <div class="noUi-handle noUi-handle-upper"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="caption">Desde: <strong id="slider-range-value1" class="text-brand">$0</strong></div>
                                <div class="caption">Hasta: <strong id="slider-range-value2" class="text-brand">$0</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group">
                        <div class="list-group-item mb-10 mt-10">
                            <label class="fw-900">Subcategorías</label>
                            <div class="custome-checkbox">

                            </div>
                        </div>
                    </div>
                    <a class="btn btn-sm btn-default"><i class="fi-rs-filter mr-5"></i> Filtrar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="col-lg-12">
        <div class="gallery-wrapper" id="gallery-wrapper" style="display:none;">
            <div class="gallery-header">
                <h3>Galería de la empresa</h3>
                <p>Conoce algunos trabajos, productos e instalaciones</p>
            </div>

            <div class="gallery-grid" id="gallery-empresa"></div>
        </div>
    </div>
</div>

<!-- Modal Ubicación -->
<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalUbicacionLabel">
                    <i class="fi-rs-marker me-1"></i> Elige tu ubicación
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label mb-1">Provincia</label>
                    <select id="selectProvincia" class="form-control" required></select>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1">Cantón</label>
                    <select id="selectCanton" class="form-control" required></select>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" id="limpiarUbicacion">Limpiar ubicación</button>
                <button type="button" class="btn btn-primary" id="guardarUbicacion">Listo</button>
            </div>
        </div>
    </div>
</div>



<?php
include 'includes/footer.php';
?>
<script src="js/productos_vendor.js?v1.0.0.0.0.0.0.0.0.0.11"></script>