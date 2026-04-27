<?php
include 'includes/header.php';

$id_evento = $_GET["q"];
$sinCuentaMode = defined('APP_SIN_CUENTA') && APP_SIN_CUENTA;
echo '<input type="hidden" class="form-control" value="' . $id_evento . '" id="id_evento">';

?>
<link rel="canonical" href="https://fulmuv.com/detalle_eventos.php?q=<?php echo $id_evento; ?>">
<style>
    .event-shell {
        padding: 28px 0 44px;
    }

    .event-hero-card,
    .event-panel,
    .event-side-card {
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
    }

    .event-hero-card {
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(14, 165, 233, .08), transparent 32%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .event-cover {
        width: 100%;
        height: 520px;
        object-fit: contain;
        display: block;
        background: #eef2f7;
    }

    .event-hero-body {
        padding: 1.5rem;
    }

    .event-kicker {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: .85rem;
    }

    .event-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .38rem .72rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1;
    }

    .event-chip-primary {
        background: rgba(2, 132, 199, .12);
        color: #075985;
    }

    .event-chip-soft {
        background: #f1f5f9;
        color: #475569;
    }

    .event-title {
        font-size: clamp(1.8rem, 3vw, 2.7rem);
        line-height: 1.1;
        color: #0f172a;
        margin: 0 0 .75rem;
    }

    .event-submeta {
        display: flex;
        flex-wrap: wrap;
        gap: .8rem 1rem;
        color: #475569;
        font-size: .92rem;
        margin-bottom: .95rem;
    }

    .event-submeta a {
        color: #0f172a;
        font-weight: 700;
    }

    .event-countdown {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 999px;
        padding: .48rem .82rem;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: .85rem;
    }

    .event-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(300px, .9fr);
        gap: 1.35rem;
        align-items: start;
        margin-top: 1.35rem;
    }

    .event-panel {
        padding: 1.35rem;
    }

    .event-panel-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1rem;
    }

    .event-description {
        white-space: pre-line;
        color: #334155;
        line-height: 1.8;
        font-size: .97rem;
        margin: 0;
    }

    .event-side-card {
        padding: 1.2rem;
        position: sticky;
        top: 110px;
    }

    .event-side-section + .event-side-section {
        margin-top: 1.1rem;
        padding-top: 1.1rem;
        border-top: 1px solid rgba(148, 163, 184, .22);
    }

    .event-side-title {
        font-size: .92rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .7rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .event-detail-list {
        display: grid;
        gap: .72rem;
    }

    .event-detail-item {
        display: grid;
        gap: .22rem;
    }

    .event-detail-label {
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
    }

    .event-detail-value {
        color: #0f172a;
        font-weight: 600;
        line-height: 1.45;
    }

    .event-badge-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }

    .event-outline-badge {
        display: inline-flex;
        align-items: center;
        border: 1px solid #cbd5e1;
        color: #334155;
        background: #fff;
        border-radius: 999px;
        padding: .35rem .62rem;
        font-size: .78rem;
        font-weight: 700;
    }

    .event-action-list {
        display: grid;
        gap: .65rem;
    }

    .guest-session-note {
        padding: 16px 18px;
        border: 1px solid rgba(2, 132, 199, .14);
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(2, 132, 199, .08) 0%, rgba(14, 165, 233, .08) 100%);
    }

    .guest-session-note h6 {
        margin: 0 0 6px;
        font-size: .95rem;
        font-weight: 800;
        color: #0f172a;
    }

    .guest-session-note p {
        margin: 0;
        color: #475569;
        line-height: 1.6;
        font-size: .9rem;
    }

    .event-empty {
        color: #94a3b8;
        font-size: .88rem;
    }

    /* ====== Galería en GRID (estilo cards con caption) ====== */
    .gallery-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(3, 1fr);
    }

    @media (max-width: 1200px) {
        .gallery-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .gallery-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .gallery-grid {
            grid-template-columns: 1fr;
        }
    }

    .g-item {
        border-radius: 12px;
        overflow: hidden;
        background: #f4f4f4;
        box-shadow: 0 10px 22px rgba(0, 0, 0, .08);
        transition: transform .2s ease, box-shadow .2s ease;
        cursor: pointer;
    }

    .g-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px rgba(0, 0, 0, .12);
    }

    .g-thumb {
        position: relative;
        width: 100%;
        /* relación similar a tu referencia (4:3) */
        aspect-ratio: 4 / 3;
        overflow: hidden;
    }

    .g-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .4s ease;
    }

    .g-item:hover .g-thumb img {
        transform: scale(1.03);
    }

    /* ===== Lightbox (se mantiene) ===== */
    .glightbox {
        position: fixed;
        inset: 0;
        z-index: 1055;
        background: rgba(0, 0, 0, .85);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .glightbox img {
        max-width: 96vw;
        max-height: 88vh;
        border-radius: 10px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .45);
    }

    .glightbox-close {
        position: absolute;
        top: 14px;
        right: 18px;
        font-size: 34px;
        line-height: 1;
        color: #fff;
        background: transparent;
        border: 0;
        cursor: pointer;
    }

    /* ===========================
   MOBILE: Ajustes visuales
   =========================== */
    @media (max-width: 576px) {
        .event-shell {
            padding-top: 18px !important;
        }

        .event-cover {
            height: 320px !important;
        }

        .event-hero-body,
        .event-panel,
        .event-side-card {
            padding: 1rem !important;
        }

        .event-title {
            font-size: 1.55rem !important;
        }

        .event-submeta {
            font-size: .84rem !important;
            gap: .55rem .75rem !important;
        }

        .gallery-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px !important;
        }

        .g-thumb {
            aspect-ratio: 1 / 1 !important;
            /* cuadradas en móvil */
        }

        #contacto-telefonos .btn {
            padding: 6px 10px !important;
            font-size: 12px !important;
        }

        #contacto-telefonos .badge {
            font-size: 12px !important;
            padding: 7px 10px !important;
        }
        #map-evento {
            height: 260px !important;
            border-radius: 12px !important;
        }

        .glightbox {
            padding: 12px !important;
        }

        .glightbox img {
            max-width: 96vw !important;
            max-height: 82vh !important;
        }

    }

    @media (max-width: 991px) {
        .event-grid {
            grid-template-columns: 1fr;
        }

        .event-side-card {
            position: static;
            top: auto;
        }
    }
