let establecimientos = [];
let membresias = [];

$(document).ready(function () {

  $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#tabla_empresas_lista").text("");
      returned.data.forEach(empresa => {
        $("#tabla_empresas_lista").append(`
          <tr class="btn-reveal-trigger">
              <td class="name align-middle white-space-nowrap py-2">
                <a href="empresa_detalle.php?id_empresa=${empresa.id_empresa}">
                  <h5 class="mb-0 fs-10">${empresa.nombre}</h5>  
                </a>
              </td>
              <td class="email align-middle py-2">${empresa.direccion}</td>
              <td class="phone align-middle white-space-nowrap py-2">${empresa.descripcion}</td>
              <td class="address align-middle white-space-nowrap">${empresa.razon_social}</td>
              <td class="address align-middle white-space-nowrap">${ (empresa.membresia != null) ? empresa.membresia.nombre : ''}</td>
              <td class="align-middle white-space-nowrap py-2 text-end">
                <div class="dropdown font-sans-serif position-static">
                  <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                  <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                    <div class="py-2">
                      <a class="dropdown-item" onclick="editEmpresa(${empresa.id_empresa})">Editar</a>
                      <!--a class="dropdown-item text-warning" onclick="asignarMembresia(${empresa.id_empresa})">Asignar membresía</a-->
                      <a class="dropdown-item text-warning" href="asignar_membresia2.php?id_empresa=${empresa.id_empresa}">Asignar membresía</a>
                      <a class="dropdown-item text-danger" onclick="remove(${empresa.id_empresa},'empresas')">Eliminar</a>
                    </div>
                  </div>
                </div>
              </td>
          </tr>
        `);
      });
      $("#my_table").DataTable({
        "searching": true,
        "responsive": false,
        "pageLength": 8,
        "info": true,
        "lengthChange": false,
        "language": {
          "url": "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
          "paginate": {
            "next": "<span class=\"fas fa-chevron-right\"></span>",
            "previous": "<span class=\"fas fa-chevron-left\"></span>"
          }
        },
        "dom": "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" + "<'table-responsive scrollbar'tr>" + "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>"
      })
    }
  });

  $.get('../api/v1/fulmuv/tiposEstablecimientos', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      establecimientos = returned.data;
    }
  });

  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      membresias = returned.data;
    }
  });

});

function asignarMembresia(id_empresa){
  /*$.get('../api/v1/fulmuv/empresas/' + id_empresa, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      empresaData = returned.data
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar membresía</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                      <input disabled class="form-control" id="nombre" type="text" placeholder="nombre" value="${empresaData.nombre}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Membresía</label>
                      <select class="form-select" id="id_membresia">
                        <option value="">Sin membresía</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <button onclick="saveMembresia(${id_empresa})" class="btn btn-iso" type="submit">Actualizar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      membresias.forEach(membresia => {
        $("#id_membresia").append(`
          <option value="${membresia.id_membresia}">${membresia.nombre}</option>
        `);
      });
      $("#btnModal").click();
    }
  });*/
}

function saveMembresia(id_empresa){
  $.post('../api/v1/fulmuv/empresas/membresiasUpdate', {
    id_empresa: id_empresa,
    id_membresia: $("#id_membresia").val(),
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      SweetAlert("url_success", returned.msg, "empresas.php")
    } else {
      SweetAlert("error", returned.msg)
    }
  });
}

/*function addEmpresa() {
  // Limpiar modal anterior si existe
  $("#staticBackdrop").remove();

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
              <h4 class="mb-1" id="staticBackdropLabel">Crear empresa</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nombre</label>
                  <input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()"/>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Dirección</label>
                  <input class="form-control" type="text" id="direccion">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="imagen_empresa">Logo o Imagen</label>
                  <input class="form-control" type="file" id="imagen_empresa" accept="image/*">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tiempo en el mercado</label>
                  <select class="form-select" id="tipo_establecimiento"></select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tipo establecimiento</label>
                  <select class="form-select" id="tipo_establecimiento"></select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Razón social</label>
                  <input class="form-control" id="razon_social" type="text" placeholder="razón social" oninput="this.value = this.value.toUpperCase()"/>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Latitud</label>
                  <input class="form-control" id="latitud" type="text" placeholder="latitud"/>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Longitud</label>
                  <input class="form-control" id="longitud" type="text" placeholder="longitud"/>
                </div>
                <div class="col-12">
                  <button onclick="saveEmpresa()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  // Rellenar select
  $("#tipo_establecimiento").text("");
  establecimientos.forEach(establecimiento => {
    $("#tipo_establecimiento").append(`
      <option value="${establecimiento.id_establecimiento}">${establecimiento.descripcion}</option>
    `);
  });

  // Mostrar modal
  $("#btnModal").click();

}*/

