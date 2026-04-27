let categorias = [];
var tagsInput = '';
let modelos = [];
let modelosCatalogo = [];
let tipos_auto = [];
let marcas = [];
let traccion = [];
let motor = [];
let tipo_user = $("#tipo_user").val();
let vehiculoEditData = null;
let modelosRequestSeq = 0;
let formReadyTimer = null;

// Dropzone GLOBAL para poder usarla en editar
window.myDropzone = null;
let isEditLoading = false; // ✅ Bloqueador temporal para edición

const cantones = {
  "Azuay": ["Cuenca", "Camilo Ponce Enríquez", "Chordeleg", "El Pan", "Girón", "Gualaceo", "Nabón", "Oña", "Paute", "Pucará", "San Fernando", "Santa Isabel", "Sevilla de Oro", "Sigsig"],
  "Bolívar": ["Guaranda", "Chillanes", "Chimbo", "Echeandía", "Las Naves", "San Miguel"],
  "Cañar": ["Azogues", "Biblián", "Cañar", "Déleg", "El Tambo", "La Troncal", "Suscal"],
  "Carchi": ["Tulcán", "Bolívar", "Espejo", "Mira", "Montúfar", "San Pedro de Huaca"],
  "Cotopaxi": ["Latacunga", "La Maná", "Pangua", "Pujilí", "Salcedo", "Saquisilí", "Sigchos"],
  "Chimborazo": ["Riobamba", "Alausí", "Chambo", "Chunchi", "Colta", "Cumandá", "Guamote", "Guano", "Pallatanga", "Penipe"],
  "El Oro": ["Machala", "Arenillas", "Atahualpa", "Balsas", "Chilla", "El Guabo", "Huaquillas", "Las Lajas", "Marcabelí", "Pasaje", "Piñas", "Portovelo", "Santa Rosa", "Zaruma"],
  "Esmeraldas": ["Esmeraldas", "Atacames", "Eloy Alfaro", "Muisne", "Quinindé", "Rioverde", "San Lorenzo"],
  "Guayas": ["Guayaquil", "Alfredo Baquerizo Moreno", "Balao", "Balzar", "Colimes", "Daule", "Durán", "El Empalme", "El Triunfo", "General Antonio Elizalde", "Isidro Ayora", "Lomas de Sargentillo", "Marcelino Maridueña", "Milagro", "Naranjal", "Naranjito", "Nobol", "Palestina", "Pedro Carbo", "Playas", "Salitre", "Samborondón", "Santa Lucía", "Simón Bolívar", "Yaguachi"],
  "Imbabura": ["Ibarra", "Antonio Ante", "Cotacachi", "Otavalo", "Pimampiro", "San Miguel de Urcuquí"],
  "Loja": ["Loja", "Calvas", "Catamayo", "Celica", "Chaguarpamba", "Espíndola", "Gonzanamá", "Macará", "Olmedo", "Paltas", "Pindal", "Puyango", "Quilanga", "Saraguro", "Sozoranga", "Zapotillo"],
  "Los Ríos": ["Babahoyo", "Baba", "Buena Fe", "Mocache", "Montalvo", "Palenque", "Puebloviejo", "Quevedo", "Quinsaloma", "Urdaneta", "Valencia", "Ventanas", "Vinces"],
  "Manabí": ["Portoviejo", "Bolívar", "Chone", "El Carmen", "Flavio Alfaro", "Jama", "Jaramijó", "Jipijapa", "Junín", "Manta", "Montecristi", "Olmedo", "Paján", "Pedernales", "Pichincha", "Puerto López", "Rocafuerte", "Santa Ana", "Sucre", "Tosagua", "Veinticuatro de Mayo"],
  "Morona Santiago": ["Morona", "Gualaquiza", "Huamboya", "Limón Indanza", "Logroño", "Pablo Sexto", "Palora", "San Juan Bosco", "Sucúa", "Taisha", "Tiwintza"],
  "Napo": ["Tena", "Archidona", "Carlos Julio Arosemena Tola", "El Chaco", "Quijos"],
  "Pastaza": ["Puyo", "Arajuno", "Mera", "Santa Clara"],
  "Pichincha": ["Quito", "Cayambe", "Mejía", "Pedro Moncayo", "Pedro Vicente Maldonado", "Puerto Quito", "Rumiñahui", "San Miguel de Los Bancos"],
  "Tungurahua": ["Ambato", "Baños de Agua Santa", "Cevallos", "Mocha", "Patate", "Quero", "San Pedro de Pelileo", "Santiago de Píllaro", "Tisaleo"],
  "Zamora Chinchipe": ["Zamora", "Centinela del Cóndor", "Chinchipe", "El Pangui", "Nangaritza", "Palanda", "Paquisha", "Yacuambi", "Yantzaza"],
  "Galápagos": ["San Cristóbal", "Isabela", "Santa Cruz"],
  "Sucumbíos": ["Nueva Loja", "Cascales", "Cuyabeno", "Gonzalo Pizarro", "Lago Agrio", "Putumayo", "Shushufindi", "Sucumbíos"],
  "Orellana": ["Francisco de Orellana", "Aguarico", "La Joya de Los Sachas", "Loreto"],
  "Santo Domingo de los Tsáchilas": ["Santo Domingo"],
  "Santa Elena": ["Santa Elena", "La Libertad", "Salinas"]
};

/* ==============================
   ✅ HELPERS DE NORMALIZACIÓN
================================= */

function parseMaybeJSON(v) {
  if (typeof v !== 'string') return v;
  try { return JSON.parse(v); } catch (_) { return v; }
}

function parseAjaxJSON(raw, fallback = { error: true, data: [] }) {
  if (raw == null || raw === '') return fallback;
  if (typeof raw === 'object') return raw;
  try {
    return JSON.parse(raw);
  } catch (_) {
    return fallback;
  }
}

function showVehiculoFormLoading() {
  $("#vehiculoLoadingOverlay").removeClass("is-hidden");
  $("#vehiculoFormWrapper").addClass("is-loading");
}

function hideVehiculoFormLoading(delay = 0) {
  clearTimeout(formReadyTimer);
  formReadyTimer = setTimeout(() => {
    $("#vehiculoLoadingOverlay").addClass("is-hidden");
    $("#vehiculoFormWrapper").removeClass("is-loading");
  }, delay);
}

function safeArr(x){
  if (Array.isArray(x)) return x;
  if (!x) return [];
  if (typeof x === 'string') {
    const s = x.trim();
    if (!s) return [];
    try {
      const p = JSON.parse(s);
      return Array.isArray(p) ? p : [p];
    } catch (e) {
      return [s];
    }
  }
  return [x];
}

