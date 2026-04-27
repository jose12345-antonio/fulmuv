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
.catalog-price-wrap {
  display: flex;
  align-items: baseline;
  gap: .38rem;
  flex-wrap: wrap;
}
.catalog-price-original {
  color: #94a3b8;
  font-size: .72rem;
  font-weight: 600;
}
.catalog-price-discounted {
  color: #b45309;
  font-weight: 800;
  font-size: .95rem;
}
.catalog-description {
  color: #475569;
  font-size: .74rem;
  line-height: 1.35;
  min-height: 2rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.catalog-location-badge {
  max-width: 46%;
  white-space: normal;
  text-align: right;
  line-height: 1.2;
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
.catalog-outline-badge {
  border: 1px solid #cbd5e1;
  color: #475569;
  background: #fff;
  font-size: .7rem;
  font-weight: 600;
  padding: .14rem .44rem;
  border-radius: 999px;
}
.catalog-separator {
  color: #94a3b8;
  font-size: .72rem;
  line-height: 1;
}
.catalog-pagination .page-link {
  border-radius: .75rem;
}
@media (max-width: 576px){
  .catalog-media { height: 150px; }
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
        <div class="catalog-pagination d-flex justify-content-center mt-3" id="paginacion_vehiculos"></div>
    </div>
</div>



<!-- Requiere Bootstrap 5 y (opcional) jQuery. También íconos de Bootstrap para el botón (+) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<!-- Conexión API js -->
<script src="js/vehiculos.js?1.0.0.0.0.0.0.0.0.3"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
