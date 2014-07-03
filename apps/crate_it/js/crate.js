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


function SearchManager(definition, selectedList, $resultsLi, $selectedLi) {

  var _self = this;
  this.searchResultsList = [];
  this.selectedList = selectedList;
  this.definition = definition;
  this.$resultsLi = $resultsLi;
  this.$selectedLi = $selectedLi;

  this.search = function(keywords) {
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': _self.definition.actions.search,
        'keywords': keywords
      },
      success: function(data) {
        _self.searchResultsList = [];
        var records = data.map(function(record) { return parseMintResult(record); });
        _self.searchResultsList = records.filter(function(record) { return !isSelected(record.id); });
        _self.drawList(_self.$resultsLi, _self.searchResultsList, 'fa-plus');
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  };

  this.drawList = function ($li, list, faIcon) {
    $li.empty();
    list.forEach(function(record) {
      var html = renderRecord(record, faIcon);
      $li.append(html);
      $li.find('#'+record.id).click(function(){
        _self.toggle(record.id);
      });
    });
  }

  var resultComparitor = function(a, b) {
    var result = 0;
    if(a[_self.definition.sortField] > b[_self.definition.sortField]) {
      result = 1;
    } else if (a[_self.definition.sortField] < b[_self.definition.sortField]) {
      result = -1;
    }
    return result;
  }

  this.toggle = function(id) {
    var action = _self.definition.actions.add;
    var faIcon = 'fa-minus';
    var $sourceLi = $resultsLi;
    var $destLi = $selectedLi;
    var record = getRecord(id);
    if (isSelected(id)) {
      action = _self.definition.actions.remove;
      faIcon = 'fa-plus';
      $sourceLi = $selectedLi;
      $destLi = $resultsLi;
      // TODO: This array switching should be called in update:success
      remove(record, _self.selectedList);
      _self.searchResultsList.push(record);
    } else {
      remove(record, _self.searchResultsList);
      _self.selectedList.push(record);
    }
    var payload = {'action': action};
    var html = renderRecord(record, faIcon);
    $.extend(payload, record);
    update(payload, html, $sourceLi, $destLi);
  };

  var remove = function(record, array) {
    var index = array.indexOf(record);
    if(index > -1) {
      array = array.splice(index, 1);
    }
  };

  var update = function(payload, html, $sourceLi, $destLi) {
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: payload,
      success: function(data) {
        $sourceLi.find('#'+payload.id).parent().remove();
        $destLi.append(html);
        $destLi.find('#'+payload.id).click(function(){
          _self.toggle(payload.id);
        });
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  };

  var isSelected = function(id) {
    var result = false;
    _self.selectedList.forEach(function(searchResult) {
      if (searchResult.id == id) {
        result = true;
      }
    });
    return result;
  };

  var getRecord = function(id) {
    var records = _self.searchResultsList.concat(_self.selectedList);
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
  var parseMintResult = function(mintObject) {
    var metadata = mintObject['result-metadata']['all'];
    var result = {};
    for(var destField in _self.definition.mapping) {
      var sourceField = _self.definition.mapping[destField];
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

  var parseField = function(field) {
    var result = field;
    if($.isArray(field)) {
      result = field[0];
    }
    return $.trim(result);
  };

    // fields is an ordered list of fields to render, with the first being used as the title
  var renderRecord = function(record, faIcon) {
    var html = '<button class="pull-right" id="' + record.id + '"><i class="fa ' + faIcon + '"></i></button>';
    html += '<p class="full_name">' + record[_self.definition.displayFields[0]] + '</p>';
    for (var i = 1; i < _self.definition.displayFields.length ; i++) {
      html += '<p class=>' + record[_self.definition.displayFields[i]] + '</p>';
    }
    return '<li>' + html + '</li>';
  };

  _self.drawList($selectedLi, selectedList, 'fa-minus');
}



function initSearchHandlers() {

  manifest = getMaifest();

  var creatorDefinition = {
    actions: {
      search: 'search_people',
      add: 'save_people',
      remove: 'remove_people'
    },
    mapping: {
      'id': 'id',
      'name' : ['Honorific', 'Given_Name', 'Family_Name'],
      'email': 'Email'
    },
    displayFields: ['name', 'email'],
    sortField: 'name'
  };

  var creatorSelectedList = manifest.creators;
  var creator$resultsLi = $('#search_people_results');
  var creator$selectedLi = $('#creators');
  var CreatorSearchManager = new SearchManager(creatorDefinition, creatorSelectedList, creator$resultsLi, creator$selectedLi);
  $('#search_people').click(function () {
    CreatorSearchManager.search($.trim($('#keyword').val()));
  });


  var activityDefinition = {
    actions: {
      search: 'search_activity',
      add: 'save_activity',
      remove: 'remove_activity'
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
  var activity$resultsLi = $('#search_activity_results');
  var activity$selectedLi = $('#activities');
  var ActivitySearchManager = new SearchManager(activityDefinition, activitySelectedList, activity$resultsLi, activity$selectedLi);
  $('#search_activity').click(function () {
    ActivitySearchManager.search($.trim($('#keyword_activity').val()));
  });
}

// TODO: Super hacky synchronous call
// There are many of async calls on page load that could probably all be reduced to this one
function getMaifest() {
  var result =[];
  $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
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
    if ($(this).val().length > 6000) {
      $("#crate_description_validation_error").text('Crate Description has reached the limit of 6,000 characters');
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