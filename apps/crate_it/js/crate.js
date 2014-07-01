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

function displayError(errorMessage, delayTime) {
  displayNotification('There was an error: ' + errorMessage, delayTime);
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


function indentTree() {
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

  var attachModalHandlers = function($modal, confirmCallback) {
    var $confirm = $modal.find('.btn-primary');

    var clearInput = function() {
      var $input = $modal.find('input');
      if ($input) {
        $input.val('');
      }
    };

    $confirm.click(function() {
      confirmCallback();
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
    }
   // the successMessage function gets called after the name has changed
    attachModalHandlers($modal, confirmCallback);
  }

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
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });
}


function validateCrateName($input, $error, $confirm) {
  var inputName = $input.val();
    var crates = $.map($('#crates > option'), function(el, i) {
    return $(el).attr('id');
  });
  var emptyName = function() {
    return (!inputName || /^\s*$/.test(inputName));
  };
  var existingName = function() {
    return crates.indexOf(inputName) > -1;
  };
  if(existingName() || emptyName()) {
    $confirm.prop('disabled', true);
    if (emptyName()) {
      $error.text('Crate name cannot be blank');
    } else {
      $error.text('Crate with name "' + inputName + '" already exists');
    }
    $error.show();
  } else if (inputName.length > 128) {
      $error.text('Crate name has reached the limit of 128 characters');
      $input.val(inputName.substr(0, 128));
      $error.show();
      $confirm.prop('disabled', false);
   } else {
    $confirm.prop('disabled', false);
    $error.hide();
  }
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

  var createCrate = function() {
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
        $('#createCrateModal').modal('hide');
        $("#crates").append('<option id="' + data + '" value="' + data + '" >' + data + '</option>');
        $("#crates").val(data);
        $('#crates').trigger('change');
        displayNotification('Crate ' + data + ' successfully created', 6000);
      },
      error: function(data) {
        // $('#create_crate_error').text(data.statusText);
        // $('#create_crate_error').show();
        displayError(data.statusText);
      }
    });
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

  $('#crate_input_name').keyup(function() {
      var $input = $(this);
      var $error = $('#crate_name_validation_error');
      var $confirm = $('#createCrateModal').find('.btn-primary');
      validateCrateName($input, $error, $confirm);
  });

  $('#createCrateModal').find('.btn-primary').click(createCrate);

  $('#createCrateModal').on('show.bs.modal', function() {
    $('#crate_input_name').val('');
    $('#crate_input_description').val('');
    $("#crate_name_validation_error").hide();
    $("#crate_description_validation_error").hide();
  });

  $('#clearCrateModal').find('.btn-primary').click(function() {
    var children = $tree.tree('getNodeById', 'rootfolder').children;
    // NOTE: The while loop is a workaround to the forEach loop inexplicably skipping
    // the first element
    while(children.length > 0) {
      children.forEach(function(node) {
        $tree.tree('removeNode', node);
      });
    }
    saveTree($('#crates').val() + ' has been cleared');
    indentTree();
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
      indentTree();
    },
    error: function(data) {
      var e = data.statusText;
      alert(e);
    }
  });
}


