let membresiasData = [];
let agentes = [];
let id_membresia_seleccionada = null;
let costo_seleccionado = null;
let id_empresa_devuelto = null;
let id_usuario_devuelto = null;
let username_guardado = null;
let groupedMembresias = {};
let valor_pagado = 0;

let agenteAplicado = null;     // objeto del agente encontrado
let codigoAplicado = null;     // string del código
let promoConfig = null;        // ya lo tienes


let map, marker, geocoder, latitud = "",
  longitud = "";
let autocompleteService, placesService;

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

let tipo_pago = 'corriente';  // 'corriente' | 'sin_interes' | 'con_interes'
let meses_pago = 0;

// Meses disponibles por tipo
const MESES_POR_TIPO = {
  sin_interes: [{ v: 3, t: '3 meses' }],
  con_interes: [{ v: 6, t: '6 meses' }, { v: 9, t: '9 meses' }]
};

const __TIPO_PAGO_MAP = {
  corriente: 0,
  sin_interes: 3,
  con_interes: 2

};

function __mapTipoPagoCode(tipo) {
  // fallback a corriente (0) si viene undefined o extraño
  return __TIPO_PAGO_MAP[tipo] ?? 0;
}

function __normalizarMeses(tipo, meses) {
  // Si es corriente o no hay meses, envía vacío
  if (tipo === 'corriente' || !meses || Number(meses) === 0) return "";
  return Number(meses);
}

$(document).ready(function () {
  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      membresiasData = returned.data;
      // renderAllTabs(); // Llenar todos los tabs al cargar
      renderMembresiasSelect(); // Render inicial con selects por card
    }
  });
  $.get('../api/v1/fulmuv/agentes/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      agentes = returned.data;
    }
  });
});

// Cambiar etiqueta del botón según estado del collapse (Bootstrap 5)
$(document).on('shown.bs.collapse hidden.bs.collapse', '.collapse', function () {
  const id = this.id;
  const $btn = $(`[data-bs-target="#${id}"]`);
  $btn.text($(this).hasClass('show') ? 'Leer menos' : 'Leer más');
});

function splitItems(itemsHtml, visibleCount = 4) {
  const lis = itemsHtml.match(/<li[\s\S]*?<\/li>/g) || [];
  return {
    preview: lis.slice(0, visibleCount).join(''),
    rest: lis.slice(visibleCount).join(''),
    hasMore: lis.length > visibleCount
  };
}

function renderMembresiasSelect() {
  groupByNombre();

  const cont = $('#contenedor-membresias');
  cont.empty();

  Object.keys(groupedMembresias).forEach(key => {
    const planes = groupedMembresias[key];
    const nombre = planes[0].nombre;
    const nombreKey = nombre.replace(/\s+/g, '').toLowerCase();

    const defaultPlan = planes.find(p => String(p.dias_permitidos) === "365") || planes[0];

    const selectId = `select_${nombreKey}`;
    const precioId = `precio_${nombreKey}`;
    const periodoId = `periodo_${nombreKey}`;
    const btnId = `btn_${nombreKey}`;

    const options = planes.map(p => {
      const dias = String(p.dias_permitidos);
      const sel = (p.id_membresia == defaultPlan.id_membresia) ? 'selected' : '';
      return `<option value="${dias}" ${sel}>${diasToText(dias)}</option>`;
    }).join('');

    const items = buildItems(key);
    const contentId = `collapse_${nombreKey}`;
    const toggleId = `toggle_${nombreKey}`;

    const isFulMuv = nombre.trim().toLowerCase() === 'fulmuv';
    const sucursalCheckId = `suc_${nombreKey}`;
    const sucursalWrapId = `suc_wrap_${nombreKey}`;
    const badgeContainerId = `badge_${nombreKey}`;
    const badgeInicial = badgeFor(nombre, defaultPlan.dias_permitidos);

    const parts = splitItems(items, 4);

    const cardHtml = `
      <div class="col-md-4 col-sm-12 mb-3 d-flex">
        <div class="border rounded-3 overflow-hidden flex-fill d-flex flex-column h-100">
          <div class="d-flex flex-between-center p-4">
            <div class="text-start">
              <h4 class="fw-light text-primary fs-5 mb-0">${nombre}</h4>

              <div class="align-items-center gap-2 mt-2">
                <select id="${selectId}" data-group="${key}" class="form-select form-select-sm w-auto">
                  ${options}
                </select>

                ${isFulMuv ? `
                  <div id="${sucursalWrapId}" class="form-check my-2" style="${String(defaultPlan.dias_permitidos) === '365' ? '' : 'display:none;'}">
                    <input class="form-check-input" type="checkbox" id="${sucursalCheckId}">
                    <label class="form-check-label" for="${sucursalCheckId}">
                      <strong>Tengo sucursales</strong> <span class="fas fa-shopping-bag text-success"></span>
                    </label>
                  </div>
                ` : ``}
              </div>

              <h2 class="fw-light text-primary mt-2">
                <sup class="fs-8">&dollar;</sup>
                <span id="${precioId}" class="fs-6">${defaultPlan.costo}</span>
                <span id="${periodoId}" class="fs-9 mt-1">/ ${diasToText(defaultPlan.dias_permitidos)}</span>
              </h2>

              <div class="text-start" id="${badgeContainerId}">
                ${badgeInicial}
              </div>
            </div>
            <div class="pe-3"></div>
          </div>

          <div class="p-4 pt-0 d-flex flex-column flex-grow-1 bg-body-tertiary">
            <div class="flex-grow-1">
              <ul class="list-unstyled mb-0">
                ${parts.preview}
              </ul>
              ${parts.hasMore ? `
                <div id="${contentId}" class="collapse mt-2">
                  <ul class="list-unstyled mb-0">
                    ${parts.rest}
                  </ul>
                </div>
                <div class="text-center mt-2">
                  <button
                    id="${toggleId}"
                    class="btn btn-link p-0 text-decoration-none"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#${contentId}"
                    aria-expanded="false"
                    aria-controls="${contentId}">
                    Leer más
                  </button>
                </div>
              ` : ''}
            </div>

            <div class="mt-auto pt-3">
              <button id="${btnId}" class="btn btn-outline-primary w-100" type="button">
                Comprar
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    cont.append(cardHtml);

    // 👉 Guardar precio base del plan por defecto
    $(`#${precioId}`).data('base', Number(defaultPlan.costo));

    // Botón comprar con plan por defecto
    /* $(`#${btnId}`).off('click').on('click', function () {
      saveMembresia(defaultPlan.id_membresia);
    }); */
    $(`#${btnId}`).off('click').on('click', function () {
      saveMembresia(defaultPlan.id_membresia, precioId);
    });

    /*if (isFulMuv) {
      $(document).off('change', `#${sucursalCheckId}`).on('change', `#${sucursalCheckId}`, function () {

        const checked = $(this).is(':checked');
        const diasSel = String($(`#${selectId}`).val());

        // base guardada
        const base = $(`#${precioId}`).data('base');

        if (diasSel === '365') {
          $(`#${precioId}`).text(checked ? '317' : base);
        } else if (diasSel === '180') {
          $(`#${precioId}`).text(checked ? '177' : base);
        } else if (diasSel === '30') {
          $(`#${precioId}`).text(checked ? '35' : base);
        }

        // ✅ actualizar total pago siempre que cambie el precio
        actualizarTotalPago(precioId);
      });
    }

    // Cambio de plan
    $(document).off('change', `#${selectId}`).on('change', `#${selectId}`, function () {
      const diasSel = String($(this).val());
      const grupoKey = $(this).data('group');
      const plan = (groupedMembresias[grupoKey] || []).find(p => String(p.dias_permitidos) === diasSel);
      if (!plan) return;

      // Actualiza base y textos
      $(`#${precioId}`).data('base', Number(plan.costo));      // 👈 actualizar precio base
      $(`#${precioId}`).text(plan.costo);
      $(`#${periodoId}`).text(`/ ${diasToText(diasSel)}`);
      $(`#${badgeContainerId}`).html(badgeFor(nombre, diasSel));

      if (isFulMuv) {
        if (diasSel === '365') {
          const checked = $(`#${sucursalCheckId}`).is(':checked');
          if (checked) {
            $(`#${precioId}`).text('317');
          } else {
            $(`#${precioId}`).text(plan.costo);
          }
        } else if (diasSel === '180') {
          const checked = $(`#${sucursalCheckId}`).is(':checked');
          if (checked) {
            $(`#${precioId}`).text('177');
          } else {
            $(`#${precioId}`).text(plan.costo);
          }
        } else if (diasSel === '30') {
          const checked = $(`#${sucursalCheckId}`).is(':checked');
          if (checked) {
            $(`#${precioId}`).text('35');
          } else {
            $(`#${precioId}`).text(plan.costo);
          }
        }
      }

      // Actualiza acción del botón
      $(`#${btnId}`).off('click').on('click', function () {
        saveMembresia(plan.id_membresia);
      });
    });*/
    if (isFulMuv) {
      $(document).off('change', `#${sucursalCheckId}`).on('change', `#${sucursalCheckId}`, function () {
        actualizarPrecioCard({ nombreKey, nombre, selectId, precioId, sucursalCheckId });
      });
    }

    $(document).off('change', `#${selectId}`).on('change', `#${selectId}`, function () {
      const diasSel = String($(this).val());
      const grupoKey = $(this).data('group');
      const plan = (groupedMembresias[grupoKey] || []).find(p => String(p.dias_permitidos) === diasSel);
      if (!plan) return;

      // base real del plan (del backend)
      $(`#${precioId}`).data('base', Number(plan.costo));

      $(`#${periodoId}`).text(`/ ${diasToText(diasSel)}`);
      $(`#${badgeContainerId}`).html(badgeFor(nombre, diasSel));

      actualizarPrecioCard({ nombreKey, nombre, selectId, precioId, sucursalCheckId });

      /* $(`#${btnId}`).off('click').on('click', function () {
        saveMembresia(plan.id_membresia);
      }); */
      $(`#${btnId}`).off('click').on('click', function () {
        saveMembresia(plan.id_membresia, precioId);
      });
    });


  });
}

