<script>
/**
 * TikTok Ads tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let tiktokFilterDate = initDateRangePicker('tiktokFilterDates');
    let tiktokFilterCategory = $('#tiktokKategoriProdukFilter');
    let tiktokFilterPic = $('#tiktokPicFilter');
    let tiktokFunnelChart = null;
    let tiktokImpressionChart = null;

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