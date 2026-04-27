let empresas = [];
var id_usuario = $("#id_principal").val();
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

let map, marker, geocoder, placesService;
let latitud = null, longitud = null; // coordenadas de trabajo (empresa / selección)
window.__mapReady = false;           // si ya se construyó el mapa del modal

let autocompleteService;

// ✅ Coordenadas por defecto (al abrir "Crear")
const DEFAULT_LAT = -2.198162936534796;
const DEFAULT_LNG = -79.88539898573629;

let mapEntrega, markerEntrega, geocoderEntrega, autocompleteEntrega;
let mapsLoaded = false;
let sucursalesTable = null;

function getSucursalInitials(nombre) {
  const safeNombre = (nombre || '').trim();
  if (!safeNombre) return 'S';
  return safeNombre
    .split(/\s+/)
    .slice(0, 2)
    .map(parte => parte.charAt(0).toUpperCase())
    .join('');
}

function renderSucursalesRows(sucursales) {
  if (!sucursales || sucursales.length === 0) {
    $("#lista_sucursales").html(`
      <tr>
        <td colspan="5" class="sucursales-empty-state">No hay sucursales registradas para este contexto.</td>
      </tr>
    `);
    return;
  }

  $("#lista_sucursales").html("");

  sucursales.forEach(sucursal => {
    const initials = getSucursalInitials(sucursal.nombre);
    const empresa = sucursal.empresa && sucursal.empresa.trim() !== '' ? sucursal.empresa : 'Sin empresa';
    const direccion = sucursal.direccion && sucursal.direccion.trim() !== '' ? sucursal.direccion : 'Sin dirección registrada';

    $("#lista_sucursales").append(`
      <tr>
        <td class="align-middle">
          <div class="sucursales-cell-name">
            <div class="sucursales-avatar">${initials}</div>
            <div>
              <div class="sucursales-name-title">${sucursal.nombre}</div>
              <div class="sucursales-name-meta">Sucursal #${sucursal.id_sucursal}</div>
            </div>
          </div>
        </td>
        <td class="align-middle">
          <div class="sucursales-address">${direccion}</div>
        </td>
        <td class="align-middle">
          <span class="sucursales-pill">
            <span class="fas fa-building"></span>
            ${empresa}
          </span>
        </td>
        <td class="align-middle text-700">${sucursal.created_at}</td>
        <td class="align-middle text-end">
          <div class="sucursales-actions">
            <button class="btn btn-tertiary border-300 btn-sm text-600 shadow-none sucursales-action-btn"
              type="button"
              onclick="editSucursal(${sucursal.id_sucursal})"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Editar sucursal">
              <span class="fas fa-edit"></span>
            </button>
            <button class="btn btn-tertiary border-300 btn-sm text-danger shadow-none sucursales-action-btn"
              type="button"
              onclick="remove(${sucursal.id_sucursal},'sucursales')"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Eliminar sucursal">
              <span class="fas fa-trash-alt"></span>
            </button>
          </div>
        </td>
      </tr>
    `);
  });
}

function initSucursalesDataTable() {
  if ($.fn.DataTable.isDataTable('#my_table')) {
    $('#my_table').DataTable().destroy();
  }

  sucursalesTable = $("#my_table").DataTable({
    searching: true,
    responsive: false,
    autoWidth: false,
    pageLength: 10,
    lengthChange: false,
    info: true,
    order: [[3, 'desc']],
    columnDefs: [
      { orderable: false, targets: 4 },
      { width: '26%', targets: 0 },
      { width: '30%', targets: 1 },
      { width: '18%', targets: 2 },
      { width: '14%', targets: 4 }
    ],
    language: {
      search: "",
      searchPlaceholder: "Buscar sucursal, dirección o empresa",
      info: "Mostrando _START_ a _END_ de _TOTAL_ sucursales",
      infoEmpty: "Mostrando 0 a 0 de 0 sucursales",
      zeroRecords: "No se encontraron sucursales con ese criterio",
      emptyTable: "No hay sucursales disponibles",
      paginate: {
        next: "<span class=\"fas fa-chevron-right\"></span>",
        previous: "<span class=\"fas fa-chevron-left\"></span>"
      }
    },
    dom: "<'row align-items-center g-3 mb-3'<'col-md-6'f><'col-md-6 text-md-end'>>" +
      "<'table-responsive scrollbar'tr>" +
      "<'row align-items-center g-3 pt-3'<'col-md-6'i><'col-md-6 d-flex justify-content-md-end'p>>",
    drawCallback: function () {
      $('[data-bs-toggle="tooltip"]').tooltip();
    }
  });
}

