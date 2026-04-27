<?php
include 'includes/header.php';
?>
<link rel="canonical" href="https://fulmuv.com/anuncia_fulmuv.php">

<style>
    :root {
        --af-page-bg: #f5f7fb;
        --af-surface: #ffffff;
        --af-surface-soft: #eef3f8;
        --af-border: rgba(15, 23, 42, 0.08);
        --af-text: #0f172a;
        --af-text-secondary: #64748b;
        --af-accent: #004e60;
        --af-accent-2: #0f766e;
        --af-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    body,
    body .main.pages,
    body .page-content {
        background: var(--af-page-bg);
        color: var(--af-text);
    }

    .fulmuv-fullscreen {
        min-height: 100vh;
        display: flex;
        align-items: center;
        background:
            radial-gradient(circle at top left, rgba(15, 118, 110, 0.12), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #f6f7fb 100%);
    }

    .fulmuv-hero {
        position: relative;
        overflow: hidden;
        padding: 30px 26px;
        border: 1px solid var(--af-border);
        background:
            radial-gradient(circle at top right, rgba(0, 78, 96, 0.10), transparent 34%),
            linear-gradient(135deg, #ffffff 0%, #eef5f8 100%);
        box-shadow: var(--af-shadow);
        margin-bottom: 18px;
    }

    .fulmuv-hero h2 {
        font-size: clamp(30px, 5vw, 46px);
        font-weight: 900;
        margin-bottom: 12px;
        color: var(--af-text);
    }

    .fulmuv-hero p {
        max-width: 760px;
        margin: 0;
        color: var(--af-text-secondary);
        line-height: 1.7;
    }

    #fulmuv-contacto .btn-motivo {
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        min-height: 118px;
        border: 1px solid var(--af-border) !important;
        background: var(--af-surface) !important;
    }

    #fulmuv-contacto .btn-motivo:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
    }

    #fulmuv-contacto .btn-motivo.is-active {
        border-color: var(--af-accent) !important;
        box-shadow: 0 18px 36px rgba(15, 23, 42, .12);
        transform: translateY(-2px);
        background: linear-gradient(135deg, var(--af-accent) 0%, var(--af-accent-2) 100%) !important;
        color: #fff;
    }

    #fulmuv-contacto .btn-motivo.is-active small {
        color: rgba(255, 255, 255, .75) !important;
    }

    #fulmuv-contacto .card {
        border: 1px solid var(--af-border);
        box-shadow: var(--af-shadow);
        background: var(--af-surface);
    }

    #fulmuv-contacto .form-control,
    #fulmuv-contacto textarea {
        border-radius: 16px !important;
        border: 1px solid #dbe4ee;
        min-height: 54px;
        box-shadow: none !important;
    }

    #fulmuv-contacto textarea {
        min-height: 240px;
    }

    #fulmuv-contacto .form-control:focus,
    #fulmuv-contacto textarea:focus {
        border-color: var(--af-accent);
    }

    #btnEnviar {
        background: linear-gradient(135deg, var(--af-accent) 0%, var(--af-accent-2) 100%);
        border: none;
    }

    #btnLimpiar {
        border-color: #cbd5e1;
    }

    @media (max-width: 768px) {
        .fulmuv-fullscreen {
            align-items: flex-start;
        }

        .fulmuv-hero {
            padding: 22px 18px;
        }
    }
</style>

