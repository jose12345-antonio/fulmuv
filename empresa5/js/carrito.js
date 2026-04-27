var id_usuario = $("#id_principal").val()
let carrito;
let productos = [];
var items = 0;
var total = 0;

$(document).ready(function () {
  // localStorage.clear();
  if ($("#id_rol_principal").val() == 2) {
    $("#searh_empresa").empty()
    $.get('../api/v1/fulmuv/empresas/' + $("#id_empresa").val() + '/sucursales', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(sucursal => {
          $("#lista_sucursales").append(`
            <option value="${sucursal.id_sucursal}">${sucursal.nombre}</option>
          `);
        });
        $("#lista_sucursales").trigger("change")
      }
    });
  } else if ($("#id_rol_principal").val() == 5) {
    $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(empresa => {
          $("#lista_empresas").append(`
            <option value="${empresa.id_empresa}">${empresa.nombre}</option>
          `);
        });
        buscarSucursales();
      }
    });
  } else {
    $("#searh_sucursal").empty()
    $("#searh_empresa").empty()
    console.log($("#id_empresa").val())
    mostrarCarrito($("#id_empresa").val())
  }
});

function buscarSucursales(){
  $("#lista_sucursales").text("");
  $.get('../api/v1/fulmuv/empresas/' + $("#lista_empresas").val() + '/sucursales', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      returned.data.forEach(sucursal => {
        $("#lista_sucursales").append(`
          <option value="${sucursal.id_sucursal}">${sucursal.nombre}</option>
        `);
      });
      $("#lista_sucursales").trigger("change")
    }
  });
}

function mostrarCarrito(id_sucursal) {
  console.log("asdasd")
  carrito = JSON.parse(localStorage.getItem(`carrito_${id_usuario}_${id_sucursal}`)) || []
  $("#id_carrito").val(`carrito_${$("#id_principal").val()}_${id_sucursal}`)
  $("#lista_productos").text("");
  items = 0
  $("#items").text(items + " (items)")
  $("#total_product").text("(0)")
  $(".carrito_items").text("0")
  $("#total").text("$0")

  if (carrito.length > 0) {
    carrito = carrito [0]
    // $.post('../api/v1/fulmuv/sucursales/' + id_sucursal + '/catalogo', {
    $.post('../api/v1/fulmuv/productos/general', {
      ids_productos: carrito.productos.map(item => item.id),
      id_catalogo: carrito.id_catalogo
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      console.log(returned)
      if (returned.data.length) {
        productos = returned.data;

        carrito.productos.forEach(product => {
          var productId = parseInt(product.id);
          items += product.cantidad
          const arrayProducto = productos.find((pro) => pro.id_producto === productId);

          $("#lista_productos").append(`
            <div class="row gx-x1 mx-0 align-items-center border-bottom border-200" id="item-${productId}">
              <div class="col-8 py-3 px-x1">
                  <div class="d-flex align-items-center">
                      <a>
                          <img class="img-fluid rounded-1 me-3 d-none d-md-block" src="${arrayProducto.img_path}" alt="" width="60" />
                      </a>
                      <div class="flex-1">
                          <h5 class="fs-9 text-900">${arrayProducto.nombre}</h5>
                          <div class="fs-11 fs-md--1"><a class="text-danger" onclick="quitProducto(${productId})">Quitar</a></div>
                      </div>
                  </div>
              </div> 
              <div class="col-4 py-3 px-x1">
                  <div class="row align-items-center fs-10">
                      <div class="col-md-8 d-flex justify-content-end justify-content-md-center order-1 order-md-0">
                          <div>
                              <div class="input-group input-group-sm flex-nowrap" data-quantity="data-quantity">
                                  <button onclick="decrement(${productId})" class="btn btn-sm btn-outline-secondary border-300 px-2 shadow-none" data-type="minus">-</button>
                                  <input id="stepperValue-${productId}" class="form-control text-center px-2 input-spin-none" type="number" min="1" value="${product.cantidad}" style="width: 50px" />
                                  <button onclick="increment(${productId})" class="btn btn-sm btn-outline-secondary border-300 px-2 shadow-none" data-type="plus">+</button>
                              </div>
                          </div>
                      </div>
                      <div class="col-md-4 text-end ps-0 order-0 order-md-1 mb-2 mb-md-0 text-600 carrito">$${arrayProducto.valor}</div>
                  </div>
              </div>
            </div> 
          `);
        });

        actualizaTotalAPagar()
        if ($("#id_rol_principal").val() == 3) {
          $(".carrito").remove()
        }

        $("#lista_areas").empty()
        $.get('../api/v1/fulmuv/sucursales/' + id_sucursal, {}, function (returnedData) {
          var returned = JSON.parse(returnedData)
          if (returned.error == false) {
            $("#lista_areas").append(`<option value="Todas">Todas</option>`);
            returned.data.areas.forEach(area => {
              $("#lista_areas").append(`
                  <option value="${area.id_area}">${area.nombre}</option>
                  `);
            });
          } else {
            SweetAlert("error", "Ocurrió un error al cargar las áreas de esta sucursal!")
          }
        });

      } else {
        SweetAlert("error", "Ocurrió un error al cargar los productos!")
      }
    });

  }else{
    console.log("no")
  }
}

