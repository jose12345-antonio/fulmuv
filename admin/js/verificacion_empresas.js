var id_usuario = $("#id_principal").val()

$(document).ready(function () {
  $.get('../api/v1/fulmuv/getVerificaciones/', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {

      $("#tabla_empresas_lista").text("");
      returned.data.forEach(empresa => {


        var estado = "";

        // 🔴 Si fue rechazada
        if (parseInt(empresa.rechazo_verificacion_empresa) === 1) {

          estado = `
            <span class='badge rounded-pill badge-subtle-danger text-capitalize'>
              Se rechazó la verificación
              <span class='ms-1 fas fa-times-circle' data-fa-transform='shrink-2'></span>
            </span>
          `;

          // 🟡 Si no está verificada (pero tampoco rechazada)
        } else if (parseInt(empresa.verificado) === 0) {

          estado = `
            <span class='badge rounded-pill badge-subtle-warning text-capitalize'>
              No verificada
              <span class='ms-1 fas fa-clock' data-fa-transform='shrink-2'></span>
            </span>
          `;

          // 🟢 Si está verificada
        } else if (parseInt(empresa.verificado) === 1) {

          estado = `
            <span class='badge rounded-pill badge-subtle-success text-capitalize'>
              Verificada
              <span class='ms-1 fas fa-check-circle' data-fa-transform='shrink-2'></span>
            </span>
          `;
        }


        // ✅ Menú dinámico según estado
        let opciones = "";

        // Si NO está verificada -> mostrar Verificar
        if (parseInt(empresa.verificado) === 0) {
          opciones += `
            <a class="dropdown-item" onclick="updateEstado(${empresa.verificado}, ${empresa.id_verificacion}, 1)">Verificar</a>
            <div class="dropdown-divider"></div>
          `;
        }

        let imgUrl = empresa.img_path;
        const correo = empresa.correo ? empresa.correo : "";
        const correoLink = correo ? `<a href="mailto:${correo}" class="text-decoration-none">${correo}</a>` : `<span class="text-muted">-</span>`;
        const direccion = empresa.direccion ? empresa.direccion : "<span class='text-muted'>-</span>"; const cedulaRuc = empresa.cedula_ruc ? empresa.cedula_ruc : "<span class='text-muted'>-</span>";

        // Rechazar verificación (siempre disponible)
        opciones += `
        <a class="dropdown-item text-danger" onclick="openRechazoModal(${empresa.verificado}, ${empresa.id_verificacion})">
          Rechazar verificación
        </a>
      `;


        $("#tabla_empresas_lista").append(`
        <tr class="btn-reveal-trigger">
          <td class="py-2 align-middle">
            <div class="d-flex align-items-center">
              <img src="../empresa/${imgUrl}" class="rounded-circle me-2" width="34" height="34" style="object-fit:cover;" />
              <div class="fw-semi-bold">${empresa.nombre}</div>
            </div>
          </td>

          <td class="py-2 align-middle">${correoLink}</td>
          <td class="py-2 align-middle">${direccion}</td>
          <td class="py-2 align-middle">${cedulaRuc}</td>
          <td class="py-2 align-middle">${estado}</td>

          <td class="align-middle white-space-nowrap text-end">
            <!-- ✅ Ver documentos fijo en la fila -->
            <button class="btn btn-falcon-default btn-sm me-2" type="button"
              onclick="verDocuemntos(${empresa.id_verificacion})" title="Ver documentos">
              <span class="fas fa-file-alt"></span>
            </button>

            <!-- ✅ Menú (Verificar solo si está pendiente / Rechazar siempre) -->
            <div class="dropstart font-sans-serif position-static d-inline-block">
              <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button"
                data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <span class="fas fa-ellipsis-h fs-10"></span>
              </button>
              <div class="dropdown-menu dropdown-menu-end border py-2">
                ${opciones}
              </div>
            </div>
          </td>
        </tr>
      `);


      });

      options = {
        'responsive': false,
        'lengthChange': false,
        'searching': true,
        'pageLength': 100, 'info': true,
        // 'language': {
        //   'url': 'http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
        //   'paginate': {
        //     'next': '<span class=\'fas fa-chevron-right\'></span>',
        //     'previous': '<span class=\'fas fa-chevron-left\'></span>'
        //   }
        // }
      }

      $("#my_table").attr("data-datatables", JSON.stringify(options));
      $("#checkbox-bulk-table-item-select").attr("data-bulk-select", '{"body":"lista_ordenes","actions":"table-number-pagination-actions","replacedElement":"table-number-pagination-replace-element"}');
      dataTablesInit()
      bulkSelectInit()
    }

  });
});

function openRechazoModal(estado_actual, id_verificacion) {
  // si ya está en 0, evita hacer nada
  if (estado_actual == 1) {
    SweetAlert("success", "La empresa ya está verificada.");
    return;
  }

  $("#rechazo_estado_actual").val(estado_actual);
  $("#rechazo_id_verificacion").val(id_verificacion);
  $("#motivo_rechazo").val("");

  const modal = new bootstrap.Modal(document.getElementById("modalRechazoVerificacion"));
  modal.show();
}

$("#btnConfirmarRechazo").on("click", function () {
  const motivo = ($("#motivo_rechazo").val() || "").trim();
  const id_verificacion = $("#rechazo_id_verificacion").val();
  const estado_actual = $("#rechazo_estado_actual").val();

  if (!motivo) {
    SweetAlert("error", "Debes ingresar una observación para rechazar la verificación.");
    return;
  }

  updateEstado(estado_actual, id_verificacion, 0, motivo);

  const modalEl = document.getElementById("modalRechazoVerificacion");
  bootstrap.Modal.getInstance(modalEl)?.hide();
});




