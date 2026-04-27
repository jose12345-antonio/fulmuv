// carga_masiva_productos.js v2.0.0

// ==========================================
// VARIABLES GLOBALES
// ==========================================
let marcas = [];
let tiposAuto = [];
let tiposTraccion = [];
let funcionamiento_motor = [];
let categorias = [];
let marcasProductos = [];
let referencias = [];

let productosEliminados = [];

const container = document.getElementById("vehiculosContainer");

// ==========================================
// EVENT LISTENERS & INIT
// ==========================================
document.getElementById("btnAddRow").addEventListener("click", () => addRow());

document.getElementById("btnClear").addEventListener("click", () => {
  container.querySelectorAll(".veh-card").forEach(card => {
    if (card.dataset.id_producto) productosEliminados.push(card.dataset.id_producto);
    $(card).find("select").each(function () {
      if ($(this).data("select2")) $(this).select2("destroy");
    });
  });
  container.innerHTML = "";
  addRow();
});

document.getElementById("btnGuardarBorrador").addEventListener("click", guardarBorrador);
document.getElementById("btnPublicar").addEventListener("click", publicarBorrador);

document.getElementById("buscadorBorrador").addEventListener("input", function () {
  const q = normTxt(this.value);
  container.querySelectorAll(".veh-card").forEach(card => {
    const lbl = normTxt(card.querySelector(".veh-label")?.textContent || "");
    const cod = normTxt(card.querySelector(".codigo")?.value || "");
    card.style.display = (!q || lbl.includes(q) || cod.includes(q)) ? "" : "none";
  });
});

document.addEventListener("DOMContentLoaded", async () => {
  await cargarCombos();
  await cargarBorrador();
});

// ==========================================
// FETCH JSON
// ==========================================
async function fetchJSON(url) {
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
    const json = await res.json();
    if (Array.isArray(json)) return json;
    if (Array.isArray(json.data)) return json.data;
    if (json.data && Array.isArray(json.data.data)) return json.data.data;
    return [];
  } catch (error) {
    console.error("Error en fetchJSON:", error, "URL:", url);
    return [];
  }
}

// ==========================================
// CARGAR COMBOS
// ==========================================
async function cargarCombos() {
  marcas = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/marcas/"), "nombre");
  tiposAuto = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tiposAuto/"), "nombre");
  tiposTraccion = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tipo_tracccion/"), "nombre");
  funcionamiento_motor = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getFuncionamientoMotor/"), "nombre");
  categorias = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/categorias/?tipo=producto"), "nombre");
  marcasProductos = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getMarcasProductos/"), "nombre");
  const REFS_BLOQUEADAS = new Set(["UTV","UTVS","ATV","ATVS","CAMIONES Y PESADOS","CAMIONES","PESADOS"].map(normTxt));
  referencias = orderNAandVariosFirstObjects(
    (await fetchJSON("../api/v1/fulmuv/getReferencias/")).filter(r => !REFS_BLOQUEADAS.has(normTxt(r))),
    "nombre"
  );
}

