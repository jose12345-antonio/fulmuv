<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
ini_set("display_errors", 0);
session_start();
session_cache_limiter('private_no_expire');
date_default_timezone_set('America/Guayaquil');

if (!(isset($_SESSION["id_usuario"]) && (
    (isset($_SESSION["empresa_auth"]) && $_SESSION["empresa_auth"] === true) ||
    !empty($_SESSION["id_empresa"])
))) {
    echo "<script>window.location.href='login.php'</script>";
    exit();
}

$correo           = $_SESSION["correo"]           ?? '';
$id_usuario       = $_SESSION["id_usuario"]       ?? '';
$nombres          = $_SESSION["nombres"]          ?? '';
$apellidos        = $_SESSION["apellidos"]        ?? '';
$username         = $_SESSION["username"]         ?? '';
$rol_id           = $_SESSION["rol_id"]           ?? '';
$imagen           = $_SESSION["imagen"]           ?? '';
$imagen_fallback  = "../img/FULMUV-LOGO-60X60.png";
$imagen_principal_src = trim((string)$imagen) !== '' ? $imagen : $imagen_fallback;
$nombre_rol_user  = $_SESSION["nombre_rol_user"]  ?? '';
$permisos         = $_SESSION["permisos"]         ?? [];
$id_empresa       = $_SESSION["id_empresa"]       ?? '';
$dashboard        = $_SESSION["dashboard"]        ?? 'home.php';
$membresia        = $_SESSION["membresia"]        ?? [];
$tipo_user        = $_SESSION["tipo_user"]        ?? 'empresa';
$tipo_user = ($tipo_user === "sucursal" || $tipo_user === 3 || $tipo_user === "3") ? "sucursal" : "empresa";

if (!empty($permisos["error"])) {
    echo "<script>window.location.href='login.php'</script>";
    exit();
}

$normalizarMembresia = function ($n) {
    $n = trim((string)$n);
    $l = function_exists('mb_strtolower') ? mb_strtolower($n, 'UTF-8') : strtolower($n);
    $a = @iconv('UTF-8', 'ASCII//TRANSLIT', $l);
    return preg_replace('/[^a-z0-9]+/', '', $a !== false ? $a : $l);
};

$membresia_nombre    = $membresia["nombre"] ?? "";
$mem_norm            = $normalizarMembresia($membresia_nombre);
$es_fulmuv           = $mem_norm === "fulmuv";
$es_onemuv           = $mem_norm === "onemuv";
$es_basicmuv         = $mem_norm === "basicmuv";
$es_sucursal         = $tipo_user === "sucursal";

$visible             = ($es_fulmuv && !$es_sucursal) ? "" : "disabled";
$visible_upgrade     = $es_sucursal ? "disabled" : "";
$visible_productos   = ($es_basicmuv || $es_sucursal) ? "disabled" : "";
$visible_veh_ev      = ($es_basicmuv || $es_sucursal) ? "disabled" : "";
$visible_masiva      = ($es_fulmuv  && !$es_sucursal) ? "" : "disabled";
$visible_ordenes     = ($es_onemuv  || $es_basicmuv)  ? "disabled" : "";

