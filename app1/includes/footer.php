</div>
</main>



<!-- Preloader Start -->
<div id="preloader-active">
    <div class="preloader d-flex align-items-center justify-content-center">
        <div class="preloader-inner position-relative">
            <div class="text-center">
                <img src="../img/FulMuv-Loading-sinFondoBlanco.gif" alt="" style="width: 250px; height: 250px;object-fit: contain" />
            </div>
        </div>
    </div>
</div>

<!-- Vendor JS-->
<script src="../themelading/nest-frontend/assets/js/vendor/modernizr-3.6.0.min.js"></script>
<script src="../themelading/nest-frontend/assets/js/vendor/jquery-3.7.1.min.js"></script>
<script src="../themelading/nest-frontend/assets/js/vendor/jquery-migrate-3.3.0.min.js"></script>
<script src="../themelading/nest-frontend/assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/slick.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/jquery.syotimer.min.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/wow.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/slider-range.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/perfect-scrollbar.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/magnific-popup.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/select2.min.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/waypoints.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/counterup.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/jquery.countdown.min.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/images-loaded.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/isotope.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/scrollup.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/jquery.vticker-min.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/jquery.theia.sticky.js"></script>
<script src="../themelading/nest-frontend/assets/js/plugins/jquery.elevatezoom.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Template  JS -->
<script src="../themelading/nest-frontend/assets/js/main.js?v=6.1"></script>
<script src="../themelading/nest-frontend/assets/js/shop.js?v=6.1"></script>

<style>
    :root {
        --app-card-title-font: "Inter", sans-serif;
        --app-card-body-font: "Figtree", sans-serif;
        --app-card-price: #004e60;
        --app-card-price-old: #dc2626;
        --app-card-title: #0f172a;
        --app-card-copy: #475569;
        --app-card-radius: 18px;
        --app-card-shadow: 0 16px 34px rgba(15, 23, 42, 0.10);
        --app-card-border: rgba(15, 23, 42, 0.08);
    }

    body,
    body.single-product,
    body.single-product p,
    body.single-product span,
    body.single-product small,
    body.single-product label,
    body.single-product input,
    body.single-product select,
    body.single-product textarea,
    body.single-product button {
        font-family: var(--app-card-body-font);
    }

    body.single-product h1,
    body.single-product h2,
    body.single-product h3,
    body.single-product h4,
    body.single-product h5,
    body.single-product h6,
    .product-title-exclusive,
    .product-title-modern,
    .service-title-modern,
    .vehicle-title-modern,
    .vendor-title-modern,
    .home-event-title,
    .home-job-title,
    .home-card-title,
    .product-content-wrap h2,
    .product-content-wrap h2 a {
        font-family: var(--app-card-title-font) !important;
        letter-spacing: 0.01em;
    }

    .product-card-modern,
    .product-exclusive-card,
    .service-card-modern,
    .vehicle-card-modern,
    .vendor-card-modern,
    .product-cart-wrap,
    .home-vehicle-card {
        border-radius: var(--app-card-radius) !important;
        overflow: hidden;
        box-shadow: var(--app-card-shadow);
        border: 1px solid var(--app-card-border);
    }

    .product-card-modern,
    .product-exclusive-card,
    .service-card-modern,
    .vehicle-card-modern,
    .vendor-card-modern,
    .product-cart-wrap {
        background: #fff;
    }

    .product-img-container,
    .product-card-modern .product-card-image,
    .product-card-modern .product-media,
    .service-card-modern .service-image-modern,
    .service-card-modern .service-media-modern,
    .vehicle-card-modern .vehicle-image-modern,
    .vehicle-card-modern .vehicle-media-modern,
    .vendor-card-modern .vendor-img-wrapper,
    .product-cart-wrap .product-img,
    .home-vehicle-media {
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
    }

    .product-img-container img,
    .product-card-modern .product-card-image img,
    .product-card-modern .product-media img,
    .service-card-modern .service-image-modern img,
    .service-card-modern .service-media-modern img,
    .vehicle-card-modern .vehicle-image-modern img,
    .vehicle-card-modern .vehicle-media-modern img,
    .vendor-card-modern .vendor-main-img,
    .product-cart-wrap .product-img img,
    .home-vehicle-media img {
        width: 100%;
        height: 100% !important;
        object-fit: contain !important;
    }

    .product-content-wrap h2,
    .product-card-modern .product-title,
    .product-card-modern .product-title-modern,
    .product-title-exclusive,
    .product-title-modern,
    .service-title-modern,
    .vehicle-title-modern,
    .vendor-title-modern {
        font-size: 15px !important;
        font-weight: 800 !important;
        line-height: 1.32 !important;
        color: var(--app-card-title) !important;
    }

    .vendor-location-modern,
    .product-brand,
    .product-card-modern .product-body,
    .product-card-modern .product-footer,
    .service-meta-modern,
    .vehicle-meta-modern,
    .home-card-meta,
    .home-event-meta,
    .home-job-meta {
        font-family: var(--app-card-body-font);
        color: var(--app-card-copy);
    }

    .product-price,
    .product-price-modern,
    .service-price-modern,
    .vehicle-price-modern,
    .product-content-wrap .product-price,
    .product-card-modern .product-price {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        margin-top: auto;
    }

    .product-price > span:first-child,
    .product-card-modern .product-price strong,
    .product-price-modern .product-price-current,
    .service-price-modern .service-price-current,
    .vehicle-price-modern .vehicle-price-current,
    .product-content-wrap .product-price > span:first-child {
        color: var(--app-card-price) !important;
        font-family: var(--app-card-title-font) !important;
        font-weight: 900 !important;
        font-size: 1rem !important;
        line-height: 1.1;
    }

    .product-price .old-price,
    .product-price-modern .product-price-old,
    .service-price-modern .service-price-old,
    .vehicle-price-modern .vehicle-price-old,
    .product-content-wrap .product-price .old-price,
    .old-price {
        display: block !important;
        color: var(--app-card-price-old) !important;
        font-family: var(--app-card-body-font) !important;
        font-weight: 700 !important;
        font-size: 0.84rem !important;
        line-height: 1.1;
        text-decoration: line-through !important;
        text-decoration-color: var(--app-card-price-old) !important;
    }

    .product-details-exclusive,
    .product-card-modern .product-card-body,
    .service-card-modern .service-content-modern,
    .vehicle-card-modern .vehicle-content-modern,
    .vendor-info-modern {
        font-family: var(--app-card-body-font);
    }

    @media (max-width: 767px) {
        .product-content-wrap h2,
        .product-title-exclusive,
        .product-title-modern,
        .service-title-modern,
        .vehicle-title-modern,
        .vendor-title-modern {
            font-size: 14px !important;
        }
    }
