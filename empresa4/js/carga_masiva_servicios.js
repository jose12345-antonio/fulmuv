// ==========================================
// VARIABLES GLOBALES
// ==========================================
let marcas = [];
let modelos = [];
let tiposAuto = [];
let tiposTraccion = [];
let funcionamiento_motor = [];
let categorias = [];
let subcategorias = [];
let nombresProductos = [];   // aquí cargas nombres_servicios
let referencias = [];

// Memoria temporal para eliminar servicios de la BD
let serviciosEliminados = []; 

const tbody = document.getElementById("tbodyProductos");

// ==========================================
// EVENT LISTENERS & INIT
// ==========================================
document.getElementById("btnAddRow").addEventListener("click", () => addRow());

document.getElementById("btnClear").addEventListener("click", () => {
  // Guardar los IDs que ya estaban en BD para eliminarlos luego
  tbody.querySelectorAll("tr").forEach(tr => {
    if (tr.dataset.id_producto) serviciosEliminados.push(tr.dataset.id_producto);
  });
  tbody.innerHTML = "";
  addRow();
});

document.getElementById("btnGuardarBorrador").addEventListener("click", guardarBorrador);
document.getElementById("btnPublicar").addEventListener("click", publicarBorrador);

document.addEventListener("DOMContentLoaded", async () => {
  await cargarCombos();
  await cargarBorrador();
});

// ==========================================
// 1) FETCH JSON (BLINDADO CON TRY/CATCH)
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
// 2) CARGAR COMBOS
// ==========================================
async function cargarCombos() {
  marcas = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/marcas/"), "nombre");
  tiposAuto = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tiposAuto/"), "nombre");
  tiposTraccion = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tipo_tracccion/"), "nombre");
  funcionamiento_motor = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getFuncionamientoMotor/"), "nombre");
  
  // ✅ Categorías incluye subcategorías anidadas
  categorias = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/categorias/?tipo=servicio"), "nombre");
  
  nombresProductos = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/nombres_servicios/"), "nombre");
  referencias = orderNAandVariosFirstStrings(await fetchJSON("../api/v1/fulmuv/getReferencias/"));

  addRow();
}

function normTxt(v) {
  return String(v ?? "").trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); 
}

function orderNAandVariosFirstObjects(arr, textKey = "nombre") {
  const TOP = new Set(["N/A", "NA", "NO APLICA", "VARIOS"]);
  return (arr || []).slice().sort((a, b) => {
    const A = normTxt(a?.[textKey]);
    const B = normTxt(b?.[textKey]);
    const aTop = TOP.has(A);
    const bTop = TOP.has(B);

    if (aTop && !bTop) return -1;
    if (!aTop && bTop) return 1;

    const pri = (x) => (x === "N/A" || x === "NA" || x === "NO APLICA") ? 0 : (x === "VARIOS" ? 1 : 2);
    const pa = pri(A), pb = pri(B);
    if (pa !== pb) return pa - pb;

    return A.localeCompare(B, "es");
  });
}

function orderNAandVariosFirstStrings(arr) {
  const TOP = new Set(["N/A", "NA", "NO APLICA", "VARIOS"]);
  return (arr || []).slice().sort((a, b) => {
    const A = normTxt(a);
    const B = normTxt(b);
    const aTop = TOP.has(A);
    const bTop = TOP.has(B);

    if (aTop && !bTop) return -1;
    if (!aTop && bTop) return 1;

    const pri = (x) => (x === "N/A" || x === "NA" || x === "NO APLICA") ? 0 : (x === "VARIOS" ? 1 : 2);
    const pa = pri(A), pb = pri(B);
    if (pa !== pb) return pa - pb;

    return A.localeCompare(B, "es");
  });
}

