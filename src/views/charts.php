<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Recovery Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</head>
<body>
    <div class="container-fluid mt-4">
        <h2 class="mb-4">LMS Recovery Analytics Dashboard</h2>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label>Date Range:</label>
                <input type="text" id="daterange" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Branch:</label>
                <select id="branch" class="form-control">
                    <option value="">All Branches</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Recovery Type:</label>
                <select id="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="1">Normal Recovery</option>
                    <option value="2">Advance Recovery</option>
                    <option value="3">OD Recovery</option>
                    <option value="4">Arrear Recovery</option>
                    <option value="5">Close Loans</option>
                    <option value="6">Death Recovery</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>OD Status:</label>
                <select id="isOD" class="form-control">
                    <option value="">All</option>
                    <option value="0">Normal</option>
                    <option value="1">OD Recovered</option>
                    <option value="2">OD Unrecovered</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Period:</label>
                <select id="period" class="form-control">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
        </div>

        <!-- Export and Email Buttons -->
        <div class="row mb-4">
            <div class="col-md-12">
                <button id="exportBtn" class="btn btn-success">Export to Excel</button>
                <button id="emailBtn" class="btn btn-primary">Send Report via Email</button>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <canvas id="recoveryChart"></canvas>
            </div>
            <div class="col-md-6 mb-4">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recovery Summary</h5>
                        <div id="summaryStats" class="row mt-3">
                            <!-- Will be filled by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Table -->
        <div class="row">
            <div class="col-md-12">
                <table id="summaryTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Branch</th>
                            <th>Normal Recovery</th>
                            <th>Advance Recovery</th>
                            <th>OS Recovery</th>
                            <th>Arrear Recovery</th>
                            <th>Close Loans</th>
                            <th>Death Recovery</th>
                            <th>Normal Amount</th>
                            <th>OD Recovered</th>
                            <th>OD Unrecovered</th>
                            <th>Total Principal</th>
                            <th>Total Interest</th>
                            <th>Total Amount</th>
                            <th>Transactions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Report via Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="emailInput" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendEmailBtn">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize date range picker with more options
            $('#daterange').daterangepicker({
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                    'Last 2 Years': [moment().subtract(2, 'year').startOf('year'), moment()],
                    'Last 5 Years': [moment().subtract(5, 'year').startOf('year'), moment()]
                },
                locale: {
                    format: 'DD-MM-YYYY'
                }
            });

            let recoveryChart, trendChart;

            function updateCharts() {
                const dateRange = $('#daterange').data('daterangepicker');
                $.get('index.php', {
                    controller: 'chart',
                    action: 'getData',
                    start_date: dateRange.startDate.format('YYYY-MM-DD'),
                    end_date: dateRange.endDate.format('YYYY-MM-DD'),
                    branch: $('#branch').val() || null,
                    type: $('#type').val() || null,
                    isOD: $('#isOD').val() || null,
                    period: $('#period').val()
                }, function(data) {
                    updateChartsWithData(data);
                    updateTable(data);
                });
            }

            function updateChartsWithData(data) {
                const period = $('#period').val();
                const labels = data.map(d => {
                    switch(period) {
                        case 'yearly':
                            return d.period; // Already in YYYY format from SQL
                        case 'monthly':
                            // Convert YYYY-MM to MMM YYYY
                            return moment(d.period + '-01').format('MMM YYYY');
                        case 'weekly':
                            // Convert YYYYWW to date range
                            const weekYear = d.period.toString();
                            const year = weekYear.substring(0, 4);
                            const week = weekYear.substring(4);
                            const startOfWeek = moment().year(year).week(week).startOf('week');
                            const endOfWeek = moment().year(year).week(week).endOf('week');
                            return `${startOfWeek.format('DD MMM')} - ${endOfWeek.format('DD MMM YYYY')}`;
                        default:
                            // Daily - convert YYYY-MM-DD to DD MMM YYYY
                            return moment(d.period).format('DD MMM YYYY');
                    }
                });

                // Calculate totals for summary
                const totals = {
                    normal: data.reduce((sum, d) => sum + (parseFloat(d.normal_recovery) || 0), 0),
                    advance: data.reduce((sum, d) => sum + (parseFloat(d.advance_recovery) || 0), 0),
                    os: data.reduce((sum, d) => sum + (parseFloat(d.os_recovery) || 0), 0),
                    arrear: data.reduce((sum, d) => sum + (parseFloat(d.arrear_recovery) || 0), 0),
                    close: data.reduce((sum, d) => sum + (parseFloat(d.close_loans) || 0), 0),
                    death: data.reduce((sum, d) => sum + (parseFloat(d.death_recovery) || 0), 0)
                };

                const totalAmount = Object.values(totals).reduce((a, b) => a + b, 0);
                const totalTransactions = data.reduce((sum, d) => sum + parseInt(d.total_transactions || 0), 0);

                // Update summary stats
                $('#summaryStats').html(`
                    <div class="col-4 mb-3">
                        <strong>Total Recovery:</strong><br>
                        ${formatAmount(totalAmount)}
                    </div>
                    <div class="col-4 mb-3">
                        <strong>Total Transactions:</strong><br>
                        ${totalTransactions}
                    </div>
                    <div class="col-4 mb-3">
                        <strong>OD Recovered:</strong><br>
                        ${formatAmount(data.reduce((sum, d) => sum + (parseFloat(d.od_recovered) || 0), 0))}
                    </div>
                    ${Object.entries(totals).map(([key, value]) => `
                        <div class="col-4 mb-2">
                            <strong>${key.charAt(0).toUpperCase() + key.slice(1)}:</strong><br>
                            ${formatAmount(value)} (${((value/totalAmount)*100).toFixed(1)}%)
                        </div>
                    `).join('')}
                `);

                const chartColors = {
                    normal: '#4CAF50',
                    advance: '#2196F3',
                    os: '#FFC107',
                    arrear: '#FF5722',
                    close: '#9C27B0',
                    death: '#795548'
                };

                const datasets = [
                    {
                        label: 'Normal Recovery',
                        data: data.map(d => parseFloat(d.normal_recovery) || 0),
                        backgroundColor: chartColors.normal,
                        borderColor: chartColors.normal,
                        borderWidth: 1
                    },
                    {
                        label: 'Advance Recovery',
                        data: data.map(d => parseFloat(d.advance_recovery) || 0),
                        backgroundColor: chartColors.advance,
                        borderColor: chartColors.advance,
                        borderWidth: 1
                    },
                    {
                        label: 'OS Recovery',
                        data: data.map(d => parseFloat(d.os_recovery) || 0),
                        backgroundColor: chartColors.os,
                        borderColor: chartColors.os,
                        borderWidth: 1
                    },
                    {
                        label: 'Arrear Recovery',
                        data: data.map(d => parseFloat(d.arrear_recovery) || 0),
                        backgroundColor: chartColors.arrear,
                        borderColor: chartColors.arrear,
                        borderWidth: 1
                    },
                    {
                        label: 'Close Loans',
                        data: data.map(d => parseFloat(d.close_loans) || 0),
                        backgroundColor: chartColors.close,
                        borderColor: chartColors.close,
                        borderWidth: 1
                    },
                    {
                        label: 'Death Recovery',
                        data: data.map(d => parseFloat(d.death_recovery) || 0),
                        backgroundColor: chartColors.death,
                        borderColor: chartColors.death,
                        borderWidth: 1
                    }
                ];

                // Destroy existing charts
                if (recoveryChart) recoveryChart.destroy();
                if (trendChart) trendChart.destroy();

                // Bar Chart
                recoveryChart = new Chart('recoveryChart', {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Recovery Distribution',
                                font: { size: 16, weight: 'bold' }
                            },
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 20 }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    callback: value => formatAmount(value)
                                }
                            }
                        }
                    }
                });

                // Line Chart
                trendChart = new Chart('trendChart', {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets.map(d => ({
                            ...d,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: false
                        }))
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Recovery Trends',
                                font: { size: 16, weight: 'bold' }
                            },
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 20 }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => formatAmount(value)
                                }
                            }
                        }
                    }
                });
            }

            function updateTable(data) {
                const tbody = $('#summaryTable tbody');
                tbody.empty();
                data.forEach(row => {
                    tbody.append(`
                        <tr>
                            <td>${row.period}</td>
                            <td>${row.branch_id}</td>
                            <td>${formatAmount(row.normal_recovery)}</td>
                            <td>${formatAmount(row.advance_recovery)}</td>
                            <td>${formatAmount(row.os_recovery)}</td>
                            <td>${formatAmount(row.arrear_recovery)}</td>
                            <td>${formatAmount(row.close_loans)}</td>
                            <td>${formatAmount(row.death_recovery)}</td>
                            <td>${formatAmount(row.normal_amount)}</td>
                            <td>${formatAmount(row.od_recovered)}</td>
                            <td>${formatAmount(row.od_unrecovered)}</td>
                            <td>${formatAmount(row.total_principal)}</td>
                            <td>${formatAmount(row.total_interest)}</td>
                            <td>${formatAmount(row.total_amount)}</td>
                            <td>${row.total_transactions}</td>
                        </tr>
                    `);
                });
            }

            function formatAmount(amount) {
                return new Intl.NumberFormat('en-IN', {
                    style: 'currency',
                    currency: 'INR'
                }).format(amount);
            }

            // Event listeners
            $('#daterange, #branch, #type, #isOD, #period').on('change', updateCharts);
            
            $('#exportBtn').click(function() {
                const dateRange = $('#daterange').data('daterangepicker');
                window.location.href = `index.php?controller=export&start_date=${dateRange.startDate.format('YYYY-MM-DD')}&end_date=${dateRange.endDate.format('YYYY-MM-DD')}&branch=${$('#branch').val()}&type=${$('#type').val()}&isOD=${$('#isOD').val()}`;
            });

            $('#emailBtn').click(function() {
                new bootstrap.Modal('#emailModal').show();
            });

            $('#sendEmailBtn').click(function() {
                const email = $('#emailInput').val();
                if (!email) {
                    alert('Please enter an email address');
                    return;
                }

                const dateRange = $('#daterange').data('daterangepicker');
                $.post('index.php', {
                    controller: 'email',
                    email: email,
                    start_date: dateRange.startDate.format('YYYY-MM-DD'),
                    end_date: dateRange.endDate.format('YYYY-MM-DD'),
                    branch: $('#branch').val(),
                    type: $('#type').val(),
                    isOD: $('#isOD').val()
                }, function(response) {
                    alert('Report sent successfully!');
                    bootstrap.Modal.getInstance('#emailModal').hide();
                }).fail(function() {
                    alert('Failed to send email. Please try again.');
                });
            });

            // Initial load
            updateCharts();
        });
    </script>
</body>
</html>
