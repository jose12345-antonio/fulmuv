let establecimientos = [];
let roles = [];
let sucursales = [];
let areas = [];
var id_empresa = document.getElementById("id_empresa_detalle")?.value;
var tipo_user = document.getElementById("tipo_user")?.value;

let detalle_empresa;

let map, marker, geocoder, placesService;
let latitud = null, longitud = null; // coordenadas de trabajo (empresa / selección)
window.__mapReady = false;           // si ya se construyó el mapa del modal

let autocompleteService;

$(document).ready(function () {


  // Ocultar cosas cuando es sucursal
  if (tipo_user === 'sucursal') {
    // 1. Oculta el overlay "actualizar" del avatar
    // $('label[for="profile-image"]').addClass('d-none'); // o .hide()

    // 2. Deshabilita el input file para que no se pueda usar por JS
    // $('#profile-image').prop('disabled', true);

    // (ya tenías) ocultar botón actualizar ubicación / card usuarios si quieres
    $('#btnActualizarUbicacion').hide();
    $('#colUsuarios').hide();
    $("#viewOrdenesRecientes").removeClass("col-xl-7 col-xxl-8")
  }

  $.get('../api/v1/fulmuv/empresas2/' + id_empresa + '/' + tipo_user + '/detalle', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#nombre_empresa").text(returned.data.nombre)

      $("#direccion_empresa").text(returned.data.direccion)
      $("#tipo_establecimiento").text(returned.data.descripcion)
      $("#razon_social").text(returned.data.razon_social)
      if (tipo_user == "sucursal") {
        $("#imagen_empresa").attr("src", returned.data.imagen);
      } else {
        $("#imagen_empresa").attr("src", returned.data.img_path);
      }

      //$("#membresia").text(returned.data.membresia.nombre);

      const mem = returned.data.membresia || null;

      // defaults
      let nombreMem = "Sin membresía";
      let tipoMem = "-";
      let fechaFin = null;
      let fechaInicio = null;

      // según tu consola, viene: returned.data.membresia.fecha_fin / fecha_inicio
      if (mem) {
        nombreMem = mem.nombre || "Sin nombre";
        tipoMem = mem.tipo || "-";
        fechaFin = mem.fecha_fin || null;
        fechaInicio = mem.fecha_inicio || null;
      }

      $("#membresia_nombre").text(nombreMem);
      $("#membresia_tipo").text(tipoMem);

      // formatea fecha (si viene tipo "2025-10-23 18:15:09")
      function parseDateTime(str) {
        if (!str) return null;
        // convierte "YYYY-MM-DD HH:mm:ss" a formato ISO "YYYY-MM-DDTHH:mm:ss"
        const iso = String(str).replace(" ", "T");
        const d = new Date(iso);
        return isNaN(d.getTime()) ? null : d;
      }

      const dFin = parseDateTime(fechaFin);
      const dIni = parseDateTime(fechaInicio);

      $("#membresia_fecha_fin").text(dFin ? dFin.toLocaleString() : "--");

      // calcula días restantes
      let diasRestantes = null;
      if (dFin) {
        const ahora = new Date();
        const ms = dFin.getTime() - ahora.getTime();
        diasRestantes = Math.ceil(ms / (1000 * 60 * 60 * 24));
      }

      if (diasRestantes === null) {
        $("#membresia_dias_restantes").text("--");
        $("#membresia_estado_texto").text("No hay fecha de caducidad registrada");
        $("#membresia_progress").css("width", "0%");
      } else {
        const diasTxt = diasRestantes < 0 ? 0 : diasRestantes;
        $("#membresia_dias_restantes").text(`${diasTxt} días`);

        // estado textual + estilos
        if (diasRestantes <= 0) {
          $("#membresia_estado_texto").text("Membresía caducada");
          $("#membresia_progress").addClass("bg-danger");
        } else if (diasRestantes <= 7) {
          $("#membresia_estado_texto").text("Por caducar (urgente)");
          $("#membresia_progress").addClass("bg-warning");
        } else {
          $("#membresia_estado_texto").text("Activa");
          $("#membresia_progress").addClass("bg-success");
        }

        // progreso (si tenemos fecha_inicio y fecha_fin)
        if (dIni && dFin && dFin > dIni) {
          const total = dFin.getTime() - dIni.getTime();
          const trans = (new Date().getTime() - dIni.getTime());
          let pct = Math.round((trans / total) * 100);
          pct = Math.max(0, Math.min(100, pct));
          $("#membresia_progress").css("width", pct + "%");
          $("#membresia_rango_texto").text(
            `${dIni.toLocaleDateString()} → ${dFin.toLocaleDateString()}`
          );
        } else {
          // sin rango completo, al menos muestra barra estimada
          $("#membresia_progress").css("width", diasRestantes <= 0 ? "100%" : "60%");
          $("#membresia_rango_texto").text("");
        }
      }

      // =====================
      // VERIFICACIÓN (UI)
      // =====================
      const ver = (returned.data.verificacion && returned.data.verificacion.length > 0)
        ? returned.data.verificacion[0]
        : null;

      const isVerified =
        ver &&
        (
          ver.verificado === 1 ||
          ver.verificado === true ||
          String(ver.estado || "").toUpperCase() === "A" ||
          String(ver.estado || "").toLowerCase() === "aprobado"
        );

      // 👇 usa tus rutas reales (pueden ser png/svg)
      const IMG_OK = "../img/verificado_empresa.png";
      const IMG_NO = "../theme/public/assets/img/icons/not-verified.png";

      if (isVerified) {
        $("#img_verificacion")
          .attr("src", IMG_OK)
          .attr("title", "Empresa verificada")
          .attr("alt", "Verificada")
          .removeClass("d-none");
      } else {
        $("#img_verificacion")
          .attr("src", IMG_NO)
          .attr("title", "Empresa no verificada")
          .attr("alt", "No verificada")
          .addClass("d-none");
      }

      const lat = parseFloat(returned.data.latitud);
      const lng = parseFloat(returned.data.longitud);

      latitud = isFinite(lat) ? lat : null;
      longitud = isFinite(lng) ? lng : null;

      // startPos viene de getStartPos() con coords reales si existen
      if (!(window.google && google.maps)) return;
      console.log("PASA 1")
      map = new google.maps.Map(document.getElementById("map_new"), {
        center: { lat: parseFloat(latitud), lng: parseFloat(longitud) },
        zoom: 14,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false
      });

      console.log("PASA 2")

      geocoder = new google.maps.Geocoder();
      placesService = new google.maps.places.PlacesService(map);

      console.log("PASA 3")
      marker = new google.maps.Marker({
        map,
        draggable: true,
        position: { lat: parseFloat(latitud), lng: parseFloat(longitud) },
      });

      // sincroniza globales + llena dirección
      latitud = latitud;
      longitud = longitud;
      obtenerDireccionDesdeCoords(latitud, longitud);

      detalle_empresa = returned.data;

      sucursales = returned.data.sucursales;
      returned.data.sucursales.forEach(function (sucursal, index) {
        $("#sucursales").append(`
          <li class="nav-item" role="presentation">
            <a class="nav-link p-x1 mb-0 ${index == 0 ? 'active' : ''}" id="tab-${sucursal.id_surcusal}" role="tab" data-bs-toggle="tab" href="#${sucursal.id_sucursal}" aria-controls="${sucursal.id_sucursal}" aria-selected="${index == 0 ? 'true' : 'false'}">
              <div class="d-flex gap-1 py-1 pe-3">
                <h6 class="mb-1 text-700 fs-12 text-nowrap">${sucursal.nombre}</h6>
              </div>
            </a>
          </li>
        `);
        $("#areas").append(`
        <div class="tab-pane ${index == 0 ? 'active' : ''}" id="${sucursal.id_sucursal}" role="tabpanel" aria-labelledby="tab-${sucursal.id_sucursal}">
          <div class="card-header">
            <div class="row flex-between-center">
              <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">${sucursal.direccion}</h5>
              </div>
              <div class="col-8 col-sm-auto ms-auto text-end ps-0">
                <div>
                  <button onclick="addArea(${sucursal.id_sucursal})" class="btn btn-falcon-default btn-sm" type="button"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Crear Área</span></button>
                  <button onclick="editSucursal(${sucursal.id_sucursal})" class="btn btn-falcon-default btn-sm mx-2" type="button"><span class="fas fa-edit" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Editar Sucursal</span></button>
                  <button onclick="remove(${sucursal.id_sucursal}, 'sucursales')" class="btn btn-falcon-default btn-sm" type="button"><span class="text-danger fas fa-trash-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Eliminar Sucursal</span></button>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body px-0 py-0">
            <div class="table-responsive scrollbar">
              <table class="table fs-10 mb-0 overflow-hidden">
                <thead class="bg-200">
                  <tr class="font-sans-serif">
                    <th class="text-900 fw-medium">Nombre</th>
                    <th class="text-900 fw-medium">Fecha creación</th>
                    <th class="text-900 fw-medium">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tabla-${sucursal.id_sucursal}">
                  
                </tbody>
              </table>
            </div>
          </div>
        </div>
        `);

        sucursal.areas.forEach(function (area, i) {
          areas.push(area);
          $("#tabla-" + sucursal.id_sucursal).append(`
             <tr class="fw-semi-bold">
               <td>
                 <a href="../app/e-learning/course/course-details.html">${area.nombre}</a>
               </td>
               <td>
                 <a class="text-800" href="../app/e-learning/trainer-profile.html">${area.created_at}</a>
               </td>
               <td>
                   <div>
                     <button onclick="editArea(${area.id_area})" class="btn btn-link p-0" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                       <span class="text-500 fas fa-edit"></span>
                     </button>
                     <button onclick="remove(${area.id_area}, 'areas')" class="btn btn-link p-0 ms-2" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                       <span class="text-danger fas fa-trash-alt"></span>
                     </button>
                   </div>
               </td>
             </tr>
           `);
        });

      });

      $("#my_table").DataTable({
        "searching": true,
        "responsive": false,
        "pageLength": 10,
        "info": true,
        "lengthChange": false,
        // "language": {
        //   "url": "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
        //   "paginate": {
        //     "next": "<span class=\"fas fa-chevron-right\"></span>",
        //     "previous": "<span class=\"fas fa-chevron-left\"></span>"
        //   }
        // },
        "dom": "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" + "<'table-responsive scrollbar'tr>" + "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>"
      })
      var contador = 0;

      returned.data.usuarios.forEach(function (usuario, index) {
        if (contador < 10) {
          var opciones = "";
          if (usuario.rol == "Manager" || usuario.rol == "Area") {
            opciones = `
              <div class="hover-actions end-0 top-50 translate-middle-y">
                  <button onclick="EditUsuario(${usuario.id_usuario})" class="btn btn-tertiary border-300 btn-sm me-1 text-600 shadow-none" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar"><span class="text-500 fas fa-edit"></span></button>
                  <button onclick="remove(${usuario.id_usuario}, 'usuarios')" class="btn btn-tertiary border-300 btn-sm me-1 text-600 shadow-none" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><span class="text-danger fas fa-trash-alt"></span></button>
              </div>
            `;
          }
          $("#listaUsuarios").append(`
            <div class="d-flex mb-3 hover-actions-trigger align-items-center">
              <div class="avatar avatar-2xl">
                <img class="rounded-circle" src="../theme/public/assets/img/team/avatar.png" alt="" />
              </div>
              <div class="ms-3 flex-shrink-1 flex-grow-1">
                  <h6 class="mb-1">${usuario.nombres}</h6>
                  <div class="fs-10"><span class="text-dark fw-semi-bold">${usuario.rol}</span><span class="fw-medium text-600 ms-2">${usuario.nombre_nivel}</span></div>
                  ${opciones}
              </div>
            </div>
            <hr class="text-200" />
          `);
        }
      });
      getNotas("D")

    }
  });

  $.get('../api/v1/fulmuv/roles/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      roles = returned.data;
    }
  });

  $.post('../api/v1/fulmuv/ordenes/', {
    id_principal: $("#id_principal").val(),
    id_empresa: $("#id_empresa").val()
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {

      $("#lista_ordenes").text("");
      var contador = 0;

      returned.data.forEach(orden => {

        if (contador < 10) {

          estado = ""
          opciones = `
          <a class='dropdown-item' onclick="showNotes(${orden.id_orden})">Registro de Actividad</a>
          <a class='dropdown-item' href='orden_detalle.php?id_orden=${orden.id_orden}'>Ver Detalle</a>
          `
          switch (orden.orden_estado) {
            case "creada":
              estado = "<span class='badge badge rounded-pill badge-subtle-secondary text-capitalize'>creada<span class='ms-1 fas fa-shopping-cart' data-fa-transform='shrink-2'></span></span>"
              opciones += `
                    <a class="dropdown-item" onclick="updateEstado('aprobada', [${orden.id_orden}], '${orden.orden_estado}')">Aprobar</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" onclick="deleteOrden([${orden.id_orden}])">Eliminar</a>`
              break;
            case "aprobada":
              estado = "<span class='badge badge rounded-pill badge-subtle-warning text-capitalize'>aprobada<span class='ms-1 fas fa-user-check' data-fa-transform='shrink-2'></span></span>"
              opciones += `
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" onclick="deleteOrden([${orden.id_orden}])">Eliminar</a>`
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

          $("#lista_ordenes").append(`
            <tr class="btn-reveal-trigger">
              <td class="align-middle white-space-nowrap fw-semi-bold name"><a href="orden_detalle.php?id_orden=${orden.id_orden}">#${orden.id_orden}</a></td>
              <td class="date py-2 align-middle">${orden.created_at}</td>
              <td class="align-middle text-center fs-9 white-space-nowrap payment">
                ${estado}
              </td>
              <td class="align-middle text-end amount">$${orden.total}</td>
              <td class="align-middle white-space-nowrap text-end">
                <div class="dropstart font-sans-serif position-static d-inline-block">
                  <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-number-pagination-table-item-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-10"></span></button>
                  <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-number-pagination-table-item-0">
                    ${opciones}
                  </div>
                </div>
              </td>
            </tr>
          `);
        }
        contador = contador + 1;
      });

      options = {
        'responsive': false,
        'lengthChange': false,
        'searching': true,
        'pageLength': 10, 'info': true,
        // 'language': {
        //   'url': 'http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
        //   'paginate': {
        //     'next': '<span class=\'fas fa-chevron-right\'></span>',
        //     'previous': '<span class=\'fas fa-chevron-left\'></span>'
        //   }
        // }
      }

      //$("#my_table2").attr("data-datatables", JSON.stringify(options));
      $("#my_table2").DataTable({
        "searching": true,
        "responsive": false,
        "pageLength": 10,
        "info": true,
        "lengthChange": false,
        // "language": {
        //   "url": "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
        //   "paginate": {
        //     "next": "<span class=\"fas fa-chevron-right\"></span>",
        //     "previous": "<span class=\"fas fa-chevron-left\"></span>"
        //   }
        // },
        "dom": "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" + "<'table-responsive scrollbar'tr>" + "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>"
      })
      $("#checkbox-bulk-table-item-select").attr("data-bulk-select", '{"body":"lista_ordenes","actions":"table-number-pagination-actions","replacedElement":"table-number-pagination-replace-element"}');
      dataTablesInit()
      bulkSelectInit()
    }

  });

});

