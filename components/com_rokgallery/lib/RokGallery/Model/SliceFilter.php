<?php
 /**
 * @version   $Id: SliceFilter.php 39525 2011-07-05 18:58:14Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Model_SliceFilter extends RokCommon_Doctrine_Filter
{

    protected static $slices = array();

    protected function getThumbInfo(RokGallery_Model_Slice $record)
    {
        if (array_key_exists($record->id, RokGallery_Model_SliceFilter::$slices)) {
            return RokGallery_Model_SliceFilter::$slices[$record->id];
        }

        $info = new stdClass();
        $info->thumb_xsize = 0;
        $info->thumb_ysize = 0;
        $info->admin_thumb_xsize = RokGallery_Config::DEFAULT_ADMIN_THUMB_XSIZE;
        $info->admin_thumb_ysize = RokGallery_Config::DEFAULT_ADMIN_THUMB_YSIZE;
        $info->mini_admin_thumb_xsize = RokGallery_Config::DEFAULT_MINI_ADMIN_THUMB_XSIZE;
        $info->mini_admin_thumb_ysize = RokGallery_Config::DEFAULT_MINI_ADMIN_THUMB_YSIZE;

        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_BASE_URL);
        $info->image_url = $root . $record->getRelativePath('/');


        $info->thumb_path = $this->getThumbPath($record, 'thumb');
        $info->thumb_url = $this->getThumbUrl($record, 'thumb');
        $this->checkThumbExists($record, $info->thumb_path,'thumb');

        $info->admin_thumb_path = $this->getThumbPath($record, 'admin-thumb');
        $info->admin_thumb_url = $this->getThumbUrl($record, 'admin-thumb');
        if ($this->checkThumbExists($record, $info->admin_thumb_path, 'admin-thumb') && is_file($info->admin_thumb_path)) {
            $imageinfo = getimagesize($info->admin_thumb_path);
            $info->admin_thumb_xsize = $imageinfo[0];
            $info->admin_thumb_ysize = $imageinfo[1];
        }

        $info->mini_admin_thumb_path = $this->getThumbPath($record, 'mini-admin-thumb');
        $info->mini_admin_thumb_url = $this->getThumbUrl($record, 'mini-admin-thumb');
        if ($this->checkThumbExists($record, $info->mini_admin_thumb_path, 'mini-admin-thumb') && is_file($info->mini_admin_thumb_path)) {
            $imageinfo = getimagesize($info->mini_admin_thumb_path);
            $info->mini_admin_thumb_xsize = $imageinfo[0];
            $info->mini_admin_thumb_ysize = $imageinfo[1];
        }

        RokGallery_Model_SliceFilter::$slices[$record->id] = $info;
        return RokGallery_Model_SliceFilter::$slices[$record->id];
    }

    public static function cleanThumbInfo()
    {
        RokGallery_Model_SliceFilter::$slices = array();
    }

    protected function getThumbPath(RokGallery_Model_Slice $record, $type)
    {
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_ROOT_PATH);
        $path = $root . $record->getRelativeThumbPath(DS, $type);
        return $path;
    }

    protected function getThumbUrl(RokGallery_Model_Slice $record, $type)
    {
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_THUMBNAIL_BASE_URL);
        $path = $root . $record->getRelativeThumbPath('/', $type);
        return $path;
    }

    protected function checkThumbExists(RokGallery_Model_Slice $record, $path, $type)
    {
        $ret = false;
        if (!file_exists($path)) {
            switch ($type)
            {
                case 'admin-thumb':
                    $record->generateAdminThumbnail();
                    $ret = true;
                    break;
                case 'mini-admin-thumb':
                    $record->generateMiniAdminThumbnail();
                    $ret = true;
                    break;
                case 'thumb':
                    $record->generateDefaultThumbnail();
                    $record->save();
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        }
        else if ($record->thumb_xsize == 0 && $record->thumb_ysize == 0)
        {
            $record->generateDefaultThumbnail();
            $record->save();
        }
        else {
            $ret = true;
        }
        return $ret;
    }

    protected function _getThumbPath(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->thumb_path;
    }

    protected function _getThumbUrl(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->thumb_url;
    }

    protected function _getAdminThumbPath(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->admin_thumb_path;
    }

    protected function _getAdminThumbUrl(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->admin_thumb_url;
    }

    protected function _getMiniAdminThumbPath(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->mini_admin_thumb_path;
    }

    protected function _getMiniAdminThumbUrl(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->mini_admin_thumb_url;
    }

    protected function _getImageUrl($record)
    {
        $info = $this->getThumbInfo($record);
        return $info->image_url;
    }

    protected function _getAdminThumbXSize(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->admin_thumb_xsize;
    }

    protected function _getAdminThumbYSize(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->admin_thumb_ysize;
    }

    protected function _getMiniAdminThumbXSize(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->mini_admin_thumb_xsize;
    }
    protected function _getMiniAdminThumbYSize(RokGallery_Model_Slice $record)
    {
        $info = $this->getThumbInfo($record);
        return $info->mini_admin_thumb_ysize;
    }

    protected function _getDoILove($record)
    {
        return RokCommon_Session::get('com_rokgallery.site.loves.file_' . $record->file_id, false);
    }
}