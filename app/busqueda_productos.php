<?php
include 'includes/header.php';

$search = $_GET["search"];

echo '<input type="hidden" placeholder="" id="search" class="form-control" value=' . $search . ' />';
?>

<style>

</style>

<section class="section-padding pb-5 py-0 mb-30">
    <div class="container">
        <div class="section-title wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <h3 class="titulo-subrayado">Servicios destacados</h3>
            </div>
        </div>

        <div class="row">
            <!-- Carrusel a la IZQUIERDA -->
            <div class="col-lg-10 col-md-12 wow animate__animated animate__fadeIn order-lg-1" data-wow-delay=".4s">
                <div class="tab-content" id="myTabContent-1">
                    <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                        <div class="carausel-4-columns-cover arrow-center position-relative">
                            <div class="slider-arrow slider-arrow-2 carausel-4-columns-arrow" id="carausel-4-columns-arrows"></div>
                            <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-servicio">
                                <!-- items del carrusel -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner a la DERECHA -->
            <div class="col-lg-2 d-none d-lg-flex wow animate__animated animate__fadeIn order-lg-2">
                <div class="banner-img style-2 d-flex align-items-center justify-content-center text-center text-white"
                    style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('img/2149030399.jpg');
                    background-size: cover; background-position: center; height: 300px;">
                    <div class="p-2">
                        <h4 class="fw-bold mb-1 text-white">Servicios para tu vehículo</h4>
                        <p class="fw-bold mb-2 text-white">Instalación, mantenimiento y diagnóstico.</p>
                        <a href="servicios.php" class="btn btn-md btn-light">
                            Ver servicios <i class="fi-rs-arrow-small-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- End Banner -->
        </div>
    </div>
</section>

<section class="product-tabs section-padding position-relative mb-30" style="background-color: rgba(0, 96, 112, 0.09);">
    <div class="container">
        <div class="section-title wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <h3 class="titulo-subrayado">Productos Destacados</h3>
            </div>
        </div>
        <div class="tab-content" id="myTabContent-1">
            <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                <div class="carausel-4-columns-cover arrow-center position-relative">
                    <div class="slider-arrow slider-arrow-2 carausel-4-columns-arrow" id="carausel-4-columns-arrows-oferta"></div>
                    <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-oferta">

                    </div>
                </div>
            </div>
        </div>
        <!--End tab-content-->
    </div>
</section>

<section class="section-padding pb-5 mb-30" style="background-color: rgba(0, 96, 112, 0.09);">
    <div class="container">
        <div class="section-title wow animate__animated animate__fadeIn mb-2">
            <div class="title">
                <h3 class="titulo-subrayado">Vehículos recién llegados</h3>
            </div>
        </div>
        <div class="tab-content" id="myTabContent-1">
            <div class="tab-pane fade show active" id="tab-one-1" role="tabpanel" aria-labelledby="tab-one-1">
                <div class="carausel-4-columns-cover arrow-center position-relative">
                    <div class="slider-arrow slider-arrow-2 carausel-4-columns-arrow" id="carausel-4-columns-arrows-para-ti"></div>
                    <div class="carausel-4-columns carausel-arrow-center" id="carausel-4-columns-vehiculos">

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>





<?php
include 'includes/footer.php';
?>
<script src="js/busqueda_productos.js?v1.0.0.0.0.0.0.0.0.0.0.0.0.7"></script>