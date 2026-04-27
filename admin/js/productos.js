let categorias = [];
var tagsInput = '';

$(document).ready(function () {
  $.get('../api/v1/fulmuv/categorias/', {tipo: 'producto'}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      categorias = returned.data;
    }
  });
  // La carga de empresas la maneja el footer (initAdminEmpresaSelector)
});

$("#lista_empresas").on('change', function () {
  getProductos($(this).val());
});

function getProductos(id_empresa){
  $.get('../api/v1/fulmuv/productos/all/'+id_empresa, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#tabla_contenido").text("")
      $("#tabla_contenido").append(`
        <table class="table table-sm mb-0 data-table fs-10" id="my_table">
            <thead class="bg-200">
                <tr>
                    <th class="text-900 sort pe-1 align-middle white-space-nowrap">Nombre</th>
                    <th class="text-900 sort pe-1 align-middle white-space-nowrap">Precio base</th>
                    <th class="text-900 sort pe-1 align-middle white-space-nowrap">Categoría</th>
                    <th class="text-900 sort pe-1 align-middle white-space-nowrap">Sub-categoría</th>
                    <th class="align-middle no-sort"></th>
                </tr>
            </thead>
            <tbody id="lista_productos">

            </tbody>
        </table>
      `);
      $("#lista_productos").text("");
      returned.data.forEach(producto => {

        // Buscar el primer archivo tipo 'imagen'
        const archivoImagen = producto.archivos?.find(archivo => archivo.tipo === 'imagen');

        $("#lista_productos").append(`
          <tr class="btn-reveal-trigger">
            <td>
              <div class="d-flex align-items-center position-relative"><img class="rounded-1 border border-200" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" width="60" height="60" alt="" />
                <div class="flex-1 ms-3">
                  <h6 class="mb-1 fw-semi-bold text-nowrap"><a class="text-900 stretched-link" onclick="editProducto(${producto.id_producto})">${producto.nombre}</a></h6>
                  <p class="fw-semi-bold mb-0 text-500">${producto.tags}</p>
                </div>
              </div>
            </td>
            <td class="amount py-2 align-middle fs-9 fw-medium">$${producto.precio_referencia}</td>
            <td class="align-middle text-start fw-semi-bold">${producto.nombre_categoria}</td>
            <td class="align-middle text-start fw-semi-bold">${producto.nombre_sub_categoria}</td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                  <div class="py-2">
                    <a class="dropdown-item" onclick="editProducto(${producto.id_producto})">Editar</a>
                    <a class="dropdown-item text-danger" onclick="remove(${producto.id_producto}, 'productos')">Eliminar</a>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        `);
      });
      // if ($.fn.DataTable.isDataTable('#my_table')) {
      //   $('#my_table').DataTable().destroy();
      // }
      $("#my_table").DataTable({
        "searching": true,
        "responsive": false,
        "pageLength": 8,
        "info": true,
        "lengthChange": false,
        "language": {
          "url": "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
          "paginate": {
            "next": "<span class=\"fas fa-chevron-right\"></span>",
            "previous": "<span class=\"fas fa-chevron-left\"></span>"
          }
        },
        "dom": "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" + "<'table-responsive scrollbar'tr>" + "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>"
      })
    }
  });
}

