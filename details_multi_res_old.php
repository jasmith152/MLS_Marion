<?php
// Establish GET & POST variables
//import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];
if (!empty($_GET['mls_id'])) {
   $mls_id = $_GET['mls_id'];
   $firm_id = $_GET['firm_id'];
   $display_firm = $_GET['display_firm'];
   $office_id = $_GET['office_id'];
   $display_office = $_GET['display_office'];
   $agent_id = $_GET['agent_id'];
   $display_agent = $_GET['display_agent'];
   $require_login = $_GET['require_login'];
   $login_fields = $_GET['login_fields'];
   $user_email = $_GET['user_email'];
   $err_msg = $_GET['err_msg'];
}
if (!empty($_POST['mls_id'])) {
   $mls_id = $_POST['mls_id'];
   $firm_id = $_POST['firm_id'];
   $display_firm = $_POST['display_firm'];
   $office_id = $_POST['office_id'];
   $display_office = $_POST['display_office'];
   $agent_id = $_POST['agent_id'];
   $display_agent = $_POST['display_agent'];
   $require_login = $_POST['require_login'];
   $login_fields = $_POST['login_fields'];
   $user_email = $_POST['user_email'];
   $err_msg = $_POST['err_msg'];
}

/* Set some variables */
//$idx_dir = "/home/idx/";
$idx_dir = "/home/mychurchserver/domains/citrusmls.mychurchserver.com/public_html/";
$http_home = "http://citrusmls.mychurchserver.com/";
$http_photos = $http_home."photos/";
$http_mugs = $http_home."agents/";
$http_imgs = $http_home."images/";
/* for testing only
$mls_id = ""; */

/* Check for variables */
if (empty($mls_id)) {
   $err_msg = "<span class='err_msg'>No Listing selected.</span>\n";
   $exit = 1;
}
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

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<div align='center'>$err_msg</div>\n";
}
if ($exit > 0) {
   exit;
}

// Require the customer's information, if requested by referring website.
if ($require_login == 'Y') {
   require 'user_login.php';
}

//Include Db connection script
include 'dbconn.php';

/* Select listing details */
$sql1 = "SELECT * FROM tbl_idx_multi_res WHERE LM_MST_MLS_NO = '".$mls_id."'";
$result1 = @mysql_query($sql1);
$num_rows1 = mysql_num_rows($result1);
$row1 = mysql_fetch_array($result1);

// Check for office info
if ($display_office == '' && $office_id != '') {
   $display_office = $office_id;
}
/* Insist on having a display_office, set a default*/
if ($display_office == '') {
   $display_office = '0';
}
// Determine display firm/agent and select their info
if (!empty($display_agent) || !empty($agent_id)) {
   if (empty($display_agent) && !empty($agent_id)) {
      $display_agent = $agent_id;
   }
   $sql2 = "SELECT agent.*,firm.* FROM tbl_agents_info AS agent, tbl_firms_info AS firm WHERE agent.agent_id = '$display_agent' AND agent.firm_id = firm.firm_id";
   $sql2 .= " AND firm.off_id = '$display_office'";
} else {
   if (empty($display_firm) && !empty($firm_id)) {
      $display_firm = $firm_id;
   }
   if ($display_office == '' && $office_id != '') {
      $display_office = $office_id;
   }
   /* Insist on having a display_office, set a default*/
   if ($display_office == '') {
      $display_office = '0';
   }
   if ($display_firm == ltrim($row1['LM_MST_LIST_FRM']) && $display_office == ltrim($row1['LM_MST_LIST_OFF'])) {
      $display_agent = ltrim($row1['LM_MST_LIST_AGT']);
      $sql2 = "SELECT agent.*,firm.* FROM tbl_agents_info AS agent,tbl_firms_info AS firm WHERE agent.agent_id = '$display_agent' AND agent.firm_id = firm.firm_id";
   } else {
      $sql2 = "SELECT firm.* FROM tbl_firms_info AS firm WHERE firm.firm_id = '$display_firm'";
   }
   $sql2 .= " AND firm.off_id = '$display_office'";
}

// Determine listing agent and select office name
if ((!empty($display_firm) && ltrim($row1['LM_MST_LIST_FRM']) != $display_firm) || (!empty($display_agent) && ltrim($row1['LM_MST_LIST_AGT']) != $display_agent)) {
   $sql_lister = "SELECT MM_OFF_NAME FROM tbl_idx_agents WHERE MM_AGT_AGT_ID = '".$row1['LM_MST_LIST_AGT']."'";
   $result_lister = @mysql_query($sql_lister);
   $row_lister = mysql_fetch_array($result_lister);
}

