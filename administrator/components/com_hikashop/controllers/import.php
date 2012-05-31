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
class ImportController extends hikashopController{
	var $type='import';
	function __construct(){
		parent::__construct();
		$this->modify[]='import';
		$this->registerDefaultTask('show');
		$this->helper = hikashop_get('helper.import');
	}
	function import(){
		JRequest::checkToken('request') or die( 'Invalid Token' );
		$function = JRequest::getCmd('importfrom');
		$this->helper->addTemplate(JRequest::getInt('template_product',0));
		switch($function){
			case 'file':
				$this->_file();
				break;
			case 'textarea':
				$this->_textarea();
				break;
			case 'folder':
				if(hikashop_level(2)){
					$this->_folder();
				}else{
					$app =& JFactory::getApplication();
					$app->enqueueMessage(Text::_('ONLY_FROM_BUSINESS'),'error');
				}
				break;
			case 'vm':
				$database =& JFactory::getDBO();
				$query='SHOW TABLES LIKE '.$database->Quote($database->getPrefix().substr(hikashop_table('vm_product',false),3));
				$database->setQuery($query);
				$table = $database->loadResult();
				if(!empty($table)){
					$this->_vm();
				}else{
					$app =& JFactory::getApplication();
					$app->enqueueMessage('VirtueMart has not been found in the database','error');
				}
				break;
			default:
				$plugin = hikashop_import('hikashop',$function);
				if($plugin){
					$plugin->onImportRun();
				}
				break;
		}
		return $this->show();
	}
	function _vm(){
		return $this->helper->importFromVM();
	}
	function _textarea(){
		$content = JRequest::getVar('textareaentries','','','string',JREQUEST_ALLOWRAW);
		$this->helper->overwrite = JRequest::getInt('textarea_update_products');
		$this->helper->createCategories = JRequest::getInt('textarea_create_categories');
		$this->helper->force_published = JRequest::getInt('textarea_force_publish');
		return $this->helper->handleContent($content);
	}
	function _folder(){
		$type = JRequest::getCmd('importfolderfrom');
		$delete = JRequest::getInt('delete_files_automatically');
		$uploadFolder = JRequest::getVar($type.'_folder','');
		return $this->helper->importFromFolder($type,$delete,$uploadFolder);
	}
	function _file(){
		$importFile =  JRequest::getVar( 'importfile', array(), 'files','array');
		$this->helper->overwrite = JRequest::getInt('file_update_products');
		$this->helper->createCategories = JRequest::getInt('file_create_categories');
		$this->helper->force_published = JRequest::getInt('file_force_publish');
		return $this->helper->importFromFile($importFile);
	}
}