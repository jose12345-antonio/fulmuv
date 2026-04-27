let id_empresa = $("#id_empresa").val();

$(document).ready(function () {

  // Estado inicial
  $("#verificado").hide();
  $("#contenido").hide();          // card principal
  $("#formVerificacion").show();
  $("#boxEnProceso").addClass("d-none");
  $("#boxRechazado").addClass("d-none");

  $.get('../api/v1/fulmuv/empresas/validaVerificacion/' + id_empresa, {}, function (returnedData) {
    const returned = (typeof returnedData === "string") ? JSON.parse(returnedData) : returnedData;
    console.log(returned);

    // Siempre mostramos el card base si NO está verificado
    $("#contenido").show();

    // Si no hay registro de verificación, deja formulario normal
    if (returned.error !== false || !returned.data) {
      $("#formVerificacion").show();
      $("#boxEnProceso").addClass("d-none");
      $("#boxRechazado").addClass("d-none");
      return;
    }

    const v = returned.data;
    const verificado = parseInt(v.verificado || 0);
    const rechazado = parseInt(v.rechazo_verificacion_empresa || 0);
    const obs = (v.observacion_verificacion || "").trim();

    // Caso: ya verificado
    if (verificado === 1) {
      $("#verificado").show();
      $("#contenido").hide();
      return;
    }

    // Caso: rechazado -> mostrar formulario + banner rojo
    if (rechazado === 1) {
      $("#formVerificacion").show();
      $("#boxRechazado").removeClass("d-none");
      $("#boxEnProceso").addClass("d-none");
      $("#txtObservacion").text(obs || "Sin observación registrada.");
      return;
    }

    // Caso: en proceso -> ocultar formulario + mostrar cuadro de proceso
    $("#formVerificacion").hide();
    $("#boxEnProceso").removeClass("d-none");
    $("#boxRechazado").addClass("d-none");

  }).fail(function (err) {
    console.error("Error validaVerificacion", err);
    // Si falla, deja formulario normal
    $("#contenido").show();
    $("#formVerificacion").show();
  });
});


const sleep = (ms) => new Promise(r => setTimeout(r, ms));
const pickPath = o => o?.pdf ?? o?.img ?? o?.url ?? o?.file ?? '';

async function saveEmpresaEditar() {
  setBtnLoading("btnGuardarEmpresa", true); // ✅ ON

  try {
    const nombre_comercial = $("#nombre_comercial").val();
    if (!nombre_comercial) {
      setBtnLoading("btnGuardarEmpresa", false); // ✅ OFF
      SweetAlert("error", "El campo 'Nombre comercial' es obligatorio."); return;
    }

    const inputs = [
      { el: $("#ruc")[0], label: "ruc" },
      { el: $("#cedula")[0], label: "cedula" },
      { el: $("#nombramiento")[0], label: "nombramiento" },
      { el: $("#patente")[0], label: "patente" },
      { el: $("#planilla")[0], label: "planilla" },
    ];

    // Validaciones
    for (const { el, label } of inputs) {
      if (!el?.files?.length) {
        setBtnLoading("btnGuardarEmpresa", false); // ✅ OFF
        SweetAlert("error", `Debe subir ${label}.`); return;
      }
    }

    // SUBIDA SECUENCIAL con espera > 1 segundo entre cada una
    const uploaded = {};
    for (const { el, label } of inputs) {
      const data = await uploadOne(el.files[0]);   // sube 1 archivo
      uploaded[label] = pickPath(data);

      // Espera 1.3–1.7 s para cambiar de segundo en el timestamp del servidor
      await sleep(1300 + Math.floor(Math.random() * 400));
    }

    // Enviar verificación
    const payload = {
      id_empresa,
      nombre_comercial,
      ...uploaded
    };

    $.ajax({
      type: 'POST',
      url: '../api/v1/fulmuv/empresas/verificar',
      data: payload,
      dataType: 'json',
      success: (returned) => {
        setBtnLoading("btnGuardarEmpresa", false); // ✅ OFF
        if (returned.error == false) {
          SweetAlert("url_success", "El equipo de FULMUV revisará tu información y serás notificado. !Gracias por iniciar tu proceso de verificación!", "verificar.php?id_empresa=" + id_empresa);
        } else {
          SweetAlert("error", returned.msg || "No se pudo verificar la empresa.");
        }
      },
      error: (xhr) => {
        setBtnLoading("btnGuardarEmpresa", false); // ✅ OFF
        SweetAlert("error", "Fallo de red al verificar (HTTP " + xhr.status + ").");
      }
    });

  } catch (e) {
    setBtnLoading("btnGuardarEmpresa", false); // ✅ OFF
    console.error(e);
    SweetAlert("error", "Ocurrió un error al verificar la empresa.");
  }
}

// Sube 1 archivo y devuelve res.data (que contiene {pdf: "..."} ó {img: "..."})
function uploadOne(file) {
  return new Promise((resolve, reject) => {
    const fd = new FormData();
    // Mantén el nombre de campo que tu PHP espera:
    fd.append('archivos[]', file);

    $.ajax({
      type: 'POST',
      url: '../admin/cargar_imagen.php',
      data: fd,
      contentType: false,
      processData: false,
      cache: false,
      dataType: 'json',
      success: (res) => {
        if (res?.response === 'success') {
          resolve(res.data); // ejemplo: { pdf: "files/Registro ..._20250926_175135.pdf" }
        } else {
          console.error('Upload error payload:', res);
          reject(new Error('upload-failed'));
        }
      },
      error: (xhr) => {
        console.error('Upload xhr error:', xhr);
        reject(new Error('network-failed'));
      }
    });
  });
}

function setBtnLoading(btnId, loading, text = "Guardando...") {
  const btn = document.getElementById(btnId);
  if (!btn) return;

  if (loading) {
    btn.dataset.oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      ${text}
    `;
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.oldHtml || "Guardar";
  }
}