if ($result2 = mysql_query($sql2)) {
   $row2 = mysql_fetch_array($result2);
   for ($i = 0; $i < mysql_num_fields($result2); $i++) {
       $meta = mysql_fetch_field($result2, $i);
       $row2_assoc[$meta ->table . '.' . $meta->name] = $row2[$i];
   }
} else {
   echo "<p>Error performing query: ".mysql_error()."</p>\n";
   echo "sql2: $sql2<br />\n";
}
if (!empty($display_agent)) {
   $agent_name = $row2_assoc['agent.fname']." ".$row2_assoc['agent.lname'];
   $agent_phone1 = "(".substr($row2_assoc['agent.phone1'],0,3).")".substr($row2_assoc['agent.phone1'],3,3)."-".substr($row2_assoc['agent.phone1'],6,4);
   if (!empty($row2_assoc['agent.phone2'])) {
      $agent_phone2 = "(".substr($row2_assoc['agent.phone2'],0,3).")".substr($row2_assoc['agent.phone2'],3,3)."-".substr($row2_assoc['agent.phone2'],6,4);
   }
   $to_email = $row2_assoc['agent.email_leads'];
   $to_name = $agent_name;
   if (!empty($row2_assoc['agent.website1'])) {
      $website = $row2_assoc['agent.website1'];
   } else {
      $website = $row2_assoc['firm.website1'];
   }
} else {
   $to_email = $row2_assoc['firm.email_leads'];
   $to_name = $row2_assoc['firm.name'];
   $website = $row2_assoc['firm.website1'];
}

// Create link variable to this specific listing
$link_back = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]."?mls_id=".$row1['LM_MST_MLS_NO'];
if (isset($display_agent)) {
   $link_back .= "&display_agent=$display_agent";
}
if (isset($display_firm)) {
   $link_back .= "&display_firm=$display_firm";
}
if (isset($display_office)) {
   $link_back .= "&display_office=$display_office";
}

/* Debugging info
echo "sql1: $sql1<br />\n";
echo "num_rows1: $num_rows1<br />\n";
echo "sql2: $sql2<br />\n"; */

// Function for finding photos for listings
function photo_exists($uploaddir_abs,$listing_num,$photo_num) {
  if (file_exists($uploaddir_abs.$listing_num.$photo_num.'.jpg')) {
    return $listing_num.$photo_num.'.jpg';
  } elseif (file_exists($uploaddir_abs.$listing_num.$photo_num.'.JPG')) {
    return $listing_num.$photo_num.'.JPG';
  } elseif (file_exists($uploaddir_abs.$listing_num.$photo_num.'.gif')) {
    return $listing_num.$photo_num.'.gif';
  } elseif (file_exists($uploaddir_abs.$listing_num.$photo_num.'.GIF')) {
    return $listing_num.$photo_num.'.GIF';
  } elseif (file_exists($uploaddir_abs.$listing_num.$photo_num.'.png')) {
    return $listing_num.$photo_num.'.png';
  } elseif (file_exists($uploaddir_abs.$listing_num.$photo_num.'.PNG')) {
    return $listing_num.$photo_num.'.PNG';
  } else {
    return FALSE;
  }
}
?>
<SCRIPT LANGUAGE='JAVASCRIPT' TYPE='TEXT/JAVASCRIPT'>
<!--
var win=null;
function NewWindow(mypage,myname,w,h,pos,infocus){
if(pos=="random"){myleft=(screen.width)?Math.floor(Math.random()*(screen.width-w)):100;mytop=(screen.height)?Math.floor(Math.random()*((screen.height-h)-75)):100;}
if(pos=="center"){myleft=(screen.width)?(screen.width-w)/2:100;mytop=(screen.height)?(screen.height-h)/2:100;}
else if((pos!='center' && pos!="random") || pos==null){myleft=0;mytop=20}
settings="width=" + w + ",height=" + h + ",top=" + mytop + ",left=" + myleft + ",scrollbars=yes,location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=yes";win=window.open(mypage,myname,settings);
win.focus();}
// -->
</script>
<SCRIPT LANGUAGE='JAVASCRIPT' TYPE='TEXT/JAVASCRIPT'>
<!--
function roll(img_name1, img_src1)
   {
   document[img_name1].src = img_src1;
   }
