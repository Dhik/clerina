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
