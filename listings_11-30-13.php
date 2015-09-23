<?php
/* This file is designed to be included on a page, not to be used as a stand-alone page.
 * The calling page will need to declare some config variables.
 */

/* for testing only
$agent_id = ""; //for displaying only listings for this agent
$firm_id = ""; //for displaying only listings for this firm
$office_id = ""; //for displaying only listings for a specific office of this firm
$display_agent = ""; //for displaying contact info on details page
$display_firm = ""; //for displaying contact info on details page
$display_office = ""; //for displaying contact info on details page
$listingsin_city = ""; //for displaying listings in a certain city
$listingsin_subdivision = ""; //for displaying listings in a certain subdivision
$waterfront_yn = ""; //for displaying waterfront or non-waterfront listings
$specific_listing = ""; //for displaying only a specific listing (for lists seperate with |)
$where_custom = ""; //for selecting listings by a custom Where statement
$limit = ""; //for limiting number of results (also can use $limit_[property_type])
$show = ""; //for displaying only specific property types [Residential, Vacant Land, MultiRes, Commercial, Business Op]
$hide = ""; //for hiding specific property types
$sort_order = ""; //for specifying a sort order (duh)
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
$err_msg = getVar('err_msg');
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
$prev_prop_type = 'Residential';

/* Clear certain variables */
unset($str_url_vars);
$str_url_var_num = 0;
unset($where_clause);
$where_clause_num = 0;
unset($order_clause);
$order_clause_num = 0;
unset($order_clause_res);
$order_clause_res_num = 0;

// Connect to the Db
$dbcnx = dbconn($db_host,$db_username,$db_password,$db_name);

/* Check for variables */
if (empty($agent_id) && empty($firm_id) && empty($display_agent) && empty($display_firm)) {
   $err_msg .= "<span class='err_msg'>No Firm or Agent selected.</span>\n";
   $exit = 1;
} else {
   /* Verify agent is allowed to use our system */
   include 'allow_firms.php';
   include 'allow_agents.php';
   if (!stristr($allow_agents,$agent_id) && !stristr($allow_agents,$display_agent) && !stristr($allow_firms,$firm_id) && !stristr($allow_firms,$display_firm)) {
      $err_msg .= "<span class='err_msg'>We are sorry, but this website is not authorized to use this feature.</span>\n";
      $exit = 1;
   }
}
if (empty($show) || $show == 'all') {
   $show = "residential,vacant land,multires,commercial,business op";
}

