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

/*** grid.css ***/

.rg-gm-slice-container {margin: 0 -5px;}.rg-gm-slice-list {margin: 0;padding: 0;list-style: none;overflow: hidden;text-align: center;}.cols2 .rg-gm-slice-list li {width: 50%;}.cols3 .rg-gm-slice-list li {width: 33%;}.cols4 .rg-gm-slice-list li {width: 25%;}.cols5 .rg-gm-slice-list li {width: 20%;}.cols6 .rg-gm-slice-list li {width: 16.6%;}.cols7 .rg-gm-slice-list li {width: 14.2%;}.cols8 .rg-gm-slice-list li {width: 12.5%;}.cols9 .rg-gm-slice-list li {width: 11.1%;}.cols10 .rg-gm-slice-list li {width: 10%;}.rg-gm-slice-list li {float: left;}.rg-gm-slice-item {padding: 8px;border-radius: 3px;-moz-border-radius: 3px;transition: background-color 0.3s linear;-webkit-transition: background-color 0.3s linear;-moz-transition: background 0.3s linear;-o-transition: background-color 0.3s linear;}.rg-gm-slice {display: inline-block;padding: 4px;border-radius: 3px;-moz-border-radius: 3px;margin: 0 0 5px 0;}.rg-gm-slice a {display: inline-block;}.rg-gm-title {font-size: 120%;display: block;}.rg-gm-caption {display: block;margin-top: 5px;}