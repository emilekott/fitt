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
	function __construct($config = array(),$skip=false){
		$this->display[]='update';
		if(!$skip){
			parent::__construct($config,$skip);
			$this->registerDefaultTask('update');
		}
		JRequest::setVar('tmpl','component');
	}
	function update(){
		$currency=JRequest::getInt('hikashopcurrency',0);
		if(!empty($currency)){
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.currency_id', $currency );
			$url = JRequest::getString('return_url','');
			if(!empty($url)){
				$app->redirect(urldecode($url));
			}
		}
		return true;
	}
}