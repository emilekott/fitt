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
class documentationController extends JController{
	function listing(){
		hikashop_setTitle(JText::_('DOCUMENTATION'),'help_header','documentation');
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$config =& hikashop_config();
		$level = $config->get('level');
		$url = HIKASHOP_HELPURL.'documentation&level='.$level;
?>
        <div id="hikashop_div">
            <iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="hikashop_frame" id="hikashop_frame" src="<?php echo $url; ?>">
            </iframe>
        </div>
<?php
	}
}