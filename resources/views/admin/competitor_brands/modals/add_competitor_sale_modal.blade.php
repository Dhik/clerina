<div class="modal fade" id="addCompetitorSaleModal" tabindex="-1" aria-labelledby="addCompetitorSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addCompetitorSaleForm" method="POST" action="{{ route('competitor_sales.store') }}">
                @csrf
                <input type="hidden" name="competitor_brand_id" value="{{ $competitorBrand->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCompetitorSaleModalLabel">Add Competitor Sale</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="channel">Channel</label>
                        <select name="channel" id="channel" class="form-control" required>
                            <option value="" disabled selected>Select a channel</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Tiktok">Tiktok</option>
                            <option value="Twitter">Twitter</option>
                            <option value="Shopee">Shopee</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="omset">Omset</label>
                        <input type="text" name="omset" id="omset" class="form-control money" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="" disabled selected>Select a type</option>
                            <option value="Direct">Direct</option>
                            <option value="Indirect">Indirect</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
