let itemsPerPage = 10;
let currentPage = 1;
let empresasData = [];
let sortOption = "todos";
let searchText = "";
let categoriasSeleccionadas = [];
let empresasFiltradas = [];
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let datosEcuador = {};
let categoriasFiltroMap = {};
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
    updateResultsButtonCount();

    $.get("../api/v1/fulmuv/categoriasPrincipales/All", function (returnedDataCategoria) {
        if (!returnedDataCategoria.error) {
            renderCheckboxCategorias(returnedDataCategoria.data);
        }
    }, "json");

    $.get("../api/v1/fulmuv/empresasTotalProductos/", function (returnedData) {
        if (!returnedData.error) {
            empresasData = returnedData.data;
            renderEmpresas(empresasData, currentPage);
        }
    }, "json");

    $("#inputBusqueda").on("input", function () {
        searchText = $(this).val().trim();
        currentPage = 1;
        renderEmpresas(empresasData, 1);
        updateFilterActiveCount();
    });

    $("#selectOrderPanel").on("change", function () {
        sortOption = $(this).val();
        currentPage = 1;
        renderEmpresas(empresasData, 1);
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
        $("#selectOrderPanel").val("todos");
        $("input[name='checkbox-panel']").prop("checked", false);
        $("#selectProvincia").val("");
        resetSelectCanton();
        updateLocationButtonLabel();
        updateFilterActiveCount();

        currentPage = 1;
        renderEmpresas(empresasData, 1);
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $("#panelFiltros").hasClass("is-open")) {
            closeFilterPanel();
        }
    });

    if (typeof datosEcuador !== "undefined") {
        cargarProvincias();
    }
});

function renderEmpresas(data, page = 1) {
    empresasFiltradas = Array.isArray(data) ? [...data] : [];

    if (categoriasSeleccionadas.length > 0) {
        empresasFiltradas = empresasFiltradas.filter(emp => {
            const categoriaIdsEmpresa = parseCategoriasEmpresa(emp);

            return categoriasSeleccionadas.some(idSeleccionado => {
                const idsRelacionados = categoriasFiltroMap[idSeleccionado] || [idSeleccionado];
                const coincideCategoriaSecundaria = idsRelacionados.some(id => categoriaIdsEmpresa.includes(id));

                return coincideCategoriaSecundaria;
            });
        });
    }

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

    if (searchText) {
        empresasFiltradas.sort((a, b) => {
            const scoreDiff = (b._searchScore || 0) - (a._searchScore || 0);
            if (scoreDiff !== 0) return scoreDiff;
            return compareEmpresasBySortOption(a, b);
        });
    } else if (sortOption === "mayor") {
        empresasFiltradas.sort((a, b) => (parseInt(b.total_productos, 10) || 0) - (parseInt(a.total_productos, 10) || 0));
    } else if (sortOption === "menor") {
        empresasFiltradas.sort((a, b) => (parseInt(a.total_productos, 10) || 0) - (parseInt(b.total_productos, 10) || 0));
    }

    $("#countVendedores").text(`Encontramos ${empresasFiltradas.length} resultados`);
    updateResultsButtonCount();

    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    let listHtml = "";

    empresasPagina.forEach(function (emp) {
        const idRuta = emp.id_ruta || emp.id_empresa;
        const esSucursal = emp.tipo_registro === "sucursal";
        const nombreEmpresaPadre = capitalizarPrimeraLetra(emp.nombre_empresa_padre || "");
        const totalSucursales = Array.isArray(emp.lista_sucursales)
            ? emp.lista_sucursales.length
            : (parseInt(emp.total_sucursales, 10) || 0);
        const empresaVerificada = isEmpresaVerificada(emp);
        let htmlVerificacion = "";
        if (empresaVerificada) {
            htmlVerificacion = `
                <div class="badge-verificacion-flotante">
                    <img src="../img/verificado_empresa.png" alt="Verificado">
                </div>`;
        }

        const ubicacionTexto = emp.provincia
            ? `${capitalizarPrimeraLetra(emp.provincia)}; ${capitalizarPrimeraLetra(emp.canton || "")}`
            : "Ecuador";

        listHtml += `
            <div class="vendor-card-container">
                <div class="vendor-card-modern shadow-sm">
                    <a href="javascript:void(0)"
                        onclick="abrirProductosTienda(${idRuta})"
                        class="vendor-link">
                        <div class="vendor-img-wrapper">
                            <img src="../empresa/${emp.img_path ? "" + emp.img_path : "../img/FULMUV_LOGO-13.png"}"
                                class="vendor-main-img"
                                onerror="this.src='../img/FULMUV_LOGO-13.png';">
                            ${htmlVerificacion}
                        </div>
                        <div class="vendor-info-modern">
                            <div class="vendor-location-modern text-truncate">${ubicacionTexto}</div>
                            <h5 class="vendor-title-modern text-truncate">
                                ${capitalizarPrimeraLetra(emp.nombre || "")}
                            </h5>
                            <div class="vendor-location-modern">${esSucursal ? "Sucursal" : "Empresa matriz"}</div>
                            <div class="vendor-location-modern">${esSucursal ? `Empresa: ${nombreEmpresaPadre}` : `${totalSucursales} sucursal${totalSucursales === 1 ? "" : "es"} activa${totalSucursales === 1 ? "" : "s"}`}</div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="vendor-items-count">${emp.total_productos || 0} productos</div>
                                <div class="btn-circle-action">
                                    <i class="fi-rs-arrow-small-right"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>`;
    });

    $("#listaVendedoresContainer").html(listHtml || buildEmptyStateHtml(
        "No se encontraron empresas",
        "Prueba con otra busqueda, categoria o ubicacion para encontrar empresas activas dentro de la plataforma."
    ));
    renderPaginacion(empresasFiltradas.length, page);
}

