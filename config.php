<?php
// Variables that can be used throughout the search, listing and details pages
$PHP_SELF = $_SERVER['PHP_SELF'];
$idx_dir = "/home/naturmls/public_html/marion/";
$http_home = "http://marion.naturecoastmls.com/";
$agents_dir = "agents/";
$images_dir = "images/";
$photos_dir = "photos/";
$home_dir = $idx_dir; //Home directory
$data_dir = $home_dir."data/"; //Data files directory
$http_mugs = $http_home.$agents_dir;
$http_imgs = $http_home.$images_dir;
$http_photos = $http_home.$photos_dir;
$abs_agents = $idx_dir.$agents_dir;
$abs_imgs = $idx_dir.$images_dir;
$abs_photos = $idx_dir.$photos_dir;
$db_tbl_agents = "tbl_idx_agent";
$db_tbl_firms = "tbl_idx_office";
$db_tbl_residential = "tbl_idx_residential";
$db_tbl_vacantland = "tbl_idx_vacant_land";
$db_tbl_multires = "tbl_idx_multi_res";
$db_tbl_commercial = "tbl_idx_commercial";
$db_tbl_businessop = "tbl_idx_business_op";
$db_tbl_rental = "tbl_idx_rental";

$disclaimer = "<p style='font-size: 11px;'>The information contained herein has been provided by REALTORS &reg; Association of Citrus County, Inc. This information is from sources deemed reliable but not guaranteed by REALTORS &reg; Association of Citrus County, Inc. The information is for consumers' personal, non-commerical use and may not be used for any purpose other than identifying properties which consumers may be interested in purchasing. The information contained in this web site is believed to be reliable and while every effort is made to assure that the information is as accurate as possible, the owner of this site (whose name appears above) and Nature Coast Web Design & Marketing, Inc. disclaim any implied warranty or representation about it's accuracy, completeness or appropriateness for any particular purpose. This includes but is not limited to information provided by any third party which is accessed through this site via a hyperlink.<br />Those persons who access this information assume full responsibility for the use of said information and understand and agree that the owner of this site named above, or Nature Coast Web Design & Marketing, Inc., are not responsible or liable for any claim, loss or damage arising from the use of any information contained in this site.<br />Any reference to specific products, companies or services does not necessarily constitute or imply recommendation or endorsement by the owner of this site or Nature Coast Web Design & Marketing, Inc.</p>\n";

// Database connection variables
$db_host = 'localhost';
$db_username = 'naturmls_marion';
$db_password = 's3llmyLand';
$db_name = 'naturmls_marionmls';

// RETS config variables
$rets_login_url = "http://rets.offutt-innovia.com:8080/ocl/login";
$rets_username = "nature41";
$rets_password = "GlHHRFsoVX2fCBuve2K3";
$rets_user_agent = "PHRETS/1.0";
//$rets_user_agent_password = "C1ru4c0N";
$Rets_Version = 'RETS/1.5'; 

// Debugging
if (!isset($debugging) || empty($debugging)) {
   $debugging = true;
}

$webadmin_email = "webadmin@naturecoastmls.com";
$webadmin_mobile_text = "3526010603@vtext.com";
$webadmin_email2 = "john@naturecoastdesign.net";
$webadmin_mobile_text2 = "3524641279@vtext.com";