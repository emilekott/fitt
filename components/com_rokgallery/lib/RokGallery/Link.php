<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Link
{
    /** @var RokGallery_Link_Type */
    protected $link;

    public function __construct($json)
    {
        $decode = json_decode($json);
        $classname = 'RokGallery_Link_Type_' . ucfirst($decode->type);
        if (!class_exists($classname)) {
            throw new RokCommon_Loader_Exception('Unable to find Link Type ' . $decode->type);
        }
        $this->link = new $classname(get_object_vars($decode));
    }

    public function getUrl()
    {
        return $this->link->getUrl();
    }

    public function getJSON()
    {
        return json_encode($this->link->getJSONable());
    }

    public static function isJson($string)
    {
        return @json_decode($string) != null;
    }

    public function getType()
    {
        return $this->link->getType();
    }
}
