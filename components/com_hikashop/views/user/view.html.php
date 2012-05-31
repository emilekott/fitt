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
class userViewUser extends JView{
	var $extraFields=array();
	var $requiredFields = array();
	var	$validMessages = array();
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function after_register(){
	}
	function cpanel(){
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$buttons = array();
		$buttons[] = array('link'=>hikashop_completeLink('address'.$url_itemid),'level'=>0,'image'=>'user','text'=>JText::_('ADDRESSES'),'description'=>'<ul><li>'.JText::_('MANAGE_ADDRESSES').'</li></ul>');
		$buttons[] = array('link'=>hikashop_completeLink('order'.$url_itemid),'level'=>0,'image'=>'order','text'=>JText::_('ORDERS'),'description'=>'<ul><li>'.JText::_('VIEW_ORDERS').'</li></ul>');
		JPluginHelper::importPlugin( 'hikashop' );
		JPluginHelper::importPlugin( 'hikashoppayment' );
		JPluginHelper::importPlugin( 'hikashopshipping' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onUserAccountDisplay', array( & $buttons) );
		$htmlbuttons = array();
		foreach($buttons as $oneButton){
			$htmlbuttons[] = $this->_quickiconButton($oneButton['link'],$oneButton['image'],$oneButton['text'],$oneButton['description'],$oneButton['level']);
		}
		$this->assignRef('buttons',$htmlbuttons);
		$app =& JFactory::getApplication();
		$pathway	=& $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items))
			$pathway->addItem(JText::_('CUSTOMER_ACCOUNT'),hikashop_completeLink('user'));
	}
	function _quickiconButton( $link, $image, $text,$description,$level){
		$url = hikashop_level($level) ? 'onclick="document.location.href=\''.$link.'\';"' : '';
		$html = '<div style="float:left;width: 100%;" '.$url.' class="icon"><a href="';
		$html .= hikashop_level($level) ? $link : '#';
		$html .= '"><table width="100%"><tr><td style="text-align: center;" width="120px">';
		$html .= '<span class="icon-48-'.$image.'" style="background-repeat:no-repeat;background-position:center;height:48px" title="'.$text.'"> </span>';
		$html .= '<span class="hikashop_cpanel_button_text">'.$text.'</span></td><td><div class="hikashop_cpanel_button_description">'.$description.'</div></td></tr></table></a>';
		$html .= '</div>';
		return $html;
	}
	function form(){
		$this->registration();
	}
	function registration(){
		$mainUser =& JFactory::getUser();
		$data = JRequest::getVar('main_user_data',null);
		if(!empty($data)){
			foreach($data as $key => $val){
				$mainUser->$key = $val;
			}
		}
		$this->assignRef('mainUser',$mainUser);
		$lang =& JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		$user_id = hikashop_loadUser();
		JHTML::_('behavior.formvalidation');
		$user = @$_SESSION['hikashop_user_data'];
		$address = @$_SESSION['hikashop_address_data'];
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;
		$extraFields['user'] = $fieldsClass->getFields('frontcomp',$user,'user');
		$extraFields['address'] = $fieldsClass->getFields('frontcomp',$address,'address');
		$this->assignRef('extraFields',$extraFields);
		$this->assignRef('user',$user);
		$this->assignRef('address',$address);
		$config =& hikashop_config();
		$simplified_reg = $config->get('simplified_registration',1);
		$this->assignRef('config',$config);
		$this->assignRef('simplified_registration',$simplified_reg);
		$values = array('address'=>$address,'user'=>$user);
		$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);
		$main = array('email');
		$main = array('name','username','email','password','password2');
		if($simplified_reg){
			$main = array('email');
		}
		foreach($main as $field){
			$this->requiredFields['register'][] = $field;
			$this->validMessages['register'][] = addslashes(JText::sprintf('FIELD_VALID',$fieldsClass->trans($field)));
		}
		$fieldsClass->addJS($this->requiredFields,$this->validMessages,array('register','user','address'));
		jimport('joomla.html.parameter');
		$params=new JParameter('');
		$class = hikashop_get('helper.cart');
		$this->assignRef('url_itemid',$url_itemid);
		$this->assignRef('params',$params);
		$this->assignRef('cartClass',$class);
		$affiliate = $config->get( 'affiliate_registration_default',0);
		if($affiliate){
			$affiliate = 'checked="checked"';
		}else{
			$affiliate = '';
		}
		$this->assignRef('affiliate_checked',$affiliate);
	}
}