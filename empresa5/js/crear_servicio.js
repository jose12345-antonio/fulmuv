let categorias = [];
var tagsInput = '';
let modelos = [];
let tipos_auto = [];
let marcas = [];
let traccion = [];
let motor = [];
let tipo_user = $("#tipo_user").val();
var id_producto = $("#id_producto").val();
let isEditLoading = false;
let servicioEditPayload = null;
let servicioEditResponse = null;

var myDropzone; // Dropzone global
let imagenFrontalEdit = 0;   // 0 = no cambiÃ³, 1 = se subiÃ³ nueva
let imagenPosteriorEdit = 0; // 0 = no cambiÃ³, 1 = se subiÃ³ nueva

/* ===== Helpers de normalizaciÃ³n ===== */
function parseMaybeJSON(v) {
  if (typeof v !== 'string') return v;
  try { return JSON.parse(v); } catch (_) { return v; }
}

function normSingle(v, idKey = 'id', textKey = 'nombre') {
  if (v == null) return null;

  v = parseMaybeJSON(v);

  if (Array.isArray(v)) {
    if (!v.length) return null;
    v = v[0];
  }

  if (typeof v === 'object' && v !== null) {
    const id = (v[idKey] ?? v.id ?? v.value ?? '').toString();
    const text = (v[textKey] ?? v.nombre ?? v.text ?? v.label ?? id).toString();
    if (!id) return null;
    return { id, text };
  }

  const id = String(v);
  return { id, text: id };
}

$("#referencia").on("change", function () {
  if (isEditLoading) return;
  $("#modelo").val(null).trigger("change");
  buscarModelosReferencia();
});

function normMulti(v, idKey = 'id', textKey = 'nombre') {
  if (v == null) return [];
  v = parseMaybeJSON(v);

  const toPair = (x) => {
    if (x == null) return null;

    if (Array.isArray(x)) {
      if (!x.length) return null;
      x = x[0];
    }

    if (typeof x === 'object' && x !== null) {
      const id = (x[idKey] ?? x.id ?? x.value ?? '').toString();
      const text = (x[textKey] ?? x.nombre ?? x.text ?? x.label ?? id).toString();
      if (!id) return null;
      return { id, text };
    }

    const id = String(x);
    return { id, text: id };
  };

  if (Array.isArray(v)) return v.map(toPair).filter(Boolean);

  if (typeof v === 'string') {
    const s = v.trim();
    if (s.includes(',')) {
      return s
        .split(',')
        .map(t => t.trim())
        .filter(Boolean)
        .map(x => ({ id: String(x), text: String(x) }));
    }
    if ((s.startsWith('[') && s.endsWith(']')) || (s.startsWith('{') && s.endsWith('}'))) {
      try {
        const j = JSON.parse(s);
        return normMulti(j, idKey, textKey);
      } catch (_) { }
    }
  }

  const single = toPair(v);
  return single ? [single] : [];
}

/* ===== Utilities para selects y archivos ===== */
function ensureOption($sel, value, text) {
  if (value == null || value === '') return;
  const v = String(value);
  if ($sel.find('option[value="' + v + '"]').length === 0) {
    $sel.append(new Option(text ?? v, v, false, false));
  }
}

function setSelectValue($sel, value, text) {
  if (value == null || value === '') {
    $sel.val(null).trigger('change');
    return;
  }
  ensureOption($sel, value, text);
  $sel.val(String(value)).trigger('change');
}

function setSelectMultiplePairs($sel, pairs) {
  const ids = [];
  pairs.forEach(p => {
    if (!p) return;
    ensureOption($sel, p.id, p.text);
    ids.push(p.id);
  });
  $sel.val(ids).trigger('change');
}

function setCheckbox($el, v) {
  $el.prop('checked', String(v) === '1' || v === true);
}

function safeUpperArrayCSV(str) {
  if (!str) return [];
  return String(str)
    .split(',')
    .map(s => s.trim())
    .filter(Boolean)
    .map(s => s.toUpperCase());
}

function normalizeOptionLabel(value) {
  return String(value || '')
    .trim()
    .replace(/[()]/g, ' ')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-zA-Z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .toLowerCase();
}

function normalizeIdsArrayJs(ids) {
  if (!ids) return [];
  let source = ids;

  if (typeof source === 'string') {
    try {
      const parsed = JSON.parse(source);
      source = parsed;
    } catch (_) {
      source = String(source).split(',');
    }
  }

  if (!Array.isArray(source)) return [];

  return source
    .map(v => parseInt(v, 10))
    .filter(v => Number.isInteger(v) && v > 0);
}

function dedupeSelectOptionsByText($select) {
  const seen = new Set();
  let selectedToKeep = null;

  $select.find('option').each(function () {
    const $option = $(this);
    const value = String($option.val() || '').trim();
    if (!value) return;

    const normalizedText = normalizeOptionLabel($option.text());
    if (!normalizedText) return;

    if (seen.has(normalizedText)) {
      if ($option.is(':selected') && !selectedToKeep) {
        selectedToKeep = value;
      }
      $option.remove();
      return;
    }

    seen.add(normalizedText);
    if ($option.is(':selected') && !selectedToKeep) {
      selectedToKeep = value;
    }
  });

  if (selectedToKeep) {
    $select.val(selectedToKeep);
  }
}

function findExistingOptionByNormalizedText($select, text, excludeValue = null) {
  const normalizedTarget = normalizeOptionLabel(text);
  if (!normalizedTarget) return null;

  let found = null;
  $select.find('option').each(function () {
    const $option = $(this);
    const optionValue = String($option.val() || '').trim();
    if (!optionValue) return;
    if (excludeValue != null && String(excludeValue) === optionValue) return;

    const normalizedOption = normalizeOptionLabel($option.text());
    if (normalizedOption === normalizedTarget) {
      found = {
        value: optionValue,
        text: $option.text()
      };
      return false;
    }
  });

  return found;
}

