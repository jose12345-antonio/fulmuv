<?php
$menu = "catalogos";
$sub_menu = "crear_catalogo";
$hide_filter_bar = true;
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Catalogos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<!-- <style>
    .select2-container--bootstrap-5 .select2-selection--single {
        border-radius: 1rem;
    }
</style> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<title>Crear catalogo</title>
<div class="row g-0">
    <div class="col-lg-12 pe-lg-2">
        <div class="card mb-3">
            <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                <h6 class="mb-0">Información</h6>
                <div>
                    <button class="btn btn-success" type="button" onclick="$('#subirArchivo').val('');$('#subirArchivo').click();">
                        <span class="far fa-file-excel" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Cargar Excel</span>
                    </button>
                    <input type="file" class="form-control" id="subirArchivo" accept=".xlsx, .xls" style="display: none;">
                    <button class="btn btn-primary" type="button" onclick="importar()">
                        <span class="fas fa-file-import" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Importar</span>
                    </button>
                    <button class="btn btn-iso" type="button" onclick="saveCatalogo()">
                        <span class="fas fa-save" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Guardar</span>
                    </button>
                </div>
                

            </div>
            <div class="card-body">
                <div class="row gx-2">
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="nombre">Nombre:</label>
                        <input class="form-control" id="nombre" type="text" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="descripcion">Descripción: </label>
                        <textarea class="form-control" id="descripcion" type="text" rows="1" oninput="this.value = this.value.toUpperCase()"></textarea>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="lista_empresas">Empresa:</label>
                        <select class="form-select selectpicker" id="lista_empresas" onchange="getSucursales()">
                            <option value="">Seleccione una empresa</option>
                        </select>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="lista_sucursales">Sucursal:</label>
                        <select class="form-select selectpicker" id="lista_sucursales">
                        </select>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <label class="form-label" for="lista_tipo">Tipo:</label>
                        <select class="form-select selectpicker" id="lista_tipo" onchange="llenarCategorias()">
                            <option value="producto">Producto</option>
                            <option value="servicio">Servicio</option>
                        </select>
                    </div>
                    <!-- <div class="col-lg-6 mb-2">
                        <label class="form-label" for="lista_sucursales">Categoría:</label>
                        <select class="form-select selectpicker" id="lista_categorias" onchange="llenarTabla()">
                            <option value="">Seleccione una categoría</option>
                        </select>
                    </div> -->
                </div>
                <!--div class="row flex-between-center">
                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div class="d-none" id="table-number-pagination-actions">
                            <div class="d-flex">
                                <button class="btn btn-falcon-default btn-sm ms-2" type="button" onclick="agregarProductos()">Agregar</button>
                            </div>
                        </div>
                        <div id="table-number-pagination-replace-element">
                            
                        </div>
                    </div>
                </div>

                <div class="falcon-data-table" id="tablaProductos" style="display:none;">
                    <table class="table table-sm mb-0 data-table fs-10" id="my_table">
                        <thead class="bg-200">
                            <tr>
                                <th class="text-900 no-sort white-space-nowrap">
                                    <div class="form-check mb-0 d-flex align-items-center">
                                        <input class="form-check-input" id="checkbox-bulk-table-item-select" type="checkbox" />
                                    </div>
                                </th>
                                <th class="text-900 sort pe-1 align-middle white-space-nowrap">Código</th>
                                <th class="text-900 sort pe-1 align-middle white-space-nowrap pe-7">Nombre</th>
                                <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Descripción</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody class="" id="lista_prod">

                        </tbody>
                    </table>
                </div-->

            </div>
        </div>
        <div class="card mb-3">
            <!--div class="card-header bg-body-tertiary">
                <div class="row">
                    <div class="col-lg-6">
                        <select class="form-select selectpicker" id="lista_productos" aria-label=" Seleccione producto">
                            <option value="-1">Seleccione producto</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex justify-content-center align-items-center">
                        <a onclick="addProducto()" class="btn btn-primary">Agregar</a>
                    </div>
                </div>
            </div-->
            <div class="card-header bg-body-tertiary">
                <div class="row mb-3">
                    <div class="col-lg-2">
                        <select class="form-select" id="tipo_agregado" onchange="cambiarTipoAgregado()">
                            <option value="individual">Producto individual</option>
                            <option value="categoria">Categoría</option>
                        </select>
                    </div>

                    <!-- Select productos individuales + Botón agregar -->
                    <div class="col-lg-6 d-flex align-items-center" id="seccion_individual">
                        <!-- <div class="col-lg-8"> -->
                        <select class="form-select selectpicker" id="lista_productos" aria-label="Seleccione producto">
                            <option value="-1">Seleccione producto</option>
                        </select>
                        <a onclick="addProducto()" class="btn btn-primary ms-2">Agregar</a>

                        <!-- </div>
                        <div class="col-lg-4 align-items-center">
                            <a onclick="addProducto()" class="btn btn-primary">Agregar</a>
                        </div> -->

                    </div>

                    <!-- Select categorías (múltiple) -->
                    <div class="col-lg-4 mb-2 d-none" id="seccion_categoria">
                        <select class="form-select selectpicker" id="lista_categorias" onchange="llenarTabla()">
                            <option value="">Seleccione una categoría</option>
                            <option value="Todas">Todas</option>
                        </select>
                    </div>
                    <div class="col-lg-4 align-items-center">
                        <button class="btn btn-danger" type="button" onclick="removerTodo()">
                            <span class="fas fa-trash" data-fa-transform="shrink-3 down-2"></span>
                            <span class="d-none d-sm-inline-block ms-1">Limpiar todo</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0 bg-body-tertiary">
                <div class="row p-3" id="productos_agregados">
                    
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/crear_catalogo.js"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>