<?php
require 'includes/header.php';
?>
<style>
    /* .select2-container--bootstrap-5 .select2-selection--single {
        border-radius: 1rem;
    } */
</style>
<title>Ordenes</title>
<div class="card">
    <div class="card-header">
        <div class="row">
            <!-- <div class="col-lg-6">
                <label for="organizerSingle">Buscar</label>
                <select class="form-select selectpicker" id="lista_productos" data-options='{"placeholder":"Seleccione producto..."}'>
                    <option>Seleccione producto...</option>

                </select>
            </div> -->
            <div class="col-lg-6">
                <select class="form-select selectpicker" id="lista_productos" aria-label=" Seleccione producto">
                    <option value="-1">Seleccione producto</option>
                </select>
            </div>
            <div class="col-lg-2 d-flex justify-content-center align-items-center">
                <a onclick="addProducto()" class="btn btn-primary">Agregar</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="row gx-x1 mx-0 bg-200 text-900 fs-10 fw-semi-bold">
            <div class="col-9 col-md-8 py-2 px-x1">Nombre</div>
            <div class="col-3 col-md-4 px-x1">
                <div class="row">
                    <div class="col-md-12 py-2 d-none d-md-block text-center">Cantidad</div>
                </div>
            </div>
        </div>
        <div id="productos_agregados">
        </div>

        <div class="row fw-bold gx-x1 mx-0">
            <div class="col-9 col-md-8 py-2 px-x1 text-end text-900">Total</div>
            <div class="col px-0">
                <div class="row gx-x1 mx-0">
                    <div class="col-md-12 py-2 px-x1 text-center" id="total_items">0 (items)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary d-flex justify-content-end">
        <a class="btn btn-primary" onclick="saveOrden()">Generar orden</a>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/crear_orden_v2.js?v1.0.42"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>