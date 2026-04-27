$(document).ready(function () {

    $.get('../api/v1/fulmuv/correos/getContenedor', function (returnedData) {
        var returned = JSON.parse(returnedData)
        if (returned["error"] == false) {

            contenedor = returned.data
            $("#cuerpoImagen").attr("src", contenedor.imagen);
            // Establece el valor de data-img y src
            $('#cuerpoImagen').attr('data-img', contenedor.imagen).attr('src', contenedor.imagen);

            $("#btnView").attr("href", contenedor.imagen);
            $("#btnDownload").attr("href", contenedor.imagen);

            $("#cuerpoImagen").css('background-color', contenedor.color);
            $("#cuerpoColor").val(contenedor.color);

        } else {
            SweetAlert("error", returned["msg"]);
        }
    });

    $.get('../api/v1/fulmuv/correos/', function (returnedData) {
        var returned = JSON.parse(returnedData)
        if (returned["error"] == false) {
            returned["data"].forEach(function (data) {
                $('#orden_creada').append(`<option value='${data.id_correo}'>${data.titulo}</option>`);
                $('#orden_enviada').append(`<option value='${data.id_correo}'>${data.titulo}</option>`);
                $('#orden_procesada').append(`<option value='${data.id_correo}'>${data.titulo}</option>`);
                $('#orden_aprobada').append(`<option value='${data.id_correo}'>${data.titulo}</option>`);
                $('#orden_completada').append(`<option value='${data.id_correo}'>${data.titulo}</option>`);
            });
        } else {
            SweetAlert("error", proyecto["msg"]);
        }
    });

    $.get('../api/v1/fulmuv/correos/getCorreosControl', function (returnedData) {
        returned = JSON.parse(returnedData);
        if (returned["error"] == false) {
            returned["data"].forEach(function (data) {
                if (data["nombre"] == "orden_creada") {
                    $('#orden_creada').val(data.id_correo_plantilla);
                }
                if (data["nombre"] == "orden_enviada") {
                    $('#orden_enviada').val(data.id_correo_plantilla);
                }
                if (data["nombre"] == "orden_procesada") {
                    $('#orden_procesada').val(data.id_correo_plantilla);
                }
                if (data["nombre"] == "orden_aprobada") {
                    $('#orden_aprobada').val(data.id_correo_plantilla);
                }
                if (data["nombre"] == "orden_completada") {
                    $('#orden_completada').val(data.id_correo_plantilla);
                }
            });
        }
    });
    sessionByBrowserChartInit2()

});

// Detecta cuando el usuario selecciona una imagen
$("#img").on("change", function (event) {
    var archivo = event.target.files[0]; // Obtiene el archivo seleccionado
    if (archivo) {
        var lector = new FileReader(); // Crea un lector de archivos
        // Cuando el archivo se ha leído completamente
        lector.onload = function (e) {
            // Actualiza el src de la imagen de vista previa
            $("#cuerpoImagen").attr("src", e.target.result);
        };
        // Lee el archivo como una URL de datos
        lector.readAsDataURL(archivo);
    }
});

$("#cuerpoColor").on("change", function () {
    var colorSeleccionado = $(this).val(); // Obtiene el color seleccionado
    $("#cuerpoImagen").css("background-color", colorSeleccionado); // Cambia el fondo de #cuerpoImagen
});


function editContenedor() {
    var editColor = $("#cuerpoColor").val();
    var filePromise = $('#img')[0].files[0] == undefined ? Promise.resolve($("#cuerpoImagen").data("img")) : saveFiles($('#img')[0].files[0]);

    filePromise.then(function (file) {

        $.post('../api/v1/fulmuv/correos/updateContenedor', {
            color: $("#cuerpoColor").val(),
            imagen: file,
        }, function (returnedData) {
            returned = JSON.parse(returnedData);
            if (returned["error"] == false) {
                SweetAlert("url_success", returned["msg"], "header_footer.php");
            } else {
                SweetAlert("error", returned.msg);
            }
        });
    });
}

