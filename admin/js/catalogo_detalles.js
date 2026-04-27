var id_catalogo = document.getElementById("id_catalogo")?.value;
var id_empresa = document.getElementById("id_empresa")?.value;
let productos = [];
var tipo_membresia = "";
var articulos = 0;
$(document).ready(function () {
  

      $.get('../api/v1/fulmuv/catalogos/' + id_catalogo + '/productos', {}, function (returnedData) {
        var returned = JSON.parse(returnedData)
        
        if (returned.error == false) {
          var cat = returned.data
          $("#nombre").val(cat.nombre)
          $("#descripcion").val(cat.descripcion)
          $("#lista_empresas").append(`<option value="${cat.id_empresa}">${cat.empresa}</option>`);
          $("#lista_sucursales").append(`<option value="${cat.id_sucursal}">${cat.sucursal}</option>`);

          productos = returned.productos_all;
          returned.productos_all.forEach(producto => {
            $("#lista_productos").append(`
              <option value="${producto.id_producto}">${producto.nombre}</option>
            `);
          });

          $("#lista_productos option").prop("disabled", false);
          cat.productos.forEach(prod => {
            $("#lista_productos").find(`option[value='${prod.id_producto}']`).prop("disabled", true);

            // Buscar el primer archivo tipo 'imagen'
            const archivoImagen = prod.archivos?.find(archivo => archivo.tipo === 'imagen');

            $("#productos_agregados").append(`
               <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3 mb-2 item" id="item-${prod.id_producto}">
                 <div class="card h-100">
                     <div class="card-body d-flex align-items-center px-2 h-100">
                         <img class="img-fluid rounded-1 me-2" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" alt="" height="60" width="60" />
                         <div class="col">
                             <!--div class="col-12 text-wrap fs-9 h5 mb-2">
                                ${prod.nombre}
                             </div-->
                             <div class="form-check">
                                <input class="form-check-input" id="check-${prod.id_producto}" type="checkbox" ${prod.default == "true" ? 'checked' : ''} />
                                <label class="form-check-label col-12 text-wrap fs-9 h5" for="check-${prod.id_producto}">${prod.nombre}</label>
                             </div>
                             <div class="d-flex mt-1">
                                 <div class="input-group me-2 input-group-sm">
                                     <span class="input-group-text">$</span>
                                     <input class="form-control" type="number" min="0" value="${prod.valor}" />
                                 </div>
                                 <a class="btn btn-sm btn-falcon-default hover-primary" onclick="quitProducto(${prod.id_producto})" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
                                     <span class="fas fa-trash text-danger" data-fa-transform="down-2"></span>
                                 </a>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             `);
          });

          tipo_membresia = cat.membresia.tipo;
          articulos = cat.membresia.numero;



          // $.get('../api/v1/fulmuv/productos/all/'+returned.data.id_empresa, {}, function (returnedDat) {
          //   var returne = JSON.parse(returnedDat)
          //   if (returne.error == false) {
          //     productos = returne.data;
          //     returne.data.forEach(producto => {
          //       $("#lista_productos").append(`
          //         <option value="${producto.id_producto}">${producto.nombre}</option>
          //       `);
          //     });

          //   }
          // });


        }
      });
});

function addProducto() {
  var prod = $("#lista_productos").val();
  if (prod != "-1") {
    productos.forEach(producto => {
      if (producto.id_producto == prod) {

        // Buscar el primer archivo tipo 'imagen'
        const archivoImagen = producto.archivos?.find(archivo => archivo.tipo === 'imagen');

        $("#productos_agregados").append(`
          <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3 mb-2 item" id="item-${prod}">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center px-2 h-100">
                    <img class="img-fluid rounded-1 me-2" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" alt="" height="60" width="60" />
                    <div class="col">
                        <!--div class="col-12 text-wrap fs-9 h5 mb-2">
                        ${producto.nombre}
                        </div-->
                        <div class="form-check">
                          <input class="form-check-input" id="check-${prod}" type="checkbox" checked>
                          <label class="form-check-label col-12 text-wrap fs-9 h5" for="check-${prod}">${producto.nombre}</label>
                        </div>
                        <div class="d-flex mt-1">
                            <div class="input-group me-2 input-group-sm">
                                <span class="input-group-text">$</span>
                                <input class="form-control" type="number" min="0" value="${producto.precio_referencia}" />
                            </div>
                            <a class="btn btn-sm btn-falcon-default hover-primary" onclick="quitProducto(${prod})" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
                                <span class="fas fa-trash text-danger" data-fa-transform="down-2"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `);
      }
    });
    $("#lista_productos").val("-1").trigger("change").find(`option[value='${prod}']`).prop("disabled", true);
  }
}

function quitProducto(id_producto) {
  $("#item-" + id_producto).remove();
  $("#lista_productos").find(`option[value='${id_producto}']`).prop("disabled", false);
}

function updateCatalogo() {
  var descripcion = $("#descripcion").val();

  if (!descripcion) {
    return SweetAlert("error", "Todos los campos son obligatorios!!!");
  }

  var catalogo = [];
  var checkCount = 0;

  var allInputsFilled = $(".item").toArray().every(function (item) {
    var id_producto = $(item).attr('id').split('-')[1];
    var valor = $(item).find('input[type="number"]').val();
    var check = $("#check-" + id_producto).is(':checked')

    if (!valor) return false;
    catalogo.push({ id_producto: id_producto, valor: valor, default: check  });

    if (check) {
      checkCount++;  // Incrementamos si este proveedor tiene el check true
    }

    return true;
  });

  if (!allInputsFilled) {
    return SweetAlert("error", "Todos los productos deben tener un valor.");
  }
  if (catalogo.length === 0) {
    return SweetAlert("error", "Debes agregar al menos un producto.");
  }

  if(tipo_membresia == "articulos"){
    if (checkCount > articulos) {
      return SweetAlert("error", "Solo puedes seleccionar un máximo de "+articulos+" artículos.");
    }
  }

  $.post('../api/v1/fulmuv/catalogos/update', {
    id_catalogo: id_catalogo,
    descripcion: descripcion,
    productos: catalogo
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      SweetAlert("url_success", returned.msg, "catalogos.php")
    } else {
      SweetAlert("error", returned.msg)
    }
  });

}