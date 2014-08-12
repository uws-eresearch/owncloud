<div class="modal" id="publishModal" tabindex="-1" role="dialog" aria-labelledby="publishModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="publishModalLabel">Publish Crate</h2>
      </div>
      <div class="modal-body">
        <section>
          <h3>Collection</h3>
          <select id="publish-collection">
            {% for collection in collections %}
              <option value="">
                {{ collection }}
              </option>
            {% endfor %}
          </select>
        </section>

        <section>
          <div id='publish-description'>
            <h3>Description</h3>
          </div>
        </section>

        <section>
          <h3>Creators</h3>
          <ul id="publish-creators">
          </ul>
        </section>

        <section>
          <h3>Grant Numbers</h3>
          <ul id="publish-activities">
          </ul>
        </section>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Publish</button>
      </div>
    </div>
  </div>
</div>
