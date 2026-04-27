/***********************
 * VARIABLES / DATA
 ***********************/
let tipos_auto    = [];
let marcas        = [];
let traccion      = [];
let motor         = [];
let modelos       = [];
let modelosCatalogoCMV = [];
let referencias   = [];
let vendedor      = [];
let climatizacion = [];
let direccion     = [];
let tapiceria     = [];
let colores       = [];
let transmision   = [];

let vehiculosEliminados = [];

const cantones = {
  "Azuay": ["Cuenca","Camilo Ponce Enríquez","Chordeleg","El Pan","Girón","Gualaceo","Nabón","Oña","Paute","Pucará","San Fernando","Santa Isabel","Sevilla de Oro","Sigsig"],
  "Bolívar": ["Guaranda","Chillanes","Chimbo","Echeandía","Las Naves","San Miguel"],
  "Cañar": ["Azogues","Biblián","Cañar","Déleg","El Tambo","La Troncal","Suscal"],
  "Carchi": ["Tulcán","Bolívar","Espejo","Mira","Montúfar","San Pedro de Huaca"],
  "Cotopaxi": ["Latacunga","La Maná","Pangua","Pujilí","Salcedo","Saquisilí","Sigchos"],
  "Chimborazo": ["Riobamba","Alausí","Chambo","Chunchi","Colta","Cumandá","Guamote","Guano","Pallatanga","Penipe"],
  "El Oro": ["Machala","Arenillas","Atahualpa","Balsas","Chilla","El Guabo","Huaquillas","Las Lajas","Marcabelí","Pasaje","Piñas","Portovelo","Santa Rosa","Zaruma"],
  "Esmeraldas": ["Esmeraldas","Atacames","Eloy Alfaro","Muisne","Quinindé","Rioverde","San Lorenzo"],
  "Guayas": ["Guayaquil","Alfredo Baquerizo Moreno","Balao","Balzar","Colimes","Daule","Durán","El Empalme","El Triunfo","General Antonio Elizalde","Isidro Ayora","Lomas de Sargentillo","Marcelino Maridueña","Milagro","Naranjal","Naranjito","Nobol","Palestina","Pedro Carbo","Playas","Salitre","Samborondón","Santa Lucía","Simón Bolívar","Yaguachi"],
  "Imbabura": ["Ibarra","Antonio Ante","Cotacachi","Otavalo","Pimampiro","San Miguel de Urcuquí"],
  "Loja": ["Loja","Calvas","Catamayo","Celica","Chaguarpamba","Espíndola","Gonzanamá","Macará","Olmedo","Paltas","Pindal","Puyango","Quilanga","Saraguro","Sozoranga","Zapotillo"],
  "Los Ríos": ["Babahoyo","Baba","Buena Fe","Mocache","Montalvo","Palenque","Puebloviejo","Quevedo","Quinsaloma","Urdaneta","Valencia","Ventanas","Vinces"],
  "Manabí": ["Portoviejo","Bolívar","Chone","El Carmen","Flavio Alfaro","Jama","Jaramijó","Jipijapa","Junín","Manta","Montecristi","Olmedo","Paján","Pedernales","Pichincha","Puerto López","Rocafuerte","Santa Ana","Sucre","Tosagua","Veinticuatro de Mayo"],
  "Morona Santiago": ["Morona","Gualaquiza","Huamboya","Limón Indanza","Logroño","Pablo Sexto","Palora","San Juan Bosco","Sucúa","Taisha","Tiwintza"],
  "Napo": ["Tena","Archidona","Carlos Julio Arosemena Tola","El Chaco","Quijos"],
  "Pastaza": ["Puyo","Arajuno","Mera","Santa Clara"],
  "Pichincha": ["Quito","Cayambe","Mejía","Pedro Moncayo","Pedro Vicente Maldonado","Puerto Quito","Rumiñahui","San Miguel de Los Bancos"],
  "Tungurahua": ["Ambato","Baños de Agua Santa","Cevallos","Mocha","Patate","Quero","San Pedro de Pelileo","Santiago de Píllaro","Tisaleo"],
  "Zamora Chinchipe": ["Zamora","Centinela del Cóndor","Chinchipe","El Pangui","Nangaritza","Palanda","Paquisha","Yacuambi","Yantzaza"],
  "Galápagos": ["San Cristóbal","Isabela","Santa Cruz"],
  "Sucumbíos": ["Nueva Loja","Cascales","Cuyabeno","Gonzalo Pizarro","Putumayo","Shushufindi","Sucumbíos","Lago Agrio"],
  "Orellana": ["Francisco de Orellana","Aguarico","La Joya de Los Sachas","Loreto"],
  "Santo Domingo de los Tsáchilas": ["Santo Domingo"],
  "Santa Elena": ["Santa Elena","La Libertad","Salinas"]
};

const container = document.getElementById("vehiculosContainer");

/***********************
 * EVENTOS BOTONES
 ***********************/
document.getElementById("btnAddRow").addEventListener("click", () => {
  addRow();
  actualizarNumerosFilas();
});

document.getElementById("btnClear").addEventListener("click", () => {
  container.querySelectorAll(".veh-card").forEach(card => {
    if (card.dataset.id_vehiculo) vehiculosEliminados.push(card.dataset.id_vehiculo);
  });
  container.innerHTML = "";
  addRow();
});

document.getElementById("btnGuardarBorrador").addEventListener("click", () => guardarMasivo("P"));
document.getElementById("btnPublicar").addEventListener("click",        () => guardarMasivo("A"));

document.getElementById("buscadorBorrador").addEventListener("input", function () {
  const q = normTxt(this.value);
  container.querySelectorAll(".veh-card").forEach(card => {
    if (!q) { card.style.display = ""; return; }
    const ref    = normTxt(card.querySelector(".referencias")?.value || "");
    const marca  = normTxt(card.querySelector(".id_marca option:checked")?.textContent || "");
    const modelo = normTxt(card.querySelector(".id_modelo option:checked")?.textContent || "");
    const tags   = normTxt(card.querySelector(".tags")?.value || "");
    const desc   = normTxt(card.querySelector(".descripcion")?.value || "");
    const anio   = normTxt(card.querySelector(".anio")?.value || "");
    card.style.display = `${ref} ${marca} ${modelo} ${tags} ${desc} ${anio}`.includes(q) ? "" : "none";
  });
});

/***********************
 * INIT
 ***********************/
document.addEventListener("DOMContentLoaded", async () => {
  await cargarCombos();
  await cargarBorradorVehiculos();
  if (!container.querySelectorAll(".veh-card").length) addRow();
});

/***********************
 * HELPERS BÁSICOS
 ***********************/
async function fetchJSON(url) {
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    if (Array.isArray(json)) return json;
    if (Array.isArray(json.data)) return json.data;
    if (json.data && Array.isArray(json.data.data)) return json.data.data;
    return [];
  } catch (e) { console.error("fetchJSON:", e, url); return []; }
}

