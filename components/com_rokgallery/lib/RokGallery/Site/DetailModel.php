<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Site_DetailModel
{


    protected $id;
    protected $current_page;
    protected $gallery_id;
    protected $order_by;
    protected $order_direction;
    protected $items_per_page;

    protected $next_page;
    protected $next_id;

    protected $prev_page;
    protected $prev_id;


    public function __construct($gallery_id, $id, $current_page, $items_per_page, $order_by = null, $order_direction = null)
    {
        $this->gallery_id = $gallery_id;
        $this->id = $id;
        $this->current_page = $current_page;
        $this->order_by = $order_by;
        $this->order_direction = $order_direction;
        $this->items_per_page = $items_per_page;

        $this->getPagination();


    }

    /**
     * @param $id
     * @return \RokGallery_Model_Slice|bool
     */
    public function &getSingle()
    {
        /** @var $single RokGallery_Model_Slice */
        $single = RokGallery_Model_SliceTable::getSingle($this->id);
        if (!$single->published) {
            return false;
        }
        return $single;
    }


    protected function getPagination()
    {
        $filter = new RokGallery_Site_DetailFilter($this->gallery_id, $this->order_by, $this->order_direction);
        $query = $filter->getQuery();
        $current_pager = new Doctrine_Pager($query, $this->current_page, $this->items_per_page);
        $current_page_ids = $current_pager->execute(array(), Doctrine_Core::HYDRATE_SCALAR);

        $current_index = 0;
        foreach ($current_page_ids as $index => $id_holder) {
            if ($this->id == $id_holder['s_id']) {
                $current_index = $index;
                break;
            }
        }

        if ($current_pager->getFirstIndice()+$current_index > $current_pager->getFirstIndice()) {
            $this->prev_page = $this->current_page;
            $this->prev_id = $current_page_ids[$current_index - 1]['s_id'];
        }

        if ($current_pager->getFirstIndice()+$current_index < $current_pager->getLastIndice()) {
            $this->next_page = $this->current_page;
            $this->next_id = $current_page_ids[$current_index + 1]['s_id'];
        }

        if ($current_pager->getFirstIndice()+$current_index == $current_pager->getFirstIndice()) {
            $this->prev_page = $current_pager->getPreviousPage();
            if ($current_pager->getFirstPage() != $this->current_page) {
                $prev_pager = new Doctrine_Pager($query, $this->current_page - 1, $this->items_per_page);
                $prev_page_ids = $prev_pager->execute(array(), Doctrine_Core::HYDRATE_SCALAR);
                $this->prev_id = $prev_page_ids[count($prev_page_ids)-1]['s_id'];
            }
        }

        if ($current_pager->getFirstIndice()+$current_index == $current_pager->getLastIndice()) {
            $this->next_page = $current_pager->getNextPage();
            if ($current_pager->getLastPage() != $this->current_page) {
                $next_pager = new Doctrine_Pager($query, $this->current_page + 1, $this->items_per_page);
                $next_page_ids = $next_pager->execute(array(), Doctrine_Core::HYDRATE_SCALAR);
                $this->next_id = $next_page_ids[0]['s_id'];
            }
        }
    }


    /**
     * @return the previous page
     */
    public function getPrevPage()
    {
        return $this->prev_page;
    }

    /**
     * @return int|null the previous slices id or null of there is no previous slice
     */
    public function getPrevId()
    {
        return $this->prev_id;
    }

    /**
     * @return int the next page
     */
    public function getNextPage()
    {
        return $this->next_page;
    }

    /**
     * @return int|null the next slices id or null if there is no next slice
     */
    public function getNextId()
    {
        return $this->next_id;
    }


    /**
     * @return int the current page
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }


}
