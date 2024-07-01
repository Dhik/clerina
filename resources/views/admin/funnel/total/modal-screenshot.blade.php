<!-- Modal -->
<div class="modal fade" id="screenshotModal" tabindex="-1" role="dialog" aria-labelledby="screenshotModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="screenshotModalLabel">{{ trans('labels.upload') }} {{ trans('labels.screenshot') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="screenshotUploadForm" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="fileScreenshot">{{ trans('labels.image') }}</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fileScreenshot" name="image" required>
                            <label class="custom-file-label" for="customFile" id="labelFileScreenshot">{{ trans('placeholder.select_image') }}</label>
                        </div>
                    </div>

                    <input type="hidden" name="funnelTotalId" id="funnelTotalId"/>

                    <div class="form-group d-none" id="errorUploadScreenshot"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('labels.upload') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
