<?php
include 'includes/header.php';

$tema = isset($_GET['tema']) ? (int) $_GET['tema'] : 0;
$isDarkTheme = $tema === 1;
$sinCuentaMode = defined('APP_SIN_CUENTA') && APP_SIN_CUENTA;
?>

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>,
        vendorPath: "vendor_sincuenta.php",
        vendorProductsPath: "productos_vendor_sincuenta.php",
        categoryProductsPath: "productos_categoria_sincuenta.php",
        productDetailPath: "detalle_producto_sincuenta.php"
    });
</script>

<style>
    :root {
        --vendor-page-bg: <?= $isDarkTheme ? '#0f172a' : '#fcfcfc' ?>;
        --vendor-surface: <?= $isDarkTheme ? '#111827' : '#ffffff' ?>;
        --vendor-surface-soft: <?= $isDarkTheme ? '#1e293b' : '#f1f3f4' ?>;
        --vendor-surface-muted: <?= $isDarkTheme ? '#0b1220' : '#f8fafc' ?>;
        --vendor-border: <?= $isDarkTheme ? 'rgba(148, 163, 184, 0.16)' : 'rgba(15, 23, 42, 0.08)' ?>;
        --vendor-border-strong: <?= $isDarkTheme ? '#334155' : '#d7dde5' ?>;
        --vendor-text-primary: <?= $isDarkTheme ? '#e5e7eb' : '#111111' ?>;
        --vendor-text-secondary: <?= $isDarkTheme ? '#94a3b8' : '#666666' ?>;
        --vendor-text-muted: <?= $isDarkTheme ? '#cbd5e1' : '#888888' ?>;
        --vendor-accent: #004e60;
        --vendor-accent-hover: <?= $isDarkTheme ? '#0b7285' : '#003a47' ?>;
        --vendor-shadow: <?= $isDarkTheme ? '0 20px 45px rgba(2, 6, 23, 0.45)' : '0 20px 45px rgba(15, 23, 42, 0.08)' ?>;
        --vendor-overlay: <?= $isDarkTheme ? 'rgba(2, 6, 23, 0.72)' : 'rgba(15, 23, 42, 0.28)' ?>;
        --vendor-chip-bg: <?= $isDarkTheme ? '#1f2937' : '#f1f3f4' ?>;
    }

    .container-fluid {
        padding: 0 10px;
    }

    body,
    body .main.pages,
    body .page-content {
        background-color: var(--vendor-page-bg);
        color: var(--vendor-text-primary);
    }

    body .text-dark,
    body .text-dark a,
    body h1,
    body h2,
    body h3,
    body h4,
    body h5,
    body h6,
    body label,
    body .modal-title {
        color: var(--vendor-text-primary) !important;
    }

    .search-container-modern {
        padding: 15px 0;
        background: color-mix(in srgb, var(--vendor-surface) 92%, transparent);
        backdrop-filter: blur(14px);
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 1px solid var(--vendor-border);
    }

    .input-search-modern {
        background-color: var(--vendor-surface-soft) !important;
        border: 1px solid transparent !important;
        border-radius: 16px !important;
        padding: 10px 15px !important;
        font-size: 14px;
        height: 48px;
        color: var(--vendor-text-primary) !important;
        box-shadow: none !important;
    }

    .input-search-modern::placeholder {
        color: var(--vendor-text-secondary);
    }

    .input-search-modern:focus {
        border-color: var(--vendor-accent) !important;
        background-color: var(--vendor-surface) !important;
    }

    .btn-filter-modern {
        background: linear-gradient(135deg, var(--vendor-accent) 0%, #0f766e 100%) !important;
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

    .btn-filter-modern:hover {
        transform: translateY(-1px);
    }

    .btn-filter-modern i {
        font-size: 18px;
    }

    .results-count {
        color: var(--vendor-text-primary);
    }

    .vendor-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
        align-items: stretch;
    }

    .vendor-card-container {
        height: 100%;
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
        background:
            radial-gradient(circle at top right, rgba(0, 78, 96, 0.10), transparent 34%),
            linear-gradient(180deg, var(--vendor-surface) 0%, var(--vendor-surface-soft) 100%);
        border: 1px solid var(--vendor-border);
        box-shadow: var(--vendor-shadow);
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
        color: var(--vendor-accent);
        font-size: 26px;
    }

    .empty-state-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--vendor-text-primary);
        margin-bottom: 6px;
    }

    .empty-state-text {
        font-size: 14px;
        color: var(--vendor-text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    @media (min-width: 768px) {
        .vendor-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .container-fluid {
            padding: 0 30px;
        }
    }

    @media (min-width: 1200px) {
        .vendor-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    .vendor-card-modern {
        background: var(--vendor-surface);
        border: 1px solid var(--vendor-border);
        border-radius: 0;
        box-shadow: var(--vendor-shadow);
        height: 100%;
        display: flex;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .vendor-card-modern:hover {
        transform: translateY(-4px);
        border-color: rgba(0, 78, 96, 0.32);
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
        border-radius: 0;
        overflow: hidden;
        background: var(--vendor-surface-soft);
    }

    .vendor-main-img {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 200px;
        object-fit: contain;
        background: var(--vendor-surface-soft);
    }

    .badge-verificacion-flotante {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 40px;
        height: 40px;
        z-index: 3;
        background: var(--vendor-surface);
        border-radius: 50%;
        padding: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .badge-verificacion-flotante img {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }

    .vendor-info-modern {
        padding: 15px 12px;
        flex: 1 1 auto;
    }

    .vendor-title-modern {
        font-size: 14px;
        font-weight: 800;
        margin: 5px 0;
        color: var(--vendor-text-primary);
    }

    .vendor-location-modern {
        font-size: 10px;
        color: var(--vendor-text-muted);
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .vendor-items-count {
        font-size: 11px;
        color: var(--vendor-text-secondary);
        margin-top: 2px;
    }

    .btn-circle-action {
        width: 32px;
        height: 32px;
        background-color: var(--vendor-accent);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, background-color 0.2s;
        cursor: pointer;
    }

    .btn-circle-action i {
        font-size: 20px;
        line-height: 1;
    }

    .btn-circle-action:hover {
        background-color: var(--vendor-accent-hover);
        transform: scale(1.1);
    }

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: var(--vendor-overlay);
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
        background: var(--vendor-surface);
        border-left: 1px solid var(--vendor-border);
        box-shadow: var(--vendor-shadow);
        z-index: 1201;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }

    .filter-panel-modern.is-open {
        transform: translateX(0);
    }

    #modalUbicacion {
        z-index: 1305;
    }

    .modal-backdrop.show {
        z-index: 1300;
    }

    .filter-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--vendor-border);
    }

    .filter-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--vendor-text-primary);
    }

    .filter-panel-subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: var(--vendor-text-secondary);
    }

    .filter-panel-close {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid var(--vendor-border);
        background: var(--vendor-surface-soft);
        color: var(--vendor-text-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .filter-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 0;
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
        color: var(--vendor-text-secondary);
    }

    .filter-summary-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        border-radius: 999px;
        background: rgba(0, 78, 96, 0.14);
        color: var(--vendor-accent);
        font-size: 12px;
        font-weight: 800;
    }

    .filter-layout-modern {
        display: grid;
        grid-template-columns: 138px minmax(0, 1fr);
        min-height: 100%;
    }

    .filter-nav-modern {
        padding: 14px 10px 18px;
        background:
            linear-gradient(180deg, var(--vendor-surface-soft) 0%, color-mix(in srgb, var(--vendor-surface-soft) 75%, var(--vendor-surface) 25%) 100%);
        border-right: 1px solid var(--vendor-border);
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
        color: var(--vendor-text-secondary);
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }

    .filter-nav-item.is-active {
        background: var(--vendor-surface);
        border-color: var(--vendor-border);
        color: var(--vendor-text-primary);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transform: translateX(4px);
    }

    .filter-nav-label {
        display: block;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.25;
    }

    .filter-nav-meta {
        margin-top: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        background: rgba(0, 78, 96, 0.10);
        color: var(--vendor-accent);
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
        animation: filterPanelFade 0.22s ease;
    }

    @keyframes filterPanelFade {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .filter-section-heading {
        font-size: 24px;
        font-weight: 900;
        line-height: 1.05;
        margin-bottom: 6px;
        color: var(--vendor-text-primary);
    }

    .filter-section-copy {
        margin: 0 0 16px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--vendor-text-secondary);
    }

    .filter-block {
        background: var(--vendor-surface-muted);
        border: 1px solid var(--vendor-border);
        border-radius: 22px;
        padding: 16px;
        margin-bottom: 14px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .filter-block-title {
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: var(--vendor-text-secondary);
        margin-bottom: 12px;
    }

    .filter-actions-inline {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-chip {
        min-height: 42px;
        border-radius: 14px;
        background: var(--vendor-chip-bg);
        border: 1px solid var(--vendor-border);
        color: var(--vendor-text-primary);
    }

    .filter-info-card {
        padding: 14px 16px;
        border-radius: 18px;
        background:
            radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 38%),
            linear-gradient(180deg, var(--vendor-surface) 0%, var(--vendor-surface-soft) 100%);
        border: 1px solid var(--vendor-border);
        margin-bottom: 14px;
    }

    .filter-info-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--vendor-accent);
        margin-bottom: 8px;
    }

    .filter-info-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--vendor-text-primary);
        margin-bottom: 4px;
    }

    .filter-info-text {
        margin: 0;
        color: var(--vendor-text-secondary);
        font-size: 13px;
        line-height: 1.55;
    }

    .filter-chip:focus,
    .form-select.filter-chip:focus {
        border-color: var(--vendor-accent);
        box-shadow: none;
    }

    .filter-panel-footer {
        display: flex;
        gap: 10px;
        padding: 16px 20px 20px;
        border-top: 1px solid var(--vendor-border);
        background: var(--vendor-surface);
    }

    .btn-filter-secondary,
    .btn-filter-primary {
        flex: 1;
        min-height: 46px;
        border-radius: 14px;
        font-weight: 700;
    }

    .btn-filter-secondary {
        background: var(--vendor-surface-soft);
        color: var(--vendor-text-primary);
        border: 1px solid var(--vendor-border);
    }

    .btn-filter-primary {
        background: linear-gradient(135deg, var(--vendor-accent) 0%, #0f766e 100%);
        color: #fff;
        border: none;
    }

    .btn-filter-primary span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    #filtro-categorias-panel {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        max-height: 420px;
        overflow-y: auto;
        scrollbar-width: thin;
        margin: 0;
    }

    #filtro-categorias-panel>[class*="col-"] {
        width: 100%;
        padding: 0;
    }

    #filtro-categorias-panel .form-check {
        padding: 10px 12px;
        margin: 0;
        border: 1px solid var(--vendor-border);
        border-radius: 14px;
        width: 100%;
        min-height: 54px;
        display: flex;
        align-items: center;
        background: var(--vendor-surface);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }

    #filtro-categorias-panel .form-check-input {
        display: inline-block;
        margin-right: 8px;
    }

    #filtro-categorias-panel .form-check-label {
        display: inline-block;
        padding: 0;
        background: transparent;
        font-size: 14px;
        cursor: pointer;
        color: var(--vendor-text-primary);
    }

    #filtro-categorias-panel .form-check-input:checked+.form-check-label {
        background: transparent;
        color: var(--vendor-accent);
        font-weight: 600;
    }

    .pagination .page-link {
        background: var(--vendor-surface);
        border-color: var(--vendor-border);
        color: var(--vendor-text-primary);
    }

    .pagination .page-item.active .page-link {
        background: var(--vendor-accent);
        border-color: var(--vendor-accent);
        color: #fff;
    }

    .modal-content {
        background: var(--vendor-surface);
        color: var(--vendor-text-primary);
    }

    .modal-content .form-select {
        background-color: var(--vendor-surface-soft);
        border-color: var(--vendor-border-strong);
        color: var(--vendor-text-primary);
    }

    .modal-content .btn-close {
        filter: <?= $isDarkTheme ? 'invert(1)' : 'none' ?>;
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

        #filtro-categorias-panel {
            grid-template-columns: 1fr;
        }
    }
