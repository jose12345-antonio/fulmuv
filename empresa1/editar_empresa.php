<?php
$menu = "empresas";
$sub_menu = "empresas";
require 'includes/header.php';
// foreach ($permisos as $value) {
//     if ($value["permiso"] == "Empresas" && $value["valor"] == "false") {
//         echo "<script>window.location.href = '" . $dashboard . "'</script>";
//     }
// }
?>
<title>Editar empresa</title>
<div class="card mb-3">

    <div class="card-body">
        <!-- Datos personales y de contacto -->
        <h5 class="text-secondary">Datos personales y de contacto</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Nombre Completo Empresa</label>
                <input type="text" class="form-control" id="nombre_completo">
            </div>
            <div class="col-md-6">
                <label class="form-label">Nombre Titular</label>
                <input type="text" class="form-control" id="nombre_titular">
            </div>
            <div id="div_inputs">

            </div>
            <div class="col-md-6">
                <label class="form-label">Provincia</label>
                <select class="form-control" id="provincia" onchange="cargarCantones(this.value)">
                    <option value="">Seleccione provincia</option>
                    <option value="Azuay">Azuay</option>
                    <option value="Bolívar">Bolívar</option>
                    <option value="Cañar">Cañar</option>
                    <option value="Carchi">Carchi</option>
                    <option value="Cotopaxi">Cotopaxi</option>
                    <option value="Chimborazo">Chimborazo</option>
                    <option value="El Oro">El Oro</option>
                    <option value="Esmeraldas">Esmeraldas</option>
                    <option value="Guayas">Guayas</option>
                    <option value="Imbabura">Imbabura</option>
                    <option value="Loja">Loja</option>
                    <option value="Los Ríos">Los Ríos</option>
                    <option value="Manabí">Manabí</option>
                    <option value="Morona Santiago">Morona Santiago</option>
                    <option value="Napo">Napo</option>
                    <option value="Pastaza">Pastaza</option>
                    <option value="Pichincha">Pichincha</option>
                    <option value="Tungurahua">Tungurahua</option>
                    <option value="Zamora Chinchipe">Zamora Chinchipe</option>
                    <option value="Galápagos">Galápagos</option>
                    <option value="Sucumbíos">Sucumbíos</option>
                    <option value="Orellana">Orellana</option>
                    <option value="Santo Domingo de los Tsáchilas">Santo Domingo de los Tsáchilas</option>
                    <option value="Santa Elena">Santa Elena</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Cantón</label>
                <select class="form-select" id="canton"></select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Calle Principal</label>
                <input class="form-control" id="calle_principal" type="text" placeholder="Calle Principal">
            </div>
            <div class="col-md-12">
                <label class="form-label">Calle Secundaria</label>
                <input class="form-control" id="calle_secundaria" type="text" placeholder="Calle Secundaria">
            </div>
            <div class="col-md-12">
                <label class="form-label"># de Bien Inmueble</label>
                <input class="form-control" id="bien_inmueble" type="text" placeholder="# de Bien Inmueble">
            </div>
            <div class="col-md-6">
                <label class="form-label">Celular Whastapp</label>
                <input type="text" class="form-control mb-2" id="whatsapp_contacto" placeholder="Número de celular">
            </div>
            <div class="col-md-6">
                <label class="form-label">Celular Llamadas</label>
                <input type="text" class="form-control mb-2" id="telefono_contacto" placeholder="Número de llamadas">
            </div>
            <div class="col-md-12">
                <label class="form-label">Correo</label>
                <input type="email" class="form-control" id="correo" placeholder="Correo electrónico">
            </div>
        </div>

        <button class="btn btn-primary w-100" onclick="saveEmpresaEditar()">Guardar</button>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/editar_empresa.js?v1.0.0"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>