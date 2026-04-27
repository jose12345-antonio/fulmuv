/****************************************************
 * VARIABLES GLOBALES
 ****************************************************/
let marcas = [];
let modelos = [];
let tiposAuto = [];
let tiposTraccion = [];
let funcionamiento_motor = [];
let categorias = [];
let subcategorias = [];
let marcasProductos = [];
let nombresProductos = [];
let referencias = [];

// Memoria temporal para eliminar productos de la BD
let productosEliminados = [];

const tbody = document.getElementById("tbodyProductos");

/****************************************************
 * EVENTOS BOTONES & INIT
 ****************************************************/
document.getElementById("btnAddRow").addEventListener("click", () => addRow());

document.getElementById("btnClear").addEventListener("click", () => {
  tbody.querySelectorAll("tr").forEach(tr => {
    if (tr.dataset.id_producto) productosEliminados.push(tr.dataset.id_producto);
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

/****************************************************
 * FETCH JSON (Blindado)
 ****************************************************/
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

/****************************************************
 * CARGAR COMBOS
 ****************************************************/
async function cargarCombos() {
  marcas = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/marcas/"), "nombre");
  tiposAuto = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tiposAuto/"), "nombre");
  tiposTraccion = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tipo_tracccion/"), "nombre");
  funcionamiento_motor = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getFuncionamientoMotor/"), "nombre");
  
  // Traemos las categorías completas (incluye subcategorías)
  categorias = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/categorias/?tipo=producto"), "nombre");
  
  marcasProductos = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getMarcasProductos/"), "nombre");
  referencias = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getReferencias/"), "nombre");

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

/****************************************************
 * OPTIONS HELPERS
 ****************************************************/
function optionList(arr, valueKey, textKey) {
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach((item) => {
    html += `<option value="${item[valueKey]}">${item[textKey]}</option>`;
  });
  return html;
}

function optionListStrings(arr) {
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach((s) => {
    html += `<option value="${String(s)}">${String(s)}</option>`;
  });
  return html;
}

function escapeHtml(str) {
  return String(str).replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
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

/****************************************************
 * ADD ROW (LÓGICA EN CASCADA INTEGRADA)
 ****************************************************/
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
    <td><input class="form-control form-control-sm codigo" placeholder="Código"></td>
    <td><textarea class="form-control form-control-sm descripcion" rows="1" placeholder="Descripción"></textarea></td>

    <td>
      <select class="form-select form-select-sm id_marca_producto">
        ${optionList(marcasProductos, "id_marca_producto", "nombre")}
      </select>
    </td>

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

    <td>
      <select class="form-select form-select-sm sub_categoria" multiple></select>
    </td>

    <td><input class="form-control form-control-sm precio" type="number" step="0.01" min="0" value="0"></td>
    <td><input class="form-control form-control-sm descuento" type="number" step="0.01" min="0" value="0"></td>
    <td><input class="form-control form-control-sm peso" type="number" step="0.01" min="0" value="0"></td>

    <td class="text-center"><input class="form-check-input iva" type="checkbox"></td>
    <td class="text-center"><input class="form-check-input negociable" type="checkbox"></td>
    <td><input class="form-control form-control-sm tags" placeholder="Ej: unidad, caja"></td>

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
  `;

  // Eliminar fila
  tr.querySelector(".btnDel").addEventListener("click", () => {
    if (tr.dataset.id_producto) productosEliminados.push(tr.dataset.id_producto);
    $(tr).find("select").each(function () {
      if ($(this).data("select2")) $(this).select2("destroy");
    });
    tr.remove();
    if (tbody.children.length === 0) addRow();
  });

  // Previsualización de imágenes y docs
  tr.querySelector(".img_frontal").addEventListener("change", function(e) {
    const file = e.target.files[0];
    const preview = tr.querySelector(".preview_frontal");
    if (file) { preview.src = URL.createObjectURL(file); preview.classList.remove("d-none"); } 
    else if (!tr.dataset.img_frontal) preview.classList.add("d-none");
  });

  tr.querySelector(".img_posterior").addEventListener("change", function(e) {
    const file = e.target.files[0];
    const preview = tr.querySelector(".preview_posterior");
    if (file) { preview.src = URL.createObjectURL(file); preview.classList.remove("d-none"); } 
    else if (!tr.dataset.img_posterior) preview.classList.add("d-none");
  });

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
  // LÓGICA EN CASCADA (DEPENDENCIAS)
  // ==========================================

  // 1. Categoría -> Subcategoría
  $(tr).find('.categoria').on('change', function () {
    const sels = $(this).val() || [];
    const $sub = $(tr).find('.sub_categoria');
    const currentSubVals = $sub.val() || [];
    
    $sub.empty(); // Limpiar

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
    // Restaurar selección si existe, sino null
    $sub.val(currentSubVals).trigger('change.select2');
  });

  // 2. Referencia -> Modelo
  $(tr).find('.referencias').on('change', async function () {
    if (tr.dataset.isloading === "1") return; // Ignorar durante carga inicial

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
    if (tr.dataset.isloading === "1") return; // Ignorar durante carga inicial

    const mods = $(this).val() || [];
    const id_modelo = Array.isArray(mods) ? mods[0] : mods;
    if (!id_modelo || id_modelo === 'nuevo') return;

    try {
      const returnedData = await $.get('../api/v1/fulmuv/getModeloById/' + id_modelo);
      const r = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      if (r.error || !r.data) return;

      const d = r.data;

      // Helper para seleccionar agregando al actual
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

  // Inicializar Select2 general
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
      if ($sel.hasClass("id_marca_producto")) wireSelectEnsure($sel, { entity: "marcas_productos", label: "Marca de producto", requireParents: false });
      if ($sel.hasClass("referencias")) wireSelectEnsure($sel, { entity: "referencias", label: "Referencia", requireParents: false });
      if ($sel.hasClass("categoria")) wireSelectEnsure($sel, { entity: "categorias", label: "Categoría", requireParents: false });

      if ($sel.hasClass("sub_categoria")) {
        wireSelectEnsure($sel, {
          entity: "sub_categorias",
          label: "Subcategoría",
          requireParents: true,
          parents: function () {
            return { id_categoria: firstNumericIdFromMulti(tr.querySelector(".categoria")) };
          },
        });
      }

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

/****************************************************
 * GET VALUES
 ****************************************************/
function getSelectedValues(selectEl) {
  if (!selectEl) return [];
  return Array.from(selectEl.selectedOptions).map((o) => o.value).filter(Boolean);
}

function getRowsData() {
  const rows = tbody.querySelectorAll("tr");
  const productos = [];

  rows.forEach((tr, index) => {
    const id_producto = tr.dataset.id_producto ? Number(tr.dataset.id_producto) : null;
    const imgFrontalFile = tr.querySelector(".img_frontal")?.files?.[0] || null;
    const imgPosteriorFile = tr.querySelector(".img_posterior")?.files?.[0] || null;

    productos.push({
      nodoTR: tr,
      index,
      id_producto,
      publicar: tr.querySelector(".chk_publicar")?.checked ? 1 : 0,

      titulo_producto: tr.querySelector(".nombre").value.trim(),
      codigo: tr.querySelector(".codigo").value.trim(),
      descripcion: tr.querySelector(".descripcion").value.trim(),

      precio_referencia: parseFloat(tr.querySelector(".precio").value || "0"),
      descuento: parseFloat(tr.querySelector(".descuento").value || "0"),
      peso: parseFloat(tr.querySelector(".peso").value || "0"),

      iva: tr.querySelector(".iva").checked ? 1 : 0,
      negociable: tr.querySelector(".negociable").checked ? 1 : 0,

      tags: tr.querySelector(".tags").value.trim(),
      id_marca_producto: tr.querySelector(".id_marca_producto").value || "",

      id_marca: getSelectedValues(tr.querySelector(".id_marca")),
      id_modelo: getSelectedValues(tr.querySelector(".id_modelo")),
      tipo_auto: getSelectedValues(tr.querySelector(".tipo_auto")),
      tipo_traccion: getSelectedValues(tr.querySelector(".tipo_traccion")),
      categoria: getSelectedValues(tr.querySelector(".categoria")),
      sub_categoria: getSelectedValues(tr.querySelector(".sub_categoria")),
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

/****************************************************
 * POST helper & UPLOADS
 ****************************************************/
function postJQ(url, data) {
  return new Promise((resolve, reject) => {
    $.post(url, data, (resp) => resolve(resp)).fail((xhr) => reject(new Error(xhr.responseText || "Error POST")));
  });
}

function subirImagenesPrincipalesFila(imgFrontalFile, imgPosteriorFile) {
  return new Promise((resolve, reject) => {
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
      success: (res) => {
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
      error: () => {
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

/****************************************************
 * BORRADOR: helpers
 ****************************************************/
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
  Array.from(selectEl.options).forEach((opt) => opt.selected = set.has(String(opt.value)));
  $(selectEl).trigger("change");
}

/****************************************************
 * BORRADOR: agregar fila con data (Carga en cadena)
 ****************************************************/
async function addRowWithData(p) {
  addRow();
  const tr = tbody.lastElementChild;

  // BLOQUEAMOS los triggers automáticos para no borrar datos
  tr.dataset.isloading = "1";

  tr.dataset.id_producto = p.id_producto || "";
  tr.querySelector(".nombre").value = p.titulo_producto || "";
  tr.querySelector(".codigo").value = p.codigo || "";
  tr.querySelector(".descripcion").value = p.descripcion || "";
  tr.querySelector(".precio").value = p.precio_referencia || 0;
  tr.querySelector(".descuento").value = p.descuento || 0;
  tr.querySelector(".peso").value = p.peso || 0;
  tr.querySelector(".iva").checked = String(p.iva) === "1";
  tr.querySelector(".negociable").checked = String(p.negociable) === "1";
  tr.querySelector(".tags").value = p.tags || "";

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

  // Archivos
  let archivos = [];
  if (Array.isArray(p.archivos)) archivos = p.archivos;
  tr.dataset.archivos_actual = JSON.stringify(archivos);

  const previewContainer = tr.querySelector(".docs_preview");
  const hintElement = tr.querySelector(".docs_hint");
  if (archivos && archivos.length > 0) {
    if (hintElement) hintElement.innerText = `${archivos.length} archivos (guardados)`;
    archivos.forEach(arc => {
      const fileUrl = typeof arc === 'string' ? arc : (arc?.archivo || arc?.url || ""); 
      if (!fileUrl) return;
      const esImagen = (arc?.tipo === "imagen") || fileUrl.split('?')[0].match(/\.(jpeg|jpg|gif|png|webp)$/i);

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

  if (p.marca_producto) tr.querySelector(".id_marca_producto").value = p.marca_producto;
  $(tr).find(".id_marca_producto").trigger("change");

  // Cargar Categoría (dispara carga de subcategoría localmente)
  setSelectMultiple(tr.querySelector(".categoria"), safeParseJSON(p.categoria));
  $(tr).find('.categoria').trigger('change'); 
  setSelectMultiple(tr.querySelector(".sub_categoria"), safeParseJSON(p.sub_categoria));

  // Cargar Referencia y traer modelos
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

  // Llenar los demás combos independientemente de que se autocompleten (usamos lo guardado)
  setSelectMultiple(tr.querySelector(".id_modelo"), safeParseJSON(p.id_modelo));
  setSelectMultiple(tr.querySelector(".id_marca"), safeParseJSON(p.id_marca));
  setSelectMultiple(tr.querySelector(".tipo_auto"), safeParseJSON(p.tipo_auto));
  setSelectMultiple(tr.querySelector(".tipo_traccion"), safeParseJSON(p.tipo_traccion));
  setSelectMultiple(tr.querySelector(".funcionamiento_motor"), safeParseJSON(p.funcionamiento_motor));

  // Desbloqueamos los triggers para futuras ediciones manuales del usuario
  tr.dataset.isloading = "0";
}

async function cargarBorrador() {
  try {
    productosEliminados = []; // Limpiar memoria
    const id_empresa = $("#id_empresa").val();
    if (!id_empresa) return;
    
    const borrador = await fetchJSON(`../api/v1/fulmuv/productos/borrador/${id_empresa}`);
    tbody.innerHTML = "";
    if (!borrador || !borrador.length) { addRow(); return; }

    // Renderizar secuencial para evitar cruces en peticiones a la BD
    for (const p of borrador) {
      await addRowWithData(p);
    }
  } catch (e) {
    console.error(e); addRow();
  }
}

function isEmptyStr(v) { return !String(v ?? "").trim(); }
function isEmptyArr(v) { return !Array.isArray(v) || v.length === 0; }

/****************************************************
 * GUARDAR BORRADOR (Con Try/Finally UX)
 ****************************************************/
async function guardarBorrador() {
  const id_empresa = $("#id_empresa").val();
  if (!id_empresa) {
    SweetAlert("error", "No se encontró la empresa (id_empresa).");
    return;
  }

  const productos = getRowsData();
  const btnGuardar = document.getElementById("btnGuardarBorrador");
  const btnPublicar = document.getElementById("btnPublicar");
  const textoOriginal = btnGuardar.innerText;

  function isRowEmpty(p) {
    const hasText = !isEmptyStr(p.titulo_producto) || !isEmptyStr(p.nombre) || !isEmptyStr(p.descripcion) || !isEmptyStr(p.tags);
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
          nombre: (p.nombre || "").trim(), 
          descripcion: emojiToEntities(p.descripcion || ""),
          codigo: (p.codigo || ""),
          categoria: Array.isArray(p.categoria) ? p.categoria : [],
          sub_categoria: Array.isArray(p.sub_categoria) ? p.sub_categoria : [],
          tags: (p.tags || ""),
          precio_referencia: Number(p.precio_referencia || 0),
          descuento: Number(p.descuento || 0),
          peso: Number(p.peso || 0),
          img_frontal: img_frontal_final,
          img_posterior: img_posterior_final,
          archivos: {archivos: archivosFinal}, 
          atributos: [],
          id_empresa: id_empresa,
          tipo_vehiculo: Array.isArray(p.tipo_auto) ? p.tipo_auto : [],
          modelo: Array.isArray(p.id_modelo) ? p.id_modelo : [],
          marca: Array.isArray(p.id_marca) ? p.id_marca : [],
          traccion: Array.isArray(p.tipo_traccion) ? p.tipo_traccion : [],
          referencias: Array.isArray(p.referencias) ? p.referencias : [],
          funcionamiento_motor: Array.isArray(p.funcionamiento_motor) ? p.funcionamiento_motor : [],
          titulo_producto: (p.titulo_producto || "").trim(),
          marca_producto: p.id_marca_producto || "",
          iva: Number(p.iva || 0),
          negociable: Number(p.negociable || 0),
          tipo_creador: "empresa",
        };

        if (!p.id_producto) {
          const payloadCreate = { ...basePayload, estado: "P", tipo_producto: "producto" };
          const returnedData = await postJQ("../api/v1/fulmuv/productos/create", payloadCreate);
          const returned = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;

          if (returned.error == false) okCreate.push(filaHuman);
          else fail.push({ fila: filaHuman, msg: returned.msg || "Error al crear" });

        } else {
          const payloadUpdate = {
            ...basePayload,
            id_producto: p.id_producto,
            imagenFrontalEdit: seleccionoFrontal ? 1 : 0,
            imagenPosteriorEdit: seleccionoPosterior ? 1 : 0
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

    // ELIMINAR 
    const okDelete = [];
    for (const idEliminar of productosEliminados) {
      try {
        const respDel = await postJQ("../api/v1/fulmuv/productos/delete", { id: idEliminar });
        const rDel = typeof respDel === "string" ? JSON.parse(respDel) : respDel;
        if (rDel.error === false) okDelete.push(idEliminar);
        else fail.push({ fila: "Borrado", msg: rDel.msg || `Error al eliminar ID ${idEliminar}` });
      } catch (e) {
        fail.push({ fila: "Borrado", msg: `Error de red al eliminar ID ${idEliminar}` });
      }
    }
    productosEliminados = []; // Vaciamos memoria

    SweetAlert("success", `Borrador guardado.\nCreados: ${okCreate.length}\nActualizados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nSaltados (vacíos): ${skipped.length}\nErrores: ${fail.length}`);
    await cargarBorrador();

  } finally {
    btnGuardar.disabled = false;
    btnPublicar.disabled = false;
    btnGuardar.innerText = textoOriginal;
  }
}

/****************************************************
 * PUBLICAR BORRADOR (Con validación y UX)
 ****************************************************/
async function publicarBorrador() {
  const id_empresa = $("#id_empresa").val();
  if (!id_empresa) {
      SweetAlert("error", "No se encontró el ID de la empresa");
      return;
  }

  const productos = getRowsData().filter((p) => p.publicar === 1);

  if (!productos.length) {
    SweetAlert("warning", "Selecciona al menos una fila para publicar.");
    return;
  }

  const errores = [];
  for (let i = 0; i < productos.length; i++) {
    const p = productos[i];
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
    // Bloquear botones
    btnGuardar.disabled = true;
    btnPublicar.disabled = true;
    btnPublicar.innerText = "Procesando...";

    const idsExistentesParaPublicar = [];
    const okCreate = [];
    const okUpdate = [];
    const fail = [];

    for (let i = 0; i < productos.length; i++) {
      const p = productos[i];

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
          archivos: {archivos: archivosFinal}, 
          atributos: [],
          id_empresa: id_empresa,
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
        id_empresa, ids: JSON.stringify(idsExistentesParaPublicar)
      });
      const pub = typeof respPub === "string" ? JSON.parse(respPub) : respPub;
      if (pub.error !== false) fail.push({ fila: "-", msg: pub.msg || "No se pudo publicar los existentes" });
    }

    // ELIMINAR 
    const okDelete = [];
    for (const idEliminar of productosEliminados) {
      try {
        const respDel = await postJQ("../api/v1/fulmuv/productos/delete", { id: idEliminar });
        const rDel = typeof respDel === "string" ? JSON.parse(respDel) : respDel;
        if (rDel.error === false) okDelete.push(idEliminar);
        else fail.push({ fila: "Borrado", msg: rDel.msg || `Error al eliminar ID ${idEliminar}` });
      } catch (e) {
        fail.push({ fila: "Borrado", msg: `Error de red al eliminar ID ${idEliminar}` });
      }
    }
    productosEliminados = []; // Vaciamos memoria

    SweetAlert("success", `Publicación completada.\nCreados y publicados: ${okCreate.length}\nActualizados y publicados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nErrores: ${fail.length}`);
    await cargarBorrador();

  } finally {
    // Desbloquear botones pase lo que pase
    btnGuardar.disabled = false;
    btnPublicar.disabled = false;
    btnPublicar.innerText = textoOriginal;
  }
}

/****************************************************
 * EDIT & UTILS
 ****************************************************/
function actualizarProducto(payload) {
  return postJQ("../api/v1/fulmuv/productos/edit", payload);
}

function emojiToEntities(str) {
  try {
    return str.replace(/\p{Extended_Pictographic}/gu, (m) => Array.from(m).map((ch) => `&#${ch.codePointAt(0)};`).join(""));
  } catch (e) {
    return Array.from(str).map((ch) => { const cp = ch.codePointAt(0); return cp > 0xffff ? `&#${cp};` : ch; }).join("");
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
        ensureRemote(cfg.entity, txt, parents)
          .then(function (id) {
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