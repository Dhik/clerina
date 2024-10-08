<div class="modal fade" id="editTalentModal" tabindex="-1" aria-labelledby="editTalentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTalentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editTalentModalLabel">Edit Talent</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_talent_name">Talent Name</label>
                        <input type="text" name="talent_name" id="edit_talent_name" class="form-control" required>
                    </div>
                    <!-- Add more fields as needed -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
