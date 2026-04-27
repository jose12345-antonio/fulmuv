<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
ini_set("display_errors", 0);
session_start();
session_cache_limiter('private_no_expire');
date_default_timezone_set('America/Guayaquil');

if (!isset($_SESSION["id_usuario"])) {
    echo "<script>window.location.href='login.php'</script>";
    exit();
}

$correo           = $_SESSION["correo"]          ?? '';
$id_usuario       = $_SESSION["id_usuario"]      ?? '';
$nombres          = $_SESSION["nombres"]         ?? '';
$apellidos        = $_SESSION["apellidos"]       ?? '';
$username         = $_SESSION["username"]        ?? '';
$rol_id           = $_SESSION["rol_id"]          ?? '';
$imagen           = $_SESSION["imagen"]          ?? '';
$nombre_rol_user  = $_SESSION["nombre_rol_user"] ?? '';
$permisos         = $_SESSION["permisos"]        ?? [];
$id_empresa       = $_SESSION["id_empresa"]      ?? 0;
$dashboard        = $_SESSION["dashboard"]       ?? 'home.php';
$imagen_fallback  = "../img/FULMUV-LOGO-60X60.png";
$imagen_principal_src = trim((string)$imagen) !== '' ? $imagen : $imagen_fallback;

if (!empty($permisos["error"])) {
    echo "<script>window.location.href='login.php'</script>";
    exit();
}

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

    <script src="../theme/public/assets/js/config.js?v=3"></script>
    <script src="../theme/public/vendors/simplebar/simplebar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
        :root {
            --fmv-green: #00686f;
            --fmv-green-hover: #00797f;
            --fmv-green-dark: #004d52;
            --fmv-green-light: rgba(0, 104, 111, .12);
            --fmv-green-ultra: rgba(0, 104, 111, .06);
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
            --tb-bg: #ffffff;
            --tb-border: #e8ecf0;
            --tb-shadow: 0 1px 0 #e8ecf0, 0 4px 16px rgba(15, 23, 42, .04);
            --tb-height: 64px;
            --tb-text: #0f172a;
            --tb-muted: #64748b;
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

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            background-color: var(--page-bg) !important;
            color: var(--text-main);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            transition: background-color .25s ease, color .25s ease;
        }

        .fmv-layout { display: flex; min-height: 100vh; }

        #fmvSidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sb-width); height: 100vh;
            background-color: var(--sb-bg);
            display: flex; flex-direction: column;
            overflow: hidden; z-index: 1050;
            transition: width .25s cubic-bezier(.4,0,.2,1), transform .28s cubic-bezier(.4,0,.2,1), background-color .25s ease;
            box-shadow: 1px 0 0 var(--sb-border), 4px 0 24px rgba(0,0,0,.18);
        }

        .sb-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 0 20px; height: var(--tb-height);
            border-bottom: 1px solid var(--sb-border);
            flex-shrink: 0; overflow: hidden; text-decoration: none;
        }
        .sb-brand-icon {
            width: 34px; height: 34px; border-radius: 10px;
            background: #00686f; display: flex; align-items: center;
            justify-content: center; flex-shrink: 0; overflow: hidden;
        }
        .sb-brand-icon img { width: 28px; height: 28px; object-fit: contain; filter: brightness(0) invert(1); }
        .sb-brand-text { overflow: hidden; transition: opacity .2s, width .25s; }
        .sb-brand-name { font-size: .92rem; font-weight: 800; color: #ffffff; letter-spacing: .02em; white-space: nowrap; line-height: 1.2; }
        .sb-brand-sub { font-size: .65rem; color: var(--sb-text-muted); white-space: nowrap; letter-spacing: .06em; text-transform: uppercase; font-weight: 500; }

        .sb-toggle {
            margin-left: auto; flex-shrink: 0; width: 28px; height: 28px;
            border-radius: 7px; border: none;
            background: rgba(255,255,255,.08); color: var(--sb-text);
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background .18s;
        }
        .sb-toggle:hover { background: rgba(255,255,255,.15); }
        .sb-toggle svg { pointer-events: none; }

        .sb-body {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            padding: 10px 0 16px;
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.12) transparent;
        }
        .sb-body::-webkit-scrollbar { width: 3px; }
        .sb-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 999px; }

        .sb-section-label {
            padding: 16px 20px 5px; font-size: .65rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--sb-text-muted); white-space: nowrap; overflow: hidden;
            transition: opacity .2s;
        }

        .sb-nav { list-style: none; }
        .sb-nav-item { position: relative; }

        .sb-nav-link {
            display: flex; align-items: center; gap: 0;
            padding: 9px 14px 9px 16px; margin: 1px 8px;
            border-radius: var(--radius-sm);
            color: var(--sb-text); text-decoration: none;
            font-size: .84rem; font-weight: 500;
            transition: background .16s, color .16s;
            cursor: pointer; white-space: nowrap; overflow: hidden;
            position: relative; border: none; background: none;
            width: calc(100% - 16px); text-align: left;
        }
        .sb-nav-link:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sb-nav-link.active {
            background: var(--sb-bg-active); color: var(--sb-text-active);
            font-weight: 600; box-shadow: 0 4px 14px rgba(0,104,111,.35);
        }
        .sb-nav-link.active .sb-nav-icon { color: #fff; }

        .sb-nav-icon {
            width: 36px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .9rem; color: var(--sb-icon-muted); transition: color .16s;
        }
        .sb-nav-link:hover .sb-nav-icon { color: rgba(255,255,255,.85); }

        .sb-nav-text { flex: 1; overflow: hidden; transition: opacity .2s, max-width .25s; }

        .sb-nav-arrow {
            flex-shrink: 0; width: 20px;
            display: flex; align-items: center; justify-content: center;
            transition: transform .2s ease; color: var(--sb-text-muted); overflow: hidden;
        }
        .sb-nav-link[aria-expanded="true"] .sb-nav-arrow { transform: rotate(90deg); }

        .sb-submenu {
            list-style: none; overflow: hidden;
            max-height: 0; transition: max-height .25s ease;
        }
        .sb-submenu.open { max-height: 600px; }

        .sb-submenu-link {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 14px 7px 52px; margin: 1px 8px;
            border-radius: var(--radius-sm);
            color: var(--sb-text); text-decoration: none;
            font-size: .81rem; font-weight: 400;
            transition: background .15s, color .15s;
            white-space: nowrap; overflow: hidden;
        }
        .sb-submenu-link::before {
            content: ''; width: 5px; height: 5px; border-radius: 50%;
            background: currentColor; opacity: .4; flex-shrink: 0;
        }
        .sb-submenu-link:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sb-submenu-link.active { color: #fff; font-weight: 600; }
        .sb-submenu-link.active::before { opacity: 1; background: var(--sb-bg-active); }

        /* Sidebar colapsado */
        .fmv-layout.sb-collapsed #fmvSidebar { width: var(--sb-width-collapsed); }
        .fmv-layout.sb-collapsed .sb-brand-text,
        .fmv-layout.sb-collapsed .sb-section-label,
        .fmv-layout.sb-collapsed .sb-nav-text,
        .fmv-layout.sb-collapsed .sb-nav-arrow {
            opacity: 0; pointer-events: none; overflow: hidden; max-width: 0;
        }
        .fmv-layout.sb-collapsed .sb-brand { justify-content: center; padding: 0 14px; }
        .fmv-layout.sb-collapsed .sb-nav-link { justify-content: center; padding: 10px 12px; margin: 2px 6px; }
        .fmv-layout.sb-collapsed .sb-nav-icon { width: auto; }
        .fmv-layout.sb-collapsed .sb-submenu { max-height: 0 !important; }
        .fmv-layout.sb-collapsed #fmvContent { margin-left: var(--sb-width-collapsed); }

        /* Contenido principal */
        #fmvContent {
            flex: 1; margin-left: var(--sb-width); min-height: 100vh;
            display: flex; flex-direction: column;
            transition: margin-left .25s cubic-bezier(.4,0,.2,1); min-width: 0;
        }

        /* Topbar */
        #fmvTopbar {
            position: sticky; top: 0; z-index: 1000;
            height: var(--tb-height);
            background: var(--tb-bg); border-bottom: 1px solid var(--tb-border);
            box-shadow: var(--tb-shadow);
            display: flex; align-items: center; gap: 12px; padding: 0 20px;
            transition: background .25s, border-color .25s;
        }

        /* Barra filtro empresa/sucursal */
        .admin-filter-bar {
            background: var(--tb-bg);
            border-bottom: 1px solid var(--tb-border);
            padding: 8px 20px;
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            position: sticky; top: var(--tb-height); z-index: 999;
        }
        .admin-filter-label {
            font-size: .72rem; font-weight: 700; color: var(--fmv-green);
            text-transform: uppercase; letter-spacing: .06em; white-space: nowrap;
            display: flex; align-items: center; gap: 5px;
        }
        .admin-filter-sep { width: 1px; height: 20px; background: var(--divider); }
        .admin-filter-sel {
            font-size: .8rem; border-radius: 8px; border: 1px solid var(--card-border);
            background: var(--card-bg); color: var(--text-main);
            padding: 4px 28px 4px 10px; min-width: 170px; max-width: 240px;
            cursor: pointer; transition: border-color .18s, box-shadow .18s;
            appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 8px center;
        }
        .admin-filter-sel:focus { outline: none; border-color: var(--fmv-green); box-shadow: 0 0 0 3px var(--fmv-green-light); }
        html[data-bs-theme="dark"] .admin-filter-sel { background-color: #1e293b; border-color: rgba(255,255,255,.1); color: #f1f5f9; }

        .tb-hamburger {
            width: 36px; height: 36px; border: none; background: transparent;
            border-radius: 9px; display: none; align-items: center; justify-content: center;
            cursor: pointer; color: var(--tb-muted); transition: background .18s, color .18s; flex-shrink: 0;
        }
        .tb-hamburger:hover { background: var(--fmv-green-ultra); color: var(--fmv-green); }

        .tb-logo-mobile {
            display: none; align-items: center; text-decoration: none;
            gap: 8px; flex-shrink: 0;
        }
        .tb-logo-mobile .sb-brand-icon { width: 30px; height: 30px; border-radius: 8px; }
        .tb-logo-mobile .sb-brand-icon img { width: 24px; height: 24px; }
        .tb-logo-mobile span { font-size: .88rem; font-weight: 800; color: var(--fmv-green); letter-spacing: .02em; }

        .tb-sep { width: 1px; height: 26px; background: var(--divider); flex-shrink: 0; }

        .tb-welcome { display: flex; flex-direction: column; line-height: 1.25; }
        .tb-welcome-hi { font-size: .68rem; font-weight: 600; color: var(--tb-muted); text-transform: uppercase; letter-spacing: .06em; }
        .tb-welcome-name { font-size: .88rem; font-weight: 700; color: var(--tb-text); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .tb-spacer { flex: 1; }

        .tb-actions { display: flex; align-items: center; gap: 8px; }

        .tb-theme-btn {
            width: 36px; height: 36px; border-radius: 10px;
            border: 1px solid var(--card-border); background: var(--card-bg);
            color: var(--tb-muted); display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background .18s, color .18s, border-color .18s, box-shadow .18s; flex-shrink: 0;
        }
        .tb-theme-btn:hover { background: var(--fmv-green); color: #fff; border-color: var(--fmv-green); box-shadow: 0 4px 12px rgba(0,104,111,.3); }

        .tb-user-wrap {
            display: flex; align-items: center; gap: 10px; cursor: pointer;
            padding: 5px 10px 5px 6px; border-radius: 30px;
            border: 1px solid var(--card-border); background: var(--card-bg);
            transition: border-color .18s, box-shadow .18s; text-decoration: none;
        }
        .tb-user-wrap:hover { border-color: var(--fmv-green); box-shadow: 0 0 0 3px var(--fmv-green-light); }

        .tb-user-avatar {
            width: 32px; height: 32px; border-radius: 50%; object-fit: cover;
            border: 2px solid var(--fmv-green); flex-shrink: 0;
        }
        .tb-user-initials {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--fmv-green); color: #fff;
            font-size: .8rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        .tb-user-info { display: flex; flex-direction: column; line-height: 1.2; }
        .tb-user-name { font-size: .80rem; font-weight: 700; color: var(--tb-text); max-width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tb-user-role { font-size: .67rem; font-weight: 600; color: var(--fmv-green); }

        .tb-chevron { color: var(--tb-muted); transition: transform .2s; margin-left: 2px; }
        .tb-user-wrap[aria-expanded="true"] .tb-chevron { transform: rotate(180deg); }

        .tb-dropdown-menu {
            min-width: 230px; border: 1px solid var(--card-border) !important;
            border-radius: 14px !important; box-shadow: 0 8px 32px rgba(15,23,42,.12) !important;
            padding: 6px !important; background: var(--card-bg) !important;
        }
        html[data-bs-theme="dark"] .tb-dropdown-menu { background: #1e293b !important; border-color: rgba(255,255,255,.08) !important; }

        .tb-dropdown-header { padding: 10px 12px 12px; margin-bottom: 4px; border-bottom: 1px solid var(--divider); }
        .tb-dropdown-user-name { font-size: .84rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tb-dropdown-user-email { font-size: .72rem; color: var(--text-muted); margin-top: 2px; }

        .fmv-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 999px; margin-top: 6px;
            background: var(--fmv-green-light); color: var(--fmv-green);
            font-size: .68rem; font-weight: 700; border: 1px solid rgba(0,104,111,.18);
        }

        .tb-dropdown-item {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px;
            border-radius: 9px; font-size: .83rem; font-weight: 500;
            color: var(--text-main) !important; text-decoration: none;
            cursor: pointer; transition: background .15s, color .15s;
        }
        .tb-dropdown-item:hover { background: var(--fmv-green-light) !important; color: var(--fmv-green) !important; }
        .tb-dropdown-item.danger:hover { background: rgba(220,38,38,.08) !important; color: #dc2626 !important; }
        .tb-dropdown-item i { width: 16px; flex-shrink: 0; text-align: center; font-size: .85rem; }

        .tb-divider { border: none; border-top: 1px solid var(--divider); margin: 4px 0; }

        .fmv-page { flex: 1; padding: 24px 22px 32px; }

        html[data-bs-theme="dark"] .card { background: var(--card-bg) !important; border-color: var(--card-border) !important; }
        html[data-bs-theme="dark"] .card-header, html[data-bs-theme="dark"] .card-footer { background: rgba(255,255,255,.02) !important; border-color: var(--card-border) !important; }

        .btn-iso {
            --falcon-btn-color: #fff; --falcon-btn-bg: #00686f; --falcon-btn-border-color: #00686f;
            --falcon-btn-hover-color: #fff; --falcon-btn-hover-bg: #004d52; --falcon-btn-hover-border-color: #004d52;
            --falcon-btn-active-color: #fff; --falcon-btn-active-bg: #004d52; --falcon-btn-active-border-color: #004d52;
            --falcon-btn-disabled-color: #fff; --falcon-btn-disabled-bg: #00686f; --falcon-btn-disabled-border-color: #00686f;
        }

        #fmvOverlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.48); z-index: 1049;
            backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
        }
        #fmvOverlay.show { display: block; }

        @media (max-width: 1199.98px) {
            #fmvSidebar { transform: translateX(-100%); transition: transform .28s cubic-bezier(.4,0,.2,1), background-color .25s ease; }
            #fmvSidebar.mobile-open { transform: translateX(0); }
            #fmvContent { margin-left: 0 !important; }
            .fmv-layout.sb-collapsed #fmvContent { margin-left: 0 !important; }
            .tb-hamburger { display: inline-flex; }
            .tb-logo-mobile { display: flex; }
            .tb-sep, .tb-welcome { display: none !important; }
            .admin-filter-bar { top: var(--tb-height); }
        }

        @media (max-width: 767.98px) {
            .fmv-page { padding: 16px 14px 24px; }
            .tb-user-info { display: none; }
            .tb-user-wrap { padding: 4px; border-radius: 50%; border: none; background: transparent; }
            .tb-user-wrap:hover { background: transparent; }
            .tb-chevron { display: none; }
            .admin-filter-bar { flex-wrap: wrap; }
            .admin-filter-sel { min-width: 140px; max-width: 100%; }
        }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,104,111,.22); border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,104,111,.4); }
    </style>
</head>

<body>
    <div id="fmvOverlay"></div>

    <div class="fmv-layout" id="fmvLayout">

        <!-- ============================================================
             SIDEBAR
             ============================================================ -->
        <aside id="fmvSidebar">

            <div class="sb-brand">
                <div class="sb-brand-icon">
                    <img src="../img/FULMUV-LOGO-60X60.png" alt="FULMUV">
                </div>
                <div class="sb-brand-text">
                    <div class="sb-brand-name">FULMUV</div>
                    <div class="sb-brand-sub">Panel Admin</div>
                </div>
                <button class="sb-toggle" id="sbToggleBtn" title="Contraer menú" aria-label="Contraer menú">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="sb-body">
                <ul class="sb-nav">
                    <!-- HOME -->
                    <?php
                    $show_home = false;
                    foreach ($permisos as $v) {
                        if (($v["permiso"] === "Dashboard" && $v["valor"] === "true") ||
                            ($v["permiso"] === "Ordenes"   && $v["valor"] === "true")) {
                            $show_home = true; break;
                        }
                    }
                    if ($show_home):
                    ?>
                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= ($menu === "dashboard" || $menu === "home") ? 'active' : '' ?>" href="<?= htmlspecialchars($dashboard, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="sb-nav-icon"><i class="fas fa-home"></i></span>
                            <span class="sb-nav-text">Home</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- ───── ADMINISTRACIÓN ───── -->
                <div class="sb-section-label">Administración</div>
                <ul class="sb-nav">

                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Usuarios" && $v["valor"] === "true"): ?>
                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "usuarios" ? 'active' : '' ?>" href="usuarios.php">
                            <span class="sb-nav-icon"><i class="fas fa-users"></i></span>
                            <span class="sb-nav-text">Usuarios</span>
                        </a>
                    </li>
                    <?php endif; endforeach; ?>

                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Empresas" && $v["valor"] === "true"): ?>
                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $menu === "empresas" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-empresas',this)" aria-expanded="<?= $menu === 'empresas' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="fas fa-city"></i></span>
                            <span class="sb-nav-text">Empresas</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'empresas' ? 'open' : '' ?>" id="sub-empresas">
                            <li><a class="sb-submenu-link <?= $sub_menu === "empresas" ? 'active' : '' ?>" href="empresas.php">Ver empresas</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "crear_empresa" ? 'active' : '' ?>" href="crear_empresa.php">Crear empresa</a></li>
                        </ul>
                    </li>
                    <?php endif; endforeach; ?>

                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Membresias" && $v["valor"] === "true"): ?>
                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "membresias" ? 'active' : '' ?>" href="membresias.php">
                            <span class="sb-nav-icon"><i class="fas fa-id-card-alt"></i></span>
                            <span class="sb-nav-text">Membresías</span>
                        </a>
                    </li>
                    <?php endif; endforeach; ?>

                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "verificacion" ? 'active' : '' ?>" href="verificacion_empresas.php">
                            <span class="sb-nav-icon"><i class="fas fa-clipboard-check"></i></span>
                            <span class="sb-nav-text">Verificación</span>
                        </a>
                    </li>

                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Usuarios" && $v["valor"] === "true"): ?>
                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "agentes" ? 'active' : '' ?>" href="agentes.php">
                            <span class="sb-nav-icon"><i class="fas fa-headset"></i></span>
                            <span class="sb-nav-text">Agentes</span>
                        </a>
                    </li>
                    <?php endif; endforeach; ?>

                </ul>

                <!-- ───── EMPRESA ACTIVA ───── -->
                <div class="sb-section-label">Empresa Activa</div>
                <ul class="sb-nav">

                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Empresas" && $v["valor"] === "true"): ?>
                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "sucursales" ? 'active' : '' ?>" href="sucursales.php">
                            <span class="sb-nav-icon"><i class="fas fa-store"></i></span>
                            <span class="sb-nav-text">Sucursales</span>
                        </a>
                    </li>
                    <?php endif; endforeach; ?>

                    <?php foreach ($permisos as $v): if ($v["permiso"] === "Productos" && $v["valor"] === "true"): ?>
                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $menu === "productos" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-productos',this)" aria-expanded="<?= $menu === 'productos' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="fas fa-cubes"></i></span>
                            <span class="sb-nav-text">Productos</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'productos' ? 'open' : '' ?>" id="sub-productos">
                            <li><a class="sb-submenu-link <?= $sub_menu === "crear_producto" ? 'active' : '' ?>" href="crear_producto.php">Crear producto</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "productos" ? 'active' : '' ?>" href="productos.php">Ver productos</a></li>
                        </ul>
                    </li>

                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $menu === "servicios" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-servicios',this)" aria-expanded="<?= $menu === 'servicios' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="fas fa-tools"></i></span>
                            <span class="sb-nav-text">Servicios</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'servicios' ? 'open' : '' ?>" id="sub-servicios">
                            <li><a class="sb-submenu-link <?= $sub_menu === "crear_servicio" ? 'active' : '' ?>" href="crear_servicio.php">Crear servicio</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "servicios" ? 'active' : '' ?>" href="servicios.php">Ver servicios</a></li>
                        </ul>
                    </li>

                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $menu === "vehiculos" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-vehiculos',this)" aria-expanded="<?= $menu === 'vehiculos' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="fas fa-car-alt"></i></span>
                            <span class="sb-nav-text">Vehículos</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'vehiculos' ? 'open' : '' ?>" id="sub-vehiculos">
                            <li><a class="sb-submenu-link <?= $sub_menu === "crear_vehiculo" ? 'active' : '' ?>" href="crear_vehiculo.php">Crear vehículo</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "vehiculos" ? 'active' : '' ?>" href="vehiculos.php">Ver vehículos</a></li>
                        </ul>
                    </li>

                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "eventos" ? 'active' : '' ?>" href="eventos.php">
                            <span class="sb-nav-icon"><i class="far fa-calendar-alt"></i></span>
                            <span class="sb-nav-text">Eventos</span>
                        </a>
                    </li>

                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $menu === "empleos" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-empleos',this)" aria-expanded="<?= $menu === 'empleos' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="fas fa-briefcase"></i></span>
                            <span class="sb-nav-text">Empleos</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'empleos' ? 'open' : '' ?>" id="sub-empleos">
                            <li><a class="sb-submenu-link <?= $sub_menu === "crear_empleo" ? 'active' : '' ?>" href="crear_empleo.php">Crear empleo</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "empleos" ? 'active' : '' ?>" href="empleos.php">Ver empleos</a></li>
                        </ul>
                    </li>

                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "galeria" ? 'active' : '' ?>" href="galeria.php">
                            <span class="sb-nav-icon"><i class="fas fa-images"></i></span>
                            <span class="sb-nav-text">Galería</span>
                        </a>
                    </li>
                    <?php endif; endforeach; ?>

                </ul>

                <!-- ───── GENERAL ───── -->
                <?php foreach ($permisos as $v): if ($v["permiso"] === "Productos" && $v["valor"] === "true"): ?>
                <div class="sb-section-label">General</div>
                <ul class="sb-nav">
                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $menu === "general" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-general',this)" aria-expanded="<?= $menu === 'general' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="far fa-file-alt"></i></span>
                            <span class="sb-nav-text">General</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'general' ? 'open' : '' ?>" id="sub-general">
                            <li><a class="sb-submenu-link <?= $sub_menu === "categorias_principales" ? 'active' : '' ?>" href="categorias_principales.php">Cat. Principales</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "categorias" ? 'active' : '' ?>" href="categorias.php">Categorías</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "sub_categorias" ? 'active' : '' ?>" href="sub_categorias.php">SubCategorías</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "nombres_productos" ? 'active' : '' ?>" href="nombres_productos.php">Nombres Productos</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "nombres_servicios" ? 'active' : '' ?>" href="nombres_servicios.php">Nombres Servicios</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "atributos" ? 'active' : '' ?>" href="atributos.php">Atributos</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "modelos_autos" ? 'active' : '' ?>" href="modelos_autos.php">Modelos Autos</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "marcas" ? 'active' : '' ?>" href="marcas.php">Marcas</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "publicidad" ? 'active' : '' ?>" href="publicidad.php">Publicidad</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "banner" ? 'active' : '' ?>" href="banner.php">Banner</a></li>
                        </ul>
                    </li>
                </ul>
                <?php endif; endforeach; ?>

                <!-- ───── E-MAIL ───── -->
                <?php foreach ($permisos as $v): if ($v["permiso"] === "E-mail" && $v["valor"] === "true"): ?>
                <div class="sb-section-label">Comunicación</div>
                <ul class="sb-nav">
                    <li class="sb-nav-item">
                        <button class="sb-nav-link <?= $menu === "email" ? 'active' : '' ?>"
                            onclick="sbToggleSub('sub-email',this)" aria-expanded="<?= $menu === 'email' ? 'true' : 'false' ?>">
                            <span class="sb-nav-icon"><i class="fas fa-envelope-open"></i></span>
                            <span class="sb-nav-text">E-mail</span>
                            <span class="sb-nav-arrow"><i class="fas fa-chevron-right" style="font-size:.7rem;"></i></span>
                        </button>
                        <ul class="sb-submenu <?= $menu === 'email' ? 'open' : '' ?>" id="sub-email">
                            <li><a class="sb-submenu-link <?= $sub_menu === "crear_email" ? 'active' : '' ?>" href="crear_email.php">Crear E-mail</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "emails" ? 'active' : '' ?>" href="emails.php">Ver E-mails</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "default" ? 'active' : '' ?>" href="default_emails.php">Default</a></li>
                            <li><a class="sb-submenu-link <?= $sub_menu === "configurar_email" ? 'active' : '' ?>" href="header_footer.php">Configurar</a></li>
                        </ul>
                    </li>
                </ul>
                <?php endif; endforeach; ?>

                <!-- ───── ÓRDENES ───── -->
                <?php foreach ($permisos as $v): if ($v["permiso"] === "Ordenes" && $v["valor"] === "true"): ?>
                <div class="sb-section-label">Operaciones</div>
                <ul class="sb-nav">
                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $sub_menu === "ordenes_fulmuv" ? 'active' : '' ?>" href="ordenes_fulmuv.php">
                            <span class="sb-nav-icon"><i class="fas fa-receipt"></i></span>
                            <span class="sb-nav-text">Órdenes Fulmuv</span>
                        </a>
                    </li>
                    <li class="sb-nav-item">
                        <a class="sb-nav-link <?= $menu === "refund" ? 'active' : '' ?>" href="refund.php">
                            <span class="sb-nav-icon"><i class="fas fa-wallet"></i></span>
                            <span class="sb-nav-text">Refund</span>
                        </a>
                    </li>
                </ul>
                <?php endif; endforeach; ?>

            </div><!-- /sb-body -->
        </aside><!-- /sidebar -->

        <!-- ============================================================
             CONTENIDO PRINCIPAL
             ============================================================ -->
        <div id="fmvContent">

            <!-- TOPBAR -->
            <header id="fmvTopbar">

                <button class="tb-hamburger" id="tbHamburger" aria-label="Abrir menú">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <a class="tb-logo-mobile" href="<?= htmlspecialchars($dashboard, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="sb-brand-icon"><img src="../img/FULMUV-LOGO-60X60.png" alt="FULMUV"></div>
                    <span>FULMUV</span>
                </a>

                <div class="tb-sep d-none d-xl-block"></div>
                <div class="tb-welcome d-none d-xl-flex">
                    <span class="tb-welcome-hi">Panel Admin</span>
                    <span class="tb-welcome-name"><?= htmlspecialchars($nombre_display, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="tb-spacer"></div>

                <div class="tb-actions">
                    <button id="tbThemeBtn" class="tb-theme-btn" aria-label="Cambiar tema" title="Cambiar tema">
                        <svg id="tbIconSun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg id="tbIconMoon" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>

                    <div class="dropdown">
                        <a class="tb-user-wrap" id="tbUserDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false" href="#" role="button">
                            <img class="tb-user-avatar img-perfil-dinamica"
                                src="<?= htmlspecialchars($imagen_principal_src, ENT_QUOTES, 'UTF-8') ?>"
                                onerror="this.onerror=null;this.src='../img/FULMUV-LOGO-60X60.png';" alt="Perfil">
                            <div class="tb-user-info d-none d-md-flex">
                                <span class="tb-user-name"><?= htmlspecialchars($nombre_display, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="tb-user-role">
                                    <i class="fas fa-shield-alt" style="font-size:.55rem;"></i>
                                    <?= htmlspecialchars($nombre_rol_user, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <svg class="tb-chevron d-none d-md-block" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </a>

                        <div class="dropdown-menu tb-dropdown-menu dropdown-menu-end" aria-labelledby="tbUserDropdown">
                            <div class="tb-dropdown-header">
                                <div class="tb-dropdown-user-name"><?= htmlspecialchars($nombre_display, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="tb-dropdown-user-email"><?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="fmv-badge"><i class="fas fa-shield-alt" style="font-size:.6rem;"></i> Admin</div>
                            </div>
                            <a class="tb-dropdown-item" href="#" id="btnAbrirConfiguracion">
                                <i class="fas fa-cog"></i> Configuración
                            </a>
                            <div class="tb-divider"></div>
                            <a class="tb-dropdown-item danger" href="login.php">
                                <i class="fas fa-power-off"></i> Cerrar sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- BARRA FILTRO EMPRESA / SUCURSAL (ocultada en páginas de creación) -->
            <?php if (empty($hide_filter_bar)): ?>
            <div class="admin-filter-bar" id="adminFilterBar">
                <span class="admin-filter-label">
                    <i class="fas fa-filter"></i> Filtrar por:
                </span>
                <div class="admin-filter-sep d-none d-sm-block"></div>
                <select id="lista_empresas" class="admin-filter-sel" title="Seleccione empresa">
                    <option value="">— Empresa —</option>
                </select>
                <select id="lista_sucursales" class="admin-filter-sel" title="Seleccione sucursal">
                    <option value="">— Todas las sucursales —</option>
                </select>
                <span class="admin-filter-label ms-1" id="admin_empresa_badge" style="display:none;font-size:.72rem;background:var(--fmv-green-light);color:var(--fmv-green);padding:3px 10px;border-radius:999px;border:1px solid rgba(0,104,111,.2);">
                    <i class="fas fa-building"></i> <span id="admin_empresa_nombre"></span>
                </span>
            </div>
            <?php endif; ?>

            <!-- INPUTS OCULTOS DE SESIÓN -->
            <input type="hidden" id="id_principal" value="<?= (int)$id_usuario ?>">
            <input type="hidden" id="id_rol_principal" value="<?= (int)$rol_id ?>">
            <input type="hidden" id="nombre_rol_principal" value="<?= htmlspecialchars($nombre_rol_user, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="username_principal" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="id_empresa" value="<?= (int)$id_empresa ?>">
            <input type="hidden" id="id_sucursal_admin" value="0">
            <input type="hidden" id="imagen_principal" value="<?= htmlspecialchars($imagen_principal_src, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="id_carrito" value="carrito_<?= (int)$id_usuario ?>_0">

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
                            <div class="text-center mb-4" style="position:relative;display:inline-block;width:100%;">
                                <div style="position:relative;display:inline-block;">
                                    <img id="imagenConfigModal" class="rounded-circle img-perfil-dinamica"
                                        src="../img/FULMUV-LOGO-60X60.png"
                                        onerror="this.onerror=null;this.src='../img/FULMUV-LOGO-60X60.png';"
                                        width="90" height="90"
                                        style="object-fit:cover;border:3px solid var(--fmv-green);cursor:pointer;"
                                        onclick="document.getElementById('imgFileInput').click()" title="Cambiar foto">
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
