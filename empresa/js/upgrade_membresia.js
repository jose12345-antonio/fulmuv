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
let upgradeBillingData = null;

function loadUpgradeBillingData() {
  return new Promise((resolve) => {
    $.get(`../api/v1/fulmuv/empresas/${id_empresa}`, function (res) {
      let response = res;
      if (typeof res === 'string') {
        try {
          response = JSON.parse(res);
        } catch (_) {
          resolve(null);
          return;
        }
      }

      const source = Array.isArray(response?.data)
        ? (response.data[0] || null)
        : (response?.data || response || null);

      if (!source || typeof source !== 'object') {
        resolve(null);
        return;
      }

      const billing = {
        razon_social: String(source.razon_social || '').trim(),
        tipo_identificacion: String(source.tipo_identificacion || 'cedula').trim() || 'cedula',
        cedula_ruc: String(source.cedula_ruc || '').trim(),
        direccion_facturacion: String(source.direccion_facturacion || source.direccion || '').trim(),
        telefono_facturacion: String(source.telefono_facturacion || source.telefono_contacto || '').trim(),
        correo_facturacion: String(source.correo_facturacion || source.correo || '').trim()
      };

      upgradeBillingData = billing;
      fillUpgradeBillingForm(billing);
      resolve(billing);
    }).fail(function () {
      resolve(null);
    });
  });
}

function fillUpgradeBillingForm(data) {
  const billing = data || {};
  $('#upgrade_razon_social').val(billing.razon_social || '');
  $('#upgrade_tipo_identificacion').val(billing.tipo_identificacion || 'cedula');
  $('#upgrade_cedula_ruc').val(billing.cedula_ruc || '');
  $('#upgrade_direccion_facturacion').val(billing.direccion_facturacion || '');
  $('#upgrade_telefono_facturacion').val(billing.telefono_facturacion || '');
  $('#upgrade_correo_facturacion').val(billing.correo_facturacion || '');
}

function getUpgradeBillingPayload() {
  return {
    razon_social: String($('#upgrade_razon_social').val() || '').trim(),
    tipo_identificacion: String($('#upgrade_tipo_identificacion').val() || 'cedula').trim(),
    cedula_ruc: String($('#upgrade_cedula_ruc').val() || '').trim(),
    direccion_facturacion: String($('#upgrade_direccion_facturacion').val() || '').trim(),
    telefono_facturacion: String($('#upgrade_telefono_facturacion').val() || '').trim(),
    correo_facturacion: String($('#upgrade_correo_facturacion').val() || '').trim()
  };
}

function syncUpgradeBillingDraftFromForm() {
  upgradeBillingData = getUpgradeBillingPayload();
  return upgradeBillingData;
}

function validateUpgradeBillingPayload() {
  const billing = getUpgradeBillingPayload();
  if (!billing.razon_social) return 'Completa la razon social de facturacion.';
  if (!billing.cedula_ruc) return 'Completa la identificacion de facturacion.';
  if (!billing.direccion_facturacion) return 'Completa la direccion de facturacion.';
  if (!billing.telefono_facturacion) return 'Completa el telefono de facturacion.';
  if (!billing.correo_facturacion) return 'Completa el correo de facturacion.';
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(billing.correo_facturacion)) return 'Ingresa un correo de facturacion valido.';
  return '';
}

function fireUpgradeAlert(options) {
  const opts = options || {};

  if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
    return Swal.fire(opts);
  }

  if (typeof swal === 'function') {
    return new Promise((resolve) => {
      swal({
        title: opts.title || '',
        text: opts.text || '',
        icon: opts.icon || 'info',
        button: opts.confirmButtonText || 'OK'
      }, function () {
        resolve({ isConfirmed: true });
      });
    });
  }

  if (opts.text) {
    window.alert(opts.text);
  }

  return Promise.resolve({ isConfirmed: true });
}

