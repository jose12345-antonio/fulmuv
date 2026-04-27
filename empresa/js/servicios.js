let tipo_user = $("#tipo_user").val();
let servicios = [];
let serviciosFiltrados = [];
let categorias = [];
let paginaServicios = 1;

const SERVICIOS_PAGE_SIZE = 8;

function formatMoney(value) {
  const n = Number(value || 0);
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderEmptyServiciosState(mensaje = "No existen servicios registrados.", descripcion = "Cuando registres tu primer servicio, aparecerá aquí para que puedas administrarlo.") {
  $("#lista_servicios").html(`
    <div class="col-12">
      <div class="card border-200 shadow-sm">
        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5" style="min-height:320px;">
          <div class="rounded-circle bg-body-tertiary d-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;">
            <span class="fas fa-tools text-600 fs-4"></span>
          </div>
          <h4 class="mb-2">${mensaje}</h4>
          <p class="text-600 mb-0" style="max-width:520px;">${descripcion}</p>
        </div>
      </div>
    </div>
  `);
  $("#paginacion_servicios").empty();
}

function renderPagination($container, totalItems, currentPage, pageSize, onChange) {
  $container.empty();
  const totalPages = Math.ceil(totalItems / pageSize);
  if (totalPages <= 1) return;

  let html = '<nav><ul class="pagination pagination-sm mb-0">';
  html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">Anterior</a></li>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
  }
  html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a></li>`;
  html += '</ul></nav>';
  $container.html(html);

  $container.find(".page-link").on("click", function (e) {
    e.preventDefault();
    const page = Number($(this).data("page"));
    if (!page || page < 1 || page > totalPages || page === currentPage) return;
    onChange(page);
  });
}

function renderServiciosCards(items, page = 1) {
  const $list = $("#lista_servicios").empty();
  const total = items.length;
  const start = (page - 1) * SERVICIOS_PAGE_SIZE;
  const slice = items.slice(start, start + SERVICIOS_PAGE_SIZE);

  slice.forEach((servicio) => {
    const cat0 = firstFromJson(servicio.categoria);
    const cat0Num = cat0 !== null ? Number(cat0) : 0;
    const tagsHtml = servicio.tags
      ? servicio.tags.split(',').map(tag => `<span class="badge rounded-pill badge-subtle-primary">${tag.trim()}</span>`).join(' ')
      : '<span class="badge rounded-pill badge-subtle-secondary">Sin tags</span>';
    const imagenUrl = servicio.img_frontal
      ? "../admin/" + servicio.img_frontal
      : "files/producto_no_found.jpg";
    const descuento = Number(servicio.descuento || 0);

    $list.append(`
      <div class="col-md-6 col-xl-4 mb-3">
        <div class="catalog-card">
          <div class="catalog-media">
            ${descuento > 0 ? `<span class="catalog-discount">-${descuento}%</span>` : ''}
            <img src="${imagenUrl}" alt="${servicio.titulo_producto || 'Servicio'}">
          </div>
          <div class="catalog-body">
            <div class="catalog-title mb-1">${servicio.titulo_producto || 'Servicio sin título'}</div>
            <div class="catalog-meta mb-1">${servicio.nombre || 'Sin nombre de referencia'}</div>
            <div class="d-flex flex-wrap gap-1 mb-1">${tagsHtml}</div>
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
              <div class="catalog-price">$${formatMoney(servicio.precio_referencia)}</div>
              ${descuento > 0 ? `<div class="catalog-meta text-end">Ahorra ${descuento}%</div>` : `<div class="catalog-meta text-end">Servicio activo</div>`}
            </div>
            <div class="catalog-actions">
              <a class="btn btn-sm btn-falcon-default" href="crear_servicio.php?id_producto=${servicio.id_producto}">
                <span class="fas fa-edit me-1"></span> Editar
              </a>
              <button class="btn btn-sm btn-falcon-default text-danger" onclick="remove(${servicio.id_producto}, 'productos')">
                <span class="fas fa-trash-alt me-1"></span> Eliminar
              </button>
              <button class="btn btn-sm btn-falcon-default text-primary" onclick="cargarAtributosCategoria(${JSON.stringify(cat0Num)}, ${servicio.id_producto})">
                <i class="fi-rs-plus me-1"></i> Información
              </button>
            </div>
          </div>
        </div>
      </div>
    `);
  });

  renderPagination($("#paginacion_servicios"), total, page, SERVICIOS_PAGE_SIZE, (nextPage) => {
    paginaServicios = nextPage;
    renderServiciosCards(items, nextPage);
  });
}

function aplicarFiltroServicios(texto = "") {
  const term = String(texto || "").trim().toLowerCase();
  serviciosFiltrados = !term ? [...servicios] : servicios.filter((item) => {
    const categoria = (item.categorias && item.categorias[0]?.nombre) ? item.categorias[0].nombre : "";
    return [
      item.titulo_producto,
      item.nombre,
      item.tags,
      categoria
    ].some(v => String(v || "").toLowerCase().includes(term));
  });

  paginaServicios = 1;
  if (!serviciosFiltrados.length) {
    renderEmptyServiciosState(
      "No se encontraron servicios.",
      term
        ? `No hay resultados para "${texto}". Prueba con otro criterio de búsqueda.`
        : "Cuando registres tu primer servicio, aparecerá aquí para que puedas administrarlo."
    );
    return;
  }

  renderServiciosCards(serviciosFiltrados, paginaServicios);
}

$(document).ready(function () {
  $.get('../api/v1/fulmuv/categorias/', { tipo: 'servicio' }, function (returnedData) {
    const returned = JSON.parse(returnedData);
    if (returned.error == false) {
      categorias = returned.data || [];
    }
  });

  if ($("#id_rol_principal").val() == 1) {
    $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
      const returned = JSON.parse(returnedData);
      if (returned.error == false) {
        returned.data.forEach(empresa => {
          $("#lista_empresas").append(`<option value="${empresa.id_empresa}">${empresa.nombre}</option>`);
        });
        $("#lista_empresas").trigger('change');
      }
    });
  } else {
    $("#searh_empresa").empty();
    getServicios($("#id_empresa").val());
  }
});

$("#lista_empresas").on('change', function () {
  getServicios($(this).val());
});

function filtrarServiciosLive(texto) {
  aplicarFiltroServicios(texto);
}

function getServicios(id_empresa) {
  $.get('../api/v1/fulmuv/servicios/all/' + id_empresa + '/' + tipo_user, {}, function (returnedData) {
    const returned = JSON.parse(returnedData);
    if (returned.error == false) {
      servicios = returned.data || [];
      $("#buscar_servicio").val("");

      if (!servicios.length) {
        renderEmptyServiciosState();
        return;
      }

      aplicarFiltroServicios("");
    }
  });
}

function firstFromJson(val) {
  if (Array.isArray(val)) return val[0] ?? null;
  if (typeof val === 'string') {
    try {
      const arr = JSON.parse(val || '[]');
      return arr[0] ?? null;
    } catch {
      return null;
    }
  }
  return null;
}

function getServicioById(id_producto) {
  return (servicios || []).find(item => Number(item.id_producto) === Number(id_producto)) || null;
}

function parseDetalleProducto(value) {
  if (!value) return [];
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }
  return [];
}

function remove(id, tabla) {
  swal({
    title: "Alerta",
    text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    $.post('../api/v1/fulmuv/' + tabla + '/delete', {
      id: id
    }, function (returnedData) {
      const returned = JSON.parse(returnedData);
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "servicios.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  });
}

const API_BASE = '../api/v1/fulmuv/atributosCategoriaCompleto/';

async function cargarAtributosCategoria(idCategoria, id_producto) {
  const servicioActual = getServicioById(id_producto);
  const detalleExistente = parseDetalleProducto(servicioActual?.detalle_producto);
  $("#alert").text("");

  $("#alert").append(`  
    <div class="modal fade" id="modalAgregarInfo" tabindex="-1" aria-labelledby="modalAgregarInfoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAgregarInfoLabel">Agregar Información Adicional</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" id="informacionExtra" />

            <div class="small text-muted mb-3">
              Marca el check para incluir cada atributo en el servicio. Los campos se cargan según la categoría.
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Atributos de la categoría</h6>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="chkAllAttrs">
                <label class="form-check-label" for="chkAllAttrs">Marcar / desmarcar todos</label>
              </div>
            </div>

            <div id="contenedorAtributos" class="row"></div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Campos personalizados</h6>
              <button class="btn btn-sm btn-outline-primary" type="button" id="btnAgregarCampo" onclick="getAgregarInfo()">
                <i class="bi bi-plus-circle"></i> Agregar más info
              </button>
            </div>
            <div id="contenedorCamposExtra" class="vstack g-3"></div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarInfo" onclick="guardarInformacion(${id_producto})">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  `);

  $("#modalAgregarInfo").modal("show");

  const cont = document.getElementById('contenedorAtributos');
  const master = document.getElementById('chkAllAttrs');

  function toggleAll(checked) {
    cont.querySelectorAll('input[type="checkbox"]').forEach(chk => {
      chk.checked = checked;
      chk.dispatchEvent(new Event('change', { bubbles: true }));
    });
    syncMaster();
  }

  function syncMaster() {
    const chks = cont.querySelectorAll('input[type="checkbox"]');
    if (!chks.length) {
      master.checked = false;
      master.indeterminate = false;
      master.disabled = true;
      return;
    }
    master.disabled = false;
    const total = chks.length;
    const marcados = Array.from(chks).filter(c => c.checked).length;
    master.checked = marcados === total;
    master.indeterminate = marcados > 0 && marcados < total;
  }

  cont.addEventListener('change', e => {
    if (e.target.matches('input[type="checkbox"]')) syncMaster();
  });
  master.addEventListener('change', e => toggleAll(e.target.checked));

  cont.innerHTML = '<div class="text-muted">Cargando atributos…</div>';
  try {
    const resp = await fetch(`${API_BASE}${idCategoria}`);
    const json = await resp.json();

    if (json.error) {
      cont.innerHTML = '<div class="text-danger">No se pudieron cargar los atributos.</div>';
      return;
    }

    cont.innerHTML = '';
    (json.data || []).forEach((attr, idx) => {
      cont.appendChild(renderAtributo(attr, idx));
    });

    applyDetalleExistente(detalleExistente);

    syncMaster();
  } catch (e) {
    console.error(e);
    cont.innerHTML = '<div class="text-danger">Error de red al cargar atributos.</div>';
  }
}

