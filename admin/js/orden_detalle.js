let establecimientos = [];
var id_empresa = document.getElementById("id_empresa")?.value;

$(document).ready(function () {

  $.get('../api/v1/fulmuv/getPermisosByUser/' + $("#id_principal").val(), {}, function (returnedDat) {
    permisosData = JSON.parse(returnedDat);
    if (permisosData.error == false) {
      var permiso = permisosData.data.filter(permiso => (permiso.permiso == "Ordenes"))[0];
      console.log(permiso)
      if (permiso.valor == "true") {
        levels = permiso.levels
        $.get('../api/v1/fulmuv/ordenes/' + $("#id_orden").val(), {}, function (returnedData) {
          var returned = JSON.parse(returnedData)
          if (returned.error == false) {

            $("#numero_orden").append(returned.data.id_orden)
            $("#nombre_empresa").append(returned.data.empresa)
            $("#nombre_sucursal").append(returned.data.sucursal)
            $("#nombre_area").append(returned.data.area)
            $("#fecha").text(returned.data.created_at)

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
            returned.data.productos.forEach(function (producto, index) {
              $("#lista_productos").append(`
                <tr class="border-200">
                  <td class="d-flex align-items-center">
                    <a>
                      <img class="img-fluid rounded-1 me-3 d-none d-md-block" src="${producto.img_path}" alt="" width="60">
                    </a>
                    <div class="flex-1">
                      <h6 class="mb-0 text-nowrap">${producto.nombre}</h6>
                    </div>
                  </td>
                  <td class="align-middle text-center">${producto.cantidad}</td>
                  <td class="align-middle text-end carrito">$${producto.valor}</td>
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

          }
        });
      }
    }
  });
});