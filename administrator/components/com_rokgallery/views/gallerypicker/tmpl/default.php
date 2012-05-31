<?php
/**
  * @version   $Id: default.php 39778 2011-07-08 00:02:29Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

defined('_JEXEC') or die('Restricted access');

$this->_replaceMooTools();

RokCommon_Composite::get($this->context)->load('includes.php', array('that'=>$this));
echo RokCommon_Composite::get($this->context)->load('default.php', array('that'=>$this));