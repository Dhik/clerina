
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addPaymentForm" method="POST" action="{{ route('talent_payments.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="talent_id" id="paymentTalentId">

                    <div class="form-group">
                        <label for="status_payment">Status Payment</label>
                        <select name="status_payment" id="status_payment" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="Full Payment">Full Payment</option>
                            <option value="DP 50%">DP 50%</option>
                            <option value="Pelunasan 50%">Pelunasan 50%</option>
                            <option value="Termin 1">Termin 1</option>
                            <option value="Termin 2">Termin 2</option>
                            <option value="Termin 3">Termin 3</option>
                        </select>
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
