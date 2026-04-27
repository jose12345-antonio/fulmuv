<?php
include 'includes/header.php';

$tema = isset($_GET['tema']) ? (int) $_GET['tema'] : 0;
$isDarkTheme = $tema === 1;
$sinCuentaMode = defined('APP_SIN_CUENTA');
$productDetailPath = $sinCuentaMode ? 'detalle_producto_sincuenta.php' : 'detalle_productos.php';
$vendorProductsPath = $sinCuentaMode ? 'productos_vendor_sincuenta.php' : 'productos_vendor.php';
$categoryProductsPath = $sinCuentaMode ? 'productos_categoria_sincuenta.php' : 'productos_categoria.php';
?>
<link rel="canonical" href="https://fulmuv.com/">

<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>,
        productDetailPath: "<?= $productDetailPath ?>",
        vendorProductsPath: "<?= $vendorProductsPath ?>",
        categoryProductsPath: "<?= $categoryProductsPath ?>"
    });
</script>

<style>
    :root {
        --app-page-bg: <?= $isDarkTheme ? '#0f172a' : '#f8fafc' ?>;
        --app-surface: <?= $isDarkTheme ? '#111827' : '#ffffff' ?>;
        --app-surface-soft: <?= $isDarkTheme ? '#1e293b' : '#f8fafc' ?>;
        --app-surface-muted: <?= $isDarkTheme ? '#0b1220' : '#fafafa' ?>;
        --app-border: <?= $isDarkTheme ? 'rgba(148, 163, 184, 0.18)' : 'rgba(15, 23, 42, 0.08)' ?>;
        --app-border-strong: <?= $isDarkTheme ? '#334155' : '#d1d5db' ?>;
        --app-text-primary: <?= $isDarkTheme ? '#e5e7eb' : '#0f172a' ?>;
        --app-text-secondary: <?= $isDarkTheme ? '#94a3b8' : '#64748b' ?>;
        --app-text-muted: <?= $isDarkTheme ? '#cbd5e1' : '#475569' ?>;
        --app-link: <?= $isDarkTheme ? '#5eead4' : '#0f766e' ?>;
        --app-price: <?= $isDarkTheme ? '#fbbf24' : '#b45309' ?>;
        --app-badge-bg: <?= $isDarkTheme ? 'rgba(15, 23, 42, 0.92)' : 'rgba(15, 23, 42, 0.78)' ?>;
        --app-shadow: <?= $isDarkTheme ? '0 14px 34px rgba(2, 6, 23, 0.45)' : '0 14px 34px rgba(15, 23, 42, 0.08)' ?>;
        --app-soft-section-bg: <?= $isDarkTheme ? 'rgba(15, 23, 42, 0.78)' : 'rgba(0, 96, 112, 0.09)' ?>;
        --app-skeleton-a: <?= $isDarkTheme ? '#1e293b' : '#e2e8f0' ?>;
        --app-skeleton-b: <?= $isDarkTheme ? '#334155' : '#f8fafc' ?>;
        --app-button-muted-bg: <?= $isDarkTheme ? '#334155' : '#e5e7eb' ?>;
        --app-button-muted-text: <?= $isDarkTheme ? '#e5e7eb' : '#111827' ?>;
    }

    body.single-product,
    body.single-product .main.pages,
    body.single-product .page-content {
        background: var(--app-page-bg);
        color: var(--app-text-primary);
    }

    body.single-product .text-dark,
    body.single-product .text-dark a,
    body.single-product h1,
    body.single-product h2,
    body.single-product h3,
    body.single-product h4,
    body.single-product h5,
    body.single-product h6 {
        color: var(--app-text-primary) !important;
    }

    .theme-soft-section {
        background-color: var(--app-soft-section-bg) !important;
    }

    <?php if ($sinCuentaMode): ?>.guest-mode-hero {
        position: relative;
        overflow: hidden;
        margin: 18px auto 24px;
        border: 1px solid var(--app-border);
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.24), transparent 35%),
            linear-gradient(135deg, var(--app-surface) 0%, var(--app-surface-soft) 100%);
        box-shadow: var(--app-shadow);
    }

    .guest-mode-hero::after {
        content: "";
        position: absolute;
        right: -60px;
        top: -50px;
        width: 180px;
        height: 180px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.14);
        filter: blur(6px);
    }

    .guest-mode-hero-body {
        position: relative;
        z-index: 1;
        padding: 24px 22px;
    }

    .guest-mode-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--app-link);
        margin-bottom: 10px;
    }

    .guest-mode-title {
        font-size: clamp(28px, 6vw, 42px);
        font-weight: 900;
        line-height: 1.03;
        margin: 0 0 10px;
        color: var(--app-text-primary);
    }

    .guest-mode-copy {
        max-width: 760px;
        color: var(--app-text-secondary);
        line-height: 1.65;
        margin: 0;
    }

    .guest-mode-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .guest-mode-chip {
        padding: 9px 14px;
        border-radius: 999px;
        border: 1px solid var(--app-border);
        background: var(--app-surface);
        color: var(--app-text-primary);
        font-size: 13px;
        font-weight: 700;
    }

    <?php endif; ?>.slick-arrow-custom {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        background-color: <?= $isDarkTheme ? 'rgba(15, 23, 42, 0.85)' : 'rgba(255, 255, 255, 0.7)' ?>;
        border: none;
        font-size: 30px;
        padding: 10px;
        border-radius: 50%;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .slick-prev {
        left: 20px;
    }

    .slick-next {
        right: 20px;
    }

    .slick-arrow-custom:hover {
        background-color: <?= $isDarkTheme ? 'rgba(30, 41, 59, 1)' : 'rgba(255, 255, 255, 1)' ?>;
    }



    /* Limitar altura del título para alinear cards */
    .product-cart-wrap {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-img-action-wrap {
        position: relative;
    }

    .product-cart-wrap .product-img img {
        width: 100%;
        height: 150px !important;
        object-fit: contain;
    }

    .product-content-wrap h2 {
        min-height: 3.2em;
        margin-bottom: 10px;
    }

    .product-content-wrap .product-price {
        margin-top: auto;
        margin-bottom: 15px;
    }

    .home-verify-overlay {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 3;
    }

    .home-verify-overlay img {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }


    .slick-prev,
    .slick-next {
        position: absolute;
        top: 50%;
        z-index: 10;
        border-radius: 50%;
        padding: 10px;
    }

    .slick-prev {
        left: 8px;
    }

    .slick-next {
        right: 8px;
    }

    .carausel-4-columns-cover,
    .carausel-8-columns-cover {
        position: relative;
    }

    .section-title.section-title-with-arrows {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .section-title.section-title-with-arrows .title {
        flex: 1;
        min-width: 0;
    }

    .section-title-arrows {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-shrink: 0;
        min-width: 92px;
    }

    .section-title-arrows .slider-btn {
        position: static !important;
        transform: none !important;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--app-border);
        background: color-mix(in srgb, var(--app-surface) 92%, transparent);
        color: var(--app-text-primary);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        backdrop-filter: blur(10px);
        transition: transform .2s ease, background-color .2s ease, box-shadow .2s ease, color .2s ease;
    }

    .section-title-arrows .slider-btn:hover {
        transform: scale(1.05) !important;
        background: var(--app-surface);
        color: var(--app-link);
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.18);
    }

    .section-title-arrows .slider-btn i {
        font-size: 20px;
        line-height: 1;
    }

    .home-carousel-section .container,
    .home-carousel-section .container-fluid {
        padding-left: 0;
        padding-right: 0;
    }

    .home-carousel-section .section-title,
    .home-carousel-section .row,
    .home-carousel-section .tab-content,
    .home-carousel-section .tab-pane {
        margin-left: 0;
        margin-right: 0;
    }

    #listInsertBanner {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
    }

    #listInsertBanner .banner-slide-link {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }

    #listInsertBanner .slick-list,
    #listInsertBanner .slick-track {
        height: 100%;
    }

    .publicidad-slot {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .publicidad-slot .publicidad-item,
    .publicidad-slot .publicidad-item-link {
        display: block;
        width: 100%;
    }

    .publicidad-slot .publicidad-item-link {
        text-decoration: none;
    }

    .publicidad-slot img {
        display: block;
        width: 100%;
        max-height: 600px;
        object-fit: fill;
    }

    .publicidad-slot .publicidad-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 4;
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--app-surface) 90%, transparent);
        color: var(--app-text-primary);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.2);
        transition: transform .2s ease, background-color .2s ease, color .2s ease;
    }

    .publicidad-slot .publicidad-arrow:hover {
        transform: translateY(-50%) scale(1.06);
        background: var(--app-surface);
        color: var(--app-link);
    }

    .publicidad-slot .publicidad-prev {
        left: 14px;
    }

    .publicidad-slot .publicidad-next {
        right: 14px;
    }

    .publicidad-slot .publicidad-arrow i {
        font-size: 18px;
        line-height: 1;
    }

    .publicidad-slot .slick-dots {
        bottom: 12px;
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .publicidad-slot .slick-dots li {
        width: auto;
        height: auto;
        margin: 0;
    }

    .publicidad-slot .slick-dots li button {
        width: 12px;
        height: 12px;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: color-mix(in srgb, var(--app-surface) 82%, transparent);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
        font-size: 0;
        color: transparent;
        line-height: 0;
    }

    .publicidad-slot .slick-dots li button::before {
        display: none;
    }

    .publicidad-slot .slick-dots li.slick-active button {
        background: var(--app-link);
        transform: scale(1.15);
    }

    @media (max-width: 767px) {
        .publicidad-slot .publicidad-arrow {
            width: 36px;
            height: 36px;
        }

        .publicidad-slot .publicidad-prev {
            left: 10px;
        }

        .publicidad-slot .publicidad-next {
            right: 10px;
        }
    }

    #listInsertBanner .slick-prev.slick-arrow-custom,
    #listInsertBanner .slick-next.slick-arrow-custom {
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 48px;
        height: 48px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--app-border);
        background: color-mix(in srgb, var(--app-surface) 94%, transparent);
        color: var(--app-text-primary);
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16);
        backdrop-filter: blur(10px);
    }

    #listInsertBanner .slick-prev.slick-arrow-custom {
        left: 14px !important;
    }

    #listInsertBanner .slick-next.slick-arrow-custom {
        right: 14px !important;
    }

    @media (max-width: 767px) {
        #listInsertBanner .slick-prev.slick-arrow-custom,
        #listInsertBanner .slick-next.slick-arrow-custom {
            width: 42px;
            height: 42px;
        }

        #listInsertBanner .slick-prev.slick-arrow-custom {
            left: 8px !important;
        }

        #listInsertBanner .slick-next.slick-arrow-custom {
            right: 8px !important;
        }

        .section-title.section-title-with-arrows {
            gap: 10px;
        }

        .section-title-arrows {
            min-width: 84px;
            gap: 6px;
        }

        .section-title-arrows .slider-btn {
            width: 36px;
            height: 36px;
        }

        .home-carousel-section {
            overflow: hidden;
        }

        .home-carousel-section .container,
        .home-carousel-section .container-fluid,
        .home-carousel-section .col-lg-10,
        .home-carousel-section .col-md-12,
        .home-carousel-section .col-lg-12 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .home-carousel-section .section-title {
            padding-left: 14px;
            padding-right: 14px;
        }

        .home-carousel-section .carausel-4-columns-cover,
        .home-carousel-section .carausel-8-columns-cover {
            margin-left: -10px;
            margin-right: -10px;
            padding-left: 10px;
            padding-right: 10px;
            overflow: visible;
        }

        .home-carousel-section .carausel-4-columns,
        .home-carousel-section .carausel-8-columns {
            width: calc(100% + 10px);
            margin-right: -10px;
        }

        .carausel-4-columns .slick-list,
        .carausel-8-columns .slick-list {
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow: visible;
        }

        .carausel-4-columns .slick-slide,
        .carausel-8-columns .slick-slide {
            padding-left: 0;
            padding-right: 0;
        }
    }

    .home-vehicle-card,
    .home-event-card,
    .home-job-card {
        border: 1px solid var(--app-border);
        border-radius: 18px;
        background: var(--app-surface);
        box-shadow: var(--app-shadow);
        overflow: hidden;
        height: 100%;
        margin: 0 .15rem;
        display: flex;
        flex-direction: column;
    }

    .home-vehicle-media,
    .home-event-media,
    .home-job-media {
        position: relative;
        width: 100%;
        height: 210px;
        background: var(--app-surface-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .home-vehicle-media > img,
    .home-event-media > img,
    .home-job-media > img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .home-vehicle-verify {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 40px !important;
        height: 40px !important;
        object-fit: contain;
        z-index: 2;
        max-width: 40px;
        max-height: 40px;
        pointer-events: none;
    }

    .home-card-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .35rem .65rem;
        background: var(--app-badge-bg);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
    }

    .home-vehicle-content,
    .home-event-content,
    .home-job-content {
        padding: .95rem 1rem 1rem;
        min-height: 138px;
    }

    .home-vehicle-title,
    .home-event-title,
    .home-job-title {
        color: var(--app-text-primary);
        font-weight: 700;
        line-height: 1.22;
        margin-bottom: .45rem;
        min-height: 2.6em;
    }

    .home-vehicle-meta,
    .home-event-meta,
    .home-job-meta {
        color: var(--app-text-secondary);
        font-size: .82rem;
        display: flex;
        flex-wrap: wrap;
        gap: .4rem .8rem;
        margin-bottom: .65rem;
    }

    .home-vehicle-price {
        color: var(--app-price);
        font-weight: 800;
        text-align: center;
        min-height: 48px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .home-event-desc,
    .home-job-desc {
        color: var(--app-text-muted);
        font-size: .86rem;
        line-height: 1.55;
        min-height: 4.6em;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .home-card-link {
        color: var(--app-link);
        font-weight: 700;
        text-decoration: none;
    }

    .home-loading-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 1rem;
    }

    .home-loading-card {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, var(--app-surface) 0%, var(--app-surface-soft) 100%);
        border: 1px solid var(--app-border);
        box-shadow: var(--app-shadow);
        min-height: 340px;
    }

    .home-loading-media,
    .home-loading-line,
    .home-loading-pill {
        background: linear-gradient(90deg, var(--app-skeleton-a) 25%, var(--app-skeleton-b) 37%, var(--app-skeleton-a) 63%);
        background-size: 400% 100%;
        animation: homeSkeleton 1.4s ease infinite;
    }

    .home-loading-media {
        width: 100%;
        height: 210px;
    }

    .home-loading-content {
        padding: .95rem 1rem 1rem;
    }

    .home-loading-caption {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .82rem;
        font-weight: 700;
        color: var(--app-text-muted);
        margin-bottom: .9rem;
    }

    .home-loading-caption::before {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #0f766e;
        box-shadow: 0 0 0 6px rgba(15, 118, 110, .12);
    }

    .home-loading-line {
        height: 12px;
        border-radius: 999px;
        margin-bottom: .65rem;
    }

    .home-loading-line.w-90 {
        width: 90%;
    }

    .home-loading-line.w-75 {
        width: 75%;
    }

    .home-loading-line.w-60 {
        width: 60%;
    }

    .home-loading-pill {
        width: 44%;
        height: 26px;
        border-radius: 999px;
        margin-top: .8rem;
    }

    @keyframes homeSkeleton {
        0% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0 50%;
        }
    }

    @media (max-width: 991px) {
        .home-loading-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .home-vehicle-media,
        .home-event-media,
        .home-job-media {
            height: 190px;
        }
    }

    @media (max-width: 575px) {
        .home-loading-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .home-loading-card {
            min-height: 310px;
        }

        .home-vehicle-media,
        .home-event-media,
        .home-job-media {
            height: 175px;
        }
    }


    /* Botón alineado al fondo */
    .product-cart-wrap .btn {
        margin-top: auto;
    }

    /* Fondo oscuro del modal */
    #modal-config-cookies-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
        display: none;
        align-items: center;
        justify-content: center;
    }

    /* Caja del modal */
    #modal-config-cookies {
        background: var(--app-surface);
        max-width: 400px;
        width: 90%;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, .2);
        padding: 20px 24px;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        position: relative;
    }

    /* Header modal */
    #modal-config-cookies h5 {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 4px;
    }

    #modal-config-cookies p.desc {
        font-size: 13px;
        color: var(--app-text-secondary);
        margin: 0 0 16px;
        line-height: 1.4;
    }

    /* Bloque de cada categoría */
    .cookie-group {
        border: 1px solid var(--app-border);
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 12px;
        background: var(--app-surface-muted);
    }

    .cookie-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cookie-group-title {
        font-size: 14px;
        font-weight: 600;
        margin: 0;
    }

    .cookie-group-desc {
        font-size: 12px;
        color: var(--app-text-secondary);
        margin-top: 4px;
        line-height: 1.4;
    }

    /* Toggle estilo switch */
    .switch-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--app-text-muted);
    }

    .switch-wrap input[type="checkbox"] {
        width: 38px;
        height: 20px;
        appearance: none;
        background: #d1d5db;
        border-radius: 999px;
        position: relative;
        cursor: pointer;
        outline: none;
        transition: all .2s;
    }

    .switch-wrap input[type="checkbox"]:checked {
        background: #4ade80;
        /* verde suave */
    }

    .switch-wrap input[type="checkbox"]::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 2px;
        width: 16px;
        height: 16px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .3);
        transition: all .2s;
    }

    .switch-wrap input[type="checkbox"]:checked::after {
        left: 20px;
    }

    /* Footer botones */
    .modal-footer-cookies {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .modal-btn {
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        padding: 8px 12px;
    }

    #btn-cancelar-config {
        background: var(--app-button-muted-bg);
        color: var(--app-button-muted-text);
    }

    #btn-guardar-config {
        background: #111827;
        color: #fff;
    }

    /* Banner cookies base */
    #cookie-banner {
        position: fixed;
        bottom: 20px;
        left: 20px;
        right: 20px;
        z-index: 9997;
        background: var(--app-surface);
        border: 1px solid var(--app-border-strong);
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, .15);
        display: none;
        max-width: 100%;
        width: calc(100% - 40px);
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .cookie-inner {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cookie-text p {
        font-size: 14px;
        color: var(--app-text-primary);
        line-height: 1.4;
        margin: 0 0 8px;
    }

    .cookie-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cookie-btn {
        flex: 1;
        min-width: 90px;
        font-size: 14px;
        font-weight: 600;
        border: 0;
        border-radius: 8px;
        padding: 10px 12px;
        cursor: pointer;
    }

    #cookie-btn-rechazar {
        background: var(--app-button-muted-bg);
        color: var(--app-button-muted-text);
    }

    #cookie-btn-configurar {
        background: var(--app-surface);
        border: 1px solid var(--app-border-strong);
        color: var(--app-text-primary);
    }

    #cookie-btn-aceptar {
        background: #111827;
        color: #fff;
    }

    @media(min-width:480px) {
        .cookie-inner {
            flex-direction: row;
            align-items: flex-start;
        }

        .cookie-text {
            flex: 1;
        }

        .cookie-actions {
            flex-direction: column;
            flex: 0 0 140px;
        }
    }

    /* Base: que el slider no colapse */
    #listInsertBanner .single-hero-slider {
        width: 100%;
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        background-color: var(--app-surface-soft);
        border-radius: 14px;
    }

    /* Desktop */
    @media (min-width: 992px) {
        #listInsertBanner .single-hero-slider {
            min-height: 420px;
            /* ajusta a tu gusto */
        }
    }

    /* Tablet */
    @media (min-width: 768px) and (max-width: 991px) {
        #listInsertBanner .single-hero-slider {
            min-height: 320px;
        }
    }

    /* Mobile */
    @media (max-width: 767px) {
        #listInsertBanner .single-hero-slider {
            min-height: 220px;
            /* aquí se arregla el “pequeño” */
        }
    }

    @media (max-width: 767px) {
        #listInsertBanner .single-hero-slider {
            aspect-ratio: 16 / 9;
            min-height: auto;
        }
    }
