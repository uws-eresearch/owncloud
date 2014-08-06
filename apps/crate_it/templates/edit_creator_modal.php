<div class="modal" id="editCreatorsModal" tabindex="-1" role="dialog" aria-labelledby="editCreatorsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="editCreatorsModalLabel">Edit Creator</h4>
      </div>
      <div>
      </div>
      <div class="modal-body">
        <div id="original-creators">
          <p>Orignal Name</p>
          <input id="original-creators-name" type="text"  class="modal-input" readonly></input>
          <p>Original Email</p>
          <input id="original-creators-email" type="text" class="modal-input" readonly></input>
        </div>
        <input id="edit-creators-record" type="hidden"></input>
        <p>Name</p>
        <input id="edit-creators-name" type="text" class="modal-input"></input>
        <p>
          <label style="color:red;display:none"></label>
        <p>
        <p>Email</p>
        <input id="edit-creators-email" type="text" class="modal-input"></input>
        <p>
          <label style="color:red;display:none"></label>
        <p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->