// ==========================================
// 3) OPTIONS HELPERS
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
    const val = String(s);
    html += `<option value="${val}">${val}</option>`;
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
// 4) ADD ROW (SERVICIOS CON CASCADA)
// ==========================================
function addRow() {
  const tr = document.createElement("tr");

  tr.innerHTML = `
    <td class="text-center">
      <button class="btn btn-sm btn-outline-danger btnDel" type="button">✕</button>
    </td>
    <td class="chk-cell text-center">
      <div class="chk-wrap">
        <input class="form-check-input chk_publicar" type="checkbox" title="Marcar para publicar">
      </div>
    </td>

    <td><input class="form-control form-control-sm nombre" placeholder="Título"></td>

    <td>
      <select class="form-select form-select-md id_nombre_producto">
        ${optionListNombreTexto(nombresProductos)}
      </select>
    </td>

    <td><textarea class="form-control form-control-sm descripcion" rows="1" placeholder="Descripción"></textarea></td>

    <td>
      <select class="form-select form-select-sm id_marca" multiple>
        ${optionList(marcas, "id_marca", "nombre")}
      </select>
    </td>

    <td>
      <select class="form-select form-select-sm referencias" multiple>
        ${optionListStrings(referencias)}
      </select>
    </td>

    <td>
      <select class="form-select form-select-sm id_modelo" multiple></select>
    </td>

    <td>
      <select class="form-select form-select-sm tipo_auto" multiple>
        ${optionList(tiposAuto, "id_tipo_auto", "nombre")}
      </select>
    </td>

    <td>
      <select class="form-select form-select-sm tipo_traccion" multiple>
        ${optionList(tiposTraccion, "id_tipo_traccion", "nombre")}
      </select>
    </td>

    <td>
      <select class="form-select form-select-sm funcionamiento_motor" multiple>
        ${optionList(funcionamiento_motor, "id_funcionamiento_motor", "nombre")}
      </select>
    </td>

    <td>
      <select class="form-select form-select-sm categoria" multiple>
        ${optionList(categorias, "id_categoria", "nombre")}
      </select>
    </td>

    <td><input class="form-control form-control-sm precio" type="number" step="0.01" min="0" value="0"></td>
    <td><input class="form-control form-control-sm descuento" type="number" step="0.01" min="0" value="0"></td>
    <td><input class="form-control form-control-sm tags" placeholder="Ej: test,test"></td>

    <td>
      <div class="d-flex flex-column align-items-center gap-2">
        <img class="preview_frontal d-none rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" src="" alt="Frontal">
        <input class="form-control form-control-sm img_frontal" type="file" accept="image/*">
      </div>
    </td>
    <td>
      <div class="d-flex flex-column align-items-center gap-2">
        <img class="preview_posterior d-none rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" src="" alt="Posterior">
        <input class="form-control form-control-sm img_posterior" type="file" accept="image/*">
      </div>
    </td>

    <td>
      <input class="form-control form-control-sm docs_producto" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*">
      <small class="text-muted d-block mt-1 docs_hint">0 archivos</small>
      <div class="docs_preview d-flex flex-wrap gap-1 mt-1"></div>
    </td>

    <td class="text-center"><input class="form-check-input emergencia_24_7" type="checkbox"></td>
    <td class="text-center"><input class="form-check-input emergencia_carretera" type="checkbox"></td>
    <td class="text-center"><input class="form-check-input emergencia_domicilio" type="checkbox"></td>
  `;

  // Botón Eliminar
  tr.querySelector(".btnDel").addEventListener("click", () => {
    if (tr.dataset.id_producto) serviciosEliminados.push(tr.dataset.id_producto);
    $(tr).find("select").each(function () {
      if ($(this).data("select2")) $(this).select2("destroy");
    });
    tr.remove();
    if (tbody.children.length === 0) addRow();
  });

  // Previsualización de imágenes local
  tr.querySelector(".img_frontal").addEventListener("change", function(e) {
    const file = e.target.files[0];
    const preview = tr.querySelector(".preview_frontal");
    if (file) { preview.src = URL.createObjectURL(file); preview.classList.remove("d-none"); } 
    else { if (!tr.dataset.img_frontal) preview.classList.add("d-none"); }
  });

  tr.querySelector(".img_posterior").addEventListener("change", function(e) {
    const file = e.target.files[0];
    const preview = tr.querySelector(".preview_posterior");
    if (file) { preview.src = URL.createObjectURL(file); preview.classList.remove("d-none"); } 
    else { if (!tr.dataset.img_posterior) preview.classList.add("d-none"); }
  });

  // Previsualización dinámica de múltiples documentos
  tr.querySelector(".docs_producto").addEventListener("change", (e) => {
    const files = e.target.files;
    tr.querySelector(".docs_hint").innerText = `${files.length} archivos (nuevos)`;
    const previewContainer = tr.querySelector(".docs_preview");
    previewContainer.innerHTML = ""; 

    Array.from(files).forEach(file => {
      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.style.width = "40px"; img.style.height = "40px"; img.style.objectFit = "cover";
        img.className = "rounded border shadow-sm";
        previewContainer.appendChild(img);
      } else {
        const badge = document.createElement("span");
        badge.className = "badge bg-secondary d-flex align-items-center justify-content-center text-xs text-white";
        badge.style.width = "40px"; badge.style.height = "40px"; badge.style.fontSize = "10px";
        badge.innerText = file.name.split('.').pop().toUpperCase();
        previewContainer.appendChild(badge);
      }
    });
  });

  tbody.appendChild(tr);

  // ==========================================
  // ✅ LÓGICA EN CASCADA (DEPENDENCIAS)
  // ==========================================

  // 1. Categoría -> Subcategoría
  $(tr).find('.categoria').on('change', function () {
    const sels = $(this).val() || [];
    const $sub = $(tr).find('.sub_categoria');
    const currentSubVals = $sub.val() || [];
    
    $sub.empty();

    if (sels.length > 0) {
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
    }
    $sub.val(currentSubVals).trigger('change.select2');
  });

  // 2. Referencia -> Modelo
  $(tr).find('.referencias').on('change', async function () {
    if (tr.dataset.isloading === "1") return;

    const refs = $(this).val() || [];
    const ref = Array.isArray(refs) ? refs[0] : refs;
    const $mod = $(tr).find('.id_modelo');
    const currentModVals = $mod.val() || [];

    if (!ref) {
      $mod.empty().trigger('change.select2');
      return;
    }

    try {
      const returnedData = await $.get('../api/v1/fulmuv/getModelosByReferencia/' + ref);
      const r = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      
      $mod.empty();
      (r.data || []).forEach(m => {
        $mod.append(new Option(m.nombre, m.id_modelos_autos, false, false));
      });
      $mod.val(currentModVals).trigger('change.select2');
    } catch (e) {
      console.error("Error cargando modelos:", e);
    }
  });

  // 3. Modelo -> Auto-Relleno (Marca, Tipo, Tracción, Motor)
  $(tr).find('.id_modelo').on('change', async function () {
    if (tr.dataset.isloading === "1") return;

    const mods = $(this).val() || [];
    const id_modelo = Array.isArray(mods) ? mods[0] : mods;
    if (!id_modelo || id_modelo === 'nuevo') return;

    try {
      const returnedData = await $.get('../api/v1/fulmuv/getModeloById/' + id_modelo);
      const r = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      if (r.error || !r.data) return;

      const d = r.data;
      const setAutoVal = (selector, val) => {
        if (!val) return;
        const $sel = $(tr).find(selector);
        let arr = Array.isArray(val) ? val : [String(val)];
        let current = $sel.val() || [];
        let combined = [...new Set([...current, ...arr])];
        $sel.val(combined).trigger('change.select2');
      };

      setAutoVal('.id_marca', d.id_marca);
      setAutoVal('.tipo_auto', d.id_tipo_auto);
      setAutoVal('.tipo_traccion', d.id_tipo_traccion);
      setAutoVal('.funcionamiento_motor', d.id_funcionamiento_motor);
    } catch (e) {
      console.error("Error asignando datos del modelo:", e);
    }
  });


  // Inicializar Select2 + Autocreate
  $(tr).find("select").each(function () {
      const isMultiple = this.multiple;
      const $sel = $(this);

      if ($sel.data("select2")) $sel.select2("destroy");

      $sel.select2({
        theme: "bootstrap-5",
        width: "100%",
        placeholder: "Seleccione...",
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

      if ($sel.hasClass("id_marca")) wireSelectEnsure($sel, { entity: "marcas", label: "Marca", requireParents: false });
      if ($sel.hasClass("tipo_auto")) wireSelectEnsure($sel, { entity: "tipos_auto", label: "Tipo de vehículo", requireParents: false });
      if ($sel.hasClass("tipo_traccion")) wireSelectEnsure($sel, { entity: "tipo_traccion", label: "Tracción", requireParents: false });
      if ($sel.hasClass("funcionamiento_motor")) wireSelectEnsure($sel, { entity: "funcionamiento_motor", label: "Funcionamiento de motor", requireParents: false });
      if ($sel.hasClass("referencias")) wireSelectEnsure($sel, { entity: "referencias", label: "Referencia", requireParents: false });
      if ($sel.hasClass("categoria")) wireSelectEnsure($sel, { entity: "categorias", label: "Categoría", requireParents: false });

      if ($sel.hasClass("id_modelo")) {
        wireSelectEnsure($sel, {
          entity: "modelos_autos",
          label: "Modelo",
          requireParents: true,
          parents: function () {
            return {
              id_marca: firstNumericIdFromMulti(tr.querySelector(".id_marca")),
              id_tipo_auto: firstNumericIdFromMulti(tr.querySelector(".tipo_auto")),
              id_tipo_traccion: firstNumericIdFromMulti(tr.querySelector(".tipo_traccion")),
              id_funcionamiento_motor: firstNumericIdFromMulti(tr.querySelector(".funcionamiento_motor")),
            };
          },
        });
      }
    });

  return tr;
}

// ==========================================
// 5) GET ROWS DATA (INCLUYE nodoTR VITAL)
// ==========================================
function getSelectedValues(selectEl) {
  if (!selectEl) return [];
  return Array.from(selectEl.selectedOptions).map(o => o.value).filter(Boolean);
}

function getRowsData() {
  const rows = tbody.querySelectorAll("tr");
  const productos = [];

  rows.forEach((tr, index) => {
    const id_producto = tr.dataset.id_producto ? Number(tr.dataset.id_producto) : null;
    const nombreServicio = (tr.querySelector(".id_nombre_producto")?.value || "").trim();
    const imgFrontalFile = tr.querySelector(".img_frontal")?.files?.[0] || null;
    const imgPosteriorFile = tr.querySelector(".img_posterior")?.files?.[0] || null;

    productos.push({
      nodoTR: tr,
      index,
      id_producto,
      publicar: tr.querySelector(".chk_publicar")?.checked ? 1 : 0,

      titulo_servicio: tr.querySelector(".nombre").value.trim(),
      nombre: nombreServicio,
      descripcion: tr.querySelector(".descripcion").value.trim(),
      precio_referencia: parseFloat(tr.querySelector(".precio").value || "0"),
      descuento: parseFloat(tr.querySelector(".descuento").value || "0"),
      tags: tr.querySelector(".tags").value.trim(),

      emergencia_24_7: tr.querySelector(".emergencia_24_7").checked ? 1 : 0,
      emergencia_carretera: tr.querySelector(".emergencia_carretera").checked ? 1 : 0,
      emergencia_domicilio: tr.querySelector(".emergencia_domicilio").checked ? 1 : 0,

      id_marca: getSelectedValues(tr.querySelector(".id_marca")),
      id_modelo: getSelectedValues(tr.querySelector(".id_modelo")),
      tipo_auto: getSelectedValues(tr.querySelector(".tipo_auto")),
      tipo_traccion: getSelectedValues(tr.querySelector(".tipo_traccion")),
      categoria: getSelectedValues(tr.querySelector(".categoria")),
      referencias: getSelectedValues(tr.querySelector(".referencias")),
      funcionamiento_motor: getSelectedValues(tr.querySelector(".funcionamiento_motor")),

      files: {
        img_frontal: imgFrontalFile,
        img_posterior: imgPosteriorFile,
        docs: tr.querySelector(".docs_producto")?.files ? Array.from(tr.querySelector(".docs_producto").files) : []
      },
      
      imagenFrontalEdit: imgFrontalFile ? 1 : 0,
      imagenPosteriorEdit: imgPosteriorFile ? 1 : 0,
      img_frontal_actual: tr.dataset.img_frontal || "",
      img_posterior_actual: tr.dataset.img_posterior || "",
      archivos_actual: tr.dataset.archivos_actual || "[]"
    });
  });

  return productos;
}

// ==========================================
// 6) HELPERS POST & UPLOADS
// ==========================================
function postJQ(url, data) {
  return new Promise((resolve, reject) => {
    $.post(url, data, function (resp) {
      resolve(resp);
    }).fail(function (xhr) {
      reject(new Error(xhr.responseText || "Error POST"));
    });
  });
}

function subirImagenesPrincipalesFila(imgFrontalFile, imgPosteriorFile) {
  return new Promise(function (resolve, reject) {
    if (!imgFrontalFile && !imgPosteriorFile) {
      resolve({ img_frontal: "", img_posterior: "" });
      return;
    }

    const formData = new FormData();
    if (imgFrontalFile) formData.append("img_frontal", imgFrontalFile);
    if (imgPosteriorFile) formData.append("img_posterior", imgPosteriorFile);

    $.ajax({
      url: "../admin/cargar_imagenes_frontales_masivo.php", 
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (res) {
        if (res && res.response === "success") {
          resolve({
            img_frontal: res.data?.img_frontal || "",
            img_posterior: res.data?.img_posterior || ""
          });
        } else {
          SweetAlert("error", res?.error || "Error al subir las imágenes principales.");
          reject(new Error(res?.error || "Error al subir imágenes"));
        }
      },
      error: function () {
        SweetAlert("error", "Error de red al subir imágenes principales.");
        reject(new Error("Error de red"));
      }
    });
  });
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    if (!files || !files.length) {
      resolve([]);
      return;
    }

    const formData = new FormData();
    files.forEach(file => formData.append("archivos[]", file));

    $.ajax({
      type: "POST",
      url: "../admin/cargar_imagen_multiple.php",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (res) {
        if (res?.response === "success") {
          const arr = Array.isArray(res?.data?.archivos) ? res.data.archivos : (Array.isArray(res?.data) ? res.data : []);
          resolve(arr);
        } else {
          SweetAlert("error", res?.error || "Ocurrió un error al guardar los archivos.");
          reject(new Error(res?.error || "upload error"));
        }
      },
      error: function (xhr) {
        reject(new Error(xhr?.responseText || "Error de red al subir archivos"));
      }
    });
  });
}