function sanitizeUsernameValue(value) {
  return (value || '').replace(/\s+/g, '');
}

function handleUsernameInput(input) {
  input.value = sanitizeUsernameValue(input.value);
}

// Este callback ya lo tienes en el script: &callback=onMapsReady
window.onMapsReady = function () {
  mapsLoaded = true;
};


$(document).ready(function () {

  $.post('../api/v1/fulmuv/sucursales/', {
    id_principal: id_usuario,
    id_empresa: $("#id_empresa").val()
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      renderSucursalesRows(returned.data);
      initSucursalesDataTable();
    }
  });

  $.get('../api/v1/fulmuv/empresas/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      empresas = returned.data;
    }
  });




});
function initMapaEntrega(lat = DEFAULT_LAT, lng = DEFAULT_LNG) {
  if (!mapsLoaded || !(window.google && google.maps)) return;

  const elMap = document.getElementById("mapaEntrega");
  if (!elMap) return;

  const center = { lat, lng };

  // Si ya existe el mapa, solo recentra y actualiza marker
  if (mapEntrega && markerEntrega) {
    mapEntrega.setCenter(center);
    markerEntrega.setPosition(center);
    google.maps.event.trigger(mapEntrega, "resize");
    latitud = lat;
    longitud = lng;
    $("#direccion_mapa").val(`${lat},${lng}`);
    return;
  }

  geocoderEntrega = new google.maps.Geocoder();

  mapEntrega = new google.maps.Map(elMap, {
    center,
    zoom: 16,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false
  });

  markerEntrega = new google.maps.Marker({
    map: mapEntrega,
    position: center,
    draggable: true
  });

  // ✅ set inicial coords
  latitud = lat;
  longitud = lng;
  $("#direccion_mapa").val(`${lat},${lng}`);

  // ✅ al mover marker => actualizar lat/lng + dirección en input
  markerEntrega.addListener("dragend", () => {
    const pos = markerEntrega.getPosition();
    latitud = pos.lat();
    longitud = pos.lng();
    $("#direccion_mapa").val(`${latitud},${longitud}`);
    reverseGeocodeEntrega(latitud, longitud);
  });

  // ✅ click en mapa => mover marker + actualizar lat/lng + dirección
  mapEntrega.addListener("click", (e) => {
    if (!e?.latLng) return;
    markerEntrega.setPosition(e.latLng);
    latitud = e.latLng.lat();
    longitud = e.latLng.lng();
    $("#direccion_mapa").val(`${latitud},${longitud}`);
    reverseGeocodeEntrega(latitud, longitud);
  });

  // ✅ Autocomplete para el input buscarDireccion
  const input = document.getElementById("buscarDireccion");
  if (input) {
    autocompleteEntrega = new google.maps.places.Autocomplete(input, {
      fields: ["formatted_address", "geometry", "name"],
    });

    autocompleteEntrega.addListener("place_changed", () => {
      const place = autocompleteEntrega.getPlace();
      if (!place.geometry || !place.geometry.location) return;

      const pos = place.geometry.location;

      mapEntrega.setCenter(pos);
      mapEntrega.setZoom(18);
      markerEntrega.setPosition(pos);

      latitud = pos.lat();
      longitud = pos.lng();

      $("#direccion_mapa").val(place.formatted_address || `${latitud},${longitud}`);
      $("#calle_principal").val(place.formatted_address || `${latitud},${longitud}`);
      input.value = place.formatted_address || place.name || input.value;
    });
  }

  // ✅ opcional: cargar dirección inicial
  reverseGeocodeEntrega(lat, lng);
}