// dentro del $.get(...detalle empresa...)
//setCoordsFromEmpresa(returned.data.latitud, returned.data.longitud);
// if (window.__mapsReady && window.google && google.maps) {
//   iniciarMapa(parseFloat(returned.data.latitud), parseFloat(returned.data.longitud));
// } else {
//   window.__coordsPendientes = {
//     lat: parseFloat(returned.data.latitud),
//     lng: parseFloat(returned.data.longitud)
//   };
// }


function iniciarMapa(lat, lng) {
  if (!(window.google && google.maps)) return;
  const el = document.getElementById('map_new');
  if (!el) return;
  const mapa = new google.maps.Map(el, {
    zoom: 18,
    center: { lat, lng },
    mapTypeId: 'satellite'
  });
  new google.maps.Marker({ position: { lat, lng }, map: mapa, title: 'Ubicación especificada' });
}

$("#profile-image").on("change", function () {
  const files = this.files[0];
  if (files) {
    var filePromise = files === undefined ? Promise.resolve(detalle_empresa.img_path) : saveFiles(files);
    filePromise.then(function (file) {
      $.post('../api/v1/fulmuv/empresas/update', {
        id_empresa: id_empresa,
        nombre: detalle_empresa.nombre,
        tipo_user: $("#tipo_user").val(),
        direccion: detalle_empresa.descripcion,
        tipo_establecimiento: detalle_empresa.tipo_establecimiento,
        razon_social: detalle_empresa.razon_social,
        img_path: file.img ? file.img : empresaData.img_path,
      }, function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (!returned.error) {
          SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
        } else {
          SweetAlert("error", returned.msg);
        }
      });
    });
    // $("#imagen_empresa_preview").attr("src", empresaData.img_path);
  }
});

