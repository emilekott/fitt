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
class ImportViewImport extends JView{
	var $ctrl= 'import';
	var $icon = 'generic';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function show(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$config =& hikashop_config();
		hikashop_setTitle(JText::_('IMPORT'),$this->icon,$this->ctrl.'&task=show');
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::custom('import', 'upload', '',JText::_('IMPORT'), false);
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl);
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$importData = array();
		$import = null;
		$import->text = JText::_('PRODUCTS_FROM_CSV');
		$import->key = 'file';
		$importData[] = $import;
		$textArea = null;
		$textArea->text = JText::_('PRODUCTS_FROM_TEXTAREA');
		$textArea->key = 'textarea';
		$importData[] = $textArea;
		$folder = null;
		$folder->text = JText::_('PRODUCTS_FROM_FOLDER');
		$folder->key = 'folder';
		$importData[] = $folder;
		$database =& JFactory::getDBO();
		$query='SHOW TABLES LIKE '.$database->Quote($database->getPrefix().substr(hikashop_table('vm_product',false),3));
		$database->setQuery($query);
		$table = $database->loadResult();
		if(!empty($table)){
			$vm_here = true;
		}else{
			$vm_here = false;
		}
		$this->assignRef('vm',$vm_here);
		$vm = null;
		$vm->text = JText::_('PRODUCTS_FROM_VM');
		$vm->key = 'vm';
		$importData[] = $vm;
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onDisplayImport', array( & $importData) );
		$this->assignRef('importData',$importData);
		$importValues = array();
		foreach($importData as $data){
			if(!empty($data->key)){
				$importValues[] = JHTML::_('select.option', $data->key,$data->text);
			}
		}
		$this->assignRef('importValues',$importValues);
		$importFolders = array(JHTML::_('select.option', 'images',JText::_('HIKA_IMAGES')),JHTML::_('select.option', 'files',JText::_('HIKA_FILES')));
		$this->assignRef('importFolders',$importFolders);
		$js = '
		var currentoption = \'file\';
		function updateImport(newoption){
			document.getElementById(currentoption).style.display = "none";
			document.getElementById(newoption).style.display = \'block\';
			currentoption = newoption;
		}
		var currentoptionFolder = \'images\';
		function updateImportFolder(newoption){
			document.getElementById(currentoptionFolder).style.display = "none";
			document.getElementById(newoption).style.display = \'block\';
			currentoptionFolder = newoption;
		}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		JHTML::_('behavior.modal');
	}
}