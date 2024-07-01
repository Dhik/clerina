<!-- Modal -->
<div class="modal fade" id="marketingImportModal" tabindex="-1" role="dialog" aria-labelledby="marketingImportModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketingExportModalLabel">{{ trans('labels.import') }} {{ trans('labels.marketing') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <a href="{{ route('marketing.download-template') }}">
                        {{ trans('labels.download_template') }} <i class="fas fa-download"></i>
                    </a>
                </div>
                <form id="marketingImportForm" action="{{ route('marketing.import') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fileMarketingImport" name="fileMarketingImport" required>
                            <label class="custom-file-label" for="customFile" id="labelUploadImport">{{ trans('placeholder.select_file') }}</label>
                        </div>
                    </div>

                    <div class="form-group d-none" id="errorImportMarketing"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('labels.import') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
