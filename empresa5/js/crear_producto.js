/* ================================
   FULMUV - CREAR/EDITAR PRODUCTO
   Normalización + Fixes select2
================================= */

let categorias = [];
var tagsInput = '';
let modelos = [];
let tipos_auto = [];
let marcas = [];
let traccion = [];
let motor = [];
let tipo_user = $("#tipo_user").val();
var id_producto = $("#id_producto").val();
const IVA_RATE = 0.15;

let imagenFrontalEdit = 0;
let imagenPosteriorEdit = 0;

/* ===== Helpers de normalización ===== */
function parseMaybeJSON(v) {
  if (typeof v !== 'string') return v;
  try { return JSON.parse(v); } catch (_) { return v; }
}

// Normaliza un solo registro (puede venir como objeto, array con 1 objeto, string, etc.)
function normSingle(v, idKey = 'id', textKey = 'nombre') {
  if (v == null) return null;

  v = parseMaybeJSON(v);

  // si viene como array, usamos el primero
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

// Normaliza varios (array/CSV/JSON)
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
    // CSV simple "1,2,3"
    if (s.includes(',')) {
      return s
        .split(',')
        .map(t => t.trim())
        .filter(Boolean)
        .map(x => ({ id: String(x), text: String(x) }));
    }
    // JSON de array u objeto
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

/* ===== Utilities para selects ===== */
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

function setTinyMCE(id, html) {
  const ed = window.tinymce?.get(id);
  if (ed) { ed.setContent(html || ''); }
  else { $("#" + id).val(html || ''); }
}

/* ================== Ready ================== */
$(document).ready(function () {

  /* Tags (Choices) */
  tagsInput = new Choices('#tags', {
    removeItemButton: true,
    placeholder: false,
    maxItemCount: 10,
    addItemText: (value) => `Presiona Enter para añadir <b>"${value}"</b>`,
    maxItemText: (maxItemCount) => `Solo ${maxItemCount} tags pueden ser añadidos`,
  });

  /* ======= Categorías ======= */
  $.get('../api/v1/fulmuv/categorias/', {
    tipo: 'producto',
    id_empresa: $("#id_empresa").val(),
    tipo_usuario: tipo_user
  }, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      categorias = returned.data;
      returned.data.forEach(categoria => {
        $("#categoria").append(
          `<option value="${categoria.id_categoria}">${categoria.nombre}</option>`
        );
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
      llenarSubCategria();
    }
  });


  // ✅ Mostrar/ocultar y recalcular cuando cambie IVA o el precio
  $(document).on("change", "#iva", renderIvaResumen);
  $(document).on("input", "#precio_referencia", renderIvaResumen);
  /* ======= Referencias ======= */
  $.get('../api/v1/fulmuv/getReferencias/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      returned.data.forEach(referencia => {
        $("#referencia").append(
          `<option value="${referencia}">${referencia}</option>`
        );
      });
      $("#referencia").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione referencia',
        allowClear: true,
      });
    }
  });

  /* ======= Tipos de vehículo ======= */
  $.get('../api/v1/fulmuv/tiposAuto/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      tipos_auto = returned.data;
      returned.data.forEach(t => {
        $("#tipo_vehiculo").append(
          `<option value="${t.id_tipo_auto}">${t.nombre}</option>`
        );
      });
      $("#tipo_vehiculo").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione tipo de vehículo',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#tipo_vehiculo'), {
        entity: 'tipos_auto',
        label: 'Tipo de vehículo'
      });
    }
  });

  /* ======= Marcas (vehículo) ======= */
  $.get('../api/v1/fulmuv/marcas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      marcas = returned.data;
      returned.data.forEach(m => {
        $("#marca").append(
          `<option value="${m.id_marca}">${m.nombre}</option>`
        );
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

  /* ======= Tracción ======= */
  $.get('../api/v1/fulmuv/tipo_tracccion/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      traccion = returned.data;
      returned.data.forEach(t => {
        $("#traccion").append(
          `<option value="${t.id_tipo_traccion}">${t.nombre}</option>`
        );
      });
      $("#traccion").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione tracción',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#traccion'), {
        entity: 'tipo_traccion',
        label: 'Tracción'
      });
    }
  });

  /* ======= Funcionamiento de motor ======= */
  $.get('../api/v1/fulmuv/getFuncionamientoMotor/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      motor = returned.data;
      returned.data.forEach(mo => {
        $("#motor").append(
          `<option value="${mo.id_funcionamiento_motor}">${mo.nombre}</option>`
        );
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
      wireSelectEnsure($('#motor'), {
        entity: 'funcionamiento_motor',
        label: 'Funcionamiento de motor'
      });
    }
  });

  /* ======= Marcas de producto ======= */
  $.get('../api/v1/fulmuv/getMarcasProductos/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      returned.data.forEach(mp => {
        $("#marca_producto").append(
          `<option value="${mp.id_marca_producto}">${mp.nombre}</option>`
        );
      });
      $("#marca_producto").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione marca de producto',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) term = term.substring(0, 100);
          if (term === '') return null;
          return { id: 'nuevo', text: term, newTag: true };
        }
      });
      wireSelectEnsure($('#marca_producto'), {
        entity: 'marcas_productos',
        label: 'Marca de producto'
      });
    }
  });

  /* ======= Dropzone ======= */
  $("#myAwesomeDropzone").attr("data-dropzone", 'data-dropzone');
  let myDropzone = new Dropzone("#myAwesomeDropzone", {
    url: "#",
    acceptedFiles: "image/*,application/pdf",
    previewsContainer: document.querySelector(".dz-preview"),
    previewTemplate: document.querySelector(".dz-preview").innerHTML,
    init: function () {
      $("#file-previews").empty()
      this.on("addedfile", function (file) {
        let pdfFileCount = 0;
        this.files.forEach(function (f) {
          if (f.type === "application/pdf") pdfFileCount++;
        });
        if (pdfFileCount > 1) {
          this.removeFile(file);
          toastr.options.timeOut = 1500;
          toastr.warning("Solo se permite un archivo PDF!");
        }
      });
    }
  });

  /* ======= Empresas (si admin) ======= */
  if ($("#id_rol_principal").val() == 1) {
    $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(empresa => {
          $("#lista_empresas").append(
            `<option value="${empresa.id_empresa}">${empresa.nombre}</option>`
          );
        });
      }
    });
  } else {
    $("#searh_empresa").empty()
  }

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

  $("#img_frontal").on("change", function () {
    previewFile(this, "#preview_frontal");
  });

  $("#img_posterior").on("change", function () {
    previewFile(this, "#preview_posterior");
  });

  /* ======= Cargar producto (edición) ======= */
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

      // === Básicos
      $("#titulo_producto").val(p.titulo_producto || "");
      $("#codigo").val(p.codigo || "");
      $("#peso").val(p.peso || "");
      setTinyMCEWhenReady('descripcion', p.descripcion || "");

      if (p.img_frontal) $("#preview_frontal").removeClass("d-none").attr("src", "../admin/" + p.img_frontal);
      if (p.img_posterior) $("#preview_posterior").removeClass("d-none").attr("src", "../admin/" + p.img_posterior);


      const ivaEnBD = String(p.iva) === "1" || p.iva === true;
      const precioBD = Number(p.precio_referencia ?? 0);

      // ✅ Si IVA=1, en BD tienes TOTAL -> mostramos BASE
      const precioMostrar = ivaEnBD ? calcularBaseDesdeTotalIVA(precioBD) : precioBD;

      $("#precio_referencia").val(precioMostrar || "");
      $("#descuento").val(p.descuento ?? "");
      setCheckbox($("#iva"), p.iva);
      setCheckbox($("#negociable"), p.negociable);
      renderIvaResumen();

      // Tags
      const tagsArr = Array.isArray(p.tags) ? p.tags : safeUpperArrayCSV(p.tags);
      try { tagsInput.clearStore(); } catch (e) { }
      if (tagsArr?.length) tagsArr.forEach(t => tagsInput.setValue([t]));

      // === Marca de producto (single)
      {
        const mp = normSingle(p.marca_productos, 'id_marca_producto', 'nombre');
        if (mp) setSelectValue($("#marca_producto"), mp.id, mp.text);
      }

      // === Categoría (multi)
      {
        let catPairs = normMulti(p.categoria, 'id_categoria', 'nombre');
        if (!catPairs.length) catPairs = normMulti(p.id_categoria);
        setSelectMultiplePairs($("#categoria"), catPairs);

        llenarSubCategria();
        setTimeout(() => {
          let subPairs = normMulti(p.sub_categoria, 'id_sub_categoria', 'nombre');
          setSelectMultiplePairs($("#sub_categoria"), subPairs);
        }, 250);
      }

      // === Referencia
      // if (p.referencia) {
      //   setSelectValue($("#referencia"), p.referencias, p.referencias);
      // }
      // === Referencia (puede venir como p.referencias o p.referencia)
      /* const refs = p.referencias ?? p.referencia;
      setTimeout(() => {
        setSelect2ValueByText($("#referencia"), refs);
        // si los modelos dependen de referencia:
        buscarModelosReferencia();
      }, 300);
 
      // === Modelos (multi, depende de referencia)
      {
        const modelosPairs = normMulti(p.modelo, 'id_modelos_autos', 'nombre');
        if (p.referencia) {
          //buscarModelosReferencia();
          setTimeout(() => setSelectMultiplePairs($("#modelo"), modelosPairs), 400);
        } else {
          setSelectMultiplePairs($("#modelo"), modelosPairs);
        }
      } */

      // === Referencia
      const refs = p.referencias ?? p.referencia;
      setSelect2ValueByText($("#referencia"), refs);

      // === Modelos: primero cargar por referencia, luego asignar
      const modelosPairs = normMulti(p.modelo, 'id_modelos_autos', 'nombre');

      // IMPORTANTE: aquí esperamos a que termine el ajax de modelos
      buscarModelosReferencia($("#referencia").val(), modelosPairs);


      // === Tipo de vehículo (multi)
      {
        let tvPairs = normMulti(p.tipo_vehiculo, 'id_tipo_auto', 'nombre');
        if (!tvPairs.length) tvPairs = normMulti(p.tipo_auto, 'id', 'nombre');
        setSelectMultiplePairs($("#tipo_vehiculo"), tvPairs);
      }

      // === Marca de vehículo (multi)
      {
        const marPairs = normMulti(p.marca, 'id', 'nombre');
        setSelectMultiplePairs($("#marca"), marPairs);
      }

      // === Tracción (multi) ✅ viene como IDs en p.tipo_traccion
      {
        const idsTraccion = normMulti(p.tipo_traccion)
          .map(x => String(x.id).trim())
          .filter(id => id && id !== "0" && id !== "null" && id !== "undefined");

        idsTraccion.forEach(id => ensureOption($("#traccion"), id, id));
        $("#traccion").val(idsTraccion).trigger("change");
      }

      // === Funcionamiento de motor (multi)
      {
        let motPairs = normMulti(p.motor, 'id_funcionamiento_motor', 'nombre');
        if (!motPairs.length) motPairs = normMulti(p.funcionamiento_motor, 'id', 'nombre');
        setSelectMultiplePairs($("#motor"), motPairs);
      }

      // === Rutas imágenes actuales (por si las necesitas en update)
      if (!$("#img_frontal_actual").length) {
        $('<input type="hidden" id="img_frontal_actual">').appendTo('body');
        $('<input type="hidden" id="img_posterior_actual">').appendTo('body');
      }
      $("#img_frontal_actual").val(p.img_frontal || "");
      $("#img_posterior_actual").val(p.img_posterior || "");

      // ✅ En edición NO obligar a re-subir frontal/posterior
      $("#img_frontal, #img_posterior").prop("required", false);


      // === Galería: archivos existentes a Dropzone
      const archivosGaleria =
        (resp.data && resp.data.archivos) ? resp.data.archivos :
          (resp.archivos || []);

      if (myDropzone && Array.isArray(archivosGaleria)) {
        myDropzone.removeAllFiles(true);

        archivosGaleria.forEach(function (arch) {
          const ruta = "../admin/" + arch.archivo; // ajusta prefijo si hace falta
          const esImagen = arch.tipo === 'imagen' ||
            /\.(png|jpe?g|webp|gif)$/i.test(ruta);

          const mockFile = {
            name: ruta.split('/').pop(),
            size: 123456,
            type: esImagen ? 'image/*' : 'application/pdf',
            accepted: true
          };

          myDropzone.emit("addedfile", mockFile);

          if (esImagen) {
            myDropzone.emit("thumbnail", mockFile, ruta);
          }

          myDropzone.emit("complete", mockFile);
          mockFile.status = Dropzone.SUCCESS;
          mockFile.existing = true;

          // 🔗 Conectar el botón "Eliminar archivo" a esta fila
          const $preview = $(mockFile.previewElement);

          $preview.find('.dz-remove-galeria')
            .off('click')
            .on('click', function (e) {
              e.preventDefault();
              eliminarArchivoGaleria(arch.id_archivo_producto, mockFile);
            });
        });
      }

    }, 'json');
  }
});

