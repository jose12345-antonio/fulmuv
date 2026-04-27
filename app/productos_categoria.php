<?php
include 'includes/header.php';
$id_categoria = $_GET["q"];
$sinCuentaMode = defined('APP_SIN_CUENTA') && APP_SIN_CUENTA;
echo '<input type="hidden" id="id_categoria" value="' . $id_categoria . '" />';
?>

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>,
        categoryProductsPath: "productos_categoria_sincuenta.php",
        productDetailPath: "detalle_producto_sincuenta.php"
    });
</script>

<style>
    :root {
        --pc-page-bg: #f8fafc;
        --pc-surface: #ffffff;
        --pc-surface-soft: #eef2f7;
        --pc-surface-muted: #f8fafc;
        --pc-border: rgba(15, 23, 42, 0.08);
        --pc-text: #0f172a;
        --pc-text-secondary: #64748b;
        --pc-accent: #004e60;
        --pc-accent-2: #0f766e;
        --pc-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        --pc-overlay: rgba(15, 23, 42, 0.28);
    }

    body,
    body .main.pages,
    body .page-content {
        background: var(--pc-page-bg);
        color: var(--pc-text);
    }

    .container-fluid {
        padding: 0 14px;
    }

    .toolbar-modern {
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 14px 0;
        background: color-mix(in srgb, var(--pc-surface) 92%, transparent);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--pc-border);
    }

    .toolbar-search {
        display: flex;
        gap: 10px;
    }

    .input-search-modern {
        height: 48px;
        background: var(--pc-surface-soft) !important;
        border: 1px solid transparent !important;
        color: var(--pc-text) !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .input-search-modern::placeholder {
        color: var(--pc-text-secondary);
    }

    .input-search-modern:focus {
        background: var(--pc-surface) !important;
        border-color: var(--pc-accent) !important;
    }

    .btn-filter-modern {
        width: 48px;
        min-width: 48px;
        height: 48px;
        border: none;
        background: linear-gradient(135deg, var(--pc-accent) 0%, var(--pc-accent-2) 100%) !important;
        color: #fff !important;
        border-radius: 16px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 12px 24px rgba(0, 78, 96, 0.24);
        transition: transform 0.2s ease;
    }

    .btn-filter-modern:hover {
        transform: translateY(-1px);
    }

    .btn-filter-modern i {
        font-size: 18px;
    }

    .results-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 18px 0 16px;
    }

    .results-copy {
        min-width: 0;
    }

    .results-count {
        color: var(--pc-text);
        font-size: 15px;
        font-weight: 800;
    }

    .results-sub {
        font-size: 13px;
        color: var(--pc-text-secondary);
        margin-top: 2px;
    }

    .results-order {
        min-width: 152px;
        height: 42px;
        border: 1px solid var(--pc-border);
        background: var(--pc-surface);
        color: var(--pc-text);
    }

    #listaProductosContainer {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    @media (min-width: 768px) {
        .container-fluid {
            padding: 0 30px;
        }

        #listaProductosContainer {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (min-width: 1200px) {
        #listaProductosContainer {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }

    .product-exclusive-card {
        background: #fff;
        overflow: hidden;
        border: 1px solid #f0f0f0;
        transition: transform 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 100%;
    }

    .product-img-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        aspect-ratio: 1 / 1;
        min-height: 180px;
        background: #f8fafc;
        padding: 14px;
    }

    .img-main {
        object-fit: contain;
    }

    .exclusive-badge-discount {
        position: absolute;
        top: 8px;
        left: 8px;
        background: #FF5E5E;
        color: white;
        font-weight: 700;
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 50px;
        z-index: 2;
    }

    .product-verify-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 40px;
        height: 40px;
        object-fit: contain;
        z-index: 2;
    }

    .product-details-exclusive {
        padding: 0 12px 12px 12px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 112px;
    }

    .product-title-exclusive {
        font-size: 13px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
        height: 32px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-price {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        line-height: 1.1;
    }

    .product-price span:first-child {
        font-size: 18px;
        font-weight: 800;
        color: #2563eb;
    }

    .product-price .old-price {
        font-size: 12px;
        color: #dc2626;
        text-decoration: line-through;
        margin-top: 4px;
        font-weight: 700;
    }

    .btn-add-exclusive-circle {
        background: #004E60;
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-add-exclusive-circle i {
        font-size: 14px;
        line-height: 1;
    }

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: var(--pc-overlay);
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s ease, visibility .25s ease;
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
        width: min(430px, 100%);
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: var(--pc-surface);
        border-left: 1px solid var(--pc-border);
        box-shadow: var(--pc-shadow);
        transform: translateX(100%);
        transition: transform .3s ease;
        z-index: 1201;
    }

    .filter-panel-modern.is-open {
        transform: translateX(0);
    }

    #modalUbicacion {
        z-index: 1400;
    }

    #modalUbicacion+.modal-backdrop,
    .modal-backdrop.show {
        z-index: 1390;
    }

    .filter-panel-header,
    .filter-panel-footer {
        padding: 18px 20px;
        border-bottom: 1px solid var(--pc-border);
    }

    .filter-panel-footer {
        border-bottom: 0;
        border-top: 1px solid var(--pc-border);
        display: flex;
        gap: 10px;
    }

    .filter-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 20px;
    }

    .filter-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .filter-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
    }

    .filter-subtitle {
        margin: 5px 0 0;
        color: var(--pc-text-secondary);
        font-size: 13px;
    }

    .filter-close {
        width: 40px;
        height: 40px;
        border: 1px solid var(--pc-border);
        background: var(--pc-surface-soft);
        color: var(--pc-text);
    }

    .filter-active-badge,
    .filter-summary-badge,
    .filter-nav-meta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 9px;
        background: rgba(0, 78, 96, 0.12);
        color: var(--pc-accent);
        font-weight: 800;
        font-size: 12px;
    }

    .filter-layout-modern {
        display: grid;
        grid-template-columns: 138px minmax(0, 1fr);
        min-height: 100%;
    }

    .filter-nav-modern {
        padding: 14px 10px 18px;
        background: linear-gradient(180deg, var(--pc-surface-soft) 0%, color-mix(in srgb, var(--pc-surface-soft) 75%, var(--pc-surface) 25%) 100%);
        border-right: 1px solid var(--pc-border);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-nav-item {
        width: 100%;
        text-align: left;
        border: 1px solid transparent;
        background: transparent;
        padding: 14px 12px;
        color: var(--pc-text-secondary);
        transition: background-color .2s ease, border-color .2s ease, transform .2s ease, color .2s ease;
    }

    .filter-nav-item.is-active {
        background: var(--pc-surface);
        border-color: var(--pc-border);
        color: var(--pc-text);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transform: translateX(4px);
    }

    .filter-nav-label {
        display: block;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.25;
    }

    .filter-content-modern {
        padding: 18px 18px 20px;
    }

    .filter-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
        padding: 0 2px;
    }

    .filter-summary-text {
        font-size: 13px;
        color: var(--pc-text-secondary);
    }

    .filter-detail-panel {
        display: none;
    }

    .filter-detail-panel.is-active {
        display: block;
    }

    .filter-grid-2 {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 10px;
    }

    .filter-select {
        min-height: 44px;
        border: 1px solid var(--pc-border);
        background: var(--pc-surface);
        color: var(--pc-text);
    }

    .filter-select:focus {
        box-shadow: none;
        border-color: var(--pc-accent);
    }

    .filter-chip-action {
        min-height: 44px;
        border: 1px solid var(--pc-border);
        background: var(--pc-surface);
        color: var(--pc-text);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
    }

    .filter-section-heading {
        font-size: 24px;
        font-weight: 900;
        line-height: 1.05;
        margin-bottom: 6px;
        color: var(--pc-text);
    }

    .filter-section-copy {
        margin: 0 0 16px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--pc-text-secondary);
    }

    .filter-info-card {
        padding: 14px 16px;
        background: radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 38%), linear-gradient(180deg, var(--pc-surface) 0%, var(--pc-surface-soft) 100%);
        border: 1px solid var(--pc-border);
        margin-bottom: 14px;
    }

    .filter-info-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--pc-accent);
        margin-bottom: 8px;
    }

    .filter-info-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--pc-text);
        margin-bottom: 4px;
    }

    .filter-info-text {
        margin: 0;
        color: var(--pc-text-secondary);
        font-size: 13px;
        line-height: 1.55;
    }

    .filter-block {
        padding: 16px;
        background: var(--pc-surface-muted);
        border: 1px solid var(--pc-border);
        margin-bottom: 14px;
    }

    .filter-block-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--pc-text-secondary);
        font-weight: 800;
        margin-bottom: 12px;
    }

    .filter-list-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        align-items: stretch;
    }

    .filter-list-grid .form-check {
        min-height: 56px;
        margin: 0;
        padding: 10px 12px 10px 36px;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .filter-list-grid .form-check-input {
        position: absolute;
        left: 12px;
        top: 50%;
        margin-top: -9px;
    }

    .filter-list-grid .form-check-label {
        color: var(--pc-text);
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        min-height: 100%;
        line-height: 1.25;
    }

    .btn-filter-clear {
        flex: 1;
        min-height: 48px;
        border: 1px solid var(--pc-border);
        background: var(--pc-surface-soft);
        color: var(--pc-text);
        font-weight: 800;
    }

    .btn-filter-apply {
        flex: 1.1;
        min-height: 48px;
        border: none;
        background: linear-gradient(135deg, var(--pc-accent) 0%, var(--pc-accent-2) 100%);
        color: #fff;
        font-weight: 800;
        box-shadow: 0 12px 24px rgba(0, 78, 96, 0.22);
    }

    .empty-state-modern {
        grid-column: 1 / -1;
        display: flex;
        justify-content: center;
        padding: 24px 0 8px;
    }

    @media (max-width: 767px) {
        .results-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .results-order {
            width: 100%;
        }

        .filter-panel-modern {
            width: 100%;
        }

        .filter-layout-modern {
            grid-template-columns: 112px minmax(0, 1fr);
        }

        .filter-nav-modern {
            padding-left: 8px;
            padding-right: 8px;
        }

        .filter-nav-item {
            padding: 12px 10px;
        }

        .filter-nav-label {
            font-size: 13px;
        }

        .filter-content-modern {
            padding: 16px 14px 18px;
        }

    }
</style>

<section class="toolbar-modern">
    <div class="container-fluid">
        <div class="toolbar-search">
            <input type="text" id="inputBusqueda" class="form-control input-search-modern" placeholder="Buscar productos de esta categoría">
            <button class="btn-filter-modern" type="button" id="openFilterPanel" aria-controls="panelFiltros" aria-expanded="false">
                <i class="fi-rs-filter"></i>
            </button>
        </div>
    </div>
</section>

<div class="filters-overlay" id="filtersOverlay"></div>

<aside class="filter-panel-modern" id="panelFiltros" aria-hidden="true">
    <div class="filter-panel-header">
        <div class="filter-header-row">
            <div>
                <h3 class="filter-title">Filtrar productos</h3>
                <p class="filter-subtitle">Explora esta categoría por tipo de producto y atributos disponibles.</p>
            </div>
            <button type="button" class="filter-close" id="closeFilterPanel" aria-label="Cerrar filtros">
                <i class="fi-rs-cross-small"></i>
            </button>
        </div>
    </div>

    <div class="filter-panel-body">
        <div class="filter-summary">
            <span class="filter-summary-text">Filtros activos</span>
            <span class="filter-summary-badge" id="filterActiveCount">0</span>
        </div>

        <div class="filter-layout-modern">
            <div class="filter-nav-modern">
                <button type="button" class="filter-nav-item is-active" data-filter-target="ubicacion-orden">
                    <span class="filter-nav-label">Ubicación y orden</span>
                    <span class="filter-nav-meta" id="filterGroupCountUbicacionOrden">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="categorias">
                    <span class="filter-nav-label">Categorías</span>
                    <span class="filter-nav-meta" id="filterGroupCountCategorias">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="subcategorias">
                    <span class="filter-nav-label">Subcategorías</span>
                    <span class="filter-nav-meta" id="filterGroupCountSubcategorias">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="marcas">
                    <span class="filter-nav-label">Marcas</span>
                    <span class="filter-nav-meta" id="filterGroupCountMarcas">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="modelos">
                    <span class="filter-nav-label">Modelos</span>
                    <span class="filter-nav-meta" id="filterGroupCountModelos">0</span>
                </button>
            </div>

            <div class="filter-content-modern">
                <div class="filter-detail-panel is-active" data-filter-panel="ubicacion-orden">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-marker"></i> Contexto</div>
                        <div class="filter-info-title">Ajusta ubicación y orden</div>
                        <p class="filter-info-text">Gestiona la ubicación y el criterio de orden desde una sola opción dentro del panel.</p>
                    </div>
                    <div class="filter-block">
                        <div class="filter-block-title">Ubicación y orden</div>
                        <div class="filter-grid-2">
                            <button type="button" class="filter-chip-action" id="btnUbicacionPanel" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <i class="fi-rs-marker"></i> Cambiar ubicación
                            </button>
                            <select class="form-select filter-select" id="selectOrderPanel">
                                <option value="todos">Todos</option>
                                <option value="menor">Menor precio</option>
                                <option value="mayor">Mayor precio</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="categorias">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-apps"></i> Catálogo</div>
                        <div class="filter-info-title">Filtra por categoría visible</div>
                        <p class="filter-info-text">Mostramos solo categorías disponibles dentro de los productos cargados en esta sección.</p>
                    </div>
                    <div class="filter-block">
                        <div class="filter-block-title">Categorías</div>
                        <div id="filtro-categorias" class="filter-list-grid"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="subcategorias">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-layers"></i> Subniveles</div>
                        <div class="filter-info-title">Afina por subcategoría</div>
                        <p class="filter-info-text">Las subcategorías se actualizan según las categorías que vayas seleccionando.</p>
                    </div>
                    <div class="filter-block" id="subcats-box">
                        <div class="filter-block-title">Subcategorías</div>
                        <div id="filtro-sub-categorias" class="filter-list-grid"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="marcas">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-badge"></i> Marcas</div>
                        <div class="filter-info-title">Selecciona marcas disponibles</div>
                        <p class="filter-info-text">Solo verás marcas presentes dentro de los productos visibles en esta categoría.</p>
                    </div>
                    <div class="filter-block">
                        <div class="filter-block-title">Marca</div>
                        <div id="filtro-marca" class="filter-list-grid"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="modelos">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-car-side"></i> Modelos</div>
                        <div class="filter-info-title">Filtra por modelo</div>
                        <p class="filter-info-text">Los modelos se recalculan automáticamente según las marcas activas.</p>
                    </div>
                    <div class="filter-block">
                        <div class="filter-block-title">Modelo</div>
                        <div id="filtro-modelo" class="filter-list-grid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-panel-footer">
        <button type="button" class="btn-filter-clear" id="clearFiltersPanel">Limpiar</button>
        <button type="button" class="btn-filter-apply" id="applyFiltersPanel">
            Ver <span id="filterResultsCount">0</span> resultados
        </button>
    </div>
</aside>

<div class="container-fluid">
    <div class="results-head">
        <div class="results-copy">
            <div class="results-count" id="countVendedores">Encontramos 0 artículos</div>
            <div class="results-sub">Usa búsqueda, orden y filtros rápidos para explorar esta categoría.</div>
        </div>
    </div>

    <div id="listaProductosContainer"></div>

    <div class="pagination-area mt-4 pb-5">
        <nav>
            <ul class="pagination justify-content-center"></ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-body p-4">
                <select id="selectProvincia" class="form-select mb-3 rounded-3"></select>
                <select id="selectCanton" class="form-select mb-3 rounded-3"></select>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary w-50 rounded-3" id="limpiarUbicacion">Limpiar</button>
                    <button class="btn btn-primary w-50 rounded-3" id="guardarUbicacion" style="background:#004E60; border:none;">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="js/productos_categoria.js?v1.0.1.1"></script>
