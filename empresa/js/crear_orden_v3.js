let productos = [];
let categorias = [];
let carrito;

// carrito = {
//   id_catalogo: null, // ID del catálogo correspondiente
//   productos: {} // Contendrá productos como objetos con sus respectivas cantidades
// };

$(document).ready(function () {
  if ($("#id_rol_principal").val() == 2) {
    $("#searh_empresa").empty()
    $("#searh_catalogo").empty()
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
  } else if($("#id_rol_principal").val() == 5){
    $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(empresa => {
          $("#lista_empresas").append(`
            <option value="${empresa.id_empresa}">${empresa.nombre}</option>
          `);
        });
        buscarSucursales();
        //$("#lista_empresas").trigger("change")
      }
    });
  }else {
    $("#searh_empresa").empty()
    $("#searh_sucursal").empty()
    $("#searh_catalogo").empty()
    getCatalogo($("#id_empresa").val())

  }
  $.get('../api/v1/fulmuv/categorias/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      categorias = returned.data;
      returned.data.forEach(categoria => {
        $("#categorias").append(`
          <option value="${categoria.id_categoria}">${categoria.nombre}</option>
        `);
      })
      llenarSubCategria();
    }
  });
});

function buscarSucursales(){
  document.getElementById('lista_sucursales').onchange = null;
  $('#lista_sucursales').off('change');
  $('#lista_sucursales').on('change', obtenerCatalogos);
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
      obtenerCatalogos();
    }
  });
}

function llenarSubCategria() {
  var categoria = parseInt($("#categorias").val());
  $("#sub_categorias").text("")
  $("#sub_categorias").append(`
    <option value="-1">Todas</option>
  `)
  if (categoria != -1) {
    const arrayCategoria = categorias.find((cat) => cat.id_categoria === categoria);
    arrayCategoria.sub_categorias.forEach(sub_categoria => {
      $("#sub_categorias").append(`
        <option value="${sub_categoria.id_sub_categoria}">${sub_categoria.nombre}</option>
      `);
    });
  }
  filtrarCategoria()
}

function filtrarCategoria() {
  let productosFiltrados = productos;
  // Filtrar por categoría
  if ($("#categorias").val() != "-1") {
    productosFiltrados = productosFiltrados.filter(producto => producto.categoria == $("#categorias").val());
  }

  // Filtrar por subcategoría
  if ($("#sub_categorias").val() != "-1") {
    productosFiltrados = productosFiltrados.filter(producto => producto.sub_categoria == $("#sub_categorias").val());
  }

  $("#tickets-card-fallback").addClass("d-none");
  $("#lista_productos").empty();

  if (productosFiltrados.length) {
    productosFiltrados.forEach(producto => {
      let tags = "";
      if (producto.tags && producto.tags.split(",").length) {
        producto.tags.split(",").forEach(tag => {
          tags += `<span class="badge rounded-pill bg-success me-2"><span class="fas fa-tags"></span> ${tag}</span>`;
        });
      }
      $("#lista_productos").append(`
          <div class="mb-4 col-md-6 col-lg-4">
            <div class="border rounded-1 d-flex flex-column justify-content-between pb-3 h-auto">
              <div class="overflow-hidden">
                <div class="position-relative rounded-top overflow-hidden">
                  <a class="d-block">
                    <img class="img-fluid rounded-top" src="${producto.img_path}" alt="" width="350"/>
                  </a>
                </div>
                <hr class="my-0">
                <div class="px-3 py-2">
                  <h5 class="fs-9"><a class="text-1100 producto">${producto.nombre}</a></h5>
                  <p class="fs-10 mb-0 text-truncate-2 text-500 descripcion">${producto.descripcion}</p>
                  <a href="#" class="ver-mas fs-10 mb-1">Ver más</a>
                  <h5 class="precio fs-md-7 text-warning mb-0 mt-2 d-flex align-items-center mb-1">$${producto.valor}</h5>
                  <p class="fs-10 mb-1"><strong>Categoría:</strong> ${producto.nombre_categoria}</p>
                  <p class="fs-10 mb-1"><strong>Sub-categoría:</strong> ${producto.nombre_sub_categoria}</p>
                </div>
                <div class="d-flex flex-between-center px-3 mb-1 align-items-start">
                  <div class="tag">
                    ${tags}
                  </div>
                  <div class="d-flex">
                    <a class="btn btn-sm btn-falcon-default me-2" href="${producto.ficha_tecnica}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Ficha técnica">
                      <span class="far fa-file-pdf"></span>
                    </a>
                    <a onclick="agregarAlCarrito(${producto.id_producto})" class="btn btn-sm btn-falcon-default" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar al carrito">
                      <span class="fas fa-cart-plus"></span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `);
    });
    if ($("#id_rol_principal").val() == 3) {
      $(".precio").remove()
    }
    $("#ticketsTable").attr("data-list", '{"valueNames":["producto","tag"],"page":9,"pagination":true,"fallback":"tickets-card-fallback"}');
    listInit();
  } else {
    $("#tickets-card-fallback").removeClass("d-none");
  }

}

