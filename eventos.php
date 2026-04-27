<?php
include 'includes/header.php';
?>
<link rel="canonical" href="https://fulmuv.com/eventos.php">

<style>
    .fulmuv-filter-accordion .accordion-item {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 14px;
        background: #fff;
    }

    .fulmuv-filter-accordion .accordion-button {
        font-weight: 700;
        font-size: 16px;
        color: #111827;
        background: #fff;
        box-shadow: none;
        padding: 14px 16px;
    }

    .fulmuv-filter-accordion .accordion-button:not(.collapsed) {
        color: #004e60;
        background: #f8fafc;
    }

    .fulmuv-filter-accordion .accordion-button:focus {
        box-shadow: none;
    }

    .fulmuv-filter-accordion .accordion-body {
        padding: 14px 16px 10px;
    }
</style>

<div class="container">
    <div class="archive-header-2 text-center mt-30">
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="sidebar-widget-2 widget_search mb-50">
                    <div class="fulmuv-pgsearch-shell">
                        <span class="fulmuv-pgsearch-brain" aria-hidden="true">
                            <i class="fa-solid fa-brain"></i>
                        </span>
                        <input type="text" class="fulmuv-pgsearch-input" placeholder="Buscar por título de eventos" autocomplete="off" />
                        <button type="button" class="fulmuv-pgsearch-clear" aria-label="Limpiar búsqueda">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row flex-row-reverse" style="transform: none;">
        <div class="mobile-filterbar d-lg-none mb-3 justify-content-end align-items-end d-flex">
            <button type="button" class="btn btn-primary d-flex align-items-center justify-content-between"
                id="btnToggleMobileFilters">
                <span class="d-flex align-items-center gap-2">
                    <i class="fi-rs-search"></i>
                    <span class="text-white fw-bold">Búsqueda y filtros</span>
                </span>
            </button>
        </div>
        <div class="col-lg-4-5">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <h5>Encontramos <strong class="text-brand" id="totalProductosGeneral"></strong> eventos para ti!</h5>
                </div>
                <div class="sort-by-product-area">
                    <div class="sort-by-cover d-flex justify-content-center align-items-center me-2">
                        <div>
                            <button type="button" id="btnUbicacion" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <i class="fi-rs-marker me-1"></i> Cambiar ubicación
                            </button>
                        </div>
                    </div>
                    <div class="sort-by-cover mr-10">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-calendar"></i> Fecha:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span id="textoOrdenFecha">Más cercana <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-fecha">
                            <ul>
                                <li><a class="active" href="#" data-value="cercana">Más cercana</a></li>
                                <li><a href="#" data-value="lejana">Más lejana</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="sort-by-cover mr-10">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps"></i>Show:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span id="textoCantidadEventos"> 6 <i class="fi-rs-angle-small-down"></i></span>
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
            <div class="loop-grid loop-list pr-30 mb-50"></div>

            <div class="pagination-area mt-15 mb-sm-5 mb-lg-0">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start" id="paginacionEventos"></ul>
                </nav>
            </div>
        </div>
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar fulmuv-sidebar-col" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
            <div class="fulmuv-filter-panel" id="mobileFilters">
                <div class="accordion fulmuv-filter-accordion mt-30" id="filtersAccordionEventos">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSubtipoEventos">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubtipoEventos" aria-expanded="true" aria-controls="collapseSubtipoEventos">
                                SubTipo de Eventos
                            </button>
                        </h2>
                        <div id="collapseSubtipoEventos" class="accordion-collapse collapse show" aria-labelledby="headingSubtipoEventos" data-bs-parent="#filtersAccordionEventos">
                            <div class="accordion-body"><div id="filtro-subtipo"></div></div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingModalidadEventos">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModalidadEventos" aria-expanded="false" aria-controls="collapseModalidadEventos">
                                Modalidad
                            </button>
                        </h2>
                        <div id="collapseModalidadEventos" class="accordion-collapse collapse" aria-labelledby="headingModalidadEventos" data-bs-parent="#filtersAccordionEventos">
                            <div class="accordion-body"><div id="filtro-modalidad"></div></div>
                        </div>
                    </div>
                    <div id="subcats-box" class="accordion-item">
                        <h2 class="accordion-header" id="headingTipoEntradaEventos">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTipoEntradaEventos" aria-expanded="false" aria-controls="collapseTipoEntradaEventos">
                                Tipo Entrada
                            </button>
                        </h2>
                        <div id="collapseTipoEntradaEventos" class="accordion-collapse collapse" aria-labelledby="headingTipoEntradaEventos" data-bs-parent="#filtersAccordionEventos">
                            <div class="accordion-body"><div id="filtro-tipo_entrada"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal Ubicación -->
<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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


<?php include 'includes/mobile_bottom_nav.php'; ?>
<?php
include 'includes/footer.php';
?>
<script src="js/eventos.js"></script>

