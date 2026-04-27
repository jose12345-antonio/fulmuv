/* ====================== Estado global ====================== */
let itemsPerPage = 20;
let currentPage = 1;
let productosData = [];
let sliderMaxActual = null;
let isUpdatingFromSlider = false;
let sliderRefreshTimer = null;
let sortOption = "todos"; // "mayor", "menor", "todos"
let searchText = "";

// Categorías (en tu código: subcategoriasSeleccionadas = categorías seleccionadas)
let subcategoriasSeleccionadas = [];              // IDs de CATEGORÍAS
let subcategoriasHijas = [];                      // subcategorías disponibles para las categorías seleccionadas
let subcategoriasHijasSeleccionadas = [];         // IDs de SUBCATEGORÍAS seleccionadas

// ✅ Marca/Modelo como CHECKBOX
let marcasSeleccionadas = new Set();   // ids string
let modelosSeleccionados = new Set();  // ids string
let marcasUnicas = [];
let modelosUnicos = [];
let filtroTextoMarca = "";
let filtroTextoModelo = "";
let filtroTextoCategoria = "";
let filtroTextoSubcategoria = "";

let precioMin = 0;
let precioMax = Infinity;

let categoriasFiltradas = []; // lista de categorías disponibles
let catsIndex = null;         // índice de categorías/subcats basado en data cargada (o por ubicación)
let datosEcuador = {};

// Tipo (si quieres que solo muestre "servicio" o "producto")
const tipoFiltro = 'servicio'; // 'producto' | 'servicio' | null

// Ubicación
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };

// ✅ Evitar redeclaraciones
window.formatoMoneda = window.formatoMoneda || new Intl.NumberFormat('es-EC', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
});
window.moneyFormat = window.moneyFormat || wNumb({
    decimals: 0,
    thousand: ",",
    prefix: "$"
});

