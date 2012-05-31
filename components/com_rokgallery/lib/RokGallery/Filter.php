<?php
 /**
 * @version   $Id: Filter.php 39204 2011-06-30 07:34:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Filter
{

    /** @var Doctrine_Query */
    protected $_query = null;

    protected $_tag_added = false;

    protected $_gallery_added = false;

    protected $_orderby_run = false;

    protected $_build_run = false;

    /**
     * @param RokGallery_Filter_Item[] $filters
     * @return RokGallery_Filter
     */
    public function __construct($filters = array())
    {
        $this->reset();
        $this->buildQuery($filters);
        $this->setOrderBy('');
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

    }

    /**
     * @param  RokGallery_Filter_Item[] $filters
     */
    public function buildQuery($filters = array())
    {
        foreach ($filters as $filter)
        {
            $type = $filter->type;
            $this->$type();
        }
        $this->setOrderBy('');
    }

    /**
     * @param $filter
     */
    protected function title($filter)
    {
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('f.title = ?', $filter->query);
                break;
            case 'is_not':
                $this->_query->andWhere('f.title <> ?', $filter->query);
                break;
            case 'contains':
                $this->_query->andWhere('f.title LIKE %?%', $filter->query);
                break;
            case 'contains_not':
                $this->_query->andWhere('f.title NOT LIKE %?%', $filter->query);
                break;
            default:
        }
    }

    /**
     * @param $filter
     */
    protected function tags($filter)
    {
        if (!$this->_gallery_added) {
            $this->_query->leftJoin('f.RokGallery_Model_FileTags ft');
            $this->_gallery_added = true;
        }
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('ft.tag = ?', $filter->query);
                break;
            case 'is_not':
                $this->_query->andWhere('ft.tag <> ?', $filter->query);
                break;
            case 'contains':
                $this->_query->andWhere('ft.tag LIKE %?%', $filter->query);
                break;
            case 'contains_not':
                $this->_query->andWhere('ft.tag NOT LIKE %?%', $filter->query);
                break;
            default:
        }
    }

    /**
     * @param $filter
     */
    protected function gallery($filter)
    {
        if (!$this->_tag_added) {
            $this->_query->leftJoin('f.RokGallery_Model_Gallery g WITH f.id = g.file_id');
            $this->_tag_added = true;
        }
        switch ($filter->operator)
        {
            case 'is':
                $this->_query->andWhere('g.id = ?', $filter->query);
                break;
            case 'is_not':
                $this->_query->andWhere('g.id <> ?', $filter->query);
                break;
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
    protected function setOrderBy($orderby)
    {
        if ($this->_orderby_run) return;
        $this->_query->orderBy('f.created_at DESC');
        $this->_orderby_run = true;
    }
}
