const DT_ES = {
  emptyTable: "No hay datos disponibles", info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
  infoEmpty: "Mostrando 0 a 0 de 0 registros", infoFiltered: "(filtrado de _MAX_ totales)",
  thousands: ".", lengthMenu: "Mostrar _MENU_ registros", loadingRecords: "Cargando...",
  processing: "Procesando...", search: "Buscar:", zeroRecords: "No se encontraron resultados",
  paginate: { first: "Primero", last: "Último", next: '<span class="fas fa-chevron-right"></span>', previous: '<span class="fas fa-chevron-left"></span>' }
};

let _catPrincipales = [];

$(document).ready(function () {
  $.get('../api/v1/fulmuv/categoriasPrincipales/All', {}, function (raw) {
    const r = JSON.parse(raw);
    if (!r.error) { _catPrincipales = r.data; }
  });
  cargarTabla();
});

function cargarTabla() {
  if ($.fn.DataTable.isDataTable('#my_table')) { $('#my_table').DataTable().destroy(); }
  $('#lista_categorias').empty();
  $.get('../api/v1/fulmuv/categorias/All', {}, function (raw) {
    const r = JSON.parse(raw);
    if (!r.error) {
      r.data.forEach(function (c) {
        const tipoBadge = c.tipo === 'producto' ? 'badge bg-success' : c.tipo === 'servicio' ? 'badge bg-info' : 'badge bg-secondary';
        $('#lista_categorias').append(`
          <tr>
            <td class="align-middle fw-semibold ps-3">${c.nombre}</td>
            <td class="align-middle"><span class="${tipoBadge}">${c.tipo || '-'}</span></td>
            <td class="align-middle text-end pe-3" style="width:140px">
              <button class="btn btn-sm btn-outline-secondary me-1" title="Atributos" onclick="asignar_atributo(${c.id_categoria})"><i class="fas fa-tags"></i></button>
              <button class="btn btn-sm btn-outline-primary me-1" title="Editar" onclick="abrirEditar(${c.id_categoria})"><i class="fas fa-pen"></i></button>
              <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminar(${c.id_categoria})"><i class="fas fa-trash"></i></button>
            </td>
          </tr>`);
      });
    }
    $('#my_table').DataTable({ searching: true, responsive: false, pageLength: 25, info: true, lengthChange: false, language: DT_ES, dom: "<'row mx-0'<'col-md-6'l><'col-md-6'f>><'table-responsive scrollbar'tr><'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>" });
  });
}

function _modalCategoria(titulo, datos, onGuardar) {
  $('#modalCat').remove();
  $('body').append(`
    <div class="modal fade" id="modalCat" data-bs-backdrop="static" tabindex="-1">
      <div class="modal-dialog modal-lg mt-6">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-body-tertiary border-0">
            <h5 class="modal-title fw-bold"><i class="fas fa-sitemap me-2 text-primary"></i>${titulo}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                <input class="form-control" id="cat_nombre" type="text" placeholder="Nombre de la categoría" oninput="this.value=this.value.toUpperCase()">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="cat_tipo">
                  <option value="">Seleccionar tipo</option>
                  <option value="producto">Producto</option>
                  <option value="servicio">Servicio</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Categorías Principales</label>
                <select class="form-select" id="cat_principal" multiple></select>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button>
            <button class="btn btn-primary btn-sm" id="btnGuardarCat"><i class="fas fa-save me-1"></i>Guardar</button>
          </div>
        </div>
      </div>
    </div>`);

  _catPrincipales.forEach(function (cp) {
    $('#cat_principal').append(`<option value="${cp.id_categoria_principal}">${cp.nombre}</option>`);
  });
  $('#cat_principal').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalCat'), placeholder: 'Seleccione categorías principales' });

  if (datos) {
    $('#cat_nombre').val(datos.nombre);
    $('#cat_tipo').val(datos.tipo);
    try {
      const sel = JSON.parse(datos.categoria_principal);
      if (Array.isArray(sel)) { $('#cat_principal').val(sel.map(String)).trigger('change'); }
    } catch (e) {}
  }
  $('#btnGuardarCat').off('click').on('click', onGuardar);
  new bootstrap.Modal(document.getElementById('modalCat')).show();
}

