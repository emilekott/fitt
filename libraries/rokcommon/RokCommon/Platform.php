<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// No direct access
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Platform
{
    protected $php_version;
    protected $platform;
    protected $platform_version;
    protected $platform_short_version;
    protected $jslib;
    protected $jslib_version;
    protected $jslib_shortname;
    protected $_js_file_checks = array();


    /**
     * @var RokCommon_Platform
     */
    protected static $instance;

    /**
     * @static
     * @return RokCommon_Platform
     */
    public static function &getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new RokCommon_Platform();
        }
        return self::$instance;
    }

    /**
     *
     */
    protected function __construct()
    {
        $this->php_version = phpversion();
        $this->_getPlatformInfo();
    }

    /**
     * @return void
     */
    protected function _getPlatformInfo()
    {
        if (defined('_JEXEC') && defined('JVERSION')) {  // See if its joomla
            $this->platform = 'joomla';
            if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')) {
                $this->platform_version = JVERSION;
                $this->platform_short_version = '1.5';
                $this->_getJoomla15Info();
            }
            else if (version_compare(JVERSION, '1.6', '>=')) {
                $this->platform_version = JVERSION;
                $this->platform_short_version = '1.6';
                $this->_getJoomla16Info();
            }
            else
            {
                $this->_unsuportedInfo();
            }
        }
        else if (defined('ABSPATH')) {          // See if its wordpress
            $this->platform = 'wordpress';
            global $wp_version;
            if (isset($wp_version))
            {
                $this->platform_version = $wp_version;
                $this->platform_short_version = '1.5';
            }
            else {
                $this->_unsuportedInfo();
            }
        }
        else
        {
            $this->_unsuportedInfo();
        }
    }

    /**
     * @return void
     */
    protected function _unsuportedInfo()
    {
        $this->platform = 'unsupported';
        $this->platform_short_version = '';

    }

    // Get info for JoomlaRTCacheDriver 1.5 versions
    /**
     * @return void
     */
    protected function _getJoomla15Info()
    {
        $mainframe =& JFactory::getApplication();

        $this->jslib = 'mootools';

        $this->jslib_shortname = 'mt';

        $mootools_version = JFactory::getApplication()->get('MooToolsVersion', '1.11');
        if ($mootools_version != "1.11" || $mainframe->isAdmin()) {
            $this->jslib_version = '1.2';
        }
        else
        {
            $this->jslib_version = '1.1';
        }

        // Create the JS checks for JoomlaRTCacheDriver 1.5
        $this->_js_file_checks = array(
            '-' . $this->jslib . $this->jslib_version,
            '-' . $this->jslib_shortname . $this->jslib_version
        );
        if (JPluginHelper::isEnabled('system', 'mtupgrade')) {
            $this->_js_file_checks[] = '-upgrade';
        }
        $this->_js_file_checks[] = '';
    }

    // Get info for JoomlaRTCacheDriver 1.6 versions
    protected function _getJoomla16Info()
    {
        $this->jslib = 'mootools';
        $this->jslib_shortname = 'mt';
        $this->jslib_version = '1.2';
        $this->_js_file_checks = array(
            '-' . $this->jslib . $this->jslib_version,
            '-' . $this->jslib_shortname . $this->jslib_version,
            ''
        );
    }

    public function getJSChecks($file, $keep_path = false)
    {
        $checkfiles = array();
        $ext = substr($file, strrpos($file, '.'));
        $path = ($keep_path) ? dirname($file) . DS : '';
        $filename = basename($file, $ext);
        foreach ($this->_js_file_checks as $suffix)
        {
            $checkfiles[] = $path . $filename . $suffix . $ext;
        }
        return $checkfiles;
    }

    public function getJSInit()
    {
        return $this->jslib_shortname . '_' . str_replace('.', '_', $this->jslib_version);
    }

    public function getJslib()
    {
        return $this->jslib;
    }

    public function getJslibShortname()
    {
        return $this->jslib_shortname;
    }

    public function getJslibVersion()
    {
        return $this->jslib_version;
    }

    public function getPhpVersion()
    {
        return $this->php_version;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function getPlatformVersion()
    {
        return $this->platform_version;
    }

    public function getPlatformId()
    {
        return strtolower($this->getPlatform()) . preg_replace('/[\.]/i', '', $this->getPlatformShortVersion());
    }

    public function getPlatformShortVersion()
    {
        return $this->platform_short_version;
    }

}