</style>

<section class="home-slider position-relative mb-30 mt-30">
    <div class="container">
        <div class="home-slide-cover">
            <div class="hero-slider-1 style-4 dot-style-1 dot-style-1-position-1" id="listInsertBanner">
            </div>
        </div>
    </div>
</section>



<section class="popular-categories section-padding mb-30 py-3 d-none theme-soft-section">
    <div class="container wow animate__ animate__fadeIn animated">
        <div class="section-title section-title-with-arrows mb-2">
            <div class="title d-flex justify-content-between align-items-center">
                <h3 class="titulo-subrayado">Comprar por Categorías</h3>
            </div>
            <div class="section-title-arrows" id="carausel-8-columns-arrows"></div>
        </div>

        <div class="carausel-8-columns-cover position-relative">
            <div class="carausel-8-columns" id="carausel-8-columns"></div>
        </div>

        <div class="d-flex justify-content-end mt-2" id="viewButtonCategory">
            <button id="btn-ver-mas-categorias"
                class="btn btn-outline-primary d-flex align-items-center justify-content-center"
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0;"
                onclick="cargarExtraCategorias()">
                <i class="fi-rs-angle-small-down"></i>
            </button>
        </div>

        <div class="row mt-4 vendor-grid" id="masCategoriasGrid" style="display: none;"></div>
        <div class="pagination-area mb-10 d-flex justify-content-center align-items-center">
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-start">

                </ul>
            </nav>
        </div>
    </div>
