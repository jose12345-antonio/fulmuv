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
    getEmpleos($("#id_empresa").val())
  }
});

function filtrarEmpleosLive(texto) {
  clearTimeout(timerBusqueda);
  timerBusqueda = setTimeout(() => {
    //filtrarProductos(texto);
    getEmpleosFiltro($("#id_empresa").val(), texto)
  }, 300); // 300ms después de dejar de escribir
}

$("#lista_empresas").on('change', function () {
  getEmpleos($(this).val());
});

function getEmpleos(id_empresa) {
  $.get('../api/v1/fulmuv/empleos/all/' + id_empresa + '/' + tipo_user, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_empleos").text("");
      returned.data.forEach(empleo => {

        // Buscar el primer archivo tipo 'imagen'
        //const archivoImagen = producto.archivos?.find(archivo => archivo.tipo === 'imagen');
        //<!--img class="rounded-1 border border-200" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" width="60" height="60" alt="" /-->
        $("#lista_empleos").append(`
          <div class="mb-4 col-md-6 col-lg-3">
            <div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
              <div class="overflow-hidden">
                  <div class="position-relative rounded-top overflow-hidden">
                    <a class="d-block">
                      <img class="product-img-wrap rounded-top" src="${empleo.img_frontal ? "../admin/" + empleo.img_frontal : "files/producto_no_found.jpg"}" alt="" />
                    </a>
                  </div>
                  <div class="p-3">
                      <h5 class="fs-9"><a class="text-1100" onclick="editProducto(${empleo.id_empleo})">${empleo.titulo}</a></h5>
                      <p class="fs-10 mb-0"><a class="text-500" href="#!">${empleo.provincia}</a></p>
                      <p class="fs-10 mb-3"><a class="text-500" href="#!">${empleo.canton}</a></p>
                      <!--h5 class="fs-md-7 text-warning mb-0 d-flex align-items-center mb-3"> $${empleo.precio_referencia}
                          <del class="ms-2 fs-10 text-500">$${empleo.precio_referencia} </del>
                      </h5-->
                      </p>
                  </div>
              </div>
              <div class="d-flex flex-end-center px-3">
                  <div class="text-center">
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2" 
                      onclick="openCVRecibidos(${empleo.id_empleo}, '${(empleo.titulo || '').replace(/'/g, "\\'")}')" 
                      data-bs-toggle="tooltip" data-bs-placement="top" title="CV recibidos">
                      <i class="bi bi-file-earmark-person"></i> CV
                    </a>
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2" onclick="editEmpleo(${empleo.id_empleo})" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar">
                      <span class="fas fa-edit"></span>
                    </a>
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2 text-danger" onclick="remove(${empleo.id_empleo}, 'vehiculos')" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
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

function getEmpleosFiltro(id_empresa, consulta) {
  if (consulta == "") {
    consulta = "0"
  }
  $.get('../api/v1/fulmuv/empleos/allFiltro/' + id_empresa + '/' + tipo_user + '/' + consulta, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_empleos").text("");
      returned.data.forEach(empleo => {

        // Buscar el primer archivo tipo 'imagen'
        //const archivoImagen = producto.archivos?.find(archivo => archivo.tipo === 'imagen');
        //<!--img class="rounded-1 border border-200" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" width="60" height="60" alt="" /-->
        $("#lista_empleos").append(`
          <div class="mb-4 col-md-6 col-lg-3">
            <div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
              <div class="overflow-hidden">
                  <div class="position-relative rounded-top overflow-hidden">
                    <a class="d-block" >
                      <img class="product-img-wrap rounded-top" src="${empleo.img_frontal ? "../admin/" + empleo.img_frontal : "files/producto_no_found.jpg"}" alt="" />
                    </a>
                  </div>
                  <div class="p-3">
                      <h5 class="fs-9"><a class="text-1100" onclick="editProducto(${empleo.id_empleo})">${empleo.titulo}</a></h5>
                      <p class="fs-10 mb-0"><a class="text-500" href="#!">${empleo.provincia}</a></p>
                      <p class="fs-10 mb-3"><a class="text-500" href="#!">${empleo.canton}</a></p>
                      <!--h5 class="fs-md-7 text-warning mb-0 d-flex align-items-center mb-3"> $${empleo.precio_referencia}
                          <del class="ms-2 fs-10 text-500">$${empleo.precio_referencia} </del>
                      </h5-->
                      </p>
                  </div>
              </div>
              <div class="d-flex flex-end-center px-3">
                  <div class="text-center">
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2" onclick="editEmpleo(${empleo.id_empleo})" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar">
                      <span class="fas fa-edit"></span>
                    </a>
                    <a class="btn btn-sm btn-falcon-default me-2 mt-2 text-danger" onclick="remove(${empleo.id_empleo}, 'vehiculos')" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
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

function editEmpleo(id_empleo) {
  $.get('../api/v1/fulmuv/empleos/' + id_empleo, {}, function (returnedData) {
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar empleo</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-12 mb-2">
                      <label class="form-label" for="titulo">Título:</label>
                      <input class="form-control" id="titulo" type="text" value="${prod.titulo}" />
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="provincia">Provincia:</label>
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
                      <label class="form-label" for="canton">Cantón:</label>
                      <select class="form-select" id="canton" name="canton"></select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="product-tags">Fecha Inicio:</label>
                      <input
                          class="form-control"
                          id="fecha_inicio"
                          type="date"
                          name="fecha_inicio"
                          required="required"
                          />
                    </div>
                    <div class="col-6 mb-2">
                     <label class="form-label" for="product-tags">Fecha Fin:</label>
                      <input
                          class="form-control"
                          id="fecha_fin"
                          type="date"
                          name="fecha_fin"
                          required="required"
                          />
                    </div>
                    <div class="col-12 mb-2">
                      <label class="form-label" for="descripcion">Descripción: </label>
                      <textarea class="form-control" id="descripcion">${prod.descripcion || ''}</textarea>
                    </div>
                    
                    <div class="col-12 mb-2">
                      <label class="form-label" for="tags">Tags:</label>
                      <input class="form-control" id="tags" type="text" name="tags" required="required" size="1" data-options='{"removeItemButton":true,"placeholder":false}' />
                    </div>

                    <div class="col-12 mb-2">
                      <h6>Archivos (Imagen y Ficha Técnica)</h6>
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
                <button class="btn btn-iso" type="button" onclick="updateEmpleo(${prod.id_empleo})">Actualizar</button>
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

      $("#fecha_inicio").val(prod.fecha_inicio);
      $("#fecha_fin").val(prod.fecha_fin);

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

      // --------- ARCHIVOS / DROPZONE ----------
      loadFilesToDropzone(prod.id_empleo, prod.archivos || []);
    }
  });
}


function updateEmpleo(id_empleo) {
  var titulo = $("#titulo").val();
  var descripcion = $("#descripcion").val();
  var provincia = $("#provincia").val();
  var canton = $("#canton").val();
  var tags = tagsInput.getValue(true);
  tags = tags.map(tag => tag.toUpperCase());

  if (descripcion == "" || titulo == "") {
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
    $.post('../api/v1/fulmuv/empleos/update', {
      id_empleo: id_empleo,
      titulo: titulo,
      descripcion: descripcion,
      tags: tags.join(', '),
      provincia: provincia,
      canton: canton
      // img_path: file.img ? file.img : prod.img_path,
      // ficha_tecnica: file.pdf ? file.pdf : prod.ficha_tecnica
    }, function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (!returned.error) {
        SweetAlert("url_success", returned.msg, "empleos.php");
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
        nameLink.href = "../admin/" + file.archivo;
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

function openCVRecibidos(id_empleo, titulo = "") {
  $("#cvModalTitle").text(`CV recibidos - ${titulo || ("Empleo #" + id_empleo)}`);

  $("#tbodyCVRecibidos").html("");
  $("#cvEmpty").hide();
  $("#cvLoading").show();

  const modal = new bootstrap.Modal(document.getElementById("modalCVRecibidos"));
  modal.show();

  // ✅ AJUSTA esta ruta a tu endpoint real:
  const url = `../api/v1/fulmuv/empleos/envioempleos`;

  $.post(url, {id_empleo}, function (resp) {
    $("#cvLoading").hide();

    let data = resp;
    if (typeof resp === "string") {
      try { data = JSON.parse(resp); } catch { data = []; }
    }

    // ✅ Soporta array directo
    const rows = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);

    if (!rows.length) {
      $("#cvEmpty").show();
      return;
    }

    rows.forEach(p => {
      const nombre = p.nombres_apellidos || "-";
      const correo = p.correo || "-";
      const telefono = p.telefono || "-";
      const fecha = (p.created_at || "").replace("T", " ").split(".")[0] || "-";

      // cv viene como "files/....pdf"
      const cvPath = p.cv || "";
      const cvUrl = cvPath
        ? (cvPath.startsWith("http") ? cvPath : `../${cvPath}`)
        : "";

      $("#tbodyCVRecibidos").append(`
        <tr>
          <td>${nombre}</td>
          <td>${correo}</td>
          <td>${telefono}</td>
          <td>${fecha}</td>
          <td class="text-end">
            ${cvUrl ? `
              <a class="btn btn-sm btn-falcon-default"
                 href="${cvUrl}" target="_blank" rel="noopener">
                <i class="bi bi-file-earmark-pdf"></i> Ver CV
              </a>
            ` : `<span class="text-muted">Sin CV</span>`}
          </td>
        </tr>
      `);
    });

  }).fail(function (xhr) {
    $("#cvLoading").hide();
    $("#cvEmpty").show();
    console.log("Error CV:", xhr.responseText);
  });
}


document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