function actualizarTotalPago(precioId) {
  // Lee el precio actual que está en el span/div del precio
  const precioTxt = $(`#${precioId}`).text().trim();   // ejemplo: "317"
  const precioNum = parseFloat(precioTxt.replace(',', '.')) || 0;

  // Si tienes otros recargos/descuentos, aquí los sumas/restas.
  // Por ahora, total = precio
  console.log(precioNum.toFixed(2));
  costo_seleccionado = precioNum.toFixed(2);
  $("#totalPago").text("$" + precioNum.toFixed(2));
}

/* -------------------- NUEVO RENDER POR CARD CON SELECT -------------------- */

function diasToText(dias) {
  if (String(dias) === "30") return "mensual";
  if (String(dias) === "180") return "semestral";
  return "anual";
}

function groupByNombre() {
  groupedMembresias = {};
  (membresiasData || []).forEach(m => {
    const key = (m.nombre || '').toLowerCase();
    if (!groupedMembresias[key]) groupedMembresias[key] = [];
    groupedMembresias[key].push(m);
  });

  // Orden sugerido 30, 180, 360
  const order = ["30", "180", "365"];
  Object.keys(groupedMembresias).forEach(k => {
    groupedMembresias[k].sort(
      (a, b) => order.indexOf(String(a.dias_permitidos)) - order.indexOf(String(b.dias_permitidos))
    );
  });
}

function buildItems(nombreLower) {
  if (nombreLower.includes('onemuv')) {
    return `
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Ideal para <strong>particulares, no empresas/negocios</strong></li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publicación de <strong>"1" accesorio, repuesto, servicio, vehículo o evento.</strong></li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publica tu vehículo, evento, servicio, accesorio o repuesto que tengas en casa.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Por el equivalente de <strong>$0.11 centavos diarios, con tu plan ANUAL de OneMuv, </strong>recibe clientes potenciales en todo el país. Confirma que estés eligiendo tu plan ANUAL.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Inviertes en formar parte del futuro de las ventas vehiculares en una plataforma de <strong>especialidad.</strong></li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Este <strong>NO ES TU PLAN </strong>si ofreces más de 1 producto y/o servicio y/o vehículo y/o evento. Si es así, elige el plan <strong>FULMUV, anual.</strong></li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Hasta <strong>15 fotos </strong>en la publicación de tu producto o servicio.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Fotos, descripción, especificaciones, precio y datos de contacto.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Visibilidad nacional en plataforma. Clientes potenciales en todo el país. </li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Sin comisiones por venta. Vendes sin barreras ni límites. </li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>NO incluye </strong>este plan envíos a domicilio por parte de <strong>FULMUV.</strong></li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Ideal para ventas puntuales. (Ej: 1 auto / 1 avión / 1 perno) </li>
    `;
  }
  if (nombreLower.includes('fulmuv')) {
    return `
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Para <strong>empresas, negocios y emprendedores.</strong></li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>Digitaliza tu empresa</strong> dentro de la plataforma de especialidad vehicular del país.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Por menos del equivalente a <strong>$0.73 centavos diarios, con tu plan ANUAL de FULMUV,</strong> recibes clientes potenciales en todo el país.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Ahorra en pautas y publicidad.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>Catálogo ilimitado</strong> de productos, servicios, vehículos y eventos. Publica todo lo que ofreces al mercado.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>Envíos asegurados</strong> a nivel nacional con FULMUV. Tú lo empacas, FULMUV lo envía a tu cliente final.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>Posicionamiento prioritario</strong> en búsquedas.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>Sello de Empresa Verificada,</strong> completando tu información.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Comunicación directa con clientes. Pueden ver tus datos de contacto y gestionar su compra directamente.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publicación de vacantes laborales.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Visibilidad nacional en plataforma. Clientes potenciales en todo el país.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Vende en todo el país, no te limites más a tu localidad.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Presencia y atención 24/7.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Añade todas tus sucursales, cada una con catálogos diferentes, o el mismo, eligiendo tu plan anual <strong>con sucursales</strong>.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>Cero comisiones</strong> por venta.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publica el precio <strong>REAL</strong> de tus productos y servicios. En FULMUV acceden clientes a la realidad.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Aplica <strong>descuentos</strong> a tus productos y servicios cuando lo requieras.</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Recibe <strong> 1 año de beneficios, </strong> invirtiendo en <strong>9.2 meses</strong>.</li>
    `;
  }
  // basicmuv
  return `
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Ideal únicamente para <strong>lavadoras BÁSICAS o vulcanizadoras BÁSICAS.</strong></li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Este <strong>NO ES TU PLAN</strong> si ofreces varios tipos de servicios de lavado y/o vendes productos de limpieza y cuidado vehicular. Si es así, elige el plan <strong>FULMUV, anual.</strong></li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Por el equivalente de <strong>$0.11 centavos diarios, con tu plan ANUAL de BasicMuv,</strong> recibes clientes potenciales en todo el país.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Inviertes en formar parte del futuro de las ventas vehiculares en una plataforma de <strong>especialidad.</strong></li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Hasta <strong>15 fotos</strong> en la publicación de tu servicio.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Solo se permite la publicación de servicios correspondientes a lavadoras básicas y vulcanizadoras básicas.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Fotos, descripción, precios y datos completos.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Digitaliza tu negocio y obtén visibilidad nacional en plataforma. Clientes potenciales en todo el país.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Conexión directa con clientes.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Sin comisiones por venta. Vendes sin barreras ni límites.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publica el precio <strong>REAL</strong> de tus servicios. En FULMUV acceden clientes en busca de la realidad.</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> <strong>NO incluye</strong> este plan envíos a domicilio por parte de FULMUV.</li>
  `;
}

