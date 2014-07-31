<div class="container-metadata">
          <div class="panel-group" id="meta-data">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#meta-data" href="#crate-information">
                    Crate Information
                    <i class="pull-right fa fa-caret-down"></i>
                  </a>
                </h4>
              </div>
              
              <div id="crate-information" class="panel-collapse collapse in info">
                <div class="panel-body">
                  <div id='description_box'>
                    <h6>
                      Description
                      <button id="edit_description" class="pull-right trans-button" type="button" placeholder="Edit"><i class="fa fa-edit"></i></button>
                    </h6>
                    <div id="description" class="metadata"><?php echo htmlentities($_['description']) ?></div>
                  </div>
                  <div class='crate-size'>
                    <h6 class="info">Crate Size: <span id="crate_size_human" class="standard"></span></h6>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#meta-data" href="#data-creators">
                    Data Creators
                    <i class="pull-right fa fa-caret-down"></i>
                  </a>
                </h4>
              </div>
              <div id="data-creators" class="panel-collapse collapse in standard">
                <div class="panel-body">

                  
                  <div id="creators_box" class="data-creators">

                      <h6>Selected Data Creators (<span id="creators_count"></span>)
                        <button id="clear_creators" class="pull-right trans-button" type="button">
                          <i class="fa fa-times muted"></i>
                        </button>
                      </h6>

                      <ul id="selected_creators">
                      </ul>

                    <h6>Select New Data Creators
                      <button id="add_creator_number" class="pull-right trans-button" type="button" data-toggle="modal" data-target="#addCreatorModal">
                        <i class="fa fa-plus muted"></i>
                      </button>
                    </h6>
                    <div id="search_people_box" class="input-group">
                      <input id="keyword_creator" class="form-control" type="text" name="keyword" placeholder="Search Creators..." />
                      <span class="input-group-btn">
                        <button id="search_people" class="btn btn-default" type="button">
                          <i class="fa fa-search"></i>
                        </button>
                      </span>
                    </div>
                    <span id="creators_search_notification"></span>
                    <div id="search_people_result_box">
                      <ul id="search_people_results">
                      </ul>
                    </div>

                  </div>

                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#meta-data" href="#grant-numbers">
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
                    <ul id="selected_activities"></ul>
                    <!-- TODO: Be more consistent with naming, are they "grants" or "activities"? -->
                    </ul>


                    <h6>Add New Grants
                      <button id="add_grant_number" class="pull-right trans-button" type="button" data-toggle="modal" data-target="#addGrantModal">
                        <i class="fa fa-plus muted"></i>
                      </button>
                    </h6>
                    <div id="search_activity_box" class="input-group">
                      <input id="keyword_activity" class="form-control" type="text" name="keyword_activity" placeholder="Search Grants..."/>
                      <span class="input-group-btn">
                        <button id="search_activity" class="btn btn-default" type="button" value="Search Grant Number">
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
          </div>

        </div>

    </div>