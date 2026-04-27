<?php
include 'includes/header.php';

?>
<link rel="canonical" href="https://fulmuv.com/ofertas_imperdibles.php">

<style>
    .fulmuv-filter-accordion .accordion-item {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 14px;
        background: #fff;
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

    /* texto superior derecha con cantidad vendida */
    .sold-pill {
        position: absolute;
        top: 0px;
        right: 0px;
        z-index: 0;
        background: #253D4E;
        /* discreto, legible */ 
        color: #fff;
        padding: 3px 8px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
    }

    @media (max-width:576px) {
        .sold-pill {
            font-size: 11px;
            top: 6px;
            right: 6px;
        }
    }

    /* por si tu theme no la trae */
    .product-img-action-wrap {
        position: relative;
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
                        <input type="text" class="fulmuv-pgsearch-input" placeholder="Buscar por Nombre de Producto" autocomplete="off" />
                        <button type="button" class="fulmuv-pgsearch-clear" aria-label="Limpiar búsqueda">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row flex-row-reverse" style="transform: none;">
        <!-- Barra móvil: búsqueda + filtros -->
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
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row product-grid">
            </div>

            <input type="hidden" id="provincia_id_hidden">
            <input type="hidden" id="provincia_nombre_hidden">
            <input type="hidden" id="canton_id_hidden">
            <input type="hidden" id="canton_nombre_hidden">
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
        </div>
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar fulmuv-sidebar-col" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
            <div class="fulmuv-filter-panel" id="mobileFilters">
                <div class="accordion fulmuv-filter-accordion mt-30" id="filtersAccordionOfertas">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingMarcaOfertas">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMarcaOfertas" aria-expanded="true" aria-controls="collapseMarcaOfertas">
                                Marca
                            </button>
                        </h2>
                        <div id="collapseMarcaOfertas" class="accordion-collapse collapse show" aria-labelledby="headingMarcaOfertas" data-bs-parent="#filtersAccordionOfertas">
                            <div class="accordion-body"><div id="filtro-marca"></div></div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingModeloOfertas">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModeloOfertas" aria-expanded="false" aria-controls="collapseModeloOfertas">
                                Modelo
                            </button>
                        </h2>
                        <div id="collapseModeloOfertas" class="accordion-collapse collapse" aria-labelledby="headingModeloOfertas" data-bs-parent="#filtersAccordionOfertas">
                            <div class="accordion-body"><div id="filtro-modelo"></div></div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCategoriasOfertas">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategoriasOfertas" aria-expanded="false" aria-controls="collapseCategoriasOfertas">
                                Categorías
                            </button>
                        </h2>
                        <div id="collapseCategoriasOfertas" class="accordion-collapse collapse" aria-labelledby="headingCategoriasOfertas" data-bs-parent="#filtersAccordionOfertas">
                            <div class="accordion-body"><div id="filtro-categorias"></div></div>
                        </div>
                    </div>
                    <div id="subcats-box" class="accordion-item" style="display:none;">
                        <h2 class="accordion-header" id="headingSubcategoriasOfertas">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubcategoriasOfertas" aria-expanded="false" aria-controls="collapseSubcategoriasOfertas">
                                Sub categorías
                            </button>
                        </h2>
                        <div id="collapseSubcategoriasOfertas" class="accordion-collapse collapse" aria-labelledby="headingSubcategoriasOfertas" data-bs-parent="#filtersAccordionOfertas">
                            <div class="accordion-body"><div id="filtro-sub-categorias"></div></div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingPrecioOfertas">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrecioOfertas" aria-expanded="false" aria-controls="collapsePrecioOfertas">
                                Filtrar por precio
                            </button>
                        </h2>
                        <div id="collapsePrecioOfertas" class="accordion-collapse collapse" aria-labelledby="headingPrecioOfertas" data-bs-parent="#filtersAccordionOfertas">
                            <div class="accordion-body">
                                <div class="price-filter">
                                    <div class="price-filter-inner">
                                        <div id="slider-range" class="mb-20"></div>
                                        <div class="d-flex justify-content-between">
                                            <div class="caption">From: <strong id="slider-range-value1" class="text-brand"></strong></div>
                                            <div class="caption">To: <strong id="slider-range-value2" class="text-brand"></strong></div>
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
<script src="js/ofertas_imperdibles.js?v1.0.0.0.0.0.0.0.0.0.1.1.0.6"></script>
