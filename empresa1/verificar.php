<?php
$menu = "empresas";
$sub_menu = "empresas";
require 'includes/header.php';
// foreach ($permisos as $value) {
//     if ($value["permiso"] == "Empresas" && $value["valor"] == "false") {
//         echo "<script>window.location.href = '" . $dashboard . "'</script>";
//     }
// }
?>
<title>Editar empresa</title>

<div class="card mb-3" id="contenido" style="display:none;">

    <div class="card-header">
        <!-- ✅ Mensaje superior de verificación (sin CSS, letra más pequeña) -->
        <div class="alert alert-info bg-light border rounded-3 p-3 mb-3">
            <div class="d-flex align-items-start gap-3">
                <div class="flex-shrink-0">
                    <span class="badge bg-primary p-2">
                        <i class="fas fa-shield-alt"></i>
                    </span>
                </div>

                <div class="flex-grow-1">
                    <h6 class="mb-2 fw-bold text-dark">VERIFICACIÓN DE EMPRESA</h6>

                    <div class="border rounded-2 p-2 mb-2 bg-white">
                        <div class="small fw-semibold text-primary">
                            “Verifica tu empresa y gana la confianza de más clientes”
                        </div>
                    </div>

                    <p class="small mb-2 text-secondary">
                        Completa tu proceso de verificación como vendedor en <b>FULMUV</b>. Al validar tu información y documentos,
                        obtendrás el <b>sello de Empresa Verificada</b>, visible para todos los usuarios de la plataforma.
                    </p>

                    <div class="small fw-semibold text-dark mb-1">Las empresas verificadas:</div>
                    <ul class="small mb-2 ps-3 text-secondary">
                        <li>Generan mayor confianza.</li>
                        <li>Reciben más consultas y contactos.</li>
                        <li>Incrementan sus oportunidades de venta.</li>
                        <li>Destacan frente a la competencia.</li>
                    </ul>

                    <div class="small fw-semibold text-dark">
                        Tu negocio se ve más profesional y confiable.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- ✅ En proceso -->
        <div class="alert alert-warning bg-light border rounded-3 p-3 mb-3 d-none" id="boxEnProceso">
            <div class="d-flex align-items-start gap-3">
                <div class="flex-shrink-0">
                    <span class="badge bg-warning text-dark p-2">
                        <i class="fas fa-hourglass-half"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold text-dark">Verificación en proceso</h6>
                    <div class="small text-secondary">
                        Sus datos están en proceso de verificación. El equipo de FULMUV revisará su información y será notificado.
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Rechazado -->
        <div class="alert alert-danger border rounded-3 p-3 mb-3 d-none" id="boxRechazado">
            <div class="d-flex align-items-start gap-3">
                <div class="flex-shrink-0">
                    <span class="badge bg-danger p-2">
                        <i class="fas fa-times-circle"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">Verificación rechazada</h6>
                    <div class="small">
                        <b>Observación:</b>
                        <span id="txtObservacion"></span>
                    </div>
                    <div class="small mt-2">
                        Carga nuevamente todos los documentos y vuelve a enviar para verificación.
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos personales y de contacto -->
        <!-- <h5 class="text-secondary">Datos personales y de contacto</h5> -->
        <div id="formVerificacion">
            <div class="row g-3 mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">RUC (Registro Único de Contribuyente) Actualizado, emitido por el SRI. (PDF)</label>
                    <input type="file" class="form-control" id="ruc" accept=".pdf">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Razón Social/Nombre Comercial</label>
                    <input type="text" class="form-control" id="nombre_comercial" placeholder="Nombre comercial">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Copia de cédula/pasaporte de propietario/representante legal (PDF)</label>
                    <input type="file" class="form-control" id="cedula" accept=".pdf">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombramiento o poder legal (PDF)</label>
                    <input type="file" class="form-control" id="nombramiento" accept=".pdf">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Patente Municipal de Funcionamiento (PDF)</label>
                    <input type="file" class="form-control" id="patente" accept=".pdf">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Planilla Servicio Básico (PDF)</label>
                    <input type="file" class="form-control" id="planilla" accept=".pdf">
                </div>

            </div>

            <button class="btn btn-primary w-100" id="btnGuardarEmpresa" onclick="saveEmpresaEditar()">
                <i class="fas fa-paper-plane me-2"></i> Enviar verificación
            </button>
        </div>

    </div>
</div>
<div class="row flex-center min-vh-100 py-4 text-center" id="verificado" style="display:none;">
    <div class="col-sm-10 col-md-8 col-lg-6 col-xxl-5">
        <!-- <a class="d-flex flex-center mb-4" href="../../index.html"><img class="me-2" src="../../assets/img/icons/spot-illustrations/falcon.png" alt="" width="58"><span class="font-sans-serif fw-bolder fs-5 d-inline-block">falcon</span></a> -->
        <div class="card">
            <div class="card-body p-4 p-sm-5">
                <div class="fw-black lh-1 text-300 fs-error"><span class="text-success fs-0 fas fa-check-double"></span></div>
                <p class="lead mt-4 text-800 font-sans-serif fw-semi-bold w-md-75 w-xl-100 mx-auto">Tu empresa se encuentra verificada</p>
                <hr>
                <p>Ahora saldrás con el ícono de verificado en la lista de empresas mientras los clientes busquen productos.
            </div>
        </div>
    </div>
    <!-- Conexión API js -->
    <script src="js/verificar.js?v1.0.0.3"></script>
    <!-- Alerts js -->
    <script src="js/alerts.js"></script>
    <?php
    require 'includes/footer.php';
    ?>