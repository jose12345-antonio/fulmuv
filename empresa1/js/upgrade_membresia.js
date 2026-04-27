/**
 * VARIABLES GLOBALES Y ESTADO
 */
var id_empresa = document.getElementById("id_empresa_detalle")?.value;
var id_usuario = document.getElementById("id_principal")?.value;
let membresiasData = [];
let agentes = [];
let id_membresia_seleccionada = null;
let costo_seleccionado = null;
let tokenSeleccionado = null;
let groupedMembresias = {};

$(document).ready(function () {
  // Carga inicial de membresías del catálogo
  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      membresiasData = returned.data;
      renderMembresiasSelect();
    }
  });

  $.get('../api/v1/fulmuv/agentes/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) agentes = returned.data;
  });
});

/**
 * FUNCIONES DE APOYO
 */
function diasToText(dias) {
  if (String(dias) === "30") return "mensual";
  if (String(dias) === "180") return "semestral";
  return "anual";
}

function isAnnual(dias) {
  return String(dias) === '360' || String(dias) === '365';
}

function badgeFor(nombre, dias) {
  if (!isAnnual(dias)) return '';
  const text = /fulmuv/i.test(nombre) ? 'Ahorra $81 +' : 'Ahorra $11 +';
  return `<span class="badge bg-success rounded-pill mt-1">${text}</span>`;
}

function getPrecioVisible(precioId) {
  const raw = ($(`#${precioId}`).text() || "").trim();
  return Number(raw.replace(/[^\d.]/g, '')) || 0;
}

function groupByNombre() {
  groupedMembresias = {};
  (membresiasData || []).forEach(m => {
    const key = (m.nombre || '').toLowerCase();
    if (!groupedMembresias[key]) groupedMembresias[key] = [];
    groupedMembresias[key].push(m);
  });

  const order = ["30", "180", "360", "365"];
  Object.keys(groupedMembresias).forEach(k => {
    groupedMembresias[k].sort((a, b) =>
      order.indexOf(String(a.dias_permitidos)) - order.indexOf(String(b.dias_permitidos))
    );
  });
}

/**
 * RENDERIZADO DE INTERFAZ
 */
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
    const sucursalCheckId = `suc_${nombreKey}`;
    const sucursalWrapId = `suc_wrap_${nombreKey}`;
    const badgeContainerId = `badge_${nombreKey}`;

    const options = planes.map(p => {
      const dias = String(p.dias_permitidos);
      const sel = (p.id_membresia == defaultPlan.id_membresia) ? 'selected' : '';
      return `<option value="${dias}" ${sel}>${diasToText(dias)}</option>`;
    }).join('');

    const items = buildItems(key);
    const parts = splitItems(items, 4);
    const contentId = `collapse_${nombreKey}`;

    const cardHtml = `
            <div class="col-md-4 col-sm-12 mb-3 d-flex">
                <div class="border rounded-3 overflow-hidden flex-fill d-flex flex-column h-100 shadow-sm bg-white text-center">
                    <div class="p-4">
                        <h4 class="fw-bold text-primary mb-0">${nombre}</h4>
                        <div class="mt-2">
                            <select id="${selectId}" data-group="${key}" class="form-select form-select-sm w-auto d-inline-block">
                                ${options}
                            </select>
                            ${nombre.toLowerCase().includes('fulmuv') ? `
                            <div id="${sucursalWrapId}" class="form-check mt-2" style="${isAnnual(defaultPlan.dias_permitidos) ? '' : 'display:none;'}">
                                <input class="form-check-input" type="checkbox" id="${sucursalCheckId}">
                                <label class="form-check-label small fw-bold" for="${sucursalCheckId}">Tengo sucursales</label>
                            </div>` : ''}
                        </div>
                        <h2 class="fw-light text-primary mt-2">
                            <sup class="fs-8">$</sup><span id="${precioId}" class="fs-6">${defaultPlan.costo}</span>
                            <span id="${periodoId}" class="fs-9 mt-1">/ ${diasToText(defaultPlan.dias_permitidos)}</span>
                        </h2>
                        <div id="${badgeContainerId}">${badgeFor(nombre, defaultPlan.dias_permitidos)}</div>
                    </div>
                    <div class="p-4 pt-0 flex-grow-1 bg-light text-start">
                        <ul class="list-unstyled mb-0 small text-muted">${parts.preview}</ul>
                        ${parts.hasMore ? `
                        <div id="${contentId}" class="collapse mt-2"><ul class="list-unstyled mb-0 small text-muted">${parts.rest}</ul></div>
                        <div class="text-center mt-2">
                            <button class="btn btn-link p-0 text-decoration-none small" type="button" data-bs-toggle="collapse" data-bs-target="#${contentId}" 
                                onclick="$(this).text($(this).text() == 'Leer más' ? 'Leer menos' : 'Leer más')">Leer más</button>
                        </div>` : ''}
                    </div>
                    <div class="p-4 pt-0 bg-light">
                        <button id="${btnId}" class="btn btn-primary w-100 fw-bold">Comprar</button>
                    </div>
                </div>
            </div>`;

    cont.append(cardHtml);
    $(`#${precioId}`).data('base', Number(defaultPlan.costo));

    $(`#${selectId}`).on('change', function () {
      const diasSel = String($(this).val());
      const plan = (groupedMembresias[$(this).data('group')] || []).find(p => String(p.dias_permitidos) === diasSel);
      if (!plan) return;

      $(`#${precioId}`).data('base', Number(plan.costo)).text(plan.costo);
      $(`#${periodoId}`).text(`/ ${diasToText(diasSel)}`);
      $(`#${badgeContainerId}`).html(badgeFor(nombre, diasSel));

      if (nombre.toLowerCase().includes('fulmuv')) {
        if (isAnnual(diasSel)) $(`#${sucursalWrapId}`).fadeIn();
        else { $(`#${sucursalWrapId}`).fadeOut(); $(`#${sucursalCheckId}`).prop('checked', false); }
      }
      $(`#${sucursalCheckId}`).trigger('change');
    });

    $(`#${sucursalCheckId}`).on('change', function () {
      const checked = $(this).is(':checked');
      const dias = $(`#${selectId}`).val();
      const base = $(`#${precioId}`).data('base');
      let final = base;

      if (checked) {
        if (dias === '365' || dias === '360') final = 317;
        else if (dias === '180') final = 177;
        else if (dias === '30') final = 35;
      }
      $(`#${precioId}`).text(final);
    });

    $(`#${btnId}`).on('click', function () {
      costo_seleccionado = getPrecioVisible(precioId);
      const diasSel = $(`#${selectId}`).val();
      const tieneSucChecked = nombre.toLowerCase().includes('fulmuv') && $(`#${sucursalCheckId}`).is(':checked');
      const planFinal = (groupedMembresias[key] || []).find(p => String(p.dias_permitidos) === diasSel);
      saveMembresia(planFinal.id_membresia, costo_seleccionado, diasSel, tieneSucChecked);
    });
  });
}

