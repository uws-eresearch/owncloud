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
          <select id="publish-collection">
            {% for collection, href in collections %}
              <option value="{{ href }}">
                {{ collection }}
              </option>
            {% endfor %}
          </select>
        </section>
        <div class="row publish-meta">
          <div class="col-3">
             <h4 class="margin-bottom">Description</h4>
             <h6>Crate Size: <span id="crate_size_human" class="standard">6.4 MB</span></h6>
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