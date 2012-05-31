<?php
/**
 * @package		HikaShop for Joomla!
 * @version		1.5.5
 * @author		hikashop.com
 * @copyright	(C) 2010-2011 HIKARI SOFTWARE. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
include(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');
$taskGroup = JRequest::getCmd('ctrl','dashboard');
$config =& hikashop_config();
JHTML::_('behavior.tooltip');
$bar = & JToolBar::getInstance('toolbar');
$bar->addButtonPath(HIKASHOP_BUTTON);
if($taskGroup != 'update' && !$config->get('installcomplete')){
    $url = hikashop_completeLink('update&task=install',false,true);
    echo "<script>document.location.href='".$url."';</script>\n";
    echo 'Install not finished... You will be redirected to the second part of the install screen<br/>';
    echo '<a href="'.$url.'">Please click here if you are not automatically redirected within 3 seconds</a>';
    return;
}


if(!include(HIKASHOP_CONTROLLER.$taskGroup.'.php')){
	echo 'controller '.$taskGroup.' not found';
	return;
}
ob_start();
$className = ucfirst($taskGroup).'Controller';
$classGroup = new $className();
JRequest::setVar( 'view', $classGroup->getName() );
$classGroup->execute( JRequest::getCmd('task','listing'));
$classGroup->redirect();
if(JRequest::getString('tmpl') !== 'component'){
	echo hikashop_footer();
}
echo '<div id="hikashop_main_content">'.ob_get_clean().'</div>';
hikashop_cleanCart();