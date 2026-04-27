$("#subirArchivo").change(function (event) {
  let file = event.target.files[0];
  if (!file) return;

  let reader = new FileReader();

  reader.onload = function (e) {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: "array" });

    const hoja = workbook.Sheets["ATRIBUTOS +"];
    const datos = XLSX.utils.sheet_to_json(hoja, { header: 1 });

    let resultado = [];
    let referenciaActual = "";
    let tituloOriginal = "";
    let filaInicio = 570;
    let filaFin = 1988;

    const referenciasMap = {
      "ATRIBUTOS PARA Servicios Mecánicos, Mecánicas y Técnicos": "Servicios Mecánicos, Mecánicas y Técnicos",
      "ATRIBUTOS PARA Servicios de Estética, Detailing y Limpieza": "Servicios de Estética, Detailing y Limpieza",
      "ATRIBUTOS PARA Servicios para Flotas y Empresas de Transporte": "Servicios para Flotas y Empresas de Transporte",
      "ATRIBUTOS PARA Servicios para Bicicletas y Scooters": "Servicios para Bicicletas y Scooters",
      "ATRIBUTOS PARA Servicios de Aire Acondicionado": "Servicios de Aire Acondicionado",
      "ATRIBUTOS PARA Servicios de Parabrisas y Vidrios": "Servicios de Parabrisas y Vidrios",
      "ATRIBUTOS PARA Servicios de Importación y Exportación": "Servicios de Importación y Exportación",
      "ATRIBUTOS PARA Servicios de Asesoría Vehicular": "Servicios de Asesoría Vehicular",
      "ATRIBUTOS PARA Servicios de Construcción y Personalización de Vehículos de Alto Rendimiento": "Servicios de Construcción y Personalización de Vehículos de Alto Rendimiento",
      "ATRIBUTOS PARA Servicios de Reparación de Carrocería y Pintura": "Servicios de Reparación de Carrocería y Pintura",
      "ATRIBUTOS PARA Servicio de Tapicería": "Servicio de Tapicería",
      "ATRIBUTOS PARA Servicios de Potenciación Vehicular": "Servicios de Potenciación Vehicular",
      "ATRIBUTOS PARA Servicios de Lubricación y Fluidos": "Servicios de Lubricación y Fluidos",
      "ATRIBUTOS PARA Servicios Preventivos": "Servicios Preventivos",
      "ATRIBUTOS PARA Servicios de Llantas y Aros": "Servicios de Llantas y Aros",
      "ATRIBUTOS PARA Servicios Alternativos de Propulsión Vehicular": "Servicios Alternativos de Propulsión Vehicular",
      "ATRIBUTOS PARA Servicios de Luces y Faros": "Servicios de Luces y Faros",
      "ATRIBUTOS PARA Servicios de Venta y Comercio": "Servicios de Venta y Comercio",
      "ATRIBUTOS PARA Servicios de Seguros Vehiculares": "Servicios de Seguros Vehiculares",
      "ATRIBUTOS PARA Servicios de Capacitación y Formación": "Servicios de Capacitación y Formación",
      "ATRIBUTOS PARA Servicios de Gestión y Trámites Vehiculares": "Servicios de Gestión y Trámites Vehiculares",
      "ATRIBUTOS PARA Servicios de Dirección y Transmisión": "Servicios de Dirección y Transmisión",
      "ATRIBUTOS PARA Servicios de Frenos y Sistemas de Estabilidad y Tracción": "Servicios de Frenos y Sistemas de Estabilidad y Tracción",
      "ATRIBUTOS PARA Servicio de Eléctrica y Electrónica": "Servicio de Eléctrica y Electrónica",
      "ATRIBUTOS PARA Servicios de Asistencia en Carretera y Emergencias": "Servicios de Asistencia en Carretera y Emergencias",
      "ATRIBUTOS PARA Sistemas de Sonido, Vídeo y Mutimedia": "Sistemas de Sonido, Vídeo y Mutimedia",
      "ATRIBUTOS PARA Servicios de Movilidad y Transporte": "Servicios de Movilidad y Transporte",
      "ATRIBUTOS PARA Servicios para Motores": "Servicios para Motores",
      "ATRIBUTOS PARA Sistemas de Escape": "Sistemas de Escape",
      "ATRIBUTOS PARA Servicios de Suspensión y Amortiguación": "Servicios de Suspensión y Amortiguación",
      "ATRIBUTOS PARA Servicios de Innovación y Tecnología": "Servicios de Innovación y Tecnología",
      "ATRIBUTOS PARA Servicios de Seguridad y Blindaje Automotriz": "Servicios de Seguridad y Blindaje Automotriz",
      "ATRIBUTOS PARA Lavadoras y Vulcanizadoras Automotrices": "Lavadoras y Vulcanizadoras Automotrices",
      "ATRIBUTOS PARA Lavadoras Premium": "Lavadoras Premium",
      "ATRIBUTOS PARA Vulcanizadoras Premium": "Vulcanizadoras Premium",
      "ATRIBUTOS PARA BasicMuv: 1 Lavadora Básica": "BasicMuv: 1 Lavadora Básica",
      "ATRIBUTOS PARA BasicMuv: 1 Vulcanizadora Básica": "BasicMuv: 1 Vulcanizadora Básica"
    };

    let bloqueActual = null;

    for (let i = filaInicio; i <= filaFin; i++) {
      let colB = datos[i]?.[1]?.toString().trim() ?? ""; // Nombre del atributo
      let colC = datos[i]?.[2]?.toString().trim().toUpperCase() ?? ""; // Tipo de dato desde columna C
    
      if (/^ATRIBUTOS PARA/i.test(colB) || /^SEGURIDAD Y PROTECCION VEHICULAR/i.test(colB)) {
        tituloOriginal = colB;
        referenciaActual = referenciasMap[colB] ?? null;
    
        if (referenciaActual) {
          bloqueActual = {
            referencia: referenciaActual,
            titulo: tituloOriginal,
            atributos: []
          };
          resultado.push(bloqueActual);
        } else {
          bloqueActual = null;
        }
    
        continue;
      }
    
      if (bloqueActual && colB !== "") {
        let limpio = colB.replace(/\s*\(.*?\)\s*/g, "").trim();
      
        // Validar tipo de dato desde el texto del atributo (colB o limpio)
        let tipo_dato = "TEXTO"; // Por defecto
        let referencia = colC.toUpperCase(); // O usa limpio.toUpperCase()
      
        if (referencia.includes("DESPLEGABLE")) {
          tipo_dato = "OPCIONES";
        } else if (referencia.includes("SI") && referencia.includes("NO")) {
          tipo_dato = "BOOLEANO";
        } else if (referencia.includes("NÚMERO") || referencia.includes("NUMERO")) {
          tipo_dato = "NUMERO";
        } else if (referencia.includes("MULTIPLE")) {
          tipo_dato = "MULTIOPCION";
        } else if (referencia.includes("FECHA")) {
          tipo_dato = "FECHA";
        }
      
        const yaExiste = bloqueActual.atributos.some(
          a => a.limpio.toLowerCase() === limpio.toLowerCase()
        );
      
        if (limpio && !yaExiste) {
          bloqueActual.atributos.push({
            limpio: limpio,
            tipo_dato: tipo_dato
          });
        }
      }
      
    }
    

    console.log("Array generado para enviar a PHP:", resultado);

    enviarLotes(resultado);

    // Enviar a PHP si lo necesitas
    // $.post('../api/v1/cargarExcel', {
    //   data:resultado
    // }, function (returnedData) {
    //   var returned = JSON.parse(returnedData)
    //   console.log(returned);
    // });
  };

  reader.readAsArrayBuffer(file);
});

