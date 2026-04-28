function shuffleArray(arr) {
    const a = arr.slice();
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

/* ====================== Estado global ====================== */
let itemsPerPage = 20;
let currentPage = 1;

// ✅ Dataset maestro (NO se toca). Todo sale de getServiciosEmergenciaAll/All
let productosMaster = [];
let productosData = []; // (opcional) vista, si quieres conservar

let sortOption = "todos"; // "mayor", "menor", "todos"
let searchText = "";

// Categorías/Subcategorías
let subcategoriasSeleccionadas = [];        // (en realidad son categorías)
let subcategoriasHijas = [];               // listado construido localmente
let subcategoriasHijasSeleccionadas = [];  // checks seleccionados

// Marca/Modelo como CHECKBOX (multi)
let marcasUnicas = [];
let modelosUnicos = [];
let marcasSeleccionadas = new Set();   // ids marca
let modelosSeleccionados = new Set();  // ids modelo
let filtroTextoEmergencia = "";
let filtroTextoMarca = "";
let filtroTextoModelo = "";
let filtroTextoCategoria = "";
let filtroTextoSubcategoria = "";

// Precio
let precioMin = 0;
let precioMax = Infinity;
let categoriasFiltradas = [];
let catsIndex = null;

// Slider
let rangeSlider;
let moneyFormat = wNumb({
    decimals: 0,
    thousand: ",",
    prefix: "$"
});

// Tipo fijo (seguridad)
const tipoFiltro = 'servicio'; // 'producto' | 'servicio' | null (ambos)

// Emergencia
let emergenciaSeleccionadas = new Set(); // '24_7' | 'carretera' | 'domicilio'

// Ubicación
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let datosEcuador = {};

function matchesEmergencySelection(prod) {
    if (emergenciaSeleccionadas.size === 0) return true;

    if (emergenciaSeleccionadas.has("24_7") && Number(prod.emergencia_24_7) !== 1) return false;
    if (emergenciaSeleccionadas.has("carretera") && Number(prod.emergencia_carretera) !== 1) return false;
    if (emergenciaSeleccionadas.has("domicilio") && Number(prod.emergencia_domicilio) !== 1) return false;

    return true;
}

function normalizarCadenaFiltro(value) {
    return String(value || "")
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function renderFilterSearchInput(tipo, placeholder, value) {
    return `<div class="mb-3"><input type="text" class="form-control form-control-sm filter-option-search-emergencia" data-filter-type="${tipo}" placeholder="${placeholder}" value="${String(value || '').replace(/"/g, '&quot;')}"></div>`;
}

function applyFilterOptionSearch(tipo, rawValue) {
    const selectorMap = {
        emergencia: "#filtro-emergencia",
        marca: "#filtro-marca",
        modelo: "#filtro-modelo",
        categoria: "#filtro-categorias",
        subcategoria: "#filtro-sub-categorias"
    };

    const containerSelector = selectorMap[tipo];
    if (!containerSelector) return;

    const query = normalizarCadenaFiltro(rawValue);
    const $container = $(containerSelector);

    $container.find(".filter-option-row").each(function () {
        const candidate = normalizarCadenaFiltro($(this).attr("data-filter-search-text") || "");
        $(this).toggle(!query || candidate.includes(query));
    });
}


/* ====================== Carga JSON Ecuador ====================== */
fetch('provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

/* ====================== Init ====================== */
$(document).ready(function () {

    actualizarIconoCarrito();

    $("#breadcrumb").append(`
    <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
    <span></span> Servicios de Emergencia
  `);

    // ✅ SOLO 1 API PRINCIPAL
    $.get("api/v1/fulmuv/getServiciosEmergenciaAll/All", function (returnedData) {
        if (!returnedData.error) {
            productosMaster = shuffleArray(returnedData.data || []);
            productosData = productosMaster;

            // Construcción inicial de filtros (desde MASTER)
            buildEmergenciaFilters(productosMaster);

            catsIndex = buildCatsAndSubcatsFromProductos(productosMaster);
            categoriasFiltradas = catsIndex.categoriasLista;
            renderCheckboxCategorias(categoriasFiltradas);

            // Slider inicial
            const precios = productosMaster.map(p => Number(p.precio_referencia)).filter(n => Number.isFinite(n) && n > 0);
            const maxPrecio = precios.length ? Math.max(...precios) : 0;
            inicializarSlider(maxPrecio);

            // Marca/Modelo
            buildMarcasYModelos(productosMaster);
            syncModelosConMarcas();

            // Render inicial
            renderEmpresas(productosMaster, 1);
        }
    }, 'json');

});


/* ====================== FILTROS: Marca (checkbox) ====================== */
function renderCheckboxMarcas(marcas) {
    // ❌ NO ocultar nunca
    $("#wrap-marca").show(); // asegúrate de tener este id en el wrapper

    // Si no hay marcas, muestro mensaje
    if (!Array.isArray(marcas) || marcas.length === 0) {
        $("#filtro-marca").html(`
          <div class="text-muted small">
            No hay marcas disponibles para los filtros seleccionados.
          </div>
        `);
        return;
    }

    let html = renderFilterSearchInput("marca", "Buscar marca", filtroTextoMarca) + `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="todos">
        <input class="form-check-input chk-marca" type="checkbox" id="marca-all" value="__all__" ${marcasSeleccionadas.size === 0 ? "checked" : ""}>
        <label class="form-check-label fw-normal" for="marca-all">Todos</label>
      </div>`;
    marcas.forEach(m => {
        const checked = marcasSeleccionadas.has(Number(m.id)) ? "checked" : "";
        html += `
          <div class="form-check mb-2 filter-option-row" data-filter-search-text="${normalizarCadenaFiltro(capitalizarPrimeraLetra(m.nombre))}">
            <input class="form-check-input chk-marca" type="checkbox"
                   id="marca-${m.id}" value="${m.id}" ${checked}>
            <label class="form-check-label fw-normal" for="marca-${m.id}">
              ${capitalizarPrimeraLetra(m.nombre)}
            </label>
          </div>`;
    });

    html += `<div id="msg-modelo-sin-data" class="text-muted small mt-2" style="display:none;">
                La marca seleccionada no tiene modelos disponibles
            </div>`;

    $("#filtro-marca").html(html);
    applyFilterOptionSearch("marca", filtroTextoMarca);

    $(document).off("change", ".chk-marca").on("change", ".chk-marca", function () {
        if ($(this).val() === "__all__") {
            marcasSeleccionadas.clear();
            $(".chk-marca").not(this).prop("checked", false);
            $(this).prop("checked", true);
            syncModelosConMarcas();
            refreshFiltersFromMaster(false);
            renderEmpresas(productosMaster, 1);
            return;
        }

        const id = Number($(this).val());
        if (this.checked) marcasSeleccionadas.add(id);
        else marcasSeleccionadas.delete(id);

        $("#marca-all").prop("checked", marcasSeleccionadas.size === 0);

        syncModelosConMarcas();
        refreshFiltersFromMaster(false); // 👈 no reconstruir marcas/modelos desde 0
        renderEmpresas(productosMaster, 1);
    });
}

// $(document).on('keydown', '.widget_search input', function (e) {
//     if (e.key === 'Enter' || e.keyCode === 13) {
//         e.preventDefault();
//         return false;
//     }
// });

// $(document).on('submit', '.widget_search form', function (e) {
//     e.preventDefault();
//     return false;
// });


/* ====================== FILTROS: Modelo (checkbox) ====================== */
function renderCheckboxModelos(modelos) {
    // ❌ NO ocultar nunca
    $("#wrap-modelo").show(); // asegúrate de tener este id en el wrapper

    if (!Array.isArray(modelos) || modelos.length === 0) {
        $("#filtro-modelo").html(`
          <div class="text-muted small">
            No hay modelos disponibles para los filtros seleccionados.
          </div>
        `);
        return;
    }

    let html = renderFilterSearchInput("modelo", "Buscar modelo", filtroTextoModelo) + `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="todos">
        <input class="form-check-input chk-modelo" type="checkbox" id="modelo-all" value="__all__" ${modelosSeleccionados.size === 0 ? "checked" : ""}>
        <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
      </div>`;
    modelos.forEach(mo => {
        const checked = modelosSeleccionados.has(Number(mo.id)) ? "checked" : "";
        html += `
          <div class="form-check mb-2 filter-option-row" data-filter-search-text="${normalizarCadenaFiltro(capitalizarPrimeraLetra(mo.nombre))}">
            <input class="form-check-input chk-modelo" type="checkbox"
                   id="modelo-${mo.id}" value="${mo.id}" ${checked}>
            <label class="form-check-label fw-normal" for="modelo-${mo.id}">
              ${capitalizarPrimeraLetra(mo.nombre)}
            </label>
          </div>`;
    });

    $("#filtro-modelo").html(html);
    applyFilterOptionSearch("modelo", filtroTextoModelo);

    $(document).off("change", ".chk-modelo").on("change", ".chk-modelo", function () {
        if ($(this).val() === "__all__") {
            modelosSeleccionados.clear();
            $(".chk-modelo").not(this).prop("checked", false);
            $(this).prop("checked", true);
            refreshFiltersFromMaster(false);
            renderEmpresas(productosMaster, 1);
            return;
        }

        const id = Number($(this).val());
        if (this.checked) modelosSeleccionados.add(id);
        else modelosSeleccionados.delete(id);

        $("#modelo-all").prop("checked", modelosSeleccionados.size === 0);

        refreshFiltersFromMaster(false);
        renderEmpresas(productosMaster, 1);
    });
}


/* ====================== Construir Marcas/Modelos ====================== */
function buildMarcasYModelos(data) {
    const marcasMap = new Map();  // id -> {id, nombre}
    const modelosMap = new Map(); // id -> {id, nombre}

    (data || []).forEach(p => {
        // MARCAS
        const marcaIds = parseIdsArray(p.id_marca);
        const marcaObjs = Array.isArray(p.marca) ? p.marca : [];

        marcaIds.forEach(id => {
            const nid = Number(id);
            if (!nid) return;
            if (!marcasMap.has(nid)) {
                const found = marcaObjs.find(m => Number(m.id) === nid);
                marcasMap.set(nid, { id: nid, nombre: found?.nombre || `Marca ${nid}` });
            }
        });

        // MODELOS
        const modeloIds = parseIdsArray(p.id_modelo);
        const modeloObjs = Array.isArray(p.modelo) ? p.modelo
            : Array.isArray(p.modelo_productoo) ? p.modelo_productoo
                : Array.isArray(p.modelo_producto) ? [p.modelo_producto]
                    : [];

        modeloIds.forEach(id => {
            const nid = Number(id);
            if (!nid) return;
            if (!modelosMap.has(nid)) {
                const found = modeloObjs.find(m => Number(m.id) === nid);
                modelosMap.set(nid, { id: nid, nombre: found?.nombre || `Modelo ${nid}` });
            }
        });
    });

    marcasUnicas = Array.from(marcasMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
    modelosUnicos = Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));

    renderCheckboxMarcas(marcasUnicas);
    // Modelos iniciales (antes de sync con marcas)
    renderCheckboxModelos(modelosUnicos);
}


/* ====================== Modelos dependen de marcas ====================== */
function syncModelosConMarcas() {
    // ✅ base sin precio
    const base = filtrarDatasetParaOpciones(productosMaster, false);

    const modelosDisponibles = new Set();

    base.forEach(p => {
        const brands = parseIdsArray(p.id_marca);
        const models = parseIdsArray(p.id_modelo);

        const pasaMarca = (marcasSeleccionadas.size === 0)
            ? true
            : brands.some(b => marcasSeleccionadas.has(Number(b)));

        if (pasaMarca) models.forEach(m => modelosDisponibles.add(Number(m)));
    });

    modelosSeleccionados = new Set(
        Array.from(modelosSeleccionados).filter(id => modelosDisponibles.has(Number(id)))
    );

    const modelosMap = new Map();

    base.forEach(p => {
        const modeloIds = parseIdsArray(p.id_modelo);
        const modeloObjs = Array.isArray(p.modelo) ? p.modelo
            : Array.isArray(p.modelo_productoo) ? p.modelo_productoo
                : Array.isArray(p.modelo_producto) ? [p.modelo_producto]
                    : [];

        modeloIds.forEach(id => {
            const nid = Number(id);
            if (!modelosDisponibles.has(nid)) return;
            if (!modelosMap.has(nid)) {
                const found = modeloObjs.find(m => Number(m.id) === nid);
                modelosMap.set(nid, { id: nid, nombre: found?.nombre || `Modelo ${nid}` });
            }
        });
    });

    const modelosFiltrados = Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));

    if (marcasSeleccionadas.size > 0 && modelosFiltrados.length === 0) {
        $("#wrap-modelo").show();
        $("#filtro-modelo").html(`<div class="text-muted small">La marca seleccionada no tiene modelos disponibles.</div>`);
        return;
    }

    renderCheckboxModelos(modelosFiltrados);
}



