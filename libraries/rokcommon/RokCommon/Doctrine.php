<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

class RokCommon_Doctrine
{

    /** @var RokCommon_Doctrine */
    protected static $_instance;


    /** @var Doctrine_Manager */
    protected $manager;

    /** @var RokCommon_Doctrine_Platform */
    protected $platform_instance;

    /** @var Doctrine_Connection */
    protected $connection;

    /** @var Doctrine_Cache_Db */
    protected $cacheDriver;


    /**
     * @static
     * @return RokCommon_Doctrine
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new RokCommon_Doctrine();
        }
        return self::$_instance;
    }

    protected function __construct()
    {
        if (!ini_get('date.timezone')){
            date_default_timezone_set('UTC');
        }
        
        $this->loadPlatformInstance();

        // load the doctrine class loader
        spl_autoload_register(array('Doctrine', 'autoload'));
        // Load up the manager and put base settings
        $this->manager = Doctrine_Manager::getInstance();
        $this->manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_PEAR);
        $this->manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $this->_initializeExtensions();
        $this->connection = Doctrine_Manager::connection($this->platform_instance->getConnectionUrl(), 'default');
    }

    /**
     * Initialize all the custom Doctrime Extensions
     */
    protected function _initializeExtensions()
    {
        spl_autoload_register(array('Doctrine', 'extensionsAutoload'));

        $extention_dir = dirname(__FILE__) . '/Doctrine/Extensions';
        if ($handle = opendir($extention_dir)) {
            while (false !== ($file = readdir($handle)))
            {
                if (!preg_match('/^\./', $file) && is_dir($extention_dir . '/' . $file)) {
                    $this->manager->registerExtension($file, $extention_dir . '/' . $file);
                }
            }
            closedir($handle);
        }
    }


    /**
     * @throws RokCommon_Loader_Exception
     * @return RokCommon_Doctrine_Platform
     */
    protected function loadPlatformInstance()
    {
        $platform = RokCommon_Platform::getInstance();
        $classname = 'RokCommon_Doctrine_Platform_' . ucfirst($platform->getPlatformId());
        if (!class_exists($classname)) {
            throw new RokCommon_Loader_Exception('Unable to find Doctrine library for Platform ' . $platform->getPlatformId());
        }
        $this->platform_instance = new $classname();
    }

    /**
     * @static
     * @param  $path
     * @return void
     */
    public static function addModelPath($path)
    {
        $self = self::getInstance();
        RokCommon_ClassLoader::addPath($path);
        Doctrine_Core::loadModels($path);
    }

    /**
     * @static
     * @return Doctrine_Connection
     */
    public static function getConnection()
    {
        $self = self::getInstance();
        return $self->connection;
    }

    /**
     * @return Doctrine_Manager
     */
    public static function &getManager()
    {
        $self = self::getInstance();
        return $self->manager;
    }

    /**
     * @return RokCommon_Doctrine_Platform
     */
    public static function &getPlatformInstance()
    {
        $self = self::getInstance();
        return $self->platform_instance;
    }


    public static function useApcCache()
    {
        $self = self::getInstance();
        $self->cacheDriver = new Doctrine_Cache_Apc();
        $self->manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $self->cacheDriver);
        $self->manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $self->cacheDriver);

    }

    public static function useMemDBCache()
    {
        $self = self::getInstance();
        $cacheConn = Doctrine_Manager::connection(new PDO('sqlite::memory:'));
        $self->cacheDriver = new Doctrine_Cache_Db(array('connection' => $cacheConn, 'tableName' => "RokGallery"));
        $self->cacheDriver->createTable();
        $self->manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $self->cacheDriver);
        $self->manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $self->cacheDriver);
        $self->manager->setCurrentConnection($self->connection->getName());
    }

    public static function useMemcacheCache($host = 'localhost', $port = 11211, $persistent = false, $compression = false)
    {
        $self = self::getInstance();
        $servers = array(
            'host' => $host,
            'port' => $port,
            'persistent' => $persistent
        );
        $self->cacheDriver = new Doctrine_Cache_Memcache(array(
                                                              'servers' => $servers,
                                                              'compression' => $compression
                                                         ));
        $self->manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $self->cacheDriver);
        $self->manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $self->cacheDriver);

    }


}