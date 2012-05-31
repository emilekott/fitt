<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
$url = JURI::root(true) . '/administrator/index.php?option=com_rokgallery&task=ajax&format=raw';
echo "var RokGallery = {url: '".$url."'};";