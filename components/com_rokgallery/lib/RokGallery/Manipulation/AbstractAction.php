<?php
 /**
  * @version   $Id: AbstractAction.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
abstract class RokGallery_Manipulation_AbstractAction implements RokGallery_Manipulation_Action
{

    protected $setup = false;


    public function __construct($options = array()){
        $this->setup($options);
    }

    /**
     * Takes an array of options and applies them to be properties of the Manipulation.
     * The key of the array is the name of the property to apply the value to.
     *
     * @param array $options key value pairs of property name and value
     */
    public function setup($options = array())
    {
        if (is_object($options))
        {
            $options = get_object_vars($options);
        }
        foreach($options as $key => &$option)
        {
            if (property_exists($this, $key)){
                $this->$key = $option;
            }
        }
        $this->setup = true;
    }

    /**
     * @return bool
     */
    protected function isSetup()
    {
        return $this->setup;
    }
}
