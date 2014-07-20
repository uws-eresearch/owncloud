
function expandRoot() {
  var rootnode = $tree.tree('getNodeById', 'rootfolder'); // NOTE: also see getTree
  $tree.tree('openNode', rootnode);
}


function drawCrateContents() {
    $.ajax({
        url:'crate/items',
        type: 'get',
        dataType: 'json',
        success: function(data) {
          alert(data);
          $tree = buildFileTree(data);
          indentTree();
        },
        error: function(data) {
          // TODO: why alert? It's overwritten anyway
          var e = data.statusText;
          alert(e);
        }
    });
}

function buildFileTree(data) {

  var createImgUrl = function(node) {
    var icon_set = ['application-epub+zip', 'application-pdf', 'application-rss+xml',
      'application', 'audio', 'calendar', 'database', 'file', 'flash',
      'folder-drag-accept', 'folder-external', 'folder-public', 'folder-shared',
      'folder', 'font', 'image', 'image-svg+xml', 'package-x-generic', 'text-code',
      'text-html', 'text', 'text-vcard', 'text-x-c', 'text-x-h', 'text-x-javascript',
      'text-x-python', 'video', 'web', 'x-office-document',
      'x-office-presentation', 'x-office-spreadsheet'
    ];
    var icon = 'file';
    if(node.id == 'rootfolder') {
      return 'url('+ OC.filePath('crate_it', 'img', 'crate.png') + ')';
    } else if (node.folder || node.id == 'folder') {
      icon = 'folder';
    } else {
      var mime_base = node.mime.split('/')[0];
      var mime = node.mime.replace(/\//, '-');
      if ($.inArray(mime, icon_set) > 0) {
        icon = mime;
      } else if ($.inArray(mime_base, icon_set) > 0) {
        icon = mime_base;
      }
    }
    return 'url(' + OC.imagePath('core', 'filetypes/' + icon + '.svg') + ')';
  };

  var addFolder = function(node) {
    var $modal = $('#addFolderModal');
    var confirmCallback = function() {
      var folder = $('#add-folder').val();
      $tree.tree('appendNode', {
        id: 'folder',
        label: folder,
      }, node);
      $tree.tree('openNode', node);
      indentTree();
      var successMessage = folder + ' added';
      var errorMessage = folder + 'not added';
      saveTree(successMessage, errorMessage);
    };
    attachModalHandlers($modal, confirmCallback);
  };

  var renameCrate = function(node) {
    var $modal = $('#renameCrateModal');
    var oldName = node.name;
    $('#rename-crate').val(oldName);
    $('#rename-crate').keyup(function() {
      var $input = $('#rename-crate');
      var $error = $('#rename_crate_error');
      var $confirm = $modal.find('.btn-primary');
      validateCrateName($input, $error, $confirm);
    });

    var confirmCallback = function() {
      var newName = $('#rename-crate').val();
      $tree.tree('updateNode', node, newName);
      var vfs = $tree.tree('toJson');
      $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {
          'action': 'rename_crate',
          'new_name': newName,
          'vfs': vfs
        },
        success: function() {
          $('#crates > #' + oldName).val(newName).attr('id', newName).text(newName);
          var successMessage = 'Renamed ' + oldName + ' to ' + newName;
          var errorMessage = oldName + ' not renamed';
          saveTree(successMessage, errorMessage, true);
          
        },
        error: function(data) {
          $tree.tree('updateNode', node, oldName);
          displayError(oldName + ' not renamed');
          location.reload();
        }
      });
    };
   // the successMessage function gets called after the name has changed
    attachModalHandlers($modal, confirmCallback);
  };

  var renameItem = function(node) {
    var $modal = $('#renameItemModal');
    var oldName = node.name; // the successMessage function gets called after the name has changed
    $('#rename-item').val(node.name);
    var confirmCallback = function() {
      var newName = $('#rename-item').val();
      $tree.tree('updateNode', node, newName);
      indentTree();
      var successMessage = 'Renamed ' + oldName + ' to ' + newName;
      var errorMessage = 'error renaming' +  oldName;
      saveTree(successMessage, errorMessage);
    };
    attachModalHandlers($modal, confirmCallback);
  };

  var removeItem = function(node) {
    var $modal = $('#removeCrateModal');
    var msg = "Remove item '" + node.name + "' from crate?";
    $modal.find('.modal-body > p').text(msg);
    var confirmCallback = function() {
      $tree.tree('removeNode', node);
      indentTree();
      var successMessage = node.name + ' removed';
      var errorMessage = node.name + ' not removed';
      saveTree(successMessage, errorMessage);
    };
    attachModalHandlers($modal, confirmCallback);
  };

  $tree = $('#files').tree({
    data: data.vfs,
    autoOpen: false,
    dragAndDrop: true,
    saveState: false,
    selectable: false,
    useContextMenu: false,
    onCreateLi: function(node, $li) {
      $div = $li.find('.jqtree-element');
      $div.css('background-image', createImgUrl(node));
      $ul = $div.append('<ul class="crate-actions pull-right"></ul>').find('ul');
      var type = node.id;
      if (type == 'rootfolder' || type == 'folder') {
        $ul.append('<li><a><i class="fa fa-plus"></i>Add Folder Item</a></li>');
        $ul.find('.fa-plus').parent().click(function() {
          addFolder(node);
        });
      }
      if (type == 'rootfolder') {
        $div.addClass('rootfolder');
        $ul.append('<li><a><i class="fa fa-pencil"></i>Rename Crate</a></li>');  
      } else {
        $ul.append('<li><a><i class="fa fa-pencil"></i>Rename Item</a></li>');
      }
      $ul.find('.fa-pencil').parent().click(function() {
        if (type == 'rootfolder') {
          renameCrate(node);
        } else {
          renameItem(node);
        }
      });
      if (type != 'rootfolder') {
        $ul.append('<li><a><i class="fa fa-trash-o"></i>Remove Item</a></li>');
        $ul.find('.fa-trash-o').parent().click(function() {
          removeItem(node);
        });
      }
    },
    onCanMove: function(node) {
      var result = true;
      // Cannot move root node
      if (!node.parent.parent) {
        result = false;
      }
      return result;
    },
    onCanMoveTo: function(moved_node, target_node, position) {
      // Can move before or after any node.
      // Can only move INSIDE of a node whose id ends with 'folder' 
      // console.log(target_node.id);
      if (target_node.id.indexOf('folder', target_node.id.length - 'folder'.length) == -1) {
        return (position != 'inside');
      } else if (target_node.id == 'rootfolder') {
        return (position != 'before' && position != 'after');
      } else {
        return true;
      }
    },
  });

  $tree.bind('tree.open', function(event) {
    saveTree(false);
  });

  $tree.bind('tree.close', function(event) {
    saveTree(false);
  });

  $tree.bind('tree.move', function(event) {
    event.preventDefault();
    // do the move first, and _then_ POST back.
    event.move_info.do_move();
    var msg = 'Item ' + event.move_info.moved_node.name + ' moved';
    saveTree(msg);
    indentTree();
  });

  expandRoot();

  return $tree;
}


function expandRoot() {
  var rootnode = $tree.tree('getNodeById', 'rootfolder'); // NOTE: also see getTree
  $tree.tree('openNode', rootnode);
}

