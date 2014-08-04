function setupDescriptionOps() {
    
  $('#crate_input_description').keyup(function() {
    if ($(this).val().length > 6000) {
      $("#crate_description_validation_error").text('Crate Description has reached the limit of 6,000 characters');
      $("#crate_description_validation_error").show();
      // TODO read from model params loaded from PageController!
      $(this).val($(this).val().substr(0, 6000));
    }
    else {
      $("#crate_description_validation_error").text('');
    }
  });

  $('#edit_description').click(function(event) {
    var old_description = $('#description').text();
    $('#description').text('');
    $('#description').html('<textarea id="crate_description" maxlength="' + description_length + '" style="width: 40%;" placeholder="Enter a description of the research data package for this Crate">' + old_description + '</textarea><br/><input id="save_description" type="button" value="Save" /><input id="cancel_description" type="button" value="Cancel" />');
    $('#edit_description').addClass('hidden');
    $('#save_description').click(function(event) {
    var c_url = OC.generateUrl('apps/crate_it/crate/edit_description');
      $.ajax({
        url: c_url,
        type: 'post',
        dataType: 'json',
        data: {
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
}

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

function attachModalHandlers($modal, confirmCallback) {
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
      var newCrateName = $('#rename-crate').val();
      $tree.tree('updateNode', node, newCrateName); // TODO: shouldn't this be in success?
      var c_url = OC.generateUrl('apps/crate_it/crate/rename');
      $.ajax({
        url: c_url,
        type: 'post',
        dataType: 'json',
        data: {
          'newCrateName': newCrateName,
        },
        success: function() {
          $('#crates > #' + oldName).val(newCrateName).attr('id', newCrateName).text(newCrateName);
          // TODO: move messges to server side generations
          var successMessage = 'Renamed ' + oldName + ' to ' + newCrateName;
          var errorMessage = oldName + ' not renamed';
          // TODO: try to do this withou a page reload
          saveTree(successMessage, errorMessage, true);
        },
        error: function(data) {
          $tree.tree('updateNode', node, oldName);
          displayError(oldName + ' not renamed');
          // location.reload();
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
    url: 'crate/get_crate_size',
    type: 'get',
    dataType: 'json',
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
    error: function(data) {
        // do nothing - some owncloud ajax call somehow triggers this error block
    }
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
  var c_url = OC.generateUrl('apps/crate_it/crate/update');
  $.ajax({
    url: c_url,
    type: 'post',
    dataType: 'json',
    data: {
      'field': 'vfs',
      'value': JSON.parse($tree.tree('toJson'))
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

// TODO: Possibly better off migrating to jQuery validation plugin
//       see http://jqueryvalidation.org/documentation/



function validateEmail($input, $error, $confirm) {
  validateTextLength($input, $error, $confirm, 128);
  var email = $input.val();
  var isEmail = function() {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }
  if(!isEmail()) {
    $confirm.prop('disabled', true);
    $error.text('Not recognised as a valid email address');
    $error.show();
  }
}

function validateYear($input, $error, $confirm) {
  var inputYear = $.trim($input.val());
  var isYear = function() {
    var regex = /^\d{4}$/;
    return regex.test(inputYear);
  }
  var emptyYear = function() {
    return (!inputYear || /^\s*$/.test(inputYear));
  };
  if(emptyYear()) {
    $confirm.prop('disabled', true);
    $error.text('Year can not be blank');
    $error.show();
  } else if(!isYear()) {
    $confirm.prop('disabled', true);
    $error.text('Must be a valid submit year');
    $error.show();
  } else {
    $confirm.prop('disabled', false);
    $error.hide();
  }
}

function validateTextLength($input, $error, $confirm, maxLength) {
  if (typeof(maxLength) === 'undefined') {
    maxLength = 256;
  }
  var inputText = $input.val();
  var emptyText = function() {
    return (!inputText || /^\s*$/.test(inputText));
  };
  if(emptyText()) {
    $confirm.prop('disabled', true);
    $error.text('Field cannot be blank');
    $error.show();
  } else if (inputText.length > maxLength) {
      $error.text('Field has reached the limit of ' + maxLength + ' characters');
      $input.val(inputText.substr(0, maxLength));
      $error.show();
      $confirm.prop('disabled', false);
   } else {
    $confirm.prop('disabled', false);
    $error.hide();
  }
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
  };

  var crateEmpty = function() {
    return $tree.tree('getNodeById', 'rootfolder').children.length == 0;
  };

  var createCrate = function() {
    var params =  {
        'name': $('#crate_input_name').val(),
        'description': $('#crate_input_description').val(),
      };
    var c_url = OC.generateUrl('apps/crate_it/crate/create');
    $.ajax({
      url: c_url,
      type: 'post',
      dataType: 'json',
      async: false,
      data: params,
      success: function(data) {
        var crateName = data.crateName;
        $('#crate_input_name').val('');
        $('#crate_input_description').val('');
        $('#createCrateModal').modal('hide');
        $("#crates").append('<option id="' + crateName + '" value="' + crateName + '" >' + crateName + '</option>');
        $("#crates").val(crateName);
        $("#description").text(data.crateDescription);
        $('#crates').trigger('change');
        displayNotification('Crate ' + crateName + ' successfully created', 6000);
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  };

  var deleteCrate = function() {
    var current_crate = $('#crates').val();
    $.ajax({
      url: OC.generateUrl('apps/crate_it/crate/delete'),
      type: 'get',
      dataType: 'json',
      success: function(data) {
        // TODO: push notification messages to server side to data.msg
        displayNotification('Crate ' + current_crate + ' deleted');
        location.reload();
      },
      error: function(data) {
        // TODO: be consistent with response messages
        displayError(data.statusText);
      }
    });
    $('#deleteCrateModal').modal('hide');
  };

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
    var c_url = OC.generateUrl('apps/crate_it/crate/get_items?crate_id={crateName}', {crateName: id});
    $.ajax({
      url: c_url,
      type: 'get',
      dataType: 'json',
      async: false,
      success: function(data) {
        manifest = data;
        reloadCrateData(data);
      },
      error: function(data) 
      {
        displayError(data.statusText);
      }
    });
  });

}

//TODO use something like this when the pages loads
function reloadCrateData(manifest) {
  $('#description').text(manifest['description']);
  $('#files').remove();
  $('#container').after('<div id="files"></div>');
  buildFileTree(manifest);
  indentTree();
  // TODO Have a registry of search managers and loop over them
  CreatorSearchManager.loadManifestData(manifest);
  ActivitySearchManager.loadManifestData(manifest);
}

function drawCrateContents() {
  // TODO: maybe get rid of this and just use reloadCrateData
  var c_url = OC.generateUrl('apps/crate_it/crate/get_items?crate_id={crateName}', {'crateName': templateVars['selected_crate']});
  $.ajax({
    url: c_url,
    type: 'get',
    dataType: 'json',
    success: function(data) {
      manifest = data;
      $tree = buildFileTree(data);
      indentTree();
    },
    error: function(data) {
      var e = data.statusText;
    }
  });
}


function SearchManager(definition, selectedList, $resultsUl, $selectedUl, $notification) {

  var _self = this;
  var searchResultsList = [];
  var eventListeners = [];

  this.search = function(keywords) {
    var c_url = OC.generateUrl('apps/crate_it/crate/search');
    $.ajax({
      url: c_url,
      type: 'post',
      dataType: 'json',
      data: {
        'type': definition.actions.search,
        'keywords': keywords
      },
      success: function(data) {
        searchResultsList = [];
        var records = data.map(function(record) { return parseMintResult(record); });
        searchResultsList = records.filter(function(record) { return !isSelected(record.id); });
        _self.notifyListeners();
        if(searchResultsList == 0) {
          $notification.text('0 new results returned');
        } else {
          $notification.text('');
        }
        drawList($resultsUl, searchResultsList, 'fa-plus');
      },
      error: function(data) {
        $notification.text(data.statusText);
      }
    });
  };

  this.loadManifestData = function(manifest) {
    selectedList = manifest[definition.manifestField];
    drawList($selectedUl, selectedList, 'fa-minus');
    _self.notifyListeners();
  }

  var drawList = function ($li, list, faIcon) {
    $li.empty();
    list.forEach(function(record) {
      var html = renderRecord(record, faIcon);
      $li.append(html);
      $li.find('#'+record.id).click(function(){
        toggle(record.id);
      });
    });
  };

  this.addEventListener = function(callback) {
    eventListeners.push(callback);
  };

  this.clearSelected = function() {
    var c_url = OC.generateUrl('apps/crate_it/crate/update');
    $.ajax({
      url: c_url,
      type: 'post',
      dataType: 'json',
      data: {
        field: definition.manifestField,
      },
      success: function(data) {
        searchResultsList = searchResultsList.concat(selectedList);
        selectedList = [];
        $selectedUl.empty();
        drawList($resultsUl, searchResultsList, 'fa-plus');
        _self.notifyListeners();
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  };

  this.notifyListeners = function() {
    var e = {
      selected: selectedList.length,
      results: searchResultsList.length
      };
    eventListeners.forEach(function(listener) {
      listener(e);
    });
  };

  function toggle(id) {
    var faIcon = 'fa-minus';
    var $sourceLi = $resultsUl;
    var $destLi = $selectedUl;
    var record = getRecord(id);
    if (isSelected(id)) {
      faIcon = 'fa-plus';
      $sourceLi = $selectedUl;
      $destLi = $resultsUl;
      // TODO: This array switching should be called in update:success
      remove(record, selectedList);
      searchResultsList.push(record);
    } else {
      remove(record, searchResultsList);
      selectedList.push(record);
    }
    var html = renderRecord(record, faIcon);
    update(record, html, $sourceLi, $destLi);
  };

  var remove = function(record, array) {
    var index = array.indexOf(record);
    if(index > -1) {
      array = array.splice(index, 1);
    }
  };

  function createEmptyRecord() {
    var record = {};
    for (field in definition.mapping) {
      record[field] = '';
    }
    return record;
  }

  // TODO: this indicates that we probably need a Record object
  //       with this as a member function
  function hashCode(record) {
    var hashString = function(string) {
      var hash = 0, i, chr, len;
      if (string.length == 0) return hash;
      for (i = 0, len = string.length; i < len; i++) {
        chr   = string.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
      }
      return hash;
    }

    var getRecordString = function(record) {
      var recordString;
        for (field in record) {
          var value = record[field];
          if (typeof value == 'string' || value instanceof String) {
            recordString += value;
          } else if (value != null && typeof value == 'object') {
            recordString += getRecordString(value);
          }
        }
      return recordString;
    }
    recordString = getRecordString(record);
    return hashString(recordString);
  }

  this.addRecord = function(overrides) {
    var record = createEmptyRecord();
    addOverrides(record, overrides);
    record['id'] = hashCode(record);
    selectedList.push(record);
    var html = renderRecord(record, 'fa-minus');
    update(record, html, null, $selectedUl);
  }


  function addOverrides(record, overrides) {
    record['overrides'] = overrides;
  }

  function applyOverrides(record) {
    newRecord = record;
    if('undefined' !== typeof record.overrides) {
      var newRecord = $.extend(true, {}, record);
      for (override in newRecord.overrides) {
        newRecord[override] = newRecord.overrides[override];
      }
    }
    return newRecord;
  }

  function update(record, html, $sourceLi, $destLi) {
    var c_url = OC.generateUrl('apps/crate_it/crate/update');
    $.ajax({
      url: c_url,
      type: 'post',
      dataType: 'json',
      data: {field: definition.manifestField, value: selectedList},
      success: function(data) {
        if($sourceLi) {
          $sourceLi.find('#'+record.id).parent().remove();
        }
        $destLi.append(html);
        $destLi.find('#'+record.id).click(function(){
          toggle(record.id);
        });
        _self.notifyListeners();
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  };

  function isSelected(id) {
    var result = false;
    selectedList.forEach(function(searchResult) {
      if (searchResult.id == id) {
        result = true;
      }
    });
    return result;
  };

  function getRecord(id) {
    var records = searchResultsList.concat(selectedList);
    var result = null;
    records.forEach(function(record){
      if (record.id == id) {
        result = record;
      }
    });
    return result;
  };

    // mapping object is has {dest: source} format
  // source can be an array of fields that will be merge into the dest
  // var parseMintResult = function(mintObject) {
  function parseMintResult(mintObject) {
    var metadata = mintObject['result-metadata']['all'];
    var result = {};
    for(var destField in definition.mapping) {
      var sourceField = definition.mapping[destField];
      if($.isArray(sourceField)) {
        var fieldElements = [];
        sourceField.forEach(function(field) {
          fieldElements.push(parseField(metadata[field]));
        });
        result[destField] = fieldElements.join(' ');
      } else {
        result[destField] = parseField(metadata[sourceField]);
      }
    }
    return result;
  };

  function parseField(field) {
    var result = field;
    if($.isArray(field)) {
      result = field[0];
    }
    return $.trim(result);
  };

  // fields is an ordered list of fields to render, with the first being used as the title
  function renderRecord(record, faIcon) {
    record = applyOverrides(record);
    var html = '<button class="pull-right" id="' + record.id + '"><i class="fa ' + faIcon + '"></i></button>';
    html += '<p class="metadata_heading">' + record[definition.displayFields[0]] + '</p>';
    for (var i = 1; i < definition.displayFields.length ; i++) {
      html += '<p class=>' + record[definition.displayFields[i]] + '</p>';
    }
    return '<li>' + html + '</li>';
  };

  drawList($selectedUl, selectedList, 'fa-minus');
}



function initSearchHandlers() {
  // TODO: prefix this with var to close scope when not dubugging
  // TODO: replace this call with a variable shared between buildFileTree
  //       as the manifest is retrieved multiple times
  manifest = getMaifest();
  $clearMetadataModal = $('#clearMetadataModal');

  var creatorDefinition = {
    manifestField: 'creators',
    actions: {
      search: 'people'
    },
    mapping: {
      'id': 'id',
      'name' : ['Honorific', 'Given_Name', 'Family_Name'],
      'email': 'Email'
    },
    displayFields: ['name', 'email'],
    sortField: 'name'
  };

  // TODO: a lot of these elements could be pushed into the SearchManager constructor
  //      so it creates the widget
  var creatorSelectedList = manifest.creators;
  var creator$resultsUl = $('#search_people_results');
  var creator$selectedUl = $('#selected_creators');
  var creator$notification = $('#creators_search_notification');
  // TODO: add this to a namespace rather than exposing globally
  CreatorSearchManager = new SearchManager(creatorDefinition, creatorSelectedList, creator$resultsUl, creator$selectedUl, creator$notification);
  $('#search_people').click(function () {
    CreatorSearchManager.search($.trim($('#keyword_creator').val()));
  });
  $('#keyword_creator').keyup(function(e) {
    if (e.keyCode == 13) {
      CreatorSearchManager.search($.trim($(this).val()));
    }
  });
  var creatorsCount = function(e) {
    $('#creators_count').text(e.selected);
  };
  CreatorSearchManager.addEventListener(creatorsCount);
  CreatorSearchManager.notifyListeners();
  $('#clear_creators').click(function() {
    $('#clearMetadataField').text('Creators');
    attachModalHandlers($clearMetadataModal, CreatorSearchManager.clearSelected);
  });

  var addCreator = function() {
    var name = $('#add-creator-name').val();
    var email = $('#add-creator-email').val();
    var overrides = {'name': name, 'email': email}
    CreatorSearchManager.addRecord(overrides);
  }
  var $addCreatorModal = $('#addCreatorModal');
  var $addCreatorConfirm = $addCreatorModal.find('.btn-primary');

  $('#add-creator-name').keyup(function() {
      var $input = $(this);
      var $error = $('#add-creator-name-validation-error');
      validateTextLength($input, $error, $addCreatorConfirm);
  });

  $('#add-creator-email').keyup(function() {
      var $input = $(this);
      var $error = $('#add-creator-email-validation-error');
      validateEmail($input, $error, $addCreatorConfirm);
  });

  $('#add-creator').click(function() {
    attachModalHandlers($addCreatorModal, addCreator);
  });

  var activityDefinition = {
    manifestField: 'activities',
    actions: {
      search: 'activities'
    },
    mapping: {
      'id':'id',
      'title': 'dc_title',
      'date': 'dc_date',
      'grant_number': 'grant_number'
    },
    displayFields: ['grant_number', 'date', 'title'],
    sortField: 'title'
  };
  
  var activitySelectedList = manifest.activities;
  var activity$resultsUl = $('#search_activity_results');
  var activity$selectedUl = $('#selected_activities');
  var activity$notification = $('#activites_search_notification');
  // TODO: add this to a namespace rather than exposing globally
  ActivitySearchManager = new SearchManager(activityDefinition, activitySelectedList, activity$resultsUl, activity$selectedUl, activity$notification);

  $('#search_activity').click(function () {
    ActivitySearchManager.search($.trim($('#keyword_activity').val()));
  });
  $('#keyword_activity').keyup(function(e) {
    if (e.keyCode == 13) {
      ActivitySearchManager.search($.trim($(this).val()));
    }
  });
  var activitiesSelectedCount = function(e) {
    $('#activities_count').text(e.selected);
  };
  ActivitySearchManager.addEventListener(activitiesSelectedCount);
  ActivitySearchManager.notifyListeners();
  $('#clear_grant_numbers').click(function() {
    $('#clearMetadataField').text('Grants');
    attachModalHandlers($clearMetadataModal, ActivitySearchManager.clearSelected);
  });

  var addActivity = function() {
    var grant_number = $('#add-grant-number').val();
    var date = $('#add-grant-year').val();
    var title = $('#add-grant-title').val();
    var institution = $('#add-grant-institution').val();
    var overrides = {'grant_number': grant_number,
                     'date': date,
                     'title': title,
                     'institution': institution};
    ActivitySearchManager.addRecord(overrides);
  }

  // TODO: Naming inconsistency here between 'grants' and activities
  var $addActivityModal = $('#addGrantModal');

  var grantValidator = new CrateIt.Util.FormValidator($addActivityModal);
  grantValidator.addValidator($('#add-grant-number'), new CrateIt.Util.RequiredValidator('Grant number'));
  grantValidator.addValidator($('#add-grant-number'), new CrateIt.Util.MaxLengthValidator('Grant number', 256));

  grantValidator.addValidator($('#add-grant-year'), new CrateIt.Util.RequiredValidator('Grant number'));
  grantValidator.addValidator($('#add-grant-year'), new CrateIt.Util.YearValidator());
  
  grantValidator.addValidator($('#add-grant-institution'), new CrateIt.Util.RequiredValidator('Institution'));
  grantValidator.addValidator($('#add-grant-institution'), new CrateIt.Util.MaxLengthValidator('Institution', 256));

  grantValidator.addValidator($('#add-grant-title'), new CrateIt.Util.RequiredValidator('Title'));
  grantValidator.addValidator($('#add-grant-title'), new CrateIt.Util.MaxLengthValidator('Title', 256));


  $('#add-activity').click(function() {
    attachModalHandlers($addActivityModal, addActivity);
  })

}

// TODO: Super hacky blocking synchronous call
// There are many of async calls on page load that could probably all be reduced to this one
function getMaifest() {
  var result =[];
  var c_url = OC.generateUrl('apps/crate_it/crate/get_items?crate_id={crateName}', {crateName: $('#crates').val()});
  $.ajax({
      url: c_url,
      type: 'get',
      async: false,
      dataType: 'json',
      data: {'action': 'get_manifest'},
      success: function(data) {
        result = data;
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  return result;
}


function loadTemplateVars() {
  templateVars = {}
  $('#hidden_vars').children().each(function() {
    var $el = $(this);
    var key = $el.attr('id');
    var value = $el.text();
    if(!isNaN(value)) {
      value = +value;
    }
    templateVars[key] = value;
  });
}


function calulateHeights() {
  var tabsHeight = ($('.panel-heading').outerHeight() * ($('.panel-heading').length + 1 )) + $('.collapse.info.in .panel-body').outerHeight();
  var height = $('#meta-data').innerHeight() - tabsHeight;
  $('.collapse.standard .panel-body').height(height + 12);
}


function initAutoResizeMetadataTabs() {
  $('#meta-data').on('show.bs.collapse', function (e) {
      $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
      calulateHeights();
  });
  $('#meta-data').on('hide.bs.collapse', function (e) {
      $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
      calulateHeights();
  });

  $(window).resize(function() {
    calulateHeights();
  });  
}



