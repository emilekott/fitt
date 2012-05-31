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
class hikashopLimitparentType{
	function load($type){
		$this->values = array();
		$fieldClass = hikashop_get('class.field');
		$fields = $fieldClass->getData('',$type);
		$this->values[] = JHTML::_('select.option', '',JText::_('HIKA_ALL'));
		foreach($fields as $field){
			$this->values[] = JHTML::_('select.option', $field->field_namekey,$field->field_realname);
		}
	}
	function display($map,$value,$type,$parent_value){
		$this->load($type);
		if(is_array($parent_value)){
			$parent_value=implode(',', $parent_value);
		}
		$url=hikashop_completeLink('field&task=parentfield&type='.$type.'&value='.$parent_value,true,true);
		$js ="
		function hikashopLoadParent(namekey){
			try{
				new Ajax('".$url."&namekey='+namekey, { method: 'get', onComplete: function(result) { old = window.document.getElementById('parent_value'); if(old){ old.innerHTML = result;}}}).request();
			}catch(err){
				new Request({url:'".$url."&namekey='+namekey, method: 'get', onComplete: function(result) { old = window.document.getElementById('parent_value'); if(old){ old.innerHTML = result;}}}).send();
			}
		}
		window.addEvent('domready', function(){
			hikashopLoadParent(document.getElementById('limit_parent_select').value);
		});
		";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onChange="hikashopLoadParent(this.value);"', 'value', 'text', $value, 'limit_parent_select' );
	}
}