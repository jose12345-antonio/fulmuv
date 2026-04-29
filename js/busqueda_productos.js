const SEARCH_PAGE_TYPES = {
    all: {
        title: "Todos",
        detailHref: function(item) {
            return getTypeConfig(resolveItemType(item)).detailHref(item);
        },
        detailAction: function(item) {
            return getTypeConfig(resolveItemType(item)).detailAction(item);
        },
        showBrand: true,
        showModel: true,
        showCategory: true,
        showServiceNames: true,
        showReference: true,
        showColor: true,
        showTapiceria: true,
        showYear: true
    },
    products: {
        title: "Productos",
        searchEndpoint: "api/v1/fulmuv/ProductosSearch/All",
        detailHref: function(item) {
            return `detalle_productos.php?q=${item.id_producto}`;
        },
        detailAction: function(item) {
            return `irADetalleProductoConTerminos(${Number(item.id_producto) || 0}); return false;`;
        },
        showBrand: true,
        showModel: true,
        showCategory: true,
        showServiceNames: false,
        showReference: false,
        showColor: false,
        showTapiceria: false,
        showYear: false
    },
    services: {
        title: "Servicios",
        searchEndpoint: "api/v1/fulmuv/serviciosProductosSearch/All",
        detailHref: function(item) {
            return `detalle_productos.php?q=${item.id_producto}`;
        },
        detailAction: function(item) {
            return `irADetalleProductoConTerminos(${Number(item.id_producto) || 0}); return false;`;
        },
        showBrand: true,
        showModel: true,
        showCategory: true,
        showServiceNames: true,
        showReference: false,
        showColor: false,
        showTapiceria: false,
        showYear: false
    },
    vehicles: {
        title: "Vehículos",
        searchEndpoint: "api/v1/fulmuv/vehiculos/All",
        detailHref: function(item) {
            return `detalle_vehiculo.php?q=${item.id_vehiculo}`;
        },
        detailAction: function(item) {
            return "return true;";
        },
        showBrand: true,
        showModel: true,
        showCategory: false,
        showServiceNames: false,
        showReference: true,
        showColor: true,
        showTapiceria: true,
        showYear: true
    }
};

let sliderMaxActual = null;

const SEARCH_PAGE_STATE = {
    search: "",
    secondarySearch: "",
    activeType: "products",
    currentPage: 1,
    itemsPerPage: 20,
    raw: {
        products: [],
        services: [],
        vehicles: []
    },
    filtered: [],
    optionSearch: {
        brand: "",
        model: "",
        category: "",
        serviceName: "",
        reference: "",
        color: "",
        tapiceria: ""
    },
    filters: {
        sort: "relevance",
        location: "",
        brands: [],
        models: [],
        categories: [],
        serviceNames: [],
        references: [],
        colors: [],
        tapicerias: [],
        yearMin: null,
        yearMax: null,
        priceMin: 0,
        priceMax: Infinity
    },
    slider: {
        min: 0,
        max: 0,
        selectedMin: 0,
        selectedMax: 0
    }
};

$(document).ready(function() {
    SEARCH_PAGE_STATE.search = String($("#search").val() || "").trim();
    SEARCH_PAGE_STATE.secondarySearch = "";
    SEARCH_PAGE_STATE.activeType = getTypeFromHash();
    $("#smartItemsPerPage").val(String(SEARCH_PAGE_STATE.itemsPerPage));

    bindSearchResultsEvents();
    loadSearchResults();
});

