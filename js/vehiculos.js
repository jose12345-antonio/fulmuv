/* =================== ESTADO =================== */
let itemsPerPage = 40, currentPage = 1, productosData = [];
let sortOption = "todos", searchText = "";
let subcategoriasSeleccionadas = [], subcategoriasHijas = [], subcategoriasHijasSeleccionadas = [];
let marcasUnicas = [], modelosUnicos = [], marcaSeleccionada = null, modeloSeleccionado = null;
let precioMin = 0, precioMax = Infinity, categoriasFiltradas = [], rangeSlider, catsIndex = null, datosEcuador = {};
// let formatoMoneda = new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
let moneyFormat = wNumb({ decimals: 0, thousand: ",", prefix: "$" });

// Ubicación
let provinciaSel = { id: null, nombre: null }, cantonSel = { id: null, nombre: null };

/* ====== NUEVOS ESTADOS FILTRO ====== */
let modelosSeleccionados = new Set();
let condicionSeleccionadas = new Set();
let tipoAutoSeleccionados = new Set();
let marcaVehSeleccionadas = new Set();
let colorSeleccionados = new Set();
let tapiceriaSeleccionados = new Set();
let climatizacionSeleccionados = new Set();
let referenciasSeleccionadas = new Set();
let anioMinSel = null, anioMaxSel = null;
let isRebuildingFilters = false;
let anioTimer = null;

function formatPrecioSuperscript(valor) {
    const num = Number(valor) || 0;
    const entero = Math.floor(num);
    const centavos = Math.round((num - entero) * 100).toString().padStart(2, '0');
    const enteroFormateado = entero.toLocaleString('es-EC');
    return `<span style="font-size:0.55em; font-weight:normal; vertical-align:middle;">US$</span><strong style="font-size:1.1em;">${enteroFormateado}</strong><sup style="font-size:0.45em; font-weight:normal; vertical-align:super; line-height:1;">${centavos}</sup>`;
}

function recheckSelected(containerSel, set, group) {
    // marca/modelo guardan IDs en set
    $(`${containerSel} .chk-${group}`).each(function () {
        this.checked = set.has(String($(this).val()));
    });
}

function matchesVehicleFilters(prod, options = {}) {
    const ignoreGroups = new Set(options.ignoreGroups || []);
    const ignorePrice = options.ignorePrice === true;
    const ignoreYear = options.ignoreYear === true;
    const ignoreLocation = options.ignoreLocation === true;

    if (!ignoreLocation) {
        const selProv = normalizarTexto(provinciaSel.nombre);
        const selCant = normalizarTexto(cantonSel.nombre);
        const provinciasProd = normalizeToNameArray(prod.provincia).map(normalizarTexto);
        const cantonesProd = normalizeToNameArray(prod.canton).map(normalizarTexto);
        const matchProvincia = !selProv || (provinciasProd.length === 0 ? true : provinciasProd.includes(selProv));
        const matchCanton = !selCant || (cantonesProd.length === 0 ? true : cantonesProd.includes(selCant));

        if (!matchProvincia || !matchCanton) return false;
    }

    if (!ignoreGroups.has("referencia")) {
        const referenciasDeProd = normalizeToNameArray(prod.referencias);
        if (referenciasSeleccionadas.size && !referenciasDeProd.some(v => referenciasSeleccionadas.has(v))) return false;
    }

    if (!ignoreGroups.has("marcav")) {
        const marcaIdsProd = getMarcaIds(prod);
        if (marcaVehSeleccionadas.size && !marcaIdsProd.some(id => marcaVehSeleccionadas.has(id))) return false;
    }

    if (!ignoreGroups.has("modelo")) {
        const modeloIdsProd = getModeloIds(prod);
        if (modelosSeleccionados.size && !modeloIdsProd.some(id => modelosSeleccionados.has(id))) return false;
    }

    if (!ignoreGroups.has("condicion")) {
        const condicionDeProd = normalizeToNameArray(prod.condicionArray ?? prod.condicion);
        if (condicionSeleccionadas.size && !condicionDeProd.some(v => condicionSeleccionadas.has(v))) return false;
    }

    if (!ignoreGroups.has("tipoauto")) {
        const tipoAutoDeProd = normalizeToNameArray(prod.tipo_autoArray ?? prod.tipo_auto);
        if (tipoAutoSeleccionados.size && !tipoAutoDeProd.some(v => tipoAutoSeleccionados.has(v))) return false;
    }

    if (!ignoreGroups.has("color")) {
        const colorDeProd = normalizeToNameArray(prod.colorArray ?? prod.color);
        if (colorSeleccionados.size && !colorDeProd.some(v => colorSeleccionados.has(v))) return false;
    }

    if (!ignoreGroups.has("tapiceria")) {
        const tapDeProd = normalizeToNameArray(prod.tapiceriaArray ?? prod.tapiceria);
        if (tapiceriaSeleccionados.size && !tapDeProd.some(v => tapiceriaSeleccionados.has(v))) return false;
    }

    if (!ignoreGroups.has("clima")) {
        const climaDeProd = normalizeToNameArray(prod.climatizacionArray ?? prod.climatizacion);
        if (climatizacionSeleccionados.size && !climaDeProd.some(v => climatizacionSeleccionados.has(v))) return false;
    }

    if (!ignoreGroups.has("anio") && !ignoreYear) {
        const anioNum = asNumber(prod.anio, NaN);
        const matchAnio = (!Number.isFinite(anioNum)) || ((anioMinSel === null || anioNum >= anioMinSel) && (anioMaxSel === null || anioNum <= anioMaxSel));
        if (!matchAnio) return false;
    }

    if (!ignorePrice) {
        const precio = Number(prod.precio_referencia);
        if (!(precio >= precioMin && precio <= precioMax)) return false;
    }

    return true;
}

function syncPriceSlider(dataset) {
    const precios = (dataset || []).map(p => Number(p.precio_referencia)).filter(n => Number.isFinite(n) && n >= 0);
    const maxPrecio = precios.length ? Math.max(...precios) : 0;
    const nextMin = Number.isFinite(precioMin) ? Math.max(0, Math.min(precioMin, maxPrecio)) : 0;
    const nextMaxBase = Number.isFinite(precioMax) ? precioMax : maxPrecio;
    const nextMax = Math.max(nextMin, Math.min(nextMaxBase, maxPrecio));
    inicializarSlider(maxPrecio, nextMin, nextMax);
}

function getVehicleFilterDataset(base, group, options = {}) {
    return (base || []).filter(prod => matchesVehicleFilters(prod, {
        ignoreGroups: [group],
        ignorePrice: options.ignorePrice !== false,
        ignoreYear: options.ignoreYear === true
    }));
}

