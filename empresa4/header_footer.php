<?php
$menu = "email";
$sub_menu = "configurar_email";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "E-mail" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<title>Configuracion</title>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="mb-2 mb-md-0">Configurar E-mail</h5>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-4xl mb-2 hover-actions-trigger">
                    <img class="rounded-circle" src="" data-img="" alt="" id="cuerpoImagen" style="object-fit: contain;">
                    <div class="hover-actions top-50">
                        <a class="btn icon-item btn-sm rounded-3 me-2 fs-11 icon-item-sm" id="btnDownload" download><span class="fas fa-file-download"></span></a>
                        <a class="btn icon-item btn-sm rounded-3 me-2 fs-11 icon-item-sm" id="btnView" target="_blank" href=""><span class="fas fa-eye"></span></a>
                        <label class="btn icon-item btn-sm rounded-3 me-2 fs-11 icon-item-sm" for="img" id="btnEdit">
                            <span class="fas fa-pen"></span>
                        </label>
                        <input type="file" id="img" style="display:none;" accept="image/*">
                    </div>
                </div>
                <p class="mb-0 text-600">
                    Color banner:<br>
                    <input type="color" id="cuerpoColor" value="">
                </p>
            </div>
            <div class="card-footer border-top border-200">
                <button class="btn btn-iso w-100 px-2" type="button" onclick="editContenedor()"><span class="far fa-save me-1"></span>Guardar</button>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-9">
        <div class="row">
            <div class="col-sm-12 col-lg-4 mb-2">
                <div class="card m-0">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart text-muted font-24"></i>
                        <p class="text-muted font-15 mb-1">Orden creada</p>
                        <select id="orden_creada" class="form-select form-select-sm" onchange="addCorreoProces(this.value, '1')">
                            <option value="0">No Mail</option>
                        </select>

                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-4 mb-2">
                <div class="card m-0">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart text-muted font-24"></i>
                        <p class="text-muted font-15 mb-1">Orden enviada</p>
                        <select id="orden_enviada" class="form-select form-select-sm" onchange="addCorreoProces(this.value, '2')">
                            <option value="0">No Mail</option>
                        </select>

                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-4 mb-2">
                <div class="card m-0">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart text-muted font-24"></i>
                        <p class="text-muted font-15 mb-1">Orden procesada</p>
                        <select id="orden_procesada" class="form-select form-select-sm" onchange="addCorreoProces(this.value, '3')">
                            <option value="0">No Mail</option>
                        </select>

                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-4 mb-2">
                <div class="card m-0">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart text-muted font-24"></i>
                        <p class="text-muted font-15 mb-1">Orden aprobada</p>
                        <select id="orden_aprobada" class="form-select form-select-sm" onchange="addCorreoProces(this.value, '4')">
                            <option value="0">No Mail</option>
                        </select>

                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-4 mb-2">
                <div class="card m-0">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart text-muted font-24"></i>
                        <p class="text-muted font-15 mb-1">Orden completada</p>
                        <select id="orden_completada" class="form-select form-select-sm" onchange="addCorreoProces(this.value, '5')">
                            <option value="0">No Mail</option>
                        </select>

                    </div>
                </div>
            </div>
        </div>
    </div>   
    
    <!-- <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="echart-session-example h-100" data-echart-responsive="true" id="echart-session-example"></div>

            </div>

        </div>
    </div> -->
</div>


<script src="js/header_footer.js"></script>
<?php
require 'includes/footer.php';
?>