function bindSearchResultsEvents() {
    $(window).on("hashchange", function() {
        const nextType = getTypeFromHash();
        if (nextType === SEARCH_PAGE_STATE.activeType) return;

        SEARCH_PAGE_STATE.activeType = nextType;
        SEARCH_PAGE_STATE.currentPage = 1;
        sliderMaxActual = null;
        resetSearchFilters();
        const q = SEARCH_PAGE_STATE.secondarySearch || SEARCH_PAGE_STATE.search;
        loadDataForActiveType(q);
    });

    $(document).on("click", ".smart-type-card", function() {
        const nextType = $(this).data("search-type");
        if (!SEARCH_PAGE_TYPES[nextType] || nextType === SEARCH_PAGE_STATE.activeType) return;
        window.location.hash = getHashForType(nextType);
    });

    $(document).on("click", ".smart-filter-toggle", function() {
        const key = $(this).data("filter-toggle");
        const $card = $(`.smart-filter-card[data-filter-card="${key}"]`);
        const $visibleCards = $(".smart-filter-card:visible");
        const $firstVisible = $visibleCards.first();

        if ($firstVisible.length && $card.is($firstVisible) && $card.hasClass("is-open")) {
            return;
        }

        $card.toggleClass("is-open");
    });

    $("#smartResultsSearchInput").on("input", function() {
        SEARCH_PAGE_STATE.secondarySearch = $(this).val().trim();
        SEARCH_PAGE_STATE.currentPage = 1;
        $(".fulmuv-pgsearch-clear").toggleClass("is-visible", $(this).val().length > 0);
        renderSearchResultsPage();
    });

    $(document).on("click", ".fulmuv-pgsearch-clear", function() {
        $("#smartResultsSearchInput").val("").trigger("input");
    });

    $("#smartSortSelect").on("change", function() {
        SEARCH_PAGE_STATE.filters.sort = $(this).val();
        SEARCH_PAGE_STATE.currentPage = 1;
        renderSearchResultsPage();
    });

    $("#smartItemsPerPage").on("change", function() {
        SEARCH_PAGE_STATE.itemsPerPage = Number($(this).val()) || 20;
        SEARCH_PAGE_STATE.currentPage = 1;
        renderSearchResultsPage();
    });

    $("#smartLocationSelect").on("change", function() {
        SEARCH_PAGE_STATE.filters.location = $(this).val();
        SEARCH_PAGE_STATE.currentPage = 1;
        renderSearchResultsPage();
    });

    $("#smartFilterSearchBrand").on("input", function() {
        SEARCH_PAGE_STATE.optionSearch.brand = $(this).val().trim();
        renderBrandOptions();
    });

    $("#smartFilterSearchModel").on("input", function() {
        SEARCH_PAGE_STATE.optionSearch.model = $(this).val().trim();
        renderModelOptions();
    });

    $("#smartFilterSearchCategory").on("input", function() {
        SEARCH_PAGE_STATE.optionSearch.category = $(this).val().trim();
        renderCategoryOptions();
    });

    $("#smartFilterSearchServiceName").on("input", function() {
        SEARCH_PAGE_STATE.optionSearch.serviceName = $(this).val().trim();
        renderServiceNameOptions();
    });

    $("#smartFilterSearchReference").on("input", function() {
        SEARCH_PAGE_STATE.optionSearch.reference = $(this).val().trim();
        renderReferenceOptions();
    });

    $("#smartFilterSearchColor").on("input", function() {
        SEARCH_PAGE_STATE.optionSearch.color = $(this).val().trim();
        renderColorOptions();
    });

    $("#smartFilterSearchTapiceria").on("input", function() {
        SEARCH_PAGE_STATE.optionSearch.tapiceria = $(this).val().trim();
        renderTapiceriaOptions();
    });

    $("#smartYearMin, #smartYearMax").on("input change", function() {
        const minRaw = $("#smartYearMin").val();
        const maxRaw = $("#smartYearMax").val();
        const minYear = Number(minRaw);
        const maxYear = Number(maxRaw);

        SEARCH_PAGE_STATE.filters.yearMin = Number.isFinite(minYear) && minRaw !== "" ? minYear : null;
        SEARCH_PAGE_STATE.filters.yearMax = Number.isFinite(maxYear) && maxRaw !== "" ? maxYear : null;
        SEARCH_PAGE_STATE.currentPage = 1;
        renderSearchResultsPage();
    });

    $(document).on("change", ".smart-filter-checkbox", function() {
        const type = $(this).data("filter-type");
        const value = $(this).data("filterValue");
        if (value === "__ALL__") {
            SEARCH_PAGE_STATE.filters[type] = [];
            SEARCH_PAGE_STATE.currentPage = 1;
            renderSearchResultsPage();
            return;
        }

        const list = Array.isArray(SEARCH_PAGE_STATE.filters[type]) ? SEARCH_PAGE_STATE.filters[type].slice() : [];
        const index = list.indexOf(value);

        if (this.checked && index === -1) {
            list.push(value);
        } else if (!this.checked && index !== -1) {
            list.splice(index, 1);
        }

        SEARCH_PAGE_STATE.filters[type] = list;
        SEARCH_PAGE_STATE.currentPage = 1;
        renderSearchResultsPage();
    });

    $(document).on("click", "#searchResultsPagination .page-link", function(e) {
        e.preventDefault();
        const page = Number($(this).data("page"));
        const totalPages = Math.ceil(SEARCH_PAGE_STATE.filtered.length / SEARCH_PAGE_STATE.itemsPerPage);
        if (!page || page < 1 || page > totalPages || page === SEARCH_PAGE_STATE.currentPage) return;

        SEARCH_PAGE_STATE.currentPage = page;
        renderSearchResultsPage();
        $("html, body").animate({
            scrollTop: $(".smart-results-main").offset().top - 100
        }, 180);
    });
}

function loadSearchResults() {
    const storageKey = `fulmuvSmartSearch:${String(SEARCH_PAGE_STATE.search || "").toLowerCase()}`;
    try {
        const cached = sessionStorage.getItem(storageKey);
        if (cached) {
            const parsed = JSON.parse(cached);
            if (parsed && (Array.isArray(parsed.products) || Array.isArray(parsed.services) || Array.isArray(parsed.vehicles))) {
                SEARCH_PAGE_STATE.raw.products = Array.isArray(parsed.products) ? parsed.products : [];
                SEARCH_PAGE_STATE.raw.services = Array.isArray(parsed.services) ? parsed.services : [];
                SEARCH_PAGE_STATE.raw.vehicles = Array.isArray(parsed.vehicles) ? parsed.vehicles : [];
                resetSearchFilters();
                renderSearchResultsPage();
                return;
            }
        }
    } catch (e) {}

    $.when(
        $.post(SEARCH_PAGE_TYPES.products.searchEndpoint, { search: SEARCH_PAGE_STATE.search }, null, "json"),
        $.post(SEARCH_PAGE_TYPES.services.searchEndpoint, { search: SEARCH_PAGE_STATE.search }, null, "json"),
        $.get(SEARCH_PAGE_TYPES.vehicles.searchEndpoint, null, null, "json")
    )
        .done(function(productsResponse, servicesResponse, vehiclesResponse) {
            const productsRes = productsResponse && productsResponse[0] ? productsResponse[0] : {};
            const servicesRes = servicesResponse && servicesResponse[0] ? servicesResponse[0] : {};
            const vehiclesRes = vehiclesResponse && vehiclesResponse[0] ? vehiclesResponse[0] : {};

            SEARCH_PAGE_STATE.raw.products = Array.isArray(productsRes.data) ? productsRes.data : [];
            SEARCH_PAGE_STATE.raw.services = Array.isArray(servicesRes.data) ? servicesRes.data : [];
            SEARCH_PAGE_STATE.raw.vehicles = Array.isArray(vehiclesRes.data) ? vehiclesRes.data : [];

            try {
                sessionStorage.setItem(storageKey, JSON.stringify({
                    products: SEARCH_PAGE_STATE.raw.products,
                    services: SEARCH_PAGE_STATE.raw.services,
                    vehicles: SEARCH_PAGE_STATE.raw.vehicles
                }));
            } catch (e) {}

            resetSearchFilters();
            renderSearchResultsPage();
        })
        .fail(function() {
            $("#searchResultsGrid").html("");
            $("#searchResultsEmpty").removeClass("d-none");
            $("#searchResultsEmptyText").text("No pudimos cargar la búsqueda. Intenta nuevamente.");
        });
}

function loadDataForActiveType(query) {
    const type = SEARCH_PAGE_STATE.activeType;
    let request;

    if (type === "vehicles") {
        request = $.get(SEARCH_PAGE_TYPES.vehicles.searchEndpoint, null, null, "json");
    } else if (type === "services") {
        request = $.post(SEARCH_PAGE_TYPES.services.searchEndpoint, { search: query }, null, "json");
    } else {
        request = $.post(SEARCH_PAGE_TYPES.products.searchEndpoint, { search: query }, null, "json");
    }

    request.done(function(res) {
        const data = res && Array.isArray(res.data) ? res.data : [];
        if (type === "products" || type === "services" || type === "vehicles") {
            SEARCH_PAGE_STATE.raw[type] = data;
        }
        SEARCH_PAGE_STATE.currentPage = 1;
        renderSearchResultsPage();
    });
}