function refreshDependentFilters() {
    if (isRebuildingFilters) return;
    isRebuildingFilters = true;

    // dataset base (respeta ubicación)
    const base = filtrarPorUbicacionDataset(productosData);

    const datasetParaFiltros = base.filter(p => matchesVehicleFilters(p, { ignorePrice: true }));
    const groupDatasets = {
        referencia: getVehicleFilterDataset(base, "referencia"),
        marcav: getVehicleFilterDataset(base, "marcav"),
        modelo: getVehicleFilterDataset(base, "modelo"),
        condicion: getVehicleFilterDataset(base, "condicion"),
        tipoauto: getVehicleFilterDataset(base, "tipoauto"),
        color: getVehicleFilterDataset(base, "color"),
        tapiceria: getVehicleFilterDataset(base, "tapiceria"),
        clima: getVehicleFilterDataset(base, "clima"),
        anio: getVehicleFilterDataset(base, "anio", { ignoreYear: true })
    };

    // reconstruye filtros según base
    syncPriceSlider(datasetParaFiltros);
    buildVehicleFilters(datasetParaFiltros, groupDatasets);
    bindVehicleFiltersUI();

    // re-check de lo que ya estaba seleccionado
    recheckSelected("#filtro-marca-veh", marcaVehSeleccionadas, "marcav");
    recheckSelected("#filtro-modelos", modelosSeleccionados, "modelo");
    recheckSelected("#filtro-condicion", condicionSeleccionadas, "condicion");
    recheckSelected("#filtro-tipo-auto", tipoAutoSeleccionados, "tipoauto");
    recheckSelected("#filtro-color", colorSeleccionados, "color");
    recheckSelected("#filtro-tapiceria", tapiceriaSeleccionados, "tapiceria");
    recheckSelected("#filtro-climatizacion", climatizacionSeleccionados, "clima");
    recheckSelected("#filtro-referencias", referenciasSeleccionadas, "referencia");

    isRebuildingFilters = false;
}

/* =================== CARGA INICIAL =================== */
fetch('provincia_canton_parroquia.json').then(r => r.json()).then(d => { datosEcuador = d; cargarProvincias(); });

$(document).ready(function () {
    $("#breadcrumb")?.append(`<a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a><span></span> Lista de Vehículos`);
    $.get("api/v1/fulmuv/vehiculos/All", {}, function (returnedData) {
        if (!returnedData.error) {
            productosData = returnedData.data || [];
            const maxPrecio = Math.max(...productosData.map(p => Number(p.precio_referencia) || 0));
            inicializarSlider(maxPrecio);
            buildMarcasYModelos(productosData);
            // catsIndex = buildCatsAndSubcatsFromProductos(productosData);
            // categoriasFiltradas = catsIndex.categoriasLista;
            // renderCheckboxCategorias(categoriasFiltradas);
            // NUEVOS FILTROS
            buildVehicleFilters(productosData);
            bindVehicleFiltersUI();
            renderEmpresas(productosData, currentPage);
        }
    }, 'json');
});

$(document).on('keydown', '.widget_search input', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

$(document).on('submit', '.widget_search form', function (e) {
    e.preventDefault();
    return false;
});

/* =================== HANDLERS UI =================== */
// DESPUÉS
$("#searchInput").on("input", function () {
    searchText = $(this).val(); // sin .trim() para permitir espacios
    renderEmpresas(productosData, 1);
});

$(".sort-show").on("click", "a", function (e) {
    e.preventDefault();
    $(".sort-show a").removeClass("active"); $(this).addClass("active");
    const value = $(this).data("value");
    itemsPerPage = value === "all" ? productosData.length : parseInt(value);
    $(".sort-by-product-wrap:first .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);
    currentPage = 1; renderEmpresas(productosData, currentPage);
});

$(".sort-order").on("click", "a", function (e) {
    e.preventDefault();
    $(".sort-order a").removeClass("active"); $(this).addClass("active");
    sortOption = $(this).data("value");
    $(".sort-by-product-wrap:last .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);
    renderEmpresas(productosData, 1);
});

$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    if ($(this).closest(".page-item").hasClass("disabled") || $(this).closest(".page-item").hasClass("active")) return;
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(productosData, currentPage);
        scrollToProductsTop();
    }
});

/* =================== HELPERS =================== */
function cleanStr(s) { return (s || '').toString().trim(); }
function asNumber(n, fb = 0) { const v = Number(String(n ?? '').replace(/[^\d.]/g, '')); return Number.isFinite(v) ? v : fb; }

function getMarcaIds(prod) {
    const arr = [...toArray(prod.marcaArray), ...toArray(prod.marca)];
    return arr.map(m => String(m?.id_marca ?? m?.id ?? '')).filter(Boolean);
}

function getModeloIds(prod) {
    const arr = [
        ...toArray(prod.modeloArray),
        ...toArray(prod.modelo),
        ...toArray(prod.modelo_producto),
        ...toArray(prod.modelo_productoo),
        ...toArray(prod.model)
    ];
    return arr.map(m => String(m?.id_modelos_autos ?? m?.id_modelo ?? m?.id ?? '')).filter(Boolean);
}

function parseIdsArray(x) {
    if (Array.isArray(x)) return x.map(n => Number(n)).filter(Boolean);
    if (typeof x === 'string') {
        try { const j = JSON.parse(x); if (Array.isArray(j)) return j.map(Number).filter(Boolean); } catch (_) { }
        return x.split(/[,\s]+/).map(Number).filter(Boolean);
    }
    if (x == null) return []; return [Number(x)].filter(Boolean);
}

function normalizeToNameArray(val) {
    if (val == null) return [];
    if (Array.isArray(val)) {
        return val.flatMap(v => {
            if (typeof v === 'string') return [cleanStr(v)];
            if (typeof v === 'number') return [String(v)];
            if (typeof v === 'object' && v !== null) {
                if ('nombre' in v) return [cleanStr(v.nombre)];
                if ('name' in v) return [cleanStr(v.name)];
                if ('id_color' in v && v.nombre) return [cleanStr(v.nombre)];
            }
            return [];
        });
    }
    if (typeof val === 'object') {
        if ('nombre' in val) return [cleanStr(val.nombre)];
        if ('name' in val) return [cleanStr(val.name)];
    }
    if (typeof val === 'string') {
        try { const j = JSON.parse(val); return normalizeToNameArray(j); } catch (_) { }
        return val.replace(/^\[|\]$/g, '').replace(/^"+|"+$/g, '').replace(/^'+|'+$/g, '')
            .split(',').map(s => cleanStr(s)).filter(Boolean);
    }
    return [];
}

function normalizarTexto(s) { return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase(); }
function hasIntersection(idsArr, idsSet) { for (const id of idsArr) if (idsSet.has(Number(id))) return true; return false; }