/**
 * LÓGICA DE UPGRADE (Evita NaN)
 */
function saveMembresia(id_membresia_nueva, costo_nuevo, dias_nuevos, tiene_sucursales) {
  $.get(`../api/v1/fulmuv/empresa/membresia_actual/${id_empresa}`, function (res) {
    let response = JSON.parse(res);
    if (response.error) return;

    let actual = response.data;
    // Validación anti-NaN obligatoria
    let valorPagadoActual = parseFloat(actual.valor_membresia) || 0;
    let diasTotalesActual = parseInt(actual.dias_base) || 30;
    let costoNuevoNum = parseFloat(costo_nuevo) || 0;

    let fechaFin = new Date(actual.fecha_fin);
    let hoy = new Date();
    let diasRestantes = Math.ceil((fechaFin - hoy) / (1000 * 60 * 60 * 24));
    if (diasRestantes < 0) diasRestantes = 0;

    let creditoAFavor = 0;
    if (costoNuevoNum >= valorPagadoActual && diasRestantes > 0) {
      // Cálculo proporcional de upgrade
      creditoAFavor = (valorPagadoActual / diasTotalesActual) * diasRestantes;
    }
    let valorFinal = (costoNuevoNum - creditoAFavor).toFixed(2);

    $('#detallePagoUpgrade').html(`
            <div class="mb-1 small"><strong>Actual:</strong> ${actual.nombre} ($${valorPagadoActual})</div>
            <div class="mb-1 small"><strong>Días restantes:</strong> ${diasRestantes} días</div>
        `);

    $('#subtotalModal').text(`$${costoNuevoNum.toFixed(2)}`);
    $('#creditoModal').text(`-$${creditoAFavor.toFixed(2)}`);
    $('#totalModal').text(`$${valorFinal}`);

    // CARGAR TARJETAS DESDE NUVEI USANDO LOS TOKENS
    cargarTarjetasUpgrade(id_membresia_nueva, valorFinal, dias_nuevos, tiene_sucursales);
    $('#modalUpgradePago').modal('show');
  });
}

/**
 * CARGA DE TARJETAS DESDE API NUVEI (Usa los tokens obtenidos)
 */