function obtenerCategorias() {
  var categoria_principal = $("#categoria_principal").val();
  $.get('../api/v1/fulmuv/categoriasByPrincipales/' + categoria_principal, {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      $("#categoria").text("");
      document.getElementById("categoria").multiple = true;
      returned.data.forEach(function (cate) {
        $("#categoria").append(`
          <option value="${cate.id_categoria}">${cate.nombre}</option>          
        `)
      });
      $("#categoria").select2({
        placeholder: "Seleccione categoría",
        allowClear: true,
        dropdownParent: $('#staticBackdrop'),
        dropdownPosition: 'below'
      })
    }
  });
}


/* function saveMembresia(id_membresia) {
  const membresiaSeleccionada = membresiasData.find(m => m.id_membresia == id_membresia);
  id_membresia_seleccionada = id_membresia;
  //costo_seleccionado = membresiaSeleccionada?.costo || 0;
  if (promoConfig != null) {
    $("#totalPago").text("$1")
    valor_pagado = 1;
  } else {
    $("#totalPago").text("$" + costo_seleccionado)
    valor_pagado = costo_seleccionado;
  }
  mostrarFormulario()
} */

function saveMembresia(id_membresia, precioId) {
  const membresiaSeleccionada = membresiasData.find(m => m.id_membresia == id_membresia);
  id_membresia_seleccionada = id_membresia;

  // ✅ Lee el precio actual mostrado en la card
  if (precioId) {
    const precioTxt = $(`#${precioId}`).text().trim(); // "317"
    const precioNum = parseFloat(precioTxt.replace(',', '.')) || 0;
    costo_seleccionado = precioNum.toFixed(2);
  } else {
    // Fallback por si no mandan precioId
    costo_seleccionado = (Number(membresiaSeleccionada?.costo) || 0).toFixed(2);
  }

  // Total a pagar (promo = $1)
  if (promoConfig != null) {
    $("#totalPago").text("$1");
    valor_pagado = 1;
  } else {
    $("#totalPago").text("$" + costo_seleccionado);
    valor_pagado = Number(costo_seleccionado);
  }

  mostrarFormulario();
}

function mostrarFormulario() {
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
              
                      <div class="row flex-between-center mb-2">
                          <div class="col-auto">
                              <h5>Registro</h5>
                          </div>
                          <div class="col-auto fs--1 text-600"><span class="mb-0 undefined">¿Ya tienes una cuenta?</span> <span><a href="login.php">Login</a></span></div>
                      </div>
                      <div class="p-4">
                          <div class="row g-2">
                              
                              <div class="col-md-6 mb-3"><label class="form-label">Nombre de empresa</label><input class="form-control" id="nombre" type="text" placeholder="Nombre de empresa" oninput="this.value = this.value.toUpperCase()" /></div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Nombres del titular</label>
                                <input class="form-control" id="nombre_titular" type="text" placeholder="Nombres del titular" oninput="this.value = this.value.toUpperCase()" />
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Apellidos del titular</label>
                                <input class="form-control" id="apellido_titular" type="text" placeholder="Apellidos del titular" oninput="this.value = this.value.toUpperCase()" />
                              </div>
                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Tipo de local</label>
                                  <select class="form-control" type="text" id="tipo_local">
                                      <option value="">Seleccione tipo de local</option>
                                      <option value="fisico">Físico</option>
                                      <option value="online">Online</option>
                                  </select>
                              </div>
                              <div class="col-md-12 mb-3">
                                <div class="d-flex justify-content-start align-items-center">
                                <label class="form-label">Dirección - Selecciona tu ubicación exacta</label>
                                <button type="button" class="btn btn-danger ms-2" onclick="abrirMapa()">
                                      <i class="fas fa-map-marker-alt"></i>
                                  </button>
                                </div>
                                <div class="text-700 fw-bold fs-10">
                                  Si tu empresa/negocio tiene SUCURSALES, añade todas desde tu perfil de vendedor, con sus ubicaciones exactas y sus catálogos de productos y servicios específicos. 
                                  Es importante que añadas tus sucursales para el correcto funcionamiento de tu negocio, y tus clientes sepan que tienen más opciones a nivel nacional.
                                </div>
                                <!--input class="form-control" type="text" id="direccion"-->
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Provincia</label>
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
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Cantón</label>
                                <select class="form-control" type="text" id="canton">
                                    <option value="">Seleccione cantón</option>
                                </select>
                              </div>
                              <div class="col-md-12 mb-3">
                                  <label class="form-label">Calle Principal</label>
                                  <input class="form-control mb-2" type="text" id="calle_principal" placeholder="Calle Principal">
                              </div>
                              
                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Calle Secundaria</label>
                                  <input class="form-control mb-2" type="text" id="calle_secundaria" placeholder="Calle Secundaria">
                              </div>

                              <div class="col-md-6 mb-3">
                                  <label class="form-label"># de Bien Inmueble</label>
                                  <input class="form-control mb-2" type="text" id="bien_inmueble" placeholder="# de bien inmueble">
                              </div>
                              
                              <!--div class="col-md-6 mb-3">
                                  <label class="form-label">Número para llamadas</label>
                                  <input class="form-control mb-2" type="text" id="telefono_contacto" placeholder="Ej. 0999999999">
                              </div-->

                              <!--div class="col-md-6 mb-3">
                                  <label class="form-label">Número para WhatsApp</label>
                                  <input class="form-control mb-2" type="text" id="whatsapp_contacto" placeholder="Ej. 0999999999">
                              </div-->

                              <div class="col-md-6 mb-3">
                                  <label class="form-label mb-0">Categoría Principal</label>
                                  <div class="text-700 fw-bold fs-10">Desde tu perfil de vendedor elige varias, de acuerdo a tus productos y servicios ofrecidos.</div>
                                  <select class="form-control" type="text" id="categoria_principal" onchange="obtenerCategorias()">
                                      <option value="">Seleccione categoría Principal</option>
                                  </select>
                              </div>

                              <div class="col-md-6 mb-3">
                                  <label class="form-label mb-0">Categorías Específicas</label>
                                  <div class="text-700 fw-bold fs-10">Desde tu perfil de vendedor elige varias, de acuerdo a tus productos y servicios ofrecidos.</div>
                                  <select class="form-control" type="text" id="categoria">
                                      <option value="">Seleccione categoría</option>
                                  </select>
                              </div>

                              <hr>
                              <h5>Datos para acceder al sistema</h5>
                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Usuario</label>
                                  <input class="form-control mb-2" type="text" id="username" placeholder="Usuario">
                              </div>

                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Correo</label>
                                  <input class="form-control mb-2" type="text" id="email" placeholder="Correo">
                              </div>

                            <div class="col-md-6 mb-3">
                              <label class="form-label">Contraseña</label>
                              <div class="input-group">
                                <input type="password" class="form-control" id="password" placeholder="Contraseña" required>
                                <button class="btn d-block text-white" style="background-color:#004E60;color:#FFF"
                                  type="button" id="togglePassword1" aria-label="Mostrar contraseña">
                                  <i class="fas fa-eye"></i>
                                </button>
                              </div>
                            </div>

                            <div class="col-md-6 mb-3">
                              <label class="form-label">Confirmar Contraseña</label>
                              <div class="input-group">
                                <input type="password" class="form-control" id="repeat_password" placeholder="Contraseña" required>
                                <button class="btn d-block text-white" style="background-color:#004E60;color:#FFF"
                                  type="button" id="togglePassword2" aria-label="Mostrar contraseña">
                                  <i class="fas fa-eye"></i>
                                </button>
                              </div>
                            </div>

                              <h5>Datos para facturación</h5>
                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Nombre/Razón social</label>
                                  <input class="form-control mb-2" type="text" id="razon_social" placeholder="Nombre/Razón social">
                              </div>

                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Celular/Teléfono</label>
                                  <input class="form-control mb-2" type="text" id="celular" placeholder="Celular/Teléfono">
                              </div>

                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Tipo de identificación</label>
                                  <select class="form-control" type="text" id="tipo_identificacion">
                                      <option value="cedula">Cédula</option>
                                      <option value="ruc">Ruc</option>
                                  </select>
                              </div>

                              <div class="col-md-6 mb-3">
                                  <label class="form-label">Cédula/Ruc</label>
                                  <input class="form-control mb-2" type="text" id="cedula_ruc" placeholder="Cédula/Ruc">
                              </div>

                              <div class="col-md-12 mb-3">
                                  <label class="form-label">Dirección</label>
                                  <input class="form-control" type="text" id="direccion">
                              </div>
                              

                          </div>

                        <!-- CONSENTIMIENTOS PROVEEDORES -->
                        <div class="mt-3">
                          <!-- Check 1 -->
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="chkLegales" required>
                            <label class="form-check-label" for="chkLegales">
                              Declaro que he leído y acepto los
                              <a href="terminos_condiciones_proveedores.php" target="_blank" rel="noopener" class="link-primary">Términos y Condiciones para Proveedores</a>,
                              la <a href="politica_privacidad_cookies.php" target="_blank" rel="noopener" class="link-primary">Política de Privacidad y Cookies</a>
                              y el <a href="aviso_legal.php" target="_blank" rel="noopener" class="link-primary">Aviso Legal</a> de FULMUV; que actúo debidamente facultado y
                              autorizo expresamente a FULMUV el tratamiento de mis datos personales conforme a las finalidades y bases legales descritas,
                              en cumplimiento de la Ley Orgánica de Protección de Datos Personales del Ecuador.
                              <div class="d-flex justify-content-center align-items-center mt-3 mb-3">
                                <h6 class="fw-bold me-3"><a href="../documentos/3_1_Aviso_Legal_y_Descargos_de_Responsabilidad_de_FULMUV.pdf" target="_blank" rel="noopener">Ver Términos y Condiciones para Proveedores</a></h6>
                                <h6 class="fw-bold me-3"><a href="../documentos/3_1_Política_de_Privacidad_y_Cookies_de_FULMUV.pdf" target="_blank" rel="noopener">Ver Políticas de Privacidad y Cookies</a></h6>
                                <h6 class="fw-bold me-3"><a href="../documentos/3_1_Términos_y_Condiciones_de_Uso_Política_de_Privacidad_Aviso_Legal_y_Descargos_de_Responsabilidad_para_Proveedores_de_FULMUV.pdf" target="_blank" rel="noopener">Ver Aviso Legal</a></h6>
                              </div>
                            </label>
                          </div>

                          <!-- Check 2 -->
                          <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" value="" id="chkEnvios" required>
                            <label class="form-check-label" for="chkEnvios">
                              Acepto las <strong>Condiciones de Uso del Servicio de Envíos y Logística FULMUV – GRUPO ENTREGAS</strong>, incluidas mis responsabilidades
                              sobre peso declarado, embalaje, etiquetado y valor comercial. Entiendo que la compraventa se realiza directamente entre comprador y proveedor,
                              sin intervención de FULMUV, y que FULMUV no asume responsabilidad por el transporte. Acepto una cobertura de seguro, para los envíos de mis productos, y acepto emitir una factura que sustente mi venta y sirva como respaldo de envío para mi cliente.
                              <div class="d-flex mt-3 mb-3">
                                <h6 class="fw-bold"><a href="../documentos/3_2_NW_Términos_y_Condiciones_de_Envíos_y_Logística_de_FULMUV.pdf" target="_blank" rel="noopener">Ver Condiciones de Envíos y Logística Proveedores</a></h6>
                              </div>
                            </label>
                          </div>

                          <!-- Mensaje de validación opcional -->
                          <div id="consentAlert" class="form-text text-danger d-none mt-2">
                            Debes aceptar ambos consentimientos para continuar.
                          </div>
                        </div>



                      </div>
                      <div class="mb-3">
                          <button class="btn d-block w-100 mt-3 text-white" type="submit" name="submit" onclick="saveEmpresa()" style="background: #004E60;">Guardar</button>
                      </div>
                  
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  $.get('../api/v1/fulmuv/categoriasPrincipales/All', {}, function (returnedData) {
    const res = (typeof returnedData === 'string') ? JSON.parse(returnedData) : returnedData;
    if (res && res.error === false) {
      const orden = ['accesorios', 'repuestos', 'servicios', 'vehiculos', 'eventos'];
      const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();

      const filtradas = (res.data || [])
        .filter(c => orden.includes(norm(c.nombre)))
        .sort((a, b) => orden.indexOf(norm(a.nombre)) - orden.indexOf(norm(b.nombre)));

      const sel = document.getElementById('categoria_principal');
      sel.innerHTML = '<option value="" disabled selected>Seleccione categoría principal</option>';

      filtradas.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id_categoria_principal;
        opt.textContent = c.nombre;
        sel.appendChild(opt);
      });
    }
  });

  // Mostrar modal
  $("#btnModal").click();
}

