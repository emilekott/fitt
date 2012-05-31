<?php
 /**
  * @version   $Id: rokgallerystyles.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

defined('_JEXEC' ) or die( 'Restricted access');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldLayout extends JFormField
{
	protected  $type = 'Layout';
	protected static $js_loaded = false;

	protected function getInput() 
	{

    	$doc =& JFactory::getDocument();
		$js = "window.addEvent('domready', function() {
			var str = '".$this->value."';
			$$('.layout').getParent('li').setStyle('display','none');
			$$('.'+str).getParent('li').setStyle('display','block');
			$('".$this->id."').addEvent('change', function(){
				var sel = document.id(this.options[this.selectedIndex]).get('value'),
					rel = document.id(this.options[this.selectedIndex]).get('rel');
					
				RokGalleryFixed = rel == 'false' ? false : true;
				$$('.layout').getParent('li').setStyle('display','none');
				$$('.'+sel).getParent('li').setStyle('display','block');
			}).fireEvent('change');
		});";
		
		if (!self::$js_loaded){
			$doc->addScriptDeclaration($js);
			self::$js_loaded = true;
		}
		
				
		$list = $options = '';
		
		foreach($this->element->children() as $option){
			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}
			
			$val = $class = (string)$option['value'];
			$fixed = (string)$option['fixed'];
			$text = $option->data();
			
			if ($this->value == $val) $selected = ' selected="selected"';
			else $selected = "";
			
			$options .= '<option value="'.$val.'" class="'.$class.'" rel="'.$fixed.'"'.$selected.'>'.JText::_($text).'</option>';
		}
		
		$list = '<select id="'.$this->id.'" class="inputbox" name="'.$this->name.'">'.$options.'</select>';
				
		return $list;
		
	}

}