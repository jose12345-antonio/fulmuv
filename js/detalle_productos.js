var id_producto = $("#id_producto").val()
let subcategoriasSeleccionadas = [];

function formatPrecioSuperscript(valor) {
    const num = parseFloat(valor) || 0;
    const entero = Math.floor(num);
    const decimales = Math.round((num - entero) * 100).toString().padStart(2, '0');
    return `<span style="font-size:0.6em;vertical-align:super;">US$</span><strong>${entero.toLocaleString('es-EC')}</strong><sup style="font-size:0.6em;top:-0.4em;">${decimales}</sup>`;
}
let vistaDetalleRegistrada = false;
let tipoInteraccionGlobal = "producto";

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

function registrarInteraccionProducto(tipoEvento, payload = {}) {
    if (!id_producto || !tipoEvento) {
        return;
    }

    $.ajax({
        url: "api/track_producto_interaccion.php",
        method: "POST",
        dataType: "json",
        data: {
            id_producto: id_producto,
            tipo_evento: tipoEvento,
            cantidad: payload.cantidad || 1,
            session_key: obtenerSessionKeyTracking(),
            detalle_url: window.location.href,
            referencia: document.referrer || "",
            metadata: JSON.stringify(payload.metadata || {}),
            tipo: payload.tipo || "producto",
            id_usuario: obtenerIdUsuarioTracking()
        }
    }).fail(function () {
        console.warn("No se pudo registrar la interacción del producto");
    });
}

function safeCompanyText(value) {
    return (value || "").toString().trim();
}

