<?php
/**
  * @version   $Id: Files.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelFiles extends RokCommon_Ajax_AbstractModel
{

    /**
     * Get the full list of jobs
     * <code>
     * {
     *  'ids': [1,2,3]
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function delete($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $q = Doctrine_Query::create()
                    ->select('f.*')
                    ->from('RokGallery_Model_File f')
                    ->whereIn('f.id', $params->ids);

            $files = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
            $q->free();
            try
            {
                RokGallery_Doctrine::getConnection()->beginTransaction();
                foreach ($files as &$file)
                {
                    $realdirpath = realpath($file->basepath);
                    if ($realdirpath == false) {
                        throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_DIR_N_FOR_FILE_N_TO_REMOVE', $dirpath, $file->title));
                    }

                    $delret = RokGallery_Helper::delete_folder($realdirpath);
                    if ($delret === false) {
                        throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_DELETE_DIR_N_FOR_FILE_N_TO_REMOVE', $dirpath, $file->title));
                    }
                    $file->delete();
                }
                RokGallery_Doctrine::getConnection()->commit();
            }
            catch (Exception $e)
            {
                RokGallery_Doctrine::getConnection()->rollback();
                throw $e;
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Get the full list of jobs
     * <code>
     * {
     *   'ids': [1,2,3],
     *   'settings': {'pubished':true}
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function update($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {

            try
            {
                RokGallery_Doctrine::getConnection()->beginTransaction();

                if (count($params->settings) <= 0)
                    throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_NO_SETTINGS_PASSED_TO_UPDATE'));


                $q = Doctrine_Query::create()->update('RokGallery_Model_File');

                foreach ($params->settings as $column => $value)
                {
                    $q->set($column, '?', $value);
                }
                $q->whereIn('id', $params->ids);
                $q->execute();

                RokGallery_Doctrine::getConnection()->commit();
            }
            catch (Exception $e)
            {
                RokGallery_Doctrine::getConnection()->rollback();
                throw $e;
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Get the full list of jobs
     * <code>
     * {
     *   'ids': [1,2,3],
     *   'tags': ['tag1','tag2']
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function addTags($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {

            try
            {
                RokGallery_Doctrine::getConnection()->beginTransaction();

                $properties = array();
                foreach($params->ids as $file_id)
                {
                    $properties[] = new RokGallery_Job_Property_FileTags($file_id,$params->tags);
                }

                $job = RokGallery_Job::create(RokGallery_Job::TYPE_TAGADDITION);
                $job->setProperties($properties);
                $job->save();

                RokGallery_Doctrine::getConnection()->commit();

                // Disconnect and process job
                $this->sendDisconnectingReturn($result);

                $job->Ready();
                $job->Run('Starting Tag Additions');
                $job->process();
                die();
            }
            catch (Exception $e)
            {
                RokGallery_Doctrine::getConnection()->rollback();
                throw $e;
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
	
	/**
     * Get the Tag Popup layout populated with galleries
     * <code>
     * {
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
	public function getTagPopup($params)
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
	        $html = RokCommon_Composite::get('com_rokgallery.files')->load('default_tag.php', array('galleries' => $galleries));
	        $result->setPayload(array('galleries' => $outgalleries, 'html' => $html));
		}
		catch (Exception $e)
        {
            throw $e;
        }
        return $result;
	}

    /**
     * Get the full list of jobs
     * <code>
     * {
     *   'ids': [1,2,3],
     *   'tags': ['tag1','tag2']
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function removeTags($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {

            try
            {
                RokGallery_Doctrine::getConnection()->beginTransaction();

                $properties = array();
                foreach($params->ids as $file_id)
                {
                    $properties[] = new RokGallery_Job_Property_FileTags($file_id,$params->tags);
                }

                $job = RokGallery_Job::create(RokGallery_Job::TYPE_TAGREMOVAL);
                $job->setProperties($properties);
                $job->save();

                RokGallery_Doctrine::getConnection()->commit();

                // Disconnect and process job
                $this->sendDisconnectingReturn($result);

                $job->Ready();
                $job->Run('Starting Tag Removals');
                $job->process();
                die();
            }
            catch (Exception $e)
            {
                RokGallery_Doctrine::getConnection()->rollback();
                throw $e;
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
}
