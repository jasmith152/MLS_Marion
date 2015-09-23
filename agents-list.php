<?php
// Establish GET & POST variables
import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];

/* Check for variables */
if (empty($firm_id)) {
   $err_msg .= "<span class='err_msg'>No Firm selected.</span>\n";
   $exit = 1;
} else {
   /* Verify firm is allowed to use our system */
   include 'allow_firms.php';
   if (!stristr($allow_firms,$firm_id)) {
      $err_msg .= "<span class='err_msg'>We are sorry, but this website is not authorized to use this feature.</span>\n";
      $exit = 1;
   }
}

//Include Db connection script
include 'dbconn.php';

/* Set some variables */
$idx_dir = "/home/naturmls/public_html/";
$http_home = "http://naturecoastmls.com/";
$agents_dir = "agents/";
$images_dir = "images/";
$http_agents = $http_home.$agents_dir;
$http_imgs = $http_home.$images_dir;
$abs_agents = $idx_dir.$agents_dir;
$abs_imgs = $idx_dir.$images_dir;
$db_tbl_agents = "tbl_idx_agent";
$db_tbl_firms = "tbl_idx_office";
$db_agent_id = "AgentID";
$db_firm_id = "FirmID";
$db_office_id = "OfficeID";
$db_agent_fname = "FirstName";
$db_agent_lname = "LastName";
$db_agent_phone = "CellPhone";
$db_agent_phone2 = "Pager";
$db_agent_fax = "Fax";
$db_agent_email = "Email";
$db_agent_website = "URL";
/* for testing only
$firm_id = ""; //for displaying only listings for this firm
$office_id = ""; //for displaying only listings for a specific office of this firm
$sort_order = ""; //for specifying a sort order (duh)
$agent_id = ""; //for displaying details for an agent
 */

// Function for finding photo of agent
function agent_photo_exists($agent_num,$uploaddir_abs="/home/naturmls/public_html/agents/") {
  if (file_exists($uploaddir_abs.'agent'.$agent_num.'.jpg')) {
    return 'agent'.$agent_num.'.jpg';
  } elseif (file_exists($uploaddir_abs.'agent'.$agent_num.'.JPG')) {
    return 'agent'.$agent_num.'.JPG';
  } elseif (file_exists($uploaddir_abs.'agent'.$agent_num.'.gif')) {
    return 'agent'.$agent_num.'.gif';
  } elseif (file_exists($uploaddir_abs.'agent'.$agent_num.'.GIF')) {
    return 'agent'.$agent_num.'.GIF';
  } elseif (file_exists($uploaddir_abs.'agent'.$agent_num.'.png')) {
    return 'agent'.$agent_num.'.png';
  } elseif (file_exists($uploaddir_abs.'agent'.$agent_num.'.PNG')) {
    return 'agent'.$agent_num.'.PNG';
  } else {
    return FALSE;
  }
}

/* Clear certain variables */
unset($str_url_vars);
$str_url_var_num = 0;
unset($where_clause);
$where_clause_num = 0;
unset($order_clause);
$order_clause_num = 0;
unset($order_clause_res);
$order_clause_res_num = 0;

