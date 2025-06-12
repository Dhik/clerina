<script>
/**
 * Shopee Mall tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let filterDate = initDateRangePicker('filterDates');
    let filterCategory = $('#kategoriProdukFilter');
    let filterPic = $('#picFilter');
    let modalFilterDate = initDateRangePicker('modalFilterDates');
    let funnelChart = null;
    let impressionChart = null;

    // Initialize Shopee Mall DataTable
    let adsShopeeMallTable = $('#adsMetaTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_ads_cpas') }}",
            data: function (d) {
                if (filterDate.val()) {
                    let dates = filterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (filterCategory.val()) {
                    d.kategori_produk = filterCategory.val();
                }
                if (filterPic.val()) {
                    d.pic = filterPic.val();
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

    // Campaign details table
    let campaignDetailsTable = $('#campaignDetailsTable').DataTable({
        responsive: false, // Set to false for horizontal scrolling
        scrollX: true,     // Enable horizontal scrolling
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_details_by_date') }}",
            data: function(d) {
                // Add the date from the modal to the request
                if (modalFilterDate.val()) {
                    let dates = modalFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else {
                    // If no date range is selected, use the single date
                    d.date = $('#dailyDetailsModal').data('date');
                }
                if (filterPic.val()) {
                    d.pic = filterPic.val();
                }
                if (filterCategory.val()) {
                    d.kategori_produk = filterCategory.val();
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

    // Button click handlers
    $('#resetFilterBtn').click(function() {
        filterDate.val('');
        filterCategory.val('');
        filterPic.val('');
        adsShopeeMallTable.draw();
        fetchImpressionData();
        initFunnelChart();
    });

    // File input handler
    handleFileInputChange('metaAdsCsvFile');

    // Form submit handler
    $('#submitMetaAdsSpentBtn').click(function() {
        $('#importMetaAdsSpentForm').submit();
    });
    
    $('#importMetaAdsSpentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingSwal('Processing...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('adSpentSocialMedia.import') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importMetaAdsSpentModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
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
    filterCategory.change(function() {
        adsShopeeMallTable.draw();
        if ($('#dailyDetailsModal').is(':visible')) {
            campaignDetailsTable.draw();
            updateCampaignSummary();
        }
        fetchImpressionData();
        initFunnelChart();
    });

    filterDate.change(function() {
        adsShopeeMallTable.draw();
        fetchImpressionData();
        initFunnelChart();
    });

    filterPic.change(function() {
        adsShopeeMallTable.draw();
        if ($('#dailyDetailsModal').is(':visible')) {
            campaignDetailsTable.draw();
            updateCampaignSummary();
        }
        fetchImpressionData();
        initFunnelChart();
    });

    // Modal event handlers
    $('#dailyDetailsModal').on('shown.bs.modal', function() {
        // If no date range is set, initialize with the clicked date
        if (!modalFilterDate.val()) {
            const clickedDate = $('#dailyDetailsModal').data('date');
            const formattedDate = moment(clickedDate).format('DD/MM/YYYY');
            modalFilterDate.val(formattedDate + ' - ' + formattedDate);
        }
        updateCampaignSummary();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    $('#dailyDetailsModal').on('hidden.bs.modal', function() {
        modalFilterDate.val('');
    });

    // Click event handler for date details
    $('#adsMetaTable').on('click', '.date-details', function() {
        let date = $(this).data('date');
        let formattedDate = $(this).text();
        
        $('#dailyDetailsModalLabel').text('Campaign Details for ' + formattedDate);
        $('#dailyDetailsModal').data('date', date);

        modalFilterDate.val('');
        
        campaignDetailsTable.draw();
        $('#dailyDetailsModal').modal('show');
    });

    $('#campaignDetailsTable').on('click', '.delete-account', function() {
        const accountName = $(this).data('account');
        let modalDate = $('#dailyDetailsModal').data('date');
        
        console.log('Delete button clicked:', {
            accountName: accountName,
            modalDate: modalDate
        });
        
        let formattedDate = moment(modalDate).format('YYYY-MM-DD');
        
        console.log('Formatted date:', formattedDate);
        
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
                    url: "{{ route('adSpentSocialMedia.delete_by_account') }}",
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
                            campaignDetailsTable.draw();
                            adsShopeeMallTable.draw();
                            fetchImpressionData();
                            initFunnelChart();
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

    function fetchImpressionData() {
        const filterValue = filterDate.val();
        const kategoriProduk = filterCategory.val();
        const picValue = filterPic.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.line-data') }}", window.location.origin);
        
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
                    
                    createLineChart('impressionChart', 'Shopee Mall Impressions', impressionDates, impressions);
                }
            })
            .catch(error => {
                console.error('Error fetching impression data:', error);
            });
    }

    function initFunnelChart() {
        const filterValue = filterDate.val();
        const picValue = filterPic.val();
        const kategoriProduk = filterCategory.val();

        const url = new URL("{{ route('adSpentSocialMedia.funnel-data') }}", window.location.origin);
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
                    createFunnelChart('funnelChart', result.data, 'funnelMetrics', result);
                }
            })
            .catch(error => {
                console.error('Error fetching funnel data:', error);
            });
    }
    // Initialize when Shopee Mall tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'shopee-mall-tab') {
            setTimeout(function() {
                adsShopeeMallTable.columns.adjust();
                fetchImpressionData();
                initFunnelChart();
            }, 150);
        }
    });

    // Initialize the page if this tab is active on page load
    $(function () {
        if ($('#shopee-mall-tab').hasClass('active')) {
            adsShopeeMallTable.draw();
            fetchImpressionData();
            initFunnelChart();
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
});
</script>