function resetSearchFilters() {
    SEARCH_PAGE_STATE.filters.sort = "relevance";
    SEARCH_PAGE_STATE.filters.location = "";
    SEARCH_PAGE_STATE.filters.brands = [];
    SEARCH_PAGE_STATE.filters.models = [];
    SEARCH_PAGE_STATE.filters.categories = [];
    SEARCH_PAGE_STATE.filters.serviceNames = [];
    SEARCH_PAGE_STATE.filters.references = [];
    SEARCH_PAGE_STATE.filters.colors = [];
    SEARCH_PAGE_STATE.filters.tapicerias = [];
    SEARCH_PAGE_STATE.filters.yearMin = null;
    SEARCH_PAGE_STATE.filters.yearMax = null;
    SEARCH_PAGE_STATE.filters.priceMin = 0;
    SEARCH_PAGE_STATE.filters.priceMax = Infinity;
    SEARCH_PAGE_STATE.optionSearch.brand = "";
    SEARCH_PAGE_STATE.optionSearch.model = "";
    SEARCH_PAGE_STATE.optionSearch.category = "";
    SEARCH_PAGE_STATE.optionSearch.serviceName = "";
    SEARCH_PAGE_STATE.optionSearch.reference = "";
    SEARCH_PAGE_STATE.optionSearch.color = "";
    SEARCH_PAGE_STATE.optionSearch.tapiceria = "";
    SEARCH_PAGE_STATE.secondarySearch = "";
    SEARCH_PAGE_STATE.currentPage = 1;

    $("#smartSortSelect").val("relevance");
    $("#smartLocationSelect").val("");
    $("#smartFilterSearchBrand").val("");
    $("#smartFilterSearchModel").val("");
    $("#smartFilterSearchCategory").val("");
    $("#smartFilterSearchServiceName").val("");
    $("#smartFilterSearchReference").val("");
    $("#smartFilterSearchColor").val("");
    $("#smartFilterSearchTapiceria").val("");
    $("#smartYearMin").val("");
    $("#smartYearMax").val("");
    $("#smartResultsSearchInput").val("");
    $(".fulmuv-pgsearch-clear").removeClass("is-visible");
}

function renderSearchResultsPage() {
    const config = getTypeConfig(SEARCH_PAGE_STATE.activeType);
    const activeData = getActiveData();

    syncSliderBounds();
    renderTypeTabs();
    renderLocationOptions(activeData);
    renderBrandOptions();
    renderModelOptions();
    renderCategoryOptions();
    renderServiceNameOptions();
    renderReferenceOptions();
    renderColorOptions();
    renderTapiceriaOptions();
    syncYearInputs();
    renderFilterVisibility(config);
    renderSearchPlaceholder();

    const filtered = applySearchFilters(activeData, SEARCH_PAGE_STATE.activeType);
    SEARCH_PAGE_STATE.filtered = filtered;

    renderHeading(filtered.length);
    renderResultsGrid(filtered);
    renderPagination(filtered.length);
}

function renderTypeTabs() {
    $(".smart-type-card").each(function() {
        $(this).toggleClass("is-active", $(this).data("search-type") === SEARCH_PAGE_STATE.activeType);
    });
}

function renderFilterVisibility(config) {
    $('.smart-filter-card[data-filter-card="brand"]').toggle(config.showBrand);
    $('.smart-filter-card[data-filter-card="model"]').toggle(config.showModel);
    $('.smart-filter-card[data-filter-card="category"]').toggle(config.showCategory);
    $('.smart-filter-card[data-filter-card="service-name"]').toggle(config.showServiceNames);
    $('.smart-filter-card[data-filter-card="reference"]').toggle(config.showReference);
    $('.smart-filter-card[data-filter-card="color"]').toggle(config.showColor);
    $('.smart-filter-card[data-filter-card="tapiceria"]').toggle(config.showTapiceria);
    $('.smart-filter-card[data-filter-card="year"]').toggle(config.showYear);
    ensureFirstVisibleFilterOpen();
}

function ensureFirstVisibleFilterOpen() {
    const $visibleCards = $(".smart-filter-card:visible");
    if (!$visibleCards.length) return;

    let hasOpenVisible = false;
    $visibleCards.each(function() {
        if ($(this).hasClass("is-open")) {
            hasOpenVisible = true;
        }
    });

    if (!hasOpenVisible) {
        $visibleCards.first().addClass("is-open");
    }
}

function renderHeading(total) {
    const type = SEARCH_PAGE_STATE.activeType;
    const label = type === "services" ? "servicios" : type === "vehicles" ? "vehículos" : "productos";
    const query = SEARCH_PAGE_STATE.secondarySearch;
    let msg;
    if (total === 0) {
        msg = `No encontramos ${label} para ti`;
    } else {
        const rangeStart = Math.floor((total - 1) / 10) * 10 + 1;
        msg = `Encontramos más de ${rangeStart} ${label} para ti!`;
    }
    $("#searchResultsHeading").text(msg);
    $("#searchResultsEmptyText").text(`No encontramos coincidencias para "${query}" con los filtros actuales.`);
}

function renderSearchPlaceholder() {
    let placeholder = "Buscar por Nombre de Producto";

    if (SEARCH_PAGE_STATE.activeType === "all") {
        placeholder = "Buscar productos, servicios y vehículos";
    } else if (SEARCH_PAGE_STATE.activeType === "services") {
        placeholder = "Buscar por Nombre del Servicio";
    } else if (SEARCH_PAGE_STATE.activeType === "vehicles") {
        placeholder = "Buscar por marca, modelo, referencia, color o tapicería";
    }

    $("#smartResultsSearchInput").attr("placeholder", placeholder);
}

