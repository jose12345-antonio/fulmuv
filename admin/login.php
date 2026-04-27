<?php
if (isset($_POST['id_usuario'])) {
    ini_set("session.cookie_lifetime", "888800");
    ini_set('session.gc_maxlifetime', "888800");
    session_start();
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
    $_SESSION["id_empresa"] = $_POST["id_empresa"];
    if ($_SESSION["rol_id"] == 1) {
        $_SESSION["dashboard"] = "home.php";
        header("Location: home.php");
    }else{
        session_unset();
        session_destroy();
        header("Location: login.php");
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
                                        <img src="../img/FULMUV-BLANCO.png" width="250">
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
                                                <input class="form-control" id="password" type="password" placeholder="Contraseña" />
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


    <!-- Conexión API js -->
    <script src="js/login.js?v1.0.0.0.0.0.0.1"></script>

    <!-- Alerts js -->
    <script src="js/alerts.js"></script>

    <div id="alert">

    </div>

</body>

</html>