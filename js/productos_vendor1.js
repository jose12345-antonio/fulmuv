let itemsPerPage = 20;
let currentPage = 1;
let productosData = [];

let sortOption = "todos"; // opciones: "mayor", "menor", "todos"
let searchText = "";
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
const tipoFiltro = null; // 'producto' | 'servicio' | null (ambos)
let id_empresa = $("#id_empresa").val();


// Estado seleccionado
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let catsIndex = null;
let datosEcuador = {};


fetch('provincia_canton_parroquia.json')
    .then(res => res.json())
    .then(data => {
        datosEcuador = data;
        cargarProvincias();
    });

// helper para evitar undefined
function safe(text) {
    return (text || '').toString();
}


$(document).ready(function () {

    actualizarIconoCarrito();
    $("#breadcrumb").append(`
        <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
        <span></span> Lista de Servicios
    `)

    $.get("api/v1/fulmuv/empresas/" + id_empresa, function (returnedData) {

        if (!returnedData.error) {

            $("#imagenEmpresa")
                .attr("src", "admin/" + returnedData.data.img_path)
                .attr("onerror", "this.onerror=null;this.src='img/FULMUV-NEGRO.png';");

            // Construye el html del check si aplica 
            let verificacion = "";
            if (returnedData.data.verificacion?.length && returnedData.data.verificacion[0].verificado == 1) {
                verificacion = `<img src="img/verificado_empresa.png" alt="Verificado" title="Empresa verificada" style="width:36px;height:36px;">`;
            }
            // Coloca el nombre y, si existe, agrega el ícono a la derecha 
            const $nombre = $("#nombreEmpresa");
            $nombre.text(capitalizarPrimeraLetra(returnedData.data.nombre));
            // hace que queden en la misma línea y centrados verticalmente 
            $nombre.parent().addClass("d-flex align-items-center justify-content-between");
            if (verificacion) {
                $nombre.after(verificacion);
            }
            $("#direccionEmpresa").text(capitalizarPrimeraLetra(returnedData.data.direccion))
            $("#telefonoEmpresa").text(returnedData.data.telefono_contacto)
            $("#fechaInicioEmpresa").text(calcularTiempoTexto(returnedData.data.tiempo_anos, returnedData.data.tiempo_meses) || "Sin fecha")

            const emp = returnedData.data || {};

            const telRaw = (emp.telefono_contacto || "").toString().trim();
            const waRaw = (emp.whatsapp_contacto || emp.telefono_contacto || "").toString().trim();

            // dígitos para links
            const telDigits = telRaw.replace(/\D/g, "");

            // normalizar whatsapp Ecuador
            let waDigits = waRaw.replace(/\D/g, "");
            if (waDigits.length === 10 && waDigits.startsWith("0")) waDigits = "593" + waDigits.slice(1);
            if (waDigits.startsWith("5930")) waDigits = "593" + waDigits.slice(4);

            // mensaje al whatsapp
            const waText = encodeURIComponent(`Hola, me gustaría comunicarme con la empresa "${emp.nombre || "tu tienda"}"`);
            const waUrl = waDigits ? `https://wa.me/${waDigits}?text=${waText}` : "#";

            // botón principal (sidebar)
            $("#btnWhatsSidebar").attr({
                href: waUrl,
                target: "_blank",
                rel: "noopener"
            });

            // debajo del botón: teléfonos
            $("#contactosSidebar").html(`
                <div class="contact-line">
                    <span class="icon-circle icon-tel">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.07 21 3 13.93 3 5a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.46.57 3.58a1 1 0 0 1-.24 1.01l-2.21 2.2Z"/>
                    </svg>
                    </span>
                    <span>Teléfono:</span>
                    ${telDigits
                                    ? `<a href="tel:${telDigits}">${telRaw}</a>`
                                    : `<span class="text-muted">No disponible</span>`
                                }
                </div>

                <div class="contact-line">
                    <span class="icon-circle icon-wa">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 32 32">
                        <path fill="currentColor" d="M19.11 17.05c-.27-.14-1.61-.79-1.86-.88c-.25-.09-.43-.14-.61.14c-.18.27-.7.88-.86 1.06c-.16.18-.32.2-.59.07c-.27-.14-1.15-.42-2.19-1.34c-.81-.72-1.36-1.6-1.52-1.87c-.16-.27-.02-.41.12-.55c.12-.12.27-.32.41-.48c.14-.16.18-.27.27-.45c.09-.18.05-.34-.02-.48c-.07-.14-.61-1.47-.83-2.01c-.22-.53-.44-.46-.61-.46h-.52c-.18 0-.48.07-.73.34c-.25.27-.96.94-.96 2.3c0 1.36.98 2.67 1.11 2.85c.14.18 1.93 2.95 4.68 4.14c.65.28 1.16.45 1.55.57c.65.21 1.24.18 1.71.11c.52-.08 1.61-.66 1.84-1.3c.23-.64.23-1.19.16-1.3c-.07-.11-.25-.18-.52-.32zM16 3C8.83 3 3 8.83 3 16c0 2.29.62 4.44 1.7 6.28L3 29l6.9-1.81A12.93 12.93 0 0 0 16 29c7.17 0 13-5.83 13-13S23.17 3 16 3z"/>
                    </svg>
                    </span>
                    <span>WhatsApp:</span>
                    ${waDigits
                                    ? `<a href="${waUrl}" target="_blank" rel="noopener">${waRaw}</a>`
                                    : `<span class="text-muted">No disponible</span>`
                                }
                </div>
                `);

            const archivos = returnedData.data.archivos || [];

            if (archivos.length === 0) {
                $("#gallery-wrapper").hide();
            } else {
                $("#gallery-wrapper").show();

                archivos.forEach(function (data) {
                    const titulo = data.titulo || '';
                    const descripcion = data.descripcion || '';

                    $("#gallery-empresa").append(`
                        <div class="gallery-item">
                            <img src="admin/${data.archivo}" alt="${titulo}">
                            <div class="gallery-info">
                                <h4>${titulo}</h4>
                                <p>${descripcion}</p>
                            </div>
                        </div>
                    `);
                });
            }
        }

    }, 'json');

    $.post("api/v1/fulmuv/productos/idEmpresa", { id_empresa: id_empresa }, function (returnedData) {
        if (!returnedData.error) {
            productosData = returnedData.data;
            renderEmpresas(productosData, currentPage);
            console.log(returnedData.data)
            console.log(productosData)
            // sliders, marcas, modelos
            const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
            inicializarSlider(maxPrecio);
            buildMarcasYModelos(productosData);



            // ✅ Construir categorías y subcategorías ÚNICAS según productos
            catsIndex = buildCatsAndSubcatsFromProductos(productosData);
            categoriasFiltradas = catsIndex.categoriasLista;   // array: [{id_categoria, nombre}]
            renderCheckboxCategorias(categoriasFiltradas);      // pinta el panel de categorías

        }
    }, 'json');




})

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
        $.post("api/v1/fulmuv/productos/idEmpresa", { id_empresa: id_empresa }, function (returnedData) {
            if (!returnedData.error) {
                productosData = returnedData.data;
                const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
                inicializarSlider(maxPrecio);


                // limpiar subcategorías
                subcategoriasHijas = [];
                subcategoriasHijasSeleccionadas = [];
                $("#filtro-sub-categorias").html("");
                $("#subcats-box").hide(); // 👈 aquí lo ocultas


                renderEmpresas(productosData, 1);
            }
        }, 'json');
        return; // <- importante, no sigas pidiendo subcategorías
    }

    // 1) Productos por categorías seleccionadas (únicas)
    $.post("api/v1/fulmuv/productos/idEmpresa", { id_empresa: id_empresa }, function (returnedData) {
        if (!returnedData.error) {
            productosData = returnedData.data;
            const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
            inicializarSlider(maxPrecio);
            renderEmpresas(productosData, 1);
        }
    }, 'json');

    // 2) Subcategorías por categorías seleccionadas (únicas) – local, sin API
    const subcats = buildSubcatsForSelected(idsUnicos);
    subcategoriasHijas = subcats;
    subcategoriasHijasSeleccionadas = [];
    renderCheckboxSubcategorias(subcats);
    $("#subcats-box").toggle(subcats.length > 0);

});


