<?php
/**
  * @version   $Id: FileTags.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_Property_FileTags extends RokGallery_Job_Property
{
    /** @var int */
    protected $fileId;

    /** @var array */
    protected $tags;

    /**
     * @param $fileId
     * @param $tags
     * @return \RokGallery_Job_Property_FileTags
     */
    public function __construct($fileId, $tags)
    {
        $this->fileId = $fileId;
        $this->tags = $tags;
    }

    /**
     * @param int $fileId
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * @return int
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }
}
