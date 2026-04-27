var id_empresa = document.getElementById("id_empresa_detalle")?.value;
// $( document ).ready(function() {
//   $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
//     var returned = JSON.parse(returnedData)
//     if (returned.error == false) {
//       $("#cont_membresias").text("");
//       returned.data.forEach(membresia => {
//         var dias = "";
//         if(membresia.dias_permitidos == "30"){
//           dias = "mensual";
//         }else if(membresia.dias_permitidos == "180"){
//           dias = "semestral";
//         }else if(membresia.dias_permitidos == "360"){
//           dias = "anual";
//         }
//         $("#cont_membresias").append(`
//           <div class="col-md-4 mb-3">
//             <div class="border rounded-3 overflow-hidden">
//               <div class="d-flex flex-between-center p-4">
//                 <div>
//                   <h3 class="fw-light text-primary fs-4 mb-0">${membresia.nombre}</h3>
//                   <h2 class="fw-light text-primary mt-0"><sup class="fs-8">&dollar;</sup><span class="fs-6">${membresia.costo}</span><span class="fs-9 mt-1">/ ${dias}</span></h2>
//                 </div>
//                 <div class="pe-3"><img src="../theme/assets/img/icons/pro.svg" width="70" alt="" /></div>
//               </div>
//               <div class="p-4 bg-body-tertiary">
//                 <ul class="list-unstyled">
//                   <li class="py-2 border-bottom"><span class="fas fa-check text-primary" data-fa-transform="shrink-2"></span> Advanced marketing </li>
//                   <li class="py-2 border-bottom"><span class="fas fa-check text-primary" data-fa-transform="shrink-2"></span> Api &amp; Developer Tools</li>
//                   <li class="py-2 border-bottom text-300"><span class="fas fa-check" data-fa-transform="shrink-2"></span> Integrations</li>
//                   <li class="py-2 border-bottom text-300"><span class="fas fa-check" data-fa-transform="shrink-2"></span> Payments </li>
//                 </ul>
//                 <button class="btn btn-outline-primary d-block w-100" type="button" onclick="saveMembresia(${membresia.id_membresia})">Comprar</button>
//               </div>
//             </div>
//           </div>
//         `);
//       });
//     }
//   });
// });

function saveMembresia(id_membresia) {
  swal({
    title: "Alerta",
    text: "¿Deseas comprar esta membresía?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#27b394",
    confirmButtonText: "Sí",
    cancelButtonText: 'No',
    closeOnConfirm: false
  }, function () {
    
    $.post('../api/v1/fulmuv/empresas/membresiasUpdate', {
      id_empresa: id_empresa,
      id_membresia: id_membresia,
    }, function (returnedData) {
      var returned = JSON.parse(returnedData);
      if (returned.error == false) {
        SweetAlert("url_success", returned.msg, "asignar_membresia.php?id_empresa="+id_empresa);
      } else {
        SweetAlert("error", returned.msg);
      }
    });

  });
}

let membresiasData = [];

$(document).ready(function () {
  $.get('../api/v1/fulmuv/membresias/', {}, function (returnedData) {
    let returned = JSON.parse(returnedData);
    if (returned.error === false) {
      membresiasData = returned.data;
      renderAllTabs(); // Llenar todos los tabs al cargar
    }
  });
});

function renderAllTabs() {
  renderMembresiasPorTipo("30", "#pill-tab-home");
  renderMembresiasPorTipo("180", "#pill-tab-profile");
  renderMembresiasPorTipo("360", "#pill-tab-contact");
}

function renderMembresiasPorTipo(diasFiltrado, contenedorID) {
  const contenedor = $(contenedorID);
  contenedor.empty();

  const membresiasFiltradas = membresiasData.filter(m => m.dias_permitidos == diasFiltrado);
  const diasTexto = diasFiltrado == "30" ? "mensual" : diasFiltrado == "180" ? "semestral" : "anual";

  if (membresiasFiltradas.length === 0) {
    contenedor.append(`<p class="text-center text-muted">No hay membresías ${diasTexto} disponibles.</p>`);
    return;
  }

  let row = $('<div class="row justify-content-center"></div>');

  membresiasFiltradas.forEach(membresia => {
    let card = `
      <div class="col-md-4 mb-3">
        <div class="border rounded-3 overflow-hidden">
          <div class="d-flex flex-between-center p-4">
            <div>
              <h3 class="fw-light text-primary fs-4 mb-0">${membresia.nombre}</h3>
              <h2 class="fw-light text-primary mt-0">
                <sup class="fs-8">&dollar;</sup><span class="fs-6">${membresia.costo}</span><span class="fs-9 mt-1">/ ${diasTexto}</span>
              </h2>
            </div>
            <div class="pe-3">
              <img src="../theme/assets/img/icons/pro.svg" width="70" alt="" />
            </div>
          </div>
          <div class="p-4 bg-body-tertiary">
            <ul class="list-unstyled">
              <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> Advanced marketing</li>
              <li class="py-2 border-bottom"><span class="fas fa-check text-primary"></span> API &amp; Developer Tools</li>
              <li class="py-2 border-bottom text-300"><span class="fas fa-check"></span> Integrations</li>
              <li class="py-2 border-bottom text-300"><span class="fas fa-check"></span> Payments</li>
            </ul>
            <button class="btn btn-outline-primary d-block w-100" type="button" onclick="saveMembresia(${membresia.id_membresia})">Comprar</button>

            <!--div class="form-check mt-2">
              <input class="form-check-input me-2" id="checkTerminoCondiciones" type="checkbox" value="">
              <label class="form-check-label mb-0" for="checkTerminoCondiciones">
                He leído y acepto los <a href="terminos_condiciones/TERMINOS_CONDICIONES_USO.pdf" download="Términos_Condiciones_Legal_Solutions" class="fs-10">
                  Términos y Condiciones
                </a>
              </label>
            </div-->


          </div>
        </div>
      </div>
    `;
    row.append(card);
  });

  contenedor.append(row);
}
