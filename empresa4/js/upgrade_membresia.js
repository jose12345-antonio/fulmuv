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

function planRank(nombre) {
  const n = (nombre || '').toLowerCase();
  if (n.includes('basicmuv')) return 1;
  if (n.includes('onemuv')) return 2;
  if (n.includes('fulmuv')) return 3;
  return 0;
}

function parseDateLocal(value) {
  if (!value) return null;
  const s = String(value).trim().replace(' ', 'T');
  const d = new Date(s);
  return isNaN(d.getTime()) ? null : d;
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
      saveMembresia(planFinal, costo_seleccionado, diasSel, tieneSucChecked);
    });
  });
}

/**
 * LÓGICA DE UPGRADE (Evita NaN)
 */
function saveMembresia(planNuevo, costo_nuevo, dias_nuevos, tiene_sucursales) {
  if (!planNuevo) return;
  const id_membresia_nueva = planNuevo.id_membresia;
  $.get(`../api/v1/fulmuv/empresa/membresia_actual/${id_empresa}`, function (res) {
    let response = JSON.parse(res);
    if (response.error) return;

    let actual = response.data;
    const valorPagadoActual = parseFloat(actual.valor_membresia) || 0;
    const diasTotalesActual = parseInt(actual.dias_permitidos) || 30;
    const costoNuevoNum = parseFloat(costo_nuevo) || 0;

    const rankActual = planRank(actual.nombre || '');
    const rankNuevo = planRank(planNuevo.nombre || '');
    const diasNuevoNum = parseInt(dias_nuevos) || 0;

    if (rankNuevo < rankActual) {
      swal("No permitido", "No se permite hacer downgrade de plan.", "warning");
      return;
    }
    if (rankNuevo === rankActual && (costoNuevoNum <= valorPagadoActual) && (diasNuevoNum <= diasTotalesActual) && !tiene_sucursales) {
      swal("No permitido", "No se permite hacer downgrade o mantener el mismo plan con menor duración.", "warning");
      return;
    }

    const fechaFin = parseDateLocal(actual.fecha_fin);
    const hoy = new Date();
    let diasRestantes = 0;
    if (fechaFin) {
      diasRestantes = Math.ceil((fechaFin - hoy) / (1000 * 60 * 60 * 24));
      if (diasRestantes < 0) diasRestantes = 0;
    }

    let creditoAFavor = 0;
    if (costoNuevoNum >= valorPagadoActual && diasRestantes > 0 && diasTotalesActual > 0) {
      creditoAFavor = (valorPagadoActual / diasTotalesActual) * diasRestantes;
    }
    creditoAFavor = Math.min(creditoAFavor, costoNuevoNum);
    let valorFinal = (costoNuevoNum - creditoAFavor);

    $('#detallePagoUpgrade').html(`
      <div class="mb-2 small"><strong>Plan actual:</strong> ${actual.nombre} · ${diasTotalesActual} días</div>
      <div class="mb-2 small"><strong>Vence:</strong> ${actual.fecha_fin || '—'}</div>
      <div class="mb-2 small"><strong>Días restantes:</strong> ${diasRestantes} días</div>
      <div class="mb-2 small"><strong>Plan nuevo:</strong> ${planNuevo.nombre} · ${diasNuevoNum} días</div>
    `);

    $('#subtotalModal').text(`$${costoNuevoNum.toFixed(2)}`);
    $('#creditoModal').text(`-$${creditoAFavor.toFixed(2)}`);
    $('#totalModal').text(`$${valorFinal.toFixed(2)}`);

    cargarTarjetasUpgrade(id_membresia_nueva, valorFinal.toFixed(2), dias_nuevos, tiene_sucursales);
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
      swal({
        title: "¡Membresía actualizada!",
        text: "Para mayor seguridad vamos a reiniciar tu sesión y aplicar tu nueva membresía.",
        icon: "success",
        button: "Continuar"
      }).then(() => {
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