</style>

<!-- Modal Términos del Producto -->
<!-- Modal Términos del Producto/Servicio -->
<div id="modal-terminos-producto-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
    <div id="modal-terminos-producto" style="background:#fff; width:92%; max-width:880px; border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,.2); padding:20px 24px;">
        <h5 style="margin:0 0 10px; font-size:18px; font-weight:900;" class="border-bottom">Términos y condiciones</h5>

        <!-- texto legal -->
        <div style="max-height:320px; overflow:auto; padding-right:6px;">
            <p style="font-size:14px; color:#111; margin:0 0 10px;">
                Declaro que he leído y acepto los <strong>Términos y Condiciones de Uso para Usuarios Visitantes y Compradores</strong> y el <strong>Aviso Legal</strong> de FULMUV.
            </p>
            <p style="font-size:14px; color:#111; margin:0 0 10px;">
                Entiendo que FULMUV actúa como plataforma de contacto y no interviene en las negociaciones ni garantiza productos o servicios; toda relación comercial se realiza directamente con el proveedor.
            </p>
            <p style="font-size:14px; color:#111; margin:0 0 10px;">
                Autorizo el tratamiento de mis datos personales conforme a las finalidades y bases legales descritas, en cumplimiento de la <strong>LOPDP del Ecuador</strong>.
                Reconozco que las entregas podrán realizarse mediante el socio logístico autorizado bajo las condiciones informadas.
            </p>
            <p style="font-size:14px; color:#111; margin:0;">
            <div class="d-flex gap-5 justify-content-center align-items-center mt-4 mb-4">
                <a href="documentos/2_Terminos_Condiciones_Uso_General_para_Usuarios_Visitantes_Compradores_de_FULMUV.pdf" target="_blank" rel="noopener noreferrer">Ver Términos y Condiciones de Usuarios Visitantes y Compradores</a>
                <a href="documentos/2_Aviso_Legal_Descargos_Responsabilidad_FULMUV.pdf" target="_blank" rel="noopener noreferrer">Ver Aviso Legal</a>
                <a href="documentos/2_Política_Privacidad_Cookies_FULMUV.pdf" target="_blank" rel="noopener noreferrer">Ver Política de Privacidad</a>
            </div>
            </p>
        </div>

        <!-- check -->
        <div class="form-check mt-3 mb-2">
            <input class="form-check-input" type="checkbox" id="chk-acepto-terminos">
            <label class="form-check-label" for="chk-acepto-terminos" style="font-size:14px;">
                Acepto los términos y condiciones indicados.
            </label>
        </div>

        <!-- acciones -->
        <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px;">
            <button id="btn-cancelar-terminos" style="border:0; background:#e5e7eb; color:#111; padding:8px 12px; border-radius:6px; font-weight:600;">
                Cancelar
            </button>
            <button id="btn-aceptar-terminos" class="btn btn-primary">
                Aceptar y continuar
            </button>
        </div>

        <!-- Guarda el id del producto/servicio cuando se abre -->
        <input type="hidden" id="terminos-product-id" value="">
        <input type="hidden" id="terminos-target-type" value="producto">
    </div>
</div>