/* ====================== Emergencia filters ====================== */
function buildEmergenciaFilters(dataset) {
    let show24 = false, showCarretera = false, showDomicilio = false;

    (dataset || []).forEach(p => {
        if (Number(p.emergencia_24_7) === 1) show24 = true;
        if (Number(p.emergencia_carretera) === 1) showCarretera = true;
        if (Number(p.emergencia_domicilio) === 1) showDomicilio = true;
    });

    let html = renderFilterSearchInput("emergencia", "Buscar tipo de emergencia", filtroTextoEmergencia) + `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="todos">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-all" value="__all__" ${emergenciaSeleccionadas.size === 0 ? "checked" : ""}>
        <label class="form-check-label fw-normal" for="emerg-all">Todos</label>
      </div>`;
    if (show24) {
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="24 7">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-24_7" value="24_7">
        <label class="form-check-label fw-normal" for="emerg-24_7">24/7</label>
      </div>`;
    }
    if (showCarretera) {
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="carretera">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-carretera" value="carretera">
        <label class="form-check-label fw-normal" for="emerg-carretera">Carretera</label>
      </div>`;
    }
    if (showDomicilio) {
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="domicilio">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-domicilio" value="domicilio">
        <label class="form-check-label fw-normal" for="emerg-domicilio">Domicilio</label>
      </div>`;
    }

    if (!html) {
        $("#filtro-emergencia").closest('.categories-dropdown-wrap').hide();
        return;
    }

    $("#filtro-emergencia").closest('.categories-dropdown-wrap').show();
    $("#filtro-emergencia").html(html);
    applyFilterOptionSearch("emergencia", filtroTextoEmergencia);

    $(document).off("change", ".chk-emerg").on("change", ".chk-emerg", function () {
        if ($(this).val() === "__all__") {
            emergenciaSeleccionadas.clear();
            $(".chk-emerg").not(this).prop("checked", false);
            $(this).prop("checked", true);
            refreshFiltersFromMaster();
            renderEmpresas(productosMaster, 1);
            return;
        }

        const v = $(this).val();
        if (this.checked) emergenciaSeleccionadas.add(v);
        else emergenciaSeleccionadas.delete(v);

        $("#emerg-all").prop("checked", emergenciaSeleccionadas.size === 0);

        refreshFiltersFromMaster();
        renderEmpresas(productosMaster, 1);
    });
}


/* ====================== Categorías (checkbox) ====================== */
function renderCheckboxCategorias(categorias) {
    if (!categorias || categorias.length === 0) {
        $("#filtro-categorias").empty();
        toggleFilterBlock('#filtro-categorias', false);
        return;
    }
    toggleFilterBlock('#filtro-categorias', true);

    let htmlVisible = `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="todos">
        <input class="form-check-input" type="checkbox" value="__all__" id="categoria-all" name="checkbox" ${subcategoriasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-all">Todos</label>
      </div>`;
    let htmlOcultas = '';
    const maxVisible = 10;

    categorias.forEach((cat, index) => {
        const checkboxHTML = `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="${normalizarCadenaFiltro(capitalizarPrimeraLetra(cat.nombre))}">
        <input class="form-check-input" type="checkbox" value="${cat.id_categoria}" id="categoria-${cat.id_categoria}" name="checkbox">
        <label class="form-check-label fw-normal" for="categoria-${cat.id_categoria}">
          ${capitalizarPrimeraLetra(cat.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += checkboxHTML;
        else htmlOcultas += checkboxHTML;
    });

    const htmlFinal = `
    ${renderFilterSearchInput("categoria", "Buscar categoría", filtroTextoCategoria)}
    <div class="checkbox-list-visible">${htmlVisible}</div>
    <div class="more_slide_open-cat" style="display:none;">${htmlOcultas}</div>
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

/* ✅ Categoría change: SOLO LOCAL, SIN API */
$(document).on("change", "input[name='checkbox']", function () {
    if ($(this).val() === "__all__") {
        subcategoriasSeleccionadas = [];
        subcategoriasHijas = [];
        subcategoriasHijasSeleccionadas = [];
        $("input[name='checkbox']").not(this).prop("checked", false);
        $(this).prop("checked", true);
        $("#filtro-sub-categorias").empty();
        $("#wrap-subcategorias").hide();
        refreshFiltersFromMaster(false);
        renderEmpresas(productosMaster, 1);
        return;
    }

    const id = Number($(this).val());

    if (this.checked) {
        if (!subcategoriasSeleccionadas.includes(id)) subcategoriasSeleccionadas.push(id);
    } else {
        subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(x => Number(x) !== id);
    }
    $("#categoria-all").prop("checked", subcategoriasSeleccionadas.length === 0);

    const idsUnicos = [...new Set(subcategoriasSeleccionadas.map(Number))];

    if (idsUnicos.length === 0) {
        subcategoriasHijas = [];
        subcategoriasHijasSeleccionadas = [];
        $("#filtro-sub-categorias").empty();
        $("#wrap-subcategorias").hide();
    } else {
        const subcats = buildSubcatsForSelected(idsUnicos);
        subcategoriasHijas = subcats;
        subcategoriasHijasSeleccionadas = [];
        renderCheckboxSubcategorias(subcats);
        $("#wrap-subcategorias").show();
    }

    // ✅ recalcula opciones sin “encerrarse” por precio
    refreshFiltersFromMaster(false);

    // ✅ actualiza servicios SIEMPRE
    renderEmpresas(productosMaster, 1);
});



/* ====================== Subcategorías (checkbox) ====================== */
function renderCheckboxSubcategorias(subcats) {
    if (!Array.isArray(subcats)) subcats = [];

    let htmlVisible = `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="todos">
        <input class="form-check-input" type="checkbox" value="__all__" id="subcat-all" name="checkbox-sub" ${subcategoriasHijasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="subcat-all">Todos</label>
      </div>`;
    let htmlOcultas = '';
    const maxVisible = 10;

    if (subcats.length === 0) {
        $("#filtro-sub-categorias").html(`<div class="text-muted small">No hay subcategorías disponibles.</div>`);
        $("#wrap-subcategorias").show(); // muéstralo igual con mensaje
        return;
    }
    $("#wrap-subcategorias").show();

    subcats.forEach((sc, index) => {
        const id = Number(sc.sub_categoria ?? sc.id_sub_categoria ?? sc.id_subcategoria ?? sc.id_sub_categoria);
        const item = `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${normalizarCadenaFiltro(capitalizarPrimeraLetra(sc.nombre))}">
        <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-sub">
        <label class="form-check-label fw-normal" for="subcat-${id}">
          ${capitalizarPrimeraLetra(sc.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += item;
        else htmlOcultas += item;
    });

    const htmlFinal = `
    ${renderFilterSearchInput("subcategoria", "Buscar sub categoría", filtroTextoSubcategoria)}
    <div class="checkbox-list-visible">
      ${htmlVisible || '<small class="text-muted">No hay sub-categorías para las categorías seleccionadas.</small>'}
    </div>
    <div class="more_slide_open-sub" style="display:none;">
      ${htmlOcultas}
    </div>
    ${htmlOcultas ? `
      <div class="more_categories-sub cursor-pointer">
        <span class="icon">+</span>
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}
  `;

    $("#filtro-sub-categorias").html(htmlFinal);
    applyFilterOptionSearch("subcategoria", filtroTextoSubcategoria);

    $(document)
        .off("click.moreSub", "#filtro-sub-categorias .more_categories-sub")
        .on("click.moreSub", "#filtro-sub-categorias .more_categories-sub", function () {
            $(this).toggleClass("show");
            $(this).prev(".more_slide_open-sub").slideToggle();
        });
}

/* ✅ Subcategoría change: SOLO LOCAL */
$(document).on("change", "input[name='checkbox-sub']", function () {
    if ($(this).val() === "__all__") {
        subcategoriasHijasSeleccionadas = [];
        $("input[name='checkbox-sub']").not(this).prop("checked", false);
        $(this).prop("checked", true);
        refreshFiltersFromMaster();
        renderEmpresas(productosMaster, 1);
        return;
    }

    const id = Number($(this).val());
    if ($(this).is(":checked")) {
        if (!subcategoriasHijasSeleccionadas.includes(id)) subcategoriasHijasSeleccionadas.push(id);
    } else {
        subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => x !== id);
    }

    $("#subcat-all").prop("checked", subcategoriasHijasSeleccionadas.length === 0);

    refreshFiltersFromMaster();
    renderEmpresas(productosMaster, 1);
});

$(document)
    .off("input.filterOptionSearchEmergencia")
    .on("input.filterOptionSearchEmergencia", ".filter-option-search-emergencia", function () {
        const tipo = $(this).data("filter-type");
        const value = $(this).val().trim();

        if (tipo === "emergencia") filtroTextoEmergencia = value;
        if (tipo === "marca") filtroTextoMarca = value;
        if (tipo === "modelo") filtroTextoModelo = value;
        if (tipo === "categoria") filtroTextoCategoria = value;
        if (tipo === "subcategoria") filtroTextoSubcategoria = value;

        applyFilterOptionSearch(tipo, value);
    });


/* ====================== Render principal con filtros ====================== */
function renderEmpresas(data, page = 1) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    let empresasFiltradas = (data || []).filter(prod => {
        const prodNombre = (prod.nombre || prod.titulo_producto || '').toLowerCase();
        const matchSearch = prodNombre.includes(searchText.toLowerCase()) ||
            String(prod.id_producto).includes(searchText);

        const matchTipo = categoriaInicialCumpleTipo(prod);

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);

        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        const matchSubHija = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);

        const matchEmergencia = matchesEmergencySelection(prod);

        const matchMarca = (marcasSeleccionadas.size === 0) || prodBrand.some(id => marcasSeleccionadas.has(Number(id)));
        const matchModelo = (modelosSeleccionados.size === 0) || prodModel.some(id => modelosSeleccionados.has(Number(id)));

        // Precio
        const precio = Number(prod.precio_referencia);
        const matchPrecio = precio >= precioMin && precio <= precioMax;

        // Ubicación
        const selProv = normalizarTexto(provinciaSel.nombre);
        const selCant = normalizarTexto(cantonSel.nombre);
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);

        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);

        return matchSearch && matchTipo && matchCat && matchSubHija
            && matchMarca && matchModelo
            && matchPrecio && matchProvincia && matchCanton
            && matchEmergencia;
    });

    // Ordenamiento
    if (sortOption === "mayor") {
        empresasFiltradas.sort((a, b) => ((b._searchScore || 0) - (a._searchScore || 0)) || (Number(b.precio_referencia) - Number(a.precio_referencia)));
    } else if (sortOption === "menor") {
        empresasFiltradas.sort((a, b) => ((b._searchScore || 0) - (a._searchScore || 0)) || (Number(a.precio_referencia) - Number(b.precio_referencia)));
    }

    // Paginación
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    // Render
    let listProductos = "";

    empresasPagina.forEach(function (productos) {
        const tieneDescuento = parseFloat(productos.descuento) > 0;
        const precioRef = Number(productos.precio_referencia) || 0;
        const precioDescuento = precioRef - (precioRef * Number(productos.descuento) / 100);

        let verificacion = "";
        if (Array.isArray(productos.verificacion) && productos.verificacion.length > 0) {
            if (productos.verificacion[0].verificado == 1) {
                verificacion = `
          <span class="fw-bold product-category" style="font-size: 12px;">
            <i class="fi-rs-check ms-1"></i> Vendedor Verificado
          </span>`;
            }
        }

        listProductos += `
      <div class="col-md-4 col-lg-3 col-sm-4 col-6 mb-4 d-flex">
        <div class="product-cart-wrap w-100 d-flex flex-column">
          <div class="product-img-action-wrap text-center">
            <div class="product-img product-img-zoom">
              <a href="detalle_productos.php?q=${productos.id_producto}" target="_blank" rel="noopener noreferrer">
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
            <div class="text-end">${verificacion}</div>
            <h2 class="text-center">
              <a href="detalle_productos.php?q=${productos.id_producto}" target="_blank" rel="noopener noreferrer"
                 onclick="irADetalleProductoConTerminos(${productos.id_producto}); return false;"
                 class="limitar-lineas mt-1 fw-normal">
                ${capitalizarPrimeraLetra(productos.titulo_producto || productos.nombre || '')}
              </a>
            </h2>
            <div class="product-price mb-2 mt-0 text-center">
              <span style="font-size:24px;">${formatPrecioSuperscript(tieneDescuento ? precioDescuento : precioRef)}</span>
              ${tieneDescuento ? `<span class="old-price">${formatPrecioSuperscript(precioRef)}</span>` : ''}
            </div>
          </div>
        </div>
      </div>`;
    });

    $("#totalProductosGeneral").text(empresasFiltradas.length);
    $(".product-grid").html(listProductos);
    renderPaginacion(empresasFiltradas.length, page);

    if (empresasPagina.length === 0) {
        $(".product-grid").html(`
      <div class="col-12 text-center">
        <p class="text-muted">No hay productos disponibles para este proveedor.</p>
      </div>
    `);
    }
}


/* ====================== Paginación ====================== */
function renderPaginacion(totalItems, currentPage) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    let pagHtml = '';

    for (let i = 1; i <= totalPages; i++) {
        pagHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
      <a class="page-link" href="#" data-page="${i}">${i}</a>
    </li>`;
    }

    $(".pagination").html(`
    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">
        <i class="fi-rs-arrow-small-left"></i>
      </a>
    </li>
    ${pagHtml}
    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">
        <i class="fi-rs-arrow-small-right"></i>
      </a>
    </li>
  `);
}

$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(productosMaster, currentPage);
    }
});


