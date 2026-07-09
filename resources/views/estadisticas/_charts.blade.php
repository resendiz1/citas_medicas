<div class="card shadow-2 p-4 mb-4" id="estadisticas-section">
    <h5 class="fw-bold mb-3" style="color:#1266f1">
        <i class="fa fa-chart-simple me-2"></i>Estadísticas
    </h5>
    <div id="stats-error" class="alert alert-danger py-2 mb-3 d-none" style="font-size:0.85rem;border-radius:8px"></div>

    <div class="row g-4" id="stats-cards">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-2 p-3 text-center" style="border-radius:12px">
                <div class="stat-icon mx-auto mb-2" style="color:#1266f1;font-size:1.5rem"><i class="fa fa-calendar-check"></i></div>
                <h6>Total Citas</h6>
                <p class="display-6 mb-0 fw-bold" id="stats-total-citas">—</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-2 p-3 text-center" style="border-radius:12px">
                <div class="stat-icon mx-auto mb-2" style="color:#00b894;font-size:1.5rem"><i class="fa fa-circle-check"></i></div>
                <h6>Finalizadas</h6>
                <p class="display-6 mb-0 fw-bold" id="stats-finalizadas">—</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-2 p-3 text-center" style="border-radius:12px">
                <div class="stat-icon mx-auto mb-2" style="color:#ff4444;font-size:1.5rem"><i class="fa fa-circle-xmark"></i></div>
                <h6>Canceladas</h6>
                <p class="display-6 mb-0 fw-bold" id="stats-canceladas">—</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-2 p-3" style="border-radius:12px">
                <h6 class="fw-bold mb-3 text-center" style="color:#555">Citas por Estado</h6>
                <div class="chart-wrapper" style="position:relative;height:220px">
                    <canvas id="chart-estado"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-2 p-3" style="border-radius:12px">
                <h6 class="fw-bold mb-3 text-center" style="color:#555">Citas por Mes</h6>
                <div class="chart-wrapper" style="position:relative;height:220px">
                    <canvas id="chart-mes"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-2 p-3" style="border-radius:12px">
                <h6 class="fw-bold mb-3 text-center" style="color:#555" id="chart-med-title">Medicamentos más Recetados</h6>
                <div id="chart-med-container" class="chart-wrapper" style="position:relative;height:220px">
                    <canvas id="chart-medicamentos"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-2 p-3" style="border-radius:12px">
                <h6 class="fw-bold mb-3 text-center" style="color:#555" id="chart-diag-title">Diagnósticos más Frecuentes</h6>
                <div id="chart-diag-container" class="chart-wrapper" style="position:relative;height:220px">
                    <canvas id="chart-diagnosticos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2 d-none" id="stats-medicos-row">
        <div class="col-12 col-md-12">
            <div class="card border-0 shadow-2 p-3" style="border-radius:12px">
                <h6 class="fw-bold mb-3 text-center" style="color:#555">Médicos más Visitados</h6>
                <div class="chart-wrapper" style="position:relative;height:200px">
                    <canvas id="chart-medicos"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statsUrl = {!! json_encode($statsUrl) !!};

    fetch(statsUrl)
        .then(r => r.ok ? r.json() : Promise.reject('Error ' + r.status))
        .then(data => {
            const coloresEstado = {
                pendiente: '#1266f1', confirmada: '#00b894', en_espera: '#ffa500',
                en_consulta: '#1e90ff', finalizada: '#555', cancelada: '#ff4444',
                no_asistio: '#dc143c', reprogramada: '#9370db',
            };
            const nombresEstado = {
                pendiente: 'Pendiente', confirmada: 'Confirmada', en_espera: 'En espera',
                en_consulta: 'En consulta', finalizada: 'Finalizada', cancelada: 'Cancelada',
                no_asistio: 'No asistió', reprogramada: 'Reprogramada',
            };
            const orden = ['pendiente', 'confirmada', 'en_espera', 'en_consulta', 'finalizada', 'cancelada', 'no_asistio', 'reprogramada'];

            const citasEstado = data.citasPorEstado || {};
            const totalCitas = Object.values(citasEstado).reduce((a, b) => a + b, 0);
            document.getElementById('stats-total-citas').textContent = totalCitas;
            document.getElementById('stats-finalizadas').textContent = citasEstado.finalizada || 0;
            document.getElementById('stats-canceladas').textContent = (citasEstado.cancelada || 0) + (citasEstado.no_asistio || 0);

            const labelsEstado = [];
            const valuesEstado = [];
            const colorsEstado = [];
            orden.forEach(key => {
                if (citasEstado[key]) {
                    labelsEstado.push(nombresEstado[key] || key);
                    valuesEstado.push(citasEstado[key]);
                    colorsEstado.push(coloresEstado[key] || '#ccc');
                }
            });

            if (labelsEstado.length) {
                new Chart(document.getElementById('chart-estado'), {
                    type: 'doughnut',
                    data: { labels: labelsEstado, datasets: [{ data: valuesEstado, backgroundColor: colorsEstado, borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } } }
                });
            } else {
                document.getElementById('chart-estado').parentElement.innerHTML = '<p class="text-muted text-center py-4 mb-0">Sin datos.</p>';
            }

            const citasMes = data.citasPorMes || {};
            const mesLabels = Object.keys(citasMes);
            const mesValues = Object.values(citasMes);
            if (mesLabels.length) {
                new Chart(document.getElementById('chart-mes'), {
                    type: 'line',
                    data: {
                        labels: mesLabels,
                        datasets: [{
                            label: 'Citas', data: mesValues,
                            borderColor: '#1266f1', backgroundColor: 'rgba(18,102,241,0.1)',
                            fill: true, tension: 0.3, pointBackgroundColor: '#1266f1', pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });
            } else {
                document.getElementById('chart-mes').parentElement.innerHTML = '<p class="text-muted text-center py-4 mb-0">Sin datos.</p>';
            }

            const medData = data.medicamentos || {};
            const medLabels = Object.keys(medData);
            if (medLabels.length) {
                const medValues = Object.values(medData);
                new Chart(document.getElementById('chart-medicamentos'), {
                    type: 'bar',
                    data: {
                        labels: medLabels,
                        datasets: [{ label: 'Veces recetado', data: medValues, backgroundColor: 'rgba(18,102,241,0.7)', borderRadius: 4 }]
                    },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });
            } else {
                document.getElementById('chart-med-title').textContent = 'Sin medicamentos recetados';
                const medContainer = document.getElementById('chart-med-container');
                medContainer.style.height = 'auto';
                medContainer.innerHTML = '<p class="text-muted text-center py-4 mb-0">Aún no hay datos.</p>';
            }

            const diagData = data.diagnosticos || {};
            const diagLabels = Object.keys(diagData);
            if (diagLabels.length) {
                const diagValues = Object.values(diagData);
                new Chart(document.getElementById('chart-diagnosticos'), {
                    type: 'bar',
                    data: {
                        labels: diagLabels,
                        datasets: [{ label: 'Frecuencia', data: diagValues, backgroundColor: 'rgba(0,184,148,0.7)', borderRadius: 4 }]
                    },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });
            } else {
                document.getElementById('chart-diag-title').textContent = 'Sin diagnósticos registrados';
                const diagContainer = document.getElementById('chart-diag-container');
                diagContainer.style.height = 'auto';
                diagContainer.innerHTML = '<p class="text-muted text-center py-4 mb-0">Aún no hay datos.</p>';
            }

            const medVisData = data.medicosVisited || {};
            const medVisLabels = Object.keys(medVisData);
            if (medVisLabels.length) {
                document.getElementById('stats-medicos-row').classList.remove('d-none');
                const medVisValues = Object.values(medVisData);
                new Chart(document.getElementById('chart-medicos'), {
                    type: 'bar',
                    data: {
                        labels: medVisLabels,
                        datasets: [{ label: 'Citas', data: medVisValues, backgroundColor: 'rgba(147,112,219,0.7)', borderRadius: 4 }]
                    },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });
            }
        })
        .catch(err => {
            const errorEl = document.getElementById('stats-error');
            errorEl.textContent = 'No se pudieron cargar las estadísticas: ' + (err.message || err);
            errorEl.classList.remove('d-none');
        });
});
</script>
@endpush
