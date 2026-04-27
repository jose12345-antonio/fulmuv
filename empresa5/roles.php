<?php
$menu = "roles";
require 'includes/header.php';
?>
<title>Roles</title>
<div class="row g-2">
    <div class="col-xl-3">

        <div class="card">
            <div class="card-body" id="cardRoles">
                <h5 class="header-title mt-0 mb-3">Roles</h5>
                <p class="text-muted font-13">
                    Rol principal
                </p>
                <div class="col-sm-12 mb-2 mb-sm-0" id="ownerId">
                </div>

                <hr>

                <div class="col-sm-12 mb-2 mb-sm-0">
                    <p class="text-muted font-13">
                        Rol secundario
                    </p>
                </div>
                <div class="col-sm-12 mb-2 mb-sm-0" id="roles">
                </div>
                <button id="add" type="button" class="btn btn-success rounded-pill mt-2" onclick="caja()"><i class="ri-add-circle-line"></i> Add Role </button>
            </div>
        </div>

    </div>

    <div class="col-xl-9">

        <div class="card">
            <div class="card-body">
                <div class="row mb-2" style="display: none;">
                    <div class="col-lg-4">
                        <h5 class="header-title mb-3"> Permisos </h5>
                    </div>
                    <div class="col-lg-8 row">
                        <div class="col-lg-4">
                            <button id="btn_editar" type="button" class="btn btn-warning rounded-pill" onclick="rename()"><i class="ri-pencil-line"></i> Editar </button>
                        </div>
                        <div class="col-lg-4">
                            <button id="btn_eliminar" type="button" class="btn btn-danger rounded-pill" onclick="deleterecord()"><i class="ri-delete-bin-line"></i> Eliminar</button>
                        </div>
                        <div class="col-lg-4">
                            <input type="text" id="nameRole" class="form-control" placeholder="Add Name" data-id="">
                        </div>
                    </div>
                </div>
                <div dir="ltr">
                    <div class="table-responsive">
                        <table class="table table-sm table-centered mb-0 font-14">
                            <thead class="table-light">
                                <tr>
                                    <th>Permiso</th>
                                    <th>Estado</th>
                                    <th>Nivel</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Empresas</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionEmpresas" type="checkbox" onchange="actualizarCampo('valor', 'Empresas')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionEmpresas"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" id="selectEmpresas" data-idPermiso="" onchange="actualizarCampoSelect('levels', 'Empresas')">
                                            <option value="Fulmuv">Fulmuv</option>
                                            <option value="Vendedor">Vendedor</option>
                                            <option value="Empresa">Empresa</option>
                                            <option value="Sucursal">Sucursal</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Usuarios</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionUsuarios" type="checkbox" onchange="actualizarCampo('valor', 'Usuarios')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionUsuarios"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" id="selectUsuarios" data-idPermiso="" onchange="actualizarCampoSelect('levels', 'Usuarios')">
                                            <option value="Fulmuv">Fulmuv</option>
                                            <option value="Vendedor">Vendedor</option>
                                            <option value="Empresa">Empresa</option>
                                            <option value="Sucursal">Sucursal</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Productos</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionProductos" type="checkbox" onchange="actualizarCampo('valor', 'Productos')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionProductos"></label>
                                        </div>
                                    </td>
                                    <td>

                                    </td>
                                </tr>
                                <tr>
                                    <td>Catalogos</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionCatalogos" type="checkbox" onchange="actualizarCampo('valor', 'Catalogos')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionCatalogos"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" id="selectCatalogos" data-idPermiso="" onchange="actualizarCampoSelect('levels', 'Catalogos')">
                                            <option value="Fulmuv">Fulmuv</option>
                                            <option value="Vendedor">Vendedor</option>
                                            <option value="Empresa">Empresa</option>
                                            <option value="Sucursal">Sucursal</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>E-mail</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionE-mail" type="checkbox" onchange="actualizarCampo('valor', 'E-mail')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionE-mail"></label>
                                        </div>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Ordenes</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionOrdenes" type="checkbox" onchange="actualizarCampo('valor', 'Ordenes')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionOrdenes"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" id="selectOrdenes" data-idPermiso="" onchange="actualizarCampoSelect('levels', 'Ordenes')">
                                            <option value="Fulmuv">Fulmuv</option>
                                            <option value="Vendedor">Vendedor</option>
                                            <option value="Empresa">Empresa</option>
                                            <option value="Sucursal">Sucursal</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Dashboard</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionDashboard" type="checkbox" onchange="actualizarCampo('valor', 'Dashboard')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionDashboard"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" id="selectDashboard" data-idPermiso="" onchange="actualizarCampoSelect('levels', 'Dashboard')">
                                            <option value="Fulmuv">Fulmuv</option>
                                            <option value="Vendedor">Vendedor</option>
                                            <option value="Empresa">Empresa</option>
                                            <option value="Sucursal">Sucursal</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Roles</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionRoles" type="checkbox" onchange="actualizarCampo('valor', 'Roles')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionRoles"></label>
                                        </div>
                                    </td>
                                    <td>

                                    </td>
                                </tr>
                                <tr>
                                    <td>Membresias</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" data-idPermiso="" id="editionMembresias" type="checkbox" onchange="actualizarCampo('valor', 'Membresias')" />
                                            <label class="form-check-label" data-on-label="On" data-off-label="Off" for="editionMembresias"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" id="selectMembresias" data-idPermiso="" onchange="actualizarCampoSelect('levels', 'Membresias')">
                                            <option value="Fulmuv">Fulmuv</option>
                                            <option value="Vendedor">Vendedor</option>
                                            <option value="Empresa">Empresa</option>
                                            <option value="Sucursal">Sucursal</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Conexión API js -->
<script src="js/roles.js?v1.0.42"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
<?php
require 'includes/footer.php';
?>