function normalizarTextoVehiculo(v) {
  return String(v || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
    .toUpperCase();
}

function getReferenciasPermitidas() {
  return ['UTV', 'ATV', 'CAMIONES Y PESADOS', 'CAMIONES', 'PESADOS'].map(normalizarTextoVehiculo);
}

function sanitizarReferenciaVisible(ref) {
  const normalized = normalizarTextoVehiculo(ref);
  if (!normalized) return '';
  if (['CAMIONES Y PESADOS', 'CAMIONES', 'PESADOS'].includes(normalized)) return 'COMERCIALES';
  if (['UTV', 'ATV'].includes(normalized)) return '';
  return String(ref).trim();
}

function expandirReferenciaSeleccionada(ref) {
  const normalized = normalizarTextoVehiculo(ref);
  if (normalized === 'COMERCIALES') {
    return ['COMERCIALES', 'CAMIONES Y PESADOS', 'CAMIONES', 'PESADOS'].map(normalizarTextoVehiculo);
  }
  return normalized ? [normalized] : [];
}

function placeholderOption(text = 'Seleccione....') {
  return `<option value="">${text}</option>`;
}

/* ==============================
   ✅ FILTRADO POR TIPO DE VEHÍCULO
================================= */

/**
 * Devuelve los ítems de `lista` cuya columna `referencia` incluye
 * el tipo seleccionado (comparación normalizada, case-insensitive).
 * Los ítems sin referencia se muestran siempre.
 */
function filtrarPorReferencia(lista, referencia) {
  if (!referencia) return lista || [];
  const refNorm = normalizarTextoVehiculo(String(referencia));
  return (lista || []).filter(function (item) {
    const refStr = String(item.referencia || '').trim();
    if (!refStr) return true; // sin restricción → aparece para todos los tipos
    return refStr.split(',').map(function (r) {
      return normalizarTextoVehiculo(r.trim());
    }).indexOf(refNorm) !== -1;
  });
}

/**
 * Re-puebla los selects de Marca, Subtipo, Tracción y Motor
 * filtrando solo los ítems correspondientes al tipo de vehículo.
 *
 * @param {string}  referencia  - Valor del select #referencia
 * @param {boolean} keepValues  - true = no resetea los valores ya elegidos
 *                                (útil en modo edición antes de re-aplicar)
 */
function actualizarSelectsByReferencia(referencia, keepValues) {
  var keep = keepValues === true;

  // ── Marca ──────────────────────────────────────────────────
  var $m   = $('#marca');
  var prevM = $m.val();
  var mFilt = filtrarPorReferencia(marcas, referencia);
  $m.empty().append(placeholderOption());
  mFilt.forEach(function (m) { $m.append(new Option(m.nombre, m.id_marca)); });
  if (!keep) {
    var okM = prevM && mFilt.some(function (m) { return String(m.id_marca) === String(prevM); });
    $m.val(okM ? prevM : null).trigger('change');
  }

  // ── Subtipo ─────────────────────────────────────────────────
  var $t   = $('#tipo_vehiculo');
  var prevT = $t.val();
  var tFilt = filtrarPorReferencia(tipos_auto, referencia);
  $t.empty().append(placeholderOption());
  tFilt.forEach(function (t) { $t.append(new Option(t.nombre, t.id_tipo_auto)); });
  if (!keep) {
    var okT = prevT && tFilt.some(function (t) { return String(t.id_tipo_auto) === String(prevT); });
    $t.val(okT ? prevT : null).trigger('change');
  }

  // ── Tracción ────────────────────────────────────────────────
  var $tr   = $('#traccion');
  var prevTr = $tr.val();
  var trFilt = filtrarPorReferencia(traccion, referencia);
  $tr.empty().append(placeholderOption());
  trFilt.forEach(function (t) { $tr.append(new Option(t.nombre, t.id_tipo_traccion)); });
  if (!keep) {
    var okTr = prevTr && trFilt.some(function (t) { return String(t.id_tipo_traccion) === String(prevTr); });
    $tr.val(okTr ? prevTr : null).trigger('change');
  }

  // ── Motor ───────────────────────────────────────────────────
  var $mo   = $('#motor');
  var prevMo = $mo.val();
  var moFilt = filtrarPorReferencia(motor, referencia);
  $mo.empty().append(placeholderOption());
  moFilt.forEach(function (m) { $mo.append(new Option(m.nombre, m.id_funcionamiento_motor)); });
  if (!keep) {
    var okMo = prevMo && moFilt.some(function (m) { return String(m.id_funcionamiento_motor) === String(prevMo); });
    $mo.val(okMo ? prevMo : null).trigger('change');
  }
}

function propagarRelacionModelo() {
  var idModelo = parseInt($('#modelo').val() || '0', 10);
  if (!idModelo || idModelo <= 0) return;
  var payload = { id_modelos_autos: idModelo };
  var ta = parseInt($('#tipo_vehiculo').val() || '0', 10);
  var tr = parseInt($('#traccion').val()       || '0', 10);
  var mo = parseInt($('#motor').val()          || '0', 10);
  if (ta > 0) payload.id_tipo_auto           = ta;
  if (tr > 0) payload.id_tipo_traccion        = tr;
  if (mo > 0) payload.id_funcionamiento_motor = mo;
  if (Object.keys(payload).length <= 1) return;
  $.post('../api/v1/fulmuv/modelos_autos/enrich', payload);
}

function obtenerModeloCatalogoPorId(idModelo) {
  const id = String(idModelo || '');
  return (modelosCatalogo || []).find(m => String(m.id_modelos_autos) === id) || null;
}

function obtenerReferenciaVehiculoSeleccionada() {
  return $("#referencia").val() || '';
}

function getModelosPairsFromVehiculo(v) {
  const modeloData = obtenerModeloCatalogoPorId(v?.id_modelo);
  if (modeloData) {
    return [{ id: modeloData.id_modelos_autos, text: modeloData.nombre }];
  }
  return normMulti(v?.id_modelo, 'id_modelos_autos', 'nombre');
}

function syncEditModeloSelection() {
  if (!vehiculoEditData || !modelosCatalogo.length) return;

  const referenciaActual = obtenerReferenciaVehiculoSeleccionada();
  const marcaActual = $("#marca").val();
  const modeloData = obtenerModeloCatalogoPorId(vehiculoEditData.id_modelo);
  const referenciaFallback = sanitizarReferenciaVisible(
    safeArr(vehiculoEditData.referencias)[0] || (modeloData?.referencia ? String(modeloData.referencia).split(',')[0].trim() : '')
  );
  const marcaFallback = String(vehiculoEditData.id_marca || modeloData?.id_marca || '').trim();

  if (!referenciaActual && referenciaFallback) {
    setSelect2ValueByText($("#referencia"), referenciaFallback);
  }

  if (!marcaActual && marcaFallback) {
    aplicarSeleccionSimpleSiExiste($("#marca"), marcaFallback);
  }

  const referenciaFinal = obtenerReferenciaVehiculoSeleccionada();
  const marcaFinal = $("#marca").val();
  if (!referenciaFinal || !marcaFinal) return;

  actualizarModelosDisponibles(getModelosPairsFromVehiculo(vehiculoEditData));
}

function forzarCargaModeloEdicion() {
  if (!vehiculoEditData) return;
  const referencia = obtenerReferenciaVehiculoSeleccionada();
  const marcaId = $("#marca").val();
  if (!referencia || !marcaId) return;
  actualizarModelosDisponibles(getModelosPairsFromVehiculo(vehiculoEditData));
}

function filtrarModelosPorReferenciaYMarca(referencia, marcaId) {
  const referenciasValidas = expandirReferenciaSeleccionada(referencia);
  const marcaStr = String(marcaId || '').trim();

  return (modelosCatalogo || []).filter(modelo => {
    const referenciasModelo = String(modelo.referencia || '')
      .split(',')
      .map(normalizarTextoVehiculo)
      .filter(Boolean);

    const matchReferencia = !referenciasValidas.length || referenciasModelo.some(ref => referenciasValidas.includes(ref));
    const matchMarca = !marcaStr || String(modelo.id_marca) === marcaStr;

    return matchReferencia && matchMarca;
  });
}

function filtrarModelosPorMarca(marcaId) {
  const marcaStr = String(marcaId || '').trim();
  if (!marcaStr) return [];
  return (modelosCatalogo || []).filter(modelo => String(modelo.id_marca) === marcaStr);
}

function construirModelosPairsDesdeListado(lista, idModeloSeleccionado) {
  const id = String(idModeloSeleccionado || '').trim();
  if (!id) return [];
  const encontrado = (lista || []).find(modelo => String(modelo.id_modelos_autos) === id);
  if (encontrado) {
    return [{ id: encontrado.id_modelos_autos, text: encontrado.nombre }];
  }
  return getModelosPairsFromVehiculo(vehiculoEditData);
}

function asegurarModeloSeleccionadoEnEdicion($sel, lista) {
  if (!vehiculoEditData?.id_modelo) return false;
  const seleccion = construirModelosPairsDesdeListado(lista, vehiculoEditData.id_modelo);
  if (!seleccion.length) return false;
  setSelectMultiplePairs($sel, seleccion);
  return true;
}

function inicializarModeloSelect() {
  const $sel = $("#modelo");
  $sel.empty().append(placeholderOption());

  if ($sel.hasClass('select2-hidden-accessible')) {
    $sel.select2('destroy');
  }

  $sel.select2({
    theme: 'bootstrap-5',
    tags: true,
    placeholder: 'Seleccione....',
    allowClear: true,
    createTag: function (params) {
      var term = $.trim(params.term).toUpperCase();
      if (!term) return null;
      return { id: 'nuevo', text: term, newTag: true };
    }
  });

  wireSelectEnsure($sel, {
    entity:'modelos_autos', label:'Modelo',
    parents: function(){
      function idNum(v){ return /^\d+$/.test(String(v)) ? parseInt(v,10) : 0; }
      // Solo id_marca es obligatorio; los demás se incluyen si están disponibles
      var p   = { id_marca: idNum($('#marca').val()) };
      var ta  = idNum($('#tipo_vehiculo').val());
      var tr  = idNum($('#traccion').val());
      var mo  = idNum($('#motor').val());
      var ref = $('#referencia').val() || '';
      if (ta  > 0) p.id_tipo_auto              = ta;
      if (tr  > 0) p.id_tipo_traccion           = tr;
      if (mo  > 0) p.id_funcionamiento_motor    = mo;
      if (ref)     p.referencia                 = ref;
      return p;
    }
  });
}

function actualizarModelosDisponibles(modelosPairs = []) {
  const referencia = obtenerReferenciaVehiculoSeleccionada();
  const marcaId = $("#marca").val();
  const $sel = $("#modelo");
  const valorActual = String($sel.val() || '').trim();

  if (!$sel.hasClass('select2-hidden-accessible')) {
    inicializarModeloSelect();
    return actualizarModelosDisponibles(modelosPairs);
  }

  if (!referencia || !marcaId) {
    const listaFallback = marcaId ? filtrarModelosPorMarca(marcaId) : [];
    $sel.empty().append(placeholderOption());
    listaFallback.forEach(model => {
      $sel.append(`<option value="${model.id_modelos_autos}">${model.nombre}</option>`);
    });

    const puedeEditar = !!(vehiculoEditData?.id_modelo && marcaId);
    $sel.prop('disabled', !(puedeEditar || listaFallback.length));

    if (puedeEditar) {
      asegurarModeloSeleccionadoEnEdicion($sel, listaFallback);
    } else {
      $sel.val(null).trigger('change');
    }
    return;
  }

  const requestId = ++modelosRequestSeq;
  $.get('../api/v1/fulmuv/getModelosByReferenciaMarca/' + encodeURIComponent(referencia), {
    id_marca: marcaId
  }, function (returnedData) {
    if (requestId !== modelosRequestSeq) return;

    let returned = returnedData;
    if (typeof returnedData === 'string') {
      try {
        returned = JSON.parse(returnedData);
      } catch (e) {
        returned = { error: true, data: [] };
      }
    }
    const lista = returned.data || filtrarModelosPorReferenciaYMarca(referencia, marcaId);
    modelos = lista;

    $sel.empty().append(placeholderOption());
    lista.forEach(model => {
      $sel.append(`<option value="${model.id_modelos_autos}">${model.nombre}</option>`);
    });

    $sel.prop('disabled', false);

    if (modelosPairs && modelosPairs.length) {
      setSelectMultiplePairs($sel, modelosPairs);
    } else if (valorActual && lista.some(model => String(model.id_modelos_autos) === valorActual)) {
      setSelectMultiplePairs($sel, [{ id: valorActual, text: lista.find(model => String(model.id_modelos_autos) === valorActual)?.nombre || valorActual }]);
    } else if (vehiculoEditData?.id_modelo) {
      if (asegurarModeloSeleccionadoEnEdicion($sel, lista)) {
        return;
      }
      else $sel.val(null).trigger('change');
    } else {
      $sel.val(null).trigger('change');
    }
  }).fail(function () {
    if (requestId !== modelosRequestSeq) return;

    const lista = filtrarModelosPorReferenciaYMarca(referencia, marcaId);
    modelos = lista;

    $sel.empty().append(placeholderOption());
    lista.forEach(model => {
      $sel.append(`<option value="${model.id_modelos_autos}">${model.nombre}</option>`);
    });

    $sel.prop('disabled', false);

    if (modelosPairs && modelosPairs.length) {
      setSelectMultiplePairs($sel, modelosPairs);
    } else if (valorActual && lista.some(model => String(model.id_modelos_autos) === valorActual)) {
      setSelectMultiplePairs($sel, [{ id: valorActual, text: lista.find(model => String(model.id_modelos_autos) === valorActual)?.nombre || valorActual }]);
    } else if (vehiculoEditData?.id_modelo) {
      if (asegurarModeloSeleccionadoEnEdicion($sel, lista)) {
        return;
      }
      else $sel.val(null).trigger('change');
    } else {
      $sel.val(null).trigger('change');
    }
  });
}

function aplicarSeleccionSimpleSiExiste($select, value) {
  const val = String(value || '').trim();
  if (!val) return;
  if ($select.find(`option[value="${val.replace(/"/g, '\\"')}"]`).length) {
    $select.val(val).trigger('change');
  }
}

function seleccionarValorFiltrado($select, value) {
  const val = String(value || '').trim();
  if (!val) return false;
  const existe = $select.find(`option[value="${CSS.escape(val)}"]`).length > 0;
  if (!existe) return false;
  $select.val(val).trigger('change');
  return true;
}

function aplicarSeleccionMultipleSiExiste($select, values) {
  const arr = safeArr(values).map(v => String(v));
  if (!arr.length) return;
  const existentes = arr.filter(v => $select.find(`option[value="${v.replace(/"/g, '\\"')}"]`).length);
  if (existentes.length) {
    $select.val(existentes).trigger('change');
  }
}

function normMulti(v, idKey = 'id', textKey = 'nombre') {
  if (v == null) return [];
  v = parseMaybeJSON(v);

  const toPair = (x) => {
    if (x == null) return null;
    if (Array.isArray(x)) { if (!x.length) return null; x = x[0]; }
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
    if (s.includes(',')) return s.split(',').map(t => t.trim()).filter(Boolean).map(x => ({ id: String(x), text: String(x) }));
    if ((s.startsWith('[') && s.endsWith(']')) || (s.startsWith('{') && s.endsWith('}'))) {
      try { return normMulti(JSON.parse(s), idKey, textKey); } catch (_) { }
    }
  }
  const single = toPair(v);
  return single ? [single] : [];
}

function ensureOption($sel, value, text) {
  if (value == null || value === '') return;
  const v = String(value);
  if ($sel.find('option[value="' + v + '"]').length === 0) {
    $sel.append(new Option(text ?? v, v, false, false));
  }
}

function setSelectMultiplePairs($sel, pairs) {
  const ids = [];
  pairs.forEach(p => {
    if (!p) return;
    ensureOption($sel, p.id, p.text);
    ids.push(p.id);
  });
  if ($sel.prop('multiple')) {
    $sel.val(ids).trigger('change');
  } else {
    const firstId = ids.length ? String(ids[0]) : '';
    $sel.val(firstId || null).trigger('change');
  }
}

function parseTagsAny(raw){
  if (!raw) return [];
  if (Array.isArray(raw)) return raw.map(s => String(s).trim()).filter(Boolean);
  const s = String(raw).trim();
  if (!s) return [];
  if (s.startsWith('[') && s.endsWith(']')) {
    try { const p = JSON.parse(s); if (Array.isArray(p)) return p.map(x => String(x).trim()).filter(Boolean); } catch(e){}
  }
  return s.split(',').map(x => x.trim()).filter(Boolean);
}

function setTagsChoices(tagsArr){
  try{
    if (typeof tagsInput.removeActiveItems === 'function') tagsInput.removeActiveItems();
    if (typeof tagsInput.clearInput === 'function') tagsInput.clearInput();
    const payload = (tagsArr || []).map(t => String(t).trim()).filter(Boolean).map(t => ({ value: t.toUpperCase(), label: t.toUpperCase() }));
    tagsInput.setValue(payload);
  }catch(e){}
}

function parseArchivosAny(raw){
  if (!raw) return [];
  if (Array.isArray(raw)) return raw;
  const s = String(raw).trim();
  if (!s) return [];
  if (s.startsWith('[') && s.endsWith(']')) {
    try { return JSON.parse(s) || []; } catch(e){ return []; }
  }
  if (s.includes(',')) return s.split(',').map(x => x.trim()).filter(Boolean);
  return [s];
}

function fileUrlAdmin(path){
  if(!path) return '';
  const p = String(path).replace(/\\/g,'/').trim();
  if(/^https?:\/\//i.test(p)) return p;
  if(p.startsWith('../')) return p;
  if(p.startsWith('admin/')) return '../' + p;
  if(p.startsWith('/admin/')) return '..' + p;
  return '../admin/' + p.replace(/^\/+/, '');
}

// ✅ Cargar archivos al dropzone visualizando imágenes
function preloadDropzoneFiles(rawFiles){
  const dz = window.myDropzone;
  if (!dz) return;

  const files = parseArchivosAny(rawFiles);

  files.forEach((fileObj, idx) => {
    // Evalúa si es un string o un objeto {archivo: "ruta", tipo: "imagen"}
    const path = typeof fileObj === 'string' ? fileObj : (fileObj.archivo || fileObj.url || '');
    if(!path) return;

    const url = fileUrlAdmin(path);
    const esImagen = (fileObj.tipo === 'imagen') || /\.(png|jpe?g|webp|gif)$/i.test(url);

    const mockFile = {
      name: path.split('/').pop() || `archivo_${idx+1}`,
      size: 12345,
      type: esImagen ? 'image/*' : 'application/pdf',
      accepted: true,
      status: Dropzone.SUCCESS,
      existing: true,
      id_archivo_producto: fileObj.id_archivo_producto || null
    };

    dz.emit("addedfile", mockFile);
    if (esImagen) dz.emit("thumbnail", mockFile, url);
    dz.emit("complete", mockFile);

    // Control de eliminación de archivos existentes
    const $preview = $(mockFile.previewElement);
    $preview.find('[data-dz-remove], .dz-remove-galeria').off('click').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if(mockFile.id_archivo_producto){
            eliminarArchivoGaleria(mockFile.id_archivo_producto, mockFile);
        } else {
            dz.removeFile(mockFile);
        }
    });
  });
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

/* ==============================
   ✅ READY & INICIALIZACIÓN
================================= */
$(document).ready(function () {

  showVehiculoFormLoading();

  initTinyMCEIfNeeded();

  $("#condicion").select2({theme: 'bootstrap-5'});
  $("#duenio").select2({theme: 'bootstrap-5'});
  $("#provincia").select2({theme: 'bootstrap-5'});
  $("#canton").select2({theme: 'bootstrap-5'});

  tagsInput = new Choices('#tags', {
    removeItemButton: true,
    placeholder: false,
    maxItemCount: 10,
    addItemText: (value) => `Presiona Enter para añadir <b>"${value}"</b>`,
    maxItemText: (maxItemCount) => `Solo ${maxItemCount} tags pueden ser añadidos`,
  });

  // Consultas AJAX de catálogos
  $.get('../api/v1/fulmuv/getReferencias/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      const bloqueadas = new Set(getReferenciasPermitidas());
      d.data.forEach(ref => {
        const refNormalizada = normalizarTextoVehiculo(ref);
        if (!bloqueadas.has(refNormalizada)) {
          $("#referencia").append(`<option value="${ref}">${ref}</option>`);
        }
      });
      if (vehiculoEditData) {
        const refs = safeArr(vehiculoEditData.referencias).map(sanitizarReferenciaVisible).filter(Boolean);
        setSelect2ValueByText($("#referencia"), refs);
        syncEditModeloSelection();
      }
    }
  });

  $.get('../api/v1/fulmuv/getTipoVendedor/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      d.data.forEach(v => $("#tipo_vendedor").append(`<option value="${v.id_tipo_vendedor}">${v.nombre}</option>`));
      $("#tipo_vendedor").select2({theme: 'bootstrap-5'});
      if (vehiculoEditData) aplicarSeleccionMultipleSiExiste($("#tipo_vendedor"), vehiculoEditData.tipo_vendedor);
    }
  });

  $.get('../api/v1/fulmuv/getTransmision/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      d.data.forEach(t => $("#transmision").append(`<option value="${t.id_transmision}">${t.nombre}</option>`));
      $("#transmision").select2({theme: 'bootstrap-5'});
      if (vehiculoEditData) aplicarSeleccionMultipleSiExiste($("#transmision"), vehiculoEditData.transmision);
    }
  });

  $.get('../api/v1/fulmuv/getColores/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      d.data.forEach(c => $("#color").append(`<option value="${c.id_color}">${c.nombre}</option>`));
      $("#color").select2({theme: 'bootstrap-5'});
      if (vehiculoEditData) aplicarSeleccionSimpleSiExiste($("#color"), vehiculoEditData.color);
    }
  });

  $.get('../api/v1/fulmuv/getTapiceria/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      d.data.forEach(t => $("#tapiceria").append(`<option value="${t.id_tapiceria}">${t.nombre}</option>`));
      $("#tapiceria").select2({theme: 'bootstrap-5'});
      if (vehiculoEditData) aplicarSeleccionMultipleSiExiste($("#tapiceria"), vehiculoEditData.tapiceria);
    }
  });

  $.get('../api/v1/fulmuv/getDireccion/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      d.data.forEach(dir => $("#direccion").append(`<option value="${dir.id_direccion}">${dir.nombre}</option>`));
      $("#direccion").select2({theme: 'bootstrap-5'});
      if (vehiculoEditData) aplicarSeleccionMultipleSiExiste($("#direccion"), vehiculoEditData.direccion);
    }
  });

  $.get('../api/v1/fulmuv/getClimatizacion/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      d.data.forEach(c => $("#climatizacion").append(`<option value="${c.id_climatizacion}">${c.nombre}</option>`));
      $("#climatizacion").select2({theme: 'bootstrap-5'});
      if (vehiculoEditData) aplicarSeleccionMultipleSiExiste($("#climatizacion"), vehiculoEditData.climatizacion);
    }
  });

  $.get('../api/v1/fulmuv/tiposAuto/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      const tiposBloqueados = new Set(['UTV', 'ATV', 'CAMIONES Y PESADOS', 'CAMIONES', 'PESADOS'].map(normalizarTextoVehiculo));
      tipos_auto = (d.data || []).filter(t => !tiposBloqueados.has(normalizarTextoVehiculo(t.nombre)));
      $("#tipo_vehiculo").empty().append(placeholderOption());
      tipos_auto.forEach(t => $("#tipo_vehiculo").append(`<option value="${t.id_tipo_auto}">${t.nombre}</option>`));
      $("#tipo_vehiculo").select2({
        theme: 'bootstrap-5', tags: true, placeholder: 'Seleccione....', allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (!term) return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#tipo_vehiculo'), {
        entity:'tipos_auto', label:'Subtipo',
        parents: function() { return { referencia: $('#referencia').val() || '' }; },
        onCreated: function(id, txt, parents) {
          tipos_auto.push({ id_tipo_auto: id, nombre: txt, referencia: parents.referencia || '' });
        }
      });
      if (vehiculoEditData) aplicarSeleccionSimpleSiExiste($("#tipo_vehiculo"), vehiculoEditData.tipo_auto);
    }
  });

  $.get('../api/v1/fulmuv/marcas/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      marcas = d.data;
      $("#marca").empty().append(placeholderOption());
      d.data.forEach(m => $("#marca").append(`<option value="${m.id_marca}">${m.nombre}</option>`));
      $("#marca").select2({
        theme: 'bootstrap-5', tags: true, placeholder: 'Seleccione....', allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (!term) return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#marca'), {
        entity:'marcas', label:'Marca',
        parents: function() { return { referencia: $('#referencia').val() || '' }; },
        onCreated: function(id, txt, parents) {
          marcas.push({ id_marca: id, nombre: txt, referencia: parents.referencia || '' });
        }
      });
      if (vehiculoEditData) {
        aplicarSeleccionSimpleSiExiste($("#marca"), vehiculoEditData.id_marca);
        syncEditModeloSelection();
      }
    }
  });

  $.get('../api/v1/fulmuv/tipo_tracccion/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      traccion = d.data;
      d.data.forEach(t => $("#traccion").append(`<option value="${t.id_tipo_traccion}">${t.nombre}</option>`));
      $("#traccion").select2({
        theme: 'bootstrap-5', tags: true, placeholder: 'Seleccione....', allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (!term) return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#traccion'), {
        entity:'tipo_traccion', label:'Tracción',
        parents: function() { return { referencia: $('#referencia').val() || '' }; },
        onCreated: function(id, txt, parents) {
          traccion.push({ id_tipo_traccion: id, nombre: txt, referencia: parents.referencia || '' });
        }
      });
      if (vehiculoEditData) aplicarSeleccionSimpleSiExiste($("#traccion"), vehiculoEditData.tipo_traccion);
    }
  });

  $.get('../api/v1/fulmuv/getFuncionamientoMotor/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      motor = d.data;
      d.data.forEach(m => $("#motor").append(`<option value="${m.id_funcionamiento_motor}">${m.nombre}</option>`));
      $("#motor").select2({
        theme: 'bootstrap-5', tags: true, placeholder: 'Seleccione....', allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (!term) return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#motor'), {
        entity:'funcionamiento_motor', label:'Funcionamiento de motor',
        parents: function() { return { referencia: $('#referencia').val() || '' }; },
        onCreated: function(id, txt, parents) {
          motor.push({ id_funcionamiento_motor: id, nombre: txt, referencia: parents.referencia || '' });
        }
      });
      if (vehiculoEditData) aplicarSeleccionSimpleSiExiste($("#motor"), vehiculoEditData.funcionamiento_motor);
    }
  });

  $.get('../api/v1/fulmuv/modelosAutos/', {}, function (r) {
    const d = parseAjaxJSON(r);
    if (!d.error) {
      modelosCatalogo = d.data || [];
      inicializarModeloSelect();
      if (vehiculoEditData) {
        syncEditModeloSelection();
      }
    }
  });

  // ✅ DROPZONE
  $("#myAwesomeDropzone").attr("data-dropzone", 'data-dropzone');
  window.myDropzone = new Dropzone("#myAwesomeDropzone", {
    url: "#",
    acceptedFiles: "image/*,application/pdf",
    previewsContainer: document.querySelector(".dz-preview"),
    previewTemplate: document.querySelector(".dz-preview").innerHTML,
    init: function () {
      $("#file-previews").empty();
      this.on("addedfile", function (file) {
        let pdfFileCount = this.files.filter(f => f.type === "application/pdf").length;
        if (pdfFileCount > 1) {
          this.removeFile(file);
          toastr.warning("Solo se permite un archivo PDF!");
        }
      });
    }
  });

  // Empresas Admin
  if ($("#id_rol_principal").val() == 1) {
    $.get('../api/v1/fulmuv/empresas/', {}, function (r) {
      const d = parseAjaxJSON(r);
      if (!d.error) d.data.forEach(e => $("#lista_empresas").append(`<option value="${e.id_empresa}">${e.nombre}</option>`));
    });
  } else {
    $("#searh_empresa").empty();
  }

  // ✅ DEPENDENCIA REFERENCIA -> MARCA / SUBTIPO / TRACCIÓN / MOTOR / MODELO
  $("#referencia").on("change", function () {
    if (isEditLoading) return; // Evitar reset al cargar
    actualizarSelectsByReferencia($(this).val()); // filtra marcas y demás selects
    buscarModelosReferencia();
  });

  $("#marca").on("change", function () {
    if (isEditLoading) return;
    buscarModelosReferencia();
  });

  // ✅ Propagar subtipo/tracción/motor al modelo seleccionado (solo campos vacíos)
  $('#tipo_vehiculo, #traccion, #motor').on('change', function () {
    if (isEditLoading) return;
    propagarRelacionModelo();
  });

  // Iniciar edición si hay ID
  const idVehiculoEdit = Number($("#id_vehiculo").val() || 0);
  if (idVehiculoEdit > 0) {
    cargarVehiculoParaEditar(idVehiculoEdit);
  } else {
    hideVehiculoFormLoading(350);
  }
});