<script>
    let eventosPorPagina = 6;
    let todosLosEventos = [];
    let filtradosCache = [];
    let ordenFecha = 'cercana';

    function parseMysqlDatetime(dtStr) {
        if (!dtStr) return null;
        return new Date(String(dtStr).replace(" ", "T"));
    }

    function isMembresiaActiva(item) {
        const memb = item?.membresia;
        if (!memb) return false;
        const estado = (memb.estado_membresia || memb.estado || "").toString().toUpperCase();
        if (estado && estado !== "ACTIVA") return false;
        const now = new Date();
        const inicio = parseMysqlDatetime(memb.fecha_inicio || "");
        const fin = parseMysqlDatetime(memb.fecha_fin || "");
        if (inicio && now.getTime() < inicio.getTime()) return false;
        if (fin && now.getTime() > fin.getTime()) return false;
        return true;
    }

    function filterByMembresiaActiva(items) {
        return (items || []).filter(isMembresiaActiva);
    }

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

    fetch('provincia_canton_parroquia.json')
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
    const filtrosTexto = {
        subtipo: '',
        modalidad: '',
        tipo_entrada: ''
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
            $('#filtro-subtipo .filter-all-option').prop('checked', stateFiltros.subtipos.size === 0);
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
            $('#filtro-modalidad .filter-all-option').prop('checked', stateFiltros.modalidad.size === 0);
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
            $('#filtro-tipo_entrada .filter-all-option').prop('checked', stateFiltros.tipoEntrada.size === 0);
            filtrarYMostrar(1);
        });
    }

    function renderCheckGroup(containerSel, items) {
        const wrap = document.querySelector(containerSel);
        if (!wrap) return;
        const tipo = containerSel.replace('#filtro-', '');
        wrap.innerHTML = `
        <div class="mb-3">
            <input type="text" class="form-control form-control-sm filter-option-search"
                data-filter-type="${tipo}" placeholder="Buscar dentro del filtro"
                value="${String(filtrosTexto[tipo] || '').replace(/"/g, '&quot;')}">
        </div>
        <div class="form-check mb-1 filter-option-row" data-filter-search-text="todos">
            <input class="form-check-input filter-all-option" type="checkbox" id="${tipo}-all" name="${items[0]?.name || tipo}" value="__all__" checked>
            <label class="form-check-label" for="${tipo}-all">Todos</label>
        </div>`;
        items.forEach(it => {
            wrap.insertAdjacentHTML('beforeend', `
      <div class="form-check mb-1 filter-option-row" data-filter-search-text="${norm(it.label)}">
        <input class="form-check-input" type="checkbox" id="${it.id}" name="${it.name}" value="${it.value}">
        <label class="form-check-label" for="${it.id}">${it.label}</label>
      </div>
    `);
        });
        wrap.insertAdjacentHTML('beforeend', `<small class="text-muted filter-option-empty" style="display:none;">No hay coincidencias.</small>`);
        applyFilterOptionSearch(tipo, filtrosTexto[tipo] || '');
    }

    function applyFilterOptionSearch(tipo, valor) {
        const wrap = document.querySelector(`#filtro-${tipo}`);
        if (!wrap) return;
        const rows = wrap.querySelectorAll('.filter-option-row');
        let visibles = 0;
        rows.forEach(row => {
            const candidate = row.getAttribute('data-filter-search-text') || '';
            const match = !valor || candidate.includes(norm(valor));
            row.style.display = match ? '' : 'none';
            if (match) visibles += 1;
        });
        const empty = wrap.querySelector('.filter-option-empty');
        if (empty) empty.style.display = visibles === 0 ? '' : 'none';
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
            cont.html('<p class="text-muted">No hay eventos con los filtros actuales.</p>');
            return;
        }

        eventosPagina.forEach(ev => {
            const fechaInicio = ev.fecha_hora_inicio ? new Date(ev.fecha_hora_inicio).toLocaleString() : '';
            const fechaFin = ev.fecha_hora_fin ? new Date(ev.fecha_hora_fin).toLocaleString() : '';
            const img = ev.imagen ? `admin/${ev.imagen}` : 'img/FULMUV-NEGRO.png';

            cont.append(`
<article class="wow fadeIn animated hover-up mb-30" style="visibility: visible;">
    <div class="post-thumb"
        style="background-image:url('${img}');background-size:cover;background-position:center;height:210px;border-radius:8px;">
    </div>
    <div class="entry-content-2 pl-50">
        <h3 class="post-title mb-20">
            <a href="detalle_eventos.php?q=${ev.id_evento}">${ev.titulo ?? ''}</a>
        </h3>
        <p class="post-exerpt mb-40">${ev.descripcion ?? ''}</p>
        <div class="entry-meta meta-1 font-xs color-grey mt-10 pb-10">
            <div>
                <span class="post-on me-2">
                    <i class="fi-rs-marker"></i> ${(ev.provincia||'')}${ev.canton ? ' · '+ev.canton : ''}
                </span>
                <span class="post-on">Inicio: ${fechaInicio}</span>
                <span class="hit-count has-dot">Fin: ${fechaFin}</span>
            </div>
            <a href="detalle_eventos.php?q=${ev.id_evento}" class="text-brand font-heading fw-bold">
                Ver más <i class="fi-rs-arrow-right"></i>
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
        const total = Math.max(1, Math.ceil(filtradosCache.length / eventosPorPagina));
        const paginaSegura = Math.min(Math.max(pagina, 1), total);
        mostrarEventosPagina(paginaSegura);
        generarPaginacion(total, paginaSegura);
    }

    const searchInput = document.querySelector('.widget_search input[type="text" ]');
    if (searchInput) {
        searchInput.addEventListener('input', () => filtrarYMostrar(1));
        searchInput.closest('form')?.addEventListener('submit', e => e.preventDefault());
    }

    $(document).on('input', '.filter-option-search', function() {
        const tipo = this.dataset.filterType;
        filtrosTexto[tipo] = this.value.trim();
        applyFilterOptionSearch(tipo, filtrosTexto[tipo]);
    });

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

    $.get('api/v1/fulmuv/eventos/all', function(ret) {
        if (!ret.error && Array.isArray(ret.data)) {
            todosLosEventos = filterByMembresiaActiva(ret.data || []);
            buildFiltros(todosLosEventos);
            filtrarYMostrar(1);
        } else {
            $(".loop-grid").html('<p class="text-danger">No se encontraron eventos disponibles.</p>');
        }
    }, 'json');
</script>
