let itemsPerPage = 20;
let currentPage = 1;
let productosData = [];

let sortOption = "todos"; // opciones: "mayor", "menor", "todos"
let searchText = "";
let id_categoria = $("#id_categoria").val();
let subcategoriasSeleccionadas = [];
let subcategoriasHijas = [];              // listado devuelto por la API
let subcategoriasHijasSeleccionadas = []; // checks seleccionados
let marcasUnicas = [];
let modelosUnicos = [];
let marcaSeleccionada = null;   // id_marca o null (Todos)
let modeloSeleccionado = null;  // id_modelo o null (Todos)
let precioMin = 0;
let precioMax = Infinity;
let categoriasFiltradas = [];
let rangeSlider;
let moneyFormat = wNumb({
    decimals: 0,
    thousand: ",",
    prefix: "$"
});

// Estado seleccionado
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let catsIndex = null;
let datosEcuador = {};
let categoriasOriginales = [];

function setActiveFilterSection(sectionKey) {
    $(".filter-nav-item").removeClass("is-active");
    $(`.filter-nav-item[data-filter-target="${sectionKey}"]`).addClass("is-active");

    $(".filter-detail-panel").removeClass("is-active");
    $(`.filter-detail-panel[data-filter-panel="${sectionKey}"]`).addClass("is-active");
}

function openFilterPanel() {
    setActiveFilterSection($(".filter-nav-item.is-active").data("filter-target") || "ubicacion-orden");
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
    const categoriesCount = subcategoriasSeleccionadas.length + subcategoriasHijasSeleccionadas.length;
    const attributesCount =
        (marcaSeleccionada !== null ? 1 : 0) +
        (modeloSeleccionado !== null ? 1 : 0);
    const priceCount = (precioMin > 0 || precioMax !== Infinity) ? 1 : 0;
    const count = locationCount + categoriesCount + attributesCount + priceCount + (searchText ? 1 : 0);

    $("#filterActiveCount").text(count);
    $("#filterGroupCountLocation").text(locationCount);
    $("#filterGroupCountCategories").text(categoriesCount);
    $("#filterGroupCountAttributes").text(attributesCount);
    $("#filterGroupCountPrice").text(priceCount);
}

function updateResultsButtonCount(total) {
    $("#filterResultsCount").text(Number.isFinite(total) ? total : 0);
}

function buildEmptyStateHtml(title, message, iconClass = "fi-rs-box") {
    return `
        <div class="empty-state-modern">
            <div class="empty-state-card">
                <div class="empty-state-icon"><i class="${iconClass}"></i></div>
                <div class="empty-state-title">${title}</div>
                <p class="empty-state-text">${message}</p>
            </div>
        </div>`;
}


