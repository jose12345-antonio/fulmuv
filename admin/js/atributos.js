const DT_ES = {
  emptyTable: "No hay datos disponibles", info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
  infoEmpty: "Mostrando 0 a 0 de 0 registros", infoFiltered: "(filtrado de _MAX_ totales)",
  thousands: ".", lengthMenu: "Mostrar _MENU_ registros", loadingRecords: "Cargando...",
  processing: "Procesando...", search: "Buscar:", zeroRecords: "No se encontraron resultados",
  paginate: { first: "Primero", last: "Último", next: '<span class="fas fa-chevron-right"></span>', previous: '<span class="fas fa-chevron-left"></span>' }
};
$(document).ready(function () {
  cargarTabla();
});
function cargarTabla() {
  if ($.fn.DataTable.isDataTable('#my_table')) { $('#my_table').DataTable().destroy(); }
  $('#lista_categorias').empty();
  $.get('../api/v1/fulmuv/atributos/', {}, function (raw) {
    const r = JSON.parse(raw);
    if (!r.error) {
      r.data.forEach(function (a) {
        const tipoBadge = a.tipo_dato === 'OPCIONES' ? 'badge bg-warning text-dark' : a.tipo_dato === 'NUMBER' ? 'badge bg-info' : 'badge bg-secondary';
        const opcs = a.opciones ? `<small class="text-muted">${a.opciones}</small>` : '-';
        $('#lista_categorias').append(`<tr><td class="align-middle fw-semibold ps-3">${a.nombre}</td><td class="align-middle"><span class="${tipoBadge}">${a.tipo_dato || '-'}</span></td><td class="align-middle">${opcs}</td><td class="align-middle text-end pe-3" style="width:110px"><button class="btn btn-sm btn-outline-primary me-1" onclick="abrirEditar(${a.id_atributo})"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-outline-danger" onclick="eliminar(${a.id_atributo})"><i class="fas fa-trash"></i></button></td></tr>`);
      });
    }
    $('#my_table').DataTable({ searching: true, responsive: false, pageLength: 25, info: true, lengthChange: false, language: DT_ES, dom: "<'row mx-0'<'col-md-6'l><'col-md-6'f>><'table-responsive scrollbar'tr><'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>" });
  });
}
function _toggleOpciones() {
  const tipo = $('#at_tipo_dato').val();
  $('#fila_opciones').toggle(tipo === 'OPCIONES');
}
function _modal(titulo, d, onGuardar) {
  $('#modalAT').remove();
  $('body').append(`<div class="modal fade" id="modalAT" data-bs-backdrop="static" tabindex="-1"><div class="modal-dialog modal-lg mt-6"><div class="modal-content border-0 shadow"><div class="modal-header bg-body-tertiary border-0"><h5 class="modal-title fw-bold"><i class="fas fa-tags me-2 text-primary"></i>${titulo}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3"><div class="col-md-8"><label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label><input class="form-control" id="at_nombre" type="text" placeholder="Nombre del atributo" oninput="this.value=this.value.toUpperCase()"></div><div class="col-md-4"><label class="form-label fw-semibold">Tipo de dato <span class="text-danger">*</span></label><select class="form-select" id="at_tipo_dato" onchange="_toggleOpciones()"><option value="TEXT">TEXT</option><option value="NUMBER">NUMBER</option><option value="OPCIONES">OPCIONES</option></select></div><div class="col-12" id="fila_opciones" style="display:none"><label class="form-label fw-semibold">Opciones <small class="text-muted fw-normal">(JSON, ej: ["Rojo","Azul"])</small></label><textarea class="form-control" id="at_opciones" rows="3" placeholder='["Opción 1","Opción 2"]'></textarea></div></div></div><div class="modal-footer border-0"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button><button class="btn btn-primary btn-sm" id="btnGuardarAT"><i class="fas fa-save me-1"></i>Guardar</button></div></div></div></div>`);
  if (d) {
    $('#at_nombre').val(d.nombre);
    $('#at_tipo_dato').val(d.tipo_dato || 'TEXT');
    $('#at_opciones').val(d.opciones || '');
    _toggleOpciones();
  }
  $('#btnGuardarAT').off('click').on('click', onGuardar);
  new bootstrap.Modal(document.getElementById('modalAT')).show();
}
function abrirCrear() {
  _modal('Nuevo Atributo', null, function () {
    const nombre = $('#at_nombre').val().trim(), tipo_dato = $('#at_tipo_dato').val();
    if (!nombre) { toastr.error('El nombre es obligatorio.'); return; }
    const opciones = tipo_dato === 'OPCIONES' ? $('#at_opciones').val().trim() : null;
    $.post('../api/v1/fulmuv/atributos/create', { nombre, tipo_dato, opciones }, function (raw) {
      const r = JSON.parse(raw);
      if (!r.error) { bootstrap.Modal.getInstance(document.getElementById('modalAT')).hide(); toastr.success(r.msg); cargarTabla(); }
      else { toastr.error(r.msg); }
    });
  });
}
function abrirEditar(id) {
  $.get('../api/v1/fulmuv/getAtributoById/' + id, function (r) {
    if (r.error) { toastr.error('No se pudo cargar.'); return; }
    _modal('Editar Atributo', r.data, function () {
      const nombre = $('#at_nombre').val().trim(), tipo_dato = $('#at_tipo_dato').val();
      if (!nombre) { toastr.error('El nombre es obligatorio.'); return; }
      const opciones = tipo_dato === 'OPCIONES' ? $('#at_opciones').val().trim() : null;
      $.post('../api/v1/fulmuv/atributos/update', { id, nombre, tipo_dato, opciones }, function (raw) {
        const res = JSON.parse(raw);
        if (!res.error) { bootstrap.Modal.getInstance(document.getElementById('modalAT')).hide(); toastr.success(res.msg); cargarTabla(); }
        else { toastr.error(res.msg); }
      });
    });
  }, 'json');
}
function eliminar(id) {
  swal({ title: 'Eliminar', text: '¿Desea eliminar este atributo?', type: 'warning', showCancelButton: true, confirmButtonColor: '#e63757', confirmButtonText: 'Sí', cancelButtonText: 'No', closeOnConfirm: false },
    function () {
      $.post('../api/v1/fulmuv/atributos/delete', { id }, function (raw) {
        const r = JSON.parse(raw);
        if (!r.error) { swal.close(); toastr.success(r.msg); cargarTabla(); } else { swal('Error', r.msg, 'error'); }
      });
    });
}
