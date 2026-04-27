const $desde = $('#repDesde');
const $hasta = $('#repHasta');
const $tipo = $('#repTipo');
const idEmpresa = $('#id_empresa_reportes').val();

function setFechasDefault() {
  const hoy = new Date();
  const hace30 = new Date();
  hace30.setDate(hoy.getDate() - 30);
  $desde.val(hace30.toISOString().slice(0, 10));
  $hasta.val(hoy.toISOString().slice(0, 10));
}

function numberOrZero(v) {
  const n = Number(v);
  return isNaN(n) ? 0 : n;
}

function renderTipoEventoChart(data) {
  const el = document.getElementById('chartTipoEvento');
  if (!el) return;
  const chart = window.echarts.init(el);
  const series = (data || []).map(d => ({ name: d.tipo_evento, value: numberOrZero(d.total) }));

  chart.setOption({
    tooltip: { trigger: 'item' },
    legend: { top: 8 },
    series: [{
      type: 'pie',
      radius: ['40%', '70%'],
      data: series
    }]
  });
}

function renderTopProductosChart(data) {
  const el = document.getElementById('chartTopProductos');
  if (!el) return;
  const chart = window.echarts.init(el);
  const labels = (data || []).map(d => {
    const base = d.nombre || `#${d.id_producto}`;
    const tipo = d.tipo ? `(${d.tipo})` : '';
    return `${base} ${tipo}`.trim();
  });
  const values = (data || []).map(d => numberOrZero(d.total));

  chart.setOption({
    tooltip: { trigger: 'axis' },
    grid: { left: 40, right: 16, top: 20, bottom: 40 },
    xAxis: { type: 'category', data: labels, axisLabel: { rotate: 30 } },
    yAxis: { type: 'value' },
    series: [{
      type: 'bar',
      data: values,
      itemStyle: { color: '#00686f' }
    }]
  });
}

function renderTabla(data) {
  const $tbody = $('#tbodyInteracciones').empty();
  if (!Array.isArray(data) || !data.length) {
    $tbody.append('<tr><td colspan="4" class="text-center text-muted">No hay registros.</td></tr>');
    return;
  }

  data.forEach(r => {
    $tbody.append(`
      <tr>
        <td>${r.producto || '-'}</td>
        <td>${r.tipo || '-'}</td>
        <td>${r.tipo_evento || '-'}</td>
        <td>${r.fecha || '-'}</td>
      </tr>
    `);
  });

  if ($.fn.DataTable.isDataTable('#tablaInteracciones')) {
    $('#tablaInteracciones').DataTable().destroy();
  }
  $('#tablaInteracciones').DataTable({
    responsive: true,
    lengthChange: false,
    pageLength: 10
  });
}

function cargarReportes() {
  const desde = $desde.val();
  const hasta = $hasta.val();
  const tipo = $tipo.val() || "todos";

  $.get('../api/v1/fulmuv/reportes/interacciones', {
    id_empresa: idEmpresa,
    desde,
    hasta,
    tipo
  }, function (res) {
    if (res?.error) {
      SweetAlert("error", res.msg || "No se pudieron cargar los reportes.");
      return;
    }

    $('#kpiTotalInteracciones').text(numberOrZero(res?.kpis?.total_interacciones));
    $('#kpiSesiones').text(numberOrZero(res?.kpis?.sesiones));
    $('#kpiProductos').text(numberOrZero(res?.kpis?.productos));
    $('#kpiEventos').text(numberOrZero(res?.kpis?.eventos));

    renderTipoEventoChart(res?.por_tipo || []);
    renderTopProductosChart(res?.top_productos || []);
    renderTabla(res?.detalle || []);
  }, 'json');
}

$(document).ready(function () {
  setFechasDefault();
  cargarReportes();
  $('#btnRefrescarReportes').on('click', cargarReportes);
  $tipo.on('change', cargarReportes);
});
