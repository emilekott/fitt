<?php 
ob_start ("ob_gzhandler");
header("Content-type: text/css; charset= UTF-8");
header("Cache-Control: must-revalidate");
$expires_time = 1440;
$offset = 60 * $expires_time ;
$ExpStr = "Expires: " . 
gmdate("D, d M Y H:i:s",
time() + $offset) . " GMT";
header($ExpStr);
                ?>

/*** style.css ***/

.rg-gm-slice-item:hover {background: #f2f2f2;}.rg-gm-slice {background: #fff;box-shadow: 0 1px 3px rgba(0,0,0,0.2);-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.2);}