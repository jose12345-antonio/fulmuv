let categorias = [];
var tagsInput = '';
let tipo_user = $("#tipo_user").val();
let timerBusqueda = null;

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

$(document).ready(function () {
  if ($("#id_rol_principal").val() == 1) {
    $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(empresa => {
          $("#lista_empresas").append(`
            <option value="${empresa.id_empresa}">${empresa.nombre}</option>
          `);
        });
        $("#lista_empresas").trigger('change');
      }
    });
  } else {
    $("#searh_empresa").empty()
    getVehiculos($("#id_empresa").val())
  }
});

$("#lista_empresas").on('change', function () {
  getVehiculos($(this).val());
});

function filtrarVehiculosLive(texto){
  clearTimeout(timerBusqueda);
  timerBusqueda = setTimeout(() => {
    //filtrarProductos(texto);
    getVehiculosFiltro($("#id_empresa").val(),texto)
  }, 300); // 300ms después de dejar de escribir
}

function getVehiculos(id_empresa) {
  $.get('../api/v1/fulmuv/vehiculos/all/' + id_empresa, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_vehiculos").text("");
      returned.data.forEach(vehiculo => {

        // Buscar el primer archivo tipo 'imagen'
        //const archivoImagen = producto.archivos?.find(archivo => archivo.tipo === 'imagen');
        //<!--img class="rounded-1 border border-200" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" width="60" height="60" alt="" /-->
        $("#lista_vehiculos").append(`
          <div class="mb-4 col-md-6 col-lg-3">
            <div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
              <div class="overflow-hidden">
                  <div class="position-relative rounded-top overflow-hidden">
                    <a class="d-block">
                      <img class="product-img-wrap rounded-top" src="${vehiculo.img_frontal ? "../admin/" + vehiculo.img_frontal : "files/producto_no_found.jpg"}" alt="" />
                    </a>
                  </div>
                  <div class="p-3">
                      <h5 class="fs-9"><a class="text-1100" href="crear_vehiculo.php?id_vehiculo=${vehiculo.id_vehiculo}">${vehiculo.modelo}</a></h5>
                      <p class="fs-10 mb-0"><a class="text-500" href="#!">${vehiculo.provincia}</a></p>
                      <p class="fs-10 mb-3"><a class="text-500" href="#!">${vehiculo.canton}</a></p>
                      <h5 class="fs-md-7 text-warning mb-0 d-flex align-items-center mb-3"> $${vehiculo.precio_referencia}
                          <del class="ms-2 fs-10 text-500">$${vehiculo.precio_referencia} </del>
                      </h5>
                      </p>
                  </div>
              </div>
              <div class="d-flex flex-end-center px-3">
                  <div class="text-center">
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2" href="crear_vehiculo.php?id_vehiculo=${vehiculo.id_vehiculo}" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar">
                      <span class="fas fa-edit"></span>
                    </a>
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2 text-danger" onclick="remove(${vehiculo.id_vehiculo}, 'vehiculos')" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
                      <span class="fas fa-trash"></span>
                    </a>
                  </div>
              </div>
            </div>
          </div>
        `);
      });
    }
  });
}

function getVehiculosFiltro(id_empresa,consulta) {
  if(consulta == ""){
    consulta = "0"
  }
  $.get('../api/v1/fulmuv/vehiculos/allFiltro/' + id_empresa + '/' + consulta, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_vehiculos").text("");
      returned.data.forEach(vehiculo => {

        // Buscar el primer archivo tipo 'imagen'
        //const archivoImagen = producto.archivos?.find(archivo => archivo.tipo === 'imagen');
        //<!--img class="rounded-1 border border-200" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" width="60" height="60" alt="" /-->
        $("#lista_vehiculos").append(`
          <div class="mb-4 col-md-6 col-lg-3">
            <div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
              <div class="overflow-hidden">
                  <div class="position-relative rounded-top overflow-hidden">
                    <a class="d-block" href="../../../app/e-commerce/product/product-details.html">
                      <img class="product-img-wrap rounded-top" src="${vehiculo.img_frontal ? "../admin/" + vehiculo.img_frontal : "files/producto_no_found.jpg"}" alt="" />
                    </a>
                  </div>
                  <div class="p-3">
                      <h5 class="fs-9"><a class="text-1100" onclick="editProducto(${vehiculo.id_vehiculo})">${vehiculo.modelo}</a></h5>
                      <p class="fs-10 mb-0"><a class="text-500" href="#!">${vehiculo.provincia}</a></p>
                      <p class="fs-10 mb-3"><a class="text-500" href="#!">${vehiculo.canton}</a></p>
                      <h5 class="fs-md-7 text-warning mb-0 d-flex align-items-center mb-3"> $${vehiculo.precio_referencia}
                          <del class="ms-2 fs-10 text-500">$${vehiculo.precio_referencia} </del>
                      </h5>
                      </p>
                  </div>
              </div>
              <div class="d-flex flex-end-center px-3">
                  <div class="text-center">
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2" onclick="editVehiculo(${vehiculo.id_vehiculo})" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar">
                      <span class="fas fa-edit"></span>
                    </a>
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2 text-danger" onclick="remove(${vehiculo.id_vehiculo}, 'vehiculos')" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
                      <span class="fas fa-trash"></span>
                    </a>
                  </div>
              </div>
            </div>
          </div>
        `);
      });
    }
  });
}

