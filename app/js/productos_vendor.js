let itemsPerPage = 20;
let currentPage = 1;
let productosData = [];
let sortOption = "todos";
let searchText = "";
let categoriasSeleccionadas = [];
let subcategoriasSeleccionadas = [];
let marcasSeleccionadas = [];
let modelosSeleccionados = [];
let precioMin = 0;
let precioMax = Infinity;
let catsIndex = null;
let allPriceMax = 0;
let tipoItemSeleccionado = "producto";
let condicionSeleccionadas = [];
let tipoAutoSeleccionados = [];
let colorSeleccionados = [];
let tapiceriaSeleccionados = [];
let climatizacionSeleccionados = [];
let referenciasSeleccionadas = [];
let nombresServiciosSeleccionados = [];
let anioMinSel = null;
let anioMaxSel = null;

let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let datosEcuador = {};
let empresaActual = null;
let activeFilterSection = "ubicacion-orden";

const id_empresa = $("#id_empresa").val();
const moneyFormat = wNumb({
    decimals: 0,
    thousand: ",",
    prefix: "$"
});

function obtenerSessionKeyTracking() {
    const key = "fulmuv_tracking_session";
    let value = localStorage.getItem(key);

    if (!value) {
        value = `track_${Date.now()}_${Math.random().toString(36).slice(2, 12)}`;
        localStorage.setItem(key, value);
    }

    return value;
}

function obtenerIdUsuarioTracking() {
    const raw = ($("#id_usuario_session").val() || "0").toString().trim();
    return /^\d+$/.test(raw) ? raw : "0";
}

function registrarInteraccionEmpresaApp(tipoEvento, payload = {}) {
    if (!id_empresa || !tipoEvento) return;

    $.ajax({
        url: "../api/track_producto_interaccion.php",
        method: "POST",
        dataType: "json",
        data: {
            id_producto: id_empresa,
            tipo_evento: tipoEvento,
            cantidad: payload.cantidad || 1,
            session_key: obtenerSessionKeyTracking(),
            detalle_url: window.location.href,
            referencia: document.referrer || "",
            metadata: JSON.stringify(payload.metadata || {}),
            tipo: "empresa",
            id_usuario: obtenerIdUsuarioTracking()
        }
    });
}

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

function updateFilterActiveCount() {
    const locationCount =
        (provinciaSel.id ? 1 : 0) +
        (cantonSel.id ? 1 : 0) +
        (sortOption !== "todos" ? 1 : 0);
    const typeCount = tipoItemSeleccionado !== "producto" ? 1 : 0;
    const categoriesCount = categoriasSeleccionadas.length + subcategoriasSeleccionadas.length;
    const marcasCount = marcasSeleccionadas.length;
    const modelosCount = modelosSeleccionados.length;
    const nombresServiciosCount = nombresServiciosSeleccionados.length;
    const attributesCount = marcasCount + modelosCount + nombresServiciosCount;
    const vehicleCount =
        condicionSeleccionadas.length +
        tipoAutoSeleccionados.length +
        colorSeleccionados.length +
        tapiceriaSeleccionados.length +
        climatizacionSeleccionados.length +
        referenciasSeleccionadas.length +
        (anioMinSel ? 1 : 0) +
        (anioMaxSel ? 1 : 0);
    const priceCount = (precioMin > 0 || (Number.isFinite(precioMax) && allPriceMax > 0 && precioMax < allPriceMax)) ? 1 : 0;
    const count =
        typeCount +
        categoriesCount +
        attributesCount +
        vehicleCount +
        (searchText ? 1 : 0) +
        locationCount +
        priceCount;

    $("#filterActiveCount").text(count);
    $("#filterGroupCountLocation").text(locationCount);
    $("#filterGroupCountCategories").text(categoriesCount);
    $("#filterGroupCountMarcas").text(marcasCount);
    $("#filterGroupCountModelos").text(modelosCount);
    $("#filterGroupCountServiceNames").text(nombresServiciosCount);
    $("#filterGroupCountPrice").text(priceCount);
}

function updateResultsButtonCount(total) {
    const count = Number.isFinite(total) ? total : 0;
    $("#filterResultsCount").text(count);
}

function updateVehicleFilterVisibility() {
    const isVehicleMode = tipoItemSeleccionado === "vehiculo";
    $(".vehicle-filter-nav").toggle(isVehicleMode);
    $(".vehicle-filter-panel").toggleClass("vehicle-mode-enabled", isVehicleMode);

    if (!isVehicleMode) {
        const currentTarget = $(`.filter-nav-item.is-active`).data("filter-target");
        if ($(`.vehicle-filter-nav[data-filter-target="${currentTarget}"]`).length) {
            setActiveFilterSection("ubicacion-orden");
        }
    }
}

function normalizarTelefono(raw) {
    const clean = (raw || "").toString().replace(/\D/g, "");
    if (!clean) return "";
    if (clean.startsWith("593")) return clean;
    if (clean.startsWith("0")) return "593" + clean.slice(1);
    return clean;
}

function formatDisplayPhone(raw) {
    return (raw || "").toString().trim() || "No disponible";
}

function updateLocationButtonLabel() {
    $("#btnUbicacionPanel span").html(`<i class="fi-rs-marker me-1"></i> ${labelUbicacion()}`);
}