// ==========================================
// 7-8-9) UTILS & CARGAR BORRADOR
// ==========================================
function safeParseJSON(v) {
  if (!v) return [];
  if (Array.isArray(v)) return v;
  try {
    const parsed = JSON.parse(v);
    return Array.isArray(parsed) ? parsed : [];
  } catch (e) {
    return [];
  }
}

function setSelectMultiple(selectEl, values) {
  if (!selectEl) return;
  const set = new Set((values || []).map(String));
  Array.from(selectEl.options).forEach(opt => {
    opt.selected = set.has(String(opt.value));
  });
  $(selectEl).trigger("change");
}

function normalizeText(v) {
  return String(v ?? "").replace(/<[^>]*>/g, "").trim().toLowerCase().replace(/\s+/g, " ").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function setSelectByText($select, text) {
  const target = normalizeText(text);
  if (!target) return false;

  let found = null;
  $select.find("option").each(function () {
    if (normalizeText($(this).text()) === target || normalizeText($(this).val()) === target) {
      found = $(this).val();
      return false;
    }
  });

  if (found !== null) {
    $select.val(found).trigger("change");
    return true;
  }
  return false;
}

async function addRowWithData(p) {
  addRow();
  const tr = tbody.lastElementChild;

  // Bloqueamos triggers automáticos para no borrar datos guardados
  tr.dataset.isloading = "1";

  tr.dataset.id_producto = p.id_producto || "";
  tr.querySelector(".nombre").value = p.titulo_producto || ""; 
  tr.querySelector(".descripcion").value = p.descripcion || "";
  tr.querySelector(".precio").value = p.precio_referencia || 0;
  tr.querySelector(".descuento").value = p.descuento || 0;
  tr.querySelector(".tags").value = p.tags || "";

  tr.querySelector(".emergencia_24_7").checked = String(p.emergencia_24_7) === "1";
  tr.querySelector(".emergencia_carretera").checked = String(p.emergencia_carretera) === "1";
  tr.querySelector(".emergencia_domicilio").checked = String(p.emergencia_domicilio) === "1";
  tr.dataset.img_frontal = p.img_frontal || "";
  tr.dataset.img_posterior = p.img_posterior || "";

  if (p.img_frontal) {
    const previewF = tr.querySelector(".preview_frontal");
    previewF.src = '../admin/'+p.img_frontal; 
    previewF.classList.remove("d-none");
  }

  if (p.img_posterior) {
    const previewP = tr.querySelector(".preview_posterior");
    previewP.src = '../admin/'+p.img_posterior; 
    previewP.classList.remove("d-none");
  }

  let archivos = Array.isArray(p.archivos) ? p.archivos : [];
  tr.dataset.archivos_actual = JSON.stringify(archivos);

  const previewContainer = tr.querySelector(".docs_preview");
  const hintElement = tr.querySelector(".docs_hint");
  
  if (archivos && archivos.length > 0) {
    if (hintElement) hintElement.innerText = `${archivos.length} archivos (guardados)`;
    
    archivos.forEach(arc => {
      const fileUrl = arc?.archivo || ""; 
      if (!fileUrl) return;

      const esImagen = arc?.tipo === "imagen" || fileUrl.match(/\.(jpeg|jpg|gif|png|webp)$/i);

      if (esImagen) {
        const img = document.createElement("img");
        img.src = '../admin/' + fileUrl;
        img.style.width = "40px"; img.style.height = "40px"; img.style.objectFit = "cover";
        img.className = "rounded border shadow-sm";
        previewContainer.appendChild(img);
      } else {
        const badge = document.createElement("span");
        badge.className = "badge bg-info d-flex align-items-center justify-content-center text-xs text-white";
        badge.style.width = "40px"; badge.style.height = "40px"; badge.style.fontSize = "10px";
        badge.innerText = fileUrl.split('.').pop().toUpperCase();
        previewContainer.appendChild(badge);
      }
    });
  }

  const $sel = $(tr).find(".id_nombre_producto");
  $sel.val(p.nombre || "").trigger("change");
  if (!$sel.val() && p.nombre) setSelectByText($sel, p.nombre);

  // ✅ Cargar Categoría (dispara carga de subcategoría localmente)
  setSelectMultiple(tr.querySelector(".categoria"), safeParseJSON(p.categoria));
  $(tr).find('.categoria').trigger('change'); 

  // ✅ Cargar Referencia y traer modelos
  const refsArr = safeParseJSON(p.referencias);
  setSelectMultiple(tr.querySelector(".referencias"), refsArr);

  if (refsArr.length > 0) {
    const ref = Array.isArray(refsArr) ? refsArr[0] : refsArr;
    try {
      const returnedData = await $.get('../api/v1/fulmuv/getModelosByReferencia/' + ref);
      const r = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      const $mod = $(tr).find('.id_modelo');
      $mod.empty();
      (r.data || []).forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos, false, false)));
    } catch(e) { console.error(e) }
  }

  // Llenar el resto de combos
  setSelectMultiple(tr.querySelector(".id_modelo"), safeParseJSON(p.id_modelo));
  setSelectMultiple(tr.querySelector(".id_marca"), safeParseJSON(p.id_marca));
  setSelectMultiple(tr.querySelector(".tipo_auto"), safeParseJSON(p.tipo_auto));
  setSelectMultiple(tr.querySelector(".tipo_traccion"), safeParseJSON(p.tipo_traccion));
  setSelectMultiple(tr.querySelector(".funcionamiento_motor"), safeParseJSON(p.funcionamiento_motor));

  // Desbloqueamos triggers
  tr.dataset.isloading = "0";
}

