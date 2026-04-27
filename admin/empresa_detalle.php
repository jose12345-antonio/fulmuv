<?php
$menu = "empresas";
require 'includes/header.php';
if (isset($_GET["id_empresa"]) && $rol_id == 1) {
    $id_empresa_detalle = $_GET["id_empresa"];
    $input_id_empresa = "<input type='hidden' id='id_empresa_detalle' value='$id_empresa_detalle' >";
} else {
    $input_id_empresa = "<input type='hidden' id='id_empresa_detalle' value='$id_empresa' >";
}
echo $input_id_empresa;
if (!in_array($rol_id, [1, 2])) {
    echo "<script>window.location.href = '" . $dashboard . "'</script>";
}
?>
<style>
    #map_new {
        flex-grow: 1; /* Permitir que el mapa ocupe el espacio disponible */
        width: 100%; /* Ancho completo */
        min-height: 300px; /* Altura mínima */
    }
</style>
<title>Empresa Detalle</title>
<div class="row g-3 mb-3">
    <div class="col-lg-5 col-xxl-3">
        <div class="card h-100">
            <div class="card-header bg-body-tertiary d-flex flex-between-center py-2">
                <h6 class="mb-0">Detalles</h6>
                <!-- <div class="dropdown font-sans-serif position-static d-inline-block btn-reveal-trigger">
                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal dropdown-caret-none" type="button" id="dropdown-payment-methods" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                    <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-payment-methods"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Edit</a>
                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#!">Delete</a>
                    </div>
                </div> -->
            </div>
            <div class="card-body">
                <div class="row g-3 h-100">
                    <div class="col-sm-6 col-lg-12 text-center">
                        <!-- <div class="avatar avatar-5xl">
                            <img class="rounded-3" src="" alt="" id="imagen_empresa" />
                        </div> --> 
                        <div class="avatar avatar-5xl">
                            <img class="rounded-3 border" src="" alt="" id="imagen_empresa" />
                            <input class="d-none" id="profile-image" type="file">
                            <label class="mb-0 overlay-icon d-flex flex-center" for="profile-image">
                                <span class="bg-holder overlay overlay-0"></span>
                                <span class="z-1 text-white dark__text-white text-center fs-10">
                                    <span class="fas fa-camera"></span>
                                    <span class="d-block">actualizar</span>
                                </span>
                            </label>
                        </div> 
                        <!-- <div class="avatar avatar-5xl avatar-profile shadow-sm img-thumbnail rounded-circle">
                            <div class="h-100 w-100 rounded-circle overflow-hidden position-relative"> 
                                <img id="imagen_empresa" width="200" alt="" data-dz-thumbnail="data-dz-thumbnail">
                                <input class="d-none" id="profile-image" type="file">
                                <label class="mb-0 overlay-icon d-flex flex-center" for="profile-image">
                                    <span class="bg-holder overlay overlay-0"></span>
                                    <span class="z-1 text-white dark__text-white text-center fs-10">
                                        <span class="fas fa-camera"></span>
                                        <span class="d-block">Update</span>
                                    </span>
                                </label>
                            </div>
                        </div> -->
                    </div>
                    <div class="col-sm-6 col-lg-12">
                        <table class="table table-borderless fw-medium font-sans-serif fs-10 mb-2">
                            <tbody>
                                <tr>
                                    <td class="p-1" style="width: 35%;">Nombre:</td>
                                    <td class="p-1 text-600" id="nombre_empresa"></td>
                                </tr>
                                <tr>
                                    <td class="p-1" style="width: 35%;">Dirección:</td>
                                    <td class="p-1 text-600" id="direccion_empresa"></td>
                                </tr>
                                <tr>
                                    <td class="p-1" style="width: 35%;">Tipo establecimiento:</td>
                                    <td class="p-1 text-600" id="tipo_establecimiento"></td>
                                </tr>
                                <tr>
                                    <td class="p-1" style="width: 35%;">Razón social:</td>
                                    <td class="p-1 text-600" id="razon_social"></td>
                                </tr>
                                <tr>
                                    <td class="p-1" style="width: 35%;">Membresía:</td>
                                    <td class="p-1 text-600" id="membresia"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
        </div>
    </div>

    <div class="col-md-12 col-lg-7 col-xxl-4 order-xxl-2 order-lg-2 order-1">
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