fetch('../provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

$(document).ready(function () {

    actualizarIconoCarrito();
    setActiveFilterSection("ubicacion-orden");
    updateFilterActiveCount();
    updateResultsButtonCount(0);

    $("#openFilterPanel").on("click", openFilterPanel);
    $("#closeFilterPanel, #minimizeFilterPanel, #filtersOverlay").on("click", closeFilterPanel);
    $("#clearFiltersPanel").on("click", function () {
        clearAllFilters();
    });
    $(".filter-nav-item").on("click", function () {
        const sectionKey = $(this).data("filter-target");
        if (sectionKey) setActiveFilterSection(sectionKey);
    });

    $("#inputBusqueda").on("input", function () {
        searchText = $(this).val().trim();
        renderEmpresas(productosData, 1);
        updateFilterActiveCount();
    });

    $("#selectOrderPanel").on("change", function () {
        sortOption = $(this).val();
        renderEmpresas(productosData, 1);
        updateFilterActiveCount();
    });

    // 1) Traer TODAS las categorías
    $.get("../api/v1/fulmuv/categorias/All", function (returnedDataCategoria) {
        if (!returnedDataCategoria.error) {
            categoriasOriginales = returnedDataCategoria.data;
            categoriasFiltradas = returnedDataCategoria.data;

            renderCheckboxCategorias(categoriasFiltradas);

            // 2) Traer productos vendidos hoy
            $.get("../api/v1/fulmuv/getProductosVendidosHoy/", function (returnedData) {
                if (!returnedData.error) {
                    productosData = returnedData.data;

                    const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
                    inicializarSlider(maxPrecio);
                    buildMarcasYModelos(productosData);
                    renderEmpresas(productosData, currentPage);

                    // ✅ Construir categorías y subcategorías ÚNICAS según productos
                    catsIndex = buildCatsAndSubcatsFromProductos(productosData);
                    categoriasFiltradas = catsIndex.categoriasLista;   // array: [{id_categoria, nombre}]
                    renderCheckboxCategorias(categoriasFiltradas);      // pinta el panel de categorías
                }
            }, 'json');
        }
    }, 'json');
});

// $(document).on("click", "#btnToggleMobileFilters", function () {
//     // Espera a que abra el collapse y enfoca el input de búsqueda
//     setTimeout(() => {
//         const input = document.querySelector(".widget_search input");
//         if (input) input.focus();
//     }, 250);
// });

// Selección/deselección de CATEGORÍAS
$(document).on("change", "input[name='checkbox']", function () {
    const id = $(this).val();

    if ($(this).is(":checked")) {
        if (!subcategoriasSeleccionadas.includes(id)) {
            subcategoriasSeleccionadas.push(id); // agrega solo si no existe
        }
    } else {
        subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(sub => sub !== id);
    }

    // normalizar IDs únicos (evita payload duplicado)
    const idsUnicos = [...new Set(subcategoriasSeleccionadas.map(x => parseInt(x)))];

    // toggle del bloque de subcategorías
    const haySeleccion = idsUnicos.length > 0;
    $("#subcats-box").toggle(haySeleccion);

    if (!haySeleccion) {
        // 👉 SIN categorías seleccionadas:
        // Volvemos al estado base: productos vendidos hoy
        $.get("../api/v1/fulmuv/getProductosVendidosHoy/", function (returnedData) {
            if (!returnedData.error) {
                productosData = returnedData.data;

                const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
                inicializarSlider(maxPrecio);

                // limpiar subcategorías
                subcategoriasHijas = [];
                subcategoriasHijasSeleccionadas = [];
                $("#filtro-sub-categorias").html("");
                $("#subcats-box").hide();

                // reconstruir marcas/modelos
                buildMarcasYModelos(productosData);

                // reconstruir índice de categorías/subcats desde dataset base
                catsIndex = buildCatsAndSubcatsFromProductos(productosData);
                categoriasFiltradas = catsIndex.categoriasLista;
                renderCheckboxCategorias(categoriasFiltradas);

                renderEmpresas(productosData, 1);
            }
        }, 'json');
        return; // <- importante, no sigas pidiendo subcategorías
    }

    // 1) Productos por categorías seleccionadas (únicas)
    //    (Mantengo esta API como pediste)
    $.post("../api/v1/fulmuv/productos/idCategoria", { id_categoria: idsUnicos }, function (returnedData) {
        if (!returnedData.error) {
            productosData = returnedData.data;
            const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
            inicializarSlider(maxPrecio);
            buildMarcasYModelos(productosData);

            // Actualizamos índice de cats/subcats según este subset
            catsIndex = buildCatsAndSubcatsFromProductos(productosData);
            categoriasFiltradas = catsIndex.categoriasLista;
            renderCheckboxCategorias(categoriasFiltradas);
            recheckCategoriasSeleccionadas();

            // Subcategorías para estas categorías seleccionadas
            const subcats = buildSubcatsForSelected(idsUnicos);
            subcategoriasHijas = subcats;
            subcategoriasHijasSeleccionadas = [];
            renderCheckboxSubcategorias(subcats);
            $("#subcats-box").toggle(subcats.length > 0);

            renderEmpresas(productosData, 1);
        }
    }, 'json');
});


function fetchSubcategorias(idsCategorias) {
    $.post("../api/v1/fulmuv/subcategorias/byCategorias", { id_categoria: idsCategorias }, function (returned) {
        if (returned && returned.error === false) {
            subcategoriasHijas = returned.data || [];
            subcategoriasHijasSeleccionadas = []; // reinicia selección al cambiar categorías
            renderCheckboxSubcategorias(subcategoriasHijas);
            $("#subcats-box").toggle(subcategoriasHijas.length > 0);

        } else {
            subcategoriasHijas = [];
            $("#filtro-sub-categorias").html("");
            $("#subcats-box").hide(); // 👈 si no hay data, ocúltalo
        }
    }, 'json');
}

function renderCheckboxSubcategorias(subcats) {
    if (!Array.isArray(subcats)) subcats = [];

    let htmlVisible = '';
    let htmlOcultas = '';
    const maxVisible = 10;

    subcats.forEach((sc, index) => {
        const id = Number(sc.sub_categoria ?? sc.id_sub_categoria ?? sc.id_subcategoria);
        const item = `
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-sub">
        <label class="form-check-label fw-normal" for="subcat-${id}">
          ${capitalizarPrimeraLetra(sc.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += item; else htmlOcultas += item;
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
        <span class="icon">+</span>
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}
  `;

    $("#filtro-sub-categorias").html(htmlFinal);

    // Handler SOLO para subcategorías (namespace .sub)
    $(document)
        .off("click.moreSub", "#filtro-sub-categorias .more_categories-sub")
        .on("click.moreSub", "#filtro-sub-categorias .more_categories-sub", function () {
            $(this).toggleClass("show");
            $(this).prev(".more_slide_open-sub").slideToggle();
        });
}


function buildMarcasYModelos(data) {
    const marcasMap = new Map();  // id -> {id, nombre}
    const modelosMap = new Map(); // id -> {id, nombre}

    (data || []).forEach(p => {
        // --- MARCAS ---
        const marcaIds = parseIdsArray(p.id_marca); // e.g. ["193","195"]
        const marcaObjs = Array.isArray(p.marca) ? p.marca : [];

        marcaIds.forEach(id => {
            if (!marcasMap.has(id)) {
                const found = marcaObjs.find(m => Number(m.id) === Number(id));
                marcasMap.set(id, { id, nombre: found?.nombre || `Marca ${id}` });
            }
        });

        // --- MODELOS ---
        const modeloIds = parseIdsArray(p.id_modelo);
        const modeloObjs = Array.isArray(p.modelo) ? p.modelo
            : Array.isArray(p.modelo_productoo) ? p.modelo_productoo
                : Array.isArray(p.modelo_producto) ? [p.modelo_producto]
                    : [];

        modeloIds.forEach(id => {
            if (!modelosMap.has(id)) {
                const found = modeloObjs.find(m => Number(m.id) === Number(id));
                modelosMap.set(id, { id, nombre: found?.nombre || `Modelo ${id}` });
            }
        });
    });

    marcasUnicas = Array.from(marcasMap.values())
        .sort((a, b) => a.nombre.localeCompare(b.nombre));
    modelosUnicos = Array.from(modelosMap.values())
        .sort((a, b) => a.nombre.localeCompare(b.nombre));

    renderRadioMarcas(marcasUnicas);
    renderRadioModelos(modelosUnicos);
}


function renderRadioMarcas(marcas) {
    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input" type="radio" name="radio-marca" id="marca-all" value="" ${marcaSeleccionada === null ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="marca-all">Todos</label>
    </div>`;
    (marcas || []).forEach(m => {
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="radio-marca" id="marca-${m.id}" value="${m.id}" ${Number(marcaSeleccionada) === Number(m.id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="marca-${m.id}">${capitalizarPrimeraLetra(m.nombre)}</label>
      </div>`;
    });
    $("#filtro-marca").html(html);

    $(document)
        .off("change", "input[name='radio-marca']")
        .on("change", "input[name='radio-marca']", function () {
            const v = $(this).val();
            marcaSeleccionada = v === "" ? null : Number(v);
            renderEmpresas(productosData, 1);
        });
}

function renderRadioModelos(modelos) {
    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input" type="radio" name="radio-modelo" id="modelo-all" value="" ${modeloSeleccionado === null ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
    </div>`;
    (modelos || []).forEach(mo => {
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="radio-modelo" id="modelo-${mo.id}" value="${mo.id}" ${Number(modeloSeleccionado) === Number(mo.id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="modelo-${mo.id}">${capitalizarPrimeraLetra(mo.nombre)}</label>
      </div>`;
    });
    $("#filtro-modelo").html(html);

    $(document)
        .off("change", "input[name='radio-modelo']")
        .on("change", "input[name='radio-modelo']", function () {
            const v = $(this).val();
            modeloSeleccionado = v === "" ? null : Number(v);
            renderEmpresas(productosData, 1);
        });
}


