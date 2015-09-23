<?php
$page_title = 'Home Page';
$matatag_description = '';
include('header.php');
?>
<div class="content">
    <h1>Rental Details</h1>
<?php
$uploaddir_abs = "/home/jwmorton/public_html/uploads/";
$uploaddir_rel = "../uploads/";

// Function for finding photos for listings
function photo_exists($uploaddir_abs,$listing_num,$photo_num) {
  if (file_exists($uploaddir_abs.$listing_num.'_'.$photo_num.'.jpg')) {
    return $listing_num.'_'.$photo_num.'.jpg';
  } elseif (file_exists($uploaddir_abs.$listing_num.'_'.$photo_num.'.JPG')) {
    return $listing_num.'_'.$photo_num.'.JPG';
  } elseif (file_exists($uploaddir_abs.$listing_num.'_'.$photo_num.'.gif')) {
    return $listing_num.'_'.$photo_num.'.gif';
  } elseif (file_exists($uploaddir_abs.$listing_num.'_'.$photo_num.'.GIF')) {
    return $listing_num.'_'.$photo_num.'.GIF';
  } elseif (file_exists($uploaddir_abs.$listing_num.'_'.$photo_num.'.png')) {
    return $listing_num.'_'.$photo_num.'.png';
  } elseif (file_exists($uploaddir_abs.$listing_num.'_'.$photo_num.'.PNG')) {
    return $listing_num.'_'.$photo_num.'.PNG';
  } else {
    return FALSE;
  }
}

// Connect to the Db
include 'admin/dbconn.php';
// Establish GET & POST variables
import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];

echo " <table width='569' border='0' cellspacing='4' cellpadding='0'>\n";
// Select items from db
$sql = "SELECT * FROM tbl_realestate";
$sql .= " WHERE id='$id'";
//echo "sql: $sql<br />\n";
$result = @mysql_query($sql);
if (!$result) {
   echo("<p>Error performing query: " . mysql_error() . "</p>");
   exit();
}
while ($row = @mysql_fetch_array($result)) {
   $photo_name = str_replace(" ","_",str_replace("\"","",str_replace("\'","",str_replace("#","",$row['address']))));
   echo " <tr>\n";
   echo "  <td align='center' colspan='3'>";
   if (photo_exists($uploaddir_abs,$photo_name,1)) {
      $photo_src = photo_exists($uploaddir_abs,$photo_name,1);
      $img_info = getimagesize($uploaddir_abs.$photo_src);
      if ($img_info[0] < 321) {
         echo "<img src='".$uploaddir_rel.$photo_src."' ".$img_info[3]." border='0' />";
      } else {
         $new_height = ($img_info[1]/$img_info[0])*320;
         echo "<img src='".$uploaddir_rel.$photo_src."' width='320' height='$new_height' border='0' />\n";
      }
   } else {
      echo "&nbsp;";
   }
   if (!empty($row['virtual_tour_url'])) {
      echo "<strong><a href='".$row['virtual_tour_url']."' target='_blank'>Click for Virtual Tour</a></strong>\n";
   }
   echo "</td>\n";
   echo "<td>";
   echo "<a href='index.php'>Back to Search Results</a><br />\n";
   echo "<p>Rental #: ".$row['rental_id']."</p>\n";
   echo "<p>".$row['address']."<br />".$row['city'].", FL<br />Citrus County</p>\n";
   echo "<p><span style='font-weight: bold; color: #f00;'>".$row['status']."</span><br />\n";
   /*if ($row['status'] == 'Available') {
      echo "<a href='rental_app.pdf' target='_blank'>Click Here for Rental Application</a>\n";
   }*/
   echo "</p>\n";
   echo "<p>For More Information Contact:<br /><a href='mailto:jlfudgec21@gmail.com'>".$row['agent_name']."</a><br />Phone: 352.726.9010</p>\n";
   echo "</td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='center' colspan='4'><hr color='#000000' size='2' noshade /></td>";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='center' colspan='4'><font face='Arial,Helvetica,sans-serif' size='3'><b>Rental Property Details</b></font></td>";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='center' colspan='4'><hr color='#000000' size='2' noshade /></td>";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='center' colspan='2'><font face='Arial,Helvetica' size='2'><b>Rent</b></font></td>";
   echo "  <td align='center' colspan='2'><font face='Arial,Helvetica' size='2'><b>Schools</b></font></td>";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Monthly Rent: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['rent']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Elementary School: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['school_elementary']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Security Deposit: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['security']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Middle School: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['school_middle']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Lease Length: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['lease']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>High School: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['school_high']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='center' colspan='2'><font face='Arial,Helvetica' size='2'><b>Property Detail</b></font></td>";
   echo "  <td align='center' colspan='2'><font face='Arial,Helvetica' size='2'><b>More Info...</b></font></td>";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Type: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['type']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2' color='#FF0000'>Smoking Allowed: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2' color='#FF0000'>".$row['smoking']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Subdivision: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['subdivision']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Pets Allowed: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['pets']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Bedrooms: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['bedrooms']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Lot Size: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['lot_size']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Bathrooms: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['bathrooms']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Garage: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['garage']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Year Built: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['year_built']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Parking: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['parking']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Waterfront: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['waterfront']."</font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Pool: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['pool']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'> </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'></font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>Furnished: </font></td>\n";
   echo "  <td><font face='Arial,Helvetica' size='2'>".$row['furnished']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td colspan='4'><font face='Arial,Helvetica' size='2'>".$row['descr']."</font></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td colspan='4'>\n";
   for ($i=1; $i<=10; $i++) {
      if (photo_exists($uploaddir_abs,$photo_name,$i)) {
         $photo_src = photo_exists($uploaddir_abs,$photo_name,$i);
         $img_info = getimagesize($uploaddir_abs.$photo_src);
         if ($img_info[0] < 641) {
            echo "<img src='".$uploaddir_rel.$photo_src."' ".$img_info[3]." border='0' /><br /><br />";
         } else {
            $new_height = ($img_info[1]/$img_info[0])*640;
            echo "<img src='".$uploaddir_rel.$photo_src."' width='640' height='$new_height' border='0' /><br /><br />\n";
         }
      } else {
         echo "&nbsp;";
      }
   }
   echo "  </td>\n";
   echo " </tr>\n";
}
echo " </table>\n";
/* Free the mysql result */
mysql_free_result($result);
/* Closing connection */
mysql_close($dbcnx);
?>
 </div>
</body>
</html>
