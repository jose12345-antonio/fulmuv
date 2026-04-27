$( document ).ready(function() {
  $.get('../api/v1/fulmuv/home/getData', {}, function (returnedData) {
    var returned = JSON.parse(returnedData)
    if (returned.error == false) {
        $("#contadores").append(`
            <div class="col-lg-4 border-end-lg border-bottom border-bottom-lg-0 pb-3 pb-lg-0 text-center">
                <div class="icon-circle icon-circle-primary mb-0">
                    <span class="fs-8 fas fa-industry text-primary"></span>
                </div>
                <h5 class="mb-0 font-sans-serif">
                    <span class="text-700 mx-2" data-countup='{"endValue":${returned.total_empresas}}'>0</span>
                </h5>
                <h6 class="mb-0 font-sans-serif">
                    <span class="fw-normal text-600">Empresas</span>
                </h6>
            </div>
            <div class="col-lg-4 border-end-lg border-bottom border-bottom-lg-0 pb-2 pb-lg-0 text-center">
                <div class="icon-circle icon-circle-primary mb-0 mt-1">
                    <span class="fs-8 fas fa-file-invoice-dollar text-primary"></span>
                </div>
                <h5 class="mb-0 font-sans-serif">
                    <span class="text-700 mx-2" data-countup='{"endValue":${returned.total_ordenes}}'>0</span>
                </h5>
                <h6 class="mb-0 font-sans-serif">
                    <span class="fw-normal text-600">órdenes</span>
                </h6>
            </div>
            <div class="col-lg-4 pb-0 pb-lg-0 text-center">
                <div class="icon-circle icon-circle-primary mb-0 mt-1">
                    <span class="fs-8 fas fa-user text-primary"></span>
                </div>
                <h5 class="mb-0 font-sans-serif">
                    <span class="text-700 mx-2" data-countup='{"endValue":${returned.total_usuarios}}'>0</span>
                </h5>
                <h6 class="mb-0 font-sans-serif">
                    <span class="fw-normal text-600">Usuarios</span>
                </h6>
            </div>
        `);
        /*
        returned.empresas_agrupadas.forEach(empresas => { 
            $("#empresas_agrupadas").append(`
                <div class="d-flex flex-between-center border-bottom py-3 pt-md-0 pt-xxl-3">
                    <div class="d-flex">
                        <h6 class="text-700 mb-0">${empresas.nombre} </h6>
                    </div>
                    <h6 class="text-700 mb-0">${empresas.total}</h6>
                </div>
            `);
        });
        var $sessionByBroswser = document.querySelector('.echart-session-example2');
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

            var selectMenu = document.querySelector("[data-target='.echart-session-example2']");
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
        // Llenar el dataset con los datos de la API
        var dataset = {
            week: returned.empresas_agrupadas.map(empresas => ({
                value: empresas.total,
                name: empresas.nombre
            }))
        };
        */
        var totalOrdenes = returned.empresas_agrupadas.reduce((acc, empresa) => acc + empresa.total, 0);
        var dataset = {
            week: []
        };

        // Generar colores dinámicos
        var generateColors = function (count) {
            var colors = [];
            for (var i = 0; i < count; i++) {
                colors.push(`hsl(${Math.random() * 360}, 70%, 50%)`);
            }
            return colors;
        };

        var colors = generateColors(returned.empresas_agrupadas.length);

        returned.empresas_agrupadas.forEach((empresa, index) => { 
            // Insertar empresa con el color correspondiente
            $("#empresas_agrupadas").append(`
                <div class="d-flex flex-between-center border-bottom py-3 pt-md-0 pt-xxl-3">
                    <div class="d-flex">
                        <h6 class="text-700 mb-0"><span class="fas fa-circle me-2" style="color:${colors[index]}"></span>${empresa.nombre}</h6>
                    </div>
                    <h6 class="text-700 mb-0">${empresa.total}</h6>
                </div>
            `);

            // Calcular porcentaje y llenar dataset
            var porcentaje = ((empresa.total / totalOrdenes) * 100).toFixed(1); // Redondear a un decimal
            dataset.week.push({
                value: parseFloat(porcentaje), // Convertir a número flotante
                name: empresa.nombre
            });
        });

        // Configuración del gráfico
        var $sessionByBroswser = document.querySelector('.echart-session-example2');
        if ($sessionByBroswser) {
            var userOptions = utils.getData($sessionByBroswser, 'options');
            var chart = window.echarts.init($sessionByBroswser);

            var getDefaultOptions = function getDefaultOptions() {
                return {
                    color: colors,
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

            // Manejo del menú desplegable si existe
            var selectMenu = document.querySelector("[data-target='.echart-session-example2']");
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
        
        $("#total_creadas").text( returned.estados_agrupados.creadas );
        $("#total_enviadas").text( returned.estados_agrupados.enviadas );
        $("#total_procesadas").text( returned.estados_agrupados.procesadas );
        $("#total_aprobadas").text( returned.estados_agrupados.aprobadas );
        $("#total_completadas").text( returned.estados_agrupados.completadas );
        $("#total_eliminadas").text( returned.estados_agrupados.eliminadas );

        countupInit()
    }
    
  });

  // Obtener la fecha actual
  const fechaActual = new Date();

  // Determinar el trimestre basado en el mes actual
  const mesActual = fechaActual.getMonth(); // Enero es 0
  var trimestre = "";

  if (mesActual >= 0 && mesActual <= 3) {
    trimestre = 1; // Enero a Abril
  } else if (mesActual >= 4 && mesActual <= 7) {
    trimestre = 2; // Mayo a Agosto
  } else {
    trimestre = 3; // Septiembre a Diciembre
  }

  $("#quarterSelect").val(trimestre)

  //sessionByBrowserChartInit2()
  setQuarter()

  //echartsNumberOfTicketsInit2()
});

/*
function sessionByBrowserChartInit2() {
    var $sessionByBroswser = document.querySelector('.echart-session-example2');
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
            // month: [{
            //     value: 35.1,
            //     name: 'Chrome'
            // }, {
            //     value: 25.6,
            //     name: 'Safari'
            // }, {
            //     value: 40.3,
            //     name: 'Mozilla'
            // }],
            // year: [{
            //     value: 26.1,
            //     name: 'Chrome'
            // }, {
            //     value: 10.6,
            //     name: 'Safari'
            // }, {
            //     value: 64.3,
            //     name: 'Mozilla'
            // }]
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

        var selectMenu = document.querySelector("[data-target='.echart-session-example2']");
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
*/

function setQuarter() {
    const today = new Date();
    const year = today.getFullYear();
    console.log(year)
    var quarter = $("#quarterSelect").val()

    let startDate, endDate;
    switch (quarter) {
        case "1":
            startDate = new Date(year, 0, 1); // January 1
            endDate = new Date(year, 3, new Date(year, 3 + 1, 0).getDate()); // April 31
            break;
        case "2":
            startDate = new Date(year, 4, 1); // May 1
            endDate = new Date(year, 7, new Date(year, 7 + 1, 0).getDate()); // August 30
            break;
        case "3":
            startDate = new Date(year, 8, 1); // September 1
            endDate = new Date(year, 11, new Date(year, 11 + 1, 0).getDate()); // December 31
            break;
    }
    startDate = startDate.toISOString().split('T')[0];
    endDate = endDate.toISOString().split('T')[0];
    $.get('../api/v1/fulmuv/home/getTotalOrdenesByHistory/'+startDate+'/'+endDate, {}, function (returnedData){
        var returned = JSON.parse(returnedData)
        var $numberOfTickets = document.querySelector('.echart-number-of-tickets');
        if ($numberOfTickets) {
            var userOptions = utils.getData($numberOfTickets, 'options');
            var chart = window.echarts.init($numberOfTickets);
            var numberOfTicketsLegend = document.querySelectorAll('[data-number-of-tickets2]');

            console.log(numberOfTicketsLegend)
            
            // Arrays para datos
            var xAxisData = [];
            var data1 = []; // total_creada
            var data2 = []; // total_enviada
            var data3 = []; // total_completada
            var data4 = []; // total_procesada
            var data5 = []; // total_aprobada
            var data6 = []; // total_eliminada

            // Llenar los datos a partir del JSON
            for (var month in returned.data) {
                if (returned.data.hasOwnProperty(month)) {
                    var monthData = returned.data[month];
                    
                    // Agregar nombres de los meses al eje X
                    xAxisData.push(month);

                    // Agregar valores al eje Y para cada tipo
                    data1.push(monthData.total_creada || 0);
                    data2.push(monthData.total_enviada || 0);
                    data3.push(monthData.total_completada || 0);
                    data4.push(monthData.total_procesada || 0);
                    data5.push(monthData.total_aprobada || 0);
                    data6.push(monthData.total_eliminada || 0);
                }
            }
            // Configuración de estilos de énfasis
            var emphasisStyle = {
                itemStyle: {
                    shadowColor: utils.rgbaColor(utils.getColor('dark'), 0.3),
                    borderRadius: [5, 5, 5, 5, 5, 5]
                }
            };

            // Función para obtener las opciones por defecto
            var getDefaultOptions = function getDefaultOptions() {
                return {
                    color: [utils.getColor('secondary'), utils.getColor('info'), utils.getColor('success'), utils.getColor('primary'), utils.getColor('warning'), utils.getColor('danger')],
                    tooltip: {
                        trigger: 'item',
                        padding: [7, 10],
                        backgroundColor: utils.getGrays()['100'],
                        borderColor: utils.getGrays()['300'],
                        textStyle: {
                            color: utils.getGrays()['900']
                        },
                        borderWidth: 1,
                        transitionDuration: 0,
                        axisPointer: {
                            type: 'none'
                        }
                    },
                    legend: {
                        data: ['Creadas', 'Enviadas', 'Completadas', 'Procesadas', 'Aprobadas', 'Eliminadas'],
                        show: false
                    },
                    xAxis: {
                        data: xAxisData,
                        splitLine: {
                            show: false
                        },
                        splitArea: {
                            show: false
                        },
                        axisLabel: {
                            color: utils.getGrays()['600']
                        },
                        axisLine: {
                            lineStyle: {
                                color: utils.getGrays()['300'],
                                type: 'dashed'
                            }
                        },
                        axisTick: {
                            show: false
                        }
                    },
                    yAxis: {
                        splitLine: {
                            lineStyle: {
                                color: utils.getGrays()['300'],
                                type: 'dashed'
                            }
                        },
                        axisLabel: {
                            color: utils.getGrays()['600']
                        }
                    },
                    series: [{
                        name: 'Creadas',
                        type: 'bar',
                        stack: 'one',
                        emphasis: emphasisStyle,
                        data: data1
                    }, {
                        name: 'Enviadas',
                        type: 'bar',
                        stack: 'two',
                        emphasis: emphasisStyle,
                        data: data2
                    }, {
                        name: 'Completadas',
                        type: 'bar',
                        stack: 'three',
                        emphasis: emphasisStyle,
                        data: data3
                    }, {
                        name: 'Procesadas',
                        type: 'bar',
                        stack: 'four',
                        emphasis: emphasisStyle,
                        data: data4
                    }, {
                        name: 'Aprobadas',
                        type: 'bar',
                        stack: 'five',
                        emphasis: emphasisStyle,
                        data: data5
                    }, {
                      name: 'Eliminadas',
                      type: 'bar',
                      stack: 'six',
                      emphasis: emphasisStyle,
                      data: data6
                  }],
                    itemStyle: {
                        borderRadius: [3, 3, 0, 0]
                    },
                    barWidth: '12px',
                    grid: {
                        top: '10%',
                        bottom: 0,
                        left: 0,
                        right: 0,
                        containLabel: true
                    }
                };
            };

            // Establecer opciones y cargar el gráfico
            echartSetOption(chart, userOptions, getDefaultOptions);

            // Agregar eventos para el selector de leyenda
            numberOfTicketsLegend.forEach(function (el) {
                if (!el.dataset.eventBound) {
                    el.addEventListener('change', function () {
                        const actionName = utils.getData(el, 'number-of-tickets2');
                        //console.log(actionName)
                        chart.dispatchAction({
                            type: 'legendToggleSelect',
                            name: actionName
                        });
                    });
                    // Marcar el elemento como "evento registrado"
                    el.dataset.eventBound = "true";
                }

            });
        }
    });
    //echartsNumberOfTicketsInit2()
}

