<?php
include 'includes/header.php';

$tema = isset($_GET['tema']) ? (int) $_GET['tema'] : 0;
$isDarkTheme = $tema === 1;
?>
<link rel="canonical" href="https://fulmuv.com/productos_vendidos_hoy.php">

<style>
    :root {
        --pv-page-bg: <?= $isDarkTheme ? '#0f172a' : '#f8fafc' ?>;
        --pv-surface: <?= $isDarkTheme ? '#111827' : '#ffffff' ?>;
        --pv-surface-soft: <?= $isDarkTheme ? '#1e293b' : '#eef2f7' ?>;
        --pv-border: <?= $isDarkTheme ? 'rgba(148, 163, 184, 0.16)' : 'rgba(15, 23, 42, 0.08)' ?>;
        --pv-border-strong: <?= $isDarkTheme ? '#334155' : '#d8e1eb' ?>;
        --pv-text: <?= $isDarkTheme ? '#e5e7eb' : '#0f172a' ?>;
        --pv-text-secondary: <?= $isDarkTheme ? '#94a3b8' : '#64748b' ?>;
        --pv-accent: #004e60;
        --pv-accent-2: #0f766e;
        --pv-shadow: <?= $isDarkTheme ? '0 20px 45px rgba(2, 6, 23, 0.45)' : '0 20px 45px rgba(15, 23, 42, 0.08)' ?>;
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

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.35);
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
        z-index: 1200;
    }

    .filters-overlay.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .filter-panel-modern {
        position: fixed;
        inset: 0 0 0 auto;
        width: min(100%, 420px);
        background: var(--pv-surface);
        border-left: 1px solid var(--pv-border);
        box-shadow: var(--pv-shadow);
        transform: translateX(100%);
        transition: transform .24s ease;
        z-index: 1250;
        display: flex;
        flex-direction: column;
    }

    .filter-panel-modern.is-open {
        transform: translateX(0);
    }

    .filter-panel-header,
    .filter-panel-body,
    .filter-panel-footer {
        padding: 18px 20px;
    }

    .filter-panel-header,
    .filter-panel-footer {
        border-bottom: 1px solid var(--pv-border);
    }

    .filter-panel-footer {
        border-bottom: none;
        border-top: 1px solid var(--pv-border);
        margin-top: auto;
        display: flex;
        gap: 12px;
    }

    .filter-panel-body {
        overflow-y: auto;
    }

    .filter-title {
        font-size: 22px;
        font-weight: 800;
        margin: 0;
    }

    .filter-subtitle {
        font-size: 13px;
        color: var(--pv-text-secondary);
        margin: 4px 0 0;
    }

    .filter-close {
        width: 40px;
        height: 40px;
        border: 1px solid var(--pv-border);
        background: var(--pv-surface-soft);
        color: var(--pv-text);
    }

    .filter-layout-modern {
        display: grid;
        grid-template-columns: 132px minmax(0, 1fr);
        gap: 0;
        min-height: 100%;
    }

    .filter-nav-modern {
        border-right: 1px solid var(--pv-border);
        padding: 8px 10px 18px 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .filter-nav-item {
        width: 100%;
        text-align: left;
        border: 1px solid transparent;
        background: transparent;
        padding: 12px 10px;
        color: var(--pv-text-secondary);
    }

    .filter-nav-item.is-active {
        background: var(--pv-surface-soft);
        border-color: var(--pv-border);
        color: var(--pv-text);
    }

    .filter-nav-label {
        display: block;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-nav-meta {
        display: inline-flex;
        margin-top: 6px;
        min-width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        background: rgba(0, 78, 96, 0.12);
        color: var(--pv-accent);
        font-size: 12px;
        font-weight: 800;
    }

    .filter-content-modern {
        padding-left: 16px;
    }

    .filter-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .filter-summary-text {
        font-size: 13px;
        font-weight: 700;
        color: var(--pv-text-secondary);
    }

    .filter-summary-badge {
        min-width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--pv-accent) 0%, var(--pv-accent-2) 100%);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-detail-panel {
        display: none;
    }

    .filter-detail-panel.is-active {
        display: block;
    }

    .filter-section-heading {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .filter-section-copy {
        font-size: 13px;
        line-height: 1.65;
        color: var(--pv-text-secondary);
        margin-bottom: 18px;
    }

    .filter-info-card {
        padding: 16px;
        background: linear-gradient(180deg, color-mix(in srgb, var(--pv-surface-soft) 94%, transparent), var(--pv-surface));
        border: 1px solid var(--pv-border);
        box-shadow: var(--pv-shadow);
        margin-bottom: 18px;
    }

    .filter-info-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--pv-accent);
        margin-bottom: 10px;
    }

    .filter-info-title {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .filter-info-text {
        font-size: 13px;
        line-height: 1.6;
        color: var(--pv-text-secondary);
        margin: 0;
    }

    .filter-block {
        margin-bottom: 18px;
    }

    .filter-block-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--pv-text-secondary);
        margin-bottom: 10px;
    }

    .filter-chip,
    .filter-select {
        min-height: 48px;
        border: 1px solid var(--pv-border-strong);
        background: var(--pv-surface-soft);
        color: var(--pv-text);
        box-shadow: none !important;
    }

    .filter-select:focus,
    .filter-chip:focus {
        border-color: var(--pv-accent);
        background: var(--pv-surface);
    }

    .filter-list-grid,
    .filter-radio-list {
        display: grid;
        gap: 10px;
    }

    .filter-list-grid .form-check,
    .filter-radio-list .form-check {
        min-height: 56px;
        margin: 0;
        padding: 10px 12px 10px 36px;
        background: var(--pv-surface);
        border: 1px solid var(--pv-border);
        display: flex;
        align-items: center;
        position: relative;
        width: 100%;
    }

    .filter-list-grid .form-check-input,
    .filter-radio-list .form-check-input {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
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

    .product-card-modern {
        background: var(--pv-surface);
        border: 1px solid var(--pv-border);
        box-shadow: var(--pv-shadow);
        min-height: 100%;
    }

    .product-card-link {
        color: inherit;
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-media {
        position: relative;
        aspect-ratio: 1 / 1;
        background: var(--pv-surface-soft);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-media img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 12px;
    }

    .product-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
        background: #c9fbff;
        color: #0f172a;
        min-width: 60px;
        min-height: 34px;
        padding: 8px 12px;
        font-size: 14px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .sold-pill {
        position: absolute;
        bottom: 10px;
        right: 10px;
        z-index: 2;
        background: rgba(15, 23, 42, 0.84);
        color: #fff;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .verified-badge-floating {
        position: absolute;
        top: 4px;
        right: 4px;
        z-index: 3;
        width: 64px;
        height: 64px;
        border-radius: 999px;
        padding: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .verified-badge-floating img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-body {
        padding: 14px 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }

    .product-brand {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--pv-text-secondary);
    }

    .product-title {
        font-size: 15px;
        font-weight: 800;
        line-height: 1.45;
        color: var(--pv-text);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: calc(1.45em * 2);
    }

    .product-meta {
        font-size: 12px;
        color: var(--pv-text-secondary);
    }

    .product-footer {
        margin-top: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .product-price {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: baseline;
        color: var(--pv-text);
    }

    .product-price strong {
        font-size: 22px;
        line-height: 1;
    }

    .old-price {
        font-size: 14px;
        text-decoration: line-through;
        color: #f97316;
        font-weight: 700;
    }

    .product-cta {
        width: 40px;
        height: 40px;
        border: 1px solid var(--pv-border);
        background: var(--pv-surface-soft);
        color: var(--pv-accent);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
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
        background: linear-gradient(180deg, var(--pv-surface) 0%, var(--pv-surface-soft) 100%);
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
        background: rgba(0, 78, 96, 0.12);
        color: var(--pv-accent);
        font-size: 26px;
    }

    .empty-state-title {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .empty-state-text {
        font-size: 14px;
        color: var(--pv-text-secondary);
        line-height: 1.6;
        margin: 0;
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

    .modal-content {
        background: var(--pv-surface);
        color: var(--pv-text);
        border: 1px solid var(--pv-border);
    }

    #modalUbicacion {
        z-index: 1305;
    }

    .modal-backdrop.show {
        z-index: 1300;
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
        .filter-layout-modern {
            grid-template-columns: 112px minmax(0, 1fr);
        }

        .filter-nav-modern {
            padding: 12px 8px 18px;
        }

        .filter-content-modern {
            padding-left: 12px;
        }

        .filter-section-heading {
            font-size: 22px;
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

    .btn-circle-action {
        width: 32px;
        height: 32px;
        background-color: var(--catalog-accent);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, background-color 0.2s;
        cursor: pointer;
        border: none;
    }

    .btn-circle-action i {
        font-size: 20px;
        line-height: 1;
    }
</style>
<link rel="stylesheet" href="filter-panels-unified.css?v=1.0.0">

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
        <div>
            <h4 class="filter-title">Filtrar productos</h4>
            <p class="filter-subtitle">Revisa lo más vendido con filtros rápidos y claros.</p>
        </div>
        <button class="filter-close" type="button" id="closeFilterPanel" aria-label="Cerrar filtros">
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
                <button type="button" class="filter-nav-item" data-filter-target="marca-modelo">
                    <span class="filter-nav-label">Marca y modelo</span>
                    <span class="filter-nav-meta" id="filterGroupCountAttributes">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="precio">
                    <span class="filter-nav-label">Precio</span>
                    <span class="filter-nav-meta" id="filterGroupCountPrice">0</span>
                </button>
            </div>

            <div class="filter-content-modern">
                <div class="filter-summary">
                    <span class="filter-summary-text">Filtros activos</span>
                    <span class="filter-summary-badge" id="filterActiveCount">0</span>
                </div>

                <section class="filter-detail-panel is-active" data-filter-panel="ubicacion-orden">
                    <div class="filter-section-heading">Ubicación y orden</div>
                    <p class="filter-section-copy">Define desde dónde quieres revisar productos vendidos hoy y cómo prefieres ordenar los resultados.</p>

                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow">
                            <i class="fi-rs-marker"></i>
                            Contexto activo
                        </div>
                        <div class="filter-info-title">Exploración geográfica del catálogo</div>
                        <p class="filter-info-text">Puedes cambiar provincia y cantón, y después ordenar por precio para revisar mejor los resultados disponibles.</p>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Ubicación</div>
                        <button type="button" id="btnUbicacionPanel" class="btn filter-chip w-100 text-start" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                            <span><i class="fi-rs-marker me-1"></i> Cambiar ubicación</span>
                        </button>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Orden</div>
                        <select class="form-select filter-select" id="selectOrderPanel">
                            <option value="todos">Por defecto</option>
                            <option value="menor">Precio mas bajo</option>
                            <option value="mayor">Precio mas alto</option>
                        </select>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="categorias">
                    <div class="filter-section-heading">Categorías</div>
                    <p class="filter-section-copy">Refina el listado por categoría y subcategoría para encontrar más rápido el tipo de producto que buscas.</p>

                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow">
                            <i class="fi-rs-apps"></i>
                            Navegación inteligente
                        </div>
                        <div class="filter-info-title">Selección por familia de producto</div>
                        <p class="filter-info-text">Marca una o varias categorías, y si aplica, afina aún más con subcategorías relacionadas.</p>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Categorías</div>
                        <div id="filtro-categorias" class="filter-list-grid"></div>
                    </div>

                    <div class="filter-block" id="subcats-box" style="display:none;">
                        <div class="filter-block-title">Subcategorías</div>
                        <div id="filtro-sub-categorias" class="filter-list-grid"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="marca-modelo">
                    <div class="filter-section-heading">Marca y modelo</div>
                    <p class="filter-section-copy">Filtra por compatibilidad comercial usando la marca y el modelo que correspondan al producto que buscas.</p>

                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow">
                            <i class="fi-rs-settings-sliders"></i>
                            Precisión
                        </div>
                        <div class="filter-info-title">Compatibilidad del inventario</div>
                        <p class="filter-info-text">Combina marca y modelo para reducir el catálogo y quedarte solo con lo que te interesa.</p>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Marca</div>
                        <div id="filtro-marca" class="filter-radio-list"></div>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Modelo</div>
                        <div id="filtro-modelo" class="filter-radio-list"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="precio">
                    <div class="filter-section-heading">Precio</div>
                    <p class="filter-section-copy">Ajusta el rango económico para ver solo los productos dentro del presupuesto que quieres revisar.</p>

                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow">
                            <i class="fi-rs-label"></i>
                            Rango activo
                        </div>
                        <div class="filter-info-title">Control de presupuesto</div>
                        <p class="filter-info-text">El deslizador te permite acotar el listado sin perder el contexto del catálogo completo.</p>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Precio</div>
                        <div class="price-summary">
                            <span id="slider-range-value1">$0</span>
                            <span id="slider-range-value2">$0</span>
                        </div>
                        <div id="slider-range"></div>
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

<section class="py-4">
    <div class="container-fluid">
        <div class="results-head">
            <div>
                <div class="results-count" id="countProductos">Encontramos 0 artículos</div>
                <div class="results-sub">Productos vendidos hoy con mejor movimiento dentro de la plataforma.</div>
            </div>
        </div>

        <div class="product-grid" id="listaProductosContainer"></div>

        <input type="hidden" id="provincia_id_hidden">
        <input type="hidden" id="provincia_nombre_hidden">
        <input type="hidden" id="canton_id_hidden">
        <input type="hidden" id="canton_nombre_hidden">

        <div class="pagination-area mt-4 pb-5">
            <nav>
                <ul class="pagination justify-content-center"></ul>
            </nav>
        </div>
    </div>
</section>

<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalUbicacionLabel">Cambiar ubicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Provincia</label>
                    <select id="selectProvincia" class="form-select"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Cantón</label>
                    <select id="selectCanton" class="form-select"></select>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-50" id="limpiarUbicacion">Limpiar</button>
                    <button type="button" class="btn btn-primary w-50" id="guardarUbicacion" style="background:#004e60;border:none;">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="js/productos_vendidos_hoy.js?v1.0.0.0.0.0.0.0.0.0.1.1.1.2"></script>