$("#referencia").on("change", async function () {
  $("#modelo").val(null).trigger("change");
  await buscarModelosReferencia($(this).val(), []);
});


function money(n) {
  const x = Number(n || 0);
  return new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD' }).format(x);
}

function calcularPrecioConIVA(precioBase) {
  const base = Number(precioBase || 0);
  const iva = +(base * IVA_RATE).toFixed(2);
  const total = +(base + iva).toFixed(2);
  return { base, iva, total };
}

function renderIvaResumen() {
  const base = Number($("#precio_referencia").val() || 0);
  const marcado = $("#iva").is(":checked");

  if (!marcado || !base || base <= 0) {
    $("#ivaResumen").addClass("d-none");
    $("#ivaCalculo").html("");
    return;
  }
  const { iva, total } = calcularPrecioConIVA(base);

  $("#ivaCalculo").html(`
    Base: <b>${money(base)}</b> 
    + IVA (15%): <b>${money(iva)}</b> 
    = Total: <b>${money(total)}</b>
  `);
  $("#ivaResumen").removeClass("d-none");
}



function setSelect2ValueByText($select, values) {
  if (!values) return;

  // normaliza a array
  let arr = [];
  if (Array.isArray(values)) arr = values;
  else if (typeof values === "string") {
    // intenta JSON array o CSV
    try {
      const j = JSON.parse(values);
      arr = Array.isArray(j) ? j : [values];
    } catch (e) {
      arr = values.split(",").map(x => x.trim()).filter(Boolean);
    }
  } else {
    arr = [String(values)];
  }

  // si tu select NO es multiple, solo toma la primera
  const isMultiple = $select.prop("multiple");
  if (!isMultiple && arr.length) arr = [arr[0]];

  // crea/selecciona opciones
  arr.forEach(v => {
    const val = String(v).toUpperCase().trim();
    if (!val) return;

    // si no existe option, créala (para tags:true)
    if ($select.find(`option[value="${CSS.escape(val)}"]`).length === 0) {
      const opt = new Option(val, val, true, true);
      $select.append(opt);
    }
  });

  $select.val(arr.map(x => String(x).toUpperCase().trim())).trigger("change");
}


