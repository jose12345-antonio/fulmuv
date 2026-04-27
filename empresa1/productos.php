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


<style>
/* Solo estética para indicar desactivado */
.opacity-50 { opacity: .55; }
/* Contenedor fijo para la imagen */
.product-img-wrap{
  width: 100%;
  height: 200px;           /* tamaño estándar */
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;        /* opcional */
  object-fit: contain;     /* si prefieres recortar: cover */
}

/* La imagen NO se estira, mantiene proporción */
.product-img-wrap img{
  width: 100%;
  height: 100%;
}

/* Responsive: baja un poco la altura en pantallas pequeñas */
@media (max-width: 576px){
  .product-img-wrap{ height: 150px; }
}

</style>


<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<title>Productos</title>

<div class="card mb-3">

    <div class="card-header">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center">
                <div class="col-md-auto">
                    <h5 class="mb-2 mb-md-0">Productos</h5>
                </div>

            </div>
            <div class="row flex-between-center gy-2 px-x1 mb-2" id="searh_empresa">
                <div class="col-auto pe-0">
                    <h6 class="mb-0">Empresa</h6>
                </div>
                <div class="col-auto">
                    <div class="input-group input-search-width">
                        <select class="form-select selectpicker" id="lista_empresas">
                        </select>
                    </div>
                </div>
            </div>
            <!-- <div class="row flex-between-center">
                <div class="col-auto">
                    <button class="btn btn-success" type="button" onclick="$('#subirArchivo').val('');$('#subirArchivo').click();">
                        <span class="far fa-file-excel" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Cargar Excel</span>
                    </button>
                    <input type="file" class="form-control" id="subirArchivo" accept=".xlsx, .xls" style="display: none;">
                </div>
                <div class="col-auto">
                    <a class="btn btn-falcon-default btn-sm" type="button" href="crear_producto.php">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a>
                </div>
            </div> -->

            <!-- <div class="row flex-between-center">
                <div class="col-auto">
                    <button class="btn btn-success" type="button"
                    onclick="$('#subirArchivo').val(''); $('#subirArchivo').click();">
                    <span class="far fa-file-excel" data-fa-transform="shrink-3 down-2"></span>
                    <span class="d-none d-sm-inline-block ms-1">Cargar Excel</span>
                    </button>

                    < Excel >
                    <input type="file" class="form-control" id="subirArchivo"
                    accept=".xlsx, .xls" style="display: none;">

                    < ✅ Archivos (imagenes/pdf) >
                    <input type="file" class="form-control" id="subirArchivosProducto"
                    multiple accept="image/*,.pdf,.webp,.jpg,.jpeg,.png"
                    style="display:none;">
                </div>

                <div class="col-auto">
                    <a class="btn btn-falcon-default btn-sm" type="button" href="crear_producto.php">
                    <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                    <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a>
                </div>
            </div> -->

            <div class="row flex-between-center">
                <!-- <div class="col-auto">
                    <a class="btn btn-success" type="button" href="carga_masiva_productos.php">
                    <span class="far fa-file-excel" data-fa-transform="shrink-3 down-2"></span>
                    <span class="d-none d-sm-inline-block ms-1">Carga masiva</span>
                </a> -->
                </div>

                <div class="col-auto">
                    <a class="btn btn-falcon-default btn-sm" type="button" href="crear_producto.php">
                    <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                    <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a>
                </div>
            </div>


        </div>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-lg-4">
                <form class="position-relative" data-bs-toggle="search" data-bs-display="static">
                    <input class="form-control search-input fuzzy-search pe-5"
                            id="buscar_producto"
                            type="search"
                            placeholder="Buscar..."
                            aria-label="Buscar"
                            oninput="filtrarProductosLive(this.value)">

                    <span class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></span>
                </form>

            </div>
        </div>
        <!-- <div class="falcon-data-table" id="tabla_contenido">
            
        </div> -->
        <div class="row" id="lista_productos">
        </div>
    </div>
</div>



<!-- Requiere Bootstrap 5 y (opcional) jQuery. También íconos de Bootstrap para el botón (+) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<!-- Conexión API js -->
<script src="js/productos.js?1.0.0.0.0.0.0.0.0.1"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>