function addEmpresa() {
  // Limpiar modal anterior si existe
  $("#staticBackdrop").remove();

  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl mt-6" role="document">
        <div class="modal-content border-0">
          <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
              <h4 class="mb-1" id="staticBackdropLabel">Crear empresa</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <!-- Datos básicos -->
                <div class="col-md-6 mb-3"><label class="form-label">Nombre</label><input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()"/></div>
                <div class="col-md-6 mb-3"><label class="form-label">Dirección</label><input class="form-control" type="text" id="direccion"></div>
                <div class="col-md-12 mb-3"><label class="form-label">Logo o Imagen</label><input class="form-control" type="file" id="imagen_empresa" accept="image/*"></div>
                <!--div class="col-md-6 mb-3"><label class="form-label">Razón social</label><input class="form-control" id="razon_social" type="text" placeholder="razón social" oninput="this.value = this.value.toUpperCase()"/></div>
                <div class="col-md-6 mb-3"><label class="form-label">Latitud</label><input class="form-control" id="latitud" type="text" placeholder="latitud"/></div>
                <div class="col-md-6 mb-3"><label class="form-label">Longitud</label><input class="form-control" id="longitud" type="text" placeholder="longitud"/></div-->

                <!-- Información del Local -->
                <div class="col-md-12 mb-3">
                  <label class="form-label">Información del local</label>
                  <div class="row">
                    <div class="col-md-4"><input class="form-check-input me-1" type="checkbox" id="guardiania"><label class="form-check-label" for="guardiania">Tiene guardia</label></div>
                    <div class="col-md-4"><input class="form-check-input me-1" type="checkbox" id="camaras"><label class="form-check-label" for="camaras">Tiene cámaras de seguridad</label></div>
                    <div class="col-md-4"><input class="form-check-input me-1" type="checkbox" id="parqueadero"><label class="form-check-label" for="parqueadero">Tiene parqueadero</label></div>
                    <div class="col-md-12 mt-2"><label class="form-label">Parqueadero</label><select id="parqueadero_tipo" class="form-select"><option value="">Seleccione</option><option>Interno</option><option>Externo</option></select></div>
                  </div>
                </div>

                <!-- Tiempo en el Mercado -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tiempo en el mercado</label>
                  <div class="d-flex gap-2">
                    <input class="form-control" id="tiempo_anos" type="number" placeholder="Años"/>
                    <input class="form-control" id="tiempo_meses" type="number" placeholder="Meses"/>
                  </div>
                </div>

                <!-- Garantías del Vendedor -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Garantías del Vendedor</label>
                  <select class="form-select mb-2" id="garantia_vendedor" onchange="$('#garantia_detalle').toggle(this.value === 'SI')">
                    <option value="NO">NO</option>
                    <option value="SI">SÍ</option>
                  </select>
                  <div id="garantia_detalle" style="display: none;">
                    <input class="form-control mb-2" id="garantia_tiempo" placeholder="Tiempo de garantía">
                    <input class="form-control mb-2" id="garantia_condiciones" placeholder="Condiciones">
                    <input class="form-control" id="garantia_terminos" placeholder="Términos">
                  </div>
                </div>

                <!-- Instalación -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">¿Instalan los productos?</label>
                  <select class="form-select mb-2" id="instalacion_producto" onchange="$('#instalacion_detalle').toggle(this.value === 'SI')">
                    <option value="NO">NO</option>
                    <option value="SI">SÍ</option>
                  </select>
                  <div id="instalacion_detalle" style="display: none;">
                    <select class="form-select mb-2" id="instalacion_costo_tipo">
                      <option value="">Seleccione</option>
                      <option value="sin_costo">Sin costo adicional</option>
                      <option value="con_costo">Con costo adicional</option>
                    </select>
                    <input class="form-control" id="instalacion_valor" placeholder="Costo adicional (en caso de aplicar)">
                  </div>
                </div>

                <!-- Horario de Atención -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Horario de Atención</label>
                  <select class="form-select mb-2" id="horario_atencion" onchange="$('#horario_otro').toggle(this.value === 'OTRO')">
                    <option value="24H">Atienden las 24 horas</option>
                    <option value="7DIAS">Atienden los 7 días</option>
                    <option value="OTRO">Otro</option>
                  </select>
                  <div id="horario_otro" style="display: none;">
                    <textarea class="form-control" placeholder="Especificar días y horas" id="detalle_horario"></textarea>
                  </div>
                </div>

                <!-- Contacto Llamadas -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Números para llamadas</label>
                  <input class="form-control mb-2" type="text" id="telefono_contacto" placeholder="Ej. 0999999999">
                </div>

                <!-- Contacto WhatsApp -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Números para WhatsApp</label>
                  <input class="form-control mb-2" type="text" id="whatsapp_contacto" placeholder="Ej. 0999999999">
                </div>

                <div class="col-12 text-end">
                  <button onclick="saveEmpresa()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  // Mostrar modal
  $("#btnModal").click();
}

