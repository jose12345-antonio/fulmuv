$(document).ready(function () {
  $.get("../api/v1/fulmuv/categoriasPrincipales/All", {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      $("#lista_categorias").text("");
      returned.data.forEach((categoria) => {
        $("#lista_categorias").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium">${categoria.nombre}</td>
            <td class="py-2 align-middle fs-9 fw-medium"><img src="${categoria.imagen}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" style="width: 80px; height: 80px; object-fit: scale-down"></td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                  <div class="py-2">
                  <a class="dropdown-item text-info" onclick="CategoriaById(${categoria.id_categoria_principal})">Actualizar</a>
                    <a class="dropdown-item text-danger" onclick="remove(${categoria.id_categoria_principal}, 'categorias_principales')">Eliminar</a>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        `);
      });
      $("#my_table").DataTable({
        searching: true,
        responsive: false,
        pageLength: 8,
        info: true,
        lengthChange: false,
        language: {
          url: "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
          paginate: {
            next: '<span class="fas fa-chevron-right"></span>',
            previous: '<span class="fas fa-chevron-left"></span>',
          },
        },
        dom:
          "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" +
          "<'table-responsive scrollbar'tr>" +
          "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>",
      });
    }
  });
});

function remove(id, tabla) {
  swal(
    {
      title: "Alerta",
      text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#27b394",
      confirmButtonText: "Sí",
      cancelButtonText: "No",
      closeOnConfirm: false,
    },
    function () {
      $.post(
        "../api/v1/fulmuv/" + tabla + "/delete",
        {
          id: id,
        },
        function (returnedData) {
          var returned = JSON.parse(returnedData);
          if (returned.error == false) {
            SweetAlert("url_success", returned.msg, "categorias.php");
          } else {
            SweetAlert("error", returned.msg);
          }
        }
      );
    }
  );
}

function UpdateCategoria(id_categoria) {
  console.log($("#atributos").val());
  /*$.post(
    "../api/v1/fulmuv/updateCategoria",
    {
      id_categoria: id_categoria,
      atributos: $("#atributos").val(),
    },
    function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "categorias.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    }
  );*/
}

function addCategorias() {
  console.log("a");
  // Limpiar modal anterior si existe
  $("#staticBackdrop").remove();

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
              <h4 class="mb-1" id="staticBackdropLabel">Crear Categoría Principal</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <!-- Datos básicos -->
                <div class="col-md-12 mb-3">
                  <label class="form-label">Nombre</label>
                  <input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()"/>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">Imagen Categoria</label>
                  <input class="form-control" id="imagen" type="file" />
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="guardarCategorias()">Guardar</button>
        </div>
  

        </div>
      </div>
    </div>
  `);

  // Mostrar modal
  
  $("#btnModal").click();
}

function guardarCategorias() {
  var nombre = $("#nombre").val();
  var files = $("#imagen")[0].files;

  if (nombre == "" && files.length === 0) {
    SweetAlert("error", "Los campos nombre e imagen son obligatorios!!!");
    return;
  }

  var file = files[0];
  var filePromise =
    file === undefined
      ? Promise.resolve(empresaData.img_path)
      : saveFiles(file);

  filePromise.then(function (file) {
    $.post(
      "../api/v1/fulmuv/categoriasPrincipales/create",
      {
        nombre: nombre,
        imagen: file.img ? file.img : empresaData.img_path,
      },
      function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (returned.error == false) {
          SweetAlert("url_success", returned.msg, "categorias_principales.php");
        } else {
          SweetAlert("error", returned.msg);
        }
      }
    );
  });
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    console.log(files);
    if (files == undefined) {
      resolve(); // Resuelve la promesa incluso si no hay imágenes
    } else {
      const formData = new FormData();
      formData.append(`archivos[]`, files); // añadrir los archivos al form
      $.ajax({
        type: "POST",
        data: formData,
        url: "cargar_imagen.php",
        cache: false,
        contentType: false,
        processData: false,
        success: function (returnedImagen) {
          if (returnedImagen["response"] == "success") {
            resolve(returnedImagen["data"]); // Resuelve la promesa cuando la llamada AJAX se completa con éxito
          } else {
            SweetAlert(
              "error",
              "Ocurrió un error al guardar los archivos." +
                returnedImagen["error"]
            );
            reject(); // Rechaza la promesa en caso de error
          }
        },
      });
    }
  });
}

function CategoriaById(categoria) {
  $.get(
    "../api/v1/fulmuv/categoriaPrincipal/" + categoria,
    function (returnedData) {
      if (!returnedData.error) {
        // Limpiar modal anterior si existe
        $("#staticBackdrop").remove();

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
                    <h4 class="mb-1" id="staticBackdropLabel">Actualizar Categoria</h4>
                  </div>
                  <div class="p-4">
                    <div class="row g-2">
                      <!-- Datos básicos -->
                      <div class="col-md-12 mb-3"><label class="form-label">Nombre</label><input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()"/></div>
                      
                      <div class="col-md-12 mb-3"><label class="form-label">Imagen Categoria</label><input class="form-control" id="imagen" type="file" /></div>

                    </div>

                  </div>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editCategorias(${returnedData.data.id_categoria_principal})">Guardar</button>
              </div>
        

              </div>
            </div>
          </div>
        `);

        

        
        $("#nombre").val(returnedData.data.nombre)

        // Mostrar modal
        $("#btnModal").click();
      }
    },
    "json"
  );
}



function editCategorias(id_categoria) {
  const nombre = $("#nombre").val();
  const files = $("#imagen")[0].files;

  if (nombre.trim() === "") {
    SweetAlert("error", "El campo nombre es obligatorio.");
    return;
  }

  const file = files.length > 0 ? files[0] : undefined;

  // Subir imagen si se seleccionó una nueva
  const filePromise = file ? saveFiles(file) : Promise.resolve({ img: null });

  filePromise.then((file) => {
    // const datos = {
    //   id_categoria: id_categoria,
    //   nombre: nombre,
    //   tipo: tipo,
    // };

    // Solo añadir imagen si se subió una nueva
    if (file.img) {
      // datos.imagen = file.img;
    }

    $.post("../api/v1/fulmuv/categoriaPrincipal/update", {
      id_categoria: id_categoria,
      nombre: nombre,
      imagen: file.img
    }, function (returnedData) {
      const returned = JSON.parse(returnedData);
      if (returned.error === false) {
        SweetAlert("url_success", returned.msg, "categorias_principales.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  }).catch((error) => {
    console.error("Error al subir imagen:", error);
    SweetAlert("error", "Ocurrió un error al subir la imagen.");
  });
}










