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
class hikashopCategorysubType{
	var $type='tax';
	var $value='';
	function load($form=true){
		static $data = array();
		if(!isset($data[$this->type])){
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE  category_parent_id=0 LIMIT 1';
			$db =& JFactory::getDBO();
			$db->setQuery($query);
			$parent = (int)$db->loadResult();
			$select = 'SELECT a.category_name,a.category_id,a.category_namekey';
			$table = ' FROM '.hikashop_table('category') . ' AS a';
			$app = JFactory::getApplication();
			$translationHelper = hikashop_get('helper.translation');
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
			$query = $select.$table;
			$query .= ' WHERE  a.category_type = \''.$this->type.'\' AND a.category_parent_id!='.$parent.' ORDER BY a.category_ordering ASC';
			if(!$app->isAdmin() && $translationHelper->isMulti(true) && class_exists('JFDatabase')){
				$db->setQuery($query);
				$this->categories = $db->loadObjectList('',false);
			}else{
				$db->setQuery($query);
				$this->categories = $db->loadObjectList();
			}
			$data[$this->type] =& $this->categories;
		}else{
			$this->categories =& $data[$this->type];
		}
		$this->values = array();
		if($form){
			if(in_array($this->type,array('status','tax'))){
				$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_NONE') );
			}else{
				$this->values[] = JHTML::_('select.option', 0, JText::_('HIKA_NONE') );
			}
		}else{
			if($this->type=='status'){
				$this->values[] = JHTML::_('select.option', '', JText::_('ALL_STATUSES') );
			}else{
				$this->values[] = JHTML::_('select.option', 0, JText::_('ALL_'.strtoupper($this->type)) );
			}
		}
		if(!empty($this->categories)){
			foreach($this->categories as $k => $category){
				if(empty($category->value)){
					$val = str_replace(' ','_',strtoupper($category->category_name));
					$category->value = JText::_($val);
					if($val==$category->value){
						$category->value = $category->category_name;
					}
					$this->categories[$k]->value = $category->value;
				}
				if($this->type=='status'){
					$this->values[] = JHTML::_('select.option', $category->category_name, $category->value );
				}elseif($this->type=='tax'){
					$field = $this->field;
					$this->values[] = JHTML::_('select.option', $category->$field, $category->value );
				}else{
					$this->values[] = JHTML::_('select.option', (int)$category->category_id, $category->value );
				}
			}
		}
	}
	function trans($status){
		foreach($this->categories as $value){
			if($value->category_name == $status){
				return $value->value;
			}
		}
		return $status;
	}
	function get($val){
		foreach($this->values as $value){
			if($value->value == $val){
				return $value->text;
			}
		}
		return $val;
	}
	function display($map,$value,$form=true,$none=true){
		$this->value = $value;
		if(!is_bool($form)){
			$attribute = $form;
			$form = $none;
		}elseif(!$form){
			$attribute = ' onchange="document.adminForm.submit();"';
		}else{
			$attribute = '';
		}
		$this->load($form);
		if(!in_array($this->type,array('status','tax'))){
			$value = (int)$value;
		}
		if(strpos($attribute,'size="')===false){
			$attribute.=' size="1"';
		}
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox"'.$attribute, 'value', 'text', $value );
	}
}