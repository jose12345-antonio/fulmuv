/* ============================================================
   productos_categoria.js  (COMPLETO)
   - Carga productos UNA sola vez (idCategoria)
   - Filtra TODO local (sin volver a llamar idCategoria)
   - Marca y Modelo con CHECKBOX
   - Modelos se REDUCEN según marcas seleccionadas
   - Incluye TODAS las funciones auxiliares
   ============================================================ */

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

        // 2) Productos UNA sola vez
        $.post("api/v1/fulmuv/productos/idCategoria", { id_categoria: idsCategorias }, function (returnedData) {
            if (returnedData?.error) return;

            productosDataAll = returnedData.data || [];

            // Index categorías/subcategorías desde productos
            catsIndex = buildCatsAndSubcatsFromProductos(productosDataAll);
            categoriasFiltradas = catsIndex.categoriasLista;
            renderCheckboxCategorias(categoriasFiltradas);

            // Slider max
            const precios = productosDataAll.map(p => Number(p.precio_referencia)).filter(n => Number.isFinite(n) && n > 0);
            const maxPrecio = precios.length ? Math.max(...precios) : 0;
            inicializarSlider(maxPrecio);

            // Marcas/Modelos base (según filtros actuales)
            buildMarcasYModelos(getBaseDatasetForFilters());
            reduceModelosPorMarca();

            // Render inicial
            renderEmpresas(productosDataAll, 1);

        }, 'json');

    }, 'json');
});


/* ====================== EVENTOS: BÚSQUEDA ====================== */
$(".widget_search input").on("input", function () {
    searchText = $(this).val().trim();

    // Recalcular opciones disponibles en sidebar (marca/modelo) según filtros
    buildMarcasYModelos(getBaseDatasetForFilters());
    reduceModelosPorMarca();

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
    const id = Number($(this).val());
    if (!Number.isFinite(id)) return;

    if (this.checked) {
        if (!subcategoriasSeleccionadas.includes(id)) subcategoriasSeleccionadas.push(id);
    } else {
        subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(x => Number(x) !== id);
    }

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
    buildMarcasYModelos(getBaseDatasetForFilters());
    reduceModelosPorMarca();

    renderEmpresas(productosDataAll, 1);
});


/* ====================== EVENTOS: CHECKBOX SUBCATEGORÍAS (LOCAL) ====================== */
$(document).on("change", "input[name='checkbox-sub']", function () {
    const id = Number($(this).val());
    if (!Number.isFinite(id)) return;

    if (this.checked) {
        if (!subcategoriasHijasSeleccionadas.includes(id)) subcategoriasHijasSeleccionadas.push(id);
    } else {
        subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => Number(x) !== id);
    }

    buildMarcasYModelos(getBaseDatasetForFilters());
    reduceModelosPorMarca();

    renderEmpresas(productosDataAll, 1);
});


/* ====================== EVENTOS: CHECKBOX MARCA/MODELO ====================== */
$(document).off("change.chkMarca").on("change.chkMarca", ".chk-marca", function () {
    const id = String($(this).val());
    this.checked ? marcasSeleccionadas.add(id) : marcasSeleccionadas.delete(id);

    // reduce modelos según marcas
    reduceModelosPorMarca();

    renderEmpresas(productosDataAll, 1);
});

$(document).off("change.chkModelo").on("change.chkModelo", ".chk-modelo", function () {
    const id = String($(this).val());
    this.checked ? modelosSeleccionados.add(id) : modelosSeleccionados.delete(id);

    renderEmpresas(productosDataAll, 1);
});


/* ====================== RENDER PRINCIPAL (GRID) ====================== */
function renderEmpresas(dataset, page = 1) {
    const empresasFiltradas = applyAllFilters(dataset);

    // Orden
    if (sortOption === "mayor") empresasFiltradas.sort((a, b) => Number(b.precio_referencia) - Number(a.precio_referencia));
    if (sortOption === "menor") empresasFiltradas.sort((a, b) => Number(a.precio_referencia) - Number(b.precio_referencia));

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
              <a href="detalle_productos.php?q=${productos.id_producto}" target="_blank" rel="noopener noreferrer" onclick="irADetalleProductoConTerminos(${productos.id_producto}); return false;" class="limitar-lineas mt-1">
                ${capitalizarPrimeraLetra(productos.titulo_producto)}
              </a>
            </h2>
            <div class="product-price mb-2 mt-0 text-center">
              <span>${formatoMoneda.format(tieneDescuento ? precioDescuento : precioRef)}</span>
              ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(precioRef)}</span>` : ''}
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
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (productosDataAll || []).filter(prod => {
        if (!primeraCategoriaEsProducto(prod)) return false;
        if (!matchBusquedaProducto(prod, searchText)) return false;

        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        if (!matchCat) return false;

        const matchSub = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);
        if (!matchSub) return false;

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
    let html = '';
    (marcas || []).forEach(m => {
        const id = String(m.id);
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-marca" type="checkbox" id="marca-${id}" value="${id}"
          ${marcasSeleccionadas.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="marca-${id}">
          ${capitalizarPrimeraLetra(m.nombre)}
        </label>
      </div>`;
    });
    $("#filtro-marca").html(html || `<small class="text-muted">No hay marcas.</small>`);
}

function renderCheckboxModelos(modelos) {
    let html = '';
    (modelos || []).forEach(m => {
        const id = String(m.id);
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input chk-modelo" type="checkbox" id="modelo-${id}" value="${id}"
          ${modelosSeleccionados.has(id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="modelo-${id}">
          ${capitalizarPrimeraLetra(m.nombre)}
        </label>
      </div>`;
    });
    $("#filtro-modelo").html(html || `<small class="text-muted">No hay modelos.</small>`);
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
function renderCheckboxCategorias(categorias) {
    let htmlVisible = '';
    let htmlOcultas = '';
    const maxVisible = 10;

    (categorias || []).forEach((cat, index) => {
        const id = Number(cat.id_categoria);
        const checked = subcategoriasSeleccionadas.includes(id);

        const checkboxHTML = `
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="${id}" id="categoria-${id}" name="checkbox" ${checked ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-${id}">
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


/* ====================== SUBCATEGORÍAS UI ====================== */
function renderCheckboxSubcategorias(subcats) {
    if (!Array.isArray(subcats)) subcats = [];

    let htmlVisible = '';
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

        buildMarcasYModelos(getBaseDatasetForFilters());
        reduceModelosPorMarca();

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

    buildMarcasYModelos(getBaseDatasetForFilters());
    reduceModelosPorMarca();

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

    buildMarcasYModelos(getBaseDatasetForFilters());
    reduceModelosPorMarca();

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

    const base = [
        prod.titulo_producto,
        prod.nombre,
        prod.tags
    ].map(normalizarCadena).join(' ');

    const palabras = textoBuscar.split(/\s+/).filter(Boolean);
    const todasPresentes = palabras.every(p => base.includes(p));

    const soloNumerosBusqueda = (searchText || '').replace(/\s+/g, '');
    let idMatch = false;
    if (/^\d+$/.test(soloNumerosBusqueda)) {
        const idStr = String(prod.id_producto || '');
        idMatch = idStr.includes(soloNumerosBusqueda);
    }

    return todasPresentes || idMatch;
}


/* ====================== BUILD CATS / SUBCATS DESDE PRODUCTOS ====================== */
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
    const s = (texto || '').toString();
    if (!s) return '';
    return s.charAt(0).toUpperCase() + s.slice(1);
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
    // Ajusta aquí si necesitas validación de términos.
    window.open(`detalle_productos.php?q=${id_producto}`, '_blank');
}