</style>

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
                        <div class="filter-info-eyebrow">
                            <i class="fi-rs-marker"></i>
                            Contexto activo
                        </div>
                        <div class="filter-info-title">Búsqueda geográfica y orden visual</div>
                        <p class="filter-info-text">Puedes cambiar provincia y cantón, y luego ordenar las empresas por cantidad de productos publicados.</p>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Ubicación</div>
                        <div class="filter-actions-inline mb-2">
                            <button type="button" id="btnUbicacionPanel" class="btn filter-chip w-100 d-flex justify-content-between align-items-center px-3" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <span class="text-truncate small"><i class="fi-rs-marker me-1"></i>Cambiar ubicacion</span>
                                <i class="fi-rs-angle-small-right"></i>
                            </button>
                        </div>
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
                        <div class="filter-info-eyebrow">
                            <i class="fi-rs-apps"></i>
                            Navegación inteligente
                        </div>
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

    <div class="vendor-grid row" id="listaVendedoresContainer"></div>

    <div class="pagination-area mt-4 pb-5">
        <nav>
            <ul class="pagination justify-content-center"></ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Elige tu ubicacion</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Provincia</label>
                    <select id="selectProvincia" class="form-select rounded-3"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Canton</label>
                    <select id="selectCanton" class="form-select rounded-3"></select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary w-100 rounded-3 py-2" id="guardarUbicacion">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
<script src="js/vendor.js?v1.1.8"></script>