function increment(id_producto) {
  var input = document.getElementById('stepperValue-' + id_producto);
  input.value = parseInt(input.value) + 1;
  items += 1
  actualizarCantidad(id_producto, parseInt(input.value));
}

function decrement(id_producto) {
  var input = document.getElementById('stepperValue-' + id_producto);
  if (parseInt(input.value) > parseInt(input.min)) {
    input.value = parseInt(input.value) - 1;
    items -= 1
    actualizarCantidad(id_producto, parseInt(input.value));
  }
}

function actualizarCantidad(productId, cambio) {
  const productoEnCarrito = carrito.productos.find(item => item.id === productId);

  if (productoEnCarrito) {
    productoEnCarrito.cantidad = cambio;

    // Si la cantidad es 0 o menor, eliminamos el producto del carrito
    if (productoEnCarrito.cantidad <= 0) {
      carrito = carrito.productos.filter(item => item.id !== productId);
    }
    
    // Guardar el carrito actualizado en localStorage
    localStorage.setItem($("#id_carrito").val(), JSON.stringify(carrito));
    actualizaTotalAPagar()
  }
}

function quitProducto(id_producto) {
  carrito.productos = carrito.productos.filter(item => item.id !== id_producto);
  localStorage.setItem($("#id_carrito").val(), JSON.stringify(carrito));
  items -= $("#stepperValue-" + id_producto).val()
  $("#item-" + id_producto).remove();
  actualizaTotalAPagar()

}

function actualizaTotalAPagar() {
  let totalPagar = 0;

  if(carrito.productos.length){
    carrito.productos.forEach(item => {
      const producto = productos.find(prod => prod.id_producto === item.id);
      if (producto) {
        totalPagar += item.cantidad * parseFloat(producto.valor);
      }
    });
    $("#items").text(items + " (items)")
    $("#total_product").text("(" + carrito.productos.length + ")")
    $(".carrito_items").text(carrito.productos.length)
  }else{
    $("#items").text(items + " (items)")
    $("#total_product").text("(0)")
    $(".carrito_items").text(0)
    carrito = []
    localStorage.setItem($("#id_carrito").val(), JSON.stringify(carrito));
  }

  $("#total").text("$" + totalPagar)
  $("#subtotal").text("");
  $("#subtotal").text("$" + totalPagar.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
  var iva = totalPagar * 0.15;
  $("#iva").text("");
  $("#iva").text("$" + iva.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
  $("#total_pagar").text("");
  $("#total_pagar").text("$" + (totalPagar + iva).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
  total = totalPagar
}

function generarOrden() {

  if (!carrito.productos.length) {
    return SweetAlert("error", "No hay productos en el carrito!");
  }
  var id_sucursal = $("#id_carrito").val().split('_').pop()

  if (id_sucursal == "" || id_sucursal == null) {
    return SweetAlert("error", "Debe escoger una sucursal!");
  }
  var area = $("#lista_areas").val()
  var carritoConPrecios = carrito.productos.map(item => {
    var producto = productos.find(prod => prod.id_producto === item.id);
    return {
      id: item.id,
      cantidad: item.cantidad,
      valor: producto ? producto.valor : 0
    };
  });
  console.log(total)
  $.post('../api/v1/fulmuv/ordenes/create', {
    id_sucursal: id_sucursal,
    area: area,
    productos: carritoConPrecios,
    total: total,
    id_usuario: id_usuario
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      carrito = []
      localStorage.setItem($("#id_carrito").val(), JSON.stringify(carrito));
      SweetAlert("url_success", returned.msg, "ordenes.php")
    } else {
      SweetAlert("error", returned.msg)
    }
  });
}