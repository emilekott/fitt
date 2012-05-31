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
class hikashopCssType{
	var $type = 'component';
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '',JText::_('HIKA_NONE'));
		jimport('joomla.filesystem.folder');
		$regex = '^'.$this->type.'_([-_A-Za-z0-9]*)\.css$';
		$allCSSFiles = JFolder::files( HIKASHOP_MEDIA.'css', $regex );
		foreach($allCSSFiles as $oneFile){
			preg_match('#'.$regex.'#i',$oneFile,$results);
			$this->values[] = JHTML::_('select.option', $results[1],$results[1]);
		}
	}
	function display($map,$value){
		$this->load();
		$this->addJS();
		$js = ' onchange="updateCSSLink(\''.$this->type.'\',\''.$this->type.'\',this.value);"';
		$aStyle = empty($value) ? ' style="display:none"' : '';
		$html = JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'.$js, 'value', 'text', $value,$this->type.'_choice' );
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_config_manage','all'));
		if($manage){
			$linkEdit = 'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=config&amp;task=css&amp;file='.$this->type.'_'.$value.'&amp;var='.$this->type;
			$html .= '<a '.$aStyle.' id="'.$this->type.'_link" class="modal" title="'.JText::_('HIKA_EDIT',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img src="'.HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT',true).'"/></a>';
		}
		return $html;
	}
	function addJS(){
		static $done=false;
		if(!$done){
			$done=true;
			$js = "function updateCSSLink(myid,type,newval){
				if(newval){
					document.getElementById(myid+'_link').style.display = '';
				}else{
					document.getElementById(myid+'_link').style.display = 'none';
				}
				document.getElementById(myid+'_link').href = 'index.php?option=com_hikashop&tmpl=component&ctrl=config&task=css&file='+type+'_'+newval+'&var='+myid;
			}";
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
		}
	}
}