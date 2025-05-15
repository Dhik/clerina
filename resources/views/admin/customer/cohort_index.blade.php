@extends('adminlte::page')
@section('title', trans('labels.customer'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Cohort Analysis</h1>
        <a href="{{ route('customer.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cohort Analysis Dashboard</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h5>Analysis Period: <span id="analysis-period">...</span></h5>
                            <p class="text-muted">Filters: Tenant ID: 1, Sales Channel ID: 1</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" id="retention-tab-btn" class="btn btn-primary active">Retention Rate</button>
                            <button type="button" id="revenue-tab-btn" class="btn btn-outline-primary">Average Order Value</button>
                        </div>
                    </div>

                    <!-- Tables Container with Loading Spinner -->
                    <div class="position-relative">
                        <div id="tables-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        
                        <!-- Retention Rate Table -->
                        <div id="retention-table-container" class="table-responsive" style="display: none;">
                            <table class="table table-bordered">
                                <thead id="retention-table-header">
                                    <tr>
                                        <th style="width: 120px;">Cohort</th>
                                        <th style="width: 100px;">Customers</th>
                                        <!-- Month headers will be dynamically inserted here -->
                                    </tr>
                                </thead>
                                <tbody id="retention-table-body">
                                    <!-- Table rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Revenue Table -->
                        <div id="revenue-table-container" class="table-responsive" style="display: none;">
                            <table class="table table-bordered">
                                <thead id="revenue-table-header">
                                    <tr>
                                        <th style="width: 120px;">Cohort</th>
                                        <th style="width: 100px;">Customers</th>
                                        <!-- Month headers will be dynamically inserted here -->
                                    </tr>
                                </thead>
                                <tbody id="revenue-table-body">
                                    <!-- Table rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Retention Over Time</h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div id="retention-chart-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div style="height: 250px;">
                            <canvas id="retentionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cohort Size Distribution</h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div id="cohort-size-chart-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="cohortSizeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Average Order Value by Cohort</h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div id="aov-chart-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="aovChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .retention-cell, .revenue-cell {
        text-align: center;
    }
    
    .retention-cell {
        font-weight: bold;
        color: white;
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.7);
    }
    
    .revenue-cell {
        font-weight: bold;
    }

    .month-0 {
        background-color: #28a745 !important;
        color: white;
    }
    
    .loading-spinner-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 5;
    }
    
    /* Fixed first two columns for horizontal scrolling */
    .sticky-col {
        position: sticky;
        background-color: white;
        z-index: 1;
    }
    .first-col {
        left: 0;
    }
    .second-col {
        left: 120px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch cohort data from API
        fetch('{{ route("net-profit.cohort-data") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Update analysis period
                document.getElementById('analysis-period').textContent = 
                    `${data.analysis_period.start_date} to ${data.analysis_period.end_date}`;

                // Determine the maximum number of months to display
                const maxMonths = determineMaxMonths(data.cohort_data);
                
                // Create table headers for all months
                createTableHeaders(maxMonths);
                
                // Render cohort tables
                renderCohortTables(data.cohort_data, maxMonths);
                
                // Render charts (each with its own loading state)
                renderRetentionChart(data.cohort_data, maxMonths);
                renderCohortSizeChart(data.cohort_data);
                renderAOVChart(data.cohort_data);
                
                // Set up tab switching
                setupTabSwitching();
            })
            .catch(error => {
                console.error('Error fetching cohort data:', error);
                
                // Hide all loading indicators
                document.querySelectorAll('.loading-spinner-container').forEach(loader => {
                    loader.style.display = 'none';
                });
                
                // Display error notification using AdminLTE's toast
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Failed to load cohort data. Please try again later.',
                    autohide: true,
                    delay: 5000,
                    class: 'bg-danger'
                });
            });
    });
    
    // Function to determine the maximum number of months across all cohorts
    function determineMaxMonths(cohortData) {
        let maxMonths = 0;
        
        // Find the maximum month index across all cohorts
        Object.values(cohortData).forEach(cohort => {
            const monthIndices = Object.keys(cohort.months).map(Number);
            if (monthIndices.length > 0) {
                const cohortMax = Math.max(...monthIndices);
                maxMonths = Math.max(maxMonths, cohortMax);
            }
        });
        
        // Add 1 because we're 0-indexed (months 0-11 = 12 months)
        return maxMonths + 1;
    }
    
    // Function to create table headers for all months
    function createTableHeaders(maxMonths) {
        const retentionHeader = document.querySelector('#retention-table-header tr');
        const revenueHeader = document.querySelector('#revenue-table-header tr');
        
        // Clear existing month headers (after cohort and customers columns)
        while (retentionHeader.children.length > 2) {
            retentionHeader.removeChild(retentionHeader.lastChild);
        }
        
        while (revenueHeader.children.length > 2) {
            revenueHeader.removeChild(revenueHeader.lastChild);
        }
        
        // Add headers for each month
        for (let i = 0; i < maxMonths; i++) {
            // Retention header
            const retentionTh = document.createElement('th');
            retentionTh.style.width = '90px';
            retentionTh.textContent = `Month ${i}`;
            retentionHeader.appendChild(retentionTh);
            
            // Revenue header
            const revenueTh = document.createElement('th');
            revenueTh.style.width = '90px';
            revenueTh.textContent = `Month ${i}`;
            revenueHeader.appendChild(revenueTh);
        }
        
        // Make the first two columns sticky for better horizontal scrolling
        retentionHeader.children[0].className = 'sticky-col first-col';
        retentionHeader.children[1].className = 'sticky-col second-col';
        
        revenueHeader.children[0].className = 'sticky-col first-col';
        revenueHeader.children[1].className = 'sticky-col second-col';
    }

    function renderCohortTables(cohortData, maxMonths) {
        const tablesLoading = document.getElementById('tables-loading');
        const retentionTableBody = document.getElementById('retention-table-body');
        const revenueTableBody = document.getElementById('revenue-table-body');
        const retentionTableContainer = document.getElementById('retention-table-container');
        const revenueTableContainer = document.getElementById('revenue-table-container');
        
        // Clear existing content
        retentionTableBody.innerHTML = '';
        revenueTableBody.innerHTML = '';
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        sortedCohorts.forEach(cohortMonth => {
            const cohort = cohortData[cohortMonth];
            
            // Create retention rate row
            const retentionRow = document.createElement('tr');
            
            // Add cohort month and size
            retentionRow.innerHTML = `
                <td class="sticky-col first-col">${formatMonthYear(cohortMonth)}</td>
                <td class="sticky-col second-col">${formatNumber(cohort.total_customers)}</td>
            `;
            
            // Create revenue row
            const revenueRow = document.createElement('tr');
            
            // Add cohort month and size
            revenueRow.innerHTML = `
                <td class="sticky-col first-col">${formatMonthYear(cohortMonth)}</td>
                <td class="sticky-col second-col">${formatNumber(cohort.total_customers)}</td>
            `;
            
            // Add cells for each month (0 to maxMonths-1)
            for (let i = 0; i < maxMonths; i++) {
                const monthData = cohort.months[i];
                
                // Retention cell
                const retentionCell = document.createElement('td');
                retentionCell.className = 'retention-cell';
                
                if (monthData) {
                    const retentionRate = monthData.retention_rate;
                    retentionCell.textContent = `${retentionRate}%`;
                    
                    // Special color for month 0 (always 100%)
                    if (i === 0) {
                        retentionCell.classList.add('month-0');
                    } else {
                        // Color based on retention rate
                        retentionCell.style.backgroundColor = `rgba(0, 123, 255, ${retentionRate / 100})`;
                    }
                } else {
                    retentionCell.textContent = '-';
                    retentionCell.style.backgroundColor = '#f8f9fa';
                    retentionCell.style.color = '#6c757d';
                }
                
                retentionRow.appendChild(retentionCell);
                
                // Revenue cell
                const revenueCell = document.createElement('td');
                revenueCell.className = 'revenue-cell';
                
                if (monthData) {
                    revenueCell.textContent = formatCurrency(monthData.average_order_value);
                    
                    // Special color for month 0
                    if (i === 0) {
                        revenueCell.classList.add('month-0');
                    } else {
                        // Color based on AOV (assuming max AOV around 200,000)
                        const maxAOV = 200000;
                        const intensity = Math.min(1, monthData.average_order_value / maxAOV);
                        revenueCell.style.backgroundColor = `rgba(40, 167, 69, ${intensity})`;
                        if (intensity > 0.5) {
                            revenueCell.style.color = 'white';
                            revenueCell.style.textShadow = '0 0 2px rgba(0, 0, 0, 0.7)';
                        }
                    }
                } else {
                    revenueCell.textContent = '-';
                    revenueCell.style.backgroundColor = '#f8f9fa';
                    revenueCell.style.color = '#6c757d';
                }
                
                revenueRow.appendChild(revenueCell);
            }
            
            retentionTableBody.appendChild(retentionRow);
            revenueTableBody.appendChild(revenueRow);
        });
        
        // Hide loading indicator and show tables
        tablesLoading.style.display = 'none';
        retentionTableContainer.style.display = 'block';
    }

    function renderRetentionChart(cohortData, maxMonths) {
        const loadingSpinner = document.getElementById('retention-chart-loading');
        const ctx = document.getElementById('retentionChart').getContext('2d');
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        // Create labels for all months
        const labels = [];
        for (let i = 0; i < maxMonths; i++) {
            labels.push(`Month ${i}`);
        }
        
        // Prepare datasets
        const datasets = sortedCohorts.map((cohortMonth, index) => {
            const cohort = cohortData[cohortMonth];
            const data = [];
            
            // Get retention rates for each period
            for (let i = 0; i < maxMonths; i++) {
                if (cohort.months[i]) {
                    data.push(cohort.months[i].retention_rate);
                } else {
                    data.push(null);
                }
            }
            
            // Generate a color based on index
            const colors = [
                'rgba(255, 99, 132, 1)',   // Red
                'rgba(54, 162, 235, 1)',   // Blue
                'rgba(255, 206, 86, 1)',   // Yellow
                'rgba(75, 192, 192, 1)',   // Teal
                'rgba(153, 102, 255, 1)',  // Purple
                'rgba(255, 159, 64, 1)',   // Orange
                'rgba(201, 203, 207, 1)',  // Grey
                'rgba(0, 204, 150, 1)',    // Seafoam
                'rgba(215, 119, 0, 1)',    // Orange-brown
                'rgba(118, 17, 195, 1)',   // Purple
                'rgba(0, 139, 139, 1)',    // Teal-dark
                'rgba(205, 92, 92, 1)'     // Indian red
            ];
            
            return {
                label: `Cohort ${formatMonthYear(cohortMonth)}`,
                data: data,
                borderColor: colors[index % colors.length],
                backgroundColor: 'transparent',
                tension: 0.1
            };
        });
        
        // Create the chart
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Retention Rate (%)'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });
        
        // Hide loading spinner once chart is rendered
        loadingSpinner.style.display = 'none';
    }

    function renderCohortSizeChart(cohortData) {
        const loadingSpinner = document.getElementById('cohort-size-chart-loading');
        const ctx = document.getElementById('cohortSizeChart').getContext('2d');
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        // Prepare data
        const labels = sortedCohorts.map(cohortMonth => formatMonthYear(cohortMonth));
        const data = sortedCohorts.map(cohortMonth => cohortData[cohortMonth].total_customers);
        
        // Create the chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Customers',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Customer Count'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Hide loading spinner once chart is rendered
        loadingSpinner.style.display = 'none';
    }

    function renderAOVChart(cohortData) {
        const loadingSpinner = document.getElementById('aov-chart-loading');
        const ctx = document.getElementById('aovChart').getContext('2d');
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        // Prepare data
        const labels = sortedCohorts.map(cohortMonth => formatMonthYear(cohortMonth));
        const data = sortedCohorts.map(cohortMonth => {
            // Use month 0 average order value
            return cohortData[cohortMonth].months[0] ? cohortData[cohortMonth].months[0].average_order_value : 0;
        });
        
        // Create the chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Order Value',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Average Order Value (IDR)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrencyShort(value);
                            }
                        }
                    }
                },
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Average Order Value: ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
        
        // Hide loading spinner once chart is rendered
        loadingSpinner.style.display = 'none';
    }

    function setupTabSwitching() {
        const retentionTabBtn = document.getElementById('retention-tab-btn');
        const revenueTabBtn = document.getElementById('revenue-tab-btn');
        const retentionTableContainer = document.getElementById('retention-table-container');
        const revenueTableContainer = document.getElementById('revenue-table-container');
        
        retentionTabBtn.addEventListener('click', function() {
            retentionTabBtn.classList.add('btn-primary');
            retentionTabBtn.classList.remove('btn-outline-primary');
            revenueTabBtn.classList.remove('btn-primary');
            revenueTabBtn.classList.add('btn-outline-primary');
            
            retentionTableContainer.style.display = 'block';
            revenueTableContainer.style.display = 'none';
        });
        
        revenueTabBtn.addEventListener('click', function() {
            revenueTabBtn.classList.add('btn-primary');
            revenueTabBtn.classList.remove('btn-outline-primary');
            retentionTabBtn.classList.remove('btn-primary');
            retentionTabBtn.classList.add('btn-outline-primary');
            
            revenueTableContainer.style.display = 'block';
            retentionTableContainer.style.display = 'none';
        });
    }

    // Helper functions
    function formatMonthYear(dateString) {
        const [year, month] = dateString.split('-');
        const date = new Date(year, month - 1);
        return date.toLocaleString('en-US', { month: 'short', year: 'numeric' });
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    function formatCurrencyShort(value) {
        if (value >= 1000000) {
            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return 'Rp ' + (value / 1000).toFixed(1) + 'K';
        } else {
            return 'Rp ' + value;
        }
    }
</script>
@stop