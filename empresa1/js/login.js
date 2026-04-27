const password = document.getElementById("password");
var id_membresia_seleccionada;
var id_usuario_devuelto;
var id_empresa_devuelto;
var costo_seleccionado = 0;
let username_guardado;
let membresiasData = [];

let tipo_pago = 'corriente';  // 'corriente' | 'sin_interes' | 'con_interes'
let meses_pago = 0;

// Meses disponibles por tipo
const MESES_POR_TIPO = {
  sin_interes: [{ v: 3, t: '3 meses' }],
  con_interes: [{ v: 6, t: '6 meses' }, { v: 9, t: '9 meses' }]
};

const __TIPO_PAGO_MAP = {
  corriente: 0,
  sin_interes: 3,
  con_interes: 2
};

document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("password");
  const btn = document.getElementById("togglePassword");
  const icon = document.getElementById("iconEye");

  btn.addEventListener("click", () => {
    const isPass = input.getAttribute("type") === "password";
    input.setAttribute("type", isPass ? "text" : "password");

    // cambia ícono
    if (icon) {
      icon.className = isPass ? "fi-rs-eye-crossed" : "fi-rs-eye";
    }

    btn.setAttribute("aria-label", isPass ? "Ocultar contraseña" : "Mostrar contraseña");
  });
});

function __mapTipoPagoCode(tipo) {
  // fallback a corriente (0) si viene undefined o extraño
  return __TIPO_PAGO_MAP[tipo] ?? 0;
}

function __normalizarMeses(tipo, meses) {
  // Si es corriente o no hay meses, envía vacío
  if (tipo === 'corriente' || !meses || Number(meses) === 0) return "";
  return Number(meses);
}

$(document).ready(function () {
  if ($("#acceso").val()) {
    var user = $("#username_new").val()
    logear2(user);
  }

  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      membresiasData = returned.data;
    }
  });

});


function logear() {
  var username = $("#username").val()
  var password = $("#password").val()
  if (username == "" || password == "") {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Error",
      text: "Campo de usuario y/o contraseña obligatorios.",
      showConfirmButton: true,
    });
  } else {
    $.post('../api/v1/fulmuv/admin/login', {
      username: $("#username").val(),
      password: $("#password").val()
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == true) {
        Swal.fire({
          position: "center",
          icon: "error",
          title: "Error",
          text: returned["msg"],
          showConfirmButton: true,
        });
      } else {
        // Rellenar el formulario oculto
        $("#formlogin").append(`
          <input type='hidden' name='id_usuario' value='${returned.administrador.id_usuario}'/>
          <input type='hidden' name='rol_id' value='${returned.administrador.rol_id}' />
          <input type='hidden' name='username' value='${returned.administrador.nombre_usuario}' />
          <input type='hidden' name='correo' value='${returned.administrador.correo}' />
          <input type='hidden' name='nombres' value='${returned.administrador.nombres}' />
          <input type='hidden' name='imagen' value='${returned.administrador.imagen}' />
          <input type='hidden' name='nombre_rol_user' value='${returned.administrador.nombre_rol_user}' />
          <input type='hidden' name='permisos' value='${JSON.stringify(returned.permisos)}' />
          <input type='hidden' name='id_empresa' value='${returned.administrador.id_empresa}' />
          <input type='hidden' name='tipo_user' value='${returned.tipo_user}' />
          <input type='hidden' name='membresia' value='${JSON.stringify(returned.membresia)}' />
        `);

        console.log("Membresía:", returned.membresia);

        // ====== LÓGICA DE MEMBRESÍA ======
        let estado = "SIN_MEMBRESIA";

        if (returned.membresia && typeof returned.membresia === "object") {
          if (returned.membresia.estado_membresia) {
            estado = returned.membresia.estado_membresia; // ACTIVA / VENCIDA / SIN_MEMBRESIA
          }
        }

        if (estado === "ACTIVA") {
          // Todo OK, continúa el login normal
          $("#formlogin").submit();
        } else {
          // No tiene membresía o está vencida → mostrar modal para ir a checkout
          //$("#modalMembresia").modal("show");
          // // Configurar botón de ir al checkout
          // $("#btnIrCheckout").off("click").on("click", function () {
          //   let id_empresa = returned.administrador.id_empresa;
          //   // Aquí rediriges al checkout de membresías
          //   window.location.href = "checkout_membresia.php?id_empresa=" + id_empresa;
          // });
          id_membresia_seleccionada = returned.membresia.id_membresia;
          id_empresa_devuelto = returned.administrador.id_empresa;
          id_usuario_devuelto = returned.administrador.id_usuario;
          costo_seleccionado = returned.membresia.costo;
          username_guardado = returned.administrador.nombre_usuario;
          $("#totalPago").append("$" + returned.membresia.costo);
          tokenizarTarjeta(returned.administrador.correo)
        }
      }
    });
  }
}

