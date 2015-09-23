<?php
$cfgProgDir = 'phpSecurePages/';
include($cfgProgDir . "secure.php");
/* Set Some variables */
$PHP_SELF = $_SERVER['PHP_SELF'];
$idx_dir = "/home/naturmls/public_html/";
$http_home = "http://naturecoastmls.com/";
$mugs = "../agents/";
$http_mugs = $http_home."agents/";
$http_imgs = $http_home."images/";
$exit = 0;

/* Check for variables */
if (!empty($_POST['AgentID'])) {
   $agent_id = $_POST['AgentID'];
} else {
   $agent_id = $_GET['AgentID'];
}
if (!empty($_POST['search_db'])) {
   $dbtosearch = $_POST['search_db'];
} else {
   $dbtosearch = $_GET['search_db'];
}
if (!empty($_POST['keyword_type'])) {
   $keyword_type = $_POST['keyword_type'];
} else {
   $keyword_type = $_GET['keyword_type'];
}
if (!empty($_POST['keyword'])) {
   $keyword = $_POST['keyword'];
} else {
   $keyword = $_GET['keyword'];
}
if (empty($search_db)) {
   $err_msg = "Missing variable, please go back and select an agent again.";
   $exit++;
}
if (!empty($_POST['submit_update'])) {
      $sql_update = "UPDATE tbl_agents_info SET 
  		              agent_id='".addslashes($_POST['agent_id'])."',
  		              firm_id='".addslashes($_POST['firm_id'])."',
  		              off_id='".addslashes($_POST['off_id'])."',
  		              title='".addslashes($_POST['title'])."',
  		              phone1='".addslashes($_POST['phone1'])."',
  		              phone2='".addslashes($_POST['phone2'])."',
  		              fax='".addslashes($_POST['fax'])."',
  		              email_leads='".addslashes($_POST['email_leads'])."',
  		              descr='".addslashes($_POST['descr'])."',
  		              website1='".addslashes($_POST['website1'])."',
  		              website2='".addslashes($_POST['website2'])."',
  		              service_level='".addslashes($_POST['service_level'])."',
  		              sort_num='".addslashes($_POST['sort_num'])."',
  		              fname='".addslashes($_POST['fname'])."',
  		              lname='".addslashes($_POST['lname'])."',
  		              credentials='".addslashes($_POST['credentials'])."',
		                email_reports='".addslashes($_POST['email_reports'])."'";
	$sql_update .= " WHERE agent_id='".$_POST['agent_id']."'";
	//Include Db connection script
	include '../dbconn.php';
	// run the query
	try {
   		$results = $dbcnx->prepare($sql_update);
		$results->execute();
		echo "<p style='color: #00f;'>Agent updated successfully.</p>\n";
		$dbtosearch = $_POST['dbtosearch'];
		$agent_id = $_POST['agent_id'];
		
	} catch(PDOException $e) {
  		echo "Error running query: ".$e->getMessage()."<br />\n";
	}
   }
   
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Agent Details</title>
</head>

<body>
<p style='color: #00f;'><a href="index.php">Back to Main Menu</a></p>
<?

