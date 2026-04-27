/* =========================================================
   ✅ 1) PEGAR ESTE BLOQUE (UNA SOLA VEZ) EN TU ARCHIVO JS
   (arriba del todo, fuera de addEventos)
========================================================= */

let map = null;
let marker = null;
let geocoder = null;
let placesService = null;
let latitud, longitud;
window.__mapsReady = false;
window.onMapsReady = function () { window.__mapsReady = true; };

const cantones = {
  "Azuay": ["Cuenca", "Camilo Ponce Enríquez", "Chordeleg", "El Pan", "Girón", "Gualaceo", "Nabón", "Oña", "Paute", "Pucará", "San Fernando", "Santa Isabel", "Sevilla de Oro", "Sigsig"],
  "Bolívar": ["Guaranda", "Chillanes", "Chimbo", "Echeandía", "Las Naves", "San Miguel"],
  "Cañar": ["Azogues", "Biblián", "Cañar", "Déleg", "El Tambo", "La Troncal", "Suscal"],
  "Carchi": ["Tulcán", "Bolívar", "Espejo", "Mira", "Montúfar", "San Pedro de Huaca"],
  "Cotopaxi": ["Latacunga", "La Maná", "Pangua", "Pujilí", "Salcedo", "Saquisilí", "Sigchos"],
  "Chimborazo": ["Riobamba", "Alausí", "Chambo", "Chunchi", "Colta", "Cumandá", "Guamote", "Guano", "Pallatanga", "Penipe"],
  "El Oro": ["Machala", "Arenillas", "Atahualpa", "Balsas", "Chilla", "El Guabo", "Huaquillas", "Las Lajas", "Marcabelí", "Pasaje", "Piñas", "Portovelo", "Santa Rosa", "Zaruma"],
  "Esmeraldas": ["Esmeraldas", "Atacames", "Eloy Alfaro", "Muisne", "Quinindé", "Rioverde", "San Lorenzo"],
  "Guayas": ["Guayaquil", "Alfredo Baquerizo Moreno", "Balao", "Balzar", "Colimes", "Daule", "Durán", "El Empalme", "El Triunfo", "General Antonio Elizalde", "Isidro Ayora", "Lomas de Sargentillo", "Marcelino Maridueña", "Milagro", "Naranjal", "Naranjito", "Nobol", "Palestina", "Pedro Carbo", "Playas", "Salitre", "Samborondón", "Santa Lucía", "Simón Bolívar", "Yaguachi"],
  "Imbabura": ["Ibarra", "Antonio Ante", "Cotacachi", "Otavalo", "Pimampiro", "San Miguel de Urcuquí"],
  "Loja": ["Loja", "Calvas", "Catamayo", "Celica", "Chaguarpamba", "Espíndola", "Gonzanamá", "Macará", "Olmedo", "Paltas", "Pindal", "Puyango", "Quilanga", "Saraguro", "Sozoranga", "Zapotillo"],
  "Los Ríos": ["Babahoyo", "Baba", "Buena Fe", "Mocache", "Montalvo", "Palenque", "Puebloviejo", "Quevedo", "Quinsaloma", "Urdaneta", "Valencia", "Ventanas", "Vinces"],
  "Manabí": ["Portoviejo", "Bolívar", "Chone", "El Carmen", "Flavio Alfaro", "Jama", "Jaramijó", "Jipijapa", "Junín", "Manta", "Montecristi", "Olmedo", "Paján", "Pedernales", "Pichincha", "Puerto López", "Rocafuerte", "Santa Ana", "Sucre", "Tosagua", "Veinticuatro de Mayo"],
  "Morona Santiago": ["Morona", "Gualaquiza", "Huamboya", "Limón Indanza", "Logroño", "Pablo Sexto", "Palora", "San Juan Bosco", "Sucúa", "Taisha", "Tiwintza"],
  "Napo": ["Tena", "Archidona", "Carlos Julio Arosemena Tola", "El Chaco", "Quijos"],
  "Pastaza": ["Puyo", "Arajuno", "Mera", "Santa Clara"],
  "Pichincha": ["Quito", "Cayambe", "Mejía", "Pedro Moncayo", "Pedro Vicente Maldonado", "Puerto Quito", "Rumiñahui", "San Miguel de Los Bancos"],
  "Tungurahua": ["Ambato", "Baños de Agua Santa", "Cevallos", "Mocha", "Patate", "Quero", "San Pedro de Pelileo", "Santiago de Píllaro", "Tisaleo"],
  "Zamora Chinchipe": ["Zamora", "Centinela del Cóndor", "Chinchipe", "El Pangui", "Nangaritza", "Palanda", "Paquisha", "Yacuambi", "Yantzaza"],
  "Galápagos": ["San Cristóbal", "Isabela", "Santa Cruz"],
  "Sucumbíos": ["Nueva Loja", "Cascales", "Cuyabeno", "Gonzalo Pizarro", "Lago Agrio", "Putumayo", "Shushufindi", "Sucumbíos"],
  "Orellana": ["Francisco de Orellana", "Aguarico", "La Joya de Los Sachas", "Loreto"],
  "Santo Domingo de los Tsáchilas": ["Santo Domingo"],
  "Santa Elena": ["Santa Elena", "La Libertad", "Salinas"]
};


function waitMapsReady(cb) {
  if (window.__mapsReady && window.google && google.maps) return cb();
  const t = setInterval(() => {
    if (window.__mapsReady && window.google && google.maps) { clearInterval(t); cb(); }
  }, 150);
}

function defaultPos() {
  return { lat: -2.189412, lng: -79.889066 }; // Ecuador (ajusta si quieres)
}

function setDireccionUI(address) {
  if ($("#direccionExacta").length) $("#direccionExacta").val(address || "");
  const input = document.getElementById("buscarDireccion");
  if (input) input.value = address || "";
}

function reverseGeocode(lat, lng) {
  if (!geocoder) geocoder = new google.maps.Geocoder();
  geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, (results, status) => {
    if (status === "OK" && results && results[0]) {
      setDireccionUI(results[0].formatted_address || "");
    }
  });
}

