<?php
/**
  * @version   $Id: Galleries.php 39506 2011-07-05 16:46:11Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
class RokGalleryAdminAjaxModelGalleries extends RokCommon_Ajax_AbstractModel
{

    /**
     * Get the basic file info and supporting slices/tags
     * $params object should be a json like
     * <code>
     * {
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function get($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $q = Doctrine_Query::create()
                    ->select('j.*')
                    ->from('RokGallery_Model_Gallery j')
                    ->orderBy('j.name DESC');

            /** @var Doctrine_Collection $galleries  */
            $galleries = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
            $outgalleries = array();
            foreach ($galleries as $gallery)
            {
                /** @var RokGallery_Model_Gallery $gallery  */
                $outgalleries[] = $gallery->toJsonableArray();
            }
            $html = RokCommon_Composite::get('com_rokgallery.galleries')->load('default.php', array('galleries' => $galleries));
            $result->setPayload(array('galleries' => $outgalleries, 'html' => $html, 'delete_slices'=> RokGallery_Config::getOption(RokGallery_Config::OPTION_GALLERY_REMOVE_SLICES,0)));

        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }
}
