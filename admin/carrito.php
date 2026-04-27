<?php
$menu = "carrito";
$hide_filter_bar = true;
require 'includes/header.php';
if (!in_array($_SESSION["rol_id"], [2, 3, 5])) {
    echo "<script>window.location.href = '" . $dashboard . "'</script>";
}
?>
<title>Carrito</title>
<div class="card">
    <div class="card-header">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center mb-2">
                <div class="col-md-auto">
                    <h5 class="mb-3 mb-md-0">Productos seleccionados <span id="total_product">(0)</span></h5>
                </div>
            </div>
            <div class="row flex-between-center gy-2 px-x1 mb-2" id="searh_empresa">
                <div class="col-auto pe-0">
                    <h6 class="mb-0">Empresa</h6>
                </div>
                <div class="col-auto">
                    <div class="input-group input-search-width">
                        <select class="form-select selectpicker" id="lista_empresas" onchange="buscarSucursales()">
                        </select>
                    </div>
                </div>
            </div>
            <div class="row flex-between-center gy-2 px-x1 mb-2" id="searh_sucursal">
                <div class="col-auto pe-0">
                    <h6 class="mb-0">Sucursal</h6>
                </div>
                <div class="col-auto">
                    <div class="input-group input-search-width">
                        <select class="form-select selectpicker" id="lista_sucursales" onchange="mostrarCarrito(value)">
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="row gx-x1 mx-0 bg-200 text-900 fs-10 fw-semi-bold">
            <div class="col-9 col-md-8 py-2 px-x1">Nombre</div>
            <div class="col-3 col-md-4 px-x1">
                <div class="row">
                    <div class="col-md-8 py-2 d-none d-md-block text-center">Cantidad</div>
                    <div class="col-12 col-md-4 text-end py-2 carrito">Precio</div>
                </div>
            </div>
        </div>
        <div id="lista_productos">
        </div>
        <div class="row fw-bold gx-x1 mx-0 fs-10">
            <div class="col-9 col-md-8 py-2 px-x1 text-end text-900">Total</div>
            <div class="col px-0">
                <div class="row gx-x1 mx-0">
                    <div class="col-md-8 py-2 px-x1 d-none d-md-block text-center" id="items">0 (items)</div>
                    <div class="col-12 col-md-4 text-end py-2 px-x1 carrito" id="total">$0</div>
                </div>
            </div>
        </div>
        <div class="row g-0 justify-content-end carrito">
            <div class="col-auto">
                <table class="table table-sm table-borderless fs-10 text-end">
                    <tbody>
                        <tr>
                            <th class="text-900">Subtotal:</th>
                            <td class="fw-semi-bold" id="subtotal">$0.00</td>
                        </tr>
                        <tr>
                            <th class="text-900">IVA 15%:</th>
                            <td class="fw-semi-bold" id="iva">$0.00</td>
                        </tr>
                        <tr class="border-top">
                            <th class="text-900">Total:</th>
                            <td class="fw-semi-bold" id="total_pagar">$0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary d-flex justify-content-between">
        <div class="row flex-between-center gy-2">
            <div class="col-auto pe-0">
                <h6 class="mb-0">Área</h6>
            </div>
            <div class="col-auto">
                <div class="input-group input-search-width">
                    <select class="form-select selectpicker" id="lista_areas">
                    </select>
                </div>
            </div>
        </div>
        <a class="btn btn-iso" onclick="generarOrden()">Generar orden</a>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/carrito_new.js?v1.0.42"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>