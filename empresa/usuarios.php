<?php
$menu = "usuarios";
$sub_menu = "usuarios";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "Usuarios" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<style>
    .usuarios-page-card {
        border: 1px solid var(--card-border);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .usuarios-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .usuarios-toolbar-copy h5 {
        margin-bottom: .2rem;
    }

    .usuarios-toolbar-copy p {
        margin: 0;
        color: var(--text-muted);
        font-size: .9rem;
    }

    .usuarios-table-wrap {
        padding: 1rem 1rem .35rem;
    }

    .usuarios-table-wrap .dataTables_wrapper .dataTables_filter input {
        min-width: 260px;
        border-radius: 10px;
    }

    .usuarios-table-wrap .dataTables_wrapper .dataTables_filter,
    .usuarios-table-wrap .dataTables_wrapper .dataTables_info,
    .usuarios-table-wrap .dataTables_wrapper .dataTables_paginate {
        padding-inline: .25rem;
    }

    .usuarios-table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .usuarios-table thead th {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        border-bottom-width: 1px;
    }

    .usuarios-table tbody td {
        padding-top: .95rem !important;
        padding-bottom: .95rem !important;
        vertical-align: middle;
        border-color: rgba(148, 163, 184, .18);
    }

    .usuarios-cell-name {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 220px;
    }

    .usuarios-avatar {
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

    .usuarios-name-title {
        font-size: .92rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.2;
    }

    .usuarios-name-meta {
        color: var(--text-muted);
        font-size: .78rem;
        margin-top: .15rem;
    }

    .usuarios-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .38rem .65rem;
        border-radius: 999px;
        background: rgba(0, 104, 111, .09);
        color: var(--fmv-green-dark);
        font-weight: 700;
        font-size: .76rem;
        line-height: 1;
    }

    .usuarios-role-pill {
        background: rgba(15, 23, 42, .08);
        color: var(--text-main);
    }

    .usuarios-actions {
        display: flex;
        justify-content: flex-end;
        gap: .45rem;
        min-width: 152px;
    }

    .usuarios-action-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .usuarios-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--text-muted);
    }

    @media (max-width: 767px) {
        .usuarios-table-wrap {
            padding-inline: .75rem;
        }

        .usuarios-table-wrap .dataTables_wrapper .dataTables_filter input {
            min-width: 100%;
        }

        .usuarios-cell-name {
            min-width: 180px;
        }
    }
</style>
<title>Usuarios</title>
<div class="card mb-3 usuarios-page-card">

    <div class="card-header border-bottom">
        <div class="usuarios-toolbar">
            <div class="usuarios-toolbar-copy">
                <h5 class="fs-9 text-nowrap">Usuarios</h5>
                <p>Administra accesos, roles y acciones rápidas desde una tabla más clara.</p>
            </div>
            <div id="table-customers-replace-element">
                <button onclick="addUsuario()" class="btn btn-falcon-default btn-sm" type="button">
                    <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                    <span class="d-none d-sm-inline-block ms-1">Crear usuario</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="usuarios-table-wrap">
            <table class="table table-sm align-middle usuarios-table" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Nombres</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Usuario</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Correo</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Empresa</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Rol</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Fecha de creación</th>
                        <th class="text-900 align-middle text-end white-space-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody id="lista_usuarios">
                    <tr>
                        <td colspan="7" class="usuarios-empty-state">Cargando usuarios...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/usuarios.js?v1.0.0.0.0.4"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>