function renderLocationOptions(data) {
    const values = uniqueOptionList(collectOptionValues(data, function(item) {
        const provincia = titleCase(firstFromJsonLike(item.provincia || ""));
        const canton = titleCase(firstFromJsonLike(item.canton || ""));
        const location = [provincia, canton].filter(Boolean).join(" / ");
        return location ? [location] : [];
    }));

    const options = ['<option value="">Cambiar ubicacion</option>'].concat(
        values.map(function(value) {
            const selected = SEARCH_PAGE_STATE.filters.location === value ? " selected" : "";
            return `<option value="${escapeHtml(value)}"${selected}>${escapeHtml(value)}</option>`;
        })
    );

    $("#smartLocationSelect").html(options.join(""));
}

function renderBrandOptions() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'brands');
    renderCheckboxOptions({
        target: "#smartBrandOptions",
        filterType: "brands",
        values: uniqueOptionList(collectOptionValues(d, getBrandNames)),
        search: SEARCH_PAGE_STATE.optionSearch.brand,
        emptyText: "No hay marcas disponibles."
    });
}

function renderModelOptions() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'models');
    renderCheckboxOptions({
        target: "#smartModelOptions",
        filterType: "models",
        values: uniqueOptionList(collectOptionValues(d, getModelNames)),
        search: SEARCH_PAGE_STATE.optionSearch.model,
        emptyText: "No hay modelos disponibles."
    });
}

function renderCategoryOptions() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'categories');
    renderCheckboxOptions({
        target: "#smartCategoryOptions",
        filterType: "categories",
        values: uniqueOptionList(collectOptionValues(d, getCategoryNames)),
        search: SEARCH_PAGE_STATE.optionSearch.category,
        emptyText: "No hay categorías disponibles."
    });
}

function renderServiceNameOptions() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'serviceNames');
    renderCheckboxOptions({
        target: "#smartServiceNameOptions",
        filterType: "serviceNames",
        values: uniqueOptionList(collectOptionValues(d, getServiceNames)),
        search: SEARCH_PAGE_STATE.optionSearch.serviceName,
        emptyText: "No hay nombres de servicio disponibles."
    });
}

function renderReferenceOptions() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'references');
    renderCheckboxOptions({
        target: "#smartReferenceOptions",
        filterType: "references",
        values: uniqueOptionList(collectOptionValues(d, getReferenceNames)),
        search: SEARCH_PAGE_STATE.optionSearch.reference,
        emptyText: "No hay referencias disponibles."
    });
}

function renderColorOptions() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'colors');
    renderCheckboxOptions({
        target: "#smartColorOptions",
        filterType: "colors",
        values: uniqueOptionList(collectOptionValues(d, getColorNames)),
        search: SEARCH_PAGE_STATE.optionSearch.color,
        emptyText: "No hay colores disponibles."
    });
}

function renderTapiceriaOptions() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'tapicerias');
    renderCheckboxOptions({
        target: "#smartTapiceriaOptions",
        filterType: "tapicerias",
        values: uniqueOptionList(collectOptionValues(d, getTapiceriaNames)),
        search: SEARCH_PAGE_STATE.optionSearch.tapiceria,
        emptyText: "No hay tapicerías disponibles."
    });
}

function renderCheckboxOptions(config) {
    const searchTerm = normalizeText(config.search || "");
    const selectedValues = SEARCH_PAGE_STATE.filters[config.filterType] || [];
    const filteredValues = (config.values || []).filter(function(value) {
        if (!searchTerm) return true;
        return normalizeText(value).includes(searchTerm);
    });

    const html = filteredValues.length
        ? [`
                <label class="smart-filter-option" for="${config.filterType}-all">
                    <input
                        type="checkbox"
                        id="${config.filterType}-all"
                        class="smart-filter-checkbox"
                        data-filter-type="${config.filterType}"
                        data-filter-value="__ALL__"${selectedValues.length === 0 ? " checked" : ""}>
                    <span>Todos</span>
                </label>
            `].concat(filteredValues.map(function(value) {
            const checked = selectedValues.indexOf(value) !== -1 ? " checked" : "";
            const id = `${config.filterType}-${slugify(value)}`;
            return `
                <label class="smart-filter-option" for="${id}">
                    <input
                        type="checkbox"
                        id="${id}"
                        class="smart-filter-checkbox"
                        data-filter-type="${config.filterType}"
                        data-filter-value="${escapeHtml(value)}"${checked}>
                    <span>${escapeHtml(value)}</span>
                </label>
            `;
        })).join("")
        : `<div class="smart-filter-empty">${config.emptyText}</div>`;

    $(config.target).html(html);
}

function syncYearInputs() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'year');
    const years = (d || [])
        .map(function(item) { return Number(item.anio || 0); })
        .filter(function(value) { return Number.isFinite(value) && value > 0; });

    const currentYear = new Date().getFullYear();
    const minYear = years.length ? Math.min.apply(null, years) : 1900;
    const maxYear = years.length ? Math.max.apply(null, years) : currentYear;

    $("#smartYearMin, #smartYearMax").attr({ min: minYear, max: maxYear });
}

function syncSliderBounds() {
    const d = filterItemsForFacet(getActiveData(), SEARCH_PAGE_STATE.activeType, 'price');
    const prices = (d || [])
        .map(function(item) { return Number(item.precio_referencia || 0); })
        .filter(function(value) { return !Number.isNaN(value) && value >= 0; });

    const maxPrice = prices.length ? Math.max.apply(null, prices) : 0;
    SEARCH_PAGE_STATE.slider.min = 0;
    SEARCH_PAGE_STATE.slider.max = maxPrice;

    if (!Number.isFinite(SEARCH_PAGE_STATE.filters.priceMax) || SEARCH_PAGE_STATE.filters.priceMax === Infinity) {
        SEARCH_PAGE_STATE.filters.priceMax = maxPrice;
    } else {
        SEARCH_PAGE_STATE.filters.priceMax = Math.min(SEARCH_PAGE_STATE.filters.priceMax, maxPrice);
    }

    SEARCH_PAGE_STATE.filters.priceMin = Math.max(0, SEARCH_PAGE_STATE.filters.priceMin);
    if (SEARCH_PAGE_STATE.filters.priceMin > SEARCH_PAGE_STATE.filters.priceMax) {
        SEARCH_PAGE_STATE.filters.priceMin = SEARCH_PAGE_STATE.filters.priceMax;
    }

    renderPriceSlider(maxPrice);
}

