$(document).ready(function () {
  $.get('../api/v1/fulmuv/roles/', {}, function (returnedData) {
    proyecto = JSON.parse(returnedData);
    if (proyecto["error"] == false) {
      proyecto['data'].forEach(function (member, i) {
        if (member["rol"] == "Owner") {
          $('#ownerId').append(`                        
                <div class="form-check mt-1 mb-1">
                    <input type="radio" class="form-check-input" id="${member["rol"]}" name="customRadio" data-id="${member["id_rol"]}" onclick="permisos(${member["id_rol"]}, '${member["rol"]}')" >
                    <label class="form-check-label" for="${member["rol"]}">
                        ${member["rol"]}
                    </label>
                </div> 
                `);
        } else {
          $('#roles').append(`                        
                <div class="form-check mt-1 mb-1">
                    <input type="radio" class="form-check-input" id="${member["id_rol"]}" name="customRadio" data-id="${member["id_rol"]}" onclick="permisos(${member["id_rol"]})" >
                    <label class="form-check-label" for="${member["id_rol"]}">
                        ${member["rol"]}
                    </label>
                </div> 
                `);
        }
      });
      $("#Owner").click();
    }
  });
});

function caja(){
  $('#add').hide();
  $('#cardRoles').append(`
      <input type="text" id="nameNew" class="form-control mt-2" placeholder="Add Name" data-id="">
      <div class="row mt-2" id="bContenedor" >
          <button type="button" class="btn btn-outline-success rounded-pill" onclick="guardar()"><i class=" ri-checkbox-circle-line"></i> Save </button>
          <button type="button" class="btn btn-outline-danger rounded-pill mt-1" onclick="deleteButon()"><i class="ri-delete-bin-line"></i> Cancel </button>
      </div>
  `);
}

function deleteButon(){
  $('#bContenedor').remove();
  $('#nameNew').remove();
  $('#add').show();
}

function guardar(){

  var plantilla = [];

  var nameRole = $('#nameNew').val();
  if(nameRole == ""){
      SweetAlert("error", "Name Role is a required field.");
  }else{
      plantilla.push({datos: "Empresas"});
  
      plantilla.push({datos: "Usuarios"});
  
      plantilla.push({datos: "Productos"});
  
      plantilla.push({datos: "Catalogos"});
  
      plantilla.push({datos: "E-mail"});
  
      plantilla.push({datos: "Ordenes"});

      plantilla.push({datos: "Dashboard"});

      plantilla.push({datos: "Roles"});

      plantilla.push({datos: "Membresias"});

      $.post('../api/v1/fulmuv/postPermisos/', {
          plantilla:  plantilla,
          nameRole:   nameRole,
          id_empresa: $("#id_empresa").val(),
      }, function(returnedData) {
          var returned = JSON.parse(returnedData)
          //console.log(returned);
          if (returned["error"] == false) {
              SweetAlert("url_success", returned["msg"], "roles.php");
          }else{
              SweetAlert("error", returned["msg"]);
          }
      });
      
      
  }
}

function permisos(id_rol, nombre) {

  if (nombre != "Owner") {
    var editable = false;
    $('#btn_editar').show();
    $('#btn_eliminar').show();
    $('#nameRole').show();
  } else {
    var editable = true;
    $('#btn_editar').hide();
    $('#btn_eliminar').hide();
    $('#nameRole').hide();
  }

  /* SOLO PINTA EL NOMBRE DEL ROL */
  $.get('../api/v1/fulmuv/roles/' + id_rol, {}, function (returnedData) {
    proyecto = JSON.parse(returnedData);
    if (proyecto.error == false) {
      $('#nameRole').val(proyecto.data.rol);
      //agregamos el id_rol
      var article = document.getElementById('nameRole');
      article.dataset.id = proyecto.data.id_rol;

    }
  });
  /* SOLO PINTA EL NOMBRE DEL ROL */



  /* TRAER PERMISOS DEL ROL */
  $.get('../api/v1/fulmuv/roles/' + id_rol + '/permisos', {}, function (returnedData) {
    proyecto = JSON.parse(returnedData);
    if (proyecto["error"] == false) {
      proyecto['data'].forEach(function (member) {

        if (member["valor"] == "true") {
          var valor = true;
        } else {
          var valor = false;
        }
        if (member["permiso"] == "Empresas") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          $('#select' + member["permiso"]).val(member["levels"]);
          document.getElementById('select' + member["permiso"]).disabled = editable;

          //se agrega data-set
          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];

        }

        if (member["permiso"] == "Usuarios") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          $('#select' + member["permiso"]).val(member["levels"]);
          document.getElementById('select' + member["permiso"]).disabled = editable;

          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

        if (member["permiso"] == "Productos") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

        if (member["permiso"] == "Catalogos") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          $('#select' + member["permiso"]).val(member["levels"]);
          document.getElementById('select' + member["permiso"]).disabled = editable;

          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

        if (member["permiso"] == "E-mail") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

        if (member["permiso"] == "Ordenes") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          $('#select' + member["permiso"]).val(member["levels"]);
          document.getElementById('select' + member["permiso"]).disabled = editable;

          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

        if (member["permiso"] == "Dashboard") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          $('#select' + member["permiso"]).val(member["levels"]);
          document.getElementById('select' + member["permiso"]).disabled = editable;

          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

        if (member["permiso"] == "Roles") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;
          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

        if (member["permiso"] == "Membresias") {
          $('#edition' + member["permiso"]).prop("checked", valor);
          document.getElementById('edition' + member["permiso"]).disabled = editable;

          $('#select' + member["permiso"]).val(member["levels"]);
          document.getElementById('select' + member["permiso"]).disabled = editable;

          var data = document.getElementById('edition' + member["permiso"]);
          data.dataset.idPermiso = member["id_permisos"];
        }

      });
    }
  });
  /* TRAER PERMISOS DEL ROL */
}

/* ACTUALIZAR AUTOMATICO */
function actualizarCampo(campo, idCheck) {
  var ayuda = 'edition' + idCheck;
  var nameRole = document.getElementById(ayuda);
  var id = nameRole.dataset.idPermiso;

  var valor = $('#edition' + idCheck).prop("checked");

  $.post('../api/v1/fulmuv/actualizaPermiso/', {
    id_role: id,
    nameRole: campo,
    valor: valor
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned["error"] == false) {
      toastr.success("Updated data");
    }
  });

}
/* ACTUALIZAR AUTOMATICO */

/* ACTUALIZAR SELECT */
function actualizarCampoSelect(campo, idCheck) {
  var ayuda = 'edition' + idCheck;
  var nameRole = document.getElementById(ayuda);
  var id = nameRole.dataset.idPermiso;

  var valor = $('#select' + idCheck).val();

  $.post('../api/v1/fulmuv/actualizaPermiso/', {
    id_role: id,
    nameRole: campo,
    valor: valor
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned["error"] == false) {
      toastr.success("Updated data");
    }
  });

}
/* ACTUALIZAR SELECT */