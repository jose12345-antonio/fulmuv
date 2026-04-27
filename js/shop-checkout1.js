/* ====================== Estado global ====================== */
let itemsPerPage = 12, currentPage = 1, productosData = [];
let sortOption = "todos", searchText = "", id_empresa = $("#id_empresa").val(), subcategoriasSeleccionadas = [];
let precioMin = 0, precioMax = Infinity, map, marker, geocoder, latitud = "", longitud = "";
let autocompleteService, placesService;

let LS_IDENT = 'checkout_identificacion', LS_ENVIO = 'checkout_envio', LS_MODE = 'checkout_modo_entrega';
let modoEntrega = 1; const pickupEmpresasCache = new Map(); let mapaPickup, markerPickup;

/* Rutas seleccionadas */
let rutaSeleccionada = null;         // última fila completa
let rutasSeleccionadas = [];         // historial (se persiste)
const LS_RUTAS = 'checkout_rutas_seleccionadas';
let AGENCIAS_RAW = []; // listado completo que viene del API
// Provincias permitidas según GRUPO ENTREGA
let PROVINCIAS_GE = new Set();
const PROVINCIAS_EXCLUIR = new Set([
    'ZONA NO DELIMITADA',
    'MIAMI',
    'EUROPA',
    '-'
]);

/* Helpers */
//const capitalizarPrimeraLetra = (s = '') => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
const formatoUSD = (n) => '$' + Number(n || 0).toFixed(2);


/* ►► NUEVO: variables globales para id_ruta y tarifa ◄◄ */
let idRutaSeleccionada = null;
let aplica_domicilio = null;

let tarifaSeleccionada = {
    trayecto: '',          // texto del trayecto (LOCAL/PROVINCIAL/…)
    base2kg: 0,            // valor hasta 2 kg
    adicional1kg: 0        // valor por cada kg adicional
};

/* ====================== Inicio ====================== */
$(document).ready(function () {
    cargarProvincias();

    /* Cambio de provincia */
    $(document).on('change', '#selectProvincia', function () {
        const prov = ($(this).val() || '').trim();

        filtrarAgenciasPorProvincia(prov);   // << filtra agencias según provincia

        // Reset dependientes
        $('#selectCanton').val('').prop('disabled', true).empty().append('<option value="">Seleccione un cantón</option>');
        $('#selectSector').val('').prop('disabled', true).empty().append('<option value="">Seleccione un sector</option>');
        //$('#envioCostoBox').addClass('d-none').empty();
        $('#cantonAviso').addClass('d-none').text('');

        if (!prov) return;
        cargarCantones(prov);
        saveEnvio();
    });

    getCiudadesAgencia();
    // $('#envioCostoBox').addClass('d-none'); // oculto al inicio
    actualizarIconoCarrito?.();
    $("#breadcrumb").append(`
    <a href="vendor.php" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
    <span></span> Su Carrito de Compra
  `);
    actualizarshopCart();
});

/* Cambio de cantón */
$(document).on('change', '#selectCanton', function () {
    $('#cantonAviso').addClass('d-none').text('');
    const canton = ($(this).val() || '').trim();
    const prov = ($('#selectProvincia').val() || '').trim();

    // Reset de sector/valor referencial
    $('#selectSector').val('').prop('disabled', true).empty().append('<option value="">Seleccione un sector</option>');
    // $('#envioCostoBox').addClass('d-none').empty();

    if (!prov || !canton) return;


    const idProvincia = getSelectedDataId($('#selectProvincia')); // ✅
    const idCanton = getSelectedDataId($('#selectCanton'));

    // 1) Cargar sectores
    cargarSectores(prov, canton, idProvincia, idCanton);

    saveEnvio();
});


document.addEventListener("DOMContentLoaded", () => {
    const selectHorario = document.getElementById("horario_entrega");
    for (let hora = 9; hora <= 18; hora++) {
        const hora12 = hora > 12 ? hora - 12 : hora;
        const ampm = hora < 12 ? "a.m." : "p.m.";
        selectHorario.appendChild(new Option(`${hora12}:00 ${ampm}`, `${hora}:00`));
    }
});

function normalizeStr(s = '') {
    return String(s)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // quita acentos
        .toUpperCase()
        .trim();
}
function renderAgencias(list) {
    const $sel = $("#selectCiudadesAgencia");
    $sel.empty().append('<option value="">Seleccione una agencia</option>');
    list.forEach(c => $sel.append(`<option value="${c.id}">${c.nombre}</option>`));
}
/* =================== Autocomplete Provincia/Cantón/Sector =================== */
function postJSON(url, data) { return $.ajax({ url, method: 'POST', data, dataType: 'json' }); }

/** Filtra por provincia (por texto incluido en el nombre de la agencia) */
function filtrarAgenciasPorProvincia(provincia) {
    const p = normalizeStr(provincia);
    if (!p) { renderAgencias(AGENCIAS_RAW); return; }
    const filtradas = AGENCIAS_RAW.filter(a => normalizeStr(a.nombre).includes(p));
    renderAgencias(filtradas.length ? filtradas : []); // si no hay coincidencias, dejar vacío
}


/* ===== TARIFAS por trayecto (exactamente estos nombres) ===== */
const TARIFAS = {
    "TARIFA_QUITO_GUAYAQUIL": { base: 2.78, adicional: 0.51 },
    "PRINCIPAL": { base: 4.11, adicional: 0.7 },
    "SECUNDARIO": { base: 5.69, adicional: 0.82 },
    "ESPECIAL": { base: 7.59, adicional: 1.14 },
    "GALAPAGOS": { base: 22.77, adicional: 1.90 }
};

function normalizeKey(str = "") {
    return String(str)
        .toUpperCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // sin tildes
        .replace(/\s+/g, " ")
        .trim();
}

function tarifaByTrayecto(nombre = '') {
    const k = (nombre); // ya quita tildes y pone mayúsculas

    console.log(k)

    // // --- Galápagos primero (dos tarifas posibles) ---
    // if (/\bGALAPAGOS\b/.test(k)) {
    //     if (/\bSANTA\s*ISABEL\b/.test(k)) return TARIFAS.GALAPAGOS_SI;
    //     return TARIFAS.GALAPAGOS;
    // }

    // // --- Coincidencias por tipo de trayecto (con sinónimos y plurales) ---
    // if (/\bTARIFA QUITO - GUAYAQUIL?\b/.test(k)) return TARIFAS.TARIFA_QUITO_GUAYAQUIL;
    // if (/\bPRINCIPAL?)\b/.test(k)) return TARIFAS.PRINCIPAL;
    // if (/\bSECUNDARIO?\b/.test(k)) return TARIFAS.SECUNDARIO;
    // if (/\bESPECIAL?\b/.test(k)) return TARIFAS.ESPECIAL;
    // if (/\GALAPAGOS?\b/.test(k)) return TARIFAS.GALAPAGOS;

    // // --- Fallback por coincidencia exacta del “normalize” (por si ya viene limpio) ---
    // return TARIFAS[k] || null;
}

