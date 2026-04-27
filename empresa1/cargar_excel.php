<?php
require 'includes/header.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<title>Excel</title>
<div class="card mb-3">

   <input type="file" id="subirArchivo"/>
</div>
<!-- Conexión API js -->
<script src="js/cargar_excel.js?v1.0.1"></script>
<!-- Alerts js -->
<script src="js/alerts.js"></script>

<?php
require 'includes/footer.php';
?>