<?php
/**
 * MoneyFlow - Dashboard Principal
 */

require_once __DIR__ . '/../includes/functions.php';

// Obtener estado financiero completo
$estado = calcularEstadoFinanciero();

if (!$estado) {
    die("Error: No se pudo cargar la configuración del sistema.");
}

// Obtener datos para gráficos
$gastosPorCategoria = obtenerGastosPorCategoria($estado['fecha_inicio'], $estado['fecha_fin']);
$evolucionDiaria = obtenerEvolucionDiaria($estado['fecha_inicio'], $estado['fecha_fin']);
$gastosRecientes = obtenerGastos($estado['fecha_inicio'], $estado['fecha_fin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MoneyFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <h1>💰 MoneyFlow</h1>
            <div class="nav-links">
                <a href="index.php" class="active">Dashboard</a>
                <a href="../forms/add_expense.php">Nuevo Gasto</a>
            </div>
        </nav>

        <!-- Alerta de estado -->
        <?php if ($estado['estado'] === 'ALERTA' || $estado['estado'] === 'ALERTA_AVANZADA'): ?>
            <div class="alert alert-danger">
                <strong>⚠️ ALERTA:</strong> 
                <?php if ($estado['estado'] === 'ALERTA_AVANZADA'): ?>
                    <?php echo $estado['analisis_ritmo']['mensaje']; ?>
                <?php else: ?>
                    Tu saldo está por debajo del mínimo recomendado (<?php echo formatearMoneda(ALERTA_SALDO_MINIMO); ?>)
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- KPIs Principales -->
        <div class="kpi-grid">
            <div class="kpi-card <?php echo $estado['estado'] !== 'OK' ? 'kpi-alert' : ''; ?>">
                <div class="kpi-icon">💵</div>
                <div class="kpi-content">
                    <h3>Saldo Actual</h3>
                    <p class="kpi-value"><?php echo formatearMoneda($estado['saldo_actual']); ?></p>
                    <small>De <?php echo formatearMoneda($estado['saldo_inicial']); ?> inicial</small>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon">💳</div>
                <div class="kpi-content">
                    <h3>Gourmet Disponible</h3>
                    <p class="kpi-value"><?php echo formatearMoneda($estado['gourmet_disponible']); ?></p>
                    <small>De <?php echo formatearMoneda($estado['gourmet_inicial']); ?> inicial</small>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon">📊</div>
                <div class="kpi-content">
                    <h3>Total Gastado</h3>
                    <p class="kpi-value"><?php echo formatearMoneda($estado['gastos_totales']); ?></p>
                    <small>
                        Efectivo: <?php echo formatearMoneda($estado['gastos_efectivo']); ?> | 
                        Gourmet: <?php echo formatearMoneda($estado['gastos_gourmet']); ?>
                    </small>
                </div>
            </div>

            <div class="kpi-card kpi-success">
                <div class="kpi-icon">🎯</div>
                <div class="kpi-content">
                    <h3>Ahorro Proyectado</h3>
                    <p class="kpi-value"><?php echo formatearMoneda($estado['ahorro_actual']); ?></p>
                    <small>
                        Objetivo: <?php echo formatearMoneda($estado['objetivo_ahorro']); ?>
                        (<?php echo $estado['porcentaje_ahorro']; ?>%)
                    </small>
                </div>
            </div>
        </div>

        <!-- Barra de Progreso del Ahorro -->
        <div class="card">
            <h3>Progreso del Ahorro</h3>
            <div class="progress-container">
                <div class="progress-bar" style="width: <?php echo min(100, $estado['porcentaje_ahorro']); ?>%"></div>
            </div>
            <p class="progress-text">
                <?php echo $estado['porcentaje_ahorro']; ?>% del objetivo completado
            </p>
        </div>

        <!-- Análisis de Ritmo de Gasto -->
        <div class="card">
            <h3>📈 Análisis de Ritmo de Gasto</h3>
            <div class="analisis-grid">
                <div class="analisis-item">
                    <strong>Día del Periodo:</strong>
                    <span><?php echo $estado['analisis_ritmo']['dia_actual']; ?> de <?php echo $estado['analisis_ritmo']['dias_totales']; ?></span>
                </div>
                <div class="analisis-item">
                    <strong>Progreso Temporal:</strong>
                    <span><?php echo $estado['analisis_ritmo']['porcentaje_periodo']; ?>%</span>
                </div>
                <div class="analisis-item">
                    <strong>Gasto Esperado:</strong>
                    <span><?php echo formatearMoneda($estado['analisis_ritmo']['gasto_esperado']); ?></span>
                </div>
                <div class="analisis-item">
                    <strong>Gasto Real:</strong>
                    <span><?php echo formatearMoneda($estado['analisis_ritmo']['gasto_real']); ?></span>
                </div>
                <div class="analisis-item <?php echo $estado['analisis_ritmo']['alerta_avanzada'] ? 'text-danger' : 'text-success'; ?>">
                    <strong>Estado:</strong>
                    <span><?php echo $estado['analisis_ritmo']['mensaje']; ?></span>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <div class="card">
                <h3>Gastos por Categoría</h3>
                <canvas id="chartCategoria"></canvas>
            </div>

            <div class="card">
                <h3>Evolución Diaria</h3>
                <canvas id="chartEvolucion"></canvas>
            </div>
        </div>

        <!-- Tabla de Gastos Recientes -->
        <div class="card">
            <h3>Últimos Gastos Registrados</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($gastosRecientes)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay gastos registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($gastosRecientes, 0, 10) as $gasto): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($gasto['fecha'])); ?></td>
                                    <td>
                                        <span class="badge badge-categoria">
                                            <?php echo CATEGORIAS[$gasto['categoria']]; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($gasto['descripcion']); ?></td>
                                    <td><?php echo TIPOS_GASTO[$gasto['tipo']]; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $gasto['metodo']; ?>">
                                            <?php echo METODOS_PAGO[$gasto['metodo']]; ?>
                                        </span>
                                    </td>
                                    <td class="text-right"><?php echo formatearMoneda($gasto['monto']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Datos para gráficos
        const categorias = <?php echo json_encode(array_column($gastosPorCategoria, 'categoria')); ?>;
        const montosCategoria = <?php echo json_encode(array_column($gastosPorCategoria, 'total')); ?>;
        
        const fechasEvolucion = <?php echo json_encode(array_column($evolucionDiaria, 'fecha')); ?>;
        const efectivoEvolucion = <?php echo json_encode(array_column($evolucionDiaria, 'efectivo')); ?>;
        const gourmetEvolucion = <?php echo json_encode(array_column($evolucionDiaria, 'gourmet')); ?>;

        // Gráfico de Categorías (Pie)
        const ctxCategoria = document.getElementById('chartCategoria').getContext('2d');
        new Chart(ctxCategoria, {
            type: 'doughnut',
            data: {
                labels: categorias.map(cat => {
                    const nombres = {
                        'electricidad': 'Electricidad',
                        'transporte': 'Transporte',
                        'supermercado': 'Supermercado',
                        'servicios': 'Servicios',
                        'otros': 'Otros'
                    };
                    return nombres[cat] || cat;
                }),
                datasets: [{
                    data: montosCategoria,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de Evolución (Líneas)
        const ctxEvolucion = document.getElementById('chartEvolucion').getContext('2d');
        new Chart(ctxEvolucion, {
            type: 'line',
            data: {
                labels: fechasEvolucion.map(fecha => {
                    const d = new Date(fecha + 'T00:00:00');
                    return d.getDate() + '/' + (d.getMonth() + 1);
                }),
                datasets: [
                    {
                        label: 'Efectivo',
                        data: efectivoEvolucion,
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Gourmet',
                        data: gourmetEvolucion,
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