/* ==============================
   ✅ CANTONES
================================= */
function cargarCantones(provincia) {
  const cantonSelect = document.getElementById("canton");
  cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';

  if (provincia && cantones[provincia]) {
    cantones[provincia].forEach(c => {
      const option = document.createElement("option");
      option.value = c;
      option.textContent = c;
      cantonSelect.appendChild(option);
    });
  }
}

/* ==============================
   ✅ EDITAR VEHÍCULO (CARGA DE DATOS)
================================= */
function cargarVehiculoParaEditar(id){
  $.ajax({
    url: '../api/v1/fulmuv/vehiculos/byIdVehiculo',
    type: 'POST',
    data: { id_vehiculo: id },
    dataType: 'json',
    success: function(r){
      let v = null;
      if (Array.isArray(r)) v = r[0] || null;
      else if (r && typeof r === 'object') v = r.data ? r.data : r;

      if (!v || !v.id_vehiculo) {
        SweetAlert("error", "No se encontró el vehículo.");
        return;
      }

      vehiculoEditData = v;
      isEditLoading = true; // Activar seguro

      $("#precio_referencia").val(v.precio_referencia || '');
      $("#descuento").val(v.descuento || 0);
      $("#iva").prop("checked", String(v.iva) === "1");
      $("#negociable").prop("checked", String(v.negociable) === "1");
      $("#anio").val(v.anio || '');
      $("#kilometraje").val(v.kilometraje || '');
      $("#inicio_placa").val(v.inicio_placa || '');
      $("#fin_placa").val(v.fin_placa || '');
      $("#cilindraje").val(v.cilindraje || '');

      setDescripcionHTML(v.descripcion || '');

      // Provincia y Cantón
      const provArr = safeArr(v.provincia);
      const cantArr = safeArr(v.canton);
      $("#provincia").val(provArr).trigger("change");
      cargarCantones(provArr[0] || '');
      setTimeout(() => { $("#canton").val(cantArr).trigger("change"); }, 0);

      // Selects Básicos
      $("#color").val(String(v.color || '')).trigger("change");
      $("#condicion").val(safeArr(v.condicion)).trigger("change");
      $("#tipo_vendedor").val(safeArr(v.tipo_vendedor)).trigger("change");
      $("#transmision").val(safeArr(v.transmision)).trigger("change");
      $("#tapiceria").val(safeArr(v.tapiceria)).trigger("change");
      $("#duenio").val(safeArr(v.tipo_dueno)).trigger("change");
      $("#direccion").val(safeArr(v.direccion)).trigger("change");
      $("#climatizacion").val(safeArr(v.climatizacion)).trigger("change");

      // Tags
      const tagsArr = parseTagsAny(v.tags);
      setTagsChoices(tagsArr);

      // ✅ Cargar Referencia -> filtrar selects dependientes -> Marca -> Modelos
      const refs = safeArr(v.referencias).map(sanitizarReferenciaVisible).filter(Boolean);
      if (refs.length) {
        setSelect2ValueByText($("#referencia"), refs[0]);
        // Filtrar opciones de marca / subtipo / tracción / motor según el tipo de vehículo.
        // keepValues=true: solo re-puebla opciones sin resetear; las selecciones se aplican a continuación.
        actualizarSelectsByReferencia(refs[0], true);
      }
      aplicarSeleccionSimpleSiExiste($("#marca"), String(v.id_marca || ''));
      syncEditModeloSelection();

      $("#tipo_vehiculo").val(String(v.tipo_auto || '')).trigger("change");
      $("#traccion").val(String(v.tipo_traccion || '')).trigger("change");
      $("#motor").val(String(v.funcionamiento_motor || '')).trigger("change");

      // Imágenes previsualización
      if (v.img_frontal) {
        $("#img_frontal_old").val(v.img_frontal);
        $("#preview_frontal").attr("src", fileUrlAdmin(v.img_frontal)).show();
      }
      if (v.img_posterior) {
        $("#img_posterior_old").val(v.img_posterior);
        $("#preview_posterior").attr("src", fileUrlAdmin(v.img_posterior)).show();
      }

      if (!$("#img_frontal_actual").length) {
        $('<input type="hidden" id="img_frontal_actual">').appendTo('body');
        $('<input type="hidden" id="img_posterior_actual">').appendTo('body');
      }
      $("#img_frontal_actual").val(v.img_frontal || "");
      $("#img_posterior_actual").val(v.img_posterior || "");
      $("#img_frontal, #img_posterior").prop("required", false);

      // Archivos Galería a Dropzone
      preloadDropzoneFiles(v.archivos);

      $("#btnGuardarVehiculo").text("Actualizar vehículo");

      // Apagar seguro y forzar la carga final del modelo
      setTimeout(() => {
        isEditLoading = false;
        forzarCargaModeloEdicion();
      }, 500);
      hideVehiculoFormLoading(700);
    }
  });
}

