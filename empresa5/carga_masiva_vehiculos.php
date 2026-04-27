<?php
$menu = "empresas";
$sub_menu = "vehiculos";
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
                window.location.href = 'vehiculos.php';
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
  /* ✅ NO global: solo en este card */
  .card.carga-masiva-vehiculos .card-body{ overflow: hidden; }

  .excel-wrap{
    width: 100%;
    max-width: 100%;
    max-height: 520px;
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    scrollbar-gutter: stable both-edges;
    position: relative;
  }

  table.excel-table{
    width: max-content;
    min-width: 1600px;
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
  }

  table.excel-table th, table.excel-table td{
    white-space: nowrap;
    vertical-align: middle;
  }

  table.excel-table thead th{
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8fafc;
    box-shadow: 0 1px 0 #e5e7eb;
  }

  .excel-table input,
  .excel-table select,
  .excel-table textarea{
    min-width: 140px;
    font-size: 12px;
  }

  .w-xs { min-width: 70px !important; }
  .w-sm { min-width: 110px !important; }
  .w-md { min-width: 160px !important; }
  .w-lg { min-width: 240px !important; }

  .excel-table textarea{ resize: vertical; min-height: 34px; }

  /* ✅ checkbox estilo excel (scoped) */
  .excel-table input[type="checkbox"]{
    all: unset;
    -webkit-appearance: none;
    appearance: none;
    width: 18px !important;
    height: 18px !important;
    border: 2px solid #cbd5e1;
    border-radius: 4px;
    display: inline-block;
    cursor: pointer;
    background: #fff;
  }
  .excel-table input[type="checkbox"]:checked{
    background: #16a34a;
    border-color: #16a34a;
    position: relative;
  }
  .excel-table input[type="checkbox"]:checked::after{
    content: "";
    position: absolute;
    left: 5px; top: 1px;
    width: 5px; height: 10px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
  }
  .chk-cell{ min-width: 90px; width: 90px; text-align:center; }
  .chk-wrap{ display:flex; justify-content:center; align-items:center; }

  /* ✅ que el dropdown NO quede debajo del thead sticky */
  .select2-container { z-index: 99999 !important; }
  .select2-dropdown  { z-index: 99999 !important; }

  /* tu thead sticky puede quedar más bajo */
  table.excel-table thead th{
    z-index: 5; /* antes 10 */
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

<title>Carga Masiva Vehículos</title>

<div class="card shadow mb-3 carga-masiva-vehiculos">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Carga Masiva - Vehículos</h5>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-secondary" id="btnAddRow" type="button">+ Agregar fila</button>
      <button class="btn btn-sm btn-danger" id="btnClear" type="button">Limpiar</button>
      <button id="btnGuardarBorrador" class="btn btn-secondary" type="button">Guardar borrador</button>
      <button id="btnPublicar" class="btn btn-success" type="button">Publicar</button>
    </div>
  </div>

  <div class="card-body">
    <div class="excel-wrap" id="excelWrapVehiculos">
      <table class="table table-bordered table-sm align-middle excel-table" id="tablaVehiculos">
        <thead>
          <tr>
            <th class="w-sm">Acción</th>
            <th class="w-sm text-center">Publicar</th>
            <th class="w-md">Tipo Vehículo</th>
            <th class="w-md">Modelo</th>
            <th class="w-sm">Subtipo</th>
            <th class="w-md">Marca</th>
            <th class="w-md">Tracción</th>
            <th class="w-md">Func. Motor</th>
            <th class="w-lg">Descripción</th>
            <th class="w-lg">Imagen frontal</th>
            <th class="w-lg">Imagen posterior</th>
            <th class="w-sm">Año</th>
            <th class="w-md">Condición</th>
            <th class="w-md">Tipo Vendedor</th>
            <th class="w-sm">Km/Millaje</th>
            <th class="w-md">Transmisión</th>
            <th class="w-sm">Inicio Placa</th>
            <th class="w-sm">Fin Placa</th>
            <th class="w-md">Color</th>
            <th class="w-md">Cilindraje</th>
            <th class="w-md">Tapicería Asientos</th>
            <th class="w-md">Tipo Dueño</th>
            <th class="w-md">Dirección</th>
            <th class="w-md">Climatización</th>

            <th class="w-md">Provincia</th>
            <th class="w-md">Cantón</th>
            <th class="w-md">Tags</th>
            <th class="w-sm">Precio</th>
            <th class="w-sm text-center">IVA</th>
            <th class="w-sm text-center">Negociable</th>
            <th class="w-sm">Descuento</th>
            <th class="w-lg">Archivos/Documentos</th>
          </tr>
        </thead>
        <tbody id="tbodyVehiculos"></tbody>
      </table>
    </div>
  </div>
</div>

<script src="js/carga_masiva_vehiculos.js?v1.0.5"></script>
<script src="js/alerts.js"></script>

<?php require 'includes/footer.php'; ?>