function renderEnvioCostoFromRuta(origen = '') {

    const prov = $("#selectProvincia").val();
    const cant = $("#selectCanton").val();
    const sect = $("#selectSector").val();

    $.post(
        "api/v1/fulmuv/getRutaByProvinciaCantonSector",
        { nombre_provincia: prov, nombre_canton: cant, sector: sect },
        function (returnedData) {

            if (returnedData?.error) {
                console.warn("Error ruta:", returnedData?.msg || returnedData);
                return;
            }

            // tu API devuelve: { error:false, data:[{...}] }
            const row = Array.isArray(returnedData?.data) ? returnedData.data[0] : returnedData?.data;
            if (!row) return;

            // ✅ trayecto viene como objeto: { valor, adicional }
            const t = row.trayecto || {};

            // ✅ llenar tarifaSeleccionada
            tarifaSeleccionada = {
                id_ruta: row.id_ruta || 0,
                trayecto: row.tipo_cobertura || row.nombre_trayecto || '',   // texto (ej: ESPECIAL)
                base2kg: Number(t.valor || 0),
                adicional1kg: Number(t.adicional || 0)
            };

            console.log("tarifaSeleccionada:", tarifaSeleccionada);

            const base2kg = Number(tarifaSeleccionada?.base2kg || 0);
            const adicional1kg = Number(tarifaSeleccionada?.adicional1kg || 0);
            idRutaSeleccionada = row.id_ruta ?? null;
            aplica_domicilio = 1;

            let html = `
                <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-cash-coin"></i>
                <span class="fw-semibold">Valor referencial por la entrega</span>
                </div>
                <div class="small text-muted mb-2">
                Ruta <strong>#${row.id_ruta ?? '—'}</strong> — Trayecto: <strong>${tarifaSeleccionada.trayecto || '—'}</strong>
                </div>
            `;

            html += `
                <div class="d-flex flex-wrap align-items-center gap-4 mb-1">
                <div>
                    <div class="small text-muted">Hasta 2 kg</div>
                    <div class="h5 mb-0">${formatoUSD(base2kg)}</div>
                </div>
                <div class="vr" style="height:36px"></div>
                <div>
                    <div class="small text-muted">+1 kg (cada kilo adicional)</div>
                    <div class="h5 mb-0">${formatoUSD(adicional1kg)}</div>
                </div>
                </div>
                <div class="small text-muted">
                Ejemplos: 3 kg = ${formatoUSD(base2kg + adicional1kg)}, 4 kg = ${formatoUSD(base2kg + 2 * adicional1kg)}.
                </div>
            `;

            html += `<div class="text-muted mt-2">Valores referenciales; pueden variar por peso/volumen y punto exacto. A estas tarifas ya se encuentra agregado el IVA.</div>`;
            $('#envioCostoBox').html(html).removeClass('d-none');

            // (opcional) si quieres mostrarlo en UI:
            // $('#envioCostoBox').removeClass('d-none').html(`
            //   <div><b>Trayecto:</b> ${tarifaSeleccionada.trayecto}</div>
            //   <div><b>Hasta 2kg:</b> ${formatoUSD(tarifaSeleccionada.base2kg)}</div>
            //   <div><b>+1kg:</b> ${formatoUSD(tarifaSeleccionada.adicional1kg)}</div>
            // `);
        },
        "json"
    );
}

/* ------- Provincia ------- */
let provinciaTimer = null;
$('#inputProvincia').on('input', function () {
    const q = (this.value || '').trim();

    filtrarAgenciasPorProvincia(q);  // << filtra “en vivo” mientras escribe

    $('#sugProvincia').hide().empty();
    // reset dependientes
    $('#inputCanton').prop('disabled', true).val(''); $('#sugCanton').hide().empty();
    $('#inputSector').prop('disabled', true).val(''); $('#sugSector').hide().empty();
    $('#envioCostoBox').addClass('d-none');

    if (q.length < 1) return;
    clearTimeout(provinciaTimer);
    provinciaTimer = setTimeout(() => {
        postJSON('api/v1/fulmuv/buscarProvinciaDestino', { search: q })
            .done(res => {
                if (res && !res.error && Array.isArray(res.data)) {
                    const provs = [...new Set(res.data.map(r => (r.provincia_destino || '').trim()).filter(Boolean))]
                        .slice(0, 10).sort((a, b) => a.localeCompare(b, 'es'));
                    if (provs.length) {
                        const $l = $('#sugProvincia').empty();
                        provs.forEach(p => $l.append(`<li class="autocomplete-item" data-value="${p}">${p}</li>`));
                        $l.show();
                    }
                }
            });
    }, 250);
});


// $(document).on('click', '#sugCanton .autocomplete-item', function () {
//     // limpiar aviso
//     $('#cantonAviso').addClass('d-none').text('');

//     const canton = $(this).data('value') || '';
//     $('#inputCanton').val(canton);
//     $('#sugCanton').hide().empty();

//     // “Cargando sectores…”
//     $('#selectSector')
//         .prop('disabled', true)
//         .html('<option value="">Cargando sectores...</option>');

//     const prov = ($('#inputProvincia').val() || '').trim();

//     // 1) Cargar TODOS los sectores (zona_peligrosa) al select
//     cargarSectores(prov, canton);

//     // 2) Buscar UNA ruta con aplica_domicilio = "Y" para mostrar tarifa por cantón
//     postJSON('api/v1/fulmuv/getSectoresByProvinciaCanton', {
//         nombre_provincia: prov,
//         nombre_canton: canton,
//         search: '' // todos
//     }).done(res => {
//         const filas = Array.isArray(res?.data) ? res.data : (res?.data ? [res.data] : []);
//         const filaDomicilio = filas.find(r => String(r.aplica_domicilio || '').toUpperCase() === 'Y');

//         console.log("RUTAS")
//         console.log(filas)
//         console.log(filaDomicilio)
//         if (filaDomicilio) {
//             // sí hay entrega a domicilio: muestra el cuadro con trayecto y valores
//             //renderEnvioCostoFromRuta(filaDomicilio, 'cantón');
//             $('#cantonAviso').addClass('d-none').text('');
//         } else {
//             // no hay entrega a domicilio a nivel cantón
//             $('#envioCostoBox').addClass('d-none').empty();
//             $('#cantonAviso')
//                 .removeClass('d-none')
//                 .html('Para este cantón <strong>no aplica entrega a domicilio</strong>. '
//                     + 'Selecciona un <strong>sector</strong> para verificar la agencia más cercana.');
//         }
//     });

//     saveEnvio();
// });


$(document).on('click', '#sugProvincia .autocomplete-item', function () {
    const prov = $(this).data('value') || '';
    $('#inputProvincia').val(prov);
    $('#sugProvincia').hide().empty();

    filtrarAgenciasPorProvincia(prov);   // << filtra agencias según provincia elegida


    // habilitar cantón y resetear sector
    $('#inputCanton').prop('disabled', false).focus();
    $('#selectSector').prop('disabled', true).html('<option value="">Seleccione un sector</option>');

    // SIEMPRE ocultar tarifas al seleccionar provincia
    // $('#envioCostoBox').addClass('d-none').empty();
    // ocultar aviso previo, si existiera
    $('#cantonAviso').addClass('d-none').text('');

    saveEnvio();

    // Sólo comprobaremos si existe alguna ruta “Y” para decidir si mostrar un aviso,
    // PERO ya no mostramos tarifas aquí.
    postJSON('api/v1/fulmuv/getCantonesDestinoByProvinciaLike', {
        search: '',
        nombre_provincia: prov
    }).done(res => {
        const rows = Array.isArray(res?.data) ? res.data : [];
        const existeY = rows.some(r => String(r.aplica_domicilio || '').toUpperCase() === 'Y');

        if (!existeY) {
            $('#cantonAviso')
                .removeClass('d-none')
                .text('No se encontraron rutas con entrega a domicilio para esta provincia. Selecciona un cantón para ver opciones.');
        } else {
            $('#cantonAviso').addClass('d-none').text('');
        }
    });
});


/* ------- Cantón ------- */
let cantonTimer = null;
$('#inputCanton').on('input', function () {
    const q = (this.value || '').trim(), prov = ($('#inputProvincia').val() || '').trim();
    $('#sugCanton').hide().empty();
    $('#inputSector').prop('disabled', true).val(''); $('#sugSector').hide().empty();
    // $('#envioCostoBox').addClass('d-none');

    if (!prov || q.length < 3) return;
    clearTimeout(cantonTimer);
    cantonTimer = setTimeout(() => {
        postJSON('api/v1/fulmuv/getCantonesDestinoByProvinciaLike', { search: q, nombre_provincia: prov })
            .done(res => {
                if (res && !res.error && Array.isArray(res.data)) {
                    const cant = [...new Set(res.data.map(r => (r.canton_destino || '').trim()).filter(Boolean))]
                        .slice(0, 10).sort((a, b) => a.localeCompare(b, 'es'));
                    if (cant.length) {
                        const $l = $('#sugCanton').empty();
                        cant.forEach(c => $l.append(`<li class="autocomplete-item" data-value="${c}">${c}</li>`));
                        $l.show();
                    }
                }
            });
    }, 250);
});