//servicios y categorias
/*
$("#subirArchivo").change(function (event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function (e) {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: "array" });

    const hoja = workbook.Sheets["REPUESTOS Y ACCESORIOS"];
    const datos = XLSX.utils.sheet_to_json(hoja, { header: 1 });

    let resultado = [];
    let categoriaActual = null;

    for (let i = 5250; i <= 5755; i++) {
      const fila = datos[i];
      if (!fila) continue;

      const colA = fila[0]?.toString().trim(); // Categoría
      const colB = fila[1]?.toString().trim(); // Servicio

      // Si hay categoría en la columna A, iniciamos una nueva categoría
      if (colA && !colB) {
        categoriaActual = {
          categoria: colA,
          servicios: []
        };
        resultado.push(categoriaActual);
      }

      // Si hay servicio en la columna B y tenemos una categoría actual
      if (colB && categoriaActual) {
        categoriaActual.servicios.push(colB);
      }
    }

    console.log("✅ Array de categorías con servicios:", resultado);

    // enviarLotes(resultado)
  };

  reader.readAsArrayBuffer(file);
});
*/

function limpiarTexto(texto) {
    return texto
      .toString()
      .replace(/[\r\n\t]/g, '')       // eliminar saltos de línea, tabulaciones
      .replace(/:/g, '')              // eliminar todos los dos puntos
      .replace(/\s+/g, ' ')           // espacios múltiples → 1 solo
      .trim();                        // quitar espacios al inicio y final
  }

function enviarLotes(modelos) {
  let lotes = 10;
  let total = modelos.length;
  let index = 0;

  function enviarSiguienteLote() {
      if (index >= total) {
          console.log("Todos los modelos enviados.");
          return;
      }
      let lote = modelos.slice(index, index + lotes);
      $.post('../api/v1/cargarExcel', {
        data:lote
      }, function (returnedData) {
        var returned = JSON.parse(returnedData)
        console.log("Lote enviado:", lote.length, returned);
        index += lotes;
        enviarSiguienteLote();
      });
  }

  enviarSiguienteLote();
}

