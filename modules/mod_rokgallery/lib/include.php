<?php
 /**
 * @version   $Id: include.php 39393 2011-07-03 02:59:03Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if (!defined('ROKGALLERYMODULE_LIB')) {
    define('ROKGALLERYMODULE_LIB','ROKGALLERYMODULE_LIB');

    if (!defined('ROKCOMMON_LIB_PATH')) define('ROKCOMMON_LIB_PATH', JPATH_SITE . '/libraries/rokcommon');

    $rokgallery_lib_path = JPATH_SITE . '/components/com_rokgallery/lib';
    $include_file = @realpath($rokgallery_lib_path . '/include.php');
    $included_files = get_included_files();
    if (!in_array($include_file, $included_files) && ($loaderrors = require_once(realpath($rokgallery_lib_path . '/include.php'))) !== 'ROKGALLERY_LIB_INCLUDED') {
        return $loaderrors;
    }

     //Do base initialization
    RokCommon_ClassLoader::addPath(dirname(__FILE__));
}
return "ROKGALLERYMODULE_LIB_INCLUDED";