// ✅ reverse geocode (coords => dirección en input)
function reverseGeocodeEntrega(lat, lng) {
  if (!geocoderEntrega) geocoderEntrega = new google.maps.Geocoder();

  geocoderEntrega.geocode(
    { location: { lat: parseFloat(lat), lng: parseFloat(lng) } },
    (results, status) => {
      if (status === "OK" && results && results[0]) {
        const addr = results[0].formatted_address;
        $("#direccion_mapa").val(addr);
        $("#calle_principal").val(addr);
        const input = document.getElementById("buscarDireccion");
        if (input) input.value = addr;
      }
    }
  );
}


function addSucursal() {
  //verifica si tiene membresia fulmuv

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
              <h4 class="mb-1" id="staticBackdropLabel">Crear sucursal</h4>
            </div>
            <div class="p-2">
              <div class="row g-2">
                <div class="col-md-12 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Nombre</label>
                  <input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()" />
                </div>
                <div class="col-md-12 mb-1">
                  <label class="form-label" for="username">Usuarios</label>
                  <input class="form-control" id="username" type="text" placeholder="usuario_sucursal" oninput="handleUsernameInput(this)" autocomplete="off" />
                  <small class="text-muted">Este dato se usará como usuario de acceso y no permite espacios.</small>
                </div>
                <div class="col-md-6 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Provincia</label>
                  <select class="form-control" id="provincia" onchange="cargarCantones(this.value)">
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
                <div class="col-md-6 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Cantón</label>
                  <select class="form-control" type="text" id="canton">
                    <option value="">Seleccione cantón</option>
                  </select>
                </div>
                <div class="col-md-6 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Teléfono</label>
                  <input class="form-control" id="telefono_contacto" type="number" placeholder="teléfono" />
                </div>
                <div class="col-md-6 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Whatsapp</label>
                  <input class="form-control" id="whatsapp_contacto" type="number" placeholder="whatsapp" />
                </div>
                <div class="col-md-12 mb-1">
                  <label class="form-label" for="correo_electronico">Correo electrónico</label>
                  <input class="form-control" id="correo_electronico" type="email" placeholder="Correo electrónico" />
                </div>
                <div class="map-wrapper position-relative">
                    <input type="hidden" id="direccion_mapa">
                    <div id="mapaEntrega"></div>

                    <div class="map-search">
                        <div class="input-group">
                            <input id="buscarDireccion" class="form-control form-control-sm"
                                style="width: clamp(200px, 39vw, 400px); margin-top:10px; background:#fff; height:40px"
                                placeholder="Buscar dirección..." />
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Calle principal</label>
                  <input class="form-control" id="calle_principal" type="text" placeholder="calle principal" />
                </div>
                <div class="col-md-6 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Calle secundaria</label>
                  <input class="form-control" id="calle_secundaria" type="text" placeholder="calle secundaria" />
                </div>
                <div class="col-md-6 mb-1">
                  <label class="form-label" for="exampleFormControlInput1"># Bien inmueble</label>
                  <input class="form-control" id="bien_inmueble" type="text" placeholder="bien inmueble" />
                </div>
                <!--div class="col-md-12 mb-1">
                  <label class="form-label" for="exampleFormControlInput1">Empresa</label>
                  <select class="form-select" id="empresa">
                    
                  </select>
                </div-->
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


  // $("#empresa").text("");
  // empresas.forEach(empresa => {
  //   $("#empresa").append(`
  //     <option value="${empresa.id_empresa}">${empresa.nombre}</option>
  //   `);
  // });

  $("#btnModal").click();

  // ✅ Cuando se abre el modal, inicializa el mapa en las coordenadas dadas
  setTimeout(() => {
    // por si el mapa ya existía de un modal anterior
    mapEntrega = null;
    markerEntrega = null;

    // Si google maps aún no cargó, reintenta hasta que esté listo
    const tryInit = () => {
      if (mapsLoaded && window.google && google.maps) {
        initMapaEntrega(DEFAULT_LAT, DEFAULT_LNG);
      } else {
        setTimeout(tryInit, 250);
      }
    };
    tryInit();
  }, 300);

}

function obtenerDireccionDesdeCoords(lat, lng, callback = null) {
  if (!geocoder) geocoder = new google.maps.Geocoder();
  const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
  geocoder.geocode({ location: latlng }, (results, status) => {
    if (status === "OK" && results && results[0]) {
      $("#direccion_mapa").val(results[0].formatted_address);
      $("#calle_principal").val(results[0].formatted_address);
      const input = document.getElementById("buscarDireccion");
      if (input) input.value = results[0].formatted_address;
    }
    if (typeof callback === "function") callback();
  });
}


