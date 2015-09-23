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
$http_agents = $http_home."agents/";
$http_imgs = $http_home."images/";
/* for testing only
$firm_id = ""; //for displaying only listings for this firm
$office_id = ""; //for displaying only listings for a specific office of this firm
$sort_order = ""; //for specifying a sort order (duh)
$agent_id = ""; //for displaying details for an agent
 */
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
   $where_clause .= " firm_id = '".$firm_id."'";
   $where_clause_num++;
   if (isset($office_id)) {
      $where_clause .= " AND off_id = '".$office_id."'";
      $where_clause_num++;
   }
}
if (!empty($agent_id)) {
   $where_clause .= " AND agent_id = '".$agent_id."'";
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
           $order_clause = " ORDER BY lname";
        break;
        case 'fname_az':
           // first name a-z
           $order_clause = " ORDER BY fname";
        break;
        default:
           // last name a-z
           $order_clause = " ORDER BY lname";
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
$sql1 = "SELECT * FROM tbl_agents_info".$where_clause.$order_clause;
$result1 = $dbcnx->prepare($sql1);
$result1->execute();
try  {
	$result1->execute();
$num_rows1 = $result1->rowCount();

/* Debugging info
echo "sql1: $sql1<br />\n";
echo "num_rows1: $num_rows1<br />\n"; */

echo "<table width='100%' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;color: #000;'>\n";
/* Display Agent details if agent_id is present */
if (isset($agent_id)) {
while ($row1 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<tr>\n";
      echo " <td align='center' valign='top'>\n";
      if (file_exists($idx_dir."agents/agent".$row1['agent_id'].".jpg")) {
         echo "<img src='".$http_agents."agent".$row1['agent_id'].".jpg' border='0' alt='".$row1['fname']." ".$row1['lname']."' />\n";
      } else {
         echo "&nbsp;";
      }
      echo " </td>\n<td>\n";      
      echo "<b>".$row1['fname']." ".$row1['lname'];
      if (!empty($row1['credentials'])) { echo ", ".$row1['credentials']; }
      if (!empty($row1['title'])) { echo "<br />".$row1['title']; }
      echo "</b><br />\n";
      if (!empty($row1['phone1'])) {
      	 $agent_phone1 = "(".substr($row1['phone1'],0,3).")".substr($row1['phone1'],3,3)."-".substr($row1['phone1'],6,4);
         echo "Phone: $agent_phone1<br />\n";
      }
      if (!empty($row1['phone2'])) {
      	 $agent_phone2 = "(".substr($row1['phone2'],0,3).")".substr($row1['phone2'],3,3)."-".substr($row1['phone2'],6,4);
         echo "Phone: $agent_phone2<br />\n";
      }
      if (!empty($row1['fax'])) {
      	 $agent_fax = "(".substr($row1['fax'],0,3).")".substr($row1['fax'],3,3)."-".substr($row1['fax'],6,4);
      	 echo "Fax: $agent_fax<br />\n";
      }
      if (!empty($row1['email_leads'])) {
      	 echo "<a href='mailto:".$row1['email_leads']."'>Contact Me</a><br />\n";
      }
      if (!empty($row1['website1'])) {
      	 echo "<a href='".$row1['website1']."'>Visit My Website</a>\n";
      }
      echo " </td>\n";
      echo "</tr>\n";
      echo "<tr>\n";
      echo " <td colspan='2' valign='top'>\n";
      if (!empty($row1['descr'])) {
         echo stripslashes($row1['descr']);
      }
      echo " </td>\n";
      echo "</tr>\n";
}
} else {
/* Otherwise display List of all agents in firm and office */
while ($row1 = $result1->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<tr>\n";
      echo " <td align='center'>\n";
      echo "<a href='".$_SERVER['PHP_SELF']."?agent_id=".$row1['agent_id']."&$str_url_vars'><b>".$row1['fname']." ".$row1['lname'];
      if (!empty($row1['credentials'])) { echo ", ".$row1['credentials']; }
      if (!empty($row1['title'])) { echo ", ".$row1['title']; }
      echo "</b></a>\n";
      echo " </td>\n";
      echo "</tr>\n";
}
}
echo "</table><br />\n";
} catch(PDOException $e) {
	echo "Db Error: ".$e->getMessage()."<br />\n";
   echo "sql1: $sql1<br />\n";
}

/* Close out the result set */
if (!empty($result1)) {$result1 = 'null'; }
/* Closing connection */
$dbcnx = NULL;
?>
