<html>
  <body>
    <h1>Table of Contents</h1>
    <p>
      <!-- {% for file in files %}-->
      <?php foreach ($_['files'] as $file) { ?>
      	<a href="file://<?php p($file['preview']);?>"><?php p($file['name']);?></a><br>
      <?php }?>
        <!-- <a href="file://{{ file.preview }}">{{ file.name }}</a><br>
      {% endfor %}-->
    </p>
  </body>
</html>
