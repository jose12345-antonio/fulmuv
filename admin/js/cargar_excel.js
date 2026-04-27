/*$("#subirArchivo").change(function (event) {
    let file = event.target.files[0];
    if (!file) return;

    let reader = new FileReader();

    reader.onload = function (e) {
        let data = new Uint8Array(e.target.result);
        let workbook = XLSX.read(data, { type: "array" });

        let sheetName = "COBERTURAS";
        let sheet = workbook.Sheets[sheetName];

        if (!sheet) {
            alert("No se encontró la hoja: " + sheetName);
            return;
        }

        let jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

        console.log(jsonData)

        let rutas = [];

        for (let i = 22; i < jsonData.length; i++) {
            let fila = jsonData[i];
            let columnaMarca = fila[0]; // Columna D
            let columnaModelo = fila[1]; // Columna E
            let tipo = fila[2];          // Columna F
            let motor = fila[3];         // Columna G


            // Si hay modelo, registramos el objeto completo
            if (columnaModelo) {
                rutas.push({
                    marca: marcaActual,
                    modelo: columnaModelo.trim(),
                    tipo: tipo ? tipo.trim() : null,
                    motor: motor ? motor.trim() : null
                });
            }
        }

        console.log("Rutas encontrados:", modelos);
        //enviarLotes(modelos); // Llamada opcional
    };

    reader.readAsArrayBuffer(file);
});


function enviarLotes(modelos) {
  let lotes = 10;
  let total = modelos.length;
  let index = 0;

  function enviarSiguienteLote() {
      if (index >= total) {
          console.log("Todas las rutas enviados.");
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
*/

// ===== utilidades =====
function col(letter){
  letter = letter.toUpperCase();
  let n = 0;
  for (let i=0;i<letter.length;i++) n = n*26 + (letter.charCodeAt(i)-64);
  return n-1; // 0-based
}
const upOrNull = v => {
  if (v === undefined || v === null) return null;
  const x = String(v).trim();
  return x === "" ? null : x.toUpperCase();
};
const upOrEmpty = v => { // para J,K,L,M: vacío => ""
  if (v === undefined || v === null) return "";
  const x = String(v).trim();
  return x === "" ? "" : x.toUpperCase();
};
const isBlank = v => v === undefined || v === null || String(v).trim() === "";

// ===== índices según tu pedido =====
const IDX = {
  provincia_origen:  col('F'), // 5
  canton_origen:     col('G'), // 6
  provincia_destino: col('C'), // 2
  canton_destino:    col('D'), // 3
  trayecto:          col('I'), // 8
  aplica_col_H:      col('H'), // 7  -> vacío=Y, lleno=N
  zona_peligrosa:    col('H'), // 8

  // NUEVOS
  agencia_1:         col('J'), // 9
  direccion_1:       col('K'), // 10
  agencia_2:         col('L'), // 11
  direccion_2:       col('M')  // 12
};

// detecta header (por si hay filas antes)
function findHeaderRow(matrix){
  const keys = ["provincia origen","ciudad","cantón","canton","tipo de trayecto","provincia destino","zona peligrosa"];
  let best = 0, bestScore = -1;
  for (let i=0; i<Math.min(25, matrix.length); i++){
    const row = (matrix[i]||[]).map(c => String(c||"").toLowerCase());
    const score = row.reduce((s,cell)=> s + keys.some(k=>cell.includes(k)), 0);
    if (score > bestScore){ bestScore = score; best = i; }
  }
  return best;
}

$("#subirArchivo").on("change", function (event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(e){
    try{
      const data = new Uint8Array(e.target.result);
      const wb = XLSX.read(data, { type: "array" });
      const sheetName = wb.Sheets["COBERTURAS"] ? "COBERTURAS" : wb.SheetNames[0];
      const sh = wb.Sheets[sheetName];
      if (!sh){ alert("No se encontró la hoja."); return; }

      const matrix = XLSX.utils.sheet_to_json(sh, { header: 1, raw: false });

      const headerRow = findHeaderRow(matrix);
      const startRow = headerRow + 1;

      const records = [];
      for (let r=startRow; r<matrix.length; r++){
        const row = matrix[r] || [];

        const aplica = isBlank(row[IDX.aplica_col_H]) ? 'Y' : 'N';  // H: vacío=Y, lleno=N

        const rec = {
          provincia_origen:  upOrEmpty(row[IDX.provincia_origen]),
          canton_origen:     upOrEmpty(row[IDX.canton_origen]),
          provincia_destino: upOrNull(row[IDX.provincia_destino]),
          canton_destino:    upOrNull(row[IDX.canton_destino]),
          trayecto:          upOrNull(row[IDX.trayecto]),
          aplica_domicilio:  aplica,
          zona_peligrosa:    upOrEmpty(row[IDX.zona_peligrosa]),

          // NUEVOS (vacío => "")
          agencia_1:         upOrEmpty(row[IDX.agencia_1]),
          direccion_1:       upOrEmpty(row[IDX.direccion_1]),
          agencia_2:         upOrEmpty(row[IDX.agencia_2]),
          direccion_2:       upOrEmpty(row[IDX.direccion_2]),
        };

        // descarta filas totalmente vacías (sin afectar a los campos "" de J..M)
        const coreKeys = ['provincia_origen','canton_origen','provincia_destino','canton_destino','trayecto'];
        const algunCore = coreKeys.some(k => rec[k] !== null);
        if (!algunCore && rec.agencia_1 === "" && rec.direccion_1 === "" && rec.agencia_2 === "" && rec.direccion_2 === "") {
          continue;
        }

        records.push(rec);
      }

      console.log("Ejemplo (5):", records.slice(0,5));
      console.log("Total:", records.length);
      // enviarLotes(records); // si quieres mandarlos a tu API
      const LAST_N = 691;
      const toSend = records.slice(-LAST_N); // toma los últimos 691 (si hay menos, toma todos)
      console.log(`Enviando solo los últimos ${toSend.length} de ${records.length} registros`);
      // enviarLotes(toSend);
    }catch(err){
      console.error(err);
      alert("Error leyendo el Excel (revisa consola).");
    }
  };
  reader.readAsArrayBuffer(file);
});

// (opcional) envío por lotes a tu backend
async function enviarLotes(records){
  const ENDPOINT = "../api/v1/cargarExcel";
  const BATCH_SIZE = 300;
  let i = 0;
  while (i < records.length){
    const lote = records.slice(i, i+BATCH_SIZE);
    await $.ajax({
      url: ENDPOINT,
      method: "POST",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      data: JSON.stringify({ data: lote })
    });
    i += BATCH_SIZE;
    await new Promise(r => setTimeout(r, 120));
  }
  alert("Importación completada");
}