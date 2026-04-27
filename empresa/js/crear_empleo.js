let categorias = [];
let modelos = [];
let tipos_auto = [];
let marcas = [];
let traccion = [];
let motor = [];

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

let tagsInput = null;

function resolveTipoCreador() {
  const rawTipo = String($("#tipo_user").val() || "").trim().toLowerCase();
  const rawRol = String($("#id_rol_principal").val() || "").trim();

  if (rawTipo === "sucursal" || rawTipo === "3" || rawRol === "3") {
    return "sucursal";
  }

  return "empresa";
}

let tipo_user = resolveTipoCreador();

// ✅ Dropzone global
window.myDropzone = null;

/* ==============================
   Helpers
============================== */

function fileUrlAdmin(path){
  if(!path) return '';
  const p = String(path).replace(/\\/g,'/').trim();
  if(/^https?:\/\//i.test(p)) return p;
  if(p.startsWith('../')) return p;
  if(p.startsWith('admin/')) return '../' + p;
  if(p.startsWith('/admin/')) return '..' + p;
  return '../admin/' + p.replace(/^\/+/, '');
}

function parseTagsAny(raw){
  if (!raw) return [];
  if (Array.isArray(raw)) return raw.map(s => String(s).trim()).filter(Boolean);

  const s = String(raw).trim();
  if (!s) return [];

  if (s.startsWith('[') && s.endsWith(']')) {
    try {
      const parsed = JSON.parse(s);
      if (Array.isArray(parsed)) return parsed.map(x => String(x).trim()).filter(Boolean);
    } catch(e){}
  }

  return s.split(',').map(x => x.trim()).filter(Boolean);
}

function setTagsChoices(tagsArr){
  try{
    if (!tagsInput) return;

    if (typeof tagsInput.removeActiveItems === 'function') tagsInput.removeActiveItems();

    const payload = (tagsArr || [])
      .map(t => String(t).trim())
      .filter(Boolean)
      .map(t => ({ value: t.toUpperCase(), label: t.toUpperCase() }));

    tagsInput.setValue(payload);
  }catch(e){
    console.log("setTagsChoices error:", e);
  }
}

/* ==============================
   TinyMCE (para editar)
============================== */
window._descPendingHTML = null;

function setDescripcionHTML(html){
  const content = html || '';

  if (window.tinymce && tinymce.get('descripcion')) {
    tinymce.get('descripcion').setContent(content);
    return true;
  }

  window._descPendingHTML = content;
  $('#descripcion').val(content);
  return false;
}

function initTinyMCEIfNeeded(){
  if (!window.tinymce) {
    console.warn("TinyMCE no está cargado. Revisa que el theme incluya tinymce.min.js");
    return;
  }

  if (tinymce.get('descripcion')) return;

  tinymce.init({
    selector: '#descripcion',
    menubar: false,
    height: 260,
    plugins: 'lists link table code',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
    setup: function (editor) {
      editor.on('init', function () {
        if (window._descPendingHTML !== null) {
          editor.setContent(window._descPendingHTML);
          window._descPendingHTML = null;
        }
      });
    }
  });
}

/* ==============================
   READY
============================== */

$(document).ready(function () {
  tipo_user = resolveTipoCreador();

  $("#provincia").select2({ theme: 'bootstrap-5' });
  $("#canton").select2({ theme: 'bootstrap-5' });

  tagsInput = new Choices('#tags', {
    removeItemButton: true,
    placeholder: false,
    maxItemCount: 10
  });

  // ✅ Dropzone (FIX container + template)
  const previews = document.querySelector("#file-previews");
  const template = previews ? previews.innerHTML : "";

  if (previews) previews.innerHTML = "";

  window.myDropzone = new Dropzone("#myAwesomeDropzone", {
    url: "../admin/cargar_imagen_multiple.php", // (no se usa si autoProcessQueue=false)
    paramName: "archivos[]",
    acceptedFiles: "image/*,application/pdf",
    uploadMultiple: true,
    parallelUploads: 10,
    autoProcessQueue: false,  // ✅ tú subes con saveFiles()
    previewsContainer: previews,
    previewTemplate: template,
    init: function () {
      this.on("addedfile", function (file) {
        const pdfCount = this.files.filter(f => f.type === "application/pdf").length;
        if (pdfCount > 1) {
          this.removeFile(file);
          toastr.options.timeOut = 1500;
          toastr.warning("Solo se permite un archivo PDF!");
        }
      });
    }
  });

  // Preview imágenes principales
  $("#img_frontal").on("change", function () {
    const file = this.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => $("#preview_frontal").attr("src", e.target.result).show();
    reader.readAsDataURL(file);
  });

  $("#img_posterior").on("change", function () {
    const file = this.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => $("#preview_posterior").attr("src", e.target.result).show();
    reader.readAsDataURL(file);
  });

  // TinyMCE
  initTinyMCEIfNeeded();

  // Si viene ID => editar
  const idEmpleoEdit = Number($("#id_empleo").val() || 0);
  if (idEmpleoEdit > 0) {
    cargarEmpleoParaEditar(idEmpleoEdit);
  }
});

/* ==============================
   Cantones
============================== */
function cargarCantones(provincia) {
  const cantonSelect = document.getElementById("canton");
  if (!cantonSelect) return;

  cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';

  if (provincia && cantones[provincia]) {
    cantones[provincia].forEach(canton => {
      const option = document.createElement("option");
      option.value = canton;
      option.textContent = canton;
      cantonSelect.appendChild(option);
    });
  }
}

/* ==============================
   Guardar
============================== */
function guardarEmpleo(){
  tipo_user = resolveTipoCreador();
  const id = Number($("#id_empleo").val() || 0);
  const $btn = $("#btnGuardarEmpleo");
  const originalHtml = $btn.html();
  $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

  const wrapAction = (fn) => () => Promise.resolve(fn()).catch(() => {
    $btn.prop("disabled", false).html(originalHtml);
  });

  if (!id) {
    validarMembresiaEmpleo(wrapAction(() => crearEmpleo()));
    return;
  }
  validarMembresiaEmpleo(wrapAction(() => actualizarEmpleo(id)), id);
}

function validarMembresiaEmpleo(onSuccess, idEmpleo = 0) {
  let id_empresa;
  if ($("#id_rol_principal").val() == 1) id_empresa = $("#lista_empresas").val();
  else id_empresa = $("#id_empresa").val();

  $.get('../api/v1/fulmuv/validarMembresiaProductos/' + id_empresa + '/' + tipo_user, {
    modulo: 'empleo',
    id_registro: idEmpleo || 0
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
      return;
    }

    if (typeof onSuccess === "function") {
      onSuccess();
    }
  }).fail(function () {
    SweetAlert("error", "Error de red validando la membresía.");
  });
}