function selectNombreServicioPorTexto(nombreServicio, fallbackValue = null) {
  const $select = $("#nombre");
  const texto = String(nombreServicio || '').trim();
  if (!texto || !$select.length) return;

  const existente = findExistingOptionByNormalizedText($select, texto, null);
  if (existente && existente.value) {
    $select.val(existente.value).trigger('change');
    return;
  }

  const value = fallbackValue != null && String(fallbackValue).trim() !== ''
    ? String(fallbackValue).trim()
    : texto;

  ensureOption($select, value, texto);
  $select.val(value).trigger('change');
}

function resolveNombreServicioTexto(p) {
  if (typeof p?.nombre === 'string' && p.nombre.trim()) return p.nombre.trim();
  if (typeof p?.nombre_servicio === 'string' && p.nombre_servicio.trim()) return p.nombre_servicio.trim();
  if (typeof p?.nombre_producto === 'string' && p.nombre_producto.trim()) return p.nombre_producto.trim();
  return '';
}

function esPlanBasicMuv() {
  const nombre = String($("#membresia_nombre").val() || '')
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]/g, '');
  return nombre === 'basicmuv';
}

function validarLimiteBasicMuvPorCategoria(idEmpresa, categoriaIds, idExcluir = 0) {
  return new Promise(function (resolve) {
    if (!esPlanBasicMuv()) {
      resolve({ error: false });
      return;
    }

    const categoriasSeleccionadas = normalizeIdsArrayJs(categoriaIds);
    if (!categoriasSeleccionadas.length) {
      resolve({ error: false });
      return;
    }

    $.get('../api/v1/fulmuv/productos/all/' + idEmpresa + '/servicio', {}, function (returnedData) {
      let returned = returnedData;
      if (typeof returnedData === 'string') {
        try { returned = JSON.parse(returnedData); } catch (_) { returned = { data: [] }; }
      }

      const servicios = Array.isArray(returned?.data) ? returned.data : [];
      const conteo = {};
      categoriasSeleccionadas.forEach(id => { conteo[id] = 0; });

      servicios.forEach(function (servicio) {
        const idServicio = parseInt(servicio.id_producto || 0, 10);
        if (idExcluir > 0 && idServicio === parseInt(idExcluir, 10)) return;

        const categoriasServicio = normalizeIdsArrayJs(servicio.categoria);
        categoriasSeleccionadas.forEach(function (idCat) {
          if (categoriasServicio.includes(idCat)) {
            conteo[idCat] = (conteo[idCat] || 0) + 1;
          }
        });
      });

      const categoriasTexto = (categorias || []).filter(cat =>
        categoriasSeleccionadas.includes(parseInt(cat.id_categoria || 0, 10))
      );

      const categoriaBloqueada = categoriasTexto.find(cat =>
        (conteo[parseInt(cat.id_categoria || 0, 10)] || 0) >= 1
      );

      if (categoriaBloqueada) {
        resolve({
          error: true,
          msg: 'Tu plan BasicMuv solo permite un servicio por categoría. Ya tienes un servicio registrado en ' + categoriaBloqueada.nombre + '.'
        });
        return;
      }

      resolve({ error: false });
    }).fail(function () {
      resolve({
        error: true,
        msg: 'No se pudo validar el límite por categoría de tu plan BasicMuv.'
      });
    });
  });
}

function applyNombreServicioEdit(p) {
  if (!p) return;

  const nombreLocal = resolveNombreServicioTexto(p);
  if (nombreLocal) {
    selectNombreServicioPorTexto(nombreLocal, p.id_nombre_servicio || nombreLocal);
  }

  if (!p.id_nombre_servicio) return;

  $.get('../api/v1/fulmuv/getNombreServicioById/' + encodeURIComponent(p.id_nombre_servicio), {}, function (returnedData) {
    let returned = returnedData;
    if (typeof returnedData === 'string') {
      try { returned = JSON.parse(returnedData); } catch (_) { returned = null; }
    }

    const nombreApi = String(returned?.data?.nombre || returned?.data?.nombre_servicio || returned?.nombre || '').trim();
    if (!nombreApi) return;

    const existente = findExistingOptionByNormalizedText($("#nombre"), nombreApi, null);
    if (existente && existente.value) {
      $("#nombre").val(existente.value).trigger('change');
      return;
    }

    selectNombreServicioPorTexto(nombreApi, p.id_nombre_servicio);
    $("#nombre").trigger('change.select2');
  });
}

