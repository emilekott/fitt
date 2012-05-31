<?php
 /**
 * @version   $Id: RokGalleryModule.php 39426 2011-07-04 05:13:45Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('ROKGALLERYMODULE') or die('Restricted access');
jimport('joomla.utilities.date');

class RokGalleryModule
{
    public function getSlices(JParameter &$params)
    {
        $conf =& JFactory::getConfig();
        if ($conf->getValue('config.caching') && $params->get('module_cache')) {
            $user =& JFactory::getUser();
            $cache =& JFactory::getCache('mod_rokgallery');
            $cache->setCaching(true);
            $args = array($params);
            $checksum = md5($params->toString());
            $slices = $cache->get(array($this, '_getSlices',), $args, 'mod_rokgallery-' . $user->get('aid', 0) . '-' . $checksum);
        }
        else
        {
            $slices = $this->_getSlices($params);
        }
        return $slices;
    }

    public function _getSlices($params)
    {

        $gallery_id = $params->get('gallery_id', '');
        $link = $params->get('gallery_link', 'false');

        if (empty($gallery_id)) return;


        $sort_by = $params->get('sort_by', 'slice_title');
        $sort_direction = $params->get('sort_direction', 'ASC');
        $model = new RokGallery_Site_GalleryModel($gallery_id, $sort_by, $sort_direction);

        $slices = $model->getSlices();



        $images = array();

        $limit = $params->get('limit_count', 0);
        foreach($slices as $slice)
        {
            $images[] = $this->_getPresentationImage($slice, $params);
            $limit--;
            if ($limit == 0) break;
        }
        return $images;
    }

    protected function &_getPresentationImage(RokGallery_Model_Slice &$slice, JRegistry &$params)
    {
        $image = new stdClass();
        $image->id = $slice->id;
        $image->title = ($params->get('gallery_use_title_from', 'slice') == 'slice') ? $slice->title
                : $slice->File->title;
        $image->caption = ($params->get('gallery_use_caption_from', 'slice') == 'slice') ? $slice->caption
                : $slice->File->description;
        $image->slug = $slice->slug;
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
        if (!RokGallery_Link::isJson($slice->link))
        {
            $link = new RokGallery_Link(json_encode(new RokGallery_Link_Type_Manual_Info($slice->link)));
        }

        else {
            $link = new RokGallery_Link($slice->link);
        }

        switch ($params->get('link'))
        {
            case 'rokbox':
                $gallery_name = preg_replace("/(\s|_|-|!)/i", '', $slice->Gallery->name);
                $image->link = $slice->imageurl;
                $image->rel = 'rel="rokbox[' . $image->xsize . ' ' . $image->ysize . '](' . $gallery_name . ')" title="' . $image->title . ' :: ' . $image->caption . '" ';
                break;
            case 'rokbox_full':
                $image->link = $slice->File->imageurl;
                $image->rel = 'rel="rokbox[' . $slice->File->xsize . ' ' . $slice->File->ysize . '](' . str_replace(' ', '', $slice->Gallery->name) . ')" title="' . $image->title . ' :: ' . $image->caption . '" ';
                break;
            case 'slice_link':
                switch ($link->getType()){
                    case 'manual':
                        if ($link->getUrl() == ''){
                            $menu = &JSite::getMenu();
                            $activeenuitem  = $menu->getActive();
                            $menuItem = &$menu->getItem($params->get('default_menuitem',$activeenuitem->id));
                            $image->link =  JRoute::_($menuItem->link.'&Itemid='.$menuItem->id);
                        }
                        else
                        {
                            $image->link = $link->getUrl();
                        }
                        break;
                    case 'article':
                        $image->link = JRoute::_($link->getUrl());
                        break;
                }
                break;
            default:
                $image->link = null;
        }
        return $image;
    }
}