function renderPriceSlider(maxPrice) {
    const sliderElement = document.getElementById("smart-price-slider");
    if (!sliderElement) return;

    if (sliderElement.noUiSlider && sliderMaxActual === maxPrice) return;

    if (sliderElement.noUiSlider) {
        try { sliderElement.noUiSlider.destroy(); } catch (_) {}
    }
    sliderMaxActual = maxPrice;
    sliderElement.innerHTML = "";

    const moneyFormat = window.moneyFormat || wNumb({
        decimals: 0,
        thousand: ",",
        prefix: "$"
    });

    if (maxPrice <= 0) {
        $("#smartPriceMinValue").text("$0");
        $("#smartPriceMaxValue").text("$0");
        SEARCH_PAGE_STATE.filters.priceMin = 0;
        SEARCH_PAGE_STATE.filters.priceMax = 0;
        return;
    }

    noUiSlider.create(sliderElement, {
        start: [SEARCH_PAGE_STATE.filters.priceMin, SEARCH_PAGE_STATE.filters.priceMax || maxPrice],
        connect: true,
        step: 1,
        range: { min: 0, max: maxPrice },
        format: moneyFormat
    });

    sliderElement.noUiSlider.on("update", function(values, handle, unencoded) {
        $("#smartPriceMinValue").text(values[0]);
        $("#smartPriceMaxValue").text(values[1]);
        SEARCH_PAGE_STATE.filters.priceMin = Number(unencoded[0]) || 0;
        SEARCH_PAGE_STATE.filters.priceMax = Number(unencoded[1]) || maxPrice;
    });

    sliderElement.noUiSlider.on("change", function() {
        SEARCH_PAGE_STATE.currentPage = 1;
        renderSearchResultsPage();
    });
}

/* Filtra items para construir opciones de facetas (sin ranking).
 * skipFacet: nombre del filtro que se omite para mostrar qué opciones quedan disponibles. */
function filterItemsForFacet(items, itemType, skipFacet) {
    const filters = SEARCH_PAGE_STATE.filters;
    const query = SEARCH_PAGE_STATE.secondarySearch;
    const terms = splitSearchTerms(query);

    return (items || []).filter(function(item) {
        const type = itemType || resolveItemType(item);
        const config = getTypeConfig(type);

        if (terms.length > 0) {
            const haystack = normalizeText(pickSearchableParts(item, type).join(" "));
            if (terms.filter(function(t) { return haystack.includes(t); }).length === 0) return false;
        }

        const location = [
            titleCase(firstFromJsonLike(item.provincia || "")),
            titleCase(firstFromJsonLike(item.canton || ""))
        ].filter(Boolean).join(" / ");
        const price = Number(item.precio_referencia || 0);
        const year  = Number(item.anio || 0);

        if (skipFacet !== 'brands'       && config.showBrand         && filters.brands.length       && !hasAnyMatch(filters.brands,       getBrandNames(item)))       return false;
        if (skipFacet !== 'models'       && config.showModel         && filters.models.length       && !hasAnyMatch(filters.models,       getModelNames(item)))       return false;
        if (skipFacet !== 'categories'   && config.showCategory      && filters.categories.length   && !hasAnyMatch(filters.categories,   getCategoryNames(item)))    return false;
        if (skipFacet !== 'serviceNames' && config.showServiceNames  && filters.serviceNames.length && !hasAnyMatch(filters.serviceNames,  getServiceNames(item)))    return false;
        if (skipFacet !== 'references'   && config.showReference     && filters.references.length   && !hasAnyMatch(filters.references,   getReferenceNames(item)))   return false;
        if (skipFacet !== 'colors'       && config.showColor         && filters.colors.length       && !hasAnyMatch(filters.colors,       getColorNames(item)))       return false;
        if (skipFacet !== 'tapicerias'   && config.showTapiceria     && filters.tapicerias.length   && !hasAnyMatch(filters.tapicerias,   getTapiceriaNames(item)))   return false;
        if (filters.location && location !== filters.location) return false;
        if (skipFacet !== 'year') {
            if (config.showYear && filters.yearMin !== null && Number.isFinite(year) && year > 0 && year < filters.yearMin) return false;
            if (config.showYear && filters.yearMax !== null && Number.isFinite(year) && year > 0 && year > filters.yearMax) return false;
        }
        if (skipFacet !== 'price' && (price < filters.priceMin || price > filters.priceMax)) return false;

        return true;
    });
}

function applySearchFilters(items, itemType) {
    const filters = SEARCH_PAGE_STATE.filters;
    const query = SEARCH_PAGE_STATE.secondarySearch;
    const ranked = [];

    (items || []).forEach(function(item, index) {
        const type = itemType || resolveItemType(item);
        const config = getTypeConfig(type);
        const brands = getBrandNames(item);
        const models = getModelNames(item);
        const categories = getCategoryNames(item);
        const serviceNames = getServiceNames(item);
        const references = getReferenceNames(item);
        const colors = getColorNames(item);
        const tapicerias = getTapiceriaNames(item);
        const location = [titleCase(firstFromJsonLike(item.provincia || "")), titleCase(firstFromJsonLike(item.canton || ""))].filter(Boolean).join(" / ");
        const price = Number(item.precio_referencia || 0);
        const year = Number(item.anio || 0);
        const ranking = buildRankingEntry(item, type, query, index);

        if (ranking.hasQuery && ranking.matchedTerms === 0) return;
        if (config.showBrand && filters.brands.length && !hasAnyMatch(filters.brands, brands)) return;
        if (config.showModel && filters.models.length && !hasAnyMatch(filters.models, models)) return;
        if (config.showCategory && filters.categories.length && !hasAnyMatch(filters.categories, categories)) return;
        if (config.showServiceNames && filters.serviceNames.length && !hasAnyMatch(filters.serviceNames, serviceNames)) return;
        if (config.showReference && filters.references.length && !hasAnyMatch(filters.references, references)) return;
        if (config.showColor && filters.colors.length && !hasAnyMatch(filters.colors, colors)) return;
        if (config.showTapiceria && filters.tapicerias.length && !hasAnyMatch(filters.tapicerias, tapicerias)) return;
        if (filters.location && location !== filters.location) return;
        if (config.showYear && filters.yearMin !== null && Number.isFinite(year) && year > 0 && year < filters.yearMin) return;
        if (config.showYear && filters.yearMax !== null && Number.isFinite(year) && year > 0 && year > filters.yearMax) return;
        if (price < filters.priceMin || price > filters.priceMax) return;

        ranked.push(ranking);
    });

    return sortRankedItems(ranked, filters.sort).map(function(entry) {
        return entry.item;
    });
}

