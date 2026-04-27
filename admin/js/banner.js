let atributos = [];
const BANNER_MAX_FILE_SIZE = 350 * 1024 * 1024;
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
  cargarTablaBanners();

  $.get("../api/v1/fulmuv/atributos/", {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      atributos = returned.data;
    }
  });
});

function cargarTablaBanners() {
  $.get("../api/v1/fulmuv/banner/all", {}, function (returnedData) {
    var returned = JSON.parse(returnedData);
    if (returned.error == false) {
      if ($.fn.DataTable.isDataTable("#my_table")) {
        $("#my_table").DataTable().destroy();
      }

      $("#lista_banner").html("");

      returned.data.forEach((banner) => {
        $("#lista_banner").append(`
          <tr class="btn-reveal-trigger">
            <td class="py-2 align-middle fs-9 fw-medium">
              ${renderBannerImageCell(banner.imagen)}
            </td>
            <td class="py-2 align-middle fs-9 fw-medium">
              ${renderBannerImageCell(banner.imagen_tablet)}
            </td>
            <td class="py-2 align-middle fs-9 fw-medium">
              ${renderBannerImageCell(banner.imagen_movil)}
            </td>
            <td class="py-2 align-middle fs-9 fw-medium">
              ${banner.url ? `<a href="${escapeHtml(
                banner.url
              )}" target="_blank" rel="noopener noreferrer">${escapeHtml(
          banner.url
        )}</a>` : '<span class="text-600">Sin URL</span>'}
            </td>
            <td class="align-middle white-space-nowrap py-2 text-end">
              <div class="dropdown font-sans-serif position-static">
                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="fas fa-ellipsis-h fs-10"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border py-0">
                  <div class="py-2">
                    <a class="dropdown-item text-info" onclick="BannerById(${banner.id_banner})">Actualizar</a>
                    <a class="dropdown-item text-danger" onclick="remove(${banner.id_banner}, 'banner')">Eliminar</a>
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

function renderBannerImageCell(path) {
  if (!path) {
    return '<span class="text-600">Sin imagen</span>';
  }

  return `<img src="${path}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" style="width: 80px; height: 80px; object-fit: scale-down">`;
}

function formatFileSize(bytes) {
  if (bytes >= 1024 * 1024 * 1024) {
    return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)} GB`;
  }

  if (bytes >= 1024 * 1024) {
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
  }

  if (bytes >= 1024) {
    return `${(bytes / 1024).toFixed(2)} KB`;
  }

  return `${bytes} B`;
}

function escapeHtml(text) {
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
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
        {
          id: id,
        },
        function (returnedData) {
          var returned = JSON.parse(returnedData);
          if (returned.error == false) {
            SweetAlert("url_success", returned.msg, "banner.php");
          } else {
            SweetAlert("error", returned.msg);
          }
        }
      );
    }
  );
}

function addBanner() {
  abrirModalBanner("crear");
}

function BannerById(idBanner) {
  $.get(
    "../api/v1/fulmuv/banner/" + idBanner,
    function (returnedData) {
      if (!returnedData.error) {
        abrirModalBanner("editar", returnedData.data);
      } else {
        SweetAlert("error", returnedData.msg || "No se pudo obtener el banner.");
      }
    },
    "json"
  );
}

