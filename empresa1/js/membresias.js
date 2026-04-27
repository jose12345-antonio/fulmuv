let establecimientos = [];

$(document).ready(function () {

  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_membresias").text("");
      returned.data.forEach(membresia => {
        $("#lista_membresias").append(`
          <tr class="btn-reveal-trigger">
              <td class="name align-middle white-space-nowrap py-2">${membresia.nombre}</td>
              <td class="email align-middle py-2">${membresia.tipo}</td>
              <td class="address align-middle white-space-nowrap">${membresia.costo}</td>
              <td class="address align-middle white-space-nowrap">${membresia.numero}</td>
              <td class="address align-middle white-space-nowrap">${membresia.dias_permitidos}</td>
              <td class="align-middle white-space-nowrap py-2 text-end">
                <div class="dropdown font-sans-serif position-static">
                  <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                  <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                    <div class="py-2">
                      <a class="dropdown-item" onclick="editMembresia(${membresia.id_membresia})">Editar</a>
                      <a class="dropdown-item text-danger" onclick="remove(${membresia.id_membresia},'empresas')">Eliminar</a>
                    </div>
                  </div>
                </div>
              </td>
          </tr>
        `);
      });
      $("#my_table").DataTable({
        "searching": true,
        "responsive": false,
        "pageLength": 100,
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

});

function addMembresia() {
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
              <h4 class="mb-1" id="staticBackdropLabel">Crear membresía</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                  <input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()"/>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Tipo membresía</label>
                  <select class="form-select" id="tipo_establecimiento">
                    <option value="todos">Todos</option>
                    <option value="articulos">N° artículos</option>
                    <option value="servicios">Servicios</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Costo</label>
                  <input class="form-control" id="costo" type="text" placeholder="costo" oninput="this.value = this.value.toUpperCase()"/>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Días permitidos</label>
                  <input class="form-control" id="dias_permitidos" type="text" placeholder="dias permitidos" oninput="this.value = this.value.toUpperCase()"/>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">N° artículos</label>
                  <input class="form-control" id="numero" type="text" oninput="this.value = this.value.toUpperCase()"/>
                </div>
                <div class="col-12">
                  <button onclick="saveMembresia()" class="btn btn-primary" type="submit">Guardar</button>
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

function saveMembresia() {
  var nombre = $("#nombre").val();
  var tipo = $("#tipo_establecimiento").val();
  var costo = $("#costo").val();
  var dias_permitidos = $("#dias_permitidos").val();
  var numero = $("#numero").val();
  if (nombre == "" || numero == "" || costo == "" || dias_permitidos == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/membresias/create', {
      nombre: nombre,
      tipo: tipo,
      numero: numero,
      costo: costo,
      dias_permitidos: dias_permitidos
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "membresias.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function editMembresia(id_membresia) {
  $.get('../api/v1/fulmuv/membresias/' + id_membresia, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      membresiaData = returned.data
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar membresía</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                      <input class="form-control" id="nombre" type="text" placeholder="nombre" value="${membresiaData.nombre}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Tipo membresía</label>
                      <select class="form-select" id="tipo_establecimiento">
                        <option value="todos">Todos</option>
                        <option value="articulos">N° artículos</option>
                        <option value="servicios">Servicios</option>
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Costo</label>
                      <input class="form-control" id="costo" type="text" placeholder="costo" value="${membresiaData.costo}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Días permitidos</label>
                      <input class="form-control" id="dias_permitidos" type="text" placeholder="dias permitidos" value="${membresiaData.dias_permitidos}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-12 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">N° artículos</label>
                      <input class="form-control" id="numero" type="text" value="${membresiaData.numero}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-12">
                      <button onclick="updateMembresia(${id_membresia})" class="btn btn-iso" type="submit">Actualizar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      $("#tipo_establecimiento").val(membresiaData.tipo)
      var tipo = $("#tipo_establecimiento").val();
      if(tipo == "tiempo"){
        $("#fecha_inicio").val(membresiaData.fecha_inicio);
        $("#fecha_fin").val(membresiaData.fecha_fin);
        $("#contenidoArticulos").hide();
        $("#contenidoTiempo").show();
      }else if(tipo == "articulos"){
        $("#numero_articulos").val(membresiaData.limite_articulos);
        $("#contenidoTiempo").hide();
        $("#contenidoArticulos").show();
      }
      $("#btnModal").click();
    }
  });
}

function updateMembresia(id_membresia) {
  var nombre = $("#nombre").val();
  var tipo = $("#tipo_establecimiento").val();
  var costo = $("#costo").val();
  var dias_permitidos = $("#dias_permitidos").val();
  var numero = $("#numero").val();
  if (nombre == "" || numero == "" || costo == "" || dias_permitidos == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/membresias/update', {
      nombre: nombre,
      tipo: tipo,
      numero: numero,
      costo: costo,
      dias_permitidos: dias_permitidos,
      id_membresia: id_membresia
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "membresias.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    console.log(files)
    if (files == undefined) {
      resolve(); // Resuelve la promesa incluso si no hay imágenes
    } else {
      const formData = new FormData();
      formData.append(`archivos[]`, files); // añadrir los archivos al form
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
        SweetAlert("url_success", returned.msg, "empresas.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}