<div class="modal fade" id="exportModalXML" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Export Talent Filter</h5>
               <button type="button" class="close" data-dismiss="modal">&times;</button>
           </div>
           <div class="modal-body">
               <form id="exportForm">
                   <div class="form-group">
                        <label>NIK</label>
                        <select id="filterNIK" class="form-select select2" style="width: 100%;" multiple="multiple">
                            @foreach($uniqueNIK as $nik)
                                <option value="{{ $nik }}">{{ $nik }}</option>
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