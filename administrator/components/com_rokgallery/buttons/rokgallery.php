<?php
/**
  * @version   $Id: rokgallery.php 39428 2011-07-04 07:11:27Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JButtonRokGallery extends JButton {
	
	protected $_name = '';

	function fetchButton($type = 'rokgallery', $name = 'custom', $ref = '#', $classes = '', $rel = '', $lnkClass = '') {
		$this->_name = $name;

		$text  = JText::_(ucfirst($name));
		$class = $this->fetchIconClass($name);
		
		if (strlen($rel)) $rel = 'rel="'.$rel.'"';
		else $rel = '';
		
		if (strlen($lnkClass)) $lnkClass = 'class="'.$lnkClass.'"';
		else $lnkClass = '';

		$html  = "<a href=\"$ref\" $rel $lnkClass>\n";
		$html .= "<span class=\"$class\" title=\"$text\">\n";
		$html .= "</span>\n";
 		$html .= "$text\n";
		$html .= "</a>\n";

		return $html;
	}

	function fetchId($type, $name) {
		return 'toolbar-'.$name;
	}
	
	function render(&$definition){
		/*
		 * Initialize some variables
		 */
		$html	= null;
		$id		= call_user_func_array(array(&$this, 'fetchId'), $definition);
		$action	= call_user_func_array(array(&$this, 'fetchButton'), $definition);

		// Build id attribute
		if ($id) {
			$id = "id=\"$id\"";
		}
		
		$classes = isset($definition[3]) ? ' '.$definition[3] : '';

		// Build the HTML Button
		$html	.= "<li class=\"button$classes\" $id>\n";
		$html	.= $action;
		$html	.= "</li>\n";

		return $html;
	}

}
