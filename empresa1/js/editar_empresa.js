let tipo_user = $("#tipo_user").val();
const ID_VAL = $("#id_empresa").val();
const isEmpresa = (tipo_user === "empresa");
const GET_BASE   = isEmpresa ? "../api/v1/fulmuv/empresas/"      : "../api/v1/fulmuv/sucursales/";
const UPDATE_URL = isEmpresa ? "../api/v1/fulmuv/empresas/update" : "../api/v1/fulmuv/sucursales/update";
const ID_PARAM   = isEmpresa ? "id_empresa"                       : "id_sucursal";

const cantones = {
  "Azuay": ["Cuenca", "Camilo Ponce Enríquez", "Chordeleg", "El Pan", "Girón", "Gualaceo", "Nabón", "Oña", "Paute", "Pucará", "San Fernando", "Santa Isabel", "Sevilla de Oro", "Sigsig"],
  "Bolívar": ["Guaranda", "Chillanes", "Chimbo", "Echeandía", "Las Naves", "San Miguel"],
  "Cañar": ["Azogues", "Biblián", "Cañar", "Déleg", "El Tambo", "La Troncal", "Suscal"],
  "Carchi": ["Tulcán", "Bolívar", "Espejo", "Mira", "Montúfar", "San Pedro de Huaca"],
  "Cotopaxi": ["Latacunga", "La Maná", "Pangua", "Pujilí", "Salcedo", "Saquisilí", "Sigchos"],
  "Chimborazo": ["Riobamba", "Alausí", "Chambo", "Chunchi", "Colta", "Cumandá", "Guamote", "Guano", "Pallatanga", "Penipe"],
  "El Oro": ["Machala", "Arenillas", "Atahualpa", "Balsas", "Chilla", "El Guabo", "Huaquillas", "Las Lajas", "Marcabelí", "Pasaje", "Piñas", "Portovelo", "Santa Rosa", "Zaruma"],
  "Esmeraldas": ["Esmeraldas", "Atacames", "Eloy Alfaro", "Muisne", "Quinindé", "Rioverde", "San Lorenzo"],
  "Guayas": ["Guayaquil", "Alfredo Baquerizo Moreno", "Balao", "Balzar", "Colimes", "Daule", "Durán", "El Empalme", "El Triunfo", "General Antonio Elizalde", "Isidro Ayora", "Lomas de Sargentillo", "Marcelino Maridueña", "Milagro", "Naranjal", "Naranjito", "Nobol", "Palestina", "Pedro Carbo", "Playas", "Salitre", "Samborondón", "Santa Lucía", "Simón Bolívar", "Yaguachi"],
  "Imbabura": ["Ibarra", "Antonio Ante", "Cotacachi", "Otavalo", "Pimampiro", "San Miguel de Urcuquí"],
  "Loja": ["Loja", "Calvas", "Catamayo", "Celica", "Chaguarpamba", "Espíndola", "Gonzanamá", "Macará", "Olmedo", "Paltas", "Pindal", "Puyango", "Quilanga", "Saraguro", "Sozoranga", "Zapotillo"],
  "Los Ríos": ["Babahoyo", "Baba", "Buena Fe", "Mocache", "Montalvo", "Palenque", "Puebloviejo", "Quevedo", "Quinsaloma", "Urdaneta", "Valencia", "Ventanas", "Vinces"],
  "Manabí": ["Portoviejo", "Bolívar", "Chone", "El Carmen", "Flavio Alfaro", "Jama", "Jaramijó", "Jipijapa", "Junín", "Manta", "Montecristi", "Olmedo", "Paján", "Pedernales", "Pichincha", "Puerto López", "Rocafuerte", "Santa Ana", "Sucre", "Tosagua", "Veinticuatro de Mayo"],
  "Morona Santiago": ["Morona", "Gualaquiza", "Huamboya", "Limón Indanza", "Logroño", "Pablo Sexto", "Palora", "San Juan Bosco", "Sucúa", "Taisha", "Tiwintza"],
  "Napo": ["Tena", "Archidona", "Carlos Julio Arosemena Tola", "El Chaco", "Quijos"],
  "Pastaza": ["Puyo", "Arajuno", "Mera", "Santa Clara"],
  "Pichincha": ["Quito", "Cayambe", "Mejía", "Pedro Moncayo", "Pedro Vicente Maldonado", "Puerto Quito", "Rumiñahui", "San Miguel de Los Bancos"],
  "Tungurahua": ["Ambato", "Baños de Agua Santa", "Cevallos", "Mocha", "Patate", "Quero", "San Pedro de Pelileo", "Santiago de Píllaro", "Tisaleo"],
  "Zamora Chinchipe": ["Zamora", "Centinela del Cóndor", "Chinchipe", "El Pangui", "Nangaritza", "Palanda", "Paquisha", "Yacuambi", "Yantzaza"],
  "Galápagos": ["San Cristóbal", "Isabela", "Santa Cruz"],
  "Sucumbíos": ["Nueva Loja", "Cascales", "Cuyabeno", "Gonzalo Pizarro", "Lago Agrio", "Putumayo", "Shushufindi", "Sucumbíos"],
  "Orellana": ["Francisco de Orellana", "Aguarico", "La Joya de Los Sachas", "Loreto"],
  "Santo Domingo de los Tsáchilas": ["Santo Domingo"],
  "Santa Elena": ["Santa Elena", "La Libertad", "Salinas"]
};

