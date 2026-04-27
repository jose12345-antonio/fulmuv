/*$(document).ready(function () {
    // Carga inicial del primer PDF
    // $("#pill-tab-home").html('<iframe src="../documentos/4. Condiciones servicio logística.pdf" width="100%" height="800px" frameborder="0"></iframe>');
    $("#pill-tab-profile").html('<iframe src="../documentos/3. Condiciones pago en línea .pdf" width="100%" height="800px" frameborder="0" style="border:none; overflow:auto; -webkit-overflow-scrolling:touch;"></iframe>');

    // Escucha el cambio de tab
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href"); // Por ejemplo: "#pill-tab-profile"

        // Mapeo entre IDs de tab y rutas a PDFs
        var pdfs = {
            // "#pill-tab-home": "../documentos/4. Condiciones servicio logística.pdf",
            "#pill-tab-profile": "../documentos/3. Condiciones pago en línea .pdf",
            "#pill-tab-contact": "../documentos/2. CLIENTES - T&C Generales.pdf",
            "#pill-tab-content": "../documentos/1. PROVEEDORES - T&C_Política de Privacidad_Aviso Legal_Cookies.pdf"
        };

        if (pdfs[target]) {
            // Carga el iframe con el PDF correspondiente
            $(target).html('<iframe src="' + pdfs[target] + '" width="100%" height="800px" frameborder="0" style="border:none; overflow:auto; -webkit-overflow-scrolling:touch;"></iframe>');
        }
    });
});*/

$(document).ready(function () {
    var pdfs = {
        "#pill-tab-profile": "../documentos/3. Condiciones pago en línea .pdf",
        "#pill-tab-contact": "../documentos/2. CLIENTES - T&C Generales.pdf",
        "#pill-tab-content": "../documentos/1. PROVEEDORES - T&C_Política de Privacidad_Aviso Legal_Cookies.pdf"
    };

    // Detecta solo móviles (no tablets ni escritorios)
    function isMobileDevice() {
        return /Mobi|Android|iPhone|iPod/i.test(navigator.userAgent);
    }

    function cargarDocumento(target) {
        const pdf = pdfs[target];
        if (!pdf) return;

        if (isMobileDevice()) {
            $(target).html(`
                <div class="text-center">
                    <a href="${pdf}" target="_blank" class="btn btn-primary mt-3">
                        Ver documento
                    </a>
                </div>
            `);
        } else {
            $(target).html(`
                <div class="responsive-iframe-container">
                    <iframe 
                        src="${pdf}" 
                        width="100%" 
                        height="800px" 
                        frameborder="0" 
                        style="border:none; overflow:auto; -webkit-overflow-scrolling:touch;">
                    </iframe>
                </div>
            `);
        }
    }

    // Carga inicial
    cargarDocumento("#pill-tab-profile");

    // Cambio de pestaña
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr("href");
        cargarDocumento(target);
    });
});