// Función para abrir llamada
function abrirLlamada() {
  const numero = $('#telefono_contacto').val();
  if (numero) {
    window.open(`tel:${numero}`);
  }
}

// Función para abrir WhatsApp
function abrirWhatsapp() {
  const numero = $('#whatsapp_contacto').val();
  if (numero) {
    const numeroFormateado = numero.replace(/\D/g, '');
    window.open(`https://wa.me/593${numeroFormateado.substring(1)}`);
  }
}


document.addEventListener("gmpx-placeautocomplete:place", function (e) {
  const place = e.detail;
  if (place && place.geometry) {
    const lat = place.geometry.location.lat();
    const lng = place.geometry.location.lng();
    document.getElementById("latitud").value = lat;
    document.getElementById("longitud").value = lng;
    console.log("Ubicación seleccionada:", lat, lng);
  } else {
    console.warn("No se encontró ubicación válida.");
  }
});


function saveEmpresa() {
  var nombre = $("#nombre").val();
  var direccion = $("#direccion").val();
  var latitud = '-2.054590966583526';
  var longitud = '-79.87851214202014';

  if (nombre == "" || direccion == "") {
    SweetAlert("error", "Los campos nombre y dirección son obligatorios!!!")
  } else {

    var files = $('#imagen_empresa')[0].files[0];
    console.log(files)
    var filePromise = files === undefined ? Promise.resolve(empresaData.img_path) : saveFiles(files);

    filePromise.then(function (file) {
      $.post('../api/v1/fulmuv/empresas/create', {
        nombre: nombre,
        direccion: direccion,
        img_path: file.img ? file.img : empresaData.img_path,
        latitud: latitud,
        longitud: longitud,
  
        tiempo_anos: $('#tiempo_anos').val(),
        tiempo_meses: $('#tiempo_meses').val(),
  
        guardiania: $('#guardiania').is(":checked") ? 1 : 0,
        camaras: $('#camaras').is(":checked") ? 1 : 0,
        parqueadero: $('#parqueadero').is(":checked") ? 1 : 0,
        tipo_parqueadero: $('#parqueadero_tipo').val(),
  
        garantia_ofrecida: $('#garantia_vendedor').val(),
        garantia_tiempo: $('#garantia_tiempo').val(),
        garantia_condiciones: $('#garantia_condiciones').val(),
        garantia_terminos: $('#garantia_terminos').val(),
  
        instala_productos: $('#instalacion_producto').val(),
        instalacion_tipo: $('#instalacion_costo_tipo').val(),
        instalacion_valor: $('#instalacion_valor').val(),
  
        horario_tipo: $('#horario_atencion').val(),
        horario_otro: $('#detalle_horario').val(),
  
        telefono_contacto: $('#telefono_contacto').val(),
        whatsapp_contacto: $('#whatsapp_contacto').val(),
  
      }, function (returnedData) {
        var returned = JSON.parse(returnedData)
        if (returned.error == false) {
          SweetAlert("url_success", returned.msg, "empresas.php")
        } else {
          SweetAlert("error", returned.msg)
        }
      });
    });
    
  }
  
}

