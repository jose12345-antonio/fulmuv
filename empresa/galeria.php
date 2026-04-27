<?php
$menu = "galeria";
$sub_menu = "galeria";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Productos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
  .gallery-toolbar-card{
    border-radius: 18px;
    border: 1px solid #e6edf3;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    background: linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);
  }
  .gallery-toolbar-title{
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
  }
  .gallery-toolbar-text{
    font-size: .88rem;
    color: #64748b;
    margin-bottom: 0;
  }
  .gallery-list-shell{
    border-radius: 18px;
    border: 1px solid #e6edf3;
    box-shadow: 0 14px 32px rgba(15, 23, 42, .06);
    background: linear-gradient(180deg,#ffffff 0%,#fbfdff 100%);
  }
  .gallery-card {
    border-radius: 1rem;
    border: 1px solid #e6edf3;
    box-shadow: 0 10px 24px rgba(15,23,42,.08);
    overflow: hidden;
    background: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
  }
  .gallery-card:hover{
    transform: translateY(-3px);
    border-color: rgba(0, 104, 111, .28);
    box-shadow: 0 16px 32px rgba(15,23,42,.12);
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
    position: absolute; right: .65rem; bottom: .65rem; z-index: 2;
    display: flex; gap: .35rem;
  }
  .gallery-actions .btn {
    backdrop-filter: blur(6px);
    background: rgba(255,255,255,.85);
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 999px;
    width: 36px;
    height: 36px;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .gallery-body {
    padding: 1rem 1rem 1.05rem;
  }
  .gallery-title { margin: 0 0 .35rem; font-size: 1rem; font-weight: 700; color:#1b1f24; }
  .gallery-desc  { margin: 0; font-size: .92rem; line-height: 1.6; color:#5c6670; min-height: 44px; }
  .gallery-click-hint{
    margin-top: .6rem;
    font-size: .75rem;
    color:#94a3b8;
  }
  .gallery-form-shell{
    background: linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);
    border: 1px solid #e6edf3;
    border-radius: 18px;
    padding: 18px;
  }
  .gallery-preview-box{
    border: 1px solid #dbe4ea;
    border-radius: 16px;
    overflow: hidden;
    background: #eef2f7;
  }
  .gallery-preview-meta{
    font-size: .76rem;
    color: #64748b;
    margin-top: 8px;
  }
  .gallery-description-lg{
    min-height: 124px;
    resize: vertical;
    font-size: 1rem;
    line-height: 1.65;
  }
  .gallery-upload-box{
    border: 1.5px dashed #cbd5e1;
    border-radius: 16px;
    background: #f8fafc;
    padding: 18px;
  }
  .gallery-loading-btn .spinner-border{
    width: 1rem;
    height: 1rem;
    border-width: .16em;
  }
  .modal-gallery-fix .modal-content{
    background: #020617;
  }
  #lightboxModal .modal-body{
    min-height: calc(100vh - 160px);
  }
</style>
<title>Galería</title>
<div class="card gallery-toolbar-card mb-3">
    <div class="card-body">
        <div class="d-lg-flex justify-content-between">
            <div class="row flex-between-center">
                <div class="col-md-auto">
                    <h5 class="mb-1 gallery-toolbar-title">Galería</h5>
                    <p class="gallery-toolbar-text">Administra tus imágenes, edítalas rápidamente y visualízalas en un visor completo.</p>
                </div>

            </div>
            <div class="row flex-between-center">
                <div class="col-auto">
                    <button onclick="addImagen()" class="btn btn-primary" role="button">
                      <i class="fas fa-plus me-1"></i> Agregar imagen
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-0">
    <div class="col-lg-12 pe-lg-2">
        <div class="card gallery-list-shell mb-3">
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
<div class="modal fade modal-gallery-fix" id="lightboxModal" tabindex="-1" aria-hidden="true">
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
<script src="js/galeria.js?v2.0.0.0.0.8"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
