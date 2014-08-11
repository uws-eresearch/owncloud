<div class="modal" id="publishModal" tabindex="-1" role="dialog" aria-labelledby="publishModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="publishModalLabel">Publish Crate</h2>
      </div>
      <div class="modal-body">
        <select id="sword_collection">
          {% for collection in collections %}
            <option value="">
              {{ collection }}
            </option>
          {% endfor %}
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
