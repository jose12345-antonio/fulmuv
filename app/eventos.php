<?php
include 'includes/header.php';
?>
<link rel="canonical" href="https://fulmuv.com/eventos.php">

<style>
    :root {
        --events-page-bg: #f7fafc;
        --events-surface: #ffffff;
        --events-surface-soft: #eef4f8;
        --events-border: rgba(15, 23, 42, 0.08);
        --events-text: #0f172a;
        --events-text-muted: #64748b;
        --events-accent: #004e60;
        --events-accent-2: #0f766e;
        --events-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    body,
    body .main.pages,
    body .page-content {
        background: var(--events-page-bg);
        color: var(--events-text);
    }

    .events-shell {
        padding: 12px 0 40px;
    }

    .events-hero {
        margin: 12px 0 18px;
        padding: 24px 22px;
        border: 1px solid var(--events-border);
        background:
            radial-gradient(circle at top left, rgba(15, 118, 110, 0.16), transparent 38%),
            linear-gradient(135deg, #ffffff 0%, #eef5f8 100%);
        box-shadow: var(--events-shadow);
        border-radius: 18px;
    }

    .events-hero h1 {
        margin: 0 0 10px;
        font-size: clamp(28px, 5vw, 40px);
        font-weight: 900;
    }

    .events-hero p {
        margin: 0;
        max-width: 760px;
        color: var(--events-text-muted);
        line-height: 1.65;
    }

    .search-container-modern {
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 14px 0;
        background: color-mix(in srgb, var(--events-surface) 92%, transparent);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--events-border);
        margin-bottom: 18px;
    }

    .toolbar-search {
        display: flex;
        gap: 10px;
    }

    .input-search-modern {
        min-height: 50px;
        border: 1px solid var(--events-border);
        background: var(--events-surface);
        box-shadow: var(--events-shadow);
        border-radius: 14px;
    }

    .btn-filter-modern {
        width: 50px;
        min-width: 50px;
        height: 50px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: linear-gradient(135deg, var(--events-accent) 0%, var(--events-accent-2) 100%);
        color: #fff;
        box-shadow: var(--events-shadow);
        border-radius: 14px;
    }

    .shop-product-fillter {
        border: 1px solid var(--events-border);
        background: var(--events-surface);
        box-shadow: var(--events-shadow);
        padding: 16px 18px;
        margin-bottom: 18px;
        border-radius: 18px;
    }

    .shop-product-fillter .sort-by-product-area {
        display: none;
    }

    .event-list-modern {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .event-card-modern {
        background: var(--events-surface);
        border: 1px solid var(--events-border);
        box-shadow: var(--events-shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        height: 100%;
        border-radius: 18px;
    }

    .event-card-modern:hover {
        transform: translateY(-4px);
        border-color: rgba(0, 78, 96, 0.25);
    }

    .event-card-media {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        background: var(--events-surface-soft);
        overflow: hidden;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .event-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .event-card-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.82);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        gap: 6px;
    }

    .event-card-content {
        padding: 14px 12px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }

    .event-card-category {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--events-text-muted);
        font-weight: 700;
    }

    .event-card-title {
        margin: 0;
        font-size: 15px;
        line-height: 1.3;
        font-weight: 800;
        min-height: 38px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .event-card-title a {
        color: var(--events-text);
        text-decoration: none;
    }

    .event-card-summary {
        margin: 0;
        color: var(--events-text-muted);
        line-height: 1.55;
        font-size: 13px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 40px;
    }

    .event-card-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 12px;
        color: var(--events-text-muted);
        min-height: 76px;
    }

    .event-card-meta span {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: auto;
        padding: 0;
        background: transparent;
        font-size: 12px;
        font-weight: 600;
        color: var(--events-text-muted);
    }

    .event-card-footer {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }

    .event-card-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--events-accent) 0%, var(--events-accent-2) 100%);
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.2s ease;
    }

    .event-card-link:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    .event-empty-state {
        grid-column: 1 / -1;
        padding: 40px 24px;
        border: 1px solid var(--events-border);
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfd 100%);
        box-shadow: var(--events-shadow);
        text-align: center;
        border-radius: 18px;
    }

    .event-empty-state i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        background: rgba(15, 118, 110, 0.1);
        color: var(--events-accent);
        font-size: 24px;
        margin-bottom: 14px;
        border-radius: 50%;
    }

    .event-empty-state h4 {
        margin: 0 0 8px;
        font-size: 22px;
        font-weight: 800;
    }

    .event-empty-state p {
        margin: 0;
        color: var(--events-text-muted);
    }

    .filters-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.28);
        backdrop-filter: blur(3px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        z-index: 1200;
    }

    .filters-overlay.is-open {
        opacity: 1;
        visibility: visible;
    }

    .filter-panel-modern {
        position: fixed;
        top: 0;
        right: 0;
        width: min(420px, 100%);
        height: 100vh;
        background: var(--events-surface);
        border-left: 1px solid var(--events-border);
        box-shadow: var(--events-shadow);
        z-index: 1201;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }

    .filter-panel-modern.is-open {
        transform: translateX(0);
    }

    .filter-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--events-border);
    }

    .filter-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
    }

    .filter-panel-subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: var(--events-text-muted);
    }

    .filter-panel-close {
        width: 40px;
        height: 40px;
        border: 1px solid var(--events-border);
        background: var(--events-surface-soft);
        border-radius: 12px;
    }

    .filter-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 20px 20px;
    }

    .filter-panel-body .categories-dropdown-wrap {
        padding: 16px !important;
        margin: 0 0 14px !important;
        border: 1px solid var(--events-border);
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
        border-radius: 18px;
    }

    .filter-panel-body .categories-dropdown-wrap h5 {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 800;
    }

    .filter-panel-body #btnUbicacion {
        min-height: 46px;
        border: 1px solid var(--events-border);
        background: var(--events-surface-soft);
        color: var(--events-text);
        font-weight: 700;
        border-radius: 12px;
    }

    .filter-panel-body .sort-fecha,
    .filter-panel-body .sort-show {
        display: block !important;
        position: static !important;
        box-shadow: none !important;
        border: none !important;
        background: transparent !important;
    }

    .filter-panel-body .sort-fecha ul,
    .filter-panel-body .sort-show ul {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .filter-panel-body .sort-fecha a,
    .filter-panel-body .sort-show a {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        border: 1px solid var(--events-border);
        background: var(--events-surface-soft);
        color: var(--events-text);
        font-weight: 700;
        text-decoration: none;
        border-radius: 12px;
    }

    .filter-panel-body .sort-fecha a.active,
    .filter-panel-body .sort-show a.active {
        background: linear-gradient(135deg, var(--events-accent) 0%, var(--events-accent-2) 100%);
        color: #fff;
        border-color: transparent;
    }

    .filter-panel-body .form-check {
        padding: 10px 12px;
        margin: 0 0 8px;
        border: 1px solid var(--events-border);
        border-radius: 14px;
        width: 100%;
        min-height: 50px;
        display: flex;
        align-items: center;
        background: var(--events-surface);
    }

    .filter-panel-body .form-check-input {
        margin-right: 8px;
        flex-shrink: 0;
    }

    .filter-panel-body .form-check-label {
        margin: 0;
        font-size: 14px;
        color: var(--events-text);
        font-weight: 600;
    }

    .filter-panel-footer {
        display: flex;
        gap: 10px;
        padding: 16px 20px 20px;
        border-top: 1px solid var(--events-border);
    }

    .btn-filter-secondary,
    .btn-filter-primary {
        flex: 1;
        min-height: 46px;
        font-weight: 700;
        border-radius: 12px;
    }

    .btn-filter-secondary {
        border: 1px solid var(--events-border);
        background: var(--events-surface-soft);
    }

    .btn-filter-primary {
        border: none;
        background: linear-gradient(135deg, var(--events-accent) 0%, var(--events-accent-2) 100%);
        color: #fff;
    }

    .pagination .page-link {
        background: var(--events-surface);
        border-color: var(--events-border);
        color: var(--events-text);
        border-radius: 10px;
        margin: 0 2px;
    }

    .pagination .page-item.active .page-link {
        background: var(--events-accent);
        border-color: var(--events-accent);
        color: #fff;
    }

    @media (min-width: 768px) {
        .event-list-modern {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1200px) {
        .event-list-modern {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 575.98px) {
        .event-list-modern {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container events-shell">
    <section class="events-hero">
        <h1>Eventos para conectar, aprender y crecer</h1>
        <p>Encuentra eventos activos con una vista más limpia, ordenada y enfocada en ubicación, modalidad, tipo de entrada y cantidad mostrada.</p>
    </section>

    <div class="search-container-modern">
        <div class="container-fluid">
            <div class="toolbar-search">
                <div class="flex-grow-1">
                    <input type="text" id="inputBusquedaEventos" class="form-control input-search-modern" placeholder="Buscar por título de eventos">
                </div>
                <button type="button" class="btn-filter-modern" id="openFilterPanel" aria-expanded="false" aria-controls="panelFiltros">
                    <i class="fi-rs-filter"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row" style="transform: none;">
        <div class="col-lg-12">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <h5>Encontramos <strong class="text-brand" id="totalProductosGeneral">0</strong> eventos para ti</h5>
                </div>
                <div class="sort-by-product-area">
                    <div class="results-count text-muted small">Usa el panel lateral para cambiar ubicación, fecha, cantidad mostrada y filtros.</div>
                </div>
            </div>

            <div class="loop-grid event-list-modern mb-50"></div>

            <div class="pagination-area mt-15 mb-sm-5 mb-lg-0">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start" id="paginacionEventos"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div id="filtersOverlay" class="filters-overlay"></div>

<aside id="panelFiltros" class="filter-panel-modern" aria-hidden="true">
    <div class="filter-panel-header">
        <div>
            <h4 class="filter-panel-title">Filtrar eventos</h4>
            <p class="filter-panel-subtitle">Ubicación, fecha, cantidad y características del evento.</p>
        </div>
        <button type="button" class="filter-panel-close" id="closeFilterPanel">
            <i class="fi-rs-cross-small"></i>
        </button>
    </div>

    <div class="filter-panel-body">
        <div class="categories-dropdown-wrap style-2 font-heading">
            <h5 class="border-bottom mb-2 mt-0 pb-2"><i class="fi-rs-marker"></i> Ubicación</h5>
            <button type="button" id="btnUbicacion" class="btn btn-sm btn-outline-dark w-100" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                <i class="fi-rs-marker me-1"></i> Cambiar ubicación
            </button>
        </div>

        <div class="categories-dropdown-wrap style-2 font-heading">
            <h5 class="border-bottom mb-2 mt-0 pb-2"><i class="fi-rs-calendar"></i> Fecha</h5>
            <div class="sort-by-dropdown sort-fecha d-block">
                <ul>
                    <li><a class="active" href="#" data-value="cercana">Más cercana</a></li>
                    <li><a href="#" data-value="lejana">Más lejana</a></li>
                </ul>
            </div>
        </div>

        <div class="categories-dropdown-wrap style-2 font-heading">
            <h5 class="border-bottom mb-2 mt-0 pb-2"><i class="fi-rs-apps"></i> Show</h5>
            <div class="sort-by-dropdown sort-show d-block">
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

        <div class="categories-dropdown-wrap style-2 font-heading">
            <h5 class="border-bottom mb-2 mt-0 pb-2"><i class="fi-rs-ticket"></i> SubTipo de Eventos</h5>
            <div id="filtro-subtipo" class="mt-2"></div>
        </div>

        <div class="categories-dropdown-wrap style-2 font-heading">
            <h5 class="border-bottom mb-2 mt-0 pb-2"><i class="fi-rs-world"></i> Modalidad</h5>
            <div id="filtro-modalidad" class="mt-2"></div>
        </div>

        <div id="subcats-box" class="categories-dropdown-wrap style-2 font-heading">
            <h5 class="border-bottom mb-2 mt-0 pb-2"><i class="fi-rs-ticket-alt"></i> Tipo Entrada</h5>
            <div id="filtro-tipo_entrada" class="mt-2"></div>
        </div>
    </div>

    <div class="filter-panel-footer">
        <button type="button" class="btn-filter-secondary" id="minimizeFilterPanel">Minimizar</button>
        <button type="button" class="btn-filter-primary" id="applyFilterPanel">Aplicar</button>
    </div>
</aside>

<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px;">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalUbicacionLabel">
                    <i class="fi-rs-marker me-1"></i> Elige tu ubicación
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label mb-1">Provincia</label>
                    <select id="selectProvincia" class="form-control" required></select>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1">Cantón</label>
                    <select id="selectCanton" class="form-control" required></select>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" id="limpiarUbicacion">Limpiar ubicación</button>
                <button type="button" class="btn btn-primary" id="guardarUbicacion">Listo</button>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>

<script>
    let eventosPorPagina = 6;
    let todosLosEventos = [];
    let filtradosCache = [];
    let ordenFecha = 'cercana';

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

    const stateFiltros = {
        subtipos: new Set(),
        modalidad: new Set(),
        tipoEntrada: new Set()
    };

    const norm = s => (s ?? '').toString().trim().toLowerCase();
    const cap = s => (s ?? '').toString().trim().replace(/^\p{L}/u, c => c.toUpperCase());

    function capitalizarPrimeraLetra(s) {
        s = (s ?? '').toString().trim();
        return s ? s[0].toUpperCase() + s.slice(1) : s;
    }

    function buildFiltros(data) {
        const mapSubtipos = new Map();
        data.forEach(ev => {
            if (Array.isArray(ev.subtipos) && ev.subtipos.length) {
                ev.subtipos.forEach(s => {
                    const id = Number(s.id);
                    if (id > 0 && !mapSubtipos.has(id)) mapSubtipos.set(id, s.nombre);
                });
                return;
            }
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

        const setModalidad = new Set();
        data.forEach(ev => {
            const v = (ev.modalidad || '').toString().trim();
            if (v) setModalidad.add(v);
        });

        const setTipoEnt = new Set();
        data.forEach(ev => {
            const v = (ev.tipo_entrada || '').toString().trim();
            if (v) setTipoEnt.add(v);
        });

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

        $('#filtro-subtipo').off('change').on('change', 'input[name="flt-subtipo"]', function() {
            if (this.value === '__all__') {
                stateFiltros.subtipos.clear();
                $('#filtro-subtipo input[name="flt-subtipo"]').not(this).prop('checked', false);
                this.checked = true;
                filtrarYMostrar(1);
                return;
            }
            this.checked ? stateFiltros.subtipos.add(this.value) : stateFiltros.subtipos.delete(this.value);
            $('#subtipo-all').prop('checked', stateFiltros.subtipos.size === 0);
            filtrarYMostrar(1);
        });
        $('#filtro-modalidad').off('change').on('change', 'input[name="flt-modalidad"]', function() {
            if (this.value === '__all__') {
                stateFiltros.modalidad.clear();
                $('#filtro-modalidad input[name="flt-modalidad"]').not(this).prop('checked', false);
                this.checked = true;
                filtrarYMostrar(1);
                return;
            }
            this.checked ? stateFiltros.modalidad.add(this.value) : stateFiltros.modalidad.delete(this.value);
            $('#modalidad-all').prop('checked', stateFiltros.modalidad.size === 0);
            filtrarYMostrar(1);
        });
        $('#filtro-tipo_entrada').off('change').on('change', 'input[name="flt-tipo-entrada"]', function() {
            if (this.value === '__all__') {
                stateFiltros.tipoEntrada.clear();
                $('#filtro-tipo_entrada input[name="flt-tipo-entrada"]').not(this).prop('checked', false);
                this.checked = true;
                filtrarYMostrar(1);
                return;
            }
            this.checked ? stateFiltros.tipoEntrada.add(this.value) : stateFiltros.tipoEntrada.delete(this.value);
            $('#tipo_entrada-all').prop('checked', stateFiltros.tipoEntrada.size === 0);
            filtrarYMostrar(1);
        });
    }

    function renderCheckGroup(containerSel, items) {
        const wrap = document.querySelector(containerSel);
        if (!wrap) return;
        wrap.innerHTML = '';
        const tipo = containerSel.replace('#filtro-', '');
        wrap.insertAdjacentHTML('beforeend', `
      <div class="form-check mb-1">
        <input class="form-check-input" type="checkbox" id="${tipo}-all" name="${items[0]?.name || tipo}" value="__all__" checked>
        <label class="form-check-label" for="${tipo}-all">Todos</label>
      </div>
    `);
        items.forEach(it => {
            wrap.insertAdjacentHTML('beforeend', `
      <div class="form-check mb-1">
        <input class="form-check-input" type="checkbox" id="${it.id}" name="${it.name}" value="${it.value}">
        <label class="form-check-label" for="${it.id}">${it.label}</label>
      </div>
    `);
        });
    }

    function cargarProvincias() {
        const selectProvincia = document.getElementById("selectProvincia");
        selectProvincia.innerHTML = '<option value="">Seleccione una provincia</option>';

        Object.entries(datosEcuador).forEach(([codProv, objProv]) => {
            const option = document.createElement("option");
            option.value = codProv;
            option.textContent = capitalizarPrimeraLetra(objProv.provincia);
            selectProvincia.appendChild(option);
        });

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

    function cargarCantones(codProvincia) {
        const selectCanton = document.getElementById("selectCanton");
        selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';

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

    document.getElementById('guardarUbicacion').addEventListener('click', function() {
        const selProv = document.getElementById('selectProvincia').value || '';
        const selCant = document.getElementById('selectCanton').value || '';

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
        filtrarYMostrar(1);

        const modalEl = document.getElementById('modalUbicacion');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    });

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

        localStorage.setItem('ubicacionSeleccionada', JSON.stringify({
            provincia: provinciaSel,
            canton: cantonSel
        }));
    }

    function resetSelectCanton() {
        const selectCanton = document.getElementById("selectCanton");
        selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
        cantonSel = {
            id: null,
            nombre: null
        };
    }

    function labelUbicacion() {
        if (provinciaSel.nombre && cantonSel.nombre) return `PROVINCIA: ${provinciaSel.nombre}; CANTÓN: ${cantonSel.nombre}`;
        if (provinciaSel.nombre) return `PROVINCIA: ${provinciaSel.nombre}`;
        return 'Cambiar ubicación';
    }

    function parseFechaSeguro(v) {
        if (!v) return null;

        let s = String(v).trim();
        if (/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}/.test(s)) {
            s = s.replace(" ", "T");
        }

        const d = new Date(s);
        return isNaN(d.getTime()) ? null : d;
    }

    function eventoVigente(ev) {
        const now = new Date();
        const ini = parseFechaSeguro(ev.fecha_hora_inicio);
        const fin = parseFechaSeguro(ev.fecha_hora_fin);

        if (fin) return fin.getTime() >= now.getTime();
        if (ini) return ini.getTime() >= now.getTime();
        return false;
    }

    function filtrarActual() {
        const q = norm(document.querySelector('.widget_search input[type="text"]')?.value || '');
        let data = todosLosEventos.slice();

        data = data.filter(eventoVigente);

        if (q) data = data.filter(e => norm(e.titulo).includes(q));
        if (provinciaSel.nombre) data = data.filter(e => norm(e.provincia) === norm(provinciaSel.nombre));
        if (cantonSel.nombre) data = data.filter(e => norm(e.canton) === norm(cantonSel.nombre));

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
                        if (Array.isArray(arr)) ids = arr.map(x => String(Number(String(x).replace(/\D+/g, ''))));
                    } catch (_) {}
                }
                return ids.some(id => stateFiltros.subtipos.has(String(id)));
            });
        }

        data.sort((a, b) => {
            const fa = parseFechaSeguro(a.fecha_hora_inicio);
            const fb = parseFechaSeguro(b.fecha_hora_inicio);
            const ta = fa ? fa.getTime() : Number.MAX_SAFE_INTEGER;
            const tb = fb ? fb.getTime() : Number.MAX_SAFE_INTEGER;
            return ordenFecha === 'lejana' ? tb - ta : ta - tb;
        });

        return data;
    }

    function mostrarEventosPagina(pagina = 1) {
        const inicio = (pagina - 1) * eventosPorPagina;
        const fin = inicio + eventosPorPagina;
        const eventosPagina = filtradosCache.slice(inicio, fin);

        const cont = $(".loop-grid");
        cont.html("");

        if (!eventosPagina.length) {
            cont.html(`
            <div class="event-empty-state">
                <i class="fi-rs-calendar"></i>
                <h4>No encontramos eventos</h4>
                <p>Ajusta la ubicación, el texto de búsqueda o los filtros para ver nuevas opciones.</p>
            </div>
        `);
            return;
        }

        eventosPagina.forEach(ev => {
            const fechaInicioObj = parseFechaSeguro(ev.fecha_hora_inicio);
            const fechaFinObj = parseFechaSeguro(ev.fecha_hora_fin);

            const fechaInicio = fechaInicioObj ?
                fechaInicioObj.toLocaleString('es-EC', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) :
                'Fecha por confirmar';

            const fechaFin = fechaFinObj ?
                fechaFinObj.toLocaleString('es-EC', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) :
                'Fecha fin por confirmar';

            const img = ev.imagen ? `../admin/${ev.imagen}` : '../img/FULMUV-NEGRO.png';
            const modalidad = ev.modalidad || 'Modalidad por confirmar';
            const tipoEntrada = ev.tipo_entrada || 'Entrada por confirmar';
            const ubicacion = `${(ev.provincia || '').trim()}${ev.canton ? ' - ' + ev.canton.trim() : ''}`.trim() || 'Ubicación por confirmar';

            let subtipoTxt = 'Evento';
            if (Array.isArray(ev.subtipos) && ev.subtipos.length) {
                subtipoTxt = ev.subtipos[0].nombre || 'Evento';
            }

            cont.append(`
            <article class="event-card-modern wow fadeIn animated">
                <div class="event-card-media" onclick="return irADetalleEventoConTerminos(${ev.id_evento});">
                    <img src="${img}" alt="${ev.titulo ?? 'Evento'}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">
                    <span class="event-card-badge">
                        <i class="fi-rs-calendar"></i> Activo
                    </span>
                </div>

                <div class="event-card-content">
                    <div class="event-card-category">${subtipoTxt}</div>

                    <h3 class="event-card-title">
                        <a href="detalle_eventos.php?q=${ev.id_evento}" onclick="return irADetalleEventoConTerminos(${ev.id_evento});">
                            ${ev.titulo ?? ''}
                        </a>
                    </h3>

                    <p class="event-card-summary">
                        ${ev.descripcion ?? 'Consulta el detalle completo del evento para conocer agenda, acceso y condiciones.'}
                    </p>

                    <div class="event-card-meta">
                        <span><i class="fi-rs-marker"></i> ${ubicacion}</span>
                        <span><i class="fi-rs-world"></i> ${modalidad}</span>
                        <span><i class="fi-rs-ticket-alt"></i> ${tipoEntrada}</span>
                        <span><i class="fi-rs-clock"></i> ${fechaInicio}</span>
                        <span><i class="fi-rs-time-check"></i> ${fechaFin}</span>
                    </div>

                    <div class="event-card-footer">
                        <a href="detalle_eventos.php?q=${ev.id_evento}" class="event-card-link" onclick="return irADetalleEventoConTerminos(${ev.id_evento});">
                            Ver detalle <i class="fi-rs-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>
        `);
        });
    }

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

    function filtrarYMostrar(pagina = 1) {
        filtradosCache = filtrarActual();
        $("#totalProductosGeneral").text(filtradosCache.length);
        mostrarEventosPagina(pagina);
        generarPaginacion(Math.max(1, Math.ceil(filtradosCache.length / eventosPorPagina)), pagina);
    }

    const searchInput = document.querySelector('.widget_search input[type="text" ]');
    if (searchInput) {
        searchInput.addEventListener('input', () => filtrarYMostrar(1));
        searchInput.closest('form')?.addEventListener('submit', e => e.preventDefault());
    }

    $('.sort-show a').on('click', function(e) {
        e.preventDefault();
        const val = $(this).data('value');
        $('.sort-show a').removeClass('active');
        $(this).addClass('active');
        $("#textoCantidadEventos").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

        if (val === 'all') eventosPorPagina = Number.MAX_SAFE_INTEGER;
        else eventosPorPagina = parseInt(val, 10) || 6;
        filtrarYMostrar(1);
    });

    $('.sort-fecha a').on('click', function(e) {
        e.preventDefault();
        const val = $(this).data('value');
        $('.sort-fecha a').removeClass('active');
        $(this).addClass('active');
        ordenFecha = val || 'cercana';
        $("#textoOrdenFecha").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);
        filtrarYMostrar(1);
    });

    $.get('../api/v1/fulmuv/eventos/all', function(ret) {
        if (!ret.error && Array.isArray(ret.data)) {
            todosLosEventos = ret.data;
            buildFiltros(todosLosEventos);
            filtrarYMostrar(1);
        } else {
            $(".loop-grid").html('<p class="text-danger">No se encontraron eventos disponibles.</p>');
        }
    }, 'json');
</script>
