<?php
$menu = "general";
$sub_menu = "eventos";
require 'includes/header.php';

if (isset($membresia["nombre"])) {
    $nombre_membresia = preg_replace('/[^a-z0-9]+/', '', strtolower(trim((string)$membresia["nombre"])));
    if ($tipo_user == "sucursal" || $nombre_membresia === "basicmuv") {
        echo "<script>
            swal({
                title: 'Necesitas mejorar tu plan',
                text: 'Tu plan actual no permite registrar eventos.\n\n¿Deseas ir ahora a actualizar tu membresía?',
                icon: 'info',
                buttons: {
                    cancel: { text: 'Cancelar', visible: true, closeModal: true },
                    confirm: { text: 'Mejorar plan', value: true, closeModal: true }
                }
            }, function () {
                window.location.href = 'upgrade_membresia.php?id_empresa=' + " . json_encode($id_empresa) . ";
            });
        </script>";
        exit;
    }
}

foreach ($permisos as $value) {
    if ($value["permiso"] == "Eventos" && $value["valor"] == "false") {
        echo "<script>window.location.href = '" . $dashboard . "'</script>";
    }
}
?>

<style>
    .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* imágenes dentro del modal: que nunca se salgan de la ventana */
    #modalViewEvento .modal-body {
        padding-bottom: 1rem;
    }

    #modalViewEvento .modal-cover-img,
    #modalViewEvento .modal-gallery-img {
        max-height: 60vh;
        /* ajusta a gusto */
        object-fit: contain;
    }

    #map_new {
        flex-grow: 1;
        /* Permitir que el mapa ocupe el espacio disponible */
        width: 100%;
        /* Ancho completo */
        min-height: 300px;
        /* Altura mínima */
    }

    #mapaEntrega {
        width: 100%;
        height: 400px;
        /* o 55vh, lo que prefieras */
        border-radius: 8px;
    }


    .map-wrapper {
        position: relative;
    }

    /* Ubica el input arriba a la derecha del mapa */
    .map-search {
        position: absolute;
        top: 0px;
        right: 110px;
        /* <- antes estaba left */
        left: auto;
        /* <- importante para soltar el anclaje izquierdo */
        z-index: 2000;
    }

    /* Para que el autocomplete siempre quede visible */
    .pac-container {
        z-index: 20000 !important;
    }

    /* (Opcional) en pantallas pequeñas que no quede cortado */
    @media (max-width: 576px) {
        .map-search {
            left: 8px;
            right: 8px;
        }

        /* se centra con margen a ambos lados */
        #buscarDireccion {
            width: 100%;
        }
    }
</style>
<title>Eventos</title>

<div class="card mb-3">

    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Eventos</h5>
            </div>
            <div class="col-8 col-sm-auto text-end ps-2">
                <div id="table-customers-replace-element">
                    <button onclick="addEventos()" class="btn btn-falcon-default btn-sm" type="button">
                        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                        <span class="d-none d-sm-inline-block ms-1">Crear</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    <div class="card-body border-bottom bg-light py-3">
        <div class="d-flex align-items-start gap-2">
            <span class="fas fa-image text-primary mt-1"></span>
            <div class="fs-10 text-700">
                Para la portada del evento, usa de preferencia una imagen horizontal de aproximadamente
                <strong>1200 x 675 px</strong> o similar, y un peso recomendado de hasta <strong>2 MB</strong>
                para que cargue mejor en la plataforma.
            </div>
        </div>
    </div>


    <div class="card-body p-0">
        <div class="falcon-data-table">
            <table class="table table-sm mb-0 data-table fs-10" id="my_table">
                <thead class="bg-200">
                    <tr>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Imagen</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Título</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Descripción</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Tipo</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Fecha_hora_inicio</th>
                        <th class="text-900 sort pe-1 align-middle white-space-nowrap">Fecha_hora_fin</th>
                        <th class="align-middle no-sort"></th>
                    </tr>
                </thead>
                <tbody id="lista_categorias">

                </tbody>
            </table>
        </div>
    </div>
</div>


 

<?php
require 'includes/footer.php';
?>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAO-o5grVvaS5wwq6CFZ3-VBOMBzSclCEg&libraries=places&loading=async&callback=onMapsReady" async defer></script>
<!-- Conexión API js -->
<script src="js/eventos.js?v1.0.0.0.0.0.2.5"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>
 