function firstFromJson(val) {
  if (Array.isArray(val)) return val[0] ?? null;
  if (typeof val === 'string') {
    try {
      const arr = JSON.parse(val || '[]');
      return arr[0] ?? null;
    } catch { return null; }
  }
  return null;
}


function editVehiculo(id_vehiculo) {
  $.get('../api/v1/fulmuv/vehiculos/' + id_vehiculo, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      const prod = returned.data;

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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar vehículo</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-6 mb-2">
                      <label class="form-label" for="modelo">Modelo <span class="text-danger">*</span></label></label>
                      <select class="form-select" id="modelo" name="modelo"></select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="marca">Marca <span class="text-danger">*</span></label></label>
                      <select class="form-select" id="marca" name="marca"></select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="traccion">Tracción <span class="text-danger">*</span></label></label>
                      <select class="form-select" id="traccion" name="traccion"></select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="subtipo">Subtipo <span class="text-danger">*</span></label></label>
                      <select class="form-select" id="subtipo" name="subtipo"></select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="funcionamiento_motor">Funcionamiento de motor <span class="text-danger">*</span></label></label>
                      <select class="form-select" id="funcionamiento_motor" name="funcionamiento_motor"></select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="provincia">Provincia <span class="text-danger">*</span></label></label>
                      <select class="form-control" id="provincia" onchange="cargarCantones(this.value)">
                        <option value="">Seleccione provincia</option>
                        <option value="Azuay">Azuay</option>
                        <option value="Bolívar">Bolívar</option>
                        <option value="Cañar">Cañar</option>
                        <option value="Carchi">Carchi</option>
                        <option value="Cotopaxi">Cotopaxi</option>
                        <option value="Chimborazo">Chimborazo</option>
                        <option value="El Oro">El Oro</option>
                        <option value="Esmeraldas">Esmeraldas</option>
                        <option value="Guayas">Guayas</option>
                        <option value="Imbabura">Imbabura</option>
                        <option value="Loja">Loja</option>
                        <option value="Los Ríos">Los Ríos</option>
                        <option value="Manabí">Manabí</option>
                        <option value="Morona Santiago">Morona Santiago</option>
                        <option value="Napo">Napo</option>
                        <option value="Pastaza">Pastaza</option>
                        <option value="Pichincha">Pichincha</option>
                        <option value="Tungurahua">Tungurahua</option>
                        <option value="Zamora Chinchipe">Zamora Chinchipe</option>
                        <option value="Galápagos">Galápagos</option>
                        <option value="Sucumbíos">Sucumbíos</option>
                        <option value="Orellana">Orellana</option>
                        <option value="Santo Domingo de los Tsáchilas">Santo Domingo de los Tsáchilas</option>
                        <option value="Santa Elena">Santa Elena</option>
                      </select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="canton">Cantón <span class="text-danger">*</span></label></label>
                      <select class="form-select" id="canton" name="canton"></select>
                    </div>
                    <div class="col-12 mb-2">
                      <label class="form-label" for="descripcion">Descripción <span class="text-danger">*</span></label> </label>
                      <textarea class="form-control" id="descripcion">${prod.descripcion || ''}</textarea>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="precio_referencia">Precio base <span class="text-danger">*</span></label></label>
                      <input class="form-control" id="precio_referencia" type="number" min="1" value="${prod.precio_referencia || ''}" />
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="descuento">Descuento % <span class="text-danger">*</span></label></label>
                      <input class="form-control" id="descuento" type="number" min="0" value="${prod.descuento || 0}" />
                    </div>
                    <div class="col-12 mb-2">
                      <label class="form-label" for="tags">Tags <span class="text-danger">*</span></label></label>
                      <input class="form-control" id="tags" type="text" name="tags" required="required" size="1" data-options='{"removeItemButton":true,"placeholder":false}' />
                    </div>

                    <div class="col-12 mb-2">
                      <h6>Archivos (Imagen y Ficha Técnica) (Opcional)</h6>
                      <form class="dropzone dropzone-multiple p-0" id="myAwesomeDropzone" action="../admin/cargar_imagen_drop.php" data-dropzone="data-dropzone">
                        <div class="fallback">
                          <input name="archivos[]" type="file" multiple />
                        </div>
                        <div class="dz-message my-0" data-dz-message="data-dz-message">
                          <img class="me-2" src="../theme/public/assets/img/icons/cloud-upload.svg" width="25" alt="" />
                          <span class="d-none d-lg-inline">Drag your image here<br />or, </span>
                          <span class="btn btn-link p-0 fs-10">Browse</span>
                        </div>
                        <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column" id="file-previews"></div>
                      </form>
                    </div>
                    <div id="uploadPreviewTemplate" style="display:none;">
                      <div class="d-flex media align-items-center mb-3 pb-3 border-bottom btn-reveal-trigger dz-file-preview">
                        <div class="avatar avatar-2xl me-2">
                          <img class="rounded-soft border" src="../theme/public/assets/img/generic/image-file-2.png" alt="" data-dz-thumbnail />
                        </div>
                        <div class="flex-1 d-flex flex-between-center">
                          <div>
                            <a target="_blank" class="h6" data-dz-name></a>
                            <div class="d-flex align-items-center">
                              <p class="mb-0 fs-10 text-400 lh-1" data-dz-size></p>
                            </div>
                          </div>
                          <div class="dropdown font-sans-serif file_buttons"></div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-iso" type="button" onclick="updateVehiculo(${prod.id_vehiculo})">Actualizar</button>
              </div>
            </div>
          </div>
        </div>
      `);

      // Abrir modal
      $("#btnModal").click();

      // --------- PROVINCIA / CANTÓN ----------
      const prov = firstFromJson(prod.provincia) || prod.provincia || '';
      const cant = firstFromJson(prod.canton) || prod.canton || '';

      $("#provincia").val(prov).trigger("change");
      $("#canton").val(cant);

      // --------- TAGS ----------
      tagsInput = new Choices('#tags', {
        removeItemButton: true,
        placeholder: false,
        items: (prod.tags && prod.tags.length) ? prod.tags.split(",") : [],
        maxItemCount: 10,
        addItemText: value => `Presiona Enter para añadir <b>"${value}"</b>`,
        maxItemText: maxItemCount => `Solo ${maxItemCount} tags pueden ser añadidos`,
      });

      // --------- TINYMCE (corrige el selector) ----------
      tinymce.init({
        selector: '#descripcion',
        menubar: false,
        height: 200
      });

      // --------- CARGAR SELECTS (modelo, marca, tracción, subtipo, motor) ----------
      const idModelo = firstFromJson(prod.id_modelo) || prod.id_modelo || '';
      const idMarca = firstFromJson(prod.id_marca) || prod.id_marca || '';
      const idTracc = firstFromJson(prod.tipo_traccion) || prod.tipo_traccion || '';
      const idSubtipo = firstFromJson(prod.tipo_auto) || prod.tipo_auto || '';
      const idMotor = firstFromJson(prod.funcionamiento_motor) || prod.funcionamiento_motor || '';

      cargarModelos(idModelo);
      cargarMarcas(idMarca);
      cargarTraccion(idTracc);
      cargarSubtipos(idSubtipo);
      cargarFuncionamientoMotor(idMotor);

      // --------- ARCHIVOS / DROPZONE ----------
      loadFilesToDropzone(prod.id_vehiculo, prod.archivos || []);
    }
  });
}


function updateVehiculo(id_vehiculo) {
  var descripcion = $("#descripcion").val();
  var provincia = $("#provincia").val();
  var canton = $("#canton").val();
  var tags = tagsInput.getValue(true);
  tags = tags.map(tag => tag.toUpperCase());
  var precio_referencia = $("#precio_referencia").val();
  var descuento = $("#descuento").val();


  if (descripcion == "" || precio_referencia == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    const inputs = document.querySelectorAll('input[name="detalle_valor[]"]');
    let detalleActualizado = [];

    inputs.forEach(input => {
      detalleActualizado.push({
        id: input.dataset.id,
        label: input.previousElementSibling.textContent,
        valor: input.value
      });
    });
    // var dropzoneInstance = Dropzone.forElement("#myAwesomeDropzone");
    // var files = dropzoneInstance.getAcceptedFiles();
    // var filePromise = files.length === 0 ? Promise.resolve({ "img": prod.img_path, "pdf": prod.ficha_tecnica }) : saveFiles(files);

    // filePromise.then(function (file) {
    $.post('../api/v1/fulmuv/vehiculos/update', {
      id_vehiculo: id_vehiculo,
      descripcion: descripcion,
      tags: tags.join(', '),
      precio_referencia: precio_referencia,
      descuento: descuento,
      provincia: provincia,
      canton: canton
      // img_path: file.img ? file.img : prod.img_path,
      // ficha_tecnica: file.pdf ? file.pdf : prod.ficha_tecnica
    }, function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (!returned.error) {
        SweetAlert("url_success", returned.msg, "vehiculos.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    });
    // });
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
        url: '../admin/cargar_imagen.php',
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

function remove(id, tabla) {
  swal({
    title: "Alerta",
    text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    $.post('../api/v1/fulmuv/' + tabla + '/delete', {
      id: id
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "vehiculos.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}

function loadFilesToDropzone(id_producto, files) {
  // Destruir instancias previas
  if (Dropzone.instances.length > 0) {
    Dropzone.instances.forEach(dz => dz.destroy());
    $("#file-previews").empty();
  }

  let myDropzone = new Dropzone("#myAwesomeDropzone", {
    url: "../admin/cargar_imagen_drop.php",
    // paramName: "archivos",
    previewsContainer: "#file-previews",
    previewTemplate: document.querySelector("#uploadPreviewTemplate").innerHTML,
    autoProcessQueue: true,
    addRemoveLinks: false,
    init: function () {
      const dropzoneInstance = this;

      files.forEach(file => {
        let fileName = file.archivo.split("/").pop();
        let fileExtension = file.archivo.split('.').pop().toLowerCase();

        let mockFile = {
          name: fileName,
          size: 123456, // Tamaño ficticio
          accepted: true,
          existing: true
        };

        dropzoneInstance.emit("addedfile", mockFile);

        // Icono según tipo
        if (["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(fileExtension)) {
          dropzoneInstance.emit("thumbnail", mockFile, file.archivo);
        } else {
          dropzoneInstance.emit("thumbnail", mockFile, "../img/pdf.png");
        }

        // Asignar enlaces de descarga
        let nameLink = mockFile.previewElement.querySelector("a[data-dz-name]");
        nameLink.href = "../admin/"+file.archivo;
        nameLink.textContent = fileName;
        nameLink.target = "_blank";

        // Agregar input oculto con ID del archivo
        let inputHidden = document.createElement("input");
        inputHidden.setAttribute("type", "hidden");
        inputHidden.setAttribute("data-dz-id", "");
        inputHidden.value = file.id_archivo_producto;
        mockFile.previewElement.querySelector(".file_buttons").appendChild(inputHidden);

        // Botón personalizado para eliminar
        let removeBtn = Dropzone.createElement(
          "<a href='' class='btn btn-link btn-lg text-danger' data-dz-remove><i class='far fa-trash-alt'></i></a>"
        );
        removeBtn.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          $.post('../api/v1/fulmuv/deleteFileVehiculo', {
            id_archivo: file.id_archivo_producto,
          }, function (resp) {
            if (!resp.error) {
              toastr.success("Archivo eliminado");
              dropzoneInstance.removeFile(mockFile);
            } else {
              SweetAlert("error", resp.msg || "No se pudo eliminar");
            }
          });
        });

        mockFile.previewElement.querySelector(".file_buttons").appendChild(removeBtn);
      });

      this.on("success", function (file, response) {
        console.log("File uploaded successfully:", response);
        // Manejar la respuesta del servidor aquí
        if (response.error) {
          SweetAlert("error", response.error);
          this.removeFile(file);
        } else {
          // Manejar éxito
          toastr.success("File uploaded");
          // Agregar URL de descarga al nombre del archivo
          var nameLink = file.previewElement.querySelector("a[data-dz-name]");
          nameLink.href = response.data[0].archivo;
          nameLink.target = "_blank";

          // Guardar en base de datos
          $.post('../api/v1/fulmuv/createFileProducto', {
            id_producto: id_producto,
            archivo: response.data[0].archivo,
            tipo: response.data[0].tipo,
          }, function (returnedData) {
            var returned = JSON.parse(returnedData)
            if (returned["error"] == false) {
              toastr.success("Archivo guardado");
              // Crear botón eliminar (como en archivos precargados)
              let removeBtn = Dropzone.createElement(
                "<a href='' class='btn btn-link btn-lg text-danger' data-dz-remove><i class='far fa-trash-alt'></i></a>"
              );
              removeBtn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                $.post('../api/v1/fulmuv/deleteFileProducto', {
                  id_archivo: returned.id_archivo,
                }, function (resp) {
                  if (!resp.error) {
                    toastr.success("Archivo eliminado");
                    dropzoneInstance.removeFile(file);
                  } else {
                    SweetAlert("error", resp.msg || "No se pudo eliminar");
                  }
                });
              });
              // Agregar input oculto con el ID del archivo creado
              let inputHidden = document.createElement("input");
              inputHidden.setAttribute("type", "hidden");
              inputHidden.setAttribute("data-dz-id", "");
              inputHidden.value = returned.id_archivo_producto;
              file.previewElement.querySelector(".file_buttons").appendChild(inputHidden);
              //  Agregar botón eliminar
              file.previewElement.querySelector(".file_buttons").appendChild(removeBtn);
            } else {
              SweetAlert("error", returned.msg);
            }
          });
        }
      });
    }
  });
}

function cargarCantones(provincia) {
  const cantonSelect = document.getElementById("canton");
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

function cargarModelos(idSeleccionado) {
  // AJUSTA la URL al endpoint donde devuelves todos los modelos
  $.get('../api/v1/fulmuv/modelos_autos/all', {}, function (resp) {
    var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
    if (r.error) return;

    $("#modelo").empty().append('<option value="">Seleccione modelo</option>');
    r.data.forEach(m => {
      $("#modelo").append(`<option value="${m.id_modelos_autos}">${m.nombre}</option>`);
    });

    if (idSeleccionado) {
      $("#modelo").val(String(idSeleccionado));
    }
  });
}

function cargarMarcas(idSeleccionado) {
  $.get('../api/v1/fulmuv/marcas/', {}, function (resp) {
    var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
    if (r.error) return;

    $("#marca").empty().append('<option value="">Seleccione marca</option>');
    r.data.forEach(m => {
      $("#marca").append(`<option value="${m.id_marca}">${m.nombre}</option>`);
    });

    if (idSeleccionado) {
      $("#marca").val(String(idSeleccionado));
    }
  });
}

function cargarTraccion(idSeleccionado) {
  $.get('../api/v1/fulmuv/tipo_tracccion/', {}, function (resp) {
    var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
    if (r.error) return;

    $("#traccion").empty().append('<option value="">Seleccione tracción</option>');
    r.data.forEach(t => {
      $("#traccion").append(`<option value="${t.id_tipo_traccion}">${t.nombre}</option>`);
    });

    if (idSeleccionado) {
      $("#traccion").val(String(idSeleccionado));
    }
  });
}

function cargarSubtipos(idSeleccionado) {
  // Aquí puedes usar tu catálogo de tipos de auto (sedán, SUV, etc.)
  $.get('../api/v1/fulmuv/tiposAuto/', {}, function (resp) {
    var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
    if (r.error) return;

    $("#subtipo").empty().append('<option value="">Seleccione subtipo</option>');
    r.data.forEach(t => {
      $("#subtipo").append(`<option value="${t.id_tipo_auto}">${t.nombre}</option>`);
    });

    if (idSeleccionado) {
      $("#subtipo").val(String(idSeleccionado));
    }
  });
}

function cargarFuncionamientoMotor(idSeleccionado) {
  $.get('../api/v1/fulmuv/getFuncionamientoMotor/', {}, function (resp) {
    var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
    if (r.error) return;

    $("#funcionamiento_motor").empty().append('<option value="">Seleccione funcionamiento de motor</option>');
    r.data.forEach(f => {
      $("#funcionamiento_motor").append(`<option value="${f.id_funcionamiento_motor}">${f.nombre}</option>`);
    });

    if (idSeleccionado) {
      $("#funcionamiento_motor").val(String(idSeleccionado));
    }
  });
}