function optionList(arr, valueKey, textKey) {
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach(x => html += `<option value="${x[valueKey]}">${x[textKey]}</option>`);
  return html;
}

function optionListStrings(arr) {
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach(s => html += `<option value="${s}">${s}</option>`);
  return html;
}

function safeParseJSON(v) {
  if (!v) return [];
  if (Array.isArray(v)) return v;
  try { const p = JSON.parse(v); return Array.isArray(p) ? p : []; } catch { return []; }
}

function setSelectSingle(selectEl, value) {
  if (!selectEl) return;
  selectEl.value = String(value ?? "");
  if (window.$ && $.fn.select2) $(selectEl).trigger("change.select2");
}

function isEmptyStr(v) { return !String(v ?? "").trim(); }

function postJQ(url, data) {
  return new Promise((resolve, reject) => {
    $.post(url, data, r => resolve(r)).fail(xhr => reject(new Error(xhr.responseText || "Error POST")));
  });
}

/***********************
 * NORMALIZACIÓN Y FILTRADO
 ***********************/
function normTxt(v) {
  return String(v ?? "").trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

// Idéntico a filtrarPorReferencia en crear_vehiculo.js
function filtrarPorRefCMV(lista, referencia) {
  if (!referencia) return lista || [];
  const refNorm = normTxt(String(referencia));
  return (lista || []).filter(item => {
    const refStr = String(item.referencia || "").trim();
    if (!refStr) return true;
    return refStr.split(",").map(r => normTxt(r.trim())).includes(refNorm);
  });
}

/***********************
 * ORDEN N/A Y VARIOS
 ***********************/
function orderNAandVariosFirstObjects(arr, textKey = "nombre") {
  const TOP = new Set(["N/A","NA","NO APLICA","VARIOS"]);
  return (arr || []).slice().sort((a, b) => {
    const A = normTxt(a?.[textKey]), B = normTxt(b?.[textKey]);
    const aTop = TOP.has(A), bTop = TOP.has(B);
    if (aTop && !bTop) return -1; if (!aTop && bTop) return 1;
    const pri = x => x==="N/A"||x==="NA"||x==="NO APLICA" ? 0 : x==="VARIOS" ? 1 : 2;
    if (pri(A) !== pri(B)) return pri(A) - pri(B);
    return A.localeCompare(B, "es");
  });
}

function orderNAandVariosFirstStrings(arr) {
  const TOP = new Set(["N/A","NA","NO APLICA","VARIOS"]);
  return (arr || []).slice().sort((a, b) => {
    const A = normTxt(a), B = normTxt(b);
    const aTop = TOP.has(A), bTop = TOP.has(B);
    if (aTop && !bTop) return -1; if (!aTop && bTop) return 1;
    const pri = x => x==="N/A"||x==="NA"||x==="NO APLICA" ? 0 : x==="VARIOS" ? 1 : 2;
    if (pri(A) !== pri(B)) return pri(A) - pri(B);
    return A.localeCompare(B, "es");
  });
}

/***********************
 * CARGAR COMBOS
 ***********************/
async function cargarCombos() {
  const BLOQUEADOS = new Set(["UTV","UTVS","ATV","ATVS","CAMIONES Y PESADOS","CAMIONES","PESADOS"].map(normTxt));

  const rawTipos  = await fetchJSON("../api/v1/fulmuv/tiposAuto/");
  tipos_auto      = orderNAandVariosFirstObjects(
    rawTipos.filter(t => !BLOQUEADOS.has(normTxt(t.nombre))), "nombre"
  );

  marcas          = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/marcas/"),                      "nombre");
  traccion        = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tipo_tracccion/"),               "nombre");
  motor           = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getFuncionamientoMotor/"),       "nombre");
  const REFS_BLOQUEADAS = new Set(["UTV","UTVS","ATV","ATVS","CAMIONES Y PESADOS","CAMIONES","PESADOS"].map(normTxt));
  referencias     = orderNAandVariosFirstStrings(
    (await fetchJSON("../api/v1/fulmuv/getReferencias/")).filter(r => !REFS_BLOQUEADAS.has(normTxt(r)))
  );
  vendedor        = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getTipoVendedor/"),              "nombre");
  climatizacion   = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getClimatizacion/"),            "nombre");
  direccion       = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getDireccion/"),                "nombre");
  tapiceria       = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getTapiceria/"),                "nombre");
  colores         = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getColores/"),                  "nombre");
  transmision     = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getTransmision/"),              "nombre");
  modelosCatalogoCMV = await fetchJSON("../api/v1/fulmuv/modelosAutos/");
}

/***********************
 * PROVINCIA -> CANTONES
 ***********************/
function cargarCantonesFila(card, provincia) {
  const sel = card.querySelector("select.canton");
  if (!sel) return;
  const prev = $(sel).val();
  sel.innerHTML = `<option value="">Elija un cantón</option>`;
  if (provincia && cantones[provincia]) {
    cantones[provincia].forEach(c => {
      const opt = document.createElement("option");
      opt.value = c; opt.textContent = c;
      sel.appendChild(opt);
    });
  }
  if (prev) $(sel).val(prev);
  if (window.$ && $.fn.select2) $(sel).trigger("change.select2");
}

/***********************
 * FILTRADO POR REFERENCIA (por tarjeta)
 * Repuebla: marca, tipo_auto, tipo_traccion, funcionamiento_motor
 * Si keepValues=false → resetea valores que ya no están en la lista filtrada
 ***********************/
function actualizarSelectsByReferenciaCard(card, referencia, keepValues) {
  const keep = keepValues === true;

  function rebuildSelect($el, lista, valueKey) {
    const prev = $el.val();
    $el.empty().append('<option value="">Seleccione...</option>');
    lista.forEach(item => $el.append(new Option(item.nombre, item[valueKey])));
    if (!keep) {
      const ok = prev && lista.some(i => String(i[valueKey]) === String(prev));
      $el.val(ok ? prev : null).trigger("change");
    }
  }

  rebuildSelect($(card).find(".id_marca"),             filtrarPorRefCMV(marcas,   referencia), "id_marca");
  rebuildSelect($(card).find(".tipo_auto"),            filtrarPorRefCMV(tipos_auto, referencia), "id_tipo_auto");
  rebuildSelect($(card).find(".tipo_traccion"),        filtrarPorRefCMV(traccion,  referencia), "id_tipo_traccion");
  rebuildSelect($(card).find(".funcionamiento_motor"), filtrarPorRefCMV(motor,     referencia), "id_funcionamiento_motor");
}

/***********************
 * MODELOS: cargar por referencia + marca
 ***********************/
function buscarModelosReferenciaCard(card) {
  if (card.dataset.isloading === "1") return;

  const ref     = card.querySelector(".referencias")?.value  || "";
  const marcaId = card.querySelector(".id_marca")?.value     || "";
  const $mod    = $(card).find(".id_modelo");
  const prevVal = $mod.val();

  $mod.empty().append('<option value="">Seleccione...</option>');

  if (!ref && !marcaId) {
    $mod.prop("disabled", true).trigger("change.select2");
    return;
  }

  if (ref && marcaId) {
    $.get("../api/v1/fulmuv/getModelosByReferenciaMarca/" + encodeURIComponent(ref), { id_marca: marcaId }, function (raw) {
      const r = typeof raw === "string" ? JSON.parse(raw) : raw;
      const lista = r.data || [];
      lista.forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos)));
      $mod.prop("disabled", false);
      const ok = prevVal && lista.some(m => String(m.id_modelos_autos) === String(prevVal));
      $mod.val(ok ? prevVal : null).trigger("change.select2");
    }).fail(() => {
      // Fallback en memoria
      const lista = filtrarPorRefCMV(modelosCatalogoCMV, ref)
        .filter(m => String(m.id_marca) === String(marcaId));
      lista.forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos)));
      $mod.prop("disabled", !lista.length);
      const ok = prevVal && lista.some(m => String(m.id_modelos_autos) === String(prevVal));
      $mod.val(ok ? prevVal : null).trigger("change.select2");
    });
    return;
  }

  // Solo marca (sin referencia) — fallback en memoria
  const lista = marcaId
    ? modelosCatalogoCMV.filter(m => String(m.id_marca) === String(marcaId))
    : [];
  lista.forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos)));
  $mod.prop("disabled", !lista.length);
  const ok = prevVal && lista.some(m => String(m.id_modelos_autos) === String(prevVal));
  $mod.val(ok ? prevVal : null).trigger("change.select2");
}