$(document).ready(function () {
  // Cargar datos de empresa o sucursal
  $.get(GET_BASE + ID_VAL, {}, function (returnedData) {
    const returned = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;

    if (!returned.error) {

      if (!isEmpresa) {
        // ==== MODO SUCURSAL ====

        // 1) Campo NUEVO: Nombre Sucursal
        $("#div_inputs").append(`
          <div class="col-md-12">
            <label class="form-label">Nombre Sucursal</label>
            <input type="text" class="form-control" id="nombre_sucursal" value="${returned.data.nombre || ''}">
          </div>
        `);

        // Campos de empresa → solo lectura
        $("#nombre_completo, #nombre_titular")
          .prop("readonly", true)
          .addClass("bg-light");

        // 2) Mostrar datos de empresa en los campos existentes
        //    y ponerlos solo lectura (para que no se editen desde la sucursal)
        $("#nombre_completo, #nombre_titular").prop("readonly", true);

        // 3) Traer datos de la EMPRESA usando el id_empresa de la sucursal
        //    (AJUSTA 'id_empresa' si en tu API viene con otro nombre)
        if (returned.data.id_empresa) {
          $.get("../api/v1/fulmuv/empresas/" + returned.data.id_empresa, {}, function (empresaData) {
            const empresa = typeof empresaData === "string" ? JSON.parse(empresaData) : empresaData;
            if (!empresa.error) {
              $("#nombre_completo").val(empresa.data.nombre || "");
              $("#nombre_titular").val(empresa.data.nombre_titular || "");
            }
          });
        }

      } else {
        // ==== MODO EMPRESA ====
        $("#nombre_completo").val(returned.data.nombre || "");
        $("#nombre_titular").val(returned.data.nombre_titular || "");
      }

      $("#provincia").val(returned.data.provincia);
      cargarCantones(returned.data.provincia);
      $("#canton").val(returned.data.canton);
      $("#calle_principal").val(returned.data.calle_principal || "");
      $("#calle_secundaria").val(returned.data.calle_secundaria || "");
      $("#bien_inmueble").val(returned.data.bien_inmueble || "");
      $("#whatsapp_contacto").val(returned.data.whatsapp_contacto || "");
      $("#telefono_contacto").val(returned.data.telefono_contacto || "");
      $("#correo").val(returned.data.correo || "");

    } else {
      SweetAlert("error", returned.msg);
    }
  });

  // Cuando cambie provincia, recargar cantones
  $("#provincia").on("change", function () {
    cargarCantones(this.value);
    $("#canton").val(""); // reset
  });
});

function saveEmpresaEditar() {
  const payload = {
    // Si es empresa, se edita el nombre de empresa;
    // si es sucursal, se edita solo el nombre de la sucursal
    nombre: isEmpresa ? $("#nombre_completo").val() : $("#nombre_sucursal").val(),
    nombre_titular: isEmpresa ? $("#nombre_titular").val() : $("#nombre_titular").val(), // si no quieres que se edite desde sucursal, puedes dejarlo fijo
    provincia: $("#provincia").val(),
    canton: $("#canton").val(),
    calle_principal: $("#calle_principal").val(),
    calle_secundaria: $("#calle_secundaria").val(),
    bien_inmueble: $("#bien_inmueble").val(),
    whatsapp_contacto: $("#whatsapp_contacto").val(),
    telefono_contacto: $("#telefono_contacto").val(),
    correo: $("#correo").val(),
  };

  if (!payload.nombre) {
    SweetAlert("error", "Todos los campos obligatorios deben estar llenos.");
    return;
  }

  // ID dinámico: id_empresa o id_sucursal
  payload[ID_PARAM] = ID_VAL;

  $.post(UPDATE_URL, payload, function (returnedData) {
    const returned = typeof returnedData === "string" ? JSON.parse(returnedData) : returnedData;
    if (!returned.error) {
      SweetAlert("url_success", returned.msg, "editar_empresa.php");
    } else {
      SweetAlert("error", returned.msg);
    }
  });
}

function cargarCantones(provincia) {
  const cantonSelect = document.getElementById("canton");
  cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';

  if (provincia && cantones[provincia]) {
    cantones[provincia].forEach(canton => {
      const option = document.createElement("option");
      option.value = canton;
      option.textContent = canton;
      cantonSelect.appendChild(option);
    });
  }
}
