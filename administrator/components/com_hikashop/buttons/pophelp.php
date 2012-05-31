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
class JButtonPophelp extends JButton
{
	var $_name = 'Pophelp';
	function fetchButton( $type='Pophelp', $namekey = '', $id = 'pophelp' )
	{
		$doc =& JFactory::getDocument();
		$config =& hikashop_config();
		$level = $config->get('level');
		$url = HIKASHOP_HELPURL.$namekey.'&level='.$level;
		$iFrame = "'<iframe src=\'$url\' width=\'100%\' height=\'100%\' scrolling=\'auto\'></iframe>'";
		$js = "
		var openHelp = true; 
		function displayDoc(){
			var box=document.getElementById('iframedoc');
			if(openHelp){
				box.innerHTML=".$iFrame.";
				box.setStyle('display','block');
			}
			try{			
				var fx = box.effects({duration: 1500, transition: Fx.Transitions.Quart.easeOut});
				if(openHelp){
					fx.start({'height': 300});
				}else{
					fx.start({'height': 0}).chain(function() {
						box.innerHTML='';
						box.setStyle('display','none');
					});
				} 
			}catch(err){
				var myVerticalSlide = new Fx.Slide('iframedoc');
				if(openHelp){
					myVerticalSlide.slideIn();
				}else{
					myVerticalSlide.slideOut().chain(function() {
						box.innerHTML='';
						box.setStyle('display','none');
					});
				}
			}
			openHelp = !openHelp;
		}";
		$doc->addScriptDeclaration( $js );
		return '<a href="'.$url.'" target="_blank" onclick="displayDoc();return false;" class="toolbar"><span class="icon-32-help" title="'.JText::_('HIKA_HELP',true).'"></span>'.JText::_('HIKA_HELP').'</a>';
	}
	function fetchId( $type='Pophelp', $html = '', $id = 'pophelp' )
	{
		return $this->_name.'-'.$id;
	}
}