function cargarCantones(provincia) {
  const cantonSelect = document.getElementById("cantonEvento");
  cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';

  if (provincia && cantones[provincia]) {
    cantones[provincia].forEach(canton => {
      const option = document.createElement("option");
      option.value = canton;
      option.textContent = canton;
      cantonSelect.appendChild(option);
    });
  }
}


function initMapaEnModalCrear(modalId, startPos = null) {
  waitMapsReady(() => {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;

    // evita duplicar en reaperturas
    if (modalEl.__mapShownHandler) {
      modalEl.removeEventListener("shown.bs.modal", modalEl.__mapShownHandler);
      modalEl.__mapShownHandler = null;
    }

    const onShown = () => {
      const mapEl = document.getElementById("mapaEntrega");
      if (!mapEl) return;

      // ✅ importante: altura del mapa
      mapEl.style.height = mapEl.style.height || "320px";

      const pos = startPos || defaultPos();

      map = new google.maps.Map(mapEl, {
        center: pos,
        zoom: 15,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false
      });

      geocoder = new google.maps.Geocoder();
      placesService = new google.maps.places.PlacesService(map);

      marker = new google.maps.Marker({
        map,
        draggable: true,
        position: pos
      });

      // Dirección inicial por coords
      reverseGeocode(pos.lat, pos.lng);

      // ✅ click en mapa mueve marcador
      marker.addListener("dragend", () => {
        const p = marker.getPosition();
        if (!p) return;

        // ✅ guardar global
        latitud = p.lat();
        longitud = p.lng();

        reverseGeocode(latitud, longitud);
      });


      // ✅ drag marker actualiza dirección
      marker.addListener("dragend", () => {
        const p = marker.getPosition();
        if (!p) return;
        reverseGeocode(p.lat(), p.lng());
      });

      // ✅ SearchBox
      const input = document.getElementById("buscarDireccion");
      if (input) {
        // limpiar listeners anteriores
        if (input.__enterHandler) input.removeEventListener("keydown", input.__enterHandler);

        const searchBox = new google.maps.places.SearchBox(input);
        map.addListener("bounds_changed", () => searchBox.setBounds(map.getBounds()));

        searchBox.addListener("places_changed", () => {
          const places = searchBox.getPlaces();
          if (!places || !places.length) return;
          const place = places[0];
          if (!place.geometry) return;

          const loc = place.geometry.location;
          const p = { lat: loc.lat(), lng: loc.lng() };

          latitud = p.lat;
          longitud = p.lng;

          marker.setPosition(p);
          map.setCenter(p);
          map.setZoom(16);

          setDireccionUI(place.formatted_address || place.name || input.value);
        });

        // ✅ ENTER sin seleccionar sugerencia
        input.__enterHandler = (e) => {
          if (e.key !== "Enter") return;
          e.preventDefault();
          const query = input.value.trim();
          if (!query) return;

          placesService.findPlaceFromQuery(
            { query, fields: ["name", "geometry", "formatted_address"] },
            (results, status) => {
              if (status === google.maps.places.PlacesServiceStatus.OK && results?.length) {
                const r = results[0];
                const loc = r.geometry.location;
                const p = { lat: loc.lat(), lng: loc.lng() };

                latitud = p.lat;
                longitud = p.lng;

                marker.setPosition(p);
                map.setCenter(p);
                map.setZoom(16);

                setDireccionUI(r.formatted_address || r.name || query);
              }
            }
          );
        };
        input.addEventListener("keydown", input.__enterHandler);
      }

      // ✅ fix render en modal
      setTimeout(() => {
        google.maps.event.trigger(map, "resize");
        map.setCenter(marker.getPosition());
      }, 250);
    };

    modalEl.__mapShownHandler = onShown;
    modalEl.addEventListener("shown.bs.modal", onShown, { once: true });
  });
}

/* =========================================================
   ✅ 2) AHORA SOLO AGREGA ESTO DENTRO DE addEventos()
   JUSTO DESPUÉS DE:
   $("#btnModal").trigger("click");
========================================================= */

// initMapaEnModalCrear("modalEvento", defaultPos());

/* =========================================================
   ✅ 3) (IMPORTANTE) El script de Google debe tener callback:
   <script src="https://maps.googleapis.com/maps/api/js?key=TU_KEY&libraries=places&callback=onMapsReady" async defer></script>
========================================================= */



let atributos = [];
var imagenActual = ""
var id_empresa = $("#id_empresa").val()
let tipo_user = $("#tipo_user").val();

