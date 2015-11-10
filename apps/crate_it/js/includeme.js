function recalculateFileTreePosition() {
    var barActionHeight = $('.bar-actions').height();
    var titleHeight = parseInt(barActionHeight);
    $('#files').css('margin-top', titleHeight.toString() + 'px');
}

$(window).resize(recalculateFileTreePosition);


function setupEditDesriptionOp() {
    $('#crate_description').keyup(function () {
        var description_length = templateVars['description_length'];
        if ($(this).val().length > description_length) {
            $("#edit_description_validation_error").text('Crate Description has reached the limit of 6,000 characters');
            $("#edit_description_validation_error").show();
            $(this).val($(this).val().substr(0, description_length));
        } else {
            $("#edit_description_validation_error").text('');
        }
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
    setTimeout(function () {
        OC.Notification.hide();
    }, delayTime);
}


function indentTree() {
    $tree.find('.jqtree-element').each(function () {
        var indent = $(this).parents('li').length * 20;
        $(this).css('padding-left', indent);
        $(this).css('background-position', indent + 20 + 'px 50%');
    });
}

function attachModalHandlers($modal, confirmCallback) {
    var $confirm = $modal.find('.btn-primary');
    var confirmDisabled = $confirm.prop('disabled');

    var clearInput = function () {
        var $input = $modal.find('input');
        if ($input) {
            $input.val('');
        }
        var $label = $modal.find('label');
        if ($label) {
            $label.hide();
        }
    };

    $confirm.click(function () {
        confirmCallback();
        $modal.modal('hide');
    });


    $modal.on('hide.bs.modal', function () {
        $confirm.off('click');
        $confirm.prop('disabled', confirmDisabled);
        clearInput();
    });

    $modal.modal('show');
};


function buildFileTree(data) {

    var createImgUrl = function (node) {
        var icon_set = ['application-epub+zip', 'application-pdf', 'application-rss+xml',
            'application', 'audio', 'calendar', 'database', 'file', 'flash',
            'folder-drag-accept', 'folder-external', 'folder-public', 'folder-shared',
            'folder', 'font', 'image', 'image-svg+xml', 'package-x-generic', 'text-code',
            'text-html', 'text', 'text-vcard', 'text-x-c', 'text-x-h', 'text-x-javascript',
            'text-x-python', 'video', 'web', 'x-office-document',
            'x-office-presentation', 'x-office-spreadsheet'
        ];
        var icon = 'file';
        if (node.id == 'rootfolder') {
            return 'url(' + OC.filePath('crate_it', 'img', 'crate.png') + ')';
        } else if (node.folder || node.id == 'folder') {
            icon = 'folder';
        } else if (mime) {
            var mime_base = node.mime.split('/')[0];
            var mime = node.mime.replace(/\//, '-');
            if ($.inArray(mime, icon_set) > 0) {
                icon = mime;
            } else if ($.inArray(mime_base, icon_set) > 0) {
                icon = mime_base;
            }
        } else {
            icon = 'file';
        }
        return 'url(' + OC.imagePath('core', 'filetypes/' + icon + '.svg') + ')';
    };

    var addFolder = function (node) {
        var $modal = $('#addFolderModal');
        var confirmCallback = function () {
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

    var renameCrate = function (node) {
        var $modal = $('#renameCrateModal');
        var oldName = node.name;
        $('#rename-crate').val(oldName);
        $('#rename-crate').keyup(function () {
            var $input = $('#rename-crate');
            var $error = $('#rename_crate_error');
            var $confirm = $modal.find('.btn-primary');
            validateCrateName($input, $error, $confirm);
        });

        var confirmCallback = function () {
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
                success: function (data) {
                    $('#crates option:selected').val(newCrateName).attr('id', newCrateName).text(newCrateName);
                    var errorMessage = oldName + ' not renamed';
                    // TODO: try to do this withou a page reload
                    saveTree(data.msg, errorMessage, true);
                },
                error: function (jqXHR) {
                    $tree.tree('updateNode', node, oldName);
                    displayError(jqXHR.responseJSON.msg);
                }
            });
        };
        // the successMessage function gets called after the name has changed
        attachModalHandlers($modal, confirmCallback);
    };

    var renameItem = function (node) {
        var $modal = $('#renameItemModal');
        var oldName = node.name; // the successMessage function gets called after the name has changed
        $('#rename-item').val(node.name);
        var confirmCallback = function () {
            var newName = $('#rename-item').val();
            $tree.tree('updateNode', node, newName);
            indentTree();
            var successMessage = 'Renamed ' + oldName + ' to ' + newName;
            var errorMessage = 'error renaming' + oldName;
            saveTree(successMessage, errorMessage);
        };
        attachModalHandlers($modal, confirmCallback);
    };

    var removeItem = function (node) {
        var $modal = $('#removeCrateModal');
        var msg = "Remove item '" + node.name + "' from crate?";
        $modal.find('.modal-body > p').text(msg);
        var confirmCallback = function () {
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
        onCreateLi: function (node, $li) {
            $div = $li.find('.jqtree-element');
            $div.css('background-image', createImgUrl(node));
            $ul = $div.append('<ul class="crate-actions pull-right"></ul>').find('ul');
            // append consistency checker icon
            var valid = node.valid;
            if (valid == 'false') {
                $title = $div.find('.jqtree-title');
                $title.prepend('<i class="fa fa-times" style="color:red;  padding-right: 5px;"></i>');
            }
            else if (valid == 'true') {
                $title = $div.find('.jqtree-title');
                $title.prepend('<i class="fa fa-check" style="color:green; padding-right: 5px;"></i>');
            }
            var type = node.id;
            if (type == 'rootfolder' || type == 'folder') {
                $ul.append('<li><a><i class="fa fa-plus"></i>Add Folder Item</a></li>');
                $ul.find('.fa-plus').parent().click(function () {
                    addFolder(node);
                });
            }
            if (type == 'rootfolder') {
                $div.addClass('rootfolder');
                $ul.append('<li><a><i class="fa fa-pencil"></i>Rename Crate</a></li>');
            } else {
                $ul.append('<li><a><i class="fa fa-pencil"></i>Rename Item</a></li>');
            }
            $ul.find('.fa-pencil').parent().click(function () {
                if (type == 'rootfolder') {
                    renameCrate(node);
                } else {
                    renameItem(node);
                }
            });
            if (type != 'rootfolder') {
                $ul.append('<li><a><i class="fa fa-trash-o"></i>Remove Item</a></li>');
                $ul.find('.fa-trash-o').parent().click(function () {
                    removeItem(node);
                });
            }
        },
        onCanMove: function (node) {
            var result = true;
            // Cannot move root node
            if (!node.parent.parent) {
                result = false;
            }
            return result;
        },
        onCanMoveTo: function (moved_node, target_node, position) {
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

    $tree.bind('tree.open', function (event) {
        saveTree(false);
    });

    $tree.bind('tree.close', function (event) {
        saveTree(false);
    });

    $tree.bind('tree.move', function (event) {
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
    var maxSwordMB = templateVars['max_sword_mb'];
    var maxZipMB = templateVars['max_zip_mb'];
    var publishWarningMB = templateVars['publish_warning_mb'];
    var swordEnabled = templateVars['sword_enabled'].trim() == 'true';
    $.ajax({
        url: 'crate/get_crate_size',
        type: 'get',
        dataType: 'json',
        success: function (data) {
            $('#crate_size_human').text(data['human']);
            $('#crate_size_human_publish').text(data['human']);
            // TODO: Maybe cleaner to do this server-side
            var crate_size_mb = data['size'] / (1024 * 1024);
            var warnings = [];
            var notify = false;
            var disablePublish = false;
            var disableDownload = false;
            // TODO: The max SWORD limit was disabled as a quick fix
            // for publishing taking a long time. Also not that the limits
            // don't seem to prevent large crates from being published.
            // There should also be a check to see if sword is actually enabled in
            // the cr8it_config.json, and perhaps allow other publishers to set limits too
            if (swordEnabled && maxSwordMB > 0 && crate_size_mb > maxSwordMB) {
                warnings.push('exceeds SWORD limit');
                disablePublish = true;
                notify = true;
            }
            if (maxZipMB > 0 && crate_size_mb > maxZipMB) {
                warnings.push('exceeds ZIP file limit');
                disableDownload = true;
                notify = true;
            }
            if (publishWarningMB > 0 && crate_size_mb > publishWarningMB) {
                warnings.push('will cause publishing to take a long time');
                notify = true;
            }
            var msg = 'WARNING: Crate size ' + warnings.join(', and ') + '.';

            if (disablePublish) {
                $('#publish').attr("disabled", "disabled");
            } else {
                $('#publish').removeAttr("disabled");
            }

            if (disableDownload) {
                $('#download').attr("disabled", "disabled");
            } else {
                $('#download').removeAttr("disabled");
            }

            if (notify) {
                displayNotification(msg, 6000);
            }
        },
        error: function (jqXHR) {
            displayError(jqXHR.responseJSON.msg);
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
        submitdata: function (value, settings) {
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
            'fields': [{
                'field': 'vfs',
                'value': $tree.tree('toJson')
            }]
        },
        success: function (data) {
            if (successMessage) {
                displayNotification(successMessage);
                updateCrateSize();
            }
            if (reload) {
                location.reload();
            }
        },
        error: function (data) {
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
    buttonObj.click('click', function (event) {
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
            success: function (data) {
                buttonObj.parent().remove();
            },
            error: function (data) {
                displayError(data.statusText);
            }
        });
    });
}

// TODO: Migrate the clients of the following to the validations.js framework
function validateEmail($input, $error, $confirm) {
    validateTextLength($input, $error, $confirm, 128);
    var email = $input.val();
    var isEmail = function () {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    };
    if (!isEmail()) {
        $confirm.prop('disabled', true);
        $error.text('Not recognised as a valid email address');
        $error.show();
    }
}

function validateYear($input, $error, $confirm) {
    var inputYear = $.trim($input.val());
    var isYear = function () {
        var regex = /^\d{4}$/;
        return regex.test(inputYear);
    };
    var emptyYear = function () {
        return (!inputYear || /^\s*$/.test(inputYear));
    };
    if (emptyYear()) {
        $confirm.prop('disabled', true);
        $error.text('Year can not be blank');
        $error.show();
    } else if (!isYear()) {
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
    var emptyText = function () {
        return (!inputText || /^\s*$/.test(inputText));
    };
    if (emptyText()) {
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

// TODO: See if some of this can make use of the validation framework
function validateCrateName($input, $error, $confirm) {
    var inputName = $input.val();
    var crates = $.map($('#crates > option'), function (el, i) {
        return $(el).attr('id');
    });
    var emptyName = function () {
        return (!inputName || /^\s*$/.test(inputName));
    };
    var existingName = function () {
        return crates.indexOf(inputName) > -1;
    };

    var regex = /[\/\\\<\>:\"\|?\*]/;

    if (existingName() || emptyName()) {
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
    } else if (regex.test(inputName)) {
        $confirm.prop('disabled', true);
        $error.text("Invalid name. Illegal characters '\\', '/', '<', '>', ':', '\"', '|', '?' and '*' are not allowed");
        $error.show();
    } else {
        $confirm.prop('disabled', false);
        $error.hide();
    }
}


//TODO use something like this when the pages loads
function reloadCrateData(manifest) {
    $('#retention_period_value').text(manifest['data_retention_period']);
    updateCrateSize();
    $('#description').text(manifest['description']);
    $('#files').remove();
    $('#container').after('<div id="files"></div>');
    // Make sure edit description icon shows up on startup
    $('#edit_description').removeClass('hidden');
    $('#choose_retention_period').removeClass('hidden');

    if (manifest['embargo_enabled']) {
        $('span#embargo_enabled').html(manifest['embargo_enabled'] === 'true' ? 'Yes' : 'No');
        $('#embargo_enabled_yes').prop("checked", manifest['embargo_enabled'] === 'true');
        $('#embargo_enabled_no').prop("checked", manifest['embargo_enabled'] === 'false');
    } else {
        $('span#embargo_enabled').html('');
        $('#embargo_enabled_yes').prop("checked", false);
        $('#embargo_enabled_no').prop("checked", false);
    }

    if (manifest['embargo_date']) {
        $('span#embargo_until').html(manifest['embargo_date']);
        $('input#embargo_date').val(manifest['embargo_date']);
    } else {
        $('span#embargo_until').html('');
        $('input#embargo_date').val('');
    }

    if (manifest['embargo_details']) {
        $('span#embargo_note').html(manifest['embargo_details'].replace(/\n/g, "<br>"));
        $('textarea#embargo_details').val(manifest['embargo_details']);
    } else {
        $('span#embargo_note').html('');
        $('textarea#embargo_details').val('');
    }

    buildFileTree(manifest);
    indentTree();
    // TODO Have a registry of search managers and loop over them
    CreatorSearchManager.loadManifestData(manifest);
    ActivitySearchManager.loadManifestData(manifest);
}


// TODO: Super hacky blocking synchronous call
// There are many of async calls on page load that could probably all be reduced to this one
function getMaifest() {
    var result = [];
    var c_url = OC.generateUrl('apps/crate_it/crate/get_manifest?crate_id={crateName}', {
        crateName: encodeURIComponent($('#crates').val())
    });
    $.ajax({
        url: c_url,
        type: 'get',
        async: false,
        dataType: 'json',
        data: {
            'action': 'get_manifest'
        },
        success: function (data) {
            result = data;
        },
        error: function (data) {
            displayError(data.statusText);
        }
    });
    return result;
}

function calulateHeights() {
    var tabsHeight = ($('.panel-heading').outerHeight() * ($('.panel-heading').length + 1)) + $('.collapse.info.in .panel-body').outerHeight();
    var height = $('#meta-data').innerHeight() - tabsHeight;
    $('.collapse.standard .panel-body').height(height + 12);
}

$(function () {
    $('#embargo_datetime_picker_button').datetimepicker();
});