</body>
<!-- <div class="zoomContainer" style="-webkit-transform: translateZ(0);position:absolute;left:0px;top:0px;height:0px;width:0px;"><div class="zoomWindowContainer" style="width: 400px;"><div style="z-index: 999; overflow: hidden; margin-left: 0px; margin-top: 0px; background-position: 0px 0px; width: 0px; height: 0px; float: left; display: none; cursor: crosshair; background-repeat: no-repeat; position: absolute; background-image: url(themelading/nest-frontend/assets/imgs/shop/product-16-4.jpg);" class="zoomWindow"></div></div></div>
<div class="zoomContainer" style="transform: translateZ(0px); position: absolute; left: 69.0903px; top: 409.236px; height: 435.99px; width: 435.99px;"><div class="zoomWindowContainer" style="width: 400px;"><div style="z-index: 999; overflow: hidden; margin-left: 0px; margin-top: 0px; background-position: -664.01px -545.876px; width: 435.99px; height: 435.99px; float: left; cursor: crosshair; background-repeat: no-repeat; position: absolute; background-image: url(themelading/nest-frontend/assets/imgs/shop/product-16-4.jpg); top: 0px; left: 0px; display: none;" class="zoomWindow">&nbsp;</div></div></div> -->

</html>


<script>
    document.getElementById('btnLoginProveedor')?.addEventListener('click', function() {
        window.location.href = 'empresa/login.php';
    });
    document.getElementById('btnLoginCliente')?.addEventListener('click', function() {
        window.location.href = 'login.php';
    });

    $(document).ready(function() {
        getEmpresasAll()

        // cuando abres el menú móvil, recalcula select2
        $(document).on('click', '.burger-icon', function() {
            setTimeout(function() {
                if ($("#listTienda2").length) {
                    $("#listTienda2").css('width', '100%');
                    if ($("#listTienda2").hasClass("select2-hidden-accessible")) {
                        $("#listTienda2").select2('open');
                        $("#listTienda2").select2('close');
                    }
                }
            }, 250);
        });

        // Cargar categorías
        $.get("../api/v1/fulmuv/categorias/All", function(returnedData) {
            if (!returnedData.error) {
                var listCategoria = `<option value="all" selected>Categorías</option>`;
                returnedData.data.forEach(function(categorias) {
                    listCategoria += `<option value="${categorias.id_categoria}">${categorias.nombre}</option>`;
                });
                $("#selectActiveCategory").append(listCategoria);
            }
        }, 'json');

        $(function() {
            const $input = $("#input-busqueda");
            const $wrap = $(".search-style-2"); // contenedor del input
            const $panel = $("#resultados-productos");

            // Asegura posicionamiento para que el panel tome el ancho del input
            $wrap.css("position", "relative");

            // Overlay para oscurecer el fondo
            const overlayId = "search-overlay";
            if (!document.getElementById(overlayId)) {
                $("body").append('<div id="search-overlay" style="display:none;"></div>');
            }
            const $overlay = $("#search-overlay");

            // Evento de búsqueda (ya existente)
            $input.on("input", function() {
                const searchText = $(this).val().trim();
                const categoriaId = $("#selectActiveCategory").val();

                if (searchText.length === 0) {
                    $panel.hide().html('');
                    $overlay.hide();
                    return;
                }

                $.post(`../api/v1/fulmuv/productos/busqueda`, {
                    q: searchText,
                    categoria: categoriaId
                }, function(res) {
                    if (res.error) {
                        $panel.html('<div class="text-muted px-3 py-2">Ocurrió un error</div>').show();
                        $overlay.show();
                        return;
                    }

                    const {
                        products,
                        categories,
                        randomCategories,
                        vehicles
                    } = res.data || {};
                    let html = '';

                    // ---------- Productos ----------
                    if (products?.length > 0) {
                        html += `<div class="px-3 pt-3 pb-2 fw-bold text-muted small">Búsquedas sugeridas</div>`;
                        products.slice(0, 10).forEach(p => {
                            html += `
                                <div class="resultado-item d-flex align-items-center border-bottom py-0 px-2"
                                    onclick="redirigirProductoDetalle(${p.id_producto})">
                                <img src="admin/${p.img_frontal || ''}" alt="${p.titulo_producto || ''}"
                                    style="width:50px;height:50px;object-fit:contain;" class="me-2"
                                    onerror="this.onerror=null;this.src='img/FULMUV LOGO-15.png';">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="font-size:14px;">${capitalizarPrimeraLetra(p.titulo_producto || p.nombre || '')}</h6>
                                </div>
                                </div>`;
                        });
                    }

                    // ---------- Vehículos ----------
                    if (vehicles?.length > 0) {
                        html += `<div class="px-3 pt-3 pb-2 fw-bold text-muted small">Vehículos relacionados</div>`;
                        vehicles.slice(0, 10).forEach(v => {
                            const titulo = [
                                (v.marca_nombre || v.marca_referencia || '').toString().trim(),
                                (v.modelo_nombre || '').toString().trim()
                            ].filter(Boolean).join(' ');
                            const sub = [
                                v.anio ? `Año: ${v.anio}` : null,
                                (v.kilometraje && !isNaN(Number(v.kilometraje))) ? `Km: ${v.kilometraje}` : null
                            ].filter(Boolean).join(' · ');

                            html += `
                                <div class="resultado-item d-flex align-items-center border-bottom py-0 px-2"
                                    onclick="redirigirVehiculoDetalle(${v.id_vehiculo})">
                                <img src="admin/${v.img_frontal || ''}" alt="${titulo}"
                                    style="width:50px;height:50px;object-fit:cover;" class="me-2"
                                    onerror="this.onerror=null;this.src='img/FULMUV LOGO-15.png';">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="font-size:14px;">${capitalizarPrimeraLetra(titulo)}</h6>
                                    ${sub ? `<small class="text-muted">${sub}</small>` : ``}
                                </div>
                                </div>`;
                        });
                    }

                    // ---------- Categorías relacionadas ----------
                    if (categories?.length > 0) {
                        html += `<div class="px-3 pt-3 pb-2 fw-bold text-muted small">Categorías relacionadas</div>`;
                        categories.forEach(c => {
                            html += `
                                <a class="resultado-item d-block border-bottom py-2 px-3"
                                href="${window.APP_MODE_CONFIG?.sinCuenta ? (window.APP_MODE_CONFIG?.categoryProductsPath || 'productos_categoria_sincuenta.php') : 'productos_categoria.php'}?q=${c.id_categoria}">
                                ${capitalizarPrimeraLetra(c.nombre || '')}
                                </a>`;
                        });
                    }

                    // ---------- Categorías sugeridas (si no hubo productos) ----------
                    if (!products?.length && randomCategories?.length > 0) {
                        html += `<div class="px-3 pt-3 pb-2 fw-bold text-muted small">Categorías sugeridas</div>`;
                        randomCategories.forEach(c => {
                            html += `
                                <a class="resultado-item d-block border-bottom py-2 px-3"
                                href="${window.APP_MODE_CONFIG?.sinCuenta ? (window.APP_MODE_CONFIG?.categoryProductsPath || 'productos_categoria_sincuenta.php') : 'productos_categoria.php'}?q=${c.id_categoria}">
                                ${capitalizarPrimeraLetra(c.nombre || '')}
                                </a>`;
                        });
                    }

                    // Si no hay nada
                    if (!html) {
                        html = `<div class="text-muted px-3 py-2">Sin resultados</div>`;
                    }

                    // Mostrar resultados
                    $panel.html(html).show();
                    $overlay.show();
                    const w = $input.outerWidth();
                    $panel.css("width", w + "px");
                }, 'json');

            });


            // NUEVO: capturar Enter en el input y abrir nueva pestaña con la búsqueda global
            $input.on("keydown", function(e) {
                // e.which para jQuery, e.key === 'Enter' por claridad moderna
                if (e.key === "Enter" || e.which === 13) {
                    e.preventDefault(); // evita que el form trate de hacer submit normal

                    const termino = $(this).val().trim();
                    if (termino.length === 0) {
                        return; // no hagas nada si está vacío
                    }

                    // armar URL con encodeURIComponent para no romper con espacios o caracteres raros
                    const url = "busqueda_productos.php?search=" + encodeURIComponent(termino);

                    // abrir en nueva pestaña/ventana
                    window.open(url, "_blank");
                }
            });



            // Cerrar haciendo click fuera
            $(document).on("click", function(e) {
                if (!$(e.target).closest('.search-style-2, #resultados-productos').length) {
                    $panel.hide();
                    $overlay.hide();
                }
            });
        });


        // Ocultar resultados si se hace clic fuera
        $(document).on("click", function(e) {
            if (!$(e.target).closest('.search-style-2').length) {
                $("#resultados-productos").hide();
            }
        });
    });

    function buildAppDetailUrl(path, idParamName, idValue) {
        const separator = path.includes('?') ? '&' : '?';
        return `${path}${separator}${idParamName}=${idValue}`;
    }

    function redirigirProductoDetalle(id_producto) {
        const detailPath = window.APP_MODE_CONFIG?.sinCuenta ?
            (window.APP_MODE_CONFIG?.productDetailPath || "detalle_producto_sincuenta.php") :
            "detalle_productos.php";

        if (!window.APP_MODE_CONFIG?.sinCuenta && window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('navegarDetalle', id_producto);
        } else {
            window.location.href = buildAppDetailUrl(detailPath, 'q', id_producto);
        }
    }

    function redirigirVehiculoDetalle(idVehiculo) {
        const detailPath = window.APP_MODE_CONFIG?.vehicleDetailPath || "detalle_vehiculo.php";

        if (!window.APP_MODE_CONFIG?.sinCuenta && window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('navegarVehiculoDetalle', idVehiculo);
        } else {
            window.location.href = buildAppDetailUrl(detailPath, 'q', idVehiculo);
        }
    }

    function redirigirEventoDetalle(idEvento) {
        if (!window.APP_MODE_CONFIG?.sinCuenta && window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('navegarEventoDetalle', idEvento);
        } else {
            window.location.href = `detalle_eventos.php?q=${idEvento}`;
        }
    }

    function puedeUsarFlutterBridge() {
        return !!window.flutter_inappwebview?.callHandler;
    }

    function normalizarTelefonoApp(phone) {
        const raw = String(phone || "").trim();
        if (!raw) return "";

        let digits = raw.replace(/\D/g, "");
        if (digits.length === 10 && digits.startsWith("0")) {
            digits = "593" + digits.slice(1);
        }
        if (digits.startsWith("5930")) {
            digits = "593" + digits.slice(4);
        }
        return digits;
    }

    function abrirWhatsAppApp(phone, message = "", fallbackUrl = "") {
        const normalizedPhone = normalizarTelefonoApp(phone);
        const payload = {
            phone: normalizedPhone,
            message: String(message || ""),
            url: fallbackUrl || (normalizedPhone ? `https://wa.me/${normalizedPhone}${message ? `?text=${encodeURIComponent(message)}` : ""}` : "")
        };

        if (puedeUsarFlutterBridge()) {
            window.flutter_inappwebview.callHandler('openWhatsApp', payload);
            return false;
        }

        if (payload.url) {
            window.open(payload.url, "_blank", "noopener");
        }
        return false;
    }

    function abrirLlamadaApp(phone) {
        const raw = String(phone || "").trim();
        const normalizedPhone = raw.replace(/\s+/g, "");

        if (puedeUsarFlutterBridge()) {
            window.flutter_inappwebview.callHandler('openPhoneCall', {
                phone: normalizedPhone
            });
            return false;
        }

        if (normalizedPhone) {
            window.location.href = `tel:${normalizedPhone}`;
        }
        return false;
    }

    function abrirMapaApp(lat, lng, extra = {}) {
        const latNum = Number(lat);
        const lngNum = Number(lng);
        const payload = Object.assign({
            lat: Number.isFinite(latNum) ? latNum : null,
            lng: Number.isFinite(lngNum) ? lngNum : null
        }, extra || {});

        if (puedeUsarFlutterBridge()) {
            window.flutter_inappwebview.callHandler('openMapLocation', payload);
            return false;
        }

        if (payload.lat != null && payload.lng != null) {
            window.open(`https://www.google.com/maps/search/?api=1&query=${payload.lat},${payload.lng}`, "_blank", "noopener,noreferrer");
        }
        return false;
    }

    function abrirEnlaceExternoApp(url) {
        const targetUrl = String(url || "").trim();
        if (!targetUrl) {
            return false;
        }

        if (puedeUsarFlutterBridge()) {
            window.flutter_inappwebview.callHandler('openExternalUrl', targetUrl);
            return false;
        }

        window.open(targetUrl, "_blank", "noopener,noreferrer");
        return false;
    }

    function base64ToUint8Array(base64) {
        const clean = String(base64 || "").includes(",") ? String(base64).split(",").pop() : String(base64 || "");
        const binary = atob(clean);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes;
    }

    function mapFlutterFilesToBrowserFiles(items) {
        return (Array.isArray(items) ? items : []).map((item, index) => {
            if (item instanceof File) return item;

            const name = item?.name || item?.fileName || `archivo_${index + 1}`;
            const type = item?.mimeType || item?.type || 'application/octet-stream';
            const bytes = base64ToUint8Array(item?.base64 || item?.bytesBase64 || item?.data || "");
            const file = new File([bytes], name, {
                type
            });
            file._fromFlutter = true;
            return file;
        });
    }

    function seleccionarArchivosApp(options = {}) {
        if (puedeUsarFlutterBridge()) {
            return window.flutter_inappwebview.callHandler('pickDeviceFiles', options)
                .then(mapFlutterFilesToBrowserFiles)
                .catch(() => []);
        }

        return new Promise((resolve) => {
            const input = document.createElement("input");
            input.type = "file";
            input.multiple = !!options.multiple;
            if (options.accept) {
                input.accept = options.accept;
            }
            input.addEventListener("change", () => {
                resolve(Array.from(input.files || []));
            }, {
                once: true
            });
            input.click();
        });
    }

    function getEmpresasAll() {
        $.get(`../api/v1/fulmuv/empresas/`, function(returnedData) {

            if (!returnedData.error) {
                const $select = $("#listTienda");
                const $select2 = $("#listTienda2");

                // limpiar selects (y destruir select2 si ya estaba aplicado)
                if ($select.hasClass("select2-hidden-accessible")) $select.select2("destroy");
                if ($select2.hasClass("select2-hidden-accessible")) $select2.select2("destroy");

                $select.empty();
                $select2.empty();

                // opciones base
                const baseOptions = `
        <option value="">Selecciona tu tienda</option>
        <option value="all">Todos las tiendas</option>
    `;
                $select.append(baseOptions);
                $select2.append(baseOptions);

                // agregar empresas
                returnedData.data.forEach(function(data) {
                    $select.append(`<option value="${data.id_empresa}">${data.nombre}</option>`);
                    $select2.append(`<option value="${data.id_empresa}">${data.nombre}</option>`);
                });

                // inicializar select2 con 100% (CLAVE)
                $select.select2({
                    width: '100%',
                    placeholder: "Selecciona tu tienda"
                });
                $select2.select2({
                    width: '100%',
                    placeholder: "Selecciona tu tienda",
                    dropdownParent: $(".mobile-header-wrapper-inner")
                });

                // change handler (compartido)
                const onChange = function() {
                    const idEmpresa = $(this).val();
                    if (!idEmpresa) return;

                    if (idEmpresa === "all") window.location.href = `vendor.php`;
                    else window.location.href = `productos_vendor.php?q=${idEmpresa}`;
                };

                $select.off('change').on('change', onChange);
                $select2.off('change').on('change', onChange);
            }

        }, 'json');
    }
    $(document).off("click", ".btn-eliminar-item").on("click", ".btn-eliminar-item", function(e) {
        e.preventDefault();
        e.stopPropagation();

        // ✅ id desde el botón, y si falla, desde el <li>
        const idBtn = $(this).attr("data-id");
        const idLi = $(this).closest("li").attr("data-id");
        const id = String(idBtn || idLi || "").trim();

        if (!id) {
            console.warn("[CARRITO] No se encontró data-id para eliminar");
            return;
        }

        let carritoData = {};
        try {
            carritoData = JSON.parse(localStorage.getItem("carrito")) || {};
        } catch (_) {}

        let carrito = Array.isArray(carritoData.data) ? carritoData.data : [];

        // ✅ borra todo lo que tenga ese id (string-safe)
        const nuevo = carrito.filter(p => String(p.id) !== id);

        if (nuevo.length === 0) {
            localStorage.removeItem("carrito");
        } else {
            localStorage.setItem("carrito", JSON.stringify({
                data: nuevo,
                timestamp: new Date().getTime()
            }));
        }

        // ✅ refrescar UI
        actualizarIconoCarrito();
    });

    $(document).on("click", ".cart-dropdown-wrap", function(e) {
        e.stopPropagation();
    });
    $(document).on("click", ".qty-up-mini, .qty-down-mini, .input-qty-mini", function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $(document).on("click", ".qty-up-mini", function() {
        const id = $(this).data("id");
        modificarCantidadCarrito(id, 1);
    });

    $(document).on("click", ".qty-down-mini", function() {
        const id = $(this).data("id");
        modificarCantidadCarrito(id, -1);
    });

    $(document).on("click", ".btn-eliminar-producto", function(e) {
        e.preventDefault();
        const id = $(this).data("id");
        eliminarDelCarrito(id);
    });

    function modificarCantidadCarrito(id, cambio) {
        let carritoData = {};
        try {
            carritoData = JSON.parse(localStorage.getItem("carrito")) || {};
        } catch (_) {}

        const now = new Date().getTime();

        if (Array.isArray(carritoData.data) && now - carritoData.timestamp < 2 * 60 * 60 * 1000) {
            let carrito = carritoData.data;
            const index = carrito.findIndex(p => String(p.id) === String(id));

            if (index !== -1) {
                const actual = parseInt(carrito[index].cantidad, 10) || 0;
                carrito[index].cantidad = actual + cambio;

                if (carrito[index].cantidad <= 0) carrito.splice(index, 1);

                if (carrito.length === 0) {
                    localStorage.removeItem("carrito");
                } else {
                    localStorage.setItem("carrito", JSON.stringify({
                        data: carrito,
                        timestamp: now
                    }));
                }

                actualizarIconoCarrito();
            }
        }
    }

    function eliminarDelCarrito(id) {
        let carritoData = {};
        try {
            carritoData = JSON.parse(localStorage.getItem("carrito")) || {};
        } catch (_) {}

        const now = new Date().getTime();

        let carrito = Array.isArray(carritoData.data) ? carritoData.data : [];
        carrito = carrito.filter(p => String(p.id) !== String(id));

        if (carrito.length === 0) {
            localStorage.removeItem("carrito");
        } else {
            localStorage.setItem("carrito", JSON.stringify({
                data: carrito,
                timestamp: now
            }));
        }

        actualizarIconoCarrito();
    }

    const formatoMoneda = new Intl.NumberFormat('es-EC', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function capitalizarPrimeraLetra(texto) {
        if (!texto) return ''; // Manejo de cadena vacía
        texto = texto.toLowerCase(); // Convierte todo a minúscula
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    }

    window.fulmuvTitleCase = window.fulmuvTitleCase || function(texto) {
        const input = (texto ?? "").toString().trim();
        if (!input) return "";

        return input
            .toLowerCase()
            .split(/\s+/)
            .map(function(parte) {
                return parte
                    .split("-")
                    .map(function(fragmento) {
                        if (!fragmento) return "";
                        return fragmento.charAt(0).toUpperCase() + fragmento.slice(1);
                    })
                    .join("-");
            })
            .join(" ");
    };

    window.capitalizarPrimeraLetra = window.fulmuvTitleCase;
    capitalizarPrimeraLetra = window.fulmuvTitleCase;

    // Config
    const TERMS_VERSION = 'v1'; // súbelo cuando cambien T&C
    const TERMS_ACCEPTED_STORAGE_KEY = `fulmuv_terms_general_${TERMS_VERSION}`;
    const TERMS_USER_IP = (() => {
        if (typeof window !== 'undefined' && typeof window.USER_IP === 'string') {
            return window.USER_IP.trim();
        }
        if (typeof USER_IP !== 'undefined' && typeof USER_IP === 'string') {
            return USER_IP.trim();
        }
        return '';
    })();

    function hasAcceptedGeneralTermsLocally() {
        try {
            return localStorage.getItem(TERMS_ACCEPTED_STORAGE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function saveAcceptedGeneralTermsLocally() {
        try {
            localStorage.setItem(TERMS_ACCEPTED_STORAGE_KEY, '1');
        } catch (e) {}
    }

    // APIs
    function apiCheckProductTerms(productId) {
        const payload = {
            product_id: productId,
            version: TERMS_VERSION
        };
        if (TERMS_USER_IP !== '') payload.ip = TERMS_USER_IP;

        console.log('[TERMS][CHECK][POST] -> /fulmuv/terminos/product/check', payload);
        return $.ajax({
            url: '../api/v1/fulmuv/terminos/product/check',
            method: 'POST',
            data: payload,
            dataType: 'json',
            cache: false
        });
    }

    function apiAcceptProductTerms(productId) {
        const payload = {
            product_id: productId,
            version: TERMS_VERSION,
            ip: TERMS_USER_IP,
            user_agent: navigator.userAgent,
            source_page: location.pathname
        };
        console.log('[TERMS][ACCEPT][POST] -> /fulmuv/terminos/product/accept', payload);
        return $.ajax({
            url: '../api/v1/fulmuv/terminos/product/accept',
            method: 'POST',
            data: payload,
            dataType: 'json',
            cache: false
        });
    }

    // Modal controls
    function openTerminosModal(productId, targetType = 'producto') {
        $('#terminos-product-id').val(productId);
        $('#terminos-target-type').val(targetType);
        $('#chk-acepto-terminos').prop('checked', false);
        $('#modal-terminos-producto-overlay').css('display', 'flex');
    }

    function closeTerminosModal() {
        $('#modal-terminos-producto-overlay').hide();
    }

    // Eventos modal
    $(document).on('click', '#btn-cancelar-terminos', function() {
        closeTerminosModal();
    });
    $(document).on('click', '#btn-aceptar-terminos', async function(e) {
        e.preventDefault();
        const $btn = $(this);

        // evita dobles clics
        if ($btn.data('loading')) return;

        const productId = Number($('#terminos-product-id').val());
        const targetType = ($('#terminos-target-type').val() || 'producto').toString();
        if (!productId) {
            console.warn('[TERMS] productId vacío al aceptar');
            return;
        }
        if (!$('#chk-acepto-terminos').is(':checked')) {
            await Swal.fire({
                title: 'FULMUV',
                text: 'Debes aceptar los términos para continuar.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#111827',
                allowOutsideClick: false,
                heightAuto: false,
                didOpen: () => {
                    const c = document.querySelector('.swal2-container');
                    if (c) c.style.zIndex = 200000;
                }
            });
            return;
        }

        // loading UI
        const originalText = $btn.text();
        $btn.data('loading', true).prop('disabled', true).css('opacity', '0.6').text('Guardando...');

        try {
            const res = await apiAcceptProductTerms(productId);
            console.log('[TERMS][ACCEPT][RES]=', res);

            if (res && res.error === false) {
                saveAcceptedGeneralTermsLocally();

                // cerrar modal y redirigir
                $('#modal-terminos-producto-overlay').hide();
                // agradecimiento y continuación
                await Swal.fire({
                    title: 'FULMUV',
                    html: '¡Gracias por aceptar los términos y condiciones!<br>Ahora podrás ver los detalles de los productos.',
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#111827',
                    allowOutsideClick: false,
                    heightAuto: false,
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.zIndex = 200000; // aseguramos que esté por delante del modal
                    }
                });
                if (targetType === 'vehiculo') {
                    redirigirVehiculoDetalle(productId);
                } else {
                    const acceptedDetailPath = window.APP_MODE_CONFIG?.sinCuenta ?
                        (window.APP_MODE_CONFIG?.productDetailPath || "detalle_producto_sincuenta.php") :
                        "detalle_productos.php";
                    window.location.href = buildAppDetailUrl(acceptedDetailPath, 'q', productId);
                }
            } else {
                throw new Error(res?.msg || 'No se pudo registrar la aceptación.');
            }
        } catch (err) {
            console.warn('[TERMS][ACCEPT][FAIL]', err);
            saveAcceptedGeneralTermsLocally();
            $('#modal-terminos-producto-overlay').hide();
            if (targetType === 'vehiculo') {
                redirigirVehiculoDetalle(productId);
            } else {
                const acceptedDetailPath = window.APP_MODE_CONFIG?.sinCuenta ?
                    (window.APP_MODE_CONFIG?.productDetailPath || "detalle_producto_sincuenta.php") :
                    "detalle_productos.php";
                window.location.href = buildAppDetailUrl(acceptedDetailPath, 'q', productId);
            }

        } finally {
            // restaurar botón si algo falla
            $btn.data('loading', false).prop('disabled', false).css('opacity', '1').text(originalText);
        }
    });


    async function irADetalleProductoConTerminos(id_producto) {
        if (hasAcceptedGeneralTermsLocally()) {
            redirigirProductoDetalle(id_producto);
            return;
        }
        try {
            const res = await apiCheckProductTerms(id_producto);
            console.log('[TERMS][CHECK][RES]=', res);
            if (res && !res.error && res.exists) {
                saveAcceptedGeneralTermsLocally();
                redirigirProductoDetalle(id_producto);
            } else {
                openTerminosModal(id_producto, 'producto');
            }
        } catch (e) {
            console.warn('[TERMS][CHECK][FAIL]', e);
            openTerminosModal(id_producto, 'producto');
        }
    }

    async function irADetalleVehiculoConTerminos(id_vehiculo) {
        if (hasAcceptedGeneralTermsLocally()) {
            redirigirVehiculoDetalle(id_vehiculo);
            return;
        }
        try {
            const res = await apiCheckProductTerms(id_vehiculo);
            console.log('[TERMS][CHECK][RES]=', res);
            if (res && !res.error && res.exists) {
                saveAcceptedGeneralTermsLocally();
                redirigirVehiculoDetalle(id_vehiculo);
            } else {
                openTerminosModal(id_vehiculo, 'vehiculo');
            }
        } catch (e) {
            console.warn('[TERMS][CHECK][FAIL]', e);
            openTerminosModal(id_vehiculo, 'vehiculo');
        }
    }

      function irADetalleEventoConTerminos(id_evento) {

        if (window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('navegarEventoDetalle', id_evento);
        }
    }

    function setCantidadCarrito(id, nuevaCantidad) {
        let carritoData = {};
        try {
            carritoData = JSON.parse(localStorage.getItem("carrito")) || {};
        } catch (_) {}
        const now = new Date().getTime();
        const carrito = Array.isArray(carritoData.data) ? carritoData.data : [];

        const idx = carrito.findIndex(p => String(p.id) === String(id));
        if (idx === -1) return;

        let c = parseInt(nuevaCantidad, 10);
        if (!Number.isFinite(c) || c < 1) c = 1;

        carrito[idx].cantidad = c;

        localStorage.setItem("carrito", JSON.stringify({
            data: carrito,
            timestamp: now
        }));
        actualizarIconoCarrito();
    }

    // solo números
    $(document).on("input", ".input-qty-mini", function() {
        this.value = this.value.replace(/[^\d]/g, '');
    });

    // cuando termina de editar
    $(document).on("change", ".input-qty-mini", function() {
        const id = $(this).data("id");
        setCantidadCarrito(id, $(this).val());
    });

    // Enter aplica
    $(document).on("keydown", ".input-qty-mini", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            const id = $(this).data("id");
            setCantidadCarrito(id, $(this).val());
            this.blur();
        }
    });

    // Función para enviar los datos a Flutter
    function abrirProductosTienda(idEmpresa) {
        const vendorPath = window.APP_MODE_CONFIG?.sinCuenta ?
            (window.APP_MODE_CONFIG?.vendorProductsPath || 'productos_vendor_sincuenta.php') :
            'productos_vendor.php';

        if (!window.APP_MODE_CONFIG?.sinCuenta && window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('navToVendor', idEmpresa);
        } else {
            window.location.href = `${vendorPath}?q=${idEmpresa}`;
        }
    };

    // ✅ Función de actualización con puente a Flutter
    function actualizarIconoCarrito() {
        let carrito = [];
        try {
            const stored = JSON.parse(localStorage.getItem("carrito"));
            const now = new Date().getTime();

            if (stored && Array.isArray(stored.data)) {
                carrito = stored.data;
                // Limpieza por expiración (2 horas)
                if (stored.timestamp && now - stored.timestamp >= 2 * 60 * 60 * 1000) {
                    localStorage.removeItem("carrito");
                    carrito = [];
                }
            }
        } catch (e) {
            console.warn("Error en el carrito:", e);
        }

        const totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);

        // ✅ NOTIFICAR A FLUTTER (Nativo)
        if (window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('updateCartBadge', {
                'totalItems': totalItems,
                'items': carrito
            });
        }

        // Actualización visual de la web (Tu código original)
        $(".contadorCarrito").text(totalItems);
        // ... (resto de tu lógica de renderizado de listaProductoCarrito) ...
    }

    // ✅ Estas funciones deben ser globales para que Flutter pueda llamarlas
    window.modificarCantidadDesdeFlutter = function(id, cambio) {
        modificarCantidadCarrito(id, cambio);
    };

    window.eliminarDesdeFlutter = function(id) {
        eliminarDelCarrito(id);
    };
</script>
