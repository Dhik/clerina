<div class="modal fade" id="addSpentTargetModal" tabindex="-1" aria-labelledby="addSpentTargetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addSpentTargetForm" method="POST" action="{{ route('spentTarget.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addSpentTargetModalLabel">Add Spent Target</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="budget">Budget</label>
                        <input type="text" name="budget" id="budget" class="form-control money" required>
                    </div>
                    <div class="form-group">
                        <label for="kol_percentage">KOL Percentage</label>
                        <input type="number" name="kol_percentage" id="kol_percentage" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="ads_percentage">Ads Percentage</label>
                        <input type="number" name="ads_percentage" id="ads_percentage" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="creative_percentage">Creative Percentage</label>
                        <input type="number" name="creative_percentage" id="creative_percentage" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="activation_percentage">Activation Percentage</label>
                        <input type="number" name="activation_percentage" id="activation_percentage" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="free_product_percentage">Free Product Percentage</label>
                        <input type="number" name="free_product_percentage" id="free_product_percentage" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="month">Month</label>
                        <input type="text" name="month" id="month" class="form-control month-picker" placeholder="MM/YYYY" required>
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
