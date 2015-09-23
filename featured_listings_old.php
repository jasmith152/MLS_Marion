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
/* for testing only
$agent_id = ""; //for displaying only listings for this agent
$firm_id = ""; //for displaying only listings for this firm
$office_id = ""; //for displaying only listings for a specific office of this firm
$display_agent = ""; //for displaying contact info on details page
$display_firm = ""; //for displaying contact info on details page
$specific_listing = ""; //for displaying only a specific listing (for lists seperate with |)
$limit = ""; //for limiting number of results (also can use $limit_[property_type])
$show = ""; //for displaying only specific property types
$hide = ""; //for hiding specific property types
$sort_order = ""; //for specifying a sort order (duh)
$random = ""; //use 1 to select a listing randomly
 */
$str_url_vars = "";
$str_url_var_num = 0;
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

/* Construct Where clause */
if (!empty($agent_id)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " TRIM(LM_MST_LIST_AGT) = '".$agent_id."'";
   $where_clause_num++;
}
if (!empty($firm_id)) {
   if ($where_clause_num > 0) { $where_clause .= " AND"; }
   $where_clause .= " TRIM(LM_MST_LIST_FRM) = '".$firm_id."'";
   $where_clause_num++;
   if (isset($office_id)) {
      $where_clause .= " AND TRIM(LM_MST_LIST_OFF) = '".$office_id."'";
      $where_clause_num++;
   }
}
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
if (!empty($limit)) {
   $limit_residential = " LIMIT $limit";
   $limit_vacantland = " LIMIT $limit";
   $limit_multires = " LIMIT $limit";
   $limit_commercial = " LIMIT $limit";
   $limit_businessop = " LIMIT $limit";
}
if (!empty($random)) {
   if (!empty($specific_listing)) {
      $mls_ids_array = explode("|", $specific_listing);
      shuffle($mls_ids_array);
      $specific_listing = $mls_ids_array[0];
      if ($limit > 1) {
         for ($i=1; $i<=($limit-1); $i++) {
             $specific_listing .= "|".$mls_ids_array[$i];
         }
      }
   } else {
      if ($order_clause_num > 0) { $order_clause .= ","; }
      if ($order_clause_res_num > 0) { $order_clause_res .= ","; }
      $order_clause = " RAND()";
      $order_clause_res = " RAND()";
      $order_clause_num++;
      $order_clause_res_num++;
   }
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
           $where_clause .= " LM_MST_MLS_NO = '".$mls_id."'";
           $where_clause_num++;
   }
   if ($where_clause_num > 1) { $where_clause .= ")"; }
}
if (!empty($where_clause)) {
   $where_clause = " WHERE".$where_clause;
} else {
   // If no search criteria and no limit is specified, then all listings would be displayed (and probably crash the server)
   if (empty($limit_residential) && empty($limit_vacantland) && empty($limit_multires) && empty($limit_commercial) && empty($limit_businessop)) {
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
           if ($order_clause_num > 0) { $order_clause .= ","; }
           if ($order_clause_res_num > 0) { $order_clause_res .= ","; }
           // property type, then price (low to high)
           $order_clause_res = " LM_MST_PROP_TYP DESC, LM_MST_LIST_PRC";
           $order_clause = " LM_MST_LIST_PRC";
           $order_clause_num++;
           $order_clause_res_num++;
        break;
        case 'price_high':
           if ($order_clause_num > 0) { $order_clause .= ","; }
           if ($order_clause_res_num > 0) { $order_clause_res .= ","; }
           // property type, then price (high to low)
           $order_clause_res = " LM_MST_PROP_TYP DESC, LM_MST_LIST_PRC DESC";
           $order_clause = " LM_MST_LIST_PRC";
           $order_clause_num++;
           $order_clause_res_num++;
        break;
        default:
           if ($order_clause_num > 0) { $order_clause .= ","; }
           if ($order_clause_res_num > 0) { $order_clause_res .= ","; }
           // property type, then price (high to low)
           $order_clause_res = " LM_MST_PROP_TYP DESC, LM_MST_LIST_PRC DESC";
           $order_clause = " LM_MST_LIST_PRC DESC";
           $order_clause_num++;
           $order_clause_res_num++;
        break;
}
if (!empty($order_clause)) {
   $order_clause = " ORDER BY".$order_clause;
}
if (!empty($order_clause_res)) {
   $order_clause_res = " ORDER BY".$order_clause_res;
}

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<div align='center'>$err_msg</div>\n";
}
if ($exit > 0) {
   exit;
}

if (stristr($show,"residential") && !stristr($hide,"residential")) {
/* Residential Listings */
$sql1 = "SELECT * FROM tbl_idx_residential".$where_clause.$order_clause_res.$limit_residential;
if ($result1 = mysql_query($sql1)) {
$num_rows1 = mysql_num_rows($result1);
$prev_prop_type = 'Residential';

/* Debugging info
echo "sql1: $sql1<br />\n";
echo "num_rows1: $num_rows1<br />\n"; */

if ($num_rows1 > 0) {
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
while ($row1 = mysql_fetch_array($result1)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_residential.php?mls_id=".$row1['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row1['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row1['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row1['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row1['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row1['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row1['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row1['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row1['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row1['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row1['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row1['LM_MST_CITY']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row1['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row1['LM_RES_REMARK'],0,97)."...<br /><a href='".$http_home."details_residential.php?mls_id=".$row1['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";

}
echo "</table>\n";
}
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql1: $sql1<br />\n";
}
/* End of Residential Listings */
}

