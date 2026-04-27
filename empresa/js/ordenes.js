var levels
var acceso
var id_usuario = $("#id_principal").val()
let ordenesTable = null;

function getEstadoClass(estado) {
  switch (estado) {
    case "creada": return "is-creada";
    case "aprobada": return "is-aprobada";
    case "procesada": return "is-procesada";
    case "enviada": return "is-enviada";
    case "completada": return "is-completada";
    case "eliminada": return "is-eliminada";
    default: return "is-creada";
  }
}

function getEstadoIcon(estado) {
  switch (estado) {
    case "creada": return "fa-shopping-cart";
    case "aprobada": return "fa-user-check";
    case "procesada": return "fa-cogs";
    case "enviada": return "fa-truck";
    case "completada": return "fa-check";
    case "eliminada": return "fa-trash-alt";
    default: return "fa-circle";
  }
}

function initOrdenesDataTable(permisoLevel) {
  if ($.fn.DataTable.isDataTable('#my_table')) {
    $('#my_table').DataTable().destroy();
  }

  ordenesTable = $("#my_table").DataTable({
    responsive: false,
    lengthChange: false,
    searching: true,
    autoWidth: false,
    pageLength: 10,
    info: true,
    order: [[1, 'desc']],
    columnDefs: [
      { orderable: false, targets: [0] },
      { visible: permisoLevel == "Sucursal" ? false : true, targets: [4] }
    ],
    language: {
      search: "",
      searchPlaceholder: "Buscar orden, cliente o empresa",
      info: "Mostrando _START_ a _END_ de _TOTAL_ órdenes",
      infoEmpty: "Mostrando 0 a 0 de 0 órdenes",
      zeroRecords: "No se encontraron órdenes con ese criterio",
      emptyTable: "No hay órdenes disponibles",
      paginate: {
        next: "<span class='fas fa-chevron-right'></span>",
        previous: "<span class='fas fa-chevron-left'></span>"
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

  $.get('../api/v1/fulmuv/getPermisosByUser/' + id_usuario, {}, function (returnedDat) {
    permisosData = JSON.parse(returnedDat);
    if (permisosData.error == false) {

      var permiso = permisosData.data.filter(permiso => (permiso.permiso == "Ordenes"))[0];
      if (permiso.valor == "true") {
        levels = permiso.levels

        switch (permiso.levels) {
          case "Fulmuv":
            // $("#orden_estado").append(`
            //   <option value="procesada">Procesar</option>
            // `);
            break;
          case "Empresa":
            $("#orden_estado").append(`
              <option value="aprobada">Aprobar</option>
              <option value="procesada">Procesar</option>
              <option value="enviada">Enviar</option>
              <option value="completada">Completar</option>
              <option value="eliminada">Eliminar</option>
            `);
            break;
          case "Sucursal":
            $("#orden_estado").append(`
              <option value="aprobada">Aprobar</option>
              <option value="procesada">Procesar</option>
              <option value="enviada">Enviar</option>
              <option value="completada">Completar</option>
              <option value="eliminada">Eliminar</option>
            `);
            break;
          default:
            break;
        }

        $.post('../api/v1/fulmuv/ordenes/', {
          id_principal: id_usuario,
          id_empresa: $("#id_empresa").val()
        }, function (returnedData) {
          var returned = JSON.parse(returnedData)
          if (returned.error == false) {

            $("#lista_ordenes").text("");
            returned.data.forEach(orden => {
              var estado = ""
              var opciones = ""
              var opciones_estados = ""
              var boton_confirma = "";
              const tieneGuia = Array.isArray(orden.guia) && orden.guia.length > 0;
              if(orden.estado_venta == 0){
                boton_confirma = `<button class="btn btn-success text-white btn-sm" type="button" onclick="confirmarPago(${orden.id_orden})">Confirmar pago</button>`;
              }else if(orden.estado_venta == 1){
                boton_confirma = `<button class="btn btn-info text-white btn-sm" type="button" onclick="procesarOrden(${orden.id_orden})">Confirmar envío</button>`;
              }else if (orden.estado_venta === 3 && tieneGuia) {
                boton_confirma = `<button class="btn btn-info text-white btn-sm" type="button" onclick="verGuia(${orden.id_orden})">Ver guía</button>`;
              }

              switch (orden.orden_estado) {
                case "creada":
                  estado = "creada"
                  break;
                case "aprobada":
                  estado = "aprobada"
                  break;
                case "procesada":
                  estado = "procesada"
                  break;
                case "enviada":
                  estado = "enviada"
                  break;
                case "completada":
                  estado = "completada"
                  break;
                default:
                  break;
              }

              const badgeEntrega = orden.envio_domicilio == 1
                ? `<span class="badge bg-success mt-1">Retiro en empresa</span>`
                : `<span class="badge bg-primary mt-1">Envío a domicilio</span>`;
              const badgePago = orden.estado_venta == 0
                ? `<span class="badge bg-warning text-dark mt-1">Pago pendiente</span>`
                : (orden.estado_venta == 2
                  ? `<span class="badge bg-info mt-1">Pago confirmado</span>`
                  : ``);

              const clienteNombre = `${orden.cliente_nombres ?? ''} ${orden.cliente_apellidos ?? ''}`.trim();
              const clienteCedula = orden.cliente_cedula ? `<div class="text-500">CI/RUC: ${orden.cliente_cedula}</div>` : '';
              const clienteTelefono = orden.cliente_telefono ? `<div class="text-500">Tel: ${orden.cliente_telefono}</div>` : '';
              const clienteCorreo = orden.cliente_correo ? `<div class="text-500">${orden.cliente_correo}</div>` : '';
              const estadoBadge = `<span class="ordenes-status-badge ${getEstadoClass(orden.orden_estado)}"><span class="fas ${getEstadoIcon(orden.orden_estado)}"></span>${estado}</span>`;

              $("#lista_ordenes").append(`
                <tr>
                  <td class="align-middle white-space-nowrap text-end">
                    <div class="ordenes-icon-actions">
                      <button class="btn btn-tertiary border-300 btn-sm text-600 shadow-none ordenes-icon-btn"
                        type="button"
                        onclick="showNotes(${orden.id_orden})"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Registro de actividad">
                        <span class="fas fa-stream"></span>
                      </button>
                      <a class="btn btn-tertiary border-300 btn-sm text-600 shadow-none ordenes-icon-btn"
                        href="orden_detalle.php?id_orden=${orden.id_orden}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Ver detalle">
                        <span class="fas fa-eye"></span>
                      </a>
                      <button class="btn btn-tertiary border-300 btn-sm text-danger shadow-none ordenes-icon-btn"
                        type="button"
                        onclick="deleteOrden([${orden.id_orden}])"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Eliminar orden">
                        <span class="fas fa-trash-alt"></span>
                      </button>
                    </div>
                  </td>
                  <td class="align-middle white-space-nowrap text-700">${orden.created_at}</td>
                  <td class="align-middle">
                    <div class="ordenes-client-name">${clienteNombre || '-'}</div>
                    <div class="ordenes-client-meta">
                      ${clienteCedula}
                      ${clienteTelefono}
                      ${clienteCorreo}
                    </div>
                  </td>
                  <td class="align-middle">
                    <div class="ordenes-company-name">${orden.empresa}</div>
                    <div class="ordenes-company-meta">${orden.sucursal}</div>
                    ${badgeEntrega}
                  </td>
                  <td class="align-middle amount"><span class="ordenes-total">$${orden.total}</span></td>
                  <td class="align-middle white-space-nowrap text-end">
                    <div class="ordenes-action-cluster">
                      ${estadoBadge}
                      ${badgePago}
                    </div>
                    <div class="ordenes-action-cluster mt-2">
                      ${boton_confirma}
                    </div>
                  </td>
                </tr>
              `);
            });
            initOrdenesDataTable(permiso.levels)
          }

        });
      }
    }
  });
});

function updateEstado(orden_estado, id_orden, estado) {
  if (orden_estado != estado) {
    $.post('../api/v1/fulmuv/ordenes/updateEstado', {
      id_orden: id_orden,
      id_usuario: id_usuario,
      orden_estado: orden_estado
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes.php")
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
          case "eliminada":

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

            break;
          case "aprobada":

            // Validar que todas las órdenes sean estado "creada"
            var createdStateCount = selectedRows.filter(function (rowData) {
              return rowData.estado === 'creada';
            }).length;

            if (createdStateCount === selectedRows.length) {
              selectedRows = selectedRows.map(orden => orden.id)
              $.post('../api/v1/fulmuv/ordenes/updateEstado', {
                id_orden: selectedRows,
                id_usuario: id_usuario,
                orden_estado: orden_estado
              }, function (returnedData) {
                var returned = JSON.parse(returnedData);
                if (returned.error === false) {
                  SweetAlert("url_success", returned.msg, "ordenes.php");
                } else {
                  SweetAlert("error", returned.msg);
                }
              });
            } else {
              SweetAlert("warning", "Todas las órdenes deben estar en estado 'creada' para poder ser aprobadas.");
            }

            break;
          case "procesada":
            // validar que las órdenes estén en estado "aprobada" antes de procesarlas.

            var approvedStateCount = selectedRows.filter(function (rowData) {
              return rowData.estado === 'aprobada';
            }).length;

            if (approvedStateCount === selectedRows.length) {

              // selectedRows = selectedRows.map(orden => orden.id)
              // procesarOrden(selectedRows);

              const idsConEnvio  = selectedRows.filter(r => r.envio_domicilio === 1).map(r => r.id);
              const idsSinEnvio  = selectedRows.filter(r => r.envio_domicilio !== 1).map(r => r.id);

              if (idsSinEnvio.length) {
                $.post('../api/v1/fulmuv/ordenes/updateEstado', {
                  id_orden: selectedRows,
                  id_usuario: id_usuario,
                  orden_estado: orden_estado
                }, function (returnedData) {
                  var returned = JSON.parse(returnedData);
                  if (returned.error === false) {
                    if (idsConEnvio.length) {
                      procesarOrden(idsConEnvio);
                    }else{
                      SweetAlert("url_success", returned.msg, "ordenes.php");
                    }
                  } else {
                    SweetAlert("error", returned.msg);
                  }
                });
              }

            } else {
              SweetAlert("warning", "Todas las órdenes deben estar en estado 'aprobada' para poder ser procesadas.");
            }
            break;
          case "enviada":
            // validar que las órdenes estén en estado "aprobada" antes de procesarlas.

            var approvedStateCount = selectedRows.filter(function (rowData) {
              return rowData.estado === 'procesada';
            }).length;

            if (approvedStateCount === selectedRows.length) {

              selectedRows = selectedRows.map(orden => orden.id)
              // procesarOrden(selectedRows);
              $.post('../api/v1/fulmuv/ordenes/updateEstado', {
                id_orden: selectedRows,
                id_usuario: id_usuario,
                orden_estado: orden_estado
              }, function (returnedData) {
                var returned = JSON.parse(returnedData);
                if (returned.error === false) {
                  SweetAlert("url_success", returned.msg, "ordenes.php");
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

            var approvedStateCount = selectedRows.filter(function (rowData) {
              return rowData.estado === 'enviada';
            }).length;

            if (approvedStateCount === selectedRows.length) {

              selectedRows = selectedRows.map(orden => orden.id)
              // procesarOrden(selectedRows);
              $.post('../api/v1/fulmuv/ordenes/updateEstado', {
                id_orden: selectedRows,
                id_usuario: id_usuario,
                orden_estado: orden_estado
              }, function (returnedData) {
                var returned = JSON.parse(returnedData);
                if (returned.error === false) {
                  SweetAlert("url_success", returned.msg, "ordenes.php");
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
        SweetAlert("url_success", returned.msg, "ordenes.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  });
}

function showNotes(id_orden) {
  $("#notesLog").empty()
  $("#div_submit").empty()
  $("#show_notes_modal").modal("show")

  $.get('../api/v1/fulmuv/ordenes/' + id_orden + '/notas', {}, function (returnedData) {
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
          </div>`)
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
    $.post('../api/v1/fulmuv/ordenes/' + id_orden + '/notas/create', {
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

function procesarOrden(id_orden) {

  swal({
    title: "Warning",
    text: `Estás seguro que quieres procesar ${id_orden.length > 1 ? 'estas órdenes' : 'esta orden'}? \n
    ¡Recuerda que es OBLIGATORIO el llenado correcto del peso de tu envío y el valor pagado por tu cliente!`,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#eb9bb2",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false,
    closeOnConfirm: true
  }, function () {
    $.post('../api/v1/fulmuv/ordenes_iso/create', {
      id_orden: id_orden,
      id_usuario: id_usuario
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  });

}

function confirmarPago(id_orden) {
  swal({
    title: "Confirmar pago",
    text: "¿Deseas confirmar el pago de esta orden? Esta acción actualizará el estado de venta.",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#2bcb75",
    confirmButtonText: "Sí, confirmar",
    cancelButtonText: 'Cancelar',
    closeOnConfirm: false,
    closeOnConfirm: true
  }, function () {
    $.post('../api/v1/fulmuv/ordenes/updateEstadoVenta', {
      id_orden: id_orden,
      estado_venta: 2
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  });
}

function verGuia(id_orden_empresa){
  $.post('../api/v1/fulmuv/getPDFGUIAA4', {
    id_orden_empresa: id_orden_empresa
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned["error"] == false) {
      console.log(returned)
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
                  <h4 class="mb-1" id="staticBackdropLabel">Visualizar Guía </h4>
                </div>
                <div class="p-4">
                  <iframe src="${returned.data[0].url_grupoentrega}" title="description" style="width:100%;height:75vh;border:0"></iframe>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
              </div>
            </div>
          </div>
      `);
      $("#btnModal").click();
    } else {
      SweetAlert("error", returned.msg);
    }
  });
}
