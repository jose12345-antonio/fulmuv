<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
ini_set("display_errors", 0);
session_start();
//header('Cache-Control: no cache'); //no cache
session_cache_limiter('private_no_expire'); // works
date_default_timezone_set('America/Guayaquil');
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->



    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <!--  <link rel="apple-touch-icon" sizes="180x180" href="../theme/public/assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../theme/public/assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../theme/public/assets/img/favicons/favicon-16x16.png"> 
    <link rel="shortcut icon" type="image/x-icon" href="../theme/public/assets/img/favicons/favicon.ico">-->
    <link rel="manifest" href="../theme/public/assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="../theme/public/assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
    <!-- <script>
        localStorage.clear();
    </script> -->
    <script src="../theme/public/assets/js/config.js?0"></script>
    <script src="../theme/public/vendors/simplebar/simplebar.min.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Alerts -->
    <link href="../theme/public/vendors/sweetalert/sweetalert.css" rel="stylesheet">
    <script src="js/alerts.js"></script>

    <!-- SweetAlert2 CDN -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->



    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link href="../theme/public/vendors/leaflet/leaflet.css" rel="stylesheet">
    <link href="../theme/public/vendors/leaflet.markercluster/MarkerCluster.css" rel="stylesheet">
    <link href="../theme/public/vendors/leaflet.markercluster/MarkerCluster.Default.css" rel="stylesheet">
    <link href="../theme/public/vendors/flatpickr/flatpickr.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
    <link href="../theme/public/vendors/simplebar/simplebar.min.css" rel="stylesheet">
    <link href="../theme/public/assets/css/theme-rtl.css" rel="stylesheet" id="style-rtl">
    <link href="../theme/public/assets/css/theme.css" rel="stylesheet" id="style-default">
    <link href="../theme/public/assets/css/user-rtl.css" rel="stylesheet" id="user-style-rtl">
    <link href="../theme/public/assets/css/user.css" rel="stylesheet" id="user-style-default">
    <script src="../theme/public/vendors/datatables.net-bs5/dataTables.bootstrap5.min.css"></script>

    <link href="../theme/public/vendors/select2/select2.min.css" rel="stylesheet">
    <link href="../theme/public/vendors/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="../theme/public/vendors/dropzone/dropzone.css" rel="stylesheet">
    <link href="../theme/public/vendors/choices/choices.min.css" rel="stylesheet">

    <!-- Hoja de estilos Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <!-- Toastr.js Después -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        var isRTL = JSON.parse(localStorage.getItem('isRTL'));
        if (isRTL) {
            var linkDefault = document.getElementById('style-default');
            var userLinkDefault = document.getElementById('user-style-default');
            linkDefault.setAttribute('disabled', true);
            userLinkDefault.setAttribute('disabled', true);
            document.querySelector('html').setAttribute('dir', 'rtl');
        } else {
            var linkRTL = document.getElementById('style-rtl');
            var userLinkRTL = document.getElementById('user-style-rtl');
            linkRTL.setAttribute('disabled', true);
            userLinkRTL.setAttribute('disabled', true);
        }
    </script>
    <style>
        .btn-iso {
            --falcon-btn-color: #fff;
            --falcon-btn-bg: #00686f;
            --falcon-btn-border-color: #00686f;
            --falcon-btn-hover-color: #fff;
            --falcon-btn-hover-bg: #015258;
            --falcon-btn-hover-border-color: #015258;
            --falcon-btn-focus-shadow-rgb: 137, 148, 164;
            --falcon-btn-active-color: #fff;
            --falcon-btn-active-bg: #015258;
            --falcon-btn-active-border-color: #015258;
            --falcon-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
            --falcon-btn-disabled-color: #fff;
            --falcon-btn-disabled-bg: #00686f;
            --falcon-btn-disabled-border-color: #00686f;
        }

        .paymentez-checkout-frame {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 9999 !important;
            width: 420px !important;
            max-width: 95vw;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            border-radius: 12px;
        }

        .navbar-top,
        .navbar-vertical {
            transition: all 0.3s ease;
        }

        body.modal-open .navbar-top,
        body.modal-open .navbar-vertical {
            filter: blur(2px);
            pointer-events: none;
        }

        a.nav-link.disabled {
            pointer-events: none;
            /* No permite hacer clic */
            cursor: not-allowed;
            /* Cambia el cursor */
            opacity: 0.6;
            /* Se ve deshabilitado */
        }

        /* 1. Fondo principal con la imagen */
        body {
            background-image:
                linear-gradient(rgba(240, 245, 249, 0.65), rgba(240, 245, 249, 0.65)),
                url('../img/PATRONES_DECORATIVOS_PNG_48.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            height: 100vh;
            position: relative;
        }

        html[data-bs-theme="dark"] body,
        body.dark-theme-active {
            background-image: none !important;
            background-color: #000 !important;
        }

        /* 2. Capa de transparencia (Overlay) */
        /* Esto crea el efecto "transparentoso" sobre la imagen pero detrás del contenido */
        body::before {

            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Ajusta el 0.8 para más o menos transparencia (0.1 es muy transparente, 0.9 casi sólido) */
            background-color: rgba(255, 255, 255, 0.85);
            z-index: -1;
            /* Se mantiene detrás de todo el contenido */
        }

        html[data-bs-theme="dark"] body::before,
        body.dark-theme-active::before {
            background-color: rgba(0, 0, 0, 0.96) !important;
        }

        html[data-bs-theme="dark"] .navbar-top,
        html[data-bs-theme="dark"] .navbar-vertical,
        body.dark-theme-active .navbar-top,
        body.dark-theme-active .navbar-vertical {
            background: #000 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: none !important;
        }

        html[data-bs-theme="dark"] .navbar-vertical-content,
        body.dark-theme-active .navbar-vertical-content {
            background: #000 !important;
        }

        html[data-bs-theme="dark"] .navbar-nav.flex-column.mb-3,
        body.dark-theme-active .navbar-nav.flex-column.mb-3 {
            background: #000 !important;
        }

        html[data-bs-theme="dark"] .navbar-top .nav-link,
        html[data-bs-theme="dark"] .navbar-top .navbar-brand,
        html[data-bs-theme="dark"] .navbar-vertical .nav-link,
        body.dark-theme-active .navbar-top .nav-link,
        body.dark-theme-active .navbar-top .navbar-brand,
        body.dark-theme-active .navbar-vertical .nav-link {
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .dropdown-menu,
        body.dark-theme-active .dropdown-menu {
            background: #0a0a0a !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        html[data-bs-theme="dark"] .dropdown-item,
        body.dark-theme-active .dropdown-item {
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .dropdown-item:hover,
        body.dark-theme-active .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
        }

        html[data-bs-theme="light"] .navbar-top,
        html[data-bs-theme="light"] .navbar-vertical,
        html[data-bs-theme="light"] .navbar-vertical-content,
        body:not(.dark-theme-active) .navbar-top,
        body:not(.dark-theme-active) .navbar-vertical,
        body:not(.dark-theme-active) .navbar-vertical-content {
            background-color: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(10px);
        }

        .content {
            padding-top: 20px;
        }

        .theme-select-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .theme-select-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            color: #111827;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
            font-size: 12px;
            flex: 0 0 28px;
        }

        .theme-select {
            width: 80px;
            height: 24px;
            border: none;
            background: transparent;
            color: #111827;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: none !important;
            padding: 0 26px 0 2px;
            cursor: pointer;
        }

        .theme-select:focus {
            outline: none;
            border: none;
        }

        html[data-bs-theme="dark"] .theme-select-wrap,
        body.dark-theme-active .theme-select-wrap {
            background: rgba(10, 10, 10, 0.92);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3);
        }

        html[data-bs-theme="dark"] .theme-select,
        body.dark-theme-active .theme-select {
            color: #f8fafc;
        }

        html[data-bs-theme="dark"] .theme-select-icon,
        body.dark-theme-active .theme-select-icon {
            background: #111827;
            color: #f8fafc;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .support-shortcuts {
            margin-top: 14px;
            padding: 14px 12px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        .support-shortcuts-title {
            font-size: 0.82rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .support-shortcuts-text {
            font-size: 0.72rem;
            line-height: 1.6;
            color: #64748b;
            margin-bottom: 10px;
        }

        .support-shortcuts-actions {
            display: grid;
            gap: 8px;
        }

        .support-shortcut-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 10px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .support-shortcut-btn:hover {
            transform: translateY(-1px);
        }

        .support-shortcut-mail {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid rgba(37, 99, 235, 0.18);
        }

        .support-shortcut-wa {
            background: #ecfdf5;
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        html[data-bs-theme="dark"] .support-shortcuts,
        body.dark-theme-active .support-shortcuts {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.92) 0%, rgba(2, 6, 23, 0.96) 100%);
            border-color: rgba(148, 163, 184, 0.16);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        html[data-bs-theme="dark"] .support-shortcuts-title,
        body.dark-theme-active .support-shortcuts-title {
            color: #f8fafc;
        }

        html[data-bs-theme="dark"] .support-shortcuts-text,
        body.dark-theme-active .support-shortcuts-text {
            color: #cbd5e1;
        }
    </style>
</head>

<?php
if (!(isset($_SESSION["id_usuario"]) && (
    (isset($_SESSION["empresa_auth"]) && $_SESSION["empresa_auth"] === true) ||
    !empty($_SESSION["id_empresa"])
))) {
    echo "<script>window.location.href = 'login.php'</script>";
} else {
    $correo = $_SESSION["correo"];
    $id_usuario = $_SESSION["id_usuario"];
    $nombres = $_SESSION["nombres"];
    $apellidos = $_SESSION["apellidos"];
    $username = $_SESSION["username"];
    $rol_id = $_SESSION["rol_id"];
    $imagen = $_SESSION["imagen"];
    $imagen_fallback = "../img/FULMUV-LOGO-60X60.png";
    $imagen_principal_src = trim((string)$imagen) !== "" ? $imagen : $imagen_fallback;
    $nombre_rol_user = $_SESSION["nombre_rol_user"];
    $permisos = $_SESSION["permisos"];
    $id_empresa = $_SESSION["id_empresa"];
    $dashboard = $_SESSION["dashboard"];
    $membresia = $_SESSION["membresia"];
    $tipo_user = $_SESSION["tipo_user"];
    $tipo_user = ($tipo_user === "sucursal" || $tipo_user === 3 || $tipo_user === "3") ? "sucursal" : "empresa";

    if ($permisos["error"] == true) {
        echo "<script>window.location.href = 'login.php'</script>";
    }
    echo '<input type="hidden" id="id_principal" value="' . $id_usuario . '">';
    echo '<input type="hidden" id="id_rol_principal" value="' . $rol_id . '">';
    echo '<input type="hidden" id="nombre_rol_principal" value="' . $nombre_rol_user . '">';
    echo '<input type="hidden" id="username_principal" value="' . $username . '">';
    echo '<input type="hidden" id="id_empresa" value="' . $id_empresa . '">';
    echo '<input type="hidden" id="imagen_principal" value="' . htmlspecialchars($imagen_principal_src, ENT_QUOTES, "UTF-8") . '">';
    echo '<input type="hidden" id="id_carrito" value="carrito_' . $id_usuario . '_' . $id_empresa . '">';
    echo '<input type="hidden" id="id_carrito" value="carrito_' . $id_usuario . '_' . $id_empresa . '">';
    $normalizarMembresia = function ($nombre) {
        $nombre = trim((string)$nombre);
        $lower = function_exists('mb_strtolower') ? mb_strtolower($nombre, 'UTF-8') : strtolower($nombre);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $lower);
        return preg_replace('/[^a-z0-9]+/', '', $ascii !== false ? $ascii : $lower);
    };

    $membresia_nombre = $membresia["nombre"] ?? "";
    $membresia_normalizada = $normalizarMembresia($membresia_nombre);
    $es_fulmuv = $membresia_normalizada === "fulmuv";
    $es_onemuv = $membresia_normalizada === "onemuv";
    $es_basicmuv = $membresia_normalizada === "basicmuv";
    $es_sucursal = $tipo_user == "sucursal";

    echo '<input type="hidden" id="tipo_user" value="' . $tipo_user . '">';
    echo '<input type="hidden" id="membresia_nombre" value="' . htmlspecialchars($membresia_nombre, ENT_QUOTES, "UTF-8") . '">';
    echo '<input type="hidden" id="fulmuv_support_email" value="gestiones@fulmuv.com">';
    echo '<input type="hidden" id="fulmuv_support_whatsapp" value="593992744454">';

    $visible = ($es_fulmuv && !$es_sucursal) ? "" : "disabled";
    $visible_upgrade = $es_sucursal ? "disabled" : "";
    $visible_productos = ($es_basicmuv || $es_sucursal) ? "disabled" : "";
    $visible_vehiculos_eventos = ($es_basicmuv || $es_sucursal) ? "disabled" : "";
    $visible_carga_masiva = $es_fulmuv && !$es_sucursal ? "" : "disabled";
    $visible_ordenes = ($es_onemuv || $es_basicmuv) ? "disabled" : "";
}
?>

<body>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const observer = new MutationObserver(() => {
                const modal = document.querySelector(".paymentez-checkout-frame");
                if (modal) {
                    document.body.classList.add("modal-open");
                } else {
                    document.body.classList.remove("modal-open");
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>


    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <div class="container" data-layout="container">
            <script>
                var isFluid = JSON.parse(localStorage.getItem('isFluid'));
                if (isFluid) {
                    var container = document.querySelector('[data-layout]');
                    container.classList.remove('container');
                    container.classList.add('container-fluid');
                }
            </script>
            <nav class="navbar navbar-light navbar-vertical navbar-expand-xl p-1">
                <script>
                    var navbarStyle = localStorage.getItem("navbarStyle");
                    if (navbarStyle && navbarStyle !== 'transparent') {
                        document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
                    }
                </script>
                <div class="d-flex align-items-center">
                    <div class="toggle-icon-wrapper">
                        <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>
                    </div>
                    <a class="navbar-brand" href="<?php echo $dashboard ?>">
                        <div class="d-flex align-items-center py-3">
                            <!-- <img class="me-2 logo_iso" src="../img/Grupo-ISO-verde.png" alt="" width="150" /> -->
                        </div>
                    </a>

                </div>
                <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
                    <div class="navbar-vertical-content scrollbar card">
                        <ul class="navbar-nav flex-column mb-3 bg-white p-1" id="navbarVerticalNav">

                            <?php
                            foreach ($permisos as $value) {
                                if (($value["permiso"] == "Dashboard" && $value["valor"] == "true") || ($value["permiso"] == "Ordenes" && $value["valor"] == "true" && ($value["levels"] == "Empresa" || $value["levels"] == "Sucursal"))) {
                            ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php if (($menu == "dashboard" &&  $rol_id == 1) || ($menu == "empresas" &&  $rol_id == 2) || ($menu == "ordenes" &&  $rol_id == 3 && $sub_menu == "crear_orden") || ($menu == "ordenes" &&  $rol_id == 5 && $sub_menu == "crear_orden")) echo 'active' ?>" href="<?php echo $dashboard ?>" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-home"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Home</span>
                                            </div>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                            <li class="nav-item">
                                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                    <div class="col-auto navbar-vertical-label">Admin</div>
                                    <div class="col ps-0">
                                        <hr class="mb-0 navbar-vertical-divider" />
                                    </div>
                                </div>

                                <?php
                                foreach ($permisos as $value) {
                                    if ($value["permiso"] == "Usuarios" && $value["valor"] == "true") {
                                ?>
                                        <a class="nav-link <?php echo $visible;
                                                            if ($menu == "usuarios") echo 'active' ?>" href="usuarios.php" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-user"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Usuarios</span>
                                            </div>
                                        </a>
                                <?php
                                    }
                                }
                                ?>

                            </li>
                            <li class="nav-item">
                                <?php
                                foreach ($permisos as $value) {
                                    if ($value["permiso"] == "Productos" && $value["valor"] == "true") {
                                        //validacion de membresia
                                ?>
                                        <a class="nav-link dropdown-indicator <?php echo $visible_productos;
                                                                                if ($menu == "productos") echo 'active' ?>" href="#productos" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="productos">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-cubes"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Productos</span>
                                            </div>
                                        </a>
                                        <ul class="nav collapse" id="productos">
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo $visible_productos;
                                                                    if ($sub_menu == "crear_producto") echo 'active' ?>" href="crear_producto.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Crear Producto</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo $visible_productos;
                                                                    if ($sub_menu == "productos") echo 'active' ?>" href="productos.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Ver Productos</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo $visible_carga_masiva;
                                                                    if ($sub_menu == "productos") echo 'active' ?>" href="carga_masiva_productos.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Carga masiva</span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                        <a class="nav-link dropdown-indicator <?php if ($menu == "servicios") echo 'active' ?>" href="#servicios" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="servicios">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-tools"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Servicios</span>
                                            </div>
                                        </a>
                                        <ul class="nav collapse" id="servicios">
                                            <li class="nav-item">
                                                <a class="nav-link <?php if ($sub_menu == "crear_servicio") echo 'active' ?>" href="crear_servicio.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Crear Servicios</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?php if ($sub_menu == "servicios") echo 'active' ?>" href="servicios.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Ver Servicios</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo $visible_carga_masiva;
                                                                    if ($sub_menu == "servicios") echo 'active' ?>" href="carga_masiva_servicios.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Carga masiva</span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                <?php
                                    }
                                }
                                ?>

                                <?php
                                // foreach ($permisos as $value) {
                                // if ($value["permiso"] == "Productos" && $value["valor"] == "true") {
                                //validacion de membresia
                                ?>
                                <a class="nav-link dropdown-indicator <?php echo $visible_vehiculos_eventos;
                                                                        if ($menu == "vehiculos") echo 'active' ?>" href="#vehiculos" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="vehiculos">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-icon">
                                            <span class="fas fa-car-alt"></span>
                                        </span>
                                        <span class="nav-link-text ps-1">Vehículos</span>
                                    </div>
                                </a>
                                <ul class="nav collapse" id="vehiculos">
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo $visible_vehiculos_eventos;
                                                            if ($sub_menu == "crear_vehiculo") echo 'active' ?>" href="crear_vehiculo.php">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-text ps-1">Crear Vehículo</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo $visible_vehiculos_eventos;
                                                            if ($sub_menu == "vehiculos") echo 'active' ?>" href="vehiculos.php">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-text ps-1">Ver Vehículos</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo $visible_carga_masiva;
                                                            if ($sub_menu == "vehiculos") echo 'active' ?>" href="carga_masiva_vehiculos.php">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-text ps-1">Carga masiva</span>
                                            </div>
                                        </a>
                                    </li>
                                </ul>

                                <?php
                                // }
                                // }
                                ?>


                                <?php
                                foreach ($permisos as $value) {
                                    if ($value["permiso"] == "Ordenes" && $value["valor"] == "true") {
                                ?>
                                        <a class="nav-link <?php echo $visible;
                                                            if ($menu == "sucursales") echo 'active' ?>" href="sucursales.php" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-file-alt"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Ver Sucursales</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php echo $visible_ordenes;
                                                            if ($menu == "ordenes") echo 'active' ?>" href="<?php echo $visible_ordenes ? '#' : 'ordenes.php'; ?>" role="button" <?php echo $visible_ordenes ? 'aria-disabled="true"' : ''; ?>>
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-file-alt"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Ver Órdenes</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php echo $visible_vehiculos_eventos;
                                                            if ($menu == "eventos") echo 'active' ?>" href="eventos.php" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="far fa-calendar-alt"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Eventos</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php echo $visible_upgrade;
                                                            if ($menu == "upgrade") echo 'active' ?>" href="upgrade_membresia.php?id_empresa=<?php echo $id_empresa; ?>" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-rocket"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Upgrade</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php if ($menu == "empresa") echo 'active' ?>" href="editar_empresa.php?id_empresa=<?php echo $id_empresa; ?>" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-wrench"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Perfil</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php echo $visible;
                                                            if ($menu == "verificar") echo 'active' ?>" href="verificar.php?id_empresa=<?php echo $id_empresa; ?>" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-check-double"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Verificar Empresa</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php echo $visible;
                                                            if ($menu == "galeria") echo 'active' ?>" href="galeria.php?id_empresa=<?php echo $id_empresa; ?>" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-file-image"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Galería</span>
                                            </div>
                                        </a>
                                        <!-- <a class="nav-link <?php if ($menu == "empleos") echo 'active' ?>" href="empleos.php" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-suitcase"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Empleos</span>
                                            </div>
                                        </a> -->


                                        <a class="nav-link dropdown-indicator <?php echo $visible;
                                                                                if ($menu == "empleos") echo 'active' ?>" href="#empleos" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="empleos">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-suitcase"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Empleos</span>
                                            </div>
                                        </a>
                                        <ul class="nav collapse" id="empleos">
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo $visible;
                                                                    if ($sub_menu == "crear_empelo") echo 'active' ?>" href="crear_empleo.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Crear Empleo</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo $visible;
                                                                    if ($sub_menu == "empleos") echo 'active' ?>" href="empleos.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Ver Empleos</span>
                                                    </div>
                                                </a>
                                            </li>

                                        </ul>
                                        <a class="nav-link <?php if ($menu == "reportes") echo 'active' ?>" href="reportes.php" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-chart-line"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Reportes</span>
                                            </div>
                                        </a>

                                <?php
                                    }
                                }
                                ?>

                            </li>
                        </ul>
                        <div class="support-shortcuts">
                            <div class="support-shortcuts-title">Contactar a FULMUV</div>
                            <div class="support-shortcuts-text">
                                Si necesitas ayuda comercial, técnica o de facturación, escríbenos directamente.
                            </div>
                            <div class="support-shortcuts-actions">
                                <a id="btnSupportMail" class="support-shortcut-btn support-shortcut-mail" href="#">
                                    <i class="fas fa-envelope"></i>
                                    <span>Mail</span>
                                </a>
                                <a id="btnSupportWhatsapp" class="support-shortcut-btn support-shortcut-wa" href="#" target="_blank" rel="noopener">
                                    <i class="fab fa-whatsapp"></i>
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="content">
                <nav class="navbar navbar-light navbar-glass navbar-top navbar-expand d-print-none card mb-2 bg-white ms-0 me-0">

                    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>
                    <a class="navbar-brand me-1 me-sm-3" href="<?php echo $dashboard ?>">
                        <div class="d-flex align-items-center py-3">
                            <img class="me-2 logo_iso2" src="../img/Grupo-ISO-verde.png" alt="" width="150" />
                        </div>
                        <!--script>
                            var theme = localStorage.getItem("theme");
                            document.querySelector('.logo_iso').setAttribute("src", theme === 'light' ? "../img/Grupo-ISO-verde.png" : "../img/Grupo-ISO-blanco.png")
                            document.querySelector('.logo_iso2').setAttribute("src", theme === 'light' ? "../img/Grupo-ISO-verde.png" : "../img/Grupo-ISO-blanco.png")
                        </script-->
                    </a>
                    <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">
                        <li class="nav-item ps-2 pe-0">
                            <div class="theme-select-wrap">
                                <span class="theme-select-icon">
                                    <i class="fas fa-desktop" id="empresaThemeIcon"></i>
                                </span>
                                <select id="empresaThemeSelect" class="form-select form-select-sm theme-select" aria-label="Cambiar tema">
                                    <option value="light">Claro</option>
                                    <option value="dark">Oscuro</option>
                                </select>
                            </div>
                        </li>
                        <li class="nav-item dropdown"><a class="nav-link pe-0 ps-2" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="avatar avatar-xl">
                                    <!-- <img class="rounded-circle" src="../theme/public/assets/img/team/avatar.png" alt=""> -->
                                    <img class="rounded-circle img-perfil-dinamica" src="<?php echo htmlspecialchars($imagen_principal_src, ENT_QUOTES, 'UTF-8'); ?>" onerror="this.onerror=null;this.src='../img/FULMUV-LOGO-60X60.png';" alt="Perfil">
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end py-0" aria-labelledby="navbarDropdownUser">
                                <div class="bg-white dark__bg-1000 rounded-2 py-2">
                                    <a class="dropdown-item text-dark" onclick="mostrarUser()">Configuración</a>
                                    <hr>
                                    <?php
                                    if ($tipo_user != "sucursal") {
                                    ?>
                                        <a class="dropdown-item text-primary" id="btnBajaFulmuv">Darme de baja en FULMUV</a>
                                        <hr>
                                    <?php
                                    }
                                    ?>
                                    <a class="dropdown-item text-danger" href="login.php">Cerrar sesión</a>
                                    <script>
                                        function mostrarUser() {
                                            $("#alert").text("");
                                            $("#alert").append(`
                                                    <button id="modal" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bs-example-modal-lg" data-bs-whatever="@mdo" style="display:none;">Open modal for @mdo</button>
                                                    <!--  Modal content for the Large example -->
                                                    <div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title" id="myLargeModalLabel">Configuración</h4>
                                                                    <button id="modalClose" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class=" justify-content-center m-0 p-0 row">
                                                                        <div class="col-auto text-center position-relative m-0 p-0" id="imagenUserModal">

                                                                            <div class="position-absolute top-100 start-50 translate-middle">
                                                                                <input type="file" id="img" style="display:none;" accept="image/*">
                                                                                <a onclick="abrirImagen()" style="font-size:25px;color:black;" class="action-icon text-warning"> <i class="fas fa-edit"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                    </div>
                                                                    <div class="row g-2" id="contacto">
                                                                        <input type="hidden" id="idUsuarios" value="${ $("#id_principal").val() }">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label for="inputEmail4" class="form-label">Nombre de usuario</label>
                                                                            <input readonly type="text" class="form-control" id="firstName" placeholder="Username" name="firstName" value="${ $("#username_principal").val() }" disabled>
                                                                        </div>
                                                                        <div class="mb-3 col-md-12">
                                                                            <label for="inputPassword4" class="form-label">Contraseña</label>
                                                                            <input type="password" class="form-control" id="password" placeholder="contraseña">
                                                                        </div>
                                                                        <div class="col-md-12">
                                                                            <label for="inputPassword4" class="form-label">Repetir contraseña</label>
                                                                            <input type="password" class="form-control" id="password2" placeholder="repetir contraseña">
                                                                        </div>
                                                                        <div class="col-md-12 m-1 p-0">
                                                                            <input type="checkbox" id="mostrar_contrasena"/>
                                                                             <label for="mostrar_contrasena" class="form-label">Ver contraseña</label>
                                                                        </div>
                                                                        <div class="mb-3 col-md-12 text-center">
                                                                            <button type="button" class="btn btn-outline-danger rounded-pill" id="modalClose" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                                                                            <button type="button" class="btn btn-outline-primary rounded-pill" onclick="updatePassword()">Actualizar</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div><!-- /.modal-content -->
                                                        </div><!-- /.modal-dialog -->
                                                    </div><!-- /.modal -->
                                                `);

                                            var id_principal = document.getElementById("id_principal")?.value;



                                            $('#imagenUserModal').html(`<img id="imagen" class="rounded-circle text-center m-0 p-0 img-perfil-dinamica" src="${$("#imagen_principal").val() || "../img/FULMUV-LOGO-60X60.png"}" onerror="this.onerror=null;this.src='../img/FULMUV-LOGO-60X60.png';" width="150" height="150">`);

                                            // const $seleccionArchivos = document.querySelector("#img"),
                                            //     $imagenPrevisualizacion = document.querySelector("#imagen");
                                            // $seleccionArchivos.addEventListener("change", () => {
                                            //     // Los archivos seleccionados, pueden ser muchos o uno
                                            //     const archivos = $seleccionArchivos.files;
                                            //     // Si no hay archivos salimos de la función y quitamos la imagen
                                            //     if (!archivos || !archivos.length) {
                                            //         $imagenPrevisualizacion.src = "";
                                            //         return;
                                            //     }
                                            //     // Ahora tomamos el primer archivo, el cual vamos a previsualizar
                                            //     const primerArchivo = archivos[0];
                                            //     // Lo convertimos a un objeto de tipo objectURL
                                            //     const objectURL = URL.createObjectURL(primerArchivo);
                                            //     // Y a la fuente de la imagen le ponemos el objectURL
                                            //     $imagenPrevisualizacion.src = objectURL;
                                            // });


                                            $("#modal").click();

                                            $('#mostrar_contrasena').click(function() {
                                                if ($('#mostrar_contrasena').is(':checked')) {
                                                    $('#password').attr('type', 'text');
                                                    $('#password2').attr('type', 'text');
                                                } else {
                                                    $('#password').attr('type', 'password');
                                                    $('#password2').attr('type', 'password');
                                                }
                                            });

                                        }

                                        function abrirImagen() {
                                            $("#img").click();
                                        }



                                        /* ACTUALIZAR CONTRASEÑA */
                                        function updatePassword() {
                                            toastr.options.timeOut = 1500;

                                            let contraseña1 = $('#password').val();
                                            let contraseña2 = $('#password2').val();
                                            let idUsuarios = $('#idUsuarios').val();

                                            if (contraseña1 === "" || contraseña2 === "") {
                                                swal("Error", "Todos los campos son requeridos.", "error");
                                                return;
                                            }

                                            if (contraseña1 !== contraseña2) {
                                                swal("Error", "Las contraseñas no coinciden.", "error");
                                                return;
                                            }

                                            swal({
                                                title: "Confirmar cambio",
                                                text: "¿Estás seguro de que quieres actualizar la contraseña?",
                                                type: "warning",
                                                showCancelButton: true,
                                                confirmButtonText: "Sí, actualizar",
                                                cancelButtonText: "Cancelar",
                                                confirmButtonColor: "#27b394"
                                            }, function(isConfirm) {
                                                if (!isConfirm) return;

                                                $.post('../api/v1/fulmuv/usuarios/updatepass', {
                                                    pass: contraseña1,
                                                    id_usuario: idUsuarios
                                                }, function(returnedData) {
                                                    const returned = JSON.parse(returnedData);
                                                    if (returned["error"] == false) {
                                                        swal("Contraseña actualizada", returned["msg"], "success");
                                                        $('#password').val('');
                                                        $('#password2').val('');
                                                        $("#modalClose").click();
                                                    } else {
                                                        swal("Error", returned["msg"], "error");
                                                    }
                                                });
                                            });
                                        }
                                        /* ACTUALIZAR CONTRASEÑA */

                                        function applyEmpresaTheme(themeValue) {
                                            const html = document.documentElement;
                                            const body = document.body;
                                            const isDark = themeValue === 'dark';
                                            html.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
                                            body.classList.toggle('dark-theme-active', isDark);

                                            const logoDark = "../img/Grupo-ISO-blanco.png";
                                            const logoLight = "../img/Grupo-ISO-verde.png";
                                            document.querySelectorAll('.logo_iso, .logo_iso2').forEach((img) => {
                                                img.setAttribute('src', isDark ? logoDark : logoLight);
                                            });

                                            const themeSelect = document.getElementById('empresaThemeSelect');
                                            const switchIcon = document.getElementById('empresaThemeIcon');
                                            if (themeSelect) {
                                                themeSelect.value = themeValue || 'light';
                                            }
                                            if (switchIcon) {
                                                switchIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
                                            }
                                        }

                                        function construirMensajeSoporte(nombreEmpresa) {
                                            const nombreFinal = (nombreEmpresa || '').trim() || ('empresa #' + ($('#id_empresa').val() || ''));
                                            return `Hola FULMUV, soy empresa ${nombreFinal} y requiero: `;
                                        }

                                        function actualizarAccesosSoporte(nombreEmpresa) {
                                            const soporteMail = $('#fulmuv_support_email').val() || 'gestiones@fulmuv.com';
                                            const soporteWhatsapp = ($('#fulmuv_support_whatsapp').val() || '593992744454').replace(/\D/g, '');
                                            const mensaje = construirMensajeSoporte(nombreEmpresa);
                                            const asunto = `Contacto desde panel FULMUV - ${nombreEmpresa || ('empresa #' + ($('#id_empresa').val() || ''))}`;
                                            const mailtoUrl = `mailto:${soporteMail}?subject=${encodeURIComponent(asunto)}&body=${encodeURIComponent(mensaje)}`;
                                            const whatsappUrl = `https://wa.me/${soporteWhatsapp}?text=${encodeURIComponent(mensaje)}`;

                                            $('#btnSupportMail')
                                                .attr('href', mailtoUrl)
                                                .attr('data-mailto', mailtoUrl);

                                            $('#btnSupportWhatsapp')
                                                .attr('href', whatsappUrl)
                                                .attr('data-whatsapp', whatsappUrl);
                                        }

                                        document.addEventListener('DOMContentLoaded', function() {
                                            const storedTheme = localStorage.getItem('theme') || document.documentElement.getAttribute('data-bs-theme') || 'light';
                                            applyEmpresaTheme(storedTheme);
                                            actualizarAccesosSoporte('');

                                            const themeSelect = document.getElementById('empresaThemeSelect');
                                            if (themeSelect) {
                                                themeSelect.addEventListener('change', function() {
                                                    const nextTheme = this.value || 'light';
                                                    localStorage.setItem('theme', nextTheme);
                                                    applyEmpresaTheme(nextTheme);
                                                });
                                            }

                                            $(document).off('click', '#btnSupportMail').on('click', '#btnSupportMail', function(e) {
                                                e.preventDefault();
                                                const mailtoUrl = $(this).attr('data-mailto') || $(this).attr('href');
                                                if (mailtoUrl) {
                                                    window.location.href = mailtoUrl;
                                                }
                                            });

                                            $(document).off('click', '#btnSupportWhatsapp').on('click', '#btnSupportWhatsapp', function(e) {
                                                e.preventDefault();
                                                const whatsappUrl = $(this).attr('data-whatsapp') || $(this).attr('href');
                                                if (whatsappUrl) {
                                                    window.open(whatsappUrl, '_blank', 'noopener');
                                                }
                                            });

                                            const empresaId = $('#id_empresa').val();
                                            if (empresaId) {
                                                $.get(`../api/v1/fulmuv/empresas/${empresaId}`, function(returnedData) {
                                                    const returned = typeof returnedData === 'string' ? JSON.parse(returnedData) : returnedData;
                                                    if (!returned.error && returned.data) {
                                                        actualizarAccesosSoporte(returned.data.nombre || '');
                                                    }
                                                }, 'json');
                                            }
                                        });
                                    </script>
                                </div>
                            </div>
                        </li>
                    </ul>

                </nav>
