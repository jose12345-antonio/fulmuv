<?php
include 'includes/header.php';

$id_empresa = $_GET["q"] ?? "";
$tema = isset($_GET['tema']) ? (int) $_GET['tema'] : 0;
$isDarkTheme = $tema === 1;
$sinCuentaMode = defined('APP_SIN_CUENTA') && APP_SIN_CUENTA;

echo '<input type="hidden" id="id_empresa" value="' . htmlspecialchars($id_empresa, ENT_QUOTES, 'UTF-8') . '" />';
?>

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>,
        vendorProductsPath: "productos_vendor_sincuenta.php",
        categoryProductsPath: "productos_categoria_sincuenta.php",
        productDetailPath: "detalle_producto_sincuenta.php"
    });
</script>

<style>
    :root {
        --pv-page-bg: <?= $isDarkTheme ? '#0f172a' : '#f8fafc' ?>;
        --pv-surface: <?= $isDarkTheme ? '#111827' : '#ffffff' ?>;
        --pv-surface-soft: <?= $isDarkTheme ? '#1e293b' : '#eef2f7' ?>;
        --pv-surface-muted: <?= $isDarkTheme ? '#0b1220' : '#f8fafc' ?>;
        --pv-border: <?= $isDarkTheme ? 'rgba(148, 163, 184, 0.16)' : 'rgba(15, 23, 42, 0.08)' ?>;
        --pv-border-strong: <?= $isDarkTheme ? '#334155' : '#d8e1eb' ?>;
        --pv-text: <?= $isDarkTheme ? '#e5e7eb' : '#0f172a' ?>;
        --pv-text-secondary: <?= $isDarkTheme ? '#94a3b8' : '#64748b' ?>;
        --pv-text-muted: <?= $isDarkTheme ? '#cbd5e1' : '#475569' ?>;
        --pv-accent: #004e60;
        --pv-accent-2: #0f766e;
        --pv-price: #2563eb;
        --pv-old-price: #dc2626;
        --pv-shadow: <?= $isDarkTheme ? '0 20px 45px rgba(2, 6, 23, 0.45)' : '0 20px 45px rgba(15, 23, 42, 0.08)' ?>;
        --pv-overlay: <?= $isDarkTheme ? 'rgba(2, 6, 23, 0.72)' : 'rgba(15, 23, 42, 0.28)' ?>;
    }

    body,
    body .main.pages,
    body .page-content {
        background: var(--pv-page-bg);
        color: var(--pv-text);
    }

    body h1,
    body h2,
    body h3,
    body h4,
    body h5,
    body h6,
    body label,
    body .text-dark,
    body .text-dark a,
    body .modal-title {
        color: var(--pv-text) !important;
    }

    .container-fluid {
        padding: 0 14px;
    }

    .store-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top left, rgba(15, 118, 110, 0.22), transparent 38%),
            linear-gradient(135deg, <?= $isDarkTheme ? '#111827' : '#ffffff' ?> 0%, <?= $isDarkTheme ? '#0f172a' : '#eef5f8' ?> 100%);
        border-bottom: 1px solid var(--pv-border);
        padding: 8px 0 6px;
    }

    .store-hero::after {
        content: "";
        position: absolute;
        right: -80px;
        top: -60px;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: rgba(0, 78, 96, 0.10);
        filter: blur(4px);
    }

    .store-hero-card {
        position: relative;
        z-index: 1;
        background: color-mix(in srgb, var(--pv-surface) 92%, transparent);
        border: 1px solid var(--pv-border);
        box-shadow: var(--pv-shadow);
        padding: 8px;
    }

    .store-hero-top {
        display: grid;
        grid-template-columns: 92px 1fr;
        gap: 8px;
        align-items: center;
    }

    .store-logo {
        width: 92px;
        height: 92px;
        background: var(--pv-surface-soft);
        border: 1px solid var(--pv-border);
        overflow: hidden;
    }

    .store-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .store-name {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 4px;
        line-height: 1.1;
    }

    .store-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 0;
    }

    .store-action-btn {
        min-height: 34px;
        padding: 0 10px;
        border: 1px solid var(--pv-border);
        background: var(--pv-surface-soft);
        color: var(--pv-text);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .store-action-btn.primary {
        background: linear-gradient(135deg, var(--pv-accent) 0%, var(--pv-accent-2) 100%);
        border: none;
        color: #fff;
    }

    .toolbar-modern {
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 14px 0;
        background: color-mix(in srgb, var(--pv-surface) 92%, transparent);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--pv-border);
    }

    .toolbar-search {
        display: flex;
        gap: 10px;
    }

    .input-search-modern {
        height: 48px;
        background: var(--pv-surface-soft) !important;
        border: 1px solid transparent !important;
        color: var(--pv-text) !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .input-search-modern::placeholder {
        color: var(--pv-text-secondary);
    }

    .input-search-modern:focus {
        background: var(--pv-surface) !important;
        border-color: var(--pv-accent) !important;
    }

    .btn-filter-modern {
        width: 48px;
        min-width: 48px;
        height: 48px;
        border: none;
        background: linear-gradient(135deg, var(--pv-accent) 0%, var(--pv-accent-2) 100%) !important;
        color: #fff !important;
        box-shadow: 0 12px 24px rgba(0, 78, 96, 0.24);
    }

    .results-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .results-count {
        color: var(--pv-text);
        font-size: 15px;
        font-weight: 800;
    }

    .results-sub {
        font-size: 13px;
        color: var(--pv-text-secondary);
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
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
            radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 34%),
            linear-gradient(180deg, var(--pv-surface) 0%, var(--pv-surface-soft) 100%);
        border: 1px solid var(--pv-border);
        box-shadow: var(--pv-shadow);
        text-align: center;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.10);
        color: #2563eb;
        font-size: 26px;
    }

    .empty-state-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--pv-text);
        margin-bottom: 6px;
    }

    .empty-state-text {
        font-size: 14px;
        color: var(--pv-text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    @media (min-width: 768px) {
        .container-fluid {
            padding: 0 30px;
        }

        .product-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (min-width: 1200px) {
        .product-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }

    .product-card-modern {
        background: var(--pv-surface);
        border: 1px solid var(--pv-border);
        box-shadow: var(--pv-shadow);
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.18s ease, border-color 0.18s ease;
    }

    .product-card-modern:hover {
        transform: translateY(-4px);
        border-color: rgba(0, 78, 96, 0.32);
    }

    .product-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .product-media {
        position: relative;
        aspect-ratio: 1 / 1;
        background: var(--pv-surface-soft);
        overflow: hidden;
    }

    .product-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        background: #ef4444;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
    }

    .product-body {
        padding: 12px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .product-brand {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--pv-text-secondary);
        margin-bottom: 6px;
    }

    .product-title {
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
        color: var(--pv-text);
        min-height: 38px;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-meta {
        display: none;
    }

    .product-footer {
        margin-top: auto;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
    }

    .product-price {
        display: flex;
        flex-direction: column;
    }

    .product-price strong {
        font-size: 18px;
        color: var(--pv-price);
        line-height: 1;
    }

    .product-price .old-price {
        font-size: 12px;
        color: var(--pv-old-price);
        text-decoration: line-through;
        margin-top: 4px;
        font-weight: 700;
    }

    .product-cta {
        width: 38px;
        min-width: 38px;
        height: 38px;
        border: none;
        background: var(--pv-accent);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: var(--pv-overlay);
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
        background: var(--pv-surface);
        border-left: 1px solid var(--pv-border);
        box-shadow: var(--pv-shadow);
        transform: translateX(100%);
        transition: transform .3s ease;
        z-index: 1201;
    }

    .filter-panel-modern.is-open {
        transform: translateX(0);
    }

    .filter-panel-header,
    .filter-panel-footer {
        padding: 18px 20px;
        border-bottom: 1px solid var(--pv-border);
    }

    .filter-panel-footer {
        border-bottom: 0;
        border-top: 1px solid var(--pv-border);
        display: flex;
        gap: 10px;
    }

    .filter-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 20px;
    }

    .filter-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
    }

    .filter-subtitle {
        margin: 5px 0 0;
        color: var(--pv-text-secondary);
        font-size: 13px;
    }

    .filter-close {
        width: 40px;
        height: 40px;
        border: 1px solid var(--pv-border);
        background: var(--pv-surface-soft);
        color: var(--pv-text);
    }

    .filter-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .filter-active-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 9px;
        background: rgba(0, 78, 96, 0.12);
        color: var(--pv-accent);
        font-weight: 800;
        font-size: 12px;
    }

    .filter-block {
        padding: 16px;
        background: var(--pv-surface-muted);
        border: 1px solid var(--pv-border);
        margin-bottom: 14px;
    }

    .filter-block-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--pv-text-secondary);
        font-weight: 800;
        margin-bottom: 12px;
    }

    .filter-chip,
    .filter-select {
        min-height: 42px;
        border: 1px solid var(--pv-border);
        background: var(--pv-surface);
        color: var(--pv-text);
    }

    .filter-select:focus,
    .filter-chip:focus {
        box-shadow: none;
        border-color: var(--pv-accent);
    }

    .filter-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .filter-list-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        align-items: stretch;
    }

    .filter-list-grid .form-check {
        min-height: 56px;
        margin: 0;
        padding: 10px 12px 10px 36px;
        background: var(--pv-surface);
        border: 1px solid var(--pv-border);
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .filter-list-grid .form-check-input,
    .filter-radio-list .form-check-input {
        position: absolute;
        left: 12px;
        top: 50%;
        margin-top: -9px;
    }

    .filter-list-grid .form-check-label,
    .filter-radio-list .form-check-label {
        color: var(--pv-text);
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        min-height: 100%;
        line-height: 1.25;
    }

    .filter-radio-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        align-items: stretch;
    }

    .filter-radio-list .form-check {
        min-height: 56px;
        margin: 0;
        padding: 10px 12px 10px 36px;
        background: var(--pv-surface);
        border: 1px solid var(--pv-border);
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .price-summary {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
        color: var(--pv-text-secondary);
        font-size: 13px;
        font-weight: 700;
    }

    #slider-range {
        margin: 10px 6px 4px;
    }

    .btn-filter-secondary,
    .btn-filter-primary {
        flex: 1;
        min-height: 46px;
        border: none;
        font-weight: 800;
    }

    .btn-filter-secondary {
        background: var(--pv-surface-soft);
        color: var(--pv-text);
        border: 1px solid var(--pv-border);
    }

    .btn-filter-primary {
        background: linear-gradient(135deg, var(--pv-accent) 0%, var(--pv-accent-2) 100%);
        color: #fff;
    }

    .pagination .page-link {
        background: var(--pv-surface);
        border-color: var(--pv-border);
        color: var(--pv-text);
    }

    .pagination .page-item.active .page-link {
        background: var(--pv-accent);
        border-color: var(--pv-accent);
        color: #fff;
    }

    #modalUbicacion {
        z-index: 1305;
    }

    .modal-backdrop.show {
        z-index: 1300;
    }

    .modal-content {
        background: var(--pv-surface);
        color: var(--pv-text);
        border: 1px solid var(--pv-border);
    }

    .modal-content .form-select {
        background: var(--pv-surface-soft);
        border-color: var(--pv-border-strong);
        color: var(--pv-text);
    }

    .modal-content .btn-close {
        filter: <?= $isDarkTheme ? 'invert(1)' : 'none' ?>;
    }

    @media (max-width: 767px) {
        .store-hero-top {
            grid-template-columns: 74px 1fr;
        }

        .store-logo {
            width: 74px;
            height: 74px;
        }

        .store-name {
            font-size: 20px;
            margin-bottom: 2px;
        }

        .filter-grid-2,
        .filter-list-grid,
        .filter-radio-list {
            grid-template-columns: 1fr;
        }

        .filter-panel-modern {
            width: 100%;
        }

        .filter-panel-header,
        .filter-panel-body,
        .filter-panel-footer {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>

<section class="store-hero">
    <div class="container-fluid">
        <div class="store-hero-card">
            <div class="store-hero-top">
                <div class="store-logo">
                    <img id="imagenEmpresa" src="../img/FULMUV_LOGO-13.png" alt="Empresa" onerror="this.src='../img/FULMUV_LOGO-13.png';">
                </div>
                <div>
                    <h1 class="store-name" id="nombreEmpresa">Cargando tienda...</h1>
                    <div class="store-actions">
                        <a class="store-action-btn primary" href="#" id="empresaWhatsappCta" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp"></i> Comunicate por WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="toolbar-modern">
    <div class="container-fluid">
        <div class="toolbar-search">
            <input type="text" id="inputBusqueda" class="form-control input-search-modern" placeholder="Buscar por producto, marca o modelo...">
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
                <h4 class="filter-title">Filtrar tienda</h4>
                <p class="filter-subtitle">Marca, modelo, categoria, subcategoria y rango de precio.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="filter-active-badge" id="filterActiveCount">0</span>
                <button class="filter-close" type="button" id="closeFilterPanel" aria-label="Cerrar filtros">
                    <i class="fi-rs-cross-small"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="filter-panel-body">
        <div class="filter-block" data-filter-block="modelo">
            <div class="filter-block-title">Modelo</div>
            <div id="filtro-modelo" class="filter-radio-list"></div>
        </div>

        <div class="filter-block" data-filter-block="marca">
            <div class="filter-block-title">Marca</div>
            <div id="filtro-marca" class="filter-radio-list"></div>
        </div>

        <div class="filter-block" data-filter-block="categorias">
            <div class="filter-block-title">Categorias</div>
            <div id="filtro-categorias-panel" class="filter-list-grid"></div>
        </div>

        <div class="filter-block" id="subcats-box" data-filter-block="subcategorias" style="display:none;">
            <div class="filter-block-title">Subcategorias</div>
            <div id="filtro-sub-categorias" class="filter-list-grid"></div>
        </div>

        <div class="filter-block" data-filter-block="precio">
            <div class="filter-block-title">Precio</div>
            <div class="price-summary">
                <span id="slider-range-value1">$0</span>
                <span id="slider-range-value2">$0</span>
            </div>
            <div id="slider-range"></div>
        </div>

        <div class="filter-block">
            <div class="filter-block-title">Ubicacion y orden</div>
            <div class="filter-grid-2 mb-3">
                <button type="button" id="btnUbicacionPanel" class="btn filter-chip text-start" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                    <span><i class="fi-rs-marker me-1"></i> Cambiar ubicacion</span>
                </button>
                <select class="form-select filter-select" id="selectOrderPanel">
                    <option value="todos">Por defecto</option>
                    <option value="menor">Precio mas bajo</option>
                    <option value="mayor">Precio mas alto</option>
                </select>
            </div>
        </div>
    </div>

    <div class="filter-panel-footer">
        <button type="button" class="btn btn-filter-secondary" id="clearFiltersPanel">Limpiar</button>
        <button type="button" class="btn btn-filter-primary" id="minimizeFilterPanel">Minimizar</button>
    </div>
</aside>

<section class="py-4">
    <div class="container-fluid">
        <div class="results-head">
            <div>
                <div class="results-count" id="countProductos">Encontramos 0 articulos</div>
                <div class="results-sub">Resultados filtrados dentro de esta tienda.</div>
            </div>
        </div>

        <div class="product-grid" id="listaProductosContainer"></div>

        <div class="pagination-area mt-4 pb-5">
            <nav>
                <ul class="pagination justify-content-center"></ul>
            </nav>
        </div>
    </div>
</section>

<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Cambiar ubicacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Provincia</label>
                    <select id="selectProvincia" class="form-select"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Canton</label>
                    <select id="selectCanton" class="form-select"></select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light w-50" type="button" id="limpiarUbicacion">Limpiar</button>
                    <button class="btn btn-primary w-50" type="button" id="guardarUbicacion" style="background:#004e60;border:none;">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="js/productos_vendor.js?v1.0.0.17"></script>
