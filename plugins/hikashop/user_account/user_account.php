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
class plgHikashopUser_account extends JPlugin{
    function onUserAccountDisplay(&$buttons){
    	global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
    	if(version_compare(JVERSION,'1.6','<')){
			$url = JRoute::_('index.php?option=com_user&view=user&task=edit'.$url_itemid);
		}else{
			$url = JRoute::_('index.php?option=com_users&view=profile&layout=edit'.$url_itemid);
		}
		$button = array('link'=>$url,'level'=>0,'image'=>'user2','text'=>JText::_('CUSTOMER_ACCOUNT'),'description'=>'<ul><li>'.JText::_('EDIT_INFOS').'</li></ul>');
		array_unshift($buttons,$button);
		return true;
    }
}