function getSelectedDataId($select) {
    const v = $select.find('option:selected').data('id');
    return (v === undefined || v === null || v === '') ? null : v;
}
function cargarSectores(provincia, canton, idProvincia, idCanton) {

    console.log("INGRESA AQUÍ")
    console.log(provincia, canton, idProvincia, idCanton)

    $.post("api/v1/fulmuv/getSectoresByProvinciaCanton",
        {
            nombre_provincia: provincia,
            nombre_canton: canton,
            search: '',
            id_provincia: idProvincia,
            id_canton: idCanton // traer todos
        }, function (returnedData) {

            if (!returnedData.error) {
                const $sel = $('#selectSector');
                $sel.empty();
                if (returnedData.data.length != 0) {
                    $sel.append('<option value="">Seleccione un sector</option>');
                    $sel.prop('disabled', false);

                    returnedData.data.forEach(s => $sel.append(`<option value="${s.ParroquiaNombre}" data-id="${s.ParroquiaID}">${s.ParroquiaNombre}</option>`));

                } else {
                    $sel.append('<option value="">Sin sectores configurados</option>').prop('disabled', true);
                    return;
                }

            }

        }, 'json')
    postJSON('', {

    }).done(res => {
        const $sel = $('#selectSector');
        $sel.empty();

        const sectores = Array.isArray(res?.data)
            ? [...new Set(res.data
                .map(r => (r.sector || r.zona_peligrosa || '').trim())
                .filter(Boolean))]
                .sort((a, b) => a.localeCompare(b, 'es'))
            : [];

        if (!sectores.length) {
            $sel.append('<option value="">Sin sectores configurados</option>').prop('disabled', true);
            return;
        }


    });
}

// $(document).on('click', '#sugSector .autocomplete-item', function () {
//     const sector = $(this).data('value') || '';
//     $('#inputSector').val(sector);
//     $('#sugSector').hide().empty();
//     saveEnvio();

//     const prov = ($('#inputProvincia').val() || '').trim();
//     const canton = ($('#inputCanton').val() || '').trim();

//     // Traer la fila completa por provincia + cantón + sector
//     postJSON('api/v1/fulmuv/getRutaByProvinciaCantonSector', { nombre_provincia: prov, nombre_canton: canton, sector })
//         .done(res => {
//             let row = null;
//             if (Array.isArray(res?.data) && res.data.length) { row = res.data[0]; }
//             else if (res?.data) { row = res.data; }
//             if (row) { renderEnvioCostoFromRuta(row, 'sector'); }
//         });
// });

$(document).on('change', '#selectSector', function () {
    const sector = ($(this).val() || '').trim();
    // if (!sector) { $('#envioCostoBox').addClass('d-none').empty(); return; }

    // const prov = ($('#inputProvincia').val() || '').trim();
    // const canton = ($('#inputCanton').val() || '').trim();

    const prov = ($('#selectProvincia').val() || '').trim();
    const canton = ($('#selectCanton').val() || '').trim();

    postJSON('api/v1/fulmuv/getRutaByProvinciaCantonSector', {
        nombre_provincia: prov,
        nombre_canton: canton,
        sector
    }).done(res => {
        let row = null;
        if (Array.isArray(res?.data) && res.data.length) { row = res.data[0]; }
        else if (res?.data) { row = res.data; }

        if (row) { renderEnvioCostoFromRuta(row, 'sector'); }
    });

    saveEnvio();
});


/* Cerrar listas al hacer click fuera */
$(document).on('click', function (e) {
    if (!$(e.target).closest('#inputProvincia,#sugProvincia').length) $('#sugProvincia').hide();
    if (!$(e.target).closest('#inputCanton,#sugCanton').length) $('#sugCanton').hide();
    if (!$(e.target).closest('#inputSector,#sugSector').length) $('#sugSector').hide();
});

/* =================== Stepper / Validaciones =================== */
$(function () {
    const $step1 = $('#step1'), $step2 = $('#step2');
    const $s1Head = $('#step1Head'), $s2Head = $('#step2Head');

    function goStep2() { $step1.addClass('d-none'); $step2.removeClass('d-none'); $s1Head.addClass('is-complete').removeClass('is-active'); $s2Head.addClass('is-active'); window.scrollTo({ top: $step2.offset().top - 80, behavior: 'smooth' }); }
    function goStep1() { $step2.addClass('d-none'); $step1.removeClass('d-none'); $s2Head.removeClass('is-active is-complete'); $s1Head.addClass('is-active').removeClass('is-complete'); window.scrollTo({ top: $step1.offset().top - 80, behavior: 'smooth' }); }

    function validateStep1() {
        const req = [
            '#fact_nombre',
            '#tipo_identificacion',
            '#identificacion',
            '#correo',
            '#telefono_fact',
            '#direccion_fiscal',
            '#forma_pago',
            '#correo_sesion',
            '#password'
        ];

        let ok = true;
        req.forEach(sel => {
            const $el = $(sel);
            if (!($el.val() || '').trim()) {
                ok = false;
                $el.addClass('is-invalid');
            } else {
                $el.removeClass('is-invalid');
            }
        });

        if (!ok) {
            $('#s1Msg').removeClass('d-none'); // si quieres, incluso puedes ocultar este div
            Swal.fire({
                icon: 'warning',
                title: 'Faltan datos de facturación',
                text: 'Completa todos los campos obligatorios de la sección de Facturación para continuar.'
            });
        } else {
            $('#s1Msg').addClass('d-none');
        }

        return ok;
    }

    function validateStep2() {
        let ok = true;
        let msg = '';

        if ($('#btnEnvio').hasClass('active')) {
            const req = [
                '#receptor_nombre',
                '#receptor_cedula',
                '#telefono_receptor',
                '#selectProvincia',
                '#selectCanton',
                '#selectSector',
                '#codigo_postal',
                '#horario_entrega',
                '#direccion_mapa'
            ];

            req.forEach(sel => {
                const $el = $(sel);
                if (!($el.val() || '').trim()) {
                    ok = false;
                    $el.addClass('is-invalid');
                } else {
                    $el.removeClass('is-invalid');
                }
            });

            if (!ok) {
                msg = 'Completa todos los campos obligatorios de la sección Envío a domicilio.';
            }

        } else {
            const $r = $('#pickup_responsable');
            ok = !!($r.val() || '').trim();
            $r.toggleClass('is-invalid', !ok);
            if (!ok) {
                msg = 'Selecciona el responsable que recogerá el pedido en tienda.';
            }
        }

        if (!ok) {
            $('#s2Msg').removeClass('d-none');
            Swal.fire({
                icon: 'warning',
                title: 'Faltan datos de envío',
                text: msg || 'Revisa los campos marcados en rojo para continuar.'
            });
        } else {
            $('#s2Msg').addClass('d-none');
        }

        return ok;
    }


    $('#btnToStep2').on('click', function () { if (validateStep1()) goStep2(); });
    $('#btnBackToStep1').on('click', goStep1);

    $('#btnEnvio').on('click', function () { $(this).addClass('active'); $('#btnPickup').removeClass('active'); $('#envioDomicilio').removeClass('d-none'); $('#pickupTienda').addClass('d-none'); });
    $('#btnPickup').on('click', function () { $(this).addClass('active'); $('#btnEnvio').removeClass('active'); $('#envioDomicilio').addClass('d-none'); $('#pickupTienda').removeClass('d-none'); cargarPuntosRecogidaDesdeCarrito(); });

    // wrapper de generarOrden para validar
    window.generarOrden = (function (orig) {
        return function () {
            if (!$('#step2').hasClass('d-none')) { if (!validateStep2()) return; }
            else { if (!validateStep1()) return goStep2(); }
            if (typeof orig === 'function') return orig();
        };
    })(window.generarOrden);
});

/* =================== Agencias =================== */
async function getCiudadesAgencia() {
    $.get("api/v1/fulmuv/getCiudadesAgencia/", function (r) {
        AGENCIAS_RAW = Array.isArray(r?.data) ? r.data : [];
        renderAgencias(AGENCIAS_RAW); // lista completa al inicio
    }, "json");
}

/* =================== LocalStorage Ident/Envio =================== */
// Purga inicial por si quedaron provincia/cantón/sector guardados
(function purgarUbicacionDeLS() {
    try {
        const raw = JSON.parse(localStorage.getItem(LS_ENVIO) || '{}');
        let touched = false;
        ['provincia', 'canton', 'sector'].forEach(k => { if (k in raw) { delete raw[k]; touched = true; } });
        if (touched) localStorage.setItem(LS_ENVIO, JSON.stringify(raw));
    } catch (_) { }
})();

