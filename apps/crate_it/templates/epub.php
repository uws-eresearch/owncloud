<html>
  <body>
    <h1>Table of Contents</h1>
    <p>
      {% for file in files %}
        <a href="file://{{ file.preview }}">{{ file.name }}</a><br>
      {% endfor %}
    </p>
  </body>
</html>
