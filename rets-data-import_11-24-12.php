<?php
/* Set some config variables */
$rets_login_url = "http://rets.offutt-innovia.com:8080/cit/login";
$rets_username = "T56e34W";
$rets_password = "J785T23";
$rets_user_agent = "PHRETS/1.0";
//$rets_user_agent_password = "C1ru4c0N";
$Rets_Version = 'RETS/1.5'; 
$home_dir = "/home/naturmls/public_html/"; //Home directory
$data_dir = $home_dir."data/"; //Data files directory
$handle_log = fopen ($home_dir."import_log.txt", "a");
$webadmin_email = "webadmin@naturecoastmls.com";
$webadmin_mobile_text = "3526010603@vtext.com";
$debugging = true;

// use http://retsmd.com to help determine the SystemName of the DateTime field which
// designates when a record was last modified
$rets_modtimestamp_field = "ModificationTimestamp";
// use http://retsmd.com to help determine the names of the classes you want to pull.
// these might be something like RE_1, RES, RESI, 1, etc.
$resources = array("Office" => array("Office"),"Agent" => array("Agent"),"Property" => array("ResidentialProperty","VacantLand","CommercialProperty","MultiFamily","BusinessOpportunity","Rental"));
// DateTime which is used to determine how far back to retrieve records.
// using a really old date so we can get everything
$previous_start_time = "1980-01-01T00:00:00";

/* some more variables for internal use */
$log_file = "";
$eol = "\n";
$alerts_txt = "";
$files_arr = array();

/* Function for sending out alerts */
function webadmin_alert($alerts_txt,$log_file) {
   global $debugging, $webadmin_email, $webadmin_mobile_text, $eol;
   
   if (!empty($webadmin_email) && $debugging != true) {
   /* Send an email alert to the web administrator */
   $mail_eol = "\r\n";
   $to = $webadmin_email;
   $subject = "Citrus IDX alert";
   $from = "postmaster@naturecoastmls.com";
   $headers = "From: $from".$mail_eol;
   $headers .= "Reply-To: $from".$mail_eol;
   $headers .= "Return-Path: $from".$mail_eol;
   $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$mail_eol;
   $headers .= "X-Mailer: PHP v".phpversion()."".$mail_eol;
   $headers .= "MIME-Version: 1.0".$mail_eol;
   $headers .= "Content-type: text/html; charset=\"us-ascii\"".$mail_eol;
   $headers .= "Content-Transfer-Encoding: 8bit;".$mail_eol;
   // Use log entry as message body
   $text = str_replace("\n","<br />",$log_file);
   if (mail($to,$subject,$text,$headers)) {
      $log_file .= "Notice sent to $to.".$eol;
   }
   }

   if (!empty($webadmin_mobile_text) && $debugging != true) {
   /* Send a text message alert to the web administrator */
   $to = $webadmin_mobile_text;
   $headers = "From: $from".$eol;
   $headers .= "MIME-Version: 1.0".$eol;
   if (mail($to,$subject,$alerts_txt,$headers)) {
      $log_file .= "Notice sent to $to.".$eol;
   }
   }
}

/* Get our phRets class */
require "inc-class-phrets.php";
$rets = new phRETS;

/* Start the log entry */
$log_file = "--------------------------------------------------------------".$eol;
$log_file .= date("Y-m-d G:i").$eol;

// Connect to the RETS Server
if ($debugging) { echo "+ Connecting to {$rets_login_url} as {$rets_username}<br />\n"; }
$connect = $rets->Connect($rets_login_url, $rets_username, $rets_password);
if ($connect) {
   if ($debugging) { echo "  + Connected<br />\n"; }
   $log_file .= "Connected to RETS server and downloaded:".$eol;
} else {
   if ($debugging) { echo "  + Not connected:<br />\n"; print_r($rets->Error()); }
   $alerts_txt .= "Could not connect to the RETS server.";
   $log_file .= $alerts_txt.$eol;
   $log_file .= print_r($rets->Error());
   webadmin_alert($alerts_txt,$log_file);
   if (!fwrite($handle_log, $log_file)) {}
   fclose($handle_log);
   exit;
}

/* Get type info from the meta data */
$types = $rets->GetMetadataTypes();
if (!$types) {
   $log_file .= print_r($rets->Error());
} else {
   foreach ($types as $type) {
      if ($debugging) { echo "+ Resource {$type['Resource']}<br />\n"; }
      foreach ($type['Data'] as $class) {
         if ($debugging) { echo "  + Class {$class['ClassName']}<br />\n"; }
      }
   }
}

/* Get some more RETS server info */
$rets_version = $rets->GetServerVersion();
if ($debugging) { echo "+ RETS version: {$rets_version}<br />\n"; }
$server_software = $rets->GetServerSoftware();
if ($debugging) { echo "+ Server Technology: {$server_software}<br />\n"; }
$last_url = $rets->LastRequestURL();
if ($debugging) { echo "+ Last URL: {$last_url}<br />\n"; }
$server = $rets->GetServerInformation();
if ($debugging) { echo "+ System ID: {$server['SystemID']}<br />\n"; }
if ($debugging) { echo "+ System Description: {$server['SystemDescription']}<br />\n"; }
if ($debugging) { echo "+ System Comment: {$server['Comments']}<br />\n"; }
if ($debugging) { echo "+ RETS 1.7.2 TimeZoneOffset: {$server['TimeZoneOffset']}<br />\n"; }