function syncDescripcionAntesDeGuardar() {
  const editor = window.tinymce?.get('descripcion');
  if (editor) {
    editor.save();
    const html = editor.getContent() || '';
    $("#descripcion").val(html);
    return emojiToEntities(html);
  }

  const fallback = $("#descripcion").val() || window._descPendingHTML || '';
  $("#descripcion").val(fallback);
  return emojiToEntities(fallback);
}

function setSelect2ValueByText($select, values) {
  if (!values) return;
  let arr = Array.isArray(values) ? values : [values];
  const isMultiple = $select.prop("multiple");
  if (!isMultiple && arr.length) arr = [arr[0]];

  arr.forEach(v => {
    const val = String(v).toUpperCase().trim();
    if (!val) return;
    if ($select.find(`option[value="${CSS.escape(val)}"]`).length === 0) {
      $select.append(new Option(val, val, true, true));
    }
  });
  $select.val(arr.map(x => String(x).toUpperCase().trim())).trigger("change");
}

/* ==============================
   ✅ MODELOS POR REFERENCIA
================================= */
function buscarModelosReferencia(referenciaParam = null, modelosPairs = []){
  let referencia = referenciaParam ?? $("#referencia").val();
  if (Array.isArray(referencia)) referencia = referencia[0] || '';
  referencia = (referencia || '').toString().trim();

  if (!referencia || !$("#marca").val()) {
    const $sel = $("#modelo");
    if (!$sel.hasClass('select2-hidden-accessible')) {
      inicializarModeloSelect();
    }
    $sel.empty().append(placeholderOption()).prop('disabled', true).val(null).trigger('change');
    return;
  }

  actualizarModelosDisponibles(modelosPairs);
}

