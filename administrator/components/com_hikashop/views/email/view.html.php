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
class EmailViewEmail extends JView
{
	var $type = '';
	var $ctrl= 'email';
	var $nameListing = 'EMAILS';
	var $nameForm = 'EMAILS';
	var $icon = 'inbox';
	function display($tpl = null)
	{
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		return parent::display($tpl);
	}
	function form(){
		$mail_name = JRequest::getString('mail_name');
		$mailClass = hikashop_get('class.mail');
		$data = true;
		$mail = $mailClass->get($mail_name,$data);
		if(empty($mail)){
			$config =& hikashop_config();
			$mail->from_name = $config->get('from_name');
			$mail->from_email = $config->get('from_email');
			$mail->reply_name = $config->get('reply_name');
			$mail->reply_email = $config->get('reply_email');
			$mail->subject = '';
			$mail->html = 1;
			$mail->published = 1;
			$mail->body = '';
			$mail->altbody = '';
			$mail->mail = $mail_name;
		};
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		$values = null;
		$values->maxupload = (hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');
		$toggleClass = hikashop_get('helper.toggle');
		$js = "function updateEditor(htmlvalue){";
			$js .= 'if(htmlvalue == \'0\'){window.document.getElementById("htmlfieldset").style.display = \'none\'}else{window.document.getElementById("htmlfieldset").style.display = \'block\'}';
		$js .= '}';
		$js .='window.addEvent(\'load\', function(){ updateEditor('.$mail->html.'); });';
		$script = 'function addFileLoader(){
		var divfile=window.document.getElementById("loadfile");
		var input = document.createElement(\'input\');
		input.type = \'file\';
		input.size = \'30\';
		input.name = \'attachments[]\';
		divfile.appendChild(document.createElement(\'br\'));
		divfile.appendChild(input);}
		';
		$script .= 'function submitbutton(pressbutton){
						if (pressbutton == \'cancel\') {
							submitform( pressbutton );
							return;
						}';
		$script .= 'if(window.document.getElementById("subject").value.length < 2){alert(\''.JText::_('ENTER_SUBJECT',true).'\'); return false;}';
		$script .= 'submitform( pressbutton );}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js.$script );
		if(JRequest::getString('tmpl')!='component'){
			$bar = & JToolBar::getInstance('toolbar');
			JToolBarHelper::save();
			JToolBarHelper::apply();
			JToolBarHelper::cancel();
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
			hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task=edit&mail_name='.$mail_name);
		}
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('values',$values);
		$this->assignRef('mail_name',$mail_name);
		$this->assignRef('mail',$mail);
		$this->assignRef('tabs',$tabs);
		$editor = hikashop_get('helper.editor');
		$this->assignRef('editor',$editor);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$config =& hikashop_config();
		$pageInfo=null;
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		jimport('joomla.filesystem.file');
		$folder = HIKASHOP_MEDIA.'mail'.DS;
		$files = array('cron_report','order_admin_notification','order_creation_notification','order_status_notification','order_notification','user_account','out_of_stock','order_cancel','waitlist_notification');
		$emails = array();
		foreach($files as $file){
			$email = null;
			$email->file = $file;
			$email->overriden_text = JFile::exists($folder.$file.'.text.modified.php');
			$email->overriden_html = JFile::exists($folder.$file.'.html.modified.php');
			$email->published = $config->get($file.'.published');
			$emails[]=$email;
		}
		jimport('joomla.html.pagination');
		$pageInfo->elements->total = count($emails);
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$emails = array_slice($emails, $pagination->limitstart, $pagination->limit);
		$pageInfo->elements->page = count($emails);
		$this->assignRef('rows',$emails);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		$manage = hikashop_isAllowed($config->get('acl_email_manage','all'));
		$this->assignRef('manage',$manage);
		$delete = hikashop_isAllowed($config->get('acl_email_delete','all'));
		$this->assignRef('delete',$delete);
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
		$this->assignRef('toggleClass',hikashop_get('helper.toggle'));
	}
}
