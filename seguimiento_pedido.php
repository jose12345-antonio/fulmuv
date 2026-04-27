<?php
include 'includes/header.php';

$numero_orden = $_GET["q"];

echo '<input type="hidden" placeholder="" id="numero_orden" class="form-control" value=' . $numero_orden . ' />';
?>
<link rel="canonical" href="https://fulmuv.com/seguimiento_pedido.php?q=<?php echo $numero_orden; ?>">

<style>
    @media (min-width: 992px) {

        /* Solo aplica en pantallas grandes */
        .sticky-checkout-box {
            position: sticky;
            top: 100px;
            /* Espacio desde el top del viewport */
        }
    }

    .seguimiento-estado .step {
        text-align: center;
        width: 20%;
        position: relative;
    }

    .seguimiento-estado .circle {
        width: 30px;
        height: 30px;
        margin: 0 auto;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .seguimiento-estado .line {
        height: 2px;
        background-color: #ccc;
        flex-grow: 1;
        margin: 0 5px;
    }

    .seguimiento-estado {
        gap: 0.5rem;
    }

    .pago-card {
        max-width: 100%;
        border-radius: 20px;
        padding: 28px 28px 24px;
        background: linear-gradient(145deg, #016b84, #004E60, #003a48);
        position: relative;
        color: #ffffff;
        font-size: 20px;

    }

    .pago-card h3 {
        font-weight: 900;
        letter-spacing: .5px;
        margin: 0 0 6px 0;
        text-transform: uppercase;
        color: #ffffff;
    }

    .pago-card .sub {
        font-weight: 700;
        letter-spacing: .3px;
        color: #c2f0f6;
    }

    .pago-chip {
        width: 62px;
        height: 44px;
        border-radius: 10px;
        background: linear-gradient(145deg, #e9c55f, #b98d28);
        box-shadow: inset 0 3px 6px rgba(0, 0, 0, .25);
    }

    .pago-item b {
        display: inline-block;
        min-width: 120px;
        color: #ffffff;
    }

    .pago-item {
        color: #e6f9fc;
    }

    .pago-divider {
        border-top: 1px solid rgba(255, 255, 255, .25);
        margin: 10px 0 14px;
    }

    .meta-line {
        font-size: .9rem;
        color: #d0f2f6;
        opacity: .9;
    }

    a {
        color: #c2f0f6;
        text-decoration: underline;
    }

    @media (max-width:576px) {
        .pago-card {
            padding: 20px;
        }

        .pago-item b {
            min-width: 110px;
        }
    }
</style>

<div class="container mt-30">
    <div class="row">
        <div class="col-lg-12 m-auto">
            <div class="row">
                <div class="col-md-3">
                    <div class="dashboard-menu">
                        <ul class="nav flex-column" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="false"><i class="fi-rs-shopping-bag mr-10"></i>Pedidos Pendientes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="track-orders-tab" data-bs-toggle="tab" href="#track-orders" role="tab" aria-controls="track-orders" aria-selected="false"><i class="fi-rs-receipt mr-10"></i>Datos de Facturación</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="address-tab" data-bs-toggle="tab" href="#address" role="tab" aria-controls="address" aria-selected="true"><i class="fi-rs-marker mr-10"></i>Mi dirección a domicilio</a>
                            </li>

                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade active show" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="mb-0">Tu Orden</h3>
                                    <div class="alert alert-primary mt-3 mb-0" role="alert">
                                        <div class="fw-bold mb-1">Importante para tu pedido</div>
                                        <div class="small">
                                            Para ver información completa de tu pedido, inicia sesión con el correo y contraseña usados al generar la orden.
                                            El pago de los productos se realiza directamente con la empresa (proveedor).
                                            Si tu pedido incluye envío a domicilio, FULMUV gestionará el proceso logístico y deberás esperar los valores de pago del envío.
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-borderless" id="tabla-productos">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>No. Orden</th>
                                                    <th>Fecha</th>
                                                    <th>Status</th>
                                                    <th>Nombre del Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Descuento</th>
                                                    <th>Precio</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="pago-bg mb-3 d-none">
                                        <div class="pago-card">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h3 class="mb-1">Datos de pago</h3>
                                                    <div class="sub text-uppercase">CHANGETHEMOVE S.A.S.</div>
                                                    <div class="text-muted small">RUC: 1793223041001</div>
                                                </div>
                                                <div class="pago-chip"></div>
                                            </div>

                                            <hr class="pago-divider">

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="pago-item"><b>Banco:</b> BANCO PICHINCHA</div>
                                                    <div class="pago-item"><b>Tipo Cta:</b> Cuenta Corriente</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="pago-item"><b># Cta:</b> 2100338115</div>
                                                    <div class="pago-item"><b>Correo:</b> <a href="mailto:gesLones@fulmuv.com">gesLones@fulmuv.com</a></div>
                                                </div>
                                            </div>

                                            <hr class="pago-divider">

                                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                                <div class="meta-line">
                                                    Enviar comprobante a <a href="mailto:gesLones@fulmuv.com">gesLones@fulmuv.com</a>
                                                </div>
                                                <div class="meta-line">
                                                    Cobertura de seguro del envío: <b>90%</b> del valor declarado
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="track-orders" role="tabpanel" aria-labelledby="track-orders-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card mb-3 mb-lg-0">
                                        <div class="card-header">
                                            <h3 class="mb-0">Datos de Facturación</h3>
                                        </div>
                                        <div class="card-body" id="facturacion-entrega">
                                            <!-- Dirección llenada con JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card mb-3 mb-lg-0">
                                        <div class="card-header">
                                            <h3 class="mb-0">Dirección de entrega</h3>
                                        </div>
                                        <div class="card-body" id="direccion-entrega">
                                            <!-- Dirección llenada con JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleEnvio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del costo de envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body" id="detalleEnvioBody">
                <!-- se llena por JS -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<?php
include 'includes/footer.php';
?>
<script src="js/seguimiento_pedido.js?v1.0.0.0.0.0.0.0.0.0.5"></script>

<script>

</script>
