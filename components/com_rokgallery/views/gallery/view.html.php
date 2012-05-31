<?php
 /**
 * @version   $Id: view.html.php 39533 2011-07-05 20:08:15Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the rokgallery component
 */
class RokgalleryViewGallery extends JView
{
    public function __construct($config = array())
    {
        $option = JRequest::getCmd('option');
        parent::__construct($config);
    }

    function display($tpl = null)
    {

        JHTML::_('behavior.mootools');
        $session = JFactory::getSession();
        $session_namespace = 'com_rokgallery.site';
        $app =& JFactory::getApplication();
        $menu = $app->getMenu();
        $activemenu = $menu->getActive();
        if (empty($activemenu)) {
            $activemenu = $menu->getDefault();
            $menu->setActive($activemenu->id);
        }
        /** @var $params JParameter */
        $params = &$activemenu->params;
        foreach ($params->toArray() as $param_name => $param_value)
        {
            $this->assign($param_name, $param_value);
        }

        $document =& JFactory::getDocument();

        $uri = JRequest::getURI();

        // Get session variables
        $style = JRequest::getWord('style', $params->get('default_style'));

        $sort_direction = JRequest::getVar('sort_direction', $session->get('sort_direction', $params->get('default_sort_direction'), $session_namespace));

        if ($params->get('show_sorts')) {
            $sort_by = JRequest::getVar('sort_by', $session->get('sort_by', $params->get('default_sort_by'), $session_namespace));
        }
        else {
            $sort_by = JRequest::getVar('sort_by', $params->get('default_sort_by'));
        }

        if ($params->get('show_available_layouts', true))
            $layout = JRequest::getVar('layout', $session->get('layout', $this->getLayout(), $session_namespace));
        else
            $layout = JRequest::getVar('layout', $this->getLayout());
        if ($layout == 'default') {
            $layout = $params->get('default_layout');
        }

        $items_per_row = (int)$params->get($layout . '-items_per_row', 2);
        $items_per_page = (int)$params->get($layout . '-items_per_row', 2) * (int)$params->get($layout . '-rows_per_page', 2);
        $gallery_id = $params->get('gallery_id');
        $current_page = JRequest::getInt('page', 1);
        $menu_item = JRequest::getInt('Itemid');

        // Set session passed vars
        $session->set('gallery_id', $gallery_id, $session_namespace);
        $session->set('sort_by', $sort_by, $session_namespace);
        $session->set('sort_direction', $sort_direction, $session_namespace);
        $session->set('layout', $layout, $session_namespace);
        $session->set('last_page', $current_page, $session_namespace);
        $session->set('items_per_page', $items_per_page, $session_namespace);


        $base_page_url = RokCommon_URL::setParams('index.php',
                                                  array(
                                                       'option' => 'com_rokgallery',
                                                       'view' => 'gallery'
                                                  ));

        $base_ajax_url = RokCommon_URL::setParams('index.php',
                                                  array(
                                                       'option' => 'com_rokgallery',
                                                       'task' => 'ajax',
                                                       'format' => 'raw'
                                                  ));

        $model = new RokGallery_Site_GalleryModel($gallery_id, $sort_by, $sort_direction);


        $gallery = RokGallery_Model_GalleryTable::getSingle($gallery_id);
        if ($gallery == false) {
            return JError::raiseError(500, 'Gallery with ID "' . $params->get('gallery_id') . '" not found');
        }

        $slices = $model->getPagedSlices($current_page, $items_per_page);
        $pager = $model->getPager();


        if ($pager->getLastPage() < $current_page) $current_page = $pager->getLastPage();

        $next_page = false;
        $prev_page = false;
        $pages = false;
        if ($pager->haveToPaginate()) {
            $ranger = $pager->getRange('Sliding', array('chunk' => $params->get('pages_in_shown_range')));
            $pages = array();
            foreach ($ranger->rangeAroundPage() as $page)
            {
                $page_class = new stdClass();
                $page_class->page_num = $page;
                $page_class->active = ($current_page == $page);
                if (!$page_class->active) {
                    $page_class->link = JRoute::_(RokCommon_URL::updateParams($base_page_url, array('page' => $page)));
                }
                else {
                    $page_class->link = '#';
                }
                $pages[] = $page_class;
                if ($page == $pager->getNextPage() && $pager->getNextPage() != $current_page) {
                    $next_page = $page_class;
                }
                if ($page == $pager->getPreviousPage() && $pager->getPreviousPage() != $current_page) {
                    $prev_page = $page_class;
                }
            }
        }

        $images = array();
        $passed_slices = array();
        foreach ($slices as &$slice)
        {
            $images[] = $this->getPresentationImage($slice, $params, $base_page_url, $sort_by, $sort_direction);
            $passed_slices[$slice->id] = $slice;
        }
        $this->assignRef('images', $images);
        $this->assignRef('slices', $passed_slices);

        $layout_names = array('grid-3col' => rc__('ROKGALLERY_GRID_3COL'),
                              'grid-4col' => rc__('ROKGALLERY_GRID_4COL'),
                              'list-2col' => rc__('ROKGALLERY_LIST_2COL'));
        $layouts = $this->getList('layout', $layout, $current_page, $layout_names, $menu_item, $base_page_url);
        $this->assignRef('layouts', $layouts);

        $style_names = array('light' => rc__('ROKGALLERY_LIGHT'),
                             'dark' => rc__('ROKGALLERY_DARK'));
        $styles = $this->getList('style', $style, $current_page, $style_names, $menu_item, $base_page_url);
        $this->assignRef('styles', $styles);

        $sort_by_names = array('gallery_ordering' => rc__('ROKGALLERY_SORT_GALLERY_ORDERING'),
                                'file_created_at' => rc__('ROKGALLERY_SORT_CREATED'),
                               'slice_updated_at' => rc__('ROKGALLERY_SORT_UPDATED'),
                               'slice_title' => rc__('ROKGALLERY_SORT_TITLE'),
                               'loves' => rc__('ROKGALLERY_SORT_LOVES'),
                               'views' => rc__('ROKGALLERY_SORT_VIEWS'));
        $sort_bys = $this->getList('sort_by', $sort_by, $current_page, $sort_by_names, $menu_item, $base_page_url);
        $this->assignRef('sort_bys', $sort_bys);

        $sort_dir_names = array('ASC' => 'ascending', 'DESC' => 'descending');
        $sort_directions = $this->getList('sort_direction', $sort_direction, $current_page, $sort_dir_names, $menu_item, $base_page_url);
        $this->assignRef('sort_directions', $sort_directions);


        $total_items = $pager->getNumResults();
        $item_number = $pager->getFirstIndice();
        $layout_context = 'com_rokgallery.gallery.' . $layout;
        $style_context = $layout_context . '.' . $style;

        // Assignments to JS namespaces
        $this->assign('base_ajax_url', $base_ajax_url);

        $this->assign('available_layouts', $params->get('available_layouts', array()));
        $this->assign('available_sorts', $params->get('available_sorts', array()));

        $sort_dir = false;
        foreach ($sort_bys as $sort_by_item)
        {
            if ($sort_by_item->active) {
                $sort_dir = new stdClass();
                $sort_dir->field = $sort_by_item->name;
                $other_sort = ($sort_direction == 'ASC') ? 'DESC' : 'ASC';
                $sort_dir->link = $sort_directions[$other_sort]->link;
                $sort_dir->class = ($other_sort == 'ASC') ? 'ascending' : 'descending';
                break;
            }
        }

        // Joomla params
        $this->assign('show_page_heading', $params->get('show_page_heading', 1));
        $this->assign('page_heading', $this->escape($params->get('page_heading')));

        // Assignments to page passed vars
        $this->assign('pages', $pages);
        $this->assign('next_page', $next_page);
        $this->assign('prev_page', $prev_page);
        $this->assign('items_per_row', $items_per_row);
        $this->assign('total_items', $total_items);
        $this->assign('items_per_page', $items_per_page);
        $this->assign('item_number', $item_number);
        $this->assign('context', $layout_context);
        $this->assign('style_context', $style_context);
        $this->assign('current_page', $current_page);
        $this->assign('thumb_width', $gallery->thumb_xsize);
        $this->assign('thumb_height', $gallery->thumb_ysize);

        $this->assign('sort_by', $sort_by);
        $this->assign('sort_direction', $sort_direction);
        $this->assign('sort_dir', $sort_dir);
        $this->assign('style', $style);
        $this->assign('layout', $layout);

        // populate basic page render vars
        $this->assign('show_created_at', $params->get('gallery_show_created_at', true));
        $this->assign('show_tags', $params->get('gallery_show_tags', false));
        $this->assign('show_tags_count', $params->get('gallery_show_tags_count', false));
        $this->assign('show_caption', $params->get('gallery_show_caption', false));
        $this->assign('show_title', $params->get('gallery_show_title', false));
        $this->assign('show_loves', $params->get('gallery_show_loves', false));
        $this->assign('show_views', $params->get('gallery_show_views', false));
        $this->assign('show_available_layouts', $params->get('show_available_layouts', true));


        $this->setLayout('default');
        parent::display($tpl);
    }

