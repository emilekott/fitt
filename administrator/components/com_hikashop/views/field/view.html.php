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
class FieldViewField extends JView{
	function display($tpl = null){
		$function = $this->getLayout();
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function form(){
		$fieldid = hikashop_getCID('field_id');
		$fieldsClass = hikashop_get('class.field');
		if(!empty($fieldid)){
			$field = $fieldsClass->getField($fieldid);
		}else{
			$app =& JFactory::getApplication();
			$field = null;
			if(hikashop_level(1)){
				$field->field_table = $app->getUserStateFromRequest( $this->paramBase.".filter_table",'filter_table','product','string');
			}else{
				$field->field_table = 'address';
			}
			$field->field_published = 1;
			$field->field_type = 'text';
			$field->field_backend = 1;
		}
		if(!empty($field->field_id)) $fieldTitle = ' : '.$field->field_namekey;
		else $fieldTitle = '';
		hikashop_setTitle(JText::_('FIELD').$fieldTitle,'field','field&task=edit&field_id='.$fieldid);
		$script = 'function addLine(){
			var myTable=window.document.getElementById("tablevalues");
			var newline = document.createElement(\'tr\');
			var column = document.createElement(\'td\');
			var column2 = document.createElement(\'td\');
			var column3 = document.createElement(\'td\');
			var input = document.createElement(\'input\');
			var input2 = document.createElement(\'input\');
			var input3 = document.createElement(\'select\');
			var option1 = document.createElement(\'option\');
			var option2 = document.createElement(\'option\');
			input.type = \'text\';
			input2.type = \'text\';
			option1.value= \'0\';
			option2.value= \'1\';
			input.name = \'field_values[title][]\';
			input2.name = \'field_values[value][]\';
			input3.name = \'field_values[disabled][]\';
			option1.text= \''.JText::_('HIKASHOP_NO',true).'\';
			option2.text= \''.JText::_('HIKASHOP_YES',true).'\';
			try { input3.add(option1, null); } catch(ex) { input3.add(option1); }
			try { input3.add(option2, null); } catch(ex) { input3.add(option2); }
			column.appendChild(input);
			column2.appendChild(input2);
			column3.appendChild(input3);
			newline.appendChild(column);
			newline.appendChild(column2);
			newline.appendChild(column3);
			myTable.appendChild(newline);
		}
		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display="none";
			}
			return false;
		}
		function setVisible(value){
			if(value=="product" || value=="item" || value=="category"){
				document.getElementById(\'category_field\').style.display = "";
			}else{
				document.getElementById(\'category_field\').style.display = \'none\';
			}
		}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $script);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','field-form');
		$this->assignRef('fieldtype',hikashop_get('type.fields'));
		$this->assignRef('field',$field);
		$this->assignRef('fieldsClass',$fieldsClass);
		if(hikashop_level(1)){
			$this->assignRef('tabletype',hikashop_get('type.table'));
		}
		$this->assignRef('zoneType',hikashop_get('type.zone'));
		$this->assignRef('allowType',hikashop_get('type.allow'));
		if(hikashop_level(2)){
			$this->assignRef('limitParent',hikashop_get('type.limitparent'));
			if(!empty($field->field_options['product_id'])){
				$product = hikashop_get('class.product');
				$element = $product->get($field->field_options['product_id']);
				$this->assignRef('element',$element);
			}
		}
		$categories=array();
		if(isset($this->field->field_categories)){
			$this->field->field_categories=$this->field->field_categories;
			$this->categories= explode(",", $this->field->field_categories);
			unset($this->categories[0]);
			unset($this->categories[count($this->categories)]);
			if(!empty($this->categories)){
				foreach($this->categories as $k => $cat){
					$categories[$k]->category_id=$cat;
				}
				$db =& JFactory::getDBO();
				$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$this->categories).')');
				$cats = $db->loadObjectList('category_id');
				foreach($this->categories as $k => $cat){
					if(!empty($cats[$cat])){
						$categories[$k]->category_name = $cats[$cat]->category_name;
					}else{
						$categories[$k]->category_name = JText::_('CATEGORY_NOT_FOUND');
					}
				}
			}
			$this->categories=$categories;
		}
		JHTML::_('behavior.modal');
	}
	function listing(){
		$db =& JFactory::getDBO();
		$filter = '';
		if(hikashop_level(1)){
			$app =& JFactory::getApplication();
			$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_table",'filter_table','','string');
			if(!empty($selectedType)){
				$filter = ' WHERE a.field_table='.$db->Quote($selectedType);
			}
			$this->assignRef('tabletype',hikashop_get('type.table'));
		}else{
			$filter = ' WHERE a.field_table=\'address\'';
		}
		$db->setQuery('SELECT a.* FROM '.hikashop_table('field').' AS a'.$filter.' ORDER BY a.`field_table` ASC, a.`field_ordering` ASC');
		$rows = $db->loadObjectList();
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_field_manage','all'));
		$this->assignRef('manage',$manage);
		if($manage){
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_field_delete','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','field-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		jimport('joomla.html.pagination');
		$total = count($rows);
		$pagination = new JPagination($total, 0,$total);
		hikashop_setTitle(JText::_('FIELDS'),'field','field');
		$this->assignRef('rows',$rows);
		$this->assignRef('toggleClass',hikashop_get('helper.toggle'));
		$this->assignRef('pagination',$pagination);
		$this->assignRef('selectedType',$selectedType);
		$type = hikashop_get('type.fields');
		$type->load();
		$this->assignRef('fieldtype',$type);
		$this->assignRef('fieldsClass',hikashop_get('class.field'));
	}
	function state(){
		$namekey = JRequest::getCmd('namekey','');
		if(!empty($namekey)){
			$class = hikashop_get('type.country');
			$class->type = 'state';
			$class->published = true;
			$class->country_name = $namekey;
			$states = $class->load();
			if(!empty($states)){
				$obj = null;
				$obj->suffix = '';
				$obj->prefix = '';
				$obj->excludeValue = array();
				$fieldClass = hikashop_get('class.field');
				$dropdown = new hikashopSingledropdown($obj);
				$field = null;
				$field->field_namekey = 'address_state';
				$statesArray=array();
				foreach($states as $state){
					$title = $state->zone_name_english;
					if($state->zone_name_english != $state->zone_name){
						$title.=' ('.$state->zone_name.')';
					}
					$obj = null;
					$obj->disabled = '0';
					$obj->value = $title;
					$statesArray[$state->zone_namekey]=$obj;
				}
				$field->field_value = $statesArray;
				echo $dropdown->display($field,'','data[address][address_state]','','');
			}
		}
		exit;
	}
}