// âœ… Formateador de rutas para previsualizaciÃ³n
function fileUrlAdmin(path) {
  if (!path) return '';
  const p = String(path).replace(/\\/g, '/').trim();
  if (/^https?:\/\//i.test(p)) return p;
  if (p.startsWith('../')) return p;
  if (p.startsWith('admin/')) return '../' + p;
  if (p.startsWith('/admin/')) return '..' + p;
  return '../admin/' + p.replace(/^\/+/, '');
}

function setTinyMCE(id, html) {
  const $ta = $("#" + id);
  $ta.val(html || "");

  const ed = window.tinymce?.get(id);
  if (ed) {
    ed.setContent(html || "");
    ed.save();
  } else {
    const timer = setInterval(() => {
      const ed2 = window.tinymce?.get(id);
      if (ed2) {
        clearInterval(timer);
        ed2.setContent(html || "");
        ed2.save();
      }
    }, 100);
    setTimeout(() => clearInterval(timer), 5000);
  }
}

function waitTinyMCE(id, cb, tries = 25) {
  const ed = window.tinymce?.get(id);
  if (ed) return cb(ed);
  if (tries <= 0) return cb(null);
  setTimeout(() => waitTinyMCE(id, cb, tries - 1), 150);
}

function setTinyMCEWhenReady(id, html) {
  waitTinyMCE(id, (ed) => {
    if (ed) {
      ed.setContent(html || '');
      ed.save();
    } else {
      $("#" + id).val(html || '');
    }
  });
}

function applyServicioEditSelections(p, resp) {
  if (!p) return;

  servicioEditPayload = p;
  servicioEditResponse = resp;

  applyNombreServicioEdit(p);
  dedupeSelectOptionsByText($("#nombre"));

  let catPairs = normMulti(p.categoria, 'id_categoria', 'nombre');
  if (!catPairs.length) catPairs = normMulti(p.id_categoria);
  setSelectMultiplePairs($("#categoria"), catPairs);

  const refs = p.referencias ?? p.referencia;
  setSelect2ValueByText($("#referencia"), refs);
  const modelosPairs = normMulti(p.modelo, 'id_modelos_autos', 'nombre');
  buscarModelosReferencia($("#referencia").val(), modelosPairs);

  let tvPairs = normMulti(p.tipo_vehiculo, 'id_tipo_auto', 'nombre');
  if (!tvPairs.length) tvPairs = normMulti(p.tipo_auto, 'id', 'nombre');
  setSelectMultiplePairs($("#tipo_vehiculo"), tvPairs);

  const marPairs = normMulti(p.marca, 'id', 'nombre');
  setSelectMultiplePairs($("#marca"), marPairs);

  let trPairs = normMulti(p.traccion, 'id_tipo_traccion', 'nombre');
  if (!trPairs.length) trPairs = normMulti(p.tipo_traccion, 'id', 'nombre');
  setSelectMultiplePairs($("#traccion"), trPairs);

  let motPairs = normMulti(p.motor, 'id_funcionamiento_motor', 'nombre');
  if (!motPairs.length) motPairs = normMulti(p.funcionamiento_motor, 'id', 'nombre');
  setSelectMultiplePairs($("#motor"), motPairs);

  const archivosGaleria = (resp.data && resp.data.archivos) ? resp.data.archivos : (resp.archivos || []);
  if (myDropzone && Array.isArray(archivosGaleria)) {
    myDropzone.removeAllFiles(true);

    archivosGaleria.forEach(function (arch) {
      const ruta = fileUrlAdmin(arch.archivo);
      const esImagen = arch.tipo === 'imagen' || /\.(png|jpe?g|webp|gif)$/i.test(ruta);

      const mockFile = {
        name: ruta.split('/').pop(),
        size: 123456,
        type: esImagen ? 'image/*' : 'application/pdf',
        accepted: true
      };

      myDropzone.emit("addedfile", mockFile);
      if (esImagen) myDropzone.emit("thumbnail", mockFile, ruta);
      myDropzone.emit("complete", mockFile);
      mockFile.status = Dropzone.SUCCESS;
      mockFile.existing = true;

      const $preview = $(mockFile.previewElement);
      $preview.find('.dz-remove-galeria').off('click').on('click', function (e) {
        e.preventDefault();
        eliminarArchivoGaleria(arch.id_archivo_producto, mockFile);
      });
    });
  }
}

/* ====== DOCUMENT READY ====== */
$(document).ready(function () {
  /* ==== Tags (Choices.js) ==== */
  tagsInput = new Choices('#tags', {
    removeItemButton: true,
    placeholder: false,
    maxItemCount: 10,
    addItemText: (value) => `Presiona Enter para aÃ±adir <b>"${value}"</b>`,
    maxItemText: (maxItemCount) => `Solo ${maxItemCount} tags pueden ser aÃ±adidos`,
  });

  /* ==== NOMBRE SERVICIOS ==== */
  $.get('../api/v1/fulmuv/nombres_servicios/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      const seenServiceNames = new Set();
      (returned.data || []).forEach(n => {
        const normalizedName = normalizeOptionLabel(n.nombre);
        if (!normalizedName || seenServiceNames.has(normalizedName)) return;
        seenServiceNames.add(normalizedName);
        $("#nombre").append(`<option value="${n.id_nombre_servicio}">${n.nombre}</option>`);
      });
      dedupeSelectOptionsByText($("#nombre"));
      $("#nombre").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione servicio',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          const existe = $("#nombre option").toArray().some(opt =>
            normalizeOptionLabel($(opt).text()) === normalizeOptionLabel(term)
          );
          if (existe) return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#nombre'), { entity: 'nombres_productos', label: 'Nombre de servicio' });

      if (servicioEditPayload) {
        setTimeout(() => applyServicioEditSelections(servicioEditPayload, servicioEditResponse || {}), 150);
      }
    }
  });

  /* ==== CATEGORÃAS ==== */
  $.get('../api/v1/fulmuv/categorias/', {
    tipo: 'servicio',
    id_empresa: $("#id_empresa").val(),
    tipo_usuario: tipo_user
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      categorias = returned.data;
      returned.data.forEach(categoria => {
        $("#categoria").append(`<option value="${categoria.id_categoria}">${categoria.nombre}</option>`);
      });
      $("#categoria").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione categoria',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
    }
  });

  /* ==== REFERENCIAS ==== */
  $.get('../api/v1/fulmuv/getReferencias/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      returned.data.forEach(referencia => {
        $("#referencia").append(`<option value="${referencia}">${referencia}</option>`);
      });
      $("#referencia").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione la referencia',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
    }
  });

  /* ==== TIPO DE VEHÃCULO ==== */
  $.get('../api/v1/fulmuv/tiposAuto/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      tipos_auto = returned.data;
      returned.data.forEach(tipo_vehi => {
        $("#tipo_vehiculo").append(`<option value="${tipo_vehi.id_tipo_auto}">${tipo_vehi.nombre}</option>`);
      });
      $("#tipo_vehiculo").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione tipo de vehÃ­culo',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#tipo_vehiculo'), { entity: 'tipos_auto', label: 'Tipo de vehÃ­culo' });
    }
  });

  /* ==== MARCAS ==== */
  $.get('../api/v1/fulmuv/marcas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      marcas = returned.data;
      returned.data.forEach(marc => {
        $("#marca").append(`<option value="${marc.id_marca}">${marc.nombre}</option>`);
      });
      $("#marca").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione marca',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#marca'), { entity: 'marcas', label: 'Marca' });
    }
  });

  /* ==== TRACCIÃ“N ==== */
  $.get('../api/v1/fulmuv/tipo_tracccion/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      traccion = returned.data;
      returned.data.forEach(tracc => {
        $("#traccion").append(`<option value="${tracc.id_tipo_traccion}">${tracc.nombre}</option>`);
      });
      $("#traccion").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione tracciÃ³n',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#traccion'), { entity: 'tipo_traccion', label: 'TracciÃ³n' });
    }
  });

  /* ==== FUNCIONAMIENTO MOTOR ==== */
  $.get('../api/v1/fulmuv/getFuncionamientoMotor/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      motor = returned.data;
      returned.data.forEach(m => {
        $("#motor").append(`<option value="${m.id_funcionamiento_motor}">${m.nombre}</option>`);
      });
      $("#motor").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione funcionamiento de motor',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#motor'), { entity: 'funcionamiento_motor', label: 'Funcionamiento de motor' });
    }
  });

  /* ==== DROPZONE (galerÃ­a) ==== */
  $("#myAwesomeDropzone").attr("data-dropzone", 'data-dropzone');

  myDropzone = new Dropzone("#myAwesomeDropzone", {
    url: "#",
    acceptedFiles: "image/*,application/pdf",
    previewsContainer: document.querySelector(".dz-preview"),
    previewTemplate: document.querySelector(".dz-preview").innerHTML,
    init: function () {
      $("#file-previews").empty();
    }
  });

  /* ==== Empresas para admin ==== */
  if ($("#id_rol_principal").val() == 1) {
    $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(empresa => {
          $("#lista_empresas").append(`<option value="${empresa.id_empresa}">${empresa.nombre}</option>`);
        });
      }
    });
  } else {
    $("#searh_empresa").empty()
  }

  /* ======= Preview de imÃ¡genes locales ======= */
  function previewFile(input, imgSelector) {
    const file = input.files && input.files[0];
    const $img = $(imgSelector);

    if (!file) {
      $img.addClass("d-none").attr("src", "");
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      $img.removeClass("d-none").attr("src", e.target.result);
    };
    reader.readAsDataURL(file);
  }

  $("#img_frontal").on("change", function () { previewFile(this, "#preview_frontal"); });
  $("#img_posterior").on("change", function () { previewFile(this, "#preview_posterior"); });


  /* ======= Cargar producto (ediciÃ³n) ======= */
  if (id_producto != "") {
    $.get(`../api/v1/fulmuv/productos/${id_producto}`, function (respRaw) {
      let resp = respRaw;
      if (typeof respRaw === 'string') {
        try { resp = JSON.parse(respRaw); } catch (e) { resp = respRaw; }
      }
      if (!resp || resp.error) return;

      let p = null;
      if (resp.data && !Array.isArray(resp.data) && typeof resp.data === 'object') {
        p = resp.data.producto || resp.data;
      } else if (Array.isArray(resp.data)) {
        p = resp.data[0] || null;
      } else if (resp.producto) {
        p = resp.producto;
      } else if (typeof resp === 'object') {
        p = resp;
      }
      if (!p) return;

      isEditLoading = true;

      // === BÃ¡sicos
      $("#titulo_producto").val(p.titulo_producto || "");
      setTinyMCEWhenReady('descripcion', p.descripcion || "");
      $("#precio_referencia").val(p.precio_referencia ?? "");
      $("#descuento").val(p.descuento ?? "");
      setCheckbox($("#iva"), p.iva);

      // === Flags de emergencia
      setCheckbox($("#emergencia_24_7"), p.emergencia_24_7);
      setCheckbox($("#emergencia_carretera"), p.emergencia_carretera);
      setCheckbox($("#emergencia_domicilio"), p.emergencia_domicilio);

      // Tags
      const tagsArr = Array.isArray(p.tags) ? p.tags : safeUpperArrayCSV(p.tags);
      try { tagsInput.clearStore(); } catch (e) { }
      if (tagsArr?.length) tagsArr.forEach(t => tagsInput.setValue([t]));

      applyServicioEditSelections(p, resp);

      // âœ… Preview ImÃ¡genes Guardadas
      if (!$("#img_frontal_actual").length) {
        $('<input type="hidden" id="img_frontal_actual">').appendTo('body');
        $('<input type="hidden" id="img_posterior_actual">').appendTo('body');
      }

      $("#img_frontal_actual").val(p.img_frontal || "");
      $("#img_posterior_actual").val(p.img_posterior || "");

      if (p.img_frontal) {
        $("#preview_frontal").removeClass("d-none").attr("src", fileUrlAdmin(p.img_frontal));
      }
      if (p.img_posterior) {
        $("#preview_posterior").removeClass("d-none").attr("src", fileUrlAdmin(p.img_posterior));
      }

      // En ediciÃ³n NO obligar a re-subir
      $("#img_frontal, #img_posterior").prop("required", false);

      setTimeout(() => applyServicioEditSelections(p, resp), 700);
      setTimeout(() => { isEditLoading = false; }, 900);

    }, 'json');
  }
});

