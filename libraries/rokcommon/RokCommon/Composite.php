<?php
 /**
 * @version   $Id: Composite.php 43050 2011-09-29 23:11:42Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


class RokCommon_Composite
{
    /**
     * @var RokCommon_Composite_Platform
     */
    protected static $_platform;


    const DEFAULT_PRIORITY = 10;
    const DEFAULT_TEMPLATE_PRIORITY = 20;

    /** @var array[] */
    protected static $_packages_paths = array();

    /** @var bool */
    protected static $_packages_paths_dirty = true;

    /** @var RokCommon_Composite_Package[] */
    protected static $_packages = array();


    protected static function _loadPlatform()
    {
        if (!isset(self::$_platform)) {
            $platform = RokCommon_Platform::getInstance();
            $classname = 'RokCommon_Composite_Platform_' . ucfirst($platform->getPlatformId());
            if (!class_exists($classname, true))
            {
                throw new RokCommon_Loader_Exception('Unable to find Composite library for Platform ' . $platform->getPlatformId());
            }
            self::$_platform  = new $classname();
        }
    }

    public static function getPlatform()
    {
        self::_loadPlatform();
        return self::$_platform;
    }

    /**
     * @param $path
     * @param int $priority
     */
    public static function addPath($path, $priority = self::DEFAULT_PRIORITY)
    {
        self::_loadPlatform();
        if (is_dir($path)) {
            self::$_packages_paths[$priority][$path] = $path;
            self::$_packages_paths_dirty = true;
        }
    }

    /**
     * @param $package
     * @param $path
     * @param int $priority
     */
    public static function addPackagePath($package, $path, $priority = self::DEFAULT_PRIORITY)
    {
        self::_loadPlatform();
        $package_name = strtolower($package);
        self::populatePackage($package_name);
        if (!array_key_exists($package_name, self::$_packages)) self::$_packages[$package_name] = new RokCommon_Composite_Package($package_name);
        self::$_packages[$package_name]->addPath($path, $priority);
    }

    /**
     * @param $package_name
     */
    protected static function populatePackage($package_name)
    {
        if (self::$_packages_paths_dirty) {
            foreach (self::$_packages_paths as $priority => $paths)
            {
                foreach ($paths as $path)
                {
                    $package_path = $path . DS . $package_name;
                    if (file_exists($package_path) && is_dir($package_path)) {

                        // create a context if it wasnt there
                        if (!array_key_exists($package_name, self::$_packages)) {
                            self::$_packages[$package_name] = new RokCommon_Composite_Package($package_name);
                        }
                        // add the path to the context
                        self::$_packages[$package_name]->addPath($package_path, $priority);
                    }
                }
            }
        }
    }

    /**
     * @param $context_path
     * @return bool|\RokCommon_Composite_Context
     */
    public static function &get($context_path)
    {
        self::_loadPlatform();
        if (empty($context_path)) return false;

        $context_path = strtolower($context_path);
        $split = explode('.', $context_path);
        $package_name = array_shift($split);
        $sub_path = implode('.', $split);


        self::populatePackage($package_name);

        if (array_key_exists($package_name, self::$_packages)) {
            return self::$_packages[$package_name]->getContext($sub_path);
        }
        return false;
    }
}
