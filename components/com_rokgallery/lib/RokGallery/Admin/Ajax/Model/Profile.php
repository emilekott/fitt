<?php
/**
  * @version   $Id: Profile.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelProfile extends RokCommon_Ajax_AbstractModel
{
    /**
     * Get the basic profile info
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1
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
                    ->select('p.*')
                    ->from('RokGallery_Model_Profile p')
                    ->where('p.id = ?', $params->id)
                    ->orderBy('p.name');

            $profile = $q->fetchOne();
            $q->free();
            $result->setPayload(array('profile' => $profile->toJsonableArray()));
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Delete the profile
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function delete($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            RokGallery_Doctrine::getConnection()->beginTransaction();
            $profile = Doctrine_Core::getTable('RokGallery_Model_Profile')->getSingle($params->id);
            $profile->delete();
            RokGallery_Doctrine::getConnection()->commit();
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            throw $e;
        }
        return $result;
    }

    /**
     * Update the profile
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1,
     *  'profile':{'name':'new name','description':'new description'}
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function update($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            RokGallery_Doctrine::getConnection()->beginTransaction();
            $profile = Doctrine_Core::getTable('RokGallery_Model_Profile')->getSingle($params->id);

            if (array_key_exists('profile', $params->profile))
            {
                $params->profile['profile'] = json_encode($params->profile['profile']);
            }
            foreach($params->file as $field => $value) {
                if (isset($profile->$field)){
                    $profile->$field = $value;
                }
            }
            $profile->save();
            RokGallery_Doctrine::getConnection()->commit();
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            throw $e;
        }
        return $result;
    }
}
