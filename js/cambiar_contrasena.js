function cambiarcontrasena() {
    const id_usuario = $("#id_usuario").val();
    const password = $("#password").val().trim();
    const RepeatPassword = $("#RepeatPassword").val().trim();

    if (!password || !RepeatPassword) {
        Swal.fire("Campos vacíos", "Todos los campos son obligatorios", "warning");
        return;
    }

    if (password !== RepeatPassword) {
        Swal.fire("Error", "Las nuevas contraseñas no coinciden", "error");
        return;
    }

    const $btn = $("#btnGuardar");
    if ($btn.prop("disabled")) return;

    const originalHtml = $btn.html();

    // Botón con spinner + deshabilitado
    $btn.prop("disabled", true);
    $btn.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Cargando...`);

    // Modal cargando
    Swal.fire({
        title: "Procesando",
        text: "Actualizando contraseña...",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

    $.post("api/v1/fulmuv/cliente/update_password", { // si no funciona, prueba "../api/v1/fulmuv/cliente/update_password"
        id_usuario: id_usuario,
        password: password
    })
    .done(function (response) {
        const res = (typeof response === "string") ? JSON.parse(response) : response;

        if (res.error) {
            Swal.fire("Error", res.msg || "Ocurrió un error", "error");
        } else {
            Swal.fire({
                icon: "success",
                title: "Éxito",
                text: res.msg || "Contraseña actualizada correctamente"
            }).then(() => {
                window.location.href = "login.php"; // recomendado: volver a login
            });

            $("#formCambiarPassword")[0].reset();
        }
    })
    .fail(function () {
        Swal.fire("Error", "No se pudo completar la solicitud. Verifica tu conexión.", "error");
    })
    .always(function () {
        // Restaurar botón
        $btn.prop("disabled", false);
        $btn.html(originalHtml);
    });
}
