<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokGallery_Link_Type_Manual_Info extends RokGallery_Link_Info
{
    public $type = 'manual';
    public $link;

    public function __construct($link = '')
    {
        $this->link = $link;
    }
    
    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }
}
