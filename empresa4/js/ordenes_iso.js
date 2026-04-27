var id_usuario = $("#id_principal").val()

$(document).ready(function () {

  $.post('../api/v1/fulmuv/ordenes_iso/', {
    id_principal: id_usuario,
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_ordenes").text("");
      returned.data.forEach(orden => {

        estado = ""
        opciones = ` 
        <a class='dropdown-item' onclick="showNotes(${orden.id_orden_iso})">Registro de Actividad</a>
        <a class='dropdown-item' href='orden_iso_detalle.php?id_orden_iso=${orden.id_orden_iso}'>Ver Detalle</a>
        `
        switch (orden.orden_estado) {
          case "procesada":
            estado = "<span class='badge badge rounded-pill badge-subtle-primary text-capitalize'>procesada<span class='ms-1 fas fas fa-cogs' data-fa-transform='shrink-2'></span></span>"
            opciones += `
                        <a class="dropdown-item" onclick="updateEstado('enviada', [${orden.id_orden_iso}], '${orden.orden_estado}')">Enviar</a>`
            /* opciones += `
                        <a class="dropdown-item" onclick="updateEstado('enviada', [${orden.id_orden_iso}], '${orden.orden_estado}')">Enviar</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" onclick="deleteOrden([${orden.id_orden_iso}])">Eliminar</a>` */
            break;
          case "enviada":
            estado = "<span class='badge badge rounded-pill badge-subtle-info text-capitalize'>enviada<span class='ms-1 fas fas fa-truck' data-fa-transform='shrink-2'></span></span>"
            opciones += `
                        <a class="dropdown-item" onclick="updateEstado('completada', [${orden.id_orden_iso}], '${orden.orden_estado}')">Completar</a>`
            break;
          case "completada":
            estado = "<span class='badge badge rounded-pill badge-subtle-success text-capitalize'>completada<span class='ms-1 fas fas fa-check' data-fa-transform='shrink-2'></span></span>"
            break;
          default:
            break;
        }

        empresas = ''

        orden.ordenes.forEach(ord => {
          empresas += `
                <div class="position-relative me-2">
                  <h6 class="mb-1 fw-semi-bold text-nowrap"><a class="text-900 stretched-link"  href='orden_detalle.php?id_orden=${ord.id_orden}'>${ord.empresa}</a></h6>
                  <p class="fw-semi-bold mb-0 text-500">${ord.sucursal}</p>
                </div>`
        });

        $("#lista_ordenes").append(`
          <tr class="btn-reveal-trigger">
            <td class="align-middle" style="width: 28px;">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="number-pagination-item-${orden.id_orden_iso}" data-id="${orden.id_orden_iso}" data-estado="${orden.orden_estado}" data-bulk-select-row="data-bulk-select-row" />
              </div>
            </td>
            <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="orden_iso_detalle.php?id_orden_iso=${orden.id_orden_iso}">#${orden.id_orden_iso}</a></td>

            <td class="date py-2 align-middle">${orden.created_at}</td>
            <td class="align-left d-flex ">
              ${empresas}
            </td>
            <td class="align-middle text-center fs-9 white-space-nowrap payment">
                ${estado}
            </td>
            <td class="align-middle text-end">$${orden.total}</td>
            <td class="align-middle white-space-nowrap text-end">
              <div class="dropstart font-sans-serif position-static d-inline-block">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-number-pagination-table-item-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-number-pagination-table-item-0">
                  ${opciones}
                </div>
              </div>
            </td>
          </tr>
        `);
      });

      options = {
        'responsive': false,
        'lengthChange': false,
        'searching': true,
        'pageLength': 10, 'info': true,
        'language': {
          'url': 'http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
          'paginate': {
            'next': '<span class=\'fas fa-chevron-right\'></span>',
            'previous': '<span class=\'fas fa-chevron-left\'></span>'
          }
        }
      }

      $("#my_table").attr("data-datatables", JSON.stringify(options));
      $("#checkbox-bulk-table-item-select").attr("data-bulk-select", '{"body":"lista_ordenes","actions":"table-number-pagination-actions","replacedElement":"table-number-pagination-replace-element"}');
      dataTablesInit()
      bulkSelectInit()
    }
  });
});

