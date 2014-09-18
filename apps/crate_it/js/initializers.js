function loadTemplateVars() {
  templateVars = {};
  $('#hidden_vars').children().each(function() {
    var $el = $(this);
    var key = $el.attr('id');
    var value = $el.text();
    if (!isNaN(value)) {
      value = +value;
    }
    templateVars[key] = value;
  });
}


function drawCrateContents() {
  // TODO: maybe get rid of this and just use reloadCrateData
  var c_url = OC.generateUrl('apps/crate_it/crate/get_items?crate_id={crateName}', {
    'crateName': templateVars['selected_crate']
  });
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


function initCrateActions() {

  var metadataEmpty = function() {
    var result = false;
    $('.metadata').each(function() {
      if ($(this).text() === '') {
        result = true;
      }
    });
    return result;
  };
  
  var checkCrate = function() {
      $('#result-message').text('');
      $('#check-results-table').empty();
      var c_url = OC.generateUrl('apps/crate_it/crate/check');
      $.ajax({
          url: c_url,
          type: 'get',
          dataType: 'json',
          async: false,
          success: function(data) {
              $('#result-message').text(data.msg);
              res = data.result;
              var key;
              for (key in res) {
                newRow = '<tr><td>' + key + '</td></tr>';
                 $("#check-results-table").last().append(newRow); 
              }
              
          },
          error: function(data) {
              // TODO Format errors
          }
      });
  };

  var crateEmpty = function() {
    return $tree.tree('getNodeById', 'rootfolder').children.length == 0;
  };

  var createCrate = function() {
    var params = {
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
        displayError(jqXHR.responseJSON.msg); // TODO: Make sure all ajax errors are this form instrad of data.msg
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

  var downloadCrate = function() {
    if (treeHasNoFiles()) {
      displayNotification('No items in the crate to package');
      return;
    }

    displayNotification('Your download is being prepared. This might take some time if the files are big');
    var c_url = OC.generateUrl('apps/crate_it/crate/downloadzip?requesttoken={requesttoken}', {requesttoken: oc_requesttoken});
    window.location = c_url;
  };

  $('#checkCrateModal').on('hide.bs.modal', function() {
    location.reload();
  });
  
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
    $(this).find('.btn-primary').prop('disabled', true);
  });

  $('#clearCrateModal').find('.btn-primary').click(function() {
    var children = $tree.tree('getNodeById', 'rootfolder').children;
    // NOTE: The while loop is a workaround to the forEach loop inexplicably skipping
    // the first element
    while (children.length > 0) {
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

  $('#check').click(checkCrate);

  $('#crates').change(function() {
    var id = $(this).val();
    var c_url = OC.generateUrl('apps/crate_it/crate/get_items?crate_id={crateName}', {
      crateName: id
    });
    $.ajax({
      url: c_url,
      type: 'get',
      dataType: 'json',
      async: false, // TODO: why not async?
      success: function(data) {
        manifest = data;
        reloadCrateData(data);
      },
      error: function(data) {
        displayError(data.statusText);
      }
    });
  });

  $('#download').click(downloadCrate);


  var publishCrate = function(crateName, endpoint, collection){
    var c_url = OC.generateUrl('apps/crate_it/crate/publish');
    // TODO: Delete the following, just used for testing because the test server
    //       wont change it's url from localhost
    // collection = collection.replace('localhost', '10.0.2.2');
    var postData = {
      'name': crateName,
      'endpoint': endpoint,
      'collection': collection
    };
    $.ajax({
      url: c_url,
      type: 'post',
      data: postData,
      dataType: 'json',
      success: function(data) {
        confirmPublish(data.msg);
      },
      error: function(jqXHR) {
        confirmPublish(jqXHR.responseJSON.msg);
      }
    });
  };


  var confirmPublish = function(msg) {
    $('#publish-confirm-status').text(msg);
    $('#publishConfirmModal').modal('show');
    $('#publish-confirm-email-send').click(function(){
      var c_url = OC.generateUrl('apps/crate_it/crate/email');
      $.ajax({
        url: c_url,
        type: 'post',
        data: {address: $('#publish-confirm-email').val() },
        dataType: 'json',
        success: function(data) {
          $('#publish-confirm-email-status').text(data.msg);
        },
        error: function(jqXHR) {
          $('#publish-confirm-email-status').text(jqXHR.responseJSON.msg);
        }
      });
    });
  };

  var $publishConfirmModal = $('#publishConfirmModal');
  var publishConfirmValidator = new CrateIt.Validation.FormValidator($publishConfirmModal);
  publishConfirmValidator.addValidator($('#publish-confirm-email'), new CrateIt.Validation.EmailValidator());

  $('#publish').click(function() {
    // TODO: Migrate to a single  client side shared model of the manifest
    // TODO: let this be handled by the search managers perhaps?
    $('#publish-consistency').text('');
    $('#publish-consistency-table').empty();
    var c_url = OC.generateUrl('apps/crate_it/crate/check');
    $.ajax({
        url: c_url,
        type: 'get',
        dataType: 'json',
        async: false,
        success: function(data) {
          var inconsistencies = Object.keys(data.result);
          if(inconsistencies.length > 0) {
            $('#publish-consistency').text(data.msg);
            for(var i = 0; i < inconsistencies.length ; i++) {
              $("#publish-consistency-table").last().append('<tr><td>' + inconsistencies[i] + '</td>/tr>'); 
            }
          }
        },
        error: function(jqXHR) {
          $('#publish-consistency').text('Unable ot determine crate consistency');
        }
    });
    
    $('#publish-description').text($('#description').text());

    $('#publish-creators').children().remove();
    // TODO: create proper render functions
    var records = CreatorSearchManager.getSelected();
    records.forEach(function(record){
      var html = CreatorSearchManager.renderSummary(record);
      $('#publish-creators').append(html);
    });

    $('#publish-activities').children().remove();
    records = ActivitySearchManager.getSelected();
    records.forEach(function(record){
      var html = ActivitySearchManager.renderSummary(record);
      $('#publish-activities').append(html);
    });

  });

  if($('#publish-collection > option').length == 0) {
    $('#publish-collection').next().css('display','inline');
    $('#publishModal').find('.btn-primary').prop('disabled', true);
  } else {
    $('#publish-collection').next().css('display','none');
    $('#publishModal').find('.btn-primary').prop('disabled', false);
  }

  $('#publishModal').find('.btn-primary').click(function() {
    var crateName = $('#crates').val();
    var endpoint = $('#publish-collection option:selected').attr('data-endpoint');
    var collection = $('#publish-collection').val();
    publishCrate(crateName, endpoint, collection);
    $('#publishModal').modal('hide');
  });

  $('#userguide').click(function(event) {
     event.preventDefault();
     window.open($(this).attr("href"), "popupWindow", "width=600,height=600,scrollbars=yes");
  });

}


function setupDescriptionOps() {

  $('#crate_input_description').keyup(function() {
    var description_length = templateVars['description_length'];
    if ($(this).val().length > description_length) {
      $("#crate_description_validation_error").text('Crate Description has reached the limit of 6,000 characters');
      $("#crate_description_validation_error").show();
      $(this).val($(this).val().substr(0, description_length));
    } else {
      $("#crate_description_validation_error").text('');
    }
  });

  $('#edit_description').click(function(event) {
    var old_description = $('#description').text();
    $('#description').text('');
    $('#description').html('<textarea id="crate_description" maxlength="' + description_length + '" style="width: 40%;" placeholder="Enter a description of the research data package for this Crate">' + old_description + '</textarea><br/><div id="edit_description_validation_error" style="color:red;"></div><input id="save_description" type="button" value="Save" /><input id="cancel_description" type="button" value="Cancel" />');
    setupEditDesriptionOp();
    $('#edit_description').addClass('hidden');
    $('#save_description').click(function(event) {
      var c_url = OC.generateUrl('apps/crate_it/crate/update');
      $.ajax({
        url: c_url,
        type: 'post',
        dataType: 'json',
        data: {
          'field': 'description',
          'value': $('#crate_description').val()
        },
        success: function(data) {
          $('#description').html('');
          $('#description').text(data.description);
          $('#edit_description').removeClass('hidden');
          calulateHeights();
        },
        error: function(data) {
          displayError(data.statusText);
        }
      });
    });
    $('#cancel_description').click(function(event) {
      
      //$('#description').html('');
      // var escaped = $('<div>').text(old_description).text();
      //$('#description').html(escaped.replace(/\n/g, '<br />'));     
      //$('#edit_description').removeClass('hidden');
        
      $('#description').html('');
      $('#description').text(old_description);
      $('#edit_description').removeClass('hidden');
    });
  });
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
      'identifier': 'dc_identifier',
      'name': ['Honorific', 'Given_Name', 'Family_Name'],
      'email': 'Email'
    },
    displayFields: ['name', 'email'],
    editFields: ['name', 'email', 'identifier'],
    editableRecords: ['manual', 'mint']
  };

  // TODO: a lot of these elements could be pushed into the SearchManager constructor
  //      so it creates the widget
  var creatorSelectedList = manifest.creators;
  var creator$resultsUl = $('#search_people_results');
  var creator$selectedUl = $('#selected_creators');
  var creator$notification = $('#creators_search_notification');
  var creator$editModal = $('#editCreatorsModal');

  // TODO: for add it's 'creator', but edit it's 'creators'
  // logic works on field name, so make them call creators
  var editCreatorValidator = new CrateIt.Validation.FormValidator(creator$editModal);
  editCreatorValidator.addValidator($('#edit-creators-name'), new CrateIt.Validation.RequiredValidator('Name'));
  editCreatorValidator.addValidator($('#edit-creators-name'), new CrateIt.Validation.MaxLengthValidator('Name', 256));

  editCreatorValidator.addValidator($('#edit-creators-email'), new CrateIt.Validation.RequiredValidator('Email'));
  editCreatorValidator.addValidator($('#edit-creators-email'), new CrateIt.Validation.MaxLengthValidator('Email', 128));
  editCreatorValidator.addValidator($('#edit-creators-email'), new CrateIt.Validation.EmailValidator());
  
  var editCreatorUrlValidator = new CrateIt.Validation.UrlValidator();
  editCreatorValidator.addValidator($('#edit-creators-identifier'), new CrateIt.Validation.MaxLengthValidator('Identifier', 2000));
  editCreatorValidator.addValidator($('#edit-creators-identifier'), new CrateIt.Validation.OptionalValidator(editCreatorUrlValidator));

  // TODO: add this to a namespace rather than exposing globally
  CreatorSearchManager = new SearchManager(creatorDefinition, creatorSelectedList, creator$resultsUl, creator$selectedUl, creator$notification, creator$editModal);
  $('#search_people').click(function() {
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
    $('#clearMetadataField').text('creators');
    attachModalHandlers($clearMetadataModal, CreatorSearchManager.clearSelected);
  });

  var addCreator = function() {
    var name = $('#add-creator-name').val();
    var email = $('#add-creator-email').val();
    var identifier = $('#add-creator-identifier').val();
    var overrides = {
      'name': name,
      'email': email,
      'identifier': identifier
    };
    CreatorSearchManager.addRecord(overrides);
  };
  var $addCreatorModal = $('#addCreatorModal');
  var $addCreatorConfirm = $addCreatorModal.find('.btn-primary');

  var addCreatorValidator = new CrateIt.Validation.FormValidator($addCreatorModal);
  addCreatorValidator.addValidator($('#add-creator-name'), new CrateIt.Validation.RequiredValidator('Name'));
  addCreatorValidator.addValidator($('#add-creator-name'), new CrateIt.Validation.MaxLengthValidator('Name', 256));

  addCreatorValidator.addValidator($('#add-creator-email'), new CrateIt.Validation.RequiredValidator('Email'));
  addCreatorValidator.addValidator($('#add-creator-email'), new CrateIt.Validation.MaxLengthValidator('Email', 128));
  addCreatorValidator.addValidator($('#add-creator-email'), new CrateIt.Validation.EmailValidator());

  var addCreatorUrlValidator = new CrateIt.Validation.UrlValidator();
  addCreatorValidator.addValidator($('#add-creator-identifier'), new CrateIt.Validation.MaxLengthValidator('Identifier', 2000));
  addCreatorValidator.addValidator($('#add-creator-identifier'), new CrateIt.Validation.OptionalValidator(addCreatorUrlValidator));

  // TODO: this doesn't need to be dynamically attached, maybe create a second helper
  $('#add-creator').click(function() {
    attachModalHandlers($addCreatorModal, addCreator);
  });

  var activityDefinition = {
    manifestField: 'activities',
    actions: {
      search: 'activities'
    },
    mapping: {
      'id': 'id',
      'identifier': 'dc_identifier',
      'title': 'dc_title',
      'date': 'dc_date',
      'institution': 'foaf_name',
      'grant_number': 'grant_number',
      'date_submitted': 'dc_date_submitted',
      'description' : 'dc_description',
      'contributors' :  'dc_contributor',
      'repository_name' : 'repository_name',
      'repository_type' : 'repository_type',
      'oai_set' : 'oai_set',
      'format' : 'dc_format',
      'display_type' : 'display_type',
      'subject' : 'dc_subject'
       
    },
    displayFields: ['grant_number', 'date', 'title'],
    editFields: ['grant_number', 'date', 'title', 'institution'],
    editableRecords: ['manual']
  };

  var activitySelectedList = manifest.activities;
  var activity$resultsUl = $('#search_activity_results');
  var activity$selectedUl = $('#selected_activities');
  var activity$notification = $('#activites_search_notification');
  var activity$editModal = $('#editActivitiesModal');
  var editActivityValidator = new CrateIt.Validation.FormValidator(activity$editModal);
  editActivityValidator.addValidator($('#edit-activities-grant_number'), new CrateIt.Validation.RequiredValidator('Grant number'));
  editActivityValidator.addValidator($('#edit-activities-grant_number'), new CrateIt.Validation.MaxLengthValidator('Grant number', 256));

  editActivityValidator.addValidator($('#edit-activities-date'), new CrateIt.Validation.RequiredValidator('Year'));
  editActivityValidator.addValidator($('#edit-activities-date'), new CrateIt.Validation.YearValidator());

  editActivityValidator.addValidator($('#edit-activities-institution'), new CrateIt.Validation.RequiredValidator('Institution'));
  editActivityValidator.addValidator($('#edit-activities-institution'), new CrateIt.Validation.MaxLengthValidator('Institution', 256));

  editActivityValidator.addValidator($('#edit-activities-title'), new CrateIt.Validation.RequiredValidator('Title'));
  editActivityValidator.addValidator($('#edit-activities-title'), new CrateIt.Validation.MaxLengthValidator('Title', 256));


  // TODO: add this to a namespace rather than exposing globally
  ActivitySearchManager = new SearchManager(activityDefinition, activitySelectedList, activity$resultsUl, activity$selectedUl, activity$notification, activity$editModal);

  $('#search_activity').click(function() {
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
    var overrides = {
      'grant_number': grant_number,
      'date': date,
      'title': title,
      'institution': institution
    };
    ActivitySearchManager.addRecord(overrides);
  };

  // TODO: Naming inconsistency here between 'grants' and activities
  var $addActivityModal = $('#addGrantModal');

  var addGrantValidator = new CrateIt.Validation.FormValidator($addActivityModal);
  addGrantValidator.addValidator($('#add-grant-number'), new CrateIt.Validation.RequiredValidator('Grant number'));
  addGrantValidator.addValidator($('#add-grant-number'), new CrateIt.Validation.MaxLengthValidator('Grant number', 256));

  addGrantValidator.addValidator($('#add-grant-year'), new CrateIt.Validation.RequiredValidator('Year'));
  addGrantValidator.addValidator($('#add-grant-year'), new CrateIt.Validation.YearValidator());

  addGrantValidator.addValidator($('#add-grant-institution'), new CrateIt.Validation.RequiredValidator('Institution'));
  addGrantValidator.addValidator($('#add-grant-institution'), new CrateIt.Validation.MaxLengthValidator('Institution', 256));

  addGrantValidator.addValidator($('#add-grant-title'), new CrateIt.Validation.RequiredValidator('Title'));
  addGrantValidator.addValidator($('#add-grant-title'), new CrateIt.Validation.MaxLengthValidator('Title', 256));


  $('#add-activity').click(function() {
    attachModalHandlers($addActivityModal, addActivity);
  });

}


function initAutoResizeMetadataTabs() {
  $('#meta-data').on('show.bs.collapse', function(e) {
    $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    calulateHeights();
  });
  $('#meta-data').on('hide.bs.collapse', function(e) {
    $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
    calulateHeights();
  });

  $(window).resize(function() {
    calulateHeights();
  });
}