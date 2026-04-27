let membresiasData = [];
let agentes = [];
let id_membresia_seleccionada = null;
let costo_seleccionado = null;
let id_empresa_devuelto = null;
let id_usuario_devuelto = null;
let username_guardado = null;
let password_guardado = null;
let groupedMembresias = {};
let valor_pagado = 0;
let membresiaSeleccionadaActual = null;

let agenteAplicado = null;     // objeto del agente encontrado
let codigoAplicado = null;     // string del código
let promoConfig = null;        // ya lo tienes
let promoResumenActual = null;
let empresaRegistroPayload = null;
let checkoutSelectionState = null;
const EMPRESA_CHECKOUT_DRAFT_KEY = 'fulmuv_empresa_checkout_draft_v1';
let draftResumeHandled = false;
let returningToWizardFromPayment = false;


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
  // Si es corriente o no hay meses, envía 0
  if (tipo === 'corriente' || !meses || Number(meses) === 0) return 0;
  return Number(meses);
}

function storageDisponible() {
  try {
    const key = '__fulmuv_storage_test__';
    localStorage.setItem(key, '1');
    localStorage.removeItem(key);
    return true;
  } catch (_) {
    return false;
  }
}

function getDraftStorage() {
  return storageDisponible() ? localStorage : null;
}

function collectPlanSelections() {
  const selections = {};
  Object.keys(groupedMembresias || {}).forEach(key => {
    const nombre = groupedMembresias[key]?.[0]?.nombre || key;
    const nombreKey = nombre.replace(/\s+/g, '').toLowerCase();
    const selectId = `select_${nombreKey}`;
    const sucId = `suc_${nombreKey}`;
    selections[nombreKey] = {
      dias: $(`#${selectId}`).val() || null,
      sucursales: $(`#${sucId}`).is(':checked')
    };
  });
  return selections;
}

function hydrateFormularioDesdePayload() {
  const payload = empresaRegistroPayload || {};
  if (!Object.keys(payload).length) return;

  const pairs = {
    '#nombre': payload.nombre || '',
    '#nombre_titular': payload.nombre_titular_nombres || (payload.nombre_titular || '').split(' ').slice(0, -1).join(' ') || payload.nombre_titular || '',
    '#apellido_titular': payload.apellido_titular || (payload.nombre_titular || '').split(' ').slice(-1).join(' ') || '',
    '#direccion': payload.direccion || '',
    '#direccion_mapa': payload.direccion_mapa || payload.ubicacion_exacta || '',
    '#username': payload.username || '',
    '#email': payload.email || '',
    '#password': payload.password || '',
    '#repeat_password': payload.password || '',
    '#telefono_contacto': payload.telefono_contacto || '',
    '#whatsapp_contacto': payload.whatsapp_contacto || '',
    '#tipo_local': payload.tipo_local || '',
    '#provincia': payload.provincia || '',
    '#canton': payload.canton || '',
    '#calle_principal': payload.calle_principal || '',
    '#calle_secundaria': payload.calle_secundaria || '',
    '#bien_inmueble': payload.bien_inmueble || '',
    '#razon_social': payload.razon_social || '',
    '#celular': payload.celular || '',
    '#tipo_identificacion': payload.tipo_identificacion || 'cedula',
    '#cedula_ruc': payload.cedula_ruc || ''
  };

  Object.entries(pairs).forEach(([selector, value]) => {
    const $el = $(selector);
    if ($el.length) {
      $el.val(value);
    }
  });

  if ($('#provincia').length && payload.provincia) {
    cargarCantones(payload.provincia);
    $('#canton').val(payload.canton || '');
  }

  if ($('#chkLegales').length) $('#chkLegales').prop('checked', !!payload.chkLegales);
  if ($('#chkEnvios').length) $('#chkEnvios').prop('checked', !!payload.chkEnvios);
}

function collectCurrentFormData() {
  const modalAbierto = $('#staticBackdrop').length > 0;
  if (!modalAbierto && !empresaRegistroPayload) return null;

  const payload = {
    ...(empresaRegistroPayload || {}),
    nombre: $('#nombre').length ? $('#nombre').val() : (empresaRegistroPayload?.nombre || ''),
    direccion: $('#direccion').length ? $('#direccion').val() : (empresaRegistroPayload?.direccion || ''),
    direccion_mapa: $('#direccion_mapa').length ? $('#direccion_mapa').val() : (empresaRegistroPayload?.direccion_mapa || empresaRegistroPayload?.ubicacion_exacta || ''),
    tipo_local: $('#tipo_local').length ? $('#tipo_local').val() : (empresaRegistroPayload?.tipo_local || ''),
    telefono_contacto: $('#telefono_contacto').length ? $('#telefono_contacto').val() : (empresaRegistroPayload?.telefono_contacto || ''),
    whatsapp_contacto: $('#whatsapp_contacto').length ? $('#whatsapp_contacto').val() : (empresaRegistroPayload?.whatsapp_contacto || ''),
    username: $('#username').length ? $('#username').val() : (empresaRegistroPayload?.username || ''),
    email: $('#email').length ? $('#email').val() : (empresaRegistroPayload?.email || ''),
    password: $('#password').length ? $('#password').val() : (empresaRegistroPayload?.password || ''),
    provincia: $('#provincia').length ? $('#provincia').val() : (empresaRegistroPayload?.provincia || ''),
    canton: $('#canton').length ? $('#canton').val() : (empresaRegistroPayload?.canton || ''),
    calle_principal: $('#calle_principal').length ? $('#calle_principal').val() : (empresaRegistroPayload?.calle_principal || ''),
    calle_secundaria: $('#calle_secundaria').length ? $('#calle_secundaria').val() : (empresaRegistroPayload?.calle_secundaria || ''),
    bien_inmueble: $('#bien_inmueble').length ? $('#bien_inmueble').val() : (empresaRegistroPayload?.bien_inmueble || ''),
    razon_social: $('#razon_social').length ? $('#razon_social').val() : (empresaRegistroPayload?.razon_social || ''),
    celular: $('#celular').length ? $('#celular').val() : (empresaRegistroPayload?.celular || ''),
    tipo_identificacion: $('#tipo_identificacion').length ? $('#tipo_identificacion').val() : (empresaRegistroPayload?.tipo_identificacion || ''),
    cedula_ruc: $('#cedula_ruc').length ? $('#cedula_ruc').val() : (empresaRegistroPayload?.cedula_ruc || ''),
    latitud: latitud,
    longitud: longitud,
    chkLegales: $('#chkLegales').is(':checked'),
    chkEnvios: $('#chkEnvios').is(':checked')
  };

  const nombres = $('#nombre_titular').length ? $('#nombre_titular').val() : '';
  const apellidos = $('#apellido_titular').length ? $('#apellido_titular').val() : '';
  if (nombres || apellidos) {
    payload.nombre_titular = `${nombres} ${apellidos}`.trim();
    payload.nombre_titular_nombres = nombres;
    payload.apellido_titular = apellidos;
  }

  return payload;
}

function saveCheckoutDraft(stageOverride = null) {
  const storage = getDraftStorage();
  if (!storage) return;

  const empresaPayload = collectCurrentFormData() || empresaRegistroPayload;
  const stage = stageOverride || ($('#modal-pago').hasClass('show') ? 'payment' : ($('#staticBackdrop').hasClass('show') ? 'form' : 'plan'));

  const draft = {
    version: 1,
    saved_at: new Date().toISOString(),
    stage,
    id_membresia_seleccionada,
    costo_seleccionado,
    valor_pagado,
    membresiaSeleccionadaActual,
    agenteAplicado,
    codigoAplicado,
    promoResumenActual,
    empresaRegistroPayload: empresaPayload || null,
    checkoutSelectionState,
    tipo_pago,
    meses_pago,
    latitud,
    longitud,
    planSelections: collectPlanSelections()
  };

  storage.setItem(EMPRESA_CHECKOUT_DRAFT_KEY, JSON.stringify(draft));
}

function clearCheckoutDraft() {
  const storage = getDraftStorage();
  if (!storage) return;
  storage.removeItem(EMPRESA_CHECKOUT_DRAFT_KEY);
}

function resetCheckoutState(options = {}) {
  const keepDraftHandled = !!options.keepDraftHandled;

  clearCheckoutDraft();

  id_membresia_seleccionada = null;
  costo_seleccionado = null;
  id_empresa_devuelto = null;
  id_usuario_devuelto = null;
  username_guardado = null;
  password_guardado = null;
  valor_pagado = 0;
  membresiaSeleccionadaActual = null;
  agenteAplicado = null;
  codigoAplicado = null;
  promoConfig = null;
  promoResumenActual = null;
  empresaRegistroPayload = null;
  checkoutSelectionState = null;
  tipo_pago = 'corriente';
  meses_pago = 0;
  latitud = "";
  longitud = "";

  if (!keepDraftHandled) {
    draftResumeHandled = false;
  }

  $('#agente').val('');
  $('#totalPago').text('$0');
  $('#modal-pago').modal('hide');
  $('#staticBackdrop').modal('hide');

  if ($('#tokenize_example').length) {
    $('#tokenize_example').html('');
  }
  if ($('#tokenize_response').length) {
    $('#tokenize_response').html('');
  }
}

