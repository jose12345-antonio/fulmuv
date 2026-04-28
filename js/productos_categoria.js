/* ============================================================
   productos_categoria.js  (COMPLETO)
   - Carga productos UNA sola vez (idCategoria)
   - Filtra TODO local (sin volver a llamar idCategoria)
   - Marca y Modelo con CHECKBOX
   - Modelos se REDUCEN según marcas seleccionadas
   - Incluye TODAS las funciones auxiliares
   ============================================================ */

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

// ✅ Dataset original (NO se vuelve a pedir ni se pisa)
let productosDataAll = [];

// Opciones
let sortOption = "todos"; // "mayor", "menor", "todos"
let searchText = "";
let id_categoria = $("#id_categoria").val();

// Categorías y subcategorías (local)
let subcategoriasSeleccionadas = [];          // (en tu UI se llaman "Categorías")
let subcategoriasHijasSeleccionadas = [];     // "Sub categorías"

let catsIndex = null;                         // índice cats/subcats construido desde productos
let categoriasFiltradas = [];
let categoriasPermitidasPrincipal = new Set();
let filtroTextoMarca = "";
let filtroTextoModelo = "";
let filtroTextoCategoria = "";

// Marca/Modelo (checkbox)
let marcasUnicas = [];
let modelosUnicos = [];
let marcasSeleccionadas = new Set();          // ids string
let modelosSeleccionados = new Set();         // ids string

// Precio
let precioMin = 0;
let precioMax = Infinity;

let moneyFormat = wNumb({
    decimals: 0,
    thousand: ",",
    prefix: "$"
});

// Ubicación
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let datosEcuador = {};

function parseMysqlDatetime(dtStr) {
    if (!dtStr) return null;
    return new Date(String(dtStr).replace(" ", "T"));
}

function isMembresiaActiva(item) {
    const memb = item?.membresia;
    if (!memb) return true;
    const estado = (memb.estado_membresia || memb.estado || "").toString().toUpperCase();
    if (estado && estado !== "ACTIVA") return false;
    const now = new Date();
    const inicio = parseMysqlDatetime(memb.fecha_inicio || "");
    const fin = parseMysqlDatetime(memb.fecha_fin || "");
    if (inicio && now.getTime() < inicio.getTime()) return false;
    if (fin && now.getTime() > fin.getTime()) return false;
    return true;
}

function filterByMembresiaActiva(items) {
    return (items || []).filter(isMembresiaActiva);
}


// /* ====================== Formato moneda ====================== */
// let formatoMoneda = new Intl.NumberFormat('es-EC', {
//     style: 'currency',
//     currency: 'USD',
//     minimumFractionDigits: 2
// });

/* ====================== Cargar JSON provincias ====================== */
fetch('provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

/* ====================== INIT ====================== */
$(document).ready(function () {
    actualizarIconoCarrito();

    $("#breadcrumb").append(`
    <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
    <span></span> Lista de Productos
  `);

    // 1) Categorías principales para obtener IDs
    $.get("api/v1/fulmuv/categoriasByPrincipales/" + id_categoria, function (returnedDataCategoria) {
        if (returnedDataCategoria?.error) return;

        const idsCategorias = (returnedDataCategoria.data || [])
            .map(cat => parseInt(cat.id_categoria))
            .filter(n => Number.isFinite(n));
        categoriasPermitidasPrincipal = new Set(idsCategorias);

        // 2) Productos UNA sola vez
        $.post("api/v1/fulmuv/productos/idCategoria", { id_categoria: idsCategorias }, function (returnedData) {
            if (returnedData?.error) return;

            productosDataAll = shuffleArray(filterByMembresiaActiva(returnedData.data || []));

            // Index categorías/subcategorías desde productos
            refreshAvailableFilters();

            // Slider max
            const precios = productosDataAll.map(p => Number(p.precio_referencia)).filter(n => Number.isFinite(n) && n > 0);
            const maxPrecio = precios.length ? Math.max(...precios) : 0;
            inicializarSlider(maxPrecio);

            // Marcas/Modelos base (según filtros actuales)
            // Render inicial
            renderEmpresas(productosDataAll, 1);

        }, 'json');

    }, 'json');
});


/* ====================== EVENTOS: BÚSQUEDA ====================== */
$(".widget_search input").on("input", function () {
    searchText = $(this).val().trim();

    // Recalcular opciones disponibles en sidebar (marca/modelo) según filtros
    refreshAvailableFilters();

    renderEmpresas(productosDataAll, 1);
});


/* ====================== EVENTOS: PAGINACIÓN ====================== */
$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(productosDataAll, currentPage);
    }
});


