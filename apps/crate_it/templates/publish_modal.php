<div class="modal" id="publishModal" tabindex="-1" role="dialog" aria-labelledby="publishModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="publishModalLabel">Publish Crate</h2>
      </div>
      <div class="modal-body">
        <section class="publish-body">
          <h3>Select Collection</h3>
          <label for="publish-collection" class="element-invisible">Select Collection</label>
          <select id="publish-collection">
            <?php foreach($_['collections'] as $collection) {
            		foreach ($collection['settings'] as $setting) { ?>
            			<option value="<?php p($setting['href']);?>" data-endpoint="<?php p($collection['endpoint']);?>">
            <?php 	 	p($setting['collection']);
					}
            	  }	?></option>
          </select>
          <label style="color:red;display:none">Error: No collections available for publishing</label>
        </section>
        <!-- another section goes here for publish as dropdown. Anchoring is done using css  -->
        <section class="publish-to">
        	<h3>Publish to: </h3>
        	<select id="publish-within">
        		<option value="public">Published Open Access</option>
        		<option value="private">Mediated Access</option>
        	</select>
        </section>
        <div class="row publish-meta">
          <div class="col-3">
             <h4 class="margin-bottom">Description</h4>
             <h6>Crate Size: <span id="crate_size_human_publish" class="standard"></span></h6>
             <div id='publish-description'></div>
          </div>

          <div class="col-3">
            <h4 class="margin-bottom">Creators</h4>
            <ul id="publish-creators">
            </ul>
          </div>

          <div class="col-3">
            <h4 class="margin-bottom">Grant Numbers</h4>
            <ul id="publish-activities">
            </ul>
          </div>
        </div>
          
        <div style="color:red; font-weight:bold;">
          <span id="publish-consistency"></span>
          <table id="publish-consistency-table" class="table table-striped"></table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Publish</button>
      </div>
    </div>
  </div>
</div>

<style>

</style>