let categorias = [];
var tagsInput = '';
let modelos = [];
let tipos_auto = [];
let marcas = [];
let traccion = [];
let motor = [];

$(document).ready(function () {
  tagsInput = new Choices('#tags', {
    removeItemButton: true,
    placeholder: false,
    maxItemCount: 3,
    addItemText: (value) => {
      return `Presiona Enter para añadir <b>"${value}"</b>`;
    },
    maxItemText: (maxItemCount) => {
      return `Solo ${maxItemCount} tags pueden ser añadidos`;
    },
  });
  //traer nombres de productos
  $.get('../api/v1/fulmuv/nombres_productos/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      returned.data.forEach(nombres_productos => {
        $("#nombre").append(`
          <option value="${nombres_productos.id_nombre_producto}">${nombres_productos.nombre}</option>
        `);
      });
      $("#nombre").select2({
        theme: 'bootstrap-5',
        tags: true,
        placeholder: 'Seleccione producto',
        allowClear: true,
        createTag: function (params) {
          var term = $.trim(params.term).toUpperCase();
          if (term.length > 100) {
            term = term.substring(0, 100);
          }
          if (term === '') {
            return null;
          }
          return {
            id: 'nuevo',
            text: term,
            newTag: true
          };
        }
      });

      // $('#nombre').on('change', function () {
      //   console.log("eso")
      //   var idProducto = $(this).val();
        
      //   if (idProducto != '' && idProducto != null) {
      //     $('#cardDetallesProducto').removeClass('d-none');
      //     $.get('../api/v1/fulmuv/getNombreProductoById/'+idProducto, {}, function (returnedData) {
      //       var returned = JSON.parse(returnedData)
      //       if (returned.error == false) {
      //         $("#categoria").val(returned.data.categoria).trigger('change');
      //         renderizarAtributos(returned.data.atributos);
      //         $("#sub_categoria").val(returned.data.sub_categoria)
      //       }
      //     });
      //   }else{
      //     console.log("no hay");
      //     // Ocultar la tarjeta si no se ha seleccionado un producto
      //     $('#cardDetallesProducto').addClass('d-none');
      //     $('#contenedorAtributos').empty();
      //   }
      // });

    }
  });
  //traer categorias
  $.get('../api/v1/fulmuv/categorias/', {tipo: 'producto'}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      categorias = returned.data;
      returned.data.forEach(categoria => {
        $("#categoria").append(`
          <option value="${categoria.id_categoria}">${categoria.nombre}</option>
        `);
      });
      $("#categoria").append(`
        <option value="otra">Otra</option>
      `);
      $("#categoria").select2({
        theme: 'bootstrap-5',
      });
      llenarSubCategria();
    }
  });
  //traer modelos
  // $.get('../api/v1/fulmuv/modelosAutos/', {}, function (returnedData) {
  //   var returned = JSON.parse(returnedData)
  //   if (returned.error == false) {
  //     modelos = returned.data;
  //     returned.data.forEach(model => {
  //       $("#modelo").append(`
  //         <option value="${model.id_modelos_autos}">${model.nombre}</option>
  //       `);
  //     });
  //   }
  // });
  $.get('../api/v1/fulmuv/getReferencias/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      returned.data.forEach(referencias => {
        $("#referencia").append(`
          <option value="${referencias.referencia}">${referencias.referencia}</option>
        `);
      });
    }
  });
  $.get('../api/v1/fulmuv/tiposAuto/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      tipos_auto = returned.data;
      returned.data.forEach(tipo_vehi => {
        $("#tipo_vehiculo").append(`
          <option value="${tipo_vehi.id_tipo_auto}">${tipo_vehi.nombre}</option>
        `);
      });
    }
  });
  $.get('../api/v1/fulmuv/marcas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      marcas = returned.data;
      returned.data.forEach(marc => {
        $("#marca").append(`
          <option value="${marc.id_marca}">${marc.nombre}</option>
        `);
      });
    }
  });
  $.get('../api/v1/fulmuv/tipo_tracccion/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      traccion = returned.data;
      returned.data.forEach(tracc => {
        $("#traccion").append(`
          <option value="${tracc.id_tipo_traccion}">${tracc.nombre}</option>
        `);
      });
    }
  });
  $.get('../api/v1/fulmuv/getFuncionamientoMotor/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      motor = returned.data;
      returned.data.forEach(funcionamiento_motor => {
        $("#motor").append(`
          <option value="${funcionamiento_motor.id_funcionamiento_motor}">${funcionamiento_motor.nombre}</option>
        `);
      });
    }
  });
  $("#myAwesomeDropzone").attr("data-dropzone", 'data-dropzone');
  let myDropzone = new Dropzone("#myAwesomeDropzone", {
    url: "#",
    //maxFiles: 2, // Permitimos 2 archivos en total
    acceptedFiles: "image/*,application/pdf", // Aceptamos imágenes y PDFs
    previewsContainer: document.querySelector(".dz-preview"),
    previewTemplate: document.querySelector(".dz-preview").innerHTML,
    init: function () {
      $("#file-previews").empty()
      this.on("addedfile", function (file) {
        // Filtrar para mantener solo 1 imagen y 1 PDF
        let imageFileCount = 0;
        let pdfFileCount = 0;
        this.files.forEach(function (f) {
          if (f.type.startsWith("image/")) {
            imageFileCount++;
          } else if (f.type === "application/pdf") {
            pdfFileCount++;
          }
        });
        if (pdfFileCount > 1) {
          this.removeFile(file);
          toastr.options.timeOut = 1500; // Configuración temporal para esta notificación
          toastr.warning("Solo se permite un archivo PDF!");
        }
      });
    }
  });
  if ($("#id_rol_principal").val() == 1) {
    $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(empresa => {
          $("#lista_empresas").append(`
            <option value="${empresa.id_empresa}">${empresa.nombre}</option>
          `);
        });
      }
    });
  }else{
    $("#searh_empresa").empty()
  }
});