/* ====================== Búsqueda ====================== */
$(".widget_search input").on("input", function () {
    searchText = $(this).val().trim();
    refreshFiltersFromMaster();
    renderEmpresas(productosMaster, 1);
});


/* ====================== Sort / Items per page ====================== */
$(".sort-show li a").on("click", function (e) {
    e.preventDefault();

    $(".sort-show li a").removeClass("active");
    $(this).addClass("active");

    const value = $(this).data("value");
    itemsPerPage = value === "all" ? productosMaster.length : parseInt(value);

    $(".sort-by-product-wrap:first .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    currentPage = 1;
    renderEmpresas(productosMaster, currentPage);
});

$(".sort-order li a").on("click", function (e) {
    e.preventDefault();

    $(".sort-order li a").removeClass("active");
    $(this).addClass("active");

    sortOption = $(this).data("value");
    $(".sort-by-product-wrap:last .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    renderEmpresas(productosMaster, 1);
});


/* ====================== Slider ====================== */
function inicializarSlider(maxPrecio) {
    const sliderElement = document.getElementById("slider-range");
    if (!sliderElement) return;

    if (sliderElement.noUiSlider) {
        try { sliderElement.noUiSlider.destroy(); } catch (_) { }
    }

    if (!Number.isFinite(maxPrecio) || maxPrecio <= 0) {
        sliderElement.innerHTML = "";
        $("#slider-range-value1").text(moneyFormat?.to ? moneyFormat.to(0) : "$0");
        $("#slider-range-value2").text(moneyFormat?.to ? moneyFormat.to(0) : "$0");
        precioMin = 0;
        precioMax = Infinity;
        return;
    }

    noUiSlider.create(sliderElement, {
        start: [0, maxPrecio],
        step: 1,
        range: { min: 0, max: maxPrecio },
        format: moneyFormat,
        connect: true
    });

    sliderElement.noUiSlider.on("update", function (values) {
        $("#slider-range-value1").text(values[0]);
        $("#slider-range-value2").text(values[1]);

        precioMin = parseFloat(moneyFormat.from(values[0]));
        precioMax = parseFloat(moneyFormat.from(values[1]));

        // solo render (los filtros de opciones los recalculamos en otros eventos)
        renderEmpresas(productosMaster, 1);
    });
}


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
            refreshFiltersFromMaster();
            renderEmpresas(productosMaster, 1);
            return;
        }

        provinciaSel.id = codProv;
        provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);

        resetSelectCanton();
        cargarCantones(codProv);
        actualizarUIUbicacionPersistir();
        refreshFiltersFromMaster();
        renderEmpresas(productosMaster, 1);
    });
}

