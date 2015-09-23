<?php
// Establish GET & POST variables
import_request_variables("gp");
$PHP_SELF = $_SERVER['PHP_SELF'];

if (isset($display_agent)) {
   $link_back .= "&display_agent=$display_agent";
}
if (isset($display_firm)) {
   $link_back .= "&display_firm=$display_firm";
}
/* 
$to_email = "cwebb@mychurchserver.com";
$to_name = "Chris Webb";
For testing only
$firm_name = "";
$website = "";
 */

If (empty($submit)) {
   /* Display the contact form */
   echo "<html>\n<head>\n <title>Email a Friend</title>\n</head>\n";
   echo "<body>\n<div align='center'>";
   echo "<form action='$PHP_SELF' name='ContactForm' method='post'>\n";
   echo "<table width='500' border='0' cellpadding='2' cellspacing='0' style='font-family: Arial,Helvetica,sans-serif;font-size: 14px;background: #CCC;border: 1px solid #000;'>\n";
   echo " <tr>\n";
   echo "  <td colspan='2' align='center'><span style='font-size: 15px;'><b>Email a Friend</b></span></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='right' valign='top'><b>To:</b></td>\n";
   echo "  <td align='left'><input type='text' name='to_name' size='20' /><i>Friend's Name</i><br /><input type='text' name='to_email' size='20' /><i>Friend's Email</i></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='right' valign='top'><b>From:</b></td>\n";
   echo "  <td align='left'><input type='text' name='from_name' size='20' /><i>Your Name</i><br /><input type='text' name='from_email' size='20' /><i>Your Email</i></td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='right'><b>Subject:</b></td>\n";
   echo "  <td align='left'>Look at this property I just found online</td>\n";
   echo " </tr>\n";
   echo " <tr>\n";
   echo "  <td align='left' colspan='2'><hr width='100%' size='1' color='#000000' noshade /><b>Message:</b><br />\n";
   echo "  Here is some info about a property I just found online. <u>More Details</u><br /> MLS#: $mls_id offered at $".$price."<br />\n";
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
   echo "<input type='hidden' name='link_back' value='$link_back' />\n";
   echo "<input type='hidden' name='price' value='$price' />\n";
   echo "</form>\n";
   echo "</div>\n</body>\n</html>\n";
} Else {
   /* Check for variables */
   if (empty($mls_id) || empty($link_back)) {
      echo "I'm sorry, but we are currently missing some information necessary for contacting this agent or firm. Please check back soon.<br />\n";
      exit();
   }

   $headers = "From: $from".$eol;
   $headers .= "Reply-To: $from".$eol;
   $headers .= "Return-Path: $from".$eol;
   $headers .= "Bcc: $bcc".$eol;
   $headers .= "Message-ID: <".time()." Postmaster@".$_SERVER['SERVER_NAME'].">".$eol;
   $headers .= "X-Mailer: PHP v".phpversion().$eol;
   $headers .= "MIME-Version: 1.0".$eol;
   $headers .= "Content-type: text/html; charset=\"us-ascii\"".$eol;
   $settings = "Settings: eol: ".str_replace("\\","",$eol).", headers: $headers";
   $text .= "<html>";
   $text .= " <body style='font-family: Arial,Helvetica,sans-serif; font-size: 13px; color: #000;'>";
   $text .= $content.$settings;
   $text .= " </body>";
   $text .= "</html>";

   /* Set some variables */
   $eol = "\r\n";
   $to = "$to_name <$to_email>";
   $subject = "Look at this property I just found online";
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
   $text .= "Here is some info about a property I just found online. <a href='$link_back'>More Details</a><br /> MLS#: $mls_id offered at $".$price."<br />";
   $text .= "Additional Note:<br />$safenote<br />";
   $text .= " </body>";
   $text .= "</html>";

   if (mail($to,$subject,$text,$headers)) {
      echo "Thank you for using our service. Have a great day!<br />\n";
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
