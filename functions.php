<?php
// Functions that can be used throughout the search, listing and details pages

/* 
 * Checks input arrays for requested variable in order of GET, SESSION, POST
 *  returns value or false on failure
 */
function getVar($varname) {
   if (isset($_GET[$varname]) && !empty($_GET[$varname])) {
      $variable = $_GET[$varname];
   }
   if (isset($_SESSION[$varname]) && !empty($_SESSION[$varname])) {
      $variable = $_SESSION[$varname];
   }
   if (isset($_POST[$varname]) && !empty($_POST[$varname])) {
      $variable = $_POST[$varname];
   }
   if (isset($variable) && !empty($variable)) {
      return $variable;
   } else {
      return false;
   }
}

/* Function for sending out alerts */
function webadmin_alert($alerts_txt) {
   global $webadmin_email, $webadmin_mobile_text, $eol;
   
   if (!empty($webadmin_email)) {
   /* Send an email alert to the web administrator */
   $mail_eol = "\r\n";
   $to = $webadmin_email;
   $subject = "Marion IDX alert";
   $from = "postmaster@naturecoastmls.com";
   $headers = "From: $from".$mail_eol;
   $headers .= "Reply-To: $from".$mail_eol;
   $headers .= "Return-Path: $from".$mail_eol;
   $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$mail_eol;
   $headers .= "X-Mailer: PHP v".phpversion()."".$mail_eol;
   $headers .= "MIME-Version: 1.0".$mail_eol;
   $headers .= "Content-type: text/html; charset=\"us-ascii\"".$mail_eol;
   $headers .= "Content-Transfer-Encoding: 8bit;".$mail_eol;
   // Use log entry as message body
   $text = str_replace("\n","<br />",$alerts_txt);
   mail($to,$subject,$text,$headers);
   }
   if (!empty($webadmin_email2)) {
   /* Send an email alert to the web administrator */
   $mail_eol = "\r\n";
   $to = $webadmin_email2;
   $subject = "Marion IDX alert";
   $from = "postmaster@naturecoastmls.com";
   $headers = "From: $from".$mail_eol;
   $headers .= "Reply-To: $from".$mail_eol;
   $headers .= "Return-Path: $from".$mail_eol;
   $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$mail_eol;
   $headers .= "X-Mailer: PHP v".phpversion()."".$mail_eol;
   $headers .= "MIME-Version: 1.0".$mail_eol;
   $headers .= "Content-type: text/html; charset=\"us-ascii\"".$mail_eol;
   $headers .= "Content-Transfer-Encoding: 8bit;".$mail_eol;
   // Use log entry as message body
   $text = str_replace("\n","<br />",$alerts_txt);
   mail($to,$subject,$text,$headers);
   }

   if (!empty($webadmin_mobile_text2)) {
   /* Send a text message alert to the web administrator */
   $to = $webadmin_mobile_text2;
   $headers = "From: $from".$eol;
   $headers .= "MIME-Version: 1.0".$eol;
   mail($to,$subject,$alerts_txt,$headers);
   }
}

// Connect to the database server and Select the database
function dbconn($db_host,$db_username,$db_password,$db_name) {
   try {
      $dbcnx = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password);
      $dbcnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $dbcnx;
   } catch(PDOException $e) {
      $err_msg .= "Db Error: ".$e->getMessage()."<br />\n";
      return $err_msg;
   }
}

// Connects to the RETS system and returns rets object
function connectRETS() {
   global $rets_login_url,$rets_username,$rets_password;
   /* Get our phRets class */
   require_once "inc-class-phrets.php";
   $rets = new phRETS;

   // Connect to the RETS Server
   if ($debugging) { echo "+ Connecting to {$rets_login_url} as {$rets_username}<br />\n"; }
   $connect = $rets->Connect($rets_login_url, $rets_username, $rets_password);
   if ($connect) {
      if ($debugging) { echo "  + Connected<br />\n"; }
      return $rets;
   } else {
      if ($debugging) { echo "  + Not connected:<br />\n"; print_r($rets->Error()); }
      return null;
   }
}

// Check for exixting photos and then download from RETS if needed; no return value or data
function photoCheck($abs_photos,$listingID) {
   //for($i=3;$i>=0;$i--) {
   if (!photo_exists($abs_photos,$listingID,0)) {
      if (!photo_exists($abs_photos,$listingID,1)) {
         if (!photo_exists($abs_photos,$listingID,2)) {
            if (!photo_exists($abs_photos,$listingID,3)) {
         // Download the photo from RETS
         $rets = connectRETS();
         if ($rets == null) {
            $alerts_txt = "Could not connect to the RETS server.";
            webadmin_alert($alerts_txt);
         } else {
            $photos = $rets->GetObject("Property", "Photo", $listingID);
            foreach ($photos as $photo) {
               $number = $photo['Object-ID'];
               if ($photo['Success'] == true) {
                  file_put_contents($abs_photos.$listingID."_".$number.".jpg", $photo['Data']);
               }
            }
         }
         $rets->Disconnect();
         $rets = null;
         //return "photo doesn't exist ($abs_photos,$listingID)";
         return false;
            }
         }
      }
   } else {
      //return "photo exists";
      return true;
   }
}

