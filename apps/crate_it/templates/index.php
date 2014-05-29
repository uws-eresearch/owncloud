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

    <a id="subbutton" class="button" data-toggle="modal" data-target="#newCrateModal">
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
        </a>
      <?php endif; ?>

      <?php if ($_['previews']==="on" ):?>
        <a id="epub" class="button">
          <i class="fa fa-book"></i>
           Publish
        </a>
      <?php endif; ?>

      <a id="download" class="button">
        <i class="fa fa-download"></i>
         Download
      </a>

      <a id="clear" class="button">
        <i class="fa fa-ban"></i>
         Clear
      </a>

      <a id="delete" class="button">
        <i class="fa fa-trash-o"></i>
         Delete
      </a>

      <a id="help_button" class="button"   data-toggle="modal" data-target="#helpModal">
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

          <span id="crateName"><?php echo $_['selected_crate'] ?></span>

          <div id='description_box'>
            <label for="description">Description</label>
            <input id="edit_description" class='pull-right' type="button" value="Edit" />
            <div id="description"><?php echo htmlentities($_['description']) ?></div>
          </div>

          <div class='crate-size'>
            <span>Crate size: </span>
            <span id="crate_size_human"></span>
          </div>

          <div id="creators_box" class='data-creators'>
            <label for="creators">Add Data Creator/s</label>
            <ul id="creators">
              <?php foreach($_['creators'] as $creator):?>
                <li>
                  <button id="creator_<?php echo $creator['creator_id'] ?>" class ="pull-right" type="button" value="Remove">
                    <i class="fa fa-times"></i>
                  </button>
                  <span id="<?php echo $creator['creator_id'] ?>" class="full_name"><?php echo $creator['full_name'] ?></span>
                </li>
              <?php endforeach;?>
            </ul>

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


          <div id="avtivities_box" class="input-group grant-numbers">
            <label for="activities">Add Grant Number/s</label>
            <ul id="activities">
              <?php foreach($_['activities'] as $activity):?>
                <li>
                  <button id="activity_<?php echo $activity['activity_id'] ?>" class="pull-right" type="button" value="Remove">
                    <i class="fa fa-times"></i>
                  </button>
                  <span id="<?php echo $activity['activity_id'] ?>" class="grant_number"><?php echo $activity['grant_number'] ?></span>
                </li>
              <?php endforeach;?>
            </ul>

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

      <footer class="attribution">
        Cr9it has been developed through a collaboration between the University of Newcastle, the University of Western Sydney, and Intersect Australia Ltd.
      </footer>
    </div>



<div>
    <ul id="fileMenu" class="dropdown-menu" role="menu" aria-labelledby="dLabel">
        <li class="add"><a href="#add"><i class=".glyphicon .glyphicon-plus"></i> Add</a></li>
        <li class="rename"><a href="#rename"><i class=".glyphicon .glyphicon-edit"></i> Rename</a></li>
        <li class="divider"></li>
        <li class="delete"><a href="#delete"><i class=".glyphicon .glyphicon-floppy-remove"></i> Delete</a></li>
    </ul>
</div>

<div id="dialog-add" title="Add Folder">
    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>New folder name:</p>
    <input id="add-folder" type="text"></input>
</div>
<div id="dialog-rename" title="Rename Item">
    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>New name:</p>
    <input id="rename-item" type="text"></input>
</div>
<div id="dialog-delete" title="Remove Item">
    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>Remove item from crate?</p>
</div>

<div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="publishModalLabel">Cr8it Help</h2>
      </div>

      <div class="modal-body">
        <section>
          <h3>Create New Data Crate</h3>
          <p>Enter the name of your crate and click <b><i>Submit</i></b></p>
          <p>Select your crate name from the <b><i>default_crate</i></b> dropdown menu</p>
        </section>

        <section>
          <h3>Describe Your Crate</h3>
          <p>Click <b><i>Edit</i></b> to enter a description of the data in your crate. Include information about the research dataset and its characteristics and features</p>
          <p>Click <b><i>Save</i></b></p>
          <p>Search/add the grant ID/number associated with your data if relevant</p>
          <p>Search/add names of Data Creator/s</p>
        </section>

        <section>
          <h3>Add Files to Data Crate</h3>
          <p>Select <b><i>Files</i></b></p>
          <p>Navigate to the file or folder you wish to add</p>
          <p>Hover your mouse over the file/folder and select <b><i>Add to Crate</i></b></p>
          <p>Add all desired files to crate</p>
          <p>Select <b><i>Cr8it</i></b> to view your crate</p>
        </section>

        <section>
          <h3>Delete a Crate</h3>
          <p>Select <b><i>Cr8it</i></b></p>
          <p>Select crate from the <b><i>default_crate</i></b> dropdown menu</p>
          <p>Select <b><i>Delete Crate</i></b></p>
        </section>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
      </div>
    </div>
  </div>
</div>


<!-- workaround to make var avalaide to javascript -->
<div id="hidden_vars" hidden="hidden">
    <span id="description_length"><?php echo $_['description_length']; ?></span>
    <span id="max_sword_mb"><?php echo $_['max_sword_mb'] ?></span>
    <span id="max_zip_mb"><?php echo $_['max_zip_mb'] ?></span>
</div>

<div class="modal" id="publishModal" tabindex="-1" role="dialog" aria-labelledby="publishModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="publishModalLabel">Publish Cr8t</h2>
      </div>
      <div class="modal-body">
        <select id="sword_collection">
          <?php foreach ($_['sword_collections'] as $collection => $href): ?>
            <option value="<?php echo $href?>">
              <?php echo $collection; ?>
            </option>
          <?php endforeach; ?>
        </select>

        <section>
          <h3>Creators</h3>
          <ul id="creators">
            <?php foreach($_['creators'] as $creator):?>
              <li>
                <input id="creator_<?php echo $creator['creator_id'] ?>" type="button" value="Remove" />
                <span id="<?php echo $creator['creator_id'] ?>" class="full_name"><?php echo $creator['full_name'] ?></span>
              </li>
            <?php endforeach;?>
          </ul>
        </section>

        <section>
          <h3>Grant Numbers</h3>
          <ul id="activities">
            <?php foreach($_['activities'] as $activity):?>
              <li>
                <input id="activity_<?php echo $activity['activity_id'] ?>" type="button" value="Remove" />
                <span id="<?php echo $activity['activity_id'] ?>" class="grant_number"><?php echo $activity['grant_number'] ?></span>
              </li>
            <?php endforeach;?>
          </ul>
        </section>

        <div id='#description_box'>
          <label for="description">Description</label>
          <input id="edit_description" type="button" value="Edit" />
          <div id="description"><?php echo htmlentities($_['description']) ?></div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Publish</button>
      </div>
    </div>
  </div>
</div>

<?php include("create_crate_modal.php"); ?>

<div class="modal" id="removeCrateModal" tabindex="-1" role="dialog" aria-labelledby="removeCrateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="removeCrateModalLabel">Remove Item</h4>
      </div>
      <div class="modal-body">

        <p>Remove item from crate?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Remove</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal" id="renameCrateModal" tabindex="-1" role="dialog" aria-labelledby="renameCrateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="renameCrateModalLabel">Remove Item</h4>
      </div>
      <div class="modal-body">
        <p>New Name:</p>
        <input id="rename-item" type="text"></input>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Rename</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