function editEmpresa(id_empresa) {
  $.get('../api/v1/fulmuv/empresas/' + id_empresa, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      empresaData = returned.data
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar empresa</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                      <input class="form-control" id="nombre" type="text" placeholder="nombre" value="${empresaData.nombre}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Dirección</label>
                      <input class="form-control" id="direccion" type="text" placeholder="dirección" value="${empresaData.direccion}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Tipo establecimiento</label>
                      <select class="form-select" id="tipo_establecimiento">
                        
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Razón social</label>
                      <input class="form-control" id="razon_social" type="text" placeholder="razón social" value="${empresaData.razon_social}" oninput="this.value = this.value.toUpperCase()"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Latitud</label>
                      <input class="form-control" id="latitud" type="text" placeholder="latitud" value="${empresaData.latitud}"/>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Longitud</label>
                      <input class="form-control" id="longitud" type="text" placeholder="longitud" value="${empresaData.longitud}"/>
                    </div>
                    <div class="col-md-12 mb-3">
                      <label class="form-label" for="imagen_empresa">Imagen</label>
                      <input class="form-control" type="file" id="imagen_empresa" accept="image/*">
                    </div>
                    <div class="col-12">
                      <button onclick="updateEmpresa(${id_empresa})" class="btn btn-iso" type="submit">Actualizar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      establecimientos.forEach(establecimiento => {
        $("#tipo_establecimiento").append(`
          <option value="${establecimiento.id_establecimiento}">${establecimiento.descripcion}</option>
        `);
      });
      $("#tipo_establecimiento").val(returned.data.tipo_establecimiento)
      $("#btnModal").click();
    }
  });
}

function updateEmpresa(id_empresa) {
  var nombre = $("#nombre").val();
  var direccion = $("#direccion").val();
  var tipo_establecimiento = $("#tipo_establecimiento").val();
  var razon_social = $("#razon_social").val();
  var latitud = $("#latitud").val();
  var longitud = $("#longitud").val();
  if (nombre == "" || direccion == "" || razon_social == "" || latitud == "" || longitud == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {

    var files = $('#imagen_empresa')[0].files[0];
    console.log(files)
    var filePromise = files === undefined ? Promise.resolve(empresaData.img_path) : saveFiles(files);

    filePromise.then(function (file) {
      $.post('../api/v1/fulmuv/empresas/update', {
        id_empresa: id_empresa,
        nombre: nombre,
        direccion: direccion,
        tipo_establecimiento: tipo_establecimiento,
        razon_social: razon_social,
        img_path: file.img ? file.img : empresaData.img_path,
      }, function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (!returned.error) {
          SweetAlert("url_success", returned.msg, "empresas.php")
        } else {
          SweetAlert("error", returned.msg);
        }
      });
    });

    /*  console.log()
     $.post('../api/v1/fulmuv/empresas/update', {
       id_empresa: id_empresa,
       nombre: nombre,
       direccion: direccion,
       tipo_establecimiento: tipo_establecimiento,
       razon_social: razon_social
     }, function (returnedData) {
       var returned = JSON.parse(returnedData)
       if (returned.error == false) {
         SweetAlert("url_success", returned.msg, "empresas.php")
       } else {
         SweetAlert("error", returned.msg)
       }
     }); */
  }
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    console.log(files)
    if (files == undefined) {
      resolve(); // Resuelve la promesa incluso si no hay imágenes
    } else {
      const formData = new FormData();
      formData.append(`archivos[]`, files); // añadrir los archivos al form
      $.ajax({
        type: 'POST',
        data: formData,
        url: 'cargar_imagen.php',
        cache: false,
        contentType: false,
        processData: false,
        success: function (returnedImagen) {
          if (returnedImagen["response"] == "success") {
            resolve(returnedImagen["data"]); // Resuelve la promesa cuando la llamada AJAX se completa con éxito
          } else {
            SweetAlert("error", "Ocurrió un error al guardar los archivos." + returnedImagen["error"]);
            reject(); // Rechaza la promesa en caso de error
          }
        }
      });
    }
  });
}


function remove(id, tabla) {
  swal({
    title: "Alerta",
    text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    $.post('../api/v1/fulmuv/' + tabla + '/delete', {
      id: id
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empresas.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}