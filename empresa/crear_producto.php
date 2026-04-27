<?php
$menu = "productos";
$sub_menu = "crear_producto";
require 'includes/header.php';
$id_producto = $_GET["id_producto"];

if (isset($membresia["nombre"])) {
    $nombre_membresia_producto = preg_replace('/[^a-z0-9]+/', '', strtolower(trim((string)$membresia["nombre"])));
    if ($nombre_membresia_producto === "basicmuv") {
        echo "<script>
            swal({
                title: 'Plan no disponible',
                text: 'Tu plan BasicMuv no permite registrar productos.',
                icon: 'info'
            }, function () {
                window.location.href = 'servicios.php';
            });
        </script>";
        exit;
    }
}

foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
echo "<input type='hidden' class='form-control' id='id_producto' value='$id_producto'>";
?>
<!-- <style>
    .select2-container--bootstrap-5 .select2-selection--single {
        border-radius: 1rem;
    }
</style> -->
<title>Crear producto</title>
<!-- <div class="card mb-3">
    <div class="card-body">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center">
                <div class="col-md-auto">
                    <h5 class="mb-2 mb-md-0">Crear producto</h5>
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
                    <button onclick="addCategoria()" class="btn btn-primary" role="button">Agregar categoría </button>
                    <button onclick="addSubCategoria()" class="btn btn-primary" role="button">Agregar sub-categoría </button>
                </div>
            </div>
        </div>
    </div>
