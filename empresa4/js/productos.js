let categorias = [];
var tagsInput = '';
let tipo_user = $("#tipo_user").val();
let productos = [];
let productosFiltrados = [];
let id_empresa = $("#id_empresa").val();
let timerBusqueda = null;
let paginaProductos = 1;

const PRODUCTOS_PAGE_SIZE = 8;

function formatMoney(value) {
  const n = Number(value || 0);
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderEmptyProductosState(mensaje = "No existen productos registrados.", descripcion = "Cuando registres tu primer producto, aparecerá aquí para que puedas administrarlo.") {
  $("#lista_productos").html(`
    <div class="col-12">
      <div class="card border-200 shadow-sm empty-products-state">
        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5">
          <div class="rounded-circle bg-body-tertiary d-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;">
            <span class="fas fa-box-open text-600 fs-4"></span>
          </div>
          <h4 class="mb-2">${mensaje}</h4>
          <p class="text-600 mb-0" style="max-width: 520px;">${descripcion}</p>
        </div>
      </div>
    </div>
  `);
  $("#paginacion_productos").empty();
}

function renderPagination($container, totalItems, currentPage, pageSize, onChange) {
  $container.empty();
  const totalPages = Math.ceil(totalItems / pageSize);
  if (totalPages <= 1) return;

  let html = '<nav><ul class="pagination pagination-sm mb-0">';
  html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">Anterior</a></li>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
  }
  html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a></li>`;
  html += '</ul></nav>';
  $container.html(html);

  $container.find(".page-link").on("click", function (e) {
    e.preventDefault();
    const page = Number($(this).data("page"));
    if (!page || page < 1 || page > totalPages || page === currentPage) return;
    onChange(page);
  });
}

function renderProductosCards(items, page = 1) {
  const $list = $("#lista_productos").empty();
  const total = items.length;
  const start = (page - 1) * PRODUCTOS_PAGE_SIZE;
  const slice = items.slice(start, start + PRODUCTOS_PAGE_SIZE);

  slice.forEach((producto) => {
    const cat0 = firstFromJson(producto.categoria);
    const cat0Num = cat0 !== null ? Number(cat0) : 0;
    const imagenUrl = producto.img_frontal ? "../admin/" + producto.img_frontal : "files/producto_no_found.jpg";
    const categoria = (producto.nombre_categoria || '').trim();
    const tagsHtml = producto.tags
      ? producto.tags.split(',').map(tag => `<span class="badge rounded-pill badge-subtle-primary">${tag.trim()}</span>`).join(' ')
      : '<span class="badge rounded-pill badge-subtle-secondary">Sin tags</span>';
    const descuento = Number(producto.descuento || 0);

    $list.append(`
      <div class="col-md-6 col-xl-4 mb-3">
        <div class="catalog-card">
          <div class="catalog-media">
            ${descuento > 0 ? `<span class="catalog-discount">-${descuento}%</span>` : ''}
            <img src="${imagenUrl}" alt="${producto.titulo_producto || 'Producto'}" />
          </div>
          <div class="catalog-body">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
              <div class="catalog-title">${producto.titulo_producto || 'Producto sin título'}</div>
              ${categoria ? `<span class="badge rounded-pill badge-subtle-success">${categoria}</span>` : ''}
            </div>
            <div class="d-flex flex-wrap gap-1 mb-1">${tagsHtml}</div>
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
              <div class="catalog-price">$${formatMoney(producto.precio_referencia)}</div>
              ${descuento > 0 ? `<div class="catalog-meta text-end">Ahorra ${descuento}%</div>` : `<div class="catalog-meta text-end">Disponible</div>`}
            </div>
            <div class="catalog-actions">
              <a class="btn btn-sm btn-falcon-default" href="crear_producto.php?id_producto=${producto.id_producto}">
                <span class="fas fa-edit me-1"></span> Editar
              </a>
              <button class="btn btn-sm btn-falcon-default text-danger" onclick="remove(${producto.id_producto}, 'productos')">
                <span class="fas fa-trash me-1"></span> Eliminar
              </button>
              <button class="btn btn-sm btn-falcon-default text-primary" onclick="cargarAtributosCategoria(${JSON.stringify(cat0Num)}, ${producto.id_producto})">
                <i class="fi-rs-plus me-1"></i> Información
              </button>
            </div>
          </div>
        </div>
      </div>
    `);
  });

  renderPagination($("#paginacion_productos"), total, page, PRODUCTOS_PAGE_SIZE, (nextPage) => {
    paginaProductos = nextPage;
    renderProductosCards(items, nextPage);
  });
}

$(document).ready(function () {
  $.get('../api/v1/fulmuv/categorias/', { tipo: 'producto' }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      categorias = returned.data;
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
        $("#lista_empresas").trigger('change');
      }
    });
  } else {
    $("#searh_empresa").empty()
    getProductos($("#id_empresa").val())
  }
});

$("#lista_empresas").on('change', function () {
  getProductos($(this).val());
});

function filtrarProductosLive(texto){
  clearTimeout(timerBusqueda);
  timerBusqueda = setTimeout(() => {
    //filtrarProductos(texto);
    getProductosFiltro($("#id_empresa").val(),texto)
  }, 300); // 300ms después de dejar de escribir
}

function getProductos(id_empresa) {
  $.get('../api/v1/fulmuv/productos/all/' + id_empresa + '/' + tipo_user, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_productos").text("");
      productos = returned.data || [];
      productosFiltrados = [...productos];
      paginaProductos = 1;
      if (!Array.isArray(productos) || productos.length === 0) {
        renderEmptyProductosState();
        return;
      }
      renderProductosCards(productosFiltrados, paginaProductos);
    }
  });
}

function getProductosFiltro(id_empresa,consulta) {
  if(consulta == ""){
    consulta = "0"
  }
  $.get('../api/v1/fulmuv/productos/allFiltro/' + id_empresa + '/' + tipo_user + '/' + consulta, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_productos").text("");
      productos = returned.data || [];
      productosFiltrados = [...productos];
      paginaProductos = 1;
      if (!Array.isArray(productos) || productos.length === 0) {
        renderEmptyProductosState(
          "No se encontraron productos.",
          consulta && consulta !== "0"
            ? `No hay resultados para "${consulta}". Prueba con otro criterio de búsqueda.`
            : "Cuando registres tu primer producto, aparecerá aquí para que puedas administrarlo."
        );
        return;
      }
      renderProductosCards(productosFiltrados, paginaProductos);
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

function getProductoById(id_producto) {
  return (productos || []).find(item => Number(item.id_producto) === Number(id_producto)) || null;
}

function parseDetalleProducto(value) {
  if (!value) return [];
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }
  return [];
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
                        <textarea class="tinymce d-none" data-tinymce="data-tinymce" id="descripcion" ></textarea>
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
                      <form class="dropzone dropzone-multiple p-0" id="myAwesomeDropzone" action="../admin/cargar_imagen_drop.php" data-dropzone="data-dropzone">
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
      // $("#categoria").val(prod.id_categoria).trigger("change")
      // $("#sub_categoria").val(prod.sub_categoria)
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
  tinymce.init({
    selector: '#Descripción'
  });


}

function cargarDetalleProducto(jsonDetalle) {
  const detalle = JSON.parse(jsonDetalle);

  const contenedor = document.getElementById('detalleInputsContainer');
  contenedor.innerHTML = ''; // Limpiar antes de cargar nuevos inputs

  if (Array.isArray(detalle)) {
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
  console.log(categoria)
  console.log(categorias)
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


// ====== CONFIG ======
const API_BASE = '../api/v1/fulmuv/atributosCategoriaCompleto/'; // ajusta si necesitas prefijo

// ====== CARGA DINÁMICA DE ATRIBUTOS POR CATEGORÍA ======
/*async function cargarAtributosCategoria(idCategoria, id_producto) {

  $("#alert").text("")

  $("#alert").append(`  
    <div class="modal fade" id="modalAgregarInfo" tabindex="-1" aria-labelledby="modalAgregarInfoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAgregarInfoLabel">Agregar Información Adicional</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body">
            <!-- Campo oculto para devolver JSON -->
            <input type="hidden" id="informacionExtra" />

            <div class="small text-muted mb-3">
              Marca el check para incluir cada atributo en el producto. Los campos se cargan según la categoría.
            </div>

            <!-- ===== Atributos desde API ===== -->
            <div id="contenedorAtributos" class="row"></div>

            <hr class="my-4">

            <!-- ===== Campos personalizados ===== -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Campos personalizados</h6>
              <button class="btn btn-sm btn-outline-primary" type="button" id="btnAgregarCampo" onclick="getAgregarInfo()">
                <i class="bi bi-plus-circle"></i> Agregar más info
              </button>
            </div>
            <div id="contenedorCamposExtra" class="vstack g-3"></div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="guardarInformacion(${id_producto})">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  `)

  $("#modalAgregarInfo").modal("show")
  const cont = document.getElementById('contenedorAtributos');
  cont.innerHTML = '<div class="text-muted">Cargando atributos…</div>';
  try {
    const resp = await fetch(`${API_BASE}${idCategoria}`);
    const json = await resp.json();

    if (json.error) {
      cont.innerHTML = '<div class="text-danger">No se pudieron cargar los atributos.</div>';
      return;
    }

    // Render de cada atributo
    cont.innerHTML = '';
    (json.data || []).forEach((attr, idx) => {
      cont.appendChild(renderAtributo(attr, idx));
    });
  } catch (e) {
    console.error(e);
    cont.innerHTML = '<div class="text-danger">Error de red al cargar atributos.</div>';
  }
}*/

async function cargarAtributosCategoria(idCategoria, id_producto) {
  const productoActual = getProductoById(id_producto);
  const detalleExistente = parseDetalleProducto(productoActual?.detalle_producto);
  $("#alert").text("")

  $("#alert").append(`  
    <div class="modal fade" id="modalAgregarInfo" tabindex="-1" aria-labelledby="modalAgregarInfoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAgregarInfoLabel">Agregar Información Adicional</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" id="informacionExtra" />

            <div class="small text-muted mb-3">
              Marca el check para incluir cada atributo en el producto. Los campos se cargan según la categoría.
            </div>

            <!-- Barra con check maestro -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Atributos de la categoría</h6>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="chkAllAttrs">
                <label class="form-check-label" for="chkAllAttrs">Marcar / desmarcar todos</label>
              </div>
            </div>

            <!-- ===== Atributos desde API ===== -->
            <div id="contenedorAtributos" class="row"></div>

            <hr class="my-4">

            <!-- ===== Campos personalizados ===== -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Campos personalizados</h6>
              <button class="btn btn-sm btn-outline-primary" type="button" id="btnAgregarCampo" onclick="getAgregarInfo()">
                <i class="bi bi-plus-circle"></i> Agregar más info
              </button>
            </div>
            <div id="contenedorCamposExtra" class="vstack g-3"></div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="guardarInformacion(${id_producto})">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  `)

  $("#modalAgregarInfo").modal("show")

  // --- utilidades del check maestro ---
  const cont = document.getElementById('contenedorAtributos');
  const master = document.getElementById('chkAllAttrs');

  // Activa/desactiva todos
  function toggleAll(checked) {
    cont.querySelectorAll('input[type="checkbox"]').forEach(chk => {
      chk.checked = checked;
      // Si tus items reaccionan a 'change':
      chk.dispatchEvent(new Event('change', { bubbles: true }));
    });
    syncMaster();
  }

  // Sincroniza estado del maestro (checked / indeterminate)
  function syncMaster() {
    const chks = cont.querySelectorAll('input[type="checkbox"]');
    if (!chks.length) {
      master.checked = false;
      master.indeterminate = false;
      master.disabled = true;
      return;
    }
    master.disabled = false;
    const total = chks.length;
    const marcados = Array.from(chks).filter(c => c.checked).length;
    master.checked = marcados === total;
    master.indeterminate = marcados > 0 && marcados < total;
  }

  // Delegación: cuando cambie cualquier checkbox, actualiza el maestro
  cont.addEventListener('change', e => {
    if (e.target.matches('input[type="checkbox"]')) syncMaster();
  });
  // Click del maestro
  master.addEventListener('change', e => toggleAll(e.target.checked));

  // --- carga de atributos ---
  cont.innerHTML = '<div class="text-muted">Cargando atributos…</div>';
  try {
    const resp = await fetch(`${API_BASE}${idCategoria}`);
    const json = await resp.json();

    if (json.error) {
      cont.innerHTML = '<div class="text-danger">No se pudieron cargar los atributos.</div>';
      return;
    }

    // Render de cada atributo
    cont.innerHTML = '';
    (json.data || []).forEach((attr, idx) => {
      // Asegúrate de que cada atributo tenga un <input type="checkbox"> dentro
      // (ideal si tu renderAtributo ya lo incluye).
      cont.appendChild(renderAtributo(attr, idx));
    });

    applyDetalleExistente(detalleExistente);

    // Ajusta estado del maestro según lo cargado
    syncMaster();
  } catch (e) {
    console.error(e);
    cont.innerHTML = '<div class="text-danger">Error de red al cargar atributos.</div>';
  }
}


// Renderiza 1 atributo como una columna (col-12 col-lg-6)
// Checkbox+label ARRIBA, control ABAJO
function renderAtributo(attr, idx) {
  const col = document.createElement('div');
  col.className = 'col-12 col-lg-6 atributo-col mt-2';
  col.dataset.id_atributo = attr.id_atributo;
  col.dataset.nombre = attr.nombre;
  col.dataset.tipo = attr.tipo_dato;

  // ---- seleccionar control según tipo_dato ----
  let controlHTML = '';
  const tipo = (attr.tipo_dato || '').toUpperCase();

  if (tipo === 'OPCIONES') {
    let opciones = Array.isArray(attr.opciones) && attr.opciones.length ? attr.opciones : [];
    if ((!opciones || !opciones.length) && Number(attr.id_atributo) === 1) {
      opciones = ['SÍ', 'NO']; // caso especial
    }
    controlHTML = opciones.length
      ? `<select class="form-select campo-valor" data-attr-id="${attr.id_atributo}">
           ${opciones.map(o => `<option value="${escapeHtml(o)}">${escapeHtml(o)}</option>`).join('')}
         </select>`
      : `<input type="text" class="form-control campo-valor" placeholder="Escribe una opción" data-attr-id="${attr.id_atributo}">`;

  } else if (tipo === 'BOOLEANO') {
    controlHTML = `
      <select class="form-select campo-valor" data-attr-id="${attr.id_atributo}">
        <option value="SÍ">SÍ</option>
        <option value="NO">NO</option>
      </select>`;

  } else if (tipo === 'NUMERO') {
    controlHTML = `<input type="number" class="form-control campo-valor" step="any" placeholder="Ej: 10" data-attr-id="${attr.id_atributo}">`;

  } else if (tipo === 'TEXTO') {
    const nombreUpper = (attr.nombre || '').toUpperCase();
    const esLargo = /DESCRIPCIÓN|POLÍTICA|POLÍTICAS|IMÁGENES|VÍDEOS|HORARIO|UBICACIÓN/.test(nombreUpper);
    controlHTML = esLargo
      ? `<textarea class="form-control campo-valor" rows="3" placeholder="Escribe aquí…" data-attr-id="${attr.id_atributo}"></textarea>`
      : `<input type="text" class="form-control campo-valor" placeholder="Escribe aquí…" data-attr-id="${attr.id_atributo}">`;
  } else {
    controlHTML = `<input type="text" class="form-control campo-valor" placeholder="Escribe aquí…" data-attr-id="${attr.id_atributo}">`;
  }

  // ---- estructura: check arriba, control abajo ----
  col.innerHTML = `
    <div class="form-check mb-2">
      <input class="form-check-input atributo-check" type="checkbox" id="attr_${attr.id_atributo}" checked>
      <label class="form-check-label fw-semibold" for="attr_${attr.id_atributo}">
        ${attr.nombre}
      </label>
    </div>
    <div class="control-wrapper">
      ${controlHTML}
    </div>
  `;

  // Toggle habilitado según check
  const chk = col.querySelector('.atributo-check');
  const toggle = () => {
    const disabled = !chk.checked;
    col.classList.toggle('opacity-50', disabled);
    col.querySelectorAll('.control-wrapper input, .control-wrapper textarea, .control-wrapper select').forEach(el => {
      el.disabled = disabled;
    });
  };
  chk.addEventListener('change', toggle);
  toggle(); // estado inicial

  return col;
}


// Escapador básico para opciones
function escapeHtml(str) {
  return String(str)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function applyDetalleExistente(detalles) {
  const detalleArray = Array.isArray(detalles) ? detalles : [];
  if (!detalleArray.length) return;

  const attrsById = new Map();
  detalleArray.forEach(item => {
    const id = Number(item?.id);
    if (id > 0) attrsById.set(id, item);
  });

  document.querySelectorAll('#contenedorAtributos .atributo-col').forEach(col => {
    const id = Number(col.dataset.id_atributo);
    const item = attrsById.get(id);
    if (!item) return;

    const chk = col.querySelector('.atributo-check');
    const ctrl = col.querySelector('.campo-valor');
    if (!ctrl) return;

    if (chk) {
      chk.checked = true;
      chk.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const valor = item?.valor ?? '';
    ctrl.classList.remove('is-invalid');
    ctrl.value = valor;
    $(ctrl).trigger('change');
  });

  detalleArray
    .filter(item => Number(item?.id || 0) <= 0)
    .forEach(item => {
      getAgregarInfo(item);
    });
}

let contadorExtra = 0;


// Listener: usa .atributo-col (no .atributo-row)
document.addEventListener('change', (e) => {
  if (!e.target.classList.contains('atributo-check')) return;

  const col = e.target.closest('.atributo-col');
  if (!col) return;

  const disabled = !e.target.checked;
  col.classList.toggle('opacity-50', disabled);
  col.querySelectorAll('.control-wrapper input, .control-wrapper textarea, .control-wrapper select')
    .forEach(inp => (inp.disabled = disabled));
});

function guardarInformacion(id_producto) {
  const datos = [];

  // helper para marcar y avisar
  const invalid = (el, msg) => {
    try { el.classList.add('is-invalid'); } catch (_) { }
    el?.focus({ preventScroll: false });
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    swal({
      type: 'warning',
      title: 'Completa el atributo seleccionado.',
      html: msg,
      confirmButtonText: 'Ok'
    });
  };

  // ===== Atributos del API =====
  const attrs = document.querySelectorAll('#contenedorAtributos .atributo-col');
  for (const col of attrs) {
    const chk = col.querySelector('.atributo-check');
    if (!chk || !chk.checked) continue;

    const id_atributo = Number(col.dataset.id_atributo);
    const nombre = col.dataset.nombre || '';
    const tipo = (col.dataset.tipo || '').toUpperCase();

    const ctrl = col.querySelector('.campo-valor');
    if (!ctrl) continue;

    // limpiar estado inválido previo
    ctrl.classList.remove('is-invalid');

    // obtener valor y detectar vacío según tipo
    let valor = '';
    if (ctrl.tagName === 'SELECT') {
      valor = ctrl.value;
      if (valor === '' || valor == null) {
        invalid(ctrl, `Debes seleccionar un valor para <b>${nombre}</b>.`);
        return;
      }
    } else if (ctrl.tagName === 'TEXTAREA') {
      valor = (ctrl.value || '').trim();
      if (valor === '') {
        invalid(ctrl, `Debes escribir un valor para <b>${nombre}</b>.`);
        return;
      }
    } else if (ctrl.type === 'number') {
      // "0" es válido; vacío NO
      valor = ctrl.value;
      if (valor === '') {
        invalid(ctrl, `Debes ingresar un número para <b>${nombre}</b>.`);
        return;
      }
    } else {
      valor = (ctrl.value || '').trim();
      if (valor === '') {
        invalid(ctrl, `Debes ingresar un valor para <b>${nombre}</b>.`);
        return;
      }
    }

    // (Opcional) normaliza booleano si hiciera falta:
    if (tipo === 'BOOLEANO' && (valor === '' || valor == null)) valor = 'NO';

    datos.push({ id: id_atributo, label: nombre, valor });
  }

  // ===== Campos personalizados =====
  const extras = document.querySelectorAll('#contenedorCamposExtra > .border');
  for (const wrapper of extras) {
    const chk = wrapper.querySelector('.chk-extra');
    if (!chk || !chk.checked) continue;

    const etqInput = wrapper.querySelector('.etq-extra');
    const tipoSel = wrapper.querySelector('.tipo-extra');
    const tipo = tipoSel?.value || 'text';

    const etiqueta = (etqInput?.value || '').trim();
    if (!etiqueta) {
      invalid(etqInput, 'Escribe la <b>etiqueta</b> del campo personalizado.');
      return;
    }

    let valor = '';
    if (tipo === 'textarea') {
      const valEl = wrapper.querySelector('.val-extra');
      valor = (valEl?.value || '').trim();
      if (valor === '') { invalid(valEl, `Debes escribir un valor para <b>${etiqueta}</b>.`); return; }
    } else if (tipo === 'color') {
      const hexEl = wrapper.querySelector('.val-extra-color');
      const nomEl = wrapper.querySelector('.val-extra');
      const hex = hexEl?.value || '';
      const nom = (nomEl?.value || '').trim();
      valor = nom ? `${nom} (${hex})` : hex;
      if (valor === '') { invalid(hexEl || nomEl, `Debes elegir un color para <b>${etiqueta}</b>.`); return; }
    } else if (tipo === 'number') {
      const valEl = wrapper.querySelector('.val-extra');
      // "0" es válido; vacío NO
      valor = valEl?.value ?? '';
      if (valor === '') { invalid(valEl, `Debes ingresar un número para <b>${etiqueta}</b>.`); return; }
    } else {
      const valEl = wrapper.querySelector('.val-extra');
      valor = (valEl?.value || '').trim();
      if (valor === '') { invalid(valEl, `Debes ingresar un valor para <b>${etiqueta}</b>.`); return; }
    }

    // extras NO llevan id_atributo
    datos.push({ id: 0, label: etiqueta, valor });
  }

  // OK -> exporta JSONdatos
  $.post("../api/v1/fulmuv/productoAtributo/update", { id_producto: id_producto, detalle_producto: datos }, function (returnedData) {

    if (!returnedData.error) {
      SweetAlert("url_success", returned.msg, "productos.php");
    } else {
      SweetAlert("error", returned.msg);

    }

  }, 'json')


  // Si quieres cerrar el modal:
  // const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarInfo'));
  // modal?.hide();
}

async function getAgregarInfo(initialData = null) {

  console.log("INGRESO AQUÍ")
  contadorExtra++;
  const id = `extra_${contadorExtra}`;

  const wrapper = document.createElement('div');
  wrapper.className = 'border rounded p-2';

  wrapper.innerHTML = `
    <div class="row g-2 align-items-center">
      <div class="col-12 col-md-2">
        <div class="form-check">
          <input class="form-check-input chk-extra" type="checkbox" id="${id}_chk" checked>
          <label class="form-check-label fw-semibold" for="${id}_chk">Incluir</label>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <input type="text" class="form-control etq-extra" id="${id}_label" placeholder="Etiqueta (Ej: Garantía)">
      </div>

      <div class="col-12 col-md-3">
        <select class="form-select tipo-extra" id="${id}_tipo">
          <option value="text">Texto</option>
          <option value="number">Número</option>
          <option value="textarea">Área de texto</option>
          <option value="color">Color</option>
          <option value="date">Fecha</option>
        </select>
      </div>

      <div class="col valor-col"></div>

      <div class="col-12 col-md-1 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" title="Eliminar">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  `;

  const valorCol = wrapper.querySelector('.valor-col');
  const tipoSel = wrapper.querySelector('.tipo-extra');
  const chk = wrapper.querySelector('.chk-extra');

  const renderValor = () => {
    const tipo = tipoSel.value;
    let html = '';
    if (tipo === 'textarea') {
      html = `<textarea class="form-control val-extra" rows="2" placeholder="Escribe el valor"></textarea>`;
    } else if (tipo === 'color') {
      html = `
        <div class="d-flex align-items-center gap-2">
          <input type="color" class="form-control form-control-color val-extra-color" value="#000000" title="Elige un color">
          <input type="text" class="form-control val-extra" placeholder="Nombre/HEX del color (opcional)">
        </div>`;
    } else {
      html = `<input type="${tipo}" class="form-control val-extra" placeholder="Escribe el valor">`;
    }
    valorCol.className = 'col';
    valorCol.innerHTML = html;
    toggleHabilitado();
  };

  const toggleHabilitado = () => {
    const disabled = !chk.checked;
    wrapper.classList.toggle('opacity-50', disabled);
    wrapper.querySelectorAll('input, textarea, select').forEach(el => {
      if (el !== chk) el.disabled = disabled;
    });
  };

  tipoSel.addEventListener('change', renderValor);
  wrapper.addEventListener('click', (ev) => {
    if (ev.target.closest('.btn-eliminar')) {
      wrapper.remove();
    }
  });
  chk.addEventListener('change', toggleHabilitado);

  renderValor();

  if (initialData) {
    const etiqueta = String(initialData.label || '').trim();
    const valorInicial = String(initialData.valor || '').trim();
    const tipoInferido = /\(#[0-9a-fA-F]{6}\)$/.test(valorInicial) || /^#[0-9a-fA-F]{6}$/.test(valorInicial)
      ? 'color'
      : (valorInicial.length > 120 ? 'textarea' : 'text');

    wrapper.querySelector('.etq-extra').value = etiqueta;
    tipoSel.value = tipoInferido;
    renderValor();

    if (tipoInferido === 'color') {
      const colorMatch = valorInicial.match(/(#[0-9a-fA-F]{6})/);
      const hex = colorMatch ? colorMatch[1] : '#000000';
      const nombre = valorInicial.replace(/\s*\(#[0-9a-fA-F]{6}\)\s*$/, '').trim();
      const colorInput = wrapper.querySelector('.val-extra-color');
      const textInput = wrapper.querySelector('.val-extra');
      if (colorInput) colorInput.value = hex;
      if (textInput) textInput.value = nombre || hex;
    } else {
      const input = wrapper.querySelector('.val-extra');
      if (input) input.value = valorInicial;
    }
  }

  const cont = document.getElementById('contenedorCamposExtra');
  if (cont) cont.appendChild(wrapper);
}

/*$("#subirArchivo").change(function (event) {
  let file = event.target.files[0];
  if (!file) return;
  let reader = new FileReader();
  reader.onload = function (e) {
    let data = new Uint8Array(e.target.result);
    let workbook = XLSX.read(data, { type: "array" });
    let sheetName = workbook.SheetNames[0]; // Tomar la primera hoja
    let sheet = workbook.Sheets[sheetName];
    let jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });
    // Procesar los datos del Excel
    procesarExcel(jsonData);
  };
  reader.readAsArrayBuffer(file);
});

function procesarExcel(data) {
  if (data.length < 2) {
    alert("El archivo Excel está vacío o no tiene productos.");
    return;
  }
  agregarProductoDesdeExcel(data.slice(1));

}

function agregarProductoDesdeExcel(data){
  $.post('../api/v1/fulmuv/productos/excel', {
    data: data
  }, function (returnedData) {
    var returned = JSON.parse(returnedData);
    console.log(returned)
  });
}*/

let excelRows = null;

// 1) Selecciona Excel
$("#subirArchivo").change(function (event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function (e) {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: "array" });
    const sheetName = workbook.SheetNames[0];
    const sheet = workbook.Sheets[sheetName];

    const jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

    // ✅ sin cabecera + sin filas vacías
    excelRows = jsonData
      .slice(1)
      .filter(row => row && row.some(cell => String(cell ?? "").trim() !== ""));

    if (!excelRows || excelRows.length === 0) {
      alert("El Excel no tiene productos.");
      return;
    }

    // 2) Luego de leer Excel -> pedir archivos
    $("#subirArchivosProducto").val("");
    $("#subirArchivosProducto").click();
  };

  reader.readAsArrayBuffer(file);
});

// 2) Selecciona archivos y envía
$("#subirArchivosProducto").change(function () {
  if (!excelRows) {
    alert("Primero carga el Excel.");
    return;
  }

  const files = $("#subirArchivosProducto")[0].files;

  // ✅ Si quieres permitir sin archivos, puedes quitar esta validación
  /*if (!files || files.length === 0) {
    alert("Selecciona imágenes/pdf para anexar a los productos.");
    return;
  }*/

  // ✅ Inyectar 3 campos por fila (al final)
  const excelRowsFinal = excelRows.map(row => {
    const r = [...row]; // copia para no mutar el original
    r.push(id_empresa);
    r.push(tipo_user);
    return r;
  });

  const fd = new FormData();
  fd.append("data", JSON.stringify(excelRows)); // 👈 excel como JSON string

  for (let i = 0; i < files.length; i++) {
    fd.append("archivos[]", files[i], files[i].name); // 👈 nombre real
  }

  $.ajax({
    url: "../api/v1/fulmuv/productos/excel",
    type: "POST",
    data: fd,
    processData: false,
    contentType: false,
    success: function (resp) {
      // si tu API devuelve string JSON, parsea:
      try { resp = typeof resp === "string" ? JSON.parse(resp) : resp; } catch(e){}
      console.log(resp);

      // Limpia variables
      excelRows = null;
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      //alert("Error al cargar Excel/archivos.");
      excelRows = null;
    }
  });
});
