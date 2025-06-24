<script>
/**
 * Common functions and utilities for Ads CPAS Monitor
 */

// Global variables and utility functions
let dateRangePickers = {};

/**
 * Initialize a date range picker
 * @param {string} elementId - The ID of the input element
 * @returns {object} - The daterangepicker object
 */
function initDateRangePicker(elementId) {
    const element = $('#' + elementId);
    
    // Initialize daterangepicker
    element.daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        alwaysShowCalendars: true,
        opens: 'right',
        locale: {
            cancelLabel: 'Clear',
            format: 'DD/MM/YYYY'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    $(document).ready(function() {
        // Check if there's a stored active tab
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            // Activate the stored tab
            $('#' + activeTab).tab('show');
            // Clear the storage to prevent unexpected tab switching on future page loads
            localStorage.removeItem('activeTab');
        }
    });

    // Apply and Cancel event handlers for daterangepicker
    element.on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $(this).trigger('change');
    });

    element.on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $(this).trigger('change');
    });

    // Store reference for later use
    dateRangePickers[elementId] = element;
    
    return element;
}

/**
 * Format numbers for display
 * @param {number} value - The number to format
 * @param {number} decimals - The number of decimal places (default: 0)
 * @returns {string} - The formatted number
 */
function numberFormat(value, decimals = 0) {
    if (value === null || value === undefined) return '-';
    return Number(value).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

/**
 * Show loading overlay using SweetAlert
 * @param {string} message - The message to display
 */
function showLoadingSwal(message) {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Handle file input change for better UX
 * @param {string} inputId - The ID of the file input
 */
function handleFileInputChange(inputId) {
    $('#' + inputId).on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Choose file');
        
        if (fileName && fileName.toLowerCase().endsWith('.zip')) {
            $('<div class="alert alert-info mt-2">ZIP file detected. All CSV files in the archive will be processed.</div>')
                .insertAfter($(this).closest('.custom-file'));
        }
    });
}

/**
 * Create a line chart using Chart.js
 * @param {string} ctxId - The canvas element ID
 * @param {string} label - The dataset label
 * @param {Array} dates - Array of dates for x-axis
 * @param {Array} data - Array of values for y-axis
 * @param {string} color - The color for the chart (optional)
 * @returns {object} - The Chart.js instance
 */
function createLineChart(ctxId, label, dates, data, color = 'rgba(54, 162, 235, 1)') {
    const ctx = document.getElementById(ctxId).getContext('2d');
    
    // Destroy existing chart if it exists
    if (window[ctxId + 'Chart'] && typeof window[ctxId + 'Chart'].destroy === 'function') {
        window[ctxId + 'Chart'].destroy();
    }
    
    // Create new chart
    window[ctxId + 'Chart'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: color.replace('1)', '0.5)'),
                borderColor: color,
                borderWidth: 1
            }]
        },
        options: {
            tooltips: {
                enabled: true,
                callbacks: {
                    label: function(tooltipItem, data) {
                        let label = data.datasets[tooltipItem.datasetIndex].label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        return label;
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            if (parseInt(value) >= 1000) {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            } else {
                                return value;
                            }
                        }
                    }
                }]
            }
        }
    });
    
    return window[ctxId + 'Chart'];
}

/**
 * Create a funnel chart using ApexCharts
 * @param {string} elementId - The element ID to render the chart
 * @param {Array} data - Array of data objects with name and value properties
 * @param {string} metricsElementId - The element ID to display metrics
 * @returns {object} - The ApexCharts instance
 */