function cargarCantones(codProvincia) {
    const selectCanton = document.getElementById("selectCanton");
    if (!selectCanton) return;

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
            cantonSel = { id: null, nombre: null };
            actualizarUIUbicacionPersistir();
            refreshFiltersFromMaster();
            renderEmpresas(productosMaster, 1);
            return;
        }

        cantonSel.id = codCanton;
        cantonSel.nombre = capitalizarPrimeraLetra((datosEcuador[codProvincia].cantones[codCanton] || {}).canton || "");

        actualizarUIUbicacionPersistir();
        refreshFiltersFromMaster();
        renderEmpresas(productosMaster, 1);
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

    localStorage.setItem('ubicacionSeleccionada', JSON.stringify({ provincia: provinciaSel, canton: cantonSel }));
}

document.getElementById('guardarUbicacion')?.addEventListener('click', function () {
    actualizarUIUbicacionPersistir();
    refreshFiltersFromMaster();
    renderEmpresas(productosMaster, 1);

    const modalEl = document.getElementById('modalUbicacion');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    }
});

document.getElementById('limpiarUbicacion')?.addEventListener('click', () => {
    provinciaSel = { id: null, nombre: null };
    cantonSel = { id: null, nombre: null };

    const selectProvincia = document.getElementById('selectProvincia');
    if (selectProvincia) selectProvincia.value = '';
    resetSelectCanton();

    actualizarUIUbicacionPersistir();
    refreshFiltersFromMaster();
    renderEmpresas(productosMaster, 1);
});


