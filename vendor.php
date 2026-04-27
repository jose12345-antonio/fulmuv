<?php
include 'includes/header.php';
?>

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

    .vendor-mobile-shell {
        background: #fcfcfc;
    }

    .search-container-modern {
        padding: 15px 0;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(14px);
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    }

    .vendor-mobile-shell .container-fluid {
        padding: 0 10px;
    }

    .input-search-modern {
        background-color: #f1f3f4 !important;
        border: 1px solid transparent !important;
        border-radius: 16px !important;
        padding: 10px 15px !important;
        font-size: 14px;
        height: 48px;
        color: #111111 !important;
        box-shadow: none !important;
    }

    .input-search-modern::placeholder {
        color: #666666;
    }

    .input-search-modern:focus {
        border-color: #004e60 !important;
        background-color: #ffffff !important;
    }

    .btn-filter-modern {
        background: linear-gradient(135deg, #004e60 0%, #0f766e 100%) !important;
        color: white !important;
        border-radius: 16px !important;
        min-width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        box-shadow: 0 12px 24px rgba(0, 78, 96, 0.24);
        transition: transform 0.2s ease;
    }

    .btn-filter-modern i {
        font-size: 18px;
    }

    .results-count {
        color: #111111;
    }

    .vendor-grid-mobile {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
        align-items: stretch;
    }

    .vendor-card-container {
        height: 100%;
    }

    .vendor-card-modern {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        height: 100%;
        display: flex;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .vendor-link {
        text-decoration: none !important;
        color: inherit;
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
    }

    .vendor-img-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f1f3f4;
    }

    .vendor-main-img {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 200px;
        object-fit: contain;
        background: #f1f3f4;
    }

    .badge-verificacion-flotante {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 40px;
        height: 40px;
        z-index: 3;
        background: #ffffff;
        border-radius: 50%;
        padding: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .badge-verificacion-flotante img {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }

    .vendor-verification-inline {
        width: 40px;
        height: 40px;
        object-fit: contain;
        flex: 0 0 40px;
    }

    .vendor-info-modern {
        padding: 15px 12px;
        flex: 1 1 auto;
    }

    .vendor-title-modern {
        font-size: 14px;
        font-weight: 800;
        margin: 5px 0;
        color: #111111;
    }

    .vendor-location-modern {
        font-size: 10px;
        color: #888888;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .vendor-items-count {
        font-size: 11px;
        color: #666666;
        margin-top: 2px;
    }

    .btn-circle-action {
        width: 32px;
        height: 32px;
        background-color: #004e60;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-circle-action i {
        font-size: 20px;
        line-height: 1;
    }

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.28);
        backdrop-filter: blur(3px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        z-index: 1200;
    }

    .filters-overlay.is-open {
        opacity: 1;
        visibility: visible;
    }

    .filter-panel-modern {
        position: fixed;
        top: 0;
        right: 0;
        width: min(420px, 100%);
        height: 100vh;
        background: #ffffff;
        border-left: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        z-index: 1201;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }

    .filter-panel-modern.is-open {
        transform: translateX(0);
    }

    .filter-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    }

    .filter-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #111111;
    }

    .filter-panel-subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: #666666;
    }

    .filter-panel-close {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #f1f3f4;
        color: #111111;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .filter-panel-body {
        flex: 1;
        overflow-y: auto;
    }

    .filter-layout-modern {
        display: grid;
        grid-template-columns: 138px minmax(0, 1fr);
        min-height: 100%;
    }

    .filter-nav-modern {
        padding: 14px 10px 18px;
        background: linear-gradient(180deg, #f1f3f4 0%, #ffffff 100%);
        border-right: 1px solid rgba(15, 23, 42, 0.08);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-nav-item {
        width: 100%;
        text-align: left;
        border: 1px solid transparent;
        background: transparent;
        border-radius: 18px;
        padding: 14px 12px;
        color: #666666;
    }

    .filter-nav-item.is-active {
        background: #ffffff;
        border-color: rgba(15, 23, 42, 0.08);
        color: #111111;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transform: translateX(4px);
    }

    .filter-nav-label {
        display: block;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.25;
    }

    .filter-nav-meta,
    .filter-summary-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        background: rgba(0, 78, 96, 0.10);
        color: #004e60;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-content-modern {
        padding: 18px 18px 20px;
    }

    .filter-detail-panel {
        display: none;
    }

    .filter-detail-panel.is-active {
        display: block;
    }

    .filter-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
        padding: 0 2px;
    }

    .filter-summary-text,
    .filter-section-copy,
    .filter-info-text {
        color: #666666;
    }

    .filter-section-heading {
        font-size: 24px;
        font-weight: 900;
        line-height: 1.05;
        margin-bottom: 6px;
        color: #111111;
    }

    .filter-block,
    .filter-info-card {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 22px;
        padding: 16px;
        margin-bottom: 14px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .filter-block-title,
    .filter-info-eyebrow {
        font-size: 13px;
        font-weight: 800;
        color: #004e60;
        margin-bottom: 12px;
    }

    .filter-chip {
        min-height: 42px;
        border-radius: 14px;
        background: #f1f3f4;
        border: 1px solid rgba(15, 23, 42, 0.08);
        color: #111111;
    }

    .filter-panel-footer {
        display: flex;
        gap: 10px;
        padding: 16px 20px 20px;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
        background: #ffffff;
    }

    .btn-filter-secondary,
    .btn-filter-primary {
        flex: 1;
        min-height: 46px;
        border-radius: 14px;
        font-weight: 700;
    }

    .btn-filter-secondary {
        background: #f1f3f4;
        color: #111111;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }

    .btn-filter-primary {
        background: linear-gradient(135deg, #004e60 0%, #0f766e 100%);
        color: #fff;
        border: none;
    }

    #filtro-categorias-panel {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        max-height: 420px;
        overflow-y: auto;
        margin: 0;
    }

    #filtro-categorias-panel > [class*="col-"] {
        width: 100%;
        padding: 0;
    }

    #filtro-categorias-panel .form-check {
        padding: 10px 12px;
        margin: 0;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 14px;
        width: 100%;
        min-height: 54px;
        display: flex;
        align-items: center;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }

    #filtro-categorias-panel .form-check-input {
        display: inline-block;
        margin-right: 8px;
    }

    #filtro-categorias-panel .form-check-label {
        display: inline-block;
        color: #111111;
    }

    .empty-state-modern {
        grid-column: 1 / -1;
        display: flex;
        justify-content: center;
        padding: 24px 0 8px;
    }

    .empty-state-card {
        width: min(100%, 520px);
        padding: 28px 24px;
        background: linear-gradient(180deg, #ffffff 0%, #f1f3f4 100%);
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        text-align: center;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 78, 96, 0.12);
        color: #004e60;
        font-size: 26px;
    }

    .empty-state-title {
        font-size: 20px;
        font-weight: 800;
        color: #111111;
        margin-bottom: 6px;
    }

    .empty-state-text {
        font-size: 14px;
        color: #666666;
        line-height: 1.6;
        margin: 0;
    }

    @media (max-width: 767px) {
        .filter-panel-modern {
            width: 100%;
        }

        .filter-panel-header,
        .filter-panel-footer {
            padding-left: 16px;
            padding-right: 16px;
        }

        .filter-layout-modern {
            grid-template-columns: 112px minmax(0, 1fr);
        }

        .filter-nav-modern {
            padding: 12px 8px 18px;
        }

        .filter-content-modern {
            padding: 16px 14px 18px;
        }

        .filter-section-heading {
            font-size: 22px;
        }
    }
