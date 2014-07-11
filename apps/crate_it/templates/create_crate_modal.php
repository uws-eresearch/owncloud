<div class="modal" id="createCrateModal" tabindex="-1" role="dialog" aria-labelledby="createCrateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="createCrateModalLabel">New Crate</h4>
      </div>
      <div class="modal-body">
      	<p>
        New Crate Name *
        </p>	
        <input id="crate_input_name" name="New Crate Name" type="text" class="modal-input"></input>
        <p/>
        <label id="crate_name_validation_error" validates="New Crate Name" style="color:red;display:none"></label>
        
        <p>
        New Crate Description
        </p>
          <textarea id="crate_input_description" name="New Crate Description" maxlength="8001" class="modal-input"></textarea>
        <p/>
        <label id="crate_description_validation_error" validates="New Crate Description" style="color:red;display:none"></label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button id="create_crate_submit" type="button" class="btn btn-primary" disabled>Create</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->