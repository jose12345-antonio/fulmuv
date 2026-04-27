<?php
session_start();

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

session_cache_limiter('private_no_expire');
date_default_timezone_set('America/Guayaquil');

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,600&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Roboto+Slab&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        .cart-dropdown-wrap {
            width: 450px !important;
        }

        .shopping-cart-title {
            font-size: 14px;
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
            z-index: 1050;
            /* Menor que input y panel */
            display: none;
        }

        /* Panel de resultados */
        #resultados-productos {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            z-index: 1061;
            /* Por encima del overlay */
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
            z-index: 1061;
            position: relative;
        }

        #search-overlay {
            pointer-events: none;
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
        }

        .cart-mini-thumb {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            border-radius: 10px;
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
            font-size: 14px;
            line-height: 1.2;
            max-width: 230px;
            /* ajusta a tu dropdown */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* 2 líneas */
            -webkit-box-orient: vertical;
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
            width: 44px !important;
            height: 32px;
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
                padding-top: .5rem !important;
                padding-bottom: .5rem !important;
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
                width: 38px !important;
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
                width: 34px !important;
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
                                <li><a href="empresa/crear_empresa.php">Vende en FULMUV</a></li>
                                <li><a href="anuncia_fulmuv.php">Anuncia en FULMUV</a></li>
                                <li><a href="empleos.php">Encuentra tu empleo</a></li>
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
                        <div class="search-style-2" style="z-index: 1060;">
                            <form action="javascript:void(0);" class="d-flex w-100">
                                <select class="select-active" id="selectActiveCategory">
                                    <!-- Se llena dinámicamente -->
                                </select>
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
                                            <div class="shopping-cart-total" id="cartTotals">
                                                <h4 class="fw-bold">
                                                    Descuento
                                                    <span id="totalDescuentoCarrito" class="totalDescuentoCarrito text-danger font-md ml-15" style="text-decoration: line-through"></span>
                                                </h4>

                                                <h4 class="fw-bold mt-3">
                                                    Total
                                                    <span id="totalProductoCarrito" class="totalProductoCarrito"></span>
                                                </h4>
                                            </div>

                                            <div class="shopping-cart-button" id="cartBtnWrap">
                                                <a id="btnVerCarrito" href="shop-cart.php" class="btn btn-primary w-100">
                                                    Ver Carrito
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION["id_usuario"])): ?>
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
                            <?php if (!isset($_SESSION["id_usuario"])): ?>
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
                                        <div class="shopping-cart-total" id="cartTotals2">
                                            <h4 class="fw-bold">
                                                Descuento
                                                <span id="totalDescuentoCarrito" class="totalDescuentoCarrito text-danger font-md ml-15" style="text-decoration: line-through"></span>
                                            </h4>

                                            <h4 class="fw-bold mt-3">
                                                Total
                                                <span id="totalProductoCarrito" class="totalProductoCarrito"></span>
                                            </h4>
                                        </div>

                                        <div class="shopping-cart-button" id="cartBtnWrap2">
                                            <a id="btnVerCarrito" href="shop-cart.php" class="btn btn-primary w-100">
                                                Ver Carrito
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

            <?php if (isset($_SESSION["id_usuario"])): ?>
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
                                <a href="empresa/crear_empresa.php">Vende en FULMUV</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a href="empresa/crear_empresa.php">Anuncia en FULMUV</a>
                            </li>
                            <li class="menu-item-has-children">
                                <a href="empleos.php">Encuentra tu empleo</a>
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
                <?php if (!isset($_SESSION["id_usuario"])): ?>
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
            <?php if (isset($_SESSION["id_usuario"])): ?>
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