function loadCheckoutDraft() {
  const storage = getDraftStorage();
  if (!storage) return null;
  const raw = storage.getItem(EMPRESA_CHECKOUT_DRAFT_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch (_) {
    storage.removeItem(EMPRESA_CHECKOUT_DRAFT_KEY);
    return null;
  }
}

function restorePlanSelectionsFromDraft(draft) {
  const selections = draft?.planSelections || {};
  Object.entries(selections).forEach(([nombreKey, info]) => {
    const selectId = `#select_${nombreKey}`;
    const sucId = `#suc_${nombreKey}`;
    if ($(selectId).length && info?.dias) {
      $(selectId).val(info.dias).trigger('change');
    }
    if ($(sucId).length) {
      $(sucId).prop('checked', !!info?.sucursales).trigger('change');
    }
  });
}

function resumeCheckoutFromDraft(draft) {
  if (!draft) return;

  latitud = draft.latitud || latitud;
  longitud = draft.longitud || longitud;
  tipo_pago = draft.tipo_pago || 'corriente';
  meses_pago = Number(draft.meses_pago || 0);
  empresaRegistroPayload = draft.empresaRegistroPayload || null;
  username_guardado = empresaRegistroPayload?.username || username_guardado;
  password_guardado = empresaRegistroPayload?.password || password_guardado;
  agenteAplicado = draft.agenteAplicado || null;
  codigoAplicado = draft.codigoAplicado || null;
  promoResumenActual = draft.promoResumenActual || null;

  if ($('#agente').length && codigoAplicado) {
    $('#agente').val(codigoAplicado);
  }

  restorePlanSelectionsFromDraft(draft);

  if (draft.id_membresia_seleccionada) {
    saveMembresia(draft.id_membresia_seleccionada);
    setTimeout(() => {
      hydrateFormularioDesdePayload();
      updateEmpresaWizardStep(1);
      $('#modal-pago').modal('hide');
      $('#staticBackdrop').modal('show');
    }, 250);
  }
}

function promptResumeCheckoutDraft() {
  if (draftResumeHandled) return;
  draftResumeHandled = true;

  const draft = loadCheckoutDraft();
  if (!draft || !draft.id_membresia_seleccionada) return;

  const stageText = draft.stage === 'payment' ? 'quedaste en el pago' : 'quedaste en el registro';
  Swal.fire({
    icon: 'info',
    title: 'Tienes un proceso guardado',
    text: `Detectamos que ${stageText}. ¿Deseas continuar?`,
    showCancelButton: true,
    confirmButtonText: 'Continuar',
    cancelButtonText: 'Empezar de nuevo'
  }).then((result) => {
    if (result.isConfirmed) {
      resumeCheckoutFromDraft(draft);
      return;
    }
    resetCheckoutState({ keepDraftHandled: true });
  });
}

$(document).on('click', '#btnEmpezarDeNuevoCheckout', function () {
  Swal.fire({
    icon: 'warning',
    title: 'Empezar de nuevo',
    text: 'Se borrará el registro guardado y tendrás que volver a seleccionar el plan.',
    showCancelButton: true,
    confirmButtonText: 'Sí, borrar todo',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (!result.isConfirmed) return;

    resetCheckoutState({ keepDraftHandled: true });
    renderMembresiasSelect();
  });
});

$(document).ready(function () {
  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      membresiasData = returned.data;
      // renderAllTabs(); // Llenar todos los tabs al cargar
      renderMembresiasSelect(); // Render inicial con selects por card
      promptResumeCheckoutDraft();
    }
  });
  $.get('../api/v1/fulmuv/agentes/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      agentes = returned.data;
    }
  });
});

$(document).on('input change', '#staticBackdrop input, #staticBackdrop select', function () {
  if ($('#staticBackdrop').hasClass('show')) {
    saveCheckoutDraft('form');
    $(this).removeClass('is-invalid');
    $(this).siblings('.empresa-wizard-error').remove();
    $(this).closest('.input-group').siblings('.empresa-wizard-error').remove();
  }
});

$(document).on('input', '#username', function () {
  const normalized = String($(this).val() || '').replace(/\s+/g, '');
  if ($(this).val() !== normalized) {
    $(this).val(normalized);
  }
});

$(document).on('input change', '#modal-pago input, #modal-pago select', function () {
  if ($('#modal-pago').hasClass('show')) {
    saveCheckoutDraft('payment');
  }
});

$(document).on('hidden.bs.modal', '#staticBackdrop', function () {
  if (id_membresia_seleccionada || empresaRegistroPayload) {
    saveCheckoutDraft('form');
  }
});

$(document).on('hidden.bs.modal', '#modal-pago', function () {
  if (id_membresia_seleccionada || empresaRegistroPayload) {
    saveCheckoutDraft(returningToWizardFromPayment ? 'form' : 'payment');
  }
  returningToWizardFromPayment = false;
});

$(document).on('click', '#empresaWizardNext', function () {
  const current = Number($('#staticBackdrop').attr('data-current-step') || 1);
  if (!validateEmpresaWizardStep(current)) return;
  updateEmpresaWizardStep(Math.min(current + 1, 3));
  saveCheckoutDraft('form');
});

$(document).on('click', '#empresaWizardPrev', function () {
  const current = Number($('#staticBackdrop').attr('data-current-step') || 1);
  updateEmpresaWizardStep(Math.max(current - 1, 1));
  saveCheckoutDraft('form');
});

$(document).on('click', '#btnEditarInformacionPago', function () {
  returningToWizardFromPayment = true;
  saveCheckoutDraft('form');
  $('#modal-pago').modal('hide');
  setTimeout(() => {
    $('#staticBackdrop').modal('show');
    updateEmpresaWizardStep(3);
  }, 220);
});