function asignarModelo(){
  const id_modelo = $("#modelo").val();
  if (!id_modelo || id_modelo === "nuevo") return;

  $.get('../api/v1/fulmuv/getModeloById/' + id_modelo, {}, function (returnedData) {
    const r = (typeof returnedData === 'string') ? JSON.parse(returnedData) : returnedData;
    if (r.error || !r.data) return;

    const d = r.data;
    seleccionarValorFiltrado($("#tipo_vehiculo"), d.id_tipo_auto);
    seleccionarValorFiltrado($("#traccion"), d.id_tipo_traccion);
    seleccionarValorFiltrado($("#motor"), d.id_funcionamiento_motor);
  });
}

/* ==============================
   ✅ GUARDADO Y VALIDACIONES
================================= */
function guardarVehiculo() {
  const idVehiculoEdit = Number($("#id_vehiculo").val() || 0);
  if (!idVehiculoEdit) {
    verificarMembresiaYGuardar();
  } else {
    actualizarVehiculo(idVehiculoEdit);
  }
}

function addProducto() {
  let tags = (tagsInput.getValue(true) || []).map(t => String(t).toUpperCase());
  let descripcion = syncDescripcionAntesDeGuardar();

  const payload = getPayloadBasico(tags, descripcion);
  if (!validarCamposObligatorios()) return;

  setBtnLoading("#btnGuardarVehiculo", "Registrando...");

  const dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");
  const files = dropzoneInstance.getAcceptedFiles();

  subirImagenesPrincipales().then(imagenes => {
    saveFiles(files).then(archivos => {
      payload.img_frontal = imagenes.img_frontal;
      payload.img_posterior = imagenes.img_posterior;
      payload.archivos = archivos || [];

      $.post('../api/v1/fulmuv/vehiculos/create', payload, function (returnedData) {
        const returned = (typeof returnedData === 'string') ? JSON.parse(returnedData) : returnedData;
        if (!returned.error) SweetAlert("url_success", returned.msg, "crear_vehiculo.php");
        else SweetAlert("error", returned.msg || "Ocurrió un error.");
        resetBtnLoading("#btnGuardarVehiculo");
      });
    });
  }).catch(() => resetBtnLoading("#btnGuardarVehiculo"));
}