function setSelect2ValueByText($select, values) {
  if (!values) return;

  let arr = [];
  if (Array.isArray(values)) arr = values;
  else if (typeof values === "string") {
    try {
      const j = JSON.parse(values);
      arr = Array.isArray(j) ? j : [values];
    } catch (e) {
      arr = values.split(",").map(x => x.trim()).filter(Boolean);
    }
  } else {
    arr = [String(values)];
  }

  const isMultiple = $select.prop("multiple");
  if (!isMultiple && arr.length) arr = [arr[0]];

  arr.forEach(v => {
    const val = String(v).toUpperCase().trim();
    if (!val) return;

    if ($select.find(`option[value="${CSS.escape(val)}"]`).length === 0) {
      const opt = new Option(val, val, true, true);
      $select.append(opt);
    }
  });

  $select.val(arr.map(x => String(x).toUpperCase().trim())).trigger("change");
}

/* ====== EVENTOS EXTRA ====== */
$("#nombre").on('change', function (e) {
  var idProducto = $(this).val();
  if (idProducto != '' && idProducto != null && idProducto != 'nuevo') {
    $.get('../api/v1/fulmuv/getNombreServicioById/' + idProducto, {}, function (returnedData) {
      // var returned = JSON.parse(returnedData);
    });
  }
});

