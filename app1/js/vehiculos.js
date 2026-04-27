/* =================== ESTADO =================== */
let itemsPerPage = 20, currentPage = 1, productosData = [];
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
let anioMinSel = null, anioMaxSel = null;
let isRebuildingFilters = false;
let anioTimer = null;
let activeFilterSection = "ubicacion-orden";

function setActiveFilterSection(sectionKey) {
    activeFilterSection = sectionKey;
    $(".filter-nav-item").removeClass("is-active");
    $(`.filter-nav-item[data-filter-target="${sectionKey}"]`).addClass("is-active");
    $(".filter-detail-panel").removeClass("is-active");
    $(`.filter-detail-panel[data-filter-panel="${sectionKey}"]`).addClass("is-active");
}

function openFilterPanel() {
    setActiveFilterSection(activeFilterSection || "ubicacion-orden");
    $("#panelFiltros").addClass("is-open").attr("aria-hidden", "false");
    $("#filtersOverlay").addClass("is-open");
    $("#openFilterPanel").attr("aria-expanded", "true");
    $("body").css("overflow", "hidden");
}

function closeFilterPanel() {
    $("#panelFiltros").removeClass("is-open").attr("aria-hidden", "true");
    $("#filtersOverlay").removeClass("is-open");
    $("#openFilterPanel").attr("aria-expanded", "false");
    $("body").css("overflow", "");
}

function updateResultsButtonCount(total = 0) {
    $("#minimizeFilterPanel").text(`Ver ${total} resultados`);
}

function recheckSelected(containerSel, set, group) {
    // marca/modelo guardan IDs en set
    $(`${containerSel} .chk-${group}`).each(function () {
        this.checked = set.has(String($(this).val()));
    });
}

function refreshDependentFilters() {
    if (isRebuildingFilters) return;
    isRebuildingFilters = true;

    // dataset base (respeta ubicación)
    let base = filtrarPorUbicacionDataset(productosData);

    // aplica SOLO marca/modelo para recalcular opciones
    base = base.filter(p => {
        const marcaIds = getMarcaIds(p);
        const modeloIds = getModeloIds(p);

        const okMarca = (marcaVehSeleccionadas.size === 0) || marcaIds.some(id => marcaVehSeleccionadas.has(id));
        const okModelo = (modelosSeleccionados.size === 0) || modeloIds.some(id => modelosSeleccionados.has(id));
        return okMarca && okModelo;
    });

    // reconstruye filtros según base
    buildVehicleFilters(base);
    bindVehicleFiltersUI();

    // re-check de lo que ya estaba seleccionado
    recheckSelected("#filtro-marca-veh", marcaVehSeleccionadas, "marcav");
    recheckSelected("#filtro-modelos", modelosSeleccionados, "modelo");
    recheckSelected("#filtro-condicion", condicionSeleccionadas, "condicion");
    recheckSelected("#filtro-tipo-auto", tipoAutoSeleccionados, "tipoauto");
    recheckSelected("#filtro-color", colorSeleccionados, "color");
    recheckSelected("#filtro-tapiceria", tapiceriaSeleccionados, "tapiceria");
    recheckSelected("#filtro-climatizacion", climatizacionSeleccionados, "clima");

    isRebuildingFilters = false;
}

/* =================== CARGA INICIAL =================== */
fetch('../provincia_canton_parroquia.json').then(r => r.json()).then(d => { datosEcuador = d; cargarProvincias(); });