function getStartPos() {
  if (isFinite(latitud) && isFinite(longitud)) {
    return { lat: parseFloat(latitud), lng: parseFloat(longitud) };
  }
  // fallback solo si NO hay coordenadas de empresa
  return { lat: -2.066660613045653, lng: -79.89915462468714 };
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

function updateEstado(orden_estado, id_orden, estado) {
  if (orden_estado != estado) {
    $.post('../api/v1/fulmuv/ordenes/updateEstado', {
      id_orden: id_orden,
      id_usuario: id_usuario,
      orden_estado: orden_estado
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes_iso.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  } else {
    SweetAlert("warning", "La orden tiene el estado igual al estado que estas intentando actualizar.");
    return;
  }
}

function updateEstado(orden_estado, id_orden, estado) {
  if (orden_estado != estado) {
    $.post('../api/v1/fulmuv/ordenes/updateEstado', {
      id_orden: id_orden,
      id_usuario: $("#id_principal").val(),
      orden_estado: orden_estado
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned["error"] == false) {
        SweetAlert("url_success", returned.msg, "ordenes_iso.php")
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  } else {
    SweetAlert("warning", "La orden tiene el estado igual al estado que estas intentando actualizar.");
    return;
  }
}

function addSucursal() {
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
              <h4 class="mb-1" id="staticBackdropLabel">Crear Sucursal</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="nombre">Nombre</label>
                  <input class="form-control" id="nombre" type="text" placeholder="Nombre" />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="direccion">Dirección</label>
                  <input class="form-control" id="direccion" type="text" placeholder="Dirección" />
                </div>
                <div class="col-12">
                  <button onclick="saveSucursal()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
  $("#btnModal").click();
}

function saveSucursal() {
  var nombre = $("#nombre").val();
  var direccion = $("#direccion").val();
  if (nombre == "" || direccion == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/sucursales/create', {
      id_empresa: id_empresa,
      nombre: nombre,
      direccion: direccion,
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function editSucursal(id_sucursal) {
  $.get('../api/v1/fulmuv/sucursales/' + id_sucursal, {}, function (returnedData) {
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar empresa</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                      <input class="form-control" id="nombre" type="text" placeholder="nombre" value="${returned.data.nombre}" />
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Dirección</label>
                      <input class="form-control" id="direccion" type="text" placeholder="dirección" value="${returned.data.direccion}" />
                    </div>
                    <div class="col-12">
                      <button onclick="updateSucursal(${id_sucursal})" class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      $("#btnModal").click();
    }
  });
}

function updateSucursal(id_sucursal) {
  var nombre = $("#nombre").val();
  var direccion = $("#direccion").val();
  if (nombre == "" || direccion == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/sucursales/update', {
      id_sucursal: id_sucursal,
      nombre: nombre,
      direccion: direccion,
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}


function addArea(id_sucursal) {
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
              <h4 class="mb-1" id="staticBackdropLabel">Crear Área</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="mb-3">
                  <label class="form-label" for="nombre">Nombre</label>
                  <input class="form-control" id="nombre" type="text" placeholder="Nombre" />
                </div>
                <div class="col-12">
                  <button onclick="saveArea(${id_sucursal})" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
  $("#btnModal").click();
}

function saveArea(id_sucursal) {
  var nombre = $("#nombre").val();
  if (nombre == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/areas/create', {
      id_sucursal: id_sucursal,
      nombre: nombre,
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function editArea(id_area) {
  $.get('../api/v1/fulmuv/areas/' + id_area, {}, function (returnedData) {
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar área</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                      <input class="form-control" id="nombre" type="text" placeholder="nombre" value="${returned.data.nombre}" />
                    </div>
                    <div class="col-12">
                      <button onclick="updateArea(${id_area})" class="btn btn-primary" type="submit">Guardar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      $("#btnModal").click();
    }
  });
}

function updateArea(id_area) {
  var nombre = $("#nombre").val();
  if (nombre == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/areas/update', {
      id_area: id_area,
      nombre: nombre,
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
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
        SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}

function addUsuario() {
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
              <h4 class="mb-1" id="staticBackdropLabel">Crear Usuario</h4>
            </div>
            <div class="p-4">
              <div class="row g-2">
                <div class="col-lg-12 mb-3">
                  <label class="form-label" for="nombre">Nombres</label>
                  <input class="form-control" id="nombres" type="text" placeholder="Nombres" />
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Correo</label>
                  <input class="form-control" id="correo" type="text" placeholder="Correo" />
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Nombre de usuario</label>
                  <input class="form-control" id="nombre_usuario" type="text" placeholder="Nombre de usuario" />
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Rol</label>
                  <select class="form-select" id="roles" onchange="verTipo()">
                
                  </select>
                </div>
                <div class="col-lg-6 mb-3">
                  <label class="form-label" for="nombre">Sucursal/Area</label>
                  <select class="form-select" id="tipo">
                
                  </select>
                </div>
                <div class="col-12">
                  <button onclick="saveUsuario()" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
  roles.forEach(function (rol, index) {
    if (rol.rol != "Owner" && rol.rol != "Admin") {
      $("#roles").append(`
        <option value="${rol.id_rol}">${rol.rol}</option>
      `);
    }

  });
  verTipo()
  $("#btnModal").click();
}

function verTipo() {
  var rol = $("#roles option:selected").text();
  if (rol == "Manager") {
    $("#tipo").text("")
    sucursales.forEach(function (sucursal, index) {
      $("#tipo").append(`
        <option value="${sucursal.id_sucursal}">${sucursal.nombre}</option>
      `);
    });
  } else if (rol == "Area") {
    $("#tipo").text("")
    areas.forEach(function (area, index) {
      $("#tipo").append(`
        <option value="${area.id_area}">${area.nombre}</option>
      `);
    });
  }
}

function saveUsuario() {
  var nombres = $("#nombres").val();
  var nombre_usuario = $("#nombre_usuario").val();
  var correo = $("#correo").val();
  var rol = $("#roles").val();
  var tipo = $("#tipo").val();
  if (nombres == "" || correo == "" || nombre_usuario == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/usuarios/create', {
      nombres: nombres,
      nombre_usuario: nombre_usuario,
      correo: correo,
      imagen: '',
      id: tipo,
      rol_id: rol,
      pass: ''
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function EditUsuario(id_usuario) {
  $.get('../api/v1/fulmuv/usuarios/' + id_usuario, {}, function (returnedData) {
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
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar usuario</h4>
                </div>
                <div class="p-4">
                  <div class="row g-2">
                    <div class="col-md-12 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombres</label>
                      <input class="form-control" id="nombres" type="text" placeholder="nombre" value="${returned.data.nombres}" />
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Correo</label>
                      <input class="form-control" id="correo" type="text" placeholder="correo" value="${returned.data.correo}" />
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label" for="exampleFormControlInput1">Nombre de usuario</label>
                      <input class="form-control" id="nombre_usuario" type="text" placeholder="dirección" value="${returned.data.nombre_usuario}" />
                    </div>
                    <div class="col-lg-6 mb-3">
                      <label class="form-label" for="nombre">Rol</label>
                      <select class="form-select" id="roles" onchange="verTipo()">
                    
                      </select>
                    </div>
                    <div class="col-lg-6 mb-3">
                      <label class="form-label" for="nombre">Sucursal/Area</label>
                      <select class="form-select" id="tipo">
                    
                      </select>
                    </div>
                    <div class="col-12">
                      <button onclick="updateUsuario(${id_usuario})" class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
      roles.forEach(function (rol, index) {
        if (rol.rol != "Owner" && rol.rol != "Admin") {
          $("#roles").append(`
            <option value="${rol.id_rol}">${rol.rol}</option>
          `);
        }
      });
      $("#roles").val(returned.data.rol_id)
      verTipo()
      $("#tipo").val(returned.data.id)
      $("#btnModal").click();
    }
  });
}

function updateUsuario(id_usuario) {
  var nombres = $("#nombres").val();
  var nombre_usuario = $("#nombre_usuario").val();
  var correo = $("#correo").val();
  var rol = $("#roles").val();
  var tipo = $("#tipo").val();
  if (nombres == "" || correo == "" || nombre_usuario == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else {
    $.post('../api/v1/fulmuv/usuarios/update', {
      id_usuario: id_usuario,
      nombres: nombres,
      nombre_usuario: nombre_usuario,
      correo: correo,
      imagen: '',
      id: tipo,
      rol_id: rol
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empresa_detalle.php?id_empresa=" + id_empresa)
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  }
}

function showNotes(id_orden) {
  $("#notesLog").empty()
  $("#div_submit").empty()
  $("#show_notes_modal").modal("show")

  $.get('../api/v1/fulmuv/ordenes/' + id_orden + '/notas', {}, function (returnedData) {
    notesOrdenData = JSON.parse(returnedData);
    if (notesOrdenData.error == false) {
      $("#notesLog").empty()
      if (notesOrdenData.data.length) {
        notesOrdenData.data.forEach(function (note) {
          $("#notesLog").append(`
                      <div class="row g-3 timeline timeline-primary timeline-past pb-x1">
                        <div class="col-auto ps-4 ms-2">
                            <div class="ps-2">
                              <div class="avatar avatar-2xl">
                                <img class="rounded-circle" src="${note.imagen}" alt="">
                              </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row gx-0 border-bottom pb-x1">
                                <div class="col">
                                    <h6 class="text-800 mb-1">${note.usuario}</h6>
                                    <p class="fs-10 text-600 mb-0">${note.accion}</p>
                                </div>
                                <div class="col-auto">
                                    <p class="fs-11 text-500 mb-0">${note.created_at}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                      `)
        })
      } else {
        $("#notesLog").append(`
                  Nada que mostrar
              `)
      }
      $("#div_submit").append(`<button type="button" onclick="createOrdenNota(${id_orden})" class="btn btn-sm btn-iso"><i class='uil uil-message me-1'></i>Enviar</button>`)
      $("#show_notes_modal").modal("show")
      // Desplazar el scroll al fondo una vez que el modal esté completamente mostrado
      $('#show_notes_modal').on('shown.bs.modal', function () {
        $("#notesLog").scrollTop($("#notesLog")[0].scrollHeight);
      });
    } else {
      SweetAlert("error", notesPayment.msg);
    }
  });
}

function createOrdenNota(id_orden) {

  if ($("#comment").val() != "") {
    $.post('../api/v1/fulmuv/ordenes/' + id_orden + '/notas/create', {
      id_orden: id_orden,
      accion: $("#comment").val(),
      id_usuario: id_usuario

    }, function (returnedData) {
      returned = JSON.parse(returnedData);
      if (returned["error"] == false) {
        const date = new Date();
        const options = {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
          hour: 'numeric',
          minute: 'numeric',
          hour12: true
        };
        const formattedDate = new Intl.DateTimeFormat('en-US', options).format(date);
        $("#notesLog").append(`
              <div class="row g-3 timeline timeline-primary timeline-past pb-x1">
                        <div class="col-auto ps-4 ms-2">
                            <div class="ps-2">
                              <div class="avatar avatar-2xl">
                                <img class="rounded-circle" src="${$("#imagen_principal").val()}" alt="">
                              </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row gx-0 border-bottom pb-x1">
                                <div class="col">
                                    <h6 class="text-800 mb-1">Tú</h6>
                                    <p class="fs-10 text-600 mb-0">${$("#comment").val()}</p>
                                </div>
                                <div class="col-auto">
                                    <p class="fs-11 text-500 mb-0">${formattedDate}</p>
                                </div>
                            </div>
                        </div>
                    </div>
              `)
        $("#comment").val("")
        $("#notesLog").scrollTop($("#notesLog")[0].scrollHeight);
      } else {
        SweetAlert("error", returned.msg);
      }
    });
  } else {
    SweetAlert("error", "Por favor ingrese un comentario!!");
  }
}


function getNotas(value) {

  $.post('../api/v1/fulmuv/ordenes/notas', {
    id_empresa: id_empresa,
    tiempo: value
  }, function (returnedData) {
    notesOrdenData = JSON.parse(returnedData);
    if (notesOrdenData.error == false) {
      $("#notesLogAll").empty()
      if (notesOrdenData.data.length) {
        notesOrdenData.data.forEach(function (note) {
          $("#notesLogAll").append(`
              <div class="timeline-item position-relative">
                <div class="row g-0 align-items-center">
                    <div class="col-auto d-flex align-items-center">
                        <h6 class="timeline-item-date fs-11 text-500 mb-0 me-1" title="${note.created_at}"> ${note.tiempo}</h6>
                        <div class="position-relative">
                            <div class="icon-item icon-item-md shadow-none bg-200"><span class="text-primary fas fa-${note.tipo}"></span></div>
                        </div>
                    </div>
                    <div class="col ps-3 fs-10 text-500">
                        <div class="py-x1">
                            <h5 class="fs-10">${note.tipo == "envelope" ? `Orden #${note.id_orden}:` : ""} ${note.accion}</h5>
                            <p class="mb-0">${note.usuario}</p>
                        </div>
                        <hr class="text-200 my-0" />
                    </div>
                </div>
            </div>
          `)
        })
      } else {
        $("#notesLogAll").append(`
                  Nada que mostrar
              `)
      }
    } else {
      SweetAlert("error", notesPayment.msg);
    }
  });
}
function abrirMapa() {
  const el = document.getElementById('modalMapa');
  const modal = bootstrap.Modal.getOrCreateInstance(el, { backdrop: true, keyboard: true });

  // cuando el modal YA es visible, inicializamos/ajustamos el mapa
  const onShown = () => {
    el.removeEventListener('shown.bs.modal', onShown);
  };

  el.addEventListener('shown.bs.modal', onShown, { once: true });
  modal.show();

  // startPos viene de getStartPos() con coords reales si existen
  if (!(window.google && google.maps)) return;

  map = new google.maps.Map(document.getElementById("mapaEntrega"), {
    center: { lat: parseFloat(latitud), lng: parseFloat(longitud) },
    zoom: 16,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false
  });

  geocoder = new google.maps.Geocoder();
  placesService = new google.maps.places.PlacesService(map);

  marker = new google.maps.Marker({
    map,
    draggable: true,
    position: { lat: parseFloat(latitud), lng: parseFloat(longitud) },
  });

  // sincroniza globales + llena dirección
  latitud = latitud;
  longitud = longitud;
  obtenerDireccionDesdeCoords(latitud, longitud);

  // SearchBox
  const input = document.getElementById("buscarDireccion");
  if (!input) return;
  const searchBox = new google.maps.places.SearchBox(input);

  map.addListener('bounds_changed', () => searchBox.setBounds(map.getBounds()));
  searchBox.addListener('places_changed', () => {
    const places = searchBox.getPlaces();
    if (!places || !places.length) return;
    const place = places[0];
    if (!place.geometry) return;

    const pos = place.geometry.location;
    map.setCenter(pos);
    map.setZoom(16);
    marker.setPosition(pos);
    latitud = pos.lat();
    longitud = pos.lng();
    $("#direccion_mapa").val(place.formatted_address || place.name || input.value);
  });

  // Enter sin elegir sugerencia
  input.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const query = input.value.trim();
    if (!query) return;
    placesService.findPlaceFromQuery(
      { query, fields: ['name', 'geometry', 'formatted_address'] },
      (results, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK && results?.length) {
          const place = results[0];
          const pos = place.geometry.location;
          map.setCenter(pos);
          map.setZoom(16);
          marker.setPosition(pos);
          latitud = pos.lat();
          longitud = pos.lng();
          $("#direccion_mapa").val(place.formatted_address || place.name || input.value);
        }
      }
    );
  });

  // arrastrar marcador actualiza dirección
  marker.addListener("dragend", () => {
    const pos = marker.getPosition();
    latitud = pos.lat();
    longitud = pos.lng();
    obtenerDireccionDesdeCoords(latitud, longitud);
  });
}

function initMap(startPos) {
  // startPos viene de getStartPos() con coords reales si existen
  if (!(window.google && google.maps)) return;

  map = new google.maps.Map(document.getElementById("mapaEntrega"), {
    center: startPos,
    zoom: 14,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false
  });

  geocoder = new google.maps.Geocoder();
  placesService = new google.maps.places.PlacesService(map);

  marker = new google.maps.Marker({
    map,
    draggable: true,
    position: startPos,
  });

  // sincroniza globales + llena dirección
  latitud = startPos.lat;
  longitud = startPos.lng;
  obtenerDireccionDesdeCoords(latitud, longitud);

  // SearchBox
  const input = document.getElementById("buscarDireccion");
  if (!input) return;
  const searchBox = new google.maps.places.SearchBox(input);

  map.addListener('bounds_changed', () => searchBox.setBounds(map.getBounds()));
  searchBox.addListener('places_changed', () => {
    const places = searchBox.getPlaces();
    if (!places || !places.length) return;
    const place = places[0];
    if (!place.geometry) return;

    const pos = place.geometry.location;
    map.setCenter(pos);
    map.setZoom(16);
    marker.setPosition(pos);
    latitud = pos.lat();
    longitud = pos.lng();
    $("#direccion_mapa").val(place.formatted_address || place.name || input.value);
  });

  // Enter sin elegir sugerencia
  input.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const query = input.value.trim();
    if (!query) return;
    placesService.findPlaceFromQuery(
      { query, fields: ['name', 'geometry', 'formatted_address'] },
      (results, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK && results?.length) {
          const place = results[0];
          const pos = place.geometry.location;
          map.setCenter(pos);
          map.setZoom(16);
          marker.setPosition(pos);
          latitud = pos.lat();
          longitud = pos.lng();
          $("#direccion_mapa").val(place.formatted_address || place.name || input.value);
        }
      }
    );
  });

  // arrastrar marcador actualiza dirección
  marker.addListener("dragend", () => {
    const pos = marker.getPosition();
    latitud = pos.lat();
    longitud = pos.lng();
    obtenerDireccionDesdeCoords(latitud, longitud);
  });
}

function obtenerDireccionDesdeCoords(lat, lng, callback = null) {
  if (!geocoder) geocoder = new google.maps.Geocoder();
  const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
  geocoder.geocode({ location: latlng }, (results, status) => {
    if (status === "OK" && results && results[0]) {
      $("#direccion_mapa").val(results[0].formatted_address);
      const input = document.getElementById("buscarDireccion");
      if (input) input.value = results[0].formatted_address;
    }
    if (typeof callback === "function") callback();
  });
}

document.addEventListener('click', (e) => {
  if (e.target && e.target.id === 'guardarUbicacion') {
    if (isFinite(latitud) && isFinite(longitud)) {
      const direccion = $("#direccion_mapa").val() || "";
      $.post('../api/v1/fulmuv/empresas/updateUbicacion', {
        id_empresa: document.getElementById("id_empresa_detalle")?.value,
        latitud: String(latitud),
        longitud: String(longitud),
        direccion: direccion,
        tipo: $("#tipo_user").val()
      }, function (resp) {
        try { resp = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (_) { }
        if (!resp?.error) {
          $("#direccion_empresa").text(direccion);
          const modal = bootstrap.Modal.getInstance(document.getElementById('modalMapa'));
          modal?.hide();
          SweetAlert("url_success", "Ubicación actualizada correctamente.", "empresa_detalle.php?id_empresa=" + document.getElementById("id_empresa_detalle")?.value);
        } else {
          SweetAlert("error", resp?.msg || "No se pudo actualizar la ubicación.");
        }
      });
    } else {
      SweetAlert("warning", "No hay coordenadas válidas para guardar.");
    }
  }
});
