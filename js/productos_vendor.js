let itemsPerPage = 20;
let currentPage = 1;
let productosData = [];

function shuffleArray(arr) {
    const a = arr.slice();
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

let sortOption = "todos"; // opciones: "mayor", "menor", "todos"
let searchText = "";
let subcategoriasSeleccionadas = [];
let subcategoriasHijas = [];              // listado devuelto por la API
let subcategoriasHijasSeleccionadas = []; // checks seleccionados
let marcasUnicas = [];
let modelosUnicos = [];
let marcasSeleccionadas = [];
let modelosSeleccionados = [];
let filtroTextoMarca = "";
let filtroTextoModelo = "";
let filtroTextoCategoria = "";
let filtroTextoSubcategoria = "";
let precioMin = 0;
let precioMax = Infinity;
let categoriasFiltradas = [];
let rangeSlider;
let condicionSeleccionadas = [];
let tipoAutoSeleccionados = [];
let colorSeleccionados = [];
let tapiceriaSeleccionados = [];
let climatizacionSeleccionadas = [];
let referenciasSeleccionadas = [];
let nombresServiciosSeleccionados = [];
let anioMinSel = null;
let anioMaxSel = null;
let moneyFormat = wNumb({
    decimals: 0,
    thousand: ",",
    prefix: "$"
});
let tipoFiltro = "producto";
let id_empresa = $("#id_empresa").val();


// Estado seleccionado
let provinciaSel = { id: null, nombre: null };
let cantonSel = { id: null, nombre: null };
let catsIndex = null;
let datosEcuador = {};

function parseMysqlDatetime(dtStr) {
    if (!dtStr) return null;
    return new Date(String(dtStr).replace(" ", "T"));
}

function isMembresiaActiva(item) {
    const memb = item?.membresia;
    if (!memb) return false;
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

function escapeHtml(text) {
    return safe(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function normalizarUrlRed(url) {
    const raw = safe(url).trim();
    if (!raw) return "";
    if (/^https?:\/\//i.test(raw)) return raw;
    return `https://${raw}`;
}

function getEmpresaNombreBusqueda(prod) {
    return $("#nombreEmpresa").text().trim() ||
        prod?.empresa_nombre ||
        prod?.nombre_empresa ||
        prod?.empresa ||
        prod?.tienda_nombre ||
        "";
}

// function getSearchScoreForItem(prod) {
//     const query = normalizarTexto(searchText);
//     if (!query) return 0;

//     const terms = query.split(/\s+/).filter(Boolean);
//     const empresa = normalizarTexto(getEmpresaNombreBusqueda(prod));
//     const provincia = normalizarTexto(prod?.provincia);
//     const canton = normalizarTexto(prod?.canton);
//     const fullText = [empresa, provincia, canton].filter(Boolean).join(" ");

//     let score = 0;

//     if (empresa.includes(query)) score += 300;
//     else if (provincia.includes(query) || canton.includes(query)) score += 180;
//     else if (fullText.includes(query)) score += 120;

//     terms.forEach(term => {
//         if (empresa.includes(term)) score += 40;
//         else if (provincia.includes(term) || canton.includes(term)) score += 22;
//         else if (fullText.includes(term)) score += 12;
//     });

//     return score;
// }

// function getSearchScoreForItem(prod) {
//     const query = normalizarTexto(searchText);
//     if (!query) return 0;

//     const itemType = getItemType(prod);

//     // Solo puntuar si coincide con el tipo activo
//     if (itemType !== tipoFiltro) return 0;

//     const terms = query.split(/\s+/).filter(Boolean);

//     let campos = [];

//     if (itemType === "vehiculo") {
//         campos = [
//             normalizarTexto(getMarcaNombres(prod).join(" ")),
//             normalizarTexto(getModeloNombres(prod).join(" ")),
//             normalizarTexto(prod?.anio),
//             normalizarTexto(prod?.descripcion),
//             normalizarTexto(prod?.provincia),
//             normalizarTexto(prod?.canton),
//         ];
//     } else if (itemType === "servicio") {
//         campos = [
//             normalizarTexto(prod?.titulo_producto),
//             normalizarTexto(prod?.nombre),
//             normalizarTexto(prod?.tags),
//             normalizarTexto(prod?.provincia),
//             normalizarTexto(prod?.canton),
//         ];
//     } else {
//         // producto
//         campos = [
//             normalizarTexto(prod?.titulo_producto),
//             normalizarTexto(prod?.nombre),
//             normalizarTexto(prod?.tags),
//             normalizarTexto(JSON.stringify(prod?.marca || "")),
//             normalizarTexto(JSON.stringify(prod?.modelo || "")),
//             normalizarTexto(prod?.provincia),
//             normalizarTexto(prod?.canton),
//         ];
//     }

//     const fullText = campos.filter(Boolean).join(" ").replace(/\s+/g, " ").trim();

//     let score = 0;

//     // Prioridad 1: coincidencia exacta de la frase completa
//     if (fullText.includes(query)) score += 300;

//     // Prioridad 2: todas las palabras presentes (en cualquier orden)
//     const todasPresentes = terms.every(term => fullText.includes(term));
//     if (todasPresentes && score === 0) score += 150;

//     // Prioridad 3: algunas palabras presentes
//     if (score === 0) {
//         terms.forEach(term => {
//             if (fullText.includes(term)) score += 40;
//         });
//     }

//     return score;
// }

function getSearchScoreForItem(prod) {
    const query = normalizarTexto(searchText.trim());
    if (!query) return 1; // ← si no hay búsqueda, todos pasan con score 1

    const itemType = getItemType(prod);
    if (itemType !== tipoFiltro) return 0;

    const terms = query.split(/\s+/).filter(Boolean);

    let campos = [];

    if (itemType === "vehiculo") {
        campos = [
            normalizarTexto(getMarcaNombres(prod).join(" ")),
            normalizarTexto(getModeloNombres(prod).join(" ")),
            normalizarTexto(prod?.anio),
            normalizarTexto(prod?.descripcion),
            normalizarTexto(prod?.provincia),
            normalizarTexto(prod?.canton),
        ];
    } else if (itemType === "servicio") {
        campos = [
            normalizarTexto(prod?.titulo_producto),
            normalizarTexto(prod?.nombre),
            normalizarTexto(prod?.tags),
            normalizarTexto(prod?.provincia),
            normalizarTexto(prod?.canton),
        ];
    } else {
        campos = [
            normalizarTexto(prod?.titulo_producto),
            normalizarTexto(prod?.nombre),
            normalizarTexto(prod?.tags),
            normalizarTexto(JSON.stringify(prod?.marca || "")),
            normalizarTexto(JSON.stringify(prod?.modelo || "")),
            normalizarTexto(prod?.provincia),
            normalizarTexto(prod?.canton),
        ];
    }

    const fullText = campos.filter(Boolean).join(" ").replace(/\s+/g, " ").trim();

    let score = 0;

    // Prioridad 1: frase completa exacta
    if (fullText.includes(query)) score += 300;

    // Prioridad 2: todas las palabras presentes
    const todasPresentes = terms.every(term => fullText.includes(term));
    if (todasPresentes && score === 0) score += 150;

    // Prioridad 3: alguna palabra presente
    if (score === 0) {
        terms.forEach(term => {
            if (fullText.includes(term)) score += 40;
        });
    }

    return score;
}
function formatPrecioSuperscript(valor) {
    const num = Number(valor) || 0;
    const entero = Math.floor(num);
    const centavos = Math.round((num - entero) * 100).toString().padStart(2, '0');
    const enteroFormateado = entero.toLocaleString('es-EC');
    return `<span style="font-size:0.55em; font-weight:normal; vertical-align:middle;">US$</span><strong style="font-size:1.1em;">${enteroFormateado}</strong><sup style="font-size:0.45em; font-weight:normal; vertical-align:super; line-height:1;">${centavos}</sup>`;
}

function updateVehicleFilterVisibility() {
    const isVehicleMode = tipoFiltro === "vehiculo";
    $(".vehicle-filter-item").toggle(isVehicleMode);
}

function openMobileFilters() {
    if (window.innerWidth >= 992) return;
    $("#mobileFilters").addClass("is-open");
    $("#productsMobileOverlay").addClass("is-open");
    $("body").addClass("products-filter-open");
}

function closeMobileFilters() {
    $("#mobileFilters").removeClass("is-open");
    $("#productsMobileOverlay").removeClass("is-open");
    $("body").removeClass("products-filter-open");
}

function clearAllFilters() {
    searchText = "";
    sortOption = "todos";
    tipoFiltro = "producto";
    subcategoriasSeleccionadas = [];
    subcategoriasHijas = [];
    subcategoriasHijasSeleccionadas = [];
    marcasSeleccionadas = [];
    modelosSeleccionados = [];
    nombresServiciosSeleccionados = [];
    filtroTextoMarca = "";
    filtroTextoModelo = "";
    filtroTextoCategoria = "";
    filtroTextoSubcategoria = "";
    condicionSeleccionadas = [];
    tipoAutoSeleccionados = [];
    colorSeleccionados = [];
    tapiceriaSeleccionados = [];
    climatizacionSeleccionadas = [];
    referenciasSeleccionadas = [];
    anioMinSel = null;
    anioMaxSel = null;
    provinciaSel = { id: null, nombre: null };
    cantonSel = { id: null, nombre: null };

    $("#inputBusqueda").val("");
    $(".widget_search input").val("");
    $("#typeSwitcherVendor .vendor-type-chip").removeClass("is-active");
    $('#typeSwitcherVendor [data-item-type="producto"]').addClass("is-active");
    $(".sort-order li a").removeClass("active");
    $(".sort-order li a[data-value='todos']").addClass("active");
    $(".sort-by-product-wrap:last .sort-by-dropdown-wrap span").html(`Todos <i class="fi-rs-angle-small-down"></i>`);
    const selectProvincia = document.getElementById("selectProvincia");
    if (selectProvincia) selectProvincia.value = "";
    resetSelectCanton();
    $("#anioMin").val("");
    $("#anioMax").val("");

    actualizarUIUbicacionPersistir();
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
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
        return `<a href="${href}" class="vendor-social-link" target="_blank" rel="noopener" aria-label="${label}" title="${label}"><i class="${icon}"></i></a>`;
    }).join("");
}

function renderCompanyDescriptionPreview(empresa) {
    const text = safe(empresa?.descripcion).trim();
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

function syncMobileStoreHero(empresa, waUrl, verificado) {
    $("#mobileStoreImage")
        .attr("src", empresa?.img_path ? `admin/${empresa.img_path}` : "img/FULMUV-NEGRO.png")
        .attr("onerror", "this.onerror=null;this.src='img/FULMUV-NEGRO.png';");
    $("#mobileStoreName").text(capitalizarPrimeraLetra(empresa?.nombre || "Tienda"));
    $("#mobileStoreSocialLinks").html(buildSocialLinksHtml(empresa));
    $("#mobileStoreWhatsappCta").attr({
        href: waUrl || "#",
        target: "_blank",
        rel: "noopener"
    }).toggle(!!waUrl && waUrl !== "#");
    $("#mobileStoreVerifyBadge").toggle(!!verificado);
}

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

function registrarInteraccionEmpresa(tipoEvento, payload = {}) {
    if (!id_empresa || !tipoEvento) return;
    const data = new FormData();
    data.append("id_producto", id_empresa);
    data.append("tipo_evento", tipoEvento);
    data.append("cantidad", payload.cantidad || 1);
    data.append("session_key", obtenerSessionKeyTracking());
    data.append("detalle_url", window.location.href);
    data.append("referencia", document.referrer || "");
    data.append("metadata", JSON.stringify(payload.metadata || {}));
    data.append("tipo", "empresa");
    data.append("id_usuario", obtenerIdUsuarioTracking());

    if (navigator.sendBeacon) {
        navigator.sendBeacon("api/track_producto_interaccion.php", data);
    } else {
        $.ajax({
            url: "api/track_producto_interaccion.php",
            method: "POST",
            data: Object.fromEntries(data),
            dataType: "json"
        });
    }
}


$(document).ready(function () {

    actualizarIconoCarrito();
    $("#breadcrumb").append(`
        <a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
        <span></span> Lista de Servicios
    `)

    $("#btnToggleMobileFilters").on("click", openMobileFilters);
    $("#closeMobileFilters, #applyMobileFilters, #productsMobileOverlay").on("click", closeMobileFilters);
    $("#clearMobileFilters").on("click", function () {
        clearAllFilters();
        closeMobileFilters();
    });

    $(document).on("keydown.mobileFiltersVendor", function (e) {
        if (e.key === "Escape") {
            closeMobileFilters();
        }
    });

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
            $("#empresaSocialLinks").html(buildSocialLinksHtml(returnedData.data));
            renderCompanyDescriptionPreview(returnedData.data);
            $("#mobileStoreDescriptionPreview").text(safe(returnedData.data.descripcion || ""));
            const mobileNeedsToggle = safe(returnedData.data.descripcion || "").trim().length > 180;
            $("#mobileStoreDescriptionPreview").toggle(!!safe(returnedData.data.descripcion || "").trim()).addClass("is-collapsed");
            $("#mobileStoreDescriptionToggle").text("Ver más").toggle(mobileNeedsToggle);

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
            syncMobileStoreHero(returnedData.data, waUrl, !!verificacion);

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
                    ? `<a class="contact-phone" href="tel:${telDigits}">${telRaw}</a>`
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
                    ? `<a class="contact-whatsapp" href="${waUrl}" target="_blank" rel="noopener">${waRaw}</a>`
                    : `<span class="text-muted">No disponible</span>`
                }
                </div>
                `);

            $(document).off("click", "#btnWhatsSidebar, #mobileStoreWhatsappCta").on("click", "#btnWhatsSidebar, #mobileStoreWhatsappCta", function () {
                registrarInteraccionEmpresa("click_whatsapp", { metadata: { origen: "productos_vendor_btn" } });
            });

            $(document).off("click", ".contact-whatsapp").on("click", ".contact-whatsapp", function () {
                registrarInteraccionEmpresa("click_whatsapp", { metadata: { origen: "productos_vendor_contacto" } });
            });

            $(document).off("click", ".contact-phone").on("click", ".contact-phone", function () {
                registrarInteraccionEmpresa("click_telefono", { metadata: { origen: "productos_vendor_contacto" } });
            });

            $(document).off("click", "#toggleDescripcionEmpresa, #mobileStoreDescriptionToggle").on("click", "#toggleDescripcionEmpresa, #mobileStoreDescriptionToggle", function (e) {
                e.preventDefault();
                const targetSelector = this.id === "mobileStoreDescriptionToggle" ? "#mobileStoreDescriptionPreview" : "#descripcionEmpresaPreview";
                const expanded = $(targetSelector).toggleClass("is-collapsed").hasClass("is-collapsed");
                $(this).text(expanded ? "Ver más" : "Ver menos");
            });

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
            productosData = shuffleArray(filterByMembresiaActiva(normalizarItemsEmpresa(returnedData) || []));
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
            updateVehicleFilterVisibility();

        }
    }, 'json');

    $("#typeSwitcherVendor").on("click", "[data-item-type]", function () {
        tipoFiltro = $(this).data("item-type") || "producto";
        $("#typeSwitcherVendor .vendor-type-chip").removeClass("is-active");
        $(this).addClass("is-active");
        updateVehicleFilterVisibility();
        refreshFiltersForCurrentLocation();
        renderEmpresas(productosData, 1);
    });




})

$(document).on("change", "input[name='checkbox']", function () {
    if ($(this).val() === "__all__") {
        subcategoriasSeleccionadas = [];
        subcategoriasHijas = [];
        subcategoriasHijasSeleccionadas = [];
        $("input[name='checkbox']").not(this).prop("checked", false);
        $(this).prop("checked", true);
        $("#filtro-sub-categorias").html("");
        $("#subcats-box").hide();
        $.post("api/v1/fulmuv/productos/idEmpresa", { id_empresa: id_empresa }, function (returnedData) {
            if (!returnedData.error) {
                productosData = shuffleArray(filterByMembresiaActiva(normalizarItemsEmpresa(returnedData) || []));
                const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
                inicializarSlider(maxPrecio);
                refreshFiltersForCurrentLocation();
                renderEmpresas(productosData, 1);
            }
        }, 'json');
        return;
    }

    const id = $(this).val();

    if ($(this).is(":checked")) {
        if (!subcategoriasSeleccionadas.includes(id)) {
            subcategoriasSeleccionadas.push(id); // agrega solo si no existe
        }
    } else {
        subcategoriasSeleccionadas = subcategoriasSeleccionadas.filter(sub => sub !== id);
    }
    $("#categoria-all").prop("checked", subcategoriasSeleccionadas.length === 0);

    // normalizar IDs únicos (evita payload duplicado)
    const idsUnicos = [...new Set(subcategoriasSeleccionadas.map(x => parseInt(x)))];

    // toggle del bloque de subcategorías
    const haySeleccion = idsUnicos.length > 0;
    $("#subcats-box").toggle(haySeleccion);

    if (!haySeleccion) {
        $.post("api/v1/fulmuv/productos/idEmpresa", { id_empresa: id_empresa }, function (returnedData) {
            if (!returnedData.error) {
                productosData = shuffleArray(filterByMembresiaActiva(normalizarItemsEmpresa(returnedData) || []));
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
            productosData = shuffleArray(filterByMembresiaActiva(normalizarItemsEmpresa(returnedData) || []));
            const maxPrecio = Math.max(...productosData.map(p => parseFloat(p.precio_referencia)));
            inicializarSlider(maxPrecio);
            refreshFiltersForCurrentLocation();
            renderEmpresas(productosData, 1);
        }
    }, 'json');

    // 2) Subcategorías por categorías seleccionadas (únicas) – local, sin API
    const subcats = buildSubcatsForSelected(idsUnicos);
    subcategoriasHijas = subcats;
    subcategoriasHijasSeleccionadas = [];
    renderCheckboxSubcategorias(subcats);
    $("#subcats-box").toggle(subcats.length > 0);
    refreshFiltersForCurrentLocation();

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

    let html = renderFilterSearchInput("subcategoria", "Buscar sub categoría", filtroTextoSubcategoria);
    let htmlVisible = `
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="__all__" id="subcat-all" name="checkbox-sub" ${subcategoriasHijasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="subcat-all">Todos</label>
      </div>`;
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
        const searchableName = capitalizarPrimeraLetra(sc.nombre);
        const item = `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(searchableName))}">
        <input class="form-check-input" type="checkbox" value="${id}" id="subcat-${id}" name="checkbox-sub">
        <label class="form-check-label fw-normal" for="subcat-${id}">
          ${searchableName}
        </label>
      </div>`;
        if (index < maxVisible) htmlVisible += item; else htmlOcultas += item;
    });

    const htmlFinal = html + `
    <div class="checkbox-list-visible">
      ${htmlVisible || '<small class="text-muted">No hay sub-categorías para las categorías seleccionadas.</small>'}
    </div>
    <div class="more_slide_open-sub" style="display:none;">
      ${htmlOcultas}
    </div>
    <small class="text-muted filter-option-empty"${subcats.length ? ' style="display:none;"' : ''}>No hay subcategorías.</small>
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

    applyFilterOptionSearch("subcategoria", filtroTextoSubcategoria);
}

function renderVehicleCheckboxGroup(targetSelector, inputName, items, selectedValues) {
    const $target = $(targetSelector);
    if (!$target.length) return;

    if (!items || items.length === 0) {
        $target.empty();
        return;
    }

    const selectedSet = new Set((selectedValues || []).map(normalizarTexto));
    let html = "";
    items.forEach((item, index) => {
        const checked = selectedSet.has(normalizarTexto(item)) ? "checked" : "";
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="${escapeHtmlAttribute(item)}" id="${inputName}-${index}" name="${inputName}" ${checked}>
        <label class="form-check-label fw-normal" for="${inputName}-${index}">
          ${capitalizarPrimeraLetra(item)}
        </label>
      </div>`;
    });

    $target.html(html);
}

function renderServicioCheckboxGroup(targetSelector, inputName, items, selectedValues) {
    const $target = $(targetSelector);
    if (!$target.length) return;

    if (!items || items.length === 0) {
        $target.empty();
        toggleFilterBlock(targetSelector, false);
        return;
    }

    toggleFilterBlock(targetSelector, true);
    const selectedSet = new Set((selectedValues || []).map(normalizarTexto));
    let html = `
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="${inputName}" id="${inputName}-all" value="__all__" ${selectedSet.size === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="${inputName}-all">Todos</label>
    </div>`;

    items.forEach((item, index) => {
        const checked = selectedSet.has(normalizarTexto(item)) ? "checked" : "";
        html += `
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="${escapeHtmlAttribute(item)}" id="${inputName}-${index}" name="${inputName}" ${checked}>
        <label class="form-check-label fw-normal" for="${inputName}-${index}">${capitalizarPrimeraLetra(item)}</label>
      </div>`;
    });

    $target.html(html);
}


function buildMarcasYModelos(marcasData, modelosData = marcasData) {
    const marcasMap = new Map();  // id -> {id, nombre}
    const modelosMap = new Map(); // id -> {id, nombre}

    (marcasData || []).forEach(p => {
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

    });

    (modelosData || []).forEach(p => {
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

    renderCheckboxMarcas(marcasUnicas);
    renderCheckboxModelos(modelosUnicos);
}


function renderCheckboxMarcas(marcas) {
    if (!marcas || marcas.length === 0) {
        $("#filtro-marca").empty();
        toggleFilterBlock('#filtro-marca', false);
        return;
    }
    toggleFilterBlock('#filtro-marca', true);

    let html = renderFilterSearchInput("marca", "Buscar marca", filtroTextoMarca) + `
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="checkbox-marca" id="marca-all" value="__all__" ${marcasSeleccionadas.length === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="marca-all">Todos</label>
    </div>`;
    marcas.forEach(m => {
        const searchableName = capitalizarPrimeraLetra(m.nombre);
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(searchableName))}">
        <input class="form-check-input" type="checkbox" name="checkbox-marca" id="marca-${m.id}" value="${m.id}" ${marcasSeleccionadas.includes(Number(m.id)) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="marca-${m.id}">${searchableName}</label>
      </div>`;
    });
    html += `<small class="text-muted filter-option-empty"${marcas.length ? ' style="display:none;"' : ''}>No hay marcas.</small>`;
    $("#filtro-marca").html(html);
    applyFilterOptionSearch("marca", filtroTextoMarca);

    $(document).off("change", "input[name='checkbox-marca']")
        .on("change", "input[name='checkbox-marca']", function () {
            if (this.value === "__all__") {
                marcasSeleccionadas = [];
                $("input[name='checkbox-marca']").not(this).prop("checked", false);
                this.checked = true;
            } else {
                const v = Number($(this).val());
                if ($(this).is(":checked")) {
                    if (!marcasSeleccionadas.includes(v)) marcasSeleccionadas.push(v);
                } else {
                    marcasSeleccionadas = marcasSeleccionadas.filter(id => id !== v);
                }
                $("#marca-all").prop("checked", marcasSeleccionadas.length === 0);
            }
            refreshFiltersForCurrentLocation();
            renderEmpresas(productosData, 1);
        });
}

function renderCheckboxModelos(modelos) {
    if (!modelos || modelos.length === 0) {
        $("#filtro-modelo").empty();
        toggleFilterBlock('#filtro-modelo', false);
        return;
    }
    toggleFilterBlock('#filtro-modelo', true);

    let html = renderFilterSearchInput("modelo", "Buscar modelo", filtroTextoModelo) + `
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="checkbox-modelo" id="modelo-all" value="__all__" ${modelosSeleccionados.length === 0 ? 'checked' : ''}>
      <label class="form-check-label fw-normal" for="modelo-all">Todos</label>
    </div>`;
    modelos.forEach(mo => {
        const searchableName = capitalizarPrimeraLetra(mo.nombre);
        html += `
      <div class="form-check mb-2 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(searchableName))}">
        <input class="form-check-input" type="checkbox" name="checkbox-modelo" id="modelo-${mo.id}" value="${mo.id}" ${modelosSeleccionados.includes(Number(mo.id)) ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="modelo-${mo.id}">${searchableName}</label>
      </div>`;
    });
    html += `<small class="text-muted filter-option-empty"${modelos.length ? ' style="display:none;"' : ''}>No hay modelos.</small>`;
    $("#filtro-modelo").html(html);
    applyFilterOptionSearch("modelo", filtroTextoModelo);

    $(document).off("change", "input[name='checkbox-modelo']")
        .on("change", "input[name='checkbox-modelo']", function () {
            if (this.value === "__all__") {
                modelosSeleccionados = [];
                $("input[name='checkbox-modelo']").not(this).prop("checked", false);
                this.checked = true;
            } else {
                const v = Number($(this).val());
                if ($(this).is(":checked")) {
                    if (!modelosSeleccionados.includes(v)) modelosSeleccionados.push(v);
                } else {
                    modelosSeleccionados = modelosSeleccionados.filter(id => id !== v);
                }
                $("#modelo-all").prop("checked", modelosSeleccionados.length === 0);
            }
            refreshFiltersForCurrentLocation();
            renderEmpresas(productosData, 1);
        });
}

$(document).on("change", "input[name='checkbox-condicion']", function () {
    condicionSeleccionadas = collectCheckedValues("checkbox-condicion");
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});

$(document).on("change", "input[name='checkbox-tipo-auto']", function () {
    tipoAutoSeleccionados = collectCheckedValues("checkbox-tipo-auto");
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});

$(document).on("change", "input[name='checkbox-color']", function () {
    colorSeleccionados = collectCheckedValues("checkbox-color");
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});

$(document).on("change", "input[name='checkbox-tapiceria']", function () {
    tapiceriaSeleccionados = collectCheckedValues("checkbox-tapiceria");
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});

$(document).on("change", "input[name='checkbox-climatizacion']", function () {
    climatizacionSeleccionadas = collectCheckedValues("checkbox-climatizacion");
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});

$(document).on("change", "input[name='checkbox-referencia']", function () {
    referenciasSeleccionadas = collectCheckedValues("checkbox-referencia");
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
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
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});

$(document).on("input", "#anioMin, #anioMax", function () {
    anioMinSel = normalizeYearValue($("#anioMin").val());
    anioMaxSel = normalizeYearValue($("#anioMax").val());
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});


// Selección/deselección de subcategorías
$(document).on("change", "input[name='checkbox-sub']", function () {
    if ($(this).val() === "__all__") {
        subcategoriasHijasSeleccionadas = [];
        $("input[name='checkbox-sub']").not(this).prop("checked", false);
        $(this).prop("checked", true);
        refreshFiltersForCurrentLocation();
        renderEmpresas(productosData, 1);
        return;
    }

    const id = Number($(this).val()); // <-- número
    if ($(this).is(":checked")) {
        if (!subcategoriasHijasSeleccionadas.includes(id)) subcategoriasHijasSeleccionadas.push(id);
    } else {
        subcategoriasHijasSeleccionadas = subcategoriasHijasSeleccionadas.filter(x => x !== id);
    }
    $("#subcat-all").prop("checked", subcategoriasHijasSeleccionadas.length === 0);
    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
});


function renderEmpresas(data, page = 1) {

    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    let empresasFiltradas = data.filter(prod => {
        const searchScore = getSearchScoreForItem(prod);
        const matchSearch = searchText.trim() === "" || getSearchScoreForItem(prod) > 0;
        const itemType = getItemType(prod);
        const matchTipo = itemType === tipoFiltro;
        // --- Categorías / Subcategorías ---
        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);
        const anio = Number(prod.anio || 0);

        const matchCat = itemType === "vehiculo" ? true : ((selectedCatsSet.size === 0) || hasIntersection(prodCats, selectedCatsSet));
        const matchSubHija = itemType === "vehiculo" ? true : ((selectedSubSet.size === 0) || hasIntersection(prodSubs, selectedSubSet));

        // --- Marca/Modelo ---
        const matchMarca = marcasSeleccionadas.length === 0 || prodBrand.some(id => marcasSeleccionadas.includes(Number(id)));
        const matchModelo = modelosSeleccionados.length === 0 || prodModel.some(id => modelosSeleccionados.includes(Number(id)));
        const matchCondicion = itemType !== "vehiculo" || condicionSeleccionadas.length === 0 || hasIntersectionText(getVehicleConditionNames(prod), condicionSeleccionadas);
        const matchTipoAuto = itemType !== "vehiculo" || tipoAutoSeleccionados.length === 0 || hasIntersectionText(getVehicleTipoAutoNames(prod), tipoAutoSeleccionados);
        const matchColor = itemType !== "vehiculo" || colorSeleccionados.length === 0 || hasIntersectionText(getVehicleColorNames(prod), colorSeleccionados);
        const matchTapiceria = itemType !== "vehiculo" || tapiceriaSeleccionados.length === 0 || hasIntersectionText(getVehicleTapiceriaNames(prod), tapiceriaSeleccionados);
        const matchClimatizacion = itemType !== "vehiculo" || climatizacionSeleccionadas.length === 0 || hasIntersectionText(getVehicleClimatizacionNames(prod), climatizacionSeleccionadas);
        const matchReferencia = itemType !== "vehiculo" || referenciasSeleccionadas.length === 0 || hasIntersectionText(getVehicleReferenciaNames(prod), referenciasSeleccionadas);
        const matchNombreServicio = itemType !== "servicio" || nombresServiciosSeleccionados.length === 0 || hasIntersectionText(getServiceNameOptions(prod), nombresServiciosSeleccionados);
        const matchAnioMin = itemType !== "vehiculo" || !anioMinSel || (anio > 0 && anio >= anioMinSel);
        const matchAnioMax = itemType !== "vehiculo" || !anioMaxSel || (anio > 0 && anio <= anioMaxSel);

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
            && matchCondicion
            && matchTipoAuto
            && matchColor
            && matchTapiceria
            && matchClimatizacion
            && matchReferencia
            && matchNombreServicio
            && matchAnioMin
            && matchAnioMax
            && matchProvincia
            && matchCanton;
    });




    // Ordenamiento
    if (sortOption === "mayor") {
        empresasFiltradas.sort((a, b) => b.precio_referencia - a.precio_referencia);
    } else if (sortOption === "menor") {
        empresasFiltradas.sort((a, b) => a.precio_referencia - b.precio_referencia);
    } else if (searchText.trim()) {
        empresasFiltradas.sort((a, b) => getSearchScoreForItem(b) - getSearchScoreForItem(a));
    }

    // Paginación
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const empresasPagina = empresasFiltradas.slice(start, end);

    // Renderizado
    let listProductos = "";
    const isMobileAppStyle = window.innerWidth < 992;
    $(".product-grid").toggleClass("app-mobile-grid", isMobileAppStyle);

    console.log("BUSQUEDA SD")
    console.log(empresasPagina)
    empresasPagina.forEach(function (productos) {
        const itemType = getItemType(productos);
        const tieneDescuento = parseFloat(productos.descuento) > 0;
        const precioDescuento = productos.precio_referencia - (productos.precio_referencia * productos.descuento / 100);

        const detalleUrl = getDetalleUrl(productos);
        const tituloCard = getTituloProductoOServicio(productos);
        const tipoItem = itemType || "producto";
        const itemId = getItemId(productos);
        const verificado = isVerifiedItem(productos);
        if (isMobileAppStyle) {
            listProductos += `
            <article class="product-card-modern">
                <a href="${detalleUrl}" target="_blank" rel="noopener noreferrer" class="product-card-link track-detalle" data-track-id="${itemId}" data-track-tipo="${tipoItem}">
                    <div class="product-media">
                        ${tieneDescuento ? `<span class="product-badge">-${parseInt(productos.descuento)}</span>` : ''}
                        ${verificado ? `<img src="img/verificado_empresa.png" alt="Empresa verificada" class="product-verify-badge">` : ""}
                        <img src="admin/${productos.img_frontal}" onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" alt="${tituloCard}">
                    </div>
                    <div class="product-body">
                        <div class="product-brand">${capitalizarPrimeraLetra((getMarcaNombres(productos)[0] || "General"))}</div>
                        <div class="product-title fw-normal">${tituloCard}</div>
                        <div class="product-footer">
                            <div class="product-price">
                                <strong>${formatPrecioSuperscript(tieneDescuento ? precioDescuento : productos.precio_referencia)}</strong>
${tieneDescuento ? `<span class="old-price">${formatPrecioSuperscript(productos.precio_referencia)}</span>` : ''}                            </div>
                            <span class="product-cta"><i class="fi-rs-arrow-small-right"></i></span>
                        </div>
                    </div>
                </a>
            </article>`;
        } else {
            listProductos += `
            <div class="col-md-4 col-lg-3 col-sm-4 col-6 mb-4 d-flex">
                <div class="product-cart-wrap w-100 d-flex flex-column">
                <div class="product-img-action-wrap text-center">
                    <div class="product-img product-img-zoom">
                    <a href="${detalleUrl}" target="_blank" rel="noopener noreferrer" class="track-detalle" data-track-id="${itemId}" data-track-tipo="${tipoItem}">
                        ${verificado ? `<img src="img/verificado_empresa.png" alt="Empresa verificada" class="product-verify-badge">` : ""}
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
                    <h6 class="limitar-lineas mb-2 mt-2 fw-normal">
                        <a href="${detalleUrl}" target="_blank" rel="noopener noreferrer" class="track-detalle" data-track-id="${itemId}" data-track-tipo="${tipoItem}">${tituloCard}</a>
                    </h6>
                    </div>

                    <div class="mt-auto">
                    <div class="product-price text-center">
<span>${formatPrecioSuperscript(tieneDescuento ? precioDescuento : productos.precio_referencia)}</span>
${tieneDescuento ? `<span class="old-price">${formatPrecioSuperscript(productos.precio_referencia)}</span>` : ''}
                    </div>
                    </div>
                </div>
                </div>
            </div>`;
        }
    });
    $("#totalProductosGeneral").text(empresasFiltradas.length);
    $(".product-grid").html(listProductos);
    renderPaginacion(empresasFiltradas.length, page);

    if (empresasPagina.length === 0) {
        $(".product-grid").html(`
        <div class="col-12 text-center">
            <p class="text-muted">No hay resultados disponibles para este proveedor con los filtros actuales.</p>
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

    let htmlVisible = `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="todos">
        <input class="form-check-input" type="checkbox" value="__all__" id="categoria-all" name="checkbox" ${subcategoriasSeleccionadas.length === 0 ? 'checked' : ''}>
        <label class="form-check-label fw-normal" for="categoria-all">Todos</label>
      </div>`;

    categorias.forEach((cat) => {
        const searchableName = capitalizarPrimeraLetra(cat.nombre);
        const checkboxHTML = `
      <div class="form-check mb-3 filter-option-row" data-filter-search-text="${escapeHtmlAttribute(normalizarTexto(searchableName))}">
        <input class="form-check-input" type="checkbox" value="${cat.id_categoria}" id="categoria-${cat.id_categoria}" name="checkbox">
        <label class="form-check-label fw-normal" for="categoria-${cat.id_categoria}">
          ${searchableName}
        </label>
      </div>`;
        htmlVisible += checkboxHTML;
    });

    const htmlFinal = `
    ${renderFilterSearchInput("categoria", "Buscar categoría", filtroTextoCategoria)}
    <div class="checkbox-list-visible">${htmlVisible}</div>
    <small class="text-muted filter-option-empty"${categorias.length ? ' style="display:none;"' : ''}>No hay categorías.</small>`;

    $("#filtro-categorias").html(htmlFinal);

    applyFilterOptionSearch("categoria", filtroTextoCategoria);
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
    const targetMap = {
        marca: "#filtro-marca",
        modelo: "#filtro-modelo",
        categoria: "#filtro-categorias",
        subcategoria: "#filtro-sub-categorias"
    };

    const container = $(targetMap[tipo] || "");
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

    if (tipo === "subcategoria") {
        const moreButton = container.find(tipo === "categoria" ? ".more_categories-cat" : ".more_categories-sub");
        const hiddenBlock = container.find(tipo === "categoria" ? ".more_slide_open-cat" : ".more_slide_open-sub");
        if (moreButton.length) {
            moreButton.toggle(visibles > 10);
        }
        if (hiddenBlock.length && visibles <= 10) {
            hiddenBlock.hide();
            moreButton.removeClass("show");
        }
    }
}

$(document).off("input.filterOptionSearchVendor").on("input.filterOptionSearchVendor", ".filter-option-search", function () {
    const tipo = $(this).data("filter-type");
    const valor = $(this).val().trim();

    if (tipo === "marca") {
        filtroTextoMarca = valor;
    } else if (tipo === "modelo") {
        filtroTextoModelo = valor;
    } else if (tipo === "categoria") {
        filtroTextoCategoria = valor;
    } else if (tipo === "subcategoria") {
        filtroTextoSubcategoria = valor;
    }

    applyFilterOptionSearch(tipo, valor);
});






$(document).on("click", ".pagination .page-link", function (e) {
    e.preventDefault();
    const page = parseInt($(this).data("page"));
    if (!isNaN(page)) {
        currentPage = page;
        renderEmpresas(productosData, currentPage);
        $("html, body").animate({
            scrollTop: $(".product-grid").offset().top - 120
        }, 300);
    }
});


// // Búsqueda por nombre o ID
// $("#inputBusqueda, .widget_search input").on("input", function () {
//     searchText = $(this).val().trim();
//     $("#inputBusqueda, .widget_search input").val(searchText);
//     refreshFiltersForCurrentLocation();
//     renderEmpresas(productosData, 1); // usa productosData, no empresasData
// });

// DESPUÉS
// $("#inputBusqueda, .widget_search input").on("input", function () {
//     searchText = $(this).val();
//     // Sincroniza ambos inputs si existen los dos
//     $("#inputBusqueda, .widget_search input").val(searchText);
//     refreshFiltersForCurrentLocation();
//     renderEmpresas(productosData, 1);
// });

$("#inputBusqueda, .widget_search input").on("input", function () {
    searchText = $(this).val(); // sin .trim()

    // Si el buscador está vacío, reset completo de búsqueda
    if (searchText === "") {
        refreshFiltersForCurrentLocation();
        renderEmpresas(productosData, 1);
        return;
    }

    refreshFiltersForCurrentLocation();
    renderEmpresas(productosData, 1);
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

// function normalizarTexto(s) {
//     return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();
// }

// DESPUÉS
function normalizarTexto(s) {
    return (s || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, ' ')
        .toLowerCase()
        .trim();
}
function categoriaInicialCumpleTipo(prod) {
    if (tipoFiltro === "vehiculo") return getItemType(prod) === "vehiculo";

    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    if (!ids.length) return true; // no bloquees si no hay ids
    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return first ? (first.tipo === tipoFiltro) : getItemType(prod) === tipoFiltro;
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
        return matchProvincia && matchCanton;
    });
}

// function getFilteredDataForOptionRefresh(dataset, options = {}) {
//     const ignoreGroups = new Set(options.ignoreGroups || []);
//     const source = filtrarPorUbicacionDataset(dataset).filter(prod => getItemType(prod) === tipoFiltro);
//     const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
//     const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

//     return (source || []).filter(prod => {
//         const matchSearch = searchText.trim() === "" || getSearchScoreForItem(prod) > 0;
//         const itemType = getItemType(prod);
//         const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
//         const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
//         const prodBrand = parseIdsArray(prod.id_marca);
//         const prodModel = parseIdsArray(prod.id_modelo);
//         const anio = Number(prod.anio || 0);
//         const precio = Number(prod.precio_referencia || 0);

//         return matchSearch
//             && (itemType === "vehiculo" || ignoreGroups.has("categoria") || selectedCatsSet.size === 0 || hasIntersection(prodCats, selectedCatsSet))
//             && (itemType === "vehiculo" || ignoreGroups.has("subcategoria") || selectedSubSet.size === 0 || hasIntersection(prodSubs, selectedSubSet))
//             && (ignoreGroups.has("marca") || marcasSeleccionadas.length === 0 || prodBrand.some(id => marcasSeleccionadas.includes(Number(id))))
//             && (ignoreGroups.has("modelo") || modelosSeleccionados.length === 0 || prodModel.some(id => modelosSeleccionados.includes(Number(id))))
//             && precio >= precioMin && precio <= precioMax
//             && (itemType !== "vehiculo" || ignoreGroups.has("condicion") || condicionSeleccionadas.length === 0 || hasIntersectionText(getVehicleConditionNames(prod), condicionSeleccionadas))
//             && (itemType !== "vehiculo" || ignoreGroups.has("tipoauto") || tipoAutoSeleccionados.length === 0 || hasIntersectionText(getVehicleTipoAutoNames(prod), tipoAutoSeleccionados))
//             && (itemType !== "vehiculo" || ignoreGroups.has("color") || colorSeleccionados.length === 0 || hasIntersectionText(getVehicleColorNames(prod), colorSeleccionados))
//             && (itemType !== "vehiculo" || ignoreGroups.has("tapiceria") || tapiceriaSeleccionados.length === 0 || hasIntersectionText(getVehicleTapiceriaNames(prod), tapiceriaSeleccionados))
//             && (itemType !== "vehiculo" || ignoreGroups.has("climatizacion") || climatizacionSeleccionadas.length === 0 || hasIntersectionText(getVehicleClimatizacionNames(prod), climatizacionSeleccionadas))
//             && (itemType !== "vehiculo" || ignoreGroups.has("referencia") || referenciasSeleccionadas.length === 0 || hasIntersectionText(getVehicleReferenciaNames(prod), referenciasSeleccionadas))
//             && (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMinSel || (anio > 0 && anio >= anioMinSel))
//             && (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMaxSel || (anio > 0 && anio <= anioMaxSel))
//             && (itemType !== "servicio" || ignoreGroups.has("nombreServicio") || nombresServiciosSeleccionados.length === 0 || hasIntersectionText(getServiceNameOptions(prod), nombresServiciosSeleccionados));
//     });
// }

// Reaplica checks “categorías” tras re-render

// function getFilteredDataForOptionRefresh(dataset, options = {}) {
//     const ignoreGroups = new Set(options.ignoreGroups || []);
//     const source = filtrarPorUbicacionDataset(dataset).filter(prod => getItemType(prod) === tipoFiltro);
//     const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
//     const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

//     return (source || []).filter(prod => {
//         // Si no hay búsqueda, todos pasan sin evaluar score
//         const matchSearch = searchText.trim() === "" || getSearchScoreForItem(prod) > 0;

//         const itemType = getItemType(prod);
//         const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
//         const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
//         const prodBrand = parseIdsArray(prod.id_marca);
//         const prodModel = parseIdsArray(prod.id_modelo);
//         const anio = Number(prod.anio || 0);
//         const precio = Number(prod.precio_referencia || 0);

//         return matchSearch
//             && (itemType === "vehiculo" || ignoreGroups.has("categoria") || selectedCatsSet.size === 0 || hasIntersection(prodCats, selectedCatsSet))
//             && (itemType === "vehiculo" || ignoreGroups.has("subcategoria") || selectedSubSet.size === 0 || hasIntersection(prodSubs, selectedSubSet))
//             && (ignoreGroups.has("marca") || marcasSeleccionadas.length === 0 || prodBrand.some(id => marcasSeleccionadas.includes(Number(id))))
//             && (ignoreGroups.has("modelo") || modelosSeleccionados.length === 0 || prodModel.some(id => modelosSeleccionados.includes(Number(id))))
//             && precio >= precioMin && precio <= precioMax
//             && (itemType !== "vehiculo" || ignoreGroups.has("condicion") || condicionSeleccionadas.length === 0 || hasIntersectionText(getVehicleConditionNames(prod), condicionSeleccionadas))
//             && (itemType !== "vehiculo" || ignoreGroups.has("tipoauto") || tipoAutoSeleccionados.length === 0 || hasIntersectionText(getVehicleTipoAutoNames(prod), tipoAutoSeleccionados))
//             && (itemType !== "vehiculo" || ignoreGroups.has("color") || colorSeleccionados.length === 0 || hasIntersectionText(getVehicleColorNames(prod), colorSeleccionados))
//             && (itemType !== "vehiculo" || ignoreGroups.has("tapiceria") || tapiceriaSeleccionados.length === 0 || hasIntersectionText(getVehicleTapiceriaNames(prod), tapiceriaSeleccionados))
//             && (itemType !== "vehiculo" || ignoreGroups.has("climatizacion") || climatizacionSeleccionadas.length === 0 || hasIntersectionText(getVehicleClimatizacionNames(prod), climatizacionSeleccionadas))
//             && (itemType !== "vehiculo" || ignoreGroups.has("referencia") || referenciasSeleccionadas.length === 0 || hasIntersectionText(getVehicleReferenciaNames(prod), referenciasSeleccionadas))
//             && (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMinSel || (anio > 0 && anio >= anioMinSel))
//             && (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMaxSel || (anio > 0 && anio <= anioMaxSel))
//             && (itemType !== "servicio" || ignoreGroups.has("nombreServicio") || nombresServiciosSeleccionados.length === 0 || hasIntersectionText(getServiceNameOptions(prod), nombresServiciosSeleccionados));
//     });
// }

function getFilteredDataForOptionRefresh(dataset, options = {}) {
    const ignoreGroups = new Set(options.ignoreGroups || []);
    const source = filtrarPorUbicacionDataset(dataset).filter(prod => getItemType(prod) === tipoFiltro);
    const selectedCatsSet = new Set(subcategoriasSeleccionadas.map(Number));
    const selectedSubSet = new Set(subcategoriasHijasSeleccionadas.map(Number));

    return (source || []).filter(prod => {
        // Evalúa búsqueda directo, sin depender de getSearchScoreForItem
        let matchSearch = true;
        if (searchText.trim() !== "") {
            const query = normalizarTexto(searchText.trim());
            const itemType = getItemType(prod);
            let campos = [];
            if (itemType === "vehiculo") {
                campos = [
                    normalizarTexto(getMarcaNombres(prod).join(" ")),
                    normalizarTexto(getModeloNombres(prod).join(" ")),
                    normalizarTexto(prod?.anio),
                    normalizarTexto(prod?.descripcion),
                    normalizarTexto(prod?.provincia),
                    normalizarTexto(prod?.canton),
                ];
            } else if (itemType === "servicio") {
                campos = [
                    normalizarTexto(prod?.titulo_producto),
                    normalizarTexto(prod?.nombre),
                    normalizarTexto(prod?.tags),
                    normalizarTexto(prod?.provincia),
                    normalizarTexto(prod?.canton),
                ];
            } else {
                campos = [
                    normalizarTexto(prod?.titulo_producto),
                    normalizarTexto(prod?.nombre),
                    normalizarTexto(prod?.tags),
                    normalizarTexto(JSON.stringify(prod?.marca || "")),
                    normalizarTexto(JSON.stringify(prod?.modelo || "")),
                    normalizarTexto(prod?.provincia),
                    normalizarTexto(prod?.canton),
                ];
            }
            const fullText = campos.filter(Boolean).join(" ").replace(/\s+/g, " ").trim();
            const terms = query.split(/\s+/).filter(Boolean);
            matchSearch = fullText.includes(query) || terms.every(t => fullText.includes(t)) || terms.some(t => fullText.includes(t));
        }

        const itemType = getItemType(prod);
        const prodCats = parseIdsArray(prod.categoria ?? prod.id_categoria);
        const prodSubs = parseIdsArray(prod.sub_categoria ?? prod.id_subcategoria ?? prod.id_sub_categoria);
        const prodBrand = parseIdsArray(prod.id_marca);
        const prodModel = parseIdsArray(prod.id_modelo);
        const anio = Number(prod.anio || 0);
        const precio = Number(prod.precio_referencia || 0);

        return matchSearch
            && (itemType === "vehiculo" || ignoreGroups.has("categoria") || selectedCatsSet.size === 0 || hasIntersection(prodCats, selectedCatsSet))
            && (itemType === "vehiculo" || ignoreGroups.has("subcategoria") || selectedSubSet.size === 0 || hasIntersection(prodSubs, selectedSubSet))
            && (ignoreGroups.has("marca") || marcasSeleccionadas.length === 0 || prodBrand.some(id => marcasSeleccionadas.includes(Number(id))))
            && (ignoreGroups.has("modelo") || modelosSeleccionados.length === 0 || prodModel.some(id => modelosSeleccionados.includes(Number(id))))
            && precio >= precioMin && precio <= precioMax
            && (itemType !== "vehiculo" || ignoreGroups.has("condicion") || condicionSeleccionadas.length === 0 || hasIntersectionText(getVehicleConditionNames(prod), condicionSeleccionadas))
            && (itemType !== "vehiculo" || ignoreGroups.has("tipoauto") || tipoAutoSeleccionados.length === 0 || hasIntersectionText(getVehicleTipoAutoNames(prod), tipoAutoSeleccionados))
            && (itemType !== "vehiculo" || ignoreGroups.has("color") || colorSeleccionados.length === 0 || hasIntersectionText(getVehicleColorNames(prod), colorSeleccionados))
            && (itemType !== "vehiculo" || ignoreGroups.has("tapiceria") || tapiceriaSeleccionados.length === 0 || hasIntersectionText(getVehicleTapiceriaNames(prod), tapiceriaSeleccionados))
            && (itemType !== "vehiculo" || ignoreGroups.has("climatizacion") || climatizacionSeleccionadas.length === 0 || hasIntersectionText(getVehicleClimatizacionNames(prod), climatizacionSeleccionadas))
            && (itemType !== "vehiculo" || ignoreGroups.has("referencia") || referenciasSeleccionadas.length === 0 || hasIntersectionText(getVehicleReferenciaNames(prod), referenciasSeleccionadas))
            && (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMinSel || (anio > 0 && anio >= anioMinSel))
            && (itemType !== "vehiculo" || ignoreGroups.has("anio") || !anioMaxSel || (anio > 0 && anio <= anioMaxSel))
            && (itemType !== "servicio" || ignoreGroups.has("nombreServicio") || nombresServiciosSeleccionados.length === 0 || hasIntersectionText(getServiceNameOptions(prod), nombresServiciosSeleccionados));
    });
} 
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
    marcasSeleccionadas = marcasSeleccionadas.filter(id => brands.has(Number(id)));
    modelosSeleccionados = modelosSeleccionados.filter(id => models.has(Number(id)));
}

// 🔁 Punto central: reconstruye TODOS los filtros con base SOLO en la ubicación actual
function refreshFiltersForCurrentLocation() {
    const locDataBase = filtrarPorUbicacionDataset(productosData).filter(prod => getItemType(prod) === tipoFiltro);
    const locDataTipo = getFilteredDataForOptionRefresh(productosData);
    const locDataCategorias = locDataTipo.filter(prod => getItemType(prod) !== "vehiculo");
    const locVehiculos = locDataTipo.filter(prod => getItemType(prod) === "vehiculo");
    const locServicios = locDataTipo.filter(prod => getItemType(prod) === "servicio");
    const serviceOptionsData = getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["nombreServicio"] })
        .filter(prod => getItemType(prod) === "servicio");
    const vehicleDatasets = {
        marca: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["marca"] }).filter(prod => getItemType(prod) === "vehiculo"),
        modelo: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["modelo"] }).filter(prod => getItemType(prod) === "vehiculo"),
        condicion: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["condicion"] }).filter(prod => getItemType(prod) === "vehiculo"),
        tipoauto: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["tipoauto"] }).filter(prod => getItemType(prod) === "vehiculo"),
        color: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["color"] }).filter(prod => getItemType(prod) === "vehiculo"),
        tapiceria: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["tapiceria"] }).filter(prod => getItemType(prod) === "vehiculo"),
        climatizacion: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["climatizacion"] }).filter(prod => getItemType(prod) === "vehiculo"),
        referencia: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["referencia"] }).filter(prod => getItemType(prod) === "vehiculo"),
        anio: getFilteredDataForOptionRefresh(productosData, { ignoreGroups: ["anio"] }).filter(prod => getItemType(prod) === "vehiculo")
    };

    const precios = (locDataTipo || [])
        .map(p => Number(p.precio_referencia))
        .filter(n => Number.isFinite(n) && n > 0);

    const maxPrecio = precios.length ? Math.max(...precios) : 0;
    inicializarSlider(maxPrecio);

    const marcasDataset = tipoFiltro === "vehiculo" ? vehicleDatasets.marca : locDataBase;
    const modelosDataset = tipoFiltro === "vehiculo" ? vehicleDatasets.modelo : locDataTipo;
    validarMarcaModeloVigentes(marcasDataset);
    buildMarcasYModelos(marcasDataset, modelosDataset);

    catsIndex = buildCatsAndSubcatsFromProductos(locDataCategorias);
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

    const showCategoryFilters = tipoFiltro !== "vehiculo" && categoriasFiltradas.length > 0;
    $("#filtro-categorias").closest(".accordion-item").toggle(showCategoryFilters);
    $("#subcats-box").toggle(tipoFiltro !== "vehiculo" && subcats.length > 0);
    buildVehicleFilters(locVehiculos, vehicleDatasets);
    buildServiceFilters(serviceOptionsData, locServicios);
    updateVehicleFilterVisibility();
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
    climatizacionSeleccionadas = climatizacionSeleccionadas.filter(value => climatizaciones.some(item => normalizarTexto(item) === normalizarTexto(value)));
    referenciasSeleccionadas = referenciasSeleccionadas.filter(value => referencias.some(item => normalizarTexto(item) === normalizarTexto(value)));

    renderVehicleCheckboxGroup("#filtro-condicion", "checkbox-condicion", condiciones, condicionSeleccionadas);
    renderVehicleCheckboxGroup("#filtro-tipo-auto", "checkbox-tipo-auto", tiposAuto, tipoAutoSeleccionados);
    renderVehicleCheckboxGroup("#filtro-color", "checkbox-color", colores, colorSeleccionados);
    renderVehicleCheckboxGroup("#filtro-tapiceria", "checkbox-tapiceria", tapicerias, tapiceriaSeleccionados);
    renderVehicleCheckboxGroup("#filtro-climatizacion", "checkbox-climatizacion", climatizaciones, climatizacionSeleccionadas);
    renderVehicleCheckboxGroup("#filtro-referencias", "checkbox-referencia", referencias, referenciasSeleccionadas);

    const years = (optionDatasets.anio || dataset).map(item => Number(item.anio || 0)).filter(year => Number.isFinite(year) && year > 0);
    const minYear = years.length ? Math.min(...years) : "";
    const maxYear = years.length ? Math.max(...years) : "";
    $("#anioMin").attr("min", minYear || 1900).attr("max", maxYear || 2100);
    $("#anioMax").attr("min", minYear || 1900).attr("max", maxYear || 2100);
}

function buildServiceFilters(optionDataset, selectedDataset) {
    const nombres = uniqueSorted((optionDataset || []).flatMap(getServiceNameOptions));
    nombresServiciosSeleccionados = nombresServiciosSeleccionados.filter(value =>
        nombres.some(item => normalizarTexto(item) === normalizarTexto(value))
    );
    renderServicioCheckboxGroup("#filtro-nombre-servicio", "checkbox-nombre-servicio", nombres, nombresServiciosSeleccionados);
    toggleFilterBlock("#filtro-nombre-servicio", tipoFiltro === "servicio" && nombres.length > 0);
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
    const $wrap = $(contentSelector).closest('.accordion-item');
    if ($wrap.length) $wrap.toggle(!!hasItems);
}

function tipoPrimeraCategoria(prod) {
    if (prod?.tipo_item === "vehiculo" || prod?.id_vehiculo) return "vehiculo";
    const catObjs = Array.isArray(prod.categorias) ? prod.categorias : [];
    const ids = parseIdsArray(prod.categoria ?? prod.id_categoria);
    if (!ids.length) return null;
    const firstId = Number(ids[0]);
    const first = catObjs.find(c => Number(c.id) === firstId);
    return first?.tipo || null; // 'producto' | 'servicio' | null
}

function getDetalleUrl(prod) {
    const tipo = getItemType(prod);
    const base = tipo === 'vehiculo' ? 'detalle_vehiculo.php' : 'detalle_productos.php';
    return `${base}?q=${getItemId(prod)}`;
}

function getTituloProductoOServicio(prod) {
    if (getItemType(prod) === "vehiculo") {
        const marca = getMarcaNombres(prod)[0] || prod.marca_nombre || "";
        const modelo = getModeloNombres(prod)[0] || prod.modelo_nombre || "";
        return [marca, modelo].filter(Boolean).join(" ") || prod.descripcion || "Vehículo";
    }
    return prod.nombre || prod.titulo_producto || 'Sin título';
}

function getItemType(prod) {
    if (prod?.tipo_item === "vehiculo" || prod?.id_vehiculo) return "vehiculo";
    const tipo = tipoPrimeraCategoria(prod);
    if (tipo === "servicio" || tipo === "producto") return tipo;
    return "producto";
}

function getItemId(prod) {
    return prod?.id_vehiculo || prod?.id_producto || "";
}

function normalizarItemsEmpresa(response) {
    const productosServicios = Array.isArray(response?.data) ? response.data.map(item => ({
        ...item,
        tipo_item: tipoPrimeraCategoria(item) || "producto"
    })) : [];
    const vehiculos = Array.isArray(response?.vehiculos) ? response.vehiculos.map(item => ({
        ...item,
        tipo_item: "vehiculo"
    })) : [];
    return [...productosServicios, ...vehiculos];
}

function getMarcaNombres(prod) {
    const nombres = (Array.isArray(prod?.marca) ? prod.marca : [])
        .map(item => capitalizarPrimeraLetra(item?.nombre))
        .filter(Boolean);
    if (!nombres.length && prod?.marca_nombre) nombres.push(capitalizarPrimeraLetra(prod.marca_nombre));
    return nombres;
}

function getModeloNombres(prod) {
    const fuenteModelo = Array.isArray(prod?.modelo) ? prod.modelo
        : Array.isArray(prod?.modelo_productoo) ? prod.modelo_productoo
            : Array.isArray(prod?.modelo_producto) ? prod.modelo_producto
                : [];
    const nombres = fuenteModelo
        .map(item => capitalizarPrimeraLetra(item?.nombre))
        .filter(Boolean);
    if (!nombres.length && prod?.modelo_nombre) nombres.push(capitalizarPrimeraLetra(prod.modelo_nombre));
    return nombres;
}

function normalizeToNameArray(val) {
    if (val == null) return [];
    if (Array.isArray(val)) {
        return val.flatMap(v => {
            if (typeof v === 'string') return [v.trim()];
            if (typeof v === 'number') return [String(v)];
            if (typeof v === 'object' && v !== null) {
                if ('nombre' in v) return [String(v.nombre || '').trim()];
                if ('name' in v) return [String(v.name || '').trim()];
            }
            return [];
        }).filter(Boolean);
    }
    if (typeof val === 'object') {
        if ('nombre' in val) return [String(val.nombre || '').trim()].filter(Boolean);
        if ('name' in val) return [String(val.name || '').trim()].filter(Boolean);
    }
    if (typeof val === 'string') {
        try {
            const parsed = JSON.parse(val);
            return normalizeToNameArray(parsed);
        } catch (_) { }
        return val.replace(/^\[|\]$/g, '').replace(/^"+|"+$/g, '').replace(/^'+|'+$/g, '').split(',').map(s => s.trim()).filter(Boolean);
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

function collectCheckedValues(name) {
    return $(`input[name='${name}']:checked`).map(function () { return $(this).val(); }).get();
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
        ...(Array.isArray(prod?.tipo_autoo) ? prod.tipo_autoo.map(item => item?.nombre).filter(Boolean) : []),
        ...normalizeToNameArray(prod?.tipo_auto)
    ]);
}

function getVehicleColorNames(prod) {
    return uniqueSorted([
        ...normalizeToNameArray(prod?.color),
        ...(Array.isArray(prod?.colorr) ? prod.colorr.map(item => item?.nombre).filter(Boolean) : [])
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
