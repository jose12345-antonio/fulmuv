let atributos = [];

$(document).ready(function () {
  $.get("../api/v1/fulmuv/banner/all", {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      $("#lista_banner").text("");

      

      returned.data.forEach((banner) => {
        $("#lista_banner").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium">
              <img src="${banner.imagen}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" style="width: 80px; height: 80px; object-fit: scale-down">
            </td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="fas fa-ellipsis-h fs-10"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border py-0">
                  <div class="py-2">
                    <a class="dropdown-item text-info" onclick="BannerById(${banner.id_banner})">Actualizar</a>
                    <a class="dropdown-item text-danger" onclick="remove(${banner.id_banner}, 'banner')">Eliminar</a>
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

  $.get("../api/v1/fulmuv/atributos/", {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      atributos = returned.data;
    }
  });
});

function asignar_atributo(id_banner) {
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
            
            <div class="p-4">
              <div class="row g-2">
                
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Atributos</label>
                  <select class="form-select" id="atributos" multiple>
                    
                  </select>
                </div>
                <div class="col-12">
                  <button onclick="UpdateBanner(${id_banner})" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  const organizerMultiple = document.getElementById("atributos");
  const choices = new Choices(organizerMultiple, {
    removeItemButton: true,
    placeholder: true,
    placeholderValue: "Seleccione atributos",
    allowHTML: true,
    position: "bottom",
  });
  $.get(
    "../api/v1/fulmuv/atributosBanner/" + id_banner,
    {},
    function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        console.log(returned.data.atributos);
        var atri = JSON.parse(returned.data.atributos);
        atri.forEach((val) => {
          choices.setChoiceByValue(val.toString());
        });
      }
    }
  );
  /*$.get('../api/v1/fulmuv/atributos/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#atributos").text("");
      returned.data.forEach(atributos => {
        $("#atributos").append(
          `<option value="${atributos.id_atributo}">${atributos.nombre}</option>`
        );
      });
      const organizerMultiple = document.getElementById('atributos');
      const choices = new Choices(organizerMultiple, {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'Seleccione atributos',
      });
    }
  });*/
  $("#btnModal").click();
}

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
            SweetAlert("url_success", returned.msg, "banner.php");
          } else {
            SweetAlert("error", returned.msg);
          }
        }
      );
    }
  );
}


function UpdateBanner(id_banner) {
  console.log($("#atributos").val());
  $.post(
    "../api/v1/fulmuv/banner/update",
    {
      id_banner: id_banner,
      atributos: $("#atributos").val(),
    },
    function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "banner.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    }
  );
}

function addBanner() {
  console.log("a");
  // Limpiar modal anterior si existe

  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0">
          <div class="modal-header bg-light">
            <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Agregar Banner</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>


          
          <div class="modal-body p-4">
            <div class="mb-3">
              <label for="formFile" class="form-label">Imagen de Banner</label>
              <input class="form-control" type="file" id="formfile">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="guardarBanner()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  `);

  // Mostrar modal

  $("#btnModal").click();
}

function guardarBanner(modo = "crear") {
  var files = $("#formfile")[0].files;

  if (files.length === 0) {
    SweetAlert("error", "Imagen (solo en creación) son obligatorios!!!");
    return;
  }

  var file = files[0];
  var filePromise =
    file === undefined
      ? Promise.resolve(empresaData.img_path)
      : saveFiles(file);

  filePromise.then(function (file) {
    $.post(
      "../api/v1/fulmuv/banner/create",
      {
        imagen: file.img ? file.img : empresaData.img_path,
      },
      function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (returned.error == false) {
          SweetAlert("url_success", returned.msg, "banner.php");
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
      resolve(); 
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

function BannerById(banner) {
  $.get(
    "../api/v1/fulmuv/banner/" + banner,
    function (returnedData) {
      if (!returnedData.error) {
        console.log("a");
        // Limpiar modal anterior si existe
        $("#staticBackdrop").remove();

        $("#alert").text("");
        $("#alert").append(`
          <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
          <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl mt-6" role="document">
              <div class="modal-content border-0">
                <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                  <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                  <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                    <h4 class="mb-1" id="staticBackdropLabel">Actualizar Banner</h4>
                  </div>
                </div>
                <div class="p-4">
                    <div class="row g-2">
                      <div class="col-md-12 mb-3"><label class="form-label">Imagen de Banner</label>
                      <input class="form-control" id="imagen" type="file" /></div>
                    </div>

                  </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editBanner(${returnedData.data.id_banner})">Guardar</button>
              </div>
        

              </div>
            </div>
          </div>
        `);

        // Mostrar modal
        $("#btnModal").click();
      }
    },
    "json"
  );
}

function editBanner(id_banner) {
  var files = $("#imagen")[0].files;

  if (files.length === 0) {
    SweetAlert("error", "Imagen (solo en creación) son obligatorios!!!");
    return;
  }


  var file = files.length > 0 ? files[0] : undefined;
  var filePromise = file ? saveFiles(file) : Promise.resolve({ img: null });

  filePromise
    .then((file) => {
      const datos = {
        id_banner: id_banner,
      };


      if (file.img) {
        datos.imagen = file.img;
      }

      $.post(
        "../api/v1/fulmuv/banner/update",
        datos,
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (returned.error === false) {
            SweetAlert("url_success", returned.msg, "banner.php");
          } else {
            SweetAlert("error", returned.msg);
          }
        }
      );
    })
    .catch((error) => {
      console.error("Error al subir imagen:", error);
      SweetAlert("error", "Ocurrió un error al subir la imagen.");
    });
}





