/***********************
 * VARIABLES / DATA
 ***********************/
let tipos_auto = [];
let marcas = [];
let traccion = [];
let motor = [];
let modelos = [];
let referencias = [];
let vendedor = [];
let climatizacion = [];
let direccion = [];
let tapiceria = [];
let colores = [];
let transmision = [];

// ✅ Memoria de vehículos eliminados de la pantalla
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

const tbody = document.getElementById("tbodyVehiculos");
const wrap  = document.getElementById("excelWrapVehiculos");

/***********************
 * EVENTOS BOTONES
 ***********************/
document.getElementById("btnAddRow").addEventListener("click", () => addRow());

document.getElementById("btnClear").addEventListener("click", () => {
  tbody.querySelectorAll("tr").forEach(tr => {
    if (tr.dataset.id_vehiculo) vehiculosEliminados.push(tr.dataset.id_vehiculo);
  });
  tbody.innerHTML = "";
  addRow();
});

document.getElementById("btnGuardarBorrador").addEventListener("click", () => guardarMasivo("P"));
document.getElementById("btnPublicar").addEventListener("click", () => guardarMasivo("A"));

/***********************
 * INIT
 ***********************/
document.addEventListener("DOMContentLoaded", async () => {
  await cargarCombos();
  await cargarBorradorVehiculos();
  if (!tbody.children.length) addRow();
});

/***********************
 * HELPERS (Protegidos)
 ***********************/
