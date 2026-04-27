<?php
$menu = "tarjetas";
require 'includes/header.php';
if ($tipo_user === "sucursal") {
    echo "<script>window.location.href = '" . $dashboard . "'</script>";
}
?>
<title>Tarjetas</title>
<input type="hidden" id="correo_empresa_wallet" value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>">

<script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>

<style>
  .wallet-card-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .wallet-card-visual {
    position: relative;
    min-height: 185px;
    border-radius: 22px;
    padding: 18px 18px 16px;
    color: #fff;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(15, 23, 42, .18);
    transition: transform .18s ease, box-shadow .18s ease;
  }

  .wallet-card-visual:hover {
    transform: translateY(-3px);
    box-shadow: 0 26px 46px rgba(15, 23, 42, .24);
  }

  .wallet-card-visual::before,
  .wallet-card-visual::after {
    content: "";
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    opacity: .28;
  }

  .wallet-card-visual::before {
    width: 220px;
    height: 220px;
    top: -70px;
    right: -60px;
    background: rgba(255, 255, 255, .18);
  }

  .wallet-card-visual::after {
    width: 180px;
    height: 180px;
    bottom: -80px;
    left: -55px;
    background: rgba(255, 255, 255, .12);
  }

  .wallet-card-visual.is-default {
    outline: 3px solid rgba(255, 255, 255, .34);
    outline-offset: -3px;
  }

  .wallet-card-visual.is-expired {
    filter: saturate(.78);
  }

  .wallet-card-top,
  .wallet-card-bottom {
    position: relative;
    z-index: 1;
  }

  .wallet-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .wallet-card-statuses {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
  }

  .wallet-card-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .65rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, .16);
    border: 1px solid rgba(255, 255, 255, .18);
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .02em;
    backdrop-filter: blur(6px);
  }

  .wallet-card-visual.text-dark {
    color: #111827;
  }

  .wallet-card-visual.text-dark .wallet-card-number,
  .wallet-card-visual.text-dark .wallet-card-holder-value,
  .wallet-card-visual.text-dark .wallet-card-exp-value,
  .wallet-card-visual.text-dark .wallet-card-client-value,
  .wallet-card-visual.text-dark .wallet-card-brand,
  .wallet-card-visual.text-dark .wallet-card-menu .btn {
    color: #111827;
  }

  .wallet-card-visual.text-dark .wallet-card-holder-label,
  .wallet-card-visual.text-dark .wallet-card-exp-label,
  .wallet-card-visual.text-dark .wallet-card-client-label {
    color: rgba(17, 24, 39, .72);
    opacity: 1;
  }

  .wallet-card-visual.text-dark .wallet-card-pill,
  .wallet-card-visual.text-dark .wallet-card-menu .btn {
    background: rgba(255, 255, 255, .42);
    border-color: rgba(17, 24, 39, .10);
  }

  .wallet-card-brand {
    font-size: 1.95rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    min-width: 70px;
  }

  .wallet-card-number {
    position: relative;
    z-index: 1;
    margin-bottom: .95rem;
    font-family: 'Courier New', Courier, monospace;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 2.2px;
    color: #fff;
  }

  .wallet-card-meta {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: .75rem;
  }

  .wallet-card-client {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .45rem .6rem;
    margin-top: .9rem;
    padding-right: 2.75rem;
  }

  .wallet-card-client-item {
    min-width: 0;
  }

  .wallet-card-client-item-wide {
    grid-column: 1 / -1;
  }

  .wallet-card-client-label {
    display: block;
    font-size: .55rem;
    font-weight: 700;
    letter-spacing: .12em;
    opacity: .72;
    margin-bottom: .18rem;
    text-transform: uppercase;
  }

  .wallet-card-client-value {
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .03em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .wallet-card-holder-label,
  .wallet-card-exp-label {
    display: block;
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .12em;
    opacity: .7;
    margin-bottom: .2rem;
    text-transform: uppercase;
  }

  .wallet-card-holder-value,
  .wallet-card-exp-value {
    font-size: .8rem;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
  }

  .wallet-card-menu {
    position: absolute;
    right: 14px;
    bottom: 14px;
    z-index: 3;
  }

  .wallet-card-menu .btn {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .18);
    background: rgba(255, 255, 255, .12);
    color: #fff;
    box-shadow: none !important;
    backdrop-filter: blur(10px);
  }

  .wallet-card-menu .dropdown-menu {
    min-width: 220px;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 24px 50px rgba(15, 23, 42, .22);
    overflow: hidden;
    padding: .5rem;
  }

  .wallet-card-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: .65rem;
    border-radius: 12px;
    padding: .7rem .85rem;
    font-weight: 600;
  }

  .wallet-card-menu .dropdown-item i {
    width: 16px;
    text-align: center;
  }

  .wallet-card-visual.wallet-brand-visa::before {
    background: rgba(26, 84, 107, .22);
  }

  .wallet-card-visual.wallet-brand-visa::after {
    background: rgba(255, 214, 102, .12);
  }

  .wallet-card-visual.wallet-brand-mastercard::before {
    background: rgba(255, 255, 255, .32);
  }

  .wallet-card-visual.wallet-brand-mastercard::after {
    background: rgba(255, 255, 255, .18);
  }

  .wallet-panel {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #fff;
  }

  .wallet-empty {
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    padding: 28px 18px;
    text-align: center;
    color: #64748b;
    background: #f8fafc;
  }

  #tokenize_example {
    min-height: 180px;
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
    margin-bottom: .85rem;
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

  html[data-bs-theme="dark"] .wallet-panel {
    background: #0f172a;
    border-color: rgba(148, 163, 184, .18);
  }

  html[data-bs-theme="dark"] .wallet-sdk-badge {
    background: rgba(15, 23, 42, .8);
    border-color: rgba(148, 163, 184, .18);
    color: #e2e8f0;
  }

  html[data-bs-theme="dark"] .wallet-sdk-badge-logo {
    background: rgba(255, 255, 255, .96);
  }

  html[data-bs-theme="dark"] .wallet-sdk-badge-copy strong {
    color: #e2e8f0;
  }

  html[data-bs-theme="dark"] .wallet-sdk-badge-copy span {
    color: #cbd5e1;
  }

  html[data-bs-theme="dark"] .wallet-card-menu .dropdown-menu {
    background: #0f172a;
    box-shadow: 0 24px 50px rgba(2, 6, 23, .45);
  }

  html[data-bs-theme="dark"] .wallet-card-menu .dropdown-item {
    color: #e2e8f0;
  }

  html[data-bs-theme="dark"] .wallet-card-menu .dropdown-item:hover {
    background: rgba(148, 163, 184, .12);
  }

  @media (max-width: 991.98px) {
    .wallet-card-grid {
      grid-template-columns: 1fr;
    }

    .wallet-card-client {
      grid-template-columns: 1fr;
      padding-right: 0;
    }

    .wallet-card-client-item-wide {
      grid-column: auto;
    }
  }