if (isset($agent_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "agent_id=$agent_id";
   $str_url_var_num++;
}
if (isset($display_agent)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_agent=$display_agent";
   $str_url_var_num++;
}
if (isset($firm_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "firm_id=$firm_id";
   $str_url_var_num++;
}
if (isset($display_firm)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_firm=$display_firm";
   $str_url_var_num++;
}
if (isset($office_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "office_id=$office_id";
   $str_url_var_num++;
}
if (isset($display_office)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "display_office=$display_office";
   $str_url_var_num++;
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

if (empty($bgcolor1)) {
   $bgcolor1 = '#ccc';
}
if (empty($bgcolor2)) {
   $bgcolor2 = '#999';
}

/* Construct Where clause */
if (!empty($agent_id)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " TRIM($db_listing_agent_id) = '".$agent_id."'";
   $where_clause_num++;
}
if (!empty($firm_id)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " TRIM($db_listing_firm_id) = '".$firm_id."'";
   $where_clause_num++;
   if (isset($office_id)) {
      $where_clause .= " AND TRIM($db_office_id) = '".$office_id."'";
      $where_clause_num++;
   }
}
if (!empty($listingsin_city)) {
   if (stripos($listingsin_city,"|")) {
   $cities_array = explode("|", $listingsin_city);
   foreach ($cities_array as $list_key => $city_value) {
           if ($where_clause_num > 0) {
              if ($list_key < 1) {
                 $where_clause .= " AND (";
              } else {
              	 $where_clause .= " OR";
              }
           } else {
              $where_clause .= " (";
           }
           $where_clause .= " $db_city = '".$city_value."'";
           $where_clause_num++;
   }
   $where_clause .= ")";
   } else {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_city = '".$listingsin_city."'";
      $where_clause_num++;
   }
}
if (!empty($listingsin_subdivision)) {
   if (stripos($listingsin_subdivision,"|")) {
   $subdivisions_array = explode("|", $listingsin_subdivision);
   foreach ($subdivisions_array as $list_key => $subdivision_value) {
           if ($where_clause_num > 0) {
              if ($list_key < 1) {
                 $where_clause .= " AND (";
              } else {
              	 $where_clause .= " OR";
              }
           } else {
              $where_clause .= " (";
           }
           $where_clause .= " $db_subdivision = '".$subdivision_value."'";
           $where_clause_num++;
   }
   $where_clause .= ")";
   } else {
      if ($where_clause_num > 0) { $where_clause .= " AND"; }
      $where_clause .= " $db_subdivision = '".$listingsin_subdivision."'";
      $where_clause_num++;
   }
}
if (!empty($waterfront_yn)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " $db_waterfront_yn = '".$waterfront_yn."'";
   $where_clause_num++;
}
if (!empty($specific_listing)) {
   $mls_ids_array = explode("|", $specific_listing);
   foreach ($mls_ids_array as $list_key => $mls_id) {
           if ($where_clause_num > 0) {
              if ($list_key < 1) {
                 $where_clause .= " AND (";
              } else {
              	 $where_clause .= " OR";
              }
           }
           $where_clause .= " $db_mls_id = '".$mls_id."'";
           $where_clause_num++;
   }
   $where_clause .= ")";
}
if (!empty($where_custom)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= $where_custom;
   $where_clause_num++;
}
if (!empty($where_clause)) {
   $where_clause = " WHERE".$where_clause;
} else {
   // If no search criteria and no limit is specified, then all listings would be displayed (and probably crash the server)
   if (empty($limit) && empty($limit_residential) && empty($limit_vacantland) && empty($limit_multires) && empty($limit_commercial) && empty($limit_businessop)) {
      $err_msg .= "<span class='err_msg'>No search criteria specified.</span>\n";
      $exit = 1;
   }
}

/* Construct Order clause */
switch ($sort_order) {
	 case 'none':
	    $order_clause_res = "";
      $order_clause = "";
   break;
   case 'price_low':
      // property type, then price (low to high)
      $order_clause_res = " ORDER BY $db_property_type DESC, $db_price";
      $order_clause = " ORDER BY $db_price";
   break;
   default:
      // property type, then price (high to low)
      $order_clause_res = " ORDER BY $db_property_type DESC, $db_price DESC";
      $order_clause = " ORDER BY $db_price DESC";
   break;
}

/* Construct Limit clause */
if (!empty($limit_residential)) {
   $limit_residential = " LIMIT $limit_residential";
}
if (!empty($limit_vacantland)) {
   $limit_vacantland = " LIMIT $limit_vacantland";
}
if (!empty($limit_multires)) {
   $limit_multires = " LIMIT $limit_multires";
}
if (!empty($limit_commercial)) {
   $limit_commercial = " LIMIT $limit_commercial";
}
if (!empty($limit_businessop)) {
   $limit_businessop = " LIMIT $limit_businessop";
}
if (!empty($limit_rental)) {
   $limit_rental = " LIMIT $limit_rental";
}
if (!empty($limit)) {
   $limit_residential = " LIMIT $limit";
   $limit_vacantland = " LIMIT $limit";
   $limit_multires = " LIMIT $limit";
   $limit_commercial = " LIMIT $limit";
   $limit_businessop = " LIMIT $limit";
   $limit_rental = " LIMIT $limit";
}

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<p class='error'>$err_msg</p>\n";
}
/* Stop here if there are any errors */
if ($exit > 0) {
   exit;
}

if (stristr($show,"residential") && !stristr($hide,"residential")) {
/* Residential Listings */
$sql1 = "SELECT * FROM $db_tbl_residential".$where_clause.$order_clause_res.$limit_residential;
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "where_clause_num: $where_clause_num<br />\n";
   echo "sql1: $sql1<br />\n";
   echo "</p>\n";
}
$result1 = $dbcnx->prepare($sql1);
//$result->bindParam(':mls_id', $mls_id, PDO::PARAM_INT);
$result1->execute($where_clause_arr);
$data_results1 = $result1->fetchAll(PDO::FETCH_ASSOC);
$num_rows1 = count($data_results1);
/* Debugging info */
if ($debugging !== false) {
   echo "<p class='debugging'>\n";
   echo "num_rows1: $num_rows1<br />\n";
   echo "str_url_vars: $str_url_vars<br />\n";
   echo "</p>\n";
}

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows1 > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'>Residential</td></tr>\n";
}
foreach ($data_results1 as $row1) {
      // Initial photo check
      photoCheck($abs_photos,$row1[$db_mls_id]);
      echo "<tr style='background-color: #999'>\n";
      echo " <td valign='top'><strong>MLS# ".$row1[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row1[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row1[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: #CCC'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_residential.php?mls_id=".$row1[$db_mls_id]."&$str_url_vars' target='_blank'>";
      // Display thumbnail photo
      echo thumbnailPhoto($abs_photos,$row1[$db_mls_id]);
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Subdivision:</strong> ".$row1[$db_subdivision]."<br /><strong>Bedrooms:</strong> ".$row1[$db_bedrooms]."<br /><strong>Baths:</strong> ".$row1[$db_baths]."</td>\n";
      echo " <td valign='top'><strong>Total SqFt:</strong> ".$row1[$db_total_sqft]."<br /><strong>Year Built:</strong> ".$row1[$db_year_built]."<br /><strong>Pool:</strong> ".$row1[$db_pool_yn]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_residential.php?mls_id=".$row1[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row1[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />Virtual Tour available\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
}
echo "</table><br />\n";
$prev_prop_type = $row1[$db_property_type];
}
/* End of Residential Listings */

if (stristr($show,"vacant land") && !stristr($hide,"vacant land")) {
/* Vacant Land Listings */
$sql2 = "SELECT * FROM $db_tbl_vacantland".$where_clause.$order_clause.$limit_vacantland;
if ($result2 = mysql_query($sql2)) {
$num_rows2 = mysql_num_rows($result2);

/* Debugging info
echo "sql2: $sql2<br />\n";
echo "num_rows2: $num_rows2<br />\n"; */

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows2 > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'><a name='Vacant_Land'></a>Vacant Land <span style='font-size: 13px; font-weight: normal;'><em>($num_rows2 Found)</em></span></td></tr>\n";
}
while ($row2 = mysql_fetch_array($result2)) {
      echo "<tr style='background-color: $bgcolor2'>\n";
      echo " <td valign='top'><strong>MLS# ".$row2[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row2[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row2[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: $bgcolor1'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_vacantland.php?mls_id=".$row2[$db_mls_id]."&$str_url_vars' target='_blank'>";
      if (file_exists($abs_photos.$row2[$db_mls_id]."a.jpg")) {
         echo "<img src='".$http_photos.$row2[$db_mls_id]."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($abs_photos.$row2[$db_mls_id]."b.jpg")) {
            echo "<img src='".$http_photos.$row2[$db_mls_id]."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($abs_photos.$row2[$db_mls_id]."c.jpg")) {
               echo "<img src='".$http_photos.$row2[$db_mls_id]."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($abs_photos.$row2[$db_mls_id]."d.jpg")) {
                  echo "<img src='".$http_photos.$row2[$db_mls_id]."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($abs_photos.$row2[$db_mls_id]."e.jpg")) {
                     echo "<img src='".$http_photos.$row2[$db_mls_id]."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Subdivision:</strong> ".$row2[$db_subdivision]."<br /><strong>Apx. Acres:</strong> ".$row2[$db_acres]."<br /><strong>Waterfront:</strong> ".$row2[$db_waterfront_yn]."</td>\n";
      echo " <td valign='top'><strong>Apx. Lot Size:</strong> ".$row2[$db_lot_size]."<br /><strong>Road Front:</strong> ".$row2[$db_road_front]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_vacantland.php?mls_id=".$row2[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row2[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' /><span style='font-size: 13px;font-style: italic;'>Virtual Tour available</span>\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
      //echo "<tr style='background-color: #CCC'><td colspan='4' height='3'><hr size='1' color='#000000' /></td></tr>\n";
}
echo "</table><br />\n";
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql2: $sql2<br />\n";
}
/* End of Vacant Land Listings */
}
if (stristr($show,"multires") && !stristr($hide,"multires")) {
/* Multi Residential Listings */
$sql3 = "SELECT * FROM $db_tbl_multires".$where_clause.$order_clause.$limit_multires;
if ($result3 = mysql_query($sql3)) {
$num_rows3 = mysql_num_rows($result3);

/* Debugging info
echo "sql3: $sql3<br />\n";
echo "num_rows3: $num_rows3<br />\n"; */

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows3 > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'><a name='Multi_Res'></a>Multi Residential <span style='font-size: 13px; font-weight: normal;'><em>($num_rows3 Found)</em></span></td></tr>\n";
}
while ($row3 = mysql_fetch_array($result3)) {
      echo "<tr style='background-color: $bgcolor2'>\n";
      echo " <td valign='top'><strong>MLS# ".$row3[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row3[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row3[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: $bgcolor1'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_multi_res.php?mls_id=".$row3[$db_mls_id]."&$str_url_vars' target='_blank'>";
      if (file_exists($abs_photos.$row3[$db_mls_id]."a.jpg")) {
         echo "<img src='".$http_photos.$row3[$db_mls_id]."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($abs_photos.$row3[$db_mls_id]."b.jpg")) {
            echo "<img src='".$http_photos.$row3[$db_mls_id]."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($abs_photos.$row3[$db_mls_id]."c.jpg")) {
               echo "<img src='".$http_photos.$row3[$db_mls_id]."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($abs_photos.$row3[$db_mls_id]."d.jpg")) {
                  echo "<img src='".$http_photos.$row3[$db_mls_id]."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($abs_photos.$row3[$db_mls_id]."e.jpg")) {
                     echo "<img src='".$http_photos.$row3[$db_mls_id]."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Units:</strong> ".$row3[$db_units]."<br /><strong>Subdivision:</strong> ".$row3[$db_subdivision]."<br /><strong>Waterfront:</strong> ".$row3[$db_waterfront_yn]."</td>\n";
      echo " <td valign='top'><strong>Total SqFt:</strong> ".$row3[$db_total_sqft]."<br /><strong>Year Built:</strong> ".$row3[$db_year_built]."<br /><strong>Pool:</strong> ".$row3[$db_pool_yn]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_multi_res.php?mls_id=".$row3[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row3[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' /><span style='font-size: 13px;font-style: italic;'>Virtual Tour available</span>\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
      //echo "<tr style='background-color: #CCC'><td colspan='4' height='3'><hr size='1' color='#000000' /></td></tr>\n";
}
echo "</table><br />\n";
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql3: $sql3<br />\n";
}
/* End of Multi Residential Listings */
}
if (stristr($show,"commercial") && !stristr($hide,"commercial")) {
/* Commercial Listings */
$sql4 = "SELECT * FROM $db_tbl_commercial".$where_clause.$order_clause.$limit_commercial;
if ($result4 = mysql_query($sql4)) {
$num_rows4 = mysql_num_rows($result4);

/* Debugging info
echo "sql4: $sql4<br />\n";
echo "num_rows4: $num_rows4<br />\n"; */

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows4 > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'><a name='Commercial'></a>Commercial <span style='font-size: 13px; font-weight: normal;'><em>($num_rows4 Found)</em></span></td></tr>\n";
}
while ($row4 = mysql_fetch_array($result4)) {
      echo "<tr style='background-color: $bgcolor2'>\n";
      echo " <td valign='top'><strong>MLS# ".$row4[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row4[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row4[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: $bgcolor1'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_commercial.php?mls_id=".$row4[$db_mls_id]."&$str_url_vars' target='_blank'>";
      if (file_exists($abs_photos.$row4[$db_mls_id]."a.jpg")) {
         echo "<img src='".$http_photos.$row4[$db_mls_id]."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($abs_photos.$row4[$db_mls_id]."b.jpg")) {
            echo "<img src='".$http_photos.$row4[$db_mls_id]."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($abs_photos.$row4[$db_mls_id]."c.jpg")) {
               echo "<img src='".$http_photos.$row4[$db_mls_id]."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($abs_photos.$row4[$db_mls_id]."d.jpg")) {
                  echo "<img src='".$http_photos.$row4[$db_mls_id]."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($abs_photos.$row4[$db_mls_id]."e.jpg")) {
                     echo "<img src='".$http_photos.$row4[$db_mls_id]."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Bldg SqFt:</strong> ".$row4[$db_total_sqft]."<br /><strong># of Units:</strong> ".$row4[$db_units]."<br /></td>\n";
      echo " <td valign='top'><strong>Asset Sales:</strong> ".$row4[$db_asset_sales]."<br /><strong>Year Built:</strong> ".$row4[$db_year_built]."<br /></td>\n";
      echo " <td valign='top'><a href='".$http_home."details_commercial.php?mls_id=".$row4[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row4[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' /><span style='font-size: 13px;font-style: italic;'>Virtual Tour available</span>\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
      //echo "<tr style='background-color: #CCC'><td colspan='4' height='3'><hr size='1' color='#000000' /></td></tr>\n";
}
echo "</table><br />\n";
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql4: $sql4<br />\n";
}
/* End of Commercial Listings */
}
if (stristr($show,"business op") && !stristr($hide,"business op")) {
/* Business Op Listings */
$sql5 = "SELECT * FROM $db_tbl_businessop".$where_clause.$order_clause.$limit_businessop;
if ($result5 = mysql_query($sql5)) {
$num_rows5 = mysql_num_rows($result5);

/* Debugging info
echo "sql5: $sql5<br />\n";
echo "num_rows5: $num_rows5<br />\n"; */

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows5 > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'><a name='Business_Op'></a>Business Opportunity <span style='font-size: 13px; font-weight: normal;'><em>($num_rows5 Found)</em></span></td></tr>\n";
}
while ($row5 = mysql_fetch_array($result5)) {
      echo "<tr style='background-color: $bgcolor2'>\n";
      echo " <td valign='top'><strong>MLS# ".$row5[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row5[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row5[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: $bgcolor1'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_business_op.php?mls_id=".$row5[$db_mls_id]."&$str_url_vars' target='_blank'>";
      if (file_exists($abs_photos.$row5[$db_mls_id]."a.jpg")) {
         echo "<img src='".$http_photos.$row5[$db_mls_id]."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($abs_photos.$row5[$db_mls_id]."b.jpg")) {
            echo "<img src='".$http_photos.$row5[$db_mls_id]."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($abs_photos.$row5[$db_mls_id]."c.jpg")) {
               echo "<img src='".$http_photos.$row5[$db_mls_id]."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($abs_photos.$row5[$db_mls_id]."d.jpg")) {
                  echo "<img src='".$http_photos.$row5[$db_mls_id]."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($abs_photos.$row5[$db_mls_id]."e.jpg")) {
                     echo "<img src='".$http_photos.$row5[$db_mls_id]."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Subdivision:</strong> ".$row5[$db_subdivision]."<br /><strong>Bedrooms:</strong> ".$row5[$db_bedrooms]."<br /><strong>Baths:</strong> ".$row5[$db_baths]."</td>\n";
      echo " <td valign='top'><strong>Total SqFt:</strong> ".$row5[$db_total_sqft]."<br /><strong>Year Built:</strong> ".$row5[$db_year_built]."<br /><strong>Pool:</strong> ".$row5[$db_pool_yn]."</td>\n";
      echo " <td valign='top'><a href='".$http_home."details_business_op.php?mls_id=".$row5[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row5[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' /><span style='font-size: 13px;font-style: italic;'>Virtual Tour available</span>\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
      //echo "<tr style='background-color: #CCC'><td colspan='4' height='3'><hr size='1' color='#000000' /></td></tr>\n";
}
echo "</table><br />\n";
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql5: $sql5<br />\n";
}
/* End of Business Op Listings */
}

if (stristr($show,"rental") && !stristr($hide,"rental")) {
/* Rental Listings */
$sql6 = "SELECT * FROM $db_tbl_rental".$where_clause.$order_clause_res.$limit_rental;
if ($result6 = mysql_query($sql6)) {
$num_rows6 = mysql_num_rows($result6);
$prev_prop_type = 'Rental';

/* Debugging info
echo "sql1: $sql1<br />\n";
echo "num_rows1: $num_rows1<br />\n"; */

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
if ($num_rows6 > 0) {
   echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'><a name='Rental'></a>Rental <span style='font-size: 13px; font-weight: normal;'><em>($num_rows6 Found)</em></span></td></tr>\n";
}
while ($row6 = mysql_fetch_array($result6)) {
      if ($prev_prop_type != $row6[$db_property_type]) {
         echo "<tr><td colspan='4' align='center' style='background-color: #FFF;font-size: 16px;font-weight: bold;'><a name='".$row6[$db_property_type]."'></a>".$row6[$db_property_type]."</td></tr>\n";
      }
      echo "<tr style='background-color: $bgcolor2'>\n";
      echo " <td valign='top'><strong>MLS# ".$row6[$db_mls_id]."</strong></td>\n";
      echo " <td colspan='2' valign='top' align='center'><strong>".$row6[$db_city]."</strong></td>\n";
      echo " <td valign='top' align='right'><strong>$".number_format($row6[$db_price])."</strong></td>\n";
      echo "</tr>\n";
      echo "<tr style='background-color: $bgcolor1'>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_residential.php?mls_id=".$row6[$db_mls_id]."&$str_url_vars' target='_blank'>";
      if (file_exists($abs_photos.$row6[$db_mls_id]."a.jpg")) {
         echo "<img src='".$http_photos.$row6[$db_mls_id]."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($abs_photos.$row6[$db_mls_id]."b.jpg")) {
            echo "<img src='".$http_photos.$row6[$db_mls_id]."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($abs_photos.$row6[$db_mls_id]."c.jpg")) {
               echo "<img src='".$http_photos.$row6[$db_mls_id]."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($abs_photos.$row6[$db_mls_id]."d.jpg")) {
                  echo "<img src='".$http_photos.$row6[$db_mls_id]."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($abs_photos.$row6[$db_mls_id]."e.jpg")) {
                     echo "<img src='".$http_photos.$row6[$db_mls_id]."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo " <td valign='top'><strong>Subdivision:</strong><br /> ".$row6[$db_subdivision]."<br /><strong>Bedrooms:</strong> ".$row6[$db_bedrooms]."<br /><strong>Baths:</strong> ".$row6[$db_baths]."</td>\n";
      echo " <td valign='top'><strong>Total SqFt:</strong> ".$row6[$db_living_sqft]."<br /><strong>Year Built:</strong> ".$row6[$db_year_built]."<br /><strong>Pool:</strong> ".$row6[$db_pool_yn]."</td>\n";
      echo " <td valign='top' align='right'><a href='".$http_home."details_rental.php?mls_id=".$row6[$db_mls_id]."&$str_url_vars' target='_blank' style='font-weight: bold;text-decoration: none;'><img src='".$http_imgs."details.gif' border='0' alt='Click for more details' title='Click for more details' />More Details</a><br />\n";
      if (!empty($row6[$db_virtual_tour_url])) {
         echo "  <img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' /><span style='font-size: 13px;font-style: italic;'>Virtual Tour available</span>\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
      $prev_prop_type = $row6[$db_property_type];
      //echo "<tr style='background-color: #CCC'><td colspan='4' height='3'><hr size='1' color='#000000' /></td></tr>\n";
}
echo "</table><br />\n";
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql1: $sql6<br />\n";
}
/* End of Rental Listings */
}

/* Disclaimer */
echo "<p style='font-size: 11px;'>The information contained herein has been provided by REALTORS &reg; Association of Citrus County, Inc. This information is from sources deemed reliable but not guaranteed by REALTORS &reg; Association of Citrus County, Inc. The information is for consumers' personal, non-commerical use and may not be used for any purpose other than identifying properties which consumers may be interested in purchasing. The information contained in this web site is believed to be reliable and while every effort is made to assure that the information is as accurate as possible, the owner of this site (whose name appears above) and Nature Coast Web Design & Marketing, Inc. disclaim any implied warranty or representation about it's accuracy, completeness or appropriateness for any particular purpose. This includes but is not limited to information provided by any third party which is accessed through this site via a hyperlink.<br />Those persons who access this information assume full responsibility for the use of said information and understand and agree that the owner of this site named above, or Nature Coast Web Design & Marketing, Inc., are not responsible or liable for any claim, loss or damage arising from the use of any information contained in this site.<br />Any reference to specific products, companies or services does not necessarily constitute or imply recommendation or endorsement by the owner of this site or Nature Coast Web Design & Marketing, Inc.</p>";

/* Close out the result set */
if (is_resource($result1)) { mysql_free_result($result1); }
if (is_resource($result2)) { mysql_free_result($result2); }
if (is_resource($result3)) { mysql_free_result($result3); }
if (is_resource($result4)) { mysql_free_result($result4); }
if (is_resource($result5)) { mysql_free_result($result5); }
if (is_resource($result6)) { mysql_free_result($result6); }
/* Closing connection */
mysql_close($dbcnx);
?>
