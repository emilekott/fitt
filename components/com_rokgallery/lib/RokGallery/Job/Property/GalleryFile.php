<?php
/**
  * @version   $Id: GalleryFile.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_Property_GalleryFile extends RokGallery_Job_Property
{
    /** @var int */
    protected $id;

    /**
     * @param $id
     * @return RokGallery_Job_Property_GalleryFile
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
