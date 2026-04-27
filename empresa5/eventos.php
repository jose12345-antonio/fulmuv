<?php
$menu = "eventos";
$sub_menu = "eventos";
require 'includes/header.php';

if (isset($membresia["nombre"])) {
    $nombre_membresia = preg_replace('/[^a-z0-9]+/', '', strtolower(trim((string)$membresia["nombre"])));
    if ($tipo_user == "sucursal" || $nombre_membresia === "basicmuv") {
        echo "<script>
            swal({
                title: 'Necesitas mejorar tu plan',
                text: 'Tu plan actual no permite registrar eventos.\n\n¿Deseas ir ahora a actualizar tu membresía?',
                icon: 'info',
                buttons: {
                    cancel: { text: 'Cancelar', visible: true, closeModal: true },
                    confirm: { text: 'Mejorar plan', value: true, closeModal: true }
                }
            }, function () {
                window.location.href = 'upgrade_membresia.php?id_empresa=' + " . json_encode($id_empresa) . ";
            });
        </script>";
        exit;
    }
}

foreach ($permisos as $value) {
    if ($value["permiso"] == "Eventos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>

<style>
    :root {
        --eventos-calendar-surface: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        --eventos-calendar-panel: #f8fafc;
        --eventos-calendar-panel-strong: #eef4f8;
        --eventos-calendar-border: #d8e1ea;
        --eventos-calendar-text: #0f172a;
        --eventos-calendar-muted: #475569;
        --eventos-calendar-today: rgba(0, 104, 111, 0.12);
        --eventos-calendar-list-hover: #edf7f8;
    }

    html[data-bs-theme="dark"] {
        --eventos-calendar-surface: linear-gradient(180deg, #13202b 0%, #0f1b26 100%);
        --eventos-calendar-panel: #162432;
        --eventos-calendar-panel-strong: #1c2d3d;
        --eventos-calendar-border: rgba(148, 163, 184, 0.22);
        --eventos-calendar-text: #e2e8f0;
        --eventos-calendar-muted: #cbd5e1;
        --eventos-calendar-today: rgba(45, 212, 191, 0.18);
        --eventos-calendar-list-hover: #1a2b39;
    }

    .eventos-calendar-shell {
        padding: 1.5rem;
    }

    .eventos-calendar-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem 0;
    }

    .eventos-calendar-intro {
        max-width: 720px;
    }

    .eventos-calendar-intro h4 {
        margin-bottom: .35rem;
        color: var(--eventos-calendar-text);
    }

    .eventos-calendar-intro p {
        margin-bottom: 0;
        color: var(--eventos-calendar-muted);
        font-size: .95rem;
    }

    .eventos-calendar-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .eventos-calendar-legend span {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .45rem .8rem;
        border-radius: 999px;
        background: var(--eventos-calendar-panel);
        border: 1px solid var(--eventos-calendar-border);
        color: var(--eventos-calendar-muted);
        font-size: .78rem;
        font-weight: 600;
    }

    .eventos-calendar-legend i {
        width: .65rem;
        height: .65rem;
        border-radius: 999px;
        display: inline-block;
    }

    .eventos-calendar-card {
        background: var(--eventos-calendar-surface);
        border: 1px solid var(--eventos-calendar-border);
        border-radius: 1.15rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    #eventosCalendar {
        min-height: 780px;
    }

    .fc .fc-toolbar.fc-header-toolbar {
        margin-bottom: 1.25rem;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .fc .fc-toolbar-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--eventos-calendar-text);
        text-transform: capitalize;
    }

    .fc .fc-button {
        background: var(--eventos-calendar-panel) !important;
        border: 1px solid var(--eventos-calendar-border) !important;
        color: var(--eventos-calendar-muted) !important;
        border-radius: .85rem !important;
        box-shadow: none !important;
        padding: .55rem .95rem !important;
        font-weight: 700 !important;
        text-transform: capitalize !important;
    }

    .fc .fc-button:hover,
    .fc .fc-button.fc-button-active {
        background: #00686f !important;
        border-color: #00686f !important;
        color: #fff !important;
    }

    .fc .fc-button-group {
        display: inline-flex;
        gap: .55rem;
    }

    .fc .fc-button-group > .fc-button {
        margin: 0 !important;
        border-radius: .85rem !important;
    }

    .fc .fc-scrollgrid,
    .fc .fc-daygrid-day-frame,
    .fc .fc-timegrid-slot,
    .fc .fc-timegrid-axis {
        border-color: var(--eventos-calendar-border) !important;
    }

    .fc .fc-scrollgrid,
    .fc-theme-standard td,
    .fc-theme-standard th,
    .fc .fc-list {
        border-color: var(--eventos-calendar-border) !important;
    }

    .fc .fc-daygrid-day,
    .fc .fc-timegrid-col,
    .fc .fc-list-day-cushion,
    .fc .fc-list-table td,
    .fc .fc-timegrid-axis,
    .fc .fc-timegrid-slot-label,
    .fc .fc-daygrid-body,
    .fc .fc-daygrid-bg-harness {
        background: var(--eventos-calendar-panel) !important;
    }

    .fc .fc-day-other .fc-daygrid-day-frame,
    .fc .fc-day-other,
    .fc .fc-timegrid-col.fc-day-today.fc-timegrid-col-frame,
    .fc .fc-non-business {
        background: var(--eventos-calendar-panel-strong) !important;
    }

    .fc .fc-col-header-cell {
        background: var(--eventos-calendar-panel-strong);
        color: var(--eventos-calendar-muted);
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: .75rem 0;
    }

    .fc .fc-daygrid-day-number,
    .fc .fc-timegrid-axis-cushion,
    .fc .fc-timegrid-slot-label-cushion,
    .fc .fc-list-day-text,
    .fc .fc-list-day-side-text {
        color: var(--eventos-calendar-muted);
        font-weight: 700;
    }

    .fc .fc-day-today {
        background: var(--eventos-calendar-today) !important;
    }

    .fc .fc-list-event:hover td,
    .fc .fc-timegrid-col:hover,
    .fc .fc-daygrid-day-frame:hover {
        background: var(--eventos-calendar-list-hover) !important;
    }

    .fc .fc-list-event-title a,
    .fc .fc-list-event-time,
    .fc .fc-list-day-text,
    .fc .fc-list-day-side-text,
    .fc .fc-col-header-cell-cushion {
        color: var(--eventos-calendar-text) !important;
    }

    .fc .fc-event {
        border: 0 !important;
        border-radius: .85rem !important;
        padding: .2rem .25rem !important;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        cursor: pointer;
    }

    .evento-calendar-chip {
        display: flex;
        flex-direction: column;
        gap: .12rem;
        padding: .3rem .45rem;
        line-height: 1.2;
    }

    .evento-calendar-chip small {
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .04em;
        opacity: .85;
        text-transform: uppercase;
    }

    .evento-calendar-chip strong {
        font-size: .78rem;
        font-weight: 800;
        white-space: normal;
    }

    .evento-status-inline {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        margin-left: .4rem;
        padding: .12rem .45rem;
        border-radius: 999px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .02em;
        vertical-align: middle;
        white-space: nowrap;
    }

    .evento-status-active {
        background: rgba(16, 185, 129, .18);
        color: #ecfdf5;
    }

    .evento-status-pending {
        background: rgba(245, 158, 11, .22);
        color: #fffbeb;
    }

    .evento-status-inactive {
        background: rgba(239, 68, 68, .22);
        color: #fef2f2;
    }

    .evento-status-neutral {
        background: rgba(148, 163, 184, .26);
        color: #f8fafc;
    }

    .eventos-empty-state {
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .evento-modal-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .85rem;
        margin-bottom: 1.25rem;
    }

    .evento-modal-summary-item {
        border: 1px solid var(--eventos-calendar-border);
        border-radius: 1rem;
        background: var(--eventos-calendar-panel);
        padding: .95rem 1rem;
    }

    .evento-modal-summary-item span {
        display: block;
        color: var(--eventos-calendar-muted);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: .2rem;
    }

    .evento-modal-summary-item strong {
        color: var(--eventos-calendar-text);
        font-size: .92rem;
        line-height: 1.35;
    }

    .evento-modal-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {
        .eventos-calendar-shell {
            padding: 1rem;
        }

        .eventos-calendar-toolbar {
            padding: 1rem 1rem 0;
        }

        #eventosCalendar {
            min-height: 620px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.05rem;
        }
    }

    .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* imágenes dentro del modal: que nunca se salgan de la ventana */
    #modalViewEvento .modal-body {
        padding-bottom: 1rem;
    }

    #modalViewEvento .modal-cover-img,
    #modalViewEvento .modal-gallery-img {
        max-height: 60vh;
        /* ajusta a gusto */
        object-fit: contain;
    }

    #map_new {
        flex-grow: 1;
        /* Permitir que el mapa ocupe el espacio disponible */
        width: 100%;
        /* Ancho completo */
        min-height: 300px;
        /* Altura mínima */
    }

    #mapaEntrega {
        width: 100%;
        height: 400px;
        /* o 55vh, lo que prefieras */
        border-radius: 8px;
    }


    .map-wrapper {
        position: relative;
    }

    /* Ubica el input arriba a la derecha del mapa */
    .map-search {
        position: absolute;
        top: 0px;
        right: 110px;
        /* <- antes estaba left */
        left: auto;
        /* <- importante para soltar el anclaje izquierdo */
        z-index: 2000;
    }

    /* Para que el autocomplete siempre quede visible */
    .pac-container {
        z-index: 20000 !important;
    }

    /* (Opcional) en pantallas pequeñas que no quede cortado */
    @media (max-width: 576px) {
        .map-search {
            left: 8px;
            right: 8px;
        }

        /* se centra con margen a ambos lados */
        #buscarDireccion {
            width: 100%;
        }
    }