function saveEmpresa() {
  var nombre = $("#nombre").val();
  var nombre_titular = $("#nombre_titular").val();
  var apellido_titular = $("#apellido_titular").val();
  var direccion = $("#direccion").val();
  var username = $('#username').val();
  var email = $('#email').val();
  var password = $('#password').val();
  var repeat_password = $('#repeat_password').val();
  var whatsapp_contacto = '';
  var tipo_local = $('#tipo_local').val();
  // var agente = $('#agente').val();
  var categoria_principal = $('#categoria_principal').val();
  var categorias_referencia = $('#categoria').val();
  var provincia = $('#provincia').val();
  var canton = $('#canton').val();
  var calle_principal = $('#calle_principal').val();
  var calle_secundaria = $('#calle_secundaria').val();
  var bien_inmueble = $('#bien_inmueble').val();
  var razon_social = $('#razon_social').val();
  var celular = $('#celular').val();
  var tipo_identificacion = $('#tipo_identificacion').val();
  var cedula_ruc = $('#cedula_ruc').val();

  var sucursales = 'N';
  var $sucCheck = $('#suc_fulmuv, [id^="suc_"]').filter(':checkbox'); // soporta id dinámico como suc_{nombreKey}
  if ($sucCheck.length) {
    // si hay varios, toma el primero visible; si ninguno visible, usa el primero
    var $target = $sucCheck.filter(':visible').first();
    if ($target.length === 0) $target = $sucCheck.first();
    sucursales = $target.is(':checked') ? 'Y' : 'N';
  }

  username_guardado = username;

  if (nombre == "" || nombre_titular == "") {
    //SweetAlert("error", "Los campos nombre y dirección son obligatorios!!!")
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Los campos nombre y nombre de titular son obligatorios!!!",
    });
  } else if (!username || !email || !password || !repeat_password) {
    //SweetAlert("error", "Los campos usuario, correo y contraseñas son obligatorios.");
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Los campos usuario, correo y contraseñas son obligatorios.",
    });
    return;
  } else if (password !== repeat_password) {
    //SweetAlert("error", "Las contraseñas no coinciden.");
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Las contraseñas no coinciden.",
    });
    return;
  } else if (provincia == "" || canton == "") {
    //SweetAlert("error", "Las contraseñas no coinciden.");
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Debe seleccionar provincia y cantón.",
    });
    return;
  } else if (razon_social == "" || celular == "" || cedula_ruc == "") {
    //SweetAlert("error", "Las contraseñas no coinciden.");
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Debe llenar los datos de facturación.",
    });
    return;
  } else if (!$('#chkLegales').is(':checked')) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Debe aceptar los términos y condiciones Legales.",
    });
  } else if (!$('#chkEnvios').is(':checked')) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Debe aceptar los términos de envíos.",
    });
  } else {
    $.post('../api/v1/fulmuv/empresas/create', {
      nombre: nombre,
      nombre_titular: nombre_titular + " " + apellido_titular,
      direccion: direccion,
      latitud: latitud,
      longitud: longitud,
      tipo_local: tipo_local,
      whatsapp_contacto: whatsapp_contacto,
      username: username,
      email: email,
      password: password,
      categorias_referencia: categorias_referencia,
      provincia: provincia,
      canton: canton,
      calle_principal: calle_principal,
      calle_secundaria: calle_secundaria,
      bien_inmueble: bien_inmueble,
      razon_social: razon_social,
      celular: celular,
      tipo_identificacion: tipo_identificacion,
      cedula_ruc: cedula_ruc,
      latitud: latitud,
      longitud: longitud,
      sucursales: sucursales
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        id_empresa_devuelto = returned.id_empresa;
        id_usuario_devuelto = returned.id_usuario;
        $("#staticBackdrop").modal('hide');
        tokenizarTarjeta(email)
      } else {
        Swal.fire("Error", returned.msg, "error")
      }
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

function redirigirConPost(acceso, username_new) {
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "login.php"; // o la URL que necesites
  form.style.display = "none";

  // Campo acceso
  const input1 = document.createElement("input");
  input1.name = "acceso";
  input1.value = acceso;
  form.appendChild(input1);

  // Campo username_new
  const input2 = document.createElement("input");
  input2.name = "username_new";
  input2.value = username_new;
  form.appendChild(input2);

  document.body.appendChild(form);
  form.submit();
}

function tokenizarTarjeta(email) {
  $("#modal-pago").modal('show')

  console.log(id_membresia_seleccionada)
  // Configura UI de crédito según el plan elegido:
  configurarUICreditoSegunMembresia(id_membresia_seleccionada);

  // === Variable to use ===
  let environment = 'stg';
  let application_code = 'TESTECUADORSTG-EC-CLIENT'; // Provided by Payment Gateway
  let application_key = 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6'; // Provided by Payment Gateway
  // let environment = 'prod';
  // let application_code = 'FULMUV-PR-EC-CLIENT'; // Provided by Payment Gateway
  // let application_key = '8XJbDPhiJYeezjr92Qr3Tr4tSyC5gH'; // Provided by Payment Gateway
  let submitButton = document.querySelector('#tokenize_btn');
  submitButton.innerText = "Pagar";
  let submitInitialText = submitButton.textContent;
  submitButton.removeAttribute('disabled');
  submitButton.style.display = 'block';
  document.getElementById('tokenize_response').innerHTML = '';


  // Get the required additional data to tokenize card

  let get_tokenize_data = () => {
    let data = {
      locale: 'es',
      user: {
        id: id_empresa_devuelto,
        email: email,
      }, configuration: {
        default_country: 'ECU',
      },
      conf: {
        style_version: 2
      }
    }

    if (data.user.email == '') {
      swal({
        title: "Warning",
        text: "Email inválido, por favor contacte al administrador",
        type: "warning",
        confirmButtonColor: "#f5921e",
        confirmButtonText: "Ok",
        closeOnConfirm: false
      }, function () {
        // window.history.back(-1);
        window.location.reload()

      });
      return
    } else {
      return data
    }

  }

  // === Required callbacks ===
  // Executed when was called 'tokenize' function but the form was not completed.
  let notCompletedFormCallback = message => {

    // SweetAlert("error", message);


    document.getElementById('tokenize_response').innerHTML = `Not completed form: ${message}, Please fill required data`;
    submitButton.innerText = submitInitialText;
    submitButton.removeAttribute('disabled');
  }

  // Executed when was called 'tokenize' and the services response successfully.
  let responseCallback = response => {

    if (response.card) {

      // registrar el token en la bd
      if (response.card.status == "valid") {
        console.log(response)

        guardarToken(response.card.token, response.card.transaction_reference, id_usuario_devuelto, id_empresa_devuelto).then(function (token) {

          // realizar el cobro con token
          debitToken(token, id_usuario_devuelto, id_membresia_seleccionada, id_empresa_devuelto, valor_pagado, tipo_pago, meses_pago).then(function (transaction) {
            // registrar el pago eb la bd
            comprarDirecto(id_empresa_devuelto, transaction.id, transaction.authorization_code, "Y", transaction.payment_date)
          }).catch(function (error) {
            console.error(error);
          });

        }).catch(function (error) {
          console.error(error);
        });
      } else {
        SweetAlert("error", "Tajeta rechazada");

      }

    } else if (response.error) {

      // la tarjeta ya existe
      if (response.error.type.includes("Card already added")) {
        // intentar el cobro con token

        // Expresión regular para encontrar el número en el campo "type"
        var regex = /(\d+)/;

        // Extraer el número
        var resultado = regex.exec(response.error.type);

        // Comprobar si se encontró el número y almacenarlo en una variable
        var token = resultado ? resultado[0] : null;

        console.log(response)

        guardarToken(token, null, id_usuario_devuelto, id_empresa_devuelto).then(function (token) {

          // realizar el cobro con token
          debitToken(token, id_usuario_devuelto, id_membresia_seleccionada, id_empresa_devuelto, costo_seleccionado, tipo_pago, meses_pago).then(function (transaction) {
            // registrar el pago eb la bd
            comprarDirecto(id_empresa_devuelto, transaction.id, transaction.authorization_code, "Y", transaction.payment_date)
          }).catch(function (error) {
            console.error(error);
          });

        }).catch(function (error) {
          console.error(error);
        });


      } else {//manejo de algun otro error
        SweetAlert("error", response.error.type + ". " + response.error.help);
      }
    }
    // document.getElementById('tokenize_response').innerHTML = JSON.stringify(response);
    submitButton.style.display = 'none';
    // submitButton.style.display = 'none';
    $("#modal-pago").modal('hide')

  }


  // 2. Instance the [PaymentGateway](#PaymentGateway-class) with the required parameters.
  let pg_sdk = new PaymentGateway(environment, application_code, application_key);

  // 3. Generate the tokenization form with the required data. [generate_tokenize](#generate_tokenize-function)
  // At this point it's when the form is rendered on page.
  pg_sdk.generate_tokenize(get_tokenize_data(), '#tokenize_example', responseCallback, notCompletedFormCallback);

  // 4. Define the event to execute the [tokenize](#tokenize-function) action.
  submitButton.addEventListener('click', event => {
    if (!$('#checkTerminoCondicionesPago').is(':checked')) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Debe aceptar los términos y condiciones.",
      });
      return;
    }
    // Si hay selects visibles y el tipo es diferido, exigir meses
    if (tipo_pago !== 'corriente' && (!meses_pago || meses_pago === 0)) {
      Swal.fire({ icon: "error", title: "Error", text: "Seleccione el número de meses del diferido." });
      return;
    }
    document.getElementById('tokenize_response').innerHTML = '';
    submitButton.innerText = 'Procesando pago...';
    submitButton.setAttribute('disabled', 'disabled');
    pg_sdk.tokenize();
    event.preventDefault();

  });

}

