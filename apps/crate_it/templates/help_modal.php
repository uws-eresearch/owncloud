<div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="helpModalLabel">Cr8it Help</h2>
      </div>

      <div class="modal-body">

        {{ help | raw }}

        <h2 class="modal-title">About Cr8it</h2>
        <section>
          <p>Cr8it has been developed through a collaboration between the University of Newcastle, the University of Western Sydney, and Intersect Australia Ltd.</p>
        </section>
        <section>
          Release {{ release }} at commit {{ commit }}.
        </section>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
      </div>
    </div>
  </div>
</div>
