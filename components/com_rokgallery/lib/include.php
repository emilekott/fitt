<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if (!defined('ROKGALLERY_LIB')) {
    define('ROKGALLERY_LIB', 'ROKGALLERY_LIB');

    if (!defined('ROKCOMMON_LIB_PATH')) {
        if (!defined('ROKGALLERY_ERROR_MISSING_LIBS')) define('ROKGALLERY_ERROR_MISSING_LIBS', true);
        return array('ROKCOMMON_LIB_PATH needs to be defined');
    }

    $rokcommon_inlcude_path = @realpath(ROKCOMMON_LIB_PATH . '/include.php');
    if (!file_exists($rokcommon_inlcude_path)) {
        if (!defined('ROKGALLERY_ERROR_MISSING_LIBS')) define('ROKGALLERY_ERROR_MISSING_LIBS', true);
        return array('RokGallery needs the RokCommon library to be installed');
    }

    $included_files = get_included_files();
    if (!in_array($rokcommon_inlcude_path, $included_files) && ($libret = require_once($rokcommon_inlcude_path)) !== 'ROKCOMMON_LIB_INCLUDED') {
        if (!defined('ROKGALLERY_ERROR_MISSING_LIBS')) define('ROKGALLERY_ERROR_MISSING_LIBS', true);
        return $libret;
    }


    if (($loaderrors = require_once(dirname(__FILE__) . '/requirements.php')) !== true) {
        if (!defined('ROKGALLERY_ERROR_MISSING_LIBS')) define('ROKGALLERY_ERROR_MISSING_LIBS', true);
        return $loaderrors;
    }

    //Do base initialization
    RokCommon_ClassLoader::addPath(dirname(__FILE__));


    function RokGallery_exception_handler($exception)
    {
        echo "Uncaught exception: ", $exception->getMessage(), "\n";
    }

    function RokGallery_error_handler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }

        switch ($errno) {
            case E_USER_ERROR:
                echo "<b>ERROR</b> [$errno] $errstr<br />\n";
                echo "  Fatal error on line $errline in file $errfile";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                exit(1);
                break;

            case E_USER_WARNING:
                echo "<b>WARNING</b> [$errno] $errstr\n";
                break;

            case E_USER_NOTICE:
                echo "<b>NOTICE</b> [$errno] $errstr\n";
                break;
        }

        /* Don't execute PHP internal error handler */
        return true;
    }
    set_error_handler("RokGallery_error_handler");
    set_exception_handler('RokGallery_exception_handler');

}
return "ROKGALLERY_LIB_INCLUDED";