// -->
</script>
<?php
echo "<table width='780' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;background-color: #CCC;border: 1px solid #999;'>\n";
echo " <tr>\n";
echo "  <td align='center'>\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0'>\n";
echo "    <td width='50%' valign='top'>\n";
/* Find photos and display them */
echo " <table cellpadding='0' cellspacing='0' border='0'>\n";
$photo_count = 0;
for ($a = 'a'; $a <= 'j'; $a++) {
   if (photo_exists($idx_dir.'photos/',$mls_id,$a)) {
      $photo_src = photo_exists($idx_dir.'photos/',$mls_id,$a);
      $photo_count++;
      if ($photo_count == 1) {
         $img_info = getimagesize($idx_dir.'photos/'.$photo_src);
         if ($img_info[1] < 390) {
            echo "  <tr><td colspan='5' align='center'><a name='photo'></a><img src='".$http_photos.$photo_src."' alt='Photo' title='Photo' name='Photo' ".$img_info[3]." border='0' /></td></tr>\n";
         } else {
            echo "  <tr><td colspan='5' align='center'><a name='photo'></a><img src='".$http_photos.$photo_src."' alt='Photo' title='Photo' name='Photo' width='390' height='293' border='0' /></td></tr>\n";
         }
         echo "  <tr>\n";
      }
      echo "   <td align='center'><a href='#photo' onclick=\"roll('Photo', '".$http_photos.$photo_src."')\"><img src='".$http_photos.$photo_src."' alt='Photo Thumbnail' title='Photo Thumbnail' width='78' height='59' border='0' /></a></td>\n";
      if ($photo_count == 5) {
         echo "  </tr>\n";
         echo "  <tr>\n";
      }
      if ($photo_count == 10) {
         echo "  </tr>\n";
      }
   }
}
if ($photo_count == 0) {
   echo "  <tr><td align='center'><img src='".$http_imgs."nophoto.jpg' width='120' height='72' border='0' alt='No photo available' title='No photo available' /></td></tr>\n";
} else {
   if ($photo_count < 5) {
      for ($j = $photo_count; $j <= 5; $j++) {
         echo "   <td>&nbsp;</td>\n";
      }
      echo "  </tr>\n";
   } elseif ($photo_count < 10) {
      for (; $photo_count <= 10; $photo_count++) {
         echo "   <td>&nbsp;</td>\n";
      }
      echo "  </tr>\n";
   }
}
echo " </table>\n";

