<?php
if (isset($_POST['id_usuario'])) {
    ini_set("session.cookie_lifetime", "888800");
    ini_set('session.gc_maxlifetime', "888800");
    session_start();
    unset($_SESSION["front_auth"], $_SESSION["cedula"], $_SESSION["telefono"]);
    $_SESSION["id_usuario"] = $_POST["id_usuario"];
    $_SESSION["username"] = $_POST["username"];
    $_SESSION["rol_id"] = $_POST["rol_id"];
    $_SESSION["nombres"] = $_POST["nombres"];
    $_SESSION["apellidos"] = $_POST["apellidos"];
    $_SESSION["correo"] = $_POST["correo"];
    $_SESSION["imagen"] = $_POST["imagen"];
    $_SESSION["nombre_rol_user"] = $_POST["nombre_rol_user"];
    $data = json_decode($_POST['permisos'], true);
    $data2 = json_decode($_POST['membresia'], true);
    $_SESSION["permisos"] = $data;
    $_SESSION["membresia"] = $data2;
    $_SESSION["id_empresa"] = (int)($_POST["id_empresa"] ?? 0);
    $_SESSION["tipo_user"] = $_POST["tipo_user"];
    $_SESSION["empresa_auth"] = true;

    $id_empresa_login = (int)($_POST["id_empresa"] ?? 0);
    if ($_SESSION["rol_id"] == 2 || $_SESSION["rol_id"] == 3) {
        $_SESSION["dashboard"] = "empresa_detalle.php?id_empresa=" . $id_empresa_login;
        header("Location: empresa_detalle.php?id_empresa=" . $id_empresa_login);
    } else {
        session_unset();
        session_destroy();
        header("Location: login.php");
    }
} else {
    session_start();
    session_unset();
    session_destroy();

    $acceso       = isset($_POST["acceso"])       ? $_POST["acceso"]       : '';
    $username_new = isset($_POST["username_new"]) ? $_POST["username_new"] : '';
    if ($acceso && $username_new) {
        echo '<input type="hidden" id="acceso" value="' . htmlspecialchars($acceso, ENT_QUOTES, 'UTF-8') . '">';
        echo '<input type="hidden" id="username_new" value="' . htmlspecialchars($username_new, ENT_QUOTES, 'UTF-8') . '">';
        echo htmlspecialchars($username_new, ENT_QUOTES, 'UTF-8');
    }
}
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
    <title>Fulmuv | Login</title>


    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <!-- <link rel="apple-touch-icon" sizes="180x180" href="../theme/public/assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../theme/public/assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../theme/public/assets/img/favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="../theme/public/assets/img/favicons/favicon.ico"> -->
    <link rel="manifest" href="../theme/public/assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="../theme/public/assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
    <script src="../theme/public/assets/js/config.js"></script>
    <script src="../theme/public/vendors/simplebar/simplebar.min.js"></script>



    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
    <link href="../theme/public/vendors/simplebar/simplebar.min.css" rel="stylesheet">
    <link href="../theme/public/assets/css/theme-rtl.css" rel="stylesheet" id="style-rtl">
    <link href="../theme/public/assets/css/theme.css" rel="stylesheet" id="style-default">
    <link href="../theme/public/assets/css/user-rtl.css" rel="stylesheet" id="user-style-rtl">
    <link href="../theme/public/assets/css/user.css" rel="stylesheet" id="user-style-default">
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
        html,
        body {
            width: 100%;
            min-height: 100%;
            height: 100%;
        }

        body {
            background-image:
                linear-gradient(rgba(240, 245, 249, 0.65), rgba(240, 245, 249, 0.65)),
                url('../img/PATRONES_DECORATIVOS_PNG_48.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-color: rgba(255, 255, 255, 0.82);
            z-index: -1;
        }

        .main,
        .container-fluid,
        .min-vh-100 {
            position: relative;
            z-index: 1;
        }

        .main,
        .container-fluid {
            min-height: 100vh;
        }

        .card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.10);
            backdrop-filter: blur(6px);
        }

        .tok_btn {
            padding: 10px 20px;
            margin: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
        }

        #response {
            margin-top: 20px;
        }


        #mapaEntrega {
            width: 100%;
            height: 60vh;
            min-height: 320px;
            border-radius: .5rem;
        }

        .map-wrapper {
            position: relative;
        }

        /* Ubica el input arriba a la derecha del mapa */
        .map-search {
            position: absolute;
            top: 0px;
            right: 110px;
            /* <- antes estaba left */
            left: auto;
            /* <- importante para soltar el anclaje izquierdo */
            z-index: 2000;
        }

        /* Para que el autocomplete siempre quede visible */
        .pac-container {
            z-index: 20000 !important;
        }

        /* (Opcional) en pantallas pequeñas que no quede cortado */
        @media (max-width: 576px) {
            .map-search {
                left: 8px;
                right: 8px;
            }

            /* se centra con margen a ambos lados */
            #buscarDireccion {
                width: 100%;
            }
        }
    </style>
</head>


