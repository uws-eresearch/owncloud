<div class="modal" id="addGrantModal" tabindex="-1" role="dialog" aria-labelledby="addGrantModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="addGrantModalLabel">Add Grant</h4>
      </div>
      <div class="modal-body">
        <p>Number:</p>
        <input id="add-grant-number" type="text" class="modal-input"></input>
        <p>
          <label id="add-grant-number-validation-error" validates="add-creator-name" style="color:red;display:none"></label>
        <p>
        <p>Submit Year:</p>
        <input id="add-grant-year" type="text" class="modal-input"></input>
        <p>
          <label id="add-grant-year-validation-error" validates="add-creator-name" style="color:red;display:none"></label>
        <p>
        <p>Institution:</p>
        <input id="add-grant-institution" type="text" class="modal-input"></input>
        <p>
          <label id="add-grant-institution-validation-error" validates="add-creator-name" style="color:red;display:none"></label>
        <p>
        <p>Title:</p>
        <input id="add-grant-title" type="text" class="modal-input"></input>
        <p>
          <label id="add-grant-title-validation-error" validates="add-creator-name" style="color:red;display:none"></label>
        <p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Add</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->