<?php
/**
 * RokGallery Macros RokGallery Macro Editor Plugin
 *
 * @package		Joomla
 * @subpackage	RokGallery Macros
 * @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author RocketTheme, LLC
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgButtonRokGallery extends JPlugin
{
	function onDisplay($name)
	{
		$button = new JObject();

		/* @var $doc JDocumentHTML */
		$doc = & JFactory::getDocument();
		
		$declaration	= 
		"function jSelectArticle(id, title, object) {
			var content 		= tinyMCE.getContent();
			
			var articlehref = 'index.php?option=com_content&view=article&id='+id;
			var articlelink = ' <a href=\"'+articlehref+'\">'+title+'</a> ';

			jInsertEditorText( articlelink, 'text' );
			document.getElementById('sbox-window').close();
		}
	";
		
		$doc->addScriptDeclaration($declaration);
		
		$declaration	="
		.button2-left .linkrokgallery { background: url(components/com_rokgallery/assets/images/rokgallery-button.png) 100% 0 no-repeat; } ";
		
		$doc->addStyleDeclaration($declaration);
		
		//$link = 'index.php?option=com_rokgallery&task=list&tmpl=component&object=id&textarea=' . $name;
		$link = 'index.php?option=com_rokgallery&view=gallerypicker&tmpl=component&textarea=' . $name;

		JHTML::_('behavior.modal');
		
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('RokGallery'));
		$button->set('name', 'linkrokgallery');
		$button->set('options', "{handler: 'iframe', size: {x: 675, y: 400}}");

		return $button;
	}
}