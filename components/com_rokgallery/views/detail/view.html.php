<?php
 /**
 * @version   $Id: view.html.php 39564 2011-07-06 06:45:56Z btowles $
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
class RokgalleryViewDetail extends JView
{

    /** @var RokGallery_Site_DetailModel */
    protected $rtmodel;

    public function __construct($config = array())
    {
        $option = JRequest::getCmd('option');
        parent::__construct($config);

        $document =& JFactory::getDocument();
        $session =& JFactory::getSession();

        //$this->rtmodel = new RokGallery_Site_DetailModel();
    }

    function display($tpl = null)
    {
        JHTML::_('behavior.mootools');
        $session_namespace = 'com_rokgallery.site';

        $session =& JFactory::getSession();
        $app =& JFactory::getApplication();
        $params = &$app->getParams();
        $menu = &JSite::getMenu();
        $menuItem = &$menu->getActive();
        //$params = new JParameter($menuItem->params);
        $document =& JFactory::getDocument();
        $id = JRequest::getVar('id');
        $style = JRequest::getWord('style', $params->get('default_style'));
        $layout = JRequest::getVar('layout', $session->get('layout', $this->getLayout(), $session_namespace));
        if ($layout == 'default') {
            $layout = $params->get('detail_layout');
        }

        $gallery_id = $session->get('gallery_id', $params->get('gallery_id'), $session_namespace);
        $sort_by = $session->get('sort_by', $params->get('default_sort_by'), $session_namespace);
        $sort_direction = $session->get('sort_direction', $params->get('default_sort_direction'), $session_namespace);
        $page = JRequest::getVar('page', $session->get('last_page', 1, $session_namespace));
        $items_per_page = $session->get('items_per_page', (int)$params->get($layout . '-items_per_row', 2) * (int)$params->get($layout . '-rows_per_page', 2), $session_namespace);


        /** @var RokGallery_Site_DetailModel $rtmodel  */
        $this->rtmodel = new RokGallery_Site_DetailModel($gallery_id, $id, $page, $items_per_page, $sort_by, $sort_direction);
        $slice = $this->rtmodel->getSingle();
        if ($slice === false) {
            return JError::raiseError(500, 'Gallery Item is not published.');
        }
        if (!RokCommon_Session::get('com_rokgallery.site.views.file_' . $slice->file_id, false)) {
            $slice->incrementView();
            RokCommon_Session::set('com_rokgallery.site.views.file_' . $slice->file_id, true);
        }

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


        // Assignments to JS namespaces
		$this->assign('base_ajax_url', $base_ajax_url);


        $next_link = null;
        $prev_link = null;
        if ($this->rtmodel->getNextId() != null){
            $next_link = JRoute::_(RokCommon_URL::updateParams($base_page_url, array('view' => 'detail', 'id' => $this->rtmodel->getNextId(), 'page' => $this->rtmodel->getNextPage())));
        }
        if ($this->rtmodel->getPrevId() != null){
            $prev_link = JRoute::_(RokCommon_URL::updateParams($base_page_url, array('view' => 'detail', 'id' => $this->rtmodel->getPrevId(), 'page' => $this->rtmodel->getPrevPage())));
        }

        $pathway = &$app->getPathway();
        $pwc = $pathway->getPathway();
        $pwc[count($pwc)-1]->link=$pwc[count($pwc)-1]->link.'&page='.$page;
        $pathway->setPathway($pwc);
        $pathway->addItem($slice->title);

        $layout = $this->getLayout();
        $context = 'com_rokgallery.detail.' . $layout;
        $style_context = $context . "." . $style;

        $this->assign('gallery_link', JRoute::_($menuItem->link."&page=".$this->rtmodel->getCurrentPage()));
        $this->assign('gallery_name', $menuItem->title);
        $this->assign('context', $context);
        $this->assign('style_context', $style_context);
        $this->assign('width', $slice->Gallery->width);
        $this->assign('height', $slice->Gallery->height);
        $this->assign('love_text',rc__(RokGallery_Config::getOption(RokGallery_Config::OPTION_LOVE_TEXT)));
        $this->assign('unlove_text',rc__(RokGallery_Config::getOption(RokGallery_Config::OPTION_UNLOVE_TEXT)));
        $this->assignRef('slice', $slice);
        $image = $this->getPresentationImage($slice,$params);
        $this->assignRef('image',$image);

        $session->set('last_page', $this->rtmodel->getCurrentPage(), $session_namespace);


        $document->setTitle($document->getTitle().' - '. $image->title);
        
        $this->assign('next_link', $next_link);
        $this->assign('prev_link', $prev_link);
        $this->assign('show_title', $params->get('detail_show_title', false));
        $this->assign('show_caption', $params->get('detail_show_caption', false));
        $this->assign('show_tags', $params->get('detail_show_tags', false));
        $this->assign('show_tags_count', $params->get('detail_show_tags_count', false));
        $this->assign('show_created_at', $params->get('detail_show_created_at', true));
        $this->assign('show_updated_at', $params->get('detail_show_updated_at', true));
        $this->assign('show_loves', $params->get('detail_show_loves', false));
        $this->assign('show_views', $params->get('detail_show_views', false));
        $this->assign('show_filesize', $params->get('detail_show_filesize', true));
        $this->assign('show_dimensions', $params->get('detail_show_dimensions', true));
        $this->assign('show_download_full', $params->get('detail_show_download_full', true));
        $this->assign('show_gallery_info', $params->get('detail_show_download_full', true));

        $this->setLayout('default');
        parent::display($tpl);
    }


    protected function &getPresentationImage(RokGallery_Model_Slice $slice, JRegistry $params)
    {
        $image = new stdClass();
        $image->id = $slice->id;
        $image->title = ($params->get('detail_use_title_from', 'slice') == 'slice') ? $slice->title
                : $slice->File->title;
        $image->caption = ($params->get('detail_use_caption_from', 'slice') == 'slice') ? $slice->caption
                : $slice->File->description;
        $image->created_at = date('j M Y', strtotime($slice->File->created_at));
        $image->updated_at = date('j M Y', strtotime($slice->updated_at));
        $image->views = $slice->File->Views->count;
        $image->loves = $slice->File->Loves->count;
        $image->thumburl = $slice->thumburl;
        $image->imageurl = $slice->imageurl;
        $image->xsize = ($params->get('detail_use_dimensions_from', 'file') == 'slice') ? $slice->xsize
                : $slice->File->xsize;
        $image->ysize = ($params->get('detail_use_dimensions_from', 'file') == 'slice') ? $slice->xsize
                : $slice->File->xsize;

        $image->filesize =   RokGallery_Helper::decodeSize(($params->get('detail_use_filesize_from', 'file') == 'slice') ? $slice->filesize
                : $slice->File->filesize);
        $image->fullimageurl = $slice->File->imageurl;
        $image->doilove = $slice->doilove;


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
