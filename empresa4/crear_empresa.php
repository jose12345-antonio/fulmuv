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
            padding: 14px 28px;
            margin: 0;
            background: linear-gradient(135deg, #004e60 0%, #0f766e 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            box-shadow: 0 14px 30px rgba(0, 78, 96, 0.22);
            transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
        }

        .tok_btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(0, 78, 96, 0.28);
        }

        .tok_btn:disabled {
            opacity: .7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        #modal-pago .modal-dialog {
            max-width: 1180px;
        }

        #modal-pago .modal-content {
            border-radius: 24px;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(0, 78, 96, 0.12), transparent 28%),
                linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.18);
        }

        .payment-modal-header {
            padding: 24px 28px 20px;
            background:
                linear-gradient(135deg, rgba(0, 78, 96, 0.96) 0%, rgba(15, 118, 110, 0.96) 100%);
            color: #fff;
        }

        .payment-modal-title {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .payment-modal-subtitle {
            margin: 6px 0 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 14px;
            line-height: 1.6;
        }

        .payment-modal-body {
            padding: 26px 28px 30px !important;
        }

        .payment-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
            gap: 22px;
            align-items: start;
        }

        .payment-main-column,
        .payment-side-column {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .payment-card-modern {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            padding: 22px;
        }

        .payment-section-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 14px;
        }

        .payment-total-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(0, 78, 96, 0.08) 0%, rgba(15, 118, 110, 0.12) 100%);
            border: 1px solid rgba(0, 78, 96, 0.12);
        }

        .payment-total-label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            margin-bottom: 4px;
        }

        .payment-total-value {
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
            color: #004e60;
        }

        .payment-help-text {
            font-size: 13px;
            line-height: 1.7;
            color: #64748b;
            margin: 0;
        }

        .payment-consent {
            padding: 18px 18px 14px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.22);
        }

        .payment-consent .form-check-input {
            margin-top: .25rem;
        }

        .payment-consent .form-check-label {
            color: #334155;
            line-height: 1.75;
        }

        .payment-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .payment-inline-note {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        #checkoutResumenPromo .card {
            border: 1px solid rgba(15, 23, 42, 0.08) !important;
            border-radius: 22px !important;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            margin-bottom: 0 !important;
        }

        #checkoutResumenPromo .card-body {
            padding: 24px !important;
            background:
                radial-gradient(circle at top right, rgba(0, 78, 96, 0.08), transparent 34%),
                linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        }

        #checkoutResumenPromo h6 {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px !important;
        }

        #checkoutResumenPromo .row>.col-12,
        #checkoutResumenPromo .row>.col-md-6 {
            position: relative;
        }

        #checkoutResumenPromo .row>.col-12:not(:last-child)::after,
        #checkoutResumenPromo .row>.col-md-6:first-child::after {
            content: "";
            position: absolute;
            right: 0;
            top: 8px;
            bottom: 8px;
            width: 1px;
            background: linear-gradient(180deg, transparent 0%, rgba(148, 163, 184, 0.35) 15%, rgba(148, 163, 184, 0.35) 85%, transparent 100%);
        }

        #checkoutResumenPromo .col-12:last-child::after {
            display: none;
        }

        #checkoutResumenPromo strong {
            color: #334155;
        }

        #checkoutResumenPromo .text-primary {
            color: #004e60 !important;
        }

        #tokenize_response {
            min-height: 24px;
        }

        #tokenize_response:empty {
            display: none;
        }

        #modal-pago .modal-footer {
            padding: 0 28px 24px;
            border-top: 0;
            background: transparent;
        }

        #modal-pago .btn-close {
            background-color: rgba(255, 255, 255, 0.88);
            border-radius: 50%;
            opacity: 1;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.14);
        }

        @media (max-width: 991.98px) {
            .payment-shell {
                grid-template-columns: 1fr;
            }

            #checkoutResumenPromo .row>.col-md-6:first-child::after,
            #checkoutResumenPromo .row>.col-12:not(:last-child)::after {
                display: none;
            }

            .payment-total-box {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 767.98px) {
            .payment-modal-header {
                padding: 20px 20px 18px;
            }

            .payment-modal-title {
                font-size: 24px;
            }

            .payment-modal-body {
                padding: 18px 18px 22px !important;
            }

            .payment-card-modern,
            #checkoutResumenPromo .card-body {
                padding: 18px !important;
            }

            .payment-total-value {
                font-size: 28px;
            }

            #modal-pago .modal-footer {
                padding: 0 18px 18px;
            }
        }

        .payment-modal-header {
            position: relative;
            padding: 24px 28px 14px;
        }

        .payment-brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .payment-brand-title {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: .04em;
            margin: 0;
            color: #ffffff;
        }

        .payment-header-close {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
        }

        .payment-header-close:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        .payment-stepper {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin: 0;
            padding: 18px 0 0;
            list-style: none;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
        }

        .payment-stepper li {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            text-align: center;
            color: rgba(255, 255, 255, 0.78);
            font-size: 12px;
            font-weight: 700;
        }

        .payment-stepper-bullet {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
        }

        .payment-stepper li.is-complete .payment-stepper-bullet,
        .payment-stepper li.is-active .payment-stepper-bullet {
            background: #ffffff;
            border-color: #ffffff;
            color: #0b6770;
        }

        .payment-stepper li.is-active {
            color: #ffffff;
        }

        .payment-shell.payment-shell-single {
            display: block;
        }

        .payment-unified-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .payment-unified-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
            padding: 24px;
        }

        .payment-summary-panel {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 18px;
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        }

        .payment-summary-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
            gap: 18px;
            margin-bottom: 18px;
        }

        .payment-summary-hero-card {
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 20px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(0, 78, 96, 0.08) 0%, rgba(15, 118, 110, 0.12) 100%);
        }

        .payment-summary-plan-kicker {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .payment-summary-plan-title {
            font-size: 30px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .payment-summary-plan-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .payment-summary-pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.18);
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
        }

        .payment-summary-total-card {
            border: 1px solid rgba(0, 78, 96, 0.12);
            border-radius: 20px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.06);
        }

        .payment-summary-total-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .payment-summary-total-value {
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
            color: #004e60;
            margin-bottom: 8px;
        }

        .payment-summary-total-note {
            color: #475569;
            font-size: 13px;
            line-height: 1.6;
        }

        .payment-summary-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .payment-summary-panel h6 {
            margin: 0 0 14px;
            font-size: 17px;
            font-weight: 800;
            color: #004e60;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .payment-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .payment-summary-item {
            border-radius: 14px;
            padding: 12px 14px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .payment-summary-item.is-wide {
            grid-column: 1 / -1;
        }

        .payment-summary-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
        }

        .payment-summary-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        .payment-summary-inline {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 14px;
            color: #334155;
        }

        .payment-summary-inline strong {
            color: #0f172a;
        }

        .payment-payment-panel {
            border-top: 1px solid rgba(148, 163, 184, 0.18);
            padding: 24px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.85) 0%, #ffffff 100%);
        }

        .payment-payment-panel .payment-section-title {
            margin-bottom: 18px;
        }

        .payment-payment-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 22px;
            align-items: start;
        }

        .payment-total-aside {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .payment-header-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: rgba(255, 255, 255, 0.92);
            font-size: 13px;
            font-weight: 700;
            margin-top: 10px;
        }

        .payment-header-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .payment-header-meta .payment-header-chip strong {
            color: #ffffff;
        }

        .payment-payment-panel {
            border-top: 1px solid rgba(148, 163, 184, 0.18);
            padding: 24px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.85) 0%, #ffffff 100%);
        }

        .payment-payment-box {
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 20px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
        }

        .payment-actions {
            display: flex;
            align-items: stretch;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .payment-btn-secondary {
            min-height: 50px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-weight: 700;
            padding: 12px 18px;
        }

        #checkoutResumenPromo .card,
        #checkoutResumenPromo .card-body {
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        @media (max-width: 991.98px) {
            .payment-stepper {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .payment-unified-grid,
            .payment-summary-hero,
            .payment-summary-columns,
            .payment-summary-grid,
            .payment-payment-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .payment-brand-title {
                font-size: 24px;
            }

            .payment-unified-grid,
            .payment-payment-panel {
                padding: 18px;
            }

            .payment-header-close {
                width: 38px;
                height: 38px;
            }
        }

        .empresa-wizard-modal .modal-content {
            border-radius: 24px;
            overflow: hidden;
            border: 0;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.18);
        }

        .empresa-wizard-header {
            background: linear-gradient(180deg, #0f6c72 0%, #0d6469 100%);
            color: #fff;
            padding: 22px 28px 16px;
        }

        .empresa-wizard-brand {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .empresa-wizard-brand h4 {
            margin: 0;
            font-size: 35px;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .empresa-wizard-brand p {
            margin: 2px 0 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
        }

        .empresa-wizard-close {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-size: 24px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .empresa-wizard-close:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .empresa-wizard-stepper {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            padding-top: 18px;
            border-top: 3px solid rgba(255, 255, 255, 0.25);
        }

        .empresa-wizard-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .empresa-wizard-step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 800;
            background: rgba(255, 255, 255, 0.08);
        }

        .empresa-wizard-step.is-active,
        .empresa-wizard-step.is-complete {
            color: #fff;
        }

        .empresa-wizard-step.is-active .empresa-wizard-step-circle,
        .empresa-wizard-step.is-complete .empresa-wizard-step-circle {
            background: #fff;
            border-color: #fff;
            color: #0d6469;
        }

        .empresa-wizard-body {
            background: #fff;
            max-height: 62vh;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .empresa-wizard-section {
            display: none;
            padding: 24px 28px 18px;
        }

        .empresa-wizard-section.is-active {
            display: block;
        }

        .empresa-wizard-section-title {
            margin: 0 0 18px;
            font-size: 22px;
            font-weight: 800;
            color: #0b4f63;
            letter-spacing: .08em;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }

        .empresa-wizard-section .form-label {
            color: #516179;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empresa-wizard-section .form-control,
        .empresa-wizard-section .form-select {
            min-height: 48px;
            border-radius: 14px;
            border: 1px solid #d7e1ea;
            box-shadow: none;
        }

        .empresa-wizard-section .form-control.is-invalid,
        .empresa-wizard-section .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .12);
        }

        .empresa-wizard-error {
            display: block;
            margin-top: 6px;
            color: #dc3545;
            font-size: 12px;
            font-weight: 600;
        }

        .empresa-wizard-map-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
        }

        .empresa-wizard-map-note {
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
            margin-top: 10px;
        }

        .empresa-wizard-summary-card {
            border: 1px solid #d7e1ea;
            border-radius: 18px;
            padding: 18px;
            background: #f8fbfc;
        }

        .empresa-wizard-consent-title {
            margin: 0;
            color: #0b4f63;
            font-size: 18px;
            font-weight: 800;
        }

        .empresa-wizard-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .empresa-wizard-summary-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 14px;
        }

        .empresa-wizard-summary-item.is-wide {
            grid-column: 1 / -1;
        }

        .empresa-wizard-summary-label {
            display: block;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .empresa-wizard-summary-value {
            color: #0f172a;
            font-size: 16px;
            font-weight: 700;
            word-break: break-word;
        }

        .empresa-wizard-footer {
            background: #f5f9fc;
            border-top: 1px solid #e2e8f0;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .empresa-wizard-step-counter {
            color: #64748b;
            font-weight: 600;
            text-align: center;
            flex: 1;
        }

        .empresa-wizard-footer-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .empresa-wizard-btn-primary {
            min-width: 150px;
            min-height: 46px;
            border-radius: 14px;
            border: 0;
            background: #004e60;
            color: #fff;
            font-weight: 700;
        }

        .empresa-wizard-btn-outline {
            min-width: 120px;
            min-height: 46px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-weight: 700;
        }

        .checkout-reset-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            border-radius: 999px;
            min-height: 40px;
            padding: 0 16px;
            font-weight: 700;
            transition: all .2s ease;
        }

        .checkout-reset-btn:hover {
            border-color: #f59e0b;
            color: #9a3412;
            background: #fff7ed;
        }

        @media (max-width: 991.98px) {
            .empresa-wizard-stepper {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .empresa-wizard-summary-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .empresa-wizard-header,
            .empresa-wizard-section {
                padding-left: 18px;
                padding-right: 18px;
            }

            .empresa-wizard-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .empresa-wizard-footer-actions {
                width: 100%;
                justify-content: space-between;
            }

            .empresa-wizard-brand h4 {
                font-size: 28px;
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
                                    <div class="col-auto fs--1 text-600"><span class="mb-0 undefined">&iquest;Ya tienes una cuenta?</span> <span><a href="login.php">Login</a></span></div>
                                </div>

                                <!-- <div class="card">
                                    <div class="card-body p-4">
                                        <div class="row flex-between-center mb-2">
                                            <div class="col-auto">
                                                <h5>Registro</h5>
                                            </div>
                                            <div class="col-auto fs--1 text-600"><span class="mb-0 undefined">&iquest;Ya tienes una cuenta?</span> <span><a href="login.php">Login</a></span></div>
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
                                            <div class="col-12 mb-4">
                                                <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3">
                                                    <div class="text-center text-md-start flex-grow-1">
                                                        <div class="fs-8">Membresías</div>
                                                        <h4 class="fs-8 mb-0">Inversión mínima, multiplicación máxima. Está en ti. <br class="d-none d-md-block" />Planes para particulares, empresas y negocios.</h4>
                                                    </div>
                                                    <div class="text-center text-md-end">
                                                        <button type="button" class="checkout-reset-btn" id="btnEmpezarDeNuevoCheckout">
                                                            <i class="fas fa-rotate-left"></i>
                                                            <span>Empezar de nuevo</span>
                                                        </button>
                                                    </div>
                                                </div>
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
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable mt-6" role="document">
            <div class="modal-content border-0">
                <div class="modal-body p-0 payment-modal-body">
                    <div class="payment-modal-header">
                        <div class="payment-brand-row">
                            <div>
                                <h4 class="payment-brand-title" id="">FULMUV</h4>
                                <p class="payment-modal-subtitle">Confirma tu información y completa el pago seguro desde un solo flujo.</p>
                                <div class="payment-header-meta">
                                    <div class="payment-header-chip">Membresía: <strong id="paymentHeaderPlan">-</strong></div>
                                    <div class="payment-header-chip">Precio: <strong id="paymentHeaderPrice">$0</strong></div>
                                </div>
                            </div>
                            <button class="payment-header-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar">×</button>
                        </div>
                        <ul class="payment-stepper">
                            <li class="is-complete">
                                <span class="payment-stepper-bullet">1</span>
                                <span>Empresa</span>
                            </li>
                            <li class="is-complete">
                                <span class="payment-stepper-bullet">2</span>
                                <span>Usuario</span>
                            </li>
                            <li class="is-active">
                                <span class="payment-stepper-bullet">3</span>
                                <span>Facturación y pago</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4">

                            <input value="" hidden id="tipo">
                            <input value="" hidden id="id_membresia_producto">

                            <!--1. Create an element to contain the dynamic form.-->
                            <div id='payment_example_div'>
                                <div class="payment-shell payment-shell-single">
                                    <div class="payment-unified-card">
                                        <div class="payment-unified-grid">
                                            <div id="checkoutResumenPromo"></div>
                                        </div>

                                        <div class="payment-payment-panel">
                                            <div class="payment-payment-grid">
                                                <div>
                                                    <div class="payment-payment-box">
                                                        <div class="payment-section-title">Pago seguro</div>
                                                        <div id='tokenize_example'></div>
                                                        <div id="tokenize_response" class="mt-3"></div>

                                                        <div class="row g-3 mt-1">
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

                                                        <div class="mt-3" id="cuotaBox" style="display:none;">
                                                            <small class="text-700">Cuota estimada: <span id="cuotaEstimada">$0.00</span> / mes</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="payment-total-aside">
                                                    <div class="payment-total-box">
                                                        <div>
                                                            <div class="payment-total-label">Total a pagar hoy</div>
                                                            <div class="payment-total-value" id="totalPago">$0</div>
                                                        </div>
                                                        <div class="payment-inline-note">Este valor solo se procesará cuando confirmes el pago final.</div>
                                                    </div>

                                                    <div class="payment-consent">
                                                        <div class="form-check mt-0">
                                                            <input class="form-check-input me-2" id="checkTerminoCondicionesPago" type="checkbox" value="">
                                                            <label class="form-check-label mb-0" for="checkTerminoCondicionesPago">
                                                                Acepto las Condiciones de Uso del Servicio de Pago en Línea de FULMUV y autorizo el cargo recurrente del plan seleccionado a través de la pasarela NUVEI. Entiendo que la renovación puede cancelarse desde mi perfil de vendedor, y confirmo que soy titular o estoy autorizado para usar este medio de pago.
                                                                <p class="mt-2 mb-0"><a href="../documentos/4_Condiciones Pago en Li╬ôo╠Ça╠üuΓòáe╠énea de FULMUV.pdf" target="_blank" class="fs-10 fw-bold">
                                                                        Ver Condiciones de Pago en Línea
                                                                    </a>
                                                                </p>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="payment-actions">
                                                        <button type="button" class="payment-btn-secondary" id="btnEditarInformacionPago">Editar información</button>
                                                        <button id='tokenize_btn' class='tok_btn'>Pagar</button>
                                                        <div class="payment-inline-note">La empresa, el usuario, la facturación y la membresía se registrarán al confirmar este pago.</div>
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
    <script src="js/crear_empresa.js?v1.0.0.0.0.0.0.0.0.1.22"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places" async defer></script>
    <!-- Alerts js -->
    <script src="js/alerts.js"></script>

    <div id="alert">

    </div> 
    <div id="alertMapa">

    </div>

</body>

</html>




