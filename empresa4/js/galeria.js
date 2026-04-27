let GALLERY_FILES = []; // para navegar en lightbox
let currentIndex = 0;
let lightboxModalInstance = null;

function getGaleriaRedirectUrl() {
  return "galeria.php?id_empresa=" + $("#id_empresa").val();
}

function setButtonLoading(buttonSelector, isLoading, loadingText = "Guardando...") {
  const $btn = $(buttonSelector);
  if (!$btn.length) return;

  if (isLoading) {
    $btn.data('original-html', $btn.html());
    $btn.prop('disabled', true).addClass('gallery-loading-btn');
    $btn.html(`
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      ${loadingText}
    `);
  } else {
    const originalHtml = $btn.data('original-html');
    if (originalHtml) {
      $btn.html(originalHtml);
    }
    $btn.prop('disabled', false).removeClass('gallery-loading-btn');
  }
}

function cargarGaleria(callback) {
  $.get('../api/v1/fulmuv/filesEmpresa/' + $("#id_empresa").val(), {}, function (returnedData) {
    const returned = typeof returnedData === 'string' ? JSON.parse(returnedData) : returnedData;
    if (returned.error === false) {
      GALLERY_FILES = returned.data || [];
      pintarGaleria(GALLERY_FILES);
      prepararLightbox();
      if (typeof callback === 'function') callback(returned);
    } else {
      renderEmptyGaleriaState();
    }
  });
}

function renderEmptyGaleriaState() {
  $("#galeria").html(`
    <div class="col-12">
      <div class="card border-200 shadow-sm" style="border-radius:18px;">
        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5" style="min-height:320px;">
          <div class="rounded-circle bg-body-tertiary d-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;">
            <span class="fas fa-images text-600 fs-4"></span>
          </div>
          <h4 class="mb-2">No existen elementos en la galería.</h4>
          <p class="text-600 mb-0" style="max-width:520px;">Cuando agregues tu primera imagen, aparecerá aquí para que puedas administrarla.</p>
        </div>
      </div>
    </div>
  `);
}

$(document).ready(function () {
  cargarGaleria();
});

function pintarGaleria(files) {
  const $g = $("#galeria").empty();
  if (!Array.isArray(files) || files.length === 0) {
    renderEmptyGaleriaState();
    return;
  }
  files.forEach((file, idx) => {
    const src = `../admin/${file.archivo}`;
    const title = file.titulo || 'Sin título';
    const desc  = file.descripcion || '';

    $g.append(`
      <div class="mb-4 col-12 col-md-6 col-lg-4" data-file-card="${file.id_archivo_empresa}">
        <div class="gallery-card" data-file-id="${file.id_archivo_empresa}">
          <div class="gallery-media">
            <img class="gallery-img" loading="lazy" src="${src}" alt="${escapeHtml(title)}"
                 onload="this.classList.add('is-loaded'); this.parentElement.classList.add('loaded');"
                 data-index="${idx}" onclick="openLightbox(${idx})">
            <div class="gallery-actions">
              <!--button class="btn btn-sm" title="Ver" onclick="openLightbox(${idx})">
                <i class="fas fa-external-link-alt"></i>
              </button-->
              <button class="btn btn-sm" title="Editar" onclick="editImagen(${file.id_archivo_empresa})">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm text-danger" title="Eliminar" onclick="remove(${file.id_archivo_empresa})">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          <div class="gallery-body">
            <h6 class="gallery-title" title="${escapeHtml(title)}">${escapeHtml(title)}</h6>
            <p class="gallery-desc" title="${escapeHtml(desc)}">${escapeHtml(desc)}</p>
            <div class="gallery-click-hint"><i class="far fa-image me-1"></i> Click en la imagen para ampliar</div>
          </div>
        </div>
      </div>
    `);
  });
}