function abrirCrear() {
  _modalCategoria('Nueva Categoría', null, function () {
    const nombre = $('#cat_nombre').val().trim();
    const tipo = $('#cat_tipo').val();
    if (!nombre || !tipo) { toastr.error('Nombre y tipo son obligatorios.'); return; }
    const catPrincipal = ($('#cat_principal').val() || []).map(Number);
    $.post('../api/v1/fulmuv/categoria/create', { nombre: nombre, tipo: tipo, imagen: '', categoria_principal: catPrincipal }, function (raw) {
      const r = JSON.parse(raw);
      if (!r.error) { bootstrap.Modal.getInstance(document.getElementById('modalCat')).hide(); toastr.success(r.msg); cargarTabla(); }
      else { toastr.error(r.msg); }
    });
  });
}

function abrirEditar(id) {
  $.get('../api/v1/fulmuv/categorias/' + id, function (r) {
    if (r.error) { toastr.error('No se pudo cargar.'); return; }
    _modalCategoria('Editar Categoría', r.data, function () {
      const nombre = $('#cat_nombre').val().trim();
      const tipo = $('#cat_tipo').val();
      if (!nombre || !tipo) { toastr.error('Nombre y tipo son obligatorios.'); return; }
      const catPrincipal = ($('#cat_principal').val() || []).map(Number);
      $.post('../api/v1/fulmuv/categoria/update', { id_categoria: id, nombre: nombre, tipo: tipo, imagen: r.data.imagen, categoria_principal: catPrincipal }, function (raw) {
        const res = JSON.parse(raw);
        if (!res.error) { bootstrap.Modal.getInstance(document.getElementById('modalCat')).hide(); toastr.success(res.msg); cargarTabla(); }
        else { toastr.error(res.msg); }
      });
    });
  }, 'json');
}

function eliminar(id) {
  swal({ title: 'Eliminar', text: '¿Está seguro que desea eliminar esta categoría?', type: 'warning', showCancelButton: true, confirmButtonColor: '#e63757', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', closeOnConfirm: false },
    function () {
      $.post('../api/v1/fulmuv/categorias/delete', { id: id }, function (raw) {
        const r = JSON.parse(raw);
        if (!r.error) { swal.close(); toastr.success(r.msg); cargarTabla(); }
        else { swal('Error', r.msg, 'error'); }
      });
    });
}

function asignar_atributo(id_categoria) {
  let _choices = null;
  $('#modalAtributo').remove();
  $('body').append(`
    <div class="modal fade" id="modalAtributo" data-bs-backdrop="static" tabindex="-1">
      <div class="modal-dialog modal-lg mt-6">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-body-tertiary border-0">
            <h5 class="modal-title fw-bold"><i class="fas fa-tags me-2 text-primary"></i>Asignar Atributos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label class="form-label fw-semibold">Atributos</label>
            <select class="form-select" id="selAtributos" multiple></select>
          </div>
          <div class="modal-footer border-0">
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancelar</button>
            <button class="btn btn-primary btn-sm" id="btnGuardarAtributos"><i class="fas fa-save me-1"></i>Guardar</button>
          </div>
        </div>
      </div>
    </div>`);

  $.get('../api/v1/fulmuv/atributos/', {}, function (rawA) {
    const rA = JSON.parse(rawA);
    if (!rA.error) {
      rA.data.forEach(function (a) {
        $('#selAtributos').append(`<option value="${a.id_atributo}">${a.nombre}</option>`);
      });
      _choices = new Choices(document.getElementById('selAtributos'), { removeItemButton: true, placeholder: true, placeholderValue: 'Seleccione atributos', allowHTML: true, position: 'bottom' });
      $.get('../api/v1/fulmuv/atributosCategoria/' + id_categoria, {}, function (rawC) {
        const rC = JSON.parse(rawC);
        if (!rC.error && rC.data && rC.data.atributos) {
          try {
            const sel = JSON.parse(rC.data.atributos);
            if (Array.isArray(sel)) { sel.forEach(function (v) { _choices.setChoiceByValue(v.toString()); }); }
          } catch (e) {}
        }
      });
    }
  });

  $('#btnGuardarAtributos').off('click').on('click', function () {
    const vals = _choices ? _choices.getValue(true) : [];
    $.post('../api/v1/fulmuv/updateCategoria', { id_categoria: id_categoria, atributos: vals }, function (raw) {
      const r = JSON.parse(raw);
      if (!r.error) { bootstrap.Modal.getInstance(document.getElementById('modalAtributo')).hide(); toastr.success(r.msg); }
      else { toastr.error(r.msg); }
    });
  });
  new bootstrap.Modal(document.getElementById('modalAtributo')).show();
}
