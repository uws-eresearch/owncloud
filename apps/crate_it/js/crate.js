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

$(document).ready(function() {  
  /*      
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
  */
  drawCrateContents();
 
  max_sword_mb = parseInt($('#max_sword_mb').text());
  max_zip_mb = parseInt($('#max_zip_mb').text());
  crate_size_mb = 0;

  updateCrateSize();

  //initSearchHandlers();

  initCrateActions();
  
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
  var height = $('#meta-data').innerHeight() - tabsHeight;
  $('.collapse.standard .panel-body').height(height + 12);
}