</div> -->
<div class="row g-0">
    <div class="col-lg-8 pe-lg-2">
        <div class="card mb-3">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Registro de Producto</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="row gx-2">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="">Título del producto <span class="text-danger">*</span></label>
                            <div class="text-700 fw-normal mb-2 fs-10">
                                Incluye hasta 5 características importantes, separadas por una COMA “,”

                                <br>Ej: “Llantas BFGoodrich, AT, 285/75 R17”

                                <br>Ej: “Suspensión IRONMAN FOAMCELL PRO, Toyota Land Cruiser 300, 2021 - 2026, Ideal también para Blindados”
                            </div>
                            <input class="form-control" id="titulo_producto" type="text" required />
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="">Marca del producto <span class="text-danger">*</span></label>
                            <!-- <input class="form-control" id="marca_producto" type="text" /> -->
                            <select id="marca_producto" class="form-select" required>
                                <option value="">Seleccione marca de producto</option>
                            </select>
                        </div>
                        <!-- <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Nombre producto <span class="text-danger">*</span></label>
                            <select id="nombre" class="form-select">
                                <option value="">Seleccione nombre de producto</option>
                            </select>
                        </div> -->
                        <div class="col-6 mb-3">
                            <label class="form-label" for="identification-no">SKU o Código </label>
                            <input class="form-control" id="codigo" type="text" oninput="this.value = this.value.toUpperCase()" />
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="identification-no">Peso(kg) </label>
                            <input class="form-control" id="peso" type="number" />
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="product-summary">Descripción <span class="text-danger">*</span> </label>
                            <!-- <textarea class="form-control" id="descripcion" type="text" oninput="this.value = this.value.toUpperCase()"></textarea> -->
                            <div class="create-product-description-textarea">
                                <textarea class="tinymce d-none" data-tinymce="data-tinymce" id="descripcion"></textarea>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Imagen frontal <span class="text-danger">*</span> </label>
                            <input class="form-control" id="img_frontal" type="file" accept="image/*" required />
                            <img id="preview_frontal" class="mt-2 rounded border d-none" style="max-width:180px; height:auto;" />

                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-summary">Imagen posterior <span class="text-danger">*</span> </label>
                            <input class="form-control" id="img_posterior" type="file" accept="image/*" required />
                            <img id="preview_posterior" class="mt-2 rounded border d-none" style="max-width:180px; height:auto;" />

                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Referencia <span class="text-danger">*</span></label>
                            <!-- <input class="form-control" id="nombre" type="text" oninput="this.value = this.value.toUpperCase()" /> -->
                            <select id="referencia" class="form-select" onchange="buscarModelosReferencia()" required>
                                <option value="">Seleccione referencia</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Modelo de vehículo <span class="text-danger">*</span></label>
                            <!-- <input class="form-control" id="nombre" type="text" oninput="this.value = this.value.toUpperCase()" /> -->
                            <select id="modelo" class="form-select" onchange="asignarModelo()" required>
                                <option value="">Seleccione modelo</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Tipo de vehículo <span class="text-danger">*</span></label>
                            <!-- <input class="form-control" id="nombre" type="text" oninput="this.value = this.value.toUpperCase()" /> -->
                            <select id="tipo_vehiculo" class="form-select" multiple required>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Marca de vehículo <span class="text-danger">*</span></label>
                            <!-- <input class="form-control" id="nombre" type="text" oninput="this.value = this.value.toUpperCase()" /> -->
                            <select id="marca" class="form-select" multiple required>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Tracción <span class="text-danger">*</span></label>
                            <!-- <input class="form-control" id="nombre" type="text" oninput="this.value = this.value.toUpperCase()" /> -->
                            <select id="traccion" class="form-select" multiple required>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="product-name">Funcionamiento de motor <span class="text-danger">*</span></label>
                            <!-- <input class="form-control" id="nombre" type="text" oninput="this.value = this.value.toUpperCase()" /> -->
                            <select id="motor" class="form-select" multiple required>
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
                <h6 class="mb-0">Archivos (Imagen y Ficha Técnica) (Opcional)</h6>
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
                <!-- <form class="dropzone dropzone-multiple p-0" id="myAwesomeDropzone" data-dropzone="data-dropzone" data-options='{"maxFiles":1,"acceptedFiles":"image/*"}'>
                    <div class="fallback">
                        <input name="file" type="file" multiple="multiple" />
                    </div>
                    <div class="dz-message my-0" data-dz-message="data-dz-message"> <img class="me-2" src="../theme/public/assets/img/icons/cloud-upload.svg" width="25" alt="" /><span class="d-none d-lg-inline">Drag your image here<br />or, </span><span class="btn btn-link p-0 fs-10">Browse</span></div>

                    <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column">
                        <div class="d-flex media align-items-center mb-3 pb-3 border-bottom btn-reveal-trigger">
                            <div class="avatar avatar-l ">
                                <img class="rounded-soft border" src="" alt="" data-dz-thumbnail="data-dz-thumbnail" />
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
                                    <div class="dropdown-menu dropdown-menu-end border py-2"><a class="dropdown-item" href="#!" data-dz-remove="data-dz-remove">Remove File</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form> -->
            </div>
        </div>
    </div>
    <div class="col-lg-4 ps-lg-2">
        <div class="sticky-sidebar">
            <div class="card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h6 class="mb-0">Tipo</h6>
                </div>
                <div class="card-body">
                    <div class="row gx-2">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="product-category">Seleccione categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="categoria" name="product-category" onchange="llenarSubCategria()" multiple required>
                                <option value="">Elija una categoría</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="product-subcategory">Seleccione sub-categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="sub_categoria" name="product-subcategory" required>
                                <option value="">Elija una subcategoría</option>
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
                    <label class="form-label" for="product-tags">Agrega para facilitar búsqueda <span class="text-danger">*</span></label>
                    <input class="form-control" id="tags" type="text" name="tags" required="required" size="1" data-options='{"removeItemButton":true,"placeholder":false}' />
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h6 class="mb-0">Precio</h6>
                </div>
                <div class="card-body">
                    <label class="form-label" for="precio_referencia">Precio base <span class="text-danger">*</span> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Precio regular del producto"><span class="fas fa-question-circle text-primary fs-10 ms-1"></span></span></label>
                    <input class="form-control" id="precio_referencia" type="number" min="1" required />
                    <div class="form-check my-2">
                        <input class="form-check-input" id="iva" type="checkbox" />
                        <label class="form-check-label" for="flexCheckDefault">+ IVA 15%</label>
                    </div>
                    <div id="ivaResumen" class="alert alert-info py-2 px-3 mt-2 d-none">
                        <div class="fw-bold">Este producto incluirá IVA 15%</div>
                        <div class="small" id="ivaCalculo"></div>
                    </div>

                    <div class="form-check my-2">
                        <input class="form-check-input" id="negociable" type="checkbox" />
                        <label class="form-check-label" for="flexCheckDefault">Negociable</label>
                    </div>
                    <label class="form-label" for="descuento">Descuento % (Opcional) <span data-bs-toggle="tooltip" data-bs-placement="top" title="Descuento del producto"><span class="fas fa-question-circle text-primary fs-10 ms-1"></span></span></label>
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
                <?php if ($id_producto != "") { ?>
                    <button onclick="verificarMembresiaYEditar()" id="btnGuardarProducto" class="btn btn-primary" role="button">Actualizar producto </button>
                <?php } else { ?>
                    <button onclick="verificarMembresiaYGuardar()" id="btnGuardarProducto" class="btn btn-primary" role="button">Registrar producto </button>
                <?php } ?>

            </div>
        </div>
    </div>
</div>
</div>
<!-- Conexión API js -->
<script src="js/crear_producto.js?v2.0.0.0.0.1.26"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