<section id="fulmuv-contacto" class="fulmuv-fullscreen">
    <div class="container py-2">
        <div class="fulmuv-hero">
            <h2>Haz que FULMUV trabaje a favor de tu marca</h2>
            <p>Selecciona el tipo de apoyo que necesitas y comparte tu información. El equipo comercial revisará tu caso y te contactará con una propuesta clara para visibilidad, promoción o acompañamiento.</p>
        </div>

        <!-- ENCABEZADO -->
        <div class="text-center mb-4">
            <h2 class="mb-2">¿Cómo deseas que FULMUV te aporte?</h2>
            <p class="text-muted mb-0">
                Elige una opción y déjanos tus datos. Te contactaremos lo antes posible.
            </p>
        </div>

        <!-- BOTONES (selección) -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <button type="button"
                                class="btn w-100 btn-outline-dark rounded-4 py-3 btn-motivo"
                                data-motivo="Anuncia en FULMUV">
                                <div class="fw-bold">Anuncia en FULMUV</div>
                                <small class="text-muted">Publica tu negocio y servicios</small>
                            </button>
                        </div>

                        <div class="col-md-4">
                            <button type="button"
                                class="btn w-100 btn-outline-dark rounded-4 py-3 btn-motivo"
                                data-motivo="Promociona tus productos en FULMUV">
                                <div class="fw-bold">Promociona tus productos</div>
                                <small class="text-muted">Impulsa tus repuestos y accesorios</small>
                            </button>
                        </div>

                        <div class="col-md-4">
                            <button type="button"
                                class="btn w-100 btn-outline-dark rounded-4 py-3 btn-motivo"
                                data-motivo="Obtén ayuda del equipo FULMUV">
                                <div class="fw-bold text-uppercase">Obtén ayuda</div>
                                <small class="text-muted">Te asesoramos paso a paso</small>
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <span class="badge bg-dark-subtle text-dark px-3 py-2 rounded-pill" id="motivoSeleccionadoLabel">
                            Selecciona una opción para continuar
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULARIO (oculto hasta elegir motivo) -->
        <div class="row justify-content-center mt-4" id="wrapFormulario" style="display:none;">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-lg-5">

                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <h4 class="mb-1">Completa tu información</h4>
                                <p class="text-muted mb-0">
                                    <strong>Nota:</strong> Estamos para aportar a tu desarrollo. Una vez enviada tu información, te contactaremos lo antes posible.
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">Motivo:</div>
                                <div class="fw-bold" id="motivoSeleccionadoTitulo">—</div>
                            </div>
                        </div>

                        <!-- hidden motivo -->
                        <input type="hidden" id="motivoSeleccionado" value="">

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de Empresa <span class="text-danger">*</span></label>
                                <input type="text" id="empresa" class="form-control form-control-lg rounded-4" placeholder="Ingrese el nombre de la empresa" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nombres y Apellidos de Titular <span class="text-danger">*</span></label>
                                <input type="text" id="titular" class="form-control form-control-lg rounded-4" placeholder="Ingrese los nombres y apellidos del titular" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Número de Contacto <span class="text-danger">*</span></label>
                                <input type="text" id="telefono" class="form-control form-control-lg rounded-4" placeholder="Ingrese el número de contacto" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico de Contacto <span class="text-danger">*</span></label>
                                <input type="email" id="correo" class="form-control form-control-lg rounded-4" placeholder="Ingrese el correo electrónico" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Coméntanos cómo deseas que FULMUV te aporte:</label>
                                <textarea id="comentario" rows="5" class="form-control form-control-lg rounded-4"
                                    placeholder="Escribe aquí tu requerimiento..." rows="5" style="height: 272px;"></textarea>
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <button type="button" class="btn btn-outline-secondary rounded-4 px-4" id="btnLimpiar">
                                    Limpiar
                                </button>
                                <button type="button" class="btn btn-primary rounded-4 px-4" id="btnEnviar">
                                    <span class="btn-text">ENVIAR</span>
                                    <span class="btn-spinner d-none ms-2" aria-hidden="true">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- MENSAJE FINAL -->
                <div class="alert alert-success rounded-4 mt-4" id="mensajeExito" style="display:none;">
                    <h5 class="mb-2">Gracias por contactarte con el equipo de FULMUV.</h5>
                    <p class="mb-2">Hemos recibido tu información correctamente.</p>
                    <p class="mb-0">
                        Un miembro de nuestro equipo se pondrá en contacto contigo lo más rápido posible para brindarte toda la asesoría necesaria y acompañarte en el proceso de publicación y promoción de tus productos y/o servicios en la plataforma de especialidad vehicular del país.
                    </p>
                </div>

            </div>
        </div>

    </div>
