<?php
/* This file is designed to be included on a page, not to be used as a stand-alone page.
 * The calling page will need to declare some config variables.
 */

/* for testing only
$firm_id = ""; //for searching only listings for this firm
$agent_id = ""; //for searching only listings for this agent
$display_firm = ""; //for displaying this firm's contact info
$display_agent = ""; //for displaying this agent's contact info
$display_office = ""; //for displaying this office's contact info
$searchbox_width = "600"; //specify the width of the search box (duh)
$require_login = ""; //for gathering user/lead info (must be requested by the agent/firm)
$login_fields = "name,address,zip,phone1,phone2,email,buyer_timeframe,buyer_pricerange"; //for selecting fields for user registration
 */

// Include config
require 'config.php';

// Get some functions ready to use
require 'functions.php';

// Map some data fields
require 'data-fields.php';

// Establish some variables
$mls_id = getVar('mls_id');
$user_email = getVar('user_email');
$err_msg = getVar('err_msg');
$submit = getVar('submit');

$max_price = getVar('max_price');
$city = getVar('city');
$min_acres = getVar('min_acres');
$waterfront = getVar('waterfront');
$min_beds = getVar('min_beds');
$min_baths = getVar('min_baths');
$min_sqft = getVar('min_sqft');
$min_yr_blt = getVar('min_yr_blt');
$pool = getVar('pool');

$db_price = "ListPrice";
$db_city = "City";
$db_subdivision = "Subdivision";
$db_waterfront_yn = "WaterFrontPresent";
$db_property_type = "PropertyType";
$db_bedrooms = "Bedrooms";
$db_baths = "BathsTotal";
$db_living_sqft = "SqFtHeated";
$db_year_built = "YearBuilt";
$db_pool_yn = "PoolPresent";
$db_virtual_tour_url = "VirtualTourURL";
$db_acres = "AcresTotal";
$db_lot_size = "ApxLotSize";
$db_road_front = "RoadFrontage";
$db_units = "TotalUnits";
$db_total_sqft = "SqFtTotal";
$db_asset_sales = "AssetSales";

$where_clause = '';
$where_clause_arr = array();
$where_clause_num = 0;
$output = '';
$exit = 0;