// guardar el token del cliente
function guardarToken(token, transaction_reference = null, id_usuario, id_empresa) {
  return new Promise((resolve, reject) => {
    $.post('../api/v1/fulmuv/venta/recurrente/', {
      token: token,
      transaction_reference: transaction_reference,
      id_usuario: id_usuario,
      id_empresa: id_empresa,
    }, function (returnedData) {
      returnedData = JSON.parse(returnedData)
      if (returnedData["error"] == false) {
        resolve(token);//devolver el token para recalizar el cobro
      } else {
        SweetAlert("error", returnedData["msg"]);
        reject("Error en al guardar la tarjeta en la BD");
      }
    });
  });
}

// debito con token
function debitToken(token, id_usuario, id_membresia, id_empresa, valor, tipo_pago_param, meses_param) {
  if (promoConfig != null) {
    valor = 1;
  }

  // Usa params si llegan, si no usa tus globals existentes
  const tipoSeleccionado = (typeof tipo_pago_param !== 'undefined') ? tipo_pago_param : (typeof tipo_pago !== 'undefined' ? tipo_pago : 'corriente');
  const mesesSeleccionados = (typeof meses_param !== 'undefined') ? meses_param : (typeof meses_pago !== 'undefined' ? meses_pago : 0);

  const tipo_code = __mapTipoPagoCode(tipoSeleccionado);
  const meses_send = __normalizarMeses(tipoSeleccionado, mesesSeleccionados);

  return new Promise((resolve, reject) => {
    $.post('../api/v1/fulmuv/debitToken/', {
      token: token,
      id_usuario: id_usuario,
      id_membresia: id_membresia,
      id_empresa: id_empresa,
      valor: valor,
      tipo_pago: tipo_code,
      meses: meses_send
    }, function (returnedData) {
      returnedData = JSON.parse(returnedData)
      if (returnedData["error"] == false) {
        // devolver la transaccion
        resolve(returnedData["transaction"]);
      } else {
        SweetAlert("error", returnedData["msg"]);
        reject("Error en debitToken");
      }
    });
  });
}

