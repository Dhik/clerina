<div class="modal fade" id="importTalentModal" tabindex="-1" aria-labelledby="importTalentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderExportModalLabel">{{ trans('labels.import') }} Talents</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <a href="{{ route('talent.downloadTemplate') }}">
                        {{ trans('labels.download_template') }} <i class="fas fa-download"></i>
                    </a>
                </div>
                <form id="importTalentForm" method="POST" action="{{ route('talent.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Choose Excel File</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
