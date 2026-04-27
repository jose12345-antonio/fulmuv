<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">
<link rel="canonical" href="https://fulmuv.com/crear_empresa.php">

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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <link href="../theme/public/vendors/select2/select2.min.css" rel="stylesheet">
    <link href="../theme/public/vendors/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <script src="https://cdn.paymentez.com/ccapi/sdk/payment_checkout_stable.min.js"></script>
    <script src="https://cdn.paymentez.com/ccapi/sdk/payment_checkout_3.0.0.min.js"></script>

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

    <style>
        .tok_btn {
            padding: 10px 20px;
            margin: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
        }

        #response {
            margin-top: 20px;
        }


        #mapaEntrega {
            width: 100%;
            height: 60vh;
            min-height: 320px;
            border-radius: .5rem;
        }

        .map-wrapper {
            position: relative;
        }

        /* Ubica el input arriba a la derecha del mapa */
        .map-search {
            position: absolute;
            top: 0px;
            right: 110px;
            /* <- antes estaba left */
            left: auto;
            /* <- importante para soltar el anclaje izquierdo */
            z-index: 2000;
        }

        /* Para que el autocomplete siempre quede visible */
        .pac-container {
            z-index: 20000 !important;
        }

        /* (Opcional) en pantallas pequeñas que no quede cortado */
        @media (max-width: 576px) {
            .map-search {
                left: 8px;
                right: 8px;
            }

            /* se centra con margen a ambos lados */
            #buscarDireccion {
                width: 100%;
            }
        }
    </style>

</head>


