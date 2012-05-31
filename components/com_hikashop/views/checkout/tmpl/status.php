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
<br/>
<span id="hikashop_checkout_status">
	<?php 
	$array=array();
	if(!empty($this->shipping_data)){
		$array[]= JText::sprintf('HIKASHOP_SHIPPING_METHOD_CHOSEN',$this->shipping_data->shipping_name);
	}
	if(!empty($this->payment_data)){
		$array[]= JText::sprintf('HIKASHOP_PAYMENT_METHOD_CHOSEN',$this->payment_data->payment_name);
	}
	echo implode('<br/>',$array);
	?>
</span>