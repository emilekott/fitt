<?php
/**
  * @version   $Id: ImportFile.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_Property_ImportFile extends RokGallery_Job_Property
{
    /** @var string */
    protected $path;

    /** @var int */
    protected $id;

    /** @var string */
    protected $filename;

    /** @var bool */
    protected $completed = false;

    /** @var bool */
    protected $error = false;

    /** @var string */
    protected $status;

    /**
     *
     */
    public function __construct()
    {
        $this->id = RokCommon_UUID::generate();
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

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
