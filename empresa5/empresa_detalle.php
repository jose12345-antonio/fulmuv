<?php
$menu = "empresas";
require 'includes/header.php';
$id_empresa_detalle = (int)($_GET["id_empresa"] ?? $id_empresa ?? 0);
echo "<input type='hidden' id='id_empresa_detalle' value='" . $id_empresa_detalle . "'>";
// if(!$membresia){
//   echo "<script>window.location.href = 'asignar_membresia.php?id_empresa=".$id_empresa_detalle."'</script>";
// }
// if (!in_array($rol_id, [1, 2])) {
//     echo "<script>window.location.href = '" . $dashboard . "'</script>";
// }
?>
<style>
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

    .dashboard-kpi .card {
        border-radius: 12px;
        border: 1px solid #eef2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .dashboard-list .list-item {
        padding: 8px 10px;
        border-radius: 10px;
        border: 1px solid #eef2f7;
        background: #fff;
    }

    .dashboard-list .list-item .title {
        max-width: 72%;
    }

    .dashboard-list .list-item .badge-date {
        background: #f1f5f9;
        color: #475569;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 11px;
    }
</style>
<title>Empresa Detalle</title>
<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-light">
                Documentos de Políticas de Seguridad
            </div>
            <div class="card-body text-center p-1">
                <a href="../documentos/6_1_Términos y Condiciones de Uso, Política de Privacidad, Aviso Legal y Descargos de Responsabilidad para Proveedores de FULMUV.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">T&C para Proveedores</a>
                <a href="../documentos/6_2_Política de Privacidad y Cookies de FULMUV.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">Privacidad y Cookies</a>
                <a href="../documentos/6_3_Aviso Legal y Descargos de Responsabilidad de FULMUV.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">Aviso Legal</a>
                <a href="../documentos/6_4_NW_Términos_y_Condiciones_de_Envíos_y_Logística_de_FULMUV.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">Condiciones de Envíos y Logística Proveedores</a>
                <a href="../documentos/6_5_NW_Instrucciones_de_Empaque_y_Embalaje_y_Etiquetado_para_Repuestos_y_Accesorios_Vehiculares.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">Instrucciones de Empaque, Embalaje y Etiquetado</a>
                <a href="../documentos/6_6_Condiciones Pago en Línea de FULMUV.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">Condiciones de Pago en Línea</a>
                <a href="../documentos/6_7_NW_Checklist_Imprimible_para_el_Vendedor.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">Checklist Imprimible para el Vendedor</a>
                <a href="../documentos/6_8_NW_Guía_de_Empaquetado_y_Envío_a_Domicilio.pdf" target="_blank" class="btn btn-outline-primary btn-sm mb-1 me-1">Guía de Empaquetado y Envío a Domicilio</a>
            </div>
        </div>
    </div>
