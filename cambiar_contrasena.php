<?php
session_start();
include 'includes/header.php';
?>
<link rel="canonical" href="https://fulmuv.com/cambiar_contrasena.php">

<div class="container mt-4">
    <div class="row d-flex justify-content-center align-items-center">
        <divb class="col-lg-8">
            <h4 class="mb-3">Cambiar Contraseña</h4>
            <form id="formCambiarPassword" autocomplete="off" onsubmit="cambiarcontrasena(); return false;">
                <input type="hidden" id="id_usuario" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">Nueva contraseña</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Ingrese la contraseña"
                        required>
                </div>

                <div class="mb-3">
                    <label for="RepeatPassword" class="form-label">Confirmar nueva contraseña</label>
                    <input
                        type="password"
                        class="form-control"
                        id="RepeatPassword"
                        name="RepeatPassword"
                        placeholder="Repite la contraseña"
                        required>
                </div>

                <button id="btnGuardar" type="submit" class="btn btn-primary w-100">
                    Guardar contraseña
                </button>
            </form>
        </divb>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
<script src="js/cambiar_contrasena.js?v1.0.0.0.4"></script>