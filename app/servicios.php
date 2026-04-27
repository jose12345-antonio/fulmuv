<?php
include 'includes/header.php';

$sinCuentaMode = defined('APP_SIN_CUENTA') && APP_SIN_CUENTA;
?>

<link rel="canonical" href="https://fulmuv.com/app/servicios.php">

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>,
        productDetailPath: "<?= $sinCuentaMode ? 'detalle_productos.php?sin_cuenta=1' : 'detalle_productos.php' ?>"
    });
</script>

<style>
    :root {
        --catalog-page-bg: #f8fafc;
        --catalog-surface: #ffffff;
        --catalog-surface-soft: #eef2f7;
        --catalog-surface-muted: #f8fafc;
        --catalog-border: rgba(15, 23, 42, 0.08);
        --catalog-border-strong: #d7dde5;
        --catalog-text-primary: #0f172a;
        --catalog-text-secondary: #64748b;
        --catalog-text-muted: #94a3b8;
        --catalog-accent: #004e60;
        --catalog-accent-hover: #003a47;
        --catalog-accent-2: #0f766e;
        --catalog-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        --catalog-overlay: rgba(15, 23, 42, 0.28);
        --catalog-chip-bg: #f1f3f4;
    }

    .container-fluid {
        padding: 0 10px;
    }

    body,
    body .main.pages,
    body .page-content {
        background-color: var(--catalog-page-bg);
        color: var(--catalog-text-primary);
    }

    .toolbar-modern {
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 14px 0;
        background: color-mix(in srgb, var(--catalog-surface) 92%, transparent);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--catalog-border);
    }

    .toolbar-search {
        display: flex;
        gap: 10px;
    }

    .input-search-modern {
        background-color: var(--catalog-surface-soft) !important;
        border: 1px solid transparent !important;
        height: 48px;
        color: var(--catalog-text-primary) !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .input-search-modern::placeholder {
        color: var(--catalog-text-secondary);
    }

    .input-search-modern:focus {
        border-color: var(--catalog-accent) !important;
        background-color: var(--catalog-surface) !important;
    }

    .btn-filter-modern {
        width: 48px;
        min-width: 48px;
        background: linear-gradient(135deg, var(--catalog-accent) 0%, var(--catalog-accent-2) 100%) !important;
        color: white !important;
        border-radius: 16px !important;
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
        color: var(--catalog-text-primary);
        font-size: 15px;
        font-weight: 800;
    }

    .results-sub {
        font-size: 13px;
        color: var(--catalog-text-secondary);
        margin-top: 2px;
    }

    #listaServiciosContainer {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .service-card-modern {
        background: var(--catalog-surface);
        border: 1px solid var(--catalog-border);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        overflow: hidden;
    }

    .service-card-modern:hover {
        transform: translateY(-4px);
        border-color: rgba(0, 78, 96, 0.32);
    }

    .service-media-modern {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: var(--catalog-surface-soft);
        cursor: pointer;
    }

    .service-main-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 14px;
        background: var(--catalog-surface-soft);
    }

    .service-discount-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 46px;
        height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
    }

    .service-info-modern {
        padding: 14px 12px;
    }

    .service-title-modern {
        font-size: 14px;
        font-weight: 800;
        margin: 5px 0 10px;
        color: var(--catalog-text-primary);
        min-height: 38px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .service-price-modern {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .service-price-current {
        font-size: 16px;
        font-weight: 800;
        color: var(--catalog-accent);
    }

    .service-price-old {
        font-size: 12px;
        color: var(--catalog-text-muted);
        text-decoration: line-through;
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

    .btn-circle-action:hover {
        background-color: var(--catalog-accent-hover);
        transform: scale(1.1);
    }

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: var(--catalog-overlay);
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
        width: min(430px, 100%);
        height: 100vh;
        background: var(--catalog-surface);
        border-left: 1px solid var(--catalog-border);
        box-shadow: var(--catalog-shadow);
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
        border-bottom: 1px solid var(--catalog-border);
    }

    .filter-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--catalog-text-primary);
    }

    .filter-panel-subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: var(--catalog-text-secondary);
    }

    .filter-panel-close {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid var(--catalog-border);
        background: var(--catalog-surface-soft);
        color: var(--catalog-text-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .filter-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 20px;
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
        color: var(--catalog-text-secondary);
    }

    .filter-summary-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        background: rgba(0, 78, 96, 0.14);
        color: var(--catalog-accent);
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
        background: linear-gradient(180deg, var(--catalog-surface-soft) 0%, color-mix(in srgb, var(--catalog-surface-soft) 75%, var(--catalog-surface) 25%) 100%);
        border-right: 1px solid var(--catalog-border);
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
        color: var(--catalog-text-secondary);
        transition: background-color .2s ease, border-color .2s ease, transform .2s ease, color .2s ease;
    }

    .filter-nav-item.is-active {
        background: var(--catalog-surface);
        border-color: var(--catalog-border);
        color: var(--catalog-text-primary);
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 9px;
        background: rgba(0, 78, 96, 0.12);
        color: var(--catalog-accent);
        font-weight: 800;
        font-size: 12px;
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

    .filter-info-card {
        padding: 14px 16px;
        background: radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 38%), linear-gradient(180deg, var(--catalog-surface) 0%, var(--catalog-surface-soft) 100%);
        border: 1px solid var(--catalog-border);
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
        color: var(--catalog-accent);
        margin-bottom: 8px;
    }

    .filter-info-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--catalog-text-primary);
        margin-bottom: 4px;
    }

    .filter-info-text {
        margin: 0;
        color: var(--catalog-text-secondary);
        font-size: 13px;
        line-height: 1.55;
    }

    .filter-block {
        padding: 16px;
        background: var(--catalog-surface-muted);
        border: 1px solid var(--catalog-border);
        margin-bottom: 14px;
    }

    .filter-block-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--catalog-text-secondary);
        font-weight: 800;
        margin-bottom: 12px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 10px;
    }

    .filter-select {
        min-height: 44px;
        border: 1px solid var(--catalog-border);
        background: var(--catalog-surface);
        color: var(--catalog-text-primary);
    }

    .filter-select:focus {
        box-shadow: none;
        border-color: var(--catalog-accent);
    }

    .filter-chip-action {
        min-height: 44px;
        border: 1px solid var(--catalog-border);
        background: var(--catalog-surface);
        color: var(--catalog-text-primary);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        padding: 0 14px;
    }

    .filter-panel-footer {
        border-bottom: 0;
        border-top: 1px solid var(--catalog-border);
        display: flex;
        gap: 10px;
        padding: 18px 20px;
    }

    .btn-filter-secondary,
    .btn-filter-primary {
        flex: 1;
        min-height: 48px;
        font-weight: 800;
    }

    .btn-filter-secondary {
        background: var(--catalog-surface-soft);
        color: var(--catalog-text-primary);
        border: 1px solid var(--catalog-border);
    }

    .btn-filter-primary {
        background: linear-gradient(135deg, var(--catalog-accent) 0%, var(--catalog-accent-2) 100%);
        color: #fff;
        border: none;
        box-shadow: 0 12px 24px rgba(0, 78, 96, 0.22);
    }

    .filter-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        max-height: 320px;
        overflow-y: auto;
        align-items: stretch;
    }

    .filter-grid .form-check,
    .filter-list .form-check {
        min-height: 56px;
        margin: 0;
        padding: 10px 12px 10px 36px;
        border: 1px solid var(--catalog-border);
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
        background: var(--catalog-surface);
    }

    .filter-grid .form-check-input,
    .filter-list .form-check-input {
        position: absolute;
        left: 12px;
        top: 50%;
        margin-top: -9px;
    }

    .filter-grid .form-check-label,
    .filter-list .form-check-label {
        font-size: 13px;
        color: var(--catalog-text-primary);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        min-height: 100%;
        line-height: 1.25;
    }

    .price-pill-wrap {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-top: 12px;
    }

    .price-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 100px;
        padding: .45rem .75rem;
        border-radius: 999px;
        background: var(--catalog-surface);
        border: 1px solid var(--catalog-border);
        color: #334155;
        font-size: .8rem;
        font-weight: 700;
    }

    .pagination .page-link {
        background: var(--catalog-surface);
        border-color: var(--catalog-border);
        color: var(--catalog-text-primary);
    }

    .pagination .page-item.active .page-link {
        background: var(--catalog-accent);
        border-color: var(--catalog-accent);
        color: #fff;
    }

    .modal-content {
        background: var(--catalog-surface);
        color: var(--catalog-text-primary);
    }

    #modalUbicacion {
        z-index: 1400;
    }

    #modalUbicacion+.modal-backdrop,
    .modal-backdrop.show {
        z-index: 1390;
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
            linear-gradient(180deg, var(--catalog-surface) 0%, var(--catalog-surface-soft) 100%);
        border: 1px solid var(--catalog-border);
        box-shadow: var(--catalog-shadow);
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
        color: var(--catalog-accent);
        font-size: 26px;
    }

    .empty-state-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--catalog-text-primary);
        margin-bottom: 6px;
    }

    .empty-state-text {
        font-size: 14px;
        color: var(--catalog-text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    @media (min-width: 768px) {
        #listaServiciosContainer {
            grid-template-columns: repeat(4, 1fr);
        }

        .container-fluid {
            padding: 0 30px;
        }
    }

    @media (min-width: 1200px) {
        #listaServiciosContainer {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    @media (max-width: 767px) {
        .results-head {
            align-items: flex-start;
            flex-direction: column;
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
            <input type="text" id="inputBusqueda" class="form-control input-search-modern" placeholder="Buscar servicios de esta categoría">
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
            <h4 class="filter-panel-title">Filtrar servicios</h4>
            <p class="filter-panel-subtitle">Busca, ordena, ajusta la ubicación y refina tus resultados sin cubrir el grid.</p>
        </div>
        <button class="filter-panel-close" type="button" id="closeFilterPanel" aria-label="Cerrar filtros">
            <i class="fi-rs-cross-small"></i>
        </button>
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
                <button type="button" class="filter-nav-item" data-filter-target="nombres-servicio">
                    <span class="filter-nav-label">Nombres de servicio</span>
                    <span class="filter-nav-meta" id="filterGroupCountNombres">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="precio">
                    <span class="filter-nav-label">Precio</span>
                    <span class="filter-nav-meta" id="filterGroupCountPrecio">0</span>
                </button>
            </div>

            <div class="filter-content-modern">
                <div class="filter-detail-panel is-active" data-filter-panel="ubicacion-orden">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-marker"></i> Contexto</div>
                        <div class="filter-info-title">Ajusta ubicación y orden</div>
                        <p class="filter-info-text">Gestiona la zona, el orden de resultados y cuántos servicios quieres ver por página.</p>
                    </div>
                    <div class="filter-block">
                        <div class="filter-block-title">Ubicación y orden</div>
                        <div class="filter-grid">
                            <button type="button" id="btnUbicacionPanel" class="filter-chip-action" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <span class="text-truncate"><i class="fi-rs-marker me-1"></i>Cambiar ubicación</span>
                                <i class="fi-rs-angle-small-right"></i>
                            </button>
                            <select class="form-select filter-select" id="selectOrderPanel">
                                <option value="todos">Por defecto</option>
                                <option value="menor">Menor precio</option>
                                <option value="mayor">Mayor precio</option>
                            </select>
                            <select class="form-select filter-select" id="selectShowPanel">
                                <option value="20">Ver 20</option>
                                <option value="40">Ver 40</option>
                                <option value="60">Ver 60</option>
                                <option value="all">Ver todo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="categorias">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-apps"></i> Catálogo</div>
                        <div class="filter-info-title">Filtra por categoría</div>
                        <p class="filter-info-text">Te mostramos solo categorías disponibles dentro de los servicios visibles en esta vista.</p>
                    </div>
                    <div class="filter-block">
                        <div class="filter-block-title">Categorías</div>
                        <div class="filter-grid" id="filtro-categorias-panel"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="subcategorias">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-layers"></i> Subniveles</div>
                        <div class="filter-info-title">Afina por subcategoría</div>
                        <p class="filter-info-text">Las subcategorías se recalculan automáticamente según las categorías activas.</p>
                    </div>
                    <div class="filter-block" id="subcats-box" style="display:none;">
                        <div class="filter-block-title">Subcategorías</div>
                        <div class="filter-list" id="filtro-sub-categorias"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="marcas">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-badge"></i> Marcas</div>
                        <div class="filter-info-title">Selecciona marcas disponibles</div>
                        <p class="filter-info-text">Las marcas se actualizan con base en los filtros activos y la ubicación elegida.</p>
                    </div>
                    <div class="filter-block categories-dropdown-wrap">
                        <div class="filter-block-title">Marcas</div>
                        <div class="filter-list" id="filtro-marca"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="modelos">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-car-side"></i> Modelos</div>
                        <div class="filter-info-title">Filtra por modelo</div>
                        <p class="filter-info-text">Los modelos cambian según las marcas y categorías que ya tienes activas.</p>
                    </div>
                    <div class="filter-block categories-dropdown-wrap">
                        <div class="filter-block-title">Modelos</div>
                        <div class="filter-list" id="filtro-modelo"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="nombres-servicio">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-apps"></i> Servicios</div>
                        <div class="filter-info-title">Filtra por nombre de servicio</div>
                        <p class="filter-info-text">La lista de nombres se ajusta automáticamente según las categorías y filtros activos.</p>
                    </div>
                    <div class="filter-block categories-dropdown-wrap">
                        <div class="filter-block-title">Nombres de servicio</div>
                        <div class="filter-list" id="filtro-nombre-servicio"></div>
                    </div>
                </div>

                <div class="filter-detail-panel" data-filter-panel="precio">
                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow"><i class="fi-rs-dollar"></i> Presupuesto</div>
                        <div class="filter-info-title">Ajusta tu rango de precio</div>
                        <p class="filter-info-text">Desliza el rango para ver únicamente servicios dentro del presupuesto que necesitas.</p>
                    </div>
                    <div class="filter-block">
                        <div class="filter-block-title">Rango de precio</div>
                        <div id="slider-range"></div>
                        <div class="price-pill-wrap">
                            <span class="price-pill" id="slider-range-value1">$0</span>
                            <span class="price-pill" id="slider-range-value2">$0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-panel-footer">
        <button type="button" class="btn btn-filter-secondary" id="clearFiltersPanel">Limpiar</button>
        <button type="button" class="btn btn-filter-primary" id="applyFiltersPanel">Ver <span id="filterResultsCount">0</span> resultados</button>
    </div>
</aside>

<div class="container-fluid">
    <div class="results-head">
        <div class="results-copy">
            <div class="results-count" id="countVendedores">Encontramos 0 resultados</div>
            <div class="results-sub">Usa búsqueda, ubicación y filtros por atributos para encontrar el servicio ideal.</div>
        </div>
    </div>
    <div id="totalProductosGeneral" class="d-none">0</div>

    <div id="listaServiciosContainer"></div>

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

<div class="modal fade" id="modalUbicacion" tabindex="-1">
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
<script src="js/servicios.js?v1.0.1.1"></script>