function buildEmptyStateHtml(title, message, iconClass = "fi-rs-box") {
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

function getEmpresaNombreBusqueda(prod) {
    return empresaActual?.nombre ||
        prod?.empresa_nombre ||
        prod?.nombre_empresa ||
        prod?.empresa ||
        prod?.tienda_nombre ||
        "";
}

function getSearchScoreForItem(prod) {
    const query = normalizarTexto(searchText);
    if (!query) return 0;

    const terms = query.split(/\s+/).filter(Boolean);
    const empresa = normalizarTexto(getEmpresaNombreBusqueda(prod));
    const provincia = normalizarTexto(prod?.provincia);
    const canton = normalizarTexto(prod?.canton);
    const fullText = [empresa, provincia, canton].filter(Boolean).join(" ");

    let score = 0;

    if (empresa.includes(query)) score += 300;
    else if (provincia.includes(query) || canton.includes(query)) score += 180;
    else if (fullText.includes(query)) score += 120;

    terms.forEach(term => {
        if (empresa.includes(term)) score += 40;
        else if (provincia.includes(term) || canton.includes(term)) score += 22;
        else if (fullText.includes(term)) score += 12;
    });

    return score;
}

function escapeHtml(text) {
    return (text || "")
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function normalizarUrlRed(url) {
    const raw = (url || "").toString().trim();
    if (!raw) return "";
    if (/^https?:\/\//i.test(raw)) return raw;
    return `https://${raw}`;
}

function buildSocialLinksHtml(empresa) {
    const links = [
        { key: "red_tiktok", icon: "fab fa-tiktok", label: "TikTok" },
        { key: "red_instagram", icon: "fab fa-instagram", label: "Instagram" },
        { key: "red_youtube", icon: "fab fa-youtube", label: "YouTube" },
        { key: "red_facebook", icon: "fab fa-facebook-f", label: "Facebook" },
        { key: "red_linkedln", icon: "fab fa-linkedin-in", label: "LinkedIn" },
        { key: "red_web", icon: "fas fa-globe", label: "Sitio web" }
    ];

    return links.map(({ key, icon, label }) => {
        const href = normalizarUrlRed(empresa?.[key]);
        if (!href) return "";
        return `<a href="${href}" class="store-social-link company-external-link" data-external-url="${href}" target="_blank" rel="noopener" aria-label="${label}" title="${label}"><i class="${icon}"></i></a>`;
    }).join("");
}

function renderCompanyDescriptionPreview(empresa) {
    const text = (empresa?.descripcion || "").toString().trim();
    const $preview = $("#descripcionEmpresaPreview");
    const $toggle = $("#toggleDescripcionEmpresa");

    $preview.text(text);
    if (!text) {
        $preview.hide();
        $toggle.hide();
        return;
    }

    $preview.show().addClass("is-collapsed");
    const needsToggle = text.length > 180;
    $toggle.text("Ver más").toggle(needsToggle);
}

function renderEmpresaInfo(data) {
    empresaActual = data || null;

    const nombre = capitalizarPrimeraLetra(data?.nombre || "Tienda");
    const tipo = capitalizarPrimeraLetra(data?.tipo_tienda || data?.tipo || "Tienda");
    const imagen = data?.img_path ? `../empresa/${data.img_path}` : "../img/FULMUV_LOGO-13.png";

    $("#nombreEmpresa").text(nombre);
    $("#imagenEmpresa").attr("src", imagen);
    $("#empresaTipoTienda").html(`<i class="fi-rs-shop"></i> ${tipo}`);
    $("#empresaSocialLinks").html(buildSocialLinksHtml(data));
    renderCompanyDescriptionPreview(data);

    const whatsappRaw = data?.whatsapp_contacto || data?.telefono_contacto || "";
    const whatsappDigits = normalizarTelefono(whatsappRaw);

    if (whatsappDigits) {
        const waHref = `https://wa.me/${whatsappDigits}`;
        $("#empresaWhatsappCta")
            .attr("href", waHref)
            .off("click.vendorWhatsapp")
            .on("click.vendorWhatsapp", function (e) {
                e.preventDefault();
                registrarInteraccionEmpresaApp("click_whatsapp", {
                    metadata: { origen: "productos_vendor_app_btn" }
                });
                abrirWhatsAppApp(whatsappDigits, `Hola, quiero comunicarme con ${nombre}.`, waHref);
            })
            .show();
    } else {
        $("#empresaWhatsappCta").hide();
    }
}

fetch("../provincia_canton_parroquia.json")
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

$(document).ready(function () {
    actualizarIconoCarrito();
    setActiveFilterSection("ubicacion-orden");
    updateFilterActiveCount();
    updateLocationButtonLabel();
    updateResultsButtonCount(0);

    $("#openFilterPanel").on("click", openFilterPanel);
    $("#closeFilterPanel, #minimizeFilterPanel, #filtersOverlay").on("click", closeFilterPanel);
    $(".filter-nav-item").on("click", function () {
        const sectionKey = $(this).data("filter-target");
        if (sectionKey) setActiveFilterSection(sectionKey);
    });

    $(document).on("click", "#toggleDescripcionEmpresa", function (e) {
        e.preventDefault();
        const expanded = $("#descripcionEmpresaPreview").toggleClass("is-collapsed").hasClass("is-collapsed");
        $(this).text(expanded ? "Ver más" : "Ver menos");
    });

    $(document).off("click.companyExternalLink").on("click.companyExternalLink", ".company-external-link", function (e) {
        const externalUrl = $(this).data("external-url") || $(this).attr("href");
        if (!externalUrl) {
            return;
        }

        e.preventDefault();
        if (typeof abrirEnlaceExternoApp === "function") {
            abrirEnlaceExternoApp(externalUrl);
            return;
        }

        window.open(externalUrl, "_blank", "noopener,noreferrer");
    });

    $("#typeSwitcher").on("click", "[data-item-type]", function () {
        tipoItemSeleccionado = $(this).data("item-type") || "producto";
        $("#typeSwitcher .type-chip").removeClass("is-active");
        $(this).addClass("is-active");
        currentPage = 1;
        updateVehicleFilterVisibility();
        refreshFiltersForCurrentLocation();
        renderProductos(productosData, 1);
        updateFilterActiveCount();
    });

    $("#inputBusqueda").on("input", function () {
        searchText = $(this).val().trim();
        currentPage = 1;
        refreshFiltersForCurrentLocation();
        renderProductos(productosData, 1);
        updateFilterActiveCount();
    });

    $("#selectOrderPanel").on("change", function () {
        sortOption = $(this).val();
        currentPage = 1;
        renderProductos(productosData, 1);
        updateFilterActiveCount();
    });

    $("#clearFiltersPanel").on("click", function () {
        clearAllFilters();
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $("#panelFiltros").hasClass("is-open")) {
            closeFilterPanel();
        }
    });

    $.get(`../api/v1/fulmuv/empresas/${id_empresa}`, function (res) {
        if (!res.error) {
            renderEmpresaInfo(res.data);
        }
    }, "json");

    $.post("../api/v1/fulmuv/productos/idEmpresa", { id_empresa }, function (res) {
        if (!res.error) {
            productosData = normalizarItemsEmpresa(res);
            allPriceMax = getMaxPrice(productosData);
            updateVehicleFilterVisibility();
            refreshFiltersForCurrentLocation();
            renderProductos(productosData, 1);
        }
    }, "json");
});

function clearAllFilters() {
    searchText = "";
    sortOption = "todos";
    tipoItemSeleccionado = "producto";
    categoriasSeleccionadas = [];
    subcategoriasSeleccionadas = [];
    marcasSeleccionadas = [];
    modelosSeleccionados = [];
    nombresServiciosSeleccionados = [];
    condicionSeleccionadas = [];
    tipoAutoSeleccionados = [];
    colorSeleccionados = [];
    tapiceriaSeleccionados = [];
    climatizacionSeleccionados = [];
    referenciasSeleccionadas = [];
    anioMinSel = null;
    anioMaxSel = null;
    provinciaSel = { id: null, nombre: null };
    cantonSel = { id: null, nombre: null };

    $("#inputBusqueda").val("");
    $("#selectOrderPanel").val("todos");
    $("#selectProvincia").val("");
    $("#anioMin").val("");
    $("#anioMax").val("");
    $("#typeSwitcher .type-chip").removeClass("is-active");
    $('#typeSwitcher [data-item-type="producto"]').addClass("is-active");
    resetSelectCanton();

    updateVehicleFilterVisibility();
    refreshFiltersForCurrentLocation();
    updateLocationButtonLabel();
    updateFilterActiveCount();
    currentPage = 1;
    renderProductos(productosData, 1);
}

function renderProductos(data, page = 1) {
    const source = filtrarPorUbicacionDataset(data);
    const selectedCatsSet = new Set(categoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasSeleccionadas.map(Number));
    let filtrados = source.filter(prod => {
        const itemType = getItemType(prod);
        const searchScore = getSearchScoreForItem(prod);

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);
        const precio = Number(prod.precio_referencia || 0);
        const anio = Number(prod.anio || 0);
        const matchCondicion = itemType !== "vehiculo" || condicionSeleccionadas.length === 0 || hasIntersectionText(getVehicleConditionNames(prod), condicionSeleccionadas);
        const matchTipoAuto = itemType !== "vehiculo" || tipoAutoSeleccionados.length === 0 || hasIntersectionText(getVehicleTipoAutoNames(prod), tipoAutoSeleccionados);
        const matchColor = itemType !== "vehiculo" || colorSeleccionados.length === 0 || hasIntersectionText(getVehicleColorNames(prod), colorSeleccionados);
        const matchTapiceria = itemType !== "vehiculo" || tapiceriaSeleccionados.length === 0 || hasIntersectionText(getVehicleTapiceriaNames(prod), tapiceriaSeleccionados);
        const matchClimatizacion = itemType !== "vehiculo" || climatizacionSeleccionados.length === 0 || hasIntersectionText(getVehicleClimatizacionNames(prod), climatizacionSeleccionados);
        const matchReferencia = itemType !== "vehiculo" || referenciasSeleccionadas.length === 0 || hasIntersectionText(getVehicleReferenciaNames(prod), referenciasSeleccionadas);
        const matchNombreServicio = itemType !== "servicio" || nombresServiciosSeleccionados.length === 0 || hasIntersectionText(getServiceNameOptions(prod), nombresServiciosSeleccionados);
        const matchAnioMin = itemType !== "vehiculo" || !anioMinSel || (anio > 0 && anio >= anioMinSel);
        const matchAnioMax = itemType !== "vehiculo" || !anioMaxSel || (anio > 0 && anio <= anioMaxSel);

        const matchTipo = itemType === tipoItemSeleccionado;
        const matchSearch = searchText.trim() === "" || searchScore > 0;
        const matchCat = itemType === "vehiculo" ? true : (selectedCatsSet.size === 0 || hasIntersection(prodCats, selectedCatsSet));
        const matchSub = itemType === "vehiculo" ? true : (selectedSubSet.size === 0 || hasIntersection(prodSubs, selectedSubSet));
        const matchMarca = marcasSeleccionadas.length === 0 || prodBrand.some(id => marcasSeleccionadas.includes(Number(id)));
        const matchModelo = modelosSeleccionados.length === 0 || prodModel.some(id => modelosSeleccionados.includes(Number(id)));
        const matchPrecio = precio >= precioMin && precio <= precioMax;

        return matchTipo && matchSearch && matchCat && matchSub && matchMarca && matchModelo && matchPrecio &&
            matchNombreServicio &&
            matchCondicion && matchTipoAuto && matchColor && matchTapiceria && matchClimatizacion && matchReferencia && matchAnioMin && matchAnioMax;
    });

    if (sortOption === "mayor") {
        filtrados.sort((a, b) => Number(b.precio_referencia || 0) - Number(a.precio_referencia || 0));
    } else if (sortOption === "menor") {
        filtrados.sort((a, b) => Number(a.precio_referencia || 0) - Number(b.precio_referencia || 0));
    } else if (searchText.trim()) {
        filtrados.sort((a, b) => getSearchScoreForItem(b) - getSearchScoreForItem(a));
    }

    const start = (page - 1) * itemsPerPage;
    const paginados = filtrados.slice(start, start + itemsPerPage);

    let html = "";
    paginados.forEach(prod => {
        const itemType = getItemType(prod);
        const itemId = getItemId(prod);
        const tieneDesc = Number(prod.descuento || 0) > 0;
        const precioBase = Number(prod.precio_referencia || 0);
        const precioFinal = tieneDesc ? precioBase - (precioBase * Number(prod.descuento || 0) / 100) : precioBase;
        const titulo = capitalizarPrimeraLetra(getTituloProductoOServicio(prod));
        const marcaTxt = getMarcaNombres(prod)[0] || "General";
        const detalleUrl = getDetalleUrl(prod);
        const onclickAction = itemType === "vehiculo"
            ? `return redirigirVehiculoDetalle(${itemId});`
            : `return redirigirProductoDetalle(${itemId});`;
        const img = prod.img_frontal ? `../admin/${prod.img_frontal}` : "../img/FULMUV_LOGO-13.png";
        const verificado = isVerifiedItem(prod);

        html += `
            <article class="product-card-modern">
                <a href="${detalleUrl}" onclick="${onclickAction}" class="product-card-link">
                    <div class="product-media">
                        ${tieneDesc ? `<span class="product-badge">-${parseInt(prod.descuento, 10)}%</span>` : ""}
                        ${verificado ? `<img src="../img/verificado_empresa.png" alt="Empresa verificada" class="product-verify-badge">` : ""}
                        <img src="${img}" alt="${titulo}" onerror="this.src='../img/FULMUV_LOGO-13.png';">
                    </div>
                    <div class="product-body">
                        <div class="product-brand">${capitalizarPrimeraLetra(marcaTxt)}</div>
                        <div class="product-title">${titulo}</div>
                        <div class="product-footer"> 
                            <div class="product-price">
                                <strong>${formatoMoneda.format(precioFinal)}</strong>
                                ${tieneDesc ? `<span class="old-price">${formatoMoneda.format(precioBase)}</span>` : ""}
                            </div>
                            <span class="product-cta"><i class="fi-rs-arrow-small-right"></i></span>
                        </div>
                    </div>
                </a>
            </article>`;
    });

    $("#listaProductosContainer").html(html || buildEmptyStateHtml(
        "No se encontraron productos",
        "Prueba con otro tipo, marca, modelo, categoria, subcategoria o rango de precio para ver resultados disponibles en esta tienda."
    ));
    updateResultsButtonCount(filtrados.length);
    $("#countProductos").text(`Encontramos ${filtrados.length} articulos`);
    $("#totalProductosGeneral").html(`<i class="fi-rs-box"></i> ${filtrados.length} articulos`);
    renderPaginacion(filtrados.length, page);
}

function renderPaginacion(totalItems, activePage) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    if (totalPages <= 1) {
        $(".pagination").html("");
        return;
    }

    let pagHtml = "";
    for (let i = 1; i <= totalPages; i++) {
        pagHtml += `<li class="page-item ${i === activePage ? "active" : ""}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
    }

    $(".pagination").html(`
        <li class="page-item ${activePage === 1 ? "disabled" : ""}">
            <a class="page-link" href="#" data-page="${activePage - 1}"><i class="fi-rs-arrow-small-left"></i></a>
        </li>
        ${pagHtml}
        <li class="page-item ${activePage === totalPages ? "disabled" : ""}">
            <a class="page-link" href="#" data-page="${activePage + 1}"><i class="fi-rs-arrow-small-right"></i></a>
        </li>
    `);
}

$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"), 10);
    if (!isNaN(page)) {
        currentPage = page;
        renderProductos(productosData, currentPage);
        $("html, body").animate({
            scrollTop: $("#listaProductosContainer").offset().top - 120
        }, 300);
    }
});

function renderCheckboxCategorias(categorias) {
    const $target = $("#filtro-categorias-panel");
    if (!categorias || categorias.length === 0) {
        $target.empty();
        toggleFilterBlock("categorias", false);
        return;
    }

    toggleFilterBlock("categorias", true);
    let html = `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="__all__" id="categoria-all" name="checkbox-categoria" ${categoriasSeleccionadas.length === 0 ? "checked" : ""}>
            <label class="form-check-label" for="categoria-all">Todos</label>
        </div>`;
    categorias.forEach(cat => {
        const checked = categoriasSeleccionadas.includes(Number(cat.id_categoria)) ? "checked" : "";
        html += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${cat.id_categoria}" id="categoria-${cat.id_categoria}" name="checkbox-categoria" ${checked}>
                <label class="form-check-label" for="categoria-${cat.id_categoria}">${capitalizarPrimeraLetra(cat.nombre)}</label>
            </div>`;
    });
    $target.html(html);
}