/* ====================== Init Ecuador JSON ====================== */
fetch('provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

/* ====================== Ready ====================== */
$(document).ready(function () {
    actualizarIconoCarrito();

    $("#breadcrumb").append(`
    <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
    <span></span> Lista de Servicios
  `);

    // ✅ 1 sola carga (base)
    $.get("api/v1/fulmuv/serviciosProductos/All", function (returnedData) {
        if (returnedData && returnedData.error === false) {
            productosData = returnedData.data || [];

            // Construye índice base de categorías/subcats a partir de dataset (luego se reconstruye por ubicación)
            refreshAllUIFromCurrentState(true);
        }
    }, 'json');
});

/* ====================== Helpers Core ====================== */

// Normaliza texto (sin tildes, minúsculas)
function normalizarTexto(s) {
    return (s || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase();
}

function capitalizarPrimeraLetra(str) {
    return window.fulmuvTitleCase ? window.fulmuvTitleCase(str) : ((str || '').toString());
}

function getNombreServicioVisible(prod) {
    return capitalizarPrimeraLetra(prod?.nombre || prod?.titulo_producto || "");
}

// Convierte "['1','2']" | [1,2] | "1,2" -> [1,2]
function parseIdsArray(x) {
    if (Array.isArray(x)) return x.map(n => Number(n)).filter(Boolean);
    if (typeof x === 'string') {
        try {
            const j = JSON.parse(x);
            if (Array.isArray(j)) return j.map(n => Number(n)).filter(Boolean);
        } catch (_) { }
        return x.split(/[,\s]+/).map(Number).filter(Boolean);
    }
    if (x == null) return [];
    return [Number(x)].filter(Boolean);
}

// true si hay intersección entre array de ids y Set<number>
function hasIntersection(idsArr, idsSet) {
    for (const id of idsArr) if (idsSet.has(Number(id))) return true;
    return false;
}

function toggleFilterBlock(contentSelector, hasItems) {
    const $wrap = $(contentSelector).closest('.accordion-item');
    if ($wrap.length) $wrap.toggle(!!hasItems);
}

function renderFilterSearchInput(tipo, placeholder, value) {
    return `
    <div class="mb-3">
      <input type="text" class="form-control form-control-sm filter-option-search"
        data-filter-type="${tipo}" placeholder="${placeholder}"
        value="${String(value || '').replace(/"/g, '&quot;')}">
    </div>`;
}

function escapeHtmlAttribute(value) {
    return String(value || "").replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function matchesMixedSearch(candidate, query) {
    const normalizedCandidate = normalizarTexto(candidate);
    const normalizedQuery = normalizarTexto(query);
    if (!normalizedQuery) return true;
    const terms = normalizedQuery.split(/\s+/).filter(Boolean);
    return terms.every(term => normalizedCandidate.includes(term));
}

function applyFilterOptionSearch(tipo, valor) {
    const targetMap = {
        marca: "#filtro-marca",
        modelo: "#filtro-modelo",
        categoria: "#filtro-categorias",
        subcategoria: "#filtro-sub-categorias"
    };
    const container = $(targetMap[tipo] || "");
    if (!container.length) return;
    const rows = container.find(".filter-option-row");
    let visibles = 0;
    rows.each(function () {
        const candidate = $(this).attr("data-filter-search-text") || "";
        const match = matchesMixedSearch(candidate, valor);
        $(this).toggle(match);
        if (match) visibles += 1;
    });
    const emptyLabel = container.find(".filter-option-empty");
    if (emptyLabel.length) emptyLabel.toggle(visibles === 0);
    if (tipo === "categoria" || tipo === "subcategoria") {
        const moreButton = container.find(tipo === "categoria" ? ".more_categories-cat" : ".more_categories-sub");
        const hiddenBlock = container.find(tipo === "categoria" ? ".more_slide_open-cat" : ".more_slide_open-sub");
        if (moreButton.length) moreButton.toggle(visibles > 10);
        if (hiddenBlock.length && visibles <= 10) {
            hiddenBlock.hide();
            moreButton.removeClass("show");
        }
    }
}

// Comprueba tipo en primera categoría (si viene `categorias` con `{id,tipo}`)
function categoriaInicialCumpleTipo(prod) {
    if (tipoFiltro == null) return true;

    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);

    if (!ids.length) return true; // no bloquees si faltan ids
    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return first ? (first.tipo === tipoFiltro) : true;
}

// match búsqueda (mejorado)
function matchBusquedaProducto(prod, text) {
    const textoBuscar = normalizarTexto(text);
    if (!textoBuscar) return true;
    return getBusquedaProductoScore(prod, text) > 0;
}

function getBusquedaProductoScore(prod, text) {
    const textoBuscar = normalizarTexto(text);
    if (!textoBuscar) return 0;

    const base = [
        prod.nombre,
        prod.titulo_producto,
        prod.tags,
        prod.anio,
        prod.provincia,
        prod.canton,
        JSON.stringify(prod.marca || ""),
        JSON.stringify(prod.modelo || ""),
        JSON.stringify(prod.color || ""),
        JSON.stringify(prod.tapiceria || ""),
        JSON.stringify(prod.climatizacion || ""),
        JSON.stringify(prod.marcaArray || ""),
        JSON.stringify(prod.modeloArray || "")
    ].map(normalizarTexto).join(' ');

    const palabras = Array.from(new Set(textoBuscar.split(/\s+/).filter(Boolean)));
    const idStr = String(prod.id_producto || prod.id_vehiculo || '');
    return palabras.reduce((total, palabra) => total + ((base.includes(palabra) || idStr.includes(palabra)) ? 1 : 0), 0);
}

/* ====================== Aplicar filtros ====================== */
/**
 * Devuelve dataset filtrado según estado actual.
 * skipFacet:
 *  - null: aplica TODO
 *  - 'marca': ignora filtro marca (para calcular opciones de marca)
 *  - 'modelo': ignora filtro modelo (para calcular opciones de modelo)
 */
function applyFilters(dataset, skipFacet = null) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));        // categorías
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));    // subcategorías

    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        // tipo
        if (!categoriaInicialCumpleTipo(prod)) return false;

        // búsqueda
        if (!matchBusquedaProducto(prod, searchText)) return false;

        // categorías/subcategorías
        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        if (!matchCat) return false;

        const matchSub = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);
        if (!matchSub) return false;

        // marca/modelo
        const prodBrandIds = parseIdsArray(prod.id_marca).map(String);
        const prodModelIds = parseIdsArray(prod.id_modelo).map(String);

        if (skipFacet !== 'marca') {
            const matchMarca = (marcasSeleccionadas.size === 0) || prodBrandIds.some(id => marcasSeleccionadas.has(id));
            if (!matchMarca) return false;
        }
        if (skipFacet !== 'modelo') {
            const matchModelo = (modelosSeleccionados.size === 0) || prodModelIds.some(id => modelosSeleccionados.has(id));
            if (!matchModelo) return false;
        }

        // precio
        const precio = Number(prod.precio_referencia);
        if (!(precio >= precioMin && precio <= precioMax)) return false;

        // ubicación
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);
        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);
        if (!matchProvincia || !matchCanton) return false;

        return true;
    });
}

