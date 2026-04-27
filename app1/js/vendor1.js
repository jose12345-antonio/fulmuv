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

function updateFilterActiveCount() {
    const count =
        categoriasSeleccionadas.length +
        (sortOption !== "todos" ? 1 : 0) +
        (searchText ? 1 : 0) +
        (provinciaSel.id ? 1 : 0) +
        (cantonSel.id ? 1 : 0);

    $("#filterActiveCount").text(count);
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
    updateFilterActiveCount();
    updateLocationButtonLabel();

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
            const categoriaPrincipalEmpresa = parseCategoriasPrincipalesEmpresa(emp);

            return categoriasSeleccionadas.some(idSeleccionado => {
                const idsRelacionados = categoriasFiltroMap[idSeleccionado] || [idSeleccionado];

                const coincideCategoriaPrincipal = categoriaPrincipalEmpresa.includes(idSeleccionado);
                const coincideCategoriaSecundaria = idsRelacionados.some(id => categoriaIdsEmpresa.includes(id));

                return coincideCategoriaPrincipal || coincideCategoriaSecundaria;
            });
        });
    }

    if (searchText) {
        const q = searchText.toLowerCase();
        empresasFiltradas = empresasFiltradas.filter(emp =>
            (emp.nombre || "").toLowerCase().includes(q)
        );
    }

    if (sortOption === "mayor") {
        empresasFiltradas.sort((a, b) => (parseInt(b.total_productos, 10) || 0) - (parseInt(a.total_productos, 10) || 0));
    } else if (sortOption === "menor") {
        empresasFiltradas.sort((a, b) => (parseInt(a.total_productos, 10) || 0) - (parseInt(b.total_productos, 10) || 0));
    }

    $("#countVendedores").text(`Encontramos ${empresasFiltradas.length} resultados`);

    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    let listHtml = "";

    empresasPagina.forEach(function (emp) {
        let htmlVerificacion = "";
        if (emp.verificacion?.length > 0 && emp.verificacion[0].verificado == 1) {
            htmlVerificacion = `
                <div class="badge-verificacion-flotante">
                    <img src="../img/verificado_empresa.png" alt="Verificado">
                </div>`;
        }

        const ubicacionTexto = emp.provincia ? `${emp.provincia}; ${emp.canton || ""}` : "Ecuador";

        listHtml += `
            <div class="vendor-card-container">
                <div class="vendor-card-modern shadow-sm">
                    <a href="javascript:void(0)"
                        onclick="abrirProductosTienda(${emp.id_empresa})"
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

    const modalEl = document.getElementById("modalUbicacion");
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
});

function renderCheckboxCategorias(categorias) {
    let html = "";
    categoriasFiltroMap = {};

    categorias.forEach((cat) => {
        const principalId = parseInt(cat.id_categoria_principal ?? cat.id_categoria, 10);
        const secundarias = Array.isArray(cat.categorias_secundarias) ? cat.categorias_secundarias : [];

        categoriasFiltroMap[principalId] = secundarias
            .map(item => parseInt(item?.id_categoria, 10))
            .filter(Number.isFinite);

        html += `
            <div class="col-6">
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
    const id = parseInt(this.value, 10);
    if (this.checked) {
        if (!categoriasSeleccionadas.includes(id)) categoriasSeleccionadas.push(id);
    } else {
        categoriasSeleccionadas = categoriasSeleccionadas.filter(c => c !== id);
    }

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
    if (!string) return "";
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

function parseCategoriasEmpresa(emp) {
    const ids = new Set();

    const pushId = (value) => {
        const id = parseInt(value, 10);
        if (Number.isFinite(id)) ids.add(id);
    };

    const parseCollection = (collection, key = "id_categoria") => {
        if (Array.isArray(collection)) {
            collection.forEach(item => {
                if (typeof item === "object" && item !== null) {
                    pushId(item[key] ?? item.id_categoria ?? item.id);
                } else {
                    pushId(item);
                }
            });
            return;
        }

        if (typeof collection === "string" && collection.trim()) {
            try {
                const parsed = JSON.parse(collection);
                parseCollection(parsed, key);
                return;
            } catch (e) {
                collection.split(",").forEach(item => pushId(item.trim()));
            }
        }
    };

    parseCollection(emp.categorias, "id_categoria");
    parseCollection(emp.categorias_secundarias, "id_categoria");
    parseCollection(emp.id_categorias, "id_categoria");

    return [...ids];
}

function parseCategoriasPrincipalesEmpresa(emp) {
    const ids = new Set();

    const pushId = (value) => {
        const id = parseInt(value, 10);
        if (Number.isFinite(id)) ids.add(id);
    };

    if (emp?.id_categoria_principal !== undefined && emp?.id_categoria_principal !== null) {
        pushId(emp.id_categoria_principal);
    }

    if (Array.isArray(emp?.categorias_principales)) {
        emp.categorias_principales.forEach(item => {
            if (typeof item === "object" && item !== null) {
                pushId(item.id_categoria_principal ?? item.id_categoria ?? item.id);
            } else {
                pushId(item);
            }
        });
    }

    return [...ids];
}
