<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokCommon_Header {

    /**
     * @var RokCommon_Header
     */
    protected static $instance;

    /**
     * @static
     * @throws RokCommon_Loader_Exception
     * @return RokCommon_Header_Platform
     */
    public static function &getInstance()
    {
        if (!isset(self::$instance))
        {
            $platform = RokCommon_Platform::getInstance();
            $classname = 'RokCommon_Header_Platform_' . ucfirst($platform->getPlatformId());
            if (!class_exists($classname, true))
            {
                throw new RokCommon_Loader_Exception('Unable to find Header implementation for Platform ' . $platform->getPlatformId());
            }
            self::$instance = new $classname();
        }
        return self::$instance;
    }

    /**
     */
    public static function initialize()
    {
    }

    /**
     *
     */
    protected function __construct()
    {
    }

    /**
     * @param $file
     */
    public static function addScript($file)
    {
        $self = self::getInstance();
        $self->addScript($file);
    }

    /**
     * @param $text
     */
    public static function addInlineScript($text)
    {
        $self = self::getInstance();
        $self->addInlineScript($text);
    }

    /**
     * @param $file
     */
    public static function addStyle($file)
    {
        $self = self::getInstance();
        $self->addStyle($file);
    }

    /**
     * @param $text
     */
    public static function addInlineStyle($text)
    {
        $self = self::getInstance();
        $self->addInlineStyle($text);
    }


}
