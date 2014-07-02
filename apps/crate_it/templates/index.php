<!--
  ownCloud - Cr8it App

  @author Lloyd Harischandra
  @copyright 2014 University of Western Sydney www.uws.edu.au

  This library is free software; you can redistribute it and/or
  modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
  License as published by the Free Software Foundation; either
  version 3 of the License, or any later version.

  This library is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU AFFERO GENERAL PUBLIC LICENSE for more details.

  You should have received a copy of the GNU Lesser General Public
  License along with this library.  If not, see <http://www.gnu.org/licenses/>.

 -->

<div id="container" class="crateit">

  <div class="bar-actions">

    <img class="icon svg" src="/owncloud/apps/crate_it/img/milk-crate-dark.png">

    <a id="create" class="button" data-toggle="modal" data-target="#createCrateModal">
      <i class="fa fa-plus"></i>
    </a>

    <select id="crates">
      <?php foreach($_['crates'] as $crate):?>
        <option id="<?php echo $crate; ?>" value="<?php echo $crate; ?>" <?php if($_['selected_crate']==$crate){echo 'selected';}?>>
          <?php echo $crate;?>
        </option>
      <?php endforeach;?>
    </select>

    <div class="pull-right">

      <?php if ($_['sword_status'] === "enabled" ):?>

        <a id="post" class="button" data-toggle="modal" data-target="#publishModal">
          <i class="fa fa-envelope"></i>
          Publish
        </a>
      <?php endif; ?>


      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-download"></i>
          Download
        </button>
        <ul class="dropdown-menu">
          <?php if ($_['previews']==="on" ):?>
            <li>
              <a id="epub" class="dropdown-btn">
                <i class="fa fa-book"></i>
                 ePub
              </a>
            </li>
          <?php endif; ?>
          <li>
            <a id="download" class="dropdown-btn">
              <i class="fa fa-archive"></i>
               Zip
            </a>
          </li>
        </ul>
      </div>

      <a id="clear" class="button" data-toggle="modal" data-target="#clearCrateModal">
        <i class="fa fa-ban"></i>
         Clear
      </a>

      <a id="delete" class="button">
        <i class="fa fa-trash-o"></i>
         Delete
      </a>

      <a id="help_button" class="button" data-toggle="modal" data-target="#helpModal">
        <i class="fa fa-question"></i>
         Help
      </a>
    </div>

  </div>

<!--     <div id="metadata" style="float:right;">
      <?php if ($_['mint_status'] === "enabled" ):?>


        <div id="anzsrc_for">
        <div>
        <select id="for_top_level" class="for_codes">
        <option id="select_top" value="for_top_choose">Choose a code</option>
        <?php foreach($_['top_for'] as $item): $vars=get_object_vars($item); //$prefLabel=$ vars[ 'skos:prefLabel']; ?>
        <option id="<?php echo $vars['rdf:about'];?>" value="<?php echo $vars['rdf:about'];?>">
        <?php echo $vars[ 'skos:prefLabel']?>
      </option>
    <?php endforeach;?>
  </select>
</div>
<div>
<select id="for_second_level" class="for_codes">
<option id="select_second" value="for_second_choose">Choose a code</option>
</select>
</div>
<div>
<select id="for_third_level" class="for_codes">
<option id="select_third" value="for_third_choose">Choose a code</option>
</select>
</div>
</div> -->






            </div>



        <div id="files"></div>

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

                      <h6>Selected Data Creators (<?php echo count($_['creators']); ?>) <button id="clear_creators" class="pull-right trans-button" type="button" placeholder="Edit"><i class="fa fa-times muted"></i></button></h6>
                      <ul id="creators">
                        <?php if($_['creators']): ?>
                          <?php foreach($_['creators'] as $creator):?>
                            <li>
                              <button id="creator_<?php echo $creator['id'] ?>" class ="pull-right" type="button" value="Remove">
                                <i class="fa fa-minus"></i>
                              </button>
                              <p class="full_name_email"><?php echo $creator['name'] ?></p>
                              <p><?php echo $creator['email'] ?></p>
                            </li>
                          <?php endforeach;?>
                        <?php else: ?>
                          <p class="nothing-selected">No Data Creators selected</p>  
                        <?php endif; ?>
                      </ul>

                    <h6>Select New Data Creators</h6>
                    <div id="search_people_box" class="input-group">
                      <input id="keyword" class="form-control" type="text" name="keyword" placeholder="Search Creators..." />
                      <span class="input-group-btn">
                        <button id="search_people" class="btn btn-default" type="button" placeholder="Search Creators...">
                          <i class="fa fa-search"></i>
                        </button>
                      </span>
                    </div>

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
                    Grant Numbers
                    <i class="pull-right fa fa-caret-up"></i>
                  </a>
                </h4>
              </div>
              <div id="grant-numbers" class="panel-collapse collapse standard">
                <div class="panel-body">
                  
                  <div id="avtivities_box" class="grant-numbers">
                      <h6>
                        Selected Grant Numbers (<?php echo count($_['activities']); ?>)
                       <button id="clear_grant_numbers" class="pull-right trans-button" type="button" placeholder="Edit"><i class="fa fa-times muted"></i></button>
                     </h6>
                      <ul id="activities">
                        <?php if($_['activities']): ?>
                          <?php foreach($_['activities'] as $activity):?>
                            <li>
                              <button id="activity_<?php echo $activity['id'] ?>" class="pull-right" type="button" value="Remove">
                                <i class="fa fa-minus"></i>
                              </button>
                              <p class="grant_number"><?php echo $activity['grant_number'] ?></p>
                              <p><?php echo $activity['date'] ?></p>
                              <p><?php echo $activity['title'] ?></p>
                            </li>
                          <?php endforeach;?>
                        <?php else: ?>
                          <p class="nothing-selected">No Grant Numbers selected</p>  
                        <?php endif; ?>
                      </ul>

                    <h6>Add New Grant Numbers</h6>
                    <div id="search_activity_box" class="input-group">
                      <input id="keyword_activity" class="form-control" type="text" name="keyword_activity" placeholder="Search Grants..."/>
                      <span class="input-group-btn">
                        <button id="search_activity" class="btn btn-default" type="button" value="Search Grant Number">
                          <i class="fa fa-search"></i>
                        </button>
                      </span>
                    </div>

                    <div id="search_activity_result_box">
                      <ul id="search_activity_results">
                      </ul>
                    </div>

                    <?php endif; ?>

                  </div>


                </div>
              </div>
            </div>
          </div>

        </div>

    </div>


<?php include 'help_modal.php'; ?>

<?php include 'publish_modal.php'; ?>

<?php include 'create_crate_modal.php'; ?>

<?php include 'remove_crate_modal.php'; ?>

<?php include 'rename_item_modal.php'; ?>

<?php include 'rename_crate_modal.php'; ?>

<?php include 'add_folder_modal.php'; ?>

<?php include 'clear_crate_modal.php'; ?>

<?php include 'delete_crate_modal.php'; ?>

<?php include 'javascript_vars.php'; ?>
