function guarda() {

    let titulo = $('#email-subject').val();
    let descripcion = $('#email-descripcion').val();
    let cuerpo = $('#email-body').val();

    if (!titulo || !cuerpo) {
        SweetAlert("error", "El asunto y el cuerpo son campos requeridos.");
    } else {
        $.post('../api/v1/fulmuv/correos/create', {
            titulo: titulo,
            descripcion: descripcion,
            cuerpo: cuerpo,
        }, function (returnedData) {
            returned = JSON.parse(returnedData);
            if (returned["error"] == false) {
                SweetAlert("url_success", returned["msg"], "emails.php");
            } else {
                SweetAlert("error", returned["msg"]);
            }
        });
    }
}