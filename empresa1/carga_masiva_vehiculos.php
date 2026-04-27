<?php
$menu = "empresas";
$sub_menu = "vehiculos";
require 'includes/header.php';
foreach ($permisos as $value) {
  if ($value["permiso"] == "Empresas" && $value["valor"] == "false") {
    echo "<script>window.location.href = '" . $dashboard . "'</script>";
  }
}
?>

<style>
  /* ✅ NO global: solo en este card */
  .card.carga-masiva-vehiculos .card-body {
    overflow: hidden;
  }

  .excel-wrap {
    width: 100%;
    max-width: 100%;
    max-height: 520px;
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    scrollbar-gutter: stable both-edges;
    position: relative;
  }

  table.excel-table {
    width: max-content;
    min-width: 1600px;
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
  }

  table.excel-table th,
  table.excel-table td {
    white-space: nowrap;
    vertical-align: middle;
  }

  table.excel-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8fafc;
    box-shadow: 0 1px 0 #e5e7eb;
  }

  .excel-table input,
  .excel-table select,
  .excel-table textarea {
    min-width: 140px;
    font-size: 12px;
  }

  .w-xs {
    min-width: 70px !important;
  }

  .w-sm {
    min-width: 110px !important;
  }

  .w-md {
    min-width: 160px !important;
  }

  .w-lg {
    min-width: 240px !important;
  }

  .excel-table textarea {
    resize: vertical;
    min-height: 34px;
  }

  /* ✅ checkbox estilo excel (scoped) */
  .excel-table input[type="checkbox"] {
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

  .excel-table input[type="checkbox"]:checked {
    background: #16a34a;
    border-color: #16a34a;
    position: relative;
  }

  .excel-table input[type="checkbox"]:checked::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 1px;
    width: 5px;
    height: 10px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
  }

  .chk-cell {
    min-width: 90px;
    width: 90px;
    text-align: center;
  }

  .chk-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* ✅ que el dropdown NO quede debajo del thead sticky */
  .select2-container {
    z-index: 99999 !important;
  }

  .select2-dropdown {
    z-index: 99999 !important;
  }

  /* tu thead sticky puede quedar más bajo */
  table.excel-table thead th {
    z-index: 5;
    /* antes 10 */
  }

  /* Scroll para alertas largas de SweetAlert */
  .sweet-alert p {
    max-height: 40vh;
    /* Limita la altura máxima al 40% de la pantalla */
    overflow-y: auto;
    /* Activa el scroll vertical automático */
    padding-right: 10px;
    /* Da un pequeño respiro para la barra de scroll */
    text-align: center !important;
    /* Mantiene el texto centrado */
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

  /* ✅ DISEÑO MODERNIZADO */
  .card.carga-masiva-vehiculos {
    border: none;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
    background: #ffffff;
  }

  .card-header {
    background: #ffffff !important;
    border-bottom: 1px solid #f1f5f9 !important;
    padding: 1.5rem !important;
  }

  .excel-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fcfcfd;
  }

  /* Tabla Estilo Notion */
  table.excel-table {
    border: none;
    font-family: 'Inter', sans-serif;
  }

  table.excel-table thead th {
    background: #f8fafc !important;
    color: #475569;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.05em;
    padding: 12px 15px !important;
    border-bottom: 2px solid #e2e8f0 !important;
  }

  table.excel-table tbody td {
    padding: 8px !important;
    border-color: #f1f5f9 !important;
  }

  /* Inputs minimalistas */
  .excel-table .form-control,
  .excel-table .form-select {
    border: 1px solid transparent;
    background: transparent;
    transition: all 0.2s;
    border-radius: 6px;
  }

  .excel-table tr:hover {
    background: #f1f5f955;
  }

  .excel-table tr:hover .form-control,
  .excel-table tr:hover .form-select {
    background: #fff;
    border-color: #cbd5e1;
  }

  .excel-table .form-control:focus {
    background: #fff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  /* Botones con Personalidad */
  .btn-success {
    background: #10b981;
    border: none;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
  }

  .btn-secondary {
    background: #64748b;
    border: none;
  }

  .btn-outline-danger {
    border-radius: 50%;
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Sticky Primera Columna */
  table.excel-table th:nth-child(2),
  table.excel-table td:nth-child(2) {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
  }

  /* ✅ Efecto de guardado exitoso en la fila */
  .row-saved {
    animation: flash-green 1.5s ease-out;
  }

  @keyframes flash-green {
    0% {
      background-color: rgba(16, 185, 129, 0.2);
    }

    100% {
      background-color: transparent;
    }
  }

  /* ✅ DISEÑO DE CELDAS ESPECIALES */
  .preview_frontal,
  .preview_posterior {
    transition: transform 0.2s;
    cursor: zoom-in;
  }

  .preview_frontal:hover,
  .preview_posterior:hover {
    transform: scale(2.5);
    z-index: 100;
    position: relative;
  }

  /* Badge para el estado de Publicación */
  .chk-cell {
    background: #f8fafc;
  }

  .chk-wrap input[type="checkbox"] {
    accent-color: #10b981;
  }

  /* Animación para filas nuevas */
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  #tbodyVehiculos tr {
    animation: slideIn 0.3s ease-out;
  }

  /* Estilo para las cabeceras de los grupos de datos */
  table.excel-table thead th {
    background: linear-gradient(to bottom, #f8fafc, #eff6ff) !important;
    border-top: 3px solid #3b82f6 !important;
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

<script src="js/carga_masiva_vehiculos.js?v1.0.10"></script>
<script src="js/alerts.js"></script>

<?php require 'includes/footer.php'; ?>