</section>

<?php if ($sinCuentaMode): ?>
    <section class="container">
        <div class="guest-mode-hero">
            <div class="guest-mode-hero-body">
                <div class="guest-mode-eyebrow">
                    <i class="fi-rs-compass"></i>
                    Explorar sin cuenta
                </div>
                <h1 class="guest-mode-title">Descubre productos, servicios y oportunidades sin iniciar sesión</h1>
                <p class="guest-mode-copy">Navega banners, publicidades, recomendados, servicios destacados, vehículos recién llegados, empleos y eventos desde una experiencia pública pensada para explorar primero y decidir después.</p>
                <div class="guest-mode-chip-row">
                    <span class="guest-mode-chip">Sin carrito</span>
                    <span class="guest-mode-chip">Detalles públicos</span>
                    <span class="guest-mode-chip">Tiendas visibles</span>
                    <span class="guest-mode-chip">Carga rápida</span>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!$sinCuentaMode): ?>
    <section class="section-padding pb-5 py-0 mb-30 home-carousel-section">
        <div class="container">
        <div class="section-title section-title-with-arrows wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <a href="productos_vendidos_hoy.php" onclick="return navegarDesdeIndex('tendencias_hoy', 'productos_vendidos_hoy.php');">
                    <h3 class="titulo-subrayado">Tendencias del día</h3>
                </a>
            </div>
            <div class="section-title-arrows" id="carausel-4-columns-arrows-tendencias"></div>
        </div>
            <div class="row">
                <div class="col-lg-2 d-none d-lg-flex wow animate__animated animate__fadeIn">
                    <div class="banner-img style-2 d-flex align-items-center justify-content-center text-center text-white"
                        style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('img/2149030399.jpg'); 
                background-size: cover; background-position: center; height: 300px;">

                        <div class="p-2">
                            <h4 class="fw-bold mb-1 text-white">Todo para tu vehículo</h4>
                            <p class="fw-bold mb-2 text-white">Repuestos, mantenimiento y más.</p>
                            <a href="productos_vendidos_hoy.php" class="btn btn-md btn-light" onclick="return navegarDesdeIndex('tendencias_hoy', 'productos_vendidos_hoy.php');">Compra ahora <i class="fi-rs-arrow-small-right"></i></a>
                        </div>

                    </div>
                </div>

                <div class="col-lg-10 col-md-12 wow animate__animated animate__fadeIn" data-wow-delay=".4s">
                    <div class="tab-content" id="myTabContent-1">
                        <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                            <div class="carausel-4-columns-cover arrow-center position-relative">
                                <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns">

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End tab-content-->
                </div>
                <!--End Col-lg-9-->
            </div>
        </div>
    </section>
<?php endif; ?>
<section class="section-padding pb-5 mb-30">
    <div class="container p-0">
        <div class="w-100">
            <div class="publicidad-slot" id="imgpublicidad"></div>
        </div>
    </div>
</section>

<section class="product-tabs section-padding position-relative py-0 mb-30 d-none">
    <div class="container">
        <div class="section-title wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <h3 class="titulo-subrayado">Indispensables del camino</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="banner-img mb-sm-0 wow animate__ animate__fadeInUp animated w-100 animated" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                    <img src="img/1416.jpg" alt="" style="height: 500px; object-fit: cover" class="w-100">
                    <div class="banner-text">
                        <h3 class="text-white fw-bold mb-2">
                            Repuestos de Sol<br>
                            Protege tu vehículo<br>
                            con calidad superior
                        </h3>
                        <a href="#" class="btn btn-primary">Ver productos <i class="fi-rs-arrow-small-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="banner-img mb-sm-0 wow animate__ animate__fadeInUp animated w-100 animated" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                    <img src="img/2148321920.jpg" alt="" style="height: 500px; object-fit: cover" class="w-100">
                    <div class="banner-text">
                        <h3 class="text-white fw-bold mb-2">
                            Repuestos para Lluvia<br>
                            Maneja seguro en<br>
                            cualquier clima
                        </h3>
                        <a href="#" class="btn btn-primary">Ver productos <i class="fi-rs-arrow-small-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Repuestos de Maleta -->
            <div class="col-lg-4">
                <div class="banner-img mb-sm-0 wow animate__ animate__fadeInUp animated w-100" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                    <img src="img/2149593887.jpg" alt="" style="height: 500px; object-fit: cover" class="w-100">
                    <div class="banner-text">
                        <h3 class="text-white fw-bold mb-2">
                            Repuestos de Maleta<br>
                            Todo lo que tu maletero<br>
                            necesita
                        </h3>
                        <a href="#" class="btn btn-primary">Ver productos <i class="fi-rs-arrow-small-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="product-tabs section-padding position-relative mb-30 theme-soft-section home-carousel-section">
    <div class="container">
        <div class="section-title section-title-with-arrows wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <a href="ofertas_imperdibles.php" onclick="return navegarDesdeIndex('ofertas_imperdibles', 'ofertas_imperdibles.php');">
                    <h3 class="titulo-subrayado">Ofertas imperdibles</h3>
                </a>
            </div>
            <div class="section-title-arrows" id="carausel-4-columns-arrows-oferta"></div>
        </div>
        <div class="tab-content" id="myTabContent-1">
            <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                <div class="carausel-4-columns-cover arrow-center position-relative">
                    <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-oferta">

                    </div>
                </div>
            </div>
        </div>
        <!--End tab-content-->
    </div>
</section>


<section class="section-padding pb-5 mb-30">
    <div class="container p-0">
        <div class="w-100">
            <div class="publicidad-slot" id="imgpublicidad2"></div>
        </div>
    </div>
</section>

<section class="section-padding pb-5 mb-30 theme-soft-section home-carousel-section">
    <div class="container">
        <div class="section-title section-title-with-arrows wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <a href="<?= $categoryProductsPath ?>?q=2" onclick="return navegarDesdeIndex('recomendados', '<?= $categoryProductsPath ?>?q=2', { categoryId: 2 });">
                    <h3 class="titulo-subrayado">Recomendados para ti</h3>
                </a>
            </div>
            <div class="section-title-arrows" id="carausel-4-columns-arrows-recomendados"></div>
        </div>
        <div class="tab-content" id="myTabContent-1">
            <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                <div class="carausel-4-columns-cover arrow-center position-relative">
                    <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-para-ti">

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding pb-5 py-0 mb-30 home-carousel-section">
    <div class="container">
        <div class="section-title section-title-with-arrows wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <a href="servicios.php" onclick="return navegarDesdeIndex('servicios_destacados', 'servicios.php');">
                    <h3 class="titulo-subrayado">Servicios destacados</h3>
                </a>
            </div>
            <div class="section-title-arrows" id="carausel-4-columns-arrows-servicios"></div>
        </div>

        <div class="row">
            <!-- Carrusel a la IZQUIERDA -->
            <div class="col-lg-10 col-md-12 wow animate__animated animate__fadeIn order-lg-1" data-wow-delay=".4s">
                <div class="tab-content" id="myTabContent-1">
                    <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                        <div class="carausel-4-columns-cover arrow-center position-relative">
                            <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-servicio">
                                <!-- items del carrusel -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner a la DERECHA -->
            <div class="col-lg-2 d-none d-lg-flex wow animate__animated animate__fadeIn order-lg-2">
                <div class="banner-img style-2 d-flex align-items-center justify-content-center text-center text-white"
                    style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('img/2149030399.jpg');
                    background-size: cover; background-position: center; height: 300px;">
                    <div class="p-2">
                        <h4 class="fw-bold mb-1 text-white">Servicios para tu vehículo</h4>
                        <p class="fw-bold mb-2 text-white">Instalación, mantenimiento y diagnóstico.</p>
                        <a href="servicios.php" class="btn btn-md btn-light" onclick="return navegarDesdeIndex('servicios_destacados', 'servicios.php');">
                            Ver servicios <i class="fi-rs-arrow-small-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- End Banner -->
        </div>
    </div>
</section>

<section class="section-padding pb-5 mb-30 theme-soft-section home-carousel-section">
    <div class="container">
        <div class="section-title section-title-with-arrows wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <a href="vehiculos.php" onclick="return navegarDesdeIndex('vehiculos_recien_llegados', 'vehiculos.php');">
                    <h3 class="titulo-subrayado">Vehículos recién llegados</h3>
                </a>
            </div>
            <div class="section-title-arrows" id="carausel-4-columns-arrows-vehiculos"></div>
        </div>
        <div class="tab-content" id="myTabContent-1">
            <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                <div class="carausel-4-columns-cover arrow-center position-relative">
                    <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-vehiculos">

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding pb-5 mb-30 home-carousel-section" id="section-eventos">
    <div class="container">
        <div class="section-title section-title-with-arrows wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <a href="eventos.php" onclick="return navegarDesdeIndex('eventos', 'eventos.php');">
                    <h3 class="titulo-subrayado">Eventos</h3>
                </a>
            </div>
            <div class="section-title-arrows" id="carausel-4-columns-arrows-eventos"></div>
        </div>

        <div class="tab-content" id="myTabContent-1">
            <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                <div class="carausel-4-columns-cover arrow-center position-relative">
                    <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-eventos"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding pb-5 mb-30 theme-soft-section home-carousel-section" id="section-empleos">
    <div class="container">
        <div class="section-title section-title-with-arrows wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <a href="empleos.php" onclick="return navegarDesdeIndex('empleos', 'empleos.php');">
                    <h3 class="titulo-subrayado">Empleos</h3>
                </a>
            </div>
            <div class="section-title-arrows" id="carausel-4-columns-arrows-empleos"></div>
        </div>

        <div class="tab-content" id="myTabContent-1">
            <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                <div class="carausel-4-columns-cover arrow-center position-relative">
                    <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-empleos"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Overlay + Modal de configuración de cookies -->