// Connect to the Db
$dbcnx = dbconn($db_host,$db_username,$db_password,$db_name);
if (false === is_object($dbcnx)) {
   $err_msg .= "dbcnx: $dbcnx\n";
} else {

/* Check for variables */
if (empty($agent_id) && empty($firm_id) && empty($display_agent) && empty($display_firm)) {
   $err_msg .= "<span class='err_msg'>No Firm or Agent selected.</span>\n";
   $exit = 1;
} else {
   /* Verify agent is allowed to use our system */
   include 'allow_firms.php';
   include 'allow_agents.php';
   if (!stristr($allow_agents,$agent_id) && !stristr($allow_firms,$firm_id) && !stristr($allow_agents,$display_agent) && !stristr($allow_firms,$display_firm)) {
      $err_msg .= "<span class='err_msg'>We are sorry, but this website is not authorized to use this feature.</span>\n";
      $exit = 1;
   }
}
$str_url_vars = "";
$str_url_var_num = 0;
if (!empty($agent_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "agent_id=$agent_id";
   $str_url_var_num++;
}
if (!empty($display_agent)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_agent=$display_agent";
   $str_url_var_num++;
}
if (!empty($firm_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "firm_id=$firm_id";
   $str_url_var_num++;
}
if (!empty($display_firm)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_firm=$display_firm";
   $str_url_var_num++;
}
if (!empty($display_office)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_office=$display_office";
   $str_url_var_num++;
}
if (empty($searchbox_width)) {
    $searchbox_width = "500";
}
if (isset($require_login)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "require_login=$require_login";
   $str_url_var_num++;
}
if (isset($login_fields)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "login_fields=$login_fields";
   $str_url_var_num++;
}
if (empty($searchbox_width)) {
    $searchbox_width = "500";
}
if (!empty($submit)) {
   $prop_type = getVar('prop_type');
   if (empty($prop_type)) {
      $err_msg = "<span class='err_msg'>No Property Type selected.</span>\n";
      $exit = 1;
   }
}
if (!empty($submit_reset)) {
   $max_price = '';
   $city = '';
   $min_acres = '';
   $waterfront = '';
   $min_beds = '';
   $min_baths = '';
   $min_sqft = '';
   $min_yr_blt = '';
   $pool = '';
   $mls_id = '';
}
if (empty($bgcolor1)) {
   $bgcolor1 = "#ccc";
}
if (empty($bgcolor2)) {
   $bgcolor2 = "#999";
}
if (empty($textcolor)) {
   $textcolor = "#000";
}

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<p class='error'>$err_msg</p>\n";
}

/* Display Search box */
/* Select city names to be used */
$sql_city = "SELECT DISTINCT $db_city FROM $db_tbl_residential ORDER BY $db_city";
$result_city = $dbcnx->prepare($sql_city);
$result_city->execute();
$data_city = $result_city->fetchAll(PDO::FETCH_ASSOC);
echo "<form action='$PHP_SELF' method='post' name='listing_search'>\n";
echo "<table width='$searchbox_width' border='0' cellspacing='0' cellpadding='2' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: $textcolor;border: 1px solid #000;'>\n";
echo "<tr style='background-color: $bgcolor1;font-size: 16px;border-bottom: 1px solid #000;'>\n";
echo "<td><strong>Search for Listings</strong></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor2;'>\n";
echo "<td><strong>By Features</strong></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor1;border-bottom: 1px solid #000;'>\n";
echo "<td>Property Type: <select name='prop_type' size='1'>\n";
if (!empty($prop_type)) {
   /* Let's make it look nice */
   $prop_type_name_arr = explode('_', $prop_type);
   $prop_type_name = ucfirst($prop_type_name_arr[0])." ".ucfirst($prop_type_name_arr[1]);
   echo "<option value='$prop_type'>$prop_type_name</option>\n";
}
echo "<option value='residential'>Residential</option>\n";
echo "<option value='mobile'>Mobile</option>\n";
echo "<option value='condo/villa/townhome'>Condo/Villa/Townhome</option>\n";
echo "<option value='multi_res'>Multi-Family</option>\n";
echo "<option value='vacant_land'>Vacant Land</option>\n";
echo "<option value='commercial'>Commercial</option>\n";
echo "<option value='business_op'>Business Op</option>\n";
//echo "<option value='rental'>Rental</option>";
echo "</select>\n";
echo "&nbsp;&nbsp;Max Price: $<select name='max_price' size='1'>\n";
if (!empty($max_price)) {
   echo "<option value='$max_price'>".number_format($max_price)."</option>\n";
}
echo "<option value=''>---</option>\n";
echo "<option value='50000'>50,000</option>\n";
echo "<option value='100000'>100,000</option>\n";
echo "<option value='150000'>150,000</option>\n";
echo "<option value='200000'>200,000</option>\n";
echo "<option value='250000'>250,000</option>\n";
echo "<option value='300000'>300,000</option>\n";
echo "<option value='350000'>350,000</option>\n";
echo "<option value='400000'>400,000</option>\n";
echo "<option value='500000'>500,000</option>\n";
echo "<option value='1000000'>1,000,000</option>\n";
echo "<option value=''>1,000,000+</option>\n";
echo "</select>\n";
echo "<br />City: <select name='city' size='1'>\n";
if (!empty($city)) {
   echo "<option value='$city'>$city</option>\n";
}
echo "<option value=''>---</option>\n";
foreach($data_city as $row_city) {
   echo "<option value='".$row_city[$db_city]."'>".$row_city[$db_city]."</option>\n";
}
echo "</select>\n";
echo "&nbsp;&nbsp;Min. Acres: <select name='min_acres' size='1'>\n";
if (!empty($min_acres)) {
   echo "<option value='$min_acres'>$min_acres</option>\n";
}
echo "<option value=''>--</option>\n";
echo "<option value='0.25'>.25</option>\n";
echo "<option value='0.5'>.5</option>\n";
echo "<option value='1'>1</option>\n";
echo "<option value='1.5'>1.5</option>\n";
echo "<option value='2'>2</option>\n";
echo "<option value='2.5'>2.5</option>\n";
echo "<option value='3'>3</option>\n";
echo "<option value='4'>4</option>\n";
echo "<option value='5'>5</option>\n";
echo "<option value='10'>10</option>\n";
echo "<option value='20'>20</option>\n";
echo "</select>&nbsp;&nbsp;Waterfront: <select name='waterfront' size='1'>\n";
if (!empty($waterfront)) {
   /* Let's make it look nice */
   if ($waterfront == 'Y') {
      $waterfront_name = 'Yes';
   } else {
      $waterfront_name = 'No';
   }
   echo "<option value='$waterfront'>$waterfront_name</option>\n";
}
echo "<option value=''>--</option>\n";
echo "<option value='Y'>Yes</option>\n";
echo "<option value='N'>No</option>\n";
echo "</select></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor1;'>\n";
echo "<td><i>Residential Options</i><br /></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor1;'>\n";
echo "<td>Min. Beds: <select name='min_beds' size='1'>\n";
if (!empty($min_beds)) {
   echo "<option value='$min_beds'>$min_beds</option>\n";
}
echo "<option value=''>--</option>\n";
echo "<option value='1'>1</option>\n";
echo "<option value='2'>2</option>\n";
echo "<option value='3'>3</option>\n";
echo "<option value='4'>4</option>\n";
echo "<option value='5'>5</option></select>\n";
echo "&nbsp;&nbsp;Min. Baths: <select name='min_baths' size='1'>\n";
if (!empty($min_baths)) {
   echo "<option value='$min_baths'>$min_baths</option>\n";
}
echo "<option value=''>--</option>\n";
echo "<option value='1'>1</option>\n";
echo "<option value='1.5'>1.5</option>\n";
echo "<option value='2'>2</option>\n";
echo "<option value='2.5'>2.5</option>\n";
echo "<option value='3'>3</option>\n";
echo "<option value='3.5'>3.5</option>\n";
echo "<option value='4'>4</option></select>\n";
echo "&nbsp;&nbsp;Min. SqFt: <select name='min_sqft' size='1'>\n";
if (!empty($min_sqft)) {
   echo "<option value='$min_sqft'>".number_format($min_sqft)."</option>\n";
}
echo "<option value=''>--</option>\n";
echo "<option value='500'>500</option>\n";
echo "<option value='1000'>1,000</option>\n";
echo "<option value='1500'>1,500</option>\n";
echo "<option value='2000'>2,000</option>\n";
echo "<option value='2500'>2,500</option>\n";
echo "<option value='3000'>3,000</option>\n";
echo "<option value='3500'>3,500</option>\n";
echo "<option value='4000'>4,000</option>\n";
echo "<option value='5000'>5,000</option>\n";
echo "<option value='10000'>10,000</option></select><br />\n";
echo "Min. Year Built: <select name='min_yr_blt' size='1'>\n";
if (!empty($min_yr_blt)) {
   echo "<option value='$min_yr_blt'>$min_yr_blt</option>\n";
}
echo "<option value=''>--</option>\n";
echo "<option value='1970'>1970</option>\n";
echo "<option value='1980'>1980</option>\n";
echo "<option value='1990'>1990</option>\n";
echo "<option value='2000'>2000</option>\n";
echo "<option value='".date("Y",mktime(1,1,1,1,1,date("Y")-3))."'>".date("Y",mktime(1,1,1,1,1,date("Y")-3))."</option>\n";
echo "<option value='".date("Y",mktime(1,1,1,1,1,date("Y")))."'>".date("Y",mktime(1,1,1,1,1,date("Y")))."</option></select>\n";
echo "&nbsp;&nbsp;Pool: <select name='pool' size='1'>\n";
if (!empty($pool)) {
   /* Let's make it look nice */
   if ($pool == 'Y') {
      $pool_name = 'Yes';
   } else {
      $pool_name = 'No';
   }
   echo "<option value='$pool'>$pool_name</option>\n";
}
echo "<option value=''>--</option>\n";
echo "<option value='Y'>Yes</option>\n";
echo "<option value='N'>No</option>\n";
echo "</select></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor2;'>\n";
echo "<td><strong>Or By MLS Number</strong> <input type='text' name='mls_id' size='10' value='$mls_id' /></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor1;'>\n";
echo "<td><input type='submit' name='submit' value='Search' />&nbsp;<input type='submit' name='submit_reset' value='Reset All' />&nbsp;&nbsp;<span style='font-size: 12px;font-style: italic;'>Tip: Try resetting fields in your search to get more results.</span></td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";
/* End Search box */

if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "exit: $exit<br />\n";
   echo "submit: $submit<br />\n";
   echo "</p>\n";
}
/* Stop here if there are any errors */
if ($exit > 0) {
   exit;
}

If (!empty($submit)) {
/* Construct sql statement */
switch ($prop_type) {
	case 'mobile':
         $table = $db_tbl_residential;
         if ($where_clause_num > 0) { $where_clause .= " AND"; }
         $where_clause .= " $db_property_type = :prop_type";
         $where_clause_arr['prop_type'] = 'Mobile';
         $where_clause_num++;
	break;
	case 'condo/villa/townhome':
         $table = $db_tbl_residential;
         if ($where_clause_num > 0) { $where_clause .= " AND"; }
         $where_clause .= " $db_property_type = :prop_type";
         $where_clause_arr['prop_type'] = 'Condo/Villa/Townhome';
         $where_clause_num++;
	break;
	case 'residential':
         $table = $db_tbl_residential;
         if ($where_clause_num > 0) { $where_clause .= " AND"; }
         $where_clause .= " $db_property_type = :prop_type";
         $where_clause_arr['prop_type'] = 'Residential';
         $where_clause_num++;
	break;
	default:
         $table = 'tbl_idx_'.$prop_type;
	break;
}
if (!empty($mls_id)) {
   $where_clause = " $db_mls_id = :mls_id";
   $where_clause_arr['mls_id'] = $mls_id;
   $where_clause_num++;
} else {
   if (!empty($agent_id)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " TRIM($db_agent_id) = :agent_id";
      $where_clause_arr['agent_id'] = $agent_id;
      $where_clause_num++;
   }
   if (!empty($firm_id)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " TRIM($db_firm_id) = :firm_id";
      $where_clause_arr['firm_id'] = $firm_id;
      $where_clause_num++;
   }
   if (!empty($max_price)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_price < :max_price";
      $where_clause_arr['max_price'] = $max_price;
      $where_clause_num++;
   }
   if (!empty($city)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_city = :city";
      $where_clause_arr['city'] = $city;
      $where_clause_num++;
   }
   if (!empty($min_acres)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_acres >= :min_acres";
      $where_clause_arr['min_acres'] = $min_acres;
      $where_clause_num++;
   }
   if (!empty($waterfront)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_waterfront_yn = :waterfront";
      $where_clause_arr['waterfront'] = $waterfront;
      $where_clause_num++;
   }
   if (!empty($min_beds)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_bedrooms >= :min_beds";
      $where_clause_arr['min_beds'] = $min_beds;
      $where_clause_num++;
   }
   if (!empty($min_baths)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_baths >= :min_baths";
      $where_clause_arr['min_baths'] = $min_baths;
      $where_clause_num++;
   }
   if (!empty($min_sqft)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_total_sqft >= :min_sqft";
      $where_clause_arr['min_sqft'] = $min_sqft;
      $where_clause_num++;
   }
   if (!empty($min_yr_blt)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_year_built >= :min_yr_blt";
      $where_clause_arr['min_yr_blt'] = $min_yr_blt;
      $where_clause_num++;
   }
   if (!empty($pool)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_pool_yn = :pool";
      $where_clause_arr['pool'] = $pool;
      $where_clause_num++;
   }
}
if (!empty($where_clause)) {
   $where_clause = " WHERE".$where_clause;
}
$sql = "SELECT * FROM ".$table.$where_clause." ORDER BY $db_price DESC";
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "where_clause_num: $where_clause_num<br />\n";
   echo "sql: $sql<br />\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "</p>\n";
}
$result = $dbcnx->prepare($sql);
//$result->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result->execute($where_clause_arr);
$data_results = $result->fetchAll(PDO::FETCH_ASSOC);
$num_rows = count($data_results);
/* Debugging info */
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "num_rows: $num_rows<br />\n";
   echo "</p>\n";
}

