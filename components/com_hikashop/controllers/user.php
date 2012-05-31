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
class userController extends hikashopController{
	var $delete = array();
	var $modify = array('register');
	var $modify_views = array();
	var $add = array();
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('cpanel');
		}
		$this->display[]='cpanel';
		$this->display[]='form';
	}
	function register(){
		$class = hikashop_get('class.user');
		$status = $class->register($this,'user');
		if($status){
			$app=&JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('THANK_YOU_FOR_REGISTERING',HIKASHOP_LIVE));
		}
		JRequest::setVar( 'layout', 'after_register'  );
		return parent::display();
	}
	function cpanel(){
		$user = JFactory::getUser();
		if ($user->guest) {
			$app=&JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(version_compare(JVERSION,'1.6','<')){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl())),false));
			return false;
		}
		JRequest::setVar( 'layout', 'cpanel'  );
		return parent::display();
	}
	function form(){
		$user = JFactory::getUser();
		if ($user->guest) {
			JRequest::setVar( 'layout', 'form'  );
			return $this->display();
		}else{
			$app=&JFactory::getApplication();
			$app->redirect(hikashop_completeLink('user&task=cpanel',false,true));
			return false;
		}
	}
}