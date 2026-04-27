<?php
require 'includes/header.php';
$id_orden = $_GET["id_orden"];
$input_id_orden = "<input type='hidden' id='id_orden' value='$id_orden' >";
echo $input_id_orden;
?>
<title>Orden Detalle</title>
<div class="card mb-3">
    <div class="bg-holder d-none d-lg-block bg-card" style="background-image:url(../theme/public/assets/img/icons/spot-illustrations/corner-4.png);opacity: 0.7;">
    </div>
    <!--/.bg-holder-->

    <div class="card-body position-relative">
        <h5 class="mb-2" id="numero_orden">Detalle de orden: #</h5>
        <p class="mb-0 fs-10" id="fecha"></p>
        <p class="mb-0 fs-10" id="nombre_empresa"> <strong>Empresa: </strong> </p>
        <p class="mb-0 fs-10" id="nombre_sucursal"> <strong>Sucursal: </strong> </p>
        <p class="mb-2 fs-10" id="nombre_area"> <strong>Área: </strong> </p>
        <div id="estado">
            
        </div>
    </div>
</div>
<!-- <div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4 mb-lg-0">
                <h5 class="mb-3 fs-9">Billing Address</h5>
                <h6 class="mb-2">Antony Hopkins</h6>
                <p class="mb-1 fs-10">2393 Main Avenue<br />Penasauka, New Jersey 87896</p>
                <p class="mb-0 fs-10"> <strong>Email: </strong><a href="mailto:ricky@gmail.com">antony@example.com</a></p>
                <p class="mb-0 fs-10"> <strong>Phone: </strong><a href="tel:7897987987">7897987987</a></p>
            </div>
            <div class="col-md-6 col-lg-4 mb-4 mb-lg-0">
                <h5 class="mb-3 fs-9">Shipping Address</h5>
                <h6 class="mb-2">Antony Hopkins</h6>
                <p class="mb-0 fs-10">2393 Main Avenue<br />Penasauka, New Jersey 87896</p>
                <div class="text-500 fs-10">(Free Shipping)</div>
            </div>
            <div class="col-md-6 col-lg-4">
                <h5 class="mb-3 fs-9">Payment Method</h5>
                <div class="d-flex"><img class="me-3" src="../theme/public/assets/img/icons/visa.png" width="40" height="30" alt="" />
                    <div class="flex-1">
                        <h6 class="mb-0">Antony Hopkins</h6>
                        <p class="mb-0 fs-10">**** **** **** 9809</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
<div class="card mb-3">
    <div class="card-body">
        <div class="table-responsive fs-10">
            <table class="table table-striped">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 border-0">Producto</th>
                        <th class="text-900 border-0 text-center">Cantidad</th>
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
<script src="js/orden_detalle.js?v1.0.42"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>