/* Download the data by iterating through our list of property classes */
//$property_classes = array("CommercialProperty"); // For testing only
foreach ($resources as $resource => $classes) {
foreach ($classes as $class) {

        if ($debugging) { echo "+ {$resource}:{$class}<br>\n"; }
        $file_name = strtolower("{$resource}_{$class}.csv");
        $files_arr[] = $file_name;
        $log_file .= $file_name.$eol;
        $fh = fopen($data_dir.$file_name, "w+");

        $maxrows = true;
        $offset = 1;
        $limit = 1000;
        $fields_order = array();

        while ($maxrows) {
                $query = "({$rets_modtimestamp_field}={$previous_start_time}+)";
                // run RETS search
                if ($debugging) { echo "   + Query: {$query}  Limit: {$limit}  Offset: {$offset}<br>\n"; }
                $search = $rets->SearchQuery($resource, $class, $query, array('Limit' => $limit, 'Offset' => $offset, 'Format' => 'COMPACT-DECODED', 'Count' => 1));
                if ($rets->NumRows() > 0) {
                        if ($offset == 1) {
                                // print filename headers as first line
                                $fields_order = $rets->SearchGetFields($search);
                                fputcsv($fh, $fields_order);
                        }
                        // process results
                        while ($record = $rets->FetchRow($search)) {
                                $this_record = array();
                                foreach ($fields_order as $fo) {
                                        $this_record[] = $record[$fo];
                                }
                                fputcsv($fh, $this_record);
                        }
                        $offset = ($offset + $rets->NumRows());
                }
                $maxrows = $rets->IsMaxrowsReached();
                if ($debugging) { echo "    + Total found: {$rets->TotalRecordsFound()}<br>\n"; }
                $rets->FreeResult($search);
        }
        fclose($fh);
        if ($debugging) { echo "  - done<br>\n"; }
}
}

if ($debugging) { echo "+ Disconnecting<br>\n"; }
$rets->Disconnect();

// Connect to the Database
include 'dbconn.php';

