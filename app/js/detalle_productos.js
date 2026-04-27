var id_producto = $("#id_producto").val()
let subcategoriasSeleccionadas = [];
let vistaDetalleRegistrada = false;

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

function obtenerTelefonoEnmascarado(raw) {
    const digits = (raw || "").toString().replace(/\D/g, "");
    if (!digits) return "No disponible";
    return `***${digits.slice(-4)}`;
}

function registrarInteraccionProducto(tipoEvento, payload = {}) {
    if (!id_producto || !tipoEvento) {
        return;
    }

    $.ajax({
        url: "../api/track_producto_interaccion.php",
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

function isSinCuentaMode() {
    return !!window.APP_MODE_CONFIG?.sinCuenta;
}

function getAppPath(key, fallback) {
    if (isSinCuentaMode()) {
        return window.APP_MODE_CONFIG?.[key] || fallback;
    }
    return fallback;
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

function escapeHtml(text) {
    return (text || "")
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
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

function formatearTextoDetalle(valor) {
    const texto = (valor || "").toString().trim();
    if (!texto) return "";
    return window.fulmuvTitleCase ? window.fulmuvTitleCase(texto) : texto;
}

function buildDetalleProductoImageUrl(path) {
    const raw = (path || "").toString().trim();
    if (!raw) return "../img/FULMUV-NEGRO.png";
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith("../")) return raw;
    if (raw.startsWith("admin/")) return `../${raw}`;
    if (raw.startsWith("/admin/")) return `..${raw}`;
    return `../admin/${raw.replace(/^\/+/, "")}`;
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
        return `<a href="${href}" class="vendor-social-link company-external-link" data-external-url="${href}" target="_blank" rel="noopener" aria-label="${label}" title="${label}"><i class="${icon}"></i></a>`;
    }).join("");
}

function aplicarMensajeDetalleSegunPlanApp(producto, opciones = {}) {
    const tipoProducto = String(producto?.tipo_producto || "").toLowerCase().trim();
    const esServicio = opciones.esServicio === true || tipoProducto === "servicio";
    const nombreEmpresa = formatearTextoDetalle(producto?.empresa?.nombre || "la empresa");
    const nombreMembresia = normalizarNombreMembresia(producto?.empresa?.membresia?.nombre);
    const restringeEntrega = !esServicio && (nombreMembresia === "onemuv" || nombreMembresia === "basicmuv");
    const sinCuenta = opciones.sinCuenta === true;

    if (sinCuenta) {
        $("#purchasePanel").hide();
        $("#serviceCtaPanel").show();
        $("#btnServiceContact span").text(`Comunícate con ${nombreEmpresa}`);
        $("#serviceInfoBox").text(`Debes comunicarte por WhatsApp con ${nombreEmpresa} para poder obtener este ${esServicio ? "servicio" : "producto"}.`);
        $("#loginUpsellNote").show().html(`
            <strong>Inicia sesión para tener mejores opciones</strong>
            <p>Al ingresar con tu cuenta podrás guardar favoritos, agregar productos al carrito, comparar opciones y dar seguimiento a tus solicitudes desde un solo lugar.</p>
        `);
        return;
    }

    if (esServicio) {
        $("#purchasePanel").hide();
        $("#serviceCtaPanel").show();
        $("#btnServiceContact span").text(`Comunícate con ${nombreEmpresa}`);
        $("#serviceInfoBox").text(`Debes comunicarte por WhatsApp con ${nombreEmpresa} para poder obtener este servicio.`);
        $("#loginUpsellNote").hide();
        return;
    }

    if (restringeEntrega) {
        $("#purchasePanel").hide();
        $("#serviceCtaPanel").show();
        $("#btnServiceContact span").text(`Comunícate con ${nombreEmpresa}`);
        $("#serviceInfoBox").text(`Este producto no tiene envío a domicilio con GRUPO ENTREGAS ni retiro en la empresa ${nombreEmpresa}, pero puede comunicarse por medio de WhatsApp.`);
        $("#loginUpsellNote").hide();
        return;
    }

    $("#purchasePanel").show();
    $("#serviceCtaPanel").hide();
    $("#loginUpsellNote").hide();
}


$(document).ready(function () {
    // localStorage.removeItem("carrito");
    actualizarIconoCarrito();
    $("#breadcrumb")?.append(`<a href="https://fulmuv.com/" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a><span></span> Detalle del producto`);
    $(document).off('click.companyExternalLink').on('click.companyExternalLink', '.company-external-link', function (e) {
        const url = $(this).data('external-url') || $(this).attr('href');
        if (typeof abrirEnlaceExternoApp === 'function') {
            e.preventDefault();
            abrirEnlaceExternoApp(url);
        }
    });


    $.get("../api/v1/fulmuv/categoriasPrincipales/All", function (returnedDataCategoria) {
        if (!returnedDataCategoria.error) {
            console.log(returnedDataCategoria)
            categoriasOriginales = returnedDataCategoria.data;
            console.log(categoriasOriginales)
            renderizarCategorias();
        }
    }, 'json')

    $.get("../api/v1/fulmuv/productos/" + id_producto, function (returnedData) {

        if (!returnedData.error) {
            const p = returnedData.data;
            const tipoProducto = String(p.tipo_producto || "").toLowerCase().trim();
            const esServicio = tipoProducto === "servicio" || (
                !tipoProducto &&
                Array.isArray(p.categorias) &&
                p.categorias.some(cat => String(cat.tipo || "").toLowerCase() === "servicio")
            );
            const tipoInteraccion = esServicio ? "servicio" : "producto";

            // --- 1. CONFIGURACIÓN DE PRECIOS Y FLAGS ---
            $("#flagsPrecio").empty();
            const iva = parseInt(p.iva || 0);
            const negociable = parseInt(p.negociable || 0);

            if (iva === 1) $("#flagsPrecio").append(`<span class="price-flag iva">IVA incluido</span>`);
            if (negociable === 1) $("#flagsPrecio").append(`<span class="price-flag neg">Precio negociable</span>`);

            const tieneDescuento = parseFloat(p.descuento) > 0;
            const precioRef = parseFloat(p.precio_referencia);
            const precioDescuento = precioRef - (precioRef * (parseFloat(p.descuento) || 0) / 100);

            $(".title-detail").text(capitalizarPrimeraLetra(p.titulo_producto));
            $(".current-price").text(formatoMoneda.format(tieneDescuento ? precioDescuento : precioRef));

            if (tieneDescuento) {
                $("#listDescuento").removeClass("d-none");
                $("#valorporcentaje").text(p.descuento + "% de descuento");
                $("#valorDescuento").text(formatoMoneda.format(precioRef));
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

            $("#Description").append(`
                <span class="hot">${returnedData.data.descripcion}</span>
            `)
            // MARCA(S)
            const marcasTxt = formatearTextoDetalle(obtenerNombreMarcaProducto(returnedData.data));
            $("#marcaProducto").text(marcasTxt || "-");

            // MODELO(S) (a veces viene como objeto, otras como array "modelo_productoo")
            const modelosRaw = returnedData.data.modelo_productoo || returnedData.data.modelo_producto;
            const modelosTxt = formatearTextoDetalle(joinNombres(modelosRaw));
            $("#modeloProducto").text(modelosTxt || "-");
            const categoriasTxt = formatearTextoDetalle(joinNombres(returnedData.data.categorias) || returnedData.data.nombre_categoria || "");
            $("#categoriaProducto").text(categoriasTxt || "-");

            const subcategoriasTxt = formatearTextoDetalle(joinNombres(returnedData.data.subcategorias || returnedData.data.sub_categorias) || returnedData.data.nombre_sub_categoria || "");
            $("#subcategoriaProducto").text(subcategoriasTxt || "-");
            $("#subCategoriasProductos").text(subcategoriasTxt || "-");
            var verificacion = "";
            if (returnedData.data.verificacion.length != 0) {
                if (returnedData.data.verificacion[0].verificado == 1) {
                    verificacion = `
                        <img src="../img/verificado_empresa.png" 
                            alt="Verificado" 
                            title="Empresa verificada" 
                            style="width:36px;height:36px;margin-left:8px;vertical-align:middle;">
                    `;
                }
            }

            const vendorProductsPath = getAppPath("vendorProductsPath", "productos_vendor.php");

            $("#viewVisitaTienda").append(`
                <div class="d-inline-flex align-items-center">
                    <a href="${vendorProductsPath}?q=${returnedData.data.empresa.id_empresa}" 
                    class="fw-bold text-primary text-decoration-none">
                    Visitar la tienda ${returnedData.data.empresa.nombre}
                    </a>
                    ${verificacion}
                </div>
            `);

            if (!vistaDetalleRegistrada) {
                vistaDetalleRegistrada = true;
                registrarInteraccionProducto("vista_detalle", {
                    metadata: {
                        origen: "detalle_producto_app"
                    }
                });
            }
            const mainContainer = $(".main-image-container");
            const thumbGrid = $("#thumbGrid");

            // 1. Recolectar imágenes
            let todasLasImagenes = [];
            if (p.img_frontal) todasLasImagenes.push(p.img_frontal);
            if (p.img_posterior) todasLasImagenes.push(p.img_posterior);
            if (Array.isArray(p.archivos)) {
                p.archivos.forEach(img => {
                    if (img?.archivo) {
                        todasLasImagenes.push(img.archivo);
                    }
                });
            }

            const galleryGroupId = "galeria-producto-app";
            const galleryUrls = [...new Set(
                todasLasImagenes
                    .map(buildDetalleProductoImageUrl)
                    .filter(Boolean)
            )];

            mainContainer.empty();
            thumbGrid.empty();

            function bindProductGallery() {
                if (!window.Fancybox) return;

                if (typeof Fancybox.unbind === "function") {
                    Fancybox.unbind(`[data-fancybox="${galleryGroupId}"]`);
                }

                Fancybox.bind(`[data-fancybox="${galleryGroupId}"]`, {
                    Thumbs: {
                        autoStart: true
                    },
                    Toolbar: {
                        display: ["close"]
                    },
                    infinite: true
                });
            }

            function renderMainImage(url) {
                const hiddenGallery = galleryUrls
                    .filter(imageUrl => imageUrl !== url)
                    .map(imageUrl => `
                        <a href="${imageUrl}" data-fancybox="${galleryGroupId}" class="d-none" aria-hidden="true" tabindex="-1">
                            <img src="${imageUrl}" alt="Imagen del producto" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" />
                        </a>
                    `)
                    .join("");

                mainContainer.html(`
                    <a href="${url}" id="currentMainImageLink" class="main-image-link" data-fancybox="${galleryGroupId}">
                        <span class="zoom-icon"><i class="fi-rs-search"></i></span>
                        <img src="${url}" id="currentMainImage" alt="Imagen principal del producto" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';" />
                    </a>
                    <div class="d-none">${hiddenGallery}</div>
                `);

                bindProductGallery();
            }

            // 2. Mostrar la primera imagen por defecto
            renderMainImage(galleryUrls[0] || "../img/FULMUV-NEGRO.png");

            // 3. Renderizar rejilla de miniaturas (máximo 5)
            const maxVisibles = 5;
            galleryUrls.forEach((imageUrl, index) => {
                if (index < maxVisibles) {
                    let overlayClass = "";
                    let counterAttr = "";
                    const isActive = index === 0 ? "active" : "";

                    if (index === maxVisibles - 1 && galleryUrls.length > maxVisibles) {
                        overlayClass = "thumb-more-overlay";
                        counterAttr = `data-count="+${galleryUrls.length - (maxVisibles - 1)}"`;
                    }

                    thumbGrid.append(`
                    <div class="thumb-item ${overlayClass} ${isActive}" ${counterAttr} data-image="${imageUrl}">
                        <img src="${imageUrl}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">
                    </div>
                `);
                }
            });

            // Función para cambiar imagen (sin slider)
            window.cambiarImagen = function (url, thumbEl) {
                renderMainImage(url);
                $("#thumbGrid .thumb-item").removeClass("active");
                if (thumbEl) {
                    $(thumbEl).addClass("active");
                } else {
                    $(`#thumbGrid .thumb-item[data-image="${url}"]`).addClass("active");
                }
            };
            $("#thumbGrid").off("click.detalleThumb").on("click.detalleThumb", ".thumb-item", function () {
                const imageUrl = $(this).data("image");
                if (imageUrl) {
                    window.cambiarImagen(imageUrl, this);
                }
            });
            if (p.empresa.latitud && p.empresa.longitud) {
                setMapCoords(p.empresa.latitud, p.empresa.longitud);
            }

            // --- 5. LOGICA WHATSAPP ---
            const detailUrl = window.location.href;
            const phoneRaw = (p.empresa.whatsapp_contacto || p.empresa.telefono_contacto || "").toString().replace(/\D/g, "");
            let phone = phoneRaw.startsWith("0") ? "593" + phoneRaw.slice(1) : phoneRaw;

            const waText = encodeURIComponent(`Hola, me interesa este ${esServicio ? "servicio" : "producto"}:\n${detailUrl}`);
            const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
            const registrarWhatsapp = () => registrarInteraccionProducto("click_whatsapp", {
                tipo: tipoInteraccion,
                metadata: {
                    telefono: phone,
                    canal: "detalle_producto_app"
                }
            });

            const whatsappHref = isMobile ? `whatsapp://send?phone=${phone}&text=${waText}` : `https://web.whatsapp.com/send?phone=${phone}&text=${waText}`;
            const configurarBotonWhatsapp = function (selector) {
                $(selector).attr({
                    href: whatsappHref,
                    target: "_blank"
                });

                $(selector).off("click.detalleWhatsapp").on("click.detalleWhatsapp", function (e) {
                    e.preventDefault();
                    registrarWhatsapp();
                    abrirWhatsAppApp(phone, decodeURIComponent(waText), `https://api.whatsapp.com/send?phone=${phone}&text=${waText}`);
                });
            };

            configurarBotonWhatsapp("#btnWhatsApp");
            configurarBotonWhatsapp("#btnServiceContact");

            const telefonoRaw = (p.empresa.telefono_contacto || "").toString().trim();
            const telefonoDigits = telefonoRaw.replace(/\D/g, "");
            const telefonoMascara = obtenerTelefonoEnmascarado(telefonoRaw);
            $("#contactosEmpresa").html(`
                <div class="d-flex flex-wrap gap-2 mt-2">
                    ${telefonoDigits ? `
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="small text-muted">Teléfono: ${telefonoMascara}</span>
                            <a href="#" class="btn btn-sm btn-outline-primary" id="btnTelefonoReveal">
                                <i class="fi-rs-eye me-1"></i> Ver más
                            </a>
                            <a href="tel:${telefonoDigits}" class="btn btn-sm btn-outline-primary d-none" id="btnTelefonoEmpresa">
                                <i class="fi-rs-phone-call me-1"></i> ${escapeHtml(telefonoRaw)}
                            </a>
                        </div>
                    ` : ""}
                </div>
            `);

            $("#btnTelefonoReveal").off("click.detalleTelefonoReveal").on("click.detalleTelefonoReveal", function (e) {
                e.preventDefault();
                registrarInteraccionProducto("click_telefono", {
                    tipo: tipoInteraccion,
                    metadata: { origen: "detalle_producto_app_reveal" }
                });
                $("#btnTelefonoEmpresa").removeClass("d-none");
                $(this).remove();
            });

            $("#btnTelefonoEmpresa").off("click.detalleTelefono").on("click.detalleTelefono", function (e) {
                e.preventDefault();
                registrarInteraccionProducto("click_telefono", {
                    tipo: tipoInteraccion,
                    metadata: { origen: "detalle_producto_app_call" }
                });
                abrirLlamadaApp(telefonoRaw || telefonoDigits);
            });

            aplicarMensajeDetalleSegunPlanApp(p, {
                esServicio,
                sinCuenta: isSinCuentaMode()
            });


            $(document).off("click.detalleAddCart").on("click.detalleAddCart", ".button-add-to-cart", function () {
                if (isSinCuentaMode()) {
                    return;
                }
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
                    cantidad: cantidad,
                    metadata: {
                        nombre: nombre,
                        precio: precioConDescuento
                    }
                });

                actualizarIconoCarrito();
            });

            var imgPath = returnedData.data.empresa.img_path;

            var imgEmpr = imgPath
                ? `../empresa/${imgPath}`
                : "../img/FULMUV-NEGRO.png";

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

            renderRelatedLoading("#carausel-4-columns-oferta", esServicio ? "servicio" : "producto");

            $.post("../api/v1/fulmuv/categorias/productos", {
                // Enviamos como array JSON para que el PHP lo procese correctamente
                id_categoria: p.id_categoria,
                id_empresa: p.id_empresa
            }, function (returnedDataProducto) {

                if (!returnedDataProducto.error && returnedDataProducto.data.length > 0) {

                    let $slider = $('#carausel-4-columns-oferta');

                    // 1. Resetear el slider si ya está inicializado
                    if ($slider.hasClass('slick-initialized')) {
                        $slider.slick('unslick');
                    }

                    // 2. Limpiar contenido
                    $slider.empty();

                    // 3. Insertar productos correctamente envueltos
                    returnedDataProducto.data.forEach(function (data) {

                        if (id_producto != data.id_producto) {

                            var htmlVerificacion = "";
                            if (Array.isArray(data.verificacion) && data.verificacion.length > 0) {
                                if (data.verificacion[0].verificado == 1) {
                                    htmlVerificacion = `
                                        <div class="home-verify-overlay">
                                            <img src="../img/verificado_empresa.png" alt="Empresa verificada">
                                        </div>`;
                                }
                            }

                            const tieneDescuento = parseFloat(data.descuento) > 0;
                            const precioDescuento = data.precio_referencia - (data.precio_referencia * data.descuento / 100);
                            const tipoRelacionado = Array.isArray(data.categorias) && data.categorias[0]?.tipo ? String(data.categorias[0].tipo).toLowerCase() : "producto";

                            $slider.append(`
                                <div class="product-cart-wrap">
                                    <div class="product-img-action-wrap">
                                        ${htmlVerificacion}
                                        <div class="product-img product-img-zoom">
                                            <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;">
                                                <img class="default-img" src="../admin/${data.img_frontal}" alt=""
                                                    onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';"
                                                    style="height: 150px; object-fit: contain;" />
                                            </a>
                                        </div>

                                        ${tieneDescuento ? `
                                            <div class="product-badges product-badges-position product-badges-mrg">
                                                <span class="best">-${parseInt(data.descuento)}%</span>
                                            </div>` : ''}

                                        <div class="position-absolute top-0 end-0 m-2 d-none">
                                            <button class="btn rounded-circle d-flex justify-content-center align-items-center p-0"
                                                style="width: 40px; height: 40px;"
                                                onclick="agregarProductoCarrito(${data.id_producto}, '${escapeJsString(data.titulo_producto)}', '${data.precio_referencia}', '${data.img_frontal}')">
                                                <img alt="Carrito de compra" src="../img/carrito_transparente.png"/>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="product-content-wrap p-1">
                                        <h2 class="text-center">
                                            <a onclick="irADetalleProductoConTerminos(${data.id_producto}); return false;" class="limitar-lineas mt-1">
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

                    $slider.slick({
                        dots: false,
                        infinite: false,
                        speed: 1000,
                        arrows: true, // Siempre activas
                        autoplay: true,
                        slidesToShow: 5,
                        slidesToScroll: 1,
                        // Forzamos que las flechas sean botones limpios
                        prevArrow: '<button type="button" class="slick-prev"><i></i></button>',
                        nextArrow: '<button type="button" class="slick-next"><i></i></button>',
                        responsive: [{
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 3
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 2,
                                arrows: true // Mantenlas en móvil pero centradas
                            }
                        }
                        ]
                    });
                }
            }, 'json');
        }
    }, 'json');



})


function renderizarCategorias() {
    let html = '';

    categoriasOriginales.forEach(cat => {
        html += `
            <li class="p-1">
                <a href="${getAppPath("categoryProductsPath", "productos_categoria.php")}?q=${cat.id_categoria_principal}">
                    <img src="../admin/${cat.icono}" alt="${capitalizarPrimeraLetra(cat.nombre)}" onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">
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
        abrirMapaApp(latVal, lngVal, {
            title: "Ubicacion del vendedor"
        });
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
            abrirGoogleMaps(e.latLng.lat(), e.latLng.lng());
        });

        // Click en el marcador: abre la ubicación del marcador
        marker.addListener("click", () => {
            const pos = marker.getPosition();
            if (!pos) return;
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

function renderRelatedLoading(selector, label, count = 4) {
    const $target = $(selector);
    if (!$target.length) return;

    let html = '<div class="home-loading-grid">';
    for (let i = 0; i < count; i++) { 
        html += `
            <div class="home-loading-card">
                <div class="home-loading-media"></div>
                <div class="home-loading-content">
                    <div class="home-loading-caption">Cargando ${label}...</div>
                    <div class="home-loading-line w-90"></div>
                    <div class="home-loading-line w-75"></div>
                    <div class="home-loading-line w-60"></div>
                    <div class="home-loading-pill"></div>
                </div>
            </div>
        `;
    }
    html += '</div>';
    $target.html(html);
}

function escapeJsString(texto = "") {
    return String(texto)
        .replace(/\\/g, "\\\\")
        .replace(/'/g, "\\'")
        .replace(/"/g, '\\"');
}

