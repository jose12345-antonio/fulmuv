let establecimientos = [];
let productos = [];
var id_empresa = document.getElementById("id_empresa")?.value;
let ordenDetalleTable = null;
let id_trayecto_global = null;
let envio_domicilio_orden = 0;
const CONFIG_ENVIO = {
  divisorVolumetrico: 6000, // AJUSTAR según GRUPO ENTREGAS (5000 o 6000 normalmente)
  iva: 0.15,
  seguroPct: 0.011,
  umbralAlertaKg: 50
};

$(document).ready(function () {

  $.get('../api/v1/fulmuv/getPermisosByUser/' + $("#id_principal").val(), {}, function (returnedDat) {
    permisosData = JSON.parse(returnedDat);
    if (permisosData.error == false) {
      var permiso = permisosData.data.filter(permiso => (permiso.permiso == "Ordenes"))[0];
      if (permiso.valor == "true") {
        levels = permiso.levels
        $.get('../api/v1/fulmuv/ordenes/' + $("#id_orden").val(), {}, function (returnedData) {
          var returned = JSON.parse(returnedData)
          if (returned.error == false) {

            id_trayecto_global = returned.data.id_trayecto ? String(returned.data.id_trayecto) : null;
            envio_domicilio_orden = Number(returned.data.envio_domicilio ?? 0);

            $("#numero_orden").append(returned.data.id_orden)
            $("#nombre_empresa").append(returned.data.empresa)
            $("#nombre_sucursal").append(returned.data.sucursal)
            $("#nombre_area").append(returned.data.area)
            $("#fecha").text(returned.data.created_at)

            if (returned.data.estado_venta == 0) {
              $("#mensaje").append("Empaca siempre tus productos como se indica para su correcto transporte, de acuerdo a la referencia mostrada. Esto permite que el producto llegue en condiciones adecuadas a tu comprador, y mejora la manipulación y transporte que la empresa de envío realiza. Cualquier daño al producto por un empaquetado deficiente, será imputado al vendedor, quien deberá reemplazar el producto y asumir el 100% de los costos (producto, logística, trámite, y adicionales que puedan generarse.) RECUERDA QUE: el vendedor debe proyectar una imagen profesional y confiable, y estamos para sumarte.")
            } else if (returned.data.estado_venta == 1) {
              $("#mensaje").append("Una vez el cliente final pague por su envío a domicilio, recibirás unas guías enviadas a tu perfil por FULMUV, y a tus correos electrónicos registrados, que debes imprimir y pegar en el exterior de los empaques que contienen a los productos. Puedes imprimir las guías en hojas de papel bond, pueden ser recicladas, siempre que uno de los dos lados esté en blanco, para que sea el que la guía ocupe. Debes imprimir mínimo 2 guías por empaque. Pega las guías en los empaques, en lugares visibles, con cinta adhesiva, cubriendolas a manera de plastificado, para evitar su deterioro o daño hasta llegar a su punto de entrega.")
            } if (returned.data.estado_venta == 2) {
              $("#mensaje").append("Imprime y pega las GUÍAS DE ENVÍO en el exterior de los empaques que contienen a los productos. Puedes imprimir las guías en hojas de papel bond, pueden ser recicladas, siempre que uno de los dos lados esté en blanco, para que sea el que la guía ocupe. Debes imprimir mínimo 2 guías por empaque. Pega las guías en los empaques, en lugares visibles, con cinta adhesiva, cubriendolas a manera de plastificado, para evitar su deterioro o daño hasta llegar a su punto de entrega.")
            }
            if (envio_domicilio_orden === 1) {
              $("#btn_confirmar_peso").prop("disabled", true).addClass("disabled");
              $("#alert_retiro_empresa")
                .removeClass("d-none")
                .text("Este pedido se debe retirar en la empresa, por lo tanto no se confirma peso ni envío.");
            }

            var estado = "";
            switch (returned.data.orden_estado) {
              case "creada":
                estado = "<span class='badge badge rounded-pill badge-subtle-secondary text-capitalize'>creada<span class='ms-1 fas fa-shopping-cart' data-fa-transform='shrink-2'></span></span>"
                break;
              case "aprobada":
                estado = "<span class='badge badge rounded-pill badge-subtle-warning text-capitalize'>aprobada<span class='ms-1 fas fa-user-check' data-fa-transform='shrink-2'></span></span>"
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
            $("#estado").append(estado)
            productos = returned.data.productos;
            $("#lista_productos").html("");
            returned.data.productos.forEach(function (producto, index) {

              const pesoUnit = Number(String(producto.peso ?? 0).replace(',', '.')) || 0;
              const cant = parseInt(producto.cantidad ?? 0);
              const pesoTot = pesoUnit * cant;

              $("#lista_productos").append(`
                <tr class="border-200">
                  <td class="align-middle" style="width: 28px;">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="number-pagination-item-${producto.id}" data-id="${producto.id}" data-estado="${producto.estado}" data-bulk-select-row="data-bulk-select-row" />
                    </div>
                  </td>
                  <td class="align-middle">
                    <div class="orden-detalle-product">
                      <img class="img-fluid d-none d-md-block" src="../${producto.imagen}" alt="">
                      <div class="flex-1">
                        <div class="orden-detalle-product-name">${producto.nombre}</div>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle text-center"><span class="orden-detalle-number">${producto.peso}</span></td>
                  <td class="align-middle text-center"><span class="orden-detalle-number">${producto.cantidad}</span></td>
                  <td class="align-middle text-center">
                    <span class="orden-detalle-number">${pesoTot}</span>
                  </td>
                  <td class="align-middle text-end carrito"><span class="orden-detalle-number">$${producto.precio.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span></td>
                </tr>
              `);
            });
            $("#subtotal").text("$" + parseFloat(returned.data.total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            var iva = parseFloat(returned.data.total) * 0.15;
            $("#iva").text("$" + iva.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $("#total").text("$" + (parseFloat(returned.data.total) + iva).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))

            if (permiso.levels == "Sucursal") {
              $('.carrito').hide();
            }

            if ($.fn.DataTable.isDataTable('#my_table')) {
              $('#my_table').DataTable().destroy();
            }

            ordenDetalleTable = $("#my_table").DataTable({
              responsive: false,
              lengthChange: false,
              searching: true,
              pageLength: 100,
              info: true,
              autoWidth: false,
              columnDefs: [
                { visible: permiso.levels == "Sucursal" ? false : true, targets: [5] },
                { orderable: false, targets: [0] }
              ],
              language: {
                search: "",
                searchPlaceholder: "Buscar producto",
                info: "Mostrando _START_ a _END_ de _TOTAL_ productos",
                infoEmpty: "Mostrando 0 a 0 de 0 productos",
                zeroRecords: "No se encontraron productos con ese criterio",
                emptyTable: "No hay productos en esta orden",
                paginate: {
                  next: "<span class='fas fa-chevron-right'></span>",
                  previous: "<span class='fas fa-chevron-left'></span>"
                }
              },
              dom: "<'row align-items-center g-3 mb-3'<'col-md-6'f><'col-md-6 text-md-end'>>" +
                "<'table-responsive scrollbar'tr>" +
                "<'row align-items-center g-3 pt-3'<'col-md-6'i><'col-md-6 d-flex justify-content-md-end'p>>"
            });

            $("#checkbox-bulk-table-item-select").attr("data-bulk-select", '{"body":"lista_productos","actions":"table-number-pagination-actions","replacedElement":"table-number-pagination-replace-element"}');
            bulkSelectInit()

          }
        });
      }
    }
  });
});

function llenarTrayectos(preselectId) {
  const tipo = $("#tipo_trayecto").val() || 'mercancia_premier';
  $.get('../api/v1/fulmuv/getTrayectos/' + tipo, {}, function (returnedData) {
    const returned = JSON.parse(returnedData);
    if (returned.error === false) {
      $("#trayecto").empty().append(`<option value="">Seleccione trayecto</option>`);
      returned.data.forEach(t => {
        $("#trayecto").append(`<option value="${t.id_trayecto}">${t.nombre}</option>`);
      });
      if (preselectId) {
        $("#trayecto").val(String(preselectId));
      }
    }
  });
}

function verTrayecto() {
  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;"></button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg mt-6" role="document">
        <div class="modal-content border-0">
          <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
              <h4 class="mb-1" id="staticBackdropLabel">Trayectos</h4>
            </div>
            <div class="p-4">
              <div class="row">
                <div class="col-6">
                  <label class="form-label">Tipo de Trayecto</label>
                  <select id="tipo_trayecto" class="form-select">
                      <option value="documentos">DOCUMENTOS</option>
                      <option value="mercancia_premier" selected>MERCANCIA PREMIER (CARGA LIVIANA)</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label">Trayecto</label>
                  <select id="trayecto" class="form-select">
                      <option value="">Seleccione trayecto</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="guardarTrayecto()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  `);

  // por defecto mercancia_premier y cargar opciones
  $("#tipo_trayecto").val("mercancia_premier");
  llenarTrayectos(id_trayecto_global);

  // si cambian el tipo, recarga trayectos (sin preselección)
  $(document).off('change', '#tipo_trayecto').on('change', '#tipo_trayecto', () => llenarTrayectos());

  $("#btnModal").click();
}


function guardarTrayecto() {
  var trayecto = $("#trayecto").val();
  var id_orden = $("#id_orden").val()
  $.post('../api/v1/fulmuv/ordenes/trayecto', {
    id_orden: id_orden,
    trayecto: trayecto
  }, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned["error"] == false) {
      SweetAlert("url_success", returned.msg, "orden_detalle.php?id_orden=" + id_orden)
    } else {
      SweetAlert("error", returned.msg);
    }
  });
}

function verPago() {
  $.get('../api/v1/fulmuv/ordenPago/' + $("#id_orden").val(), {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      let pagos = returned.data;
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
                  <h4 class="mb-1" id="staticBackdropLabel">Visualizar pago</h4>
                </div>
                <div class="p-4">
                  <div class="row" id="fotos_pagos">

                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
              </div>
            </div>
          </div>
      `);
      pagos.forEach(pago => {
        $("#fotos_pagos").append(`
          <div class="col-6 mb-3">
            <img class="img-thumbnail w-100" src="../admin/${pago.imagen}" alt="Pago" />
          </div>
        `);
      });
      $("#btnModal").click();
    }
  });
}

// function confirmarPeso() {
//   $("#alert").text("");
//   $("#alert").append(`
//     <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;"></button>
//     <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
//       <div class="modal-dialog modal-lg mt-6" role="document">
//         <div class="modal-content border-0">
//           <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
//             <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
//           </div>
//           <div class="modal-body p-0">
//             <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
//               <h4 class="mb-1" id="staticBackdropLabel">Confirmar empaque y envío</h4>
//             </div>
//             <h6 class="p-4 fw-normal">
//               El peso ingresado debe ser lo más exacto posible. Si tienes dudas, coloca un peso superior referencial.<br/>
//               El valor del producto debe ser exacto para el cálculo de seguro.
//             </h6>

//             <div class="px-4 pb-2">
//               <div class="alert alert-warning d-none" id="alert_envio_modal"></div>
//               <div class="alert alert-danger d-none" id="errors_envio_modal"></div>
//             </div>

//             <div class="px-4 py-2">
//               <div class="row g-3" id="pesos_productos"></div>
//             </div>
//           </div>
//           <div class="modal-footer">
//             <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
//             <button class="btn btn-primary" type="button" onclick="guardarPesos()">Guardar</button>
//           </div>
//         </div>
//       </div>
//     </div>
//   `);

//   // productos seleccionados en la tabla (si no hay, muestra todos)
//   const seleccionados = new Set(
//     $('#lista_productos input[type="checkbox"]:checked')
//       .map((_, el) => String($(el).data('id'))).get()
//   );
//   const lista = seleccionados.size
//     ? productos.filter(p => seleccionados.has(String(p.id)))
//     : productos;

//   $("#pesos_productos").empty();

//   // Render inputs por producto
//   lista.forEach(producto => {
//     const id = String(producto.id);
//     const cant = parseInt(producto.cantidad ?? 0);

//     // valores iniciales normalizados
//     const pIni = Number(String(producto.peso ?? 0).replace(',', '.')) || 0;
//     const valorIni = Number(String(producto.total_pagado ?? 0).replace(',', '.')) || 0;

//     const largoIni = Number(String(producto.largo_cm ?? producto.dimensiones?.largo ?? 0).replace(',', '.')) || 0;
//     const anchoIni = Number(String(producto.ancho_cm ?? producto.dimensiones?.ancho ?? 0).replace(',', '.')) || 0;
//     const altoIni = Number(String(producto.alto_cm ?? producto.dimensiones?.alto ?? producto.dimensiones?.altura ?? 0).replace(',', '.')) || 0;

//     const fragilIni = !!(producto.fragil ?? false);

//     $("#pesos_productos").append(`
//       <div class="col-12">
//         <div class="border rounded p-3">
//           <div class="d-flex justify-content-between align-items-start">
//             <div>
//               <label class="form-label fw-bold mb-1">${producto.nombre}</label>
//               <div class="text-muted small">Cantidad: <b>${cant}</b></div>
//             </div>
//             <div class="form-check form-switch">
//               <input class="form-check-input" type="checkbox" id="fragil_${id}" ${fragilIni ? 'checked' : ''}>
//               <label class="form-check-label" for="fragil_${id}">Frágil</label>
//             </div>
//           </div>

//           <div class="row g-3 mt-1">
//             <div class="col-md-6">
//               <label class="form-label fw-bold">Peso (kg/unidad)</label>
//               <div class="text-muted small">Obligatorio para cálculo de valor de envío.</div>
//               <input type="number" step="0.01" min="0" class="form-control" data-id="${id}" name="peso_${id}" value="${pIni || ''}" placeholder="Ingrese peso en kg">
//               <small class="text-muted">Se multiplicará por la cantidad (${cant})</small>
//             </div>

//             <div class="col-md-6">
//               <label class="form-label fw-bold">Valor real del producto (USD)</label>
//               <div class="text-muted small">Obligatorio para cálculo de seguro (1.3% + IVA).</div>
//               <input type="number" step="0.01" min="0" class="form-control" data-id="${id}" name="valor_${id}" value="${valorIni || ''}" placeholder="Ingrese valor real del producto">
//             </div>

//             <div class="col-12">
//               <label class="form-label fw-bold mb-1">Dimensiones del paquete (cm)</label>
//               <div class="row g-2">
//                 <div class="col-md-4">
//                   <input type="number" step="0.01" min="0" class="form-control" name="largo_${id}" value="${largoIni || ''}" placeholder="Largo">
//                 </div>
//                 <div class="col-md-4">
//                   <input type="number" step="0.01" min="0" class="form-control" name="ancho_${id}" value="${anchoIni || ''}" placeholder="Ancho">
//                 </div>
//                 <div class="col-md-4">
//                   <input type="number" step="0.01" min="0" class="form-control" name="alto_${id}" value="${altoIni || ''}" placeholder="Alto">
//                 </div>
//               </div>
//               <div class="text-muted small mt-1">
//                 FULMUV calcula automáticamente el peso volumétrico y usará el mayor entre peso real y volumétrico.
//               </div>
//             </div>

//             <div class="col-12">
//               <label class="form-label">Peso real total (kg) = peso * ${cant}</label>
//               <input type="number" step="0.01" class="form-control" id="peso_total_${id}" readonly>
//             </div>

//             <div class="col-md-4">
//               <label class="form-label">Peso volumétrico (kg)</label>
//               <input type="number" step="0.01" class="form-control" id="peso_vol_${id}" readonly>
//             </div>

//             <div class="col-md-4">
//               <label class="form-label fw-bold">Peso para envío (kg)</label>
//               <input type="number" step="0.01" class="form-control" id="peso_fact_${id}" readonly>
//             </div>

//             <div class="col-md-4">
//               <label class="form-label">Seguro total (USD)</label>
//               <input type="number" step="0.01" class="form-control" id="seguro_total_${id}" readonly>
//               <div class="text-muted small" id="seguro_det_${id}"></div>
//             </div>

//             <div class="col-12 d-none" id="warn_${id}">
//               <div class="alert alert-warning mb-0">
//                 ⚠️ Este envío supera ${CONFIG_ENVIO.umbralAlertaKg} kg (real o volumétrico). Se activará advertencia interna y para GRUPO ENTREGAS tras el pago.
//               </div>
//             </div>

//           </div>
//         </div>
//       </div>
//     `);

//     // Calcula inicial
//     recalcularFilaEnvio(id, cant);

//     // listeners (con namespace para evitar duplicados)
//     $(document).off('input.envio', `#pesos_productos input[name='peso_${id}'], #pesos_productos input[name='valor_${id}'], #pesos_productos input[name='largo_${id}'], #pesos_productos input[name='ancho_${id}'], #pesos_productos input[name='alto_${id}']`);
//     $(document).on('input.envio', `#pesos_productos input[name='peso_${id}'], #pesos_productos input[name='valor_${id}'], #pesos_productos input[name='largo_${id}'], #pesos_productos input[name='ancho_${id}'], #pesos_productos input[name='alto_${id}']`, function () {
//       recalcularFilaEnvio(id, cant);
//       validarModalEnvio(); // refresca errores globales
//     });

//     $(document).off('change.envio', `#fragil_${id}`);
//     $(document).on('change.envio', `#fragil_${id}`, function () {
//       validarModalEnvio();
//     });
//   });

//   validarModalEnvio();
//   $("#btnModal").click();
// }

function confirmarPeso() {
  if (envio_domicilio_orden === 1) {
    SweetAlert("warning", "Este pedido se debe retirar en la empresa. No es necesario confirmar peso ni envío.");
    return;
  }
  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;"></button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg mt-6" role="document">
        <div class="modal-content border-0">
          <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-0">
            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
              <h4 class="mb-1" id="staticBackdropLabel">Confirmar empaque y envío</h4>
            </div>
           <div class="p-4">
            <ul class="mb-3 ps-3 small" style="line-height:2;">
              <li class="h5">El peso ingresado debe ser lo más exacto posible. Si tienes dudas, coloca un peso superior referencial.</li>
              <li class="h5">El valor del producto debe ser exacto para el cálculo de seguro.</li>
            </ul>
            <hr>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
              <span class="text-black fw-bold h5">¿Dudas para empacar correctamente?</span>
              <a type="button" class="btn btn-outline-primary" id="btnGuiaEmpaque" href="../documentos/6_8_NW_Guía_de_Empaquetado_y_Envío_a_Domicilio.pdf" target="_blank">
                <i class="fi-rs-info me-1"></i> Sigue la guía de empaquetado
              </a>
            </div>
            <hr>
          </div>


            <div class="px-4 pb-2">
              <div class="alert alert-warning d-none" id="alert_envio_modal"></div>
              <div class="alert alert-danger d-none" id="errors_envio_modal"></div>
            </div>

            <!-- BLOQUE UNICO POR ORDEN -->
            <div class="px-4">
              <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-bold">Datos del paquete (ORDEN)</div>
                    <div class="text-muted small">Estos datos se guardan como campos independientes en la tabla <b>ordenes</b>.</div>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="fragil_orden">
                    <label class="form-check-label" for="fragil_orden">Frágil</label>
                  </div>
                </div>

                <div class="row g-2 mt-2">
                  <div class="col-md-4">
                    <label class="form-label mb-1">Largo (cm)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="largo_orden" placeholder="Largo">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label mb-1">Ancho (cm)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="ancho_orden" placeholder="Ancho">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label mb-1">Alto (cm)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="alto_orden" placeholder="Alto">
                  </div>

                  <div class="col-md-6 mt-2">
                    <label class="form-label mb-1 fw-bold">Valor real pagado por el cliente (USD)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="valor_producto_orden" placeholder="Ej: 200.00">
                    <div class="text-muted small">Se usa paa asegurar el envío de tu cliente</div>
                  </div>

                  <div class="col-md-6 mt-2">
                    <label class="form-label mb-1">Resumen de envío (FULMUV)</label>
                    <div class="border rounded p-2 bg-body-tertiary">
                      <div class="small">Peso real total: <b><span id="res_peso_real">0</span> kg</b></div>
                      <div class="small">Peso volumétrico: <b><span id="res_peso_vol">0</span> kg</b></div>
                      <div class="small">Peso para envío: <b><span id="res_peso_fact">0</span> kg</b></div>
                      <div class="small">Seguro total: <b>$<span id="res_seguro_total">0</span></b>
                        <span class="text-muted">(<span id="res_seguro_det">1.1% + IVA</span>)</span>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 mt-2 d-none" id="warn_orden_50kg">
                    <div class="alert alert-warning mb-0">
                      ⚠️ El envío supera ${CONFIG_ENVIO.umbralAlertaKg} kg (real o volumétrico). Se registrará advertencia interna y para GRUPO ENTREGAS tras el pago.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- LISTA POR PRODUCTO (SOLO PESO + TOTAL PAGADO) -->
            <div class="px-4 py-2">
              <div class="row g-3" id="pesos_productos"></div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" type="button" onclick="guardarPesos()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  `);

  // productos seleccionados en la tabla (si no hay, muestra todos)
  const seleccionados = new Set(
    $('#lista_productos input[type="checkbox"]:checked')
      .map((_, el) => String($(el).data('id'))).get()
  );
  const lista = seleccionados.size
    ? productos.filter(p => seleccionados.has(String(p.id)))
    : productos;

  $("#pesos_productos").empty();

  // Render inputs por producto (solo peso y total pagado)
  lista.forEach(producto => {
    const id = String(producto.id);
    const pIni = Number(String(producto.peso ?? '').replace(',', '.')) || '';

    // Si ya guardaron total_pagado antes, respétalo.
    // Si no, usar "valor real" calculado (precio * cantidad)
    const cant = parseInt(producto.cantidad ?? 0);
    const totalGuardado = Number(String(producto.total_pagado ?? '').replace(',', '.')) || 0;

    const precioUnit = Number(String(producto.precio ?? 0).replace(',', '.')) || 0;
    // valor real sugerido (puedes cambiar a precioUnit si lo quieres unitario)
    const totalSugerido = round2(precioUnit * cant);

    const totalIni = (totalGuardado > 0) ? totalGuardado : (totalSugerido > 0 ? totalSugerido : '');


    $("#pesos_productos").append(`
      <div class="col-12">
        <div class="border rounded p-3">
          <div class="fw-bold mb-1">${producto.nombre}</div>
          <div class="text-muted small mb-2">Cantidad: <b>${cant}</b></div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Peso (kg/unidad)</label>
              <input type="number" step="0.01" min="0" class="form-control" name="peso_${id}" value="${pIni}" placeholder="Ingrese peso en kg">
              <small class="text-muted">Se multiplicará por la cantidad (${cant})</small>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Total pagado por el cliente (USD)</label>
              <input type="number" step="0.01" min="0" class="form-control" name="total_${id}" value="${totalIni}" placeholder="Ingrese total pagado">
              <small class="text-muted">Este valor se guarda en productos como <b>total_pagado</b>.</small>
            </div>

            <div class="col-12">
              <label class="form-label">Peso real total (kg) = peso * ${cant}</label>
              <input type="number" step="0.01" class="form-control" id="peso_total_${id}" readonly>
            </div>
          </div>
        </div>
      </div>
    `);

    // recalcula peso_total por producto
    const calcPesoTotalProducto = () => {
      const p = toNum($(`#pesos_productos input[name='peso_${id}']`).val());
      $(`#peso_total_${id}`).val(round2(p * cant));
    };

    calcPesoTotalProducto();

    $(document).off('input.envio', `#pesos_productos input[name='peso_${id}']`);
    $(document).on('input.envio', `#pesos_productos input[name='peso_${id}']`, function () {
      calcPesoTotalProducto();
      recalcularResumenOrden(lista); // actualiza resumen general
      validarModalEnvio(lista);       // validación global
    });

    $(document).off('input.envio', `#pesos_productos input[name='total_${id}']`);
    $(document).on('input.envio', `#pesos_productos input[name='total_${id}']`, function () {
      // opcional: si quieres que valor_producto_orden se autollené con la suma:
      // $("#valor_producto_orden").val( round2(sumarTotales(lista)) );
      validarModalEnvio(lista);
    });
  });

  // listeners del bloque ORDEN
  $(document).off('input.envio', `#largo_orden, #ancho_orden, #alto_orden, #valor_producto_orden`);
  $(document).on('input.envio', `#largo_orden, #ancho_orden, #alto_orden, #valor_producto_orden`, function () {
    recalcularResumenOrden(lista);
    validarModalEnvio(lista);
  });

  $(document).off('change.envio', `#fragil_orden`);
  $(document).on('change.envio', `#fragil_orden`, function () {
    validarModalEnvio(lista);
  });

  // inicial
  recalcularResumenOrden(lista);
  validarModalEnvio(lista);

  $("#btnModal").click();
}

function recalcularResumenOrden(lista) {
  // peso real total = SUM(peso_unit * cantidad) para los productos en el modal
  let pesoRealTotal = 0;

  lista.forEach(p => {
    const id = String(p.id);
    const cant = parseInt(p.cantidad ?? 0);
    const pesoUnit = toNum($(`#pesos_productos input[name='peso_${id}']`).val());
    pesoRealTotal += (pesoUnit * cant);
  });

  pesoRealTotal = round2(pesoRealTotal);

  const largo = toNum($("#largo_orden").val());
  const ancho = toNum($("#ancho_orden").val());
  const alto = toNum($("#alto_orden").val());
  const valorProducto = toNum($("#valor_producto_orden").val());

  const pesoVol = (largo > 0 && ancho > 0 && alto > 0)
    ? calcPesoVolumetricoKg(largo, ancho, alto)
    : 0;

  const pesoFact = round2(Math.max(pesoRealTotal, pesoVol));

  const seguro = (valorProducto > 0)
    ? calcSeguro(valorProducto)
    : { base: 0, iva: 0, total: 0 };

  $("#res_peso_real").text(pesoRealTotal);
  $("#res_peso_vol").text(pesoVol);
  $("#res_peso_fact").text(pesoFact);
  $("#res_seguro_total").text(seguro.total);
  $("#res_seguro_det").text(valorProducto > 0 ? `1.1%: $${seguro.base} + IVA: $${seguro.iva}` : `1.1% + IVA`);

  const alerta50 = (pesoRealTotal > CONFIG_ENVIO.umbralAlertaKg) || (pesoVol > CONFIG_ENVIO.umbralAlertaKg);
  $("#warn_orden_50kg").toggleClass("d-none", !alerta50);

  return { pesoRealTotal, pesoVol, pesoFact, seguro };
}


/** Helpers */
function toNum(val) {
  return Number(String(val ?? '').replace(',', '.')) || 0;
}

function round2(n) {
  return Math.round((n + Number.EPSILON) * 100) / 100;
}

function calcPesoVolumetricoKg(largoCm, anchoCm, altoCm) {
  // cm³ / divisor = kg (según configuración del courier)
  const vol = (largoCm * anchoCm * altoCm) / CONFIG_ENVIO.divisorVolumetrico;
  return round2(vol);
}

function calcSeguro(valorProducto) {
  const base = round2(valorProducto * CONFIG_ENVIO.seguroPct);
  const iva = round2(base * CONFIG_ENVIO.iva);
  const total = round2(base + iva);
  return { base, iva, total };
}

function recalcularFilaEnvio(id, cant) {
  const pesoUnit = toNum($(`#pesos_productos input[name='peso_${id}']`).val());
  const valorProd = toNum($(`#pesos_productos input[name='valor_${id}']`).val());

  const largo = toNum($(`#pesos_productos input[name='largo_${id}']`).val());
  const ancho = toNum($(`#pesos_productos input[name='ancho_${id}']`).val());
  const alto = toNum($(`#pesos_productos input[name='alto_${id}']`).val());

  const pesoRealTotal = round2(pesoUnit * cant);
  $(`#peso_total_${id}`).val(pesoRealTotal);

  const pesoVol = (largo > 0 && ancho > 0 && alto > 0) ? calcPesoVolumetricoKg(largo, ancho, alto) : 0;
  $(`#peso_vol_${id}`).val(pesoVol);

  const pesoFact = round2(Math.max(pesoRealTotal, pesoVol));
  $(`#peso_fact_${id}`).val(pesoFact);

  const seguro = (valorProd > 0) ? calcSeguro(valorProd) : { base: 0, iva: 0, total: 0 };
  $(`#seguro_total_${id}`).val(seguro.total);
  $(`#seguro_det_${id}`).text(valorProd > 0 ? `1.1%: $${seguro.base} + IVA: $${seguro.iva}` : '');

  // alerta > 50 kg
  const alert50 = (pesoRealTotal > CONFIG_ENVIO.umbralAlertaKg) || (pesoVol > CONFIG_ENVIO.umbralAlertaKg);
  $(`#warn_${id}`).toggleClass('d-none', !alert50);
}

function validarModalEnvio(lista) {
  const errores = [];

  // validar campos ORDEN
  const largo = toNum($("#largo_orden").val());
  const ancho = toNum($("#ancho_orden").val());
  const alto = toNum($("#alto_orden").val());
  const valorProducto = toNum($("#valor_producto_orden").val());

  if (largo <= 0 || ancho <= 0 || alto <= 0) errores.push("Complete las dimensiones del paquete (Largo/Ancho/Alto).");
  if (valorProducto <= 0) errores.push("Ingrese el valor real del producto (USD) para calcular el seguro.");

  // validar por producto: solo peso y total_pagado
  lista.forEach(p => {
    const id = String(p.id);
    const cant = parseInt(p.cantidad ?? 0);
    if (cant <= 0) errores.push(`Cantidad inválida en "${p.nombre}" (${cant}).`);

    const pesoUnit = toNum($(`#pesos_productos input[name='peso_${id}']`).val());
    const totalPagado = toNum($(`#pesos_productos input[name='total_${id}']`).val());

    if (pesoUnit <= 0) errores.push(`Ingrese el peso (kg/unidad) para "${p.nombre}".`);
    if (totalPagado <= 0) errores.push(`Ingrese el total pagado por el cliente para "${p.nombre}".`);
  });

  // Render errores
  const $err = $("#errors_envio_modal");
  if (errores.length) {
    $err.removeClass("d-none")
      .html(`<b>Faltan datos obligatorios:</b><ul class="mb-0">${errores.map(e => `<li>${e}</li>`).join('')}</ul>`);
  } else {
    $err.addClass("d-none").empty();
  }

  return errores.length === 0;
}


// function guardarPesos(){
//   var id_orden = $("#id_orden").val();
//   $("#pesos_productos input[name^='peso_']").each(function(){
//     const id = String($(this).data('id'));
//     const valor = $(this).val();
//     const nuevoPeso = (valor === "" || valor === null) ? null : Number(valor);

//     // busca el producto y actualiza su peso
//     const idx = productos.findIndex(x => String(x.id) === id);
//     if (idx !== -1) {
//       productos[idx] = { ...productos[idx], peso: nuevoPeso };
//     }
//   });

//   $.post('../api/v1/fulmuv/ordenes/updateProductos', {
//     id_orden: id_orden,
//     productos: productos,
//     estado: 1
//   }, function (returnedData) {
//     var returned = JSON.parse(returnedData);
//     if (returned["error"] == false) {
//       SweetAlert("url_success", returned.msg, "orden_detalle.php?id_orden="+id_orden)
//     } else {
//       SweetAlert("error", returned.msg);
//     }
//   });
// }

function guardarPesos() {
  const id_orden = $("#id_orden").val();

  // IDs renderizados en modal
  const idsRenderizados = $("#pesos_productos")
    .find("input[name^='peso_']")
    .map((_, el) => (String($(el).attr("name")).split("_")[1]))
    .get();

  const idsSet = new Set(idsRenderizados);

  // lista = productos que están en el modal
  const lista = productos.filter(p => idsSet.has(String(p.id)));

  // Validación global (bloquea guardar)
  if (!validarModalEnvio(lista)) {
    SweetAlert("error", "Complete los datos obligatorios antes de guardar.");
    return;
  }

  // ============================
  // 1) ACTUALIZAR productos[] SOLO con lo de siempre:
  //    - nuevoPeso -> producto.peso
  //    - nuevoTotal -> producto.total_pagado
  // ============================
  productos.forEach(producto => {
    const id = String(producto.id);
    if (!idsSet.has(id)) return;

    const pesoVal = $(`#pesos_productos input[name='peso_${id}']`).val();
    const nuevoPeso = (pesoVal === "" || pesoVal === null) ? null : Number(String(pesoVal).replace(',', '.'));

    const totalVal = $(`#pesos_productos input[name='total_${id}']`).val();
    const nuevoTotal = (totalVal === "" || totalVal === null) ? null : Number(String(totalVal).replace(',', '.'));

    producto.peso = nuevoPeso;
    producto.total_pagado = nuevoTotal;
  });

  // ============================
  // 2) CALCULAR CAMPOS INDEPENDIENTES PARA LA ORDEN (ordenes.*)
  //    - peso_real_total_kg = suma(peso_unit * cantidad) de los seleccionados
  //    - valor_producto_usd = suma(total_pagado) o el campo que el dueño llame "valor real compra"
  //    - dimensiones/frágil: 1 SOLO set (ideal que lo tengas en inputs globales del modal)
  // ============================

  // (A) Peso real total (sumatoria)
  let peso_real_total_kg = 0;
  let valor_producto_usd = 0;

  productos.forEach(producto => {
    const id = String(producto.id);
    if (!idsSet.has(id)) return;

    const cant = parseInt(producto.cantidad ?? 0);
    const pesoUnit = Number(String(producto.peso ?? 0).replace(',', '.')) || 0;
    const totalPagado = Number(String(producto.total_pagado ?? 0).replace(',', '.')) || 0;

    peso_real_total_kg += (pesoUnit * cant);
    valor_producto_usd += totalPagado;
  });

  peso_real_total_kg = round2(peso_real_total_kg);
  valor_producto_usd = round2(valor_producto_usd);

  // (B) Dimensiones y frágil: CAMPOS ÚNICOS PARA LA ORDEN
  // IMPORTANTE: estos inputs deben existir una sola vez en el modal.
  // Si todavía están por producto, deja SOLO una sección global (recomendado).
  const largo_cm = toNum($("#largo_orden").val());
  const ancho_cm = toNum($("#ancho_orden").val());
  const alto_cm = toNum($("#alto_orden").val());
  const fragil = $("#fragil_orden").is(":checked") ? 1 : 0;

  // (C) Cálculos FULMUV para la orden
  const peso_volumetrico_kg = calcPesoVolumetricoKg(largo_cm, ancho_cm, alto_cm);
  const peso_facturable_kg = round2(Math.max(peso_real_total_kg, peso_volumetrico_kg));

  const seguro = calcSeguro(valor_producto_usd);

  const alerta_mayor_50kg =
    (peso_real_total_kg > CONFIG_ENVIO.umbralAlertaKg || peso_volumetrico_kg > CONFIG_ENVIO.umbralAlertaKg) ? 1 : 0;

  // ============================
  // 3) ENVIAR A BACKEND:
  //    - productos (solo peso y total_pagado)
  //    - campos nuevos independientes para UPDATE de ordenes
  // ============================

  console.log(id_orden)
  console.log(productos)
  console.log(peso_real_total_kg)
  console.log(largo_cm)
  console.log(ancho_cm)
  console.log(alto_cm)
  console.log(fragil)
  console.log(valor_producto_usd)
  console.log(peso_volumetrico_kg)
  console.log(peso_facturable_kg)
  console.log(seguro.base)
  console.log(seguro.iva)
  console.log(seguro.total)
  console.log(CONFIG_ENVIO.divisorVolumetrico)
  console.log(CONFIG_ENVIO.iva)
  console.log(CONFIG_ENVIO.seguroPct)
  console.log(alerta_mayor_50kg)

  $.post('../api/v1/fulmuv/ordenes/updateProductos', {
    id_orden: id_orden,
    productos: productos, 
    estado: 1,

    // Campos nuevos independientes (ordenes.*)
    peso_real_total_kg: peso_real_total_kg,
    largo_cm: largo_cm,
    ancho_cm: ancho_cm,
    alto_cm: alto_cm,
    fragil: fragil,
    valor_producto_usd: valor_producto_usd,

    peso_volumetrico_kg: peso_volumetrico_kg,
    peso_facturable_kg: peso_facturable_kg,

    seguro_base_usd: seguro.base,
    seguro_iva_usd: seguro.iva,
    seguro_total_usd: seguro.total,

    alerta_mayor_50kg: alerta_mayor_50kg,

    // Auditoría/config
    divisor_volumetrico: CONFIG_ENVIO.divisorVolumetrico, 
    iva_envio: CONFIG_ENVIO.iva,
    seguro_pct: CONFIG_ENVIO.seguroPct

  }, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned["error"] == false) {
      if (fragil === 1) {
        SweetAlert("success", "Guardado. Recuerda imprimir y pegar la etiqueta FRÁGIL en el paquete.");
      }
      SweetAlert("url_success", returned.msg, "orden_detalle.php?id_orden=" + id_orden);
    } else {
      SweetAlert("error", returned.msg);
    }
  });
}



function updateEstadoBulk() {
  const accion = $("#orden_estado").val();

  // === Caso productos: Confirmar venta ===
  if (accion === 'vender') {
    const id_orden = $("#id_orden").val();

    // IDs seleccionados en la tabla de PRODUCTOS
    const seleccionados = new Set(
      $('#lista_productos input[type="checkbox"]:checked')
        .map((_, el) => String($(el).data('id'))).get()
    );

    // IMPORTANTE: no filtres; actualiza el array completo
    productos = productos.map(p => ({
      ...p,
      producto_confirmado: seleccionados.has(String(p.id)) ? 1 : 0
    }));

    // Envía TODO el JSON de productos (no 'productos' como objeto; envíalo como string)
    $.post('../api/v1/fulmuv/ordenes/updateProductos', {
      id_orden: id_orden,
      productos: productos,
      estado: 0
    }, function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "orden_detalle.php?id_orden=" + id_orden)
      } else {
        SweetAlert("error", returned.msg);
      }
    });

    return; // no sigas con la lógica de estados de ÓRDENES
  }
}
