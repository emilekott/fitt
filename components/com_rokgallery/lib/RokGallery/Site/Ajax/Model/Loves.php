<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallerySiteAjaxModelLoves extends RokCommon_Ajax_AbstractModel
{

    const CONTEXT_ROOT ='com_rokgallery.site.loves.file_';
    /**
     *
     * $params object should be a json like
     * <code>
     * {
     *      "id": 1   // this is the slice ID displayed
     * }
     * </code>
     * @throws Exception
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function add($params)
    {
        try
        {
            $result = new RokCommon_Ajax_Result();
            $slice = RokGallery_Model_SliceTable::getSingle($params->id);
            if ($slice === false) {
                throw new RokCommon_Ajax_Exception('No Slice Found');
            }
            if (!RokCommon_Session::get(self::CONTEXT_ROOT.$slice->file_id,false))
            {
                $slice->incrementLoves();
                RokCommon_Session::set(self::CONTEXT_ROOT.$slice->file_id,true);
            }
            $result->setPayload(array('loves'=>$slice->File->Loves->count, 'new_action'=> 'unlove', 'text'=> rc__(RokGallery_Config::getOption(RokGallery_Config::OPTION_UNLOVE_TEXT))));
        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $result;
    }

    /**
     * $params object should be a json like
     * <code>
     * {
     *      "id": 1   // this is the slice ID displayed
     * }
     * @throws Exception|RokCommon_Ajax_Exception
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function remove($params)
    {
        try
        {
            $result = new RokCommon_Ajax_Result();
            $slice = RokGallery_Model_SliceTable::getSingle($params->id);
            if ($slice === false) {
                throw new RokCommon_Ajax_Exception('No Slice Found');
            }
            if (RokCommon_Session::get(self::CONTEXT_ROOT.$slice->file_id,false)) {
                $slice->decrementLoves();
                RokCommon_Session::clear(self::CONTEXT_ROOT.$slice->file_id);
            }
            $result->setPayload(array('loves'=>$slice->File->Loves->count, 'new_action'=> 'love', 'text'=> rc__(RokGallery_Config::getOption(RokGallery_Config::OPTION_LOVE_TEXT))));

        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }
}
