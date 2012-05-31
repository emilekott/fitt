<?php
/**
  * @version   $Id: MainPageFilter.php 39310 2011-07-02 00:31:55Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Admin_MainPageFilter
{
    /** @var Doctrine_Query */
    protected $_query = null;

    protected $_tag_added = false;

    protected $_gallery_added = false;

    protected $_orderby_run = false;

    protected $_order_by = 'created_at';

    protected $_order_direction = 'DESC';

    protected $_build_run = false;

    protected $_gallery_remove_joint_added = false;

    protected $_slice_alias_count = 0;

    protected $_tag_subselect_count = 0;

    /**
     * @param RokGallery_Filter_Item[] $filters
     * @param null $order_by
     * @param null $order_direction
     * @return \RokGallery_Admin_MainPageFilter
     */
    public function __construct($filters = array(), $order_by = null, $order_direction = null)
    {
        if (null != $order_by) $this->_order_by = $order_by;
        if (null != $order_direction) $this->_order_direction = $order_direction;
        $this->reset();
    }

    /**
     */
    public function reset()
    {
        $this->_query = Doctrine_Query::create()
                ->select('f.*')
                ->from('RokGallery_Model_File f');
        $this->_tag_added = false;
        $this->_gallery_added = false;
        $this->_orderby_run = false;
        $this->_build_run = false;
        $this->_gallery_remove_joint_added = false;
        $this->_slice_alias_count = 0;
        $this->_tag_subselect_count = 0;
    }

    /**
     * @param  RokGallery_Filter_Item[] $filters
     * @param null $order_by
     * @param null $order_direction
     */
    public function buildQuery($filters = array(), $order_by = null, $order_direction = null)
    {
        if (null != $order_by) $this->_order_by = $order_by;
        if (null != $order_direction) $this->_order_direction = $order_direction;

        foreach ($filters as $filter)
        {
            $type = $filter->type;
            $this->$type($filter);
        }
        $this->setOrderBy();
    }

    /**
     * @param $filter
     */
     protected function id($filter)
     {
         switch ($filter->operator)
         {
            case 'is':
                $this->_query->andWhere('f.id = ?', (int)$filter->query);
                break;
            case 'is_not':
                $this->_query->andWhere('f.id <> ?', (int)$filter->query);
                break;
            default:
         }
     }

    /**
     * @param $filter
     */
    protected function title($filter)
    {
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('LOWER(f.title) = ?', $filter->query);
                break;
            case 'is_not':
                $this->_query->andWhere('LOWER(f.title) <> ?', $filter->query);
                break;
            case 'contains':
                $this->_query->andWhere('LOWER(f.title) LIKE ?', '%' . $filter->query . '%');
                break;
            case 'contains_not':
                $this->_query->andWhere('LOWER(f.title) NOT LIKE ?', '%' . $filter->query . '%');
                break;
            default:
        }
    }

    /**
     * @param $filter
     */
    protected function tags($filter)
    {
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere(sprintf('f.id in (SELECT ft%1$d.file_id from RokGallery_Model_FileTags ft%1$d where ft%1$d.tag = \'%2$s\')',$this->_tag_subselect_count,$filter->query));
                break;
            case 'is_not':
                $this->_query->andWhere(sprintf('f.id in (SELECT ft%1$d.file_id from RokGallery_Model_FileTags ft%1$d where ft%1$d.tag <> \'%2$s\')',$this->_tag_subselect_count,$filter->query));
                break;
            case 'contains':
                $query = sprintf('f.id in (SELECT ft%1$d.file_id from RokGallery_Model_FileTags ft%1$d where ft%1$d.tag LIKE \'%2$s\')',$this->_tag_subselect_count,'%'.$filter->query.'%');
                $this->_query->andWhere(sprintf('f.id in (SELECT ft%1$d.file_id from RokGallery_Model_FileTags ft%1$d where ft%1$d.tag LIKE \'%2$s\')',$this->_tag_subselect_count,'%'.$filter->query.'%'));
                break;
            case 'contains_not':
                $this->_query->andWhere(sprintf('f.id in (SELECT ft%1$d.file_id from RokGallery_Model_FileTags ft%1$d where ft%1$d.tag NOT LIKE \'%2$s\')',$this->_tag_subselect_count,'%'.$filter->query.'%'));
                break;
            default:
        }
        $this->_tag_subselect_count++;
        $this->_tag_added = true;
    }

    /**
     * @param $filter
     */
    protected function gallery($filter)
    {
        if (!$this->_gallery_added) {
            $this->_query->leftJoin('f.Slices s');
            $this->_query->groupBy('f.id');
            $this->_gallery_added = true;
        }
        switch ($filter->operator)
        {
            case 'is':
                $s = $this->_slice_alias_count++;
                $this->_query->andWhere('f.id in (SELECT s' . $s . '.file_id FROM RokGallery_Model_Slice s' . $s . ' where s' . $s . '.gallery_id = ? GROUP BY s' . $s . '.file_id)', $filter->query);
                $this->_query->andWhere('s.gallery_id = ?',  $filter->query);
                break;
            default:
        }
    }

    /**
     * @param $filter
     */
    protected function published($filter)
    {
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('f.published = ?', 1);
                break;
            case 'is_not':
                $this->_query->andWhere('f.published = ?', 0);
                break;
            default:
        }
    }

    /**
     * @param $filter
     */
    protected function filesize($filter)
    {
        $filter->query = RokGallery_Helper::getFilesizeAsInt($filter->query);
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('f.filesize = ?', (int)$filter->query);
                break;
            case 'greater_than':
                $this->_query->andWhere('f.filesize >= ?', (int)$filter->query);
                break;
            case 'lesser_than':
                $this->_query->andWhere('f.filesize <= ?', (int)$filter->query);
                break;
            default:
        }
    }

    /**
     * @param $filter
     */
    protected function xsize($filter)
    {
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('f.xsize = ?', (int)$filter->query);
                break;
            case 'greater_than':
                $this->_query->andWhere('f.xsize >= ?', (int)$filter->query);
                break;
            case 'lesser_than':
                $this->_query->andWhere('f.xsize <= ?', (int)$filter->query);
                break;
            default:
        }
    }

    /**
     * @param $filter
     */
    protected function ysize($filter)
    {
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('f.ysize = ?', (int)$filter->query);
                break;
            case 'greater_than':
                $this->_query->andWhere('f.ysize >= ?', (int)$filter->query);
                break;
            case 'lesser_than':
                $this->_query->andWhere('f.ysize <= ?', (int)$filter->query);
                break;
            default:
        }
    }

    /**
     * @return \Doctrine_Query
     */
    public function &getQuery()
    {
        if (!$this->_build_run) {
            $this->buildQuery();
        }
        return $this->_query;
    }


    /**
     * @param $orderby
     */
    protected function setOrderBy()
    {
        if ($this->_orderby_run) return;
        switch($this->_order_by){
            case 'gallery_ordering':
                if ($this->_gallery_added){
                    $this->_query->orderBy('s.ordering ' . $this->_order_direction);
                }
                break;
            default:
                $this->_query->orderBy('f.' . $this->_order_by . ' ' . $this->_order_direction);
        }
        $this->_orderby_run = true;
    }


}