/* ====== REFERENCIAS -> MODELOS ====== */
function buscarModelosReferencia(referenciaParam = null, modelosPairs = []) {

  let referencia = referenciaParam ?? $("#referencia").val();

  if (Array.isArray(referencia)) {
    referencia = referencia[0] || '';
  }

  referencia = (referencia || '').toString().trim();
  if (!referencia) {
    $("#modelo").empty().append(`<option value="">Seleccione modelo</option>`).trigger("change");
    return;
  }

  $.get('../api/v1/fulmuv/getModelosByReferencia/' + encodeURIComponent(referencia), {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    modelos = returned.data || [];

    $("#modelo").empty();

    modelos.forEach(model => {
      $("#modelo").append(`<option value="${model.id_modelos_autos}">${model.nombre}</option>`);
    });

    const $sel = $('#modelo');

    if ($sel.hasClass('select2-hidden-accessible')) {
      $sel.select2('destroy');
    }

    $sel.attr('multiple', 'multiple');

    $sel.select2({
      theme: 'bootstrap-5',
      tags: true,
      placeholder: 'Seleccione modelo',
      allowClear: true,
      createTag: function (params) {
        var term = $.trim(params.term).toUpperCase();
        if (term.length > 100) term = term.substring(0, 100);
        if (term === '') return null;
        return { id: 'nuevo', text: term, newTag: true };
      }
    });

    wireSelectEnsure($('#modelo'), {
      entity: 'modelos_autos',
      label: 'Modelo',
      parents: function () {
        function idNum(v) { return /^\d+$/.test(v) ? parseInt(v, 10) : 0; }
        return {
          id_marca: idNum($('#marca').val()),
          id_tipo_auto: idNum($('#tipo_vehiculo').val()),
          id_tipo_traccion: idNum($('#traccion').val()),
          id_funcionamiento_motor: idNum($('#motor').val())
        };
      }
    });

    if (modelosPairs && modelosPairs.length) {
      setSelectMultiplePairs($("#modelo"), modelosPairs);
    }
  });
}

function asignarModelo() {
  var id_modelo = $("#modelo").val();
  if (id_modelo && id_modelo !== "nuevo") {
    $.get('../api/v1/fulmuv/getModeloById/' + id_modelo, {}, function (returnedData) {
      var returned = JSON.parse(returnedData);
      $("#marca").val(returned.data.id_marca).trigger("change");
      $("#tipo_vehiculo").val(returned.data.id_tipo_auto).trigger("change");
      $("#traccion").val(returned.data.id_tipo_traccion).trigger("change");
      $("#motor").val(returned.data.id_funcionamiento_motor).trigger("change");
    });
  }
}

// =========================================
// 4) GUARDAR PRODUCTO
// =========================================
function addProducto(btnSelector) {

  var tags = tagsInput.getValue(true).map(tag => tag.toUpperCase());
  var texto = $("#nombre option:selected").text();
  let descripcion = $("#descripcion").val();

  if (typeof tinymce !== 'undefined' && tinymce.get('descripcion')) {
    descripcion = tinymce.get('descripcion').getContent();
    descripcion = emojiToEntities(descripcion);
  }

  var tipo_vehiculo = $("#tipo_vehiculo").val();
  var modelo = $("#modelo").val();
  var referencia = $("#referencia").val();
  var marca = $("#marca").val();
  var traccion = $("#traccion").val();
  var categoria = $("#categoria").val();
  var precio_referencia = $("#precio_referencia").val();
  var descuento = $("#descuento").val();
  var titulo_producto = $("#titulo_producto").val();
  var funcionamiento_motor = $("#motor").val();

  var emergencia_24_7 = $('#emergencia_24_7').is(':checked') ? 1 : 0;
  var emergencia_carretera = $('#emergencia_carretera').is(':checked') ? 1 : 0;
  var emergencia_domicilio = $('#emergencia_domicilio').is(':checked') ? 1 : 0;

  if (!validarCamposObligatorios()) {
    if (btnSelector) resetBtnLoading(btnSelector);
    return;
  }

  if (!Array.isArray(referencia)) {
    referencia = [];
  }

  if (btnSelector) setBtnLoading(btnSelector, "Registrando...");

  var dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");
  var files = dropzoneInstance.getAcceptedFiles();

  subirImagenesPrincipales()
    .then(imagenes => saveFiles(files).then(archivos => ({ imagenes, archivos })))
    .then(({ imagenes, archivos }) => {

      return postJSON('../api/v1/fulmuv/productos/create', {
        nombre: texto,
        descripcion: descripcion,
        codigo: '',
        categoria: categoria,
        sub_categoria: [], // servicio
        tags: tags.join(', '),
        precio_referencia: precio_referencia,
        img_frontal: imagenes.img_frontal,
        img_posterior: imagenes.img_posterior,
        archivos: archivos,
        atributos: [],
        id_empresa: $("#id_empresa").val(),
        descuento: descuento,
        tipo_vehiculo: tipo_vehiculo,
        modelo: modelo,
        marca: marca,
        traccion: traccion,
        peso: 0,
        titulo_producto: titulo_producto,
        marca_producto: '',
        iva: 0,
        negociable: 0,
        emergencia_24_7: emergencia_24_7,
        emergencia_carretera: emergencia_carretera,
        emergencia_domicilio: emergencia_domicilio,
        referencias: referencia,
        tipo_creador: tipo_user,
        tipo_producto: 'servicio',
        funcionamiento_motor: funcionamiento_motor
      });

    })
    .then(function (returned) {
      if (returned && returned.error == false) {
        SweetAlert("url_success", returned.msg, "servicios.php");
      } else {
        SweetAlert("error", (returned && returned.msg) ? returned.msg : "Hubo un error al registrar el servicio.");
      }
    })
    .catch(function () {
      SweetAlert("error", "Ocurrió un error al registrar el servicio.");
    })
    .finally(function () {
      if (btnSelector) resetBtnLoading(btnSelector);
    });
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    if (!files.length) {
      resolve([]);
    } else {
      const formData = new FormData();
      files.forEach(function (file) {
        formData.append(`archivos[]`, file);
      });
      $.ajax({
        type: 'POST',
        data: formData,
        url: '../admin/cargar_imagen_multiple.php',
        cache: false,
        contentType: false,
        processData: false,
        success: function (returnedImagen) {
          if (returnedImagen["response"] == "success") {
            resolve(returnedImagen["data"]);
          } else {
            SweetAlert("error", "OcurriÃ³ un error al guardar los archivos." + returnedImagen["error"]);
            reject();
          }
        }
      });
    }
  });
}

