<!-- <footer class="footer">
    <div class="row g-0 justify-content-between fs-10 mt-4 mb-3">
        <div class="col-12 col-sm-auto text-center">
            <p class="mb-0 text-600">Thank you for creating with Falcon <span class="d-none d-sm-inline-block">| </span><br class="d-sm-none" /> 2024 &copy; <a href="https://themewagon.com">Themewagon</a></p>
        </div>
        <div class="col-12 col-sm-auto text-center">
            <p class="mb-0 text-600">v3.21.1</p>
        </div>
    </div>
</footer> -->
</div>
<div class="modal fade" id="authentication-modal" tabindex="-1" role="dialog" aria-labelledby="authentication-modal-label" aria-hidden="true">
    <div class="modal-dialog mt-6" role="document">
        <div class="modal-content border-0">
            <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                <div class="position-relative z-1">
                    <h4 class="mb-0 text-white" id="authentication-modal-label">Register</h4>
                    <p class="fs-10 mb-0 text-white">Please create your free Falcon account</p>
                </div>
                <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 px-5">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="modal-auth-name">Name</label>
                        <input class="form-control" type="text" autocomplete="on" id="modal-auth-name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="modal-auth-email">Email address</label>
                        <input class="form-control" type="email" autocomplete="on" id="modal-auth-email" />
                    </div>
                    <div class="row gx-2">
                        <div class="mb-3 col-sm-6">
                            <label class="form-label" for="modal-auth-password">Password</label>
                            <input class="form-control" type="password" autocomplete="on" id="modal-auth-password" />
                        </div>
                        <div class="mb-3 col-sm-6">
                            <label class="form-label" for="modal-auth-confirm-password">Confirm Password</label>
                            <input class="form-control" type="password" autocomplete="on" id="modal-auth-confirm-password" />
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="modal-auth-register-checkbox" />
                        <label class="form-label" for="modal-auth-register-checkbox">I accept the <a href="#!">terms </a>and <a class="white-space-nowrap" href="#!">privacy policy</a></label>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Register</button>
                    </div>
                </form>
                <div class="position-relative mt-5">
                    <hr />
                    <div class="divider-content-center">or register with</div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-sm-6"><a class="btn btn-outline-google-plus btn-sm d-block w-100" href="#"><span class="fab fa-google-plus-g me-2" data-fa-transform="grow-8"></span> google</a></div>
                    <div class="col-sm-6"><a class="btn btn-outline-facebook btn-sm d-block w-100" href="#"><span class="fab fa-facebook-square me-2" data-fa-transform="grow-8"></span> facebook</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</main>
<!-- ===============================================-->
<!--    End of Main Content-->
<!-- ===============================================-->

<div id="alert"></div>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<!-- ===============================================-->
<!--    JavaScripts-->
<!-- ===============================================-->
<script src="../theme/public/vendors/popper/popper.min.js"></script>
<script src="../theme/public/vendors/bootstrap/bootstrap.min.js"></script>
<script src="../theme/public/vendors/anchorjs/anchor.min.js"></script>
<script src="../theme/public/vendors/is/is.min.js"></script>
<script src="../theme/public/vendors/chart/chart.umd.js"></script>
<script src="../theme/public/vendors/leaflet/leaflet.js"></script>
<script src="../theme/public/vendors/leaflet.markercluster/leaflet.markercluster.js"></script>
<script src="../theme/public/vendors/leaflet.tilelayer.colorfilter/leaflet-tilelayer-colorfilter.min.js"></script>
<script src="../theme/public/vendors/countup/countUp.umd.js"></script>
<script src="../theme/public/vendors/echarts/echarts.min.js"></script>
<script src="../theme/public/assets/data/world.js"></script>
<script src="../theme/public/vendors/dayjs/dayjs.min.js"></script>
<script src="../theme/public/vendors/flatpickr/flatpickr.min.js"></script>
<script src="../theme/public/vendors/fontawesome/all.min.js"></script>
<script src="../theme/public/vendors/lodash/lodash.min.js"></script>
<script src="../theme/public/vendors/list.js/list.min.js"></script>
<script src="../theme/public/vendors/tinymce/tinymce.min.js"></script>

<script src="../theme/public/assets/js/theme.js"></script>

<!-- <script src="../theme/public/vendors/jquery/jquery.min.js"></script> -->
<script src="../theme/public/vendors/datatables.net/dataTables.min.js"></script>
<script src="../theme/public/vendors/datatables.net-bs5/dataTables.bootstrap5.min.js"></script>
<script src="../theme/public/vendors/datatables.net-fixedcolumns/dataTables.fixedColumns.min.js"></script>

