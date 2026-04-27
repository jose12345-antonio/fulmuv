<?php
session_start();

// echo "ESTE ES EL ID_USUARIO ".$_SESSION["id_usuario"];
if (isset($_POST['id_usuario'])) {
    ini_set("session.cookie_lifetime", "888800");
    ini_set('session.gc_maxlifetime', "888800");

    unset($_SESSION["empresa_auth"], $_SESSION["id_empresa"], $_SESSION["tipo_user"], $_SESSION["dashboard"], $_SESSION["permisos"], $_SESSION["membresia"], $_SESSION["rol_id"], $_SESSION["username"], $_SESSION["imagen"], $_SESSION["nombre_rol_user"]);
    $_SESSION["id_usuario"] = $_POST["id_usuario"];
    $_SESSION["correo"] = $_POST["correo"];
    $_SESSION["nombres"] = $_POST["nombres"];
    $_SESSION["apellidos"] = $_POST["apellidos"];
    $_SESSION["cedula"] = $_POST["cedula"];
    $_SESSION["telefono"] = $_POST["telefono"];
    $_SESSION["front_auth"] = true;

    header("Location: lista_pedidos.php");
    exit();
}

if (isset($_SESSION["id_usuario"]) && (
    (isset($_SESSION["front_auth"]) && $_SESSION["front_auth"] === true) ||
    empty($_SESSION["id_empresa"])
)) {
    header("Location: lista_pedidos.php");
    exit();
}
?>
<link rel="canonical" href="https://fulmuv.com/login.php">

<?php include 'includes/header.php'; ?>
<style>
    .auth-switch-link {
        color: #004E60;
        font-weight: 700;
        text-decoration: none;
    }

    .auth-switch-link:hover {
        text-decoration: underline;
    }

    .auth-panel.d-none {
        display: none !important;
    }
</style>
<div class="container mt-2">
    <div class="row d-flex justify-content-center align-items-center">
        <div class="col-lg-6">
            <div class="card shadow-lg">
                <div class="card-header text-center bg-teal text-white" style="background-color: #004E60;">
                    <img src="img/FULMUV LOGO-13.png" alt="FULMUV" width="250">
                </div>
                <div class="card-body">
                    <div id="loginPanel" class="auth-panel">
                        <h4 class="card-title text-center mb-4">Iniciar Sesión</h4>
                        <form id="formlogin" action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control" id="correo" placeholder="Usuario" required>
                            </div>

                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>

                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" placeholder="Contraseña" required>
                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-toggle-target="password" data-toggle-icon="iconEye" aria-label="Mostrar contraseña">
                                        <i class="fi-rs-eye" id="iconEye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <a href="recuperar_contrasena.php" class="text-primary">¿Olvidó su contraseña?</a>
                            </div>
                        </form>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn text-white" style="background-color: #004E60;" onclick="logear()">Login</button>
                        </div>
                        <div class="text-center">
                            <span>¿No tienes cuenta?</span>
                            <a href="#" class="auth-switch-link" id="showRegisterPanel">Regístrate</a>
                        </div>
                    </div>

                    <div id="registerPanel" class="auth-panel d-none">
                        <h4 class="card-title text-center mb-4">Registro de Cliente</h4>
                        <form id="formRegistroCliente" action="javascript:void(0);" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="registro_nombres" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control text-capitalize-input" id="registro_nombres" placeholder="Ingrese su nombre completo" required>
                            </div>
                            <div class="mb-3">
                                <label for="registro_correo" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="registro_correo" placeholder="correo@ejemplo.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="registro_telefono" class="form-label">Teléfono <small class="text-muted">(opcional)</small></label>
                                <input type="text" class="form-control" id="registro_telefono" placeholder="0999999999">
                            </div>
                            <div class="mb-3">
                                <label for="registro_password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="registro_password" placeholder="Contraseña" required>
                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-toggle-target="registro_password" data-toggle-icon="iconEyeRegistro" aria-label="Mostrar contraseña">
                                        <i class="fi-rs-eye" id="iconEyeRegistro"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="registro_password_repeat" class="form-label">Repetir contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="registro_password_repeat" placeholder="Repita la contraseña" required>
                                    <button class="btn btn-outline-secondary password-toggle" type="button" data-toggle-target="registro_password_repeat" data-toggle-icon="iconEyeRegistroRepeat" aria-label="Mostrar contraseña">
                                        <i class="fi-rs-eye" id="iconEyeRegistroRepeat"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="d-grid mb-3">
                            <button type="button" class="btn text-white" style="background-color: #004E60;" id="btnRegistrarCliente">Registrarme</button>
                        </div>
                        <div class="text-center">
                            <span>¿Ya tienes cuenta?</span>
                            <a href="#" class="auth-switch-link" id="showLoginPanel">Inicia sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="js/login.js?v1.0.0.0.0.0.6"></script>