/* ====================== Facets (Marcas/Modelos disminuyen) ====================== */
function buildFacetsFromDataset(data) {
    // Construye mapas id->nombre desde objetos si vienen
    const marcasMap = new Map();
    const modelosMap = new Map();

    (data || []).forEach(p => {
        const marcaIds = parseIdsArray(p.id_marca).map(String);
        const marcaObjs = Array.isArray(p.marca) ? p.marca : [];

        marcaIds.forEach(id => {
            if (!marcasMap.has(id)) {
                const found = marcaObjs.find(m => String(m.id) === String(id));
                marcasMap.set(id, { id, nombre: found?.nombre || `Marca ${id}` });
            }
        });

        const modeloIds = parseIdsArray(p.id_modelo).map(String);
        const modeloObjs = Array.isArray(p.modelo) ? p.modelo
            : Array.isArray(p.modelo_productoo) ? p.modelo_productoo
                : Array.isArray(p.modelo_producto) ? [p.modelo_producto]
                    : [];

        modeloIds.forEach(id => {
            if (!modelosMap.has(id)) {
                const found = modeloObjs.find(m => String(m.id) === String(id));
                modelosMap.set(id, { id, nombre: found?.nombre || `Modelo ${id}` });
            }
        });
    });

    const marcas = Array.from(marcasMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
    const modelos = Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));

    return { marcas, modelos };
}

/**
 * Recalcula opciones disponibles:
 * - Marcas: aplica todos los filtros EXCEPTO marca
 * - Modelos: aplica todos los filtros EXCEPTO modelo (pero respeta marca si está seleccionada)
 */
function refreshBrandModelFacets() {
    const baseMarca = applyFilters(productosData, 'marca');
    const baseModelo = applyFilters(productosData, 'modelo');

    const { marcas } = buildFacetsFromDataset(baseMarca);
    const { modelos } = buildFacetsFromDataset(baseModelo);

    // Limpia selecciones inválidas
    const marcasAllow = new Set(marcas.map(x => String(x.id)));
    marcasSeleccionadas = new Set([...marcasSeleccionadas].filter(id => marcasAllow.has(id)));

    const modelosAllow = new Set(modelos.map(x => String(x.id)));
    modelosSeleccionados = new Set([...modelosSeleccionados].filter(id => modelosAllow.has(id)));

    // Render
    marcasUnicas = marcas;
    modelosUnicos = modelos;
    renderCheckboxMarcas(marcasUnicas);
    renderCheckboxModelos(modelosUnicos);
}

/* ====================== Render Marcas/Modelos CHECKBOX ====================== */
function renderCheckboxMarcas(marcas) {
    if (!marcas || marcas.length === 0) {
        $("#filtro-marca").empty();
        toggleFilterBlock('#filtro-marca', false);
        return;
    }
    toggleFilterBlock('#filtro-marca', true);

    let html = renderFilterSearchInput("marca", "Buscar marca", filtroTextoMarca) + `
    <div class="form-check mb-2">
      <input class="form-check-input chk-marca" type="checkbox" id="marca-all" value="__all__" ${marcasSeleccionadas.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="marca-all">Todos</label>
    </div>`;

    marcas.forEach(m => {
        const id = String(m.id);
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(capitalizarPrimeraLetra(m.nombre)))}">
        <input class="form-check-input chk-marca" type="checkbox" id="marca-${id}" value="${id}" ${marcasSeleccionadas.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="marca-${id}">
          ${capitalizarPrimeraLetra(m.nombre)}
        </label>
      </div>`;
    });
    html += `<small class="text-muted filter-option-empty" style="display:none;">No hay marcas.</small>`;
    $("#filtro-marca").html(html);
    applyFilterOptionSearch("marca", filtroTextoMarca);
}

function renderCheckboxModelos(modelos) {
    if (!modelos || modelos.length === 0) {
        $("#filtro-modelo").empty();
        toggleFilterBlock('#filtro-modelo', false);
        return;
    }
    toggleFilterBlock('#filtro-modelo', true);

    let html = renderFilterSearchInput("modelo", "Buscar modelo", filtroTextoModelo) + `
    <div class="form-check mb-2">
      <input class="form-check-input chk-modelo" type="checkbox" id="modelo-all" value="__all__" ${modelosSeleccionados.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
    </div>`;

    modelos.forEach(m => {
        const id = String(m.id);
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(capitalizarPrimeraLetra(m.nombre)))}">
        <input class="form-check-input chk-modelo" type="checkbox" id="modelo-${id}" value="${id}" ${modelosSeleccionados.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="modelo-${id}">
          ${capitalizarPrimeraLetra(m.nombre)}
        </label>
      </div>`;
    });
    html += `<small class="text-muted filter-option-empty" style="display:none;">No hay modelos.</small>`;
    $("#filtro-modelo").html(html);
    applyFilterOptionSearch("modelo", filtroTextoModelo);
}

/* ✅ Handlers marca/modelo checkbox */
$(document).on("change", ".chk-marca", function () {
    const val = String($(this).val());

    if (val === "__all__") {
        // Si selecciona "Todos" => limpia
        marcasSeleccionadas.clear();
        // Desmarca las demás
        $(".chk-marca").not(this).prop("checked", false);
        $(this).prop("checked", true);
    } else {
        // Si marca una marca => desmarca "Todos"
        $("#marca-all").prop("checked", false);

        if ($(this).is(":checked")) marcasSeleccionadas.add(val);
        else marcasSeleccionadas.delete(val);

        // Si se queda sin marcas seleccionadas -> activa "Todos"
        if (marcasSeleccionadas.size === 0) $("#marca-all").prop("checked", true);
    }

    // 🔥 recalcula facetas y render
    refreshAllUIFromCurrentState(false);
});

