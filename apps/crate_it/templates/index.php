<div id="container" class="crateit">

  <div class="bar-actions">

    <img class="icon svg" src="/owncloud/apps/crate_it/img/milk-crate-dark.png">

    <a id="create" class="button" data-toggle="modal" data-target="#createCrateModal">
      <i class="fa fa-plus"></i>
    </a>
    <label for="crates" class="element-invisible">Crate Selector</label>
    <select id="crates">
      
      <?php foreach($_['crates'] as $crate) {   
          if($_['selected_crate'] == $crate) { ?>
            <option id="<?php p($crate) ?>" value="<?php p($crate) ?>" selected>
         <?php } else { ?>
            <option id="<?php p($crate) ?>" value="<?php p($crate) ?>">
        <?php }
          p($crate) ?>
        </option>
      <?php } ?>
    </select>

    <div class="pull-right">

      <a id="publish" class="button" data-toggle="modal" data-target="#publishModal">
        <i class="fa fa-envelope"></i>
        Submit
      </a>

      <a id="check" class="button" data-toggle="modal" data-target="#checkCrateModal">
         <i class="fa fa-check-circle"></i>
         Check Crate
      </a>  
      
      <div class="btn-group">      
        <button type="button" class="dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-download"></i>
          Download
        </button>
        <ul class="dropdown-menu">
          <?php if($_['previews'] == "on") { ?>
            <li>
              <a id="epub" class="dropdown-btn" href="crate/epub">
                <i class="fa fa-book"></i>
                 ePub
              </a>
            </li>
          <?php } ?>
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

      <div class="btn-group">      
        <button type="button" class="dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-question"></i>
          Help
        </button>
        <ul class="dropdown-menu" style="right: 0;left: auto;">
            <li>
              <a id="help_button" class="dropdown-btn" data-toggle="modal" data-target="#helpModal">
                <i class="fa fa-question"></i>
                 About
              </a>
            </li>
          <li>
            <a id="userguide" href="<?php p($_['help_url']) ?>" class="dropdown-btn">
              <i class="fa fa-book"></i>
               User Guide
            </a>
          </li>
        </ul>
      </div>
    </div>

  </div>

</div>


<div id="files"></div>
<?php

  print_unescaped($this->inc('metadata'));       
  print_unescaped($this->inc('help_modal'));   
  print_unescaped($this->inc('publish_modal'));   
  print_unescaped($this->inc('create_crate_modal'));   
  print_unescaped($this->inc('remove_crate_modal'));   
  print_unescaped($this->inc('rename_item_modal'));   
  print_unescaped($this->inc('rename_crate_modal'));   
  print_unescaped($this->inc('add_folder_modal'));   
  print_unescaped($this->inc('clear_crate_modal'));   
  print_unescaped($this->inc('delete_crate_modal'));   
  print_unescaped($this->inc('clear_metadata_modal'));  
  print_unescaped($this->inc('add_creator_modal'));
  print_unescaped($this->inc('edit_creator_modal'));
  print_unescaped($this->inc('add_grant_modal'));
  print_unescaped($this->inc('edit_activities_modal'));
  print_unescaped($this->inc('check_crate_modal'));
  print_unescaped($this->inc('publish_confirm_modal'));
  print_unescaped($this->inc('javascript_vars'));

?>