function saveIdent() {
    const d = {
        fact_nombre: $('#fact_nombre').val() || '',
        tipo_identificacion: $('#tipo_identificacion').val() || '',
        identificacion: $('#identificacion').val() || '',
        correo: $('#correo').val() || '',
        telefono_fact: $('#telefono_fact').val() || '',
        direccion_fiscal: $('#direccion_fiscal').val() || '',
        forma_pago: $('#forma_pago').val() || '',
        correo_sesion: $('#correo_sesion').val() || '',
        password: $('#password').val() || ''
    };
    localStorage.setItem(LS_IDENT, JSON.stringify(d));
}
function restoreIdent() {
    const raw = localStorage.getItem(LS_IDENT); if (!raw) return; const d = JSON.parse(raw);
    $('#fact_nombre').val(d.fact_nombre || ''); $('#tipo_identificacion').val(d.tipo_identificacion || '');
    $('#identificacion').val(d.identificacion || ''); $('#correo').val(d.correo || '');
    $('#telefono_fact').val(d.telefono_fact || ''); $('#direccion_fiscal').val(d.direccion_fiscal || '');
    $('#forma_pago').val(d.forma_pago || ''); $('#correo_sesion').val(d.correo_sesion || '');
    $('#password').val(d.password || '');
}

// Guardar envío SIN provincia/cantón/sector
function saveEnvio() {
    if (modoEntrega !== 1) { localStorage.removeItem(LS_ENVIO); return; }
    const d = {
        receptor_nombre: $('#receptor_nombre').val() || '',
        receptor_cedula: $('#receptor_cedula').val() || '',
        telefono_receptor: $('#telefono_receptor').val() || '',
        codigo_postal: $('#codigo_postal').val() || '',
        horario_entrega: $('#horario_entrega').val() || '',
        direccion_mapa: $('#direccion_mapa').val() || '',
        referencia: $('#referencia').val() || '',
        observaciones: $('#observaciones_entrega').val() || '',
        lat: (typeof latitud !== 'undefined' ? latitud : ''),
        lng: (typeof longitud !== 'undefined' ? longitud : '')
    };
    localStorage.setItem(LS_ENVIO, JSON.stringify(d));
}

// Restaurar envío SIN tocar provincia/cantón/sector.
// Si hay ruta guardada, mostrar automáticamente el cuadro con tarifas.
function restoreEnvio() {
    const raw = localStorage.getItem(LS_ENVIO);
    if (raw) {
        const d = JSON.parse(raw);
        $('#receptor_nombre').val(d.receptor_nombre || '');
        $('#receptor_cedula').val(d.receptor_cedula || '');
        $('#telefono_receptor').val(d.telefono_receptor || '');
        $('#codigo_postal').val(d.codigo_postal || '');
        $('#horario_entrega').val(d.horario_entrega || '');
        $('#direccion_mapa').val(d.direccion_mapa || '');
        $('#referencia').val(d.referencia || '');
        $('#observaciones_entrega').val(d.observaciones || '');
        if (typeof d.lat === 'number' || typeof d.lat === 'string') {
            latitud = parseFloat(d.lat) || latitud;
            longitud = parseFloat(d.lng) || longitud;
            try {
                const pos = new google.maps.LatLng(latitud, longitud);
                map.setCenter(pos); marker.setPosition(pos);
            } catch (_) { }
        }
    }

    // Mostrar automáticamente el “valor referencial” si existe una ruta guardada
    try {
        const arr = JSON.parse(localStorage.getItem(LS_RUTAS) || '[]');
        if (Array.isArray(arr) && arr.length) {
            rutasSeleccionadas = arr;
            const last = arr[arr.length - 1];
            // if (last) renderEnvioCostoFromRuta(last, last._origen || '');
        }
    } catch (_) { }
}

/* Helpers de modo */
function applyModoEntregaUI() {
    if (modoEntrega === 1) {
        $('#btnEnvio').addClass('active'); $('#btnPickup').removeClass('active');
        $('#envioDomicilio').removeClass('d-none'); $('#pickupTienda').addClass('d-none');
    } else {
        $('#btnPickup').addClass('active'); $('#btnEnvio').removeClass('active');
        $('#envioDomicilio').addClass('d-none'); $('#pickupTienda').removeClass('d-none');
    }
    localStorage.setItem(LS_MODE, String(modoEntrega));
}
$(function () {
    const savedMode = localStorage.getItem(LS_MODE); if (savedMode === '0' || savedMode === '1') { modoEntrega = Number(savedMode); }
    applyModoEntregaUI();
    restoreIdent(); setTimeout(restoreEnvio, 100);

    $('#step1').on('input change', '#fact_nombre,#tipo_identificacion,#identificacion,#correo,#telefono_fact,#direccion_fiscal,#forma_pago,#correo_sesion,#password', saveIdent);
    // antes incluía #inputSector
    $('#step2').on('input change',
        '#receptor_nombre,#receptor_cedula,#telefono_receptor,#selectProvincia,#selectCanton,#selectSector,#codigo_postal,#horario_entrega,#direccion_mapa,#referencia,#observaciones_entrega',
        saveEnvio
    );



    $('#guardarUbicacion').on('click', saveEnvio);
    $('#btnEnvio').on('click', function () { modoEntrega = 1; applyModoEntregaUI(); saveEnvio(); });
    $('#btnPickup').on('click', function () { modoEntrega = 0; applyModoEntregaUI(); localStorage.removeItem(LS_ENVIO); });

    $('#btnFinalizar').on('click', function () { if (modoEntrega === 1) saveEnvio(); });

    // Limpiar LS al generar la orden (luego del éxito del AJAX en generarOrden)
    window.generarOrden = (function (orig) {
        return function () {
            localStorage.removeItem(LS_IDENT);
            localStorage.removeItem(LS_ENVIO);
            localStorage.removeItem(LS_MODE);
            if (typeof orig === 'function') return orig();
        };
    })(window.generarOrden);
});

/* =================== Google Maps =================== */
function initMap() {
    const defaultPos = { lat: -1.8312, lng: -78.1834 }; // Ecuador
    map = new google.maps.Map(document.getElementById("mapaEntrega"), { center: defaultPos, zoom: 14 });
    geocoder = new google.maps.Geocoder(); autocompleteService = new google.maps.places.AutocompleteService(); placesService = new google.maps.places.PlacesService(map);

    marker = new google.maps.Marker({ map, draggable: true, position: defaultPos });
    marker.addListener("dragend", () => {
        const pos = marker.getPosition();
        latitud = pos.lat(); longitud = pos.lng();
        obtenerDireccionDesdeCoords(latitud, longitud);
    });

    const input = document.getElementById("buscarDireccion");
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    const searchBox = new google.maps.places.SearchBox(input);
    map.addListener('bounds_changed', () => searchBox.setBounds(map.getBounds()));
    searchBox.addListener('places_changed', () => {
        const places = searchBox.getPlaces();
        if (!places || !places.length) return;

        const place = places[0];
        if (!place.geometry) return;

        const pos = place.geometry.location;
        map.setCenter(pos);
        map.setZoom(16);
        marker.setPosition(pos);

        latitud = pos.lat();
        longitud = pos.lng();

        $("#direccion_mapa").val(place.formatted_address || place.name || input.value);

        // ✅ EXTRAER Y SETEAR CÓDIGO POSTAL (si viene en el place)
        const cp1 = extraerCodigoPostalDeComponents(place.address_components || []);
        if (cp1) {
            setCodigoPostal(cp1);
        } else {
            // 🔁 Si no vino en place, hacemos reverse geocoding por coords
            obtenerDireccionDesdeCoords(latitud, longitud);
        }
    });

}
function obtenerDireccionDesdeCoords(lat, lng, callback = null) {
    const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
    geocoder.geocode({ location: latlng }, (results, status) => {
        if (status === "OK" && results[0]) {
            $("#direccion_mapa").val(results[0].formatted_address);
            $("#buscarDireccion").val(results[0].formatted_address);
            // ✅ EXTRAER Y SETEAR CÓDIGO POSTAL
            const cp = extraerCodigoPostalDeComponents(r0.address_components);
            setCodigoPostal(cp);
            // if (rutaSeleccionada) $('#envioCostoBox').removeClass('d-none');
            if (typeof callback === "function") callback();
        } else {
            if (typeof callback === "function") callback();
        }
    });
}