$(document).on("change", ".chk-modelo", function () {
    const val = String($(this).val());

    if (val === "__all__") {
        modelosSeleccionados.clear();
        $(".chk-modelo").not(this).prop("checked", false);
        $(this).prop("checked", true);
    } else {
        $("#modelo-all").prop("checked", false);

        if ($(this).is(":checked")) modelosSeleccionados.add(val);
        else modelosSeleccionados.delete(val);

        if (modelosSeleccionados.size === 0) $("#modelo-all").prop("checked", true);
    }

    refreshAllUIFromCurrentState(false);
});

/* ====================== Categorías/Subcategorías (SIN API) ====================== */

// Render categorías (igual que antes)
function renderCheckboxCategorias(categorias) {
    if (!categorias || categorias.length === 0) {
        $("#filtro-categorias").empty();
        toggleFilterBlock('#filtro-categorias', false);
        return;
    }
    toggleFilterBlock('#filtro-categorias', true);

    let htmlVisible = '';
    let htmlOcultas = '';
    const maxVisible = 10;

    categorias.forEach((cat, index) => {
        const checkboxHTML = `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(capitalizarPrimeraLetra(cat.nombre)))}">
        <input class="form-check-input" type="checkbox" value="${cat.id_categoria}" id="categoria-${cat.id_categoria}" name="checkbox"
          ${subcategoriasSeleccionadas.map(Number).includes(Number(cat.id_categoria)) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-${cat.id_categoria}">
          ${capitalizarPrimeraLetra(cat.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += checkboxHTML; else htmlOcultas += checkboxHTML;
    });

    const htmlFinal = `
    ${renderFilterSearchInput("categoria", "Buscar categoría", filtroTextoCategoria)}
    <div class="checkbox-list-visible">${htmlVisible}</div>
    <div class="more_slide_open-cat" style="display:none;">${htmlOcultas}</div>
    <small class="text-muted filter-option-empty" style="display:none;">No hay categorías.</small>
    ${htmlOcultas ? `
      <div class="more_categories-cat cursor-pointer">
        <span class="icon">+</span>
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}`;

    $("#filtro-categorias").html(htmlFinal);
    applyFilterOptionSearch("categoria", filtroTextoCategoria);

    $(document).off("click.moreCat", "#filtro-categorias .more_categories-cat")
        .on("click.moreCat", "#filtro-categorias .more_categories-cat", function () {
            $(this).toggleClass("show");
            $(this).prev(".more_slide_open-cat").slideToggle();
        });
}

// Al cambiar categorías: SOLO estado + subcats local + re-render
$(document).on("change", "input[name='checkbox']", function () {
    const id = String($(this).val());

    if ($(this).is(":checked")) {
        if (!subcategoriasSeleccionadas.includes(id)) subcategoriasSeleccionadas.push(id);
    } else {
        subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(x => x !== id);
    }

    // ✅ Subcats basadas en catsIndex local (no API)
    const idsUnicos = [...new Set(subcategoriasSeleccionadas.map(Number))];

    if (idsUnicos.length === 0) {
        subcategoriasHijas = [];
        subcategoriasHijasSeleccionadas = [];
        $("#filtro-sub-categorias").empty();
        $("#subcats-box").hide();

        refreshAllUIFromCurrentState(false);
        return;
    }

    subcategoriasHijas = buildSubcatsForSelected(idsUnicos);
    // limpia seleccionadas que ya no existan
    const allow = new Set(subcategoriasHijas.map(s => Number(s.id_sub_categoria)));
    subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => allow.has(Number(x)));

    renderCheckboxSubcategorias(subcategoriasHijas);
    $("#subcats-box").toggle(subcategoriasHijas.length > 0);

    refreshAllUIFromCurrentState(false);
});

// Subcategorías
function renderCheckboxSubcategorias(subcats) {
    if (!Array.isArray(subcats) || subcats.length === 0) {
        $("#filtro-sub-categorias").empty();
        $("#subcats-box").hide();
        return;
    }
    $("#subcats-box").show();

    let htmlVisible = '';
    let htmlOcultas = '';
    const maxVisible = 10;

    subcats.forEach((sc, index) => {
        const id = Number(sc.sub_categoria ?? sc.id_sub_categoria ?? sc.id_subcategoria ?? sc.id_subcategoria);
        const checked = subcategoriasHijasSeleccionadas.map(Number).includes(Number(id));

        const item = `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(capitalizarPrimeraLetra(sc.nombre)))}">
        <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-sub" ${checked ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="subcat-${id}">
          ${capitalizarPrimeraLetra(sc.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += item; else htmlOcultas += item;
    });

    const htmlFinal = `
    ${renderFilterSearchInput("subcategoria", "Buscar sub categoría", filtroTextoSubcategoria)}
    <div class="checkbox-list-visible">${htmlVisible}</div>
    <div class="more_slide_open-sub" style="display:none;">${htmlOcultas}</div>
    <small class="text-muted filter-option-empty" style="display:none;">No hay subcategorías.</small>
    ${htmlOcultas ? `
      <div class="more_categories-sub cursor-pointer">
        <span class="icon">+</span>
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}`;

    $("#filtro-sub-categorias").html(htmlFinal);
    applyFilterOptionSearch("subcategoria", filtroTextoSubcategoria);

    $(document)
        .off("click.moreSub", "#filtro-sub-categorias .more_categories-sub")
        .on("click.moreSub", "#filtro-sub-categorias .more_categories-sub", function () {
            $(this).toggleClass("show");
            $(this).prev(".more_slide_open-sub").slideToggle();
        });
}

// Handler subcategorías seleccionadas
$(document).on("change", "input[name='checkbox-sub']", function () {
    const id = Number($(this).val());
    if ($(this).is(":checked")) {
        if (!subcategoriasHijasSeleccionadas.includes(id)) subcategoriasHijasSeleccionadas.push(id);
    } else {
        subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => x !== id);
    }
    refreshAllUIFromCurrentState(false);
});

/* ====================== Cats/Subcats index ====================== */
function buildCatsAndSubcatsFromProductos(productos) {
    const catsMap = new Map();       // catId -> {id_categoria, nombre}
    const subsPorCatMap = new Map(); // catId -> Map(subId -> {id_sub_categoria, nombre})

    (productos || []).forEach(p => {
        const catObjs = Array.isArray(p.categorias) ? p.categorias : [];
        let catIds = parseIdsArray(p.categoria ?? p.id_categoria);
        if (catObjs.length) {
            const idsDeObjs = catObjs.map(o => Number(o.id)).filter(Boolean);
            catIds = Array.from(new Set([...catIds, ...idsDeObjs]));
        }

        const subObjs = Array.isArray(p.subcategorias) ? p.subcategorias
            : Array.isArray(p.sub_categorias) ? p.sub_categorias
                : [];
        let subIds = parseIdsArray(p.sub_categoria ?? p.id_subcategoria ?? p.id_sub_categoria);
        if (subObjs.length) {
            const idsDeObjs = subObjs.map(o => Number(o.id)).filter(Boolean);
            subIds = Array.from(new Set([...subIds, ...idsDeObjs]));
        }

        catIds.forEach(cid => {
            if (!cid) return;
            const nombreCat = (catObjs.find(o => Number(o.id) === cid)?.nombre) || `Categoría ${cid}`;
            if (!catsMap.has(cid)) catsMap.set(cid, { id_categoria: cid, nombre: capitalizarPrimeraLetra(nombreCat) });

            if (!subsPorCatMap.has(cid)) subsPorCatMap.set(cid, new Map());
            const subMap = subsPorCatMap.get(cid);

            subIds.forEach(sid => {
                if (!sid) return;
                const nombreSub = (subObjs.find(o => Number(o.id) === sid)?.nombre) || `Subcategoría ${sid}`;
                if (!subMap.has(sid)) subMap.set(sid, { id_sub_categoria: sid, nombre: capitalizarPrimeraLetra(nombreSub) });
            });
        });
    });

    const categoriasLista = Array.from(catsMap.values())
        .sort((a, b) => a.nombre.localeCompare(b.nombre));

    const subsPorCat = new Map(
        Array.from(subsPorCatMap.entries()).map(([cid, m]) => [
            Number(cid),
            Array.from(m.values()).sort((a, b) => a.nombre.localeCompare(b.nombre))
        ])
    );

    return { categoriasLista, subsPorCat };
}

function buildSubcatsForSelected(idsCategorias) {
    if (!catsIndex) return [];
    const unicas = new Map();
    (idsCategorias || []).map(Number).forEach(cid => {
        const arr = catsIndex.subsPorCat.get(cid) || [];
        arr.forEach(sc => {
            if (!unicas.has(sc.id_sub_categoria)) unicas.set(sc.id_sub_categoria, sc);
        });
    });
    return Array.from(unicas.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}

function inicializarSlider(maxPrecio) {
    const sliderElement = document.getElementById("slider-range");
    if (!sliderElement) return;

    // Si maxPrecio no es válido
    if (!Number.isFinite(maxPrecio) || maxPrecio <= 0) {
        if (sliderElement.noUiSlider) {
            try { sliderElement.noUiSlider.destroy(); } catch (_) { }
        }
        sliderElement.innerHTML = "";
        $("#slider-range-value1").text(window.moneyFormat.to(0));
        $("#slider-range-value2").text(window.moneyFormat.to(0));
        precioMin = 0;
        precioMax = Infinity;
        sliderMaxActual = 0;
        return;
    }

    // ✅ Si ya existe slider y el max NO cambió => NO reconstruyas
    if (sliderElement.noUiSlider && sliderMaxActual === maxPrecio) {
        return;
    }

    // ✅ Si existe y cambió el max => reconstruir
    if (sliderElement.noUiSlider) {
        try { sliderElement.noUiSlider.destroy(); } catch (_) { }
    }

    sliderMaxActual = maxPrecio;

    noUiSlider.create(sliderElement, {
        start: [0, maxPrecio],
        step: 1,
        range: { min: 0, max: maxPrecio },
        format: window.moneyFormat,
        connect: true
    });

    // ✅ IMPORTANTE: aquí NO llamamos refreshAllUIFromCurrentState
    sliderElement.noUiSlider.on("update", function (values) {
        $("#slider-range-value1").text(values[0]);
        $("#slider-range-value2").text(values[1]);

        precioMin = parseFloat(window.moneyFormat.from(values[0]));
        precioMax = parseFloat(window.moneyFormat.from(values[1]));
    });

    // ✅ Solo cuando suelta el handle (change) aplicas filtros
    sliderElement.noUiSlider.on("change", function () {
        // Evita spam de renders si el usuario mueve rápido
        clearTimeout(sliderRefreshTimer);
        sliderRefreshTimer = setTimeout(() => {
            // aquí NO reconstruyas slider
            refreshBrandModelFacets();     // disminuye filtros marca/modelo
            renderEmpresas(productosData, 1);
        }, 80);
    });
}

/* ====================== Render Grid ====================== */
function renderEmpresas(data, page = 1) {
    // Aplica TODO
    let filtrado = applyFilters(data, null);

    if (searchText) {
        filtrado.sort((a, b) => getBusquedaProductoScore(b, searchText) - getBusquedaProductoScore(a, searchText));
    }

    // Ordenamiento
    if (sortOption === "mayor") {
        filtrado.sort((a, b) => (getBusquedaProductoScore(b, searchText) - getBusquedaProductoScore(a, searchText)) || (Number(b.precio_referencia) - Number(a.precio_referencia)));
    } else if (sortOption === "menor") {
        filtrado.sort((a, b) => (getBusquedaProductoScore(b, searchText) - getBusquedaProductoScore(a, searchText)) || (Number(a.precio_referencia) - Number(b.precio_referencia)));
    }

    // Paginación
    const totalPages = Math.max(1, Math.ceil(filtrado.length / itemsPerPage));
    const safePage = Math.min(Math.max(page, 1), totalPages);
    currentPage = safePage;
    const start = (safePage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pagina = filtrado.slice(start, end);

    let listProductos = "";

    pagina.forEach(productos => {
        const nombreServicioVisible = getNombreServicioVisible(productos);
        const tieneDescuento = parseFloat(productos.descuento) > 0;
        const precioRef = Number(productos.precio_referencia) || 0;
        const precioDescuento = precioRef - (precioRef * (Number(productos.descuento) || 0) / 100);

        let verificacion = "";
        if (Array.isArray(productos.verificacion) && productos.verificacion.length > 0) {
            if (productos.verificacion[0].verificado == 1) {
                verificacion = `
          <div style="position:absolute;top:10px;right:10px;z-index:4;width:40px;height:40px;">
            <img src="img/verificado_empresa.png"
              alt="Verificado"
              title="Empresa verificada"
              style="width:80px;height:80px;object-fit:contain;">
          </div>`;
            }
        }

        listProductos += `
      <div class="col-md-4 col-lg-3 col-sm-4 col-12 mb-3 d-flex px-2">
        <div class="product-cart-wrap w-100 d-flex flex-column">
          <div class="product-img-action-wrap text-center">
            <div class="product-img product-img-zoom">
              <a href="detalle_productos.php?q=${productos.id_producto}" target="_blank" rel="noopener noreferrer"
                 onclick="irADetalleProductoConTerminos(${productos.id_producto}); return false;">
                <img class="default-img img-fluid mb-1"
                  src="admin/${productos.img_frontal}"
                  onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"
                  style="object-fit: contain; width: 100%; height: 200px">
                <img class="hover-img img-fluid"
                  src="admin/${productos.img_posterior}"
                  onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"
                  style="object-fit: contain; width: 100%; height: 200px">
              </a>
            </div>
            ${tieneDescuento ? `
              <div class="product-badges product-badges-position product-badges-mrg">
                <span class="best">-${parseInt(productos.descuento)}%</span>
              </div>` : ''}
          </div>

          <div class="product-content-wrap p-1">
            ${verificacion}
            <h2 class="text-center">
              <a href="detalle_productos.php?q=${productos.id_producto}" target="_blank" rel="noopener noreferrer"
                 onclick="irADetalleProductoConTerminos(${productos.id_producto}); return false;"
                 class="limitar-lineas mt-1">
                ${nombreServicioVisible}
              </a>
            </h2>
            <div class="product-price mb-2 mt-0 text-center">
              <span>${window.formatoMoneda.format(tieneDescuento ? precioDescuento : precioRef)}</span>
              ${tieneDescuento ? `<span class="old-price">${window.formatoMoneda.format(precioRef)}</span>` : ''}
            </div>
          </div>
        </div>
      </div>`;
    });

    $("#totalProductosGeneral").text(filtrado.length);
    $(".product-grid").html(listProductos || `
    <div class="col-12 text-center">
      <p class="text-muted">No hay resultados con los filtros seleccionados.</p>
    </div>
  `);

    renderPaginacion(filtrado.length, safePage);
}

function renderPaginacion(totalItems, current) {
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
    let pagHtml = '';

    for (let i = 1; i <= totalPages; i++) {
        pagHtml += `<li class="page-item ${i === current ? 'active' : ''}">
      <a class="page-link" href="#" data-page="${i}">${i}</a>
    </li>`;
    }

    $(".pagination").html(`
    <li class="page-item ${current === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${current - 1}">
        <i class="fi-rs-arrow-small-left"></i>
      </a>
    </li>
    ${pagHtml}
    <li class="page-item ${current === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${current + 1}">
        <i class="fi-rs-arrow-small-right"></i>
      </a>
    </li>
  `);
}

$(document).off("input.filterOptionSearchServicios").on("input.filterOptionSearchServicios", ".filter-option-search", function () {
    const tipo = $(this).data("filter-type");
    const valor = $(this).val().trim();
    if (tipo === "marca") filtroTextoMarca = valor;
    if (tipo === "modelo") filtroTextoModelo = valor;
    if (tipo === "categoria") filtroTextoCategoria = valor;
    if (tipo === "subcategoria") filtroTextoSubcategoria = valor;
    applyFilterOptionSearch(tipo, valor);
});

/* ====================== UI global refresh ====================== */
function refreshAllUIFromCurrentState(firstLoad = false) {
    const locData = filtrarPorUbicacionDataset(productosData);

    const precios = (locData || [])
        .map(p => Number(p.precio_referencia))
        .filter(n => Number.isFinite(n) && n > 0);

    const maxPrecio = precios.length ? Math.max(...precios) : 0;

    // ✅ Esto ya no entra en loop, porque inicializarSlider no se recrea si max no cambia
    inicializarSlider(maxPrecio);

    // ... el resto igual (catsIndex, categorias, subcats, facets, grid)
    catsIndex = buildCatsAndSubcatsFromProductos(locData);
    categoriasFiltradas = catsIndex.categoriasLista || [];

    const catDisponibles = new Set(categoriasFiltradas.map(c => Number(c.id_categoria)));
    subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(id => catDisponibles.has(Number(id)));
    renderCheckboxCategorias(categoriasFiltradas);

    const idsCatsSel = subcategoriasSeleccionadas.map(Number);
    subcategoriasHijas = idsCatsSel.length ? buildSubcatsForSelected(idsCatsSel) : [];

    const subAllow = new Set(subcategoriasHijas.map(s => Number(s.id_sub_categoria)));
    subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => subAllow.has(Number(x)));

    renderCheckboxSubcategorias(subcategoriasHijas);
    $("#subcats-box").toggle(subcategoriasHijas.length > 0);

    refreshBrandModelFacets();

    currentPage = 1;
    renderEmpresas(productosData, currentPage);
}

// dataset → subset solo por ubicación + tipo
function filtrarPorUbicacionDataset(dataset) {
    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        if (!categoriaInicialCumpleTipo(prod)) return false;

        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);
        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);
        return matchProvincia && matchCanton;
    });
}

/* ====================== Handlers básicos (paginación/búsqueda/sort) ====================== */
$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(productosData, currentPage);
    }
});

