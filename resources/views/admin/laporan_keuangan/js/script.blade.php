<script>
    // Date range picker
    let filterDate = $('#filterDates');
    let currentTab = 'summary';
    let dataTables = {};

    $('.daterange').daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        alwaysShowCalendars: true,
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

    $('.daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $(this).trigger('change'); 
    });

    $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $(this).trigger('change'); 
    });

    filterDate.change(function () {
        reloadAllTables();
        fetchSummary();
    });

    function formatNumber(num) {
        return Math.round(num).toLocaleString('id-ID');
    }

    // Initialize DataTables for all tabs
    function initializeTables() {
        // Summary table (updated with order count column)
        dataTables.summary = $('#summaryTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Export to Excel',
                    className: 'btn btn-success',
                    title: 'Financial Report Summary'
                }
            ],
            ajax: {
                url: "{{ route('lk.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val();
                    d.type = 'summary';
                }
            },
            columns: [
                { data: 'date', name: 'date' },
                { data: 'total_gross_revenue', name: 'total_gross_revenue' },
                { data: 'total_hpp', name: 'total_hpp' },
                { data: 'total_fee_admin', name: 'total_fee_admin' },
                { data: 'net_profit', name: 'net_profit' },
                { data: 'total_count_orders', name: 'total_count_orders' }
            ],
            columnDefs: [
                { 
                    "targets": [1, 2, 3, 4], 
                    "className": "text-right" 
                },
                { 
                    "targets": [5], 
                    "className": "text-center" 
                }
            ],
            order: [[0, 'desc']],
            createdRow: function(row, data, dataIndex) {
                // Make Gross Revenue clickable in column 1
                const grossRevenueCell = $(row).find('td:eq(1)');
                const grossRevenueData = grossRevenueCell.html();
                grossRevenueCell.html(`<a href="#" class="show-gross-revenue-details" data-date="${data.date}" data-type="gross_revenue">${grossRevenueData}</a>`);
                
                // Make HPP clickable in column 2
                const hppCell = $(row).find('td:eq(2)');
                const hppData = hppCell.html();
                hppCell.html(`<a href="#" class="show-details" data-date="${data.date}" data-type="hpp">${hppData}</a>`);
                
                // Make Fee Admin clickable in column 3
                const feeAdminCell = $(row).find('td:eq(3)');
                const feeAdminData = feeAdminCell.html();
                feeAdminCell.html(`<a href="#" class="show-details" data-date="${data.date}" data-type="fee_admin">${feeAdminData}</a>`);
                
                // Make Net Profit clickable in column 4
                const netProfitCell = $(row).find('td:eq(4)');
                const netProfitData = netProfitCell.html();
                netProfitCell.html(`<a href="#" class="show-details" data-date="${data.date}" data-type="net_profit">${netProfitData}</a>`);
            }
        });
        
        // Create the channel columns dynamically for the other tabs
        // Use an array where we know the exact structure
        let grossRevenueColumns = [
            { data: 'date', name: 'date' }
        ];
        
        // Loop through sales channels to build column definitions
        @foreach($salesChannels as $channel)
        grossRevenueColumns.push({ 
            data: 'channel_{{ $channel->id }}', 
            name: 'channel_{{ $channel->id }}',
            className: 'text-right',
            defaultContent: 'Rp 0'  // Provide default content if the value is missing
        });
        @endforeach
        
        grossRevenueColumns.push({ 
            data: 'total', 
            name: 'total',
            className: 'text-right font-weight-bold'
        });
        
        // Gross Revenue table with dynamic columns
        dataTables.grossRevenue = $('#grossRevenueTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            dom: 'Bfrtip',
            scrollX: true,
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Export to Excel',
                    className: 'btn btn-success',
                    title: 'Gross Revenue by Channel'
                }
            ],
            ajax: {
                url: "{{ route('lk.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val();
                    d.type = 'gross_revenue';
                }
            },
            columns: grossRevenueColumns,
            order: [[0, 'desc']],
            createdRow: function(row, data, dataIndex) {
                // For each cell except the first (date) and last (total)
                $(row).find('td').not(':first').not(':last').each(function(cellIndex) {
                    const cellData = $(this).html();
                    if (cellData !== 'Rp 0' && cellData !== '') {
                        // Add data attributes and click handler class to the cell
                        const date = data.date;
                        $(this).html(`<a href="#" class="show-gross-revenue-details" data-date="${date}" data-type="gross_revenue">${cellData}</a>`);
                    }
                });
            }
        });
        
        // HPP table (similar structure)
        let hppColumns = [
            { data: 'date', name: 'date' }
        ];
        
        @foreach($salesChannels as $channel)
        hppColumns.push({ 
            data: 'channel_{{ $channel->id }}', 
            name: 'channel_{{ $channel->id }}',
            className: 'text-right',
            defaultContent: 'Rp 0'  // Provide default content
        });
        @endforeach
        
        hppColumns.push({ 
            data: 'total', 
            name: 'total',
            className: 'text-right font-weight-bold'
        });
        
        dataTables.hpp = $('#hppTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            dom: 'Bfrtip',
            scrollX: true,
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Export to Excel',
                    className: 'btn btn-success',
                    title: 'HPP by Channel'
                }
            ],
            ajax: {
                url: "{{ route('lk.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val();
                    d.type = 'hpp';
                }
            },
            columns: hppColumns,
            order: [[0, 'desc']],
            createdRow: function(row, data, dataIndex) {
                // For each cell except the first (date) and last (total)
                $(row).find('td').not(':first').not(':last').each(function(cellIndex) {
                    const cellData = $(this).html();
                    if (cellData !== 'Rp 0' && cellData !== '') {
                        // Add data attributes and click handler class to the cell
                        const date = data.date;
                        $(this).html(`<a href="#" class="show-details" data-date="${date}" data-type="hpp">${cellData}</a>`);
                    }
                });
            }
        });

        // Fee Admin table (similar structure)
        let feeAdminColumns = [
            { data: 'date', name: 'date' }
        ];
        
        @foreach($salesChannels as $channel)
        feeAdminColumns.push({ 
            data: 'channel_{{ $channel->id }}', 
            name: 'channel_{{ $channel->id }}',
            className: 'text-right',
            defaultContent: 'Rp 0'  // Provide default content
        });
        @endforeach
        
        feeAdminColumns.push({ 
            data: 'total', 
            name: 'total',
            className: 'text-right font-weight-bold'
        });
        
        dataTables.feeAdmin = $('#feeAdminTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            dom: 'Bfrtip',
            scrollX: true,
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Export to Excel',
                    className: 'btn btn-success',
                    title: 'Fee Admin by Channel'
                }
            ],
            ajax: {
                url: "{{ route('lk.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val();
                    d.type = 'fee_admin';
                }
            },
            columns: feeAdminColumns,
            order: [[0, 'desc']]
        });
    }

    // Reload all tables when date filter changes
    function reloadAllTables() {
        for (const key in dataTables) {
            if (dataTables.hasOwnProperty(key)) {
                dataTables[key].ajax.reload();
            }
        }
    }

    // Handle tab change
    $('#reportTabs a').on('shown.bs.tab', function (e) {
        const tabId = $(e.target).attr('id');
        currentTab = tabId.replace('-tab', '');
        
        // Adjust datatable to correct size when tab is shown
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    // Initialize channel dataTables object to store DataTable instances
    let channelDataTables = {};

    // Handle detail modal
    $(document).on('click', '.show-details', function(e) {
        e.preventDefault();
        
        const date = $(this).data('date');
        const type = $(this).data('type');
        
        // Special handling for HPP details with tabs
        if (type === 'hpp') {
            // Show the HPP modal with loading overlay
            $('#hppDetailModal').modal('show');
            // Loading overlay is already visible by default
            
            $.ajax({
                url: "{{ route('lk.details') }}",
                method: 'GET',
                data: {
                    date: date,
                    type: type
                },
                success: function(response) {
                    // Hide loading overlay when data is ready
                    $('#hpp-loading-overlay').hide();
                    
                    // Set modal title
                    $('#hppDetailModalLabel').text('HPP Details - ' + date);
                    
                    // Clear previous tabs and content
                    $('#channel-tabs').empty();
                    $('#channel-tab-content').empty();
                    
                    // Add tabs for each channel
                    let isFirst = true;
                    response.channels.forEach(function(channel, index) {
                        // Create tab
                        const tabId = 'channel-tab-' + channel.id;
                        const tabClass = isFirst ? 'nav-link active' : 'nav-link';
                        const tab = `
                            <li class="nav-item">
                                <a class="${tabClass}" id="${tabId}-tab" data-toggle="pill" href="#${tabId}" 
                                role="tab" aria-controls="${tabId}" aria-selected="${isFirst ? 'true' : 'false'}">
                                    ${channel.name}
                                </a>
                            </li>
                        `;
                        $('#channel-tabs').append(tab);
                        
                        // Create tab content
                        const channelData = response.data[channel.id] || [];
                        const channelSummary = response.summaries[channel.id] || { total: 0 };
                        const tabContentClass = isFirst ? 'tab-pane fade show active' : 'tab-pane fade';
                        
                        let tabContent = `
                            <div class="${tabContentClass}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                                <div class="channel-summary">
                                    <h5>${channel.name}</h5>
                                    <h5>Total: Rp ${formatNumber(channelSummary.total)}</h5>
                                </div>
                                <div class="table-responsive">
                                    <table id="hpp-table-${channel.id}" class="table table-bordered table-striped table-sm" width="100%">
                                        <thead>
                                            <tr>
                                                <th>SKU</th>
                                                <th>Product</th>
                                                <th class="text-right">Quantity</th>
                                                <th class="text-right">HPP</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                        
                        // Add rows to the tab content
                        if (channelData.length > 0) {
                            channelData.forEach(function(item) {
                                tabContent += `
                                    <tr>
                                        <td>${item.sku}</td>
                                        <td>${item.product}</td>
                                        <td class="text-right">${item.qty}</td>
                                        <td class="text-right">Rp ${formatNumber(item.hpp)}</td>
                                        <td class="text-right">Rp ${formatNumber(item.total)}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            tabContent += `
                                <tr>
                                    <td colspan="5" class="text-center">No data available</td>
                                </tr>
                            `;
                        }
                        
                        tabContent += `
                                        </tbody>
                                        <tfoot>
                                            <tr class="font-weight-bold">
                                                <td colspan="4" class="text-right">Total</td>
                                                <td class="text-right">Rp ${formatNumber(channelSummary.total)}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        `;
                        
                        $('#channel-tab-content').append(tabContent);
                        
                        isFirst = false;
                    });
                    
                    // Initialize DataTables for each channel
                    response.channels.forEach(function(channel) {
                        if (channelDataTables[channel.id]) {
                            channelDataTables[channel.id].destroy();
                        }
                        
                        channelDataTables[channel.id] = $(`#hpp-table-${channel.id}`).DataTable({
                            paging: true,
                            lengthChange: false,
                            searching: true,
                            ordering: true,
                            info: true,
                            autoWidth: false,
                            pageLength: 10,
                            language: {
                                search: "Search SKU/Product:"
                            }
                        });
                    });
                },
                error: function(xhr, status, error) {
                    // Hide loading overlay on error
                    $('#hpp-loading-overlay').hide();
                    console.error('Error fetching HPP details:', error);
                    alert('Error fetching HPP details. Please try again.');
                }
            });
        } else {
            // Standard detail modal for other types (fee_admin and net_profit)
            $.ajax({
                url: "{{ route('lk.details') }}",
                method: 'GET',
                data: {
                    date: date,
                    type: type
                },
                success: function(response) {
                    // Set modal title based on type and date
                    let modalTitle;
                    switch(type) {
                        case 'fee_admin':
                            modalTitle = 'Fee Admin Details - ' + date;
                            break;
                        case 'net_profit':
                            modalTitle = 'Net Profit & HPP Percentage Details - ' + date;
                            break;
                        default:
                            modalTitle = 'Details - ' + date;
                    }
                    
                    $('#detailModalLabel').text(modalTitle);
                    
                    // Clear previous table content
                    $('#detailTableHead').empty();
                    $('#detailTableBody').empty();
                    $('#detailTableFoot').empty();
                    
                    // Create table header
                    let headerRow = '<tr>';
                    headerRow += '<th>Sales Channel</th>';
                    
                    if (type === 'fee_admin') {
                        headerRow += '<th class="text-right">Fee Admin</th>';
                        headerRow += '<th class="text-right">Orders Count</th>';
                    } else if (type === 'net_profit') {
                        headerRow += '<th class="text-right">Gross Revenue</th>';
                        headerRow += '<th class="text-right">Fee Admin</th>';
                        headerRow += '<th class="text-right">Net Profit</th>';
                        headerRow += '<th class="text-right">HPP</th>';
                        headerRow += '<th class="text-right">HPP %</th>';
                        headerRow += '<th class="text-right">Orders Count</th>';
                    }
                    
                    headerRow += '</tr>';
                    $('#detailTableHead').append(headerRow);
                    
                    // Add data rows
                    $.each(response.details, function(index, item) {
                        let row = '<tr>';
                        row += '<td>' + item.channel_name + '</td>';
                        
                        if (type === 'fee_admin') {
                            row += '<td class="text-right">Rp ' + formatNumber(item.fee_admin) + '</td>';
                            row += '<td class="text-center"><span class="badge badge-info">' + formatNumber(item.count_orders) + '</span></td>';
                        } else if (type === 'net_profit') {
                            row += '<td class="text-right">Rp ' + formatNumber(item.gross_revenue) + '</td>';
                            row += '<td class="text-right">Rp ' + formatNumber(item.fee_admin) + '</td>';
                            row += '<td class="text-right">Rp ' + formatNumber(item.net_profit) + '</td>';
                            row += '<td class="text-right">Rp ' + formatNumber(item.hpp) + '</td>';
                            row += '<td class="text-right">' + item.hpp_percentage.toFixed(2) + '%</td>';
                            row += '<td class="text-center"><span class="badge badge-info">' + formatNumber(item.count_orders) + '</span></td>';
                        }
                        
                        row += '</tr>';
                        $('#detailTableBody').append(row);
                    });
                    
                    // Add footer row with totals
                    let footerRow = '<tr class="font-weight-bold">';
                    footerRow += '<td>Total</td>';
                    
                    if (type === 'fee_admin') {
                        footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_fee_admin) + '</td>';
                        footerRow += '<td class="text-center"><span class="badge badge-primary">' + formatNumber(response.summary.total_count_orders) + '</span></td>';
                    } else if (type === 'net_profit') {
                        footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_gross_revenue) + '</td>';
                        footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_fee_admin) + '</td>';
                        footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_net_profit) + '</td>';
                        footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_hpp) + '</td>';
                        footerRow += '<td class="text-right">' + response.summary.total_hpp_percentage.toFixed(2) + '%</td>';
                        footerRow += '<td class="text-center"><span class="badge badge-primary">' + formatNumber(response.summary.total_count_orders) + '</span></td>';
                    }
                    
                    footerRow += '</tr>';
                    $('#detailTableFoot').append(footerRow);
                    
                    // Show the modal
                    $('#detailModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching details:', error);
                    alert('Error fetching details. Please try again.');
                }
            });
        }
    });

    // Handle gross revenue detail modal
    $(document).on('click', '.show-gross-revenue-details', function(e) {
        e.preventDefault();
        
        const date = $(this).data('date');
        const type = 'gross_revenue'; // Always set to gross_revenue for this function
        
        // Show the modal with loading overlay
        $('#hppDetailModal').modal('show');
        $('#hpp-loading-overlay').show();
        
        $.ajax({
            url: "{{ route('lk.gross_revenue_details') }}",
            method: 'GET',
            data: {
                date: date,
                type: type
            },
            success: function(response) {
                // Hide loading overlay when data is ready
                $('#hpp-loading-overlay').hide();
                
                // Set modal title
                $('#hppDetailModalLabel').text('Gross Revenue Details - ' + date);
                
                // Clear previous tabs and content
                $('#channel-tabs').empty();
                $('#channel-tab-content').empty();
                
                // Add tabs for each channel
                let isFirst = true;
                response.channels.forEach(function(channel, index) {
                    // Create tab
                    const tabId = 'channel-tab-' + channel.id;
                    const tabClass = isFirst ? 'nav-link active' : 'nav-link';
                    const tab = `
                        <li class="nav-item">
                            <a class="${tabClass}" id="${tabId}-tab" data-toggle="pill" href="#${tabId}" 
                            role="tab" aria-controls="${tabId}" aria-selected="${isFirst ? 'true' : 'false'}">
                                ${channel.name}
                            </a>
                        </li>
                    `;
                    $('#channel-tabs').append(tab);
                    
                    // Create tab content
                    const channelData = response.data[channel.id] || [];
                    const channelSummary = response.summaries[channel.id] || { total: 0 };
                    const tabContentClass = isFirst ? 'tab-pane fade show active' : 'tab-pane fade';
                    
                    let tabContent = `
                        <div class="${tabContentClass}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                            <div class="channel-summary">
                                <h5>${channel.name}</h5>
                                <h5>Total: Rp ${formatNumber(channelSummary.total)}</h5>
                            </div>
                            <div class="table-responsive">
                                <table id="gross-revenue-table-${channel.id}" class="table table-bordered table-striped table-sm" width="100%">
                                    <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>Product</th>
                                            <th class="text-right">Quantity</th>
                                            <th class="text-right">Gross Revenue</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                    
                    // Add rows to the tab content
                    if (channelData.length > 0) {
                        channelData.forEach(function(item) {
                            tabContent += `
                                <tr>
                                    <td>${item.sku}</td>
                                    <td>${item.product}</td>
                                    <td class="text-right">${item.qty}</td>
                                    <td class="text-right">Rp ${formatNumber(item.gross_revenue)}</td>
                                    <td class="text-right">Rp ${formatNumber(item.total)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tabContent += `
                            <tr>
                                <td colspan="5" class="text-center">No data available</td>
                            </tr>
                        `;
                    }
                    
                    tabContent += `
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="4" class="text-right">Total</td>
                                            <td class="text-right">Rp ${formatNumber(channelSummary.total)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `;
                    
                    $('#channel-tab-content').append(tabContent);
                    
                    isFirst = false;
                });
                
                // Initialize DataTables for each channel
                response.channels.forEach(function(channel) {
                    if (channelDataTables[channel.id]) {
                        channelDataTables[channel.id].destroy();
                    }
                    
                    channelDataTables[channel.id] = $(`#gross-revenue-table-${channel.id}`).DataTable({
                        paging: true,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        info: true,
                        autoWidth: false,
                        pageLength: 10,
                        language: {
                            search: "Search SKU/Product:"
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                // Hide loading overlay on error
                $('#hpp-loading-overlay').hide();
                console.error('Error fetching Gross Revenue details:', error);
                alert('Error fetching Gross Revenue details. Please try again.');
            }
        });
    });

    function fetchSummary() {
        const filterDates = document.getElementById('filterDates').value;
        const url = new URL("{{ route('lk.summary') }}");
        if (filterDates) {
            url.searchParams.append('filterDates', filterDates);
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Update KPI cards
                document.getElementById('totalGrossRevenue').textContent = 'Rp ' + formatNumber(data.total_gross_revenue || 0);
                document.getElementById('totalHpp').textContent = 'Rp ' + formatNumber(data.total_hpp || 0);
                document.getElementById('totalFeeAdmin').textContent = 'Rp ' + formatNumber(data.total_fee_admin || 0);
                document.getElementById('netProfit').textContent = 'Rp ' + formatNumber(data.net_profit || 0);
                
                // Update channel summary tables
                updateChannelRevenueCards(data.channel_summary);
                updateChannelHppCards(data.channel_summary);
                updateChannelFeeAdminCards(data.channel_summary);
                updateMonthlyOrderCountCards(data.monthly_order_count);
            })
            .catch(error => console.error('Error:', error));
    }

    function updateMonthlyOrderCountCards(monthlyOrderCount) {
        const container = document.getElementById('monthlyOrderCountCards');
        container.innerHTML = ''; // Clear previous content
        
        // Calculate total orders for percentage calculation
        const totalOrders = monthlyOrderCount.reduce((sum, channel) => sum + parseInt(channel.total_orders), 0);
        
        // Create a table row for each channel
        monthlyOrderCount.forEach(channel => {
            const channelName = channel.channel_name.toLowerCase();
            let indicatorClass = 'other-indicator';
            let logoClass = 'fa-shopping-bag';
            
            // Determine the indicator class and logo based on channel name
            if (channelName.includes('shopee') && !channelName.includes('2') && !channelName.includes('3')) {
                indicatorClass = 'shopee-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 2') || channelName.includes('shopee2')) {
                indicatorClass = 'shopee-2-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 3') || channelName.includes('shopee3')) {
                indicatorClass = 'shopee-3-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('lazada')) {
                indicatorClass = 'lazada-indicator';
                logoClass = 'fa-box';
            } else if (channelName.includes('tokopedia')) {
                indicatorClass = 'tokopedia-indicator';
                logoClass = 'fa-store';
            } else if (channelName.includes('tiktok')) {
                indicatorClass = 'tiktok-indicator';
                logoClass = 'fa-music';
            } else if (channelName === 'b2b') {
                indicatorClass = 'b2b-indicator';
                logoClass = 'fa-handshake';
            } else if (channelName === 'crm') {
                indicatorClass = 'crm-indicator';
                logoClass = 'fa-users';
            }
            
            // Calculate percentage
            const percentage = totalOrders > 0 ? ((channel.total_orders / totalOrders) * 100) : 0;
            
            // Create table row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="${indicatorClass}">
                    <span class="channel-icon"><i class="fas ${logoClass}"></i></span>
                    ${channel.channel_name}
                </td>
                <td class="text-center">
                    <span class="badge badge-primary">${formatNumber(channel.total_orders)} orders</span>
                </td>
                <td class="text-right">${percentage.toFixed(1)}%</td>
            `;
            
            container.appendChild(row);
        });
    }

    function updateChannelRevenueCards(channelSummary) {
        const container = document.getElementById('channelRevenueCards');
        container.innerHTML = ''; // Clear previous content
        
        // Create a table row for each channel
        channelSummary.forEach(channel => {
            const channelName = channel.channel_name.toLowerCase();
            let indicatorClass = 'other-indicator';
            let logoClass = 'fa-shopping-bag';
            
            // Determine the indicator class and logo based on channel name
            if (channelName.includes('shopee') && !channelName.includes('2') && !channelName.includes('3')) {
                indicatorClass = 'shopee-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 2') || channelName.includes('shopee2')) {
                indicatorClass = 'shopee-2-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 3') || channelName.includes('shopee3')) {
                indicatorClass = 'shopee-3-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('lazada')) {
                indicatorClass = 'lazada-indicator';
                logoClass = 'fa-box';
            } else if (channelName.includes('tokopedia')) {
                indicatorClass = 'tokopedia-indicator';
                logoClass = 'fa-store';
            } else if (channelName.includes('tiktok')) {
                indicatorClass = 'tiktok-indicator';
                logoClass = 'fa-music';
            } else if (channelName === 'b2b') {
                indicatorClass = 'b2b-indicator';
                logoClass = 'fa-handshake';
            } else if (channelName === 'crm') {
                indicatorClass = 'crm-indicator';
                logoClass = 'fa-users';
            }
            
            // Create table row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="${indicatorClass}">
                    <span class="channel-icon"><i class="fas ${logoClass}"></i></span>
                    ${channel.channel_name}
                </td>
                <td class="text-right">Rp ${formatNumber(channel.channel_gross_revenue)}</td>
            `;
            
            container.appendChild(row);
        });
    }

    function updateChannelHppCards(channelSummary) {
        const container = document.getElementById('channelHppCards');
        container.innerHTML = ''; // Clear previous content
        
        // Create a table row for each channel
        channelSummary.forEach(channel => {
            const channelName = channel.channel_name.toLowerCase();
            let indicatorClass = 'other-indicator'; // Use the same indicator classes as revenue table
            let logoClass = 'fa-shopping-bag';
            
            // Determine the indicator class and logo based on channel name
            if (channelName.includes('shopee') && !channelName.includes('2') && !channelName.includes('3')) {
                indicatorClass = 'shopee-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 2') || channelName.includes('shopee2')) {
                indicatorClass = 'shopee-2-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 3') || channelName.includes('shopee3')) {
                indicatorClass = 'shopee-3-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('lazada')) {
                indicatorClass = 'lazada-indicator';
                logoClass = 'fa-box';
            } else if (channelName.includes('tokopedia')) {
                indicatorClass = 'tokopedia-indicator';
                logoClass = 'fa-store';
            } else if (channelName.includes('tiktok')) {
                indicatorClass = 'tiktok-indicator';
                logoClass = 'fa-music';
            } else if (channelName === 'b2b') {
                indicatorClass = 'b2b-indicator';
                logoClass = 'fa-handshake';
            } else if (channelName === 'crm') {
                indicatorClass = 'crm-indicator';
                logoClass = 'fa-users';
            }
            
            // Create table row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="${indicatorClass}">
                    <span class="channel-icon"><i class="fas ${logoClass}"></i></span>
                    ${channel.channel_name}
                </td>
                <td class="text-right">Rp ${formatNumber(channel.channel_hpp)}</td>
                <td class="text-right">${channel.channel_hpp_percentage.toFixed(1)}%</td>
            `;
            
            container.appendChild(row);
        });
    }

    function updateChannelFeeAdminCards(channelSummary) {
        const container = document.getElementById('channelFeeAdminCards');
        container.innerHTML = ''; // Clear previous content
        
        // Create a table row for each channel
        channelSummary.forEach(channel => {
            const channelName = channel.channel_name.toLowerCase();
            let indicatorClass = 'other-indicator'; // Use the same indicator classes as revenue table
            let logoClass = 'fa-shopping-bag';
            
            // Determine the indicator class and logo based on channel name
            if (channelName.includes('shopee') && !channelName.includes('2') && !channelName.includes('3')) {
                indicatorClass = 'shopee-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 2') || channelName.includes('shopee2')) {
                indicatorClass = 'shopee-2-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('shopee 3') || channelName.includes('shopee3')) {
                indicatorClass = 'shopee-3-indicator';
                logoClass = 'fa-shopping-bag';
            } else if (channelName.includes('lazada')) {
                indicatorClass = 'lazada-indicator';
                logoClass = 'fa-box';
            } else if (channelName.includes('tokopedia')) {
                indicatorClass = 'tokopedia-indicator';
                logoClass = 'fa-store';
            } else if (channelName.includes('tiktok')) {
                indicatorClass = 'tiktok-indicator';
                logoClass = 'fa-music';
            } else if (channelName === 'b2b') {
                indicatorClass = 'b2b-indicator';
                logoClass = 'fa-handshake';
            } else if (channelName === 'crm') {
                indicatorClass = 'crm-indicator';
                logoClass = 'fa-users';
            }
            
            // Calculate fee admin percentage relative to gross revenue
            const feeAdminPercentage = (channel.channel_gross_revenue > 0) 
                ? ((channel.channel_fee_admin / channel.channel_gross_revenue) * 100)
                : 0;
            
            // Create table row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="${indicatorClass}">
                    <span class="channel-icon"><i class="fas ${logoClass}"></i></span>
                    ${channel.channel_name}
                </td>
                <td class="text-right">Rp ${formatNumber(channel.channel_fee_admin)}</td>
                <td class="text-right">${feeAdminPercentage.toFixed(1)}%</td>
            `;
            
            container.appendChild(row);
        });
    }

    // Initialize all tables and load data
    $(document).ready(function() {
        initializeTables();
        fetchSummary();
    });
</script>