function renderCheckboxSubcategorias(subcats) {
    const $target = $("#filtro-sub-categorias");
    if (!subcats || subcats.length === 0) {
        $target.empty();
        $("#subcats-box").hide();
        return;
    }

    $("#subcats-box").show();
    let html = `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="__all__" id="subcat-all" name="checkbox-subcategoria" ${subcategoriasSeleccionadas.length === 0 ? "checked" : ""}>
            <label class="form-check-label" for="subcat-all">Todos</label>
        </div>`;
    subcats.forEach(sc => {
        const id = Number(sc.id_sub_categoria ?? sc.sub_categoria ?? sc.id_subcategoria);
        const checked = subcategoriasSeleccionadas.includes(id) ? "checked" : "";
        html += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-subcategoria" ${checked}>
                <label class="form-check-label" for="subcat-${id}">${capitalizarPrimeraLetra(sc.nombre)}</label>
            </div>`;
    });
    $target.html(html);
}

function renderVehicleCheckboxGroup(targetSelector, inputName, items, selectedValues) {
    const $target = $(targetSelector);
    if (!$target.length) return;

    const normalizedSelected = new Set((selectedValues || []).map(normalizarTexto));
    if (!items || items.length === 0) {
        $target.empty();
        return;
    }

    const html = items.map((item, index) => {
        const value = escapeHtml(item);
        const id = `${inputName}-${index}`;
        const checked = normalizedSelected.has(normalizarTexto(item)) ? "checked" : "";
        return `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${value}" id="${id}" name="${inputName}" ${checked}>
                <label class="form-check-label" for="${id}">${capitalizarPrimeraLetra(item)}</label>
            </div>`;
    }).join("");

    $target.html(html);
}

function renderServicioCheckboxGroup(targetSelector, inputName, items, selectedValues) {
    const $target = $(targetSelector);
    if (!$target.length) return;

    if (!items || items.length === 0) {
        $target.empty();
        toggleFilterBlock("nombres-servicio", false);
        return;
    }

    toggleFilterBlock("nombres-servicio", true);
    const selectedSet = new Set((selectedValues || []).map(normalizarTexto));

    const html = `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="__all__" id="${inputName}-all" name="${inputName}" ${selectedSet.size === 0 ? "checked" : ""}>
            <label class="form-check-label" for="${inputName}-all">Todos</label>
        </div>` + items.map((item, index) => {
        const value = escapeHtml(item);
        const id = `${inputName}-${index}`;
        const checked = selectedSet.has(normalizarTexto(item)) ? "checked" : "";
        return `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${value}" id="${id}" name="${inputName}" ${checked}>
                <label class="form-check-label" for="${id}">${capitalizarPrimeraLetra(item)}</label>
            </div>`;
    }).join("");

    $target.html(html);
}

function buildMarcasYModelos(marcasData, modelosData = marcasData) {
    const marcasMap = new Map();
    const modelosMap = new Map();

    (marcasData || []).forEach(prod => {
        parseIdsArray(prod.id_marca).forEach(id => {
            if (!marcasMap.has(id)) {
                const found = toArray(prod.marca).find(item => Number(item.id ?? item.id_marca) === Number(id));
                marcasMap.set(id, { id, nombre: found?.nombre || `Marca ${id}` });
            }
        });
    });

    (modelosData || []).forEach(prod => {
        parseIdsArray(prod.id_modelo).forEach(id => {
            if (!modelosMap.has(id)) {
                const found = [
                    ...toArray(prod.modelo),
                    ...toArray(prod.modelo_productoo),
                    ...toArray(prod.modelo_producto)
                ].find(item => Number(item.id ?? item.id_modelo ?? item.id_modelos_autos) === Number(id));
                modelosMap.set(id, { id, nombre: found?.nombre || `Modelo ${id}` });
            }
        });
    });

    renderCheckboxMarcas(Array.from(marcasMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre)));
    renderCheckboxModelos(Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre)));
}

function renderCheckboxMarcas(marcas) {
    const $target = $("#filtro-marca");
    if (!marcas || marcas.length === 0) {
        $target.empty();
        toggleFilterBlock("marca", false);
        return;
    }

    toggleFilterBlock("marca", true);
    let html = `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checkbox-marca" id="marca-all" value="__all__" ${marcasSeleccionadas.length === 0 ? "checked" : ""}>
            <label class="form-check-label" for="marca-all">Todas</label>
        </div>`;

    marcas.forEach(item => {
        html += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="checkbox-marca" id="marca-${item.id}" value="${item.id}" ${marcasSeleccionadas.includes(Number(item.id)) ? "checked" : ""}>
                <label class="form-check-label" for="marca-${item.id}">${capitalizarPrimeraLetra(item.nombre)}</label>
            </div>`;
    });
    $target.html(html);
}