<div class="card overflow-hidden mb-3">
    <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
        <h6 class="mb-0">Sucursales</h6>
        <div class="dropdown font-sans-serif btn-reveal-trigger">
            <button class="btn btn-link btn-reveal text-600 btn-sm dropdown-toggle dropdown-caret-none" type="button" id="studentInfoDropdown" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                <span class="fas fa-ellipsis-h fs-11"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="studentInfoDropdown">
                <a class="dropdown-item" onclick="addSucursal()">Crear Sucursal</a>
                <!-- <a class="dropdown-item" href="#!">Enrolled Courses</a> -->
                <!-- <div class="dropdown-divider"></div> -->
                <!-- <a class="dropdown-item text-danger" href="#!">Logout</a> -->
            </div>
        </div>
    </div>
    <div class="card-header p-0 scrollbar border-bottom">
        <ul class="nav nav-tabs border-0 top-courses-tab flex-nowrap" role="tablist" id="sucursales">
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="areas">

        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-5 col-xxl-4">
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
    <div class="col-12 col-xl-7 col-xxl-8">
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
                            <!-- <tr class="btn-reveal-trigger">
                                <td class="align-middle" style="width: 28px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="number-pagination-item-0" data-bulk-select-row="data-bulk-select-row" />
                                    </div>
                                </td>
                                <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="">#123456</a></td>
                                <td class="date py-2 align-middle">20/04/2019</td>
                                <td class="align-middle white-space-nowrap product">Slick - Drag &amp; Drop Bootstrap Generator</td>
                                <td class="align-middle text-center fs-9 white-space-nowrap payment"><span class="badge badge rounded-pill badge-subtle-success">Entregada<span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span></span>
                                </td>
                                <td class="align-middle text-end amount">$99</td>
                                <td class="align-middle white-space-nowrap text-end">
                                    <div class="dropstart font-sans-serif position-static d-inline-block">
                                        <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-number-pagination-table-item-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-number-pagination-table-item-0">
                                            <a class="dropdown-item" href="#!">Ver</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#!">Eliminar</a>
                                        </div>
                                    </div>
                                </td>
                            </tr> -->
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
            <!-- <div class="modal-body bg-body"> -->
            <!-- <div class="col-12"> -->
            <!-- <div class="card">
                        <div class="card-body pt-1">

                            <div class="row px-3" data-simplebar style="max-height: 403px;" id="simplebarScroll">
                                <div class="col" id="notesLog">
                                    <div class="d-flex mt-1 p-1">
                                        <img src="../theme/assets/images/users/avatar-0.png" class="me-2 rounded-circle" height="36" />
                                        <div class="w-100">
                                            <h5 class="mt-0 mb-0">
                                                <span class="float-end text-muted font-12">4:30am</span>
                                                Joseph Test
                                            </h5>
                                            <p class="mt-1 mb-0 text-muted">
                                                Should I review the last 3 years legal documents as well?
                                            </p>
                                        </div>
                                    </div>
                                    <hr />

                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col">
                                    <div class="border rounded">
                                        <form action="#" class="comment-area-box">
                                            <textarea rows="3" class="form-control border-0 resize-none" id="comment" placeholder="Your comment..."></textarea>
                                            <div class="p-2 bg-light text-end" id="div_submit">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
            <!-- </div> -->

            <div class="card h-100">
                <!-- <div class="card-header">
                        <h6 class="mb-0">Recent Activity</h6>
                    </div> -->
                <div class="card-body scrollbar recent-activity-body-height ps-2" id="notesLog">

                    <!-- <div class="row g-3 timeline timeline-primary timeline-past pb-x1">
                        <div class="col-auto ps-4 ms-2">
                            <div class="ps-2">
                                <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none"><span class="text-primary fas fa-envelope"></span></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row gx-0 border-bottom pb-x1">
                                <div class="col">
                                    <h6 class="text-800 mb-1">Antony Hopkins sent an Email</h6>
                                    <p class="fs-10 text-600 mb-0">Got an email for previous year sale report</p>
                                </div>
                                <div class="col-auto">
                                    <p class="fs-11 text-500 mb-0">2m ago</p>
                                </div>
                            </div>
                        </div>
                    </div> -->
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
<!-- Inicializar mapa -->
<!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&callback=initMap" async defer></script> -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg"></script>
<!-- Conexión API js -->
<script src="js/empresa_detalle.js?v1.0.42"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>