$(document).on('keydown', '.widget_search input', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

$(document).on('submit', '.widget_search form', function (e) {
    e.preventDefault();
    return false;
});


/* ====================== EVENTOS: ORDEN / SHOW ====================== */
$(".sort-show li a").on("click", function (e) {
    e.preventDefault();
    $(".sort-show li a").removeClass("active");
    $(this).addClass("active");

    const value = $(this).data("value");
    itemsPerPage = value === "all" ? productosDataAll.length : parseInt(value);

    $(".sort-by-product-wrap:first .sort-by-dropdown-wrap span")
        .html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    currentPage = 1;
    renderEmpresas(productosDataAll, 1);
});

$(".sort-order li a").on("click", function (e) {
    e.preventDefault();
    $(".sort-order li a").removeClass("active");
    $(this).addClass("active");

    sortOption = $(this).data("value");

    $(".sort-by-product-wrap:last .sort-by-dropdown-wrap span")
        .html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    renderEmpresas(productosDataAll, 1);
});


/* ====================== EVENTOS: CHECKBOX CATEGORÍAS (LOCAL) ====================== */
$(document).on("change", "input[name='checkbox']", function () {
    if ($(this).val() === "__all__") {
        subcategoriasSeleccionadas = [];
        subcategoriasHijasSeleccionadas = [];
        $("input[name='checkbox']").not(this).prop("checked", false);
        $(this).prop("checked", true);
        $("#filtro-sub-categorias").html("");
        $("#subcats-box").hide();
        refreshAvailableFilters();
        renderEmpresas(productosDataAll, 1);
        return;
    }

    const id = Number($(this).val());
    if (!Number.isFinite(id)) return;

    if (this.checked) {
        if (!subcategoriasSeleccionadas.includes(id)) subcategoriasSeleccionadas.push(id);
    } else {
        subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(x => Number(x) !== id);
    }
    $("#categoria-all").prop("checked", subcategoriasSeleccionadas.length === 0);

    const hayCats = subcategoriasSeleccionadas.length > 0;
    $("#subcats-box").toggle(hayCats);

    if (!hayCats) {
        // limpiar subcats
        subcategoriasHijasSeleccionadas = [];
        $("#filtro-sub-categorias").html("");
        $("#subcats-box").hide();
    } else {
        const subcats = buildSubcatsForSelected(subcategoriasSeleccionadas.map(Number));
        renderCheckboxSubcategorias(subcats);
        $("#subcats-box").toggle(subcats.length > 0);

        // limpiar subcats seleccionadas que ya no existan
        const allowed = new Set(subcats.map(s => Number(s.id_sub_categoria)));
        subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => allowed.has(Number(x)));
        recheckSubcategoriasSeleccionadas();
    }

    // recalcular marcas/modelos disponibles según filtros actuales
    refreshAvailableFilters();

    renderEmpresas(productosDataAll, 1);
});


/* ====================== EVENTOS: CHECKBOX SUBCATEGORÍAS (LOCAL) ====================== */
$(document).on("change", "input[name='checkbox-sub']", function () {
    if ($(this).val() === "__all__") {
        subcategoriasHijasSeleccionadas = [];
        $("input[name='checkbox-sub']").not(this).prop("checked", false);
        $(this).prop("checked", true);
        refreshAvailableFilters();
        renderEmpresas(productosDataAll, 1);
        return;
    }

    const id = Number($(this).val());
    if (!Number.isFinite(id)) return;

    if (this.checked) {
        if (!subcategoriasHijasSeleccionadas.includes(id)) subcategoriasHijasSeleccionadas.push(id);
    } else {
        subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => Number(x) !== id);
    }
    $("#subcat-all").prop("checked", subcategoriasHijasSeleccionadas.length === 0);

    refreshAvailableFilters();

    renderEmpresas(productosDataAll, 1);
});


/* ====================== EVENTOS: CHECKBOX MARCA/MODELO ====================== */
$(document).off("change.chkMarca").on("change.chkMarca", ".chk-marca", function () {
    if ($(this).val() === "__all__") {
        marcasSeleccionadas.clear();
        $(".chk-marca").not(this).prop("checked", false);
        $(this).prop("checked", true);
        refreshAvailableFilters();
        renderEmpresas(productosDataAll, 1);
        return;
    }

    const id = String($(this).val());
    this.checked ? marcasSeleccionadas.add(id) : marcasSeleccionadas.delete(id);
    $("#marca-all").prop("checked", marcasSeleccionadas.size === 0);

    // reduce modelos según marcas
    refreshAvailableFilters();

    renderEmpresas(productosDataAll, 1);
});

