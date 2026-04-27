$(document).ready(function () {

  $.get('../api/v1/fulmuv/modelosAutos/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_categorias").text("");
      returned.data.forEach(categoria => {
        $("#lista_categorias").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium">${categoria.nombre}</td>
            <td class="py-2 align-middle fs-9 fw-medium">${categoria.referencia}</td>
            <td class="py-2 align-middle fs-9 fw-medium"></td>
            <!--td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                  <div class="py-2">
                    <a class="dropdown-item text-danger" onclick="remove(${categoria.id_modelos_autos}, 'categorias')">Eliminar</a>
                  </div>
                </div>
              </div>
            </td-->
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
