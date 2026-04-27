<?php
session_start();

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// session_cache_limiter('private_no_expire');
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
    <title>FULMUV</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta property="og:title" content="" />
    <meta property="og:type" content="" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="img/FULMUV-LOGO-60X60.png" />
    <link rel="stylesheet" href="../themelading/nest-frontend/assets/css/plugins/slider-range.css">
    <!-- Template CSS -->
    <link rel="stylesheet" href="../themelading/nest-frontend/assets/css/main.css?v=6.2.0.0.0.0.0.0.0.10" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,600&family=Figtree:wght@300;400;500;600;700;800;900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Roboto+Slab&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --fulmuv-green: #00686f;
            --fulmuv-bg-soft: #f8fafc;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --glass-bg: rgba(255, 255, 255, 0.8);
        }

        /* --- 2. Cuerpo y Fondo (Efecto Moderno) --- */
        body {
            background-color: var(--fulmuv-bg-soft);
            font-family: 'Figtree', sans-serif;
        }

        /* --- 3. Tarjetas (Cards) Renovadas --- */
        .product-cart-wrap {
            border: none !important;
            background: #fff !important;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: var(--card-shadow) !important;
            overflow: hidden;
            height: 100%;
        }

        .product-cart-wrap:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 104, 111, 0.12) !important;
        }

        /* Imagen del producto más grande y limpia */
        .product-img img.default-img {
            height: 180px !important;
            padding: 10px;
            object-fit: contain !important;
        }

        /* Tipografía de títulos */
        .product-content-wrap h2 a {
            font-size: 15px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            line-height: 1.3 !important;
            font-family: 'Inter', sans-serif !important;
        }


        /* --- 5. Títulos de Sección (Subrayado Minimalista) --- */
        .titulo-subrayado {
            font-size: 22px !important;
            font-weight: 800 !important;
            border-bottom: 4px solid var(--fulmuv-green) !important;
            padding-bottom: 8px;
            text-transform: none;
            letter-spacing: -0.5px;
            font-family: 'Inter', sans-serif !important;
        }

        /* --- 6. Optimizaciones para Móvil (Clave) --- */
        @media (max-width: 767px) {
            .section-title h3 {
                font-size: 19px !important;
            }

            /* Mostrar 2 productos por fila en móvil con espaciado elegante */
            #carausel-4-columns-oferta .slick-slide,
            #carausel-4-columns-para-ti .slick-slide {
                padding: 8px !important;
            }

            .product-img img.default-img {
                height: 140px !important;
            }

            /* Reducir banners de publicidad para que no sean intrusivos */
            #imgpublicidad,
            #imgpublicidad2 {
                max-height: 200px !important;
            }
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
    </style>
</head>

<body class="single-product">

    <!--End header-->
    <main class="main pages mb-10">
        <div class="page-content">
