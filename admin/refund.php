<?php
$menu = "refund";
$sub_menu = "refund";
include_once('includes/header.php');
?>
<title>Refund</title>
<style>
    .refund-page-card {
        border: 1px solid var(--card-border);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .refund-toolbar-copy h5 {
        margin-bottom: .2rem;
    }

    .refund-toolbar-copy p {
        margin: 0;
        color: var(--text-muted);
        font-size: .9rem;
    }

    .refund-table-wrap {
        padding: 1rem 1rem .35rem;
    }

    .refund-table-wrap .dataTables_wrapper .dataTables_filter input {
        min-width: 260px;
        border-radius: 10px;
    }

    .refund-table-wrap .dataTables_wrapper .dataTables_filter,
    .refund-table-wrap .dataTables_wrapper .dataTables_info,
    .refund-table-wrap .dataTables_wrapper .dataTables_paginate {
        padding-inline: .25rem;
    }

    .refund-table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .refund-table thead th {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        border-bottom-width: 1px;
        text-align: center !important;
        vertical-align: middle !important;
    }

    .refund-table tbody td {
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        vertical-align: middle;
        border-color: rgba(148, 163, 184, .18);
        text-align: center !important;
    }

    .refund-company {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        min-width: 220px;
    }

    .refund-avatar {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 104, 111, .12);
        color: var(--fmv-green);
        font-weight: 800;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .refund-meta {
        text-align: left;
        line-height: 1.2;
    }

    .refund-meta strong {
        display: block;
        font-size: .92rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .refund-meta span {
        display: block;
        color: var(--text-muted);
        font-size: .78rem;
        margin-top: .15rem;
    }

    .refund-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        padding: .38rem .65rem;
        border-radius: 999px;
        background: rgba(0, 104, 111, .09);
        color: var(--fmv-green-dark);
        font-weight: 700;
        font-size: .76rem;
        line-height: 1;
    }

    .refund-amount {
        font-weight: 800;
        color: var(--text-main);
        white-space: nowrap;
    }

    .refund-amount.refund-target {
        color: var(--fmv-green-dark);
    }

    .refund-transaction-id {
        font-weight: 700;
        color: var(--text-main);
    }

    .refund-transaction-meta {
        font-size: .78rem;
        color: var(--text-muted);
        margin-top: .15rem;
    }

    .refund-actions {
        display: flex;
        justify-content: center;
        gap: .45rem;
        min-width: 72px;
    }

    .refund-action-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .refund-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--text-muted);
    }

    @media (max-width: 767px) {
        .refund-table-wrap {
            padding-inline: .75rem;
        }

        .refund-table-wrap .dataTables_wrapper .dataTables_filter input {
            min-width: 100%;
        }

        .refund-company {
            min-width: 190px;
        }
    }
</style>
<div class="card mb-3 refund-page-card">
    <div class="card-header border-bottom">
        <div class="refund-toolbar-copy">
            <h5 class="fs-9 text-nowrap">Reembolsos de membresía</h5>
            <p>Administra pagos aprobados, estados de refund y membresías inactivadas desde una tabla más clara.</p>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="refund-table-wrap">
            <table class="table table-sm align-middle refund-table" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Empresa</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Membresía</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Valor pagó</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Valor reembolso</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Transacción</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Fecha</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Estado pago</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center">Estado membresía</th>
                        <th class="text-900 align-middle white-space-nowrap text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="lista_refunds">
                    <tr>
                        <td colspan="9" class="refund-empty-state">Cargando pagos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="js/refund.js?v1.1.2"></script>
<script src="js/alerts.js"></script>
<?php
include_once('includes/footer.php');
?>
