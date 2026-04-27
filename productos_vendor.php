<?php
include 'includes/header.php';

$id_empresa = $_GET["q"];

echo '<input type="hidden" placeholder="" id="id_empresa" class="form-control" value=' . $id_empresa . ' />';
echo '<input type="hidden" id="id_usuario_session" value="' . (int)($_SESSION["id_usuario"] ?? 0) . '" />';
?>
<link rel="canonical" href="https://fulmuv.com/productos_vendor.php?q=<?php echo $id_empresa; ?>">

<style>
    :root {
        --vendor-app-bg: #f8fafc;
        --vendor-app-surface: #ffffff;
        --vendor-app-surface-soft: #eef2f7;
        --vendor-app-border: rgba(15, 23, 42, 0.08);
        --vendor-app-text: #0f172a;
        --vendor-app-text-secondary: #64748b;
        --vendor-app-accent: #004e60;
        --vendor-app-accent-2: #0f766e;
        --vendor-app-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

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

    /* ==== GALERÍA EN GRID 4xN ==== */
    .gallery-wrapper {
        margin-top: 40px;
    }

    .gallery-header {
        text-align: center;
        margin-bottom: 25px;
    }

    /* Contenedor de las cartas */
    .gallery-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        /* espacio entre cartas */
        justify-content: flex-start;
    }

    /* Carta */
    .gallery-item {
        position: relative;
        flex: 0 0 calc(25% - 12px);
        /* 4 por fila en desktop */
        max-width: calc(25% - 12px);
        height: 360px;
        /* tamaño específico */
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.15);
        cursor: pointer;
        background: #000;
    }

    /* Imagen dentro de la carta */
    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Overlay con info */
    .gallery-info {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        /* un poco oscuro por defecto */
        color: #fff;
        opacity: 0;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        transition: opacity 0.3s ease, background 0.3s ease;
    }

    .gallery-info h4 {
        font-size: 15px;
        margin: 0 0 4px 0;
        font-weight: 600;
        color: #FFFFFF;
        transition: font-size 0.3s ease;
    }

    .gallery-info p {
        font-size: 13px;
        margin: 0;
        color: #FFFFFF;
        opacity: 0.95;
        transition: font-size 0.3s ease;
    }

    /* Hover: más oscuro y letras más grandes */
    .gallery-item:hover .gallery-info {
        opacity: 1;
        background: rgba(15, 23, 42, 0.9);
    }

    .gallery-item:hover .gallery-info h4 {
        font-size: 18px;
    }

    .gallery-item:hover .gallery-info p {
        font-size: 14px;
    }

    /* Responsivo: 3 por fila */
    @media (max-width: 1200px) {
        .gallery-item {
            flex: 0 0 calc(33.333% - 12px);
            max-width: calc(33.333% - 12px);
        }
    }

    /* 2 por fila */
    @media (max-width: 768px) {
        .gallery-item {
            flex: 0 0 calc(50% - 12px);
            max-width: calc(50% - 12px);
        }
    }

    /* 1 por fila */
    @media (max-width: 480px) {
        .gallery-item {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    .contact-line {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-top: .4rem;
        font-size: 13px;
    }

    .icon-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .icon-wa {
        background: rgba(37, 211, 102, .15);
        color: #25D366;
    }

    .icon-tel {
        background: rgba(13, 110, 253, .12);
        color: #0d6efd;
    }

    .contact-line a {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 600;
    }

    .contact-line a:hover {
        text-decoration: underline;
    }

    .vendor-description-preview {
        margin-top: 14px;
        font-size: 14px;
        line-height: 1.6;
        color: #475569;
    }

    .vendor-description-preview.is-collapsed {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .vendor-description-toggle {
        display: inline-block;
        margin-top: 8px;
        font-weight: 700;
        color: #0d6efd;
        text-decoration: none;
    }

    .vendor-social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .vendor-social-link {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid #dbe4ec;
        background: #fff;
        color: #253d4e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 16px;
    }

    .vendor-type-switcher {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin: -14px 0 22px;
    }

    .vendor-type-chip {
        border: 1px solid #dbe4ec;
        background: #fff;
        color: #253d4e;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 999px;
        font-weight: 700;
        transition: .2s ease;
    }

    .vendor-type-chip.is-active {
        background: #3bb77e;
        border-color: #3bb77e;
        color: #fff;
        box-shadow: 0 12px 24px rgba(59, 183, 126, .18);
    }

    .product-verify-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 3;
        width: 40px;
        height: 40px;
        object-fit: contain;
        pointer-events: none;
    }

    .products-mobile-overlay {
        display: none;
    }

    .products-mobile-panel-header,
    .products-mobile-panel-footer {
        display: none;
    }

    .mobile-store-hero {
        display: none;
    }

    @media (max-width: 991.98px) {
        body.products-filter-open {
            overflow: hidden;
            background: var(--vendor-app-bg);
        }

        .mobile-store-hero {
            display: block;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.18), transparent 38%),
                linear-gradient(135deg, #ffffff 0%, #eef5f8 100%);
            border-bottom: 1px solid var(--vendor-app-border);
            padding: 10px 0 8px;
        }

        .mobile-store-hero::after {
            content: "";
            position: absolute;
            right: -80px;
            top: -60px;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: rgba(0, 78, 96, 0.10);
        }

        .mobile-store-hero-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--vendor-app-border);
            box-shadow: var(--vendor-app-shadow);
            padding: 8px;
        }

        .mobile-store-hero-top {
            display: grid;
            grid-template-columns: 74px 1fr;
            gap: 8px;
            align-items: center;
        }

        .mobile-store-logo {
            width: 74px;
            height: 74px;
            background: var(--vendor-app-surface-soft);
            border: 1px solid var(--vendor-app-border);
            overflow: hidden;
        }

        .mobile-store-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mobile-store-name-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mobile-store-name {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.1;
            margin: 0 0 4px;
            color: var(--vendor-app-text);
        }

        .mobile-store-verify {
            width: 32px;
            height: 32px;
            object-fit: contain;
            display: none;
        }

        .mobile-store-action {
            min-height: 34px;
            padding: 0 10px;
            border: none;
            background: linear-gradient(135deg, var(--vendor-app-accent) 0%, var(--vendor-app-accent-2) 100%);
            color: #fff;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border-radius: 12px;
        }

        .mobile-store-description {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.6;
            color: var(--vendor-app-text-secondary);
        }

        .mobile-store-description.is-collapsed {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .mobile-store-description-toggle {
            display: inline-block;
            margin-top: 8px;
            font-weight: 700;
            color: var(--vendor-app-accent);
            text-decoration: none;
        }

        .mobile-store-social-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .mobile-store-social-links .vendor-social-link {
            background: var(--vendor-app-surface-soft);
        }

        .archive-header-2 {
            margin-top: 0 !important;
            padding-top: 14px;
        }

        .sidebar-widget-2.widget_search {
            margin-bottom: 16px !important;
        }

        .fulmuv-pgsearch-shell {
            min-height: 48px;
            border-radius: 0;
            padding: 0 10px 0 16px;
            background: var(--vendor-app-surface-soft);
            border: 1px solid transparent;
            box-shadow: none;
        }

        .fulmuv-pgsearch-input {
            font-size: 16px;
        }

        .mobile-toolbar-search {
            display: flex;
            gap: 10px;
            align-items: stretch;
        }

        .mobile-toolbar-search .widget_search {
            flex: 1;
            margin-bottom: 0 !important;
        }

        .vendor-type-switcher {
            justify-content: flex-start;
            flex-wrap: nowrap;
            overflow-x: auto;
            margin: 0 0 16px;
            padding-bottom: 4px;
            scrollbar-width: none;
        }

        .vendor-type-switcher::-webkit-scrollbar {
            display: none;
        }

        .vendor-type-chip {
            min-height: 42px;
            white-space: nowrap;
            border-radius: 0;
            border: 1px solid var(--vendor-app-border);
            background: var(--vendor-app-surface);
            color: var(--vendor-app-text);
            font-weight: 800;
        }

        .vendor-type-chip.is-active {
            background: linear-gradient(135deg, var(--vendor-app-accent) 0%, var(--vendor-app-accent-2) 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 12px 24px rgba(0, 78, 96, .18);
        }

        .mobile-filterbar {
            margin-bottom: 18px !important;
            padding: 0;
            background: transparent;
            border-bottom: 0;
            display: none !important;
        }

        #btnToggleMobileFilters {
            width: 48px;
            min-width: 48px;
            height: 48px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--vendor-app-accent) 0%, var(--vendor-app-accent-2) 100%);
            box-shadow: 0 12px 24px rgba(0, 78, 96, 0.24);
        }

        #btnToggleMobileFilters .text-white {
            display: none;
        }

        .shop-product-fillter {
            margin-bottom: 16px;
            padding: 0;
            border: 0;
            box-shadow: none;
            background: transparent;
        }

        .shop-product-fillter .sort-by-product-area {
            display: none !important;
        }

        .shop-product-fillter .totall-product p {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: var(--vendor-app-text);
        }

        .products-mobile-overlay {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.36);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .2s ease, visibility .2s ease;
            z-index: 1040;
        }

        .products-mobile-overlay.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .fulmuv-sidebar-col {
            position: static !important;
        }

        .fulmuv-filter-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: min(100%, 420px);
            height: 100vh;
            height: 100dvh;
            margin: 0;
            background: #fff;
            box-shadow: -24px 0 48px rgba(15, 23, 42, 0.18);
            transform: translateX(100%);
            transition: transform .22s ease;
            z-index: 1050;
            overflow-y: auto;
            padding: 0 0 92px;
            border-radius: 28px 0 0 28px;
        }

        .fulmuv-filter-panel.is-open {
            transform: translateX(0);
        }

        .fulmuv-filter-panel .sidebar-widget.widget-store-info {
            display: none;
        }

        .products-mobile-panel-header,
        .products-mobile-panel-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 18px 16px;
            background: #fff;
        }

        .products-mobile-panel-header {
            position: sticky;
            top: 0;
            z-index: 2;
            border-bottom: 1px solid #e2e8f0;
        }

        .products-mobile-panel-title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        .products-mobile-panel-copy {
            margin: 4px 0 0;
            font-size: 13px;
            color: #64748b;
        }

        .products-mobile-panel-close {
            width: 42px;
            height: 42px;
            border: 1px solid #dbe4ec;
            border-radius: 14px;
            background: #f8fafc;
            color: #0f172a;
        }

        .products-mobile-panel-footer {
            position: sticky;
            bottom: 0;
            z-index: 2;
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -12px 24px rgba(15, 23, 42, 0.06);
        }

        .products-mobile-panel-footer .btn {
            min-height: 46px;
            border-radius: 14px;
            font-weight: 800;
        }

        .products-mobile-panel-footer .btn-primary {
            border: none;
            background: linear-gradient(135deg, #004e60 0%, #0f766e 100%);
        }

        .sidebar-widget.widget-store-info {
            margin: 0;
            padding: 18px 18px 0 !important;
        }

        .accordion.fulmuv-filter-accordion {
            margin-top: 18px !important;
            padding: 0 18px;
        }

        .container {
            max-width: 100%;
            padding-left: 14px;
            padding-right: 14px;
        }

        .product-grid.app-mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin: 0 !important;
        }

        .product-grid.app-mobile-grid::before,
        .product-grid.app-mobile-grid::after {
            display: none !important;
        }

        .product-grid.app-mobile-grid > * {
            width: auto !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .product-card-modern {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            height: 100%;
            border-radius: 24px;
            overflow: hidden;
        }

        .product-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .product-media {
            position: relative;
            aspect-ratio: 1 / 1;
            background: #eef2f7;
            overflow: hidden;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            min-height: 28px;
            padding: 0 10px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
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
            color: #64748b;
            margin-bottom: 8px;
        }

        .product-title {
            font-size: 15px;
            font-weight: 800;
            line-height: 1.35;
            color: #0f172a;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
            color: #2563eb;
            line-height: 1;
        }

        .product-price .old-price {
            font-size: 12px;
            color: #dc2626;
            text-decoration: line-through;
            margin-top: 4px;
            font-weight: 700;
        }

        .product-cta {
            width: 38px;
            min-width: 38px;
            height: 38px;
            border-radius: 999px;
            background: linear-gradient(135deg, #004e60 0%, #0f766e 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 24px rgba(0, 78, 96, 0.18);
        }
    }
</style>

<section class="mobile-store-hero d-lg-none">
    <div class="container">
        <div class="mobile-store-hero-card">
            <div class="mobile-store-hero-top">
                <div class="mobile-store-logo">
                    <img id="mobileStoreImage" src="img/FULMUV-NEGRO.png" alt="Empresa" onerror="this.src='img/FULMUV-NEGRO.png';">
                </div>
                <div>
                    <div class="mobile-store-name-row">
                        <h1 class="mobile-store-name" id="mobileStoreName">Cargando tienda...</h1>
                        <img id="mobileStoreVerifyBadge" class="mobile-store-verify" src="img/verificado_empresa.png" alt="Empresa verificada">
                    </div>
                    <a class="mobile-store-action" href="#" id="mobileStoreWhatsappCta" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp"></i> Comunícate por WhatsApp
                    </a>
                    <div id="mobileStoreDescriptionPreview" class="mobile-store-description is-collapsed"></div>
                    <a href="#" id="mobileStoreDescriptionToggle" class="mobile-store-description-toggle" style="display:none;">Ver más</a>
                    <div id="mobileStoreSocialLinks" class="mobile-store-social-links"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="archive-header-2 text-center mt-30">
        <!-- <h1 class="display-2 mb-50">Lista de Productos</h1> -->
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="mobile-toolbar-search">
                <div class="sidebar-widget-2 widget_search mb-50">
                    <div class="fulmuv-pgsearch-shell">
                        <span class="fulmuv-pgsearch-brain" aria-hidden="true">
                            <i class="fa-solid fa-brain"></i>
                        </span>
                        <input type="text" id="inputBusqueda" class="fulmuv-pgsearch-input" placeholder="Buscar por producto, marca o modelo..." autocomplete="off" />
                        <button type="button" class="fulmuv-pgsearch-clear" aria-label="Limpiar búsqueda">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center d-lg-none"
                    id="btnToggleMobileFilters" aria-label="Abrir filtros">
                    <i class="fi-rs-filter"></i>
                </button>
                </div>
            </div>
        </div>
    </div>
    <div class="vendor-type-switcher" id="typeSwitcherVendor">
        <button type="button" class="btn vendor-type-chip is-active" data-item-type="producto">Productos</button>
        <button type="button" class="btn vendor-type-chip" data-item-type="servicio">Servicios</button>
        <button type="button" class="btn vendor-type-chip" data-item-type="vehiculo">Vehículos</button>
    </div>
    <div class="products-mobile-overlay" id="productsMobileOverlay"></div>
    <div class="row flex-row-reverse" style="transform: none;">
        <div class="mobile-filterbar d-lg-none mb-3 justify-content-end align-items-end d-flex">
            <button type="button" class="btn btn-primary d-flex align-items-center justify-content-between"
                id="btnToggleMobileFiltersLegacy">
                <span class="d-flex align-items-center gap-2">
                    <i class="fi-rs-search"></i>
                    <span class="text-white fw-bold">Búsqueda y filtros</span>
                </span>
            </button>
        </div>
        <div class="col-lg-4-5">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <p>¡Encontramos <strong class="text-brand" id="totalProductosGeneral"></strong> artículos para ti!</p>
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
                                <li><a href="#" data-value="menor">Menor productos</a></li>
                                <li><a href="#" data-value="mayor">Mayor productos</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row product-grid">
            </div>
            <!--product grid-->

            <input type="hidden" id="provincia_id_hidden">
            <input type="hidden" id="provincia_nombre_hidden">
            <input type="hidden" id="canton_id_hidden">
            <input type="hidden" id="canton_nombre_hidden">

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
            <section class="section-padding pb-5 d-none">
                <div class="section-title">
                    <h3 class="">Deals Of The Day</h3>
                    <a class="show-all" href="shop-grid-right.html">
                        All Deals
                        <i class="fi-rs-angle-right"></i>
                    </a>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-5.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/12/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">195</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">07</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Seeds of Change Organic Quinoa, Brown</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">NestFood</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$32.85</span>
                                            <span class="old-price">$33.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-6.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2026/04/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">316</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">07</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Perdue Simply Smart Organics Gluten</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Old El Paso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$24.85</span>
                                            <span class="old-price">$26.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-lg-block">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-7.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2027/03/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">650</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">07</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">02</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Signature Wood-Fired Mushroom</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Progresso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$12.85</span>
                                            <span class="old-price">$13.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-xl-block">
                        <div class="product-cart-wrap style-2">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="themelading/nest-frontend/assets/imgs/banner/banner-8.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/02/25 00:00:00"><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> days </span></span><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> hours </span></span><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> mins </span></span><span class="countdown-section"><span class="countdown-amount hover-up">00</span><span class="countdown-period"> sec </span></span></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Simply Lemonade with Raspberry Juice</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Yoplait</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>$15.85</span>
                                            <span class="old-price">$16.8</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="shop-cart.html"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!--End Deals-->
        </div>
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar fulmuv-sidebar-col" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">

            <div class="fulmuv-filter-panel" id="mobileFilters">
                <div class="products-mobile-panel-header">
                    <div>
                        <h4 class="products-mobile-panel-title">Filtrar tienda</h4>
                        <p class="products-mobile-panel-copy">Ajusta los filtros segÃºn productos, servicios o vehÃ­culos.</p>
                    </div>
                    <button type="button" class="products-mobile-panel-close" id="closeMobileFilters" aria-label="Cerrar filtros">
                        <i class="fi-rs-cross-small"></i>
                    </button>
                </div>
                <div class="sidebar-widget widget-store-info mb-30 border-0">
                    <div class="mb-3" style="height: 200px; width: 200px">
                        <img id="imagenEmpresa" alt="" style="height: 100%; width: 100%; object-fit: contain">
                    </div>
                    <div class="vendor-info">
                        <div class="product-category">
                            <span class="text-muted" id="fechaInicioEmpresa"></span>
                        </div>
                        <h4 class="mb-5"><a href="productos_vendor.php?q=<?php echo $id_empresa; ?>" class="text-heading" id="nombreEmpresa"></a></h4>
                        <div class="vendor-info">
                            <ul class="font-sm mb-20 d-none">
                                <li><img class="mr-5" src="themelading/nest-frontend/assets/imgs/theme/icons/icon-location.svg" alt=""><strong>Dirección: </strong> <span id="direccionEmpresa"></span></li>
                                <li><img class="mr-5" src="themelading/nest-frontend/assets/imgs/theme/icons/icon-contact.svg" alt=""><strong>Teléfono:</strong><span id="telefonoEmpresa"></span></li>

                            </ul>
                            <!-- <?php if (isset($_SESSION["id_usuario"])): ?>
                                <a href="#" class="btn btn-xs">Contactar <i class="fi-rs-arrow-small-right"></i></a>
                            <?php endif; ?> -->

                            <a href="#" id="btnWhatsSidebar" class="btn btn-xs">
                                Comunícate al WhatsApp <i class="fi-rs-arrow-small-right"></i>
                            </a>

                            <div class="mt-2" id="contactosSidebar"></div>
                            <div id="descripcionEmpresaPreview" class="vendor-description-preview is-collapsed"></div>
                            <a href="#" id="toggleDescripcionEmpresa" class="vendor-description-toggle" style="display:none;">Ver más</a>
                            <div id="empresaSocialLinks" class="vendor-social-links"></div>

                        </div>
                    </div>
                </div>
                <div class="accordion fulmuv-filter-accordion mt-30" id="filtersAccordionVendor">
                    <div class="accordion-item vehicle-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingReferenciaVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReferenciaVendor" aria-expanded="false" aria-controls="collapseReferenciaVendor">
                                Referencia
                            </button>
                        </h2>
                        <div id="collapseReferenciaVendor" class="accordion-collapse collapse" aria-labelledby="headingReferenciaVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-referencias"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingMarcaVendor">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMarcaVendor" aria-expanded="true" aria-controls="collapseMarcaVendor">
                                Marca
                            </button>
                        </h2>
                        <div id="collapseMarcaVendor" class="accordion-collapse collapse show" aria-labelledby="headingMarcaVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-marca"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingModeloVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModeloVendor" aria-expanded="false" aria-controls="collapseModeloVendor">
                                Modelo
                            </button>
                        </h2>
                        <div id="collapseModeloVendor" class="accordion-collapse collapse" aria-labelledby="headingModeloVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-modelo"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item vehicle-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingAnioVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnioVendor" aria-expanded="false" aria-controls="collapseAnioVendor">
                                Año
                            </button>
                        </h2>
                        <div id="collapseAnioVendor" class="accordion-collapse collapse" aria-labelledby="headingAnioVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div class="row g-2">
                                    <div class="col-6"><input id="anioMin" type="number" class="form-control" placeholder="Desde" min="1900" max="2100"></div>
                                    <div class="col-6"><input id="anioMax" type="number" class="form-control" placeholder="Hasta" min="1900" max="2100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item vehicle-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingCondicionVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCondicionVendor" aria-expanded="false" aria-controls="collapseCondicionVendor">
                                Condición
                            </button>
                        </h2>
                        <div id="collapseCondicionVendor" class="accordion-collapse collapse" aria-labelledby="headingCondicionVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-condicion"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item vehicle-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingTipoAutoVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTipoAutoVendor" aria-expanded="false" aria-controls="collapseTipoAutoVendor">
                                Tipo de auto
                            </button>
                        </h2>
                        <div id="collapseTipoAutoVendor" class="accordion-collapse collapse" aria-labelledby="headingTipoAutoVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-tipo-auto"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item vehicle-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingColorVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseColorVendor" aria-expanded="false" aria-controls="collapseColorVendor">
                                Color
                            </button>
                        </h2>
                        <div id="collapseColorVendor" class="accordion-collapse collapse" aria-labelledby="headingColorVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-color"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item vehicle-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingTapiceriaVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTapiceriaVendor" aria-expanded="false" aria-controls="collapseTapiceriaVendor">
                                Tapicería
                            </button>
                        </h2>
                        <div id="collapseTapiceriaVendor" class="accordion-collapse collapse" aria-labelledby="headingTapiceriaVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-tapiceria"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item vehicle-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingClimatizacionVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClimatizacionVendor" aria-expanded="false" aria-controls="collapseClimatizacionVendor">
                                Climatización
                            </button>
                        </h2>
                        <div id="collapseClimatizacionVendor" class="accordion-collapse collapse" aria-labelledby="headingClimatizacionVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-climatizacion"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCategoriasVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategoriasVendor" aria-expanded="false" aria-controls="collapseCategoriasVendor">
                                Categorías
                            </button>
                        </h2>
                        <div id="collapseCategoriasVendor" class="accordion-collapse collapse" aria-labelledby="headingCategoriasVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-categorias"></div>
                            </div>
                        </div>
                    </div>

                    <div id="subcats-box" class="accordion-item" style="display:none;">
                        <h2 class="accordion-header" id="headingSubcategoriasVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubcategoriasVendor" aria-expanded="false" aria-controls="collapseSubcategoriasVendor">
                                Sub categorías
                            </button>
                        </h2>
                        <div id="collapseSubcategoriasVendor" class="accordion-collapse collapse" aria-labelledby="headingSubcategoriasVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-sub-categorias"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item service-filter-item" style="display:none;">
                        <h2 class="accordion-header" id="headingNombreServicioVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNombreServicioVendor" aria-expanded="false" aria-controls="collapseNombreServicioVendor">
                                Nombres de servicio
                            </button>
                        </h2>
                        <div id="collapseNombreServicioVendor" class="accordion-collapse collapse" aria-labelledby="headingNombreServicioVendor" data-bs-parent="#filtersAccordionVendor">
                            <div class="accordion-body">
                                <div id="filtro-nombre-servicio"></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingPrecioVendor">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrecioVendor" aria-expanded="false" aria-controls="collapsePrecioVendor">
                                Filtrar por precio
                            </button>
                        </h2>
                        <div id="collapsePrecioVendor" class="accordion-collapse collapse" aria-labelledby="headingPrecioVendor" data-bs-parent="#filtersAccordionVendor">
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
                <div class="products-mobile-panel-footer">
                    <button type="button" class="btn btn-light" id="clearMobileFilters">Limpiar</button>
                    <button type="button" class="btn btn-primary" id="applyMobileFilters">Ver resultados</button>
                </div>
                <div class="sidebar-widget p-3 range mb-30 d-none">
                    <h5 class="section-title style-1 mb-30">Filtrar por precio</h5>
                    <div class="price-filter">
                        <div class="price-filter-inner">
                            <div id="slider-range" class="mb-20 noUi-target noUi-ltr noUi-horizontal noUi-background">
                                <div class="noUi-base">
                                    <div class="noUi-origin noUi-connect" style="left: 25%;">
                                        <div class="noUi-handle noUi-handle-lower"></div>
                                    </div>
                                    <div class="noUi-origin noUi-background" style="left: 50%;">
                                        <div class="noUi-handle noUi-handle-upper"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="caption">Desde: <strong id="slider-range-value1" class="text-brand">$0</strong></div>
                                <div class="caption">Hasta: <strong id="slider-range-value2" class="text-brand">$0</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group">
                        <div class="list-group-item mb-10 mt-10">
                            <label class="fw-900">Subcategorías</label>
                            <div class="custome-checkbox">

                            </div>
                        </div>
                    </div>
                    <a class="btn btn-sm btn-default"><i class="fi-rs-filter mr-5"></i> Filtrar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="col-lg-12">
        <div class="gallery-wrapper" id="gallery-wrapper" style="display:none;">
            <div class="gallery-header">
                <h3>Galería de la empresa</h3>
                <p>Conoce algunos trabajos, productos e instalaciones</p>
            </div>

            <div class="gallery-grid" id="gallery-empresa"></div>
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
<script src="js/productos_vendor.js?v1.1.16"></script>
