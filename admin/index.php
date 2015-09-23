<?php
$cfgProgDir = 'phpSecurePages/';
include($cfgProgDir . "secure.php");
$PHP_SELF = $_SERVER['PHP_SELF'];
$idx_dir = "/home/naturmls/public_html/";
$http_home = "http://naturecoastmls.com/";
$http_mugs = $http_home."agents/";
$http_imgs = $http_home."images/";
if(isset($_POST['search_db'])){$search_db = $_POST['search_db'];}
if(isset($_POST['search_by'])){$search_by = $_POST['search_by'];}
if(isset($_POST['keyword'])){$keyword = $_POST['keyword'];}
$exit = 0;
if(!isset($keyword) || empty($keyword))
{
	$err_msg = 'No search term provided, please try your search again with a search term.';
	$exit++;	
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Find an Agent</title>
</head>

<body>
<form action="<? echo $PHP_SELF ?>" method="post" target="_self">
<table align="center" width="35%" border="0" cellspacing="3" cellpadding="3">
  <tr align="center">
    <td>Search</td>
    <td>Citrus MLS <input name="search_db" type="radio" value="citrus_mls" <?php if (empty($search_db) || $search_db == 'citrus_mls') { echo " checked"; } ?>></td>
    <td><!--Marion MLS <input name="search_db" type="radio" value="marion_mls <?php if (empty($search_db) || $search_db == 'marion_mls') { echo " checked"; } ?>">--></td>
    <td>NCWD Db <input name="search_db" type="radio" value="ncwd_mls" <?php if (empty($search_db) || $search_db == 'ncwd_mls') { echo " checked"; } ?>></td>
  </tr>
  <tr align="center">
    <td>By</td>
    <td>Agent ID <input name="search_by" type="radio" value="AgentID" <?php if (empty($search_by) || $search_by == 'AgentID') { echo " checked"; } ?>></td>
    <td>Firm ID <input name="search_by" type="radio" value="FirmID" <?php if (empty($search_by) || $search_by == 'FirmID') { echo " checked"; } ?>></td>
    <td>Last Name <input name="search_by" type="radio" value="LastName" <?php if (empty($search_by) || $search_by == 'LastName') { echo " checked"; } ?>></td>
  </tr>
  <tr>
    <td align="center" colspan="3"><input name="keyword" type="text" size="40"></td>
    <td><input name="sumbit" type="submit" value="search"></td>
  </tr>
</table>
</form>
<hr size='1' noshade />
<?php
/* Display any messages needed */
if (!empty($err_msg)) {
   echo "<div align='center'>$err_msg</div>\n";
}
if ($exit > 0) {
   exit;
}
?>

<table align="center" width="85%" border="0" cellspacing="3" cellpadding="3">
  <tr>
    <td align="left" colspan="5"><p style="font-size:16px;"><b>Search Results from the <?php echo $search_db; ?> Db</b></p></td>
  </tr>
  <tr align="center">
    <td>Agent Name</td>
    <td>Agent ID</td>
    <td>Firm Name</td>
    <td>Firm ID</td>
    <td>Office ID</td>
  </tr>
  <tr>
    <td align="center" colspan="5"><hr size='1' noshade /></td>
  </tr>
  
  <?php
  /*switch to determin what to search and from what database*/
  switch ($search_db) {
  	case 'citrus_mls':
    	$sql = "SELECT AgentID,LastName,FirstName,FirmID,OfficeID FROM tbl_idx_agent";
		/*nested switch for where clause if $search_db = citrus_mls*/
		switch ($search_by) {
        case 'AgentID':
             $sql .= " WHERE TRIM(AgentID) = '$keyword' ORDER BY OfficeID,LastName";
        break;
        case 'FirmID':
             $sql .= " WHERE TRIM(FirmID) = '$keyword' ORDER BY OfficeID,LastName";
        break;
        case 'LastName':
             $sql .= " WHERE LastName Like '$keyword' ORDER BY OfficeID,LastName";
        break;
        }
    break;
    case 'ncwd_mls':
        $sql = "SELECT agent_id,lname,fname,firm_id,off_id FROM tbl_agents_info";
		/*nested switch for where clause if $search_db = ncwd_mls*/
		switch ($search_by) {
        case 'AgentID':
             $sql .= " WHERE TRIM(agent_id) = '$keyword' ORDER BY off_id,lname";
        break;
        case 'FirmID':
             $sql .= " WHERE TRIM(firm_id) = '$keyword' ORDER BY off_id,lname";
        break;
        case 'LastName':
             $sql .= " WHERE lname Like '$keyword' ORDER BY off_id,lname";
        break;
        }
    break;
  }
	//Include Db connection script
	include '../dbconn.php';
	// run the query
	try {
   		$results = $dbcnx->prepare($sql);
		$results->execute();
		$num_rows = count($results);
	} catch(PDOException $e) {
  		echo "Error running query: ".$e->getMessage()."<br />\n";
	}
  	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		if($search_db == 'citrus_mls'){
			$sql2 = "SELECT name FROM tbl_firms_info WHERE TRIM(firm_id) = '$row[FirmID]'";	
		//query to get firm name
		try {
			$results2 = $dbcnx->prepare($sql2);
			$results2->execute();
			$num_rows2 = count($results2);
			$row2 = $results2->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			echo "Error running query to get firm name: ".$e->getMessage()."<br />\n";
		}
  ?>
  <tr>
    <td align="center">
    <?php 
	echo "<a href='agent_details.php?AgentID=".trim($row['AgentID'])."&keyword=".$keyword."&search_db=".$search_db."&keyword_type=".$search_by."'>".$row['LastName'].", ".$row['FirstName']."</a>";?></td>
    <td align="center"><?php echo $row['AgentID'];?></td>
    <td align="center"><?php echo $row2['name'];?></td>
    <td align="center"><?php echo $row['FirmID'];?></td>
    <td align="center"><?php echo $row['OfficeID'];?></td>
  </tr>
  <?php }else{
	  $sql2 = "SELECT name FROM tbl_firms_info WHERE TRIM(firm_id) = '$row[firm_id]'";	
		//query to get firm name
		try {
			$results2 = $dbcnx->prepare($sql2);
			$results2->execute();
			$num_rows2 = count($results2);
			$row2 = $results2->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			echo "Error running query to get firm name: ".$e->getMessage()."<br />\n";
		}?>
  <tr>
    <td align="center">
    <?php 
	echo "<a href='agent_details.php?AgentID=".trim($row['agent_id'])."&keyword=".$keyword."&search_db=".$search_db."&keyword_type=".$search_by."'>".$row['lname'].", ".$row['fname']."</a>";?></td>
    <td align="center"><?php echo $row['agent_id'];?></td>
    <td align="center"><?php echo $row2['name'];?></td>
    <td align="center"><?php echo $row['firm_id'];?></td>
    <td align="center"><?php echo $row['off_id'];?></td>
  </tr>
<?php }/*close if-else*/}/*close while statement*/?>
  <tr>
    <td align="center" colspan="5"><hr size='1' noshade /></td>
  </tr>
</table>
<?php $dbcnx = null; ?>
</body>
</html>