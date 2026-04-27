<?php
session_start();

// echo "ESTE ES EL ID_USUARIO ".$_SESSION["id_usuario"];
if (isset($_POST['id_usuario'])) {
    ini_set("session.cookie_lifetime", "888800");
    ini_set('session.gc_maxlifetime', "888800");

    $_SESSION["id_usuario"] = $_POST["id_usuario"];
    $_SESSION["correo"] = $_POST["correo"];
    $_SESSION["nombres"] = $_POST["nombres"];
    $_SESSION["apellidos"] = $_POST["apellidos"];
    $_SESSION["cedula"] = $_POST["cedula"];
    $_SESSION["telefono"] = $_POST["telefono"];

    header("Location: lista_pedidos.php");
    exit();
}

if (isset($_SESSION["id_usuario"])) {
    header("Location: lista_pedidos.php");
    exit();
}
?>
<link rel="canonical" href="https://fulmuv.com/recuperar_contrasena.php">

<?php include 'includes/header.php'; ?>

<div class="container mt-2">
    <div class="row d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">

                <div class="card-header text-center text-white py-4" style="background-color: #004E60;">
                    <img src="img/FULMUV LOGO-13.png" alt="FULMUV" width="250">
                </div>

                <div class="card-body p-4 p-md-5">
                    <h4 class="card-title text-center mb-2">Recupera tu contraseña</h4>

                    <p class="text-center text-muted mb-4" style="font-size: 0.95rem;">
                        Ingresa tu correo electrónico y recibirás un mensaje con una nueva contraseña para acceder a tu cuenta.
                    </p>

                    <form id="formlogin" action="javascript:void(0);" method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="Ingresa tu correo electrónico"
                                required>
                        </div>

                        <div class="d-grid mt-4">
                            <button
                                id="btnRecuperar"
                                type="button"
                                class="btn text-white py-2"
                                style="background-color:#004E60;"
                                onclick="enviarCorreo()">
                                Recuperar contraseña
                            </button>
                        </div>

                        <small class="d-block text-center text-muted mt-3">
                            Revisa también tu bandeja de “Spam” o “No deseado”.
                        </small>

                        <!-- Login link -->
                        <div class="text-center mt-3">
                            <span class="text-muted">Si deseas iniciar sesión, </span>
                            <a href="login.php" class="fw-bold" style="color:#004E60; text-decoration:none;">
                                LOGIN
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="js/recuperar_contrasena.js?v1.0.0.0.0.0.5"></script>
