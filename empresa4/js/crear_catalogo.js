let productos = [];
var tipo_membresia = "";
let membresia;
var articulos = 0;

let productos_agregados = [];

$(document).ready(function () {
  $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      returned.data.forEach(empresa => {
        $("#lista_empresas").append(`
          <option value="${empresa.id_empresa}">${empresa.nombre}</option>
        `);
      });
      if ($("#id_rol_principal").val() == 2) {
        $("#lista_empresas").val($("#id_empresa").val()).trigger("change")
        document.getElementById('lista_empresas').disabled = true;
      }
    }
  });
  llenarCategorias();
});

$("#subirArchivo").change(function (event) {
  let file = event.target.files[0];
  if (!file) return;
  let reader = new FileReader();
  reader.onload = function (e) {
    let data = new Uint8Array(e.target.result);
    let workbook = XLSX.read(data, { type: "array" });
    let sheetName = workbook.SheetNames[0]; // Tomar la primera hoja
    let sheet = workbook.Sheets[sheetName];
    let jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });
    // Procesar los datos del Excel
    procesarExcel(jsonData);
  };
  reader.readAsArrayBuffer(file);
});

function procesarExcel(data) {
  console.log(data)
  if (data.length < 2) {
    SweetAlert("error", "El archivo Excel está vacío o no tiene productos.");
    return;
  }
  data.slice(1).forEach(row => {
    let codigoExcel = row[0]; // Código del producto en el Excel
    let precioExcel = row[2]; // Precio del producto en el Excel
    let productoEncontrado = productos.find(p => p.codigo == codigoExcel);
    if (productoEncontrado) {
      agregarProductoDesdeExcel(productoEncontrado, precioExcel);
    }else{
      console.log("no se han encontrado productos");
    }
  });
}

function llenarCategorias(){
  $.get('../api/v1/fulmuv/categorias/', {tipo: $("#lista_tipo").val()}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    console.log(returned)
    if (returned.error == false) {
      $("#lista_categorias").empty()
      $("#lista_categorias").append(`
        <option value="">Seleccione una categoría</option>
        <option value="Todas">Todas</option>
      `);
      returned.data.forEach(categoria => {
        $("#lista_categorias").append(`
          <option value="${categoria.id_categoria}">${categoria.nombre}</option>
        `);
      });
    }
  });
}

function getSucursales() {
  membresia = "";
  if ($("#lista_empresas").val() != "") {
    var id_empresa = $("#lista_empresas").val()
    $("#lista_sucursales").empty()
    $.get('../api/v1/fulmuv/empresas/' + id_empresa + '/sucursales', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        returned.data.forEach(sucursal => {
          $("#lista_sucursales").append(`
          <option value="${sucursal.id_sucursal}">${sucursal.nombre}</option>
          `);
        });
        membresia = returned.membresia;
      }
    });
    $("#lista_productos").text("")
    $("#lista_productos").append(`
      <option value="-1">Seleccione producto</option>
    `);
    $.get('../api/v1/fulmuv/productos/allTipo/'+id_empresa+'/'+$("#lista_tipo").val(), {}, function (returnedData) {
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
    $("#productos_agregados").text("")
  }
}