<div id="modal-config-cookies-overlay">
    <div id="modal-config-cookies">
        <h5>Preferencias de cookies</h5>
        <p class="desc">Controla qué tipos de cookies quieres permitir. Puedes cambiar esto cuando quieras.</p>

        <!-- Cookies esenciales -->
        <div class="cookie-group">
            <div class="cookie-group-header">
                <p class="cookie-group-title">Estrictamente necesarias</p>
                <div class="switch-wrap">
                    <input type="checkbox" checked disabled />
                    <span>Siempre activas</span>
                </div>
            </div>
            <div class="cookie-group-desc">
                Requeridas para el funcionamiento básico del sitio (inicio de sesión, carrito, seguridad).
            </div>
        </div>

        <!-- Cookies analíticas -->
        <div class="cookie-group">
            <div class="cookie-group-header">
                <p class="cookie-group-title">Analíticas y rendimiento</p>
                <div class="switch-wrap">
                    <input type="checkbox" id="toggle-analiticas" />
                    <span id="label-analiticas">Desactivadas</span>
                </div>
            </div>
            <div class="cookie-group-desc">
                Nos ayudan a entender cómo navegas para mejorar la experiencia (por ejemplo, Google Analytics).
            </div>
        </div>

        <!-- Cookies de personalización / publicidad -->
        <div class="cookie-group">
            <div class="cookie-group-header">
                <p class="cookie-group-title">Publicidad / personalización</p>
                <div class="switch-wrap">
                    <input type="checkbox" id="toggle-publicidad" />
                    <span id="label-publicidad">Desactivadas</span>
                </div>
            </div>
            <div class="cookie-group-desc">
                Guardan tus preferencias y permiten mostrarte anuncios relevantes.
            </div>
        </div>

        <div class="modal-footer-cookies">
            <button class="modal-btn" id="btn-cancelar-config">Cancelar</button>
            <button class="modal-btn" id="btn-guardar-config">Guardar preferencias</button>
        </div>
    </div>
</div>

<div id="cookie-banner">
    <div class="cookie-inner">
        <div class="cookie-text">
            <p><strong>FULMUV utiliza cookies</strong> propias y de terceros para garantizar el correcto funcionamiento del sitio, analizar la navegación, personalizar contenidos y mostrar publicidad relacionada con tus intereses. Puedes aceptar todas las cookies, configurarlas o rechazarlas en cualquier momento.</p>
            <p style="font-size:13px; margin-bottom:10px;">
                Más información en nuestra
                <a href="documentos/1_Política_Privacidad_Cookies_FULMUV.pdf" target="_blank" rel="noopener noreferrer">
                    Política de Privacidad y Cookies
                </a>.
            </p>
            <p style="font-size:12px;color:#666;margin:0;">
                Solo activamos cookies esenciales por defecto.
            </p>
        </div>

        <div class="cookie-actions">
            <button id="cookie-btn-rechazar" class="cookie-btn">Rechazar</button>
            <button id="cookie-btn-configurar" class="cookie-btn">Configurar</button>
            <button id="cookie-btn-aceptar" class="cookie-btn">Aceptar todas</button>
        </div>
    </div>
</div>
<?php
include 'includes/footer.php';
?>


<script>

</script>


