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
let nombresServiciosSeleccionados = new Set(); // nombres normalizados
let marcasUnicas = [];
let modelosUnicos = [];
let nombresServiciosUnicos = [];

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

function scrollToResultsTop() {
    const toolbar = document.querySelector(".toolbar-modern");
    const resultsHead = document.querySelector(".results-head");
    const resultsContainer = resultsHead || document.getElementById("listaServiciosContainer");
    if (!resultsContainer) return;

    const toolbarOffset = toolbar ? toolbar.offsetHeight + 12 : 12;
    const top = resultsContainer.getBoundingClientRect().top + window.pageYOffset - toolbarOffset;

    window.scrollTo({
        top: Math.max(top, 0),
        behavior: "smooth"
    });
}

function openFilterPanel() {
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

function setActiveFilterSection(target) {
    const panelName = target || "ubicacion-orden";
    $(".filter-nav-item").removeClass("is-active");
    $(`.filter-nav-item[data-filter-target="${panelName}"]`).addClass("is-active");
    $(".filter-detail-panel").removeClass("is-active");
    $(`.filter-detail-panel[data-filter-panel="${panelName}"]`).addClass("is-active");
}

function updateFilterGroupCounts() {
    const countUbicacionOrden =
        (provinciaSel.id ? 1 : 0) +
        (cantonSel.id ? 1 : 0) +
        (sortOption !== "todos" ? 1 : 0);
    const countCategorias = subcategoriasSeleccionadas.length;
    const countSubcategorias = subcategoriasHijasSeleccionadas.length;
    const countMarcas = marcasSeleccionadas.size;
    const countModelos = modelosSeleccionados.size;
    const countNombres = nombresServiciosSeleccionados.size;
    const countPrecio = (precioMin > 0 || precioMax !== Infinity) ? 1 : 0;

    $("#filterGroupCountUbicacionOrden").text(countUbicacionOrden);
    $("#filterGroupCountCategorias").text(countCategorias);
    $("#filterGroupCountSubcategorias").text(countSubcategorias);
    $("#filterGroupCountMarcas").text(countMarcas);
    $("#filterGroupCountModelos").text(countModelos);
    $("#filterGroupCountNombres").text(countNombres);
    $("#filterGroupCountPrecio").text(countPrecio);
}

function updateFilterActiveCount() {
    const count =
        subcategoriasSeleccionadas.length +
        subcategoriasHijasSeleccionadas.length +
        marcasSeleccionadas.size +
        modelosSeleccionados.size +
        nombresServiciosSeleccionados.size +
        (sortOption !== "todos" ? 1 : 0) +
        (searchText ? 1 : 0) +
        (provinciaSel.id ? 1 : 0) +
        (cantonSel.id ? 1 : 0) +
        (precioMin > 0 || precioMax !== Infinity ? 1 : 0);

    $("#filterActiveCount").text(count);
    $("#filterResultsCount").text($("#totalProductosGeneral").text() || 0);
    updateFilterGroupCounts();
}

function updateLocationButtonLabel() {
    const label = `<i class="fi-rs-marker me-1"></i>${labelUbicacion()}`;
    $("#btnUbicacionPanel span").html(label);
}

function buildEmptyStateHtml(title, message, iconClass = "fi-rs-settings-sliders") {
    return `
        <div class="empty-state-modern">
            <div class="empty-state-card">
                <div class="empty-state-icon">
                    <i class="${iconClass}"></i>
                </div>
                <div class="empty-state-title">${title}</div>
                <p class="empty-state-text">${message}</p>
            </div>
        </div>`;
}

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
fetch('../provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

/* ====================== Ready ====================== */
$(document).ready(function () {
    actualizarIconoCarrito();
    updateFilterActiveCount();
    updateLocationButtonLabel();
    setActiveFilterSection("ubicacion-orden");

    $("#breadcrumb").append(`
    <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
    <span></span> Lista de Servicios
  `);

    // ✅ 1 sola carga (base)
    $.get("../api/v1/fulmuv/serviciosProductos/All", function (returnedData) {
        if (returnedData && returnedData.error === false) {
            productosData = returnedData.data || [];

            // Construye índice base de categorías/subcats a partir de dataset (luego se reconstruye por ubicación)
            refreshAllUIFromCurrentState(true);
        }
    }, 'json');

    $("#openFilterPanel").on("click", openFilterPanel);
    $("#closeFilterPanel, #filtersOverlay").on("click", closeFilterPanel);
    $("#applyFiltersPanel").on("click", closeFilterPanel);

    $(document).on("click", ".filter-nav-item", function () {
        setActiveFilterSection($(this).data("filter-target"));
    });

    $("#selectOrderPanel").on("change", function () {
        sortOption = $(this).val();
        refreshAllUIFromCurrentState(false);
        updateFilterActiveCount();
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
        marcasSeleccionadas.clear();
        modelosSeleccionados.clear();
        nombresServiciosSeleccionados.clear();
        provinciaSel = { id: null, nombre: null };
        cantonSel = { id: null, nombre: null };

        $("#inputBusqueda").val("");
        $("#selectOrderPanel").val("todos");
        $("#selectShowPanel").val("20");
        $("input[name='checkbox'], input[name='checkbox-sub'], .chk-marca, .chk-modelo, .chk-nombre-servicio").prop("checked", false);
        $("#selectProvincia").val("");
        resetSelectCanton();

        const sliderElement = document.getElementById("slider-range");
        if (sliderElement?.noUiSlider && sliderMaxActual > 0) {
            sliderElement.noUiSlider.set([0, sliderMaxActual]);
        }

        updateLocationButtonLabel();
        updateFilterActiveCount();
        refreshAllUIFromCurrentState(true);
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $("#panelFiltros").hasClass("is-open")) {
            closeFilterPanel();
        }
    });
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
    const $wrap = $(contentSelector).closest('.categories-dropdown-wrap');
    if ($wrap.length) $wrap.toggle(!!hasItems);
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

    const base = [
        prod.titulo_producto,
        prod.nombre,
        prod.tags
    ].map(normalizarTexto).join(' ');

    const palabras = textoBuscar.split(/\s+/).filter(Boolean);
    const todasPresentes = palabras.every(p => base.includes(p));

    const soloNumerosBusqueda = (text || '').replace(/\s+/g, '');
    let idMatch = false;
    if (/^\d+$/.test(soloNumerosBusqueda)) {
        const idStr = String(prod.id_producto || '');
        idMatch = idStr.includes(soloNumerosBusqueda);
    }
    return todasPresentes || idMatch;
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

        if (skipFacet !== 'nombre_servicio') {
            const nombreServicio = normalizarTexto(prod.titulo_producto || prod.nombre || "");
            const matchNombreServicio = (nombresServiciosSeleccionados.size === 0) || nombresServiciosSeleccionados.has(nombreServicio);
            if (!matchNombreServicio) return false;
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
    const nombresServiciosMap = new Map();

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

        const nombreServicio = capitalizarPrimeraLetra(p.titulo_producto || p.nombre || "");
        const nombreServicioId = normalizarTexto(nombreServicio);
        if (nombreServicioId && !nombresServiciosMap.has(nombreServicioId)) {
            nombresServiciosMap.set(nombreServicioId, { id: nombreServicioId, nombre: nombreServicio });
        }
    });

    const marcas = Array.from(marcasMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
    const modelos = Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
    const nombresServicios = Array.from(nombresServiciosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));

    return { marcas, modelos, nombresServicios };
}

/**
 * Recalcula opciones disponibles:
 * - Marcas: aplica todos los filtros EXCEPTO marca
 * - Modelos: aplica todos los filtros EXCEPTO modelo (pero respeta marca si está seleccionada)
 */
function refreshBrandModelFacets() {
    const baseMarca = applyFilters(productosData, 'marca');
    const baseModelo = applyFilters(productosData, 'modelo');
    const baseNombreServicio = applyFilters(productosData, 'nombre_servicio');

    const { marcas } = buildFacetsFromDataset(baseMarca);
    const { modelos } = buildFacetsFromDataset(baseModelo);
    const { nombresServicios } = buildFacetsFromDataset(baseNombreServicio);

    // Limpia selecciones inválidas
    const marcasAllow = new Set(marcas.map(x => String(x.id)));
    marcasSeleccionadas = new Set([...marcasSeleccionadas].filter(id => marcasAllow.has(id)));

    const modelosAllow = new Set(modelos.map(x => String(x.id)));
    modelosSeleccionados = new Set([...modelosSeleccionados].filter(id => modelosAllow.has(id)));

    const nombresServiciosAllow = new Set(nombresServicios.map(x => String(x.id)));
    nombresServiciosSeleccionados = new Set([...nombresServiciosSeleccionados].filter(id => nombresServiciosAllow.has(id)));

    // Render
    marcasUnicas = marcas;
    modelosUnicos = modelos;
    nombresServiciosUnicos = nombresServicios;
    renderCheckboxMarcas(marcasUnicas);
    renderCheckboxModelos(modelosUnicos);
    renderCheckboxNombresServicio(nombresServiciosUnicos);
}

/* ====================== Render Marcas/Modelos CHECKBOX ====================== */
function renderCheckboxMarcas(marcas) {
    if (!marcas || marcas.length === 0) {
        $("#filtro-marca").empty();
        toggleFilterBlock('#filtro-marca', false);
        return;
    }
    toggleFilterBlock('#filtro-marca', true);

    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input chk-marca" type="checkbox" id="marca-all" value="__all__" ${marcasSeleccionadas.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="marca-all">Todos</label>
    </div>`;

    marcas.forEach(m => {
        const id = String(m.id);
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-marca" type="checkbox" id="marca-${id}" value="${id}" ${marcasSeleccionadas.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="marca-${id}">
          ${capitalizarPrimeraLetra(m.nombre)}
        </label>
      </div>`;
    });

    $("#filtro-marca").html(html);
}