function renderAtributo(attr, idx) {
  const col = document.createElement('div');
  col.className = 'col-12 col-lg-6 atributo-col mt-2';
  col.dataset.id_atributo = attr.id_atributo;
  col.dataset.nombre = attr.nombre;
  col.dataset.tipo = attr.tipo_dato;

  let controlHTML = '';
  const tipo = (attr.tipo_dato || '').toUpperCase();

  if (tipo === 'OPCIONES') {
    let opciones = Array.isArray(attr.opciones) && attr.opciones.length ? attr.opciones : [];
    if ((!opciones || !opciones.length) && Number(attr.id_atributo) === 1) {
      opciones = ['SÍ', 'NO'];
    }
    controlHTML = opciones.length
      ? `<select class="form-select campo-valor" data-attr-id="${attr.id_atributo}">
           ${opciones.map(o => `<option value="${escapeHtml(o)}">${escapeHtml(o)}</option>`).join('')}
         </select>`
      : `<input type="text" class="form-control campo-valor" placeholder="Escribe una opción" data-attr-id="${attr.id_atributo}">`;
  } else if (tipo === 'BOOLEANO') {
    controlHTML = `
      <select class="form-select campo-valor" data-attr-id="${attr.id_atributo}">
        <option value="SÍ">SÍ</option>
        <option value="NO">NO</option>
      </select>`;
  } else if (tipo === 'NUMERO') {
    controlHTML = `<input type="number" class="form-control campo-valor" step="any" placeholder="Ej: 10" data-attr-id="${attr.id_atributo}">`;
  } else if (tipo === 'TEXTO') {
    const nombreUpper = (attr.nombre || '').toUpperCase();
    const esLargo = /DESCRIPCIÓN|POLÍTICA|POLÍTICAS|IMÁGENES|VÍDEOS|HORARIO|UBICACIÓN/.test(nombreUpper);
    controlHTML = esLargo
      ? `<textarea class="form-control campo-valor" rows="3" placeholder="Escribe aquí…" data-attr-id="${attr.id_atributo}"></textarea>`
      : `<input type="text" class="form-control campo-valor" placeholder="Escribe aquí…" data-attr-id="${attr.id_atributo}">`;
  } else {
    controlHTML = `<input type="text" class="form-control campo-valor" placeholder="Escribe aquí…" data-attr-id="${attr.id_atributo}">`;
  }

  col.innerHTML = `
    <div class="form-check mb-2">
      <input class="form-check-input atributo-check" type="checkbox" id="attr_${attr.id_atributo}" checked>
      <label class="form-check-label fw-semibold" for="attr_${attr.id_atributo}">
        ${attr.nombre}
      </label>
    </div>
    <div class="control-wrapper">
      ${controlHTML}
    </div>
  `;

  const chk = col.querySelector('.atributo-check');
  const toggle = () => {
    const disabled = !chk.checked;
    col.classList.toggle('opacity-50', disabled);
    col.querySelectorAll('.control-wrapper input, .control-wrapper textarea, .control-wrapper select').forEach(el => {
      el.disabled = disabled;
    });
  };
  chk.addEventListener('change', toggle);
  toggle();

  return col;
}

