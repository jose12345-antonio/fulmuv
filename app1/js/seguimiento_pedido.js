const numero_orden = $("#numero_orden").val();

$(document).ready(function () {

    $("#breadcrumb").append(`
        <a href="vendor.php" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
        <span></span> Seguimiento de tu pedido
    `)

    $.post("api/v1/fulmuv/getOrdenesSeguimiento", { numero_orden }, function (res) {
        if (res.error) {
            alert("No se encontró la orden.");
            return;
        }

        const data = res.data[0];

        // Mostrar productos (agrupado por registro de orden_empresa)
        let productosHTML = "";

        data.empresas.forEach(empresa => {
            try {
                const estadoProducto = (empresa.orden_estado || '').toString();
                const id_orden_empresa = empresa.id_ordenes; // id del registro en ordenes_empresas
                const nombreEmpresa = empresa.datos_empresa?.nombre || "Sin nombre";
                const nombreTrayecto = empresa.trayecto[0].nombre || "Sin nombre";

                // Totales del registro (del backend)
                const pesoTotalRegistro = parseFloat(empresa.peso_total ?? 0);
                const totalEnvioRegistro = parseFloat(empresa.total_envio ?? 0);


                // Productos del registro
                let productos = [];
                try { productos = JSON.parse(empresa.productos || '[]'); } catch (_) { productos = []; }

                // Subtotal de productos en este registro
                const subtotalProductos = productos.reduce((acc, p) => {
                    const precio = Number(p.valor_descuento ?? p.precio ?? 0);
                    const cant = Number(p.cantidad ?? 1);
                    return acc + precio * cant;
                }, 0);

                let totalIVA152 = subtotalProductos * 0.15;

                const totalProveedor = subtotalProductos + totalIVA152;

                // // Cabecera del registro (grupo)
                // productosHTML += `
                // <tr class="table-active">
                //     <td colspan="8">
                //     <div class="d-flex flex-wrap justify-content-between align-items-center">
                //         <div>
                //         <strong>Registro #${String(id_orden_empresa).padStart(3, '0')}</strong>
                //         &nbsp;${getBadgeEstado(estadoProducto)}
                //         &nbsp;—&nbsp;<span class="text-muted">Empresa:</span> ${nombreEmpresa}
                //         &nbsp;—&nbsp;<span class="text-muted">Tratecto:</span> ${nombreTrayecto}
                //         </div>
                //         <!--div>
                //         <strong>Peso total:</strong> ${pesoTotalRegistro} kg
                //         &nbsp;|&nbsp;
                //             <a href="javascript:void(0)" class="text-primary"
                //             onclick="mostrarDetalleEnvio(${empresa.peso_total}, ${empresa.valor_base_trayecto}, ${empresa.valor_adicional_kg}, ${totalEnvioRegistro})">
                //             <strong>Envío:</strong> ${formatoMoneda.format(totalEnvioRegistro)}
                //             </a>                        
                //         </div-->
                //     </div>
                //     </td>
                // </tr>
                // `;

                const waLink = linkWhatsAppProveedor(empresa, id_orden_empresa, numero_orden);

                productosHTML += `
                <tr class="table-active">
                <td colspan="8">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    
                    <div class="me-2">
                        <strong>Registro #${String(id_orden_empresa).padStart(3, '0')}</strong>
                        &nbsp;${getBadgeEstado(estadoProducto)}
                        &nbsp;—&nbsp;<span class="text-muted">Empresa:</span> ${nombreEmpresa}
                        &nbsp;—&nbsp;<span class="text-muted">Trayecto:</span> ${nombreTrayecto}
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                        <div class="border rounded-3 px-3 py-2 bg-white text-end">
                        <div class="small text-muted">Total a pagar al proveedor</div>
                        <div class="fw-bold">${formatoMoneda.format(totalProveedor)}</div>
                        </div>

                        ${waLink
                        ? `<a href="${waLink}" target="_blank" class="btn btn-success btn-sm">
                                <i class="fi-rs-headset me-1"></i> Contactar proveedor
                            </a>`
                        : `<button type="button" class="btn btn-outline-secondary btn-sm" disabled
                                title="Proveedor sin WhatsApp registrado">
                                <i class="fi-rs-headset me-1"></i> Contactar proveedor
                            </button>`
                    }
                    </div>

                    </div>
                </td>
                </tr>
                `;


                // Filas de productos del registro
                productos.forEach(p => {
                    productosHTML += `
                        <tr class="text-center">
                        <td>00${id_orden_empresa}</td>
                        <td>${data.created_at}</td>
                        <td>${getBadgeEstado(estadoProducto)}</td>
                        <td>${(typeof capitalizarPrimeraLetra === 'function' ? capitalizarPrimeraLetra(p.nombre) : (p.nombre || ''))}
                            <br><small><span class="fw-bold">By</span> <span>${nombreEmpresa}</span></small>
                        </td>
                        <td>${p.cantidad}</td>
                        <td>${p.descuento}%</td>
                        <td>${formatoMoneda.format(p.valor_descuento)}</td>
                        <td>${formatoMoneda.format(Number(p.valor_descuento) * Number(p.cantidad))}</td>
                        </tr>
                    `;
                });

                let totalIVA15 = subtotalProductos * 0.15;
                // const totalProveedor = subtotalProductos + totalIVA15; // (sin envío)

                // Subtotales y totales del registro
                productosHTML += `
                    <tr class="table-light">
                        <td colspan="7" class="text-end"><strong>Subtotal productos</strong></td>
                        <td class="text-end"><strong>${formatoMoneda.format(subtotalProductos)}</strong></td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="7" class="text-end"><strong>IVA 15%</strong></td>
                        <td class="text-end"><strong>${formatoMoneda.format(totalIVA15)}</strong></td>
                    </tr>
                    <!--tr class="table-light">
                        <td colspan="7" class="text-end"><strong>Total envío estimado</strong></td>
                        <td class="text-end"><strong>${formatoMoneda.format(totalEnvioRegistro)}</strong></td>
                    </tr-->
                    <tr class="table-secondary">
                        <td colspan="7" class="text-end"><strong>Total (productos + IVA)</strong></td>
                        <td class="text-end"><strong>${formatoMoneda.format(subtotalProductos + totalIVA15)}</strong></td>
                    </tr>
                    `;
            } catch (err) {
                console.error("Error al procesar registro de empresa", err);
            }
        });

        $("#tabla-productos tbody").html(productosHTML);


        // Seguimiento
        const estado = data.estado; // recogido, en_camino, en_camino_entrega, entregado
        actualizarBarraSeguimiento(estado);

        // Dirección
        if (data.domicilio) {
            $("#direccion-entrega").html(`
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3"><strong>Nombre:</strong> ${data.domicilio.nombres}</h6>
                    <h6 class="mb-3"><strong>Cédula:</strong> ${data.domicilio.cedula}</h6>
                    <h6 class="mb-3"><strong>Teléfono:</strong> ${data.domicilio.telefono}</h6>
                    <h6 class="mb-3"><strong>Dirección exacta:</strong> ${data.domicilio.direccion_exacta}</h6>
                    <h6 class="mb-3"><strong>Punto referencial:</strong> ${data.domicilio.punto_referencial}</h6>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3"><strong>Provincia:</strong> ${data.domicilio.provincia}</h6>
                    <h6 class="mb-3"><strong>Cantón:</strong> ${data.domicilio.canton}</h6>
                    <h6 class="mb-3"><strong>Parroquia:</strong> ${data.domicilio.parroquia}</h6>
                    <h6 class="mb-3"><strong>Código postal:</strong> ${data.domicilio.codigo_postal}</h6>
                    <h6 class="mb-3"><strong>Horario de entrega:</strong> ${data.domicilio.horario_entrega}</h6>
                </div>
                <div class="col-12">
                    <h6 class="mb-3"><strong>Observaciones:</strong> ${data.domicilio.observaciones}</h6>
                </div>
            </div>
          `);
        }

        // FACTURACIÓN
        if (data.facturacion) {
            $("#facturacion-entrega").html(`
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3"><strong>Razón Social:</strong> ${data.facturacion.razon_social}</h6>
                    <h6 class="mb-3"><strong>Número de identificación:</strong> ${data.facturacion.numero_identificacion}</h6>
                    <h6 class="mb-3"><strong>Correo electrónico:</strong> ${data.facturacion.correo}</h6>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3"><strong>Dirección fiscal:</strong> ${data.facturacion.direccion}</h6>
                    <h6 class="mb-3"><strong>Teléfono:</strong> ${data.facturacion.telefono}</h6>
                    <h6 class="mb-3"><strong>Forma de pago:</strong> ${data.facturacion.forma_pago}</h6>
                </div>
            </div>
          `);
        }
    }, "json");

    function actualizarBarraSeguimiento(estado) {
        const pasos = ["recogido", "en_camino", "en_camino_entrega", "entregado"];
        let html = "";

        pasos.forEach((paso, index) => {
            let clase = "";
            const actualIndex = pasos.indexOf(estado);
            if (actualIndex > index) clase = "done";
            else if (actualIndex === index) clase = "active";

            html += `
                <div class="step ${clase}">
                    <div class="circle">${clase === "done" ? "✔" : index + 1}</div>
                    <p>${paso.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase())}</p>
                </div>
            `;
        });

        $("#seguimiento-entrega").html(html);
    }

})
function getBadgeEstado(estado) {
    const estadoNormalizado = estado.toLowerCase();

    switch (estadoNormalizado) {
        case "creada":
            return `<span class="badge bg-secondary">Creada</span>`; // Gris
        case "procesada":
            return `<span class="badge bg-warning text-dark">Procesada</span>`; // Amarillo
        case "enviada":
            return `<span class="badge bg-primary">Enviada</span>`; // Azul
        case "aprobada":
            return `<span class="badge bg-success">Aprobada</span>`; // Verde
        case "completada":
            return `<span class="badge bg-success">Completada</span>`; // Negro
        case "eliminada":
            return `<span class="badge bg-danger">Rechazada</span>`; // Rojo
        case "pendiente":
            return `<span class="badge bg-info text-dark">Pendiente</span>`; // Celeste
        default:
            return `<span class="badge bg-light text-dark">Desconocido</span>`; // Gris claro
    }



    // switch (estadoNormalizado) {
    //     case "creada":
    //         return `<span class="badge bg-secondary">Creado</span>`;
    //     case "en proceso":
    //         return `<span class="badge bg-warning text-dark">En proceso</span>`;
    //     case "recogido":
    //         return `<span class="badge bg-primary">Recogido</span>`;
    //     case "en camino":
    //         return `<span class="badge bg-info text-dark">En camino</span>`;
    //     case "en camino a entrega":
    //         return `<span class="badge bg-dark">Camino a Entrega</span>`;
    //     case "entregado":
    //         return `<span class="badge bg-success">Entregado</span>`;
    //     default:
    //         return `<span class="badge bg-light text-dark">Desconocido</span>`;
    // }
}



