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
  $.ajax({
    url: 'crate_it/update',
    type: 'post',
    dataType: 'html',
    data: {
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
      dataType: 'html',
      async: false,
      data: params,
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
        displayError(data.statusText);
      }
    });
  };

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
          displayNotification('Crate ' + current_crate + ' deleted');
          location.reload();
        } else {
          displayError(data.msg);
        }
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

  // $('#crates').change(function() {
  //   var id = $(this).val();
  //   var c_url = OC.generateUrl('apps/crate_it/crate/switch?crate_id={crateName}', {crateName: id});
  //   $.ajax({
  //     url: c_url,
  //     type: 'get',
  //     dataType: 'html',
  //     async: false,
  //     success: function(data) {
  //       location.reload();
  //     },
  //     error: function(data) {
  //       var e = data.statusText;
  //       alert(e);
  //     }
  //   });
  // });


  $('#crates').change(function() {
    var id = $(this).val();
    var c_url = OC.generateUrl('apps/crate_it/crate/get_items?crate_id={crateName}', {crateName: id});
    console.log(c_url);
    $.ajax({
      url: c_url,
      type: 'get',
      dataType: 'html',
      async: false,
      success: function(data) {
        manifest = JSON.parse(data);
        reloadCrateData(manifest);
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });

}

//TODO use something like this when the pages loads
function reloadCrateData(manifest) {
  // TODO load other metadata
  $tree.remove();
  $('#container').after('<div id="files"></div>');
  buildFileTree(manifest);
  indentTree();
}

function drawCrateContents() {
  $.ajax({
    url: 'crate/get_items',
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
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        'action': 'search',
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
    $.ajax({
      url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
      type: 'post',
      dataType: 'json',
      data: {
        action: 'clear_field',
        field: definition.manifestField
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

  // NOTE: not currently used
  // var resultComparitor = function(a, b) {
  //   var result = 0;
  //   if(a[definition.sortField] > b[definition.sortField]) {
  //     result = 1;
  //   } else if (a[definition.sortField] < b[definition.sortField]) {
  //     result = -1;
  //   }
  //   return result;
  // }

  // this.toggle = function(id) {
  function toggle(id) {
    var action = definition.actions.add;
    var faIcon = 'fa-minus';
    var $sourceLi = $resultsUl;
    var $destLi = $selectedUl;
    var record = getRecord(id);
    if (isSelected(id)) {
      action = definition.actions.remove;
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
          toggle(payload.id);
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

  // var parseField = function(field) {
  function parseField(field) {
    var result = field;
    if($.isArray(field)) {
      result = field[0];
    }
    return $.trim(result);
  };

    // fields is an ordered list of fields to render, with the first being used as the title
  // var renderRecord = function(record, faIcon) {
  function renderRecord(record, faIcon) {
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
  manifest = getMaifest();
  $clearMetadataModal = $('#clearMetadataModal');

  var creatorDefinition = {
    manifestField: 'creators',
    actions: {
      search: 'people',
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
  var creator$resultsUl = $('#search_people_results');
  var creator$selectedUl = $('#selected_creators');
  var creator$notification = $('#creators_search_notification');
  var CreatorSearchManager = new SearchManager(creatorDefinition, creatorSelectedList, creator$resultsUl, creator$selectedUl, creator$notification);
  $('#search_people').click(function () {
    CreatorSearchManager.search($.trim($('#keyword').val()));
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

  var activityDefinition = {
    manifestField: 'activities',
    actions: {
      search: 'activities',
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
  var activity$resultsUl = $('#search_activity_results');
  var activity$selectedUl = $('#selected_activities');
  var activity$notification = $('#activites_search_notification');
  var ActivitySearchManager = new SearchManager(activityDefinition, activitySelectedList, activity$resultsUl, activity$selectedUl, activity$notification);

  $('#search_activity').click(function () {
    ActivitySearchManager.search($.trim($('#keyword_activity').val()));
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
}

// TODO: Super hacky synchronous call
// There are many of async calls on page load that could probably all be reduced to this one
function getMaifest() {
  var result =[];
  $.ajax({
      url: OC.linkTo('crate_it/crates/manifest'),
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