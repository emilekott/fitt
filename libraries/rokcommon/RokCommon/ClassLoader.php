<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Based on
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * http://groups.google.com/group/php-standards/web/final-proposal
 *
 *     // Example which loads classes for the Doctrine Common package in the
 *     // Doctrine\Common namespace.
 *     $classLoader = new SplClassLoader('Doctrine\Common', '/path/to/doctrine');
 *     $classLoader->register();
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Roman S. Borschel <roman@code-factory.org>
 * @author Matthew Weier O'Phinney <matthew@zend.com>
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 * @author Juozas Kaziukenas <juozas@juokaz.com>
 */

if (!class_exists('RokCommon_ClassLoader', false)) {

    class RokCommon_ClassLoader
    {

        /**
         * @var RokCommon_ClassLoader
         */
        static $_instance;

        /**
         * @var array
         */
        static $_orderedLoaders = array();

        /**
         * @var array
         */
        static $_namedLoaders = array();


        /**
         * @static
         * @return RokCommon_ClassLoader
         */
        public static function getInstance()
        {
            if (!isset(self::$_instance)) {
                self::$_instance = new RokCommon_ClassLoader();
                self::_setupDefaultLoader();
            }
            return self::$_instance;
        }

        private static function _setupDefaultLoader()
        {
            $bootstrapLoaderPath = dirname(__FILE__) . '/BootStrap.php';
            if (!class_exists('RokCommon_BootStrap') && file_exists($bootstrapLoaderPath)) {
                require_once($bootstrapLoaderPath);
                $bootstrapLoader = new RokCommon_BootStrap();
                self::_registerLoader('BOOTSTRAP', $bootstrapLoader, -1000);
            }

            $currentLibPath = realpath(dirname(__FILE__).'/..');
            $defaultLoader = new RokCommon_Loader_Default();
            $defaultLoader->addPath($currentLibPath);
            self::_registerLoader(RokCommon_Loader_Default::NAME, $defaultLoader, RokCommon_Loader_Default::PRIORITY);
            self::unregisterLoader('BOOTSTRAP');
        }

        /**
         * @static
         * @throws RokCommon_Loader_Exception if the loader already exists.
         * @param string $loaderName name to register the loader under.
         * @param RokCommon_Loader $loader the instance of the loader to register
         * @param int $priority priority of the loader
         * @return void
         */
        public static function registerLoader($loaderName, RokCommon_Loader &$loader, $priority = 10)
        {
            self::getInstance();
            if (array_key_exists($loaderName, self::$_namedLoaders)) {
                throw new RokCommon_Loader_Exception('Class Loader ' . $loaderName . ' already exists');
            }
            self::_registerLoader($loaderName, $loader, $priority);
        }

        /**
         * Convenience function to add a path to the default loader
         * @static
         * @param string $path the path to add to the default loader
         * @param string $namespace the namespace to give to the path
         * @return void
         */
        public static function addPath($path, $namespace = null){
            $defaultLoader = self::getLoader(RokCommon_Loader_Default::NAME);
            $defaultLoader->addPath($path, $namespace);
        }

        /**
         * @param $loaderName
         * @param $loader
         * @param $priority
         *
         */
        private static function _registerLoader($loaderName, &$loader, $priority)
        {
            self::$_orderedLoaders[$priority][$loaderName] = &$loader;
            self::$_namedLoaders[$loaderName] = &$loader;
        }

        /**
         * @static
         * @throws RokCommon_Loader_Exception if the loader doesnt exists
         * @param  string $loaderName loader to unregister
         * @return void
         */
        public static function unregisterLoader($loaderName)
        {
            self::getInstance();
            if (!array_key_exists($loaderName, self::$_namedLoaders)) {
                throw new RokCommon_Loader_Exception('Class Loader ' . $loaderName . ' not found');
            }

            // unset loader
            foreach (self::$_orderedLoaders as $priority => $loaders)
            {
                if (array_key_exists($loaderName, $loaders)) {
                    unset(self::$_orderedLoaders[$priority][$loaderName]);
                }
            }
            unset(self::$_namedLoaders[$loaderName]);
        }

        /**
         * Returns a reference to the named loader.
         * @static
         * @param  string $loaderName the named loader to return
         * @return RokCommon_Loader|bool FALSE if no loader found with that name
         */
        public static function &getLoader($loaderName)
        {
            self::getInstance();
            if (!array_key_exists($loaderName, self::$_namedLoaders)) {
                throw new RokCommon_Loader_Exception('Loader ' . $loaderName . ' does not exists');
            }
            return self::$_namedLoaders[$loaderName];
        }

        /**
         * See if the loader is registered
         * @static
         * @param  $loaderName
         * @return bool
         */
        public static function isLoaderRegistered($loaderName)
        {
            return array_key_exists($loaderName, self::$_namedLoaders);
        }

        private function __construct()
        {
            $this->_register();
        }

        public function __destruct()
        {
            $this->_unregister();
        }

        /**
         * Installs this class loader on the SPL autoload stack.
         */
        private function _register()
        {
            // prepend original autoloader
            if (function_exists('__autoload')) {
                 spl_autoload_register('__autoload');
            }
            spl_autoload_register(array($this, 'loadClass'));
        }

        /**
         * Uninstalls this class loader from the SPL autoloader stack.
         */
        private function _unregister()
        {
            spl_autoload_unregister(array($this, 'loadClass'));
        }

        /**
         * Loads the given class or interface.
         *
         * @param string $className The name of the class to load.
         * @return void
         */
        public function loadClass($className)
        {
            if (!empty(self::$_orderedLoaders)) {
                ksort(self::$_orderedLoaders);
                foreach (self::$_orderedLoaders as $priority => $priorityLoaders)
                {
                    foreach($priorityLoaders as $loaderName => $loader){
                        if ($loader->loadClass($className)) break;
                    }
                }
            }
        }


    }
    // Initialize the Loader
    RokCommon_ClassLoader::getInstance();
}