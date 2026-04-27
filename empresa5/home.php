<?php
$menu = "dashboard";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Dashboard" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
    .icon-circle {
        width: 2.5rem;
        height: 2.5rem;
    }
    #empresas_agrupadas {
        max-height: 150px; /* Ajusta la altura máxima según lo necesario */
        overflow-y:visible; /* Habilita el desplazamiento vertical */
        overflow-x: hidden;
    }
    #empresas_agrupadas::-webkit-scrollbar {
        display: none; /* Oculta el scroll en navegadores basados en WebKit como Chrome y Safari */
    }
</style>
<title>Home</title>
<div class="row mb-3 g-3">
    <div class="col-lg-12 col-xxl-9 h-100">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row" id="contadores">

                </div>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header d-flex flex-between-center py-3 border-bottom">
                <h6 class="mb-0">Órdenes</h6>
            </div>
            <div class="card-body py-3">
                <div class="row g-0">
                    <div class="col-6 col-md-4 border-200 border-bottom border-end pb-4">
                        <h6 class="pb-1 text-700">Creadas </h6>
                        <p class="font-sans-serif lh-1 mb-1 fs-5" id="total_creadas">0 </p>
                        <!-- <div class="d-flex align-items-center">
                            <h6 class="fs--1 text-500 mb-0">13,675 </h6>
                            <h6 class="fs--2 ps-3 mb-0 text-primary"><span class="me-1 fas fa-caret-up"></span>21.8%</h6>
                        </div> -->
                    </div>
                    <div class="col-6 col-md-4 border-200 border-bottom border-end-md pb-4 ps-3">
                        <h6 class="pb-1 text-700">Enviadas</h6>
                        <p class="font-sans-serif lh-1 mb-1 fs-5" id="total_enviadas">0 </p>
                        <!-- <div class="d-flex align-items-center">
                            <h6 class="fs--1 text-500 mb-0">13,675 </h6>
                            <h6 class="fs--2 ps-3 mb-0 text-warning"><span class="me-1 fas fa-caret-up"></span>21.8%</h6>
                        </div> -->
                    </div>
                    <div class="col-6 col-md-4 border-200 border-bottom border-end border-end-md-0 pb-4 pt-4 pt-md-0 ps-md-3">
                        <h6 class="pb-1 text-700">Procesadas </h6>
                        <p class="font-sans-serif lh-1 mb-1 fs-5" id="total_procesadas">0 </p>
                        <!-- <div class="d-flex align-items-center">
                            <h6 class="fs--1 text-500 mb-0">13,675 </h6>
                            <h6 class="fs--2 ps-3 mb-0 text-success"><span class="me-1 fas fa-caret-up"></span>21.8%</h6>
                        </div> -->
                    </div>
                    <div class="col-6 col-md-4 border-200 border-bottom border-bottom-md-0 border-end-md pt-4 pb-md-0 ps-3 ps-md-0">
                        <h6 class="pb-1 text-700">Aprobadas </h6>
                        <p class="font-sans-serif lh-1 mb-1 fs-5" id="total_aprobadas">0 </p>
                        <!-- <div class="d-flex align-items-center">
                            <h6 class="fs--1 text-500 mb-0">$109.65 </h6>
                            <h6 class="fs--2 ps-3 mb-0 text-danger"><span class="me-1 fas fa-caret-up"></span>21.8%</h6>
                        </div> -->
                    </div>
                    <div class="col-6 col-md-4 border-200 border-bottom-md-0 border-end pt-4 pb-md-0 ps-md-3">
                        <h6 class="pb-1 text-700">Completadas </h6>
                        <p class="font-sans-serif lh-1 mb-1 fs-5" id="total_completadas">0 </p>
                        <!-- <div class="d-flex align-items-center">
                            <h6 class="fs--1 text-500 mb-0">13,675 </h6>
                            <h6 class="fs--2 ps-3 mb-0 text-success"><span class="me-1 fas fa-caret-up"></span>21.8%</h6>
                        </div> -->
                    </div>
                    <div class="col-6 col-md-4 pb-0 pt-4 ps-3">
                        <h6 class="pb-1 text-700">Eliminadas </h6>
                        <p class="font-sans-serif lh-1 mb-1 fs-5" id="total_eliminadas">0 </p>
                        <!-- <div class="d-flex align-items-center">
                            <h6 class="fs--1 text-500 mb-0">13,675 </h6>
                            <h6 class="fs--2 ps-3 mb-0 text-info"><span class="me-1 fas fa-caret-up"></span>21.8%</h6>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="card-footer bg-body-tertiary p-0">
                <a class="btn btn-sm btn-link d-block py-2" href="#!">Ver órdenes
                    <span class="fas fa-chevron-right ms-1 fs-11"></span>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xxl-3">
        <div class="card h-100">
            <div class="card-header d-flex flex-between-center py-3 border-bottom">
                <h6 class="mb-0">Órdenes por empresas</h6>
                <!-- <div class="dropdown font-sans-serif btn-reveal-trigger">
                    <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal" type="button" id="dropdown-most-leads" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                        <span class="fas fa-ellipsis-h fs-11"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-most-leads"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Export</a>
                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#!">Remove</a>
                    </div>
                </div> -->
            </div>
            <div class="card-body justify-content-center pb-0">
                <div class="echart-session-example2 h-50" data-echart-responsive="true" style="min-height: 150px;"></div>
                <div class="row align-items-center">
                    <div class="col-xxl-12 col-md-12" id="empresas_agrupadas">
                        <hr class="mx-nx1 mb-0 d-md-none d-xxl-block">

                    </div>
                </div>
            </div>
            <div class="card-footer bg-body-tertiary p-0">
                <a class="btn btn-sm btn-link d-block py-2" href="#!">Ver empresas
                    <span class="fas fa-chevron-right ms-1 fs-11"></span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xxl-12">
        <div class="card h-100">
            <div class="card-header d-md-flex justify-content-between border-bottom border-200 py-3 py-md-2">
                <h6 class="mb-2 mb-md-0 py-md-2">Histórico de órdenes</h6>
                <div class="row g-md-0">
                    <div class="col-auto d-md-flex">
                        <div class="d-flex align-items-center me-md-3 form-check mb-0">
                            <input class="form-check-input form-check-input-secondary dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer" type="checkbox" data-number-of-tickets2="Creadas" value="" id="onHoldTickets" checked="checked" />
                            <label class="form-check-label lh-base mb-0 fs--2 text-500 fw-semi-bold font-base cursor-pointer" for="onHoldTickets">Creadas</label>
                        </div>
                        <div class="d-flex align-items-center me-md-3 form-check mb-0 mt-n1 mt-md-0">
                            <input class="form-check-input form-check-input-info dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer " type="checkbox" data-number-of-tickets2="Enviadas" value="" id="openTickets" checked="checked" />
                            <label class="form-check-label lh-base mb-0 fs--2 text-500 fw-semi-bold font-base cursor-pointer" for="openTickets">Enviadas</label>
                        </div>
                    </div>
                    <div class="col-auto d-md-flex">
                        <div class="d-flex align-items-center me-md-3 form-check mb-0">
                            <input class="form-check-input form-check-input-success dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer" type="checkbox" data-number-of-tickets2="Completadas" value="" id="dueTickets" checked="checked" />
                            <label class="form-check-label lh-base mb-0 fs--2 text-500 fw-semi-bold font-base cursor-pointer" for="dueTickets">Completadas</label>
                        </div>
                        <div class="d-flex align-items-center me-md-3 form-check mb-0">
                            <input class="form-check-input form-check-input-primary dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer" type="checkbox" data-number-of-tickets2="Procesadas" value="" id="unassignedTickets" checked="checked" />
                            <label class="form-check-label lh-base mb-0 fs--2 text-500 fw-semi-bold font-base cursor-pointer" for="unassignedTickets">Procesadas</label>
                        </div>
                    </div>
                    <div class="col-auto d-md-flex">
                        <div class="d-flex align-items-center me-md-3 form-check mb-0">
                            <input class="form-check-input form-check-input-warning dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer" type="checkbox" data-number-of-tickets2="Aprobadas" value="" id="dueTickets2" checked="checked" />
                            <label class="form-check-label lh-base mb-0 fs--2 text-500 fw-semi-bold font-base cursor-pointer" for="dueTickets2">Aprobadas</label>
                        </div>
                        <div class="d-flex align-items-center form-check mb-0 mt-n1 mt-md-0">
                            <input class="form-check-input form-check-input-danger dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer" type="checkbox" data-number-of-tickets2="Eliminadas" value="" id="unassignedTickets2" checked="checked" />
                            <label class="form-check-label lh-base mb-0 fs--2 text-500 fw-semi-bold font-base cursor-pointer" for="unassignedTickets2">Eliminadas</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- <div class="d-flex">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="fs-0 d-flex align-items-center text-700 mb-1">125<small class="badge text-success bg-transparent px-0"><span class="fas fa-caret-up fs--2 ms-2 me-1"></span><span>5.3%</span></small></h6>
                            <h6 class="text-600 mb-0 fs--2 text-nowrap">Total On Hold Tickets</h6>
                        </div>
                        <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="fs-0 d-flex align-items-center text-700 mb-1">100<small class="badge px-0 text-primary"><span class="fas fa-caret-up fs--2 ms-2 me-1"></span><span>3.20%</span></small></h6>
                            <h6 class="fs--2 text-600 mb-0 text-nowrap">Total Open Tickets</h6>
                        </div>
                        <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="fs-0 d-flex align-items-center text-700 mb-1">53<small class="badge px-0 text-warning"><span class="fas fa-caret-down fs--2 ms-2 me-1"></span><span>2.3%</span></small></h6>
                            <h6 class="fs--2 text-600 mb-0 text-nowrap">Total Due Tickets</h6>
                        </div>
                        <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                    </div>
                    <div>
                        <h6 class="fs-0 d-flex align-items-center text-700 mb-1">136<small class="badge px-0 text-danger"><span class="fas fa-caret-up fs--2 ms-2 me-1"></span><span>3.12%</span></small></h6>
                        <h6 class="fs--2 text-600 mb-0 text-nowrap">Total Unassigned Tickets</h6>
                    </div>
                </div> -->
                <div class="echart-number-of-tickets" data-echart-responsive="true"></div>
            </div>
            <div class="card-footer bg-light py-2">
                <div class="row flex-between-center">
                    <div class="col-auto">
                        <select id="quarterSelect" class="form-select form-select-sm" onchange="setQuarter()">
                            <option value="1">1° Cuarto</option>
                            <option value="2">2° Cuarto</option>
                            <option value="3">3° Cuarto</option>
                            
                        </select>
                    </div>
                    <!-- <div class="col-auto"><a class="btn btn-link btn-sm px-0" href="#!">View all reports<span class="fas fa-chevron-right ms-1 fs--2"></span></a></div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conexión API js -->
<script src="js/home.js"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>