function renderCheckboxModelos(modelos) {
    if (!modelos || modelos.length === 0) {
        $("#filtro-modelo").empty();
        toggleFilterBlock('#filtro-modelo', false);
        return;
    }
    toggleFilterBlock('#filtro-modelo', true);

    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input chk-modelo" type="checkbox" id="modelo-all" value="__all__" ${modelosSeleccionados.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
    </div>`;

    modelos.forEach(m => {
        const id = String(m.id);
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-modelo" type="checkbox" id="modelo-${id}" value="${id}" ${modelosSeleccionados.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="modelo-${id}">
          ${capitalizarPrimeraLetra(m.nombre)}
        </label>
      </div>`;
    });

    $("#filtro-modelo").html(html);
}

function renderCheckboxNombresServicio(nombresServicios) {
    if (!nombresServicios || nombresServicios.length === 0) {
        $("#filtro-nombre-servicio").empty();
        toggleFilterBlock('#filtro-nombre-servicio', false);
        return;
    }
    toggleFilterBlock('#filtro-nombre-servicio', true);

    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input chk-nombre-servicio" type="checkbox" id="nombre-servicio-all" value="__all__" ${nombresServiciosSeleccionados.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="nombre-servicio-all">Todos</label>
    </div>`;

    nombresServicios.forEach(item => {
        const id = String(item.id);
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-nombre-servicio" type="checkbox" id="nombre-servicio-${id}" value="${id}" ${nombresServiciosSeleccionados.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="nombre-servicio-${id}">
          ${capitalizarPrimeraLetra(item.nombre)}
        </label>
      </div>`;
    });

    $("#filtro-nombre-servicio").html(html);
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

