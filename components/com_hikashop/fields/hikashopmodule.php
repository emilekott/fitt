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
class JFormFieldHikashopmodule extends JFormField{
        protected $type = 'hikashopmodule';
        protected function getInput() {
            if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
				return 'This module can not work without the Hikashop Component';
			}
			$id = JRequest::getInt('id');
			if(!empty($id)){
				$text = '<a style="float:left;" title="'.JText::_('HIKASHOP_OPTIONS').'"  href="'.JRoute::_('index.php?option=com_hikashop&ctrl=modules&task=edit&cid[]='.$id).'" >'.JText::_('HIKASHOP_OPTIONS').'</a>';
			}else{
				$text = JText::_('HIKASHOP_OPTIONS_EDIT');
			}
			return $text;
        }
}
