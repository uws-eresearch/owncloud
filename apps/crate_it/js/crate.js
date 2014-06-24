/**
 * ownCloud - Cr8it App
 *
 * @author Lloyd Harischandra
 * @copyright 2014 University of Western Sydney www.uws.edu.au
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
alert = function() {};

function displayError(errorMessage) {
  displayNotification('There was an error: ' + errorMessage);
}


function displayNotification(message, delayTime) {
  if (typeof(delayTime) === 'undefined') {
    delayTime = 3000;
  }
  OC.Notification.show(message);
  setTimeout(function() {
    OC.Notification.hide();
  }, delayTime);
}


function indentTree($tree) {
  $tree.find('.jqtree-element').each(function() {
    var indent = $(this).parents('li').length * 20;
    $(this).css('padding-left', indent);
    $(this).css('background-position', indent + 20 + 'px 50%');
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
    var icon = 'file'
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

  var attachModalHandlers = function($modal, confirmCallback, successMessage) {
    var $confirm = $modal.find('.btn-primary');

    var clearInput = function() {
      var $input = $modal.find('input');
      if ($input) {
        $input.val('');
      }
    };

    $confirm.click(function() {
      confirmCallback();
      if (typeof(successMessage) == 'function') {
        successMessage = successMessage();
      }
      // removeHandlers();
      saveTree($tree, successMessage);
      indentTree($tree);
      $modal.modal('hide');
    });

    $modal.on('hide.bs.modal', function() {
      $confirm.off('click');
      clearInput();
    });

    $modal.modal('show');
  };

  var addFolder = function(node) {
    var $modal = $('#addFolderModal');
    var confirmCallback = function() {
      $tree.tree('appendNode', {
        id: 'folder',
        label: $('#add-folder').val(),
      }, node);
      $tree.tree('openNode', node);
    };
    var successMessage = function() {
      return $('#add-folder').val() + ' added';
    };
    attachModalHandlers($modal, confirmCallback, successMessage);
  };

  var renameCrate = function(node) {
    var $modal = $('#renameCrateModal');
    var oldName = node.name;
    var crates = $.map($('#crates > option'), function(el, i) {
      return $(el).attr('id');
    });
    $('#rename-crate').val(oldName);
    $('#rename-crate').keyup(function() {
      var inputName = $('#rename-crate').val();
      var $confirm = $modal.find('.btn-primary');
      var $error = $('#rename_crate_error');
      var emptyName = function() {
        return (!inputName || /^\s*$/.test(inputName));
      };
      var existingName = function() {
        return crates.indexOf(inputName) > -1;
      };
      if(existingName() || emptyName()) {
        $confirm.prop('disabled', true);
        if (emptyName()) {
          $('#rename_crate_error').text('Crate name cannot be blank');
        } else {
          $('#rename_crate_error').text('Crate with name "' + inputName + '" already exists');
        }
        $error.show();
      } else {
        $confirm.prop('disabled', false);
        $error.hide();
      }
    });

    var confirmCallback = function() {
      var newName = $('#rename-crate').val();
      $tree.tree('updateNode', node, newName);
      saveTree($tree, false);
      indentTree($tree);
      $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {
          'action': 'rename_crate',
          'new_name': newName
        },
        success: function() {
          $('#crates > #' + oldName).val(newName).attr('id', newName).text(newName);
          location.reload();
        },
        error: function(data) {
          $tree.tree('updateNode', node, oldName);
          saveTree($tree, false);
          location.reload();
          displayError(data.statusText);
        }
      });
    }
   // the successMessage function gets called after the name has changed
    var successMessage = function() {
      return 'Renamed ' + oldName + ' to ' + $('#rename-crate').val();
    };
    attachModalHandlers($modal, confirmCallback, successMessage);
  }

  var renameItem = function(node) {
    var $modal = $('#renameItemModal');
    $('#rename-item').val(node.name);
    var confirmCallback = function() {
      $tree.tree('updateNode', node, $('#rename-item').val());
    };
    var oldName = node.name; // the successMessage function gets called after the name has changed
    var successMessage = function() {
      return 'Renamed ' + oldName + ' to ' + $('#rename-item').val();
    };
    attachModalHandlers($modal, confirmCallback, successMessage);
  };

  var removeItem = function(node) {
    var $modal = $('#removeCrateModal');
    var msg = "Remove item '" + node.name + "' from crate?";
    $modal.find('.modal-body > p').text(msg);
    var confirmCallback = function() {
      $tree.tree('removeNode', node);
    };
    var successMessage = node.name + ' removed';
    attachModalHandlers($modal, confirmCallback, successMessage);
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
    saveTree($tree, false);
  });

  $tree.bind('tree.close', function(event) {
    saveTree($tree, false);
  });

  $tree.bind('tree.move', function(event) {
    event.preventDefault();
    // do the move first, and _then_ POST back.
    event.move_info.do_move();
    var msg = 'Item ' + event.move_info.moved_node.name + ' moved';
    saveTree($tree, msg);
    indentTree($tree);
  });

  expandRoot();

  return $tree;
}


function updateCrateSize() {
  $.ajax({
    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
    type: 'post',
    dataType: 'json',
    data: {
      'action': 'crate_size'
    },
    success: function(data) {
      $('#crate_size_human').text(data['human']);
      crate_size_mb = data['size'] / (1024 * 1024);
      var msg = null;
      if (max_zip_mb > 0 && crate_size_mb > max_zip_mb) {
        msg = 'WARNING: Crate size exceeds zip file limit: ' + max_zip_mb + ' MB';
        $('#download').attr("disabled", "disabled");
        if (max_sword_mb > 0 && crate_size_mb > max_sword_mb) {
          msg += ', and SWORD limit: ' + max_sword_mb + 'MB';
          $('#post').attr("disabled", "disabled");
        }
        msg += '.';
      } else if (max_sword_mb > 0 && crate_size_mb > max_sword_mb) {
        msg = 'WARNING: Crate size exceeds SWORD limit: ' + max_sword_mb + 'MB.';
        $('#post').attr("disabled", "disabled");
      }
      if (msg) {
        displayNotification(msg, 6000);
      } else {
        $('#post').removeAttr("disabled");
        $('#download').removeAttr("disabled");
      }
    },
    error: function(data) {}
  });
}

// function togglePostCrateToSWORD() {
//     $.ajax({
//         url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
//         type: 'post',
//         dataType: 'json',
//         data: {'action': 'validate_metadata'},
//         success: function(data) {
//       if (data.status == "Success") {
//     $('#post').removeAttr("title");
//     $('#post').removeAttr("disabled");
//       }
//       else {
//     $('#post').attr("title", "You cannot post this crate until metadata(title, description, creator) are all set");
//     $('#post').attr("disabled", "disabled");
//       }    
//         },
//         error: function(data) {
//             OC.Notification.show(data.statusText);
//       hideNotification(3000);
//         }
//     });
// }

// MISC: Disable function until UI is fixed
function togglePostCrateToSWORD() {}

function makeCrateListEditable() {
  $('#crateList .title').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php') + '?action=edit_title', {
    name: 'new_title',
    indicator: '<img src=' + OC.imagePath('crate_it', 'indicator.gif') + '>',
    tooltip: 'Double click to edit...',
    event: 'dblclick',
    style: 'inherit',
    submitdata: function(value, settings) {
      return {
        'elementid': this.parentNode.parentNode.getAttribute('id')
      };
    }
  });
}

function expandRoot() {
  var rootnode = $tree.tree('getNodeById', 'rootfolder'); // NOTE: also see getTree
  $tree.tree('openNode', rootnode);
}


function saveTree($tree, successMessage) {
  if (typeof(successMessage) === 'undefined') {
    successMessage = 'Crate updated';
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
    },
    error: function(data) {
      displayError(data.statusText);
    }
  });
}

function treeHasNoFiles() {
  var children = $tree.tree('getNodeById', 'rootfolder').children;
  return children.length == 0;
}

function removeFORCodes() {
  var first = $('#for_second_level option:first').detach();
  $('#for_second_level').children().remove();
  $('#for_second_level').append(first);
}

function activateRemoveCreatorButton(buttonObj) {
  buttonObj.click('click', function(event) {
    // Remove people from backend
    var id = $(this).attr("id");
    creator_id = id.replace("creator_", "");

    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'remove_people',
        'creator_id': creator_id,
        'full_name': $(this).parent().text()
      },
      success: function(data) {
        buttonObj.parent().remove();
        togglePostCrateToSWORD();
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });
}

function activateRemoveCreatorButtons() {
  $("input[id^='creator_']").click('click', function(event) {
    // Remove people from backend
    var input_element = $(this);
    var id = input_element.attr("id");
    creator_id = id.replace("creator_", "");

    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'remove_people',
        'creator_id': creator_id,
        'full_name': input_element.parent().text()
      },
      success: function(data) {
        input_element.parent().remove();
        togglePostCrateToSWORD();
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });
}

function makeCreatorsEditable() {
  $('#creators .full_name').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php') + '?action=edit_creator', {
    id: 'creator_id',
    name: 'new_full_name',
    indicator: '<img src=' + OC.imagePath('crate_it', 'indicator.gif') + '>',
    tooltip: 'Double click to edit...',
    event: 'dblclick',
    style: 'inherit'
  });
}

function makeCreatorEditable(creatorObj) {
  creatorObj.editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php') + '?action=edit_creator', {
    id: 'creator_id',
    name: 'new_full_name',
    indicator: '<img src=' + OC.imagePath('crate_it', 'indicator.gif') + '>',
    tooltip: 'Double click to edit...',
    event: 'dblclick',
    style: 'inherit'
  });
}

function activateRemoveActivityButton(buttonObj) {
  buttonObj.click('click', function(event) {
    // Remove activity from backend
    var id = $(this).attr("id");
    activity_id = id.replace("activity_", "");

    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'remove_activity',
        'activity_id': activity_id,
      },
      success: function(data) {
        buttonObj.parent().remove();
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });
}

function activateRemoveActivityButtons() {
  $("input[id^='activity_']").click('click', function(event) {
    // Remove activity from backend
    var input_element = $(this);
    var id = input_element.attr("id");
    activity_id = id.replace("activity_", "");

    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'remove_activity',
        'activity_id': activity_id,
      },
      success: function(data) {
        input_element.parent().remove();
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });
}



function initCrateActions() {

  var metadataEmpty = function() {
    var result = false;
    $('.metadata').each(function() {
      if($(this).text() === '') {
        result = true;
      }
    });
    return result;
  }

  var crateEmpty = function() {
    return $tree.tree('getNodeById', 'rootfolder').children.length == 0;
  }

  var deleteCrate = function () {
    var current_crate = $('#crates').val();
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'delete_crate'
      },
      success: function(data) {
        if (data.status == "Success") {
          displayNotification('Crate ' + current_crate + ' deleted')
          location.reload();
        } else {
          displayError(data.msg);
        }
      }
    });
    $('#deleteCrateModal').modal('hide');
  }

  $('#clearCrateModal').find('.btn-primary').click(function() {
    var children = $tree.tree('getNodeById', 'rootfolder').children;
    // NOTE: The while loop is a workaround to the forEach loop inexplicably skipping
    // the first element
    while(children.length > 0) {
      children.forEach(function(node) {
        $tree.tree('removeNode', node);
      });
    }
    saveTree($tree, $('#crates').val() + ' has been cleared');
    indentTree($tree);
    $('#clearCrateModal').modal('hide');
  });  


  $('#deleteCrateModal').on('show.bs.modal', function() {
    var currentCrate = $('#crates').val();
    $('#deleteCrateMsg').text('Crate ' + currentCrate + ' is not empty, proceed with deletion?');
  });

  $('#deleteCrateModal').find('.btn-primary').click(deleteCrate);

  $('#delete').click(function() {
    if (metadataEmpty() && crateEmpty()) {
      deleteCrate();
    } else {
      $('#deleteCrateModal').modal('show');
    }
  });

}

function drawCrateContents() {
  $.ajax({
    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
    type: 'get',
    dataType: 'json',
    data: {
      'action': 'get_items'
    },
    success: function(data) {
      $tree = buildFileTree(data);
      indentTree($tree);
    },
    error: function(data) {
      var e = data.statusText;
      alert(e);
    }
  });
}


$(document).ready(function() {

  togglePostCrateToSWORD();

  $('#download').click('click', function(event) {
    if (treeHasNoFiles()) {
      displayNotification('No items in the crate to package');
      return;
    }

    displayNotification('Your download is being prepared. This might take some time if the files are big');
    window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php') + '?action=zip';

  });

  $('#post').click('click', function(event) {

    if (treeHasNoFiles()) {
      displayNotification('No items in the crate to package');
      return;
    }

    var sword_collection = $('#sword_collection').val();

    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'postzip',
        'sword_collection': sword_collection
      },
      success: function(data) {
        displayNotification('Crate posted successfully');
      },
      error: function(data) {
        displayError(data.statusText)
      }
    });

  });

  $('#epub').click(function(event) {
    if (treeHasNoFiles()) {
      displayNotification('No items in the crate to package');
    }
    //get all the html previews available, concatenate 'em all
    displayNotification('Your download is being prepared. This might take some time');
    window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php') + '?action=epub';
  });

  initCrateActions();


  $('#crate_input_name').keyup(function() {
    if ($(this).val().length > 128) {
      $("#crate_name_validation_error").text('Cr8 Name has reached the limit of 128 characters');
      $("#crate_name_validation_error").show();
      $(this).val($(this).val().substr(0, 128));
    }
    else {
      $("#crate_name_validation_error").text('');	
    }
  });

  $('#crate_input_description').keyup(function() {
    if ($(this).val().length > 8000) {
      $("#crate_description_validation_error").text('Cr8 Description has reached the limit of 8,000 characters');
      $("#crate_description_validation_error").show();
      $(this).val($(this).val().substr(0, 8000));
    }
    else {
      $("#crate_description_validation_error").text('');
    }
  });

  $('#subbutton').click(function(event) {
    $('#create_crate_error').hide();
    $("#crate_name_validation_error").hide();
    $('#crate_input_name').val('');
    $('#crate_input_description').val('');
    $("#crate_description_validation_error").hide();
  });

  $('#create_crate_submit').click(function(event) {
    $('#create_crate_error').hide();
    $("#crate_name_validation_error").hide();
    if ($('#crate_input_name').val() == '') {
      $("#crate_name_validation_error").text("This field is mandatory");
      $("#crate_name_validation_error").show();
      return false;
    }
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'get',
      dataType: 'html',
      async: false,
      data: {
        'action': 'create',
        'crate_name': $('#crate_input_name').val(),
        'crate_description': $('#crate_input_description').val(),
      },
      success: function(data) {
        $('#crate_input_name').val('');
        $('#crate_input_description').val('');
        $('#newCrateModal').modal('hide');
        $("#crates").append('<option id="' + data + '" value="' + data + '" >' + data + '</option>');
        $("#crates").val(data);
        $('#crates').trigger('change');
        displayNotification('Crate ' + data + ' successfully created', 6000);
      },
      error: function(data) {
        $('#create_crate_error').text(data.statusText);
        $('#create_crate_error').show();
        displayError(data.statusText);
      }
    });
    return false;
  });

  $('#crates').change(function() {
    var id = $(this).val();
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php') + '?action=switch&crate_id=' + id,
      type: 'get',
      dataType: 'html',
      async: false,
      success: function(data) {
        location.reload();
      },
      error: function(data) {
        var e = data.statusText;
        alert(e);
      }
    });
  });

  $('#for_top_level').change(function() {
    var id = $(this).find(':selected').attr("id");
    if (id === "select_top") {
      //remove all the child selects
      removeFORCodes();
      return;
    }
    //make a call to the backend, get next level codes, populate option
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php') + '?action=get_for_codes&level=' + id,
      type: 'get',
      dataType: 'json',
      success: function(data) {
        if (data != null) {
          removeFORCodes();
          for (var i = 0; i < data.length; i++) {
            $("#for_second_level").append('<option id="' + data[i] + '" value="' + data[i] + '" >' + data[i] + '</option>');
          }
        }
      },
      error: function(data) {
        var e = data.statusText;
        alert(e);
      }
    });
  });

  $('#search_people').click('click', function(event) {
    if ($.trim($('#keyword').val()).length == 0) {
      $('#search_people_results').empty();
      return;
    }

    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'search_people',
        'keyword': $.trim($('#keyword').val())
      },
      success: function(data) {
        // populate list of results
        $('#search_people_results').empty();
        for (var i = 0; i < data.length; i++) {
          var all_data = data[i]['result-metadata']['all'];
          var id = all_data['id'];
          var honorific = $.trim(all_data['Honorific'][0]);
          var given_name = $.trim(all_data['Given_Name'][0]);
          var family_name = $.trim(all_data['Family_Name'][0]);
          var email = $.trim(all_data['Email'][0]);
          var full_name = "";
          if (honorific)
            full_name = full_name + honorific + ' ';
          if (given_name)
            full_name = full_name + given_name + ' ';
          if (family_name)
            full_name = full_name + family_name;
          if (email)
          // TODO: Fix this
            full_name = full_name + '</p> <p>' + email;
          $('#search_people_results').append('<li><button id="' + 'search_people_result_' + id + '"><i class="fa fa-plus"></i></button>' + '<p id="' + id + '" class="full_name">' + full_name + '</p></li>');
        }
        $("button[id^='search_people_result_']").click('click', function(event) {
          // Add people to backend
          var input_element = $(this);
          var id = input_element.attr("id");
          creator_id = id.replace("search_people_result_", "");

          $.ajax({
            url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
            type: 'post',
            dataType: 'json',
            data: {
              'action': 'save_people',
              'creator_id': creator_id,
              'full_name': input_element.parent().text()
            },
            success: function(data) {
              //TODO: This no longer matches the template/index.php structure
              $('#creators').append('<li><button id="' + 'creator_' + creator_id + ' />' + '<span id="' + creator_id + '" class="full_name">' + input_element.parent().text() + '</span></li>');

              input_element.parent().remove();

              activateRemoveCreatorButton($('#creator_' + creator_id));
              makeCreatorEditable($('#' + creator_id));
              togglePostCrateToSWORD();
            },
            error: function(data) {
              displayError(data.statusText);
            }
          });
        });
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });

  });

  $('#search_activity').click('click', function(event) {
    if ($.trim($('#keyword_activity').val()).length == 0) {
      $('#search_activity_results').empty();
      return;
    }

    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'search_activity',
        'keyword_activity': $.trim($('#keyword_activity').val())
      },
      success: function(data) {
        // populate list of results
        $('#search_activity_results').empty();
        for (var i = 0; i < data.length; i++) {
          var all_data = data[i]['result-metadata']['all'];
          var id = all_data['id'];
          var dc_title = $.trim(data[i]['dc:title']);
          var grant_number = $.trim(data[i]['grant_number']);
          var full_grant_code = grant_number + ": " + dc_title;
          $('#search_activity_results').append('<li><input id="' + 'search_activity_result_' + id + '" type="button" value="Add" />' + '<span id="' + id + '"title="' + dc_title + '">' + grant_number + '</span></li>');
        }
        $("input[id^='search_activity_result_']").click('click', function(event) {
          // Add grant code to backend
          var input_element = $(this);
          var id = input_element.attr("id");
          var activity_id = id.replace("search_activity_result_", "");
          var grant_number = input_element.parent().text();
          var dc_title = $("span[id=" + activity_id + "]").attr('title');

          $.ajax({
            url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
            type: 'post',
            dataType: 'json',
            data: {
              'action': 'save_activity',
              'activity_id': activity_id,
              'grant_number': grant_number,
              'dc_title': dc_title
            },
            success: function(data) {
              //TODO: This no longer matches the template/index.php structure
              $('#activities').append('<li><input id="' + 'activity_' + activity_id + '" type="button" value="Remove" />' + '<span id="' + activity_id + '"title="' + dc_title + '">' + grant_number + '</span></li>');
              input_element.parent().remove();
              activateRemoveActivityButton($('#activity_' + activity_id));
            },
            error: function(data) {
              displayError(data.statusText);
            }
          });
        });
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });

  });

  var description_length = $('#description_length').text();

  $('#edit_description').click(function(event) {
    var old_description = $('#description').text();
    $('#description').text('');
    $('#description').html('<textarea id="crate_description" maxlength="' + description_length + '" style="width: 40%;" placeholder="Enter a description of the research data package for this Crate">' + old_description + '</textarea><br/><input id="save_description" type="button" value="Save" /><input id="cancel_description" type="button" value="Cancel" />');
    $('#save_description').click(function(event) {
      $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {
          'action': 'describe',
          'crate_description': $('#crate_description').val()
        },
        success: function(data) {
          $('#description').html('');
          $('#description').text(data.description);
          togglePostCrateToSWORD();
        },
        error: function(data) {
          displayError(data.statusText);
        }
      });
    });
    $('#cancel_description').click(function(event) {
      $('#description').html('');
      $('#description').text(old_description);
    });
  });

  drawCrateContents();

  max_sword_mb = parseInt($('#max_sword_mb').text());
  max_zip_mb = parseInt($('#max_zip_mb').text());
  crate_size_mb = 0;

  updateCrateSize();

  activateRemoveCreatorButtons();
  makeCreatorsEditable();

  activateRemoveActivityButtons();

});