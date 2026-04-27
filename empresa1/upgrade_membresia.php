<?php
$menu = "membresia";
// $sub_menu = "empresa_detalle";
require 'includes/header.php';
foreach ($permisos as $value) {
  if ($value["permiso"] == "Membresias" && $value["valor"] == "false") {
    echo "<script>window.location.href = '" . $dashboard . "'</script>";
  }
}
$id_empresa_detalle = $_GET["id_empresa"];
$input_id_empresa = "<input type='hidden' id='id_empresa_detalle' value='$id_empresa_detalle' >";
echo $input_id_empresa;
?>

<script src="https://cdn.paymentez.com/ccapi/sdk/payment_checkout_stable.min.js"></script>
<script src="https://cdn.paymentez.com/ccapi/sdk/payment_checkout_3.0.0.min.js"></script>
<style>
  .credit-card-item {
    border: 2px solid #e9ecef;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    background: #fff;
    position: relative;
  }

  .credit-card-item:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .credit-card-item.selected {
    border-color: #28a745;
    background-color: #f8fff9;
  }

  .credit-card-item.selected::after {
    content: "\f058";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #28a745;
    font-size: 1.2rem;
  }

  .card-number-mask {
    letter-spacing: 2px;
    font-family: 'Courier New', Courier, monospace;
    font-weight: bold;
    color: #495057;
  }

  .card-brand-icon {
    font-size: 1.8rem;
    width: 45px;
    text-align: center;
  }
</style>
<title>Asignar Membresía</title>
<div class="card mb-3">
  <div class="card-body">
    <div class="row justify-content-center">
      <div class="col-12 text-center mb-4">
        <!-- <div class="fs-8">Membresías</div> -->
        <h4 class="fs-9">
          Tu plan actual limita la cantidad de publicaciones <br>
          <strong>¡Libérate de límites actualizando a FULMUV Anual!</strong> <br>
          Al cambiarte al plan <strong>FULMUV Anual,</strong> podrás publicar <strong>todo tu catálogo</strong> de productos, <br> acceder a herramientas avanzadas, mayor visibilidad y envíos nacionales. <br>
          <strong>Y lo mejor:</strong> No pierdes lo que ya pagaste. Solo pagas la <strong>diferencia proporcional,</strong> <br> calculada automáticamente por la plataforma.
        </h4>

      </div>
      <div class="col-md-12 col-lg-12 col-xl-10">
        <div id="contenedor-membresias" class="row justify-content-center align-items-stretch"></div>
      </div>

    </div>
  </div>
  <div class="card-footer bg-light d-flex justify-content-end">
    <div class="me-3">
      <div class="input-group input-group-sm">

      </div>
    </div>
  </div>
</div>

<div id="response"></div>

<div class="modal fade" id="modalUpgradePago" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-xl modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i> Finalizar Cambio de Membresía</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
          <div class="col-md-5 border-end">
            <h6 class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Resumen del Upgrade</h6>
            <div id="detallePagoUpgrade" class="mb-3">
            </div>
            <div class="bg-light p-3 rounded shadow-sm">
              <div class="d-flex justify-content-between small mb-1">
                <span>Subtotal:</span>
                <span id="subtotalModal" class="fw-bold">$0.00</span>
              </div>
              <div class="d-flex justify-content-between small mb-1 text-success">
                <span>Crédito (A favor):</span>
                <span id="creditoModal" class="fw-bold">-$0.00</span>
              </div>
              <hr class="my-2">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">Total a pagar:</span>
                <span id="totalModal" class="h4 mb-0 fw-bold text-primary">$0.00</span>
              </div>
            </div>
          </div>

          <div class="col-md-7 ps-md-4">
            <h6 class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Selecciona tu Tarjeta Guardada</h6>
            <div id="listaTarjetas" class="pe-2" style="max-height: 280px; overflow-y: auto;">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnProcesarPago" class="btn btn-success px-4 fw-bold shadow-sm" disabled>
          <i class="fas fa-lock me-2"></i> Pagar y Confirmar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Conexión API js -->
<script src="js/upgrade_membresia.js?v1.0.0.0.1"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>

<?php
require 'includes/footer.php';
?>