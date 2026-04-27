let empresas = [];

$(document).ready(function () {

  $.get('../api/v1/fulmuv/sucursales/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#tabla_sucursales").text("");
      returned.data.forEach(sucursal => {
        $("#tabla_sucursales").append(`
          <tr class="btn-reveal-trigger">
              <td class="name align-middle white-space-nowrap py-2">
                <h5 class="mb-0 fs-10">${sucursal.nombre}</h5>  
              </td>
              <td class="email align-middle py-2">${sucursal.direccion}</td>
              <td class="phone align-middle white-space-nowrap py-2">${sucursal.empresa}</td>
              <td class="address align-middle white-space-nowrap">${sucursal.created_at}</td>
              <td class="align-middle white-space-nowrap py-2 text-end">
                <div class="dropdown font-sans-serif position-static">
                  <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                  <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                    <div class="py-2">
                      <a class="dropdown-item" onclick="editSucursal(${sucursal.id_sucursal})">Editar</a>
                      <a class="dropdown-item text-danger" onclick="remove(${sucursal.id_sucursal},'sucursales')">Eliminar</a>
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

  $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      empresas = returned.data;
    }
  });

});

function addSucursal() {
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
              <h4 class="mb-1" id="staticBackdropLabel">Crear sucursal</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                  <input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()" />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Dirección</label>
                  <input class="form-control" id="direccion" type="text" placeholder="dirección" oninput="this.value = this.value.toUpperCase()" />
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Empresa</label>
                  <select class="form-select" id="empresa">
                    
                  </select>
                </div>
                <div class="col-12">
                  <button onclick="saveSucursal()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  
  $("#empresa").text("");
  empresas.forEach(empresa => {
    $("#empresa").append(`
      <option value="${empresa.id_empresa}">${empresa.nombre}</option>
    `);
  });
   
  $("#btnModal").click();
}

function saveSucursal(){
  var nombre = $("#nombre").val();
  var direccion = $("#direccion").val();
  var empresa = $("#empresa").val();
  if(nombre == "" || direccion == ""){
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  }else{
    $.post('../api/v1/fulmuv/sucursales/create', {
      nombre: nombre,
      direccion: direccion,
      id_empresa: empresa
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "sucursales.php")
      }else{
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function editSucursal(id_sucursal){
  $.get('../api/v1/fulmuv/sucursales/'+id_sucursal, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar sucursal</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                      <input class="form-control" id="nombre" type="text" placeholder="nombre" value="${returned.data.nombre}" oninput="this.value = this.value.toUpperCase()" />
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Dirección</label>
                      <input class="form-control" id="direccion" type="text" placeholder="dirección" value="${returned.data.direccion}" oninput="this.value = this.value.toUpperCase()" />
                    </div>
                    <!--div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Empresa</label>
                      <select class="form-select" id="empresa">
                        
                      </select>
                    </div-->
                    <div class="col-12">
                      <button onclick="updateSucursal(${id_sucursal})" class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      // empresas.forEach(empresa => {
      //   $("#empresa").append(`
      //     <option value="${empresa.id_empresa}">${empresa.nombre}</option>
      //   `);
      // });
      // $("#empresa").val(returned.data.id_empresa)
      $("#btnModal").click();
    }
  });
}

function updateSucursal(id_sucursal){
  var nombre = $("#nombre").val();
  var direccion = $("#direccion").val();
  if(nombre == "" || direccion == ""){
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  }else{
    $.post('../api/v1/fulmuv/sucursales/update', {
      id_sucursal: id_sucursal,
      nombre: nombre,
      direccion: direccion
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "sucursales.php")
      }else{
        SweetAlert("error", returned.msg)
      }
    });
  }
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
        SweetAlert("url_success", returned.msg, "sucursales.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}