/* ======= Poblar subcategorías ======= */
function llenarSubCategria() {
  const sel = $("#categoria").val();
  $("#sub_categoria").empty();

  const $sele = $('#sub_categoria');
  if ($sele.hasClass('select2-hidden-accessible')) $sele.select2('destroy');

  if (!sel || (Array.isArray(sel) && sel.length === 0)) {
    $sele.attr('multiple', 'multiple');
    $sele.select2({ theme: 'bootstrap-5' });
    return;
  }

  const categoriaId = Array.isArray(sel) ? parseInt(sel[0], 10) : parseInt(sel, 10);
  if (isNaN(categoriaId)) {
    $sele.attr('multiple', 'multiple');
    $sele.select2({ theme: 'bootstrap-5' });
    return;
  }

  const cat = (categorias || []).find(c => String(c.id_categoria) === String(categoriaId));
  if (!cat || !Array.isArray(cat.sub_categorias)) {
    $sele.attr('multiple', 'multiple');
    $sele.select2({ theme: 'bootstrap-5' });
    return;
  }

  cat.sub_categorias.forEach(sc => {
    $("#sub_categoria").append(
      `<option value="${sc.id_sub_categoria}">${sc.nombre}</option>`
    );
  });

  $sele.attr('multiple', 'multiple');
  $sele.select2({ theme: 'bootstrap-5' }).trigger("change");

  $.get('../api/v1/fulmuv/atributosCategoriaCompleto/' + categoriaId, {}, function (_returnedData) {
    // Aquí podrías llamar a renderizarAtributos si lo necesitas
  });
}

