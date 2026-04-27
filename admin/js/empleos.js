let categorias = [];
var tagsInput = '';
let tipo_user = "empresa"; // admin siempre consulta a nivel empresa
let timerBusqueda = null;

function renderEmptyEmpleosState(mensaje = "No existen empleos registrados.", descripcion = "Cuando publiques tu primera vacante, aparecerá aquí para que puedas administrarla.") {
  $("#lista_empleos").html(`
    <div class="col-12">
      <div class="card border-200 shadow-sm">
        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5" style="min-height:320px;">
          <div class="rounded-circle bg-body-tertiary d-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;">
            <span class="fas fa-briefcase text-600 fs-4"></span>
          </div>
          <h4 class="mb-2">${mensaje}</h4>
          <p class="text-600 mb-0" style="max-width:520px;">${descripcion}</p>
        </div>
      </div>
    </div>
  `);
}

function getEmpleoEditUrl(idEmpleo) {
  return `crear_empleo.php?id_empleo=${encodeURIComponent(idEmpleo)}`;
}

function empleoImageSrc(path) {
  return path ? `../admin/${path}` : "files/producto_no_found.jpg";
}

function renderEmpleoCard(empleo, includeCvButton = true) {
  const fechaInicio = empleo.fecha_inicio || "Sin fecha";
  const fechaFin = empleo.fecha_fin || "Sin fecha";
  const titulo = empleo.titulo || "Empleo sin título";
  const provincia = empleo.provincia || "Provincia no definida";
  const canton = empleo.canton || "Cantón no definido";
  const empresa = empleo.nombre_empresa || empleo.empresa || "";
  const cvButton = includeCvButton ? `
    <a class="btn btn-sm btn-falcon-default me-2 mt-2"
      onclick="openCVRecibidos(${empleo.id_empleo}, '${titulo.replace(/'/g, "\\'")}')"
      data-bs-toggle="tooltip" data-bs-placement="top" title="CV recibidos">
      <i class="bi bi-file-earmark-person"></i> CV
    </a>
  ` : "";

  return `
    <div class="mb-4 col-md-6 col-xl-4">
      <div class="empleo-card h-100 d-flex flex-column justify-content-between">
        <div>
          <div class="empleo-card-image">
            <span class="empleo-card-badge">Vacante activa</span>
            <a class="d-block h-100" href="${getEmpleoEditUrl(empleo.id_empleo)}">
              <img src="${empleoImageSrc(empleo.img_frontal)}" alt="${titulo}" />
            </a>
          </div>
          <div class="p-3 p-lg-4">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
              <h5 class="empleo-card-title mb-0">
                <a class="text-1100 text-decoration-none" href="${getEmpleoEditUrl(empleo.id_empleo)}">${titulo}</a>
              </h5>
            </div>
            ${empresa ? `<div class="empleo-card-meta mb-2"><span class="fas fa-building me-2 text-primary"></span>${empresa}</div>` : ""}
            <div class="empleo-card-meta mb-2"><span class="fas fa-map-marker-alt me-2 text-primary"></span>${provincia} · ${canton}</div>
            <div class="empleo-card-meta"><span class="fas fa-calendar-alt me-2 text-primary"></span>${fechaInicio} al ${fechaFin}</div>
          </div>
        </div>
        <div class="px-3 px-lg-4 pb-3 pb-lg-4 empleo-card-actions">
          ${cvButton}
          <a class="btn btn-sm btn-falcon-default me-2 mt-2" href="${getEmpleoEditUrl(empleo.id_empleo)}" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar">
            <span class="fas fa-edit"></span> Editar
          </a>
          <a class="btn btn-sm btn-falcon-default mt-2 text-danger" onclick="remove(${empleo.id_empleo}, 'empleos')" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar">
            <span class="fas fa-trash"></span>
          </a>
        </div>
      </div>
    </div>
  `;
}

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
  // La carga de empresas la maneja el footer (initAdminEmpresaSelector)
});

function filtrarEmpleosLive(texto) {
  clearTimeout(timerBusqueda);
  timerBusqueda = setTimeout(() => {
    //filtrarProductos(texto);
    getEmpleosFiltro($("#lista_empresas").val(), texto)
  }, 300); // 300ms después de dejar de escribir
}

$("#lista_empresas").on('change', function () {
  getEmpleos($(this).val());
});

function getEmpleos(id_empresa) {
  $.get('../api/v1/fulmuv/empleos/all/' + id_empresa + '/' + tipo_user, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_empleos").text("");
      if (!Array.isArray(returned.data) || returned.data.length === 0) {
        renderEmptyEmpleosState();
        return;
      }
      returned.data.forEach(empleo => {
        $("#lista_empleos").append(renderEmpleoCard(empleo, true));
      });
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }
  });
}