/* =================== Carrito / Orden =================== */
function actualizarshopCart() {
    let carrito = [];
    try {
        const stored = JSON.parse(localStorage.getItem("carrito"));
        const now = Date.now();
        if (stored && Array.isArray(stored.data) && now - stored.timestamp < 2 * 60 * 60 * 1000) {
            carrito = stored.data;
        }
    } catch (e) { }

    const totalItems = carrito.reduce((s, i) => s + i.cantidad, 0);
    $("#totalCarritoShop").text(totalItems);

    // Inicialización de acumuladores unificados con la lógica del carrito
    let totalValorNeto = 0;      // Subtotal real (sin IVA)
    let totalIVAGlobal = 0;      // IVA (15%)
    let totalAhorroCupon = 0;    // Ahorro por descuento (Precio Original - Precio Descuento)

    $("#listaTotalProductosCheckOut").empty();

    carrito.forEach(item => {
        const precioOriginal = parseFloat(item.precio) || 0;
        const precioConDesc = parseFloat(item.valor_descuento || item.precio) || 0;
        const cantidad = parseInt(item.cantidad) || 0;
        const tieneIVA = parseInt(item.iva) === 1;

        // 1. Cálculo de Ahorro por Descuento
        const ahorroUnitario = precioOriginal - precioConDesc;
        const ahorroTotalItem = ahorroUnitario * cantidad;
        totalAhorroCupon += ahorroTotalItem;

        // 2. Lógica de IVA (15%) siguiendo el primer código
        const subtotalConDescuento = precioConDesc * cantidad;
        const ivaCalculado = subtotalConDescuento * 0.15;

        let netoLinea = 0;
        let ahorroIVA = 0;

        if (tieneIVA) {
            // Precio ya incluye IVA -> quitarlo para el subtotal
            netoLinea = subtotalConDescuento - ivaCalculado;
            ahorroIVA = ivaCalculado;
        } else {
            // Precio sin IVA -> el neto es el subtotal
            netoLinea = subtotalConDescuento;
            ahorroIVA = ivaCalculado;
        }

        totalValorNeto += netoLinea;
        totalIVAGlobal += ahorroIVA;

        // --- LÓGICA VISUAL DE PRECIO TACHADO ---
        let htmlPrecio = '';
        if (precioOriginal > precioConDesc) {
            htmlPrecio = `
            <div class="text-end">
                <small class="text-muted" style="text-decoration: line-through; display: block; margin-bottom: -5px;">
                    ${formatoUSD(precioOriginal)}
                </small>
                <h4 class="text-brand mb-0">${formatoUSD(precioConDesc)}</h4>
            </div>`;
        } else {
            htmlPrecio = `<h4 class="text-brand text-end">${formatoUSD(precioOriginal)}</h4>`;
        }

        $("#listaTotalProductosCheckOut").append(`
            <tr>
                <td class="image product-thumbnail">
                    <img src="${item.imagen}" style="width:60px;height:60px;object-fit:cover" onerror="this.src='img/FULMUV-NEGRO.png';">
                </td>
                <td class="text-start">
                    <h6 class="w-200" style="font-size: 14px"><a href="#" class="text-heading">${item.nombre}</a></h6>
                </td>
                <td class="text-center"><h6 class="text-muted">x ${item.cantidad}</h6></td>
                <td class="text-end">${htmlPrecio}</td>
            </tr>
        `);
    });

    // TOTALES FINALES UNIFICADOS
    // El Total a Pagar es la suma del Neto + IVA (el ahorro ya está implícito en el precioConDesc)
    const totalFinal = totalValorNeto + totalIVAGlobal - totalAhorroCupon;

    $("#listaTotalProductosCheckOut").append(`
    <tr class="p-1">
      <td colspan="3"><strong class="h5 fw-bold">Subtotal</strong></td>
      <td class="text-end"><strong class="h5 fw-bold">${formatoUSD(totalValorNeto)}</strong></td>
    </tr>
    <tr class="p-1">
      <td colspan="3"><strong class="h5 fw-bold text-danger">Ahorro por Descuento</strong></td>
      <td class="text-end"><strong class="h5 fw-bold text-danger">-${formatoUSD(totalAhorroCupon)}</strong></td>
    </tr>
    <tr class="p-1">
      <td colspan="3"><strong class="h5 fw-bold">IVA (15%)</strong></td>
      <td class="text-end"><strong class="h5 fw-bold">${formatoUSD(totalIVAGlobal)}</strong></td>
    </tr>
    <tr class="p-1 total">
      <td colspan="3"><strong class="h5 fw-bold">Total a Pagar</strong></td>
      <td class="text-end"><strong class="h5 fw-bold">${formatoUSD(totalFinal)}</strong></td>
    </tr>
  `);

    $("#cart_subtotal_amount").text(formatoUSD(totalValorNeto));
    $("#cart_total_amount").text(formatoUSD(totalFinal));

    actualizarIconoCarrito?.();
    cargarPuntosRecogidaDesdeCarrito();
}

async function eliminarAllCarrito() {
    localStorage.removeItem("carrito");
    actualizarIconoCarrito?.();
    actualizarshopCart();
}

