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
class ExplorerViewExplorer extends JView{
	function display($tpl = null,$task,$defaultId,$popup,$type){
		$doc =& JFactory::getDocument();
		$doc->addScript(HIKASHOP_JS.'dtree.js');
		$doc->addStyleSheet(HIKASHOP_CSS.'dtree.css');
		$database	=& JFactory::getDBO();
		$translationHelper = hikashop_get('helper.translation');
		$select = 'SELECT a.*';
		$table=' FROM '.hikashop_table('category').' AS a';
		$app =& JFactory::getApplication();
		if($app->isAdmin() && $translationHelper->isMulti()){
			$user =& JFactory::getUser();
			$locale = $user->getParam('language');
			if(empty($locale)){
				$config =& JFactory::getConfig();
				$locale = $config->getValue('config.language');
			}
			$lgid = $translationHelper->getId($locale);
			$select .= ',b.value';
			$table .=' LEFT JOIN '.hikashop_table('jf_content',false).' AS b ON a.category_id=b.reference_id AND b.reference_table=\'hikashop_category\' AND b.reference_field=\'category_name\' AND b.published=1 AND language_id='.$lgid;
		}
		$where='';
		if(!empty($type)){
			$where = ' WHERE a.category_type IN ('.$database->Quote($type).',\'root\')';
		}
		$database->setQuery($select.$table.$where.' ORDER BY a.category_parent_id ASC, a.category_ordering ASC');
		$elements=$database->loadObjectList();
		$this->assignRef('elements', $elements);
		if(!is_numeric($defaultId)){
			$class = hikashop_get('class.category');
			$class->getMainElement($defaultId);
		}
		foreach($elements as $k => $element){
			if(empty($element->value)){
				$val = str_replace(' ','_',strtoupper($element->category_name));
				$element->value = JText::_($val);
				if($val==$element->value){
					$element->value = $element->category_name;
				}
			}	
			$elements[$k]->category_name = $element->value;
			if($element->category_namekey=='root'){
				if(empty($defaultId)){
					$defaultId=$element->category_id;
				}
				$elements[$k]->category_parent_id=-1;
			}
		}
		$this->assignRef('defaultId', $defaultId);
		$this->assignRef('popup', $popup);
		$this->assignRef('task', $task);
		$this->assignRef('type', $type);
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
}