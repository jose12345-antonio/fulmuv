<?php
$menu = "upgrade";
$sub_menu = "upgrade";
require 'includes/header.php';
foreach ($permisos as $value) {
  if ($value["permiso"] == "Membresias" && $value["valor"] == "false") {
    echo "<script>window.location.href = '" . $dashboard . "'</script>";
  }
}
$id_empresa_detalle = (int)($_GET["id_empresa"] ?? $id_empresa ?? 0);
echo "<input type='hidden' id='id_empresa_detalle' value='" . $id_empresa_detalle . "'>";
echo "<input type='hidden' id='correo_empresa_upgrade' value='" . htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') . "'>";
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

  /* ── Wallet: item de tarjeta compacto ── */
  .wallet-card-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
    user-select: none;
  }

  .wallet-card-item:hover {
    border-color: #0f766e;
    background: #f0fdfa;
    box-shadow: 0 2px 8px rgba(15, 118, 110, 0.12);
  }

  .wallet-card-item.selected {
    border-color: #22c55e;
    background: #f0fdf4;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
  }

  .wallet-card-item.brand-visa {
    background: linear-gradient(135deg, #072d43 0%, #0c3f59 62%, #0a2238 100%);
    border-color: rgba(12, 63, 89, .65);
    color: #fff;
  }

  .wallet-card-item.brand-mastercard {
    background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 55%, #94a3b8 100%);
    border-color: rgba(148, 163, 184, .75);
    color: #111827;
  }

  .wallet-card-item.brand-diners,
  .wallet-card-item.brand-amex,
  .wallet-card-item.brand-discover {
    color: #fff;
  }

  .wallet-card-item.brand-diners {
    background: linear-gradient(135deg, #0f766e 0%, #4338ca 100%);
  }

  .wallet-card-item.brand-amex {
    background: linear-gradient(135deg, #06b6d4 0%, #2563eb 100%);
  }

  .wallet-card-item.brand-discover {
    background: linear-gradient(135deg, #f43f5e 0%, #f59e0b 100%);
  }

  .wallet-card-item.expired {
    border-color: #fecaca;
    background: #fef2f2;
    cursor: not-allowed;
    opacity: 0.85;
  }

  .wallet-card-item.expired:hover {
    border-color: #fecaca;
    background: #fef2f2;
    box-shadow: none;
  }

  .wallet-card-icon {
    font-size: 2rem;
    width: 38px;
    text-align: center;
    flex-shrink: 0;
    line-height: 1;
  }

  .wallet-card-info {
    flex: 1;
    min-width: 0;
  }

  .wallet-card-number {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.88rem;
    font-weight: 600;
    color: #1e293b;
    letter-spacing: 1.5px;
  }

  .wallet-card-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 3px;
    font-size: 0.73rem;
    color: #64748b;
  }

  .wallet-card-item.brand-visa .wallet-card-number,
  .wallet-card-item.brand-diners .wallet-card-number,
  .wallet-card-item.brand-amex .wallet-card-number,
  .wallet-card-item.brand-discover .wallet-card-number {
    color: #fff;
  }

  .wallet-card-item.brand-visa .wallet-card-meta,
  .wallet-card-item.brand-diners .wallet-card-meta,
  .wallet-card-item.brand-amex .wallet-card-meta,
  .wallet-card-item.brand-discover .wallet-card-meta {
    color: rgba(255, 255, 255, .82);
  }

  .wallet-sdk-badge {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .7rem .95rem;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: .78rem;
    font-weight: 700;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
  }

  .wallet-sdk-badge-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 96px;
    min-height: 34px;
    padding: .2rem .35rem;
    border-radius: 12px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, .16);
  }

  .wallet-sdk-badge-logo img {
    width: 84px;
    height: auto;
    display: block;
  }

  .wallet-sdk-badge-copy {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
  }

  .wallet-sdk-badge-copy strong {
    font-size: .8rem;
    color: #0f172a;
  }

  .wallet-sdk-badge-copy span {
    font-size: .72rem;
    color: #64748b;
    font-weight: 600;
  }

  .wallet-card-expiry {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.75rem;
  }

  .wallet-card-expiry.is-expired {
    color: #ef4444;
    font-weight: 700;
  }

  .wallet-expired-badge {
    font-size: 0.58rem;
    background: #ef4444;
    color: #fff;
    padding: 1px 7px;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    vertical-align: middle;
  }

  .wallet-card-bank {
    color: #94a3b8;
    font-size: 0.72rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
  }

  .wallet-card-check {
    flex-shrink: 0;
    color: #22c55e;
    font-size: 1.15rem;
    display: none;
  }

  .wallet-card-item.selected .wallet-card-check {
    display: block;
  }

  /* ── Separador "o agregar" ── */
  .wallet-divider {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #94a3b8;
    font-size: 0.75rem;
    margin: 10px 0 8px;
  }

  .wallet-divider::before,
  .wallet-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
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

<style>
  #modalUpgradePago .modal-content {
    border-radius: 18px;
    overflow: hidden;
  }

  #modalUpgradePago .modal-header {
    background: linear-gradient(135deg, #0f766e, #1e293b);
    color: #fff;
  }

  #modalUpgradePago .summary-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
  }

  #modalUpgradePago .summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    padding: 6px 0;
  }

  #modalUpgradePago .summary-total {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f766e;
  }

  #modalUpgradePago .card-list-wrap { 
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px;
  }

  body.nuvei-checkout-open .modal-backdrop.show {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }
