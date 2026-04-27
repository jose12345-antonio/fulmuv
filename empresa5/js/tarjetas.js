const walletState = {
  tarjetas: [],
  editingToken: null,
  editingWasDefault: false
};

function walletTranslateCardStatus(rawStatus) {
  const status = String(rawStatus || '').toLowerCase().trim();
  if (!status) return '';
  if (status === 'valid') return 'Valida';
  if (status === 'review') return 'En revision';
  if (status === 'pending') return 'Pendiente de verificacion';
  if (status === 'rejected') return 'Rechazada';
  if (status === 'local') return 'Registrada localmente';
  return rawStatus;
}

function walletTranslateTransactionStatus(rawStatus) {
  const status = String(rawStatus || '').toLowerCase().trim();
  if (!status) return '';
  if (status === 'success') return 'Aprobada';
  if (status === 'failure') return 'Fallida';
  if (status === 'pending') return 'Pendiente';
  return rawStatus;
}

function walletTranslateCurrentStatus(rawStatus) {
  const status = String(rawStatus || '').toUpperCase().trim();
  if (!status) return '';
  if (status === 'APPROVED') return 'Aprobada';
  if (status === 'PENDING') return 'Pendiente';
  if (status === 'REJECTED') return 'Rechazada';
  if (status === 'CANCELLED') return 'Cancelada';
  if (status === 'EXPIRED') return 'Expirada';
  if (status === 'INITIATED') return 'Iniciada';
  return rawStatus;
}

function walletTranslateStatusDetail(rawDetail) {
  const detail = Number(rawDetail);
  if (Number.isNaN(detail)) return '';

  const detailMap = {
    0: 'Esperando pago.',
    1: 'Transaccion en revision o verificacion requerida.',
    2: 'Pago parcial.',
    3: 'Pago aprobado.',
    4: 'En disputa.',
    5: 'Pago excedido.',
    6: 'Fraude.',
    7: 'Reembolso.',
    8: 'Contracargo.',
    9: 'Rechazada por el emisor o banco.',
    10: 'Error del sistema.',
    11: 'Rechazada por sistema antifraude de Nuvei.',
    12: 'Tarjeta en lista negra de Nuvei.',
    13: 'Fuera de tiempo permitido.',
    14: 'Expirada por Nuvei.',
    15: 'Expirada por el emisor.',
    16: 'Rechazada por Nuvei.',
    17: 'Abandonada en Nuvei.',
    18: 'Abandonada por el cliente.',
    19: 'Codigo de autorizacion invalido.',
    20: 'Codigo de autorizacion expirado.',
    21: 'Fraude Nuvei - reembolso pendiente.',
    22: 'Codigo invalido - reembolso pendiente.',
    23: 'Codigo expirado - reembolso pendiente.',
    24: 'Fraude Nuvei - reembolso solicitado.',
    25: 'Codigo invalido - reembolso solicitado.',
    26: 'Codigo expirado - reembolso solicitado.',
    27: 'Comercio - reembolso pendiente.',
    28: 'Comercio - reembolso solicitado.',
    29: 'Transaccion anulada.',
    30: 'Transaccion asentada.',
    31: 'Esperando OTP.',
    32: 'OTP validado correctamente.',
    33: 'OTP no validado.',
    34: 'Reembolso parcial.',
    35: 'Se solicito metodo 3DS, esperando continuar.',
    36: 'Se solicito desafio 3DS.',
    37: 'Rechazada por 3DS.',
    47: 'Fallo en validacion CPF.',
    48: 'Autenticada por 3DS.'
  };

  return detailMap[detail] || '';
}

function walletTranslateGatewayMessage(rawMessage) {
  const message = String(rawMessage || '').trim();
  if (!message) return '';

  const normalized = message.toLowerCase();
  const knownMessages = {
    'charge succeeds': 'El cargo fue aprobado correctamente.',
    'charge is under review': 'El cargo quedo en revision.',
    'not authorized': 'La transaccion fue rechazada: no autorizada.',
    'rejected by fraud system': 'La transaccion fue rechazada por el sistema antifraude.',
    'card in black list': 'La tarjeta se encuentra en lista negra.',
    'card already added': 'La tarjeta ya estaba agregada previamente.'
  };

  return knownMessages[normalized] || message;
}

