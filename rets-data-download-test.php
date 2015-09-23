<?php
/* Set some variables */
$rets_login_url = "http://rets.offutt-innovia.com:8080/cit/login";
$rets_username = "T56e34W";
$rets_password = "J785T23";
$rets_user_agent = "PHRETS/1.0";
//$rets_user_agent_password = "C1ru4c0N";
$Rets_Version = 'RETS/1.5'; 
$debugging = false;

// use http://retsmd.com to help determine the SystemName of the DateTime field which
// designates when a record was last modified
$rets_modtimestamp_field = "ModificationTimestamp";

// use http://retsmd.com to help determine the names of the classes you want to pull.
// these might be something like RE_1, RES, RESI, 1, etc.
$resources = array("Office" => array("Office"),"Agent" => array("Agent"),"Property" => array("ResidentialProperty","VacantLand","CommercialProperty","MultiFamily","BusinessOpportunity","Rental"));

// DateTime which is used to determine how far back to retrieve records.
// using a really old date so we can get everything
$previous_start_time = "1980-01-01T00:00:00";

/* Get our phRets class */
require "inc-class-phrets.php";

$rets = new phRETS;

//$rets->AddHeader("User-Agent", $rets_user_agent);
//$rets->AddHeader("Accept", "*/*");
//$rets->AddHeader("RETS-Version", "$Rets_Version");
//$rets->AddHeader("User-Agent", "$rets_user_agent");
//$rets->SetParam("cookie_file", "phrets_cookies.txt");
//$rets->SetParam("debug_mode", TRUE); // ends up in rets_debug.txt 

echo "+ Connecting to {$rets_login_url} as {$rets_username}<br>\n";
$connect = $rets->Connect($rets_login_url, $rets_username, $rets_password);

// check for errors
if ($connect) {
   echo "  + Connected<br>\n";
}
else {
   echo "  + Not connected:<br>\n";
   print_r($rets->Error());
   exit;
}

$types = $rets->GetMetadataTypes();

// check for errors
if (!$types) {
   print_r($rets->Error());
} else {
   foreach ($types as $type) {
      echo "+ Resource {$type['Resource']}<br>\n";
      foreach ($type['Data'] as $class) {
         echo "  + Class {$class['ClassName']}<br>\n";
      }
   }
}

$rets_version = $rets->GetServerVersion();
echo "+ RETS version: {$rets_version}<br />\n";

$server_software = $rets->GetServerSoftware();
echo "+ Server Technology: {$server_software}<br />\n";

//$fields = $rets->GetMetadataTable("Property", "RES");
$last_url = $rets->LastRequestURL();
echo "+ Last URL: {$last_url}<br />\n";
$server = $rets->GetServerInformation();
echo "+ System ID: {$server['SystemID']}<br />\n";
echo "+ System Description: {$server['SystemDescription']}<br />\n";
echo "+ System Comment: {$server['Comments']}<br />\n";
echo "+ RETS 1.7.2 TimeZoneOffset: {$server['TimeZoneOffset']}<br />\n";

// Download some data
//$property_classes = array("CommercialProperty");
//print_r($classes);
foreach ($resources as $resource => $classes) {
//print_r($subclass);
foreach ($classes as $class) {
//print_r($classname);
        echo "+ {$resource}:{$class}<br>\n";

        $file_name = strtolower("data/{$resource}_{$class}.csv");
        $fh = fopen($file_name, "w+");

        /*if ($resource == 'Office' || $resource == 'Agent') {
           $rets_modtimestamp_field = "ModificationTimestamp";
        }*/
        $maxrows = true;
        $offset = 1;
        $limit = 1000;
        $fields_order = array();

        while ($maxrows) {

                $query = "({$rets_modtimestamp_field}={$previous_start_time}+)";

                // run RETS search
                echo "   + Query: {$query}  Limit: {$limit}  Offset: {$offset}<br>\n";
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
                echo "    + Total found: {$rets->TotalRecordsFound()}<br>\n";

                $rets->FreeResult($search);
        }

        fclose($fh);

        echo "  - done<br>\n";

}
}

echo "+ Disconnecting<br>\n";
$rets->Disconnect();

if ($debugging) {
   echo "<p>Debugging info:<br />\n";
   echo "login url: $rets_login_url<br />\n";
   echo "username: $rets_username<br />\n";
   echo "password: $rets_password<br />\n";
   echo "user agent: $rets_user_agent<br />\n";
   echo "user agent password: $rets_user_agent_password<br />\n";
   echo "</p>\n";
}
?>
