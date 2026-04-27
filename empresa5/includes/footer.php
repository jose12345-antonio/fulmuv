    <!-- /contenido de la página -->
    </div><!-- /.fmv-page -->
    </div><!-- /#fmvContent -->
</div><!-- /.fmv-layout -->

<!-- ============================================================
     VENDOR SCRIPTS
     ============================================================ -->
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
<script src="../theme/public/vendors/datatables.net/dataTables.min.js"></script>
<script src="../theme/public/vendors/datatables.net-bs5/dataTables.bootstrap5.min.js"></script>
<script src="../theme/public/vendors/datatables.net-fixedcolumns/dataTables.fixedColumns.min.js"></script>
<script src="../theme/public/vendors/select2/select2.full.min.js"></script>
<script src="../theme/public/vendors/select2/select2.min.js"></script>
<script src="../theme/public/vendors/dropzone/dropzone-min.js"></script>
<script src="../theme/public/vendors/choices/choices.min.js"></script>
<script src="../theme/public/vendors/sweetalert/sweetalert.min.js"></script>

<!-- ============================================================
     FULMUV PANEL — JavaScript principal
     ============================================================ -->
<script>
(function () {
    'use strict';

    /* ----------------------------------------------------------
       SIDEBAR SUBMENU TOGGLE
       ---------------------------------------------------------- */
    window.sbToggleSub = function (subId, btn) {
        var sub = document.getElementById(subId);
        if (!sub) return;
        var isOpen = sub.classList.contains('open');
        sub.classList.toggle('open', !isOpen);
        if (btn) {
            btn.setAttribute('aria-expanded', String(!isOpen));
            btn.classList.toggle('open', !isOpen);
        }
    };

    /* ----------------------------------------------------------
       TEMA: dark / light
       ---------------------------------------------------------- */
    function applyTheme(theme) {
        var isDark = theme === 'dark';
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
        localStorage.setItem('theme', theme);

        var sunIcon  = document.getElementById('tbIconSun');
        var moonIcon = document.getElementById('tbIconMoon');
        if (sunIcon)  sunIcon.style.display  = isDark ? 'none'  : 'block';
        if (moonIcon) moonIcon.style.display = isDark ? 'block' : 'none';
    }

    function initTheme() {
        var current = document.documentElement.getAttribute('data-bs-theme')
                   || localStorage.getItem('theme')
                   || 'light';
        applyTheme(current);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('tbThemeBtn');
        if (btn) {
            btn.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
                applyTheme(current === 'dark' ? 'light' : 'dark');
            });
        }
    });

    /* ----------------------------------------------------------
       SIDEBAR COLLAPSE (desktop) — contraer / expandir
       ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        var toggleBtn = document.getElementById('sbToggleBtn');
        var layout    = document.getElementById('fmvLayout');
        if (!toggleBtn || !layout) return;

        var STORAGE_KEY = 'fmv_sb_collapsed';
        // Restaurar estado guardado
        if (localStorage.getItem(STORAGE_KEY) === '1' && window.innerWidth >= 1200) {
            layout.classList.add('sb-collapsed');
        }

        toggleBtn.addEventListener('click', function () {
            layout.classList.toggle('sb-collapsed');
            var isCollapsed = layout.classList.contains('sb-collapsed');
            localStorage.setItem(STORAGE_KEY, isCollapsed ? '1' : '0');
        });
    });

    /* ----------------------------------------------------------
       SIDEBAR MOBILE: toggle + overlay
       ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        var hamburger = document.getElementById('tbHamburger');
        var sidebar   = document.getElementById('fmvSidebar');
        var overlay   = document.getElementById('fmvOverlay');

        function openSidebar() {
            if (sidebar)   sidebar.classList.add('mobile-open');
            if (overlay)   overlay.classList.add('show');
            if (hamburger) hamburger.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar)   sidebar.classList.remove('mobile-open');
            if (overlay)   overlay.classList.remove('show');
            if (hamburger) hamburger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        if (hamburger) {
            hamburger.addEventListener('click', function () {
                var isOpen = sidebar && sidebar.classList.contains('mobile-open');
                isOpen ? closeSidebar() : openSidebar();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Cerrar sidebar mobile al hacer clic en un nav-link
        if (sidebar) {
            sidebar.querySelectorAll('.sb-nav-link:not(.has-sub)').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 1200) closeSidebar();
                });
            });
        }

        // Limpiar estado al volver a desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1200) {
                closeSidebar();
                document.body.style.overflow = '';
            }
        });
    });

    /* ----------------------------------------------------------
       IMAGEN DE EMPRESA / PERFIL (carga dinámica)
       ---------------------------------------------------------- */
    function cargarImagenPerfil() {
        var idEmpresa = document.getElementById('id_empresa')?.value;
        var tipoUser  = document.getElementById('tipo_user')?.value;
        if (!idEmpresa) return;

        $.ajax({
            url: '../api/v1/fulmuv/empresas/' + idEmpresa,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.error) return;
                var rutaImagen = tipoUser === 'empresa'
                    ? response.data?.img_path
                    : response.data?.imagen;
                if (rutaImagen) {
                    document.querySelectorAll('.img-perfil-dinamica').forEach(function (img) {
                        img.setAttribute('src', rutaImagen);
                    });
                }
            }
        });
    }

    /* ----------------------------------------------------------
       SOPORTE: mail + whatsapp (construye URLs dinámicamente)
       ---------------------------------------------------------- */
    function construirURLsSoporte(nombreEmpresa) {
        var email  = document.getElementById('fulmuv_support_email')?.value    || 'gestiones@fulmuv.com';
        var wa     = (document.getElementById('fulmuv_support_whatsapp')?.value || '593992744454').replace(/\D/g, '');
        var idEmp  = document.getElementById('id_empresa')?.value || '';
        var nombre = (nombreEmpresa || '').trim() || ('empresa #' + idEmp);
        var mensaje  = 'Hola FULMUV, soy empresa ' + nombre + ' y requiero: ';
        var asunto   = 'Contacto desde panel FULMUV - ' + nombre;

        var btnMail = document.getElementById('btnSupportMail');
        var btnWa   = document.getElementById('btnSupportWhatsapp');

        if (btnMail) {
            var mailto = 'mailto:' + email + '?subject=' + encodeURIComponent(asunto) + '&body=' + encodeURIComponent(mensaje);
            btnMail.setAttribute('href', mailto);
        }
        if (btnWa) {
            btnWa.setAttribute('href', 'https://wa.me/' + wa + '?text=' + encodeURIComponent(mensaje));
        }
    }

    /* ----------------------------------------------------------
       MODAL CONFIGURACIÓN
       ---------------------------------------------------------- */
    function abrirConfiguracion() {
        var modal = new bootstrap.Modal(document.getElementById('modalConfiguracion'));

        var usernameInput = document.getElementById('usernameConfig');
        var idInput       = document.getElementById('idUsuariosConfig');
        var imgModal      = document.getElementById('imagenConfigModal');
        var imgPrincipal  = document.getElementById('imagen_principal')?.value || '../img/FULMUV-LOGO-60X60.png';
        var username      = document.getElementById('username_principal')?.value || '';
        var idPrincipal   = document.getElementById('id_principal')?.value || '';

        if (usernameInput) usernameInput.value = username;
        if (idInput)       idInput.value = idPrincipal;
        if (imgModal)      imgModal.setAttribute('src', imgPrincipal);

        var p1 = document.getElementById('passwordConfig');
        var p2 = document.getElementById('passwordConfig2');
        if (p1) p1.value = '';
        if (p2) p2.value = '';

        modal.show();
    }

    function fmvUpdatePassword() {
        var pass1  = document.getElementById('passwordConfig')?.value  || '';
        var pass2  = document.getElementById('passwordConfig2')?.value || '';
        var idUser = document.getElementById('idUsuariosConfig')?.value || '';

        if (!pass1 || !pass2) {
            swal('Error', 'Todos los campos son requeridos.', 'error');
            return;
        }
        if (pass1 !== pass2) {
            swal('Error', 'Las contraseñas no coinciden.', 'error');
            return;
        }

        swal({
            title: 'Confirmar cambio',
            text: '¿Estás seguro de que quieres actualizar la contraseña?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#00686f'
        }, function (isConfirm) {
            if (!isConfirm) return;

            $.post('../api/v1/fulmuv/usuarios/updatepass', { pass: pass1, id_usuario: idUser }, function (data) {
                var returned = typeof data === 'string' ? JSON.parse(data) : data;
                if (returned.error === false) {
                    swal('Actualizado', returned.msg || 'Contraseña actualizada correctamente.', 'success');
                    var p1 = document.getElementById('passwordConfig');
                    var p2 = document.getElementById('passwordConfig2');
                    if (p1) p1.value = '';
                    if (p2) p2.value = '';
                    bootstrap.Modal.getInstance(document.getElementById('modalConfiguracion'))?.hide();
                } else {
                    swal('Error', returned.msg || 'No se pudo actualizar.', 'error');
                }
            });
        });
    }

    /* ----------------------------------------------------------
       MODAL DARSE DE BAJA
       ---------------------------------------------------------- */
    function abrirModal1Baja(idEmpresa) {
        document.getElementById('style-baja-modal')?.remove();
        document.getElementById('bajaWrap')?.remove();

        swal({
            title: '¿Quieres darte de baja de FULMUV?',
            text: 'Si confirmas tu baja, dejaremos de cobrarte al finalizar tu plan vigente.\n\nElige cómo quieres que funcione tu salida:',
            type: 'warning',
            html: true,
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar',
            closeOnConfirm: false
        }, function (isConfirm) {
            if (!isConfirm) return;
            var elegido = document.querySelector('input[name="modo_baja"]:checked')?.value;
            if (!elegido) {
                swal('Falta selección', 'Selecciona una opción para continuar.', 'warning');
                return;
            }
            swal.close();
            setTimeout(function () { abrirModal2Baja(idEmpresa, elegido); }, 100);
        });

        setTimeout(function () {
            var container = document.querySelector('.sweet-alert');
            if (!container) return;

            var style = document.createElement('style');
            style.id = 'style-baja-modal';
            style.innerHTML = [
                '.baja-wrap{text-align:left;margin-top:12px}',
                '.baja-card{border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin:10px 0;',
                '  cursor:pointer;display:flex;gap:12px;align-items:flex-start;transition:.15s ease;background:#fff}',
                '.baja-card:hover{background:#f8fafc}',
                '.baja-radio{width:18px;height:18px;margin-top:2px;cursor:pointer}',
                '.baja-title{font-weight:700;margin-bottom:4px;color:#333;font-size:13px}',
                '.baja-desc{color:#475569;font-size:12px;line-height:1.4}',
                '.baja-card.is-selected{border-color:#00686f;background:#f0faf9;box-shadow:0 0 0 3px rgba(0,104,111,.15)}'
            ].join('');
            document.head.appendChild(style);

            var html = [
                '<div class="baja-wrap" id="bajaWrap">',
                '  <div class="baja-card is-selected" data-value="VISIBLE_HASTA_FIN_PLAN">',
                '    <input class="baja-radio" type="radio" name="modo_baja" value="VISIBLE_HASTA_FIN_PLAN" checked>',
                '    <div><div class="baja-title">1) Seguir visible hasta que termine mi plan actual</div>',
                '    <div class="baja-desc">Tu perfil y catálogo siguen activos hasta la fecha de finalización.</div></div>',
                '  </div>',
                '  <div class="baja-card" data-value="OCULTAR_INMEDIATO">',
                '    <input class="baja-radio" type="radio" name="modo_baja" value="OCULTAR_INMEDIATO">',
                '    <div><div class="baja-title">2) Ocultar mi catálogo e información de inmediato</div>',
                '    <div class="baja-desc">Se oculta desde ahora. La suscripción se mantiene solo para facturación.</div></div>',
                '  </div>',
                '</div>'
            ].join('');

            var p = container.querySelector('p');
            if (p) p.insertAdjacentHTML('afterend', html);

            var wrap = container.querySelector('#bajaWrap');
            if (wrap) {
                wrap.addEventListener('click', function (e) {
                    var card = e.target.closest('.baja-card');
                    if (!card) return;
                    wrap.querySelectorAll('.baja-card').forEach(function (c) { c.classList.remove('is-selected'); });
                    card.classList.add('is-selected');
                    card.querySelector('input').checked = true;
                });
            }
        }, 120);
    }

    function abrirModal2Baja(idEmpresa, modo) {
        document.getElementById('bajaWrap')?.remove();
        var desc = modo === 'OCULTAR_INMEDIATO'
            ? '<b>Ocultar mi catálogo e información de inmediato</b>'
            : '<b>Seguir visible hasta que termine mi plan actual</b>';

        swal({
            title: 'Confirmar baja',
            text: '<div style="text-align:left;margin-top:12px;border-top:1px solid #eee;padding-top:10px;">' +
                  '<p>Recuerda que cuando decidas regresar, no tendrás que volver a cargar tus catálogos.</p>' +
                  '<p>Elegiste: ' + desc + '</p>' +
                  '<p style="margin-top:10px"><b>¿Confirmas tu baja definitiva?</b></p></div>',
            type: 'warning',
            html: true,
            showCancelButton: true,
            confirmButtonText: 'Sí, darme de baja',
            cancelButtonText: 'Volver atrás',
            closeOnConfirm: false
        }, function (isConfirm) {
            if (isConfirm) {
                ejecutarBaja(idEmpresa, modo);
            } else {
                swal.close();
                setTimeout(function () { abrirModal1Baja(idEmpresa); }, 100);
            }
        });
    }

    function ejecutarBaja(idEmpresa, modo) {
        swal({ title: 'Procesando...', text: 'Registrando tu baja. Por favor espera.', type: 'info', html: true, showConfirmButton: false });

        $.post('../api/v1/fulmuv/empresa/darsebaja', { id_empresa: idEmpresa, modo_baja: modo }, function (data) {
            var returned = typeof data === 'string' ? JSON.parse(data) : data;
            if (returned && returned.error === false) {
                swal({ title: 'Listo', text: returned.msg || 'Tu solicitud de baja fue registrada.', type: 'success', html: true },
                     function () { window.location.href = 'login.php'; });
            } else {
                swal('No se pudo completar', (returned && returned.msg) ? returned.msg : 'Intenta nuevamente.', 'error');
            }
        }).fail(function () {
            swal('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
        });
    }

    /* ----------------------------------------------------------
       INICIALIZACIÓN AL CARGAR EL DOM
       ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {

        // 1. Cargar imagen de perfil
        cargarImagenPerfil();

        // 2. Soporte (primero sin nombre, luego con nombre real de la empresa)
        construirURLsSoporte('');
        var idEmpresa = document.getElementById('id_empresa')?.value;
        if (idEmpresa) {
            $.getJSON('../api/v1/fulmuv/empresas/' + idEmpresa, function (data) {
                if (!data.error && data.data) construirURLsSoporte(data.data.nombre || '');
            });
        }

        // 3. Botón "Configuración"
        document.getElementById('btnAbrirConfiguracion')?.addEventListener('click', function (e) {
            e.preventDefault();
            abrirConfiguracion();
        });

        // 4. Ver/ocultar contraseña en modal config
        document.getElementById('verPasswordConfig')?.addEventListener('change', function () {
            var type = this.checked ? 'text' : 'password';
            var p1 = document.getElementById('passwordConfig');
            var p2 = document.getElementById('passwordConfig2');
            if (p1) p1.type = type;
            if (p2) p2.type = type;
        });

        // 5. Upload imagen de perfil desde modal config
        document.getElementById('imgFileInput')?.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var idUser = document.getElementById('id_principal')?.value;
            var fd = new FormData();
            fd.append('img', file);
            fd.append('id_usuario', idUser);

            $.ajax({
                url: '../api/v1/fulmuv/usuarios/updateImagen',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (data) {
                    var returned = typeof data === 'string' ? JSON.parse(data) : data;
                    if (returned.error === false) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            document.querySelectorAll('.img-perfil-dinamica').forEach(function (img) {
                                img.setAttribute('src', e.target.result);
                            });
                            var imgModal = document.getElementById('imagenConfigModal');
                            if (imgModal) imgModal.setAttribute('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                        toastr.success(returned.msg || 'Imagen actualizada.');
                    } else {
                        toastr.error(returned.msg || 'Error al actualizar imagen.');
                    }
                },
                error: function () { toastr.error('Error de conexión al subir imagen.'); }
            });
        });

        // 6. Botón "Darme de baja"
        document.getElementById('btnBajaFulmuv')?.addEventListener('click', function (e) {
            e.preventDefault();
            var id = document.getElementById('id_empresa')?.value;
            if (!id) { swal('Error', 'No se encontró el ID de empresa.', 'warning'); return; }
            abrirModal1Baja(id);
        });

        // 7. Soporte: abrir mailto correctamente en Safari/mobile
        document.getElementById('btnSupportMail')?.addEventListener('click', function (e) {
            e.preventDefault();
            var href = this.getAttribute('href');
            if (href && href !== '#') window.location.href = href;
        });

    });

    // Exponer funciones necesarias globalmente
    window.fmvUpdatePassword = fmvUpdatePassword;
    window.applyTheme = applyTheme;

})();
</script>

</body>
</html>
