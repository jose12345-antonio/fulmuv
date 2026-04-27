<?php
$menu = "empresa";
$sub_menu = "asignar_membresia";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Membresias" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}


// Simulando datos de empresas
$empresas = [
  ["id" => 1, "nombre" => "BONSAI", "membresia" => "Sin membresía"],
  ["id" => 2, "nombre" => "EMPRESA 3", "membresia" => "Sin membresía"]
];
?>

<title>Asignar Membresía</title>
<div class="card mb-3">
  <div class="card-header">
      <h5>Asignar Membresía a Empresas</h5>
  </div>
  <div class="card-body">
      <table class="table">
          <thead>
              <tr>
                  <th>Empresa</th>
                  <th>Membresía Actual</th>
                  <th>Acciones</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach ($empresas as $empresa): ?>
              <tr>
                  <td><?= $empresa['nombre'] ?></td>
                  <td><?= $empresa['membresia'] ?></td>
                  <td>
                      <button class="btn btn-primary" onclick="abrirModal(<?= $empresa['id'] ?>, '<?= $empresa['nombre'] ?>')">Asignar</button>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  </div>
</div>

<!-- Modal -->
<div class="modal" tabindex="-1" role="dialog" id="modalMembresia">
<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Asignar Membresía</h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
      <form id="formMembresia">
        <input type="hidden" id="empresaId">
        <div class="form-group">
          <label for="empresaNombre">Empresa</label>
          <input type="text" id="empresaNombre" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label for="tipoMembresia">Tipo de Membresía</label>
          <select id="tipoMembresia" class="form-control" onchange="actualizarPlanes()">
            <option value="">Seleccione</option>
            <option value="OneMuv">OneMuv</option>
            <option value="FulMuv">FulMuv</option>
            <option value="BasicMuv">BasicMuv</option>
          </select>
        </div>
        <div class="form-group">
          <label for="planPago">Plan de Pago</label>
          <select id="planPago" class="form-control"></select>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" onclick="guardarMembresia()">Guardar</button>
      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
    </div>
  </div>
</div>
</div>




<!-- Conexión API js -->
<script src="js/asignar_membresia.js?v1.0.0"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>