async function cargarBorrador() {
  try {
    serviciosEliminados = []; // Limpiar memoria de eliminados al cargar de BD
    const id_empresa = $("#id_empresa").val();
    if (!id_empresa) return;

    const borrador = await fetchJSON(`../api/v1/fulmuv/servicios/borrador/${id_empresa}`);

    tbody.innerHTML = "";

    if (!borrador || !borrador.length) {
      addRow();
      return;
    }

    // Carga secuencial para evitar cruces
    for (const p of borrador) {
      await addRowWithData(p);
    }
  } catch (e) {
    console.error(e);
    addRow();
  }
}

function isEmptyStr(v) { return !String(v ?? "").trim(); }
function isEmptyArr(v) { return !Array.isArray(v) || v.length === 0; }

// ==========================================
// 10) GUARDAR BORRADOR (CON TRY...FINALLY)
// ==========================================
async function guardarBorrador() {
  const id_empresa = $("#id_empresa").val();
  if (!id_empresa) {
    SweetAlert("error", "No se encontró la empresa (id_empresa).");
    return;
  }

  const productos = getRowsData();

  function isRowEmpty(p) {
    const hasText = !isEmptyStr(p.titulo_servicio) || !isEmptyStr(p.nombre) || !isEmptyStr(p.descripcion) || !isEmptyStr(p.tags);
    const hasNumbers = Number(p.precio_referencia) > 0 || Number(p.descuento) > 0;
    const hasArrays =
      (Array.isArray(p.categoria) && p.categoria.length) || (Array.isArray(p.id_marca) && p.id_marca.length) ||
      (Array.isArray(p.id_modelo) && p.id_modelo.length) || (Array.isArray(p.tipo_auto) && p.tipo_auto.length) ||
      (Array.isArray(p.tipo_traccion) && p.tipo_traccion.length) || (Array.isArray(p.referencias) && p.referencias.length) ||
      (Array.isArray(p.funcionamiento_motor) && p.funcionamiento_motor.length);
    const hasFiles = !!p.files?.img_frontal || !!p.files?.img_posterior || (Array.isArray(p.files?.docs) && p.files.docs.length);

    return !(hasText || hasNumbers || hasArrays || hasFiles);
  }

  const btnGuardar = document.getElementById("btnGuardarBorrador");
  const btnPublicar = document.getElementById("btnPublicar");
  const textoOriginal = btnGuardar.innerText;

  try {
    btnGuardar.disabled = true;
    btnPublicar.disabled = true;
    btnGuardar.innerText = "Procesando...";

    const okCreate = [];
    const okUpdate = [];
    const skipped = [];
    const fail = [];

    for (let i = 0; i < productos.length; i++) {
      const p = productos[i];
      const filaHuman = p.index + 1;

      if (isRowEmpty(p)) {
        skipped.push(filaHuman);
        continue;
      }

      try {
        let img_frontal_final = p.img_frontal_actual || "";
        let img_posterior_final = p.img_posterior_actual || "";

        const tieneNuevaFrontal = !!p.files?.img_frontal;
        const tieneNuevaPosterior = !!p.files?.img_posterior;

        if (tieneNuevaFrontal || tieneNuevaPosterior) {
          const imagenes = await subirImagenesPrincipalesFila(p.files.img_frontal, p.files.img_posterior);
          if (imagenes.img_frontal) img_frontal_final = imagenes.img_frontal;
          if (imagenes.img_posterior) img_posterior_final = imagenes.img_posterior;
        }

        let archivosSubidos = [];
        if (p.files?.docs && p.files.docs.length) {
          archivosSubidos = await saveFiles(p.files.docs); 
        }

        const archivosFinal = archivosSubidos;

        const basePayload = {
          nombre: (p.nombre || "").trim(),
          descripcion: emojiToEntities(p.descripcion || ""),
          codigo: "",
          precio_referencia: Number(p.precio_referencia || 0),
          descuento: Number(p.descuento || 0),
          peso: 0,
          img_frontal: img_frontal_final,
          img_posterior: img_posterior_final,
          
          archivos: {archivos: archivosFinal}, 
          
          iva: 0,
          emergencia_24_7: p.emergencia_24_7 ? 1 : 0,
          emergencia_carretera: p.emergencia_carretera ? 1 : 0,
          emergencia_domicilio: p.emergencia_domicilio ? 1 : 0,
          categoria: Array.isArray(p.categoria) ? p.categoria : [],
          sub_categoria: [],
          marca: Array.isArray(p.id_marca) ? p.id_marca : [],
          modelo: Array.isArray(p.id_modelo) ? p.id_modelo : [],
          tipo_vehiculo: Array.isArray(p.tipo_auto) ? p.tipo_auto : [],
          traccion: Array.isArray(p.tipo_traccion) ? p.tipo_traccion : [],
          referencias: Array.isArray(p.referencias) ? p.referencias : [],
          funcionamiento_motor: Array.isArray(p.funcionamiento_motor) ? p.funcionamiento_motor : [],
          atributos: [],
          id_empresa: id_empresa,
          tags: (p.tags || "").trim(),
          titulo_producto: (p.titulo_servicio || "").trim(),
          negociable: 0,
          tipo_creador: "empresa",
          tipo_producto: "servicio"
        };

        if (!p.id_producto) {
          const payloadCreate = { ...basePayload, estado: "P" };
          const returnedData = await postJQ("../api/v1/fulmuv/productos/create", payloadCreate);
          const returned = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;

          if (returned.error == false) okCreate.push(filaHuman);
          else fail.push({ fila: filaHuman, msg: returned.msg || "Error al crear" });

        } else {
          const payloadUpdate = {
            ...basePayload,
            id_producto: p.id_producto,
            imagenFrontalEdit: tieneNuevaFrontal ? 1 : 0,
            imagenPosteriorEdit: tieneNuevaPosterior ? 1 : 0,
          };
          const resp = await actualizarProducto(payloadUpdate);
          const r = typeof resp === "string" ? JSON.parse(resp) : resp;

          if (r.error == false) okUpdate.push(filaHuman);
          else fail.push({ fila: filaHuman, msg: r.msg || "Error al actualizar" });
        }
      } catch (e) {
        fail.push({ fila: filaHuman, msg: e.message || "Error" });
      }
    }

    // ===============================
    // ✅ PROCESAR ELIMINACIONES PENDIENTES
    // ===============================
    const okDelete = [];
    for (const idEliminar of serviciosEliminados) {
      try {
        const respDel = await postJQ("../api/v1/fulmuv/productos/delete", { id: idEliminar });
        const rDel = typeof respDel === "string" ? JSON.parse(respDel) : respDel;
        
        if (rDel.error === false) {
          okDelete.push(idEliminar);
        } else {
          fail.push({ fila: "Borrado", msg: rDel.msg || `Error al eliminar ID ${idEliminar}` });
        }
      } catch (e) {
        fail.push({ fila: "Borrado", msg: `Error de red al eliminar ID ${idEliminar}` });
      }
    }
    serviciosEliminados = []; // Limpiar memoria de eliminados

    SweetAlert("success", `Borrador guardado.\nCreados: ${okCreate.length}\nActualizados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nSaltados (vacíos): ${skipped.length}\nErrores: ${fail.length}`);
    await cargarBorrador();

  } finally {
    btnGuardar.disabled = false;
    btnPublicar.disabled = false;
    btnGuardar.innerText = textoOriginal;
  }
}

