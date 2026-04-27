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
    .empleos-hero-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(0, 104, 111, 0.08), rgba(255, 255, 255, 0.98));
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    .empleos-toolbar-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .empleos-search-box .form-control,
    .empleos-search-box .form-select {
        border-radius: 12px;
        min-height: 44px;
    }

    .empleo-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .empleo-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
    }

    .empleo-card-image {
        position: relative;
        height: 220px;
        overflow: hidden;
        background: #f8fafc;
    }

    .empleo-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .empleo-card-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 2;
        background: rgba(15, 23, 42, 0.78);
        color: #fff;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        letter-spacing: .04em;
        text-transform: uppercase;
        backdrop-filter: blur(6px);
    }

    .empleo-card-title {
        font-size: 1rem;
        line-height: 1.4;
        min-height: 44px;
    }

    .empleo-card-meta {
        color: #64748b;
        font-size: 13px;
    }

    .empleo-card-actions .btn {
        border-radius: 10px;
    }

    @media (max-width: 576px) {
        .empleo-card-image {
            height: 180px;
        }
    }
</style>



<title>Empleos</title>

<div class="card empleos-hero-card mb-3">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill text-bg-light border border-200 text-dark mb-3">Panel de empleos</span>
                <h3 class="mb-2">Gestiona tus vacantes desde un solo lugar</h3>
                <p class="text-700 mb-0">Publica, revisa postulaciones y mantén cada empleo actualizado con una presentación más clara y profesional para tu equipo.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-primary" type="button" href="crear_empleo.php">
                    <span class="fas fa-plus me-1"></span>
                    Crear empleo
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card empleos-toolbar-card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4 empleos-search-box">
                <label class="form-label mb-1" for="buscar_empleo">Buscar empleo</label>
                <div class="position-relative" data-bs-toggle="search" data-bs-display="static">
                    <input class="form-control search-input fuzzy-search pe-5"
                        id="buscar_empleo"
                        type="search"
                        placeholder="Título, provincia o cantón..."
                        aria-label="Buscar"
                        oninput="filtrarEmpleosLive(this.value)">
                    <span class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></span>
                </div>
            </div>

            <div class="col-lg-4 empleos-search-box" id="searh_empresa">
                <label class="form-label mb-1" for="lista_empresas">Empresa</label>
                <select class="form-select selectpicker" id="lista_empresas"></select>
            </div>

            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-falcon-default" type="button" href="crear_empleo.php">
                    <span class="fas fa-plus me-1"></span>
                    Nueva vacante
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row" id="lista_empleos"></div>



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
<script src="js/empleos.js?1.0.0.0.0.0.0.0.0.0.0.0.11"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
