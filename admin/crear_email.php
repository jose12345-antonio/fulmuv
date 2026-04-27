<?php
$menu = "email";
$sub_menu = "crear_email";
$hide_filter_bar = true;
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "E-mail" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<title>Crear E-mail</title>
<div class="card">
    <div class="card-header bg-body-tertiary">
        <h5 class="mb-0">Nuevo correo</h5>
    </div>
    <div class="card-body p-0">
        <div class="border border-top-0 border-200">
            <input class="form-control border-0 rounded-0 outline-none px-x1" id="email-subject" type="text" maxlength="300" aria-describedby="email-subject" placeholder="Asunto" />
        </div>
        <div class="border border-y-0 border-200">
            <input class="form-control border-0 rounded-0 outline-none px-x1" id="email-descripcion" type="text" maxlength="300" aria-describedby="email-descripcion" placeholder="Descripción">
        </div>
        <div class="min-vh-50 email-compose-textarea">
            <textarea class="tinymce d-none" id="email-body" data-tinymce="data-tinymce" name="content"></textarea>
        </div>
    </div>
    <div class="card-footer border-top border-200">

        <button class="btn btn-iso w-100 px-5 me-2" type="button" onclick="guarda()">Guardar</button>

    </div>
</div>

<script src="js/crear_email.js"></script>

<?php
require 'includes/footer.php';
?>