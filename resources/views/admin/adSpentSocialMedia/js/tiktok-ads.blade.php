<script>
/**
 * TikTok Ads tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let tiktokFilterDate = initDateRangePicker('tiktokFilterDates');
    let tiktokFilterCategory = $('#tiktokKategoriProdukFilter');
    let tiktokFilterPic = $('#tiktokPicFilter');
    let tiktokModalFilterDate = initDateRangePicker('tiktokModalFilterDates'); // New variable
    let tiktokFunnelChart = null;
    let tiktokImpressionChart = null;

    // $('#btnImportTiktokAdsSpent').on('click', function() {
    //     console.log('Button clicked');
    //     console.log('Modal exists:', $('#importTiktokAdsSpentModal').length > 0);
    //     $('#importTiktokAdsSpentModal').modal('show');
    // });

    // Initialize TikTok Ads DataTable
    let adsTiktokTable = $('#adsTiktokTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_tiktok_ads_cpas') }}",
            data: function (d) {
                if (tiktokFilterDate.val()) {
                    let dates = tiktokFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (tiktokFilterCategory.val()) {
                    d.kategori_produk = tiktokFilterCategory.val();
                }
                if (tiktokFilterPic.val()) {
                    d.pic = tiktokFilterPic.val();
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {
                data: 'last_updated_count',
                name: 'last_updated_count',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            },
            {
                data: 'new_created_count',
                name: 'new_created_count',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            },
            {
                data: 'total_amount_spent', 
                name: 'total_amount_spent',
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            },
            {
                data: 'total_content_views', 
                name: 'total_content_views',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_adds_to_cart', 
                name: 'total_adds_to_cart',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_purchases', 
                name: 'total_purchases',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'cost_per_purchase', 
                name: 'cost_per_purchase',
                searchable: false,
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_conversion_value', 
                name: 'total_conversion_value',
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
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
            {
                data: 'total_impressions', 
                name: 'total_impressions',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            },
            {
                data: 'cpm', 
                name: 'cpm', 
                searchable: false,
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_link_clicks', 
                name: 'total_link_clicks',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
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
            {data: 'performance', name: 'performance', searchable: false}
        ],
        columnDefs: [
            { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], "className": "text-right" },
            { "targets": [12], "className": "text-center" }
        ],
        order: [[0, 'desc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    handleFileInputChange('tiktokGmvMaxFile');

    // GMV Max form submit handler
    $('#importTiktokGmvMaxForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingSwal('Processing GMV Max data...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('adSpentSocialMedia.import_tiktok_gmv_max') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importTiktokGmvMaxModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        localStorage.setItem('activeTab', 'tiktok-tab');
                        window.location.reload();
                    });
                } else {
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

    $('#adsTiktokTable').on('click', '.date-details', function() {
        let date = $(this).data('date');
        let formattedDate = $(this).text();
        
        $('#tiktokDailyDetailsModalLabel').text('TikTok Campaign Details for ' + formattedDate);
        $('#tiktokDailyDetailsModal').data('date', date);

        tiktokModalFilterDate.val('');
        
        tiktokCampaignDetailsTable.draw();
        $('#tiktokDailyDetailsModal').modal('show');
    });

    let tiktokCampaignDetailsTable = $('#tiktokCampaignDetailsTable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_tiktok_details_by_date') }}",
            data: function(d) {
                // Add the date from the modal to the request
                if (tiktokModalFilterDate.val()) {
                    let dates = tiktokModalFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else {
                    // If no date range is selected, use the single date
                    d.date = $('#tiktokDailyDetailsModal').data('date');
                }
                if (tiktokFilterPic.val()) {
                    d.pic = tiktokFilterPic.val();
                }
                if (tiktokFilterCategory.val()) {
                    d.kategori_produk = tiktokFilterCategory.val();
                }
            }
        },
        columns: [
            {data: 'account_name', name: 'account_name'},
            {data: 'kategori_produk', name: 'kategori_produk'},
            // New TOFU/MOFU/BOFU columns
            {data: 'tofu_spent', name: 'tofu_spent'},
            {data: 'tofu_percentage', name: 'tofu_percentage'},
            {data: 'mofu_spent', name: 'mofu_spent'},
            {data: 'mofu_percentage', name: 'mofu_percentage'},
            {data: 'bofu_spent', name: 'bofu_spent'},
            {data: 'bofu_percentage', name: 'bofu_percentage'},
            {data: 'last_updated_count', name: 'last_updated_count'},
            {data: 'new_created_count', name: 'new_created_count'},
            // Original columns
            {data: 'amount_spent', name: 'amount_spent'},
            {data: 'impressions', name: 'impressions'},
            {
                data: 'link_clicks', 
                name: 'link_clicks',
                render: function(data) {
                    // Handle string formatted numbers with comma as decimal separator
                    if (typeof data === 'string') {
                        // Extract the whole number part before the comma
                        return data.split(',')[0];
                    }
                    return Math.floor(data);
                }
            },
            {
                data: 'content_views_shared_items', 
                name: 'content_views_shared_items',
                render: function(data) {
                    // Handle string formatted numbers with comma as decimal separator
                    if (typeof data === 'string') {
                        // Extract the whole number part before the comma
                        return data.split(',')[0];
                    }
                    return Math.floor(data);
                }
            },
            {
                data: 'adds_to_cart_shared_items', 
                name: 'adds_to_cart_shared_items',
                render: function(data) {
                    // Handle string formatted numbers with comma as decimal separator
                    if (typeof data === 'string') {
                        // Extract the whole number part before the comma
                        return data.split(',')[0];
                    }
                    return Math.floor(data);
                }
            },
            {
                data: 'purchases_shared_items', 
                name: 'purchases_shared_items',
                render: function(data) {
                    // Handle string formatted numbers with comma as decimal separator
                    if (typeof data === 'string') {
                        // Extract the whole number part before the comma
                        return data.split(',')[0];
                    }
                    return Math.floor(data);
                }
            },
            {data: 'purchases_conversion_value_shared_items', name: 'purchases_conversion_value_shared_items'},
            {data: 'cost_per_view', name: 'cost_per_view'},
            {data: 'cost_per_atc', name: 'cost_per_atc'},
            {data: 'cost_per_purchase', name: 'cost_per_purchase'},
            {data: 'roas', name: 'roas'},
            {data: 'cpm', name: 'cpm'},
            {data: 'ctr', name: 'ctr'},
            {data: 'performance', name: 'performance', searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        columnDefs: [
            // Update the targets for right alignment to include the new columns
            { "targets": [2, 4, 6, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22], "className": "text-right" },
            // Update the targets for center alignment to include the new columns
            { "targets": [1, 3, 5, 7, 23], "className": "text-center" },
            { "targets": [24], "className": "text-center" }
        ],
        order: [[0, 'asc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    function updateTiktokCampaignSummary() {
        const filterValue = tiktokModalFilterDate.val();
        const date = $('#tiktokDailyDetailsModal').data('date');
        const kategoriProduk = tiktokFilterCategory.val();
        const picValue = tiktokFilterPic.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.get_tiktok_campaign_summary') }}", window.location.origin);
        
        if (filterValue) {
            let dates = filterValue.split(' - ');
            url.searchParams.append('date_start', moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD'));
            url.searchParams.append('date_end', moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD'));
        } else if (date) {
            url.searchParams.append('date', date);
        }
        
        if (kategoriProduk) {
            url.searchParams.append('kategori_produk', kategoriProduk);
        }
        
        if (picValue) {
            url.searchParams.append('pic', picValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    
                    // Update summary cards with tiktok prefix
                    $('#tiktokSummaryAccountsCount').text(data.accounts_count);
                    $('#tiktokSummaryTotalSpent').text('Rp ' + formatNumber(data.total_amount_spent));
                    $('#tiktokSummaryTotalPurchases').text(formatNumber(data.total_purchases));
                    $('#tiktokSummaryConversionValue').text('Rp ' + formatNumber(data.total_conversion_value));
                    $('#tiktokSummaryRoas').text(formatNumber(data.roas));
                    $('#tiktokSummaryCostPerPurchase').text('Rp ' + formatNumber(data.cost_per_purchase));
                    $('#tiktokSummaryImpressions').text(formatNumber(data.total_impressions));
                    $('#tiktokSummaryCtr').text(formatNumber(data.ctr) + '%');
                    
                    // Update funnel stage cards with tiktok prefix
                    $('#tiktokSummaryTofuSpent').text('Rp ' + formatNumber(data.tofu_spent));
                    $('#tiktokSummaryTofuPercentage').text(formatNumber(data.tofu_percentage) + '%');
                    $('#tiktokSummaryMofuSpent').text('Rp ' + formatNumber(data.mofu_spent));
                    $('#tiktokSummaryMofuPercentage').text(formatNumber(data.mofu_percentage) + '%');
                    $('#tiktokSummaryBofuSpent').text('Rp ' + formatNumber(data.bofu_spent));
                    $('#tiktokSummaryBofuPercentage').text(formatNumber(data.bofu_percentage) + '%');
                }
            })
            .catch(error => {
                console.error('Error fetching TikTok campaign summary:', error);
            });
    }

    // Format number for display
    function formatNumber(value) {
        if (value === null || value === undefined) return '-';
        return Number(value).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    $('#tiktokDailyDetailsModal').on('shown.bs.modal', function() {
        // If no date range is set, initialize with the clicked date
        if (!tiktokModalFilterDate.val()) {
            const clickedDate = $('#tiktokDailyDetailsModal').data('date');
            const formattedDate = moment(clickedDate).format('DD/MM/YYYY');
            tiktokModalFilterDate.val(formattedDate + ' - ' + formattedDate);
        }
        updateTiktokCampaignSummary();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    $('#tiktokDailyDetailsModal').on('hidden.bs.modal', function() {
        tiktokModalFilterDate.val('');
    });
    tiktokModalFilterDate.change(function() {
        tiktokCampaignDetailsTable.draw();
        updateTiktokCampaignSummary();
    });

    $('#tiktokCampaignDetailsTable').on('click', '.delete-account', function() {
        const accountName = $(this).data('account');
        let modalDate = $('#tiktokDailyDetailsModal').data('date');
        
        console.log('Delete button clicked:', {
            accountName: accountName,
            modalDate: modalDate
        });
        
        let formattedDate = moment(modalDate).format('YYYY-MM-DD');
        
        Swal.fire({
            title: 'Are you sure?',
            text: `This will delete all data for "${accountName}" on ${moment(modalDate).format('D MMM YYYY')}!`,
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
                    url: "{{ route('adSpentSocialMedia.delete_tiktok_by_account') }}",
                    type: 'DELETE',
                    data: {
                        account_name: accountName,
                        date: formattedDate,
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
                            tiktokCampaignDetailsTable.draw();
                            adsTiktokTable.draw();
                            fetchTiktokImpressionData();
                            initTiktokFunnelChart();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to delete data';
                        let debugInfo = '';
                        
                        // Show debug info if available
                        if (xhr.responseJSON && xhr.responseJSON.debug_info) {
                            console.log('Debug info:', xhr.responseJSON.debug_info);
                            const info = xhr.responseJSON.debug_info;
                            debugInfo = `\n\nRequested: ${info.requested_account} (${info.requested_date})`;
                            if (info.available_accounts && info.available_accounts.length > 0) {
                                debugInfo += `\nAvailable accounts: ${info.available_accounts.join(', ')}`;
                            } else {
                                debugInfo += '\nNo accounts available for this date.';
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg + debugInfo,
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }
        });
    });

    // Button click handlers
    $('#tiktokResetFilterBtn').click(function() {
        tiktokFilterDate.val('');
        tiktokFilterCategory.val('');
        tiktokFilterPic.val('');
        adsTiktokTable.draw();
        fetchTiktokImpressionData();
        initTiktokFunnelChart();
    });

    // File input handler
    handleFileInputChange('tiktokAdsFile');

    // Form submit handler
    $('#submitTiktokAdsSpentBtn').click(function() {
        $('#importTiktokAdsSpentForm').submit();
    });
    
    $('#importTiktokAdsSpentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingSwal('Processing...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('adSpentSocialMedia.import_tiktok') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importTiktokAdsSpentModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        localStorage.setItem('activeTab', 'tiktok-tab');
                        window.location.reload();
                    });
                } else {
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
    tiktokFilterCategory.change(function() {
        adsTiktokTable.draw();
        fetchTiktokImpressionData();
        initTiktokFunnelChart();
    });

    tiktokFilterDate.change(function() {
        adsTiktokTable.draw();
        fetchTiktokImpressionData();
        initTiktokFunnelChart();
    });

    tiktokFilterPic.change(function() {
        adsTiktokTable.draw();
        fetchTiktokImpressionData();
        initTiktokFunnelChart();
    });

    // Functions specific to TikTok Ads tab
    function fetchTiktokImpressionData() {
        const filterValue = tiktokFilterDate.val();
        const kategoriProduk = tiktokFilterCategory.val();
        const picValue = tiktokFilterPic.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.tiktok-line-data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (kategoriProduk) {
            url.searchParams.append('kategori_produk', kategoriProduk);
        }

        if (picValue) {
            url.searchParams.append('pic', picValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const impressionData = result.impressions;
                    const impressionDates = impressionData.map(data => data.date);
                    const impressions = impressionData.map(data => data.impressions);
                    
                    createLineChart('tiktokImpressionChart', 'TikTok Impressions', impressionDates, impressions, 'rgba(0, 0, 0, 1)');
                }
            })
            .catch(error => {
                console.error('Error fetching TikTok impression data:', error);
            });
    }

    function initTiktokFunnelChart() {
        const filterValue = tiktokFilterDate.val();
        const picValue = tiktokFilterPic.val();
        const kategoriProduk = tiktokFilterCategory.val();

        const url = new URL("{{ route('adSpentSocialMedia.tiktok-funnel-data') }}", window.location.origin);
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        if (kategoriProduk) {
            url.searchParams.append('kategori_produk', kategoriProduk);
        }
        
        if (picValue) {
            url.searchParams.append('pic', picValue);
        }

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    createFunnelChart('tiktokFunnelChart', result.data, 'tiktokFunnelMetrics');
                }
            })
            .catch(error => {
                console.error('Error fetching TikTok funnel data:', error);
            });
    }

    // Initialize when TikTok tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'tiktok-tab') {
            setTimeout(function() {
                adsTiktokTable.columns.adjust();
                if (!tiktokImpressionChart) {
                    fetchTiktokImpressionData();
                }
                if (!tiktokFunnelChart) {
                    initTiktokFunnelChart();
                }
            }, 150);
        }
    });

    // Initialize if TikTok tab is active on page load
    if ($('#tiktok-tab').hasClass('active')) {
        fetchTiktokImpressionData();
        initTiktokFunnelChart();
    }
});
</script>