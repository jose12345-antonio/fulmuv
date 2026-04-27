<?php
require 'includes/header.php';
$id_orden = $_GET["id_orden"];
$input_id_orden = "<input type='hidden' id='id_orden' value='$id_orden' >";
echo $input_id_orden;
?>
<title>Órden Detalle</title>
<div class="card mb-3">
    <div class="bg-holder d-none d-lg-block bg-card" style="background-image:url(../theme/public/assets/img/icons/spot-illustrations/corner-4.png);opacity: 0.7;">
    </div>
    <!--/.bg-holder-->

    <div class="card-body position-relative">
        <div class="row g-2 align-items-sm-center">
            <div class="col-auto">
                <h5 class="mb-2" id="numero_orden">Detalle de órden: #</h5>
                <p class="mb-0 fs-10" id="fecha"></p>
                <p class="mb-0 fs-10" id="nombre_empresa"> <strong>Empresa: </strong> </p>
                <p class="mb-0 fs-10" id="nombre_sucursal"> <strong>Sucursal: </strong> </p>
                <p class="mb-2 fs-10" id="nombre_area"> <strong>Área: </strong> </p>
                <div id="estado">

                </div>
                <div class="alert alert-success mt-3">
                    <h6 class="mb-0 text-800" id="mensaje"></h6>
                </div>
            </div>
            <div class="col">
                <div class="row align-items-center">
                    <!-- <div class="col-3 col-sm-auto align-items-center">
                        <label for="form-label">Tipo de Trayecto</label>
                        <select id="tipo_trayecto" class="form-select" onchange="llenarTrayectos()">
                            <option value="documentos">DOCUMENTOS</option>
                            <option value="mercancia_premier">MERCANCIA PREMIER (CARGA LIVIANA)</option>
                        </select>
                    </div>
                    <div class="col-3 col-sm-auto align-items-center">
                        <label for="form-label">Trayecto</label>
                        <select id="trayecto" class="form-select">
                            <option value="">Seleccione trayecto</option>
                        </select>
                    </div> -->
                    <div class="col-12 col-sm-auto ms-auto">
                        <!-- <button class="btn btn-falcon-primary" type="button" onclick="verTrayecto()">Trayecto</button> -->
                        <button class="btn btn-falcon-primary" type="button" onclick="confirmarPeso()">Confirmar Peso</button>
                        <!-- <button class="btn btn-falcon-primary" type="button" onclick="confirmarEnvio()">Confirmar Envío</button> -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="card mb-3">
    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                <div class="d-none" id="table-number-pagination-actions">
                    <div class="d-flex">
                        <select class="form-select form-select-sm" aria-label="Bulk actions" id="orden_estado">
                            <option value="vender">Confirmar venta</option>
                        </select>
                        <button class="btn btn-falcon-default btn-sm ms-2" type="button" onclick="updateEstadoBulk()">Aplicar</button>
                    </div>
                </div>
                <div id="table-number-pagination-replace-element">

                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive fs-10">
            <table class="table table-striped" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 no-sort white-space-nowrap">...</th>
                        <th class="text-900 border-0">Producto</th>
                        <th class="text-900 border-0">Peso</th>
                        <th class="text-900 border-0 text-center">Cantidad</th>
                        + <th class="text-900 border-0 text-center">Peso total (kg)</th>
                        <th class="text-900 border-0 text-end carrito">Valor</th>
                    </tr>
                </thead>

                <tbody id="lista_productos">
                </tbody>
            </table>
        </div>
        <div class="row g-0 justify-content-end carrito">
            <div class="col-auto">
                <table class="table table-sm table-borderless fs-10 text-end">
                    <tr>
                        <th class="text-900">Subtotal:</th>
                        <td class="fw-semi-bold" id="subtotal">$0.00 </td>
                    </tr>
                    <tr>
                        <th class="text-900">IVA 15%:</th>
                        <td class="fw-semi-bold" id="iva">$0.00</td>
                    </tr>
                    <tr class="border-top">
                        <th class="text-900">Total:</th>
                        <td class="fw-semi-bold" id="total">$0.00</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Conexión API js -->
<script src="js/orden_detalle.js?v1.0.0.0.0.0.6"></script> 
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>