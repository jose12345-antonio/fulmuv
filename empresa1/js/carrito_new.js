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
  console.log("Cargando carrito...");
  let claveCarrito = `carrito_${id_usuario}_${id_sucursal}`;
  
  // Recuperar el carrito desde localStorage
  let carrito = JSON.parse(localStorage.getItem(claveCarrito)) || [];

  console.log("Carrito cargado:", carrito);

  $("#id_carrito").val(claveCarrito);
  $("#lista_productos").text("");
  $("#items").text("0 (items)");
  $("#total_product").text("(0)");
  $(".carrito_items").text("0");
  $("#total").text("$0");

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
  let claveCarrito = $("#id_carrito").val();
  let carrito = JSON.parse(localStorage.getItem(claveCarrito)) || [];

  if (!Array.isArray(carrito) || carrito.length === 0) {
      console.warn("No hay carrito en localStorage.");
      return;
  }

  let carritoActual = carrito[0];
  let productoEnCarrito = carritoActual.productos.find(item => item.id === productId);

  if (productoEnCarrito) {
      productoEnCarrito.cantidad = cambio;

      if (productoEnCarrito.cantidad <= 0) {
          carritoActual.productos = carritoActual.productos.filter(item => item.id !== productId);
      }

      console.log("Carrito actualizado:", carrito);
      localStorage.setItem(claveCarrito, JSON.stringify([carritoActual]));
      actualizaTotalAPagar();
  } else {
      console.warn("Producto no encontrado en el carrito.");
  }
}

function quitProducto(id_producto) {
  let claveCarrito = $("#id_carrito").val();
  let carrito = JSON.parse(localStorage.getItem(claveCarrito)) || [];

  if (!Array.isArray(carrito) || carrito.length === 0) {
      console.warn("No hay carrito en localStorage.");
      return;
  }

  let carritoActual = carrito[0];

  carritoActual.productos = carritoActual.productos.filter(item => item.id !== id_producto);

  localStorage.setItem(claveCarrito, JSON.stringify([carritoActual]));
  items -= $("#stepperValue-" + id_producto).val();
  $("#item-" + id_producto).remove();
  actualizaTotalAPagar();
}

function actualizaTotalAPagar() {
  let claveCarrito = $("#id_carrito").val();
  let carrito = JSON.parse(localStorage.getItem(claveCarrito)) || [];

  // Verificamos que el carrito tenga la estructura correcta (siempre [0])
  if (!Array.isArray(carrito) || carrito.length === 0) {
      console.warn("No hay carrito en localStorage.");
      $("#items").text("0 (items)");
      $("#total_product").text("(0)");
      $(".carrito_items").text("0");
      $("#total").text("$0.00");
      return;
  }

  let carritoActual = carrito[0]; // Siempre accediendo a la posición 0

  if (!carritoActual.productos || carritoActual.productos.length === 0) {
      console.warn("El carrito no tiene productos.");
      $("#items").text("0 (items)");
      $("#total_product").text("(0)");
      $(".carrito_items").text("0");
      $("#total").text("$0.00");
      carrito = []
      localStorage.setItem($("#id_carrito").val(), JSON.stringify(carrito));
      return;
  }

  let totalPagar = 0;
  let items = 0;

  carritoActual.productos.forEach(item => {
      const producto = productos.find(prod => prod.id_producto === item.id);
      if (producto) {
          totalPagar += item.cantidad * parseFloat(producto.valor);
          items += item.cantidad;
      }
  });

  $("#items").text(items + " (items)");
  $("#total_product").text("(" + carritoActual.productos.length + ")");
  $(".carrito_items").text(carritoActual.productos.length);
  $("#total").text("$" + totalPagar.toFixed(2));

  // Calcular y mostrar subtotal e IVA
  $("#subtotal").text("$" + totalPagar.toFixed(2));
  let iva = totalPagar * 0.15;
  $("#iva").text("$" + iva.toFixed(2));
  $("#total_pagar").text("$" + (totalPagar + iva).toFixed(2));

  // Guardar cambios en `localStorage` asegurando la estructura con `[0]`
  localStorage.setItem(claveCarrito, JSON.stringify([carritoActual]));
}

function generarOrden() {

  let claveCarrito = $("#id_carrito").val();
  let carrito = JSON.parse(localStorage.getItem(claveCarrito)) || [];

  // Verificar que el carrito tenga la estructura correcta (siempre [0])
  if (!Array.isArray(carrito) || carrito.length === 0) {
      console.warn("No hay carrito en localStorage.");
      return SweetAlert("error", "No hay productos en el carrito!");
  }

  let carritoActual = carrito[0]; // Siempre accediendo a la posición 0

  // Verificar si hay productos en el carrito
  if (!carritoActual.productos || carritoActual.productos.length === 0) {
      return SweetAlert("error", "No hay productos en el carrito!");
  }

  let id_sucursal = claveCarrito.split('_').pop(); // Extraer el ID de sucursal desde la clave del carrito

  if (!id_sucursal) {
      return SweetAlert("error", "Debe escoger una sucursal!");
  }

  let area = $("#lista_areas").val();

  // Construir la lista de productos con sus precios
  let carritoConPrecios = carritoActual.productos.map(item => {
      let producto = productos.find(prod => prod.id_producto === item.id);
      return {
          id: item.id,
          cantidad: item.cantidad,
          valor: producto ? producto.valor : 0
      };
  });
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