$("#nombre").on('change', function (e) {
  var idProducto = $(this).val();
  if (idProducto != '' && idProducto != null && idProducto != 'nuevo') {
    $.get('../api/v1/fulmuv/getNombreProductoById/' + idProducto, {}, function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        $("#categoria").val(returned.data.categoria).trigger('change');
        //renderizarAtributos(returned.data.atributos);
        $("#sub_categoria").val(returned.data.sub_categoria);
      }
    });
  } else {
    // $('#cardDetallesProducto').addClass('d-none');
    // $('#contenedorAtributos').empty();
    $("#categoria").val("").trigger('change');
    $("#sub_categoria").val("").trigger('change');
  }
});

function llenarSubCategria() {
  if($("#categoria").val() != "" && $("#categoria").val() != null){
    var categoria = parseInt($("#categoria").val());
    const arrayCategoria = categorias.find((cat) => cat.id_categoria === categoria);
    $("#sub_categoria").text("")
    $("#sub_categoria").append(`<option value="">Elija una subcategoría</option>`)
    arrayCategoria.sub_categorias.forEach(sub_categoria => {
      $("#sub_categoria").append(`
        <option value="${sub_categoria.id_sub_categoria}">${sub_categoria.nombre}</option>
      `);
    });

    //llenar atributos completos por categorias
    $.get('../api/v1/fulmuv/atributosCategoriaCompleto/' + categoria, {}, function (returnedData) {
      var returned = JSON.parse(returnedData);
      // $('#cardDetallesProducto').removeClass('d-none');
      // renderizarAtributos(returned.data);
    }); 
  }
}

function buscarModelosReferencia(){
  var referencia = $("#referencia").val();
  $.get('../api/v1/fulmuv/getModelosByReferencia/' + referencia, {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    modelos = returned.data;
    $("#modelo").text("");
    $("#modelo").append(`
      <option value="">Seleccione modelo</option>
    `);
    returned.data.forEach(model => {
      $("#modelo").append(`
        <option value="${model.id_modelos_autos}">${model.nombre}</option>
      `);
    });
  }); 
}

function asignarModelo(){
  var id_modelo = $("#modelo").val();
  $.get('../api/v1/fulmuv/getModeloById/' + id_modelo, {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    $("#marca").val(returned.data.id_marca);
    $("#tipo_vehiculo").val(returned.data.id_tipo_auto);
    $("#traccion").val(returned.data.id_tipo_traccion);
    $("#motor").val(returned.data.id_funcionamiento_motor);
  }); 
}