function getEmpleosFiltro(id_empresa, consulta) {
  if (consulta == "") {
    consulta = "0"
  }
  $.get('../api/v1/fulmuv/empleos/allFiltro/' + id_empresa + '/' + tipo_user + '/' + consulta, {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
      $("#lista_empleos").text("");
      if (!Array.isArray(returned.data) || returned.data.length === 0) {
        renderEmptyEmpleosState(
          "No se encontraron empleos.",
          consulta && consulta !== "0"
            ? `No hay resultados para "${consulta}". Prueba con otro criterio de búsqueda.`
            : "Cuando publiques tu primera vacante, aparecerá aquí para que puedas administrarla."
        );
        return;
      }
      returned.data.forEach(empleo => {
        $("#lista_empleos").append(renderEmpleoCard(empleo, true));
      });
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }
  });
}

function firstFromJson(val) {
  if (Array.isArray(val)) return val[0] ?? null;
  if (typeof val === 'string') {
    try {
      const arr = JSON.parse(val || '[]');
      return arr[0] ?? null;
    } catch { return null; }
  }
  return null;
}

function editEmpleo(id_empleo) {
  window.location.href = getEmpleoEditUrl(id_empleo);
}

function saveFiles(files) {
  return new Promise(function (resolve, reject) {
    if (!files.length) {
      resolve(); // Resuelve la promesa incluso si no hay imágenes
    } else {
      const formData = new FormData();
      files.forEach(function (file) {
        formData.append(`archivos[]`, file); // añadrir los archivos al form
      });
      $.ajax({
        type: 'POST',
        data: formData,
        url: '../admin/cargar_imagen.php',
        cache: false,
        contentType: false,
        processData: false,
        success: function (returnedImagen) {
          if (returnedImagen["response"] == "success") {
            resolve(returnedImagen["data"]); // Resuelve la promesa cuando la llamada AJAX se completa con éxito
          } else {
            SweetAlert("error", "Ocurrió un error al guardar los archivos." + returnedImagen["error"]);
            reject(); // Rechaza la promesa en caso de error
          }
        }
      });
    }
  });
}

function remove(id, tabla) {
  swal({
    title: "Alerta",
    text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    $.post('../api/v1/fulmuv/' + tabla + '/delete', {
      id: id
    }, function (returnedData) {
      var returned = JSON.parse(returnedData)
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "empleos.php")
      } else {
        SweetAlert("error", returned.msg)
      }
    });
  });
}