<script>
    $(".breadcrumb-wrap").addClass("d-none")

    let categoriasExtraData = [];
    let currentPage = 1;
    const itemsPerPage = 18;
    let searchText = ""; // puedes usarlo para filtro posterior si deseas
    // IP del usuario desde PHP (fallback al backend si viene vacío)
    const USER_IP = window.USER_IP || ""; // define en PHP: <script>window.USER_IP="<?= $_SERVER['REMOTE_ADDR'] ?? '' ?>"

    const COOKIE_STORAGE_KEY = "fulmuv_cookieConsent";
    const COOKIE_VERSION = "v1";
    const REJECT_COOLDOWN_HOURS = 2;

    // Abrir modal al pulsar "Configurar" (NO ocultamos el banner)
    $(document).on("click", "#cookie-btn-configurar", function() {
        openConfigModal();
    });

    function openConfigModal() {
        preloadTogglesFromLocal();
        $("#modal-config-cookies-overlay").css("display", "flex");
    }

    function updateToggleLabels() {
        $("#label-analiticas").text($("#toggle-analiticas").is(":checked") ? "Activadas" : "Desactivadas");
        $("#label-publicidad").text($("#toggle-publicidad").is(":checked") ? "Activadas" : "Desactivadas");
    }

    $(document).on("change", "#toggle-analiticas, #toggle-publicidad", updateToggleLabels);


    function preloadTogglesFromLocal() {
        const local = getConsent && getConsent(); // ya definida en tu código
        if (local && local.optional) {
            $("#toggle-analiticas").prop("checked", !!local.optional.analiticas);
            $("#toggle-publicidad").prop("checked", !!local.optional.publicidad);
            updateToggleLabels();
        } else {
            // por defecto apagadas (como en el texto del banner)
            $("#toggle-analiticas").prop("checked", false);
            $("#toggle-publicidad").prop("checked", false);
            updateToggleLabels();
        }
    }

    function closeConfigModal() {
        $("#modal-config-cookies-overlay").hide();
    }

    // Cerrar modal con "Cancelar"
    $(document).on("click", "#btn-cancelar-config", function() {
        closeConfigModal();
    });

    function getIndexAppPath(key, fallback) {
        if (window.APP_MODE_CONFIG?.sinCuenta) {
            return window.APP_MODE_CONFIG?.[key] || fallback;
        }
        return fallback;
    }

    function resolveIndexNavigationUrl(fallbackUrl) {
        if (!window.APP_MODE_CONFIG?.sinCuenta) {
            return fallbackUrl;
        }

        const replacements = [
            ["servicios.php", "servicios_sincuenta.php"],
            ["vehiculos.php", "vehiculos.php?sin_cuenta=1"],
            ["vehiculos_sincuenta.php", "vehiculos.php?sin_cuenta=1"],
            ["eventos.php", "eventos_sincuenta.php"],
            ["empleos.php", "empleos_sincuenta.php"],
            ["vendor.php", "vendor_sincuenta.php"],
            ["productos_vendor.php", "productos_vendor_sincuenta.php"],
            ["productos_categoria.php", "productos_categoria_sincuenta.php"],
            ["detalle_eventos.php", "detalle_eventos_sincuenta.php"]
        ];

        for (const [from, to] of replacements) {
            if (fallbackUrl.includes(from)) {
                return fallbackUrl.replace(from, to);
            }
        }

        return fallbackUrl;
    }

    function navegarDesdeIndex(route, fallbackUrl, extra = {}) {
        const resolvedUrl = resolveIndexNavigationUrl(fallbackUrl);
        const payload = Object.assign({
            route: route,
            url: resolvedUrl
        }, extra || {});

        if (!window.APP_MODE_CONFIG?.sinCuenta && window.flutter_inappwebview?.callHandler) {
            window.flutter_inappwebview.callHandler('navFromIndex', payload);
            return false;
        }

        window.location.href = resolvedUrl;
        return false;
    }

    $(document).ready(function() {
        initConsent(); // <- aquí se decide mostrar/ocultar según GET + localStorage


        actualizarIconoCarrito();
        cargarBanner()
        cargarCategorias('producto');
        <?php if (!$sinCuentaMode): ?>
            cargarProductosVendidos();
        <?php endif; ?>
        cargarServiciosVendidos();
        cargarPublicidad();
        cargarProductosOferta();
        cargarProductosGenialParati()
        cargarVehiculosLlegados()
        cargarEmpleos()
        cargarEventos()
        $(document).on('click', '.filtro-categoria', function(e) {
            e.preventDefault();

            $('.filtro-categoria').removeClass('active');
            $(this).addClass('active');

            const tipoSeleccionado = $(this).data('tipo'); // 'producto' o 'servicio'
            cargarCategorias(tipoSeleccionado);
        });


    })

    // ---------- Utils de tiempo para logs/decisiones ----------
    function parseServerTsToMs(tsStr) {
        // tsStr tipo "2025-11-05 10:30:00"
        if (!tsStr) return 0;
        // Safari-friendly: "YYYY-MM-DDTHH:mm:ss"
        return Date.parse(tsStr.replace(' ', 'T')) || 0;
    }

    function hoursDiffMs(fromMs, toMs) {
        return (toMs - fromMs) / (1000 * 60 * 60);
    }

    // ---------- Mostrar/Ocultar con logs ----------
    function showCookieBannerWithLog(reason) {
        $("#cookie-banner").fadeIn(200);
    }

    function hideCookieBannerWithLog(reason) {
        $("#cookie-banner").fadeOut(200);
    }

    // ---------- Decisor asíncrono: localStorage + backend ----------
    async function shouldShowBannerAsync() {
        const now = Date.now();
        const local = getConsent(); // {version, decision, timestamp, optional:{...}}


        // 1) Si hay localStorage, úsalo primero
        if (local) {
            // versión cambió => hay que pedir consent otra vez
            if (local.version !== COOKIE_VERSION) {
                return true;
            }
            // Aceptó (accept_all o configurar) => no mostrar
            if (local.decision === 'accept_all' || local.decision === 'configurar') {
                return false;
            }
            // Rechazó => aplicar cooldown local
            if (local.decision === 'reject') {
                const diffH = hoursDiffMs(local.timestamp, now);
                return diffH >= REJECT_COOLDOWN_HOURS; // true = mostrar de nuevo, false = aún en cooldown
            }
        } else {}

        // 2) Consultar backend por IP+versión
        try {
            const check = await apiCheckConsent(USER_IP, COOKIE_VERSION);

            if (!check || check.error) {
                return true;
            }

            if (!check.exists) {
                return true;
            }

            // Hay registro en backend
            const row = check.data;

            const decision = String(row.decision || '').toLowerCase();
            const serverTsMs = parseServerTsToMs(row.ts_ms);
            const diffH = hoursDiffMs(serverTsMs, now);

            if (decision === 'accept_all' || decision === 'configurar') {
                return false;
            }
            if (decision === 'reject') {
                return diffH >= REJECT_COOLDOWN_HOURS;
            }

            return true;
        } catch (e) {
            return true;
        }
    }

    // ---------- Init maestro ----------
    async function initConsent() {
        try {
            const mustShow = await shouldShowBannerAsync();
            if (mustShow) {
                showCookieBannerWithLog('initConsent() decisión=SHOW');
            } else {
                hideCookieBannerWithLog('initConsent() decisión=HIDE');
            }
        } catch (e) {
            showCookieBannerWithLog('initConsent() catch');
        }
    }

    // ---- helpers API (robustos)
    function apiCheckConsent(ip, version) {
        const payload = {};
        if (ip && ip.trim() !== "") payload.ip = ip.trim();
        payload.version = version;

        return $.ajax({
                url: '../api/v1/fulmuv/cookies/consentExist',
                method: 'POST', // 🔹 Cambiado a POST
                data: payload, // 🔹 Enviamos como body x-www-form-urlencoded
                dataType: 'json',
                cache: false
            })
            .done(res => {})
            .fail((xhr, status, err) => {});
    }


    function apiPostConsent(payload) {

        return $.ajax({
            url: '../api/v1/fulmuv/cookies/consent',
            method: 'POST',
            data: payload,
            dataType: 'json', // <-- asegura JSON
            cache: false
        });
    }


    // ---- local storage
    function getConsent() {
        try {
            const raw = localStorage.getItem(COOKIE_STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    }

    function saveLocal(consentObj) {
        localStorage.setItem(COOKIE_STORAGE_KEY, JSON.stringify(consentObj));
    }

    async function saveConsent(decisionType, optionalPrefs) {
        const nowTs = Date.now();
        const prefs = {
            essential: true,
            analiticas: !!optionalPrefs?.analiticas,
            publicidad: !!optionalPrefs?.publicidad
        };

        // 1) GET: ¿ya existe? 
        let exists = false,
            last = null;
        try {
            const check = await apiCheckConsent(USER_IP, COOKIE_VERSION);
            // el backend devuelve: { error:false, exists:bool, data:{...} }
            exists = !!check.exists;
            last = check.data || null;
        } catch (e) {
            console.warn("GET consent falló, continuo con POST:", e);
        }

        // 2) Evitar duplicado idéntico < 24h
        let skipInsert = false;
        if (exists && last) {
            const sameFlags =
                Number(last.cookie_essential) === (prefs.essential ? 1 : 0) &&
                Number(last.cookie_analiticas) === (prefs.analiticas ? 1 : 0) &&
                Number(last.cookie_publicidad) === (prefs.publicidad ? 1 : 0) &&
                String(last.decision) === String(decisionType);

            // last.ts_ms viene del server como TIMESTAMP (ej. "2025-11-05 10:30:00")
            const lastTs = last.ts_ms ? Date.parse(last.ts_ms.replace(' ', 'T')) : 0;
            const hours = (nowTs - lastTs) / (1000 * 60 * 60);

            if (sameFlags && hours < 24) {
                skipInsert = true;
            }
        }
        // 3) POST si corresponde
        if (!skipInsert) {
            const payload = {
                version: COOKIE_VERSION,
                decision: decisionType,
                timestamp: nowTs, // ms
                ip: USER_IP, // si viene vacío, backend usa REMOTE_ADDR
                essential: prefs.essential ? 1 : 0,
                analiticas: prefs.analiticas ? 1 : 0,
                publicidad: prefs.publicidad ? 1 : 0,
                user_agent: navigator.userAgent,
                source_page: location.pathname
            };

            try {
                await apiPostConsent(payload);
            } catch (e) {
                console.warn("POST consent error:", e);
            }
        }

        // 4) Persistir en localStorage
        saveLocal({
            version: COOKIE_VERSION,
            decision: decisionType,
            essential: true,
            optional: {
                analiticas: prefs.analiticas,
                publicidad: prefs.publicidad
            },
            timestamp: nowTs
        });
    }


    //====Resto de tu flujo sigue igual====// Ejemplos de uso:
    $(document).on("click", "#cookie-btn-aceptar", async function() {
        await saveConsent("accept_all", {
            analiticas: true,
            publicidad: true
        });
        $("#cookie-banner").fadeOut(200);
        $("#modal-config-cookies-overlay").hide();
    });

    $(document).on("click", "#cookie-btn-rechazar", async function() {
        await saveConsent("reject", {
            analiticas: false,
            publicidad: false
        });
        $("#cookie-banner").fadeOut(200);
        $("#modal-config-cookies-overlay").hide();
    });

    $(document).on("click", "#btn-guardar-config", async function() {
        const analiticasOn = $("#toggle-analiticas").is(":checked");
        const publicidadOn = $("#toggle-publicidad").is(":checked");
        await saveConsent("configurar", {
            analiticas: analiticasOn,
            publicidad: publicidadOn
        });
        $("#cookie-banner").fadeOut(200);
        $("#modal-config-cookies-overlay").hide();
    });

    function getBannerBreakpoint() {
        if (window.innerWidth <= 767) return "mobile";
        if (window.innerWidth <= 991) return "tablet";
        return "desktop";
    }

    let homeBannerData = [];
    let homeBannerBreakpoint = null;
    let homeBannerResizeTimer = null;

    function getBannerImageByViewport(data) {
        const breakpoint = getBannerBreakpoint();

        if (breakpoint === "mobile") {
            return data.imagen_movil || data.imagen_tablet || data.imagen || "";
        }

        if (breakpoint === "tablet") {
            return data.imagen_tablet || data.imagen || data.imagen_movil || "";
        }

        return data.imagen || data.imagen_tablet || data.imagen_movil || "";
    }

    function cargarBanner() {
        $.get("../api/v1/fulmuv/banner/all", function(returnedData) {
            if (!returnedData.error) {
                const $slider = $("#listInsertBanner");
                homeBannerData = returnedData.data || [];
                homeBannerBreakpoint = getBannerBreakpoint();

                // Si Slick ya está inicializado, destruirlo antes de volver a cargar
                if ($slider.hasClass('slick-initialized')) {
                    $slider.slick('unslick');
                }
                $slider.html('');

                homeBannerData.slice(0, 10).forEach(function(data) {
                    const imagen = getBannerImageByViewport(data);
                    const imageUrl = imagen ? `../admin/${imagen}` : "../img/FULMUV-NEGRO.png";
                    const url = (data.url || "").trim();
                    const tagStart = url ? `<a class="banner-slide-link" href="${url}" target="_blank" rel="noopener noreferrer">` : `<div class="banner-slide-link">`;
                    const tagEnd = url ? `</a>` : `</div>`;

                    $("#listInsertBanner").append(`
                        ${tagStart}
                            <div class="single-hero-slider single-animation-wrap slick-slide" style="background-image: url('${imageUrl}');" data-banner-id="${data.id_banner}">
                            </div>
                        ${tagEnd}
                    `);
                });

                $("#listInsertBanner").slick({
                    dots: true,
                    arrows: true,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    infinite: true,
                    speed: 500,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    adaptiveHeight: false, // 👈
                    prevArrow: '<button type="button" class="slick-prev slick-arrow-custom">&#10094;</button>',
                    nextArrow: '<button type="button" class="slick-next slick-arrow-custom">&#10095;</button>',
                });


            }
        }, 'json')
    }

    $(window).on("resize", function() {
        clearTimeout(homeBannerResizeTimer);
        homeBannerResizeTimer = setTimeout(function() {
            const nextBreakpoint = getBannerBreakpoint();

            if (nextBreakpoint !== homeBannerBreakpoint && homeBannerData.length) {
                homeBannerBreakpoint = nextBreakpoint;
                cargarBanner();
            }
        }, 180);
    });

    function cargarCategorias(tipoFiltro) {
        $.get("../api/v1/fulmuv/categoriasPrincipales/All", function(returnedData) {
            if (!returnedData.error) {
                const categorias = returnedData.data;
                const limite = 20;
                const contenedor = $("#carausel-8-columns");

                if (contenedor.hasClass('slick-initialized')) {
                    contenedor.slick('unslick');
                }


                categorias.forEach(function(categoria, index) {
                    contenedor.append(`
                        <div class="card-2 wow animate__animated animate__fadeInUp d-flex m-2 flex-column justify-content-between bg-white" data-wow-delay=".${index+1}s">
                            <div class="text-center mb-0">
                                <figure class="img-hover-scale overflow-hidden mb-2">
                                    <a href="#">
                                        <img src="../admin/${categoria.imagen}" 
                                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" 
                                            style="width: 150px; height: 150px; object-fit: contain" 
                                            alt="" />
                                    </a>
                                </figure>
                                <div class="mt-auto text-center">
                                    <h6 class="limitar-lineas text-dark mb-0"><a href="#">${capitalizarPrimeraLetra(categoria.nombre)}</a></h6>
                                </div>
                            </div>
                        </div>
                    `);

                });
                contenedor.slick({
                    dots: false,
                    infinite: true,
                    speed: 1000,
                    arrows: true,
                    autoplay: true,
                    slidesToShow: 6,
                    slidesToScroll: 1,
                    loop: true,
                    adaptiveHeight: true,
                    responsive: [{
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 4,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1
                            }
                        }
                    ],
                    prevArrow: '<span class="slider-btn slider-prev"><i class="fi-rs-arrow-small-left"></i></span>',
                    nextArrow: '<span class="slider-btn slider-next"><i class="fi-rs-arrow-small-right"></i></span>',
                    appendArrows: "#carausel-8-columns-arrows"
                });
            } else {
                $("#carausel-8-columns").html('<p class="text-danger">No se pudieron cargar las categorías.</p>');
            }
        }, 'json');
    }


    function cargarExtraCategorias() {
        $.get("../api/v1/fulmuv/categorias/All", function(response) {
            if (!response.error) {
                categoriasExtraData = response.data;
                $("#btn-ver-mas-categorias").hide();
                $("#masCategoriasGrid").show();
                renderCategorias(categoriasExtraData, 1);
            } else {
                alert("No se pudieron cargar más categorías.");
            }
        }, 'json');
    }

    function renderCategorias(data, page = 1) {
        const totalPages = Math.ceil(data.length / itemsPerPage);
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const categoriasPagina = data.slice(start, end);

        let html = '';
        categoriasPagina.forEach(categoria => {
            html += `
            <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <img src="../admin/${categoria.imagen}" 
                             onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" 
                             style="width: 120px; height: 120px; object-fit: contain;" 
                             class="mb-3" />
                        <h6 class="text-dark limitar-lineas mb-0">
                            ${capitalizarPrimeraLetra(categoria.nombre)}
                        </h6>
                    </div>
                </div>
            </div>`;
        });

        $("#carausel-8-columns").hide()
        $("#carausel-8-columns-arrows").hide()
        $(".pagination-area").addClass("mt-20")
        $("#viewButtonCategory").addClass("d-none")
        $("#masCategoriasGrid").html(html);
        renderPaginacionCategorias(data.length, page);
    }

    function renderPaginacionCategorias(totalItems, currentPage) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        let pagHtml = '';

        for (let i = 1; i <= totalPages; i++) {
            pagHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
        }

        $(".pagination").html(`
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}"><i class="fi-rs-arrow-small-left"></i></a>
        </li>
        ${pagHtml}
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}"><i class="fi-rs-arrow-small-right"></i></a>
        </li>
    `);
    }

    $(document).on("click", ".pagination .page-link", function(e) {
        e.preventDefault();
        const page = parseInt($(this).data("page"));
        if (!isNaN(page)) {
            currentPage = page;
            renderCategorias(categoriasExtraData, currentPage);
        }
    });

    function cargarProductosVendidos() {
        renderSectionLoading("#carausel-4-columns", "productos");
        $.get("../api/v1/fulmuv/getProductosVendidosHoy/", function(returnedData) {
            if (!returnedData.error) {
                let $slider = $('#carausel-4-columns');

                // 1. Resetear el slider si ya está inicializado
                if ($slider.hasClass('slick-initialized')) {
                    $slider.slick('unslick');
                }

                // 2. Limpiar contenido
                $slider.empty();

                var listaServicio = "";
                returnedData.data.forEach(function(data) {

                    var verificacion = "";

                    // 1) Verificar que exista y sea un array
                    if (Array.isArray(data.verificacion) && data.verificacion.length > 0) {
                        // 2) Verificar que el primer elemento tenga la propiedad verificado en 1
                        if (data.verificacion[0].verificado == 1) {
                            verificacion = `
                                <div class="home-verify-overlay">
                                    <img src="../img/verificado_empresa.png" alt="Empresa verificada">
                                </div>`;
                        }
                    }
                    if (data.categorias[0].tipo == "producto") {
                        const tieneDescuento = parseFloat(data.descuento) > 0;
                        const precioDescuento = data.precio_referencia - (data.precio_referencia * data.descuento / 100);

                        $slider.append(`
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap">
                                ${verificacion}
                                <div class="product-img product-img-zoom">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;">
                                        <img class="default-img" src="../admin/${data.img_frontal}" alt="" 
                                            onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" 
                                            style="height: 150px; object-fit: contain;" />
                                    </a>
                                </div>

                                ${tieneDescuento ? `
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="best">-${parseInt(data.descuento)}%</span>
                                    </div>` : ''}

                                <!-- Botón flotante arriba derecha -->
                                <div class="position-absolute top-0 end-0 m-2 d-none">
                                    <button class="btn rounded-circle d-flex justify-content-center align-items-center p-0"
                                        style="width: 40px; height: 40px;"
                                        onclick="agregarProductoCarrito(${data.id_producto}, '${data.titulo_producto}', '${data.precio_referencia}', '${data.img_frontal}')">
                                        <img alt="Carrito de compra" src="img/carrito_transparente.png"/>
                                    </button>
                                </div>
                            </div>

                            <div class="product-content-wrap p-1">
                                <h2 class="text-center">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;" class="limitar-lineas mt-1">
                                        ${capitalizarPrimeraLetra(data.titulo_producto)}
                                    </a>
                                </h2>
                                <div class="product-price mb-2 mt-0 text-center">
                                    <span>
                                        ${formatoMoneda.format(tieneDescuento ? precioDescuento : data.precio_referencia)}
                                    </span>
                                    ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `);
                    } else {
                        listaServicio += `
                            <div class="col-lg-2 col-md-3 col-12 col-sm-6 mt-2">
                                <div class="vendor-wrap h-100 d-flex flex-column justify-content-between">
                                    <div class="text-center">
                                        <div class="vendor-img-action-wrap">
                                            <div class="vendor-img d-flex justify-content-center align-items-center">
                                                <a href="${getIndexAppPath('vendorProductsPath', 'productos_vendor.php')}?q=4">
                                                    <img class="default-img" src="admin/${data.img_frontal}" alt="" 
                                                        onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" 
                                                        style="height: 150px; object-fit: contain;" />
                                                </a>
                                            </div>
                                        </div>
                                        <h4 class="my-2 text-dark text-center" style="font-size: 16px;">
                                            <a href="${getIndexAppPath('productDetailPath', 'detalle_productos.php')}?q=${data.id_producto}" class="text-center">${capitalizarPrimeraLetra(data.titulo_producto)}</a>
                                        </h4>
                                    </div>
                                    <div class="vendor-content-wrap px-3 pb-3">
                                        <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;"
                                        class="btn btn-sm btn-outline-primary w-100 d-flex justify-content-center align-items-center">
                                            Detalle del servicio <i class="fi-rs-arrow-small-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });

                $("#listaServiciosPopulares").append(listaServicio)

                // 4. Volver a inicializar slick
                $slider.slick(buildHomeCarouselConfig("#carausel-4-columns-arrows-tendencias"));
            }
        }, 'json');
    }

    function cargarServiciosVendidos() {
        renderSectionLoading("#carausel-4-columns-servicio", "servicios");
        $.get("../api/v1/fulmuv/serviciosProductos/All", function(returnedData) {
            if (!returnedData.error) {
                let $slider = $('#carausel-4-columns-servicio');

                // 1. Resetear el slider si ya está inicializado
                if ($slider.hasClass('slick-initialized')) {
                    $slider.slick('unslick');
                }

                // 2. Limpiar contenido
                $slider.empty();

                var listaServicio = "";
                returnedData.data.forEach(function(data) {

                    var verificacion = "";

                    // 1) Verificar que exista y sea un array
                    if (Array.isArray(data.verificacion) && data.verificacion.length > 0) {
                        // 2) Verificar que el primer elemento tenga la propiedad verificado en 1
                        if (data.verificacion[0].verificado == 1) {
                            verificacion = `
                                <div class="home-verify-overlay">
                                    <img src="../img/verificado_empresa.png" alt="Empresa verificada">
                                </div>`;
                        }
                    }

                    console.log("ESTE ES EL ID_PRODUCTO " + data.id_producto)
                    if (data.categorias[0].tipo == "servicio") {
                        const tieneDescuento = parseFloat(data.descuento) > 0;
                        const precioDescuento = data.precio_referencia - (data.precio_referencia * data.descuento / 100);

                        $slider.append(`
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap">
                                ${verificacion}
                                <div class="product-img product-img-zoom">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;">
                                        <img class="default-img" src="../admin/${data.img_frontal}" alt="" 
                                            onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" 
                                            style="height: 150px; object-fit: contain;" />
                                    </a>
                                </div>

                                ${tieneDescuento ? `
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="best">-${parseInt(data.descuento)}%</span>
                                    </div>` : ''}

                                <!-- Botón flotante arriba derecha -->
                                <div class="position-absolute top-0 end-0 m-2 d-none">
                                    <button class="btn rounded-circle d-flex justify-content-center align-items-center p-0"
                                        style="width: 40px; height: 40px;"
                                        onclick="agregarProductoCarrito(${data.id_producto}, '${data.titulo_producto}', '${data.precio_referencia}', '${data.img_frontal}')">
                                        <img alt="Carrito de compra" src="img/carrito_transparente.png"/>
                                    </button>
                                </div>
                            </div>

                            <div class="product-content-wrap p-1">
                                <h2 class="text-center">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;" class="limitar-lineas mt-1">
                                        ${capitalizarPrimeraLetra(data.titulo_producto)}
                                    </a>
                                </h2>
                                <div class="product-price mb-2 mt-0 text-center">
                                    <span>
                                        ${formatoMoneda.format(tieneDescuento ? precioDescuento : data.precio_referencia)}
                                    </span>
                                    ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `);
                    }
                });

                $("#listaServiciosPopulares").append(listaServicio)

                // 4. Volver a inicializar slick
                $slider.slick(buildHomeCarouselConfig("#carausel-4-columns-arrows-servicios"));
            }
        }, 'json');
    }


    function renderPublicidadSlot(selector, items) {
        const $slot = $(selector);

        if (!$slot.length) {
            return;
        }

        if ($slot.hasClass('slick-initialized')) {
            $slot.slick('unslick');
        }

        $slot.empty();

        if (!items.length) {
            return;
        }

        items.forEach(function(item) {
            const imageUrl = item.imagen ? `../admin/${item.imagen}` : "../img/FULMUV-NEGRO.png";
            const linkUrl = (item.url || "").trim();
            const tagStart = linkUrl ? `<a class="publicidad-item-link" href="${linkUrl}" target="_blank" rel="noopener noreferrer">` : `<div class="publicidad-item-link">`;
            const tagEnd = linkUrl ? `</a>` : `</div>`;

            $slot.append(`
                <div class="publicidad-item">
                    ${tagStart}
                        <img alt="Publicidad" src="${imageUrl}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">
                    ${tagEnd}
                </div>
            `);
        });

        if (items.length > 1) {
            $slot.slick({
                dots: true,
                arrows: true,
                autoplay: true,
                autoplaySpeed: 3500,
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                adaptiveHeight: false,
                prevArrow: '<button type="button" class="publicidad-arrow publicidad-prev" aria-label="Publicidad anterior"><i class="fi-rs-angle-left"></i></button>',
                nextArrow: '<button type="button" class="publicidad-arrow publicidad-next" aria-label="Publicidad siguiente"><i class="fi-rs-angle-right"></i></button>'
            });
        }
    }

    function cargarPublicidad() {
        $.get("../api/v1/fulmuv/publicidad/all", function(returnedData) {
            if (!returnedData.error) {
                const publicidad = returnedData.data || [];
                const publicidadPosicion1 = publicidad.filter((data) => String(data.posicion) === "1");
                const publicidadPosicion2 = publicidad.filter((data) => String(data.posicion) === "2");

                renderPublicidadSlot("#imgpublicidad", publicidadPosicion1);
                renderPublicidadSlot("#imgpublicidad2", publicidadPosicion2);
            }

        }, 'json')
    }

    function cargarProductosOferta() {
        renderSectionLoading("#carausel-4-columns-oferta", "ofertas");
        $.get("../api/v1/fulmuv/ofertas_imperdibles/", function(returnedData) {
            if (!returnedData.error) {
                let $slider = $('#carausel-4-columns-oferta');

                // 1. Resetear el slider si ya está inicializado
                if ($slider.hasClass('slick-initialized')) {
                    $slider.slick('unslick');
                }

                // 2. Limpiar contenido
                $slider.empty();

                var listaProducto = "";
                let contador = 0;

                // 3. Insertar productos correctamente envueltos
                returnedData.data.forEach(function(data, index) {
                    var verificacion = "";

                    // 1) Verificar que exista y sea un array
                    if (Array.isArray(data.verificacion) && data.verificacion.length > 0) {
                        // 2) Verificar que el primer elemento tenga la propiedad verificado en 1
                        if (data.verificacion[0].verificado == 1) {
                            verificacion = `
                                <div class="home-verify-overlay">
                                    <img src="../img/verificado_empresa.png" alt="Empresa verificada">
                                </div>`;
                        }
                    }

                    if (data.categorias[0].tipo == "producto") {
                        const tieneDescuento = parseFloat(data.descuento) > 0;
                        const precioDescuento = data.precio_referencia - (data.precio_referencia * data.descuento / 100);
                        const categoriaPrincipal = Array.isArray(data.categorias) && data.categorias.length > 0 ? capitalizarPrimeraLetra(data.categorias[0].nombre || "Producto") : "Producto";
                        const subcategoriaPrincipal = Array.isArray(data.subcategorias) && data.subcategorias.length > 0 ? capitalizarPrimeraLetra(data.subcategorias[0].nombre || "") : "";
                        const marcaPrincipal = Array.isArray(data.marca) && data.marca.length > 0 ? capitalizarPrimeraLetra(data.marca[0].nombre || "") : "";
                        const empresaNombre = capitalizarPrimeraLetra(data.nombre_empresa || "");
                        const provincia = capitalizarPrimeraLetra(data.provincia || "");
                        const canton = capitalizarPrimeraLetra(data.canton || "");
                        const vendidosHoy = Number(data.cantidad_vendida ?? 0);

                        $slider.append(`
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap">
                                ${verificacion}
                                <div class="product-img product-img-zoom">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;">
                                        <img class="default-img" src="../admin/${data.img_frontal}" alt="" 
                                            onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" 
                                            style="height: 150px; object-fit: contain;" />
                                    </a>
                                </div>

                                ${tieneDescuento ? `
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="best">-${parseInt(data.descuento)}%</span>
                                    </div>` : ''}

                                <!-- Botón flotante arriba derecha -->
                                <div class="position-absolute top-0 end-0 m-2 d-none">
                                    <button class="btn rounded-circle d-flex justify-content-center align-items-center p-0"
                                        style="width: 40px; height: 40px;"
                                        onclick="agregarProductoCarrito(${data.id_producto}, '${data.titulo_producto}', '${data.precio_referencia}', '${data.img_frontal}')">
                                        <img alt="Carrito de compra" src="img/carrito_transparente.png"/>
                                    </button>
                                </div>
                            </div>

                            <div class="product-content-wrap p-1">
                                <div class="text-center text-muted small mb-1">
                                    ${categoriaPrincipal}${subcategoriaPrincipal ? ` · ${subcategoriaPrincipal}` : ""}
                                </div>
                                <h2 class="text-center">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;" class="limitar-lineas mt-1">
                                        ${capitalizarPrimeraLetra(data.titulo_producto)}
                                    </a>
                                </h2>
                                ${marcaPrincipal ? `<div class="text-center small text-muted mb-1">${marcaPrincipal}</div>` : ""}
                                ${empresaNombre ? `<div class="text-center small text-muted mb-1">Empresa: ${empresaNombre}</div>` : ""}
                                ${(provincia || canton) ? `<div class="text-center small text-muted mb-1"><i class="fi-rs-marker"></i> ${provincia || '—'}${canton ? ` · ${canton}` : ''}</div>` : ""}
                                ${vendidosHoy > 0 ? `<div class="text-center small text-muted mb-1">Vendidos hoy: ${vendidosHoy}</div>` : ""}
                                <div class="product-price mb-2 mt-0 text-center">
                                    <span>
                                        ${formatoMoneda.format(tieneDescuento ? precioDescuento : data.precio_referencia)}
                                    </span>
                                    ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `);
                    }
                });

                // 4. Volver a inicializar slick
                $slider.slick(buildHomeCarouselConfig("#carausel-4-columns-arrows-oferta"));

            }
        }, 'json');

    }

    function cargarProductosGenialParati() {
        renderSectionLoading("#carausel-4-columns-para-ti", "productos");
        $.get("../api/v1/fulmuv/productosAll/all", function(returnedData) {
            if (!returnedData.error) {
                let $slider = $('#carausel-4-columns-para-ti');

                // 1. Resetear el slider si ya está inicializado
                if ($slider.hasClass('slick-initialized')) {
                    $slider.slick('unslick');
                }

                // 2. Limpiar contenido
                $slider.empty();

                returnedData.data.forEach(function(data) {
                    var verificacion = "";

                    // 1) Verificar que exista y sea un array
                    if (Array.isArray(data.verificacion) && data.verificacion.length > 0) {
                        // 2) Verificar que el primer elemento tenga la propiedad verificado en 1
                        if (data.verificacion[0].verificado == 1) {
                            verificacion = `
                                <div class="home-verify-overlay">
                                    <img src="../img/verificado_empresa.png" alt="Empresa verificada">
                                </div>`;
                        }
                    }
                    if (data.categorias[0].tipo == "producto") {
                        const tieneDescuento = parseFloat(data.descuento) > 0;
                        const precioDescuento = data.precio_referencia - (data.precio_referencia * data.descuento / 100);

                        $slider.append(`
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap">
                                ${verificacion}
                                <div class="product-img product-img-zoom">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;">
                                        <img class="default-img" src="../admin/${data.img_frontal}" alt="" 
                                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" 
                                            style="height: 150px; object-fit: contain;" />
                                    </a>
                                </div>

                                ${tieneDescuento ? `
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="best">-${parseInt(data.descuento)}%</span>
                                    </div>` : ''}

                                <!-- Botón flotante arriba derecha -->
                                <div class="position-absolute top-0 end-0 m-2 d-none">
                                    <button class="btn rounded-circle d-flex justify-content-center align-items-center p-0"
                                        style="width: 40px; height: 40px;"
                                        onclick="agregarProductoCarrito(${data.id_producto}, '${data.titulo_producto}', '${data.precio_referencia}', '${data.img_frontal}')">
                                        <img alt="Carrito de compra" src="../img/carrito_transparente.png"/>
                                    </button>
                                </div>
                            </div>

                            <div class="product-content-wrap p-1">
                                <h2 class="text-center">
                                    <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;" class="limitar-lineas mt-1">
                                        ${capitalizarPrimeraLetra(data.titulo_producto)}
                                    </a>
                                </h2>
                                <div class="product-price mb-2 mt-0 text-center">
                                    <span>
                                        ${formatoMoneda.format(tieneDescuento ? precioDescuento : data.precio_referencia)}
                                    </span>
                                    ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `);
                    }
                });

                // $("#listaServiciosPopulares").append(listaServicio)

                // 4. Volver a inicializar slick
                $slider.slick(buildHomeCarouselConfig("#carausel-4-columns-arrows-recomendados"));
            }
        }, 'json');
    }


    function cargarVehiculosLlegados() {
        renderSectionLoading("#carausel-4-columns-vehiculos", "vehículos");
        $.get("../api/v1/fulmuv/vehiculosLlegados/All", function(returnedData) {
            if (!returnedData.error) {
                let $slider = $('#carausel-4-columns-vehiculos');

                // 1. Resetear el slider si ya está inicializado
                if ($slider.hasClass('slick-initialized')) {
                    $slider.slick('unslick');
                }

                // 2. Limpiar contenido
                $slider.empty();

                (returnedData.data || []).slice(0, 10).forEach(function(data) {
                    // Campos según tu payload (2da y 3ra imagen)
                    const marca = (data?.marcaArray[0]?.nombre) || "";
                    const modelo = (data?.modeloArray?.nombre) || data?.titulo_producto || "";
                    const anio = data?.anio ?? "";
                    const prov = firstFromJsonLike(data?.provincia);
                    const canton = firstFromJsonLike(data?.canton);
                    const kms = formatKms(data?.kilometraje);

                    const precioRef = parseFloat(data?.precio_referencia || 0);
                    const desc = parseFloat(data?.descuento || 0);
                    const tieneDesc = !isNaN(precioRef) && !isNaN(desc) && desc > 0;
                    const precioConDesc = tieneDesc ? (precioRef - (precioRef * desc / 100)) : precioRef;

                    // Imagen
                    const img = data?.img_frontal ? `../admin/${data.img_frontal}` : '../img/FULMUV-NEGRO.png';
                    const verificacion = (Array.isArray(data.verificacion) && data.verificacion[0]?.verificado == 1) ?
                        `<img class="home-vehicle-verify" src="img/verificado_empresa.png" alt="Empresa verificada" width="40" height="40">` :
                        "";

                    $slider.append(`
                        <div class="px-1">
                            <div class="home-vehicle-card">
                                <a class="home-vehicle-media" href="detalle_vehiculo.php?q=${data.id_vehiculo}">
                                    ${verificacion}
                                    ${tieneDesc ? `<span class="home-card-badge">-${parseInt(data.descuento)}%</span>` : ""}
                                    <img src="${img}" alt="${modelo}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">
                                </a>
                                <div class="home-vehicle-content d-flex flex-column">
                                    <div class="home-vehicle-meta">
                                        <span>${marca || 'Sin marca'}</span>
                                        <span>${anio || 'Sin año'}</span>
                                    </div>
                                    <a class="home-card-link" href="detalle_vehiculo.php?q=${data.id_vehiculo}">
                                        <div class="home-vehicle-title">${modelo || 'Vehículo sin modelo'}</div>
                                    </a>
                                    <div class="home-vehicle-meta">
                                        <span><i class="fi-rs-marker"></i> ${prov || '—'}${canton ? ` · ${canton}` : ''}</span>
                                        <span><i class="fi-rs-dashboard"></i> ${kms}</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="home-vehicle-price">
                                            <span>${formatoMoneda.format(tieneDesc ? precioConDesc : data.precio_referencia)}</span>
                                            ${tieneDesc ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });
                // $("#listaServiciosPopulares").append(listaServicio)

                // 4. Volver a inicializar slick
                $slider.slick(buildHomeCarouselConfig("#carausel-4-columns-arrows-vehiculos"));
            }
        }, 'json');
    }


    // Helpers para leer arrays serializados como '["Azuay"]'
    function firstFromJsonLike(v) {
        try {
            if (Array.isArray(v)) return v[0] ?? "";
            if (typeof v === "string") {
                const arr = JSON.parse(v);
                return Array.isArray(arr) ? (arr[0] ?? "") : "";
            }
        } catch (e) {}
        return "";
    }

    function formatKms(km) {
        const n = parseInt(km, 10);
        if (isNaN(n)) return "";
        return n.toLocaleString("es-EC") + " Kms";
    }

    function agregarProductoCarrito(id_producto, nombreP, precio_referencia, img_frontal) {
        const productoId = id_producto;
        const cantidad = 1;
        const nombre = nombreP;
        const precio = precio_referencia;
        const img = ("../admin/" + img_frontal) || "../img/FULMUV-NEGRO.png";

        let carrito = [];
        try {
            const stored = JSON.parse(localStorage.getItem("carrito"));
            const now = new Date().getTime();

            if (stored && Array.isArray(stored.data)) {
                if (now - stored.timestamp < 2 * 60 * 60 * 1000) {
                    carrito = stored.data;
                } else {
                    console.warn("El carrito expiró. Reiniciando...");
                }
            }
        } catch (e) {
            console.warn("carrito malformado, reiniciando...");
        }

        const existente = carrito.find(p => p.id === productoId);
        if (existente) {

            toastr.warning(`"${nombre}" ya está en el carrito`, 'Fulmuv', {
                closeButton: true,
                progressBar: true,
                timeOut: 3000
            });

            return; // salimos de la función
        }

        // Agregar nuevo producto
        carrito.push({
            id: productoId,
            nombre: nombre,
            precio: precio,
            cantidad: cantidad,
            imagen: img
        });

        localStorage.setItem("carrito", JSON.stringify({
            data: carrito,
            timestamp: new Date().getTime()
        }));

        actualizarIconoCarrito();

        toastr.success(`"${nombre}" agregado al carrito`, 'Fulmuv', {
            closeButton: true,
            progressBar: true,
            timeOut: 3000
        });

    }

    // --- Helpers de fecha y formato ---
    function parseMysqlDatetime(dtStr) {
        // Espera: "YYYY-MM-DD HH:mm:ss"
        // Convertimos a "YYYY-MM-DDTHH:mm:ss" para que Date lo tome como local.
        if (!dtStr || typeof dtStr !== "string") return null;
        return new Date(dtStr.replace(" ", "T"));
    }

    function pad2(n) {
        return String(n).padStart(2, "0");
    }

    function formatCountdown(ms) {
        if (ms <= 0) return "0m";
        const totalMinutes = Math.floor(ms / 60000);
        const days = Math.floor(totalMinutes / (60 * 24));
        const hours = Math.floor((totalMinutes % (60 * 24)) / 60);
        const minutes = totalMinutes % 60;

        let parts = [];
        if (days > 0) parts.push(`${days}d`);
        if (hours > 0 || days > 0) parts.push(`${hours}h`);
        parts.push(`${minutes}m`);
        return parts.join(" ");
    }

    function safeText(v, fallback = "—") {
        const s = (v ?? "").toString().trim();
        return s ? s : fallback;
    }

    function capitalizarPrimeraLetra(str) {
        return window.fulmuvTitleCase ? window.fulmuvTitleCase(str) : ((str ?? "").toString().trim());
    }

    function renderSectionLoading(selector, label, count = 6) {
        const $target = $(selector);
        if (!$target.length) return;

        let html = '<div class="home-loading-grid">';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="home-loading-card">
                    <div class="home-loading-media"></div>
                    <div class="home-loading-content">
                        <div class="home-loading-caption">Cargando ${label}...</div>
                        <div class="home-loading-line w-90"></div>
                        <div class="home-loading-line w-75"></div>
                        <div class="home-loading-line w-60"></div>
                        <div class="home-loading-pill"></div>
                    </div>
                </div>
            `;
        }
        html += '</div>';
        $target.html(html);
    }

    function buildHomeCarouselConfig(appendArrowsSelector) {
        return {
            dots: false,
            infinite: true,
            speed: 1000,
            arrows: true,
            autoplay: true,
            slidesToShow: 6,
            slidesToScroll: 1,
            loop: true,
            adaptiveHeight: false,
            responsive: [{
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                }
            ],
            prevArrow: '<span class="slider-btn slider-prev"><i class="fi-rs-arrow-small-left"></i></span>',
            nextArrow: '<span class="slider-btn slider-next"><i class="fi-rs-arrow-small-right"></i></span>',
            appendArrows: appendArrowsSelector
        };
    }

    // --- Timers para contadores (para evitar duplicar intervalos) ---
    let eventosCountdownInterval = null;

    function cargarEventos() {
        renderSectionLoading("#carausel-4-columns-eventos", "eventos");
        $.get("../api/v1/fulmuv/eventos/all", function(returnedData) {
            if (returnedData.error) return;

            const $section = $("#section-eventos");
            const $slider = $("#carausel-4-columns-eventos");

            // Reset slick si está inicializado
            if ($slider.hasClass("slick-initialized")) {
                $slider.slick("unslick");
            }

            // Detener intervalos previos
            if (eventosCountdownInterval) {
                clearInterval(eventosCountdownInterval);
                eventosCountdownInterval = null;
            }

            $slider.empty();

            const now = new Date();

            // 1) Filtrar: SOLO mostrar si fecha_hora_fin > now
            const visibles = (returnedData.data || []).filter(ev => {
                const fin = parseMysqlDatetime(ev.fecha_hora_fin);
                if (!fin || isNaN(fin.getTime())) return false;
                return fin.getTime() > now.getTime();
            });

            // 2) Si no hay visibles, ocultar section y salir
            if (!visibles.length) {
                $section.addClass("d-none");
                return;
            } else {
                $section.removeClass("d-none");
            }

            // 3) Pintar cards
            visibles.forEach(function(data) {
                const inicio = parseMysqlDatetime(data.fecha_hora_inicio);
                const fin = parseMysqlDatetime(data.fecha_hora_fin);

                // Estado inicial del contador (se actualizará por interval)
                let countdownLabel = "Próximamente";
                let countdownMs = null;

                if (inicio && fin) {
                    if (now.getTime() < inicio.getTime()) {
                        countdownMs = inicio.getTime() - now.getTime();
                        countdownLabel = `Empieza en ${formatCountdown(countdownMs)}`;
                    } else if (now.getTime() >= inicio.getTime() && now.getTime() < fin.getTime()) {
                        countdownMs = fin.getTime() - now.getTime();
                        countdownLabel = `Termina en ${formatCountdown(countdownMs)}`;
                    } else {
                        // por seguridad (aunque ya filtramos por fin > now)
                        countdownLabel = "Finalizado";
                    }
                }

                const provincia = safeText(data.provincia);
                const canton = safeText(data.canton);
                $slider.append(`
        <div class="px-1">
          <div class="home-event-card">
            <div class="position-relative">
              <a href="detalle_eventos.php?q=${data.id_evento}" class="home-event-media" onclick="return navegarDesdeIndex('evento_detalle', 'detalle_eventos.php?q=${data.id_evento}', { eventId: ${data.id_evento} });">
                <img
                  src="../admin/${data.imagen}"
                  alt="${capitalizarPrimeraLetra(data.titulo)}"
                  onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';"
                />
              </a>
              <span
                class="home-card-badge"
                data-countdown="1"
                data-id="${data.id_evento}"
                data-inicio="${data.fecha_hora_inicio || ""}"
                data-fin="${data.fecha_hora_fin || ""}"
              >
                <i class="bi bi-clock me-1"></i>${countdownLabel}
              </span>
            </div>
            <div class="home-event-content d-flex flex-column">
              <a href="detalle_eventos.php?q=${data.id_evento}" class="home-card-link" onclick="return navegarDesdeIndex('evento_detalle', 'detalle_eventos.php?q=${data.id_evento}', { eventId: ${data.id_evento} });">
                <div class="home-event-title">${capitalizarPrimeraLetra(data.titulo)}</div>
              </a>
              <div class="home-event-meta">
                <span><i class="fi-rs-marker"></i> ${provincia}</span>
                <span>${canton}</span>
              </div>
            </div>
          </div>
        </div>
      `);
            });

            // 4) Inicializar slick SOLO si hay visibles
            $slider.slick(buildHomeCarouselConfig("#carausel-4-columns-arrows-eventos"));

            // 5) Actualizar contadores cada 30s (puedes bajar a 10s si quieres)
            eventosCountdownInterval = setInterval(() => {
                const now2 = new Date();

                // Si ya no hay ningún evento válido, ocultar section
                let anyVisible = false;

                $section.find('[data-countdown="1"]').each(function() {
                    const $badge = $(this);
                    const inicioStr = $badge.attr("data-inicio");
                    const finStr = $badge.attr("data-fin");

                    const inicio = parseMysqlDatetime(inicioStr);
                    const fin = parseMysqlDatetime(finStr);

                    if (!fin || isNaN(fin.getTime())) {
                        $badge.remove();
                        return;
                    }

                    // Si ya terminó (fin <= now) -> eliminar tarjeta completa
                    if (fin.getTime() <= now2.getTime()) {
                        // borrar el slide/card completo
                        const $slide = $badge.closest(".px-1");
                        $slide.remove();
                        return;
                    }

                    anyVisible = true;

                    let label = "Próximamente";
                    if (inicio && !isNaN(inicio.getTime())) {
                        if (now2.getTime() < inicio.getTime()) {
                            label = `Empieza en ${formatCountdown(inicio.getTime() - now2.getTime())}`;
                        } else {
                            label = `Termina en ${formatCountdown(fin.getTime() - now2.getTime())}`;
                        }
                    } else {
                        label = `Termina en ${formatCountdown(fin.getTime() - now2.getTime())}`;
                    }

                    $badge.html(`<i class="bi bi-clock me-1"></i>${label}`);
                });

                // Si ya no hay cards visibles, ocultar section + reset slick
                if (!anyVisible) {
                    if ($slider.hasClass("slick-initialized")) $slider.slick("unslick");
                    $slider.empty();
                    $section.addClass("d-none");
                    clearInterval(eventosCountdownInterval);
                    eventosCountdownInterval = null;
                }
            }, 30000);

        }, "json");
    }

    function cargarEmpleos() {
        renderSectionLoading("#carausel-4-columns-empleos", "empleos");
        $.get("../api/v1/fulmuv/empleosAll/all", function(returnedData) {
            if (returnedData.error) return;

            const $section = $("#section-empleos");
            const $slider = $("#carausel-4-columns-empleos");

            // Reset slick si ya está inicializado
            if ($slider.hasClass("slick-initialized")) {
                $slider.slick("unslick");
            }

            $slider.empty();

            const now = new Date();

            // 1) Filtrar por fecha_fin: solo mostrar si fecha_fin > ahora
            const visibles = (returnedData.data || []).filter(emp => {
                const fin = parseMysqlDatetime(emp.fecha_fin);
                if (!fin || isNaN(fin.getTime())) return false;
                return fin.getTime() > now.getTime();
            });

            // 2) Si no hay visibles, ocultar section y salir
            if (!visibles.length) {
                $section.addClass("d-none");
                return;
            } else {
                $section.removeClass("d-none");
            }

            // 3) Render cards con imagen mismo tamaño + badge "Disponible"
            visibles.forEach(function(data) {
                const provincia = safeText(data.provincia);
                const canton = safeText(data.canton);

                $slider.append(`
        <div class="px-1">
          <div class="home-job-card">
            <a href="empleos.php" class="home-job-media text-decoration-none" onclick="return navegarDesdeIndex('empleos', 'empleos.php');">
              <span class="home-card-badge"><i class="fi-rs-check me-1"></i>Disponible</span>
              <img class="img-fluid"
                   src="../admin/${data.img_frontal}"
                   alt="${capitalizarPrimeraLetra(data.titulo)}"
                   onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" />
            </a>
            <div class="home-job-content d-flex flex-column">
              <a href="empleos.php" class="home-card-link" onclick="return navegarDesdeIndex('empleos', 'empleos.php');">
                <div class="home-job-title">${capitalizarPrimeraLetra(data.titulo)}</div>
              </a>
              <div class="home-job-meta">
                <span><i class="fi-rs-marker me-1"></i>${capitalizarPrimeraLetra(provincia)} · ${capitalizarPrimeraLetra(canton)}</span>
              </div>
            </div>
          </div>
        </div>
      `);
            });

            // 4) Inicializar slick
            $slider.slick(buildHomeCarouselConfig("#carausel-4-columns-arrows-empleos"));

        }, "json");
    }
</script>
