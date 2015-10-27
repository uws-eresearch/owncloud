function SearchManager(definition, selectedList, $resultsUl, $selectedUl, $notification, $editModal) {

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
        var records = data.map(function(record) {
          return parseMintResult(record);
        });
        searchResultsList = records.filter(function(record) {
          return !isSelected(record.id);
        });
        _self.notifyListeners();
        if (searchResultsList == 0) {
          $notification.text('0 new results returned');
        } else {
          $notification.text('');
        }
        drawList($resultsUl, searchResultsList, 'fa-plus');
      },
      error: function(jqXHR) {
        $notification.text(jqXHR.responseJSON.msg);
      }
    });
  };

  this.loadManifestData = function(manifest) {
    selectedList = manifest[definition.manifestField];
    drawList($selectedUl, selectedList, 'fa-minus');
    _self.notifyListeners();
  };

  var drawList = function($li, list, faIcon) {
    $li.empty();
    list.forEach(function(record) {
      var html = renderRecord(record, faIcon);
      $li.append(html);
      var $toggleButton = $li.find('#' + record.id);
      $toggleButton.click(function() {
        toggle(record.id);
      });
      // TODO: using faIcon as a flag again
      if (faIcon == 'fa-minus') {
        $toggleButton.next().click(function() {
          displayEditRecordModal(record.id);
        });
      }
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
        fields: [{
          field: definition.manifestField,
          value: ''
        }]
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

  this.getSelected = function getSelected() {
    return selectedList.slice();
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
    if (index > -1) {
      array = array.splice(index, 1);
    }
  };

  function isEditable(record) {
    return definition.editableRecords.indexOf(record.source) > -1;
  }

  function createEmptyRecord() {
    var record = {
      source: 'manual'
    };
    for (field in definition.mapping) {
      record[field] = '';
    }
    return record;
  }

  // TODO: this indicates that we probably need a Record object
  //       with this as a member function
  function hashCode(record) {
    var hashString = function(string) {
      var hash = 0,
        i, chr, len;
      if (string.length == 0) return hash;
      for (i = 0, len = string.length; i < len; i++) {
        chr = string.charCodeAt(i);
        hash = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
      }
      return hash;
    };

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
    };
    recordString = getRecordString(record);
    return hashString(recordString + String($.now()));
  }

  this.addRecord = function(overrides) {
    var record = createEmptyRecord();
    addOverrides(record, overrides);
    record['id'] = hashCode(record);
    selectedList.push(record);
    var html = renderRecord(record, 'fa-minus');
    update(record, html, null, $selectedUl);
  };

  function displayEditRecordModal(id) {
    var record = getRecord(id, 'selected');
    if (isEditable(record)) {
      var editPrefix = '#edit-' + definition.manifestField + '-';
      var originalPrefix = '#original-' + definition.manifestField;
      var manualPrefix = '#manual-' + definition.manifestField;
      if (record.source == 'manual') {
        $(originalPrefix).hide();
        $(manualPrefix).show();
      } else {
        $(originalPrefix).show();
        $(manualPrefix).hide();
      }
      originalPrefix += '-';
      $(editPrefix + 'record').val(id);
      definition.editFields.forEach(function(field) {
        var editElId = editPrefix + field;
        if (record.overrides && record.overrides[field]) {
          $(editElId).val(record.overrides[field]);
        } else {
          $(editElId).val(record[field]);
        }
        var originalElId = originalPrefix + field;
        $(originalElId).val(record[field]);
      });
      attachModalHandlers($editModal, editRecord);
      $editModal.modal('show');
    }
  }

  function editRecord() {
    var prefix = '#edit-' + definition.manifestField + '-';
    var id = $(prefix + 'record').val();
    var record = getRecord(id);
    var overrides = record.overrides || {};
    definition.editFields.forEach(function(field) {
      var elId = prefix + field;
      overrides[field] = $(elId).val();
    });
    record['overrides'] = overrides;
    var html = renderRecord(record, 'fa-minus');
    update(record, html, $selectedUl, $selectedUl);
  }

  function addOverrides(record, overrides) {
    record['overrides'] = overrides;
  }

  function applyOverrides(record) {
    var newRecord = $.extend(true, {}, record);
    if ('undefined' !== typeof record.overrides) {
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
      data: {
        fields: [{
          field: definition.manifestField,
          value: selectedList
        }]
      },
      success: function(data) {
        if ($sourceLi) {
          $sourceLi.find('#' + record.id).parent().remove();
        }
        $destLi.append(html);
        $destLi.find('#' + record.id).click(function() {
          toggle(record.id); // TODO: move event handle attachment to separate function
        });
        if ($destLi.find('#' + record.id + ' > i').hasClass('fa-minus')) { // TODO: clean this up, hack and nasty
          $destLi.find('#' + record.id).next().click(function() {
            displayEditRecordModal(record.id);
          });
        }
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

  function getRecord(id, type) {
    var records;
    if (type == 'selected') {
      records = selectedList;
    } else if (type == 'results') {
      records = searchResultsList;
    } else {
      records = searchResultsList.concat(selectedList);
    }
    var result = null;
    records.forEach(function(record) {
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
    var result = {
      source: 'mint'
    };
    for (var destField in definition.mapping) {
      var sourceField = definition.mapping[destField];
      if ($.isArray(sourceField)) {
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
    if ($.isArray(field)) {
      result = field[0];
    }
    return $.trim(result);
  };

  // fields is an ordered list of fields to render, with the first being used as the title
  function renderRecord(record, faIcon) {
    var record = applyOverrides(record);
    var editable = ' ';
    if (!isEditable(record)) {
      editable += 'disabled';
    }
    var html = '<button class="pull-right" id="' + record.id + '">'
    html += '<i class="fa ' + faIcon + '"></i></button>';
    // TODO: using the icon as a switch, should probably just have a flag
    if (faIcon == 'fa-minus') {
      html += '<button class="pull-right" data-record="' + record.id + '"' + editable + '>';
      html += '<i class="fa fa-edit"></i></button>';
    }
    html += '<p class="metadata_heading">' + record[definition.displayFields[0]] + '</p>';
    for (var i = 1; i < definition.displayFields.length; i++) {
      html += '<p class=>' + record[definition.displayFields[i]] + '</p>';
    }
    return '<li>' + html + '</li>';
  };

  // NOTE: This is used on the publish summary
  // TODO: Refactor?
  this.renderSummary = function(record) {
    var record = applyOverrides(record);
    var html = '';
    definition.displayFields.forEach(function(field){
      html += '<span>' + record[field] +'</span>';
    });
    return '<div>' + html + '</div>';
  }

  drawList($selectedUl, selectedList, 'fa-minus');
}