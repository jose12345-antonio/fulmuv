let atributos = [];
var imagenActual = ""

$(document).ready(function () {
  $.get("../api/v1/fulmuv/eventos/all", {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      $("#lista_categorias").text("");
      returned.data.forEach((eventos) => {
        $("#lista_categorias").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium"><img src="${eventos.imagen
          }" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" style="width: 80px; height: 80px; object-fit: scale-down"></td>
            <td class="py-2 align-middle fs-9">${eventos.titulo || ""
          }</td>
            <td class="py-2 align-middle fs-9">${eventos.descripcion || ""
          }</td>
            <td class="py-2 align-middle fs-9">${eventos.tipo || ""
          }</td>
            <td class="py-2 align-middle fs-9">${eventos.fecha_hora_inicio || ""
          }</td>
            <td class="py-2 align-middle fs-9">${eventos.fecha_hora_fin || ""
          }</td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                  <span class="fas fa-ellipsis-h fs-10"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border py-0">
                  <div class="py-2">
                    <a class="dropdown-item text-info" onclick="EventosById(${eventos.id_evento
          })">Actualizar</a>
                    <a class="dropdown-item text-danger" onclick="remove(${eventos.id_evento
          }, 'eventos')">Eliminar</a>
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

function asignar_atributo(id_eventos) {
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
                  <select class="form-select" id="atributos" multiple></select>
                </div>
                <div class="col-12">
                  <button onclick="UpdateEventos(${id_eventos})" class="btn btn-primary" type="submit">Guardar</button>
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

  atributos.forEach((a) => {
    choices.setChoices([
      {
        value: a.id_atributo.toString(),
        label: a.nombre,
        selected: false,
        disabled: false,
      },
    ]);
  });

  $.get(
    "../api/v1/fulmuv/atributosEventos/" + id_eventos,
    {},
    function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        var atri = JSON.parse(returned.data.atributos);
        atri.forEach((val) => {
          choices.setChoiceByValue(val.toString());
        });
        $("#btnModal").click();
      }
    }
  );
}
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
        `../api/v1/fulmuv/${tabla}/delete`,
        { id: id },
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (!returned.error) {
            SweetAlert("url_success", returned.msg, "eventos.php");
          } else {
            SweetAlert("error", returned.msg);
          }
        }
      );
    }
  );
}

function UpdateEventos(id_eventos) {
  console.log($("#atributos").val());
  /*$.post(
    "../api/v1/fulmuv/updateEventos",
    {
      id_eventos: id_eventos,
      atributos: $("#atributos").val(),
    },
    function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "eventos.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    }
  );*/
}