async function generarOrden() {
    // ✅ activar spinner
    setBtnLoading(true);

    try {
        const nombres = $("#receptor_nombre").val(),
            cedula = $("#receptor_cedula").val(),
            telefono = $("#telefono_receptor").val(),
            direccion = $("#direccion_mapa").val(),
            provincia = $("#selectProvincia").val(),
            canton = $("#selectCanton").val(),
            sector_nombre = ($("#selectSector").val() || '').trim(),
            sector_id = $("#selectSector option:selected").data("id") || null,
            canton_id = $("#selectCanton option:selected").data("id") || null,
            horario = $("#horario_entrega").val(),
            selectAgencia = $("#selectCiudadesAgencia").val();

        const codigo_postal = $("#codigo_postal").val();

        if (modoEntrega == 1) {
            if (!nombres || !cedula || !telefono || !direccion || !provincia || !canton || !sector_nombre || !horario || !$("#referencia").val() || !codigo_postal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Faltan datos de envío',
                    text: 'Completa todos los campos requeridos (incluido el código postal) en la sección de Envío a domicilio.'
                });
                setBtnLoading(false);
                return;
            }

            if (latitud === "" || longitud === "") {
                Swal.fire({ icon: 'warning', title: 'Faltan datos de envío', text: 'Agrega tu ubicación en el mapa.' });
                setBtnLoading(false);
                return;
            }
        } else {
            if (!$("#pickup_responsable").val()) {
                Swal.fire({ icon: 'warning', title: 'Faltan datos de recoger en la tienda', text: 'Seleccione el responsable que recogerá el pedido.' });
                setBtnLoading(false);
                return;
            }
        }

        // FACTURACIÓN
        const razon_social = $("#fact_nombre").val(),
            tipo_identificacion = $("#tipo_identificacion").val(),
            identificacion = $("#identificacion").val(),
            correo = $("#correo").val(),
            telefono_fact = $("#telefono_fact").val(),
            direccion_fiscal = $("#direccion_fiscal").val(),
            forma_pago = $("#forma_pago").val();

        // ... más validaciones ...
        if (!razon_social || !tipo_identificacion || !identificacion || !correo || !telefono_fact || !direccion_fiscal || !forma_pago) {
            Swal.fire({ icon: 'warning', title: 'Faltan datos de facturación', text: 'Completa todos los campos requeridos en la sección de Facturación.' });
            setBtnLoading(false);
            return;
        }

        const correo_sesion = $("#correo_sesion").val(), password = $("#password").val();
        if (!correo_sesion || !password) {
            Swal.fire({ icon: 'warning', title: 'Faltan datos de facturación', text: 'Completa todos los campos requeridos en la sección de Inicio de Sesión.' }); return;
        }

        const carrito = JSON.parse(localStorage.getItem("carrito"))?.data || [];
        if (carrito.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Carrito vacío',
                text: 'No tienes productos en tu carrito. Agrega al menos un producto antes de continuar.'
            });
            return;
        }

        const subtotal = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0),
            iva = subtotal * 0.15,
            total = subtotal + iva;

        // Agrupar productos por empresa
        const productosAgrupados = {};
        carrito.forEach(it => {
            if (!productosAgrupados[it.id_empresa]) productosAgrupados[it.id_empresa] = [];
            productosAgrupados[it.id_empresa].push(it);
        });
        const ordenes_empresas = Object.entries(productosAgrupados)
            .map(([id_empresa, productos]) => ({ id_empresa, productos, total: productos.reduce((acc, p) => acc + p.precio * p.cantidad, 0) }));

        const datos = {
            numero_orden: "ORD-" + Date.now(),
            subtotal: subtotal.toFixed(2), iva: iva.toFixed(2), total: total.toFixed(2),
            ordenes_empresas,
            correo_inicio: correo_sesion, password_inicio: password,
            envio_domicilio: modoEntrega,
            latitud, longitud,
            nombre_retiro: $("#pickup_responsable").val(),
            agencia_cercana: selectAgencia,
            datos_domicilio: {
                nombres, cedula, telefono,
                direccion_exacta: $("#direccion_mapa").val(),
                punto_referencial: $("#referencia").val(),
                provincia, canton,
                sector: sector_nombre,
                sector_id: sector_id,
                canton_id: canton_id,
                codigo_postal: $("#codigo_postal").val(),
                horario_entrega: horario,
                observaciones: $("#observaciones_entrega").val(),
                latitud, longitud
            },
            datos_facturacion: {
                tipo_identificacion, razon_social, numero_identificacion: identificacion, correo,
                telefono: telefono_fact, direccion: direccion_fiscal, forma_pago, correo_sesion, password
            },
            // ruta_seleccionada: rutaSeleccionada,           // última ruta elegida
            // rutas_seleccionadas: rutasSeleccionadas,       // historial completo

            /* ►► NUEVO: campos explícitos ◄◄ */
            id_ruta_seleccionada: idRutaSeleccionada,
            aplica_domicilio: aplica_domicilio,
            tarifa_envio: {
                trayecto: tarifaSeleccionada.trayecto,
                base_hasta_2kg: tarifaSeleccionada.base2kg,
                adicional_por_kg: tarifaSeleccionada.adicional1kg
            }

        };

        // ✅ AJAX (usa .always para garantizar que se quite el spinner)
        $.post("api/v1/fulmuv/generarOrden", datos, function (response) {
            if (!response.error && response.success) {
                Swal.fire({
                    title: "¡Orden generada!",
                    text: "Tu orden ha sido registrada exitosamente.",
                    icon: "success",
                    confirmButtonText: "Ver pedido"
                }).then(() => {
                    window.location.href = "seguimiento_pedido.php?q=" + response.numero_orden;
                });
            } else {
                Swal.fire({ title: "Error", text: response.msg, icon: "error", confirmButtonText: "Cerrar" });
            }
        }, 'json')
            .fail(function () {
                Swal.fire({ title: "Error", text: "No se pudo generar la orden. Intenta nuevamente.", icon: "error" });
            })
            .always(function () {
                setBtnLoading(false); // ✅ pase lo que pase, se quita el spinner
            });

    } catch (e) {
        console.error(e);
        setBtnLoading(false);
        Swal.fire({ title: "Error", text: "Ocurrió un error inesperado.", icon: "error" });
    }
}

function syncCodigoPostalRequired() {
    const isEnvio = $('#btnEnvio').hasClass('active');
    $('#codigo_postal').prop('required', isEnvio);

    // opcional: cambia placeholder para que se note
    $('#codigo_postal').attr('placeholder', isEnvio ? 'Código postal *' : 'Código postal (opcional)');
}

$(function () {
    syncCodigoPostalRequired();
    $('#btnEnvio, #btnPickup').on('click', syncCodigoPostalRequired);
});


function extraerCodigoPostalDeComponents(components = []) {
    const c = (components || []).find(x => (x.types || []).includes("postal_code"));
    return c ? (c.long_name || c.short_name || "") : "";
}

function setCodigoPostal(cp) {
    if (!cp) return; // si no viene, no sobreescribas
    $("#codigo_postal").val(cp).trigger("change"); // trigger para que saveEnvio lo guarde
}

// async function generarOrden() {
//     // ENVÍO
//     const nombres = $("#receptor_nombre").val(),
//         cedula = $("#receptor_cedula").val(),
//         telefono = $("#telefono_receptor").val(),
//         direccion = $("#direccion_mapa").val(),
//         provincia = $("#selectProvincia").val(),
//         canton = $("#selectCanton").val(),
//         sector = $("#selectSector").val(),
//         horario = $("#horario_entrega").val(),
//         selectAgencia = $("#selectCiudadesAgencia").val();

//     if (modoEntrega == 1) {
//         if (!nombres || !cedula || !telefono || !direccion || !provincia || !canton || !sector || !horario || !$("#referencia").val()) {
//             Swal.fire({ icon: 'warning', title: 'Faltan datos de envío', text: 'Completa todos los campos requeridos en la sección de Envío a domicilio.' }); return;
//         }
//         if (latitud === "" || longitud === "") { Swal.fire({ icon: 'warning', title: 'Faltan datos de envío', text: 'Agrega tu ubicación en el mapa.' }); return; }
//     } else {
//         if (!$("#pickup_responsable").val()) {
//             Swal.fire({ icon: 'warning', title: 'Faltan datos de recoger en la tienda', text: 'Seleccione el responsable que recogerá el pedido.' }); return;
//         }
//     }

//     // FACTURACIÓN
//     const razon_social = $("#fact_nombre").val(),
//         tipo_identificacion = $("#tipo_identificacion").val(),
//         identificacion = $("#identificacion").val(),
//         correo = $("#correo").val(),
//         telefono_fact = $("#telefono_fact").val(),
//         direccion_fiscal = $("#direccion_fiscal").val(),
//         forma_pago = $("#forma_pago").val();

//     if (!razon_social || !tipo_identificacion || !identificacion || !correo || !telefono_fact || !direccion_fiscal || !forma_pago) {
//         Swal.fire({ icon: 'warning', title: 'Faltan datos de facturación', text: 'Completa todos los campos requeridos en la sección de Facturación.' }); return;
//     }
//     const correo_sesion = $("#correo_sesion").val(), password = $("#password").val();
//     if (!correo_sesion || !password) {
//         Swal.fire({ icon: 'warning', title: 'Faltan datos de facturación', text: 'Completa todos los campos requeridos en la sección de Inicio de Sesión.' }); return;
//     }

//     const carrito = JSON.parse(localStorage.getItem("carrito"))?.data || [];
//     if (carrito.length === 0) {
//         Swal.fire({
//             icon: 'warning',
//             title: 'Carrito vacío',
//             text: 'No tienes productos en tu carrito. Agrega al menos un producto antes de continuar.'
//         });
//         return;
//     }

//     const subtotal = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0),
//         iva = subtotal * 0.15,
//         total = subtotal + iva;

//     // Agrupar productos por empresa
//     const productosAgrupados = {};
//     carrito.forEach(it => {
//         if (!productosAgrupados[it.id_empresa]) productosAgrupados[it.id_empresa] = [];
//         productosAgrupados[it.id_empresa].push(it);
//     });
//     const ordenes_empresas = Object.entries(productosAgrupados)
//         .map(([id_empresa, productos]) => ({ id_empresa, productos, total: productos.reduce((acc, p) => acc + p.precio * p.cantidad, 0) }));

//     const datos = {
//         numero_orden: "ORD-" + Date.now(),
//         subtotal: subtotal.toFixed(2), iva: iva.toFixed(2), total: total.toFixed(2),
//         ordenes_empresas,
//         correo_inicio: correo_sesion, password_inicio: password,
//         envio_domicilio: modoEntrega,
//         latitud, longitud,
//         nombre_retiro: $("#pickup_responsable").val(),
//         agencia_cercana: selectAgencia,
//         datos_domicilio: {
//             nombres, cedula, telefono,
//             direccion_exacta: $("#direccion_mapa").val(),
//             punto_referencial: $("#referencia").val(),
//             provincia, canton, sector,
//             codigo_postal: $("#codigo_postal").val(),
//             horario_entrega: horario,
//             observaciones: $("#observaciones_entrega").val(),
//             latitud, longitud
//         },
//         datos_facturacion: {
//             tipo_identificacion, razon_social, numero_identificacion: identificacion, correo,
//             telefono: telefono_fact, direccion: direccion_fiscal, forma_pago, correo_sesion, password
//         },
//         // ruta_seleccionada: rutaSeleccionada,           // última ruta elegida
//         // rutas_seleccionadas: rutasSeleccionadas,       // historial completo