<body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <div class="container-fluid">
            <div class="row min-vh-100 flex-center g-0">
                <div class="col-lg-12 col-xxl-12 py-3">
                    <div class="col-sm-12 col-md-11 px-sm-0 align-self-center mx-auto py-5">
                        <div class="row justify-content-center g-0">

                            <a class="d-flex flex-center mb-4"><img class="me-2" src="../img/FULMUV LOGO-13.png" alt="" width="250"></a>

                            <div class="col-lg-12 col-xl-12 col-xxl-12">
                                
                                <div class="row flex-between-center mb-2">
                                    <div class="col-auto fs--1 text-600"><span class="mb-0 undefined">¿Ya tienes una cuenta?</span> <span><a href="login.php">Login</a></span></div>
                                </div>

                                <!-- <div class="card">
                                    <div class="card-body p-4">
                                        <div class="row flex-between-center mb-2">
                                            <div class="col-auto">
                                                <h5>Registro</h5>
                                            </div>
                                            <div class="col-auto fs--1 text-600"><span class="mb-0 undefined">¿Ya tienes una cuenta?</span> <span><a href="login.php">Login</a></span></div>
                                        </div>
                                        <div class="p-4">
                                            <div class="row g-2">
                                                
                                                <div class="col-md-6 mb-3"><label class="form-label">Nombre de empresa</label><input class="form-control" id="nombre" type="text" placeholder="Nombre de empresa" oninput="this.value = this.value.toUpperCase()" /></div>
                                                <div class="col-md-6 mb-3"><label class="form-label">Nombre del titular</label><input class="form-control" id="nombre_titular" type="text" placeholder="Nombre del titular" oninput="this.value = this.value.toUpperCase()" /></div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Tipo de local</label>
                                                    <select class="form-control" type="text" id="tipo_local">
                                                        <option value="">Seleccione tipo de local</option>
                                                        <option value="fisico">Físico</option>
                                                        <option value="online">Online</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3"><label class="form-label">Dirección</label><input class="form-control" type="text" id="direccion"></div>
                                                
                                                

                                               

                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Número para llamadas</label>
                                                    <input class="form-control mb-2" type="text" id="telefono_contacto" placeholder="Ej. 0999999999">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Número para WhatsApp</label>
                                                    <input class="form-control mb-2" type="text" id="whatsapp_contacto" placeholder="Ej. 0999999999">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Categoría Principal</label>
                                                    <select class="form-control" type="text" id="categoria_principal" onchange="obtenerCategorias()">
                                                        <option value="">Seleccione categoría Principal</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Categoría</label>
                                                    <select class="form-control" type="text" id="categoria">
                                                        <option value="">Seleccione categoría</option>
                                                    </select>
                                                </div>

                                                <hr>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Usuario</label>
                                                    <input class="form-control mb-2" type="text" id="username" placeholder="Usuario">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Correo</label>
                                                    <input class="form-control mb-2" type="text" id="email" placeholder="Correo">
                                                </div>

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
                                </div> -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row justify-content-center">
                                            <div class="col-12 text-center mb-4">
                                                <div class="fs-8">Membresías</div>
                                                <h4 class="fs-8">Inversión mínima, multiplicación máxima. Está en ti. <br class="d-none d-md-block" />Planes para particulares, empresas y negocios.</h3>
                                            </div>
                                            <div class="col-md-12 col-lg-12 col-xl-10">
                                                <div id="contenedor-membresias" class="row justify-content-center align-items-stretch"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light d-flex justify-content-end">
                                        <div class="me-3">
                                            <div class="input-group input-group-sm">
                                                <input class="form-control" type="text" placeholder="Código agente" id="agente" />
                                                <button id="btnAplicarCodigo" class="btn btn-outline-secondary border-300 btn-sm shadow-none" type="submit">Aplicar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
    </main>

    <div class="modal fade" id="modal-pago" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg mt-6" role="document">
            <div class="modal-content border-0">
                <div class="position-absolute top-0 end-0 mt-3 me-3 z-index-1"><button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body p-0">
                    <div class="bg-light rounded-top-lg py-3 ps-4 pe-6">
                        <h4 class="mb-1" id="">Pago FULMUV</h4>
                    </div>
                    <div class="p-4">

                        <div class="col align-items-center">

                            <input value="" hidden id="tipo">
                            <input value="" hidden id="id_membresia_producto">

                            <!--1. Create an element to contain the dynamic form.-->
                            <div id='payment_example_div'>
                                <div id='tokenize_example'></div>
                                <div class="border-bottom border-dashed my-3"></div>
                                <div class="fs-12 fw-semi-bold">Total: <span class="text-primary" id="totalPago"></span></div>

                                <div id="tokenize_response"></div>

                                <div class="row g-3 mt-2">
                                    <div class="col-12 col-md-6" id="wrapperTipo" style="display:none;">
                                        <label for="selectTipoDiferido" class="form-label fw-semi-bold">Forma de pago</label>
                                        <select id="selectTipoDiferido" class="form-select" onchange="onTipoChange(this.value)">
                                            <!-- options se llenan dinámicamente según el plan -->
                                        </select>
                                        <div class="form-text" id="ayudaTipo"></div>
                                    </div>

                                    <div class="col-12 col-md-6" id="wrapperMeses" style="display:none;">
                                        <label for="selectMeses" class="form-label fw-semi-bold">Meses</label>
                                        <select id="selectMeses" class="form-select" disabled onchange="onMesesChange(this.value)">
                                            <!-- options se llenan dinámicamente según el tipo -->
                                        </select>
                                        <div class="form-text" id="ayudaMeses"></div>
                                    </div>
                                </div>

                                <div class="mt-2" id="cuotaBox" style="display:none;">
                                    <small class="text-700">Cuota estimada: <span id="cuotaEstimada">$0.00</span> / mes</small>
                                </div>


                                <div class="form-check mt-2">
                                    <input class="form-check-input me-2" id="checkTerminoCondicionesPago" type="checkbox" value="">
                                    <label class="form-check-label mb-0" for="checkTerminoCondicionesPago">
                                        Acepto las Condiciones de Uso del Servicio de Pago en Línea de FULMUV y autorizo el cargo recurrente del plan seleccionado a través de la pasarela NUVEI. Entiendo que la renovación puede cancelarse desde mi perfil de vendedor, y confirmo que soy titular o estoy autorizado para usar este medio de pago.
                                        <p><a href="../documentos/4_Condiciones Pago en Línea de FULMUV.pdf" target="_blank" class="fs-10 fw-bold">
                                                Ver Condiciones de Pago en Línea
                                            </a>
                                        </p>
                                    </label>
                                </div>
                                <button id='tokenize_btn' class='tok_btn'>Pagar</button>
                                <p class="fs--1 mt-3 mb-0">Al hacer clic en el botón <strong>Pagar</strong>, se procederá a realizar el pago y registrar la membresia.</p>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer justify-contend-end">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Mapa (fuera del modal de crear empresa) -->
    <div class="modal fade" id="modalMapa" tabindex="-1" aria-labelledby="modalMapaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMapaLabel">Selecciona una ubicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="map-wrapper position-relative">
                        <div id="mapaEntrega"></div>

                        <div class="map-search">
                            <div class="input-group">
                                <input id="buscarDireccion" class="form-control form-control-sm"
                                    style="width: clamp(200px, 39vw, 400px); margin-top:10px; background:#fff; height:40px"
                                    placeholder="Buscar dirección..." />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="guardarUbicacion">Guardar dirección</button>
                </div>
            </div>
        </div>
    </div>

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
    <script src="../theme/public/vendors/select2/select2.full.min.js"></script>
    <script src="../theme/public/vendors/select2/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="../theme/public/vendors/sweetalert/sweetalert.css" rel="stylesheet">
    <script src="../theme/public/vendors/sweetalert/sweetalert.min.js"></script>

    <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>

    <!-- Conexión API js -->
    <script src="js/crear_empresa.js?v1.0.0.0.0.0.0.0.0.1.14"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places" async defer></script>
    <!-- Alerts js -->
    <script src="js/alerts.js"></script>

    <div id="alert">

    </div>
    <div id="alertMapa">

    </div>

</body>

</html>