function walletResultAlertMeta(response) {
  const cardStatus = String(response?.card?.status || '').toLowerCase().trim();
  const trxStatus = String(response?.transaction?.status || '').toLowerCase().trim();
  const statusDetail = Number(response?.transaction?.status_detail);

  if (cardStatus === 'valid' || trxStatus === 'success' || statusDetail === 3) {
    return {
      title: 'Tarjeta guardada',
      text: 'La tarjeta se guardo correctamente.',
      type: 'success'
    };
  }

  if (cardStatus === 'review' || cardStatus === 'pending' || trxStatus === 'pending' || statusDetail === 1) {
    return {
      title: 'Tarjeta en revision',
      text: 'La tarjeta fue guardada y quedo en revision.',
      type: 'warning'
    };
  }

  if (cardStatus === 'rejected' || trxStatus === 'failure' || [9, 11, 12, 16, 37].includes(statusDetail)) {
    return {
      title: 'Tarjeta rechazada',
      text: 'La tarjeta fue rechazada.',
      type: 'error'
    };
  }

  return {
    title: 'Resultado de tarjeta',
    text: 'La tarjeta fue procesada.',
    type: 'info'
  };
}

function walletBuildGatewayDetail(response, fallbackMessage = 'No se pudo procesar la tarjeta.') {
  const lines = [];
  const trx = response?.transaction || {};
  const card = response?.card || trx?.card || {};
  const error = response?.error || {};

  const pushLine = (label, value) => {
    const clean = String(value || '').trim();
    if (clean) lines.push(`${label}: ${clean}`);
  };

  const cardStatus = walletTranslateCardStatus(card.status);
  const trxStatus = walletTranslateTransactionStatus(trx.status);
  const currentStatus = walletTranslateCurrentStatus(trx.current_status);
  const statusDetailText = walletTranslateStatusDetail(trx.status_detail || card.status_detail);
  const gatewayMessage = walletTranslateGatewayMessage(card.message || trx.message);
  const errorDescription = walletTranslateGatewayMessage(error.description || error.help || error.type);

  pushLine('Estado tarjeta', cardStatus);
  pushLine('Estado transaccion', trxStatus || currentStatus);
  pushLine('Detalle Nuvei', statusDetailText || gatewayMessage);
  pushLine('Respuesta banco', trx.carrier_code || trx.authorization_code || error.code);
  pushLine('Descripcion', errorDescription);
  pushLine('Codigo detalle', trx.status_detail || card.status_detail);
  pushLine('Referencia', trx.id || card.transaction_reference);

  return lines.length ? lines.join('\n') : fallbackMessage;
}

function walletBrandName(rawType) {
  const t = String(rawType || '').toLowerCase().trim();
  if (t === 'vi' || t.includes('visa')) return 'Visa';
  if (t === 'mc' || t.includes('master')) return 'Mastercard';
  if (t === 'ax' || t.includes('amex') || t.includes('american')) return 'American Express';
  if (t === 'dc' || t.includes('diners')) return 'Diners Club';
  if (t === 'di' || t.includes('discover')) return 'Discover';
  return 'Tarjeta';
}

function walletBrandIcon(rawType) {
  const t = String(rawType || '').toLowerCase().trim();
  if (t === 'vi' || t.includes('visa')) return '<i class="fab fa-cc-visa" style="color:#f8d277;"></i>';
  if (t === 'mc' || t.includes('master')) return '<i class="fab fa-cc-mastercard" style="color:#111827;"></i>';
  if (t === 'ax' || t.includes('amex') || t.includes('american')) return '<i class="fab fa-cc-amex" style="color:#ffffff;"></i>';
  if (t === 'dc' || t.includes('diners')) return '<i class="fab fa-cc-diners-club" style="color:#ffffff;"></i>';
  if (t === 'di' || t.includes('discover')) return '<i class="fab fa-cc-discover" style="color:#ffffff;"></i>';
  return '<i class="fas fa-credit-card" style="color:#ffffff;"></i>';
}

function walletExpiryLabel(expMonth, expYear) {
  if (!expMonth || !expYear) return 'Sin fecha';
  return `${String(expMonth).padStart(2, '0')}/${String(expYear).slice(-2)}`;
}