// ==========================================
// SORT & NORM HELPERS
// ==========================================
function normTxt(v) {
  return String(v ?? "").trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function orderNAandVariosFirstObjects(arr, textKey = "nombre") {
  const TOP = new Set(["N/A", "NA", "NO APLICA", "VARIOS"]);
  return (arr || []).slice().sort((a, b) => {
    const A = normTxt(a?.[textKey]);
    const B = normTxt(b?.[textKey]);
    const aTop = TOP.has(A), bTop = TOP.has(B);
    if (aTop && !bTop) return -1;
    if (!aTop && bTop) return 1;
    const pri = (x) => (x === "N/A" || x === "NA" || x === "NO APLICA") ? 0 : (x === "VARIOS" ? 1 : 2);
    const pa = pri(A), pb = pri(B);
    if (pa !== pb) return pa - pb;
    return A.localeCompare(B, "es");
  });
}

// ==========================================
// OPTIONS HELPERS
// ==========================================
function optionList(arr, valueKey, textKey) {
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach(item => {
    html += `<option value="${item[valueKey]}">${item[textKey]}</option>`;
  });
  return html;
}

function optionListStrings(arr) {
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach(s => {
    html += `<option value="${String(s)}">${String(s)}</option>`;
  });
  return html;
}

function optionListNombreTexto(arr) {
  let html = `<option value="">Seleccione...</option>`;
  const seen = new Set();
  (arr || []).forEach(item => {
    const nombre = String(item?.nombre ?? "").trim();
    if (!nombre) return;
    const k = nombre.toLowerCase();
    if (seen.has(k)) return;
    seen.add(k);
    html += `<option value="${nombre}">${nombre}</option>`;
  });
  return html;
}

// ==========================================
// ADD ROW — crea una tarjeta
// ==========================================
function addRow() {
  const card = document.createElement("div");
  card.className = "veh-card mb-3";

  card.innerHTML = `
    <div class="veh-card-hd">
      <span class="veh-num">#${container.children.length + 1}</span>
      <span class="veh-label">Nuevo producto</span>
      <div class="ms-auto d-flex align-items-center gap-3">
        <label class="veh-publicar-label">
          <input type="checkbox" class="chk_publicar" style="width:15px;height:15px;cursor:pointer;accent-color:#16a34a"> Publicar
        </label>
        <button class="btn btn-sm btn-outline-danger btnDel" type="button">✕</button>
      </div>
    </div>
    <div class="veh-card-bd">
      <div class="row g-2">

        <div class="col-12"><div class="veh-sec-lbl">Identificación</div></div>
        <div class="col-md-4 col-lg-3">
          <label class="veh-lbl">Título</label>
          <input class="form-control form-control-sm nombre" placeholder="Título del producto">
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Código</label>
          <input class="form-control form-control-sm codigo" placeholder="Código">
        </div>
        <div class="col-6 col-md-3">
          <label class="veh-lbl">Marca producto</label>
          <select class="form-select form-select-sm id_marca_producto">
            ${optionList(marcasProductos, "id_marca_producto", "nombre")}
          </select>
        </div>

        <div class="col-12"><div class="veh-sec-lbl">Vehículo compatible</div></div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Referencias</label>
          <select class="form-select form-select-sm referencias" multiple>
            ${optionListStrings(referencias)}
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Marca vehículo</label>
          <select class="form-select form-select-sm id_marca" multiple>
            ${optionList(marcas, "id_marca", "nombre")}
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Modelo</label>
          <select class="form-select form-select-sm id_modelo" multiple></select>
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Tipo vehículo</label>
          <select class="form-select form-select-sm tipo_auto" multiple>
            ${optionList(tiposAuto, "id_tipo_auto", "nombre")}
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Tracción</label>
          <select class="form-select form-select-sm tipo_traccion" multiple>
            ${optionList(tiposTraccion, "id_tipo_traccion", "nombre")}
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Func. motor</label>
          <select class="form-select form-select-sm funcionamiento_motor" multiple>
            ${optionList(funcionamiento_motor, "id_funcionamiento_motor", "nombre")}
          </select>
        </div>

        <div class="col-12"><div class="veh-sec-lbl">Categoría</div></div>
        <div class="col-6 col-md-3">
          <label class="veh-lbl">Categoría</label>
          <select class="form-select form-select-sm categoria" multiple>
            ${optionList(categorias, "id_categoria", "nombre")}
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="veh-lbl">Subcategoría</label>
          <select class="form-select form-select-sm sub_categoria" multiple></select>
        </div>

        <div class="col-12"><div class="veh-sec-lbl">Precio</div></div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Precio</label>
          <input class="form-control form-control-sm precio" type="number" step="0.01" min="0" value="0">
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Descuento %</label>
          <input class="form-control form-control-sm descuento" type="number" step="0.01" min="0" value="0">
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Peso (kg)</label>
          <input class="form-control form-control-sm peso" type="number" step="0.01" min="0" value="0">
        </div>
        <div class="col-3 col-md-1">
          <label class="veh-lbl">IVA</label>
          <div class="pt-1"><input type="checkbox" class="form-check-input iva" style="width:15px;height:15px"></div>
        </div>
        <div class="col-3 col-md-1">
          <label class="veh-lbl">Negociable</label>
          <div class="pt-1"><input type="checkbox" class="form-check-input negociable" style="width:15px;height:15px"></div>
        </div>

        <div class="col-12"><div class="veh-sec-lbl">Descripción y tags</div></div>
        <div class="col-md-6">
          <label class="veh-lbl">Descripción</label>
          <textarea class="form-control form-control-sm descripcion" rows="2" placeholder="Descripción"></textarea>
        </div>
        <div class="col-md-6">
          <label class="veh-lbl">Tags</label>
          <input class="form-control form-control-sm tags" placeholder="Ej: unidad, caja">
        </div>

        <div class="col-12"><div class="veh-sec-lbl">Imágenes y archivos</div></div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Frontal</label>
          <img class="veh-img-preview img_preview_frontal" src="" alt="">
          <input class="form-control form-control-sm img_frontal" type="file" accept="image/*">
        </div>
        <div class="col-6 col-md-2">
          <label class="veh-lbl">Posterior</label>
          <img class="veh-img-preview img_preview_posterior" src="" alt="">
          <input class="form-control form-control-sm img_posterior" type="file" accept="image/*">
        </div>
        <div class="col-md-4">
          <label class="veh-lbl">Documentos</label>
          <input class="form-control form-control-sm docs_producto" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*">
          <small class="text-muted d-block mt-1 docs_hint">0 archivos</small>
          <div class="docs_preview d-flex flex-wrap gap-1 mt-1"></div>
        </div>

      </div>
    </div>
  `;

  // Botón eliminar
  card.querySelector(".btnDel").addEventListener("click", () => {
    if (card.dataset.id_producto) productosEliminados.push(card.dataset.id_producto);
    $(card).find("select").each(function () {
      if ($(this).data("select2")) $(this).select2("destroy");
    });
    card.remove();
    actualizarNumerosFilas();
    if (container.children.length === 0) addRow();
  });

  // Preview imágenes
  card.querySelector(".img_frontal").addEventListener("change", function (e) {
    const file = e.target.files[0];
    const preview = card.querySelector(".img_preview_frontal");
    if (file) { preview.src = URL.createObjectURL(file); preview.classList.add("visible"); }
    else if (!card.dataset.img_frontal) preview.classList.remove("visible");
  });

  card.querySelector(".img_posterior").addEventListener("change", function (e) {
    const file = e.target.files[0];
    const preview = card.querySelector(".img_preview_posterior");
    if (file) { preview.src = URL.createObjectURL(file); preview.classList.add("visible"); }
    else if (!card.dataset.img_posterior) preview.classList.remove("visible");
  });

  // Preview docs
  card.querySelector(".docs_producto").addEventListener("change", (e) => {
    const files = e.target.files;
    card.querySelector(".docs_hint").innerText = `${files.length} archivos (nuevos)`;
    const previewContainer = card.querySelector(".docs_preview");
    previewContainer.innerHTML = "";
    Array.from(files).forEach(file => {
      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.style.cssText = "width:40px;height:40px;object-fit:cover";
        img.className = "rounded border shadow-sm";
        previewContainer.appendChild(img);
      } else {
        const badge = document.createElement("span");
        badge.className = "badge bg-secondary d-flex align-items-center justify-content-center text-white";
        badge.style.cssText = "width:40px;height:40px;font-size:10px";
        badge.innerText = file.name.split('.').pop().toUpperCase();
        previewContainer.appendChild(badge);
      }
    });
  });

  // Actualizar label al escribir título
  card.querySelector(".nombre").addEventListener("input", () => actualizarLabelCard(card));

  container.appendChild(card);

  // ==========================================
  // CASCADA
  // ==========================================

  // 1. Categoría → Subcategoría
  $(card).find('.categoria').on('change', function () {
    const sels = $(this).val() || [];
    const $sub = $(card).find('.sub_categoria');
    const currentSubVals = $sub.val() || [];
    $sub.empty();
    sels.forEach(catId => {
      const cat = (categorias || []).find(c => String(c.id_categoria) === String(catId));
      if (cat && Array.isArray(cat.sub_categorias)) {
        cat.sub_categorias.forEach(sc => {
          if ($sub.find(`option[value="${sc.id_sub_categoria}"]`).length === 0) {
            $sub.append(new Option(sc.nombre, sc.id_sub_categoria, false, false));
          }
        });
      }
    });
    $sub.val(currentSubVals).trigger('change.select2');
  });

  // 2. Referencias → Modelos
  $(card).find('.referencias').on('change', async function () {
    if (card.dataset.isloading === "1") return;
    const refs = $(this).val() || [];
    const ref = Array.isArray(refs) ? refs[0] : refs;
    const $mod = $(card).find('.id_modelo');
    const currentModVals = $mod.val() || [];
    if (!ref) { $mod.empty().trigger('change.select2'); return; }
    try {
      const r = await $.get('../api/v1/fulmuv/getModelosByReferencia/' + ref);
      const data = typeof r === "string" ? JSON.parse(r) : r;
      $mod.empty();
      (data.data || []).forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos, false, false)));
      $mod.val(currentModVals).trigger('change.select2');
    } catch (e) { console.error("Error cargando modelos:", e); }
  });

  // 3. Modelo → Auto-relleno
  $(card).find('.id_modelo').on('change', async function () {
    if (card.dataset.isloading === "1") return;
    const mods = $(this).val() || [];
    const id_modelo = Array.isArray(mods) ? mods[0] : mods;
    if (!id_modelo || id_modelo === 'nuevo') return;
    try {
      const r = await $.get('../api/v1/fulmuv/getModeloById/' + id_modelo);
      const data = typeof r === "string" ? JSON.parse(r) : r;
      if (data.error || !data.data) return;
      const d = data.data;
      const setAutoVal = (selector, val) => {
        if (!val) return;
        const $sel = $(card).find(selector);
        const arr = Array.isArray(val) ? val : [String(val)];
        const current = $sel.val() || [];
        $sel.val([...new Set([...current, ...arr])]).trigger('change.select2');
      };
      setAutoVal('.id_marca', d.id_marca);
      setAutoVal('.tipo_auto', d.id_tipo_auto);
      setAutoVal('.tipo_traccion', d.id_tipo_traccion);
      setAutoVal('.funcionamiento_motor', d.id_funcionamiento_motor);
    } catch (e) { console.error("Error asignando datos del modelo:", e); }
  });

  // Inicializar Select2
  $(card).find("select").each(function () {
    const isMultiple = this.multiple;
    const $sel = $(this);
    if ($sel.data("select2")) $sel.select2("destroy");

    $sel.select2({
      theme: "bootstrap-5",
      width: "100%",
      placeholder: "Seleccione...",
      dropdownParent: $(document.body),
      closeOnSelect: !isMultiple,
      tags: true,
      allowClear: true,
      createTag: function (params) {
        let term = $.trim(params.term || "").toUpperCase();
        if (term.length > 100) term = term.substring(0, 100);
        if (!term) return null;
        return { id: term, text: term, newTag: true };
      }
    });

    if ($sel.hasClass("id_marca"))
      wireSelectEnsure($sel, { entity: "marcas", label: "Marca vehículo", requireParents: false,
        onCreated: (id, txt) => marcas.push({ id_marca: id, nombre: txt }) });
    if ($sel.hasClass("tipo_auto"))
      wireSelectEnsure($sel, { entity: "tipos_auto", label: "Tipo de vehículo", requireParents: false,
        onCreated: (id, txt) => tiposAuto.push({ id_tipo_auto: id, nombre: txt }) });
    if ($sel.hasClass("tipo_traccion"))
      wireSelectEnsure($sel, { entity: "tipo_traccion", label: "Tracción", requireParents: false,
        onCreated: (id, txt) => tiposTraccion.push({ id_tipo_traccion: id, nombre: txt }) });
    if ($sel.hasClass("funcionamiento_motor"))
      wireSelectEnsure($sel, { entity: "funcionamiento_motor", label: "Funcionamiento de motor", requireParents: false,
        onCreated: (id, txt) => funcionamiento_motor.push({ id_funcionamiento_motor: id, nombre: txt }) });
    if ($sel.hasClass("id_marca_producto"))
      wireSelectEnsure($sel, { entity: "marcas_productos", label: "Marca de producto", requireParents: false,
        onCreated: (id, txt) => marcasProductos.push({ id_marca_producto: id, nombre: txt }) });
    if ($sel.hasClass("referencias"))
      wireSelectEnsure($sel, { entity: "referencias", label: "Referencia", requireParents: false,
        onCreated: (id, txt) => referencias.push(txt) });
    if ($sel.hasClass("categoria"))
      wireSelectEnsure($sel, { entity: "categorias", label: "Categoría", requireParents: false });

    if ($sel.hasClass("sub_categoria")) {
      wireSelectEnsure($sel, {
        entity: "sub_categorias", label: "Subcategoría", requireParents: true,
        parents: function () {
          return { id_categoria: firstNumericIdFromMulti(card.querySelector(".categoria")) };
        }
      });
    }

    if ($sel.hasClass("id_modelo")) {
      wireSelectEnsure($sel, {
        entity: "modelos_autos", label: "Modelo", requireParents: true,
        parents: function () {
          return {
            id_marca: firstNumericIdFromMulti(card.querySelector(".id_marca")),
            id_tipo_auto: firstNumericIdFromMulti(card.querySelector(".tipo_auto")),
            id_tipo_traccion: firstNumericIdFromMulti(card.querySelector(".tipo_traccion")),
            id_funcionamiento_motor: firstNumericIdFromMulti(card.querySelector(".funcionamiento_motor")),
          };
        }
      });
    }
  });

  actualizarNumerosFilas();
  return card;
}