function addProducto() {

  let atributos = [];

  /*$('#contenedorAtributos')
  .find('input[name^="atributo_"], select[name^="atributo_"]')
  .each(function () {
    const nombre = $(this).attr('name'); // ejemplo: "atributo_23"
    const idAtributo = nombre.split('_')[1]; // obtiene "23"
    const valor = $(this).val(); // valor ingresado o seleccionado

    // Para obtener el label (texto del label)
    const label = $(this).closest('.mb-3').find('label').text();

    atributos.push({
      id: idAtributo,
      label: label,
      valor: valor
    });
  });*/

  /*$('#contenedorAtributos')
  .find('input[name^="atributo_"], select[name^="atributo_"]')
  .each(function () {
    const nombre = $(this).attr('name'); // ejemplo: "atributo_23" o "atributo_custom_12345"
    let idAtributo = nombre.replace('atributo_', '');

    if (!idAtributo.startsWith('custom_')) {
      idAtributo = parseInt(idAtributo);
    }

    const valor = $(this).val();
    const label = $(this).closest('.mb-3').find('label').clone().children().remove().end().text().trim();

    atributos.push({
      id: idAtributo,
      label: label,
      valor: valor
    });
  });*/

  var tags = tagsInput.getValue(true);
  tags = tags.map(tag => tag.toUpperCase());
  var nombre = $("#nombre").val();
  var texto = $("#nombre option:selected").text();
  var descripcion = $("#descripcion").val();
  var codigo = $("#codigo").val();
  var tipo_vehiculo = $("#tipo_vehiculo").val();
  var modelo = $("#modelo").val();
  var marca = $("#marca").val();
  var traccion = $("#traccion").val();
  var categoria = $("#categoria").val();
  var sub_categoria = $("#sub_categoria").val();
  var precio_referencia = $("#precio_referencia").val();
  var descuento = $("#descuento").val();
  var peso = $("#peso").val();
  var id_empresa;
  if ($("#id_rol_principal").val() == 1) {
    id_empresa = $("#lista_empresas").val()
  }else{
    id_empresa = $("#id_empresa").val()
  }
  if (nombre == "" || descripcion == "" || codigo == "" || precio_referencia == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    // Obtener la instancia de Dropzone asociada al formulario
    var dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");

    // Obtener los archivos seleccionados
    var files = dropzoneInstance.getAcceptedFiles();
    subirImagenesPrincipales().then(imagenes => {
      saveFiles(files).then(function (archivos) {
        $.post('../api/v1/fulmuv/productos/create', {
          nombre: texto,
          descripcion: descripcion,
          codigo: codigo,
          categoria: categoria,
          sub_categoria: sub_categoria,
          tags: tags.join(', '),
          precio_referencia: precio_referencia,
          img_frontal: imagenes.img_frontal,
          img_posterior: imagenes.img_posterior,
          archivos: archivos,
          atributos: [],
          id_empresa: id_empresa,
          descuento, descuento,
          tipo_vehiculo: tipo_vehiculo,
          modelo: modelo,
          marca: marca,
          traccion: traccion,
          peso: peso
        }, function (returnedData) {
          var returned = JSON.parse(returnedData)
          if (returned.error == false) {
            SweetAlert("url_success", returned.msg, "crear_producto.php")
          } else {
            SweetAlert("error", returned.msg)
          }
        });

      });
    });
  }
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    if (!files.length) {
      resolve(); // Resuelve la promesa incluso si no hay imágenes
    } else {
      const formData = new FormData();
      files.forEach(function (file) {
        formData.append(`archivos[]`, file); // añadrir los archivos al form
      });
      $.ajax({
        type: 'POST',
        data: formData,
        url: 'cargar_imagen_multiple.php',
        cache: false,
        contentType: false,
        processData: false,
        success: function (returnedImagen) {
          if (returnedImagen["response"] == "success") {
            resolve(returnedImagen["data"]); // Resuelve la promesa cuando la llamada AJAX se completa con éxito
          } else {
            SweetAlert("error", "Ocurrió un error al guardar los archivos." + returnedImagen["error"]);
            reject(); // Rechaza la promesa en caso de error
          }
        }
      });
    }
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
      url: 'cargar_imagenes_frontales.php',
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
          reject();
        }
      },
      error: function () {
        SweetAlert("error", "Error de red al subir imágenes principales.");
        reject();
      }
    });
  });
}

function addCategoria() {
  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg mt-6" role="document">
        <div class="modal-content border-0">
          <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
              <h4 class="mb-1" id="staticBackdropLabel">Crear categoría</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                  <input class="form-control" id="nombre_categoria" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()" />
                </div>
                <div class="col-12">
                  <button onclick="saveCategoria()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
  $("#btnModal").click();
}

