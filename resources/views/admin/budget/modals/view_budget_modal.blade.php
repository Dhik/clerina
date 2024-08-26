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
