<div class="modal" id="publishModal" tabindex="-1" role="dialog" aria-labelledby="publishModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="publishModalLabel">Submit Crate</h2>
        <p>
          <br />
          Please review the metadata associated with your Crate as detailed below. <br />
          Click Submit to proceed or click Cancel to return to your Crate and update your metadata.
        </p>
      </div>
      <div class="modal-body">
        <section class="publish-body" id="collection-choice">
          <h3>Select Collection</h3>
          <label for="publish-collection" class="element-invisible">Select Collection</label>
          <select id="publish-collection">
            <?php foreach($_['collections'] as $endpoint => $settings) {
              foreach($settings as $collection => $href) { ?>
                <option value="<?php p($href) ?>" data-endpoint="<?php p($endpoint) ?>">
                  <?php p($collection) ?>
                </option>
              <?php }
                } ?>
          </select>
          <label style="color:red;display:none">Error: No collections available for publishing</label>
        </section>
        <div class="row publish-meta">
          <div class="col-4">
             <h4 class="margin-bottom">Description</h4>
             <h6>Crate Size: <span id="crate_size_human_publish" class="standard"></span></h6>
             <div id='publish-description'></div>
          </div>

          <div class="col-4">
            <h4 class="margin-bottom">Creators</h4>
            <ul id="publish-creators">
            </ul>
          </div>

          <div class="col-4">
            <h4 class="margin-bottom">Grant Numbers</h4>
            <ul id="publish-activities">
            </ul>
          </div>

          <div class="col-4">
            <h4 class="margin-bottom">Data Retention Period</h4>
            <div id='publish-data-retention-period'></div>

            <h4 class="margin-top margin-bottom">Embargo Details</h4>
            <div>
              <h6>Embargo Enabled:</h6>
              <div id="publish-embargo-enabled"></div>
            </div>
            <div>
              <h6>Embargo Date:</h6>
              <div id="publish-embargo-date"></div>
            </div>
            <div>
              <h6>Embargo Note:</h6>
              <div id="publish-embargo-note"></div>
            </div>
          </div>
        </div>
          
        <div style="color:red; font-weight:bold;">
          <span id="publish-consistency"></span>
          <table id="publish-consistency-table" class="table table-striped"></table>
        </div>
      </div>
      <div class="modal-body">
        <strong>NOTE:</strong> For the submission of data sets (i.e. Crates) larger than 16Gbytes you are advised to contact a Data Librarian for assistance by sending an email to
        <a href="mailto:researchdata@newcastle.edu.au">researchdata@newcastle.edu.au</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>

<style>

</style>