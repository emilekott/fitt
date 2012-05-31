<?php
 /**
 * @version   $Id: FileFilter.php 39200 2011-06-30 04:31:21Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Model_FileFilter extends RokCommon_Doctrine_Filter
{
    /**
     * @param RokGallery_Model_File $record
     * @return string
     */
    protected function _getBasePath(RokGallery_Model_File $record)
    {
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_ROOT_PATH);
        $path = $root . RokGallery_Helper::getPathFromGUID($record->guid, DS);
        return $path;
    }

    /**
     * @param RokGallery_Model_File $record
     * @return string
     */
    protected function _getImageUrl(RokGallery_Model_File $record)
    {
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_BASE_URL);
        $path = $root . $record->getRelativePath('/');
        return $path;
    }

    /**
     * @param RokGallery_Model_File $record
     * @return string
     */
    protected function _getAdminThumbPath(RokGallery_Model_File $record)
    {
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_ROOT_PATH);
        $slice =& $record->getAdminThumbSlice();
        if (null == $slice) {
            return $root . RokGallery_Config::getOption(RokGallery_Config::OPTION_MISSING_IMAGE_PATH, '/missing_image.png');
        }
        $path = $root . $slice->getRelativeThumbPath(DS, 'admin-thumb');
        return $path;
    }

    /**
     * @param RokGallery_Model_File $record
     * @return string
     */
    protected function _getAdminThumbUrl(RokGallery_Model_File $record)
    {
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_THUMBNAIL_BASE_URL);
        $slice =& $record->getAdminThumbSlice();
        if (null == $slice) {
            return $root . RokGallery_Config::getOption(RokGallery_Config::OPTION_MISSING_IMAGE_PATH, '/missing_image.png');
        }
        $path = $root . $slice->getRelativeThumbPath('/', 'admin-thumb');
        return $path;
    }

    /**
     * @param RokGallery_Model_File $record
     * @return string
     */
    protected function _getMiniAdminThumbPath(RokGallery_Model_File $record)
    {
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_ROOT_PATH);
        $slice =& $record->getAdminThumbSlice();
        $path = $root . $slice->getRelativeThumbPath(DS, 'mini-admin-thumb');
        if (null == $slice) {
            return $root . RokGallery_Config::getOption(RokGallery_Config::OPTION_MISSING_IMAGE_PATH, '/missing_image.png');
        }
        if (!file_exists($path)) {
            $slice->generateMiniAdminThumbnail();
        }
        return $path;
    }

    /**
     * @param RokGallery_Model_File $record
     * @return string
     */
    protected function _getMiniAdminThumbUrl(RokGallery_Model_File $record)
    {
        $this->_getMiniAdminThumbPath($record);
        $slice =& $record->getAdminThumbSlice();
        $root = RokGallery_Config::getOption(RokGallery_Config::OPTION_THUMBNAIL_BASE_URL);
        if (null == $slice) {
            return $root . RokGallery_Config::getOption(RokGallery_Config::OPTION_MISSING_IMAGE_PATH, '/mini_missing_image.png');
        }
        $path = $root . $slice->getRelativeThumbPath('/','mini-admin-thumb');
        return $path;
    }


    /**
     * @param RokGallery_Model_File $record
     * @return RokGallery_Model_Slice
     */
    protected function &_getAdminSlice(RokGallery_Model_File $record)
    {
        $slice = $record->getAdminThumbSlice();
        if (null == $slice) {
            $slice = $this->createMissingAdminSlice();
        }
        return $slice;
    }

    /**
     * @return RokGallery_Model_Slice
     */
    protected function &createMissingAdminSlice()
    {
        $slice = new RokGallery_Model_Slice();
        $slice->id = RokGallery_Config::MISSING_ADMIN_SLICE_ID;
        $slice->title = 'Missing Admin Slice';
        return $slice;
    }

}
