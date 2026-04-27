<?php
include 'includes/header.php';

$sinCuentaMode = (defined('APP_SIN_CUENTA') && APP_SIN_CUENTA) || (isset($_GET['sin_cuenta']) && $_GET['sin_cuenta'] == '1');
?>

<link rel="canonical" href="https://fulmuv.com/app/vehiculos.php">

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>,
        vehicleDetailPath: "<?= $sinCuentaMode ? 'detalle_vehiculo.php?sin_cuenta=1' : 'detalle_vehiculo.php' ?>"
    });
</script>

<style>
    :root {
        --vehicle-page-bg: #fcfcfc;
        --vehicle-surface: #ffffff;
        --vehicle-surface-soft: #f1f3f4;
        --vehicle-surface-muted: #f8fafc;
        --vehicle-border: rgba(15, 23, 42, 0.08);
        --vehicle-border-strong: #d7dde5;
        --vehicle-text-primary: #111111;
        --vehicle-text-secondary: #666666;
        --vehicle-text-muted: #888888;
        --vehicle-accent: #004e60;
        --vehicle-accent-hover: #003a47;
        --vehicle-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        --vehicle-overlay: rgba(15, 23, 42, 0.28);
        --vehicle-chip-bg: #f1f3f4;
    }

    .container-fluid {
        padding: 0 10px;
    }

    body,
    body .main.pages,
    body .page-content {
        background-color: var(--vehicle-page-bg);
        color: var(--vehicle-text-primary);
    }

    .search-container-modern {
        padding: 15px 0;
        background: color-mix(in srgb, var(--vehicle-surface) 92%, transparent);
        backdrop-filter: blur(14px);
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 1px solid var(--vehicle-border);
    }

    .input-search-modern {
        background-color: var(--vehicle-surface-soft) !important;
        border: 1px solid transparent !important;
        border-radius: 16px !important;
        padding: 10px 15px !important;
        font-size: 14px;
        height: 48px;
        color: var(--vehicle-text-primary) !important;
        box-shadow: none !important;
    }

    .input-search-modern::placeholder {
        color: var(--vehicle-text-secondary);
    }

    .input-search-modern:focus {
        border-color: var(--vehicle-accent) !important;
        background-color: var(--vehicle-surface) !important;
    }

    .btn-filter-modern {
        background: linear-gradient(135deg, var(--vehicle-accent) 0%, #0f766e 100%) !important;
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

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: var(--vehicle-overlay);
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
        background: var(--vehicle-surface);
        border-left: 1px solid var(--vehicle-border);
        box-shadow: var(--vehicle-shadow);
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
        border-bottom: 1px solid var(--vehicle-border);
    }

    .filter-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--vehicle-text-primary);
    }

    .filter-panel-subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: var(--vehicle-text-secondary);
    }

    .filter-panel-close {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid var(--vehicle-border);
        background: var(--vehicle-surface-soft);
        color: var(--vehicle-text-primary);
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
        color: var(--vehicle-text-secondary);
    }

    .filter-summary-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        border-radius: 999px;
        background: rgba(0, 78, 96, 0.14);
        color: var(--vehicle-accent);
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
        background: linear-gradient(180deg, var(--vehicle-surface-soft) 0%, color-mix(in srgb, var(--vehicle-surface-soft) 75%, var(--vehicle-surface) 25%) 100%);
        border-right: 1px solid var(--vehicle-border);
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
        color: var(--vehicle-text-secondary);
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }

    .filter-nav-item.is-active {
        background: var(--vehicle-surface);
        border-color: var(--vehicle-border);
        color: var(--vehicle-text-primary);
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
        color: var(--vehicle-accent);
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

    .filter-section-heading {
        font-size: 24px;
        font-weight: 900;
        line-height: 1.05;
        margin-bottom: 6px;
        color: var(--vehicle-text-primary);
    }

    .filter-section-copy {
        margin: 0 0 16px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--vehicle-text-secondary);
    }

    .filter-block {
        background: var(--vehicle-surface-muted);
        border: 1px solid var(--vehicle-border);
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
        color: var(--vehicle-text-secondary);
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
        background: var(--vehicle-chip-bg);
        border: 1px solid var(--vehicle-border);
        color: var(--vehicle-text-primary);
    }

    .filter-info-card {
        padding: 14px 16px;
        border-radius: 18px;
        background: radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 38%), linear-gradient(180deg, var(--vehicle-surface) 0%, var(--vehicle-surface-soft) 100%);
        border: 1px solid var(--vehicle-border);
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
        color: var(--vehicle-accent);
        margin-bottom: 8px;
    }

    .filter-info-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--vehicle-text-primary);
        margin-bottom: 4px;
    }

    .filter-info-text {
        margin: 0;
        color: var(--vehicle-text-secondary);
        font-size: 13px;
        line-height: 1.55;
    }

    .filter-grid,
    .filter-list {
        display: grid;
        gap: 10px;
        max-height: 340px;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .filter-grid .form-check,
    .filter-list .form-check {
        padding: 10px 12px;
        margin: 0;
        border: 1px solid var(--vehicle-border);
        border-radius: 14px;
        min-height: 54px;
        display: flex;
        align-items: center;
        background: var(--vehicle-surface);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }

    .filter-grid .form-check-input,
    .filter-list .form-check-input {
        margin-right: 8px;
        flex-shrink: 0;
    }

    .filter-grid .form-check-label,
    .filter-list .form-check-label {
        color: var(--vehicle-text-primary);
        font-size: 14px;
    }

    .year-range-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .vehicle-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .vehicle-card-modern {
        background: var(--vehicle-surface);
        border: 1px solid var(--vehicle-border);
        box-shadow: var(--vehicle-shadow);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .vehicle-card-modern:hover {
        transform: translateY(-4px);
        border-color: rgba(0, 78, 96, 0.32);
    }

    .vehicle-media-modern {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--vehicle-surface-soft);
        cursor: pointer;
    }

    .vehicle-main-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: var(--vehicle-surface-soft);
    }

    .vehicle-discount-badge {
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

    .vehicle-info-modern {
        padding: 14px 12px 16px;
    }

    .vehicle-brand-modern {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--vehicle-text-muted);
        margin-bottom: 4px;
    }

    .vehicle-title-modern {
        font-size: 15px;
        font-weight: 800;
        line-height: 1.25;
        margin: 0 0 10px;
        color: var(--vehicle-text-primary);
        min-height: 38px;
    }

    .vehicle-meta-modern {
        display: grid;
        gap: 5px;
        font-size: 12px;
        color: var(--vehicle-text-secondary);
        margin-bottom: 12px;
    }

    .vehicle-price-modern {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .vehicle-price-current {
        font-size: 17px;
        font-weight: 800;
        color: var(--vehicle-accent);
    }

    .vehicle-price-old {
        font-size: 12px;
        color: var(--vehicle-text-muted);
        text-decoration: line-through;
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
        background: radial-gradient(circle at top right, rgba(0, 78, 96, 0.10), transparent 34%), linear-gradient(180deg, var(--vehicle-surface) 0%, var(--vehicle-surface-soft) 100%);
        border: 1px solid var(--vehicle-border);
        box-shadow: var(--vehicle-shadow);
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
        color: var(--vehicle-accent);
        font-size: 26px;
    }

    .empty-state-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--vehicle-text-primary);
        margin-bottom: 6px;
    }

    .empty-state-text {
        font-size: 14px;
        color: var(--vehicle-text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    .pagination .page-link {
        background: var(--vehicle-surface);
        border-color: var(--vehicle-border);
        color: var(--vehicle-text-primary);
    }

    .pagination .page-item.active .page-link {
        background: var(--vehicle-accent);
        border-color: var(--vehicle-accent);
        color: #fff;
    }

    .modal-content {
        background: var(--vehicle-surface);
        color: var(--vehicle-text-primary);
    }

    @media (min-width: 768px) {
        .vehicle-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .container-fluid {
            padding: 0 30px;
        }
    }

    @media (min-width: 1200px) {
        .vehicle-grid {
            grid-template-columns: repeat(4, 1fr);
        }
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

<div class="search-container-modern shadow-sm mb-0">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <input type="text" id="searchInput" class="form-control input-search-modern" placeholder="Buscar vehículos...">
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
            <h4 class="filter-panel-title">Filtrar vehículos</h4>
            <p class="filter-panel-subtitle">Explora el inventario con filtros rápidos como en el módulo de vendedores.</p>
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
                <button type="button" class="filter-nav-item" data-filter-target="precio">
                    <span class="filter-nav-label">Precio</span>
                    <span class="filter-nav-meta" id="filterGroupCountPrecio">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="marca">
                    <span class="filter-nav-label">Marca</span>
                    <span class="filter-nav-meta" id="filterGroupCountMarca">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="modelo">
                    <span class="filter-nav-label">Modelo</span>
                    <span class="filter-nav-meta" id="filterGroupCountModelo">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="anio">
                    <span class="filter-nav-label">Año</span>
                    <span class="filter-nav-meta" id="filterGroupCountAnio">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="condicion">
                    <span class="filter-nav-label">Condición</span>
                    <span class="filter-nav-meta" id="filterGroupCountCondicion">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="tipo-auto">
                    <span class="filter-nav-label">Tipo de auto</span>
                    <span class="filter-nav-meta" id="filterGroupCountTipoAuto">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="color">
                    <span class="filter-nav-label">Color</span>
                    <span class="filter-nav-meta" id="filterGroupCountColor">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="tapiceria">
                    <span class="filter-nav-label">Tapicería</span>
                    <span class="filter-nav-meta" id="filterGroupCountTapiceria">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="climatizacion">
                    <span class="filter-nav-label">Climatización</span>
                    <span class="filter-nav-meta" id="filterGroupCountClimatizacion">0</span>
                </button>
                <button type="button" class="filter-nav-item" data-filter-target="referencia">
                    <span class="filter-nav-label">Referencia</span>
                    <span class="filter-nav-meta" id="filterGroupCountReferencia">0</span>
                </button>
            </div>
            <div class="filter-content-modern">
                <div class="filter-summary">
                    <span class="filter-summary-text">Filtros activos</span>
                    <span class="filter-summary-badge" id="filterActiveCount">0</span>
                </div>

                <section class="filter-detail-panel is-active" data-filter-panel="ubicacion-orden">
                    <div class="filter-section-heading">Ubicación y orden</div>
                    <p class="filter-section-copy">Controla la ubicación visible, el orden de resultados y cuántos vehículos mostrar por página.</p>

                    <div class="filter-info-card">
                        <div class="filter-info-eyebrow">
                            <i class="fi-rs-marker"></i>
                            Vista dinámica
                        </div>
                        <div class="filter-info-title">Consulta datos cargados desde la API</div>
                        <p class="filter-info-text">Esta pantalla consume el listado de vehículos desde la API y muestra la información con filtros compatibles con la experiencia del módulo de vendedores.</p>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Ubicación</div>
                        <div class="filter-actions-inline">
                            <button type="button" id="btnUbicacionPanel" class="btn filter-chip w-100 d-flex justify-content-between align-items-center px-3" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <span class="text-truncate small"><i class="fi-rs-marker me-1"></i>Cambiar ubicación</span>
                                <i class="fi-rs-angle-small-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Orden</div>
                        <select class="form-select filter-chip" id="selectOrderPanel">
                            <option value="todos">Por defecto</option>
                            <option value="menor">Menor precio</option>
                            <option value="mayor">Mayor precio</option>
                            <option value="km_menor">Menor kilometraje</option>
                            <option value="km_mayor">Mayor kilometraje</option>
                        </select>
                    </div>

                    <div class="filter-block">
                        <div class="filter-block-title">Cantidad por página</div>
                        <select class="form-select filter-chip" id="selectShowPanel">
                            <option value="20">20</option>
                            <option value="40">40</option>
                            <option value="60">60</option>
                            <option value="80">80</option>
                            <option value="100">100</option>
                            <option value="120">120</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="precio">
                    <div class="filter-section-heading">Precio</div>
                    <p class="filter-section-copy">Filtra vehículos dentro de un rango de precio específico.</p>
                    <div class="filter-block">
                        <div id="slider-range" class="mb-3"></div>
                        <div class="d-flex justify-content-between gap-2">
                            <span class="badge rounded-pill text-bg-light" id="slider-range-value1">$0</span>
                            <span class="badge rounded-pill text-bg-light" id="slider-range-value2">$0</span>
                        </div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="marca">
                    <div class="filter-section-heading">Marca</div>
                    <p class="filter-section-copy">Selecciona una o varias marcas para filtrar el listado.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-marca-veh"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="modelo">
                    <div class="filter-section-heading">Modelo</div>
                    <p class="filter-section-copy">Filtra por modelo específico dentro de las marcas seleccionadas.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-modelos"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="anio">
                    <div class="filter-section-heading">Año</div>
                    <p class="filter-section-copy">Define un rango de año de fabricación.</p>
                    <div class="filter-block">
                        <div class="year-range-grid">
                            <input id="anioMin" type="number" class="form-control filter-chip" placeholder="Desde" min="1900" max="2100">
                            <input id="anioMax" type="number" class="form-control filter-chip" placeholder="Hasta" min="1900" max="2100">
                        </div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="condicion">
                    <div class="filter-section-heading">Condición</div>
                    <p class="filter-section-copy">Filtra entre vehículos nuevos, usados u otras condiciones.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-condicion"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="tipo-auto">
                    <div class="filter-section-heading">Tipo de auto</div>
                    <p class="filter-section-copy">Sedán, SUV, camioneta, deportivo y más.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-tipo-auto"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="color">
                    <div class="filter-section-heading">Color</div>
                    <p class="filter-section-copy">Filtra por color exterior del vehículo.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-color"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="tapiceria">
                    <div class="filter-section-heading">Tapicería</div>
                    <p class="filter-section-copy">Filtra por tipo de tapizado interior.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-tapiceria"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="climatizacion">
                    <div class="filter-section-heading">Climatización</div>
                    <p class="filter-section-copy">Filtra por sistema de climatización del vehículo.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-climatizacion"></div>
                    </div>
                </section>

                <section class="filter-detail-panel" data-filter-panel="referencia">
                    <div class="filter-section-heading">Referencia</div>
                    <p class="filter-section-copy">Filtra por la referencia registrada en el campo <code>referencias</code> del vehículo.</p>
                    <div class="filter-block">
                        <div class="filter-list" id="filtro-referencias"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="filter-panel-footer d-flex gap-2 p-3 border-top">
        <button type="button" class="btn btn-light flex-fill" id="clearFiltersPanel">Limpiar</button>
        <button type="button" class="btn btn-primary flex-fill" id="minimizeFilterPanel">Ver resultados</button>
    </div>
</aside>

<div class="container-fluid mt-3">
    <div class="results-count mb-3 ms-1" id="countVendedores" style="font-size: 16px; font-weight: 700;">
        Encontramos <strong id="totalProductosGeneral">0</strong> resultados
    </div>

    <div class="vehicle-grid" id="listaVehiculosContainer"></div>

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

<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Elige tu ubicación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Provincia</label>
                    <select id="selectProvincia" class="form-select rounded-3"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Cantón</label>
                    <select id="selectCanton" class="form-select rounded-3"></select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary w-100 rounded-3 py-2" id="guardarUbicacion">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="js/vehiculos.js?v1.0.0.0.0.0.0.0.0.0.1.1.0.0.0.11"></script>