function actualizarVehiculo(id_vehiculo) {
  let tags = (tagsInput.getValue(true) || []).map(t => String(t).toUpperCase());
  let descripcion = syncDescripcionAntesDeGuardar();

  const payload = getPayloadBasico(tags, descripcion);
  payload.id_vehiculo = id_vehiculo;

  if (!validarCamposObligatorios()) return;

  setBtnLoading("#btnGuardarVehiculo", "Actualizando...");
  const dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");

  subirImagenesPrincipalesEdit().then(imagenes => {
    saveFilesEdit(dropzoneInstance).then(archivosNuevos => {
      payload.img_frontal = imagenes.img_frontal;
      payload.img_posterior = imagenes.img_posterior;
      payload.archivos = archivosNuevos || [];

      $.post('../api/v1/fulmuv/vehiculos/update_full', payload, function (returnedData) {
        const returned = (typeof returnedData === 'string') ? JSON.parse(returnedData) : returnedData;
        if (!returned.error) SweetAlert("url_success", returned.msg || "Vehículo actualizado.", "vehiculos.php");
        else SweetAlert("error", returned.msg || "Ocurrió un error al actualizar.");
        resetBtnLoading("#btnGuardarVehiculo");
      });
    });
  }).catch(() => resetBtnLoading("#btnGuardarVehiculo"));
}