</style>

<div class="container event-shell">
    <div class="event-hero-card">
        <img id="hero_imagen" class="event-cover" src="img/FULMUV-NEGRO.png" alt="Portada del evento">
        <div class="event-hero-body">
            <div class="event-kicker">
                <span class="event-chip event-chip-primary" id="hero_tipo">Evento</span>
                <span class="event-chip event-chip-soft" id="hero_modalidad" style="display:none;"></span>
                <span class="event-chip event-chip-soft" id="hero_tipo_entrada" style="display:none;"></span>
            </div>
            <h1 class="event-title" id="titulo_evento">Evento</h1>
            <div class="event-submeta">
                <span>Organiza: <a href="#" id="empresa"></a></span>
                <span id="hero_inicio">Inicio: —</span>
                <span id="hero_fin">Fin: —</span>
            </div>
            <div class="event-countdown" id="countdown_evento">Cargando...</div>
        </div>
    </div>

    <div class="event-grid">
        <div>
            <div class="event-panel mb-4">
                <div class="event-panel-title">Descripción</div>
                <p class="event-description" id="descripcion_evento">—</p>
            </div>

            <div class="event-panel mb-4">
                <div class="event-panel-title">Galería</div>
                <div id="galeria-evento" class="gallery-grid"></div>
            </div>

            <div id="box-contacto" class="event-panel mb-4" style="display:none;">
                <div class="event-panel-title">Contacto</div>
                <div class="mb-2">
                    <strong>Nombre:</strong> <span id="contacto-nombre">—</span>
                </div>
                <div class="mb-2">
                    <strong>Teléfonos:</strong>
                    <div id="contacto-telefonos" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>
                <div class="mb-2" id="wrap-contacto-email" style="display:none;">
                    <strong>Correo:</strong>
                    <a href="#" id="contacto-email" class="text-decoration-none"></a>
                </div>
            </div>

            <div class="event-panel" id="box-ubicacion" style="display:none;">
                <div class="event-panel-title">Ubicación</div>
                <div id="map-wrap" style="display:none;">
                    <div id="map-evento" style="width:100%; height:360px; border-radius:16px; overflow:hidden;"></div>
                </div>
                <div class="mt-3" id="wrap-provincia-canton" style="display:none;">
                    <h6 class="mb-1">Provincia y cantón</h6>
                    <div class="text-muted" style="line-height:1.6;">
                        <span id="txt-provincia"></span>
                        <span id="txt-canton"></span>
                    </div>
                </div>
                <div class="mt-3" id="wrap-direccion" style="display:none;">
                    <h6 class="mb-1">Dirección</h6>
                    <div id="txt-direccion" class="text-muted" style="line-height:1.6;"></div>
                </div>
            </div>
        </div>

        <aside class="event-side-card">
            <div class="event-side-section">
                <div class="event-side-title">Resumen</div>
                <div class="event-detail-list">
                    <div class="event-detail-item">
                        <div class="event-detail-label">Subtipo de evento</div>
                        <div id="subtipos_evento" class="event-badge-wrap">
                            <span class="event-empty">No especificado</span>
                        </div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Modalidad</div>
                        <div class="event-detail-value" id="modalidad_evento">No especificada</div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Tipo de entrada</div>
                        <div class="event-detail-value" id="tipo_entrada_evento">No especificado</div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Precio / secciones</div>
                        <div class="event-detail-value" id="precio_secciones_evento">No especificado</div>
                    </div>
                </div>
            </div>

            <div class="event-side-section">
                <div class="event-side-title">Acciones</div>
                <div id="acciones_evento" class="event-action-list">
                    <span class="event-empty">No hay enlaces disponibles para este evento.</span>
                </div>
            </div>

            <?php if ($sinCuentaMode): ?>
                <div class="event-side-section">
                    <div class="guest-session-note">
                        <h6>Inicia sesion para una mejor experiencia</h6>
                        <p>Inicia sesion para gestionar mejor tus registros, seguimiento y demas acciones dentro de FULMUV.</p>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>

    <div id="gallery-lightbox" class="glightbox" style="display:none;">
        <button type="button" class="glightbox-close" aria-label="Cerrar">&times;</button>
        <img id="glightbox-img" src="" alt="Imagen del evento" />
    </div>