function updateEstado(estado_actual, id_verificacion, estado, motivo = "") {
  // if (estado_actual != estado) {
  $.post('../api/v1/fulmuv/verificaciones/updateEstado', {
    id_verificacion,
    estado,
    motivo
  }, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned["error"] == false) {
      SweetAlert("url_success", returned.msg, "verificacion_empresas.php")
    } else {
      SweetAlert("error", returned.msg);
    }
  });
  // } else {
  //   SweetAlert("warning", "La empresa ya tiene ese estado.");
  // }
}


// Si tu API entrega rutas relativas, pon aquí la base (ajústalo a tu servidor/ruta real)
const BASE_FILES_URL = ''; // p.ej. '/files/verificaciones/'

function verDocuemntos(id_verificacion) {
  $.get('../api/v1/fulmuv/getVerificacionById/' + id_verificacion, {}, function (returnedData) {
    let res;
    try { res = typeof returnedData === 'string' ? JSON.parse(returnedData) : returnedData; }
    catch (e) { console.error('Respuesta no es JSON', e, returnedData); return; }

    if (!res || res.error !== false) return;

    const v = res.data || {};

    // helpers
    const isAbs = (u) => /^https?:\/\//i.test(u || '');
    const norm = (u) => (u || '').toString().trim();
    const fullURL = (u) => {
      const s = norm(u);
      if (!s) return '';
      return isAbs(s) ? s : (BASE_FILES_URL + s).replace(/([^:]\/)\/+/g, '$1');
    };
    const getExt = (u) => (u.split('?')[0].split('#')[0].match(/\.([a-z0-9]+)$/i) || [])[1]?.toLowerCase() || '';
    const isImg = (e) => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(e);
    const isPDF = (e) => e === 'pdf';

    // limpiar modal previo
    $('#staticBackdrop').remove();
    $('#btnModal').remove();

    // header: empresa/verificado
    const tituloEmpresa = norm(v.nombre_comercial) || `Empresa #${norm(v.id_empresa) || '-'}`;
    const badgeEstado = (v.verificado == 1)
      ? '<span class="badge text-bg-success">Verificado</span>'
      : '<span class="badge text-bg-warning">Pendiente</span>';

    // crear modal
    $("#alert").html(`
      <button id="btnModal" class="btn btn-primary d-none" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Open</button>
      <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl mt-6" role="document">
          <div class="modal-content border-0">
            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
              <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                <h4 class="mb-1" id="staticBackdropLabel">Documentos cargados</h4>
                <div class="d-flex flex-wrap gap-2 small text-muted">
                  <span>${tituloEmpresa}</span>
                  ${badgeEstado}
                  <span>Creado: ${norm(v.created_at) || '-'}</span>
                  <span>Actualizado: ${norm(v.updated_at) || '-'}</span>
                </div>
              </div>
              <div class="p-4">
                <div id="docuemntos" class="row g-4"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    `);

    const $wrap = $('#docuemntos');

    // Campos que trae tu API (ajusta etiquetas si quieres)
    const fields = [
      { key: 'ruc', label: 'RUC' },
      { key: 'cedula', label: 'Cédula representante' },
      { key: 'nombramiento', label: 'Nombramiento' },
      { key: 'patente', label: 'Patente municipal' },
      { key: 'planilla', label: 'Planilla de servicio' },
    ];

    let count = 0;

    fields.forEach(({ key, label }) => {
      const raw = norm(v[key]);
      if (!raw) return;

      const url = fullURL(raw);
      if (!url) return;

      const ext = getExt(url);
      let previewHTML = '';

      if (isImg(ext)) {
        previewHTML = `
          <img src="${encodeURI(url)}" alt="${label}" class="img-fluid rounded border" loading="lazy" style="max-height:420px;object-fit:contain;width:100%;">
        `;
      } else if (isPDF(ext)) {
        previewHTML = `
          <div class="ratio ratio-16x9">
            <iframe src="${encodeURI(url)}" title="${label}" loading="lazy" style="border:1px solid #e5e5e5;border-radius:.5rem;"></iframe>
          </div>
        `;
      } else {
        previewHTML = `
          <div class="alert alert-info" role="alert">
            Vista previa no disponible para .${ext || 'archivo'}. Usa “Abrir” o “Descargar”.
          </div>
        `;
      }

      $wrap.append(`
        <div class="col-12 ${isImg(ext) || isPDF(ext) ? 'col-md-6' : 'col-12'}">
          <div class="card h-100 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
              <strong>${label}</strong>
              <span class="text-muted small">${ext ? '.' + ext : ''}</span>
            </div>
            <div class="card-body">
              ${previewHTML}
              <div class="d-flex gap-2 mt-3">
                <a class="btn btn-outline-primary btn-sm" href="${encodeURI(url)}" target="_blank" rel="noopener">Abrir</a>
                <a class="btn btn-primary btn-sm" href="${encodeURI(url)}" download>Descargar</a>
              </div>
            </div>
          </div>
        </div>
      `);

      count++;
    });

    if (count === 0) {
      $wrap.html(`
        <div class="col-12">
          <div class="text-center text-muted py-5">
            <div style="font-size:2rem;line-height:1;">📄</div>
            <p class="mt-2 mb-0">No hay documentos cargados para esta verificación.</p>
          </div>
        </div>
      `);
    }

    // abrir modal
    $("#btnModal").trigger('click');
  })
    .fail(function (err) {
      console.error('Error al consultar verificación', err);
    });
}
