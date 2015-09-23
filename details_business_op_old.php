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
$sql1 = "SELECT * FROM tbl_idx_business_op WHERE LM_MST_MLS_NO = '".$mls_id."'";
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
echo "     <tr><td><b>Bldg Stories:</b> </td><td>".$row1['LM_CMI_STORIES']."</td>\n";
echo "     <td><b>Apx. Acres:</b> </td><td>".$row1['LM_MST_ACRES']."</td></tr>\n";
echo "     <tr><td><b>Park Spaces:</b> </td><td>".$row1['LM_CMI_N_PARK']."</td>\n";
echo "     <td><b>Apx. Net Leaseable SqFt:</b> </td><td>".$row1['LM_CMI_NET_RENT']."</td></tr>\n";
echo "     <tr><td><b>Waterfront:</b> </td><td>".$row1['LM_MST_WFRT_YN']."</td>\n";
echo "     <td><b>Apx. Bldg SqFt:</b> </td><td>".$row1['LM_MST_SQFT_N']."</tr>\n";
echo "     <tr><td><b>Waterfront Ft:</b> </td><td>".$row1['LM_MST_WTRFRT_N']."</td>\n";
echo "     <td><b>Apx. Land SqFt:</b> </td><td>".$row1['LM_CMI_SF_LOT']."</tr>\n";
echo "     <tr><td><b>Load Docks:</b> </td><td>".$row1['LM_CMI_N_DOCKS']."</td>\n";
echo "     <td><b>Sec/Twp/Rng:</b> </td><td>".$row1['LM_MST_SEC']."/".$row1['LM_MST_TWP']."/".$row1['LM_MST_RANGE']."</td></tr>\n";
echo "     <tr><td><b>Rails:</b> </td><td>".$row1['LM_CMI_RAILS']."</td>\n";
echo "     <td><b>Alt Key:</b> </td><td>".$row1['LM_MST_PARC_NO']."</td></tr>\n";
echo "     <tr><td><b>Asset Sales:</b> </td><td>".$row1['LM_CMI_INC_OTH']."</td>\n";
echo "     <td><b>Land Use:</b> </td><td>".$row1['LM_MST_ZONING']."</td></tr>\n";
echo "     <tr><td><b>Apx. Lot Size:</b> </td><td>".$row1['LM_MST_DLOT']."</td>\n";
echo "     <td colspan='2'>&nbsp;</td></tr>\n";
echo "     <tr><td valign='top'><b>Public Rems:</b> </td><td colspan='3' valign='top'>".$row1['LM_CMI_REMARK']."</td></tr>\n";
echo "     <tr><td valign='top'><b>Directions:</b> </td><td colspan='3' valign='top'>".$row1['LM_MST_DIR']."</td></tr>\n";
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
echo "     <tr><td><b>Int Features:</b> </td><td>".$row1['LM_MST_CFF_INT_FEATURES']."</td></tr>\n";
echo "     <tr><td><b>Ext Features:</b> </td><td>".$row1['LM_MST_CFF_EXT_FEATURES']."</td></tr>\n";
echo "     <tr><td><b>Special Info:</b> </td><td>".$row1['LM_MST_CFF_SPECIAL_INFO']."</td></tr>\n";
echo "   </table><br />\n";
echo "   <table width='100%' border='0' cellpadding='1' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 12px;color: #000;'>\n";
echo "     <tr><td><b>Curr Bldg Use:</b> </td><td>".$row1['LM_MST_CFF_CURRENT_BLDG_USE']."</td>\n";
echo "     <td><b>Lot Desc:</b> </td><td>".$row1['LM_MST_CFF_LOT_DESCRIPTION']."</td></tr>\n";
echo "     <tr><td><b>Sewer/Water:</b> </td><td>".$row1['LM_MST_CFF_SEWER_WATER']."</td>\n";
echo "     <td><b>Floor:</b> </td><td>".$row1['LM_MST_CFF_FLOOR']."</td></tr>\n";
echo "     <tr><td><b>Const/Foundation:</b> </td><td>".$row1['LM_MST_CFF_FOUNDATION']."</td>\n";
echo "     <td><b>Heat/Cool:</b> </td><td>".$row1['LM_MST_CFF_HEAT_COOL']."</td></tr>\n";
echo "     <tr><td><b>Terms Avail:</b> </td><td>".$row1['LM_MST_CFF_TERMS_AVAILABLE']."</td>\n";
echo "     <td><b>Roof:</b> </td><td>".$row1['LM_MST_CFF_ROOF']."</td></tr>\n";
echo "     <tr><td><b>Energy Feat:</b> </td><td>".$row1['LM_MST_CFF_ENERGY_FEATURES']."</td>\n";
echo "     <td><b>Parking:</b> </td><td>".$row1['LM_MST_CFF_PARKING']."</td></tr>\n";
echo "     <tr><td><b>Possession:</b> </td><td>".$row1['LM_MST_CFF_POSSESSION']."