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
                const labels = data.map(d => d.period);
                const datasets = [
                    { label: 'Normal Recovery', data: data.map(d => parseFloat(d.normal_recovery) || 0), borderColor: '#4CAF50' },
                    { label: 'Advance Recovery', data: data.map(d => parseFloat(d.advance_recovery) || 0), borderColor: '#2196F3' },
                    { label: 'OS Recovery', data: data.map(d => parseFloat(d.os_recovery) || 0), borderColor: '#FFC107' },
                    { label: 'Arrear Recovery', data: data.map(d => parseFloat(d.arrear_recovery) || 0), borderColor: '#FF5722' },
                    { label: 'Close Loans', data: data.map(d => parseFloat(d.close_loans) || 0), borderColor: '#9C27B0' },
                    { label: 'Death Recovery', data: data.map(d => parseFloat(d.death_recovery) || 0), borderColor: '#795548' }
                ];

                // Destroy existing charts if they exist
                if (recoveryChart) recoveryChart.destroy();
                if (trendChart) trendChart.destroy();

                // Bar Chart
                recoveryChart = new Chart('recoveryChart', {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets.map(d => ({...d, type: 'bar'}))
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Recovery Distribution'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('en-IN', {
                                            style: 'currency',
                                            currency: 'INR',
                                            maximumSignificantDigits: 3
                                        }).format(value);
                                    }
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
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Recovery Trends'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('en-IN', {
                                            style: 'currency',
                                            currency: 'INR',
                                            maximumSignificantDigits: 3
                                        }).format(value);
                                    }
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
