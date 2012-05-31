<?php
/**
 * RokCandy Macros RokCandy Macro Categories
 *
 * @package		Joomla
 * @subpackage	RokCandy Macros
 * @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author RocketTheme, LLC
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementRokCandyList
{
   	function getCategories( $name, $active = NULL, $javascript = NULL, $order = 'lft', $size = 1, $sel_cat = 1 )
	{
		$db =& JFactory::getDBO();
        $extension = JApplicationHelper::getComponentName();

		$query = 'SELECT id AS value, title AS text'
		. ' FROM #__categories'
		. ' WHERE extension = '.$db->Quote($extension)
		. ' AND published = 1'
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query );
		
		if ( $sel_cat and $name!='catid') {
			$categories[] = JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Category' ) .' -' );
			$categories[] = JHTML::_('select.option', '-1', 'Template Overrides');
			$categories = array_merge( $categories, $db->loadObjectList() );
		} else {
			$categories = $db->loadObjectList();
		}

		$category = JHTML::_('select.genericlist',   $categories, $name, 'class="inputbox" size="'. $size .'" '. $javascript, 'value', 'text', $active );
		return $category;
	}
}