function verificarMembresiaYGuardar(btnEl) {
  const btnSelector = btnEl ? btnEl : null;
  var id_empresa = ($("#id_rol_principal").val() == 1) ? $("#lista_empresas").val() : $("#id_empresa").val();

  if (btnSelector) setBtnLoading(btnSelector, "Cargando...");

  $.get('../api/v1/fulmuv/validarMembresiaProductos/' + id_empresa + '/' + tipo_user, {
    modulo: 'servicio',
    categoria_id: $("#categoria").val()
  }, function (data) {
    var res = JSON.parse(data);
    if (res.error) {
      if (btnSelector) resetBtnLoading(btnSelector);
      swal({
        title: "Necesitas mejorar tu plan",
        text: `${res.msg}\n\nÂ¿Deseas ir ahora a actualizar tu membresÃ­a?`,
        icon: "info",
        buttons: {
          cancel: { text: "Cancelar", visible: true, closeModal: true },
          confirm: { text: "Mejorar plan", value: true, closeModal: true }
        }
      }, function () { window.location.href = "upgrade_membresia.php?id_empresa=" + id_empresa; });
    } else {
      validarLimiteBasicMuvPorCategoria(id_empresa, $("#categoria").val(), 0).then(function (validacionBasic) {
        if (validacionBasic.error) {
          if (btnSelector) resetBtnLoading(btnSelector);
          SweetAlert("error", validacionBasic.msg);
          return;
        }
        addProducto(btnSelector);
      });
    }
  }).fail(function () {
    if (btnSelector) resetBtnLoading(btnSelector);
    SweetAlert("error", "Error de conexiÃ³n al validar la membresÃ­a.");
  });
}

function verificarMembresiaYEditar(btnEl) {
  const btnSelector = btnEl ? btnEl : null;
  var id_empresa = ($("#id_rol_principal").val() == 1) ? $("#lista_empresas").val() : $("#id_empresa").val();

  if (btnSelector) setBtnLoading(btnSelector, "Cargando...");

  $.get('../api/v1/fulmuv/validarMembresiaProductos/' + id_empresa + '/' + tipo_user, {
    modulo: 'servicio',
    id_registro: id_producto || 0,
    categoria_id: $("#categoria").val()
  }, function (data) {
    var res = JSON.parse(data);
    if (res.error) {
      if (btnSelector) resetBtnLoading(btnSelector);
      swal({
        title: "Necesitas mejorar tu plan",
        text: `${res.msg}\n\nÂ¿Deseas ir ahora a actualizar tu membresÃ­a?`,
        icon: "info",
        buttons: {
          cancel: { text: "Cancelar", visible: true, closeModal: true },
          confirm: { text: "Mejorar plan", value: true, closeModal: true }
        }
      }, function () { window.location.href = "upgrade_membresia.php?id_empresa=" + id_empresa; });
    } else {
      validarLimiteBasicMuvPorCategoria(id_empresa, $("#categoria").val(), id_producto || 0).then(function (validacionBasic) {
        if (validacionBasic.error) {
          if (btnSelector) resetBtnLoading(btnSelector);
          SweetAlert("error", validacionBasic.msg);
          return;
        }
        editProducto(btnSelector);
      });
    }
  }).fail(function () {
    if (btnSelector) resetBtnLoading(btnSelector);
    SweetAlert("error", "Error de conexiÃ³n al validar la membresÃ­a.");
  });
}


function swalConfirmV1(title, text, okText, cancelText, onOk, onCancel) {
  swal({
    title: title,
    text: text,
    type: "info",
    showCancelButton: true,
    confirmButtonText: okText || "SÃ­",
    cancelButtonText: cancelText || "No",
    closeOnConfirm: true,
    closeOnCancel: true
  }, function (isConfirm) {
    if (isConfirm) { if (typeof onOk === 'function') onOk(); }
    else { if (typeof onCancel === 'function') onCancel(); }
  });
}

function ensureRemote(entity, nombre, parents) {
  var payload = $.extend({ entity: entity, nombre: nombre }, parents || {});
  return $.post('../api/v1/fulmuv/catalog/ensure', payload)
    .then(function (raw) {
      var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
      if (r.error) return $.Deferred().reject(r.msg || 'No se pudo registrar').promise();
      return r.id;
    });
}

function wireSelectEnsure($el, cfg) {
  $el.on('select2:opening', function () { $(this).data('prev', $(this).val()); });

  $el.on('select2:select', function (e) {
    var data = e.params.data || {};
    var val = data.id;
    var txt = (data.text || '').trim();
    var isNumeric = /^\d+$/.test(String(val));
    var isNew = (data.newTag === true) || (!data.element && !isNumeric) || (val === 'nuevo');

    if (!isNew) return;

    var existente = findExistingOptionByNormalizedText($el, txt, val);
    if (existente) {
      swal(
        "Nombre ya existe",
        'El nombre "' + existente.text + '" ya existe en la lista. BÃºscalo y selecciÃ³nalo en lugar de registrarlo nuevamente.',
        "warning"
      );
      $el.find('option').filter(function () { return $(this).val() == val; }).remove();
      $el.val(existente.value).trigger('change');
      return;
    }

    var parents = (cfg.parents && typeof cfg.parents === 'function') ? (cfg.parents() || {}) : {};

    for (var k in parents) {
      if (parents.hasOwnProperty(k) && (!parents[k] || +parents[k] <= 0)) {
        swal("Falta seleccionar", "Debes seleccionar primero el campo relacionado para registrar " + (cfg.label || cfg.entity).toLowerCase() + ".", "warning");
        $el.val($el.data('prev') || null).trigger('change');
        return;
      }
    }

    swalConfirmV1(
      "Registrar nuevo " + (cfg.label || cfg.entity),
      'Â¿Deseas registrar "' + txt + '"?',
      "SÃ­, registrar", "Cancelar",
      function () {
        ensureRemote(cfg.entity, txt, parents).then(function (id) {
          $el.find('option').filter(function () { return $(this).val() == val; }).remove();
          var newOpt = new Option(txt, id, true, true);
          $el.append(newOpt).trigger('change');
          swal("Listo", (cfg.label || cfg.entity) + " registrado correctamente.", "success");
        }).fail(function (msg) {
          swal("Error", (msg && msg.toString ? msg.toString() : "No se pudo registrar."), "error");
          $el.val($el.data('prev') || null).trigger('change');
        });
      },
      function () {
        $el.val($el.data('prev') || null).trigger('change');
      }
    );
  });
}

