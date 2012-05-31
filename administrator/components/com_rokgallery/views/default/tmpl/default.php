<?php
/**
  * @version   $Id: default.php 39778 2011-07-08 00:02:29Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

defined('_JEXEC') or die('Restricted access');
JToolBarHelper::title(JText::_('RokGallery'), 'generic.png');
//JToolBarHelper::preferences('com_rokgallery');
//JToolBarHelper::custom('', '', '', 'custom');

JHTML::_('behavior.modal');
$this->_replaceMooTools();

$toolbar = JToolBar::getInstance('toolbar');
$toolbar->addButtonPath(JPATH_COMPONENT.DS.'buttons');


$toolbar->appendButton('rokgallery', 'publish');
$toolbar->appendButton('rokgallery', 'unpublish');
$toolbar->appendButton('rokgallery', 'tag');
$toolbar->appendButton('rokgallery', 'delete');

$toolbar->appendButton('Separator');

$toolbar->appendButton('rokgallery', 'jobs');
$toolbar->appendButton('rokgallery', 'galleries');
$toolbar->appendButton('rokgallery', 'settings', 'index.php?option=com_config&view=component&layout=modal&component=com_rokgallery&tmpl=component&path=', '', "{handler: 'iframe', size: {x: 570, y: 300}}", 'modal');
$toolbar->appendButton('rokgallery', 'upload', '#', 'ok');

echo RokCommon_Composite::get('com_rokgallery.default')->load('default.php', array('that'=>$this));