$(document).ready(function () {
  $.get("../api/v1/fulmuv/eventosEmpresa/" + id_empresa + '/' + tipo_user, {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      $("#lista_categorias").text("");
      returned.data.forEach((eventos) => {
        $("#lista_categorias").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium">
              <img src="../admin/${eventos.imagen}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" style="width:80px;height:80px;object-fit:scale-down">
            </td>
            <td class="py-2 align-middle text-wrap fs-9">${eventos.titulo || ""}</td>
            <td class="py-2 align-middle text-wrap fs-9">${eventos.descripcion || ""}</td>
            <td class="py-2 align-middle fs-9">${eventos.tipo || ""}</td>
            <td class="py-2 align-middle fs-9">${eventos.fecha_hora_inicio || ""}</td>
            <td class="py-2 align-middle fs-9">${eventos.fecha_hora_fin || ""}</td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                  <span class="fas fa-ellipsis-h fs-10"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border py-0">
                  <div class="py-2">
                    <a class="dropdown-item text-primary" onclick="viewEvento(${eventos.id_evento})">Visualizar</a>
                    <a class="dropdown-item text-info" onclick="EventosById(${eventos.id_evento})">Actualizar</a>
                    <a class="dropdown-item text-danger" onclick="remove(${eventos.id_evento}, 'eventos')">Eliminar</a>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        `);
      });
      $("#my_table").DataTable({
        searching: true,
        responsive: false,
        pageLength: 8,
        info: true,
        lengthChange: false,
        // language: {
        //   url: "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
        //   paginate: {
        //     next: '<span class="fas fa-chevron-right"></span>',
        //     previous: '<span class="fas fa-chevron-left"></span>',
        //   },
        // },
        dom:
          "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" +
          "<'table-responsive scrollbar'tr>" +
          "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>",
      });
    }
  });

  $.get("../api/v1/fulmuv/atributos/", {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      atributos = returned.data;
    }
  });
});

function asignar_atributo(id_eventos) {
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
            <div class="p-4">
              <div class="row g-2">
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="exampleFormControlInput1">Atributos</label>
                  <select class="form-select" id="atributos" multiple></select>
                </div>
                <div class="col-12">
                  <button onclick="UpdateEventos(${id_eventos})" class="btn btn-primary" type="submit">Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  const organizerMultiple = document.getElementById("atributos");
  const choices = new Choices(organizerMultiple, {
    removeItemButton: true,
    placeholder: true,
    placeholderValue: "Seleccione atributos",
    allowHTML: true,
    position: "bottom",
  });

  atributos.forEach((a) => {
    choices.setChoices([
      {
        value: a.id_atributo.toString(),
        label: a.nombre,
        selected: false,
        disabled: false,
      },
    ]);
  });

  $.get(
    "../api/v1/fulmuv/atributosEventos/" + id_eventos,
    {},
    function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        var atri = JSON.parse(returned.data.atributos);
        atri.forEach((val) => {
          choices.setChoiceByValue(val.toString());
        });
        $("#btnModal").click();
      }
    }
  );
}
/*$.get('../api/v1/fulmuv/atributos/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#atributos").text("");
      returned.data.forEach(atributos => {
        $("#atributos").append(
          `<option value="${atributos.id_atributo}">${atributos.nombre}</option>`
        );
      });
      const organizerMultiple = document.getElementById('atributos');
      const choices = new Choices(organizerMultiple, {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'Seleccione atributos',
      });
    }
  });*/
$("#btnModal").click();

function remove(id, tabla) {
  swal(
    {
      title: "Alerta",
      text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#27b394",
      confirmButtonText: "Sí",
      cancelButtonText: "No",
      closeOnConfirm: false,
    },
    function () {
      $.post(
        `../api/v1/fulmuv/${tabla}/delete`,
        { id: id },
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (!returned.error) {
            SweetAlert("url_success", returned.msg, "eventos.php");
          } else {
            SweetAlert("error", returned.msg);
          }
        }
      );
    }
  );
}

function UpdateEventos(id_eventos) {
  console.log($("#atributos").val());
  /*$.post(
    "../api/v1/fulmuv/updateEventos",
    {
      id_eventos: id_eventos,
      atributos: $("#atributos").val(),
    },
    function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "eventos.php");
      } else {
        SweetAlert("error", returned.msg);
      }
    }
  );*/
}
function addEventos() {
  // ==== Construye el modal ====
  $("#alert").empty().append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalEvento" style="display:none;"></button>

    <div class="modal fade" id="modalEvento" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEventoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0">
          <div class="modal-header bg-light">
            <h5 class="modal-title w-100 text-center" id="modalEventoLabel">Agregar Evento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body p-4">
            <form id="formEvento" enctype="multipart/form-data">
              <!-- =============== DATOS GENERALES =============== -->
              <h6 class="mb-3 fw-bold text-uppercase">Datos Generales</h6>
              <div class="row g-3">
                <div class="col-lg-6">
                  <label class="form-label">Título del Evento <span class="text-danger">*</span></label>
                  <input type="text" id="tituloEvento" class="form-control" maxlength="150" required>
                </div>

                <div class="col-lg-6">
                  <label class="form-label">Tipo de Evento <span class="text-danger">*</span></label>
                  <select id="tipoEvento" class="form-select" required>
                    <option value="">Seleccione…</option>
                  </select>
                </div>

                <div class="col-lg-6">
                  <label class="form-label">Subtipo(s) de Evento</label>
                  <select id="subtipoEvento" class="form-select" multiple disabled>
                    <!-- se llena por ajax al escoger un tipo -->
                  </select>
                  <div class="form-text">Puede seleccionar varios subtipos.</div>
                </div>

                <div class="col-12">
                  <label class="form-label">Descripción Detallada <span class="text-danger">*</span></label>
                  <textarea id="descripcionEvento" class="form-control" rows="4" maxlength="1000" required></textarea>
                </div>

                <div class="col-lg-6">
                  <label class="form-label">Organizador / Club / Empresa <span class="text-danger">*</span></label>
                  <input type="text" id="organizador" class="form-control" required>
                </div>

                <div class="col-lg-6">
                  <label class="form-label">Enlace Web o Redes Sociales (opcional)</label>
                  <input type="url" id="enlaceEvento" class="form-control" placeholder="https://">
                </div>
              </div>

              <hr class="my-4">

              <!-- =============== FECHAS Y HORARIOS =============== -->
              <h6 class="mb-3 fw-bold text-uppercase">Fechas y Horarios</h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Fecha y hora de inicio <span class="text-danger">*</span></label>
                  <input type="datetime-local" id="fechaHoraInicio" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fecha y hora de fin <span class="text-danger">*</span></label>
                  <input type="datetime-local" id="fechaHoraFin" class="form-control" required>
                </div>
              </div>

              <hr class="my-4">

              <div class="row">
                  <div class="col-lg-12">
                      <div class="map-wrapper position-relative">
                          <input type="hidden" id="direccionExacta">
                          <div id="mapaEntrega"></div>

                          <div class="map-search">
                              <div class="input-group">
                                  <input id="buscarDireccion" class="form-control form-control-sm"
                                      style="width: clamp(200px, 39vw, 400px); margin-top:10px; background:#fff; height:40px"
                                      placeholder="Buscar dirección..." />
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <!-- =============== UBICACIÓN =============== -->
              <!--h6 class="mb-3 fw-bold text-uppercase">Ubicación</h6-->
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Provincia <span class="text-danger">*</span></label>
                  <select id="provinciaEvento" class="form-select" required onchange="cargarCantones(this.value)">
                    <option value="">Seleccione provincia</option>
                    <option value="Azuay">Azuay</option>
                    <option value="Bolívar">Bolívar</option>
                    <option value="Cañar">Cañar</option>
                    <option value="Carchi">Carchi</option>
                    <option value="Cotopaxi">Cotopaxi</option>
                    <option value="Chimborazo">Chimborazo</option>
                    <option value="El Oro">El Oro</option>
                    <option value="Esmeraldas">Esmeraldas</option>
                    <option value="Guayas">Guayas</option>
                    <option value="Imbabura">Imbabura</option>
                    <option value="Loja">Loja</option>
                    <option value="Los Ríos">Los Ríos</option>
                    <option value="Manabí">Manabí</option>
                    <option value="Morona Santiago">Morona Santiago</option>
                    <option value="Napo">Napo</option>
                    <option value="Pastaza">Pastaza</option>
                    <option value="Pichincha">Pichincha</option>
                    <option value="Tungurahua">Tungurahua</option>
                    <option value="Zamora Chinchipe">Zamora Chinchipe</option>
                    <option value="Galápagos">Galápagos</option>
                    <option value="Sucumbíos">Sucumbíos</option>
                    <option value="Orellana">Orellana</option>
                    <option value="Santo Domingo de los Tsáchilas">Santo Domingo de los Tsáchilas</option>
                    <option value="Santa Elena">Santa Elena</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Cantón <span class="text-danger">*</span></label>
                  <select id="cantonEvento" class="form-select" required>
                    <option value="">Seleccione…</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Modalidad <span class="text-danger">*</span></label>
                  <select id="modalidad" class="form-select" required>
                    <option value="">Seleccione…</option>
                    <option value="presencial">Presencial</option>
                    <option value="virtual">Virtual</option>
                    <option value="hibrido">Híbrido</option>
                  </select>
                </div>
              </div>

              <hr class="my-4">

              <!-- =============== PARTICIPACIÓN =============== -->
              <h6 class="mb-3 fw-bold text-uppercase">Participación</h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Tipo de Entrada <span class="text-danger">*</span></label>
                  <select id="tipoEntrada" class="form-select" required>
                    <option value="">Seleccione…</option>
                    <option value="gratuita">Gratuita</option>
                    <option value="pagada">Pagada</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Precio (valor general o por secciones) <span class="text-danger">*</span></label>
                  <select id="tipoPrecio" class="form-select" required>
                    <option value="">Seleccione…</option>
                    <option value="gratis">Gratis</option>
                    <option value="precio_unico">Precio único</option>
                    <option value="por_secciones">Por secciones</option>
                  </select>
                </div>

                <div class="col-md-6" id="wrapMontoPrecio" style="display:none;">
                  <label class="form-label">Monto (USD)</label>
                  <input type="number" id="montoPrecio" class="form-control" min="0" step="0.01" placeholder="0.00">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Enlace de compra/registro (opcional)</label>
                  <input type="url" id="enlaceCompra" class="form-control" placeholder="https://">
                </div>
              </div>

              <hr class="my-4">

              <!-- =============== MULTIMEDIA (portada + galería) =============== -->
              <h6 class="mb-3 fw-bold text-uppercase">Multimedia</h6>
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Portada del Evento (Imagen Destacada) <span class="text-danger">*</span></label>
                  <!-- usa id="imagen" porque tu guardarEventos() espera ese id -->
                  <input class="form-control" type="file" id="imagen" accept="image/*" required>
                </div>

                <!-- Dropzone Galería -->
                <div class="col-12">
                  <label class="form-label">Galería de Imágenes (hasta 5)</label>
                  <div id="galeriaDropzone" class="p-4 border border-2 rounded-3 text-center bg-light" style="cursor:pointer;">
                    <div class="fw-semibold mb-1">Arrastra y suelta imágenes aquí</div>
                    <div class="text-muted mb-2">o</div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnPickGaleria">Seleccionar</button>
                    <input type="file" id="galeriaInput" accept="image/*" multiple class="d-none">
                    <div class="form-text mt-2"><span id="galeriaContador">0</span>/5 imágenes</div>
                  </div>
                  <div id="galeriaLista" class="row g-3 mt-2"></div>
                </div>
              </div>

              <hr class="my-4">

              <!-- =============== CONTACTO =============== -->
              <h6 class="mb-3 fw-bold text-uppercase">Contacto</h6>
              <div class="row g-3">
                <div class="col-lg-6">
                  <label class="form-label">Nombre de contacto <span class="text-danger">*</span></label>
                  <input type="text" id="contactoNombre" class="form-control" required>
                </div>
                <div class="col-lg-6">
                  <label class="form-label">Teléfono / WhatsApp (puede ingresar varios)</label>
                  <input type="text" id="contactoTelefonos" class="form-control" placeholder="0999999999, 022222222">
                </div>
                <div class="col-lg-6">
                  <label class="form-label">Correo electrónico (opcional)</label>
                  <input type="email" id="contactoEmail" class="form-control" placeholder="correo@dominio.com">
                </div>
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarEvento">Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para previsualizar una imagen de la galería -->
    <div class="modal fade" id="previewImgModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-black">
          <img id="previewImg" class="img-fluid rounded" alt="Vista previa">
        </div>
      </div>
    </div>
  `);

  // Mostrar modal
  $("#btnModal").trigger("click");
  initMapaEnModalCrear("modalEvento", defaultPos());

  const $modal = $("#modalEvento");

  // ==== Helpers Select2 ====
  function ensureSelect2($el, opts = {}) {
    if ($el.data('select2')) { $el.select2('destroy'); }
    $el.select2(Object.assign({ width: '100%', dropdownParent: $('#modalEvento') }, opts));
  }
  const pickId = (o, keys) => { for (const k of keys) if (o[k] != null) return o[k]; return null; };
  const pickText = (o, keys) => { for (const k of keys) if (o[k] != null) return o[k]; return '—'; };

  // ==== PROVINCIA / CANTÓN (desde datosEcuador global) ====
  // (function initProvinciaCanton() {
  //   const $prov = $("#provinciaEvento");
  //   const $cant = $("#cantonEvento");

  //   $prov.find("option:not(:first)").remove();
  //   Object.entries(datosEcuador || {}).forEach(([codProv, obj]) => {
  //     $prov.append(`<option value="${codProv}">${capitalizarPrimeraLetra(obj.provincia)}</option>`);
  //   });

  //   $prov.off("change.ev").on("change.ev", function () {
  //     const cod = $(this).val();
  //     $cant.empty().append(`<option value="">Seleccione…</option>`);
  //     if (!cod || !datosEcuador[cod]) return;
  //     const cantones = datosEcuador[cod].cantones || {};
  //     Object.entries(cantones).forEach(([codC, obj]) => {
  //       $cant.append(`<option value="${codC}">${capitalizarPrimeraLetra(obj.canton)}</option>`);
  //     });
  //   });
  // })();

  // ==== UX precio/entrada ====
  $("#tipoEntrada, #tipoPrecio").off("change.pre").on("change.pre", function () {
    const tipoEntrada = $("#tipoEntrada").val();
    const tipoPrecio = $("#tipoPrecio").val();
    const showMonto = (tipoEntrada === "pagada") && (tipoPrecio !== "gratis");
    $("#wrapMontoPrecio").toggle(showMonto);
    if (!showMonto) $("#montoPrecio").val("");
  });

  // ==== Select2: Tipo y Subtipo dependiente ====
  (function initTiposYSubtipos() {
    const $tipo = $("#tipoEvento");
    const $sub = $("#subtipoEvento");

    // Cargar TIPOS
    $.get("../api/v1/fulmuv/getTipoEvento/", function (r) {
      console.log(r)
      if (r.error === false) {
        $tipo.empty().append(`<option value="">Seleccione…</option>`);
        r.data.forEach(it => {
          const id = pickId(it, ['id_tipo_evento', 'id', 'codigo', 'ID']);
          const text = pickText(it, ['nombre', 'nombre_tipo', 'titulo', 'name']);
          if (id != null) $tipo.append(`<option value="${id}">${text}</option>`);
        });
        ensureSelect2($tipo, { placeholder: 'Seleccione…', allowClear: true });

        // on change: cargar SUBTIPOS
        $tipo.off('change.sub').on('change.sub', function () {
          const id_tipo = $(this).val();

          $sub.prop('disabled', true);
          try { $sub.select2('destroy'); } catch (_) { }
          $sub.empty();

          if (!id_tipo) return;

          // Nota: la ruta que enviaste tiene /fulmuv/fulmuv/getSubTipoEvento
          $.post("../api/v1/fulmuv/getSubTipoEvento", { id_tipo }, function (rr) {
            if (rr.error === false) {
              rr.data.forEach(st => {
                const id = pickId(st, ['id_subtipo', 'id_subtipo_eventos', 'id', 'codigo', 'ID']);
                const text = pickText(st, ['nombre', 'nombre_subtipo', 'titulo', 'name']);
                if (id != null) $sub.append(`<option value="${id}">${text}</option>`);
              });
              $sub.prop('disabled', false);
              ensureSelect2($sub, { placeholder: 'Seleccione subtipo(s)…', allowClear: true, multiple: true });
            }
          }, 'json');
        });
      }
    }, 'json');
  })();

  // ==== DROPZONE Galería (delegación, funciona en modal dinámico) ====
  (function initGaleriaDelegada() {
    const MAX = 5;

    const getArr = () => $('#galeriaDropzone').data('galeria') || [];
    const setArr = (arr) => { $('#galeriaDropzone').data('galeria', arr); renderLista(); actualizarUI(); };

    // expone para guardarEventos()
    window.getGaleriaSeleccion = () => [...getArr()];

    // abrir selector nativo
    $(document)
      .off('click.pickGaleria')
      .on('click.pickGaleria', '#btnPickGaleria', () => $('#galeriaInput').trigger('click'));

    // input change
    $(document)
      .off('change.galeriaInput')
      .on('change.galeriaInput', '#galeriaInput', function () {
        anexarArchivos(this.files);
        this.value = '';
      });

    // drag & drop
    $(document)
      .off('dragenter.dz dragover.dz', '#galeriaDropzone')
      .on('dragenter.dz dragover.dz', '#galeriaDropzone', function (e) {
        e.preventDefault(); e.stopPropagation();
        if (getArr().length < MAX) $(this).addClass('border-primary bg-white');
      });

    $(document)
      .off('dragleave.dz dragend.dz', '#galeriaDropzone')
      .on('dragleave.dz dragend.dz', '#galeriaDropzone', function (e) {
        e.preventDefault(); e.stopPropagation();
        $(this).removeClass('border-primary bg-white');
      });

    $(document)
      .off('drop.dz', '#galeriaDropzone')
      .on('drop.dz', '#galeriaDropzone', function (e) {
        e.preventDefault(); e.stopPropagation();
        $(this).removeClass('border-primary bg-white');
        const dt = e.originalEvent.dataTransfer;
        if (dt && dt.files) anexarArchivos(dt.files);
      });

    // botones tarjetas
    $(document)
      .off('click.gal.remove', '#galeriaLista .btn-remove')
      .on('click.gal.remove', '#galeriaLista .btn-remove', function () {
        const idx = Number($(this).closest('[data-idx]').data('idx'));
        const arr = getArr(); arr.splice(idx, 1); setArr(arr);
      });

    $(document)
      .off('click.gal.preview', '#galeriaLista .btn-preview')
      .on('click.gal.preview', '#galeriaLista .btn-preview', function () {
        const idx = Number($(this).closest('[data-idx]').data('idx'));
        const file = getArr()[idx];
        if (!file) return;
        $('#previewImg').attr('src', URL.createObjectURL("../admin/" + file));
        new bootstrap.Modal(document.getElementById('previewImgModal')).show();
      });

    function actualizarUI() {
      const n = getArr().length;
      $('#galeriaContador').text(n);
      const lleno = n >= MAX;
      $('#btnPickGaleria').prop('disabled', lleno);
      $('#galeriaDropzone').toggleClass('opacity-50', lleno)
        .css('pointer-events', lleno ? 'none' : 'auto');
    }

    function renderLista() {
      const $lista = $('#galeriaLista').empty();
      getArr().forEach((file, idx) => {
        const url = URL.createObjectURL(file);
        $lista.append(`
          <div class="col-6 col-md-4 col-lg-3" data-idx="${idx}">
            <div class="card h-100 shadow-sm">
              <img src="${url}" class="card-img-top" style="aspect-ratio:1/1;object-fit:cover;">
              <div class="card-body p-2">
                <div class="d-flex justify-content-between">
                  <button type="button" class="btn btn-sm btn-outline-secondary btn-preview">Visualizar</button>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Eliminar</button>
                </div>
              </div>
            </div>
          </div>
        `);
      });
    }

    function anexarArchivos(fileList) {
      if (!fileList || !fileList.length) return;
      let arr = getArr();
      const espacio = MAX - arr.length;
      if (espacio <= 0) { alert('Has alcanzado el límite de 5 imágenes.'); return; }

      const nuevos = Array.from(fileList)
        .filter(f => f.type && f.type.startsWith('image/'))
        .slice(0, espacio)
        .filter(f => !arr.some(a => a.name === f.name && a.size === f.size && a.lastModified === f.lastModified));

      if (!nuevos.length) return;
      arr = arr.concat(nuevos);
      setArr(arr);
    }

    // estado inicial
    setArr([]);

    // limpiar al cerrar el modal principal
    document.getElementById("modalEvento").addEventListener("hidden.bs.modal", () => {
      setArr([]);
      $('#galeriaLista').empty();
      $('#galeriaContador').text('0');
    });
  })();

  // ==== Guardar ====
  $("#btnGuardarEvento").off("click.save").on("click.save", function () {
    // Si quieres enviar subtipos: const subs = $("#subtipoEvento").val() || [];
    // y adjuntarlos en tu guardarEventos()
    guardarEventos("crear");

  });
}




async function guardarEventos(modo = "crear") {
  setBtnLoading("#btnGuardarEvento", true);

  try {
    const id_empresa = $("#id_empresa").val();

    const titulo = $("#tituloEvento").val()?.trim();
    const tipo = $("#tipoEvento").val();
    const subtipos = $("#subtipoEvento").val() || [];
    const descripcion = $("#descripcionEvento").val()?.trim();
    const organizador = $("#organizador").val()?.trim();
    const enlace = $("#enlaceEvento").val()?.trim();

    const fecha_hora_inicio = $("#fechaHoraInicio").val();
    const fecha_hora_fin = $("#fechaHoraFin").val();

    const direccion = $("#direccionExacta").val()?.trim();

    // ✅ OJO: tu select usa VALUE = "Los Ríos", etc.
    // Si quieres guardar el VALUE, usa .val()
    const provincia = $("#provinciaEvento").val() || "";
    const canton = $("#cantonEvento").val() || "";
    const modalidad = $("#modalidad").val();

    const tipo_entrada = $("#tipoEntrada").val();
    const tipo_precio = $("#tipoPrecio").val();
    const montoPrecio = $("#montoPrecio").val();

    const portadaFiles = $("#imagen")[0]?.files || [];
    const portadaFile = portadaFiles.length ? portadaFiles[0] : null;

    const galeriaFiles = (window.getGaleriaSeleccion && window.getGaleriaSeleccion()) || [];

    const nombre_contacto = $("#contactoNombre").val()?.trim();
    const telefono = $("#contactoTelefonos").val()?.trim();
    const correo = $("#contactoEmail").val()?.trim();
    const enlace_compra = $("#enlaceCompra").val()?.trim();

    // ✅ Validación
    const faltantes = [];
    if (!titulo) faltantes.push("Nombre/Título del evento");
    if (!tipo) faltantes.push("Tipo de evento");
    if (!descripcion) faltantes.push("Descripción detallada");
    if (!organizador) faltantes.push("Organizador / Club / Empresa");
    if (!fecha_hora_inicio) faltantes.push("Fecha y hora de inicio");
    if (!fecha_hora_fin) faltantes.push("Fecha y hora de fin");
    if (!provincia) faltantes.push("Provincia");
    if (!canton) faltantes.push("Cantón");
    if (!modalidad) faltantes.push("Modalidad");
    if (!tipo_entrada) faltantes.push("Tipo de entrada");
    if (!tipo_precio) faltantes.push("Precio (tipo)");
    if (!nombre_contacto) faltantes.push("Nombre de contacto");
    if (!portadaFile) faltantes.push("Portada del evento (imagen)");

    if (faltantes.length) {
      SweetAlert("warning", "Completa los siguientes campos:<br><br>• " + faltantes.join("<br>• "));
      return;
    }

    if (new Date(fecha_hora_inicio) > new Date(fecha_hora_fin)) {
      SweetAlert("warning", "La fecha/hora de inicio no puede ser mayor que la de fin.");
      return;
    }

    // ✅ coordenadas: si aún no hay, usa default (para evitar undefined)
    if (typeof latitud === "undefined" || latitud === null) latitud = defaultPos().lat;
    if (typeof longitud === "undefined" || longitud === null) longitud = defaultPos().lng;

    const precioPayload = {
      tipo: tipo_precio,
      monto: (tipo_entrada === "pagada" && tipo_precio !== "gratis" && montoPrecio)
        ? Number(montoPrecio)
        : null
    };

    // 1) Subir portada
    const portada = await saveFiles(portadaFile); // {img:"..."}

    // 2) Crear evento
    const payloadEvento = {
      id_empresa,
      titulo,
      descripcion,
      organizador,
      enlace,
      fecha_hora_inicio,
      fecha_hora_fin,

      tipo_evento: tipo,
      subtipo_evento: JSON.stringify(subtipos),

      direccion,
      provincia,
      canton,
      modalidad,

      tipo_entrada,
      precio_secciones: JSON.stringify(precioPayload),
      enlace_compra,

      // ✅ ojo con la ruta: en tu listado haces ../admin/${eventos.imagen}
      // si tu API espera "imagen" sin prefijo, deja portada.img tal cual te devuelve cargar_imagen.php
      imagen: portada.img,
      portada_evento: portada.img,

      nombre_contacto,
      telefono,
      correo,

      estado: "A",
      tipo_user: tipo_user,

      latitud: latitud,
      longitud: longitud
    };

    const resCrear = await postJSON("../api/v1/fulmuv/eventos/create", payloadEvento);

    if (resCrear.error) {
      SweetAlert("error", resCrear.msg || "No se pudo crear el evento.");
      return;
    }

    const id_evento = resCrear.data?.id_evento;

    // 3) Subir galería
    if (galeriaFiles.length && id_evento) {
      const urlsGaleria = await saveManyFiles(galeriaFiles); // ['url1','url2']
      if (urlsGaleria.length) {
        await postJSON("../api/v1/fulmuv/eventos/galeria/create", {
          id_evento,
          imagenes: JSON.stringify(urlsGaleria)
        });
      }
    }

    SweetAlert("url_success", "Evento creado correctamente.", "eventos.php");

  } catch (e) {
    console.error(e);
    SweetAlert("error", "Ocurrió un error al crear el evento.");
  } finally {
    setBtnLoading("#btnGuardarEvento", false);
  }
}

function saveFiles(file) {
  return new Promise(function (resolve, reject) {
    const formData = new FormData();
    formData.append("archivos[]", file);
    $.ajax({
      type: "POST",
      data: formData,
      url: "../admin/cargar_imagen.php",
      cache: false,
      contentType: false,
      processData: false,
      success: function (returnedImagen) {
        if (returnedImagen.response === "success") {
          resolve(returnedImagen.data);
        } else {
          SweetAlert("error", "Error al guardar archivo: " + returnedImagen.error);
          reject();
        }
      },
    });
  });
}

function EventosById(eventos) {
  $.get("../api/v1/fulmuv/eventos/" + eventos, function (returnedData) {
    if (!returnedData.error) {
      let evento = returnedData.data;
      imagenActual = returnedData.data.imagen

      $("#staticBackdrop").remove();
      $("#alert").text("");
      $("#alert").append(`
        <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>

        <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0">
              <div class="modal-header bg-light">
                <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">Actualizar Eventos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body p-4">
                <div class="mb-3">
                  <label for="formFile" class="form-label">Imagen del Evento</label>
                  <input class="form-control" type="file" id="imagen">
                  

                  <div class="mt-2">
                    <img src="${evento.imagen}" alt="Imagen actual" class="img-fluid rounded" style="max-height: 150px;">
                  </div>
                </div>

                <div class="mb-3">
                  <label for="tipoEvento" class="form-label">Tipo de Evento</label>
                  <select class="form-select" id="tipoEvento">
                    <option value="">Seleccione tipo</option>
                    <option value="Conferencia" ${evento.tipo === 'Conferencia' ? 'selected' : ''}>Conferencia</option>
                    <option value="Seminario" ${evento.tipo === 'Seminario' ? 'selected' : ''}>Seminario</option>
                    <option value="Feria" ${evento.tipo === 'Feria' ? 'selected' : ''}>Feria</option>
                    <option value="Festival" ${evento.tipo === 'Festival' ? 'selected' : ''}>Festival</option>
                    <option value="Taller" ${evento.tipo === 'Taller' ? 'selected' : ''}>Taller</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="tituloEvento" class="form-label">Título del Evento</label>
                  <input class="form-control" type="text" id="tituloEvento" value="${evento.titulo}" placeholder="Ingrese el título">
                </div>

                <div class="mb-3">
                  <label for="descripcionEvento" class="form-label">Descripción</label>
                  <textarea class="form-control" id="descripcionEvento" rows="5" placeholder="Describa brevemente el evento">${evento.descripcion}</textarea>
                </div>

                <div class="mb-3">
                  <label for="fechaHoraInicio" class="form-label">Fecha y hora de inicio</label>
                  <input class="form-control" type="datetime-local" id="fechaHoraInicio" value="${evento.fecha_hora_inicio.replace(' ', 'T')}">
                </div>

                <div class="mb-3">
                  <label for="fechaHoraFin" class="form-label">Fecha y hora de finalización</label>
                  <input class="form-control" type="datetime-local" id="fechaHoraFin" value="${evento.fecha_hora_fin.replace(' ', 'T')}">
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="editEventos(${evento.id_evento})" id="btnEditarEvento">Guardar</button>
              </div>
            </div>
          </div>
        </div>
      `);

      // Mostrar el modal
      $("#btnModal").click();
    }
  }, "json");
}

$("#btnEditarEvento").off("click.edit").on("click.edit", function () {
  editEventos(evento.id_evento);
});

async function editEventos(id_eventos) {
  setBtnLoading("#btnEditarEvento", true);

  try {
    const titulo = $("#tituloEvento").val()?.trim();
    const descripcion = $("#descripcionEvento").val()?.trim();
    const tipo = $("#tipoEvento").val();
    const fecha_hora_inicio = $("#fechaHoraInicio").val();
    const fecha_hora_fin = $("#fechaHoraFin").val();

    const files = $("#imagen")[0]?.files || [];
    const file = files.length ? files[0] : null;

    if (!titulo || !descripcion || !tipo || !fecha_hora_inicio || !fecha_hora_fin) {
      SweetAlert("warning", "¡Campos obligatorios!");
      return;
    }

    // ✅ Si sube nueva imagen, la subimos; si no, usamos la actual
    let imagenFinal = imagenActual; // ya la guardas global
    if (file) {
      const subida = await saveFiles(file);
      imagenFinal = subida.img;
    }

    const payload = {
      id_evento: id_eventos,
      titulo,
      descripcion,
      tipo,
      fecha_hora_inicio,
      fecha_hora_fin,
      imagen: imagenFinal
    };

    const res = await postJSON("../api/v1/fulmuv/eventos/update", payload);

    if (res.error) {
      SweetAlert("error", res.msg || "No se pudo actualizar el evento.");
      return;
    }

    SweetAlert("url_success", "Evento actualizado correctamente.", "eventos.php");
  } catch (e) {
    console.error(e);
    SweetAlert("error", "Ocurrió un error al actualizar el evento.");
  } finally {
    setBtnLoading("#btnEditarEvento", false);
  }
}

// ========= Helpers AJAX =========
function postJSON(url, data) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url,
      method: "POST",
      data,
      success: (res) => resolve(typeof res === "string" ? JSON.parse(res) : res),
      error: (xhr) => reject(xhr)
    });
  });
}

