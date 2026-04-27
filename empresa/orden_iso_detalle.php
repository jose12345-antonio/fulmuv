<?php
require 'includes/header.php';
$id_orden_iso = $_GET["id_orden_iso"];
$input_id_orden_iso = "<input type='hidden' id='id_orden_iso' value='$id_orden_iso' >";
echo $input_id_orden_iso;

foreach ($permisos as $value) {
    if ($value["permiso"] == "Ordenes" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
    if ($value["permiso"] == "Ordenes" && $value["valor"] == "true") {
        if ($value["levels"] != "Fulmuv") {
            echo "<script>window.location.href = '" . $dashboard . "'</script>";
        }
    }
}
?>
<title>Orden Detalle</title>

<div class="card mb-3 d-print-none">
    <div class="card-body">
        <div class="row justify-content-between align-items-center">
            <div class="col-md">
                <h5 class="mb-2 mb-md-0 numero_orden">Detalle de orden #</h5>
            </div>
            <div class="col-auto">
                <a href="javascript:window.print()" class="btn btn-falcon-default btn-sm me-1 mb-2 mb-sm-0" type="button"><span class="fas fa-print me-1"> </span>Imprimir</a>
                <!-- <button class="btn btn-falcon-default btn-sm me-1 mb-2 mb-sm-0" type="button" onclick="printDiv('areaImprimir')"><span class="fas fa-print me-1"> </span>Imprimir</button> -->
            </div>
        </div>
    </div>
</div>

<div class="card mb-3" id="areaImprimir">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col" id="lista_clientes">
                <h6 class="text-500">Clientes (Empresa-Sucursal-Área)</h6>
            </div>
            <div class="col-sm-auto ms-auto">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless fs-10">
                        <tbody id="tabla">
                            <tr>
                                <th class="text-sm-end">Orden #:</th>
                                <td class="numero_orden"></td>
                            </tr>
                            <tr>
                                <th class="text-sm-end">Fecha:</th>
                                <td id="fecha"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="table-responsive scrollbar mt-4 fs-10">
            <table class="table table-striped">
                <thead data-bs-theme="light">
                    <tr class="bg-primary dark__bg-1000">
                        <th class="text-white border-0">Productos</th>
                        <th class="text-white border-0 text-center">Cantidad</th>
                        <th class="text-white border-0 text-end" id="col_valores">Valor</th>
                    </tr>
                </thead>
                <tbody id="lista_productos">
                </tbody>
            </table>
        </div>
        <div class="row justify-content-end" id="div_totales">
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
<script src="js/orden_iso_detalle.js"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>