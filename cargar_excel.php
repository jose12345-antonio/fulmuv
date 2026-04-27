<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Subir Excel y leer en JS</title>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- SheetJS -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .box { max-width: 900px; margin: auto; }
    pre {
      background: #111;
      color: #eee;
      padding: 15px;
      border-radius: 8px;
      max-height: 350px;
      overflow: auto;
    }
    button { padding: 10px 14px; cursor: pointer; }
    input { padding: 8px; width: 100%; }
  </style>
</head>

<body>
<div class="box">
  <h2>Subir Excel (.xlsx / .xls)</h2>

  <label>Selecciona el archivo:</label>
  <input type="file" id="excelFile" accept=".xlsx,.xls,.csv">

  <br><br>

  <button id="btnEnviar" disabled>Enviar a API</button>

  <h3>Preview JSON</h3>
  <pre id="preview">Aquí se mostrará el contenido...</pre>
</div>

<script>
const excelInput = document.getElementById("excelFile");
const preview = document.getElementById("preview");
const btnEnviar = document.getElementById("btnEnviar");

let excelRows = [];

// 1️⃣ Leer el Excel
excelInput.addEventListener("change", async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  try {
    const buffer = await file.arrayBuffer();
    const workbook = XLSX.read(buffer, { type: "array" });

    const sheetName = workbook.SheetNames[0];
    const sheet = workbook.Sheets[sheetName];

    // Leer como matriz (posición fija)
    let rows = XLSX.utils.sheet_to_json(sheet, {
      header: 1,
      defval: ""
    });

    // Eliminar encabezado
    rows.shift();

    // Mapear al JSON requerido
    excelRows = rows
      .filter(r => r.length >= 8) // filas válidas
      .map(r => ({
        provincia: String(r[0] ?? "").trim(),
        canton: String(r[1] ?? "").trim(),
        parroquia: String(r[2] ?? "").trim(),
        tipo_cobertura: String(r[3] ?? "").trim(),
        dias_laborables: String(r[4] ?? "").trim(),
        fuera_cobertura: String(r[5] ?? "").trim(),
        valor_kg: parseFloat(String(r[6]).replace(",", ".")) || 0,
        valor_kg_adicional: parseFloat(String(r[7]).replace(",", ".")) || 0
      }))
      // Validación mínima
      .filter(r => r.provincia && r.canton && r.tipo_cobertura);

    preview.textContent = JSON.stringify(excelRows, null, 2);
    btnEnviar.disabled = excelRows.length === 0;

  } catch (err) {
    console.error(err);
    alert("Error al leer el Excel");
    preview.textContent = "Error leyendo el archivo.";
    btnEnviar.disabled = true;
  }
});

// 2️⃣ Enviar a la API
btnEnviar.addEventListener("click", () => {
  if (!excelRows.length) {
    alert("No hay datos para enviar");
    return;
  }

  $.post("api/v1/fulmuv/rutas/insertExcel", {
    data: JSON.stringify(excelRows)
  })
  .done(resp => {
    console.log("Respuesta API:", resp);
    alert("Datos enviados correctamente");
  })
  .fail(err => {
    console.error(err.responseText);
    alert("Error al enviar a la API");
  });
});
</script>
</body>
</html>
