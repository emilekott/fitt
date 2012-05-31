<?php
/**
  * @version   $Id: Mainpage.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
  
class RokGallery_Admin_MainPage
{

    /** @var Doctrine_Pager */
    protected $pager;

    /**
     * @param $page
     * @param $resultsPerPage
     * @param array $filters
     * @return RokGallery_Model_File[]|bool
     */
    public function &getFiles($page, $resultsPerPage, $filters = array(), $order_by = null, $order_direction = null)
    {
        $this->setupPager($page, $resultsPerPage, $filters, $order_by, $order_direction);
        /** @var RokGallery_Model_File $file  */
        $files = $this->pager->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        return $files;
    }

    /**
     * @param $page
     * @param $resultsPerPage
     * @param array $filters
     * @return \Doctrine_Pager
     */
    public function &getPager($page, $resultsPerPage, $filters = array(), $order_by = null, $order_direction = null)
    {
        $this->setupPager($page, $resultsPerPage, $filters, $order_by, $order_direction);
        return $this->pager;
    }

    public function clearPager()
    {
        unset($this->pager);
    }

    /**
     * @param $page
     * @param $resultsPerPage
     * @param array $filter_items
     * @return Doctrine_Pager
     */
    protected function &setupPager($page, $resultsPerPage, $filter_items = array(), $order_by = null, $order_direction = null)
    {
        if (!isset($this->pager)) {
            $filter = new RokGallery_Admin_MainPageFilter();
            $filter->buildQuery($filter_items, $order_by, $order_direction);
            $this->pager = new Doctrine_Pager(
                $filter->getQuery(),
                $page, // Current page of request
                $resultsPerPage // (Optional) Number of results per page. Default is 25
            );
        }
        return $this->pager;
    }

}