// ==========================================
// LABEL & NÚMERO HELPERS
// ==========================================
function actualizarLabelCard(card) {
  const titulo = (card.querySelector(".nombre")?.value || "").trim();
  const lbl = card.querySelector(".veh-label");
  if (lbl) lbl.textContent = titulo || "Nuevo producto";
}

function actualizarNumerosFilas() {
  container.querySelectorAll(".veh-card").forEach((card, i) => {
    const badge = card.querySelector(".veh-num");
    if (badge) badge.textContent = `#${i + 1}`;
  });
}

// ==========================================
// GET ROWS DATA
// ==========================================
function getSelectedValues(selectEl) {
  if (!selectEl) return [];
  return Array.from(selectEl.selectedOptions).map(o => o.value).filter(Boolean);
}

function getRowsData() {
  const cards = container.querySelectorAll(".veh-card");
  return Array.from(cards).map((card, index) => {
    const imgFrontalFile = card.querySelector(".img_frontal")?.files?.[0] || null;
    const imgPosteriorFile = card.querySelector(".img_posterior")?.files?.[0] || null;
    return {
      nodoTR: card,
      index,
      id_producto: card.dataset.id_producto ? Number(card.dataset.id_producto) : null,
      publicar: card.querySelector(".chk_publicar")?.checked ? 1 : 0,
      titulo_producto: (card.querySelector(".nombre")?.value || "").trim(),
      codigo: (card.querySelector(".codigo")?.value || "").trim(),
      descripcion: (card.querySelector(".descripcion")?.value || "").trim(),
      precio_referencia: parseFloat(card.querySelector(".precio")?.value || "0"),
      descuento: parseFloat(card.querySelector(".descuento")?.value || "0"),
      peso: parseFloat(card.querySelector(".peso")?.value || "0"),
      iva: card.querySelector(".iva")?.checked ? 1 : 0,
      negociable: card.querySelector(".negociable")?.checked ? 1 : 0,
      tags: (card.querySelector(".tags")?.value || "").trim(),
      id_marca_producto: card.querySelector(".id_marca_producto")?.value || "",
      id_marca: getSelectedValues(card.querySelector(".id_marca")),
      id_modelo: getSelectedValues(card.querySelector(".id_modelo")),
      tipo_auto: getSelectedValues(card.querySelector(".tipo_auto")),
      tipo_traccion: getSelectedValues(card.querySelector(".tipo_traccion")),
      categoria: getSelectedValues(card.querySelector(".categoria")),
      sub_categoria: getSelectedValues(card.querySelector(".sub_categoria")),
      referencias: getSelectedValues(card.querySelector(".referencias")),
      funcionamiento_motor: getSelectedValues(card.querySelector(".funcionamiento_motor")),
      files: {
        img_frontal: imgFrontalFile,
        img_posterior: imgPosteriorFile,
        docs: card.querySelector(".docs_producto")?.files ? Array.from(card.querySelector(".docs_producto").files) : []
      },
      imagenFrontalEdit: imgFrontalFile ? 1 : 0,
      imagenPosteriorEdit: imgPosteriorFile ? 1 : 0,
      img_frontal_actual: card.dataset.img_frontal || "",
      img_posterior_actual: card.dataset.img_posterior || "",
      archivos_actual: card.dataset.archivos_actual || "[]"
    };
  });
}

