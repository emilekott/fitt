<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Link_Type_Article extends RokGallery_Link_AbstractType
{
    /**
     * @var RokGallery_Link_Type_Article_Platform
     */
    protected $platform_type;

    /** @var RokGallery_Link_Type_Article_Info */
    protected $article;

    public function __construct(array $vars)
    {
        parent::__construct($vars);
        $platform = RokCommon_Platform::getInstance();
        $classname = 'RokGallery_Link_Type_Article_Platform_' . ucfirst($platform->getPlatformId());
        if (!class_exists($classname))
        {
            throw new RokCommon_Loader_Exception('Unable to find Article Link Type library for Platform ' . $platform->getPlatformId());
        }
        $this->platform_type = new $classname();
        $this->populateArticleInfo();
    }

    protected function populateArticleInfo(){
        $this->article = $this->platform_type->getArticleInfo($this->id);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->article->getLink();
    }


    public function getJSONable()
    {
        return $this->article;
    }



}
