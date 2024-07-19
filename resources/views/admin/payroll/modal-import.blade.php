<!-- Modal -->
<div class="modal fade" id="payrollImportModal" tabindex="-1" role="dialog" aria-labelledby="payrollImportModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payrollImportModalLabel">{{ trans('labels.import') }} {{ trans('labels.payroll') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="payrollImportForm" action="{{ route('payrolls.import') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="filePayrollImport">{{ trans('labels.file') }}</label>
                        <div class="custom-file">
                            <input type="file" name="file" id="filePayrollImport" class="form-control" required>
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
