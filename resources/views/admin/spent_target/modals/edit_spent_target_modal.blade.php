<div class="modal fade" id="editSpentTargetModal" tabindex="-1" aria-labelledby="editSpentTargetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editSpentTargetForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editSpentTargetModalLabel">Edit Spent Target</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_budget">Budget</label>
                        <input type="text" name="budget" id="edit_budget" class="form-control money">
                    </div>
                    <div class="form-group">
                        <label for="edit_kol_percentage">KOL Percentage</label>
                        <input type="number" name="kol_percentage" id="edit_kol_percentage" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_ads_percentage">Ads Percentage</label>
                        <input type="number" name="ads_percentage" id="edit_ads_percentage" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_creative_percentage">Creative Percentage</label>
                        <input type="number" name="creative_percentage" id="edit_creative_percentage" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_activation_percentage">Activation Percentage</label>
                        <input type="number" name="activation_percentage" id="edit_activation_percentage" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_free_product_percentage">Free Product Percentage</label>
                        <input type="number" name="free_product_percentage" id="edit_free_product_percentage" class="form-control" required>
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
