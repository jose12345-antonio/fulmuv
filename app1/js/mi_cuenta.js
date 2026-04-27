let id_usuario = null;

$(document).ready(function () {
    id_usuario = $("#id_usuario").val(); // ahora sí, cuando el DOM ya cargó
    obtenerDatosCuenta();
});

function setBtnLoading($btn, loading, textLoading = "Cargando...") {
    if (loading) {
        if ($btn.data("original") == null) $btn.data("original", $btn.html());
        $btn.prop("disabled", true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${textLoading}`);
    } else {
        $btn.prop("disabled", false);
        $btn.html($btn.data("original"));
    }
}

function obtenerDatosCuenta() {
    // (Opcional) mostrar loading global
    Swal.fire({
        title: "Fulmuv",
        text: "Cargando datos...",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

    $.post("../api/v1/fulmuv/cliente/getClienteById", { id_usuario: id_usuario })
        .done(function (response) {
            const res = (typeof response === "string") ? JSON.parse(response) : response;

            if (!res.error) {
                $("#nombres").val(res.data.nombres || ""); $("#cedula").val(res.data.cedula || "");
                $("#telefono").val(res.data.telefono || "");
                $("#correo").val(res.data.correo || "");
                Swal.close();
            } else {
                Swal.fire("Error", res.msg || "No se pudo cargar los datos.", "error");
            }
        })
        .fail(function () {
            Swal.fire("Error", "No se pudo conectar con el servidor.", "error");
        });
}

function actualizarPerfil() {
    const $btn = $("#btnActualizar");

    const nombres = $("#nombres").val().trim();
    const cedula = $("#cedula").val().trim();
    const telefono = $("#telefono").val().trim();
    const correo = $("#correo").val().trim();

    if (!nombres || !cedula || !telefono || !correo) {
        Swal.fire({ icon: "warning", title: "Campos vacíos", text: "Todos los campos son obligatorios" });
        return;
    }

    // Evitar doble click
    if ($btn.prop("disabled")) return;

    setBtnLoading($btn, true, "Actualizando...");

    $.post("../api/v1/fulmuv/cliente/update_datos", {
        id_usuario: id_usuario,
        nombres: nombres,

        cedula: cedula,
        telefono: telefono,
        correo: correo
    })
        .done(function (response) {
            const res = (typeof response === "string") ? JSON.parse(response) : response;

            if (res.error) {
                Swal.fire("Error", res.msg || "No se pudo actualizar.", "error");
            } else {
                Swal.fire("Éxito", res.msg || "Datos actualizados correctamente", "success");
            }
        })
        .fail(function () {
            Swal.fire("Error", "No se pudo conectar con el servidor.", "error");
        })
        .always(function () {
            setBtnLoading($btn, false);
        });
}