function initSearchHandlers() {
  var formatNames = function(mintObject) {
    var fields = ['Honorific', 'Given_Name', 'Family_Name', 'Email', 'id'];
    var result = [];
    fields.forEach(function(field){
      result.push(mintObject['result-metadata']['all'][field][0]);
    });
    return {'name': result.slice(0,3).join(' '), 'email': result[3], 'id': result[4]};
  };

  var formatActivities = function(mintObject) {
    var metadata = mintObject['result-metadata']['all']; 
    return {'id': metadata['id'], 'title': metadata['dc_title'], 'grant_number': metadata['grant_number'][0]};
  }

  var addResult = function(person, element, callback) {
    var result = function() {
      $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {
          'action': 'save_people',
          'creator_id': person.id,
          'full_name': person.name
        },
        success: function(data) {
          $('#search_people_results').find('#'+person.id).parent().remove();
          $('#creators').append(element);
          $('#creators').find('#'+person.id).click(callback);
        },
        error: function(data) {
          displayError(data.statusText);
        }
      });
    };
    return result;
  };

  var addActivityResult = function(activity, element, callback) {
    var result = function() {
      $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {
          'action': 'save_activity',
          'activity_id': activity.id,
          'grant_number': activity.grant_number,
          'dc_title': activity.title
        },
        success: function(data) {
          $('#search_activity_results').find('#'+activity.id).parent().remove();
          $('#activities').append(element);
          $('#activities').find('#'+activity.id).click(callback);
        },
        error: function(data) {
          displayError(data.statusText);
        }
      });
    };
    return result;
  };


  var removeResult = function(person) {
    var result = function() {
      $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {
          'action': 'remove_people',
          'creator_id': person.id,
          'full_name': person.name
        },
        success: function(data) {
          $('#creators').find('#'+person.id).parent().remove();
        },
        error: function(data) {
          displayError(data.statusText);
        }
      });
    };
    return result;
  }

  var removeActivityResult = function(activity) {
    var result = function() {
      $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {
          'action': 'remove_activity',
          'activity_id': activity.id,
        },
        success: function(data) {
          $('#activities').find('#'+activity.id).parent().remove();
        },
        error: function(data) {
          displayError(data.statusText);
        }
      });
    }
    return result;
  };


  var createResult = function(person, faIcon) {
    var button = '<button class="pull-right" id="' + person.id + '"><i class="fa ' + faIcon + '"></i></button>';
    var name = '<p class="full_name">' + person.name + '</p>';
    var email = '<p>'  + person.email + '</p>';
    return '<li>' + button + name + email + '</li>';
  };

  var createActivityResult = function(activity, faIcon) {
    // $('#search_activity_results').append('<li><button id="' + 'search_activity_result_' + id + '" class="pull-right"><i class="fa fa-plus"></i></button>' + '<p id="' + id + '"title="' + dc_title + '"><strong>' + grant_number + '</strong> ' + dc_title + '</p></li>');
    var button = '<button class="pull-right" id="' + activity.id + '"><i class="fa ' + faIcon + '"></i></button>';
    var grant_number = '<p class="full_name">' + activity.grant_number + '</p>';
    var title = '<p>'  + activity.title + '</p>';
    return '<li>' + button + grant_number + title + '</li>';
  };




  $('#search_people').click('click', function(event) {
    // TODO: Fix this
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
        $('#search_people_results').empty();
        var people = data.map(formatNames);
        people.forEach(function(person) {
          var removeCreator = createResult(person, 'fa-minus');
          var addCreator = createResult(person, 'fa-plus');
          var removeCallback = removeResult(person);
          var addCallback = addResult(person, removeCreator, removeCallback);
          $('#search_people_results').append(addCreator);
          $('#search_people_results').find('#' + person.id).click(addCallback);
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
        $('#search_activity_results').empty();
        var activities = data.map(formatActivities);
        activities.forEach(function(activity) {
          var removeActivity = createActivityResult(activity, 'fa-minus');
          var addActivity = createActivityResult(activity, 'fa-plus');
          var removeCallback = removeActivityResult(activity);
          var addCallback = addActivityResult(activity, removeActivity, removeCallback);
          $('#search_activity_results').append(addActivity);
          $('#search_activity_results').find('#' + activity.id).click(addCallback);
        });
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });

}

$(document).ready(function() {

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
        displayError(data.statusText);
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

  $('#crate_input_description').keyup(function() {
    if ($(this).val().length > 8000) {
      $("#crate_description_validation_error").text('Cr8 Description has reached the limit of 8,000 characters');
      $("#crate_description_validation_error").show();
      $(this).val($(this).val().substr(0, description_length));
    }
    else {
      $("#crate_description_validation_error").text('');
    }
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

  var description_length = $('#description_length').text();

  $('#edit_description').click(function(event) {
    var old_description = $('#description').text();
    $('#description').text('');
    $('#description').html('<textarea id="crate_description" maxlength="' + description_length + '" style="width: 40%;" placeholder="Enter a description of the research data package for this Crate">' + old_description + '</textarea><br/><input id="save_description" type="button" value="Save" /><input id="cancel_description" type="button" value="Cancel" />');
    $('#edit_description').addClass('hidden');
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
          $('#edit_description').removeClass('hidden');
        },
        error: function(data) {
          displayError(data.statusText);
        }
      });
    });
    $('#cancel_description').click(function(event) {
      $('#description').html('');
      $('#description').text(old_description);
      $('#edit_description').removeClass('hidden');
    });
  });

  drawCrateContents();

  max_sword_mb = parseInt($('#max_sword_mb').text());
  max_zip_mb = parseInt($('#max_zip_mb').text());
  crate_size_mb = 0;

  updateCrateSize();

  initSearchHandlers();

  calulate_heights();

  $('#meta-data').on('show.bs.collapse', function (e) {
      $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
      calulate_heights();
  });
  $('#meta-data').on('hide.bs.collapse', function (e) {
      $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
      calulate_heights();
  });
});
$( window ).resize(function() {
  calulate_heights();
});
function calulate_heights() {
  var tabsHeight = ($('.panel-heading').outerHeight() * ($('.panel-heading').length + 1 )) + $('.collapse.info.in .panel-body').outerHeight();
  alert(tabsHeight)
  var height = $('#meta-data').innerHeight() - tabsHeight;
  $('.collapse.standard .panel-body').height(height + 12);
}