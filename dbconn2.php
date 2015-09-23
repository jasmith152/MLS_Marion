<?php
// Connect to the database server and Select the database
try {
   $dbcnx = new PDO('mysql:host=localhost;dbname=naturmls_citrusmls', 'naturmls_citrus', 's3llmyLand');
   $dbcnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
   $err_msg .= "Db Error: ".$e->getMessage()."<br />\n";
}