/***********************
 * PROPAGACIÓN AL MODELO (igual que crear_vehiculo.js)
 ***********************/
function propagarRelacionModeloCard(card) {
  const idModelo = parseInt(card.querySelector(".id_modelo")?.value || "0", 10);
  if (!idModelo || idModelo <= 0) return;
  const payload = { id_modelos_autos: idModelo };
  const ta = parseInt(card.querySelector(".tipo_auto")?.value              || "0", 10);
  const tr = parseInt(card.querySelector(".tipo_traccion")?.value          || "0", 10);
  const mo = parseInt(card.querySelector(".funcionamiento_motor")?.value   || "0", 10);
  if (ta > 0) payload.id_tipo_auto           = ta;
  if (tr > 0) payload.id_tipo_traccion        = tr;
  if (mo > 0) payload.id_funcionamiento_motor = mo;
  if (Object.keys(payload).length <= 1) return;
  $.post("../api/v1/fulmuv/modelos_autos/enrich", payload);
}

/***********************
 * SWAL CONFIRM
 ***********************/
function swalConfirmV1(title, text, okText, cancelText, onOk, onCancel) {
  swal(
    { title, text, type: "info", showCancelButton: true,
      confirmButtonText: okText || "Sí", cancelButtonText: cancelText || "No",
      closeOnConfirm: true, closeOnCancel: true },
    function (isConfirm) {
      if (isConfirm) { if (typeof onOk     === "function") onOk(); }
      else           { if (typeof onCancel === "function") onCancel(); }
    }
  );
}

/***********************
 * ENSURE REMOTE (igual que crear_vehiculo.js)
 ***********************/
function ensureRemoteCMV(entity, txt, parents = {}) {
  const payload = { entity, nombre: txt, ...parents };
  return $.post("../api/v1/fulmuv/catalog/ensure", payload).then(r => {
    const res = typeof r === "string" ? JSON.parse(r) : r;
    if (res.error) return $.Deferred().reject(res.msg).promise();
    return res.id;
  });
}

/***********************
 * WIRE SELECT ENSURE (igual que crear_vehiculo.js)
 ***********************/
function wireSelectEnsureCMV($el, cfg) {
  $el.on("select2:opening", function () { $(this).data("prev", $(this).val()); });
  $el.on("select2:select", function (e) {
    const d   = e.params.data || {};
    const val = d.id;
    const txt = (d.text || "").trim();
    if (!d.newTag && !(!d.element && !/^\d+$/.test(val)) && val !== "nuevo") return;

    const parents = typeof cfg.parents === "function" ? cfg.parents() : {};
    for (let k in parents) {
      if (!parents[k] || +parents[k] <= 0) {
        swal("Falta seleccionar", `Debes seleccionar primero para registrar ${cfg.label || cfg.entity}.`, "warning");
        $el.val($el.data("prev") || null).trigger("change"); return;
      }
    }

    swalConfirmV1(`Registrar ${cfg.label || cfg.entity}`, `¿Registrar "${txt}"?`, "Sí", "No",
      function () {
        ensureRemoteCMV(cfg.entity, txt, parents).then(id => {
          $el.find("option").filter(function () { return $(this).val() == val; }).remove();
          $el.append(new Option(txt, id, true, true)).trigger("change");
          if (typeof cfg.onCreated === "function") cfg.onCreated(id, txt, parents);
          swal("Listo", "Registrado correctamente.", "success");
        }).fail(msg => { swal("Error", msg, "error"); $el.val($el.data("prev")).trigger("change"); });
      },
      function () { $el.val($el.data("prev")).trigger("change"); }
    );
  });
}

/***********************
 * LABEL Y NUMERACIÓN
 ***********************/
function actualizarLabelCard(card) {
  const ref    = card.querySelector(".referencias")?.value || "";
  const marca  = card.querySelector(".id_marca option:checked")?.textContent?.trim()  || "";
  const modelo = card.querySelector(".id_modelo option:checked")?.textContent?.trim() || "";
  const partes = [];
  if (ref) partes.push(ref);
  if (marca  && marca  !== "Seleccione...") partes.push(marca);
  if (modelo && modelo !== "Seleccione...") partes.push(modelo);
  const lbl = card.querySelector(".veh-label");
  if (lbl) lbl.textContent = partes.length ? partes.join(" · ") : "Nuevo vehículo";
}

function actualizarNumerosFilas() {
  container.querySelectorAll(".veh-card").forEach((card, i) => {
    const b = card.querySelector(".veh-num");
    if (b) b.textContent = `#${i + 1}`;
  });
}

/***********************
 * ADD ROW
 ***********************/