function fetchSubcategorias(idsCategorias) {
    $.post("api/v1/fulmuv/subcategorias/byCategorias", { id_categoria: idsCategorias }, function (returned) {
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

    if (subcats.length === 0) {
        $("#filtro-sub-categorias").empty();
        $("#subcats-box").hide();
        return;
    }
    $("#subcats-box").show();

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
            // solo abre/cierra su bloque oculto hermano
            $(this).prev(".more_slide_open-sub").slideToggle();
        });
}


function buildMarcasYModelos(data) {
    const marcasMap = new Map();  // id -> {id, nombre}
    const modelosMap = new Map(); // id -> {id, nombre}

    (data || []).forEach(p => {
        // --- MARCAS ---
        const marcaIds = parseIdsArray(p.id_marca); // e.g. ["193","195"]
        // si el backend envía nombres: p.marca = [{id, nombre}, ...]
        const marcaObjs = Array.isArray(p.marca) ? p.marca : [];

        marcaIds.forEach(id => {
            if (!marcasMap.has(id)) {
                // busca nombre por id si vino en p.marca, si no, usa "Marca {id}"
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
    if (!marcas || marcas.length === 0) {
        $("#filtro-marca").empty();
        toggleFilterBlock('#filtro-marca', false);
        return;
    }
    toggleFilterBlock('#filtro-marca', true);

    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input" type="radio" name="radio-marca" id="marca-all" value="" ${marcaSeleccionada === null ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="marca-all">Todos</label>
    </div>`;
    marcas.forEach(m => {
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="radio-marca" id="marca-${m.id}" value="${m.id}" ${Number(marcaSeleccionada) === Number(m.id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="marca-${m.id}">${capitalizarPrimeraLetra(m.nombre)}</label>
      </div>`;
    });
    $("#filtro-marca").html(html);

    $(document).off("change", "input[name='radio-marca']")
        .on("change", "input[name='radio-marca']", function () {
            const v = $(this).val();
            marcaSeleccionada = v === "" ? null : Number(v);
            renderEmpresas(productosData, 1);
        });
}

function renderRadioModelos(modelos) {
    if (!modelos || modelos.length === 0) {
        $("#filtro-modelo").empty();
        toggleFilterBlock('#filtro-modelo', false);
        return;
    }
    toggleFilterBlock('#filtro-modelo', true);

    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input" type="radio" name="radio-modelo" id="modelo-all" value="" ${modeloSeleccionado === null ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
    </div>`;
    modelos.forEach(mo => {
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="radio-modelo" id="modelo-${mo.id}" value="${mo.id}" ${Number(modeloSeleccionado) === Number(mo.id) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="modelo-${mo.id}">${capitalizarPrimeraLetra(mo.nombre)}</label>
      </div>`;
    });
    $("#filtro-modelo").html(html);

    $(document).off("change", "input[name='radio-modelo']")
        .on("change", "input[name='radio-modelo']", function () {
            const v = $(this).val();
            modeloSeleccionado = v === "" ? null : Number(v);
            renderEmpresas(productosData, 1);
        });
}


// Selección/deselección de subcategorías
$(document).on("change", "input[name='checkbox-sub']", function () {
    const id = Number($(this).val()); // <-- número
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
        const prodNombre = (prod.nombre || '').toLowerCase();
        const matchSearch = prodNombre.includes(searchText.toLowerCase()) ||
            String(prod.id_producto).includes(searchText);

        // --- Primera categoría debe ser tipo 'producto' (red de seguridad) ---
        const matchTipo = categoriaInicialCumpleTipo(prod);
        // --- Categorías / Subcategorías ---
        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);

        const matchCat = (selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet);
        const matchSubHija = (selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet);

        // --- Marca/Modelo ---
        const matchMarca = (marcaSeleccionada === null) || prodBrand.includes(Number(marcaSeleccionada));
        const matchModelo = (modeloSeleccionado === null) || prodModel.includes(Number(modeloSeleccionado));

        // --- Precio ---
        const precio = Number(prod.precio_referencia);
        const matchPrecio = precio >= precioMin && precio <= precioMax;

        // --- Ubicación (Provincia/Cantón) ---
        // prod.provincia y prod.canton vienen en los datos (ver screenshot)

        const selProv = normalizarTexto(provinciaSel.nombre);
        const selCant = normalizarTexto(cantonSel.nombre);
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);

        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);

        return matchSearch
            && matchTipo
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

    console.log("BUSQUEDA SD")
    console.log(empresasPagina)
    empresasPagina.forEach(function (productos) {
        const tieneDescuento = parseFloat(productos.descuento) > 0;
        const precioDescuento = productos.precio_referencia - (productos.precio_referencia * productos.descuento / 100);

        const detalleUrl = getDetalleUrl(productos);
        const tituloCard = getTituloProductoOServicio(productos);

        listProductos += `
        <div class="col-md-4 col-lg-3 col-sm-4 col-6 mb-4 d-flex">
            <div class="product-cart-wrap w-100 d-flex flex-column">
            <div class="product-img-action-wrap text-center">
                <div class="product-img product-img-zoom">
                <a href="${detalleUrl}">
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

            <div class="product-content-wrap d-flex flex-column flex-grow-1 text-center px-2 pb-2">
                <div>
                <h6 class="limitar-lineas mb-2 mt-2">
                    <a href="${detalleUrl}">${tituloCard}</a>
                </h6>
                </div>

                <div class="mt-auto">
                <div class="product-price text-center">
                    <span>${formatoMoneda.format(tieneDescuento ? precioDescuento : productos.precio_referencia)}</span>
                    ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(productos.precio_referencia)}</span>` : ''}
                </div>
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
    if (!categorias || categorias.length === 0) {
        $("#filtro-categorias").empty();
        toggleFilterBlock('#filtro-categorias', false);
        return;
    }
    toggleFilterBlock('#filtro-categorias', true);

    let htmlVisible = '';
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
        if (index < maxVisible) htmlVisible += checkboxHTML; else htmlOcultas += checkboxHTML;
    });

    const htmlFinal = `
    <div class="checkbox-list-visible">${htmlVisible}</div>
    <div class="more_slide_open-cat" style="display:none;">${htmlOcultas}</div>
    ${htmlOcultas ? `
      <div class="more_categories-cat cursor-pointer">
        <span class="icon">+</span> 
        <span class="heading-sm-1">Show more...</span>
      </div>` : ''}`;

    $("#filtro-categorias").html(htmlFinal);

    $(document).off("click.moreCat", "#filtro-categorias .more_categories-cat")
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


