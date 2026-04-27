<?php
$menu = "email";
$sub_menu = "default";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "E-mail" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<title>Default</title>

<div class="card mb-3">
    <div class="card-header text-center">
        <div>
            <h3 class="text-primary mb-1">Configurar correos por defecto</h3>
            <p>Estos usuarios siempre recibirán correos del sistema.</p>
        </div>
    </div>
</div>

<div class="row" id="contenedor">

</div>


<!-- MODAL GUARDAR -->
<div class="modal fade" id="modal_add" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content border-0">
            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="rounded-top-3 py-3 ps-4 pe-6 bg-body-tertiary">
                    <h4 class="mb-1" id="modalExampleDemoLabel">Agregar nuevo E-mail</h4>
                </div>
                <div class="p-4 pb-0">
                    <div class="col-lg-12 mb-3">
                        <label for="valuePrice" class="form-label">User</label>
                        <select id="userNew" class="form-select form-select-sm">
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close">Cerrar</button>
                <button type="button" class="btn btn-iso" onclick="agregar()">Guardar</button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL GUARDAR -->

<script src="js/default_emails.js"></script>
<?php
require 'includes/footer.php';
?>