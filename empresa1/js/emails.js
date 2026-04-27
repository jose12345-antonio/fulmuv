$(document).ready(function () {
    $.get('../api/v1/fulmuv/correos/', function (returnedData) {
        var returned = JSON.parse(returnedData)
        if (returned.error == false) {

            returned.data.forEach(correo => {
                $("#lista_emails").append(`
                    <tr class="btn-reveal-trigger">
                        <td class="name align-middle white-space-nowrap py-2">
                            <h5 class="mb-0 fs-10">${correo.titulo}</h5>  
                        </td>
                        <td class="align-middle py-2">${correo.descripcion}</td>
                        <td class="align-middle white-space-nowrap">${correo.created_at}</td>
                        <td class="align-middle white-space-nowrap py-2 text-end">
                            <div class="dropdown font-sans-serif position-static">
                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                            <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                                <div class="py-2">
                                <a class="dropdown-item" onclick="editEmail(${correo.id_correo})">Editar</a>
                                <a class="dropdown-item text-danger" onclick="remove(${correo.id_correo},'correos')">Eliminar</a>
                                </div>
                            </div>
                            </div>
                        </td>
                    </tr>
                `);
            });

            $("#my_table").DataTable({
                "searching": true,
                "responsive": false,
                "pageLength": 8,
                "info": true,
                "lengthChange": false,
                "language": {
                    "url": "http://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
                    "paginate": {
                        "next": "<span class=\"fas fa-chevron-right\"></span>",
                        "previous": "<span class=\"fas fa-chevron-left\"></span>"
                    }
                },
                "dom": "<'row mx-0'<'col-md-6'l><'col-md-6'f>>" + "<'table-responsive scrollbar'tr>" + "<'row g-0 align-items-center justify-content-center justify-content-sm-between'<'col-auto mb-2 mb-sm-0 px-3'i><'col-auto px-3'p>>"
            })
        } else {
            SweetAlert("error", returned["msg"]);
        }

    });

});

document.addEventListener('focusin', (e) => {
    if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
      e.stopImmediatePropagation();
    }
});

function editEmail(id_correo) {
    $.get('../api/v1/fulmuv/correos/'+id_correo, function (returnedData) {
        var returned = JSON.parse(returnedData)
        if (returned.error == false) {
            $("#alert").text("");
            $("#alert").append(`
                <button id="btnModal" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="display:none;">Launch static backdrop modal</button>
                <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                    <div class="modal-content border-0">
                        <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                        <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                        <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                            <h4 class="mb-1" id="staticBackdropLabel">Editar E-mail</h4>
                        </div>
                        <div class="p-0">
                            <div class="border border-top-0 border-200">
                                <input value="${returned.data.titulo}" class="form-control border-0 rounded-0 outline-none px-x1" id="email-subject" type="text" maxlength="300" aria-describedby="email-subject" placeholder="Asunto" />
                            </div>
                            <div class="border border-y-0 border-200">
                                <input value="${returned.data.descripcion}" class="form-control border-0 rounded-0 outline-none px-x1" id="email-descripcion" type="text" maxlength="300" aria-describedby="email-descripcion" placeholder="Descripción">
                            </div>
                            <div class="min-vh-50 email-compose-textarea">
                                <textarea class=" d-none" id="email-body" name="content"></textarea>
                            </div>
                        </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
                            <button class="btn btn-primary" type="button" onclick="updateCorreo(${id_correo})">Actualizar</button>
                        </div>
                    </div>
                    </div>
                </div>
            `);
            $("#email-body").addClass("tinymce");
            $("#email-body").attr("data-tinymce", "data-tinymce");
            if (tinymce.get("email-body")) {
                // Destruir la instancia existente
                tinymce.get("email-body").destroy();
                console.log("q")
            }
            tinymceInit()

            if (tinymce.get("email-body")) {
                // Destruir la instancia existente
                console.log("n")
                setTimeout(() => {
                    tinymce.get("email-body").setContent(returned.data.cuerpo);
                }, 1250); // Esperar 200ms
            }
            
            //tinymceInit()
            // Inicializa TinyMCE con el nuevo contenido
/*             tinymce.init({
                selector: '#email-body',
                height: 300,
                menubar: false,
                // plugins: [
                //     'advlist autolink lists link image charmap print preview anchor',
                //     'searchreplace visualblocks code fullscreen',
                //     'insertdatetime media table paste code help wordcount'
                // ],
                //toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                init_instance_callback: function (editor) {
                    editor.setContent(returned.data.cuerpo); // Establece el valor dinámico aquí
                }
            }); */

            // Asegúrate de usar el ID correcto
            //tinymce.get("email-body").setContent('<h1><strong>Hola, tu orden ha sido completada.</strong></h1><p><strong><img src="https://t3.ftcdn.net/jpg/00/48/46/22/360_F_48462228_vKbgWrwJIUWSNCoUyyUxJ4xF1h8Bqbkb.jpg" alt="" width="360" height="360"></strong></p><table style="border-collapse: collapse; width: 100%;" border="1"><colgroup><col style="width: 50%;"><col style="width: 50%;"></colgroup><tbody><tr><td>test</td><td>tests</td></tr><tr><td>tatsta</td><td>asdas</td></tr></tbody></table>');
            $("#btnModal").click();
        }
    });
  }

function remove(id, tabla) {
    swal({
        title: "Alerta",
        text: "El registro se va a eliminar para siempre. ¿Está seguro que desea continuar?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#27b394",
        confirmButtonText: "Sí",
        cancelButtonText: 'No',
        closeOnConfirm: false
    }, function () {
        $.post('../api/v1/fulmuv/' + tabla + '/delete', {
            id: id
        }, function (returnedData) {
            var returned = JSON.parse(returnedData)
            if (returned.error == false) {
                SweetAlert("url_success", returned.msg, "emails.php")
            } else {
                SweetAlert("error", returned.msg)
            }
        });
    });
}

function updateCorreo(id_correo){
    let titulo = $('#email-subject').val();
    let descripcion = $('#email-descripcion').val();
    let cuerpo = tinymce.get("email-body").getContent();
    if (!titulo || !cuerpo) {
        SweetAlert("error", "El asunto y el cuerpo son campos requeridos.");
    } else {
        $.post('../api/v1/fulmuv/correos/update', {
            id_correo: id_correo,
            titulo: titulo,
            descripcion: descripcion,
            cuerpo: cuerpo,
        }, function (returnedData) {
            var returned = JSON.parse(returnedData)
            if (returned.error == false) {
                SweetAlert("url_success", returned.msg, "emails.php")
            } else {
                SweetAlert("error", returned.msg)
            }
        });
    }
}