@extends('adminlte::page')

@section('title', trans('labels.marketing'))

@section('content_header')
    <h1>{{ trans('labels.marketing') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                        </div>
                        <div class="col-auto">
                            <select class="form-control" id="filterMarketingType">
                                <option value="" selected>{{ trans('placeholder.select_type') }}</option>
                                <option value="">{{ trans('labels.all') }}</option>
                                @foreach($marketingTypes as $marketingType)
                                    <option value={{ $marketingType }}>{{ ucfirst($marketingType) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <select class="form-control" id="filterCategory">
                                <option value="" selected>{{ trans('placeholder.select_category') }}</option>
                                <option value="">{{ trans('labels.all') }}</option>
                                @foreach($brandingCategories as $brandingCategory)
                                    <option value={{ $brandingCategory->id }}>{{ $brandingCategory->name }}</option>
                                @endforeach
                                @foreach($marketingCategories as $marketingCategory)
                                    <option value={{ $marketingCategory->id }}>{{ $marketingCategory->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <select class="form-control" id="filterSubCategory">
                                <option value="" selected>{{ trans('placeholder.select_sub_category') }}</option>
                                <option value="">{{ trans('labels.all') }}</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4 id="newMarketingExpense">0</h4>
                            <p>{{ trans('labels.marketing_expense') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4 id="newBrandingExpense">0</h4>
                            <p>{{ trans('labels.branding_expense') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <canvas id="marketingChart" width="800" height="400"></canvas>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <canvas id="marketingPieChart" width="800" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#brandingModal" id="btnAddBranding">
                            <i class="fas fa-plus"></i> {{ trans('labels.branding') }}
                        </button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#marketingModal" id="btnAddMarketing">
                            <i class="fas fa-plus"></i> {{ trans('labels.marketing') }}
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#marketingExportModal">
                            <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                        </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#marketingImportModal">
                            <i class="fas fa-file-upload"></i> {{ trans('labels.import') }}
                        </button>
                    </div>
                </div>
                </div>
                <div class="card-body">
                    <table id="marketingTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="marketing-info" width="100%">
                        <thead>
                            <tr>
                                <th width="10%">{{ trans('labels.date') }}</th>
                                <th width="10%">{{ trans('labels.type') }}</th>
                                <th width="15%">{{ trans('labels.category') }}</th>
                                <th width="15%">{{ trans('labels.sub_category') }}</th>
                                <th width="40%">{{ trans('labels.amount') }} ({{ trans('labels.rp') }})</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.marketing.modal-branding')
    @include('admin.marketing.modal-branding-update')
    @include('admin.marketing.modal-marketing')
    @include('admin.marketing.modal-marketing-update')
    @include('admin.marketing.modal-export')
    @include('admin.marketing.modal-import')
@stop

@section('js')
    <script>
        $(function () {
            marketingTable.draw();
            updateRecapCount();
            bsCustomFileInput.init();
        });

        $('#btnAddMarketing').click(function() {
            $('#dateMarketing').val(moment().format("DD/MM/YYYY"));
        });

        $('#btnAddBranding').click(function() {
            $('#dateBranding').val(moment().format("DD/MM/YYYY"));
        });

        let marketingChart;
        let marketingPieChart;

        const marketingTableSelector = $('#marketingTable');
        const errorSubmitBranding = $('#errorSubmitBranding');
        const errorSubmitMarketing = $('#errorSubmitMarketing');

        let categories = @json($marketingCategories);
        const marketingSubCategories = Object.values(categories).flatMap(item => item.marketing_sub_categories);

        $.each(marketingSubCategories, function(index, subCategory) {
            $('#filterSubCategory').append($('<option>', {
                value: subCategory.id,
                text: subCategory.name
            }));
        });

        $('#categoryMarketing').change(function () {
            populateSubCategory($('#subCategoryMarketing'), $(this).val())
        });

        $('#categoryUpdateMarketing').change(function () {
            populateSubCategory($('#subCategoryUpdateMarketing'),  $(this).val())
        });

        function populateSubCategory(selector, categoryId)
        {
            selector.empty();
            selector.append($('<option>', {
                value: '',
                text: "{{ trans('placeholder.select_sub_category') }}"
            }));

            if (categoryId !== '') {
                let selectedCategory = null;

                for (let key in categories) {
                    if (categories.hasOwnProperty(key)) {
                        let obj = categories[key];

                        if (obj.id === parseInt(categoryId)) {
                            selectedCategory = obj;
                            break;
                        }
                    }
                }

                if (selectedCategory.marketing_sub_categories.length > 0) {
                    $.each(selectedCategory.marketing_sub_categories, function(index, subCategory) {
                        selector.append($('<option>', {
                            value: subCategory.id,
                            text: subCategory.name
                        }));
                    });
                } else {
                    selector.empty();
                    selector.append($('<option>', {
                        value: '',
                        text: "{{ trans('placeholder.sub_category_empty') }}"
                    }));
                }
            }
        }

        const filterDate = $('#filterDates');

        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            $('#filterMarketingType').val('')
            $('#filterCategory').val('')
            $('#filterSubCategory').val('')
            marketingTable.draw()
            updateRecapCount()
        })

        filterDate.change(function () {
            marketingTable.draw()
            updateRecapCount()
        });

        $('#filterMarketingType').change(function () {
            marketingTable.draw()
        });

        $('#filterCategory').change(function () {
            marketingTable.draw()
        });

        $('#filterSubCategory').change(function () {
            marketingTable.draw()
        });

        // datatable
        let marketingTable = marketingTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            search: false,
            searching: false,
            lengthChange: false,
            ajax: {
                url: "{{ route('marketing.get') }}",
                data: function (d) {
                    d.filterDates = $('#filterDates').val()
                    d.filterMarketingType = $('#filterMarketingType').val()
                    d.filterCategory = $('#filterCategory').val()
                    d.filterSubCategory = $('#filterSubCategory').val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'type_transform', name: 'type', sortable: false},
                {data: 'marketingCategory', name: 'marketingCategory', sortable: false},
                {data: 'marketingSubCategory', name: 'marketingSubCategory', sortable: false},
                {data: 'amountFormatted', name:'amount'},
                {data: 'actions', sortable: false}
            ],
            columnDefs: [
                { "targets": [4], "className": "text-right" },
                { "targets": [5], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        // submit form
        $('#brandingForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: "{{ route('marketing.branding.store') }}",
                data: formData,
                success: function(response) {
                    errorSubmitBranding.addClass('d-none');
                    marketingTable.ajax.reload();
                    $('#brandingForm')[0].reset();
                    $('#brandingModal').modal('hide');
                    updateRecapCount();
                    toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.marketing')]) }}');
                },
                error: function(xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorSubmitBranding'));
                }
            });
        });

        // submit form
        $('#marketingForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: "{{ route('marketing.marketing.store') }}",
                data: formData,
                success: function(response) {
                    errorSubmitMarketing.addClass('d-none');
                    marketingTable.ajax.reload();
                    $('#marketingForm')[0].reset();
                    $('#marketingModal').modal('hide');
                    updateRecapCount();
                    toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.marketing')]) }}');
                },
                error: function(xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorSubmitMarketing'));
                }
            });
        });

        // Handle row click event to open modal and fill form
        marketingTableSelector.on('draw.dt', function() {
            const tableBodySelector =  $('#marketingTable tbody');

            tableBodySelector.on('click', '.updateButton', function() {
                let rowData = marketingTable.row($(this).closest('tr')).data();

                let dateObject = moment(rowData.date, "DD MMM YYYY");
                let formattedDate = dateObject.format("DD/MM/YYYY");

                if (rowData.type === 'branding')  {
                    if (rowData.marketing_category_id !== null) {
                        $('#categoryUpdateBranding').val(rowData.marketing_category_id)
                    }
                    $('#dateUpdateBranding').val(formattedDate);
                    $('#amountUpdateBranding').val(rowData.amount);
                    $('#brandingId').val(rowData.id);
                    $('#brandingUpdateModal').modal('show');
                }

                if (rowData.type === 'marketing') {
                    if (rowData.marketing_category_id !== null) {
                        $('#categoryUpdateMarketing').val(rowData.marketing_category_id)
                    }
                    $('#dateUpdateMarketing').val(formattedDate);
                    $('#amountUpdateMarketing').val(rowData.amount);
                    $('#categoryUpdateMarketing').trigger('change');
                    $('#subCategoryUpdateMarketing').val(rowData.marketing_sub_category_id)
                    $('#marketingId').val(rowData.id);
                    $('#marketingUpdateModal').modal('show');
                }
            });

            tableBodySelector.on('click', '.deleteButton', function() {
                let rowData = marketingTable.row($(this).closest('tr')).data();
                let route = '{{ route('marketing.destroy', ':id') }}';
                deleteAjax(route, rowData.id, marketingTable);
            });
        });

        // submit branding update form
        $('#brandingUpdateForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let brandingId = $('#brandingId').val();

            let updateUrl = '{{ route('marketing.branding.update', ':brandingId') }}';
            updateUrl = updateUrl.replace(':brandingId', brandingId);

            $.ajax({
                type: 'PUT',
                url: updateUrl,
                data: formData,
                success: function(response) {
                    marketingTable.ajax.reload();
                    $('#brandingUpdateModal').modal('hide');
                    updateRecapCount();
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.branding')]) }}');
                },
                error: function(xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorSubmitUpdateBranding'))
                }
            });
        });

        // submit branding update form
        $('#marketingUpdateForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let marketingId = $('#marketingId').val();

            let updateUrl = '{{ route('marketing.marketing.update', ':marketingId') }}';
            updateUrl = updateUrl.replace(':marketingId', marketingId);

            $.ajax({
                type: 'PUT',
                url: updateUrl,
                data: formData,
                success: function(response) {
                    marketingTable.ajax.reload();
                    $('#marketingUpdateModal').modal('hide');
                    updateRecapCount();
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.marketing')]) }}');
                },
                error: function(xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorSubmitUpdateMarketing'))
                }
            });
        });

        function updateRecapCount() {
            $.ajax({
                url: '{{ route('marketing.get-marketing-recap') }}?filterDates=' + filterDate.val(), // Endpoint URL to fetch updated count
                method: 'GET', // You can use 'POST' if required
                success: function(response) {
                    // Update the count with the retrieved value
                    $('#newMarketingExpense').text(response.marketing_expense);
                    $('#newBrandingExpense').text(response.branding_expense);

                    // Extracting data
                    const marketingData = response.marketing;
                    const dates = marketingData.map(data => data.date);
                    const totalBrandingData = marketingData.map(data => data.total_branding);
                    const totalMarketingData = marketingData.map(data => data.total_marketing);

                    // Clear existing chart if it exists
                    if (marketingChart) {
                        marketingChart.destroy();
                    }

                    // Create a bar chart
                    const ctx = document.getElementById('marketingChart').getContext('2d');
                    marketingChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Total Branding',
                                data: totalBrandingData,
                                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }, {
                                label: 'Total Marketing',
                                data: totalMarketingData,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            tooltips: {
                                enabled: true, // Always display tooltips
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
                                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                            } else {
                                                return value;
                                            }
                                        }
                                    }
                                }]
                            }
                        }
                    });

                    // Clear existing chart if it exists
                    if (marketingPieChart) {
                        marketingPieChart.destroy();
                    }

                    const pieData = response.pie_chart;
                    const labels = Object.keys(pieData);
                    const values = Object.values(pieData);

                    // Get the canvas element
                    const ctxPie = document.getElementById('marketingPieChart').getContext('2d');

                    // Create the pie chart
                    marketingPieChart = new Chart(ctxPie, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Marketing vs Branding',
                                data: values,
                                backgroundColor: generatePredefinedColors(labels.length),
                                borderColor: generatePredefinedColors(labels.length),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            title: {
                                display: true,
                                text: 'Marketing vs Branding'
                            },
                            legend: {
                                position: 'right'
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        let dataset = data.datasets[tooltipItem.datasetIndex];
                                        let value = dataset.data[tooltipItem.index];
                                        return data.labels[tooltipItem.index] + ': ' + value + '%';
                                    }
                                }
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching new orders count:', error);
                }
            });
        }
    </script>

    @include('admin.marketing.script-export')
    @include('admin.marketing.script-import')
@stop
