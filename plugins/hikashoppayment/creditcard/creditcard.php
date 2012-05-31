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
class plgHikashoppaymentCreditcard extends JPlugin
{
	function __construct(& $subject, $config){
		parent::__construct($subject, $config);
	}
	function needCC(&$method) {
		$method->ask_cc=true;
		$method->ask_ccv = @$method->payment_params->ask_ccv;
		$method->ask_owner = @$method->payment_params->ask_owner;
		$method->ask_cctype = @$method->payment_params->ask_cctype;
		if(!empty($method->ask_cctype)){
			$types = explode(',',$method->ask_cctype);
			$method->ask_cctype = array();
			foreach($types as $type){
				$method->ask_cctype[$type]=$type;
			}
		}
		return true;
	}
	function loadCC(){
		$app =& JFactory::getApplication();
		$this->cc_number = $app->getUserState( HIKASHOP_COMPONENT.'.cc_number');
		if(!empty($this->cc_number)){
			$this->cc_number = base64_decode($this->cc_number);
		}
		$this->cc_month = $app->getUserState( HIKASHOP_COMPONENT.'.cc_month');
		if(!empty($this->cc_month)){
			$this->cc_month = base64_decode($this->cc_month);
		}
		$this->cc_year = $app->getUserState( HIKASHOP_COMPONENT.'.cc_year');
		if(!empty($this->cc_year)){
			$this->cc_year = base64_decode($this->cc_year);
		}
		$this->cc_CCV = $app->getUserState( HIKASHOP_COMPONENT.'.cc_CCV');
		if(!empty($this->cc_CCV)){
			$this->cc_CCV = base64_decode($this->cc_CCV);
		}
		$this->cc_owner = $app->getUserState( HIKASHOP_COMPONENT.'.cc_owner');
		if(!empty($this->cc_owner)){
			$this->cc_owner = base64_decode($this->cc_owner);
		}
		$this->cc_type = $app->getUserState( HIKASHOP_COMPONENT.'.cc_type');
		if(!empty($this->cc_type)){
			$this->cc_type = base64_decode($this->cc_type);
		}
	}
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='creditcard' || !$method->enabled){
					continue;
				}
				if(!empty($method->payment_zone_namekey)){
					$zoneClass=hikashop_get('class.zone');
	    			$zones = $zoneClass->getOrderZones($order);
					if(!in_array($method->payment_zone_namekey,$zones)){
						return true;
					}
				}
				$this->needCC($method);
				$usable_methods[$method->ordering]=$method;
    		}
    	}
    	return true;
    }
    function onPaymentSave(&$cart,&$rates,&$payment_id){
    	$usable = array();
    	$this->onPaymentDisplay($cart,$rates,$usable);
    	$payment_id = (int) $payment_id;
    	foreach($usable as $usable_method){
    		if($usable_method->payment_id==$payment_id){
    			return $usable_method;
    		}
    	}
    	return false;
    }
    function onPaymentConfiguration(&$element){
    	$this->creditcard = JRequest::getCmd('name','creditcard');
    	if(empty($element)){
    		$element = null;
    		$element->payment_name='Credit card';
    		$element->payment_description='You can pay by credit card.';
    		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
    		$element->payment_type=$this->creditcard;
    		$element->payment_params=null;
    		$element->payment_params->information='We will now process the credit card transaction and contact you when completed.';
    		$element->payment_params->order_status='created';
    		$element->payment_params->ask_ccv = true;
    		$element->payment_params->ask_owner = false;
    		$element->payment_params->ask_cctype = '';
    		$element = array($element);
    	}
    	$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-creditcard-form');
		hikashop_setTitle(JText::_('CREDITCARD_PAYMENT'),'plugin','plugins&plugin_type=payment&task=edit&name='.$this->creditcard);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->creditcard);
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
		$this->editor = hikashop_get('helper.editor');
    }
    function onPaymentConfigurationSave(&$element){
    	$element->payment_params->information = JRequest::getVar('creditcard_information','','','string',JREQUEST_ALLOWRAW);
    	return true;
    }
    function onBeforeOrderCreate(&$order,&$do){
    	$app =& JFactory::getApplication();
		if($app->isAdmin()) {
			return true;
		}
    	if($order->order_payment_method=='creditcard'){
    		$this->loadCC();
    		$order->credit_card_info = $this;
    		$order->history->history_payment_id = $order->order_payment_id;
			$order->history->history_payment_method =$order->order_payment_method;
			$order->history->history_type = 'credit card';
    		$obj = null;
			$obj->cc_number=substr($this->cc_number,0,8);
			$obj->cc_month=$this->cc_month;
			$obj->cc_year=$this->cc_year;
			$obj->cc_type=@$this->cc_type;
			$order->history->history_data = base64_encode(serialize($obj));
    	}
    }
    function onHistoryDisplay(&$histories){
    	foreach($histories as $k => $history){
    		if($history->history_payment_method=='creditcard' && !empty($history->history_data)){
    			$data = unserialize(base64_decode($history->history_data));
    			$string='';
    			if(!empty($data->cc_type)){
    				$string.= JText::_('CARD_TYPE').': '.$data->cc_type.'<br />';
    			}
    			$string.= JText::_('DATE').': '.$data->cc_month.'/'.$data->cc_year.'<br />';
    			$string.= JText::_('BEGINNING_OF_CREDIT_CARD_NUMBER').': '.$data->cc_number.'<br />';
    			$string.='<a href="'.hikashop_completeLink('order&task=remove_history_data&history_id='.$history->history_id).'"><img src="'.HIKASHOP_IMAGES.'delete.png" /></a>';
    			$histories[$k]->history_data = $string;
    			static $done = false;
    			if(!$done){
    				$done = true;
    				$app =& JFactory::getApplication();
    				$app->enqueueMessage(JText::_('CREDITCARD_WARNING'));
    			}
    		}
    	}
    }
    function onAfterOrderConfirm(&$order,&$methods,$method_id){
    	$this->loadCC();
    	$method =& $methods[$method_id];
    	$orderObj = null;
    	$orderObj->order_status = $method->payment_params->order_status;
    	$orderObj->order_id = $order->order_id;
    	$orderClass=hikashop_get('class.order');
    	$orderClass->save($orderObj);
    	$app =& JFactory::getApplication();
    	$this->removeCart = true;
		$name = $method->payment_type.'_end.php';
    	$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;
    	if(!file_exists($path)){
    		if(version_compare(JVERSION,'1.6','<')){
    			$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$name;
    		}else{
    			$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$method->payment_type.DS.$name;
    		}
    		if(!file_exists($path)){
    			return true;
    		}
    	}
    	$information = $method->payment_params->information;
    	if(preg_match('#^[a-z0-9_]*$#i',$information)){
    		$information = JText::_($information);
    	}
    	$currencyClass = hikashop_get('class.currency');
    	$amount = $currencyClass->format($order->order_full_price,$order->order_currency_id);
    	$order_number = $order->order_number;
    	require($path);
		return true;
    }
}