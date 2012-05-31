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
class JElementSelectproduct extends JElement{
	function fetchElement($name, $value, &$node, $control_name)
	{
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			echo 'HikaShop is required';
			return;
		}
		$db =& JFactory::getDBO();
		$db->setQuery("SELECT `product_id`, CONCAT(product_name,' ( ',product_code,' )') as `title` FROM #__hikashop_product WHERE `product_type`='main' AND `product_published`=1  ORDER BY `product_code` ASC");
		$results = $db->loadObjectList();
		return JHTML::_('select.genericlist', $results, $control_name.'['.$name.']' , 'size="1"', 'product_id', 'title', $value);
	}
}