async function fetchJSON(url){
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

function optionList(arr, valueKey, textKey){
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach(x => html += `<option value="${x[valueKey]}">${x[textKey]}</option>`);
  return html;
}

function optionListStrings(arr){
  let html = `<option value="">Seleccione...</option>`;
  (arr || []).forEach(s => html += `<option value="${String(s)}">${String(s)}</option>`);
  return html;
}

function safeParseJSON(v) {
  if (!v) return [];
  if (Array.isArray(v)) return v;
  try {
    const parsed = JSON.parse(v);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function setSelectSingle(selectEl, value) {
  if (!selectEl) return;
  selectEl.value = String(value ?? "");
  if (window.$ && $.fn.select2) $(selectEl).trigger("change.select2");
}

function isEmptyStr(v){ return !String(v ?? "").trim(); }

function postJQ(url, data){
  return new Promise((resolve, reject) => {
    $.post(url, data, (resp) => resolve(resp))
      .fail(xhr => reject(new Error(xhr.responseText || "Error POST")));
  });
}

/***********************
 * CARGAR COMBOS
 ***********************/
async function cargarCombos(){
  tipos_auto     = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tiposAuto/"), "nombre");
  marcas         = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/marcas/"), "nombre");
  traccion       = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/tipo_tracccion/"), "nombre");
  motor          = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getFuncionamientoMotor/"), "nombre");
  referencias    = orderNAandVariosFirstStrings(await fetchJSON("../api/v1/fulmuv/getReferencias/"));
  vendedor       = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getTipoVendedor/"), "nombre");
  climatizacion  = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getClimatizacion/"), "nombre");
  direccion      = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getDireccion/"), "nombre");
  tapiceria      = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getTapiceria/"), "nombre");
  colores        = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getColores/"), "nombre");
  transmision    = orderNAandVariosFirstObjects(await fetchJSON("../api/v1/fulmuv/getTransmision/"), "nombre");
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

/***********************
 * PROVINCIA -> CANTONES
 ***********************/
function cargarCantonesFila(tr, provincia) {
  const cantonSel = tr.querySelector("select.canton");
  if (!cantonSel) return;

  const currentVal = $(cantonSel).val(); 
  cantonSel.innerHTML = `<option value="">Elija un cantón</option>`;

  if (provincia && cantones[provincia]) {
    cantones[provincia].forEach(c => {
      const opt = document.createElement("option");
      opt.value = c;
      opt.textContent = c;
      cantonSel.appendChild(opt);
    });
  }

  if (currentVal) $(cantonSel).val(currentVal);
  if (window.$ && $.fn.select2) $(cantonSel).trigger("change.select2");
}

/***********************
 * ADD ROW (Con Miniaturas y Cascada)
 ***********************/
function addRow(){
  const tr = document.createElement("tr");

  tr.innerHTML = `
    <td class="text-center">
      <button class="btn btn-sm btn-outline-danger btnDel" type="button">✕</button>
    </td>
    <td class="chk-cell text-center">
      <div class="chk-wrap">
        <input class="chk_publicar" type="checkbox" title="Marcar para publicar">
      </div>
    </td>

    <td><select class="form-select form-select-sm referencias">${optionListStrings(referencias)}</select></td>
    
    <td>
      <select class="form-select form-select-sm id_modelo"></select>
    </td>

    <td><select class="form-select form-select-sm tipo_auto">${optionList(tipos_auto,"id_tipo_auto","nombre")}</select></td>
    <td><select class="form-select form-select-sm id_marca">${optionList(marcas,"id_marca","nombre")}</select></td>
    <td><select class="form-select form-select-sm tipo_traccion">${optionList(traccion,"id_tipo_traccion","nombre")}</select></td>
    <td><select class="form-select form-select-sm funcionamiento_motor">${optionList(motor,"id_funcionamiento_motor","nombre")}</select></td>

    <td><textarea class="form-control form-control-sm descripcion" rows="1" placeholder="Descripción..."></textarea></td>
    
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

    <td><input class="form-control form-control-sm anio" type="number" min="1900" max="2100" placeholder="2020"></td>

    <td>
      <select class="form-select form-select-sm condicion">
        <option value="">Seleccione...</option>
        <option value="nuevo">Nuevo</option>
        <option value="usado">Usado</option>
        <option value="seminuevo">Seminuevo</option>
      </select>
    </td>

    <td><select class="form-select form-select-sm tipo_vendedor">${optionList(vendedor,"id_tipo_vendedor","nombre")}</select></td>
    <td><input class="form-control form-control-sm kilometraje" type="number" min="0" placeholder="85000"></td>
    <td><select class="form-select form-select-sm transmision">${optionList(transmision,"id_transmision","nombre")}</select></td>
    <td><input class="form-control form-control-sm inicio_placa" maxlength="1" placeholder="G"></td>
    <td><input class="form-control form-control-sm fin_placa" maxlength="1" placeholder="9"></td>
    <td><select class="form-select form-select-sm color">${optionList(colores,"id_color","nombre")}</select></td>
    <td><input class="form-control form-control-sm cilindraje" type="number" step="0.01" min="0" value="0"></td>
    <td><select class="form-select form-select-sm tapiceria">${optionList(tapiceria,"id_tapiceria","nombre")}</select></td>

    <td>
      <select class="form-select form-select-sm tipo_duenio">
        <option value="">Seleccione...</option>
        <option value="único dueño">Único dueño</option>
        <option value="Segundo dueño">Segundo dueño</option>
        <option value="Tercer dueño">Tercer dueño</option>
      </select>
    </td>

    <td><select class="form-select form-select-sm direccion">${optionList(direccion,"id_direccion","nombre")}</select></td>
    <td><select class="form-select form-select-sm climatizacion">${optionList(climatizacion,"id_climatizacion","nombre")}</select></td>

    <td>
      <select class="form-select form-select-sm provincia">
        <option value="">Seleccione provincia</option>
        ${Object.keys(cantones).map(p=>`<option value="${p}">${p}</option>`).join("")}
      </select>
    </td>

    <td>
      <select class="form-select form-select-sm canton">
        <option value="">Elija un cantón</option>
      </select>
    </td>

    <td><input class="form-control form-control-sm tags" placeholder="Ej: sedan, familiar"></td>
    <td><input class="form-control form-control-sm precio_referencia" type="number" step="0.01" min="0" value="0"></td>
    <td class="text-center"><input class="iva" type="checkbox"></td>
    <td class="text-center"><input class="negociable" type="checkbox"></td>
    <td><input class="form-control form-control-sm descuento" type="number" step="0.01" min="0" value="0"></td>

    <td>
      <input class="form-control form-control-sm archivos" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*">
      <small class="text-muted d-block mt-1 hint_files">0 archivos</small>
      <div class="docs_preview d-flex flex-wrap gap-1 mt-1"></div>
    </td>
  `;

  tbody.appendChild(tr);

  // ==========================================
  // ✅ LÓGICA EN CASCADA (DEPENDENCIAS)
  // ==========================================
  
  const selProv = tr.querySelector("select.provincia");
  const onProvinciaChange = () => cargarCantonesFila(tr, selProv.value);
  selProv.addEventListener("change", onProvinciaChange);

  // 1. Referencia -> Modelo
  $(tr).find('.referencias').on('change', async function () {
    if (tr.dataset.isloading === "1") return; 

    const ref = $(this).val() || '';
    const $mod = $(tr).find('.id_modelo');
    const currentModVal = $mod.val();

    if (!ref) {
      $mod.empty().trigger('change.select2');
      return;
    }

    try {
      const returnedData = await $.get('../api/v1/fulmuv/getModelosByReferencia/' + encodeURIComponent(ref));
      const r = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      
      $mod.empty().append('<option value="">Seleccione...</option>');
      (r.data || []).forEach(m => {
        $mod.append(new Option(m.nombre, m.id_modelos_autos, false, false));
      });
      $mod.val(currentModVal).trigger('change.select2');
    } catch (e) {
      console.error("Error cargando modelos:", e);
    }
  });

  // 2. Modelo -> Auto-Relleno (Marca, Tipo, Tracción, Motor)
  $(tr).find('.id_modelo').on('change', async function () {
    if (tr.dataset.isloading === "1") return;

    const id_modelo = $(this).val();
    if (!id_modelo || id_modelo === 'nuevo') return;

    try {
      const returnedData = await $.get('../api/v1/fulmuv/getModeloById/' + id_modelo);
      const r = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      if (r.error || !r.data) return;

      const d = r.data;
      $(tr).find('.id_marca').val(d.id_marca).trigger('change.select2');
      $(tr).find('.tipo_auto').val(d.id_tipo_auto).trigger('change.select2');
      $(tr).find('.tipo_traccion').val(d.id_tipo_traccion).trigger('change.select2');
      $(tr).find('.funcionamiento_motor').val(d.id_funcionamiento_motor).trigger('change.select2');
    } catch (e) {
      console.error("Error asignando datos del modelo:", e);
    }
  });

  // ==========================================
  // EVENTOS BÁSICOS E IMÁGENES
  // ==========================================

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

  tr.querySelector(".archivos").addEventListener("change", (e) => {
    const files = e.target.files;
    tr.querySelector(".hint_files").innerText = `${files.length} archivos (nuevos)`;
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

  tr.querySelector(".btnDel").addEventListener("click", () => {
    if (tr.dataset.id_vehiculo) vehiculosEliminados.push(tr.dataset.id_vehiculo);
    $(tr).find("select").each(function () {
      if ($(this).data("select2")) $(this).select2("destroy");
    });
    tr.remove();
    if (tbody.children.length === 0) addRow();
  });

  // Inicializar Select2 en la fila
  if (window.$ && $.fn.select2) {
    $(tr).find("select").each(function () {
      if ($(this).data("select2")) $(this).select2("destroy");
      $(this).select2({
        theme: "bootstrap-5",
        width: "resolve",
        placeholder: "Seleccione...",
        dropdownParent: $(document.body)
      });
    });
    $(selProv).on("select2:select", onProvinciaChange);
  }

  return tr;
}

function getIdEmpresa(){
  if ($("#id_rol_principal").val() == 1) return $("#lista_empresas").val();
  return $("#id_empresa").val();
}

/***********************
 * CARGAR BORRADOR
 ***********************/
async function cargarBorradorVehiculos(){
  try{
    vehiculosEliminados = []; // Limpiamos la memoria
    const id_empresa = getIdEmpresa();
    if (!id_empresa) return;

    const borrador = await fetchJSON(`../api/v1/fulmuv/vehiculos/borrador/${id_empresa}`);
    tbody.innerHTML = "";
    if (!borrador || !borrador.length) return;
    
    // Carga secuencial para no sobrecargar peticiones de modelos
    for(const v of borrador){
      await addRowWithDataVehiculo(v);
    }

  } catch(e){
    console.error("Error cargarBorradorVehiculos:", e);
  }
}

/***********************
 * PINTAR FILA CON DATA
 ***********************/
async function addRowWithDataVehiculo(v){
  addRow();
  const tr = tbody.lastElementChild;

  // Bloqueo temporal de eventos de cascada
  tr.dataset.isloading = "1";

  tr.dataset.id_vehiculo = v.id_vehiculo || "";
  tr.dataset.img_frontal = v.img_frontal || "";
  tr.dataset.img_posterior = v.img_posterior || "";

  if (v.img_frontal) {
    const previewF = tr.querySelector(".preview_frontal");
    previewF.src = '../admin/'+v.img_frontal; 
    previewF.classList.remove("d-none");
  }

  if (v.img_posterior) {
    const previewP = tr.querySelector(".preview_posterior");
    previewP.src = '../admin/'+v.img_posterior; 
    previewP.classList.remove("d-none");
  }

  // ✅ EXTRACCIÓN Y RENDERIZADO DE ARCHIVOS
  let archivos = [];
  try {
    const parsed = typeof v.archivos === 'string' ? JSON.parse(v.archivos) : v.archivos;
    if (Array.isArray(parsed)) archivos = parsed;
    else if (parsed && Array.isArray(parsed.archivos)) archivos = parsed.archivos;
  } catch(e){}

  tr.dataset.archivos_actual = JSON.stringify(archivos);

  const previewContainer = tr.querySelector(".docs_preview");
  const hintElement = tr.querySelector(".hint_files");
  
  if (archivos && archivos.length > 0) {
    if (hintElement) hintElement.innerText = `${archivos.length} archivos (guardados)`;
    
    archivos.forEach(arc => {
      const fileUrl = typeof arc === 'string' ? arc : (arc?.archivo || arc?.url || ""); 
      if (!fileUrl) return;

      const esImagen = (arc?.tipo === "imagen") || fileUrl.split('?')[0].match(/\.(jpeg|jpg|gif|png|webp)$/i);

      if (esImagen) {
        const img = document.createElement("img");
        img.src = '../admin/' + fileUrl;
        img.style.width = "40px";
        img.style.height = "40px";
        img.style.objectFit = "cover";
        img.className = "rounded border shadow-sm";
        previewContainer.appendChild(img);
      } else {
        const badge = document.createElement("span");
        badge.className = "badge bg-info d-flex align-items-center justify-content-center text-xs text-white";
        badge.style.width = "40px";
        badge.style.height = "40px";
        badge.style.fontSize = "10px";
        badge.innerText = fileUrl.split('.').pop().toUpperCase();
        previewContainer.appendChild(badge);
      }
    });
  }

  tr.querySelector(".anio").value = v.anio || "";
  tr.querySelector(".kilometraje").value = v.kilometraje || "";
  tr.querySelector(".inicio_placa").value = v.inicio_placa || "";
  tr.querySelector(".fin_placa").value = v.fin_placa || "";
  tr.querySelector(".cilindraje").value = v.cilindraje || 0;
  tr.querySelector(".descripcion").value = v.descripcion || "";
  tr.querySelector(".precio_referencia").value = v.precio_referencia || 0;
  tr.querySelector(".descuento").value = v.descuento || 0;
  tr.querySelector(".tags").value = v.tags || "";

  tr.querySelector(".iva").checked = String(v.iva) === "1";
  tr.querySelector(".negociable").checked = String(v.negociable) === "1";

  // Arrays de selects
  const condicionArr      = safeParseJSON(v.condicion);
  const transmisionArr    = safeParseJSON(v.transmision);
  const provinciaArr      = safeParseJSON(v.provincia);
  const cantonArr         = safeParseJSON(v.canton);
  const tapiceriaArr      = safeParseJSON(v.tapiceria);
  const tipoDuenoArr      = safeParseJSON(v.tipo_dueno);
  const direccionArr      = safeParseJSON(v.direccion);
  const climatizacionArr  = safeParseJSON(v.climatizacion);
  const referenciasArr    = safeParseJSON(v.referencias);
  const tipoVendedorArr   = safeParseJSON(v.tipo_vendedor);

  setSelectSingle(tr.querySelector(".condicion"), condicionArr[0] ?? "");
  setSelectSingle(tr.querySelector("select.transmision"), transmisionArr[0] ?? "");
  setSelectSingle(tr.querySelector(".tapiceria"), tapiceriaArr[0] ?? "");
  setSelectSingle(tr.querySelector(".tipo_duenio"), tipoDuenoArr[0] ?? "");
  setSelectSingle(tr.querySelector(".direccion"), direccionArr[0] ?? "");
  setSelectSingle(tr.querySelector(".climatizacion"), climatizacionArr[0] ?? "");
  setSelectSingle(tr.querySelector(".tipo_vendedor"), tipoVendedorArr[0] ?? "");
  setSelectSingle(tr.querySelector(".color"), v.color || "");

  // ✅ 1. Cargar Provincias y Cantones
  const provincia = provinciaArr[0] ?? "";
  const canton = cantonArr[0] ?? "";
  setSelectSingle(tr.querySelector("select.provincia"), provincia);
  cargarCantonesFila(tr, provincia);
  setSelectSingle(tr.querySelector("select.canton"), canton);

  // ✅ 2. Cargar Referencia -> Modelos
  const ref = referenciasArr[0] ?? (v.referencias || "");
  setSelectSingle(tr.querySelector(".referencias"), ref);
  
  if (ref) {
    try {
      const returnedData = await $.get('../api/v1/fulmuv/getModelosByReferencia/' + encodeURIComponent(ref));
      const r = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      const $mod = $(tr).find('.id_modelo');
      $mod.empty().append('<option value="">Seleccione...</option>');
      (r.data || []).forEach(m => $mod.append(new Option(m.nombre, m.id_modelos_autos, false, false)));
    } catch(e) { console.error("Error BD", e) }
  }

  // ✅ 3. Seteo manual del resto (independiente de la cascada)
  setSelectSingle(tr.querySelector(".id_modelo"), v.id_modelo || "");
  setSelectSingle(tr.querySelector(".tipo_auto"), v.tipo_auto || "");
  setSelectSingle(tr.querySelector(".id_marca"), v.id_marca || "");
  setSelectSingle(tr.querySelector(".tipo_traccion"), v.tipo_traccion || "");
  setSelectSingle(tr.querySelector(".funcionamiento_motor"), v.funcionamiento_motor || "");

  // Liberar el bloqueo para interacción humana
  tr.dataset.isloading = "0";

  return tr;
}

/***********************
 * GET DATA DE TABLA
 ***********************/
function getRowsData(){
  const rows = tbody.querySelectorAll("tr");
  const out = [];

  rows.forEach((tr, index) => {
    const imgFrontalFile = tr.querySelector(".img_frontal")?.files?.[0] || null;
    const imgPosteriorFile = tr.querySelector(".img_posterior")?.files?.[0] || null;

    out.push({
      nodoTR: tr, // ✅ REFERENCIA DIRECTA AL NODO
      index,
      id_vehiculo: tr.dataset.id_vehiculo ? Number(tr.dataset.id_vehiculo) : null,
      publicar: tr.querySelector(".chk_publicar")?.checked ? 1 : 0,

      referencias: tr.querySelector(".referencias").value || "",
      id_modelo: tr.querySelector(".id_modelo").value,
      tipo_auto: tr.querySelector(".tipo_auto").value,
      id_marca: tr.querySelector(".id_marca").value,
      tipo_traccion: tr.querySelector(".tipo_traccion").value,
      funcionamiento_motor: tr.querySelector(".funcionamiento_motor").value,
      
      anio: tr.querySelector(".anio").value.trim(),
      condicion: tr.querySelector(".condicion").value,
      kilometraje: tr.querySelector(".kilometraje").value.trim(),
      
      transmision: tr.querySelector("select.transmision").value,
      tipo_vendedor: tr.querySelector(".tipo_vendedor").value,

      inicio_placa: tr.querySelector(".inicio_placa").value.trim(),
      fin_placa: tr.querySelector(".fin_placa").value.trim(),

      provincia: tr.querySelector("select.provincia").value.trim(),
      canton: tr.querySelector("select.canton").value.trim(),

      color: tr.querySelector("select.color").value,
      cilindraje: tr.querySelector(".cilindraje").value,

      tapiceria: tr.querySelector(".tapiceria").value,
      tipo_duenio: tr.querySelector(".tipo_duenio").value,
      direccion: tr.querySelector(".direccion").value,
      climatizacion: tr.querySelector(".climatizacion").value,

      descripcion: tr.querySelector(".descripcion").value.trim(),

      precio_referencia: parseFloat(tr.querySelector(".precio_referencia").value || "0"),
      descuento: parseFloat(tr.querySelector(".descuento").value || "0"),
      iva: tr.querySelector(".iva").checked ? 1 : 0,
      negociable: tr.querySelector(".negociable").checked ? 1 : 0,
      tags: tr.querySelector(".tags").value.trim(),

      files: {
        img_frontal: imgFrontalFile,
        img_posterior: imgPosteriorFile,
        archivos: tr.querySelector(".archivos")?.files ? Array.from(tr.querySelector(".archivos").files) : []
      },

      img_frontal_actual: tr.dataset.img_frontal || "",
      img_posterior_actual: tr.dataset.img_posterior || "",
      archivos_actual: tr.dataset.archivos_actual || "[]"
    });
  });

  return out;
}

/***********************
 * VALIDACIÓN
 ***********************/
function validarFila(v){
  const errores = [];
  const fila = v.index + 1;
  const isNew = !v.id_vehiculo;

  if (isEmptyStr(v.anio)) errores.push(`Fila ${fila}: Falta año`);
  if (isEmptyStr(v.condicion)) errores.push(`Fila ${fila}: Falta condición`);
  if (isEmptyStr(v.tipo_auto)) errores.push(`Fila ${fila}: Falta tipo auto`);
  if (isEmptyStr(v.id_marca)) errores.push(`Fila ${fila}: Falta marca`);
  if (isEmptyStr(v.tipo_traccion)) errores.push(`Fila ${fila}: Falta tracción`);
  if (isEmptyStr(v.funcionamiento_motor)) errores.push(`Fila ${fila}: Falta funcionamiento motor`);
  if (isEmptyStr(v.id_modelo)) errores.push(`Fila ${fila}: Falta modelo`);
  if (isEmptyStr(v.kilometraje)) errores.push(`Fila ${fila}: Falta kilometraje`);
  if (isEmptyStr(v.provincia)) errores.push(`Fila ${fila}: Falta provincia`);
  if (isEmptyStr(v.canton)) errores.push(`Fila ${fila}: Falta cantón`);
  if (isEmptyStr(v.descripcion)) errores.push(`Fila ${fila}: Falta descripción`);
  if (!(Number(v.precio_referencia) > 0)) errores.push(`Fila ${fila}: Precio debe ser > 0`);
  if (isEmptyStr(v.tags)) errores.push(`Fila ${fila}: Falta tags`);

  if (isNew){
    if (!v.files.img_frontal) errores.push(`Fila ${fila}: Falta imagen frontal`);
    if (!v.files.img_posterior) errores.push(`Fila ${fila}: Falta imagen posterior`);
  } else {
    const tieneFrontal = !!v.img_frontal_actual || !!v.files.img_frontal;
    const tienePosterior = !!v.img_posterior_actual || !!v.files.img_posterior;
    if (!tieneFrontal) errores.push(`Fila ${fila}: Falta imagen frontal`);
    if (!tienePosterior) errores.push(`Fila ${fila}: Falta imagen posterior`);
  }

  return errores;
}

/***********************
 * SUBIDA IMÁGENES / ARCHIVOS
 ***********************/
function subirImagenesPrincipalesFila(imgFrontalFile, imgPosteriorFile){
  return new Promise((resolve, reject) => {
    if (!imgFrontalFile && !imgPosteriorFile){
      resolve({ img_frontal:"", img_posterior:"" });
      return;
    }
    const fd = new FormData();
    if (imgFrontalFile) fd.append("img_frontal", imgFrontalFile);
    if (imgPosteriorFile) fd.append("img_posterior", imgPosteriorFile);

    $.ajax({
      url: "../admin/cargar_imagenes_frontales_masivo.php",
      method: "POST",
      data: fd,
      processData: false,
      contentType: false,
      dataType: "json",
      success: (res) => {
        if (res?.response === "success"){
          resolve({
            img_frontal: res.data?.img_frontal || "",
            img_posterior: res.data?.img_posterior || ""
          });
        } else reject(new Error(res?.error || "Error al subir imágenes"));
      },
      error: () => reject(new Error("Error de red al subir imágenes"))
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

/***********************
 * GUARDAR MASIVO (P / A) CON UX SEGURA Y DELETE
 ***********************/
async function guardarMasivo(estado) {
  const rows = getRowsData();
  const aProcesar = (estado === "A") ? rows.filter(r => r.publicar === 1) : rows;

  if (estado === "A" && !aProcesar.length) {
    SweetAlert("warning", "Selecciona al menos una fila en la casilla de 'Publicar'.");
    return;
  }

  // ✅ Validar SOLO si PUBLICA
  if (estado === "A") {
    for (let i = 0; i < aProcesar.length; i++) {
      const r = aProcesar[i];
      const fila = (r.index != null) ? (Number(r.index) + 1) : (i + 1);

      const erroresFila = validarFila(r) || [];
      if (erroresFila.length) {
        const msg = erroresFila.map(e => e.startsWith("Fila ") ? e : `Fila ${fila}: ${e}`);
        SweetAlert("error", "Corrige estos campos:\n\n" + msg.join("\n"));
        return;
      }
    }
  }

  const isRowEmpty = (v) => {
    const hasText = !!String(v.anio || "").trim() || !!String(v.kilometraje || "").trim() || !!String(v.inicio_placa || "").trim() || !!String(v.fin_placa || "").trim() || !!String(v.color || "").trim() || !!String(v.cilindraje || "").trim() || !!String(v.descripcion || "").trim() || !!String(v.tags || "").trim();
    const hasSelects = !!String(v.id_modelo || "").trim() || !!String(v.id_marca || "").trim() || !!String(v.tipo_auto || "").trim() || !!String(v.tipo_traccion || "").trim() || !!String(v.funcionamiento_motor || "").trim();
    const hasMoney = Number(v.precio_referencia || 0) > 0 || Number(v.descuento || 0) > 0;
    const hasFiles = !!v.files?.img_frontal || !!v.files?.img_posterior || (Array.isArray(v.files?.archivos) && v.files.archivos.length);

    return !(hasText || hasSelects || hasMoney || hasFiles);
  };

  const btnGuardar = document.getElementById("btnGuardarBorrador");
  const btnPublicar = document.getElementById("btnPublicar");
  const origGuardar = btnGuardar.innerText;
  const origPublicar = btnPublicar.innerText;

  try {
    // Bloquear Botones
    btnGuardar.disabled = true;
    btnPublicar.disabled = true;
    if(estado === "P") btnGuardar.innerText = "Procesando...";
    else btnPublicar.innerText = "Procesando...";

    const okCreate = [];
    const okUpdate = [];
    const skipped = [];
    const fail = [];

    for (const v of aProcesar) {
      const filaHuman = (v.index != null) ? (Number(v.index) + 1) : 0;

      if (estado === "P" && isRowEmpty(v)) {
        skipped.push(filaHuman || "-");
        continue;
      }

      try {
        let img_frontal_final = v.img_frontal_actual || "";
        let img_posterior_final = v.img_posterior_actual || "";

        const seleccionoFrontal = !!v.files?.img_frontal;
        const seleccionoPosterior = !!v.files?.img_posterior;

        if (estado === "A") {
          const requiereSubidaImagen = !v.id_vehiculo || v.files?.img_frontal || v.files?.img_posterior;
          if (requiereSubidaImagen) {
            if (!(seleccionoFrontal && seleccionoPosterior)) {
              throw new Error("Para publicar imágenes debes seleccionar frontal y posterior.");
            }
            const imgs = await subirImagenesPrincipalesFila(v.files.img_frontal, v.files.img_posterior);
            img_frontal_final = imgs.img_frontal || img_frontal_final;
            img_posterior_final = imgs.img_posterior || img_posterior_final;
          }
        } else {
          if (seleccionoFrontal || seleccionoPosterior) {
            const imgs = await subirImagenesPrincipalesFila(v.files.img_frontal, v.files.img_posterior);
            if (imgs.img_frontal) img_frontal_final = imgs.img_frontal;
            if (imgs.img_posterior) img_posterior_final = imgs.img_posterior;
          }
        }

        let archivosSubidos = [];
        if (v.files?.archivos && v.files.archivos.length) {
          archivosSubidos = await saveFiles(v.files.archivos);
        }

        const archivosFinal = archivosSubidos;

        const payloadBase = {
          id_empresa: getIdEmpresa(),
          tipo_creador: $("#tipo_user").val() || "empresa",

          id_modelo: v.id_modelo || "",
          anio: v.anio || "",
          tipo_auto: v.tipo_auto || "",
          id_marca: v.id_marca || "",
          kilometraje: v.kilometraje || "",
          tipo_traccion: v.tipo_traccion || "",
          funcionamiento_motor: v.funcionamiento_motor || "",
          inicio_placa: v.inicio_placa || "",
          fin_placa: v.fin_placa || "",
          color: v.color || "",
          cilindraje: v.cilindraje || "",
          descripcion: v.descripcion || "",
          precio_referencia: Number(v.precio_referencia || 0),
          descuento: Number(v.descuento || 0),
          iva: Number(v.iva || 0),
          negociable: Number(v.negociable || 0),
          tags: v.tags || "",

          condicion: v.condicion ? [v.condicion] : [],
          transmision: v.transmision ? [v.transmision] : [],
          tipo_vendedor: v.tipo_vendedor ? [v.tipo_vendedor] : [],
          provincia: v.provincia ? [v.provincia] : [],
          canton: v.canton ? [v.canton] : [],
          tapiceria: v.tapiceria ? [v.tapiceria] : [],
          tipo_dueno: v.tipo_duenio ? [v.tipo_duenio] : [],
          direccion: v.direccion ? [v.direccion] : [],
          climatizacion: v.climatizacion ? [v.climatizacion] : [],
          referencias: v.referencias ? [v.referencias] : [],

          img_frontal: img_frontal_final,
          img_posterior: img_posterior_final,
          
          // ✅ ENVIADO EN EL MISMO FORMATO
          archivos: { archivos: archivosFinal }
        };

        if (!v.id_vehiculo) {
          const resp = await postJQ("../api/v1/fulmuv/vehiculos/create", {
            ...payloadBase,
            estado
          });

          const r = typeof resp === "string" ? JSON.parse(resp) : resp;

          if (r.error === false) {
            okCreate.push(filaHuman);
            const newId = r.data?.id_vehiculo || r.id_vehiculo || null;
            if (newId && v.nodoTR) {
              v.nodoTR.dataset.id_vehiculo = newId;
            }
          } else {
            fail.push({ fila: filaHuman, msg: r.msg || "Error al crear" });
          }

        } else {
          const resp = await postJQ("../api/v1/fulmuv/vehiculos/update_full", {
            ...payloadBase,
            id_vehiculo: v.id_vehiculo,
            estado
          });

          const r = typeof resp === "string" ? JSON.parse(resp) : resp;
          if (r.error === false) okUpdate.push(filaHuman);
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
    for (const idEliminar of vehiculosEliminados) {
      try {
        const respDel = await postJQ("../api/v1/fulmuv/vehiculos/delete", { id: idEliminar });
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
    
    vehiculosEliminados = []; // Vaciamos la memoria de borrados

    let resultMsg = (estado === "P") ? "Borrador guardado" : "Publicación completada";
    SweetAlert("success", `${resultMsg}.\nCreados: ${okCreate.length}\nActualizados: ${okUpdate.length}\nEliminados: ${okDelete.length}\nErrores: ${fail.length}`);

    if (estado === "P") {
      await cargarBorradorVehiculos();
      if (!tbody.children.length) addRow();
    } else {
      await cargarBorradorVehiculos(); // Refresca si quedó algo en borrador
    }

  } finally {
    // Restaurar botones pase lo que pase
    btnGuardar.disabled = false;
    btnPublicar.disabled = false;
    btnGuardar.innerText = origGuardar;
    btnPublicar.innerText = origPublicar;
  }
}