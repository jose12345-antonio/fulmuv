let itemsPerPage = 12;
let currentPage = 1;
let empresasData = [];

let sortOption = "todos"; // opciones: "mayor", "menor", "todos"
let searchText = "";

let categoriasOriginales = [];
let categoriasSeleccionadas = [];
let categoriasFiltradas = [];
let empresasFiltradas = [];
let filtroTextoCategoria = "";
let activeFilterSection = "ubicacion-orden";

// Estado seleccionado
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let datosEcuador = {};

fetch('provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

$(document).ready(function () {
    actualizarIconoCarrito();
    initMobileVendorUI();

    $("#breadcrumb").append(`
        <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
        <span></span> Lista de Proveedores
    `)

    $.get("api/v1/fulmuv/empresasTotalProductos/", function (returnedData) {

        if (!returnedData.error) {

            empresasData = returnedData.data;
            categoriasOriginales = getCategoriasPrincipalesDesdeEmpresas(empresasData);
            categoriasFiltradas = [...categoriasOriginales];
            renderCheckboxCategorias(categoriasFiltradas);
            renderEmpresas(empresasData, currentPage);


        }

    }, 'json')

})

function initMobileVendorUI() {
    setActiveFilterSection("ubicacion-orden");
    updateFilterActiveCount();
    updateLocationButtonLabel();
    updateResultsButtonCount();

    $("#inputBusqueda").on("input", function () {
        searchText = $(this).val().trim();
        $(".widget_search input").val(searchText);
        currentPage = 1;
        renderEmpresas(empresasData, currentPage);
        updateFilterActiveCount();
    });

    $("#selectOrderPanel").on("change", function () {
        sortOption = $(this).val();
        currentPage = 1;
        renderEmpresas(empresasData, currentPage);
        updateFilterActiveCount();
    });

    $("#openFilterPanel").on("click", openFilterPanel);
    $("#closeFilterPanel, #minimizeFilterPanel, #filtersOverlay").on("click", closeFilterPanel);
    $(".filter-nav-item").on("click", function () {
        const sectionKey = $(this).data("filter-target");
        if (sectionKey) {
            setActiveFilterSection(sectionKey);
        }
    });

    $("#clearFiltersPanel").on("click", function () {
        searchText = "";
        sortOption = "todos";
        categoriasSeleccionadas = [];
        provinciaSel = { id: null, nombre: null };
        cantonSel = { id: null, nombre: null };

        $("#inputBusqueda").val("");
        $(".widget_search input").val("");
        $("#selectOrderPanel").val("todos");
        $("#filtro-categorias-panel input[name='checkbox-panel']").prop("checked", false);
        $("#filtro-categorias input[name='checkbox']").prop("checked", false);
        $("#selectProvincia").val("");
        resetSelectCanton();
        $("#categoria-all, #cat-p-all").prop("checked", true);
        updateLocationButtonLabel();
        updateFilterActiveCount();

        currentPage = 1;
        renderEmpresas(empresasData, currentPage);
    });

    $(document).on("keydown.vendorMobile", function (e) {
        if (e.key === "Escape" && $("#panelFiltros").hasClass("is-open")) {
            closeFilterPanel();
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
    const locationCount = (provinciaSel.id ? 1 : 0) + (cantonSel.id ? 1 : 0) + (sortOption !== "todos" ? 1 : 0);
    const categoriesCount = categoriasSeleccionadas.length;
    const count = categoriesCount + locationCount + (searchText ? 1 : 0);

    $("#filterActiveCount").text(count);
    $("#filterGroupCountLocation").text(locationCount);
    $("#filterGroupCountCategories").text(categoriesCount);
}

function updateResultsButtonCount() {
    $("#filterResultsCount").text(empresasFiltradas.length || 0);
}

function updateLocationButtonLabel() {
    const label = `<i class="fi-rs-marker me-1"></i>${labelUbicacion()}`;
    $("#btnUbicacionPanel span").html(label);
}

function buildEmptyStateHtml(title, message, iconClass = "fi-rs-shop") {
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

function renderEmpresas(data, page = 1) {
    // 0) Punto de partida SIEMPRE: copia de data
    empresasFiltradas = Array.isArray(data) ? [...data] : [];

    // 1) Filtro por CATEGORÃAS (OR: si la empresa tiene al menos una seleccionada)
    if (categoriasSeleccionadas.length > 0) {
        const sel = categoriasSeleccionadas.map(n => parseInt(n, 10)).filter(n => !isNaN(n));
        empresasFiltradas = empresasFiltradas.filter(emp => {
            const cats = parseCategoriasPrincipalesEmpresa(emp);
            return sel.some(id => cats.includes(id));
        });
    }

    // 2) Filtro por BÃšSQUEDA
    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    if (selProv) {
        empresasFiltradas = empresasFiltradas.filter(emp =>
            normalizarTexto(emp.provincia) === selProv
        );
    }

    if (selCant) {
        empresasFiltradas = empresasFiltradas.filter(emp =>
            normalizarTexto(emp.canton) === selCant
        );
    }

    if (searchText) {
        empresasFiltradas = empresasFiltradas
            .map(emp => ({
                ...emp,
                _searchScore: getEmpresaSearchScore(emp, searchText)
            }))
            .filter(emp => emp._searchScore > 0);
    }

    // 3) ORDEN
    if (searchText) {
        empresasFiltradas.sort((a, b) => {
            const scoreDiff = (b._searchScore || 0) - (a._searchScore || 0);
            if (scoreDiff !== 0) return scoreDiff;
            return compareEmpresasBySortOption(a, b);
        });
    } else if (sortOption === "mayor") {
        empresasFiltradas.sort((a, b) => (b.total_productos || 0) - (a.total_productos || 0));
    } else if (sortOption === "menor") {
        empresasFiltradas.sort((a, b) => (a.total_productos || 0) - (b.total_productos || 0));
    }

    // 4) PaginaciÃ³n
    const totalPages = Math.max(1, Math.ceil(empresasFiltradas.length / itemsPerPage));
    const safePage = Math.min(Math.max(page, 1), totalPages);
    currentPage = safePage;
    const start = (safePage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    // 5) Render cards
    let listProductos = "";
    let listMobile = "";
    empresasPagina.forEach(function (empresa) {
        const idRuta = empresa.id_ruta || empresa.id_empresa;
        const esSucursal = empresa.tipo_registro === "sucursal";
        const nombreEmpresaPadre = capitalizarPrimeraLetra(empresa.nombre_empresa_padre || "");
        const tiempoTexto = calcularTiempoTexto(empresa.tiempo_anos, empresa.tiempo_meses);
        const totalSucursales = Array.isArray(empresa.lista_sucursales)
            ? empresa.lista_sucursales.length
            : (parseInt(empresa.total_sucursales, 10) || 0);
        const empresaVerificada = isEmpresaVerificada(empresa);
        var verificacion = "";
        if (empresaVerificada) {
            verificacion = `<img src="img/verificado_empresa.png" 
                             alt="Verificado" 
                             title="Empresa verificada" 
                             class="vendor-verification-inline">`;
        }
        const imagenEmpresa = buildEmpresaImageUrl(empresa.img_path);
        listProductos += `
        <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-2"> 
            <div class="vendor-wrap">
            <div class="vendor-img text-center">
                <a href="productos_vendor.php?q=${idRuta}">
                <img class="default-img vendor-main-img" src="${imagenEmpresa}" alt=""
                    onerror="this.onerror=null;this.src='img/FULMUV_LOGO-13.png';"
                    style="width:100%;height:230px;" />
                </a>  
            </div>
            <div class="vendor-content-wrap text-center px-2 flex-grow-1 d-flex flex-column justify-content-between py-2">
                <div class="mb-2">
                <span class="d-none text-muted small d-block mb-1">Desde ${tiempoTexto}</span>
                <h4 class="mb-2">
                    <a href="productos_vendor.php?q=${idRuta}"
                    class="d-flex align-items-center justify-content-between">
                    <span class="limite-lineas">${capitalizarPrimeraLetra(empresa.nombre || "")}</span>
                    ${verificacion}
                    </a>
                </h4>
                <span class="small d-block text-muted">${esSucursal ? "Sucursal" : "Empresa matriz"}</span>
                <span class="small d-block text-muted">${esSucursal ? `Empresa: ${nombreEmpresaPadre}` : `${totalSucursales} sucursal${totalSucursales === 1 ? '' : 'es'} activa${totalSucursales === 1 ? '' : 's'}`}</span>
                <span class="total-product small d-block">${empresa.total_productos || 0} productos</span>
                </div>
                <a href="productos_vendor.php?q=${idRuta}" class="btn btn-sm btn-primary" style="background-color:#004E60;color:#FFFFFF">
                VISITAR TIENDA: <strong class="fw-bold">${capitalizarPrimeraLetra(empresa.nombre || "")}</strong><i class="fi-rs-arrow-small-right"></i>
                </a>
            </div>
            </div>
        </div>`;

        const ubicacionTexto = empresa.provincia
            ? `${capitalizarPrimeraLetra(empresa.provincia)}; ${capitalizarPrimeraLetra(empresa.canton || "")}`
            : "Ecuador";

        let htmlVerificacion = "";
        if (empresaVerificada) {
            htmlVerificacion = `
                <div class="badge-verificacion-flotante">
                    <img src="img/verificado_empresa.png" alt="Verificado">
                </div>`;
        }

        listMobile += `
            <div class="vendor-card-container">
                <div class="vendor-card-modern shadow-sm">
                    <a href="productos_vendor.php?q=${idRuta}" class="vendor-link">
                        <div class="vendor-img-wrapper">
                            <img src="${imagenEmpresa}" class="vendor-main-img"
                                onerror="this.onerror=null;this.src='img/FULMUV_LOGO-13.png';">
                            ${htmlVerificacion}
                        </div>
                        <div class="vendor-info-modern">
                            <div class="vendor-location-modern text-truncate">${ubicacionTexto}</div>
                            <h5 class="vendor-title-modern text-truncate">${capitalizarPrimeraLetra(empresa.nombre || "")}</h5>
                            <div class="vendor-location-modern">${esSucursal ? "Sucursal" : "Empresa matriz"}</div>
                            <div class="vendor-location-modern">${esSucursal ? `Empresa: ${nombreEmpresaPadre}` : `${totalSucursales} sucursal${totalSucursales === 1 ? "" : "es"} activa${totalSucursales === 1 ? "" : "s"}`}</div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="vendor-items-count">${empresa.total_productos || 0} productos</div>
                                <div class="btn-circle-action">
                                    <i class="fi-rs-arrow-small-right"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>`;
    });

    $(".vendor-grid").html(listProductos);
    $("#listaVendedoresContainer").html(listMobile);
    $("#countVendedores").text(`Encontramos ${empresasFiltradas.length} resultados`);
    updateResultsButtonCount();

    renderPaginacion(empresasFiltradas.length, safePage);

    // Si no hay resultados
    if (empresasPagina.length === 0) {
        $(".vendor-grid").html(`
            <div class="col-12 text-center">
                <p class="text-muted">No hay proveedores para los filtros seleccionados.</p>
            </div>
        `);
        $("#listaVendedoresContainer").html(buildEmptyStateHtml(
            "No se encontraron empresas",
            "Prueba con otra búsqueda, categoría o ubicación para encontrar empresas activas dentro de la plataforma."
        ));
    }
}



function parseCategoriasPrincipalesEmpresa(emp) {
    const raw = emp?.categorias_principales;
    if (!raw) return [];

    if (Array.isArray(raw)) {
        return raw
            .map(item => parseInt(item?.id_categoria_principal ?? item?.id ?? item, 10))
            .filter(n => !isNaN(n));
    }

    try {
        let parsed = JSON.parse(String(raw));
        if (Array.isArray(parsed)) {
            return parsed
                .map(item => parseInt(item?.id_categoria_principal ?? item?.id ?? item, 10))
                .filter(n => !isNaN(n));
        }
    } catch (_) { }

    return [];
}

function normalizarTexto(valor) {
    return String(valor || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
        .toLowerCase();
}

function getEmpresaSearchScore(emp, searchValue) {
    const query = normalizarTexto(searchValue);
    if (!query) return 0;

    const tokens = query.split(/\s+/).filter(Boolean);
    const nombre = normalizarTexto(emp.nombre);
    const nombrePadre = normalizarTexto(emp.nombre_empresa_padre);
    const provincia = normalizarTexto(emp.provincia);
    const canton = normalizarTexto(emp.canton);
    const idEmpresa = String(emp.id_empresa || "").trim().toLowerCase();

    let score = 0;

    if (nombre.includes(query)) score += 1000;
    if (nombre.startsWith(query)) score += 250;
    if (nombrePadre.includes(query)) score += 700;
    if (provincia.includes(query)) score += 500;
    if (canton.includes(query)) score += 500;
    if (idEmpresa.includes(query)) score += 350;

    tokens.forEach(token => {
        if (!token) return;

        if (nombre.includes(token)) score += 220;
        if (nombrePadre.includes(token)) score += 140;
        if (provincia.includes(token)) score += 110;
        if (canton.includes(token)) score += 110;
        if (idEmpresa.includes(token)) score += 90;
    });

    return score;
}

function compareEmpresasBySortOption(a, b) {
    const totalA = parseInt(a.total_productos, 10) || 0;
    const totalB = parseInt(b.total_productos, 10) || 0;

    if (sortOption === "mayor") {
        return totalB - totalA;
    }

    if (sortOption === "menor") {
        return totalA - totalB;
    }

    return totalB - totalA;
}

function isEmpresaVerificada(empresa) {
    const verificacion = Array.isArray(empresa?.verificacion) ? empresa.verificacion : [];
    return verificacion.some(item => String(item?.verificado) === "1" || item?.verificado === 1 || item?.verificado === true);
}

function buildEmpresaImageUrl(path) {
    const raw = String(path || "").trim();
    if (!raw) return "img/FULMUV_LOGO-13.png";
    if (/^(https?:)?\/\//i.test(raw) || raw.startsWith("data:")) return raw;
    if (raw.startsWith("../")) return raw;

    const normalized = raw.replace(/^\.?\/*/, "");
    if (
        normalized.startsWith("img/") ||
        normalized.startsWith("admin/") ||
        normalized.startsWith("empresa/")
    ) {
        return normalized;
    }

    return `empresa/${normalized}`;
}

function getCategoriasPrincipalesDesdeEmpresas(data) {
    const map = new Map();

    (Array.isArray(data) ? data : []).forEach(emp => {
        const categorias = Array.isArray(emp?.categorias_principales) ? emp.categorias_principales : [];
        categorias.forEach(cat => {
            const id = parseInt(cat?.id_categoria_principal ?? cat?.id, 10);
            const nombre = String(cat?.nombre || '').trim();
            if (isNaN(id) || !nombre || map.has(id)) return;
            map.set(id, {
                id_categoria_principal: id,
                nombre
            });
        });
    });

    return Array.from(map.values()).sort((a, b) => String(a.nombre).localeCompare(String(b.nombre), 'es'));
}
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
            <a class="page-link" href="#" data-page="${currentPage - 1}"><i class="fi-rs-arrow-small-left"></i></a>
        </li>
        ${pagHtml}
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}"><i class="fi-rs-arrow-small-right"></i></a>
        </li>
    `);
}

function calcularTiempoTexto(anos, meses) {
    const hoy = new Date(2025, 6, 1); // Julio 2025 (mes 6 porque enero = 0)
    const totalMeses = (anos * 12) + meses;
    hoy.setMonth(hoy.getMonth() - totalMeses);

    const opciones = { year: 'numeric', month: 'long' };
    return hoy.toLocaleDateString('es-ES', opciones);
}

$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(empresasData, currentPage);
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
});


// BÃºsqueda por nombre o ID
$(".widget_search input").on("input", function () {
    searchText = $(this).val().trim();
    $("#inputBusqueda").val(searchText);
    updateFilterActiveCount();
    renderEmpresas(empresasData, 1);
});

$(".sort-show li a").on("click", function (e) {
    e.preventDefault();

    $(".sort-show li a").removeClass("active");
    $(this).addClass("active");

    const value = $(this).data("value");
    itemsPerPage = value === "all" ? empresasData.length : parseInt(value);

    // Actualiza texto de dropdown
    $(".sort-by-product-wrap:first .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    currentPage = 1;
    renderEmpresas(empresasData, currentPage);
});

$(".sort-order li a").on("click", function (e) {
    e.preventDefault();

    $(".sort-order li a").removeClass("active");
    $(this).addClass("active");

    sortOption = $(this).data("value");
    $("#selectOrderPanel").val(sortOption);

    // Actualiza texto del dropdown
    $(".sort-by-product-wrap:last .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    renderEmpresas(empresasData, 1);
});




function renderCheckboxCategorias(categorias) {
    const renderSearchInput = `
    <div class="mb-3">
        <input type="text" class="form-control form-control-sm filter-option-search"
            data-filter-type="categoria" placeholder="Buscar categoría"
            value="${String(filtroTextoCategoria || '').replace(/"/g, '&quot;')}">
    </div>`;
    let htmlVisible = `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="todos">
        <input class="form-check-input" type="checkbox" value="__all__" id="categoria-all" name="checkbox" ${categoriasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-all">Todos</label>
      </div>`;

    categorias.forEach((cat) => {
        htmlVisible += `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="${capitalizarPrimeraLetra(cat.nombre).toLowerCase()}">
        <input class="form-check-input" type="checkbox" value="${cat.id_categoria_principal}" id="categoria-${cat.id_categoria_principal}" name="checkbox">
        <label class="form-check-label fw-normal" for="categoria-${cat.id_categoria_principal}">
          ${capitalizarPrimeraLetra(cat.nombre)}
        </label>
      </div>`;
    });

    const htmlFinal = `
    ${renderSearchInput}
    <div class="checkbox-list-visible">
      ${htmlVisible}
    </div>
    <small class="text-muted filter-option-empty" style="display:none;">No hay categorÃ­as.</small>
  `;

    $("#filtro-categorias").html(htmlFinal);
    $("#filtro-categorias-panel").html(buildMobileCategoriasHtml(categorias));
    categoriasSeleccionadas.forEach(id => {
        $(`#cat-p-${id}`).prop("checked", true);
    });
    $("#cat-p-all").prop("checked", categoriasSeleccionadas.length === 0);
    applyFilterOptionSearch(filtroTextoCategoria);
}

function buildMobileCategoriasHtml(categorias) {
    let html = `
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="__all__" id="cat-p-all" name="checkbox-panel" ${categoriasSeleccionadas.length === 0 ? 'checked' : ''}>
                <label class="form-check-label" for="cat-p-all">Todos</label>
            </div>
        </div>`;

    categorias.forEach((cat) => {
        html += `
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${cat.id_categoria_principal}" id="cat-p-${cat.id_categoria_principal}" name="checkbox-panel" ${categoriasSeleccionadas.includes(parseInt(cat.id_categoria_principal, 10)) ? 'checked' : ''}>
                    <label class="form-check-label" for="cat-p-${cat.id_categoria_principal}">
                        ${capitalizarPrimeraLetra(cat.nombre)}
                    </label>
                </div>
            </div>`;
    });

    return html;
}

function applyFilterOptionSearch(valor) {
    const container = $("#filtro-categorias");
    const rows = container.find(".filter-option-row");
    let visibles = 0;
    rows.each(function () {
        const candidate = normalizarTexto($(this).attr("data-filter-search-text") || "");
        const match = !valor || candidate.includes(normalizarTexto(valor));
        $(this).toggle(match);
        if (match) visibles += 1;
    });
    container.find(".filter-option-empty").toggle(visibles === 0);
}

// SelecciÃ³n/deselecciÃ³n de categorÃ­as
$(document).on("change", "#filtro-categorias input[name='checkbox']", function () {
    if (this.value === "__all__") {
        categoriasSeleccionadas = [];
        $("#filtro-categorias input[name='checkbox']").not(this).prop("checked", false);
        this.checked = true;
        currentPage = 1;
        renderEmpresas(empresasData, currentPage);
        return;
    }

    const id = parseInt(this.value, 10);
    if (this.checked) {
        if (!categoriasSeleccionadas.includes(id)) categoriasSeleccionadas.push(id);
    } else {
        categoriasSeleccionadas = categoriasSeleccionadas.filter(c => c !== id);
    }
    $("#categoria-all").prop("checked", categoriasSeleccionadas.length === 0);
    currentPage = 1;
    renderEmpresas(empresasData, currentPage);
    updateFilterActiveCount();
});

$(document).on("change", "#filtro-categorias-panel input[name='checkbox-panel']", function () {
    if (this.value === "__all__") {
        categoriasSeleccionadas = [];
        $("#filtro-categorias-panel input[name='checkbox-panel']").not(this).prop("checked", false);
        $("#filtro-categorias input[name='checkbox']").not("#categoria-all").prop("checked", false);
        $("#categoria-all").prop("checked", true);
        this.checked = true;
        currentPage = 1;
        renderEmpresas(empresasData, currentPage);
        updateFilterActiveCount();
        return;
    }

    const id = parseInt(this.value, 10);
    if (this.checked) {
        if (!categoriasSeleccionadas.includes(id)) categoriasSeleccionadas.push(id);
    } else {
        categoriasSeleccionadas = categoriasSeleccionadas.filter(c => c !== id);
    }

    $("#cat-p-all, #categoria-all").prop("checked", categoriasSeleccionadas.length === 0);
    $(`#categoria-${id}`).prop("checked", this.checked);
    currentPage = 1;
    renderEmpresas(empresasData, currentPage);
    updateFilterActiveCount();
});

$(document).off("input.filterOptionSearchVendorList").on("input.filterOptionSearchVendorList", ".filter-option-search", function () {
    filtroTextoCategoria = $(this).val().trim();
    applyFilterOptionSearch(filtroTextoCategoria);
});

// --- REEMPLAZA tu funciÃ³n cargarProvincias por esta ---
function cargarProvincias() {
    const selectProvincia = document.getElementById("selectProvincia");
    selectProvincia.innerHTML = '<option value="">Seleccione una provincia</option>';

    Object.entries(datosEcuador).forEach(([codProv, objProv]) => {
        const option = document.createElement("option");
        option.value = codProv; // ID provincia
        option.textContent = capitalizarPrimeraLetra(objProv.provincia); // Nombre provincia
        selectProvincia.appendChild(option);
    });

    // reset canton al cambiar provincia
    selectProvincia.addEventListener("change", (e) => {
        const codProv = e.target.value || null;
    if (!codProv) {
            provinciaSel = { id: null, nombre: null };
            resetSelectCanton();
            updateLocationButtonLabel();
            updateFilterActiveCount();
            return;
        }
        provinciaSel.id = codProv;
        provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);

        // rellenar cantones de la provincia elegida
        cargarCantones(codProv);
        updateLocationButtonLabel();
        updateFilterActiveCount();
    });
}

// --- REEMPLAZA tu funciÃ³n cargarCantones por esta ---
function cargarCantones(codProvincia) {
    const selectCanton = document.getElementById("selectCanton");
    selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';

    // seguridad
    if (!codProvincia || !datosEcuador[codProvincia]) {
        resetSelectCanton();
        return;
    }

    const cantones = datosEcuador[codProvincia].cantones || {};
    Object.entries(cantones).forEach(([codCanton, objCanton]) => {
        const option = document.createElement("option");
        option.value = codCanton; // ID cantÃ³n
        option.textContent = capitalizarPrimeraLetra(objCanton.canton); // Nombre cantÃ³n
        selectCanton.appendChild(option);
    });

    // al cambiar cantÃ³n, guarda ID y nombre
    selectCanton.addEventListener("change", (e) => {
        const codCanton = e.target.value || null;
        if (!codCanton) {
            cantonSel = { id: null, nombre: null };
            updateLocationButtonLabel();
            updateFilterActiveCount();
            return;
        }
        const nombre = capitalizarPrimeraLetra(
            (datosEcuador[codProvincia].cantones[codCanton] || {}).canton || ""
        );
        cantonSel.id = codCanton;
        cantonSel.nombre = nombre;
        updateLocationButtonLabel();
        updateFilterActiveCount();
    });
}

// helper para limpiar cantÃ³n si se cambia/limpia provincia
function resetSelectCanton() {
    const selectCanton = document.getElementById("selectCanton");
    selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
    cantonSel = { id: null, nombre: null };
}

// Construir el texto para el botÃ³n
function labelUbicacion() {
    if (provinciaSel.nombre && cantonSel.nombre) {
        return `PROVINCIA: ${provinciaSel.nombre}; CANTÃ“N: ${cantonSel.nombre}`;
    }
    if (provinciaSel.nombre) {
        return `PROVINCIA: ${provinciaSel.nombre}`;
    }
    return 'Cambiar ubicación';
}



// Click "Listo": actualiza botÃ³n y guarda en inputs ocultos/localStorage si quieres
document.getElementById('guardarUbicacion').addEventListener('click', function () {
    // Actualiza botÃ³n
    const btn = document.getElementById('btnUbicacion');
    if (btn) btn.innerHTML = `<i class="fi-rs-marker me-1"></i> ${labelUbicacion()}`;
    updateLocationButtonLabel();
    updateFilterActiveCount();

    // (Opcional) guarda en inputs ocultos
    const provIdH = document.getElementById('provincia_id_hidden');
    const provNomH = document.getElementById('provincia_nombre_hidden');
    const cantIdH = document.getElementById('canton_id_hidden');
    const cantNomH = document.getElementById('canton_nombre_hidden');

    if (provIdH) provIdH.value = provinciaSel.id || '';
    if (provNomH) provNomH.value = provinciaSel.nombre || '';
    if (cantIdH) cantIdH.value = cantonSel.id || '';
    if (cantNomH) cantNomH.value = cantonSel.nombre || '';

    // (Opcional) persistir en localStorage para reusar en la lista/checkout
    localStorage.setItem('ubicacionSeleccionada', JSON.stringify({
        provincia: provinciaSel,
        canton: cantonSel
    }));

    // Cerrar modal
    const modalEl = document.getElementById('modalUbicacion');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
    currentPage = 1;
    renderEmpresas(empresasData, currentPage);

    // (Opcional) si quieres re-filtrar productos por ubicaciÃ³n, llama aquÃ­ a tu funciÃ³n
    // filtrarPorUbicacion(provinciaSel, cantonSel);
});
