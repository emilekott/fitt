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
class ViewViewView extends JView{
	var $type = '';
	var $ctrl= 'view';
	var $nameListing = 'VIEWS';
	var $nameForm = 'VIEWS';
	var $icon = 'view';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function getName(){
		return 'view';
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo=null;
		$pageInfo->filter->client_id=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.client_id','client_id',2 ,'int');
		$pageInfo->filter->template=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.template','template','' ,'string');
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.limit', 'limit', $app->getCfg('list_limit'), 'int');
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$views = array();
		switch($pageInfo->filter->client_id){
			case 0:
				$views[0] = HIKASHOP_FRONT.'views'.DS;
				break;
			case 1:
				$views[1] = HIKASHOP_BACK.'views'.DS;
				break;
			default:
				$views[0] = HIKASHOP_FRONT.'views'.DS;
				$views[1] = HIKASHOP_BACK.'views'.DS;
				break;
		}
		jimport('joomla.filesystem.folder');
		if(version_compare(JVERSION,'1.6','<')){
			require_once (rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_templates'.DS.'helpers'.DS.'template.php');
		}
		$templates = array();
		$templateValues = array();
		foreach($views as $client_id => $view){
			$folders = JFolder::folders($view);
			if(!empty($folders)){
				$clientTemplates = array();
				foreach($folders as $folder){
					if(JFolder::exists($view.$folder.DS.'tmpl')){
						$files = Jfolder::files($view.$folder.DS.'tmpl');
						if(!empty($files)){
							foreach($files as $file){
								if(substr($file,-4)=='.php'){
									$obj = null;
									$obj->path = $view.$folder.DS.'tmpl'.DS.$file;
									$obj->filename = $file;
									$obj->folder = $view.$folder.DS.'tmpl'.DS;
									$obj->client_id = $client_id;
									$obj->view = $folder;
									$obj->type = 'component';
									$obj->type_name = 'com_hikashop';
									$obj->file = substr($file,0,strlen($file)-4);
									$clientTemplates[]=$obj;
								}
							}
						}
					}
				}
				if($client_id==0){
					$plugins_folder = rtrim(JPATH_PLUGINS,DS).DS.'hikashoppayment';
					if(Jfolder::exists($plugins_folder)){
						$files = Jfolder::files($plugins_folder);
						foreach($files as $file){
							if(preg_match('#^.*_(?!configuration).*\.php$#',$file)){
								$obj = null;
								$obj->path = $plugins_folder.DS.$file;
								$obj->filename = $file;
								$obj->folder = $plugins_folder;
								$obj->client_id = $client_id;
								$obj->type = 'plugin';
								$obj->view = '';
								$obj->type_name = 'hikashoppayment';
								$obj->file = substr($file,0,strlen($file)-4);
								$clientTemplates[]=$obj;
							}
						}
					}
				}
				if(!empty($clientTemplates)){
					$client	=& JApplicationHelper::getClientInfo($client_id);
					$tBaseDir = $client->path.DS.'templates';
					if(version_compare(JVERSION,'1.6','<')){
						$joomlaTemplates = TemplatesHelper::parseXMLTemplateFiles($tBaseDir);
					}else{
						$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE type=\'template\'';
						$db =& JFactory::getDBO();
						$db->setQuery($query);
						$joomlaTemplates = $db->loadObjectList();
						foreach($joomlaTemplates as $k => $v){
							$joomlaTemplates[$k]->assigned = $joomlaTemplates[$k]->protected;
							$joomlaTemplates[$k]->published = $joomlaTemplates[$k]->enabled;
							$joomlaTemplates[$k]->directory = $joomlaTemplates[$k]->element;
						}
					}
					for($i = 0; $i < count($joomlaTemplates); $i++)  {
						if(version_compare(JVERSION,'1.6','<')){
							$joomlaTemplates[$i]->assigned = TemplatesHelper::isTemplateAssigned($joomlaTemplates[$i]->directory);
							$joomlaTemplates[$i]->published = TemplatesHelper::isTemplateDefault($joomlaTemplates[$i]->directory, $client->id);
						}
						if($joomlaTemplates[$i]->published || $joomlaTemplates[$i]->assigned){
							if(!empty($pageInfo->filter->template) && $joomlaTemplates[$i]->directory!=$pageInfo->filter->template){
								continue;
							}
							$templateValues[$joomlaTemplates[$i]->directory]=$joomlaTemplates[$i]->directory;
							$templateFolder = $tBaseDir.DS.$joomlaTemplates[$i]->directory.DS;
							foreach($clientTemplates as $template){
								$templatePerJoomlaTemplate = clone($template);
								$templatePerJoomlaTemplate->template = $joomlaTemplates[$i]->directory;
								$templatePerJoomlaTemplate->override = $templateFolder.'html'.DS.$template->type_name.DS;
								if($template->type=='component'){
									$templatePerJoomlaTemplate->override .= $template->view.DS;
								}
								$templatePerJoomlaTemplate->override .= $template->filename;
								$templatePerJoomlaTemplate->overriden=false;
								if(file_exists($templatePerJoomlaTemplate->override)){
									$templatePerJoomlaTemplate->overriden=true;
								}
								$templatePerJoomlaTemplate->id = $templatePerJoomlaTemplate->client_id.'|'.$templatePerJoomlaTemplate->template .'|'. $templatePerJoomlaTemplate->type.'|'. $templatePerJoomlaTemplate->type_name.'|'. $templatePerJoomlaTemplate->view.'|'.$templatePerJoomlaTemplate->filename;
								$templates[]=$templatePerJoomlaTemplate;
							}
						}
					}
				}
			}
		}
		$searchMap = array('filename','view','template');
		if(!empty($pageInfo->search)){
			$unset = array();
			foreach($templates as $k => $template){
				$found = false;
				foreach($searchMap as $field){
					if(strpos($template->$field,$pageInfo->search)!==false){
						$found=true;
					}
				}
				if(!$found){
					$unset[]=$k;
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($templates[$u]);
				}
			}
			$templates = hikashop_search($pageInfo->search,$templates,'id');
		}
		jimport('joomla.html.pagination');
		$pageInfo->elements->total = count($templates);
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$templates = array_slice($templates, $pagination->limitstart, $pagination->limit);
		$pageInfo->elements->page = count($templates);
		$this->assignRef('rows',$templates);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('templateValues',$templateValues);
		$viewType = hikashop_get('type.view');
		$this->assignRef('viewType',$viewType);
		$templateType = hikashop_get('type.template');
		$this->assignRef('templateType',$templateType);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_view_manage','all'));
		$this->assignRef('manage',$manage);
		$delete = hikashop_isAllowed($config->get('acl_view_delete','all'));
		$this->assignRef('delete',$delete);
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
	}
	function form(){
		$id = JRequest::getString('id','');
		$viewClass = hikashop_get('class.view');
		$obj = $viewClass->get($id);
		$bar = & JToolBar::getInstance('toolbar');
		if($obj){
			jimport('joomla.filesystem.file');
			$obj->content = htmlspecialchars(JFile::read($obj->edit), ENT_COMPAT, 'UTF-8');
			$bar = & JToolBar::getInstance('toolbar');
		}
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task=edit&id='.$id);
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
		$this->assignRef('element',$obj);
		$editor = hikashop_get('helper.editor');
		$this->assignRef('editor',$editor);
	}
}