$nombre_display = trim($nombres . ' ' . $apellidos) ?: $username;
$inicial = mb_strtoupper(mb_substr($nombre_display, 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="es" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#00686f">
    <link rel="shortcut icon" type="image/x-icon" href="../img/FULMUV-LOGO-60X60.png">
    <link rel="manifest" href="../theme/public/assets/img/favicons/manifest.json">

    <!-- Inicializar tema ANTES del render (evita flash) -->
    <script src="../theme/public/assets/js/config.js?v=3"></script>
    <script src="../theme/public/vendors/simplebar/simplebar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Vendors -->
    <link href="../theme/public/vendors/simplebar/simplebar.min.css" rel="stylesheet">
    <link href="../theme/public/vendors/leaflet/leaflet.css" rel="stylesheet">
    <link href="../theme/public/vendors/leaflet.markercluster/MarkerCluster.css" rel="stylesheet">
    <link href="../theme/public/vendors/leaflet.markercluster/MarkerCluster.Default.css" rel="stylesheet">
    <link href="../theme/public/vendors/flatpickr/flatpickr.min.css" rel="stylesheet">
    <link href="../theme/public/assets/css/theme-rtl.css" rel="stylesheet" id="style-rtl">
    <link href="../theme/public/assets/css/theme.css" rel="stylesheet" id="style-default">
    <link href="../theme/public/assets/css/user-rtl.css" rel="stylesheet" id="user-style-rtl">
    <link href="../theme/public/assets/css/user.css" rel="stylesheet" id="user-style-default">
    <link href="../theme/public/vendors/select2/select2.min.css" rel="stylesheet">
    <link href="../theme/public/vendors/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="../theme/public/vendors/dropzone/dropzone.css" rel="stylesheet">
    <link href="../theme/public/vendors/choices/choices.min.css" rel="stylesheet">
    <link href="../theme/public/vendors/sweetalert/sweetalert.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <!-- RTL toggle -->
    <script>
        (function() {
            var rtl = JSON.parse(localStorage.getItem('isRTL'));
            if (rtl) {
                document.getElementById('style-default')?.setAttribute('disabled', true);
                document.getElementById('user-style-default')?.setAttribute('disabled', true);
                document.querySelector('html').setAttribute('dir', 'rtl');
            } else {
                document.getElementById('style-rtl')?.setAttribute('disabled', true);
                document.getElementById('user-style-rtl')?.setAttribute('disabled', true);
            }
        })();
    </script>
    <script src="js/alerts.js"></script>

    <style>
        /* ================================================================
   DESIGN SYSTEM — FULMUV PANEL 2025
   ================================================================ */
        :root {
            /* Colores FULMUV */
            --fmv-green: #00686f;
            --fmv-green-hover: #00797f;
            --fmv-green-dark: #004d52;
            --fmv-green-light: rgba(0, 104, 111, .12);
            --fmv-green-ultra: rgba(0, 104, 111, .06);

            /* Sidebar — siempre oscuro (identidad de marca) */
            --sb-bg: #0d1b2a;
            --sb-bg-hover: #14263a;
            --sb-bg-active: #00686f;
            --sb-text: rgba(255, 255, 255, .72);
            --sb-text-active: #ffffff;
            --sb-text-muted: rgba(255, 255, 255, .38);
            --sb-border: rgba(255, 255, 255, .07);
            --sb-icon-muted: rgba(255, 255, 255, .45);
            --sb-width: 268px;
            --sb-width-collapsed: 62px;

            /* Topbar */
            --tb-bg: #ffffff;
            --tb-border: #e8ecf0;
            --tb-shadow: 0 1px 0 #e8ecf0, 0 4px 16px rgba(15, 23, 42, .04);
            --tb-height: 64px;
            --tb-text: #0f172a;
            --tb-muted: #64748b;

            /* Contenido */
            --page-bg: #f1f5f9;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --card-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
            --card-radius: 14px;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --divider: #e2e8f0;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        /* Dark mode overrides */
        html[data-bs-theme="dark"] {
            --sb-bg: #060e16;
            --sb-bg-hover: #0d1b2a;
            --tb-bg: #0d1b2a;
            --tb-border: rgba(255, 255, 255, .07);
            --tb-shadow: 0 1px 0 rgba(255, 255, 255, .06);
            --tb-text: #f1f5f9;
            --tb-muted: #94a3b8;
            --page-bg: #060e16;
            --card-bg: #0d1b2a;
            --card-border: rgba(255, 255, 255, .07);
            --card-shadow: 0 1px 3px rgba(0, 0, 0, .3);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --divider: rgba(255, 255, 255, .07);
        }

        /* ================================================================
   RESET BASE
   ================================================================ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            background-color: var(--page-bg) !important;
            color: var(--text-main);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color .25s ease, color .25s ease;
        }

        /* ================================================================
   LAYOUT PRINCIPAL: sidebar fijo + content
   ================================================================ */
        .fmv-layout {
            display: flex;
            min-height: 100vh;
        }

        /* ================================================================
   SIDEBAR
   ================================================================ */
        #fmvSidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sb-width);
            height: 100vh;
            background-color: var(--sb-bg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1050;
            transition: width .25s cubic-bezier(.4, 0, .2, 1), transform .28s cubic-bezier(.4, 0, .2, 1), background-color .25s ease;
            box-shadow: 1px 0 0 var(--sb-border), 4px 0 24px rgba(0, 0, 0, .18);
        }

        /* ————————————————
   LOGO / BRAND
   ———————————————— */
        .sb-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px;
            height: var(--tb-height);
            border-bottom: 1px solid var(--sb-border);
            flex-shrink: 0;
            overflow: hidden;
            text-decoration: none;
        }

        .sb-brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #00686f;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .sb-brand-icon img {
            width: 28px;
            height: 28px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .sb-brand-text {
            overflow: hidden;
            transition: opacity .2s, width .25s;
        }

        .sb-brand-name {
            font-size: .92rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: .02em;
            white-space: nowrap;
            line-height: 1.2;
        }

        .sb-brand-sub {
            font-size: .65rem;
            color: var(--sb-text-muted);
            white-space: nowrap;
            letter-spacing: .06em;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* Botón toggle (colapsar) */
        .sb-toggle {
            margin-left: auto;
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: none;
            background: rgba(255, 255, 255, .08);
            color: var(--sb-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .18s;
        }

        .sb-toggle:hover {
            background: rgba(255, 255, 255, .15);
        }

        .sb-toggle svg {
            pointer-events: none;
        }

        /* ————————————————
   SCROLL WRAPPER
   ———————————————— */
        .sb-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px 0 16px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .12) transparent;
        }

        .sb-body::-webkit-scrollbar {
            width: 3px;
        }

        .sb-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 999px;
        }

        /* ————————————————
   SECCIONES DEL MENÚ
   ———————————————— */
        .sb-section-label {
            padding: 16px 20px 5px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--sb-text-muted);
            white-space: nowrap;
            overflow: hidden;
            transition: opacity .2s;
        }

        /* ————————————————
   NAV ITEMS
   ———————————————— */
        .sb-nav {
            list-style: none;
        }

        .sb-nav-item {
            position: relative;
        }

        .sb-nav-link {
            display: flex;
            align-items: center;
            gap: 0;
            padding: 9px 14px 9px 16px;
            margin: 1px 8px;
            border-radius: var(--radius-sm);
            color: var(--sb-text);
            text-decoration: none;
            font-size: .84rem;
            font-weight: 500;
            transition: background .16s, color .16s;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
            border: none;
            background: none;
            width: calc(100% - 16px);
            text-align: left;
        }

        .sb-nav-link:hover:not(.sb-disabled) {
            background: rgba(255, 255, 255, .07);
            color: #fff;
        }

        .sb-nav-link.active {
            background: var(--sb-bg-active);
            color: var(--sb-text-active);
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(0, 104, 111, .35);
        }

        .sb-nav-link.active .sb-nav-icon {
            color: #fff;
        }

        .sb-nav-link.sb-disabled {
            opacity: .35;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Icono */
        .sb-nav-icon {
            width: 36px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            color: var(--sb-icon-muted);
            transition: color .16s;
        }

        .sb-nav-link:hover:not(.sb-disabled) .sb-nav-icon {
            color: rgba(255, 255, 255, .85);
        }

        /* Texto */
        .sb-nav-text {
            flex: 1;
            overflow: hidden;
            transition: opacity .2s, max-width .25s;
        }

        /* Indicador collapse */
        .sb-nav-arrow {
            flex-shrink: 0;
            width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform .2s ease;
            color: var(--sb-text-muted);
            overflow: hidden;
        }

        .sb-nav-link[aria-expanded="true"] .sb-nav-arrow {
            transform: rotate(90deg);
        }

        /* Sub-menú */
        .sb-submenu {
            list-style: none;
            overflow: hidden;
            max-height: 0;
            transition: max-height .25s ease;
        }

        .sb-submenu.open {
            max-height: 400px;
        }

        .sb-submenu-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px 7px 52px;
            margin: 1px 8px;
            border-radius: var(--radius-sm);
            color: var(--sb-text);
            text-decoration: none;
            font-size: .81rem;
            font-weight: 400;
            transition: background .15s, color .15s;
            white-space: nowrap;
            overflow: hidden;
        }

        .sb-submenu-link::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
            opacity: .4;
            flex-shrink: 0;
        }

        .sb-submenu-link:hover:not(.sb-disabled) {
            background: rgba(255, 255, 255, .06);
            color: #fff;
        }

        .sb-submenu-link.active {
            color: #fff;
            font-weight: 600;
        }

        .sb-submenu-link.active::before {
            opacity: 1;
            background: var(--sb-bg-active);
        }

        .sb-submenu-link.sb-disabled {
            opacity: .35;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ————————————————
   CAJA DE SOPORTE
   ———————————————— */
        .sb-support {
            margin: 10px 12px 4px;
            padding: 14px;
            border-radius: 12px;
            background: rgba(0, 104, 111, .18);
            border: 1px solid rgba(0, 104, 111, .3);
            overflow: hidden;
            transition: opacity .2s;
        }

        .sb-support-title {
            font-size: .68rem;
            font-weight: 800;
            color: #4dd9e1;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 4px;
            white-space: nowrap;
        }

        .sb-support-text {
            font-size: .70rem;
            color: rgba(255, 255, 255, .55);
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .sb-support-btns {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sb-support-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 10px;
            border-radius: 9px;
            text-decoration: none;
            font-size: .76rem;
            font-weight: 700;
            transition: opacity .18s, transform .18s;
            white-space: nowrap;
        }

        .sb-support-btn:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .sb-btn-mail {
            background: rgba(59, 130, 246, .18);
            color: #93c5fd;
            border: 1px solid rgba(59, 130, 246, .25);
        }

        .sb-btn-wa {
            background: rgba(34, 197, 94, .15);
            color: #6ee7b7;
            border: 1px solid rgba(34, 197, 94, .22);
        }

        /* ————————————————
   SIDEBAR COLAPSADO (desktop)
   ———————————————— */
        .fmv-layout.sb-collapsed #fmvSidebar {
            width: var(--sb-width-collapsed);
        }

        .fmv-layout.sb-collapsed .sb-brand-text,
        .fmv-layout.sb-collapsed .sb-brand-toggle-label,
        .fmv-layout.sb-collapsed .sb-section-label,
        .fmv-layout.sb-collapsed .sb-nav-text,
        .fmv-layout.sb-collapsed .sb-nav-arrow,
        .fmv-layout.sb-collapsed .sb-support {
            opacity: 0;
            pointer-events: none;
            overflow: hidden;
            max-width: 0;
        }

        .fmv-layout.sb-collapsed .sb-brand {
            justify-content: center;
            padding: 0 14px;
        }

        .fmv-layout.sb-collapsed .sb-nav-link {
            justify-content: center;
            padding: 10px 12px;
            margin: 2px 6px;
        }

        .fmv-layout.sb-collapsed .sb-nav-icon {
            width: auto;
        }

        .fmv-layout.sb-collapsed .sb-submenu {
            max-height: 0 !important;
        }

        .fmv-layout.sb-collapsed #fmvContent {
            margin-left: var(--sb-width-collapsed);
        }

        /* ================================================================
   CONTENIDO PRINCIPAL
   ================================================================ */
        #fmvContent {
            flex: 1;
            margin-left: var(--sb-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .25s cubic-bezier(.4, 0, .2, 1);
            min-width: 0;
        }

        /* ================================================================
   TOP NAVBAR
   ================================================================ */
        #fmvTopbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            height: var(--tb-height);
            background: var(--tb-bg);
            border-bottom: 1px solid var(--tb-border);
            box-shadow: var(--tb-shadow);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            transition: background .25s, border-color .25s;
        }

        /* Botón hamburguesa mobile */
        .tb-hamburger {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            border-radius: 9px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--tb-muted);
            transition: background .18s, color .18s;
            flex-shrink: 0;
        }

        .tb-hamburger:hover {
            background: var(--fmv-green-ultra);
            color: var(--fmv-green);
        }

        /* Logo mobile */
        .tb-logo-mobile {
            display: none;
            align-items: center;
            text-decoration: none;
            gap: 8px;
            flex-shrink: 0;
        }

        .tb-logo-mobile .sb-brand-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
        }

        .tb-logo-mobile .sb-brand-icon img {
            width: 24px;
            height: 24px;
        }

        .tb-logo-mobile span {
            font-size: .88rem;
            font-weight: 800;
            color: var(--fmv-green);
            letter-spacing: .02em;
        }

        /* Separador */
        .tb-sep {
            width: 1px;
            height: 26px;
            background: var(--divider);
            flex-shrink: 0;
        }

        /* Bienvenida (desktop) */
        .tb-welcome {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
        }

        .tb-welcome-hi {
            font-size: .68rem;
            font-weight: 600;
            color: var(--tb-muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .tb-welcome-name {
            font-size: .88rem;
            font-weight: 700;
            color: var(--tb-text);
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Spacer */
        .tb-spacer {
            flex: 1;
        }

        /* ————————————————
   ACCIONES TOPBAR
   ———————————————— */
        .tb-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Botón tema */
        .tb-theme-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            color: var(--tb-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .18s, color .18s, border-color .18s, box-shadow .18s;
            flex-shrink: 0;
        }

        .tb-theme-btn:hover {
            background: var(--fmv-green);
            color: #fff;
            border-color: var(--fmv-green);
            box-shadow: 0 4px 12px rgba(0, 104, 111, .3);
        }

        /* Avatar usuario */
        .tb-user-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 5px 10px 5px 6px;
            border-radius: 30px;
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            transition: border-color .18s, box-shadow .18s;
            text-decoration: none;
        }

        .tb-user-wrap:hover {
            border-color: var(--fmv-green);
            box-shadow: 0 0 0 3px var(--fmv-green-light);
        }

        .tb-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--fmv-green);
            flex-shrink: 0;
        }

        .tb-user-initials {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--fmv-green);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .tb-user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .tb-user-name {
            font-size: .80rem;
            font-weight: 700;
            color: var(--tb-text);
            max-width: 130px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tb-user-role {
            font-size: .67rem;
            font-weight: 600;
            color: var(--fmv-green);
        }

        .tb-chevron {
            color: var(--tb-muted);
            transition: transform .2s;
            margin-left: 2px;
        }

        .tb-user-wrap[aria-expanded="true"] .tb-chevron {
            transform: rotate(180deg);
        }

        /* Dropdown menú */
        .tb-dropdown-menu {
            min-width: 230px;
            border: 1px solid var(--card-border) !important;
            border-radius: 14px !important;
            box-shadow: 0 8px 32px rgba(15, 23, 42, .12) !important;
            padding: 6px !important;
            background: var(--card-bg) !important;
        }

        html[data-bs-theme="dark"] .tb-dropdown-menu {
            background: #1e293b !important;
            border-color: rgba(255, 255, 255, .08) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .35) !important;
        }

        .tb-dropdown-header {
            padding: 10px 12px 12px;
            margin-bottom: 4px;
            border-bottom: 1px solid var(--divider);
        }

        .tb-dropdown-user-name {
            font-size: .84rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tb-dropdown-user-email {
            font-size: .72rem;
            color: var(--text-muted);
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fmv-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            margin-top: 6px;
            background: var(--fmv-green-light);
            color: var(--fmv-green);
            font-size: .68rem;
            font-weight: 700;
            border: 1px solid rgba(0, 104, 111, .18);
        }

        .tb-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: .83rem;
            font-weight: 500;
            color: var(--text-main) !important;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        .tb-dropdown-item:hover {
            background: var(--fmv-green-light) !important;
            color: var(--fmv-green) !important;
        }

        .tb-dropdown-item.danger:hover {
            background: rgba(220, 38, 38, .08) !important;
            color: #dc2626 !important;
        }

        .tb-dropdown-item.warn:hover {
            background: rgba(245, 158, 11, .08) !important;
            color: #d97706 !important;
        }

        .tb-dropdown-item i {
            width: 16px;
            flex-shrink: 0;
            text-align: center;
            font-size: .85rem;
        }

        .tb-divider {
            border: none;
            border-top: 1px solid var(--divider);
            margin: 4px 0;
        }

        /* ================================================================
   ÁREA DE CONTENIDO (páginas)
   ================================================================ */
        .fmv-page {
            flex: 1;
            padding: 24px 22px 32px;
        }

        /* ================================================================
   CARDS
   ================================================================ */
        html[data-bs-theme="dark"] .card {
            background: var(--card-bg) !important;
            border-color: var(--card-border) !important;
        }

        html[data-bs-theme="dark"] .card-header,
        html[data-bs-theme="dark"] .card-footer {
            background: rgba(255, 255, 255, .02) !important;
            border-color: var(--card-border) !important;
        }

        /* ================================================================
   BTN ISO (FULMUV primary button)
   ================================================================ */
        .btn-iso {
            --falcon-btn-color: #fff;
            --falcon-btn-bg: #00686f;
            --falcon-btn-border-color: #00686f;
            --falcon-btn-hover-color: #fff;
            --falcon-btn-hover-bg: #004d52;
            --falcon-btn-hover-border-color: #004d52;
            --falcon-btn-active-color: #fff;
            --falcon-btn-active-bg: #004d52;
            --falcon-btn-active-border-color: #004d52;
            --falcon-btn-disabled-color: #fff;
            --falcon-btn-disabled-bg: #00686f;
            --falcon-btn-disabled-border-color: #00686f;
        }

        /* ================================================================
   PAYMENTEZ FIX
   ================================================================ */
        .paymentez-checkout-frame {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 9999 !important;
            width: 420px !important;
            max-width: 95vw;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
        }

        /* ================================================================
   OVERLAY MOBILE SIDEBAR
   ================================================================ */
        #fmvOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .48);
            z-index: 1049;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        #fmvOverlay.show {
            display: block;
        }

        /* ================================================================
   RESPONSIVE MOBILE
   ================================================================ */
        @media (max-width: 1199.98px) {
            #fmvSidebar {
                transform: translateX(-100%);
                transition: transform .28s cubic-bezier(.4, 0, .2, 1), background-color .25s ease;
            }

            #fmvSidebar.mobile-open {
                transform: translateX(0);
            }

            #fmvContent {
                margin-left: 0 !important;
            }

            .fmv-layout.sb-collapsed #fmvContent {
                margin-left: 0 !important;
            }

            .tb-hamburger {
                display: inline-flex;
            }

            .tb-logo-mobile {
                display: flex;
            }

            .tb-sep,
            .tb-welcome {
                display: none !important;
            }
        }

        @media (max-width: 767.98px) {
            .fmv-page {
                padding: 16px 14px 24px;
            }

            .tb-user-info {
                display: none;
            }

            .tb-user-wrap {
                padding: 4px;
                border-radius: 50%;
                border: none;
                background: transparent;
            }

            .tb-user-wrap:hover {
                background: transparent;
            }

            .tb-chevron {
                display: none;
            }
        }

        /* ================================================================
   SCROLLBAR GLOBAL
   ================================================================ */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 104, 111, .22);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 104, 111, .4);
        }
    </style>