$(document).off("change.chkModelo").on("change.chkModelo", ".chk-modelo", function () {
    if ($(this).val() === "__all__") {
        modelosSeleccionados.clear();
        $(".chk-modelo").not(this).prop("checked", false);
        $(this).prop("checked", true);
        refreshAvailableFilters();
        renderEmpresas(productosDataAll, 1);
        return;
    }

    const id = String($(this).val());
    this.checked ? modelosSeleccionados.add(id) : modelosSeleccionados.delete(id);
    $("#modelo-all").prop("checked", modelosSeleccionados.size === 0);

    refreshAvailableFilters();
    renderEmpresas(productosDataAll, 1);
});

$(document).off("input.filterOptionSearch").on("input.filterOptionSearch", ".filter-option-search", function () {
    const tipo = $(this).data("filter-type");
    const valor = $(this).val().trim();

    if (tipo === "marca") {
        filtroTextoMarca = valor;
        applyFilterOptionSearch("marca", valor);
        return;
    }

    if (tipo === "modelo") {
        filtroTextoModelo = valor;
        applyFilterOptionSearch("modelo", valor);
        return;
    }

    if (tipo === "categoria") {
        filtroTextoCategoria = valor;
        applyFilterOptionSearch("categoria", valor);
    }
});


/* ====================== RENDER PRINCIPAL (GRID) ====================== */
function renderEmpresas(dataset, page = 1) {
    const empresasFiltradas = applyAllFilters(dataset);

    if (searchText) {
        empresasFiltradas.sort((a, b) => getBusquedaProductoScore(b, searchText) - getBusquedaProductoScore(a, searchText));
    }

    // Orden
    if (sortOption === "mayor") empresasFiltradas.sort((a, b) => (getBusquedaProductoScore(b, searchText) - getBusquedaProductoScore(a, searchText)) || (Number(b.precio_referencia) - Number(a.precio_referencia)));
    if (sortOption === "menor") empresasFiltradas.sort((a, b) => (getBusquedaProductoScore(b, searchText) - getBusquedaProductoScore(a, searchText)) || (Number(a.precio_referencia) - Number(b.precio_referencia)));

    // Paginación
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    let listProductos = "";

    empresasPagina.forEach(function (productos) {
        const tieneDescuento = parseFloat(productos.descuento) > 0;
        const precioRef = Number(productos.precio_referencia) || 0;
        const descuento = Number(productos.descuento) || 0;
        const precioDescuento = precioRef - (precioRef * descuento / 100);

        let verificacion = "";
        if (Array.isArray(productos.verificacion) && productos.verificacion.length > 0 && productos.verificacion[0].verificado == 1) {
            verificacion = `
        <span class="fw-bold product-category" style="font-size: 12px;">
          <i class="fi-rs-check ms-1"></i> Vendedor Verificado
        </span>`;
        }

        listProductos += `
      <div class="col-md-4 col-lg-3 col-sm-4 col-12 mb-2 d-flex">
        <div class="product-cart-wrap w-100 d-flex flex-column">
          <div class="product-img-action-wrap text-center">
            <div class="product-img product-img-zoom">
              <a href="detalle_productos.php?q=${productos.id_producto}" target="_blank" rel="noopener noreferrer">
                <img class="default-img img-fluid mb-1"
                  src="admin/${productos.img_frontal}"
                  onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"
                  style="object-fit: contain; width: 100%; height: 200px">
                <img class="hover-img img-fluid d-none"
                  src="admin/${productos.img_posterior}"
                  onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"
                  style="object-fit: contain; width: 100%; height: 200px">
              </a>
            </div>
            ${tieneDescuento ? `
              <div class="product-badges product-badges-position product-badges-mrg">
                <span class="best">-${parseInt(descuento)}%</span>
              </div>` : ''}
          </div>

          <div class="product-content-wrap p-1">
            <div class="text-end">${verificacion}</div>
            <h2 class="text-center">
              <a href="detalle_productos.php?q=${productos.id_producto}" target="_blank" rel="noopener noreferrer" onclick="irADetalleProductoConTerminos(${productos.id_producto}); return false;" class="limitar-lineas mt-1 fw-normal">
                ${capitalizarPrimeraLetra(productos.titulo_producto)}
              </a>
            </h2>
            <div class="product-price mb-2 mt-0 text-center">
             <span style="font-size: 24px">${formatPrecioSuperscript(tieneDescuento ? precioDescuento : precioRef)}</span>
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

function formatPrecioSuperscript(valor) {
    const num = Number(valor) || 0;
    const entero = Math.floor(num);
    const centavos = Math.round((num - entero) * 100).toString().padStart(2, '0');
    const enteroFormateado = entero.toLocaleString('es-EC');
    return `
        <span style="font-size:0.55em; font-weight:normal; vertical-align:middle;">US$</span>
        <strong style="font-size:1.1em;">${enteroFormateado}</strong><sup style="font-size:0.45em; font-weight:normal; vertical-align:super; line-height:1;">${centavos}</sup>
    `;
}
/* ====================== FILTROS: APLICAR TODO ====================== */
function applyAllFilters(dataset) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        // 1) Seguridad: categoría tipo "producto"
        if (!primeraCategoriaEsProducto(prod)) return false;

        // 2) búsqueda
        if (!matchBusquedaProducto(prod, searchText)) return false;

        // 3) categorías/subcategorías
        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        if (!matchCat) return false;

        const matchSub = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);
        if (!matchSub) return false;

        // 4) marca/modelo
        const prodBrandIds = parseIdsArray(prod.id_marca).map(String);
        const prodModelIds = parseIdsArray(prod.id_modelo).map(String);

        const matchMarca = (marcasSeleccionadas.size === 0) || prodBrandIds.some(id => marcasSeleccionadas.has(id));
        if (!matchMarca) return false;

        const matchModelo = (modelosSeleccionados.size === 0) || prodModelIds.some(id => modelosSeleccionados.has(id));
        if (!matchModelo) return false;

        // 5) precio
        const precio = Number(prod.precio_referencia);
        if (!(precio >= precioMin && precio <= precioMax)) return false;

        // 6) provincia/cantón
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);

        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);

        return matchProvincia && matchCanton;
    });
}


/* ======================
   BASE DATASET para construir marcas/modelos disponibles
   Aplica: ubicación + búsqueda + categoría/subcat + precio
   NO aplica: marca/modelo (porque justamente se construyen)
====================== */
function getBaseDatasetForFilters() {
    return getDynamicFilterDataset();
}

function getDynamicFilterDataset(options = {}) {
    const {
        includeCategorias = true,
        includeSubcategorias = true,
        includeMarcas = true,
        includeModelos = true
    } = options;

    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));
    const selectedBrandsSet = new Set([...marcasSeleccionadas].map(String));
    const selectedModelsSet = new Set([...modelosSeleccionados].map(String));

    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (productosDataAll || []).filter(prod => {
        if (!primeraCategoriaEsProducto(prod)) return false;
        if (!matchBusquedaProducto(prod, searchText)) return false;

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrandIds = parseIdsArray(prod.id_marca).map(String);
        const prodModelIds = parseIdsArray(prod.id_modelo).map(String);

        const matchCat = !includeCategorias || (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        if (!matchCat) return false;

        const matchSub = !includeSubcategorias || (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);
        if (!matchSub) return false;

        const matchMarca = !includeMarcas || (selectedBrandsSet.size === 0) || prodBrandIds.some(id => selectedBrandsSet.has(id));
        if (!matchMarca) return false;

        const matchModelo = !includeModelos || (selectedModelsSet.size === 0) || prodModelIds.some(id => selectedModelsSet.has(id));
        if (!matchModelo) return false;

        const precio = Number(prod.precio_referencia);
        if (!(precio >= precioMin && precio <= precioMax)) return false;

        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);

        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);

        return matchProvincia && matchCanton;
    });
}


/* ====================== MARCAS / MODELOS (CHECKBOX) ====================== */
function buildMarcasYModelos(data) {
    const marcasMap = new Map();   // id -> {id, nombre}
    const modelosMap = new Map();  // id -> {id, nombre}

    (data || []).forEach(p => {
        // MARCAS
        const marcaIds = parseIdsArray(p.id_marca);
        const marcaObjs = Array.isArray(p.marca) ? p.marca : [];

        marcaIds.forEach(id => {
            const sid = String(id);
            if (!marcasMap.has(sid)) {
                const found = marcaObjs.find(m => String(m.id) === sid);
                marcasMap.set(sid, { id: sid, nombre: found?.nombre || `Marca ${sid}` });
            }
        });

        // MODELOS
        const modeloIds = parseIdsArray(p.id_modelo);
        const modeloObjs = Array.isArray(p.modelo) ? p.modelo
            : Array.isArray(p.modelo_productoo) ? p.modelo_productoo
                : Array.isArray(p.modelo_producto) ? [p.modelo_producto]
                    : [];

        modeloIds.forEach(id => {
            const sid = String(id);
            if (!modelosMap.has(sid)) {
                const found = modeloObjs.find(m => String(m.id) === sid);
                modelosMap.set(sid, { id: sid, nombre: found?.nombre || `Modelo ${sid}` });
            }
        });
    });

    marcasUnicas = Array.from(marcasMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
    modelosUnicos = Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));

    // limpiar selecciones inválidas
    const allowedBrands = new Set(marcasUnicas.map(m => String(m.id)));
    marcasSeleccionadas = new Set([...marcasSeleccionadas].filter(id => allowedBrands.has(id)));

    const allowedModels = new Set(modelosUnicos.map(m => String(m.id)));
    modelosSeleccionados = new Set([...modelosSeleccionados].filter(id => allowedModels.has(id)));

    renderCheckboxMarcas(marcasUnicas);
    renderCheckboxModelos(modelosUnicos);
}

function renderCheckboxMarcas(marcas) {
    let html = renderFilterSearchInput("marca", "Buscar marca", filtroTextoMarca) + `
    <div class="form-check mb-2">
      <input class="form-check-input chk-marca" type="checkbox" id="marca-all" value="__all__" ${marcasSeleccionadas.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="marca-all">Todos</label>
    </div>`;
    (marcas || []).forEach(m => {
        const id = String(m.id);
        const searchableName = capitalizarPrimeraLetra(m.nombre);
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(searchableName))}">
        <input class="form-check-input chk-marca" type="checkbox" id="marca-${id}" value="${id}"
          ${marcasSeleccionadas.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="marca-${id}">
          ${searchableName}
        </label>
      </div>`;
    });
    html += `<small class="text-muted filter-option-empty"${marcas?.length ? ' style="display:none;"' : ''}>No hay marcas.</small>`;
    $("#filtro-marca").html(html);
    applyFilterOptionSearch("marca", filtroTextoMarca);
}

function renderCheckboxModelos(modelos) {
    let html = renderFilterSearchInput("modelo", "Buscar modelo", filtroTextoModelo) + `
    <div class="form-check mb-2">
      <input class="form-check-input chk-modelo" type="checkbox" id="modelo-all" value="__all__" ${modelosSeleccionados.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
    </div>`;
    (modelos || []).forEach(m => {
        const id = String(m.id);
        const searchableName = capitalizarPrimeraLetra(m.nombre);
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(searchableName))}">
        <input class="form-check-input chk-modelo" type="checkbox" id="modelo-${id}" value="${id}"
          ${modelosSeleccionados.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="modelo-${id}">
          ${searchableName}
        </label>
      </div>`;
    });
    html += `<small class="text-muted filter-option-empty"${modelos?.length ? ' style="display:none;"' : ''}>No hay modelos.</small>`;
    $("#filtro-modelo").html(html);
    applyFilterOptionSearch("modelo", filtroTextoModelo);
}


/* ====================== MODELOS DEPENDEN DE MARCA ====================== */
function getMarcaIdsProd(prod) {
    return parseIdsArray(prod.id_marca).map(String);
}
function getModeloIdsProd(prod) {
    return parseIdsArray(prod.id_modelo).map(String);
}

function computeModelosDisponiblesPorMarcas(baseData) {
    const modelosMap = new Map(); // id -> {id, nombre}

    (baseData || []).forEach(p => {
        const marcasProd = getMarcaIdsProd(p);
        const modelosProd = getModeloIdsProd(p);

        const pasaMarca = (marcasSeleccionadas.size === 0) || marcasProd.some(id => marcasSeleccionadas.has(id));
        if (!pasaMarca) return;

        const modeloObjs = Array.isArray(p.modelo) ? p.modelo : [];
        modelosProd.forEach(mid => {
            if (!modelosMap.has(mid)) {
                const found = modeloObjs.find(o => String(o.id) === String(mid));
                modelosMap.set(mid, { id: String(mid), nombre: found?.nombre || `Modelo ${mid}` });
            }
        });
    });

    return Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}

function reduceModelosPorMarca() {
    const base = getBaseDatasetForFilters();
    const modelosDisponibles = computeModelosDisponiblesPorMarcas(base);

    const allowed = new Set(modelosDisponibles.map(m => String(m.id)));
    modelosSeleccionados = new Set([...modelosSeleccionados].filter(id => allowed.has(id)));

    renderCheckboxModelos(modelosDisponibles);
}


/* ====================== CATEGORÍAS UI ====================== */
function computeMarcasDisponibles(data) {
    const marcasMap = new Map();

    (data || []).forEach(p => {
        const marcaIds = parseIdsArray(p.id_marca);
        const marcaObjs = Array.isArray(p.marca) ? p.marca : [];

        marcaIds.forEach(id => {
            const sid = String(id);
            if (marcasMap.has(sid)) return;
            const found = marcaObjs.find(m => String(m.id) === sid);
            marcasMap.set(sid, { id: sid, nombre: found?.nombre || `Marca ${sid}` });
        });
    });

    return Array.from(marcasMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}

function computeModelosDisponibles(data) {
    const modelosMap = new Map();

    (data || []).forEach(p => {
        const modeloIds = parseIdsArray(p.id_modelo);
        const modeloObjs = Array.isArray(p.modelo) ? p.modelo
            : Array.isArray(p.modelo_productoo) ? p.modelo_productoo
                : Array.isArray(p.modelo_producto) ? [p.modelo_producto]
                    : [];

        modeloIds.forEach(id => {
            const sid = String(id);
            if (modelosMap.has(sid)) return;
            const found = modeloObjs.find(m => String(m.id) === sid);
            modelosMap.set(sid, { id: sid, nombre: found?.nombre || `Modelo ${sid}` });
        });
    });

    return Array.from(modelosMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
}

function renderFilterSearchInput(tipo, placeholder, value) {
    return `
    <div class="mb-3">
      <input
        type="text"
        class="form-control form-control-sm filter-option-search"
        data-filter-type="${tipo}"
        placeholder="${placeholder}"
        value="${String(value || '').replace(/"/g, '&quot;')}">
    </div>`;
}

function escapeHtmlAttribute(value) {
    return String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

function matchesMixedSearch(candidate, query) {
    const normalizedCandidate = normalizarTexto(candidate);
    const normalizedQuery = normalizarTexto(query);
    if (!normalizedQuery) return true;
    const terms = normalizedQuery.split(/\s+/).filter(Boolean);
    return terms.every(term => normalizedCandidate.includes(term));
}

function applyFilterOptionSearch(tipo, valor) {
    const container = $(`#filtro-${tipo === "categoria" ? "categorias" : tipo}`);
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
    if (emptyLabel.length) {
        emptyLabel.toggle(visibles === 0);
    }

    if (tipo === "categoria") {
        const moreButton = container.find(".more_categories-cat");
        const hiddenBlock = container.find(".more_slide_open-cat");
        if (moreButton.length) {
            moreButton.toggle(visibles > 10);
        }
        if (hiddenBlock.length && visibles <= 10) {
            hiddenBlock.hide();
            moreButton.removeClass("show");
        }
    }
}

