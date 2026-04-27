$(document).ready(function () {


  $.get('../api/v1/fulmuv/ordenes_iso/' + $("#id_orden_iso").val(), {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {

      $(".numero_orden").append(returned.data.id_orden_iso)
      $("#fecha").text(returned.data.created_at)

      var estado = "";
      switch (returned.data.orden_estado) {
        case "procesada":
          estado = `<tr class="alert alert-primary fw-bold">
                                <th class="text-primary-emphasis text-sm-end">Estado:</th>
                                <td class="text-primary-emphasis text-capitalize">procesada<span class="ms-1 fas fas fa-cogs" data-fa-transform="shrink-2"></span></td>
                            </tr>`
          break;
        case "enviada":
          estado = `<tr class="alert alert-info fw-bold">
                                <th class="text-info-emphasis text-sm-end">Estado:</th>
                                <td class="text-info-emphasis text-capitalize">enviada<span class="ms-1 fas fas fa-truck" data-fa-transform="shrink-2"></span></td>
                            </tr>`
          break;
        case "completada":
          estado = `<tr class="alert alert-success fw-bold">
                                <th class="text-success-emphasis text-sm-end">Estado:</th>
                                <td class="text-success-emphasis text-capitalize">completada<span class="ms-1 fas fas fa-check" data-fa-transform="shrink-2"></span></td>
                            </tr>`
          break;
        default:
          break;
      }
      $("#tabla").append(`${estado}`)

      // Objeto para almacenar los productos agrupados
      const productosAgrupados = {};

      // Recorremos las órdenes
      returned.data.ordenes.forEach(orden => {
        // Recorremos los productos dentro de cada orden
        orden.productos.forEach(producto => {
          const nombreProducto = producto.nombre;
          const img_path = producto.img_path;
          const id_orden = orden.id_orden;

          // Si el producto no está en el objeto productosAgrupados, lo inicializamos
          if (!productosAgrupados[nombreProducto]) {
            productosAgrupados[nombreProducto] = {
              img_path: img_path,
              totalCantidad: 0,
              totalValor: 0,
              ordenes: []
            };
          }

          // Sumamos la cantidad total del producto
          const cantidadProducto = parseInt(producto.cantidad);
          const valorProducto = parseFloat(producto.valor);
          productosAgrupados[nombreProducto].totalCantidad += cantidadProducto;
          productosAgrupados[nombreProducto].totalValor += cantidadProducto * valorProducto;

          // Agregamos el detalle de la empresa y la cantidad
          productosAgrupados[nombreProducto].ordenes.push(`Orden #${id_orden} <b class="mb-0 info_orden">(${producto.cantidad} x $${producto.valor})</b>`);

        });

        $("#lista_clientes").append(`<h6 class="mb-2 fw-semi-bold text-nowrap"><a class="text-900"  href='orden_detalle.php?id_orden=${orden.id_orden}'>#${orden.id_orden} ${orden.empresa} - ${orden.sucursal} - ${orden.area}</a></h6>`)
      });

      for (let producto in productosAgrupados) {
        $("#lista_productos").append(`
          <tr>
            <td class="d-flex align-items-center">
              <a>
                <img class="img-fluid rounded-1 me-3 d-none d-md-block" src="${productosAgrupados[producto].img_path}" alt="" width="60">
              </a>
              <div class="flex-1">
                <h6 class="mb-0 text-nowrap">${producto}</h6>
                <p class="mb-0">${productosAgrupados[producto].ordenes.join(', ')}</p>
              </div>
            </td>
            <td class="align-middle text-center">${productosAgrupados[producto].totalCantidad}</td>
            <td class="align-middle text-end">$${productosAgrupados[producto].totalValor.toFixed(2)}</td>
          </tr>
        `);
      }

      $("#subtotal").text("$" + parseFloat(returned.data.total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
      var iva = parseFloat(returned.data.total) * 0.15;
      $("#iva").text("$" + iva.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
      $("#total").text("$" + (parseFloat(returned.data.total) + iva).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))

    }
  });

});


function printDiv(nombreDiv) {

  // buscar en contenido y ocultar estos elementos
  $("#col_valores").hide()
  $("#lista_productos td:nth-child(3)").hide();
  $("#div_totales").hide()
  $(".info_orden").hide()
  var contenido = document.getElementById(nombreDiv).innerHTML;

  var contenidoOriginal = document.body.innerHTML;

  document.body.innerHTML = contenido;
  window.print();
  document.body.innerHTML = contenidoOriginal;
  // Vuelve a mostrar los elementos ocultos
  $("#col_valores").show();
  $("#lista_productos td:nth-child(3)").show();
  $("#div_totales").show();
  $(".info_orden").show();
}