</section>

<?php
include 'includes/footer.php';
?>
<script src="js/eventos.js"></script>

<script>
    function setBtnLoading($btn, isLoading, textLoading = "Enviando...") {
        const $text = $btn.find(".btn-text");
        const $spinner = $btn.find(".btn-spinner");

        if (isLoading) {
            $btn.prop("disabled", true);
            $text.text(textLoading);
            $spinner.removeClass("d-none");
        } else {
            $btn.prop("disabled", false);
            $text.text("ENVIAR");
            $spinner.addClass("d-none");
        }
    }
    // solo números en teléfono
    $("#telefono").on("input", function() {
        this.value = this.value.replace(/\D/g, '');
    });

    function validarCorreo(correo) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(correo);
    }

    // Selección de motivo
    $(document).on("click", ".btn-motivo", function() {
        const motivo = $(this).data("motivo");

        $(".btn-motivo").removeClass("is-active");
        $(this).addClass("is-active");

        $("#motivoSeleccionado").val(motivo);
        $("#motivoSeleccionadoTitulo").text(motivo);
        $("#motivoSeleccionadoLabel").text("Seleccionado: " + motivo);

        $("#wrapFormulario").slideDown(250);

        // scroll suave al form
        setTimeout(() => {
            document.getElementById("wrapFormulario").scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }, 150);
    });

    // Limpiar
    $("#btnLimpiar").on("click", function() {
        $("#empresa,#titular,#telefono,#correo,#comentario").val("");
    });

    $("#btnEnviar").on("click", function() {

        const motivo = $("#motivoSeleccionado").val().trim();
        const empresa = $("#empresa").val().trim();
        const titular = $("#titular").val().trim();
        const telefono = $("#telefono").val().trim();
        const correo = $("#correo").val().trim();
        const comentario = $("#comentario").val().trim();

        if (!motivo) {
            Swal.fire({
                icon: "warning",
                title: "Selecciona una opción",
                text: "Elige un motivo para continuar."
            });
            return;
        }

        if (!empresa || !titular || !telefono || !correo) {
            Swal.fire({
                icon: "warning",
                title: "Faltan datos",
                text: "Completa los campos obligatorios (*) para continuar."
            });
            return;
        }

        if (!validarCorreo(correo)) {
            Swal.fire({
                icon: "warning",
                title: "Correo no válido",
                text: "Ingresa un correo electrónico válido."
            });
            return;
        }

        const $btn = $("#btnEnviar");
        setBtnLoading($btn, true); // ✅ spinner ON

        $.post("../api/v1/fulmuv/contactoFulmuv/create", {
            motivo: motivo,
            nombre_empresa: empresa,
            titular: titular,
            telefono: telefono,
            correo: correo,
            comentario: comentario
        }, function(resp) {

            if (resp && resp.error) {
                setBtnLoading($btn, false); // ✅ spinner OFF
                Swal.fire({
                    icon: "error",
                    title: "FULMUV",
                    text: resp.msg || "Ocurrió un error al enviar."
                });
                return;
            }

            // ✅ Éxito: quitar loading y refrescar
            setBtnLoading($btn, false);

            Swal.fire({
                icon: "success",
                title: "FULMUV",
                text: "Solicitud enviada con éxito.",
                confirmButtonColor: "#242619",
                confirmButtonText: "OK"
            }).then(() => {
                location.reload(); // 🔄 refresca la página
            });

        }, "json").fail(function() {
            setBtnLoading($btn, false); // ✅ spinner OFF
            Swal.fire({
                icon: "error",
                title: "FULMUV",
                text: "No se pudo enviar tu solicitud. Intenta nuevamente."
            });
        });

    });
</script>