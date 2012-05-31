<?php
 /**
 * @version   $Id: GalleryModel.php 39426 2011-07-04 05:13:45Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Site_GalleryModel
{
    /**
     * @var RokGallery_Site_GalleryPager
     */
    protected $pager;

    /** @var RokGallery_Site_GalleryFilter  */
    protected $filter;

    /**
     * @param $gallery_id
     * @param null $order_by
     * @param null $order_direction
     */
    public  function __construct($gallery_id, $order_by = null, $order_direction = null)
    {
        $this->filter = new RokGallery_Site_GalleryFilter($gallery_id, $order_by, $order_direction);
    }

    /**
     * @param $current_page
     * @param $items_per_page
     * @return \RokGallery_Model_Slices[]|bool
     */
    public function &getPagedSlices($current_page, $items_per_page)
    {
        $this->pager = new RokGallery_Site_GalleryPager($this->filter, $current_page, $items_per_page);
        /** @var RokGallery_Model_Slices[] $slices  */
        $slices = $this->pager->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        return $slices;
    }

    /**
     * @return \RokGallery_Model_Slices[]|bool
     */
    public function &getSlices()
    {
        $slices = $this->filter->getQuery()->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        return $slices;
    }

    /**
     * @return \RokGallery_Site_GalleryPager
     */
    public function &getPager()
    {
        return $this->pager;
    }




    /**
     * @param \RokGallery_Site_GalleryFilter $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return \RokGallery_Site_GalleryFilter
     */
    public function &getFilter()
    {
        return $this->filter;
    }


}
