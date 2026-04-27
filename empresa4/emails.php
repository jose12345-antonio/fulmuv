<?php
$menu = "email";
$sub_menu = "emails";
require 'includes/header.php';
foreach ($permisos as $value) {
    if ($value["permiso"] == "E-mail" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>
<title>E-mails</title>
<div class="card mb-3">

    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">E-mails</h5>
            </div>
            <div class="col-8 col-sm-auto text-end ps-2">
                <div id="table-customers-replace-element">
                    <a href="crear_email.php" class="btn btn-falcon-default btn-sm" type="button">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="falcon-data-table">
            <table class="table table-sm mb-0 data-table fs-10" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Subject</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Descripción</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Fecha de creación</th>
                        <th class="align-middle no-sort"></th>
                    </tr>
                </thead>
                <tbody id="lista_emails">

                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="js/emails.js"></script>

<?php
require 'includes/footer.php';
?>