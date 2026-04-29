<?php
include 'includes/header.php';
$timer = time();

$search = $_GET["search"] ?? "";
?>

<input type="hidden" id="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">

<style>
    .smart-results-shell {
        padding: 24px 0 56px;
        background: #fff;
    }

    .smart-results-topbar {
        margin-bottom: 10px;
    }

    .smart-results-layout {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .smart-results-sidebar {
        position: sticky;
        top: 120px;
    }

    .smart-filter-card,
    .smart-results-main {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        box-shadow: none;
    }

    .smart-filter-card {
        margin-bottom: 14px;
        overflow: hidden;
    }

    .smart-filter-toggle {
        width: 100%;
        border: 0;
        background: #fff;
        padding: 18px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 700;
    }

    .smart-filter-toggle i {
        color: #334155;
        transition: transform .2s ease;
    }

    .smart-filter-card.is-open .smart-filter-toggle i {
        transform: rotate(180deg);
    }

    .smart-filter-body {
        display: none;
        border-top: 1px solid #eef2f7;
        padding: 12px 14px 16px;
        background: #fff;
    }

    .smart-filter-card.is-open .smart-filter-body {
        display: block;
    }

    .smart-filter-search {
        width: 100%;
        height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0 14px;
        margin-bottom: 12px;
    }

    .smart-filter-search:focus,
    .smart-toolbar-select:focus,
    .smart-location-select:focus {
        outline: none;
        border-color: #0d748a;
        box-shadow: 0 0 0 4px rgba(13, 116, 138, 0.10);
    }

    .smart-filter-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 410px;
        overflow: auto;
        padding-right: 4px;
        background: transparent;
        border: 0;
        border-radius: 0;
    }

    .smart-filter-option {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        color: #334155;
    }

    .smart-filter-option input {
        width: 16px;
        height: 16px;
        accent-color: #0d9488;
    }

    .smart-filter-empty {
        color: #94a3b8;
        font-size: 14px;
    }

    .smart-price-values {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 14px;
        color: #0f172a;
        font-weight: 700;
    }

    .smart-price-values span {
        color: #004e60;
    }

    #smart-price-slider {
        margin: 12px 4px 0;
    }

    .smart-results-main {
        padding: 0;
        overflow: visible;
        background: transparent;
        border: 0;
    }

    .smart-results-header {
        padding: 10px 0 24px;
    }

    .smart-results-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .smart-results-title {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
    }

    .smart-results-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .smart-toolbar-btn,
    .smart-toolbar-select,
    .smart-location-select {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #dbe4ee;
        background: #fff;
        color: #334155;
        padding: 0 14px;
        font-size: 14px;
        appearance: auto;
    }

    .smart-toolbar-btn {
        background: #004e60;
        color: #fff;
        border-color: #004e60;
        font-weight: 700;
    }

    .smart-location-select {
        min-width: 200px;
        background: #005f73;
        border-color: #005f73;
        color: #fff;
        font-weight: 700;
    }

    .smart-toolbar-select {
        min-width: 160px;
        background: #fff;
        color: #64748b;
    }

    .smart-results-section {
        padding: 0 0 6px;
    }

    .smart-results-body {
        padding: 0 0 10px;
    }

    .smart-results-grid {
        min-height: 360px;
    }

    .smart-results-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
    }

    .smart-results-empty strong {
        display: block;
        color: #0f172a;
        margin-bottom: 8px;
        font-size: 18px;
    }

    .fulmuv-pgsearch-shell { position: relative; width: 100%; }
    .fulmuv-pgsearch-input { display: block; width: 100%; min-height: 50px; border-radius: 16px !important; border: 1.5px solid #d7e2ea !important; background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%); box-shadow: 0 6px 20px rgba(15,23,42,0.07); padding: 10px 44px 10px 52px !important; font-size: 14px; font-weight: 600; color: #0f172a; }
    .fulmuv-pgsearch-input:focus { border-color: rgba(0,78,96,0.42) !important; box-shadow: 0 8px 28px rgba(0,78,96,0.12) !important; outline: none; }
    .fulmuv-pgsearch-input::placeholder { color: #94a3b8; font-weight: 500; }
    .fulmuv-pgsearch-brain { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: rgba(0,78,96,0.12); color: #004e60; pointer-events: none; font-size: 12px; }
    .fulmuv-pgsearch-clear { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 28px; height: 28px; border: 0; border-radius: 999px; background: transparent; color: #94a3b8; display: none; align-items: center; justify-content: center; padding: 0; cursor: pointer; font-size: 12px; }
    .fulmuv-pgsearch-clear.is-visible { display: inline-flex; }
    .fulmuv-pgsearch-clear:hover { background: rgba(148,163,184,0.15); color: #0f172a; }

    .smart-results-pagination {
        display: flex;
        justify-content: center;
        padding: 18px 0 4px;
    }

    .smart-results-pagination .pagination {
        gap: 8px;
        flex-wrap: wrap;
    }

    .smart-results-pagination .page-link {
        min-width: 42px;
        height: 42px;
        border-radius: 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dbe4ee;
        color: #0f172a;
        font-weight: 700;
    }

    .smart-results-pagination .page-item.active .page-link {
        background: #004e60;
        border-color: #004e60;
        color: #fff;
    }

    .smart-type-row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin: 0 auto 30px;
    }

    .smart-type-card {
        border: 1px solid #d6dee8;
        background: #fff;
        border-radius: 999px;
        padding: 11px 22px;
        min-width: auto;
        min-height: 46px;
        text-align: center;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 700;
        line-height: 1;
        transition: all .18s ease;
    }

    .smart-type-card:hover {
        border-color: #b9c8d8;
        transform: translateY(-1px);
    }

    .smart-type-card.is-active {
        background: #39c27d;
        border-color: #39c27d;
        box-shadow: 0 10px 22px rgba(57, 194, 125, 0.20);
        color: #fff;
    }

    .smart-results-inner {
        padding: 10px 0 0;
    }

    .smart-main-pad {
        padding: 0 0 24px;
    }

    @media (max-width: 1199.98px) {
        .smart-results-layout {
            grid-template-columns: 240px minmax(0, 1fr);
        }
    }

    @media (max-width: 991.98px) {
        .smart-results-layout {
            grid-template-columns: 1fr;
        }

        .smart-results-sidebar {
            position: static;
        }

        .smart-type-row {
            margin-bottom: 18px;
        }

        .smart-results-toolbar {
            justify-content: flex-start;
        }
    }
</style>

<section class="smart-results-shell">
    <div class="container">
        <div class="smart-results-topbar">
            <div class="archive-header-2 text-center mt-20">
                <div class="row">
                    <div class="col-lg-5 mx-auto">
                        <div class="sidebar-widget-2 widget_search mb-20">
                            <div class="fulmuv-pgsearch-shell">
                                <span class="fulmuv-pgsearch-brain" aria-hidden="true">
                                    <i class="fa-solid fa-brain"></i>
                                </span>
                                <input type="text" id="smartResultsSearchInput" class="fulmuv-pgsearch-input"
                                    placeholder="Buscar por Nombre de Producto" autocomplete="off" />
                                <button type="button" class="fulmuv-pgsearch-clear" aria-label="Limpiar búsqueda">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="smart-type-row" id="smartTypeTabs">
            <button type="button" class="smart-type-card is-active" data-search-type="products">Productos</button>
            <button type="button" class="smart-type-card" data-search-type="services">Servicios</button>
            <button type="button" class="smart-type-card" data-search-type="vehicles">Vehículos</button>
        </div>

        <div class="smart-results-layout">
            <aside class="smart-results-sidebar">
                <div class="smart-filter-card" data-filter-card="reference">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="reference">
                        <span>Referencia</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <input type="text" id="smartFilterSearchReference" class="smart-filter-search" placeholder="Buscar referencia">
                        <div class="smart-filter-options" id="smartReferenceOptions"></div>
                    </div>
                </div>

                <div class="smart-filter-card is-open" data-filter-card="brand">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="brand">
                        <span>Marca</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <input type="text" id="smartFilterSearchBrand" class="smart-filter-search" placeholder="Buscar marca">
                        <div class="smart-filter-options" id="smartBrandOptions"></div>
                    </div>
                </div>

                <div class="smart-filter-card" data-filter-card="model">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="model">
                        <span>Modelo</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <input type="text" id="smartFilterSearchModel" class="smart-filter-search" placeholder="Buscar modelo">
                        <div class="smart-filter-options" id="smartModelOptions"></div>
                    </div>
                </div>

                <div class="smart-filter-card" data-filter-card="category">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="category">
                        <span>Categorías</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <input type="text" id="smartFilterSearchCategory" class="smart-filter-search" placeholder="Buscar categoría">
                        <div class="smart-filter-options" id="smartCategoryOptions"></div>
                    </div>
                </div>

                <div class="smart-filter-card" data-filter-card="service-name">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="service-name">
                        <span>Nombres de servicio</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <input type="text" id="smartFilterSearchServiceName" class="smart-filter-search" placeholder="Buscar nombre de servicio">
                        <div class="smart-filter-options" id="smartServiceNameOptions"></div>
                    </div>
                </div>

                <div class="smart-filter-card" data-filter-card="color">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="color">
                        <span>Color</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <input type="text" id="smartFilterSearchColor" class="smart-filter-search" placeholder="Buscar color">
                        <div class="smart-filter-options" id="smartColorOptions"></div>
                    </div>
                </div>

                <div class="smart-filter-card" data-filter-card="tapiceria">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="tapiceria">
                        <span>Tapicería</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <input type="text" id="smartFilterSearchTapiceria" class="smart-filter-search" placeholder="Buscar tapicería">
                        <div class="smart-filter-options" id="smartTapiceriaOptions"></div>
                    </div>
                </div>

                <div class="smart-filter-card" data-filter-card="year">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="year">
                        <span>Año</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" id="smartYearMin" class="smart-filter-search mb-0" placeholder="Desde" min="1900" max="2100">
                            </div>
                            <div class="col-6">
                                <input type="number" id="smartYearMax" class="smart-filter-search mb-0" placeholder="Hasta" min="1900" max="2100">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="smart-filter-card" data-filter-card="price">
                    <button type="button" class="smart-filter-toggle" data-filter-toggle="price">
                        <span>Filtrar por precio</span>
                        <i class="fi-rs-angle-small-down"></i>
                    </button>
                    <div class="smart-filter-body">
                        <div id="smart-price-slider"></div>
                        <div class="smart-price-values">
                            <div>From: <span id="smartPriceMinValue">$0</span></div>
                            <div>To: <span id="smartPriceMaxValue">$0</span></div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="smart-results-main">
                <div class="smart-main-pad">
                    <div class="smart-results-header">
                        <div class="smart-results-summary">
                            <h2 class="smart-results-title" id="searchResultsHeading">Encontramos 0 artículos para ti!</h2>
                            <div class="smart-results-toolbar">
                                <select id="smartLocationSelect" class="smart-location-select">
                                    <option value="">Cambiar ubicacion</option>
                                </select>
                                <select id="smartItemsPerPage" class="smart-toolbar-select">
                                    <option value="20">Show: 20</option>
                                    <option value="24">Show: 24</option>
                                    <option value="32">Show: 32</option>
                                </select>
                                <select id="smartSortSelect" class="smart-toolbar-select">
                                    <option value="relevance">Ordenado por: Todos</option>
                                    <option value="price_asc">Precio: menor a mayor</option>
                                    <option value="price_desc">Precio: mayor a menor</option>
                                    <option value="name_asc">Nombre: A a Z</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="smart-results-body">
                        <div class="row smart-results-grid" id="searchResultsGrid"></div>

                        <div class="smart-results-empty d-none" id="searchResultsEmpty">
                            <strong>No encontramos coincidencias</strong>
                            <span id="searchResultsEmptyText">Prueba con otro término o ajusta los filtros activos.</span>
                        </div>

                        <div class="smart-results-pagination">
                            <ul class="pagination" id="searchResultsPagination"></ul>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
<script src="js/busqueda_productos.js?v=<?php echo $timer; ?>"></script>