</div>

<?php
include 'includes/footer.php';
?>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places" async defer></script>

<script src="js/eventos.js"></script>
<script>
    window.APP_MODE_CONFIG = Object.assign({}, window.APP_MODE_CONFIG || {}, {
        sinCuenta: <?= $sinCuentaMode ? 'true' : 'false' ?>
    });

    const id_evento = $("#id_evento").val();


    // --- Helpers robustos en LOCAL ---
    function parseLocalDateTime(str) {
        // str: "YYYY-MM-DD HH:mm:ss"
        if (!str) return null;
        const [d, t = "00:00:00"] = str.trim().split(" ");
        const [Y, M, D] = d.split("-").map(n => parseInt(n, 10));
        const [h, m, s] = t.split(":").map(n => parseInt(n, 10));
        return new Date(Y, (M - 1), D, h || 0, m || 0, s || 0, 0); // local time
    }

    const p = (n, uno, muchos) => (n === 1 ? uno : muchos);

    // Diferencia "ahora" -> target (usa fecha_hora_fin)
    function countdownDiasHorasMinHastaFin(finStr) {
        const target = parseLocalDateTime(finStr);
        if (!target) return {
            texto: "",
            started: false
        };

        const now = new Date();
        let ms = target.getTime() - now.getTime();

        if (ms <= 0) return {
            texto: "El evento ya finalizó",
            started: true
        };

        const MIN = 60 * 1000;
        const HORA = 60 * MIN;
        const DIA = 24 * HORA;

        const dias = Math.floor(ms / DIA);
        ms -= dias * DIA;
        const horas = Math.floor(ms / HORA);
        ms -= horas * HORA;
        const minutos = Math.floor(ms / MIN);

        const partes = [];
        if (dias > 0) partes.push(`${dias} ${p(dias, "día", "días")}`);
        if (horas > 0) partes.push(`${horas} ${p(horas, "hora", "horas")}`);
        // muestra minutos incluso si 0 cuando no hay días ni horas
        if (minutos > 0 || (dias === 0 && horas === 0)) {
            partes.push(`${minutos} ${p(minutos, "minuto", "minutos")}`);
        }

        const verbo = (dias + horas + minutos) === 1 ? "Falta" : "Faltan";
        return {
            texto: `${verbo} ${partes.join(" y ")} para que comience el evento`,
            started: false
        };
    }

    // Duración entre inicio y fin en H y M (también en local)
    function duracionHorasMin(inicioStr, finStr) {
        const s = parseLocalDateTime(inicioStr);
        const e = parseLocalDateTime(finStr);
        if (!s || !e) return "—";
        let ms = Math.max(0, e.getTime() - s.getTime());

        const MIN = 60 * 1000;
        const HORA = 60 * MIN;

        const horas = Math.floor(ms / HORA);
        ms -= horas * HORA;
        const minutos = Math.floor(ms / MIN);

        if (horas > 0 && minutos > 0) return `${horas} ${p(horas,"hora","horas")} y ${minutos} ${p(minutos,"minuto","minutos")}`;
        if (horas > 0) return `${horas} ${p(horas,"hora","horas")}`;
        return `${minutos} ${p(minutos,"minuto","minutos")}`;
    }

    function formatDateTimeLocal(str) {
        const d = parseLocalDateTime(str);
        if (!d) return "—";
        return d.toLocaleString("es-EC", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        });
    }

    function safeText(value, fallback = "No especificado") {
        const txt = (value ?? "").toString().trim();
        return txt || fallback;
    }

    function safeUrl(value) {
        const raw = (value ?? "").toString().trim();
        if (!raw) return "";
        if (/^https?:\/\//i.test(raw)) return raw;
        return `https://${raw}`;
    }

    function abrirEnlaceExterno(url) {
        const cleanUrl = (url || "").toString().trim();
        if (!cleanUrl) return false;

        if (typeof puedeUsarFlutterBridge === "function" && puedeUsarFlutterBridge()) {
            window.flutter_inappwebview.callHandler('openExternalUrl', {
                url: cleanUrl
            });
            return false;
        }

        window.open(cleanUrl, "_blank", "noopener,noreferrer");
        return false;
    }

    function parseJsonish(value) {
        if (Array.isArray(value)) return value;
        if (!value) return [];
        if (typeof value === "object") return [value];

        const raw = String(value).trim();
        if (!raw) return [];

        const candidates = [];
        const pushCandidate = (candidate) => {
            const text = String(candidate || "").trim();
            if (text && !candidates.includes(text)) candidates.push(text);
        };

        pushCandidate(raw);
        pushCandidate(raw.replace(/\|/g, ","));
        pushCandidate(raw.replace(/\\"/g, '"'));
        pushCandidate(raw.replace(/\\"/g, '"').replace(/\|/g, ","));
        pushCandidate(raw.replace(/\\+/g, ""));
        pushCandidate(raw.replace(/\\+/g, "").replace(/\|/g, ","));

        for (const candidate of candidates) {
            try {
                const parsed = JSON.parse(candidate);
                if (typeof parsed === "string" && parsed.trim() !== candidate.trim()) {
                    return parseJsonish(parsed);
                }
                return Array.isArray(parsed) ? parsed : [parsed];
            } catch (_) {}
        }

        return raw.split(/[,;\n]+/).map(v => v.trim()).filter(Boolean);
    }

    function formatPrecioSecciones(value) {
        const entries = parseJsonish(value);
        if (!entries.length) return "No especificado";

        const texts = entries.map((item) => {
            if (typeof item === "string" || typeof item === "number") return String(item);
            if (item && typeof item === "object") {
                const tipo = item.tipo || item.nombre || item.seccion || item.titulo || item.label || "";
                const montoRaw = item.monto ?? item.precio ?? item.valor ?? null;
                let monto = "";

                if (montoRaw === null || montoRaw === undefined || String(montoRaw).trim() === "") {
                    monto = "$0";
                } else {
                    const montoTxt = String(montoRaw).trim();
                    monto = /^\$/.test(montoTxt) ? montoTxt : `$${montoTxt}`;
                }

                if (tipo && monto) return `${tipo}: ${monto}`;
                if (tipo) return tipo;
                return monto || "";
            }
            return "";
        }).filter(Boolean);

        return texts.length ? texts.join(" | ") : "No especificado";
    }

    function renderSubtipos(subtipos) {
        const wrap = document.getElementById("subtipos_evento");
        if (!wrap) return;

        const list = Array.isArray(subtipos) ? subtipos : [];
        if (!list.length) {
            wrap.innerHTML = '<span class="event-empty">No especificado</span>';
            return;
        }

        wrap.innerHTML = list.map(item => {
            const nombre = item?.nombre || item?.label || item?.titulo || "";
            return `<span class="event-outline-badge">${nombre}</span>`;
        }).join("");
    }

    function renderAcciones(ev) {
        const wrap = document.getElementById("acciones_evento");
        if (!wrap) return;

        const enlace = safeUrl(ev.enlace);
        const compra = safeUrl(ev.enlace_compra);
        const actions = [];

        if (enlace) {
            const enlaceSafe = enlace.replace(/'/g, "\\'");
            actions.push(`
                <button type="button" class="btn btn-primary w-100" onclick="return abrirEnlaceExterno('${enlaceSafe}')">
                    Dar click en el enlace
                </button>
            `);
        }

        if (compra) {
            const compraSafe = compra.replace(/'/g, "\\'");
            actions.push(`
                <button type="button" class="btn btn-outline-primary w-100" onclick="return abrirEnlaceExterno('${compraSafe}')">
                    Enlace compra
                </button>
            `);
        }

        wrap.innerHTML = actions.length
            ? actions.join("")
            : '<span class="event-empty">No hay enlaces disponibles para este evento.</span>';
    }


    // --- Carga evento ---
    $.post(`../api/v1/fulmuv/eventos/ById`, {
            id_evento
        }, function(res) {
            const ev = res?.data?.[0] || res?.data || res;
            if (!ev || res.error) {
                $("#titulo_evento").text("Evento no disponible");
                return;
            }

            // Título
            $("#titulo_evento").text(ev.titulo || "Evento");
            renderGaleria(ev.galeria);
            renderContacto(ev);
            renderUbicacion(ev);

            // Empresa con link
            const nombreOrg = ev.organizador || ev.nombre_contacto || "Organizador";
            $("#empresa").text(nombreOrg)
                .attr("href", `productos_vendor.php?q=${encodeURIComponent(ev.id_empresa)}`);

            $("#breadcrumb")?.append(`<a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a><span></span> Detalle del evento - ${nombreOrg}`);


            // ✅ Cuenta regresiva hasta la FECHA FIN (local, sin desfases)
            const cd = countdownDiasHorasMinHastaFin(ev.fecha_hora_fin);
            $("#countdown_evento").text(cd.texto || "Consulta los detalles del evento");

            // ✅ Duración H y M entre inicio y fin (local)
            $("#hero_inicio").text(`Inicio: ${formatDateTimeLocal(ev.fecha_hora_inicio)}`);
            $("#hero_fin").text(`Fin: ${formatDateTimeLocal(ev.fecha_hora_fin)}`);
            $("#hero_tipo").text(safeText(ev.tipo || ev.tipo_evento, "Evento"));

            const modalidad = safeText(ev.modalidad, "");
            if (modalidad) {
                $("#hero_modalidad").text(modalidad).show();
            } else {
                $("#hero_modalidad").hide().text("");
            }

            const tipoEntrada = safeText(ev.tipo_entrada, "");
            if (tipoEntrada) {
                $("#hero_tipo_entrada").text(tipoEntrada).show();
            } else {
                $("#hero_tipo_entrada").hide().text("");
            }

            // Imágenes
            const hero = "../"+ev.portada_evento ? `../admin/${ev.portada_evento}` :
                (ev.imagen ? `../admin/${ev.imagen}` : '../img/FULMUV-NEGRO.png');
            $("#hero_imagen")
                .attr("src", hero)
                .attr("alt", ev.titulo || "Evento")
                .on("error", function() {
                    this.src = '../img/FULMUV-NEGRO.png';
                });

            // Descripción
            $("#descripcion_evento").text(ev.descripcion || "");
            $("#modalidad_evento").text(safeText(ev.modalidad));
            $("#tipo_entrada_evento").text(safeText(ev.tipo_entrada));
            $("#precio_secciones_evento").text(formatPrecioSecciones(ev.precio_secciones));

            renderSubtipos(ev.subtipos);
            renderAcciones(ev);
        }, 'json')
        .fail(() => $("#titulo_evento").text("No se pudo cargar el evento"));


    function renderGaleria(galeria) {
        const wrap = document.getElementById('galeria-evento');
        if (!wrap) return;

        // Normalizar posibles formatos de respuesta
        let gal = [];
        if (Array.isArray(galeria)) gal = galeria;
        else if (galeria && typeof galeria === 'object') gal = Object.values(galeria);

        wrap.innerHTML = '';
        if (!gal.length) {
            wrap.innerHTML = '<div class="text-muted">Este evento no tiene imágenes adicionales.</div>';
            return;
        }

        gal.forEach((g, i) => {
            const src = "../"+g?.imagen ? `../admin/${g.imagen}` : '../img/FULMUV-NEGRO.png';
            const titulo = (g?.titulo || g?.caption || `Imagen ${i+1}`).toString();

            const item = document.createElement('figure');
            item.className = 'g-item';
            item.innerHTML = `
                <div class="g-thumb">
                    <img loading="lazy" src="${src}" alt="${titulo.replace(/"/g,'&quot;')}"
                        onerror="this.src='img/FULMUV-NEGRO.png'">
                </div>
            `;
            item.addEventListener('click', () => openGalleryLightbox(src));
            wrap.appendChild(item);
        });
    }


    // ----- Lightbox -----
    const gl = {
        el: document.getElementById('gallery-lightbox'),
        img: document.getElementById('glightbox-img'),
        closeBtn: document.querySelector('#gallery-lightbox .glightbox-close')
    };

    function openGalleryLightbox(src) {
        if (!gl.el || !gl.img) return;
        gl.img.src = src;
        gl.el.style.display = 'flex';
    }

    function closeGalleryLightbox() {
        if (!gl.el) return;
        gl.el.style.display = 'none';
        if (gl.img) gl.img.src = '';
    }
    gl?.closeBtn?.addEventListener('click', closeGalleryLightbox);
    gl?.el?.addEventListener('click', (e) => {
        // cerrar si hace click fuera de la imagen
        if (e.target === gl.el) closeGalleryLightbox();
    });

    function normalizarTelefono(t) {
        return (t || '')
            .toString()
            .replace(/[^\d+]/g, '') // deja solo numeros y +
            .trim();
    }

    function partirTelefonos(raw) {
        if (!raw) return [];
        // separa por coma, punto y coma, guion, salto de línea
        const parts = raw.toString().split(/[,;\-\n\r]+/g);
        const limpios = parts
            .map(x => x.trim())
            .filter(Boolean)
            .map(normalizarTelefono)
            .filter(x => x && x.length >= 7);

        // quitar duplicados
        return [...new Set(limpios)];
    }

    function renderContacto(ev) {
        const box = document.getElementById("box-contacto");
        if (!box) return;

        const nombre = (ev.nombre_contacto || ev.contacto || "").toString().trim();
        const telefonosRaw = (ev.telefono || ev.contactoTelefonos || "").toString().trim();
        const correo = (ev.correo || ev.contactoEmail || "").toString().trim();

        const telefonos = partirTelefonos(telefonosRaw);

        // si no hay nada de contacto, no mostramos el cuadro
        if (!nombre && !telefonos.length && !correo) {
            box.style.display = "none";
            return;
        }

        // mostrar cuadro
        box.style.display = "block";

        // nombre
        $("#contacto-nombre").text(nombre || "—");

        // telefonos
        const $wrapTel = $("#contacto-telefonos").empty();

        if (telefonos.length) {
            telefonos.forEach(tel => {
                // Para Ecuador: si viene 09..., WhatsApp debe ser 5939...
                // Si ya viene con +593 lo respetamos.
                let wa = tel;
                if (wa.startsWith("09")) wa = "593" + wa.substring(1);
                if (wa.startsWith("0") && !wa.startsWith("09")) wa = "593" + wa.substring(1);
                wa = wa.replace(/^\+/, ""); // wa.me no lleva +

                const waHref = `https://wa.me/${wa}`;
                const telSafe = tel.replace(/'/g, "\\'");

                $wrapTel.append(`
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-secondary-subtle text-secondary">${tel}</span>
          <button type="button" class="btn btn-sm btn-success" onclick="return abrirWhatsAppApp('${telSafe}', '', '${waHref}')">
            WhatsApp
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="return abrirLlamadaApp('${telSafe}')">
            Llamar
          </button>
        </div>
      `);
            });
        } else {
            $wrapTel.html(`<span class="text-muted">—</span>`);
        }

        // correo (solo si existe)
        if (correo) {
            $("#wrap-contacto-email").show();
            $("#contacto-email")
                .attr("href", `mailto:${correo}`)
                .text(correo);
        } else {
            $("#wrap-contacto-email").hide();
            $("#contacto-email").attr("href", "#").text("");
        }
    }

    let __mapEvento = null;
    let __markerEvento = null;

    function waitMapsReady(cb) {
        if (window.google && window.google.maps) return cb();

        const t = setInterval(() => {
            if (window.google && window.google.maps) {
                clearInterval(t);
                cb();
            }
        }, 150);

        // seguridad: si no carga en 10s, parar
        setTimeout(() => clearInterval(t), 10000);
    }


    function toNum(x) {
        const n = parseFloat((x ?? "").toString().replace(",", "."));
        return Number.isFinite(n) ? n : null;
    }

    function renderUbicacion(ev) {
        const lat = toNum(ev.latitud);
        const lng = toNum(ev.longitud);

        const direccion = (ev.direccion || "").toString().trim();

        const prov = (ev.provincia || "").toString().trim();
        const cant = (ev.canton || "").toString().trim();
        const hasCoords = lat != null && lng != null;
        const mapExtra = {
            title: (ev.titulo || "Evento").toString(),
            address: direccion || [prov, cant].filter(Boolean).join(" · ")
        };

        if (!hasCoords && !direccion && !prov && !cant) {
            $("#box-ubicacion").hide();
            return;
        }

        $("#box-ubicacion").show();
        $("#map-wrap").toggle(hasCoords);

        // Dirección textual
        if (direccion) {
            $("#wrap-direccion").show();
            $("#txt-direccion").text(direccion);
        } else {
            $("#wrap-direccion").hide();
            $("#txt-direccion").text("");
        }

        if (prov || cant) {
            $("#wrap-provincia-canton").show();
            $("#txt-provincia").text(prov ? `Provincia: ${prov}` : "");
            $("#txt-canton").text(cant ? `${prov ? " · " : ""}Cantón: ${cant}` : "");
        } else {
            $("#wrap-provincia-canton").hide();
            $("#txt-provincia").text("");
            $("#txt-canton").text("");
        }

        // Si hay coords, pintamos el mapa
        if (hasCoords) {
            waitMapsReady(() => {
                const el = document.getElementById("map-evento");
                if (!el) return;

                const pos = {
                    lat,
                    lng
                };

                // Crear o actualizar mapa
                if (!__mapEvento) {
                    __mapEvento = new google.maps.Map(el, {
                        center: pos,
                        zoom: 16,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: false,
                        gestureHandling: "greedy"
                    });

                    el.style.cursor = "pointer";


                    __markerEvento = new google.maps.Marker({
                        map: __mapEvento,
                        position: pos
                    });

                    // ✅ abrir google maps al click en el marcador
                    __markerEvento.addListener("click", () => {
                        abrirMapaApp(lat, lng, mapExtra);
                    });

                    // ✅ abrir google maps al click en el mapa
                    __mapEvento.addListener("click", () => {
                        abrirMapaApp(lat, lng, mapExtra);
                    });

                } else {
                    __mapEvento.setCenter(pos);
                    __mapEvento.setZoom(16);
                    __markerEvento.setPosition(pos);
                }
            });
        }
    }
</script>