// Búsqueda por nombre o ID
$(".widget_search input").on("input", function () {
    searchText = $(this).val().trim();
    renderEmpresas(productosData, 1); // usa productosData, no empresasData
});

$(".sort-show li a").on("click", function (e) {
    e.preventDefault();

    $(".sort-show li a").removeClass("active");
    $(this).addClass("active");

    const value = $(this).data("value");
    itemsPerPage = value === "all" ? productosData.length : parseInt(value);

    // Actualiza texto de dropdown
    $(".sort-by-product-wrap:first .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    currentPage = 1;
    renderEmpresas(productosData, currentPage);
});

$(".sort-order li a").on("click", function (e) {
    e.preventDefault();

    $(".sort-order li a").removeClass("active");
    $(this).addClass("active");

    sortOption = $(this).data("value");

    // Actualiza texto del dropdown
    $(".sort-by-product-wrap:last .sort-by-dropdown-wrap span").html(`${$(this).text()} <i class="fi-rs-angle-small-down"></i>`);

    renderEmpresas(productosData, 1);
});

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
            // Verifica si el carrito aún está dentro del tiempo válido (2 horas = 7200000 ms)
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

    // Guardar con timestamp actual
    localStorage.setItem("carrito", JSON.stringify({
        data: carrito,
        timestamp: new Date().getTime()
    }));

    actualizarIconoCarrito();
}


