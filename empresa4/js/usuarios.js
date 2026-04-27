let roles = [];
let empresas = [];
let sucursales = [];

$(document).ready(function () {

  $.post('../api/v1/fulmuv/usuarios/', {
    id_principal: $("#id_principal").val(),
    id_empresa: $("#id_empresa").val()
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_usuarios").text("");
      returned.data.forEach(usuario => {
        $("#lista_usuarios").append(`
          <tr class="btn-reveal-trigger">
              <td class="name align-middle white-space-nowrap py-2">
                <h5 class="mb-0 fs-10">${usuario.nombres}</h5>  
              </td>
              <td class="align-middle py-2">${usuario.nombre_usuario}</td>
              <td class="align-middle py-2">${usuario.correo}</td>
              <td class="align-middle white-space-nowrap py-2">${usuario.nombre_empresa}</td>
              <td class="align-middle white-space-nowrap">${usuario.rol}</td>
              <td class="align-middle white-space-nowrap">${usuario.created_at}</td>
              <td class="align-middle white-space-nowrap py-2 text-end">
                <div class="dropdown font-sans-serif position-static">
                  <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                  <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                    <div class="py-2">
                      <a class="dropdown-item" onclick="resetPass(${usuario.id_usuario})">Resetear Contraseña</a>
                      ${ usuario.rol != 'Owner' ? `<a class="dropdown-item" onclick="editUsuario(${usuario.id_usuario})">Editar</a>
                      <a class="dropdown-item text-danger" onclick="remove(${usuario.id_usuario},'usuarios')">Eliminar</a>`:''}
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
        // "language": {
        //   "url": "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
        //   "paginate": {
        //     "next": "<span class=\"fas fa-chevron-right\"></span>",
        //     "previous": "<span class=\"fas fa-chevron-left\"></span>"
        //   }
        // },
        "dom": "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" + "<'table-responsive scrollbar'tr>" + "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>"
      })
    }
  });

  $.get('../api/v1/fulmuv/roles/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      roles = returned.data;
      if ($("#nombre_rol_principal").val() === "Owner") {
        roles = roles;
      } else if ($("#nombre_rol_principal").val() === "Admin") {
        roles = roles.filter(rol => rol.rol === "Admin" || rol.rol === "Manager");
      } else if ($("#nombre_rol_principal").val() === "Manager") {
        roles = roles.filter(rol => rol.rol === "Manager");
      }
    }
  });

  $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      empresas = returned.data;
      if ($("#nombre_rol_principal").val() === "Owner") {
        empresas = empresas;
      } else if ($("#nombre_rol_principal").val() === "Admin") {
        empresas = empresas.filter(empresa => empresa.id_empresa == $("#id_empresa").val());
      }
    }
  });

  $.get('../api/v1/fulmuv/sucursales/all', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      sucursales = returned.data;
      console.log(sucursales)
      if ($("#nombre_rol_principal").val() === "Owner") {
        sucursales = sucursales;
      } else if ($("#nombre_rol_principal").val() === "Admin") {
        sucursales = sucursales.filter(sucursal => sucursal.id_empresa == $("#id_empresa").val());
      } else if ($("#nombre_rol_principal").val() === "Manager") {
        sucursales = sucursales.filter(sucursal => sucursal.id_sucursal == $("#id_empresa").val());
      }
    }
  });
});

function asignarMembresia(id_usuario){
  $.get('../api/v1/fulmuv/usuarios/'+id_usuario, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#alert").text("");
      $("#alert").append(`
        <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
        <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal mt-6" role="document">
            <div class="modal-content border-0">
              <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body p-0">
                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                  <h4 class="mb-1" id="staticBackdropLabel">Asignar membresía</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-12 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombres</label>
                      <input class="form-control" id="nombres" type="text" placeholder="nombres" value="${returned.data.nombres}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    .
                    <div class="col-lg-6 mb-3">
                      <label class="form-label" for="nombre">Rol</label>
                      <select class="form-select" id="roles" onchange="verTipo()">
                    
                      </select>
                    </div>
                    <div class="col-lg-6 mb-3">
                      <label class="form-label" for="nombre">Empresa/Sucursal</label>
                      <select class="form-select" id="tipo">

                      </select>
                    </div>
                    <div class="col-12">
                      <button onclick="saveMembresia(${id_usuario})" class="btn btn-primary" type="submit">Asignar</button>
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
  });
  
}

function addUsuario() {
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
              <h4 class="mb-1" id="staticBackdropLabel">Crear usuario</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-lg-12 mb-3">
                  <label class="form-label" for="nombre">Nombres</label>
                  <input class="form-control" id="nombres" type="text" placeholder="Nombres" oninput="this.value = this.value.toUpperCase()" />
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Correo</label>
                  <input class="form-control" id="correo" type="text" placeholder="Correo" />
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Nombre de usuario</label>
                  <input class="form-control" id="nombre_usuario" type="text" placeholder="Nombre de usuario" />
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Rol</label>
                  <select class="form-select" id="roles" onchange="verTipo()">
                
                  </select>
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Empresa/Sucursal</label>
                  <select class="form-select" id="tipo">
                  
                  </select>
                </div>
                <div class="col-12">
                  <button onclick="saveUsuario()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
  roles.forEach(function (rol, index) {
    if (rol.rol != "Owner") {
      if(rol.rol == "Admin"){
        rolNombre = "Empresa"
      }else{
        rolNombre = "Sucursal"
      }
      $("#roles").append(`
        <option value="${rol.id_rol}">${rolNombre}</option>
      `);
    }
  });

  $('#tipo').select2({
    dropdownParent: $('#staticBackdrop')
  });

  verTipo()
  $("#btnModal").click();
}

function saveUsuario() {
  var nombres = $("#nombres").val();
  var nombre_usuario = $("#nombre_usuario").val();
  var correo = $("#correo").val();
  var rol = $("#roles").val();
  var tipo = $("#tipo").val();
  if (nombres == "" || correo == "" || nombre_usuario == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/usuarios/create', {
      nombres: nombres,
      nombre_usuario: nombre_usuario,
      correo: correo,
      imagen: '../theme/public/assets/img/team/avatar.png',
      id: tipo,
      rol_id: rol,
      pass: ''
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "usuarios.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function editUsuario(id_usuario) {
  $.get('../api/v1/fulmuv/usuarios/' + id_usuario, {}, function (returnedData) {
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar usuario</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-12 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombres</label>
                      <input class="form-control" id="nombres" type="text" placeholder="nombres" value="${returned.data.nombres}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Correo</label>
                      <input class="form-control" id="correo" type="text" placeholder="correo" value="${returned.data.correo}" />
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre de usuario</label>
                      <input class="form-control" id="nombre_usuario" type="text" placeholder="nombre de usuario" value="${returned.data.nombre_usuario}" />
                    </div>
                    <div class="col-lg-6 mb-3">
                      <label class="form-label" for="nombre">Rol</label>
                      <select class="form-select" id="roles" onchange="verTipo()">
                    
                      </select>
                    </div>
                    <div class="col-lg-6 mb-3">
                      <label class="form-label" for="nombre">Empresa/Sucursal</label>
                      <select class="form-select" id="tipo">

                      </select>
                    </div>
                    <div class="col-12">
                      <button onclick="updateUsuario(${id_usuario})" class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      roles.forEach(function (rol, index) {
        if (rol.rol != "Owner") {
          $("#roles").append(`
            <option value="${rol.id_rol}">${rol.rol}</option>
          `);
        }
      });
      $('#tipo').select2({
        dropdownParent: $('#staticBackdrop')
      });

      $("#roles").val(returned.data.rol_id)
      verTipo();
      $("#btnModal").click();
    }
  });
}

function verTipo() {
  var rol = $("#roles option:selected").text();
  if (rol == "Empresa") {
    $("#tipo").text("")
    $("#tipo").append(`
      <option value="">Ninguno</option>
    `);
    empresas.forEach(function (empresa, index) {
      $("#tipo").append(`
        <option value="${empresa.id_empresa}">${empresa.nombre}</option>
      `);
    });
  } else if (rol == "Sucursal") {
    $("#tipo").text("")
    $("#tipo").append(`
      <option value="">Ninguno</option>
    `);
    sucursales.forEach(function (sucursal, index) {
      $("#tipo").append(`
        <option value="${sucursal.id_sucursal}">${sucursal.nombre}</option>
      `);
    });
  }
}

function updateUsuario(id_usuario) {
  var nombres = $("#nombres").val();
  var nombre_usuario = $("#nombre_usuario").val();
  var correo = $("#correo").val();
  var rol = $("#roles").val();
  var tipo = $("#tipo").val();
  if (nombres == "" || correo == "" || nombre_usuario == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/usuarios/update', {
      id_usuario: id_usuario,
      nombres: nombres,
      nombre_usuario: nombre_usuario,
      correo: correo,
      imagen: '',
      id: tipo,
      rol_id: rol
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "usuarios.php")
      } else {
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
        SweetAlert("url_success", returned.msg, "usuarios.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}

function resetPass(id_usuario){
  swal({
    title: "Alerta",
    text: "La contraseña se va a resetear. ¿Está seguro que desea continuar?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    $.post('../api/v1/fulmuv/admin/resetearPass', {
      id_usuario: id_usuario
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "usuarios.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}