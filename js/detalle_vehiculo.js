var id_producto = $("#id_producto").val()
let subcategoriasSeleccionadas = [];

function formatPrecioSuperscript(valor) {
    const num = parseFloat(valor) || 0;
    const entero = Math.floor(num);
    const decimales = Math.round((num - entero) * 100).toString().padStart(2, '0');
    return `<span style="font-size:0.6em;vertical-align:super;">US$</span><strong>${entero.toLocaleString('es-EC')}</strong><sup style="font-size:0.6em;top:-0.4em;">${decimales}</sup>`;
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

function registrarInteraccionVehiculo(tipoEvento, payload = {}) {
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
            tipo: "vehiculo",
            id_usuario: obtenerIdUsuarioTracking()
        }
    }).fail(function () {
        console.warn("No se pudo registrar la interaccion del vehiculo");
    });
}

function registrarInteraccionVehiculoWhatsapp(origen) {
    registrarInteraccionVehiculo("click_whatsapp", { metadata: { origen: origen || "detalle_vehiculo" } });
}

function registrarInteraccionVehiculoTelefono(origen) {
    registrarInteraccionVehiculo("click_telefono", { metadata: { origen: origen || "detalle_vehiculo" } });
}

function registrarInteraccionVehiculoMapa(origen) {
    registrarInteraccionVehiculo("click_mapa", { metadata: { origen: origen || "detalle_vehiculo" } });
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

function formatearTextoDetalle(valor) {
    const texto = (valor || '').toString().trim();
    if (!texto) return '';
    return window.fulmuvTitleCase ? window.fulmuvTitleCase(texto) : texto;
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

$(document).ready(function () {
    // localStorage.removeItem("carrito");
    actualizarIconoCarrito();


    $.get("api/v1/fulmuv/categoriasPrincipales/All", function (returnedDataCategoria) {
        if (!returnedDataCategoria.error) {
            console.log(returnedDataCategoria)
            categoriasOriginales = returnedDataCategoria.data;
            console.log(categoriasOriginales)
            renderizarCategorias();
        }
    }, 'json')

    function asPlainText(val) {
        const arr = normalizeToNameArray(val);   // ya tienes esta función
        if (arr.length) return arr[0];           // usa el primero
        return cleanStr(val);                    // fallback
    }
    function cleanStr(s) { return (s || '').toString().trim(); }

    function normalizeToNameArray(val) {
        if (val == null) return [];
        if (Array.isArray(val)) {
            return val.flatMap(v => {
                if (typeof v === 'string') return [cleanStr(v)];
                if (typeof v === 'number') return [String(v)];
                if (typeof v === 'object' && v !== null) {
                    if ('nombre' in v) return [cleanStr(v.nombre)];
                    if ('name' in v) return [cleanStr(v.name)];
                    if ('id_color' in v && v.nombre) return [cleanStr(v.nombre)];
                }
                return [];
            });
        }
        if (typeof val === 'object') {
            if ('nombre' in val) return [cleanStr(val.nombre)];
            if ('name' in val) return [cleanStr(val.name)];
        }
        if (typeof val === 'string') {
            try { const j = JSON.parse(val); return normalizeToNameArray(j); } catch (_) { }
            return val.replace(/^\[|\]$/g, '').replace(/^"+|"+$/g, '').replace(/^'+|'+$/g, '')
                .split(',').map(s => cleanStr(s)).filter(Boolean);
        }
        return [];
    }
    $.post("api/v1/fulmuv/vehiculos/byIdVehiculo", { id_vehiculo: id_producto }, function (returnedData) {

        if (!returnedData.error) {
            const tieneDescuento = parseFloat(returnedData.data.descuento) > 0;
            const precioDescuento = returnedData.data.precio_referencia - (returnedData.data.precio_referencia * returnedData.data.descuento / 100);

            $(".title-detail").text(capitalizarPrimeraLetra(returnedData.data.nombre))
            $(".current-price").html(formatPrecioSuperscript(tieneDescuento ? precioDescuento : returnedData.data.precio_referencia))

            if (returnedData.data.descuento != 0) {

                $("#listDescuento").removeClass("d-none")
                $("#valorporcentaje").text(returnedData.data.descuento + "% de descuento")
                $("#valorDescuento").text(tieneDescuento ? formatoMoneda.format(returnedData.data.precio_referencia) : '' + "% de descuento")
            }

            // ✅ IVA / NEGOCIABLE debajo del precio
            $("#flagsPrecio").empty();

            const iva = parseInt(returnedData?.data?.iva || 0);
            const negociable = parseInt(returnedData?.data?.negociable || 0);

            if (iva === 1) {
                $("#flagsPrecio").append(`<span class="price-flag iva">IVA incluido</span>`);
            }

            if (negociable === 1) {
                $("#flagsPrecio").append(`<span class="price-flag neg">Precio negociable</span>`);
            }
            // function decodeHtml(str = "") {
            //     const txt = document.createElement("textarea");
            //     txt.innerHTML = str;
            //     return txt.value;
            // }

            // const raw = returnedData?.data?.descripcion || "";

            // // 1ª pasada de decodificación
            // let html = decodeHtml(raw);

            // // Si aún quedan entidades (&lt; &gt;), decodifica una vez más (doble-escapado)
            // if (/&lt;|&gt;|&amp;/.test(html)) {
            //     html = decodeHtml(html);
            // }

            // // (Opcional) quitar tags peligrosos básicos
            // html = html.replace(/<(script|style|iframe)[^>]*>[\s\S]*?<\/\1>/gi, "");

            // // Inyectar como HTML real
            // $("#descripcionProducto").html(html);

            // MARCA(S)
            const marcasTxt = joinNombres(returnedData.data.anio);
            $("#anioProducto").text(marcasTxt);

            // MODELO(S) (a veces viene como objeto, otras como array "modelo_productoo")
            const provinciaRaw = returnedData.data.provincia;
            const cantonRaw = returnedData.data.canton;
            const modelosTxt = asPlainText(provinciaRaw) + " - " + asPlainText(cantonRaw);
            $("#ciudadProducto").text(modelosTxt);

            // PESO
            $("#recorridoProducto").text(`${returnedData.data.kilometraje} kms`);

            // TIPO TRACCIÓN (usa "nombre - referencia" cuando exista referencia)
            const traccionTxt = joinNombresConReferencia(returnedData.data.tipo_fraccionn);
            // Si no hay referencia, nunca pondrá "undefined"
            $("#traccionProducto").text(traccionTxt);

            // TIPO AUTO (si necesitas mostrarlo también en algún lado)
            const tipoAutoTxt = joinNombres(returnedData.data.tipo_autoo);

            // $("#subCategoriasProductos").text(returnedData.data.nombre_sub_categoria)
            // $("#garantiaProducto").text(returnedData.data.empresa.garantia_ofrecida)
            // $("#viewVisitaTienda").append(`<a href="productos_vendor.php?q=${returnedData.data.empresa.id_empresa}" class="fw-bold">Visitar la tienda ${returnedData.data.empresa.nombre}</a>`)

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
                    <h6 class="fw-bold">Visitar la tienda</h6>
                    <a href="productos_vendor.php?q=${returnedData.data.empresa.id_empresa}"
                        class="btn btn-primary d-inline-flex align-items-center gap-2 mt-2">
                        
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M3 21V9l9-6l9 6v12h-7v-7H10v7H3Z"/>
                        </svg>

                        <span>${nombreEmpresaTienda}</span>
                    </a>
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


            const productId = p.id_vehiculo;
            const detailUrl = new URL(`detalle_vehiculo.php?q=${encodeURIComponent(productId)}`, location.origin).href;

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

            const waText = `Hola, me interesa este vehículo:\n${detailUrl}`;
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

            $('#btnWhatsApp').off('click').on('click', function (e) {
                registrarInteraccionVehiculoWhatsapp("detalle_vehiculo_btn");
                if (!isMobile) return;
                e.preventDefault();
                window.location.href = waDeep;
                setTimeout(() => window.open(waApi, '_blank', 'noopener'), 700);
            });

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


            if (returnedData.data.archivos.length != 0) {
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
                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';" />
                    </div>
                `);
                })
            }

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

            $.post("api/v1/fulmuv/categorias/vehiculosRelacionados", {
                id_modelo: returnedData.data.id_modelo,
                id_empresa: returnedData.data.id_empresa
            }, function (returnedDataProducto) {

                function buildVehiculoCard(vehiculo) {
                    const marca = vehiculo.marcaArray?.[0]?.nombre ?? "";
                    const modelo = vehiculo.modeloArray?.nombre ?? vehiculo.titulo_producto ?? "";
                    const anio = vehiculo.anio ?? "";
                    const prov = firstFromJsonLike(vehiculo.provincia);
                    const kms = formatKms(vehiculo.kilometraje);
                    const precioRef = parseFloat(vehiculo.precio_referencia || 0);
                    const desc = parseFloat(vehiculo.descuento || 0);
                    const tieneDesc = desc > 0;
                    const precioConDesc = tieneDesc ? (precioRef - (precioRef * desc / 100)) : precioRef;
                    let verificacionHtml = "";
                    if (Array.isArray(vehiculo.verificacion) && vehiculo.verificacion.length > 0) {
                        if (vehiculo.verificacion[0].verificado == 1) {
                            verificacionHtml = `<div class="text-end"><span class="fw-bold product-category" style="font-size: 11px;"><i class="fi-rs-check ms-1"></i> Verificado</span></div>`;
                        }
                    }
                    return `
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap text-center">
                                <div class="product-img product-img-zoom">
                                    <a href="detalle_vehiculo.php?q=${vehiculo.id_vehiculo}" target="_blank" rel="noopener noreferrer">
                                        <img class="default-img img-fluid mb-1"
                                            src="admin/${vehiculo.img_frontal}" alt="${modelo}"
                                            onerror="this.onerror=null;this.src='img/FULMUV-NEGRO.png';"
                                            style="object-fit: contain; width: 100%; height: 180px">
                                    </a>
                                </div>
                                ${tieneDesc ? `<div class="product-badges product-badges-position"><span class="best">-${parseInt(vehiculo.descuento)}%</span></div>` : ''}
                            </div>
                            <div class="product-content-wrap p-2">
                                <div class="brand small text-muted">${marca}</div>
                                <a onclick="irADetalleVehiculoConTerminos(${vehiculo.id_vehiculo}); return false;">
                                    <h3 class="model" style="font-size: 14px; height: 38px; overflow: hidden; font-weight:700;">${modelo}</h3>
                                </a>
                                <div class="year font-sm color-grey">${anio}</div>
                                ${verificacionHtml}
                                <hr class="my-1">
                                <div class="meta-line font-xs color-grey">
                                    <i class="fi-rs-marker"></i> ${prov || '—'} | <i class="fi-rs-dashboard"></i> ${kms}
                                </div>
                                <div class="product-price text-center mt-2">
                                    <span class="text-brand">${formatPrecioSuperscript(tieneDesc ? precioConDesc : precioRef)}</span>
                                    ${tieneDesc ? `<br><span class="old-price" style="font-size: 11px;">${formatoMoneda.format(precioRef)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }

                function renderVehiculosRelacionados(lista) {
                    let $slider = $('#carausel-4-columns-vehiculos');
                    if ($slider.hasClass('slick-initialized')) $slider.slick('unslick');
                    $slider.empty();
                    lista.forEach(v => $slider.append(buildVehiculoCard(v)));
                    $slider.slick({
                        dots: false,
                        infinite: lista.length > 5,
                        speed: 800,
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
                        ]
                    });
                }

                const relacionados = (returnedDataProducto.data || []).filter(v => String(v.id_vehiculo) !== String(id_producto));
                const needed = 6 - relacionados.length;

                if (needed <= 0) {
                    renderVehiculosRelacionados(relacionados);
                } else {
                    $.get("api/v1/fulmuv/vehiculos/All", function (fallback) {
                        const existingIds = new Set(relacionados.map(v => String(v.id_vehiculo)));
                        existingIds.add(String(id_producto));
                        let extra = (fallback.data || []).filter(v => !existingIds.has(String(v.id_vehiculo)));
                        for (let i = extra.length - 1; i > 0; i--) {
                            const j = Math.floor(Math.random() * (i + 1));
                            [extra[i], extra[j]] = [extra[j], extra[i]];
                        }
                        renderVehiculosRelacionados([...relacionados, ...extra.slice(0, needed)]);
                    }, 'json');
                }
            }, 'json');

            // Aumentar/disminuir cantidad
            $(document).on("click", ".qty-up", function () {
                let input = $(".qty-val");
                let value = parseInt(input.val()) || 1;
                input.val(value + 1);
            });

            $(document).on("click", ".qty-down", function () {
                let input = $(".qty-val");
                let value = parseInt(input.val()) || 1;
                if (value > 1) input.val(value - 1);
            });

            $(document).on("click", ".button-add-to-cart", function () {
                const productoId = returnedData.data.id_producto;
                const cantidad = parseInt($(".qty-val").val());
                const nombre = returnedData.data.nombre;
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
                const img = ("admin/" + returnedData.data.img_frontal) || "img/FULMUV-NEGRO.png";
                const id_empresa = returnedData.data.id_empresa;

                registrarInteraccionVehiculo("agregar_carrito", { metadata: { origen: "detalle_vehiculo" } });

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
                        timeOut: 2000
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
                        total_pagado: 0
                    });

                    toastr.success(`"${nombre}" agregado al carrito`, 'Fulmuv', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 2000
                    });
                }

                localStorage.setItem("carrito", JSON.stringify({
                    data: carrito,
                    timestamp: new Date().getTime()
                }));

                actualizarIconoCarrito();
            });

            $(document).off("click", ".contact-whatsapp").on("click", ".contact-whatsapp", function () {
                registrarInteraccionVehiculoWhatsapp("detalle_vehiculo_contacto");
            });

            $(document).off("click", ".contact-phone").on("click", ".contact-phone", function () {
                registrarInteraccionVehiculoTelefono("detalle_vehiculo_contacto");
            });






            var imgEmpr = `admin/${returnedData.data.empresa.img_path}` || "img/FULMUV-NEGRO.png";


            const d = returnedData.data;

            $("#infoExtraVehiculo").html(`
                ${itemInfo("Marca", d?.marcaArray?.[0]?.nombre)}
                ${itemInfo("Modelo", d?.modeloArray?.nombre)}
                ${itemInfo("Recorrido", `${d?.kilometraje ?? "0"} kms`)}
                ${itemInfo("Transmisión", d?.transmisionArray?.[0]?.nombre)}
                ${itemInfo("Condición", asPlainText(d?.condicion))}
                ${itemInfo("Cilindraje", d?.cilindraje)}
                ${itemInfo("Año", d?.anio)}
                ${itemInfo("Tracción", d?.tipo_traccionArray?.[0]?.nombre)} 
            `);

            $("#imagenEmpresa").attr("src", imgEmpr)

            $("#nombreEmpresa").text(returnedData.data.empresa.nombre)
            $("#direccionEmpresa").text(returnedData.data.empresa.direccion)
            $("#telefonoEmpresa").text(returnedData.data.empresa.telefono_contacto)
            $("#descripcionEmpresa").text(safeCompanyText(returnedData.data.empresa.descripcion) || "Esta empresa no ha agregado una descripción todavía.")
            $("#empresaSocialLinks").html(buildCompanySocialLinksHtml(returnedData.data.empresa))

            $("#Description").append(`
                <span class="hot">${returnedData.data.descripcion}</span>
            `)

            // Usamos Optional Chaining (?.) y Nullish Coalescing (??) para mayor seguridad
            $("#modelo_info").text(returnedData.data.modeloArray?.nombre ?? "Sin modelo");

            $("#color_info").text(returnedData.data.colorArray?.[0]?.nombre ?? "Sin color");

            $("#traccion_info").text(returnedData.data.tipo_traccionArray?.[0]?.nombre ?? "Sin tracción");

            $("#condicion_info").text(asPlainText(returnedData.data.condicion) || "Sin condición");

            $("#transmision_info").text(returnedData.data.transmisionArray?.[0]?.nombre ?? "Sin transmisión");

            $("#dueno_info").text(asPlainText(returnedData.data.tipo_dueno) || "Sin tipo");

            const tv = returnedData?.data?.tipo_vendedorArray;
            $("#vendedor_info").text(
                Array.isArray(tv) && tv.length ? (tv[0]?.nombre || tv[0]?.name || "Sin Vendedor") : "Sin Vendedor"
            );

            // Campos de placa y básicos
            $("#placa_info").text(asPlainText(returnedData.data.inicio_placa) || "—");
            $("#placa_termina_info").text(returnedData.data.fin_placa ?? "—");

            // Campos que vienen de Arreglos (Validación de índice [0])
            $("#direccion_info").text(returnedData.data.direccionArray?.[0]?.nombre ?? "No definida");
            $("#climatizacion_info").text(returnedData.data.climatizacionArray?.[0]?.nombre ?? "No definida");
            $("#tapiceria_info").text(returnedData.data.tapiceriaArray?.[0]?.nombre ?? "No definida");
            $("#motor_info").text(returnedData.data.funcionamiento_motorArray?.[0]?.nombre ?? "No definido");

            // Otros campos técnicos
            $("#cilindraje_info").text(returnedData.data.cilindraje ?? "—");
            $("#anio_info").text(returnedData.data.anio ?? "—");

            $("#ubicacion_info").text(obtenerUbicacion(returnedData.data));
            // Información de catálogo y metadata
            $("#nombre_subcategoria").text(capitalizarPrimeraLetra(returnedData.data.nombre_sub_categoria ?? ""));
            $("#fechaCreado").text(returnedData.data.created_at ?? "—");
            $("#codigoProducto").text(returnedData.data.codigo ?? "—");
            $("#etiquetasProducto").text(capitalizarPrimeraLetra(returnedData.data.tags ?? ""));

            // let jsonArray = [];
            // const detRaw = returnedData.data.detalle_producto;

            // try {
            //     if (detRaw && detRaw !== 'null' && detRaw !== '[]') {
            //         jsonArray = JSON.parse(detRaw);
            //     }
            // } catch (e) {
            //     jsonArray = [];
            // }

            // if (!Array.isArray(jsonArray) || jsonArray.length === 0) {
            //     $("#Additional-info").html(
            //         `<p class="text-muted">No se ingresó información adicional.</p>`
            //     );
            // } else {
            //     let listTable = "";
            //     jsonArray.forEach((item, index) => {
            //         const label = (item.label || '').toString().trim();
            //         const valor = (item.valor ?? '').toString();
            //         const fondo = index % 2 === 0 ? "#f8f9fa" : "#ffffff";
            //         listTable += `
            //             <tr class="stand-up" style="background-color:${fondo};">
            //                 <th style="color:#000">${label}</th>
            //                 <td><p style="color:#000">${valor}</p></td>
            //             </tr>`;
            //     });

            //     $("#Additional-info").html(`
            //             <table class="font-md">
            //             <tbody id="listTableAdicional">
            //                 ${listTable}
            //             </tbody>
            //             </table>
            //         `);
            // }

        }

    }, 'json')



})


function itemInfo(label, value) {
    const v = (value ?? "").toString().trim();
    // Agregamos d-flex flex-column y h-100 para uniformidad
    return `
    <div class="col-6 mb-2">
      <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-center" style="min-height: 85px;">
        <div class="small text-muted mb-1" style="font-size: 0.75rem; text-transform: capitalize;">${label}</div>
        <div class="fw-bold" style="font-size: 0.95rem; line-height: 1.2;">${v || "—"}</div>
      </div>
    </div>
  `;
}

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

function obtenerUbicacion(data) {
    let provinciaArr = [];
    let cantonArr = [];

    try {
        provinciaArr = data?.provincia ? JSON.parse(data.provincia) : [];
    } catch (e) {
        provinciaArr = [];
    }

    try {
        cantonArr = data?.canton ? JSON.parse(data.canton) : [];
    } catch (e) {
        cantonArr = [];
    }

    const provincia = provinciaArr.length ? provinciaArr[0] : "";
    const canton = cantonArr.length ? cantonArr[0] : "";

    let ubicacion = "Sin ubicación";

    if (provincia || canton) {
        ubicacion = `${provincia || ""}${provincia && canton ? "; " : ""}${canton || ""}`;
    }

    return ubicacion;
}

let map, marker, mapsReady = false;
let pendingCoords = null; // guardamos coords si llegan antes que la API

// Google llamará esto cuando cargue la librería
function initMap() {
    mapsReady = true;

    // si ya llegaron coords antes, renderiza con esas
    if (pendingCoords) {
        renderMap(pendingCoords.lat, pendingCoords.lng);
        return;
    }

    // si aún no llegan coords, renderiza un fallback (Ecuador)
    renderMap(-1.8312, -78.1834);
}


function setMapCoords(lat, lng) {
    const latNum = parseFloat(String(lat).replace(",", "."));
    const lngNum = parseFloat(String(lng).replace(",", "."));

    pendingCoords = (Number.isFinite(latNum) && Number.isFinite(lngNum))
        ? { lat: latNum, lng: lngNum }
        : { lat: -1.8312, lng: -78.1834 };

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

        // ✅ Click en el mapa: abre el punto exacto clickeado
        map.addListener("click", (e) => {
            if (!e?.latLng) return;
            registrarInteraccionVehiculoMapa("detalle_vehiculo_mapa");
            abrirGoogleMaps(e.latLng.lat(), e.latLng.lng());
        });

        // ✅ Click en el marcador: abre la ubicación del marcador
        marker.addListener("click", () => {
            const pos = marker.getPosition();
            if (!pos) return;
            registrarInteraccionVehiculoMapa("detalle_vehiculo_mapa");
            abrirGoogleMaps(pos.lat(), pos.lng());
        });

    } else {
        map.setCenter(center);
        map.setZoom(15);
        if (marker) marker.setPosition(center);
    }
}
document.addEventListener('shown.bs.tab', (ev) => {
    const href = ev.target?.getAttribute('href');
    if (href === '#Vendor-info') {
        // si todavía no existe, intenta crearlo (por si nunca se abrió)
        if (mapsReady && !map && pendingCoords) {
            renderMap(pendingCoords.lat, pendingCoords.lng);
        }

        if (map) {
            google.maps.event.trigger(map, 'resize');
            if (pendingCoords) map.setCenter(pendingCoords);
        }
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

// Para tracción: si hay referencia => "8X4 - FWD", si no hay => "8X4"
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

// Helpers para leer arrays serializados como '["Azuay"]'
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