function saveSucursal() {
  var nombre = $("#nombre").val();
  var username = sanitizeUsernameValue($("#username").val());
  var provincia = $("#provincia").val();
  var canton = $("#canton").val();
  var telefono_contacto = $("#telefono_contacto").val();
  var whatsapp_contacto = $("#whatsapp_contacto").val();
  var calle_principal = $("#calle_principal").val();
  var calle_secundaria = $("#calle_secundaria").val();
  var bien_inmueble = $("#bien_inmueble").val();
  var empresa = $("#id_empresa").val();
  var correo = $("#correo_electronico").val();

  if (nombre == "" || username == "" || provincia == "" || canton == "" || telefono_contacto == "" || whatsapp_contacto == "" || calle_principal == "" || calle_secundaria == "" || bien_inmueble == "" || correo == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!")
  } else if (latitud == "" || longitud == "") {
    SweetAlert("error", "Selecciona la ubicación de tu celular")
  } else {
    $.post('../api/v1/fulmuv/sucursales/create', {
      nombre: nombre,
      username: username,
      provincia: provincia,
      canton: canton,
      telefono_contacto: telefono_contacto,
      whatsapp_contacto: whatsapp_contacto,
      calle_principal: calle_principal,
      calle_secundaria: calle_secundaria,
      bien_inmueble: bien_inmueble,
      id_empresa: empresa,
      latitud: latitud,
      longitud: longitud,
      correo: correo
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "sucursales.php")
      } else if (returned.data) {
        // Guardamos la info para el pago
        window._payloadNuevaSucursal = {
          id_empresa: empresa,
          nombre: nombre,
          username: username,
          provincia: provincia,
          canton: canton,
          telefono_contacto: telefono_contacto,
          whatsapp_contacto: whatsapp_contacto,
          calle_principal: calle_principal,
          calle_secundaria: calle_secundaria,
          bien_inmueble: bien_inmueble,
          correo: correo
        };
        renderPagoNuevaSucursal(returned.msg, returned.data);
        return;
      } else {
        //SweetAlert("error", returned.msg)
        swal({
          title: "Error",
          text: returned.msg || "Ocurrió un error.",
          type: "error",                 // en v1 es "type"
          showCancelButton: true,
          confirmButtonText: "Upgrade membresía",
          cancelButtonText: "Cancelar",
          closeOnConfirm: true,
          closeOnCancel: true
        }, function (isConfirm) {
          if (isConfirm) {
            window.location.href = "upgrade_membresia.php?id_empresa=" + $("#id_empresa").val(); // 👈 cambia la ruta
            // o si quieres nueva pestaña:
            // window.open("otra_pagina.php", "_blank");
          }
        });
      }
    });
  }
}

