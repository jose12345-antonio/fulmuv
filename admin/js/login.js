const password = document.getElementById("password");

function logear() {
  var username = $("#username").val()
  var password = $("#password").val()
  if (username == "" || password == "") {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Error",
      text: "Campo de correo electrónico y/o contraseña obligatorios.",
      showConfirmButton: true,
    });
  } else {
    $.post('../api/v1/fulmuv/admin/login', {
      username: $("#username").val(),
      password: $("#password").val()
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == true) {
        Swal.fire({
          position: "center",
          icon: "error",
          title: "Error",
          text: returned["msg"],
          showConfirmButton: true,
        });
      } else {
        $("#formlogin").append(`
          <input type='hidden' name='id_usuario' value='${returned.administrador.id_usuario}'/>
          <input type='hidden' name='rol_id' value='${returned.administrador.rol_id}' />
          <input type='hidden' name='username' value='${returned.administrador.nombre_usuario}' />
          <input type='hidden' name='correo' value='${returned.administrador.correo}' />
          <input type='hidden' name='nombres' value='${returned.administrador.nombres}' />
          <input type='hidden' name='imagen' value='${returned.administrador.imagen}' />
          <input type='hidden' name='nombre_rol_user' value='${returned.administrador.nombre_rol_user}' />
          <input type='hidden' name='permisos' value='${JSON.stringify(returned.permisos)}  ' />
          <input type='hidden' name='id_empresa' value='${returned.administrador.id_empresa}' />
          <input type='hidden' name='membresia' value='${JSON.stringify(returned.membresia)}' />
        `);
        $("#formlogin").submit();
      }
    });
  }
}

const enterEvent = e => {
  if (e.key === "Enter") {
    logear();
  }
}

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
          Swal.fire("Éxito!", returned.msg, "success")

        } else {
          Swal.fire("Error", returned.msg, "error")

          SweetAlert("error", returned.msg)
        }
      });
    });

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
            Swal.fire("Error", "Ocurrió un error al guardar los archivos." + returnedImagen["error"], "error")
            reject(); // Rechaza la promesa en caso de error
          }
        }
      });
    }
  });
}
password.addEventListener("keyup", enterEvent);

