<?php
/**
  * @version   $Id: Joomla15.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

jimport( 'joomla.application.component.helper' );


/**
 *
 */
class RokGallery_Config_Platform_Joomla15 implements RokGallery_Config_Platform
{

    /** @var JParameter */
    protected $options;
    protected $app_config;

    /**
     *
     */
    public function __construct()
    {
        $this->app_config =& JFactory::getConfig();
        $this->options =& JComponentHelper::getParams( 'com_rokgallery' );
    }

    /**
     * @param $name
     * @param null $default
     * @param null $context
     * @return mixed the options value
     */
    public function getOption($name, $default = null, $context = null)
    {
        $value = $default;
        switch($name){
            case RokGallery_Config::OPTION_THUMBNAIL_BASE_URL:
            case RokGallery_Config::OPTION_BASE_URL:
                //TODO: Change this to return from a router
                $value = JURI::root(true).'/media/rokgallery/';
                break;
            case RokGallery_Config::OPTION_ROOT_PATH:
                $value = JPATH_SITE.'/media/rokgallery/';
                break;
            case RokGallery_Config::OPTION_JOB_QUEUE_PATH;
                $value = $this->app_config->getValue('config.tmp_path', JPATH_ROOT.DS.'tmp');
                break;
            default:
                $value = $this->options->get($name, $default);
                break;
        }
        return $value;
    }
}
