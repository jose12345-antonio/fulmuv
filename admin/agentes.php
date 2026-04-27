<?php
$menu = "empresas";
$sub_menu = "agentes";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Empresas" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }

    #autocomplete_direccion {
      width: 100%;
      height: 40px;
      border: 1px solid #ccc;
      border-radius: 5px;
      padding: 8px;
    }

    .field {
      margin-top: 10px;
    }
</style>
<title>Agentes</title>
<div class="card mb-3">

    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Agentes</h5>
            </div>
            <div class="col-8 col-sm-auto text-end ps-2">
                <div id="table-customers-replace-element">
                    <button onclick="addAgente()" class="btn btn-falcon-default btn-sm" type="button">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="falcon-data-table">
            <table class="table table-sm mb-0 data-table fs-10" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Nombre</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Correo</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Código</th>
                        <th class="align-middle no-sort"></th>
                    </tr>
                </thead>
                <tbody id="lista_agentes">

                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/agentes.js?v1.0.0"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>

<?php
require 'includes/footer.php';
?>