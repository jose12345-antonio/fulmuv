<?php
include 'includes/header.php';
?>

<style>


</style>
<link rel="canonical" href="https://fulmuv.com/vendor.php">

<div class="container">
    <div class="archive-header-2 text-center mt-30">
        <!-- <h2 class="display-2 mb-50">Lista de Proveedores</h2> -->
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="sidebar-widget-2 widget_search mb-50">
                    <div class="search-form">
                        <form action="#">
                            <input type="text" placeholder="Buscar por Nombre de Empresa" />
                            <button type="submit"><i class="fi-rs-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-10">
        <div class="col-12 col-lg-12 mx-auto">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <h5>Continúan incorporándose más empresas a nivel nacional, para ti</h5>
                </div>
                <div class="sort-by-product-area">
                    <div class="sort-by-cover d-flex justify-content-center align-items-center me-2">
                        <div>
                            <button type="button" id="btnUbicacion" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalUbicacion">
                                <i class="fi-rs-marker me-1"></i> Cambiar ubicación
                            </button>
                        </div>
                    </div>
                    <div class="sort-by-cover mr-10">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps"></i>Ver:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> 12 <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-show">
                            <ul>
                                <li><a class="active" href="#" data-value="12">12</a></li>
                                <li><a href="#" data-value="24">24</a></li>
                                <li><a href="#" data-value="48">48</a></li>
                                <li><a href="#" data-value="72">72</a></li>
                                <li><a href="#" data-value="96">96</a></li>
                                <li><a href="#" data-value="120">120</a></li>
                                <li><a href="#" data-value="all">All</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="sort-by-cover">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps-sort"></i>Ordenar por:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> Todos <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown sort-order">
                            <ul>
                                <li><a class="active" href="#" data-value="todos">Todos</a></li>
                                <li><a href="#" data-value="menor">Descendentes</a></li>
                                <li><a href="#" data-value="mayor">Ascendentes</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-2 mt-2">
            <div class="categories-dropdown-wrap style-2 font-heading p-2">
                <h5 class="border-bottom mb-2 mt-0 pb-2">Categorías</h5>
                <div id="filtro-categorias" class="mt-2"></div>
            </div>
        </div>
        <div class="col-lg-10 mt-2">
            <div class="row vendor-grid">

            </div>
            <input type="hidden" id="provincia_id_hidden">
            <input type="hidden" id="provincia_nombre_hidden">
            <input type="hidden" id="canton_id_hidden">
            <input type="hidden" id="canton_nombre_hidden">
            <div class="pagination-area mt-20 mb-20">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start">

                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ubicación -->
<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalUbicacionLabel">
                    <i class="fi-rs-marker me-1"></i> Elige tu ubicación
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label mb-1">Provincia</label>
                    <select id="selectProvincia" class="form-control" required></select>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1">Cantón</label>
                    <select id="selectCanton" class="form-control" required></select>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardarUbicacion">Listo</button>
            </div>
        </div>
    </div>
</div>


<?php
include 'includes/footer.php';
?>
<script src="js/vendor.js?v1.0.0.0.0.0.0.0.0.0.7"></script>