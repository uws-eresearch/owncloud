
function indentTree() {
  $tree.find('.jqtree-element').each(function() {
    var indent = $(this).parents('li').length * 20;
    $(this).css('padding-left', indent);
    $(this).css('background-position', indent + 20 + 'px 50%');
  });
}


function saveTree(successMessage, errorMessage, reload) {
  if (typeof(successMessage) === 'undefined') {
    successMessage = 'Crate updated';
  }
  if (typeof(errorMessage) === 'undefined') {
    errorMessage = 'Crate not updated';
  }
  $.ajax({
    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
    type: 'post',
    dataType: 'html',
    data: {
      'action': 'update_vfs',
      'vfs': $tree.tree('toJson')
    },
    success: function(data) {
      if (successMessage) {
        displayNotification(successMessage);
        updateCrateSize();
      }
      if (reload) {
        location.reload();
      }
    },
    error: function(data) {
      if (errorMessage) {
        displayError(errorMessage);
      }
      if (reload) {
        location.reload();
      }
    }
  });
}

function treeHasNoFiles() {
  var children = $tree.tree('getNodeById', 'rootfolder').children;
  return children.length == 0;
}

