var id_usuario = $("#id_principal").val()

let rawArray = []
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

        // orden.ordenes.forEach(ord => {
        //   empresas += `
        //         <div class="position-relative me-2">
        //           <h6 class="mb-1 fw-semi-bold text-nowrap"><a class="text-900 stretched-link"  href='orden_detalle.php?id_orden=${ord.id_orden}'>${ord.empresa}</a></h6>
        //           <p class="fw-semi-bold mb-0 text-500">${ord.sucursal}</p>
        //         </div>`
        // });

        // ==== NUEVO: Columna Venta (estado_venta + pago) ====
        const estadoVenta = Number(orden.ordenes.estado_venta || 0);
        const tienePago = Array.isArray(orden.ordenes.pagos) && orden.ordenes.pagos.length > 0;

        let ventaCol = '';
        if (estadoVenta === 0) {
          ventaCol = `<span class="badge badge rounded-pill badge-subtle-secondary">Creada</span>`;
        } else if (estadoVenta === 1 && !tienePago) {
          ventaCol = `<span class="badge badge rounded-pill badge-subtle-warning">En proceso de confirmación de envío por parte de la empresa</span>`;
        } else if (estadoVenta === 2 && !tienePago) {
          ventaCol = `<span class="badge badge rounded-pill badge-subtle-warning">En proceso de pagar el envío</span>`;
        } else if (estadoVenta === 2 && tienePago) {
          const pago = orden.ordenes.pagos[0]; // tomamos el primero
          // Prepara payload para el modal
          const payload = {
            id_orden_iso: orden.id_orden_iso,
            numero_orden: `#${orden.id_orden_iso}`,
            productos: orden.ordenes.productos || [],

            // productos
            subtotal: Number(orden.ordenes.subtotal || orden.ordenes.total || 0), // subtotal sin IVA si lo tienes
            iva: Number(orden.ordenes.iva || 0),
            total_productos: Number(orden.ordenes.total || 0), // total productos (con IVA si tu backend lo envía así)

            // envío
            peso_kg: Number(orden.ordenes.peso_total_kg || orden.ordenes.peso_real_total_kg || orden.ordenes.peso_total || 0),
            valor_envio: Number(orden.ordenes.total_envio || orden.total_envio || 0),

            imagen: pago?.imagen || '',
            id_ordenes: orden.ordenes.id_ordenes,
            id_trayecto: orden.id_trayecto,
            id_orden_empresa: orden.id_orden_empresa
          };

          ventaCol = `
          <button class="btn btn-sm btn-outline-primary btn-ver-pago"
                  data-payload="${encodeURIComponent(JSON.stringify(payload))}">
            Visualizar pago
          </button>`;
        } else if (estadoVenta === 3) {

          // Busca la URL en diferentes posibles lugares (ajusta al que realmente envías)
          const urlGuia =
            orden?.grupo_entrega[0].url_grupoentrega ||
            orden?.grupo_entrega[0].url ||
            '';

          if (urlGuia) {
            const payloadGuia = {
              id_orden_iso: orden.id_orden_iso,
              url: urlGuia
            };

            ventaCol = `
              <button class="btn btn-sm btn-outline-success btn-ver-guia"
                      data-payload="${encodeURIComponent(JSON.stringify(payloadGuia))}">
                Ver guía
              </button>
            `;
          } else {
            ventaCol = `<span class="badge badge rounded-pill badge-subtle-danger">Guía no disponible</span>`;
          }
        }

        $("#lista_ordenes").append(`
          <tr class="btn-reveal-trigger">
            <td class="align-middle" style="width: 28px;">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox"
                      id="number-pagination-item-${orden.id_orden_iso}"
                      data-id="${orden.id_orden_iso}"
                      data-estado="${orden.ordenes.orden_estado}"
                      data-bulk-select-row="data-bulk-select-row" />
              </div>
            </td>

            <td class="align-middle white-space-nowrap fw-semi-bold name">
              <a href="orden_iso_detalle.php?id_orden_iso=${orden.id_orden_iso}">#${orden.ordenes.numero_orden}</a>
            </td>

            <td class="date py-2 align-middle">${orden.created_at}</td>

            <td class="align-left fw-bold">
              ${orden.ordenes.nombres}
              <br>
              <span class="fs-italic fw-normal" style="font-size: 10px">${orden.ordenes.cedula}</span>  
              <br>
              <span class="fs-italic fw-normal" style="font-size: 10px">${orden.ordenes.correo}</span>  
            </td>

            <td class="align-middle text-center fs-9 white-space-nowrap payment">
              ${estado}
            </td>

            <!-- NUEVA COLUMNA: VENTA -->
            <td class="align-middle text-center">
              ${ventaCol}
            </td>

            <td class="align-middle text-end">$${orden.ordenes.total_envio}</td>

            <td class="align-middle white-space-nowrap text-end">
              <div class="dropstart font-sans-serif position-static d-inline-block">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end"
                        type="button" data-bs-toggle="dropdown" data-boundary="window"
                        aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                  <span class="fas fa-ellipsis-h fs-10"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border py-2">
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
        'pageLength': 25, 'info': true,
        'language': {
          //'url': 'http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
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

// Abrir modal "Visualizar pago"
$(document).on('click', '.btn-ver-pago', function () {
  const raw = $(this).data('payload');
  rawArray = []

  console.log(raw)
  if (!raw) return;
  const data = JSON.parse(decodeURIComponent(raw));
  rawArray = data;
  console.log(data)
  // Pintar cabecera
  $('#vp-numero').text(data.numero_orden || '');
  $('#vp-id-orden-iso').val(data.id_orden_iso || '');

  // Render productos
  const $tbody = $('#vp-tabla-productos tbody').empty();
  const productos = Array.isArray(data.productos) ? data.productos : [];

  let subtotalCalc = 0;

  productos.forEach(p => {
    const nombre = (p.nombre || '').toString().trim();
    const cant = Number(p.cantidad || 0);

    // Usa precio según tu estructura real
    const precio = Number(p.valor_descuento || p.precio || 0);

    const linea = cant * precio;
    subtotalCalc += linea;

    $tbody.append(`
    <tr>
      <td>${nombre}</td>
      <td class="text-end">${cant}</td>
      <td class="text-end">${formatoMoneda.format(precio)}</td>
      <td class="text-end">${formatoMoneda.format(linea)}</td>
    </tr>
  `);
  });

  // ========== TOTALES PRODUCTOS ==========
  const subtotal = Number(data.subtotal || subtotalCalc || 0);

  // Si tu backend ya manda IVA, úsalo. Si no, calcúlalo.
  const iva = Number(data.iva || (subtotal * 0.15));
  const totalProductos = Number(subtotal + iva);

  // ========== ENVÍO ==========
  const pesoKg = Number(data.peso_kg || 0);
  const valorEnvio = Number(data.valor_envio || 0);

  // ========== TOTAL GENERAL ==========
  const totalGeneral = totalProductos + valorEnvio;

  // Pintar totales en el modal
  $('#vp-subtotal').text(formatoMoneda.format(subtotal));
  $('#vp-iva').text(formatoMoneda.format(iva));
  $('#vp-total-productos').text(formatoMoneda.format(totalProductos));

  $('#vp-peso').text(`${pesoKg.toFixed(2)} kg`);
  $('#vp-valor-envio').text(formatoMoneda.format(valorEnvio));

  $('#vp-total-general').text(formatoMoneda.format(totalGeneral));

  // Comprobante
  const img = (data.imagen || '').toString();
  $('#vp-img').attr('src', "../" + img || 'img/placeholder.png');
  $('#vp-link').attr('href', "../" + img || '#');

  // Mostrar modal
  $('#modalVerPago').modal('show');
});

// Confirmar venta (envío)
$('#vp-confirmar').on('click', function () {
  const idOrdenIso = $('#vp-id-orden-iso').val();

  Swal.fire({
    icon: 'question',
    title: 'Confirmar envío',
    text: '¿Quieres confirmar la venta y continuar con la guía?',
    showCancelButton: true,
    confirmButtonText: 'Confirmar envío Servientrega',
    cancelButtonText: 'Cancelar Operación'
  }).then((res) => {
    if (res.isConfirmed) {
      // ENDPOINT: reemplaza por el tuyo
      $.post('../api/v1/fulmuv/ordenes_iso/confirmarVenta', {
        raw: rawArray,
        id_usuario: $("#id_principal").val()
      }, function (r) {
        let ret;
        try { ret = (typeof r === 'string') ? JSON.parse(r) : r; } catch { ret = { error: true, msg: 'Error inesperado' }; }

        if (!ret.error) {
          Swal.fire({
            icon: 'success',
            title: 'Confirmado',
            text: 'El envío fue confirmado correctamente.',
          }).then(() => {
            // refrescar tabla
            window.location.reload();
          });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: ret.msg || 'No se pudo confirmar.' });
        }
      }).fail(() => {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo contactar con el servidor.' });
      });
    }
  });
});

// Currency fallback si no existiera
if (!window.formatoMoneda) {
  window.formatoMoneda = new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD' });
}

// Safe JSON parse
function safeParseArr(str) { try { const j = JSON.parse(str); return Array.isArray(j) ? j : []; } catch { return []; } }

// Abrir modal "Ver guía"
$(document).on('click', '.btn-ver-guia', function () {
  const raw = $(this).data('payload');
  if (!raw) return;

  let data;
  try { data = JSON.parse(decodeURIComponent(raw)); }
  catch { data = null; }

  if (!data?.url) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se encontró la URL de la guía.' });
    return;
  }

  // Carga iframe + link externo
  $('#vg-frame').attr('src', data.url);
  $('#vg-open').attr('href', data.url);

  $('#modalVerGuia').modal('show');
});

// Limpia el iframe al cerrar (opcional)
$('#modalVerGuia').on('hidden.bs.modal', function () {
  $('#vg-frame').attr('src', '');
  $('#vg-open').attr('href', '#');
});