function getBusquedaVehiculoScore(prod, rawSearchText) {
    const textoBuscar = normalizarTexto(rawSearchText.trim());
    if (!textoBuscar) return 1; // sin búsqueda, todos pasan

    const brandNames = [...toArray(prod.marcaArray), ...toArray(prod.marca)]
        .map(m => normalizarTexto(m?.nombre ?? m?.name ?? '')).filter(Boolean);
    const modelNames = [...toArray(prod.modeloArray), ...toArray(prod.modelo), ...toArray(prod.modelo_producto), ...toArray(prod.modelo_productoo)]
        .map(m => normalizarTexto(m?.nombre ?? m?.name ?? '')).filter(Boolean);
    const colorNames = normalizeToNameArray(prod.colorArray ?? prod.color).map(normalizarTexto);
    const tapiceriaNames = normalizeToNameArray(prod.tapiceriaArray ?? prod.tapiceria).map(normalizarTexto);

    const haystack = [
        normalizarTexto(prod.nombre),
        normalizarTexto(prod.tags),
        normalizarTexto(String(prod.anio || '')),
        ...brandNames,
        ...modelNames,
        ...colorNames,
        ...tapiceriaNames,
        String(prod.id_producto || prod.id_vehiculo || '')
    ].filter(Boolean).join(' ');

    const terms = textoBuscar.split(/\s+/).filter(Boolean);

    // Prioridad 1: frase completa exacta
    if (haystack.includes(textoBuscar)) return 300;

    // Prioridad 2: todas las palabras presentes
    if (terms.every(t => haystack.includes(t))) return 150;

    // Prioridad 3: alguna palabra presente
    const parcial = terms.reduce((total, t) => total + (haystack.includes(t) ? 40 : 0), 0);
    if (parcial > 0) return parcial;

    return 0; // no coincide
}
/* =================== SLIDER PRECIO =================== */
function inicializarSlider(maxPrecio, startMin = 0, startMax = maxPrecio) {
    const el = document.getElementById("slider-range"); if (!el) return;
    if (el.noUiSlider) { try { el.noUiSlider.destroy(); } catch (_) { } }
    if (!Number.isFinite(maxPrecio) || maxPrecio <= 0) {
        el.innerHTML = ""; $("#slider-range-value1").text("$0"); $("#slider-range-value2").text("$0");
        precioMin = 0; precioMax = Infinity; return;
    }
    const safeMin = Number.isFinite(startMin) ? Math.max(0, Math.min(startMin, maxPrecio)) : 0;
    const safeMax = Number.isFinite(startMax) ? Math.max(safeMin, Math.min(startMax, maxPrecio)) : maxPrecio;
    noUiSlider.create(el, { start: [safeMin, safeMax], step: 1, range: { min: 0, max: maxPrecio }, format: moneyFormat, connect: true });
    el.noUiSlider.on("update", function (values) {
        $("#slider-range-value1").text(values[0]); $("#slider-range-value2").text(values[1]);
        precioMin = parseFloat(moneyFormat.from(values[0])); precioMax = parseFloat(moneyFormat.from(values[1]));
        renderEmpresas(productosData, 1);
    });
}

/* =================== UBICACIÓN =================== */
function cargarProvincias() {
    const selectProvincia = document.getElementById("selectProvincia");
    selectProvincia.innerHTML = '<option value="">Seleccione una provincia</option>';
    Object.entries(datosEcuador).forEach(([codProv, objProv]) => {
        const opt = document.createElement("option"); opt.value = codProv; opt.textContent = capitalizarPrimeraLetra(objProv.provincia); selectProvincia.appendChild(opt);
    });
    selectProvincia.addEventListener("change", (e) => {
        const codProv = e.target.value || null;
        if (!codProv) { provinciaSel = { id: null, nombre: null }; resetSelectCanton(); actualizarUIUbicacionPersistir(); refreshFiltersForCurrentLocation(); renderEmpresas(productosData, 1); return; }
        provinciaSel.id = codProv; provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);
        resetSelectCanton(); cargarCantones(codProv); actualizarUIUbicacionPersistir(); refreshFiltersForCurrentLocation(); renderEmpresas(productosData, 1);
    });
}
function cargarCantones(codProvincia) {
    const selectCanton = document.getElementById("selectCanton");
    selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
    if (!codProvincia || !datosEcuador[codProvincia]) { resetSelectCanton(); return; }
    const cantones = datosEcuador[codProvincia].cantones || {};
    Object.entries(cantones).forEach(([codCanton, objCanton]) => {
        const opt = document.createElement("option"); opt.value = codCanton; opt.textContent = capitalizarPrimeraLetra(objCanton.canton); selectCanton.appendChild(opt);
    });
    selectCanton.addEventListener("change", (e) => {
        const codC = e.target.value || null;
        if (!codC) { cantonSel = { id: null, nombre: null }; actualizarUIUbicacionPersistir(); refreshFiltersForCurrentLocation(); renderEmpresas(productosData, 1); return; }
        const nombre = capitalizarPrimeraLetra((datosEcuador[codProvincia].cantones[codC] || {}).canton || "");
        cantonSel.id = codC; cantonSel.nombre = nombre; actualizarUIUbicacionPersistir(); refreshFiltersForCurrentLocation(); renderEmpresas(productosData, 1);
    });
}
function resetSelectCanton() { const s = document.getElementById("selectCanton"); s.innerHTML = '<option value="">Seleccione un cantón</option>'; cantonSel = { id: null, nombre: null }; }
function labelUbicacion() { if (provinciaSel.nombre && cantonSel.nombre) return `PROVINCIA: ${provinciaSel.nombre}; CANTÓN: ${cantonSel.nombre}`; if (provinciaSel.nombre) return `PROVINCIA: ${provinciaSel.nombre}`; return 'Cambiar ubicación'; }
function actualizarUIUbicacionPersistir() {
    const btn = document.getElementById('btnUbicacion'); if (btn) btn.innerHTML = `<i class="fi-rs-marker me-1"></i> ${labelUbicacion()}`;
    document.getElementById('provincia_id_hidden').value = provinciaSel.id || '';
    document.getElementById('provincia_nombre_hidden').value = provinciaSel.nombre || '';
    document.getElementById('canton_id_hidden').value = cantonSel.id || '';
    document.getElementById('canton_nombre_hidden').value = cantonSel.nombre || '';
    localStorage.setItem('ubicacionSeleccionada', JSON.stringify({ provincia: provinciaSel, canton: cantonSel }));
}
document.getElementById('guardarUbicacion').addEventListener('click', function () {
    actualizarUIUbicacionPersistir(); refreshFiltersForCurrentLocation(); renderEmpresas(productosData, 1);
    const modalEl = document.getElementById('modalUbicacion'); const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); modal.hide();
});
document.getElementById('limpiarUbicacion')?.addEventListener('click', () => {
    provinciaSel = { id: null, nombre: null }; cantonSel = { id: null, nombre: null };
    const sp = document.getElementById('selectProvincia'); const sc = document.getElementById('selectCanton'); if (sp) sp.value = ''; resetSelectCanton();
    actualizarUIUbicacionPersistir(); refreshFiltersForCurrentLocation(); renderEmpresas(productosData, 1);
});
function capitalizarPrimeraLetra(s) { return window.fulmuvTitleCase ? window.fulmuvTitleCase(s) : ((s || '').toString()); }
function filtrarPorUbicacionDataset(dataset) {
    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        // si no hay filtros de ubicación, no filtramos
        if (!selProv && !selCant) return primeraCategoriaEsProducto(prod);

        // prod.provincia y prod.canton pueden ser:
        //  - ['Bolívar']
        //  - '["Bolívar"]'
        //  - 'Bolívar'
        const provinciasProd = normalizeToNameArray(prod.provincia)
            .map(normalizarTexto);    // ['bolivar', ...]
        const cantonesProd = normalizeToNameArray(prod.canton)
            .map(normalizarTexto);    // ['guaranda','chimbo',...]

        const matchProvincia =
            !selProv || (provinciasProd.length === 0 ? true : provinciasProd.includes(selProv));

        const matchCanton =
            !selCant || (cantonesProd.length === 0 ? true : cantonesProd.includes(selCant));

        return primeraCategoriaEsProducto(prod) && matchProvincia && matchCanton;
    });
}