if($dbtosearch == "citrus_mls"){ 
	$sql = "SELECT * FROM tbl_idx_agent WHERE TRIM(AgentID) = '$agent_id'";
	//Include Db connection script
	include '../dbconn.php';
	// run the query
	try {
   		$results = $dbcnx->prepare($sql);
		$results->execute();
		$row = $results->fetch(PDO::FETCH_ASSOC);
	} catch(PDOException $e) {
  		echo "Error running query: ".$e->getMessage()."<br />\n";
	}
?>
<table align="left" width="40%" border="0" cellspacing="3" cellpadding="3" style='border: 1px solid #000;'>
<tr>
    <td align="center" colspan="2">Agent Details from the citrus_mls Db</td>
  </tr>
  <tr>
    <td width="36%">AgentCity</td>
    <td width="64%"><? echo $row['AgentCity'] ?></td>
  </tr>
  <tr>
    <td>AgentID</td>
    <td><? echo $row['AgentID'] ?></td>
  </tr>
  <tr>
    <td>AgentState</td>
    <td><? echo $row['AgentState'] ?></td>
  </tr>
  <tr>
    <td>AgentType</td>
    <td><? echo $row['AgentType'] ?></td>
  </tr>
  <tr>
    <td>AgentZip</td>
    <td><? echo $row['AgentZip'] ?></td>
  </tr>
  <tr>
    <td>CellPhone</td>
    <td><? echo $row['CellPhone'] ?></td>
  </tr>
  <tr>
    <td>Email</td>
    <td><? echo $row['Email'] ?></td>
  </tr>
  <tr>
    <td>Fax</td>
    <td><? echo $row['Fax'] ?></td>
  </tr>
  <tr>
    <td>FirmID</td>
    <td><? echo $row['FirmID'] ?></td>
  </tr>
  <tr>
    <td>FirstName</td>
    <td><? echo $row['FirstName'] ?></td>
  </tr>
  <tr>
    <td>LastName</td>
    <td><? echo $row['LastName'] ?></td>
  </tr>
  <tr>
    <td>OfficeID</td>
    <td><? echo $row['OfficeID'] ?></td>
  </tr>
  <tr>
    <td>OfficePhone</td>
    <td><? echo $row['OfficePhone'] ?></td>
  </tr>
  <tr>
    <td>URL</td>
    <td><? echo $row['URL'] ?></td>
  </tr>
</table>
<? }else{ 
$sql = "SELECT * FROM tbl_agents_info WHERE TRIM(agent_id) = '$agent_id'";
	//Include Db connection script
	include '../dbconn.php';
	// run the query
	try {
   		$results = $dbcnx->prepare($sql);
		$results->execute();
		$row = $results->fetch(PDO::FETCH_ASSOC);
	} catch(PDOException $e) {
  		echo "Error running query: ".$e->getMessage()."<br />\n";
	}?>
<table align="right" width="40%" border="0" cellspacing="3" cellpadding="3" style='border: 1px solid #000;'>
  <tr>
    <td align="center" colspan="2">Agent Details from the NCWD Db</td>
  </tr>
  <tr>
    <td width="35%">agent_id</td>
    <td width="65%"><? echo $row['agent_id'] ?></td>
  </tr>
  <tr>
    <td>firm_id</td>
    <td><? echo $row['firm_id'] ?></td>
  </tr>
  <tr>
    <td>off_id</td>
    <td><? echo $row['off_id'] ?></td>
  </tr>
  <tr>
    <td>title</td>
    <td><? echo $row['title'] ?></td>
  </tr>
  <tr>
    <td>fname</td>
    <td><? echo $row['fname'] ?></td>
  </tr>
  <tr>
    <td>lname</td>
    <td><? echo $row['lname'] ?></td>
  </tr>
  <tr>
    <td>phone1</td>
    <td><? echo $row['phone1'] ?></td>
  </tr>
  <tr>
    <td>phone2</td>
    <td><? echo $row['phone2'] ?></td>
  </tr>
  <tr>
    <td>fax</td>
    <td><? echo $row['fax'] ?></td>
  </tr>
  <tr>
    <td>email_leads</td>
    <td><? echo $row['email_leads'] ?></td>
  </tr>
  <tr>
    <td>email_reports</td>
    <td><? echo $row['email_reports'] ?></td>
  </tr>
  <tr>
    <td>descr</td>
    <td><? echo $row['descr'] ?></td>
  </tr>
  <tr>
    <td>website1</td>
    <td><? echo $row['website1'] ?></td>
  </tr>
  <tr>
    <td>website2</td>
    <td><? echo $row['website2'] ?></td>
  </tr>
  <tr>
    <td>service_level</td>
    <td><? echo $row['service_level'] ?></td>
  </tr>
  <tr>
    <td>sort_num</td>
    <td><? echo $row['sort_num'] ?></td>
  </tr>
  <tr>
    <td>credentials</td>
    <td><? echo $row['credentials'] ?></td>
  </tr>
  <tr>
    <td align="center" colspan="2"><?php if (file_exists($mugs.'agent'.$row['agent_id'].'.jpg')) {
            echo "<img src='".$mugs."agent".$row['agent_id'].".jpg' alt='agent photo on file' border='0' />";
         } ?></td>
  </tr>
</table>
<? } 
$sql = "SELECT * FROM tbl_agents_info WHERE TRIM(agent_id) = '$agent_id'";
	//Include Db connection script
	include '../dbconn.php';
	// run the query
	try {
   		$results = $dbcnx->prepare($sql);
		$results->execute();
		$row = $results->fetch(PDO::FETCH_ASSOC);
	} catch(PDOException $e) {
  		echo "Error running query: ".$e->getMessage()."<br />\n";
	}