// ==========================================
// 11) PUBLICAR BORRADOR (CON TRY...FINALLY)
// ==========================================
async function publicarBorrador() {
  const id_empresa = $("#id_empresa").val();
  if (!id_empresa) {
      SweetAlert("error", "No se encontró el ID de la empresa");
      return;
  }

  const filas = getRowsData().filter(p => p.publicar === 1);

  if (!filas.length) {
    SweetAlert("warning", "Selecciona al menos una fila en la casilla de 'Publicar'.");
    return;
  }

  const errores = [];

  for (let i = 0; i < filas.length; i++) {
    const p = filas[i];
    const fila = p.index + 1; 
    const isNew = !p.id_producto;

    if (isEmptyStr(p.titulo_servicio)) errores.push(`Fila ${fila}: Falta título`);
    if (isEmptyStr(p.nombre)) errores.push(`Fila ${fila}: Falta Nombre Servicio`);
    if (isEmptyStr(p.descripcion)) errores.push(`Fila ${fila}: Falta descripción`);
    if (isEmptyStr(p.tags)) errores.push(`Fila ${fila}: Falta tags`);
    if (isEmptyArr(p.categoria)) errores.push(`Fila ${fila}: Falta categoría`);
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
      const tieneFrontal = !!p.img_frontal_actual || !!p.files?.img_frontal;
      const tienePosterior = !!p.img_posterior_actual || !!p.files?.img_posterior;
      if (!tieneFrontal) errores.push(`Fila ${fila}: Falta imagen frontal (no hay actual ni nueva)`);
      if (!tienePosterior) errores.push(`Fila ${fila}: Falta imagen posterior (no hay actual ni nueva)`);
    }
  }

  if (errores.length) {
    SweetAlert("error", "Corrige estos campos:\n\n" + errores.join("\n"));
    return;
  }

  const btnGuardar = document.getElementById("btnGuardarBorrador");
  const btnPublicar = document.getElementById("btnPublicar");
  const textoOriginal = btnPublicar.innerText;

  try {
    btnGuardar.disabled = true;
    btnPublicar.disabled = true;
    btnPublicar.innerText = "Procesando...";

    const okCreate = [];
    const okUpdate = [];
    const idsExistentesParaPublicar = [];
    const fail = [];

    for (let i = 0; i < filas.length; i++) {
      const p = filas[i];

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
        if (p.files?.docs && p.files.docs.length) {
          archivosSubidos = await saveFiles(p.files.docs); 
        }

        const archivosFinal = archivosSubidos;

        const basePayload = {
          nombre: p.nombre,
          descripcion: emojiToEntities(p.descripcion),
          codigo: "",
          precio_referencia: p.precio_referencia,
          descuento: p.descuento,
          peso: 0,
          img_frontal: img_frontal_final,
          img_posterior: img_posterior_final,

          archivos: {archivos: archivosFinal},

          iva: 0,
          emergencia_24_7: p.emergencia_24_7 ? 1 : 0,
          emergencia_carretera: p.emergencia_carretera ? 1 : 0,
          emergencia_domicilio: p.emergencia_domicilio ? 1 : 0,
          categoria: p.categoria || [],
          sub_categoria: [],
          marca: p.id_marca || [],
          modelo: p.id_modelo || [],
          tipo_vehiculo: p.tipo_auto || [],
          traccion: p.tipo_traccion || [],
          referencias: p.referencias || [],
          funcionamiento_motor: p.funcionamiento_motor || [],
          atributos: [],
          id_empresa: id_empresa,
          tags: p.tags,
          titulo_producto: p.titulo_servicio,
          negociable: 0,
          tipo_creador: "empresa",
          tipo_producto: "servicio"
        };

        if (!p.id_producto) {
          const payloadCreate = { ...basePayload, estado: "A" };
          const returnedData = await postJQ("../api/v1/fulmuv/productos/create", payloadCreate);
          const returned = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;

          if (returned.error == false) {
            okCreate.push(p.index + 1);
            const newId = returned.data?.id_producto || returned.data?.id || returned.id_producto || null;
            if (newId && p.nodoTR) {
              p.nodoTR.dataset.id_producto = newId;
            }
          } else {
            fail.push({ fila: p.index + 1, msg: returned.msg || "Error al crear/publicar" });
          }

        } else {
          const payloadUpdate = {
            ...basePayload,
            id_producto: p.id_producto,
            imagenFrontalEdit: seleccionoFrontal ? 1 : 0,
            imagenPosteriorEdit: seleccionoPosterior ? 1 : 0
          };

          const resp = await actualizarProducto(payloadUpdate);
          const r = typeof resp === "string" ? JSON.parse(resp) : resp;

          if (r.error == false) {
            okUpdate.push(p.index + 1);
            idsExistentesParaPublicar.push(Number(p.id_producto));
          } else {
            fail.push({ fila: p.index + 1, msg: r.msg || "Error al actualizar" });
          }
        }
      } catch (e) {
        fail.push({ fila: p.index + 1, msg: e.message || "Error" });
      }
    }

    if (idsExistentesParaPublicar.length) {
      const respPub = await postJQ("../api/v1/fulmuv/productos/publicar_seleccionados", {
        id_empresa,
        ids: JSON.stringify(idsExistentesParaPublicar)
      });
      const pub = typeof respPub === "string" ? JSON.parse(respPub) : respPub;
      if (pub.error !== false) fail.push({ fila: "-", msg: pub.msg || "No se pudo publicar los existentes" });
    }

    // ===============================
    // ✅ PROCESAR ELIMINACIONES PENDIENTES
    // ===============================
    const okDelete = [];
    for (const idEliminar of serviciosEliminados) {
      try {
        const respDel = await postJQ("../api/v1/fulmuv/productos/delete", { id: idEliminar });
        const rDel = typeof respDel === "string" ? JSON.parse(respDel) : respDel;
        
        if (rDel.error === false) {
          okDelete.push(idEliminar);
        } else {
          fail.push({ fila: "Borrado", msg: rDel.msg || `Error al eliminar ID ${idEliminar}` });
        }
      } catch (e) {
        fail.push({ fila: "Borrado", msg: `Error de red al eliminar ID ${idEliminar}` });
      }
    }
    serviciosEliminados = []; // Limpiar memoria de eliminados

    SweetAlert("success", `Publicación completada.\nCreados y publicados: ${okCreate.length}\nActualizados y publicados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nErrores: ${fail.length}`);
    await cargarBorrador();

  } finally {
    btnGuardar.disabled = false;
    btnPublicar.disabled = false;
    btnPublicar.innerText = textoOriginal;
  }
}

// ==========================================
// UTILS EXTRAS
// ==========================================
function actualizarProducto(payload) {
  return postJQ("../api/v1/fulmuv/productos/edit", payload);
}

function emojiToEntities(str) {
  try {
    return str.replace(/\p{Extended_Pictographic}/gu, (m) => Array.from(m).map(ch => `&#${ch.codePointAt(0)};`).join(''));
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
            swal("Listo", (cfg.label || cfg.entity) + " registrado correctamente.", "success");
          })
          .fail(function (msg) {
            swal("Error", msg?.toString?.() || "No se pudo registrar.", "error");
            $el.val($el.data("prev") || null).trigger("change");
          });
      },
      function () { $el.val($el.data("prev") || null).trigger("change"); }
    );
  });
}