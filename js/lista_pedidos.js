const numero_orden = $("#numero_orden").val();
let ordenDataGlobal = null;
let mapaOrden, marcadorOrden;

function formatPrecioSuperscript(valor) {
    const num = Number(valor) || 0;
    const entero = Math.floor(num);
    const centavos = Math.round((num - entero) * 100).toString().padStart(2, '0');
    const enteroFormateado = entero.toLocaleString('es-EC');
    return `<span style="font-size:0.6em;font-weight:400;vertical-align:middle;margin-right:1px;">US$</span><strong>${enteroFormateado}</strong><span style="font-size:0.55em;font-weight:400;position:relative;top:-0.4em;margin-left:1px;">,${centavos}</span>`;
}

// === Agencias (cache global) ===
let AGENCIAS_MAP = {};
let agenciasReady = null;
let TARIFAS_MAP = {};

// --- util global para escapar HTML ---
(function (w) {
  const MAP = {
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
  };
  w.escapeHtml = function (s) {
    return String(s ?? '').replace(/[&<>"'`=\/]/g, ch => MAP[ch]);
  };
})(window);


function precargarAgencias() {
  // Devuelve un jqXHR/Promise
  return $.getJSON('api/v1/fulmuv/getCiudadesAgencia/')
    .then(r => {
      if (r && Array.isArray(r.data)) {
        AGENCIAS_MAP = Object.fromEntries(
          r.data.map(a => [String(a.id), a.nombre])
        );
      } else {
        AGENCIAS_MAP = {};
      }
    })
    .catch(() => { AGENCIAS_MAP = {}; });
}

// Devuelve nombre o "—"
function agenciaNombreById(id) {
  const key = (id === 0 || id === '0' || id === '' || id == null) ? null : String(id);
  return key && AGENCIAS_MAP[key] ? AGENCIAS_MAP[key] : '—';
}
function badgeEstado(estado) {
  const s = String(estado || '').toLowerCase();
  const icons = {
    creada:'fi-rs-clock', procesada:'fi-rs-refresh', enviada:'fi-rs-truck-side',
    aprobada:'fi-rs-check-circle', completada:'fi-rs-check-circle',
    eliminada:'fi-rs-cross-circle', pendiente:'fi-rs-time-past'
  };
  const labels = {
    creada:'Creada', procesada:'Procesada', enviada:'Enviada',
    aprobada:'Entregada', completada:'Entregada',
    eliminada:'Cancelada', pendiente:'Pendiente'
  };
  const cls   = `s-${s || 'default'}`;
  const icon  = icons[s]  || 'fi-rs-info';
  const label = labels[s] || (s ? s.charAt(0).toUpperCase() + s.slice(1) : '—');
  return `<span class="badge-estado ${cls}"><i class="${icon}"></i> ${label}</span>`;
}

function stepsHtml(estadoVenta, esRetiro) {
  if (esRetiro) {
    const steps = [
      { label: 'Creada',     done: estadoVenta >= 0, active: estadoVenta === 0 },
      { label: 'Confirmada', done: estadoVenta >= 1, active: estadoVenta === 1 },
      { label: 'Lista',      done: estadoVenta >= 2, active: estadoVenta === 2 }
    ];
    return stepsRow(steps);
  }
  const steps = [
    { label: 'Creada',     done: estadoVenta >= 0, active: estadoVenta === 0 },
    { label: 'Procesada',  done: estadoVenta >= 1, active: estadoVenta === 1 },
    { label: 'Pago envío', done: estadoVenta >= 2, active: estadoVenta === 2 },
    { label: 'En camino',  done: estadoVenta >= 3, active: estadoVenta === 3 }
  ];
  return stepsRow(steps);
}

function stepsRow(steps) {
  const html = steps.map(s => `
    <div class="order-step ${s.done && !s.active ? 'done' : ''} ${s.active ? 'active' : ''}">
      <div class="step-dot"></div>
      <span class="step-label">${s.label}</span>
    </div>`).join('');
  return `<div class="order-steps">${html}</div>`;
}

// Ajusta el endpoint si usas otro (p.ej. api/v1/fulmuv/getTarifasFulmuv)
function precargarTarifas(tipo = 'mercancia_premier') {
  return $.getJSON('api/v1/fulmuv/getTrayectos/' + tipo,)
    .then(r => {
      if (r && Array.isArray(r.data)) {
        TARIFAS_MAP = Object.fromEntries(
          r.data
            .filter(t => t.estado === 'A') // sólo activas
            .map(t => [String(t.id_trayecto), {
              nombre: t.nombre,
              valor: Number(t.valor || 0),        // hasta 2kg
              adicional: Number(t.adicional || 0),// kilo adicional
              tipo: t.tipo
            }])
        );
      } else {
        TARIFAS_MAP = {};
      }
    })
    .catch(() => { TARIFAS_MAP = {}; });
}

$(document).ready(function () {
  // 1) Cargar en paralelo agencias y tarifas
  const agenciasReady = precargarAgencias();
  const tarifasReady = precargarTarifas('mercancia_premier');

  // 2) Cuando ambas estén listas, ya armamos la UI y disparamos la primera consulta
  $.when(agenciasReady, tarifasReady).always(() => {
    $("#breadcrumb").append(`
      <a href="vendor.php" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
      <span></span> Seguimiento de tu pedido
    `);

    const $sel = $('#selectOrden');

    // opción vacía para el placeholder
    $sel.append('<option value=""></option>');

    // Poblar órdenes del cliente
    $.post('api/v1/fulmuv/cliente/getOrdenCliente',
      { id_cliente: $('#id_cliente').val() },
      function (res) {
        if (!res?.error && Array.isArray(res.data)) {
          res.data.forEach(o => {
            $sel.append(
              `<option value="${o.numero_orden}" data-id="${o.id_orden}">
                 ${o.numero_orden}
               </option>`
            );
          });
        }

        // Inicializar Select2
        $sel.select2({
          placeholder: 'Seleccione tu orden',
          allowClear: true,
          width: '100%',
          language: {
            noResults: () => 'Sin resultados',
            searching: () => 'Buscando…'
          }
        });

        // Preselección: ?q=NUMERO_ORDEN o la primera
        const q = new URLSearchParams(location.search).get('q');
        if (q && $sel.find(`option[value="${q}"]`).length) {
          $sel.val(q).trigger('change');
        } else {
          const first = $sel.find('option[value!=""]').first().val();
          if (first) $sel.val(first).trigger('change');
        }
      },
      'json'
    );

    // Al cambiar, cargar la orden seleccionada
    $sel.on('change', function () {
      const numeroOrden = $(this).val();
      if (numeroOrden) {
        getOrdenSeguimiento(numeroOrden); // <- aquí ya existen AGENCIAS_MAP y TARIFAS_MAP
      } else {
        $('#cardsEmpresas').empty();
      }
    });
  });
});

// Busca el bloque ISO correspondiente a una empresa de la orden
function findIsoForEmpresa(d, empresa) {
  const allIso = [];
  if (Array.isArray(d?.orden_iso)) allIso.push(...d.orden_iso);
  if (Array.isArray(d?.empresas)) {
    d.empresas.forEach(em => {
      if (Array.isArray(em?.orden_iso)) allIso.push(...em.orden_iso);
    });
  }
  const idEmpresaOrden = String(empresa?.id_orden || empresa?.id_ordenes || d?.id_orden || '');
  return allIso.find(x =>
    String(x.id_orden_empresa) === idEmpresaOrden || String(x.id_orden) === idEmpresaOrden
  );
}

// Calcula monto (tarifa + IVA) para el recuadro de esa empresa
function calcularTarifaDeEmpresa(d, empresa) {
  const iso = findIsoForEmpresa(d, empresa);
  if (!iso) return { ok: false };

  // id_trayecto puede venir en ISO, en la empresa o a nivel de orden
  const idTrayecto = iso.id_trayecto ?? empresa?.id_trayecto ?? d?.id_trayecto;

  // peso puede venir con coma; normalizamos
  const pesoRaw = iso.total_peso ?? iso.peso ?? 0;
  const peso = Number(String(pesoRaw).replace(',', '.')) || 0;

  // Usa la misma tabla "mercancia_premier"
  const { total, base, adicionalKg, kilosExtra, nombreTrayecto } =
    calcularTarifaFulmuv(idTrayecto, peso, 'mercancia_premier');

  const iva = total * 0.15;
  const totalConIva = total + iva;

  return {
    ok: true,
    idTrayecto,
    nombreTrayecto,
    peso,
    base,
    adicionalKg,
    kilosExtra,
    total,
    iva,
    totalConIva
  };
}

// Calcula con regla: <=2kg usa valor*kg, si >2kg: 2*valor + (kg-2)*adicional (acepta fracciones)
// function calcularTarifaFulmuv(idTrayecto, pesoKg, tipo = 'mercancia_premier') {
//   const t = TARIFAS_MAP[String(idTrayecto)];
//   if (!t || (tipo && t.tipo !== tipo)) {
//     return { total: 0, base: 0, adicionalKg: 0, extraKg: 0, nombreTrayecto: '—' };
//   }

//   const p = Math.max(0, Number(String(pesoKg ?? 0).replace(',', '.')) || 0);
//   const base = t.valor;            // tarifa por kg (hasta 2kg)
//   const adicional = t.adicional;   // por kg adicional (fracción incluida)

//   let total = 0;
//   let extra = 0;

//   if (p <= 2) {
//     total = base;
//   } else {
//     extra = p - 2;
//     total = (2 * base) + (extra * adicional);
//   }

//   return {
//     total,
//     base,
//     adicionalKg: adicional,
//     extraKg: Math.max(0, extra),
//     nombreTrayecto: t.nombre
//   };
// }

function calcularTarifaFulmuv(idTrayecto, pesoKg, tipo = 'mercancia_premier') {
  const t = TARIFAS_MAP[String(idTrayecto)];
  if (!t || (tipo && t.tipo !== tipo)) {
    return { total: 0, base: 0, adicionalKg: 0, extraKg: 0, nombreTrayecto: '—' };
  }

  const p = Math.max(0, Number(String(pesoKg ?? 0).replace(',', '.')) || 0);
  const base = Number(t.valor || 0);        // ✅ valor fijo (hasta 2kg)
  const adicional = Number(t.adicional || 0); // ✅ por kg extra

  let total = 0;
  let extra = 0;

  if (p <= 2) {
    total = base; // ✅ NO multiplicar
  } else {
    extra = p - 2;
    total = base + (extra * adicional); // ✅ base fija + extra*adicional
  }

  return {
    total,
    base,
    adicionalKg: adicional,
    extraKg: Math.max(0, extra),
    nombreTrayecto: t.nombre
  };
}


// ===== getOrdenSeguimiento AHORA RECIBE numero_orden =====
function getOrdenSeguimiento(numeroOrden) {
  $('#cardsEmpresas').html('<div class="text-center py-3">Cargando orden…</div>');

  $.post('api/v1/fulmuv/getOrdenesSeguimiento',
    { numero_orden: numeroOrden },
    function (res) {
      if (res.error || !Array.isArray(res.data) || !res.data.length) {
        $('#cardsEmpresas').html('<div class="text-center text-muted py-3">No se encontró la orden.</div>');
        return;
      }

      const data = res.data[0];
      ordenDataGlobal = data;
      const $cards = $('#cardsEmpresas').empty();

      // ✅ nombre de la agencia (usa root data.agencia_cercana del response)
      const nombreAgencia = agenciaNombreById(data.agencia_cercana);

      (data.empresas || []).forEach(empresa => {


        const estadoVenta = Number(empresa?.estado_venta ?? 0);
        const idGuia = getGuiaId(empresa);
        const tieneGuia = !!idGuia;
        const enviadoConGuia = (estadoVenta === 3 && tieneGuia); // guía sólo en estado 3

        const prods = safeParseJSON(empresa.productos);
        const empresaNombre = empresa?.datos_empresa?.nombre || data?.datos_empresa?.nombre || `Empresa #${empresa.id_empresa || ''}`;
        const esRetiroEmpresa = Number(empresa.envio_domicilio) === 1;
        const estadoCuenta = Number(empresa?.estado_cuenta ?? 0);
        const trayectoNombre = !esRetiroEmpresa && empresa?.trayecto && empresa.trayecto[0]
          ? empresa.trayecto[0].nombre
          : '';

        const fechaPedido = (data.created_at || '').split(' ')[0];
        const showPago = estadoVenta === 2;
        const isPagado = Array.isArray(empresa.pagos) && empresa.pagos.length > 0;

        const de = empresa?.datos_empresa || data?.datos_empresa || {};
        let lat = parseFloat(de.latitud), lng = parseFloat(de.longitud), direccionRef = '';
        direccionRef = [de.direccion, de.ciudad, de.provincia].filter(Boolean).join(', ');

        let subtotal = 0;
        let ivaTotal = 0;
        let totalFinal = 0;
        const productosEncoded = encodeURIComponent(empresa?.productos || '[]');
        const idOrdenEmpresa = empresa?.id_orden || empresa?.id_ordenes || data?.id_orden;

        const productosHTML = prods.map(p => {
          const cant = +p.cantidad || 0;
          const precio = +(p.valor_descuento || p.precio || 0);
          const linea = cant * precio;
          const ivaIncluido = Number(p.iva || 0) === 1;
          if (ivaIncluido) {
            const neto = linea / 1.15;
            const ivaCalc = linea - neto;
            subtotal += neto;
            ivaTotal += ivaCalc;
            totalFinal += linea;
          } else {
            subtotal += linea;
            ivaTotal += linea * 0.15;
            totalFinal += linea * 1.15;
          }
          const img = p.imagen || 'img/placeholder.png';
          return `
            <div class="product-item">
              <img class="product-thumb" src="${escapeHtml(img)}" onerror="this.src='img/placeholder.png'">
              <div class="order-info">
                <div class="product-name">${escapeHtml(p.nombre || '')}</div>
                <div class="meta">
                  <span>Vendido por: <strong>${escapeHtml(empresaNombre)}</strong></span>
                  ${ivaIncluido ? ' &nbsp;·&nbsp; <span style="color:#16a34a;font-size:10px;font-weight:700;">IVA incl.</span>' : ''}
                </div>
              </div>
              <div class="ms-auto text-end" style="flex-shrink:0;">
                <div style="font-weight:700;font-size:18px;color:#004E60;">${formatPrecioSuperscript(linea)}</div>
                <small class="text-muted">× ${cant}</small>
              </div>
            </div>
          `;
        }).join('');

        const envioInfo = calcularTarifaDeEmpresa(empresa);
        // Construir HTML del recuadro (según si debe pagar o ya pagó)
        let shippingBoxHTML = '';

        if (esRetiroEmpresa) {
          shippingBoxHTML = `
            <div class="shipping-box success">
              <i class="fi-rs-shop sbox-icon"></i>
              <div class="sbox-content">
                <div class="amount">Retiro en tienda</div>
                <div class="note">Recoge tus productos directamente en <strong>${escapeHtml(empresaNombre)}</strong>.</div>
              </div>
            </div>`;
        } else switch (estadoVenta) {
          case 0:
            shippingBoxHTML = `
            <div class="shipping-box warning">
              <i class="fi-rs-clock sbox-icon"></i>
              <div class="sbox-content">
                <div class="label">Estado del pedido</div>
                <div class="amount">Confirmando peso con la empresa</div>
                <div class="note">La empresa está verificando el peso de los productos para calcular el costo de envío.</div>
              </div>
            </div>`;
            break;

          case 1:
            if (envioInfo.ok) {
              shippingBoxHTML = `
              <div class="shipping-box info">
                <i class="fi-rs-truck-side sbox-icon"></i>
                <div class="sbox-content">
                  <div class="label">Envío a domicilio · ${escapeHtml(envioInfo.nombreTrayecto || '—')}</div>
                  <div class="amount">${envioInfo.peso.toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} kg · Estimado: ${formatPrecioSuperscript(envioInfo.total)}</div>
                  <div class="note">En proceso de confirmación por la empresa.</div>
                </div>
              </div>`;
            }
            break;

          case 2:
            if (isPagado) {
              shippingBoxHTML = `
              <div class="shipping-box success">
                <i class="fi-rs-check-circle sbox-icon"></i>
                <div class="sbox-content">
                  <div class="amount">Pago de envío registrado</div>
                  <div class="note">FULMUV está generando tu guía de despacho con Grupo Entregas.</div>
                </div>
              </div>`;
            } else if (envioInfo.ok) {
              shippingBoxHTML = `
              <div class="shipping-box warning">
                <i class="fi-rs-credit-card sbox-icon"></i>
                <div class="sbox-content">
                  <div class="label">Envío a domicilio — ${escapeHtml(envioInfo.nombreTrayecto || '—')}</div>
                  <div class="amount">Total tarifa: ${formatPrecioSuperscript(envioInfo.totalSeguro)} <small style="font-weight:400;font-size:11px;">(IVA + Seguro incluido)</small></div>
                  <div class="note">
                    ${envioInfo.peso.toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} kg ·
                    ${envioInfo.peso <= 2
                      ? `hasta 2 kg: ${formatPrecioSuperscript(envioInfo.base)} × ${envioInfo.peso.toFixed(2)}`
                      : `${formatPrecioSuperscript(envioInfo.base)} ${envioInfo.kilosExtra > 0 ? `+ ${envioInfo.kilosExtra.toFixed(2)} × ${formatPrecioSuperscript(envioInfo.adicionalKg)} (kilo adicional)` : ''}`
                    }
                  </div>
                </div>
              </div>`;
            }
            break;

          case 3:
            if (tieneGuia) {
              shippingBoxHTML = `
                <div class="shipping-box success">
                  <i class="fi-rs-box sbox-icon"></i>
                  <div class="sbox-content">
                    <div class="amount">¡Pedido en camino!</div>
                    <div class="note">Número de guía Grupo Entregas: <strong style="font-size:14px;">${escapeHtml(String(idGuia))}</strong></div>
                  </div>
                </div>`;
            }
            break;
        }

        const totalPagarProducto = totalFinal;
        const pagoRetiroRealizado = (estadoCuenta === 2 || estadoVenta === 2);

        const pagoFooter = esRetiroEmpresa
          ? (pagoRetiroRealizado
              ? '<span class="badge bg-success"><i class="fi-rs-check me-1"></i>Pago realizado</span>'
              : '<span class="badge bg-warning text-dark"><i class="fi-rs-clock me-1"></i>Pago pendiente al proveedor</span>')
          : (isPagado
              ? '<span class="badge bg-success"><i class="fi-rs-check me-1"></i>Pago de envío registrado</span>'
              : '<span class="badge bg-warning text-dark"><i class="fi-rs-clock me-1"></i>Pago de envío pendiente</span>');

        $cards.append(`
          <div class="order-card">

            <!-- Cabecera oscura -->
            <div class="card-head">
              <div class="kv">
                <span class="k">Pedido Nº</span>
                <span class="v">${escapeHtml(data.numero_orden)}</span>
              </div>
              <div class="kv">
                <span class="k">Fecha</span>
                <span class="v">${fechaPedido}</span>
              </div>
              <div class="kv">
                <span class="k">Empresa</span>
                <span class="v">${escapeHtml(empresaNombre)}</span>
              </div>
              ${!esRetiroEmpresa && trayectoNombre ? `
              <div class="kv">
                <span class="k">Trayecto</span>
                <span class="v">${escapeHtml(trayectoNombre)}</span>
              </div>` : ''}
              <div class="kv ms-auto">
                <span class="k">Total productos</span>
                <span class="v">${formatPrecioSuperscript(totalPagarProducto)}</span>
              </div>
            </div>

            <!-- Barra de pasos -->
            ${stepsHtml(estadoVenta, esRetiroEmpresa)}

            <!-- Cuerpo: columna única -->
            <div class="card-body">
              ${productosHTML || `<div class="text-muted small py-2">Sin productos registrados</div>`}

              <!-- Badge estado debajo de productos -->
              <div class="estado-bar">
                ${badgeEstado(empresa.orden_estado)}
              </div>

              ${shippingBoxHTML}
            </div>

            <!-- Barra de acciones horizontal -->
            <div class="order-actions-bar">
              <button type="button" class="btn-action btn-action-map btn-mapa"
                      data-lat="${isFinite(lat) ? lat : ''}"
                      data-lng="${isFinite(lng) ? lng : ''}"
                      data-dir="${(direccionRef || '').replace(/"/g, '&quot;')}">
                <i class="fi-rs-marker"></i> Localizar paquete
              </button>

              ${(!esRetiroEmpresa && showPago && !isPagado) ? `
              <button type="button" class="btn-action btn-action-pay"
                      onclick="abrirModalPago('${data.numero_orden}','${productosEncoded}',${Number(subtotal) || 0},${idOrdenEmpresa || 0})">
                <i class="fi-rs-credit-card"></i> Pagar envío a domicilio
              </button>` : ''}

              <button type="button" class="btn-action btn-action-detail btn-detalle"
                      onclick="abrirModalDetalle('${data.numero_orden}', ${idOrdenEmpresa || 0})"
                      data-numero="${escapeHtml(data.numero_orden)}">
                <i class="fi-rs-receipt"></i> Ver detalle
              </button>

              ${enviadoConGuia ? `
              <button type="button" class="btn-action btn-action-guide"
                      onclick="abrirModalGuia('${idOrdenEmpresa}', this)">
                <i class="fi-rs-box"></i> Ver guía Grupo Entregas
              </button>` : ''}
            </div>

            <!-- Footer: estado de pago -->
            <div class="order-footer">
              ${pagoFooter}
            </div>
          </div>
        `);
      });
    },
    'json'
  );
}

// Usa SOLO datos del registro de empresa (id_trayecto, peso_total)
function calcularTarifaDeEmpresa(empresa) {
  const idTrayecto = empresa?.id_trayecto;
  const peso = Number(String(empresa?.peso_total ?? empresa?.peso ?? 0).replace(',', '.')) || 0;

  const r = calcularTarifaFulmuv(idTrayecto, peso, 'mercancia_premier');
  const iva = r.total * 0.15;
  const totalConIva = r.total;

  const totalSeguro = totalConIva * 1.1;

  return {
    ok: !!TARIFAS_MAP[String(idTrayecto)],
    idTrayecto,
    nombreTrayecto: r.nombreTrayecto,
    peso,
    base: r.base,
    adicionalKg: r.adicionalKg,
    kilosExtra: r.extraKg,
    total: r.total,
    iva,
    totalConIva,
    totalSeguro
  };
}
function accionesHtmlEmpresa(o, empresa, lat, lng, direccionRef, totalEmpresa) {
  const showPago = (estadoVenta === 2 && !(Array.isArray(empresa.pagos) && empresa.pagos.length > 0));
  const productosEncoded = encodeURIComponent(empresa?.productos || '[]');
  const idOrdenEmpresa = empresa?.id_orden || empresa?.id_ordenes || o?.id_orden;

  return `
    <td class="align-middle text-center">
      <div class="btn-group-vertical btn-group-sm">
        <button type="button"
                class="btn btn-outline-primary btn-mapa"
                title="Ver mapa"
                data-lat="${isFinite(lat) ? lat : ''}"
                data-lng="${isFinite(lng) ? lng : ''}"
                data-dir="${(direccionRef || '').replace(/"/g, '&quot;')}">
          <i class="fi-rs-marker"></i> Mapa
        </button>

        ${showPago ? `
        <button type="button"
                class="btn btn-success"
                title="Subir comprobante"
                onclick="abrirModalPago('${o.numero_orden}', '${productosEncoded}', ${Number(totalEmpresa) || 0}, ${idOrdenEmpresa || 0})">
          <i class="fi-rs-credit-card"></i> Pagar
        </button>` : ''}

        <button type="button"
                class="btn btn-secondary btn-detalle"
                title="Detalle"
                data-numero="${o.numero_orden}">
          <i class="fi-rs-receipt"></i> Detalle
        </button>
      </div>
    </td>
  `;
}


function safeParseJSON(str) {
  try { const j = JSON.parse(str); return Array.isArray(j) ? j : []; } catch { return []; }
}
function getBadgeEstado(estado) {
  const s = String(estado || '').toLowerCase();
  switch (s) {
    case "creada": return `<span class="badge bg-secondary">Creada</span>`;
    case "procesada": return `<span class="badge bg-warning text-dark">Procesada</span>`;
    case "enviada": return `<span class="badge bg-primary">Enviada</span>`;
    case "aprobada": case "completada": return `<span class="badge bg-success">Aprobada</span>`;
    case "eliminada": return `<span class="badge bg-danger">Rechazada</span>`;
    case "pendiente": return `<span class="badge bg-info text-dark">Pendiente</span>`;
    default: return `<span class="badge bg-light text-dark">Desconocido</span>`;
  }
}

function accionesHtml(o, empresa, lat, lng, direccionRef, id_orden) {
  const showPago = Number(empresa?.estado_venta) === 1; // pago sólo si 1
  const productosEncoded = encodeURIComponent(empresa?.productos || '[]'); // <- productos de ESA empresa
  const totalEmpresa = Number(empresa?.total || 0);

  return `
    <td class="text-center">
      <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-primary btn-mapa btn-sm me-2"
                title="Ver mapa"
                data-lat="${isFinite(lat) ? lat : ''}"
                data-lng="${isFinite(lng) ? lng : ''}"
                data-dir="${(direccionRef || '').replace(/"/g, '&quot;')}">
          <i class="fi-rs-marker"></i>
        </button>
        ${showPago ? `
        <button type="button" class="btn btn-success btn-pago btn-sm me-2"
                title="Subir comprobante"
                onclick="abrirModalPago('${o.numero_orden}', '${productosEncoded}', ${totalEmpresa}, ${id_orden})">
          <i class="fi-rs-credit-card"></i>
        </button>` : ''}
        <button type="button" class="btn btn-secondary btn-detalle btn-sm me-2"
                title="Detalle"
                data-numero="${o.numero_orden}">
          <i class="fi-rs-receipt"></i>
        </button>
      </div>
    </td>
  `;
}


function abrirMapaOrden(lat, lng, direccion) {
  const $modal = $('#modalMapaOrden');
  $('#mapaOrdenDireccion').text(direccion || '');
  $modal.modal('show');


  $modal.one('shown.bs.modal', function () {
    const pos = (isFinite(lat) && isFinite(lng)) ? { lat: Number(lat), lng: Number(lng) } : { lat: -1.8312, lng: -78.1834 };
    if (!mapaOrden) {
      mapaOrden = new google.maps.Map(document.getElementById('mapaOrden'), { center: pos, zoom: isFinite(lat) && isFinite(lng) ? 15 : 6 });
      marcadorOrden = new google.maps.Marker({ map: mapaOrden, position: pos });
    } else {
      google.maps.event.trigger(mapaOrden, 'resize');
      mapaOrden.setCenter(pos);
      mapaOrden.setZoom(isFinite(lat) && isFinite(lng) ? 15 : 6);
      marcadorOrden.setPosition(pos);
    }
  });
}
$(document).on('click', '.btn-mapa', function () {
  const lat = parseFloat($(this).data('lat'));
  const lng = parseFloat($(this).data('lng'));
  const dir = $(this).data('dir') || '';
  abrirMapaOrden(lat, lng, dir);
});


function abrirModalPago(numero, productosEncoded, total, id_orden) {
  // título
  $('#numeroOrdenPago').text(numero);

  $("#id_orden_empresaPago").val(id_orden)

  // parsear productos
  let productos = [];
  try { productos = JSON.parse(decodeURIComponent(productosEncoded)); } catch (e) { productos = []; }

  // pintar tabla
  const $tbody = $('#tablaPagoProductos tbody').empty();
  let totalCalc = 0;

  productos.forEach(p => {
    const cant = Number(p.cantidad || 0);
    const precio = Number(p.valor_descuento || p.precio || 0);
    const linea = cant * precio;
    totalCalc += linea;

    $tbody.append(`
      <tr>
        <td>${escapeHtml(p.nombre || '')}</td>
        <td class="text-end">${cant}</td>
        <td class="text-end">${(window.formatoMoneda ? formatoMoneda.format(precio) : formatoMoneda.format(precio))}</td>
        <td class="text-end">${(window.formatoMoneda ? formatoMoneda.format(linea) : formatoMoneda.format(linea))}</td>
      </tr>
    `);
  });

  const totalMostrar = Number(total) || totalCalc;
  const totalIVA = parseFloat(total) * 0.15;
  const totalMos = parseFloat(total) * 1.15;

  $('#totalIVAPago').text(window.formatoMoneda ? formatoMoneda.format(totalIVA) : formatoMoneda.format(totalIVA));
  $('#totalPago').text(window.formatoMoneda ? formatoMoneda.format(totalMos) : formatoMoneda.format(totalMos));

  renderTarifaEnvioEnModal(id_orden); // <-- calcula con peso de orden_iso y pinta la tabla
  // abrir modal (Bootstrap 5)
  $("#modalPagoOrden").modal("show")
}


function abrirModalDetalle(numero, idOrdenEmpresa) {
  const d = ordenDataGlobal;
  if (!d) {
    console.warn('No hay datos de la orden aún');
    return;
  }

  // Buscar la empresa/registro seleccionado
  const em = (d.empresas || []).find(e =>
    String(e.id_orden) === String(idOrdenEmpresa) ||
    String(e.id_ordenes) === String(idOrdenEmpresa)
  );

  if (!em) {
    Swal.fire({ icon: 'warning', title: 'FULMUV', text: 'No se encontró el registro de empresa.' });
    return;
  }

  const nombreEmp = em?.datos_empresa?.nombre || d?.datos_empresa?.nombre || '—';
  const productos = safeParseJSON(em.productos);
  const fechaPed = (d.created_at || '').replace(' 00:00:00', '');
  let subtotal = 0;
  let ivaTotal = 0;
  let totalFinal = 0;

  let html = `
    <div class="mb-2"><strong>Orden:</strong> ${escapeHtml(d.numero_orden || '')}</div>
    <div class="mb-2"><strong>Fecha:</strong> ${escapeHtml(fechaPed)}</div>
    <div class="mb-2"><strong>Empresa:</strong> ${escapeHtml(nombreEmp)}</div>

    <div class="table-responsive">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-end">Cant.</th>
            <th class="text-end">Precio</th>
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody>`;

  productos.forEach(p => {
    const cant = Number(p.cantidad || 0);
    const precio = Number(p.valor_descuento || p.precio || 0);
    const total = cant * precio;
    const ivaIncluido = Number(p.iva || 0) === 1;
    if (ivaIncluido) {
      const neto = total / 1.15;
      const ivaCalc = total - neto;
      subtotal += neto;
      ivaTotal += ivaCalc;
      totalFinal += total;
    } else {
      subtotal += total;
      ivaTotal += total * 0.15;
      totalFinal += total * 1.15;
    }

    html += `
      <tr>
        <td>${escapeHtml((p.nombre || '').trim())}</td>
        <td class="text-end">${cant}</td>
        <td class="text-end">${formatoMoneda.format(precio)}</td>
        <td class="text-end">${formatoMoneda.format(total)}</td>
      </tr>`;
  });

  html += `
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">Subtotal</th>
            <th class="text-end">${formatoMoneda.format(subtotal)}</th>
          </tr>
          <tr>
            <th colspan="3" class="text-end">IVA (15%)</th>
            <th class="text-end">${formatoMoneda.format(ivaTotal)}</th>
          </tr>
          <tr>
            <th colspan="3" class="text-end">Total</th>
            <th class="text-end">${formatoMoneda.format(totalFinal)}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // Pintar y abrir modal
  document.getElementById('detalleContenido').innerHTML = html;
  $("#btnModal").click();
}

function renderTarifaEnvioEnModal(id_orden_empresa) {
  const d = ordenDataGlobal;

  const $tbody = $('#tablaTarifaEnvio tbody').empty();
  const $tfoot = $('#tablaTarifaEnvio tfoot').empty();

  const empresaMatch = (d?.empresas || []).find(em =>
    String(em.id_orden) === String(id_orden_empresa) ||
    String(em.id_ordenes) === String(id_orden_empresa)
  );

  if (!empresaMatch) {
    $tbody.append('<tr><td colspan="3" class="text-center text-muted">Sin datos de envío</td></tr>');
    $tfoot.append(`<tr><th colspan="2" class="text-end">TOTAL A PAGAR POR LA TARIFA</th><th class="text-end">${formatoMoneda.format(0)}</th></tr>`);
    $('#notaTarifaDeposito').addClass('d-none');
    $('#montoDeposito').text('—');
    return;
  }

  const peso = Number(String(empresaMatch.peso_total ?? empresaMatch.peso ?? 0).replace(',', '.')) || 0;
  const idTrayecto = empresaMatch.id_trayecto ?? d?.id_trayecto;
  const { total, base, adicionalKg, extraKg, nombreTrayecto } =
    calcularTarifaFulmuv(idTrayecto, peso, 'mercancia_premier');

  // ...tu $tbody.append anterior...

  // === SEGURO (11%) ===
  const porcentajeSeguro = 1.1;
  const totalFinalEnvio = total * porcentajeSeguro;      // 85.11 * 0.11
  const seguroEnvio = totalFinalEnvio - total ;       // 85.11 * 1.11

  $tbody.append(`
  <tr>
    <td>${escapeHtml(nombreTrayecto || '—')}</td>
    <td class="text-end">${peso.toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} kg</td>
    <td class="text-end">${formatoMoneda.format(total)}</td>
  </tr>

  <tr>
    <td colspan="3" class="small text-muted">
      ${peso <= 2
      ? `Base: ${formatoMoneda.format(base)} (hasta 2 kg)`
      : `Base: ${formatoMoneda.format(base)} + ${extraKg.toFixed(2)} × ${formatoMoneda.format(adicionalKg)} (kg adicional)`
    }
    </td>
  </tr>

  <!-- ✅ FILA DE SEGURO -->
  <tr>
    <td><strong>Seguro de envío (1.1)</strong></td>
    <td class="text-end">—</td>
    <td class="text-end"><strong>${formatoMoneda.format(seguroEnvio)}</strong></td>
  </tr>
`);

  const iva = total * 0.15; // (lo tienes comentado)
  const totalConIva = total;

  // ✅ FOOTER: total final (tarifa + seguro)
  $tfoot.append(`
    <tr>
      <th colspan="2" class="text-end">TOTAL A PAGAR POR LA TARIFA + ENVÍO</th>
      <th class="text-end">${formatoMoneda.format(totalFinalEnvio)}</th>
    </tr>
    <!--tr><th colspan="2" class="text-end">IVA (15%)</th><th class="text-end">${formatoMoneda.format(iva)}</th></tr-->
    <!--tr><th colspan="2" class="text-end">TOTAL TARIFA</th><th class="text-end">${formatoMoneda.format(totalConIva)}</th></tr-->
  `);

}


// Devuelve el id_guia si viene como objeto o array
function getGuiaId(empresa) {
  if (!empresa) return null;
  if (Array.isArray(empresa.guia) && empresa.guia.length) {
    return empresa.guia[0]?.id_guia || null;
  }
  if (empresa.guia && typeof empresa.guia === 'object') {
    return empresa.guia.id_guia || null;
  }
  return null;
}

// Abre modal y carga el PDF en iframe + spinner en el botón mientras carga
function abrirModalGuia(id_orden_empresa, btnEl) {
  if (!id_orden_empresa) {
    Swal.fire({ icon: 'warning', title: 'FulMuv', text: 'No hay guía para mostrar.' });
    return;
  }

  // --- preparar botón con spinner ---
  const $btn = btnEl ? $(btnEl) : null;
  let originalHTML = '';
  if ($btn && !$btn.prop('disabled')) {
    originalHTML = $btn.html();
    $btn.prop('disabled', true)
      .html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cargando…`);
  }

  // reset inicial del modal/iframe
  $('#modalGuiaId').text(id_orden_empresa);
  const $iframe = $('#iframeGuia');
  $iframe.attr('src', '');
  $('#linkGuiaNuevaPestana').attr('href', '#');

  // cuando el iframe termine de cargar (PDF listo), quitamos el spinner
  const onFinish = () => {
    if ($btn) {
      $btn.prop('disabled', false).html(originalHTML);
    }
  };
  $iframe.off('load').one('load', onFinish);

  // llamada a la API
  $.post('api/v1/fulmuv/getPDFGUIAA4', { id_orden_empresa: id_orden_empresa }, function (r) {
    if (r?.error === false) {
      const dataUrl = r.data[0].url_grupoentrega;
      $iframe.attr('src', dataUrl);
      $('#linkGuiaNuevaPestana').attr('href', dataUrl);
      $('#modalGuiaServi').modal('show');
      // si por alguna razón el iframe no dispara load (p.ej. bloqueo), quitamos spinner a los 3s
      setTimeout(onFinish, 3000);
    } else {
      onFinish();
      Swal.fire({ icon: 'error', title: 'FulMuv', text: r?.msj || 'No se pudo obtener el PDF de la guía.' });
    }
  }, 'json').fail(function () {
    onFinish();
    Swal.fire({ icon: 'error', title: 'FulMuv', text: 'Error al consultar la guía.' });
  });
}



