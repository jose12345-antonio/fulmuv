<?php
$menu = "empleos";
$sub_menu = "crear_empleo";
require 'includes/header.php';

foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}

$id_empleo = isset($_GET['id_empleo']) ? intval($_GET['id_empleo']) : 0;
?>
<title><?= ($id_empleo > 0) ? 'Editar empleo' : 'Crear empleo' ?></title>

<div class="row g-0">
    <div class="col-lg-8 pe-lg-2">
        <input type="hidden" id="id_empleo" value="<?= $id_empleo ?>">

        <div class="card mb-3">
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
                            <img id="preview_frontal" style="display:none; max-width:100%; height:120px; object-fit:cover; border-radius:8px; margin-top:8px;">
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label" for="img_posterior">Imagen posterior <span class="text-danger">*</span></label>
                            <input class="form-control" id="img_posterior" type="file" accept="image/*" required />
                            <input type="hidden" id="img_posterior_old">
                            <img id="preview_posterior" style="display:none; max-width:100%; height:120px; object-fit:cover; border-radius:8px; margin-top:8px;">
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Dropzone -->
        <div class="card mb-3">
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

            <div class="card mb-3">
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

            <div class="card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h6 class="mb-0">Tags</h6>
                </div>
                <div class="card-body">
                    <label class="form-label" for="tags">Agrega para facilitar búsqueda <span class="text-danger">*</span></label>
                    <input class="form-control" id="tags" type="text" required data-options='{"removeItemButton":true,"placeholder":false}' />
                </div>
            </div>

            <div class="card mb-3">
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

<script src="js/crear_empleo.js?v=2.0.2"></script>
<script src="js/alerts.js"></script>

<?php require 'includes/footer.php'; ?>