function inicializarSlider(maxPrecio) {
    const sliderElement = document.getElementById("slider-range");
    if (!sliderElement) return;

    // Destruye instancia previa si existe
    if (sliderElement.noUiSlider) {
        try { sliderElement.noUiSlider.destroy(); } catch (_) { }
    }

    // ⚠️ Guard: si no hay rango válido, NO crees el slider
    if (!Number.isFinite(maxPrecio) || maxPrecio <= 0) {
        // Limpia el contenedor para que no queden handles viejos
        sliderElement.innerHTML = "";

        // Muestra 0–0 y desactiva el filtro de precio
        if (moneyFormat?.to) {
            $("#slider-range-value1").text(moneyFormat.to(0));
            $("#slider-range-value2").text(moneyFormat.to(0));
        } else {
            $("#slider-range-value1").text("$0");
            $("#slider-range-value2").text("$0");
        }

        // El filtro de precio no limita resultados
        precioMin = 0;
        precioMax = Infinity;

        // (Opcional) ocultar el widget completo cuando no hay rango
        // $("#slider-range").closest(".price_range").hide();

        return; // <-- importante
    }

    // (Opcional) mostrar el widget si estaba oculto
    // $("#slider-range").closest(".price_range").show();

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
    });
}


// --- REEMPLAZA tu función cargarProvincias por esta ---
function cargarProvincias() {
    const selectProvincia = document.getElementById("selectProvincia");
    selectProvincia.innerHTML = '<option value="">Seleccione una provincia</option>';

    Object.entries(datosEcuador).forEach(([codProv, objProv]) => {
        const option = document.createElement("option");
        option.value = codProv;
        option.textContent = capitalizarPrimeraLetra(objProv.provincia);
        selectProvincia.appendChild(option);
    });

    // Al cambiar provincia:
    selectProvincia.addEventListener("change", (e) => {
        const codProv = e.target.value || null;

        if (!codProv) {
            provinciaSel = { id: null, nombre: null };
            resetSelectCanton();              // limpia cantón
            actualizarUIUbicacionPersistir(); // actualiza botón/inputs
            refreshFiltersForCurrentLocation();
            renderEmpresas(productosData, 1);
            return;
        }

        provinciaSel.id = codProv;
        provinciaSel.nombre = capitalizarPrimeraLetra(datosEcuador[codProv].provincia);

        resetSelectCanton();                 // limpia cantón al elegir nueva provincia
        cargarCantones(codProv);             // repuebla cantones para esa provincia
        actualizarUIUbicacionPersistir();    // actualiza botón/inputs
        refreshFiltersForCurrentLocation();  // reconstruye slider, marcas, modelos, cats/subcats
        renderEmpresas(productosData, 1);    // re-render de la grilla
    });
}