function subirImagenesPrincipales() {
  return new Promise(function (resolve, reject) {
    const imgFrontal = document.getElementById('img_frontal').files[0];
    const imgPosterior = document.getElementById('img_posterior').files[0];

    if (!imgFrontal || !imgPosterior) {
      SweetAlert("error", "Debes seleccionar la imagen frontal y la imagen posterior.");
      reject();
      return;
    }

    const formData = new FormData();
    formData.append('img_frontal', imgFrontal);
    formData.append('img_posterior', imgPosterior);

    $.ajax({
      url: '../admin/cargar_imagenes_frontales.php',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.response === "success") {
          resolve({
            img_frontal: res.data.img_frontal,
            img_posterior: res.data.img_posterior
          });
        } else {
          SweetAlert("error", res.error || "Error al subir las imÃ¡genes principales.");
          reject();
        }
      },
      error: function () {
        SweetAlert("error", "Error de red al subir imÃ¡genes principales.");
        reject();
      }
    });
  });
}

function eliminarArchivoGaleria(id_archivo_producto, file) {
  Swal.fire({
    title: 'Â¿Eliminar archivo?',
    text: 'Esta acciÃ³n no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'SÃ­, eliminar',
    cancelButtonText: 'No, cancelar'
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.post(
      '../api/v1/fulmuv/archivos_productos/delete',
      { id_archivo_producto: id_archivo_producto },
      function (respRaw) {
        var resp = (typeof respRaw === 'string') ? JSON.parse(respRaw) : respRaw;

        if (!resp.error) {
          if (file && myDropzone) {
            myDropzone.removeFile(file);
          }
          Swal.fire('Eliminado', resp.msg || 'El archivo ha sido eliminado.', 'success');
        } else {
          Swal.fire('Error', resp.msg || 'No se pudo eliminar el archivo.', 'error');
        }
      }
    ).fail(function () {
      Swal.fire('Error', 'Error de comunicaciÃ³n con el servidor.', 'error');
    });
  });
}


function editProducto(btnSelector) {
  var tags = tagsInput.getValue(true).map(tag => tag.toUpperCase());
  var texto = $("#nombre option:selected").text();

  let descripcion = $("#descripcion").val();
  if (typeof tinymce !== 'undefined' && tinymce.get('descripcion')) {
    descripcion = tinymce.get('descripcion').getContent();
    descripcion = emojiToEntities(descripcion);
  }

  // ... (tus variables de captura de datos se mantienen igual) ...
  var tipo_vehiculo = $("#tipo_vehiculo").val();
  var modelo = $("#modelo").val();
  var referencia = $("#referencia").val();
  var marca = $("#marca").val();
  var traccion = $("#traccion").val();
  var categoria = $("#categoria").val();
  var precio_referencia = $("#precio_referencia").val();
  var descuento = $("#descuento").val();
  var titulo_producto = $("#titulo_producto").val();
  var funcionamiento_motor = $("#motor").val();
  var emergencia_24_7 = $('#emergencia_24_7').is(':checked') ? 1 : 0;
  var emergencia_carretera = $('#emergencia_carretera').is(':checked') ? 1 : 0;
  var emergencia_domicilio = $('#emergencia_domicilio').is(':checked') ? 1 : 0;

  if (!validarCamposObligatorios()) {
    if (btnSelector) resetBtnLoading(btnSelector);
    return;
  }

  if (btnSelector) setBtnLoading(btnSelector, "Actualizando...");

  var dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");

  // INICIO DE LA CORRECCIÃ“N
  subirImagenesPrincipalesEdit()
    .then(imagenes => {
      return saveFilesEdit(dropzoneInstance).then(archivosNuevos => ({ imagenes, archivosNuevos }));
    })
    .then(({ imagenes, archivosNuevos }) => {
      return postJSON('../api/v1/fulmuv/productos/edit', {
        nombre: texto,
        descripcion: descripcion,
        codigo: '',
        categoria: categoria,
        sub_categoria: [],
        tags: tags.join(', '),
        precio_referencia: precio_referencia,
        img_frontal: imagenes.img_frontal,
        img_posterior: imagenes.img_posterior,
        archivos: archivosNuevos,
        atributos: [],
        id_empresa: $("#id_empresa").val(),
        descuento: descuento,
        tipo_vehiculo: tipo_vehiculo,
        modelo: modelo,
        marca: marca,
        traccion: traccion,
        peso: 0,
        titulo_producto: titulo_producto,
        marca_producto: '',
        iva: 0,
        negociable: 0,
        emergencia_24_7: emergencia_24_7,
        emergencia_carretera: emergencia_carretera,
        emergencia_domicilio: emergencia_domicilio,
        referencias: referencia,
        tipo_creador: tipo_user,
        tipo_producto: 'servicio',
        funcionamiento_motor: funcionamiento_motor,
        imagenFrontalEdit: imagenFrontalEdit,
        imagenPosteriorEdit: imagenPosteriorEdit,
        id_producto: id_producto
      });
    })
    .then(returned => {
      if (returned && returned.error == false) {
        SweetAlert("url_success", returned.msg || "El servicio ha sido actualizado con éxito.", "servicios.php");
      } else {
        SweetAlert("error", (returned && returned.msg) ? returned.msg : "Hubo un error en la actualización del servicio.");
      }
    })
    .catch(err => {
      console.error(err);
      SweetAlert("error", "Ocurrió un error al procesar la edición.");
    })
    .finally(() => {
      if (btnSelector) resetBtnLoading(btnSelector);
    });
}
function subirImagenesPrincipalesEdit() {
  return new Promise(function (resolve, reject) {
    const imgFrontal = document.getElementById('img_frontal').files[0] || null;
    const imgPosterior = document.getElementById('img_posterior').files[0] || null;

    const imgFrontalActual = $('#img_frontal_actual').val() || '';
    const imgPosteriorActual = $('#img_posterior_actual').val() || '';

    if (!imgFrontal && !imgPosterior) {
      imagenFrontalEdit = 0;
      imagenPosteriorEdit = 0;
      resolve({ img_frontal: imgFrontalActual, img_posterior: imgPosteriorActual });
      return;
    }

    const formData = new FormData();
    if (imgFrontal) formData.append('img_frontal', imgFrontal);
    if (imgPosterior) formData.append('img_posterior', imgPosterior);

    $.ajax({
      url: '../admin/cargar_imagenes_frontales.php',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.response === "success") {
          const nuevaFrontal = res.data?.img_frontal || null;
          const nuevaPosterior = res.data?.img_posterior || null;

          imagenFrontalEdit = imgFrontal ? 1 : 0;
          imagenPosteriorEdit = imgPosterior ? 1 : 0;

          resolve({
            img_frontal: nuevaFrontal || imgFrontalActual,
            img_posterior: nuevaPosterior || imgPosteriorActual
          });

        } else {
          SweetAlert("error", res.error || "Error al subir imÃ¡genes principales.");
          reject();
        }
      },
      error: function () {
        SweetAlert("error", "Error de red al subir imÃ¡genes principales.");
        reject();
      }
    });
  });
}

