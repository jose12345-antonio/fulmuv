const DT_ES = {
  emptyTable: "No hay datos disponibles", info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
  infoEmpty: "Mostrando 0 a 0 de 0 registros", infoFiltered: "(filtrado de _MAX_ totales)",
  thousands: ".", lengthMenu: "Mostrar _MENU_ registros", loadingRecords: "Cargando...",
  processing: "Procesando...", search: "Buscar:", zeroRecords: "No se encontraron resultados",
  paginate: { first: "Primero", last: "Último", next: '<span class="fas fa-chevron-right"></span>', previous: '<span class="fas fa-chevron-left"></span>' }
};

$(document).ready(function () { cargarTabla(); });

function cargarTabla() {
  if ($.fn.DataTable.isDataTable('#my_table')) { $('#my_table').DataTable().destroy(); }
  $('#lista_categorias').empty();
  $.get('../api/v1/fulmuv/categoriasPrincipales/All', {}, function (raw) {
    const r = JSON.parse(raw);
    if (!r.error) {
      r.data.forEach(function (c) {
        $('#lista_categorias').append(`
          <tr>
            <td class="align-middle fw-semibold">${c.nombre}</td>
            <td class="align-middle text-end" style="width:110px">
              <button class="btn btn-sm btn-outline-primary me-1" title="Editar" onclick="abrirEditar(${c.id_categoria_principal})"><i class="fas fa-pen"></i></button>
              <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminar(${c.id_categoria_principal})"><i class="fas fa-trash"></i></button>
            </td>
          </tr>`);
      });
    }
    $('#my_table').DataTable({ searching: true, responsive: false, pageLength: 25, info: true, lengthChange: false, language: DT_ES, dom: "<'row mx-0'<'col-md-6'l><'col-md-6'f>><'table-responsive scrollbar'tr><'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>" });
  });
}

function modal(titulo, nombre, onGuardar) {
  $('#modalCrudCP').remove();
  $('body').append(`
    <div class="modal fade" id="modalCrudCP" data-bs-backdrop="static" tabindex="-1">
      <div class="modal-dialog modal-md mt-6">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-body-tertiary border-0 pb-2">
            <h5 class="modal-title fw-bold"><i class="fas fa-tag me-2 text-primary"></i>${titulo}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
              <input class="form-control" id="cp_nombre" type="text" placeholder="Ingrese el nombre" oninput="this.value=this.value.toUpperCase()">
            </div>
          </div>
          <div class="modal-footer border-0">
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button>
            <button class="btn btn-primary btn-sm" id="btnGuardarCP"><i class="fas fa-save me-1"></i>Guardar</button>
          </div>
        </div>
      </div>
    </div>`);
  if (nombre) $('#cp_nombre').val(nombre);
  $('#btnGuardarCP').off('click').on('click', onGuardar);
  new bootstrap.Modal(document.getElementById('modalCrudCP')).show();
}

function abrirCrear() {
  modal('Nueva Categoría Principal', '', function () {
    const nombre = $('#cp_nombre').val().trim();
    if (!nombre) { toastr.error('El nombre es obligatorio.'); return; }
    $.post('../api/v1/fulmuv/categoriasPrincipales/create', { nombre: nombre, imagen: '' }, function (raw) {
      const r = JSON.parse(raw);
      if (!r.error) { bootstrap.Modal.getInstance(document.getElementById('modalCrudCP')).hide(); toastr.success(r.msg); cargarTabla(); }
      else { toastr.error(r.msg); }
    });
  });
}

function abrirEditar(id) {
  $.get('../api/v1/fulmuv/categoriaPrincipal/' + id, function (r) {
    if (r.error) { toastr.error('No se pudo cargar.'); return; }
    modal('Editar Categoría Principal', r.data.nombre, function () {
      const nombre = $('#cp_nombre').val().trim();
      if (!nombre) { toastr.error('El nombre es obligatorio.'); return; }
      $.post('../api/v1/fulmuv/categoriaPrincipal/update', { id_categoria: id, nombre: nombre, imagen: r.data.imagen }, function (raw) {
        const res = JSON.parse(raw);
        if (!res.error) { bootstrap.Modal.getInstance(document.getElementById('modalCrudCP')).hide(); toastr.success(res.msg); cargarTabla(); }
        else { toastr.error(res.msg); }
      });
    });
  }, 'json');
}

function eliminar(id) {
  swal({ title: 'Eliminar', text: '¿Está seguro que desea eliminar esta categoría?', type: 'warning', showCancelButton: true, confirmButtonColor: '#e63757', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', closeOnConfirm: false },
    function () {
      $.post('../api/v1/fulmuv/categorias_principales/delete', { id: id }, function (raw) {
        const r = JSON.parse(raw);
        if (!r.error) { swal.close(); toastr.success(r.msg); cargarTabla(); }
        else { swal('Error', r.msg, 'error'); }
      });
    });
}