</style>
<link rel="canonical" href="https://fulmuv.com/vendor.php">

<div class="container d-none d-lg-block">
    <div class="archive-header-2 text-center mt-30">
        <!-- <h2 class="display-2 mb-50">Lista de Proveedores</h2> -->
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="sidebar-widget-2 widget_search mb-50">
                    <div class="fulmuv-pgsearch-shell">
                        <span class="fulmuv-pgsearch-brain" aria-hidden="true">
                            <i class="fa-solid fa-brain"></i>
                        </span>
                        <input type="text" class="fulmuv-pgsearch-input" placeholder="Buscar por Nombre de Empresa" autocomplete="off" />
                        <button type="button" class="fulmuv-pgsearch-clear" aria-label="Limpiar búsqueda">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-10">
        <div class="col-12 col-lg-12 mx-auto">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <h5>Continúan incorporándose más empresas a nivel nacional, para ti</h5>
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
                                <span><i class="fi-rs-apps"></i>Ver:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> 12 <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-show">
                            <ul>
                                <li><a class="active" href="#" data-value="12">12</a></li>
                                <li><a href="#" data-value="24">24</a></li>
                                <li><a href="#" data-value="48">48</a></li>
                                <li><a href="#" data-value="72">72</a></li>
                                <li><a href="#" data-value="96">96</a></li>
                                <li><a href="#" data-value="120">120</a></li>
                                <li><a href="#" data-value="all">All</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="sort-by-cover">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps-sort"></i>Ordenar por:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> Todos <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-order">
                            <ul>
                                <li><a class="active" href="#" data-value="todos">Todos</a></li>
                                <li><a href="#" data-value="menor">Descendentes</a></li>
                                <li><a href="#" data-value="mayor">Ascendentes</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-filterbar d-lg-none mb-3 justify-content-end align-items-end d-flex">
        <button type="button" class="btn btn-primary d-flex align-items-center justify-content-between"
            id="btnToggleMobileFilters">
            <span class="d-flex align-items-center gap-2">
                <i class="fi-rs-search"></i>
                <span class="text-white fw-bold">Búsqueda y filtros</span>
            </span>
        </button>
    </div>
    <div class="row">
        <div class="col-lg-2 mt-2 fulmuv-sidebar-col">
            <div class="fulmuv-filter-panel" id="mobileFilters">
                <div class="accordion fulmuv-filter-accordion" id="filtersAccordionVendorList">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCategoriasVendorList">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategoriasVendorList" aria-expanded="true" aria-controls="collapseCategoriasVendorList">
                                Categorías
                            </button>
                        </h2>
                        <div id="collapseCategoriasVendorList" class="accordion-collapse collapse show" aria-labelledby="headingCategoriasVendorList" data-bs-parent="#filtersAccordionVendorList">
                            <div class="accordion-body">
                                <div id="filtro-categorias" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-10 mt-2">
            <div class="row vendor-grid">

            </div>
            <input type="hidden" id="provincia_id_hidden">
            <input type="hidden" id="provincia_nombre_hidden">
            <input type="hidden" id="canton_id_hidden">
            <input type="hidden" id="canton_nombre_hidden">
            <div class="pagination-area mt-20 mb-20">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start">

                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="vendor-mobile-shell d-lg-none">
    <div class="search-container-modern shadow-sm mb-0">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                    <input type="text" id="inputBusqueda" class="form-control input-search-modern" placeholder="Buscar empresas...">
                </div>
                <button class="btn-filter-modern" type="button" id="openFilterPanel" aria-controls="panelFiltros" aria-expanded="false">
                    <i class="fi-rs-filter"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="filters-overlay" id="filtersOverlay"></div>

    <aside class="filter-panel-modern" id="panelFiltros" aria-hidden="true">
        <div class="filter-panel-header">
            <div>
                <h4 class="filter-panel-title">Filtrar empresas</h4>
                <p class="filter-panel-subtitle">Organiza tu búsqueda por bloques claros y rápidos.</p>
            </div>
            <button class="filter-panel-close" type="button" id="closeFilterPanel" aria-label="Cerrar filtros">
                <i class="fi-rs-cross-small"></i>
            </button>
        </div>

        <div class="filter-panel-body">
            <div class="filter-layout-modern">
                <div class="filter-nav-modern">
                    <button type="button" class="filter-nav-item is-active" data-filter-target="ubicacion-orden">
                        <span class="filter-nav-label">Ubicación y orden</span>
                        <span class="filter-nav-meta" id="filterGroupCountLocation">0</span>
                    </button>
                    <button type="button" class="filter-nav-item" data-filter-target="categorias">
                        <span class="filter-nav-label">Categorías</span>
                        <span class="filter-nav-meta" id="filterGroupCountCategories">0</span>
                    </button>
                </div>
                <div class="filter-content-modern">
                    <div class="filter-summary">
                        <span class="filter-summary-text">Filtros activos</span>
                        <span class="filter-summary-badge" id="filterActiveCount">0</span>
                    </div>

                    <section class="filter-detail-panel is-active" data-filter-panel="ubicacion-orden">
                        <div class="filter-section-heading">Ubicación y orden</div>
                        <p class="filter-section-copy">Define desde dónde quieres explorar empresas y cómo quieres priorizar los resultados.</p>

                        <div class="filter-info-card">
                            <div class="filter-info-eyebrow"><i class="fi-rs-marker"></i> Contexto activo</div>
                            <div class="filter-info-title">Búsqueda geográfica y orden visual</div>
                            <p class="filter-info-text">Puedes cambiar provincia y cantón, y luego ordenar las empresas por cantidad de productos publicados.</p>
                        </div>

                        <div class="filter-block">
                            <div class="filter-block-title">Ubicación</div>
                            <button type="button" id="btnUbicacionPanel" class="btn filter-chip w-100 d-flex justify-content-between align-items-center px-3" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <span class="text-truncate small"><i class="fi-rs-marker me-1"></i>Cambiar ubicación</span>
                                <i class="fi-rs-angle-small-right"></i>
                            </button>
                        </div>

                        <div class="filter-block">
                            <div class="filter-block-title">Orden de resultados</div>
                            <select class="form-select filter-chip" id="selectOrderPanel">
                                <option value="todos">Por defecto</option>
                                <option value="menor">Menor cantidad de productos</option>
                                <option value="mayor">Mayor cantidad de productos</option>
                            </select>
                        </div>
                    </section>

                    <section class="filter-detail-panel" data-filter-panel="categorias">
                        <div class="filter-section-heading">Categorías</div>
                        <p class="filter-section-copy">Filtra empresas por su categoría principal para enfocarte solo en el tipo de proveedor que necesitas.</p>

                        <div class="filter-info-card">
                            <div class="filter-info-eyebrow"><i class="fi-rs-apps"></i> Navegación inteligente</div>
                            <div class="filter-info-title">Selección rápida por rubro</div>
                            <p class="filter-info-text">Marca una o varias categorías y el listado se actualizará al instante, sin salir del panel.</p>
                        </div>

                        <div class="filter-block">
                            <div class="filter-block-title">Categorías disponibles</div>
                            <div class="row" id="filtro-categorias-panel"></div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="filter-panel-footer">
            <button type="button" class="btn btn-filter-secondary" id="clearFiltersPanel">Limpiar</button>
            <button type="button" class="btn btn-filter-primary" id="minimizeFilterPanel">
                <span>Mostrar <strong id="filterResultsCount">0</strong> resultados</span>
            </button>
        </div>
    </aside>

    <div class="container-fluid mt-3">
        <div class="results-count mb-3 ms-1" id="countVendedores" style="font-size: 16px; font-weight: 700;">
            Encontramos 0 resultados
        </div>

        <div class="vendor-grid-mobile" id="listaVendedoresContainer"></div>

        <div class="pagination-area mt-4 pb-5">
            <nav>
                <ul class="pagination justify-content-center"></ul>
            </nav>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardarUbicacion">Listo</button>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/mobile_bottom_nav.php'; ?>
<?php
include 'includes/footer.php';
?>
<script src="js/vendor.js?v1.1.9"></script>
 