function addRow() {
  const card = document.createElement("div");
  card.className = "veh-card mb-2";

  card.innerHTML = `
    <div class="veh-card-hd">
      <span class="veh-num">#?</span>
      <span class="veh-label">Nuevo vehículo</span>
      <label class="veh-publicar-label ms-auto">
        <input type="checkbox" class="chk_publicar"> Publicar
      </label>
      <button class="btn btn-sm btn-outline-danger btnDel ms-2" type="button">✕</button>
    </div>
    <div class="veh-card-bd">

      <div class="veh-sec-lbl">Identificación</div>
      <div class="row g-2 mb-1">
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Tipo vehículo <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm referencias">${optionListStrings(referencias)}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Marca <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm id_marca">${optionList(marcas,"id_marca","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Modelo <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm id_modelo" disabled><option value="">Seleccione...</option></select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Subtipo <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm tipo_auto">${optionList(tipos_auto,"id_tipo_auto","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Tracción <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm tipo_traccion">${optionList(traccion,"id_tipo_traccion","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Func. Motor <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm funcionamiento_motor">${optionList(motor,"id_funcionamiento_motor","nombre")}</select>
        </div>
      </div>

      <div class="veh-sec-lbl">Datos técnicos</div>
      <div class="row g-2 mb-1">
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Año <span class="text-danger">*</span></label>
          <input class="form-control form-control-sm anio" type="number" min="1900" max="2100" placeholder="2020">
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Condición <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm condicion">
            <option value="">Seleccione...</option>
            <option value="nuevo">Nuevo</option>
            <option value="usado">Usado</option>
            <option value="seminuevo">Seminuevo</option>
          </select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Km/Millaje <span class="text-danger">*</span></label>
          <input class="form-control form-control-sm kilometraje" type="number" min="0" placeholder="85000">
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Tipo Vendedor <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm tipo_vendedor">${optionList(vendedor,"id_tipo_vendedor","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Transmisión</label>
          <select class="form-select form-select-sm transmision">${optionList(transmision,"id_transmision","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Cilindraje</label>
          <input class="form-control form-control-sm cilindraje" type="number" step="0.01" min="0" value="0">
        </div>
      </div>

      <div class="veh-sec-lbl">Extras</div>
      <div class="row g-2 mb-1">
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Inicio Placa</label>
          <input class="form-control form-control-sm inicio_placa" maxlength="1" placeholder="G">
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Fin Placa</label>
          <input class="form-control form-control-sm fin_placa" maxlength="1" placeholder="9">
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Color</label>
          <select class="form-select form-select-sm color">${optionList(colores,"id_color","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Tapicería</label>
          <select class="form-select form-select-sm tapiceria">${optionList(tapiceria,"id_tapiceria","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Tipo Dueño</label>
          <select class="form-select form-select-sm tipo_duenio">
            <option value="">Seleccione...</option>
            <option value="único dueño">Único dueño</option>
            <option value="Segundo dueño">Segundo dueño</option>
            <option value="Tercer dueño">Tercer dueño</option>
          </select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Dirección</label>
          <select class="form-select form-select-sm direccion">${optionList(direccion,"id_direccion","nombre")}</select>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Climatización</label>
          <select class="form-select form-select-sm climatizacion">${optionList(climatizacion,"id_climatizacion","nombre")}</select>
        </div>
      </div>

      <div class="veh-sec-lbl">Ubicación</div>
      <div class="row g-2 mb-1">
        <div class="col-6 col-md-4 col-xl-3">
          <label class="veh-lbl">Provincia <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm provincia">
            <option value="">Seleccione provincia</option>
            ${Object.keys(cantones).map(p => `<option value="${p}">${p}</option>`).join("")}
          </select>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
          <label class="veh-lbl">Cantón <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm canton"><option value="">Elija un cantón</option></select>
        </div>
        <div class="col-12 col-md-4 col-xl-6">
          <label class="veh-lbl">Tags <span class="text-danger">*</span></label>
          <input class="form-control form-control-sm tags" placeholder="Ej: sedan, familiar, 4x4">
        </div>
      </div>

      <div class="veh-sec-lbl">Precio</div>
      <div class="row g-2 align-items-end mb-1">
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Precio base <span class="text-danger">*</span></label>
          <input class="form-control form-control-sm precio_referencia" type="number" step="0.01" min="0" value="0">
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
          <label class="veh-lbl">Descuento %</label>
          <input class="form-control form-control-sm descuento" type="number" step="0.01" min="0" value="0">
        </div>
        <div class="col-auto d-flex align-items-center gap-3 pb-1">
          <label class="d-flex align-items-center gap-1 mb-0" style="font-size:12px;cursor:pointer;">
            <input class="iva" type="checkbox"> IVA 15%
          </label>
          <label class="d-flex align-items-center gap-1 mb-0" style="font-size:12px;cursor:pointer;">
            <input class="negociable" type="checkbox"> Negociable
          </label>
        </div>
      </div>

      <div class="veh-sec-lbl">Descripción</div>
      <div class="row g-2 mb-1">
        <div class="col-12">
          <textarea class="form-control form-control-sm descripcion" rows="2" placeholder="Descripción del vehículo..."></textarea>
        </div>
      </div>

      <div class="veh-sec-lbl">Imágenes y archivos</div>
      <div class="row g-2">
        <div class="col-12 col-sm-4 col-md-3">
          <label class="veh-lbl">Img frontal <span class="text-danger">*</span></label>
          <img class="veh-img-preview preview_frontal d-block mb-1" src="" alt="Frontal">
          <input class="form-control form-control-sm img_frontal" type="file" accept="image/*">
        </div>
        <div class="col-12 col-sm-4 col-md-3">
          <label class="veh-lbl">Img posterior <span class="text-danger">*</span></label>
          <img class="veh-img-preview preview_posterior d-block mb-1" src="" alt="Posterior">
          <input class="form-control form-control-sm img_posterior" type="file" accept="image/*">
        </div>
        <div class="col-12 col-sm-4 col-md-6">
          <label class="veh-lbl">Archivos / Documentos</label>
          <input class="form-control form-control-sm archivos" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*">
          <small class="text-muted d-block mt-1 hint_files">0 archivos</small>
          <div class="docs_preview d-flex flex-wrap gap-1 mt-1"></div>
        </div>
      </div>

    </div>
  `;

  container.appendChild(card);
  actualizarNumerosFilas();

  // ── Provincia → Cantón ──
  const selProv = card.querySelector("select.provincia");
  const onProvChange = () => cargarCantonesFila(card, selProv.value);
  selProv.addEventListener("change", onProvChange);

  // ── Referencia → filtrar marca/subtipo/tracción/motor + recargar modelos ──
  $(card).find(".referencias").on("change", function () {
    if (card.dataset.isloading === "1") return;
    actualizarLabelCard(card);
    actualizarSelectsByReferenciaCard(card, $(this).val() || "", false);
    // La actualización de marca dispara su propio change, que recarga modelos
    buscarModelosReferenciaCard(card);
  });

  // ── Marca → recargar modelos ──
  $(card).find(".id_marca").on("change", function () {
    if (card.dataset.isloading === "1") return;
    actualizarLabelCard(card);
    buscarModelosReferenciaCard(card);
  });

  // ── Modelo → auto-relleno de marca/subtipo/tracción/motor ──
  $(card).find(".id_modelo").on("change", async function () {
    if (card.dataset.isloading === "1") return;
    actualizarLabelCard(card);
    const id = $(this).val();
    if (!id || id === "nuevo") return;
    try {
      const raw = await $.get("../api/v1/fulmuv/getModeloById/" + id);
      const r = typeof raw === "string" ? JSON.parse(raw) : raw;
      if (r.error || !r.data) return;
      const d = r.data;
      $(card).find(".id_marca").val(d.id_marca).trigger("change.select2");
      $(card).find(".tipo_auto").val(d.id_tipo_auto).trigger("change.select2");
      $(card).find(".tipo_traccion").val(d.id_tipo_traccion).trigger("change.select2");
      $(card).find(".funcionamiento_motor").val(d.id_funcionamiento_motor).trigger("change.select2");
    } catch (e) { console.error("Error asignando modelo:", e); }
  });

  // ── Subtipo / tracción / motor → propagar al registro del modelo ──
  $(card).find(".tipo_auto, .tipo_traccion, .funcionamiento_motor").on("change", function () {
    if (card.dataset.isloading === "1") return;
    propagarRelacionModeloCard(card);
  });

  // ── Previews imágenes ──
  card.querySelector(".img_frontal").addEventListener("change", function () {
    const prev = card.querySelector(".preview_frontal");
    if (this.files[0]) { prev.src = URL.createObjectURL(this.files[0]); prev.classList.add("visible"); }
    else if (!card.dataset.img_frontal) prev.classList.remove("visible");
  });
  card.querySelector(".img_posterior").addEventListener("change", function () {
    const prev = card.querySelector(".preview_posterior");
    if (this.files[0]) { prev.src = URL.createObjectURL(this.files[0]); prev.classList.add("visible"); }
    else if (!card.dataset.img_posterior) prev.classList.remove("visible");
  });

  // ── Preview archivos ──
  card.querySelector(".archivos").addEventListener("change", function () {
    card.querySelector(".hint_files").innerText = `${this.files.length} archivos (nuevos)`;
    const div = card.querySelector(".docs_preview");
    div.innerHTML = "";
    Array.from(this.files).forEach(file => {
      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.style.cssText = "width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #e2e8f0;";
        div.appendChild(img);
      } else {
        const b = document.createElement("span");
        b.className = "badge bg-secondary d-flex align-items-center justify-content-center";
        b.style.cssText = "width:40px;height:40px;font-size:10px;";
        b.innerText = file.name.split(".").pop().toUpperCase();
        div.appendChild(b);
      }
    });
  });

  // ── Eliminar fila ──
  card.querySelector(".btnDel").addEventListener("click", () => {
    if (card.dataset.id_vehiculo) vehiculosEliminados.push(card.dataset.id_vehiculo);
    $(card).find("select").each(function () { if ($(this).data("select2")) $(this).select2("destroy"); });
    card.remove();
    actualizarNumerosFilas();
    if (!container.querySelectorAll(".veh-card").length) addRow();
  });

  // ── Select2: selects estándar (sin tags) ──
  const stdSels = [".referencias", ".condicion", ".tipo_vendedor", ".transmision",
    ".color", ".tapiceria", ".tipo_duenio", ".direccion", ".climatizacion",
    "select.provincia", "select.canton"];

  stdSels.forEach(sel => {
    const $el = $(card).find(sel);
    if (!$el.length) return;
    if ($el.data("select2")) $el.select2("destroy");
    $el.select2({ theme: "bootstrap-5", width: "100%", placeholder: "Seleccione...", dropdownParent: $(document.body) });
  });
  $(selProv).on("select2:select", onProvChange);

  // ── Select2 con tags + wireSelectEnsure ──
  const tagsCfg = {
    ".id_marca": {
      entity: "marcas", label: "Marca",
      parents: () => ({ referencia: card.querySelector(".referencias")?.value || "" }),
      onCreated: (id, txt, p) => marcas.push({ id_marca: id, nombre: txt, referencia: p.referencia || "" })
    },
    ".tipo_auto": {
      entity: "tipos_auto", label: "Subtipo",
      parents: () => ({ referencia: card.querySelector(".referencias")?.value || "" }),
      onCreated: (id, txt, p) => tipos_auto.push({ id_tipo_auto: id, nombre: txt, referencia: p.referencia || "" })
    },
    ".tipo_traccion": {
      entity: "tipo_traccion", label: "Tracción",
      parents: () => ({ referencia: card.querySelector(".referencias")?.value || "" }),
      onCreated: (id, txt, p) => traccion.push({ id_tipo_traccion: id, nombre: txt, referencia: p.referencia || "" })
    },
    ".funcionamiento_motor": {
      entity: "funcionamiento_motor", label: "Motor",
      parents: () => ({ referencia: card.querySelector(".referencias")?.value || "" }),
      onCreated: (id, txt, p) => motor.push({ id_funcionamiento_motor: id, nombre: txt, referencia: p.referencia || "" })
    },
    ".id_modelo": {
      entity: "modelos_autos", label: "Modelo",
      parents: () => {
        const idNum = v => /^\d+$/.test(String(v)) ? parseInt(v, 10) : 0;
        const p = { id_marca: idNum(card.querySelector(".id_marca")?.value) };
        const ta  = idNum(card.querySelector(".tipo_auto")?.value);
        const tr  = idNum(card.querySelector(".tipo_traccion")?.value);
        const mo  = idNum(card.querySelector(".funcionamiento_motor")?.value);
        const ref = card.querySelector(".referencias")?.value || "";
        if (ta  > 0) p.id_tipo_auto           = ta;
        if (tr  > 0) p.id_tipo_traccion        = tr;
        if (mo  > 0) p.id_funcionamiento_motor = mo;
        if (ref)     p.referencia              = ref;
        return p;
      },
      onCreated: (id, txt) => {
        const ref     = card.querySelector(".referencias")?.value || "";
        const marcaId = card.querySelector(".id_marca")?.value    || "";
        modelosCatalogoCMV.push({ id_modelos_autos: id, nombre: txt, referencia: ref, id_marca: marcaId });
      }
    }
  };

  Object.entries(tagsCfg).forEach(([sel, cfg]) => {
    const $el = $(card).find(sel);
    if (!$el.length) return;
    if ($el.data("select2")) $el.select2("destroy");
    $el.select2({
      theme: "bootstrap-5", width: "100%", placeholder: "Seleccione...",
      allowClear: true, tags: true, dropdownParent: $(document.body),
      createTag(params) {
        const term = $.trim(params.term).toUpperCase();
        if (!term) return null;
        return { id: "nuevo", text: term, newTag: true };
      }
    });
    wireSelectEnsureCMV($el, cfg);
  });

  return card;
}