function editProducto(id_producto) {
  $.get('../api/v1/fulmuv/productos/' + id_producto, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      prod = returned.data
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar producto</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-6 mb-2">
                      <label class="form-label" for="nombre">Nombre producto:</label>
                      <input class="form-control" id="nombre" type="text" value="${prod.nombre}" disabled oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="codigo">N° identificación:</label>
                      <input class="form-control" id="codigo" type="text" value="${prod.codigo}" disabled oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="categoria">Seleccione categoría:</label>
                      <select class="form-select" id="categoria" name="categoria" onchange="llenarSubCategria()">
                      </select>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="sub_categoria">Seleccione sub-categoría:</label>
                      <select class="form-select" id="sub_categoria" name="sub_categoria">
                      </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label" for="descripcion">Descripción: </label>
                        <textarea class="form-control" id="descripcion" type="text" rows="4" oninput="this.value = this.value.toUpperCase()">${prod.descripcion}</textarea>
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="precio_referencia">Precio base: <span data-bs-toggle="tooltip" data-bs-placement="top" title="Precio regular del producto"><span class="fas fa-question-circle text-primary fs-10 ms-1"></span></span></label>
                      <input class="form-control" id="precio_referencia" type="number" min="1" value="${prod.precio_referencia}" />
                    </div>
                    <div class="col-6 mb-2">
                      <label class="form-label" for="descuento">Descuento: <span data-bs-toggle="tooltip" data-bs-placement="top" title="Descuento del producto"><span class="fas fa-question-circle text-primary fs-10 ms-1"></span></span></label>
                      <input class="form-control" id="descuento" type="number" min="1" value="${prod.descuento}" />
                    </div>
                    <div class="col-12 mb-2">
                      <label class="form-label" for="tags">Tags:</label>
                      <input class="form-control" id="tags" type="text" name="tags" required="required" size="1" data-options='{"removeItemButton":true,"placeholder":false}' />     
                    </div>

                    <h6>Detalles extras</h6>
                    <hr class="mt-0"></hr>
                    <div class="row mb-2" id="detalleInputsContainer"></div>
                    
                    <div class="col-12 mb-2">
                      <h6 class="">Archivos (Imagen y Ficha Técnica)</h6>
                      <form class="dropzone dropzone-multiple p-0" id="myAwesomeDropzone" action="cargar_imagen_drop.php" data-dropzone="data-dropzone">
                        <div class="fallback">
                          <input name="archivos[]" type="file" multiple />
                        </div>
                        <div class="dz-message my-0" data-dz-message="data-dz-message">
                          <img class="me-2" src="../theme/public/assets/img/icons/cloud-upload.svg" width="25" alt="" />
                          <span class="d-none d-lg-inline">Drag your image here<br />or, </span>
                          <span class="btn btn-link p-0 fs-10">Browse</span>
                        </div>

                        <!-- Contenedor de previews (Dropzone agregará aquí los archivos) -->
                        <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column" id="file-previews"></div>
                      </form>
                    </div>

                    <!-- Template oculto para Dropzone -->
                    <div id="uploadPreviewTemplate" style="display: none;">
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
                          <div class="dropdown font-sans-serif file_buttons">
                            <!-- Aquí se agregará dinámicamente el botón eliminar -->
                          </div>
                        </div>
                      </div>
                    </div>


                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-iso" type="button" onclick="updateProducto(${id_producto})">Actualizar</button>
              </div>
            </div>
          </div>
      `);

      cargarDetalleProducto(prod.detalle_producto);
      loadFilesToDropzone(prod.id_producto, prod.archivos)

      categorias.forEach(categoria => {
        $("#categoria").append(`
          <option value="${categoria.id_categoria}">${categoria.nombre}</option>
        `);
      });
      $("#categoria").val(prod.id_categoria).trigger("change")
      $("#sub_categoria").val(prod.sub_categoria)
      tagsInput = new Choices('#tags', {
        removeItemButton: true,
        placeholder: false,
        items: (prod.tags != "" && prod.tags.split(",").length) ? prod.tags.split(",") : [],
        maxItemCount: 3,
        addItemText: (value) => {
          return `Presiona Enter para añadir <b>"${value}"</b>`;
        },
        maxItemText: (maxItemCount) => {
          return `Solo ${maxItemCount} tags pueden ser añadidos`;
        },
      });
      $("#btnModal").click();
    }
  });
}

function cargarDetalleProducto(jsonDetalle) {
    const detalle = JSON.parse(jsonDetalle);
    const contenedor = document.getElementById('detalleInputsContainer');
    contenedor.innerHTML = ''; // Limpiar antes de cargar nuevos inputs

    detalle.forEach((item, index) => {
        const col = document.createElement('div');
        col.className = 'col-md-6 mb-3';

        col.innerHTML = `
            <label class="form-label">${item.label}</label>
            <input type="text" class="form-control" name="detalle_valor[]" value="${item.valor}" data-id="${item.id}">
        `;

        contenedor.appendChild(col);
    });
}

function updateProducto(id_producto) {
  var nombre = $("#nombre").val();
  var codigo = $("#codigo").val();
  var descripcion = $("#descripcion").val();
  var categoria = $("#categoria").val();
  var sub_categoria = $("#sub_categoria").val();
  var tags = tagsInput.getValue(true);
  tags = tags.map(tag => tag.toUpperCase());
  var precio_referencia = $("#precio_referencia").val();
  var descuento = $("#descuento").val();


  if (nombre == "" || descripcion == "" || codigo == "" || precio_referencia == "") {
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
      $.post('../api/v1/fulmuv/productos/update', {
        id_producto: id_producto,
        nombre: nombre,
        descripcion: descripcion,
        codigo: codigo,
        categoria: categoria,
        sub_categoria: sub_categoria,
        tags: tags.join(', '),
        precio_referencia: precio_referencia,
        descuento: descuento,
        detalle_producto: detalleActualizado
        // img_path: file.img ? file.img : prod.img_path,
        // ficha_tecnica: file.pdf ? file.pdf : prod.ficha_tecnica
      }, function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (!returned.error) {
          SweetAlert("url_success", returned.msg, "productos.php");
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
        url: 'cargar_imagen.php',
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

function llenarSubCategria() {
  var categoria = parseInt($("#categoria").val());
  const arrayCategoria = categorias.find((cat) => cat.id_categoria === categoria);
  $("#sub_categoria").text("")
  arrayCategoria.sub_categorias.forEach(sub_categoria => {
    $("#sub_categoria").append(`
      <option value="${sub_categoria.id_sub_categoria}">${sub_categoria.nombre}</option>
    `);
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
        SweetAlert("url_success", returned.msg, "productos.php")
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
    url: "cargar_imagen_drop.php",
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
        nameLink.href = file.archivo;
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
          $.post('../api/v1/fulmuv/deleteFileProducto', {
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