/* Search results message */
echo "<p align='center' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>Your search returned $num_rows results.</p>\n";

switch ($table) {
   case 'tbl_idx_residential':
/* Residential Listings */
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>Residential</td></tr>\n";
}
foreach ($data_results as $row) {
      // Initial photo check
      photoCheck($abs_photos,$row[$db_mls_id]);
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><strong>MLS# ".$row[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_residential.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank'>";
      // Display thumbnail photo
      echo thumbnailPhoto($abs_photos,$row[$db_mls_id]);
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Subdivision:</strong> ".$row[$db_subdivision]."<br /><strong>Bedrooms:</strong> ".$row[$db_bedrooms]."<br /><strong>Baths:</strong> ".$row[$db_baths]."</td>\n";
      echo " <td valign='top'><strong>Total SqFt:</strong> ".$row[$db_total_sqft]."<br /><strong>Year Built:</strong> ".$row[$db_year_built]."<br /><strong>Pool:</strong> ".$row[$db_pool_yn]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_residential.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
}
echo "</table><br />\n";
/* End of Residential Listings */
   break;
   case 'tbl_idx_vacant_land':
/* Vacant Land Listings */
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>Vacant Land</td></tr>\n";
}
foreach ($data_results as $row) {
      // Initial photo check
      photoCheck($abs_photos,$row[$db_mls_id]);
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><strong>MLS# ".$row[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_vacantland.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank'>";
      // Display thumbnail photo
      echo thumbnailPhoto($abs_photos,$row[$db_mls_id]);
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Subdivision:</strong> ".$row[$db_subdivision]."<br /><strong>Apx. Acres:</strong> ".$row[$db_acres]."<br /><strong>Waterfront:</strong> ".$row[$db_waterfront_yn]."</td>\n";
      echo " <td valign='top'><strong>Apx. Lot Size:</strong> ".$row[$db_lot_size]."<br /><strong>Road Front:</strong> ".$row[$db_road_front]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_vacantland.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
}
echo "</table><br />\n";
/* End of Vacant Land Listings */
   break;
   case 'tbl_idx_multi_res':