function getIdEmpresa() {
  if ($("#id_rol_principal").val() == 1) return $("#lista_empresas").val();
  return $("#id_empresa").val();
}

/***********************
 * CARGAR BORRADOR
 ***********************/
async function cargarBorradorVehiculos() {
  try {
    vehiculosEliminados = [];
    const id_empresa = getIdEmpresa();
    if (!id_empresa) return;
    const borrador = await fetchJSON(`../api/v1/fulmuv/vehiculos/borrador/${id_empresa}`);
    container.innerHTML = "";
    if (!borrador || !borrador.length) return;
    for (const v of borrador) await addRowWithDataVehiculo(v);
  } catch (e) { console.error("cargarBorradorVehiculos:", e); }
}

/***********************
 * PINTAR FILA CON DATA
 ***********************/
async function addRowWithDataVehiculo(v) {
  addRow();
  const card = container.lastElementChild;

  card.dataset.isloading    = "1";
  card.dataset.id_vehiculo  = v.id_vehiculo   || "";
  card.dataset.img_frontal  = v.img_frontal   || "";
  card.dataset.img_posterior = v.img_posterior || "";

  if (v.img_frontal) {
    const p = card.querySelector(".preview_frontal");
    p.src = "../admin/" + v.img_frontal; p.classList.add("visible");
  }
  if (v.img_posterior) {
    const p = card.querySelector(".preview_posterior");
    p.src = "../admin/" + v.img_posterior; p.classList.add("visible");
  }

  // Archivos guardados
  let archivos = [];
  try {
    const parsed = typeof v.archivos === "string" ? JSON.parse(v.archivos) : v.archivos;
    if (Array.isArray(parsed)) archivos = parsed;
    else if (parsed?.archivos && Array.isArray(parsed.archivos)) archivos = parsed.archivos;
  } catch (e) {}

  card.dataset.archivos_actual = JSON.stringify(archivos);

  const previewDiv = card.querySelector(".docs_preview");
  const hintEl     = card.querySelector(".hint_files");
  if (archivos.length) {
    if (hintEl) hintEl.innerText = `${archivos.length} archivos (guardados)`;
    archivos.forEach(arc => {
      const fileUrl = typeof arc === "string" ? arc : (arc?.archivo || arc?.url || "");
      if (!fileUrl) return;
      const esImg = arc?.tipo === "imagen" || /\.(jpeg|jpg|gif|png|webp)$/i.test(fileUrl.split("?")[0]);
      if (esImg) {
        const img = document.createElement("img");
        img.src = "../admin/" + fileUrl;
        img.style.cssText = "width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #e2e8f0;";
        previewDiv.appendChild(img);
      } else {
        const b = document.createElement("span");
        b.className = "badge bg-info d-flex align-items-center justify-content-center";
        b.style.cssText = "width:40px;height:40px;font-size:10px;";
        b.innerText = fileUrl.split(".").pop().toUpperCase();
        previewDiv.appendChild(b);
      }
    });
  }

  // Campos simples
  card.querySelector(".anio").value             = v.anio            || "";
  card.querySelector(".kilometraje").value      = v.kilometraje     || "";
  card.querySelector(".inicio_placa").value     = v.inicio_placa    || "";
  card.querySelector(".fin_placa").value        = v.fin_placa       || "";
  card.querySelector(".cilindraje").value       = v.cilindraje      || 0;
  card.querySelector(".descripcion").value      = v.descripcion     || "";
  card.querySelector(".precio_referencia").value = v.precio_referencia || 0;
  card.querySelector(".descuento").value        = v.descuento       || 0;
  card.querySelector(".tags").value             = v.tags            || "";
  card.querySelector(".iva").checked        = String(v.iva)        === "1";
  card.querySelector(".negociable").checked = String(v.negociable) === "1";

  const condicionArr     = safeParseJSON(v.condicion);
  const transmisionArr   = safeParseJSON(v.transmision);
  const provinciaArr     = safeParseJSON(v.provincia);
  const cantonArr        = safeParseJSON(v.canton);
  const tapiceriaArr     = safeParseJSON(v.tapiceria);
  const tipoDuenoArr     = safeParseJSON(v.tipo_dueno);
  const direccionArr     = safeParseJSON(v.direccion);
  const climatizacionArr = safeParseJSON(v.climatizacion);
  const referenciasArr   = safeParseJSON(v.referencias);
  const tipoVendedorArr  = safeParseJSON(v.tipo_vendedor);

  setSelectSingle(card.querySelector(".condicion"),          condicionArr[0]     ?? "");
  setSelectSingle(card.querySelector("select.transmision"),  transmisionArr[0]   ?? "");
  setSelectSingle(card.querySelector(".tapiceria"),          tapiceriaArr[0]     ?? "");
  setSelectSingle(card.querySelector(".tipo_duenio"),        tipoDuenoArr[0]     ?? "");
  setSelectSingle(card.querySelector(".direccion"),          direccionArr[0]     ?? "");
  setSelectSingle(card.querySelector(".climatizacion"),      climatizacionArr[0] ?? "");
  setSelectSingle(card.querySelector(".tipo_vendedor"),      tipoVendedorArr[0]  ?? "");
  setSelectSingle(card.querySelector(".color"),              v.color             || "");

  const provincia = provinciaArr[0] ?? "";
  const canton    = cantonArr[0]    ?? "";
  setSelectSingle(card.querySelector("select.provincia"), provincia);
  cargarCantonesFila(card, provincia);
  setSelectSingle(card.querySelector("select.canton"), canton);

  // Referencia → repoblar opciones filtradas (keepValues=true)
  const ref = referenciasArr[0] ?? (v.referencias || "");
  if (ref) {
    setSelectSingle(card.querySelector(".referencias"), ref);
    actualizarSelectsByReferenciaCard(card, ref, true);
  }

  // Seteo de valores dependientes (opciones ya repobladas)
  setSelectSingle(card.querySelector(".id_marca"),             v.id_marca             || "");
  setSelectSingle(card.querySelector(".tipo_auto"),            v.tipo_auto            || "");
  setSelectSingle(card.querySelector(".tipo_traccion"),        v.tipo_traccion        || "");
  setSelectSingle(card.querySelector(".funcionamiento_motor"), v.funcionamiento_motor || "");

  // Cargar modelos y setear el seleccionado
  if (ref && v.id_marca) {
    try {
      const raw = await $.get("../api/v1/fulmuv/getModelosByReferenciaMarca/" + encodeURIComponent(ref),
        { id_marca: v.id_marca });
      const r = typeof raw === "string" ? JSON.parse(raw) : raw;
      const $mod = $(card).find(".id_modelo");
      $mod.empty().append('<option value="">Seleccione...</option>');
      (r.data || []).forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos)));
      $mod.prop("disabled", false);
    } catch (e) { console.error("Error modelos borrador:", e); }
  }
  setSelectSingle(card.querySelector(".id_modelo"), v.id_modelo || "");

  actualizarLabelCard(card);
  card.dataset.isloading = "0";
  return card;
}