/* ==============================
   CREATE
============================== */
function crearEmpleo() {
  return new Promise((resolve, reject) => {

  let descripcion = $("#descripcion").val();
  if (window.tinymce && tinymce.get('descripcion')) {
    descripcion = tinymce.get('descripcion').getContent();
    descripcion = emojiToEntities(descripcion);
  }

  const titulo = $("#titulo").val().trim();
  const provincia = $("#provincia").val();
  const canton = $("#canton").val();
  const fecha_inicio = $("#fecha_inicio").val();
  const fecha_fin = $("#fecha_fin").val();

  let tags = (tagsInput?.getValue(true) || []).map(t => String(t).toUpperCase());

  let id_empresa;
  if ($("#id_rol_principal").val() == 1) id_empresa = $("#lista_empresas").val();
  else id_empresa = $("#id_empresa").val();

  if (!titulo || !descripcion || !provincia || !canton || !fecha_inicio || !fecha_fin) {
    SweetAlert("error", "Completa todos los campos obligatorios.");
    reject();
    return;
  }

  const dz = window.myDropzone;
  const files = dz ? dz.getAcceptedFiles() : [];

  subirImagenesPrincipales().then(imagenes => {
    saveFiles(files).then(archivos => {

      $.post('../api/v1/fulmuv/empleos/create', {
        titulo,
        descripcion,
        provincia,
        canton,
        tags: tags.join(', '),
        img_frontal: imagenes.img_frontal,
        img_posterior: imagenes.img_posterior,
        archivos: archivos || [],
        id_empresa,
        tipo_creador: tipo_user,
        fecha_inicio,
        fecha_fin
      }, function(resp){
        const r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (!r.error) {
          SweetAlert("url_success", r.msg, "empleos.php");
          resolve();
        } else {
          SweetAlert("error", r.msg || "Error al crear.");
          $("#btnGuardarEmpleo").prop("disabled", false).html("Registrar empleo");
          reject();
        }
      });

    }).catch(() => {
      $("#btnGuardarEmpleo").prop("disabled", false).html("Registrar empleo");
      reject();
    });
  }).catch(() => {
    $("#btnGuardarEmpleo").prop("disabled", false).html("Registrar empleo");
    reject();
  });

  });
}

/* ==============================
   Subir imágenes principales
============================== */
function subirImagenesPrincipales() {
  return new Promise(function (resolve, reject) {
    const imgFrontal = document.getElementById('img_frontal')?.files?.[0];
    const imgPosterior = document.getElementById('img_posterior')?.files?.[0];

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
          SweetAlert("error", res.error || "Error al subir imágenes.");
          reject();
        }
      },
      error: function () {
        SweetAlert("error", "Error de red al subir imágenes.");
        reject();
      }
    });
  });
}