// ==========================================
// POST helper & UPLOADS
// ==========================================
function postJQ(url, data) {
  return new Promise((resolve, reject) => {
    $.post(url, data, resp => resolve(resp)).fail(xhr => reject(new Error(xhr.responseText || "Error POST")));
  });
}

function subirImagenesPrincipalesFila(imgFrontalFile, imgPosteriorFile) {
  return new Promise((resolve, reject) => {
    if (!imgFrontalFile && !imgPosteriorFile) { resolve({ img_frontal: "", img_posterior: "" }); return; }
    const formData = new FormData();
    if (imgFrontalFile) formData.append("img_frontal", imgFrontalFile);
    if (imgPosteriorFile) formData.append("img_posterior", imgPosteriorFile);
    $.ajax({
      url: "../admin/cargar_imagenes_frontales_masivo.php", method: "POST",
      data: formData, processData: false, contentType: false, dataType: "json",
      success: res => {
        if (res?.response === "success") {
          resolve({ img_frontal: res.data?.img_frontal || "", img_posterior: res.data?.img_posterior || "" });
        } else {
          SweetAlert("error", res?.error || "Error al subir las imágenes principales.");
          reject(new Error(res?.error || "Error al subir imágenes"));
        }
      },
      error: () => { SweetAlert("error", "Error de red al subir imágenes principales."); reject(new Error("Error de red")); }
    });
  });
}

function saveFiles(files) {
  return new Promise((resolve, reject) => {
    if (!files || !files.length) { resolve([]); return; }
    const formData = new FormData();
    files.forEach(file => formData.append("archivos[]", file));
    $.ajax({
      type: "POST", url: "../admin/cargar_imagen_multiple.php",
      data: formData, cache: false, contentType: false, processData: false, dataType: "json",
      success: res => {
        if (res?.response === "success") {
          const arr = Array.isArray(res?.data?.archivos) ? res.data.archivos : (Array.isArray(res?.data) ? res.data : []);
          resolve(arr);
        } else {
          SweetAlert("error", res?.error || "Ocurrió un error al guardar los archivos.");
          reject(new Error(res?.error || "upload error"));
        }
      },
      error: xhr => reject(new Error(xhr?.responseText || "Error de red al subir archivos"))
    });
  });
}

