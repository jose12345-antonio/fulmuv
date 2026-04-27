<?php
$menu = "empleos";
$sub_menu = "empleos";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>


<style>
    /* Solo estética para indicar desactivado */
    .opacity-50 {
        opacity: .55;
    }

    /* Contenedor fijo para la imagen */
    .product-img-wrap {
        width: 100%;
        height: 200px;
        /* tamaño estándar */
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        /* opcional */
        object-fit: contain;
        /* si prefieres recortar: cover */
    }

    /* La imagen NO se estira, mantiene proporción */
    .product-img-wrap img {
        width: 100%;
        height: 100%;
    }

    /* Responsive: baja un poco la altura en pantallas pequeñas */
    @media (max-width: 576px) {
        .product-img-wrap {
            height: 150px;
        }
    }
</style>



<title>Empleos</title>

<div class="card mb-3">

    <div class="card-header">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center">
                <div class="col-md-auto">
                    <h5 class="mb-2 mb-md-0">Empleos</h5>
                </div>

            </div>
            <div class="row flex-between-center gy-2 px-x1 mb-2" id="searh_empresa">
                <div class="col-auto pe-0">
                    <h6 class="mb-0">Empresa</h6>
                </div>
                <div class="col-auto">
                    <div class="input-group input-search-width">
                        <select class="form-select selectpicker" id="lista_empresas">
                        </select>
                    </div>
                </div>
            </div>
            <div class="row flex-between-center">
                <div class="col-auto">
                    <a class="btn btn-falcon-default btn-sm" type="button" href="crear_empleo.php">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-lg-4">
                <form class="position-relative" data-bs-toggle="search" data-bs-display="static">
                    <input class="form-control search-input fuzzy-search pe-5"
                        id="buscar_empleo"
                        type="search"
                        placeholder="Buscar..."
                        aria-label="Buscar"
                        oninput="filtrarEmpleosLive(this.value)">

                    <span class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></span>
                </form>

            </div>
        </div>
        <!-- <div class="falcon-data-table" id="tabla_contenido">
            
        </div> -->
        <div class="row" id="lista_empleos">

        </div>
    </div>
</div>



<div class="modal fade" id="modalCVRecibidos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cvModalTitle">CV recibidos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="cvLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border" role="status"></div>
                    <div class="mt-2">Cargando...</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Postulante</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCVRecibidos"></tbody>

                    </table>
                </div>

                <div id="cvEmpty" class="text-center text-muted py-4" style="display:none;">
                    No hay CVs recibidos para este empleo.
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Requiere Bootstrap 5 y (opcional) jQuery. También íconos de Bootstrap para el botón (+) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<!-- Conexión API js -->
<script src="js/empleos.js?1.0.0.0.0.0.0.0.0.0.0.0.8"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>