/* ==============================
   Guardar archivos dropzone (CREATE)
============================== */
function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    if (!files || !files.length) { resolve([]); return; }

    const formData = new FormData();
    files.forEach(file => formData.append('archivos[]', file));

    $.ajax({
      type: 'POST',
      data: formData,
      url: '../admin/cargar_imagen_multiple.php',
      cache: false,
      contentType: false,
      processData: false,
      success: function (r) {
        if (r.response === "success") resolve(r.data);
        else { SweetAlert("error", "Error al guardar archivos."); reject(); }
      },
      error: reject
    });
  });
}

/* ==============================
   EDIT: Cargar
============================== */
function cargarEmpleoParaEditar(id){
  $.ajax({
    url: '../api/v1/fulmuv/empleos/' + id,
    type: 'GET',
    dataType: 'json',
    success: function(r){

      const e = r.data || r;
      if (!e || !e.id_empleo) {
        SweetAlert("error", "No se encontró el empleo.");
        return;
      }

      $("#titulo").val(e.titulo || '');

      $("#provincia").val(e.provincia || '').trigger('change');
      cargarCantones(e.provincia || '');
      setTimeout(() => $("#canton").val(e.canton || '').trigger('change'), 0);

      $("#fecha_inicio").val(e.fecha_inicio || '');
      $("#fecha_fin").val(e.fecha_fin || '');

      // ✅ descripción
      setDescripcionHTML(e.descripcion || '');

      // ✅ tags
      setTagsChoices(parseTagsAny(e.tags));

      // ✅ imágenes actuales + preview
      if (e.img_frontal) {
        $("#img_frontal_old").val(e.img_frontal);
        $("#preview_frontal").attr("src", fileUrlAdmin(e.img_frontal)).show();
      }
      if (e.img_posterior) {
        $("#img_posterior_old").val(e.img_posterior);
        $("#preview_posterior").attr("src", fileUrlAdmin(e.img_posterior)).show();
      }

      // ✅ dropzone archivos existentes
      preloadDropzoneFiles(e.archivos);

      // ✅ no obligar a subir imagen en editar
      $("#img_frontal, #img_posterior").prop("required", false);

      $("#btnGuardarEmpleo").text("Actualizar empleo");
    },
    error: function(xhr){
      SweetAlert("error", "Error al consultar empleo.");
      console.log(xhr.responseText);
    }
  });
}

/* ==============================
   UPDATE
============================== */
function actualizarEmpleo(id_empleo){
  return new Promise((resolve, reject) => {

  let descripcion = $("#descripcion").val();
  if (window.tinymce && tinymce.get('descripcion')) {
    descripcion = tinymce.get('descripcion').getContent();
    descripcion = emojiToEntities(descripcion);
  }

  const titulo = $("#titulo").val().trim();
  const provincia = $("#provincia").val();
  const canton = $("#canton").val();
  const fecha_inicio = $("#fecha_inicio").val();
  const fecha_fin = $("#fecha_fin").val();

  let tags = (tagsInput?.getValue(true) || []).map(t => String(t).toUpperCase());

  let id_empresa;
  if ($("#id_rol_principal").val() == 1) id_empresa = $("#lista_empresas").val();
  else id_empresa = $("#id_empresa").val();

  if (!titulo || !descripcion || !provincia || !canton || !fecha_inicio || !fecha_fin) {
    SweetAlert("error", "Completa los campos obligatorios.");
    reject();
    return;
  }

  const dz = window.myDropzone;

  subirImagenesPrincipalesEdit().then(imagenes => {
    saveFilesEdit(dz).then(archivosNuevos => {

      $.post('../api/v1/fulmuv/empleos/update', {
        id_empleo,
        titulo,
        descripcion,
        provincia,
        canton,
        tags: tags.join(', '),
        img_frontal: imagenes.img_frontal,
        img_posterior: imagenes.img_posterior,
        archivos: archivosNuevos,
        id_empresa,
        tipo_creador: tipo_user,
        fecha_inicio,
        fecha_fin
      }, function(resp){
        const r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (!r.error) {
          SweetAlert("url_success", r.msg || "Actualizado.", "empleos.php");
          resolve();
        } else {
          SweetAlert("error", r.msg || "Error al actualizar.");
          $("#btnGuardarEmpleo").prop("disabled", false).html("Actualizar empleo");
          reject();
        }
      });

    }).catch(() => {
      $("#btnGuardarEmpleo").prop("disabled", false).html("Actualizar empleo");
      reject();
    });
  }).catch(() => {
    $("#btnGuardarEmpleo").prop("disabled", false).html("Actualizar empleo");
    reject();
  });

  });

}