echo "    </td>\n";
echo "    <td width='50%' valign='top'>\n";
echo "     <table width='75%' border='0' cellpadding='0' cellspacing='2' style='background-color: #E2EEFB; border: solid 1px #A8D2F5; font-size: 13px; font-weight: bold; padding: 5px;'><tr>\n";
echo "      <td width='50%' valign='top'><a href=\"javascript:NewWindow('contact_agent.php?mls_id=$mls_id&to_email=$to_email&to_name=$to_name&firm_name=".$row2_assoc['firm.name']."&website=$website','contact','550','450','center','front');\"><img src='".$http_imgs."contactus.gif' border='0' alt='Contact Us icon' />";
if($agent_id == '95'){echo "Contact Me</a>";}else{echo "Contact Us</a>";}
echo "<br /><br /><a href='#' onClick='window.print()'><img src='".$http_imgs."print.gif' border='0' alt='Print icon' />Print</a></td>\n";
echo "      <td width='50%' valign='top'><a href=\"javascript:NewWindow('contact_friend.php?mls_id=$mls_id&price=".number_format($row1['LM_MST_LIST_PRC'])."&link_back=$link_back','contactfriend','550','450','center','front');\"><img src='".$http_imgs."email.gif' border='0' alt='Email icon' />Email a Friend</a>";
echo "<br /><br /><a href='http://www.google.com/maps?f=q&hl=en&q=".$row1['LM_MST_STR_NO']."+".$row1['LM_MST_STR_DIR']."+".str_replace(" ","+",$row1['LM_MST_STR_NAM'])."+".$row1['LM_MST_ZIP']."&ie=UTF8&z=12&spn=0.146152,0.322037&om=1' target='_blank'><img src='".$http_imgs."map.gif' border='0' alt='Map icon' />Map Location</a></td>\n";
echo "     </tr></table><br />\n";
echo "    <p style='font-size: 16px;font-weight: bold; text-align: center'>MLS# ".$row1['LM_MST_MLS_NO']."<br />".$row1['LM_MST_PROP_TYP']."<br />".$row1['LM_MST_CITY']."</p>\n";
echo "    <p style='font-size: 16px;font-weight: bold; text-align: center'><span style='font-size: 14px;'>Offered at</span><br />$".number_format($row1['LM_MST_LIST_PRC'])."</p>\n";
if (!empty($row1['LM_MST_VIRT_URL'])) {
   if (substr($row1['LM_MST_VIRT_URL'],0,7) != 'http://') {
      $virt_url = "http://".$row1['LM_MST_VIRT_URL'];
   } else {
      $virt_url = $row1['LM_MST_VIRT_URL'];
   }
   echo "    <p style='font-size: 14px;font-weight: bold; text-align: center'><a href='$virt_url' target='_blank'><img src='".$http_imgs."vir_tour.gif' border='0' alt='Virtual Tour icon' />View the Virtual Tour</a></p>\n";
}
if ($photo_count > 1) {
   echo "    <p style='font-size: 12px; text-align: left;'>Click on a photo thumbnail to the left to view that photo.</p>\n";
}
echo "    </td>\n";
echo "   </table>\n";
echo "  </td>\n";
echo " </tr>\n";
echo " <tr>\n";
echo "  <td align='center' style='background-color: #999;font-size: 16px;font-weight: bold;'>Features</td>\n";
echo " </tr>\n";
echo " <tr>\n";
echo "  <td align='center'>\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
echo "     <tr><td width='15%'><b>Cross Street:</b> </td><td width='40%'>".$row1['LM_MST_CRSTREET']."</td>\n";
echo "     <td width='20%'><b># Units:</b> </td><td width='45%'>".$row1['LM_MST_UNITS']."</td></tr>\n";
echo "     <tr><td><b>County:</b> </td><td>".$row1['LM_MST_COUNTY']."</td>\n";
echo "     <td><b>Area/Zone:</b> </td><td>".$row1['LM_MST_ZONE']."</td></tr>\n";
echo "     <tr><td><b>Subdivision:</b> </td><td>".$row1['LM_MST_SUBDIV']."</td>\n";
echo "     <td><b>Year Built:</b> </td><td>".$row1['LM_MST_YR_BLT']."</td></tr>\n";
echo "     <tr><td><b>Waterfront:</b> </td><td>".$row1['LM_MST_WFRT_YN']."</td>\n";
echo "     <td><b>Apx. Acres:</b> </td><td>".$row1['LM_MST_ACRES']."</td></tr>\n";
echo "     <tr><td><b>Waterfront Ft:</b> </td><td>".$row1['LM_MST_WTRFRT_N']."</td>\n";
echo "     <td><b>Sec/Twp/Rng:</b> </td><td>".$row1['LM_MST_SEC']."/".$row1['LM_MST_TWP']."/".$row1['LM_MST_RANGE']."</td></tr>\n";
echo "     <tr><td><b>Private Pool:</b> </td><td>".$row1['LM_MST_POOL_YN']."</td>\n";
echo "     <td><b>Alt Key:</b> </td><td>".$row1['LM_MST_PARC_NO']."</td></tr>\n";
echo "     <tr><td><b>Total Bldg SqFt:</b> </td><td>".$row1['LM_MST_SQFT_N']."</td>\n";
echo "     <td><b>Flood Plain:</b> </td><td>".$row1['LM_MST_FLD_YN']."</td></tr>\n";
echo "     <tr><td><b>Apx. Lot Size:</b> </td><td>".$row1['LM_MST_DLOT']."</td>\n";
echo "     <td colspan='2'>&nbsp;</td></tr>\n";
echo "     <tr><td valign='top'><b>Public Rems:</b> </td><td colspan='3' valign='top'>".$row1['LM_MLT_REMARK']."</td></tr>\n";
echo "     <tr><td valign='top'><b>Directions:</b> </td><td colspan='3' valign='top'>".$row1['LM_MST_DIR']."</td></tr>\n";
echo "   </table>\n";
echo "  </td>\n";
echo " </tr>\n";
echo " <tr>\n";
echo "  <td align='center'>\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
echo "     <tr style='background-color: #999;'><td>&nbsp;</td><td><b>Unit #</b></td><td><b>Month Rent</b></td><td><b>Beds</b></td><td><b>Baths</b></td><td><b>Apx Liv SqFt</b></td><td><b>Lease Exp</b></td><td><b>Covered Parking/Garage</b></td></tr>\n";
if (!empty($row1['LM_MLT_U1_RENT'])) {
   echo "     <tr><td><b>U1</b></td><td>".$row1['LM_MLT_U1_ID']."</td><td>$".number_format($row1['LM_MLT_U1_RENT'])."</td><td>".$row1['LM_MLT_N_BED1']."</td><td>".$row1['LM_MLT_N_BTH1']."</td><td>".$row1['LM_MLT_SQFT1']."</td><td>".$row1['LM_MLT_TRMUN1']."</td><td>".$row1['LM_MLT_GARYN_U1']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U2_RENT'])) {
   echo "     <tr><td><b>U2</b></td><td>".$row1['LM_MLT_U2_ID']."</td><td>$".number_format($row1['LM_MLT_U2_RENT'])."</td><td>".$row1['LM_MLT_N_BED2']."</td><td>".$row1['LM_MLT_N_BTH2']."</td><td>".$row1['LM_MLT_SQFT2']."</td><td>".$row1['LM_MLT_TRMUN2']."</td><td>".$row1['LM_MLT_GARYN_U2']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U3_RENT'])) {
   echo "     <tr><td><b>U3</b></td><td>".$row1['LM_MLT_U3_ID']."</td><td>$".number_format($row1['LM_MLT_U3_RENT'])."</td><td>".$row1['LM_MLT_N_BED3']."</td><td>".$row1['LM_MLT_N_BTH3']."</td><td>".$row1['LM_MLT_SQFT3']."</td><td>".$row1['LM_MLT_TRMUN3']."</td><td>".$row1['LM_MLT_GARYN_U3']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U4_RENT'])) {
   echo "     <tr><td><b>U4</b></td><td>".$row1['LM_MLT_U4_ID']."</td><td>$".number_format($row1['LM_MLT_U4_RENT'])."</td><td>".$row1['LM_MLT_N_BED4']."</td><td>".$row1['LM_MLT_N_BTH4']."</td><td>".$row1['LM_MLT_SQFT4']."</td><td>".$row1['LM_MLT_TRMUN4']."</td><td>".$row1['LM_MLT_GARYN_U4']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U5_RENT'])) {
   echo "     <tr><td><b>U5</b></td><td>".$row1['LM_MLT_U5_ID']."</td><td>$".number_format($row1['LM_MLT_U5_RENT'])."</td><td>".$row1['LM_MLT_N_BED5']."</td><td>".$row1['LM_MLT_N_BTH5']."</td><td>".$row1['LM_MLT_SQFT5']."</td><td>".$row1['LM_MLT_TRMUN5']."</td><td>".$row1['LM_MLT_GARYN5']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U6_RENT'])) {
   echo "     <tr><td><b>U6</b></td><td>".$row1['LM_MLT_U6_ID']."</td><td>$".number_format($row1['LM_MLT_U6_RENT'])."</td><td>".$row1['LM_MLT_N_BED6']."</td><td>".$row1['LM_MLT_N_BTH6']."</td><td>".$row1['LM_MLT_SQFT6']."</td><td>".$row1['LM_MLT_TRMUN6']."</td><td>".$row1['LM_MLT_GARYN6']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U7_RENT'])) {
   echo "     <tr><td><b>U7</b></td><td>".$row1['LM_MLT_U7_ID']."</td><td>$".number_format($row1['LM_MLT_U7_RENT'])."</td><td>".$row1['LM_MLT_N_BED7']."</td><td>".$row1['LM_MLT_N_BTH7']."</td><td>".$row1['LM_MLT_SQFT7']."</td><td>".$row1['LM_MLT_TRMUN7']."</td><td>".$row1['LM_MLT_GARYN7']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U8_RENT'])) {
   echo "     <tr><td><b>U8</b></td><td>".$row1['LM_MLT_U8_ID']."</td><td>$".number_format($row1['LM_MLT_U8_RENT'])."</td><td>".$row1['LM_MLT_N_BED8']."</td><td>".$row1['LM_MLT_N_BTH8']."</td><td>".$row1['LM_MLT_SQFT8']."</td><td>".$row1['LM_MLT_TRMUN8']."</td><td>".$row1['LM_MLT_GARYN8']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U9_RENT'])) {
   echo "     <tr><td><b>U9</b></td><td>".$row1['LM_MLT_U9_ID']."</td><td>$".number_format($row1['LM_MLT_U9_RENT'])."</td><td>".$row1['LM_MLT_N_BED9']."</td><td>".$row1['LM_MLT_N_BTH9']."</td><td>".$row1['LM_MLT_SQFT9']."</td><td>".$row1['LM_MLT_TRMUN9']."</td><td>".$row1['LM_MLT_GARYN9']."</td></tr>\n";
}
if (!empty($row1['LM_MLT_U10_RENT'])) {
   echo "     <tr><td><b>U10</b></td><td>".$row1['LM_MLT_U10_ID']."</td><td>$".number_format($row1['LM_MLT_U10_RENT'])."</td><td>".$row1['LM_MLT_N_BEDX']."</td><td>".$row1['LM_MLT_N_BTHX']."</td><td>".$row1['LM_MLT_SQFTX']."</td><td>".$row1['LM_MLT_TRMUNX']."</td><td>".$row1['LM_MLT_GARYNX']."</td></tr>\n";
}
echo "   </table>\n";
echo "  </td>\n";
echo " </tr>\n";
echo " <tr>\n";
echo "  <td align='center' style='background-color: #999;font-size: 14px;font-weight: bold;'>Additional Features</td>\n";
echo " </tr>\n";
echo " <tr>\n";
echo "  <td align='center'>\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
echo "     <tr><td width='15%'><b>Waterfront:</b> </td><td width='85%'>".$row1['LM_MST_CFF_WATERFRONT']."</td></tr>\n";
echo "     <tr><td><b>Int. Features:</b> </td><td>".$row1['LM_MST_CFF_INT_FEATURES']."</td></tr>\n";
echo "     <tr><td><b>Ext. Features:</b> </td><td>".$row1['LM_MST_CFF_EXT_FEATURES']."</td></tr>\n";
echo "     <tr><td><b>Appliances:</b> </td><td>".$row1['LM_MST_CFF_EQUIP']."</td></tr>\n";
echo "     <tr><td><b>Special Info:</b> </td><td>".$row1['LM_MST_CFF_SPECIAL_INFO']."</td></tr>\n";
echo "     <tr><td><b>Garage:</b> </td><td>".$row1['LM_MST_CFF_GARAGE']."</td></tr>\n";
echo "   </table><br />\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
echo "     <tr><td><b>Style:</b> </td><td>".$row1['LM_MST_CFF_STYLE']."</td>\n";
echo "     <td><b>Roof:</b> </td><td>".$row1['LM_MST_CFF_ROOF']."</td></tr>\n";
echo "     <tr><td><b>Heat/Cool:</b> </td><td>".$row1['LM_MST_CFF_HEAT_COOL']."</td>\n";
echo "     <td><b>Floor:</b> </td><td>".$row1['LM_MST_CFF_FLOOR']."</td></tr>\n";
echo "     <tr><td><b>Road Type:</b> </td><td>".$row1['LM_MST_CFF_ROAD_TYPE']."</td>\n";
echo "     <td><b>Sewer/Water:</b> </td><td>".$row1['LM_MST_CFF_SEWER_WATER']."</td></tr>\n";
echo "     <tr><td><b>Pool Type:</b> </td><td>".$row1['LM_MST_CFF_POOL_TYPE']."</td>\n";
echo "     <td><b>Energy Feat:</b> </td><td>".$row1['LM_MST_CFF_ENERGY_FEATURES']."</td></tr>\n";
echo "     <tr><td><b>Const/Found:</b> </td><td>".$row1['LM_MST_CFF_FOUNDATION']."</td>\n";
echo "     <td><b>Lot Desc:</b> </td><td>".$row1['LM_MST_CFF_LOT_DESCRIPTION']."</td></tr>\n";
echo "     <tr><td><b>Restrictions:</b> </td><td>".$row1['LM_MST_CFF_RESTRICTIONS']."</td>\n";
echo "     <td><b>Terms Avail:</b> </td><td>".$row1['LM_MST_CFF_TERMS_AVAILABLE']."</td></tr>\n";
echo "     <tr><td><b>Comm Amen:</b> </td><td>".$row1['LM_MST_CFF_COMMUNITY_AMENITIES']."</td>\n";
echo "     <td><b>Possession:</b> </td><td>".$row1['LM_MST_CFF_POSSESSION']."</td></tr>\n";
echo "     <tr><td><b>Short Sale:</b> </td><td>".$row1['LM_MST_SHTSL_YN']."</td>\n";
echo "     <td><b>Foreclosure:</b> </td><td>".$row1['LM_MST_FORCL_YN']."</td></tr>\n";
echo "   </table><br />\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
echo "     <tr><td><b>Taxes:</b> </td><td>$".number_format($row1['LM_MST_TAX_AMT'])."</td>\n";
echo "     <td><b>Tax Year:</b> </td><td>".$row1['LM_MST_TAX_YR']."</td>\n";
echo "     <td><b>Homestead:</b> </td><td>".$row1['LM_MST_HMSTD_YN']."</td>\n";
echo "     <td><b>Rear Exposure:</b> </td><td>".$row1['LM_MST_DRFCES']."</td></tr>\n";
echo "     <tr><td><b>HOA/COA:</b> </td><td>".$row1['LM_MST_HOA']."</td>\n";
echo "     <td><b>HOA Fee:</b> </td><td>$".number_format($row1['LM_MST_HOAFEE'])."</td>\n";
echo "     <td><b>Agent Owned:</b> </td><td>".$row1['LM_MST_AGTOWN']."</td>\n";
echo "     <td><b>Land Use:</b> </td><td>".$row1['LM_MST_ZONING']."</td></tr>\n";
echo "     <tr><td><b>Elementary:</b> </td><td>".$row1['LM_MST_SCH_ELEM']."</td>\n";
echo "     <td><b>Middle:</b> </td><td>".$row1['LM_MST_SCH_MID']."</td>\n";
echo "     <td><b>High:</b> </td><td>".$row1['LM_MST_SCH_HIGH']."</td>\n";
echo "     <td colspan='2'>&nbsp;</td></tr>\n";
echo "   </table><br />\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
echo "     <tr><td><b>Legal:</b> </td><td>".$row1['LM_MST_LEGAL']."</td></tr>\n";
echo "   </table><br />\n";

echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
if (!empty($row_lister['MM_OFF_NAME'])) {
   echo "    <tr><td colspan='3'><b>Courtesy of:</b> ".$row_lister['MM_OFF_NAME']."</td></tr>\n";
}
echo "    <tr><td colspan='3'><b>For more information or to visit this property, Contact:</b></td></tr>\n";
echo "    <tr><td>\n";
if (!empty($agent_name)) {
   if (file_exists("/home/mychurchserver/domains/citrusmls.mychurchserver.com/public_html/agents/agent".$display_agent.".jpg")) {
      echo "<img src='".$http_mugs."agent".$display_agent.".jpg' border='0' alt='$agent_name' />\n";
   } else {
      if (file_exists("/home/mychurchserver/domains/citrusmls.mychurchserver.com/public_html/agents/firm".$display_firm.".jpg")) {
         echo "<img src='".$http_mugs."firm".$display_firm.".jpg' border='0' alt='".$row2_assoc['firm.name']."' />\n";
      }
   }
} else {
   if (file_exists("/home/mychurchserver/domains/citrusmls.mychurchserver.com/public_html/agents/firm".$display_firm.".jpg")) {
      echo "<img src='".$http_mugs."firm".$display_firm.".jpg' border='0' alt='".$row2_assoc['firm.name']."' />\n";
   }
}
echo "     </td>\n";
echo "     <td valign='top'><b>";
if (!empty($agent_name)) { echo $agent_name."<br />\n"; }
echo $row2_assoc['firm.name']."</b><br />\n".$row2_assoc['firm.address1']."<br />\n";
if (!empty($row2_assoc['firm.address2'])) { echo $row2_assoc['firm.address2']."<br />\n"; }
echo $row2_assoc['firm.city']." ".$row2_assoc['firm.state'].", ".$row2_assoc['firm.zip']."</td>\n";
echo "     <td valign='top'>";
if (!empty($row2_assoc['firm.phone1'])) {echo "<b>Off. Ph#:</b> (".substr($row2_assoc['firm.phone1'],0,3).")".substr($row2_assoc['firm.phone1'],3,3)."-".substr($row2_assoc['firm.phone1'],6,4)."<br />\n"; }
if (!empty($row2_assoc['firm.phone2'])) { echo "<b>Off. Ph#:</b> (".substr($row2_assoc['firm.phone2'],0,3).")".substr($row2_assoc['firm.phone2'],3,3)."-".substr($row2_assoc['firm.phone2'],6,4)."<br />\n"; }
if (!empty($agent_phone1)) { echo "<b>Agt. Ph#:</b> $agent_phone1<br />\n"; }
if (!empty($agent_phone2)) { echo "<b>Agt. Ph#:</b> $agent_phone2<br />\n"; }
echo "<b><a href=\"javascript:NewWindow('contact_agent.php?mls_id=$mls_id&to_email=$to_email&to_name=$to_name&firm_name=".$row2_assoc['firm.name']."&website=$website','contact','550','450','center','front');\">Contact Now</a></b>\n";
echo "     </td></tr>\n";
echo "   </table>\n";
echo "  </td>\n";
echo " </tr>\n";
echo "</table><br />\n";

