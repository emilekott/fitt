<?php
 /**
  * @version   $Id: Helper.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Manipulation_Helper
{
    public static function prepSerializedForJson($serialized_manips)
    {
        $manipulations = $serialized_manips;//unserialize($serialized_manips);
        $to_json = array();
        foreach ($manipulations as $manipulation)
        {
            $class = new stdClass();
            $class->action = $manipulation->getType();
            $class->options = $manipulation;
            $to_json[] = $class;
        }
        return $to_json;
    }

    public static function unserialize($json_array)
    {
        return unserialize($json_array);
    }

    public static function serialize(array $manipulations = array())
    {
        return serialize($manipulations);
    }

    public static function unserializeFromJson($json_array){
        $manipulations = array();
        foreach($json_array as $json_manip)
        {
            $classname = 'RokGallery_Manipulation_Action_'.ucfirst($json_manip->action);
            if (!class_exists($classname))
                throw new RokGallery_Manipulation_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_PASSED_MANIPULATION_ACTION_N',$json_manip->action));
            $action = new  $classname($json_manip->options);
            $manipulations[] = $action;
        }
        return $manipulations;
    }
}
