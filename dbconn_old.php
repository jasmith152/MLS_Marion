<?php
  // Connect to the database server and Select the database
  $dbcnx = mysql_connect("localhost", "naturmls_citrus", "s3llmyLand") or die("<p>Unable to connect to the database at this time.</p>");
  mysql_select_db("naturmls_citrusmls") or die("<p>Unable to locate the database at this time.</p>");
?>