    protected function getList($type, $active, $current_page, $items, $menu_item, $base_url)
    {
        $ret = array();
        foreach ($items as $key => $label)
        {
            $item_class = new stdClass();
            $item_class->name = $key;
            $item_class->link = JRoute::_(RokCommon_URL::updateParams($base_url, array($type => $key, 'page' => $current_page, 'Itemid' => $menu_item)));
            $item_class->active = ($key == $active);
            $item_class->label = $label;
            $ret[$key] = $item_class;
        }
        return $ret;
    }

    protected function &getPresentationImage(RokGallery_Model_Slice &$slice, JRegistry &$params, $base_page_url, $sort_by, $sort_direction)
    {
        $image = new stdClass();
        $image->id = $slice->id;
        $image->title = ($params->get('gallery_use_title_from', 'slice') == 'slice') ? $slice->title
                : $slice->File->title;
        $image->caption = ($params->get('gallery_use_caption_from', 'slice') == 'slice') ? $slice->caption
                : $slice->File->description;
        $image->created_at = date('j M Y', strtotime($slice->File->created_at));
        $image->updated_at = date('j M Y', strtotime($slice->updated_at));
        $image->views = $slice->File->Views->count;
        $image->loves = $slice->File->Loves->count;
        $image->thumburl = $slice->thumburl;
        $image->xsize = $slice->xsize;
        $image->ysize = $slice->ysize;
        $image->doilove = $slice->doilove;
        $image->filesize = $slice->filesize;
        $image->imageurl = $slice->imageurl;
        $image->rel = '';

        if (!RokGallery_Link::isJson($slice->link)) {
            $link = new RokGallery_Link(json_encode(new RokGallery_Link_Type_Manual_Info($slice->link)));
        }

        else {
            $link = new RokGallery_Link($slice->link);
        }


        switch ($params->get('slice_link_to'))
        {
            case 'rokbox':
                $image->link = $slice->imageurl;
                $image->rel = 'rel="rokbox[' . $image->xsize . ' ' . $image->ysize . '](' . str_replace(' ', '', $slice->Gallery->name) . ')" title="' . $image->title . ' :: ' . $image->caption . '" ';
                break;
            case 'rokbox_full':
                $image->link = $slice->imageurl;
                $image->rel = 'rel="rokbox[' . $image->xsize . ' ' . $image->ysize . '](' . str_replace(' ', '', $slice->Gallery->name) . ')" title="' . $image->title . ' :: ' . $image->caption . '" ';
                break;
            case 'force_details':
                $image->link = JRoute::_(RokCommon_URL::updateParams($base_page_url, array('view' => 'detail', 'id' => $slice->id)));
                break;
            default:
                switch ($link->getType()){
                    case 'manual':
                        $image->link = ($link->getUrl() != '')? $link->getUrl()
                            : JRoute::_(RokCommon_URL::updateParams($base_page_url, array('view' => 'detail', 'id' => $slice->id)));
                        break;
                    case 'article':
                        $image->link = JRoute::_($link->getUrl());
                        break;
                }
                break;
        }

        switch ($params->get('gallery_use_tags_from', 'slice')) {
            case 'slice':
                $tags =& $slice->Tags;
                break;
            case 'file':
                $tags =& $slice->File->Tags;
                break;
            case 'combined':
                $tags =& $slice->getCombinedTags();
                break;
        }

        $image->tags = array();
        foreach ($tags as $tag)
        {
            if (!($params->get('gallery_remove_gallery_tags', false) && in_array($tag['tag'], $slice->Gallery->filetags))) {
                $image->tags[] = $tag['tag'];
            }
        }

        return $image;
    }
}