$(document).on("change", "input[name='checkbox']", function () {
    const id = $(this).val();
    if ($(this).is(":checked")) { if (!subcategoriasSeleccionadas.includes(id)) subcategoriasSeleccionadas.push(id); }
    else { subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(x => x !== id); }
    const idsUnicos = [...new Set(subcategoriasSeleccionadas.map(x => parseInt(x)))];
    const hay = idsUnicos.length > 0; $("#subcats-box").toggle(hay);
    if (!hay) {
        const idsTodas = categoriasFiltradas.map(cat => parseInt(cat.id_categoria));
        $.post("api/v1/fulmuv/productos/idCategoria", { id_categoria: idsTodas }, function (rd) {
            if (!rd.error) {
                productosData = rd.data; const mp = Math.max(...productosData.map(p => parseFloat(p.precio_referencia))); inicializarSlider(mp);
                subcategoriasHijas = []; subcategoriasHijasSeleccionadas = []; $("#filtro-sub-categorias").html(""); $("#subcats-box").hide(); renderEmpresas(productosData, 1);
            }
        }, 'json'); return;
    }
    $.post("../api/v1/fulmuv/productos/idCategoria", { id_categoria: idsUnicos }, function (rd) {
        if (!rd.error) { productosData = rd.data; const mp = Math.max(...productosData.map(p => parseFloat(p.precio_referencia))); inicializarSlider(mp); renderEmpresas(productosData, 1); }
    }, 'json');
    const subcats = buildSubcatsForSelected(idsUnicos); subcategoriasHijas = subcats; subcategoriasHijasSeleccionadas = []; renderCheckboxSubcategorias(subcats); $("#subcats-box").toggle(subcats.length > 0);
});
// function renderCheckboxSubcategorias(subcats) {
//     if (!Array.isArray(subcats)) subcats = [];
//     let vis = '', hid = ''; const max = 10;
//     subcats.forEach((sc, i) => {
//         const id = Number(sc.sub_categoria ?? sc.id_sub_categoria ?? sc.id_subcategoria);
//         const item = `<div class="form-check mb-2">
//       <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-sub">
//       <label class="form-check-label fw-normal" for="subcat-${id}">${capitalizarPrimeraLetra(sc.nombre)}</label>
//     </div>`;
//         (i < max ? vis : hid) += item;
//     });
//     const html = `<div class="checkbox-list-visible">${vis || '<small class="text-muted">No hay sub-categorías.</small>'}</div>
//               <div class="more_slide_open-sub" style="display:none;">${hid}</div>
//               ${hid ? `<div class="more_categories-sub cursor-pointer"><span class="icon">+</span><span class="heading-sm-1">Show more...</span></div>` : ''}`;
//     $("#filtro-sub-categorias").html(html);
//     $(document).off("click.moreSub", "#filtro-sub-categorias .more_categories-sub").on("click.moreSub", "#filtro-sub-categorias .more_categories-sub", function () {
//         $(this).toggleClass("show"); $(this).prev(".more_slide_open-sub").slideToggle();
//     });
// }
$(document).on("change", "input[name='checkbox-sub']", function () {
    const id = Number($(this).val());
    if ($(this).is(":checked")) { if (!subcategoriasHijasSeleccionadas.includes(id)) subcategoriasHijasSeleccionadas.push(id); }
    else { subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => x !== id); }
    renderEmpresas(productosData, 1);
});
function buildCatsAndSubcatsFromProductos(productos) {
    const catsMap = new Map(), subsPorCatMap = new Map();
    (productos || []).forEach(p => {
        const catObjs = Array.isArray(p.categorias) ? p.categorias : [];
        let catIds = parseIdsArray(p.categoria ?? p.id_categoria);
        if (catObjs.length) { const ids = catObjs.map(o => Number(o.id)).filter(Boolean); catIds = Array.from(new Set([...catIds, ...ids])); }
        const subObjs = Array.isArray(p.subcategorias) ? p.subcategorias : Array.isArray(p.sub_categorias) ? p.sub_categorias : [];
        let subIds = parseIdsArray(p.sub_categoria ?? p.id_subcategoria ?? p.id_sub_categoria);
        if (subObjs.length) { const ids = subObjs.map(o => Number(o.id)).filter(Boolean); subIds = Array.from(new Set([...subIds, ...ids])); }
        catIds.forEach(cid => {
            if (!cid) return;
            const nombreCat = (catObjs.find(o => Number(o.id) === cid)?.nombre) || `Categoría ${cid}`;
            if (!catsMap.has(cid)) catsMap.set(cid, { id_categoria: cid, nombre: capitalizarPrimeraLetra(nombreCat) });
            if (!subsPorCatMap.has(cid)) subsPorCatMap.set(cid, new Map());
            const sm = subsPorCatMap.get(cid);
            subIds.forEach(sid => {
                if (!sid) return;
                const nombreSub = (subObjs.find(o => Number(o.id) === sid)?.nombre) || `Subcategoría ${sid}`;
                if (!sm.has(sid)) sm.set(sid, { id_sub_categoria: sid, nombre: capitalizarPrimeraLetra(nombreSub) });
            });
        });
    });
    const categoriasLista = Array.from(catsMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
    const subsPorCat = new Map(Array.from(subsPorCatMap.entries()).map(([cid, m]) => [Number(cid), Array.from(m.values()).sort((a, b) => a.nombre.localeCompare(b.nombre))]));
    return { categoriasLista, subsPorCat };
}
function buildSubcatsForSelected(idsCategorias) {
    if (!catsIndex) return [];
    const unicas = new Map();
    (idsCategorias || []).map(Number).forEach(cid => {
        const arr = catsIndex.subsPorCat.get(cid) || []; arr.forEach(sc => { if (!unicas.has(sc.id_sub_categoria)) unicas.set(sc.id_sub_categoria, sc); });
    });
    return Array.from(unicas.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}

/* =================== MARCAS/MODELOS (radios) =================== */
function buildMarcasYModelos(data) {
    const marcasMap = new Map(), modelosMap = new Map();
    (data || []).forEach(p => {
        const marcaIds = parseIdsArray(p.id_marca);
        const marcaObjs = Array.isArray(p.marca) ? p.marca : [];
        marcaIds.forEach(id => {
            if (!marcasMap.has(id)) {
                const found = marcaObjs.find(m => Number(m.id) === Number(id));
                marcasMap.set(id, { id, label: found?.nombre || `Marca ${id}` });
            }
        });
        const modeloIds = parseIdsArray(p.id_modelo);
        const modeloObjs = Array.isArray(p.modelo) ? p.modelo : Array.isArray(p.modelo_productoo) ? p.modelo_productoo : Array.isArray(p.modelo_producto) ? [p.modelo_producto] : [];
        modeloIds.forEach(id => {
            if (!modelosMap.has(id)) {
                const found = modeloObjs.find(m => Number(m.id) === Number(id));
                modelosMap.set(id, { id, label: found?.nombre || `Modelo ${id}` });
            }
        });
    });
    marcasUnicas = Array.from(marcasMap.values()).sort((a, b) => a.label.localeCompare(b.label));
    modelosUnicos = Array.from(modelosMap.values()).sort((a, b) => a.label.localeCompare(b.label));
    renderRadioMarcas(marcasUnicas);
    renderRadioModelos(modelosUnicos);
}
function renderRadioMarcas(marcas) {
    let html = `<div class="form-check mb-2">
    <input class="form-check-input" type="radio" name="radio-marca" id="marca-all" value="" ${marcaSeleccionada === null ? 'checked' : ''}>
    <label class="form-check-label fw-normal" for="marca-all">Todos</label>
  </div>`;
    (marcas || []).forEach(m => {
        html += `<div class="form-check mb-2">
      <input class="form-check-input" type="radio" name="radio-marca" id="marca-${m.id}" value="${m.id}" ${Number(marcaSeleccionada) === Number(m.id) ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="marca-${m.id}">${capitalizarPrimeraLetra(m.label)}</label>
    </div>`;
    });
    $("#filtro-marca").html(html);
    $(document).off("change", "input[name='radio-marca']").on("change", "input[name='radio-marca']", function () {
        const v = $(this).val(); marcaSeleccionada = v === "" ? null : Number(v); renderEmpresas(productosData, 1);
    });
}
function renderRadioModelos(modelos) {
    let html = `<div class="form-check mb-2">
    <input class="form-check-input" type="radio" name="radio-modelo" id="modelo-all" value="" ${modeloSeleccionado === null ? 'checked' : ''}>
    <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
  </div>`;
    (modelos || []).forEach(m => {
        html += `<div class="form-check mb-2">
      <input class="form-check-input" type="radio" name="radio-modelo" id="modelo-${m.id}" value="${m.id}" ${Number(modeloSeleccionado) === Number(m.id) ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="modelo-${m.id}">${capitalizarPrimeraLetra(m.label)}</label>
    </div>`;
    });
    $("#filtro-modelo").html(html);
    $(document).off("change", "input[name='radio-modelo']").on("change", "input[name='radio-modelo']", function () {
        const v = $(this).val(); modeloSeleccionado = v === "" ? null : Number(v); renderEmpresas(productosData, 1);
    });
}

/* =================== NUEVOS FILTROS (checkbox) =================== */
function cssSafe(s) { return String(s).replace(/[^a-zA-Z0-9_-]/g, ''); }
function toMap(set) { const m = new Map(); Array.from(set).sort((a, b) => a.localeCompare(b)).forEach(v => m.set(v, v)); return m; }
function getSelectedSetForVehicleGroup(group) {
    if (group === "modelo") return modelosSeleccionados;
    if (group === "marcav") return marcaVehSeleccionadas;
    if (group === "condicion") return condicionSeleccionadas;
    if (group === "tipoauto") return tipoAutoSeleccionados;
    if (group === "color") return colorSeleccionados;
    if (group === "tapiceria") return tapiceriaSeleccionados;
    if (group === "clima") return climatizacionSeleccionados;
    if (group === "referencia") return referenciasSeleccionadas;
    return new Set();
}

function paintCheckboxes(containerSel, mapOrSet, group) {
    const selectedSet = getSelectedSetForVehicleGroup(group);
    let html = '';

    if (group === "referencia") {
        html += `<div class="mb-3">
      <input type="text" class="form-control form-control-sm filtro-checkbox-search" data-group="${group}" placeholder="Buscar referencia...">
    </div>`;
    }

    html += `<div class="form-check mb-2 filtro-checkbox-option" data-label="todos">
      <input class="form-check-input chk-${group}" type="checkbox" id="${group}-all" value="__all__" ${selectedSet.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="${group}-all">Todos</label>
    </div>`;
    const entries = mapOrSet instanceof Map ? Array.from(mapOrSet.entries()) : Object.entries(mapOrSet || {});
    entries.sort((a, b) => a[1].localeCompare(b[1])).forEach(([val, label]) => {
        const id = `${group}-${cssSafe(val)}`;
        html += `<div class="form-check mb-2 filtro-checkbox-option" data-label="${String(label).toLowerCase()}">
      <input class="form-check-input chk-${group}" type="checkbox" id="${id}" value="${val}" ${selectedSet.has(String(val)) || selectedSet.has(val) ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="${id}">${label}</label>
    </div>`;
    });
    $(containerSel).html(html);
}

function collectVehicleFilterOptions(dataset) {
    const modelos = new Map(), condiciones = new Set(), tipos = new Set(), marcas = new Map(), colores = new Set(), taps = new Set(), clima = new Set(), referencias = new Set();

    (dataset || []).forEach(p => {
        const modelObjs = [
            ...toArray(p.modeloArray),
            ...toArray(p.modelo),
            ...toArray(p.modelo_producto),
            ...toArray(p.modelo_productoo),
            ...toArray(p.model)
        ];
        modelObjs.forEach(m => {
            const key = String(m.id_modelos_autos ?? m.id ?? m.id_modelo ?? m.nombre).trim();
            const label = cleanStr(m.nombre ?? m.name ?? `Modelo ${key}`);
            if (key) modelos.set(key, label);
        });

        const marcaObjs = [
            ...toArray(p.marcaArray),
            ...toArray(p.marca)
        ];
        marcaObjs.forEach(m => {
            const key = String(m.id_marca ?? m.id ?? m.nombre).trim();
            const label = cleanStr(m.nombre ?? `Marca ${key}`);
            if (key) marcas.set(key, label);
        });

        normalizeToNameArray(p.condicionArray ?? p.condicion).forEach(v => condiciones.add(v));
        normalizeToNameArray(p.tipo_autoArray ?? p.tipo_auto).forEach(v => tipos.add(v));
        normalizeToNameArray(p.colorArray ?? p.color).forEach(v => colores.add(v));
        normalizeToNameArray(p.tapiceriaArray ?? p.tapiceria).forEach(v => taps.add(v));
        normalizeToNameArray(p.climatizacionArray ?? p.climatizacion).forEach(v => clima.add(v));
        normalizeToNameArray(p.referencias).forEach(v => referencias.add(v));
    });

    return { modelos, condiciones, tipos, marcas, colores, taps, clima, referencias };
}

function buildVehicleFilters(dataset, groupDatasets = {}) {
    const opcionesModelo = collectVehicleFilterOptions(groupDatasets.modelo || dataset);
    const opcionesCondicion = collectVehicleFilterOptions(groupDatasets.condicion || dataset);
    const opcionesTipoauto = collectVehicleFilterOptions(groupDatasets.tipoauto || dataset);
    const opcionesMarca = collectVehicleFilterOptions(groupDatasets.marcav || dataset);
    const opcionesColor = collectVehicleFilterOptions(groupDatasets.color || dataset);
    const opcionesTapiceria = collectVehicleFilterOptions(groupDatasets.tapiceria || dataset);
    const opcionesClima = collectVehicleFilterOptions(groupDatasets.clima || dataset);
    const opcionesReferencia = collectVehicleFilterOptions(groupDatasets.referencia || dataset);

    paintCheckboxes("#filtro-modelos", opcionesModelo.modelos, "modelo");
    paintCheckboxes("#filtro-condicion", toMap(opcionesCondicion.condiciones), "condicion");
    paintCheckboxes("#filtro-tipo-auto", toMap(opcionesTipoauto.tipos), "tipoauto");
    paintCheckboxes("#filtro-marca-veh", opcionesMarca.marcas, "marcav");
    paintCheckboxes("#filtro-color", toMap(opcionesColor.colores), "color");
    paintCheckboxes("#filtro-tapiceria", toMap(opcionesTapiceria.taps), "tapiceria");
    paintCheckboxes("#filtro-climatizacion", toMap(opcionesClima.clima), "clima");
    paintCheckboxes("#filtro-referencias", toMap(opcionesReferencia.referencias), "referencia");

    const anios = (groupDatasets.anio || dataset).map(p => asNumber(p.anio, NaN)).filter(Number.isFinite);
    const minA = anios.length ? Math.min(...anios) : 1900, maxA = anios.length ? Math.max(...anios) : new Date().getFullYear();
    $("#anioMin").attr({ min: minA, max: maxA }).val(anioMinSel ?? "");
    $("#anioMax").attr({ min: minA, max: maxA }).val(anioMaxSel ?? "");
}

function bindVehicleFiltersUI() {
    $(document).off("change", ".chk-modelo").on("change", ".chk-modelo", function () {
        if ($(this).val() === "__all__") {
            modelosSeleccionados.clear();
            $(".chk-modelo").not(this).prop("checked", false);
            $(this).prop("checked", true);
            refreshDependentFilters();
            renderEmpresas(productosData, 1);
            return;
        }
        const v = String($(this).val());
        this.checked ? modelosSeleccionados.add(v) : modelosSeleccionados.delete(v);
        $("#modelo-all").prop("checked", modelosSeleccionados.size === 0);

        refreshDependentFilters();     // ✅ reduce opciones
        renderEmpresas(productosData, 1);
    });

    $(document).off("change", ".chk-marcav").on("change", ".chk-marcav", function () {
        if ($(this).val() === "__all__") {
            marcaVehSeleccionadas.clear();
            $(".chk-marcav").not(this).prop("checked", false);
            $(this).prop("checked", true);
            refreshDependentFilters();
            renderEmpresas(productosData, 1);
            return;
        }
        const v = String($(this).val());
        this.checked ? marcaVehSeleccionadas.add(v) : marcaVehSeleccionadas.delete(v);
        $("#marcav-all").prop("checked", marcaVehSeleccionadas.size === 0);

        refreshDependentFilters();     // ✅ reduce opciones
        renderEmpresas(productosData, 1);
    });

    $(document).off("change", ".chk-condicion").on("change", ".chk-condicion", function () { if ($(this).val() === "__all__") { condicionSeleccionadas.clear(); $(".chk-condicion").not(this).prop("checked", false); $(this).prop("checked", true); refreshDependentFilters(); renderEmpresas(productosData, 1); return; } const v = $(this).val(); this.checked ? condicionSeleccionadas.add(v) : condicionSeleccionadas.delete(v); $("#condicion-all").prop("checked", condicionSeleccionadas.size === 0); refreshDependentFilters(); renderEmpresas(productosData, 1); });
    $(document).off("change", ".chk-tipoauto").on("change", ".chk-tipoauto", function () { if ($(this).val() === "__all__") { tipoAutoSeleccionados.clear(); $(".chk-tipoauto").not(this).prop("checked", false); $(this).prop("checked", true); refreshDependentFilters(); renderEmpresas(productosData, 1); return; } const v = $(this).val(); this.checked ? tipoAutoSeleccionados.add(v) : tipoAutoSeleccionados.delete(v); $("#tipoauto-all").prop("checked", tipoAutoSeleccionados.size === 0); refreshDependentFilters(); renderEmpresas(productosData, 1); });

    $(document).off("change", ".chk-color").on("change", ".chk-color", function () { if ($(this).val() === "__all__") { colorSeleccionados.clear(); $(".chk-color").not(this).prop("checked", false); $(this).prop("checked", true); refreshDependentFilters(); renderEmpresas(productosData, 1); return; } const v = $(this).val(); this.checked ? colorSeleccionados.add(v) : colorSeleccionados.delete(v); $("#color-all").prop("checked", colorSeleccionados.size === 0); refreshDependentFilters(); renderEmpresas(productosData, 1); });
    $(document).off("change", ".chk-tapiceria").on("change", ".chk-tapiceria", function () { if ($(this).val() === "__all__") { tapiceriaSeleccionados.clear(); $(".chk-tapiceria").not(this).prop("checked", false); $(this).prop("checked", true); refreshDependentFilters(); renderEmpresas(productosData, 1); return; } const v = $(this).val(); this.checked ? tapiceriaSeleccionados.add(v) : tapiceriaSeleccionados.delete(v); $("#tapiceria-all").prop("checked", tapiceriaSeleccionados.size === 0); refreshDependentFilters(); renderEmpresas(productosData, 1); });
    $(document).off("change", ".chk-clima").on("change", ".chk-clima", function () { if ($(this).val() === "__all__") { climatizacionSeleccionados.clear(); $(".chk-clima").not(this).prop("checked", false); $(this).prop("checked", true); refreshDependentFilters(); renderEmpresas(productosData, 1); return; } const v = $(this).val(); this.checked ? climatizacionSeleccionados.add(v) : climatizacionSeleccionados.delete(v); $("#clima-all").prop("checked", climatizacionSeleccionados.size === 0); refreshDependentFilters(); renderEmpresas(productosData, 1); });
    $(document).off("change", ".chk-referencia").on("change", ".chk-referencia", function () { if ($(this).val() === "__all__") { referenciasSeleccionadas.clear(); $(".chk-referencia").not(this).prop("checked", false); $(this).prop("checked", true); refreshDependentFilters(); renderEmpresas(productosData, 1); return; } const v = $(this).val(); this.checked ? referenciasSeleccionadas.add(v) : referenciasSeleccionadas.delete(v); $("#referencia-all").prop("checked", referenciasSeleccionadas.size === 0); refreshDependentFilters(); renderEmpresas(productosData, 1); });
    $(document).off("input", ".filtro-checkbox-search").on("input", ".filtro-checkbox-search", function () {
        const term = normalizarTexto($(this).val());
        const group = $(this).data("group");
        const $container = $(this).closest(".accordion-body");

        $container.find(`.filtro-checkbox-option:has(.chk-${group})`).each(function () {
            const label = normalizarTexto($(this).data("label"));
            const visible = !term || label.includes(term);
            $(this).toggle(visible);
        });
    });
    $("#anioMin,#anioMax").off("input.anio").on("input.anio", function () {
        clearTimeout(anioTimer);

        anioTimer = setTimeout(() => {
            const minRaw = $("#anioMin").val();
            const maxRaw = $("#anioMax").val();

            const min = asNumber(minRaw, NaN);
            const max = asNumber(maxRaw, NaN);

            const currentYear = new Date().getFullYear();

            // Si ambos vacíos, no filtra
            if (!minRaw && !maxRaw) {
                anioMinSel = null;
                anioMaxSel = null;
                refreshDependentFilters();
                renderEmpresas(productosData, 1);
                return;
            }

            // Si "Desde" tiene valor
            if (Number.isFinite(min)) {
                anioMinSel = min;

                // Si "Hasta" está vacío => usar año actual
                if (!maxRaw) {
                    anioMaxSel = currentYear;
                } else {
                    anioMaxSel = Number.isFinite(max) ? max : currentYear;
                }

                refreshDependentFilters();
                renderEmpresas(productosData, 1);
                return;
            }

            // Si "Desde" está vacío pero "Hasta" tiene valor => filtra hasta ese año
            if (Number.isFinite(max)) {
                anioMinSel = null;
                anioMaxSel = max;
                refreshDependentFilters();
                renderEmpresas(productosData, 1);
                return;
            }

            // fallback
            anioMinSel = null;
            anioMaxSel = null;
            refreshDependentFilters();
            renderEmpresas(productosData, 1);

        }, 2000); // ✅ 2 segundos
    });
}

/* =================== RENDER GRID =================== */
function primeraCategoriaEsProducto(prod) {
    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    // Si no hay info de categorías, NO bloquear (asumir producto)
    if (!ids.length || !catObjs.length) return true;

    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    // Si no trae 'tipo', también asumimos producto
    return first?.tipo ? first.tipo === 'producto' : true;
}

function asPlainText(val) {
    const arr = normalizeToNameArray(val);   // ya tienes esta función
    if (arr.length) return arr[0];           // usa el primero
    return cleanStr(val);                    // fallback
}

function scrollToProductsTop() {
    const target = document.querySelector(".shop-product-fillter") || document.querySelector(".product-grid");
    if (!target) return;

    const offset = 120;
    const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({
        top: Math.max(0, top),
        behavior: "smooth"
    });
}


function renderEmpresas(data, page = 1) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    let empresasFiltradas = data.filter(prod => {
        prod._searchScore = getBusquedaVehiculoScore(prod, searchText);
        const matchSearch = searchText.trim() === "" || prod._searchScore > 0;

        const matchPrimeraCatProducto = primeraCategoriaEsProducto(prod);

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        const matchSubHija = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);
        const matchMarcaRadio = (marcaSeleccionada === null) || prodBrand.includes(Number(marcaSeleccionada));
        const matchModeloRadio = (modeloSeleccionado === null) || prodModel.includes(Number(modeloSeleccionado));

        // NUEVOS filtros por nombre

        const condicionDeProd = normalizeToNameArray(prod.condicionArray ?? prod.condicion);
        const tipoAutoDeProd = normalizeToNameArray(prod.tipo_autoArray ?? prod.tipo_auto);

        const marcaIdsProd = getMarcaIds(prod);
        const modeloIdsProd = getModeloIds(prod);

        const matchMarcaCheck = (marcaVehSeleccionadas.size === 0) || marcaIdsProd.some(id => marcaVehSeleccionadas.has(id));
        const matchModelos = (modelosSeleccionados.size === 0) || modeloIdsProd.some(id => modelosSeleccionados.has(id));


        const colorDeProd = normalizeToNameArray(prod.colorArray ?? prod.color);
        const tapDeProd = normalizeToNameArray(prod.tapiceriaArray ?? prod.tapiceria);
        const climaDeProd = normalizeToNameArray(prod.climatizacionArray ?? prod.climatizacion);
        const referenciasDeProd = normalizeToNameArray(prod.referencias);
        const inSet = (arr, set) => (set.size === 0) || arr.some(v => set.has(v));

        const matchCondicion = inSet(condicionDeProd, condicionSeleccionadas);
        const matchTipoAuto = inSet(tipoAutoDeProd, tipoAutoSeleccionados);

        const matchColor = inSet(colorDeProd, colorSeleccionados);
        const matchTapiceria = inSet(tapDeProd, tapiceriaSeleccionados);
        const matchClima = inSet(climaDeProd, climatizacionSeleccionados);
        const matchReferencia = inSet(referenciasDeProd, referenciasSeleccionadas);

        // Precio
        const precio = Number(prod.precio_referencia);
        const matchPrecio = precio >= precioMin && precio <= precioMax;

        // Año
        const anioNum = asNumber(prod.anio, NaN);
        const matchAnio = (!Number.isFinite(anioNum)) || ((anioMinSel === null || anioNum >= anioMinSel) && (anioMaxSel === null || anioNum <= anioMaxSel));

        // Ubicación (soporta JSON/array)
        const selProv = normalizarTexto(provinciaSel.nombre);
        const selCant = normalizarTexto(cantonSel.nombre);

        const provinciasProd = normalizeToNameArray(prod.provincia).map(normalizarTexto);
        const cantonesProd = normalizeToNameArray(prod.canton).map(normalizarTexto);

        const matchProvincia =
            !selProv || (provinciasProd.length === 0 ? true : provinciasProd.includes(selProv));

        const matchCanton =
            !selCant || (cantonesProd.length === 0 ? true : cantonesProd.includes(selCant));
        const matchVehiculoChecks = matchesVehicleFilters(prod);
        return matchSearch && matchPrimeraCatProducto && matchCat && matchSubHija
            && matchMarcaRadio && matchModeloRadio
            && matchVehiculoChecks;
    });

    if (searchText) {
        empresasFiltradas.sort((a, b) => (b._searchScore || 0) - (a._searchScore || 0));
    }

    // Ordenar
    if (sortOption === "mayor") empresasFiltradas.sort((a, b) => (Number(b.precio_referencia) || 0) - (Number(a.precio_referencia) || 0));
    else if (sortOption === "menor") empresasFiltradas.sort((a, b) => (Number(a.precio_referencia) || 0) - (Number(b.precio_referencia) || 0));
    else if (sortOption === "km_mayor") empresasFiltradas.sort((a, b) => asNumber(b.kilometraje) - asNumber(a.kilometraje));
    else if (sortOption === "km_menor") empresasFiltradas.sort((a, b) => asNumber(a.kilometraje) - asNumber(b.kilometraje));

    // Paginación
    const start = (page - 1) * itemsPerPage, end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    // Render
    let html = "";
    empresasPagina.forEach(p => {
        const tieneDesc = parseFloat(p.descuento) > 0;
        const precioDesc = p.precio_referencia - (p.precio_referencia * p.descuento / 100);
        const negociable = Number(p.negociable) === 1;
        const tipoAutoTxt = normalizeToNameArray(p.tipo_autoArray ?? p.tipo_auto)[0] || '';
        const marcaTxt = [...toArray(p.marcaArray), ...toArray(p.marca)][0]?.nombre ?? '';
        const modeloTxt = [...toArray(p.modeloArray), ...toArray(p.modelo), ...toArray(p.modelo_producto), ...toArray(p.modelo_productoo)][0]?.nombre ?? '';
        const anioTxt = cleanStr(p.anio) || '';
        const provinciaTxt = asPlainText(p.provincia);
        const cantonTxt = asPlainText(p.canton);
        const ciudadTxt = [provinciaTxt, cantonTxt].filter(Boolean).join(" - ");
        const kmTxt = `${asNumber(p.kilometraje, 0).toLocaleString()} Kms`;

        const tieneDescuento = parseFloat(p.descuento) > 0;
        const precioDescuento = p.precio_referencia - (p.precio_referencia * p.descuento / 100);


        html += `
            <div class="col-md-4 col-lg-3 col-sm-4 col-12 mb-2 d-flex">
                <div class="product-cart-wrap w-100 d-flex flex-column">
                    <div class="product-img-action-wrap text-center">
                        <div class="product-img product-img-zoom">
                            <a href="detalle_vehiculo.php?q=${p.id_vehiculo}" target="_blank" rel="noopener noreferrer">
                                <img class="default-img img-fluid mb-1"
                                    src="admin/${p.img_frontal}"  alt="${modeloTxt}"
                                    onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" style="object-fit: contain; width: 100%; height: 200px">
                                <img class="hover-img img-fluid"
                                    src="admin/${p.img_posterior}" alt="${modeloTxt}" 
                                    onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"  style="object-fit: contain; width: 100%; height: 200px">
                            </a>
                        </div>
                        ${tieneDescuento ? `
                        <div class="product-badges product-badges-position product-badges-mrg">
                            <span class="best">-${parseInt(p.descuento)}%</span>
                        </div>` : ''}
                    </div>
                    <div class="product-content-wrap d-flex flex-column flex-grow-1 px-2 pb-2">
                        <div class="brand">${marcaTxt || '&nbsp;'}</div>
<a href="detalle_vehiculo.php?q=${p.id_vehiculo}" target="_blank" rel="noopener noreferrer"><h3 class="model" style="font-weight:normal;">${modeloTxt || '&nbsp;'}</h3></a>
                        <div class="year">${anioTxt || '&nbsp;'}</div>
                        <hr class="my-1">
                        <div class="meta-line">
                            <span><i class="fi-rs-marker"></i> ${ciudadTxt || '—'}</span>
                        </div>
                        <div class="badge-type">
                            <span class="meta-dot"></span>
                            <i class="fi-rs-dashboard"></i> ${kmTxt}
                        </div>
                        <div class="mt-auto">
                            <div class="product-price text-center">
          
<span>${formatPrecioSuperscript(tieneDescuento ? precioDescuento : p.precio_referencia)}</span>
${tieneDescuento ? `<span class="old-price">${formatPrecioSuperscript(p.precio_referencia)}</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    });
    // $("#totalProductosGeneral").text(empresasFiltradas.length);


    $("#totalProductosGeneral").text(formatearCantidadProductos(empresasFiltradas.length));

    $(".product-grid").html(html || `<div class="col-12 text-center"><p class="text-muted">No hay vehículos que coincidan con los filtros.</p></div>`);
    renderPaginacion(empresasFiltradas.length, page);
}