function mostrarDetalleEnvio(peso_total, valor_base_trayecto, valor_adicional_kg, total_envio_registro = null) {
    const peso = Math.max(0, parseFloat(peso_total) || 0);
    const base = Math.max(0, parseFloat(valor_base_trayecto) || 0);
    const adi = Math.max(0, parseFloat(valor_adicional_kg) || 0);

    const baseKg = Math.min(2, peso);         // hasta 2 kg
    const extraKg = Math.max(0, peso - 2);    // exceso

    // Regla: los primeros 2kg se cobran como "2 * base" (si peso >=2), caso contrario "peso * base"
    const costoBase = (peso <= 2) ? (base * peso) : (base);
    const costoExtra = adi * extraKg;

    const totalCalc = +(costoBase + costoExtra).toFixed(2);

    const fmt = (n) => formatoMoneda ? formatoMoneda.format(n) : ('$' + Number(n || 0).toFixed(2));

    const seguroEnvio = totalCalc * 1.1;

    const valorEnvioSeguro = seguroEnvio - totalCalc;

    const html = `
    <div class="mb-2">
      <div><strong>Peso total:</strong> ${peso.toFixed(2)} kg</div>
      <div><strong>Base (hasta 2kg):</strong> ${fmt(base)} /kg</div>
      <div><strong>Adicional (+2kg):</strong> ${fmt(adi)} /kg</div>
    </div>

    <hr class="my-2">

    <div class="mb-2">
      <div class="fw-semibold">Cálculo</div>
      <div class="small text-muted">
        • Primeros ${baseKg.toFixed(2)} kg:
        ${peso <= 2
            ? `${peso.toFixed(2)} × ${fmt(base)} = <strong>${fmt(costoBase)}</strong>`
            : `2.00 = <strong>${fmt(base)}</strong>`}
      </div>
      <div class="small text-muted">
        • Kilos adicionales ${extraKg.toFixed(2)} kg:
        ${extraKg.toFixed(2)} × ${fmt(adi)} = <strong>${fmt(costoExtra)}</strong>
      </div>
      <div class="small text-muted">
        • Seguro de envío ${extraKg.toFixed(2)} kg:
        ${totalCalc.toFixed(2)} × 1,1 = <strong>${fmt(valorEnvioSeguro)}</strong>
      </div>
    </div>

    <hr class="my-2">

    <div class="d-flex justify-content-between align-items-center">
      <div class="fw-bold">Total envío (calculado):</div>
      <div class="fw-bold">${fmt(seguroEnvio)}</div>
    </div>
  `;

    $("#detalleEnvioBody").html(html);
    const modal = new bootstrap.Modal(document.getElementById("modalDetalleEnvio"));
    modal.show();
}