</style>
<title>Eventos</title>

<div class="card mb-3">

    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Eventos</h5>
            </div>
            <div class="col-8 col-sm-auto text-end ps-2">
                <div id="table-customers-replace-element">
                    <button onclick="addEventos()" class="btn btn-falcon-default btn-sm" type="button">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    <div class="card-body border-bottom bg-light py-3">
        <div class="d-flex align-items-start gap-2">
            <span class="fas fa-image text-primary mt-1"></span>
            <div class="fs-10 text-700">
                Para la portada del evento, usa de preferencia una imagen horizontal de aproximadamente
                <strong>1200 x 675 px</strong> o similar, y un peso recomendado de hasta <strong>2 MB</strong>
                para que cargue mejor en la plataforma.
            </div>
        </div>
    </div>


    <div class="card-body p-0">
        <div class="eventos-calendar-toolbar">
            <div class="eventos-calendar-intro">
                <h4>Calendario de eventos</h4>
                <p>
                    Visualiza tus eventos por fecha, identifica rápidamente su duración entre inicio y fin,
                    y abre cada uno para revisar toda la información desde un solo lugar.
                </p>
            </div>
            <div class="eventos-calendar-legend">
                <span><i style="background:#00686f;"></i> Evento programado</span>
                <span><i style="background:#0ea5e9;"></i> Inicio y fin visibles</span>
                <span><i style="background:#f59e0b;"></i> Click para ver detalle</span>
            </div>
        </div>
        <div class="eventos-calendar-shell">
            <div class="eventos-calendar-card">
                <div id="eventosCalendar"></div>
            </div>
        </div>
    </div>
</div>


 

<?php
require 'includes/footer.php';
?>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&loading=async&callback=onMapsReady" async defer></script>
<script src="../theme/public/vendors/fullcalendar/index.global.min.js"></script>
<!-- Conexión API js -->
<script src="js/eventos.js?v1.0.0.0.0.0.2.6"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
 
