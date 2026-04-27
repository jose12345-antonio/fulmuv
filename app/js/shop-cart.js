let itemsPerPage = 12;
let currentPage = 1;
let productosData = [];

let sortOption = "todos"; // opciones: "mayor", "menor", "todos"
let searchText = "";
let id_empresa = $("#id_empresa").val();
let subcategoriasSeleccionadas = [];
let precioMin = 0;
let precioMax = Infinity;
$(document).ready(function () {

    actualizarEstadoBtnTyC();
    actualizarIconoCarrito();

    $("#breadcrumb").append(`
        <a href="vendor.php" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
        <span></span> Su Carrito de Compra
    `)

    actualizarshopCart()
})

// Cambia estado cuando marcan/desmarcan TyC
$("#aceptoTyC").on("change", actualizarEstadoBtnTyC);

// Guard: evita navegación si está deshabilitado (por si algún estilo no aplica)
$(document).on("click", "#btnContinuarOrden", function (e) {
    if ($(this).hasClass("disabled") || $(this).attr("aria-disabled") === "true") {
        e.preventDefault();
        // feedback opcional
        Swal.fire("warning", "¡Debes aceptar los términos y condiciones!")
        $("#aceptoTyC").focus();
    }
});

function actualizarEstadoBtnTyC() {
    const checked = $("#aceptoTyC").is(":checked");
    const $btn = $("#btnContinuarOrden");

    if (checked) {
        $btn.removeClass("disabled")
            .attr("aria-disabled", "false")
            .removeAttr("tabindex");
    } else {
        $btn.addClass("disabled")
            .attr("aria-disabled", "true")
            .attr("tabindex", "-1");
    }
}