if (isset($firm_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "firm_id=$firm_id";
   $str_url_var_num++;
}
if (isset($office_id)) {
   if ($str_url_var_num > 0) { $str_url_vars .= "&"; }
   $str_url_vars .= "office_id=$office_id";
   $str_url_var_num++;
}
/* Construct Where clause */
if (!empty($firm_id)) {
   $where_clause .= " $db_firm_id = '".$firm_id."'";
   $where_clause_num++;
   if (isset($office_id)) {
      $where_clause .= " AND $db_office_id = '".$office_id."'";
      $where_clause_num++;
   }
}
if (!empty($agent_id)) {
   $where_clause .= " AND $db_agent_id = '".$agent_id."'";
   $where_clause_num++;
}
if (!empty($where_clause)) {
   $where_clause = " WHERE".$where_clause;
}
/* Construct Order clause */
switch ($sort_order) {
	case 'none':
	   $order_clause = "";
        break;
        case 'lname_az':
           // last name a-z
           $order_clause = " ORDER BY $db_agent_lname";
        break;
        case 'fname_az':
           // first name a-z
           $order_clause = " ORDER BY $db_agent_fname";
        break;
        case 'sort_num':
           // use the sort number
           $order_clause = " ORDER BY sort_num";
        break;
        default:
           // last name a-z
           $order_clause = " ORDER BY $db_agent_lname";
        break;
}

/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<div align='center'>$err_msg</div>\n";
}
if ($exit > 0) {
   exit;
}
/* Query the Db */
$sql1 = "SELECT * FROM ".$db_tbl_agents.$where_clause.$order_clause;
if ($result1 = mysql_query($sql1)) {
$num_rows1 = mysql_num_rows($result1);

/* Debugging info
echo "sql1: $sql1<br />\n";
echo "num_rows1: $num_rows1<br />\n"; */

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
/* Display Agent details if agent_id is present */
if (isset($agent_id)) {
while ($row1 = mysql_fetch_array($result1)) {
      echo "<tr>\n";
      echo " <td align='center' valign='top'>\n";
      if (agent_photo_exists($row1[$db_agent_id])) {
         $photo_src = agent_photo_exists($row1[$db_agent_id]);
         $img_info = getimagesize($abs_agents.$photo_src);
         echo "<img src='".$http_agents.$photo_src."' ".$img_info[3]." alt='".$row1[$db_agent_fname]." ".$row1[$db_agent_lname]."' border='0' />\n";
      } else {
         echo "&nbsp;";
      }
      echo " </td>\n<td>\n";
      echo "<strong>".$row1[$db_agent_fname]." ".$row1[$db_agent_lname];
      echo "</strong><br />\n";
      if (!empty($row1[$db_agent_phone])) {
      	 $agent_phone1 = "(".substr($row1[$db_agent_phone],0,3).")".substr($row1[$db_agent_phone],3,3)."-".substr($row1[$db_agent_phone],6,4);
         echo "Phone: $agent_phone1<br />\n";
      }
      if (!empty($row1[$db_agent_phone2])) {
      	 $agent_phone2 = "(".substr($row1[$db_agent_phone2],0,3).")".substr($row1[$db_agent_phone2],3,3)."-".substr($row1[$db_agent_phone2],6,4);
         echo "Phone: $agent_phone2<br />\n";
      }
      if (!empty($row1[$db_agent_fax])) {
      	 $agent_fax = "(".substr($row1[$db_agent_fax],0,3).")".substr($row1[$db_agent_fax],3,3)."-".substr($row1[$db_agent_fax],6,4);
      	 echo "Fax: $agent_fax<br />\n";
      }
      if (!empty($row1[$db_agent_email])) {
      	 echo "<a href='mailto:".$row1[$db_agent_email]."'>Contact Me</a><br />\n";
      }
      if (!empty($row1[$db_agent_website])) {
         // Check for http prefix
         if (substr($row1[$db_agent_website],0,7) != "http://" || substr($row1[$db_agent_website],0,8) != "https://") {
            $row1[$db_agent_website] = "http://".$row1[$db_agent_website];
         }
      	 echo "<a href='".$row1[$db_agent_website]."' target='_blank'>Visit My Website</a>\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
      /*echo "<tr>\n";
      echo " <td colspan='2' valign='top'>\n";
      if (!empty($row1['descr'])) {
         echo stripslashes($row1['descr']);
      }
      echo " </td>\n";
      echo "</tr>\n";*/
}
} else {
/* Otherwise display List of all agents in firm and office */
while ($row1 = mysql_fetch_array($result1)) {
      echo "<tr>\n";
      echo " <td align='center'>\n";
      echo "<strong><a href='".$_SERVER['PHP_SELF']."?agent_id=".$row1[$db_agent_id]."&$str_url_vars'><b>".$row1[$db_agent_fname]." ".$row1[$db_agent_lname];
      echo "</strong></a>\n";
      echo " </td>\n";
      echo "</tr>\n";
}
}
echo "</table><br />\n";
} else {
   echo "Error performing query: " . mysql_error() . "<br />\n";
   echo "sql1: $sql1<br />\n";
}

/* Close out the result set */
if (!empty($result1)) { mysql_free_result($result1); }
/* Closing connection */
mysql_close($dbcnx);
?>
