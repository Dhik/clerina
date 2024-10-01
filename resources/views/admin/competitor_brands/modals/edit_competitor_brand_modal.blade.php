<div class="modal fade" id="editCompetitorBrandModal" tabindex="-1" aria-labelledby="editCompetitorBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCompetitorBrandForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompetitorBrandModalLabel">Edit Competitor Brand</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_brand">Brand</label>
                        <input type="text" name="brand" id="edit_brand" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_keterangan">Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_logo">Logo</label>
                        <input type="file" name="logo" id="edit_logo" class="form-control-file">
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