// Selección/deselección de SUBCATEGORÍAS
$(document).on("change", "input[name='checkbox-sub']", function () {
    const id = Number($(this).val());
    if ($(this).is(":checked")) {
        if (!subcategoriasHijasSeleccionadas.includes(id)) subcategoriasHijasSeleccionadas.push(id);
    } else {
        subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => x !== id);
    }
    renderEmpresas(productosData, 1);
});


function renderEmpresas(data, page = 1) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    let empresasFiltradas = data.filter(prod => {
        const matchPrimeraCatProducto = primeraCategoriaEsProducto(prod);
        const matchSearch = matchBusquedaProducto(prod, searchText);

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        const matchSubHija = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);
        const matchMarca = (marcaSeleccionada === null) || prodBrand.includes(Number(marcaSeleccionada));
        const matchModelo = (modeloSeleccionado === null) || prodModel.includes(Number(modeloSeleccionado));

        const precio = Number(prod.precio_referencia);
        const matchPrecio = precio >= precioMin && precio <= precioMax;

        // ---------- Filtro por PROVINCIA / CANTÓN ----------
        const selProv = normalizarTexto(provinciaSel.nombre);
        const selCant = normalizarTexto(cantonSel.nombre);
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);

        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);
        // ---------------------------------------------------

        return matchSearch
            && matchPrimeraCatProducto
            && matchCat
            && matchSubHija
            && matchMarca
            && matchModelo
            && matchPrecio
            && matchProvincia
            && matchCanton;
    });

    // Ordenamiento
    if (sortOption === "mayor") {
        empresasFiltradas.sort((a, b) => b.precio_referencia - a.precio_referencia);
    } else if (sortOption === "menor") {
        empresasFiltradas.sort((a, b) => a.precio_referencia - b.precio_referencia);
    }

    // Paginación
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    // Renderizado
    let listProductos = "";

    empresasPagina.forEach(function (productos) {

        const tieneDescuento = parseFloat(productos.descuento) > 0;
        const precioDescuento = productos.precio_referencia - (productos.precio_referencia * productos.descuento / 100);
        let verificacion = "";
        const vendidosHoy = Number(productos.cantidad_vendida ?? 0);

        if (Array.isArray(productos.verificacion) && productos.verificacion.length > 0) {
            if (productos.verificacion[0].verificado == 1) {
                verificacion = `
                    <span class="verified-badge-floating">
                        <img src="../img/verificado_empresa.png" alt="Vendedor verificado" title="Vendedor verificado">
                    </span>`;
            }
        }

        listProductos += `
            <article class="product-card-modern">
                <a onclick="irADetalleProductoConTerminos(${productos.id_producto}); return false;" class="product-card-link">
                    <div class="product-media">
                        ${tieneDescuento ? `<span class="product-badge">-${parseInt(productos.descuento, 10)}%</span>` : ""}
                        ${verificacion}
                        <div class="sold-pill">Vendidos hoy: ${vendidosHoy}</div>
                        <img src="../admin/${productos.img_frontal}" alt="${capitalizarPrimeraLetra(productos.titulo_producto)}" onerror="this.src='../img/FULMUV_LOGO-13.png';">
                    </div>
                    <div class="product-body">
                        <div class="product-brand">${capitalizarPrimeraLetra(productos.marca?.[0]?.nombre || "General")}</div>
                        <div class="product-title">${capitalizarPrimeraLetra(productos.titulo_producto)}</div>
                        <div class="product-meta">${verificacion ? 'Cuenta con verificación activa' : 'Producto destacado del día'}</div>
                        <div class="product-footer">
                            <div class="product-price">
                                <strong>${formatoMoneda.format(tieneDescuento ? precioDescuento : productos.precio_referencia)}</strong>
                                ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(productos.precio_referencia)}</span>` : ''}
                            </div>
                            <button class="btn-circle-action" onclick="irADetalleProductoConTerminos(${productos.id_producto}); return false;" ><i class="fi-rs-arrow-small-right"></i></button>
                        </div>
                    </div>
                </a>
            </article>`;
    });

    $("#totalProductosGeneral").text(empresasFiltradas.length);
    $("#countProductos").text(`Encontramos ${empresasFiltradas.length} artículos`);
    $("#listaProductosContainer").html(listProductos || buildEmptyStateHtml(
        "No encontramos productos",
        "Prueba con otra marca, modelo, categoría, subcategoría o rango de precio para ver más resultados vendidos hoy."
    ));
    updateResultsButtonCount(empresasFiltradas.length);
    renderPaginacion(empresasFiltradas.length, page);
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

function renderCheckboxCategorias(categorias) {
    let htmlVisible = '';
    let htmlOcultas = '';
    const maxVisible = 10;

    (categorias || []).forEach((cat, index) => {
        const checkboxHTML = `
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="${cat.id_categoria}" id="categoria-${cat.id_categoria}" name="checkbox">
        <label class="form-check-label fw-normal" for="categoria-${cat.id_categoria}">
          ${capitalizarPrimeraLetra(cat.nombre)}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += checkboxHTML; else htmlOcultas += checkboxHTML;
    });

    const htmlFinal = `
    <div class="checkbox-list-visible">
      ${htmlVisible}
    </div>
    <div class="more_slide_open-cat" style="display:none;">
      ${htmlOcultas}
    </div>
    ${htmlOcultas ? `
      <div class="more_categories-cat cursor-pointer">
        <span class="icon">+</span> 
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}
  `;

    $("#filtro-categorias").html(htmlFinal);

    $(document)
        .off("click.moreCat", "#filtro-categorias .more_categories-cat")
        .on("click.moreCat", "#filtro-categorias .more_categories-cat", function () {
            $(this).toggleClass("show");
            $(this).prev(".more_slide_open-cat").slideToggle();
        });
}


$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(productosData, currentPage);
    }
});

// Normaliza texto (sin tildes, minúsculas)
function normalizarCadena(str) {
    return (str || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();
}

// Busca por palabras dentro del título/nombre/tags + ID
function matchBusquedaProducto(prod, searchText) {
    const textoBuscar = normalizarCadena(searchText);
    if (!textoBuscar) return true;

    const base = [
        prod.titulo_producto,
        prod.nombre,
        prod.tags
    ].map(normalizarCadena).join(' ');

    const palabras = textoBuscar.split(/\s+/).filter(Boolean);

    const todasPresentes = palabras.every(p => base.includes(p));

    const soloNumerosBusqueda = searchText.replace(/\s+/g, '');
    let idMatch = false;
    if (/^\d+$/.test(soloNumerosBusqueda)) {
        const idStr = String(prod.id_producto || '');
        idMatch = idStr.includes(soloNumerosBusqueda);
    }

    return todasPresentes || idMatch;
}


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
            } else {
                console.warn("El carrito expiró. Reiniciando...");
            }
        }
    } catch (e) {
        console.warn("carrito malformado, reiniciando...");
    }

    const existente = carrito.find(p => p.id === productoId);
    if (existente) {
        existente.cantidad += cantidad;
    } else {
        carrito.push({
            id: productoId,
            nombre: nombre,
            precio: precio,
            cantidad: cantidad,
            imagen: img
        });
    }

    localStorage.setItem("carrito", JSON.stringify({
        data: carrito,
        timestamp: new Date().getTime()
    }));

    actualizarIconoCarrito();
}


function inicializarSlider(maxPrecio) {
    const sliderElement = document.getElementById("slider-range");
    if (!sliderElement) return;

    if (sliderElement.noUiSlider) {
        try { sliderElement.noUiSlider.destroy(); } catch (_) { }
    }

    if (!Number.isFinite(maxPrecio) || maxPrecio <= 0) {
        sliderElement.innerHTML = "";

        if (moneyFormat?.to) {
            $("#slider-range-value1").text(moneyFormat.to(0));
            $("#slider-range-value2").text(moneyFormat.to(0));
        } else {
            $("#slider-range-value1").text("$0");
            $("#slider-range-value2").text("$0");
        }

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

        renderEmpresas(productosData, 1);
        updateFilterActiveCount();
    });
}


function cargarProvincias() {
    const selectProvincia = document.getElementById("selectProvincia");
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
            return;
        }

        provinciaSel.id = codProv;
        provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);

        resetSelectCanton();
        cargarCantones(codProv);
    });
}


function cargarCantones(codProvincia) {
    const selectCanton = document.getElementById("selectCanton");
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
            return;
        }

        const nombre = capitalizarPrimeraLetra(
            (datosEcuador[codProvincia].cantones[codCanton] || {}).canton || ""
        );
        cantonSel.id = codCanton;
        cantonSel.nombre = nombre;
    });
}

function resetSelectCanton() {
    const selectCanton = document.getElementById("selectCanton");
    selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
    cantonSel = { id: null, nombre: null };
}

function labelUbicacion() {
    if (provinciaSel.nombre && cantonSel.nombre) {
        return `PROVINCIA: ${provinciaSel.nombre}; CANTÓN: ${cantonSel.nombre}`;
    }
    if (provinciaSel.nombre) {
        return `PROVINCIA: ${provinciaSel.nombre}`;
    }
    return 'Cambiar ubicación';
}

function normalizarTexto(s) {
    return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();
}

function primeraCategoriaEsProducto(prod) {
    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    if (!ids.length || !catObjs.length) return false;

    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return !!(first && first.tipo === 'producto');
}

document.getElementById('guardarUbicacion').addEventListener('click', function () {
    actualizarUIUbicacionPersistir();
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);

    const modalEl = document.getElementById('modalUbicacion');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
});

function parseIdsArray(x) {
    if (Array.isArray(x)) return x.map(n => Number(n)).filter(Boolean);
    if (typeof x === 'string') {
        try {
            const j = JSON.parse(x);
            if (Array.isArray(j)) return j.map(n => Number(n)).filter(Boolean);
        } catch (_) { /* no json */ }
        return x.split(/[,\s]+/).map(Number).filter(Boolean);
    }
    if (x == null) return [];
    return [Number(x)].filter(Boolean);
}

function hasIntersection(idsArr, idsSet) {
    for (const id of idsArr) if (idsSet.has(Number(id))) return true;
    return false;
}

function buildCatsAndSubcatsFromProductos(productos) {
    const catsMap = new Map();               // catId -> {id_categoria, nombre}
    const subsPorCatMap = new Map();         // catId -> Map(subId -> {id_sub_categoria, nombre})

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
                if (!subMap.has(sid)) {
                    subMap.set(sid, { id_sub_categoria: sid, nombre: capitalizarPrimeraLetra(nombreSub) });
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
        const arr = catsIndex.subsPorCat.get(cid) || [];
        arr.forEach(sc => {
            if (!unicas.has(sc.id_sub_categoria)) unicas.set(sc.id_sub_categoria, sc);
        });
    });
    return Array.from(unicas.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}

function actualizarUIUbicacionPersistir() {
    const btn = document.getElementById('btnUbicacionPanel');
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

function filtrarPorUbicacionDataset(dataset) {
    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);
        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);
        return primeraCategoriaEsProducto(prod) && matchProvincia && matchCanton;
    });
}

function recheckCategoriasSeleccionadas() {
    const setSel = new Set(subcategoriasSeleccionadas.map(Number));
    setSel.forEach(id => {
        const el = document.getElementById(`categoria-${id}`);
        if (el) el.checked = true;
    });
}

function recheckSubcategoriasSeleccionadas() {
    const setSel = new Set(subcategoriasHijasSeleccionadas.map(Number));
    setSel.forEach(id => {
        const el = document.getElementById(`subcat-${id}`);
        if (el) el.checked = true;
    });
}

function validarMarcaModeloVigentes(data) {
    const brands = new Set(
        (data || []).flatMap(p => parseIdsArray(p.id_marca))
    );
    const models = new Set(
        (data || []).flatMap(p => parseIdsArray(p.id_modelo))
    );
    if (marcaSeleccionada !== null && !brands.has(Number(marcaSeleccionada))) {
        marcaSeleccionada = null;
    }
    if (modeloSeleccionado !== null && !models.has(Number(modeloSeleccionado))) {
        modeloSeleccionado = null;
    }
}

function refreshFiltersForCurrentLocation() {
    const locData = filtrarPorUbicacionDataset(productosData);

    const precios = (locData || [])
        .map(p => Number(p.precio_referencia))
        .filter(n => Number.isFinite(n) && n > 0);

    const maxPrecio = precios.length ? Math.max(...precios) : 0;
    inicializarSlider(maxPrecio);

    validarMarcaModeloVigentes(locData);
    buildMarcasYModelos(locData);

    catsIndex = buildCatsAndSubcatsFromProductos(locData);
    categoriasFiltradas = catsIndex.categoriasLista;

    const catDisponibles = new Set(categoriasFiltradas.map(c => Number(c.id_categoria)));
    subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(id => catDisponibles.has(Number(id)));

    renderCheckboxCategorias(categoriasFiltradas);
    recheckCategoriasSeleccionadas();

    const subcats = buildSubcatsForSelected(subcategoriasSeleccionadas.map(Number));
    const subDisponibles = new Set(subcats.map(s => Number(s.id_sub_categoria)));
    subcategoriasHijasSeleccionadas =
        subcategoriasHijasSeleccionadas.filter(id => subDisponibles.has(Number(id)));

    renderCheckboxSubcategorias(subcats);
    $("#subcats-box").toggle(subcats.length > 0);
    recheckSubcategoriasSeleccionadas();
    updateFilterActiveCount();
}

document.getElementById('limpiarUbicacion')?.addEventListener('click', () => {
    provinciaSel = { id: null, nombre: null };
    cantonSel = { id: null, nombre: null };

    const selectProvincia = document.getElementById('selectProvincia');
    const selectCanton = document.getElementById('selectCanton');
    if (selectProvincia) selectProvincia.value = '';
    resetSelectCanton();

    actualizarUIUbicacionPersistir();
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});

function clearAllFilters() {
    searchText = "";
    sortOption = "todos";
    subcategoriasSeleccionadas = [];
    subcategoriasHijasSeleccionadas = [];
    marcaSeleccionada = null;
    modeloSeleccionado = null;
    provinciaSel = { id: null, nombre: null };
    cantonSel = { id: null, nombre: null };

    $("#inputBusqueda").val("");
    $("#selectOrderPanel").val("todos");
    $("#selectProvincia").val("");
    resetSelectCanton();

    $.get("../api/v1/fulmuv/getProductosVendidosHoy/", function (returnedData) {
        if (!returnedData.error) {
            productosData = returnedData.data;
            refreshFiltersForCurrentLocation();
            renderEmpresas(productosData, 1);
        }
    }, 'json');
}
