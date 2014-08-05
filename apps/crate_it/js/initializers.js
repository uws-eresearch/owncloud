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


function setupDescriptionOps() {
    
  $('#crate_input_description').keyup(function() {
    var description_length = templateVars['description_length'];  
    if ($(this).val().length > description_length) {
      $("#crate_description_validation_error").text('Crate Description has reached the limit of 6,000 characters');
      $("#crate_description_validation_error").show();
      $(this).val($(this).val().substr(0, description_length));
    }
    else {
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
    $('#clearMetadataField').text('creators');
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

  var addCreatorValidator = new CrateIt.Util.FormValidator($addCreatorModal);
  addCreatorValidator.addValidator($('#add-creator-name'), new CrateIt.Util.RequiredValidator('Name'));
  addCreatorValidator.addValidator($('#add-creator-name'), new CrateIt.Util.MaxLengthValidator('Grant number', 256));

  addCreatorValidator.addValidator($('#add-creator-email'), new CrateIt.Util.RequiredValidator('Email'));
  addCreatorValidator.addValidator($('#add-creator-email'), new CrateIt.Util.MaxLengthValidator('Email', 128));
  addCreatorValidator.addValidator($('#add-creator-email'), new CrateIt.Util.EmailValidator());

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

  var addGrantValidator = new CrateIt.Util.FormValidator($addActivityModal);
  addGrantValidator.addValidator($('#add-grant-number'), new CrateIt.Util.RequiredValidator('Grant number'));
  addGrantValidator.addValidator($('#add-grant-number'), new CrateIt.Util.MaxLengthValidator('Grant number', 256));

  addGrantValidator.addValidator($('#add-grant-year'), new CrateIt.Util.RequiredValidator('Grant number'));
  addGrantValidator.addValidator($('#add-grant-year'), new CrateIt.Util.YearValidator());
  
  addGrantValidator.addValidator($('#add-grant-institution'), new CrateIt.Util.RequiredValidator('Institution'));
  addGrantValidator.addValidator($('#add-grant-institution'), new CrateIt.Util.MaxLengthValidator('Institution', 256));

  addGrantValidator.addValidator($('#add-grant-title'), new CrateIt.Util.RequiredValidator('Title'));
  addGrantValidator.addValidator($('#add-grant-title'), new CrateIt.Util.MaxLengthValidator('Title', 256));


  $('#add-activity').click(function() {
    attachModalHandlers($addActivityModal, addActivity);
  })

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