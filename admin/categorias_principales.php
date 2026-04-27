<?php
$menu = "general";
$sub_menu = "categorias_principales";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<title>Categorías Principales</title>

<div class="card mb-3">
  <div class="card-header">
    <div class="row flex-between-center">
      <div class="col-auto d-flex align-items-center">
        <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fas fa-layer-group me-2 text-primary"></i>Categorías Principales</h5>
      </div>
      <div class="col-auto text-end">
        <button onclick="abrirCrear()" class="btn btn-primary btn-sm" type="button">
          <span class="fas fa-plus me-1"></span>Nueva Categoría
        </button>
      </div>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0 fs-10" id="my_table">
        <thead class="bg-200">
          <tr>
            <th class="text-900 align-middle py-2 ps-3">Nombre</th>
            <th class="text-900 align-middle py-2 text-end pe-3" style="width:110px">Acciones</th>
          </tr>
        </thead>
        <tbody id="lista_categorias"></tbody>
      </table>
    </div>
  </div>
</div>

<script src="js/categorias_principales.js?v2.0.1"></script>
<script src="js/alerts.js"></script>
<?php require 'includes/footer.php'; ?>