$(".widget_search input").on("input", function () {
    searchText = $(this).val().trim();
    refreshAllUIFromCurrentState(false);
});

$(".sort-show li a").on("click", function (e) {
    e.preventDefault();
    $(".sort-show li a").removeClass("active");
    $(this).addClass("active");

    const value = $(this).data("value");
    itemsPerPage = value === "all" ? (productosData.length || 1) : parseInt(value);

    $(".sort-by-product-wrap:first .sort-by-dropdown-wrap span")
        .html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    currentPage = 1;
    renderEmpresas(productosData, currentPage);
});

$(".sort-order li a").on("click", function (e) {
    e.preventDefault();
    $(".sort-order li a").removeClass("active");
    $(this).addClass("active");

    sortOption = $(this).data("value");

    $(".sort-by-product-wrap:last .sort-by-dropdown-wrap span")
        .html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    refreshAllUIFromCurrentState(false);
});

/* ====================== Ubicación ====================== */
function cargarProvincias() {
    const selectProvincia = document.getElementById("selectProvincia");
    if (!selectProvincia) return;

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
            provinciaSel = { id: null, nombre: null };
            resetSelectCanton();
            actualizarUIUbicacionPersistir();
            refreshAllUIFromCurrentState(false);
            return;
        }

        provinciaSel.id = codProv;
        provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);

        resetSelectCanton();
        cargarCantones(codProv);

        actualizarUIUbicacionPersistir();
        refreshAllUIFromCurrentState(false);
    });
}