/* Start importing the data into the database */
Foreach ($files_arr as $idx_file) {
   //Set variables
   switch ($idx_file) {
      case 'office_office.csv':
         $newname = "idx_office";
      break;
      case 'agent_agent.csv':
         $newname = "idx_agent";
      break;
      case 'property_residentialproperty.csv':
         $newname = "idx_residential";
      break;
      case 'property_vacantland.csv':
         $newname = "idx_vacant_land";
      break;
      case 'property_commercialproperty.csv':
         $newname = "idx_commercial";
      break;
      case 'property_multifamily.csv':
         $newname = "idx_multi_res";
      break;
      case 'property_businessopportunity.csv':
         $newname = "idx_business_op";
      break;
      case 'property_rental.csv':
         $newname = "idx_rental";
      break;
   }
   //Set some more variables
   $idx_file_clean = $data_dir.$newname.".txt";
   $filename_arr = explode(".", $idx_file);
   $temp_table = "tbl_temp_".$newname;
   $sql_import = "LOAD DATA LOW_PRIORITY INFILE '$idx_file_clean' REPLACE INTO TABLE naturmls_citrusmls.$temp_table ";
   $sql_import .= "FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 LINES";
   $live_table = "tbl_".$newname;
   $sql_merge = "INSERT INTO $live_table SELECT * FROM ".$temp_table;

   if ($debugging) { echo "Reading & converting raw data from $idx_file.".$eol; }
   $raw_data = file_get_contents($data_dir.$idx_file);
   //$converted = str_replace("\n</div>","",$raw_data);
   $converted = str_replace("\"- Active\"","A",str_replace("\"- Inactive\"","I",str_replace("\"- Purge\"","P",$raw_data)));

   //Save the file
   $handle = fopen ($idx_file_clean, "w");
   if (!fwrite($handle, $converted)) {
      $alerts_txt = "Cannot write to file ($idx_file_clean)";
      $log_file .= $alerts_txt.$eol;
      webadmin_alert($alerts_txt,$log_file);
      //Write to the log file
      fwrite($handle_log, $log_file);
      fclose($handle_log);
      exit;
   }
   fclose($handle);
   //$log_file .= "Successfully wrote to $idx_file_clean.".$eol;
   if ($debugging) { "Successfully wrote to $idx_file_clean.<br />".$eol; }

   //Delete old data from temp table
   $sql_del1 = "DELETE FROM ".$temp_table;
   if (@mysql_query($sql_del1)) {
      //$log_file .= "Old data removed from $temp_table successfully.".$eol;
      if ($debugging) { "sql_del1: $sql_del1<br />".$eol; }
      if ($debugging) { "Old data removed from $temp_table successfully.<br />".$eol; }
   } else {
      $alerts_txt .= "Error deleting item: " . mysql_error();
      $log_file .= $alerts_txt.$eol;
      webadmin_alert($alerts_txt,$log_file);
      //Write to the log file
      fwrite($handle_log, $log_file);
      fclose($handle_log);
      exit;
   }

   //Import data into temp table
   if (mysql_query($sql_import)) {
      $num_imported = mysql_affected_rows($dbcnx);
      if ($debugging) { "sql_import: $sql_import<br />".$eol; }
      $log_file .= $num_imported." records successfully imported into $temp_table.".$eol;
      if ($debugging) { $num_imported." records successfully imported into $temp_table.<br />".$eol; }
   } else {
      $alerts_txt .= "Error performing query: " . mysql_error();
      $log_file .= $alerts_txt.$eol;
      webadmin_alert($alerts_txt,$log_file);
      //Write to the log file
      fwrite($handle_log, $log_file);
      fclose($handle_log);
      exit;
   }

   //Delete inactive agents from temp table
   if ($newname == "idx_agent") {
   $sql_del3 = "DELETE FROM ".$temp_table." WHERE AgentStatus = 'I' OR AgentStatus = 'P'";
   if (@mysql_query($sql_del3)) {
      //$log_file .= "Old data removed from $temp_table successfully.".$eol;
      if ($debugging) { "sql_del3: $sql_del3<br />".$eol; }
      if ($debugging) { "Inactive Agents removed from $temp_table successfully.<br />".$eol; }
   } else {
      if ($debugging) { "sql_del3: $sql_del3<br />".$eol; }
      $alerts_txt .= "Error deleting item: " . mysql_error();
      $log_file .= $alerts_txt.$eol;
      webadmin_alert($alerts_txt,$log_file);
      //Write to the log file
      fwrite($handle_log, $log_file);
      fclose($handle_log);
      exit;
   }
   }

   //Delete inactive offices from temp table
   if ($newname == "idx_office") {
   $sql_del3 = "DELETE FROM ".$temp_table." WHERE OfficeStatus = 'I' OR OfficeStatus = 'P'";
   if (@mysql_query($sql_del3)) {
      //$log_file .= "Old data removed from $temp_table successfully.".$eol;
      if ($debugging) { "sql_del3: $sql_del3<br />".$eol; }
      if ($debugging) { "Inactive Offices removed from $temp_table successfully.<br />".$eol; }
   } else {
      if ($debugging) { "sql_del3: $sql_del3<br />".$eol; }
      $alerts_txt .= "Error deleting item: " . mysql_error();
      $log_file .= $alerts_txt.$eol;
      webadmin_alert($alerts_txt,$log_file);
      //Write to the log file
      fwrite($handle_log, $log_file);
      fclose($handle_log);
      exit;
   }
   }

   // Replace live data now
   if ($num_imported > 0) {
      $sql_del2 = "DELETE FROM ".$live_table;
      if (@mysql_query($sql_del2)) {
         //$log_file .= "Old data removed from $live_table successfully.".$eol;
         if ($debugging) { "sql_del2: $sql_del2<br />".$eol; }
         if ($debugging) { "Old data removed from $live_table successfully.<br />".$eol; }
      } else {
         $alerts_txt .= "Error deleting item: " . mysql_error();
         $log_file .= $alerts_txt.$eol;
         webadmin_alert($alerts_txt,$log_file);
         //Write to the log file
         fwrite($handle_log, $log_file);
         fclose($handle_log);
         exit;
      }
      
      if (@mysql_query($sql_merge)) {
         $log_file .= "New data merged into $live_table successfully.".$eol;
         if ($debugging) { "sql_merge: $sql_merge<br />".$eol; }
         if ($debugging) { "New data merged into $live_table successfully.<br />".$eol; }
      } else {
         $alerts_txt .= "Error inserting new data: " . mysql_error();
         $log_file .= $alerts_txt.$eol;
         webadmin_alert($alerts_txt,$log_file);
         //Write to the log file
         fwrite($handle_log, $log_file);
         fclose($handle_log);
         exit;
      }
   } else {
      $alerts_txt .= "Live data not replaced. No records to import.";
      $log_file .= $alerts_txt.$eol;
      webadmin_alert($alerts_txt,$log_file);
      if ($debugging) { $alerts_txt."<br />".$eol; }
   }

}
$log_file .= "--------------------------------------------------------------".$eol;

//Write to the log file
fwrite($handle_log, $log_file);
fclose($handle_log);

/* Close Db connection */
mysql_close($dbcnx);

if ($debugging) {
   echo "<p>Debugging info:<br />\n";
   echo "login url: $rets_login_url<br />\n";
   echo "username: $rets_username<br />\n";
   echo "password: $rets_password<br />\n";
   echo "user agent: $rets_user_agent<br />\n";
   echo "user agent password: $rets_user_agent_password<br />\n";
   echo "</p>\n";
}
