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

<title>Asignar Membresía</title>
<div class="card mb-3">
  <div class="card-body">
    <div class="row justify-content-center">
      <div class="col-12 text-center mb-4">
        <div class="fs-8">Precios</div>
        <h3 class="fs-7 fs-md-6">Membresias que puede adquirir. <br class="d-none d-md-block" />Planes para empresas.</h3>
        <!--div class="d-flex justify-content-center">
          <label class="form-check-label me-2" for="customSwitch1" checked="checked">Monthly</label>
          <div class="form-check form-switch">
            <input class="form-check-input falcon-dual-switch" id="customSwitch1" type="checkbox" />
            <label class="form-check-label align-top" for="customSwitch1">Yearly</label>
          </div>
        </div-->
      </div>
      <div class="col-md-12 col-lg-12 col-xl-9">

        <ul class="nav nav-pills justify-content-center" id="pill-myTab" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-plan="30" data-bs-toggle="tab" href="#pill-tab-home" role="tab" aria-controls="pill-tab-home" aria-selected="true">Mensual</a></li>
          <li class="nav-item"><a class="nav-link" data-plan="180" data-bs-toggle="tab" href="#pill-tab-profile" role="tab" aria-controls="pill-tab-profile" aria-selected="false">Semestral</a></li>
          <li class="nav-item"><a class="nav-link" data-plan="360" data-bs-toggle="tab" href="#pill-tab-contact" role="tab" aria-controls="pill-tab-contact" aria-selected="false">Anual</a></li>
        </ul>
        <div class="tab-content p-3 mt-3 justify-content-center" id="pill-myTabContent">
          <div class="tab-pane fade show active" id="pill-tab-home" role="tabpanel" aria-labelledby="pill-home-tab"></div>
          <div class="tab-pane fade" id="pill-tab-profile" role="tabpanel" aria-labelledby="pill-profile-tab"></div>
          <div class="tab-pane fade" id="pill-tab-contact" role="tabpanel" aria-labelledby="pill-contact-tab"></div>
        </div>

      </div>
      <!--div class="col-12 text-center">
        <h5 class="mt-5">Looking for personal or small team task management?</h5>
        <p class="fs-8">Try the <a href="#">basic version</a> of Falcon</p>
      </div-->
    </div>
  </div>
</div>


<!-- Conexión API js -->
<script src="js/asignar_membresia2.js?v1.0.0"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>