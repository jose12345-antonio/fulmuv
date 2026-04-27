<?php

?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Fulmuv | Crear Empresa</title>


    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <!-- <link rel="apple-touch-icon" sizes="180x180" href="../theme/public/assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../theme/public/assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../theme/public/assets/img/favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="../theme/public/assets/img/favicons/favicon.ico"> -->
    <link rel="manifest" href="../theme/public/assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="../theme/public/assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
    <script src="../theme/public/assets/js/config.js"></script>
    <script src="../theme/public/vendors/simplebar/simplebar.min.js"></script>



    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
    <link href="../theme/public/vendors/simplebar/simplebar.min.css" rel="stylesheet">
    <link href="../theme/public/assets/css/theme-rtl.css" rel="stylesheet" id="style-rtl">
    <link href="../theme/public/assets/css/theme.css" rel="stylesheet" id="style-default">
    <link href="../theme/public/assets/css/user-rtl.css" rel="stylesheet" id="user-style-rtl">
    <link href="../theme/public/assets/css/user.css" rel="stylesheet" id="user-style-default">
    <script>
        var isRTL = JSON.parse(localStorage.getItem('isRTL'));
        if (isRTL) {
            var linkDefault = document.getElementById('style-default');
            var userLinkDefault = document.getElementById('user-style-default');
            linkDefault.setAttribute('disabled', true);
            userLinkDefault.setAttribute('disabled', true);
            document.querySelector('html').setAttribute('dir', 'rtl');
        } else {
            var linkRTL = document.getElementById('style-rtl');
            var userLinkRTL = document.getElementById('user-style-rtl');
            linkRTL.setAttribute('disabled', true);
            userLinkRTL.setAttribute('disabled', true);
        }
    </script>
</head>


