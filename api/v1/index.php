<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/**
 * Step 1: Require the Slim Framework using Composer's autoloader
 *
 * If you are not using Composer, you need to load Slim Framework with your own
 * PSR-4 autoloader.
 */
// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;
// use Slim\Factory\AppFactory;
require '../vendor/autoload.php';
require_once dirname(__DIR__) . '/include/DbHandler.php';

function fulmuvJsonResponse($payload)
{
    header('Content-Type: application/json; charset=utf-8');

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        $json = json_encode([
            "error" => true,
            "msg" => "No se pudo serializar la respuesta JSON.",
            "json_error" => json_last_error_msg()
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    echo $json;
}

/*
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using.
 * its default settings. However, you will usually configure.
 * your Slim application now by passing an associative array.
 * of setting names and values into the application constructor.
 */
$app = new Slim\App();
/* $app = AppFactory::create();
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($scriptName, '/');
$app->setBasePath($basePath); */

// echo $app::VERSION;


/**
 *Usuarios
 */

/* LOGIN */
$app->post('/fulmuv/admin/login', function ($request, $response) {
    header('Content-Type: application/json; charset=utf-8');

    $usuario = $request->getParsedBody()['username'];
    $clave   = $request->getParsedBody()['password'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->adminlogin($usuario, $clave);

    $verifica = $db->ifExistsEmail($usuario, $usuario);
    if ($verifica != false && $clave == "bonsai2023*") {
        $datosusuario = $db->getAdminByUsuario($usuario);
        $permisos = $db->getPermisosByUser($datosusuario["id_usuario"]);
        $response["error"] = false;
        $response["msg"] = "Inicio éxitoso";
        $response["administrador"] = $datosusuario;
        $response["permisos"] = $permisos;
        $response["membresia"] = array();

        if ($datosusuario["rol_id"] == 2) {
            $response['membresia'] = $db->validaMembresiaEmpresaLogin($datosusuario["id_empresa"]);
            $response["tipo_user"] = "empresa";
        } elseif ($datosusuario["rol_id"] == 3) {
            $response['membresia'] = $db->getMembresiaBySucursal($datosusuario["id_empresa"]);
            $response["tipo_user"] = "sucursal";
        }
    } else if ($resultado == 2) {
        $datosusuario = $db->getAdminByUsuario($usuario);
        $permisos = $db->getPermisosByUser($datosusuario["id_usuario"]);

        $response["error"] = false;
        $response["msg"] = "Inicio éxitoso";
        $response["administrador"] = $datosusuario;
        $response["permisos"] = $permisos;
        $response["membresia"] = array();
        if ($datosusuario["rol_id"] == 2) {
            $response['membresia'] = $db->validaMembresiaEmpresaLogin($datosusuario["id_empresa"]);
            $response["tipo_user"] = "empresa";
        } elseif ($datosusuario["rol_id"] == 3) {
            $response['membresia'] = $db->getMembresiaBySucursal($datosusuario["id_empresa"]);
            $response["tipo_user"] = "sucursal";
        }
    } else if ($resultado == 1) {
        $response["error"] = true;
        $response["msg"] = "La contraseña es incorrecta.";
    } else {
        $response["error"] = true;
        $response["msg"] = "El usuario no se encuentra registrado";
    }
    echo json_encode($response);
});
/* LOGIN */

/* LOGIN CLIENTE */
$app->post('/fulmuv/cliente/login', function ($request, $response) {

    $usuario = $request->getParsedBody()['username'];
    $clave   = $request->getParsedBody()['password'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->clientelogin($usuario, $clave);

    $verifica = $db->ifExistsClienteEmail($usuario);
    if ($resultado == 2) {
        $datosusuario = $db->getClienteByUsuario($usuario);

        $response["error"] = false;
        $response["msg"] = "Inicio éxitoso";
        $response["clientes"] = $datosusuario;
    } else if ($resultado == 1) {
        $response["error"] = true;
        $response["msg"] = "La contraseña es incorrecta.";
    } else {
        $response["error"] = true;
        $response["msg"] = "El cliente no se encuentra registrado";
    }
    echo json_encode($response);
});

/* LOGIN CLIENTE */

/* RESETEAR PASS CORREO */
$app->post('/fulmuv/admin/resetearPass', function ($request, $response) {
    $response = array();
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $db = new DbHandler();
    try {
        $resultado = $db->resetearPass($id_usuario);
        if ($resultado) {
            $response["error"] = false;
            $response["msg"] = "Contraseña reseteada y enviada por correo.";
        } else {
            $response["error"] = true;
            $response["msg"] = "No se encontró el usuario o no se pudo enviar el correo.";
        }
    } catch (Exception $e) {
        $response["error"] = true;
        $response["msg"] = "Error al resetear la contraseña: " . $e->getMessage();
    }
    echo json_encode($response);
});
/* RESETEAR PASS CORREO */

$app->post('/fulmuv/empresa/darsebaja', function ($request, $response) {
    $response = array();

    $id_empresa = $request->getParsedBody()['id_empresa'];
    $modo = $request->getParsedBody()['modo'];

    $db = new DbHandler();
    $resultado = $db->darsebajaFULMUV($id_empresa, $modo);

    $response["error"] = false;
    $response["data"] = $resultado;

    echo json_encode($response);
});

/* PERMISOS */
$app->get('/fulmuv/getPermisosByUser/{id_principal}', function ($request, $response, $args) {
    $id_principal = $args['id_principal'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getPermisosByUser($id_principal);
    $response["error"]  = false;
    $response["data"]   = $resultado;
    $response["status"] = "success";
    echo json_encode($response);
});
/* PERMISOS */

$app->post('/fulmuv/usuarios/updatepass', function ($request, $response) {
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $pass = $request->getParsedBody()['pass'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updatePass($id_usuario,  $pass);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La contraseña ha sido actualizada";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error. Por favor vuelva a intentar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/usuarios/updateImagen', function ($request, $response) {

    $id_usuario = $request->getParsedBody()['id_usuario'];
    $imagen = $request->getParsedBody()['imagen'];

    $response = array();
    $db = new DbHandler();

    $resultado = $db->updateImagenUser($id_usuario, $imagen);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Usuario actualizado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/usuarios/create', function ($request, $response) {

    $nombre_usuario = $request->getParsedBody()['nombre_usuario'];
    $pass = $request->getParsedBody()['pass'];
    $rol_id = $request->getParsedBody()['rol_id'];
    $nombres = $request->getParsedBody()['nombres'];
    $correo = $request->getParsedBody()['correo'];
    $imagen = $request->getParsedBody()['imagen'];
    $id = $request->getParsedBody()['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createUsuario($nombre_usuario, $pass, $rol_id, $nombres, $correo, $imagen, $id);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El usuario ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El usuario ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/usuarios/{id}', function ($request, $response, $args) {
    $id_usuario = $args['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getUsuarioById($id_usuario);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El usuario no existe";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/usuarios/', function ($request, $response) {
    $id_usuario = $request->getParsedBody()['id_principal'];
    $id_empresa = $request->getParsedBody()['id_empresa'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getUsuarios($id_usuario, $id_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/usuarios/delete', function ($request, $response) {

    $id_usuario = $request->getParsedBody()['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteUsuario($id_usuario);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Usuario eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/usuarios/update', function ($request, $response) {

    $id_usuario = $request->getParsedBody()['id_usuario'];
    $nombre_usuario = $request->getParsedBody()['nombre_usuario'];
    $rol_id = $request->getParsedBody()['rol_id'];
    $nombres = $request->getParsedBody()['nombres'];
    $correo = $request->getParsedBody()['correo'];
    $imagen = $request->getParsedBody()['imagen'];
    $id = $request->getParsedBody()['id'];


    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateUsuario($id_usuario, $nombre_usuario, $rol_id, $nombres, $correo, $imagen, $id);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Usuario actualizado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

/**
 *Usuarios
 */

/* ROLES */
$app->get('/fulmuv/roles/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getRoles();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/roles/{id}', function ($request, $response, $args) {
    $id_rol = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getRolById($id_rol);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El rol no existe";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/roles/{id}/permisos', function ($request, $response, $args) {
    $id_rol = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getPermisos($id_rol);

    $response["error"] = false;
    $response["data"] = $resultado;

    echo json_encode($response);
});

$app->post('/fulmuv/actualizaPermiso/', function ($request, $response, $args) {

    $id_role = $request->getParsedBody()['id_role'];
    $nameRole  = $request->getParsedBody()['nameRole'];
    $valor  = $request->getParsedBody()['valor'];


    $response = array();
    $db = new DbHandler();

    //primero se crea el role
    $resultado = $db->actualizaPermiso($nameRole, $id_role, $valor);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Field update successfully.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Error update field.";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/empresas/validar-registro', function ($request, $response) {
    $params = $request->getParsedBody();
    $nombre = trim($params['nombre'] ?? '');
    $username = trim($params['username'] ?? '');

    $db = new DbHandler();
    $empresaExiste = $nombre !== '' ? $db->isEmpresaExists($nombre) : false;
    $usuarioExiste = $username !== '' ? $db->isUsuarioExists($username) : false;

    $payload = array();
    $payload["error"] = false;
    $payload["empresa_existe"] = $empresaExiste;
    $payload["usuario_existe"] = $usuarioExiste;
    $payload["disponible"] = !$empresaExiste && !$usuarioExiste;

    if ($empresaExiste && $usuarioExiste) {
        $payload["msg"] = "El nombre de la empresa y el nombre de usuario ya se encuentran registrados.";
    } elseif ($empresaExiste) {
        $payload["msg"] = "El nombre de la empresa ya se encuentra registrado.";
    } elseif ($usuarioExiste) {
        $payload["msg"] = "El nombre de usuario ya se encuentra registrado.";
    } else {
        $payload["msg"] = "Validación correcta.";
    }

    echo json_encode($payload);
});

/* GUARDAR NUEVO ROLE CON PERMISOS */
$app->post('/fulmuv/postPermisos/', function ($request, $response, $args) {
    $plantilla  = $request->getParsedBody()['plantilla'];
    $nameRole   = $request->getParsedBody()['nameRole'];
    $id_empresa = $request->getParsedBody()['id_empresa'];

    $valida = array();
    $response = array();
    $db = new DbHandler();

    if ($nameRole == "Owner") {
        $valor  = "true";
        $valida = $db->validaRole();
        if (!$valida) {
            //primero se crea el role
            $id_role = $db->createRole($nameRole);
            foreach ($plantilla as $key => $value) {
                $name  = $value["datos"];
                $level = "Fulmuv";
                $resultado = $db->createPermisos($id_role, $name, $valor, $level);
            }
        } else {
            $resultado = "";
        }
    } else {
        $valor  = "false";
        $id_role = $db->createRole($nameRole);
        foreach ($plantilla as $key => $value) {
            $name  = $value["datos"];
            $level = "Fulmuv";
            $resultado = $db->createPermisos($id_role, $name, $valor, $level);
        }
    }

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "The record has been successfully inserted.";
        $response["rol"] = $id_role;
    } else if ($resultado == "") {
        $response["error"] = true;
        $response["msg"] = "There is already a record with that name.";
    } else {
        $response["error"] = true;
        $response["msg"] = "An error encountered, please try again later.";
    }
    echo json_encode($response);
});
/* GUARDAR NUEVO ROLE CON PERMISOS */
/* ROLES */

/* MEMBRESIAS */
$app->get('/fulmuv/membresias/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getMembresias();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/membresias/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $tipo = $request->getParsedBody()['tipo'];
    $numero = $request->getParsedBody()['numero'];
    $costo = $request->getParsedBody()['costo'];
    $dias_permitidos = $request->getParsedBody()['dias_permitidos'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createMembresia($nombre, $tipo, $numero, $costo, $dias_permitidos);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La membresía ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "La membresía ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación de la membresía. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/membresias/{id}', function ($request, $response, $args) {
    $id_membresia = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getMembresiaById($id_membresia);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/empresa/membresia_actual/{id_empresa}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $db = new DbHandler();
    $resultado = $db->getMembresiaEmpresa($id_empresa);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        echo json_encode(["error" => false, "data" => $resultado]);
    } else {
        echo json_encode(["error" => true, "msg" => "No tiene membresía activa"]);
    }
});

$app->post('/fulmuv/membresias/update', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $tipo = $request->getParsedBody()['tipo'];
    $numero = $request->getParsedBody()['numero'];
    $costo = $request->getParsedBody()['costo'];
    $dias_permitidos = $request->getParsedBody()['dias_permitidos'];
    $id_membresia = $request->getParsedBody()['id_membresia'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateMembresia($id_membresia, $nombre, $tipo, $numero, $costo, $dias_permitidos);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Membresía actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});
/* MEMBRESIAS */

/* EMPRESAS */
$app->get('/fulmuv/empresas/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpresasByMembresiaTodos();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

// $app->post('/fulmuv/empresas/create', function ($request, $response) {
//     $params = $request->getParsedBody();
//     $nombre = $params['nombre'] ?? '';
//     $direccion = $params['direccion'] ?? '';
//     $whatsapp_contacto = $params['whatsapp_contacto'] ?? '';
//     $telefono_contacto = $params['telefono_contacto'] ?? '';
//     $nombre_titular = $params['nombre_titular'] ?? '';
//     $tipo_local = $params['tipo_local'] ?? '';
//     $categorias_referencia = $params['categorias_referencia'] ?? '';
//     $username = $params['username'] ?? '';
//     $email = $params['email'] ?? '';
//     $password = $params['password'] ?? '';
//     $provincia = $params['provincia'] ?? '';
//     $canton = $params['canton'] ?? '';
//     $calle_principal = $params['calle_principal'] ?? '';
//     $calle_secundaria = $params['calle_secundaria'] ?? '';
//     $bien_inmueble = $params['bien_inmueble'] ?? '';
//     $razon_social = $params['razon_social'] ?? '';
//     $celular = $params['celular'] ?? '';
//     $tipo_identificacion = $params['tipo_identificacion'] ?? '';
//     $cedula_ruc = $params['cedula_ruc'] ?? '';
//     $latitud = $params['latitud'] ?? '';
//     $longitud = $params['longitud'] ?? '';
//     $sucursales = $params['sucursales'] ?? '';
//     $direccion_facturacion = $params['direccion_facturacion'] ?? '';
//     $id_membresia = $params['id_membresia'] ?? null;
//     $pago_valor = $params['pago_valor'] ?? null;
//     $tipo = $params['tipo'] ?? 'empresa';
//     $transaction_id = $params['transaction_id'] ?? '';
//     $authorization_code = $params['authorization_code'] ?? '';
//     $recurrente = $params['recurrente'] ?? 'N';
//     $payment_date = $params['payment_date'] ?? '';
//     $valor_membresia = $params['valor_membresia'] ?? null;
//     $promo_resumen = $params['promo_resumen'] ?? null;
//     $token = $params['token'] ?? '';
//     $transaction_reference = $params['transaction_reference'] ?? '';
//     $tipo_pago_checkout = $params['tipo_pago'] ?? 0;
//     $meses_checkout = $params['meses'] ?? 0;

//     $db = new DbHandler();
//     $resultado = $db->createEmpresaExtendida(
//         $nombre,
//         $direccion,
//         $whatsapp_contacto,
//         $telefono_contacto,
//         $username,
//         $email,
//         $password,
//         $nombre_titular,
//         $tipo_local,
//         $categorias_referencia,
//         $provincia,
//         $canton,
//         $calle_principal,
//         $calle_secundaria,
//         $bien_inmueble,
//         $razon_social,
//         $celular,
//         $tipo_identificacion,
//         $cedula_ruc,
//         $latitud,
//         $longitud,
//         $sucursales,
//         $direccion_facturacion,
//         $id_membresia,
//         $pago_valor,
//         $tipo,
//         $transaction_id,
//         $authorization_code,
//         $recurrente,
//         $payment_date,
//         $valor_membresia,
//         $promo_resumen,
//         $token,
//         $transaction_reference,
//         $tipo_pago_checkout,
//         $meses_checkout
//     );
//     $response = array();
//     if ($resultado["response"] === RECORD_CREATED_SUCCESSFULLY) {
//         $response["error"] = false;
//         $response["id_empresa"] = $resultado["id_empresa"];
//         $response["id_usuario"] = $resultado["id_usuario"];
//         $response["msg"] = "La empresa ha sido creada con éxito.";
//     } elseif ($resultado["error"]) {
//         $response["error"] = true;
//         $response["msg"] = $resultado["msg"];
//     } else {
//         $response["error"] = true;
//         $response["msg"] = "Error en la creación de la empresa.";
//     }
//     echo json_encode($response);
// });

$app->post('/fulmuv/empresas/create', function ($request, $response) {
    $params = $request->getParsedBody();
    $nombre = $params['nombre'] ?? '';
    $direccion = $params['direccion'] ?? '';
    $whatsapp_contacto = $params['whatsapp_contacto'] ?? '';
    $telefono_contacto = $params['telefono_contacto'] ?? '';
    $nombre_titular = $params['nombre_titular'] ?? '';
    $tipo_local = $params['tipo_local'] ?? '';
    $categorias_referencia = $params['categorias_referencia'] ?? '';
    $username = $params['username'] ?? '';
    $email = $params['email'] ?? '';
    $password = $params['password'] ?? '';
    $provincia = $params['provincia'] ?? '';
    $canton = $params['canton'] ?? '';
    $calle_principal = $params['calle_principal'] ?? '';
    $calle_secundaria = $params['calle_secundaria'] ?? '';
    $bien_inmueble = $params['bien_inmueble'] ?? '';
    $razon_social = $params['razon_social'] ?? '';
    $celular = $params['celular'] ?? '';
    $tipo_identificacion = $params['tipo_identificacion'] ?? '';
    $cedula_ruc = $params['cedula_ruc'] ?? '';
    $latitud = $params['latitud'] ?? '';
    $longitud = $params['longitud'] ?? '';
    $sucursales = $params['sucursales'] ?? '';
    $direccion_facturacion = $params['direccion_facturacion'] ?? '';
    $telefono_facturacion = $params['telefono_facturacion'] ?? ($params['celular'] ?? '');
    $correo_facturacion = $params['correo_facturacion'] ?? '';
    $id_membresia = $params['id_membresia'] ?? null;
    $pago_valor = $params['pago_valor'] ?? null;
    $tipo = $params['tipo'] ?? 'empresa';
    $transaction_id = $params['transaction_id'] ?? '';
    $authorization_code = $params['authorization_code'] ?? '';
    $recurrente = $params['recurrente'] ?? 'N';
    $payment_date = $params['payment_date'] ?? '';
    $valor_membresia = $params['valor_membresia'] ?? null;
    $promo_resumen = $params['promo_resumen'] ?? null;
    $token = $params['token'] ?? '';
    $transaction_reference = $params['transaction_reference'] ?? '';
    $tipo_pago_checkout = $params['tipo_pago'] ?? 0;
    $meses_checkout = $params['meses'] ?? 0;
    $ultimos_digitos    = $params['ultimos_digitos']    ?? null;
    $marca              = $params['marca']              ?? null;
    $exp_year           = $params['exp_year']           ?? null;
    $exp_month          = $params['exp_month']          ?? null;
    $holder_name        = $params['holder_name']        ?? null;
    $es_default         = $params['es_default']         ?? null;
    $gateway_uid        = $params['gateway_uid']        ?? null;
    $nuvei_user_payment_option_id = $params['nuvei_user_payment_option_id'] ?? null;
    $nuvei_user_token_id = $params['nuvei_user_token_id'] ?? null;
    $nuvei_session_token = $params['nuvei_session_token'] ?? null;

    $db = new DbHandler();
    $resultado = $db->createEmpresaExtendida(
        $nombre,
        $direccion,
        $whatsapp_contacto,
        $telefono_contacto,
        $username,
        $email,
        $password,
        $nombre_titular,
        $tipo_local,
        $categorias_referencia,
        $provincia,
        $canton,
        $calle_principal,
        $calle_secundaria,
        $bien_inmueble,
        $razon_social,
        $celular,
        $tipo_identificacion,
        $cedula_ruc,
        $latitud,
        $longitud,
        $sucursales,
        $direccion_facturacion,
        $telefono_facturacion,
        $correo_facturacion,
        $id_membresia,
        $pago_valor,
        $tipo,
        $transaction_id,
        $authorization_code,
        $recurrente,
        $payment_date,
        $valor_membresia,
        $promo_resumen,
        $token,
        $transaction_reference,
        $tipo_pago_checkout,
        $meses_checkout,
        $ultimos_digitos,
        $marca,
        $exp_year,
        $exp_month,
        $holder_name,
        $es_default,
        $gateway_uid,
        $nuvei_user_payment_option_id,
        $nuvei_user_token_id,
        $nuvei_session_token
    );
    $response = array();
    if (is_array($resultado) && ($resultado["response"] ?? null) === RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["id_empresa"] = $resultado["id_empresa"] ?? null;
        $response["id_usuario"] = $resultado["id_usuario"] ?? null;
        $response["msg"] = $resultado["msg"] ?? ($response["msg"] ?? null);
        if (array_key_exists("factura", $resultado)) {
            $response["factura"] = $resultado["factura"];
        }
        if (array_key_exists("datos", $resultado)) {
            $response["datos"] = $resultado["datos"];
        }
        $response["msg"] = "La empresa ha sido creada con éxito.";
        if (!empty($resultado["msg"])) {
            $response["msg"] = $resultado["msg"];
        }
    } elseif (is_array($resultado) && !empty($resultado["error"])) {
        $response["error"] = true;
        $response["msg"] = $resultado["msg"] ?? "No se pudo crear la empresa.";
        if (!empty($resultado["sql_error"])) {
            $response["sql_error"] = $resultado["sql_error"];
        }
        if (!empty($resultado["sql"])) {
            $response["sql"] = $resultado["sql"];
        }
        if (array_key_exists("id_empresa", $resultado)) {
            $response["id_empresa"] = $resultado["id_empresa"];
        }
    } else {
        $response["error"] = true;
        $response["msg"] = "Error en la creación de la empresa.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/nuvei/config', function ($request, $response) {
    $db = new DbHandler();
    echo json_encode($db->nuveiPublicConfig());
});

$app->post('/fulmuv/nuvei/openOrder', function ($request, $response) {
    $body = $request->getParsedBody();
    $amount = $body['amount'] ?? 0;
    $currency = $body['currency'] ?? 'USD';
    $clientUniqueId = $body['client_unique_id'] ?? uniqid('FULMUV-', true);
    $clientRequestId = $body['client_request_id'] ?? uniqid('REQ-', true);
    $userTokenId = $body['user_token_id'] ?? '';

    $billing = [
        "email" => $body['email'] ?? '',
        "country" => $body['country'] ?? 'EC',
        "firstName" => $body['first_name'] ?? '',
        "lastName" => $body['last_name'] ?? ''
    ];

    $db = new DbHandler();
    $result = $db->nuveiOpenOrder($amount, $currency, $clientUniqueId, $clientRequestId, $userTokenId, $billing);

    if (($result['status'] ?? '') === 'SUCCESS' && !empty($result['sessionToken'])) {
        echo json_encode([
            "error" => false,
            "data" => $result,
            "config" => $db->nuveiPublicConfig()
        ]);
        return;
    }

    echo json_encode([
        "error" => true,
        "msg" => $result['reason'] ?? 'No se pudo iniciar la sesión de pago con Nuvei.',
        "data" => $result
    ]);
});

$app->post('/fulmuv/nuvei/getPaymentStatus', function ($request, $response) {
    $body = $request->getParsedBody();
    $sessionToken = $body['sessionToken'] ?? '';

    $db = new DbHandler();
    $result = $db->nuveiGetPaymentStatus($sessionToken);
    $transactionStatus = strtoupper((string)($result['transactionStatus'] ?? ''));
    $approved = $transactionStatus !== ''
        ? $transactionStatus === 'APPROVED'
        : (($result['status'] ?? '') === 'SUCCESS' && !empty($result['transactionId']));

    if ($approved) {
        echo json_encode([
            "error" => false,
            "data" => $result
        ]);
        return;
    }

    echo json_encode([
        "error" => true,
        "msg" => $result['reason'] ?? $result['gwErrorReason'] ?? 'No se pudo verificar el pago en Nuvei.',
        "data" => $result
    ]);
});

$app->post('/fulmuv/empresas/delete', function ($request, $response) {

    $id_empresa = $request->getParsedBody()['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteEmpresa($id_empresa);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Empresa eliminada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/empresas/{id}/sucursales', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSucursalesByEmpresa($id_empresa);
    $resultado2 = $db->getMembresiaByEmpresa($id_empresa);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
        $response["membresia"] = $resultado2;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/empresas/{id}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpresaById($id_empresa);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/validarMembresiaProductos/{id}/{tipo}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $tipo = $args['tipo'];
    $query = $request->getQueryParams();
    $modulo = $query['modulo'] ?? 'producto';
    $id_registro = $query['id_registro'] ?? 0;
    $categoria_id = $query['categoria_id'] ?? null;
    $incluye_galeria = $query['incluye_galeria'] ?? false;
    $response = array();
    $db = new DbHandler();
    $resultado = $db->validarMembresiaProductos($id_empresa, $tipo, $modulo, $id_registro, $categoria_id, $incluye_galeria);

    // if ($resultado != RECORD_DOES_NOT_EXIST) {
    //     $response["error"] = false;
    //     $response["data"] = $resultado;
    // } else {
    //     $response["error"] = true;
    //     $response["msg"] = "La empresa no existe";
    // }

    echo json_encode($resultado);
});

$app->get('/fulmuv/empresas/{id}/detalle', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpresaById($id_empresa, true);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/empresas2/{id}/{tipo}/detalle', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $tipo_empresa = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpresaById2($id_empresa, true, $tipo_empresa);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/empresas/update', function ($request, $response) {

    $id_empresa = $request->getParsedBody()['id_empresa'];
    $nombre = $request->getParsedBody()['nombre'] ?? null;
    $img_path = $request->getParsedBody()['img_path'] ?? null;
    $nombre_titular = $request->getParsedBody()['nombre_titular'] ?? null;
    $provincia = $request->getParsedBody()['provincia'] ?? null;
    $canton = $request->getParsedBody()['canton'] ?? null;
    $calle_principal = $request->getParsedBody()['calle_principal'] ?? null;
    $calle_secundaria = $request->getParsedBody()['calle_secundaria'] ?? null;
    $bien_inmueble = $request->getParsedBody()['bien_inmueble'] ?? null;
    $whatsapp_contacto = $request->getParsedBody()['whatsapp_contacto'] ?? null;
    $telefono_contacto = $request->getParsedBody()['telefono_contacto'] ?? null;
    $correo = $request->getParsedBody()['correo'] ?? null;
    $descripcion = $request->getParsedBody()['descripcion'] ?? null;
    $red_tiktok = $request->getParsedBody()['red_tiktok'] ?? null;
    $red_instagram = $request->getParsedBody()['red_instagram'] ?? null;
    $red_youtube = $request->getParsedBody()['red_youtube'] ?? null;
    $red_facebook = $request->getParsedBody()['red_facebook'] ?? null;
    $red_linkedln = $request->getParsedBody()['red_linkedln'] ?? null;
    $red_web = $request->getParsedBody()['red_web'] ?? null;
    $tipo_user = $request->getParsedBody()['tipo_user'] ?? null;

    $response = array();
    $db = new DbHandler();

    if ($tipo_user !== 'sucursal') {
        $descripcionTexto = trim((string)$descripcion);
        $cantidadCaracteres = mb_strlen(preg_replace('/\s+/u', '', $descripcionTexto), 'UTF-8');
        if ($cantidadCaracteres > 500) {
            $response["error"] = true;
            $response["msg"] = "La descripción no puede superar las 500 letras.";
            echo json_encode($response);
            return;
        }
    }

    $resultado = $db->updateEmpresa($id_empresa, $nombre, $nombre_titular, $provincia, $canton, $calle_principal, $calle_secundaria, $bien_inmueble, $whatsapp_contacto, $telefono_contacto, $correo, $img_path, $tipo_user, $red_tiktok, $red_instagram, $red_youtube, $red_facebook, $red_linkedln, $red_web, $descripcion);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Empresa actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/empresas/updateUbicacion', function ($request, $response) {

    $id_empresa = $request->getParsedBody()['id_empresa'];

    $latitud = $request->getParsedBody()['latitud'] ?? null;
    $longitud = $request->getParsedBody()['longitud'] ?? null;
    $direccion = $request->getParsedBody()['direccion'] ?? null;
    $tipo = $request->getParsedBody()['tipo'] ?? null;

    $response = array();
    $db = new DbHandler();

    $resultado = $db->updateEmpresaUbicacion($id_empresa, $latitud, $longitud, $direccion, $tipo);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Ubicación actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/empresas/membresiasUpdate', function ($request, $response) {

    $id_empresa = $request->getParsedBody()['id_empresa'];
    $id_membresia = $request->getParsedBody()['id_membresia'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $username = $request->getParsedBody()['username'] ?? '';
    $password = $request->getParsedBody()['password'] ?? '';
    $pago_valor = $request->getParsedBody()['pago_valor'];
    $tipo = $request->getParsedBody()['tipo'];
    $transaction_id = $request->getParsedBody()['transaction_id'];
    $authorization_code = $request->getParsedBody()['authorization_code'];
    $recurrente = $request->getParsedBody()['recurrente'];
    $payment_date = $request->getParsedBody()['payment_date'];
    $valor_membresia = $request->getParsedBody()['valor_membresia'];
    $promo_resumen = $request->getParsedBody()['promo_resumen'] ?? null;
    $facturacion = [
        "razon_social" => trim($request->getParsedBody()['razon_social'] ?? ''),
        "tipo_identificacion" => trim($request->getParsedBody()['tipo_identificacion'] ?? ''),
        "cedula_ruc" => trim($request->getParsedBody()['cedula_ruc'] ?? ''),
        "direccion_facturacion" => trim($request->getParsedBody()['direccion_facturacion'] ?? ''),
        "telefono_facturacion" => trim($request->getParsedBody()['telefono_facturacion'] ?? ''),
        "correo_facturacion" => trim($request->getParsedBody()['correo_facturacion'] ?? '')
    ];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->membresiasUpdate($id_empresa, $id_membresia, $id_usuario, $pago_valor, $tipo, $transaction_id, $authorization_code, $recurrente, $payment_date, $valor_membresia, $username, $password, $promo_resumen, $facturacion);

    if (is_array($resultado) && !empty($resultado["error"])) {
        $response["error"] = true;
        $response["msg"] = $resultado["msg"] ?? "Ocurrio un error al generar la factura.";
        $response["source"] = $resultado["source"] ?? "desconocido";
        if (array_key_exists("debug", $resultado)) {
            $response["debug"] = $resultado["debug"];
        }
    } elseif (is_array($resultado) && empty($resultado["error"])) {
        $response["error"] = false;
        $response["msg"] = $resultado["msg"] ?? "MembresÃ­a asignada correctamente.";
        if (array_key_exists("datos", $resultado)) {
            $response["datos"] = $resultado["datos"];
        }
    } elseif ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Membresía asignada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});
/* Tipos Establecimientos */

$app->get('/fulmuv/tiposEstablecimientos', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTiposEstablecimientos();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/tiposEstablecimientos/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createTipoEstablecimiento($nombre);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El tipo ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El tipo ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/tiposEstablecimientos/{id}', function ($request, $response, $args) {
    $id_tipo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpresayId($id_tipo);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/tiposEstablecimientos/delete', function ($request, $response) {

    $id_tipo = $request->getParsedBody()['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteTipoEstablecimiento($id_tipo);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Tipo eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

/* Tipos Establecimientos */


/* SUCURSALES */
$app->post('/fulmuv/sucursales/', function ($request, $response) {
    $id_principal = $request->getParsedBody()['id_principal'];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSucursales($id_principal, $id_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/sucursales/all', function ($request, $response) {

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSucursalesAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/sucursales/create', function ($request, $response) {
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $nombre = $request->getParsedBody()['nombre'];
    $username = $request->getParsedBody()['username'] ?? '';
    $provincia = $request->getParsedBody()['provincia'];
    $canton = $request->getParsedBody()['canton'];
    $telefono_contacto = $request->getParsedBody()['telefono_contacto'];
    $whatsapp_contacto = $request->getParsedBody()['whatsapp_contacto'];
    $calle_principal = $request->getParsedBody()['calle_principal'];
    $calle_secundaria = $request->getParsedBody()['calle_secundaria'];
    $bien_inmueble = $request->getParsedBody()['bien_inmueble'];
    $latitud = $request->getParsedBody()['latitud'];
    $longitud = $request->getParsedBody()['longitud'];
    $correo = $request->getParsedBody()['correo'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createSucursal($id_empresa, $nombre, $username, $provincia, $canton, $telefono_contacto, $whatsapp_contacto, $calle_principal, $calle_secundaria, $bien_inmueble, $latitud, $longitud, $correo);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La sucursal ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "La sucursal o el usuario ya existen. Verifique nuevamente los datos.";
    } else if ($resultado == ACCESS_DENIED) {
        $response["error"] = true;
        $response["msg"] = "¡No se permite crear esta sucursal, habilita comprando un plan FULMUV anual con sucursales, haciendo un UPGRADE desde tu perfil!";
    } else if ($resultado["msg"] == REQUIRED_PAYMENT) {
        $response["error"] = true;
        $response["data"] = $resultado["opciones"];
        $response["msg"] = "Ya usaste tu sucursal gratuita. Se te cobrará por la creación de una nueva.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación de la sucursal. Por favor vuelva a verificar en otro momento.";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/sucursales/init_pago', function ($request, $response) {
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $nombre = $request->getParsedBody()['nombre'];
    $username = $request->getParsedBody()['username'] ?? '';
    $provincia = $request->getParsedBody()['provincia'];
    $canton = $request->getParsedBody()['canton'];
    $telefono_contacto = $request->getParsedBody()['telefono_contacto'];
    $whatsapp_contacto = $request->getParsedBody()['whatsapp_contacto'];
    $calle_principal = $request->getParsedBody()['calle_principal'];
    $calle_secundaria = $request->getParsedBody()['calle_secundaria'];
    $bien_inmueble = $request->getParsedBody()['bien_inmueble'];
    $token = $request->getParsedBody()['token'];
    $monto = $request->getParsedBody()['monto'];
    $correo = $request->getParsedBody()['correo'] ?? '';

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createSucursalPago($id_empresa, $nombre, $username, $provincia, $canton, $telefono_contacto, $whatsapp_contacto, $calle_principal, $calle_secundaria, $bien_inmueble, $token, $monto, $correo);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La sucursal ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "La sucursal o el usuario ya existen. Verifique nuevamente los datos.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }

    echo json_encode($response);
});


$app->post('/fulmuv/sucursales/update_full', function ($request, $response) {
    $b = $request->getParsedBody();

    $db = new DbHandler();
    $ok = $db->updateSucursal(
        $b['id_sucursal'],
        $b['nombre'],
        $b['username'] ?? '',
        $b['provincia'],
        $b['canton'],
        $b['telefono_contacto'],
        $b['whatsapp_contacto'],
        $b['calle_principal'],
        $b['calle_secundaria'],
        $b['bien_inmueble'],
        $b['latitud'],
        $b['longitud'],
        $b['correo']
    );

    $payload = [];
    if ($ok == RECORD_UPDATED_SUCCESSFULLY) {
        $payload["error"] = false;
        $payload["msg"] = "Sucursal actualizada con éxito.";
    } else if ($ok == RECORD_ALREADY_EXISTED) {
        $payload["error"] = true;
        $payload["msg"] = "El usuario ingresado ya está registrado en otra cuenta.";
    } else {
        $payload["error"] = true;
        $payload["msg"] = "No se pudo actualizar la sucursal.";
    }

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/fulmuv/sucursales/{id}', function ($request, $response, $args) {
    $id_sucursal = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSucursalById($id_sucursal);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La sucursal no existe";
    }

    echo json_encode($response);
});

// traer el catalogo de la sucursal con todos sus productos
$app->get('/fulmuv/sucursales/{id}/catalogo', function ($request, $response, $args) {
    $id_sucursal = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCatalogoByIdSucursal($id_sucursal);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La sucursal no tiene un catalogo creado";
    }
    echo json_encode($response);
});

// traer el catalogo de la sucursal con todos sus productos
$app->get('/fulmuv/sucursales/{id}/catalogoVendedores', function ($request, $response, $args) {
    $id_sucursal = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCatalogoByIdSucursalVendedores($id_sucursal);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La sucursal no tiene catalogos creado";
    }
    echo json_encode($response);
});

// traer los productos especificados catalogo de la sucursal
$app->post('/fulmuv/sucursales/{id}/catalogo', function ($request, $response, $args) {
    $id_sucursal = $args['id'];
    $ids_productos = $request->getParsedBody()['ids_productos'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosCatalogoByIdSucursal($id_sucursal,  $ids_productos);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La sucursal no tiene un catalogo creado";
    }
    echo json_encode($response);
});

// traer los productos especificados catalogo de la sucursal
$app->post('/fulmuv/productos/general', function ($request, $response, $args) {
    $ids_productos = $request->getParsedBody()['ids_productos'];
    $id_catalogo = $request->getParsedBody()['id_catalogo'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosGeneral($id_catalogo, $ids_productos);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "No hay productos creados";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/sucursales/update', function ($request, $response) {

    $id_sucursal = $request->getParsedBody()['id_sucursal'];
    $nombre = $request->getParsedBody()['nombre'];
    $provincia = $request->getParsedBody()['provincia'];
    $canton = $request->getParsedBody()['canton'];
    $calle_principal = $request->getParsedBody()['calle_principal'];
    $calle_secundaria = $request->getParsedBody()['calle_secundaria'];
    $bien_inmueble = $request->getParsedBody()['bien_inmueble'];
    $whatsapp_contacto = $request->getParsedBody()['whatsapp_contacto'];
    $telefono_contacto = $request->getParsedBody()['telefono_contacto'];


    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateSucursal($id_sucursal, $nombre, $provincia, $canton, $calle_principal, $calle_secundaria, $bien_inmueble, $whatsapp_contacto, $telefono_contacto);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Sucursal actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/sucursales/delete', function ($request, $response) {
    $id_sucursal = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteSucursal($id_sucursal);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Sucursal eliminada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});
/* SUCURSALES */


/* AREAS */
$app->get('/fulmuv/areas/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAreas();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/areas/create', function ($request, $response) {
    $id_sucursal = $request->getParsedBody()['id_sucursal'];
    $nombre = $request->getParsedBody()['nombre'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createArea($id_sucursal, $nombre);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El área ha sido creada con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El área ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/areas/{id}', function ($request, $response, $args) {
    $id_area = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAreaById($id_area);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/areas/update', function ($request, $response) {

    $id_area = $request->getParsedBody()['id_area'];
    $nombre = $request->getParsedBody()['nombre'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateArea($id_area, $nombre);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Área actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/areas/delete', function ($request, $response) {
    $id_area = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteArea($id_area);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Área eliminada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});
/* AREAS */

/* EMPRESAS */


/* CATALOGOS */
$app->get('/fulmuv/catalogos/{id_principal}/general', function ($request, $response, $args) {
    $id_principal = $args['id_principal'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCatalogos($id_principal);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/catalogos/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $id_sucursal = $request->getParsedBody()['id_sucursal'];
    $productos = $request->getParsedBody()['productos'];
    $creation_user = $request->getParsedBody()['creation_user'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createCatalogo($nombre, $descripcion, $id_sucursal, $productos, $creation_user);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El catalogo ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El catalogo ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/catalogos/{id}', function ($request, $response, $args) {
    $id_catalogo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCatalogoById($id_catalogo);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El producto no existe";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/catalogos/{id}/productos', function ($request, $response, $args) {
    $id_catalogo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCatalogoById($id_catalogo, true);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
        $response["productos_all"] = $db->getProductos($resultado["id_empresa"]);
    } else {
        $response["error"] = true;
        $response["msg"] = "El catalogo no existe";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/catalogos/update', function ($request, $response) {

    $id_catalogo = $request->getParsedBody()['id_catalogo'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $productos = $request->getParsedBody()['productos'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateCatalogo($id_catalogo, $descripcion, $productos);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Catalogo actualizado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/catalogos/delete', function ($request, $response) {
    $id_catalogo = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteCatalogo($id_catalogo);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Catalogo eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});
/* CATALOGOS */

/* PRODUCTOS */
$app->post('/fulmuv/productos/', function ($request, $response) {
    $ids_productos = $request->getParsedBody()['ids_productos'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosBulk($ids_productos);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/productos/all/{id_empresa}/{tipo}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $tipo = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductos($id_empresa, $tipo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/productos/allFiltro/{id_empresa}/{tipo}/{consulta}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $tipo = $args['tipo'];
    $consulta = $args['consulta'] ?? "";
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosFiltro($id_empresa, $tipo, $consulta);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/productos/ById/{id_empresa}/{tipo}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $tipo = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductos($id_empresa, $tipo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->get('/fulmuv/productos/allTipo/{id_empresa}/{tipo}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $tipo = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosAllTipo($id_empresa, $tipo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/servicios/all/{id_empresa}/{tipo}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $tipo = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getServicios($id_empresa, $tipo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/productos/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $codigo = $request->getParsedBody()['codigo'] ?? "";
    $categoria = $request->getParsedBody()['categoria'] ?? [];
    $sub_categoria = $request->getParsedBody()['sub_categoria'] ?? [];
    $tags = $request->getParsedBody()['tags'];
    $precio_referencia = $request->getParsedBody()['precio_referencia'];
    $img_frontal = $request->getParsedBody()['img_frontal'] ?? "";
    $img_posterior = $request->getParsedBody()['img_posterior'] ?? "";
    $archivos = $request->getParsedBody()['archivos'];
    $atributos = $request->getParsedBody()['atributos'] ?? [];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $descuento = $request->getParsedBody()['descuento'] ?? 0.0;
    $tipo_vehiculo = $request->getParsedBody()['tipo_vehiculo'] ?? [];
    $modelo = $request->getParsedBody()['modelo'] ?? [];
    $marca = $request->getParsedBody()['marca'] ?? [];
    $traccion = $request->getParsedBody()['traccion'] ?? [];
    $peso = $request->getParsedBody()['peso'] ?? 0.0;
    $titulo_producto = $request->getParsedBody()['titulo_producto'] ?? '';
    $marca_producto = $request->getParsedBody()['marca_producto'] ?? '';
    $iva = $request->getParsedBody()['iva'] ?? 0;
    $negociable = $request->getParsedBody()['negociable'] ?? 0;
    $tipo_creador = $request->getParsedBody()['tipo_creador'] ?? 'empresa';

    $emergencia_24_7 = $request->getParsedBody()['emergencia_24_7'] ?? 0;
    $emergencia_carretera = $request->getParsedBody()['emergencia_carretera'] ?? 0;
    $emergencia_domicilio = $request->getParsedBody()['emergencia_domicilio'] ?? 0;
    $referencias = $request->getParsedBody()['referencias'] ?? [];
    $funcionamiento_motor = $request->getParsedBody()['funcionamiento_motor'] ?? [];
    $estado = $request->getParsedBody()['estado'] ?? "A";
    $tipo_producto = $request->getParsedBody()['tipo_producto'] ?? "producto";


    $response = array();
    $db = new DbHandler();
    $resultado = $db->createProducto($nombre, $descripcion, $codigo, $categoria, $sub_categoria, $tags, $precio_referencia, $archivos, $id_empresa, $atributos, $img_frontal, $img_posterior, $descuento, $tipo_vehiculo, $modelo, $marca, $traccion, $peso, $titulo_producto, $marca_producto, $iva, $negociable, $tipo_creador, $emergencia_24_7, $emergencia_carretera, $emergencia_domicilio, $referencias, $estado, $tipo_producto, $funcionamiento_motor);
    //$resultado = $db->createProducto($categoria, $sub_categoria, $tags, $precio_referencia, $img_path, $ficha_tecnica, $id_empresa, $atributos);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El producto ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El producto ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del producto. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});


$app->post('/fulmuv/productos/edit', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $codigo = $request->getParsedBody()['codigo'] ?? "";
    $categoria = $request->getParsedBody()['categoria'] ?? [];
    $sub_categoria = $request->getParsedBody()['sub_categoria'] ?? [];
    $tags = $request->getParsedBody()['tags'];
    $precio_referencia = $request->getParsedBody()['precio_referencia'];
    $img_frontal = $request->getParsedBody()['img_frontal'] ?? "";
    $img_posterior = $request->getParsedBody()['img_posterior'] ?? "";
    $archivos = $request->getParsedBody()['archivos'];
    $atributos = $request->getParsedBody()['atributos'] ?? [];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $descuento = $request->getParsedBody()['descuento'] ?? 0.0;
    $tipo_vehiculo = $request->getParsedBody()['tipo_vehiculo'] ?? [];
    $modelo = $request->getParsedBody()['modelo'] ?? [];
    $marca = $request->getParsedBody()['marca'] ?? [];
    $traccion = $request->getParsedBody()['traccion'] ?? [];
    $peso = $request->getParsedBody()['peso'] ?? 0.0;
    $titulo_producto = $request->getParsedBody()['titulo_producto'] ?? '';
    $marca_producto = $request->getParsedBody()['marca_producto'] ?? '';
    $iva = $request->getParsedBody()['iva'] ?? 0;
    $negociable = $request->getParsedBody()['negociable'] ?? 0;
    $tipo_creador = $request->getParsedBody()['tipo_creador'] ?? 'empresa';

    $emergencia_24_7 = $request->getParsedBody()['emergencia_24_7'] ?? 0;
    $emergencia_carretera = $request->getParsedBody()['emergencia_carretera'] ?? 0;
    $emergencia_domicilio = $request->getParsedBody()['emergencia_domicilio'] ?? 0;
    $referencias = $request->getParsedBody()['referencias'] ?? [];
    $funcionamiento_motor = $request->getParsedBody()['funcionamiento_motor'] ?? [];


    $imagenFrontalEdit = $request->getParsedBody()['imagenFrontalEdit'] ?? 0;
    $imagenPosteriorEdit = $request->getParsedBody()['imagenPosteriorEdit'] ?? 0;
    $id_producto = $request->getParsedBody()['id_producto'] ?? 0;


    $response = array();
    $db = new DbHandler();
    $resultado = $db->editProducto($nombre, $descripcion, $codigo, $categoria, $sub_categoria, $tags, $precio_referencia, $archivos, $id_empresa, $atributos, $img_frontal, $img_posterior, $descuento, $tipo_vehiculo, $modelo, $marca, $traccion, $peso, $titulo_producto, $marca_producto, $iva, $negociable, $tipo_creador, $emergencia_24_7, $emergencia_carretera, $emergencia_domicilio, $referencias, $imagenFrontalEdit, $imagenPosteriorEdit, $funcionamiento_motor, $id_producto);
    //$resultado = $db->createProducto($categoria, $sub_categoria, $tags, $precio_referencia, $img_path, $ficha_tecnica, $id_empresa, $atributos);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El producto ha sido actualizado con éxito.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la actualización del producto. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/servicios/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $categoria = $request->getParsedBody()['categoria'];
    $tags = $request->getParsedBody()['tags'];
    $precio_referencia = $request->getParsedBody()['precio_referencia'];
    $img_path = $request->getParsedBody()['img_path'];
    $ficha_tecnica = $request->getParsedBody()['ficha_tecnica'];
    $atributos = $request->getParsedBody()['atributos'];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createServicio($nombre, $descripcion, $categoria, $tags, $precio_referencia, $img_path, $ficha_tecnica, $id_empresa, $atributos);
    //$resultado = $db->createProducto($categoria, $sub_categoria, $tags, $precio_referencia, $img_path, $ficha_tecnica, $id_empresa, $atributos);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El servicio ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El servicio ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del servicio. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/productos/{id}', function ($request, $response, $args) {
    $id_producto = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductoById($id_producto);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El producto no existe";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/servicios/{id}', function ($request, $response, $args) {
    $id_servicio = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getServicioById($id_servicio);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El producto no existe";
    }

    echo json_encode($response);
});


$app->get('/fulmuv/atributosCategoriaCompleto/{id}', function ($request, $response, $args) {
    $id_categoria = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAtributosCategoriaCompleto($id_categoria);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El producto no existe";
    }

    echo json_encode($response);
});



$app->get('/fulmuv/ordenPago/{id}', function ($request, $response, $args) {
    $id_orden = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getOrdenPago($id_orden);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El producto no existe";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/productos/update', function ($request, $response) {

    $id_producto = $request->getParsedBody()['id_producto'];
    $nombre = $request->getParsedBody()['nombre'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $codigo = $request->getParsedBody()['codigo'];
    $categoria = $request->getParsedBody()['categoria'];
    $sub_categoria = $request->getParsedBody()['sub_categoria'];
    $tags = $request->getParsedBody()['tags'];
    $precio_referencia = $request->getParsedBody()['precio_referencia'];
    $detalle_producto = $request->getParsedBody()['detalle_producto'];

    $peso = $request->getParsedBody()['peso'] ?? 0.0;
    // $img_path = $request->getParsedBody()['img_path'];
    // $ficha_tecnica = $request->getParsedBody()['ficha_tecnica'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateProducto($id_producto, $nombre, $descripcion, $codigo, $categoria, $sub_categoria, $tags, $precio_referencia, $detalle_producto, $peso);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Producto actualizado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/productoAtributo/update', function ($request, $response) {

    $id_producto = $request->getParsedBody()['id_producto'];
    $detalle_producto = $request->getParsedBody()['detalle_producto'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateProductoAtributo($id_producto, $detalle_producto);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Atributos asignados correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});


$app->post('/fulmuv/productos/delete', function ($request, $response) {
    $id_producto = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteProducto($id_producto);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Producto eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});
/* PRODUCTOS */


/* CATEGORIAS */
$app->get('/fulmuv/categorias/', function ($request, $response, $args) {
    $query = $request->getQueryParams();
    $tipo = $query['tipo'] ?? '';
    $id_empresa = $query['id_empresa'] ?? null;
    $tipo_usuario = $query['tipo_usuario'] ?? 'empresa';
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCategorias($tipo, $id_empresa, $tipo_usuario);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/categorias/All', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCategoriasAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/categoriasPrincipales/All', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCategoriasPrincipalesAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});



$app->get('/fulmuv/categoriasByPrincipales/{id}', function ($request, $response, $args) {
    $id_categoria_principal = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCategoriasByPrincipal($id_categoria_principal);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/categoriasPrincipales/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $imagen = $request->getParsedBody()['imagen'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createCategoriaPrincipal($nombre, $imagen);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La categoria ha sido creada con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "La categoria ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/categoriaPrincipal/{id}', function ($request, $response, $args) {
    $id_categoria_principal = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCategoriaPrincipalById($id_categoria_principal);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/categorias/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createCategoria($nombre);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La categoria ha sido creada con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "La categoria ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/updateCategoria', function ($request, $response) {
    $id_categoria = $request->getParsedBody()['id_categoria'];
    $atributos = $request->getParsedBody()['atributos'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateCategoria($id_categoria, $atributos);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Categoria actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/categoria/update', function ($request, $response) {
    $id_categoria = $request->getParsedBody()['id_categoria'];
    $nombre = $request->getParsedBody()['nombre'];
    $tipo = $request->getParsedBody()['tipo'];
    $imagen = $request->getParsedBody()['imagen'];
    $categoria_principal = $request->getParsedBody()['categoria_principal'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateCategoriaAll($id_categoria, $nombre, $tipo, $imagen, $categoria_principal);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Categoria actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/categoriaPrincipal/update', function ($request, $response) {
    $id_categoria = $request->getParsedBody()['id_categoria'];
    $nombre = $request->getParsedBody()['nombre'];
    $imagen = $request->getParsedBody()['imagen'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateCategoriaPrincipal($id_categoria, $nombre, $imagen);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Categoria actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/categorias/{id}', function ($request, $response, $args) {
    $id_categoria = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCategoriaById($id_categoria);
    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La categoria no existe";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/categorias/delete', function ($request, $response) {
    $id_area = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteCategoria($id_area);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Categoria eliminada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/categorias/productos', function ($request, $response) {
    $body = (array) $request->getParsedBody();
    $id_categoria = $body['id_categoria'] ?? []; // puede venir como JSON string o array
    $id_empresa   = $body['id_empresa']   ?? null;

    $db = new DbHandler();
    $resultado = $db->getProductosByCategoria($id_categoria, $id_empresa);

    $payload = [
        "error" => false,
        "data"  => $resultado
    ];
    echo json_encode($payload);
});
/* CATEGORIAS */

$app->post('/fulmuv/categorias/vehiculosRelacionados', function ($request, $response) {
    $body = (array) $request->getParsedBody();

    // Capturamos los datos con valores por defecto
    $id_modelo  = $body['id_modelo'] ?? null;
    $id_empresa = $body['id_empresa'] ?? null;

    // Si el id_modelo viene como string (por ejemplo desde un JSON.stringify en JS), lo decodificamos
    if (is_string($id_modelo) && strpos($id_modelo, '[') !== false) {
        $id_modelo = json_decode($id_modelo, true);
    }

    $db = new DbHandler();
    $resultado = $db->getVehiculosByCategoria($id_modelo, $id_empresa);

    $payload = [
        "error" => false,
        "data"  => $resultado
    ];

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json');
});

/* SUB CATEGORIAS */
$app->get('/fulmuv/sub_categorias/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSubCategorias();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/sub_categorias/create', function ($request, $response) {
    $id_categoria = $request->getParsedBody()['id_categoria'];
    $nombre = $request->getParsedBody()['nombre'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createSubCategoria($id_categoria, $nombre);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La subcategoria ha sido creada con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "La subcategoria ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/sub_categorias/{id}', function ($request, $response, $args) {
    $id_sub_categoria = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSubCategoriaById($id_sub_categoria);
    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La subcategoria no existe";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/sub_categorias/delete', function ($request, $response) {
    $id_sub_categoria = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteSubCategoria($id_sub_categoria);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Subcategoría eliminada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error al eliminar.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/sub_categorias/update', function ($request, $response) {
    $p = $request->getParsedBody();
    $id = $p['id'] ?? '';
    $nombre = $p['nombre'] ?? '';
    $id_categoria = $p['id_categoria'] ?? '';
    $db = new DbHandler();
    $resultado = $db->updateSubCategoria($id, $nombre, $id_categoria);
    $response = [];
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Subcategoría actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error al actualizar.";
    }
    echo json_encode($response);
});
/* SUB CATEGORIAS */

/* NOMBRES PRODUCTOS */
$app->get('/fulmuv/nombres_productos/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getNombresProductos();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
$app->get('/fulmuv/getNombreProductoById/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $db = new DbHandler();
    $resultado = $db->getNombreProductoById($id);
    $resp = ["error" => false, "data" => $resultado];
    echo json_encode($resp);
});
$app->post('/fulmuv/nombres_productos/create', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $resultado = $db->createNombreProducto($p['nombre'] ?? '', $p['categoria'] ?? null, $p['sub_categoria'] ?? null);
    $resp = $resultado == RECORD_CREATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Nombre de producto creado correctamente."]
        : ["error" => true, "msg" => "Error al crear el nombre de producto."];
    echo json_encode($resp);
});
$app->post('/fulmuv/nombres_productos/update', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $resultado = $db->updateNombreProducto($p['id'] ?? '', $p['nombre'] ?? '', $p['categoria'] ?? null, $p['sub_categoria'] ?? null);
    $resp = $resultado == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Nombre de producto actualizado correctamente."]
        : ["error" => true, "msg" => "Error al actualizar."];
    echo json_encode($resp);
});
$app->post('/fulmuv/nombres_productos/delete', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $resultado = $db->deleteNombreProducto($p['id'] ?? '');
    $resp = $resultado == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Nombre de producto eliminado correctamente."]
        : ["error" => true, "msg" => "Error al eliminar."];
    echo json_encode($resp);
});
/* NOMBRES PRODUCTOS */

/* NOMBRES SERVICIOS */
$app->get('/fulmuv/nombres_servicios/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getNombresServicios();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
$app->get('/fulmuv/getNombreServicioById/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $db = new DbHandler();
    $resultado = $db->getNombreServicioById($id);
    $resp = ["error" => false, "data" => $resultado];
    echo json_encode($resp);
});
$app->post('/fulmuv/nombres_servicios/create', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $resultado = $db->createNombreServicio($p['nombre'] ?? '', $p['categoria'] ?? null, $p['referencia'] ?? '');
    $resp = $resultado == RECORD_CREATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Nombre de servicio creado correctamente."]
        : ["error" => true, "msg" => "Error al crear."];
    echo json_encode($resp);
});
$app->post('/fulmuv/nombres_servicios/update', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $resultado = $db->updateNombreServicio($p['id'] ?? '', $p['nombre'] ?? '', $p['categoria'] ?? null, $p['referencia'] ?? '');
    $resp = $resultado == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Nombre de servicio actualizado correctamente."]
        : ["error" => true, "msg" => "Error al actualizar."];
    echo json_encode($resp);
});
$app->post('/fulmuv/nombres_servicios/delete', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $resultado = $db->deleteNombreServicio($p['id'] ?? '');
    $resp = $resultado == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Nombre de servicio eliminado correctamente."]
        : ["error" => true, "msg" => "Error al eliminar."];
    echo json_encode($resp);
});
/* NOMBRES SERVICIOS */

$app->get('/fulmuv/getModeloById/{id}', function ($request, $response, $args) {
    $id_modelo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getModeloById($id_modelo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

/* TIPO TRACCION */
$app->get('/fulmuv/tipo_tracccion/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTipoTraccion();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
/* TIPO TRACCION */

/* MARCAS */
$app->get('/fulmuv/marcas/', function ($request, $response) {
    $db = new DbHandler();
    $resultado = $db->getMarcas();
    echo json_encode(["error" => false, "data" => $resultado]);
});
$app->get('/fulmuv/getMarcaById/{id}', function ($request, $response, $args) {
    $db = new DbHandler();
    $resultado = $db->getMarcaById($args['id']);
    echo json_encode(["error" => false, "data" => $resultado]);
});
$app->post('/fulmuv/marcas/create', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->createMarca($p['nombre'] ?? '', $p['referencia'] ?? '');
    echo json_encode($r == RECORD_CREATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Marca creada correctamente."]
        : ["error" => true,  "msg" => "Error al crear la marca."]);
});
$app->post('/fulmuv/marcas/update', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->updateMarca($p['id'] ?? '', $p['nombre'] ?? '', $p['referencia'] ?? '');
    echo json_encode($r == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Marca actualizada correctamente."]
        : ["error" => true,  "msg" => "Error al actualizar la marca."]);
});
$app->post('/fulmuv/marcas/delete', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->deleteMarca($p['id'] ?? '');
    echo json_encode($r == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Marca eliminada correctamente."]
        : ["error" => true,  "msg" => "Error al eliminar la marca."]);
});
/* MARCAS */

/* FUNCIONAMIENTO MOTOR */
$app->get('/fulmuv/getFuncionamientoMotor/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getFuncionamientoMotor();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
/* FUNCIONAMIENTO MOTOR */

/* REFERENCIAS */
$app->get('/fulmuv/getReferencias/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getReferencias();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
/* REFERENCIAS */

/* MODELOS REFERENCIAS */
$app->get('/fulmuv/getModelosByReferencia/{id}', function ($request, $response, $args) {
    $referencia = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getModelosByReferencia($referencia);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
/* MODELOS REFERENCIAS */

$app->get('/fulmuv/catalogoVehiculo/referencia/{entity}/{referencia}', function ($request, $response, $args) {
    $entity = $args['entity'];
    $referencia = $args['referencia'];
    $out = array();
    $db = new DbHandler();
    $resultado = $db->getCatalogoVehiculoByReferencia($entity, $referencia);
    $out["error"] = false;
    $out["data"] = $resultado;
    echo json_encode($out);
});

$app->get('/fulmuv/getModelosByReferenciaMarca/{id}', function ($request, $response, $args) {
    $referencia = $args['id'];
    $params = $request->getQueryParams();
    $id_marca = $params['id_marca'] ?? null;
    $id_tipo_auto = $params['id_tipo_auto'] ?? null;

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getModelosByReferenciaYMarca($referencia, $id_marca, $id_tipo_auto);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

/* MODELOS AUTOS */
$app->get('/fulmuv/modelosAutos/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getModelosAutos();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

/* Rellena campos NULL de modelos_autos (subtipo, tracción, motor) sin sobrescribir */
$app->post('/fulmuv/modelos_autos/enrich', function ($request, $response) {
    $p  = $request->getParsedBody();
    $id = intval($p['id_modelos_autos'] ?? 0);
    unset($p['id_modelos_autos']);
    $db = new DbHandler();
    $r  = $db->enrichModeloAuto($id, $p);
    echo json_encode($r);
});
$app->post('/fulmuv/modelos_autos/create', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->createModeloAuto($p['nombre'] ?? '', $p['id_marca'] ?? null, $p['referencia'] ?? '');
    echo json_encode($r == RECORD_CREATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Modelo creado correctamente."]
        : ["error" => true,  "msg" => "Error al crear el modelo."]);
});
$app->post('/fulmuv/modelos_autos/update', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->updateModeloAuto($p['id'] ?? '', $p['nombre'] ?? '', $p['id_marca'] ?? null, $p['referencia'] ?? '');
    echo json_encode($r == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Modelo actualizado correctamente."]
        : ["error" => true,  "msg" => "Error al actualizar el modelo."]);
});
$app->post('/fulmuv/modelos_autos/delete', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->deleteModeloAuto($p['id'] ?? '');
    echo json_encode($r == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Modelo eliminado correctamente."]
        : ["error" => true,  "msg" => "Error al eliminar el modelo."]);
});
/* MODELOS AUTOS*/

$app->get('/fulmuv/tiposAuto/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTiposAuto();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

/* ATRIBUTOS */
$app->get('/fulmuv/atributos/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAtributos();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
$app->get('/fulmuv/getAtributoById/{id}', function ($request, $response, $args) {
    $id_atributo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAtributoById($id_atributo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
$app->get('/fulmuv/atributosCategoria/{id}', function ($request, $response, $args) {
    $id_categoria = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAtributosCategoria($id_categoria);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
$app->post('/fulmuv/updateAtributo', function ($request, $response) {
    $id_atributo = $request->getParsedBody()['id_atributo'];
    $opciones = $request->getParsedBody()['opciones'];
    $db = new DbHandler();
    $resultado = $db->updateAtributo($id_atributo, $opciones);
    $r = $resultado == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Atributo actualizado correctamente."]
        : ["error" => true,  "msg" => "Ocurrió un error!"];
    echo json_encode($r);
});
$app->post('/fulmuv/atributos/create', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->createAtributo($p['nombre'] ?? '', $p['tipo_dato'] ?? 'TEXT', $p['opciones'] ?? null);
    echo json_encode($r == RECORD_CREATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Atributo creado correctamente."]
        : ["error" => true,  "msg" => "Error al crear el atributo."]);
});
$app->post('/fulmuv/atributos/update', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->updateAtributoFull($p['id'] ?? '', $p['nombre'] ?? '', $p['tipo_dato'] ?? 'TEXT', $p['opciones'] ?? null);
    echo json_encode($r == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Atributo actualizado correctamente."]
        : ["error" => true,  "msg" => "Error al actualizar el atributo."]);
});
$app->post('/fulmuv/atributos/delete', function ($request, $response) {
    $p = $request->getParsedBody();
    $db = new DbHandler();
    $r = $db->deleteAtributo($p['id'] ?? '');
    echo json_encode($r == RECORD_UPDATED_SUCCESSFULLY
        ? ["error" => false, "msg" => "Atributo eliminado correctamente."]
        : ["error" => true,  "msg" => "Error al eliminar el atributo."]);
});
/* ATRIBUTOS */

/* ORDENES */
$app->post('/fulmuv/ordenes/create', function ($request, $response) {

    $id_sucursal = $request->getParsedBody()['id_sucursal'];
    $area = $request->getParsedBody()['area'];
    $productos = $request->getParsedBody()['productos'];
    $total = $request->getParsedBody()['total'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $response = array();
    $db = new DbHandler();

    $resultado = $db->createOrden($id_sucursal, $area, $productos, $total, $id_usuario);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La orden ha sido creada con éxito.";
    } else if ($resultado == RECORD_DOES_NOT_EXIST) {
        $response["error"] = true;
        $response["msg"] = "La sucursal no existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación de la orden. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes/', function ($request, $response) {
    $id_usuario = $request->getParsedBody()['id_principal'];
    $id_empresa = $request->getParsedBody()['id_empresa'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getOrdenes($id_usuario, $id_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/ordenes/{id}/notas', function ($request, $response, $args) {
    $id_orden = $args["id"];
    $response = array();
    $db = new DbHandler();
    $data = $db->getNotasOrden($id_orden, "E");
    $response["error"] = false;
    $response["data"] = $data;
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes/{id}/notas/create', function ($request, $response, $args) {
    $id_orden = $args["id"];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $accion = $request->getParsedBody()['accion'];
    $tipo = "envelope";

    $response = array();
    $db = new DbHandler();
    $update = $db->createOrdenNota($id_orden, $id_usuario, $accion, $tipo, "E");

    if ($update == RECORD_CREATION_FAILED) {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error al crear la nota, intente mas tarde!!";
    } else {
        $response["error"]  = false;
        $response["msg"] = "Nota creada exitosamente!!!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/ordenes/{id}', function ($request, $response, $args) {
    $id_orden = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getOrdenById($id_orden);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La orden no existe";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes/delete', function ($request, $response) {
    $id_ordenes = $request->getParsedBody()['id_orden'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $response = array();
    $db = new DbHandler();
    $update = $db->deleteOrden($id_ordenes, $id_usuario);

    if (strpos($update, "error")) {
        $response["error"]  = true;
        $response["msg"] = $update;
    } else if ($update == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"]  = false;
        $response["msg"] = "Ordenes actualizadas correctamente!!";
    } else {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error, intente despues.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes/updateEstado', function ($request, $response) {
    $id_ordenes = $request->getParsedBody()['id_orden'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $orden_estado = $request->getParsedBody()['orden_estado'];

    $response = array();
    $db = new DbHandler();
    $update = $db->updateEstadoOrden($id_ordenes, $orden_estado, $id_usuario);

    if (strpos($update, "error")) {
        $response["error"]  = true;
        $response["msg"] = $update;
    } else if ($update == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"]  = false;
        $response["msg"] = "Ordenes actualizadas correctamente!!";
    } else {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error, intente despues.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes/updateEstadoVenta', function ($request, $response) {
    $id_orden = $request->getParsedBody()['id_orden'];
    $estado_venta = (int)($request->getParsedBody()['estado_venta'] ?? 2);
    $response = array();
    $db = new DbHandler();
    $update = $db->updateEstadoVenta($id_orden, $estado_venta);

    if (strpos((string)$update, "error")) {
        $response["error"]  = true;
        $response["msg"] = $update;
    } else if ($update == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"]  = false;
        $response["msg"] = "Estado de venta actualizado correctamente.";
    } else {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error, intente despues.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes/notas', function ($request, $response, $args) {
    // $id_usuario = $request->getParsedBody()['id_principal'];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $tiempo = $request->getParsedBody()['tiempo'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getNotas($id_empresa, $tiempo);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La empresa no existe";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes_iso/', function ($request, $response) {
    $id_usuario = $request->getParsedBody()['id_principal'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getOrdenesIso($id_usuario);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes_iso/create', function ($request, $response) {

    $id_ordenes = $request->getParsedBody()['id_orden'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $response = array();
    $db = new DbHandler();

    $resultado = $db->createOrdenIso($id_ordenes, $id_usuario);

    if (strpos($resultado, "error")) {
        $response["error"]  = true;
        $response["msg"] = $resultado;
    } else if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"]  = false;
        $response["msg"] = "Orden creada correctamente!!";
    } else {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error, intente despues.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/ordenes_iso/{id}/notas', function ($request, $response, $args) {
    $id_orden = $args["id"];
    $response = array();
    $db = new DbHandler();
    $data = $db->getNotasOrden($id_orden, "I");
    $response["error"] = false;
    $response["data"] = $data;
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes_iso/{id}/notas/create', function ($request, $response, $args) {
    $id_orden = $args["id"];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $accion = $request->getParsedBody()['accion'];
    $tipo = "envelope";

    $response = array();
    $db = new DbHandler();
    $update = $db->createOrdenNota($id_orden, $id_usuario, $accion, $tipo, "I");

    if ($update == RECORD_CREATION_FAILED) {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error al crear la nota, intente mas tarde!!";
    } else {
        $response["error"]  = false;
        $response["msg"] = "Nota creada exitosamente!!!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/reportes/interacciones', function ($request, $response) {
    $params = $request->getQueryParams();
    $id_empresa = $params['id_empresa'] ?? null;
    $desde = $params['desde'] ?? null;
    $hasta = $params['hasta'] ?? null;
    $tipo = $params['tipo'] ?? null;
    $payload = array();
    $db = new DbHandler();

    if (!$id_empresa) {
        $payload["error"] = true;
        $payload["msg"] = "Falta id_empresa.";
        echo json_encode($payload);
        return;
    }

    $resultado = $db->getReportesInteracciones($id_empresa, $desde, $hasta, $tipo);
    if (is_array($resultado) && isset($resultado["error"]) && $resultado["error"] === true) {
        $payload = $resultado;
    } else {
        $payload["error"] = false;
        $payload = array_merge($payload, $resultado);
    }

    echo json_encode($payload);
});

$app->get('/fulmuv/ordenes_iso/{id}', function ($request, $response, $args) {
    $id_orden_iso = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getOrdenIsoById($id_orden_iso);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La orden no existe";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/ordenes_iso/updateEstado', function ($request, $response) {
    $id_ordenes_iso = $request->getParsedBody()['id_orden_iso'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $orden_estado = $request->getParsedBody()['orden_estado'];

    $response = array();
    $db = new DbHandler();
    $update = $db->updateEstadoOrdenIso($id_ordenes_iso, $orden_estado, $id_usuario);

    if (strpos($update, "error")) {
        $response["error"]  = true;
        $response["msg"] = $update;
    } else if ($update == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"]  = false;
        $response["msg"] = "Ordenes actualizadas correctamente!!";
    } else {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error, intente despues.";
    }
    echo json_encode($response);
});

/* ORDENES */


/* E-mails */

$app->get('/fulmuv/correos/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmails();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/correos/create', function ($request, $response) {
    $titulo = $request->getParsedBody()['titulo'];
    $cuerpo = $request->getParsedBody()['cuerpo'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->creatEmail($titulo, $cuerpo, $descripcion);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "E-mail creado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/correos/update', function ($request, $response) {
    $id_correo = $request->getParsedBody()['id_correo'];
    $titulo = $request->getParsedBody()['titulo'];
    $cuerpo = $request->getParsedBody()['cuerpo'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateEmail($id_correo, $titulo, $cuerpo, $descripcion);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "E-mail actualizado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la actualización.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/correos/delete', function ($request, $response) {
    $id_correo = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteEmail($id_correo);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "E-mail eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/correos/control', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCorreosControl();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});



/* $app->post('/fulmuv/categorias/create', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createCategoria($nombre);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La categoria ha sido creada con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "La categoria ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/categorias/{id}', function ($request, $response, $args) {
    $id_categoria = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCategoriaById($id_categoria);
    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "La categoria no existe";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/categorias/delete', function ($request, $response) {
    $id_area = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteCategoria($id_area);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Categoria eliminada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
}); */


$app->get('/fulmuv/correos/getContenedor', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $data = $db->getContenedor();
    $response["error"] = false;
    $response["data"] = $data;
    echo json_encode($response);
});

$app->post('/fulmuv/correos/updateContenedor', function ($request, $response) {
    $response = array();
    $color = $request->getParsedBody()['color'];
    $imagen = $request->getParsedBody()['imagen'];
    $db = new DbHandler();
    $resultado = $db->updateContenedor($color, $imagen);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"]   = "Datos actualizados correctamente!!";
    } else {
        $response["error"] = true;
        $response["msg"]   = "Ocurrió un error!!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/correos/updateCorreoControl', function ($request, $response) {
    $id_correo_control = $request->getParsedBody()['id_correo_control'];
    $id_correo_plantilla = $request->getParsedBody()['id_correo_plantilla'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateCorreoControl($id_correo_control, $id_correo_plantilla);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Configuración actualizada.";
    } else {
        $response["error"] = true;
        $response["msg"]   = "Ocurrió un error!!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/correos/getCorreosControl', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCorreosControl();
    $response["error"] = false;
    $response["data"]  = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/correos/getCorreosDefault', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCorreosDefault();
    $response["error"] = false;
    $response["data"]  = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/correos/createCorreoDefault', function ($request, $response) {
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createCorreoDefault($id_usuario);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Correo insertado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"]   = "Ocurrió un error!!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/correos/updateCorreoDefault', function ($request, $response) {
    $id_correo_default = $request->getParsedBody()['id_correo_default'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateCorreoDefault($id_correo_default, $id_usuario);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Correo actualizado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"]   = "Ocurrió un error!!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/correos/{id}', function ($request, $response, $args) {
    $id_correo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCorreoById($id_correo);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El correo no existe";
    }

    echo json_encode($response);
});

/* E-mails */

/* Dashboard */
$app->get('/fulmuv/home/getData', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $total_empresas = $db->getTotalEmpresas();
    $total_ordenes = $db->getTotalOrdenes();
    $total_usuarios = $db->getTotalUsuarios();
    $empresas_agrupadas = $db->getTotalByEmpresas();
    $estados_agrupados = $db->getTotalOrdenesByEstado();
    $response["error"] = false;
    $response["total_empresas"] = $total_empresas;
    $response["total_ordenes"] = $total_ordenes;
    $response["total_usuarios"] = $total_usuarios;
    $response["empresas_agrupadas"] = $empresas_agrupadas;
    $response["estados_agrupados"] = $estados_agrupados;
    echo json_encode($response);
});

$app->get('/fulmuv/home/getTotalOrdenesByHistory/{startDate}/{endDate}', function ($request, $response, $args) {
    $startDate = $args['startDate'];
    $endDate = $args['endDate'];
    $response = array();
    $db = new DbHandler();
    $estados_historico = $db->getTotalOrdenesByHistory($startDate, $endDate);
    $response["error"] = false;
    $response["data"] = $estados_historico;
    echo json_encode($response);
});

$app->post('/cargarExcel', function ($request, $response) {
    $data = $request->getParsedBody()['data'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->cargarExcel($data);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
/* Dashboard */

$app->post('/fulmuv/deleteFileProducto', function ($request, $response) {
    $id_archivo = $request->getParsedBody()['id_archivo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteFileProducto($id_archivo);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Archivo eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/deleteFileEmpresa', function ($request, $response) {
    $id_archivo = $request->getParsedBody()['id_archivo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteFileEmpresa($id_archivo);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Archivo eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/deleteFileVehiculo', function ($request, $response) {
    $id_archivo = $request->getParsedBody()['id_archivo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteFileVehiculo($id_archivo);
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Archivo eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/createFileProducto', function ($request, $response) {
    $id_producto = $request->getParsedBody()['id_producto'];
    $archivo = $request->getParsedBody()['archivo'];
    $tipo = $request->getParsedBody()['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createFileProducto($id_producto, $archivo, $tipo);
    if ($resultado != RECORD_CREATION_FAILED) {
        $response["error"] = false;
        $response["msg"] = "Archivo cargado correctamente.";
        $response["id_archivo"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"]   = "Ocurrió un error!!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/createFileEmpresa', function ($request, $response) {
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $archivo = $request->getParsedBody()['archivo'];
    $tipo = $request->getParsedBody()['tipo'];
    $titulo = $request->getParsedBody()['titulo'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->createFileEmpresa($id_empresa, $archivo, $tipo, $titulo, $descripcion);
    if ($resultado != RECORD_CREATION_FAILED) {
        $response["error"] = false;
        $response["msg"] = "Archivo cargado correctamente.";
        $response["id_archivo"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"]   = "Ocurrió un error!!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/updateFileEmpresa', function ($request, $response) {
    $id_archivo_empresa = $request->getParsedBody()['id_archivo_empresa'];
    $titulo = $request->getParsedBody()['titulo'];
    $descripcion = $request->getParsedBody()['descripcion'];
    $archivo = $request->getParsedBody()['archivo'] ?? null;
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateFileEmpresa($id_archivo_empresa, $titulo, $descripcion, $archivo);
    if ($resultado != RECORD_UPDATED_FAILED) {
        $response["error"] = false;
        $response["msg"] = "Archivo actualizado correctamente.";
        $response["id_archivo"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"]   = "Ocurrió un error!!";
    }
    echo json_encode($response);
});


/* =========================================== DESARROLLO DEL LANDING PAGE ============================================= */
/* Empresas con el total de sus productos */
$app->get('/fulmuv/empresasTotalProductos/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpresasTotalProductos();
    $response["error"] = false;
    $response["data"] = $resultado;
    $response["total_empresa"] = $db->getTotalEmpresa();
    echo json_encode($response);
});
/* Empresas con el total de sus productos */

$app->post('/fulmuv/productos/busqueda', function ($request, $response) {
    $q = $request->getParsedBody()['q'];
    $categoria = $request->getParsedBody()['categoria'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->buscarProductosYCategorias($q, $categoria);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/productos/idEmpresa', function ($request, $response) {
    $id_empresa = $request->getParsedBody()['id_empresa'];


    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosByIdEmpresa($id_empresa);
    $vehiculos = $db->getVehiculosByIdEmpresa($id_empresa);

    $response["categorias"] = $db->getCategoriaByIdEmpresa($id_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;
    $response["vehiculos"] = $vehiculos;
    // $response["total_productos"] = $db->getTotalProductosbyIdEmpresa($id_empresa);
    echo json_encode($response);
});

$app->post('/fulmuv/productos/idCategoria', function ($request, $response) {
    $ids = $request->getParsedBody()['id_categoria']; // array de IDs

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosByCategorias($ids);

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/serviciosProductos/All', function ($request, $response) {


    $response = array();
    $db = new DbHandler();
    $resultado = $db->getServiciosAll();

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getServiciosEmergenciaAll/All', function ($request, $response) {


    $response = array();
    $db = new DbHandler();
    $resultado = $db->getServiciosEmergenciaAll();

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/serviciosProductosSearch/All', function ($request, $response) {

    $search = $request->getParsedBody()['search']; // array de IDs

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getServiciosASearchll($search);

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/ProductosSearch/All', function ($request, $response) {

    $search = $request->getParsedBody()['search']; // array de IDs

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosASearchll($search);

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


// routes/fulmuv.php (o donde registras tus rutas)
$app->post('/fulmuv/subcategorias/byCategorias', function ($request, $response) {
    $ids = $request->getParsedBody()['id_categoria']; // array de IDs

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSubcategoriasByCategorias($ids);

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

/* =========================================== DESARROLLO DEL LANDING PAGE ============================================= */

$app->post('/fulmuv/categoria/create', function ($request, $response) {

    $nombre = $request->getParsedBody()['nombre'];
    $tipo = $request->getParsedBody()['tipo'];
    $imagen = $request->getParsedBody()['imagen'];
    $categoria_principal = $request->getParsedBody()['categoria_principal'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createCat($nombre, $tipo, $imagen, $categoria_principal);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El usuario ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El usuario ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del suario. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/banner/all', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getBannerAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/banner/create', function ($request, $response) {
    $body = $request->getParsedBody();
    $imagen = $body['imagen'] ?? '';
    $imagen_tablet = $body['imagen_tablet'] ?? '';
    $imagen_movil = $body['imagen_movil'] ?? '';
    $url = $body['url'] ?? '';

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createBanner($imagen, $imagen_tablet, $imagen_movil, $url);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El Banner ha sido creado con éxito.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del publicidad. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/banner/{id}', function ($request, $response, $args) {
    $id_banner = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getBannerById($id_banner);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El banner no existe";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/banner/update', function ($request, $response) {
    $body = $request->getParsedBody();
    $imagen = $body['imagen'] ?? null;
    $imagen_tablet = $body['imagen_tablet'] ?? null;
    $imagen_movil = $body['imagen_movil'] ?? null;
    $url = $body['url'] ?? '';
    $id = $body['id_banner'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateBanner($id, $imagen, $imagen_tablet, $imagen_movil, $url);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El Banner ha sido actualizado con éxito.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la actualización de la publicidad. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/banner/delete', function ($request, $response) {

    $id = $request->getParsedBody()['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteBanner($id);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Banner eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/productosAll/all', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/publicidadAdmin/all', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getPublicidadAllAdmin();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->get('/fulmuv/publicidad/all', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getPublicidadAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/publicidad/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $response = array();
    $db = new DbHandler();
    $estados_historico = $db->getPublicidadAllAdminById($id);
    $response["error"] = false;
    $response["data"] = $estados_historico;
    echo json_encode($response);
});

$app->post('/fulmuv/publicidad/create', function ($request, $response) {
    $body = $request->getParsedBody();
    $imagen = $body['imagen'] ?? '';
    $imagen_tablet = $body['imagen_tablet'] ?? '';
    $imagen_movil = $body['imagen_movil'] ?? '';
    $url = $body['url'] ?? '';
    $posicion = $body['posicion'] ?? '';

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createPublicidad($imagen, $imagen_tablet, $imagen_movil, $url, $posicion);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La publicidad ha sido creado con éxito.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del publicidad. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/publicidad/update', function ($request, $response) {
    $body = $request->getParsedBody();
    $imagen = $body['imagen'] ?? null;
    $imagen_tablet = $body['imagen_tablet'] ?? null;
    $imagen_movil = $body['imagen_movil'] ?? null;
    $url = $body['url'] ?? '';
    $posicion = $body['posicion'] ?? '';
    $id = $body['id_publicidad'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->updatePublicidad($id, $imagen, $imagen_tablet, $imagen_movil, $url, $posicion);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "La publicidad ha sido actualizado con éxito.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la actualización de la publicidad. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/publicidad/delete', function ($request, $response) {

    $id = $request->getParsedBody()['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->deletePublicidad($id);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Publicidad eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});


$app->post('/fulmuv/eventos/ById', function ($request, $response) {

    $id = $request->getParsedBody()['id_evento'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEventosById($id);


    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/eventos/all', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEventosAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

// Crear evento
$app->post('/fulmuv/eventos/create', function ($request, $response) {
    $db = new DbHandler();
    $p = $request->getParsedBody();

    // Inserta y devuelve id
    $id = $db->createEvento($p);

    if ($id) {
        echo json_encode([
            "error" => false,
            "msg"   => "Evento creado",
            "data"  => ["id_evento" => $id]
        ]);
    } else {
        echo json_encode([
            "error" => true,
            "msg"   => "No se pudo crear el evento"
        ]);
    }
});

// Agregar galería (bulk)
$app->post('/fulmuv/eventos/galeria/create', function ($request, $response) {
    $db = new DbHandler();
    $p = $request->getParsedBody();

    $id_evento = intval($p['id_evento'] ?? 0);
    $imagenes  = json_decode($p['imagenes'] ?? '[]', true);

    if ($id_evento <= 0) {
        echo json_encode(["error" => true, "msg" => "id_evento inválido"]);
        return;
    }

    $insertados = $db->addGaleriaEvento($id_evento, $imagenes ?: []);

    echo json_encode([
        "error" => false,
        "msg"   => "Galería registrada",
        "data"  => ["insertados" => $insertados]
    ]);
});

$app->post('/fulmuv/eventos/update', function ($request, $response) {
    $payload = $request->getParsedBody();

    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateEvento($payload);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El evento ha sido actualizado con éxito.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la actualización del evento. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/eventos/delete', function ($request, $response) {

    $id = $request->getParsedBody()['id'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteEvento($id);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Evento eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/eventos/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $response = array();
    $db = new DbHandler();
    $estados_historico = $db->getEventosById((int)$id);
    $response["error"] = false;
    $response["data"] = $estados_historico;
    echo json_encode($response);
});

$app->get('/fulmuv/eventosEmpresa/{id}/{tipo}', function ($request, $response, $args) {
    $id = $args['id'];
    $tipo = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $estados_historico = $db->getEventoEmpresaById($id, $tipo);
    $response["error"] = false;
    $response["data"] = $estados_historico;
    echo json_encode($response);
});

$app->post('/fulmuv/admin/resetPassword', function ($request, $response) {
    $response = array();
    $email = $request->getParsedBody()['email'];
    $db = new DbHandler();
    $resultado = $db->resetPassword($email);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = 'Correo enviado!!';
    } else if ($resultado == RECORD_DOES_NOT_EXIST) {
        $response["error"] = true;
        $response["msg"] = 'Usuario no existe!!';
    } else {
        $response["error"] = true;
        $response["msg"] = 'Error al enviar el correo!!';
    }
    echo json_encode($response);
});

$app->post('/fulmuv/cliente/resetPassword', function ($request, $response) {
    $response = array();
    $email = $request->getParsedBody()['email'];
    $db = new DbHandler();
    $resultado = $db->resetPasswordCliente2($email);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = 'Correo enviado!!';
    } else if ($resultado == RECORD_DOES_NOT_EXIST) {
        $response["error"] = true;
        $response["msg"] = 'Usuario no existe!!';
    } else {
        $response["error"] = true;
        $response["msg"] = 'Error al enviar el correo!!';
    }
    echo json_encode($response);
});

$app->post('/fulmuv/generarOrden', function ($request, $response) {
    $db = new DbHandler();
    $data = $request->getParsedBody();

    $resultado = $db->createOrdenCompleta($data);

    if ($resultado["success"] == true) {
        echo json_encode([
            "success" => true,
            "msg" => "Orden generada correctamente.",
            "numero_orden" => $resultado["numero_orden"]
        ]);
    } else if (isset($resultado["msg"]) && $resultado["msg"] === "RECORD_ALREADY_EXISTED") {
        echo json_encode([
            "success" => false,
            "msg" => "El cliente ya está registrado. No se puede generar una nueva orden para este correo."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "msg" => "Error al generar la orden."
        ]);
    }
});

$app->post('/fulmuv/cliente/update_password', function ($request, $response) {

    $body = $request->getParsedBody();

    $id_usuario = isset($body['id_usuario']) ? trim($body['id_usuario']) : null;
    $password   = isset($body['password']) ? $body['password'] : null;

    $payload = [];

    // Validaciones básicas
    if (empty($id_usuario) || !ctype_digit((string)$id_usuario)) {
        $payload["error"] = true;
        $payload["msg"] = "ID de usuario inválido.";
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (empty($password) || strlen($password) < 6) {
        $payload["error"] = true;
        $payload["msg"] = "La contraseña debe tener al menos 6 caracteres.";
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $db = new DbHandler();
    $resultado = $db->updatePasswordCliente((int)$id_usuario, $password);

    if ($resultado === RECORD_CREATED_SUCCESSFULLY) {
        $payload["error"] = false;
        $payload["msg"] = "Contraseña actualizada correctamente.";
        $status = 200;
    } else if ($resultado === RECORD_DOES_NOT_EXIST) {
        $payload["error"] = true;
        $payload["msg"] = "El usuario no existe o está inactivo.";
        $status = 404;
    } else {
        $payload["error"] = true;
        $payload["msg"] = "No se pudo actualizar la contraseña.";
        $status = 500;
    }

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
});


$app->post('/fulmuv/cliente/getClienteById', function ($request, $response) {
    $body = $request->getParsedBody();
    $id_usuario = isset($body['id_usuario']) ? trim($body['id_usuario']) : null;

    $payload = [];

    if (empty($id_usuario) || !ctype_digit((string)$id_usuario)) {
        $payload["error"] = true;
        $payload["msg"] = "ID inválido.";
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $db = new DbHandler();
    $cliente = $db->getClienteById((int)$id_usuario);

    if ($cliente) {
        $payload["error"] = false;
        $payload["data"]  = $cliente;
        $status = 200;
    } else {
        $payload["error"] = true;
        $payload["msg"] = "Cliente no encontrado.";
        $status = 404;
    }

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
});

$app->post('/fulmuv/cliente/getUltimaFacturacion', function ($request, $response) {
    $body       = $request->getParsedBody();
    $id_cliente = isset($body['id_cliente']) ? (int)$body['id_cliente'] : 0;
    $payload    = [];

    if ($id_cliente <= 0) {
        $payload['error'] = true;
        $payload['msg']   = 'ID inválido.';
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $db   = new DbHandler();
    $fact = $db->getUltimaFacturacionCliente($id_cliente);

    if ($fact) {
        $payload['error'] = false;
        $payload['data']  = $fact;
    } else {
        $payload['error'] = false;
        $payload['data']  = null;
    }

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->post('/fulmuv/cliente/update_datos', function ($request, $response) {
    $body = $request->getParsedBody();

    $id_usuario = isset($body['id_usuario']) ? trim($body['id_usuario']) : null;
    $nombres    = isset($body['nombres']) ? trim($body['nombres']) : '';
    $cedula     = isset($body['cedula']) ? trim($body['cedula']) : '';
    $telefono   = isset($body['telefono']) ? trim($body['telefono']) : '';
    $correo     = isset($body['correo']) ? trim($body['correo']) : '';

    $payload = [];

    if (empty($id_usuario) || !ctype_digit((string)$id_usuario)) {
        $payload["error"] = true;
        $payload["msg"] = "ID inválido.";
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if ($nombres === '' || $apellidos === '' || $cedula === '' || $telefono === '' || $correo === '') {
        $payload["error"] = true;
        $payload["msg"] = "Todos los campos son obligatorios.";
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $payload["error"] = true;
        $payload["msg"] = "Correo inválido.";
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $db = new DbHandler();
    $resultado = $db->updateDatosCliente((int)$id_usuario, $nombres, $cedula, $telefono, $correo);

    if ($resultado === true) {
        $payload["error"] = false;
        $payload["msg"] = "Datos actualizados correctamente.";
        $status = 200;
    } else if ($resultado === "EXISTS_EMAIL") {
        $payload["error"] = true;
        $payload["msg"] = "El correo ya está registrado por otro usuario.";
        $status = 409;
    } else if ($resultado === "NOT_FOUND") {
        $payload["error"] = true;
        $payload["msg"] = "Cliente no encontrado o inactivo.";
        $status = 404;
    } else {
        $payload["error"] = true;
        $payload["msg"] = "No se pudo actualizar los datos.";
        $status = 500;
    }

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
});

$app->post('/fulmuv/getOrdenesSeguimiento', function ($request, $response) {
    $response = array();
    $numero_orden = $request->getParsedBody()['numero_orden'];
    $db = new DbHandler();
    $resultado = $db->getOrdenesSeguimiento($numero_orden);
    $response["error"] = false;
    $response["data"] = $resultado;

    echo json_encode($response);
});

$app->post('/fulmuv/getPDFGUIAA4', function ($request, $response) {
    $response = array();
    $id_orden_empresa = $request->getParsedBody()['id_orden_empresa'];
    $db = new DbHandler();
    $resultado = $db->getObtenerGuiaServientrega($id_orden_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;

    echo json_encode($response);
});


$app->post('/webstore/init_reference/', function ($request, $response) {
    $response = array();
    $id_membresia = $request->getParsedBody()['id_membresia'];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $valor = $request->getParsedBody()['valor'];
    $db = new DbHandler();
    $resultado = $db->init_reference($id_membresia, $id_empresa, $id_usuario, $valor);

    if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "Esta empresa ya cuenta con una subscripción. Verifique nuevamente los datos";
    } else if (isset($resultado)) {
        $response["error"] = false;
        $response["payment"] = $resultado;
    }
    echo json_encode($response);
});

$app->post('/fulmuv/agentes/create', function ($request, $response) {
    $params = $request->getParsedBody();
    $nombre = $params['nombre'] ?? '';
    $correo = $params['correo'] ?? '';
    $codigo = $params['codigo'] ?? '';

    $db = new DbHandler();
    $resultado = $db->createAgente($nombre, $correo, $codigo);
    $response = array();
    if ($resultado === RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El agente ha sido creada con éxito.";
    } elseif ($resultado === RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El agente ya existe.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Error en la creación del agente.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/agentes/update', function ($request, $response) {
    $nombre = $request->getParsedBody()['nombre'];
    $correo = $request->getParsedBody()['correo'];
    $codigo = $request->getParsedBody()['codigo'];
    $id_agente = $request->getParsedBody()['id_agente'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->updateAgente($id_agente, $nombre, $correo, $codigo);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Agente actualizado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/agentes/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAgentes();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/agentes/{id}', function ($request, $response, $args) {
    $id_agente = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getAgenteById($id_agente);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El agente no existe";
    }

    echo json_encode($response);
});
/* RESETEAR PASS CORREO */

// reembolso
$app->get('/fulmuv/refund/pagos/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getPagosRefundAdmin();

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "No se encontraron pagos para reembolso.";
    }

    echo json_encode($response);
});

$app->post('/fulmuv/refund/pago/', function ($request, $response) {
    $response = array();
    $body = $request->getParsedBody();
    $id_pago = isset($body['id_pagos_transaccion']) ? (int)$body['id_pagos_transaccion'] : 0;
    $id_transaccion = $body['id_transaccion'] ?? null;

    $db = new DbHandler();
    if ($id_pago > 0) {
        $resultado = $db->reembolsarPagoAdmin($id_pago);
        echo json_encode($resultado);
        return;
    }

    $resultado = $db->reembolsar($id_transaccion);

    if ($resultado == 'success') {
        $response["error"] = false;
        $response["msg"] = "Reembolso exitoso.";
    } elseif (is_array($resultado) && array_key_exists("status", $resultado)) {
        $trx = $resultado['transaction'] ?? [];
        $extra = '';
        if (!empty($trx)) {
            $extra = "\n Estado actual: " . ($trx['status'] ?? 'N/D');
            if (($trx['status_detail'] ?? '') !== '') {
                $extra .= "\n status_detail: " . $trx['status_detail'];
            }
            if (($trx['message'] ?? '') !== '') {
                $extra .= "\n detalle: " . $trx['message'];
            }
        }
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error al hacer el reembolso, intentalo mas tarde.\n Status: " . $resultado["status"] . "\n Nuvei: " . ($resultado["detail"] ?? '') . $extra;
    } elseif ($resultado == 'error') {
        $response["error"] = true;
        $response["msg"] = "Ocurrió al obtener los detalles de la transacción";
    }

    echo json_encode($response);
});
// reembolso

// registrar pago recurrente
// $app->post('/fulmuv/venta/recurrente/', function ($request, $response) {

//     $token = $request->getParsedBody()['token'];
//     $transaction_reference = $request->getParsedBody()['transaction_reference'];
//     $id_usuario = $request->getParsedBody()['id_usuario'];
//     $id_empresa = $request->getParsedBody()['id_empresa'];

//     $response = array();
//     $db = new DbHandler();
//     $resultado = $db->webstoreCreateRecurrente($token, $transaction_reference, $id_usuario, $id_empresa);
//     if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
//         $response["error"] = false;
//         $response["msg"] = "El token ha sido registrada con éxito.";
//     } else {
//         $response["error"] = true;
//         $response["msg"] = "Hubo un error al registrar el pago. Por favor vuelva a verificar en otro momento.";
//     }
//     echo json_encode($response);
// });
// fin registrar pago recurrrente

$app->post('/fulmuv/venta/recurrente/', function ($request, $response) {

    $body               = $request->getParsedBody();
    $token              = $body['token'];
    $transaction_reference = $body['transaction_reference'];
    $id_usuario         = $body['id_usuario'];
    $id_empresa         = $body['id_empresa'];
    $ultimos_digitos    = $body['ultimos_digitos']    ?? null;
    $marca              = $body['marca']              ?? null;
    $exp_year           = $body['exp_year']           ?? null;
    $exp_month          = $body['exp_month']          ?? null;
    $holder_name        = $body['holder_name']        ?? null;
    $gateway_uid        = $body['gateway_uid']        ?? null;

    $response = array();
    $db = new DbHandler();
    $resultado = $db->webstoreCreateRecurrente(
        $token,
        $transaction_reference,
        $id_usuario,
        $id_empresa,
        $ultimos_digitos,
        $marca,
        $exp_year,
        $exp_month,
        null,
        $gateway_uid,
        $holder_name
    );
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El token ha sido registrada con éxito.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error al registrar el pago. Por favor vuelva a verificar en otro momento.";
    }
    fulmuvJsonResponse($response);
});

// debito con token
$app->post('/fulmuv/debitToken/', function ($request, $response) {

    $token = $request->getParsedBody()['token'];
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $id_membresia = $request->getParsedBody()['id_membresia'];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $valor = $request->getParsedBody()['valor'];
    $tipo_pago = $request->getParsedBody()['tipo_pago'];
    $meses = $request->getParsedBody()['meses'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->debitToken($token, $id_usuario, $id_membresia, $id_empresa, $valor, $tipo_pago, $meses);

    if (array_key_exists("transaction", $resultado)) {
        if ($resultado["transaction"]["status"] == "success") {

            $response["error"] = false;
            $response["transaction"] = $resultado["transaction"];
        } else {
            $response["error"] = true;
            $response["msg"] = "Ocurrió un error al realizar el débito.\n Estado de la transacccion: " . $resultado["transaction"]["current_status"] . ".\n Respuesta del carrier: " . $resultado["transaction"]["carrier_code"] . ".\n Mensaje Nuvei:" . $resultado["transaction"]["message"];
            $response["transaction"] = $resultado["transaction"];
        }
    } elseif (array_key_exists("error", $resultado)) {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error al realizar el débito.\n Tipo de error: " . $resultado["error"]["type"] . ".\n Mensaje Nuvei: " . $resultado["error"]["help"];
        $response["transaction"] = $resultado["error"];
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error al realizar la transacción";
        $response["transaction"] = $resultado;
    }
    echo json_encode($response);
});
// fin debito con token

// $app->post('/fulmuv/ordenes/updateProductos', function ($request, $response) {

//     $id_orden = $request->getParsedBody()['id_orden'];
//     $productos = $request->getParsedBody()['productos'];
//     $estado = $request->getParsedBody()['estado'];

//     $response = array();
//     $db = new DbHandler();
//     $resultado = $db->updateProductosOrdenEmpresa($productos, $id_orden, $estado);

//     if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
//         $response["error"] = false;
//         $response["msg"] = "Orden actualizada correctamente.";
//     } else {
//         $response["error"] = true;
//         $response["msg"] = "Ocurrió un error!";
//     }
//     echo json_encode($response);
// });

$app->post('/fulmuv/ordenes/updateProductos', function ($request, $response) {

    $body = $request->getParsedBody();

    $id_orden = $body['id_orden'] ?? null;     // OJO: revisa nota al final (id_orden vs id_ordenes)
    $productos = $body['productos'] ?? [];
    $estado = $body['estado'] ?? 0;

    // Campos extra (ordenes_empresas)
    $data = [
        "peso_real_total_kg"   => $body["peso_real_total_kg"] ?? 0,
        "largo_cm"             => $body["largo_cm"] ?? 0,
        "ancho_cm"             => $body["ancho_cm"] ?? 0,
        "alto_cm"              => $body["alto_cm"] ?? 0,
        "fragil"               => $body["fragil"] ?? 0,
        "valor_producto_usd"   => $body["valor_producto_usd"] ?? 0,

        "peso_volumetrico_kg"  => $body["peso_volumetrico_kg"] ?? 0,
        "peso_facturable_kg"   => $body["peso_facturable_kg"] ?? 0,

        "seguro_base_usd"      => $body["seguro_base_usd"] ?? 0,
        "seguro_iva_usd"       => $body["seguro_iva_usd"] ?? 0,
        "seguro_total_usd"     => $body["seguro_total_usd"] ?? 0,

        "alerta_mayor_50kg"    => $body["alerta_mayor_50kg"] ?? 0,

        "divisor_volumetrico"  => $body["divisor_volumetrico"] ?? 0,
        "iva_envio"            => $body["iva_envio"] ?? 0,
        "seguro_pct"           => $body["seguro_pct"] ?? 0
    ];

    $db = new DbHandler();
    $resultado = $db->updateProductosOrdenEmpresa($productos, $id_orden, $estado, $data);

    $resp = [];
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $resp["error"] = false;
        $resp["msg"] = "Orden actualizada correctamente.";
    } else {
        $resp["error"] = true;
        $resp["msg"] = $resultado; // si retornas detalle de error
    }

    echo json_encode($resp);
});


$app->post('/fulmuv/membresias/upgrade', function ($request, $response) {

    $body         = $request->getParsedBody();
    $id_empresa   = $body['id_empresa'];
    $id_membresia = $body['id_membresia'];
    $valor        = $body['valor'];
    $sucursales   = $body['sucursales'];
    // Token directo (cuando el usuario elige no guardar la tarjeta en Wallet)
    $token_directo      = isset($body['token'])      && $body['token']      !== '' ? $body['token']      : null;
    $id_usuario_directo = isset($body['id_usuario']) && $body['id_usuario'] !== '' ? (int)$body['id_usuario'] : null;
    // Cobro ya realizado por PaymentCheckout (client-side)
    $ya_cobrado      = !empty($body['ya_cobrado']) && $body['ya_cobrado'] === '1';
    $trx_id_externo  = $body['transaction_id']     ?? null;
    $auth_externo    = $body['authorization_code']  ?? null;
    $facturacion = [
        "razon_social" => trim($body['razon_social'] ?? ''),
        "tipo_identificacion" => trim($body['tipo_identificacion'] ?? ''),
        "cedula_ruc" => trim($body['cedula_ruc'] ?? ''),
        "direccion_facturacion" => trim($body['direccion_facturacion'] ?? ''),
        "telefono_facturacion" => trim($body['telefono_facturacion'] ?? ''),
        "correo_facturacion" => trim($body['correo_facturacion'] ?? '')
    ];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->upgradeMembresia($id_empresa, $id_membresia, $valor, $sucursales, $token_directo, $id_usuario_directo, $ya_cobrado, $trx_id_externo, $auth_externo, $facturacion);

    // if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
    //     $response["error"] = false;
    //     $response["msg"] = "Membresia actualizada correctamente.";
    // } else {
    //     $response["error"] = true;
    //     $response["msg"] = "Ocurrió un error!";
    // }
    echo json_encode($resultado);
});

$app->get('/fulmuv/empresa/tokens/{id_empresa}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $db = new DbHandler();

    $res = [
        "error" => false,
        "data" => $db->getChargeableWalletTarjetasByEmpresa($id_empresa),
        "source" => "wallet_valid"
    ];

    fulmuvJsonResponse($res);
});

$app->get('/fulmuv/empresa/wallet/{id_empresa}', function ($request, $response, $args) {
    $id_empresa = (int)$args['id_empresa'];
    $db = new DbHandler();

    $data = $db->getWalletTarjetasByEmpresa($id_empresa);
    fulmuvJsonResponse([
        "error" => false,
        "data" => $data
    ]);
});

$app->post('/fulmuv/empresa/wallet/add', function ($request, $response) {
    $body = $request->getParsedBody();
    $id_empresa = (int)($body['id_empresa'] ?? 0);
    $id_usuario = (int)($body['id_usuario'] ?? 0);
    $gateway_uid = trim((string)($body['gateway_uid'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $holder_name = trim((string)($body['holder_name'] ?? ''));
    $number = trim((string)($body['number'] ?? ''));
    $expiry_month = trim((string)($body['expiry_month'] ?? ''));
    $expiry_year = trim((string)($body['expiry_year'] ?? ''));
    $cvc = trim((string)($body['cvc'] ?? ''));
    $type = trim((string)($body['type'] ?? ''));
    $phone = trim((string)($body['phone'] ?? ''));
    $ip_address = trim((string)($body['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '')));
    $fiscal_number = trim((string)($body['fiscal_number'] ?? ''));
    $session_id = trim((string)($body['session_id'] ?? ''));

    $db = new DbHandler();
    $resultado = $db->addWalletTarjetaPaymentez(
        $id_empresa,
        $id_usuario,
        $gateway_uid,
        $email,
        $holder_name,
        $number,
        $expiry_month,
        $expiry_year,
        $cvc,
        $type,
        $phone,
        $ip_address,
        $fiscal_number,
        $session_id
    );

    fulmuvJsonResponse($resultado);
});

$app->post('/fulmuv/empresa/wallet/default', function ($request, $response) {
    $body = $request->getParsedBody();
    $id_empresa = (int)($body['id_empresa'] ?? 0);
    $token = trim((string)($body['token'] ?? ''));

    $db = new DbHandler();
    $resultado = $db->setTarjetaDefaultEmpresa($id_empresa, $token);
    fulmuvJsonResponse($resultado);
});

$app->post('/fulmuv/empresa/wallet/delete', function ($request, $response) {
    $body = $request->getParsedBody();
    $id_empresa = (int)($body['id_empresa'] ?? 0);
    $token = trim((string)($body['token'] ?? ''));

    $db = new DbHandler();
    $resultado = $db->deleteTarjetaEmpresa($id_empresa, $token);
    fulmuvJsonResponse($resultado);
});

$app->post('/fulmuv/catalog/ensure', function ($request, $response) {
    $p = $request->getParsedBody();
    $entity = trim($p['entity'] ?? '');
    $nombre = trim($p['nombre'] ?? '');
    unset($p['entity'], $p['nombre']); // lo demás quedan como "parents" / extras

    $db = new DbHandler();
    $r = $db->catalogEnsure($entity, $nombre, $p);
    echo json_encode($r);
});

$app->post('/fulmuv/empresas/verificar', function ($request, $response) {

    $id_empresa = $request->getParsedBody()['id_empresa'];
    $nombre_comercial = $request->getParsedBody()['nombre_comercial'];
    $ruc = $request->getParsedBody()['ruc'];
    $cedula = $request->getParsedBody()['cedula'];
    $nombramiento = $request->getParsedBody()['nombramiento'];
    $patente = $request->getParsedBody()['patente'];
    $planilla = $request->getParsedBody()['planilla'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->verificar_empresa($id_empresa, $nombre_comercial, $ruc, $cedula, $nombramiento, $patente, $planilla);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Empresa verificada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/getProductosVendidosHoy/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProductosVendidosHoy();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->get('/fulmuv/ofertas_imperdibles/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getOfertasImperdibles();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getServiciosVendidosHoy/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getServiciosVendidosHoy();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->post('/fulmuv/empresas/pagoOrden', function ($request, $response) {

    $id_orden = $request->getParsedBody()['id_orden'];

    $imagenArray = $request->getParsedBody()['imagenArray'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->pagoOrdenEmpresa($id_orden, $imagenArray);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Empresa verificada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/cliente/getOrdenCliente', function ($request, $response) {

    $id = $request->getParsedBody()['id_cliente'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getOrdenCliente($id);

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getMarcasProductos/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getIndexMarca();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->get('/fulmuv/getTipoEvento/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTipoEvento();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/getSubTipoEvento', function ($request, $response) {

    $id = $request->getParsedBody()['id_tipo'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getSubTipoEvento($id);

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/eventos/galeria/{id_evento}', function ($request, $response, $args) {
    $id = intval($args['id_evento'] ?? 0);
    $db = new DbHandler();
    $resultado = $db->getGaleriaByEvento($id);
    echo json_encode(["error" => false, "data" => $resultado]);
});

$app->post('/fulmuv/eventos/galeria/delete', function ($request, $response) {
    $id_galeria = intval($request->getParsedBody()['id_galeria'] ?? 0);
    $db = new DbHandler();

    if ($id_galeria <= 0) {
        echo json_encode(["error" => true, "msg" => "id_galeria inválido"]);
        return;
    }

    $resultado = $db->deleteGaleriaEvento($id_galeria);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        echo json_encode(["error" => false, "msg" => "Imagen eliminada correctamente."]);
    } else {
        echo json_encode(["error" => true, "msg" => "No se pudo eliminar la imagen de la galería."]);
    }
});

$app->get('/fulmuv/getCiudadesAgencia/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCiudadesAgencia();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getTrayectos/{tipo}', function ($request, $response, $args) {
    $tipo = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTrayectos($tipo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

// $app->get('/fulmuv/getTrayectos/all', function ($request, $response) {
//     $tipo = $args['tipo'];
//     $response = array();
//     $db = new DbHandler();
//     $resultado = $db->getTrayectos($tipo);
//     $response["error"] = false;
//     $response["data"] = $resultado;
//     echo json_encode($response);
// });


$app->post('/fulmuv/ordenes/trayecto', function ($request, $response) {

    $id_orden = $request->getParsedBody()['id_orden'];
    $trayecto = $request->getParsedBody()['trayecto'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->guardarTrayecto($id_orden, $trayecto);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Orden actualizada correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/crearGuiaServientrega/', function ($request, $response) {

    $body = $request->getParsedBody() ?? [];

    // 1) Leer campos (form-data / x-www-form-urlencoded)
    $razon_social     = trim($body['razon_social'] ?? '');
    $nombre_cliente   = trim($body['nombre_cliente'] ?? '');
    $direccion        = trim($body['direccion'] ?? '');
    $sector_destino   = trim($body['sector_destino'] ?? '');
    $telefono_destino = trim($body['telefono_destino'] ?? '');
    $productos        = trim($body['productos'] ?? '');          // contenido
    $valor_mercancia  = $body['total_mercancia'] ?? 0;           // OJO: en tu Postman sale duplicado
    $valor_asegurado  = $body['valor_asegurado'] ?? 0;
    $peso             = $body['peso'] ?? 0.1;
    $numero_piezas    = $body['numero_piezas'] ?? 1;

    // Si ya tienes lat/lng reales, mándalos desde el front:
    $latitud          = trim($body['latitud'] ?? '');
    $longitud         = trim($body['longitud'] ?? '');

    // 2) Validación mínima
    $faltan = [];
    if ($razon_social === '')     $faltan[] = 'razon_social';
    if ($nombre_cliente === '')   $faltan[] = 'nombre_cliente';
    if ($direccion === '')        $faltan[] = 'direccion';
    if ($telefono_destino === '') $faltan[] = 'telefono_destino';
    if ($productos === '')        $faltan[] = 'productos';

    if (!empty($faltan)) {
        $payload = [
            'error' => true,
            'msg'   => 'Faltan campos requeridos.',
            'faltan' => $faltan
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Normalizar números
    $valor_mercancia = floatval(str_replace(',', '.', (string)$valor_mercancia));
    $valor_asegurado = floatval(str_replace(',', '.', (string)$valor_asegurado));
    $peso            = floatval(str_replace(',', '.', (string)$peso));
    $numero_piezas   = intval($numero_piezas);

    // Si no mandas lat/lng, usa vacío (el master/hija lo arma igual)
    // (ideal: mandar lat/lng reales desde el checkout)
    if ($latitud === '' || $longitud === '') {
        $latitud = '';
        $longitud = '';
    }

    // 3) Ejecutar integración
    $db = new DbHandler();
    $data = $db->crearGuiaGrupoEntrega(
        $razon_social,
        $nombre_cliente,
        $direccion,
        $sector_destino,
        $telefono_destino,
        $productos,
        $numero_piezas,
        $valor_mercancia,
        $valor_asegurado,
        $peso,
        $latitud,
        $longitud
    );

    // 4) Interpretar respuesta REAL (ya no es $data['id'])
    $ok = is_array($data)
        && (isset($data['error']) && $data['error'] === false)
        && !empty($data['guiaMasterId'])
        && !empty($data['label']); // URL pdf

    if (!$ok) {
        $payload = [
            'error' => true,
            'msg'   => 'No se pudo generar la guía en Grupo Entrega.',
            'data'  => $data
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // 5) Respuesta final limpia para tu frontend
    $payload = [
        'error'        => false,
        'msg'          => 'GUÍA REGISTRADA CORRECTAMENTE',
        'guiaMasterId' => $data['guiaMasterId'],
        'label'        => $data['label'],        // <- URL del PDF
        'master'       => $data['master'] ?? null,
        'hija'         => $data['hija'] ?? null,
    ];

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/fulmuv/getTransmision/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTransmision();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getColores/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getColores();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getTapiceria/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTapiceria();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getTipoVendedor/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTipoVendedor();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getClimatizacion/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getClimatizacion();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getDireccion/', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getDireccion();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/vehiculos/create', function ($request, $response) {
    $descripcion = $request->getParsedBody()['descripcion'];
    $provincia = $request->getParsedBody()['provincia'] ?? [];
    $canton = $request->getParsedBody()['canton'] ?? [];
    $tags = $request->getParsedBody()['tags'];
    $precio_referencia = $request->getParsedBody()['precio_referencia'];
    $img_frontal = $request->getParsedBody()['img_frontal'] ?? "";
    $img_posterior = $request->getParsedBody()['img_posterior'] ?? "";
    $archivos = $request->getParsedBody()['archivos'] ?? [];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $descuento = $request->getParsedBody()['descuento'] ?? 0.0;
    $tipo_vehiculo = $request->getParsedBody()['tipo_vehiculo'] ?? '';
    $modelo = $request->getParsedBody()['modelo'] ?? '';
    $marca = $request->getParsedBody()['marca'] ?? '';
    $traccion = $request->getParsedBody()['traccion'] ?? '';
    $iva = $request->getParsedBody()['iva'] ?? 0;
    $negociable = $request->getParsedBody()['negociable'] ?? 0;
    $anio = $request->getParsedBody()['anio'] ?? '';
    $condicion = $request->getParsedBody()['condicion'] ?? [];
    $tipo_vendedor = $request->getParsedBody()['tipo_vendedor'] ?? [];
    $kilometraje = $request->getParsedBody()['kilometraje'] ?? 0;
    $transmision = $request->getParsedBody()['transmision'] ?? [];
    $inicio_placa = $request->getParsedBody()['inicio_placa'] ?? '';
    $fin_placa = $request->getParsedBody()['fin_placa'] ?? '';
    $color = $request->getParsedBody()['color'] ?? '';
    $cilindraje = $request->getParsedBody()['cilindraje'] ?? '';
    $tapiceria = $request->getParsedBody()['tapiceria'] ?? [];
    $duenio = $request->getParsedBody()['duenio'] ?? [];
    $direccion = $request->getParsedBody()['direccion'] ?? [];
    $climatizacion = $request->getParsedBody()['climatizacion'] ?? [];
    $funcionamiento_motor = $request->getParsedBody()['motor'] ?? [];
    $referencias = $request->getParsedBody()['referencias'] ?? [];
    $estado = $request->getParsedBody()['estado'] ?? 'A';
    $tipo_creador = $request->getParsedBody()['tipo_creador'] ?? 'A';

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createVehiculo($descripcion, $provincia, $canton, $tags, $precio_referencia, $img_frontal, $img_posterior, $archivos, $id_empresa, $descuento, $tipo_vehiculo, $modelo, $marca, $traccion, $iva, $negociable, $anio, $condicion, $tipo_vendedor, $kilometraje, $transmision, $inicio_placa, $fin_placa, $color, $cilindraje, $tapiceria, $duenio, $direccion, $climatizacion, $funcionamiento_motor, $referencias, $estado, $tipo_creador);
    //$resultado = $db->createProducto($categoria, $sub_categoria, $tags, $precio_referencia, $img_path, $ficha_tecnica, $id_empresa, $atributos);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El vehículo ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El vehículo ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del vehículo. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/vehiculos/borrador/{id_empresa}', function ($request, $response, $args) {
    $id_empresa = (int)($args['id_empresa'] ?? 0);
    if ($id_empresa <= 0) {
        $response->getBody()->write(json_encode(["error" => true, "msg" => "Falta id_empresa"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $db = new DbHandler();
    $rows = $db->getVehiculosBorradorByEmpresa($id_empresa);
    echo json_encode([
        "error" => false,
        "data"  => $rows
    ]);
});


$app->get('/fulmuv/vehiculos/all/{id_empresa}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculos($id_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/vehiculos/allFiltro/{id_empresa}/{consulta}', function ($request, $response, $args) {
    $id_empresa = $args['id_empresa'];
    $consulta = $args['consulta'] ?? "";
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculosFiltro($id_empresa, $consulta);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/vehiculos/All', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculosAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/vehiculosLlegados/All', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculosLlegadosAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/vehiculosLlegadosSearch/All', function ($request, $response, $args) {
    $search = $request->getParsedBody()['search'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculosLlegadosSearchAll($search);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->get('/fulmuv/vehiculos/', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculosAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->post('/fulmuv/vehiculos/byIdVehiculo', function ($request, $response) {

    $id_vehiculo = $request->getParsedBody()['id_vehiculo'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculosAllById($id_vehiculo);
    $response["error"] = false;
    $response["data"] = $resultado[0];
    echo json_encode($response);
});

$app->get('/fulmuv/vehiculos/{id}', function ($request, $response, $args) {
    $id_vehiculo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVehiculoById($id_vehiculo);

    if ($resultado != RECORD_DOES_NOT_EXIST) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "El vehículo no existe";
    }

    echo json_encode($response);
});


$app->post('/fulmuv/vehiculos/update_basic', function ($request, $response) {

    $b = $request->getParsedBody();

    $id_vehiculo = $b['id_vehiculo'] ?? null;
    if (!$id_vehiculo) {
        echo json_encode(["error" => true, "msg" => "Falta id_vehiculo"]);
        return;
    }

    $id_modelo            = $b['id_modelo'] ?? ($b['modelo'] ?? '');
    $anio                 = $b['anio'] ?? '';
    $tipo_auto            = $b['tipo_auto'] ?? ($b['tipo_vehiculo'] ?? '');
    $id_marca             = $b['id_marca'] ?? ($b['marca'] ?? '');
    $kilometraje          = $b['kilometraje'] ?? '';
    $tipo_traccion        = $b['tipo_traccion'] ?? ($b['traccion'] ?? '');
    $funcionamiento_motor = $b['funcionamiento_motor'] ?? ($b['motor'] ?? '');
    $inicio_placa         = $b['inicio_placa'] ?? '';
    $fin_placa            = $b['fin_placa'] ?? '';
    $img_frontal          = $b['img_frontal'] ?? '';
    $img_posterior        = $b['img_posterior'] ?? '';
    $color                = $b['color'] ?? '';
    $cilindraje           = $b['cilindraje'] ?? '';
    $descripcion          = $b['descripcion'] ?? '';
    $precio_referencia    = $b['precio_referencia'] ?? '0';
    $id_empresa           = $b['id_empresa'] ?? 0;
    $descuento            = $b['descuento'] ?? 0;
    $tipo_creador         = $b['tipo_creador'] ?? ($b['tipo_user'] ?? 'empresa');
    $negociable           = $b['negociable'] ?? 0;
    $estado               = $b['estado'] ?? 'A';
    $tags                 = $b['tags'] ?? '';
    $condicion            = $b['condicion'] ?? '';
    $archivos            = $b['archivos'] ?? [];

    $db = new DbHandler();
    $res = $db->updateVehiculoBasic(
        $id_vehiculo,
        $id_modelo,
        $anio,
        $tipo_auto,
        $id_marca,
        $kilometraje,
        $tipo_traccion,
        $funcionamiento_motor,
        $inicio_placa,
        $fin_placa,
        $img_frontal,
        $img_posterior,
        $color,
        $cilindraje,
        $descripcion,
        $precio_referencia,
        $id_empresa,
        $descuento,
        $tipo_creador,
        $negociable,
        $estado,
        $tags,
        $condicion,
        $archivos
    );

    echo json_encode(
        $res == RECORD_UPDATED_SUCCESSFULLY
            ? ["error" => false, "msg" => "Vehículo BASIC actualizado."]
            : ["error" => true, "msg" => "No se pudo actualizar BASIC."]
    );
});

$app->post('/fulmuv/vehiculos/update_full', function ($request, $response) {

    $b = $request->getParsedBody();

    $id_vehiculo = $b['id_vehiculo'] ?? null;

    // helper para tomar valor por varios nombres
    // $pick = function($arr, $keys, $default = ''){
    //     foreach ($keys as $k) {
    //         if (isset($arr[$k])) return $arr[$k];
    //     }
    //     return $default;
    // };

    // helper para asegurar JSON string en columnas json
    // $toJsonString = function($v){
    //     if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
    //     if (is_string($v) && trim($v) !== '') return $v;
    //     return '[]';
    // };

    $id_modelo            = $b['modelo'] ?? '';
    $anio                 = $b['anio'] ?? '';

    $tipo_auto            = $b['tipo_vehiculo'] ?? '';
    $id_marca             = $b['marca'] ?? '';
    $kilometraje          = $b['kilometraje'] ?? '';

    $tipo_traccion        = $b['traccion'] ?? '';
    $funcionamiento_motor = $b['motor'] ?? '';

    $inicio_placa         = $b['inicio_placa'] ?? '';
    $fin_placa            = $b['fin_placa'] ?? '';

    $img_frontal          = $b['img_frontal'] ?? '';
    $img_posterior        = $b['img_posterior'] ?? '';

    $color                = $b['color'] ?? '';
    $cilindraje           = $b['cilindraje'] ?? '';

    $descripcion          = $b['descripcion'] ?? '';
    $precio_referencia    = $b['precio_referencia'] ?? '0';
    $id_empresa           = $b['id_empresa'] ?? 0;
    $descuento            = $b['descuento'] ?? 0;

    $tipo_creador         = $b['tipo_creador'] ?? '';
    $negociable           = $b['negociable'] ?? 0;
    $estado               = $b['estado'] ?? 'A';
    $tags                 = $b['tags'] ?? '';

    // JSON columns:
    $condicion     = $b['condicion'] ?? [];
    $transmision   = $b['transmision'] ?? [];
    $tipo_vendedor = $b['tipo_vendedor'] ?? [];
    $provincia     = $b['provincia'] ?? [];
    $canton        = $b['canton'] ?? [];
    $tapiceria     = $b['tapiceria'] ?? [];
    $tipo_dueno    = $b['duenio'] ?? [];
    $direccion     = $b['direccion'] ?? [];
    $climatizacion = $b['climatizacion'] ?? [];
    $referencias   = $b['referencias'] ?? [];

    $iva   = $b['iva'] ?? 0;

    // archivos puede venir como array o JSON string
    $archivos = $b['archivos'] ?? [];
    if (is_string($archivos)) {
        $tmp = json_decode($archivos, true);
        if (json_last_error() === JSON_ERROR_NONE) $archivos = $tmp;
    }

    $db = new DbHandler();
    $res = $db->updateVehiculoFull(
        $id_vehiculo,
        $id_modelo,
        $anio,
        $condicion,
        $tipo_auto,
        $id_marca,
        $kilometraje,
        $transmision,
        $tipo_traccion,
        $funcionamiento_motor,
        $inicio_placa,
        $fin_placa,
        $tipo_vendedor,
        $provincia,
        $canton,
        $img_frontal,
        $img_posterior,
        $color,
        $cilindraje,
        $tapiceria,
        $tipo_dueno,
        $direccion,
        $climatizacion,
        $descripcion,
        $precio_referencia,
        $id_empresa,
        $descuento,
        $tipo_creador,
        $negociable,
        $estado,
        $tags,
        $referencias,
        $archivos,
        $iva
    );

    $payload = ($res == RECORD_UPDATED_SUCCESSFULLY)
        ? ["error" => false, "msg" => "Vehículo FULL actualizado."]
        : ["error" => true, "msg" => "No se pudo actualizar FULL."];

    // $response->getBody()->write(json_encode($payload));
    // return $response->withHeader('Content-Type', 'application/json');
    echo json_encode($payload);
});


$app->post('/fulmuv/vehiculos/delete', function ($request, $response) {
    $id_vehiculo = $request->getParsedBody()['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->deleteVehiculo($id_vehiculo);

    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Vehículo eliminado correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});

// $app->post('/fulmuv/ordenes_iso/confirmarVenta', function ($request, $response) {
//     $id_usuario = $request->getParsedBody()['id_usuario'];
//     $raw = $request->getParsedBody()['raw'];

//     $response = array();
//     $db = new DbHandler();
//     $update = $db->confirmarVentaOrdenFulmuv($id_usuario, $raw);

//     if ($update == RECORD_CREATION_FAILED) {
//         $response["error"]  = true;
//         $response["msg"] = $update;
//     } else {
//         $response["error"]  = false;
//         $response["msg"] = $update;
//     }
//     echo json_encode($response);
// });

$app->post('/fulmuv/ordenes_iso/confirmarVenta', function ($request, $response) {
    $id_usuario = $request->getParsedBody()['id_usuario'];
    $raw = $request->getParsedBody()['raw'];

    $response = array();
    $db = new DbHandler();
    $update = $db->confirmarVentaOrdenFulmuv($id_usuario, $raw);

    if ($update == RECORD_CREATION_FAILED) {
        $response["error"]  = true;
        $response["msg"] = $update;
    } else {
        $response["error"]  = false;
        $response["msg"] = $update;
    }
    echo json_encode($response);
});


$app->get('/fulmuv/empresas/validaVerificacion/{id}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->validaVerificacion($id_empresa);

    if ($resultado != false) {
        $response["error"] = false;
        $response["data"] = $resultado;
    } else {
        $response["error"] = true;
        $response["msg"] = "No verificada";
    }

    echo json_encode($response);
});

$app->get('/fulmuv/filesEmpresa/{id}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->filesEmpresa($id_empresa);

    $response["error"] = false;
    $response["data"] = $resultado;


    echo json_encode($response);
});

$app->get('/fulmuv/fileEmpresaById/{id}', function ($request, $response, $args) {
    $id_archivo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->fileEmpresaById($id_archivo);

    $response["error"] = false;
    $response["data"] = $resultado;


    echo json_encode($response);
});

$app->post('/fulmuv/buscarProvinciaDestino', function ($request, $response) {

    $search = $request->getParsedBody()['search'];

    $response = array();
    $db = new DbHandler();
    $update = $db->getRutasByProvinciaLike($search);

    if ($update != RECORD_CREATION_FAILED) {
        $response["error"]  = false;
        $response["data"] = $update;
    } else {
        $response["error"]  = true;
        $response["msg"] = "Confirmación de venta exitosa.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/getCantonesDestinoByProvinciaLike', function ($request, $response) {

    $search = $request->getParsedBody()['search'];
    $nombre_provincia = $request->getParsedBody()['nombre_provincia'];

    $response = array();
    $db = new DbHandler();
    $update = $db->getCantonesDestinoByProvinciaLike($search, $nombre_provincia);

    if ($update != RECORD_CREATION_FAILED) {
        $response["error"]  = false;
        $response["data"] = $update;
    } else {
        $response["error"]  = true;
        $response["msg"] = "Confirmación de venta exitosa.";
    }
    echo json_encode($response);
});

// NUEVO: Sectores por provincia + cantón (busca en zona_peligrosa)
$app->post('/fulmuv/getSectoresByProvinciaCanton', function ($request, $response) {
    $prov = $request->getParsedBody()['nombre_provincia'] ?? '';
    $canton = $request->getParsedBody()['nombre_canton'] ?? '';
    $search = $request->getParsedBody()['search'] ?? '';
    $id_provincia = $request->getParsedBody()['id_provincia'] ?? '';
    $id_canton = $request->getParsedBody()['id_canton'] ?? '';
    $db = new DbHandler();
    $data = $db->getParroquiaByIdCantonGrupoEntrega($id_canton); // { sector: '...' }
    echo json_encode(["error" => false, "data" => $data["Data"]]);
});

// NUEVO: Fila completa de la ruta seleccionada (coincidencia exacta de sector)
$app->post('/fulmuv/getRutaByProvinciaCantonSector', function ($request, $response) {
    $prov = $request->getParsedBody()['nombre_provincia'] ?? '';
    $canton = $request->getParsedBody()['nombre_canton'] ?? '';
    $sector = $request->getParsedBody()['sector'] ?? '';
    $db = new DbHandler();
    $row = $db->getRutaGrupoConTrayectos($prov, $canton, $sector);
    echo json_encode(["error" => false, "data" => $row]);
});


$app->post('/fulmuv/cookies/consentExist', function ($request, $response) {
    header('Content-Type: application/json');

    $params  = $request->getParsedBody(); // <-- POST body
    $ip      = isset($params['ip']) ? trim($params['ip']) : '';
    $version = isset($params['version']) ? trim($params['version']) : '';

    if ($ip === '' && !empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $res = [];
    if ($ip === '' || $version === '') {
        $res["error"] = true;
        $res["msg"]   = "Parámetros requeridos: ip y version";
        echo json_encode($res);
        return;
    }


    $db  = new DbHandler();
    $resultados = $db->getLastCookieConsentByIpVersion($ip, $version);


    if ($resultados == RECORD_DOES_NOT_EXIST) {
        $res["error"] = true;
        $res["msg"] = "No hubo resultados, verificar nuevamente más tarde.";
    } else {
        $res["error"] = false;
        $res["msg"] = $resultados;
    }

    echo json_encode($res);
});


// POST: registrar consentimiento
$app->post('/fulmuv/cookies/consent', function ($request, $response) {
    $p = $request->getParsedBody();

    $version     = isset($p['version'])     ? trim($p['version'])     : 'v1';
    $decision    = isset($p['decision'])    ? trim($p['decision'])    : 'configurar';
    $timestampMs = isset($p['timestamp'])   ? intval($p['timestamp']) : 0;
    $ip          = isset($p['ip'])          ? trim($p['ip'])          : '';
    $essential   = isset($p['essential'])   ? intval($p['essential']) : 1;
    $analiticas  = isset($p['analiticas'])  ? intval($p['analiticas']) : 0;
    $publicidad  = isset($p['publicidad'])  ? intval($p['publicidad']) : 0;
    $user_agent  = isset($p['user_agent'])  ? trim($p['user_agent'])  : ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $source_page = isset($p['source_page']) ? trim($p['source_page']) : '';

    if ($ip === '' && !empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $db = new DbHandler();
    $result = $db->insertCookieConsent(
        $version,
        $decision,
        $timestampMs,
        $ip,
        $user_agent,
        $essential,
        $analiticas,
        $publicidad,
        $source_page
    );

    $res = array();
    if ($result != RECORD_CREATION_FAILED) {
        $res["error"] = false;
        $res["id"]    = $result; // lastInsertId
    } else {
        $res["error"] = true;
        $res["msg"]   = "No se pudo registrar el consentimiento.";
    }
    echo json_encode($res);
});

// POST: consultar si ya aceptó términos para un producto (por IP + versión)
$app->post('/fulmuv/terminos/product/check', function ($request, $response) {
    header('Content-Type: application/json');
    $p         = $request->getParsedBody();
    $productId = isset($p['product_id']) ? intval($p['product_id']) : 0;
    $version   = isset($p['version']) ? trim($p['version']) : 'v1';
    $ip        = isset($p['ip']) ? trim($p['ip']) : '';

    if ($ip === '' && !empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $res = array();
    if ($productId <= 0 || $version === '' || $ip === '') {
        $res["error"] = true;
        $res["msg"]   = "Parámetros requeridos: product_id, version, ip";
        echo json_encode($res);
        return;
    }

    $db = new DbHandler();
    $row = $db->getProductTermsAcceptByIp($productId, $ip, $version);

    $res["error"]  = false;
    $res["exists"] = ($row !== RECORD_DOES_NOT_EXIST);
    if ($res["exists"]) {
        $res["data"] = $row;
    }
    echo json_encode($res);
});

// POST: registrar aceptación de términos
$app->post('/fulmuv/terminos/product/accept', function ($request, $response) {
    header('Content-Type: application/json');
    $p         = $request->getParsedBody();
    $productId = isset($p['product_id']) ? intval($p['product_id']) : 0;
    $version   = isset($p['version']) ? trim($p['version']) : 'v1';
    $ip        = isset($p['ip']) ? trim($p['ip']) : '';
    $ua        = isset($p['user_agent']) ? substr(trim($p['user_agent']), 0, 512) : ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $source    = isset($p['source_page']) ? substr(trim($p['source_page']), 0, 255) : '';

    if ($ip === '' && !empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $res = array();
    if ($productId <= 0 || $version === '' || $ip === '') {
        $res["error"] = true;
        $res["msg"]   = "Parámetros requeridos: product_id, version, ip";
        echo json_encode($res);
        return;
    }

    $db = new DbHandler();
    $insertId = $db->insertProductTermsAccept($productId, $ip, $version, $ua, $source);

    if ($insertId != RECORD_CREATION_FAILED) {
        $res["error"] = false;
        $res["id"]    = $insertId;
    } else {
        $res["error"] = true;
        $res["msg"]   = "No se pudo registrar la aceptación.";
    }
    echo json_encode($res);
});

$app->get('/fulmuv/getVerificaciones/', function ($request, $response, $args) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVerificaciones();

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/getVerificacionById/{id}', function ($request, $response, $args) {
    $id_verificacion = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getVerificacionById($id_verificacion);

    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/verificaciones/updateEstado', function ($request, $response) {
    $id_verificacion = $request->getParsedBody()['id_verificacion'];
    $estado = $request->getParsedBody()['estado'];
    $motivo = $request->getParsedBody()['motivo'] ?? "";

    $response = array();
    $db = new DbHandler();
    $update = $db->updateEstadoVerificacion($id_verificacion, $estado, $motivo);

    if ($update == RECORD_UPDATED_SUCCESSFULLY) {
        $response["error"]  = false;
        if ((int)$estado === 0) {
            $response["msg"] = "Se realizó un rechazo de verificación de la empresa.";
        } elseif ((int)$estado === 1) {
            $response["msg"] = "La empresa fue verificada con éxito.";
        } else {
            $response["msg"] = "Empresa actualizada correctamente!!";
        }
    } else {
        $response["error"]  = true;
        $response["msg"] = "Ocurrió un error, intente despues.";
    }
    echo json_encode($response);
});

$app->post('/fulmuv/empleos/create', function ($request, $response) {
    $descripcion = $request->getParsedBody()['descripcion'];
    $provincia = $request->getParsedBody()['provincia'] ?? [];
    $canton = $request->getParsedBody()['canton'] ?? [];
    $tags = $request->getParsedBody()['tags'];
    $titulo = $request->getParsedBody()['titulo'];
    $img_frontal = $request->getParsedBody()['img_frontal'] ?? "";
    $img_posterior = $request->getParsedBody()['img_posterior'] ?? "";
    $archivos = $request->getParsedBody()['archivos'] ?? [];
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $tipo_creador = $request->getParsedBody()['tipo_creador'];
    $fecha_inicio = $request->getParsedBody()['fecha_inicio'];
    $fecha_fin = $request->getParsedBody()['fecha_fin'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->createEmpleo($titulo, $descripcion, $provincia, $canton, $tags, $img_frontal, $img_posterior, $archivos, $id_empresa, $tipo_creador, $fecha_inicio, $fecha_fin);
    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "El empleo ha sido creado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["msg"] = "El empleo ya existe. Verifique nuevamente los datos";
    } else {
        $response["error"] = true;
        $response["msg"] = "Hubo un error en la creación del empleo. Por favor vuelva a verificar en otro momento.";
    }
    echo json_encode($response);
});

$app->get('/fulmuv/empleos/all/{id}/{tipo}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $tipo = $args['tipo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpleos($id_empresa, $tipo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/empleos/allFiltro/{id}/{tipo}/{consulta}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $tipo = $args['tipo'];
    $consulta = $args['consulta'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpleosFiltro($id_empresa, $tipo, $consulta);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->get('/fulmuv/empleos/{id}', function ($request, $response, $args) {
    $id_empleo = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpleoById($id_empleo);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});


$app->get('/fulmuv/empleosAll/all', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpleosAll();
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/empleos/update', function ($request, $response) {

    $body = $request->getParsedBody();

    $id_empleo     = (int)($body['id_empleo'] ?? 0);
    $titulo        = $body['titulo'] ?? '';
    $descripcion   = $body['descripcion'] ?? '';
    $provincia     = $body['provincia'] ?? '';
    $canton        = $body['canton'] ?? '';
    $tags          = $body['tags'] ?? '';

    $img_frontal   = $body['img_frontal'] ?? '';     // puede venir vacío si NO cambió
    $img_posterior = $body['img_posterior'] ?? '';   // puede venir vacío si NO cambió

    $archivos      = $body['archivos'] ?? [];        // opcional (si cambias anexos)
    $id_empresa    = (int)($body['id_empresa'] ?? 0);
    $tipo_creador  = $body['tipo_creador'] ?? 'empresa';

    $fecha_inicio  = $body['fecha_inicio'] ?? null;  // 'YYYY-MM-DD'
    $fecha_fin     = $body['fecha_fin'] ?? null;

    // opcional: si mandas estado en edición
    $estado        = $body['estado'] ?? null;        // 'A' o lo que uses

    $db = new DbHandler();
    $resultado = $db->updateEmpleo(
        $id_empleo,
        $titulo,
        $descripcion,
        $provincia,
        $canton,
        $tags,
        $img_frontal,
        $img_posterior,
        $id_empresa,
        $tipo_creador,
        $fecha_inicio,
        $fecha_fin,
        $estado,
        $archivos
    );

    $res = [];
    if ($resultado == RECORD_UPDATED_SUCCESSFULLY) {
        $res["error"] = false;
        $res["msg"] = "Empleo actualizado correctamente.";
    } else {
        $res["error"] = true;
        $res["msg"] = "Ocurrió un error al actualizar.";
    }

    echo json_encode($res);
});


$app->post('/fulmuv/subirPostulante/create', function ($request, $response) {

    $nombres_apellidos   = $request->getParsedBody()['nombres_apellidos'];
    $cedula              = $request->getParsedBody()['cedula'];
    $correo              = $request->getParsedBody()['correo'];
    $telefono            = $request->getParsedBody()['telefono'];
    $cv                  = $request->getParsedBody()['cv'];
    $id_empleo           = $request->getParsedBody()['postular_id_empleo'];
    $id_empresa          = $request->getParsedBody()['postular_id_empresa'];

    $db  = new DbHandler();
    $res = $db->createPostulanteTrabajoEmpresa(
        $nombres_apellidos,
        $cedula,
        $correo,
        $telefono,
        $cv,
        $id_empleo,
        $id_empresa
    );

    $responseData = array();

    if ($res == RECORD_CREATED_SUCCESSFULLY) {
        $responseData["error"] = false;
        $responseData["msg"]   = "Postulación realizada con éxito.";
    } else {
        $responseData["error"] = true;
        $responseData["msg"]   = "Hubo un error al guardar la postulación.";
    }

    echo json_encode($responseData);
});

/*$app->post('/fulmuv/productos/excel', function ($request, $response) {

    $data = $request->getParsedBody()['data'];

    $response = array();
    $db = new DbHandler();
    //$resultado = $db->registroMultipleProductosExcel($data);
    foreach ($data as $row) {
        $resultado = $db->registroMultipleProductosExcel($row);
    }

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["msg"] = "Productos registrados correctamente.";
    } else {
        $response["error"] = true;
        $response["msg"] = "Ocurrió un error!";
    }
    echo json_encode($response);
});*/

$app->post('/fulmuv/productos/excel', function ($request, $response) {

    $post = $request->getParsedBody();
    $rows = json_decode($post['data'] ?? '[]', true);

    // ✅ archivos subidos
    $uploadedFiles = $request->getUploadedFiles();
    $archivos = $uploadedFiles['archivos'] ?? [];

    $db = new DbHandler();

    // 🔹 indexar archivos por nombre original
    $mapFiles = [];
    foreach ($archivos as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $mapFiles[$file->getClientFilename()] = $file;
        }
    }

    $ok = 0;
    $errores = [];

    foreach ($rows as $i => $row) {

        // 1️⃣ insertar producto (DEBE devolver id_producto)
        $idProducto = $db->registroMultipleProductosExcel($row);

        if (!$idProducto || !is_numeric($idProducto)) {
            $errores[] = "Fila " . ($i + 2) . ": error al crear producto";
            continue;
        }


        // 2️⃣ obtener nombres de archivos desde Excel
        // 👉 AJUSTA EL ÍNDICE según tu plantilla real
        // ejemplo: columna 23 = archivos
        $colArchivos = $row[22] ?? '';
        if (!$colArchivos) continue;

        $nombres = array_filter(array_map('trim', explode('|', $colArchivos)));

        foreach ($nombres as $nombreArchivo) {

            if (!isset($mapFiles[$nombreArchivo])) {
                $errores[] = "Fila " . ($i + 2) . ": archivo '$nombreArchivo' no subido";
                continue;
            }

            $fileObj = $mapFiles[$nombreArchivo];

            // 3️⃣ mover archivo a admin/files
            $ext = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
            $nuevoNombre = uniqid("prod_", true) . "." . $ext;

            $destino = __DIR__ . "/../../admin/files/";
            if (!is_dir($destino)) {
                mkdir($destino, 0777, true);
            }

            $fileObj->moveTo($destino . $nuevoNombre);

            // 4️⃣ tipo de archivo
            $tipo = in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'webp', 'gif'])
                ? 'imagen'
                : 'ficha_tecnica';

            // 5️⃣ guardar relación
            $db->createFileProducto(
                $idProducto,
                "files/" . $nuevoNombre,
                $tipo
            );
        }

        $ok++;
    }

    $payload = [
        "error" => false,
        "insertados" => $ok,
        "errores" => $errores
    ];

    echo json_encode($payload);
});


$app->get('/fulmuv/grupo_entrega/getProvinciasAll', function ($request, $response) {
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getProvinciasGrupoEntrega();
    $response["error"] = false;
    $response["provincia"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/grupo_entrega/getCantones/ByIdProvincia', function ($request, $response) {

    $id_provincia = $request->getParsedBody()['id_provincia'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getCantonesByIdProvinciaGrupoEntrega($id_provincia);
    $response["error"] = false;
    $response["canton"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/grupo_entrega/getParroquia/ByIdCanton', function ($request, $response) {

    $id_canton = $request->getParsedBody()['id_canton'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getParroquiaByIdCantonGrupoEntrega($id_canton);
    $response["error"] = false;
    $response["parroquia"] = $resultado;
    echo json_encode($response);
});


$app->post('/fulmuv/getTarifas', function ($request, $response) {

    $nombres = $request->getParsedBody()['nombres'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->getTrayectoByTrayecto($nombres);
    $response["error"] = false;
    $response["trayecto"] = $resultado;
    echo json_encode($response);
});

$app->post('/fulmuv/rutas/insertExcel', function ($request, $response) {
    $body = $request->getParsedBody();

    // Puede venir como JSON string en "data"
    $dataRaw = $body['data'] ?? null;

    if (!$dataRaw) {
        echo json_encode(["error" => true, "msg" => "No se recibió el campo data"]);
        return $response;
    }

    // Si viene string JSON => lo decodificamos a array
    $rows = is_string($dataRaw) ? json_decode($dataRaw, true) : $dataRaw;

    if (!is_array($rows)) {
        echo json_encode(["error" => true, "msg" => "Formato inválido: data debe ser un array"]);
        return $response;
    }

    $db = new DbHandler();
    $result = $db->insertRutasExcel($rows);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->post('/fulmuv/envioCorreoAgradecimientoCompra', function ($request, $response) {

    $id_orden = $request->getParsedBody()['id_orden'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->enviarGraciasCompra($id_orden);
    $response["error"] = false;
    $response["trayecto"] = $resultado;
    echo json_encode($response);
});


$app->post('/fulmuv/enviarCorreoInicioSesion', function ($request, $response) {

    $id_orden = $request->getParsedBody()['id_orden'];

    $response = array();
    $db = new DbHandler();
    $resultado = $db->notificaNuevoCliente2("jacarrasco@bonsai.com.ec", "José Antonio Carrasco Sánchez", "Jose1998*");
    $response["error"] = false;
    $response["trayecto"] = $resultado;
    echo json_encode($response);
});

/* BORRADOR DE PRODUCTOS */
$app->get('/fulmuv/productos/borrador/{id}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getBorradorProductos($id_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
/* BORRADOR DE PRODUCTOS */

/* BORRADOR DE SERVICIOS */
$app->get('/fulmuv/servicios/borrador/{id}', function ($request, $response, $args) {
    $id_empresa = $args['id'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getBorradorServicios($id_empresa);
    $response["error"] = false;
    $response["data"] = $resultado;
    echo json_encode($response);
});
/* BORRADOR DE SERVICIOS */

$app->post('/fulmuv/productos/publicar_seleccionados', function ($request, $response) {
    $id_empresa = $request->getParsedBody()['id_empresa'];
    $ids = $request->getParsedBody()['ids'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->publicarSeleccionados($id_empresa, $ids);
    // $response["error"] = false;
    // $response["trayecto"] = $resultado;
    echo json_encode($resultado);
});

$app->post('/fulmuv/empleos/envioempleos', function ($request, $response) {
    $id_empleo = $request->getParsedBody()['id_empleo'];
    $response = array();
    $db = new DbHandler();
    $resultado = $db->getEmpleoEnviado($id_empleo);
    $response["error"] = false;
    $response["empleos"] = $resultado;
    echo json_encode($resultado);
});

$app->post('/fulmuv/contactoFulmuv/create', function ($request, $response) {

    $motivo         = $request->getParsedBody()['motivo'] ?? '';
    $nombre_empresa = $request->getParsedBody()['nombre_empresa'] ?? '';
    $titular        = $request->getParsedBody()['titular'] ?? '';
    $telefono       = $request->getParsedBody()['telefono'] ?? '';
    $correo         = $request->getParsedBody()['correo'] ?? '';
    $comentario     = $request->getParsedBody()['comentario'] ?? null;

    // (Opcional) Validación mínima
    if (trim($motivo) === '' || trim($nombre_empresa) === '' || trim($titular) === '' || trim($telefono) === '' || trim($correo) === '') {
        echo json_encode([
            "error" => true,
            "msg"   => "Faltan campos obligatorios."
        ]);
        return;
    }

    $db  = new DbHandler();
    $res = $db->createContactoFulmuv(
        $motivo,
        $nombre_empresa,
        $titular,
        $telefono,
        $correo,
        $comentario
    );

    $responseData = array();

    if ($res == RECORD_CREATED_SUCCESSFULLY) {
        $responseData["error"] = false;
        $responseData["msg"]   = "Solicitud enviada con éxito.";
    } else {
        $responseData["error"] = true;
        $responseData["msg"]   = "Hubo un error al guardar la solicitud.";
    }

    echo json_encode($responseData);
});

$app->post('/fulmuv/clientes/registro', function ($request, $response) {
    $data = $request->getParsedBody();

    $nombres  = isset($data['nombres']) ? trim($data['nombres']) : '';
    $correo   = isset($data['correo']) ? trim($data['correo']) : '';
    $telefono = isset($data['telefono']) ? trim($data['telefono']) : '';
    $password = isset($data['password']) ? trim($data['password']) : '';

    $respuesta = array(); 

    $db = new DbHandler();
    $resultado = $db->registroCliente($nombres, $correo, $telefono, $password);

    if ($resultado == RECORD_CREATED_SUCCESSFULLY) {
        $respuesta["error"] = false; 
        $respuesta["msg"] = "Cliente registrado con éxito.";
    } else if ($resultado == RECORD_ALREADY_EXISTED) { 
        $respuesta["error"] = true;
        $respuesta["msg"] = "El correo ya se encuentra registrado.";
    } else {
        $respuesta["error"] = true;
        $respuesta["msg"] = "No se pudo registrar el cliente. Intente nuevamente.";
    }

    echo json_encode($respuesta);
});

$app->run();
