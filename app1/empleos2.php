<?php
include 'includes/header.php';
$sinCuentaMode = defined('APP_SIN_CUENTA') && APP_SIN_CUENTA;
echo '<input type="hidden" id="id_usuario" value="' . htmlspecialchars($_SESSION["id_usuario"] ?? "", ENT_QUOTES, "UTF-8") . '">';
?>
<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>
    });
</script>
<link rel="canonical" href="https://fulmuv.com/empleos.php">

<style>
    :root {
        --jobs-page-bg: #f8fafc;
        --jobs-surface: #ffffff;
        --jobs-surface-soft: #eef2f7;
        --jobs-border: rgba(15, 23, 42, 0.08);
        --jobs-text: #0f172a;
        --jobs-text-secondary: #64748b;
        --jobs-accent: #004e60;
        --jobs-accent-2: #0f766e;
        --jobs-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    body,
    body .main.pages,
    body .page-content {
        background: var(--jobs-page-bg);
        color: var(--jobs-text);
    }

    .jobs-shell {
        padding: 10px 0 40px;
    }

    .jobs-hero {
        position: relative;
        overflow: hidden;
        margin: 12px 0 20px;
        padding: 24px 22px;
        border: 1px solid var(--jobs-border);
        background:
            radial-gradient(circle at top left, rgba(15, 118, 110, 0.18), transparent 38%),
            linear-gradient(135deg, #ffffff 0%, #eef5f8 100%);
        box-shadow: var(--jobs-shadow);
    }

    .jobs-hero::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -50px;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: rgba(0, 78, 96, 0.08);
    }

    .jobs-hero h1 {
        position: relative;
        z-index: 1;
        font-size: clamp(28px, 5vw, 40px);
        font-weight: 900;
        margin: 0 0 10px;
        color: var(--jobs-text);
    }

    .jobs-hero p {
        position: relative;
        z-index: 1;
        max-width: 720px;
        margin: 0;
        color: var(--jobs-text-secondary);
        line-height: 1.65;
    }

    .archive-header-2 {
        margin-top: 0 !important;
    }

    .widget_search .search-form {
        background: var(--jobs-surface);
        border: 1px solid var(--jobs-border);
        box-shadow: var(--jobs-shadow);
        padding: 10px;
    }

    .widget_search .search-form input {
        background: var(--jobs-surface-soft);
        border: 1px solid transparent;
        min-height: 48px;
    }

    .widget_search .search-form button {
        background: linear-gradient(135deg, var(--jobs-accent) 0%, var(--jobs-accent-2) 100%);
        color: #fff;
        border: none;
        min-width: 48px;
    }

    .shop-product-fillter {
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 14px 16px;
        margin-bottom: 18px;
        background: color-mix(in srgb, var(--jobs-surface) 92%, transparent);
        backdrop-filter: blur(14px);
        border: 1px solid var(--jobs-border);
        box-shadow: var(--jobs-shadow);
    }

    .loop-grid {
        padding: 6px 0;
    }
    /* Todas las imÃ¡genes del card con mismo alto y centradas */
    .card-empleo .card-img-top {
        height: 220px;
        /* ajusta si quieres mÃ¡s o menos alto */
        object-fit: cover;
    }

    /* Efecto hover en la tarjeta completa */
    .card-empleo {
        transition: box-shadow .25s ease, transform .25s ease;
    }

    .card-empleo:hover {
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        /* sombra mÃ¡s fuerte */
        transform: translateY(-4px);
        /* la tarjeta sube un poco */
    }

    /* ===== Modal layout moderno ===== */
    #modalPostular .modal-content {
        border-radius: 18px;
    }

    #modalPostular .modal-body {
        max-height: 82vh;
        /* modal alto controlado */
        overflow: hidden;
        /* evita scroll feo del body */
    }

    #modalPostular .modal-grid {
        height: 82vh;
        /* mismo alto que modal-body */
    }

    /* ===== Columna izquierda ===== */
    #modalPostular .left-pane {
        height: 82vh;
        display: flex;
        flex-direction: column;
        background: #0b1220;
    }

    #modalPostular .left-carousel-wrap {
        position: relative;
        height: 46%;
        /* ~50% pero mÃ¡s elegante */
        min-height: 260px;
    }

    #modalPostular #carouselEmpleo,
    #modalPostular #carouselEmpleo .carousel-inner,
    #modalPostular #carouselEmpleo .carousel-item {
        height: 100%;
    }

    /* Fondo con â€œcoverâ€ blur para que no se vea vacÃ­o con contain */
    #modalPostular .carousel-item {
        position: relative;
        background: #0b1220;
    }

    #modalPostular .carousel-item::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: var(--bg);
        background-size: cover;
        background-position: center;
        filter: blur(18px);
        transform: scale(1.15);
        opacity: .40;
    }

    #modalPostular .carousel-item img {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
        object-fit: contain;
        /* lo que pediste */
        padding: 10px 18px;
    }

    /* Indicadores mÃ¡s bonitos */
    #modalPostular .carousel-indicators {
        margin-bottom: 10px;
    }

    #modalPostular .carousel-indicators [data-bs-target] {
        width: 10px;
        height: 10px;
        border-radius: 999px;
    }

    /* Overlay info */
    #modalPostular .left-overlay {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 14px 16px;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, .55) 48%, rgba(0, 0, 0, .85) 100%);
    }

    #modalPostular .job-title {
        font-size: 18px;
        line-height: 1.2;
        font-weight: 800;
    }

    #modalPostular .job-meta {
        opacity: .9;
    }

    /* DescripciÃ³n debajo: scroll interno */
    #modalPostular .left-desc {
        height: 54%;
        background: #fff;
        border-top: 1px solid rgba(255, 255, 255, .08);
        padding: 14px 14px 18px;
        overflow: hidden;
    }

    #modalPostular .left-desc .desc-box {
        height: calc(100% - 26px);
        overflow-y: auto;
        padding-right: 6px;
    }

    #modalPostular .left-desc .desc-box::-webkit-scrollbar {
        width: 8px;
    }

    #modalPostular .left-desc .desc-box::-webkit-scrollbar-thumb {
        background: #d6d6d6;
        border-radius: 999px;
    }

    /* ===== Columna derecha (form) ===== */
    #modalPostular .right-pane {
        height: 82vh;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    #modalPostular .right-body {
        padding: 18px 18px 0;
        overflow-y: auto;
        /* scroll solo aquÃ­ */
        flex: 1;
    }

    #modalPostular .right-footer {
        padding: 14px 18px;
        border-top: 1px solid #eee;
        background: #fff;
    }

    .guest-login-alert {
        padding: 14px 16px;
        border: 1px solid rgba(0, 78, 96, 0.14);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(0, 78, 96, 0.08) 0%, rgba(15, 118, 110, 0.08) 100%);
        margin-bottom: 14px;
    }

    .guest-login-alert strong {
        display: block;
        margin-bottom: 4px;
        color: #0f172a;
    }

    .guest-login-alert span {
        color: #475569;
        font-size: 14px;
        line-height: 1.55;
    }

    /* Inputs mÃ¡s modernos */
    #modalPostular .form-control {
        border-radius: 12px;
        padding: 11px 12px;
    }

    /* Botones */
    #modalPostular .btn {
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 700;
    }

    /* Responsive: en mÃ³vil, apila */
    @media (max-width: 991.98px) {

        #modalPostular .modal-body,
        #modalPostular .modal-grid,
        #modalPostular .left-pane,
        #modalPostular .right-pane {
            height: auto;
            max-height: none;
        }

        #modalPostular .left-carousel-wrap {
            height: 260px;
        }

        #modalPostular .left-desc {
            height: auto;
            max-height: 260px;
        }
    }

    h2 {
        font-size: 18px
    }

    h3 {
        font-size: 17px
    }

    p {
        font-size: 16px
    }

    /* Contenedor del carrusel debe ser relative */
    .left-carousel-wrap {
        position: relative;
    }

    /* Overlay SIEMPRE visible */
    .left-overlay {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;

        z-index: 10;
        /* ðŸ‘ˆ importante */

        padding: 16px 18px;

        background: linear-gradient(180deg,
                rgba(0, 0, 0, 0) 0%,
                rgba(0, 0, 0, 0.55) 45%,
                rgba(0, 0, 0, 0.85) 100%);

        color: #fff;

        pointer-events: none;
        /* ðŸ‘ˆ evita que el carrusel deje de funcionar */
    }

    /* Texto mÃ¡s visible */
    .left-overlay .job-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .left-overlay .job-meta {
        font-size: 13px;
        opacity: 0.9;
    }

    /* Mejora de Cards de Empleo */
    .card-empleo {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff;
        box-shadow: var(--jobs-shadow);
        overflow: hidden;
    }

    .card-empleo:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.15);
    }

    .empleo-img {
        border-bottom: 1px solid #f1f5f9;
        height: 200px !important;
    }

    .card-title a {
        color: #1e293b;
        font-weight: 700;
        font-size: 1.1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .badge-location {
        background-color: #f1f5f9;
        color: #475569;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
    }

    .btn-postular {
        border-radius: 10px;
        padding: 8px 20px;
        font-weight: 600;
        letter-spacing: 0.5px;
        background: #2563eb;
        /* Azul corporativo */
        border: none;
    }

    .jobs-empty-state {
        max-width: 520px;
        margin: 0 auto;
        padding: 28px 22px;
        border: 1px solid var(--jobs-border);
        background: linear-gradient(180deg, var(--jobs-surface) 0%, var(--jobs-surface-soft) 100%);
        box-shadow: var(--jobs-shadow);
    }
</style>

<div class="container jobs-shell">
    <section class="jobs-hero">
        <h1>Empleos que conectan talento con movimiento</h1>
        <p>Explora vacantes activas, filtra por ubicaciÃ³n y postÃºlate desde una vista mÃ¡s clara, rÃ¡pida y pensada para mÃ³vil, tablet y app.</p>
    </section>
    <div class="archive-header-2 text-center mt-30">
        <!-- <h1 class="display-2 mb-50">Lista de Productos</h1> -->
        <div class="row">
            <div class="col-lg-12">
                <div class="sidebar-widget-2 widget_search mb-50">
                    <div class="search-form">
                        <form action="#">
                            <input type="text" placeholder="Buscar por tÃ­tulo de empleos" />
                            <button type="submit"><i class="fi-rs-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row flex-row-reverse" style="transform: none;">
        <div class="col-lg-12">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <h5>Encontramos <strong class="text-brand" id="totalProductosGeneral"></strong> empleos para ti!</h5>
                </div>
                <div class="sort-by-product-area">
                    <div class="sort-by-cover d-flex justify-content-center align-items-center me-2">
                        <div>
                            <button type="button" id="btnUbicacion" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <i class="fi-rs-marker me-1"></i> Cambiar ubicaciÃ³n
                            </button>
                        </div>
                    </div>
                    <div class="sort-by-cover mr-10">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps"></i>Show:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> 6 <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-show">
                            <ul>
                                <li><a class="active" href="#" data-value="6">6</a></li>
                                <li><a href="#" data-value="12">12</a></li>
                                <li><a href="#" data-value="18">18</a></li>
                                <li><a href="#" data-value="24">24</a></li>
                                <li><a href="#" data-value="30">30</a></li>
                                <li><a href="#" data-value="40">40</a></li>
                                <li><a href="#" data-value="all">All</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="loop-grid">
                <div class="row" id="listaEmpleos">

                </div>
            </div>
            <!-- <div class="loop-grid loop-list pr-30 mb-50"></div> -->

            <div class="pagination-area mt-15 mb-sm-5 mb-lg-0">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start" id="paginacionEventos"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>


<!-- Modal UbicaciÃ³n -->
<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalUbicacionLabel">
                    <i class="fi-rs-marker me-1"></i> Elige tu ubicaciÃ³n
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label mb-1">Provincia</label>
                    <select id="selectProvincia" class="form-control" required></select>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1">CantÃ³n</label>
                    <select id="selectCanton" class="form-control" required></select>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" id="limpiarUbicacion">Limpiar ubicaciÃ³n</button>
                <button type="button" class="btn btn-primary" id="guardarUbicacion">Listo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal PostulaciÃ³n -->
<div class="modal fade" id="modalPostular" tabindex="-1" aria-labelledby="modalPostularLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header border-0 bg-light py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width:38px;height:38px;background:#111827;color:#fff;">
                        <i class="fi-rs-briefcase"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modalPostularLabel">Postular</h5>
                        <small class="text-muted">Revisa el empleo y completa tus datos</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-0">
                <div class="row g-0 modal-grid">

                    <!-- ===== COLUMNA IZQUIERDA ===== -->
                    <div class="col-lg-5 left-pane">

                        <!-- CAROUSEL -->
                        <div class="left-carousel-wrap">

                            <div id="carouselEmpleo" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="4000">
                                <div class="carousel-inner" id="carouselEmpleoInner">
                                    <!-- Se llena por JS -->
                                </div>

                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselEmpleo" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>

                                <button class="carousel-control-next" type="button" data-bs-target="#carouselEmpleo" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>

                                <div class="carousel-indicators" id="carouselEmpleoIndicators"></div>
                            </div>

                            <!-- Overlay informaciÃ³n -->
                            <div class="left-overlay">
                                <h5 class="text-white mb-1 job-title" id="postularTitulo"></h5>
                                <div class="text-white small job-meta" id="postularUbicacion"></div>
                                <div class="text-white small job-meta" id="postularEmpresa"></div>
                            </div>

                        </div>

                        <!-- DESCRIPCIÃ“N -->
                        <div class="left-desc">
                            <label class="form-label mb-1 text-muted">DescripciÃ³n del empleo</label>
                            <div id="postularDescripcion" class="desc-box small"></div>
                        </div>

                    </div>

                    <!-- ===== COLUMNA DERECHA ===== -->
                    <div class="col-lg-7 right-pane">

                        <div class="right-body">

                            <!-- Campos ocultos -->
                            <input type="hidden" id="postular_id_empleo" name="id_empleo">
                            <input type="hidden" id="postular_id_empresa" name="id_empresa">

                            <?php if ($sinCuentaMode): ?>
                                <div class="guest-login-alert">
                                    <strong>Inicia sesion para postularte</strong>
                                    <span>Debes iniciar sesion para hacer uso de la gestion de postulacion y seguimiento dentro de FULMUV.</span>
                                </div>
                            <?php endif; ?>

                            <div class="row g-3">

                                <div class="col-md-12">
                                    <label class="form-label mb-1">Nombres y apellidos</label>
                                    <input type="text" class="form-control" id="nombres_apellidos"
                                        placeholder="Ej: Juan PÃ©rez" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label mb-1">CÃ©dula</label>
                                    <input type="text" class="form-control" id="cedula"
                                        placeholder="10 dÃ­gitos" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label mb-1">Correo</label>
                                    <input type="email" class="form-control" id="correo"
                                        placeholder="correo@dominio.com" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label mb-1">TelÃ©fono</label>
                                    <input type="text" class="form-control" id="telefono"
                                        placeholder="Ej: 0999999999" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label mb-1">Hoja de vida (PDF o Word)</label>
                                    <input type="file" class="form-control" id="cv_pdf" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <button type="button" class="btn btn-outline-primary" id="btnSeleccionarCv">Seleccionar archivo</button>
                                        <small class="text-muted align-self-center">PDF, DOC o DOCX. TamaÃ±o recomendado: hasta 5MB.</small>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- FOOTER BOTONES -->
                        <div class="right-footer d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cerrar
                            </button>

                            <button type="button" class="btn btn-primary" onclick="enviarPostulacion()">
                                <i class="fi-rs-paper-plane me-1"></i> Enviar postulaciÃ³n
                            </button>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
<script src="js/eventos.js"></script>

<script>
    /* ===== Estado global ===== */
    let eventosPorPagina = 6;
    let todosLosEventos = [];
    let filtradosCache = [];

    // Estado seleccionado
    let provinciaSel = {
        id: null,
        nombre: null
    };
    let cantonSel = {
        id: null,
        nombre: null
    };
    let catsIndex = null;
    let datosEcuador = {};


    fetch('../provincia_canton_parroquia.json')
        .then(res => res.json())
        .then(data => {
            datosEcuador = data;
            cargarProvincias();
        });


    /* Estado de filtros */
    const stateFiltros = {
        subtipos: new Set(),
        modalidad: new Set(),
        tipoEntrada: new Set()
    };

    const idUsuarioActual = ($("#id_usuario").val() || "").trim();
    const sinCuentaApp = !!window.APP_MODE_CONFIG?.sinCuenta;

    /* ===== Helpers ===== */
    const norm = s => (s ?? '').toString().trim().toLowerCase();
    const cap = s => (s ?? '').toString().trim().replace(/^\p{L}/u, c => c.toUpperCase());

    function capitalizarPrimeraLetra(s) {
        s = (s ?? '').toString().trim();
        return s ? s[0].toUpperCase() + s.slice(1) : s;
    }

    function mostrarAvisoLoginEmpleos() {
        Swal.fire({
            icon: 'info',
            title: 'Inicia sesion',
            text: 'Debes iniciar sesion para hacer uso de la gestion de postulacion en empleos.'
        });
    }

    function enviarPostulacion() {
        if (sinCuentaApp) {
            mostrarAvisoLoginEmpleos();
            return;
        }

        // 1. Referencia al botón y su contenido original
        const btn = document.querySelector('#modalPostular .btn-primary[onclick="enviarPostulacion()"]');
        const originalHTML = btn.innerHTML;

        var nombres_apellidos = $("#nombres_apellidos").val();
        var postular_id_empleo = $("#postular_id_empleo").val();
        var postular_id_empresa = $("#postular_id_empresa").val();
        var cedula = $("#cedula").val();
        var correo = $("#correo").val();
        var telefono = $("#telefono").val();
        var files = $("#cv_pdf")[0].files;

        // ValidaciÃ³n campos vacÃ­os
        if (!nombres_apellidos || !cedula || !correo || !telefono || !files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Faltan datos',
                text: 'Por favor complete todos los campos y adjunte su CV.'
            });
            return;
        }

        if (!validarCorreoElectronico(correo)) {
            Swal.fire({
                icon: 'warning',
                title: 'Correo no vÃ¡lido',
                text: 'Ingresa un correo electrÃ³nico vÃ¡lido.'
            });
            return;
        }

        // 2. ACTIVAR LOADING: Deshabilitar y poner Spinner
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...`;

        const formData = new FormData();
        Array.from(files).forEach(file => {
            formData.append("archivos[]", file);
        });

        // Proceso de subida de PDF
        fetch("cargar_pdf_cv.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.response === "success") {
                    // Registro del postulante en la base de datos
                    $.post("../api/v1/fulmuv/subirPostulante/create", {
                        nombres_apellidos: nombres_apellidos,
                        cedula: cedula,
                        correo: correo,
                        telefono: telefono,
                        cv: data.data.archivos[0].archivo,
                        postular_id_empleo: postular_id_empleo,
                        postular_id_empresa: postular_id_empresa
                    }, function(returnedData) {

                        // 3. FINALIZAR LOADING (Ã‰xito o Error de API)
                        if (!returnedData.error) {
                            Swal.fire({
                                icon: 'success',
                                title: 'FULMUV',
                                text: returnedData.msg,
                                confirmButtonColor: "#242619"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = originalHTML;
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: returnedData.msg
                            });
                        }
                    }, 'json').fail(() => {
                        // Manejo de error en la peticiÃ³n POST
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexiÃ³n con el servidor.'
                        });
                    });

                } else {
                    // Error al subir el PDF
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo subir el archivo PDF.'
                    });
                }
            })
            .catch(err => {
                // 4. FINALIZAR LOADING (Error de Red)
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                console.error("Error:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'OcurriÃ³ un error inesperado.'
                });
            });
    }


    function getEmpleoById(id) {
        return todosLosEventos.find(e => String(e.id_empleo) === String(id));
    }

    /* ===== ConstrucciÃ³n de filtros (sin repetidos) ===== */
    function buildFiltros(data) {
        // SUBTIPOS
        const mapSubtipos = new Map();
        data.forEach(ev => {
            console.log(ev.subtipos)
            if (Array.isArray(ev.subtipos) && ev.subtipos.length) {
                ev.subtipos.forEach(s => {
                    const id = Number(s.id);
                    if (id > 0 && !mapSubtipos.has(id)) mapSubtipos.set(id, s.nombre);
                });
                return;
            }
            console.log(ev.subtipo_evento)
            if (ev.subtipo_evento) {
                let raw = String(ev.subtipo_evento).replaceAll('\\', '');
                try {
                    const arr = JSON.parse(raw);
                    if (Array.isArray(arr)) {
                        arr.forEach(idStr => {
                            const id = Number(String(idStr).replace(/\D+/g, ''));
                            if (id > 0 && !mapSubtipos.has(id)) mapSubtipos.set(id, 'Subtipo ' + id);
                        });
                    }
                } catch (_) {}
            }
        });

        // MODALIDAD
        const setModalidad = new Set();
        data.forEach(ev => {
            const v = (ev.modalidad || '').toString().trim();
            if (v) setModalidad.add(v);
        });

        // TIPO ENTRADA
        const setTipoEnt = new Set();
        data.forEach(ev => {
            const v = (ev.tipo_entrada || '').toString().trim();
            if (v) setTipoEnt.add(v);
        });

        // Render de grupos
        renderCheckGroup('#filtro-subtipo',
            [...mapSubtipos.entries()].map(([id, nombre]) => ({
                id: 'sub_' + id,
                name: 'flt-subtipo',
                value: String(id),
                label: nombre
            }))
        );
        renderCheckGroup('#filtro-modalidad',
            [...setModalidad].map(v => ({
                id: 'mod_' + v,
                name: 'flt-modalidad',
                value: v,
                label: capitalizarPrimeraLetra(v)
            }))
        );
        renderCheckGroup('#filtro-tipo_entrada',
            [...setTipoEnt].map(v => ({
                id: 'ten_' + v,
                name: 'flt-tipo-entrada',
                value: v,
                label: capitalizarPrimeraLetra(v)
            }))
        );

        // Listeners
        $('#filtro-subtipo').off('change').on('change', 'input[name="flt-subtipo"]', function() {
            this.checked ? stateFiltros.subtipos.add(this.value) : stateFiltros.subtipos.delete(this.value);
            filtrarYMostrar(1);
        });
        $('#filtro-modalidad').off('change').on('change', 'input[name="flt-modalidad"]', function() {
            this.checked ? stateFiltros.modalidad.add(this.value) : stateFiltros.modalidad.delete(this.value);
            filtrarYMostrar(1);
        });
        $('#filtro-tipo_entrada').off('change').on('change', 'input[name="flt-tipo-entrada"]', function() {
            this.checked ? stateFiltros.tipoEntrada.add(this.value) : stateFiltros.tipoEntrada.delete(this.value);
            filtrarYMostrar(1);
        });
    }

    function renderCheckGroup(containerSel, items) {
        const wrap = document.querySelector(containerSel);
        if (!wrap) return;
        wrap.innerHTML = '';
        items.forEach(it => {
            wrap.insertAdjacentHTML('beforeend', `
      <div class="form-check mb-1">
        <input class="form-check-input" type="checkbox" id="${it.id}" name="${it.name}" value="${it.value}">
        <label class="form-check-label" for="${it.id}">${it.label}</label>
      </div>
    `);
        });
    }

    const modalPostularEl = document.getElementById('modalPostular');

    function buildCarousel(images) {
        const inner = document.getElementById('carouselEmpleoInner');
        const indicators = document.getElementById('carouselEmpleoIndicators');
        inner.innerHTML = '';
        indicators.innerHTML = '';

        if (!images.length) {
            images = ['../img/FULMUV-NEGRO.png'];
        }

        images.forEach((src, i) => {
            inner.insertAdjacentHTML('beforeend', `
            <div class="carousel-item ${i === 0 ? 'active' : ''}" style="--bg:url('${src}')">
                <img src="${src}" class="d-block w-100"
                    onerror="this.src='../img/FULMUV-NEGRO.png'">
            </div>
            `);

            indicators.insertAdjacentHTML('beforeend', `
      <button type="button" data-bs-target="#carouselEmpleo" data-bs-slide-to="${i}"
              class="${i === 0 ? 'active' : ''}" ${i === 0 ? 'aria-current="true"' : ''} aria-label="Slide ${i+1}"></button>
    `);
        });

        // asegurar autoplay a 4s
        const el = document.getElementById('carouselEmpleo');
        // const instance = bootstrap.Carousel.getOrCreateInstance(el, {
        //     interval: 4000,
        //     ride: 'carousel',
        //     pause: false,
        //     touch: true,
        //     wrap: true
        // });
        // instance.cycle();
    }

    if (modalPostularEl) {
        modalPostularEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;

            // limpiar campos
            document.getElementById('postular_id_empleo').value = "";
            document.getElementById('postular_id_empresa').value = "";
            document.getElementById('nombres_apellidos').value = "";
            document.getElementById('cedula').value = "";
            document.getElementById('correo').value = "";
            document.getElementById('telefono').value = "";
            document.getElementById('postularTitulo').textContent = "";
            document.getElementById('postularDescripcion').innerHTML = "";

            const idEmpleo = button.getAttribute('data-id') || '';
            const titulo = button.getAttribute('data-titulo') || '';
            const idEmpresa = button.getAttribute('data-id_empresa') || '';

            document.getElementById('postular_id_empleo').value = idEmpleo;
            document.getElementById('postular_id_empresa').value = idEmpresa;
            document.getElementById('postularTitulo').textContent = titulo;

            // Buscar empleo completo
            const empleo = getEmpleoById(idEmpleo);

            if (empleo) {
                let htmlEmpresa = '';

                // Validamos si es sucursal o empresa general
                if (empleo.tipo_creador === 'sucursal') {
                    // Si es sucursal, podrÃ­as incluso mostrar el nombre del cantÃ³n o un tag especÃ­fico
                    htmlEmpresa = `
                        <span class="badge bg-info text-dark mb-2">
                            <i class="fi-rs-shop me-1"></i> Sucursal: ${empleo.canton || 'Local'}
                        </span>`;
                } else {
                    htmlEmpresa = `
                        <span class="text-muted small">
                            <i class="fi-rs-building me-1"></i> Empresa: ${empleo.empresa || 'General'}
                        </span>`;
                }

                document.getElementById('postularEmpresa').innerHTML = htmlEmpresa;
            }

            // DescripciÃ³n
            if (empleo && empleo.descripcion) {
                document.getElementById('postularDescripcion').innerHTML = empleo.descripcion;
            } else {
                document.getElementById('postularDescripcion').textContent = 'Este empleo no tiene una descripciÃ³n detallada.';
            }

            // UbicaciÃ³n (tu data viene como string: "BolÃ­var", "Chillanes")
            const prov = (empleo?.provincia || '').toString().trim();
            const cant = (empleo?.canton || '').toString().trim();
            const ubic = (prov || cant) ? `${prov}${prov && cant ? '; ' : ''}${cant}` : 'Sin ubicaciÃ³n';
            document.getElementById('postularUbicacion').innerHTML = `<i class="fi-rs-marker me-1"></i>${ubic}`;

            // Empresa (opcional)
            if (empleo?.empresa) {
                document.getElementById('postularEmpresa').innerHTML = `<i class="fi-rs-building me-1"></i>${empleo.empresa}`;
            } else {
                document.getElementById('postularEmpresa').innerHTML = '';
            }

            // ImÃ¡genes para el carrusel (frontal/posterior)
            const imgs = [];
            if (empleo?.img_frontal) imgs.push(`../admin/${empleo.img_frontal}`);
            if (empleo?.img_posterior && empleo.img_posterior !== empleo.img_frontal) imgs.push(`../admin/${empleo.img_posterior}`);

            buildCarousel(imgs);
        });

        // opcional: al cerrar, pausa carrusel para ahorrar recursos
        modalPostularEl.addEventListener('hidden.bs.modal', function() {
            const el = document.getElementById('carouselEmpleo');
            const instance = bootstrap.Carousel.getInstance(el);
            if (instance) instance.pause();
        });
    }

    // Escuchar cambios en PROVINCIA
    $('#selectProvincia').on('change', function() {
        const codProv = this.value;

        if (!codProv) {
            // Sin provincia => limpiar todo
            provinciaSel = {
                id: null,
                nombre: null
            };
            cantonSel = {
                id: null,
                nombre: null
            };
            resetSelectCanton();
        } else {
            provinciaSel.id = codProv;
            provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);

            // Cargar cantones de esa provincia
            resetSelectCanton();
            cargarCantones(codProv);
        }

        actualizarUIUbicacionPersistir();
        filtrarYMostrar(1); // filtra al instante
    });

    // Escuchar cambios en CANTÃ“N
    $('#selectCanton').on('change', function() {
        const codCanton = this.value;

        if (!codCanton) {
            cantonSel = {
                id: null,
                nombre: null
            };
        } else if (provinciaSel.id) {
            cantonSel.id = codCanton;
            cantonSel.nombre = capitalizarPrimeraLetra(
                (datosEcuador[provinciaSel.id].cantones[codCanton] || {}).canton || ''
            );
        }

        actualizarUIUbicacionPersistir();
        filtrarYMostrar(1); // filtra al instante
    });


    // --- cargarProvincias ---
    function cargarProvincias() {
        const selectProvincia = document.getElementById("selectProvincia");
        selectProvincia.innerHTML = '<option value="">Seleccione una provincia</option>';

        Object.entries(datosEcuador).forEach(([codProv, objProv]) => {
            const option = document.createElement("option");
            option.value = codProv;
            option.textContent = capitalizarPrimeraLetra(objProv.provincia);
            selectProvincia.appendChild(option);
        });

        // Al cambiar provincia:
        selectProvincia.addEventListener("change", (e) => {
            const codProv = e.target.value || null;

            if (!codProv) {
                provinciaSel = {
                    id: null,
                    nombre: null
                };
                resetSelectCanton();
                actualizarUIUbicacionPersistir();
                filtrarYMostrar(1);
                return;
            }

            provinciaSel.id = codProv;
            provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);

            resetSelectCanton();
            cargarCantones(codProv);
            actualizarUIUbicacionPersistir();
            filtrarYMostrar(1);
        });
    }

    // --- cargarCantones ---
    function cargarCantones(codProvincia) {
        const selectCanton = document.getElementById("selectCanton");
        selectCanton.innerHTML = '<option value="">Seleccione un cantÃ³n</option>';

        if (!codProvincia || !datosEcuador[codProvincia]) {
            resetSelectCanton();
            return;
        }

        const cantones = datosEcuador[codProvincia].cantones || {};
        Object.entries(cantones).forEach(([codCanton, objCanton]) => {
            const option = document.createElement("option");
            option.value = codCanton;
            option.textContent = capitalizarPrimeraLetra(objCanton.canton);
            selectCanton.appendChild(option);
        });

        // Al cambiar cantÃ³n:
        selectCanton.addEventListener("change", (e) => {
            const codCanton = e.target.value || null;

            if (!codCanton) {
                cantonSel = {
                    id: null,
                    nombre: null
                };
                actualizarUIUbicacionPersistir();
                filtrarYMostrar(1);
                return;
            }

            const nombre = capitalizarPrimeraLetra(
                (datosEcuador[codProvincia].cantones[codCanton] || {}).canton || ""
            );
            cantonSel.id = codCanton;
            cantonSel.nombre = nombre;

            actualizarUIUbicacionPersistir();
            filtrarYMostrar(1);
        });
    }

    // --- Guardar ubicaciÃ³n (botÃ³n "Listo") ---
    document.getElementById('guardarUbicacion').addEventListener('click', function() {
        const selProv = document.getElementById('selectProvincia').value || '';
        const selCant = document.getElementById('selectCanton').value || '';

        // Si el usuario abriÃ³ el modal pero no disparÃ³ "change", aseguramos estado:
        if (selProv) {
            provinciaSel.id = selProv;
            provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[selProv].provincia);
        } else {
            provinciaSel = {
                id: null,
                nombre: null
            };
        }

        if (selCant && selProv) {
            cantonSel.id = selCant;
            cantonSel.nombre = capitalizarPrimeraLetra(
                (datosEcuador[selProv].cantones[selCant] || {}).canton || ""
            );
        } else {
            cantonSel = {
                id: null,
                nombre: null
            };
        }

        actualizarUIUbicacionPersistir();
        filtrarYMostrar(1); // <--- re-filtra por provincia/cantÃ³n al guardar

        // Cerrar modal
        const modalEl = document.getElementById('modalUbicacion');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    });

    // --- Limpiar ubicaciÃ³n ---
    document.getElementById('limpiarUbicacion').addEventListener('click', function() {
        document.getElementById('selectProvincia').value = '';
        resetSelectCanton();
        provinciaSel = {
            id: null,
            nombre: null
        };
        cantonSel = {
            id: null,
            nombre: null
        };

        actualizarUIUbicacionPersistir();
        filtrarYMostrar(1);
    });

    function actualizarUIUbicacionPersistir() {
        const btn = document.getElementById('btnUbicacion');
        if (btn) btn.innerHTML = `<i class="fi-rs-marker me-1"></i> ${labelUbicacion()}`;

        // (opcional) persistir
        localStorage.setItem('ubicacionSeleccionada', JSON.stringify({
            provincia: provinciaSel,
            canton: cantonSel
        }));
    }

    function resetSelectCanton() {
        const selectCanton = document.getElementById("selectCanton");
        selectCanton.innerHTML = '<option value="">Seleccione un cantÃ³n</option>';
        cantonSel = {
            id: null,
            nombre: null
        };
    }

    function labelUbicacion() {
        if (provinciaSel.nombre && cantonSel.nombre)
            return `PROVINCIA: ${provinciaSel.nombre}; CANTÃ“N: ${cantonSel.nombre}`;
        if (provinciaSel.nombre)
            return `PROVINCIA: ${provinciaSel.nombre}`;
        return 'Cambiar ubicaciÃ³n';
    }



    function filtrarActual() {
        const q = norm(document.querySelector('.widget_search input[type="text"]')?.value || '');
        let data = todosLosEventos.slice();
        data = data.filter(isEmpleoVigente);


        if (q) {
            data = data.filter(e => {
                const titulo = norm(e.titulo);
                const provincia = norm(e.provincia);
                const canton = norm(e.canton);

                // Busca coincidencias en tÃ­tulo, provincia o cantÃ³n
                return titulo.includes(q) || provincia.includes(q) || canton.includes(q);
            });
        }

        if (provinciaSel.nombre)
            data = data.filter(e => norm(e.provincia) === norm(provinciaSel.nombre));
        if (cantonSel.nombre)
            data = data.filter(e => norm(e.canton) === norm(cantonSel.nombre));

        if (stateFiltros.modalidad.size) {
            data = data.filter(e => stateFiltros.modalidad.has((e.modalidad || '').toString().trim()));
        }
        if (stateFiltros.tipoEntrada.size) {
            data = data.filter(e => stateFiltros.tipoEntrada.has((e.tipo_entrada || '').toString().trim()));
        }
        if (stateFiltros.subtipos.size) {
            data = data.filter(e => {
                let ids = [];
                if (Array.isArray(e.subtipos) && e.subtipos.length) {
                    ids = e.subtipos.map(s => String(s.id));
                } else if (e.subtipo_evento) {
                    let raw = String(e.subtipo_evento).replaceAll('\\', '');
                    try {
                        const arr = JSON.parse(raw);
                        if (Array.isArray(arr))
                            ids = arr.map(x => String(Number(String(x).replace(/\D+/g, ''))));
                    } catch (_) {}
                }
                return ids.some(id => stateFiltros.subtipos.has(String(id)));
            });
        }

        return data;
    }

    function mostrarEventosPagina(pagina = 1) {
        const inicio = (pagina - 1) * eventosPorPagina;
        const fin = inicio + eventosPorPagina;
        const eventosPagina = filtradosCache.slice(inicio, fin);

        const cont = $("#listaEmpleos");
        cont.empty();

        if (!eventosPagina.length) {
            cont.html('<div class="col-12 text-center py-5"><div class="jobs-empty-state"><img src="../img/no-results.png" style="width:150px; opacity:0.5;"><p class="mt-3 text-muted mb-0">No encontramos vacantes vigentes en este momento.</p></div></div>');
            return;
        }

        eventosPagina.forEach(ev => {
            const imgFront = ev.img_frontal ? `../admin/${ev.img_frontal}` : '../img/placeholder-job.png';
            const imgBack = ev.img_posterior ? `../admin/${ev.img_posterior}` : imgFront;
            const ubicacion = `${ev.provincia || ''} ${ev.canton ? ' - ' + ev.canton : ''}`;

            // Formatear fecha de cierre para el usuario
            const fechaCierre = ev.fecha_fin ? new Date(ev.fecha_fin).toLocaleDateString() : 'Indefinida';

            cont.append(`
            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                <div class="card card-empleo h-100">
                    <div class="position-relative">
                        <img src="${imgFront}" class="card-img-top empleo-img" 
                             data-front="${imgFront}" data-back="${imgBack}" 
                             style="object-fit: cover;">
                        <span class="position-absolute top-0 end-0 m-3 badge bg-primary">Nuevo</span>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge-location"><i class="fi-rs-marker mr-5"></i>${ubicacion}</span>
                        </div>
                        
                        <h5 class="card-title mb-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalPostular" data-id="${ev.id_empleo}" data-titulo="${ev.titulo.replace(/"/g, '&quot;')}">
                                ${ev.titulo}
                            </a>
                        </h5>

                        <p class="card-text text-muted small mb-3">
                            ${ev.descripcion ? ev.descripcion.replace(/<[^>]+>/g, '').substring(0, 100) + '...' : 'Sin descripciÃ³n disponible.'}
                        </p>

                        <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                            <div class="text-danger small font-weight-bold">
                                <i class="fi-rs-calendar-check mr-5"></i> Cierra: ${fechaCierre}
                            </div>
                            <button class="btn btn-primary btn-sm btn-postular" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalPostular" 
                                    data-id="${ev.id_empleo}" 
                                    data-id_empresa="${ev.id_empresa}" 
                                    data-titulo="${ev.titulo.replace(/"/g, '&quot;')}">
                                Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        });
    }


    // Cambiar a img_posterior al pasar el cursor y volver a img_frontal al salir
    $(document).on('mouseenter', '.empleo-img', function() {
        const back = $(this).data('back');
        if (back) {
            $(this).attr('src', back);
        }
    });

    $(document).on('mouseleave', '.empleo-img', function() {
        const front = $(this).data('front');
        if (front) {
            $(this).attr('src', front);
        }
    });

    function generarPaginacion(totalPaginas, paginaActual) {
        const pag = $("#paginacionEventos");
        pag.html("");

        const prevDisabled = (paginaActual === 1) ? "disabled" : "";
        pag.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${paginaActual-1}">
                    <i class="fi-rs-arrow-small-left"></i>
                </a>
            </li>`);

        for (let i = 1; i <= totalPaginas; i++) {
            pag.append(`
                <li class="page-item ${i===paginaActual?'active':''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`);
        }

        const nextDisabled = (paginaActual === totalPaginas) ? "disabled" : "";
        pag.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${paginaActual+1}">
                    <i class="fi-rs-arrow-small-right"></i>
                </a>
            </li>`);
    }

    $(document).on("click", "#paginacionEventos .page-link", function(e) {
        e.preventDefault();
        const page = parseInt($(this).data("page"), 10);
        const total = Math.max(1, Math.ceil(filtradosCache.length / eventosPorPagina));
        if (page >= 1 && page <= total) {
            mostrarEventosPagina(page);
            generarPaginacion(total, page);
        }
    });

    /* Filtrar + render maestro */
    function filtrarYMostrar(pagina = 1) {
        filtradosCache = filtrarActual();
        $("#totalProductosGeneral").text(filtradosCache.length);
        mostrarEventosPagina(pagina);
        generarPaginacion(Math.max(1, Math.ceil(filtradosCache.length / eventosPorPagina)), pagina);
    }

    /* Buscar por tÃ­tulo (input) */
    const searchInput = document.querySelector('.widget_search input[type="text" ]');
    if (searchInput) {
        searchInput.addEventListener('input', () => filtrarYMostrar(1));
        searchInput.closest('form')?.addEventListener('submit', e => e.preventDefault());
    }

    /* â€œShowâ€ (cantidad por pÃ¡gina) */
    $('.sort-show a').on('click', function(e) {
        e.preventDefault();
        const val = $(this).data('value');
        if (val === 'all') eventosPorPagina = Number.MAX_SAFE_INTEGER;
        else eventosPorPagina = parseInt(val, 10) || 6;
        filtrarYMostrar(1);
    });

    /* ======= CARGA INICIAL DE EVENTOS ======= */
    $.get('../api/v1/fulmuv/empleosAll/all', function(ret) {
        if (!ret.error && Array.isArray(ret.data)) {
            todosLosEventos = ret.data;

            // 1) Construir filtros con el dataset completo
            buildFiltros(todosLosEventos);

            // 2) Primer render de la grilla + paginaciÃ³n
            filtrarYMostrar(1);
        } else {
            $(".loop-grid").html('<p class="text-danger">No se encontraron empleos disponibles.</p>');
        }
    }, 'json');

    // Convierte a TÃ­tulo: Cada palabra con primera mayÃºscula
    function toTitleCaseWords(str) {
        return (str ?? '')
            .toLowerCase()
            .split(' ')
            .filter(Boolean)
            .map(w => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ');
    }

    const inputNombres = document.getElementById('nombres_apellidos');

    if (inputNombres) {
        inputNombres.addEventListener('input', function() {
            let valor = this.value;

            // Solo letras, espacios y tildes/Ã±
            valor = valor.replace(/[^A-Za-zÃÃ‰ÃÃ“ÃšÃ¡Ã©Ã­Ã³ÃºÃ‘Ã±\s]/g, '');

            // Quitar espacios duplicados
            valor = valor.replace(/\s{2,}/g, ' ');

            // âœ… NO trim aquÃ­ (para permitir el espacio mientras escribe)
            this.value = valor;
        });


    }

    ['cedula', 'telefono'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function() {
            // Eliminar todo lo que no sea dÃ­gito
            this.value = this.value.replace(/\D/g, '');
        });
    });


    function validarCorreoElectronico(correo) {
        const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regexCorreo.test(correo);
    }

    function precargarDatosClientePostulacion() {
        if (!idUsuarioActual) return;

        $.post("../api/v1/fulmuv/cliente/getClienteById", {
            id_usuario: idUsuarioActual
        }, function(response) {
            const res = (typeof response === "string") ? JSON.parse(response) : response;

            if (res?.error || !res?.data) return;

            const nombreCompleto = [
                res.data.nombres || "",
                res.data.apellidos || ""
            ].join(" ").replace(/\s+/g, " ").trim();

            $("#nombres_apellidos").val(nombreCompleto || res.data.nombres || "");
            $("#cedula").val(res.data.cedula || "");
            $("#correo").val(res.data.correo || "");
            $("#telefono").val(res.data.telefono || "");
        }, "json");
    }

    async function seleccionarCvDesdeDispositivo() {
        const files = await seleccionarArchivosApp({
            accept: ".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            multiple: false
        });

        if (!files.length) return;

        const input = document.getElementById("cv_pdf");
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;
        input.dispatchEvent(new Event("change"));
    }

    document.getElementById("btnSeleccionarCv")?.addEventListener("click", function() {
        seleccionarCvDesdeDispositivo();
    });

    $(document).on("click", '[data-bs-target="#modalPostular"]', function(e) {
        if (!sinCuentaApp) return;
        e.preventDefault();
        e.stopPropagation();
        mostrarAvisoLoginEmpleos();
    });

    precargarDatosClientePostulacion();

    function parseFechaLocalYYYYMMDD(s) {
        if (!s) return null;
        // acepta "YYYY-MM-DD" o "YYYY-MM-DD HH:MM:SS"
        const d = String(s).trim().slice(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(d)) return null;
        // local time (Ecuador)
        const [y, m, day] = d.split('-').map(Number);
        return new Date(y, m - 1, day, 0, 0, 0);
    }

    function isEmpleoVigente(ev) {
        const hoy = new Date();
        // Normalizamos hoy a las 00:00:00 para comparar solo fechas
        hoy.setHours(0, 0, 0, 0);

        // Si no tiene fecha de fin, asumimos que estÃ¡ activo
        if (!ev.fecha_fin) return true;

        // Convertir fechas del JSON (YYYY-MM-DD)
        const [yF, mF, dF] = ev.fecha_fin.split('-').map(Number);
        const fechaFin = new Date(yF, mF - 1, dF, 23, 59, 59);

        let fechaInicio = null;
        if (ev.fecha_inicio) {
            const [yI, mI, dI] = ev.fecha_inicio.split('-').map(Number);
            fechaInicio = new Date(yI, mI - 1, dI, 0, 0, 0);
        }

        // Regla: hoy <= fechaFin Y (si existe inicio) hoy >= fechaInicio
        const dentroDeFin = hoy <= fechaFin;
        const yaEmpezo = fechaInicio ? hoy >= fechaInicio : true;

        return dentroDeFin && yaEmpezo;
    }
</script>

