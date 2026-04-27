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
     FULMUV ADMIN — JavaScript principal
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
       SIDEBAR COLLAPSE (desktop)
       ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        var toggleBtn = document.getElementById('sbToggleBtn');
        var layout    = document.getElementById('fmvLayout');
        if (!toggleBtn || !layout) return;

        var STORAGE_KEY = 'fmv_sb_collapsed';
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
        if (overlay) overlay.addEventListener('click', closeSidebar);

        if (sidebar) {
            sidebar.querySelectorAll('.sb-nav-link:not(.has-sub)').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 1200) closeSidebar();
                });
            });
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1200) {
                closeSidebar();
                document.body.style.overflow = '';
            }
        });
    });

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
       UPLOAD IMAGEN PERFIL (modal configuración)
       ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
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
    });

    /* ----------------------------------------------------------
       SELECTOR GLOBAL EMPRESA / SUCURSAL
       ---------------------------------------------------------- */
    var _SS_EMP_ID   = 'admin_empresa_id';
    var _SS_EMP_NAME = 'admin_empresa_nombre';
    var _SS_SUC_ID   = 'admin_sucursal_id';

    function actualizarBadgeEmpresa(id, nombre) {
        var badge = document.getElementById('admin_empresa_badge');
        var span  = document.getElementById('admin_empresa_nombre');
        if (!badge || !span) return;
        if (id) {
            span.textContent = nombre || ('Empresa #' + id);
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }

    function cargarSucursales(idEmpresa, preselect) {
        var selSuc = document.getElementById('lista_sucursales');
        if (!selSuc) return;
        selSuc.innerHTML = '<option value="">— Todas las sucursales —</option>';
        document.getElementById('id_sucursal_admin').value = '0';

        if (!idEmpresa) return;

        $.get('../api/v1/fulmuv/empresas/' + idEmpresa + '/sucursales', {}, function (data) {
            var returned = typeof data === 'string' ? JSON.parse(data) : data;
            if (!returned || returned.error !== false || !returned.data) return;

            returned.data.forEach(function (suc) {
                var opt = document.createElement('option');
                opt.value = suc.id_sucursal;
                opt.textContent = suc.nombre || suc.ciudad || ('Sucursal ' + suc.id_sucursal);
                selSuc.appendChild(opt);
            });

            if (preselect) {
                selSuc.value = preselect;
                document.getElementById('id_sucursal_admin').value = selSuc.value || '0';
            }
        });
    }

    function initAdminEmpresaSelector() {
        var selEmp = document.getElementById('lista_empresas');
        var selSuc = document.getElementById('lista_sucursales');
        if (!selEmp) return;

        // Bind change handlers before loading data so they fire on restore
        selEmp.addEventListener('change', function () {
            var id   = this.value;
            var name = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
            sessionStorage.setItem(_SS_EMP_ID, id);
            sessionStorage.setItem(_SS_EMP_NAME, name);
            sessionStorage.removeItem(_SS_SUC_ID);
            actualizarBadgeEmpresa(id, name);
            cargarSucursales(id, null);
        });

        if (selSuc) {
            selSuc.addEventListener('change', function () {
                var id = this.value;
                sessionStorage.setItem(_SS_SUC_ID, id);
                document.getElementById('id_sucursal_admin').value = id || '0';
            });
        }

        // Load all empresas from API
        $.get('../api/v1/fulmuv/empresas/', {}, function (data) {
            var returned = typeof data === 'string' ? JSON.parse(data) : data;
            if (!returned || returned.error !== false || !returned.data) return;

            // Clear and repopulate (prevents duplicates if page JS also ran)
            selEmp.innerHTML = '<option value="">— Empresa —</option>';

            returned.data.forEach(function (empresa) {
                var opt = document.createElement('option');
                opt.value = empresa.id_empresa;
                opt.textContent = empresa.nombre;
                selEmp.appendChild(opt);
            });

            // Restore persisted selection
            var savedEmpId  = sessionStorage.getItem(_SS_EMP_ID);
            var savedEmpName = sessionStorage.getItem(_SS_EMP_NAME);
            var savedSucId  = sessionStorage.getItem(_SS_SUC_ID);

            if (savedEmpId && selEmp.querySelector('option[value="' + savedEmpId + '"]')) {
                selEmp.value = savedEmpId;
                actualizarBadgeEmpresa(savedEmpId, savedEmpName);
                cargarSucursales(savedEmpId, savedSucId);
            }

            // Notify page JS that selector is ready
            $(selEmp).trigger('change');
        });
    }

    /* ----------------------------------------------------------
       INICIALIZACIÓN AL CARGAR EL DOM
       ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {

        // 1. Botón "Configuración"
        document.getElementById('btnAbrirConfiguracion')?.addEventListener('click', function (e) {
            e.preventDefault();
            abrirConfiguracion();
        });

        // 2. Ver/ocultar contraseña en modal config
        document.getElementById('verPasswordConfig')?.addEventListener('change', function () {
            var type = this.checked ? 'text' : 'password';
            var p1 = document.getElementById('passwordConfig');
            var p2 = document.getElementById('passwordConfig2');
            if (p1) p1.type = type;
            if (p2) p2.type = type;
        });

        // 3. Inicializar selector empresa / sucursal
        initAdminEmpresaSelector();

    });

    // Exponer globalmente
    window.fmvUpdatePassword = fmvUpdatePassword;
    window.applyTheme = applyTheme;

})();
</script>

</body>
</html>
