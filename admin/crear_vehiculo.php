<?php
$menu = "vehiculos";
$sub_menu = "crear_vehiculo";
$hide_filter_bar = true;
require 'includes/header.php';

foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
$id_vehiculo = isset($_GET['id_vehiculo']) ? intval($_GET['id_vehiculo']) : 0;
?>
<title>Crear vehículo</title>
<style>
    .vehiculo-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(248, 250, 252, 0.92);
        backdrop-filter: blur(4px);
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity .25s ease, visibility .25s ease;
    }
    .vehiculo-loading-overlay.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .vehiculo-loading-card {
        min-width: 260px;
        padding: 1.25rem 1.5rem;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
        text-align: center;
    }
    .vehiculo-loading-wrapper.is-loading {
        visibility: hidden;
    }
</style>
<div id="vehiculoLoadingOverlay" class="vehiculo-loading-overlay">
    <div class="vehiculo-loading-card">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <div class="fw-semibold">Cargando información del vehículo...</div>
    </div>
</div>
<div id="vehiculoFormWrapper" class="vehiculo-loading-wrapper is-loading">
<div class="row g-0">
    <div class="col-lg-8 pe-lg-2">
        <input type="hidden" id="id_vehiculo" value="<?= $id_vehiculo ?>">
        <div class="card mb-3">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Registro de Vehículo</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="row gx-2">
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Tipo de vehículo <span class="text-danger">*</span></label></label>
                            <select id="referencia" class="form-select" onchange="buscarModelosReferencia()" required>
                                <option value="">Seleccione....</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Marca de vehículo <span class="text-danger">*</span></label></label>
                            <select id="marca" class="form-select" required>
                                <option value="">Seleccione....</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Modelo de vehículo <span class="text-danger">*</span></label></label>
                            <select id="modelo" class="form-select" onchange="asignarModelo()" required>
                                <option value="">Seleccione....</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Subtipo <span class="text-danger">*</span></label></label>
                            <select id="tipo_vehiculo" class="form-select" required>
                                <option value="">Seleccione....</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Tracción <span class="text-danger">*</span></label></label>
                            <select id="traccion" class="form-select" required>
                                <option value="">Seleccione....</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Funcionamiento de motor <span class="text-danger">*</span></label></label>
                            <select id="motor" class="form-select" required>
                                <option value="">Seleccione....</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="product-summary">Descripción <span class="text-danger">*</span></label> </label>
                            <!-- <textarea class="form-control" id="descripcion" type="text" oninput="this.value = this.value.toUpperCase()"></textarea> -->
                            <div class="create-product-description-textarea">
                                <textarea class="tinymce d-none" data-tinymce="data-tinymce" id="descripcion" required></textarea>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Imagen frontal <span class="text-danger">*</span></label> </label>
                            <input class="form-control" id="img_frontal" type="file" accept="image/*" required/>
                            <input type="hidden" id="img_frontal_old">
                            <img id="preview_frontal" style="display:none; max-width:100%; height:120px; object-fit:cover; border-radius:8px; margin-top:8px;">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Imagen posterior <span class="text-danger">*</span></label> </label>
                            <input class="form-control" id="img_posterior" type="file" accept="image/*" required/>
                            <input type="hidden" id="img_posterior_old">
                            <img id="preview_posterior" style="display:none; max-width:100%; height:120px; object-fit:cover; border-radius:8px; margin-top:8px;">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Año <span class="text-danger">*</span></label> </label>
                            <input class="form-control" id="anio" type="number" required/>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="condicion">Condición <span class="text-danger">*</span></label> </label>
                            <select class="form-select" name="condicion" id="condicion" multiple required>
                                <option value="Nuevo">Nuevo</option>
                                <option value="Usado">Usado</option>
                                <option value="Restaurado">Restaurado</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="tipo_vendedor">Tipo de vendedor <span class="text-danger">*</span></label> </label>
                            <select class="form-select" name="tipo_vendedor" id="tipo_vendedor" multiple required>

                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Kilometraje/Millaje <span class="text-danger">*</span></label> </label>
                            <input class="form-control" id="kilometraje" type="number" required/>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Transmisión (Opcional)</label> </label>
                            <select name="transmision" id="transmision" multiple >
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Placa empieza con letra (Opcional) </label>
                            <input class="form-control" id="inicio_placa" type="text" />
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Placa termina con número (Opcional) </label>
                            <input class="form-control" id="fin_placa" type="number" />
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="color">Color (Opcional) </label>
                            <select name="color" id="color">
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Cilindraje de motor (Opcional) </label>
                            <input class="form-control" id="cilindraje" type="text" />
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="tapiceria">Tapicería de asientos (Opcional) </label>
                            <select name="tapiceria" id="tapiceria" multiple>

                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="duenio">Tipo de dueño (Opcional) </label>
                            <select class="form-select" name="duenio" id="duenio" multiple>
                                <option value="único dueño">Único dueño</option>
                                <option value="Segundo dueño">Segundo dueño</option>
                                <option value="Tercer dueño">Tercer dueño</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="direccion">Dirección (Opcional)</label>
                            <select name="direccion" id="direccion" multiple>

                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="climatizacion">Climatización (Opcional) </label>
                            <select name="climatizacion" id="climatizacion" multiple>

                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mb-3 d-none" id="cardDetallesProducto">
            <div class="card-header bg-body-tertiary d-flex flex-between-center">
                <h6 class="mb-0">Detalles del producto</h6>
                <!-- <div class="dropdown font-sans-serif btn-reveal-trigger">
                    <button class="btn btn-info btn-sm" id="agregarCampo" type="button">Agregar atributo</button>
                    
                </div> -->
            </div>
            <div class="card-body">
                <div class="row gx-2" id="contenedorAtributos">

                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Archivos (Imagen y Ficha Técnica)</h6>
            </div>
            <div class="card-body">
                <form class="dropzone dropzone-multiple p-0" id="myAwesomeDropzone">
                    <div class="fallback">
                        <input name="file" type="file" multiple="multiple" />
                    </div>
                    <div class="dz-message my-0" data-dz-message="data-dz-message"> <img class="me-2" src="../theme/public/assets/img/icons/cloud-upload.svg" width="25" alt="" /><span class="d-none d-lg-inline">Drag your image here<br />or, </span><span class="btn btn-link p-0 fs-10">Browse</span></div>

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
                                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal dropdown-caret-none" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h"></span></button>
                                    <div class="dropdown-menu dropdown-menu-end border py-2"><a class="dropdown-item" href="#!" data-dz-remove="data-dz-remove">Eliminar Archivo</a></div>
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
                            <label class="form-label" for="product-category">Provincia <span class="text-danger">*</span></label></label>
                            <select class="form-control" id="provincia" onchange="cargarCantones(this.value)" multiple required>
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
                            <label class="form-label" for="cantón">Cantón <span class="text-danger">*</span></label></label>
                            <select class="form-select" id="canton" name="cantón" multiple required>
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
                    <label class="form-label" for="product-tags">Agrega para facilitar búsqueda <span class="text-danger">*</span></label> </label>
                    <input class="form-control" id="tags" type="text" name="tags" required="required" size="1" data-options='{"removeItemButton":true,"placeholder":false}' required/>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h6 class="mb-0">Precio</h6>
                </div>
                <div class="card-body">
                    <label class="form-label" for="precio_referencia">Precio base <span class="text-danger">*</span></label> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Precio regular del producto"><span class="fas fa-question-circle text-primary fs-10 ms-1"></span></span></label>
                    <input class="form-control" id="precio_referencia" type="number" min="1" required/>
                    <div class="form-check my-2">
                        <input class="form-check-input" id="iva" type="checkbox" />
                        <label class="form-check-label" for="flexCheckDefault">+ IVA 15%</label>
                    </div>
                    <div class="form-check my-2">
                        <input class="form-check-input" id="negociable" type="checkbox" />
                        <label class="form-check-label" for="flexCheckDefault">Negociable</label>
                    </div>
                    <label class="form-label" for="descuento">Descuento % </label> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Descuento del producto"><span class="fas fa-question-circle text-primary fs-10 ms-1"></span></span></label>
                    <input class="form-control" id="descuento" type="number" min="1" value="0" />
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
                <!-- <button onclick="verificarMembresiaYGuardar()" class="btn btn-primary" role="button">Registrar vehículo </button> -->
                <button onclick="guardarVehiculo()" class="btn btn-primary" role="button" id="btnGuardarVehiculo">
                <?= ($id_vehiculo > 0) ? 'Actualizar vehículo' : 'Registrar vehículo' ?>
                </button>
            </div>
        </div>
    </div>
</div>
</div>
<!-- Conexión API js -->
<script src="js/crear_vehiculo.js?v2.0.0.0.2.17"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