// Utilidad para evitar XSS en títulos/descripciones
function escapeHtml(str){ return String(str||'').replace(/[&<>"']/g, s=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[s])); }

// -------- Lightbox ----------
function prepararLightbox(){
  const modalEl = document.getElementById('lightboxModal');
  if (!modalEl) return;
  lightboxModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

  // navegación con botones
  $('#lbPrev').off('click').on('click', ()=>navLightbox(-1));
  $('#lbNext').off('click').on('click', ()=>navLightbox(1));
  // navegación con teclado
  $(document).on('keydown.gallery', function(e){
    if (!$('#lightboxModal').hasClass('show')) return;
    if (e.key === 'ArrowLeft') navLightbox(-1);
    if (e.key === 'ArrowRight') navLightbox(1);
  });

  $(modalEl).off('hidden.bs.modal.galleryfix').on('hidden.bs.modal.galleryfix', function(){
    $('#lightboxImg').attr('src', '');
    $('#lightboxTitle').text('');
    $('#lightboxDesc').text('');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
  });
}

function openLightbox(index){
  currentIndex = index;
  const item = GALLERY_FILES[currentIndex];
  if(!item) return;

  const src = `../admin/${item.archivo}`;
  const title = item.titulo || 'Imagen';
  const desc  = item.descripcion || '';

  $('#lightboxImg').attr('src', src);
  $('#lightboxTitle').text(title);
  $('#lightboxDesc').text(desc);

  if (!lightboxModalInstance) {
    prepararLightbox();
  }
  lightboxModalInstance.show();
}

function navLightbox(delta){
  if (!GALLERY_FILES.length) return;
  currentIndex = (currentIndex + delta + GALLERY_FILES.length) % GALLERY_FILES.length;
  openLightbox(currentIndex);
}

function updateGaleria(id_archivo_empresa){
  const titulo = $("#titulo").val().trim();
  const descripcion = $("#descripcion").val().trim();
  if (!titulo || !descripcion) return SweetAlert("error", "Todos los campos son obligatorios");

  $.post('../api/v1/fulmuv/updateFileEmpresa', {
    id_archivo_empresa,             // ← IMPORTANTE
    id_empresa: $("#id_empresa").val(),
    titulo,
    descripcion
  }, function (returnedData) {
    const returned = typeof returnedData === 'string' ? JSON.parse(returnedData) : returnedData;
    if (returned.error === false) {
      SweetAlert("url_success", returned.msg, "galeria.php?id_empresa="+$("#id_empresa").val());
    } else {
      SweetAlert("error", returned.msg);
    }
  });
}

function editImagen(id_archivo_empresa){
  $.get('../api/v1/fulmuv/fileEmpresaById/' + id_archivo_empresa, {}, function (returnedData) {
    var returned = (typeof returnedData === 'string') ? JSON.parse(returnedData) : returnedData;
    if (returned.error) { return SweetAlert("error", returned.msg || "No se pudo obtener el recurso"); }

    var d = returned.data || {};
    var src = '../admin/' + (d.archivo || '');
    var titulo = d.titulo || '';
    var descripcion = d.descripcion || '';

    // limpiar modal previo
    $('#staticBackdrop').remove(); $('#btnModal').remove();

    $("#alert").append(`
      <button id="btnModal" class="btn btn-primary d-none" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">open</button>
      <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg mt-6" role="document">
          <div class="modal-content border-0">
            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
              <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                <h4 class="mb-1" id="staticBackdropLabel">Actualizar imagen</h4>
              </div>
              <div class="p-4">
                <div class="gallery-form-shell">
                <div class="row g-3">
                  <div class="col-md-7">
                    <label class="form-label">Título</label>
                    <input class="form-control" id="edit_titulo" type="text" maxlength="150" value="${$('<div>').text(titulo).html()}"/>
                    <label class="form-label mt-3">Descripción</label>
                    <textarea class="form-control gallery-description-lg" id="edit_descripcion" maxlength="300" placeholder="Describe mejor esta imagen para que sea más clara visualmente.">${$('<div>').text(descripcion).html()}</textarea>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label">Imagen seleccionada para actualizar</label>
                    <div class="ratio ratio-16x9 gallery-preview-box">
                      <img id="edit_preview" src="${src}" alt="preview" style="object-fit:cover;width:100%;height:100%;">
                    </div>
                    <div class="gallery-preview-meta">Solo se actualizará esta imagen de la galería.</div>
                  </div>
                  <div class="col-12 d-flex gap-2 justify-content-end">
                    <button class="btn btn-primary" id="btnUpdateGaleria">
                      <i class="fas fa-save me-1"></i> Actualizar
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                  </div>
                </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `);

    // acción actualizar
    $(document).off('click', '#btnUpdateGaleria').on('click', '#btnUpdateGaleria', function(){
      var tituloN = $("#edit_titulo").val().trim();
      var descN   = $("#edit_descripcion").val().trim();
      if (!tituloN || !descN) { return SweetAlert("error", "Todos los campos son obligatorios"); }
      setButtonLoading('#btnUpdateGaleria', true, 'Actualizando...');

      $.post('../api/v1/fulmuv/updateFileEmpresa', {
        id_archivo_empresa: id_archivo_empresa,            //  ← importante
        id_empresa: $("#id_empresa").val(),
        titulo: tituloN,
        descripcion: descN
      }, function (respData) {
        var resp = (typeof respData === 'string') ? JSON.parse(respData) : respData;
        if (resp.error === false) {
          SweetAlert("url_success", resp.msg || "Actualizado correctamente", getGaleriaRedirectUrl());
        } else {
          setButtonLoading('#btnUpdateGaleria', false);
          SweetAlert("error", resp.msg || "No se pudo actualizar");
        }
      }).fail(function(){
        setButtonLoading('#btnUpdateGaleria', false);
        SweetAlert("error", "No se pudo actualizar la imagen.");
      });
    });

    $("#btnModal").trigger('click');
  });
}

function remove(id_archivo_empresa){
  swal({
    title: "Alerta",
    text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    $.post('../api/v1/fulmuv/deleteFileEmpresa', {
      id_archivo: id_archivo_empresa
    }, function (returnedData) {
      var returned = (typeof returnedData === 'string') ? JSON.parse(returnedData) : returnedData;

      if (returned.error === false) {
        // si existe un botón/elemento con data-file-id, quitamos la tarjeta del DOM
        var $btn = $(`[data-file-id="${id_archivo_empresa}"]`);
        if ($btn.length) {
          $btn.closest('.mb-4, .col-12, .col-md-6, .col-lg-4').remove();
          swal.close();
          toastr.success(returned.msg || "Archivo eliminado");
        } else {
          // fallback: recargar
          SweetAlert("url_success", returned.msg || "Archivo eliminado", "galeria.php?id_empresa=" + $("#id_empresa").val());
        }
      } else {
        SweetAlert("error", returned.msg || "No se pudo eliminar");
      }
    });
  });
}

function registrarImagen() {
  var titulo = $("#titulo").val();
  var descripcion = $("#descripcion").val();

  if (titulo == "" || descripcion == "") {
    SweetAlert("error", "Todos los campos son obligatorios!!!");
  } else {
    setButtonLoading('#btnGuardarGaleria', true, 'Guardando...');
    var files = $('#archivo')[0].files[0];
    var filePromise = files === undefined ? Promise.resolve(empresaData.img_path) : saveFiles(files);

    filePromise.then(function (file) {
      $.post('../api/v1/fulmuv/createFileEmpresa', {
        id_empresa: $("#id_empresa").val(),
        titulo: titulo,
        descripcion: descripcion,
        archivo: file.img ? file.img : empresaData.img_path,
        tipo: 'imagen'
      }, function (returnedData) {
        var returned = JSON.parse(returnedData);
        if (returned.error == false) {
          SweetAlert("url_success", returned.msg, getGaleriaRedirectUrl());
        } else {
          setButtonLoading('#btnGuardarGaleria', false);
          SweetAlert("error", returned.msg);
        }
      }).fail(function(){
        setButtonLoading('#btnGuardarGaleria', false);
        SweetAlert("error", "No se pudo registrar la imagen.");
      });
    }).catch(function(){
      setButtonLoading('#btnGuardarGaleria', false);
    });
  }
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    console.log(files);
    if (files == undefined) {
      resolve(); // Resuelve la promesa incluso si no hay imágenes
    } else {
      const formData = new FormData();
      formData.append('archivos[]', files); // añadir los archivos al form

      $.ajax({
        type: 'POST',
        data: formData,
        url: '../admin/cargar_imagen.php',
        cache: false,
        contentType: false,
        processData: false,
        success: function (returnedImagen) {
          if (returnedImagen["response"] == "success") {
            resolve(returnedImagen["data"]); // Resuelve la promesa cuando la llamada AJAX se completa con éxito
          } else {
            SweetAlert("error", "Ocurrió un error al guardar los archivos. " + returnedImagen["error"]);
            reject(); // Rechaza la promesa en caso de error
          }
        },
        error: function (xhr, status, error) {
          console.error("Error en la carga AJAX:", error);
          SweetAlert("error", "Error al subir la imagen: " + error);
          reject();
        }
      });
    }
  });
}

function addImagen() {
  // Evita duplicados si ya abriste el modal antes
  $('#staticBackdrop').remove();
  $('#btnModal').remove();

  $("#alert").text("");
  $("#alert").append(`
    <button id="btnModal" class="btn btn-primary d-none" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
      Launch static backdrop modal
    </button>
      <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-md mt-6" role="document">
        <div class="modal-content border-0">
          <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
              <h4 class="mb-1" id="staticBackdropLabel">Registrar Imagen</h4>
            </div>
            <div class="p-4">
              <div class="gallery-form-shell">
              <div class="row g-2">
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="titulo">Título</label>
                  <input class="form-control" id="titulo" type="text" placeholder="Ingrese un título para identificar la imagen"/>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="descripcion">Descripción</label>
                  <textarea class="form-control gallery-description-lg" id="descripcion" placeholder="Agrega una descripción más completa para esta imagen de galería."></textarea>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label" for="archivo">Archivo</label>
                  <div class="gallery-upload-box">
                    <input class="form-control" id="archivo" type="file" accept="image/*"/>
                    <div class="gallery-preview-meta">Selecciona una imagen clara y representativa de tu negocio.</div>
                  </div>
                </div>
                <div class="col-12 d-flex justify-content-end">
                  <button onclick="registrarImagen()" class="btn btn-iso" id="btnGuardarGaleria" type="button">
                    <i class="fas fa-save me-1"></i> Guardar
                  </button>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `);

  $("#btnModal").trigger('click');
}
