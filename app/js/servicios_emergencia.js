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
let activeFilterSection = "ubicacion-orden";

function setActiveFilterSection(sectionKey) {
    activeFilterSection = sectionKey || "ubicacion-orden";
    $(".filter-nav-item").removeClass("is-active");
    $(`.filter-nav-item[data-filter-target="${activeFilterSection}"]`).addClass("is-active");
    $(".filter-detail-panel").removeClass("is-active");
    $(`.filter-detail-panel[data-filter-panel="${activeFilterSection}"]`).addClass("is-active");
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

function updateFilterActiveCount() {
    const locationCount =
        (provinciaSel.id ? 1 : 0) +
        (cantonSel.id ? 1 : 0) +
        (sortOption !== "todos" ? 1 : 0);
    const emergencyCount = emergenciaSeleccionadas.size;
    const categoriesCount = subcategoriasSeleccionadas.length + subcategoriasHijasSeleccionadas.length;
    const marcasCount = marcasSeleccionadas.size;
    const modelosCount = modelosSeleccionados.size;
    const priceCount = (precioMin > 0 || precioMax !== Infinity) ? 1 : 0;
    const count =
        locationCount +
        emergencyCount +
        categoriesCount +
        marcasCount +
        modelosCount +
        priceCount +
        (searchText ? 1 : 0);

    $("#filterActiveCount").text(count);
    $("#filterGroupCountLocation").text(locationCount);
    $("#filterGroupCountEmergency").text(emergencyCount);
    $("#filterGroupCountCategories").text(categoriesCount);
    $("#filterGroupCountMarcas").text(marcasCount);
    $("#filterGroupCountModelos").text(modelosCount);
    $("#filterGroupCountPrice").text(priceCount);
}

function updateLocationButtonLabel() {
    const label = `<i class="fi-rs-marker me-1"></i> ${labelUbicacion()}`;
    $("#btnUbicacionPanel span").html(label);
}

function updateResultsButtonCount(total) {
    $("#filterResultsCount").text(Number.isFinite(total) ? total : 0);
}

function matchesEmergencySelection(prod) {
    if (emergenciaSeleccionadas.size === 0) return true;

    if (emergenciaSeleccionadas.has("24_7") && Number(prod.emergencia_24_7) !== 1) return false;
    if (emergenciaSeleccionadas.has("carretera") && Number(prod.emergencia_carretera) !== 1) return false;
    if (emergenciaSeleccionadas.has("domicilio") && Number(prod.emergencia_domicilio) !== 1) return false;

    return true;
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


/* ====================== Carga JSON Ecuador ====================== */
fetch('../provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

/* ====================== Init ====================== */
$(document).ready(function () {

    actualizarIconoCarrito();
    setActiveFilterSection("ubicacion-orden");
    updateFilterActiveCount();
    updateLocationButtonLabel();
    updateResultsButtonCount(0);

    $("#breadcrumb").append(`
    <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
    <span></span> Servicios de Emergencia
  `);

    // ✅ SOLO 1 API PRINCIPAL
    $.get("../api/v1/fulmuv/getServiciosEmergenciaAll/All", function (returnedData) {
        if (!returnedData.error) {
            productosMaster = returnedData.data || [];
            productosData = productosMaster;

            // Construcción inicial de filtros (desde MASTER)
            buildEmergenciaFilters(getBaseDatasetWithoutEmergency(productosMaster));

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

    $("#openFilterPanel").on("click", openFilterPanel);
    $("#closeFilterPanel, #filtersOverlay").on("click", closeFilterPanel);
    $("#applyFiltersPanel").on("click", closeFilterPanel);
    $(".filter-nav-item").on("click", function () {
        const sectionKey = $(this).data("filter-target");
        if (sectionKey) setActiveFilterSection(sectionKey);
    });

    $("#selectOrderPanel").on("change", function () {
        sortOption = $(this).val();
        renderEmpresas(productosMaster, 1);
        updateFilterActiveCount();
    });

    $("#selectShowPanel").on("change", function () {
        const value = $(this).val();
        itemsPerPage = value === "all" ? (productosMaster.length || 1) : parseInt(value, 10);
        currentPage = 1;
        renderEmpresas(productosMaster, 1);
    });

    $("#clearFiltersPanel").on("click", function () {
        sortOption = "todos";
        searchText = "";
        subcategoriasSeleccionadas = [];
        subcategoriasHijas = [];
        subcategoriasHijasSeleccionadas = [];
        marcasSeleccionadas.clear();
        modelosSeleccionados.clear();
        emergenciaSeleccionadas.clear();
        provinciaSel = { id: null, nombre: null };
        cantonSel = { id: null, nombre: null };

        $("#inputBusqueda").val("");
        $("#selectOrderPanel").val("todos");
        $("#selectShowPanel").val("20");
        $("input[name='checkbox'], input[name='checkbox-sub'], .chk-marca, .chk-modelo, .chk-emerg").prop("checked", false);
        $("#selectProvincia").val("");
        resetSelectCanton();

        const precios = productosMaster.map(p => Number(p.precio_referencia)).filter(n => Number.isFinite(n) && n > 0);
        const maxPrecio = precios.length ? Math.max(...precios) : 0;
        const sliderElement = document.getElementById("slider-range");
        if (sliderElement?.noUiSlider) sliderElement.noUiSlider.set([0, maxPrecio]);

        updateLocationButtonLabel();
        updateFilterActiveCount();
        refreshFiltersFromMaster();
        renderEmpresas(productosMaster, 1);
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $("#panelFiltros").hasClass("is-open")) {
            closeFilterPanel();
        }
    });

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

    let html = `
      <div class="form-check mb-2">
        <input class="form-check-input chk-marca" type="checkbox" id="marca-all" value="__all__" ${marcasSeleccionadas.size === 0 ? "checked" : ""}>
        <label class="form-check-label fw-normal" for="marca-all">Todos</label>
      </div>`;
    marcas.forEach(m => {
        const checked = marcasSeleccionadas.has(Number(m.id)) ? "checked" : "";
        html += `
          <div class="form-check mb-2">
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
        updateFilterActiveCount();
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

    let html = `
      <div class="form-check mb-2">
        <input class="form-check-input chk-modelo" type="checkbox" id="modelo-all" value="__all__" ${modelosSeleccionados.size === 0 ? "checked" : ""}>
        <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
      </div>`;
    modelos.forEach(mo => {
        const checked = modelosSeleccionados.has(Number(mo.id)) ? "checked" : "";
        html += `
          <div class="form-check mb-2">
            <input class="form-check-input chk-modelo" type="checkbox"
                   id="modelo-${mo.id}" value="${mo.id}" ${checked}>
            <label class="form-check-label fw-normal" for="modelo-${mo.id}">
              ${capitalizarPrimeraLetra(mo.nombre)}
            </label>
          </div>`;
    });

    $("#filtro-modelo").html(html);

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
        updateFilterActiveCount();
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

    if (!show24) emergenciaSeleccionadas.delete("24_7");
    if (!showCarretera) emergenciaSeleccionadas.delete("carretera");
    if (!showDomicilio) emergenciaSeleccionadas.delete("domicilio");

    let html = `
      <div class="form-check mb-2">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-all" value="__all__" ${emergenciaSeleccionadas.size === 0 ? "checked" : ""}>
        <label class="form-check-label fw-normal" for="emerg-all">Todos</label>
      </div>`;
    if (show24) {
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-24_7" value="24_7">
        <label class="form-check-label fw-normal" for="emerg-24_7">24/7</label>
      </div>`;
    }
    if (showCarretera) {
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-carretera" value="carretera">
        <label class="form-check-label fw-normal" for="emerg-carretera">Carretera</label>
      </div>`;
    }
    if (showDomicilio) {
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-emerg" type="checkbox" id="emerg-domicilio" value="domicilio">
        <label class="form-check-label fw-normal" for="emerg-domicilio">Domicilio</label>
      </div>`;
    }

    if (!html) {
        $("#wrap-emergencia").hide();
        emergenciaSeleccionadas.clear();
        return;
    }

    $("#wrap-emergencia").show();
    $("#filtro-emergencia").html(html);
    $("#emerg-24_7").prop("checked", emergenciaSeleccionadas.has("24_7"));
    $("#emerg-carretera").prop("checked", emergenciaSeleccionadas.has("carretera"));
    $("#emerg-domicilio").prop("checked", emergenciaSeleccionadas.has("domicilio"));

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
        updateFilterActiveCount();
    });
}


/* ====================== Categorías (checkbox) ====================== */
function renderCheckboxCategorias(categorias) {
    if (!categorias || categorias.length === 0) {
        $("#filtro-categorias").empty();
        return;
    }

    let htmlVisible = `
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="__all__" id="categoria-all" name="checkbox" ${subcategoriasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-all">Todos</label>
      </div>`;
    let htmlOcultas = '';
    const maxVisible = 10;

    categorias.forEach((cat, index) => {
        const checkboxHTML = `
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="${cat.id_categoria}" id="categoria-${cat.id_categoria}" name="checkbox">
        <label class="form-check-label fw-normal" for="categoria-${cat.id_categoria}">
          ${capitalizarPrimeraLetra(cat.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += checkboxHTML;
        else htmlOcultas += checkboxHTML;
    });

    const htmlFinal = `
    <div class="checkbox-list-visible">${htmlVisible}</div>
    <div class="more_slide_open-cat" style="display:none;">${htmlOcultas}</div>
    ${htmlOcultas ? `
      <div class="more_categories-cat cursor-pointer">
        <span class="icon"><i class="fi-rs-arrow-small-right"></i></span> 
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}`;

    $("#filtro-categorias").html(htmlFinal);

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
    updateFilterActiveCount();
});



/* ====================== Subcategorías (checkbox) ====================== */
function renderCheckboxSubcategorias(subcats) {
    if (!Array.isArray(subcats)) subcats = [];

    let htmlVisible = `
      <div class="form-check mb-2">
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
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-sub">
        <label class="form-check-label fw-normal" for="subcat-${id}">
          ${capitalizarPrimeraLetra(sc.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += item;
        else htmlOcultas += item;
    });

    const htmlFinal = `
    <div class="checkbox-list-visible">
      ${htmlVisible || '<small class="text-muted">No hay sub-categorías para las categorías seleccionadas.</small>'}
    </div>
    <div class="more_slide_open-sub" style="display:none;">
      ${htmlOcultas}
    </div>
    ${htmlOcultas ? `
      <div class="more_categories-sub cursor-pointer">
        <span class="icon"><i class="fi-rs-arrow-small-right"></i></span>
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}
  `;

    $("#filtro-sub-categorias").html(htmlFinal);

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
    updateFilterActiveCount();
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
        empresasFiltradas.sort((a, b) => Number(b.precio_referencia) - Number(a.precio_referencia));
    } else if (sortOption === "menor") {
        empresasFiltradas.sort((a, b) => Number(a.precio_referencia) - Number(b.precio_referencia));
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
        if (Array.isArray(productos.verificacion) && productos.verificacion.length > 0 && productos.verificacion[0].verificado == 1) {
            verificacion = `
                <span class="service-verify-badge">
                    <img src="../img/verificado_empresa.png" alt="Empresa verificada">
                </span>`;
        }

        listProductos += `
      <div class="service-card-modern">
        <div class="service-media-modern" onclick="irADetalleProductoConTerminos(${productos.id_producto})">
          ${verificacion}
          <img src="../admin/${productos.img_frontal}"
            onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';"
            class="service-main-img"
            alt="${capitalizarPrimeraLetra(productos.titulo_producto || productos.nombre || '')}">
          ${tieneDescuento ? `<div class="service-discount-badge">-${parseInt(productos.descuento)}%</div>` : ''}
        </div>

        <div class="service-info-modern">
          <h6 class="service-title-modern">
            ${capitalizarPrimeraLetra(productos.titulo_producto || productos.nombre || '')}
          </h6>
          <div class="d-flex justify-content-between align-items-end mt-2">
            <div class="service-price-modern">
              <span class="service-price-current">${formatoMoneda.format(tieneDescuento ? precioDescuento : precioRef)}</span>
              ${tieneDescuento ? `<span class="service-price-old">${formatoMoneda.format(precioRef)}</span>` : ''}
            </div>
            <button class="btn-circle-action" onclick="irADetalleProductoConTerminos(${productos.id_producto})">
              <i class="fi-rs-arrow-small-right"></i>
            </button>
          </div>
        </div>
      </div>`;
    });

    $("#totalProductosGeneral").text(empresasFiltradas.length);
    $("#countVendedores").text(`Encontramos ${empresasFiltradas.length} resultados`);
    updateResultsButtonCount(empresasFiltradas.length);
    $("#listaServiciosContainer").html(listProductos);
    renderPaginacion(empresasFiltradas.length, page);

    if (empresasPagina.length === 0) {
        $("#listaServiciosContainer").html(buildEmptyStateHtml(
            "No encontramos servicios",
            "Prueba con otra búsqueda o ajusta tus filtros para ver más servicios de emergencia."
        ));
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
$("#inputBusqueda").on("input", function () {
    searchText = $(this).val().trim();
    refreshFiltersFromMaster();
    renderEmpresas(productosMaster, 1);
    updateFilterActiveCount();
});


/* ====================== Sort / Items per page ====================== */
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
        updateFilterActiveCount();
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
    updateLocationButtonLabel();

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
    updateFilterActiveCount();

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
    updateFilterActiveCount();
});


/* ====================== Recalcular filtros desde MASTER ====================== */
function refreshFiltersFromMaster(rebuildBrandModel = true) {
    // ✅ base para opciones: SIN precio
    const base = filtrarDatasetParaOpciones(productosMaster, false);
    const baseEmergency = getBaseDatasetWithoutEmergency(productosMaster);

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

    buildEmergenciaFilters(baseEmergency);
}

function getBaseDatasetWithoutEmergency(dataset) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));
    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        const prodNombre = (prod.nombre || prod.titulo_producto || '').toLowerCase();
        const matchSearch = prodNombre.includes(searchText.toLowerCase()) ||
            String(prod.id_producto).includes(searchText);
        const matchTipo = categoriaInicialCumpleTipo(prod);

        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);
        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        const matchSubHija = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);

        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);
        const matchMarca = (marcasSeleccionadas.size === 0) || prodBrand.some(id => marcasSeleccionadas.has(Number(id)));
        const matchModelo = (modelosSeleccionados.size === 0) || prodModel.some(id => modelosSeleccionados.has(Number(id)));

        return matchSearch && matchTipo && matchProvincia && matchCanton && matchCat && matchSubHija && matchMarca && matchModelo;
    });
}



/* Base coherente para construir opciones (incluye precio actual y filtros generales) */
function filtrarDatasetParaOpciones(dataset, incluirPrecio = false) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        const prodNombre = (prod.nombre || prod.titulo_producto || '').toLowerCase();
        const matchSearch = prodNombre.includes(searchText.toLowerCase()) ||
            String(prod.id_producto).includes(searchText);

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