function cargarCantones(codProvincia) {
    const selectCanton = document.getElementById("selectCanton");
    if (!selectCanton) return;

    selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';

    if (!codProvincia || !datosEcuador[codProvincia]) return;

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
            cantonSel = { id: null, nombre: null };
            actualizarUIUbicacionPersistir();
            refreshAllUIFromCurrentState(false);
            return;
        }

        const nombre = capitalizarPrimeraLetra(
            (datosEcuador[codProvincia].cantones[codCanton] || {}).canton || ""
        );
        cantonSel.id = codCanton;
        cantonSel.nombre = nombre;

        actualizarUIUbicacionPersistir();
        refreshAllUIFromCurrentState(false);
    });
}

function resetSelectCanton() {
    const selectCanton = document.getElementById("selectCanton");
    if (selectCanton) selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
    cantonSel = { id: null, nombre: null };
}

function labelUbicacion() {
    if (provinciaSel.nombre && cantonSel.nombre) return `PROVINCIA: ${provinciaSel.nombre}; CANTÓN: ${cantonSel.nombre}`;
    if (provinciaSel.nombre) return `PROVINCIA: ${provinciaSel.nombre}`;
    return 'Cambiar ubicación';
}

function actualizarUIUbicacionPersistir() {
    const btn = document.getElementById('btnUbicacion');
    if (btn) btn.innerHTML = `<i class="fi-rs-marker me-1"></i> ${labelUbicacion()}`;

    const provIdH = document.getElementById('provincia_id_hidden');
    const provNomH = document.getElementById('provincia_nombre_hidden');
    const cantIdH = document.getElementById('canton_id_hidden');
    const cantNomH = document.getElementById('canton_nombre_hidden');

    if (provIdH) provIdH.value = provinciaSel.id || '';
    if (provNomH) provNomH.value = provinciaSel.nombre || '';
    if (cantIdH) cantIdH.value = cantonSel.id || '';
    if (cantNomH) cantNomH.value = cantonSel.nombre || '';

    localStorage.setItem('ubicacionSeleccionada', JSON.stringify({
        provincia: provinciaSel,
        canton: cantonSel
    }));
}