function getPayloadBasico(tags, descripcion) {
  return {
    descripcion,
    provincia: $("#provincia").val(),
    canton: $("#canton").val(),
    tags: tags.join(', '),
    precio_referencia: $("#precio_referencia").val(),
    descuento: $("#descuento").val(),
    tipo_vehiculo: $("#tipo_vehiculo").val(),
    referencias: [$("#referencia").val()],
    modelo: $("#modelo").val(),
    marca: $("#marca").val(),
    traccion: $("#traccion").val(),
    iva: $("#iva").is(":checked") ? 1 : 0,
    negociable: $("#negociable").is(":checked") ? 1 : 0,
    anio: $("#anio").val(),
    condicion: $("#condicion").val(),
    tipo_vendedor: $("#tipo_vendedor").val(),
    kilometraje: $("#kilometraje").val(),
    transmision: $("#transmision").val(),
    inicio_placa: $("#inicio_placa").val(),
    fin_placa: $("#fin_placa").val(),
    color: $("#color").val(),
    cilindraje: $("#cilindraje").val(),
    tapiceria: $("#tapiceria").val(),
    duenio: $("#duenio").val(),
    direccion: $("#direccion").val(),
    climatizacion: $("#climatizacion").val(),
    motor: $("#motor").val(),
    tipo_creador: tipo_user,
    id_empresa: ($("#id_rol_principal").val() == 1) ? $("#lista_empresas").val() : $("#id_empresa").val()
  };
}

/* ==============================
   ✅ UPLOADS DE ARCHIVOS
================================= */
function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    if (!files.length) { resolve([]); return; }
    const formData = new FormData();
    files.forEach(function (file) { formData.append(`archivos[]`, file); });
    $.ajax({
      type: 'POST', data: formData, url: '../admin/cargar_imagen_multiple.php',
      cache: false, contentType: false, processData: false,
      success: function (res) {
        if (res.response == "success") resolve(res.data);
        else { SweetAlert("error", "Error al guardar archivos."); reject(); }
      }
    });
  });
}

function saveFilesEdit(dropzoneInstance) {
  return new Promise(function (resolve, reject) {
    const nuevos = (dropzoneInstance.files || []).filter(f => !f.existing);
    if (!nuevos.length) { resolve([]); return; }
    const formData = new FormData();
    nuevos.forEach(file => formData.append(`archivos[]`, file));
    $.ajax({
      type: 'POST', data: formData, url: '../admin/cargar_imagen_multiple.php',
      cache: false, contentType: false, processData: false,
      success: function (res) {
        if (res.response == "success") resolve(res.data);
        else { SweetAlert("error", "Error al guardar archivos nuevos."); reject(); }
      },
      error: reject
    });
  });
}

function subirImagenesPrincipales() {
  return new Promise(function (resolve, reject) {
    const f = document.getElementById('img_frontal').files[0];
    const p = document.getElementById('img_posterior').files[0];
    if (!f || !p) { SweetAlert("error", "Falta imagen frontal o posterior."); reject(); return; }
    
    const formData = new FormData();
    formData.append('img_frontal', f); formData.append('img_posterior', p);
    $.ajax({
      url: '../admin/cargar_imagenes_frontales.php', method: 'POST', data: formData,
      processData: false, contentType: false,
      success: function (res) {
        if (res.response === "success") resolve(res.data);
        else { SweetAlert("error", res.error); reject(); }
      },
      error: () => { SweetAlert("error", "Error de red."); reject(); }
    });
  });
}