/***********************
 * GET DATA
 ***********************/
function getRowsData() {
  const out = [];
  container.querySelectorAll(".veh-card").forEach((card, index) => {
    const imgF = card.querySelector(".img_frontal")?.files?.[0]   || null;
    const imgP = card.querySelector(".img_posterior")?.files?.[0] || null;
    out.push({
      nodoTR: card, index,
      id_vehiculo: card.dataset.id_vehiculo ? Number(card.dataset.id_vehiculo) : null,
      publicar:    card.querySelector(".chk_publicar")?.checked ? 1 : 0,

      referencias:          card.querySelector(".referencias").value            || "",
      id_modelo:            card.querySelector(".id_modelo").value,
      tipo_auto:            card.querySelector(".tipo_auto").value,
      id_marca:             card.querySelector(".id_marca").value,
      tipo_traccion:        card.querySelector(".tipo_traccion").value,
      funcionamiento_motor: card.querySelector(".funcionamiento_motor").value,

      anio:          card.querySelector(".anio").value.trim(),
      condicion:     card.querySelector(".condicion").value,
      kilometraje:   card.querySelector(".kilometraje").value.trim(),
      transmision:   card.querySelector("select.transmision").value,
      tipo_vendedor: card.querySelector(".tipo_vendedor").value,
      inicio_placa:  card.querySelector(".inicio_placa").value.trim(),
      fin_placa:     card.querySelector(".fin_placa").value.trim(),
      provincia:     card.querySelector("select.provincia").value.trim(),
      canton:        card.querySelector("select.canton").value.trim(),
      color:         card.querySelector("select.color").value,
      cilindraje:    card.querySelector(".cilindraje").value,
      tapiceria:     card.querySelector(".tapiceria").value,
      tipo_duenio:   card.querySelector(".tipo_duenio").value,
      direccion:     card.querySelector(".direccion").value,
      climatizacion: card.querySelector(".climatizacion").value,
      descripcion:   card.querySelector(".descripcion").value.trim(),
      precio_referencia: parseFloat(card.querySelector(".precio_referencia").value || "0"),
      descuento:         parseFloat(card.querySelector(".descuento").value          || "0"),
      iva:       card.querySelector(".iva").checked        ? 1 : 0,
      negociable: card.querySelector(".negociable").checked ? 1 : 0,
      tags:      card.querySelector(".tags").value.trim(),

      files: {
        img_frontal:   imgF,
        img_posterior: imgP,
        archivos: card.querySelector(".archivos")?.files ? Array.from(card.querySelector(".archivos").files) : []
      },
      img_frontal_actual:   card.dataset.img_frontal   || "",
      img_posterior_actual: card.dataset.img_posterior || "",
      archivos_actual:      card.dataset.archivos_actual || "[]"
    });
  });
  return out;
}