document.getElementById('guardarUbicacion')?.addEventListener('click', function () {
    actualizarUIUbicacionPersistir();
    refreshAllUIFromCurrentState(false);

    const modalEl = document.getElementById('modalUbicacion');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
});

document.getElementById('limpiarUbicacion')?.addEventListener('click', () => {
    provinciaSel = { id: null, nombre: null };
    cantonSel = { id: null, nombre: null };

    const selectProvincia = document.getElementById('selectProvincia');
    if (selectProvincia) selectProvincia.value = '';
    resetSelectCanton();

    actualizarUIUbicacionPersistir();
    refreshAllUIFromCurrentState(false);
});

/* ====================== Carrito ====================== */
function agregarProductoCarrito(id_producto, nombreP, precio_referencia, img_frontal) {
    const productoId = id_producto;
    const cantidad = 1;
    const nombre = nombreP;
    const precio = precio_referencia;
    const img = ("admin/" + img_frontal) || "img/FULMUV-NEGRO.png";

    let carrito = [];
    try {
        const stored = JSON.parse(localStorage.getItem("carrito"));
        const now = new Date().getTime();

        if (stored && Array.isArray(stored.data)) {
            if (now - stored.timestamp < 2 * 60 * 60 * 1000) {
                carrito = stored.data;
            }
        }
    } catch (e) { }

    const existente = carrito.find(p => p.id === productoId);
    if (existente) existente.cantidad += cantidad;
    else carrito.push({ id: productoId, nombre, precio, cantidad, imagen: img });

    localStorage.setItem("carrito", JSON.stringify({
        data: carrito,
        timestamp: new Date().getTime()
    }));

    actualizarIconoCarrito();
}