function addProducto() {
  var prod = $("#lista_productos").val();
  if (prod != "-1") {
    // Verificar si ya fue agregado
    var pr = parseInt(prod);
    if (!productos_agregados.includes(pr)) {
      productos.forEach(producto => {
        if (producto.id_producto == prod) {
          // Agregar al array
          
          productos_agregados.push(pr);

          agregarProductoDesdeExcel(producto, producto.precio_referencia);
          // Agregar al DOM
          // $("#productos_agregados").append(`
          //   <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3 mb-2 item" id="item-${prod}">
          //     <div class="card h-100">
          //         <div class="card-body d-flex align-items-center px-2 h-100">
          //             <img class="img-fluid rounded-1 me-2" src="${producto.img_path}" alt="" height="60" width="60" />
          //             <div class="col">
          //                 <div class="form-check">
          //                   <input class="form-check-input" id="check-${prod}" type="checkbox" checked>
          //                   <label class="form-check-label col-12 text-wrap fs-9 h5" for="check-${prod}">${producto.nombre}</label>
          //                 </div>
          //                 <div class="d-flex mt-1">
          //                     <div class="input-group me-2 input-group-sm">
          //                         <span class="input-group-text">$</span>
          //                         <input class="form-control" type="number" min="0" value="${producto.precio_referencia}" />
          //                     </div>
          //                     <a class="btn btn-sm btn-falcon-default hover-primary" onclick="quitProducto(${prod})" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
          //                         <span class="fas fa-trash text-danger" data-fa-transform="down-2"></span>
          //                     </a>
          //                 </div>
          //             </div>
          //         </div>
          //     </div>
          // </div>
          // `);
        }
      });
    }

    // Resetear el combo y deshabilitar el producto seleccionado
    $("#lista_productos").val("-1").trigger("change").find(`option[value='${prod}']`).prop("disabled", true);
  }
}


function quitProducto(id_producto) {
  // Remover del DOM
  $("#item-" + id_producto).remove();

  // Remover del array
  productos_agregados = productos_agregados.filter(p => p != id_producto);

  // Habilitar opción de nuevo si corresponde
  $("#lista_productos").find(`option[value='${id_producto}']`).prop("disabled", false);
}

function saveCatalogo() {
  var nombre = $("#nombre").val();
  var descripcion = $("#descripcion").val();
  var id_empresa = $("#lista_empresas").val();
  var id_sucursal = $("#lista_sucursales").val();

  if (!nombre || !descripcion || !id_empresa || !id_sucursal) {
    return SweetAlert("error", "Todos los campos son obligatorios!!!");
  }

  var catalogo = [];
  var checkCount = 0;

  var allInputsFilled = $(".item").toArray().every(function (item) {
    var id_producto = $(item).attr('id').split('-')[1];
    var valor = $(item).find('input[type="number"]').val();
    var check = $("#check-" + id_producto).is(':checked')

    if (!valor) return false;
    catalogo.push({ id_producto: id_producto, valor: valor, default: check });

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
  console.log(checkCount)
  // Validación de que haya exactamente un proveedor con el checkbox marcado
  // if (checkCount === 0) {
  //   return SweetAlert("error", "Debes seleccionar un proveedor como predeterminado.");
  // }

  /*if(membresia != ""){
    tipo_membresia = membresia.tipo;
    articulos = membresia.numero;
    if(tipo_membresia == "articulos"){
      if (checkCount > articulos) {
        return SweetAlert("error", "Solo puedes seleccionar un máximo de "+articulos+" artículos.");
      }
    }
  }else{
    return SweetAlert("error", "La empresa no tiene una membresía activa");
  }*/

  $.post('../api/v1/fulmuv/catalogos/create', {
    nombre: nombre,
    descripcion: descripcion,
    id_sucursal: id_sucursal,
    productos: catalogo,
    creation_user: $("#id_principal").val()
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      SweetAlert("url_success", returned.msg, "catalogos.php")
    } else {
      SweetAlert("error", returned.msg)
    }
  });

}

function importar() {
  $.get('../api/v1/fulmuv/catalogos/'+$("#id_principal").val()+'/general', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
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
                <h4 class="mb-1" id="staticBackdropLabel">Importar catalogo</h4>
              </div>
              <div class="p-4">
                <div class="row g-2">
                  <div class="col-md-12 mb-3">
                    <label class="form-label" for="lista_empresas">Catalogo:</label>
                    <select class="form-select selectpicker" id="lista_catalogos">
                        <option value="">Seleccione un Catalogo</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <button onclick="cargarCatalogo()" class="btn btn-primary" type="submit">Importar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `);
      returned.data.forEach(catalogo => {
        $("#lista_catalogos").append(`
          <option value="${catalogo.id_catalogo}">${catalogo.nombre} (${catalogo.empresa} / ${catalogo.sucursal})</option>
        `);
      });
      $('#lista_catalogos').select2({
        dropdownParent: $('#staticBackdrop')
      });
      $("#btnModal").click();
    }
  });


}

function cargarCatalogo() {
  if ($("#lista_catalogos").val() != "") {
    $("#staticBackdrop").modal("hide");
    var id_catalogo = $("#lista_catalogos").val()
    $.get('../api/v1/fulmuv/catalogos/' + id_catalogo + '/productos', {}, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        var cat = returned.data
        $("#nombre").val(cat.nombre)
        $("#descripcion").val(cat.descripcion)
        $("#lista_empresas").val(cat.id_empresa).trigger("change")
        $("#lista_sucursales").val(cat.id_sucursal)
        $("#productos_agregados").empty()
        $("#lista_productos option").prop("disabled", false);
        cat.productos.forEach(prod => {
          $("#lista_productos").find(`option[value='${prod.id_producto}']`).prop("disabled", true);
          $("#productos_agregados").append(`
            <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3 mb-2 item" id="item-${prod.id_producto}">
              <div class="card h-100">
                  <div class="card-body d-flex align-items-center px-2 h-100">
                      <img class="img-fluid rounded-1 me-2" src="${prod.img_path}" alt="" height="60" width="60" />
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
      }
    });
  } else {
    return SweetAlert("error", "Seleccione un catalogo!");
  }
}