function loadFilesToDropzone(id_producto, files) {
  // Destruir instancias previas
  if (Dropzone.instances.length > 0) {
    Dropzone.instances.forEach(dz => dz.destroy());
    $("#file-previews").empty();
  }

  let myDropzone = new Dropzone("#myAwesomeDropzone", {
    url: "../admin/cargar_imagen_drop.php",
    // paramName: "archivos",
    previewsContainer: "#file-previews",
    previewTemplate: document.querySelector("#uploadPreviewTemplate").innerHTML,
    autoProcessQueue: true,
    addRemoveLinks: false,
    init: function () {
      const dropzoneInstance = this;

      files.forEach(file => {
        let fileName = file.archivo.split("/").pop();
        let fileExtension = file.archivo.split('.').pop().toLowerCase();

        let mockFile = {
          name: fileName,
          size: 123456, // Tamaño ficticio
          accepted: true,
          existing: true
        };

        dropzoneInstance.emit("addedfile", mockFile);

        // Icono según tipo
        if (["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(fileExtension)) {
          dropzoneInstance.emit("thumbnail", mockFile, file.archivo);
        } else {
          dropzoneInstance.emit("thumbnail", mockFile, "../img/pdf.png");
        }

        // Asignar enlaces de descarga
        let nameLink = mockFile.previewElement.querySelector("a[data-dz-name]");
        nameLink.href = "../admin/" + file.archivo;
        nameLink.textContent = fileName;
        nameLink.target = "_blank";

        // Agregar input oculto con ID del archivo
        let inputHidden = document.createElement("input");
        inputHidden.setAttribute("type", "hidden");
        inputHidden.setAttribute("data-dz-id", "");
        inputHidden.value = file.id_archivo_producto;
        mockFile.previewElement.querySelector(".file_buttons").appendChild(inputHidden);

        // Botón personalizado para eliminar
        let removeBtn = Dropzone.createElement(
          "<a href='' class='btn btn-link btn-lg text-danger' data-dz-remove><i class='far fa-trash-alt'></i></a>"
        );
        removeBtn.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          $.post('../api/v1/fulmuv/deleteFileVehiculo', {
            id_archivo: file.id_archivo_producto,
          }, function (resp) {
            if (!resp.error) {
              toastr.success("Archivo eliminado");
              dropzoneInstance.removeFile(mockFile);
            } else {
              SweetAlert("error", resp.msg || "No se pudo eliminar");
            }
          });
        });

        mockFile.previewElement.querySelector(".file_buttons").appendChild(removeBtn);
      });

      this.on("success", function (file, response) {
        console.log("File uploaded successfully:", response);
        // Manejar la respuesta del servidor aquí
        if (response.error) {
          SweetAlert("error", response.error);
          this.removeFile(file);
        } else {
          // Manejar éxito
          toastr.success("File uploaded");
          // Agregar URL de descarga al nombre del archivo
          var nameLink = file.previewElement.querySelector("a[data-dz-name]");
          nameLink.href = response.data[0].archivo;
          nameLink.target = "_blank";

          // Guardar en base de datos
          $.post('../api/v1/fulmuv/createFileProducto', {
            id_producto: id_producto,
            archivo: response.data[0].archivo,
            tipo: response.data[0].tipo,
          }, function (returnedData) {
            var returned = JSON.parse(returnedData)
            if (returned["error"] == false) {
              toastr.success("Archivo guardado");
              // Crear botón eliminar (como en archivos precargados)
              let removeBtn = Dropzone.createElement(
                "<a href='' class='btn btn-link btn-lg text-danger' data-dz-remove><i class='far fa-trash-alt'></i></a>"
              );
              removeBtn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                $.post('../api/v1/fulmuv/deleteFileProducto', {
                  id_archivo: returned.id_archivo,
                }, function (resp) {
                  if (!resp.error) {
                    toastr.success("Archivo eliminado");
                    dropzoneInstance.removeFile(file);
                  } else {
                    SweetAlert("error", resp.msg || "No se pudo eliminar");
                  }
                });
              });
              // Agregar input oculto con el ID del archivo creado
              let inputHidden = document.createElement("input");
              inputHidden.setAttribute("type", "hidden");
              inputHidden.setAttribute("data-dz-id", "");
              inputHidden.value = returned.id_archivo_producto;
              file.previewElement.querySelector(".file_buttons").appendChild(inputHidden);
              //  Agregar botón eliminar
              file.previewElement.querySelector(".file_buttons").appendChild(removeBtn);
            } else {
              SweetAlert("error", returned.msg);
            }
          });
        }
      });
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

function openCVRecibidos(id_empleo, titulo = "") {
  $("#cvModalTitle").text(`CV recibidos - ${titulo || ("Empleo #" + id_empleo)}`);

  $("#tbodyCVRecibidos").html("");
  $("#cvEmpty").hide();
  $("#cvLoading").show();

  const modal = new bootstrap.Modal(document.getElementById("modalCVRecibidos"));
  modal.show();

  // ✅ AJUSTA esta ruta a tu endpoint real:
  const url = `../api/v1/fulmuv/empleos/envioempleos`;

  $.post(url, {id_empleo}, function (resp) {
    $("#cvLoading").hide();

    let data = resp;
    if (typeof resp === "string") {
      try { data = JSON.parse(resp); } catch { data = []; }
    }

    // ✅ Soporta array directo
    const rows = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);

    if (!rows.length) {
      $("#cvEmpty").show();
      return;
    }

    rows.forEach(p => {
      const nombre = p.nombres_apellidos || "-";
      const correo = p.correo || "-";
      const telefono = p.telefono || "-";
      const fecha = (p.created_at || "").replace("T", " ").split(".")[0] || "-";

      // cv viene como "files/....pdf"
      const cvPath = p.cv || "";
      const cvUrl = cvPath
        ? (cvPath.startsWith("http") ? cvPath : `../${cvPath}`)
        : "";

      $("#tbodyCVRecibidos").append(`
        <tr>
          <td>${nombre}</td>
          <td>${correo}</td>
          <td>${telefono}</td>
          <td>${fecha}</td>
          <td class="text-end">
            ${cvUrl ? `
              <a class="btn btn-sm btn-falcon-default"
                 href="${cvUrl}" target="_blank" rel="noopener">
                <i class="bi bi-file-earmark-pdf"></i> Ver CV
              </a>
            ` : `<span class="text-muted">Sin CV</span>`}
          </td>
        </tr>
      `);
    });

  }).fail(function (xhr) {
    $("#cvLoading").hide();
    $("#cvEmpty").show();
    console.log("Error CV:", xhr.responseText);
  });
}


document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