// ==========================================
// BORRADOR: helpers
// ==========================================
function safeParseJSON(v) {
  if (!v) return [];
  if (Array.isArray(v)) return v;
  try { const parsed = JSON.parse(v); return Array.isArray(parsed) ? parsed : []; } catch (e) { return []; }
}

function setSelectMultiple(selectEl, values) {
  if (!selectEl) return;
  const set = new Set((values || []).map(String));
  Array.from(selectEl.options).forEach(opt => opt.selected = set.has(String(opt.value)));
  $(selectEl).trigger("change");
}

function normalizeText(v) {
  return String(v ?? "").replace(/<[^>]*>/g, "").trim().toLowerCase().replace(/\s+/g, " ").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

// ==========================================
// CARGAR BORRADOR — addRowWithData
// ==========================================
async function addRowWithData(p) {
  addRow();
  const card = container.lastElementChild;

  card.dataset.isloading = "1";
  card.dataset.id_producto = p.id_producto || "";

  card.querySelector(".nombre").value = p.titulo_producto || "";
  card.querySelector(".codigo").value = p.codigo || "";
  card.querySelector(".descripcion").value = p.descripcion || "";
  card.querySelector(".precio").value = p.precio_referencia || 0;
  card.querySelector(".descuento").value = p.descuento || 0;
  card.querySelector(".peso").value = p.peso || 0;
  card.querySelector(".iva").checked = String(p.iva) === "1";
  card.querySelector(".negociable").checked = String(p.negociable) === "1";
  card.querySelector(".tags").value = p.tags || "";

  card.dataset.img_frontal = p.img_frontal || "";
  card.dataset.img_posterior = p.img_posterior || "";

  if (p.img_frontal) {
    const prev = card.querySelector(".img_preview_frontal");
    prev.src = '../admin/' + p.img_frontal;
    prev.classList.add("visible");
  }
  if (p.img_posterior) {
    const prev = card.querySelector(".img_preview_posterior");
    prev.src = '../admin/' + p.img_posterior;
    prev.classList.add("visible");
  }

  let archivos = Array.isArray(p.archivos) ? p.archivos : [];
  card.dataset.archivos_actual = JSON.stringify(archivos);
  const previewContainer = card.querySelector(".docs_preview");
  const hintElement = card.querySelector(".docs_hint");
  if (archivos.length > 0) {
    if (hintElement) hintElement.innerText = `${archivos.length} archivos (guardados)`;
    archivos.forEach(arc => {
      const fileUrl = typeof arc === 'string' ? arc : (arc?.archivo || arc?.url || "");
      if (!fileUrl) return;
      const esImagen = (arc?.tipo === "imagen") || fileUrl.split('?')[0].match(/\.(jpeg|jpg|gif|png|webp)$/i);
      if (esImagen) {
        const img = document.createElement("img");
        img.src = '../admin/' + fileUrl;
        img.style.cssText = "width:40px;height:40px;object-fit:cover";
        img.className = "rounded border shadow-sm";
        previewContainer.appendChild(img);
      } else {
        const badge = document.createElement("span");
        badge.className = "badge bg-info d-flex align-items-center justify-content-center text-white";
        badge.style.cssText = "width:40px;height:40px;font-size:10px";
        badge.innerText = fileUrl.split('.').pop().toUpperCase();
        previewContainer.appendChild(badge);
      }
    });
  }

  if (p.marca_producto) card.querySelector(".id_marca_producto").value = p.marca_producto;
  $(card).find(".id_marca_producto").trigger("change");

  // Categoría → dispara sub_categoría
  setSelectMultiple(card.querySelector(".categoria"), safeParseJSON(p.categoria));
  $(card).find('.categoria').trigger('change');
  setSelectMultiple(card.querySelector(".sub_categoria"), safeParseJSON(p.sub_categoria));

  // Referencias → carga modelos
  const refsArr = safeParseJSON(p.referencias);
  setSelectMultiple(card.querySelector(".referencias"), refsArr);
  if (refsArr.length > 0) {
    const ref = Array.isArray(refsArr) ? refsArr[0] : refsArr;
    try {
      const r = await $.get('../api/v1/fulmuv/getModelosByReferencia/' + ref);
      const data = typeof r === "string" ? JSON.parse(r) : r;
      const $mod = $(card).find('.id_modelo');
      $mod.empty();
      (data.data || []).forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos, false, false)));
    } catch (e) { console.error(e); }
  }

  setSelectMultiple(card.querySelector(".id_modelo"), safeParseJSON(p.id_modelo));
  setSelectMultiple(card.querySelector(".id_marca"), safeParseJSON(p.id_marca));
  setSelectMultiple(card.querySelector(".tipo_auto"), safeParseJSON(p.tipo_auto));
  setSelectMultiple(card.querySelector(".tipo_traccion"), safeParseJSON(p.tipo_traccion));
  setSelectMultiple(card.querySelector(".funcionamiento_motor"), safeParseJSON(p.funcionamiento_motor));

  actualizarLabelCard(card);
  card.dataset.isloading = "0";
}

async function cargarBorrador() {
  try {
    productosEliminados = [];
    const id_empresa = $("#id_empresa").val();
    if (!id_empresa) return;
    const borrador = await fetchJSON(`../api/v1/fulmuv/productos/borrador/${id_empresa}`);
    container.innerHTML = "";
    if (!borrador || !borrador.length) { addRow(); return; }
    for (const p of borrador) { await addRowWithData(p); }
  } catch (e) { console.error(e); addRow(); }
}