?>
<!-- display table for search results of NCWD Db-->
<form action="<? echo $PHP_SELF ?>" method="post">
<table align="right" width="40%" border="0" cellspacing="3" cellpadding="3" style='border: 1px solid #000;'>
  <tr>
    <td align="center" colspan="2">Agent Details from the NCWD Db</td>
  </tr>
   <tr>
    <td width="35%">agent_id</td>
    <td width="65%"><input type='text' name='agent_id' size='20' value='<? echo $row['agent_id'] ?>' /></td>
  </tr>
  <tr>
    <td>firm_id</td>
    <td><input type='text' name='firm_id' size='20' value='<? echo $row['firm_id'] ?>' /></td>
  </tr>
  <tr>
    <td>off_id</td>
    <td><input type='text' name='off_id' size='20' value='<? echo $row['off_id'] ?>' /></td>
  </tr>
  <tr>
    <td>title</td>
    <td><input type='text' name='title' size='20' value='<? echo $row['title'] ?>' /></td>
  </tr>
  <tr>
    <td>fname</td>
    <td><input type='text' name='fname' size='20' value='<? echo $row['fname'] ?>' /></td>
  </tr>
  <tr>
    <td>lname</td>
    <td><input type='text' name='lname' size='20' value='<? echo $row['lname'] ?>' /></td>
  </tr>
  <tr>
    <td>phone1</td>
    <td><input type='text' name='phone1' size='20' value='<? echo $row['phone1'] ?>' /></td>
  </tr>
  <tr>
    <td>phone2</td>
    <td><input type='text' name='phone2' size='20' value='<? echo $row['phone2'] ?>' /></td>
  </tr>
  <tr>
    <td>fax</td>
    <td><input type='text' name='fax' size='20' value='<? echo $row['fax'] ?>' /></td>
  </tr>
  <tr>
    <td>email_leads</td>
    <td><input type='text' name='email_leads' size='20' value='<? echo $row['email_leads'] ?>' /></td>
  </tr>
  <tr>
    <td>email_reports</td>
    <td><input type='text' name='email_reports' size='20' value='<? echo $row['email_reports'] ?>' /></td>
  </tr>
  <tr>
    <td>descr</td>
    <td><input type='text' name='descr' size='20' value='<? echo $row['descr'] ?>' /></td>
  </tr>
  <tr>
    <td>website1</td>
    <td><input type='text' name='website1' size='20' value='<? echo $row['website1'] ?>' /></td>
  </tr>
  <tr>
    <td>website2</td>
    <td><input type='text' name='website2' size='20' value='<? echo $row['website2'] ?>' /></td>
  </tr>
  <tr>
    <td>service_level</td>
    <td><input type='text' name='service_level' size='20' value='<? echo $row['service_level'] ?>' /></td>
  </tr>
  <tr>
    <td>sort_num</td>
    <td><input type='text' name='sort_num' size='20' value='<? echo $row['sort_num'] ?>' /></td>
  </tr>
  <tr>
    <td>credentials</td>
    <td><input type='text' name='credentials' size='20' value='<? echo $row['credentials'] ?>' /></td>
  </tr>
  <tr>
    <td align="center" colspan="2"><?php if (file_exists($mugs.'agent'.$row['agent_id'].'.jpg')) {
            echo "<img src='".$mugs."agent".$row['agent_id'].".jpg' alt='agent photo on file' border='0' />";
         } ?></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input name="submit_update" type="submit" value="submit">
    <input name="dbtosearch" type="hidden" value="<? echo $dbtosearch; ?>"</td>
  </tr>
</table>
</form>

</body>
</html>