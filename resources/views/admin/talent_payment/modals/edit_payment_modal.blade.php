<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentModalLabel">Edit Talent Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPaymentForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editPaymentId" name="id">
                    <div class="form-group">
                        <label for="editUsername">Username</label>
                        <input type="text" class="form-control" id="editUsername" readonly>
                    </div>
                    <div class="form-group">
                        <label for="editDonePayment">Done Payment</label>
                        <input type="date" class="form-control" id="editDonePayment" name="done_payment">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