window.addEventListener('beforeunload', function () {
  if (id_membresia_seleccionada || empresaRegistroPayload || $('#staticBackdrop').hasClass('show') || $('#modal-pago').hasClass('show')) {
    saveCheckoutDraft();
  }
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
    const promoDetailId = `promo_detalle_${nombreKey}`;
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
              <div class="text-start" id="${promoDetailId}"></div>
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

    // ðŸ‘‰ Guardar precio base del plan por defecto
    $(`#${precioId}`).data('base', Number(defaultPlan.costo));

    // BotÃ³n comprar con plan por defecto
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

        // âœ… actualizar total pago siempre que cambie el precio
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
      $(`#${precioId}`).data('base', Number(plan.costo));      // ðŸ‘ˆ actualizar precio base
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

    actualizarPrecioCard({ nombreKey, nombre, selectId, precioId, sucursalCheckId });

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

/**
 * Convierte código de tipo Nuvei/Paymentez a nombre de marca legible
 * Códigos: vi=Visa, mc=Mastercard, ax=Amex, di=Discover, dc=Diners
 */
function _normalizeBrandCrear(rawType) {
  const t = (rawType || '').toLowerCase().trim();
  if (t === 'vi'  || t.includes('visa'))     return 'Visa';
  if (t === 'mc'  || t.includes('master'))   return 'Mastercard';
  if (t === 'ax'  || t.includes('amex'))     return 'American Express';
  if (t === 'di'  || t.includes('discover')) return 'Discover';
  if (t === 'dc'  || t.includes('diners'))   return 'Diners';
  return t || null;
}

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

function normalizarNombrePlan(nombre) {
  return String(nombre || '').trim().toLowerCase();
}

function getPlanKey(nombre) {
  const normalized = normalizarNombrePlan(nombre);
  if (normalized.includes('onemuv')) return 'onemuv';
  if (normalized.includes('basicmuv')) return 'basicmuv';
  if (normalized.includes('fulmuv')) return 'fulmuv';
  return normalized || 'desconocido';
}

function formatCurrencyValue(value) {
  const amount = Number(value || 0);
  return `$${amount.toFixed(2).replace(/\.00$/, '')}`;
}

function getFulmuvRegularPrice(dias, conSucursal, base) {
  const tabla = {
    con: { '30': 35, '180': 177, '365': 317 },
    sin: { '30': 25, '180': 147, '365': 267 }
  };

  if (conSucursal) return Number(tabla.con[String(dias)] ?? base ?? 0);
  return Number(tabla.sin[String(dias)] ?? base ?? 0);
}

function getRegularPriceForContext(context) {
  if (!context) return 0;
  if (context.planKey === 'fulmuv') {
    return getFulmuvRegularPrice(context.dias, context.conSucursal, context.base);
  }
  return Number(context.base || 0);
}

function buildPromotionBadge(label, tone = 'success') {
  return `<span class="badge badge-subtle-${tone} rounded-pill mt-1 d-inline-block">${label}</span>`;
}

function getContextFromCard({ nombreKey, nombre, selectId, precioId, sucursalCheckId }) {
  const diasSel = String($(`#${selectId}`).val() || '');
  const planes = groupedMembresias[normalizarNombrePlan(nombre)] || [];
  const plan = planes.find(p => String(p.dias_permitidos) === diasSel) || planes[0] || null;
  const base = Number($(`#${precioId}`).data('base')) || Number(plan?.costo || 0);
  const planKey = getPlanKey(nombre);
  const conSucursal = planKey === 'fulmuv' ? $(`#${sucursalCheckId}`).is(':checked') : false;

  return {
    nombreKey,
    nombre,
    planKey,
    dias: diasSel,
    periodicidad: diasToText(diasSel),
    selectId,
    precioId,
    sucursalCheckId,
    plan,
    base,
    conSucursal
  };
}

function getContextByMembresiaId(idMembresia) {
  const membresia = (membresiasData || []).find(m => String(m.id_membresia) === String(idMembresia));
  if (!membresia) return null;

  const nombre = membresia.nombre || '';
  const nombreKey = nombre.replace(/\s+/g, '').toLowerCase();

  return getContextFromCard({
    nombreKey,
    nombre,
    selectId: `select_${nombreKey}`,
    precioId: `precio_${nombreKey}`,
    sucursalCheckId: `suc_${nombreKey}`
  });
}

function evaluatePromotionForContext(context, agente = agenteAplicado, codigo = codigoAplicado) {
  const regularPrice = getRegularPriceForContext(context);
  const result = {
    applies: false,
    validCode: false,
    invalidMessage: '',
    planKey: context?.planKey || '',
    periodicidad: context?.periodicidad || '',
    originalPrice: regularPrice,
    promotionalPrice: regularPrice,
    amountToday: regularPrice,
    nextMonthAmount: null,
    displayPrice: regularPrice,
    displayPeriodText: `/ ${context?.periodicidad || ''}`,
    badgeHtml: context ? badgeFor(context.nombre, context.dias) : '',
    detailHtml: '',
    promotionMessage: '',
    detailLabel: 'Total',
    agente: agente || null,
    codigo: codigo || null,
    tipo: String(agente?.tipo || '').toLowerCase()
  };

  if (!context || !agente || !codigo) {
    return result;
  }

  const tipo = String(agente.tipo || '').trim().toLowerCase();
  const isFulmuvPlan = context.planKey === 'fulmuv';
  const isAnnual = String(context.dias) === '365';
  const isSemiAnnual = String(context.dias) === '180';

  if (tipo === 'general' || tipo === 'general_anual') {
    if (!isFulmuvPlan || !isAnnual) {
      result.invalidMessage = 'Este código solo aplica para el plan FULMUV anual.';
      return result;
    }

    const promoPrice = context.conSucursal ? 297 : 237;
    result.applies = true;
    result.validCode = true;
    result.promotionalPrice = promoPrice;
    result.amountToday = promoPrice;
    result.displayPrice = promoPrice;
    result.badgeHtml = buildPromotionBadge('Código anual aplicado');
    result.detailHtml = `
      <div class="small text-success fw-semi-bold mt-2">Código ${codigo} aplicado.</div>
      <div class="small text-700">Precio original: ${formatCurrencyValue(regularPrice)}</div>
      <div class="small text-primary fw-semi-bold">Pagas hoy: ${formatCurrencyValue(promoPrice)}</div>
    `;
    result.promotionMessage = `Código ${codigo} aplicado al plan FULMUV anual.`;
    return result;
  }

  if (tipo === 'fulmuv') {
    if (!isFulmuvPlan || (!isSemiAnnual && !isAnnual)) {
      result.invalidMessage = 'Este código solo aplica para los planes FULMUV semestral y anual.';
      return result;
    }

    const nextMonthAmount = isAnnual
      ? (context.conSucursal ? 297 : 237)
      : (context.conSucursal ? 165 : 127);

    result.applies = true;
    result.validCode = true;
    result.promotionalPrice = nextMonthAmount;
    result.amountToday = 1;
    result.nextMonthAmount = nextMonthAmount;
    result.displayPrice = 1;
    result.displayPeriodText = '/ hoy';
    result.badgeHtml = buildPromotionBadge('Primer cobro $1');
    result.detailHtml = `
      <div class="small text-success fw-semi-bold mt-2">Código ${codigo} aplicado.</div>
      <div class="small text-primary fw-semi-bold">Hoy pagas: ${formatCurrencyValue(1)}</div>
      <div class="small text-700">Próximo cobro al siguiente mes: ${formatCurrencyValue(nextMonthAmount)}</div>
      <div class="small text-700">Precio original del plan: ${formatCurrencyValue(regularPrice)}</div>
    `;
    result.promotionMessage = `Primer cobro hoy: ${formatCurrencyValue(1)}. Próximo cobro al siguiente mes: ${formatCurrencyValue(nextMonthAmount)}.`;
    result.detailLabel = 'Primer cobro hoy';
    return result;
  }

  result.invalidMessage = 'Este código no aplica al plan seleccionado.';
  return result;
}

function buildResumenPromocion(context, evaluation) {
  if (!context) return null;

  return {
    id_agente: evaluation?.agente?.id_agente ?? null,
    codigo: evaluation?.codigo ?? null,
    tipo: evaluation?.tipo ?? null,
    plan_aplicado: context.nombre,
    periodicidad: context.periodicidad,
    sucursales: context.conSucursal ? 'Y' : 'N',
    precio_original: Number(evaluation?.originalPrice ?? 0),
    precio_promocional: Number(evaluation?.promotionalPrice ?? evaluation?.originalPrice ?? 0),
    monto_hoy: Number(evaluation?.amountToday ?? 0),
    monto_siguiente_mes: evaluation?.nextMonthAmount != null ? Number(evaluation.nextMonthAmount) : null,
    mensaje_promocional: evaluation?.promotionMessage || '',
    aplica_promocion: !!evaluation?.applies
  };
}

function renderCheckoutSummary() {
  const container = $('#checkoutResumenPromo');
  if (!container.length || !checkoutSelectionState) return;

  const empresa = empresaRegistroPayload || {};
  const promo = promoResumenActual || {};
  const planName = checkoutSelectionState.nombre || '';
  const periodicidad = checkoutSelectionState.periodicidad || '';
  const sucursales = checkoutSelectionState.conSucursal ? 'Sí' : 'No';
  const codigoHtml = promo.aplica_promocion
    ? `<div><strong>Código aplicado:</strong> ${promo.codigo}</div>
       <div><strong>Tipo de código:</strong> ${promo.tipo}</div>
       <div><strong>Detalle de la promoción:</strong> ${promo.mensaje_promocional}</div>`
    : `<div><strong>Código aplicado:</strong> No aplica</div>`;
  const siguienteCobroHtml = promo.aplica_promocion && promo.monto_siguiente_mes != null
    ? `<div><strong>Siguiente cobro:</strong> ${formatCurrencyValue(promo.monto_siguiente_mes)}</div>`
    : '';
  const fulmuvEspecialHtml = promo.aplica_promocion && promo.tipo === 'fulmuv'
    ? `<div class="mt-2 text-primary fw-semi-bold">Primer cobro hoy: ${formatCurrencyValue(promo.monto_hoy)}</div>
       <div class="text-700">Próximo cobro al siguiente mes: ${formatCurrencyValue(promo.monto_siguiente_mes)}</div>`
    : '';

  $('#paymentHeaderPlan').text(planName || '-');
  $('#paymentHeaderPrice').text(formatCurrencyValue(promo.monto_hoy || valor_pagado || checkoutSelectionState.regularPrice || 0));

  container.html(`
    <div class="payment-summary-hero">
      <div class="payment-summary-hero-card">
        <div class="payment-summary-plan-kicker">Membresía seleccionada</div>
        <div class="payment-summary-plan-title">${planName || '-'}</div>
        <div class="payment-summary-plan-meta">
          <span class="payment-summary-pill">${periodicidad || '-'}</span>
          <span class="payment-summary-pill">Sucursales: ${sucursales}</span>
          <span class="payment-summary-pill">Código: ${promo.aplica_promocion ? promo.codigo : 'No aplica'}</span>
        </div>
      </div>
      <div class="payment-summary-total-card">
        <div class="payment-summary-total-label">Monto a pagar hoy</div>
        <div class="payment-summary-total-value">${formatCurrencyValue(promo.monto_hoy || valor_pagado || 0)}</div>
        <div class="payment-summary-total-note">Revisa este valor final antes de confirmar el pago seguro de tu membresía.</div>
      </div>
    </div>

    <div class="payment-summary-columns">
      <div class="payment-summary-panel">
        <h6>Información de la empresa</h6>
        <div class="payment-summary-grid">
          <div class="payment-summary-item is-wide">
            <span class="payment-summary-label">Nombre de empresa</span>
            <div class="payment-summary-value">${empresa.nombre || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Titular</span>
            <div class="payment-summary-value">${empresa.nombre_titular || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Correo</span>
            <div class="payment-summary-value">${empresa.email || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Teléfono de contacto</span>
            <div class="payment-summary-value">${empresa.telefono_contacto || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">WhatsApp</span>
            <div class="payment-summary-value">${empresa.whatsapp_contacto || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Provincia</span>
            <div class="payment-summary-value">${empresa.provincia || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Cantón</span>
            <div class="payment-summary-value">${empresa.canton || '-'}</div>
          </div>
        </div>
      </div>

      <div class="payment-summary-panel">
        <h6>Usuario, facturación y pago</h6>
        <div class="payment-summary-grid">
          <div class="payment-summary-item">
            <span class="payment-summary-label">Usuario de acceso</span>
            <div class="payment-summary-value">${empresa.username || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Razón social</span>
            <div class="payment-summary-value">${empresa.razon_social || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Identificación</span>
            <div class="payment-summary-value">${empresa.cedula_ruc || '-'}</div>
          </div>
          <div class="payment-summary-item">
            <span class="payment-summary-label">Celular de facturación</span>
            <div class="payment-summary-value">${empresa.celular || '-'}</div>
          </div>
          <div class="payment-summary-item is-wide">
            <span class="payment-summary-label">Detalle del plan</span>
            <div class="payment-summary-inline">
              <div><strong>Precio original:</strong> ${formatCurrencyValue(promo.precio_original || checkoutSelectionState.regularPrice || 0)}</div>
              <div><strong>Precio promocional:</strong> ${formatCurrencyValue(promo.precio_promocional || promo.precio_original || checkoutSelectionState.regularPrice || 0)}</div>
              <div><strong>Monto a pagar hoy:</strong> ${formatCurrencyValue(promo.monto_hoy || valor_pagado || 0)}</div>
              ${siguienteCobroHtml}
              ${codigoHtml}
              ${fulmuvEspecialHtml}
            </div>
          </div>
        </div>
      </div>
    </div>
  `);
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
  if (!document.getElementById("categoria") || document.getElementById("categoria").type === "hidden") {
    return;
  }
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
  membresiaSeleccionadaActual = membresiaSeleccionada || null;
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
  membresiaSeleccionadaActual = membresiaSeleccionada || null;

  const context = getContextByMembresiaId(id_membresia);
  const evaluation = evaluatePromotionForContext(context);

  checkoutSelectionState = {
    ...(context || {}),
    regularPrice: evaluation.originalPrice,
    promotion: evaluation
  };

  costo_seleccionado = Number(evaluation.promotionalPrice || evaluation.originalPrice || 0).toFixed(2);
  valor_pagado = Number(evaluation.amountToday || 0);
  promoResumenActual = buildResumenPromocion(context, evaluation);

  $("#totalPago").text(formatCurrencyValue(valor_pagado));
  renderCheckoutSummary();
  saveCheckoutDraft('form');

  mostrarFormulario();
}

function getEmpresaWizardSummaryData() {
  return {
    nombre: $('#nombre').val() || empresaRegistroPayload?.nombre || '-',
    nombres: $('#nombre_titular').val() || empresaRegistroPayload?.nombre_titular_nombres || '-',
    apellidos: $('#apellido_titular').val() || empresaRegistroPayload?.apellido_titular || '-',
    tipo_local: $('#tipo_local option:selected').text() || empresaRegistroPayload?.tipo_local || '-',
    telefono_contacto: $('#telefono_contacto').val() || empresaRegistroPayload?.telefono_contacto || '-',
    whatsapp_contacto: $('#whatsapp_contacto').val() || empresaRegistroPayload?.whatsapp_contacto || '-',
    provincia: $('#provincia').val() || empresaRegistroPayload?.provincia || '-',
    canton: $('#canton').val() || empresaRegistroPayload?.canton || '-',
    calle_principal: $('#calle_principal').val() || empresaRegistroPayload?.calle_principal || '-',
    calle_secundaria: $('#calle_secundaria').val() || empresaRegistroPayload?.calle_secundaria || '-',
    bien_inmueble: $('#bien_inmueble').val() || empresaRegistroPayload?.bien_inmueble || '-',
    usuario: $('#username').val() || empresaRegistroPayload?.username || '-',
    correo: $('#email').val() || empresaRegistroPayload?.email || '-',
    razon_social: $('#razon_social').val() || empresaRegistroPayload?.razon_social || '-',
    celular: $('#celular').val() || empresaRegistroPayload?.celular || '-',
    tipo_identificacion: $('#tipo_identificacion option:selected').text() || empresaRegistroPayload?.tipo_identificacion || '-',
    cedula_ruc: $('#cedula_ruc').val() || empresaRegistroPayload?.cedula_ruc || '-',
    direccion: $('#direccion').val() || empresaRegistroPayload?.direccion || '-'
  };
}

function renderEmpresaWizardSummary() {
  const $summary = $('#empresaWizardResumen');
  if (!$summary.length) return;
  const d = getEmpresaWizardSummaryData();

  $summary.html(`
    <div class="empresa-wizard-summary-grid">
      <div class="empresa-wizard-summary-item is-wide">
        <span class="empresa-wizard-summary-label">Nombre de empresa</span>
        <div class="empresa-wizard-summary-value">${d.nombre}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Nombres del titular</span>
        <div class="empresa-wizard-summary-value">${d.nombres}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Apellidos del titular</span>
        <div class="empresa-wizard-summary-value">${d.apellidos}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Tipo de local</span>
        <div class="empresa-wizard-summary-value">${d.tipo_local}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Teléfono de contacto</span>
        <div class="empresa-wizard-summary-value">${d.telefono_contacto}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">WhatsApp</span>
        <div class="empresa-wizard-summary-value">${d.whatsapp_contacto}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Provincia / Cantón</span>
        <div class="empresa-wizard-summary-value">${d.provincia} / ${d.canton}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Calle Principal</span>
        <div class="empresa-wizard-summary-value">${d.calle_principal}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Calle Secundaria</span>
        <div class="empresa-wizard-summary-value">${d.calle_secundaria}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label"># Bien Inmueble</span>
        <div class="empresa-wizard-summary-value">${d.bien_inmueble}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Usuario</span>
        <div class="empresa-wizard-summary-value">${d.usuario}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Correo</span>
        <div class="empresa-wizard-summary-value">${d.correo}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Razón social</span>
        <div class="empresa-wizard-summary-value">${d.razon_social}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Celular / Teléfono</span>
        <div class="empresa-wizard-summary-value">${d.celular}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Tipo de identificación</span>
        <div class="empresa-wizard-summary-value">${d.tipo_identificacion}</div>
      </div>
      <div class="empresa-wizard-summary-item">
        <span class="empresa-wizard-summary-label">Cédula / RUC</span>
        <div class="empresa-wizard-summary-value">${d.cedula_ruc}</div>
      </div>
      <div class="empresa-wizard-summary-item is-wide">
        <span class="empresa-wizard-summary-label">Dirección</span>
        <div class="empresa-wizard-summary-value">${d.direccion}</div>
      </div>
    </div>
  `);
}

function updateEmpresaWizardStep(step) {
  const current = Math.min(Math.max(Number(step || 1), 1), 3);
  $('#staticBackdrop').attr('data-current-step', current);
  $('#staticBackdrop .empresa-wizard-section').removeClass('is-active').hide();
  $(`#staticBackdrop .empresa-wizard-section[data-step="${current}"]`).addClass('is-active').show();
  const body = document.querySelector('#staticBackdrop .empresa-wizard-body');
  if (body) body.scrollTop = 0;

  $('#staticBackdrop .empresa-wizard-step').each(function () {
    const stepNo = Number($(this).attr('data-step'));
    $(this).removeClass('is-active is-complete');
    if (stepNo < current) $(this).addClass('is-complete');
    if (stepNo === current) $(this).addClass('is-active');
  });

  $('#empresaWizardStepCounter').text(`Paso ${current} de 3`);
  $('#empresaWizardPrev').toggle(current > 1);
  $('#empresaWizardCancel').toggle(current === 1);
  $('#empresaWizardNext').toggle(current < 3);
  $('#empresaWizardSubmit').toggle(current === 3);

}

function initEmpresaWizard() {
  updateEmpresaWizardStep(1);
}

function clearEmpresaWizardValidation(step) {
  const selector = step ? `#staticBackdrop .empresa-wizard-section[data-step="${step}"] .is-invalid` : '#staticBackdrop .empresa-wizard-section .is-invalid';
  $(selector).removeClass('is-invalid');
  const errorSelector = step ? `#staticBackdrop .empresa-wizard-section[data-step="${step}"] .empresa-wizard-error` : '#staticBackdrop .empresa-wizard-section .empresa-wizard-error';
  $(errorSelector).remove();
}

function showEmpresaWizardFieldError(selector, message) {
  const $field = $(selector).first();
  if (!$field.length) {
    return false;
  }

  clearEmpresaWizardValidation();
  $field.addClass('is-invalid');
  $field.siblings('.empresa-wizard-error').remove();
  $field.closest('.input-group').siblings('.empresa-wizard-error').remove();

  const errorHtml = `<small class="empresa-wizard-error">${message}</small>`;
  if ($field.closest('.input-group').length) {
    $field.closest('.input-group').after(errorHtml);
  } else {
    $field.after(errorHtml);
  }

  const stepSection = $field.closest('.empresa-wizard-section');
  const body = document.querySelector('#staticBackdrop .empresa-wizard-body');
  if (stepSection.length) {
    const stepNo = Number(stepSection.attr('data-step') || 1);
    updateEmpresaWizardStep(stepNo);
  }

  if (body) {
    const fieldTop = $field[0].getBoundingClientRect().top;
    const bodyTop = body.getBoundingClientRect().top;
    body.scrollTop += Math.max(fieldTop - bodyTop - 120, 0);
  }

  setTimeout(() => {
    $field.trigger('focus');
  }, 80);
  return false;
}

function validarDisponibilidadRegistro(nombre, username) {
  return new Promise((resolve, reject) => {
    $.post('../api/v1/fulmuv/empresas/validar-registro', {
      nombre: nombre,
      username: username
    }, function (returnedData) {
      let returned = returnedData;
      if (typeof returnedData === 'string') {
        try {
          returned = JSON.parse(returnedData);
        } catch (e) {
          reject(new Error('No se pudo interpretar la validación del registro.'));
          return;
        }
      }
      resolve(returned || {});
    }).fail(function () {
      reject(new Error('No se pudo validar el nombre de empresa y el usuario.'));
    });
  });
}

function validateEmpresaWizardStep(step) {
  const current = Number(step || $('#staticBackdrop').attr('data-current-step') || 1);
  const esOneMuv = /onemuv/i.test(String(membresiaSeleccionadaActual?.nombre || ""));
  clearEmpresaWizardValidation(current);

  if (current === 1) {
    if (!esOneMuv && !$('#nombre').val().trim()) return showEmpresaWizardFieldError('#nombre', 'Debes completar el nombre de empresa.');
    if (!$('#nombre_titular').val().trim()) return showEmpresaWizardFieldError('#nombre_titular', 'Debes completar los nombres del titular.');
    if (!$('#apellido_titular').val().trim()) return showEmpresaWizardFieldError('#apellido_titular', 'Debes completar los apellidos del titular.');
    if (!esOneMuv && !$('#tipo_local').val()) return showEmpresaWizardFieldError('#tipo_local', 'Debes seleccionar el tipo de local.');
    if (!$('#telefono_contacto').val().trim()) return showEmpresaWizardFieldError('#telefono_contacto', 'Debes completar el teléfono de contacto.');
    if (!$('#whatsapp_contacto').val().trim()) return showEmpresaWizardFieldError('#whatsapp_contacto', 'Debes completar el WhatsApp de contacto.');
    if (!$('#provincia').val()) return showEmpresaWizardFieldError('#provincia', 'Debes seleccionar la provincia.');
    if (!$('#canton').val()) return showEmpresaWizardFieldError('#canton', 'Debes seleccionar el cantón.');
    if (!esOneMuv && !$('#calle_principal').val().trim()) return showEmpresaWizardFieldError('#calle_principal', 'Debes completar la calle principal.');
    if (!esOneMuv && !$('#calle_secundaria').val().trim()) return showEmpresaWizardFieldError('#calle_secundaria', 'Debes completar la calle secundaria.');
    if (!esOneMuv && !$('#bien_inmueble').val().trim()) return showEmpresaWizardFieldError('#bien_inmueble', 'Debes completar el bien inmueble.');
    if (!$('#direccion_mapa').val().trim()) return showEmpresaWizardFieldError('#direccion_mapa', 'Debes seleccionar la ubicación exacta en el mapa.');
    if (!latitud || !longitud) return showEmpresaWizardFieldError('#direccion_mapa', 'Debes guardar una ubicación válida con latitud y longitud.');
    return true;
  }

  if (current === 2) {
    if (!$('#username').val().trim()) return showEmpresaWizardFieldError('#username', 'Debes completar el usuario.');
    if (/\s/.test($('#username').val())) return showEmpresaWizardFieldError('#username', 'El nombre de usuario no puede contener espacios.');
    if (!$('#email').val().trim()) return showEmpresaWizardFieldError('#email', 'Debes completar el correo.');
    const email = $('#email').val().trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showEmpresaWizardFieldError('#email', 'Debes ingresar un correo válido.');
    if (!$('#password').val()) return showEmpresaWizardFieldError('#password', 'Debes completar la contraseña.');
    if (!$('#repeat_password').val()) return showEmpresaWizardFieldError('#repeat_password', 'Debes confirmar la contraseña.');
    if ($('#password').val() !== $('#repeat_password').val()) return showEmpresaWizardFieldError('#repeat_password', 'Las contraseñas no coinciden.');
    return true;
  }

  if (current === 3) {
    if (!$('#razon_social').val().trim()) return showEmpresaWizardFieldError('#razon_social', 'Debes completar el nombre o razón social.');
    if (!$('#celular').val().trim()) return showEmpresaWizardFieldError('#celular', 'Debes completar el celular o teléfono.');
    if (!$('#tipo_identificacion').val()) return showEmpresaWizardFieldError('#tipo_identificacion', 'Debes seleccionar el tipo de identificación.');
    if (!$('#cedula_ruc').val().trim()) return showEmpresaWizardFieldError('#cedula_ruc', 'Debes completar la cédula o RUC.');
    if (!esOneMuv && !$('#direccion').val().trim()) return showEmpresaWizardFieldError('#direccion', 'Debes completar la dirección.');
    if (!$('#chkLegales').is(':checked')) return showEmpresaWizardFieldError('#chkLegales', 'Debes aceptar los términos y condiciones legales.');
    if (!$('#chkEnvios').is(':checked')) return showEmpresaWizardFieldError('#chkEnvios', 'Debes aceptar los términos de envíos.');
    return true;
  }

  return true;
}

function mostrarFormulario() {
  const esOneMuv = /onemuv/i.test(String(membresiaSeleccionadaActual?.nombre || ""));
  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable empresa-wizard-modal" role="document">
        <div class="modal-content">
          <div class="empresa-wizard-header">
            <div class="empresa-wizard-brand">
              <div>
                <h4 id="staticBackdropLabel">FULMUV</h4>
                <p>Crear empresa · Registro de membresía</p>
              </div>
              <button class="empresa-wizard-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar">×</button>
            </div>
            <div class="empresa-wizard-stepper">
              <div class="empresa-wizard-step is-active" data-step="1"><span class="empresa-wizard-step-circle">1</span><span>Empresa</span></div>
              <div class="empresa-wizard-step" data-step="2"><span class="empresa-wizard-step-circle">2</span><span>Usuario</span></div>
              <div class="empresa-wizard-step" data-step="3"><span class="empresa-wizard-step-circle">3</span><span>Facturación y términos</span></div>
            </div>
          </div>

          <div class="empresa-wizard-body">
            <div class="empresa-wizard-section is-active" data-step="1">
              <h5 class="empresa-wizard-section-title">Información de la empresa</h5>
              <div class="row g-3">
                ${esOneMuv ? `<input type="hidden" id="nombre" value="">` : `<div class="col-12"><label class="form-label">Nombre de empresa</label><input class="form-control" id="nombre" type="text" placeholder="Nombre de empresa" oninput="this.value = this.value.toUpperCase()"></div>`}
                <div class="col-12 col-lg-6"><label class="form-label">Nombres del titular</label><input class="form-control" id="nombre_titular" type="text" placeholder="Nombres del titular" oninput="this.value = this.value.toUpperCase()"></div>
                <div class="col-12 col-lg-6"><label class="form-label">Apellidos del titular</label><input class="form-control" id="apellido_titular" type="text" placeholder="Apellidos del titular" oninput="this.value = this.value.toUpperCase()"></div>
                <div class="col-12 col-lg-6"><label class="form-label">Tipo de local ${esOneMuv ? '<small class="text-muted">(Opcional en OneMuv)</small>' : ''}</label><select class="form-select" id="tipo_local"><option value="">Seleccione tipo de local</option><option value="fisico">Físico</option><option value="online">Online</option></select></div>
                <div class="col-12 col-lg-6"><label class="form-label">Teléfono de contacto</label><input class="form-control" type="text" id="telefono_contacto" placeholder="0991234567"></div>
                <div class="col-12 col-lg-6"><label class="form-label">WhatsApp de contacto</label><input class="form-control" type="text" id="whatsapp_contacto" placeholder="0991234567"></div>
                <div class="col-12 col-lg-6"><label class="form-label">${esOneMuv ? 'Provincia de ubicación de producto/servicio/evento' : 'Provincia'}</label><select class="form-select" id="provincia" onchange="cargarCantones(this.value)"><option value="">Seleccione provincia</option><option value="Azuay">Azuay</option><option value="Bolívar">Bolívar</option><option value="Cañar">Cañar</option><option value="Carchi">Carchi</option><option value="Cotopaxi">Cotopaxi</option><option value="Chimborazo">Chimborazo</option><option value="El Oro">El Oro</option><option value="Esmeraldas">Esmeraldas</option><option value="Guayas">Guayas</option><option value="Imbabura">Imbabura</option><option value="Loja">Loja</option><option value="Los Ríos">Los Ríos</option><option value="Manabí">Manabí</option><option value="Morona Santiago">Morona Santiago</option><option value="Napo">Napo</option><option value="Pastaza">Pastaza</option><option value="Pichincha">Pichincha</option><option value="Tungurahua">Tungurahua</option><option value="Zamora Chinchipe">Zamora Chinchipe</option><option value="Galápagos">Galápagos</option><option value="Sucumbíos">Sucumbíos</option><option value="Orellana">Orellana</option><option value="Santo Domingo de los Tsáchilas">Santo Domingo de los Tsáchilas</option><option value="Santa Elena">Santa Elena</option></select></div>
                <div class="col-12 col-lg-6"><label class="form-label">${esOneMuv ? 'Cantón de ubicación de producto/servicio/evento' : 'Cantón'}</label><select class="form-select" id="canton"><option value="">Seleccione cantón</option></select></div>
                <div class="col-12 col-lg-6"><label class="form-label">Calle Principal ${esOneMuv ? '<small class="text-muted">(Opcional en OneMuv)</small>' : ''}</label><input class="form-control" type="text" id="calle_principal" placeholder="Calle principal"></div>
                <div class="col-12 col-lg-6"><label class="form-label"># Bien Inmueble ${esOneMuv ? '<small class="text-muted">(Opcional en OneMuv)</small>' : ''}</label><input class="form-control" type="text" id="bien_inmueble" placeholder="# Bien inmueble"></div>
                <div class="col-12 col-lg-6"><label class="form-label">Calle Secundaria ${esOneMuv ? '<small class="text-muted">(Opcional en OneMuv)</small>' : ''}</label><input class="form-control" type="text" id="calle_secundaria" placeholder="Calle secundaria"></div>
                <div class="col-12 col-lg-6">
                  <label class="form-label">Ubicación exacta</label>
                  <input class="form-control mb-2" type="text" id="direccion_mapa" placeholder="Selecciona la ubicación en el mapa" readonly>
                  <div class="empresa-wizard-map-row">
                    <button type="button" class="btn btn-danger rounded-pill px-3" onclick="abrirMapa()"><i class="fas fa-map-marker-alt me-1"></i>Abrir mapa</button>
                  </div>
                  <div class="empresa-wizard-map-note">Si tu negocio tiene sucursales, añádelas desde tu perfil de vendedor con sus ubicaciones y catálogos independientes.</div>
                </div>
                <input type="hidden" id="categoria_principal" value="">
                <input type="hidden" id="categoria" value="">
              </div>
            </div>

            <div class="empresa-wizard-section" data-step="2">
              <h5 class="empresa-wizard-section-title">Datos de acceso al sistema</h5>
              <div class="row g-3">
                <div class="col-12 col-lg-6"><label class="form-label">Usuario</label><input class="form-control" type="text" id="username" placeholder="Usuario"></div>
                <div class="col-12 col-lg-6"><label class="form-label">Correo</label><input class="form-control" type="text" id="email" placeholder="Correo"></div>
                <div class="col-12 col-lg-6">
                  <label class="form-label">Contraseña</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="password" placeholder="Contraseña" required>
                    <button class="btn d-block text-white" style="background-color:#004E60;color:#FFF" type="button" id="togglePassword1" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                  </div>
                </div>
                <div class="col-12 col-lg-6">
                  <label class="form-label">Confirmar contraseña</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="repeat_password" placeholder="Confirmar contraseña" required>
                    <button class="btn d-block text-white" style="background-color:#004E60;color:#FFF" type="button" id="togglePassword2" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                  </div>
                </div>
              </div>
            </div>

            <div class="empresa-wizard-section" data-step="3">
              <h5 class="empresa-wizard-section-title">Datos para facturación</h5>
              <div class="row g-3">
                <div class="col-12 col-lg-6"><label class="form-label">Nombre / Razón social</label><input class="form-control" type="text" id="razon_social" placeholder="Nombre / Razón social"></div>
                <div class="col-12 col-lg-6"><label class="form-label">Celular / Teléfono</label><input class="form-control" type="text" id="celular" placeholder="Celular / Teléfono"></div>
                <div class="col-12 col-lg-6"><label class="form-label">Tipo de identificación</label><select class="form-select" id="tipo_identificacion"><option value="cedula">Cédula</option><option value="ruc">Ruc</option></select></div>
                <div class="col-12 col-lg-6"><label class="form-label">Cédula / RUC</label><input class="form-control" type="text" id="cedula_ruc" placeholder="Cédula / RUC"></div>
                <div class="col-12"><label class="form-label">Dirección ${esOneMuv ? '<small class="text-muted">(Opcional en OneMuv)</small>' : ''}</label><input class="form-control" type="text" id="direccion" placeholder="Dirección de facturación"></div>
                <div class="col-12 pt-2">
                  <h6 class="empresa-wizard-consent-title">Términos y condiciones</h6>
                </div>
                <div class="col-12">
                  <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="chkLegales" required>
                  <label class="form-check-label" for="chkLegales">
                    Declaro que he leído y acepto los
                    <a href="terminos_condiciones_proveedores.php" target="_blank" rel="noopener" class="link-primary">Términos y Condiciones para Proveedores</a>,
                    la <a href="politica_privacidad_cookies.php" target="_blank" rel="noopener" class="link-primary">Política de Privacidad y Cookies</a>
                    y el <a href="aviso_legal.php" target="_blank" rel="noopener" class="link-primary">Aviso Legal</a> de FULMUV.
                  </label>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-check mt-1">
                  <input class="form-check-input" type="checkbox" value="" id="chkEnvios" required>
                  <label class="form-check-label" for="chkEnvios">
                    Acepto las <strong>Condiciones de Uso del Servicio de Envíos y Logística FULMUV – GRUPO ENTREGAS</strong> y comprendo mis responsabilidades sobre embalaje, valor comercial y respaldo de envío.
                  </label>
                </div>
                </div>
                <div class="col-12">
                  <div id="consentAlert" class="form-text text-danger d-none mt-2">Debes aceptar ambos consentimientos para continuar.</div>
                  <div class="empresa-wizard-map-note mt-2">Al continuar, se validará tu información y te llevaremos al pago seguro de la membresía seleccionada.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="empresa-wizard-footer">
            <div class="empresa-wizard-footer-actions">
              <button type="button" class="empresa-wizard-btn-outline" id="empresaWizardCancel" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="empresa-wizard-btn-outline" id="empresaWizardPrev" style="display:none;">Anterior</button>
            </div>
            <div class="empresa-wizard-step-counter" id="empresaWizardStepCounter">Paso 1 de 3</div>
            <div class="empresa-wizard-footer-actions">
              <button type="button" class="empresa-wizard-btn-primary" id="empresaWizardNext">Siguiente →</button>
              <button type="button" class="empresa-wizard-btn-primary" id="empresaWizardSubmit" style="display:none;" onclick="saveEmpresa()">Ir al pago →</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  // Mostrar modal
  $("#btnModal").click();
  setTimeout(() => {
    hydrateFormularioDesdePayload();
    saveCheckoutDraft('form');
    initEmpresaWizard();
  }, 120);
}

async function saveEmpresa() {
  if (!validateEmpresaWizardStep(3)) {
    return;
  }

  var nombre = $("#nombre").val();
  var nombre_titular = $("#nombre_titular").val();
  var apellido_titular = $("#apellido_titular").val();
  var direccion = $("#direccion").val();
  var direccion_mapa = $("#direccion_mapa").val();
  var username = $('#username').val().replace(/\s+/g, '');
  $('#username').val(username);
  var email = $('#email').val();
  var password = $('#password').val();
  var repeat_password = $('#repeat_password').val();
  var telefono_contacto = $('#telefono_contacto').val();
  var whatsapp_contacto = $('#whatsapp_contacto').val();
  var tipo_local = $('#tipo_local').val();
  // var agente = $('#agente').val();
  var categoria_principal = '';
  var categorias_referencia = '';
  var provincia = $('#provincia').val();
  var canton = $('#canton').val();
  var calle_principal = $('#calle_principal').val();
  var calle_secundaria = $('#calle_secundaria').val();
  var bien_inmueble = $('#bien_inmueble').val();
  var razon_social = $('#razon_social').val();
  var celular = $('#celular').val();
  var tipo_identificacion = $('#tipo_identificacion').val();
  var cedula_ruc = $('#cedula_ruc').val();
  const esOneMuv = /onemuv/i.test(String(membresiaSeleccionadaActual?.nombre || ""));

  var sucursales = 'N';
  var $sucCheck = $('#suc_fulmuv, [id^="suc_"]').filter(':checkbox'); // soporta id dinámico como suc_{nombreKey}
  if ($sucCheck.length) {
    // si hay varios, toma el primero visible; si ninguno visible, usa el primero
    var $target = $sucCheck.filter(':visible').first();
    if ($target.length === 0) $target = $sucCheck.first();
    sucursales = $target.is(':checked') ? 'Y' : 'N';
  }

  username_guardado = username;
  password_guardado = password;

  if (esOneMuv) {
    nombre = (nombre || `${nombre_titular} ${apellido_titular}`.trim()).trim();
  }

  if ((!esOneMuv && nombre == "") || nombre_titular == "" || !username || !email || !password || !repeat_password || password !== repeat_password || provincia == "" || canton == "" || razon_social == "" || celular == "" || cedula_ruc == "" || !$('#chkLegales').is(':checked') || !$('#chkEnvios').is(':checked')) {
    return;
  } else {
    try {
      const disponibilidad = await validarDisponibilidadRegistro(nombre, username);
      if (disponibilidad.empresa_existe) {
        showEmpresaWizardFieldError(esOneMuv ? '#nombre_titular' : '#nombre', 'El nombre de la empresa ya se encuentra registrado.');
        return;
      }
      if (disponibilidad.usuario_existe) {
        showEmpresaWizardFieldError('#username', 'El nombre de usuario ya se encuentra registrado. Elige otro.');
        return;
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message || "No se pudo validar la información antes del pago."
      });
      return;
    }

    empresaRegistroPayload = {
      nombre: nombre,
      nombre_titular: `${nombre_titular} ${apellido_titular}`.trim(),
      nombre_titular_nombres: nombre_titular,
      apellido_titular: apellido_titular,
      direccion: direccion_mapa,
      direccion_facturacion: direccion,
      direccion_mapa: direccion_mapa,
      ubicacion_exacta: direccion_mapa,
      latitud: latitud,
      longitud: longitud,
      tipo_local: tipo_local,
      telefono_contacto: telefono_contacto,
      whatsapp_contacto: whatsapp_contacto,
      username: username,
      email: email,
      password: password,
      provincia: provincia,
      canton: canton,
      calle_principal: calle_principal,
      calle_secundaria: calle_secundaria,
      bien_inmueble: bien_inmueble,
      razon_social: razon_social,
      celular: celular,
      tipo_identificacion: tipo_identificacion,
      cedula_ruc: cedula_ruc,
      sucursales: sucursales
    };
    id_empresa_devuelto = null;
    id_usuario_devuelto = null;
    saveCheckoutDraft('payment');
    $("#staticBackdrop").modal('hide');
    tokenizarTarjeta(email)

  }
}

function crearEmpresaAntesDePago() {
  if (id_empresa_devuelto && id_usuario_devuelto) {
    return Promise.resolve({
      id_empresa: id_empresa_devuelto,
      id_usuario: id_usuario_devuelto
    });
  }

  if (!empresaRegistroPayload) {
    return Promise.reject(new Error('No hay datos de empresa preparados para el pago.'));
  }

  return new Promise((resolve, reject) => {
    $.post('../api/v1/fulmuv/empresas/create', {
      nombre: empresaRegistroPayload.nombre,
      nombre_titular: empresaRegistroPayload.nombre_titular,
      direccion: empresaRegistroPayload.direccion,
      direccion_facturacion: empresaRegistroPayload.direccion_facturacion,
      latitud: empresaRegistroPayload.latitud,
      longitud: empresaRegistroPayload.longitud,
      tipo_local: empresaRegistroPayload.tipo_local,
      telefono_contacto: empresaRegistroPayload.telefono_contacto,
      whatsapp_contacto: empresaRegistroPayload.whatsapp_contacto,
      username: empresaRegistroPayload.username,
      email: empresaRegistroPayload.email,
      password: empresaRegistroPayload.password,
      categorias_referencia: '',
      provincia: empresaRegistroPayload.provincia,
      canton: empresaRegistroPayload.canton,
      calle_principal: empresaRegistroPayload.calle_principal,
      calle_secundaria: empresaRegistroPayload.calle_secundaria,
      bien_inmueble: empresaRegistroPayload.bien_inmueble,
      razon_social: empresaRegistroPayload.razon_social,
      celular: empresaRegistroPayload.celular,
      tipo_identificacion: empresaRegistroPayload.tipo_identificacion,
      cedula_ruc: empresaRegistroPayload.cedula_ruc,
      sucursales: empresaRegistroPayload.sucursales
    }, function (returnedData) {
      let returned = returnedData;
      if (typeof returnedData === 'string') {
        try {
          returned = JSON.parse(returnedData);
        } catch (e) {
          reject(new Error('No se pudo interpretar la respuesta de creación de empresa.'));
          return;
        }
      }

      if (returned && returned.error === false) {
        id_empresa_devuelto = returned.id_empresa;
        id_usuario_devuelto = returned.id_usuario;
        resolve(returned);
        return;
      }

      reject(new Error(returned?.msg || 'No se pudo crear la empresa antes del pago.'));
    });
  });
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    console.log(files)
    if (files == undefined) {
      resolve(); // Resuelve la promesa incluso si no hay imágenes
    } else {
      const formData = new FormData();
      formData.append(`archivos[]`, files); // añadir los archivos al form
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
  $('#staticBackdrop').modal('hide');
  $("#modal-pago").modal('show')

  console.log(id_membresia_seleccionada)
  renderCheckoutSummary();
  saveCheckoutDraft('payment');
  // Configura UI de crédito según el plan elegido:
  configurarUICreditoSegunMembresia(id_membresia_seleccionada);
  setTimeout(() => {
    if ($('#selectTipoDiferido').length && tipo_pago) {
      $('#selectTipoDiferido').val(tipo_pago);
      onTipoChange(tipo_pago);
    }
    if ($('#selectMeses').length && meses_pago) {
      $('#selectMeses').val(String(meses_pago));
      onMesesChange(meses_pago);
    }
  }, 120);

  // === Variable to use ===
  let environment = 'stg';
  let application_code = 'TESTECUADORSTG-EC-CLIENT'; // Provided by Payment Gateway
  let application_key = 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6'; // Provided by Payment Gateway
  let submitButton = document.querySelector('#tokenize_btn');
  submitButton.innerText = "Pagar";
  let submitInitialText = submitButton.textContent;
  submitButton.removeAttribute('disabled');
  submitButton.style.display = 'block';
  document.getElementById('tokenize_response').innerHTML = '';
  document.getElementById('tokenize_example').innerHTML = '';

  const resetPaymentButton = () => {
    submitButton.innerText = submitInitialText;
    submitButton.removeAttribute('disabled');
    submitButton.style.display = 'block';
  };


  // Get the required additional data to tokenize card

  let get_tokenize_data = () => {
    const gatewayUserId = String((empresaRegistroPayload?.email || email || id_empresa_devuelto || 'fulmuv-checkout')).trim().toLowerCase();
    let data = {
      locale: 'es',
      user: {
        id: gatewayUserId,
        email: empresaRegistroPayload?.email || email,
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
    resetPaymentButton();
  }

  const getGatewayErrorMessage = (response) => {
    const parts = [];
    const cardStatus = String(response?.card?.status || response?.transaction?.status || response?.transaction?.current_status || '').trim();
    const cardMessage = String(
      response?.card?.message
      || response?.card?.status_detail
      || response?.transaction?.status_detail
      || response?.transaction?.message
      || ''
    ).trim();
    const errorType = String(response?.error?.type || '').trim();
    const errorHelp = String(response?.error?.help || response?.error?.description || '').trim();
    const carrierCode = String(response?.transaction?.carrier_code || response?.transaction?.authorization_code || response?.error?.code || '').trim();
    const reference = String(response?.transaction?.id || response?.card?.transaction_reference || '').trim();

    if (cardStatus) parts.push(`Estado Nuvei: ${cardStatus}`);
    if (cardMessage) parts.push(`Detalle Nuvei: ${cardMessage}`);
    if (carrierCode) parts.push(`Respuesta banco: ${carrierCode}`);
    if (errorType) parts.push(`Tipo: ${errorType}`);
    if (errorHelp) parts.push(`Descripción: ${errorHelp}`);
    if (reference) parts.push(`Referencia: ${reference}`);

    return parts.length
      ? parts.join('\n')
      : 'Nuvei rechazo la tarjeta y no devolvio un detalle adicional.';
  };

  // Executed when was called 'tokenize' and the services response successfully.
  let responseCallback = response => {

    if (response.card) {

      // registrar el token en la bd
      if (response.card.status == "valid") {
        console.log(response)

        // Extraer metadatos de la tarjeta
        const rawNum    = String(response.card.number || '');
        const cardMeta  = {
          ultimos_digitos: rawNum.replace(/\D/g, '').slice(-4) || null,
          marca:           _normalizeBrandCrear(response.card.type || ''),
          exp_year:        response.card.expiry_year  || null,
          exp_month:       response.card.expiry_month || null,
        };

        completarRegistroEmpresaConCobro(response.card.token, response.card.transaction_reference, cardMeta).then(function () {
          submitButton.style.display = 'none';
          $("#modal-pago").modal('hide');
          resetCheckoutState({ keepDraftHandled: true });
          swal({
            title: "!Pago registrado con éxito!",
            text: "Haz clic en OK para acceder al sistema. \n  Bienvenido a FULMUV",
            icon: "success",
            button: "OK",
          }, function () {
            redirigirConPost("true", username_guardado)
          });
        }).catch(function (error) {
          console.error(error);
          SweetAlert("error", error.message || "No se pudo completar el registro y el cobro.");
          resetPaymentButton();
        });
      } else {
        const rejectionDetail = getGatewayErrorMessage(response);
        console.error('Nuvei card rejected', response);
        document.getElementById('tokenize_response').innerHTML = `<div class="text-danger small">${rejectionDetail.replace(/\n/g, '<br>')}</div>`;
        SweetAlert("error", rejectionDetail);
        resetPaymentButton();

      }

    } else if (response.error) {

      // la tarjeta ya existe
      if (response.error.type.includes("Card already added")) {
        // intentar el cobro con token

        console.log(response)

        // Paymentez incluye response.card con los datos del card incluso en este error
        // El token está disponible directamente en response.card.token (alphanumerico)
        var cardMetaExistente = {};
        var token = null;

        if (response.card) {
          token = response.card.token || null;
          var rawNumEx = String(response.card.number || '');
          cardMetaExistente = {
            ultimos_digitos: rawNumEx.replace(/\D/g, '').slice(-4) || null,
            marca:           _normalizeBrandCrear(response.card.type || ''),
            exp_year:        response.card.expiry_year  || null,
            exp_month:       response.card.expiry_month || null,
          };
        }

        // Fallback: extraer token del mensaje de error si no vino en response.card
        if (!token) {
          var matchToken = response.error.type.match(/Card already added[^:]*:\s*(\S+)/i);
          token = matchToken ? matchToken[1].trim() : null;
        }

        completarRegistroEmpresaConCobro(token, null, cardMetaExistente).then(function () {
          submitButton.style.display = 'none';
          $("#modal-pago").modal('hide');
          resetCheckoutState({ keepDraftHandled: true });
          swal({
            title: "!Pago registrado con éxito!",
            text: "Haz clic en OK para acceder al sistema. \n  Bienvenido a FULMUV",
            icon: "success",
            button: "OK",
          }, function () {
            redirigirConPost("true", username_guardado)
          });
        }).catch(function (error) {
          console.error(error);
          SweetAlert("error", error.message || "No se pudo completar el registro y el cobro.");
          resetPaymentButton();
        });


      } else {//manejo de algun otro error
        const gatewayMessage = getGatewayErrorMessage(response);
        console.error('Nuvei tokenize error', response);
        document.getElementById('tokenize_response').innerHTML = `<div class="text-danger small">${gatewayMessage.replace(/\n/g, '<br>')}</div>`;
        SweetAlert("error", gatewayMessage);
        resetPaymentButton();
      }
    }

  }


  // 2. Instance the [PaymentGateway](#PaymentGateway-class) with the required parameters.
  let pg_sdk = new PaymentGateway(environment, application_code, application_key);

  // 3. Generate the tokenization form with the required data. [generate_tokenize](#generate_tokenize-function)
  // At this point it's when the form is rendered on page.
  pg_sdk.generate_tokenize(get_tokenize_data(), '#tokenize_example', responseCallback, notCompletedFormCallback);

  // 4. Define the event to execute the [tokenize](#tokenize-function) action.
  submitButton.onclick = function (event) {
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
    submitButton.innerText = 'Preparando pago...';
    submitButton.setAttribute('disabled', 'disabled');
    event.preventDefault();

    submitButton.innerText = 'Procesando pago...';
    saveCheckoutDraft('payment');
    pg_sdk.tokenize();
  };

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

function completarRegistroEmpresaConCobro(token, transaction_reference = null, cardMeta = {}) {
  const tipoSeleccionado = (typeof tipo_pago !== 'undefined') ? tipo_pago : 'corriente';
  const mesesSeleccionados = (typeof meses_pago !== 'undefined') ? meses_pago : 0;
  const tipo_code = __mapTipoPagoCode(tipoSeleccionado);
  const meses_send = __normalizarMeses(tipoSeleccionado, mesesSeleccionados);
  const montoPrueba = 1.00;
  const gatewayUidDefault = String((empresaRegistroPayload?.email || empresaRegistroPayload?.username || 'wallet-temp')).trim().toLowerCase();
  const cardMetaPayload = {
    ultimos_digitos: String(cardMeta.ultimos_digitos || '').replace(/\D/g, '').slice(-4) || '0000',
    marca: String(cardMeta.marca || '').trim() || 'Tarjeta',
    exp_year: String(cardMeta.exp_year || '').trim() || String(new Date().getFullYear()),
    exp_month: String(cardMeta.exp_month || '').trim() || String(new Date().getMonth() + 1).padStart(2, '0'),
    es_default: String(cardMeta.es_default || '').trim() || 'Y',
    gateway_uid: String(cardMeta.gateway_uid || '').trim() || gatewayUidDefault
  };

  return new Promise((resolve, reject) => {
    if (!empresaRegistroPayload) {
      reject(new Error('No hay datos de empresa preparados para completar el registro.'));
      return;
    }

    $.post('../api/v1/fulmuv/empresas/create', {
      nombre: empresaRegistroPayload.nombre,
      nombre_titular: empresaRegistroPayload.nombre_titular,
      direccion: empresaRegistroPayload.direccion,
      direccion_facturacion: empresaRegistroPayload.direccion_facturacion,
      latitud: empresaRegistroPayload.latitud,
      longitud: empresaRegistroPayload.longitud,
      tipo_local: empresaRegistroPayload.tipo_local,
      telefono_contacto: empresaRegistroPayload.telefono_contacto,
      whatsapp_contacto: empresaRegistroPayload.whatsapp_contacto,
      username: empresaRegistroPayload.username,
      email: empresaRegistroPayload.email,
      password: empresaRegistroPayload.password,
      categorias_referencia: '',
      provincia: empresaRegistroPayload.provincia,
      canton: empresaRegistroPayload.canton,
      calle_principal: empresaRegistroPayload.calle_principal,
      calle_secundaria: empresaRegistroPayload.calle_secundaria,
      bien_inmueble: empresaRegistroPayload.bien_inmueble,
      razon_social: empresaRegistroPayload.razon_social,
      celular: empresaRegistroPayload.celular,
      tipo_identificacion: empresaRegistroPayload.tipo_identificacion,
      cedula_ruc: empresaRegistroPayload.cedula_ruc,
      sucursales: empresaRegistroPayload.sucursales,
      id_membresia: id_membresia_seleccionada,
      pago_valor: montoPrueba,
      tipo: "empresa",
      recurrente: "Y",
      valor_membresia: montoPrueba,
      promo_resumen: promoResumenActual ? JSON.stringify(promoResumenActual) : "",
      token: token,
      transaction_reference: transaction_reference,
      tipo_pago: tipo_code,
      meses: meses_send,
      ultimos_digitos: cardMetaPayload.ultimos_digitos,
      marca:           cardMetaPayload.marca,
      exp_year:        cardMetaPayload.exp_year,
      exp_month:       cardMetaPayload.exp_month,
      es_default:      cardMetaPayload.es_default,
      gateway_uid:     cardMetaPayload.gateway_uid,
    }, function (returnedData) {
      let returned = returnedData;
      if (typeof returnedData === 'string') {
        try {
          returned = JSON.parse(returnedData);
        } catch (e) {
          reject(new Error('No se pudo interpretar la respuesta del registro con pago.'));
          return;
        }
      }

      if (returned && returned.error === false) {
        id_empresa_devuelto = returned.id_empresa || null;
        id_usuario_devuelto = returned.id_usuario || null;
        resolve(returned);
        return;
      }

      reject(new Error(returned?.msg || 'No se pudo completar el registro de la empresa.'));
    }, 'json');
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
    username: username_guardado,
    password: password_guardado,
    pago_valor: valor_pagado,
    tipo: "empresa",
    transaction_id: transaction_id,
    authorization_code: authorization_code,
    recurrente: recurrente,
    payment_date: payment_date,
    valor_membresia: costo_seleccionado,
    promo_resumen: promoResumenActual ? JSON.stringify(promoResumenActual) : ""
  }, function (returnedData) {
    if (!returnedData.error) {
      resetCheckoutState({ keepDraftHandled: true });
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

  const fulmuvPlanes = groupedMembresias['fulmuv'] || [];
  const fulmuvNombre = fulmuvPlanes[0]?.nombre || 'FULMUV';
  const fulmuvContext = getContextFromCard({
    nombreKey: 'fulmuv',
    nombre: fulmuvNombre,
    selectId: 'select_fulmuv',
    precioId: 'precio_fulmuv',
    sucursalCheckId: 'suc_fulmuv'
  });
  const evaluacionInicial = evaluatePromotionForContext(fulmuvContext, agente, codigo);

  if (!evaluacionInicial.applies) {
    return Swal.fire({
      icon: "error",
      title: "Error",
      text: evaluacionInicial.invalidMessage || "Este código no aplica al plan seleccionado."
    });
  }

  agenteAplicado = agente;
  codigoAplicado = codigo;
  promoConfig = evaluacionInicial;

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

  if (checkoutSelectionState) {
    const selectedContext = getContextByMembresiaId(id_membresia_seleccionada);
    const selectedEval = evaluatePromotionForContext(selectedContext);
    checkoutSelectionState = {
      ...(selectedContext || {}),
      regularPrice: selectedEval.originalPrice,
      promotion: selectedEval
    };
    costo_seleccionado = Number(selectedEval.promotionalPrice || selectedEval.originalPrice || 0).toFixed(2);
    valor_pagado = Number(selectedEval.amountToday || 0);
    promoResumenActual = buildResumenPromocion(selectedContext, selectedEval);
    $("#totalPago").text(formatCurrencyValue(valor_pagado));
    renderCheckoutSummary();
  }

  Swal.fire({ icon:"success", title:"Listo", text: evaluacionInicial.promotionMessage || "Código aplicado correctamente." });
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
    obtenerDireccionDesdeCoords(latitud, longitud, () => {
      saveCheckoutDraft('form');
      $("#modalMapa").modal("hide");
    });
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
    if (latitud && longitud && marker && map) {
      const pos = { lat: parseFloat(latitud), lng: parseFloat(longitud) };
      marker.setPosition(pos);
      map.setCenter(pos);
      map.setZoom(16);
    }
    if ($('#direccion_mapa').val()) {
      $('#buscarDireccion').val($('#direccion_mapa').val());
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
  ayudaTipo.textContent = 'Selecciona â€œDiferido" si deseas pagar en cuotas.';
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

  this.setAttribute('aria-label', showing ? 'Mostrar contraseÃ±a' : 'Ocultar contraseÃ±a');
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
  const context = getContextFromCard({ nombreKey, nombre, selectId, precioId, sucursalCheckId });
  const evaluation = evaluatePromotionForContext(context);
  const badgeContainerId = `badge_${nombreKey}`;
  const promoDetailId = `promo_detalle_${nombreKey}`;

  $(`#${precioId}`).text(Number(evaluation.displayPrice || 0).toFixed(0));
  $(`#periodo_${nombreKey}`).text(evaluation.displayPeriodText || `/ ${diasToText(context.dias)}`);
  $(`#${badgeContainerId}`).html(evaluation.badgeHtml || '');
  $(`#${promoDetailId}`).html(evaluation.detailHtml || '');

  if (checkoutSelectionState && String(checkoutSelectionState.plan?.id_membresia || '') === String(context.plan?.id_membresia || '')) {
    checkoutSelectionState = {
      ...(context || {}),
      regularPrice: evaluation.originalPrice,
      promotion: evaluation
    };
    costo_seleccionado = Number(evaluation.promotionalPrice || evaluation.originalPrice || 0).toFixed(2);
    valor_pagado = Number(evaluation.amountToday || 0);
    promoResumenActual = buildResumenPromocion(context, evaluation);
    $("#totalPago").text(formatCurrencyValue(valor_pagado));
    renderCheckoutSummary();
  }
}


function getPrecioFinal({ nombre, dias, conSucursal, base }) {
  const n = (nombre || '').toLowerCase();
  const d = String(dias);

  const esFulmuv = n.includes('fulmuv');
  const esOne = n.includes('onemuv');
  const esBasic = n.includes('basicmuv');

  // Tabla â€œnormal" (sin agente) para FulMuv con/sin sucursal
  const fulmuvTabla = {
    con: { '30': 31, '180': 165, '365': 297 },
    sin: { '30': 25, '180': 127, '365': 237 }
  };

  // Tabla â€œgeneral" (cuando el agente es tipo general) para todos los planes
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
      // precio â€œreal" mostrado en UI (renovación)
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