function escapeHtml(str) {
  return String(str)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function applyDetalleExistente(detalles) {
  const detalleArray = Array.isArray(detalles) ? detalles : [];
  if (!detalleArray.length) return;

  const attrsById = new Map();
  detalleArray.forEach(item => {
    const id = Number(item?.id);
    if (id > 0) attrsById.set(id, item);
  });

  document.querySelectorAll('#contenedorAtributos .atributo-col').forEach(col => {
    const id = Number(col.dataset.id_atributo);
    const item = attrsById.get(id);
    if (!item) return;

    const chk = col.querySelector('.atributo-check');
    const ctrl = col.querySelector('.campo-valor');
    if (!ctrl) return;

    if (chk) {
      chk.checked = true;
      chk.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const valor = item?.valor ?? '';
    ctrl.classList.remove('is-invalid');
    ctrl.value = valor;
    $(ctrl).trigger('change');
  });

  detalleArray
    .filter(item => Number(item?.id || 0) <= 0)
    .forEach(item => {
      getAgregarInfo(item);
    });
}

let contadorExtra = 0;

document.addEventListener('change', (e) => {
  if (!e.target.classList.contains('atributo-check')) return;
  const col = e.target.closest('.atributo-col');
  if (!col) return;
  const disabled = !e.target.checked;
  col.classList.toggle('opacity-50', disabled);
  col.querySelectorAll('.control-wrapper input, .control-wrapper textarea, .control-wrapper select')
    .forEach(inp => (inp.disabled = disabled));
});

function guardarInformacion(id_producto) {
  const datos = [];
  const $btnGuardar = $("#btnGuardarInfo");
  const btnHtmlOriginal = $btnGuardar.html();

  const setGuardarLoading = (loading) => {
    if (!$btnGuardar.length) return;
    $btnGuardar.prop('disabled', loading);
    $btnGuardar.html(
      loading
        ? '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...'
        : btnHtmlOriginal
    );
  };

  const invalid = (el, msg) => {
    try { el.classList.add('is-invalid'); } catch (_) { }
    el?.focus({ preventScroll: false });
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    swal({
      type: 'warning',
      title: 'Completa el atributo seleccionado.',
      html: msg,
      confirmButtonText: 'Ok'
    });
    setGuardarLoading(false);
  };

  setGuardarLoading(true);

  const attrs = document.querySelectorAll('#contenedorAtributos .atributo-col');
  for (const col of attrs) {
    const chk = col.querySelector('.atributo-check');
    if (!chk || !chk.checked) continue;

    const id_atributo = Number(col.dataset.id_atributo);
    const nombre = col.dataset.nombre || '';
    const tipo = (col.dataset.tipo || '').toUpperCase();
    const ctrl = col.querySelector('.campo-valor');
    if (!ctrl) continue;

    ctrl.classList.remove('is-invalid');

    let valor = '';
    if (ctrl.tagName === 'SELECT') {
      valor = ctrl.value;
      if (valor === '' || valor == null) {
        invalid(ctrl, `Debes seleccionar un valor para <b>${nombre}</b>.`);
        return;
      }
    } else if (ctrl.tagName === 'TEXTAREA') {
      valor = (ctrl.value || '').trim();
      if (valor === '') {
        invalid(ctrl, `Debes escribir un valor para <b>${nombre}</b>.`);
        return;
      }
    } else if (ctrl.type === 'number') {
      valor = ctrl.value;
      if (valor === '') {
        invalid(ctrl, `Debes ingresar un número para <b>${nombre}</b>.`);
        return;
      }
    } else {
      valor = (ctrl.value || '').trim();
      if (valor === '') {
        invalid(ctrl, `Debes ingresar un valor para <b>${nombre}</b>.`);
        return;
      }
    }

    if (tipo === 'BOOLEANO' && (valor === '' || valor == null)) valor = 'NO';
    datos.push({ id: id_atributo, label: nombre, valor });
  }

  const extras = document.querySelectorAll('#contenedorCamposExtra > .border');
  for (const wrapper of extras) {
    const chk = wrapper.querySelector('.chk-extra');
    if (!chk || !chk.checked) continue;

    const etqInput = wrapper.querySelector('.etq-extra');
    const tipoSel = wrapper.querySelector('.tipo-extra');
    const tipo = tipoSel?.value || 'text';
    const etiqueta = (etqInput?.value || '').trim();
    if (!etiqueta) {
      invalid(etqInput, 'Escribe la <b>etiqueta</b> del campo personalizado.');
      return;
    }

    let valor = '';
    if (tipo === 'textarea') {
      const valEl = wrapper.querySelector('.val-extra');
      valor = (valEl?.value || '').trim();
      if (valor === '') { invalid(valEl, `Debes escribir un valor para <b>${etiqueta}</b>.`); return; }
    } else if (tipo === 'color') {
      const hexEl = wrapper.querySelector('.val-extra-color');
      const nomEl = wrapper.querySelector('.val-extra');
      const hex = hexEl?.value || '';
      const nom = (nomEl?.value || '').trim();
      valor = nom ? `${nom} (${hex})` : hex;
      if (valor === '') { invalid(hexEl || nomEl, `Debes elegir un color para <b>${etiqueta}</b>.`); return; }
    } else if (tipo === 'number') {
      const valEl = wrapper.querySelector('.val-extra');
      valor = valEl?.value ?? '';
      if (valor === '') { invalid(valEl, `Debes ingresar un número para <b>${etiqueta}</b>.`); return; }
    } else {
      const valEl = wrapper.querySelector('.val-extra');
      valor = (valEl?.value || '').trim();
      if (valor === '') { invalid(valEl, `Debes ingresar un valor para <b>${etiqueta}</b>.`); return; }
    }

    datos.push({ id: 0, label: etiqueta, valor });
  }

  $.post("../api/v1/fulmuv/productoAtributo/update", { id_producto: id_producto, detalle_producto: datos }, function (returnedData) {
    if (!returnedData.error) {
      SweetAlert("url_success", returnedData.msg, "servicios.php");
    } else {
      SweetAlert("error", returnedData.msg);
      setGuardarLoading(false);
    }
  }, 'json').fail(function () {
    SweetAlert("error", "Ocurrió un error al guardar la información.");
    setGuardarLoading(false);
  });
}

async function getAgregarInfo(initialData = null) {
  contadorExtra++;
  const id = `extra_${contadorExtra}`;
  const wrapper = document.createElement('div');
  wrapper.className = 'border rounded p-2';

  wrapper.innerHTML = `
    <div class="row g-2 align-items-center">
      <div class="col-12 col-md-2">
        <div class="form-check">
          <input class="form-check-input chk-extra" type="checkbox" id="${id}_chk" checked>
          <label class="form-check-label fw-semibold" for="${id}_chk">Incluir</label>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <input type="text" class="form-control etq-extra" id="${id}_label" placeholder="Etiqueta (Ej: Garantía)">
      </div>
      <div class="col-12 col-md-3">
        <select class="form-select tipo-extra" id="${id}_tipo">
          <option value="text">Texto</option>
          <option value="number">Número</option>
          <option value="textarea">Área de texto</option>
          <option value="color">Color</option>
          <option value="date">Fecha</option>
        </select>
      </div>
      <div class="col valor-col"></div>
      <div class="col-12 col-md-1 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" title="Eliminar">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  `;

  const valorCol = wrapper.querySelector('.valor-col');
  const tipoSel = wrapper.querySelector('.tipo-extra');
  const chk = wrapper.querySelector('.chk-extra');

  const renderValor = () => {
    const tipo = tipoSel.value;
    let html = '';
    if (tipo === 'textarea') {
      html = `<textarea class="form-control val-extra" rows="2" placeholder="Escribe el valor"></textarea>`;
    } else if (tipo === 'color') {
      html = `
        <div class="d-flex align-items-center gap-2">
          <input type="color" class="form-control form-control-color val-extra-color" value="#000000" title="Elige un color">
          <input type="text" class="form-control val-extra" placeholder="Nombre/HEX del color (opcional)">
        </div>`;
    } else {
      html = `<input type="${tipo}" class="form-control val-extra" placeholder="Escribe el valor">`;
    }
    valorCol.className = 'col';
    valorCol.innerHTML = html;
    toggleHabilitado();
  };

  const toggleHabilitado = () => {
    const disabled = !chk.checked;
    wrapper.classList.toggle('opacity-50', disabled);
    wrapper.querySelectorAll('input, textarea, select').forEach(el => {
      if (el !== chk) el.disabled = disabled;
    });
  };

  tipoSel.addEventListener('change', renderValor);
  wrapper.addEventListener('click', (ev) => {
    if (ev.target.closest('.btn-eliminar')) {
      wrapper.remove();
    }
  });
  chk.addEventListener('change', toggleHabilitado);

  renderValor();

  if (initialData) {
    const etiqueta = String(initialData.label || '').trim();
    const valorInicial = String(initialData.valor || '').trim();
    const tipoInferido = /\(#[0-9a-fA-F]{6}\)$/.test(valorInicial) || /^#[0-9a-fA-F]{6}$/.test(valorInicial)
      ? 'color'
      : (valorInicial.length > 120 ? 'textarea' : 'text');

    wrapper.querySelector('.etq-extra').value = etiqueta;
    tipoSel.value = tipoInferido;
    renderValor();

    if (tipoInferido === 'color') {
      const colorMatch = valorInicial.match(/(#[0-9a-fA-F]{6})/);
      const hex = colorMatch ? colorMatch[1] : '#000000';
      const nombre = valorInicial.replace(/\s*\(#[0-9a-fA-F]{6}\)\s*$/, '').trim();
      const colorInput = wrapper.querySelector('.val-extra-color');
      const textInput = wrapper.querySelector('.val-extra');
      if (colorInput) colorInput.value = hex;
      if (textInput) textInput.value = nombre || hex;
    } else {
      const input = wrapper.querySelector('.val-extra');
      if (input) input.value = valorInicial;
    }
  }

  const cont = document.getElementById('contenedorCamposExtra');
  if (cont) cont.appendChild(wrapper);
}
