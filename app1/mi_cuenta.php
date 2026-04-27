<?php
include 'includes/header.php';

if (isset($_GET["id_usuario"])) {

    echo '<input type="hidden" class="form-control" name="id_usuario" id="id_usuario" value="' . $_GET["id_usuario"] . '" required>';
}
?>
<link rel="canonical" href="https://fulmuv.com/mi_cuenta.php">

<div class="container mt-4">
    <h4>Mis Datos Personales</h4>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="nombres" class="form-label">Nombres</label>
                <input type="text" class="form-control" name="nombres" id="nombres" placeholder="Ingrese los nombres" required>
            </div>
        </div>
      
        <div class="col-md-6">
            <div class="mb-3">
                <label for="cedula" class="form-label">Cédula/RUC</label>
                <input type="text" class="form-control" name="cedula" id="cedula" placeholder="Ingrese la cédula" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono" placeholder="Ingrese el número de teléfono" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" name="correo" id="correo" placeholder="Ingrese el correo electrónico" required disabled>
            </div>
        </div>
    </div>


    <button id="btnActualizar" type="button" class="btn btn-primary fw-bold" onclick="actualizarPerfil()">
        Actualizar Datos
    </button>
</div>

<?php include 'includes/footer.php'; ?>


<script src="js/mi_cuenta.js?v1.0.0.0.4"></script>