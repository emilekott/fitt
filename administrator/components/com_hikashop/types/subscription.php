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
class hikashopSubscriptionType{
	function hikashopSubscriptionType(){
		$acl =& JFactory::getACL();
		if(version_compare(JVERSION,'1.6.0','<')){
			$this->groups = $acl->get_group_children_tree( null, 'USERS', false );
		}else{
			$db = JFactory::getDBO();
			$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
			$this->groups = $db->loadObjectList('id');
			foreach($this->groups as $id => $group){
				if(isset($this->groups[$group->parent_id])){
					$this->groups[$id]->level = intval(@$this->groups[$group->parent_id]->level) + 1;
					$this->groups[$id]->text = str_repeat('- - ',$this->groups[$id]->level).$this->groups[$id]->text;
				}
			}
		}
		$this->choice = array();
		$this->choice[] = JHTML::_('select.option','',JText::_('HIKA_NONE'));
		$this->choice[] = JHTML::_('select.option','special',JText::_('HIKA_CUSTOM'));
		$js = "function updateSubscription(map){
			choice = eval('document.adminForm.choice_'+map);
			choiceValue = 'special';
			for (var i=0; i < choice.length; i++){
			   if (choice[i].checked){
			     choiceValue = choice[i].value;
				}
			}
			hiddenVar = document.getElementById('hidden_'+map);
			if(choiceValue != 'special'){
				hiddenVar.value = choiceValue;
				document.getElementById('div_'+map).style.display = 'none';
			}else{
				document.getElementById('div_'+map).style.display = '';
				specialVar = eval('document.adminForm.special_'+map);
				finalValue = '';
				for (var i=0; i < specialVar.length; i++){
					if (specialVar[i].checked){
			     		finalValue += specialVar[i].value;
					}
				}
				hiddenVar.value = finalValue;
			}
		}";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}
	function display($map,$values,$type='discount'){
		$js ='window.addEvent(\'domready\', function(){ updateSubscription(\''.$map.'\'); });';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		if(empty($values)) $values = '';
		$choiceValue = ($values == '') ?  $values : 'special';
		$return = JHTML::_('select.radiolist',   $this->choice, "choice_".$map, 'onclick="updateSubscription(\''.$map.'\');"', 'value', 'text',$choiceValue);
		$return .= '<input type="hidden" name="data['.$type.']['.$map.']" id="hidden_'.$map.'" value="'.$values.'"/>';
		$valuesArray = explode(',',$values);
		$listAccess = '<div style="display:none" id="div_'.$map.'"><table>';
		foreach($this->groups as $oneGroup){
			$listAccess .= '<tr><td>';
			if(version_compare(JVERSION,'1.6.0','>=') || !in_array($oneGroup->value,array(29,30))) $listAccess .= '<input type="radio" onclick="updateSubscription(\''.$map.'\');" value="'.$oneGroup->value.'" '.(in_array($oneGroup->value,$valuesArray) ? 'checked' : '').' name="special_'.$map.'" id="special_'.$map.'_'.$oneGroup->value.'"/>';
			$listAccess .= '</td><td><label for="special_'.$map.'_'.$oneGroup->value.'">'.$oneGroup->text.'</label></td></tr>';
		}
		$listAccess .= '</table></div>';
		$return .= $listAccess;
		return $return;
	}
}