function abrirModalBanner(modo, banner = {}) {
  $("#staticBackdrop").remove();

  const esEdicion = modo === "editar";
  const titulo = esEdicion ? "Actualizar Banner" : "Agregar Banner";
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
                <input class="form-control js-banner-image-input" type="file" id="imagen_desktop" accept="image/*" data-preview-target="#preview_imagen_desktop">
                <div id="preview_imagen_desktop">
                  ${renderPreview("Actual", banner.imagen)}
                </div>
              </div>
              <div class="col-lg-4 col-md-6">
                <label for="imagen_tablet" class="form-label">Imagen tablet</label>
                <input class="form-control js-banner-image-input" type="file" id="imagen_tablet" accept="image/*" data-preview-target="#preview_imagen_tablet">
                <div id="preview_imagen_tablet">
                  ${renderPreview("Actual", banner.imagen_tablet)}
                </div>
              </div>
              <div class="col-lg-4 col-md-6">
                <label for="imagen_movil" class="form-label">Imagen móvil</label>
                <input class="form-control js-banner-image-input" type="file" id="imagen_movil" accept="image/*" data-preview-target="#preview_imagen_movil">
                <div id="preview_imagen_movil">
                  ${renderPreview("Actual", banner.imagen_movil)}
                </div>
              </div>
              <div class="col-lg-12">
                <label for="banner_url" class="form-label">URL del banner</label>
                <input class="form-control" type="url" id="banner_url" placeholder="https://ejemplo.com" value="${escapeHtml(
                  banner.url || ""
                )}">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarBanner" data-default-text="${textoBotonGuardar}" onclick="${
              esEdicion
                ? `editBanner(${banner.id_banner})`
                : "guardarBanner()"
            }">${textoBotonGuardar}</button>
          </div>
        </div>
      </div>
    </div>
  `);

  bindBannerPreviewEvents();
  $("#btnModal").click();
}

function setBannerSubmitLoading(isLoading) {
  const $button = $("#btnGuardarBanner");

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

function bindBannerPreviewEvents() {
  $(".js-banner-image-input").off("change").on("change", function () {
    const target = $(this).data("preview-target");
    const previewContainer = $(target);
    const file = this.files && this.files[0] ? this.files[0] : null;

    if (!previewContainer.length) {
      return;
    }

    if (!file) {
      return;
    }

    if (file.size > BANNER_MAX_FILE_SIZE) {
      this.value = "";
      SweetAlert(
        "error",
        `El archivo ${file.name} supera el limite permitido de ${formatFileSize(
          BANNER_MAX_FILE_SIZE
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

function guardarBanner() {
  const url = $("#banner_url").val().trim();

  const imagenDesktop = $("#imagen_desktop")[0].files[0];
  const imagenTablet = $("#imagen_tablet")[0].files[0];
  const imagenMovil = $("#imagen_movil")[0].files[0];

  if (!imagenDesktop || !imagenTablet || !imagenMovil) {
    SweetAlert(
      "error",
      "Las imágenes de pantalla completa, tablet y móvil son obligatorias."
    );
    return;
  }

  setBannerSubmitLoading(true);

  Promise.all([
    saveFiles(imagenDesktop),
    saveFiles(imagenTablet),
    saveFiles(imagenMovil),
  ])
    .then(([desktop, tablet, movil]) => {
      $.post(
        "../api/v1/fulmuv/banner/create",
        {
          imagen: desktop.img,
          imagen_tablet: tablet.img,
          imagen_movil: movil.img,
          url: url,
        },
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (returned.error == false) {
            SweetAlert("url_success", returned.msg, "banner.php");
          } else {
            SweetAlert("error", returned.msg);
            setBannerSubmitLoading(false);
          }
        }
      ).fail(function () {
        setBannerSubmitLoading(false);
        SweetAlert("error", "Ocurrió un error al guardar el banner.");
      });
    })
    .catch(() => {
      setBannerSubmitLoading(false);
      SweetAlert("error", "Ocurrió un error al subir las imágenes.");
    });
}

function editBanner(idBanner) {
  const url = $("#banner_url").val().trim();

  const imagenDesktop = $("#imagen_desktop")[0].files[0];
  const imagenTablet = $("#imagen_tablet")[0].files[0];
  const imagenMovil = $("#imagen_movil")[0].files[0];

  setBannerSubmitLoading(true);

  Promise.all([
    imagenDesktop ? saveFiles(imagenDesktop) : Promise.resolve(null),
    imagenTablet ? saveFiles(imagenTablet) : Promise.resolve(null),
    imagenMovil ? saveFiles(imagenMovil) : Promise.resolve(null),
  ])
    .then(([desktop, tablet, movil]) => {
      const datos = {
        id_banner: idBanner,
        url: url,
      };

      if (desktop && desktop.img) {
        datos.imagen = desktop.img;
      }

      if (tablet && tablet.img) {
        datos.imagen_tablet = tablet.img;
      }

      if (movil && movil.img) {
        datos.imagen_movil = movil.img;
      }

      $.post(
        "../api/v1/fulmuv/banner/update",
        datos,
        function (returnedData) {
          const returned = JSON.parse(returnedData);
          if (returned.error === false) {
            SweetAlert("url_success", returned.msg, "banner.php");
          } else {
            SweetAlert("error", returned.msg);
            setBannerSubmitLoading(false);
          }
        }
      ).fail(function () {
        setBannerSubmitLoading(false);
        SweetAlert("error", "Ocurrió un error al actualizar el banner.");
      });
    })
    .catch(() => {
      setBannerSubmitLoading(false);
      SweetAlert("error", "Ocurrió un error al subir una de las imágenes.");
    });
}

function saveFiles(file) {
  return new Promise(function (resolve, reject) {
    if (!file) {
      resolve(null);
      return;
    }

    if (file.size > BANNER_MAX_FILE_SIZE) {
      SweetAlert(
        "error",
        `El archivo ${file.name} supera el limite permitido de ${formatFileSize(
          BANNER_MAX_FILE_SIZE
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
            "Ocurrió un error al guardar los archivos." +
              returnedImagen["error"]
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