$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"), 10);
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(empresasData, currentPage);
        window.scrollTo(0, 0);
    }
});

function cargarProvincias() {
    const selectProvincia = document.getElementById("selectProvincia");
    if (!selectProvincia) return;

    selectProvincia.innerHTML = '<option value="">Seleccione una provincia</option>';

    if (typeof datosEcuador === "undefined") return;

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
            updateFilterActiveCount();
            return;
        }

        provinciaSel.id = codProv;
        provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);
        cargarCantones(codProv);
        updateLocationButtonLabel();
        updateFilterActiveCount();
    };
}

function cargarCantones(codProvincia) {
    const selectCanton = document.getElementById("selectCanton");
    if (!selectCanton) return;

    selectCanton.innerHTML = '<option value="">Seleccione un canton</option>';

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

    selectCanton.onchange = (e) => {
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
        return `PROVINCIA: ${provinciaSel.nombre}; CANTON: ${cantonSel.nombre}`;
    }
    if (provinciaSel.nombre) {
        return `PROVINCIA: ${provinciaSel.nombre}`;
    }
    return "Cambiar ubicacion";
}

document.getElementById("guardarUbicacion").addEventListener("click", function () {
    updateLocationButtonLabel();
    updateFilterActiveCount();

    const provIdH = document.getElementById("provincia_id_hidden");
    const provNomH = document.getElementById("provincia_nombre_hidden");
    const cantIdH = document.getElementById("canton_id_hidden");
    const cantNomH = document.getElementById("canton_nombre_hidden");

    if (provIdH) provIdH.value = provinciaSel.id || "";
    if (provNomH) provNomH.value = provinciaSel.nombre || "";
    if (cantIdH) cantIdH.value = cantonSel.id || "";
    if (cantNomH) cantNomH.value = cantonSel.nombre || "";

    localStorage.setItem("ubicacionSeleccionada", JSON.stringify({
        provincia: provinciaSel,
        canton: cantonSel
    }));

    currentPage = 1;
    renderEmpresas(empresasData, 1);

    const modalEl = document.getElementById("modalUbicacion");
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
});

function renderCheckboxCategorias(categorias) {
    let html = `
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="__all__"
                        id="cat-p-all" name="checkbox-panel" ${categoriasSeleccionadas.length === 0 ? 'checked' : ''}>
                    <label class="form-check-label" for="cat-p-all">
                        Todos
                    </label>
                </div>
            </div>`;
    categoriasFiltroMap = {};

    categorias.forEach((cat) => {
        const principalId = parseInt(cat.id_categoria_principal ?? cat.id_categoria, 10);
        const secundarias = Array.isArray(cat.categorias_secundarias) ? cat.categorias_secundarias : [];

        categoriasFiltroMap[principalId] = secundarias
            .map(item => parseInt(item?.id_categoria, 10))
            .filter(Number.isFinite);

        html += `
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${principalId}"
                        id="cat-p-${principalId}" name="checkbox-panel">
                    <label class="form-check-label" for="cat-p-${principalId}">
                        ${capitalizarPrimeraLetra(cat.nombre)}
                    </label>
                </div>
            </div>`;
    });

    $("#filtro-categorias-panel").html(html);

    categoriasSeleccionadas.forEach(id => {
        $(`#cat-p-${id}`).prop("checked", true);
    });
}

$(document).on("change", "input[name='checkbox-panel']", function () {
    if (this.value === "__all__") {
        categoriasSeleccionadas = [];
        $("input[name='checkbox-panel']").not(this).prop("checked", false);
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
    $("#cat-p-all").prop("checked", categoriasSeleccionadas.length === 0);

    currentPage = 1;
    renderEmpresas(empresasData, 1);
    updateFilterActiveCount();
});

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

function capitalizarPrimeraLetra(string) {
    return window.fulmuvTitleCase ? window.fulmuvTitleCase(string) : ((string || "").toString());
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

function parseCategoriasEmpresa(emp) {
    const raw = emp?.categorias_referencia;
    if (!raw) return [];

    if (Array.isArray(raw)) {
        return raw.map(x => parseInt(x, 10)).filter(Number.isFinite);
    }

    let source = String(raw).trim();
    if (!source) return [];

    try {
        let parsed = JSON.parse(source);
        if (typeof parsed === "string") {
            parsed = JSON.parse(parsed);
        }
        if (Array.isArray(parsed)) {
            return parsed.map(x => parseInt(x, 10)).filter(Number.isFinite);
        }
    } catch (e) {
        // Si viene doble serializado o con formato irregular, cae al parser de respaldo.
    }

    return source
        .replace(/[\[\]\s"]/g, "")
        .split(",")
        .map(x => parseInt(x, 10))
        .filter(Number.isFinite);
}
