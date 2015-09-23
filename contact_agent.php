<?php
// Establish GET & POST variables
import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];

/* 
$to_email = "cwebb@mychurchserver.com";
$to_name = "Chris Webb";
For testing only
$firm_name = "";
$website = "";
http://citrusmls.mychurchserver.com/contact_agent.php?
mls_id=344277
to_email=pineridge@floridashowcaseproperties.com
to_name=Prudential%20Florida%20Showcase%20Prop.,%20BH
firm_name=Prudential%20Florida%20Showcase%20Prop.,%20BH
website=http://www.floridashowcaseproperties.com
 */

If (empty($submit) || empty($from_email)) {
   /* Display the contact form */
   echo "<html>\n<head>\n <title>Contact an Agent</title>\n</head>\n";
   echo "<body>\n<div align='center'>";
   echo "<form action='$PHP_SELF' name='ContactAgentForm' method='post'>\n";
   echo "<table width='500' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;background: #CCC;border: 1px solid #000;'>\n";
   echo " <tr>\n";
   echo "  <td colspan='2' align='center'><span style='font-size: 15px;'><b>Contact the Agent</b></span></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='right'><b>To:</b></td>\n";
   echo "  <td align='left'>$to_name</td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='right' valign='top'><b>From:</b></td>\n";
   echo "  <td align='left'><input type='text' name='from_name' size='20' /><i>Your Name</i><br /><input type='text' name='from_email' size='20' /><i>Your Email (required)</i></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='right'><b>Subject:</b></td>\n";
   echo "  <td align='left'>Requesting information from $to_name about MLS# $mls_id</td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='left' colspan='2'><hr width='100%' size='1' color='#000000' noshade /><b>Message:</b><br />\n";
   echo "  $to_name,<br />I would like more information about one of your listings (MLS# $mls_id). Please contact me at your earliest convenience.<br />\n";
   echo "  My phone number is: <input type='text' name='Phone' size='20' /><br />\n";
   echo "  And the best time to call me is:<br /><input type='radio' name='Best_time_to_call' value='Anytime' />Anytime <input type='radio' name='Best_time_to_call' value='Morning' />Morning <input type='radio' name='Best_time_to_call' value='Afternoon' />Afternoon <input type='radio' name='Best_time_to_call' value='Evening' />Evening<br />\n";
   echo "  </td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='left' colspan='2'><b>Additional Note:</b><br />\n";
   echo "  <textarea name='note' rows='4' cols='50'></textarea>\n";
   echo "  </td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='center' colspan='2'><input type='submit' name='submit' value='Send' />&nbsp;&nbsp;<input type='reset' name='reset' value='Cancel' onClick='javascript:window.close()' /></td>\n";
   echo " </tr>\n";
   echo "</table>\n";
   echo "<input type='hidden' name='mls_id' value='$mls_id' />\n";
   echo "<input type='hidden' name='to_name' value='$to_name' />\n";
   echo "<input type='hidden' name='to_email' value='$to_email' />\n";
   echo "<input type='hidden' name='firm_name' value='$firm_name' />\n";
   echo "<input type='hidden' name='website' value='$website' />\n";
   echo "</form>\n";
   echo "</div>\n</body>\n</html>\n";
} Else {
	$mls_id = $_POST['mls_id'];
	$to_name = $_POST['to_name'];
	$to_email = $_POST['to_email'];
	$firm_name = $_POST['firm_name'];
	$website = $_POST['website'];
   /* Check for variables */
   if (empty($mls_id) || empty($to_name) || empty($to_email) || empty($firm_name) || empty($website)) {
      echo "I'm sorry, but we are currently missing some information necessary for contacting this agent or firm. Please check back soon.<br />\n";
      exit();
   }
   /* Check the to_email against our db - stop spammers from relaying messages through us */
   include 'dbconn.php';
   $sql1 = "SELECT email_leads FROM tbl_agents_info WHERE email_leads = '$to_email'";
   $result1 = $dbcnx->query($sql1);
   $db_email1 = $result1->fetch(PDO::FETCH_ASSOC);
   if ($db_email1['email_leads'] !== $to_email) {
      $sql2 = "SELECT email_leads FROM tbl_firms_info WHERE email_leads = '$to_email'";
      $result2 = $dbcnx->query($sql2);
      $db_email2 = $result2->fetch(PDO::FETCH_ASSOC);
      if ($db_email2['email_leads'] !== $to_email) {
         echo "I'm sorry, but we are currently missing a valid email address necessary for contacting this agent or firm. Please check back soon.<br />\n";
         exit();
      }
      /*echo "I'm sorry, but we are currently missing a valid email address necessary for contacting this agent or firm. Please check back soon.<br />\n";
      exit();*/
   }
   if (!empty($result1)) { $result1->closeCursor(); }
   if (!empty($result2)) { $result2->closeCursor(); }
   /* Closing connection */
   $dbcnx = null;

   /* Set some variables */
   $eol = "\n";
   if ($firm_name == 'ERA Suncoast Realty - CR') {
      $to = "\"$to_name\" <990008.lead@cendant.leadrouter.com>,info@erasuncoast.com";
   }
   $to = "\"$to_name\" <$to_email>";
   $subject = "Requesting information from $to_name about MLS# $mls_id";
   $from = "$from_name <$from_email>";
   $headers = "From: $from".$eol;
   $headers .= "Reply-To: $from".$eol;
   $headers .= "Return-Path: $from".$eol;
   $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$eol;
   $headers .= "X-Mailer: PHP v".phpversion().$eol;
   $headers .= "MIME-Version: 1.0".$eol;
   $headers .= "Content-type: text/html; charset=\"us-ascii\"".$eol;

   $safenote = addslashes($note);
   $text = "<html>";
   $text .= " <body style='font-family: Arial,Helvetica,sans-serif; font-size: 13px; color: #000;'>";
   $text .= "$to_name,<br />I would like more information about one of your listings (MLS# $mls_id). Please contact me at your earliest convenience.<br />";
   $text .= "My phone number is: $Phone. And the best time to call me is: $Best_time_to_call.<br />";
   $text .= "Additional Note:<br />$safenote<br />";
   $text .= "I found your listing on this site: $website.<br /><br />";
   $text .= "Thank you,<br />$from_name";
   $text .= " </body>";
   $text .= "</html>";

   if (mail($to,$subject,$text,$headers)) {
      echo "Thank you for your inquiry. An agent should be contacting you very soon.<br />\n";
   } else {
      echo "There has been an error and we are unable to send your message at this time. Please try again later.<br />\n";
   }
}
/*
       // Common Headers
       $headers .= "From: $from_name <$from_email>".$eol;
       $headers .= "Reply-To: $from_name <$from_email>".$eol;
       $headers .= "Return-Path: $from_name <$from_email>".$eol;    // these two to set reply address
       $headers .= "Message-ID: <".$now." Postmaster@".$_SERVER['SERVER_NAME'].">".$eol;
       $headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters
       # Boundry for marking the split & Multitype Headers
       $mime_boundary=md5(time());
       $headers .= 'MIME-Version: 1.0'.$eol;
       $headers .= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"".$eol;
       $msg = "";

       // Setup for text OR html
       $msg .= "Content-Type: multipart/alternative".$eol;

       // Text Version
       $msg .= "--".$mime_boundary.$eol;
       $msg .= "Content-Type: text/plain; charset=iso-8859-1".$eol;
       $msg .= "Content-Transfer-Encoding: 8bit".$eol;
       $msg .= "This is a multi-part message in MIME format.".$eol;
       $msg .= "If you are reading this, please update your email-reading-software.".$eol;
       $msg .= $body_text.$eol.$eol;

       // HTML Version
       $msg .= "--".$mime_boundary.$eol;
       $msg .= "Content-Type: text/html; charset=iso-8859-1".$eol;
       $msg .= "Content-Transfer-Encoding: 8bit".$eol;
       $msg .= $body_html.$eol.$eol;

       // Finished
       $msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.

       // SEND THE EMAIL
       ini_set(sendmail_from,'postmaster@mychurchserver.com');  // the INI lines are to force the From Address to be used !
         mail($emailaddress, $emailsubject, $msg, $headers);
       ini_restore(sendmail_from);
        */

?>