function walletStatusMeta(rawStatus) {
  const status = String(rawStatus || '').toLowerCase().trim();
  if (!status || status === 'valid' || status === 'local') {
    return null;
  }

  if (status.includes('review') || status.includes('pending') || status.includes('process')) {
    return {
      label: status.includes('pending') ? 'Pendiente' : 'En revision',
      className: 'warning',
      icon: 'fas fa-clock'
    };
  }

  if (status.includes('reject') || status.includes('invalid') || status.includes('deny')) {
    return {
      label: 'Rechazada',
      className: 'expired',
      icon: 'fas fa-ban'
    };
  }

  return {
    label: rawStatus,
    className: '',
    icon: 'fas fa-info-circle'
  };
}

function walletGradient(rawType) {
  const t = String(rawType || '').toLowerCase().trim();
  if (t === 'vi' || t.includes('visa')) return 'linear-gradient(135deg, #072d43 0%, #0c3f59 62%, #0a2238 100%)';
  if (t === 'mc' || t.includes('master')) return 'linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 55%, #94a3b8 100%)';
  if (t === 'ax' || t.includes('amex') || t.includes('american')) return 'linear-gradient(135deg, #06b6d4 0%, #2563eb 100%)';
  if (t === 'dc' || t.includes('diners')) return 'linear-gradient(135deg, #0f766e 0%, #4338ca 100%)';
  if (t === 'di' || t.includes('discover')) return 'linear-gradient(135deg, #f43f5e 0%, #f59e0b 100%)';
  return 'linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%)';
}

function walletBrandClass(rawType) {
  const t = String(rawType || '').toLowerCase().trim();
  if (t === 'vi' || t.includes('visa')) return 'wallet-brand-visa';
  if (t === 'mc' || t.includes('master')) return 'wallet-brand-mastercard';
  return '';
}

function walletTextClass(rawType) {
  const t = String(rawType || '').toLowerCase().trim();
  if (t === 'mc' || t.includes('master')) return 'text-dark';
  return '';
}