function showNotes(id_orden) {
  $("#notesLog").empty()
  $("#div_submit").empty()
  $("#show_notes_modal").modal("show")

  $.get('../api/v1/fulmuv/ordenes_iso/' + id_orden + '/notas', {}, function (returnedData) {
    notesOrdenData = JSON.parse(returnedData);
    if (notesOrdenData.error == false) {
      $("#notesLog").empty()
      if (notesOrdenData.data.length) {
        notesOrdenData.data.forEach(function (note) {
          $("#notesLog").append(`
                      <div class="row g-3 timeline timeline-primary timeline-past pb-x1">
                        <div class="col-auto ps-4 ms-2">
                            <div class="ps-2">
                              <div class="avatar avatar-2xl">
                                <img class="rounded-circle" src="${note.imagen}" alt="">
                              </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row gx-0 border-bottom pb-x1">
                                <div class="col">
                                    <h6 class="text-800 mb-1">${note.usuario}</h6>
                                    <p class="fs-10 text-600 mb-0">${note.accion}</p>
                                </div>
                                <div class="col-auto">
                                    <p class="fs-11 text-500 mb-0">${note.created_at}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                      `)
        })
      } else {
        $("#notesLog").append(`
                  Nada que mostrar
              `)
      }
      $("#div_submit").append(`<button type="button" onclick="createOrdenNota(${id_orden})" class="btn btn-iso"><i class='uil uil-message me-1'></i>Enviar</button>`)
      $("#show_notes_modal").modal("show")
      // Desplazar el scroll al fondo una vez que el modal esté completamente mostrado
      $('#show_notes_modal').on('shown.bs.modal', function () {
        $("#notesLog").scrollTop($("#notesLog")[0].scrollHeight);
      });
    } else {
      SweetAlert("error", notesPayment.msg);
    }
  });
}