/* Multi Res Listings */
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>Multi Residential</td></tr>\n";
}
foreach ($data_results as $row) {
      // Initial photo check
      photoCheck($abs_photos,$row[$db_mls_id]);
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><strong>MLS# ".$row[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_multi_res.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank'>";
      // Display thumbnail photo
      echo thumbnailPhoto($abs_photos,$row[$db_mls_id]);
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Units:</strong> ".$row[$db_units]."<br /><strong>Subdivision:</strong> ".$row[$db_subdivision]."<br /><strong>Waterfront:</strong> ".$row[$db_waterfront_yn]."</td>\n";
      echo " <td valign='top'><strong>Total SqFt:</strong> ".$row[$db_total_sqft]."<br /><strong>Year Built:</strong> ".$row[$db_year_built]."<br /><strong>Pool:</strong> ".$row[$db_pool_yn]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_multi_res.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
}
echo "</table><br />\n";
/* End of Multi Residential Listings */
   break;
   case 'tbl_idx_commercial':
/* Commercial Listings */
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>Commercial</td></tr>\n";
}
foreach ($data_results as $row) {
      // Initial photo check
      photoCheck($abs_photos,$row[$db_mls_id]);
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><strong>MLS# ".$row[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_commercial.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank'>";
      // Display thumbnail photo
      echo thumbnailPhoto($abs_photos,$row[$db_mls_id]);
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Bldg SqFt:</strong> ".$row[$db_total_sqft]."<br /><strong># of Units:</strong> ".$row[$db_units]."<br /></td>\n";
      echo " <td valign='top'><strong>Asset Sales:</strong> ".$row[$db_asset_sales]."<br /><strong>Year Built:</strong> ".$row[$db_year_built]."<br /></td>\n";
      echo " <td valign='top'><a href='".$http_home."details_commercial.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
}
echo "</table><br />\n";
/* End of Commercial Listings */
   break;
   case 'tbl_idx_business_op':
