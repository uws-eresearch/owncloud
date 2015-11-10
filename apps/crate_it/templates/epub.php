<html>
  <body>
    <h1>Table of Contents</h1>
    <p>
      <?php foreach($_['files'] as $file) { ?>
        <a href="file://<?php p($_['file.preview']) ?>"><?php p($_['file.name']) ?></a><br>
      <?php } ?>
    </p>
  </body>
</html>