function comprarDirecto(id_empresa, transaction_id, authorization_code, recurrente, payment_date) {
  console.log(id_empresa, transaction_id, authorization_code, recurrente, payment_date, id_membresia_seleccionada, id_usuario_devuelto, costo_seleccionado);

  swal({
    title: "Warning",
    html: true,
    type: "info",
    showCancelButton: false,
    showConfirmButton: false,
    text: `Espere un momento mientras se realiza la operación. <br>
          <div class='spinner-grow text-warning' role='status'>
            <span class='visually-hidden'>Loading...</span>
          </div>
          <div class='spinner-grow text-warning' role='status'>
            <span class='visually-hidden'>Loading...</span>
          </div>
          <div class='spinner-grow text-warning' role='status'>
            <span class='visually-hidden'>Loading...</span>
          </div>`,

  }, function () {
  });

  $.post("../api/v1/fulmuv/empresas/membresiasUpdate", {
    id_membresia: id_membresia_seleccionada,
    id_empresa: id_empresa,
    id_usuario: id_usuario_devuelto,
    pago_valor: valor_pagado,
    tipo: "empresa",
    transaction_id: transaction_id,
    authorization_code: authorization_code,
    recurrente: recurrente,
    payment_date: payment_date,
    valor_membresia: costo_seleccionado
  }, function (returnedData) {
    if (!returnedData.error) {
      swal({
        title: "!Pago registrado con éxito!",
        text: "Haz clic en OK para acceder al sistema. \n  Bienvenido a FULMUV",
        icon: "success",
        button: "OK",
      }, function () {
        redirigirConPost("true", username_guardado)
      });
    } else {
      swal({
        title: 'Error al registrar membresía.',
        text: 'Haz clic en OK para intentarlo nuevamente.',
        icon: 'error',
        button: 'OK'
      });
    }
  }, 'json');

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

$(document).on('click', '#btnAplicarCodigo', function () {
  const codigo = ($('#agente').val() || '').trim().toUpperCase();
  if (!codigo) return Swal.fire({ icon:"error", title:"Error", text:"Ingresa un código válido." });

  const agente = (agentes || []).find(a => {
    const cod = String(a.codigo || '').trim().toUpperCase();
    const est = String(a.estado || '').trim().toUpperCase();
    return cod === codigo && est === 'A';
  });

  if (!agente) return Swal.fire({ icon:"error", title:"Error", text:"Código inválido o inactivo." });

  agenteAplicado = agente;
  codigoAplicado = codigo;

  promoConfig = (codigo === '6FULMUV777' || codigo === '12FULMUV777') ? true : null;

  // refresca precios en UI sin reconstruir todo
  Object.keys(groupedMembresias).forEach(key => {
    const planes = groupedMembresias[key];
    const nombrePlan = planes[0].nombre;
    const nombreKey = nombrePlan.replace(/\s+/g, '').toLowerCase();

    actualizarPrecioCard({
      nombreKey,
      nombre: nombrePlan,
      selectId: `select_${nombreKey}`,
      precioId: `precio_${nombreKey}`,
      sucursalCheckId: `suc_${nombreKey}`
    });
  });

  Swal.fire({ icon:"success", title:"Listo", text:"Código aplicado." });
});


function isAnnual(dias) {
  const d = String(dias);
  return d === '360' || d === '365';
}

function badgeFor(nombre, dias) {
  if (!isAnnual(dias)) return '';
  const isFulMuv = /fulmuv/i.test(nombre);
  const text = isFulMuv ? 'Ahorra $81 +' : 'Ahorra $11 +';
  return `<span class="badge badge-subtle-success rounded-pill mt-1 d-inline-block">${text}</span>`;
}

$("#guardarUbicacion").on("click", function () {
  if (latitud && longitud) {
    console.log("Latitud" + latitud)
    console.log("Longitud" + longitud)
    obtenerDireccionDesdeCoords(latitud, longitud);
    $("#modalMapa").modal("hide")
  }
});

function initMap() {
  const defaultPos = { lat: -2.066660613045653, lng: -79.89915462468714 };

  map = new google.maps.Map(document.getElementById("mapaEntrega"), {
    center: defaultPos,
    zoom: 14,
  });

  geocoder = new google.maps.Geocoder();
  placesService = new google.maps.places.PlacesService(map);

  marker = new google.maps.Marker({
    map,
    draggable: true,
    position: defaultPos,
  });

  marker.addListener("dragend", () => {
    const pos = marker.getPosition();
    latitud = pos.lat();
    longitud = pos.lng();
    obtenerDireccionDesdeCoords(latitud, longitud);
  });

  const input = document.getElementById("buscarDireccion");
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

  // Permitir Enter aunque no elija sugerencia
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
          $("#direccion_mapa").val(place.formatted_address || place.name || query);
        }
      }
    );
  });
}


function obtenerDireccionDesdeCoords(lat, lng, callback = null) {
  const latlng = {
    lat: parseFloat(lat),
    lng: parseFloat(lng)
  };

  geocoder.geocode({
    location: latlng
  }, (results, status) => {
    if (status === "OK" && results[0]) {
      console.log(results)
      $("#direccion_mapa").val(results[0].formatted_address);
      $("#buscarDireccion").val(results[0].formatted_address); // actualiza también en buscador
      // updateCostoEnvioVisibility(); // <-- aquí

      if (typeof callback === "function") callback();
    } else {
      if (typeof callback === "function") callback();
    }
  });
}

// function updateCostoEnvioVisibility() {
//   const hasAddress = ($('#direccion_mapa').val() || '').trim().length > 0;
//   if (modoEntrega === 1 && hasAddress) {
//     $('#envioCostoBox').removeClass('d-none');
//   } else {
//     $('#envioCostoBox').addClass('d-none');
//   }
// }


