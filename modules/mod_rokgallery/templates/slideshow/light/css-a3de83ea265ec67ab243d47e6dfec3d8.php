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

.rg-ss-info {color: #fff;text-shadow: 1px 1px 4px rgba(0,0,0,0.5);}.rg-ss-navigation-container {background: #eee;}.rg-ss-thumb {border: 2px solid #fff;box-shadow: 0 0 5px rgba(0,0,0,0.6);-webkit-box-shadow: 0 0 5px rgba(0,0,0,0.6);-moz-box-shadow: 0 0 5px rgba(0,0,0,0.6);}.rg-ss-thumb.active {border-color: #333;}.rg-ss-arrow-left {background: #eee url(images/divider.png) 100% 0 repeat-y;}.rg-ss-arrow-right {background: #eee url(images/divider.png) 0 0 repeat-y;}.rg-ss-arrow-left:hover, .rg-ss-arrow-right:hover {background-color: #ddd;}.rg-ss-arrow-left span, .rg-ss-arrow-right span {background-image: url(images/slideshow-arrows.png);}.rg-ss-controls .next, .rg-ss-controls .prev {background-color: #fff;background-image: url(images/slideshow-arrows.png);}.rg-ss-controls .next:hover, .rg-ss-controls .prev:hover {background-color: #ddd;}.rg-ss-loader {background: #000;border-top: 1px solid rgba(255,255,255,0.6);border-bottom: 1px solid rgba(255,255,255,0.6);}.rg-ss-progress {background: #fff;}