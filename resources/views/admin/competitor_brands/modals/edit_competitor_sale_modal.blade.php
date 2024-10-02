<div class="modal fade" id="editCompetitorSaleModal" tabindex="-1" aria-labelledby="editCompetitorSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCompetitorSaleForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="competitor_brand_id" id="edit_competitor_brand_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompetitorSaleModalLabel">Edit Competitor Sale</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_channel">Channel</label>
                        <select name="channel" id="edit_channel" class="form-control" required>
                            <option value="" disabled selected>Select a channel</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Tiktok">Tiktok</option>
                            <option value="Twitter">Twitter</option>
                            <option value="Shopee">Shopee</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_omset">Omset</label>
                        <input type="text" name="omset" id="edit_omset" class="form-control money" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_date">Date</label>
                        <input type="date" name="date" id="edit_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_type">Type</label>
                        <select name="type" id="edit_type" class="form-control" required>
                            <option value="" disabled selected>Select a type</option>
                            <option value="Direct">Direct</option>
                            <option value="Indirect">Indirect</option>
                        </select>
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
