<?php
$menu = "ordenes";
$sub_menu = "crear_orden";
$hide_filter_bar = true;
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Ordenes" && ($value["valor"] == "false" || $value["levels"] == "GrupoIso")) {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<title>Carrito</title>
<div class="row gx-3">
    <div class="col-xxl-10 col-xl-10">
        <div class="card" id="ticketsTable">
            <div class="card-header border-bottom border-200 px-0">
                 
                <div class="row px-x1 mb-2">
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="col-lg-12 pe-0">
                            <h6 class="mb-0">Productos</h6>
                        </div>
                        <div class="col-lg-12">
                            <div class="input-group">
                                <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar" aria-label="search" />
                                <button class="btn btn-sm btn-outline-secondary border-300 hover-border-secondary"><span class="fa fa-search fs-10"></span></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12" id="searh_empresa">
                        <div class="col-lg-12 pe-0">
                            <h6 class="mb-0">Empresa</h6>
                        </div>
                        <div class="col-lg-12">
                            <div class="input-group">
                                <select class="form-select selectpicker" id="lista_empresas" onchange="buscarSucursales()">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12" id="searh_sucursal">
                        <div class="col-lg-12 pe-0">
                            <h6 class="mb-0">Sucursal</h6>
                        </div>
                        <div class="col-lg-12">
                            <div class="input-group">
                                <select class="form-select selectpicker" id="lista_sucursales" onchange="getCatalogo(value)">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12" id="searh_catalogo">
                        <div class="col-lg-12 pe-0">
                            <h6 class="mb-0">Catálogos</h6>
                        </div>
                        <div class="col-lg-12">
                            <div class="input-group">
                                <select class="form-select selectpicker" id="lista_catalogos" onchange="getCatalogoId(value)">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="card-body">
                <div class="list row" id="lista_productos">
                </div>
                <div class="text-center d-none" id="tickets-card-fallback">
                    <p class="fw-bold fs-8 mt-3">No se encontraron productos</p>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    <button class="btn btn-sm btn-falcon-default me-1" type="button" title="Previous" data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
                    <ul class="pagination mb-0"></ul>
                    <button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next" data-list-pagination="next"><span class="fas fa-chevron-right"></span></button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-2 col-xl-2">
        <div class="offcanvas offcanvas-end offcanvas-filter-sidebar border-0 bg-body-quaternary h-auto rounded-xl-3" tabindex="-1" id="ticketOffcanvas" aria-labelledby="ticketOffcanvasLabelCard">
            <div class="offcanvas-header d-flex flex-between-center d-xl-none bg-body-tertiary">
                <h6 class="fs-9 mb-0 fw-semi-bold">Filter</h6>
                <button class="btn-close text-reset d-xl-none shadow-none" id="ticketOffcanvasLabelCard" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="card scrollbar shadow-none shadow-show-xl">
                <div class="card-header bg-body-tertiary d-none d-xl-block">
                    <h6 class="mb-0">Filtro</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="mb-1 mt-2">Categorías</label>
                            <select class="form-select form-select-sm" id="categorias" onchange="llenarSubCategria()">
                                <option value="-1">Todas</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="mb-1 mt-2">Sub-categorías</label>
                            <select class="form-select form-select-sm" id="sub_categorias" onchange="filtrarCategoria()">
                                <option value="-1">Todas</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/crear_orden_new.js"></script>

<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>