//         /* ►► NUEVO: campos explícitos ◄◄ */
//         id_ruta_seleccionada: idRutaSeleccionada,
//         aplica_domicilio: aplica_domicilio,
//         tarifa_envio: {
//             trayecto: tarifaSeleccionada.trayecto,
//             base_hasta_2kg: tarifaSeleccionada.base2kg,
//             adicional_por_kg: tarifaSeleccionada.adicional1kg
//         }

//     };

//     $.post("api/v1/fulmuv/generarOrden", datos, function (response) {
//         if (!response.error && response.success) {
//             Swal.fire({
//                 title: "¡Orden generada!",
//                 text: "Tu orden ha sido registrada exitosamente.",
//                 icon: "success",
//                 confirmButtonText: "Ver pedido"
//             }).then(() => {
//                 // eliminarAllCarrito();
//                 window.location.href = "seguimiento_pedido.php?q=" + response.numero_orden;
//             });
//         } else {
//             Swal.fire({ title: "Error", text: response.msg, icon: "error", confirmButtonText: "Cerrar" });
//         }
//     }, 'json');
// }

function setBtnLoading(isLoading) {
    const $btn = $("#btnGenerarOrden");

    if (isLoading) {
        $btn.data("old-html", $btn.html());
        $btn.prop("disabled", true);
        $btn.html(`
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      Generando orden...
    `);
    } else {
        const old = $btn.data("old-html") || "Generar orden";
        $btn.html(old);
        // si tu lógica lo habilita/deshabilita por validación, puedes dejarlo en true/false según corresponda.
        // Aquí lo dejamos habilitado por defecto:
        $btn.prop("disabled", false);
    }
}


/* =================== Pickup =================== */
function cargarPuntosRecogidaDesdeCarrito() {
    let carrito = [];
    try {
        const stored = JSON.parse(localStorage.getItem("carrito"));
        carrito = (stored && Array.isArray(stored.data)) ? stored.data : [];
    } catch (e) { }

    const empresaIds = [...new Set(carrito.map(it => it.id_empresa).filter(Boolean))];

    if (empresaIds.length === 0) {
        $("#pickupLista").html('<div class="list-group-item small text-muted">No hay empresas en tu carrito.</div>');
        $("#pickupListaContainer").removeClass("d-none");
        return;
    }

    const peticiones = empresaIds.map(id => {
        if (pickupEmpresasCache.has(id)) return Promise.resolve(pickupEmpresasCache.get(id));
        return $.getJSON(`api/v1/fulmuv/empresas/${id}`).then(r => {
            if (!r.error && r.data) { pickupEmpresasCache.set(id, r.data); return r.data; }
            return null;
        }).catch(() => null);
    });

    Promise.all(peticiones).then(empresas => {
        const limpias = empresas.filter(Boolean);
        renderListaPuntosRecogida(limpias);
        if (limpias[0]) seleccionarPickupUI(limpias[0]);
        $("#pickupListaContainer").removeClass("d-none");
    });
}
function renderListaPuntosRecogida(empresas) {
    const $list = $("#pickupLista").empty();
    empresas.forEach(e => {
        const id = e.id_empresa || e.id,
            nombre = (e.nombre || "Empresa"),
            direccion = [e.direccion, e.ciudad, e.provincia].filter(Boolean).join(", "),
            lat = parseFloat(e.latitud), lng = parseFloat(e.longitud);

        $list.append(`
      <label class="list-group-item d-flex justify-content-between align-items-start">
        <div class="form-check">
          <input class="form-check-input me-2" type="radio" name="pickupSelect" value="${id}"
                 data-nombre="${nombre}" data-dir="${direccion}"
                 data-lat="${isFinite(lat) ? lat : ''}" data-lng="${isFinite(lng) ? lng : ''}">
          <span class="fw-semibold">${nombre}</span>
          <div class="small text-muted">${direccion || 'Sin dirección'}</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary ms-2 ver-mapa"
                data-nombre="${nombre}" data-dir="${direccion}"
                data-lat="${isFinite(lat) ? lat : ''}" data-lng="${isFinite(lng) ? lng : ''}">
          Ver ubicación
        </button>
      </label>
    `);
    });

    const $first = $list.find('input[name="pickupSelect"]').first();
    if ($first.length) $first.prop('checked', true).trigger('change');
}
function seleccionarPickupUI(dataOrInput) {
    const nombre = dataOrInput.nombre
        ? (dataOrInput.nombre)
        : ($(dataOrInput).data("nombre") || "");
    const dir = dataOrInput.direccion
        ? [dataOrInput.direccion, dataOrInput.ciudad, dataOrInput.provincia].filter(Boolean).join(", ")
        : ($(dataOrInput).data("dir") || "");

    $("#pickupNombre").text(nombre || "Punto de retiro seleccionado");
    $("#pickupDireccion").text(dir || "Selecciona un punto de recogida");
}
$(document).on("click", "#btnCambiarPickup", function (e) {
    e.preventDefault();
    $("#pickupListaContainer").toggleClass("d-none");
});
$(document).on("change", 'input[name="pickupSelect"]', function () {
    seleccionarPickupUI(this);
});
$(document).on("click", ".ver-mapa", function () {
    const lat = parseFloat($(this).data("lat")),
        lng = parseFloat($(this).data("lng")),
        nombre = $(this).data("nombre") || "Ubicación",
        dir = $(this).data("dir") || "";

    $("#modalMapaPickupLabel").text(nombre);
    $("#modalMapaPickupDireccion").text(dir);
    $("#modalMapaPickup").modal("show");

    $('#modalMapaPickup').one('shown.bs.modal', function () {
        const pos = (!isNaN(lat) && !isNaN(lng)) ? { lat, lng } : { lat: -1.8312, lng: -78.1834 }; // fallback Ecuador
        if (!mapaPickup) {
            mapaPickup = new google.maps.Map(document.getElementById("mapaPickup"), { center: pos, zoom: 15 });
            markerPickup = new google.maps.Marker({ map: mapaPickup, position: pos });
        } else {
            google.maps.event.trigger(mapaPickup, "resize");
            mapaPickup.setCenter(pos);
            markerPickup.setPosition(pos);
        }
    });
});

/* ========== Helpers de listas ========== */
function uniqOrdenado(arr) {
    return [...new Set(arr.filter(Boolean).map(s => String(s).trim()))]
        .sort((a, b) => a.localeCompare(b, 'es'));
}

/* Provincias */
// function cargarProvincias() {
//     // Trae todas las provincias (search vacío) y deduplica
//     return postJSON('api/v1/fulmuv/buscarProvinciaDestino', { search: '' })
//         .done(res => {
//             const $prov = $('#selectProvincia');
//             $prov.empty().append('<option value="">Seleccione una provincia</option>');
//             const provs = Array.isArray(res?.data) ? uniqOrdenado(res.data.map(r => r.provincia_destino)) : [];
//             provs.forEach(p => $prov.append(`<option value="${p}">${p}</option>`));
//         });
// }

function normKey(s = '') {
    return String(s)
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quita tildes
        .replace(/\s+/g, ' ') // colapsa espacios
        .trim()
        .toUpperCase();
}

