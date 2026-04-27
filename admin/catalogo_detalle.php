<?php
$menu = "catalogos";
$hide_filter_bar = true;
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Catalogos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
$id_catalogo = $_GET["id_catalogo"];
echo "<input type='hidden' id='id_catalogo' value='$id_catalogo' >";
?>
<title>Catalogo</title>
<div class="row g-0">
    <div class="col-lg-12 pe-lg-2">
        <div class="card mb-3">
            <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                <h6 class="mb-0">Información</h6>
                <div>
                    <a class="btn btn-primary" type="button" href="catalogos.php">
                        <span class="fas fa-arrow-alt-circle-left" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Cancelar</span>
                    </a>
                    <button class="btn btn-iso" type="button" onclick="updateCatalogo()">
                        <span class="fas fa-save" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Guardar</span>
                    </button>
                </div>


            </div>
            <div class="card-body">
                <div class="row gx-2">
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="nombre">Nombre:</label>
                        <input class="form-control" id="nombre" type="text" disabled />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="descripcion">Descripción: </label>
                        <textarea class="form-control" id="descripcion" type="text" rows="1"></textarea>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="lista_empresas">Empresa:</label>
                        <select class="form-select selectpicker" id="lista_empresas" disabled>
                        </select>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="lista_sucursales">Sucursal:</label>
                        <select class="form-select selectpicker" id="lista_sucursales" disabled>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-body-tertiary">
                <div class="row">
                    <div class="col-lg-6">
                        <select class="form-select selectpicker" id="lista_productos" aria-label=" Seleccione producto">
                            <option value="-1">Seleccione producto</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex justify-content-center align-items-center">
                        <a onclick="addProducto()" class="btn btn-primary">Agregar</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0 bg-body-tertiary">
                <div class="row p-3" id="productos_agregados">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/catalogo_detalles.js"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>