if (stristr($show,"vacant land") && !stristr($hide,"vacant land")) {
/* Vacant Land Listings */
$sql2 = "SELECT * FROM tbl_idx_vacant_land".$where_clause.$order_clause.$limit_vacantland;
if ($result2 = mysql_query($sql2)) {
$num_rows2 = mysql_num_rows($result2);

/* Debugging info
echo "sql2: $sql2<br />\n";
echo "num_rows2: $num_rows2<br />\n"; */

if ($num_rows2 > 0) {
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row2 = mysql_fetch_array($result2)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_vacantland.php?mls_id=".$row2['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row2['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row2['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row2['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row2['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row2['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row2['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row2['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row2['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row2['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row2['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row2['LM_MST_CITY']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row2['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row2['LM_FML_REMARK'],0,97)."...<br /><a href='".$http_home."details_vacantland.php?mls_id=".$row2['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";
}
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql2: $sql2<br />\n";
}
/* End of Vacant Land Listings */
}
if (stristr($show,"multires") && !stristr($hide,"multires")) {
/* Multi Residential Listings */
$sql3 = "SELECT * FROM tbl_idx_multi_res".$where_clause.$order_clause.$limit_multires;
if ($result3 = mysql_query($sql3)) {
$num_rows3 = mysql_num_rows($result3);

/* Debugging info
echo "sql3: $sql3<br />\n";
echo "num_rows3: $num_rows3<br />\n"; */

if ($num_rows3 > 0) {
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row3 = mysql_fetch_array($result3)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_multi_res.php?mls_id=".$row3['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row3['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row3['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row3['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row3['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row3['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row3['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row3['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row3['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row3['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row3['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row3['LM_MST_CITY']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row3['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row3['LM_MLT_REMARK'],0,97)."...<br /><a href='".$http_home."details_multi_res.php?mls_id=".$row3['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";
}
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql3: $sql3<br />\n";
}
/* End of Multi Residential Listings */
}
if (stristr($show,"commercial") && !stristr($hide,"commercial")) {
/* Commercial Listings */
$sql4 = "SELECT * FROM tbl_idx_commercial".$where_clause.$order_clause.$limit_commercial;
if ($result4 = mysql_query($sql4)) {
$num_rows4 = mysql_num_rows($result4);

/* Debugging info
echo "sql4: $sql4<br />\n";
echo "num_rows4: $num_rows4<br />\n"; */

if ($num_rows4 > 0) {
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row4 = mysql_fetch_array($result4)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_commercial.php?mls_id=".$row4['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row4['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row4['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row4['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row4['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row4['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row4['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row4['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row4['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row4['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row4['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row4['LM_MST_CITY']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row4['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row4['LM_CMI_REMARK'],0,97)."...<br /><a href='".$http_home."details_commercial.php?mls_id=".$row4['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";
}
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql4: $sql4<br />\n";
}
/* End of Commercial Listings */
}
if (stristr($show,"business op") && !stristr($hide,"business op")) {
/* Business Op Listings */
$sql5 = "SELECT * FROM tbl_idx_business_op".$where_clause.$order_clause.$limit_businessop;
if ($result5 = mysql_query($sql5)) {
$num_rows5 = mysql_num_rows($result5);

/* Debugging info
echo "sql5: $sql5<br />\n";
echo "num_rows5: $num_rows5<br />\n"; */

if ($num_rows5 > 0) {
echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;background-color: #CCC;'>\n";
while ($row5 = mysql_fetch_array($result5)) {
      echo "<tr>\n";
      echo " <td align='center' valign='middle'><a href='".$http_home."details_business_op.php?mls_id=".$row5['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>";
      if (file_exists($idx_dir."photos/".$row5['LM_MST_MLS_NO']."a.jpg")) {
         echo "<img src='".$http_photos.$row5['LM_MST_MLS_NO']."a.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
      } else {
         if (file_exists($idx_dir."photos/".$row5['LM_MST_MLS_NO']."b.jpg")) {
            echo "<img src='".$http_photos.$row5['LM_MST_MLS_NO']."b.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
         } else {
            if (file_exists($idx_dir."photos/".$row5['LM_MST_MLS_NO']."c.jpg")) {
               echo "<img src='".$http_photos.$row5['LM_MST_MLS_NO']."c.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
            } else {
               if (file_exists($idx_dir."photos/".$row5['LM_MST_MLS_NO']."d.jpg")) {
                  echo "<img src='".$http_photos.$row5['LM_MST_MLS_NO']."d.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
               } else {
                  if (file_exists($idx_dir."photos/".$row5['LM_MST_MLS_NO']."e.jpg")) {
                     echo "<img src='".$http_photos.$row5['LM_MST_MLS_NO']."e.jpg' width='95' height='71' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
                  } else {
                     echo "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
                  }
               }
            }
         }
      }
      echo " </a></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>".$row5['LM_MST_CITY']."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td><b>$".number_format($row5['LM_MST_LIST_PRC'])."</b></td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td>".substr($row5['LM_CMI_REMARK'],0,97)."...<br /><a href='".$http_home."details_business_op.php?mls_id=".$row5['LM_MST_MLS_NO']."&$str_url_vars' target='_blank'>More Details</a></td>\n";
      echo "</tr>\n";
}
echo "</table>\n";
}
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql5: $sql5<br />\n";
}
/* End of Business Op Listings */
}

/* Close out the result set */
if (!empty($result1)) { mysql_free_result($result1); }
if (!empty($result2)) { mysql_free_result($result2); }
if (!empty($result3)) { mysql_free_result($result3); }
if (!empty($result4)) { mysql_free_result($result4); }
if (!empty($result5)) { mysql_free_result($result5); }
/* Closing connection */
mysql_close($dbcnx);
?>