function renderCheckboxModelos(modelos) {
    const $target = $("#filtro-modelo");
    if (!modelos || modelos.length === 0) {
        $target.empty();
        toggleFilterBlock("modelo", false);
        return;
    }

    toggleFilterBlock("modelo", true);
    let html = `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checkbox-modelo" id="modelo-all" value="__all__" ${modelosSeleccionados.length === 0 ? "checked" : ""}>
            <label class="form-check-label" for="modelo-all">Todos</label>
        </div>`;

    modelos.forEach(item => {
        html += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="checkbox-modelo" id="modelo-${item.id}" value="${item.id}" ${modelosSeleccionados.includes(Number(item.id)) ? "checked" : ""}>
                <label class="form-check-label" for="modelo-${item.id}">${capitalizarPrimeraLetra(item.nombre)}</label>
            </div>`;
    });
    $target.html(html);
}

$(document).on("change", "input[name='checkbox-categoria']", function () {
    if (this.value === "__all__") {
        categoriasSeleccionadas = [];
        subcategoriasSeleccionadas = [];
        $("input[name='checkbox-categoria']").not(this).prop("checked", false);
        $("input[name='checkbox-subcategoria']").prop("checked", false);
        this.checked = true;
        renderCheckboxSubcategorias([]);
        currentPage = 1;
        refreshFiltersForCurrentLocation();
        renderProductos(productosData, 1);
        updateFilterActiveCount();
        return;
    }

    const id = Number($(this).val());
    if ($(this).is(":checked")) {
        if (!categoriasSeleccionadas.includes(id)) categoriasSeleccionadas.push(id);
    } else {
        categoriasSeleccionadas = categoriasSeleccionadas.filter(item => item !== id);
    }
    $("#categoria-all").prop("checked", categoriasSeleccionadas.length === 0);

    const subcats = buildSubcatsForSelected(categoriasSeleccionadas);
    const validSubIds = new Set(subcats.map(item => Number(item.id_sub_categoria)));
    subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(idSub => validSubIds.has(idSub));
    renderCheckboxSubcategorias(subcats);

    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-subcategoria']", function () {
    if (this.value === "__all__") {
        subcategoriasSeleccionadas = [];
        $("input[name='checkbox-subcategoria']").not(this).prop("checked", false);
        this.checked = true;
        currentPage = 1;
        refreshFiltersForCurrentLocation();
        renderProductos(productosData, 1);
        updateFilterActiveCount();
        return;
    }

    const id = Number($(this).val());
    if ($(this).is(":checked")) {
        if (!subcategoriasSeleccionadas.includes(id)) subcategoriasSeleccionadas.push(id);
    } else {
        subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(item => item !== id);
    }
    $("#subcat-all").prop("checked", subcategoriasSeleccionadas.length === 0);
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-marca']", function () {
    if (this.value === "__all__") {
        marcasSeleccionadas = [];
        $("input[name='checkbox-marca']").not(this).prop("checked", false);
        this.checked = true;
    } else {
        const value = Number($(this).val());
        if ($(this).is(":checked")) {
            if (!marcasSeleccionadas.includes(value)) marcasSeleccionadas.push(value);
        } else {
            marcasSeleccionadas = marcasSeleccionadas.filter(item => item !== value);
        }
        $("#marca-all").prop("checked", marcasSeleccionadas.length === 0);
    }
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-modelo']", function () {
    if (this.value === "__all__") {
        modelosSeleccionados = [];
        $("input[name='checkbox-modelo']").not(this).prop("checked", false);
        this.checked = true;
    } else {
        const value = Number($(this).val());
        if ($(this).is(":checked")) {
            if (!modelosSeleccionados.includes(value)) modelosSeleccionados.push(value);
        } else {
            modelosSeleccionados = modelosSeleccionados.filter(item => item !== value);
        }
        $("#modelo-all").prop("checked", modelosSeleccionados.length === 0);
    }
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-condicion']", function () {
    condicionSeleccionadas = collectCheckedValues("checkbox-condicion");
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-tipo-auto']", function () {
    tipoAutoSeleccionados = collectCheckedValues("checkbox-tipo-auto");
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-color']", function () {
    colorSeleccionados = collectCheckedValues("checkbox-color");
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-tapiceria']", function () {
    tapiceriaSeleccionados = collectCheckedValues("checkbox-tapiceria");
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-climatizacion']", function () {
    climatizacionSeleccionados = collectCheckedValues("checkbox-climatizacion");
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-referencia']", function () {
    referenciasSeleccionadas = collectCheckedValues("checkbox-referencia");
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("change", "input[name='checkbox-nombre-servicio']", function () {
    if (this.value === "__all__") {
        nombresServiciosSeleccionados = [];
        $("input[name='checkbox-nombre-servicio']").not(this).prop("checked", false);
        this.checked = true;
    } else {
        const value = $(this).val();
        if ($(this).is(":checked")) {
            if (!nombresServiciosSeleccionados.some(item => normalizarTexto(item) === normalizarTexto(value))) {
                nombresServiciosSeleccionados.push(value);
            }
        } else {
            nombresServiciosSeleccionados = nombresServiciosSeleccionados.filter(item => normalizarTexto(item) !== normalizarTexto(value));
        }
        $("#checkbox-nombre-servicio-all").prop("checked", nombresServiciosSeleccionados.length === 0);
    }
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

$(document).on("input", "#anioMin, #anioMax", function () {
    anioMinSel = normalizeYearValue($("#anioMin").val());
    anioMaxSel = normalizeYearValue($("#anioMax").val());
    currentPage = 1;
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

function inicializarSlider(maxPrecio) {
    const sliderElement = document.getElementById("slider-range");
    if (!sliderElement) return;

    if (sliderElement.noUiSlider) {
        try { sliderElement.noUiSlider.destroy(); } catch (_) { }
    }

    if (!Number.isFinite(maxPrecio) || maxPrecio <= 0) {
        sliderElement.innerHTML = "";
        $("#slider-range-value1").text("$0");
        $("#slider-range-value2").text("$0");
        precioMin = 0;
        precioMax = Infinity;
        updateFilterActiveCount();
        return;
    }

    allPriceMax = maxPrecio;
    precioMin = 0;
    precioMax = maxPrecio;

    noUiSlider.create(sliderElement, {
        start: [0, maxPrecio],
        step: 1,
        range: { min: 0, max: maxPrecio },
        connect: true,
        format: moneyFormat
    });

    sliderElement.noUiSlider.on("update", function (values) {
        $("#slider-range-value1").text(values[0]);
        $("#slider-range-value2").text(values[1]);
        precioMin = parseFloat(moneyFormat.from(values[0]));
        precioMax = parseFloat(moneyFormat.from(values[1]));
        renderProductos(productosData, 1);
        updateFilterActiveCount();
    });
}

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

    selectProvincia.onchange = (e) => {
        const codProv = e.target.value || null;
        if (!codProv) {
            provinciaSel = { id: null, nombre: null };
            resetSelectCanton();
            updateLocationButtonLabel();
            refreshFiltersForCurrentLocation();
            renderProductos(productosData, 1);
            updateFilterActiveCount();
            return;
        }

        provinciaSel.id = codProv;
        provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);
        cargarCantones(codProv);
        updateLocationButtonLabel();
        refreshFiltersForCurrentLocation();
        renderProductos(productosData, 1);
        updateFilterActiveCount();
    };
}

function cargarCantones(codProvincia) {
    const selectCanton = document.getElementById("selectCanton");
    if (!selectCanton) return;

    selectCanton.innerHTML = '<option value="">Seleccione un canton</option>';
    const cantones = datosEcuador[codProvincia]?.cantones || {};

    Object.entries(cantones).forEach(([codCanton, objCanton]) => {
        const option = document.createElement("option");
        option.value = codCanton;
        option.textContent = capitalizarPrimeraLetra(objCanton.canton);
        selectCanton.appendChild(option);
    });

    selectCanton.onchange = (e) => {
        const codCanton = e.target.value || null;
        if (!codCanton) {
            cantonSel = { id: null, nombre: null };
        } else {
            cantonSel.id = codCanton;
            cantonSel.nombre = capitalizarPrimeraLetra(cantones[codCanton]?.canton || "");
        }

        updateLocationButtonLabel();
        refreshFiltersForCurrentLocation();
        renderProductos(productosData, 1);
        updateFilterActiveCount();
    };
}

function resetSelectCanton() {
    const selectCanton = document.getElementById("selectCanton");
    if (selectCanton) {
        selectCanton.innerHTML = '<option value="">Seleccione un canton</option>';
    }
    cantonSel = { id: null, nombre: null };
}

function labelUbicacion() {
    if (provinciaSel.nombre && cantonSel.nombre) {
        return `${provinciaSel.nombre} / ${cantonSel.nombre}`;
    }
    if (provinciaSel.nombre) {
        return provinciaSel.nombre;
    }
    return "Cambiar ubicacion";
}

document.getElementById("guardarUbicacion")?.addEventListener("click", function () {
    updateLocationButtonLabel();
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();

    const modalEl = document.getElementById("modalUbicacion");
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
});

document.getElementById("limpiarUbicacion")?.addEventListener("click", function () {
    provinciaSel = { id: null, nombre: null };
    cantonSel = { id: null, nombre: null };
    document.getElementById("selectProvincia").value = "";
    resetSelectCanton();
    updateLocationButtonLabel();
    refreshFiltersForCurrentLocation();
    renderProductos(productosData, 1);
    updateFilterActiveCount();
});

function filtrarPorUbicacionDataset(dataset) {
    const prov = normalizarTexto(provinciaSel.nombre);
    const cant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);
        const matchProv = !prov || pProv === prov;
        const matchCant = !cant || pCant === cant;
        return matchProv && matchCant;
    });
}

function getFilteredDataForOptionRefresh(dataset, options = {}) {
    const ignoreGroups = new Set(options.ignoreGroups || []);
    const source = filtrarPorUbicacionDataset(dataset).filter(item => getItemType(item) === tipoItemSeleccionado);
    const selectedCatsSet = new Set(categoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasSeleccionadas.map(Number));

    return source.filter(prod => {
        const itemType = getItemType(prod);
        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);
        const anio = Number(prod.anio || 0);
        const precio = Number(prod.precio_referencia || 0);
        const searchScore = getSearchScoreForItem(prod);

        return (searchText.trim() === "" || searchScore > 0) &&
            (itemType === "vehiculo" || ignoreGroups.has("categoria") || selectedCatsSet.size === 0 || hasIntersection(prodCats, selectedCatsSet)) &&
            (itemType === "vehiculo" || ignoreGroups.has("subcategoria") || selectedSubSet.size === 0 || hasIntersection(prodSubs, selectedSubSet)) &&
            (ignoreGroups.has("marca") || marcasSeleccionadas.length === 0 || prodBrand.some(id => marcasSeleccionadas.includes(Number(id)))) &&
            (ignoreGroups.has("modelo") || modelosSeleccionados.length === 0 || prodModel.some(id => modelosSeleccionados.includes(Number(id)))) &&
            precio >= precioMin && precio <= precioMax &&
            (itemType !== "vehiculo" || ignoreGroups.has("condicion") || condicionSeleccionadas.length === 0 || hasIntersectionText(getVehicleConditionNames(prod), condicionSeleccionadas)) &&
            (itemType !== "vehiculo" || ignoreGroups.has("tipoauto") || tipoAutoSeleccionados.length === 0 || hasIntersectionText(getVehicleTipoAutoNames(prod), tipoAutoSeleccionados)) &&
            (itemType !== "vehiculo" || ignoreGroups.has("color") || colorSeleccionados.length === 0 || hasIntersectionText(getVehicleColorNames(prod), colorSeleccionados)) &&
            (itemType !== "vehiculo" || ignoreGroups.has("tapiceria") || tapiceriaSeleccionados.length === 0 || hasIntersectionText(getVehicleTapiceriaNames(prod), tapiceriaSeleccionados)) &&
            (itemType !== "vehiculo" || ignoreGroups.has("climatizacion") || climatizacionSeleccionados.length === 0 || hasIntersectionText(getVehicleClimatizacionNames(prod), climatizacionSeleccionados)) &&
            (itemType !== "vehiculo" || ignoreGroups.has("referencia") || referenciasSeleccionadas.length === 0 || hasIntersectionText(getVehicleReferenciaNames(prod), referenciasSeleccionadas)) &&
            (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMinSel || (anio > 0 && anio >= anioMinSel)) &&
            (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMaxSel || (anio > 0 && anio <= anioMaxSel)) &&
            (itemType !== "servicio" || ignoreGroups.has("nombreServicio") || nombresServiciosSeleccionados.length === 0 || hasIntersectionText(getServiceNameOptions(prod), nombresServiciosSeleccionados));
    });
}

function refreshFiltersForCurrentLocation() {
    const locDataBase = filtrarPorUbicacionDataset(productosData).filter(item => getItemType(item) === tipoItemSeleccionado);
    const locDataConTipo = getFilteredDataForOptionRefresh(productosData);
    const locDataCategorias = locDataConTipo.filter(item => getItemType(item) !== "vehiculo");
    const locVehiculos = locDataConTipo.filter(item => getItemType(item) === "vehiculo");
    const locServicios = locDataConTipo.filter(item => getItemType(item) === "servicio");
    const serviceOptionsData = getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["nombreServicio"] })
        .filter(item => getItemType(item) === "servicio");
    const vehicleDatasets = {
        marca: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["marca"] }).filter(item => getItemType(item) === "vehiculo"),
        modelo: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["modelo"] }).filter(item => getItemType(item) === "vehiculo"),
        condicion: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["condicion"] }).filter(item => getItemType(item) === "vehiculo"),
        tipoauto: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["tipoauto"] }).filter(item => getItemType(item) === "vehiculo"),
        color: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["color"] }).filter(item => getItemType(item) === "vehiculo"),
        tapiceria: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["tapiceria"] }).filter(item => getItemType(item) === "vehiculo"),
        climatizacion: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["climatizacion"] }).filter(item => getItemType(item) === "vehiculo"),
        referencia: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["referencia"] }).filter(item => getItemType(item) === "vehiculo"),
        anio: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["anio"] }).filter(item => getItemType(item) === "vehiculo")
    };

    const marcasDataset = tipoItemSeleccionado === "vehiculo" ? vehicleDatasets.marca : locDataBase;
    const modelosDataset = tipoItemSeleccionado === "vehiculo" ? vehicleDatasets.modelo : locDataConTipo;
    validarMarcaModeloVigentes(marcasDataset);
    buildMarcasYModelos(marcasDataset, modelosDataset);

    catsIndex = buildCatsAndSubcatsFromProductos(locDataCategorias);
    const availableCats = new Set((catsIndex?.categoriasLista || []).map(cat => Number(cat.id_categoria)));
    categoriasSeleccionadas = categoriasSeleccionadas.filter(id => availableCats.has(Number(id)));
    renderCheckboxCategorias(catsIndex?.categoriasLista || []);

    const subcats = buildSubcatsForSelected(categoriasSeleccionadas);
    const availableSubs = new Set(subcats.map(sc => Number(sc.id_sub_categoria)));
    subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(id => availableSubs.has(Number(id)));
    renderCheckboxSubcategorias(subcats);

    toggleFilterBlock("categorias", tipoItemSeleccionado !== "vehiculo" && (catsIndex?.categoriasLista || []).length > 0);
    toggleFilterBlock("subcategorias", tipoItemSeleccionado !== "vehiculo" && subcats.length > 0);
    buildVehicleFilters(locVehiculos, vehicleDatasets);
    buildServiceFilters(serviceOptionsData, locServicios);
    syncVehicleFilterVisibility();
    inicializarSlider(getMaxPrice(locDataConTipo));
}

function buildVehicleFilters(dataset, optionDatasets = {}) {
    const condiciones = uniqueSorted((optionDatasets.condicion || dataset).flatMap(getVehicleConditionNames));
    const tiposAuto = uniqueSorted((optionDatasets.tipoauto || dataset).flatMap(getVehicleTipoAutoNames));
    const colores = uniqueSorted((optionDatasets.color || dataset).flatMap(getVehicleColorNames));
    const tapicerias = uniqueSorted((optionDatasets.tapiceria || dataset).flatMap(getVehicleTapiceriaNames));
    const climatizaciones = uniqueSorted((optionDatasets.climatizacion || dataset).flatMap(getVehicleClimatizacionNames));
    const referencias = uniqueSorted((optionDatasets.referencia || dataset).flatMap(getVehicleReferenciaNames));

    condicionSeleccionadas = condicionSeleccionadas.filter(value => condiciones.some(item => normalizarTexto(item) === normalizarTexto(value)));
    tipoAutoSeleccionados = tipoAutoSeleccionados.filter(value => tiposAuto.some(item => normalizarTexto(item) === normalizarTexto(value)));
    colorSeleccionados = colorSeleccionados.filter(value => colores.some(item => normalizarTexto(item) === normalizarTexto(value)));
    tapiceriaSeleccionados = tapiceriaSeleccionados.filter(value => tapicerias.some(item => normalizarTexto(item) === normalizarTexto(value)));
    climatizacionSeleccionados = climatizacionSeleccionados.filter(value => climatizaciones.some(item => normalizarTexto(item) === normalizarTexto(value)));
    referenciasSeleccionadas = referenciasSeleccionadas.filter(value => referencias.some(item => normalizarTexto(item) === normalizarTexto(value)));

    renderVehicleCheckboxGroup("#filtro-condicion", "checkbox-condicion", condiciones, condicionSeleccionadas);
    renderVehicleCheckboxGroup("#filtro-tipo-auto", "checkbox-tipo-auto", tiposAuto, tipoAutoSeleccionados);
    renderVehicleCheckboxGroup("#filtro-color", "checkbox-color", colores, colorSeleccionados);
    renderVehicleCheckboxGroup("#filtro-tapiceria", "checkbox-tapiceria", tapicerias, tapiceriaSeleccionados);
    renderVehicleCheckboxGroup("#filtro-climatizacion", "checkbox-climatizacion", climatizaciones, climatizacionSeleccionados);
    renderVehicleCheckboxGroup("#filtro-referencias", "checkbox-referencia", referencias, referenciasSeleccionadas);

    const years = (optionDatasets.anio || dataset).map(item => Number(item.anio || 0)).filter(year => Number.isFinite(year) && year > 0);
    const minYear = years.length ? Math.min(...years) : "";
    const maxYear = years.length ? Math.max(...years) : "";
    $("#anioMin").attr("min", minYear || 1900).attr("max", maxYear || 2100);
    $("#anioMax").attr("min", minYear || 1900).attr("max", maxYear || 2100);
}

function buildServiceFilters(optionDataset) {
    const nombres = uniqueSorted((optionDataset || []).flatMap(getServiceNameOptions));
    nombresServiciosSeleccionados = nombresServiciosSeleccionados.filter(value =>
        nombres.some(item => normalizarTexto(item) === normalizarTexto(value))
    );
    renderServicioCheckboxGroup("#filtro-nombre-servicio", "checkbox-nombre-servicio", nombres, nombresServiciosSeleccionados);
    const shouldShow = tipoItemSeleccionado === "servicio" && nombres.length > 0;
    toggleFilterBlock("nombres-servicio", shouldShow);
    $(`.filter-nav-item[data-filter-target="nombres-servicio"]`).toggle(shouldShow);
    $(`.filter-detail-panel[data-filter-panel="nombres-servicio"]`).toggle(shouldShow);
    if (!shouldShow && activeFilterSection === "nombres-servicio") {
        setActiveFilterSection("ubicacion-orden");
    }
}

function syncVehicleFilterVisibility() {
    const isVehicleMode = tipoItemSeleccionado === "vehiculo";
    toggleFilterBlock("anio", isVehicleMode);
    toggleFilterBlock("condicion", isVehicleMode && $("#filtro-condicion").children().length > 0);
    toggleFilterBlock("tipo-auto", isVehicleMode && $("#filtro-tipo-auto").children().length > 0);
    toggleFilterBlock("color", isVehicleMode && $("#filtro-color").children().length > 0);
    toggleFilterBlock("tapiceria", isVehicleMode && $("#filtro-tapiceria").children().length > 0);
    toggleFilterBlock("climatizacion", isVehicleMode && $("#filtro-climatizacion").children().length > 0);
    toggleFilterBlock("referencia", isVehicleMode && $("#filtro-referencias").children().length > 0);
    toggleFilterBlock("nombres-servicio", tipoItemSeleccionado === "servicio" && $("#filtro-nombre-servicio").children().length > 0);
}

function buildCatsAndSubcatsFromProductos(productos) {
    const catsMap = new Map();
    const subsPorCatMap = new Map();

    (productos || []).forEach(prod => {
        const catObjs = toArray(prod.categorias);
        let catIds = parseIdsArray(prod.categoria ?? prod.id_categoria);
        if (catObjs.length) {
            const ids = catObjs.map(obj => Number(obj.id ?? obj.id_categoria)).filter(Boolean);
            catIds = Array.from(new Set([...catIds, ...ids]));
        }

        const subObjs = toArray(prod.subcategorias).length ? toArray(prod.subcategorias) : toArray(prod.sub_categorias);
        let subIds = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        if (subObjs.length) {
            const ids = subObjs.map(obj => Number(obj.id ?? obj.id_sub_categoria ?? obj.sub_categoria)).filter(Boolean);
            subIds = Array.from(new Set([...subIds, ...ids]));
        }

        catIds.forEach(cid => {
            if (!cid) return;
            const nombreCat = catObjs.find(obj => Number(obj.id ?? obj.id_categoria) === cid)?.nombre || `Categoria ${cid}`;
            if (!catsMap.has(cid)) catsMap.set(cid, { id_categoria: cid, nombre: capitalizarPrimeraLetra(nombreCat) });

            if (!subsPorCatMap.has(cid)) subsPorCatMap.set(cid, new Map());
            const subMap = subsPorCatMap.get(cid);

            subIds.forEach(sid => {
                if (!sid) return;
                const nombreSub = subObjs.find(obj => Number(obj.id ?? obj.id_sub_categoria ?? obj.sub_categoria) === sid)?.nombre || `Subcategoria ${sid}`;
                if (!subMap.has(sid)) subMap.set(sid, { id_sub_categoria: sid, nombre: capitalizarPrimeraLetra(nombreSub) });
            });
        });
    });

    return {
        categoriasLista: Array.from(catsMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre)),
        subsPorCat: new Map(Array.from(subsPorCatMap.entries()).map(([cid, map]) => [
            Number(cid),
            Array.from(map.values()).sort((a, b) => a.nombre.localeCompare(b.nombre))
        ]))
    };
}

function buildSubcatsForSelected(idsCategorias) {
    if (!catsIndex) return [];
    const result = new Map();

    (idsCategorias || []).map(Number).forEach(cid => {
        const arr = catsIndex.subsPorCat.get(cid) || [];
        arr.forEach(sc => {
            if (!result.has(sc.id_sub_categoria)) result.set(sc.id_sub_categoria, sc);
        });
    });

    return Array.from(result.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}

function validarMarcaModeloVigentes(data) {
    const brands = new Set((data || []).flatMap(prod => parseIdsArray(prod.id_marca)));
    const models = new Set((data || []).flatMap(prod => parseIdsArray(prod.id_modelo)));

    marcasSeleccionadas = marcasSeleccionadas.filter(id => brands.has(Number(id)));
    modelosSeleccionados = modelosSeleccionados.filter(id => models.has(Number(id)));
}

function toggleFilterBlock(name, visible) {
    $(`[data-filter-block="${name}"]`).toggle(!!visible);
}

function collectCheckedValues(name) {
    return $(`input[name='${name}']:checked`).map(function () {
        return $(this).val();
    }).get();
}

function getMaxPrice(dataset) {
    const precios = (dataset || []).map(prod => Number(prod.precio_referencia)).filter(n => Number.isFinite(n) && n > 0);
    return precios.length ? Math.max(...precios) : 0;
}

function parseIdsArray(value) {
    if (Array.isArray(value)) return value.map(item => Number(item)).filter(Boolean);
    if (typeof value === "string") {
        try {
            const parsed = JSON.parse(value);
            if (Array.isArray(parsed)) return parsed.map(item => Number(item)).filter(Boolean);
        } catch (_) { }
        return value.split(/[,\s]+/).map(Number).filter(Boolean);
    }
    if (value == null) return [];
    return [Number(value)].filter(Boolean);
}

function hasIntersection(idsArr, idsSet) {
    for (const id of idsArr) {
        if (idsSet.has(Number(id))) return true;
    }
    return false;
}

function toArray(value) {
    if (Array.isArray(value)) return value;
    if (value && typeof value === "object") return [value];
    return [];
}

function normalizeToNameArray(val) {
    if (val == null) return [];
    if (Array.isArray(val)) {
        return val.flatMap(v => {
            if (typeof v === "string") return [v.trim()];
            if (typeof v === "number") return [String(v)];
            if (typeof v === "object" && v !== null) {
                if ("nombre" in v) return [String(v.nombre || "").trim()];
                if ("name" in v) return [String(v.name || "").trim()];
            }
            return [];
        }).filter(Boolean);
    }
    if (typeof val === "object") {
        if ("nombre" in val) return [String(val.nombre || "").trim()].filter(Boolean);
        if ("name" in val) return [String(val.name || "").trim()].filter(Boolean);
    }
    if (typeof val === "string") {
        try {
            const parsed = JSON.parse(val);
            return normalizeToNameArray(parsed);
        } catch (_) {}
        return val.replace(/^\[|\]$/g, "").replace(/^"+|"+$/g, "").replace(/^'+|'+$/g, "")
            .split(",").map(item => item.trim()).filter(Boolean);
    }
    return [];
}

function uniqueSorted(items) {
    return Array.from(new Map((items || []).filter(Boolean).map(item => [normalizarTexto(item), capitalizarPrimeraLetra(item)])).values())
        .sort((a, b) => a.localeCompare(b));
}

function hasIntersectionText(sourceItems, selectedValues) {
    const selectedSet = new Set((selectedValues || []).map(normalizarTexto));
    return (sourceItems || []).some(item => selectedSet.has(normalizarTexto(item)));
}

function normalizeYearValue(value) {
    const parsed = Number(value);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
}

function getVehicleConditionNames(prod) {
    return uniqueSorted(normalizeToNameArray(prod?.condicion));
}

function getVehicleTipoAutoNames(prod) {
    return uniqueSorted([
        ...toArray(prod?.tipo_autoo).map(item => item?.nombre).filter(Boolean),
        ...normalizeToNameArray(prod?.tipo_auto)
    ]);
}

function getVehicleColorNames(prod) {
    return uniqueSorted([
        ...normalizeToNameArray(prod?.color),
        ...toArray(prod?.colorr).map(item => item?.nombre).filter(Boolean)
    ]);
}

function getVehicleTapiceriaNames(prod) {
    return uniqueSorted(normalizeToNameArray(prod?.tapiceria));
}

function getVehicleClimatizacionNames(prod) {
    return uniqueSorted(normalizeToNameArray(prod?.climatizacion));
}

function getVehicleReferenciaNames(prod) {
    return uniqueSorted(normalizeToNameArray(prod?.referencias));
}

function getServiceNameOptions(prod) {
    if (getItemType(prod) !== "servicio") return [];
    return uniqueSorted([prod?.titulo_producto, prod?.nombre]);
}

function isVerifiedItem(prod) {
    return Array.isArray(prod?.verificacion) && Number(prod.verificacion[0]?.verificado) === 1;
}

function getTituloProductoOServicio(prod) {
    if (getItemType(prod) === "vehiculo") {
        const marca = getMarcaNombres(prod)[0] || prod?.marca_nombre || "";
        const modelo = getModeloNombres(prod)[0] || prod?.modelo_nombre || "";
        return [marca, modelo].filter(Boolean).join(" ") || prod?.descripcion || "Vehiculo";
    }
    return prod?.nombre || prod?.titulo_producto || "Sin titulo";
}

function getDetalleUrl(prod) {
    if (getItemType(prod) === "vehiculo") {
        const detailPath = window.APP_MODE_CONFIG?.vehicleDetailPath || "detalle_vehiculo.php";
        return `${detailPath}?q=${getItemId(prod)}`;
    }

    const detailPath = window.APP_MODE_CONFIG?.sinCuenta
        ? (window.APP_MODE_CONFIG?.productDetailPath || "detalle_producto_sincuenta.php")
        : "detalle_productos.php";
    return `${detailPath}?q=${getItemId(prod)}`;
}

function getMarcaNombres(prod) {
    const nombres = toArray(prod.marca).map(item => capitalizarPrimeraLetra(item?.nombre)).filter(Boolean);
    if (!nombres.length && prod?.marca_nombre) {
        nombres.push(capitalizarPrimeraLetra(prod.marca_nombre));
    }
    return nombres;
}

function getModeloNombres(prod) {
    const nombres = [
        ...toArray(prod.modelo),
        ...toArray(prod.modelo_productoo),
        ...toArray(prod.modelo_producto)
    ].map(item => capitalizarPrimeraLetra(item?.nombre)).filter(Boolean);
    if (!nombres.length && prod?.modelo_nombre) {
        nombres.push(capitalizarPrimeraLetra(prod.modelo_nombre));
    }
    return nombres;
}
 
function getCategoriaNombres(prod) {
    return toArray(prod.categorias).map(item => capitalizarPrimeraLetra(item?.nombre)).filter(Boolean);
}

function getSubcategoriaNombres(prod) {
    return toArray(prod.subcategorias).map(item => capitalizarPrimeraLetra(item?.nombre)).filter(Boolean);
}

function normalizarTexto(text) {
    return (text || "").toString().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().toLowerCase();
}

function capitalizarPrimeraLetra(string) {
    return window.fulmuvTitleCase ? window.fulmuvTitleCase(string) : ((string || "").toString());
}

function normalizarItemsEmpresa(response) {
    const productosServicios = Array.isArray(response?.data) ? response.data.map(item => ({
        ...item,
        tipo_item: getProductServiceType(item)
    })) : [];
    const vehiculos = Array.isArray(response?.vehiculos) ? response.vehiculos.map(item => ({
        ...item,
        tipo_item: "vehiculo"
    })) : [];

    return [...productosServicios, ...vehiculos];
}

function getProductServiceType(prod) {
    const categoria = toArray(prod?.categorias)[0];
    const tipoCategoria = normalizarTexto(categoria?.tipo || "");
    if (tipoCategoria === "servicio") return "servicio";
    if (tipoCategoria === "producto") return "producto";
    return normalizarTexto(prod?.tipo_item || prod?.tipo_producto || "") === "servicio" ? "servicio" : "producto";
}

function getItemType(prod) {
    const tipo = normalizarTexto(prod?.tipo_item || "");
    if (tipo === "vehiculo" || tipo === "servicio" || tipo === "producto") {
        return tipo;
    }
    return getProductServiceType(prod);
}

function getItemId(prod) {
    return prod?.id_vehiculo || prod?.id_producto || prod?.id_item || "";
}