</head>

<body>
    <!-- Overlay para mobile sidebar -->
    <div id="fmvOverlay"></div>

    <div class="fmv-layout" id="fmvLayout">

        <!-- ================================================================
     SIDEBAR
     ================================================================ -->
        <aside id="fmvSidebar">

            <!-- BRAND / LOGO -->
            <div class="sb-brand">
                <div class="sb-brand-icon">
                    <img src="../img/FULMUV-LOGO-60X60.png" alt="FULMUV">
                </div>
                <div class="sb-brand-text">
                    <div class="sb-brand-name">FULMUV</div>
                    <div class="sb-brand-sub">Panel Empresarial</div>
                </div>
                <button class="sb-toggle" id="sbToggleBtn" title="Contraer menú" aria-label="Contraer menú">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
            </div>

            <!-- CUERPO DEL MENÚ -->
            <div class="sb-body">
                <ul class="sb-nav">

                    <!-- HOME -->
                    <?php
                    $show_home = false;
                    foreach ($permisos as $v) {
                        if (($v["permiso"] === "Dashboard" && $v["valor"] === "true") ||
                            ($v["permiso"] === "Ordenes" && $v["valor"] === "true" && in_array($v["levels"] ?? '', ["Empresa", "Sucursal"]))
                        ) {
                            $show_home = true;
                            break;
                        }
                    }
                    if ($show_home):
                        $ha = (($menu === "dashboard" && $rol_id == 1) || ($menu === "empresas" && $rol_id == 2) || ($menu === "ordenes" && in_array($rol_id, [3, 5]) && $sub_menu === "crear_orden")) ? 'active' : '';
                    ?>
                        <li class="sb-nav-item">
                            <a class="sb-nav-link <?= $ha ?>" href="<?= htmlspecialchars($dashboard, ENT_QUOTES, 'UTF-8') ?>">
                                <span class="sb-nav-icon"><i class="fas fa-home"></i></span>
                                <span class="sb-nav-text">Home</span>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>

                <!-- Sección Admin -->
                <div class="sb-section-label">Administración</div>
                <ul class="sb-nav">

                    <!-- USUARIOS -->
                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Usuarios" && $v["valor"] === "true"): ?>
                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $visible ?> <?= $menu === "usuarios" ? 'active' : '' ?>" href="usuarios.php">
                                    <span class="sb-nav-icon"><i class="fas fa-users"></i></span>
                                    <span class="sb-nav-text">Usuarios</span>
                                </a>
                            </li>
                    <?php endif;
                    endforeach; ?>

                    <!-- PRODUCTOS -->
                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Productos" && $v["valor"] === "true"): ?>
                            <li class="sb-nav-item">
                                <button class="sb-nav-link <?= $visible_productos ?> <?= $menu === "productos" ? 'active' : '' ?>"
                                    onclick="sbToggleSub('sub-productos',this)" aria-expanded="<?= $menu === 'productos' ? 'true' : 'false' ?>">
                                    <span class="sb-nav-icon"><i class="fas fa-cubes"></i></span>
                                    <span class="sb-nav-text">Productos</span>
                                    <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                                </button>
                                <ul class="sb-submenu <?= $menu === 'productos' ? 'open' : '' ?>" id="sub-productos">
                                    <li><a class="sb-submenu-link <?= $visible_productos ?> <?= $sub_menu === "crear_producto" ? 'active' : '' ?>" href="crear_producto.php">Crear Producto</a></li>
                                    <li><a class="sb-submenu-link <?= $visible_productos ?> <?= $sub_menu === "productos" ? 'active' : '' ?>" href="productos.php">Ver Productos</a></li>
                                    <li><a class="sb-submenu-link <?= $visible_masiva ?>" href="carga_masiva_productos.php">Carga masiva</a></li>
                                </ul>
                            </li>

                            <!-- SERVICIOS -->
                            <li class="sb-nav-item">
                                <button class="sb-nav-link <?= $menu === "servicios" ? 'active' : '' ?>"
                                    onclick="sbToggleSub('sub-servicios',this)" aria-expanded="<?= $menu === 'servicios' ? 'true' : 'false' ?>">
                                    <span class="sb-nav-icon"><i class="fas fa-tools"></i></span>
                                    <span class="sb-nav-text">Servicios</span>
                                    <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                                </button>
                                <ul class="sb-submenu <?= $menu === 'servicios' ? 'open' : '' ?>" id="sub-servicios">
                                    <li><a class="sb-submenu-link <?= $sub_menu === "crear_servicio" ? 'active' : '' ?>" href="crear_servicio.php">Crear Servicio</a></li>
                                    <li><a class="sb-submenu-link <?= $sub_menu === "servicios" ? 'active' : '' ?>" href="servicios.php">Ver Servicios</a></li>
                                    <li><a class="sb-submenu-link <?= $visible_masiva ?>" href="carga_masiva_servicios.php">Carga masiva</a></li>
                                </ul>
                            </li>
                    <?php endif;
                    endforeach; ?>

                    <!-- VEHÍCULOS -->
                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $visible_veh_ev ?> <?= $menu === "vehiculos" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-vehiculos',this)" aria-expanded="<?= $menu === 'vehiculos' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="fas fa-car-alt"></i></span>
                            <span class="sb-nav-text">Vehículos</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'vehiculos' ? 'open' : '' ?>" id="sub-vehiculos">
                            <li><a class="sb-submenu-link <?= $visible_veh_ev ?> <?= $sub_menu === "crear_vehiculo" ? 'active' : '' ?>" href="crear_vehiculo.php">Crear Vehículo</a></li>
                            <li><a class="sb-submenu-link <?= $visible_veh_ev ?> <?= $sub_menu === "vehiculos" ? 'active' : '' ?>" href="vehiculos.php">Ver Vehículos</a></li>
                            <li><a class="sb-submenu-link <?= $visible_masiva ?>" href="carga_masiva_vehiculos.php">Carga masiva</a></li>
                        </ul>
                    </li>

                </ul>

                <!-- Sección Gestión -->
                <?php foreach ($permisos as $v): if ($v["permiso"] === "Ordenes" && $v["valor"] === "true"): ?>
                        <div class="sb-section-label">Gestión</div>
                        <ul class="sb-nav">

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $visible ?> <?= $menu === "sucursales" ? 'active' : '' ?>" href="sucursales.php">
                                    <span class="sb-nav-icon"><i class="fas fa-store"></i></span>
                                    <span class="sb-nav-text">Sucursales</span>
                                </a>
                            </li>

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $visible_ordenes ?> <?= $menu === "ordenes" ? 'active' : '' ?>"
                                    href="<?= $visible_ordenes ? '#' : 'ordenes.php' ?>"
                                    <?= $visible_ordenes ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                                    <span class="sb-nav-icon"><i class="fas fa-receipt"></i></span>
                                    <span class="sb-nav-text">Órdenes</span>
                                </a>
                            </li>

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $visible_veh_ev ?> <?= $menu === "eventos" ? 'active' : '' ?>" href="eventos.php">
                                    <span class="sb-nav-icon"><i class="far fa-calendar-alt"></i></span>
                                    <span class="sb-nav-text">Eventos</span>
                                </a>
                            </li>

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $visible ?> <?= $menu === "galeria" ? 'active' : '' ?>"
                                    href="galeria.php?id_empresa=<?= (int)$id_empresa ?>">
                                    <span class="sb-nav-icon"><i class="fas fa-images"></i></span>
                                    <span class="sb-nav-text">Galería</span>
                                </a>
                            </li>

                            <!-- EMPLEOS -->
                            <li class="sb-nav-item">
                                <button class="sb-nav-link <?= $visible ?> <?= $menu === "empleos" ? 'active' : '' ?>"
                                    onclick="sbToggleSub('sub-empleos',this)" aria-expanded="<?= $menu === 'empleos' ? 'true' : 'false' ?>">
                                    <span class="sb-nav-icon"><i class="fas fa-briefcase"></i></span>
                                    <span class="sb-nav-text">Empleos</span>
                                    <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                                </button>
                                <ul class="sb-submenu <?= $menu === 'empleos' ? 'open' : '' ?>" id="sub-empleos">
                                    <li><a class="sb-submenu-link <?= $visible ?> <?= $sub_menu === "crear_empleo" ? 'active' : '' ?>" href="crear_empleo.php">Crear Empleo</a></li>
                                    <li><a class="sb-submenu-link <?= $visible ?> <?= $sub_menu === "empleos" ? 'active' : '' ?>" href="empleos.php">Ver Empleos</a></li>
                                </ul>
                            </li>

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $menu === "reportes" ? 'active' : '' ?>" href="reportes.php">
                                    <span class="sb-nav-icon"><i class="fas fa-chart-bar"></i></span>
                                    <span class="sb-nav-text">Reportes</span>
                                </a>
                            </li>

                        </ul>

                        <!-- Sección Mi Empresa -->
                        <div class="sb-section-label">Mi Empresa</div>
                        <ul class="sb-nav">

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $menu === "empresa" ? 'active' : '' ?>"
                                    href="editar_empresa.php?id_empresa=<?= (int)$id_empresa ?>">
                                    <span class="sb-nav-icon"><i class="fas fa-building"></i></span>
                                    <span class="sb-nav-text">Perfil</span>
                                </a>
                            </li>

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $visible ?> <?= $menu === "verificar" ? 'active' : '' ?>"
                                    href="verificar.php?id_empresa=<?= (int)$id_empresa ?>">
                                    <span class="sb-nav-icon"><i class="fas fa-check-circle"></i></span>
                                    <span class="sb-nav-text">Verificar Empresa</span>
                                </a>
                            </li>

                            <li class="sb-nav-item">
                                <a class="sb-nav-link <?= $visible_upgrade ?> <?= $menu === "upgrade" ? 'active' : '' ?>"
                                    href="upgrade_membresia.php?id_empresa=<?= (int)$id_empresa ?>">
                                    <span class="sb-nav-icon"><i class="fas fa-rocket"></i></span>
                                    <span class="sb-nav-text">Upgrade</span>
                                </a>
                            </li>

                            <?php if ($tipo_user !== "sucursal"): ?>
                                <li class="sb-nav-item">
                                    <a class="sb-nav-link <?= $menu === "tarjetas" ? 'active' : '' ?>" href="tarjetas.php">
                                        <span class="sb-nav-icon"><i class="fas fa-credit-card"></i></span>
                                        <span class="sb-nav-text">Tarjetas</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                        </ul>
                <?php endif;
                endforeach; ?>

                <!-- Soporte -->
                <div class="sb-support">
                    <div class="sb-support-title"><i class="fas fa-headset" style="margin-right:5px;"></i>Soporte FULMUV</div>
                    <div class="sb-support-text">¿Necesitas ayuda? Contáctanos directamente.</div>
                    <div class="sb-support-btns">
                        <a id="btnSupportMail" class="sb-support-btn sb-btn-mail" href="#">
                            <i class="fas fa-envelope"></i><span>Correo</span>
                        </a>
                        <a id="btnSupportWhatsapp" class="sb-support-btn sb-btn-wa" href="#" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-whatsapp"></i><span>WhatsApp</span>
                        </a>
                    </div>
                </div>

            </div><!-- /sb-body -->
        </aside><!-- /sidebar -->

        <!-- ================================================================
     CONTENIDO PRINCIPAL
     ================================================================ -->
        <div id="fmvContent">

            <!-- TOP NAVBAR -->
            <header id="fmvTopbar">

                <!-- Hamburguesa (mobile) -->
                <button class="tb-hamburger" id="tbHamburger" aria-label="Abrir menú">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>

                <!-- Logo (mobile) -->
                <a class="tb-logo-mobile" href="<?= htmlspecialchars($dashboard, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="sb-brand-icon">
                        <img src="../img/FULMUV-LOGO-60X60.png" alt="FULMUV">
                    </div>
                    <span>FULMUV</span>
                </a>

                <!-- Separador + Bienvenida (desktop) -->
                <div class="tb-sep d-none d-xl-block"></div>
                <div class="tb-welcome d-none d-xl-flex">
                    <span class="tb-welcome-hi">Bienvenido/a</span>
                    <span class="tb-welcome-name"><?= htmlspecialchars($nombre_display, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="tb-spacer"></div>

                <!-- ACCIONES -->
                <div class="tb-actions">

                    <!-- Botón tema (SVG inline — no depende de FA) -->
                    <button id="tbThemeBtn" class="tb-theme-btn" aria-label="Cambiar tema" title="Cambiar tema">
                        <svg id="tbIconSun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="5" />
                            <line x1="12" y1="1" x2="12" y2="3" />
                            <line x1="12" y1="21" x2="12" y2="23" />
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                            <line x1="1" y1="12" x2="3" y2="12" />
                            <line x1="21" y1="12" x2="23" y2="12" />
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                        </svg>
                        <svg id="tbIconMoon" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                        </svg>
                    </button>

                    <!-- Usuario dropdown -->
                    <div class="dropdown">
                        <a class="tb-user-wrap" id="tbUserDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            href="#" role="button">
                            <img class="tb-user-avatar img-perfil-dinamica"
                                src="<?= htmlspecialchars($imagen_principal_src, ENT_QUOTES, 'UTF-8') ?>"
                                onerror="this.onerror=null;this.src='../img/FULMUV-LOGO-60X60.png';"
                                alt="Perfil">
                            <div class="tb-user-info d-none d-md-flex">
                                <span class="tb-user-name"><?= htmlspecialchars($nombre_display, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="tb-user-role">
                                    <?php if ($membresia_nombre): ?>
                                        <i class="fas fa-star" style="font-size:.55rem;"></i>
                                        <?= htmlspecialchars($membresia_nombre, ENT_QUOTES, 'UTF-8') ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($nombre_rol_user, ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <svg class="tb-chevron d-none d-md-block" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </a>

                        <div class="dropdown-menu tb-dropdown-menu dropdown-menu-end" aria-labelledby="tbUserDropdown">
                            <div class="tb-dropdown-header">
                                <div class="tb-dropdown-user-name"><?= htmlspecialchars($nombre_display, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="tb-dropdown-user-email"><?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($membresia_nombre): ?>
                                    <div class="fmv-badge">
                                        <i class="fas fa-star" style="font-size:.6rem;"></i>
                                        <?= htmlspecialchars($membresia_nombre, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a class="tb-dropdown-item" href="#" id="btnAbrirConfiguracion">
                                <i class="fas fa-cog"></i> Configuración
                            </a>
                            <?php if ($tipo_user !== "sucursal"): ?>
                                <div class="tb-divider"></div>
                                <a class="tb-dropdown-item warn" href="#" id="btnBajaFulmuv">
                                    <i class="fas fa-sign-out-alt"></i> Darme de baja
                                </a>
                            <?php endif; ?>
                            <div class="tb-divider"></div>
                            <a class="tb-dropdown-item danger" href="login.php">
                                <i class="fas fa-power-off"></i> Cerrar sesión
                            </a>
                        </div>
                    </div>

                </div>
            </header><!-- /topbar -->

            <!-- INPUTS OCULTOS DE SESIÓN -->
            <input type="hidden" id="id_principal" value="<?= (int)$id_usuario ?>">
            <input type="hidden" id="id_rol_principal" value="<?= (int)$rol_id ?>">
            <input type="hidden" id="nombre_rol_principal" value="<?= htmlspecialchars($nombre_rol_user, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="username_principal" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="id_empresa" value="<?= (int)$id_empresa ?>">
            <input type="hidden" id="tipo_user" value="<?= htmlspecialchars($tipo_user, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="membresia_nombre" value="<?= htmlspecialchars($membresia_nombre, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="imagen_principal" value="<?= htmlspecialchars($imagen_principal_src, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="id_carrito" value="carrito_<?= (int)$id_usuario ?>_<?= (int)$id_empresa ?>">
            <input type="hidden" id="fulmuv_support_email" value="gestiones@fulmuv.com">
            <input type="hidden" id="fulmuv_support_whatsapp" value="593992744454">

            <!-- MODAL CONFIGURACIÓN DE CUENTA -->
            <div class="modal fade" id="modalConfiguracion" tabindex="-1" aria-labelledby="lblModalConfig" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
                    <div class="modal-content" style="border-radius:18px;border:1px solid var(--card-border);background:var(--card-bg);box-shadow:0 20px 60px rgba(0,0,0,.15);">
                        <div class="modal-header" style="border-bottom:1px solid var(--divider);padding:18px 22px;">
                            <h5 class="modal-title" id="lblModalConfig" style="font-size:.95rem;font-weight:700;color:var(--text-main);">
                                <i class="fas fa-user-cog me-2" style="color:var(--fmv-green);"></i>Configuración de cuenta
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" style="padding:22px;">
                            <!-- Avatar -->
                            <div class="text-center mb-4" style="position:relative;display:inline-block;width:100%;">
                                <div style="position:relative;display:inline-block;">
                                    <img id="imagenConfigModal" class="rounded-circle img-perfil-dinamica"
                                        src="../img/FULMUV-LOGO-60X60.png"
                                        onerror="this.onerror=null;this.src='../img/FULMUV-LOGO-60X60.png';"
                                        width="90" height="90"
                                        style="object-fit:cover;border:3px solid var(--fmv-green);cursor:pointer;"
                                        onclick="document.getElementById('imgFileInput').click()"
                                        title="Cambiar foto">
                                    <button type="button" onclick="document.getElementById('imgFileInput').click()"
                                        style="position:absolute;bottom:0;right:0;width:28px;height:28px;border-radius:50%;border:none;background:var(--fmv-green);color:#fff;font-size:.7rem;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>
                                <input type="file" id="imgFileInput" style="display:none;" accept="image/*">
                            </div>
                            <div class="row g-3">
                                <input type="hidden" id="idUsuariosConfig">
                                <div class="col-12">
                                    <label style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:5px;display:block;">Usuario</label>
                                    <input type="text" class="form-control" id="usernameConfig" readonly disabled style="font-size:.84rem;border-radius:9px;">
                                </div>
                                <div class="col-12">
                                    <label style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:5px;display:block;">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="passwordConfig" placeholder="••••••••" style="font-size:.84rem;border-radius:9px;">
                                </div>
                                <div class="col-12">
                                    <label style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:5px;display:block;">Repetir contraseña</label>
                                    <input type="password" class="form-control" id="passwordConfig2" placeholder="••••••••" style="font-size:.84rem;border-radius:9px;">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="verPasswordConfig">
                                        <label class="form-check-label" for="verPasswordConfig" style="font-size:.81rem;">Mostrar contraseña</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-top:1px solid var(--divider);padding:14px 22px;gap:8px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal" style="padding:6px 18px;">Cancelar</button>
                            <button type="button" class="btn btn-iso btn-sm rounded-pill" onclick="fmvUpdatePassword()" style="padding:6px 18px;">
                                <i class="fas fa-save me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="alert"></div>

            <!-- CONTENIDO DE CADA PÁGINA EMPIEZA AQUÍ -->
            <div class="fmv-page">