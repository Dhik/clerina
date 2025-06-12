<script>
/**
 * Shopee 2 tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let shopee2FilterDate = initDateRangePicker('shopee2FilterDates');
    let shopee2FilterCategory = $('#shopee2KategoriProdukFilter');
    let shopee2FilterPic = $('#shopee2PicFilter');
    let shopee2FunnelChart = null;
    let shopee2ImpressionChart = null;

    // Initialize Shopee 2 DataTable
    let adsShopee2Table = $('#adsShopee2Table').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_shopee2_ads_cpas') }}",
            data: function (d) {
                if (shopee2FilterDate.val()) {
                    let dates = shopee2FilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (shopee2FilterCategory.val()) {
                    d.kategori_produk = shopee2FilterCategory.val();
                }
                if (shopee2FilterPic.val()) {
                    d.pic = shopee2FilterPic.val();
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

    // Button click handlers
    $('#shopee2ResetFilterBtn').click(function() {
        shopee2FilterDate.val('');
        shopee2FilterCategory.val('');
        shopee2FilterPic.val('');
        adsShopee2Table.draw();
        fetchShopee2ImpressionData();
        initShopee2FunnelChart();
    });

    // File input handler
    handleFileInputChange('shopee2AdsFile');

    // Form submit handler
    $('#submitShopee2AdsSpentBtn').click(function() {
        $('#importShopee2AdsSpentForm').submit();
    });
    
    $('#importShopee2AdsSpentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingSwal('Processing...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('adSpentSocialMedia.import_shopee2') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importShopee2AdsSpentModal').modal('hide');
                    
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
    shopee2FilterCategory.change(function() {
        adsShopee2Table.draw();
        fetchShopee2ImpressionData();
        initShopee2FunnelChart();
    });

    shopee2FilterDate.change(function() {
        adsShopee2Table.draw();
        fetchShopee2ImpressionData();
        initShopee2FunnelChart();
    });

    shopee2FilterPic.change(function() {
        adsShopee2Table.draw();
        fetchShopee2ImpressionData();
        initShopee2FunnelChart();
    });

    // Functions specific to Shopee 2 tab
    function fetchShopee2ImpressionData() {
        const filterValue = shopee2FilterDate.val();
        const kategoriProduk = shopee2FilterCategory.val();
        const picValue = shopee2FilterPic.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.shopee2-line-data') }}", window.location.origin);
        
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
                    
                    createLineChart('shopee2ImpressionChart', 'Shopee 2 Impressions', impressionDates, impressions, 'rgba(238, 77, 45, 1)');
                }
            })
            .catch(error => {
                console.error('Error fetching Shopee 2 impression data:', error);
            });
    }

    function initShopee2FunnelChart() {
        const filterValue = shopee2FilterDate.val();
        const picValue = shopee2FilterPic.val();
        const kategoriProduk = shopee2FilterCategory.val();

        const url = new URL("{{ route('adSpentSocialMedia.shopee2-funnel-data') }}", window.location.origin);
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
                    createFunnelChart('shopee2FunnelChart', result.data, 'shopee2FunnelMetrics');
                }
            })
            .catch(error => {
                console.error('Error fetching Shopee 2 funnel data:', error);
            });
    }

    // Initialize when Shopee 2 tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'shopee2-tab') {
            setTimeout(function() {
                adsShopee2Table.columns.adjust();
                if (!shopee2ImpressionChart) {
                    fetchShopee2ImpressionData();
                }
                if (!shopee2FunnelChart) {
                    initShopee2FunnelChart();
                }
            }, 150);
        }
    });

    // Initialize if Shopee 2 tab is active on page load
    if ($('#shopee2-tab').hasClass('active')) {
        fetchShopee2ImpressionData();
        initShopee2FunnelChart();
    }
});
</script>