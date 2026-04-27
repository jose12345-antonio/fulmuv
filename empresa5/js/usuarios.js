let roles = [];
let empresas = [];
let sucursales = [];
let usuariosTable = null;

function getInitials(nombre) {
  const safeNombre = (nombre || '').trim();
  if (!safeNombre) return 'U';
  return safeNombre
    .split(/\s+/)
    .slice(0, 2)
    .map(parte => parte.charAt(0).toUpperCase())
    .join('');
}

function renderUsuariosRows(usuarios) {
  if (!usuarios || usuarios.length === 0) {
    $("#lista_usuarios").html(`
      <tr>
        <td colspan="7" class="usuarios-empty-state">No hay usuarios registrados para este contexto.</td>
      </tr>
    `);
    return;
  }

  $("#lista_usuarios").html("");

  usuarios.forEach(usuario => {
    const initials = getInitials(usuario.nombres);
    const nombreEmpresa = usuario.nombre_empresa && usuario.nombre_empresa.trim() !== ''
      ? usuario.nombre_empresa
      : 'Sin asignar';
    const puedeEditar = usuario.rol != 'Owner';

    $("#lista_usuarios").append(`
      <tr>
        <td class="align-middle">
          <div class="usuarios-cell-name">
            <div class="usuarios-avatar">${initials}</div>
            <div>
              <div class="usuarios-name-title">${usuario.nombres}</div>
              <div class="usuarios-name-meta">ID #${usuario.id_usuario}</div>
            </div>
          </div>
        </td>
        <td class="align-middle fw-semi-bold text-800">${usuario.nombre_usuario}</td>
        <td class="align-middle text-700">${usuario.correo}</td>
        <td class="align-middle">
          <span class="usuarios-pill">
            <span class="fas fa-building"></span>
            ${nombreEmpresa}
          </span>
        </td>
        <td class="align-middle">
          <span class="usuarios-pill usuarios-role-pill">${usuario.rol}</span>
        </td>
        <td class="align-middle text-700">${usuario.created_at}</td>
        <td class="align-middle text-end">
          <div class="usuarios-actions">
            <button class="btn btn-tertiary border-300 btn-sm text-warning shadow-none usuarios-action-btn"
              type="button"
              onclick="resetPass(${usuario.id_usuario})"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Resetear contraseña">
              <span class="fas fa-key"></span>
            </button>
            ${puedeEditar ? `
              <button class="btn btn-tertiary border-300 btn-sm text-600 shadow-none usuarios-action-btn"
                type="button"
                onclick="editUsuario(${usuario.id_usuario})"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Editar usuario">
                <span class="fas fa-pen"></span>
              </button>
              <button class="btn btn-tertiary border-300 btn-sm text-danger shadow-none usuarios-action-btn"
                type="button"
                onclick="remove(${usuario.id_usuario},'usuarios')"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Eliminar usuario">
                <span class="fas fa-trash-alt"></span>
              </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `);
  });
}

function initUsuariosDataTable() {
  if ($.fn.DataTable.isDataTable('#my_table')) {
    $('#my_table').DataTable().destroy();
  }

  usuariosTable = $("#my_table").DataTable({
    searching: true,
    responsive: false,
    autoWidth: false,
    pageLength: 25,
    lengthChange: false,
    info: true,
    order: [[5, 'desc']],
    columnDefs: [
      { orderable: false, targets: 6 },
      { width: '22%', targets: 0 },
      { width: '18%', targets: 2 },
      { width: '16%', targets: 3 },
      { width: '14%', targets: 6 }
    ],
    language: {
      search: "",
      searchPlaceholder: "Buscar usuario, correo o rol",
      info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
      infoEmpty: "Mostrando 0 a 0 de 0 usuarios",
      zeroRecords: "No se encontraron usuarios con ese criterio",
      emptyTable: "No hay usuarios disponibles",
      paginate: {
        next: "<span class=\"fas fa-chevron-right\"></span>",
        previous: "<span class=\"fas fa-chevron-left\"></span>"
      }
    },
    dom: "<'row align-items-center g-3 mb-3'<'col-md-6'f><'col-md-6 text-md-end'>>" +
      "<'table-responsive scrollbar'tr>" +
      "<'row align-items-center g-3 pt-3'<'col-md-6'i><'col-md-6 d-flex justify-content-md-end'p>>",
    drawCallback: function () {
      $('[data-bs-toggle="tooltip"]').tooltip();
    }
  });
}

$(document).ready(function () {

  $.post('../api/v1/fulmuv/usuarios/', {
    id_principal: $("#id_principal").val(),
    id_empresa: $("#id_empresa").val()
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      renderUsuariosRows(returned.data);
      initUsuariosDataTable();
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
          const rolNombre = rol.rol === "Admin" ? "Empresa" : rol.rol === "Manager" ? "Sucursal" : rol.rol;
          $("#roles").append(`
            <option value="${rol.id_rol}">${rolNombre}</option>
          `);
        }
      });
      $('#tipo').select2({
        dropdownParent: $('#staticBackdrop')
      });

      $("#roles").val(returned.data.rol_id)
      verTipo();
      $("#tipo").val(returned.data.id).trigger('change');
      $("#btnModal").click();
    }
  });
}

function verTipo() {
  var rol = $("#roles option:selected").text();
  if (rol == "Empresa" || rol == "Admin") {
    $("#tipo").text("")
    $("#tipo").append(`
      <option value="">Ninguno</option>
    `);
    empresas.forEach(function (empresa, index) {
      $("#tipo").append(`
        <option value="${empresa.id_empresa}">${empresa.nombre}</option>
      `);
    });
  } else if (rol == "Sucursal" || rol == "Manager") {
    $("#tipo").text("")
    $("#tipo").append(`
      <option value="">Ninguno</option>
    `);
    sucursales.forEach(function (sucursal, index) {
      $("#tipo").append(`
        <option value="${sucursal.id_sucursal}">${sucursal.nombre}</option>
      `);
    });
  } else {
    $("#tipo").html(`<option value="">Ninguno</option>`);
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