// Sube muchos archivos y devuelve arreglo de URLs
function saveManyFiles(filesArr) {
  const arr = Array.from(filesArr || []);
  return Promise.all(arr.map(f => saveFiles(f).then(x => x.img)));
}


// Helpers
function esc(s) { return (s || '').toString().replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])); }
function linkify(s) {
  if (!s) return '';
  const urlRx = /(https?:\/\/[^\s]+)/g;
  return esc(s).replace(urlRx, url => `<a href="${url}" target="_blank" rel="noopener">${url}</a>`);
}
function fmtFecha(dt) {
  if (!dt) return '';
  // si viene "YYYY-MM-DD HH:MM:SS"
  const d = dt.includes('T') ? new Date(dt) : new Date(dt.replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return esc(dt);
  return d.toLocaleString();
}

function viewEvento(id_evento) {
  // preparar contenedor
  $("#alert").empty().append(`
    <button id="btnViewModal" class="d-none" data-bs-toggle="modal" data-bs-target="#modalViewEvento"></button>

    <div class="modal fade" id="modalViewEvento" tabindex="-1" aria-labelledby="modalViewEventoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0">
          <div class="modal-header bg-light">
            <h5 class="modal-title" id="modalViewEventoLabel">Detalle del evento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div id="viewEventoBody">
              <div class="text-center text-muted py-5">Cargando…</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
  $("#btnViewModal").trigger("click");

  // 1) Traer info del evento
  $.get(`../api/v1/fulmuv/eventos/${id_evento}`, function (resp) {
    if (resp && resp.error === false) {
      const e = resp.data || {};
      const portada = e.imagen ? `../admin/${e.imagen}` : '../img/FULMUV-NEGRO.png';

      // 2) Traer galería
      $.get(`../api/v1/fulmuv/eventos/galeria/${id_evento}`, function (gres) {
        const gal = (gres && gres.error === false && Array.isArray(gres.data)) ? gres.data : [];

        // Galería en grilla (200x200) dentro del modal
        let galMarkup = '';
        if (gal.length) {
          galMarkup = `
            <div class="mt-3">
              <h6 class="text-uppercase text-muted mb-2">Galería</h6>
              <div class="row">
                <div class="col-lg-12">
                  <div class="d-flex flex-wrap gap-3">
                    ${gal.map((g, i) => `
                      <div class="border rounded shadow-sm p-1" style="width:200px;height:200px;overflow:hidden;">
                        <img src="../admin/${esc(g.imagen)}"
                            alt="gal-${i}"
                            class="w-100 h-100"
                            style="object-fit:cover;">
                      </div>
                    `).join('')}
                  </div>
                </div>
              </div>
            </div>
          `;
        }

        // Etiquetas/enlaces
        const enlace = e.enlace ? `<a href="${esc(e.enlace)}" target="_blank" rel="noopener">${esc(e.enlace)}</a>` : '—';
        const compra = e.enlace_compra ? `<a href="${esc(e.enlace_compra)}" target="_blank" rel="noopener">${esc(e.enlace_compra)}</a>` : '—';

        // Subtipos (si guardaste JSON de ids)
        let subtipoTxt = '—';
        try {
          const arr = JSON.parse(e.subtipo_evento || '[]');
          subtipoTxt = Array.isArray(arr) && arr.length ? arr.join(', ') : '—';
        } catch (_) { }

        // Render del modal
        $("#viewEventoBody").html(`
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="card shadow-sm h-100">
                <img src="${portada}" class="card-img-top" style="max-height:320px;object-fit:contain;" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">
                <div class="card-body">
                  <h5 class="card-title mb-2">${esc(e.titulo || '—')}</h5>
                  <div class="mb-2"><span class="badge bg-primary-subtle text-primary me-1">Tipo</span> ${esc(e.tipo || e.tipo_evento || '—')}</div>
                  <div class="mb-2"><span class="badge bg-secondary-subtle text-secondary me-1">Subtipo(s)</span> ${esc(subtipoTxt)}</div>
                  <div class="mb-2"><span class="badge bg-info-subtle text-info me-1">Modalidad</span> ${esc(e.modalidad || '—')}</div>
                  <div class="mb-2"><span class="badge bg-success-subtle text-success me-1">Entrada</span> ${esc(e.tipo_entrada || '—')}</div>
                </div>
              </div>
            </div>

            <div class="col-lg-7">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <h6 class="text-uppercase text-muted mb-3">Información</h6>

                  <div class="mb-2"><strong>Organizador:</strong> ${esc(e.organizador || '—')}</div>
                  <div class="mb-2"><strong>Inicio:</strong> ${fmtFecha(e.fecha_hora_inicio)}</div>
                  <div class="mb-2"><strong>Fin:</strong> ${fmtFecha(e.fecha_hora_fin)}</div>

                  <div class="mb-2"><strong>Dirección:</strong> ${esc(e.direccion || '—')}</div>
                  <div class="mb-2"><strong>Provincia:</strong> ${esc(e.provincia || '—')}</div>
                  <div class="mb-3"><strong>Cantón:</strong> ${esc(e.canton || '—')}</div>

                  <div class="mb-3"><strong>Descripción:</strong><br>${linkify(e.descripcion || '')}</div>

                  <div class="mb-2"><strong>Enlace web/redes:</strong> ${enlace}</div>
                  <div class="mb-3"><strong>Compra/registro:</strong> ${compra}</div>

                  <h6 class="text-uppercase text-muted mt-4 mb-2">Contacto</h6>
                  <div class="mb-1"><strong>Nombre:</strong> ${esc(e.nombre_contacto || '—')}</div>
                  <div class="mb-1"><strong>Teléfonos:</strong> ${esc(e.telefono || '—')}</div>
                  <div class="mb-1"><strong>Correo:</strong> ${e.correo ? `<a href="mailto:${esc(e.correo)}">${esc(e.correo)}</a>` : '—'}</div>
                  ${galMarkup}
                </div>
              </div>

            </div>
          </div>
        `);
      }, 'json');

    } else {
      $("#viewEventoBody").html(`<div class="text-danger">No se pudo cargar la información del evento.</div>`);
    }
  }, 'json');
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

// ============================
// ✅ BOTÓN LOADING + BLOQUEO
// ============================
function setBtnLoading(btnSelector, isLoading, textIdle = "Guardar") {
  const $btn = $(btnSelector);
  if (!$btn.length) return;

  if (isLoading) {
    $btn.data("old-html", $btn.html());
    $btn.prop("disabled", true);
    $btn.html(`
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      Guardando...
    `);
  } else {
    const old = $btn.data("old-html");
    $btn.prop("disabled", false);
    $btn.html(old || textIdle);
  }
}
