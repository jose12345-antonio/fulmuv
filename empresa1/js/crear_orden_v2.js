let productos = [];
var total_items = 0;
let carrito = [];

$(document).ready(function () {

  $.get('../api/v1/fulmuv/productos/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      productos = returned.data;
      returned.data.forEach(producto => {
        $("#lista_productos").append(`
          <option value="${producto.id_producto}">${producto.nombre}</option>
        `);
      });
    }
  });
});

function addProducto(){
  var prod = $("#lista_productos").val();
  if(prod != "-1"){

    productos.forEach(producto => {
      if(producto.id_producto == prod){
        $("#productos_agregados").append(`
          <div class="row gx-x1 mx-0 align-items-center border-bottom border-200" id="item-${prod}">
            <div class="col-8 py-3 px-x1">
              <div class="d-flex align-items-center"><img class="img-fluid rounded-1 me-3 d-none d-md-block" src="${producto.img_path}" alt="" width="60" />
                <div class="flex-1">
                  <h5 class="fs-9 text-900">${producto.nombre} (${producto.descripcion})</h5>
                  <div class="fs-11 fs-md--1"><a class="text-danger" onclick="quitProducto(${prod})">Quitar</a></div>
                </div>
              </div>
            </div>
            <div class="col-4 py-3 px-x1">
              <div class="row align-items-center">
                <div class="col-md-12 d-flex justify-content-end justify-content-md-center order-1 order-md-0">
                  <div>
                    <div class="input-group input-group-sm flex-nowrap" data-quantity="data-quantity">
                      <button class="btn btn-sm btn-outline-secondary border-300 px-2 shadow-none" data-type="minus" onclick="decrement(${prod})">-</button>
                      <input id="stepperValue-${prod}" class="form-control text-center px-2 input-spin-none" type="number" min="1" value="1" style="width: 50px" />
                      <button class="btn btn-sm btn-outline-secondary border-300 px-2 shadow-none" data-type="plus" onclick="increment(${prod})">+</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>  
        `);
      }
      
    });

   //sumar un nuevo item
   total_items = total_items + 1;
   $("#total_items").text("");
   $("#total_items").append(`
    ${total_items} (items)
   `);

   actualizarCarrito(parseInt(prod), 1);

    //deshabilitar opción agregada
    
    
    $("#lista_productos").val("-1").trigger("change")
    
    var select = document.getElementById('lista_productos');
    var opciones = select.options;
    for (var i = 0; i < opciones.length; i++) {
      if (opciones[i].value == prod) {
        opciones[i].disabled = true;
        break;
      }
    }
    
  }
}

function increment(id_producto) {
  var input = document.getElementById('stepperValue-'+id_producto);
  input.value = parseInt(input.value) + 1;
  actualizarCarrito(id_producto, parseInt(input.value));
}

function decrement(id_producto) {
  var input = document.getElementById('stepperValue-'+id_producto);
  if (parseInt(input.value) > parseInt(input.min)) {
    input.value = parseInt(input.value) - 1;
    actualizarCarrito(id_producto, parseInt(input.value));
  }
}

function quitProducto(id_producto){
  carrito = carrito.filter(item => item.id !== id_producto);
  $("#item-"+id_producto).remove();
   //restar un item
   total_items = total_items - 1;
   $("#total_items").text("");
   $("#total_items").append(`
    ${total_items} (items)
  `);

  var select = document.getElementById('lista_productos');
  var opciones = select.options;
  for (var i = 0; i < opciones.length; i++) {
    if (opciones[i].value == id_producto) {
      opciones[i].disabled = false;
      break;
    }
  }
}

// Función para actualizar la cantidad de un producto en el carrito
function actualizarCarrito(prod, cantidad) {
  // Buscar si el producto ya está en el carrito
  let productoExistente = carrito.find(item => item.id === prod);

  if (productoExistente) {
      // Si el producto ya está en el carrito, actualiza la cantidad
      productoExistente.cantidad = cantidad;
  } else {
      // Si el producto no está en el carrito, añádelo
      carrito.push({ id: prod, cantidad: cantidad });
  }
}

function saveOrden(){
  if(carrito.length > 0){
    console.log(carrito)
    $.post('../api/v1/fulmuv/orden/create', {
      data: carrito,
      id_usuario: id_principal
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "crear_orden.php")
      }else{
        SweetAlert("error", returned.msg)
      }
    });
  }else{
    SweetAlert("error","No hay productos en la orden!!")
  }
}