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

        .content {
            padding-top: 20px;
        }
    </style>
</head>

<?php
if (!isset($_SESSION["id_usuario"])) {
    echo "<script>window.location.href = 'login.php'</script>";
} else {
    $correo = $_SESSION["correo"];
    $id_usuario = $_SESSION["id_usuario"];
    $nombres = $_SESSION["nombres"];
    $apellidos = $_SESSION["apellidos"];
    $username = $_SESSION["username"];
    $rol_id = $_SESSION["rol_id"];
    $imagen = $_SESSION["imagen"];
    $nombre_rol_user = $_SESSION["nombre_rol_user"];
    $permisos = $_SESSION["permisos"];
    $id_empresa = $_SESSION["id_empresa"];
    $dashboard = $_SESSION["dashboard"];
    $membresia = $_SESSION["membresia"];
    $tipo_user = $_SESSION["tipo_user"];

    if ($permisos["error"] == true) {
        echo "<script>window.location.href = 'login.php'</script>";
    }
    echo '<input type="hidden" id="id_principal" value="' . $id_usuario . '">';
    echo '<input type="hidden" id="id_rol_principal" value="' . $rol_id . '">';
    echo '<input type="hidden" id="nombre_rol_principal" value="' . $nombre_rol_user . '">';
    echo '<input type="hidden" id="username_principal" value="' . $username . '">';
    echo '<input type="hidden" id="id_empresa" value="' . $id_empresa . '">';
    echo '<input type="hidden" id="imagen_principal" value="' . $imagen . '">';
    echo '<input type="hidden" id="id_carrito" value="carrito_' . $id_usuario . '_' . $id_empresa . '">';
    echo '<input type="hidden" id="id_carrito" value="carrito_' . $id_usuario . '_' . $id_empresa . '">';
    echo '<input type="hidden" id="tipo_user" value="' . $tipo_user . '">';

    if ($membresia["nombre"] == "FULMUV") {
        $visible = "";
    } else {
        $visible = "disabled";
    }

    if ($tipo_user == "sucursal") {
        $visible = "disabled";
    }
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
                                        <a class="nav-link dropdown-indicator <?php if ($menu == "productos") echo 'active' ?>" href="#productos" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="productos">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-cubes"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Productos</span>
                                            </div>
                                        </a>
                                        <ul class="nav collapse" id="productos">
                                            <li class="nav-item">
                                                <a class="nav-link <?php if ($sub_menu == "crear_producto") echo 'active' ?>" href="crear_producto.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Crear Producto</span>
                                                    </div>
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link <?php if ($sub_menu == "productos") echo 'active' ?>" href="productos.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Productos</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="<?php echo $visible; ?> nav-link <?php if ($sub_menu == "productos") echo 'active' ?>" href="carga_masiva_productos.php">
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
                                                <a class="<?php echo $visible; ?> nav-link <?php if ($sub_menu == "servicios") echo 'active' ?>" href="carga_masiva_servicios.php">
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
                                <a class="nav-link dropdown-indicator <?php if ($menu == "vehiculos") echo 'active' ?>" href="#vehiculos" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="vehiculos">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-icon">
                                            <span class="fas fa-car-alt"></span>
                                        </span>
                                        <span class="nav-link-text ps-1">Vehículos</span>
                                    </div>
                                </a>
                                <ul class="nav collapse" id="vehiculos">
                                    <li class="nav-item">
                                        <a class="nav-link <?php if ($sub_menu == "crear_vehiculo") echo 'active' ?>" href="crear_vehiculo.php">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-text ps-1">Crear Vehículo</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php if ($sub_menu == "vehiculos") echo 'active' ?>" href="vehiculos.php">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-text ps-1">Ver Vehículos</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="<?php echo $visible; ?> nav-link <?php if ($sub_menu == "vehiculos") echo 'active' ?>" href="carga_masiva_vehiculos.php">
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
                                        <a class="nav-link <?php if ($menu == "ordenes") echo 'active' ?>" href="ordenes.php" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-file-alt"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Ver Órdenes</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php if ($menu == "eventos") echo 'active' ?>" href="eventos.php" role="button">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="far fa-calendar-alt"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Eventos</span>
                                            </div>
                                        </a>
                                        <a class="nav-link <?php if ($menu == "upgrade") echo 'active' ?>" href="upgrade_membresia.php?id_empresa=<?php echo $id_empresa; ?>" role="button">
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


                                        <a class="nav-link dropdown-indicator <?php if ($menu == "empleos") echo 'active' ?>" href="#empleos" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="empleos">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-suitcase"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Empleos</span>
                                            </div>
                                        </a>
                                        <ul class="nav collapse" id="empleos">
                                            <li class="nav-item">
                                                <a class="nav-link <?php if ($sub_menu == "crear_empelo") echo 'active' ?>" href="crear_empleo.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Crear Empleo</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?php if ($sub_menu == "empleos") echo 'active' ?>" href="empleos.php">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Ver Empleos</span>
                                                    </div>
                                                </a>
                                            </li>

                                        </ul>

                                <?php
                                    }
                                }
                                ?>

                            </li>
                        </ul>
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
                            <div class="dropdown theme-control-dropdown"><a class="nav-link d-flex align-items-center dropdown-toggle fa-icon-wait fs-9 pe-1 py-0" href="#" role="button" id="themeSwitchDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-sun fs-7" data-fa-transform="shrink-2" data-theme-dropdown-toggle-icon="light"></span><span class="fas fa-moon fs-7" data-fa-transform="shrink-3" data-theme-dropdown-toggle-icon="dark"></span><span class="fas fa-adjust fs-7" data-fa-transform="shrink-2" data-theme-dropdown-toggle-icon="auto"></span></a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-caret border py-0 mt-3" aria-labelledby="themeSwitchDropdown">
                                    <div class="bg-white dark__bg-1000 rounded-2 py-2">
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="light" data-theme-control="theme"><span class="fas fa-sun"></span>Light<span class="fas fa-check dropdown-check-icon ms-auto text-600"></span></button>
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="dark" data-theme-control="theme"><span class="fas fa-moon" data-fa-transform=""></span>Dark<span class="fas fa-check dropdown-check-icon ms-auto text-600"></span></button>
                                        <!-- <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="auto" data-theme-control="theme"><span class="fas fa-adjust" data-fa-transform=""></span>Auto<span class="fas fa-check dropdown-check-icon ms-auto text-600"></span></button> -->
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item dropdown"><a class="nav-link pe-0 ps-2" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="avatar avatar-xl">
                                    <!-- <img class="rounded-circle" src="../theme/public/assets/img/team/avatar.png" alt=""> -->
                                    <img class="rounded-circle img-perfil-dinamica" alt="Cargando.....">
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



                                            $('#imagenUserModal').html(`<img id="imagen" class="rounded-circle text-center m-0 p-0 img-perfil-dinamica" width="150" height="150">`);

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

                                            if ($('#img')[0].files[0] == undefined) {
                                                swal({
                                                    title: "Advertencia",
                                                    text: "¿Estás seguro que quieres cambiar la contraseña?",
                                                    type: "warning",
                                                    showCancelButton: true,
                                                    confirmButtonColor: "#27b394",
                                                    confirmButtonText: "Yes",
                                                    cancelButtonText: 'No',
                                                    closeOnConfirm: true
                                                }, function() {

                                                    let contraseña1 = $('#password').val();
                                                    let contraseña2 = $('#password2').val();
                                                    let idUsuarios = $('#idUsuarios').val();

                                                    if (contraseña1 == "" || contraseña2 == "") {
                                                        SweetAlert("error", "Todos los campos son requeridos.");
                                                    } else if (contraseña1 != contraseña2) {
                                                        SweetAlert("error", "Error, las contraseñas no coinciden");
                                                    } else {

                                                        $.post('../api/v1/fulmuv/usuarios/updatepass', {
                                                            pass: contraseña1,
                                                            id_usuario: idUsuarios
                                                        }, function(returnedData) {
                                                            //console.log(returnedData);
                                                            returned = JSON.parse(returnedData);
                                                            //console.log(returned);
                                                            if (returned["error"] == false) {
                                                                //SweetAlert("success", "Updated password");
                                                                toastr.success("Contraseña actualizada");
                                                                $("#modalClose").click();
                                                                // location.reload();
                                                            } else {
                                                                //SweetAlert("error", returned["msg"]);
                                                                toastr.warning(returned["msg"]);
                                                            }
                                                        });

                                                    }
                                                });
                                            } else {

                                                var fileSize = Math.round(($('#img')[0].files[0].size / 1024));
                                                const formData2 = new FormData();
                                                formData2.append("archivos[]", $('#img')[0].files[0]);

                                                $.ajax({
                                                    type: 'POST',
                                                    data: formData2,
                                                    url: 'cargar_imagen.php',
                                                    cache: false,
                                                    contentType: false,
                                                    processData: false,
                                                    success: function(returnedImagen2) {
                                                        console.log(returnedImagen2);
                                                        if (returnedImagen2["response"] == "success") {

                                                            let imagenUser = returnedImagen2["data"]["img"];
                                                            let idUsuarios = $('#idUsuarios').val();

                                                            $.post('../api/v1/fulmuv/usuarios/updateImagen', {
                                                                imagen: imagenUser,
                                                                id_usuario: idUsuarios
                                                            }, function(returnedData) {
                                                                returned = JSON.parse(returnedData);
                                                                if (returned["error"] == false) {
                                                                    //SweetAlert("success", "Updated imagen");
                                                                    toastr.success("Contraseña actualizada");
                                                                    // $_SESSION["imagen"] = imagenUser;
                                                                    // $("#imagen_principal").val(imagenUser)
                                                                    $("#modalClose").click();

                                                                    location.reload();
                                                                } else {
                                                                    //SweetAlert("error", returned["msg"]);
                                                                    toastr.warning(returned["msg"]);
                                                                }
                                                            });


                                                        } else {
                                                            SweetAlert("error", "La imagen no es válida");
                                                        }
                                                    }
                                                });
                                            }

                                        }
                                        /* ACTUALIZAR CONTRASEÑA */
                                    </script>
                                </div>
                            </div>
                        </li>
                    </ul>

                </nav>