function sortRankedItems(entries, sort) {
    const sorted = entries.slice();

    sorted.sort(function(a, b) {
        if (b.matchedTerms !== a.matchedTerms) return b.matchedTerms - a.matchedTerms;
        if (b.exactPhrase !== a.exactPhrase) return b.exactPhrase - a.exactPhrase;
        if (b.titleMatches !== a.titleMatches) return b.titleMatches - a.titleMatches;
        if (b.startsWithPhrase !== a.startsWithPhrase) return b.startsWithPhrase - a.startsWithPhrase;

        if (sort === "price_asc") {
            return Number(a.item.precio_referencia || 0) - Number(b.item.precio_referencia || 0);
        }

        if (sort === "price_desc") {
            return Number(b.item.precio_referencia || 0) - Number(a.item.precio_referencia || 0);
        }

        if (sort === "name_asc") {
            return getItemTitle(a.item).localeCompare(getItemTitle(b.item), "es", {
                sensitivity: "base"
            });
        }

        return a.index - b.index;
    });

    return sorted;
}

function buildRankingEntry(item, type, query, index) {
    const terms = splitSearchTerms(query);
    const normalizedQuery = normalizeText(query);
    const haystack = normalizeText(pickSearchableParts(item, type).join(" "));
    const titleHaystack = normalizeText([
        item.titulo_producto,
        item.nombre,
        item.titulo,
        item.marca_nombre,
        item.marca_referencia,
        item.modelo_nombre,
        item.modelo_referencia,
        firstFromJsonLike(item.referencias)
    ].filter(Boolean).join(" "));

    let matchedTerms = 0;
    let titleMatches = 0;

    terms.forEach(function(term) {
        if (haystack.includes(term)) matchedTerms += 1;
        if (titleHaystack.includes(term)) titleMatches += 1;
    });

    return {
        item: item,
        index: index,
        hasQuery: terms.length > 0,
        matchedTerms: matchedTerms,
        exactPhrase: normalizedQuery && haystack.includes(normalizedQuery) ? 1 : 0,
        startsWithPhrase: normalizedQuery && titleHaystack.startsWith(normalizedQuery) ? 1 : 0,
        titleMatches: titleMatches
    };
}

function renderResultsGrid(items) {
    const start = (SEARCH_PAGE_STATE.currentPage - 1) * SEARCH_PAGE_STATE.itemsPerPage;
    const visible = items.slice(start, start + SEARCH_PAGE_STATE.itemsPerPage);
    const $grid = $("#searchResultsGrid");
    const $empty = $("#searchResultsEmpty");

    if (!visible.length) {
        $grid.html("");
        $empty.removeClass("d-none");
        return;
    }

    $empty.addClass("d-none");
    $grid.html(visible.map(renderSearchCard).join(""));
}