function formatearCantidadProductos(total) {
    if (total <= 0) return "0";
    if (total < 10) return "1";        // menos de 10, muestra exacto
    const base = Math.floor(total / 10) * 10;
    if (total === base) return `más de ${base - 10}`; // exactamente 30 → "más de 20"
    return `más de ${base}`;                          // 37 → "más de 30"
}

function renderPaginacion(totalItems, currentPage) {
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

    if (totalItems <= 0 || totalPages <= 1) {
        $(".pagination").html("");
        return;
    }

    const safePage = Math.min(Math.max(1, currentPage), totalPages);
    const pages = new Set([1, totalPages, safePage - 1, safePage, safePage + 1]);
    const orderedPages = Array.from(pages)
        .filter(page => page >= 1 && page <= totalPages)
        .sort((a, b) => a - b);

    let pagHtml = '';
    let previousPage = 0;

    orderedPages.forEach(page => {
        if (previousPage && page - previousPage > 1) {
            pagHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }

        pagHtml += `<li class="page-item ${page === safePage ? 'active' : ''}"><a class="page-link" href="#" data-page="${page}" aria-label="Ir a la página ${page}">${page}</a></li>`;
        previousPage = page;
    });

    $(".pagination").html(`
    <li class="page-item ${safePage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="1" aria-label="Primera página"><i class="fi-rs-angle-double-small-left"></i></a></li>
    <li class="page-item ${safePage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${safePage - 1}" aria-label="Página anterior"><i class="fi-rs-arrow-small-left"></i></a></li>
    ${pagHtml}
    <li class="page-item ${safePage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${safePage + 1}" aria-label="Página siguiente"><i class="fi-rs-arrow-small-right"></i></a></li>
    <li class="page-item ${safePage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${totalPages}" aria-label="Última página"><i class="fi-rs-angle-double-small-right"></i></a></li>
  `);
}