/* ======= Modelos por referencia ======= */
/* function buscarModelosReferencia() {
  var referencia = $("#referencia").val();
  $.get('../api/v1/fulmuv/getModelosByReferencia/' + referencia, {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    modelos = returned.data || [];
    $("#modelo").text("");

    modelos.forEach(model => {
      $("#modelo").append(
        `<option value="${model.id_modelos_autos}">${model.nombre}</option>`
      );
    });

    const $sel = $('#modelo');
    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
    $sel.attr('multiple', 'multiple');

    $("#modelo").select2({
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
  });
} */
function buscarModelosReferencia(refs, modelosSeleccionar = []) {
  return new Promise((resolve, reject) => {

    // refs puede venir null / string / array
    let referencia = refs ?? $("#referencia").val();
    if (Array.isArray(referencia)) referencia = referencia[0]; // tu API usa 1 referencia
    if (!referencia) {
      // limpia y resuelve
      $("#modelo").empty().trigger("change");
      return resolve([]);
    }

    $.ajax({
      url: '../api/v1/fulmuv/getModelosByReferencia/' + encodeURIComponent(referencia),
      method: 'GET',
      dataType: 'json',
      success: function (returnedData) {
      let returned = returnedData;
      if (!returned || typeof returned !== 'object') {
        returned = { error: false, data: [] };
      }

      modelos = returned.data || [];

      // 1) Reemplazar options
      const $sel = $("#modelo");
      $sel.empty();

      modelos.forEach(model => {
        $sel.append(`<option value="${model.id_modelos_autos}">${model.nombre}</option>`);
      });

      // 2) Re-inicializar select2 (pero SIN perder selección al final)
      if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
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

      // 3) Volver a seleccionar modelos (LO MÁS IMPORTANTE)
      const ids = normMulti(modelosSeleccionar, 'id_modelos_autos', 'nombre').map(x => x.id);
      if (ids.length) {
        // asegurar options si vinieran ids que no están en la lista
        ids.forEach(id => ensureOption($sel, id, id));
        $sel.val(ids).trigger("change");
      }

      // 4) wireSelectEnsure como lo tenías
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

      resolve(modelos);
      },
      error: function () {
        $("#modelo").empty().trigger("change");
        resolve([]);
      }
    });

  });
}