function limpiarTelefonoWhatsApp(num) {
    if (!num) return "";
    let t = String(num).replace(/[^\d]/g, '');
    // Ecuador: si viene 0XXXXXXXXX (10 dígitos) -> 593XXXXXXXXX
    if (t.length === 10 && t.startsWith('0')) t = '593' + t.substring(1);
    // si viene 9 dígitos -> 593 + 9
    if (t.length === 9) t = '593' + t;
    return t;
}

function linkWhatsAppProveedor(empresa, id_orden_empresa, numeroOrden) {

    console.log(empresa, id_orden_empresa, numeroOrden)
    const telRaw =
        empresa?.datos_empresa?.telefono_contacto ||
        empresa?.datos_empresa?.telefono ||
        empresa?.datos_empresa?.celular ||
        empresa?.datos_empresa?.movil ||
        "";

    const tel = limpiarTelefonoWhatsApp(telRaw);
    if (!tel) return null;

    const nombreEmpresa = empresa?.datos_empresa?.nombre || "Proveedor";
    const msg = encodeURIComponent(
        `Hola ${nombreEmpresa}, tengo una consulta sobre mi pedido. Orden #${numeroOrden} (Registro #${String(id_orden_empresa).padStart(3, '0')}).`
    );
    return `https://wa.me/${tel}?text=${msg}`;
}
