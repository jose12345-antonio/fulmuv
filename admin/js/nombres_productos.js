const DT_ES = {
  emptyTable: "No hay datos disponibles", info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
  infoEmpty: "Mostrando 0 a 0 de 0 registros", infoFiltered: "(filtrado de _MAX_ totales)",
  thousands: ".", lengthMenu: "Mostrar _MENU_ registros", loadingRecords: "Cargando...",
  processing: "Procesando...", search: "Buscar:", zeroRecords: "No se encontraron resultados",
  paginate: { first: "Primero", last: "Último", next: '<span class="fas fa-chevron-right"></span>', previous: '<span class="fas fa-chevron-left"></span>' }
};
let _categorias = [], _subcategorias = [];
$(document).ready(function () {
  $.get('../api/v1/fulmuv/categorias/All', {}, function (raw) { const r = JSON.parse(raw); if (!r.error) { _categorias = r.data; } });
  $.get('../api/v1/fulmuv/sub_categorias/', {}, function (raw) { const r = JSON.parse(raw); if (!r.error) { _subcategorias = r.data; } });
  cargarTabla();
});
function cargarTabla() {
  if ($.fn.DataTable.isDataTable('#my_table')) { $('#my_table').DataTable().destroy(); }
  $('#lista_categorias').empty();
  $.get('../api/v1/fulmuv/nombres_productos/', {}, function (raw) {
    const r = JSON.parse(raw);
    if (!r.error) {
      r.data.forEach(function (c) {
        $('#lista_categorias').append(`<tr><td class="align-middle fw-semibold ps-3">${c.nombre}</td><td class="align-middle">${c.categoria || '-'}</td><td class="align-middle">${c.sub_categoria || '-'}</td><td class="align-middle text-end pe-3" style="width:110px"><button class="btn btn-sm btn-outline-primary me-1" onclick="abrirEditar(${c.id_nombre_producto})"><i class="fas fa-pen"></i></button><button class="btn btn-sm btn-outline-danger" onclick="eliminar(${c.id_nombre_producto})"><i class="fas fa-trash"></i></button></td></tr>`);
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
function _opsSub(catId, sel) {
  let o = '<option value="">Seleccione subcategoría</option>';
  _subcategorias.filter(s => !catId || String(s.id_categoria) === String(catId)).forEach(function (s) { o += `<option value="${s.id_sub_categoria}" ${String(s.id_sub_categoria)===String(sel)?'selected':''}>${s.nombre}</option>`; });
  return o;
}
function _modal(titulo, d, onGuardar) {
  $('#modalNP').remove();
  $('body').append(`<div class="modal fade" id="modalNP" data-bs-backdrop="static" tabindex="-1"><div class="modal-dialog modal-lg mt-6"><div class="modal-content border-0 shadow"><div class="modal-header bg-body-tertiary border-0"><h5 class="modal-title fw-bold"><i class="fas fa-box me-2 text-primary"></i>${titulo}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label><input class="form-control" id="np_nombre" type="text" placeholder="Nombre del producto" oninput="this.value=this.value.toUpperCase()"></div><div class="row g-3"><div class="col-md-6"><label class="form-label fw-semibold">Categoría</label><select class="form-select" id="np_categoria" onchange="actualizarSubcategorias()">${_opsCat(d ? d.categoria : '')}</select></div><div class="col-md-6"><label class="form-label fw-semibold">Subcategoría</label><select class="form-select" id="np_subcategoria">${_opsSub(d ? d.categoria : '', d ? d.sub_categoria : '')}</select></div></div></div><div class="modal-footer border-0"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button><button class="btn btn-primary btn-sm" id="btnGuardarNP"><i class="fas fa-save me-1"></i>Guardar</button></div></div></div></div>`);
  if (d) { $('#np_nombre').val(d.nombre); }
  $('#btnGuardarNP').off('click').on('click', onGuardar);
  new bootstrap.Modal(document.getElementById('modalNP')).show();
}
function actualizarSubcategorias() {
  const catId = $('#np_categoria').val();
  $('#np_subcategoria').html(_opsSub(catId, ''));
}
function abrirCrear() {
  _modal('Nuevo Nombre de Producto', null, function () {
    const nombre = $('#np_nombre').val().trim();
    if (!nombre) { toastr.error('El nombre es obligatorio.'); return; }
    $.post('../api/v1/fulmuv/nombres_productos/create', { nombre, categoria: $('#np_categoria').val(), sub_categoria: $('#np_subcategoria').val() }, function (raw) {
      const r = JSON.parse(raw);
      if (!r.error) { bootstrap.Modal.getInstance(document.getElementById('modalNP')).hide(); toastr.success(r.msg); cargarTabla(); }
      else { toastr.error(r.msg); }
    });
  });
}
function abrirEditar(id) {
  $.get('../api/v1/fulmuv/getNombreProductoById/' + id, function (r) {
    if (r.error) { toastr.error('No se pudo cargar.'); return; }
    _modal('Editar Nombre de Producto', r.data, function () {
      const nombre = $('#np_nombre').val().trim();
      if (!nombre) { toastr.error('El nombre es obligatorio.'); return; }
      $.post('../api/v1/fulmuv/nombres_productos/update', { id, nombre, categoria: $('#np_categoria').val(), sub_categoria: $('#np_subcategoria').val() }, function (raw) {
        const res = JSON.parse(raw);
        if (!res.error) { bootstrap.Modal.getInstance(document.getElementById('modalNP')).hide(); toastr.success(res.msg); cargarTabla(); }
        else { toastr.error(res.msg); }
      });
    });
  }, 'json');
}
function eliminar(id) {
  swal({ title: 'Eliminar', text: '¿Desea eliminar este nombre de producto?', type: 'warning', showCancelButton: true, confirmButtonColor: '#e63757', confirmButtonText: 'Sí', cancelButtonText: 'No', closeOnConfirm: false },
    function () {
      $.post('../api/v1/fulmuv/nombres_productos/delete', { id }, function (raw) {
        const r = JSON.parse(raw);
        if (!r.error) { swal.close(); toastr.success(r.msg); cargarTabla(); } else { swal('Error', r.msg, 'error'); }
      });
    });
}
