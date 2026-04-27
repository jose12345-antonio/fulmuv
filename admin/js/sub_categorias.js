const DT_ES = {
  emptyTable: "No hay datos disponibles", info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
  infoEmpty: "Mostrando 0 a 0 de 0 registros", infoFiltered: "(filtrado de _MAX_ totales)",
  thousands: ".", lengthMenu: "Mostrar _MENU_ registros", loadingRecords: "Cargando...",
  processing: "Procesando...", search: "Buscar:", zeroRecords: "No se encontraron resultados",
  paginate: { first: "Primero", last: "Último", next: '<span class="fas fa-chevron-right"></span>', previous: '<span class="fas fa-chevron-left"></span>' }
};
let _categorias = [];
$(document).ready(function () {
  $.get('../api/v1/fulmuv/categorias/All', {}, function (raw) { const r = JSON.parse(raw); if (!r.error) { _categorias = r.data; } });
  cargarTabla();
});
function cargarTabla() {
  if ($.fn.DataTable.isDataTable('#my_table')) { $('#my_table').DataTable().destroy(); }
  $('#lista_categorias').empty();
  $.get('../api/v1/fulmuv/sub_categorias/', {}, function (raw) {
    const r = JSON.parse(raw);
    if (!r.error) {
      r.data.forEach(function (c) {
        $('#lista_categorias').append(`<tr><td class="align-middle fw-semibold ps-3">${c.nombre}</td><td class="align-middle">${c.categoria || '-'}</td><td class="align-middle text-end pe-3" style="width:110px"><button class="btn btn-sm btn-outline-primary me-1" onclick="abrirEditar(${c.id_sub_categoria})"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-outline-danger" onclick="eliminar(${c.id_sub_categoria})"><i class="fas fa-trash"></i></button></td></tr>`);
      });
    }
    $('#my_table').DataTable({ searching: true, responsive: false, pageLength: 25, info: true, lengthChange: false, language: DT_ES, dom: "<'row mx-0'<'col-md-6'l><'col-md-6'f>><'table-responsive scrollbar'tr><'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>" });
  });
}
function _opsCat(sel) {
  let o = '<option value="">Seleccione categoría</option>';
  _categorias.forEach(function (c) { o += `<option value="${c.id_categoria}" ${String(c.id_categoria)===String(sel)?'selected':''}>${c.nombre}</option>`; });
  return o;
}
function _modal(titulo, nombre, catId, onGuardar) {
  $('#modalSC').remove();
  $('body').append(`<div class="modal fade" id="modalSC" data-bs-backdrop="static" tabindex="-1"><div class="modal-dialog modal-md mt-6"><div class="modal-content border-0 shadow"><div class="modal-header bg-body-tertiary border-0"><h5 class="modal-title fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>${titulo}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label><input class="form-control" id="sc_nombre" type="text" placeholder="Nombre" oninput="this.value=this.value.toUpperCase()"></div><div class="mb-3"><label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label><select class="form-select" id="sc_categoria">${_opsCat(catId)}</select></div></div><div class="modal-footer border-0"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button><button class="btn btn-primary btn-sm" id="btnGuardarSC"><i class="fas fa-save me-1"></i>Guardar</button></div></div></div></div>`);
  if (nombre) { $('#sc_nombre').val(nombre); }
  $('#btnGuardarSC').off('click').on('click', onGuardar);
  new bootstrap.Modal(document.getElementById('modalSC')).show();
}
function abrirCrear() {
  _modal('Nueva Subcategoría', '', '', function () {
    const nombre = $('#sc_nombre').val().trim(), id_categoria = $('#sc_categoria').val();
    if (!nombre || !id_categoria) { toastr.error('Todos los campos son obligatorios.'); return; }
    $.post('../api/v1/fulmuv/sub_categorias/create', { nombre, id_categoria }, function (raw) {
      const r = JSON.parse(raw);
      if (!r.error) { bootstrap.Modal.getInstance(document.getElementById('modalSC')).hide(); toastr.success(r.msg); cargarTabla(); }
      else { toastr.error(r.msg); }
    });
  });
}
function abrirEditar(id) {
  $.get('../api/v1/fulmuv/sub_categorias/' + id, function (r) {
    if (r.error) { toastr.error('No se pudo cargar.'); return; }
    _modal('Editar Subcategoría', r.data.nombre, r.data.id_categoria, function () {
      const nombre = $('#sc_nombre').val().trim(), id_categoria = $('#sc_categoria').val();
      if (!nombre || !id_categoria) { toastr.error('Todos los campos son obligatorios.'); return; }
      $.post('../api/v1/fulmuv/sub_categorias/update', { id, nombre, id_categoria }, function (raw) {
        const res = JSON.parse(raw);
        if (!res.error) { bootstrap.Modal.getInstance(document.getElementById('modalSC')).hide(); toastr.success(res.msg); cargarTabla(); }
        else { toastr.error(res.msg); }
      });
    });
  }, 'json');
}
function eliminar(id) {
  swal({ title: 'Eliminar', text: '¿Desea eliminar esta subcategoría?', type: 'warning', showCancelButton: true, confirmButtonColor: '#e63757', confirmButtonText: 'Sí', cancelButtonText: 'No', closeOnConfirm: false },
    function () {
      $.post('../api/v1/fulmuv/sub_categorias/delete', { id }, function (raw) {
        const r = JSON.parse(raw);
        if (!r.error) { swal.close(); toastr.success(r.msg); cargarTabla(); } else { swal('Error', r.msg, 'error'); }
      });
    });
}