function agregarAlCarrito(productId) {
  // Buscar si el producto ya está en el carrito
  const productoEnCarrito = carrito.find(item => item.id === productId);
  // Si no está, lo agregamos con cantidad 1
  toastr.options.timeOut = 1500; // Configuración temporal para esta notificación
  if (!productoEnCarrito) {
    carrito.push({ id: productId, cantidad: 1 });
    toastr.success("Producto agregado!");
  } else {
    toastr.warning("El producto ya existe en el carrito!");
  }
  $(".carrito_items").text(carrito.length)
  // Guardar el carrito actualizado en localStorage
  localStorage.setItem($("#id_carrito").val(), JSON.stringify(carrito));

}

function getCatalogo(id_sucursal) {

  carrito = JSON.parse(localStorage.getItem(`carrito_${$("#id_principal").val()}_${id_sucursal}`)) || []
  $("#id_carrito").val(`carrito_${$("#id_principal").val()}_${id_sucursal}`)
  $(".carrito_items").text(carrito.length)

  $.get('../api/v1/fulmuv/sucursales/' + id_sucursal + '/catalogo', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    $("#lista_productos").empty()
    if (returned.error == false) {

      productos = returned.data.productos;
      if (productos.length) {
        returned.data.productos.forEach(producto => {
          tags = ""
          if (producto.tags != "" && producto.tags.split(",").length) {
            producto.tags.split(",").forEach(tag => {
              tags += `<span class="badge rounded-pill bg-success me-2"><span class="fas fa-tags"></span> ${tag}</span >`
            });
          }
          $("#lista_productos").append(`
             <div class="mb-4 col-md-6 col-lg-4">
               <div class="border rounded-1 d-flex flex-column justify-content-between pb-3 h-auto">
                 <div class="overflow-hidden">
                   <div class="position-relative rounded-top overflow-hidden">
                     <a class="d-block">
                       <img class="img-fluid rounded-top" src="${producto.img_path}" alt="" width="350"/>
                     </a>
                   </div>
                   <hr class="my-0">
                   <div class="px-3 py-2">
                     <h5 class="fs-9"><a class="text-1100 producto">${producto.nombre}</a></h5>
                     <p class="fs-10 mb-0 text-truncate-2 text-500 descripcion">${producto.descripcion}</p>
                      <a href="#" class="ver-mas fs-10 mb-1">Ver más</a>
                     <h5 class="precio fs-md-7 text-warning mb-0 mt-2 d-flex align-items-center mb-1"> $${producto.valor}</h5>
                     <p class="fs-10 mb-1"><strong>Categoría:</strong> ${producto.nombre_categoria}</p>
                     <p class="fs-10 mb-1"><strong>Sub-categoría:</strong> ${producto.nombre_sub_categoria}</p>
                   </div>
                   <div class="d-flex flex-between-center px-3 mb-1 align-items-start">
                     <div class="tag"> 
                       ${tags}
                     </div>
                     <div class="d-flex">
                       <a class="btn btn-sm btn-falcon-default me-2" href="${producto.ficha_tecnica}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Ficha técnica">
                         <span class="far fa-file-pdf"></span>
                       </a>
                       <a onclick="agregarAlCarrito(${producto.id_producto})" class="btn btn-sm btn-falcon-default" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar al carrito">
                         <span class="fas fa-cart-plus"></span>
                       </a>
                     </div>
                   </div>
                 </div>
               </div>
             </div>
           `);
        });

        if ($("#id_rol_principal").val() == 3) {
          $(".precio").remove()
        }
        $("#tickets-card-fallback").addClass("d-none");
        $("#ticketsTable").attr("data-list", '{"valueNames":["producto","tag"],"page":9,"pagination":true,"fallback":"tickets-card-fallback"}');
        listInit()
        $("#categorias").val("-1").trigger("change")
      } else {
        $("#tickets-card-fallback").removeClass("d-none");
        return SweetAlert("error", "Este catalogo no tiene productos asignados!");
      }

    }
  });

}

