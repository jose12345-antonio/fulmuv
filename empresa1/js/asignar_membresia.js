var id_empresa = document.getElementById("id_empresa_detalle")?.value;
var id_usuario = document.getElementById("id_principal")?.value;
let membresiasData = [];
let agentes = [];
let id_membresia_seleccionada = null;
let costo_seleccionado = null;
let groupedMembresias = {};

$(document).ready(function () {
  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      membresiasData = returned.data;
      // renderAllTabs(); // Llenar todos los tabs al cargar
      renderMembresiasSelect(); // Render inicial con selects por card
    }
  });

  $.get('../api/v1/fulmuv/agentes/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      agentes = returned.data;
    }
  });
});

function saveMembresia(id_membresia) {

  const membresiaSeleccionada = membresiasData.find(m => m.id_membresia == id_membresia);
  id_membresia_seleccionada = id_membresia;
  costo_seleccionado = membresiaSeleccionada?.costo || 0;

  mostrarFormulario()

  /*swal({
    title: "Alerta",
    text: "¿Deseas comprar esta membresía?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    swal.close();

    //generar pago
    $.post("../api/v1/webstore/init_reference/", {
      id_membresia: id_membresia,
      id_empresa: id_empresa,
      id_usuario: id_usuario,
      valor: costo_seleccionado
    }, function(returnedData) {

      if (!returnedData.error) {
        paymentCheckout.open({
          reference: returnedData.payment.reference // reference received for Payment Gateway
        });
        window.addEventListener('popstate', function() {
          paymentCheckout.close();
        });
      }

    }, 'json')

  });*/
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

$('#btnAplicarCodigo').on('click', function () {
  const codigo = $('#agente').val().trim();

  if (codigo === "") {
    SweetAlert("error", "Por favor, ingresa un código de agente válido.");
    return;
  }

  const agenteEncontrado = agentes.find(agente =>
    agente.codigo === codigo && agente.estado === "A"
  );

  if (!agenteEncontrado) {
    SweetAlert("error", "El código ingresado no es válido o está inactivo.");
    return;
  }

  // Código válido, actualizar precios de todos los planes según tabla
  membresiasData = membresiasData.map(m => {
    const nombre = m.nombre.toLowerCase();
    const dias = String(m.dias_permitidos);

    if (nombre.includes('onemuv') || nombre.includes('basicmuv')) {
      if (dias === "360") m.costo = 37;
      else if (dias === "180") m.costo = 19;
      else if (dias === "30")  m.costo = 4;
    }

    if (nombre.includes('fulmuv')) {
      if (dias === "360") m.costo = 237;
      else if (dias === "180") m.costo = 127;
      else if (dias === "30")  m.costo = 25;
    }

    return m;
  });

  // Re-render con los nuevos costos
  renderMembresiasSelect();
});

// let btnOpenCheckout = document.querySelector('.js-payment-checkout');
// btnOpenCheckout.addEventListener('click', function() {
  

// })

let paymentCheckout = new PaymentCheckout.modal({
  env_mode: "stg", // `prod`, `stg`, `local` to change environment. Default is `stg`
  onOpen: function () {
    document.body.classList.add("modal-open");
  },
  onClose: function () {
    document.body.classList.remove("modal-open");
  },
  onResponse: function(response) {
    console.log("modal response_new");
    console.log(response);

    // Validación del estado de la transacción
    const estado = response.transaction?.current_status;

    if (estado !== "APPROVED") {
      Swal.fire({
        icon: 'error',
        title: 'Pago rechazado',
        text: 'No se pudo realizar el pago. El banco rechazó la transacción.',
        footer: `Código de transacción: ${response.transaction?.id || 'N/A'}`
      });
      return; // Detener ejecución si no fue aprobado
    }

    // Si el pago fue exitoso, continuar con el registro
    $.post("../api/v1/fulmuv/empresas/membresiasUpdate", {
      id_membresia: id_membresia_seleccionada,
      id_empresa: id_empresa,
      id_usuario: id_usuario,
      pago_valor: costo_seleccionado,
      tipo: "empresa",
      jsonArray: response
    }, function(returnedData) {
      if (!returnedData.error) {
        swal({
          title: "¡Datos registrados!",
          text: "Haz clic en OK para proceder al pago.",
          icon: "success",
          button: "OK",
        }, function() {
          redirigirConPost("true", $("#username_principal").val())
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

});


/* -------------------- NUEVO RENDER POR CARD CON SELECT -------------------- */

function diasToText(dias) {
  if (String(dias) === "30") return "mensual";
  if (String(dias) === "180") return "semestral";
  return "anual";
}

function groupByNombre() {
  groupedMembresias = {};
  (membresiasData || []).forEach(m => {
    const key = (m.nombre || '').toLowerCase();
    if (!groupedMembresias[key]) groupedMembresias[key] = [];
    groupedMembresias[key].push(m);
  });

  // Orden sugerido 30, 180, 360
  const order = ["30", "180", "360"];
  Object.keys(groupedMembresias).forEach(k => {
    groupedMembresias[k].sort(
      (a, b) => order.indexOf(String(a.dias_permitidos)) - order.indexOf(String(b.dias_permitidos))
    );
  });
}

function buildItems(nombreLower) {
  if (nombreLower.includes('onemuv')) {
    return `
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publicación de artículos</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Herramientas de venta</li>
      <li class="py-2 border-bottom text-300"><span class="fas fa-check"></span> Servicios</li>
      <li class="py-2 border-bottom text-300"><span class="fas fa-check"></span> Límite de artículos</li>
    `;
  }
  if (nombreLower.includes('fulmuv')) {
    return `
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Artículos y servicios</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Sucursal Adicional</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Integraciones y pasarelas</li>
      <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Soporte prioritario</li>
    `;
  }
  // basicmuv
  return `
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Publicación de servicios</li>
    <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Visibilidad básica</li>
    <li class="py-2 border-bottom text-300"><span class="fas fa-check"></span> Artículos</li>
    <li class="py-2 border-bottom text-300"><span class="fas fa-check"></span> Límite de servicios</li>
  `;
}

function renderMembresiasSelect() {
  groupByNombre();

  const cont = $('#contenedor-membresias');
  cont.empty();

  Object.keys(groupedMembresias).forEach(key => {
    const planes = groupedMembresias[key];          // array de 30/180/360 disponibles para ese nombre
    const nombre = planes[0].nombre;                // display
    const nombreKey = nombre.replace(/\s+/g, '').toLowerCase();

    // plan por defecto: 30 si existe, sino el primero
    const defaultPlan = planes.find(p => String(p.dias_permitidos) === "360") || planes[0];

    const selectId = `select_${nombreKey}`;
    const precioId = `precio_${nombreKey}`;
    const periodoId = `periodo_${nombreKey}`;
    const btnId    = `btn_${nombreKey}`;

    const options = planes.map(p => {
      const dias = String(p.dias_permitidos);
      const sel = (p.id_membresia == defaultPlan.id_membresia) ? 'selected' : '';
      return `<option value="${dias}" ${sel}>${diasToText(dias)}</option>`;
    }).join('');

    const items = buildItems(key);

    const cardHtml = `
      <div class="col-md-4 mb-3">
        <div class="border rounded-3 overflow-hidden">
          <div class="d-flex flex-between-center p-4">
            <div>
              <h3 class="fw-light text-primary fs-4 mb-0">${nombre}</h3>

              <div class="d-flex align-items-center gap-2 mt-2">
                <select id="${selectId}" data-group="${key}" class="form-select form-select-sm w-auto">
                  ${options}
                </select>
              </div>

              <h2 class="fw-light text-primary mt-2">
                <sup class="fs-8">&dollar;</sup>
                <span id="${precioId}" class="fs-6">${defaultPlan.costo}</span>
                <span id="${periodoId}" class="fs-9 mt-1">/ ${diasToText(defaultPlan.dias_permitidos)}</span>
              </h2>
            </div>
            <div class="pe-3">
              <img src="../theme/assets/img/icons/pro.svg" width="70" alt="" />
            </div>
          </div>

          <div class="p-4 bg-body-tertiary">
            <ul class="list-unstyled">
              ${items}
            </ul>
            <button id="${btnId}" class="btn btn-outline-primary d-block w-100" type="button">
              Comprar
            </button>
          </div>
        </div>
      </div>
    `;

    cont.append(cardHtml);

    // Inicializar botón con el plan por defecto
    $(`#${btnId}`).off('click').on('click', function(){
      saveMembresia(defaultPlan.id_membresia);
    });

    // Handler de cambio de select para esta card
    $(document).off('change', `#${selectId}`).on('change', `#${selectId}`, function () {
      const diasSel = String($(this).val());
      const grupoKey = $(this).data('group');
      const plan = (groupedMembresias[grupoKey] || []).find(p => String(p.dias_permitidos) === diasSel);
      if (!plan) return;

      // Actualizar precio/periodo visibles
      $(`#${precioId}`).text(plan.costo);
      $(`#${periodoId}`).text(`/ ${diasToText(diasSel)}`);

      // Actualizar acción de botón "Comprar" para este plan
      $(`#${btnId}`).off('click').on('click', function(){
        saveMembresia(plan.id_membresia);
      });
    });
  });
}

function mostrarFormulario(){
  
}