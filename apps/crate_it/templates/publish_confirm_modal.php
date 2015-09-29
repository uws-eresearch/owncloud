<div class="modal" id="publishConfirmModal" tabindex="-1" role="dialog" aria-labelledby="publishConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="publishConfirmModalLabel">Submit Confirmation</h4>
      </div>
      <div class="modal-body">
        <h4>Status</h4>
        <span id="publish-confirm-status"></span>
        <h4>Email Status</h4>
        <p>Enter an email address to send the submit log to</p>
        <label for="publish-confirm-email" class="element-invisible">Email address</label>
        <input id="publish-confirm-email" type="text" class="modal-input"></input>
        <p>
          <label style="color:red;display:none"></label>
        <p>
        <button id="publish-confirm-email-send" type="button" class="btn btn-primary" disabled>Send</button>
        <p>
          <span id="publish-confirm-email-status"></span>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