/* Disclaimer */
echo "<p style='font-size: 11px;'>The information contained herein has been provided by REALTORS &reg; Association of Citrus County, Inc. This information is from sources deemed reliable but not guaranteed by REALTORS &reg; Association of Citrus County, Inc. The information is for consumers' personal, non-commerical use and may not be used for any purpose other than identifying properties which consumers may be interested in purchasing. The information contained in this web site is believed to be reliable and while every effort is made to assure that the information is as accurate as possible, the owner of this site (whose name appears above) and Nature Coast Web Design & Marketing, Inc. disclaim any implied warranty or representation about it's accuracy, completeness or appropriateness for any particular purpose. This includes but is not limited to information provided by any third party which is accessed through this site via a hyperlink.<br />Those persons who access this information assume full responsibility for the use of said information and understand and agree that the owner of this site named above, or Nature Coast Web Design & Marketing, Inc., are not responsible or liable for any claim, loss or damage arising from the use of any information contained in this site.<br />Any reference to specific products, companies or services does not necessarily constitute or imply recommendation or endorsement by the owner of this site or Nature Coast Web Design & Marketing, Inc.</p>";

/* Count the hit */
$sql_hits_check = "SELECT mls_no, hits FROM tbl_hits WHERE mls_no = '".$row1['LM_MST_MLS_NO']."' AND month = '".date("m")."'";
$result_hits_check = mysql_query($sql_hits_check);
$num_rows_check = mysql_num_rows($result_hits_check);
if ($num_rows_check > '0') {
   //Update the current record
   while ($row_hits_check = mysql_fetch_array($result_hits_check)) {
         //Update the current record
         $hits = ($row_hits_check['hits'] + 1);
         $sql_hits = "UPDATE tbl_hits SET hits='$hits' WHERE mls_no='".$row1['LM_MST_MLS_NO']."'";
         if (@mysql_query($sql_hits)) {
            //echo $hits;
         }
   }
} else {
   //Create the Hits/Emails record
   $sql_hits = "INSERT INTO tbl_hits SET agent_id='".$row1['LM_MST_LIST_AGT']."', firm_id='".$row1['LM_MST_LIST_FRM']."', mls_no='".$row1['LM_MST_MLS_NO']."', month='".date("m")."', hits='1'";
   if (@mysql_query($sql_hits)) {
      //echo $hits;
   } else {
      echo("<p>Error inserting record: " . mysql_error() . "</p>");
   }
}