function abrirMapa() {
  const el = document.getElementById('modalMapa');
  const modal = bootstrap.Modal.getOrCreateInstance(el, { backdrop: true, keyboard: true });

  // Apilado de modales (Bootstrap 5)
  el.addEventListener('show.bs.modal', function (ev) {
    const z = 1050 + 10 * document.querySelectorAll('.modal.show').length;
    ev.target.style.zIndex = z;
    setTimeout(() => {
      const bds = document.querySelectorAll('.modal-backdrop');
      if (bds.length) bds[bds.length - 1].style.zIndex = z - 5;
    }, 0);
  }, { once: true });

  // Inicializa o reajusta el mapa cuando el modal sea visible
  el.addEventListener('shown.bs.modal', function onShown() {
    el.removeEventListener('shown.bs.modal', onShown);
    if (!window.__mapReady) {
      initMap();
      window.__mapReady = true;
    } else {
      google.maps.event.trigger(map, 'resize');
      if (marker?.getPosition) map.setCenter(marker.getPosition());
    }
    setTimeout(() => document.getElementById('buscarDireccion')?.focus(), 120);
  });

  modal.show();
}

// Refs UI
function refsPago() {
  return {
    wrapTipo: document.getElementById('wrapperTipo'),
    selectTipo: document.getElementById('selectTipoDiferido'),
    ayudaTipo: document.getElementById('ayudaTipo'),
    wrapMeses: document.getElementById('wrapperMeses'),
    selectMeses: document.getElementById('selectMeses'),
    ayudaMeses: document.getElementById('ayudaMeses'),
    cuotaBox: document.getElementById('cuotaBox'),
    cuotaSpan: document.getElementById('cuotaEstimada'),
    totalSpan: document.getElementById('totalPago')
  };
}

// Dada la membresía seleccionada, decide qué tipos permitir
function obtenerTiposPermitidosPorPlan(membresia) {
  const nombre = (membresia?.nombre || '').toLowerCase();
  const dias = String(membresia?.dias_permitidos || '');

  const esFulmuv = /fulmuv/i.test(nombre);
  if (!esFulmuv) {
    // BasicMuv / OneMuv: solo corriente
    return { mostrarSelects: false, tipos: ['corriente'] };
  }

  // FULMUV:
  if (dias === '30') {
    // Mensual: solo corriente
    return { mostrarSelects: false, tipos: ['corriente'] };
  } else if (dias === '180') {
    // Semestral: corriente + sin_interes(3)
    return { mostrarSelects: true, tipos: ['corriente', 'sin_interes'], mesesPorTipo: { sin_interes: [3] } };
  } else if (dias === '365') {
    // Anual: corriente + sin_interes(3) + con_interes(6,9)
    return { mostrarSelects: true, tipos: ['corriente', 'sin_interes', 'con_interes'], mesesPorTipo: { sin_interes: [3], con_interes: [6, 9] } };
  }

  // Por defecto (otros períodos): solo corriente
  return { mostrarSelects: false, tipos: ['corriente'] };
}

// Llama esto al abrir el modal (después de conocer id_membresia_seleccionada)
function configurarUICreditoSegunMembresia(id_membresia) {
  const m = (membresiasData || []).find(x => x.id_membresia == id_membresia);
  const { wrapTipo, selectTipo, ayudaTipo, wrapMeses, selectMeses, ayudaMeses, cuotaBox } = refsPago();

  // Reset estado global
  tipo_pago = 'corriente';
  meses_pago = 0;

  // Limpia UI
  selectTipo.innerHTML = '';
  selectMeses.innerHTML = '';
  ayudaTipo.textContent = '';
  ayudaMeses.textContent = '';
  wrapMeses.style.display = 'none';
  selectMeses.disabled = true;
  cuotaBox.style.display = 'none';

  const cfg = obtenerTiposPermitidosPorPlan(m);

  if (!cfg.mostrarSelects || (cfg.tipos || []).length <= 1) {
    // No mostrar selects (solo corriente)
    wrapTipo.style.display = 'none';
    wrapMeses.style.display = 'none';
    return;
  }

  // Mostrar select de tipo
  wrapTipo.style.display = '';
  // Construye opciones de tipo
  const mapText = {
    corriente: 'Corriente',
    sin_interes: 'Diferido sin intereses',
    con_interes: 'Diferido con intereses'
  };
  selectTipo.innerHTML = '';
  cfg.tipos.forEach(t => {
    const o = document.createElement('option');
    o.value = t;
    o.textContent = mapText[t] || t;
    selectTipo.appendChild(o);
  });

  // Selección inicial = corriente
  selectTipo.value = 'corriente';
  ayudaTipo.textContent = 'Selecciona “Diferido” si deseas pagar en cuotas.';
  // Mantén meses oculto hasta que elijan un tipo diferido
  wrapMeses.style.display = 'none';
  selectMeses.disabled = true;

  // Guarda en dataset qué meses permitir para cada tipo (para onTipoChange)
  // Lo guardamos como JSON en atributos para no recalcular:
  selectTipo.dataset.mesesPorTipo = JSON.stringify(cfg.mesesPorTipo || {});
}

// === Handlers onchange ===
function onTipoChange(tipo) {
  const { selectTipo, wrapMeses, selectMeses, ayudaMeses, cuotaBox } = refsPago();
  tipo_pago = tipo;
  meses_pago = 0;
  cuotaBox.style.display = 'none';

  if (tipo === 'corriente') {
    wrapMeses.style.display = 'none';
    selectMeses.innerHTML = '';
    selectMeses.disabled = true;
    ayudaMeses.textContent = '';
    return;
  }

  // Si es diferido, mostrar meses según la configuración del plan actual
  const cfgMeses = JSON.parse(selectTipo.dataset.mesesPorTipo || '{}');
  const mesesPermitidos = (cfgMeses[tipo] || []);

  // Fallback genérico por si no existiera el dataset (no debería)
  let lista = [];
  if (mesesPermitidos.length) {
    lista = mesesPermitidos.map(n => ({ v: n, t: `${n} meses` }));
  } else {
    // Usa mapa general por tipo
    lista = MESES_POR_TIPO[tipo] || [];
  }

  wrapMeses.style.display = '';
  selectMeses.disabled = false;
  selectMeses.innerHTML = '<option value="" selected disabled>Selecciona meses</option>';
  lista.forEach(opt => {
    const o = document.createElement('option');
    o.value = String(opt.v);
    o.textContent = opt.t;
    selectMeses.appendChild(o);
  });

  ayudaMeses.textContent = (tipo === 'sin_interes')
    ? 'Cuotas fijas sin recargo.'
    : 'Cuotas con interés aplicado por la emisora.';
}

function onMesesChange(meses) {
  meses_pago = Number(meses) || 0;

  // Mostrar cuota estimada (visual)
  const { totalSpan, cuotaBox, cuotaSpan } = refsPago();
  const raw = (totalSpan?.textContent || '').replace('$', '').trim();
  const total = Number(raw) || 0;

  if (tipo_pago === 'corriente' || meses_pago <= 0) {
    cuotaBox.style.display = 'none';
    return;
  }
  const base = total / meses_pago;
  cuotaSpan.textContent = '$' + base.toFixed(2);
  cuotaBox.style.display = 'inline';
}

$(document).on('click', '#togglePassword1, #togglePassword2', function () {
  const isBtn1 = this.id === 'togglePassword1';
  const input = document.getElementById(isBtn1 ? 'password' : 'repeat_password');
  if (!input) return;

  const icon = this.querySelector('i');
  const showing = input.type === 'text';

  // toggle input
  input.type = showing ? 'password' : 'text';

  // toggle icon
  if (icon) {
    icon.classList.remove('fa-eye', 'fa-eye-slash', 'fas', 'far', 'fa');
    icon.classList.add('fas', showing ? 'fa-eye' : 'fa-eye-slash');
  }

  this.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
});

/*function actualizarPrecioCard({ nombreKey, nombre, selectId, precioId, sucursalCheckId }) {
  const diasSel = String($(`#${selectId}`).val() || '');
  const base = Number($(`#${precioId}`).data('base')) || 0;

  const conSucursal = (String(nombre).toLowerCase() === 'fulmuv')
    ? $(`#${sucursalCheckId}`).is(':checked')
    : false;

  const precioFinal = getPrecioFinal({
    nombre,
    dias: diasSel,
    conSucursal,
    base
  });

  $(`#${precioId}`).text(precioFinal);

  // total pago
  actualizarTotalPago(precioId);
}*/