</style>

<div class="modal fade" id="modalUpgradePago" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-xl modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i> Finalizar Cambio de Membresía</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
          <div class="col-md-5 border-end">
            <h6 class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Resumen del Upgrade</h6>
            <div id="detallePagoUpgrade" class="mb-3">
            </div>
            <div class="summary-card">
              <div class="summary-row">
                <span>Subtotal:</span>
                <span id="subtotalModal" class="fw-bold">$0.00</span>
              </div>
              <div class="summary-row text-success">
                <span>Crédito (A favor):</span>
                <span id="creditoModal" class="fw-bold">-$0.00</span>
              </div>
              <hr class="my-2">
              <div class="summary-row align-items-center">
                <span class="fw-bold">Total a pagar:</span>
                <span id="totalModal" class="summary-total">$0.00</span>
              </div>
            </div>
          </div>

          <div class="col-md-7 ps-md-4">
            <h6 class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Selecciona tu Tarjeta Guardada</h6>
            <div id="listaTarjetas" class="card-list-wrap pe-2" style="max-height: 260px; overflow-y: auto;">
            </div>

            <!-- Opción nueva tarjeta -->
            <div class="mt-3">
              <button id="btnNuevaTarjeta" type="button" class="btn btn-outline-secondary btn-sm w-100">
                <i class="fas fa-plus-circle me-2"></i> Pagar con nueva tarjeta
              </button>
            </div>

            <!-- Formulario Nuvei para nueva tarjeta (PaymentCheckout abre su propio overlay) -->
            <div id="nuevaTarjetaForm" style="display:none;" class="mt-3">
              <div class="wallet-sdk-badge mb-2">
                <span class="wallet-sdk-badge-logo">
                  <img src="files/Nuvei_Organization_logo.png" alt="Nuvei">
                </span>
                <span class="wallet-sdk-badge-copy">
                  <strong>Checkout seguro con Nuvei</strong>
                  <span>Tu tarjeta se ingresa en el formulario oficial del SDK</span>
                </span>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="chkGuardarTarjeta" checked>
                <label class="form-check-label small text-muted" for="chkGuardarTarjeta">
                  Guardar tarjeta en Mi Wallet para pagos futuros
                </label>
              </div>
              <button id="tokenize_btn_upgrade" type="button"
                class="btn btn-success w-100 fw-bold shadow-sm">
                <i class="fas fa-credit-card me-2"></i> Ingresar datos de tarjeta
              </button>
              <div id="tokenize_response_upgrade" class="mt-2 small"></div>
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
<script src="js/upgrade_membresia.js?v1.0.0.1.7"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>

<?php
require 'includes/footer.php';
?>