function walletSafeToken(token) {
  return String(token || '').replace(/'/g, "\\'");
}

function walletHolderName(tarjeta) {
  return String(tarjeta.holder_name || tarjeta.nombre_titular || 'Tarjeta registrada').trim().toUpperCase();
}

function walletParseJson(raw) {
  if (typeof raw === 'string') {
    try {
      return JSON.parse(raw);
    } catch (error) {
      return null;
    }
  }
  return raw;
}

function walletExtractCardToken(response) {
  const candidates = [
    response?.card?.token,
    response?.transaction?.card?.token,
    response?.card?.id,
    response?.transaction?.card?.id
  ];

  for (let i = 0; i < candidates.length; i += 1) {
    const token = String(candidates[i] || '').trim();
    if (token) {
      return token;
    }
  }

  const errorType = String(response?.error?.type || '');
  const tokenFromMessage = errorType.match(/Card already added[^:]*:\s*(\S+)/i)?.[1];
  return String(tokenFromMessage || '').trim();
}

function walletExtractTransactionReference(response) {
  const candidates = [
    response?.card?.transaction_reference,
    response?.transaction?.id,
    response?.transaction?.dev_reference
  ];

  for (let i = 0; i < candidates.length; i += 1) {
    const ref = String(candidates[i] || '').trim();
    if (ref) {
      return ref;
    }
  }

  return '';
}

function walletGatewayUserId() {
  const email = ($('#correo_empresa_wallet').val() || '').trim().toLowerCase();
  const idEmpresa = ($('#id_empresa').val() || '').trim();
  return email || idEmpresa;
}

function walletClientConfig() {
  return {
    environment: 'stg',
    application_code: 'TESTECUADORSTG-EC-CLIENT',
    application_key: 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6'
  };
}

function cargarWalletTarjetas() {
  const idEmpresa = $('#id_empresa').val();
  $('#walletList').html('<div class="text-center py-4 text-600"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Cargando tarjetas...</div>');

  $.get(`../api/v1/fulmuv/empresa/wallet/${idEmpresa}`, function (raw) {
    const response = walletParseJson(raw);
    walletState.tarjetas = response?.data || [];
    renderWalletTarjetas();
  }).fail(function () {
    $('#walletList').html('<div class="alert alert-danger mb-0">No se pudieron cargar las tarjetas registradas.</div>');
  });
}

function walletNormalizeBrandKey(rawType) {
  const brand = String(rawType || '').toLowerCase().trim();
  if (brand.includes('visa') || brand === 'vi') return 'visa';
  if (brand.includes('master') || brand === 'mc') return 'mastercard';
  if (brand.includes('american') || brand.includes('amex') || brand === 'ax') return 'amex';
  if (brand.includes('diners') || brand === 'dc') return 'diners';
  if (brand.includes('discover') || brand === 'di') return 'discover';
  return brand;
}

function walletFindCardFromGateway(cardData) {
  const idEmpresa = $('#id_empresa').val();
  const beforeTokens = new Set((walletState.tarjetas || []).map((item) => String(item.token || '').trim()).filter(Boolean));

  return $.get(`../api/v1/fulmuv/empresa/wallet/${idEmpresa}`).then(function (raw) {
    const response = walletParseJson(raw);
    const cards = response?.data || [];
    const lastFour = String(cardData?.ultimos_digitos || '').slice(-4);
    const expMonth = String(cardData?.exp_month || '').padStart(2, '0');
    const expYear = String(cardData?.exp_year || '');
    const brandKey = walletNormalizeBrandKey(cardData?.marca || '');

    let candidates = cards.filter(function (card) {
      const cardToken = String(card.token || '').trim();
      const cardLastFour = String(card.ultimos_digitos || '').slice(-4);
      const cardMonth = String(card.exp_month || '').padStart(2, '0');
      const cardYear = String(card.exp_year || '');
      const cardBrand = walletNormalizeBrandKey(card.marca || '');

      return (
        cardToken &&
        !beforeTokens.has(cardToken) &&
        (!lastFour || cardLastFour === lastFour) &&
        (!expMonth || cardMonth === expMonth) &&
        (!expYear || cardYear === expYear) &&
        (!brandKey || cardBrand === brandKey)
      );
    });

    if (candidates.length === 0 && lastFour) {
      candidates = cards.filter(function (card) {
        const cardToken = String(card.token || '').trim();
        return cardToken && !beforeTokens.has(cardToken) && String(card.ultimos_digitos || '').slice(-4) === lastFour;
      });
    }

    if (candidates.length === 1) {
      return {
        token: String(candidates[0].token || '').trim(),
        transaction_reference: ''
      };
    }

    return null;
  });
}

function resetWalletEditMode() {
  walletState.editingToken = null;
  walletState.editingWasDefault = false;
  $('#walletModeBadge').addClass('d-none').html('');
  $('#walletCancelEdit').addClass('d-none');
  $('#tokenize_btn').html('<i class="fas fa-plus-circle me-2"></i>Guardar tarjeta');
  $('#tokenize_response').html('');
}

function activarEdicionTarjeta(token) {
  const tarjeta = (walletState.tarjetas || []).find((item) => String(item.token || '') === String(token || ''));
  if (!tarjeta) {
    swal('Error', 'No se encontro la tarjeta seleccionada.', 'error');
    return;
  }

  walletState.editingToken = token;
  walletState.editingWasDefault = tarjeta.es_default === 'Y';

  $('#walletModeBadge')
    .removeClass('d-none')
    .html('<i class="fas fa-sync-alt me-1"></i>Modo reemplazo: registra una nueva tarjeta para sustituir la seleccionada.');
  $('#walletCancelEdit').removeClass('d-none');
  $('#tokenize_btn').html('<i class="fas fa-sync-alt me-2"></i>Reemplazar tarjeta');
  $('#tokenize_response').html(`<span class="text-info">Se reemplazara la tarjeta terminada en ${String(tarjeta.ultimos_digitos || '').slice(-4)}.</span>`);

  const tokenizeEl = document.getElementById('tokenize_example');
  if (tokenizeEl && typeof tokenizeEl.scrollIntoView === 'function') {
    tokenizeEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

function renderWalletTarjetas() {
  const contenedor = $('#walletList');
  const tarjetas = walletState.tarjetas || [];

  if (!tarjetas.length) {
    contenedor.html(`
      <div class="wallet-empty">
        <i class="fas fa-credit-card fa-2x mb-3 d-block opacity-50"></i>
        No hay tarjetas guardadas todavia.<br>
        Usa el formulario de la derecha para registrar la primera.
      </div>
    `);
    return;
  }

  const totalActivas = tarjetas.length;
  const html = tarjetas.map((tarjeta) => {
    const isDefault = tarjeta.es_default === 'Y';
    const isExpired = !!tarjeta.is_expired;
    const lastFour = String(tarjeta.ultimos_digitos || '').slice(-4) || '****';
    const canDelete = totalActivas > 1;
    const statusMeta = walletStatusMeta(tarjeta.status);
    const safeToken = walletSafeToken(tarjeta.token);
    const dropdownId = `walletMenu${Math.random().toString(36).slice(2, 9)}`;
    const brandClass = walletBrandClass(tarjeta.marca);
    const textClass = walletTextClass(tarjeta.marca);

    return `
      <div class="wallet-card-visual ${brandClass} ${textClass} ${isDefault ? 'is-default' : ''} ${isExpired ? 'is-expired' : ''}" style="background:${walletGradient(tarjeta.marca)};">
        <div class="wallet-card-top">
          <div class="wallet-card-statuses">
            ${isDefault ? '<span class="wallet-card-pill"><i class="fas fa-star"></i> Predeterminada</span>' : ''}
            ${isExpired ? '<span class="wallet-card-pill"><i class="fas fa-exclamation-circle"></i> Vencida</span>' : ''}
            ${statusMeta ? `<span class="wallet-card-pill"><i class="${statusMeta.icon}"></i> ${statusMeta.label}</span>` : ''}
          </div>
          <div class="wallet-card-brand" title="${walletBrandName(tarjeta.marca)}">${walletBrandIcon(tarjeta.marca)}</div>
        </div>
        <div class="wallet-card-number">**** &nbsp;••••&nbsp; •••• &nbsp;${lastFour}</div>
        <div class="wallet-card-meta">
          <div>
            <span class="wallet-card-holder-label">Titular</span>
            <div class="wallet-card-holder-value">${walletHolderName(tarjeta)}</div>
          </div>
          <div class="text-end">
            <span class="wallet-card-exp-label">Vence</span>
            <div class="wallet-card-exp-value">${walletExpiryLabel(tarjeta.exp_month, tarjeta.exp_year)}</div>
          </div>
        </div>
        <div class="dropdown wallet-card-menu">
          <button class="btn btn-sm" type="button" id="${dropdownId}" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="${dropdownId}">
            <li>
              <button class="dropdown-item" type="button" onclick="activarEdicionTarjeta('${safeToken}')">
                <i class="fas fa-sync-alt text-info"></i>Reemplazar tarjeta
              </button>
            </li>
            <li>
              ${
                isDefault
                  ? `<button class="dropdown-item" type="button" disabled>
                      <i class="fas fa-check text-success"></i>Tarjeta predeterminada
                    </button>`
                  : `<button class="dropdown-item" type="button" onclick="marcarTarjetaDefault('${safeToken}')">
                      <i class="fas fa-star text-warning"></i>Marcar como predeterminada
                    </button>`
              }
            </li>
            <li>
              <button class="dropdown-item ${canDelete ? 'text-danger' : ''}" type="button" ${canDelete ? `onclick="eliminarTarjetaWallet('${safeToken}')"` : 'disabled'}>
                <i class="fas fa-trash-alt ${canDelete ? 'text-danger' : 'text-muted'}"></i>${canDelete ? 'Eliminar tarjeta' : 'Debes conservar una tarjeta'}
              </button>
            </li>
          </ul>
        </div>
      </div>
    `;
  }).join('');

  contenedor.html(`<div class="wallet-card-grid">${html}</div>`);
}

function marcarTarjetaDefaultRequest(token) {
  return $.post('../api/v1/fulmuv/empresa/wallet/default', {
    id_empresa: $('#id_empresa').val(),
    token: token
  });
}

function marcarTarjetaDefault(token) {
  marcarTarjetaDefaultRequest(token).done(function (raw) {
    const response = walletParseJson(raw);
    if (response && response.error === false) {
      swal('Correcto', response.msg || 'Tarjeta predeterminada actualizada.', 'success');
      cargarWalletTarjetas();
      return;
    }
    swal('Error', response?.msg || 'No se pudo actualizar la tarjeta predeterminada.', 'error');
  }).fail(function () {
    swal('Error', 'No se pudo conectar con el servidor.', 'error');
  });
}

function eliminarTarjetaWalletRequest(token) {
  return $.post('../api/v1/fulmuv/empresa/wallet/delete', {
    id_empresa: $('#id_empresa').val(),
    token: token
  });
}

function eliminarTarjetaWallet(token) {
  swal({
    title: 'Eliminar tarjeta',
    text: 'La tarjeta se eliminara tambien en Paymentez. Esta accion no se puede deshacer.',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d9534f',
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar',
    closeOnConfirm: false
  }, function () {
    eliminarTarjetaWalletRequest(token).done(function (raw) {
      const response = walletParseJson(raw);
      if (response && response.error === false) {
        swal('Correcto', response.msg || 'Tarjeta eliminada correctamente.', 'success');
        cargarWalletTarjetas();
        return;
      }
      swal('Error', response?.msg || 'No se pudo eliminar la tarjeta.', 'error');
    }).fail(function () {
      swal('Error', 'No se pudo conectar con el servidor.', 'error');
    });
  });
}

function guardarTarjetaWallet(token, transactionReference, cardData) {
  return $.post('../api/v1/fulmuv/venta/recurrente/', {
    token: token,
    transaction_reference: transactionReference || '',
    id_usuario: $('#id_principal').val(),
    id_empresa: $('#id_empresa').val(),
    ultimos_digitos: cardData.ultimos_digitos || '',
    marca: cardData.marca || '',
    exp_year: cardData.exp_year || '',
    exp_month: cardData.exp_month || ''
  });
}

function reemplazarTarjetaWallet(oldToken, newToken, cardData) {
  return guardarTarjetaWallet(newToken, '', cardData || {}).then(function (raw) {
    const saved = walletParseJson(raw);
    if (!saved || saved.error !== false) {
      throw new Error(saved?.msg || 'No se pudo guardar la nueva tarjeta.');
    }

    const oldCard = (walletState.tarjetas || []).find((item) => String(item.token || '') === String(oldToken || ''));

    const marcarDefaultPromise = ((oldCard?.es_default === 'Y') || walletState.editingWasDefault) && String(oldToken) !== String(newToken)
      ? marcarTarjetaDefaultRequest(newToken).then(function (defaultRaw) {
          const defaultResp = walletParseJson(defaultRaw);
          if (!defaultResp || defaultResp.error) {
            throw new Error(defaultResp?.msg || 'No se pudo marcar la nueva tarjeta como predeterminada.');
          }
        })
      : $.Deferred().resolve().promise();

    return marcarDefaultPromise.then(function () {
      if (String(oldToken) === String(newToken)) {
        return { reusedExistingToken: true };
      }

      return eliminarTarjetaWalletRequest(oldToken).then(function (deleteRaw) {
        const deleteResp = walletParseJson(deleteRaw);
        if (!deleteResp || deleteResp.error) {
          throw new Error(deleteResp?.msg || 'La nueva tarjeta se guardo, pero no se pudo eliminar la anterior.');
        }
        return { reusedExistingToken: false };
      });
    });
  });
}

function inicializarFormularioTarjeta() {
  const submitButton = document.getElementById('tokenize_btn');
  const responseEl = document.getElementById('tokenize_response');
  const gatewayUserId = walletGatewayUserId();
  const email = ($('#correo_empresa_wallet').val() || '').trim().toLowerCase();
  const config = walletClientConfig();

  if (typeof PaymentGateway === 'undefined') {
    responseEl.innerHTML = '<span class="text-danger">No se pudo cargar el SDK seguro de Paymentez.</span>';
    submitButton.setAttribute('disabled', 'disabled');
    return;
  }

  if (!gatewayUserId || !email) {
    responseEl.innerHTML = '<span class="text-danger">La empresa debe tener un correo registrado para guardar tarjetas.</span>';
    submitButton.setAttribute('disabled', 'disabled');
    return;
  }

  const pg_sdk = new PaymentGateway(config.environment, config.application_code, config.application_key);

  const getTokenizeData = () => ({
    locale: 'es',
    user: {
      id: gatewayUserId,
      email: email
    },
    configuration: {
      default_country: 'ECU'
    },
    conf: {
      style_version: 2
    }
  });

  const resetButton = () => {
    submitButton.innerHTML = walletState.editingToken
      ? '<i class="fas fa-sync-alt me-2"></i>Reemplazar tarjeta'
      : '<i class="fas fa-plus-circle me-2"></i>Guardar tarjeta';
    submitButton.removeAttribute('disabled');
  };

  const notCompletedFormCallback = (message) => {
    responseEl.innerHTML = `<span class="text-danger">Completa el formulario: ${message}</span>`;
    swal('Formulario incompleto', `Completa el formulario: ${message}`, 'warning');
    resetButton();
  };

  const responseCallback = (response) => {
    const usableToken = walletExtractCardToken(response);
    const transactionReference = walletExtractTransactionReference(response);
    const cardData = {
      ultimos_digitos: String(response.card?.number || response.transaction?.card?.number || '').replace(/\D/g, '').slice(-4),
      marca: walletBrandName(response.card?.type || response.transaction?.card?.type || ''),
      exp_year: response.card?.expiry_year || response.transaction?.card?.expiry_year || '',
      exp_month: response.card?.expiry_month || response.transaction?.card?.expiry_month || ''
    };

    if (response.card) {
      const cardStatus = String(response.card.status || '').toLowerCase().trim();
      const hasToken = usableToken !== '';
      const isValid = cardStatus === 'valid';
      const isReview = cardStatus !== '' && !isValid;

      if (!hasToken) {
        const gatewayDetail = walletBuildGatewayDetail(response, 'Nuvei no devolvio token en esta respuesta. Intentando recuperar la tarjeta desde el wallet del gateway...');
        responseEl.innerHTML = `<span class="text-warning">${gatewayDetail.replace(/\n/g, '<br>')}</span>`;
        console.warn('Wallet tokenize response without reusable token', response);

        walletFindCardFromGateway(cardData)
          .then(function (gatewayCard) {
            if (!gatewayCard?.token) {
              const finalDetail = walletBuildGatewayDetail(
                response,
                'La tarjeta fue recibida, pero Nuvei no devolvio token/card_id reutilizable ni se pudo recuperar desde el wallet.'
              );
              responseEl.innerHTML = `<span class="text-warning">${finalDetail.replace(/\n/g, '<br>')}</span>`;
              swal('Error Nuvei', finalDetail, 'error');
              resetButton();
              return;
            }

            const procesoRecuperado = walletState.editingToken
              ? reemplazarTarjetaWallet(walletState.editingToken, gatewayCard.token, cardData)
              : guardarTarjetaWallet(gatewayCard.token, gatewayCard.transaction_reference || transactionReference, cardData).then(function (raw) {
                  const saved = walletParseJson(raw);
                  if (!saved || saved.error !== false) {
                    throw new Error(saved?.msg || 'No se pudo guardar la tarjeta recuperada desde Nuvei.');
                  }
                  return { reusedExistingToken: false };
                });

            return procesoRecuperado.then(function (result) {
              const alertMeta = walletResultAlertMeta(response);
              responseEl.innerHTML = walletState.editingToken
                ? '<span class="text-success">La tarjeta se recupero desde Nuvei y reemplazo a la anterior.</span>'
                : (result?.reusedExistingToken
                    ? '<span class="text-success">La tarjeta ya existia en Nuvei y quedo vinculada a tu wallet.</span>'
                    : `<span class="text-${alertMeta.type === 'success' ? 'success' : alertMeta.type === 'warning' ? 'warning' : 'danger'}">${alertMeta.text}</span>`);
              swal(alertMeta.title, `${alertMeta.text}\n\n${walletBuildGatewayDetail(response)}`, alertMeta.type);
              resetWalletEditMode();
              cargarWalletTarjetas();
              resetButton();
            });
          })
          .catch(function (error) {
            const finalDetail = error.message || walletBuildGatewayDetail(response, 'No se pudo recuperar la tarjeta desde Nuvei.');
            responseEl.innerHTML = `<span class="text-danger">${finalDetail.replace(/\n/g, '<br>')}</span>`;
            swal('Error Nuvei', finalDetail, 'error');
            resetButton();
          });
        return;
      }

      const proceso = walletState.editingToken
        ? (isValid
            ? reemplazarTarjetaWallet(walletState.editingToken, usableToken, cardData)
            : guardarTarjetaWallet(usableToken, transactionReference, cardData).then(function (raw) {
                const saved = walletParseJson(raw);
                if (!saved || saved.error !== false) {
                  throw new Error(saved?.msg || 'No se pudo guardar la tarjeta en revision.');
                }
                return { reusedExistingToken: false, keptOldCard: true };
              }))
        : guardarTarjetaWallet(usableToken, transactionReference, cardData).then(function (raw) {
            const saved = walletParseJson(raw);
            if (!saved || saved.error !== false) {
              throw new Error(saved?.msg || 'No se pudo guardar la tarjeta localmente.');
            }
            return { reusedExistingToken: false };
          });

      proceso
        .then(function (result) {
          const alertMeta = walletResultAlertMeta(response);
          if (walletState.editingToken) {
            responseEl.innerHTML = isValid
              ? (result?.reusedExistingToken
                  ? '<span class="text-success">La tarjeta ya existia y quedo actualizada en tu wallet.</span>'
                  : '<span class="text-success">Tarjeta reemplazada correctamente.</span>')
              : '<span class="text-warning">La nueva tarjeta quedo guardada en revision. La tarjeta anterior se mantuvo activa hasta que confirmes su estado.</span>';
          } else {
            responseEl.innerHTML = isReview
              ? '<span class="text-warning">La tarjeta fue guardada, pero quedo en revision. Puedes verla en la lista y verificar luego su estado.</span>'
              : alertMeta.type === 'error'
                ? '<span class="text-danger">La tarjeta fue rechazada.</span>'
                : '<span class="text-success">Tarjeta guardada correctamente.</span>';
          }
          swal(alertMeta.title, `${alertMeta.text}\n\n${walletBuildGatewayDetail(response)}`, alertMeta.type);
          resetWalletEditMode();
          cargarWalletTarjetas();
          resetButton();
        })
        .catch(function (error) {
          responseEl.innerHTML = `<span class="text-danger">${error.message || 'No se pudo procesar la tarjeta.'}</span>`;
          resetButton();
        });
      return;
    }

    if (response.error) {
      if (String(response.error.type || '').includes('Card already added')) {
        const token = usableToken;
        if (!token) {
          responseEl.innerHTML = '<span class="text-warning">La tarjeta ya estaba registrada.</span>';
          resetButton();
          cargarWalletTarjetas();
          return;
        }

        const procesoTarjetaExistente = walletState.editingToken
          ? reemplazarTarjetaWallet(walletState.editingToken, token, cardData)
          : guardarTarjetaWallet(token, '', cardData);

        procesoTarjetaExistente
          .then(function () {
            responseEl.innerHTML = walletState.editingToken
              ? '<span class="text-success">La tarjeta existente quedo vinculada y reemplazo a la anterior.</span>'
              : '<span class="text-success">La tarjeta ya existia y quedo sincronizada con tu wallet.</span>';
            resetWalletEditMode();
            resetButton();
            cargarWalletTarjetas();
          })
          .catch(function (error) {
            responseEl.innerHTML = `<span class="text-danger">${error.message || 'No se pudo sincronizar la tarjeta existente.'}</span>`;
            resetButton();
          });
        return;
      } 

      responseEl.innerHTML = `<span class="text-danger">${response.error.description || response.error.type || 'No se pudo tokenizar la tarjeta.'}</span>`;
      swal('Error Nuvei', walletBuildGatewayDetail(response), 'error');
      resetButton();
      return;
    }

    responseEl.innerHTML = '<span class="text-danger">No se recibio una respuesta valida al guardar la tarjeta.</span>';
    swal('Error Nuvei', walletBuildGatewayDetail(response), 'error');
    resetButton();
  };

  pg_sdk.generate_tokenize(getTokenizeData(), '#tokenize_example', responseCallback, notCompletedFormCallback);

  submitButton.addEventListener('click', function () { 
    responseEl.innerHTML = ''; 
    submitButton.innerHTML = walletState.editingToken
      ? '<span class="spinner-border spinner-border-sm me-2"></span>Reemplazando...'
      : '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    submitButton.setAttribute('disabled', 'disabled');
    pg_sdk.tokenize();
  });
}

$(document).ready(function () {
  $('#walletCancelEdit').on('click', function () {
    resetWalletEditMode();
  });
  cargarWalletTarjetas();
  inicializarFormularioTarjeta();
});