function editSucursal(id_sucursal) {
  $.get('../api/v1/fulmuv/sucursales/' + id_sucursal, {}, function (returnedData) {
    const returned = typeof returnedData === 'string' ? JSON.parse(returnedData) : returnedData;

    if (returned.error === false) {
      const s = returned.data;

      $("#alert").html(`
        <button id="btnModal" class="btn btn-primary" type="button"
          data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">
          modal
        </button>

        <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static"
          tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg mt-6" role="document">
            <div class="modal-content border-0">
              <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base"
                  data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body p-0">
                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                  <h4 class="mb-1" id="staticBackdropLabel">Actualizar sucursal</h4>
                </div>

                <div class="p-2">
                  <div class="row g-2">

                    <input type="hidden" id="id_sucursal_edit" value="${id_sucursal}">

                    <div class="col-md-12 mb-1">
                      <label class="form-label">Nombre</label>
                      <input class="form-control" id="nombre" type="text"
                        placeholder="nombre" oninput="this.value=this.value.toUpperCase()" />
                    </div>

                    <div class="col-md-12 mb-1">
                      <label class="form-label">Usuarios</label>
                      <input class="form-control" id="username" type="text"
                        placeholder="usuario_sucursal" oninput="handleUsernameInput(this)" autocomplete="off" />
                      <small class="text-muted">Este dato se usará como usuario de acceso y no permite espacios.</small>
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label">Provincia</label>
                      <select class="form-control" id="provincia" onchange="cargarCantones(this.value)">
                        <option value="">Seleccione provincia</option>
                        ${Object.keys(cantones).map(p => `<option value="${p}">${p}</option>`).join('')}
                      </select>
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label">Cantón</label>
                      <select class="form-control" id="canton">
                        <option value="">Seleccione cantón</option>
                      </select>
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label">Teléfono</label>
                      <input class="form-control" id="telefono_contacto" type="number" placeholder="teléfono" />
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label">Whatsapp</label>
                      <input class="form-control" id="whatsapp_contacto" type="number" placeholder="whatsapp" />
                    </div>

                    <div class="col-md-12 mb-1">
                      <label class="form-label">Correo electrónico</label>
                      <input class="form-control" id="correo_electronico" type="email" placeholder="Correo electrónico" />
                    </div>

                    <div class="map-wrapper position-relative">
                      <input type="hidden" id="direccion_mapa">
                      <div id="mapaEntrega"></div>

                      <div class="map-search">
                        <div class="input-group">
                          <input id="buscarDireccion" class="form-control form-control-sm"
                            style="width: clamp(200px, 39vw, 400px); margin-top:10px; background:#fff; height:40px"
                            placeholder="Buscar dirección..." />
                        </div>
                      </div>
                    </div>

                    <div class="col-md-12 mb-1">
                      <label class="form-label">Calle principal</label>
                      <input class="form-control" id="calle_principal" type="text" placeholder="calle principal" />
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label">Calle secundaria</label>
                      <input class="form-control" id="calle_secundaria" type="text" placeholder="calle secundaria" />
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label"># Bien inmueble</label>
                      <input class="form-control" id="bien_inmueble" type="text" placeholder="bien inmueble" />
                    </div>

                    <div class="col-12">
                      <button onclick="updateSucursalFull()" class="btn btn-primary w-100" type="button">
                        Actualizar
                      </button>
                    </div>

                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      `);

      $("#btnModal").click();

      // ✅ llenar inputs luego de que el modal esté en DOM
      setTimeout(() => {
        fillSucursalForm(s);
      }, 200);

    } else {
      SweetAlert("error", returned.msg || "No se pudo cargar la sucursal.");
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
      direccion: direccion
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "sucursales.php")
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
        SweetAlert("url_success", returned.msg, "sucursales.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}

function formatMoney(n, currency = "USD") {
  const v = Number(n || 0);
  try { return new Intl.NumberFormat('es-EC', { style: 'currency', currency }).format(v); }
  catch { return `$${v.toFixed(2)}`; }
}

function renderPagoNuevaSucursal(msg, data) {
  // Esperado del backend (flexible):
  // data = {
  //   precio: 4.99,
  //   moneda: "USD",
  //   opciones: [
  //     { id: "tarjeta", titulo: "Tarjeta (Paymentez)", detalle: "Crédito/Débito", precio: 4.99 },
  //     { id: "transferencia", titulo: "Transferencia", detalle: "Adjuntar comprobante", precio: 4.99 }
  //   ]
  // }

  const label = document.getElementById('staticBackdropLabel');
  if (label) label.textContent = 'Pagar nueva sucursal';

  // Construye tarjetas
  const opciones = Array.isArray(data.cards) && data.cards.length ? data.cards : [{
    id: 'tarjeta',
    titulo: 'Tarjeta (Paymentez)',
    detalle: 'Crédito/Débito',
    precio: data.precio || 0
  }];

  let cards = '';
  opciones.forEach((op, idx) => {
    cards += `
      <!--div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border selectable-card" data-id="${op.id}" data-precio="${op.precio}">
          <div class="card-body">
            <h5 class="card-title mb-1">${op.titulo}</h5>
            <p class="text-muted small mb-2">${op.detalle || ''}</p>
            <div class="fw-bold fs-5">${formatMoney(op.precio, data.moneda || 'USD')}</div>
          </div>
        </div>
      </div-->
      <div class="col-sm-6 col-lg-6">
        <div class="card position-relative rounded-4 border selectable-card" data-id="${op.token}" data-precio="4">
          <div class="bg-holder bg-card rounded-4" style="background-image:url(../theme/assets/img/icons/spot-illustrations/corner-2.png);">
          </div>
          <!--/.bg-holder-->

          <div class="card-body p-3 pt-5 pt-xxl-4"><img class="mb-3" src="../theme/assets/img/icons/chip.png" alt="" width="30">
            <h6 class="text-primary font-base lh-1 mb-1">**** **** **** ${op.number}</h6>
            <h6 class="fs--2 fw-semi-bold text-facebook mb-3">${op.expiry_month}/${op.expiry_year}</h6>
            <h6 class="mb-0 text-facebook">${op.holder_name}</h6><img class="position-absolute end-0 bottom-0 mb-2 me-2" src="../theme/assets/img/icons/master-card.png" alt="" width="70">
          </div>
        </div>
      </div>
    `;
  });

  const html = `
    <div class="p-4">
      <div class="alert alert-warning" role="alert">
        ${msg || 'Ya usaste tu sucursal gratuita. Debes realizar el pago para crear una nueva.'}
      </div>

      <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex align-items-center gap-3">
          <div>
            <div class="fw-semibold">Nueva sucursal</div>
            <div class="small text-muted" id="resumenSucursal">
              ${(window._payloadNuevaSucursal?.nombre || '')} — ${(window._payloadNuevaSucursal?.calle_principal || '')}
            </div>
          </div>
        </div>
      </div>

      <div class="mb-2 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Selecciona un método</h5>
        <span class="small text-muted">Total: <strong>${formatMoney(4, 'USD')}</strong></span>
      </div>

      <div class="row g-3" id="cardsPagoOpciones">
        ${cards}
      </div>

      <div class="d-flex justify-content-between align-items-center mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" onclick="volverFormularioSucursal()">Volver</button>
          <button type="button" class="btn btn-primary" id="btnPagarAhora" disabled>Pagar ahora</button>
        </div>
      </div>
    </div>
  `;

  // Sustituye el contenido del modal (solo la parte interna)
  const modalBody = document.querySelector('#staticBackdrop .modal-body');
  if (modalBody) {
    // Mantener el header "Crear sucursal" vs "Pagar nueva sucursal": ya cambiamos el label arriba
    modalBody.innerHTML = `
      <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
        <h4 class="mb-1" id="staticBackdropLabel">${label ? label.textContent : 'Pagar nueva sucursal'}</h4>
      </div>
      ${html}
    `;
  }

  // Interacciones de selección
  let seleccion = null;
  document.querySelectorAll('.selectable-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.selectable-card').forEach(c => c.classList.remove('border-primary', 'shadow'));
      card.classList.add('border-primary', 'shadow');
      seleccion = {
        id: card.getAttribute('data-id'),
        precio: parseFloat(card.getAttribute('data-precio') || data.precio || 0),
        moneda: data.moneda || 'USD'
      };
      document.getElementById('btnPagarAhora').disabled = false;
    });
  });

  // Botón pagar
  document.getElementById('btnPagarAhora').addEventListener('click', () => {
    if (!seleccion) return;
    iniciarPagoNuevaSucursal(seleccion);
  });
}