function addEventos() {
  console.log("a");

  // Limpiar contenido anterior
  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>

    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0">
          <div class="modal-header bg-light">
            <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Agregar Eventos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-4">
            <div class="mb-3">
              <label for="formFile" class="form-label">Imagen del Evento</label>
              <input class="form-control" type="file" id="imagen">
            </div>

            <div class="mb-3">
              <label for="tipoEvento" class="form-label">Tipo de Evento</label>
              <select class="form-select" id="tipoEvento">
                <option value="">Seleccione tipo</option>
                <option value="Conferencia">Conferencia</option>
                <option value="Seminario">Seminario</option>
                <option value="Feria">Feria</option>
                <option value="Festival">Festival</option>
                <option value="Taller">Taller</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="tituloEvento" class="form-label">Título del Evento</label>
              <input class="form-control" type="text" id="tituloEvento" placeholder="Ingrese el título">
            </div>

            <div class="mb-3">
              <label for="descripcionEvento" class="form-label">Descripción</label>
              <textarea class="form-control" id="descripcionEvento" rows="5" placeholder="Describa brevemente el evento"></textarea>
            </div>

            <div class="mb-3">
              <label for="fechaHoraInicio" class="form-label">Fecha y hora de inicio</label>
              <input class="form-control" type="datetime-local" id="fechaHoraInicio">
            </div>

            <div class="mb-3">
              <label for="fechaHoraFin" class="form-label">Fecha y hora de finalización</label>
              <input class="form-control" type="datetime-local" id="fechaHoraFin">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" onclick="guardarEventos()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  `);

  // Lanzar el modal
  $("#btnModal").click();
}

function guardarEventos(modo = "crear") {
  var titulo = $("#tituloEvento").val()
  var descripcion = $("#descripcionEvento").val()
  var tipo = $("#tipoEvento").val()
  var fecha_hora_inicio = $("#fechaHoraInicio").val()
  var fecha_hora_fin = $("#fechaHoraFin").val()
  var files = $("#imagen")[0]?.files;
  var file = files?.length > 0 ? files[0] : undefined;

  if (titulo == "" || descripcion == "" || tipo == "" || fecha_hora_inicio == "" || fecha_hora_fin == "") {
    SweetAlert("error", "¡Campos obligatorios!");
    return;
  }

  if (!file) {
    SweetAlert("error", "Imagen (solo en creación) es obligatoria!");
    return;
  }

  saveFiles(file).then(function (file) {
    $.post(
      "../api/v1/fulmuv/eventos/create",
      {
        titulo: titulo,
        descripcion: descripcion,
        tipo: tipo,
        fecha_hora_fin: fecha_hora_fin,
        fecha_hora_inicio: fecha_hora_inicio,
        imagen: file.img,
      },
      function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (!returned.error) {
          SweetAlert("url_success", returned.msg, "eventos.php");
        } else {
          SweetAlert("error", returned.msg);
        }
      }
    );
  });
}

function saveFiles(file) {
  return new Promise(function (resolve, reject) {
    const formData = new FormData();
    formData.append("archivos[]", file);
    $.ajax({
      type: "POST",
      data: formData,
      url: "cargar_imagen.php",
      cache: false,
      contentType: false,
      processData: false,
      success: function (returnedImagen) {
        if (returnedImagen.response === "success") {
          resolve(returnedImagen.data);
        } else {
          SweetAlert("error", "Error al guardar archivo: " + returnedImagen.error);
          reject();
        }
      },
    });
  });
}

function EventosById(eventos) {
  $.get("../api/v1/fulmuv/eventos/" + eventos, function (returnedData) {
    if (!returnedData.error) {
      let evento = returnedData.data;
      imagenActual = returnedData.data.imagen

      $("#staticBackdrop").remove();
      $("#alert").text("");
      $("#alert").append(`
        <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>

        <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0">
              <div class="modal-header bg-light">
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Actualizar Eventos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body p-4">
                <div class="mb-3">
                  <label for="formFile" class="form-label">Imagen del Evento</label>
                  <input class="form-control" type="file" id="imagen">
                  

                  <div class="mt-2">
                    <img src="${evento.imagen}" alt="Imagen actual" class="img-fluid rounded" style="max-height: 150px;">
                  </div>
                </div>

                <div class="mb-3">
                  <label for="tipoEvento" class="form-label">Tipo de Evento</label>
                  <select class="form-select" id="tipoEvento">
                    <option value="">Seleccione tipo</option>
                    <option value="Conferencia" ${evento.tipo === 'Conferencia' ? 'selected' : ''}>Conferencia</option>
                    <option value="Seminario" ${evento.tipo === 'Seminario' ? 'selected' : ''}>Seminario</option>
                    <option value="Feria" ${evento.tipo === 'Feria' ? 'selected' : ''}>Feria</option>
                    <option value="Festival" ${evento.tipo === 'Festival' ? 'selected' : ''}>Festival</option>
                    <option value="Taller" ${evento.tipo === 'Taller' ? 'selected' : ''}>Taller</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="tituloEvento" class="form-label">Título del Evento</label>
                  <input class="form-control" type="text" id="tituloEvento" value="${evento.titulo}" placeholder="Ingrese el título">
                </div>

                <div class="mb-3">
                  <label for="descripcionEvento" class="form-label">Descripción</label>
                  <textarea class="form-control" id="descripcionEvento" rows="5" placeholder="Describa brevemente el evento">${evento.descripcion}</textarea>
                </div>

                <div class="mb-3">
                  <label for="fechaHoraInicio" class="form-label">Fecha y hora de inicio</label>
                  <input class="form-control" type="datetime-local" id="fechaHoraInicio" value="${evento.fecha_hora_inicio.replace(' ', 'T')}">
                </div>

                <div class="mb-3">
                  <label for="fechaHoraFin" class="form-label">Fecha y hora de finalización</label>
                  <input class="form-control" type="datetime-local" id="fechaHoraFin" value="${evento.fecha_hora_fin.replace(' ', 'T')}">
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="editEventos(${evento.id_evento})">Guardar</button>
              </div>
            </div>
          </div>
        </div>
      `);

      // Mostrar el modal
      $("#btnModal").click();
    }
  }, "json");
}

function editEventos(id_eventos) {
  const titulo = $("#tituloEvento").val();
  const descripcion = $("#descripcionEvento").val();
  const tipo = $("#tipoEvento").val();
  const fecha_hora_inicio = $("#fechaHoraInicio").val();
  const fecha_hora_fin = $("#fechaHoraFin").val();
  const files = $("#imagen")[0]?.files || [];
  const file = files.length > 0 ? files[0] : undefined;

  if (titulo == "" || descripcion == "" || tipo == "" || fecha_hora_inicio == "" || fecha_hora_fin == "") {
    SweetAlert("error", "¡Campos obligatorios!");
    return;
  }

  if (!file) {
    SweetAlert("error", "Imagen (solo en creación) es obligatoria!");
    return;
  }

  saveFiles(file).then(function (file) {
    $.post(
      "../api/v1/fulmuv/eventos/update",
      {
        id: id_eventos,
        titulo: titulo,
        descripcion: descripcion,
        tipo: tipo,
        fecha_hora_fin: fecha_hora_fin,
        fecha_hora_inicio: fecha_hora_inicio,
        imagen: file.img,
      },
      function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (!returned.error) {
          SweetAlert("url_success", returned.msg, "eventos.php");
        } else {
          SweetAlert("error", returned.msg);
        }
      }
    );
  });
}