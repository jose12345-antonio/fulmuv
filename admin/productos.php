<?php
$menu = "productos";
$sub_menu = "productos";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<title>Productos</title>

<div class="card mb-3">

    <div class="card-header">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center">
                <div class="col-md-auto">
                    <h5 class="mb-2 mb-md-0">Productos</h5>
                </div>
                
            </div>
            <div class="row flex-between-center">
                <div class="col-auto">
                    <a class="btn btn-primary btn-sm" type="button" href="crear_producto.php">
                        <span class="fas fa-plus me-1"></span>
                        <span class="d-none d-sm-inline-block">Crear</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- <div class="row flex-between-center">
            <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Productos</h5>
            </div>
            <div class="col-8 col-sm-auto text-end ps-2">
                <div id="table-customers-replace-element">
                    <a class="btn btn-falcon-default btn-sm" type="button" href="crear_producto.php">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a>
                </div>
            </div>
        </div> -->
    </div>
    <div class="card-body p-0">
        <div class="falcon-data-table" id="tabla_contenido">
            
        </div>
    </div>
</div>

<!-- Conexión API js -->
<script src="js/productos.js?0"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>