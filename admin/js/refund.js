let refundTable = null;

function refundBadgeEstado(estado) {
  const value = String(estado || '').toUpperCase().trim();
  if (value === 'E') {
    return '<span class="badge bg-danger-subtle text-danger">Reembolsado</span>';
  }
  if (value === 'A') {
    return '<span class="badge bg-success-subtle text-success">Activo</span>';
  }
  return `<span class="badge bg-secondary-subtle text-secondary">${value || 'N/D'}</span>`;
}

function refundCurrency(value) {
  const amount = Number(value || 0);
  return `$${amount.toFixed(2)}`;
}

function refundEscape(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function refundInitials(value) {
  const text = String(value || '').trim();
  if (!text) return 'FM';
  return text
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');
}

function renderRefundRows(rows) {
  const $tbody = $('#lista_refunds');
  $tbody.empty();

  if (!rows || rows.length === 0) {
    $tbody.html(`
      <tr>
        <td colspan="9" class="refund-empty-state">No hay pagos disponibles para este contexto.</td>
      </tr>
    `);
    return;
  }

  rows.forEach((item) => {
    const isRefunded = String(item.estado_pago || '').toUpperCase() === 'E';
    const empresa = refundEscape(item.empresa || 'Empresa sin nombre');
    const correo = refundEscape(item.correo || '');
    const membresia = refundEscape(item.membresia || 'Sin membresía');
    const transaccion = refundEscape(item.id_transaccion || '');
    const autorizacion = refundEscape(item.codigo_autorizacion || '');
    const fecha = refundEscape(item.fecha_transaccion || '');

    const empresaHtml = `
      <div class="refund-company">
        <div class="refund-avatar">${refundInitials(item.empresa)}</div>
        <div class="refund-meta">
          <strong>${empresa}</strong>
          <span>${correo}</span>
        </div>
      </div>
    `;

    const membresiaHtml = `<span class="refund-pill">${membresia}</span>`;

    const transaccionHtml = `
      <div class="refund-transaction-id">${transaccion}</div>
      <div class="refund-transaction-meta">Auth: ${autorizacion}</div>
    `;

    const accionesHtml = isRefunded
      ? '<span class="badge bg-danger-subtle text-danger">Ya reembolsado</span>'
      : `
        <div class="refund-actions">
          <button class="btn btn-tertiary border-300 btn-sm text-danger shadow-none refund-action-btn"
            type="button"
            onclick="confirmarRefund(${Number(item.id_pagos_transaccion)})"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Reembolsar">
            <span class="fas fa-undo-alt"></span>
          </button>
        </div>
      `;

    $tbody.append(`
      <tr>
        <td class="align-middle">${empresaHtml}</td>
        <td class="align-middle">${membresiaHtml}</td>
        <td class="align-middle"><span class="refund-amount">${refundCurrency(item.valor_pagado)}</span></td>
        <td class="align-middle"><span class="refund-amount refund-target">${refundCurrency(item.valor_reembolso)}</span></td>
        <td class="align-middle">${transaccionHtml}</td>
        <td class="align-middle">${fecha}</td>
        <td class="align-middle">${refundBadgeEstado(item.estado_pago)}</td>
        <td class="align-middle">${refundBadgeEstado(item.estado_membresia)}</td>
        <td class="align-middle">${accionesHtml}</td>
      </tr>
    `);
  });
}

function initRefundDataTable() {
  if ($.fn.DataTable.isDataTable('#my_table')) {
    $('#my_table').DataTable().clear().destroy();
  }

  refundTable = $('#my_table').DataTable({
    searching: true,
    responsive: false,
    autoWidth: false,
    pageLength: 25,
    lengthChange: false,
    info: true,
    order: [[5, 'desc']],
    columnDefs: [
      { orderable: false, targets: 8 },
      { className: 'text-center', targets: '_all' },
      { width: '22%', targets: 0 },
      { width: '13%', targets: 1 },
      { width: '12%', targets: 8 }
    ],
    language: {
      search: "",
      searchPlaceholder: "Buscar empresa, membresía o transacción",
      info: "Mostrando _START_ a _END_ de _TOTAL_ pagos",
      infoEmpty: "Mostrando 0 a 0 de 0 pagos",
      zeroRecords: "No se encontraron pagos con ese criterio",
      emptyTable: "No hay pagos disponibles",
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

function cargarRefunds() {
  $.get('../api/v1/fulmuv/refund/pagos/', {}, function (returnedData) {
    const returned = JSON.parse(returnedData);
    if (returned.error === false) {
      renderRefundRows(returned.data || []);
      initRefundDataTable();
      return;
    }

    $('#lista_refunds').html(`
      <tr>
        <td colspan="9" class="refund-empty-state">${refundEscape(returned.msg || 'No se pudo cargar la lista de pagos.')}</td>
      </tr>
    `);
  }).fail(function () {
    $('#lista_refunds').html(`
      <tr>
        <td colspan="9" class="refund-empty-state">No se pudo conectar con el servidor.</td>
      </tr>
    `);
  });
}

function confirmarRefund(idPago) {
  swal({
    title: 'Confirmar reembolso',
    text: 'Se ejecutará el reembolso en Nuvei, se inactivará la membresía y se marcará el pago como reembolsado.',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d9534f',
    confirmButtonText: 'Sí, reembolsar',
    cancelButtonText: 'Cancelar',
    closeOnConfirm: false
  }, function () {
    $.post('../api/v1/fulmuv/refund/pago/', {
      id_pagos_transaccion: idPago
    }, function (returnedData) {
      const returned = typeof returnedData === 'string' ? JSON.parse(returnedData) : returnedData;
      if (returned.error === false) {
        let msg = returned.msg || 'Reembolso realizado correctamente.';
        if (returned.data?.valor_reembolso) {
          msg += `\n\nValor reembolsado: ${refundCurrency(returned.data.valor_reembolso)}`;
        }
        if (returned.warnings && returned.warnings.length) {
          msg += `\n\nAdvertencias:\n- ${returned.warnings.join('\n- ')}`;
        }
        swal('Correcto', msg, 'success');
        cargarRefunds();
        return; 
      }

      let errorMsg = returned.msg || 'No se pudo realizar el reembolso.';
      if (returned.nuvei?.detail) {
        errorMsg += `\nNuvei: ${returned.nuvei.detail}`;
      }
      swal('Error', errorMsg, 'error'); 
    }).fail(function () {
      swal('Error', 'No se pudo conectar con el servidor.', 'error');
    });
  });
}

$(document).ready(function () {
  cargarRefunds();
});
