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
class hikashopUserType{
	function load($value){
		$this->values = array();
		$query = 'SELECT user_id,user_email FROM '.hikashop_table('user');
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$users = $db->loadObjectList('user_id');
		if(!empty($users)){
			foreach($users as $user){
				$this->values[] = JHTML::_('select.option', (int)$user->user_id, $user->user_id . ' ' .$user->user_email );
			}
		}
	}
	function display($map,$value,$options=''){
		if(empty($this->values)){
			$this->load($value);
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', (int)$value );
	}
}