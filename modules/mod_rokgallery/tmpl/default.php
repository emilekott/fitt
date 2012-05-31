<?php
 /**
  * @version   $Id: default.php 39617 2011-07-06 23:37:00Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

defined('_JEXEC') or die('Restricted access'); 

echo RokCommon_Composite::get($passed_params->layout_context)->load('default.php', array('passed_params'=>$passed_params));