function createFunnelChart(elementId, data, metricsElementId, result) {
    // Destroy existing chart if it exists
    if (window[elementId + 'Chart']) {
        window[elementId + 'Chart'].destroy();
    }
    
    const options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                distributed: true,
                dataLabels: {
                    position: 'bottom'
                },
            }
        },
        colors: ['#60A5FA', '#3B82F6', '#34D399', '#2563EB', '#1D4ED8'],
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val.toLocaleString();
            },
            style: {
                fontSize: '12px',
            }
        },
        xaxis: {
            categories: data.map(item => item.name),
            labels: {
                show: true,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                show: true,
                style: {
                    fontSize: '12px'
                }
            }
        },
        grid: {
            yaxis: {
                lines: {
                    show: false
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toLocaleString();
                }
            }
        }
    };

    const series = [{
        name: 'Total',
        data: data.map(item => item.value)
    }];

    // Create new ApexCharts instance
    window[elementId + 'Chart'] = new ApexCharts(document.querySelector("#" + elementId), {
        ...options,
        series: series
    });
    window[elementId + 'Chart'].render();

    // Update metrics display
    if (metricsElementId) {
        // Generate the standard metrics HTML
        const standardMetricsHtml = data.map((item, index) => `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>${item.name}</span>
                <span class="font-weight-bold">
                    ${item.value.toLocaleString()}
                    ${index > 0 ? `
                        <span class="text-muted ml-2">
                            (${((item.value / data[0].value) * 100).toFixed(2)}%)
                        </span>
                    ` : ''}
                </span>
            </div>
        `).join('');
        
        // Add the min spend and max ATC information if available
        let additionalInsightsHtml = '';
        if (result && result.min_spent && result.max_atc) {
            additionalInsightsHtml = `
                <div class="mt-4">
                    <h6 class="font-weight-bold">Interesting Insights</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-left-success shadow py-2 mb-3">
                                <div class="card-body p-3">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Date with Min Spent
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        ${result.min_spent.date || 'N/A'}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-left-info shadow py-2 mb-3">
                                <div class="card-body p-3">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Date with Max ATC
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        ${result.max_atc.date || 'N/A'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Combine the standard metrics and additional insights
        document.querySelector('#' + metricsElementId).innerHTML = standardMetricsHtml + additionalInsightsHtml;
    }
    
    return window[elementId + 'Chart'];
}

/**
 * Update campaign summary data
 * @param {object} params - Filter parameters
 * @param {string} dateElementId - The date filter element ID
 * @param {string} categoryElementId - The category filter element ID
 * @param {string} picElementId - The PIC filter element ID
 */
function updateCampaignSummary(params = {}, dateElementId = 'modalFilterDates', categoryElementId = 'kategoriProdukFilter', picElementId = 'picFilter') {
    // Build parameters object if not provided
    if (Object.keys(params).length === 0) {
        // Check if we have a date range filter in the modal
        if ($('#' + dateElementId).val()) {
            let dates = $('#' + dateElementId).val().split(' - ');
            params.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
            params.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
        } else if ($('#dailyDetailsModal').data('date')) {
            // If no date range is selected, use the single date
            params.date = $('#dailyDetailsModal').data('date');
        }
        
        // Add other filters
        if ($('#' + picElementId).val()) {
            params.pic = $('#' + picElementId).val();
        }
        
        if ($('#' + categoryElementId).val()) {
            params.kategori_produk = $('#' + categoryElementId).val();
        }
    }
    
    // Show loading state in summary cards
    $('#campaignSummary .card h4').text('Loading...');
    
    // Fetch summary data
    $.ajax({
        url: "{{ route('adSpentSocialMedia.get_campaign_summary') }}",
        type: 'GET',
        data: params,
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Update summary cards with formatted values
                $('#summaryAccountsCount').text(data.accounts_count);
                $('#summaryTotalSpent').text('Rp ' + numberFormat(data.total_amount_spent));
                $('#summaryTotalPurchases').text(numberFormat(data.total_purchases, 2));
                $('#summaryConversionValue').text('Rp ' + numberFormat(data.total_conversion_value));
                $('#summaryRoas').text(numberFormat(data.roas, 2));
                $('#summaryCostPerPurchase').text('Rp ' + numberFormat(data.cost_per_purchase));
                $('#summaryImpressions').text(numberFormat(data.total_impressions));
                $('#summaryCtr').text(numberFormat(data.ctr, 2) + '%');
                
                // Update new funnel stage metrics
                $('#summaryTofuSpent').text('Rp ' + numberFormat(data.tofu_spent));
                $('#summaryMofuSpent').text('Rp ' + numberFormat(data.mofu_spent));
                $('#summaryBofuSpent').text('Rp ' + numberFormat(data.bofu_spent));
                $('#summaryShopeeSpent').text('Rp ' + numberFormat(data.shopee_spent)); // Add this line

                // Update percentage badges
                $('#summaryTofuPercentage').text(numberFormat(data.tofu_percentage, 2) + '%');
                $('#summaryMofuPercentage').text(numberFormat(data.mofu_percentage, 2) + '%');
                $('#summaryBofuPercentage').text(numberFormat(data.bofu_percentage, 2) + '%');
                $('#summaryShopeePercentage').text(numberFormat(data.shopee_percentage, 2) + '%'); // Add this line
                
                // Add color coding for ROAS based on performance thresholds
                const roasElement = $('#summaryRoas');
                roasElement.removeClass('text-success text-primary text-info text-danger');
                if (data.roas >= 2.5) {
                    roasElement.addClass('text-success');
                } else if (data.roas >= 2.01) {
                    roasElement.addClass('text-primary');
                } else if (data.roas >= 1.75) {
                    roasElement.addClass('text-info');
                } else if (data.roas > 0) {
                    roasElement.addClass('text-danger');
                }
            } else {
                // Handle error response
                console.error('Error fetching summary data');
                $('#campaignSummary .card h4').text('-');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            $('#campaignSummary .card h4').text('-');
        }
    });
}
</script>