/* ======= Asignar modelo (auto-relleno de combos) ======= */
function asignarModelo() {
  var id_modelo = $("#modelo").val();
  if (id_modelo && id_modelo !== "nuevo") {
    $.get('../api/v1/fulmuv/getModeloById/' + id_modelo, {}, function (returnedData) {
      var r = JSON.parse(returnedData);
      if (r.error || !r.data) return;
      $("#marca").val(r.data.id_marca).trigger("change");
      $("#tipo_vehiculo").val(r.data.id_tipo_auto).trigger("change");
      $("#traccion").val(r.data.id_tipo_traccion).trigger("change");
      $("#motor").val(r.data.id_funcionamiento_motor).trigger("change");
    });
  }
}

function verificarMembresiaYGuardar() {
  setBtnLoading("#btnGuardarProducto", true, "Registrando...");

  var id_empresa = ($("#id_rol_principal").val() == 1)
    ? $("#lista_empresas").val()
    : $("#id_empresa").val();

  $.get('../api/v1/fulmuv/validarMembresiaProductos/' + id_empresa + '/' + tipo_user, {
    modulo: 'producto'
  }, function (data) {
    var res = JSON.parse(data);

    if (res.error) {
      setBtnLoading("#btnGuardarProducto", false);

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
    } else {
      addProducto(); // aquí seguirá el loading
    }
  }).fail(function () {
    setBtnLoading("#btnGuardarProducto", false);
    SweetAlert("error", "Error de red validando membresía.");
  });
}


/* ======= Confirm genérico ======= */
function swalConfirmV1(title, text, okText, cancelText, onOk, onCancel) {
  swal({
    title, text, type: "info", showCancelButton: true,
    confirmButtonText: okText || "Sí",
    cancelButtonText: cancelText || "No",
    closeOnConfirm: true, closeOnCancel: true
  }, function (isConfirm) {
    if (isConfirm) { if (typeof onOk === 'function') onOk(); }
    else { if (typeof onCancel === 'function') onCancel(); }
  });
}

