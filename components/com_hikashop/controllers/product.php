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
class productController extends hikashopController{
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display[]='updatecart';
		$this->display[]='cart';
		$this->display[]='cleancart';
		$this->display[]='contact';
		$this->display[]='compare';
		$this->display[]='waitlist';
		$this->display[]='send_email';
		$this->display[]='add_waitlist';
		$this->display[]='price';
		$this->display[]='download';
	}
	function authorize($task){
		if($this->isIn($task,array('display'))){
			return true;
		}
		return false;
	}
	function contact(){
		JRequest::setVar( 'layout', 'contact' );
		return $this->display();
	}
	function compare(){
		JRequest::setVar( 'layout', 'compare' );
		return $this->display();
	}
	function waitlist(){
		JRequest::setVar( 'layout', 'waitlist' );
		return $this->display();
	}
	function price(){
		JRequest::setVar( 'layout', 'option_price' );
		return $this->display();
	}
	function send_email(){
		JRequest::checkToken('request') or jexit( 'Invalid Token' );
		$element = null;
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['register'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$app=& JFactory::getApplication();
		if(empty($element->email)){
			$app->enqueueMessage(JText::_('VALID_EMAIL'));
			return $this->contact();
		}
		$config =& hikashop_config();
		if(!$config->get('product_contact',0)){
			return $this->contact();
		}
		$dispatcher =& JDispatcher::getInstance();
		$send = true;
		$dispatcher->trigger( 'onBeforeSendContactRequest', array( & $element,& $send ) );
		if($send){
			$subject = JText::_('CONTACT_REQUEST');
			$body = JText::_('FROM_ADDRESS').' : '.$element->email."\r\n".JText::_('FROM_NAME').' : '.$element->name."\r\n\r\n".$element->altbody;
			if(!empty($element->product_id)){
				$class = hikashop_get('class.product');
				$product = $class->get((int)$element->product_id);
				if(!empty($product)){
					if($product->product_type=='variant' && empty($product->product_name)){
						$product = $class->get((int)$element->product_parent_id);
					}
					if(!empty($product)){
						$subject = JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT',$product->product_name);
					}
				}
			}
			$mailer =& JFactory::getMailer();
			$config =& hikashop_config();
			$sender = array(
			    $config->get('from_email'),
			    $config->get('from_name') );
			$mailer->setSender($sender);
			$mailer->addReplyTo(array($element->email,@$element->name));
			$mailer->addRecipient(array($config->get('from_email')));
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$send = false;
			if($mailer->Send()){
				$app->enqueueMessage(JText::_('CONTACT_REQUEST_SENT'));
				if(!empty($product->product_id)){
					$url_itemid = '';
					if(!empty($Itemid)){
						$url_itemid = '&Itemid='.(int)$Itemid;
					}
					if(method_exists($app,'stringURLSafe')){
						$alias = $app->stringURLSafe($product->product_name);
					}else{
						$alias = JFilterOutput::stringURLSafe($product->product_name);
					}
					$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_completeLink('product&task=show&cid='.$product->product_id.'&name='.$alias.$url_itemid)));
				}
			}
		}
		$url = JRequest::getVar('redirect_url');
		if($send && !empty($url)){
			$app->redirect($url);
		}else{
			$this->contact();
		}
	}
	function add_waitlist() {
		JRequest::checkToken('request') or jexit( 'Invalid Token' );
		$element = null;
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['register'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$user = JFactory::getUser();
		$app=& JFactory::getApplication();
		if(empty($element->email) && $user->guest){
			$app->enqueueMessage(JText::_('VALID_EMAIL'));
			return $this->waitlist();
		}
		$config =& hikashop_config();
		if(!$config->get('product_waitlist',0)){
			return $this->waitlist();
		}
		$waitlist_subscribe_limit = $config->get('product_waitlist_sub_limit',10);
		$product_id = 0;
		$itemId = JRequest::getVar('Itemid');
		$alias = '';
		if(!empty($element->product_id)){
			$class = hikashop_get('class.product');
			$product = $class->get((int)$element->product_id);
			if(!empty($product)){
				if($product->product_type=='variant' && empty($product->product_name)){
					$product = $class->get((int)$element->product_parent_id);
				}
				$product_id = (int)$product->product_id;
				if(method_exists($app,'stringURLSafe')){
					$alias = $app->stringURLSafe($product->product_name);
				}else{
					$alias = JFilterOutput::stringURLSafe($product->product_name);
				}
			}
		}
		if( $product_id == 0 ) {
			return $this->waitlist();
		}
		$email = (!empty($element->email)) ? $element->email : '';
		$name = (!empty($element->name)) ? $element->name : '';
		$db =& JFactory::getDBO();
		$sql = 'SELECT waitlist_id FROM '.hikashop_table('waitlist').' WHERE email='.$db->quote($email).' AND product_id='.(int)$product_id;
		$db->setQuery($sql);
		$subscription = $db->loadResult();
		if(empty($subscription)) {
			$sql = 'SELECT count(*) FROM '.hikashop_table('waitlist').' WHERE product_id='.(int)$product_id;
			$db->setQuery($sql);
			$subscriptions = $db->loadResult();
			if( $subscriptions < $waitlist_subscribe_limit || $waitlist_subscribe_limit <= 0 ) {
				$sql = 'INSERT IGNORE INTO '.hikashop_table('waitlist').' (`product_id`,`date`,`email`,`name`,`product_item_id`) VALUES ('.(int)$product_id.', '.time().', '.$db->quote($email).', '.$db->quote($name).', '.(int)$itemId.');';
				$db->setQuery($sql);
				$db->query();
				$app->enqueueMessage(JText::_('WAITLIST_SUBSCRIBE'));
			} else {
				$app->enqueueMessage(JText::_('WAITLIST_FULL'));
			}
		} else {
			$app->enqueueMessage(JText::_('ALREADY_REGISTER_WAITLIST'));
		}
		$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_completeLink('product&task=show&cid='.$product->product_id.'&name='.$alias)));
		$url = JRequest::getVar('redirect_url');
		if(!empty($url)){
			$app->redirect($url);
		}else{
			$this->waitlist();
		}
	}
	function cleancart(){
		$class = hikashop_get('class.cart');
		if($class->hasCart()){
			$class->delete($class->cart->cart_id);
		}
		$url = JRequest::getVar('return_url','');
		if(empty($url)){
			$url = JRequest::getVar('url','');
			$url = urldecode($url);
		}else{
			$url = base64_decode(urldecode($url));
		}
		if(!empty($url)){
			if(strpos($url,'tmpl=component')!==false || strpos($url,'tmpl-component')!==false){
				if(!empty($_SERVER['HTTP_REFERER'])){
					$app =& JFactory::getApplication();
					$app->redirect($_SERVER['HTTP_REFERER']);
				}else{
					echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';
					exit;
				}
			}
			$this->setRedirect($url);
		}else{
			echo '<html><head><script type="text/javascript">history.go(-1);</script></head><body></body></html>';
			exit;
		}
	}
	function updatecart(){
		$char = JRequest::getString('characteristic','');
		if(!empty($char)){
			return $this->show();
		}else{
			$tmpl = JRequest::getCmd('tmpl','index');
			$add = JRequest::getCmd('add','');
			if(!empty($add)){
				$add=1;
			}else{
				$add=0;
			}
			$product_id = JRequest::getCmd('product_id',0);
			if(empty($product_id)){
				$product_id = JRequest::getCmd('cid',0);
			}
			$cart_product_id = JRequest::getCmd('cart_product_id',0);
			$quantity = JRequest::getInt('quantity',1);
			$class = hikashop_get('class.cart');
			if(!empty($product_id)){
				$type = JRequest::getWord('type','product');
				if($type=='product'){
					$product_id=(int)$product_id;
				}
				$status = $class->update($product_id,$quantity,$add,$type);
			}elseif(!empty($cart_product_id)){
				$status = $class->update($cart_product_id,$quantity,$add,'item');
			}else{
				$formData = JRequest::getVar( 'item', array(), '', 'array' );
				if(!empty($formData)){
					$class->update($formData,0,$add,'item');
				}else{
					$formData = JRequest::getVar( 'data', array(), '', 'array' );
					if(!empty($formData)){
						$class->update($formData,0,$add);
					}
				}
			}
			if(@$class->errors && $tmpl!='component'){
				if(!empty($_SERVER['HTTP_REFERER'])){
					$app =& JFactory::getApplication();
					$app->redirect($_SERVER['HTTP_REFERER']);
				}else{
					echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';
					exit;
				}
			}
			$checkout = JRequest::getString('checkout','');
			if(!empty($checkout)){
				global $Itemid;
				$url = 'checkout';
				if(!empty($Itemid)){
					$url.='&Itemid='.$Itemid;
				}
				$url = hikashop_completeLink($url,false,true);
				$this->setRedirect($url);
			}else{
				$url = JRequest::getVar('return_url','');
				if(empty($url)){
					$url = JRequest::getVar('url','');
					$url = urldecode($url);
				}else{
					$url = base64_decode(urldecode($url));
				}
				if(empty($url)){
					global $Itemid;
					$url = 'checkout';
					if(!empty($Itemid)){
						$url.='&Itemid='.$Itemid;
					}
					$url = hikashop_completeLink($url,false,true);
				}
				if($tmpl=='component'){
					$js ='';
					jimport('joomla.application.module.helper');
					global $Itemid;
					if(isset($Itemid) && empty($Itemid)){
						$Itemid=null;
						JRequest::setVar('Itemid',null);
					}
					$module = JModuleHelper::getModule('hikashop_cart',false);
					$config =& hikashop_config();
					jimport('joomla.html.parameter');
					$params = new JParameter( @$module->params );
					if(!empty($module)){
						$module_options = $config->get('params_'.$module->id);
					}
					if(empty($module_options)){
						$module_options = $config->get('default_params');
					}
					foreach($module_options as $key => $optionElement){
						$params->set($key,$optionElement);
					}
					if(!empty($module)){
						foreach(get_object_vars($module) as $k => $v){
							if(!is_object($v)){
								$params->set($k,$v);
							}
						}
					}
					$params->set('return_url',$url);
					hikashop_getLayout('product','cart',$params,$js);
					return true;
				}else{
					if(JRequest::getInt('popup')){
						if(strpos($url,'?')){
							$url.='&';
						}else{
							$url.='?';
						}
						$url.='popup=1';
					}
					$this->setRedirect($url);
					return false;
				}
			}
		}
	}
	function download() {
		$file_id = JRequest::getInt('file_id');
		if(!$file_id){ return false; }
		$fileClass = hikashop_get('class.file');
		$fileClass->download($file_id);
		return true;
	}
}