<script src="../theme/public/vendors/select2/select2.full.min.js"></script>
<script src="../theme/public/vendors/select2/select2.min.js"></script>
<script src="../theme/public/vendors/dropzone/dropzone-min.js"></script>
<script src="../theme/public/vendors/choices/choices.min.js"></script>
<script src="../theme/public/vendors/countup/countUp.umd.js"></script>
<script src="../theme/public/vendors/sweetalert/sweetalert.min.js"></script>

</body>

</html>

<script>
    $(document).ready(function() {
        cargarImagenEmpresa();
    });
    $(document).off("click", "#btnBajaFulmuv").on("click", "#btnBajaFulmuv", function() {
        const id_empresa = $("#id_empresa").val();

        if (!id_empresa) {
            swal({
                title: '<i class="fas fa-exclamation-triangle text-warning"></i> Falta información',
                text: "No se encontró el id_empresa del vendedor.",
                type: "warning",
                html: true
            });
            return;
        }

        abrirModal1Baja(id_empresa);
    });

    function abrirModal1Baja(id_empresa) {
        // 1. LIMPIEZA PREVIA: Eliminar cualquier rastro de estilos o contenedores anteriores
        const existingStyle = document.getElementById("style-baja-modal");
        if (existingStyle) existingStyle.remove();
        const existingWrap = document.getElementById("bajaWrap");
        if (existingWrap) existingWrap.remove();

        swal({
            title: "¿Quieres darte de baja de FULMUV?",
            text: "Si confirmas tu baja, dejaremos de cobrarte al finalizar tu plan vigente.\n\nElige cómo quieres que funcione tu salida:",
            type: "warning",
            html: true,
            showCancelButton: true,
            confirmButtonText: "Continuar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false,
            animation: "slide-from-top"
        }, function(isConfirm) {
            if (!isConfirm) return;

            const elegido = document.querySelector('input[name="modo_baja"]:checked')?.value;
            if (!elegido) {
                swal("Falta selección", "Selecciona una opción para continuar.", "warning");
                return;
            }

            swal.close();
            // Pequeño delay para que la transición de SWAL no se rompa
            setTimeout(() => abrirModal2Confirmacion(id_empresa, elegido), 100);
        });

        setTimeout(() => {
            const container = document.querySelector(".sweet-alert");
            if (!container) return;

            // 2. INYECCIÓN CONTROLADA
            const styleTag = document.createElement('style');
            styleTag.id = "style-baja-modal";
            styleTag.innerHTML = `
            .baja-wrap{ text-align:left; margin-top:12px; }
            .baja-card{
                border:1px solid #e5e7eb; border-radius:10px; padding:12px;
                margin:10px 0; cursor:pointer; display:flex; gap:12px;
                align-items:flex-start; transition:.15s ease; background:#fff;
            }
            .baja-card:hover{ background:#f8fafc; }
            .baja-radio{ width:20px; height:20px; margin-top:2px; cursor:pointer; }
            .baja-title{ font-weight:700; margin-bottom:4px; color: #333; }
            .baja-desc{ color:#475569; font-size:13px; line-height:1.25; }
            .baja-card.is-selected{
                border-color:#0d6efd; background:#f0f7ff;
                box-shadow:0 0 0 3px rgba(13,110,253,.15);
            }
        `;
            document.head.appendChild(styleTag);

            const htmlContent = `
            <div class="baja-wrap" id="bajaWrap">
                <div class="baja-card is-selected" data-value="VISIBLE_HASTA_FIN_PLAN">
                    <input class="baja-radio" type="radio" name="modo_baja" value="VISIBLE_HASTA_FIN_PLAN" checked>
                    <div>
                        <div class="baja-title">1) Seguir visible hasta que termine mi plan actual</div>
                        <div class="baja-desc">Tu perfil y catálogo siguen activos hasta la fecha de finalización del plan.</div>
                    </div>
                </div>
                <div class="baja-card" data-value="OCULTAR_INMEDIATO">
                    <input class="baja-radio" type="radio" name="modo_baja" value="OCULTAR_INMEDIATO">
                    <div>
                        <div class="baja-title">2) Ocultar mi catálogo e información de inmediato</div>
                        <div class="baja-desc">Se oculta desde ahora. La suscripción se mantiene solo para facturación.</div>
                    </div>
                </div>
            </div>
        `;

            const p = container.querySelector("p");
            if (p) p.insertAdjacentHTML("afterend", htmlContent);

            // 3. LOGICA DE SELECCIÓN (Encapsulada)
            const wrap = container.querySelector("#bajaWrap");
            const cards = wrap.querySelectorAll(".baja-card");

            wrap.onclick = (e) => {
                const card = e.target.closest(".baja-card");
                if (!card) return;

                cards.forEach(c => c.classList.remove("is-selected"));
                card.classList.add("is-selected");
                card.querySelector('input').checked = true;
            };
        }, 100);
    }

    function abrirModal2Confirmacion(id_empresa, modo) {
        // Limpieza de cualquier basura visual anterior
        const existingWrap = document.getElementById("bajaWrap");
        if (existingWrap) existingWrap.remove();

        const desc = (modo === "OCULTAR_INMEDIATO") ?
            "<b>Ocultar mi catálogo e información de inmediato</b>" :
            "<b>Seguir visible hasta que termine mi plan actual</b>";

        const contentHtml = `
        <div style="text-align:left; margin-top:15px; border-top:1px solid #eee; padding-top:10px;">
            <p>Recuerda que cuando decidas regresar, no tendrás que volver a cargar tus catálogos.</p>
            <p>Elegiste: ${desc}</p>
            <p style="margin-top:12px"><b>¿Confirmas tu baja definitiva?</b></p>
        </div>
    `;

        swal({
            title: "Confirmar baja",
            text: contentHtml,
            type: "warning",
            html: true,
            showCancelButton: true,
            confirmButtonText: "Sí, darme de baja",
            cancelButtonText: "Volver atrás",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                ejecutarBaja(id_empresa, modo);
            } else {
                // Regresar al modal 1 limpiando el estado
                swal.close();
                setTimeout(() => abrirModal1Baja(id_empresa), 100);
            }
        });
    }

    function ejecutarBaja(id_empresa, modo) {
        swal({
            title: '<i class="fas fa-spinner fa-spin"></i> Procesando...',
            text: "Estamos registrando tu baja. Por favor espera.",
            type: "info",
            html: true,
            showConfirmButton: false
        });

        $.post("../api/v1/fulmuv/empresa/darsebaja", {
            id_empresa: id_empresa,
            modo_baja: modo
        }, function(resp) {
            let returned = resp;
            try {
                if (typeof resp === "string") returned = JSON.parse(resp);
            } catch (e) {}

            if (returned && returned.error === false) {
                swal({
                    title: '<i class="fas fa-check-circle text-success"></i> Listo',
                    text: returned.msg || "Tu solicitud de baja fue registrada correctamente.",
                    type: "success",
                    html: true
                }, function() {
                    window.location.href = "login.php";
                });
            } else {
                swal({
                    title: '<i class="fas fa-times-circle text-danger"></i> No se pudo completar',
                    text: (returned && returned.msg) ? returned.msg : "Intenta nuevamente.",
                    type: "error",
                    html: true
                });
            }
        }).fail(function() {
            swal({
                title: '<i class="fas fa-times-circle text-danger"></i> Error de conexión',
                text: "No se pudo conectar con el servidor. Intenta nuevamente.",
                type: "error",
                html: true
            });
        });
    }

    function cargarImagenEmpresa() {
        // Obtenemos el ID de la empresa del input hidden que ya generas en PHP
        const idEmpresa = $("#id_empresa").val();

        if (idEmpresa) {
            $.ajax({
                url: `../api/v1/fulmuv/empresas/${idEmpresa}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if ($("#tipo_user").val() == "empresa") {
                        if (!response.error && response.data.img_path) {

                            const rutaImagen = response.data.img_path;
                            // Actualizamos todas las imágenes que tengan la clase 'img-perfil-dinamica'
                            $(".img-perfil-dinamica").attr("src", rutaImagen);

                            // Si el modal de configuración está abierto y tiene el ID 'imagen'
                            $("#imagen").attr("src", rutaImagen);

                            console.log("Imagen de empresa cargada desde API:", rutaImagen);
                        } else {
                            console.warn("La empresa no tiene img_path o no existe.");
                        }
                    } else {
                        if (!response.error && response.data.imagen) {

                            const rutaImagen = response.data.imagen;
                            // Actualizamos todas las imágenes que tengan la clase 'img-perfil-dinamica'
                            $(".img-perfil-dinamica").attr("src", rutaImagen);

                            // Si el modal de configuración está abierto y tiene el ID 'imagen'
                            $("#imagen").attr("src", rutaImagen);

                            console.log("Imagen de empresa cargada desde API:", rutaImagen);
                        } else {
                            console.warn("La empresa no tiene img_path o no existe.");
                        }
                    }

                },
                error: function(error) {
                    console.error("Error al consultar la API de empresas:", error);
                }
            });
        }
    }
</script>