function isEmptyStr(v) { return !String(v ?? "").trim(); }
function isEmptyArr(v) { return !Array.isArray(v) || v.length === 0; }

// ==========================================
// GUARDAR BORRADOR
// ==========================================
async function guardarBorrador() {
  const id_empresa = $("#id_empresa").val();
  if (!id_empresa) { SweetAlert("error", "No se encontró la empresa (id_empresa)."); return; }

  const productos = getRowsData();
  const btnGuardar = document.getElementById("btnGuardarBorrador");
  const btnPublicar = document.getElementById("btnPublicar");
  const textoOriginal = btnGuardar.innerText;

  function isRowEmpty(p) {
    const hasText = !isEmptyStr(p.titulo_producto) || !isEmptyStr(p.codigo) || !isEmptyStr(p.descripcion) || !isEmptyStr(p.tags);
    const hasNumbers = Number(p.precio_referencia) > 0 || Number(p.descuento) > 0;
    const hasArrays =
      (Array.isArray(p.categoria) && p.categoria.length) || (Array.isArray(p.id_marca) && p.id_marca.length) ||
      (Array.isArray(p.id_modelo) && p.id_modelo.length) || (Array.isArray(p.tipo_auto) && p.tipo_auto.length) ||
      (Array.isArray(p.tipo_traccion) && p.tipo_traccion.length) || (Array.isArray(p.referencias) && p.referencias.length) ||
      (Array.isArray(p.funcionamiento_motor) && p.funcionamiento_motor.length);
    const hasFiles = !!p.files?.img_frontal || !!p.files?.img_posterior || (Array.isArray(p.files?.docs) && p.files.docs.length);
    return !(hasText || hasNumbers || hasArrays || hasFiles);
  }

  try {
    btnGuardar.disabled = true; btnPublicar.disabled = true;
    btnGuardar.innerText = "Procesando...";
    const okCreate = [], okUpdate = [], skipped = [], fail = [];

    for (let i = 0; i < productos.length; i++) {
      const p = productos[i];
      const filaHuman = p.index + 1;
      if (isRowEmpty(p)) { skipped.push(filaHuman); continue; }

      try {
        let img_frontal_final = p.img_frontal_actual || "";
        let img_posterior_final = p.img_posterior_actual || "";
        const seleccionoFrontal = !!p.files?.img_frontal;
        const seleccionoPosterior = !!p.files?.img_posterior;

        if (seleccionoFrontal || seleccionoPosterior) {
          const imagenes = await subirImagenesPrincipalesFila(p.files.img_frontal, p.files.img_posterior);
          if (imagenes.img_frontal) img_frontal_final = imagenes.img_frontal;
          if (imagenes.img_posterior) img_posterior_final = imagenes.img_posterior;
        }

        let archivosSubidos = [];
        if (p.files?.docs && p.files.docs.length) archivosSubidos = await saveFiles(p.files.docs);

        const basePayload = {
          nombre: "",
          descripcion: emojiToEntities(p.descripcion || ""),
          codigo: p.codigo || "",
          categoria: Array.isArray(p.categoria) ? p.categoria : [],
          sub_categoria: Array.isArray(p.sub_categoria) ? p.sub_categoria : [],
          tags: p.tags || "",
          precio_referencia: Number(p.precio_referencia || 0),
          descuento: Number(p.descuento || 0),
          peso: Number(p.peso || 0),
          img_frontal: img_frontal_final,
          img_posterior: img_posterior_final,
          archivos: { archivos: archivosSubidos },
          atributos: [],
          id_empresa,
          tipo_vehiculo: Array.isArray(p.tipo_auto) ? p.tipo_auto : [],
          modelo: Array.isArray(p.id_modelo) ? p.id_modelo : [],
          marca: Array.isArray(p.id_marca) ? p.id_marca : [],
          traccion: Array.isArray(p.tipo_traccion) ? p.tipo_traccion : [],
          referencias: Array.isArray(p.referencias) ? p.referencias : [],
          funcionamiento_motor: Array.isArray(p.funcionamiento_motor) ? p.funcionamiento_motor : [],
          titulo_producto: p.titulo_producto || "",
          marca_producto: p.id_marca_producto || "",
          iva: Number(p.iva || 0),
          negociable: Number(p.negociable || 0),
          tipo_creador: "empresa",
        };

        if (!p.id_producto) {
          const payloadCreate = { ...basePayload, estado: "P", tipo_producto: "producto" };
          const raw = await postJQ("../api/v1/fulmuv/productos/create", payloadCreate);
          const returned = typeof raw === "string" ? JSON.parse(raw) : raw;
          if (returned.error == false) okCreate.push(filaHuman);
          else fail.push({ fila: filaHuman, msg: returned.msg || "Error al crear" });
        } else {
          const payloadUpdate = { ...basePayload, id_producto: p.id_producto,
            imagenFrontalEdit: seleccionoFrontal ? 1 : 0, imagenPosteriorEdit: seleccionoPosterior ? 1 : 0 };
          const resp = await actualizarProducto(payloadUpdate);
          const r = typeof resp === "string" ? JSON.parse(resp) : resp;
          if (r.error == false) okUpdate.push(filaHuman);
          else fail.push({ fila: filaHuman, msg: r.msg || "Error al actualizar" });
        }
      } catch (e) { fail.push({ fila: filaHuman, msg: e.message || "Error" }); }
    }

    const okDelete = [];
    for (const idEliminar of productosEliminados) {
      try {
        const respDel = await postJQ("../api/v1/fulmuv/productos/delete", { id: idEliminar });
        const rDel = typeof respDel === "string" ? JSON.parse(respDel) : respDel;
        if (rDel.error === false) okDelete.push(idEliminar);
        else fail.push({ fila: "Borrado", msg: rDel.msg || `Error al eliminar ID ${idEliminar}` });
      } catch (e) { fail.push({ fila: "Borrado", msg: `Error de red al eliminar ID ${idEliminar}` }); }
    }
    productosEliminados = [];

    SweetAlert("success", `Borrador guardado.\nCreados: ${okCreate.length}\nActualizados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nSaltados (vacíos): ${skipped.length}\nErrores: ${fail.length}`);
    await cargarBorrador();

  } finally { btnGuardar.disabled = false; btnPublicar.disabled = false; btnGuardar.innerText = textoOriginal; }
}

