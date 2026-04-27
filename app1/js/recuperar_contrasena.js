function setLoading(btn, isLoading) {
  if (isLoading) {
    btn.prop("disabled", true);
    btn.data("original-text", btn.html());
    btn.html(`
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      Cargando...
    `);
  } else {
    btn.prop("disabled", false);
    const original = btn.data("original-text");
    if (original) btn.html(original);
  }
}

function enviarCorreo() {
  var email = $("#email").val().trim();
  var btn = $("#btnRecuperar");

  // Expresión regular simple para validar formato de email
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (email === "") {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Fulmuv",
      text: "Campo de correo electrónico obligatorio.",
      showConfirmButton: true,
    });
    return;
  }

  if (!emailRegex.test(email)) {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Fulmuv",
      text: "Debe ingresar un correo electrónico válido.",
      showConfirmButton: true,
    });
    return;
  }

  // Activar loading
  setLoading(btn, true);

  $.post('api/v1/fulmuv/cliente/resetPassword', { email: email })
    .done(function (returnedData) {
      // Si tu API a veces ya devuelve JSON, esto evita errores
      let returned;
      try {
        returned = (typeof returnedData === "string") ? JSON.parse(returnedData) : returnedData;
      } catch (e) {
        returned = { error: true, msg: "Respuesta inválida del servidor." };
      }

      console.log(returned);

      if (returned.error === true) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: returned.msg,
        });
      } else {
        Swal.fire({
          icon: "success",
          title: "Fulmuv",
          text: returned.msg,
          showConfirmButton: true,
        }).then(() => {
          window.location.href = "login.php";
        });
      }
    })
    .fail(function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo completar la solicitud. Inténtalo nuevamente.",
      });
    })
    .always(function () {
      // Quitar loading (si redirige, igual no alcanza a verse mucho, pero está bien)
      setLoading(btn, false);
    });
}