function buildUpgradeGatewayDetail(response, fallbackMessage = 'Pago no completado.') {
  const lines = [];
  const trx = response?.transaction || {};
  const card = response?.card || trx?.card || {};
  const error = response?.error || {};

  const pushLine = (label, value) => {
    const clean = String(value || '').trim();
    if (clean) lines.push(`${label}: ${clean}`);
  };

  pushLine('Estado Nuvei', trx.status || trx.current_status || card.status);
  pushLine('Detalle Nuvei', trx.status_detail || trx.message || card.status_detail || card.message);
  pushLine('Respuesta banco', trx.carrier_code || trx.authorization_code || error.code);
  pushLine('Descripción', error.description || error.help || error.type);
  pushLine('Referencia', trx.id || card.transaction_reference);

  return lines.length ? lines.join('\n') : fallbackMessage;
}
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

  // Reset estado al cerrar el modal
  $('#modalUpgradePago').on('hidden.bs.modal', function () {
    syncUpgradeBillingDraftFromForm();
    tokenSeleccionado = null;
    $('.wallet-card-item').removeClass('selected');
    $('#btnProcesarPago').prop('disabled', true).show()
      .html('<i class="fas fa-lock me-2"></i> Pagar y Confirmar');
    $('#nuevaTarjetaForm').hide();
    $('#chkGuardarTarjeta').prop('checked', true);
    $('#tokenize_response_upgrade').html('');
    const tb = document.getElementById('tokenize_btn_upgrade');
    if (tb) {
      tb.disabled = false;
      tb.innerHTML = '<i class="fas fa-credit-card me-2"></i> Ingresar datos de tarjeta';
    }
    $('#btnNuevaTarjeta').removeClass('activo')
      .html('<i class="fas fa-plus-circle me-2"></i> Pagar con nueva tarjeta');
    $('#modalUpgradePago').css('opacity', 1);
    fillUpgradeBillingForm(upgradeBillingData || {});
  });

  $(document).on('input change', '#upgrade_razon_social, #upgrade_tipo_identificacion, #upgrade_cedula_ruc, #upgrade_direccion_facturacion, #upgrade_telefono_facturacion, #upgrade_correo_facturacion', function () {
    syncUpgradeBillingDraftFromForm();
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
    if (rankNuevo === rankActual && (costoNuevoNum < valorPagadoActual) && (diasNuevoNum <= diasTotalesActual) && !tiene_sucursales) {
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
    $('#detallePagoUpgrade').html(`
      <div class="upgrade-summary-item">
        <div class="upgrade-summary-label">Plan actual</div>
        <div class="upgrade-summary-value">${actual.nombre} · ${diasTotalesActual} dias</div>
      </div>
      <div class="upgrade-summary-item">
        <div class="upgrade-summary-label">Vence</div>
        <div class="upgrade-summary-value">${actual.fecha_fin || '-'}</div>
      </div>
      <div class="upgrade-summary-item">
        <div class="upgrade-summary-label">Dias restantes</div>
        <div class="upgrade-summary-value">${diasRestantes} dias</div>
      </div>
      <div class="upgrade-summary-item">
        <div class="upgrade-summary-label">Plan nuevo</div>
        <div class="upgrade-summary-value">${planNuevo.nombre} · ${diasNuevoNum} dias</div>
      </div>
    `);
    $('#upgradeHeaderPlan').text(planNuevo.nombre || '-');
    $('#upgradeHeaderPrice').text(`$${valorFinal.toFixed(2)}`);

    cargarTarjetasUpgrade(id_membresia_nueva, valorFinal.toFixed(2), dias_nuevos, tiene_sucursales);
    loadUpgradeBillingData().finally(function () {
      $('#modalUpgradePago').modal('show');
    });
  });
}

/**
 * HELPERS DE MARCA DE TARJETA
 */

/**
 * Retorna true si la tarjeta ya venció.
 * Una tarjeta es válida durante todo el mes de vencimiento;
 * vence el primer día del mes siguiente.
 */
function isCardExpired(exp_month, exp_year) {
  if (!exp_month || !exp_year) return false;
  let year = parseInt(exp_year, 10);
  if (year < 100) year += 2000;           // manejar año de 2 dígitos
  const month = parseInt(exp_month, 10);  // 1-12
  const expDate = new Date(year, month, 1); // primer día del mes SIGUIENTE
  return new Date() >= expDate;
}

function getBrandIcon(marcaStr) {
  const t = normalizeBrand(marcaStr).toLowerCase();
  if (t.includes('visa'))       return '<i class="fab fa-cc-visa" style="color:#f8d277;"></i>';
  if (t.includes('master'))     return '<i class="fab fa-cc-mastercard" style="color:#111827;"></i>';
  if (t.includes('amex') || t.includes('american')) return '<i class="fab fa-cc-amex" style="color:#ffffff;"></i>';
  if (t.includes('discover'))   return '<i class="fab fa-cc-discover" style="color:#ffffff;"></i>';
  if (t.includes('diners'))     return '<i class="fab fa-cc-diners-club" style="color:#ffffff;"></i>';
  return '<i class="fas fa-credit-card" style="color:#64748b;"></i>';
}