// ==========================================
// PUBLICAR BORRADOR
// ==========================================
async function publicarBorrador() {
  const id_empresa = $("#id_empresa").val();
  if (!id_empresa) { SweetAlert("error", "No se encontró el ID de la empresa"); return; }

  const productos = getRowsData().filter(p => p.publicar === 1);
  if (!productos.length) { SweetAlert("warning", "Selecciona al menos una fila para publicar."); return; }

  const errores = [];
  for (const p of productos) {
    const fila = p.index + 1;
    const isNew = !p.id_producto;
    if (isEmptyStr(p.titulo_producto)) errores.push(`Fila ${fila}: Falta título`);
    if (isEmptyStr(p.descripcion)) errores.push(`Fila ${fila}: Falta descripción`);
    if (isEmptyStr(p.tags)) errores.push(`Fila ${fila}: Falta tags`);
    if (isEmptyArr(p.categoria)) errores.push(`Fila ${fila}: Falta categoría`);
    if (isEmptyArr(p.sub_categoria)) errores.push(`Fila ${fila}: Falta subcategoría`);
    if (isEmptyArr(p.referencias)) errores.push(`Fila ${fila}: Falta referencia`);
    if (isEmptyArr(p.id_modelo)) errores.push(`Fila ${fila}: Falta modelo`);
    if (isEmptyArr(p.tipo_auto)) errores.push(`Fila ${fila}: Falta tipo de vehículo`);
    if (isEmptyArr(p.id_marca)) errores.push(`Fila ${fila}: Falta marca vehículo`);
    if (isEmptyArr(p.tipo_traccion)) errores.push(`Fila ${fila}: Falta tracción`);
    if (isEmptyArr(p.funcionamiento_motor)) errores.push(`Fila ${fila}: Falta funcionamiento de motor`);
    if (!(Number(p.precio_referencia) > 0)) errores.push(`Fila ${fila}: Falta precio (debe ser > 0)`);
    if (isNew) {
      if (!p.files?.img_frontal) errores.push(`Fila ${fila}: Falta imagen frontal`);
      if (!p.files?.img_posterior) errores.push(`Fila ${fila}: Falta imagen posterior`);
    } else {
      if (!p.img_frontal_actual && !p.files?.img_frontal) errores.push(`Fila ${fila}: Falta imagen frontal`);
      if (!p.img_posterior_actual && !p.files?.img_posterior) errores.push(`Fila ${fila}: Falta imagen posterior`);
    }
  }
  if (errores.length) { SweetAlert("error", "Corrige estos campos:\n\n" + errores.join("\n")); return; }

  const btnGuardar = document.getElementById("btnGuardarBorrador");
  const btnPublicar = document.getElementById("btnPublicar");
  const textoOriginal = btnPublicar.innerText;

  try {
    btnGuardar.disabled = true; btnPublicar.disabled = true;
    btnPublicar.innerText = "Procesando...";
    const idsExistentesParaPublicar = [], okCreate = [], okUpdate = [], fail = [];

    for (const p of productos) {
      try {
        let img_frontal_final = p.img_frontal_actual || "";
        let img_posterior_final = p.img_posterior_actual || "";
        const seleccionoFrontal = !!p.files?.img_frontal;
        const seleccionoPosterior = !!p.files?.img_posterior;
        if (seleccionoFrontal || seleccionoPosterior) {
          const imagenes = await subirImagenesPrincipalesFila(p.files.img_frontal, p.files.img_posterior);
          if (imagenes.img_frontal) img_frontal_final = imagenes.img_frontal;
          if (imagenes.img_posterior) img_posterior_final = imagenes.img_posterior;
        }
        let archivosSubidos = [];
        if (p.files?.docs && p.files.docs.length) archivosSubidos = await saveFiles(p.files.docs);

        const basePayload = {
          nombre: "",
          descripcion: emojiToEntities(p.descripcion),
          codigo: p.codigo,
          categoria: p.categoria || [],
          sub_categoria: p.sub_categoria || [],
          tags: p.tags,
          precio_referencia: p.precio_referencia,
          descuento: p.descuento,
          peso: p.peso,
          img_frontal: img_frontal_final,
          img_posterior: img_posterior_final,
          archivos: { archivos: archivosSubidos },
          atributos: [],
          id_empresa,
          tipo_vehiculo: p.tipo_auto || [],
          modelo: p.id_modelo || [],
          marca: p.id_marca || [],
          traccion: p.tipo_traccion || [],
          referencias: p.referencias || [],
          funcionamiento_motor: p.funcionamiento_motor || [],
          titulo_producto: p.titulo_producto,
          marca_producto: p.id_marca_producto,
          iva: p.iva,
          negociable: p.negociable,
          tipo_creador: "empresa",
          tipo_producto: "producto"
        };

        if (!p.id_producto) {
          const payloadCreate = { ...basePayload, estado: "A" };
          const raw = await postJQ("../api/v1/fulmuv/productos/create", payloadCreate);
          const returned = typeof raw === "string" ? JSON.parse(raw) : raw;
          if (returned.error == false) {
            okCreate.push(p.index + 1);
            const newId = returned.data?.id_producto || returned.data?.id || null;
            if (newId && p.nodoTR) p.nodoTR.dataset.id_producto = newId;
          } else {
            fail.push({ fila: p.index + 1, msg: returned.msg || "Error al crear/publicar" });
          }
        } else {
          const payloadUpdate = { ...basePayload, id_producto: p.id_producto,
            imagenFrontalEdit: seleccionoFrontal ? 1 : 0, imagenPosteriorEdit: seleccionoPosterior ? 1 : 0 };
          const resp = await actualizarProducto(payloadUpdate);
          const r = typeof resp === "string" ? JSON.parse(resp) : resp;
          if (r.error == false) { okUpdate.push(p.index + 1); idsExistentesParaPublicar.push(Number(p.id_producto)); }
          else fail.push({ fila: p.index + 1, msg: r.msg || "Error al actualizar" });
        }
      } catch (e) { fail.push({ fila: p.index + 1, msg: e.message || "Error" }); }
    }

    if (idsExistentesParaPublicar.length) {
      const respPub = await postJQ("../api/v1/fulmuv/productos/publicar_seleccionados",
        { id_empresa, ids: JSON.stringify(idsExistentesParaPublicar) });
      const pub = typeof respPub === "string" ? JSON.parse(respPub) : respPub;
      if (pub.error !== false) fail.push({ fila: "-", msg: pub.msg || "No se pudo publicar los existentes" });
    }

    const okDelete = [];
    for (const idEliminar of productosEliminados) {
      try {
        const respDel = await postJQ("../api/v1/fulmuv/productos/delete", { id: idEliminar });
        const rDel = typeof respDel === "string" ? JSON.parse(respDel) : respDel;
        if (rDel.error === false) okDelete.push(idEliminar);
        else fail.push({ fila: "Borrado", msg: rDel.msg || `Error al eliminar ID ${idEliminar}` });
      } catch (e) { fail.push({ fila: "Borrado", msg: `Error de red al eliminar ID ${idEliminar}` }); }
    }
    productosEliminados = [];

    SweetAlert("success", `Publicación completada.\nCreados y publicados: ${okCreate.length}\nActualizados y publicados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nErrores: ${fail.length}`);
    await cargarBorrador();

  } finally { btnGuardar.disabled = false; btnPublicar.disabled = false; btnPublicar.innerText = textoOriginal; }
}

