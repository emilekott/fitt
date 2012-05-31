<?php
/**
 * @package   Juxta Template - RocketTheme
 * @version   1.5.1 May 10, 2010
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Rockettheme Juxta Template uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

$gantry_config_mapping = array(
    'belatedPNG' => 'belatedPNG',
	'ie6Warning' => 'ie6Warning'
);

$gantry_presets = array (
    'presets' => array (
        'preset1' => array (
            'name' => 'Preset 1',
			'backgroundlevel' => 'high',
			'backgroundstyle' => 'style1',
			'bodylevel' => 'high',
            'cssstyle' => 'style1',
            'linkcolor' => '#660000',
            'font-family' => 'georgia'
        ),
        'preset2' => array (
            'name' => 'Preset 2',
			'backgroundlevel' => 'high',
			'backgroundstyle' => 'style2',
			'bodylevel' => 'high',
            'cssstyle' => 'style2',
            'linkcolor' => '#701110',
            'font-family' => 'georgia'
        ),
        'preset3' => array (
            'name' => 'Preset 3',
			'backgroundlevel' => 'high',
			'backgroundstyle' => 'style3',
			'bodylevel' => 'high',
            'cssstyle' => 'style3',
            'linkcolor' => '#701110',
            'font-family' => 'georgia'
        ),
        'preset4' => array (
            'name' => 'Preset 4',
			'backgroundlevel' => 'high',
			'backgroundstyle' => 'style4',
			'bodylevel' => 'high',
            'cssstyle' => 'style4',
            'linkcolor' => '#D76025',
            'font-family' => 'georgia'
        ),
        'preset5' => array (
            'name' => 'Preset 5',
			'backgroundlevel' => 'high',
			'backgroundstyle' => 'style5',
			'bodylevel' => 'high',
            'cssstyle' => 'style5',
            'linkcolor' => '#666600',
            'font-family' => 'georgia'
        ),
        'preset6' => array (
            'name' => 'Preset 6',
			'backgroundlevel' => 'high',
			'backgroundstyle' => 'style6',
			'bodylevel' => 'high',
            'cssstyle' => 'style6',
            'linkcolor' => '#6E4900',
            'font-family' => 'georgia'
        )
    )
);

$gantry_browser_params = array(
    'ie6' => array(
        'backgroundlevel' => 'low',
        'bodylevel' => 'low'
    )
);

$gantry_belatedPNG = array('.png', '#rt-logo','#rt-showcase', '#rt-showcase2', '.stuff', '#rt-mainbg-overlay', 'h2.title', '.rt-headline', '.feature-arrow-l', '.feature-arrow-r', '#rt-bottom', '.rt-main-inner', '.rokstories-tip','#rt-navigation', '#rt-navigation2', '#rt-navigation3', '.rt-surround', '.rt-surround2', '.rt-surround3', '.readon-main', '.rt-module-inner', '#rocket', '#gantry-logo', '.showcase-image', '.module-title', '.readon', '.readon span', '.rt-block', 'ul.bullet-e li', '.rt-joomla .icon', '.rt-pollbuttons', '.readon .button', '.roktabs-wrapper .roktabs-links ul li.active span', '#breadcrumbs-home', '.bullet-a li', '.bullet-d li', 'ul.level2 li', '.module-title .title', 'ul.menu li .item');

$gantry_ie6Warning = "<h3>IE6 DETECTED: Currently Running in Compatibility Mode</h3><h4>This site is compatible with IE6, however your experience will be enhanced with a newer browser</h4><p>Internet Explorer 6 was released in August of 2001, and the latest version of IE6 was released in August of 2004.  By continuing to run Internet Explorer 6 you are open to any and all security vulnerabilities discovered since that date.  In March of 2009, Microsoft released version 8 of Internet Explorer that, in addition to providing greater security, is faster and more standards compliant than both version 6 and 7 that came before it.</p> <br /><a class='external'  href='http://www.microsoft.com/windows/internet-explorer/?ocid=ie8_s_cfa09975-7416-49a5-9e3a-c7a290a656e2'>Download Internet Explorer 8 NOW!</a>";