function refreshAvailableFilters() {
    const categoriasBase = getDynamicFilterDataset({
        includeCategorias: false,
        includeSubcategorias: false
    });

    const catsIndexBase = buildCatsAndSubcatsFromProductos(categoriasBase);
    const usarFiltroPrincipal = categoriasPermitidasPrincipal.size > 0;

    categoriasFiltradas = (catsIndexBase.categoriasLista || []).filter(cat => {
        return !usarFiltroPrincipal || categoriasPermitidasPrincipal.has(Number(cat.id_categoria));
    });

    catsIndex = {
        categoriasLista: categoriasFiltradas,
        subsPorCat: new Map(
            Array.from((catsIndexBase.subsPorCat || new Map()).entries()).filter(([cid]) => {
                return !usarFiltroPrincipal || categoriasPermitidasPrincipal.has(Number(cid));
            })
        )
    };

    const categoriasPermitidas = new Set(categoriasFiltradas.map(cat => Number(cat.id_categoria)));
    subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(id => categoriasPermitidas.has(Number(id)));
    renderCheckboxCategorias(categoriasFiltradas);

    const subcatsDisponibles = subcategoriasSeleccionadas.length
        ? buildSubcatsForSelected(subcategoriasSeleccionadas.map(Number))
        : [];
    const subcatsPermitidas = new Set(subcatsDisponibles.map(sc => Number(sc.id_sub_categoria)));
    subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(id => subcatsPermitidas.has(Number(id)));

    const mostrarSubcategorias = subcategoriasSeleccionadas.length > 0 && subcatsDisponibles.length > 0;
    $("#subcats-box").toggle(mostrarSubcategorias);

    if (mostrarSubcategorias) {
        renderCheckboxSubcategorias(subcatsDisponibles);
        recheckSubcategoriasSeleccionadas();
    } else {
        $("#filtro-sub-categorias").html("");
        const subcatCollapse = document.getElementById("collapseSubcategorias");
        if (subcatCollapse && subcatCollapse.classList.contains("show")) {
            bootstrap.Collapse.getOrCreateInstance(subcatCollapse, { toggle: false }).hide();
        }
    }

    marcasUnicas = computeMarcasDisponibles(getDynamicFilterDataset({ includeMarcas: false }));
    const marcasPermitidas = new Set(marcasUnicas.map(m => String(m.id)));
    marcasSeleccionadas = new Set([...marcasSeleccionadas].filter(id => marcasPermitidas.has(id)));
    renderCheckboxMarcas(marcasUnicas);

    modelosUnicos = computeModelosDisponibles(getDynamicFilterDataset({ includeModelos: false }));
    const modelosPermitidos = new Set(modelosUnicos.map(m => String(m.id)));
    modelosSeleccionados = new Set([...modelosSeleccionados].filter(id => modelosPermitidos.has(id)));
    renderCheckboxModelos(modelosUnicos);
}