/*  Send lead info to firm/agent if requested */
if (($require_login == 'Y') && (!empty($user_email))) {
   /* Get the user's info again */
   $sql_user = "SELECT * FROM tbl_leads WHERE email = '$user_email'";
   $result_user = mysql_query($sql_user);
   $row_user = mysql_fetch_array($result_user);

   /* Set some variables */
   $to = "$to_name <$to_email>";
   //$to = "Webadmin <chris@naturecoastdesign.net>"; for testing only
   $subject = "A prospective lead just viewed MLS# $mls_id online.";
   //$from = $row_user['fname']." ".$row_user['lname']." <$user_email>";
   $headers = "From: $user_email\r\n";
   $headers .= "Reply-To: $from\r\n";
   $headers .= "Return-Path: $from\r\n";
   $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">\r\n";
   $headers .= "X-Mailer: PHP v".phpversion()."\r\n";
   $headers .= "MIME-Version: 1.0\r\n";
   $headers .= "Content-type: text/html; charset=\"us-ascii\"";

   $text = "Date: ".date('m-d-Y')." MLS# $mls_id<br />";
   $text .= "Here is the information that was provided by the user:<br />";
   $text .= "Email: ".$row_user['email']." <br />";
   if (!empty($row_user['fname']) || !empty($row_user['lname'])) {
      $text .= "Name: ".$row_user['fname']." ".$row_user['lname']." <br />";
   }
   if (!empty($row_user['address1'])) {
      $text .= "Street Address: ".$row_user['address1']." <br />";
   }
   if (!empty($row_user['address2'])) {
      $text .= "Street Address (con't): ".$row_user['address2']." <br />";
   }
   if (!empty($row_user['city'])) {
      $text .= "City: ".$row_user['city']." <br />";
   }
   if (!empty($row_user['state'])) {
      $text .= "State: ".$row_user['state']." <br />";
   }
   if (!empty($row_user['zip'])) {
      $text .= "Zip: ".$row_user['zip']." <br />";
   }
   if (!empty($row_user['day_phone'])) {
      $text .= "Daytime Phone: ".$row_user['day_phone']." <br />";
   }
   if (!empty($row_user['evening_phone'])) {
      $text .= "Evening Phone: ".$row_user['evening_phone']." <br />";
   }
   if (!empty($row_user['buyer_timeframe'])) {
      $text .= "Buyer's Time frame: ".$row_user['buyer_timeframe']." <br />";
   }
   if (!empty($row_user['buyer_pricerange'])) {
      $text .= "Buyer's Price Range: ".$row_user['buyer_pricerange']." <br />";
   }
   $text .= "------------------------------------------------<br />";

   if (mail("$to","$subject","$text","$headers")) {
      echo "Thank you for your inquiry. An agent should be contacting you very soon.<br />\n";
   } else {
      echo "There has been an error and we are unable to send your message at this time. Please try again later.<br />\n";
   }
}

/* Close out the result sets */
if (!empty($result1)) {
   mysql_free_result($result1);
}
if (!empty($result2)) {
   mysql_free_result($result2);
}
if (!empty($result_lister)) {
   mysql_free_result($result_lister);
}
if (!empty($result_user)) {
   mysql_free_result($result_user);
}
if (!empty($result_hits_check)) {
   mysql_free_result($result_hits_check);
}
/* Closing connection */
mysql_close($dbcnx);
?>
