<?php
 /**
  * @version   $Id: mod_rokgallery.php 39676 2011-07-07 11:21:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('ROKGALLERYMODULE')) define('ROKGALLERYMODULE','ROKGALLERYMODULE');
if (!defined('ROKGALLERYMODULE_ROOT')) define('ROKGALLERYMODULE_ROOT', dirname(__FILE__));

$include_file = @realpath(ROKGALLERYMODULE_ROOT . '/include.php');
$included_files = get_included_files();
if (defined('ROKGALLERY_ERROR_MISSING_LIBS')) return;
if (!in_array($include_file, $included_files) && ($loaderrors = require_once($include_file)) !== 'JOOMLA_ROKGALLERYMODULE_LIB_LOADED') {
    JError::raiseWarning(100, 'RokGallery Module: ' . implode('<br /> - ', $loaderrors));
    return;
}

JHTML::_('behavior.mootools');
$doc = &JFactory::getDocument();

$gallery = RokGallery_Model_GalleryTable::getSingle($params->get('gallery_id',0));
if ($gallery == false)
{
    rc_e('ROKGALLERY_NO_GALLERY_DEFINED', $module->title, $module->id);
    return;
}


$passed_params = new stdClass();
$passed_params->link = $params->get('link', 0);
$passed_params->title = $params->get('title', false);
$passed_params->caption = $params->get('caption', 0); 
$passed_params->arrows = $params->get('arrows', 'onhover');
$passed_params->navigation = $params->get('navigation', 'none');
$passed_params->image_width = str_replace('px', '', $gallery->width);
$passed_params->image_height = str_replace('px', '', $gallery->height);
$passed_params->thumb_width = str_replace('px', '', $gallery->thumb_xsize);
$passed_params->thumb_height = str_replace('px', '', $gallery->thumb_ysize);
$passed_params->columns = $params->get('columns', 2);
$passed_params->layout = $params->get('layout', 'grid');
$passed_params->style = $params->get('style', 'light');
$passed_params->animation_type = $params->get('animation_type', 'random');
$passed_params->animation_duration = $params->get('animation_duration', 500);
$passed_params->autoplay_enabled = (int)$params->get('autoplay_enabled', 0);
$passed_params->autoplay_delay = $params->get('autoplay_delay', 7) * 1000;

$passed_params->showcase_arrows = $params->get('showcase_arrows', 'onhover');
$passed_params->showcase_image_position = $params->get('showcase_image_position', 'left');
$passed_params->showcase_navigation = $params->get('showcase_navigation', 'thumbnails');
$passed_params->showcase_imgpadding = $params->get('showcase_imgpadding', 0);
$passed_params->showcase_fixedheight = $params->get('showcase_fixedheight', 0);
$passed_params->showcase_animatedheight = $params->get('showcase_animatedheight', 1);
$passed_params->showcase_animation_type = $params->get('showcase_animation_type', 'random');
$passed_params->showcase_animation_duration = $params->get('showcase_animation_duration', 500);
$passed_params->showcase_autoplay_enabled = (int)$params->get('showcase_autoplay_enabled', 0);
$passed_params->showcase_autoplay_delay = $params->get('showcase_autoplay_delay', 7) * 1000;
$passed_params->showcase_captionsanimation = $params->get('showcase_captionsanimation', 'crossfade');
$passed_params->moduleid = $module->id;


$passed_params->layout_context = 'mod_rokgallery.'.$passed_params->layout;
$passed_params->style_context = $passed_params->layout_context.'.'.$passed_params->style;

$request_var_root = 'mod_rokgallery.'.$passed_params->layout;
$request_var_css = $request_var_root.'.css';
$request_var_js = $request_var_root.'.js';

if (!RokCommon_Request::get($request_var_css, false)) {
    $doc = &JFactory::getDocument();
    if (file_exists(RokCommon_Composite::get($passed_params->layout_context)->get($passed_params->layout . '.css')))
    {
        $doc->addStyleSheet(RokCommon_Composite::get($passed_params->layout_context)->getURL($passed_params->layout . '.css'));
    }
    if (file_exists(RokCommon_Composite::get($passed_params->style_context)->get('style.css')))
    {
        $doc->addStyleSheet(RokCommon_Composite::get($passed_params->style_context)->getURL('style.css'));
    }
    RokCommon_Request::set($request_var_css, true);
}

if (!RokCommon_Request::get($request_var_js, false)) {
    $doc = &JFactory::getDocument();
    if (file_exists(RokCommon_Composite::get($passed_params->layout_context)->get($passed_params->layout.RokGallery_Helper::getJSVersion().'.js')))
    {
        $doc->addScript(RokCommon_Composite::get($passed_params->layout_context)->getURL($passed_params->layout.RokGallery_Helper::getJSVersion().'.js'));
    }
    RokCommon_Request::set($request_var_js, true);
}

if (file_exists(RokCommon_Composite::get($passed_params->layout_context)->get('javascript.php'))){
    $doc->addScriptDeclaration(RokCommon_Composite::get($passed_params->layout_context)->load('javascript.php',array('passed_params'=>$passed_params)));
}

$rokgallerymodule = new RokGalleryModule();
$passed_params->slices = $rokgallerymodule->getSlices($params);

require(JModuleHelper::getLayoutPath('mod_rokgallery'));