<body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <div class="container-fluid">
            <div class="row min-vh-100 flex-center g-0">
                <div class="col-lg-10 col-xxl-10 py-3">
                    <div class="col-sm-12 col-md-10 px-sm-0 align-self-center mx-auto py-5">
                        <div class="row justify-content-center g-0">

                            <a class="d-flex flex-center mb-4"><img class="me-2" src="../img/FULMUV-NEGRO.png" alt="" width="250"></a>

                            <div class="col-lg-12 col-xl-12 col-xxl-12">
                                <div class="card">
                                    <!-- <div class="card-header text-center" style="background: #00686f;">
                                        
                                        <img src="../img/FULMUV-BLANCO.png" width="250">
                                    </div> -->
                                    <div class="card-body p-4">
                                        <div class="row flex-between-center mb-2">
                                            <div class="col-auto">
                                                <h5>Registro</h5>
                                            </div>
                                            <div class="col-auto fs--1 text-600"><span class="mb-0 undefined">¿Ya tienes una cuenta?</span> <span><a href="login.php">Login</a></span></div>
                                        </div>
                                        <div class="p-4">
                                            <div class="row g-2">
                                                <!-- Datos básicos -->
                                                <div class="col-md-6 mb-3"><label class="form-label">Nombre</label><input class="form-control" id="nombre" type="text" placeholder="nombre" oninput="this.value = this.value.toUpperCase()" /></div>
                                                <div class="col-md-6 mb-3"><label class="form-label">Dirección</label><input class="form-control" type="text" id="direccion"></div>
                                                <div class="col-md-12 mb-3"><label class="form-label">Logo o Imagen</label><input class="form-control" type="file" id="imagen_empresa" accept="image/*"></div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Información del local</label>
                                                    <div class="row">
                                                        <div class="col-md-4"><input class="form-check-input" type="checkbox" id="guardiania"><label class="form-check-label" for="guardiania">Tiene guardia</label></div>
                                                        <div class="col-md-4"><input class="form-check-input" type="checkbox" id="camaras"><label class="form-check-label" for="camaras">Tiene cámaras de seguridad</label></div>
                                                        <div class="col-md-4"><input class="form-check-input" type="checkbox" id="parqueadero"><label class="form-check-label" for="parqueadero">Tiene parqueadero</label></div>
                                                        <div class="col-md-12 mt-2"><label class="form-label">Parqueadero</label><select id="parqueadero_tipo" class="form-select">
                                                                <option value="">Seleccione</option>
                                                                <option>Interno</option>
                                                                <option>Externo</option>
                                                            </select></div>
                                                    </div>
                                                </div>

                                                <!-- Tiempo en el Mercado -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Tiempo en el mercado</label>
                                                    <div class="d-flex gap-2">
                                                        <input class="form-control" id="tiempo_anos" type="number" placeholder="Años" />
                                                        <input class="form-control" id="tiempo_meses" type="number" placeholder="Meses" />
                                                    </div>
                                                </div>

                                                <!-- Garantías del Vendedor -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Garantías del Vendedor</label>
                                                    <select class="form-select mb-2" id="garantia_vendedor" onchange="$('#garantia_detalle').toggle(this.value === 'SI')">
                                                        <option value="NO">NO</option>
                                                        <option value="SI">SÍ</option>
                                                    </select>
                                                    <div id="garantia_detalle" style="display: none;">
                                                        <input class="form-control mb-2" id="garantia_tiempo" placeholder="Tiempo de garantía">
                                                        <input class="form-control mb-2" id="garantia_condiciones" placeholder="Condiciones">
                                                        <input class="form-control" id="garantia_terminos" placeholder="Términos">
                                                    </div>
                                                </div>

                                                <!-- Instalación -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">¿Instalan los productos?</label>
                                                    <select class="form-select mb-2" id="instalacion_producto" onchange="$('#instalacion_detalle').toggle(this.value === 'SI')">
                                                        <option value="NO">NO</option>
                                                        <option value="SI">SÍ</option>
                                                    </select>
                                                    <div id="instalacion_detalle" style="display: none;">
                                                        <select class="form-select mb-2" id="instalacion_costo_tipo">
                                                            <option value="">Seleccione</option>
                                                            <option value="sin_costo">Sin costo adicional</option>
                                                            <option value="con_costo">Con costo adicional</option>
                                                        </select>
                                                        <input class="form-control" id="instalacion_valor" placeholder="Costo adicional (en caso de aplicar)">
                                                    </div>
                                                </div>

                                                <!-- Horario de Atención -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Horario de Atención</label>
                                                    <select class="form-select mb-2" id="horario_atencion" onchange="$('#horario_otro').toggle(this.value === 'OTRO')">
                                                        <option value="24H">Atienden las 24 horas</option>
                                                        <option value="7DIAS">Atienden los 7 días</option>
                                                        <option value="OTRO">Otro</option>
                                                    </select>
                                                    <div id="horario_otro" style="display: none;">
                                                        <textarea class="form-control" placeholder="Especificar días y horas" id="detalle_horario"></textarea>
                                                    </div>
                                                </div>

                                                <!-- Contacto Llamadas -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Números para llamadas</label>
                                                    <input class="form-control mb-2" type="text" id="telefono_contacto" placeholder="Ej. 0999999999">
                                                </div>

                                                <!-- Contacto WhatsApp -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Números para WhatsApp</label>
                                                    <input class="form-control mb-2" type="text" id="whatsapp_contacto" placeholder="Ej. 0999999999">
                                                </div>

                                                <hr>

                                                <!-- Contacto Llamadas -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Usuario</label>
                                                    <input class="form-control mb-2" type="text" id="username" placeholder="Usuario">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Correo</label>
                                                    <input class="form-control mb-2" type="text" id="email" placeholder="Correo">
                                                </div>

                                                <!-- Contacto WhatsApp -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Contraseña</label>
                                                    <input class="form-control mb-2" type="text" id="password" placeholder="Contraseña">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Confirmar Contraseña</label>
                                                    <input class="form-control mb-2" type="text" id="repeat_password" placeholder="Confirmar Contraseña">
                                                </div>

                                            </div>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input me-2" id="checkTerminoCondiciones" type="checkbox" value="">
                                                <label class="form-check-label mb-0" for="checkTerminoCondiciones">
                                                    He leído y acepto los <a href="terminos_condiciones.php" target="_blank" class="fs-10">
                                                    Términos y Condiciones
                                                    </a>
                                                </label>
                                            </div>


                                        </div>
                                        <div class="mb-3">
                                            <button class="btn d-block w-100 mt-3 text-white" type="submit" name="submit" onclick="saveEmpresa()" style="background: #004E60;">Guardar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->


    <!-- ===============================================-->
    <!--    JavaScripts-->
    <!-- ===============================================-->

    <script src="../theme/public/vendors/popper/popper.min.js"></script>
    <script src="../theme/public/vendors/bootstrap/bootstrap.min.js"></script>
    <script src="../theme/public/vendors/anchorjs/anchor.min.js"></script>
    <script src="../theme/public/vendors/is/is.min.js"></script>
    <script src="../theme/public/vendors/fontawesome/all.min.js"></script>
    <script src="../theme/public/vendors/lodash/lodash.min.js"></script>
    <script src="../theme/public/vendors/list.js/list.min.js"></script>
    <script src="../theme/public/assets/js/theme.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Conexión API js -->
    <script src="js/crear_empresa.js?v2.0.1"></script>

    <!-- Alerts js -->
    <script src="js/alerts.js"></script>

    <div id="alert">

    </div>

</body>

</html>