<script>
/**
 * Shopee Ads tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let filterDate = initDateRangePicker('shopeeFilterDates');
    let filterKodeProduk = $('#shopeeKodeProdukFilter');
    let modalFilterDate = initDateRangePicker('shopeeModalFilterDates');
    let modalKodeProdukFilter = $('#shopeeModalKodeProdukFilter');
    let funnelChart = null;
    let impressionChart = null;
    let filterBiddingMode = $('#shopeeBiddingModeFilter');
    let modalBiddingModeFilter = $('#shopeeModalBiddingModeFilter');

    // Initialize Shopee Ads DataTable
    let adsShopeeTable = $('#adsShopeeTable').DataTable({
    responsive: true,
    processing: true,
    serverSide: true,
    pageLength: 10,
    ajax: {
        url: "{{ route('adSpentSocialMedia.get_ads_shopee') }}",
        data: function (d) {
            if (filterDate.val()) {
                let dates = filterDate.val().split(' - ');
                d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
            }
            if (filterKodeProduk.val()) {
                d.kode_produk = filterKodeProduk.val();
            }
            // Add the bidding mode filter
            if (filterBiddingMode.val()) {
                d.mode_bidding = filterBiddingMode.val();
            }
        }
    },
    columns: [
        {data: 'date', name: 'date'},
        {
            data: 'total_dilihat',
            name: 'total_dilihat',
            render: function(data) {
                return Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        },
        {
            data: 'total_jumlah_klik', 
            name: 'total_jumlah_klik',
            render: function(data) {
                return Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        },
        {
            data: 'ctr', 
            name: 'ctr',
            searchable: false,
            render: function(data) {
                return Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '%';
            }
        },
        {
            data: 'total_konversi', 
            name: 'total_konversi',
            render: function(data) {
                return Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        },
        {
            data: 'conversion_rate', 
            name: 'conversion_rate',
            searchable: false,
            render: function(data) {
                return Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '%';
            }
        },
        {
            data: 'total_produk_terjual', 
            name: 'total_produk_terjual',
            render: function(data) {
                return Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        },
        {
            data: 'total_biaya', 
            name: 'total_biaya',
            render: function(data) {
                return 'Rp ' + Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        },
        {
            data: 'cost_per_click', 
            name: 'cost_per_click',
            searchable: false,
            render: function(data) {
                return 'Rp ' + Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        },
        {
            data: 'total_omzet_penjualan', 
            name: 'total_omzet_penjualan',
            render: function(data) {
                return 'Rp ' + Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        },
        {
            data: 'roas', 
            name: 'roas', 
            searchable: false,
            render: function(data) {
                return Number(data).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        },
        {data: 'performance', name: 'performance', searchable: false}
    ],
    columnDefs: [
        { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], "className": "text-right" },
        { "targets": [11], "className": "text-center" }
    ],
    order: [[0, 'desc']],
    fixedHeader: true,
    scrollCollapse: true,
    deferRender: true,
    scroller: true
});

filterBiddingMode.change(function() {
    adsShopeeTable.draw();
    fetchShopeeImpressionData();
    initShopeeFunnelChart();
});

modalBiddingModeFilter.change(function() {
    shopeeDetailsTable.draw();
    updateShopeeSummary();
});

    // Campaign details table
    let shopeeDetailsTable = $('#shopeeDetailsTable').DataTable({
        responsive: false, // Set to false for horizontal scrolling
        scrollX: true,     // Enable horizontal scrolling
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_shopee_details_by_date') }}",
            data: function(d) {
                // Add the date from the modal to the request
                if (modalFilterDate.val()) {
                    let dates = modalFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else {
                    // If no date range is selected, use the single date
                    d.date = $('#shopeeDetailsModal').data('date');
                }
                if (modalKodeProdukFilter.val()) {
                    d.kode_produk = modalKodeProdukFilter.val();
                }
                if (modalBiddingModeFilter.val()) {
                    d.mode_bidding = modalBiddingModeFilter.val();
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {data: 'sku_induk', name: 'sku_induk'},
            {data: 'dilihat', name: 'dilihat'},
            {data: 'suka', name: 'suka'},
            {data: 'jumlah_klik', name: 'jumlah_klik'},
            {data: 'ctr', name: 'ctr', searchable: false},
            {data: 'dimasukan_ke_keranjang_produk', name: 'dimasukan_ke_keranjang_produk'},
            {data: 'produk_pesanan_dibuat', name: 'produk_pesanan_dibuat'},
            {data: 'produk_pesanan_siap_dikirim', name: 'produk_pesanan_siap_dikirim'},
            {data: 'produk_terjual', name: 'produk_terjual'},
            {data: 'cr', name: 'cr', searchable: false},
            {data: 'biaya', name: 'biaya'},
            {data: 'omzet_penjualan', name: 'omzet_penjualan'},
            {data: 'roas', name: 'roas', searchable: false},
            {data: 'efektivitas_iklan', name: 'efektivitas_iklan'},
            {data: 'performance', name: 'performance', searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        columnDefs: [
            { "targets": [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], "className": "text-right" },
            { "targets": [1, 15, 16], "className": "text-center" }
        ],
        order: [[0, 'asc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Button click handlers
    $('#shopeeResetFilterBtn').click(function() {
        filterDate.val('');
        filterKodeProduk.val('');
        filterBiddingMode.val('');
        adsShopeeTable.draw();
        fetchShopeeImpressionData();
        initShopeeFunnelChart();
    });

    // File input handler
    handleFileInputChange('shopeeAdsCsvFile');

    $('#importShopeeAdsForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingSwal('Processing...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('adSpentSocialMedia.import_shopee') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importShopeeAdsModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Reset the form
                        $('#importShopeeAdsForm')[0].reset();
                        $('.custom-file-label').text('Choose file');
                        
                        // Refresh the data
                        adsShopeeTable.draw();
                        fetchShopeeImpressionData();
                        initShopeeFunnelChart();
                    });
                } else {
                    // Handle unexpected success response without success status
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: response.message || 'Unknown error occurred',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Ajax error:", xhr, status, error);
                
                let errorMessage = 'An error occurred during import';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Filter change handlers
    filterKodeProduk.change(function() {
        adsShopeeTable.draw();
        fetchShopeeImpressionData();
        initShopeeFunnelChart();
    });

    filterDate.change(function() {
        adsShopeeTable.draw();
        fetchShopeeImpressionData();
        initShopeeFunnelChart();
    });

    modalKodeProdukFilter.change(function() {
        shopeeDetailsTable.draw();
        updateShopeeSummary();
    });

    modalFilterDate.change(function() {
        shopeeDetailsTable.draw();
        updateShopeeSummary();
    });

    // Modal event handlers
    $('#shopeeDetailsModal').on('shown.bs.modal', function() {
        // If no date range is set, initialize with the clicked date
        if (!modalFilterDate.val()) {
            const clickedDate = $('#shopeeDetailsModal').data('date');
            const formattedDate = moment(clickedDate).format('DD/MM/YYYY');
            modalFilterDate.val(formattedDate + ' - ' + formattedDate);
        }
        
        // Copy the current product filter to the modal filter
        if (filterKodeProduk.val()) {
            modalKodeProdukFilter.val(filterKodeProduk.val());
        }
        if (filterBiddingMode.val()) {
            modalBiddingModeFilter.val(filterBiddingMode.val());
        }
        
        updateShopeeSummary();
        
        // Adjust table columns for better display
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    $('#shopeeDetailsModal').on('hidden.bs.modal', function() {
        modalFilterDate.val('');
        modalKodeProdukFilter.val('');
        modalBiddingModeFilter.val('');
    });

    // Click event handler for date details
    $('#adsShopeeTable').on('click', '.date-details', function() {
        let date = $(this).data('date');
        let formattedDate = $(this).text();
        
        $('#shopeeDetailsModalLabel').text('Shopee Ads Details for ' + formattedDate);
        $('#shopeeDetailsModal').data('date', date);

        modalFilterDate.val('');
        modalKodeProdukFilter.val('');
        
        shopeeDetailsTable.draw();
        $('#shopeeDetailsModal').modal('show');
    });

    // Delete record handler
    $('#shopeeDetailsTable').on('click', '.delete-record', function() {
        const recordId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the selected record. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadingSwal('Deleting...');
                
                $.ajax({
                    url: "{{ route('adSpentSocialMedia.delete_shopee') }}",
                    type: 'DELETE',
                    data: {
                        id: recordId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Refresh the tables and charts
                            shopeeDetailsTable.draw();
                            adsShopeeTable.draw();
                            fetchShopeeImpressionData();
                            initShopeeFunnelChart();
                            updateShopeeSummary();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to delete record';
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }
        });
    });

    // Function to fetch impression data for chart
    function fetchShopeeImpressionData() {
        const filterValue = filterDate.val();
        const kodeProduk = filterKodeProduk.val();
        const modeBidding = filterBiddingMode.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.shopee_line_data') }}", window.location.origin);
        
        if (filterValue) {
            let dates = filterValue.split(' - ');
            url.searchParams.append('date_start', moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD'));
            url.searchParams.append('date_end', moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD'));
        }
        
        if (kodeProduk) {
            url.searchParams.append('kode_produk', kodeProduk);
        }
        if (modeBidding) { 
            url.searchParams.append('mode_bidding', modeBidding);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const chartData = result.data;
                    const dates = chartData.map(data => moment(data.date).format('DD MMM'));
                    const impressions = chartData.map(data => data.impressions);
                    
                    createLineChart('shopeeImpressionChart', 'Shopee Ads Impressions', dates, impressions);
                }
            })
            .catch(error => {
                console.error('Error fetching Shopee impression data:', error);
            });
    }

    // Function to initialize funnel chart
    function initShopeeFunnelChart() {
        const filterValue = filterDate.val();
        const kodeProduk = filterKodeProduk.val();
        const modeBidding = filterBiddingMode.val();

        const url = new URL("{{ route('adSpentSocialMedia.shopee_funnel_data') }}", window.location.origin);
        
        if (filterValue) {
            let dates = filterValue.split(' - ');
            url.searchParams.append('date_start', moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD'));
            url.searchParams.append('date_end', moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD'));
        }
        
        if (kodeProduk) {
            url.searchParams.append('kode_produk', kodeProduk);
        }

        if (modeBidding) {
            url.searchParams.append('mode_bidding', modeBidding);
        }

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    createShopeeFunnelChart(result.data, result.metrics);
                }
            })
            .catch(error => {
                console.error('Error fetching Shopee funnel data:', error);
            });
    }

    // Function to create Shopee funnel chart
    function createShopeeFunnelChart(data, metrics) {
        const funnelContainer = document.getElementById('shopeeFunnelChart');
        const metricsContainer = document.getElementById('shopeeFunnelMetrics');
        
        // Clear previous chart
        funnelContainer.innerHTML = '';
        
        // Create the funnel chart
        const height = 300;
        const maxValue = data && data.length > 0 && data[0].value ? Number(data[0].value) : 0;
        
        let html = `<div class="funnel-chart" style="width:100%; height:${height}px; position:relative;">`;
        
        // Check if data is available
        if (data && data.length > 0 && maxValue > 0) {
            data.forEach((item, index) => {
                // Ensure value is a number and not null
                const itemValue = item.value ? Number(item.value) : 0;
                const percentage = (itemValue / maxValue) * 100;
                const barWidth = Math.max(20, percentage);
                const barHeight = height / data.length;
                const leftMargin = (100 - barWidth) / 2;
                
                html += `
                    <div class="funnel-level" style="position:absolute; top:${index * barHeight}px; left:${leftMargin}%; width:${barWidth}%; height:${barHeight}px; display:flex; align-items:center; justify-content:center; background-color:rgba(60, 141, 188, ${1 - index * 0.2}); color:#fff; border-radius:5px;">
                        <div>
                            <div style="font-weight:bold;">${item.name || 'Unknown'}</div>
                            <div>${itemValue.toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                `;
            });
        } else {
            // Display a message if no data is available
            html += `<div class="text-center p-5">No data available</div>`;
        }
        
        html += `</div>`;
        funnelContainer.innerHTML = html;
        
        // Helper function to safely format numbers
        function formatNumber(value, options = {}) {
            if (value === null || value === undefined) return '0';
            try {
                return Number(value).toLocaleString('id-ID', options);
            } catch (e) {
                console.error('Error formatting number:', e);
                return '0';
            }
        }
        
        // Display metrics with safe number formatting
        let metricsHtml = `
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-mouse-pointer"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">CTR</span>
                            <span class="info-box-number">${formatNumber(metrics?.ctr, {minimumFractionDigits: 2, maximumFractionDigits: 2})}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Conversion Rate</span>
                            <span class="info-box-number">${formatNumber(metrics?.conversion_rate, {minimumFractionDigits: 2, maximumFractionDigits: 2})}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">ROAS</span>
                            <span class="info-box-number">${formatNumber(metrics?.roas, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-money-bill"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Cost</span>
                            <span class="info-box-number">Rp ${formatNumber(metrics?.cost)}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-hand-holding-usd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Revenue</span>
                            <span class="info-box-number">Rp ${formatNumber(metrics?.revenue)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        metricsContainer.innerHTML = metricsHtml;
    }

    // Function to update summary in the details modal
    function updateShopeeSummary() {
        const modalDate = $('#shopeeDetailsModal').data('date');
        const modalKodeProduk = modalKodeProdukFilter.val();
        let dateFilter = '';
        
        if (modalFilterDate.val()) {
            let dates = modalFilterDate.val().split(' - ');
            dateFilter = `date_start=${moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD')}&date_end=${moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD')}`;
        } else if (modalDate) {
            dateFilter = `date=${modalDate}`;
        }
        
        let url = `{{ route('adSpentSocialMedia.get_shopee_summary') }}?${dateFilter}`;
        
        if (modalKodeProduk) {
            url += `&kode_produk=${modalKodeProduk}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const summary = result.summary;
                    
                    // Update summary elements
                    $('#shopeeSummaryTotalAds').text(summary.total_ads.toLocaleString('id-ID'));
                    $('#shopeeSummaryTotalImpressions').text(summary.total_impressions.toLocaleString('id-ID'));
                    $('#shopeeSummaryTotalClicks').text(summary.total_clicks.toLocaleString('id-ID'));
                    $('#shopeeSummaryTotalConversions').text(summary.total_conversions.toLocaleString('id-ID'));
                    $('#shopeeSummaryTotalCost').text('Rp ' + summary.total_cost.toLocaleString('id-ID'));
                    $('#shopeeSummaryTotalRevenue').text('Rp ' + summary.total_revenue.toLocaleString('id-ID'));
                    $('#shopeeSummaryAvgCtr').text(summary.avg_ctr.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
                    $('#shopeeSummaryAvgConversionRate').text(summary.avg_conversion_rate.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
                    $('#shopeeSummaryAvgRoas').text(summary.avg_roas.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }
            })
            .catch(error => {
                console.error('Error fetching Shopee summary data:', error);
            });
    }

    // Initialize when Shopee Ads tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'shopee-ads-tab') {
            setTimeout(function() {
                adsShopeeTable.columns.adjust();
                fetchShopeeImpressionData();
                initShopeeFunnelChart();
            }, 150);
        }
    });

    // Initialize the page if this tab is active on page load
    $(function () {
        if ($('#shopee-ads-tab').hasClass('active')) {
            adsShopeeTable.draw();
            fetchShopeeImpressionData();
            initShopeeFunnelChart();
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
});
handleFileInputChange('shopeeSkuExcelFile');

// Form submit handler for importing SKU details
$('#importShopeeSkuForm').on('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    showLoadingSwal('Processing...');
    
    let formData = new FormData(this);
    $.ajax({
        url: "{{ route('adSpentSocialMedia.import_shopee_sku') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                $('#importShopeeSkuModal').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Reset the form
                    $('#importShopeeSkuForm')[0].reset();
                    $('.custom-file-label').text('Choose file');
                    
                    // Refresh the data
                    adsShopeeTable.draw();
                    fetchShopeeImpressionData();
                    initShopeeFunnelChart();
                });
            } else {
                // Handle unexpected success response without success status
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: response.message || 'Unknown error occurred',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Ajax error:", xhr, status, error);
            
            let errorMessage = 'An error occurred during import';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMessage,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>