function volverFormularioSucursal() {
  // Reconstruye la UI original del formulario de crear sucursal
  // Simplemente re-llama addSucursal() y rellena con los valores previos
  const prev = window._payloadNuevaSucursal || {};
  addSucursal();
  setTimeout(() => {
    if (prev.nombre) $('#nombre').val(prev.nombre);
    if (prev.username) $('#username').val(prev.username);
    if (prev.provincia) $('#provincia').val(prev.provincia);
    if (prev.canton) $('#canton').val(prev.canton);
    if (prev.telefono_contacto) $('#telefono_contacto').val(prev.telefono_contacto);
    if (prev.whatsapp_contacto) $('#whatsapp_contacto').val(prev.whatsapp_contacto);
    if (prev.calle_principal) $('#calle_principal').val(prev.calle_principal);
    if (prev.calle_secundaria) $('#calle_secundaria').val(prev.calle_secundaria);
    if (prev.bien_inmueble) $('#bien_inmueble').val(prev.bien_inmueble);
  }, 50);
}

function iniciarPagoNuevaSucursal(seleccion) {
  const payload = {
    ...window._payloadNuevaSucursal,
    token: seleccion.id,
    monto: seleccion.precio,
  };

  // Puedes mostrar un loader simple
  $('#btnPagarAhora').prop('disabled', true).text('Procesando...');

  $.post('../api/v1/fulmuv/sucursales/init_pago', payload, function (res) {
    const r = typeof res === 'string' ? JSON.parse(res) : res;

    if (r.error === false && r.payment_url) {
      // Redirige al flujo de pago (Paymentez/checkout/lo que uses)
      window.location.href = r.payment_url;
      return;
    }

    // Si prefieres pagar en modal/SDK, aquí abres el widget en vez de redirigir
    // ...

    // Fallo controlado
    $('#btnPagarAhora').prop('disabled', false).text('Pagar ahora');
    SweetAlert("error", r.msg || "No se pudo iniciar el pago. Inténtalo nuevamente.");
  })
    .fail(() => {
      $('#btnPagarAhora').prop('disabled', false).text('Pagar ahora');
      SweetAlert("error", "Error al iniciar el pago.");
    });
}


