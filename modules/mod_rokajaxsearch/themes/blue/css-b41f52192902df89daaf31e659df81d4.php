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

/*** rokajaxsearch-theme.css ***/

#roksearch_search_str {background: #EFF8FA url(search-icon.png) 98% 50% no-repeat;border: 1px solid #b7d9e9;color: #336D83;}#roksearch_search_str.loading {background-color:#EFF8FA;border: 1px solid #b7d9e9;}#roksearch_results {background: #EFF8FA;border: 1px solid #b7d9e9;}#roksearch_results a#roksearch_link {background: url(close.png) 0 0 no-repeat;}#roksearch_results h3 {color: #444;}#roksearch_results span {color: #888;}#roksearch_results .roksearch_header, #roksearch_results .roksearch_row_btm {background: #fff;border-bottom: 1px solid #b7d9e9;color: #666;}#roksearch_results .roksearch_row_btm span {color: #999;}#roksearch_results span.small {color: #666;}#roksearch_results span.highlight {background: #999;color: #fff;}#roksearch_results a:hover {color: #0B3768;}#roksearch_results .roksearch_odd {background: #EFF8FA;border-bottom: 1px solid #b7d9e9;}#roksearch_results .roksearch_even {background: #e9f0f2;border-bottom: 1px solid #b7d9e9;}#roksearch_results .roksearch_odd-hover, #roksearch_results .roksearch_even-hover {background-color: #fff;}#roksearch_results .roksearch_odd-hover h3, #roksearch_results .roksearch_even-hover h3 {background: url(right-arrow.png) 0 4px no-repeat;}.results ol.list li p {color: #666;}#roksearch_results .arrow-left {background: #e9e9e9 url(left-arrow.png) 50% 50% no-repeat;;border: 1px solid #d9d9d9;}#roksearch_results .arrow-left-disabled {background: #fefefe;border: 1px solid #e9e9e9;}#roksearch_results .arrow-right {background: #e9e9e9 url(right-arrow.png) 50% 50% no-repeat;border: 1px solid #d9d9d9;}#roksearch_results .arrow-right-disabled {background: #fefefe;border: 1px solid #e9e9e9;}form.search_result .inputbox {border: 1px solid #b7d9e9;}fieldset.phrase, fieldset.word, fieldset.only {border-bottom: 1px solid #b7d9e9;}#roksearch_results .rokajaxsearch-overlay {background-color: #EFF8FA;}