function renderSearchCard(item) {
    const itemType = resolveItemType(item);
    const config = getTypeConfig(itemType);
    const href = config.detailHref(item);
    const title = getItemTitle(item, itemType);
    const subtitle = getItemSubtitle(item, itemType);
    const price = Number(item.precio_referencia || 0);
    const discount = Number(item.descuento || 0);
    const discounted = discount > 0 ? price - (price * discount / 100) : price;
    const verified = item.verificacion && Array.isArray(item.verificacion) && item.verificacion.length;
    const idValue = Number(item.id_producto || item.id_vehiculo) || 0;

    return `
        <div class="col-xl-3 col-lg-4 col-md-6 col-6 mb-4 d-flex">
            <div class="product-cart-wrap w-100 d-flex flex-column">
                <div class="product-img-action-wrap text-center">
                    <div class="product-img product-img-zoom">
                        <a href="${href}" target="_blank" rel="noopener noreferrer" onclick="${config.detailAction(item)}">
                            <img class="default-img img-fluid mb-1"
                                src="${buildAdminImage(item.img_frontal)}"
                                onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"
                                style="object-fit: contain; width: 100%; height: 210px">
                        </a>
                    </div>
                    ${discount > 0 ? `
                        <div class="product-badges product-badges-position product-badges-mrg">
                            <span class="best">-${parseInt(discount, 10)}%</span>
                        </div>
                    ` : ""}
                </div>
                <div class="product-content-wrap d-flex flex-column flex-grow-1 text-center px-2 pb-3">
                    <div class="small text-muted mb-1">${verified ? "✓ Vendedor Verificado" : "&nbsp;"}</div>
                    <h6 class="limitar-lineas mb-2 mt-1" style="font-weight:normal;">
                        <a href="${href}" target="_blank" rel="noopener noreferrer" onclick="${config.detailAction(item)}">${escapeHtml(title)}</a>
                    </h6>
                    <div class="small text-muted limitar-lineas mb-2">${escapeHtml(subtitle || " ")}</div>
                    <div class="product-price text-center mt-auto">
                        <span>${formatCurrency(discounted)}</span>
                        ${discount > 0 ? `<span class="old-price">${formatCurrency(price)}</span>` : ""}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / SEARCH_PAGE_STATE.itemsPerPage);
    const $pagination = $("#searchResultsPagination");

    if (totalPages <= 1) {
        $pagination.html("");
        return;
    }

    const pages = [];
    const current = SEARCH_PAGE_STATE.currentPage;
    const windowStart = Math.max(1, current - 1);
    const windowEnd = Math.min(totalPages, current + 1);

    pages.push(`
        <li class="page-item ${current === 1 ? "disabled" : ""}">
            <a class="page-link" href="#" data-page="1">
                <i class="fi-rs-angle-double-small-left"></i>
            </a>
        </li>
    `);
    pages.push(`
        <li class="page-item ${current === 1 ? "disabled" : ""}">
            <a class="page-link" href="#" data-page="${current - 1}">
                <i class="fi-rs-arrow-small-left"></i>
            </a>
        </li>
    `);

    if (windowStart > 1) {
        pages.push(pageNumberHtml(1, current));
        if (windowStart > 2) {
            pages.push(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let page = windowStart; page <= windowEnd; page += 1) {
        pages.push(pageNumberHtml(page, current));
    }

    if (windowEnd < totalPages) {
        if (windowEnd < totalPages - 1) {
            pages.push(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        pages.push(pageNumberHtml(totalPages, current));
    }

    pages.push(`
        <li class="page-item ${current === totalPages ? "disabled" : ""}">
            <a class="page-link" href="#" data-page="${current + 1}">
                <i class="fi-rs-arrow-small-right"></i>
            </a>
        </li>
    `);
    pages.push(`
        <li class="page-item ${current === totalPages ? "disabled" : ""}">
            <a class="page-link" href="#" data-page="${totalPages}">
                <i class="fi-rs-angle-double-small-right"></i>
            </a>
        </li>
    `);

    $pagination.html(pages.join(""));
}

function pageNumberHtml(page, current) {
    return `
        <li class="page-item ${current === page ? "active" : ""}">
            <a class="page-link" href="#" data-page="${page}">${page}</a>
        </li>
    `;
}

function getActiveData() {
    if (SEARCH_PAGE_STATE.activeType === "all") {
        return []
            .concat(tagItemsWithType(SEARCH_PAGE_STATE.raw.products, "products"))
            .concat(tagItemsWithType(SEARCH_PAGE_STATE.raw.services, "services"))
            .concat(tagItemsWithType(SEARCH_PAGE_STATE.raw.vehicles, "vehicles"));
    }

    return tagItemsWithType(SEARCH_PAGE_STATE.raw[SEARCH_PAGE_STATE.activeType], SEARCH_PAGE_STATE.activeType);
}

function hasAnyMatch(selected, values) {
    return selected.some(function(item) {
        return values.indexOf(item) !== -1;
    });
}

function getHashForType(type) {
    if (type === "all") return "todos";
    if (type === "services") return "servicios";
    if (type === "vehicles") return "vehiculos";
    return "productos";
}

function getTypeFromHash() {
    const hash = String(window.location.hash || "").replace("#", "").toLowerCase();
    if (hash === "servicios") return "services";
    if (hash === "vehiculos") return "vehicles";
    return "products";
}

function getTypeConfig(type) {
    return SEARCH_PAGE_TYPES[type] || SEARCH_PAGE_TYPES.products;
}

function resolveItemType(item) {
    return item && item.__searchType ? item.__searchType : SEARCH_PAGE_STATE.activeType;
}

function tagItemsWithType(items, type) {
    return (Array.isArray(items) ? items : []).map(function(item) {
        if (item && item.__searchType === type) return item;
        return Object.assign({}, item, { __searchType: type });
    });
}

function getBrandNames(item) {
    const brandIds = parseIdsArray(item.id_marca).map(String);
    const brandObjects = normalizeOptionObjects(item.marca)
        .concat(normalizeOptionObjects(item.marcaArray));
    const names = [];

    brandIds.forEach(function(id) {
        const found = brandObjects.find(function(entry) {
            return String(entry.id) === id;
        });
        names.push(found && found.nombre ? found.nombre : `Marca ${id}`);
    });

    return uniqueOptionList(names.concat([
        titleCase(item.marca_nombre || ""),
        titleCase(item.marca_referencia || "")
    ]));
}

function getModelNames(item) {
    const modelIds = parseIdsArray(item.id_modelo).map(String);
    const modelObjects = normalizeOptionObjects(item.modelo)
        .concat(normalizeOptionObjects(item.modeloArray))
        .concat(normalizeOptionObjects(item.modelo_producto))
        .concat(normalizeOptionObjects(item.modelo_productoo));
    const names = [];

    modelIds.forEach(function(id) {
        const found = modelObjects.find(function(entry) {
            return String(entry.id) === id;
        });
        names.push(found && found.nombre ? found.nombre : `Modelo ${id}`);
    });

    return uniqueOptionList(names.concat([
        titleCase(item.modelo_nombre || ""),
        titleCase(item.modelo_referencia || "")
    ]));
}

function getCategoryNames(item) {
    const categories = uniqueOptionList(
        readArrayNames(item.categorias)
            .concat(readArrayNames(item.categoria))
            .concat(readArrayNames(item.nombre_categoria))
    );
    const subcategories = uniqueOptionList(
        readArrayNames(item.subcategorias)
            .concat(readArrayNames(item.subcategoria))
            .concat(readArrayNames(item.sub_categorias))
            .concat(readArrayNames(item.nombre_sub_categoria))
    );
    return categories.concat(subcategories.filter(function(value) {
        return categories.indexOf(value) === -1;
    }));
}

function getServiceNames(item) {
    return uniqueOptionList([titleCase(item.titulo_producto || item.nombre || "")]);
}

function getReferenceNames(item) {
    return uniqueOptionList(readArrayNames(item.referencias).concat(readArrayNames(item.referencia)));
}

function getColorNames(item) {
    return uniqueOptionList(readArrayNames(item.colorArray).concat(readArrayNames(item.color)));
}

function getTapiceriaNames(item) {
    return uniqueOptionList(readArrayNames(item.tapiceriaArray).concat(readArrayNames(item.tapiceria)));
}

function readArrayNames(value) {
    if (Array.isArray(value)) {
        return value.map(function(entry) {
            if (typeof entry === "string" || typeof entry === "number") return titleCase(entry);
            if (entry && typeof entry === "object") {
                return titleCase(entry.nombre || entry.name || entry.referencia || entry.titulo || entry.valor || "");
            }
            return "";
        }).filter(Boolean);
    }

    if (value && typeof value === "object") {
        return readArrayNames([value]);
    }

    if (typeof value === "string") {
        try {
            const parsed = JSON.parse(value);
            return readArrayNames(parsed);
        } catch (e) {
            return value.split(",").map(function(part) {
                return titleCase(part);
            }).filter(Boolean);
        }
    }

    return [];
}

function parseIdsArray(value) {
    if (Array.isArray(value)) {
        return value.map(function(entry) {
            return Number(entry);
        }).filter(Boolean);
    }

    if (typeof value === "string") {
        try {
            const parsed = JSON.parse(value);
            return parseIdsArray(parsed);
        } catch (e) {
            return value.split(/[,\s]+/).map(function(part) {
                return Number(part);
            }).filter(Boolean);
        }
    }

    if (value === null || typeof value === "undefined" || value === "") {
        return [];
    }

    const numeric = Number(value);
    return Number.isFinite(numeric) && numeric > 0 ? [numeric] : [];
}

function normalizeOptionObjects(value) {
    if (Array.isArray(value)) {
        return value.map(function(entry) {
            if (!entry || typeof entry !== "object") return null;
            return {
                id: entry.id ?? entry.id_marca ?? entry.id_modelo ?? entry.id_modelos_autos ?? "",
                nombre: titleCase(entry.nombre || entry.name || "")
            };
        }).filter(function(entry) {
            return entry && (entry.id || entry.nombre);
        });
    }

    if (value && typeof value === "object") {
        return normalizeOptionObjects([value]);
    }

    if (typeof value === "string") {
        try {
            return normalizeOptionObjects(JSON.parse(value));
        } catch (e) {
            return [];
        }
    }

    return [];
}

function firstFromJsonLike(value) {
    if (Array.isArray(value)) {
        return value.map(firstFromJsonLike).filter(Boolean).join(" ");
    }

    if (value && typeof value === "object") {
        return [
            value.nombre,
            value.name,
            value.referencia,
            value.canton,
            value.provincia,
            value.valor
        ].filter(Boolean).join(" ");
    }

    const raw = String(value || "").trim();
    if (!raw) return "";

    try {
        return firstFromJsonLike(JSON.parse(raw));
    } catch (e) {
        return raw;
    }
}

function pickSearchableParts(item, type) {
    if (type === "vehicles") {
        return [
            item.nombre,
            item.titulo_producto,
            item.descripcion,
            item.tags,
            item.anio,
            item.kilometraje,
            item.marca_nombre,
            item.marca_referencia,
            item.modelo_nombre,
            item.modelo_referencia,
            firstFromJsonLike(item.color),
            firstFromJsonLike(item.colorArray),
            firstFromJsonLike(item.tapiceria),
            firstFromJsonLike(item.tapiceriaArray),
            firstFromJsonLike(item.tipo_autoo),
            firstFromJsonLike(item.tipo_auto),
            firstFromJsonLike(item.referencias),
            firstFromJsonLike(item.provincia),
            firstFromJsonLike(item.canton)
        ];
    }

    return [
        item.titulo_producto,
        item.nombre,
        item.descripcion,
        item.tags,
        item.nombre_categoria,
        item.nombre_sub_categoria,
        item.categoria,
        item.subcategoria,
        item.marca_nombre,
        item.marca_referencia,
        item.modelo_nombre,
        item.modelo_referencia,
        item.empresa_nombre,
        item.nombre_empresa,
        firstFromJsonLike(item.provincia),
        firstFromJsonLike(item.canton)
    ];
}

function splitSearchTerms(value) {
    return Array.from(new Set(
        normalizeText(value)
            .split(/\s+/)
            .map(function(term) { return term.trim(); })
            .filter(Boolean)
    ));
}

function collectOptionValues(items, getter) {
    return (items || []).reduce(function(acc, item) {
        return acc.concat(getter(item) || []);
    }, []);
}

function uniqueOptionList(values) {
    const map = new Map();

    (values || []).forEach(function(value) {
        const clean = titleCase(value || "");
        if (!clean) return;
        const key = normalizeText(clean);
        if (!map.has(key)) {
            map.set(key, clean);
        }
    });

    return Array.from(map.values()).sort(function(a, b) {
        return a.localeCompare(b, "es", { sensitivity: "base" });
    });
}

function normalizeText(value) {
    return String(value || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
        .toLowerCase();
}

function getItemTitle(item, type) {
    if (type === "vehicles") {
        return titleCase([
            item.marca_nombre || item.marca_referencia || "",
            item.modelo_nombre || item.modelo_referencia || "",
            firstFromJsonLike(item.referencias || "")
        ].filter(Boolean).join(" "));
    }

    return titleCase(item.titulo_producto || item.nombre || item.titulo || "");
}

function getItemSubtitle(item, type) {
    if (type === "vehicles") {
        return [
            item.anio ? `Año ${item.anio}` : null,
            firstFromJsonLike(item.color || item.colorArray),
            firstFromJsonLike(item.tapiceria || item.tapiceriaArray)
        ].filter(Boolean).join(" · ");
    }

    const description = String(item.descripcion || "").replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    if (description) return description;

    return [
        firstFromJsonLike(item.provincia),
        firstFromJsonLike(item.canton)
    ].filter(Boolean).join(" / ");
}

function titleCase(value) {
    const text = String(value || "").trim();
    if (!text) return "";

    if (typeof window.fulmuvTitleCase === "function") {
        return window.fulmuvTitleCase(text);
    }

    return text
        .toLowerCase()
        .split(/\s+/)
        .map(function(word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        })
        .join(" ");
}

function slugify(value) {
    return normalizeText(value).replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
}

function buildAdminImage(path) {
    const src = String(path || "").trim();
    if (!src) return "img/FULMUV-NEGRO.png";
    if (/^https?:\/\//i.test(src)) return src;
    if (src.startsWith("admin/")) return src;
    if (src.startsWith("/admin/")) return src.substring(1);
    return `admin/${src.replace(/^\/+/, "")}`;
}

function formatPrecioSuperscript(valor) {
    const num = Number(valor) || 0;
    const entero = Math.floor(num);
    const centavos = Math.round((num - entero) * 100).toString().padStart(2, '0');
    const enteroFormateado = entero.toLocaleString('es-EC');
    return `<span style="font-size:0.6em;font-weight:400;vertical-align:middle;margin-right:1px;">US$</span><strong>${enteroFormateado}</strong><span style="font-size:0.55em;font-weight:400;position:relative;top:-0.4em;margin-left:1px;">,${centavos}</span>`;
}

function formatCurrency(value) {
    return formatPrecioSuperscript(value);
}

function escapeHtml(value) {
    return String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}