// --- REEMPLAZA tu función cargarCantones por esta ---
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
            actualizarUIUbicacionPersistir();
            refreshFiltersForCurrentLocation();
            renderEmpresas(productosData, 1);
            return;
        }

        const nombre = capitalizarPrimeraLetra(
            (datosEcuador[codProvincia].cantones[codCanton] || {}).canton || ""
        );
        cantonSel.id = codCanton;
        cantonSel.nombre = nombre;

        actualizarUIUbicacionPersistir();
        refreshFiltersForCurrentLocation();
        renderEmpresas(productosData, 1);
    });
}


// helper para limpiar cantón si se cambia/limpia provincia
function resetSelectCanton() {
    const selectCanton = document.getElementById("selectCanton");
    selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
    cantonSel = { id: null, nombre: null };
}

// Construir el texto para el botón
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

function categoriaInicialCumpleTipo(prod) {
    if (tipoFiltro == null) return true; // no filtrar por tipo

    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    if (!ids.length) return true; // no bloquees si no hay ids
    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return first ? (first.tipo === tipoFiltro) : true; // si no se encuentra, no bloquees
}

document.getElementById('guardarUbicacion').addEventListener('click', function () {
    actualizarUIUbicacionPersistir();
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);

    const modalEl = document.getElementById('modalUbicacion');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
});

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

// true si hay intersección entre array de ids y Set de ids
function hasIntersection(idsArr, idsSet) {
    for (const id of idsArr) if (idsSet.has(Number(id))) return true;
    return false;
}

