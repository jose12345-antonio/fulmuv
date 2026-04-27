$(document).ready(function () {

  $.get('../api/v1/fulmuv/ordenes_iso/' + $("#id_orden_iso").val(), {}, function (returnedData) {

    // =========================
    // Parse seguro
    // =========================
    let returned;
    try {
      returned = (typeof returnedData === "string") ? JSON.parse(returnedData) : returnedData;
    } catch (e) {
      console.error("La API devolvió algo no-JSON:", returnedData);
      SweetAlert("error", "Respuesta inválida del servidor.");
      return;
    }

    if (returned.error !== false) {
      SweetAlert("error", "No se pudo cargar la orden ISO.");
      return;
    }

    const data = returned.data || {};

    // =========================
    // Helpers
    // =========================
    const money = (v) => {
      const n = parseFloat(v);
      if (isNaN(n)) return "$0.00";
      return "$" + n.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    const num = (v) => (v === null || v === undefined || v === "" ? "-" : v);
    const yesNo = (v) => (String(v) === "1" || v === true ? "Sí" : "No");

    // Para texto tipo: 25,00 kg
    const fmtKg = (v) => {
      const n = parseFloat(v);
      if (isNaN(n)) return "-";
      return n.toLocaleString("es-EC", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " kg";
    };

    // Formato ES para banner (coma decimal)
    const expKg = (n) => {
      const x = parseFloat(n);
      if (isNaN(x)) return "-";
      return x.toLocaleString("es-EC", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    const expMoneyEs = (n) => {
      const x = parseFloat(n);
      if (isNaN(x)) return "$0,00";
      return "$" + x.toLocaleString("es-EC", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // =========================
    // CABECERA
    // =========================
    $(".numero_orden").text(`Detalle de orden #${data.id_orden_iso ?? ""}`);
    $("#fecha").text(data.created_at ?? "-");

    // Estado (tabla derecha)
    let estadoRow = "";
    const estado = (data.orden_estado || "").toLowerCase();

    if (estado === "procesada") {
      estadoRow = `
        <tr class="alert alert-primary fw-bold">
          <th class="text-primary-emphasis text-sm-end">Estado:</th>
          <td class="text-primary-emphasis text-capitalize">procesada
            <span class="ms-1 fas fa-cogs" data-fa-transform="shrink-2"></span>
          </td>
        </tr>`;
    } else if (estado === "enviada") {
      estadoRow = `
        <tr class="alert alert-info fw-bold">
          <th class="text-info-emphasis text-sm-end">Estado:</th>
          <td class="text-info-emphasis text-capitalize">enviada
            <span class="ms-1 fas fa-truck" data-fa-transform="shrink-2"></span>
          </td>
        </tr>`;
    } else if (estado === "completada") {
      estadoRow = `
        <tr class="alert alert-success fw-bold">
          <th class="text-success-emphasis text-sm-end">Estado:</th>
          <td class="text-success-emphasis text-capitalize">completada
            <span class="ms-1 fas fa-check" data-fa-transform="shrink-2"></span>
          </td>
        </tr>`;
    }

    if (estadoRow) $("#tabla").append(estadoRow);

    // =========================
    // ORDEN BASE
    // =========================
    let ordenBase = data.ordenes ?? null;

    if (typeof ordenBase === "string") {
      try { ordenBase = JSON.parse(ordenBase); } catch { ordenBase = null; }
    }
    if (Array.isArray(ordenBase)) ordenBase = ordenBase[0];
    ordenBase = ordenBase || {};

    // =========================
    // ✅ APLICAR EL CÁLCULO REAL AQUÍ (2kg base + resto adicional, con IVA)
    // Fórmula: 25,00 kg : 2 × $6,39 + 23,00 × $1,03 (kilo adicional)
    // Total tarifa + IVA = ( (2*base) + ((peso-2)*adicional) ) * (1 + IVA)
    // =========================
    const pesoFacturable = parseFloat(ordenBase.peso_facturable_kg ?? ordenBase.peso_total ?? 0);
    const kgBaseIncluidos = 2;

    const baseTrayecto = parseFloat(ordenBase.valor_base_trayecto ?? 0);
    const adicionalKg = parseFloat(ordenBase.valor_adicional_kg ?? 0);
    const ivaEnvioPct = parseFloat(ordenBase.iva_envio ?? 0); // 0.1500

    const kgAdicionales = Math.max(0, pesoFacturable - kgBaseIncluidos);

    // ✅ CÁLCULO CORRECTO (según tu ejemplo):
    const subtotalTarifa = (kgBaseIncluidos * baseTrayecto) + (kgAdicionales * adicionalKg);
    const ivaEnvioCalc = subtotalTarifa * ivaEnvioPct;
    const totalTarifaIva = subtotalTarifa + ivaEnvioCalc;

    const detalleFormula = `${expKg(pesoFacturable)} kg : ${kgBaseIncluidos} × ${expMoneyEs(baseTrayecto)} + ${expKg(kgAdicionales)} × ${expMoneyEs(adicionalKg)} (kilo adicional)`;

    // =========================
    // ✅ INFO EXTRA debajo de Clientes + Banner
    // (Quitado: id_trayecto, id_ruta, envio_domicilio)
    // =========================
    $("#info_extra_iso").html(`
      <div class="card border">
        <div class="card-body p-3">
          <h6 class="mb-2 text-700">Información logística</h6>
          <div class="row g-2 fs-10">
            <div class="col-md-4"><b>Empresa:</b> ${num(ordenBase.empresa)}</div>
            <div class="col-md-4"><b>Estado:</b> ${num(ordenBase.orden_estado)}</div>
            <div class="col-md-4"><b>Frágil:</b> ${yesNo(ordenBase.fragil)}</div>
          </div>

          <hr class="my-2">

          <h6 class="mb-2 text-700">Pesos</h6>
          <div class="row g-2 fs-10">
            <div class="col-md-4"><b>Peso total:</b> ${fmtKg(ordenBase.peso_total)}</div>
            <div class="col-md-4"><b>Peso real total:</b> ${fmtKg(ordenBase.peso_real_total_kg)}</div>
            <div class="col-md-4"><b>Peso facturable:</b> ${fmtKg(ordenBase.peso_facturable_kg)}</div>
          </div>

          <hr class="my-2">

          <h6 class="mb-2 text-700">Seguro</h6>
          <div class="row g-2 fs-10">
            <div class="col-md-4"><b>Seguro base:</b> ${money(ordenBase.seguro_base_usd)}</div>
            <div class="col-md-4"><b>Seguro IVA:</b> ${money(ordenBase.seguro_iva_usd)}</div>
            <div class="col-md-4"><b>Seguro total:</b> ${money(ordenBase.seguro_total_usd)}</div>
          </div>

          <hr class="my-2">

          <!-- ✅ Banner de envío con el cálculo aplicado -->
          <div class="p-3 rounded border bg-body-tertiary mt-2">
            <div class="text-600 fs-10 mb-1">Envío a domicilio — Especial</div>
            <div class="fw-semi-bold">Total tarifa + IVA: ${expMoneyEs(totalTarifaIva)}</div>
            <div class="text-600 fs-10 mt-1">${detalleFormula}</div>
          </div>

          <div class="row g-2 fs-10 mt-2">
            <div class="col-md-6"><b>Subtotal tarifa:</b> ${expMoneyEs(subtotalTarifa)}</div>
            <div class="col-md-6"><b>IVA envío (15%):</b> ${expMoneyEs(ivaEnvioCalc)}</div>
          </div>

        </div>
      </div>
    `);

    // =========================
    // ✅ PRODUCTOS (tabla 3 columnas)
    // =========================
    let ordenes = data.ordenes ?? null;

    if (typeof ordenes === "string") {
      try { ordenes = JSON.parse(ordenes); } catch { ordenes = null; }
    }

    if (ordenes && Array.isArray(ordenes)) {
      // ok
    } else if (ordenes && typeof ordenes === "object") {
      ordenes = [ordenes];
    } else {
      ordenes = [];
    }

    $("#lista_productos").html("");
    const productosAgrupados = {};

    ordenes.forEach((orden) => {
      let productos = orden.productos ?? [];
      if (typeof productos === "string") {
        try { productos = JSON.parse(productos); } catch { productos = []; }
      }
      if (!Array.isArray(productos)) productos = [];

      productos.forEach((p) => {
        const nombre = p.nombre ?? "Producto";
        const cantidad = parseInt(p.cantidad ?? 0);
        const totalProducto = parseFloat(p.total_pagado ?? 0);

        let img = p.imagen ?? "";
        if (img && !img.startsWith("http") && !img.startsWith("/")) img = "/" + img;

        if (!productosAgrupados[nombre]) {
          productosAgrupados[nombre] = {
            img_path: img || "img/default.png",
            totalCantidad: 0,
            totalValor: 0,
            ordenes: []
          };
        }

        productosAgrupados[nombre].totalCantidad += (isNaN(cantidad) ? 0 : cantidad);
        productosAgrupados[nombre].totalValor += (isNaN(totalProducto) ? 0 : totalProducto);

        productosAgrupados[nombre].ordenes.push(
          `Orden #${orden.id_orden} <b class="mb-0 info_orden">(${cantidad} x ${money(p.precio)})</b>`
        );
      });
    });

    Object.keys(productosAgrupados).forEach((nombre) => {
      const p = productosAgrupados[nombre];
      $("#lista_productos").append(`
        <tr>
          <td class="d-flex align-items-center">
            <a>
              <img class="img-fluid rounded-1 me-3 d-none d-md-block" src="${p.img_path}" alt="" width="60">
            </a>
            <div class="flex-1">
              <h6 class="mb-0 text-nowrap">${nombre}</h6>
              <p class="mb-0">${p.ordenes.join(", ")}</p>
            </div>
          </td>
          <td class="align-middle text-center">${p.totalCantidad}</td>
          <td class="align-middle text-end">${money(p.totalValor)}</td>
        </tr>
      `);
    });

    // =========================
    // ✅ TOTALES (ordenBase.total)
    // =========================
    const subtotal = parseFloat(ordenBase.total ?? 0);
    $("#subtotal").text(money(subtotal));

    const ivaCalc = subtotal * 0.15;
    $("#iva").text(money(ivaCalc));

    $("#total").text(money(subtotal + ivaCalc));

  });

});

function printDiv(nombreDiv) {
  $("#col_valores").hide();
  $("#lista_productos td:nth-child(3)").hide();
  $("#div_totales").hide();
  $(".info_orden").hide();

  var contenido = document.getElementById(nombreDiv).innerHTML;
  var contenidoOriginal = document.body.innerHTML;

  document.body.innerHTML = contenido;
  window.print();
  document.body.innerHTML = contenidoOriginal;

  $("#col_valores").show();
  $("#lista_productos td:nth-child(3)").show();
  $("#div_totales").show();
  $(".info_orden").show();
}
