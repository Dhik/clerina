<div class="modal fade" id="exportForm" tabindex="-1" role="dialog" aria-labelledby="exportFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportFormLabel">Export Talent Content</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="exportTalentContentForm" action="{{ route('talent_content.pengajuan') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="exportDateRange">Date Range</label>
                        <input type="text" class="form-control rangeDate" id="exportDateRange" name="date" autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Export
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>