function actualizarPrecioCard({ nombreKey, nombre, selectId, precioId, sucursalCheckId }) {
  const diasSel = String($(`#${selectId}`).val() || '');
  const base = Number($(`#${precioId}`).data('base')) || 0;

  const n = (nombre || '').trim().toLowerCase();
  const isFulMuv = n === 'fulmuv';
  const checkedSucursal = isFulMuv ? $(`#${sucursalCheckId}`).is(':checked') : false;

  // Detecta si hay código aplicado
  const hayCodigo = !!(typeof agenteAplicado !== 'undefined' && agenteAplicado) || !!(typeof codigoAplicado !== 'undefined' && codigoAplicado);

  // ===== 1) SIN CÓDIGO =====
  if (!hayCodigo) {
    if (isFulMuv) {
      if (diasSel === '365') $(`#${precioId}`).text(checkedSucursal ? '317' : base);
      else if (diasSel === '180') $(`#${precioId}`).text(checkedSucursal ? '177' : base);
      else if (diasSel === '30') $(`#${precioId}`).text(checkedSucursal ? '35' : base);
      else $(`#${precioId}`).text(base);
    } else {
      $(`#${precioId}`).text(base);
    }

    actualizarTotalPago(precioId);
    return;
  }

  // ===== 2) CON CÓDIGO =====
  const tipo = String((agenteAplicado?.tipo || '')).toLowerCase();
  const codigo = String((codigoAplicado || '')).toUpperCase();

  // --- Códigos especiales FULMUV ---
  if (tipo === 'fulmuv') {
    if (!isFulMuv) {
      $(`#${precioId}`).text(base);
      actualizarTotalPago(precioId);
      return;
    }

    // ✅ Regla normal de sucursales (SIEMPRE debe funcionar)
    const normalSucursal = () => {
      if (diasSel === '365') return checkedSucursal ? 317 : base;
      if (diasSel === '180') return checkedSucursal ? 177 : base;
      if (diasSel === '30')  return checkedSucursal ? 35  : base;
      return base;
    };

    // ✅ Si el código NO aplica al período, igual aplica la regla normal del check
    // 12FULMUV777 solo anual
    if (codigo === '12FULMUV777') {
      if (diasSel !== '365') {
        $(`#${precioId}`).text(normalSucursal());
        actualizarTotalPago(precioId);
        return;
      }
      // si es anual y aplica promo, puedes mostrar el precio “renovación” (237/297)
      $(`#${precioId}`).text(checkedSucursal ? '297' : '237');
      actualizarTotalPago(precioId);
      return;
    }

    // 6FULMUV777 solo semestral
    if (codigo === '6FULMUV777') {
      if (diasSel !== '180') {
        $(`#${precioId}`).text(normalSucursal());
        actualizarTotalPago(precioId);
        return;
      }
      $(`#${precioId}`).text(checkedSucursal ? '165' : '127');
      actualizarTotalPago(precioId);
      return;
    }

    // Otros códigos tipo fulmuv: aplica normal
    $(`#${precioId}`).text(normalSucursal());
    actualizarTotalPago(precioId);
    return;
  }


  // --- General: aplica tabla a todos los planes (según tu imagen) ---
  if (tipo === 'general') {
    const preciosGeneral = {
      onemuv:  { '30': 4,  '180': 19,  '365': 37 },
      basicmuv:{ '30': 4,  '180': 19,  '365': 37 },
      fulmuv: {
        con: { '30': 31, '180': 165, '365': 297 },
        sin: { '30': 25, '180': 127, '365': 237 }
      }
    };

    if (n.includes('onemuv')) $(`#${precioId}`).text(preciosGeneral.onemuv[diasSel] ?? base);
    else if (n.includes('basicmuv')) $(`#${precioId}`).text(preciosGeneral.basicmuv[diasSel] ?? base);
    else if (isFulMuv) {
      const val = checkedSucursal ? preciosGeneral.fulmuv.con[diasSel] : preciosGeneral.fulmuv.sin[diasSel];
      $(`#${precioId}`).text(val ?? base);
    } else {
      $(`#${precioId}`).text(base);
    }

    actualizarTotalPago(precioId);
    return;
  }

  // --- General anual: no cambia precio (mes extra se maneja backend) ---
  /* if (tipo === 'general_anual') {
    $(`#${precioId}`).text(base);
    actualizarTotalPago(precioId);
    return;
  } */
 // --- General FulMuv: aplica tabla SOLO a FULMUV (con/sin sucursales) ---
  if (tipo === 'general_anual') {
    if (!isFulMuv) {
      // si no es fulmuv, no toca
      $(`#${precioId}`).text(base);
      actualizarTotalPago(precioId);
      return;
    }

    const preciosGeneralFulmuv = {
      con: { '30': 35, '180': 177, '365': 317 },
      sin: { '30': 29, '180': 147, '365': 267 }
    };

    const val = checkedSucursal
      ? preciosGeneralFulmuv.con[diasSel]
      : preciosGeneralFulmuv.sin[diasSel];

    $(`#${precioId}`).text(val ?? base);
    actualizarTotalPago(precioId);
    return;
  }


  // fallback
  $(`#${precioId}`).text(base);
  actualizarTotalPago(precioId);
}


function getPrecioFinal({ nombre, dias, conSucursal, base }) {
  const n = (nombre || '').toLowerCase();
  const d = String(dias);

  const esFulmuv = n.includes('fulmuv');
  const esOne = n.includes('onemuv');
  const esBasic = n.includes('basicmuv');

  // Tabla “normal” (sin agente) para FulMuv con/sin sucursal
  const fulmuvTabla = {
    con: { '30': 31, '180': 165, '365': 297 },
    sin: { '30': 25, '180': 127, '365': 237 }
  };

  // Tabla “general” (cuando el agente es tipo general) para todos los planes
  const generalTabla = {
    onemuv: { '30': 4, '180': 19, '365': 37 },
    basicmuv:{ '30': 4, '180': 19, '365': 37 },
    fulmuv: {
      con: { '30': 31, '180': 165, '365': 297 },
      sin: { '30': 25, '180': 127, '365': 237 }
    }
  };

  // ===== 1) SIN AGENTE =====
  if (!agenteAplicado) {
    if (esFulmuv) {
      return conSucursal ? (fulmuvTabla.con[d] ?? base) : (fulmuvTabla.sin[d] ?? base);
    }
    return base;
  }

  // ===== 2) CON AGENTE =====
  const tipoAg = String(agenteAplicado.tipo || '').toLowerCase();

  // --- fulmuv: códigos especiales 6 y 12 (solo anual o semestral) ---
  if (tipoAg === 'fulmuv') {
    if (!esFulmuv) return base;

    if (codigoAplicado === '12FULMUV777') {
      // solo anual
      if (d !== '365') return base;
      // precio “real” mostrado en UI (renovación)
      return conSucursal ? 297 : 237;
    }

    if (codigoAplicado === '6FULMUV777') {
      // solo semestral
      if (d !== '180') return base;
      return conSucursal ? 165 : 127;
    }

    return base;
  }

  // --- general: aplica tabla a todos los planes ---
  if (tipoAg === 'general') {
    if (esOne) return generalTabla.onemuv[d] ?? base;
    if (esBasic) return generalTabla.basicmuv[d] ?? base;
    if (esFulmuv) return conSucursal ? (generalTabla.fulmuv.con[d] ?? base) : (generalTabla.fulmuv.sin[d] ?? base);
    return base;
  }

  // --- general_anual: NO cambia precio (solo +1 mes en backend) ---
  if (tipoAg === 'general_anual') {
    return base;
  }

  return base;
}