/* ======= Alta de catálogos en línea ======= */
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

    var parents = (cfg.parents && typeof cfg.parents === 'function') ? (cfg.parents() || {}) : {};

    if (cfg.entity === 'nombres_productos') {
      var hasCat = parents.id_categoria && +parents.id_categoria > 0;
      var hasSub = parents.id_sub_categoria && +parents.id_sub_categoria > 0;
      if (!hasCat && !hasSub) {
        swal("Falta seleccionar", "Selecciona al menos la Categoría o la Subcategoría para registrar el nombre del producto.", "warning");
        $el.val($el.data('prev') || null).trigger('change');
        return;
      }
    } else {
      for (var k in parents) {
        if (parents.hasOwnProperty(k) && (!parents[k] || +parents[k] <= 0)) {
          swal("Falta seleccionar", "Debes seleccionar primero el campo relacionado para registrar " + (cfg.label || cfg.entity).toLowerCase() + ".", "warning");
          $el.val($el.data('prev') || null).trigger('change');
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

function validarCamposObligatorios() {
  let ok = true;
  const errores = [];

  // 1) Inputs / selects requeridos (excepto textarea tinymce)
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
      valido = el.files && el.files.length > 0;
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

  // 2) TinyMCE (tu descripción obligatoria con *)
  // Si descripción tiene asterisco, la validamos aquí:
  if (document.querySelector('label[for="product-summary"]') || document.querySelector("#descripcion")) {
    const contenido = (window.tinymce?.get("descripcion")?.getContent({ format: "text" }) || "").trim();
    const textarea = document.getElementById("descripcion");

    if (textarea) {
      if (!contenido) {
        ok = false;
        errores.push("Falta: Descripción");
        // marca el contenedor visual
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


/* ======= Crear / Guardar ======= */
function addProducto() {

  var tags = tagsInput.getValue(true).map(tag => tag.toUpperCase());
  var descripcion = $("#descripcion").val();
  var codigo = $("#codigo").val();
  var tipo_vehiculo = $("#tipo_vehiculo").val();
  var modelo = $("#modelo").val();
  var marca = $("#marca").val();
  var traccion = $("#traccion").val();
  var categoria = $("#categoria").val();
  var sub_categoria = $("#sub_categoria").val();
  var descuento = $("#descuento").val();
  var peso = $("#peso").val();
  var titulo_producto = $("#titulo_producto").val();
  var marca_producto = $("#marca_producto").val();
  var iva = $("#iva").is(":checked") ? 1 : 0;
  var negociable = $("#negociable").is(":checked") ? 1 : 0;
  var referencia = $("#referencia").val();
  var funcionamiento_motor = $("#funcionamiento_motor").val();

  const precioGuardar = getPrecioParaGuardar();
  const ivaMarcado = $("#iva").is(":checked") ? 1 : 0;


  if (typeof tinymce !== 'undefined' && tinymce.get('descripcion')) {
    descripcion = tinymce.get('descripcion').getContent();
    descripcion = emojiToEntities(descripcion);
  }
  // if (descripcion == "" || codigo == "" || precio_referencia == "") {
  //   SweetAlert("error", "Todos los campos son obligatorios!!!");
  //   return;
  // }
  if (!validarCamposObligatorios()) {
    setBtnLoading("#btnGuardarProducto", false);
    return;
  }

  var dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");
  var files = dropzoneInstance.getAcceptedFiles();

  subirImagenesPrincipales()
    .then(imagenes => {
      return saveFiles(files).then(function (archivos) {
        return $.post('../api/v1/fulmuv/productos/create', {
          nombre: '',
          descripcion: descripcion,
          codigo: codigo,
          categoria: categoria,
          sub_categoria: sub_categoria,
          tags: tags.join(', '),
          precio_referencia: precioGuardar,
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
          peso: peso,
          titulo_producto: titulo_producto,
          marca_producto: marca_producto,
          iva: ivaMarcado,
          negociable: negociable,
          tipo_creador: tipo_user,
          referencias: referencia,
          funcionamiento_motor: funcionamiento_motor
        });
      });
    })
    .then(function (returnedData) {
      setBtnLoading("#btnGuardarProducto", false);
      var returned = (typeof returnedData === 'string') ? JSON.parse(returnedData) : returnedData;
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "crear_producto.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    })
    .catch(function () {
      setBtnLoading("#btnGuardarProducto", false);
    });
}

/* ======= Uploads ======= */
function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    if (!files.length) { resolve([]); return; }
    const formData = new FormData();
    files.forEach(function (file) { formData.append(`archivos[]`, file); });
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
          SweetAlert("error", "Ocurrió un error al guardar los archivos." + returnedImagen["error"]);
          reject(new Error(returnedImagen["error"] || "Error al guardar archivos"));
        }
      },
      error: function () {
        SweetAlert("error", "Error de red al guardar los archivos.");
        reject(new Error("Error de red al guardar archivos"));
      }
    });
  });
}

function subirImagenesPrincipales() {
  return new Promise(function (resolve, reject) {
    const imgFrontal = document.getElementById('img_frontal').files[0];
    const imgPosterior = document.getElementById('img_posterior').files[0];

    if (!imgFrontal || !imgPosterior) {
      SweetAlert("error", "Debes seleccionar la imagen frontal y la imagen posterior.");
      reject(new Error("Faltan imágenes principales"));
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
          SweetAlert("error", res.error || "Error al subir las imágenes principales.");
          reject(new Error(res.error || "Error al subir imágenes principales"));
        }
      },
      error: function () {
        SweetAlert("error", "Error de red al subir imágenes principales.");
        reject(new Error("Error de red al subir imágenes principales"));
      }
    });
  });
}

