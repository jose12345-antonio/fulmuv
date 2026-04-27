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
const tipoFiltro = 'servicio'; // 'producto' | 'servicio' | null (ambos)

let search = $("#search").val()

// Estado seleccionado
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let catsIndex = null;
let datosEcuador = {};

$(document).ready(function () {

    actualizarIconoCarrito();
    $("#breadcrumb").append(`
        <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
        <span></span> Lista de Productos y Servicios
    `)


    getServiciosSearch()
    cargarProductosSearch()
    cargarVehiculosLlegados()



})

function firstFromJsonLike(v) {
    try {
        if (Array.isArray(v)) return v[0] ?? "";
        if (typeof v === "string") {
            const arr = JSON.parse(v);
            return Array.isArray(arr) ? (arr[0] ?? "") : "";
        }
    } catch (e) { }
    return "";
}

function formatKms(km) {
    const n = parseInt(km, 10);
    if (isNaN(n)) return "";
    return n.toLocaleString("es-EC") + " Kms";
}

function getServiciosSearch() {
    $.post("api/v1/fulmuv/serviciosProductosSearch/All", { search: search }, function (returnedData) {
        if (!returnedData.error) {
            let $slider = $('#carausel-4-columns-servicio');

            // 1. Resetear el slider si ya está inicializado
            if ($slider.hasClass('slick-initialized')) {
                $slider.slick('unslick');
            }

            // 2. Limpiar contenido
            $slider.empty();

            var listaServicio = "";
            returnedData.data.forEach(function (data) {
                if (data.categorias[0].tipo == "servicio") {
                    const tieneDescuento = parseFloat(data.descuento) > 0;
                    const precioDescuento = data.precio_referencia - (data.precio_referencia * data.descuento / 100);

                    $slider.append(`
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap">
                                <div class="product-img product-img-zoom">
                                    <a href="detalle_productos.php?q=${data.id_producto}">
                                        <img class="default-img" src="admin/${data.img_frontal}" alt="" 
                                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" 
                                            style="height: 150px; object-fit: contain;" />
                                    </a>
                                </div>

                                ${tieneDescuento ? `
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="best">-${parseInt(data.descuento)}%</span>
                                    </div>` : ''}

                                <!-- Botón flotante arriba derecha -->
                                <div class="position-absolute top-0 end-0 m-2 d-none">
                                    <button class="btn rounded-circle d-flex justify-content-center align-items-center p-0"
                                        style="width: 40px; height: 40px;"
                                        onclick="agregarProductoCarrito(${data.id_producto}, '${data.titulo_producto}', '${data.precio_referencia}', '${data.img_frontal}')">
                                        <img alt="Carrito de compra" src="img/carrito_transparente.png"/>
                                    </button>
                                </div>
                            </div>

                            <div class="product-content-wrap p-2">
                                <h2 class="text-center">
                                    <a href="detalle_productos.php?q=${data.id_producto}" class="limitar-lineas mt-3">
                                        ${capitalizarPrimeraLetra(data.titulo_producto)}
                                    </a>
                                </h2>
                                <div class="product-price mb-2 mt-0 text-center">
                                    <span>
                                        ${formatoMoneda.format(tieneDescuento ? precioDescuento : data.precio_referencia)}
                                    </span>
                                    ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `);
                }
            });

            $("#listaServiciosPopulares").append(listaServicio)

            // 4. Volver a inicializar slick
            $slider.slick({
                dots: false,
                infinite: true,
                speed: 1000,
                arrows: true,
                autoplay: true,
                slidesToShow: 5,
                slidesToScroll: 1,
                loop: true,
                adaptiveHeight: true,
                responsive: [{
                    breakpoint: 1025,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
                ],
                prevArrow: '<span class="slider-btn slider-prev"><i class="fi-rs-arrow-small-left"></i></span>',
                nextArrow: '<span class="slider-btn slider-next"><i class="fi-rs-arrow-small-right"></i></span>',
                appendArrows: "#carausel-4-columns-arrows"
            });
        }
    }, 'json');
}

function cargarProductosSearch() {
    $.post("api/v1/fulmuv/ProductosSearch/All", { search: search }, function (returnedData) {
        if (!returnedData.error) {
            let $slider = $('#carausel-4-columns-oferta');

            // 1. Resetear el slider si ya está inicializado
            if ($slider.hasClass('slick-initialized')) {
                $slider.slick('unslick');
            }

            // 2. Limpiar contenido
            $slider.empty();

            var listaProducto = "";
            let contador = 0;

            // 3. Insertar productos correctamente envueltos
            returnedData.data.forEach(function (data, index) {
                console.log(data)
                console.log(data.categorias[0])
                const tieneDescuento = parseFloat(data.descuento) > 0;
                const precioDescuento = data.precio_referencia - (data.precio_referencia * data.descuento / 100);

                $slider.append(`
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap">
                                <div class="product-img product-img-zoom">
                                    <a href="detalle_productos.php?q=${data.id_producto}">
                                        <img class="default-img" src="admin/${data.img_frontal}" alt="" 
                                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" 
                                            style="height: 150px; object-fit: contain;" />
                                    </a>
                                </div>

                                ${tieneDescuento ? `
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="best">-${parseInt(data.descuento)}%</span>
                                    </div>` : ''}

                                <!-- Botón flotante arriba derecha -->
                                <div class="position-absolute top-0 end-0 m-2 d-none">
                                    <button class="btn rounded-circle d-flex justify-content-center align-items-center p-0"
                                        style="width: 40px; height: 40px;"
                                        onclick="agregarProductoCarrito(${data.id_producto}, '${data.titulo_producto}', '${data.precio_referencia}', '${data.img_frontal}')">
                                        <img alt="Carrito de compra" src="img/carrito_transparente.png"/>
                                    </button>
                                </div>
                            </div>

                            <div class="product-content-wrap p-2">
                                <h2 class="text-center">
                                    <a href="detalle_productos.php?q=${data.id_producto}" class="limitar-lineas mt-3">
                                        ${capitalizarPrimeraLetra(data.titulo_producto)}
                                    </a>
                                </h2>
                                <div class="product-price mb-2 mt-0 text-center">
                                    <span>
                                        ${formatoMoneda.format(tieneDescuento ? precioDescuento : data.precio_referencia)}
                                    </span>
                                    ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `);
            });

            // 4. Volver a inicializar slick
            $slider.slick({
                dots: false,
                infinite: true,
                speed: 1000,
                arrows: true,
                autoplay: true,
                slidesToShow: 6,
                slidesToScroll: 1,
                loop: true,
                adaptiveHeight: true,
                responsive: [{
                    breakpoint: 1025,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
                ],
                prevArrow: '<span class="slider-btn slider-prev"><i class="fi-rs-arrow-small-left"></i></span>',
                nextArrow: '<span class="slider-btn slider-next"><i class="fi-rs-arrow-small-right"></i></span>',
                appendArrows: "#carausel-4-columns-arrows-oferta"
            });

        }
    }, 'json');

}

function renderEmpresas(data, page = 1) {
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    let empresasFiltradas = data.filter(prod => {
        const prodNombre = (prod.nombre || '').toLowerCase();
        const matchSearch = prodNombre.includes(searchText.toLowerCase()) ||
            String(prod.id_producto).includes(searchText);

        // --- Categorías / Subcategorías ---
        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);



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

    empresasPagina.forEach(function (productos) {

        const tieneDescuento = parseFloat(productos.descuento) > 0;
        const precioDescuento = productos.precio_referencia - (productos.precio_referencia * productos.descuento / 100);

        listProductos += `
            <div class="col-md-4 col-lg-3 col-sm-4 col-6 mb-4 d-flex">
                <div class="product-cart-wrap w-100 d-flex flex-column">
                    <div class="product-img-action-wrap text-center">
                        <div class="product-img product-img-zoom">
                            <a href="detalle_productos.php?q=${productos.id_producto}">
                                <img class="default-img img-fluid mb-1"
                                    src="admin/${productos.img_frontal}" 
                                    onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" style="object-fit: contain; width: 100%; height: 200px">
                                <img class="hover-img img-fluid"
                                    src="admin/${productos.img_posterior}" 
                                    onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"  style="object-fit: contain; width: 100%; height: 200px">
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
                                <a href="detalle_productos.php?q=${productos.id_producto}">${productos.titulo_producto}</a>
                            </h6>
                        </div>

                        <!-- Precios al fondo -->
                        <div class="mt-auto">
                            <div class="product-price text-center">
                                <span>
                                    ${formatoMoneda.format(tieneDescuento ? precioDescuento : productos.precio_referencia)}
                                </span>
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
function normalizarTexto(s) {
    return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();
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

function cargarVehiculosLlegados() {
    $.get("api/v1/fulmuv/vehiculosLlegados/All", function (returnedData) {
        if (!returnedData.error) {
            let $slider = $('#carausel-4-columns-vehiculos');

            // 1. Resetear el slider si ya está inicializado
            if ($slider.hasClass('slick-initialized')) {
                $slider.slick('unslick');
            }

            // 2. Limpiar contenido
            $slider.empty();

            returnedData.data.forEach(function (data) {
                // Campos según tu payload (2da y 3ra imagen)
                const marca = (data?.marcaArray[0]?.nombre) || "";
                const modelo = (data?.modeloArray?.nombre) || data?.titulo_producto || "";
                const anio = data?.anio ?? "";
                const prov = firstFromJsonLike(data?.provincia);
                const canton = firstFromJsonLike(data?.canton);
                const kms = formatKms(data?.kilometraje);

                const precioRef = parseFloat(data?.precio_referencia || 0);
                const desc = parseFloat(data?.descuento || 0);
                const tieneDesc = !isNaN(precioRef) && !isNaN(desc) && desc > 0;
                const precioConDesc = tieneDesc ? (precioRef - (precioRef * desc / 100)) : precioRef;

                // Imagen
                const img = data?.img_frontal ? `admin/${data.img_frontal}` : 'img/FULMUV-NEGRO.png';

                // Render card estilo de la imagen de referencia
                $slider.append(`
                       <div class="col-md-4 col-lg-4 col-sm-4 col-6 mb-4 d-flex">
                            <div class="product-cart-wrap w-100 d-flex flex-column">
                                <div class="product-img-action-wrap text-center">
                                    <div class="product-img product-img-zoom">
                                        <a href="detalle_vehiculo.php?q=${data.id_vehiculo}">
                                            <img class="default-img img-fluid mb-1"
                                                src="admin/${data.img_frontal}"  alt="${modelo}"
                                                onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" style="object-fit: contain; width: 100%; height: 200px">
                                            <img class="hover-img img-fluid"
                                                src="admin/${data.img_posterior}" alt="${modelo}" 
                                                onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"  style="object-fit: contain; width: 100%; height: 200px">
                                        </a>
                                    </div>
                                    ${tieneDesc ? `
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="best">-${parseInt(data.descuento)}%</span>
                                    </div>` : ''}
                                </div>
                                <div class="product-content-wrap d-flex flex-column flex-grow-1 px-2 pb-2">
                                      <div class="brand">${marca || '&nbsp;'}</div>
                                        <a href="detalle_vehiculo.php?q=${data.id_vehiculo}"><h3 class="model">${modelo || '&nbsp;'}</h3></a>
                                    <div class="year">${anio || '&nbsp;'}</div>
                                    <hr class="my-1">
                                    <div class="meta-line">
                                        <span><i class="fi-rs-marker"></i> ${prov || '—'}</span>
                                    </div>
                                    <div class="badge-type">
                                        <span class="meta-dot"></span>
                                        <i class="fi-rs-dashboard"></i> ${kms}
                                    </div>
                                    <div class="mt-auto">
                                        <div class="product-price text-center">
                                            <span>
                                                ${formatoMoneda.format(tieneDesc ? precioConDesc : data.precio_referencia)}
                                            </span>
                                            ${tieneDesc ? `<span class="old-price">${formatoMoneda.format(data.precio_referencia)}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
            });
            // $("#listaServiciosPopulares").append(listaServicio)

            // 4. Volver a inicializar slick
            $slider.slick({
                dots: false,
                infinite: true,
                speed: 1000,
                arrows: true,
                autoplay: true,
                slidesToShow: 5,
                slidesToScroll: 1,
                loop: true,
                adaptiveHeight: true,
                responsive: [{
                    breakpoint: 1025,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
                ],
                prevArrow: '<span class="slider-btn slider-prev"><i class="fi-rs-arrow-small-left"></i></span>',
                nextArrow: '<span class="slider-btn slider-next"><i class="fi-rs-arrow-small-right"></i></span>',
                appendArrows: "#carausel-4-columns-arrows-para-ti"
            });
        }
    }, 'json');
}