function llenarTabla(){
  if(!$("#lista_empresas").val()){
    SweetAlert("error", "Debe seleccionar una empresa");
  }else{
    var categoria = $("#lista_categorias").val();
    if(categoria == "Todas"){
      console.log(productos)
      productos.forEach(prod => {
        var pr = parseInt(prod.id_producto)
        if (!productos_agregados.includes(pr)) {
          productos_agregados.push(pr)
          agregarProductoDesdeExcel(prod, prod.precio_referencia);

          $("#lista_productos").val("-1").trigger("change");
        }

      });
    }else if(categoria != ""){
      $.post('../api/v1/fulmuv/categorias/productos', {
        id_categoria: $("#lista_categorias").val(),
        id_empresa: $("#lista_empresas").val(),
      }, function (returnedData) {
        var returned = JSON.parse(returnedData)
        console.log(returned)
        if (returned.error == false) {
          if(returned.data.length > 0){
            $("#tablaProductos").show();
            returned.data.forEach(producto => {
              var pr = parseInt(producto.id_producto);
              if (!productos_agregados.includes(pr)) {
            
                // Agregar al array
                productos_agregados.push(pr);

                agregarProductoDesdeExcel(producto, producto.precio_referencia);

                // Agregar al DOM
                // $("#productos_agregados").append(`
                //   <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3 mb-2 item" id="item-${producto.id_producto}">
                //     <div class="card h-100">
                //         <div class="card-body d-flex align-items-center px-2 h-100">
                //             <img class="img-fluid rounded-1 me-2" src="${producto.img_path}" alt="" height="60" width="60" />
                //             <div class="col">
                //                 <div class="form-check">
                //                   <input class="form-check-input" id="check-${producto.id_producto}" type="checkbox" checked>
                //                   <label class="form-check-label col-12 text-wrap fs-9 h5" for="check-${producto.id_producto}">${producto.nombre}</label>
                //                 </div>
                //                 <div class="d-flex mt-1">
                //                   <div class="input-group me-2 input-group-sm">
                //                       <span class="input-group-text">$</span>
                //                       <input class="form-control" type="number" min="0" value="${producto.precio_referencia}" />
                //                   </div>
                //                   <a class="btn btn-sm btn-falcon-default hover-primary" onclick="quitProducto(${producto.id_producto})" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
                //                       <span class="fas fa-trash text-danger" data-fa-transform="down-2"></span>
                //                   </a>
                //                 </div>
                //             </div>
                //         </div>
                //     </div>
                //   </div>
                // `);
                
              }
            });
          }
        }
      });
    }
  }
}

