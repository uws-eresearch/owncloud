{#
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

#}

<div id="container" class="crateit">

  <div class="bar-actions">

    <img class="icon svg" src="/owncloud/apps/crate_it/img/milk-crate-dark.png">

    <a id="create" class="button" data-toggle="modal" data-target="#createCrateModal">
      <i class="fa fa-plus"></i>
    </a>

    <select id="crates">
      <?php foreach($_['crates'] as $crate):?>
        <option id="<?php echo $crate; ?>" value="<?php echo $crate; ?>" <?php
        if ($_['selected_crate'] == $crate) {echo 'selected';
        }
    ?>>
          <?php echo $crate; ?>
        </option>
      <?php endforeach; ?>
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

</div>
{# end div container #}

<div id="files"></div>

{% include 'metadata.php' %}       


{% include 'help_modal.php' %}   

{% include 'publish_modal.php' %}   

{% include 'create_crate_modal.php' %}   

{% include 'remove_crate_modal.php' %}   

{% include 'rename_item_modal.php' %}   

{% include 'rename_crate_modal.php' %}   

{% include 'add_folder_modal.php' %}   

{% include 'clear_crate_modal.php' %}   

{% include 'delete_crate_modal.php' %}   

{% include 'clear_metadata_modal.php' %}   

{% include 'javascript_vars.php' %}   