$(document).ready(function () {
    $("#breadcrumb")?.append(`<a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a><span></span> Lista de Vehículos`);
    setActiveFilterSection("ubicacion-orden");
    updateLocationButtonLabel();
    updateFilterActiveCount();
    updateResultsButtonCount(0);

    $.get("../api/v1/fulmuv/vehiculos/All", {}, function (returnedData) {
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

    $("#openFilterPanel").on("click", openFilterPanel);
    $("#closeFilterPanel, #minimizeFilterPanel, #filtersOverlay").on("click", closeFilterPanel);
    $(".filter-nav-item").on("click", function () {
        const sectionKey = $(this).data("filter-target");
        if (sectionKey) setActiveFilterSection(sectionKey);
    });

    $("#selectOrderPanel").on("change", function () {
        sortOption = $(this).val();
        updateFilterActiveCount();
        renderEmpresas(productosData, 1);
    });

    $("#selectShowPanel").on("change", function () {
        const value = $(this).val();
        itemsPerPage = value === "all" ? (productosData.length || 1) : parseInt(value, 10);
        currentPage = 1;
        renderEmpresas(productosData, currentPage);
    });

    $("#clearFiltersPanel").on("click", function () {
        searchText = "";
        sortOption = "todos";
        subcategoriasSeleccionadas = [];
        subcategoriasHijas = [];
        subcategoriasHijasSeleccionadas = [];
        marcaSeleccionada = null;
        modeloSeleccionado = null;
        modelosSeleccionados.clear();
        condicionSeleccionadas.clear();
        tipoAutoSeleccionados.clear();
        marcaVehSeleccionadas.clear();
        colorSeleccionados.clear();
        tapiceriaSeleccionados.clear();
        climatizacionSeleccionados.clear();
        anioMinSel = null;
        anioMaxSel = null;
        provinciaSel = { id: null, nombre: null };
        cantonSel = { id: null, nombre: null };

        $("#searchInput").val("");
        $("#selectOrderPanel").val("todos");
        $("#selectShowPanel").val("20");
        $("#anioMin, #anioMax").val("");
        $("input[type='checkbox']").prop("checked", false);
        $("input[type='radio']").prop("checked", false);
        $("#selectProvincia").val("");
        resetSelectCanton();

        const slider = document.getElementById("slider-range");
        if (slider?.noUiSlider && productosData.length) {
            const maxPrecio = Math.max(...productosData.map(p => Number(p.precio_referencia) || 0));
            slider.noUiSlider.set([0, maxPrecio]);
        }

        updateLocationButtonLabel();
        updateFilterActiveCount();
        refreshFiltersForCurrentLocation();
        renderEmpresas(productosData, 1);
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $("#panelFiltros").hasClass("is-open")) {
            closeFilterPanel();
        }
    });
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
$("#searchInput").on("input", function () { searchText = $(this).val().trim(); updateFilterActiveCount(); renderEmpresas(productosData, 1); });

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
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) { currentPage = page; renderEmpresas(productosData, currentPage); }
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

/* =================== SLIDER PRECIO =================== */
function inicializarSlider(maxPrecio) {
    const el = document.getElementById("slider-range"); if (!el) return;
    if (el.noUiSlider) { try { el.noUiSlider.destroy(); } catch (_) { } }
    if (!Number.isFinite(maxPrecio) || maxPrecio <= 0) {
        el.innerHTML = ""; $("#slider-range-value1").text("$0"); $("#slider-range-value2").text("$0");
        precioMin = 0; precioMax = Infinity; return;
    }
    noUiSlider.create(el, { start: [0, maxPrecio], step: 1, range: { min: 0, max: maxPrecio }, format: moneyFormat, connect: true });
    el.noUiSlider.on("update", function (values) {
        $("#slider-range-value1").text(values[0]); $("#slider-range-value2").text(values[1]);
        precioMin = parseFloat(moneyFormat.from(values[0])); precioMax = parseFloat(moneyFormat.from(values[1]));
        updateFilterActiveCount();
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
function updateLocationButtonLabel() {
    $("#btnUbicacionPanel span").html(`<i class="fi-rs-marker me-1"></i>${labelUbicacion()}`);
}
function updateFilterActiveCount() {
    const locationCount = (provinciaSel.id ? 1 : 0) + (cantonSel.id ? 1 : 0) + (sortOption !== "todos" ? 1 : 0);
    const specsCount =
        marcaVehSeleccionadas.size +
        modelosSeleccionados.size +
        condicionSeleccionadas.size +
        tipoAutoSeleccionados.size +
        colorSeleccionados.size +
        tapiceriaSeleccionados.size +
        climatizacionSeleccionados.size +
        (anioMinSel !== null ? 1 : 0) +
        (anioMaxSel !== null ? 1 : 0) +
        (precioMin > 0 || precioMax !== Infinity ? 1 : 0);

    $("#filterActiveCount").text(locationCount + specsCount + (searchText ? 1 : 0));
    $("#filterGroupCountLocation").text(locationCount);
    $("#filterGroupCountSpecs").text(specsCount);
}
function actualizarUIUbicacionPersistir() {
    updateLocationButtonLabel();
    document.getElementById('provincia_id_hidden').value = provinciaSel.id || '';
    document.getElementById('provincia_nombre_hidden').value = provinciaSel.nombre || '';
    document.getElementById('canton_id_hidden').value = cantonSel.id || '';
    document.getElementById('canton_nombre_hidden').value = cantonSel.nombre || '';
    localStorage.setItem('ubicacionSeleccionada', JSON.stringify({ provincia: provinciaSel, canton: cantonSel }));
    updateFilterActiveCount();
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
        $.post("../api/v1/fulmuv/productos/idCategoria", { id_categoria: idsTodas }, function (rd) {
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
    updateFilterActiveCount();
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

function paintCheckboxes(containerSel, mapOrSet, group) {
    let html = ''; const entries = mapOrSet instanceof Map ? Array.from(mapOrSet.entries()) : Object.entries(mapOrSet || {});
    entries.sort((a, b) => a[1].localeCompare(b[1])).forEach(([val, label]) => {
        const id = `${group}-${cssSafe(val)}`;
        html += `<div class="form-check mb-2">
      <input class="form-check-input chk-${group}" type="checkbox" id="${id}" value="${val}">
      <label class="form-check-label fw-normal" for="${id}">${label}</label>
    </div>`;
    });
    $(containerSel).html(html);
}

function buildVehicleFilters(dataset) {
    const modelos = new Map(), condiciones = new Set(), tipos = new Set(), marcas = new Map(), colores = new Set(), taps = new Set(), clima = new Set();

    (dataset || []).forEach(p => {
        const modelObjs = [
            ...toArray(p.modeloArray),
            ...toArray(p.modelo),                 // por compatibilidad
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
    });

    paintCheckboxes("#filtro-modelos", modelos, "modelo");
    paintCheckboxes("#filtro-condicion", toMap(condiciones), "condicion");
    paintCheckboxes("#filtro-tipo-auto", toMap(tipos), "tipoauto");
    paintCheckboxes("#filtro-marca-veh", marcas, "marcav");
    paintCheckboxes("#filtro-color", toMap(colores), "color");
    paintCheckboxes("#filtro-tapiceria", toMap(taps), "tapiceria");
    paintCheckboxes("#filtro-climatizacion", toMap(clima), "clima");

    const anios = (dataset || []).map(p => asNumber(p.anio, NaN)).filter(Number.isFinite);
    const minA = anios.length ? Math.min(...anios) : 1900, maxA = anios.length ? Math.max(...anios) : new Date().getFullYear();
    $("#anioMin").attr({ min: minA, max: maxA }).val("");
    $("#anioMax").attr({ min: minA, max: maxA }).val("");
}

function bindVehicleFiltersUI() {
    $(document).off("change", ".chk-modelo").on("change", ".chk-modelo", function () {
        const v = String($(this).val());
        this.checked ? modelosSeleccionados.add(v) : modelosSeleccionados.delete(v);

        refreshDependentFilters();     // ✅ reduce opciones
        updateFilterActiveCount();
        renderEmpresas(productosData, 1);
    });

    $(document).off("change", ".chk-marcav").on("change", ".chk-marcav", function () {
        const v = String($(this).val());
        this.checked ? marcaVehSeleccionadas.add(v) : marcaVehSeleccionadas.delete(v);

        refreshDependentFilters();     // ✅ reduce opciones
        updateFilterActiveCount();
        renderEmpresas(productosData, 1);
    });

    $(document).off("change", ".chk-condicion").on("change", ".chk-condicion", function () { const v = $(this).val(); this.checked ? condicionSeleccionadas.add(v) : condicionSeleccionadas.delete(v); updateFilterActiveCount(); renderEmpresas(productosData, 1); });
    $(document).off("change", ".chk-tipoauto").on("change", ".chk-tipoauto", function () { const v = $(this).val(); this.checked ? tipoAutoSeleccionados.add(v) : tipoAutoSeleccionados.delete(v); updateFilterActiveCount(); renderEmpresas(productosData, 1); });

    $(document).off("change", ".chk-color").on("change", ".chk-color", function () { const v = $(this).val(); this.checked ? colorSeleccionados.add(v) : colorSeleccionados.delete(v); updateFilterActiveCount(); renderEmpresas(productosData, 1); });
    $(document).off("change", ".chk-tapiceria").on("change", ".chk-tapiceria", function () { const v = $(this).val(); this.checked ? tapiceriaSeleccionados.add(v) : tapiceriaSeleccionados.delete(v); updateFilterActiveCount(); renderEmpresas(productosData, 1); });
    $(document).off("change", ".chk-clima").on("change", ".chk-clima", function () { const v = $(this).val(); this.checked ? climatizacionSeleccionados.add(v) : climatizacionSeleccionados.delete(v); updateFilterActiveCount(); renderEmpresas(productosData, 1); });
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
                updateFilterActiveCount();
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

                updateFilterActiveCount();
                renderEmpresas(productosData, 1);
                return;
            }

            // Si "Desde" está vacío pero "Hasta" tiene valor => filtra hasta ese año
            if (Number.isFinite(max)) {
                anioMinSel = null;
                anioMaxSel = max;
                updateFilterActiveCount();
                renderEmpresas(productosData, 1);
                return;
            }

            // fallback
            anioMinSel = null;
            anioMaxSel = null;
            updateFilterActiveCount();
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

function renderEmpresas(data, page = 1) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    let empresasFiltradas = data.filter(prod => {
        const brandNames = [...toArray(prod.marcaArray), ...toArray(prod.marca)]
            .map(m => cleanStr(m?.nombre ?? m?.name ?? '')).filter(Boolean);

        const modelNames = [...toArray(prod.modeloArray), ...toArray(prod.modelo), ...toArray(prod.modelo_producto), ...toArray(prod.modelo_productoo)]
            .map(m => cleanStr(m?.nombre ?? m?.name ?? '')).filter(Boolean);

        const searchTerms = normalizarTexto(searchText).split(/\s+/).filter(Boolean);
        const searchHay = searchTerms.length > 0; 

        const searchHaystack = [
            normalizarTexto(prod.nombre || ''),
            ...brandNames.map(normalizarTexto),
            ...modelNames.map(normalizarTexto),
            String(prod.id_producto || '')
        ].join(' ');

        const matchSearch = !searchHay || searchTerms.every(t => searchHaystack.includes(t));
        const matchPrimeraCatProducto = primeraCategoriaEsProducto(prod);

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        const matchSubHija = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);
        const matchMarcaRadio = (marcaSeleccionada === null) || prodBrand.includes(Number(marcaSeleccionada));
        const matchModeloRadio = (modeloSeleccionado === null) || prodModel.includes(Number(modeloSeleccionado));

        const condicionDeProd = normalizeToNameArray(prod.condicionArray ?? prod.condicion);
        const tipoAutoDeProd = normalizeToNameArray(prod.tipo_autoArray ?? prod.tipo_auto);

        const marcaIdsProd = getMarcaIds(prod);
        const modeloIdsProd = getModeloIds(prod);

        const matchMarcaCheck = (marcaVehSeleccionadas.size === 0) || marcaIdsProd.some(id => marcaVehSeleccionadas.has(id));
        const matchModelos = (modelosSeleccionados.size === 0) || modeloIdsProd.some(id => modelosSeleccionados.has(id));

        const colorDeProd = normalizeToNameArray(prod.colorArray ?? prod.color);
        const tapDeProd = normalizeToNameArray(prod.tapiceriaArray ?? prod.tapiceria);
        const climaDeProd = normalizeToNameArray(prod.climatizacionArray ?? prod.climatizacion);

        const inSet = (arr, set) => (set.size === 0) || arr.some(v => set.has(v));

        const matchCondicion = inSet(condicionDeProd, condicionSeleccionadas);
        const matchTipoAuto = inSet(tipoAutoDeProd, tipoAutoSeleccionados);
        const matchColor = inSet(colorDeProd, colorSeleccionados);
        const matchTapiceria = inSet(tapDeProd, tapiceriaSeleccionados);
        const matchClima = inSet(climaDeProd, climatizacionSeleccionados);

        const precio = Number(prod.precio_referencia) || 0;
        const matchPrecio = precio >= precioMin && precio <= precioMax;

        const anioNum = asNumber(prod.anio, NaN);
        const matchAnio = (!Number.isFinite(anioNum)) || (
            (anioMinSel === null || anioNum >= anioMinSel) &&
            (anioMaxSel === null || anioNum <= anioMaxSel)
        );

        const selProv = normalizarTexto(provinciaSel.nombre);
        const selCant = normalizarTexto(cantonSel.nombre);

        const provinciasProd = normalizeToNameArray(prod.provincia).map(normalizarTexto);
        const cantonesProd = normalizeToNameArray(prod.canton).map(normalizarTexto);

        const matchProvincia = !selProv || (provinciasProd.length === 0 ? true : provinciasProd.includes(selProv));
        const matchCanton = !selCant || (cantonesProd.length === 0 ? true : cantonesProd.includes(selCant));

        return matchSearch && matchPrimeraCatProducto && matchCat && matchSubHija
            && matchMarcaRadio && matchModeloRadio
            && matchModelos && matchCondicion && matchTipoAuto && matchMarcaCheck
            && matchColor && matchTapiceria && matchClima
            && matchPrecio && matchAnio
            && matchProvincia && matchCanton;
    });

    if (sortOption === "mayor") empresasFiltradas.sort((a, b) => (Number(b.precio_referencia) || 0) - (Number(a.precio_referencia) || 0));
    else if (sortOption === "menor") empresasFiltradas.sort((a, b) => (Number(a.precio_referencia) || 0) - (Number(b.precio_referencia) || 0));
    else if (sortOption === "km_mayor") empresasFiltradas.sort((a, b) => asNumber(b.kilometraje) - asNumber(a.kilometraje));
    else if (sortOption === "km_menor") empresasFiltradas.sort((a, b) => asNumber(a.kilometraje) - asNumber(b.kilometraje));

    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    let html = "";

    empresasPagina.forEach(p => {
        const tieneDescuento = parseFloat(p.descuento) > 0;
        const precioDescuento = Number(p.precio_referencia) - (Number(p.precio_referencia) * Number(p.descuento) / 100);

        const marcaTxt = capitalizarPrimeraLetra([...toArray(p.marcaArray), ...toArray(p.marca)][0]?.nombre ?? '');
        const modeloTxt = capitalizarPrimeraLetra([...toArray(p.modeloArray), ...toArray(p.modelo), ...toArray(p.modelo_producto), ...toArray(p.modelo_productoo)][0]?.nombre ?? '');
        const anioTxt = cleanStr(p.anio) || '';
        const provinciaTxt = capitalizarPrimeraLetra(asPlainText(p.provincia));
        const cantonTxt = capitalizarPrimeraLetra(asPlainText(p.canton));
        const ciudadTxt = [provinciaTxt, cantonTxt].filter(Boolean).join(" - ");
        const kmTxt = `${asNumber(p.kilometraje, 0).toLocaleString()} Kms`;

        html += `
            <div class="vehicle-card-modern">
                <div class="vehicle-media-modern" onclick="return redirigirVehiculoDetalle(${p.id_vehiculo});">
                    <img class="vehicle-main-img"
                        src="../admin/${p.img_frontal}"
                        alt="${modeloTxt}"
                        onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">

                    ${tieneDescuento ? `<span class="vehicle-discount-badge">-${parseInt(p.descuento)}%</span>` : ''}
                </div>

                <div class="vehicle-info-modern">
                    <div class="vehicle-brand-modern">${marcaTxt || '&nbsp;'}</div>
                    <h3 class="vehicle-title-modern">${modeloTxt || '&nbsp;'}</h3>

                    <div class="vehicle-meta-modern">
                        <span><strong>Año:</strong> ${anioTxt || '—'}</span>
                        <span><i class="fi-rs-marker"></i> ${ciudadTxt || '—'}</span>
                        <span><i class="fi-rs-dashboard"></i> ${kmTxt}</span>
                    </div>

                    <div class="vehicle-price-modern">
                        <span class="vehicle-price-current">
                            ${formatoMoneda.format(tieneDescuento ? precioDescuento : Number(p.precio_referencia || 0))}
                        </span>
                        ${tieneDescuento ? `<span class="vehicle-price-old">${formatoMoneda.format(Number(p.precio_referencia || 0))}</span>` : ''}
                    </div>
                </div>
            </div>`;
    });

    $("#totalProductosGeneral").text(empresasFiltradas.length);
    $("#countVendedores").text(`Encontramos ${empresasFiltradas.length} resultados`);
    updateResultsButtonCount(empresasFiltradas.length);

    $("#listaVehiculosContainer").html(
        html || `
        <div class="empty-state-modern">
            <div class="empty-state-card">
                <div class="empty-state-icon">
                    <i class="fi-rs-search"></i>
                </div>
                <h4 class="empty-state-title">No hay vehículos disponibles</h4>
                <p class="empty-state-text">No encontramos vehículos que coincidan con los filtros aplicados.</p>
            </div>
        </div>`
    );

    renderPaginacion(empresasFiltradas.length, page);
}

function renderPaginacion(totalItems, currentPage) {
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1; let pagHtml = '';
    for (let i = 1; i <= totalPages; i++) { pagHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`; }
    $(".pagination").html(`
    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}"><i class="fi-rs-arrow-small-left"></i></a></li>
    ${pagHtml}
    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}"><i class="fi-rs-arrow-small-right"></i></a></li>
  `);
}

/* =================== REFRESH POR UBICACIÓN =================== */
function refreshFiltersForCurrentLocation() {
    const locData = filtrarPorUbicacionDataset(productosData);
    const precios = (locData || []).map(p => Number(p.precio_referencia)).filter(n => Number.isFinite(n) && n > 0);
    const maxPrecio = precios.length ? Math.max(...precios) : 0; inicializarSlider(maxPrecio);
    buildMarcasYModelos(locData);
    // catsIndex = buildCatsAndSubcatsFromProductos(locData); categoriasFiltradas = catsIndex.categoriasLista;
    // renderCheckboxCategorias(categoriasFiltradas);
    // const subcats = buildSubcatsForSelected(subcategoriasSeleccionadas.map(Number)); renderCheckboxSubcategorias(subcats); $("#subcats-box").toggle(subcats.length > 0);
    // reconstruye filtros nuevos según ubicación
    buildVehicleFilters(locData); bindVehicleFiltersUI();
    updateFilterActiveCount();
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