function buildCatsAndSubcatsFromProductos(productos) {
    const catsMap = new Map();               // catId -> {id_categoria, nombre}
    const subsPorCatMap = new Map();         // catId -> Map(subId -> {id_sub_categoria, nombre})

    (productos || []).forEach(p => {
        // Preferimos arrays de objetos si vienen (con nombres); si no, usamos los IDs crudos
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

        // Por cada categoría del producto
        catIds.forEach(cid => {
            if (!cid) return;
            const nombreCat = (catObjs.find(o => Number(o.id) === cid)?.nombre) || `Categoría ${cid}`;
            if (!catsMap.has(cid)) catsMap.set(cid, { id_categoria: cid, nombre: capitalizarPrimeraLetra(nombreCat) });

            if (!subsPorCatMap.has(cid)) subsPorCatMap.set(cid, new Map());
            const subMap = subsPorCatMap.get(cid);

            // Asignar TODAS las subcats del producto a cada cat del producto (asociación flexible)
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

    // Map(catId -> array ordenada)
    const subsPorCat = new Map(
        Array.from(subsPorCatMap.entries()).map(([cid, m]) => [
            Number(cid),
            Array.from(m.values()).sort((a, b) => a.nombre.localeCompare(b.nombre))
        ])
    );

    return { categoriasLista, subsPorCat };
}

// A partir de categorías seleccionadas, devuelve subcategorías únicas
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
    // Actualiza botón
    const btn = document.getElementById('btnUbicacion');
    if (btn) btn.innerHTML = `<i class="fi-rs-marker me-1"></i> ${labelUbicacion()}`;

    // Persiste en inputs ocultos (si existen)
    const provIdH = document.getElementById('provincia_id_hidden');
    const provNomH = document.getElementById('provincia_nombre_hidden');
    const cantIdH = document.getElementById('canton_id_hidden');
    const cantNomH = document.getElementById('canton_nombre_hidden');

    if (provIdH) provIdH.value = provinciaSel.id || '';
    if (provNomH) provNomH.value = provinciaSel.nombre || '';
    if (cantIdH) cantIdH.value = cantonSel.id || '';
    if (cantNomH) cantNomH.value = cantonSel.nombre || '';

    // Guarda en localStorage
    localStorage.setItem('ubicacionSeleccionada', JSON.stringify({
        provincia: provinciaSel,
        canton: cantonSel
    }));
}

// dataset → subset solo por ubicación (y primera categoría segura)
function filtrarPorUbicacionDataset(dataset) {
    const selProv = normalizarTexto(provinciaSel.nombre);
    const selCant = normalizarTexto(cantonSel.nombre);

    return (dataset || []).filter(prod => {
        const pProv = normalizarTexto(prod.provincia);
        const pCant = normalizarTexto(prod.canton);
        const matchProvincia = !selProv || (pProv && pProv === selProv);
        const matchCanton = !selCant || (pCant && pCant === selCant);
        return categoriaInicialCumpleTipo(prod) && matchProvincia && matchCanton;
    });
}

// Reaplica checks “categorías” tras re-render
function recheckCategoriasSeleccionadas() {
    const setSel = new Set(subcategoriasSeleccionadas.map(Number)); // (son categorías)
    setSel.forEach(id => {
        const el = document.getElementById(`categoria-${id}`);
        if (el) el.checked = true;
    });
}

// Reaplica checks “subcategorías” tras re-render
function recheckSubcategoriasSeleccionadas() {
    const setSel = new Set(subcategoriasHijasSeleccionadas.map(Number));
    setSel.forEach(id => {
        const el = document.getElementById(`subcat-${id}`);
        if (el) el.checked = true;
    });
}

// Si la marca/modelo seleccionados ya no existen en el dataset filtrado, resetea a “Todos”
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

// 🔁 Punto central: reconstruye TODOS los filtros con base SOLO en la ubicación actual
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
    subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(id => subDisponibles.has(Number(id)));

    renderCheckboxSubcategorias(subcats);
    $("#subcats-box").toggle(subcats.length > 0);
    recheckSubcategoriasSeleccionadas();
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

function toggleFilterBlock(contentSelector, hasItems) {
    const $wrap = $(contentSelector).closest('.categories-dropdown-wrap');
    if ($wrap.length) $wrap.toggle(!!hasItems);
}

function tipoPrimeraCategoria(prod) {
    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    if (!ids.length) return null;
    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return first?.tipo || null; // 'producto' | 'servicio' | null
}

function getDetalleUrl(prod) {
    const tipo = tipoPrimeraCategoria(prod);
    const base = (tipo === 'servicio') ? 'detalle_productos.php' : 'detalle_productos.php';
    return `${base}?q=${prod.id_producto}`;
}

function getTituloProductoOServicio(prod) {
    // Si es servicio suele venir como titulo_producto; si es producto como nombre
    return prod.nombre || prod.titulo_producto || 'Sin título';
}