$(document).on("change", ".chk-nombre-servicio", function () {
    const val = String($(this).val());

    if (val === "__all__") {
        nombresServiciosSeleccionados.clear();
        $(".chk-nombre-servicio").not(this).prop("checked", false);
        $(this).prop("checked", true);
    } else {
        $("#nombre-servicio-all").prop("checked", false);

        if ($(this).is(":checked")) nombresServiciosSeleccionados.add(val);
        else nombresServiciosSeleccionados.delete(val);

        if (nombresServiciosSeleccionados.size === 0) $("#nombre-servicio-all").prop("checked", true);
    }

    refreshAllUIFromCurrentState(false);
    updateFilterActiveCount();
});

/* ====================== Categorías/Subcategorías (SIN API) ====================== */

// Render categorías (igual que antes)
// function renderCheckboxCategorias(categorias) {
//     if (!categorias || categorias.length === 0) {
//         $("#filtro-categorias").empty();
//         toggleFilterBlock('#filtro-categorias', false);
//         return;
//     }
//     toggleFilterBlock('#filtro-categorias', true);

//     let htmlVisible = '';
//     let htmlOcultas = '';
//     const maxVisible = 10;

//     categorias.forEach((cat, index) => {
//         const checkboxHTML = `
//       <div class="form-check mb-3">
//         <input class="form-check-input" type="checkbox" value="${cat.id_categoria}" id="categoria-${cat.id_categoria}" name="checkbox"
//           ${subcategoriasSeleccionadas.map(Number).includes(Number(cat.id_categoria)) ? 'checked' : ''}>
//         <label class="form-check-label fw-normal" for="categoria-${cat.id_categoria}">
//           ${capitalizarPrimeraLetra(cat.nombre)}
//         </label>
//       </div>`;
//         if (index < maxVisible) htmlVisible += checkboxHTML; else htmlOcultas += checkboxHTML;
//     });

//     const htmlFinal = `
//     <div class="checkbox-list-visible">${htmlVisible}</div>
//     <div class="more_slide_open-cat" style="display:none;">${htmlOcultas}</div>
//     ${htmlOcultas ? `
//       <div class="more_categories-cat cursor-pointer">
//         <span class="icon">+</span>
//         <span class="heading-sm-1">Show more...</span>
//       </div>` : ''}`;

//     $("#filtro-categorias").html(htmlFinal);

