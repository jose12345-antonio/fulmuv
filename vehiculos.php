<?php
include 'includes/header.php';
?>
<link rel="canonical" href="https://fulmuv.com/vehiculos.php">

<style>
    .fulmuv-filter-accordion .accordion-item {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 14px;
        background: #fff;
    }

    #filtersAccordionVehiculos {
        display: flex;
        flex-direction: column;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(10) {
        order: 1;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(2) {
        order: 2;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(3) {
        order: 3;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(4) {
        order: 4;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(5) {
        order: 5;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(6) {
        order: 6;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(7) {
        order: 7;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(8) {
        order: 8;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(9) {
        order: 9;
    }

    #filtersAccordionVehiculos>.accordion-item:nth-child(1) {
        order: 10;
    }

    .fulmuv-filter-accordion .accordion-button {
        font-weight: 700;
        font-size: 16px;
        color: #111827;
        background: #fff;
        box-shadow: none;
        padding: 14px 16px;
    }

    .fulmuv-filter-accordion .accordion-button:not(.collapsed) {
        color: #004e60;
        background: #f8fafc;
    }

    .fulmuv-filter-accordion .accordion-button:focus {
        box-shadow: none;
    }

    .fulmuv-filter-accordion .accordion-body {
        padding: 14px 16px 10px;
    }

    .product-img {
        position: relative;
    }

    .product-img-zoom a {
        position: relative;
        display: block;
        width: 100%;
        height: 200px;
    }

    .product-img img.default-img,
    .product-img img.hover-img {
        display: block;
        width: 100%;
        height: 200px;
        object-fit: contain;
        position: absolute;
        inset: 0;
        transition: opacity .08s ease, visibility .08s ease;
        /* 👈 inmediato */
    }

    .product-img img.default-img {
        opacity: 1;
        visibility: visible;
        z-index: 2;
    }

    /* Por defecto: solo principal */
    .product-img img.hover-img {
        opacity: 0;
        visibility: hidden;
        z-index: 1;
        transition-delay: .08s;
    }

    /* Hover: ocultar principal / mostrar hover al instante */
    .product-img-action-wrap:hover img.default-img {
        opacity: 0;
        visibility: hidden;
        transition-delay: 0s;
    }

    .product-img-action-wrap:hover img.hover-img {
        opacity: 1;
        visibility: visible;
        z-index: 2;
        transition-delay: .08s;
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
        transition: opacity .08s ease, visibility .08s ease !important;
    }
</style>

<div class="container">
    <div class="archive-header-2 text-center mt-20">
        <!-- <h1 class="display-2 mb-50">Lista de Productos</h1> -->
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="sidebar-widget-2 widget_search mb-20">
                    <div class="fulmuv-pgsearch-shell">
                        <span class="fulmuv-pgsearch-brain" aria-hidden="true">
                            <i class="fa-solid fa-brain"></i>
                        </span>
                        <input id="searchInput" type="text" class="fulmuv-pgsearch-input" placeholder="Buscar por Nombre del Vehículo" autocomplete="off" />
                        <button type="button" class="fulmuv-pgsearch-clear" aria-label="Limpiar búsqueda">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row flex-row-reverse" style="transform: none;">
        <div class="mobile-filterbar d-lg-none mb-3 justify-content-end align-items-end d-flex">
            <button type="button" class="btn btn-primary d-flex align-items-center justify-content-between"
                id="btnToggleMobileFilters">
                <span class="d-flex align-items-center gap-2">
                    <i class="fi-rs-search"></i>
                    <span class="text-white fw-bold">Búsqueda y filtros</span>
                </span>
            </button>
        </div>
        <div class="col-lg-4-5">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <h5>Encontramos <strong class="text-brand" id="totalProductosGeneral"></strong> vehículos para ti!</h5>
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
                                <span> 40 <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-show">
                            <ul>
                                <li><a href="#" data-value="20">20</a></li>
                                <li><a class="active" href="#" data-value="40">40</a></li>
                                <li><a href="#" data-value="60">60</a></li>
                                <li><a href="#" data-value="80">80</a></li>
                                <li><a href="#" data-value="100">100</a></li>
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
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar fulmuv-sidebar-col" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">

            <div class="fulmuv-filter-panel" id="mobileFilters">
                <div class="accordion fulmuv-filter-accordion mt-30" id="filtersAccordionVehiculos">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingPrecioVehiculos">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrecioVehiculos" aria-expanded="false" aria-controls="collapsePrecioVehiculos">Filtrar por precio</button>
                        </h2>
                        <div id="collapsePrecioVehiculos" class="accordion-collapse collapse" aria-labelledby="headingPrecioVehiculos" data-bs-parent="#filtersAccordionVehiculos">
                            <div class="accordion-body">
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
                        </div>
                    </div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingMarcaVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMarcaVehiculos" aria-expanded="false" aria-controls="collapseMarcaVehiculos">Marca</button></h2><div id="collapseMarcaVehiculos" class="accordion-collapse collapse" aria-labelledby="headingMarcaVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-marca-veh" class="mt-2"></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingModeloVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModeloVehiculos" aria-expanded="false" aria-controls="collapseModeloVehiculos">Modelo</button></h2><div id="collapseModeloVehiculos" class="accordion-collapse collapse" aria-labelledby="headingModeloVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-modelos" class="mt-2"></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingAnioVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnioVehiculos" aria-expanded="false" aria-controls="collapseAnioVehiculos">Año</button></h2><div id="collapseAnioVehiculos" class="accordion-collapse collapse" aria-labelledby="headingAnioVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div class="row g-2"><div class="col-6"><input id="anioMin" type="number" class="form-control" placeholder="Desde" min="1900" max="2100"></div><div class="col-6"><input id="anioMax" type="number" class="form-control" placeholder="Hasta" min="1900" max="2100"></div></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingCondicionVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCondicionVehiculos" aria-expanded="false" aria-controls="collapseCondicionVehiculos">Condición</button></h2><div id="collapseCondicionVehiculos" class="accordion-collapse collapse" aria-labelledby="headingCondicionVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-condicion" class="mt-2"></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingTipoAutoVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTipoAutoVehiculos" aria-expanded="false" aria-controls="collapseTipoAutoVehiculos">Tipo de auto</button></h2><div id="collapseTipoAutoVehiculos" class="accordion-collapse collapse" aria-labelledby="headingTipoAutoVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-tipo-auto" class="mt-2"></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingColorVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseColorVehiculos" aria-expanded="false" aria-controls="collapseColorVehiculos">Color</button></h2><div id="collapseColorVehiculos" class="accordion-collapse collapse" aria-labelledby="headingColorVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-color" class="mt-2"></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingTapiceriaVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTapiceriaVehiculos" aria-expanded="false" aria-controls="collapseTapiceriaVehiculos">Tapicería</button></h2><div id="collapseTapiceriaVehiculos" class="accordion-collapse collapse" aria-labelledby="headingTapiceriaVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-tapiceria" class="mt-2"></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingClimatizacionVehiculos"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClimatizacionVehiculos" aria-expanded="false" aria-controls="collapseClimatizacionVehiculos">Climatización</button></h2><div id="collapseClimatizacionVehiculos" class="accordion-collapse collapse" aria-labelledby="headingClimatizacionVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-climatizacion" class="mt-2"></div></div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header" id="headingReferenciasVehiculos"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReferenciasVehiculos" aria-expanded="true" aria-controls="collapseReferenciasVehiculos">Referencia</button></h2><div id="collapseReferenciasVehiculos" class="accordion-collapse collapse show" aria-labelledby="headingReferenciasVehiculos" data-bs-parent="#filtersAccordionVehiculos"><div class="accordion-body"><div id="filtro-referencias" class="mt-2"></div></div></div></div>
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





<?php include 'includes/mobile_bottom_nav.php'; ?>
<?php
include 'includes/footer.php';
?>
<script src="js/vehiculos.js?v2.1.1"></script>
