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
.opacity-50 { opacity: .55; }
.catalog-card {
  border: 1px solid #e5e7eb;
  border-radius: .8rem;
  overflow: hidden;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
  transition: transform .18s ease, box-shadow .18s ease;
  height: 100%;
}
.catalog-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 34px rgba(15, 23, 42, 0.1);
}
.catalog-media {
  height: 168px;
  background: radial-gradient(circle at top, #f8fafc 0%, #eef4f8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}
.catalog-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.catalog-body {
  padding: .72rem .78rem;
}
.catalog-title {
  color: #0f172a;
  font-weight: 700;
  font-size: .88rem;
  line-height: 1.22;
  min-height: 2.1rem;
}
.catalog-meta {
  color: #64748b;
  font-size: .74rem;
}
.catalog-price {
  color: #b45309;
  font-weight: 800;
  font-size: .95rem;
}
.catalog-actions {
  display: flex;
  gap: .35rem;
  flex-wrap: wrap;
}
.catalog-discount {
  position: absolute;
  top: .65rem;
  right: .65rem;
  background: rgba(185, 28, 28, .92);
  color: #fff;
  padding: .28rem .5rem;
  border-radius: 999px;
  font-size: .72rem;
  font-weight: 700;
  line-height: 1;
  box-shadow: 0 8px 18px rgba(127, 29, 29, .22);
}
.catalog-pagination .page-link {
  border-radius: .75rem;
}
.empty-products-state {
  min-height: 320px;
}
@media (max-width: 576px){
  .catalog-media { height: 150px; }
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
        <div class="catalog-pagination d-flex justify-content-center mt-3" id="paginacion_productos"></div>
    </div>
</div>



<!-- Requiere Bootstrap 5 y (opcional) jQuery. También íconos de Bootstrap para el botón (+) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<!-- Conexión API js -->
<script src="js/productos.js?1.0.0.0.0.0.0.0.1.2"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
