<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório Anual de Faturamento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 210mm;
            /* A4 width */
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #174111;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #174111;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .total-row {
            font-weight: bold;
            background-color: #e8f5e9 !important;
        }

        @media print {
            body {
                padding: 0;
            }

            .container {
                width: 100%;
                max-width: none;
            }

            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Relatório Anual de Faturamento - {{ $ano }}</h1>

        <!-- Canvas for Chart -->
        <div style="width: 100%; height: 400px; margin-bottom: 30px;">
            <canvas id="faturamentoChart"></canvas>
        </div>

        <div style="background-color: #f8f9fa; border-left: 5px solid #174111; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #174111;">Análise do Período</h3>
            <p style="margin-bottom: 0; color: #333; line-height: 1.5;">{{ $analiseTexto }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 15%">Mês Nº</th>
                    <th>Mês</th>
                    <th style="text-align: right">Faturamento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($faturamentoMensal as $registro)
                <tr>
                    <td>{{ $registro->mes_numero }}</td>
                    <td>{{ $registro->mes_nome }}</td>
                    <td style="text-align: right">{{ $registro->faturamento }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: right; font-size: 0.9em; color: #666;">
            <p>Gerado em {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" data-cfasync="false"></script>

    <script data-cfasync="false">
        document.addEventListener('DOMContentLoaded', function() {

            const chartLabels = @json($chartLabels ?? []);
            const chartDataFaturamento = @json($chartDataFaturamento ?? []);
            const chartDataDiferenca = @json($chartDataDiferenca ?? []);


            const diffColors = (chartDataDiferenca.length > 0) ?
                chartDataDiferenca.map(val => val >= 0 ? '#4CAF50' : '#F44336') : [];

            const ctx = document.getElementById('faturamentoChart').getContext('2d');


            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                            label: 'Faturamento Mensal (R$)',
                            data: chartDataFaturamento,
                            backgroundColor: '#174111',
                            borderColor: '#174111',
                            borderWidth: 1
                        },
                        {
                            label: 'Variação vs Mês Anterior (R$)',
                            data: chartDataDiferenca,
                            backgroundColor: diffColors,
                            borderColor: diffColors,
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        onComplete: function() {
                            setTimeout(function() {
                                window.print();
                            }, 500);
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('pt-BR', {
                                            style: 'currency',
                                            currency: 'BRL'
                                        }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>