function saveFiles(file) {
    return new Promise(function (resolve, reject) {
        if (file == undefined) {
            resolve(); // Resuelve la promesa incluso si no hay imágenes
        } else {
            const formData = new FormData();
            formData.append("archivos[]", file);
            $.ajax({
                type: 'POST',
                data: formData,
                url: 'cargar_imagen.php',
                cache: false,
                contentType: false,
                processData: false,
                success: function (returnedImagen) {
                    if (returnedImagen["response"] == "success") {
                        resolve(returnedImagen["data"]["img"]); // Resuelve la promesa cuando la llamada AJAX se completa con éxito
                    } else {
                        SweetAlert("error", "Ocurrió un error al guardar los archivos." + returnedImagen["error"]);
                        reject(); // Rechaza la promesa en caso de error
                    }
                }
            });
        }
    });
}

function addCorreoProces(valor, id_correo_control) {
    toastr.options.timeOut = 1500;
    $.post('../api/v1/fulmuv/correos/updateCorreoControl', {
        id_correo_control: id_correo_control,
        id_correo_plantilla: valor,
    }, function (returnedData) {
        proyecto = JSON.parse(returnedData);
        if (proyecto["error"] == false) {
            toastr.success("Updated data");
        } else {
            SweetAlert("error", proyecto["msg"]);
        }
    });
}



function sessionByBrowserChartInit2() {
    var $sessionByBroswser = document.querySelector('.echart-session-example');
    if ($sessionByBroswser) {
        var userOptions = utils.getData($sessionByBroswser, 'options');
        var chart = window.echarts.init($sessionByBroswser);
        var dataset = {
            week: [{
                value: 50.3,
                name: 'Chrome'
            }, {
                value: 20.6,
                name: 'Safari'
            }, {
                value: 30.1,
                name: 'Mozilla'
            }],
            month: [{
                value: 35.1,
                name: 'Chrome'
            }, {
                value: 25.6,
                name: 'Safari'
            }, {
                value: 40.3,
                name: 'Mozilla'
            }],
            year: [{
                value: 26.1,
                name: 'Chrome'
            }, {
                value: 10.6,
                name: 'Safari'
            }, {
                value: 64.3,
                name: 'Mozilla'
            }]
        };

        var getDefaultOptions = function getDefaultOptions() {
            return {
                color: [utils.getColors().primary, utils.getColors().success, utils.getColors().info],
                tooltip: {
                    trigger: 'item',
                    padding: [7, 10],
                    backgroundColor: utils.getGrays()['100'],
                    borderColor: utils.getGrays()['300'],
                    textStyle: {
                        color: utils.getGrays()['1100']
                    },
                    borderWidth: 1,
                    transitionDuration: 0,
                    formatter: function formatter(params) {
                        return "<strong>".concat(params.data.name, ":</strong> ").concat(params.data.value, "%");
                    },
                    position: function position(pos, params, dom, rect, size) {
                        return getPosition(pos, params, dom, rect, size);
                    }
                },
                legend: {
                    show: false
                },
                series: [{
                    type: 'pie',
                    radius: ['100%', '65%'],
                    avoidLabelOverlap: false,
                    hoverAnimation: false,
                    itemStyle: {
                        borderWidth: 2,
                        borderColor: utils.getColor('gray-100')
                    },
                    label: {
                        normal: {
                            show: false
                        },
                        emphasis: {
                            show: false
                        }
                    },
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data: dataset.week
                }]
            };
        };
        echartSetOption(chart, userOptions, getDefaultOptions);

        var selectMenu = document.querySelector("[data-target='.echart-session-example']");
        if (selectMenu) {
            selectMenu.addEventListener('change', function (e) {
                var value = e.currentTarget.value;
                chart.setOption({
                    series: [{
                        data: dataset[value]
                    }]
                });
            });
        }
    }
};