function renderCheckboxCategorias(categorias) {
    let htmlVisible = `
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="__all__" id="categoria-all" name="checkbox" ${subcategoriasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-all">Todos</label>
      </div>`;
    let htmlOcultas = '';
    const maxVisible = 10;

    (categorias || []).forEach((cat, index) => {
        const id = Number(cat.id_categoria);
        const checked = subcategoriasSeleccionadas.includes(id);
        const searchableName = capitalizarPrimeraLetra(cat.nombre);

        const checkboxHTML = `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(searchableName))}">
        <input class="form-check-input" type="checkbox" value="${id}" id="categoria-${id}" name="checkbox" ${checked ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-${id}">
          ${searchableName}
        </label>
      </div>`;

        if (index < maxVisible) htmlVisible += checkboxHTML; else htmlOcultas += checkboxHTML;
    });

    const htmlFinal = `
    ${renderFilterSearchInput("categoria", "Buscar categoria", filtroTextoCategoria)}
    <div class="checkbox-list-visible">
      ${htmlVisible}
    </div>
    <div class="more_slide_open-cat" style="display:none;">
      ${htmlOcultas}
    </div>
    <small class="text-muted filter-option-empty"${categorias?.length ? ' style="display:none;"' : ''}>No hay categorias.</small>
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

    applyFilterOptionSearch("categoria", filtroTextoCategoria);
}


/* ====================== SUBCATEGORÍAS UI ====================== */
function renderCheckboxSubcategorias(subcats) {
    if (!Array.isArray(subcats)) subcats = [];

    let htmlVisible = `
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="__all__" id="subcat-all" name="checkbox-sub" ${subcategoriasHijasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="subcat-all">Todos</label>
      </div>`;
    let htmlOcultas = '';
    const maxVisible = 10;

    subcats.forEach((sc, index) => {
        const id = Number(sc.sub_categoria ?? sc.id_sub_categoria ?? sc.id_subcategoria);
        const checked = subcategoriasHijasSeleccionadas.includes(id);

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

    $(document)
        .off("click.moreSub", "#filtro-sub-categorias .more_categories-sub")
        .on("click.moreSub", "#filtro-sub-categorias .more_categories-sub", function () {
            $(this).toggleClass("show");
            $(this).prev(".more_slide_open-sub").slideToggle();
        });
}

function recheckSubcategoriasSeleccionadas() {
    const setSel = new Set(subcategoriasHijasSeleccionadas.map(Number));
    setSel.forEach(id => {
        const el = document.getElementById(`subcat-${id}`);
        if (el) el.checked = true;
    });
}


/* ====================== SLIDER PRECIO ====================== */
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

        refreshAvailableFilters();

        renderEmpresas(productosDataAll, 1);
    });
}


/* ====================== UBICACIÓN ====================== */
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

    // ✅ NO addEventListener (se acumula). Usa onchange:
    selectProvincia.onchange = (e) => {
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
    };
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

    // ✅ Igual: onchange en vez de addEventListener
    selectCanton.onchange = (e) => {
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
    };
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

    refreshAvailableFilters();

    renderEmpresas(productosDataAll, 1);

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

    refreshAvailableFilters();

    renderEmpresas(productosDataAll, 1);
});


/* ====================== CATEGORÍA TIPO PRODUCTO ====================== */
function primeraCategoriaEsProducto(prod) {
    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    if (!ids.length || !catObjs.length) return false;

    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return !!(first && first.tipo === 'producto');
}


/* ====================== MATCH BÚSQUEDA ====================== */
function normalizarCadena(str) {
    return (str || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();
}

function matchBusquedaProducto(prod, searchText) {
    const textoBuscar = normalizarCadena(searchText);
    if (!textoBuscar) return true;
    return getBusquedaProductoScore(prod, searchText) > 0;
}

function getBusquedaProductoScore(prod, searchText) {
    const textoBuscar = normalizarCadena(searchText);
    if (!textoBuscar) return 0;

    const base = [
        prod.titulo_producto,
        prod.nombre,
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
    ].map(normalizarCadena).join(' ');

    const palabras = Array.from(new Set(textoBuscar.split(/\s+/).filter(Boolean)));
    const idStr = String(prod.id_producto || prod.id_vehiculo || '');
    return palabras.reduce((total, palabra) => total + ((base.includes(palabra) || idStr.includes(palabra)) ? 1 : 0), 0);
}


/* ====================== BUILD CATS / SUBCATS DESDE PRODUCTOS ====================== */
function buildCatsAndSubcatsFromProductos(productos) {
    const catsMap = new Map();       // catId -> {id_categoria, nombre}
    const subsPorCatMap = new Map(); // catId -> Map(subId -> {id_sub_categoria, nombre})
    const principalActiva = Number(id_categoria);

    (productos || []).forEach(p => {
        const catObjsRaw = Array.isArray(p.categorias) ? p.categorias : [];
        const catObjs = catObjsRaw.filter(o => {
            const principalObj = Number(o?.id_categoria_principal ?? o?.categoria_principal ?? o?.id_principal);
            return !Number.isFinite(principalActiva) || !Number.isFinite(principalObj) || principalObj === principalActiva;
        });
        let catIds = parseIdsArray(p.categoria ?? p.id_categoria);
        if (catObjs.length) {
            const idsDeObjs = catObjs.map(o => Number(o.id)).filter(Boolean);
            catIds = Array.from(new Set([...catIds, ...idsDeObjs]));
        }
        if (categoriasPermitidasPrincipal.size > 0) {
            catIds = catIds.filter(cid => categoriasPermitidasPrincipal.has(Number(cid)));
        }

        const subObjsRaw = Array.isArray(p.subcategorias) ? p.subcategorias
            : Array.isArray(p.sub_categorias) ? p.sub_categorias
                : [];
        const subObjs = subObjsRaw.filter(o => {
            const categoriaPadre = Number(o?.id_categoria ?? o?.categoria ?? o?.id_categoria_secundaria);
            return !catIds.length || !Number.isFinite(categoriaPadre) || catIds.includes(categoriaPadre);
        });
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

    const categoriasLista = Array.from(catsMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));

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


/* ====================== PAGINACIÓN HTML ====================== */
function renderPaginacion(totalItems, currentPage) {
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
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


/* ====================== HELPERS GENERALES ====================== */
function capitalizarPrimeraLetra(texto) {
    return window.fulmuvTitleCase ? window.fulmuvTitleCase(texto) : ((texto || '').toString());
}

function normalizarTexto(s) {
    return (s || '').toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase();
}

// Convierte "['1','2']" | [1,2] | "1,2" -> [1,2]
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


/* ====================== CARRITO (mínimo, para que no falle) ====================== */
function actualizarIconoCarrito() {
    // Si tu header tiene contador, aquí lo actualizas.
    // Como no pegaste tu HTML del contador, lo dejo seguro:
    try {
        const stored = JSON.parse(localStorage.getItem("carrito") || "{}");
        const cantidad = Array.isArray(stored.data) ? stored.data.reduce((a, x) => a + (Number(x.cantidad) || 0), 0) : 0;
        $("#carritoCount").text(cantidad); // si existe #carritoCount
    } catch (e) {
        // nada
    }
}


/* ====================== LINK DETALLE (placeholder) ====================== */
function irADetalleProductoConTerminos(id_producto) {
    if (typeof window.irADetalleProductoConTerminos === "function" && window.irADetalleProductoConTerminos !== irADetalleProductoConTerminos) {
        return window.irADetalleProductoConTerminos(id_producto);
    }

    window.open(`detalle_productos.php?q=${id_producto}`, '_blank');
}
