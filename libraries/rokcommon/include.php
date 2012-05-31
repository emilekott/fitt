<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if (!defined('ROKCOMMON')) {
    define('ROKCOMMON', '1.6');
    define('ROKCOMMON_ROOT_PATH', dirname(__FILE__));
    if (($loaderrors = require_once(ROKCOMMON_ROOT_PATH . '/requirements.php')) !== true) {
        return $loaderrors;
    }

    // Bootstrap the base classloader
    require_once(ROKCOMMON_ROOT_PATH . '/RokCommon/ClassLoader.php');
    RokCommon_ClassLoader::addPath(ROKCOMMON_ROOT_PATH.'/Overrides');

    //Do base initialization
    RokCommon_I18N::initialize();
}
return "ROKCOMMON_LIB_INCLUDED";
