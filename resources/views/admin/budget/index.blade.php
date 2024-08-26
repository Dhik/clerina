@extends('adminlte::page')

@section('title', 'Budgets')

@section('content_header')
    <h1>Budgets</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBudgetModal">
                        Add Budget
                    </button>
                </div>
                <div class="card-body">
                    <table id="budgetTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Budget</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.budget.modals.add_budget_modal')
    @include('admin.budget.modals.edit_budget_modal')
    @include('admin.budget.modals.view_budget_modal')
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        var table = $('#budgetTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('budgets.data') }}',
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'nama_budget', name: 'nama_budget' },
                {
                    data: 'budget',
                    name: 'budget',
                    render: function(data, type, row) {
                        return formatRupiah(data);
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        // Function to format number to Rupiah with thousand separator
        function formatRupiah(angka) {
            var number_string = angka.toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return 'Rp. ' + rupiah;
        }

        // Function to load the budget details and campaigns
        function loadBudgetDetails(budgetId) {
            $.ajax({
                url: "{{ route('budgets.showCampaigns', ':id') }}".replace(':id', budgetId),
                method: 'GET',
                success: function(response) {
                    // Populate the budget details in the modal
                    $('#view_nama_budget').val(response.budget.nama_budget);
                    $('#view_budget').val(formatRupiah(response.budget.budget));
                    $('#view_total_expense_sum').val('Rp. ' + response.totalExpenseSum);

                    // Populate the campaigns table
                    var campaignTableBody = $('#campaignTableBody');
                    campaignTableBody.empty();

                    response.campaigns.forEach(function(campaign) {
                        var row = `
                            <tr>
                                <td>${campaign.id}</td>
                                <td>${campaign.title}</td>
                                <td>${campaign.start_date}</td>
                                <td>${campaign.end_date}</td>
                                <td>${campaign.description}</td>
                                <td>${formatRupiah(campaign.total_expense)}</td>
                            </tr>`;
                        campaignTableBody.append(row);
                    });

                    // Show the modal
                    $('#viewBudgetModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching budget details:', response);
                }
            });
        }

        // Handle Edit button click
        $('#budgetTable').on('click', '.editButton', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route('budgets.edit', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#editBudgetForm').attr('action', '{{ route('budgets.update', ':id') }}'.replace(':id', id));
                    $('#edit_nama_budget').val(response.budget.nama_budget);
                    $('#edit_budget').val(response.budget.budget);

                    $('#editBudgetModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching budget data:', response);
                }
            });
        });

        // Handle View button click
        $('#budgetTable').on('click', '.viewButton', function() {
            var budgetId = $(this).data('id');
            loadBudgetDetails(budgetId);
        });

        // Clear form on modal close
        $('#addBudgetModal, #editBudgetModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('input[name="_method"]').remove();
        });

        // Handle Delete button click
        $('#budgetTable').on('click', '.deleteButton', function() {
            let rowData = table.row($(this).closest('tr')).data();
            let route = '{{ route('budgets.destroy', ':id') }}'.replace(':id', rowData.id);

            deleteAjax(route, rowData.id, table);
        });
    });
</script>
@stop
