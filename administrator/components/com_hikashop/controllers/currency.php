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
class CurrencyController extends hikashopController{
	var $type='currency';
	function __construct(){
		parent::__construct();
		$this->modify[]='update';
	}
	function update(){
		$ratePlugin = hikashop_import('hikashop','rates');
		if($ratePlugin){
			$ratePlugin->updateRates();
		}else{
			$app=& JFactory::getApplication();
			$app->enqueueMessage('Currencies rates auto update plugin not found !','error');
		}
		$this->listing();
	}
}