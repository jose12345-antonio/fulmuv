<?php
$menu = "empleos";
$sub_menu = "crear_empleo";
$hide_filter_bar = true;
require 'includes/header.php';

foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}

$id_empleo = isset($_GET['id_empleo']) ? intval($_GET['id_empleo']) : 0;
?>
<title><?= ($id_empleo > 0) ? 'Editar empleo' : 'Crear empleo' ?></title>

<style>
    .empleo-editor-hero {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(0, 104, 111, 0.08), rgba(255, 255, 255, 0.98));
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    .empleo-editor-card {
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 16px 38px rgba(15, 23, 42, 0.06);
    }

    .empleo-image-preview {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #f8fafc;
        margin-top: 10px;
    }

    .empleo-image-help {
        color: #64748b;
        font-size: 12px;
        margin-top: 6px;
    }

    .empleo-sidebar-note {
        border-radius: 16px;
        background: #0f172a;
        color: #fff;
    }
</style>

<div class="card empleo-editor-hero mb-3">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill text-bg-light border border-200 text-dark mb-3"><?= ($id_empleo > 0) ? 'Edición de empleo' : 'Nueva vacante' ?></span>
                <h3 class="mb-2"><?= ($id_empleo > 0) ? 'Actualiza el contenido visual y descriptivo del empleo' : 'Crea una vacante con una presentación más atractiva' ?></h3>
                <p class="text-700 mb-0"><?= ($id_empleo > 0) ? 'Estás editando un empleo existente. Aquí puedes identificarlo por su ID, revisar sus imágenes actuales y actualizar el contenido con más seguridad.' : 'Completa la información principal, carga imágenes claras y adjunta archivos para que el empleo quede listo para publicarse.' ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="small text-600 mb-1">Identificador</div>
                <div class="fs-5 fw-bold text-primary"><?= ($id_empleo > 0) ? ('#' . $id_empleo) : 'Nuevo registro' ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-0">
    <div class="col-lg-8 pe-lg-2">
        <input type="hidden" id="id_empleo" value="<?= $id_empleo ?>">

        <div class="card empleo-editor-card mb-3">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Registro de Empleo</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="row gx-2">

                        <div class="col-12 mb-3">
                            <label class="form-label" for="titulo">Título <span class="text-danger">*</span></label>
                            <input class="form-control" id="titulo" type="text" required />
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Descripción <span class="text-danger">*</span></label>
                            <div class="create-product-description-textarea">
                                <textarea class="tinymce" data-tinymce="data-tinymce" id="descripcion" required></textarea>
                            </div>
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label" for="img_frontal">Imagen frontal <span class="text-danger">*</span></label>
                            <input class="form-control" id="img_frontal" type="file" accept="image/*" required />
                            <input type="hidden" id="img_frontal_old">
                            <div class="empleo-image-help">Portada principal visible en la tarjeta del empleo.</div>
                            <img id="preview_frontal" class="empleo-image-preview" style="display:none;">
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label" for="img_posterior">Imagen posterior <span class="text-danger">*</span></label>
                            <input class="form-control" id="img_posterior" type="file" accept="image/*" required />
                            <input type="hidden" id="img_posterior_old">
                            <div class="empleo-image-help">Imagen secundaria para reforzar el contenido del empleo.</div>
                            <img id="preview_posterior" class="empleo-image-preview" style="display:none;">
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Dropzone -->
        <div class="card empleo-editor-card mb-3">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Archivos (Imagen / PDF)</h6>
            </div>
            <div class="card-body">
                <form class="dropzone dropzone-multiple p-0" id="myAwesomeDropzone">
                    <div class="fallback">
                        <input name="file" type="file" multiple="multiple" />
                    </div>

                    <div class="dz-message my-0" data-dz-message="data-dz-message">
                        <img class="me-2" src="../theme/public/assets/img/icons/cloud-upload.svg" width="25" alt="" />
                        <span class="d-none d-lg-inline">Arrastra aquí<br />o, </span>
                        <span class="btn btn-link p-0 fs-10">Buscar</span>
                    </div>

                    <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column" id="file-previews">
                        <div class="d-flex media align-items-center mb-3 pb-3 border-bottom btn-reveal-trigger">
                            <div class="avatar avatar-2xl me-2">
                                <img class="rounded-soft border" src="../theme/public/assets/img/generic/image-file-2.png" alt="" data-dz-thumbnail="data-dz-thumbnail" />
                            </div>
                            <div class="flex-1 d-flex flex-between-center">
                                <div>
                                    <h6 data-dz-name="data-dz-name"></h6>
                                    <div class="d-flex align-items-center">
                                        <p class="mb-0 fs-10 text-400 lh-1" data-dz-size="data-dz-size"></p>
                                    </div>
                                </div>
                                <div class="dropdown font-sans-serif">
                                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal dropdown-caret-none" type="button" data-bs-toggle="dropdown">
                                        <span class="fas fa-ellipsis-h"></span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end border py-2">
                                        <a class="dropdown-item" href="#!" data-dz-remove="data-dz-remove">Eliminar Archivo</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <div class="col-lg-4 ps-lg-2">
        <div class="sticky-sidebar">

            <div class="card empleo-editor-card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h6 class="mb-0">Ubicación</h6>
                </div>
                <div class="card-body">
                    <div class="row gx-2">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="provincia">Provincia <span class="text-danger">*</span></label>
                            <select class="form-control" id="provincia" onchange="cargarCantones(this.value)">
                                <option value="">Seleccione provincia</option>
                                <option value="Azuay">Azuay</option>
                                <option value="Bolívar">Bolívar</option>
                                <option value="Cañar">Cañar</option>
                                <option value="Carchi">Carchi</option>
                                <option value="Cotopaxi">Cotopaxi</option>
                                <option value="Chimborazo">Chimborazo</option>
                                <option value="El Oro">El Oro</option>
                                <option value="Esmeraldas">Esmeraldas</option>
                                <option value="Guayas">Guayas</option>
                                <option value="Imbabura">Imbabura</option>
                                <option value="Loja">Loja</option>
                                <option value="Los Ríos">Los Ríos</option>
                                <option value="Manabí">Manabí</option>
                                <option value="Morona Santiago">Morona Santiago</option>
                                <option value="Napo">Napo</option>
                                <option value="Pastaza">Pastaza</option>
                                <option value="Pichincha">Pichincha</option>
                                <option value="Tungurahua">Tungurahua</option>
                                <option value="Zamora Chinchipe">Zamora Chinchipe</option>
                                <option value="Galápagos">Galápagos</option>
                                <option value="Sucumbíos">Sucumbíos</option>
                                <option value="Orellana">Orellana</option>
                                <option value="Santo Domingo de los Tsáchilas">Santo Domingo de los Tsáchilas</option>
                                <option value="Santa Elena">Santa Elena</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="canton">Cantón <span class="text-danger">*</span></label>
                            <select class="form-select" id="canton" required>
                                <option value="">Elija un cantón</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card empleo-editor-card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h6 class="mb-0">Tags</h6>
                </div>
                <div class="card-body">
                    <label class="form-label" for="tags">Agrega para facilitar búsqueda <span class="text-danger">*</span></label>
                    <input class="form-control" id="tags" type="text" required data-options='{"removeItemButton":true,"placeholder":false}' />
                </div>
            </div>

            <div class="card empleo-editor-card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h6 class="mb-0">Fechas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="fecha_inicio">Fecha inicio <span class="text-danger">*</span></label>
                        <input class="form-control" id="fecha_inicio" type="date" required value="<?= date('Y-m-d'); ?>" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="fecha_fin">Fecha fin <span class="text-danger">*</span></label>
                        <input class="form-control" id="fecha_fin" type="date" required value="<?= date('Y-m-d'); ?>" />
                    </div>
                </div>
            </div>

            <div class="card empleo-sidebar-note border-0">
                <div class="card-body">
                    <h6 class="text-white mb-2">Consejo rápido</h6>
                    <p class="mb-0 text-white-50">Usa imágenes limpias, un título claro y una descripción concreta para que el empleo se vea profesional y sea fácil de identificar al editarlo más adelante.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <div class="row justify-content-between align-items-center">
            <div class="col-md">
                <h5 class="mb-2 mb-md-0">¡Sé claro en tu carga y atraerás al cliente ideal!</h5>
            </div>
            <div class="col-auto">
                <button onclick="guardarEmpleo()" class="btn btn-primary" id="btnGuardarEmpleo" type="button">
                    <?= ($id_empleo > 0) ? 'Actualizar empleo' : 'Registrar empleo' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="js/crear_empleo.js?v=2.0.3"></script>
<script src="js/alerts.js"></script>

<?php require 'includes/footer.php'; ?>
