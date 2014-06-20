<div class="modal" id="newCrateModal" tabindex="-1" role="dialog" aria-labelledby="newCrateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="newCrateModalLabel">New Crate</h4>
      </div>
      <div class="modal-body">
      	<p>
        New Cr8 Name *
        </p>	
        <input id="crate_input_name" name="New Cr8 Name" type="text" class="modal-input"></input>
        <p/>
        <label id="crate_name_validation_error" validates="New Cr8 Name" style="color:red;display:none"></label>
        
        <p>
        New Cr8 Description
        </p>
          <textarea id="crate_input_description" name = "New Cr8 Description" maxlength="8001" class="modal-input"></textarea>
        <p/>
        <label id="crate_description_validation_error" validates="New Cr8 Description" style="color:red;display:none"></label>
        
        <p></p>
        <label id="create_crate_error" name = "Error Message" style="display:none"></label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button id="create_crate_submit" type="button" class="btn btn-primary">Create</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->