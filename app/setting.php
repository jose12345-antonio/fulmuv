<?php
include 'includes/header.php';
$id_usuario = isset($_GET["id_usuario"]) ? $_GET["id_usuario"] : 1;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body {
        background-color: #f4f7f9;
        font-family: 'Quicksand', sans-serif;
    }

    .heading-2 {
        font-weight: 900;
        color: #004E60;
    }

    .profile-card {
        background: #fff;
        border-radius: 20px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        margin-bottom: 18px;
    }

    .profile-avatar {
        width: 60px;
        height: 60px;
        background: #004E60;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 900;
    }

    .settings-list {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .setting-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 18px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
    }

    .setting-label {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        color: #2d3748;
    }

    .setting-label i {
        color: #004E60;
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .social-wrap {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 14px;
    }

    .social-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #004E60;
        font-size: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .btn-logout {
        background: #004E60;
        color: #fff;
        border-radius: 15px;
        width: 100%;
        padding: 13px;
        font-weight: 900;
        border: none;
        margin-top: 20px;
    }

    .offcanvas-bottom {
        border-radius: 25px 25px 0 0;
        height: auto !important;
        padding-bottom: 30px;
    }

    .contact-option {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border-radius: 15px;
        background: #f8fafc;
        margin-bottom: 10px;
        color: #2d3748;
        font-weight: 700;
        text-decoration: none !important;
    }
</style>

<div class="container mt-20 mb-50">

    <div class="profile-card">
        <div class="profile-avatar" id="user-initial">U</div>
        <div class="profile-info">
            <h5 id="user-name" class="m-0 fw-bold">Cargando...</h5>
            <p id="user-email" class="m-0 text-muted small">usuario@fulmuv.com</p>
        </div>
    </div>

    <div class="settings-list">
        <div class="setting-item" onclick="navigateNative('perfil')">
            <div class="setting-label"><i class="bi bi-person-circle"></i> Editar Perfil</div>
            <i class="bi bi-chevron-right text-muted"></i>
        </div>
        <div class="setting-item" onclick="navigateNative('password')">
            <div class="setting-label"><i class="bi bi-shield-lock"></i> Editar Contraseña</div>
            <i class="bi bi-chevron-right text-muted"></i>
        </div>
        <div class="setting-item" onclick="abrirContactoNativo()">
            <div class="setting-label"><i class="bi bi-headset"></i> Contáctanos</div>
            <i class="bi bi-chevron-right text-muted"></i>
        </div>
    </div>

    <p class="text-center text-muted small fw-bold mt-3 mb-2">SÍGUENOS</p>
    <div class="social-wrap">
        <div class="social-circle" onclick="openExternal('https://www.linkedin.com/company/fulmuv/')"><i class="bi bi-linkedin"></i></div>
        <div class="social-circle" onclick="openExternal('https://www.instagram.com/fulmuv')"><i class="bi bi-instagram"></i></div>
        <div class="social-circle" onclick="openExternal('https://www.tiktok.com/@fulmuv')"><i class="bi bi-tiktok"></i></div>
        <div class="social-circle" onclick="openExternal('https://www.facebook.com/share/1HWpn9Wous/')"><i class="bi bi-facebook"></i></div>
    </div>

    <button class="btn-logout" onclick="triggerLogout()">Cerrar sesión</button>
    <a href="javascript:void(0)" onclick="confirmDeleteAccount()" class="btn-delete-acc d-block text-center text-danger mt-3 small fw-bold">Eliminar cuenta</a>
</div>

<div class="offcanvas offcanvas-bottom" tabindex="-1" id="sheetContact">
    <div class="offcanvas-header justify-content-center pb-0">
        <div style="width: 45px; height: 5px; background: #e2e8f0; border-radius: 10px;"></div>
    </div>
    <div class="offcanvas-body">
        <h5 class="fw-bold text-center mb-4">¿Cómo podemos ayudarte?</h5>
        <div class="contact-option" onclick="openExternal('mailto:soporte@fulmuv.com')">
            <div class="avatar-sm bg-primary text-white rounded-circle p-2"><i class="bi bi-envelope"></i></div>
            <span>Enviar Correo</span>
        </div>
        <div class="contact-option" onclick="openExternal('https://wa.me/593900000000')">
            <div class="avatar-sm bg-success text-white rounded-circle p-2"><i class="bi bi-whatsapp"></i></div>
            <span>WhatsApp</span>
        </div>
        <div class="contact-option" onclick="openExternal('tel:+593900000000')">
            <div class="avatar-sm bg-info text-white rounded-circle p-2"><i class="bi bi-telephone"></i></div>
            <span>Llamar ahora</span>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    $(document).ready(function() {
        const idUsuario = "<?php echo $id_usuario; ?>";

        // ✅ OBTENER DATOS DEL CLIENTE PARA EL HEADER
        if (idUsuario) {
            $.post("../api/v1/fulmuv/cliente/getClienteById", {
                id_usuario: idUsuario
            }, function(res) {
                if (!res.error && res.data) {
                    const c = res.data;
                    const nombreFull = c.nombres || 'Usuario FULMUV';
                    $("#user-name").text(nombreFull);
                    $("#user-email").text(c.correo || 'sin-correo@fulmuv.com');

                    // Extraer inicial
                    const inicial = nombreFull.charAt(0);
                    $("#user-initial").text(inicial);
                }
            }, 'json');
        }
    });

    // Función para cerrar sesión mediante Flutter
    function triggerLogout() {
        Swal.fire({
            title: '¿Cerrar sesión?',
            text: "Se borrará tu carrito y datos de acceso.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#004E60',
            confirmButtonText: 'Sí, salir'
        }).then((result) => {
            if (result.isConfirmed) {
                if (window.flutter_inappwebview) {
                    window.flutter_inappwebview.callHandler('logoutApp');
                } else {
                    window.location.href = 'login.php'; // Fallback web
                }
            }
        });
    }

    function confirmDeleteAccount() {
        Swal.fire({
            title: '¿Eliminar tu cuenta?',
            text: "Esta acción es irreversible y perderás tu historial de pedidos. Tu estado pasará a inactivo.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#718096',
            confirmButtonText: 'Sí, eliminar mi cuenta',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar cargando
                Swal.showLoading();

                const idUsuario = $("#id_usuario_session").val();

                $.post("../api/v1/fulmuv/cuenta_cliente/delete", {
                    id_cliente: idUsuario
                }, function(res) {
                    if (!res.error) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cuenta eliminada',
                            text: res.msg,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            // ✅ Una vez eliminada en DB, cerramos sesión en Flutter
                            if (window.flutter_inappwebview) {
                                window.flutter_inappwebview.callHandler('logoutApp');
                            } else {
                                window.location.href = 'login.php';
                            }
                        });
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                }, 'json');
            }
        });
    }

    function openExternal(url) {
        if (window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('openExternalUrl', url);
        } else {
            window.open(url, '_blank');
        }
    }

    // ✅ Función para navegar a pantallas nativas (Perfil/Password)
    function navigateNative(target) {
        if (window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('navToSettings', target);
        }
    }

    // Solución para Contáctanos: Asegurar que el Offcanvas funcione
    $(document).ready(function() {
        var myOffcanvas = document.getElementById('sheetContact');
        var bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas);

        // Si el click nativo de Bootstrap falla, forzamos con jQuery:
        $('[data-bs-target="#sheetContact"]').on('click', function() {
            bsOffcanvas.show();
        });
    });

    function abrirContactoNativo() {
        if (window.flutter_inappwebview) {
            // ✅ Enviamos la orden a Flutter
            window.flutter_inappwebview.callHandler('showContactMenu');
        }
    }
</script>