function actualizarshopCart() {
    let carrito = [];
    try {
        const stored = JSON.parse(localStorage.getItem("carrito"));
        const now = new Date().getTime();

        if (stored && Array.isArray(stored.data) && now - stored.timestamp < 2 * 60 * 60 * 1000) {
            carrito = stored.data;
        }
    } catch (e) {
        console.warn("carrito malformado o expirado");
    }

    // ✅ SI ESTÁ VACÍO -> mostrar alerta bonita + limpiar vista
    if (!carrito || carrito.length === 0) {

        $("#listaProductoShopCart").empty();
        $("#listaProductoShopCartMobile").empty();

        // Totales a 0
        $("#cart_carrito_amount").text(formatoMoneda.format(0));
        $("#cart_subtotal_amount").text(formatoMoneda.format(0));
        $("#cart_total_amount").text(formatoMoneda.format(0));
        $("#cart_ahorro_amount").text(`-${formatoMoneda.format(0)}`);
        $("#totalCarritoShop").text(0);

        // ✅ opcional: desmarcar TyC y deshabilitar botón
        $("#aceptoTyC").prop("checked", false);
        actualizarEstadoBtnTyC();

        $("#btnContinuarOrden").addClass("d-none")

        // ✅ Swal bonito (evita spameo: solo si no está abierto)
        if (!Swal.isVisible()) {
            Swal.fire({
                icon: "info",
                title: "Tu carrito está vacío",
                html: `
                    <div style="font-size:14px;line-height:1.45">
                        Agrega productos al carrito para realizar tu compra. <br>
                        Cuando tengas productos listos, podrás continuar a crear tu orden.
                    </div>
                `,
                confirmButtonText: "Ir a comprar",
                showCancelButton: false,
                heightAuto: false
            }).then((r) => {
                if (r.isConfirmed) {
                    window.location.href = "https://fulmuv.com/"; // ✅ o a donde quieras mandar a comprar
                }
            });
        }

        return; // 👈 importante: no seguir renderizando
    }

    // ============================
    // 👇 TU LÓGICA NORMAL (igual)
    // ============================
    let totalValor = 0;          // total estimado (con IVA removido si iva==1)
    let totalValorCarrito = 0;   // carrito original (sin tocar)
    let totalAhorro = 0;         // ahorro por descuento + ahorro por iva incluido
    let totalAhorroIVA = 0;      // SOLO el IVA retirado (para el modal)
    let ivaDetalles = [];        // detalle por producto (para el modal)
    let totalAhorroUnitario = 0;
    let totalEstimado = 0;
    $("#listaProductoShopCart").empty();
    $("#listaProductoShopCartMobile").empty();

    $("#btnContinuarOrden").removeClass("d-none")

    console.log(carrito)
    carrito.forEach((item, index) => {
        const precioOriginal = parseFloat(item.precio);
        const precioConDescuento = parseFloat(item.valor_descuento || item.precio);

        const cantidad = parseInt(item.cantidad) || 1;
        const subtotal = precioConDescuento * cantidad;
        const subtotalCarrito = precioConDescuento * cantidad;

        const ahorroUnitario = precioOriginal - precioConDescuento;
        const ahorroDescuento = ahorroUnitario * cantidad;

        const tieneIVA = parseInt(item.iva) === 1;

        // IVA 15%
        const ivaCalculado = subtotal * 0.15;

        let subtotalFinal = subtotal;
        let ahorroIVA = 0;

        if (tieneIVA) {
            // 🟢 Precio ya incluye IVA → quitarlo
            subtotalFinal = subtotal - ivaCalculado;
            ahorroIVA = ivaCalculado;
        } else {
            // 🔵 Precio sin IVA → beneficio cliente
            subtotalFinal = subtotal;
            ahorroIVA = ivaCalculado;
        }

        // Totales
        totalValorCarrito += subtotalCarrito;
        totalValor += subtotalFinal;
        totalAhorro += (ahorroIVA);
        totalAhorroIVA += ahorroIVA;
        const ahorroTotalDeEsteItem = ahorroUnitario * cantidad;
        totalAhorroUnitario += ahorroTotalDeEsteItem;

        // Guardar detalle modal
        ivaDetalles.push({
            nombre: item.nombre,
            codigo: item.codigo,
            cantidad,
            precioUnitario: precioConDescuento,
            subtotal,
            ivaCalculado,
            tipo: tieneIVA ? "incluido" : "beneficio"
        });



        const badgeIVA = (parseInt(item.iva) === 1)
            ? `<span class="badge bg-success ms-2">IVA incluido</span>`
            : '';
        // ✅ Desktop (tabla)
        $("#listaProductoShopCart").append(`
            <tr class="border-bottom m-2" data-id="${item.id}">
                <td>
                    <img src="../${item.imagen}" alt="${item.nombre}"  class="me-2 ms-2"
                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 6px;" 
                         onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">
                </td>
                <td>
                    <h6 class="mb-5" style="font-size: 14px">Código del producto: ${item.codigo}</h6>
                    <h5 class="mb-5 fw-bold">
                        ${capitalizarPrimeraLetra(item.nombre)}
                        <span class="h6">${badgeIVA}</span>
                    </h5>
                    <div class="text-muted small"><span class="limitar-lineas">Categoría: ${capitalizarPrimeraLetra(item.nombre_categoria) || ""}</span></div>
                    <div class="text-muted small"><span class="limitar-lineas">Peso: ${item.peso} KG</span></div>
                    <div class="mt-2">
                        <a href="#" class="text-danger btn-eliminar-producto" data-id="${item.id}">
                            <i class="fi-rs-trash"></i> Eliminar
                        </a>
                    </div>
                </td>
                <td>
                    ${precioOriginal > precioConDescuento
                ? `<del class="text-danger me-1">${formatoMoneda.format(precioOriginal)}</del> 
                           <span class="fw-bold">${formatoMoneda.format(precioConDescuento)}</span>`
                : `<span class="fw-bold">${formatoMoneda.format(precioOriginal)}</span>`
            }
                </td>
                <td>
                    <div class="detail-extralink mr-15">
                        <div class="detail-qty border radius">
                            <a href="#" class="qty-down"><i class="fi-rs-angle-small-down"></i></a>
                            <input type="text" class="qty-val qty-val-${item.id}" value="${item.cantidad}" readonly>
                            <a href="#" class="qty-up"><i class="fi-rs-angle-small-up"></i></a>
                        </div>
                    </div>
                </td>
                <td class="pt-20 text-center">
                    <strong class="text-brand" style="font-size: 17px">${formatoMoneda.format(subtotal)}</strong>
                </td>
            </tr>
        `);

        // ✅ Mobile (cards)
        $("#listaProductoShopCartMobile").append(`
          <div class="card mb-3" data-id="${item.id}">
            <div class="card-body">
              <div class="d-flex gap-2">
                <img src="../${item.imagen}" alt="${item.nombre}"
                  class="rounded border flex-shrink-0"
                  style="width:72px;height:72px;object-fit:cover;"
                  onerror="this.onerror=null;this.src='../img/FULMUV-NEGRO.png';">

                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="me-2">
                      <div class="fw-bold">
                        ${capitalizarPrimeraLetra(item.nombre)}
                        ${badgeIVA}
                    </div>
                      <div class="text-muted small">Código: ${item.codigo || "—"}</div>
                    </div>

                    <button type="button" class="btn btn-link text-danger p-0 btn-eliminar-producto"
                      data-id="${item.id}">
                      <i class="fi-rs-trash"></i>
                    </button>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                      ${precioOriginal > precioConDescuento
                ? `<div class="small"><del class="text-danger me-1">${formatoMoneda.format(precioOriginal)}</del></div>
                           <div class="fw-bold">${formatoMoneda.format(precioConDescuento)}</div>`
                : `<div class="fw-bold">${formatoMoneda.format(precioOriginal)}</div>`
            }
                    </div>

                    <div class="btn-group" role="group" aria-label="Cantidad">
                      <button type="button" class="btn btn-outline-secondary btn-sm qty-down" aria-label="Disminuir">
                        <i class="fi-rs-angle-small-down"></i>
                      </button>
                      <button type="button" class="btn btn-light btn-sm disabled">
                        <span class="qty-val qty-val-${item.id}">${item.cantidad}</span>
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm qty-up" aria-label="Aumentar">
                        <i class="fi-rs-angle-small-up"></i>
                      </button>
                    </div>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">Subtotal</div>
                    <div class="fw-bold text-brand">${formatoMoneda.format(subtotal)}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `);
    });

    totalEstimado = totalValor + totalAhorro - totalAhorroUnitario;

    $("#cart_carrito_amount").text(formatoMoneda.format(totalValorCarrito));
    $("#cart_subtotal_amount").text(formatoMoneda.format(totalEstimado));
    $("#cart_total_amount").text(formatoMoneda.format(totalValor + totalAhorro));
    $("#cart_iva_amount").text(`${formatoMoneda.format(totalAhorro)}`);
    $("#cart_ahorro_amount").text(`-${formatoMoneda.format(totalAhorroUnitario)}`);

    $("#totalCarritoShop").text(carrito.length);

    // ✅ Modal IVA: contenido
    function renderIvaModal(detalles, totalIva) {
        const $cont = $("#ivaDetalleContenido");
        $cont.empty();

        if (!detalles.length) {
            $cont.html(`
      <div class="text-muted small">
        No hay productos con <strong>IVA incluido</strong> en este carrito.
      </div>
    `);
            $("#ivaTotalRetirado").text(formatoMoneda.format(0));
            return;
        }

        const rows = detalles.map(d => `
            <tr>
            <td>
                <div class="fw-semibold">${capitalizarPrimeraLetra(d.nombre)}</div>
                <div class="text-muted small">Código: ${d.codigo || "—"}</div>
            </td>
            <td class="text-end">${d.cantidad}</td>
            <td class="text-end">${formatoMoneda.format(d.precioUnitario)}</td>
            <td class="text-end">${formatoMoneda.format(d.subtotal)}</td>
            <td class="text-end text-primary fw-bold">
                ${d.tipo === "incluido"
                ? "- " + formatoMoneda.format(d.ivaCalculado)
                : "+ " + formatoMoneda.format(d.ivaCalculado)}
            </td>
            </tr>
        `).join("");
        $cont.html(`
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-end">Cant.</th>
            <th class="text-end">P. Unit</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">IVA (15%)</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
    <div class="small text-muted">
    Productos con <strong>IVA incluido</strong>: se retiró el 15% del precio.<br>
    Productos sin IVA: se aplicó un <strong>beneficio equivalente al 15%</strong>.
    </div>
  `);

        $("#ivaTotalRetirado").text(formatoMoneda.format(totalIva));
    }

    renderIvaModal(ivaDetalles, totalAhorroIVA);

    // ✅ Si no hay IVA incluido, igual permitimos abrir modal, pero puedes ocultar el botón:
    if (ivaDetalles.length === 0) {
        $("#btnInfoIVA").addClass("opacity-50");
    } else {
        $("#btnInfoIVA").removeClass("opacity-50");
    }
}


async function eliminarAllCarrito() {
    localStorage.removeItem("carrito");
    actualizarIconoCarrito();
    actualizarshopCart()

}
$(document).on("click", ".qty-up", function (e) {
    e.preventDefault();
    const id = $(this).closest("[data-id]").data("id"); // ✅ tr o card
    modificarCantidadCarrito(id, 1);
    actualizarshopCart();
    actualizarIconoCarrito();
});

$(document).on("click", ".qty-down", function (e) {
    e.preventDefault();
    const id = $(this).closest("[data-id]").data("id"); // ✅ tr o card
    modificarCantidadCarrito(id, -1);
    actualizarshopCart();
    actualizarIconoCarrito();
});

$(document).on("click", ".btn-eliminar-producto", function (e) {
    e.preventDefault();
    const id = $(this).data("id") || $(this).closest("[data-id]").data("id");
    eliminarDelCarrito(id);
    actualizarshopCart();
    actualizarIconoCarrito();
});