function getBrandCardClass(marcaStr) {
  const t = normalizeBrand(marcaStr).toLowerCase();
  if (t.includes('visa')) return 'brand-visa';
  if (t.includes('master')) return 'brand-mastercard';
  if (t.includes('amex') || t.includes('american')) return 'brand-amex';
  if (t.includes('discover')) return 'brand-discover';
  if (t.includes('diners')) return 'brand-diners';
  return '';
}

function getBrandLabel(marcaStr) {
  const t = normalizeBrand(marcaStr).toLowerCase();
  if (t.includes('visa'))       return 'Visa';
  if (t.includes('master'))     return 'Mastercard';
  if (t.includes('amex') || t.includes('american')) return 'American Express';
  if (t.includes('discover'))   return 'Discover';
  if (t.includes('diners'))     return 'Diners Club';
  return 'Tarjeta';
}

/**
 * Convierte el code de tipo Paymentez/Nuvei a nombre de marca legible
 * Codes: vi=Visa, mc=Mastercard, ax=Amex, di=Discover, dc=Diners
 */
function normalizeBrand(rawType) {
  const t = (rawType || '').toLowerCase().trim();
  if (t === 'vi'  || t.includes('visa'))       return 'Visa';
  if (t === 'mc'  || t.includes('master'))     return 'Mastercard';
  if (t === 'ax'  || t.includes('amex'))       return 'American Express';
  if (t === 'di'  || t.includes('discover'))   return 'Discover';
  if (t === 'dc'  || t.includes('diners'))     return 'Diners';
  return t || 'Crédito / Débito';
}

/**
 * CARGA DE TARJETAS GUARDADAS (diseño visual de tarjeta física)
 */
