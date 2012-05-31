<?php
/**
 * @package		Gantry Template Framework - RocketTheme
 * @version		1.5.2 November 11, 2011
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

class GantryFeatureStyleDeclaration extends GantryFeature {
    var $_feature_name = 'styledeclaration';

    function isEnabled() {
        global $gantry;
        $menu_enabled = $this->get('enabled');

        if (1 == (int)$menu_enabled) return true;
        return false;
    }

	function init() {
        global $gantry;
        $browser = $gantry->browser;

		$this->_disableRokBoxForiPhone();

		// Style Inclusion
		$cssstyle = $gantry->get('cssstyle');
		$gantry->addStyle($cssstyle.".css");
		if ($gantry->get('typography-enabled')) $gantry->addStyle('typography.css');
		if ($gantry->get('extensions')) $gantry->addStyle('extensions.css');
		if ($gantry->get('extensions')) $gantry->addStyle('extensions-'.$cssstyle.'.css');
		if ($gantry->get('thirdparty')) $gantry->addStyle('thirdparty.css');

	}

	function _disableRokBoxForiPhone() {
		global $gantry;

		if ($gantry->browser->platform == 'iphone') {
			$gantry->addInlineScript("window.addEvent('domready', function() {\$\$('a[rel^=rokbox]').removeEvents('click');});");
		}
	}

}