<body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <div class="container-fluid">
            <div class="row min-vh-100 flex-center g-0">
                <div class="col-lg-8 col-xxl-5 py-3">
                    <div class="col-sm-12 col-md-8 px-sm-0 align-self-center mx-auto py-5">
                        <div class="row justify-content-center g-0">
                            <div class="col-lg-12 col-xl-12 col-xxl-12">
                                <div class="card">
                                    <div class="card-header text-center" style="background: #004E60;">
                                        <!-- <img src="../img/Grupo-ISO-blanco.png" width="250"> -->
                                        <img src="../img/FULMUV LOGO-03.png" width="250">
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row flex-between-center">
                                            <div class="col-auto">
                                                <h3>Iniciar Sesión</h3>
                                            </div>
                                        </div>
                                        <form id="formlogin" action="login.php" method="POST">
                                            <div class="mb-3">
                                                <label class="form-label" for="username">Usuario</label>
                                                <input class="form-control" id="username" type="email" placeholder="Usuario" />
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <label class="form-label" for="password">Contraseña</label>
                                                </div>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="password" placeholder="Contraseña" required>
                                                    <button class="btn d-block text-white" style="background-color: #004E60; color: #FFF" type="button" id="togglePassword" aria-label="Mostrar contraseña">
                                                        <i class="fas fa-eye" id="iconEye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="row flex-between-center">
                                            <!-- <div class="col-auto">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" id="basic-checkbox" checked="checked">
                                                    <label class="form-check-label mb-0" for="basic-checkbox">Remember me</label
                                                </div>
                                            </div> -->
                                            <div class="col-auto">
                                                <a class="fs--1" href="recuperar.php">¿Olvidó su contraseña?</a>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button class="btn d-block w-100 mt-3 text-white" type="submit" name="submit" onclick="logear()" style="background: #004E60;">Login</button>
                                        </div>
                                        <div class="text-center mt-3">
                                            <span>¿Aún no tienes cuenta? </span>
                                            <a href="crear_empresa.php" class="text-decoration-underline fw-bold"> Regístrate</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal fade" id="modal-pago" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg mt-6" role="document">
                    <div class="modal-content border-0">
                        <div class="position-absolute top-0 end-0 mt-3 me-3 z-index-1"><button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body p-0">
                            <div class="bg-light rounded-top-lg py-3 ps-4 pe-6">
                                <h4 class="mb-1" id="">Renovar membresía FULMUV</h4>
                            </div>
                            <div class="p-4">

                                <div class="col align-items-center">

                                    <input value="" hidden id="tipo">
                                    <input value="" hidden id="id_membresia_producto">

                                    <!--1. Create an element to contain the dynamic form.-->
                                    <div id='payment_example_div'>
                                        <div id='tokenize_example'></div>
                                        <div class="border-bottom border-dashed my-3"></div>
                                        <div class="fs-12 fw-semi-bold">Total: <span class="text-primary" id="totalPago"></span></div>

                                        <div id="tokenize_response"></div>

                                        <div class="row g-3 mt-2">
                                            <div class="col-12 col-md-6" id="wrapperTipo" style="display:none;">
                                                <label for="selectTipoDiferido" class="form-label fw-semi-bold">Forma de pago</label>
                                                <select id="selectTipoDiferido" class="form-select" onchange="onTipoChange(this.value)">
                                                    <!-- options se llenan dinámicamente según el plan -->
                                                </select>
                                                <div class="form-text" id="ayudaTipo"></div>
                                            </div>

                                            <div class="col-12 col-md-6" id="wrapperMeses" style="display:none;">
                                                <label for="selectMeses" class="form-label fw-semi-bold">Meses</label>
                                                <select id="selectMeses" class="form-select" disabled onchange="onMesesChange(this.value)">
                                                    <!-- options se llenan dinámicamente según el tipo -->
                                                </select>
                                                <div class="form-text" id="ayudaMeses"></div>
                                            </div>
                                        </div>

                                        <div class="mt-2" id="cuotaBox" style="display:none;">
                                            <small class="text-700">Cuota estimada: <span id="cuotaEstimada">$0.00</span> / mes</small>
                                        </div>


                                        <div class="form-check mt-2">
                                            <input class="form-check-input me-2" id="checkTerminoCondicionesPago" type="checkbox" value="">
                                            <label class="form-check-label mb-0" for="checkTerminoCondicionesPago">
                                                Acepto las Condiciones de Uso del Servicio de Pago en Línea de FULMUV y autorizo el cargo recurrente del plan seleccionado a través de la pasarela NUVEI. Entiendo que la renovación puede cancelarse desde mi perfil de vendedor, y confirmo que soy titular o estoy autorizado para usar este medio de pago.
                                                <p><a href="../documentos/4_Condiciones Pago en Línea de FULMUV.pdf" target="_blank" class="fs-10 fw-bold">
                                                        Ver Condiciones de Pago en Línea
                                                    </a>
                                                </p>
                                            </label>
                                        </div>
                                        <button id='tokenize_btn' class='tok_btn'>Pagar</button>
                                        <p class="fs--1 mt-3 mb-0">Al hacer clic en el botón <strong>Pagar</strong>, se procederá a realizar el pago y registrar la membresia.</p>

                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer justify-contend-end">
                                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->


    <!-- ===============================================-->
    <!--    JavaScripts-->
    <!-- ===============================================-->

    <script src="../theme/public/vendors/popper/popper.min.js"></script>
    <script src="../theme/public/vendors/bootstrap/bootstrap.min.js"></script>
    <script src="../theme/public/vendors/anchorjs/anchor.min.js"></script>
    <script src="../theme/public/vendors/is/is.min.js"></script>
    <script src="../theme/public/vendors/fontawesome/all.min.js"></script>
    <script src="../theme/public/vendors/lodash/lodash.min.js"></script>
    <script src="../theme/public/vendors/list.js/list.min.js"></script>
    <script src="../theme/public/assets/js/theme.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="../theme/public/vendors/sweetalert/sweetalert.css" rel="stylesheet">
    <script src="../theme/public/vendors/sweetalert/sweetalert.min.js"></script>

    <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>

    <!-- Conexión API js -->
    <script src="js/login.js?v1.0.46"></script>

    <!-- Alerts js -->
    <script src="js/alerts.js"></script>

    <div id="alert">

    </div>

</body>

</html>