function eliminarArchivoGaleria(id_archivo_producto, file) {
  // SweetAlert2
  Swal.fire({
    title: '¿Eliminar archivo?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'No, cancelar'
  }).then((result) => {
    if (!result.isConfirmed) return;

    // 👇 Ajusta la URL de la API a la que tú tengas
    $.post(
      '../api/v1/fulmuv/archivos_productos/delete',
      { id_archivo_producto: id_archivo_producto },
      function (respRaw) {
        var resp = (typeof respRaw === 'string') ? JSON.parse(respRaw) : respRaw;

        if (!resp.error) {
          // quitar de Dropzone
          if (file && myDropzone) {
            myDropzone.removeFile(file);
          }
          Swal.fire('Eliminado', resp.msg || 'El archivo ha sido eliminado.', 'success');
        } else {
          Swal.fire('Error', resp.msg || 'No se pudo eliminar el archivo.', 'error');
        }
      }
    ).fail(function () {
      Swal.fire('Error', 'Error de comunicación con el servidor.', 'error');
    });
  });
}


function editProducto() {

  let atributos = [];

  var tags = tagsInput.getValue(true);
  tags = tags.map(tag => tag.toUpperCase());
  var texto = $("#nombre option:selected").text();

  let descripcion = $("#descripcion").val();
  if (typeof tinymce !== 'undefined' && tinymce.get('descripcion')) {
    descripcion = tinymce.get('descripcion').getContent();
    descripcion = emojiToEntities(descripcion);
  }

  var codigo = $("#codigo").val();
  var tipo_vehiculo = $("#tipo_vehiculo").val();
  var modelo = $("#modelo").val();
  var referencia = $("#referencia").val();   // multiple -> array o null
  var marca = $("#marca").val();
  var traccion = $("#traccion").val();
  var categoria = $("#categoria").val();
  var sub_categoria = $("#sub_categoria").val();
  var peso = $("#peso").val();
  const precio_referencia = getPrecioParaGuardar(); // ✅ base o total según checkbox
  var descuento = $("#descuento").val();
  var titulo_producto = $("#titulo_producto").val();
  var motor = $("#motor").val(); // 👈 si también quieres actualizar motor
  var funcionamiento_motor = $("#funcionamiento_motor").val();
  var marca_producto = $("#marca_producto").val();

  // Checkboxes => 1 / 0
  var iva = $('#iva').is(':checked') ? 1 : 0;
  var negociable = $('#negociable').is(':checked') ? 1 : 0;
  var emergencia_24_7 = $('#emergencia_24_7').is(':checked') ? 1 : 0;
  var emergencia_carretera = $('#emergencia_carretera').is(':checked') ? 1 : 0;
  var emergencia_domicilio = $('#emergencia_domicilio').is(':checked') ? 1 : 0;

  if (!validarCamposObligatorios()) {
    setBtnLoading("#btnGuardarProducto", false);
    return;
  } else {

    // if (!Array.isArray(referencia)) {
    //   referencia = [];
    // }

    var dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");

    subirImagenesPrincipalesEdit().then(imagenes => {
      saveFilesEdit(dropzoneInstance).then(function (archivosNuevos) {
        $.post('../api/v1/fulmuv/productos/edit', {
          nombre: '',
          descripcion: descripcion,
          codigo: codigo,
          categoria: categoria,
          sub_categoria: sub_categoria,
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
          motor: motor, // opcional
          peso: peso,
          titulo_producto: titulo_producto,
          marca_producto: marca_producto,
          iva: iva,
          negociable: negociable,
          emergencia_24_7: emergencia_24_7,
          emergencia_carretera: emergencia_carretera,
          emergencia_domicilio: emergencia_domicilio,
          referencias: referencia,
          funcionamiento_motor: funcionamiento_motor,
          tipo_creador: tipo_user,
          // 👇 banderas globales
          imagenFrontalEdit: imagenFrontalEdit,
          imagenPosteriorEdit: imagenPosteriorEdit,
          id_producto: id_producto
        }, function (returnedData) {
          setBtnLoading("#btnGuardarProducto", false);
          var returned = JSON.parse(returnedData)
          if (returned.error == false) {
            setBtnLoading("#btnGuardarProducto", false);
            SweetAlert("url_success", returned.msg, "productos.php")
          } else {
            setBtnLoading("#btnGuardarProducto", false);
            SweetAlert("error", returned.msg)
          }
        });

      });
    });
  }
}

