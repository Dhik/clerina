<script>
/**
 * Shopee 3 tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let shopee3FilterDate = initDateRangePicker('shopee3FilterDates');
    let shopee3FilterCategory = $('#shopee3KategoriProdukFilter');
    let shopee3FilterPic = $('#shopee3PicFilter');
    let shopee3FunnelChart = null;
    let shopee3ImpressionChart = null;

    // Initialize Shopee 3 DataTable
    let adsShopee3Table = $('#adsShopee3Table').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_shopee3_ads_cpas') }}",
            data: function (d) {
                if (shopee3FilterDate.val()) {
                    let dates = shopee3FilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (shopee3FilterCategory.val()) {
                    d.kategori_produk = shopee3FilterCategory.val();
                }
                if (shopee3FilterPic.val()) {
                    d.pic = shopee3FilterPic.val();
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
    $('#shopee3ResetFilterBtn').click(function() {
        shopee3FilterDate.val('');
        shopee3FilterCategory.val('');
        shopee3FilterPic.val('');
        adsShopee3Table.draw();
        fetchShopee3ImpressionData();
        initShopee3FunnelChart();
    });

    // File input handler
    handleFileInputChange('shopee3AdsFile');

    // Form submit handler
    $('#submitShopee3AdsSpentBtn').click(function() {
        $('#importShopee3AdsSpentForm').submit();
    });
    
    $('#importShopee3AdsSpentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingSwal('Processing...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('adSpentSocialMedia.import_shopee3') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importShopee3AdsSpentModal').modal('hide');
                    
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
    shopee3FilterCategory.change(function() {
        adsShopee3Table.draw();
        fetchShopee3ImpressionData();
        initShopee3FunnelChart();
    });

    shopee3FilterDate.change(function() {
        adsShopee3Table.draw();
        fetchShopee3ImpressionData();
        initShopee3FunnelChart();
    });

    shopee3FilterPic.change(function() {
        adsShopee3Table.draw();
        fetchShopee3ImpressionData();
        initShopee3FunnelChart();
    });

    // Functions specific to Shopee 3 tab
    function fetchShopee3ImpressionData() {
        const filterValue = shopee3FilterDate.val();
        const kategoriProduk = shopee3FilterCategory.val();
        const picValue = shopee3FilterPic.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.shopee3-line-data') }}", window.location.origin);
        
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
                    
                    createLineChart('shopee3ImpressionChart', 'Shopee 3 Impressions', impressionDates, impressions, 'rgba(238, 77, 45, 1)');
                }
            })
            .catch(error => {
                console.error('Error fetching Shopee 3 impression data:', error);
            });
    }

    function initShopee3FunnelChart() {
        const filterValue = shopee3FilterDate.val();
        const picValue = shopee3FilterPic.val();
        const kategoriProduk = shopee3FilterCategory.val();

        const url = new URL("{{ route('adSpentSocialMedia.shopee3-funnel-data') }}", window.location.origin);
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
                    createFunnelChart('shopee3FunnelChart', result.data, 'shopee3FunnelMetrics');
                }
            })
            .catch(error => {
                console.error('Error fetching Shopee 3 funnel data:', error);
            });
    }

    // Initialize when Shopee 3 tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'shopee3-tab') {
            setTimeout(function() {
                adsShopee3Table.columns.adjust();
                if (!shopee3ImpressionChart) {
                    fetchShopee3ImpressionData();
                }
                if (!shopee3FunnelChart) {
                    initShopee3FunnelChart();
                }
            }, 150);
        }
    });

    // Initialize if Shopee 3 tab is active on page load
    if ($('#shopee3-tab').hasClass('active')) {
        fetchShopee3ImpressionData();
        initShopee3FunnelChart();
    }
});
</script>