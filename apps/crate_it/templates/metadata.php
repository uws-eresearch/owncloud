<div class="container-metadata">

    <div class="panel-group" id="meta-data">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#meta-data" href="#crate-information" id="crate-information-head">
                        Crate Information
                        <i class="pull-right fa fa-caret-down"></i>
                    </a>
                </h4>
            </div>

            <div id="crate-information" class="panel-collapse collapse in standard">
                <div class="panel-body">
                    <div id='description_box'>
                        <h6>
                            Description
                            <button id="edit_description" class="pull-right trans-button" type="button"
                                    placeholder="Edit"><i class="fa fa-edit"></i></button>
                        </h6>
                        <div id="description" style="white-space: pre-wrap;"
                             class="metadata"><?php p($_['description'].trim()) ?></div>
                    </div>
                    <div class='crate-size'>
                        <h6 class="info">
                            Crate Size: <span id="crate_size_human" class="standard"></span>
                        </h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#meta-data" href="#data-creators" id="data-creators-head">
                        Data Creators
                        <i class="pull-right fa fa-caret-up"></i>
                    </a>
                </h4>
            </div>

            <div id="data-creators" class="panel-collapse collapse standard">
                <div class="panel-body">
                    <div id="creators_box" class="data-creators">
                        <h6>Selected Data Creators (<span id="creators_count"></span>)
                            <button id="clear_creators" class="pull-right trans-button" type="button">
                                <i class="fa fa-times muted"></i>
                            </button>
                        </h6>
                        <ul id="selected_creators" class="metadata"></ul>
                        <h6>Add New Data Creators
                            <button id="add-creator" class="pull-right trans-button" type="button"
                                    data-toggle="modal" data-target="#addCreatorModal">
                                <i class="fa fa-plus muted"></i>
                            </button>
                        </h6>
                        <div id="search_people_box" class="input-group">
                            <label for="keyword_creator" class="element-invisible">Search Creators</label>
                            <input id="keyword_creator" class="form-control" type="text" name="keyword"
                                   placeholder="Search Creators..."/>
                            <span class="input-group-btn">
                                <button id="search_people" class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                        <span id="creators_search_notification"></span>
                        <div id="search_people_result_box">
                            <ul id="search_people_results"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#meta-data" href="#grant-numbers" id="grant-numbers-head">
                        Grants
                        <i class="pull-right fa fa-caret-up"></i>
                    </a>
                </h4>
            </div>

            <div id="grant-numbers" class="panel-collapse collapse standard">
                <div class="panel-body">
                    <div id="activities_box" class="grant-numbers">
                        <h6>Selected Grants (<span id="activities_count"></span>)
                            <button id="clear_grant_numbers" class="pull-right trans-button" type="button">
                                <i class="fa fa-times muted"></i>
                            </button>
                        </h6>
                        <ul id="selected_activities" class="metadata">
                            <!-- TODO: Be more consistent with naming, are they "grants" or "activities"? -->
                        </ul>
                        <h6>Add New Grants
                            <button id="add-activity" class="pull-right trans-button" type="button"
                                    data-toggle="modal" data-target="#addGrantModal">
                                <i class="fa fa-plus muted"></i>
                            </button>
                        </h6>
                        <div id="search_activity_box" class="input-group">
                            <label for="keyword_activity" class="element-invisible">Search Grants</label>
                            <input id="keyword_activity" class="form-control" type="text" name="keyword_activity"
                                   placeholder="Search Grants..."/>
                            <span class="input-group-btn">
                                <button id="search_activity" class="btn btn-default" type="button"
                                        value="Search Grant Number">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                        <span id="activites_search_notification"></span>

                        <div id="search_activity_result_box">
                            <ul id="search_activity_results"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#meta-data" href="#data-retention-period" id="data-retention-period-head">
                        Data Retention Period
                        <i class="pull-right fa fa-caret-up"></i>
                    </a>
                </h4>
            </div>
            <div id="data-retention-period" class="panel-collapse collapse standard">
                <div class="panel-body">
                    <div id='retention_peroid_list'>
                        <h6>
                            DATA RETENTION PERIOD (YEARS)
                            <button id="choose_retention_period" class="pull-right trans-button" type="button"
                                    placeholder="Choose"><i class="fa fa-edit"></i></button>
                        </h6>
                        <div id="retention_period_value" style="white-space: pre-wrap;"
                             class="metadata"><?php p($_['data_retention_period']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#meta-data" href="#embargo-details" id="embargo-details-head">
                        Embargo Details
                        <i class="pull-right fa fa-caret-up"></i>
                    </a>
                </h4>
            </div>
            <div id="embargo-details" class="panel-collapse collapse standard">

                <div id="embargo-details-submission-modal" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Cannot save embargo details</h4>
                            </div>
                            <div class="modal-body">
                                The following errors are preventing the embargo details from being saved:
                                <ul id="embargo-details-modal-ul">
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-body">
                    <h6>EMBARGO DETAILS
                        <button id="choose_embargo_details" class="pull-right trans-button" type="button" placeholder="Choose"><i class="fa fa-edit"></i></button>
                    </h6>

                    <div id="edit_embargo_details" class="metadata hidden">

                        <div>
                            Embargo Enabled
                            <div>
                                <input id="embargo_enabled_yes" type="radio" name="embargo_enabled" <?php p($_['embargo_enabled'] === 'true' ? 'checked' : '') ?>>
                                <label for="embargo_enabled_yes">Yes</label>
                                <input id="embargo_enabled_no"  type="radio" name="embargo_enabled" <?php p($_['embargo_enabled'] === 'false' ? 'checked' : '') ?>>
                                <label for="embargo_enabled_no">No</label>
                            </div>
                        </div>

                        <br/>

                        <div class='input-append date' id='embargo_datetime_picker_button'>
                            Embargo Date
                            <div>
                                <input id="embargo_date" name="embargo_date" type='text' data-format="yyyy-MM-dd" disabled="disabled" value="<?php p($_['embargo_date']) ?>">
                                <span class="add-on">
                                    <i id="embargo_datetimepicker_icon" class="fa fa-calendar embargo_datetime_icon"></i>
                                </span>
                            </div>
                        </div>

                        <br/>

                        <div>
                            Embargo Details
                            <div>
                                <textarea id="embargo_details" name="embargo_details" placeholder="Enter a details of what the embargo restrictions are"><?php p($_['embargo_details']) ?></textarea>
                            </div>
                        </div>
                        <br/>

                        <input id="save_embargo" type="button" value="Save">
                        <input id="cancel_embargo" type="button" value="Cancel">

                    </div>

                    <div id="embargo-summary">
                        <div class='embargo-enabled'>
                            <h6 class="info">
                                Embargo Enabled <?php p($_['embargo_enabled']) ?>
                            </h6>
                            <span id="embargo_enabled" class="standard"><?php if ($_['embargo_enabled']) {p($_['embargo_enabled'] === 'true' ? 'Yes' : 'No');} ?></span>
                        </div>

                        <div class='embargo-until'>
                            <h6 class="info">
                                Embargo Until
                            </h6>
                            <span id="embargo_until" class="standard"><?php p($_['embargo_date']) ?></span>
                        </div>

                        <div class='embargo-info'>
                            <h6 class="info">
                                Embargo Note
                            </h6>
                            <span id="embargo_note" class="standard"><?php echo str_replace("\n", "<br>", $_['embargo_details']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>