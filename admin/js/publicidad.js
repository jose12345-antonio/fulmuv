const PUBLICIDAD_MAX_FILE_SIZE = 350 * 1024 * 1024;
const DATA_TABLE_SPANISH_LANGUAGE = {
  decimal: "",
  emptyTable: "No hay datos disponibles en la tabla",
  info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
  infoEmpty: "Mostrando 0 a 0 de 0 registros",
  infoFiltered: "(filtrado de _MAX_ registros totales)",
  infoPostFix: "",
  thousands: ",",
  lengthMenu: "Mostrar _MENU_ registros",
  loadingRecords: "Cargando...",
  processing: "Procesando...",
  search: "Buscar:",
  zeroRecords: "No se encontraron resultados",
  paginate: {
    first: "Primero",
    last: "Último",
    next: '<span class="fas fa-chevron-right"></span>',
    previous: '<span class="fas fa-chevron-left"></span>',
  },
  aria: {
    sortAscending: ": activar para ordenar la columna de manera ascendente",
    sortDescending: ": activar para ordenar la columna de manera descendente",
  },
};

$(document).ready(function () {
  cargarTablaPublicidad();
});

function cargarTablaPublicidad() {
  $.get("../api/v1/fulmuv/publicidadAdmin/all", {}, function (returnedData) {
    const returned = JSON.parse(returnedData);

    if (returned.error == false) {
      if ($.fn.DataTable.isDataTable("#my_table")) {
        $("#my_table").DataTable().destroy();
      }

      $("#lista_publicidad").html("");

      returned.data.forEach((publicidad) => {
        $("#lista_publicidad").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium">${renderPublicidadImageCell(
              publicidad.imagen
            )}</td>
            <td class="py-2 align-middle fs-9 fw-medium">${renderPublicidadImageCell(
              publicidad.imagen_tablet
            )}</td>
            <td class="py-2 align-middle fs-9 fw-medium">${renderPublicidadImageCell(
              publicidad.imagen_movil
            )}</td>
            <td class="py-2 align-middle fs-9 fw-medium">
              ${
                publicidad.url
                  ? `<a href="${escapeHtml(
                      publicidad.url
                    )}" target="_blank" rel="noopener noreferrer">${escapeHtml(
                      publicidad.url
                    )}</a>`
                  : '<span class="text-600">Sin URL</span>'
              }
            </td>
            <td class="py-2 align-middle fs-9 fw-medium">
              ${publicidad.posicion ? `Posición ${publicidad.posicion}` : '<span class="text-600">Sin posición</span>'}
            </td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="fas fa-ellipsis-h fs-10"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border py-0">
                  <div class="py-2">
                    <a class="dropdown-item text-info" onclick="PublicidadById(${publicidad.id_publicidad})">Actualizar</a>
                    <a class="dropdown-item text-danger" onclick="remove(${publicidad.id_publicidad}, 'publicidad')">Eliminar</a>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        `);
      });

      $("#my_table").DataTable({
        searching: true,
        responsive: false,
        pageLength: 8,
        info: true,
        lengthChange: false,
        language: DATA_TABLE_SPANISH_LANGUAGE,
        dom:
          "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" +
          "<'table-responsive scrollbar'tr>" +
          "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>",
      });
    }
  });
}

function renderPublicidadImageCell(path) {
  if (!path) {
    return '<span class="text-600">Sin imagen</span>';
  }

  return `<img src="${path}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" style="width: 80px; height: 80px; object-fit: scale-down">`;
}

function escapeHtml(text) {
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatFileSize(bytes) {
  if (bytes >= 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)} GB`;
  if (bytes >= 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
  if (bytes >= 1024) return `${(bytes / 1024).toFixed(2)} KB`;
  return `${bytes} B`;
}

function remove(id, tabla) {
  swal(
    {
      title: "Alerta",
      text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#27b394",
      confirmButtonText: "Sí",
      cancelButtonText: "No",
      closeOnConfirm: false,
    },
    function () {
      $.post(
        "../api/v1/fulmuv/" + tabla + "/delete",
        { id: id },
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (returned.error == false) {
            SweetAlert("url_success", returned.msg, "publicidad.php");
          } else {
            SweetAlert("error", returned.msg);
          }
        }
      );
    }
  );
}

function addPublicidad() {
  abrirModalPublicidad("crear");
}

function PublicidadById(idPublicidad) {
  $.get(
    "../api/v1/fulmuv/publicidad/" + idPublicidad,
    function (returnedData) {
      if (!returnedData.error) {
        const publicidad = Array.isArray(returnedData.data)
          ? returnedData.data[0] || {}
          : returnedData.data || {};
        abrirModalPublicidad("editar", publicidad);
      } else {
        SweetAlert("error", returnedData.msg || "No se pudo obtener la publicidad.");
      }
    },
    "json"
  );
}

function abrirModalPublicidad(modo, publicidad = {}) {
  $("#staticBackdrop").remove();

  const esEdicion = modo === "editar";
  const titulo = esEdicion ? "Actualizar Publicidad" : "Agregar Publicidad";
  const textoBotonGuardar = esEdicion ? "Actualizar" : "Guardar";

  $("#alert").html(`
    <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Abrir</button>
    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0">
          <div class="modal-header bg-light">
            <h5 class="modal-title w-100 text-center" id="staticBackdropLabel">${titulo}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-lg-4 col-md-6">
                <label for="imagen_desktop" class="form-label">Imagen pantalla completa</label>
                <input class="form-control js-publicidad-image-input" type="file" id="imagen_desktop" accept="image/*" data-preview-target="#preview_imagen_desktop">
                <div id="preview_imagen_desktop">${renderPreview("Actual", publicidad.imagen)}</div>
              </div>
              <div class="col-lg-4 col-md-6">
                <label for="imagen_tablet" class="form-label">Imagen tablet</label>
                <input class="form-control js-publicidad-image-input" type="file" id="imagen_tablet" accept="image/*" data-preview-target="#preview_imagen_tablet">
                <div id="preview_imagen_tablet">${renderPreview("Actual", publicidad.imagen_tablet)}</div>
              </div>
              <div class="col-lg-4 col-md-6">
                <label for="imagen_movil" class="form-label">Imagen móvil</label>
                <input class="form-control js-publicidad-image-input" type="file" id="imagen_movil" accept="image/*" data-preview-target="#preview_imagen_movil">
                <div id="preview_imagen_movil">${renderPreview("Actual", publicidad.imagen_movil)}</div>
              </div>
              <div class="col-lg-12">
                <label for="publicidad_url" class="form-label">URL</label>
                <input class="form-control" type="url" id="publicidad_url" placeholder="https://ejemplo.com" value="${escapeHtml(
                  publicidad.url || ""
                )}">
              </div>
              <div class="col-lg-12">
                <label for="publicidad_posicion" class="form-label">Posición</label>
                <select class="form-select" id="publicidad_posicion">
                  <option value="">Seleccione una posición</option>
                  <option value="1" ${String(publicidad.posicion || "") === "1" ? "selected" : ""}>Posición 1</option>
                  <option value="2" ${String(publicidad.posicion || "") === "2" ? "selected" : ""}>Posición 2</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarPublicidad" data-default-text="${textoBotonGuardar}" onclick="${
              esEdicion ? `actualizarPublicidad(${publicidad.id_publicidad})` : "guardarPublicidad()"
            }">${textoBotonGuardar}</button>
          </div>
        </div>
      </div>
    </div>
  `);

  bindPublicidadPreviewEvents();
  $("#btnModal").click();
}

function setPublicidadSubmitLoading(isLoading) {
  const $button = $("#btnGuardarPublicidad");

  if (!$button.length) {
    return;
  }

  const defaultText = $button.attr("data-default-text") || "Guardar";

  if (isLoading) {
    $button.prop("disabled", true).html(`
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      Guardando...
    `);
    return;
  }

  $button.prop("disabled", false).text(defaultText);
}

function renderPreview(label, path) {
  if (!path) {
    return "";
  }

  return `
    <div class="mt-2">
      <small class="text-600 d-block mb-1">${label}</small>
      <img src="${path}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" style="width: 100%; max-height: 140px; object-fit: contain; border: 1px solid #d8e2ef; border-radius: 0.375rem; padding: 0.5rem; background: #fff;">
    </div>
  `;
}

function bindPublicidadPreviewEvents() {
  $(".js-publicidad-image-input").off("change").on("change", function () {
    const target = $(this).data("preview-target");
    const previewContainer = $(target);
    const file = this.files && this.files[0] ? this.files[0] : null;

    if (!previewContainer.length || !file) {
      return;
    }

    if (file.size > PUBLICIDAD_MAX_FILE_SIZE) {
      this.value = "";
      SweetAlert(
        "error",
        `El archivo ${file.name} supera el limite permitido de ${formatFileSize(
          PUBLICIDAD_MAX_FILE_SIZE
        )}.`
      );
      return;
    }

    const reader = new FileReader();
    reader.onload = function (event) {
      previewContainer.html(`
        <div class="mt-2">
          <small class="text-600 d-block mb-1">Nueva imagen</small>
          <img src="${event.target.result}" style="width: 100%; max-height: 220px; object-fit: contain; border: 1px solid #d8e2ef; border-radius: 0.375rem; padding: 0.5rem; background: #fff;">
        </div>
      `);
    };
    reader.readAsDataURL(file);
  });
}

function guardarPublicidad() {
  const url = $("#publicidad_url").val().trim();
  const posicion = $("#publicidad_posicion").val();
  const imagenDesktop = $("#imagen_desktop")[0].files[0];
  const imagenTablet = $("#imagen_tablet")[0].files[0];
  const imagenMovil = $("#imagen_movil")[0].files[0];

  if (!imagenDesktop || !imagenTablet || !imagenMovil || !posicion) {
    SweetAlert(
      "error",
      "Las imágenes de pantalla completa, tablet, móvil y la posición son obligatorias."
    );
    return;
  }

  setPublicidadSubmitLoading(true);

  Promise.all([
    saveFiles(imagenDesktop),
    saveFiles(imagenTablet),
    saveFiles(imagenMovil),
  ])
    .then(([desktop, tablet, movil]) => {
      $.post(
        "../api/v1/fulmuv/publicidad/create",
        {
          imagen: desktop.img,
          imagen_tablet: tablet.img,
          imagen_movil: movil.img,
          url: url,
          posicion: posicion,
        },
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (returned.error == false) {
            SweetAlert("url_success", returned.msg, "publicidad.php");
          } else {
            SweetAlert("error", returned.msg);
            setPublicidadSubmitLoading(false);
          }
        }
      ).fail(function () {
        setPublicidadSubmitLoading(false);
        SweetAlert("error", "Ocurrió un error al guardar la publicidad.");
      });
    })
    .catch(() => {
      setPublicidadSubmitLoading(false);
      SweetAlert("error", "Ocurrió un error al subir las imágenes.");
    });
}

function actualizarPublicidad(idPublicidad) {
  const url = $("#publicidad_url").val().trim();
  const posicion = $("#publicidad_posicion").val();
  const imagenDesktop = $("#imagen_desktop")[0].files[0];
  const imagenTablet = $("#imagen_tablet")[0].files[0];
  const imagenMovil = $("#imagen_movil")[0].files[0];

  if (!posicion) {
    SweetAlert("error", "La posición es obligatoria.");
    return;
  }

  setPublicidadSubmitLoading(true);

  Promise.all([
    imagenDesktop ? saveFiles(imagenDesktop) : Promise.resolve(null),
    imagenTablet ? saveFiles(imagenTablet) : Promise.resolve(null),
    imagenMovil ? saveFiles(imagenMovil) : Promise.resolve(null),
  ])
    .then(([desktop, tablet, movil]) => {
      const datos = {
        id_publicidad: idPublicidad,
        url: url,
        posicion: posicion,
      };

      if (desktop && desktop.img) datos.imagen = desktop.img;
      if (tablet && tablet.img) datos.imagen_tablet = tablet.img;
      if (movil && movil.img) datos.imagen_movil = movil.img;

      $.post(
        "../api/v1/fulmuv/publicidad/update",
        datos,
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (returned.error === false) {
            SweetAlert("url_success", returned.msg, "publicidad.php");
          } else {
            SweetAlert("error", returned.msg);
            setPublicidadSubmitLoading(false);
          }
        }
      ).fail(function () {
        setPublicidadSubmitLoading(false);
        SweetAlert("error", "Ocurrió un error al actualizar la publicidad.");
      });
    })
    .catch(() => {
      setPublicidadSubmitLoading(false);
      SweetAlert("error", "Ocurrió un error al subir una de las imágenes.");
    });
}

function saveFiles(file) {
  return new Promise(function (resolve, reject) {
    if (!file) {
      resolve(null);
      return;
    }

    if (file.size > PUBLICIDAD_MAX_FILE_SIZE) {
      SweetAlert(
        "error",
        `El archivo ${file.name} supera el limite permitido de ${formatFileSize(
          PUBLICIDAD_MAX_FILE_SIZE
        )}.`
      );
      reject(new Error("Archivo demasiado grande"));
      return;
    }

    const formData = new FormData();
    formData.append("archivos[]", file);

    $.ajax({
      type: "POST",
      data: formData,
      url: "cargar_imagen.php",
      cache: false,
      contentType: false,
      processData: false,
      success: function (returnedImagen) {
        if (returnedImagen["response"] == "success") {
          resolve(returnedImagen["data"]);
        } else {
          SweetAlert(
            "error",
            "Ocurrió un error al guardar los archivos." + returnedImagen["error"]
          );
          reject(returnedImagen);
        }
      },
      error: function (error) {
        reject(error);
      },
    });
  });
}