/* ====================== Recalcular filtros desde MASTER ====================== */
function refreshFiltersFromMaster(rebuildBrandModel = true) {
    // ✅ base para opciones: SIN precio
    const base = filtrarDatasetParaOpciones(productosMaster, false);

    // Slider max según base (sin precio)
    const precios = (base || [])
        .map(p => Number(p.precio_referencia))
        .filter(n => Number.isFinite(n) && n > 0);

    const maxPrecio = precios.length ? Math.max(...precios) : 0;
    inicializarSlider(maxPrecio);

    // ✅ validar selecciones vigentes contra base (sin precio)
    validarMarcaModeloVigentes(base);

    if (rebuildBrandModel) {
        buildMarcasYModelos(base);
        syncModelosConMarcas(); // esta también la arreglamos abajo
    } else {
        syncModelosConMarcas();
    }
}



/* Base coherente para construir opciones (incluye precio actual y filtros generales) */
function filtrarDatasetParaOpciones(dataset, incluirPrecio = false) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        const prodNombreOpc = (prod.nombre || prod.titulo_producto || '').toLowerCase();
        const matchSearch = !searchText || prodNombreOpc.includes(searchText.toLowerCase()) || String(prod.id_producto).includes(searchText);

        const matchTipo = categoriaInicialCumpleTipo(prod);

        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);
        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        const matchSubHija = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);

        const matchEmergencia = matchesEmergencySelection(prod);

        // ✅ OJO: precio opcional SOLO para construir opciones si tú quisieras
        let matchPrecio = true;
        if (incluirPrecio) {
            const precio = Number(prod.precio_referencia);
            matchPrecio = precio >= precioMin && precio <= precioMax;
        }

        return matchSearch && matchTipo && matchProvincia && matchCanton && matchCat && matchSubHija && matchEmergencia && matchPrecio;
    });
}



