<?php
 /**
  * @version   $Id: rokgallerybygallery.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

defined('_JEXEC' ) or die( 'Restricted access');
		
/**
 * @package     gantry
 * @subpackage  admin.elements
 */

class JFormFieldGallery extends JFormField
{
    protected static $js_loaded = false;
	protected $type = 'Gallery';
	
    public function __construct($parent = null)
    {
        $include_file = realpath(JPATH_SITE . '/components/com_rokgallery/include.php');
        $included_files = get_included_files();
        if (!in_array($include_file, $included_files) && ($libret = require_once($include_file)) !== 'JOOMLA_ROKGALLERY_LIB_INCLUDED') {
            JError::raiseWarning(100, 'RokGallery: ' . implode('<br /> - ', $loaderrors));
            return;
        }
        parent::__construct($parent);
    }

	protected function getInput()
	{
		
		global $mainframe;

		//$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		$fieldName	= $this->name;
		//$article =& JTable::getInstance('content');
		//if ($value) {
		//	$article->load($value);
		//} else {
		//	$article->title = JText::_('Select a Gallery');
		//}

		$js = "
		var RokGalleryPopup = RokGalleryID = null;
		var RokGalleryFixed = 0;
		window.addEvent('domready', function(){
			var mooVersion = MooTools.version;
			if (RokGalleryPopup) return;
			
			RokGalleryPopup = document.id('gallery-popup');
			RokGalleryID = document.id('".$this->id."_id');
			var titles = ['#title' , 'input[name=name]', '#jform_title', '#jform_title'],
				title = '',
				href = RokGalleryPopup.get('href');
			
			titles.each(function(selector){
				var element = document.getElement(selector); if (element) title = element;
			});
			
			RokGalleryPopup.addEvent('mouseover', function(){
				RokGalleryUpdateTitle(title, href);
			});
			
		});
		
		function RokGalleryUpdateTitle(title, href){
			title = title.get('value') || '';
			var args = '&id=' + RokGalleryID.get('value') + '&name=' + title + '&fixed=' + RokGalleryFixed + '&nocache=' + (Date.now());
			
			RokGalleryPopup.set('href', href + args);
		};";
		
		$css = ".sbox-content-iframe#sbox-content {overflow: hidden !important;}";
		
        if (!self::$js_loaded){
		    $doc->addScriptDeclaration($js);
			$doc->addStyleDeclaration($css);
            self::$js_loaded = true;
        }

		$link = 'index.php?option=com_rokgallery&view=gallerymanager&tmpl=component';



		JHTML::_('behavior.modal', 'a.modal');

        $gallery_name = '';
		$value = $this->value;
        if (!empty($value)){
            $gallery = RokGallery_Model_GalleryTable::getSingle((int)$this->value);
            if ($gallery === false) {
                $value = null;
            }
            else {
                $gallery_name = $gallery->name;
            }
        }
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$this->id.'_name" value="'.htmlspecialchars($gallery_name, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
//		$html .= "\n &nbsp; <input class=\"inputbox modal-button\" type=\"button\" value=\"".JText::_('Select')."\" />";
		$html .= '<div class="button2-left"><div class="blank"><a id="gallery-popup" class="modal" title="'.JText::_('Select a Gallery').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 420, y: 555}}">'.JText::_('Select').'</a></div></div>'."\n";
		$html .= "\n".'<input type="hidden" id="'.$this->id.'_id" name="'.$fieldName.'" value="'.$value.'" />';

		return $html;
	}
}