function agregarProductos() {
  var selectedRows = [];
  $('#lista_prod input[type="checkbox"]:checked').each(function () {
    var id = $(this).data('id');
    selectedRows.push(id);
  });

  // Verificar si hay filas seleccionadas
  if (selectedRows.length) {
    selectedRows.forEach(seleccionado => {
      // Validar si ya fue agregado antes
      var pr = parseInt(seleccionado)
      if (!productos_agregados.includes(pr)) {
        productos.forEach(producto => {
          if (producto.id_producto == seleccionado) {
            // Agregar al array
            
            productos_agregados.push(pr);

            agregarProductoDesdeExcel(producto, producto.precio_referencia);
            // Agregar al DOM
            // $("#productos_agregados").append(`
            //   <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3 mb-2 item" id="item-${seleccionado}">
            //     <div class="card h-100">
            //         <div class="card-body d-flex align-items-center px-2 h-100">
            //             <img class="img-fluid rounded-1 me-2" src="${producto.img_path}" alt="" height="60" width="60" />
            //             <div class="col">
            //                 <div class="form-check">
            //                   <input class="form-check-input" id="check-${seleccionado}" type="checkbox" checked>
            //                   <label class="form-check-label col-12 text-wrap fs-9 h5" for="check-${seleccionado}">${producto.nombre}</label>
            //                 </div>
            //                 <div class="d-flex mt-1">
            //                     <div class="input-group me-2 input-group-sm">
            //                         <span class="input-group-text">$</span>
            //                         <input class="form-control" type="number" min="0" value="${producto.precio_referencia}" />
            //                     </div>
            //                     <a class="btn btn-sm btn-falcon-default hover-primary" onclick="quitProducto(${seleccionado})" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
            //                         <span class="fas fa-trash text-danger" data-fa-transform="down-2"></span>
            //                     </a>
            //                 </div>
            //             </div>
            //         </div>
            //     </div>
            // </div>
            // `);
          }
        });
      }
    });
  }
}


function cambiarTipoAgregado() {
    const tipo = document.getElementById("tipo_agregado").value;

    const seccionIndividual = document.getElementById("seccion_individual");
    const seccionCategoria = document.getElementById("seccion_categoria");

    if (tipo === "individual") {
        seccionIndividual.classList.remove("d-none");
        seccionCategoria.classList.add("d-none");
    } else {
        seccionIndividual.classList.add("d-none");
        seccionCategoria.classList.remove("d-none");
    }
}


function removerTodo(){
  $("#productos_agregados").empty()
  $("#lista_productos option").prop("disabled", false);
  productos_agregados = [];
  $("#lista_productos").val("-1").trigger("change");
  $("#lista_categorias").val("").trigger("change");
}

function agregarProductoDesdeExcel(producto, precioExcel) {
  let prod = producto.id_producto;
  // Evita agregar duplicados
  if ($("#item-" + prod).length === 0) {

    // Buscar el primer archivo tipo 'imagen'
    const archivoImagen = producto.archivos?.find(archivo => archivo.tipo === 'imagen');

    $("#productos_agregados").append(`
          <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3 mb-2 item" id="item-${prod}">
              <div class="card h-100">
                  <div class="card-body d-flex align-items-center px-2 h-100">
                      <img class="img-fluid rounded-1 me-2" src="${archivoImagen && archivoImagen.archivo ? archivoImagen.archivo : "files/producto_no_found.jpg"}" alt="" height="60" width="60" />
                      <div class="col">
                          <div class="col-12 text-wrap fs-9 h5 mb-2">
                              ${producto.nombre}
                          </div>
                          <div class="d-flex mt-1">
                              <div class="input-group me-2 input-group-sm">
                                  <span class="input-group-text">$</span>
                                  <input class="form-control" type="number" min="0" value="${parseFloat(precioExcel).toFixed(2)}" />
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

    // Deshabilita el producto en el select
    $("#lista_productos").find(`option[data-codigo='${producto.codigo}']`).prop("disabled", true);
  }
}