/***********************
 * VALIDACIÓN
 ***********************/
function validarFila(v) {
  const errores = [], fila = v.index + 1, isNew = !v.id_vehiculo;
  if (isEmptyStr(v.anio))               errores.push(`Fila ${fila}: Falta año`);
  if (isEmptyStr(v.condicion))          errores.push(`Fila ${fila}: Falta condición`);
  if (isEmptyStr(v.tipo_auto))          errores.push(`Fila ${fila}: Falta subtipo`);
  if (isEmptyStr(v.id_marca))           errores.push(`Fila ${fila}: Falta marca`);
  if (isEmptyStr(v.tipo_traccion))      errores.push(`Fila ${fila}: Falta tracción`);
  if (isEmptyStr(v.funcionamiento_motor)) errores.push(`Fila ${fila}: Falta func. motor`);
  if (isEmptyStr(v.id_modelo))          errores.push(`Fila ${fila}: Falta modelo`);
  if (isEmptyStr(v.kilometraje))        errores.push(`Fila ${fila}: Falta kilometraje`);
  if (isEmptyStr(v.provincia))          errores.push(`Fila ${fila}: Falta provincia`);
  if (isEmptyStr(v.canton))             errores.push(`Fila ${fila}: Falta cantón`);
  if (isEmptyStr(v.descripcion))        errores.push(`Fila ${fila}: Falta descripción`);
  if (!(Number(v.precio_referencia) > 0)) errores.push(`Fila ${fila}: Precio debe ser > 0`);
  if (isEmptyStr(v.tags))               errores.push(`Fila ${fila}: Falta tags`);
  if (isNew) {
    if (!v.files.img_frontal)   errores.push(`Fila ${fila}: Falta imagen frontal`);
    if (!v.files.img_posterior) errores.push(`Fila ${fila}: Falta imagen posterior`);
  } else {
    if (!v.img_frontal_actual   && !v.files.img_frontal)   errores.push(`Fila ${fila}: Falta imagen frontal`);
    if (!v.img_posterior_actual && !v.files.img_posterior) errores.push(`Fila ${fila}: Falta imagen posterior`);
  }
  return errores;
}

/***********************
 * SUBIDA IMÁGENES / ARCHIVOS
 ***********************/
function subirImagenesPrincipalesFila(imgFrontalFile, imgPosteriorFile) {
  return new Promise((resolve, reject) => {
    if (!imgFrontalFile && !imgPosteriorFile) { resolve({ img_frontal: "", img_posterior: "" }); return; }
    const fd = new FormData();
    if (imgFrontalFile)   fd.append("img_frontal",   imgFrontalFile);
    if (imgPosteriorFile) fd.append("img_posterior", imgPosteriorFile);
    $.ajax({
      url: "../admin/cargar_imagenes_frontales_masivo.php",
      method: "POST", data: fd, processData: false, contentType: false, dataType: "json",
      success: res => {
        if (res?.response === "success")
          resolve({ img_frontal: res.data?.img_frontal || "", img_posterior: res.data?.img_posterior || "" });
        else reject(new Error(res?.error || "Error al subir imágenes"));
      },
      error: () => reject(new Error("Error de red al subir imágenes"))
    });
  });
}

function saveFiles(files) {
  return new Promise((resolve, reject) => {
    if (!files?.length) { resolve([]); return; }
    const fd = new FormData();
    files.forEach(f => fd.append("archivos[]", f));
    $.ajax({
      type: "POST", url: "../admin/cargar_imagen_multiple.php",
      data: fd, cache: false, contentType: false, processData: false, dataType: "json",
      success: res => {
        if (res?.response === "success") {
          const arr = Array.isArray(res?.data?.archivos) ? res.data.archivos
                    : Array.isArray(res?.data) ? res.data : [];
          resolve(arr);
        } else { SweetAlert("error", res?.error || "Error al guardar archivos."); reject(new Error("upload error")); }
      },
      error: xhr => reject(new Error(xhr?.responseText || "Error de red"))
    });
  });
}