function subirImagenesPrincipalesEdit(){
  return new Promise(function(resolve, reject){

    const imgFrontal = document.getElementById('img_frontal')?.files?.[0] || null;
    const imgPosterior = document.getElementById('img_posterior')?.files?.[0] || null;

    const imgFrontalActual = $("#img_frontal_old").val() || '';
    const imgPosteriorActual = $("#img_posterior_old").val() || '';

    if (!imgFrontal && !imgPosterior) {
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
      success: function(res){
        if (res.response === "success") {
          const nuevaFrontal = res.data?.img_frontal || null;
          const nuevaPosterior = res.data?.img_posterior || null;

          resolve({
            img_frontal: nuevaFrontal || imgFrontalActual,
            img_posterior: nuevaPosterior || imgPosteriorActual
          });
        } else {
          SweetAlert("error", res.error || "Error al subir imágenes.");
          reject();
        }
      },
      error: function(){
        SweetAlert("error", "Error de red al subir imágenes.");
        reject();
      }
    });

  });
}

function saveFilesEdit(dropzoneInstance){
  return new Promise(function(resolve, reject){

    if (!dropzoneInstance) { resolve([]); return; }

    // ✅ solo nuevos
    const nuevos = (dropzoneInstance.files || []).filter(f => !f.existing);

    if (!nuevos.length) { resolve([]); return; }

    const formData = new FormData();
    nuevos.forEach(file => formData.append('archivos[]', file));

    $.ajax({
      type: 'POST',
      data: formData,
      url: '../admin/cargar_imagen_multiple.php',
      cache: false,
      contentType: false,
      processData: false,
      success: function(r){
        if (r.response === "success") resolve(r.data);
        else { SweetAlert("error", "Error al guardar archivos."); reject(); }
      },
      error: reject
    });

  });
}


function normalizeArchivoItem(item){
  // Devuelve siempre un string tipo "uploads/archivo.png" o "" si no sirve
  if (!item) return "";

  // Si ya es string
  if (typeof item === "string") return item.trim();

  // Si es objeto, intenta campos comunes
  if (typeof item === "object") {
    return (
      item.path ||
      item.url ||
      item.archivo ||
      item.ruta ||
      item.file ||
      item.nombre ||
      ""
    ).toString().trim();
  }

  // Otros tipos
  return String(item).trim();
}

function parseArchivosAny(raw){
  if (!raw) return [];

  // Si ya es array
  if (Array.isArray(raw)) {
    return raw.map(normalizeArchivoItem).filter(Boolean);
  }

  // Si viene como objeto {data: [...]}
  if (typeof raw === "object" && raw.data) {
    const arr = Array.isArray(raw.data) ? raw.data : [raw.data];
    return arr.map(normalizeArchivoItem).filter(Boolean);
  }

  // Si viene como string JSON
  const s = String(raw).trim();
  if (!s) return [];

  if (s.startsWith("[") && s.endsWith("]")) {
    try {
      const parsed = JSON.parse(s);
      if (Array.isArray(parsed)) return parsed.map(normalizeArchivoItem).filter(Boolean);
    } catch(e){}
  }

  // Si viene como "a.png, b.pdf"
  if (s.includes(",")) return s.split(",").map(x => x.trim()).filter(Boolean);

  // Un solo archivo
  return [s];
}

function preloadDropzoneFiles(rawFiles){
  const dz = window.myDropzone;
  if (!dz) return;

  const files = parseArchivosAny(rawFiles);

  files.forEach((path, idx) => {
    const url = fileUrlAdmin(path);

    const fileName = (String(path).split("/").pop() || `archivo_${idx+1}`).trim();

    const mockFile = {
      name: fileName,
      size: 12345,
      accepted: true,
      status: Dropzone.SUCCESS,
      existing: true,       // ✅ CLAVE: para que saveFilesEdit NO lo vuelva a subir
      existingPath: path
    };

    dz.emit("addedfile", mockFile);

    // Thumbnail solo para imágenes
    if (/\.(png|jpe?g|webp|gif)$/i.test(url)) {
      dz.emit("thumbnail", mockFile, url);
    }

    dz.emit("complete", mockFile);

    // Importante: agregar a dz.files para que Dropzone lo "reconozca"
    dz.files.push(mockFile);
  });
}

function emojiToEntities(str) {
  // intenta capturar emojis; si el motor no soporta \p{...}, cae a un fallback
  try {
    return str.replace(/\p{Extended_Pictographic}/gu, (m) =>
      Array.from(m).map(ch => `&#${ch.codePointAt(0)};`).join('')
    );
  } catch (e) {
    // fallback simple: convierte cualquier char 4-bytes (emojis comunes) a entidades
    return Array.from(str).map(ch => {
      const cp = ch.codePointAt(0);
      return cp > 0xFFFF ? `&#${cp};` : ch;
    }).join('');
  }
}
