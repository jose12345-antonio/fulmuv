function SweetAlert(tipo, msg, url) {
        if (url === undefined) url = 'home.php';
        if (tipo == "success") {
                swal({
                        title: "Correcto",
                        text: msg,
                        type: "success"
                });
        } else if (tipo == "warning") {
                swal({
                        title: "Advertencia",
                        text: msg,
                        type: "warning"
                });
        } else if (tipo == "error") {
                swal({
                        title: "Error",
                        text: msg,
                        type: "error"
                });
        } else if (tipo == "error_time") {
                swal({
                        position: "top-end",
                        type: "error",
                        title: msg,
                        showConfirmButton: false,
                        timer: 1500
                });
        } else if (tipo == "success_time") {
                swal({
                        position: "top-end",
                        type: "success",
                        title: msg,
                        showConfirmButton: false,
                        timer: 1500
                });
        } else if (tipo == "url_success") {
                swal({
                        title: "Correcto",
                        text: msg,
                        type: "success",
                        confirmButtonColor: "#3d3d3d",
                        confirmButtonText: "Ok",
                        closeOnConfirm: false
                }, function () {
                        window.location.href = url;
                });
        } else if (tipo == "url_error") {
                swal({
                        title: "Error",
                        text: msg,
                        type: "error",
                        confirmButtonColor: "#3d3d3d",
                        confirmButtonText: "Ok",
                        closeOnConfirm: false
                }, function () {
                        window.location.href = url;
                });
        }
}