function subirImagenesPrincipalesEdit() {
  return new Promise(function (resolve, reject) {
    const imgF = document.getElementById('img_frontal').files[0] || null;
    const imgP = document.getElementById('img_posterior').files[0] || null;
    const oldF = $('#img_frontal_actual').val() || '';
    const oldP = $('#img_posterior_actual').val() || '';

    if (!imgF && !imgP) {
      resolve({ img_frontal: oldF, img_posterior: oldP });
      return;
    }

    const formData = new FormData();
    if (imgF) formData.append('img_frontal', imgF);
    if (imgP) formData.append('img_posterior', imgP);

    $.ajax({
      url: '../admin/cargar_imagenes_frontales.php', method: 'POST', data: formData,
      processData: false, contentType: false,
      success: function (res) {
        if (res.response === "success") {
          resolve({
            img_frontal: res.data?.img_frontal || oldF,
            img_posterior: res.data?.img_posterior || oldP
          });
        } else { SweetAlert("error", res.error); reject(); }
      },
      error: () => { SweetAlert("error", "Error de red."); reject(); }
    });
  });
}

function eliminarArchivoGaleria(id_archivo_producto, mockFile) {
  Swal.fire({
    title: '¿Eliminar archivo?', text: 'Esta acción no se puede deshacer.',
    icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'No, cancelar'
  }).then((result) => {
    if (!result.isConfirmed) return;
    $.post('../api/v1/fulmuv/archivos_productos/delete', { id_archivo_producto }, function (r) {
        const resp = typeof r === 'string' ? JSON.parse(r) : r;
        if (!resp.error) {
          if (mockFile && window.myDropzone) window.myDropzone.removeFile(mockFile);
          Swal.fire('Eliminado', 'Archivo borrado.', 'success');
        } else Swal.fire('Error', 'No se pudo eliminar.', 'error');
    });
  });
}

/* ==============================
   ✅ OTROS
================================= */
function validarCamposObligatorios() {
  let ok = true;
  const errores = [];
  const req = document.querySelectorAll("input[required], select[required], textarea[required]");

  req.forEach(el => {
    const lbl = (document.querySelector(`label[for="${el.id}"]`)?.innerText || el.name || el.id).replace("*", "").trim();
    let v = true;

    if (el.tagName === "SELECT") v = el.multiple ? el.selectedOptions.length > 0 : !!el.value.trim();
    else if (el.type === "file") v = ($("#id_vehiculo").val() > 0 && (el.id==="img_frontal"||el.id==="img_posterior")) ? true : el.files.length > 0;
    else if (el.type === "checkbox") v = el.checked;
    else v = !!el.value.trim();

    if (!v) { ok = false; errores.push(`Falta: ${lbl}`); el.classList.add("is-invalid"); }
    else { el.classList.remove("is-invalid"); el.classList.add("is-valid"); }
  });

  if (document.querySelector("#descripcion")) {
    const ed = window.tinymce?.get("descripcion");
    if (ed) ed.save();
    const c = (ed ? ed.getContent({ format: "text" }) : ($("#descripcion").val() || window._descPendingHTML || '')).trim();
    if (!c) { ok = false; errores.push("Falta: Descripción"); }
  }
  if (!ok) SweetAlert("error", errores.join("\n"));
  return ok;
}

function verificarMembresiaYGuardar() {
  const id_empresa = ($("#id_rol_principal").val() == 1) ? $("#lista_empresas").val() : $("#id_empresa").val();
  $.get('../api/v1/fulmuv/validarMembresiaProductos/' + id_empresa + '/' + tipo_user, {
    modulo: 'vehiculo'
  }, function (data) {
    const res = typeof data === 'string' ? JSON.parse(data) : data;
    if (res.error) {
      swal({
        title: "Necesitas mejorar tu plan",
        text: `${res.msg}\n\n¿Deseas ir ahora a actualizar tu membresía?`,
        icon: "info",
        buttons: {
          cancel: { text: "Cancelar", visible: true, closeModal: true },
          confirm: { text: "Mejorar plan", value: true, closeModal: true }
        }
      }, function () {
        window.location.href = "upgrade_membresia.php?id_empresa=" + id_empresa;
      });
    }
    else addProducto();
  });
}

function initTinyMCEIfNeeded(){
  if (!window.tinymce || tinymce.get('descripcion')) return;
  tinymce.init({
    selector: '#descripcion', menubar: false, height: 260,
    plugins: 'lists link table code', toolbar: 'undo redo | bold italic | bullist numlist | link | code',
    setup: function (ed) {
      ed.on('init', function () {
        if (window._descPendingHTML !== null) { ed.setContent(window._descPendingHTML); window._descPendingHTML = null; }
      });
    }
  });
}

function setDescripcionHTML(html){
  window._descPendingHTML = html || '';
  if (window.tinymce && tinymce.get('descripcion')) tinymce.get('descripcion').setContent(window._descPendingHTML);
  else $('#descripcion').val(window._descPendingHTML);
}

function emojiToEntities(str) {
  try { return str.replace(/\p{Extended_Pictographic}/gu, (m) => Array.from(m).map(c => `&#${c.codePointAt(0)};`).join('')); } 
  catch (e) { return str; }
}

function setBtnLoading(s, t) {
  const $b = $(s);
  if (!$b.length) return;
  if (!$b.data("orig")) $b.data("orig", $b.html());
  $b.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm me-2"></span>${t}`);
}

function resetBtnLoading(s) {
  const $b = $(s);
  if ($b.data("orig")) $b.html($b.data("orig")).prop("disabled", false);
}

function swalConfirmV1(title, text, okText, cancelText, onOk, onCancel){
  swal({ title, text, icon: "info", buttons: { cancel: cancelText||"No", confirm: okText||"Sí" } }, function(c){ if(c && onOk) onOk(); else if(!c && onCancel) onCancel(); });
}

function ensureRemote(entity, nombre, parents){
  const payload = $.extend({ entity, nombre }, parents || {});
  return $.post('../api/v1/fulmuv/catalog/ensure', payload).then(r => {
      const res = typeof r === 'string' ? JSON.parse(r) : r;
      if (res.error) return $.Deferred().reject(res.msg).promise();
      return res.id;
  });
}

function wireSelectEnsure($el, cfg){
  $el.on('select2:opening', function(){ $(this).data('prev', $(this).val()); });
  $el.on('select2:select', function(e){
    const d = e.params.data || {};
    const val = d.id; const txt = (d.text || '').trim();
    if (!d.newTag && !(!d.element && !/^\d+$/.test(val)) && val !== 'nuevo') return;
    const parents = typeof cfg.parents === 'function' ? cfg.parents() : {};
    
    for (let k in parents){
      if (!parents[k] || +parents[k] <= 0){
        swal("Falta seleccionar", `Debes seleccionar primero para registrar ${cfg.label||cfg.entity}.`, "warning");
        $el.val($el.data('prev') || null).trigger('change'); return;
      }
    }

    swalConfirmV1(`Registrar ${cfg.label || cfg.entity}`, `¿Registrar "${txt}"?`, "Sí", "No",
      function(){
        ensureRemote(cfg.entity, txt, parents).then(id => {
          $el.find('option').filter(function(){ return $(this).val() == val; }).remove();
          $el.append(new Option(txt, id, true, true)).trigger('change');
          if (typeof cfg.onCreated === 'function') cfg.onCreated(id, txt, parents);
          swal("Listo", "Registrado correctamente.", "success");
        }).fail(msg => { swal("Error", msg, "error"); $el.val($el.data('prev')).trigger('change'); });
      },
      function(){ $el.val($el.data('prev')).trigger('change'); }
    );
  });
}