// ==========================================
// UTILS
// ==========================================
function actualizarProducto(payload) {
  return postJQ("../api/v1/fulmuv/productos/edit", payload);
}

function emojiToEntities(str) {
  try {
    return str.replace(/\p{Extended_Pictographic}/gu, m => Array.from(m).map(ch => `&#${ch.codePointAt(0)};`).join(''));
  } catch (e) {
    return Array.from(str).map(ch => { const cp = ch.codePointAt(0); return cp > 0xFFFF ? `&#${cp};` : ch; }).join('');
  }
}

function swalConfirmV1(title, text, okText, cancelText, onOk, onCancel) {
  swal(
    { title, text, type: "info", showCancelButton: true, confirmButtonText: okText || "Sí", cancelButtonText: cancelText || "No", closeOnConfirm: true, closeOnCancel: true },
    function (isConfirm) { if (isConfirm) { if (typeof onOk === "function") onOk(); } else { if (typeof onCancel === "function") onCancel(); } }
  );
}

function ensureRemote(entity, nombre, parents) {
  var payload = $.extend({ entity: entity, nombre: nombre }, parents || {});
  return $.post("../api/v1/fulmuv/catalog/ensure", payload).then(function (raw) {
    var r = typeof raw === "string" ? JSON.parse(raw) : raw;
    if (r.error) return $.Deferred().reject(r.msg || "No se pudo registrar").promise();
    return r.id;
  });
}

function firstNumericIdFromMulti(selectEl) {
  if (!selectEl) return 0;
  const vals = Array.from(selectEl.selectedOptions || []).map(o => String(o.value)).filter(Boolean);
  for (const v of vals) { if (/^\d+$/.test(v)) return parseInt(v, 10); }
  return 0;
}

function wireSelectEnsure($el, cfg) {
  $el.off("select2:opening.ensure").on("select2:opening.ensure", function () { $(this).data("prev", $(this).val()); });

  $el.off("select2:select.ensure").on("select2:select.ensure", function (e) {
    var data = e.params.data || {};
    var val = data.id;
    var txt = (data.text || "").trim();
    var isNumeric = /^\d+$/.test(String(val));
    var isNew = data.newTag === true || (!data.element && !isNumeric) || val === "nuevo";
    if (!isNew) return;

    var parents = cfg.parents && typeof cfg.parents === "function" ? cfg.parents() || {} : {};

    if (cfg.requireParents) {
      for (var k in parents) {
        if (parents.hasOwnProperty(k) && (!parents[k] || +parents[k] <= 0)) {
          swal("Falta seleccionar", "Debes seleccionar primero el campo relacionado para registrar " + (cfg.label || cfg.entity).toLowerCase() + ".", "warning");
          $el.val($el.data("prev") || null).trigger("change");
          return;
        }
      }
    }

    swalConfirmV1(
      "Registrar nuevo " + (cfg.label || cfg.entity),
      '¿Deseas registrar "' + txt + '"?',
      "Sí, registrar", "Cancelar",
      function () {
        ensureRemote(cfg.entity, txt, parents).then(function (id) {
          $el.find("option").filter(function () { return $(this).val() == val; }).remove();
          var newOpt = new Option(txt, id, true, true);
          $el.append(newOpt).trigger("change");
          if (typeof cfg.onCreated === "function") cfg.onCreated(id, txt, parents);
          swal("Listo", (cfg.label || cfg.entity) + " registrado correctamente.", "success");
        }).fail(function (msg) {
          swal("Error", msg?.toString?.() || "No se pudo registrar.", "error");
          $el.val($el.data("prev") || null).trigger("change");
        });
      },
      function () { $el.val($el.data("prev") || null).trigger("change"); }
    );
  });
}
