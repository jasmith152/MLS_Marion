<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
<SCRIPT LANGUAGE='JAVASCRIPT' TYPE='TEXT/JAVASCRIPT'>
<!--
var win=null;
function NewWindow(mypage,myname,w,h,pos,infocus){
if(pos=="random"){myleft=(screen.width)?Math.floor(Math.random()*(screen.width-w)):100;mytop=(screen.height)?Math.floor(Math.random()*((screen.height-h)-75)):100;}
if(pos=="center"){myleft=(screen.width)?(screen.width-w)/2:100;mytop=(screen.height)?(screen.height-h)/2:100;}
else if((pos!='center' && pos!="random") || pos==null){myleft=0;mytop=20}
settings="width=" + w + ",height=" + h + ",top=" + mytop + ",left=" + myleft + ",scrollbars=yes,location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=yes";win=window.open(mypage,myname,settings);
win.focus();}
// -->
</script>
<SCRIPT LANGUAGE='JAVASCRIPT' TYPE='TEXT/JAVASCRIPT'>
<!--
function roll(img_name1, img_src1)
   {
   document[img_name1].src = img_src1;
   }
// -->
</script>

<!-- lightbox -->
<script type="text/javascript" src="lightbox/prototype.js"></script>
  <script type="text/javascript" src="lightbox/scriptaculous.js?load=effects"></script>
  <script type="text/javascript" src="lightbox/lightbox.js"></script>
  <link rel="stylesheet" href="lightbox/lightbox.css" type="text/css" media="screen" />
</head>

<body topmargin="0" marginheight="0">
 <div align="center">
 <table class="wrapper" cellspacing="0" cellpadding="0" border="0">
  <tr>
   <td class="wrapper-tl">&nbsp;</td>
   <td class="wrapper-top">&nbsp;</td>
   <td class="wrapper-tr">&nbsp;</td>
  </tr>
  <tr>
   <td class="wrapper-left">&nbsp;</td>
   <td class="wrapper-content">
 <div id="header">
 </div>
 <div id="content">
 <?php 
 echo $output;
 ?>
 </div>
 <div id="footer">
 </div>
   </td>
   <td class="wrapper-right">&nbsp;</td>
  </tr>
  <tr>
   <td class="wrapper-bl">&nbsp;</td>
   <td class="wrapper-bottom">&nbsp;</td>
   <td class="wrapper-br">&nbsp;</td>
  </tr>
 </table>
 </div>
</body>
</html>