/* Business Op Listings */
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>Business Opportunity</td></tr>\n";
}
foreach ($data_results as $row) {
      // Initial photo check
      photoCheck($abs_photos,$row[$db_mls_id]);
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><strong>MLS# ".$row[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_business_op.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank'>";
      // Display thumbnail photo
      echo thumbnailPhoto($abs_photos,$row[$db_mls_id]);
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Bldg SqFt:</strong> ".$row[$db_total_sqft]."<br /><strong># of Units:</strong> ".$row[$db_units]."<br /></td>\n";
      echo " <td valign='top'><strong>Asset Sales:</strong> ".$row[$db_asset_sales]."<br /><strong>Year Built:</strong> ".$row[$db_year_built]."<br /></td>\n";
      echo " <td valign='top'><a href='".$http_home."details_business_op.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
     echo "</tr>\n";
}
echo "</table><br />\n";
/* End of Business Op Listings */
   break;
   case 'tbl_idx_rental':
/* Rental Listings */
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>Rental</td></tr>\n";
}
foreach ($data_results as $row) {
      // Initial photo check
      photoCheck($abs_photos,$row[$db_mls_id]);
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><strong>MLS# ".$row[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_residential.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank'>";
      // Display thumbnail photo
      echo thumbnailPhoto($abs_photos,$row[$db_mls_id]);
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Subdivision:</strong> ".$row[$db_subdivision]."<br /><strong>Bedrooms:</strong> ".$row[$db_bedrooms]."<br /><strong>Baths:</strong> ".$row[$db_baths]."</td>\n";
      echo " <td valign='top'><strong>Total SqFt:</strong> ".$row[$db_total_sqft]."<br /><strong>Year Built:</strong> ".$row[$db_year_built]."<br /><strong>Pool:</strong> ".$row[$db_pool_yn]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_residential.php?mls_id=".$row[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
}
echo "</table><br />\n";
/* End of Rental Listings */
   break;
} // End of property type switch

/* Disclaimer */
echo $disclaimer;

} // End of If (!empty($submit))
/* Closing connection */
$dbcnx = null;
} // End check for dbcnx
?>