function normalizarUrlRed(value) {
    const raw = safeCompanyText(value);
    if (!raw) return "";
    if (/^https?:\/\//i.test(raw)) return raw;
    return `https://${raw}`;
}

function normalizarNombreMembresia(nombre) {
    return (nombre || "")
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
        .toLowerCase();
}

function obtenerNombreMarcaProducto(producto) {
    const marcaProducto = producto?.marca_productos;
    if (Array.isArray(marcaProducto) && marcaProducto.length) {
        const nombre = marcaProducto
            .map(item => item?.nombre || item?.referencia || "")
            .find(Boolean);
        if (nombre) return nombre;
    }

    if (typeof marcaProducto === "object" && marcaProducto) {
        return marcaProducto.nombre || marcaProducto.referencia || "";
    }

    return producto?.marca_producto || "";
}

function buildCompanySocialLinksHtml(empresa) {
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

function aplicarMensajeDetalleSegunPlan(producto, whatsappDisponible) {
    const tipoProducto = String(producto?.tipo_producto || "").toLowerCase().trim();
    const esServicio = tipoProducto === "servicio";
    const nombreEmpresa = formatearTextoDetalle(producto?.empresa?.nombre || "la empresa");
    const nombreMembresia = normalizarNombreMembresia(producto?.empresa?.membresia?.nombre);
    const restringeEntrega = !esServicio && (nombreMembresia === "onemuv" || nombreMembresia === "basicmuv");

    const $titulo = $("#tituloEntregaProducto");
    const $wrap = $("#mensajeEntregaProductoWrap");
    const $texto = $("#mensajeEntregaProducto");
    const $btnWhatsapp = $("#btnWhatsAppEntrega");
    const $acciones = $("#accionesCompraProducto");

    if (esServicio) {
        $titulo.html("Coordinación del servicio");
        $texto.html(`Para contratar este servicio debe comunicarse por medio de WhatsApp con ${nombreEmpresa}.`);
        $wrap.removeClass("d-none");
        $acciones.addClass("d-none");
        if (whatsappDisponible) {
            $btnWhatsapp.removeClass("d-none");
        } else {
            $btnWhatsapp.addClass("d-none");
        }
        return;
    }

    if (restringeEntrega) {
        $titulo.html("Coordinación del producto");
        $texto.html(`Este producto no tiene envío a domicilio con GRUPO ENTREGAS ni retiro en la empresa ${nombreEmpresa}, pero puede comunicarse por medio de WhatsApp.`);
        $wrap.removeClass("d-none");
        $acciones.addClass("d-none");
        if (whatsappDisponible) {
            $btnWhatsapp.removeClass("d-none");
        } else {
            $btnWhatsapp.addClass("d-none");
        }
        return;
    }

    $titulo.html("FULMUV te envía a domicilio");
    $texto.empty();
    $wrap.addClass("d-none");
    $btnWhatsapp.addClass("d-none");
    $acciones.removeClass("d-none");
}


$(document).ready(function () {
    // localStorage.removeItem("carrito");
    actualizarIconoCarrito();
    $("#breadcrumb")?.append(`<a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a><span></span> Detalle del producto`);


    $.get("api/v1/fulmuv/categoriasPrincipales/All", function (returnedDataCategoria) {
        if (!returnedDataCategoria.error) {
            console.log(returnedDataCategoria)
            categoriasOriginales = returnedDataCategoria.data;
            console.log(categoriasOriginales)
            renderizarCategorias();
        }
    }, 'json')

    $.get("api/v1/fulmuv/productos/" + id_producto, function (returnedData) {

        if (!returnedData.error) {
            const tipoProducto = String(returnedData?.data?.tipo_producto || "").toLowerCase().trim();
            const esServicio = tipoProducto === "servicio";
            const tipoInteraccion = esServicio ? "servicio" : "producto";
            tipoInteraccionGlobal = tipoInteraccion;


            // âœ… IVA / NEGOCIABLE debajo del precio
            $("#flagsPrecio").empty();

            const iva = parseInt(returnedData?.data?.iva || 0);
            const negociable = parseInt(returnedData?.data?.negociable || 0);

            if (iva === 1) {
                $("#flagsPrecio").append(`<span class="price-flag iva">IVA incluido</span>`);
            }

            if (negociable === 1) {
                $("#flagsPrecio").append(`<span class="price-flag neg">Precio negociable</span>`);
            }


            const tieneDescuento = parseFloat(returnedData.data.descuento) > 0;
            const precioDescuento = returnedData.data.precio_referencia - (returnedData.data.precio_referencia * returnedData.data.descuento / 100);

            $(".title-detail").text(capitalizarPrimeraLetra(returnedData.data.titulo_producto))
            $(".current-price").html(formatPrecioSuperscript(tieneDescuento ? precioDescuento : returnedData.data.precio_referencia))

            if (returnedData.data.descuento != 0) {

                $("#listDescuento").removeClass("d-none")
                $("#valorporcentaje").text(returnedData.data.descuento + "% de descuento")
                $("#valorDescuento").text(tieneDescuento ? formatoMoneda.format(returnedData.data.precio_referencia) : '' + "% de descuento")
            }
            function decodeHtml(str = "") {
                const txt = document.createElement("textarea");
                txt.innerHTML = str;
                return txt.value;
            }

            const raw = returnedData?.data?.descripcion || "";

            // 1ª pasada de decodificación
            let html = decodeHtml(raw);

            // Si aún quedan entidades (&lt; &gt;), decodifica una vez más (doble-escapado)
            if (/&lt;|&gt;|&amp;/.test(html)) {
                html = decodeHtml(html);
            }

            // (Opcional) quitar tags peligrosos básicos
            html = html.replace(/<(script|style|iframe)[^>]*>[\s\S]*?<\/\1>/gi, "");

            // Inyectar como HTML real
            $("#descripcionProducto").html(html);

            // MARCA(S)
            const marcasTxt = formatearTextoDetalle(obtenerNombreMarcaProducto(returnedData.data));
            $("#marcaProducto").text(marcasTxt || "-");

            // MODELO(S) (a veces viene como objeto, otras como array "modelo_productoo")
            const modelosRaw = returnedData.data.modelo_productoo || returnedData.data.modelo_producto;
            const modelosTxt = formatearTextoDetalle(joinNombres(modelosRaw));
            $("#modeloProducto").text(modelosTxt || "-");

            const categoriasTxt = formatearTextoDetalle(
                joinNombres(returnedData.data.categorias) || returnedData.data.nombre_categoria || ""
            );
            $("#categoriaProducto").text(categoriasTxt || "-");

            const subcategoriasTxt = formatearTextoDetalle(
                joinNombres(returnedData.data.subcategorias || returnedData.data.sub_categorias) || returnedData.data.nombre_sub_categoria || ""
            );
            $("#subcategoriaProducto").text(subcategoriasTxt || "-");
            $("#subCategoriasProductos").text(subcategoriasTxt || "-");
            var verificacion = "";
            if (returnedData.data.verificacion.length != 0) {
                if (returnedData.data.verificacion[0].verificado == 1) {
                    verificacion = `
                        <img src="img/verificado_empresa.png" 
                            alt="Verificado" 
                            title="Empresa verificada" 
                            style="width:36px;height:36px;margin-left:8px;vertical-align:middle;">
                    `;
                }
            }

            const nombreEmpresaTienda = formatearTextoDetalle(returnedData.data?.empresa?.nombre || "la empresa");
            const nombreMembresiaEmpresa = normalizarNombreMembresia(returnedData.data?.empresa?.membresia?.nombre);
            const restringeTienda = nombreMembresiaEmpresa === "onemuv" || nombreMembresiaEmpresa === "basicmuv";

            if (restringeTienda) {
                $("#viewVisitaTienda").html(`
                    <div class="fw-bold text-muted">
                        El vendedor ${nombreEmpresaTienda} no dispone de una tienda en FULMUV
                    </div>
                `);
            } else {
                $("#viewVisitaTienda").html(`
                    <div class="d-inline-flex align-items-center">
                        <a href="productos_vendor.php?q=${returnedData.data.empresa.id_empresa}" 
                        class="fw-bold text-primary text-decoration-none">
                        Visitar la tienda ${nombreEmpresaTienda}
                        </a>
                        ${verificacion}
                    </div>
                `);
            }
            // === WhatsApp ===
            const p = returnedData?.data || {};

            const telRaw = (p.empresa.telefono_contacto || "").toString().trim();
            const waRaw = (p.empresa.whatsapp_contacto || "").toString().trim();

            // formateo simple para mostrar bonito (opcional)
            const prettyPhone = (s) => s ? s.replace(/\s+/g, " ") : "No disponible";

            // links
            const telDigits = telRaw.replace(/\D/g, "");
            const telLink = telDigits ? `tel:${telDigits}` : "#";

            let waDigits = waRaw.replace(/\D/g, "");
            if (waDigits.length === 10 && waDigits.startsWith("0")) waDigits = "593" + waDigits.slice(1);
            if (waDigits.startsWith("5930")) waDigits = "593" + waDigits.slice(4);

            const waChat = waDigits ? `https://wa.me/${waDigits}` : "#";

            // render
            $("#contactosEmpresa").html(`
            <div class="contact-line">
                <span class="icon-circle icon-tel">
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.07 21 3 13.93 3 5a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.46.57 3.58a1 1 0 0 1-.24 1.01l-2.21 2.2Z"/>
                </svg>
                </span>
                <span>Teléfono:</span>
                ${telDigits ? `<a class="contact-phone" href="${telLink}">${prettyPhone(telRaw)}</a>` : `<span class="text-muted">No disponible</span>`}
            </div>

            <div class="contact-line">
                <span class="icon-circle icon-wa">
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 32 32">
                    <path fill="currentColor" d="M19.11 17.05c-.27-.14-1.61-.79-1.86-.88c-.25-.09-.43-.14-.61.14c-.18.27-.7.88-.86 1.06c-.16.18-.32.2-.59.07c-.27-.14-1.15-.42-2.19-1.34c-.81-.72-1.36-1.6-1.52-1.87c-.16-.27-.02-.41.12-.55c.12-.12.27-.32.41-.48c.14-.16.18-.27.27-.45c.09-.18.05-.34-.02-.48c-.07-.14-.61-1.47-.83-2.01c-.22-.53-.44-.46-.61-.46h-.52c-.18 0-.48.07-.73.34c-.25.27-.96.94-.96 2.3c0 1.36.98 2.67 1.11 2.85c.14.18 1.93 2.95 4.68 4.14c.65.28 1.16.45 1.55.57c.65.21 1.24.18 1.71.11c.52-.08 1.61-.66 1.84-1.3c.23-.64.23-1.19.16-1.3c-.07-.11-.25-.18-.52-.32zM16 3C8.83 3 3 8.83 3 16c0 2.29.62 4.44 1.7 6.28L3 29l6.9-1.81A12.93 12.93 0 0 0 16 29c7.17 0 13-5.83 13-13S23.17 3 16 3z"/>
                </svg>
                </span>
                <span>WhatsApp:</span>
                ${waDigits ? `<a class="contact-whatsapp" href="${waChat}" target="_blank" rel="noopener">${prettyPhone(waRaw)}</a>` : `<span class="text-muted">No disponible</span>`}
            </div>
            `);

            const productId = p.id_producto;
            const detailUrl = new URL(`detalle_productos.php?q=${encodeURIComponent(productId)}`, location.origin).href;

            const phoneRaw = (p.empresa.whatsapp_contacto || p.empresa.telefono_contacto || "").toString().trim();

            // solo dígitos
            let phone = phoneRaw.replace(/\D/g, "");

            // NORMALIZAR ECUADOR (si viene como 09xxxxxxxx)
            if (phone.length === 10 && phone.startsWith("0")) {
                phone = "593" + phone.slice(1); // 0 + 9 dígitos -> 593 + 9 dígitos
            }

            // Si viene con 593 pero con 0 extra (ej: 59309xxxxxxx)
            if (phone.startsWith("5930")) {
                phone = "593" + phone.slice(4);
            }

            const waText = `Hola, me interesa este producto:\n${detailUrl}`;
            const textEnc = encodeURIComponent(waText);

            // Links correctos
            const waWeb = `https://web.whatsapp.com/send?phone=${phone}&text=${textEnc}`;
            const waApi = `https://api.whatsapp.com/send?phone=${phone}&text=${textEnc}`; // fallback
            const waDeep = `whatsapp://send?phone=${phone}&text=${textEnc}`;              // móvil (intento)

            const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

            $('#btnWhatsApp').attr({
                href: isMobile ? waDeep : waWeb,
                target: '_blank',
                rel: 'noopener'
            });

            $('#btnWhatsAppEntrega').attr({
                href: isMobile ? waDeep : waWeb,
                target: '_blank',
                rel: 'noopener'
            });

            aplicarMensajeDetalleSegunPlan(p, Boolean(phone));

            $('#btnWhatsApp').off('click.track').on('click.track', function () {
                registrarInteraccionProducto("click_whatsapp", {
                    tipo: tipoInteraccion,
                    metadata: { origen: "detalle_producto" }
                });
            });

            $('#btnWhatsAppEntrega').off('click.track').on('click.track', function () {
                registrarInteraccionProducto("click_whatsapp", {
                    tipo: tipoInteraccion,
                    metadata: { origen: "detalle_producto_aviso" }
                });
            });

            $('#contactosEmpresa').off('click.trackPhone').on('click.trackPhone', 'a.contact-phone', function () {
                registrarInteraccionProducto("click_telefono", {
                    tipo: tipoInteraccion,
                    metadata: { origen: "detalle_producto" }
                });
            });

            $('#contactosEmpresa').off('click.trackWa').on('click.trackWa', 'a.contact-whatsapp', function () {
                registrarInteraccionProducto("click_whatsapp", {
                    tipo: tipoInteraccion,
                    metadata: { origen: "detalle_producto" }
                });
            });

            // Fallback si el deep link no abre (algunos navegadores lo bloquean)
            if (isMobile) {
                $('#btnWhatsApp').off('click.whatsFallback').on('click.whatsFallback', function (e) {
                    e.preventDefault();
                    window.location.href = waDeep;
                    setTimeout(() => window.open(waApi, '_blank', 'noopener'), 700);
                });

                $('#btnWhatsAppEntrega').off('click.whatsFallback').on('click.whatsFallback', function (e) {
                    e.preventDefault();
                    window.location.href = waDeep;
                    setTimeout(() => window.open(waApi, '_blank', 'noopener'), 700);
                });
            }


            setMapCoords(p.empresa.latitud, p.empresa.longitud);



            $(".product-image-slider").empty(); // Limpia antes de agregar
            $(".slider-nav-thumbnails").empty();

            $(".product-image-slider").append(`
                <a href="admin/${returnedData.data.img_frontal}"
                data-fancybox="galeria-producto">
                    <img src="admin/${returnedData.data.img_frontal}"
                        onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" />
                </a>
            `);

            $(".slider-nav-thumbnails").append(`
                <div>
                    <img src="admin/${returnedData.data.img_frontal}" alt="product image"
                        onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" />
                </div>
            `);


            $(".product-image-slider").append(`
            <a href="admin/${returnedData.data.img_posterior}"
            data-fancybox="galeria-producto">
                <img src="admin/${returnedData.data.img_posterior}"
                    onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" />
            </a>
        `);

            $(".slider-nav-thumbnails").append(`
                <div>
                    <img src="admin/${returnedData.data.img_posterior}" alt="product image"
                        onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" />
                </div>
            `);


            // Otras imágenes (archivos adicionales)
            returnedData.data.archivos.forEach(function (imagen, index) {
                $(".product-image-slider").append(`
                    <a href="admin/${imagen.archivo}"
                    data-fancybox="galeria-producto">
                        <img src="admin/${imagen.archivo}"
                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" />
                    </a>
                `);

                $(".slider-nav-thumbnails").append(`
                    <div>
                        <img src="admin/${imagen.archivo}" alt="product image"
                            style="width: 115px; height: 115px; object-fit: cover"
                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" />
                    </div>
                `);
            });

            // Después de agregar dinámicamente las imágenes
            $('.product-image-slider').slick('unslick'); // Opcional si ya estaba inicializado
            $('.product-image-slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                fade: true,
                asNavFor: '.slider-nav-thumbnails',
                prevArrow: '<button type="button" class="slick-prev"><i class="fi-rs-angle-left"></i></button>',
                nextArrow: '<button type="button" class="slick-next"><i class="fi-rs-angle-right"></i></button>',
            });


            // // Aplicar zoom después de que el DOM esté listo y el slider se haya montado
            // mediumZoom('.product-image-slider img', {
            //     background: 'rgba(0, 0, 0, 0.8)',
            //     scrollOffset: 0,
            // });

            $('.slider-nav-thumbnails').slick('unslick'); // Opcional
            $('.slider-nav-thumbnails').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                asNavFor: '.product-image-slider',
                dots: false,
                focusOnSelect: true,
                prevArrow: '<button type="button" class="slick-prev"><i class="fi-rs-angle-small-left"></i></button>',
                nextArrow: '<button type="button" class="slick-next"><i class="fi-rs-angle-small-right"></i></button>',
            });

            // const lightbox = GLightbox({
            //     selector: '.glightbox-product',
            //     loop: true,          // permite repetir al final
            //     touchNavigation: true,
            //     closeOnOutsideClick: true,
            //     autoplayVideos: false
            // });

            // Inicializar Fancybox para la galería del producto
            Fancybox.bind('[data-fancybox="galeria-producto"]', {
                Thumbs: {
                    autoStart: true   // Muestra la minigalería inferior al abrir
                },
                Toolbar: {
                    display: [
                        "close"
                    ]
                },
                infinite: true,       // Permite seguir pasando al llegar a la última
            });

            $.post("api/v1/fulmuv/categorias/productos", {
                id_categoria: returnedData.data.id_categoria,
                id_empresa: returnedData.data.id_empresa
            }, function (returnedDataProducto) {

                function initRelacionadosSlick(container) {
                    container.slick({
                        dots: false,
                        infinite: true,
                        speed: 1000,
                        arrows: true,
                        autoplay: true,
                        slidesToShow: 5,
                        slidesToScroll: 1,
                        prevArrow: '<div class="slider-btn slider-prev"><i class="fi-rs-arrow-small-left"></i></div>',
                        nextArrow: '<div class="slider-btn slider-next"><i class="fi-rs-arrow-small-right"></i></div>',
                        responsive: [
                            { breakpoint: 1200, settings: { slidesToShow: 4 } },
                            { breakpoint: 1025, settings: { slidesToShow: 3 } },
                            { breakpoint: 480, settings: { slidesToShow: 1 } }
                        ],
                        appendArrows: "#carausel-4-columns-oferta"
                    });
                }

                function renderProductosRelacionados(lista) {
                    let container = $("#carausel-4-columns-oferta");
                    if (container.hasClass('slick-initialized')) container.slick('unslick');
                    container.empty();

                    lista.forEach(function (producto) {
                        const tieneDescuento = parseFloat(producto.descuento) > 0;
                        const precioRef = parseFloat(producto.precio_referencia) || 0;
                        const precioDesc = precioRef - (precioRef * (parseFloat(producto.descuento) || 0) / 100);
                        let verificacionHtml = "";
                        if (Array.isArray(producto.verificacion) && producto.verificacion.length > 0) {
                            if (producto.verificacion[0].verificado == 1) {
                                verificacionHtml = `<span class="fw-bold product-category" style="font-size: 12px;"><i class="fi-rs-check ms-1"></i> Vendedor Verificado</span>`;
                            }
                        }
                        container.append(`
                            <div class="product-cart-wrap">
                                <div class="product-img-action-wrap">
                                    <div class="product-img product-img-zoom">
                                        <a onclick="irADetalleProductoConTerminos(${producto.id_producto}); return false;">
                                            <img class="default-img" src="admin/${producto.img_frontal}" alt=""
                                                onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"
                                                style="height: 150px; object-fit: contain;" />
                                        </a>
                                    </div>
                                    ${tieneDescuento ? `<div class="product-badges product-badges-position product-badges-mrg"><span class="best">-${parseInt(producto.descuento)}%</span></div>` : ''}
                                </div>
                                <div class="product-content-wrap p-1">
                                    <div class="text-end">${verificacionHtml}</div>
                                    <h2 class="text-center">
                                        <a onclick="irADetalleProductoConTerminos(${producto.id_producto}); return false;" class="limitar-lineas mt-1" style="font-weight:700;">
                                            ${capitalizarPrimeraLetra(producto.titulo_producto)}
                                        </a>
                                    </h2>
                                    <div class="product-price mb-2 mt-0 text-center">
                                        <span>${formatPrecioSuperscript(tieneDescuento ? precioDesc : precioRef)}</span>
                                        ${tieneDescuento ? `<span class="old-price">${formatoMoneda.format(precioRef)}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                        `);
                    });

                    initRelacionadosSlick(container);
                }

                const relacionados = (returnedDataProducto.data || []).filter(p => String(p.id_producto) !== String(id_producto));
                const needed = 6 - relacionados.length;

                if (needed <= 0) {
                    renderProductosRelacionados(relacionados);
                } else {
                    $.get("api/v1/fulmuv/productosAll/all", function (fallback) {
                        const existingIds = new Set(relacionados.map(p => String(p.id_producto)));
                        existingIds.add(String(id_producto));
                        let extra = (fallback.data || []).filter(p => !existingIds.has(String(p.id_producto)));
                        for (let i = extra.length - 1; i > 0; i--) {
                            const j = Math.floor(Math.random() * (i + 1));
                            [extra[i], extra[j]] = [extra[j], extra[i]];
                        }
                        renderProductosRelacionados([...relacionados, ...extra.slice(0, needed)]);
                    }, 'json');
                }
            }, 'json');


            $(document).on("click", ".button-add-to-cart", function () {
                const productoId = returnedData.data.id_producto;
                const cantidad = parseInt($(".qty-val").val());
                const nombre = returnedData.data.titulo_producto;
                const precioReferencial = parseFloat(returnedData.data.precio_referencia);
                const descuento = parseFloat(returnedData.data.descuento || 0);
                const descripcion = returnedData.data.descripcion;
                const nombre_categoria = returnedData.data.nombre_categoria;
                const tags = returnedData.data.tags;
                const codigo = returnedData.data.codigo;
                const peso = returnedData.data.peso;
                const precioConDescuento = descuento > 0
                    ? precioReferencial - (precioReferencial * descuento / 100)
                    : precioReferencial;
                const img = ("admin/" + returnedData.data.img_frontal) || "img/FULMUV_LOGO-13.png";
                const id_empresa = returnedData.data.id_empresa;
                const tipo_empresa_sucursal = returnedData.data.empresa.tipo;

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
                    toastr.warning(`"${nombre}" ya estaba en el carrito. Se agregaron ${cantidad} productos más.`, 'Fulmuv', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 1000
                    });
                } else {
                    carrito.push({
                        id: productoId,
                        nombre: nombre,
                        precio: precioReferencial,
                        valor_descuento: precioConDescuento,
                        descuento: descuento,
                        cantidad: cantidad,
                        imagen: img,
                        descripcion: descripcion,
                        nombre_categoria: nombre_categoria,
                        tags: tags,
                        codigo: codigo,
                        peso: peso,
                        id_empresa: id_empresa,
                        total_pagado: 0,
                        tipo: tipo_empresa_sucursal,
                        iva: returnedData.data.iva,
                        iva: returnedData.data.negociable
                    });

                    console.log(carrito)

                    toastr.success(`"${nombre}" agregado al carrito`, 'Fulmuv', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 1000
                    });
                }

                localStorage.setItem("carrito", JSON.stringify({
                    data: carrito,
                    timestamp: new Date().getTime()
                }));

                registrarInteraccionProducto("agregar_carrito", {
                    tipo: tipoInteraccion,
                    cantidad: cantidad,
                    metadata: { origen: "detalle_producto" }
                });

                actualizarIconoCarrito();
            });






            var imgPath = returnedData.data.empresa.img_path;

            var imgEmpr = imgPath
                ? `empresa/${imgPath}`
                : "img/FULMUV-NEGRO.png";

            $("#imagenEmpresa").attr("src", imgEmpr);

            $("#nombreEmpresa").text(returnedData.data.empresa.nombre)
            $("#direccionEmpresa").text(returnedData.data.empresa.direccion)
            $("#telefonoEmpresa").text(returnedData.data.empresa.telefono_contacto)
            $("#descripcionEmpresa").text(safeCompanyText(returnedData.data.empresa.descripcion) || "Esta empresa no ha agregado una descripción todavía.")
            $("#empresaSocialLinks").html(buildCompanySocialLinksHtml(returnedData.data.empresa))

            $("#Description").append(`
                <span class="hot">${returnedData.data.descripcion}</span>
            `)


            $("#nombre_subcategoria").text(capitalizarPrimeraLetra(returnedData.data.nombre_sub_categoria))
            $("#fechaCreado").text(returnedData.data.created_at)
            $("#codigoProducto").text(returnedData.data.codigo)
            $("#etiquetasProducto").text(capitalizarPrimeraLetra(returnedData.data.tags))

            let jsonArray = [];
            const detRaw = returnedData.data.detalle_producto;

            try {
                if (detRaw && detRaw !== 'null' && detRaw !== '[]') {
                    jsonArray = JSON.parse(detRaw);
                }
            } catch (e) {
                jsonArray = [];
            }

            if (!Array.isArray(jsonArray) || jsonArray.length === 0) {
                $("#Additional-info").html(
                    `<p class="text-muted">No se ingresó información adicional.</p>`
                );
            } else {
                let listTable = "";
                jsonArray.forEach((item, index) => {
                    const label = (item.label || '').toString().trim();
                    const valor = (item.valor ?? '').toString();
                    const fondo = index % 2 === 0 ? "#f8f9fa" : "#ffffff";
                    listTable += `
                        <tr class="stand-up" style="background-color:${fondo};">
                            <th style="color:#000">${label}</th>
                            <td><p style="color:#000">${valor}</p></td>
                        </tr>`;
                });

                $("#Additional-info").html(`
                        <table class="font-md">
                        <tbody id="listTableAdicional">
                            ${listTable}
                        </tbody>
                        </table>
                    `);
            }

        }

    }, 'json')



})


function renderizarCategorias() {
    let html = '';

    categoriasOriginales.forEach(cat => {
        html += `
            <li class="p-1">
                <a href="productos_categoria.php?q=${cat.id_categoria_principal}">
                    <img src="admin/${cat.icono}" alt="${capitalizarPrimeraLetra(cat.nombre)}" onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';">
                    ${capitalizarPrimeraLetra(cat.nombre)}
                </a>
                <span class="count">${cat.total_producto_categoria}</span>
            </li>
        `;
    });

    $(".widget-category-2 ul").html(html);
}

let map, marker, mapsReady = false;
let pendingCoords = null; // guardamos coords si llegan antes que la API

// Google cargará esto cuando termine (por callback=initMap)
window.initMap = function () {
    mapsReady = true;

    // Si ya tenemos coords pendientes, pintamos
    if (pendingCoords) {
        renderMap(pendingCoords.lat, pendingCoords.lng);
    }
};

function setMapCoords(lat, lng) {
    // ðŸ‘‡ importante: normaliza coma decimal si viniera " -2,2758 "
    const latNum = parseFloat(String(lat).replace(",", "."));
    const lngNum = parseFloat(String(lng).replace(",", "."));

    pendingCoords = (Number.isFinite(latNum) && Number.isFinite(lngNum))
        ? { lat: latNum, lng: lngNum }
        : { lat: -1.8312, lng: -78.1834 }; // fallback Ecuador

    if (mapsReady) renderMap(pendingCoords.lat, pendingCoords.lng);
}

function renderMap(lat, lng) {
    const center = { lat: Number(lat), lng: Number(lng) };

    // URL correcta para abrir en Maps externo
    const toMapsUrl = (latVal, lngVal) =>
        `https://www.google.com/maps/search/?api=1&query=${latVal},${lngVal}`;

    const abrirGoogleMaps = (latVal, lngVal) => {
        window.open(toMapsUrl(latVal, lngVal), "_blank", "noopener,noreferrer");
    };

    if (!map) {
        map = new google.maps.Map(document.getElementById("map"), {
            center,
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
        });

        marker = new google.maps.Marker({
            position: center,
            map,
            title: "Ubicación del vendedor",
        });

        // âœ… Click en el mapa: abre el punto exacto clickeado
        map.addListener("click", (e) => {
            if (!e?.latLng) return;
            registrarInteraccionProducto("click_mapa", {
                tipo: tipoInteraccionGlobal,
                metadata: { origen: "detalle_producto" }
            });
            abrirGoogleMaps(e.latLng.lat(), e.latLng.lng());
        });

        // Click en el marcador: abre la ubicación del marcador
        marker.addListener("click", () => {
            const pos = marker.getPosition();
            if (!pos) return;
            registrarInteraccionProducto("click_mapa", {
                tipo: tipoInteraccionGlobal,
                metadata: { origen: "detalle_producto" }
            });
            abrirGoogleMaps(pos.lat(), pos.lng());
        });

    } else {
        map.setCenter(center);
        map.setZoom(15);
        if (marker) marker.setPosition(center);
    }
}

// Si el mapa está dentro de una pestaña oculta (Bootstrap Tabs)
document.addEventListener("shown.bs.tab", (ev) => {
    if (ev.target && ev.target.getAttribute("href") === "#Vendor-info" && map) {
        google.maps.event.trigger(map, "resize");
        if (pendingCoords) map.setCenter(pendingCoords);
    }
});


// Convierte array de objetos [{nombre, referencia}] a "Nombre, Nombre2"
function joinNombres(arr) {
    if (!arr) return '';
    if (!Array.isArray(arr)) arr = [arr];
    return arr
        .map(o => (o?.nombre ?? '').toString().trim())
        .filter(Boolean)
        .join(', ');
}

function formatearTextoDetalle(valor) {
    const texto = (valor || '').toString().trim();
    if (!texto) return '';
    return window.fulmuvTitleCase ? window.fulmuvTitleCase(texto) : texto;
}

// Para tracciÃ³n: si hay referencia => "8X4 - FWD", si no hay => "8X4"
function joinNombresConReferencia(arr) {
    if (!arr) return '';
    if (!Array.isArray(arr)) arr = [arr];
    return arr
        .map(o => {
            const n = (o?.nombre ?? '').toString().trim();
            const r = (o?.referencia ?? '').toString().trim();
            return r ? `${n} - ${r}` : n;
        })
        .filter(Boolean)
        .join(', ');
}

// Imagen con fallback a Fulmuv
function setImgWithFallback($img, src, fallback = 'img/FULMUV-NEGRO.png') {
    if (!src) src = fallback;
    $img.attr('src', src);
    $img.off('error').on('error', function () {
        $(this).attr('src', fallback);
    });
}