</div>
<div class="row g-1 mb-2">
    <div class="col-lg-5 col-xxl-4">
        <div class="card h-100">
            <div class="card-header bg-body-tertiary d-flex flex-between-center py-1">
                <h6 class="mb-0">Detalles</h6>
            </div>
            <div class="card-body p-1">
                <div class="row g-1">
                    <div class="col-sm-6 col-lg-12 text-center">
                        <div class="avatar avatar-5xl">
                            <img class="rounded-3 border" src="../img/FULMUV-LOGO-60X60.png" onerror="this.onerror=null;this.src='../img/FULMUV-LOGO-60X60.png';" alt="Imagen empresa" id="imagen_empresa" />
                            <input class="d-none" id="profile-image" type="file">
                            <label class="mb-0 overlay-icon d-flex flex-center" for="profile-image">
                                <span class="bg-holder overlay overlay-0"></span>
                                <span class="z-1 text-white dark__text-white text-center fs-10">
                                    <span class="fas fa-camera"></span>
                                    <span class="d-block">actualizar</span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-12">

                        <!-- Nombre + verificación -->
                        <div class="d-flex align-items-start justify-content-between mb-1">
                            <div>
                                <div class="text-700 fs-10">Nombre</div>
                                <h6 class="mb-0" id="nombre_empresa"></h6>
                            </div>

                            <img id="img_verificacion"
                                src=""
                                alt="Verificación"
                                style="width:40px;height:40px;object-fit:contain;"
                                title="Estado de verificación">
                        </div>

                        <!-- Dirección -->
                        <div class="mb-1">
                            <div class="text-700 fs-10">Dirección</div>
                            <div class="text-600 fs-10" id="direccion_empresa"></div>
                        </div>

                        <?php
                        if ($tipo_user != "sucursal") {
                        ?>
                            <!-- Membresía -->
                            <div class="p-1 rounded-3 border bg-body mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-700 fs-10 mb-1">Membresía</div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <span class="badge bg-primary-subtle text-primary" id="membresia_nombre">--</span>
                                            <span class="badge bg-secondary-subtle text-secondary" id="membresia_tipo">--</span>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="text-700 fs-10 mb-1">Caduca</div>
                                        <div class="text-600 fs-10" id="membresia_fecha_fin">--</div>
                                    </div>
                                </div>

                                <hr class="my-1">

                                <!-- Días restantes -->
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="text-700 fs-10">Días de Servicio</div>
                                    <div class="fw-semi-bold fs-10" id="membresia_dias_restantes">--</div>
                                </div>

                                <!-- Barra progreso -->
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar" role="progressbar" id="membresia_progress" style="width: 0%"></div>
                                </div>

                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-600" id="membresia_estado_texto">--</small>
                                    <small class="text-600" id="membresia_rango_texto"></small>
                                </div>
                            </div>

                        <?php
                        }
                        ?>

                    </div>

                </div>
            </div>
            <div class="card-footer bg-body-tertiary py-2">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="editar_empresa.php?id_empresa=<?php echo $id_empresa_detalle ?>"
                        class="btn btn-falcon-default btn-sm">
                        Completar datos
                    </a>

                    <?php
                    if ($tipo_user != "sucursal") {
                    ?>
                        <a href="upgrade_membresia.php?id_empresa=<?php echo $id_empresa_detalle ?>"
                            class="btn btn-falcon-primary btn-sm">
                            Upgrade membresía
                        </a>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-5 order-xxl-1 order-lg-3 order-2">
        <div class="card h-100 font-sans-serif">
            <div class="card-header bg-body-tertiary d-flex flex-between-center py-2">
                <h6 class="mb-0">Ubicación</h6>
            </div>
            <div class="card-body p-1">
                <div id="map_new" class="h-100">

                </div>
            </div>
            <div class="card-footer bg-body-tertiary py-2">
                <div class="row justify-content-between">
                    <div class="col-auto">
                        <a onclick="abrirMapa()" class="btn btn-falcon-default btn-sm" type="button">Actualizar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-lg-7 col-xxl-3 order-xxl-2 order-lg-2 order-1">
        <div class="card font-sans-serif">
            <div class="card-header bg-body-tertiary d-flex flex-between-center py-2">
                <h6 class="mb-0">Actividad Reciente</h6>
            </div>

            <!-- <div class="card-body scrollbar recent-activity-body-height ps-2" id="notesLogAll">
            </div>
 -->
            <div class="card-body py-0 scrollbar-overlay recent-activity-body-height">
                <div class="timeline-simple" id="notesLogAll">

                </div>
            </div>
            <div class="card-footer bg-body-tertiary py-2">
                <div class="row justify-content-between">
                    <div class="col-auto">
                        <select class="form-select form-select-sm" onchange="getNotas(value)">
                            <option value="D" selected="selected">Hoy</option>
                            <option value="S">Última semana</option>
                            <option value="M">Último mes</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3" id="dashboardEmpresa">
    <div class="col-12">
        <div class="card h-100">
            <div class="card-header bg-body-tertiary d-flex flex-between-center py-2">
                <h6 class="mb-0">Resumen Operativo</h6>
                <span class="text-600 fs-10">Actualizado automáticamente</span>
            </div>
            <div class="card-body p-2">
                <div class="row g-2 dashboard-kpi" id="empresaCounters">
                    <div class="col-6 col-md-4 col-xl-2" data-section="productos">
                        <div class="card h-100 shadow-none">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary"><span class="fas fa-box"></span></span>
                                    <div>
                                        <div class="text-600 fs-10">Productos</div>
                                        <div class="fw-semi-bold fs-7" id="count_productos">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2" data-section="servicios">
                        <div class="card h-100 shadow-none">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info-subtle text-info"><span class="fas fa-concierge-bell"></span></span>
                                    <div>
                                        <div class="text-600 fs-10">Servicios</div>
                                        <div class="fw-semi-bold fs-7" id="count_servicios">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2" data-section="vehiculos">
                        <div class="card h-100 shadow-none">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success"><span class="fas fa-car"></span></span>
                                    <div>
                                        <div class="text-600 fs-10">Vehículos</div>
                                        <div class="fw-semi-bold fs-7" id="count_vehiculos">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2" data-section="ordenes">
                        <div class="card h-100 shadow-none">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-warning-subtle text-warning"><span class="fas fa-receipt"></span></span>
                                    <div>
                                        <div class="text-600 fs-10">Órdenes</div>
                                        <div class="fw-semi-bold fs-7" id="count_ordenes">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2" data-section="eventos">
                        <div class="card h-100 shadow-none">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary-subtle text-secondary"><span class="fas fa-calendar-alt"></span></span>
                                    <div>
                                        <div class="text-600 fs-10">Eventos</div>
                                        <div class="fw-semi-bold fs-7" id="count_eventos">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2" data-section="empleos">
                        <div class="card h-100 shadow-none">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger-subtle text-danger"><span class="fas fa-briefcase"></span></span>
                                    <div>
                                        <div class="text-600 fs-10">Empleos</div>
                                        <div class="fw-semi-bold fs-7" id="count_empleos">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mt-1" id="dashboardChartsRow">
                    <div class="col-lg-8" data-section="chart">
                        <div class="border rounded-3 p-2 h-100">
                            <div class="d-flex flex-between-center mb-1">
                                <div class="text-700 fs-10">Distribución de contenidos</div>
                                <span class="text-600 fs-10">Últimos datos</span>
                            </div>
                            <div id="empresaDashboardChart" style="height: 230px;"></div>
                        </div>
                    </div>
                    <div class="col-lg-4" data-section="summary">
                        <div class="border rounded-3 p-2 h-100">
                            <div class="text-700 fs-10 mb-2">Resumen rápido</div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-600 fs-10">Publicaciones activas</span>
                                <span class="fw-semi-bold fs-10" id="count_publicaciones">--</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-600 fs-10">Total órdenes</span>
                                <span class="fw-semi-bold fs-10" id="count_ordenes_resumen">--</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-600 fs-10">Última actualización</span>
                                <span class="fw-semi-bold fs-10" id="dashboard_updated_at">--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mt-2 dashboard-list" id="dashboardListsRow">
                    <div class="col-12 col-xl-4" data-section="productos">
                        <div class="border rounded-3 p-2 h-100">
                            <div class="text-700 fs-10 mb-2">Últimos productos</div>
                            <div id="list_recientes_productos" class="d-flex flex-column gap-1"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4" data-section="servicios">
                        <div class="border rounded-3 p-2 h-100">
                            <div class="text-700 fs-10 mb-2">Últimos servicios</div>
                            <div id="list_recientes_servicios" class="d-flex flex-column gap-1"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4" data-section="vehiculos">
                        <div class="border rounded-3 p-2 h-100">
                            <div class="text-700 fs-10 mb-2">Últimos vehículos</div>
                            <div id="list_recientes_vehiculos" class="d-flex flex-column gap-1"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4" data-section="eventos">
                        <div class="border rounded-3 p-2 h-100">
                            <div class="text-700 fs-10 mb-2">Últimos eventos</div>
                            <div id="list_recientes_eventos" class="d-flex flex-column gap-1"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4" data-section="empleos">
                        <div class="border rounded-3 p-2 h-100">
                            <div class="text-700 fs-10 mb-2">Últimos empleos</div>
                            <div id="list_recientes_empleos" class="d-flex flex-column gap-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-5 col-xxl-4" id="colUsuarios">
        <div class="card h-100">
            <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                <h6 class="mb-0">Usuarios</h6>
                <div class="dropdown font-sans-serif btn-reveal-trigger">
                    <button onclick="addUsuario()" class="btn btn-falcon-default btn-sm" type="button">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Nuevo Usuario</span>
                    </button>
                </div>
            </div>
            <div class="card-body py-2 scrollbar-overlay recent-activity-body-height">
                <div class="timeline-simple" id="listaUsuarios">

                </div>
            </div>
            <div class="card-footer bg-body-tertiary p-0">
                <a class="btn btn-sm btn-link d-block w-100 py-2" href="usuarios.php">Ver todos<span class="fas fa-chevron-right ms-1 fs-11"></span>
                </a>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7 col-xxl-8" id="viewOrdenesRecientes">
        <div class="card shadow-none h-100">
            <div class="card-header">
                <div class="row flex-between-center">
                    <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                        <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Ordenes Recientes</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="falcon-data-table">
                    <table class="table table-sm mb-0 data-table fs-10" id="my_table2">
                        <thead class="bg-200">
                            <tr>
                                <th class="text-900 sort pe-1 align-middle white-space-nowrap" data-sort="order">Orden</th>
                                <th class="text-900 sort pe-1 align-middle white-space-nowrap pe-7" data-sort="date">Fecha</th>
                                <!-- <th class="text-900 sort pe-1 align-middle white-space-nowrap" data-sort="address" style="min-width: 12.5rem;">Descripcion</th> -->
                                <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center" data-sort="status">Estado</th>
                                <th class="text-900 sort pe-1 align-middle white-space-nowrap text-end" data-sort="amount">Valor</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody class="" id="lista_ordenes">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="show_notes_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Actividad Reciente</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>

            <div class="card h-100">

                <div class="card-body scrollbar recent-activity-body-height ps-2" id="notesLog">


                </div>

                <div class="card-footer">
                    <div class="border rounded">
                        <form action="#" class="comment-area-box">
                            <textarea rows="2" class="form-control border-0 resize-none" id="comment" placeholder="Tu comentario..."></textarea>
                            <div class="p-2 bg-light text-end" id="div_submit">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Mapa (fuera del modal de crear empresa) -->
<div class="modal fade" id="modalMapa" tabindex="-1" aria-labelledby="modalMapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMapaLabel">Selecciona una ubicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="map-wrapper position-relative">
                    <input type="hidden" id="direccion_mapa">
                    <div id="mapaEntrega"></div>

                    <div class="map-search">
                        <div class="input-group">
                            <input id="buscarDireccion" class="form-control form-control-sm"
                                style="width: clamp(200px, 39vw, 400px); margin-top:10px; background:#fff; height:40px"
                                placeholder="Buscar dirección..." />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="guardarUbicacion">Guardar dirección</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.__mapsReadyQueue = [];

    function onMapsReady() {
        (window.__mapsReadyQueue || []).forEach(fn => {
            try {
                fn();
            } catch (e) {}
        });
        window.__mapsReadyQueue = [];
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&loading=async&callback=onMapsReady" async defer></script>
<script src="js/empresa_detalle.js?v1.0.0.0.0.0.0.0.0.0.13"></script>

<!-- Alerts js -->
<script src="js/alerts.js"></script>
<div id="alertMapa"></div>
<?php
require 'includes/footer.php';
?>
