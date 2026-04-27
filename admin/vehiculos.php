<?php
$menu = "vehiculos";
$sub_menu = "vehiculos";
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
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; position: relative;
}
.catalog-media img { width: 100%; height: 100%; object-fit: cover; }
.catalog-body { padding: .72rem .78rem; }
.catalog-title { color: #0f172a; font-weight: 700; font-size: .88rem; line-height: 1.22; min-height: 2.1rem; }
.catalog-meta { color: #64748b; font-size: .74rem; }
.catalog-price { color: #b45309; font-weight: 800; font-size: .95rem; }
.catalog-price-wrap { display: flex; align-items: baseline; gap: .38rem; flex-wrap: wrap; }
.catalog-price-original { color: #94a3b8; font-size: .72rem; font-weight: 600; }
.catalog-price-discounted { color: #b45309; font-weight: 800; font-size: .95rem; }
.catalog-description {
  color: #475569; font-size: .74rem; line-height: 1.35; min-height: 2rem;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.catalog-location-badge { max-width: 46%; white-space: normal; text-align: right; line-height: 1.2; }
.catalog-actions { display: flex; gap: .35rem; flex-wrap: wrap; }
.catalog-discount {
  position: absolute; top: .65rem; right: .65rem;
  background: rgba(185, 28, 28, .92); color: #fff;
  padding: .28rem .5rem; border-radius: 999px;
  font-size: .72rem; font-weight: 700; line-height: 1;
  box-shadow: 0 8px 18px rgba(127, 29, 29, .22);
}
.catalog-outline-badge {
  border: 1px solid #cbd5e1; color: #475569; background: #fff;
  font-size: .7rem; font-weight: 600; padding: .14rem .44rem; border-radius: 999px;
}
.catalog-separator { color: #94a3b8; font-size: .72rem; line-height: 1; }
.catalog-pagination .page-link { border-radius: .75rem; }
@media (max-width: 576px){ .catalog-media { height: 150px; } }
</style>

<title>Vehículos</title>

<div class="card mb-3">
    <div class="card-header">
        <div class="d-lg-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Vehículos</h5>
            </div>
            <div class="d-flex gap-2 align-items-center mt-2 mt-lg-0">
                <form class="position-relative">
                    <input class="form-control search-input fuzzy-search pe-5"
                        id="buscar_vehiculo"
                        type="search"
                        placeholder="Buscar..."
                        aria-label="Buscar"
                        oninput="filtrarVehiculosLive(this.value)"
                        style="min-width:220px;">
                    <span class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></span>
                </form>
                <a class="btn btn-primary btn-sm" type="button" href="crear_vehiculo.php">
                    <span class="fas fa-plus me-1"></span>
                    <span class="d-none d-sm-inline-block">Crear</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row" id="lista_vehiculos"></div>
        <div class="catalog-pagination d-flex justify-content-center mt-3" id="paginacion_vehiculos"></div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Conexión API js -->
<script src="js/vehiculos.js?admin1.0"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
