<?php
$menu = "empresas";
$sub_menu = "servicios";
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
                window.location.href = 'servicios.php';
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

<title>Carga Masiva Servicios</title>

<style>
/* ── Contenedor principal ───────────────────────── */
.cmv-wrap { max-height: 78vh; overflow-y: auto; overflow-x: hidden; padding-right: 4px; }
.cmv-wrap::-webkit-scrollbar { width: 6px; }
.cmv-wrap::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
.cmv-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* ── Tarjeta por servicio ───────────────────────── */
.veh-card {
  border: 1px solid #e2e8f0;
  border-left: 4px solid #0ea5e9;
  border-radius: 10px;
  background: #fff;
  overflow: hidden;
  transition: box-shadow .15s;
}
.veh-card:hover { box-shadow: 0 4px 16px rgba(15,23,42,.08); }

.veh-card:nth-child(6n+1) { border-left-color: #0ea5e9; }
.veh-card:nth-child(6n+2) { border-left-color: #10b981; }
.veh-card:nth-child(6n+3) { border-left-color: #f59e0b; }
.veh-card:nth-child(6n+4) { border-left-color: #8b5cf6; }
.veh-card:nth-child(6n+5) { border-left-color: #ef4444; }
.veh-card:nth-child(6n+6) { border-left-color: #ec4899; }

/* ── Header de tarjeta ──────────────────────────── */
.veh-card-hd {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 12px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  min-height: 40px;
}
.veh-num {
  font-size: 11px; font-weight: 700;
  background: #e2e8f0; color: #475569;
  border-radius: 5px; padding: 1px 7px; white-space: nowrap;
}
.veh-label {
  font-size: 12px; font-weight: 600; color: #334155;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px;
}
.veh-publicar-label {
  display: flex; align-items: center; gap: 5px;
  font-size: 12px; font-weight: 600; color: #16a34a;
  cursor: pointer; margin-bottom: 0; white-space: nowrap;
}

/* ── Cuerpo de tarjeta ──────────────────────────── */
.veh-card-bd { padding: 10px 12px 12px; }

.veh-sec-lbl {
  font-size: 10px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .06em; color: #94a3b8;
  margin: 6px 0 4px; padding-bottom: 3px; border-bottom: 1px solid #f1f5f9;
}
.veh-lbl {
  font-size: 11px; color: #64748b; margin-bottom: 2px;
  display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.veh-card .form-select,
.veh-card .form-control { font-size: 12px; }

.veh-card .select2-container { width: 100% !important; }
.select2-container { z-index: 99999 !important; }
.select2-dropdown  { z-index: 99999 !important; }

.veh-img-preview {
  width: 56px; height: 56px; object-fit: cover;
  border-radius: 6px; border: 1px solid #e2e8f0;
  display: none; margin-bottom: 4px;
}
.veh-img-preview.visible { display: block; }

.cmv-search-wrap { position: relative; }
.cmv-search-wrap .fa-magnifying-glass {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  color: #94a3b8; font-size: 13px; pointer-events: none;
}
.cmv-search-wrap input { padding-left: 30px; }

.sweet-alert p { max-height: 40vh; overflow-y: auto; padding-right: 10px; text-align: center !important; }
.sweet-alert p::-webkit-scrollbar { width: 6px; }
.sweet-alert p::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
.sweet-alert p::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>

<div class="card shadow mb-3 carga-masiva-servicios">
  <div class="card-header d-flex flex-wrap gap-2 align-items-center">
    <h5 class="mb-0 me-auto">Carga Masiva — Servicios</h5>

    <div class="cmv-search-wrap" style="min-width:310px;">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="buscadorBorrador"
             class="form-control"
             placeholder="Buscar borrador (título, servicio, marca…)">
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <button class="btn btn-sm btn-outline-secondary" id="btnAddRow" type="button">
        <i class="fa-solid fa-plus"></i> Agregar fila
      </button>
      <button class="btn btn-sm btn-outline-danger" id="btnClear" type="button">
        <i class="fa-solid fa-trash"></i> Limpiar
      </button>
      <button id="btnGuardarBorrador" class="btn btn-sm btn-secondary" type="button">
        <i class="fa-solid fa-floppy-disk"></i> Guardar borrador
      </button>
      <button id="btnPublicar" class="btn btn-sm btn-success" type="button">
        <i class="fa-solid fa-rocket"></i> Publicar
      </button>
    </div>
  </div>

  <div class="card-body pt-2">
    <div class="cmv-wrap" id="vehiculosContainer"></div>
  </div>
</div>

<script src="js/carga_masiva_servicios.js?v2.0.0"></script>
<script src="js/alerts.js"></script>

<?php require 'includes/footer.php'; ?>