function cargarTarjetasUpgrade(id_membresia, valor_final, dias, sucursales) {
  const contenedor = $('#listaTarjetas');
  contenedor.html('<div class="text-center p-3"><span class="spinner-border spinner-border-sm text-primary"></span></div>');

  $.get(`../api/v1/fulmuv/empresa/tokens/${id_empresa}`, function (res) {
    let response = JSON.parse(res);
    contenedor.empty();

    const tarjetas = (response.data || []).filter(tk => {
      const status = String(tk.status || '').toLowerCase().trim();
      const expiredCard = tk.exp_month && tk.exp_year
        ? isCardExpired(tk.exp_month, tk.exp_year)
        : false;
      return status === 'valid' && !expiredCard;
    });

    if (tarjetas.length > 0) {
      tarjetas.forEach(tk => {
        const brandIcon   = getBrandIcon(tk.marca || '');
        const brandClass  = getBrandCardClass(tk.marca || '');
        const terminacion = tk.ultimos_digitos ? String(tk.ultimos_digitos).slice(-4) : '••••';
        const banco      = tk.banco || tk.bank_name || '';

        let caducidad = '';
        let expiredCard = false;
        if (tk.exp_month && tk.exp_year) {
          const yy = String(tk.exp_year).slice(-2);
          caducidad = `${String(tk.exp_month).padStart(2,'0')}/${yy}`;
          expiredCard = isCardExpired(tk.exp_month, tk.exp_year);
        }

        const expiredClass   = expiredCard ? ' expired' : '';
        const expiredBadge   = expiredCard ? '<span class="wallet-expired-badge ms-1">Vencida</span>' : '';
        const expiryClass    = expiredCard ? ' is-expired' : '';
        const clickAttr      = expiredCard ? '' : `onclick="selectCard('${tk.token}', this)"`;
        const expiryHtml     = caducidad
          ? `<span class="wallet-card-expiry${expiryClass}">Vence ${caducidad}</span>`
          : '';
        const bancoHtml      = banco
          ? `<span class="wallet-card-bank" title="${banco}">${banco}</span>`
          : '';

        const cardHtml = `
          <div class="wallet-card-item ${brandClass} mb-2${expiredClass}"
               data-token="${tk.token}"
               ${clickAttr}>
            <div class="wallet-card-icon">${brandIcon}</div>
            <div class="wallet-card-info">
              <div class="wallet-card-number">•••• •••• •••• ${terminacion}</div>
              <div class="wallet-card-meta">
                ${expiryHtml}${expiredBadge}
                ${bancoHtml}
              </div>
            </div>
            <div class="wallet-card-check"><i class="fas fa-check-circle"></i></div>
          </div>`;
        contenedor.append(cardHtml);
      });

      $('#btnProcesarPago').off('click').on('click', function () {
        const billingError = validateUpgradeBillingPayload();
        if (billingError) {
          fireUpgradeAlert({ icon: 'warning', title: 'Revisa la facturacion', text: billingError });
          return;
        }
        if (!tokenSeleccionado) {
          fireUpgradeAlert({ icon: 'warning', title: 'Selecciona una tarjeta', text: 'Elige una tarjeta valida para continuar.' });
          return;
        }
        ejecutarUpgradeFinal(id_membresia, valor_final, dias, sucursales, tokenSeleccionado, id_usuario);
      });
    } else {
      contenedor.html(`
        <div class="text-center py-3 text-muted small">
          <i class="fas fa-credit-card fa-2x mb-2 d-block opacity-50"></i>
          No tienes tarjetas guardadas.<br>Agrega una nueva abajo.
        </div>`);
    }

    // ── Botón "nueva tarjeta" ──────────────────────────────────────────────
    $('#btnNuevaTarjeta').off('click').on('click', function () {
      const activo = $(this).hasClass('activo');
      if (activo) {
        // colapsar
        $(this).removeClass('activo').html('<i class="fas fa-plus-circle me-2"></i> Pagar con nueva tarjeta');
        $('#nuevaTarjetaForm').slideUp(200);
        // restaurar botón principal si hay tarjeta guardada seleccionada
        if (tokenSeleccionado) $('#btnProcesarPago').prop('disabled', false);
        $('#btnProcesarPago').show();
      } else {
        // desplegar form Nuvei
        $(this).addClass('activo').html('<i class="fas fa-times-circle me-2"></i> Cancelar nueva tarjeta');
        // deseleccionar tarjeta guardada
        $('.wallet-card-item').removeClass('selected');
        tokenSeleccionado = null;
        $('#btnProcesarPago').prop('disabled', true).hide();
        $('#nuevaTarjetaForm').slideDown(200);
        iniciarFormNuevaTarjetaUpgrade(id_membresia, valor_final, dias, sucursales);
      }
    });

    // Ocultar form nueva tarjeta al abrir modal (reset)
    $('#nuevaTarjetaForm').hide();
    $('#btnNuevaTarjeta').removeClass('activo').html('<i class="fas fa-plus-circle me-2"></i> Pagar con nueva tarjeta');
  });
}

function selectCard(token, el) {
  if ($('#btnNuevaTarjeta').hasClass('activo')) {
    $('#btnNuevaTarjeta').trigger('click');
  }
  $('.wallet-card-item').removeClass('selected');
  $(el).addClass('selected');
  tokenSeleccionado = token;
  $('#btnProcesarPago').prop('disabled', false).show();
}

/**
 * INICIALIZAR PAGO CON NUEVA TARJETA — PaymentCheckout.modal (Nuvei/Paymentez)
 * El SDK abre su propio overlay con el formulario de pago completo.
 */