function cargarProvincias() {
    console.log('=== cargarProvincias() INICIO ===');

    const reqRutas = postJSON('api/v1/fulmuv/buscarProvinciaDestino', { search: '' });
    const reqGrupo = $.get('api/v1/fulmuv/grupo_entrega/getProvinciasAll', null, null, 'json');

    return $.when(reqRutas, reqGrupo).done((rutasArr, grupoArr) => {
        console.log('1) rutasArr RAW:', rutasArr);
        console.log('2) grupoArr RAW:', grupoArr);

        // jQuery: $.when con $.ajax devuelve [data, status, xhr]
        const resRutas = rutasArr?.[0] ?? rutasArr ?? {};
        const resGrupo = grupoArr?.[0] ?? grupoArr ?? {};

        console.log('3) resRutas PARSE:', resRutas);
        console.log('4) resGrupo PARSE:', resGrupo);

        const $prov = $('#selectProvincia');
        $prov.empty().append('<option value="">Seleccione una provincia</option>');

        // A) Provincias desde rutas
        const rutasData = Array.isArray(resRutas?.data) ? resRutas.data : [];
        console.log('A) rutasData length:', rutasData.length);
        console.log('A.1) rutasData sample:', rutasData.slice(0, 3));

        const provsRutasRaw = rutasData
            .map(r => (r.provincia_destino || '').trim())
            .filter(Boolean);

        console.log('B.1) provsRutasRaw length:', provsRutasRaw.length);
        console.log('B.2) provsRutasRaw sample:', provsRutasRaw.slice(0, 10));

        const provsRutas = [...new Set(provsRutasRaw.map(p => normalizeStr(p)))];
        console.log('B.3) provsRutas NORMALIZADAS length:', provsRutas.length);
        console.log('B.4) provsRutas NORMALIZADAS sample:', provsRutas.slice(0, 20));

        // C) Provincias desde Grupo Entrega
        // TU RESPUESTA REAL: { error:false, provincia:{ Data:[...] } }
        const grupoList = Array.isArray(resGrupo?.provincia?.Data) ? resGrupo.provincia.Data : [];
        console.log('C) grupoList length:', grupoList.length);
        console.log('C.1) grupoList sample:', grupoList.slice(0, 5));

        // D) Mapa: NOMBRE_NORMALIZADO -> { nombreOriginal, id }
        const mapaGrupo = {};
        let excluidas = [];

        grupoList.forEach(p => {
            const nombre = (p.ProvinciaNombre || '').trim();
            const id = p.ProvinciaId ?? null;
            if (!nombre) return;

            const upper = normalizeStr(nombre);
            if (!upper) return;

            // excluir especiales + "0"
            if (PROVINCIAS_EXCLUIR.has(upper) || String(id) === '0') {
                excluidas.push({ nombre, id });
                return;
            }

            mapaGrupo[upper] = { nombre, id };
        });

        const keysGrupo = Object.keys(mapaGrupo);
        console.log('D) keysGrupo length:', keysGrupo.length);
        console.log('D.1) keysGrupo sample:', keysGrupo.slice(0, 20));
        console.log('D.2) excluidas:', excluidas);

        // E) Intersección
        const matches = provsRutas.filter(k => !!mapaGrupo[k]);
        console.log('E) matches length:', matches.length);
        console.log('E.1) matches sample:', matches.slice(0, 30));

        const noMatch = provsRutas.filter(k => !mapaGrupo[k]);
        console.log('F) noMatch length:', noMatch.length);
        console.log('F.1) noMatch sample:', noMatch.slice(0, 30));

        // F) Final (ordenado por nombre original)
        const final = matches
            .map(k => mapaGrupo[k])
            .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));

        console.log('G) final length:', final.length);
        console.log('G.1) final sample:', final.slice(0, 20));

        // Render
        PROVINCIAS_GE = new Set();
        final.forEach(p => {
            PROVINCIAS_GE.add(normalizeStr(p.nombre));
            $prov.append(`<option value="${p.nombre}" data-id="${p.id}">${p.nombre}</option>`);
        });

        console.log('=== cargarProvincias() FIN ===');
    }).fail((xhr) => {
        console.error('cargarProvincias() ERROR:', xhr);
    });
}


/* Cantones por provincia */
// function cargarCantones(provincia) {
//     const $can = $('#selectCanton');
//     $can.prop('disabled', true).empty().append('<option value="">Cargando cantones...</option>');
//     $('#selectSector').prop('disabled', true).empty().append('<option value="">Seleccione un sector</option>');
//     $('#envioCostoBox').addClass('d-none').empty();

//     return postJSON('api/v1/fulmuv/getCantonesDestinoByProvinciaLike', {
//         search: '',
//         nombre_provincia: provincia
//     }).done(res => {
//         const cantones = Array.isArray(res?.data) ? uniqOrdenado(res.data.map(r => r.canton_destino)) : [];
//         $can.empty().append('<option value="">Seleccione un cantón</option>');
//         cantones.forEach(c => $can.append(`<option value="${c}">${c}</option>`));
//         $can.prop('disabled', cantones.length === 0);

//         // Aviso si NO existe ninguna ruta con entrega a domicilio (Y) en la provincia
//         const existeY = Array.isArray(res?.data) && res.data.some(r => String(r.aplica_domicilio || '').toUpperCase() === 'Y');
//         $('#cantonAviso').toggleClass('d-none', !!existeY)
//             .text(existeY ? '' : 'No se encontraron rutas con entrega a domicilio para esta provincia. Selecciona un cantón para ver opciones.');
//     });
// }

/* Cantones por provincia:
   1) getCantonesDestinoByProvinciaLike (rutas)
   2) grupo_entrega/getCantones/ByIdProvincia (carrier)
   3) Intersección para minimizar lista */
function cargarCantones(provincia) {
    const $can = $('#selectCanton');
    $can.prop('disabled', true).empty().append('<option value="">Cargando cantones...</option>');
    $('#selectSector').prop('disabled', true).empty().append('<option value="">Seleccione un sector</option>');
    // $('#envioCostoBox').addClass('d-none').empty();

    // ID provincia (carrier) desde el select
    const idProvincia = $('#selectProvincia option:selected').data('id');
    if (!idProvincia) {
        $can.empty().append('<option value="">Seleccione un cantón</option>').prop('disabled', true);
        return $.Deferred().resolve().promise();
    }

    // 1) Cantones desde rutas
    const reqRutas = postJSON('api/v1/fulmuv/getCantonesDestinoByProvinciaLike', {
        search: '',
        nombre_provincia: provincia
    });

    // 2) Cantones desde carrier
    const reqCarrier = postJSON('api/v1/fulmuv/grupo_entrega/getCantones/ByIdProvincia', {
        id_provincia: idProvincia
    });

    return $.when(reqRutas, reqCarrier).done((resRutasArr, resCarrierArr) => {
        const resRutas = resRutasArr[0] || {};
        const resCarrier = resCarrierArr[0] || {};

        const rutasData = Array.isArray(resRutas.data) ? resRutas.data : [];
        const carrierData = (resCarrier && resCarrier.canton && Array.isArray(resCarrier.canton.Data))
            ? resCarrier.canton.Data
            : [];

        // Cantones de rutas (set)
        const cantonesRutasSet = new Set(
            rutasData
                .map(r => (r.canton_destino || '').trim())
                .filter(Boolean)
                .map(s => s.toUpperCase())
        );

        // Mapa carrier: NOMBRE_MAYUS -> { nombreOriginal, id }
        const mapaCarrier = {};
        carrierData.forEach(c => {
            const nombre = (c.CantonNombre || '').trim();
            if (!nombre) return;
            mapaCarrier[nombre.toUpperCase()] = { nombre, id: c.CantonId };
        });

        // Intersección: solo cantones que están en rutas y en carrier
        let final = [];
        cantonesRutasSet.forEach(upper => {
            if (mapaCarrier[upper]) final.push(mapaCarrier[upper]);
        });

        // Ordenar por nombre
        final.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));

        // Render
        $can.empty().append('<option value="">Seleccione un cantón</option>');
        final.forEach(c => {
            $can.append(`<option value="${c.nombre}" data-id="${c.id}">${c.nombre}</option>`);
        });
        $can.prop('disabled', final.length === 0);

        // Aviso si NO existe ninguna ruta con entrega a domicilio (Y) en la provincia (esto viene de rutas)
        const existeY = rutasData.some(r => String(r.aplica_domicilio || '').toUpperCase() === 'Y');
        $('#cantonAviso')
            .toggleClass('d-none', !!existeY)
            .text(existeY ? '' : 'No se encontraron rutas con entrega a domicilio para esta provincia. Selecciona un cantón para ver opciones.');
    });
}
