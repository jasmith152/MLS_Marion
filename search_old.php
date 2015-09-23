<?php
// Establish GET & POST variables
import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];

//Include Db connection script
include 'dbconn.php';

/* Set some variables */
//$idx_dir = "/home/idx/";
$idx_dir = "/home/mychurchserver/domains/citrusmls.mychurchserver.com/public_html/";
$http_home = "http://citrusmls.mychurchserver.com/";
$http_photos = $http_home."photos/";
$http_imgs = $http_home."images/";
$where_clause_num = 0;
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
if (!empty($submit)) {
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
   echo "<div align='center'>$err_msg</div>\n";
}

/* Display Search box */
/* Select city names to be used */
$result_city = mysql_query("SELECT DISTINCT LM_MST_CITY FROM tbl_idx_residential ORDER BY LM_MST_CITY");

echo "<form action='$PHP_SELF' method='post' name='listing_search'>\n";
echo "<table width='$searchbox_width' border='0' cellspacing='0' cellpadding='2' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: $textcolor;border: 1px solid #000;'>\n";
echo "<tr style='background-color: $bgcolor1;font-size: 16px;border-bottom: 1px solid #000;'>\n";
echo "<td><b>Search for Listings</b></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor2;'>\n";
echo "<td><b>By Features</b></td>\n";
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
echo "<option value='business_op'>Business Op</option></select>\n";
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
while ($row_city = mysql_fetch_array($result_city)) {
      echo "<option value='".$row_city['LM_MST_CITY']."'>".$row_city['LM_MST_CITY']."</option>\n";
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
echo "<td><b>Or By MLS Number</b> <input type='text' name='mls_id' size='10' value='$mls_id' /></td>\n";
echo "</tr>\n";
echo "<tr style='background-color: $bgcolor1;'>\n";
echo "<td><input type='submit' name='submit' value='Search' />&nbsp;<input type='submit' name='submit_reset' value='Reset All' />&nbsp;&nbsp;<span style='font-size: 12px;font-style: italic;'>Tip: Try resetting fields in your search to get more results.</span></td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";
/* End Search box */

/* Stop here if there are any errors */
if ($exit > 0) {
   exit;
}

If (!empty($submit)) {
/* Construct sql statement */
switch ($prop_type) {
	case 'mobile':
         $table = 'tbl_idx_residential';
         if ($where_clause_num > 0) { $where_clause .= " AND"; }
         $where_clause .= " LM_MST_PROP_TYP = 'Mobile'";
         $where_clause_num++;
	break;
	case 'condo/villa/townhome':
         $table = 'tbl_idx_residential';
         if ($where_clause_num > 0) { $where_clause .= " AND"; }
         $where_clause .= " LM_MST_PROP_TYP = 'Condo/Villa/Townhome'";
         $where_clause_num++;
	break;
	case 'residential':
         $table = 'tbl_idx_'.$prop_type;
         if ($where_clause_num > 0) { $where_clause .= " AND"; }
         $where_clause .= " LM_MST_PROP_TYP = 'Residential'";
         $where_clause_num++;
	break;
	default:
         $table = 'tbl_idx_'.$prop_type;
	break;
}
if (!empty($mls_id)) {
   $where_clause = " LM_MST_MLS_NO = '$mls_id'";
   $where_clause_num++;
} else {
   if (!empty($agent_id)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " TRIM(LM_MST_LIST_AGT) = '".$agent_id."'";
      $where_clause_num++;
   }
   if (!empty($firm_id)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " TRIM(LM_MST_LIST_FRM) = '".$firm_id."'";
      $where_clause_num++;
   }
   if (!empty($max_price)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      /* $max_price = str_replace(",","",$max_price);
      $max_price = str_replace("$","",$max_price); */
      $where_clause .= " LM_MST_LIST_PRC < $max_price";
      $where_clause_num++;
   }
   if (!empty($city)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_CITY = '$city'";
      $where_clause_num++;
   }
   if (!empty($min_acres)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_ACRES >= $min_acres";
      $where_clause_num++;
   }
   if (!empty($waterfront)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_WFRT_YN = '$waterfront'";
      $where_clause_num++;
   }
   if (!empty($min_beds)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_BDRMS >= $min_beds";
      $where_clause_num++;
   }
   if (!empty($min_baths)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_BATHS >= $min_baths";
      $where_clause_num++;
   }
   if (!empty($min_sqft)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_SQFT_N >= $min_sqft";
      $where_clause_num++;
   }
   if (!empty($min_yr_blt)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_YR_BLT >= $min_yr_blt";
      $where_clause_num++;
   }
   if (!empty($pool)) {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " LM_MST_POOL_YN = '$pool'";
      $where_clause_num++;
   }
}
if (!empty($where_clause)) {
   $where_clause = " WHERE".$where_clause;
}
$sql = "SELECT * FROM ".$table.$where_clause." ORDER BY LM_MST_LIST_PRC DESC";
$result = mysql_query($sql);
$num_rows = mysql_num_rows($result);
/* Debugging info
echo "where_clause_num: $where_clause_num<br />\n";
echo "sql: $sql<br />\n";
echo "num_rows: $num_rows<br />\n"; */

/* Search results message */
echo "<p align='center' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>Your search returned $num_rows results.</p>\n";

switch ($table) {
   case 'tbl_idx_residential':
/* Residential Listings */
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>".$prop_type."</td></tr>\n";
}
while ($row = mysql_fetch_array($result)) {
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><b>MLS# ".$row['LM_MST_MLS_NO']."</b></td>\n";
      echo " <td colspan='2' valign='top' align='center'><b>".$row['LM_MST_CITY']."</b></td>\n";
      echo " <td valign='top' align='right'><b>$".number_format($row['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_residential.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><b>Subdivision:</b> ".$row['LM_MST_SUBDIV']."<br /><b>Bedrooms:</b> ".$row['LM_MST_BDRMS']."<br /><b>Baths:</b> ".$row['LM_MST_BATHS']."</td>\n";
      echo " <td valign='top'><b>Total SqFt:</b> ".$row['LM_MST_SQFT_N']."<br /><b>Year Built:</b> ".$row['LM_MST_YR_BLT']."<br /><b>Pool:</b> ".$row['LM_MST_POOL_YN']."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_residential.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row['LM_MST_VIRT_URL'])) {
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
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>".$prop_type."</td></tr>\n";
}
while ($row = mysql_fetch_array($result)) {
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><b>MLS# ".$row['LM_MST_MLS_NO']."</b></td>\n";
      echo " <td colspan='2' valign='top' align='center'><b>".$row['LM_MST_CITY']."</b></td>\n";
      echo " <td valign='top' align='right'><b>$".number_format($row['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_vacantland.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><b>Subdivision:</b> ".$row['LM_MST_SUBDIV']."<br /><b>Apx. Acres:</b> ".$row['LM_MST_ACRES']."<br /><b>Waterfront:</b> ".$row['LM_MST_WFRT_YN']."</td>\n";
      echo " <td valign='top'><b>Apx. Lot Size:</b> ".$row['LM_MST_DLOT']."<br /><b>Road Front:</b> ".$row['LM_FML_RDFTG']."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_vacantland.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row['LM_MST_VIRT_URL'])) {
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
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>".$prop_type."</td></tr>\n";
}
while ($row = mysql_fetch_array($result)) {
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><b>MLS# ".$row['LM_MST_MLS_NO']."</b></td>\n";
      echo " <td colspan='2' valign='top' align='center'><b>".$row['LM_MST_CITY']."</b></td>\n";
      echo " <td valign='top' align='right'><b>$".number_format($row['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_multi_res.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><b>Units:</b> ".$row['LM_MST_UNITS']."<br /><b>Subdivision:</b> ".$row['LM_MST_SUBDIV']."<br /><b>Waterfront:</b> ".$row['LM_MST_WFRT_YN']."</td>\n";
      echo " <td valign='top'><b>Total SqFt:</b> ".$row['LM_MST_SQFT_N']."<br /><b>Year Built:</b> ".$row['LM_MST_YR_BLT']."<br /><b>Pool:</b> ".$row['LM_MST_POOL_YN']."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_multi_res.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row['LM_MST_VIRT_URL'])) {
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
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>".$prop_type."</td></tr>\n";
}
while ($row = mysql_fetch_array($result)) {
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><b>MLS# ".$row['LM_MST_MLS_NO']."</b></td>\n";
      echo " <td colspan='2' valign='top' align='center'><b>".$row['LM_MST_CITY']."</b></td>\n";
      echo " <td valign='top' align='right'><b>$".number_format($row['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_commercial.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><b>Bldg SqFt:</b> ".$row['LM_MST_SQFT_N']."<br /><b># of Units:</b> ".$row['LM_MST_UNITS']."<br /></td>\n";
      echo " <td valign='top'><b>Asset Sales:</b> ".$row['LM_CMI_INC_OTH']."<br /><b>Year Built:</b> ".$row['LM_MST_YR_BLT']."<br /></td>\n";
      echo " <td valign='top'><a href='".$http_home."details_commercial.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row['LM_MST_VIRT_URL'])) {
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
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>".$prop_type."</td></tr>\n";
}
while ($row = mysql_fetch_array($result)) {
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><b>MLS# ".$row['LM_MST_MLS_NO']."</b></td>\n";
      echo " <td colspan='2' valign='top' align='center'><b>".$row['LM_MST_CITY']."</b></td>\n";
      echo " <td valign='top' align='right'><b>$".number_format($row['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_business_op.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><b>Bldg SqFt:</b> ".$row['LM_MST_SQFT_N']."<br /><b># of Units:</b> ".$row['LM_MST_UNITS']."<br /></td>\n";
      echo " <td valign='top'><b>Asset Sales:</b> ".$row['LM_CMI_INC_OTH']."<br /><b>Year Built:</b> ".$row['LM_MST_YR_BLT']."<br /></td>\n";
      echo " <td valign='top'><a href='".$http_home."details_business_op.php?mls_id=".$row['LM_MST_MLS_NO']."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row['LM_MST_VIRT_URL'])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
     echo "</tr>\n";
}
echo "</table><br />\n";
/* End of Business Op Listings */
   break;
} // End of property type switch

/* Disclaimer */
echo "<p style='font-size: 11px;'>The information contained herein has been provided by REALTORS &reg; Association of Citrus County, Inc. This information is from sources deemed reliable but not guaranteed by REALTORS &reg; Association of Citrus County, Inc. The information is for consumers' personal, non-commerical use and may not be used for any purpose other than identifying properties which consumers may be interested in purchasing. The information contained in this web site is believed to be reliable and while every effort is made to assure that the information is as accurate as possible, the owner of this site (whose name appears above) and Nature Coast Web Design & Marketing, Inc. disclaim any implied warranty or representation about it's accuracy, completeness or appropriateness for any particular purpose. This includes but is not limited to information provided by any third party which is accessed through this site via a hyperlink.<br />Those persons who access this information assume full responsibility for the use of said information and understand and agree that the owner of this site named above, or Nature Coast Web Design & Marketing, Inc., are not responsible or liable for any claim, loss or damage arising from the use of any information contained in this site.<br />Any reference to specific products, companies or services does not necessarily constitute or imply recommendation or endorsement by the owner of this site or Nature Coast Web Design & Marketing, Inc.</p>";

/* Close out the result set */
mysql_free_result($result);
mysql_free_result($result_city);
} // End of If (!empty($submit))
/* Closing connection */
mysql_close($dbcnx);
?>