function buildCatsAndSubcatsFromProductos(productos) {
    const catsMap = new Map();
    const subsPorCatMap = new Map();

    (productos || []).forEach(p => {
        // Categorías
        const catObjs = Array.isArray(p.categorias) ? p.categorias : [];
        let catIds = parseIdsArray(p.categoria ?? p.id_categoria);

        if (catObjs.length) {
            const idsDeObjs = catObjs.map(o => Number(o.id)).filter(Boolean);
            catIds = Array.from(new Set([...catIds, ...idsDeObjs]));
        }

        catIds.forEach(cid => {
            if (!cid) return;
            const nombreCat = (catObjs.find(o => Number(o.id) === Number(cid))?.nombre) || `Categoría ${cid}`;
            if (!catsMap.has(Number(cid))) {
                catsMap.set(Number(cid), { id_categoria: Number(cid), nombre: capitalizarPrimeraLetra(nombreCat) });
            }
            if (!subsPorCatMap.has(Number(cid))) subsPorCatMap.set(Number(cid), new Map());
        });

        // Subcategorías (OBJETOS si existen)
        const subObjs = Array.isArray(p.subcategorias) ? p.subcategorias
            : Array.isArray(p.sub_categorias) ? p.sub_categorias : [];

        // Subcategorías (IDS si vienen como string/array/campo)
        let subIds = parseIdsArray(p.sub_categoria ?? p.id_subcategoria ?? p.id_sub_categoria);

        // Si existen objetos, integro ids
        if (subObjs.length) {
            const idsDeObjs = subObjs.map(o => Number(o.id)).filter(Boolean);
            subIds = Array.from(new Set([...subIds, ...idsDeObjs]));
        }

        // Asociar subcats a cats del producto (fallback)
        catIds.forEach(cid => {
            const subMap = subsPorCatMap.get(Number(cid));
            if (!subMap) return;

            subIds.forEach(sid => {
                const nsid = Number(sid);
                if (!nsid) return;

                // nombre si vino en objetos, si no fallback
                const nombreSub = (subObjs.find(o => Number(o.id) === nsid)?.nombre) || `Subcategoría ${nsid}`;
                if (!subMap.has(nsid)) {
                    subMap.set(nsid, { id_sub_categoria: nsid, nombre: capitalizarPrimeraLetra(nombreSub) });
                }
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
        const arr = catsIndex.subsPorCat.get(Number(cid)) || [];
        arr.forEach(sc => {
            if (!unicas.has(Number(sc.id_sub_categoria))) unicas.set(Number(sc.id_sub_categoria), sc);
        });
    });
    return Array.from(unicas.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}


/* ====================== Helpers ====================== */
function normalizarTexto(s) {
    return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();
}

function categoriaInicialCumpleTipo(prod) {
    if (tipoFiltro == null) return true;

    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    if (!ids.length) return true;

    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return first ? (first.tipo === tipoFiltro) : true;
}

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

function hasIntersection(idsArr, idsSet) {
    for (const id of idsArr) if (idsSet.has(Number(id))) return true;
    return false;
}

function validarMarcaModeloVigentes(data) {
    const brands = new Set((data || []).flatMap(p => parseIdsArray(p.id_marca)).map(Number));
    const models = new Set((data || []).flatMap(p => parseIdsArray(p.id_modelo)).map(Number));

    marcasSeleccionadas = new Set(Array.from(marcasSeleccionadas).filter(id => brands.has(Number(id))));
    modelosSeleccionados = new Set(Array.from(modelosSeleccionados).filter(id => models.has(Number(id))));
}

function toggleFilterBlock(contentSelector, hasItems) {
    // Solo oculta/muestra el wrapper DEL MISMO FILTRO, no cualquier categories-dropdown-wrap
    const $content = $(contentSelector);
    const $wrap = $content.closest('.categories-dropdown-wrap'); // ahora apunta al wrapper correcto
    if ($wrap.length) {
        $wrap.toggle(Boolean(hasItems));
    } else {
        // fallback: no ocultes nada si no encuentra wrapper (para no romper marca/modelo)
        $content.toggle(Boolean(hasItems));
    }
}
