$(document).ready(function () {

    $.post('../api/v1/fulmuv/usuarios/', {
        id_principal: $("#id_principal").val(),
        id_empresa: $("#id_empresa").val()
    }, function (returnedData) {
        var returned = JSON.parse(returnedData)
        if (returned.error == false) {

            usuarios = returned.data;
            usuarios.forEach(function (data) {
                $("#userNew").append(`
                    <option value="${data.id_usuario}">${data.nombres}</option>
                `);
            })

            $.get('../api/v1/fulmuv/correos/getCorreosDefault', {}, function (returnedData) {
                proyecto = JSON.parse(returnedData);
                if (proyecto["error"] == false) {
                    proyecto['data'].forEach(function (info2, i) {

                        $('#contenedor').append(`
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="card-pricing-plan-tag">Usuario ${i + 1}</div>
                                    <i class="far fa-envelope text-primary fs-5 mb-2"></i>
                                    <select id="userCorreo${i}" class="form-select form-select-sm" data-idDefault="${info2["id_correo_default"]}">
                                    </select>
                                    <button class="btn btn-iso mt-4 mb-2 rounded-pill" onclick="guarda(${i})">Elegir usuario</button>
                                </div>
                            </div> 
                        </div> 
                        `);
                        usuarios.forEach(function (usuario) {
                            $('#userCorreo' + i).append(`
                                <option value="${usuario.id_usuario}">${usuario.nombres}</option>
                            `);
                            $('#userCorreo' + i).val(info2.id_usuario);
                            $('#userCorreo' + i).select2();
                        });
                    });

                    $('#contenedor').append(`
                    <div class="col-md-4 pb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="border-dashed border-2 border h-100 w-100 rounded d-flex align-items-center justify-content-center">
                                    <a href="javascript:void(0);" class="text-center text-muted p-2" data-bs-toggle="modal" data-bs-target="#modal_add">
                                        <i class="fas fa-plus h3 my-0"></i> <h4 class="font-16 mt-1 mb-0 d-block">Agregar nuevo E-mail</h4>
                                    </a>
                                </div>
                            </div> 
                        </div>
                    </div>
                    `);
                }
            });
            $('#userNew').select2({
                dropdownParent: $("#modal_add")
            });
        }
    });
});

function guarda(id_select) {
    var select = document.getElementById('userCorreo' + id_select);
    console.log(select)
    var id_default = select.dataset.iddefault;
    $.post('../api/v1/fulmuv/correos/updateCorreoDefault', {
        id_correo_default: id_default,
        id_usuario: select.value
    }, function (returnedData) {
        returned = JSON.parse(returnedData);
        if (returned["error"] == false) {
            SweetAlert("url_success", returned["msg"], "default_emails.php");
        } else {
            SweetAlert("error", returned["msg"]);
        }
    });
}

/* AGREGAR E-MAIL POR DEFECTO EN LA EMPRESA */
function agregar() {
    console.log($('#userNew').val())

    $.post('../api/v1/fulmuv/correos/createCorreoDefault', {
        id_usuario: $('#userNew').val(),
    }, function (returnedData) {
        returned = JSON.parse(returnedData);
        if (returned["error"] == false) {
            SweetAlert("url_success", returned["msg"], "default_emails.php");
        } else {
            SweetAlert("error", returned["msg"]);
        }
    });
}
