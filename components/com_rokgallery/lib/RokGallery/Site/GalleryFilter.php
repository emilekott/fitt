<?php
 /**
 * @version   $Id: GalleryFilter.php 39261 2011-07-01 06:43:23Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Site_GalleryFilter
{
    /** @var Doctrine_Query */
    protected $_query = null;

    /** @var int */
    protected $gallery_id = 0;

    /** @var string */
    protected $_order_by = 'gallery_ordering';

    /** @var string */
    protected $_order_direction = 'ASC';


    protected $_build_run = false;
    protected $_orderby_run = false;

    /**
     *
     * @param int $gallery_id
     * @param string $order_by
     * @param string $order_direction
     * @return \RokGallery_Site_GalleryFilter
     */
    public function __construct($gallery_id = 0, $order_by = null, $order_direction = null)
    {
        $this->gallery_id = $gallery_id;
        if (null != $order_by) $this->_order_by = $order_by;
        if (null != $order_direction) $this->_order_direction = $order_direction;
        $this->reset();
    }

    /**
     * @return \RokGallery_Site_GalleryFilter
     */
    public function &reset()
    {
        $this->_query = Doctrine_Query::create()
                ->select('s.*')
                ->from('RokGallery_Model_Slice s')
                ->where('s.published = ?', true);
        $this->_orderby_run = false;
        return $this;
    }

    /**
     * @param int $gallery_id
     * @param string $order_by
     * @param string $order_direction
     * @return \RokGallery_Site_GalleryFilter
     */
    public function &buildQuery($gallery_id = 0, $order_by = null, $order_direction = null)
    {
        if (!$this->_build_run) {
            $this->gallery();
            $this->setOrderBy();
            $this->_build_run = true;
        }
        return $this;
    }

    /**
     *
     */
    protected function gallery()
    {
        $this->_query->andWhere('s.gallery_id = ?', $this->gallery_id);
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
     * @return
     */
    protected function setOrderBy()
    {
        if ($this->_orderby_run) return;
        switch ($this->_order_by) {
            case 'slice_title':
            case 'slice_filesize':
            case 'slice_xsize':
            case 'slice_ysize':
            case 'slice_updated_at':
            case 'slice_created_at':
                $this->_query->orderBy('s.' . str_replace('slice_', '', $this->_order_by) . ' ' . $this->_order_direction);
                break;
            case 'file_title':
            case 'file_filesize':
            case 'file_xsize':
            case 'file_ysize':
            case 'file_updated_at':
            case 'file_created_at':
                $this->_query->leftJoin('s.File f');
                $this->_query->orderBy('f.' . str_replace('file_', '', $this->_order_by) . ' ' . $this->_order_direction);
                break;
            case 'views':
                $this->_query->leftJoin('s.File f');
                $this->_query->leftJoin('f.Views v');
                $this->_query->orderBy('v.count ' . $this->_order_direction);
                break;
            case 'loves':
                $this->_query->leftJoin('s.File f');
                $this->_query->leftJoin('f.Loves l');
                $this->_query->orderBy('l.count ' . $this->_order_direction);
                break;
            case 'random':
                $this->_query->select('s.*, RANDOM() as rand');
                $this->_query->orderby('rand');
                break;
            case 'gallery_ordering':
                $this->_query->orderBy('s.ordering');

        }
        $this->_orderby_run = true;
    }
}

