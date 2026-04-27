$(document).ready(function () {

  $.get('../api/v1/fulmuv/atributos/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_categorias").text("");
      returned.data.forEach(categoria => {
        $("#lista_categorias").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium">${categoria.nombre}</td>
            <td class="py-2 align-middle fs-9 fw-medium">${categoria.tipo_dato}</td>
            <td class="py-2 align-middle fs-9 fw-medium">${categoria.opciones != null ? categoria.opciones : ''}</td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                  <div class="py-2">
                    ${categoria.tipo_dato == "OPCIONES" ? '<a class="dropdown-item text-info" onclick="editarOpciones('+categoria.id_atributo+')">Editar Opciones</a>' : ''}
                    <a class="dropdown-item text-danger" onclick="remove(${categoria.id_atributo}, 'atributos')">Eliminar</a>
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

function editarOpciones(id_atributo){
  $.get('../api/v1/fulmuv/getAtributoById/' + id_atributo, {}, function (returnedData) {
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar Opciones</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-12 mb-2">
                      <label class="form-label" for="nombre">Opciones:</label>
                      <input class="form-control" id="opciones" type="text" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-iso" type="button" onclick="updateAtributo(${id_atributo})">Actualizar</button>
              </div>
            </div>
          </div>
      `);
      
      
      tagsInput = new Choices('#opciones', {
        removeItemButton: true,
        placeholder: false,
        //items: (returned.data.opciones != "" && returned.data.opciones != null && returned.data.opciones.split(",").length) ? returned.data.opciones.split(",") : [],
        //maxItemCount: 3,
        items: (returned.data.opciones != "" && returned.data.opciones != null) ? JSON.parse(returned.data.opciones) : [],

        addItemText: (value) => {
          return `Presiona Enter para añadir <b>"${value}"</b>`;
        },
        // maxItemText: (maxItemCount) => {
        //   return `Solo ${maxItemCount} tags pueden ser añadidos`;
        // },
      });
      $("#btnModal").click();
    }
  });
}

function updateAtributo(id_atributo) {
  var tags = tagsInput.getValue(true);
  tags = tags.map(tag => tag.toUpperCase());
  swal({
    title: "Alerta",
    text: "¿Deseas actualizar las opciones?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    
    $.post('../api/v1/fulmuv/updateAtributo', {
      id_atributo: id_atributo,
      opciones: tags
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "atributos.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
    
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
        SweetAlert("url_success", returned.msg, "categorias.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}
