<?php
$menu = "empresas";
$sub_menu = "empresas";
require 'includes/header.php';

if (isset($membresia["nombre"])) {
    $nombre_membresia = preg_replace('/[^a-z0-9]+/', '', strtolower(trim((string)$membresia["nombre"])));
    if ($tipo_user == "sucursal" || $nombre_membresia !== "fulmuv") {
        echo "<script>
            swal({
                title: 'Plan no disponible',
                text: 'La carga masiva solo está disponible para el plan FULMUV.',
                icon: 'info'
            }, function () {
                window.location.href = 'productos.php';
            });
        </script>";
        exit;
    }
}

foreach ($permisos as $value) {
    if ($value["permiso"] == "Empresas" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
  /* Asegura que el card no se deforme por el overflow interno */
.card-body{
  overflow: hidden;          /* clave: el scroll NO se va al body */
}

/* Scroll solo aquí */
.excel-wrap{
  width: 100%;
  max-width: 100%;
  max-height: 520px;         
  overflow: auto;            /* auto en ambos ejes */
  border: 1px solid #e5e7eb;
  border-radius: 12px;

  /* evita “saltos” cuando aparece/desaparece la barra */
  scrollbar-gutter: stable both-edges;

  /* mejora sticky en algunos casos */
  position: relative;
}

/* tabla: que no se aplaste */
table.excel-table{
  width: max-content;
  min-width: 1400px;
  border-collapse: separate;
  border-spacing: 0;
  margin: 0;                 /* evita desajustes raros */
}

/* sticky header estable */
table.excel-table thead th{
  position: sticky;
  top: 0;
  z-index: 10;               /* un poco más alto */
  background: #f8fafc;
  box-shadow: 0 1px 0 #e5e7eb; /* línea inferior tipo excel */
}

/* evita cortes raros en celdas */
table.excel-table th,
table.excel-table td{
  white-space: nowrap;
  vertical-align: middle;
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
    <h5 class="mb-0">Registro de productos</h5>
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
              <!-- <th class="w-lg">Nombre Producto</th> -->
              <th class="w-sm">Código</th>
              <th class="w-lg">Descripción</th>
              <th class="w-md">Marca Producto</th>
              <th class="w-md">Marca Vehículo</th>
              <th class="w-md">Referencias</th>
              <th class="w-md">Modelo</th>
              <th class="w-md">Tipo Auto</th>
              <th class="w-md">Tracción</th>
              <th class="w-md">Func. Motor</th>

              <th class="w-md">Categoría</th>
              <th class="w-md">Subcategoría</th>

              <th class="w-sm">Precio</th>
              <th class="w-sm">Descuento</th>
              <th class="w-sm">Peso</th>
              <th class="w-sm text-center">IVA</th>
              <th class="w-sm text-center">Negociable</th>
              <th class="w-sm">Tags</th>

              <th class="w-lg">Imagen frontal</th>
              <th class="w-lg">Imagen posterior</th>

              <th class="w-lg">Documento / Archivo</th>
            </tr>
            </thead>
            <tbody id="tbodyProductos"></tbody>
        </table>
    </div>
  </div>
</div>

<!-- Conexión API js -->
<script src="js/carga_masiva_productos.js?v1.2.9"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>

<?php
require 'includes/footer.php';
?>