function cargarCantones(provincia) {
  const cantonSelect = document.getElementById("canton");
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

function fillSucursalForm(s) {
  $("#nombre").val(s.nombre || '');
  $("#username").val(s.nombre_usuario || '');
  $("#telefono_contacto").val(s.telefono_contacto || '');
  $("#whatsapp_contacto").val(s.whatsapp_contacto || '');
  $("#correo_electronico").val(s.correo || '');

  $("#calle_principal").val(s.calle_principal || '');
  $("#calle_secundaria").val(s.calle_secundaria || '');
  $("#bien_inmueble").val(s.bien_inmueble || '');

  // Provincia + cantón
  $("#provincia").val(s.provincia || '');
  cargarCantones(s.provincia || '');
  setTimeout(() => {
    $("#canton").val(s.canton || '');
  }, 50);

  // ✅ coords
  latitud = parseFloat(s.latitud || DEFAULT_LAT);
  longitud = parseFloat(s.longitud || DEFAULT_LNG);

  // ✅ levanta mapa en coords de la sucursal
  mapEntrega = null;
  markerEntrega = null;

  const tryInit = () => {
    if (mapsLoaded && window.google && google.maps) {
      initMapaEntrega(latitud, longitud);

      // si tu API trae direccion, úsala
      if (s.direccion) {
        $("#direccion_mapa").val(s.direccion);
        const input = document.getElementById("buscarDireccion");
        if (input) input.value = s.direccion;
      } else {
        // si no trae dirección, la calculas con reverse geocode
        reverseGeocodeEntrega(latitud, longitud);
      }

    } else {
      setTimeout(tryInit, 250);
    }
  };
  tryInit();
}


function updateSucursalFull() {
  const id_sucursal = $("#id_sucursal_edit").val();

  const payload = {
    id_sucursal: id_sucursal,
    nombre: $("#nombre").val(),
    username: sanitizeUsernameValue($("#username").val()),
    provincia: $("#provincia").val(),
    canton: $("#canton").val(),
    telefono_contacto: $("#telefono_contacto").val(),
    whatsapp_contacto: $("#whatsapp_contacto").val(),
    correo: $("#correo_electronico").val(),
    calle_principal: $("#calle_principal").val(),
    calle_secundaria: $("#calle_secundaria").val(),
    bien_inmueble: $("#bien_inmueble").val(),
    latitud: latitud,
    longitud: longitud
  };

  // validaciones básicas
  for (const k of ["nombre","username","provincia","canton","telefono_contacto","whatsapp_contacto","correo","calle_principal","calle_secundaria","bien_inmueble"]) {
    if (!payload[k]) return SweetAlert("error", "Todos los campos son obligatorios.");
  }
  if (!payload.latitud || !payload.longitud) return SweetAlert("error", "Selecciona la ubicación en el mapa.");

  $.post('../api/v1/fulmuv/sucursales/update_full', payload, function (res) {
    const r = typeof res === 'string' ? JSON.parse(res) : res;

    if (r.error === false) {
      SweetAlert("url_success", r.msg || "Sucursal actualizada con éxito.", "sucursales.php");
    } else {
      SweetAlert("error", r.msg || "No se pudo actualizar la sucursal.");
    }
  }).fail(() => {
    SweetAlert("error", "Error de red al actualizar.");
  });
}