// function eliminarArchivo(nombreArchivo) {
//     // Buscar archivo por nombre
//     const archivo = archivosPendientes.find(f => f.name === nombreArchivo);
//     if (!archivo) return;

//     // Remover de Dropzone (para mantenerlo sincronizado visualmente)
//     dropzone.removeFile(archivo);

//     // Remover del array
//     archivosPendientes = archivosPendientes.filter(f => f.name !== nombreArchivo);

//     // Volver a renderizar la lista
//     renderListaArchivos();
// }
// Subir archivos al hacer clic en "Guardar Pago"
// document.getElementById("guardarPago").addEventListener("click", function () {
//     if (archivosPendientes.length === 0) {
//         Swal.fire({
//             icon: 'warning',
//             title: 'FulMuv',
//             text: 'No hay archivos para subir.',
//             showConfirmButton: false
//         })
//         return;
//     }

//     const formData = new FormData();

//     archivosPendientes.forEach(file => {
//         formData.append("archivos[]", file);
//     });

//     fetch("cargar_imagen_pago.php", {
//         method: "POST",
//         body: formData
//     })
//         .then(res => res.json())
//         .then(data => {
//             if (data.response === "success") {
//                 console.log("Archivos subidos correctamente:");
//                 console.log(data.data); // <- array con las rutas y tipo
//                 Swal.fire({
//                     icon: 'success',
//                     title: 'FulMuv',
//                     text: 'Archivos guardados correctamente.',
//                     timer: 2000,
//                     showConfirmButton: false
//                 });

//                 // ✅ Opcional: limpiar Dropzone y lista
//                 archivosPendientes = [];
//                 dropzone.removeAllFiles(true);
//                 renderListaArchivos();
//             }
//         })
//         .catch(err => {
//             console.error("Error en la subida:", err);
//             Swal.fire({
//                 icon: 'warning',
//                 title: 'FulMuv',
//                 text: 'Error al subir archivos.',
//                 showConfirmButton: false
//             })
//         });
// });
