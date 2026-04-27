document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".password-toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetId = btn.getAttribute("data-toggle-target");
      const iconId = btn.getAttribute("data-toggle-icon");
      const input = document.getElementById(targetId);
      const icon = document.getElementById(iconId);

      if (!input) return;

      const isPass = input.getAttribute("type") === "password";
      input.setAttribute("type", isPass ? "text" : "password");

      if (icon) {
        icon.className = isPass ? "fi-rs-eye-crossed" : "fi-rs-eye";
      }

      btn.setAttribute("aria-label", isPass ? "Ocultar contraseña" : "Mostrar contraseña");
    });
  });

  $("#showRegisterPanel").on("click", function (e) {
    e.preventDefault();
    $("#loginPanel").addClass("d-none");
    $("#registerPanel").removeClass("d-none");
  });

  $("#showLoginPanel").on("click", function (e) {
    e.preventDefault();
    $("#registerPanel").addClass("d-none");
    $("#loginPanel").removeClass("d-none");
  });

  $(".text-capitalize-input").on("input", function () {
    const cursorPosition = this.selectionStart;
    const normalized = String(this.value || "")
      .toLowerCase()
      .replace(/\s+/g, " ")
      .replace(/^\s/, "")
      .replace(/\b\w/g, function (char) {
        return char.toUpperCase();
      });

    this.value = normalized;
    try {
      this.setSelectionRange(cursorPosition, cursorPosition);
    } catch (e) {}
  });

  $("#btnRegistrarCliente").on("click", registrarCliente);
});
actualizarIconoCarrito();

function logear() {
  var username = $("#correo").val()
  var password = $("#password").val()
  if (username == "" || password == "") {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Error",
      text: "Campo de correo electrónico y/o contraseña obligatorios.",
      showConfirmButton: true,
    });
  } else {
    $.post('api/v1/fulmuv/cliente/login', {
      username: $("#correo").val(),
      password: $("#password").val()
    }, function (returnedData) {
      var returned = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
      if (returned.error == true) {
        Swal.fire({
          position: "center",
          icon: "error",
          title: "Error",
          text: returned["msg"],
          showConfirmButton: true,
        });
      } else {
        $("#formlogin").append(`
          <input type='hidden' name='id_usuario' value='${returned.clientes.id_cliente}'/>
          <input type='hidden' name='correo' value='${returned.clientes.correo}' />
          <input type='hidden' name='nombres' value='${returned.clientes.nombres}' />
          <input type='hidden' name='apellidos' value='${returned.clientes.apellidos}' />
          <input type='hidden' name='cedula' value='${returned.clientes.cedula}' />
          <input type='hidden' name='telefono' value='${returned.clientes.telefono}' />
        `);
        $("#formlogin").submit();
      }
    });
  }
}

function registrarCliente() {
  const nombres = ($("#registro_nombres").val() || "").trim().replace(/\s+/g, " ");
  const correo = ($("#registro_correo").val() || "").trim().toLowerCase();
  const telefono = ($("#registro_telefono").val() || "").trim();
  const password = ($("#registro_password").val() || "").trim();
  const repeatPassword = ($("#registro_password_repeat").val() || "").trim();

  if (!nombres || !correo || !password || !repeatPassword) {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Error",
      text: "Nombre completo, correo, contraseña y repetir contraseña son obligatorios.",
      showConfirmButton: true,
    });
    return;
  }

  if (password !== repeatPassword) {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Error",
      text: "Las contraseñas no coinciden.",
      showConfirmButton: true,
    });
    return;
  }

  if (password.length < 6) {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Error",
      text: "La contraseña debe tener al menos 6 caracteres.",
      showConfirmButton: true,
    });
    return;
  }

  $.post("api/v1/fulmuv/clientes/registro", {
    nombres: nombres,
    correo: correo,
    telefono: telefono,
    password: password
  }, function (response) {
    const returned = typeof response === "string" ? JSON.parse(response) : response;

    if (returned.error) {
      Swal.fire({
        position: "center",
        icon: "error",
        title: "Error",
        text: returned.msg || "No se pudo registrar el cliente.",
        showConfirmButton: true,
      });
      return;
    }

    Swal.fire({
      position: "center",
      icon: "success",
      title: "Registro exitoso",
      text: "Tu cuenta fue creada correctamente. Revisa tu correo para la notificación de acceso.",
      showConfirmButton: true,
    }).then(() => {
      $("#correo").val(correo);
      $("#password").val("");
      $("#formRegistroCliente")[0].reset();
      $("#registerPanel").addClass("d-none");
      $("#loginPanel").removeClass("d-none");
    });
  });
}

const enterEvent = e => {
  if (e.key === "Enter") {
    logear();
  }
}
