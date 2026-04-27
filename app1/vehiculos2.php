<?php
include 'includes/header.php';
?>
<link rel="canonical" href="https://fulmuv.com/vehiculos.php">

<style>
    .product-img {
        position: relative;
    }

    .product-img img.default-img,
    .product-img img.hover-img {
        display: block;
        width: 100%;
        height: auto;
        transition: none !important;
        /* 👈 inmediato */
    }

    /* Por defecto: solo principal */
    .product-img img.hover-img {
        opacity: 0;
        visibility: hidden;
        position: absolute;
        inset: 0;
    }

    /* Hover: ocultar principal / mostrar hover al instante */
    .product-img-action-wrap:hover img.default-img {
        opacity: 0;
        visibility: hidden;
    }

    .product-img-action-wrap:hover img.hover-img {
        opacity: 1;
        visibility: visible;
    }

    .product-img-zoom {
        height: 200px;
        /* igual a tus imágenes */
        overflow: hidden;
    }

    .product-img-zoom img {
        height: 200px;
        object-fit: contain;
    }

    .product-img-zoom img,
    .product-img-zoom img.default-img,
    .product-img-zoom img.hover-img {
        transition: none !important;
    }
</style>

<div class="container">
    <div class="archive-header-2 text-center mt-20">
        <!-- <h1 class="display-2 mb-50">Lista de Productos</h1> -->
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="sidebar-widget-2 widget_search mb-20">
                    <div class="search-form">
                        <form action="#">
                            <input id="searchInput" type="text" placeholder="Buscar por Nombre del Vehículo" />
                            <button type="submit"><i class="fi-rs-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row flex-row-reverse" style="transform: none;">
        <div class="mobile-filterbar d-lg-none mb-3 justify-content-end align-items-end d-flex">
            <button type="button" class="btn btn-primary d-flex align-items-center justify-content-between"
                id="btnToggleMobileFilters" data-bs-toggle="collapse" data-bs-target="#mobileFilters"
                aria-expanded="false" aria-controls="mobileFilters">
                <span class="d-flex align-items-center gap-2">
                    <i class="fi-rs-search"></i>
                    <span class="text-white fw-bold">Búsqueda y filtros</span>
                </span>
            </button>
        </div>
        <div class="col-lg-4-5">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <h5>Encontramos <strong class="text-brand" id="totalProductosGeneral"></strong> artículos para ti!</h5>
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
                                <li><a href="#" data-value="menor">Menor precio</a></li>
                                <li><a href="#" data-value="mayor">Mayor precio</a></li>
                                <li><a href="#" data-value="km_menor">Menor kilometraje</a></li>
                                <li><a href="#" data-value="km_mayor">Mayor kilometraje</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row product-grid g-2">
            </div>

            <input type="hidden" id="provincia_id_hidden">
            <input type="hidden" id="provincia_nombre_hidden">
            <input type="hidden" id="canton_id_hidden">
            <input type="hidden" id="canton_nombre_hidden">
            <!-- Paginación -->
            <div class="pagination-area mt-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-start"></ul>
                </nav>
            </div>
        </div>
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">

            <div class="collapse d-lg-block" id="mobileFilters">


                <!-- Precio -->
                <div class="sidebar-widget price_range range mb-3 mt-3">
                    <h5 class="section-title style-1 mb-2">Filtrar por precio</h5>
                    <div class="price-filter">
                        <div class="price-filter-inner">
                            <div id="slider-range" class="mb-2"></div>
                            <div class="d-flex justify-content-between">
                                <div class="caption">From: <strong id="slider-range-value1" class="text-brand"></strong></div>
                                <div class="caption">To: <strong id="slider-range-value2" class="text-brand"></strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Marca</h5>
                    <div id="filtro-marca-veh" class="mt-2"></div>
                </div>
                <!-- ====== NUEVOS FILTROS ====== -->
                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Modelo</h5>
                    <div id="filtro-modelos" class="mt-2"></div>
                </div>
                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Año</h5>
                    <div class="row g-2">
                        <div class="col-6"><input id="anioMin" type="number" class="form-control" placeholder="Desde" min="1900" max="2100"></div>
                        <div class="col-6"><input id="anioMax" type="number" class="form-control" placeholder="Hasta" min="1900" max="2100"></div>
                    </div>
                </div>
                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Condición</h5>
                    <div id="filtro-condicion" class="mt-2"></div>
                </div>
                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Tipo de auto</h5>
                    <div id="filtro-tipo-auto" class="mt-2"></div>
                </div>

                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Color</h5>
                    <div id="filtro-color" class="mt-2"></div>
                </div>
                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Tapicería</h5>
                    <div id="filtro-tapiceria" class="mt-2"></div>
                </div>
                <div class="categories-dropdown-wrap style-2 font-heading mt-3 p-2">
                    <h5 class="border-bottom mb-2 mt-0 pb-2">Climatización</h5>
                    <div id="filtro-climatizacion" class="mt-2"></div>
                </div>

            </div>
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
<script src="js/vehiculos.js?v1.0.0.0.0.0.0.0.0.0.1.1.0.0.0.5"></script>