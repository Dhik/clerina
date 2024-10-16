<div class="modal fade" id="chooseApprovalModal" tabindex="-1" role="dialog" aria-labelledby="chooseApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chooseApprovalModalLabel">Choose Approval for Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="exportInvoiceId">
                <div class="form-group">
                    <label for="approvalSelect">Select Approval:</label>
                    <select class="form-control" id="approvalSelect">
                        <option value="">Loading approvals...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmExport">Export Invoice</button>
            </div>
        </div>
    </div>
</div>