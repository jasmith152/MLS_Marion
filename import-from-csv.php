<?php
// Establish log file to use
$handle_log = fopen ("/home/naturmls/public_html/marion/import_log.txt", "a");
$log_file = '';
$eol = "\n";

//Include Db connection script
include 'dbconn.php';

//Increase the memory limit while we import our data
ini_set("memory_limit","20M");

//Data files directory
$data_dir = "/home/naturmls/public_html/marion/data/";

//Set array of files to look for
$files = array("property_residentialproperty.csv","property_vacantland.csv","property_commercialproperty.csv","property_multifamily.csv","property_businessopportunity.csv");

$alerts = 0;
$i = 1;
Foreach ($files as $idx_file) {
   //Set variables
   switch ($i) {
      case 1:
         $newname = "idx_residential";
      break;
      case 2:
         $newname = "idx_vacant_land";
      break;
      case 3:
         $newname = "idx_commercial";
      break;
      case 4:
         $newname = "idx_multi_res";
      break;
      case 5:
         $newname = "idx_business_op";
      break;
   }
   //Set some more variables
   $idx_file_clean = $data_dir.$newname.".txt";
   $filename_arr = explode(".", $idx_file);
   $temp_table = "tbl_temp_".$newname;
   $sql_import = "LOAD DATA LOW_PRIORITY INFILE '$idx_file_clean' REPLACE INTO TABLE naturmls_marionmls.$temp_table ";
   //$sql_import .= "() ";
   $sql_import .= "FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 LINES";
   $live_table = "tbl_".$newname;
   $sql_merge = "INSERT INTO $live_table SELECT * FROM ".$temp_table;
   $verify = '1';

   //Remove last line of file & sanitize data
   $log_file .= "--------------------------------------------------------------".$eol;
   $log_file .= date("Y-m-d G:i")." - $idx_file".$eol;
   $log_file .= "Reading & converting raw data from $idx_file.".$eol;
   $raw_data = file_get_contents($data_dir.$idx_file);
   $converted = str_replace("\n</div>","",$raw_data);
   $converted = str_replace("\"- Active\"","A",$converted);

   //Save the file
   $handle = fopen ($idx_file_clean, "w");
   if (!fwrite($handle, $converted)) {
      $log_file .= "Cannot write to file ($idx_file_clean)".$eol;
      $verify = '0';
      //Write to the log file
      if (!fwrite($handle_log, $log_file)) {}
      fclose($handle_log);
      exit;
   }
   fclose($handle);
   $log_file .= "Successfully wrote to $idx_file_clean.".$eol;

   //Delete old data from temp table
   $sql_del1 = "DELETE FROM ".$temp_table;
   if (@mysql_query($sql_del1)) {
      $log_file .= "Old data removed from $temp_table successfully.".$eol;
   } else {
      $log_file .= "Error deleting item: " . mysql_error() . "".$eol;
      $verify = '0';
      //Write to the log file
      if (!fwrite($handle_log, $log_file)) {}
      fclose($handle_log);
      exit;
   }

   //Import data into temp table
   if (mysql_query($sql_import)) {
      $num_imported = mysql_affected_rows($dbcnx);
      $log_file .= $num_imported." records successfully imported into $temp_table.".$eol;
   } else {
      $log_file .= "Error performing query: " . mysql_error() . "".$eol;
      $verify = '0';
      //Write to the log file
      if (!fwrite($handle_log, $log_file)) {}
      fclose($handle_log);
      exit;
   }

   // Replace live data now
   if ($num_imported > 0) {
      $sql_del2 = "DELETE FROM ".$live_table;
      if (@mysql_query($sql_del2)) {
         $log_file .= "Old data removed from $live_table successfully.".$eol;
      } else {
         $log_file .= "Error deleting item: " . mysql_error() . $eol;
         $verify = '0';
         //Write to the log file
         if (!fwrite($handle_log, $log_file)) {}
         fclose($handle_log);
         exit;
      }
      
      if (@mysql_query($sql_merge)) {
         $log_file .= "New data merged into $live_table successfully.".$eol;
      } else {
         $log_file .= "Error inserting new data: " . mysql_error() . $eol;
         $verify = '0';
         //Write to the log file
         if (!fwrite($handle_log, $log_file)) {}
         fclose($handle_log);
         exit;
      }
   } else {
      $log_file .= "Live data not replaced. No records to import.".$eol;
      /* Send an email to the web administrator */
      $mail_eol = "\r\n";
      $to = "webadmin@naturecoastmls.com";
      $subject = "No records imported for Citrus IDX $live_table today.";
      $from = "postmaster@naturecoastmls.com";
      $headers = "From: $from".$mail_eol;
      $headers .= "Reply-To: $from".$mail_eol;
      $headers .= "Return-Path: $from".$mail_eol;
      $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$mail_eol;
      $headers .= "X-Mailer: PHP v".phpversion()."".$mail_eol;
      $headers .= "MIME-Version: 1.0".$mail_eol;
      $headers .= "Content-type: text/html; charset=\"us-ascii\"".$mail_eol;
      $headers .= "Content-Transfer-Encoding: 8bit;".$mail_eol;
      $text = str_replace("\n","<br />",$log_file);
      if (mail($to,$subject,$text,$headers)) {
         $log_file .= "Notice sent to $to.".$eol;
      }
      $alerts_txt .= "No records for $live_table.".$eol;
      $alerts++;
   }

   $log_file .= "--------------------------------------------------------------".$eol;
   
   $i++;
}

//Write to the log file
fwrite($handle_log, $log_file);
fclose($handle_log);

// Notify webadmin by mobile phone if there are alerts
if ($alerts > 0) {
      $to = "3526010603@vtext.com";
      $subject = "Citrus IDX alert";
      $from = "postmaster@naturecoastmls.com";
      $headers = "From: $from".$eol;
      //$headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$eol;
      $headers .= "MIME-Version: 1.0".$eol;
      //$headers .= "Content-type: text/html; charset=\"us-ascii\"".$eol;
      //$headers .= "Content-Transfer-Encoding: 8bit;".$eol;
      mail($to,$subject,$alerts_txt,$headers);
}

/* Closing connection */
mysql_close($dbcnx);
?>