</style>

<div class="row g-3">
  <div class="col-xl-7">
    <div class="card wallet-panel h-100">
      <div class="card-header bg-transparent border-0 pb-0">
        <h5 class="mb-1">Tarjetas guardadas</h5>
        <p class="text-600 mb-0 fs--1">Administra las tarjetas registradas para cobros con token. Las vencidas se muestran en rojo.</p>
      </div>
      <div class="card-body">
        <div id="walletList">
          <div class="text-center py-4 text-600">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>Cargando tarjetas...
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card wallet-panel h-100">
      <div class="card-header bg-transparent border-0 pb-0">
        <h5 class="mb-1">Agregar tarjeta</h5>
        <p class="text-600 mb-0 fs--1">Ingresa los datos en el formulario seguro de Paymentez. No almacenamos datos sensibles en el panel.</p>
      </div>
      <div class="card-body">
        <div class="wallet-sdk-badge">
          <span class="wallet-sdk-badge-logo">
            <img src="files/Nuvei_Organization_logo.png" alt="Paymentez">
          </span>
          <span class="wallet-sdk-badge-copy">
            <strong>Pagos protegidos con Paymentez</strong>
            <span>Formulario seguro generado por el flujo oficial de tarjetas</span>
          </span>
        </div>
        <div id="walletModeBadge" class="alert alert-info py-2 px-3 small d-none"></div>
        <div id="tokenize_example" class="mb-3"></div>
        <div id="tokenize_response" class="small mb-3"></div>
        <button id="tokenize_btn" class="btn btn-primary w-100" type="button">
          <i class="fas fa-plus-circle me-2"></i>Guardar tarjeta
        </button>
        <button id="walletCancelEdit" class="btn btn-link w-100 mt-2 d-none" type="button">Cancelar edición</button>
      </div>
    </div>
  </div>
</div>
 
<script src="js/tarjetas.js?v2.1.20"></script>
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
 