// Function for finding photos for listings
function photo_exists($uploaddir_abs,$listing_num,$photo_num) {
  if (file_exists($uploaddir_abs.$listing_num."_".$photo_num.'.jpg')) {
    return $listing_num."_".$photo_num.'.jpg';
  } elseif (file_exists($uploaddir_abs.$listing_num."_".$photo_num.'.JPG')) {
    return $listing_num."_".$photo_num.'.JPG';
  } elseif (file_exists($uploaddir_abs.$listing_num."_".$photo_num.'.gif')) {
    return $listing_num."_".$photo_num.'.gif';
  } elseif (file_exists($uploaddir_abs.$listing_num."_".$photo_num.'.GIF')) {
    return $listing_num."_".$photo_num.'.GIF';
  } elseif (file_exists($uploaddir_abs.$listing_num."_".$photo_num.'.png')) {
    return $listing_num."_".$photo_num.'.png';
  } elseif (file_exists($uploaddir_abs.$listing_num."_".$photo_num.'.PNG')) {
    return $listing_num."_".$photo_num.'.PNG';
  } else {
    return FALSE;
  }
}

// Function for finding photo of agent
function agent_photo_exists($agent_num,$uploaddir_abs="/home/naturmls/public_html/marion/agents/") {
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

// Function for finding logo for firm
function firm_logo_exists($firm_num,$uploaddir_abs="/home/naturmls/public_html/marion/agents/") {
  if (file_exists($uploaddir_abs.'firm'.$firm_num.'.jpg')) {
    return 'firm'.$firm_num.'.jpg';
  } elseif (file_exists($uploaddir_abs.'firm'.$firm_num.'.JPG')) {
    return 'firm'.$firm_num.'.JPG';
  } elseif (file_exists($uploaddir_abs.'firm'.$firm_num.'.gif')) {
    return 'firm'.$firm_num.'.gif';
  } elseif (file_exists($uploaddir_abs.'firm'.$firm_num.'.GIF')) {
    return 'firm'.$firm_num.'.GIF';
  } elseif (file_exists($uploaddir_abs.'firm'.$firm_num.'.png')) {
    return 'firm'.$firm_num.'.png';
  } elseif (file_exists($uploaddir_abs.'firm'.$firm_num.'.PNG')) {
    return 'firm'.$firm_num.'.PNG';
  } else {
    return FALSE;
  }
}

// Function for finding appropriate thumbnail photo and displaying; should always return output (html code for image)
function thumbnailPhoto($abs_photos,$listingID,$width='95',$height='71') {
   global $http_photos, $http_imgs;
   for($i=3;$i>=0;$i--) {
      if (photo_exists($abs_photos,$listingID,$i)) {
         $src = photo_exists($abs_photos,$listingID,$i);
      }
   }
   if (!empty($src)) {
      // Adjust the dimensions of the thumbnail to fit our requirements
      $image_info = getimagesize($abs_photos.$src);
      $new_height = ($image_info[1]/$image_info[0])*$width;
      $new_width = ($image_info[2]/$image_info[0])*$height;
      $output .= "<img src='".$http_photos.$src."' width='$width' height='$new_height' border='0' alt='Thumbnail photo' title='Thumbnail photo' />";
   } else {
      $output = "<img src='".$http_imgs."nophoto.jpg' width='95' height='71' border='0' alt='No photo available' title='No photo available' />";
   }
   return $output;
}

// Count the hit
function countHit($dbcnx,$mls_no,$type,$source,$sourceid) {
   $sql_hit = "INSERT INTO tbl_hits (mls_no,".$source."_id,hit_date,type) VALUES (:mls_no,:".$source."_id,:hit_date,:type)";
   $hit_date = date("Y-m-d H:i:s");
   try {
      $result_hit = $dbcnx->prepare($sql_hit);
      $result_hit->bindParam(':mls_no', $mls_no, PDO::PARAM_INT);
      $result_hit->bindParam(':'.$source.'_id', $sourceid, PDO::PARAM_INT);
      $result_hit->bindParam(':hit_date', $hit_date, PDO::PARAM_STR);
      $result_hit->bindParam(':type', $type, PDO::PARAM_STR);
      $result_hit->execute();
   } catch (PDOException $e) {
      $err_msg = "Query error: ".$e->getMessage()."<br />\n";
   }
   if (!empty($err_msg)) {
      return $err_msg;
   } else {
      return true;
   }
}
