<?php
$menu = "empresas";
$sub_menu = "empresas";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Empresas" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
  /* Evita que el card-body se distorsione por el overflow */
.card-body{
  overflow: hidden !important;
}

/* Scroll SOLO para la tabla */
.excel-wrap{
  width: 100%;
  max-width: 100%;
  max-height: 520px;
  overflow: auto;                 /* en ambos ejes */
  border: 1px solid #e5e7eb;
  border-radius: 12px;

  /* evita saltos cuando aparece scrollbar */
  scrollbar-gutter: stable both-edges;

  /* ayuda a sticky */
  position: relative;
}

/* Tabla no se aplasta */
table.excel-table{
  width: max-content;
  min-width: 1400px;
  border-collapse: separate;
  border-spacing: 0;
  margin: 0;
}

/* Celdas */
table.excel-table th,
table.excel-table td{
  white-space: nowrap;
  vertical-align: middle;
}

/* Header fijo real */
table.excel-table thead th{
  position: sticky;
  top: 0;
  z-index: 10;
  background: #f8fafc;
  box-shadow: 0 1px 0 #e5e7eb;
}

/* (Opcional) si se “mueve” el header al scrollear horizontal */
table.excel-table thead th{
  background-clip: padding-box;
}

/* Scroll para alertas largas de SweetAlert */
.sweet-alert p {
  max-height: 40vh; /* Limita la altura máxima al 40% de la pantalla */
  overflow-y: auto; /* Activa el scroll vertical automático */
  padding-right: 10px; /* Da un pequeño respiro para la barra de scroll */
  text-align: center !important; /* Mantiene el texto centrado */
}

/* (Opcional) Dale un diseño moderno a la barra de scroll para que no se vea tosca */
.sweet-alert p::-webkit-scrollbar {
  width: 6px;
}
.sweet-alert p::-webkit-scrollbar-track {
  background: #f8fafc; 
  border-radius: 4px;
}
.sweet-alert p::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 4px;
}
.sweet-alert p::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}

</style>



<title>Carga Masiva</title>
<div class="card shadow mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Registro de servicios</h5>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-secondary" id="btnAddRow" type="button">+ Agregar fila</button>
      <button class="btn btn-sm btn-danger" id="btnClear" type="button">Limpiar</button>
      <!-- <button class="btn btn-sm btn-success" id="btnGuardarTodo" type="button">Guardar productos</button> -->
      <button id="btnGuardarBorrador" class="btn btn-secondary">Guardar borrador</button>
      <button id="btnPublicar" class="btn btn-success">Publicar</button>
    </div>
  </div>

  <div class="card-body">
    <div class="excel-wrap">
        <table class="table table-bordered table-sm align-middle excel-table" id="tablaProductos">
            <thead>
            <tr>
              <th class="w-sm">Acción</th>
              <th class="w-xs">Publicar</th>
              <th class="w-md">Título</th>
              <th class="w-lg">Nombre Servicio</th>
              <th class="w-lg">Descripción</th>
              <th class="w-md">Marca Vehículo</th>
              <th class="w-md">Referencias</th>
              <th class="w-md">Modelo</th>
              <th class="w-md">Tipo Auto</th>
              <th class="w-md">Tracción</th>
              <th class="w-md">Func. Motor</th>

              <th class="w-md">Categoría</th>

              <th class="w-sm">Precio</th>
              <th class="w-sm">Descuento</th>
              <th class="w-md">Tags</th>

              <th class="w-lg">Imagen frontal</th>
              <th class="w-lg">Imagen posterior</th>

              <th class="w-lg">Documento / Archivo</th>
              <th class="w-sm text-center">24/7</th>
              <th class="w-sm text-center">Carretera</th>
              <th class="w-sm text-center">Domicilio</th>
            </tr>
            </thead>
            <tbody id="tbodyProductos"></tbody>
        </table>
    </div>
  </div>
</div>

<!-- Conexión API js -->
<script src="js/carga_masiva_servicios.js?v1.2.9"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>

<?php
require 'includes/footer.php';
?>