function validarCamposObligatorios() {
  let ok = true;
  const errores = [];

  const requeridos = document.querySelectorAll("input[required], select[required], textarea[required]");

  requeridos.forEach(el => {
    const id = el.id || "";
    const labelText =
      (document.querySelector(`label[for="${id}"]`)?.innerText || el.getAttribute("name") || id || "Campo")
        .replace("*", "")
        .trim();

    let valido = true;

    if (el.tagName === "SELECT") {
      if (el.multiple) {
        valido = el.selectedOptions && el.selectedOptions.length > 0 && el.value !== "";
      } else {
        valido = (el.value || "").trim() !== "";
      }
    } else if (el.type === "file") {
      const isEdit = Number($("#id_producto").val() || 0) > 0;
      if (isEdit && (el.id === "img_frontal" || el.id === "img_posterior")) {
        valido = true;
      } else {
        valido = el.files && el.files.length > 0;
      }
    } else if (el.type === "checkbox" || el.type === "radio") {
      valido = el.checked === true;
    } else {
      valido = (el.value || "").trim() !== "";
    }

    if (!valido) {
      ok = false;
      errores.push(`Falta: ${labelText}`);
      el.classList.add("is-invalid");
    } else {
      el.classList.remove("is-invalid");
      el.classList.add("is-valid");
    }
  });

  if (document.querySelector('label[for="product-summary"]') || document.querySelector("#descripcion")) {
    const ed = window.tinymce?.get("descripcion");
    const contenido = (ed ? ed.getContent({ format: "text" }) : ($("#descripcion").val() || "")).trim();
    if (ed) ed.save();

    const textarea = document.getElementById("descripcion");
    if (textarea) {
      if (!contenido) {
        ok = false;
        errores.push("Falta: DescripciÃ³n");
        textarea.closest(".create-product-description-textarea")?.classList.add("border", "border-danger", "rounded");
      } else {
        textarea.closest(".create-product-description-textarea")?.classList.remove("border", "border-danger", "rounded");
      }
    }
  }

  if (!ok) {
    SweetAlert("error", errores.join("\n"));
  }

  return ok;
}

function saveFilesEdit(dropzoneInstance) {
  return new Promise(function (resolve, reject) {
    const nuevos = (dropzoneInstance.files || []).filter(f => !f.existing);

    if (!nuevos.length) {
      resolve([]);
      return;
    }

    const formData = new FormData();
    nuevos.forEach(file => formData.append(`archivos[]`, file));

    $.ajax({
      type: 'POST',
      data: formData,
      url: '../admin/cargar_imagen_multiple.php',
      cache: false,
      contentType: false,
      processData: false,
      success: function (returnedImagen) {
        if (returnedImagen["response"] == "success") {
          resolve(returnedImagen["data"]);
        } else {
          SweetAlert("error", "OcurriÃ³ un error al guardar los archivos." + returnedImagen["error"]);
          reject();
        }
      },
      error: reject
    });
  });
}

function postJSON(url, data) {
  return new Promise(function (resolve, reject) {
    $.ajax({
      url: url,
      method: 'POST',
      data: data,
      success: function (returnedData) {
        try {
          resolve(typeof returnedData === 'string' ? JSON.parse(returnedData) : returnedData);
        } catch (e) {
          reject(e);
        }
      },
      error: reject
    });
  });
}

function emojiToEntities(str) {
  try {
    return str.replace(/\p{Extended_Pictographic}/gu, (m) =>
      Array.from(m).map(ch => `&#${ch.codePointAt(0)};`).join('')
    );
  } catch (e) {
    return Array.from(str).map(ch => {
      const cp = ch.codePointAt(0);
      return cp > 0xFFFF ? `&#${cp};` : ch;
    }).join('');
  }
}

function setBtnLoading(btnSelector, loadingText = "Cargando...") {
  const $btn = $(btnSelector);
  if (!$btn.length) return;

  if (!$btn.data("original-text")) {
    $btn.data("original-text", $btn.html());
  }

  $btn.prop("disabled", true);
  $btn.html(`
    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    ${loadingText}
  `);
}

function resetBtnLoading(btnSelector) {
  const $btn = $(btnSelector);
  if (!$btn.length) return;

  const original = $btn.data("original-text");
  if (original) $btn.html(original);

  $btn.prop("disabled", false);
}

