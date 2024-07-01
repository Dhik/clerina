<!-- Modal -->
<div class="modal fade" id="orderImportModal" tabindex="-1" role="dialog" aria-labelledby="orderImportModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderExportModalLabel">{{ trans('labels.import') }} {{ trans('labels.order') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <a href="{{ route('order.download-template') }}">
                        {{ trans('labels.download_template') }} <i class="fas fa-download"></i>
                    </a>
                </div>
                <form id="orderImportForm" action="{{ route('order.import') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="salesChannelId">{{ trans('labels.sales_channel') }}</label>
                        <select type="text" class="form-control" id="salesChannelIdImport" name="sales_channel_id" required>
                            <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                            @foreach($salesChannels as $salesChannel)
                                <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fileOrderImport">{{ trans('labels.file') }}</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fileOrderImport" name="fileOrderImport" required>
                            <label class="custom-file-label" for="customFile" id="labelUploadImport">{{ trans('placeholder.select_file') }}</label>
                        </div>
                    </div>

                    <div class="form-group d-none" id="errorImportOrder"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('labels.import') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
