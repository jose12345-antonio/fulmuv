<?php
session_start();

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

session_cache_limiter('private_no_expire');
date_default_timezone_set('America/Guayaquil');

$frontUserLoggedIn = isset($_SESSION["id_usuario"]) && (
    (isset($_SESSION["front_auth"]) && $_SESSION["front_auth"] === true) ||
    empty($_SESSION["id_empresa"])
);

// Redirección si no hay sesión activa
// if (!isset($_SESSION["id_usuario"])) {
//     header("Location: login.php");
//     exit();
// }


?>


<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <title>FULMUV | Repuestos, accesorios, servicios, vehículos y eventos vehiculares en Ecuador</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="" />
    <meta property="og:type" content="" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="img/FULMUV-LOGO-60X60.png" />
    <link rel="stylesheet" href="themelading/nest-frontend/assets/css/plugins/slider-range.css">
    <!-- Template CSS -->
    <link rel="stylesheet" href="themelading/nest-frontend/assets/css/main.css?v=6.2.0.0.0.0.0.0.0.10" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,600&family=Figtree:wght@300;400;500;600;700;800;900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Roboto+Slab&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        body,
        p,
        span,
        small,
        label,
        input,
        select,
        textarea,
        button,
        li,
        a {
            font-family: "Figtree", sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .product-content-wrap h2,
        .product-content-wrap h2 a,
        .widget-title,
        .section-title h1,
        .section-title h2,
        .section-title h3,
        .brand-title,
        .brand-subtitle,
        .title-detail,
        .vendor-title-modern,
        .vehicle-title-modern,
        .service-title-modern,
        .product-title-modern,
        .product-title-exclusive,
        .home-event-title,
        .home-job-title {
            font-family: "Inter", sans-serif !important;
        }

        .cart-dropdown-wrap {
            width: 450px !important;
        }

        .cart-dropdown-wrap.cart-dropdown-hm2 {
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);
            padding: 12px;
            backdrop-filter: blur(12px);
        }

        .listaProductoCarrito {
            max-height: 196px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
        }

        .listaProductoCarrito::-webkit-scrollbar {
            width: 5px;
        }

        /* ── FULMUV brand palette ──────────────────────────
           Turquesa  #004E60   Verde   #90FFBD
           Naranja   #FF6D01   Celeste #B7FFFF
           Amarillo  #FFDC2B   Blanco  #FFFFFF
        ─────────────────────────────────────────────── */

        .listaProductoCarrito::-webkit-scrollbar-thumb {
            background: rgba(0, 78, 96, 0.25);
            border-radius: 999px;
        }

        .listaProductoCarrito::-webkit-scrollbar-track {
            background: transparent;
        }

        .shopping-cart-footer {
            margin-top: 2px;
            margin-bottom: 2px;
            padding: 14px 8px 6px;
            border-top: 2px solid rgba(0, 78, 96, 0.12);
        }

        .shopping-cart-title {
            font-size: 14px;
        }

        /* -- Título "Total a pagar a proveedores" -- */
        .cart-proveedores-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 800;
            color: #004E60;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 4px 4px;
        }

        .cart-proveedores-title i {
            width: 24px;
            height: 24px;
            background: #004E60;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #FFDC2B;
            font-size: 12px;
            flex-shrink: 0;
        }

        .cart-btn-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            vertical-align: middle;
            margin-right: 4px;
            filter: brightness(0) saturate(100%) invert(89%) sepia(47%) saturate(600%) hue-rotate(5deg) brightness(105%);
        }

        /* -- Panel de totales -- */
        .cart-summary-panel {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .cart-summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 13px 16px;
            border-bottom: 1px solid rgba(0, 78, 96, 0.08);
            background: #fff;
        }

        .cart-summary-row:last-child {
            border-bottom: none;
        }

        .cart-summary-row strong {
            font-size: 18px;
            margin: 0;
            display: flex;
            align-items: center;
        }

        /* -- Íconos de fila -- */
        .cart-row-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .cart-row-icon-neutral {
            background: rgba(183, 255, 255, 0.35);   /* celeste suave */
            color: #004E60;
        }

        .cart-row-icon-red {
            background: rgba(255, 109, 1, 0.12);     /* naranja suave */
            color: #FF6D01;
        }

        /* -- Fila Subtotal -- */
        .cart-summary-row-subtotal {
            background: #fff;
        }

        .cart-summary-row-subtotal .subtotalProductoCarrito {
            color: #004E60;
            font-size: 18px;
            font-weight: 800;
            white-space: nowrap;
            flex-shrink: 0;
            display: inline-flex;
            align-items: baseline;
            gap: 0;
        }

        /* -- Fila Ahorras -- */
        .cart-summary-row-discount {
            background: #fff;
        }

        .cart-summary-row-discount .totalDescuentoCarrito {
            color: #FF6D01 !important;
            text-decoration: none !important;
            font-weight: 700;
            font-size: 18px;
            white-space: nowrap;
            flex-shrink: 0;
            display: inline-flex;
            align-items: baseline;
            gap: 0;
        }

        /* -- Fila Total (protagonista) -- */
        .cart-summary-row-total {
            background: #004E60;
            padding: 14px 16px;
            border-radius: 14px;
        }

        .cart-summary-row-total strong {
            text-transform: uppercase;
            letter-spacing: 0.04em;
            flex-direction: column;
            color: #FFDC2B;
            align-items: flex-start;
        }

        .cart-summary-row-total strong .cart-total-label-hint {
            font-size: 10px;
            font-weight: 500;
            color: #FFDC2B;
            letter-spacing: 0;
            text-transform: none;
        }

        .cart-summary-row-total .totalProductoCarrito {
            color: #FFDC2B;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.02em;
            white-space: nowrap;
            flex-shrink: 0;
            display: inline-flex;
            align-items: baseline;
            gap: 0;
        }

        /* -- Botón Ver Carrito -- */
        .cart-cta-wrap {
            margin-top: 12px;
        }

        .cart-cta-wrap .btn {
            min-height: 46px;
            border-radius: 14px;
            font-weight: 800;
            letter-spacing: .01em;
            background: #004E60 !important;
            border-color: #004E60 !important;
            color: #FFDC2B !important;
            box-shadow: 0 12px 24px rgba(0, 78, 96, 0.30);
        }

        .cart-cta-wrap .btn:hover {
            background: #003847 !important;
            border-color: #003847 !important;
            color: #FFDC2B !important;
        }

        /* -- Botones qty -/+ -- */
        .qty-down-mini,
        .qty-up-mini {
            border-color: #004E60 !important;
            color: #004E60 !important;
            background: transparent !important;
        }

        .qty-down-mini:hover,
        .qty-up-mini:hover {
            background: #004E60 !important;
            color: #FFDC2B !important;
        }

        .menu-responsive-scroll {
            overflow-x: auto;
            white-space: nowrap;
            flex-wrap: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .menu-responsive-scroll li {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .limitar-lineas {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* Número de líneas a mostrar */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .limitar-lineas-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            /* Número de líneas a mostrar */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .limitar-lineas-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            /* Número de líneas a mostrar */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .limitar-lineas-4 {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            /* Número de líneas a mostrar */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .titulo-subrayado {
            display: inline-block;
            border-bottom: 3px solid #000;
            /* Grosor y color del subrayado */
            padding-bottom: 5px;
            font-family: "Inter", sans-serif !important;
        }

        .h4 {
            font-family: "Inter", sans-serif;
            font-weight: bold;
            font-style: normal;
        }

        .card-2 {
            width: 200px
        }

        /* Contenedor del input de búsqueda */
        .search-style-2 {
            position: relative;
            z-index: 9999;
        }



        /* Ítems y estados */
        #resultados-productos .resultado-item {
            cursor: pointer;
        }

        #resultados-productos .resultado-item:hover {
            background: #f8f9fa;
        }

        /* Fondo oscuro */
        #search-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 9997;
            display: none;
        }

        /* Panel de resultados */
        #resultados-productos {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            z-index: 9999;
            width: 100%;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .1);
            max-height: 60vh;
            overflow-y: auto;
            display: none;
        }

        /* Estilo del input, solo si lo necesitas más visible aún */
        #input-busqueda {
            z-index: 9999;
            position: relative;
        }

        .form-check-input {
            margin-top: 0rem;
        }



        .btn-emergencias {
            position: relative;
            overflow: hidden;
            background: #dc3545;
            /* rojo */
            color: #fff !important;
            border: none;
            border-radius: .5rem;
            box-shadow: 0 2px 6px rgba(220, 53, 69, .22);
        }


        .btn-emergencias:hover {
            filter: brightness(.95);
            box-shadow: 0 4px 10px rgba(220, 53, 69, .32);
        }

        .header-cta-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #0f172a !important;
            background: rgba(0, 78, 96, 0.12);
            border: 1px solid rgba(0, 78, 96, 0.18);
            box-shadow: 0 6px 16px rgba(0, 78, 96, 0.12);
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .header-cta-link:hover {
            transform: translateY(-1px);
            background: rgba(0, 78, 96, 0.18);
            box-shadow: 0 10px 22px rgba(0, 78, 96, 0.18);
            color: #0f172a !important;
        }

        .header-action-2 .header-action-icon-2>a img {
            width: 100%;
            max-width: 60px;
        }

        #cookie-banner {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 99999;
            background: #ffffff;
            color: #111;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .2);
            border-top: 3px solid #4CAF50;
            font-size: 14px;
            line-height: 1.4;
            display: none;
            /* se controla por JS */
        }

        #cookie-banner .cookie-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-start;
            justify-content: space-between;
        }

        #cookie-banner .cookie-text {
            flex: 1 1 260px;
        }

        #cookie-banner .cookie-text p {
            margin: 0 0 6px 0;
            font-size: 14px;
        }

        #cookie-banner .cookie-text a {
            color: #4CAF50;
            text-decoration: underline;
            font-weight: 500;
        }

        #cookie-banner .cookie-actions {
            flex: 0 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            min-width: 220px;
            justify-content: flex-end;
        }

        #cookie-banner .cookie-btn {
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            min-width: 90px;
            text-align: center;
        }

        #cookie-btn-aceptar {
            background-color: #4CAF50;
            color: #fff;
            border-color: #4CAF50;
        }

        #cookie-btn-configurar {
            background-color: #fff;
            color: #4CAF50;
            border-color: #4CAF50;
        }

        #cookie-btn-rechazar {
            background-color: #eee;
            color: #444;
            border-color: #ccc;
        }

        @media(max-width:768px) {
            #cookie-banner .cookie-inner {
                flex-direction: column;
                align-items: stretch;
            }

            #cookie-banner .cookie-actions {
                justify-content: stretch;
                width: 100%;
            }

            #cookie-banner .cookie-btn {
                flex: 1;
            }
        }

        .swal2-container {
            z-index: 200000 !important;
            /* mayor que tu overlay */
        }

        /* Asegura que el modal quede por encima del menú móvil */
        .mobile-header-active {
            z-index: 12000 !important;
        }

        /* Modal por encima del menú */
        #modalElegirRol {
            z-index: 13000 !important;
        }

        /*Como no usaremos backdrop, por si acaso lo bajamos
        .modal-backdrop {
            z-index: 12500 !important;
            opacity: 0 !important;
             transparente 
        }*/

        /* Modal “más pequeño” para que se vea el menú detrás */
        #modalElegirRol .modal-dialog {
            max-width: 520px;
            width: calc(100% - 32px);
        }


        @media (max-width: 767px) {
            .cart-dropdown-wrap {
                width: 350px !important;
            }
        }

        .btn-eliminar-item {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .btn-eliminar-item:hover {
            background: rgba(220, 53, 69, .1);
        }

        .cart-mini-item {
            width: 100%;
            list-style: none;
            padding: 7px 10px;
            margin-bottom: 2px;
            border: 1px solid rgba(0, 78, 96, 0.10);
            border-radius: 12px;
            background: #fff;
        }

        .cart-mini-thumb {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .cart-mini-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .cart-mini-body {
            width: 100%;
        }

        /* evita que el texto rompa el layout */
        .cart-mini-title {
            font-size: 13px;
            line-height: 1.2;
            max-width: 230px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .cart-mini-price-line {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #64748b !important;
        }

        .cart-mini-old-price {
            color: #FF6D01;                          /* naranja FULMUV */
            font-size: 12px;
            text-decoration: line-through;
            font-weight: 400;
        }

        .cart-mini-saving-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 3px 9px;
            background: rgba(255, 220, 43, 0.20);   /* amarillo FULMUV */
            color: #004E60;                          /* turquesa FULMUV */
            font-size: 11px;
            font-weight: 800;
        }

        .cart-mini-subtotal {
            color: #004E60 !important;               /* turquesa FULMUV */
            font-size: 15px;
            line-height: 1.1;
        }

        /* botones mini coherentes */
        .qty-down-mini,
        .qty-up-mini {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .mini-qty {
            width: 84px !important;
            height: 32px;
            font-weight: 800;
            color: #004E60 !important;
            background: rgba(183, 255, 255, 0.15);
            border-color: rgba(0, 78, 96, 0.25) !important;
            pointer-events: none;
        }

        .listaProductoCarrito {
            padding-left: 0;
            margin: 0;
        }

        /* =========================
   Compactar mini-carrito en móvil
   ========================= */
        @media (max-width: 576px) {

            /* el dropdown/panel (si aplica) */
            .listaProductoCarrito {
                padding-left: 0;
                margin: 0;
            }

            .cart-mini-item {
                padding: .75rem !important;
            }

            /* menos separación general */
            .cart-mini-item .gap-2 {
                gap: .5rem !important;
            }

            /* imagen más pequeña */
            .cart-mini-thumb {
                width: 48px;
                height: 48px;
                flex: 0 0 48px;
                border-radius: 8px;
            }

            /* título más compacto */
            .cart-mini-title {
                font-size: 12.5px;
                max-width: 170px;
                /* ajusta según tu dropdown */
                -webkit-line-clamp: 2;
            }

            /* precios y textos */
            .cart-mini-body .small {
                font-size: 11.5px;
            }

            .cart-mini-body .fw-bold.text-secondary {
                font-size: 13px;
            }

            /* botón eliminar más pequeño */
            .btn-eliminar-item i {
                font-size: 14px;
            }

            /* botones +/- más pequeños */
            .qty-down-mini,
            .qty-up-mini {
                width: 28px;
                height: 28px;
                padding: 0;
                font-size: 14px;
            }

            /* input cantidad más pequeño */
            .mini-qty {
                width: 62px !important;
                height: 28px;
                font-size: 12px;
                padding: 0 .25rem;
            }

            /* opcional: reduce separación vertical en bloque precio */
            .cart-mini-body .mt-2 {
                margin-top: .4rem !important;
            }
        }

        /* EXTRA: para pantallas muy pequeñas */
        @media (max-width: 360px) {
            .cart-mini-title {
                max-width: 140px;
            }

            .qty-down-mini,
            .qty-up-mini {
                width: 26px;
                height: 26px;
            }

            .mini-qty {
                width: 58px !important;
                height: 26px;
            }
        }


        @media (max-width: 576px) {

            .dropdown-menu.cart-dropdown,
            .shopping-cart-dropdown {
                width: 92vw !important;
                max-width: 92vw !important;
            }
        }

        .mobile-header-wrapper-inner {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .mobile-header-content-area {
            flex: 1 1 auto;
            overflow: auto;
        }

        @media (max-width: 480px) {
            .section-title h3 {
                font-size: 22px !important;
                /* ajusta a tu gusto */
                line-height: 1.2 !important;
            }
        }

        @media(max-width: 480px) {
            .shop-product-fillter .sort-by-product-area {
                display: inline;
            }

            .sort-by-cover {
                margin-top: 10px;
            }
        }

        .mobile-filterbar button {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .06);
        }

        @media (max-width: 991.98px) {
            #mobileFilters {
                margin-top: 10px;
                padding: 10px;
                border: 1px solid #eee;
                border-radius: 14px;
                box-shadow: 0 10px 24px rgba(0, 0, 0, .08);
            }

            /* quita tanto margen superior para que no quede largo */
            #mobileFilters .mt-30 {
                margin-top: 12px !important;
            }
        }

        /* En móvil: filtros arriba, productos abajo */
        @media (max-width: 991.98px) {

            /* quita el reverse solo en móvil */
            .row.flex-row-reverse {
                flex-direction: column !important;
            }

            /* filtros arriba */
            .primary-sidebar {
                order: 1 !important;
                width: 100% !important;
                margin-bottom: 12px;
            }

            /* productos abajo */
            .col-lg-4-5 {
                order: 2 !important;
                width: 100% !important;
            }
        }

        /* Sección 1: Cuadros Uniformes */
        .featured .banner-left-icon {
            background: #fff;
            border: 1px solid #ececec;
            border-radius: 15px;
            padding: 20px;
            height: 100%;
            /* Hace que todos midan lo mismo en la fila */
            transition: 0.3s all ease;
        }

        .featured .banner-left-icon:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transform: translateY(-5px);
        }

        .icon-box-title {
            font-size: 16px !important;
            font-weight: 700;
            margin-bottom: 5px;
            line-height: 1.2;
        }

        .banner-text p {
            font-size: 13px;
            margin-bottom: 0;
            color: #7e7e7e;
        }

        /* Sección 2: Cuadro Gris con Efecto */
        .brand-box {
            background-color: #f4f6f8;
            /* Gris suave */
            border-radius: 20px;
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid transparent;
            text-align: center;
        }

        .brand-box:hover {
            background-color: #ffffff;
            border-color: #00686f;
            /* Color de tu logo */
            box-shadow: 0 20px 40px rgba(0, 104, 111, 0.1);
            transform: scale(1.02);
        }

        .brand-title {
            color: #00686f;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .header-middle .header-wrap {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .header-middle .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1 1 auto;
            min-width: 0;
        }

        .header-middle .header-action-right {
            flex: 0 0 auto;
        }

        .header-middle #listTienda {
            min-width: 190px;
        }

        .search-style-2 {
            position: relative;
            z-index: 1001;
            flex: 1 1 auto;
            min-width: 0;
            width: auto;
        }

        .search-style-2>form,
        .search-style-2>#resultados-productos {
            display: none !important;
        }

        .smart-search-shell {
            position: relative;
            width: 100%;
        }

        .smart-search-input {
            min-height: 56px;
            border-radius: 20px !important;
            border: 1px solid #d7e2ea !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            padding-left: 58px !important;
            padding-right: 48px !important;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .smart-search-input:focus {
            border-color: rgba(0, 78, 96, 0.42) !important;
            box-shadow: 0 18px 40px rgba(0, 78, 96, 0.14) !important;
        }

        .smart-search-input::placeholder {
            color: #64748b;
            font-weight: 500;
        }

        .smart-search-brain {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(0, 78, 96, 0.12);
            color: #004e60;
            pointer-events: none;
            box-shadow: inset 0 0 0 1px rgba(0, 78, 96, 0.08);
        }

        .smart-search-clear {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #64748b;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .smart-search-clear.is-visible {
            display: inline-flex;
        }

        .smart-search-clear:hover {
            background: rgba(148, 163, 184, 0.14);
            color: #0f172a;
        }

        #smart-search-overlay {
            display: none !important;
        }

        #smart-search-modal {
            position: fixed;
            top: 0;
            left: 0;
            transform: none;
            width: 0;
            max-height: 72vh;
            overflow: hidden;
            z-index: 9999;
            display: none;
        }

        #smart-search-modal.is-open {
            display: block;
        }

        .smart-search-window {
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            max-height: 72vh;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .smart-search-header {
            padding: 20px 24px 14px;
            border-bottom: 1px solid #e2e8f0;
            background:
                radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 36%),
                linear-gradient(180deg, #fbfeff 0%, #f8fbfd 100%);
        }

        .smart-search-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 6px;
        }

        .smart-search-title {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
        }

        .smart-search-subtitle {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
        }

        .smart-search-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(0, 78, 96, 0.10);
            color: #004e60;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .smart-search-body {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            min-height: 420px;
            max-height: 62vh;
            overflow-y: auto;
        }

        .smart-search-sidebar {
            border-right: 1px solid #e2e8f0;
            padding: 22px 18px;
            background: linear-gradient(180deg, #f8fbfd 0%, #f1f7fa 100%);
            overflow-y: auto;
        }

        .smart-search-sidebar-title {
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 12px;
        }

        .smart-search-filter-list {
            display: grid;
            gap: 10px;
        }

        .smart-search-filter {
            width: 100%;
            border: 1px solid #dbe5ec;
            background: #fff;
            color: #0f172a;
            border-radius: 16px;
            padding: 12px 14px;
            text-align: left;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            transition: all .2s ease;
        }

        .smart-search-filter:hover,
        .smart-search-filter.is-active {
            border-color: rgba(0, 78, 96, 0.28);
            background: rgba(0, 78, 96, 0.08);
            color: #004e60;
            transform: translateY(-1px);
        }

        .smart-search-filter small {
            font-size: 11px;
            font-weight: 700;
            color: inherit;
            opacity: .7;
        }

        .smart-search-side-note {
            margin-top: 16px;
            padding: 14px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid #dbe5ec;
            color: #475569;
            font-size: 12px;
            line-height: 1.6;
        }

        .smart-search-content {
            padding: 22px 22px 24px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .smart-search-empty-state,
        .smart-search-initial-state,
        .smart-search-loading-state {
            min-height: 460px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 32px;
            color: #64748b;
        }

        .smart-search-hero-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            border-radius: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 78, 96, 0.1);
            color: #004e60;
            font-size: 28px;
        }

        .smart-search-section {
            margin-bottom: 26px;
        }

        .smart-search-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .smart-search-section-title {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
        }

        .smart-search-section-meta {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .smart-search-context-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .smart-search-context-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(0, 78, 96, 0.08);
            color: #004e60;
            font-size: 11px;
            font-weight: 800;
            border: 1px solid rgba(0, 78, 96, 0.1);
        }

        .smart-search-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .smart-search-card {
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: #fff;
            padding: 14px;
            display: grid;
            grid-template-columns: 86px minmax(0, 1fr);
            gap: 14px;
            align-items: start;
            min-height: 148px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .smart-search-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 34px rgba(15, 23, 42, 0.12);
            border-color: rgba(0, 78, 96, 0.22);
        }

        .smart-search-card-media {
            width: 86px;
            height: 86px;
            border-radius: 18px;
            overflow: hidden;
            background: linear-gradient(180deg, #f8fafc 0%, #eef4f7 100%);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .smart-search-card-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .smart-search-card-body {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .smart-search-card-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(0, 78, 96, 0.08);
            color: #004e60;
            font-size: 11px;
            font-weight: 800;
        }

        .smart-search-card-title {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.35;
            color: #0f172a;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .smart-search-card-subtitle,
        .smart-search-card-price {
            margin: 0;
            font-size: 13px;
            color: #64748b;
            line-height: 1.45;
        }

        .smart-search-card-subtitle {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: calc(1.45em * 2);
            white-space: normal;
            word-break: break-word;
        }

        .smart-search-card-price {
            font-size: 16px;
            color: #004e60;
            font-weight: 900;
        }

        .smart-search-card-action {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border-radius: 14px;
            padding: 10px 14px;
            background: #004e60;
            color: #fff !important;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 12px 24px rgba(0, 78, 96, 0.18);
        }

        .smart-search-card-action:hover {
            background: #006274;
            color: #fff !important;
        }

        .smart-search-empty-card {
            border: 1px dashed #cbd5e1;
            border-radius: 22px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 24px 20px;
            color: #64748b;
            text-align: center;
        }

        .smart-search-empty-card strong {
            display: block;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .smart-search-more-row {
            display: flex;
            justify-content: center;
            margin-top: 12px;
        }

        .smart-search-more-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 999px;
            border: 1px solid #dbe5ec;
            background: #fff;
            color: #004e60 !important;
            font-weight: 900;
            text-decoration: none;
        }

        .smart-search-more-link:hover {
            border-color: rgba(0, 78, 96, 0.26);
            background: rgba(0, 78, 96, 0.06);
        }

        @media (max-width: 991px) {
            .smart-search-body {
                grid-template-columns: 1fr;
            }

            .smart-search-sidebar {
                border-right: 0;
                border-bottom: 1px solid #e2e8f0;
            }

            .smart-search-filter-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .smart-search-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="single-product ps-2 pe-2">
    <header class="header-area header-style-1 header-height-2">
        <div class="header-top header-top-ptb-1 d-none d-lg-block pb-0">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-5 col-lg-5">
                        <div class="header-info">
                            <ul>
                                <li><a class="header-cta-link" href="empresa/crear_empresa.php">Vende en FULMUV</a></li>
                                <li><a class="header-cta-link" href="anuncia_fulmuv.php">Anuncia en FULMUV</a></li>
                                <li><a class="header-cta-link" href="empleos.php">Encuentra tu empleo</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-7 col-lg-7 text-end">
                        <div class="d-flex justify-content-end">
                            <div id="news-flash" style="overflow: hidden; position: relative; height: 13.9844px;">
                                <ul style="position: absolute; margin: 0px; padding: 0px; top: 0px;">
                                    <li style="margin: 0px; padding: 0px; height: 13.9844px;">Todas tus empresas vehiculares del país</li>
                                    <li style="margin: 0px; padding: 0px; height: 13.9844px;">Encuentra 24/7 todos los repuestos y accesorios disponibles a nivel nacional</li>
                                    <li style="margin: 0px; padding: 0px; height: 13.9844px;">Encuentra 24/7 todos los servicios disponibles a nivel nacional</li>
                                    <li style="margin: 0px; padding: 0px; height: 13.9844px;">Todos tus vehículos y eventos vehiculares a nivel nacional</li>
                                    <li style="margin: 0px; padding: 0px; height: 13.9844px;">Elige entre una gama amplia de opciones, lo que quieras y necesites</li>
                                    <li style="margin: 0px; padding: 0px; height: 13.9844px;">FULMUV te envía a domicilio a cualquier parte del país</li>
                                    <li style="margin: 0px; padding: 0px; height: 13.9844px;">Contacta directamente a tus empresas de preferencia</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-middle header-middle-ptb-1 d-none d-lg-block py-2">
            <div class="container">
                <div class="header-wrap">
                    <div class="logo logo-width-1">
                        <a href="https://fulmuv.com/"><img src="img/FULMUV LOGO-13.png" alt="logo" /></a>
                    </div>
                    <div class="header-right">
                        <div class="search-style-2">
                            <div class="smart-search-shell">
                                <span class="smart-search-brain" aria-hidden="true">
                                    <i class="fa-solid fa-brain"></i>
                                </span>
                                <input type="text" id="input-busqueda-smart" placeholder="Busca productos, servicios, vehículos, eventos y empleos con sugerencias inteligentes" autocomplete="off" class="form-control smart-search-input" />
                                <button type="button" class="smart-search-clear" id="smartSearchClear" aria-label="Limpiar búsqueda">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <form action="javascript:void(0);" class="d-flex w-100">
                                <input type="text" id="input-busqueda" placeholder="Buscar por productos, servicios y vehículos....." autocomplete="off" class="form-control" />
                            </form>
                            <div id="resultados-productos" class="bg-white border rounded mt-1 position-absolute w-100" style="z-index: 1000; display: none;">
                                <!-- Resultados -->
                            </div>
                        </div>
                        <div class="header-action-right">
                            <div class="header-action-2">
                                <div class="ms-4">
                                    <div class="form-group w-100 border mb-0">
                                        <!-- <label for="listTienda">Tienda:</label> -->
                                        <select id="listTienda" class="form-control"></select>
                                    </div>
                                </div>
                                <div class="header-action-icon-2 position-relative ms-3 me-3">
                                    <a class="mini-cart-icon">
                                        <img alt="Carrito de compra" src="img/carrito_transparente.png" class="text-center d-flex justify-content-center align-items-center" />
                                        <span class="pro-count blue contadorCarrito" id="contadorCarrito">0</span>
                                    </a>
                                    <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                        <ul class="listaProductoCarrito" id="listaProductoCarrito"></ul>

                                        <div class="shopping-cart-footer">
                                            <div class="cart-proveedores-title"><i class="fi fi-rs-shopping-cart"></i>Total a pagar a proveedores</div>
                                            <div class="shopping-cart-total cart-summary-panel" id="cartTotals">
                                                <div class="cart-summary-row cart-summary-row-subtotal">
                                                    <strong>
                                                        <span class="cart-row-icon cart-row-icon-neutral"><i class="fi fi-rs-receipt"></i></span>
                                                        Subtotal
                                                    </strong>
                                                    <span class="subtotalProductoCarrito"></span>
                                                </div>

                                                <div class="cart-summary-row cart-summary-row-discount">
                                                    <strong>
                                                        <span class="cart-row-icon cart-row-icon-red"><i class="fi fi-rs-label"></i></span>
                                                        Ahorras
                                                    </strong>
                                                    <span id="totalDescuentoCarrito" class="totalDescuentoCarrito font-md ml-15"></span>
                                                </div>

                                                <div class="cart-summary-row cart-summary-row-total">
                                                    <strong style="font-size: 14px">
                                                        Total a pagar
                                                        <span class="cart-total-label-hint">Con descuentos aplicados</span>
                                                    </strong>
                                                    <span id="totalProductoCarrito" class="totalProductoCarrito"></span>
                                                </div>
                                            </div>

                                            <div class="shopping-cart-button cart-cta-wrap" id="cartBtnWrap">
                                                <a id="btnVerCarrito" href="shop-cart.php" class="btn btn-primary w-100">
                                                    <img src="img/carrito_transparente.png" class="cart-btn-icon"> Ver Carrito
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($frontUserLoggedIn): ?>
                                    <div class="header-action-icon-2 ms-3">
                                        <a href="#">
                                            <img class="svgInject" alt="Nest" src="themelading/nest-frontend/assets/imgs/theme/icons/icon-user.svg">
                                        </a>
                                        <div class="cart-dropdown-wrap cart-dropdown-hm2 account-dropdown">
                                            <ul>
                                                <li><a href="mi_cuenta.php"><i class="fi fi-rs-user mr-10"></i>Mi cuenta</a></li>
                                                <li><a href="lista_pedidos.php"><i class="fi fi-rs-location-alt mr-10"></i>Seguimiento de pedidos</a></li>
                                                <li><a href="cambiar_contrasena.php"><i class="fi fi-rs-settings-sliders mr-10"></i>Cambiar contraseña</a></li>
                                                <li><a href="logout.php"><i class="fi fi-rs-sign-out mr-10"></i>Cerrar Sesión</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!$frontUserLoggedIn): ?>
                                <!-- Botón Login que abre el modal -->
                                <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#modalElegirRol">
                                    <i class="fi fi-rs-sign-in"></i> <span>Login</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="header-bottom header-bottom-bg-color d-block d-lg-none">
            <div class="container">
                <div class="header-wrap header-space-between position-relative">
                    <div class="logo logo-width-1 d-block d-lg-none">
                        <a href="https://fulmuv.com/"><img src="img/FULMUV LOGO-13.png" alt="logo"></a>
                    </div>
                    <div class="header-nav d-none d-lg-flex">
                        <div class="d-none d-lg-block">
                            <a class="btn btn-md btn-emergencias" href="servicios_emergencia.php" aria-label="Atención 24/7 y emergencias">
                                <span class="me-1">🚨</span>
                                <span class="et">24/7 + Emergencias</span>
                            </a>
                        </div>
                        <div class="main-menu main-menu-padding-1 main-menu-lh-2 d-none d-lg-block font-heading ms-4">
                            <nav>
                                <ul class="nav w-100 text-center" style="justify-content: center;">
                                    <!-- <li class="nav-item flex-fill me-4">
                                        <a class="nav-link fw-bold" href="productos_categoria.php?q=10"></a>
                                    </li> -->
                                    <li class="nav-item flex-fill me-4">
                                        <a class="nav-link fw-bold text-dark" href="productos_categoria.php?q=1">Accesorios</a>
                                    </li>
                                    <li class="nav-item flex-fill me-4">
                                        <a class="nav-link fw-bold text-dark" href="productos_categoria.php?q=2">Repuestos</a>
                                    </li>
                                    <li class="nav-item flex-fill me-4">
                                        <a class="nav-link fw-bold text-dark" href="servicios.php">Servicios</a>
                                    </li>
                                    <li class="nav-item flex-fill me-4">
                                        <a class="nav-link fw-bold text-dark" href="vehiculos.php">Vehículos</a>
                                    </li>
                                    <li class="nav-item flex-fill me-4">
                                        <a class="nav-link fw-bold text-dark" href="eventos.php">Eventos</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="header-action-icon-2 d-block d-lg-none">
                        <div class="burger-icon burger-icon-white">
                            <span class="burger-icon-top"></span>
                            <span class="burger-icon-mid"></span>
                            <span class="burger-icon-bottom"></span>
                        </div>
                    </div>
                    <div class="header-action-right d-block d-lg-none">
                        <div class="header-action-2">
                            <div class="header-action-icon-2">
                                <a class="mini-cart-icon" href="#">
                                    <img alt="Carrito de compra" src="img/carrito_transparente.png" />
                                    <span class="pro-count blue contadorCarrito" id="contadorCarrito">0</span>
                                </a>
                                <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                    <ul class="listaProductoCarrito" id="listaProductoCarrito"></ul>

                                    <div class="shopping-cart-footer">
                                        <div class="cart-proveedores-title"><i class="fi fi-rs-shopping-cart"></i>Total a pagar a proveedores</div>
                                        <div class="shopping-cart-total cart-summary-panel" id="cartTotals2">
                                            <div class="cart-summary-row cart-summary-row-subtotal">
                                                <strong>
                                                    <span class="cart-row-icon cart-row-icon-neutral"><i class="fi fi-rs-receipt"></i></span>
                                                    <span style="color: #004E60">Subtotal</span>
                                                </strong>
                                                <span class="subtotalProductoCarrito"></span>
                                            </div>

                                            <div class="cart-summary-row cart-summary-row-discount">
                                                <strong>
                                                    <span class="cart-row-icon cart-row-icon-red"><i class="fi fi-rs-label"></i></span>
                                                    <span style="color: #004E60">Ahorras</span>
                                                </strong>
                                                <span id="totalDescuentoCarrito" class="totalDescuentoCarrito font-md ml-15"></span>
                                            </div>

                                            <div class="cart-summary-row cart-summary-row-total">
                                                <strong>
                                                    Total a pagar
                                                    <span class="cart-total-label-hint">Con descuentos aplicados</span>
                                                </strong>
                                                <span id="totalProductoCarrito" class="totalProductoCarrito"></span>
                                            </div>
                                        </div>

                                        <div class="shopping-cart-button cart-cta-wrap" id="cartBtnWrap2">
                                            <a id="btnVerCarrito" href="shop-cart.php" class="btn btn-primary w-100">
                                                <img src="img/carrito_transparente.png" class="cart-btn-icon"> Ver Carrito
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-bottom header-bottom-bg-color d-none d-lg-block">
            <div class="container d-none d-lg-flex align-items-center justify-content-between p-1">
                <div class="header-action-icon-2 me-4 ms-2">
                    <div class="burger-icon burger-icon-white">
                        <span class="burger-icon-top"></span>
                        <span class="burger-icon-mid"></span>
                        <span class="burger-icon-bottom"></span>
                    </div>
                </div>
                <!-- Botón 24 horas a la izquierda -->
                <div class="me-3 flex-shrink-0">
                    <a href="servicios_emergencia.php" class="btn btn-md btn-emergencias" aria-label="Atención 24/7 y emergencias">
                        <span class="me-1" aria-hidden="true">🚨</span> 24/7 + Emergencias
                    </a>
                </div>

                <!-- Menú centrado y expandido a lo ancho -->
                <nav class="flex-grow-1">
                    <ul class="nav justify-content-center text-center w-100 menu-nav">
                        <li class="nav-item flex-fill border-end">
                            <a class="nav-link fw-bold text-dark" href="productos_categoria.php?q=1">Accesorios</a>
                        </li>
                        <li class="nav-item flex-fill border-end">
                            <a class="nav-link fw-bold text-dark" href="productos_categoria.php?q=2">Repuestos</a>
                        </li>
                        <li class="nav-item flex-fill border-end">
                            <a class="nav-link fw-bold text-dark" href="servicios.php">Servicios</a>
                        </li>
                        <li class="nav-item flex-fill border-end">
                            <a class="nav-link fw-bold text-dark" href="vehiculos.php">Vehículos</a>
                        </li>
                        <li class="nav-item flex-fill">
                            <a class="nav-link fw-bold text-dark" href="eventos.php">Eventos</a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>
    </header>
    <div id="smart-search-overlay"></div>
    <div id="smart-search-modal" aria-hidden="true">
        <div class="smart-search-window">
            <div class="smart-search-header">
                <div class="smart-search-header-row">
                    <div>
                        <h3 class="smart-search-title">Búsqueda Inteligente FULMUV</h3>
                        <p class="smart-search-subtitle">Los resultados aparecen mientras escribes. No necesitas presionar Enter para encontrar coincidencias.</p>
                    </div>
                    <div class="smart-search-status" id="smartSearchStatus">
                        <i class="fa-solid fa-brain"></i>
                        <span>Esperando búsqueda</span>
                    </div>
                </div>
            </div>
            <div class="smart-search-body">
                <aside class="smart-search-sidebar">
                    <div class="smart-search-sidebar-title">Filtros rápidos</div>
                    <div class="smart-search-filter-list">
                        <button type="button" class="smart-search-filter is-active" data-search-filter="products">
                            <span>Productos</span>
                            <small id="smartCountProducts">0</small>
                        </button>
                        <button type="button" class="smart-search-filter" data-search-filter="services">
                            <span>Servicios</span>
                            <small id="smartCountServices">0</small>
                        </button>
                        <button type="button" class="smart-search-filter" data-search-filter="vehicles">
                            <span>Vehículos</span>
                            <small id="smartCountVehicles">0</small>
                        </button>
                        <button type="button" class="smart-search-filter" data-search-filter="events">
                            <span>Eventos</span>
                            <small id="smartCountEvents">0</small>
                        </button>
                        <button type="button" class="smart-search-filter" data-search-filter="jobs">
                            <span>Empleos</span>
                            <small id="smartCountJobs">0</small>
                        </button>
                    </div>
                    <div class="smart-search-side-note">
                        Esta b&uacute;squeda piensa mientras escribes. Elige un tipo y ver&aacute;s solo sus resultados con filtros contextuales.
                    </div>
                </aside>
                <section class="smart-search-content" id="smartSearchContent">
                    <div class="smart-search-initial-state">
                        <div>
                            <div class="smart-search-hero-icon"><i class="fa-solid fa-brain"></i></div>
                            <h4 class="mb-2">Empieza a escribir</h4>
                            <p class="mb-0">Selecciona un tipo y te mostraremos solo sus resultados con filtros adaptados a esa b&uacute;squeda.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <div class="mobile-header-active mobile-header-wrapper-style">
        <div class="mobile-header-wrapper-inner">
            <div class="mobile-header-top">
                <div class="mobile-header-logo d-flex justify-content-center align-items-center">
                    <a href="https://fulmuv.com/"><img src="img/FULMUV LOGO-13.png" alt="logo" /></a>
                </div>
                <div class="mobile-menu-close close-style-wrap close-style-position-inherit">
                    <button class="close-style search-close">
                        <i class="icon-top"></i>
                        <i class="icon-bottom"></i>
                    </button>
                </div>
            </div>

            <?php if ($frontUserLoggedIn): ?>
                <div class="row g-2 align-items-center justify-content-center m-2">

                    <!-- Editar usuario -->
                    <div class="col-4 col-md-6">
                        <a href="mi_cuenta.php"
                            class="btn btn-primary btn-sm w-100 position-relative d-flex align-items-center justify-content-center py-1"
                            style="border-radius:12px; min-height:40px;">
                            <i class="fi fi-rs-user position-absolute"
                                style="font-size:20px; left:12px;"></i>

                            <span class="d-none d-md-block w-100 text-center fw-bold">
                                Editar usuario
                            </span>
                        </a>
                    </div>

                    <!-- Editar contraseña -->
                    <div class="col-4 col-md-6">
                        <a href="cambiar_contrasena.php"
                            class="btn btn-primary btn-sm w-100 position-relative d-flex align-items-center justify-content-center py-1"
                            style="border-radius:12px; min-height:40px;">
                            <i class="fi fi-rs-settings-sliders position-absolute"
                                style="font-size:20px; left:12px;"></i>

                            <span class="d-none d-md-block w-100 text-center fw-bold ms-2">
                                Editar contraseña
                            </span>
                        </a>
                    </div>

                    <!-- Seguimiento de pedidos -->
                    <div class="col-4 col-md-6">
                        <a href="lista_pedidos.php"
                            class="btn btn-primary btn-sm w-100 position-relative d-flex align-items-center justify-content-center py-1"
                            style="border-radius:12px; min-height:40px;">
                            <i class="fi fi-rs-location-alt position-absolute"
                                style="font-size:20px; left:12px;"></i>

                            <span class="d-none d-md-block w-100 text-center fw-bold ms-2">
                                Pedidos
                            </span>
                        </a>
                    </div>

                </div>
            <?php endif; ?>

            <div class="mobile-header-content-area">
                <div class="mobile-menu-wrap mobile-header-border">
                    <!-- mobile menu start -->
                    <nav>
                        <ul class="mobile-menu font-heading">
                            <li class="menu-item-has-children">
                                <a class="header-cta-link" href="empresa/crear_empresa.php">Vende en FULMUV</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a class="header-cta-link" href="empresa/crear_empresa.php">Anuncia en FULMUV</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a class="header-cta-link" href="empleos.php">Encuentra tu empleo</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a href="productos_categoria.php?q=1">Accesorios</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a href="productos_categoria.php?q=2">Repuestos</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a href="servicios.php">Servicios</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a href="vehiculos.php">Vehículos</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a href="eventos.php">Eventos</a>
                            </li>
                        </ul>
                    </nav>
                    <!-- mobile menu end -->
                </div>
                <!-- <div class="mobile-header-info-wrap">
                    <div class="single-mobile-header-info">
                        <a href="page-contact.html"><i class="fi-rs-marker"></i> Our location </a>
                    </div>
                    <div class="single-mobile-header-info">
                        <a href="#"><i class="fi-rs-headphones"></i>(+01) - 2345 - 6789 </a>
                    </div>
                </div> -->
                <div class="row">
                    <div class="col-12 d-flex justify-content-center align-items-center">
                        <select id="listTienda2" class="form-control" style="font-family: none"></select>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <a class="btn btn-md btn-emergencias" href="servicios_emergencia.php" aria-label="Atención 24/7 y emergencias">
                            <span class="me-1">🚨</span>
                            <span class="">24/7 + Emergencias</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center mb-3">
                <?php if (!$frontUserLoggedIn): ?>
                    <!-- Botón Login que abre el modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalElegirRol">
                        <i class="fi fi-rs-sign-in"></i> <span>Login</span>
                    </button>
                <?php endif; ?>
            </div>
            <div class="mobile-social-icon mb-50 text-center d-none">
                <h6 class="mb-15">Síguenos</h6>
                <a href="#"><img src="themelading/nest-frontend/assets/imgs/theme/icons/icon-facebook-white.svg" alt="" /></a>
                <a href="#"><img src="themelading/nest-frontend/assets/imgs/theme/icons/icon-twitter-white.svg" alt="" /></a>
                <a href="#"><img src="themelading/nest-frontend/assets/imgs/theme/icons/icon-instagram-white.svg" alt="" /></a>
                <a href="#"><img src="themelading/nest-frontend/assets/imgs/theme/icons/icon-pinterest-white.svg" alt="" /></a>
                <a href="#"><img src="themelading/nest-frontend/assets/imgs/theme/icons/icon-youtube-white.svg" alt="" /></a>
            </div>
            <?php if ($frontUserLoggedIn): ?>
                <div class="px-3 pb-3 mt-2">
                    <a href="logout.php"
                        class="btn btn-danger w-100 d-flex align-items-center justify-content-center"
                        style="border-radius: 12px; min-height: 44px;">
                        <i class="fi fi-rs-sign-out me-2"></i>
                        Cerrar Sesión
                    </a>
                </div>
            <?php endif; ?>
            <div class="site-copyright">Copyright 2025 © FULMUV. Todos los derechos reservados.</div>
        </div>
    </div>
    </div>

    <!-- Modal: Elegir tipo de acceso -->
    <div class="modal fade" id="modalElegirRol" tabindex="-1" aria-labelledby="modalElegirRolLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="modalElegirRolLabel">Elige cómo deseas ingresar</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Proveedor -->
                        <div class="col-12 col-md-6">
                            <button type="button" id="btnLoginProveedor" class="w-100 btn btn-outline-primary d-flex align-items-center justify-content-center p-3 rounded-3 h-100">
                                <i class="fi fi-rs-briefcase me-2" style="font-size:20px;"></i>
                                <div class="text-start">
                                    <div class="fw-bold">Proveedor FULMUV</div>
                                    <small>Vende tus productos o servicios</small>
                                </div>
                            </button>
                        </div>

                        <!-- Cliente -->
                        <div class="col-12 col-md-6">
                            <button type="button" id="btnLoginCliente" class="w-100 btn btn-outline-dark d-flex align-items-center justify-content-center p-3 rounded-3 h-100">
                                <i class="fi fi-rs-user me-2" style="font-size:20px;"></i>
                                <div class="text-start">
                                    <div class="fw-bold">Soy cliente</div>
                                    <small>Compra y gestiona tus pedidos</small>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-link text-white btn-sm" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
 
    <!--End header-->
    <main class="main pages mb-80">
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb" id="breadcrumb">

                </div>
            </div>
        </div>
        <div class="page-content">