function saveCategoria() {
  var nombre = $("#nombre_categoria").val();
  if (nombre == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/categorias/create', {
      nombre: nombre,
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "crear_producto.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function addSubCategoria() {
  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg mt-6" role="document">
        <div class="modal-content border-0">
          <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
              <h4 class="mb-1" id="staticBackdropLabel">Crear sub-categoría</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                  <input class="form-control" id="nombre_sub_categoria" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()" />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Categoría</label>
                  <select class="form-select" id="tipo_categoria">
                        
                  </select>
                </div>
                <div class="col-12">
                  <button onclick="saveSubCategoria()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
  categorias.forEach(categoria => {
    $("#tipo_categoria").append(`
      <option value="${categoria.id_categoria}">${categoria.nombre}</option>
    `);
  });
  $("#btnModal").click();
}

function saveSubCategoria() {
  var nombre = $("#nombre_sub_categoria").val();
  var id_categoria = $("#tipo_categoria").val();
  if (nombre == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/sub_categorias/create', {
      nombre: nombre,
      id_categoria: id_categoria
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "crear_producto.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

/*function renderizarAtributos(atributos) {
  $('#contenedorAtributos').empty(); // Limpiar el contenedor
  var id_select_modelo;
  atributos.forEach(attr => {
    let html = `<div class="col-md-6 mb-3">
                  <label class="form-label">${attr.nombre}</label>`;

    switch (attr.tipo_dato) {
      case 'TEXTO':
        html += `<input type="text" name="atributo_${attr.id_atributo}" class="form-control">`;
        break;

      case 'NUMERO':
        html += `<input type="number" name="atributo_${attr.id_atributo}" class="form-control">`;
        break;

      case 'BOOLEANO':
        html += `
          <select name="atributo_${attr.id_atributo}" class="form-control">
            <option value="1">Sí</option>
            <option value="0">No</option>
          </select>`;
        break;

      case 'OPCIONES':
        html += `<select name="atributo_${attr.id_atributo}" id="atributo_${attr.id_atributo}" class="form-select selectpicker">`;

        if(attr.nombre == "Vehículo Compatible con el Producto"){
          modelos.forEach(opt => {
            html += `<option value="${opt.id_modelos_autos}">${opt.nombre}</option>`;
          });
          id_select_modelo = "atributo_"+attr.id_atributo;
        }else if (Array.isArray(attr.opciones) && attr.opciones.length > 0) {
          attr.opciones.forEach(opt => {
            html += `<option value="${opt}">${opt}</option>`;
          });
        } else {
          html += `<option disabled>No hay opciones disponibles</option>`;
        }

        html += `</select>`;
        break;
    }

    html += `</div>`;
    $('#contenedorAtributos').append(html);
    $(`#${id_select_modelo}`).select2();
    select2Init()
  });
}*/

/*function renderizarAtributos(atributos) {
  $('#contenedorAtributos').empty(); // Limpiar el contenedor

  let selectsToInit = []; // para almacenar IDs que necesitan select2

  atributos.forEach(attr => {
    let html = `<div class="col-md-6 mb-3">
                  <label class="form-label">${attr.nombre}</label>`;

    const nombreCampo = `atributo_${attr.id_atributo}`;
    const isNombreProducto = attr.nombre === "Nombre del Producto";

    switch (attr.tipo_dato) {
      case 'TEXTO':
        html += `<input type="text" name="${nombreCampo}" id="${isNombreProducto ? 'input_nombre_producto' : nombreCampo}" class="form-control">`;
        break;

      case 'NUMERO':
        html += `<input type="number" name="${nombreCampo}" class="form-control">`;
        break;

      case 'BOOLEANO':
        html += `
          <select name="${nombreCampo}" class="form-select">
            <option value="1">Sí</option>
            <option value="0">No</option>
          </select>`;
        break;

      case 'OPCIONES':
        html += `<select name="${nombreCampo}" id="${nombreCampo}" class="form-select selectpicker">`;

        if (attr.nombre === "Vehículo Compatible con el Producto") {
          modelos.forEach(opt => {
            html += `<option value="${opt.id_modelos_autos}">${opt.nombre}</option>`;
          });
        } else if (Array.isArray(attr.opciones) && attr.opciones.length > 0) {
          attr.opciones.forEach(opt => {
            html += `<option value="${opt}">${opt}</option>`;
          });
        } else {
          html += `<option disabled>No hay opciones disponibles</option>`;
        }

        html += `</select>`;
        selectsToInit.push(nombreCampo); // almacenar ID para aplicar select2 luego
        break;
    }

    html += `</div>`;
    $('#contenedorAtributos').append(html);
  });

  // ✅ Inicializar select2 después de renderizar
  selectsToInit.forEach(id => {
    $(`#${id}`).select2({ width: '100%' });
  });

  if (typeof select2Init === 'function') {
    select2Init(); // solo una vez, si existe la función
  }

  // ✅ Asignar automáticamente el nombre del producto seleccionado
  const nombreSeleccionado = $('#nombre option:selected').text(); // o con Choices.js: choicesInstance.getValue()[0]?.label;
  $('#input_nombre_producto').val(nombreSeleccionado);
}*/

function renderizarAtributos(atributos) {
  $('#contenedorAtributos').empty(); // Limpiar el contenedor

  let selectsToInit = []; // para almacenar IDs que necesitan select2

  // Lista de atributos que NO queremos renderizar
  const atributosNoRenderizar = [
    "Precio",
    "Nombre del Producto",
    "Descripción Detallada del Producto",
    "SKU o Código del Producto",
    "Modelo del Producto",
    "Marca del Producto",
    "Vehículo Compatible con el Producto",
    "Categoría del Producto y Subcategorías"
  ];

  atributos.forEach(attr => {
    // 🔥 Saltar si el nombre del atributo está en la lista de no renderizar
    if (atributosNoRenderizar.includes(attr.nombre)) {
      return; // continúa al siguiente atributo
    }

    let html = `<div class="col-md-6 mb-3 atributo-dinamico">
                  <label class="form-label d-flex justify-content-between">
                    ${attr.nombre}
                    <button type="button" class="btn btn-sm btn-danger eliminar-atributo" title="Eliminar">
                      &times;
                    </button>
                  </label>`;

    const nombreCampo = `atributo_${attr.id_atributo}`;

    switch (attr.tipo_dato) {
      case 'TEXTO':
        html += `<input type="text" name="${nombreCampo}" class="form-control">`;
        break;

      case 'NUMERO':
        html += `<input type="number" name="${nombreCampo}" class="form-control">`;
        break;

      case 'BOOLEANO':
        html += `
          <select name="${nombreCampo}" class="form-select">
            <option value="1">Sí</option>
            <option value="0">No</option>
          </select>`;
        break;

      case 'OPCIONES':
        html += `<select name="${nombreCampo}" id="${nombreCampo}" class="form-select selectpicker">`;

        if (attr.nombre === "Vehículo Compatible con el Producto") {
          tipos_auto.forEach(opt => {
            html += `<option value="${opt.id_tipo_auto}">${opt.nombre}</option>`;
          });
        } else if (Array.isArray(attr.opciones) && attr.opciones.length > 0) {
          attr.opciones.forEach(opt => {
            html += `<option value="${opt}">${opt}</option>`;
          });
        } else {
          html += `<option disabled>No hay opciones disponibles</option>`;
        }

        html += `</select>`;
        selectsToInit.push(nombreCampo); // almacenar ID para aplicar select2 luego
        break;
    }

    html += `</div>`;
    $('#contenedorAtributos').append(html);
  });

  // ✅ Inicializar select2 después de renderizar
  selectsToInit.forEach(id => {
    $(`#${id}`).select2({ width: '100%' });
  });

  if (typeof select2Init === 'function') {
    select2Init(); // solo una vez, si existe la función
  }
}

$('#contenedorAtributos').on('click', '.eliminar-atributo', function () {
  $(this).closest('.atributo-dinamico').remove();
});

$('#agregarCampo').on('click', function () {
  const campoID = `atributo_custom_${Date.now()}`;

  const nuevoCampo = `
    <div class="col-md-6 mb-3 atributo-dinamico">
      <label class="form-label d-flex justify-content-between">
        Atributo Personalizado
        <button type="button" class="btn btn-sm btn-danger eliminar-atributo" title="Eliminar">
          &times;
        </button>
      </label>
      <input type="text" name="${campoID}" class="form-control">
    </div>
  `;

  $('#contenedorAtributos').append(nuevoCampo);
});