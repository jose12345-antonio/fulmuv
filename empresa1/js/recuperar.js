function enviarCorreo() {
  var email = $("#email").val().trim();

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
  } else if (!emailRegex.test(email)) {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Fulmuv",
      text: "Debe ingresar un correo electrónico válido.",
      showConfirmButton: true,
    });
  } else {
    $.post('../api/v1/fulmuv/admin/resetPassword', { email: email }, function (returnedData) {
      var returned = JSON.parse(returnedData);
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
    });
  }
}