function createOrdenNota(id_orden) {

  if ($("#comment").val() != "") {
    $.post('../api/v1/fulmuv/ordenes_iso/' + id_orden + '/notas/create', {
      id_orden: id_orden,
      accion: $("#comment").val(),
      id_usuario: id_usuario

    }, function (returnedData) {
      returned = JSON.parse(returnedData);
      if (returned["error"] == false) {
        const date = new Date();
        const options = {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
          hour: 'numeric',
          minute: 'numeric',
          hour12: true
        };
        const formattedDate = new Intl.DateTimeFormat('en-US', options).format(date);
        $("#notesLog").append(`
              <div class="row g-3 timeline timeline-primary timeline-past pb-x1">
                        <div class="col-auto ps-4 ms-2">
                            <div class="ps-2">
                              <div class="avatar avatar-2xl">
                                <img class="rounded-circle" src="${$("#imagen_principal").val()}" alt="">
                              </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row gx-0 border-bottom pb-x1">
                                <div class="col">
                                    <h6 class="text-800 mb-1">Tú</h6>
                                    <p class="fs-10 text-600 mb-0">${$("#comment").val()}</p>
                                </div>
                                <div class="col-auto">
                                    <p class="fs-11 text-500 mb-0">${formattedDate}</p>
                                </div>
                            </div>
                        </div>
                    </div>
              `)
        $("#comment").val("")
        $("#notesLog").scrollTop($("#notesLog")[0].scrollHeight);
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  } else {
    SweetAlert("error", "Por favor ingrese un comentario!!");
  }
}

function updateEstado(orden_estado, id_orden_iso, estado) {
  if (orden_estado != estado) {
    $.post('../api/v1/fulmuv/ordenes_iso/updateEstado', {
      id_orden_iso: id_orden_iso,
      id_usuario: id_usuario,
      orden_estado: orden_estado
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes_iso.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  } else {
    SweetAlert("warning", "La orden tiene el estado igual al estado que estas intentando actualizar.");
    return;
  }
}

function updateEstadoBulk() {
  var orden_estado = $("#orden_estado").val()
  // Obtener las filas seleccionadas
  var selectedRows = [];
  $('#lista_ordenes input[type="checkbox"]:checked').each(function () {
    var id = $(this).data('id');
    var estado = $(this).data('estado');
    selectedRows.push({ id, estado });
  });

  // Verificar si hay filas seleccionadas
  if (selectedRows.length) {

    // Filtrar las filas donde el estado este "completada"
    var completadaCount = selectedRows.filter(function (rowData) {
      return rowData.estado === 'completada';
    }).length;

    if (completadaCount == 0) {

      var sameStatus = selectedRows.filter(function (rowData) {
        return rowData.estado === orden_estado;
      }).length;

      if (sameStatus == 0) {

        switch (orden_estado) {
          /* case "eliminada":
  
            // Validar que no existan órdenes en estado "procesada" o "enviada"
            var invalidStates = selectedRows.filter(function (rowData) {
              return rowData.estado === 'procesada' || rowData.estado === 'enviada';
            }).length;
  
            if (invalidStates === 0) {
              selectedRows = selectedRows.map(orden => orden.id)
              deleteOrden(selectedRows);
            } else {
              SweetAlert("warning", "Hay al menos una orden en estado 'procesada' o 'enviada'. No se puede eliminar.");
            }
  
            break; */
          case "enviada":

            // Validar que todas las órdenes sean estado "creada"
            var procesadaStateCount = selectedRows.filter(function (rowData) {
              return rowData.estado === 'procesada';
            }).length;

            if (procesadaStateCount === selectedRows.length) {

              selectedRows = selectedRows.map(orden => orden.id)
              $.post('../api/v1/fulmuv/ordenes_iso/updateEstado', {
                id_orden_iso: selectedRows,
                id_usuario: id_usuario,
                orden_estado: orden_estado
              }, function (returnedData) {
                var returned = JSON.parse(returnedData);
                if (returned.error === false) {
                  SweetAlert("url_success", returned.msg, "ordenes_iso.php");
                } else {
                  SweetAlert("error", returned.msg);
                }
              });

            } else {
              SweetAlert("warning", "Todas las órdenes deben estar en estado 'procesada' para poder ser enviadas.");
            }

            break;
          case "completada":
            // validar que las órdenes estén en estado "aprobada" antes de procesarlas.

            var enviadaStateCount = selectedRows.filter(function (rowData) {
              return rowData.estado === 'enviada';
            }).length;

            if (enviadaStateCount === selectedRows.length) {

              selectedRows = selectedRows.map(orden => orden.id)
              $.post('../api/v1/fulmuv/ordenes_iso/updateEstado', {
                id_orden_iso: selectedRows,
                id_usuario: id_usuario,
                orden_estado: orden_estado
              }, function (returnedData) {
                var returned = JSON.parse(returnedData);
                if (returned.error === false) {
                  SweetAlert("url_success", returned.msg, "ordenes_iso.php");
                } else {
                  SweetAlert("error", returned.msg);
                }
              });

            } else {
              SweetAlert("warning", "Todas las órdenes deben estar en estado 'enviada' para poder ser completadas.");
            }
            break;

          default:
            break;
        }

      } else {
        SweetAlert("warning", "Hay almenos una orden con el estado igual al estado que estas intentando actualizar.");
      }
    } else {
      SweetAlert("warning", "Hay almenos una orden con el estado 'completada'. El estado no se puede actualizar.");
    }

  } else {
    SweetAlert("warning", "Seleccione almenos una fila.");
    return;
  }
}

/* $(document).ready(function () {

  $.post('../api/v1/fulmuv/ordenes/', {
    id_principal: $("#id_principal").val(),
    id_empresa: $("#id_empresa").val()
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_ordenes").text("");

      returned.data.forEach(orden => {

        estado = ""
        opciones = `<a class='dropdown-item' href=''>Ver Detalle</a>`
        switch (orden.orden_estado) {
          case "creada":
            estado = "<span class='badge badge rounded-pill badge-subtle-secondary text-capitalize'>creada<span class='ms-1 fas fa-shopping-cart' data-fa-transform='shrink-2'></span></span>"
            opciones += `
                  <a class="dropdown-item" onclick="updateEstado('aprobada', [${orden.id_orden}], '${orden.orden_estado}')">Aprobar</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" onclick="deleteOrden([${orden.id_orden}])">Eliminar</a>`
            break;
          case "aprobada":
            estado = "<span class='badge badge rounded-pill badge-subtle-warning text-capitalize'>aprobada<span class='ms-1 fas fa-user-check' data-fa-transform='shrink-2'></span></span>"
            opciones += `
                  <a class="dropdown-item" onclick="updateEstado('procesada', [${orden.id_orden}], '${orden.orden_estado}')">Procesar</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" onclick="deleteOrden([${orden.id_orden}])">Eliminar</a>`
            break;
          case "procesada":
            estado = "<span class='badge badge rounded-pill badge-subtle-primary text-capitalize'>procesada<span class='ms-1 fas fas fa-cogs' data-fa-transform='shrink-2'></span></span>"
            break;
          case "enviada":
            estado = "<span class='badge badge rounded-pill badge-subtle-info text-capitalize'>enviada<span class='ms-1 fas fas fa-truck' data-fa-transform='shrink-2'></span></span>"
            break;
          case "completada":
            estado = "<span class='badge badge rounded-pill badge-subtle-success text-capitalize'>completada<span class='ms-1 fas fas fa-check' data-fa-transform='shrink-2'></span></span>"
            break;
          default:
            break;
        }

        $("#lista_ordenes").append(`
          <tr class="btn-reveal-trigger">
            <td class="align-middle" style="width: 28px;">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="number-pagination-item-${orden.id_orden}" data-id="${orden.id_orden}" data-estado="${orden.orden_estado}" data-bulk-select-row="data-bulk-select-row" />
              </div>
            </td>
            <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="">#${orden.id_orden}</a></td>
            <td class="date py-2 align-middle">${orden.created_at}</td>
            <td class="align-middle white-space-nowrap product">
              ${orden.empresa}
              <p class="mb-0 text-500">${orden.sucursal}</p>
            </td>
            <td class="align-middle text-center fs-9 white-space-nowrap payment">
                ${estado}
            </td>
            <td class="align-middle text-end amount">$${orden.total}</td>
            <td class="align-middle white-space-nowrap text-end">
              <div class="dropstart font-sans-serif position-static d-inline-block">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-number-pagination-table-item-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-number-pagination-table-item-0">
                  ${opciones}
                </div>
              </div>
            </td>
          </tr>
        `);
      });

      options = {
        'responsive': false,
        'lengthChange': false,
        'searching': true,
        'pageLength': 10, 'info': true,
        'language': {
          'url': '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
          'paginate': {
            'next': '<span class=\'fas fa-chevron-right\'></span>',
            'previous': '<span class=\'fas fa-chevron-left\'></span>'
          }
        }
      }

      $("#my_table").attr("data-datatables", JSON.stringify(options));
      $("#checkbox-bulk-table-item-select").attr("data-bulk-select", '{"body":"lista_ordenes","actions":"table-number-pagination-actions","replacedElement":"table-number-pagination-replace-element"}');
      dataTablesInit()
      bulkSelectInit()
    }
  });
});

function updateEstadoBulk() {
  var orden_estado = $("#orden_estado").val()
  // Obtener las filas seleccionadas
  var selectedRows = [];
  $('#lista_ordenes input[type="checkbox"]:checked').each(function () {
    var id = $(this).data('id');
    var estado = $(this).data('estado');
    selectedRows.push({ id, estado });
  });

  // Verificar si hay filas seleccionadas
  if (selectedRows.length) {

    // Filtrar las filas donde el estado este "completada"
    var completadaCount = selectedRows.filter(function (rowData) {
      return rowData.estado === 'completada';
    }).length;

    if (completadaCount == 0) {

      var sameStatus = selectedRows.filter(function (rowData) {
        return rowData.estado === orden_estado;
      }).length;


      if (sameStatus == 0) {

        selectedRows = selectedRows.map(orden => orden.id)
        if (orden_estado === "eliminada") {
          deleteOrden(selectedRows)
        } else {
          $.post('../api/v1/fulmuv/ordenes/updateEstado', {
            id_orden: selectedRows,
            id_usuario: id_usuario,
            orden_estado: orden_estado
          }, function (returnedData) {
            var returned = JSON.parse(returnedData)
            if (returned["error"] == false) {
              SweetAlert("url_success", returned.msg, "ordenes_iso.php")
            } else {
              SweetAlert("error", returned.msg);
            }
          });
        }

      } else {
        SweetAlert("warning", "Hay almenos una orden con el estado igual al estado que estas intentando actualizar.");
        return;
      }
    } else {
      SweetAlert("warning", "Hay almenos una orden con el estado 'completada'. El estado no se puede actualizar.");
      return;
    }

  } else {
    SweetAlert("warning", "Seleccione almenos una fila.");
    return;
  }
}

function updateEstado(orden_estado, id_orden, estado) {
  if (orden_estado != estado) {
    $.post('../api/v1/fulmuv/ordenes/updateEstado', {
      id_orden: id_orden,
      id_usuario: id_usuario,
      orden_estado: orden_estado
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes_iso.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  } else {
    SweetAlert("warning", "La orden tiene el estado igual al estado que estas intentando actualizar.");
    return;
  }
}

function deleteOrden(id_orden) {
  swal({
    title: "Warning",
    text: `Esta seguro que quiere eliminar ${id_orden.length > 1 ? 'estos registros' : 'este registro'}?`,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#eb9bb2",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false,
    closeOnConfirm: true
  }, function () {
    $.post('../api/v1/fulmuv/ordenes/delete', {
      id_orden: id_orden,
      id_usuario: id_usuario
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes_iso.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  });
} */