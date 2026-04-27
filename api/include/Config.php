<?php

define("DB_USERNAME", "induwagen");
define("DB_PASSWORD", "Bonsai!2022");
define("DB_HOST", "18.191.120.236");
define("DB_NAME", "fulmuv");
 
define('REQUIRED_PAYMENT', - 2);
define('RECORD_CREATION_FAILED', - 1);
define('RECORD_CREATED_SUCCESSFULLY', 0);
define('RECORD_ALREADY_EXISTED', 2);
define('RECORD_DOES_NOT_EXIST', 3);
define('OPERATION_COMPLETED', 4);
define('ACCESS_DENIED', 5);
define('RECORD_DOES_NOT_APPLY', 6);
define('OPERATION_FULL', 7);  
define('RECORD_UPDATED_SUCCESSFULLY', 8);
define('RECORD_UPDATED_FAILED', 9);
define('USER_ALREADY_EXISTED', 10);
define('USER_CEDULA_ALREADY_EXISTED', 11);

// PRODUCCIÓN — FULMUV-PR-EC-SERVER / rtRERKqYpSDYfSKwL3xdn6XpgC5xW7 / https://ccapi.paymentez.com/v2/
// define('server_application_code', 'FULMUV-PR-EC-SERVER');
// define('server_app_key', 'rtRERKqYpSDYfSKwL3xdn6XpgC5xW7');
// define('paymentezURL', 'https://ccapi.paymentez.com/v2/');

// PRUEBAS (STG)
define('server_application_code', 'TESTNUVEISTG-EC-SERVER');
define('server_app_key', 'JSQFl89yPhJrUPhPN03yolvCbI3FRR');
define('paymentezURL', 'https://ccapi-stg.paymentez.com/v2/');

define('user_servientrega', "changethemove.sas");
define('password_servientrega', "123456");

?>
