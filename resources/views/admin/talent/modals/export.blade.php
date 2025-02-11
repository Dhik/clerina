<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Export Filters</h5>
               <button type="button" class="close" data-dismiss="modal">&times;</button>
           </div>
           <div class="modal-body">
               <form id="exportForm">
                   <div class="form-group">
                       <label>Status Customer</label>
                       <select class="form-control select2" id="exportStatus">
                           <option value="">All Status</option>
                           @foreach($customer as $status)
                               <option value="{{ $status->status_customer }}">{{ $status->status_customer }}</option>
                           @endforeach
                       </select>
                   </div>
                   <div class="form-group">
                       <label>Date Range</label>
                       <input type="month" class="form-control" id="exportMonth">
                   </div>
                   <div class="form-group">
                    <label>Which HP</label>
                        <select class="form-control select2" id="exportWhichHp">
                            <option value="">All HP</option>
                            @foreach($whichHp as $hp)
                                <option value="{{ $hp->which_hp }}">{{ $hp->which_hp }}</option>
                            @endforeach
                        </select>
                    </div>
               </form>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
               <button type="button" class="btn btn-success" id="doExport">
                   <i class="fas fa-file-excel"></i> Export
               </button>
           </div>
       </div>
   </div>
</div>