//     $(document).off("click.moreCat", "#filtro-categorias .more_categories-cat")
//         .on("click.moreCat", "#filtro-categorias .more_categories-cat", function () {
//             $(this).toggleClass("show");
//             $(this).prev(".more_slide_open-cat").slideToggle();
//         });
// }

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
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-sub" ${checked ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="subcat-${id}">
          ${capitalizarPrimeraLetra(sc.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += item; else htmlOcultas += item;
    });

    const htmlFinal = `
    <div class="checkbox-list-visible">${htmlVisible}</div>
    <div class="more_slide_open-sub" style="display:none;">${htmlOcultas}</div>
    ${htmlOcultas ? `
      <div class="more_categories-sub cursor-pointer">
        <span class="icon">+</span>
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}`;

    $("#filtro-sub-categorias").html(htmlFinal);

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
        updateFilterActiveCount();
    });

    // ✅ Solo cuando suelta el handle (change) aplicas filtros
    sliderElement.noUiSlider.on("change", function () {
        // Evita spam de renders si el usuario mueve rápido
        clearTimeout(sliderRefreshTimer);
        sliderRefreshTimer = setTimeout(() => {
            // aquí NO reconstruyas slider
            refreshBrandModelFacets();     // disminuye filtros marca/modelo
            renderEmpresas(productosData, 1);
            updateFilterActiveCount();
        }, 80);
    });
}

/* ====================== Render Grid ====================== */
// ✅ REEMPLAZAR FUNCIÓN renderEmpresas
function renderEmpresas(data, page = 1) {
    let filtrado = applyFilters(data, null);

    if (sortOption === "mayor") filtrado.sort((a, b) => Number(b.precio_referencia) - Number(a.precio_referencia));
    else if (sortOption === "menor") filtrado.sort((a, b) => Number(a.precio_referencia) - Number(b.precio_referencia));

    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pagina = filtrado.slice(start, end);

    let listHtml = "";
    pagina.forEach(p => {
        const tieneDesc = parseFloat(p.descuento || 0) > 0;
        const precioRef = Number(p.precio_referencia) || 0;
        const pFinal = tieneDesc ? precioRef * (1 - p.descuento / 100) : precioRef;

        listHtml += `
        <div class="service-card-modern">
            <div class="service-media-modern" onclick="irADetalleProductoConTerminos(${p.id_producto})">
                <img src="../admin/${p.img_frontal}" 
                     onerror="this.src='../img/FULMUV-NEGRO.png';" 
                     class="service-main-img">
                ${tieneDesc ? `<div class="service-discount-badge">-${parseInt(p.descuento)}%</div>` : ''}
            </div>
            <div class="service-info-modern">
                <h6 class="service-title-modern">${capitalizarPrimeraLetra(p.titulo_producto)}</h6>
                <div class="d-flex justify-content-between align-items-end mt-2">
                    <div class="service-price-modern">
                        <span class="service-price-current">${window.formatoMoneda.format(pFinal)}</span>
                        ${tieneDesc ? `<span class="service-price-old">${window.formatoMoneda.format(precioRef)}</span>` : ''}
                    </div>
                    <button class="btn-circle-action" onclick="irADetalleProductoConTerminos(${p.id_producto})">
                        <i class="fi-rs-arrow-small-right"></i>
                    </button>
                </div> 
            </div> 
        </div>`;
    });

    $("#totalProductosGeneral").text(filtrado.length);
    $("#countVendedores").text(`Encontramos ${filtrado.length} resultados`);
    $("#filterResultsCount").text(filtrado.length);

    // ✅ Inyección limpia en el Grid
    $("#listaServiciosContainer").html(listHtml || buildEmptyStateHtml(
        "No encontramos servicios",
        "Prueba con otra búsqueda o ajusta tus filtros para ver más servicios disponibles."
    ));

    renderPaginacion(filtrado.length, page);
}
// ✅ Render de Categorías tipo Chips Horizontales
function renderCheckboxCategorias(categorias) {
    let html = '';
    categorias.forEach((cat) => {
        const checked = subcategoriasSeleccionadas.map(Number).includes(Number(cat.id_categoria));
        html += `
            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="${cat.id_categoria}" 
                       id="chip-p-${cat.id_categoria}" autocomplete="off" name="checkbox" ${checked ? 'checked' : ''}>
                <label class="form-check-label" for="chip-p-${cat.id_categoria}">
                    ${capitalizarPrimeraLetra(cat.nombre)}
                </label>
            </div>`;
    });
    $("#filtro-categorias-panel").html(html);
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
        scrollToResultsTop();
    }
});

$("#inputBusqueda").on("input", function () {
    searchText = $(this).val().trim();
    refreshAllUIFromCurrentState(false);
    updateFilterActiveCount();
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
    updateLocationButtonLabel();

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
    updateFilterActiveCount();

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
    updateFilterActiveCount();
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