function getCatalogoId(id_catalogo){
  var id_sucursal = $("#lista_sucursales").val();
  carrito = JSON.parse(localStorage.getItem(`carrito_${$("#id_principal").val()}_${id_sucursal}`)) || []
  $("#id_carrito").val(`carrito_${$("#id_principal").val()}_${id_sucursal}`)
  $(".carrito_items").text(carrito.length)

  $.get('../api/v1/fulmuv/catalogos/' + id_catalogo, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    $("#lista_productos").empty()
    if (returned.error == false) {

      productos = returned.data.productos;
      if (productos.length) {
        returned.data.productos.forEach(producto => {
          tags = ""
          if (producto.tags != "" && producto.tags.split(",").length) {
            producto.tags.split(",").forEach(tag => {
              tags += `<span class="badge rounded-pill bg-success me-2"><span class="fas fa-tags"></span> ${tag}</span >`
            });
          }
          $("#lista_productos").append(`
             <div class="mb-4 col-md-6 col-lg-4">
               <div class="border rounded-1 d-flex flex-column justify-content-between pb-3 h-auto">
                 <div class="overflow-hidden">
                   <div class="position-relative rounded-top overflow-hidden">
                     <a class="d-block">
                       <img class="img-fluid rounded-top" src="${producto.img_path}" alt="" width="350"/>
                     </a>
                   </div>
                   <hr class="my-0">
                   <div class="px-3 py-2">
                     <h5 class="fs-9"><a class="text-1100 producto">${producto.nombre}</a></h5>
                     <p class="fs-10 mb-0 text-truncate-2 text-500 descripcion">${producto.descripcion}</p>
                      <a href="#" class="ver-mas fs-10 mb-1">Ver más</a>
                     <h5 class="precio fs-md-7 text-warning mb-0 mt-2 d-flex align-items-center mb-1"> $${producto.valor}</h5>
                     <p class="fs-10 mb-1"><strong>Categoría:</strong> ${producto.nombre_categoria}</p>
                     <p class="fs-10 mb-1"><strong>Sub-categoría:</strong> ${producto.nombre_sub_categoria}</p>
                   </div>
                   <div class="d-flex flex-between-center px-3 mb-1 align-items-start">
                     <div class="tag"> 
                       ${tags}
                     </div>
                     <div class="d-flex">
                       <a class="btn btn-sm btn-falcon-default me-2" href="${producto.ficha_tecnica}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Ficha técnica">
                         <span class="far fa-file-pdf"></span>
                       </a>
                       <a onclick="agregarAlCarrito(${producto.id_producto})" class="btn btn-sm btn-falcon-default" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar al carrito">
                         <span class="fas fa-cart-plus"></span>
                       </a>
                     </div>
                   </div>
                 </div>
               </div>
             </div>
           `);
        });

        if ($("#id_rol_principal").val() == 3) {
          $(".precio").remove()
        }
        $("#tickets-card-fallback").addClass("d-none");
        $("#ticketsTable").attr("data-list", '{"valueNames":["producto","tag"],"page":9,"pagination":true,"fallback":"tickets-card-fallback"}');
        listInit()
        $("#categorias").val("-1").trigger("change")
      } else {
        $("#tickets-card-fallback").removeClass("d-none");
        return SweetAlert("error", "Este catalogo no tiene productos asignados!");
      }

    }
  });
}

function obtenerCatalogos(){
  var id_sucursal = $("#lista_sucursales").val();
  $.get('../api/v1/fulmuv/sucursales/' + id_sucursal + '/catalogoVendedores', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if(returned.error == false){
      $("#lista_catalogos").text("");
      returned.data.forEach(catalogo => {
        $("#lista_catalogos").append(`
          <option value="${catalogo.id_catalogo}">${catalogo.nombre}</option>
        `);
      });
      $("#lista_catalogos").trigger("change")
    }
  });
}

$(document).on('click', '.ver-mas', function (event) {
  event.preventDefault();

  var $parent = $(this).prev('.text-truncate-2');
  $parent.removeClass('text-truncate-2'); // Remueve la clase para mostrar todo el texto

  $(this).text('Ver menos').addClass('ver-menos').removeClass('ver-mas'); // Cambia el enlace a "Ver menos"
});

$(document).on('click', '.ver-menos', function (event) {
  event.preventDefault();

  var $parent = $(this).prev('.text-500');
  $parent.addClass('text-truncate-2'); // Agrega la clase para truncar el texto nuevamente

  $(this).text('Ver más').addClass('ver-mas').removeClass('ver-menos'); // Cambia el enlace a "Ver más"
});

// $(document).on('mouseenter', '.descripcion', function () {
//   $(this).removeClass('text-truncate-2'); // Elimina la clase para mostrar todo el texto
// });

// $(document).on('mouseleave', '.descripcion', function () {
//   $(this).addClass('text-truncate-2'); // Vuelve a truncar el texto cuando se quita el mouse
// });