function cargarTarjetasUpgrade(id_membresia, valor_final, dias, sucursales) {
  const contenedor = $('#listaTarjetas');
  contenedor.html('<div class="text-center p-3"><span class="spinner-border spinner-border-sm text-primary"></span></div>');

  // Consultamos tu API que retorna la lista de tokens
  $.get(`../api/v1/fulmuv/empresa/tokens/${id_empresa}`, function (res) {
    let response = JSON.parse(res);
    contenedor.empty();

    if (response.data && response.data.length > 0) {
      // Nuvei permite consultar la info de cada token
      response.data.forEach(tk => {
        // Aquí deberías llamar a la API de Nuvei (vía tu backend) para obtener el 'type' y 'termination'
        // Si ya guardaste esos datos en la tabla, úsalos directamente:
        let marcaStr = (tk.marca || 'generic').toLowerCase();
        let terminacion = tk.ultimos_digitos || '****';

        let brandIcon = 'fas fa-credit-card';
        if (marcaStr.includes('visa')) brandIcon = 'fab fa-cc-visa text-primary';
        else if (marcaStr.includes('master')) brandIcon = 'fab fa-cc-mastercard text-danger';

        let cardHtml = `
                    <div class="credit-card-item rounded p-3 mb-2 d-flex align-items-center shadow-sm border" 
                         style="cursor:pointer;" onclick="selectCard('${tk.token}', this)">
                        <div class="me-3 fs-3"><i class="${brandIcon}"></i></div>
                        <div class="flex-grow-1 text-start">
                            <div class="fw-bold">•••• •••• •••• ${terminacion}</div>
                            <div class="text-muted small text-uppercase">Método Nuvei Registrado</div>
                        </div>
                    </div>`;
        contenedor.append(cardHtml);
      });

      $('#btnProcesarPago').off('click').on('click', function () {
        ejecutarUpgradeFinal(id_membresia, valor_final, dias, sucursales, tokenSeleccionado);
      });
    } else {
      contenedor.html('<div class="alert alert-warning small text-center">No hay métodos de pago registrados.</div>');
    }
  });
}

function selectCard(token, el) {
  $('.credit-card-item').removeClass('border-success bg-light selected');
  $(el).addClass('border-success bg-light selected');
  tokenSeleccionado = token;
  $('#btnProcesarPago').prop('disabled', false);
}

function ejecutarUpgradeFinal(id_membresia, valor, dias, sucursales, token) {
  if (!token) return;
  $('#btnProcesarPago').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Pagando...');

  $.post("../api/v1/fulmuv/membresias/upgrade", {
    id_membresia, id_empresa, valor, dias_permitidos: dias,
    sucursales: sucursales ? 'Y' : 'N', token
  }, function (returnedData) {
    if (!returnedData.error) {
      $('#modalUpgradePago').modal('hide');
      swal("¡Éxito!", "Membresía actualizada con éxito.", "success").then(() => {
        redirigirConPost('true', $("#username_principal").val());
      });
    } else {
      swal("Error", returnedData.msg, "error");
      $('#btnProcesarPago').prop('disabled', false).text('Pagar y Confirmar');
    }
  }, 'json');
}

/**
 * BENEFICIOS POR PLAN
 */
function buildItems(nombreLower) {
  if (nombreLower.includes('onemuv')) {
    return `
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Ideal para particulares, no empresas</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publicación de "1" artículo/vehículo</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Hasta 15 fotos en la publicación</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Visibilidad nacional en plataforma</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Sin comisiones por venta</li>`;
  }
  if (nombreLower.includes('fulmuv')) {
    return `
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Para empresas y emprendedores</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Catálogo ilimitado de productos</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Envíos asegurados con FULMUV</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Posicionamiento prioritario en búsquedas</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Sello de Empresa Verificada</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Gestión de sucursales disponible</li>
            <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> 1 año de beneficios invirtiendo en 9.2 meses</li>`;
  }
  return `
        <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Ideal para lavadoras y vulcanizadoras básicas</li>
        <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Por solo $0.11 diarios en plan anual</li>
        <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Digitaliza tu negocio vehicular</li>
        <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Conexión directa con clientes</li>
        <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Sin comisiones por venta</li>`;
}

function splitItems(itemsHtml, visibleCount = 4) {
  const lis = itemsHtml.match(/<li[\s\S]*?<\/li>/g) || [];
  return {
    preview: lis.slice(0, visibleCount).join(''),
    rest: lis.slice(visibleCount).join(''),
    hasMore: lis.length > visibleCount
  };
}

function redirigirConPost(acceso, username_new) {
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "login.php";
  const input1 = document.createElement("input"); input1.name = "acceso"; input1.value = acceso; form.appendChild(input1);
  const input2 = document.createElement("input"); input2.name = "username_new"; input2.value = username_new; form.appendChild(input2);
  document.body.appendChild(form);
  form.submit();
}