function getPrecioParaGuardar() {
  const base = Number($("#precio_referencia").val() || 0);
  const ivaMarcado = $("#iva").is(":checked");
  if (!ivaMarcado) return base;

  const { total } = calcularPrecioConIVA(base);
  return total;
}

function calcularBaseDesdeTotalIVA(total) {
  const t = Number(total || 0);
  if (!t || t <= 0) return 0;
  const base = t / (1 + IVA_RATE);
  return +base.toFixed(2);
}


function subirImagenesPrincipalesEdit() {
  return new Promise(function (resolve, reject) {

    const imgFrontal = document.getElementById('img_frontal')?.files?.[0] || null;
    const imgPosterior = document.getElementById('img_posterior')?.files?.[0] || null;

    const imgFrontalActual = $('#img_frontal_actual').val() || '';
    const imgPosteriorActual = $('#img_posterior_actual').val() || '';

    // ✅ si no cambió nada, devuelve las anteriores
    if (!imgFrontal && !imgPosterior) {
      imagenFrontalEdit = 0;
      imagenPosteriorEdit = 0;
      return resolve({ img_frontal: imgFrontalActual, img_posterior: imgPosteriorActual });
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
        if (res.response !== "success") {
          SweetAlert("error", res.error || "Error al subir imágenes.");
          return reject();
        }

        const nuevaFrontal = res.data?.img_frontal || '';
        const nuevaPosterior = res.data?.img_posterior || '';

        imagenFrontalEdit = imgFrontal ? 1 : 0;
        imagenPosteriorEdit = imgPosterior ? 1 : 0;

        resolve({
          img_frontal: nuevaFrontal || imgFrontalActual,
          img_posterior: nuevaPosterior || imgPosteriorActual
        });
      },
      error: function () {
        SweetAlert("error", "Error de red al subir imágenes.");
        reject();
      }
    });

  });
}


function verificarMembresiaYEditar() {
  setBtnLoading("#btnGuardarProducto", true, "Actualizando...");

  var id_empresa = ($("#id_rol_principal").val() == 1) ? $("#lista_empresas").val() : $("#id_empresa").val();

  $.get('../api/v1/fulmuv/validarMembresiaProductos/' + id_empresa + '/' + tipo_user, {
    modulo: 'producto',
    id_registro: id_producto || 0
  }, function (data) {
    var res = JSON.parse(data);
    if (res.error) {
      setBtnLoading("#btnGuardarProducto", false);
    } else {
      editProducto();
    }
  }).fail(function () {
    setBtnLoading("#btnGuardarProducto", false);
    SweetAlert("error", "Error de red validando membresía.");
  });
}


function saveFilesEdit(dropzoneInstance) {
  return new Promise(function (resolve, reject) {

    // ✅ Solo archivos nuevos (no los mock existentes)
    const nuevos = (dropzoneInstance.files || []).filter(f => !f.existing);

    // si no hay nuevos -> no subimos nada
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
          resolve(returnedImagen["data"]); // ✅ solo nuevos
        } else {
          SweetAlert("error", "Ocurrió un error al guardar los archivos." + returnedImagen["error"]);
          reject();
        }
      },
      error: reject
    });
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

function waitTinyMCE(id, cb, tries = 30) {
  const ed = window.tinymce?.get(id);
  if (ed) return cb(ed);
  if (tries <= 0) return cb(null);
  setTimeout(() => waitTinyMCE(id, cb, tries - 1), 150);
}

function setTinyMCEWhenReady(id, html) {
  waitTinyMCE(id, (ed) => {
    if (ed) ed.setContent(html || '');
    else $("#" + id).val(html || '');
  });
}

function setBtnLoading(selector, loading, textLoading = "Procesando...") {
  const $btn = $(selector);
  if (!$btn.length) return;

  if (loading) {
    if (!$btn.data("old-html")) $btn.data("old-html", $btn.html());
    $btn.prop("disabled", true);

    $btn.html(`
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      ${textLoading}
    `);
  } else {
    $btn.prop("disabled", false);
    const old = $btn.data("old-html");
    if (old) $btn.html(old);
    $btn.removeData("old-html");
  }
}
