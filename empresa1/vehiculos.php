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



<title>Vehículos</title>

<div class="card mb-3">

    <div class="card-header">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center">
                <div class="col-md-auto">
                    <h5 class="mb-2 mb-md-0">Vehículos</h5>
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
            <div class="row flex-between-center">
                <!-- <div class="col-auto">
                    <a class="btn btn-success" type="button" href="carga_masiva_vehiculos.php">
                    <span class="far fa-file-excel" data-fa-transform="shrink-3 down-2"></span>
                    <span class="d-none d-sm-inline-block ms-1">Carga masiva</span>
                </a> -->
                </div>

                <div class="col-auto">
                    <a class="btn btn-falcon-default btn-sm" type="button" href="crear_vehiculo.php">
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
                            id="buscar_vehiculo"
                            type="search"
                            placeholder="Buscar..."
                            aria-label="Buscar"
                            oninput="filtrarVehiculosLive(this.value)">

                    <span class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></span>
                </form>

            </div>
        </div>
        <!-- <div class="falcon-data-table" id="tabla_contenido">
            
        </div> -->
        <div class="row" id="lista_vehiculos">
            <!-- <div class="mb-4 col-md-6 col-lg-4">
                <div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
                    <div class="overflow-hidden">
                        <div class="position-relative rounded-top overflow-hidden"><a class="d-block" href="../../../app/e-commerce/product/product-details.html"><img class="img-fluid rounded-top" src="../theme/assets/img/products/2.jpg" alt="" /></a><span class="badge rounded-pill bg-success position-absolute mt-2 me-2 z-2 top-0 end-0">New</span>
                        </div>
                        <div class="p-3">
                            <h5 class="fs-9"><a class="text-1100" href="../../../app/e-commerce/product/product-details.html">Apple iMac Pro (27-inch with Retina 5K Display, 3.0GHz 10-core Intel Xeon W, 1TB SSD)</a></h5>
                            <p class="fs-10 mb-3"><a class="text-500" href="#!">Computer &amp; Accessories</a></p>
                            <h5 class="fs-md-7 text-warning mb-0 d-flex align-items-center mb-3"> $1199.5
                                <del class="ms-2 fs-10 text-500">$2399 </del>
                            </h5>
                            <p class="fs-10 mb-1">Shipping Cost: <strong>$50</strong></p>
                            <p class="fs-10 mb-1">Stock: <strong class="text-success">Available</strong>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex flex-between-center px-3">
                        <div><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-300"></span><span class="ms-1">(8)</span>
                        </div>
                        <div><a class="btn btn-sm btn-falcon-default me-2" href="#!" data-bs-toggle="tooltip" data-bs-placement="top" title="Add to Wish List"><span class="far fa-heart"></span></a><a class="btn btn-sm btn-falcon-default" href="#!" data-bs-toggle="tooltip" data-bs-placement="top" title="Add to Cart"><span class="fas fa-cart-plus"></span></a></div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>



<!-- Requiere Bootstrap 5 y (opcional) jQuery. También íconos de Bootstrap para el botón (+) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<!-- Conexión API js -->
<script src="js/vehiculos.js?1.0.0.0.0.0.0.0.0.2"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>