/* =================== REFRESH POR UBICACIÓN =================== */
function refreshFiltersForCurrentLocation() {
    const locData = filtrarPorUbicacionDataset(productosData);
    syncPriceSlider(locData);
    buildMarcasYModelos(locData);
    // catsIndex = buildCatsAndSubcatsFromProductos(locData); categoriasFiltradas = catsIndex.categoriasLista;
    // renderCheckboxCategorias(categoriasFiltradas);
    // const subcats = buildSubcatsForSelected(subcategoriasSeleccionadas.map(Number)); renderCheckboxSubcategorias(subcats); $("#subcats-box").toggle(subcats.length > 0);
    // reconstruye filtros nuevos según ubicación
    buildVehicleFilters(locData); bindVehicleFiltersUI();
    refreshDependentFilters();
}

function toArray(val) {
    if (!val) return [];
    if (Array.isArray(val)) return val;
    if (typeof val === 'object') return [val];
    if (typeof val === 'string') {
        try { const j = JSON.parse(val); return Array.isArray(j) ? j : (j ? [j] : []); } catch (_) { return []; }
    }
    return [];
}


/* =================== MISCS =================== */
// function recheckCategoriasSeleccionadas() { const setSel = new Set(subcategoriasSeleccionadas.map(Number)); setSel.forEach(id => { const el = document.getElementById(`categoria-${id}`); if (el) el.checked = true; }); }
// function recheckSubcategoriasSeleccionadas() { const setSel = new Set(subcategoriasHijasSeleccionadas.map(Number)); setSel.forEach(id => { const el = document.getElementById(`subcat-${id}`); if (el) el.checked = true; }); }