function logear2(username) {
  var password = 'bonsai2023*'
  if (username == "" || password == "") {
    Swal.fire({
      position: "center",
      icon: "error",
      title: "Error",
      text: "Campo de usuario y/o contraseña obligatorios.",
      showConfirmButton: true,
    });
  } else {
    $.post('../api/v1/fulmuv/admin/login', {
      username: username,
      password: password
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
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
          <input type='hidden' name='id_usuario' value='${returned.administrador.id_usuario}'/>
          <input type='hidden' name='rol_id' value='${returned.administrador.rol_id}' />
          <input type='hidden' name='username' value='${returned.administrador.nombre_usuario}' />
          <input type='hidden' name='correo' value='${returned.administrador.correo}' />
          <input type='hidden' name='nombres' value='${returned.administrador.nombres}' />
          <input type='hidden' name='imagen' value='${returned.administrador.imagen}' />
          <input type='hidden' name='nombre_rol_user' value='${returned.administrador.nombre_rol_user}' />
          <input type='hidden' name='permisos' value='${JSON.stringify(returned.permisos)}  ' />
          <input type='hidden' name='id_empresa' value='${returned.administrador.id_empresa}' />
          <input type='hidden' name='tipo_user' value='${returned.tipo_user}' />
          <input type='hidden' name='membresia' value='${JSON.stringify(returned.membresia)}' />
        `);
        $("#formlogin").submit();
      }
    });
  }
}

const enterEvent = e => {
  if (e.key === "Enter") {
    logear();
  }
}

password.addEventListener("keyup", enterEvent);

function tokenizarTarjeta(email) {
  $("#modal-pago").modal('show')

  console.log(id_membresia_seleccionada)
  // Configura UI de crédito según el plan elegido:
  configurarUICreditoSegunMembresia(id_membresia_seleccionada);

  // === Variable to use ===
  let environment = 'stg';
  let application_code = 'TESTECUADORSTG-EC-CLIENT'; // Provided by Payment Gateway
  let application_key = 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6'; // Provided by Payment Gateway
  // let environment = 'prod';
  // let application_code = 'FULMUV-PR-EC-CLIENT'; // Provided by Payment Gateway
  // let application_key = '8XJbDPhiJYeezjr92Qr3Tr4tSyC5gH'; // Provided by Payment Gateway
  let submitButton = document.querySelector('#tokenize_btn');
  submitButton.innerText = "Pagar";
  let submitInitialText = submitButton.textContent;
  submitButton.removeAttribute('disabled');
  submitButton.style.display = 'block';
  document.getElementById('tokenize_response').innerHTML = '';


  // Get the required additional data to tokenize card

  let get_tokenize_data = () => {
    let data = {
      locale: 'es',
      user: {
        id: id_empresa_devuelto,
        email: email,
      }, configuration: {
        default_country: 'ECU',
      },
      conf: {
        style_version: 2
      }
    }

    if (data.user.email == '') {
      swal({
        title: "Warning",
        text: "Email inválido, por favor contacte al administrador",
        type: "warning",
        confirmButtonColor: "#f5921e",
        confirmButtonText: "Ok",
        closeOnConfirm: false
      }, function () {
        // window.history.back(-1);
        window.location.reload()

      });
      return
    } else {
      return data
    }

  }

  // === Required callbacks ===
  // Executed when was called 'tokenize' function but the form was not completed.
  let notCompletedFormCallback = message => {

    // SweetAlert("error", message);


    document.getElementById('tokenize_response').innerHTML = `Not completed form: ${message}, Please fill required data`;
    submitButton.innerText = submitInitialText;
    submitButton.removeAttribute('disabled');
  }

  // Executed when was called 'tokenize' and the services response successfully.
  let responseCallback = response => {

    if (response.card) {

      // registrar el token en la bd
      if (response.card.status == "valid") {
        console.log(response)

        guardarToken(response.card.token, response.card.transaction_reference, id_usuario_devuelto, id_empresa_devuelto).then(function (token) {

          // realizar el cobro con token
          debitToken(token, id_usuario_devuelto, id_membresia_seleccionada, id_empresa_devuelto, costo_seleccionado, tipo_pago, meses_pago).then(function (transaction) {
            // registrar el pago eb la bd
            comprarDirecto(id_empresa_devuelto, transaction.id, transaction.authorization_code, "Y", transaction.payment_date)
          }).catch(function (error) {
            console.error(error);
          });

        }).catch(function (error) {
          console.error(error);
        });
      } else {
        SweetAlert("error", "Tajeta rechazada");

      }

    } else if (response.error) {

      // la tarjeta ya existe
      if (response.error.type.includes("Card already added")) {
        // intentar el cobro con token

        // Expresión regular para encontrar el número en el campo "type"
        var regex = /(\d+)/;

        // Extraer el número
        var resultado = regex.exec(response.error.type);

        // Comprobar si se encontró el número y almacenarlo en una variable
        var token = resultado ? resultado[0] : null;

        console.log(response)

        guardarToken(token, null, id_usuario_devuelto, id_empresa_devuelto).then(function (token) {

          // realizar el cobro con token
          debitToken(token, id_usuario_devuelto, id_membresia_seleccionada, id_empresa_devuelto, costo_seleccionado, tipo_pago, meses_pago).then(function (transaction) {
            // registrar el pago eb la bd
            comprarDirecto(id_empresa_devuelto, transaction.id, transaction.authorization_code, "Y", transaction.payment_date)
          }).catch(function (error) {
            console.error(error);
          });

        }).catch(function (error) {
          console.error(error);
        });


      } else {//manejo de algun otro error
        SweetAlert("error", response.error.type + ". " + response.error.help);
      }
    }
    // document.getElementById('tokenize_response').innerHTML = JSON.stringify(response);
    submitButton.style.display = 'none';
    // submitButton.style.display = 'none';
    $("#modal-pago").modal('hide')

  }


  // 2. Instance the [PaymentGateway](#PaymentGateway-class) with the required parameters.
  let pg_sdk = new PaymentGateway(environment, application_code, application_key);

  // 3. Generate the tokenization form with the required data. [generate_tokenize](#generate_tokenize-function)
  // At this point it's when the form is rendered on page.
  pg_sdk.generate_tokenize(get_tokenize_data(), '#tokenize_example', responseCallback, notCompletedFormCallback);

  // 4. Define the event to execute the [tokenize](#tokenize-function) action.
  submitButton.addEventListener('click', event => {
    if (!$('#checkTerminoCondicionesPago').is(':checked')) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Debe aceptar los términos y condiciones.",
      });
      return;
    }
    // Si hay selects visibles y el tipo es diferido, exigir meses
    if (tipo_pago !== 'corriente' && (!meses_pago || meses_pago === 0)) {
      Swal.fire({ icon: "error", title: "Error", text: "Seleccione el número de meses del diferido." });
      return;
    }
    document.getElementById('tokenize_response').innerHTML = '';
    submitButton.innerText = 'Procesando pago...';
    submitButton.setAttribute('disabled', 'disabled');
    pg_sdk.tokenize();
    event.preventDefault();

  });

}

function configurarUICreditoSegunMembresia(id_membresia) {
  const m = (membresiasData || []).find(x => x.id_membresia == id_membresia);
  const { wrapTipo, selectTipo, ayudaTipo, wrapMeses, selectMeses, ayudaMeses, cuotaBox } = refsPago();

  // Reset estado global
  tipo_pago = 'corriente';
  meses_pago = 0;

  // Limpia UI
  selectTipo.innerHTML = '';
  selectMeses.innerHTML = '';
  ayudaTipo.textContent = '';
  ayudaMeses.textContent = '';
  wrapMeses.style.display = 'none';
  selectMeses.disabled = true;
  cuotaBox.style.display = 'none';

  const cfg = obtenerTiposPermitidosPorPlan(m);

  if (!cfg.mostrarSelects || (cfg.tipos || []).length <= 1) {
    // No mostrar selects (solo corriente)
    wrapTipo.style.display = 'none';
    wrapMeses.style.display = 'none';
    return;
  }

  // Mostrar select de tipo
  wrapTipo.style.display = '';
  // Construye opciones de tipo
  const mapText = {
    corriente: 'Corriente',
    sin_interes: 'Diferido sin intereses',
    con_interes: 'Diferido con intereses'
  };
  selectTipo.innerHTML = '';
  cfg.tipos.forEach(t => {
    const o = document.createElement('option');
    o.value = t;
    o.textContent = mapText[t] || t;
    selectTipo.appendChild(o);
  });

  // Selección inicial = corriente
  selectTipo.value = 'corriente';
  ayudaTipo.textContent = 'Selecciona “Diferido” si deseas pagar en cuotas.';
  // Mantén meses oculto hasta que elijan un tipo diferido
  wrapMeses.style.display = 'none';
  selectMeses.disabled = true;

  // Guarda en dataset qué meses permitir para cada tipo (para onTipoChange)
  // Lo guardamos como JSON en atributos para no recalcular:
  selectTipo.dataset.mesesPorTipo = JSON.stringify(cfg.mesesPorTipo || {});
}

function refsPago() {
  return {
    wrapTipo: document.getElementById('wrapperTipo'),
    selectTipo: document.getElementById('selectTipoDiferido'),
    ayudaTipo: document.getElementById('ayudaTipo'),
    wrapMeses: document.getElementById('wrapperMeses'),
    selectMeses: document.getElementById('selectMeses'),
    ayudaMeses: document.getElementById('ayudaMeses'),
    cuotaBox: document.getElementById('cuotaBox'),
    cuotaSpan: document.getElementById('cuotaEstimada'),
    totalSpan: document.getElementById('totalPago')
  };
}

function obtenerTiposPermitidosPorPlan(membresia) {
  const nombre = (membresia?.nombre || '').toLowerCase();
  const dias = String(membresia?.dias_permitidos || '');

  const esFulmuv = /fulmuv/i.test(nombre);
  if (!esFulmuv) {
    // BasicMuv / OneMuv: solo corriente
    return { mostrarSelects: false, tipos: ['corriente'] };
  }

  // FULMUV:
  if (dias === '30') {
    // Mensual: solo corriente
    return { mostrarSelects: false, tipos: ['corriente'] };
  } else if (dias === '180') {
    // Semestral: corriente + sin_interes(3)
    return { mostrarSelects: true, tipos: ['corriente', 'sin_interes'], mesesPorTipo: { sin_interes: [3] } };
  } else if (dias === '365') {
    // Anual: corriente + sin_interes(3) + con_interes(6,9)
    return { mostrarSelects: true, tipos: ['corriente', 'sin_interes', 'con_interes'], mesesPorTipo: { sin_interes: [3], con_interes: [6, 9] } };
  }

  // Por defecto (otros períodos): solo corriente
  return { mostrarSelects: false, tipos: ['corriente'] };
}


// guardar el token del cliente
function guardarToken(token, transaction_reference = null, id_usuario, id_empresa) {
  return new Promise((resolve, reject) => {
    $.post('../api/v1/fulmuv/venta/recurrente/', {
      token: token,
      transaction_reference: transaction_reference,
      id_usuario: id_usuario,
      id_empresa: id_empresa,
    }, function (returnedData) {
      returnedData = JSON.parse(returnedData)
      if (returnedData["error"] == false) {
        resolve(token);//devolver el token para recalizar el cobro
      } else {
        SweetAlert("error", returnedData["msg"]);
        reject("Error en al guardar la tarjeta en la BD");
      }
    });
  });
}

// debito con token
function debitToken(token, id_usuario, id_membresia, id_empresa, valor, tipo_pago_param, meses_param) {
  // if (promoConfig != null) {
  //   valor = 1;
  // }

  // Usa params si llegan, si no usa tus globals existentes
  const tipoSeleccionado = (typeof tipo_pago_param !== 'undefined') ? tipo_pago_param : (typeof tipo_pago !== 'undefined' ? tipo_pago : 'corriente');
  const mesesSeleccionados = (typeof meses_param !== 'undefined') ? meses_param : (typeof meses_pago !== 'undefined' ? meses_pago : 0);

  const tipo_code = __mapTipoPagoCode(tipoSeleccionado);
  const meses_send = __normalizarMeses(tipoSeleccionado, mesesSeleccionados);

  return new Promise((resolve, reject) => {
    $.post('../api/v1/fulmuv/debitToken/', {
      token: token,
      id_usuario: id_usuario,
      id_membresia: id_membresia,
      id_empresa: id_empresa,
      valor: valor,
      tipo_pago: tipo_code,
      meses: meses_send
    }, function (returnedData) {
      returnedData = JSON.parse(returnedData)
      if (returnedData["error"] == false) {
        // devolver la transaccion
        resolve(returnedData["transaction"]);
      } else {
        SweetAlert("error", returnedData["msg"]);
        reject("Error en debitToken");
      }
    });
  });
}

function comprarDirecto(id_empresa, transaction_id, authorization_code, recurrente, payment_date) {
  console.log(id_empresa, transaction_id, authorization_code, recurrente, payment_date, id_membresia_seleccionada, id_usuario_devuelto, costo_seleccionado);

  swal({
    title: "Warning",
    html: true,
    type: "info",
    showCancelButton: false,
    showConfirmButton: false,
    text: `Espere un momento mientras se realiza la operación. <br>
          <div class='spinner-grow text-warning' role='status'>
            <span class='visually-hidden'>Loading...</span>
          </div>
          <div class='spinner-grow text-warning' role='status'>
            <span class='visually-hidden'>Loading...</span>
          </div>
          <div class='spinner-grow text-warning' role='status'>
            <span class='visually-hidden'>Loading...</span>
          </div>`,

  }, function () {
  });

  $.post("../api/v1/fulmuv/empresas/membresiasUpdate", {
    id_membresia: id_membresia_seleccionada,
    id_empresa: id_empresa,
    id_usuario: id_usuario_devuelto,
    pago_valor: costo_seleccionado,
    tipo: "empresa",
    transaction_id: transaction_id,
    authorization_code: authorization_code,
    recurrente: recurrente,
    payment_date: payment_date,
    valor_membresia: costo_seleccionado
  }, function (returnedData) {
    if (!returnedData.error) {
      swal({
        title: "!Pago registrado con éxito!",
        text: "Haz clic en OK para acceder al sistema. \n  Bienvenido a FULMUV",
        icon: "success",
        button: "OK",
      }, function () {
        redirigirConPost("true", username_guardado)
      });
    } else {
      swal({
        title: 'Error al registrar membresía.',
        text: 'Haz clic en OK para intentarlo nuevamente.',
        icon: 'error',
        button: 'OK'
      });
    }
  }, 'json');

}

function redirigirConPost(acceso, username_new) {
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "login.php"; // o la URL que necesites
  form.style.display = "none";

  // Campo acceso
  const input1 = document.createElement("input");
  input1.name = "acceso";
  input1.value = acceso;
  form.appendChild(input1);

  // Campo username_new
  const input2 = document.createElement("input");
  input2.name = "username_new";
  input2.value = username_new;
  form.appendChild(input2);

  document.body.appendChild(form);
  form.submit();
}

function onTipoChange(tipo) {
  const { selectTipo, wrapMeses, selectMeses, ayudaMeses, cuotaBox } = refsPago();
  tipo_pago = tipo;
  meses_pago = 0;
  cuotaBox.style.display = 'none';

  if (tipo === 'corriente') {
    wrapMeses.style.display = 'none';
    selectMeses.innerHTML = '';
    selectMeses.disabled = true;
    ayudaMeses.textContent = '';
    return;
  }

  // Si es diferido, mostrar meses según la configuración del plan actual
  const cfgMeses = JSON.parse(selectTipo.dataset.mesesPorTipo || '{}');
  const mesesPermitidos = (cfgMeses[tipo] || []);

  // Fallback genérico por si no existiera el dataset (no debería)
  let lista = [];
  if (mesesPermitidos.length) {
    lista = mesesPermitidos.map(n => ({ v: n, t: `${n} meses` }));
  } else {
    // Usa mapa general por tipo
    lista = MESES_POR_TIPO[tipo] || [];
  }

  wrapMeses.style.display = '';
  selectMeses.disabled = false;
  selectMeses.innerHTML = '<option value="" selected disabled>Selecciona meses</option>';
  lista.forEach(opt => {
    const o = document.createElement('option');
    o.value = String(opt.v);
    o.textContent = opt.t;
    selectMeses.appendChild(o);
  });

  ayudaMeses.textContent = (tipo === 'sin_interes')
    ? 'Cuotas fijas sin recargo.'
    : 'Cuotas con interés aplicado por la emisora.';
}

function onMesesChange(meses) {
  meses_pago = Number(meses) || 0;

  // Mostrar cuota estimada (visual)
  const { totalSpan, cuotaBox, cuotaSpan } = refsPago();
  const raw = (totalSpan?.textContent || '').replace('$', '').trim();
  const total = Number(raw) || 0;

  if (tipo_pago === 'corriente' || meses_pago <= 0) {
    cuotaBox.style.display = 'none';
    return;
  }
  const base = total / meses_pago;
  cuotaSpan.textContent = '$' + base.toFixed(2);
  cuotaBox.style.display = 'inline';
}