/***********************
 * GUARDAR MASIVO
 ***********************/
async function guardarMasivo(estado) {
  const rows = getRowsData();
  const aProcesar = estado === "A" ? rows.filter(r => r.publicar === 1) : rows;

  if (estado === "A" && !aProcesar.length) {
    SweetAlert("warning", "Selecciona al menos una fila en la casilla de 'Publicar'.");
    return;
  }
  if (estado === "A") {
    for (const r of aProcesar) {
      const err = validarFila(r) || [];
      if (err.length) { SweetAlert("error", "Corrige estos campos:\n\n" + err.join("\n")); return; }
    }
  }

  const isRowEmpty = v => {
    const hasText    = !!String(v.anio||"").trim() || !!String(v.kilometraje||"").trim() || !!String(v.descripcion||"").trim() || !!String(v.tags||"").trim();
    const hasSelects = !!String(v.id_modelo||"").trim() || !!String(v.id_marca||"").trim() || !!String(v.tipo_auto||"").trim();
    const hasMoney   = Number(v.precio_referencia||0) > 0;
    const hasFiles   = !!v.files?.img_frontal || !!v.files?.img_posterior || (Array.isArray(v.files?.archivos) && v.files.archivos.length);
    return !(hasText || hasSelects || hasMoney || hasFiles);
  };

  const btnG = document.getElementById("btnGuardarBorrador");
  const btnP = document.getElementById("btnPublicar");
  const origG = btnG.innerHTML, origP = btnP.innerHTML;

  try {
    btnG.disabled = btnP.disabled = true;
    if (estado === "P") btnG.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
    else                btnP.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

    const okCreate = [], okUpdate = [], skipped = [], fail = [];

    for (const v of aProcesar) {
      const fila = (v.index != null) ? Number(v.index) + 1 : 0;
      if (estado === "P" && isRowEmpty(v)) { skipped.push(fila || "-"); continue; }
      try {
        let imgF = v.img_frontal_actual || "", imgP = v.img_posterior_actual || "";
        const selF = !!v.files?.img_frontal, selP = !!v.files?.img_posterior;

        if (estado === "A") {
          if (!v.id_vehiculo || selF || selP) {
            if (!(selF && selP)) throw new Error("Para publicar debes seleccionar imagen frontal y posterior.");
            const imgs = await subirImagenesPrincipalesFila(v.files.img_frontal, v.files.img_posterior);
            imgF = imgs.img_frontal || imgF; imgP = imgs.img_posterior || imgP;
          }
        } else {
          if (selF || selP) {
            const imgs = await subirImagenesPrincipalesFila(v.files.img_frontal, v.files.img_posterior);
            if (imgs.img_frontal)   imgF = imgs.img_frontal;
            if (imgs.img_posterior) imgP = imgs.img_posterior;
          }
        }

        let archivosSubidos = [];
        if (v.files?.archivos?.length) archivosSubidos = await saveFiles(v.files.archivos);

        const payload = {
          id_empresa: getIdEmpresa(), tipo_creador: $("#tipo_user").val() || "empresa",
          id_modelo: v.id_modelo || "", anio: v.anio || "",
          tipo_auto: v.tipo_auto || "", id_marca: v.id_marca || "",
          kilometraje: v.kilometraje || "", tipo_traccion: v.tipo_traccion || "",
          funcionamiento_motor: v.funcionamiento_motor || "",
          inicio_placa: v.inicio_placa || "", fin_placa: v.fin_placa || "",
          color: v.color || "", cilindraje: v.cilindraje || "",
          descripcion: v.descripcion || "",
          precio_referencia: Number(v.precio_referencia || 0),
          descuento: Number(v.descuento || 0), iva: Number(v.iva || 0),
          negociable: Number(v.negociable || 0), tags: v.tags || "",
          condicion:    v.condicion    ? [v.condicion]    : [],
          transmision:  v.transmision  ? [v.transmision]  : [],
          tipo_vendedor: v.tipo_vendedor ? [v.tipo_vendedor] : [],
          provincia:    v.provincia    ? [v.provincia]    : [],
          canton:       v.canton       ? [v.canton]       : [],
          tapiceria:    v.tapiceria    ? [v.tapiceria]    : [],
          tipo_dueno:   v.tipo_duenio  ? [v.tipo_duenio]  : [],
          direccion:    v.direccion    ? [v.direccion]    : [],
          climatizacion: v.climatizacion ? [v.climatizacion] : [],
          referencias:  v.referencias  ? [v.referencias]  : [],
          img_frontal: imgF, img_posterior: imgP,
          archivos: { archivos: archivosSubidos }
        };

        if (!v.id_vehiculo) {
          const resp = await postJQ("../api/v1/fulmuv/vehiculos/create", { ...payload, estado });
          const r = typeof resp === "string" ? JSON.parse(resp) : resp;
          if (r.error === false) {
            okCreate.push(fila);
            const newId = r.data?.id_vehiculo || r.id_vehiculo || null;
            if (newId && v.nodoTR) v.nodoTR.dataset.id_vehiculo = newId;
          } else fail.push({ fila, msg: r.msg || "Error al crear" });
        } else {
          const resp = await postJQ("../api/v1/fulmuv/vehiculos/update_full", { ...payload, id_vehiculo: v.id_vehiculo, estado });
          const r = typeof resp === "string" ? JSON.parse(resp) : resp;
          if (r.error === false) okUpdate.push(fila);
          else fail.push({ fila, msg: r.msg || "Error al actualizar" });
        }
      } catch (e) { fail.push({ fila, msg: e.message || "Error" }); }
    }

    // Eliminaciones
    const okDelete = [];
    for (const idEl of vehiculosEliminados) {
      try {
        const resp = await postJQ("../api/v1/fulmuv/vehiculos/delete", { id: idEl });
        const r = typeof resp === "string" ? JSON.parse(resp) : resp;
        if (r.error === false) okDelete.push(idEl);
        else fail.push({ fila: "Borrado", msg: r.msg || `Error al eliminar ID ${idEl}` });
      } catch (e) { fail.push({ fila: "Borrado", msg: `Error de red al eliminar ID ${idEl}` }); }
    }
    vehiculosEliminados = [];

    const label = estado === "P" ? "Borrador guardado" : "Publicación completada";
    SweetAlert("success", `${label}.\nCreados: ${okCreate.length}\nActualizados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nErrores: ${fail.length}`);

    await cargarBorradorVehiculos();
    if (!container.querySelectorAll(".veh-card").length) addRow();

  } finally {
    btnG.disabled = btnP.disabled = false;
    btnG.innerHTML = origG; btnP.innerHTML = origP;
  }
}