function iniciarFormNuevaTarjetaUpgrade(id_membresia, valor_final, dias, sucursales) {
  const respEl   = document.getElementById('tokenize_response_upgrade');
  const submitBtn = document.getElementById('tokenize_btn_upgrade');
  respEl.innerHTML = '';

  if (typeof PaymentCheckout === 'undefined') {
    respEl.innerHTML =
      '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>' +
      'No se pudo cargar el SDK de pagos. Recarga la página e intenta de nuevo.</span>';
    return;
  }

  // PRODUCCIÓN — env_mode:'prod' / FULMUV-PR-EC-CLIENT / 8XJbDPhiJYeezjr92Qr3Tr4tSyC5gH
  const env_mode    = 'stg';
  const app_code    = 'TESTECUADORSTG-EC-CLIENT';
  const app_key     = 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6';

  const correo    = document.getElementById('correo_empresa_upgrade')?.value || 'empresa@fulmuv.com';
  const idUsuario = document.getElementById('id_principal')?.value || '';
  const monto     = parseFloat(valor_final) || 0;
  const montoStr  = monto.toFixed(2);
  let checkoutPopstateBound = false;
  let shouldRestoreUpgradeModal = true;
  const upgradeModalEl = document.getElementById('modalUpgradePago');
  const upgradeModalInstance = upgradeModalEl ? bootstrap.Modal.getInstance(upgradeModalEl) : null;

  const setNuveiCheckoutLayering = (isOpen) => {
    document.body.classList.toggle('nuvei-checkout-open', !!isOpen);
    if (isOpen) {
      $('.modal-backdrop.show').css({ opacity: 0, visibility: 'hidden', pointerEvents: 'none' });
    } else {
      $('.modal-backdrop').css({ opacity: '', visibility: '', pointerEvents: '' });
    }
  };

  const checkout = new PaymentCheckout.modal({
    env_mode,
    onOpen: function () {
      syncUpgradeBillingDraftFromForm();
      setNuveiCheckoutLayering(true);
      if (upgradeModalInstance) {
        upgradeModalInstance.hide();
      } else {
        $('#modalUpgradePago').modal('hide');
      }
    },
    onClose: function () {
      setNuveiCheckoutLayering(false);
      if (shouldRestoreUpgradeModal) {
        setTimeout(() => {
          fillUpgradeBillingForm(upgradeBillingData || {});
          $('#modalUpgradePago').modal('show');
        }, 120);
      }
      submitBtn.disabled = false;
    },
    onResponse: function (response) {
      shouldRestoreUpgradeModal = false;
      setNuveiCheckoutLayering(false);

      if (!response.transaction || response.transaction.status !== 'success') {
        const detalle = buildUpgradeGatewayDetail(response);
        respEl.innerHTML =
          `<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>${detalle.replace(/\n/g, '<br>')}</span>`;
        fireUpgradeAlert({ icon: 'error', title: 'Error Nuvei', text: detalle });
        submitBtn.disabled = false;
        return;
      }

      const trx        = response.transaction;
      const trxId      = trx.id || null;
      const authCode   = trx.authorization_code || null;
      const cardToken  = trx.card?.token || null;
      const cardType   = trx.card?.type  || '';
      const cardNumber = String(trx.card?.number || '');
      const ultDigitos = cardNumber.replace(/\D/g,'').slice(-4) || null;
      const marcaNorm  = normalizeBrand(cardType);
      const expYear    = trx.card?.expiry_year  || null;
      const expMonth   = trx.card?.expiry_month || null;
      const guardar    = document.getElementById('chkGuardarTarjeta')?.checked !== false;

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando pago...';

      // ── Guardar en Wallet si el usuario lo pidió ──
      const guardarYLuegoProcesar = (cb) => {
        if (!guardar || !cardToken) { cb(); return; }
        $.post('../api/v1/fulmuv/venta/recurrente/', {
          token:                 cardToken,
          transaction_reference: trxId,
          id_usuario:            idUsuario,
          id_empresa,
          ultimos_digitos:       ultDigitos,
          marca:                 marcaNorm,
          exp_year:              expYear,
          exp_month:             expMonth
        }).always(cb); // Continuar aunque falle el guardado
      };

      guardarYLuegoProcesar(function () {
        // ── Registrar membresía (cobro ya realizado por PaymentCheckout) ──
        const payload = {
          id_membresia,
          id_empresa,
          valor:           montoStr,
          dias_permitidos: dias,
          sucursales:      sucursales ? 'Y' : 'N',
          id_usuario:      idUsuario,
          ya_cobrado:      '1',
          transaction_id:  trxId,
          authorization_code: authCode
        };
        Object.assign(payload, getUpgradeBillingPayload());

        $.post('../api/v1/fulmuv/membresias/upgrade', payload, function (returnedData) {
          _onUpgradeResponse(returnedData);
        }, 'json').fail(function () {
          _resetPagarBtn();
          fireUpgradeAlert({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        });
      });
    }
  });

  // Cuando el usuario hace clic en el botón, abrir el overlay de Nuvei
  submitBtn.onclick = function () {
    const billingError = validateUpgradeBillingPayload();
    if (billingError) {
      fireUpgradeAlert({ icon: 'warning', title: 'Revisa la facturacion', text: billingError });
      return;
    }
    respEl.innerHTML = '';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparando checkout...';

    $.post('../api/v1/webstore/init_reference/', {
      id_membresia,
      id_empresa,
      id_usuario: idUsuario,
      valor: montoStr
    }, function (returnedData) {
      if (returnedData?.error || !returnedData?.payment?.reference) {
        const message = returnedData?.msg || returnedData?.payment?.detail || 'No se pudo inicializar la referencia de pago con Nuvei.';
        respEl.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>${message}</span>`;
        fireUpgradeAlert({ icon: 'error', title: 'Error Nuvei', text: message });
        _resetPagarBtn();
        return;
      }

      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Abriendo checkout...';
      checkout.open({
        reference: returnedData.payment.reference
      });

      if (!checkoutPopstateBound) {
      window.addEventListener('popstate', function () {
        if (checkout && typeof checkout.close === 'function') {
          checkout.close();
        }
      });
        checkoutPopstateBound = true;
      }
    }, 'json').fail(function () {
      const message = 'No se pudo conectar con el servidor para inicializar la referencia de pago.';
      respEl.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>${message}</span>`;
      fireUpgradeAlert({ icon: 'error', title: 'Error de red', text: message });
      shouldRestoreUpgradeModal = true;
      _resetPagarBtn();
    });
  };
}

/**
 * EJECUTAR UPGRADE (usa token guardado en pagos_recurrentes)
 */
function ejecutarUpgradeFinal(id_membresia, valor, dias, sucursales, token_directo, id_usuario_directo) {
  const billingError = validateUpgradeBillingPayload();
  if (billingError) {
    fireUpgradeAlert({ icon: 'warning', title: 'Revisa la facturacion', text: billingError });
    return;
  }
  $('#btnProcesarPago').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Pagando...');

  const payload = {
    id_membresia,
    id_empresa,
    valor,
    dias_permitidos: dias,
    sucursales: sucursales ? 'Y' : 'N'
  };
  Object.assign(payload, getUpgradeBillingPayload());
  if (token_directo)     payload.token      = token_directo;
  if (id_usuario_directo) payload.id_usuario = id_usuario_directo;

  $.post("../api/v1/fulmuv/membresias/upgrade", payload, function (returnedData) {
    _onUpgradeResponse(returnedData);
  }, 'json').fail(function () {
    _resetPagarBtn();
    fireUpgradeAlert({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
  });
}

/**
 * EJECUTAR UPGRADE CUANDO YA TENEMOS EL TOKEN (tarjeta existente, nuevo token obtenido)
 */
function ejecutarUpgradeConToken(id_membresia, valor, dias, sucursales, token) {
  // Primero guardar el token para que el backend lo encuentre
  $.post('../api/v1/fulmuv/venta/recurrente/', {
    token:                 token,
    transaction_reference: '',
    id_usuario:            document.getElementById('id_principal')?.value || '',
    id_empresa:            id_empresa
  }, function () {
    ejecutarUpgradeFinal(id_membresia, valor, dias, sucursales);
  }).fail(function () {
    // Intentar upgrade de todas formas (el token puede ya estar guardado)
    ejecutarUpgradeFinal(id_membresia, valor, dias, sucursales);
  });
}

function _resetPagarBtn() {
  $('#btnProcesarPago').prop('disabled', false).html('<i class="fas fa-lock me-2"></i> Pagar y Confirmar');
  const submitBtn = document.getElementById('tokenize_btn_upgrade');
  if (submitBtn) {
    submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i> Confirmar y Pagar';
    submitBtn.removeAttribute('disabled');
  }
}

function _onUpgradeResponse(returnedData) {
  if (!returnedData.error) {
    $('#modalUpgradePago').modal('hide');
    fireUpgradeAlert({
      icon: 'success',
      title: '¡Membresía actualizada!',
      text: 'Para mayor seguridad vamos a reiniciar tu sesión y aplicar tu nueva membresía.',
      confirmButtonText: 'Continuar',
      confirmButtonColor: '#0f766e'
    }).then(() => {
      redirigirConPost('true', $("#username_principal").val());
    });
  } else {
    _resetPagarBtn();
    fireUpgradeAlert({ icon: 'error', title: 'Error', text: returnedData.msg || 'No se pudo procesar el pago.' });
  }
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
