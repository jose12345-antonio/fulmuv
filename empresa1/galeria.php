<?php
$menu = "productos";
$sub_menu = "crear_producto";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
  .gallery-card {
    border-radius: .5rem;
    border: 1px solid #e6e8ea;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    overflow: hidden;
    background: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  /* Contenedor con relación de aspecto */
  .gallery-media {
    position: relative;
    aspect-ratio: 16 / 9; /* Cambia a 4/3 si lo prefieres */
    background: #f4f6f8;
  }
  /* Skeleton */
  .gallery-media::before {
    content: "";
    position: absolute; inset: 0;
    background: linear-gradient(90deg, #f4f6f8 25%, #eef1f4 37%, #f4f6f8 63%);
    background-size: 400% 100%;
    animation: shimmer 1.2s infinite;
  }
  @keyframes shimmer { 0% {background-position: 100% 0;} 100%{background-position: 0 0;} }

  .gallery-img {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
  }
  /* Quita skeleton cuando la imagen carga */
  .gallery-img.is-loaded ~ .skeleton,
  .gallery-media.loaded::before { display: none; }

  /* Overlay de acciones */
  .gallery-actions {
    position: absolute; right: .5rem; bottom: .5rem; z-index: 2;
    display: flex; gap: .35rem;
  }
  .gallery-actions .btn {
    backdrop-filter: blur(6px);
    background: rgba(255,255,255,.85);
    border: 1px solid rgba(0,0,0,.08);
  }
  .gallery-body {
    padding: .75rem .9rem;
  }
  .gallery-title { margin: 0 0 .25rem; font-size: .95rem; font-weight: 600; color:#1b1f24; }
  .gallery-desc  { margin: 0; font-size: .85rem; color:#5c6670; }
</style>
<title>Galería</title>
<div class="card mb-3">
    <div class="card-body">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center">
                <div class="col-md-auto">
                    <h5 class="mb-2 mb-md-0">Galería</h5>
                </div>

            </div>
            <div class="row flex-between-center">
                <div class="col-auto">
                    <button onclick="addImagen()" class="btn btn-primary" role="button">Agregar imagen </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-0">
    <div class="col-lg-12 pe-lg-2">
        <div class="card mb-3">
            <!-- <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Galería</h6>
            </div> -->
            <div class="card-body">
                <div class="row" id="galeria">
                    
                </div>
                <!-- <div class="col-12 mb-2">
                    <h6 class="">Archivos (Imagen y Ficha Técnica)</h6>
                    <form class="dropzone dropzone-multiple p-0" id="myAwesomeDropzone" action="../admin/cargar_imagen_drop.php" data-dropzone="data-dropzone">
                    <div class="fallback">
                        <input name="archivos[]" type="file" multiple />
                    </div>
                    <div class="dz-message my-0" data-dz-message="data-dz-message">
                        <img class="me-2" src="../theme/public/assets/img/icons/cloud-upload.svg" width="25" alt="" />
                        <span class="d-none d-lg-inline">Drag your image here<br />or, </span>
                        <span class="btn btn-link p-0 fs-10">Browse</span>
                    </div>
                    <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column" id="file-previews"></div>
                    </form>
                </div>
                <div id="uploadPreviewTemplate" style="display: none;">
                    <div class="d-flex media align-items-center mb-3 pb-3 border-bottom btn-reveal-trigger dz-file-preview">
                    <div class="avatar avatar-2xl me-2">
                        <img class="rounded-soft border" src="../theme/public/assets/img/generic/image-file-2.png" alt="" data-dz-thumbnail />
                    </div>
                    <div class="flex-1 d-flex flex-between-center">
                        <div>
                        <a target="_blank" class="h6" data-dz-name></a>
                        <div class="d-flex align-items-center">
                            <p class="mb-0 fs-10 text-400 lh-1" data-dz-size></p>
                        </div>
                        </div>
                        <div class="dropdown font-sans-serif file_buttons">
                        </div>
                    </div>
                    </div>
                </div> -->
                <!-- <a href="#" data-gallery="gallery-2">
                    <img class="img-fluid rounded" src="../theme/assets/img/generic/11.jpg" alt="" width="300" />
                </a> -->
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <h5 class="modal-title text-white" id="lightboxTitle"></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex align-items-center justify-content-center p-0">
        <img id="lightboxImg" src="" alt="" style="max-width:100%;max-height:100vh;object-fit:contain;">
      </div>
      <div class="modal-footer border-0 justify-content-between">
        <div class="text-white-50" id="lightboxDesc"></div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-light" id="lbPrev" type="button">‹ Anterior</button>
          <button class="btn btn-light" id="lbNext" type="button">Siguiente ›</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Conexión API js -->
<script src="js/galeria.js?v2.0.0.0.0.1"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>