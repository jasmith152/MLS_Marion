<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
  <meta name="description" content="" />
  <title>Title</title>
</head>
<body topmargin="10" marginheight="10">
 <div align="center" style="width: 620px;">
<?php
 if (!empty($_GET['search_title'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "search_title=".$_GET['search_title'];
   $relink_url_var_num++;
   $title = $_GET['search_title'];
}
if (!empty($_GET['subdivision'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "subdivision=".$_GET['subdivision'];
   $relink_url_var_num++;
   $where_custom = " Subdivision Like '%".$_GET['subdivision']."%'";
}
if (!empty($_GET['city'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "city=".$_GET['city'];
   $relink_url_var_num++;
   $listingsin_city = $_GET['city'];
}
if (!empty($_GET['waterfront'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "waterfront=".$_GET['waterfront'];
   $relink_url_var_num++;
   $waterfront_yn = $_GET['waterfront'];
}
if (!empty($_GET['golf'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "golf=".$_GET['golf'];
   $relink_url_var_num++;
   $where_custom = " LotDescription Like '%On Golf Course%'";
}
if (!empty($_GET['farm'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "farm=".$_GET['farm'];
   $relink_url_var_num++;
   $where_custom = " Restrictions Like '%Horses Allowed%'";
}
if (!empty($_GET['condo'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "condo=".$_GET['condo'];
   $relink_url_var_num++;
   $where_custom = " PropertyType = 'Condo/Villa/Townhome'";
}
if (!empty($_GET['show'])) {
   if ($relink_url_var_num > 0) { $relink_url_vars .= "&"; }
   $relink_url_vars .= "show=".$_GET['show'];
   $relink_url_var_num++;
   $show = $_GET['show'];
}
if (empty($listingsin_subdivision) && empty($waterfront_yn) && empty($listingsin_city) && empty($where_custom)) {
   $firm_id = '960';
   $office_id = '0';
} else {
   $display_firm = '960';
   $display_office = '0';
}
//$limit = '10';
//if (empty($page)) { $page = 1; }
//echo $where_custom;

echo '<div align="center" style="font-size:36px;"><strong>'.$title.'</strong></div>';

//include '/home/mychurchserver/domains/citrusmls.mychurchserver.com/public_html/listings-test.php';
include 'listings.php';
?>
 </div>
</body>
</html>
