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

    <!-- Add Budget Modal -->
    <div class="modal fade" id="addBudgetModal" tabindex="-1" aria-labelledby="addBudgetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addBudgetForm" method="POST" action="{{ route('budgets.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBudgetModalLabel">Add Budget</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="add_nama_budget">Nama Budget</label>
                            <input type="text" name="nama_budget" id="add_nama_budget" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="add_budget">Budget</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp.</span>
                                </div>
                                <input type="number" name="budget" id="add_budget" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Budget Modal -->
    <div class="modal fade" id="editBudgetModal" tabindex="-1" aria-labelledby="editBudgetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editBudgetForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBudgetModalLabel">Edit Budget</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_nama_budget">Nama Budget</label>
                            <input type="text" name="nama_budget" id="edit_nama_budget" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_budget">Budget</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp.</span>
                                </div>
                                <input type="number" name="budget" id="edit_budget" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Budget Modal -->
    <div class="modal fade" id="viewBudgetModal" tabindex="-1" aria-labelledby="viewBudgetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBudgetModalLabel">View Budget</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="view_nama_budget">Nama Budget</label>
                        <input type="text" id="view_nama_budget" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="view_budget">Budget</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="text" id="view_budget" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="view_total_expense_sum">Total Expense (Sum)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="text" id="view_total_expense_sum" class="form-control" readonly>
                        </div>
                    </div>

                    <h5>Campaigns</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Description</th>
                                <th>Total Expense</th>
                            </tr>
                        </thead>
                        <tbody id="campaignTableBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#budgetTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('budgets.data') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'nama_budget', name: 'nama_budget' },
                {
                    data: 'budget',
                    name: 'budget',
                    render: function(data, type, row) {
                        return formatRupiah(data);
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
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
            var id = $(this).data('id');
            
            $.ajax({
                url: '{{ route('budgets.showCampaigns', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#view_nama_budget').val(response.budget.nama_budget);
                    $('#view_budget').val(formatRupiah(response.budget.budget));
                    $('#view_total_expense_sum').val('Rp. ' + response.totalExpenseSum);

                    var campaignTableBody = $('#campaignTableBody');
                    campaignTableBody.empty();

                    response.campaigns.forEach(function(campaign) {
                        var row = '<tr>' +
                            '<td>' + campaign.id + '</td>' +
                            '<td>' + campaign.title + '</td>' +
                            '<td>' + campaign.start_date + '</td>' +
                            '<td>' + campaign.end_date + '</td>' +
                            '<td>' + campaign.description + '</td>' +
                            '<td>' + formatRupiah(campaign.total_expense) + '</td>' +
                            '</tr>';
                        campaignTableBody.append(row);
                    });

                    $('#viewBudgetModal').modal('show');
                }
            });
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
