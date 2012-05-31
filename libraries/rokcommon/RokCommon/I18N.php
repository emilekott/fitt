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
 * @param  $string
 * @return string
 */
function rc__($string)
{
    try
    {
        $i18n = RokCommon_I18N::getInstance();
        $args = func_get_args();
        if (count($args) == 1){
            return call_user_func_array(array($i18n,'translate'), $args);
        }
        else
        {
            return call_user_func_array(array($i18n,'translateFormatted'), $args);
        }
    }
    catch (RokCommon_Loader_Exception $le)
    {
        //TODO: log a failure to load a translation driver
        return $string;
    }
}

function rc_e($string)
{
    $args = func_get_args();
    $out =  call_user_func_array('rc__', $args);
    echo $out;
}

function rc_n($string, $n)
{
    try
    {
       $i18n = RokCommon_I18N::getInstance();
       $args = func_get_args();
       return call_user_func_array(array($i18n,'translatePlural'), $args);
    }
    catch (RokCommon_Loader_Exception $le)
    {
        //TODO: log a failure to load a translation driver
        return $string;
    }
}

function rc_ne($string, $n)
{
    echo rc_n($string, $n);
}


/**
 *
 */
class RokCommon_I18N implements RokCommon_I18N_Platform
{
    /**
     * @var RokCommon_I18N
     */
    protected static $instance;

    /**
     * @static
     * @throws RokCommon_Loader_Exception
     * @return RokCommon_I18N
     */
    public static function &getInstance()
    {
        if (!isset(self::$instance))
        {
            $platform = RokCommon_Platform::getInstance();
            $classname = 'RokCommon_I18N_Platform_' . ucfirst($platform->getPlatformId());
            if (!class_exists($classname, true))
            {
                throw new RokCommon_Loader_Exception('Unable to find Translation library for Platform ' . $platform->getPlatformId());
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
     * @param  $string
     * @return string
     */
    public function translate($string)
    {
    }

    /**
     * @param  $count
     * @param  $string
     * @param  $multistring
     * @return string
     */
    public function translatePlural($string, $n)
    {
    }

    /**
     * @param  $string
     * @param  mixed    Mixed number of arguments for the sprintf function.
     * @return string
     */
    public function translateFormatted($string)
    {
    }
}

